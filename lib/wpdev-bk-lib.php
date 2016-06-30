<?php 
if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S u p p o r t    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    // Change date format
    function wpdevbk_get_date_in_correct_format( $dt, $date_format = false, $time_format = false ) {

        if ($date_format === false)
            $date_format = get_bk_option( 'booking_date_format');
        if ($time_format === false)
            $time_format = get_bk_option( 'booking_time_format');
        if (empty($date_format)) $date_format = "m / d / Y, D";
        if (empty($time_format)) $time_format = 'h:i a';
        $my_time = date('H:i:s' , mysql2date('U',$dt) );
        if ($my_time == '00:00:00')     $time_format='';
        $bk_date = date_i18n($date_format  , mysql2date('U',$dt));
        $bk_time = date_i18n(' ' . $time_format  , mysql2date('U',$dt));
        if ($bk_time == ' ') $bk_time = '';

        return array($bk_date, $bk_time);
    }

    // Check if nowday is tommorow from previosday
    function wpdevbk_is_next_day($nowday, $previosday) {

        if ( empty($previosday) ) return false;

        $nowday_d = (date('m.d.Y',  mysql2date('U', $nowday ))  );
        $prior_day = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        if ($prior_day == $nowday_d)    return true;                // if its the same date


        $previos_array = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        $previos_array = explode('.',$previos_array);
        $prior_day =  date('m.d.Y' , mktime(0, 0, 0, $previos_array[0], ($previos_array[1]+1), $previos_array[2] ));


        if ($prior_day == $nowday_d)    return true;                // tommorow
        else                            return false;               // no
    }

    // Transform the REQESTS parameters (GET and POST) into URL
    function get_params_in_url( $exclude_prms = array(), $only_these_parameters = false ){

        //$url_start = 'admin.php?';                          //$url_start = 'admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking';
        $my_page = WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking';
        if ( isset($_GET['page']) ) $my_page = $_GET['page'] ;
        $url_start     = 'admin.php?page=' . $my_page . '&' ;
        $exclude_prms[]='page';
        foreach ($_REQUEST as $prm_key => $prm_value) {
            if ( ! in_array($prm_key, $exclude_prms ) )
                    if ( ($only_these_parameters === false) || ( in_array($prm_key, $only_these_parameters ) ) )
                $url_start .= $prm_key .'=' . $prm_value . '&' ;
        }
        $url_start = substr($url_start, 0, -1);
        
        return $url_start ;
    }

    // Load default filter parameters only for the initial loading of page.     // ShiftP
    function wpdevbk_get_default_bk_listing_filter_set_to_params( $filter_name ) {

        $wpdevbk_saved_filter  = get_user_option( 'booking_listing_filter_' . $filter_name ) ;

        $exclude_options_from_saved_params = array('tab', 'tab_cvm', 'view_mode', 'wh_booking_type', 'view_days_num');         // Exclude some parameters from the saved Default parameters - the values of these parameters are loading from General Booking Settings page or from the request.
        $wpdevbk_filter_params = array();
        
        // Get here default selected tab saved in a General Booking Settings page
        if (! isset($_REQUEST['tab'])) {  
            $booking_default_toolbar_tab = get_bk_option( 'booking_default_toolbar_tab');
            if ( $booking_default_toolbar_tab !== false) {
                $wpdevbk_filter_params[ 'tab' ] = $booking_default_toolbar_tab;  // 'filter' / 'actions' ;
                $_REQUEST['tab'] = $booking_default_toolbar_tab; ;                    // Set to REQUEST
            }
        }

        // Get here default View mode saved in a General Booking Settings page
        if (! isset($_REQUEST['view_mode'])) { 
            $booking_default_view_mode = get_bk_option( 'bookings_listing_default_view_mode');            
            if ( $booking_default_view_mode !== false) {
                $wpdevbk_filter_params[ 'view_mode' ] = $booking_default_view_mode;  // 'vm_calendar' / 'vm_listing' ;
                $_REQUEST['view_mode'] = $booking_default_view_mode;                     // Set to REQUEST
            } else $_REQUEST['view_mode'] = 'vm_listing';
        }
        
        // Get here default view_days_num
        if (! isset($_REQUEST['view_days_num'])) { 
            $booking_view_days_num = get_bk_option( 'booking_view_days_num');
            if ( $booking_view_days_num !== false) {
                $wpdevbk_filter_params[ 'view_days_num' ] = $booking_view_days_num;  // '30' 
                $_REQUEST['view_days_num'] = $booking_view_days_num;                  
            } else $_REQUEST['view_days_num'] = '365';
        }


        if (     ($wpdevbk_saved_filter !== false) 
              && ($_REQUEST['view_mode'] == 'vm_listing')                       //FixIn: 6.0.1.14
            ){

            $wpdevbk_saved_filter = str_replace('admin.php?', '', $wpdevbk_saved_filter);
            $wpdevbk_saved_filter = explode('&',$wpdevbk_saved_filter);
            
            foreach ($wpdevbk_saved_filter as $bkfilter) {
                $bkfilter_key_value = explode('=',$bkfilter);
                if ( ! isset( $bkfilter_key_value[1] ) ) {                              //FixIn: 6.0.1.13
                    $bkfilter_key_value[1] = '';
                }           
                
                
                if ( ! in_array($bkfilter_key_value[0], $exclude_options_from_saved_params) ) { // Exclude some parameters from the saved Default parameters - the values of these parameters are loading from General Booking Settings page or from the request.
                    $wpdevbk_filter_params[ $bkfilter_key_value[0] ] = trim($bkfilter_key_value[1]);
                }
            }

            // If we are do not Apply POST or custom GET, so  Saved params apply to REQUEST
            if ( (! isset($_REQUEST['wh_approved'])) && (! isset($_REQUEST['scroll_day'])) ) {                            // We are do not have approved or pending value, so its mean that user open the page as default, without clicking on Filter apply.
                foreach ($wpdevbk_filter_params as $filter_key => $filter_value) {
                    $_REQUEST[$filter_key] = $filter_value ;                    // Set to REQUEST
                }
            }

        }
    }

    
    function wpdevbk_get_str_from_dates_short($bk_dates_short, $is_approved = false, $bk_dates_short_id = array() , $booking_types = array() ){
                    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get SHORT Dates showing data ////////////////////////////////////////////////////////////////////////////////////////////////////
            $short_dates_content = '';
            $dcnt = 0;
            foreach ($bk_dates_short as $dt) {
                if ($dt == '-') {       $short_dates_content .= '<span class="date_tire"> - </span>';
                } elseif ($dt == ',') { $short_dates_content .= '<span class="date_tire">, </span>';
                } else {
                    $short_dates_content .= '<a href="javascript:void(0)" class="field-booking-date ';
                    if ($is_approved) $short_dates_content .= ' approved';
                    $short_dates_content .= '">';

                    $bk_date = wpdevbk_get_date_in_correct_format($dt);
                    $short_dates_content .= $bk_date[0];
                    $short_dates_content .= '<sup class="field-booking-time">'. $bk_date[1] .'</sup>';

                     // BL
                     if (class_exists('wpdev_bk_biz_l')) {
                         if (! empty($bk_dates_short_id[$dcnt]) ) {
                             $bk_booking_type_name_date   = $booking_types[$bk_dates_short_id[$dcnt]]->title;        // Default
                             if (strlen($bk_booking_type_name_date)>19) $bk_booking_type_name_date = substr($bk_booking_type_name_date, 0,  13) . '...' . substr($bk_booking_type_name_date, -3 );

                             $short_dates_content .= '<sup class="field-booking-time date_from_dif_type"> '.$bk_booking_type_name_date.'</sup>';
                         }
                     }
                    $short_dates_content .= '</a>';
                }
                $dcnt++;
            }

            return $short_dates_content;
    }

    
    // <editor-fold desc="  C O N T R O L   E L E M E N T S  in   I N T E R F A C E  "  defaultstate="collapsed" >
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Control elements      ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdevbk_selectbox_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block, $wpdevbk_default_value = ''){

            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else                                  $wpdevbk_value = $wpdevbk_default_value;
            $wpdevbk_selector_default = array_search($wpdevbk_value, $wpdevbk_selectors);
            if ($wpdevbk_selector_default === false) {
                $wpdevbk_selector_default = key($wpdevbk_selectors);
                $wpdevbk_selector_default_value = current($wpdevbk_selectors);
            } else $wpdevbk_selector_default_value = $wpdevbk_value;
          ?>
          <div class="control-group" style="float:left;">
            <label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?></label>
            <div class="inline controls">
                
                <div class="btn-group">                    
                  <a href="javascript:void(0)" data-toggle="dropdown" id="<?php echo $wpdevbk_id;?>_selector" class="button button-secondary dropdown-toggle"><label class="label_in_filters"
                          ><?php echo $wpdevbk_help_block; ?>: </label> <span class="wpbc_selected_in_dropdown"><?php echo $wpdevbk_selector_default; ?></span> &nbsp; <span class="caret"></span></a>
                  <ul class="dropdown-menu">
                      <?php
                      foreach ($wpdevbk_selectors as $key=>$value) {
                        if ($value != 'divider') {
                          ?><li><a href="javascript:void(0)" onclick="javascript:showSelectedInDropdown('<?php echo $wpdevbk_id; ?>', jQuery(this).html(), '<?php echo $value; ?>');" ><?php echo $key; ?></a></li><?php
                        } else { ?><li class="divider"></li><?php }
                      } ?>
                  </ul>
                  <input type="hidden" value="<?php echo $wpdevbk_selector_default_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />
                </div>
              <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
            </div>
          </div>
        <?php
    }


    function wpdevbk_checkboxbutton_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block){

            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else                                  $wpdevbk_value = '';
            $wpdevbk_selector_default = array_search($wpdevbk_value, $wpdevbk_selectors);
            if ($wpdevbk_selector_default === false) $wpdevbk_selector_default = current($wpdevbk_selectors);
          ?>
          <div class="control-group" style="float:left;">
           <!--label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?>:</label-->
           <div class="inline controls">
            
            <a href="javascript:void(0)" class="btn" data-toggle="button" name="checkboxbutton_<?php echo $wpdevbk_id; ?>" id="checkboxbutton_<?php echo $wpdevbk_id; ?>"
               onclick="javascript:if (jQuery(this).attr('class').indexOf('active')>0) { jQuery('#<?php echo $wpdevbk_id; ?>').val('<?php echo $wpdevbk_selectors[0]; ?>'); } else { jQuery('#<?php echo $wpdevbk_id; ?>').val('<?php echo $wpdevbk_selectors[1]; ?>'); }; "

               ><?php echo $wpdevbk_control_label; ?></a>
            
            <script type="text/javascript">
                jQuery('#checkboxbutton_<?php echo $wpdevbk_id; ?>').button();
                <?php if ($wpdevbk_value == '1') {  // Press the button ?>
                    jQuery('#checkboxbutton_<?php echo $wpdevbk_id; ?>').button('toggle');
                <?php } ?>
            </script>

            <input type="hidden" value="<?php echo $wpdevbk_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />

            <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
           </div>
          </div>
        <?php
    }


    function wpdevbk_text_filter($wpdevbk_id, $wpdevbk_control_label, $wpdevbk_help_block) {

            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else                                  $wpdevbk_value = '';
        ?>
          <div class="control-group" style="float:left;">
           <!--label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?>:</label-->
           <div class="inline controls">
               <div class="input-prepend">
                   <span class="add-on">&nbsp;<?php echo $wpdevbk_help_block; ?>:&nbsp;</span>
                   <input type="text" class="span2"  placeholder="<?php echo $wpdevbk_control_label; ?>" value="<?php echo $wpdevbk_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />
               </div>
            <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
           </div>
          </div>
        <?php
        /*
               <div class="input-prepend" style="padding:0;">
                   <span class="add-on" style="" >&nbsp;<?php echo $wpdevbk_help_block; ?>:&nbsp;</span>
                   <input type="text" style="display: block !important;float:left;" class="span2"  placeholder="<?php echo $wpdevbk_control_label; ?>" value="<?php echo $wpdevbk_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />
                   
               </div>
        */
    }


    function wpdevbk_text_from_to_filter($wpdevbk_id, $wpdevbk_control_label, $wpdevbk_placeholder, $wpdevbk_help_block, $wpdevbk_id2, $wpdevbk_control_label2, $wpdevbk_placeholder2, $wpdevbk_help_block2, $wpdevbk_width, $input_append = '', $input_append2 = '') {

            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else                                  $wpdevbk_value = '';
            if (isset($_REQUEST[$wpdevbk_id2]))   $wpdevbk_value2 = $_REQUEST[$wpdevbk_id2];
            else                                  $wpdevbk_value2 = '';
        ?>
          <div class="control-group" style="float:left;">
           <label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?></label>
           <div class="inline controls">
            <?php if ( $input_append !== '' ) { ?><div class="input-prepend"><span class="add-on"><?php echo $input_append ?></span><?php } ?>
            <input type="text" class="<?php echo $wpdevbk_width; ?>"  placeholder="<?php echo $wpdevbk_placeholder; ?>" value="<?php echo $wpdevbk_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />
            <?php if ( $input_append !== '' ) { ?></div><?php } ?>
            <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
           </div>
          </div>
           <div class="control-group" style="float:left;">
            <label for="<?php echo $wpdevbk_id2; ?>" class="control-label" style="margin-left: -5px; text-align: left; width: 10px;"><?php echo $wpdevbk_control_label2; ?></label>
            <div class="inline controls">
            <?php if ( $input_append2 !== '' ) { ?><div class="input-prepend"><span class="add-on"><?php echo $input_append2 ?></span><?php } ?>
                <input type="text" class="<?php echo $wpdevbk_width; ?>"  placeholder="<?php echo $wpdevbk_placeholder2; ?>" value="<?php echo $wpdevbk_value2; ?>" id="<?php echo $wpdevbk_id2; ?>" name="<?php echo $wpdevbk_id2; ?>" />
            <?php if ( $input_append2 !== '' ) { ?></div><?php } ?>
            <p class="help-block"><?php echo $wpdevbk_help_block2; ?></p>
           </div>
          </div>
        <?php
    }


    function wpdevbk_dates_selection_for_filter($wpdevbk_id,  $wpdevbk_id2,
                                                $wpdevbk_control_label,    $wpdevbk_help_block,
                                                $wpdevbk_width, $input_append = '',
                                                $exclude_items = array() , $default_item = 0) {
        
            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else  {                               $wpdevbk_value = $default_item; }
            if (isset($_REQUEST[$wpdevbk_id2]))   $wpdevbk_value2 = $_REQUEST[$wpdevbk_id2];
            else                                  $wpdevbk_value2 = '';

            $dates_interval = array(  1 => '1' . ' ' . __('day' ,'booking') ,
                                      2 => '2' . ' ' . __('days' ,'booking') ,
                                      3 => '3' . ' ' . __('days' ,'booking') ,
                                      4 => '4' . ' ' . __('days' ,'booking') ,
                                      5 => '5' . ' ' . __('days' ,'booking') ,
                                      6 => '6' . ' ' . __('days' ,'booking') ,
                                      7 => '1' . ' ' . __('week' ,'booking') ,
                                      14 => '2' . ' ' . __('weeks' ,'booking') ,
                                      30 => '1' . ' ' . __('month' ,'booking') ,
                                      60 => '2' . ' ' . __('months' ,'booking') ,
                                      90 => '3' . ' ' . __('months' ,'booking') ,
                                      183 => '6' . ' ' . __('months' ,'booking') ,
                                      365 => '1' . ' ' . __('Year' ,'booking')  );

            $filter_labels = array(
                                __('Current dates' ,'booking'),
                                __('Today' ,'booking'),
                                __('Previous dates' ,'booking'),
                                __('All dates' ,'booking'),
                                __('Next' ,'booking'),
                                __('Prior' ,'booking'),
                                '',
                                __('Check In - Tomorrow' ,'booking'),
                                __('Check Out - Tomorrow' ,'booking'),
                                __('Today check in/out' ,'booking')
                               );
            ?>
            <script type="text/javascript">
                function wpdevbk_days_selection_in_filter( primary_field, secondary_field, primary_value, secondary_value ) {

                    

                    if (primary_value == '0') {         // Actual  = '', ''
                        jQuery('#' + primary_field   ).val('0');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[0]); ?>' );
                        jQuery("input:radio[name='" + primary_field + "days_interval_Radios']").each(function(i) { this.checked = false; });
                    } else if (primary_value == '1') {  // Today
                        jQuery('#' + primary_field   ).val('1');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[1]); ?>' );
                        jQuery("input:radio[name='" + primary_field + "days_interval_Radios']").each(function(i) { this.checked = false; });
                    } else if (primary_value == '2') {  // Previous
                        jQuery('#' + primary_field   ).val('2');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[2]); ?>' );
                        jQuery("input:radio[name='" + primary_field + "days_interval_Radios']").each(function(i) { this.checked = false; });
                    } else if (primary_value == '3') { // All
                        jQuery('#' + primary_field   ).val('3');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[3]); ?>' );
                        jQuery("input:radio[name='" + primary_field + "days_interval_Radios']").each(function(i) { this.checked = false; });
                    } else if (primary_value == '4') { // Next
                        jQuery('#' + primary_field   ).val('4');
                        jQuery('#' + secondary_field ).val(secondary_value);
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[4]) ; ?> ' 
                                + jQuery('#' + primary_field + 'next'  ).find(":selected").text() );
                    } else if (primary_value == '5') { // Prior
                        jQuery('#' + primary_field   ).val('5');
                        jQuery('#' + secondary_field ).val(secondary_value);
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[5]) ; ?> ' 
                                + jQuery('#' + primary_field + 'prior'  ).find(":selected").text()  );
                    } else if (primary_value == '6') { // Fixed
                        jQuery('#' + primary_field   ).val(secondary_value[0]);
                        jQuery('#' + secondary_field ).val(secondary_value[1]);
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[6]) ; ?><strong>' 
                                + jQuery('#' + primary_field   + 'fixeddates'  ).val() + ' - '
                                + jQuery('#' + secondary_field + 'fixeddates'  ).val() + '</strong>'
                               );
                    } else if (primary_value == '7') {  // Check In - Today
                        jQuery('#' + primary_field   ).val('7');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[7]); ?>' );
                    } else if (primary_value == '8') {  // Check Out - Tomorrow
                        jQuery('#' + primary_field   ).val('8');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[8]); ?>' ); 
                    } else if (primary_value == '9') {  // Check Out - Tomorrow
                        jQuery('#' + primary_field   ).val('9');
                        jQuery('#' + secondary_field ).val('');
                        jQuery('#'+primary_field+'_selector .wpbc_selected_in_dropdown').html( '<?php echo esc_js($filter_labels[9]); ?>' );
                    }  
                    jQuery('#' + primary_field+ '_container').hide();
                }
                <?php if ( isset($_REQUEST[ $wpdevbk_id ]) ) { ?>
                    jQuery(document).ready( function(){
                        jQuery('#<?php echo $wpdevbk_id; ?>_container .button.button-primary').trigger( "click" );
                    });
                <?php } ?>
            
            </script>
          <div class="control-group" style="float:left;">
            <label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?></label>
            <div class="inline controls">
                <input type="hidden" value="<?php echo $wpdevbk_value; ?>"  id="<?php echo $wpdevbk_id; ?>"  name="<?php echo $wpdevbk_id; ?>" />
                <input type="hidden" value="<?php echo $wpdevbk_value2; ?>" id="<?php echo $wpdevbk_id2; ?>" name="<?php echo $wpdevbk_id2; ?>" />
                <div class="btn-group">
                    <a onclick="javascript:jQuery('#<?php echo $wpdevbk_id; ?>_container').show();" id="<?php echo $wpdevbk_id; ?>_selector" data-toggle="dropdown"  
                       class="button button-secondary dropdown-toggle" href="javascript:void(0)"><label class="label_in_filters"
                          ><?php echo $wpdevbk_help_block; ?>: </label>  <span class="wpbc_selected_in_dropdown"><?php
                    if ( isset($_REQUEST[ $wpdevbk_id ]) ) {
                        if ( $_REQUEST[ $wpdevbk_id ] == '0' ) echo $filter_labels[0];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '1' ) echo $filter_labels[1];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '2' ) echo $filter_labels[2];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '3' ) echo $filter_labels[3];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '4' ) echo $filter_labels[4];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '5' ) echo $filter_labels[5];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '7' ) echo $filter_labels[7];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '8' ) echo $filter_labels[8];
                        else if ( $_REQUEST[ $wpdevbk_id ] == '9' ) echo $filter_labels[9];
                        else echo $filter_labels[6];
                    } else {
                        echo $filter_labels[ $default_item ];
                    }
                    ?></span> &nbsp; <span class="caret"></span></a>
                    <ul class="dropdown-menu" style="display:none;" id="<?php echo $wpdevbk_id; ?>_container" >
                        <?php   if ( ! in_array(0, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '0' , '' );" href="javascript:void(0)"><?php echo $filter_labels[0]; ?></a></li>
                        <?php } if ( ! in_array(1, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '1' , '' );" href="javascript:void(0)"><?php echo $filter_labels[1]; ?></a></li>
                        <?php } if ( ! in_array(2, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '2' , '' );" href="javascript:void(0)"><?php echo $filter_labels[2]; ?></a></li>
                        <?php } if ( ! in_array(3, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '3' , '' );" href="javascript:void(0)"><?php echo $filter_labels[3]; ?></a></li>
                        <?php } ?>
                        <li class="divider"></li>
                        <?php if ( ! in_array(9, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '9' , '' );" href="javascript:void(0)"><?php echo $filter_labels[9]; ?></a></li>
                        <?php } if ( ! in_array(7, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '7' , '' );" href="javascript:void(0)"><?php echo $filter_labels[7]; ?></a></li>
                        <?php } if ( ! in_array(8, $exclude_items ) ) { ?>
                        <li><a onclick="javascript:wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '8' , '' );" href="javascript:void(0)"><?php echo $filter_labels[8]; ?></a></li>
                        <?php } ?>                        
                        <li class="divider"></li>
                        <?php   if ( ! in_array(4, $exclude_items ) ) { ?>
                        <li><div style="line-height: 2em;margin-left: 10px;"> 
                              <fieldset>
                                <input <?php if ( isset($_REQUEST[ $wpdevbk_id . 'days_interval_Radios']) ) if ( $_REQUEST[ $wpdevbk_id . 'days_interval_Radios'] == 'next' ) echo ' checked="CHECKED" ';  ?>
                                    type="radio" value="next" id="<?php echo $wpdevbk_id; ?>days_interval1" name="<?php echo $wpdevbk_id; ?>days_interval_Radios" 
                                    style="margin:0;vertical-align: baseline;" />
                                <label style="padding-left: 0 !important;" for="<?php echo $wpdevbk_id; ?>days_interval1"><?php _e('Next' ,'booking'); ?>: </label>                                
                              <div class="btn-group" style="float: right;margin-right: 10px;">  
                                <select class="span1" style="width:85px;" id="<?php echo $wpdevbk_id; ?>next" name="<?php echo $wpdevbk_id; ?>next"
                                        onfocus="javascript:jQuery('#<?php echo $wpdevbk_id; ?>days_interval1').prop('checked', true);"
                                        >
                                  <?php
                                  foreach ($dates_interval as $key=>$value) {
                                    if ($value != 'divider') {
                                        ?><option <?php if ( isset($_REQUEST[ $wpdevbk_id . 'next']) ) if ( $_REQUEST[ $wpdevbk_id . 'next'] == $key ) echo ' selected="SELECTED" '; ?>
                                            value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                                    }
                                  }
                                  ?>
                                </select>
                              </div>
                              </fieldset>  
                            </div></li>
                        <?php } if ( ! in_array(5, $exclude_items ) ) { ?>
                        <li><div style="line-height: 2em;margin-left: 10px;"> 
                            <fieldset>
                               <input  <?php if ( isset($_REQUEST[ $wpdevbk_id . 'days_interval_Radios']) ) if ( $_REQUEST[ $wpdevbk_id . 'days_interval_Radios'] == 'prior' ) echo ' checked="CHECKED" ';  ?>
                                    type="radio" value="prior" id="<?php echo $wpdevbk_id; ?>days_interval2" name="<?php echo $wpdevbk_id; ?>days_interval_Radios" 
                                    style="margin:0;vertical-align: baseline;" />
                                <label style="padding-left: 0 !important;" for="<?php echo $wpdevbk_id; ?>days_interval2"><?php _e('Prior' ,'booking'); ?>: </label>
                            <div class="btn-group" style="float: right;margin-right: 10px;">  
                                <select class="span1" style="width:85px;" id="<?php echo $wpdevbk_id; ?>prior" name="<?php echo $wpdevbk_id; ?>prior"
                                        onfocus="javascript:jQuery('#<?php echo $wpdevbk_id; ?>days_interval2').prop('checked', true);"
                                        >
                                  <?php
                                  foreach ($dates_interval as $key=>$value) {
                                    if ($value != 'divider') {
                                        ?><option <?php if ( isset($_REQUEST[ $wpdevbk_id . 'prior']) ) if ( $_REQUEST[ $wpdevbk_id . 'prior'] == '-'.$key ) echo ' selected="SELECTED" '; ?>
                                            value="-<?php echo $key; ?>"><?php echo $value; ?></option><?php
                                    }
                                  }
                                  ?>
                                </select>
                            </div>
                            </fieldset>                                
                            </div></li>
                        <?php } if ( ! in_array(6, $exclude_items ) ) { ?>
                        <li>  
                            <fieldset>
                            <input  <?php if ( isset($_REQUEST[ $wpdevbk_id . 'days_interval_Radios']) ) if ( $_REQUEST[ $wpdevbk_id . 'days_interval_Radios'] == 'fixed' ) echo ' checked="CHECKED" ';  ?>
                                    type="radio"  value="fixed" 
                                    id="<?php echo $wpdevbk_id; ?>days_interval3" 
                                    name="<?php echo $wpdevbk_id; ?>days_interval_Radios" 
                                    style="margin:0 0 0 10px;vertical-align: middle;">                                                        
                                <label class="check_in_out" for="<?php echo $wpdevbk_id; ?>fixeddates"><?php _e('Check-in' ,'booking'); ?>:</label>
                                <div style="margin-left:30px;margin-bottom: 10px;">
                                    <div class="input-append">
                                        <input style="width:100px;" type="text" class="span2<?php echo $wpdevbk_width; ?> wpdevbk-filters-section-calendar"  placeholder="<?php echo '2012-02-25'; ?>"
                                               onfocus="javascript:jQuery('#<?php echo $wpdevbk_id; ?>days_interval3').prop('checked', true);"
                                               value="<?php if ( isset($_REQUEST[ $wpdevbk_id . 'fixeddates']) )  echo $_REQUEST[ $wpdevbk_id . 'fixeddates']; ?>" 
                                               id="<?php echo $wpdevbk_id; ?>fixeddates"  name="<?php echo $wpdevbk_id; ?>fixeddates" />
                                        <span class="add-on"><?php echo $input_append ?></span>
                                    </div>
                                    <div class="clear"></div>
                                    <label class="check_in_out" style="margin-top: 10px;" for="<?php echo $wpdevbk_id2; ?>fixeddates"><?php _e('Check-out' ,'booking'); ?>:</label>
                                    <div class="input-append">
                                        <input style="width:100px;" type="text" class="span2<?php echo $wpdevbk_width; ?> wpdevbk-filters-section-calendar"  placeholder="<?php echo '2012-02-25'; ?>"
                                               onfocus="javascript:jQuery('#<?php echo $wpdevbk_id; ?>days_interval3').prop('checked', true);"
                                               value="<?php if ( isset($_REQUEST[ $wpdevbk_id2 . 'fixeddates']) )  echo $_REQUEST[ $wpdevbk_id2 . 'fixeddates']; ?>"  
                                               id="<?php echo $wpdevbk_id2; ?>fixeddates"  name="<?php echo $wpdevbk_id2; ?>fixeddates" />
                                        <span class="add-on"><?php echo $input_append ?></span>
                                    </div>
                                </span>
                            </fieldset>
                            <div class="clear"></div>
                        </li>
                        <?php }  ?>
                        <li class="divider"></li>
                        <li style="margin: 0;padding: 0 5px;text-align: right;">
                            <div class="btn-toolbar" style="margin:0px;width:170px;">
                            <div class="btn-group">
                                <button type="button" class="button button-primary"
                                    onclick="javascript:
                                    var rad_val = jQuery('input:radio[name=<?php echo $wpdevbk_id; ?>days_interval_Radios]:checked').val();
                                    if (rad_val == 'next') wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '4' , jQuery('#<?php echo $wpdevbk_id; ?>next').val() );
                                    if (rad_val == 'prior') wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '5' , jQuery('#<?php echo $wpdevbk_id; ?>prior').val() );
                                    if (rad_val == 'fixed') wpdevbk_days_selection_in_filter( '<?php echo $wpdevbk_id; ?>', '<?php echo $wpdevbk_id2; ?>', '6' , [ jQuery('#<?php echo $wpdevbk_id; ?>fixeddates').val(), jQuery('#<?php echo $wpdevbk_id2; ?>fixeddates').val()  ]  );
                                "    ><?php _e('Apply' ,'booking'); ?></button>                            
                                <button type="button" class="button button-secondary"
                                    onclick="javascript: jQuery('#<?php echo $wpdevbk_id; ?>_container').hide();"
                                ><?php _e('Close' ,'booking'); ?></button>
                              </div>
                            </div>
                        </li>
                    </ul>
 
                </div>
              <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
            </div>
          </div>
        <?php
    }


    function wpdevbk_selection_and_custom_text_for_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block, $wpdevbk_default_value = '') {

            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else                                  $wpdevbk_value = $wpdevbk_default_value;
            $wpdevbk_selector_default = array_search($wpdevbk_value, $wpdevbk_selectors);
            if ($wpdevbk_selector_default === false) {
                    $wpdevbk_selector_default = $wpdevbk_value;//key($wpdevbk_selectors);
                    $wpdevbk_selector_default_value = $wpdevbk_value;//current($wpdevbk_selectors);
            } else $wpdevbk_selector_default_value = $wpdevbk_value;
        ?>
          <div class="control-group" style="float:left;">
            <label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?></label>
            <div class="inline controls">
                <div class="btn-group">
                  <a onclick="javascript:jQuery('#<?php echo $wpdevbk_id; ?>_container').show();" id="<?php echo $wpdevbk_id;?>_selector" class="button button-secondary dropdown-toggle"  href="javascript:void(0)" data-toggle="dropdown"  ><label class="label_in_filters"
                          ><?php echo $wpdevbk_help_block; ?>: </label> <span class="wpbc_selected_in_dropdown"><?php echo $wpdevbk_selector_default; ?></span>&nbsp; <span class="caret"></span></a>
                  <ul class="dropdown-menu"  id="<?php echo $wpdevbk_id; ?>_container"  style="display:none;"  >
                      <?php
                      foreach ($wpdevbk_selectors as $key=>$value) {
                        if ($value != 'divider') {
                          ?><li><a href="javascript:void(0)" onclick="javascript:showSelectedInDropdown('<?php echo $wpdevbk_id; ?>', jQuery(this).html(), '<?php echo $value; ?>');" ><?php echo $key; ?></a></li><?php
                        } else { ?><li class="divider"></li><?php }
                      } ?>
                    <li class="divider"></li>
                    <li style="margin: 0;padding: 0 5px 0 15px;">
                        <div><?php _e('Custom' ,'booking'); ?>: </div>
                        <input style="width:150px;margin:5px 0; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px;" type="text"  placeholder=""
                                   value="<?php $pos = strpos($wpdevbk_value, 'group_'); if (( $pos === false ) && ($wpdevbk_value !== 'all'))  echo $wpdevbk_value; ?>"
                                   id="<?php echo $wpdevbk_id; ?>custom"  name="<?php echo $wpdevbk_id; ?>custom" />
                    </li>

                    <li class="divider"></li>
                    <li style="margin: 0;padding: 0 5px;text-align: right;">
                        <div class="btn-toolbar" style="margin:0px;">
                        <div class="btn-group">
                            <button type="button" class="button button-primary"
                                onclick="javascript:
                                var custom_val = jQuery('#<?php echo $wpdevbk_id; ?>custom').val();
                                if (custom_val != '') {
                                    
                                    showSelectedInDropdown('<?php echo $wpdevbk_id; ?>', custom_val, custom_val);
                                    //jQuery('#<?php echo $wpdevbk_id; ?>').val( custom_val );
                                    //jQuery('#<?php echo $wpdevbk_id;?>_selector').html( custom_val + ' &nbsp; <span class=&quot;caret&quot;></span>');
                                }
                                jQuery('#<?php echo $wpdevbk_id; ?>_container').hide();
                            "    ><?php _e('Apply' ,'booking'); ?></button>
                        
                            <button type="button" class="button button-secondary"
                                onclick="javascript: jQuery('#<?php echo $wpdevbk_id; ?>_container').hide();"
                            ><?php _e('Close' ,'booking'); ?></button>
                          </div>
                        </div>
                    </li>
                  </ul>
                  <input type="hidden" value="<?php echo $wpdevbk_selector_default_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />
                </div>
              <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
            </div>
          </div>
        <?php
    }




    function wpdevbk_date_selection_for_navigation( $wpdevbk_id, $bk_admin_url, $scroll_titles, $scroll_params ) {
        
          $wpdevbk_width =           'span2 wpdevbk-filters-section-calendar';
          $input_append    =            '<i class="icon-calendar"></i>' ;        
          
         ?>  <a style="border-radius:0px;-webkit-border-radius:0px;-moz-border-radius:0px;"
               onclick="javascript:jQuery('#<?php echo $wpdevbk_id; ?>_container').show();" 
               id="<?php echo $wpdevbk_id; ?>_selector" 
               data-toggle="dropdown"  
               class="button button-secondary dropdown-toggle tooltip_top" href="javascript:void(0)"
               data-original-title="<?php echo _e('Custom' ,'booking') ?>"  rel="tooltip" 
            ><i class="icon-screenshot"></i> &nbsp; <span class="caret" style="border-top-color: #333;"></span></a>

            <ul class="dropdown-menu" style="display:none; margin:0 0 0 70px;" id="<?php echo $wpdevbk_id; ?>_container" >
                <li><a onclick="javascript:jQuery('#<?php echo $wpdevbk_id; ?>_container').hide();" 
                       href="<?php echo $bk_admin_url .$scroll_params[2]; ?>"><?php echo $scroll_titles[2]; ?></a></li>
                <li class="divider"></li>
                <li style="padding-left:15px;">    
                    <label style="color: #555555;font-size: 12px;"><?php _e('Start Date' ,'booking'); ?>:</label>
                    <div class="input-append">
                        <input style="width:100px;" type="text" class="span2<?php echo $wpdevbk_width; ?> wpdevbk-filters-section-calendar"  placeholder="<?php echo '2012-02-25'; ?>"
                               value=""  
                               id="<?php echo $wpdevbk_id; ?>currentdate"  
                               name="<?php echo $wpdevbk_id; ?>currentdate" />
                        <span class="add-on"><?php echo $input_append ?></span>
                    </div>
                </li>

                <li class="divider"></li>

                <li style="margin: 0;padding: 0 5px;text-align: right;">
                    <div class="btn-toolbar" style="margin:0px;width:170px;text-align: right;">
                    <div class="btn-group">
                        <button type="button" class="button button-primary"
                            onclick="javascript:jQuery('#<?php echo $wpdevbk_id; ?>_container').hide();
                            window.location.href='<?php echo $bk_admin_url . '&scroll_start_date=' ; ?>'+ jQuery('#<?php echo $wpdevbk_id; ?>currentdate').val();"                                    
                            ><?php _e('Apply' ,'booking'); ?></button>
                    
                        <button type="button" class="button button-secondary"
                            onclick="javascript: jQuery('#<?php echo $wpdevbk_id; ?>_container').hide();"
                        ><?php _e('Close' ,'booking'); ?></button>
                      </div>
                    </div>
                </li>
            </ul><?php
    }
    // </editor-fold>

    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  T O O L B A R   Shared Buttons    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Show     V i e w    M o d e      Buttons in the Toolbar
    function wpdevbk_booking_view_mode_buttons() {        
        $selected_view_mode = $_REQUEST['view_mode'];
        $bk_admin_url = get_params_in_url( array('view_mode','wh_booking_id','page_num') );
        $bk_admin_timeline_url = get_params_in_url( array(), array( 'page', 'tab', 'tab_cvm', 'wh_booking_type', 'scroll_start_date', 'scroll_month', 'view_days_num'  ) ); //FixIn: 6.0.1.14

        ?><div id="booking-listing-view-mode-buttons" class="btn-group btn-group-vertical" data-toggle="buttons-radio">
                <a id="btn_vm_listing" rel="tooltip" data-original-title="<?php  _e('Booking Listing' ,'booking'); ?>"  
                   class="tooltip_top button button-secondary" <?php if ($selected_view_mode=='vm_listing') { echo ' data-toggle="button" ' ; } ?>               
                   href="<?php echo $bk_admin_url . '&view_mode=vm_listing'; ?>" onclick="javascript:;" 
                   ><i class="icon-align-justify"></i></a>
                <a id="btn_vm_calendar" rel="tooltip" data-original-title="<?php  _e('Calendar Overview' ,'booking'); ?>"  
                   class="tooltip_bottom  button button-secondary" <?php if ($selected_view_mode=='vm_calendar') { echo ' data-toggle="button" ' ; } ?>                    
                   href="<?php echo $bk_admin_timeline_url . '&view_mode=vm_calendar'; ?>" onclick="javascript:;"  
                   ><i class="icon-calendar"></i></a>
            </div>
            <script type="text/javascript">
                jQuery('#booking-listing-view-mode-buttons .btn').button();
                jQuery('#btn_<?php echo $selected_view_mode; ?>').button('toggle');
                <?php if ($selected_view_mode=='vm_calendar') { ?>
                    jQuery('#wpdev-booking-general h2:first').html('<?php _e('Booking Calendar - Overview' ,'booking'); ?>');
                <?php } ?>
            </script><?php
    }

    // Show Help Menu buttons in the top Toolbar
    function wpdevbk_show_help_dropdown_menu_in_top_menu_line() {

        $title = __('Help' ,'booking'); 
        ?>
        <span class="dropdown pull-right">
            <a href="javascript:void(0)" data-toggle="dropdown" class="dropdown-toggle nav-tab "
               ><i class="icon-question-sign"></i> <?php  echo $title; ?> <b class="caret" style="border-top-color: #333333 !important;"></b></a>
          <ul class="dropdown-menu" id="menu1" style="right:0px; left:auto;">
            <li><a href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpbc-about' ), 'index.php' ) ) ); ?>"><?php _e('About Booking Calendar' ,'booking'); ?></a></li>
            <li class="divider"></li>
            <li><a href="http://wpbookingcalendar.com/help/" target="_blank"><?php _e('Help' ,'booking'); ?></a></li>
            <li><a href="http://wpbookingcalendar.com/faq/" target="_blank"><?php _e('FAQ' ,'booking'); ?></a></li>
            <li><a href="http://wpbookingcalendar.com/support/" target="_blank"><?php _e('Technical Support' ,'booking'); ?></a></li>
            <li class="divider"></li>
            <li><a style="font-size: 1.1em;font-weight: bold;" href="<?php echo wpbc_up_link(); ?>" target="_blank"><?php if ( wpbc_get_ver_sufix() == '' ) { _e('Upgrade Now' ,'booking'); } else { _e('Upgrade Now' ,'booking'); } ?></a></li>
          </ul>
        </span>
        <?php
    }



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Bookings listing    E N G I N E        ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get Default params or from Request
    function wpdev_get_args_from_request_in_bk_listing(){
//debuge($_REQUEST);        
        $num_per_page_check = get_bk_option( 'bookings_num_per_page');
        if (empty( $num_per_page_check)) {
            $num_per_page_check = '10';
            update_bk_option( 'bookings_num_per_page', $num_per_page_check );
        }
        $args = array(
		'wh_booking_type' =>    (isset($_REQUEST['wh_booking_type'])) ? wpbc_clean_parameter( $_REQUEST['wh_booking_type'] ):'',
                'wh_approved' =>        (isset($_REQUEST['wh_approved'])) ? wpbc_clean_parameter( $_REQUEST['wh_approved'] ):'',
		'wh_booking_id' =>      (isset($_REQUEST['wh_booking_id'])) ? wpbc_clean_parameter( $_REQUEST['wh_booking_id'] ):'',
                'wh_is_new' =>          (isset($_REQUEST['wh_is_new'])) ? wpbc_clean_parameter( $_REQUEST['wh_is_new'] ):'',
		'wh_pay_status' =>      (isset($_REQUEST['wh_pay_status'])) ? wpbc_clean_parameter( $_REQUEST['wh_pay_status'] ):'',
                'wh_keyword' =>         (isset($_REQUEST['wh_keyword'])) ? wpbc_clean_parameter( $_REQUEST['wh_keyword'] ):'',
		'wh_booking_date' =>    (isset($_REQUEST['wh_booking_date'])) ? wpbc_clean_parameter( $_REQUEST['wh_booking_date'] ):'',
                'wh_booking_date2' =>   (isset($_REQUEST['wh_booking_date2'])) ? wpbc_clean_parameter( $_REQUEST['wh_booking_date2'] ):'',
		'wh_modification_date' =>  (isset($_REQUEST['wh_modification_date'])) ? wpbc_clean_parameter( $_REQUEST['wh_modification_date'] ):'',
                'wh_modification_date2' => (isset($_REQUEST['wh_modification_date2'])) ? wpbc_clean_parameter( $_REQUEST['wh_modification_date2'] ):'',
		'wh_cost' =>            (isset($_REQUEST['wh_cost'])) ? wpbc_clean_parameter( $_REQUEST['wh_cost'] ):'',
                'wh_cost2' =>           (isset($_REQUEST['wh_cost2'])) ? wpbc_clean_parameter( $_REQUEST['wh_cost2'] ):'',
		'or_sort' =>            (isset($_REQUEST['or_sort'])) ? wpbc_clean_parameter( $_REQUEST['or_sort'] ):get_bk_option( 'booking_sort_order'),
		'page_num' =>           (isset($_REQUEST['page_num'])) ? wpbc_clean_parameter( $_REQUEST['page_num'] ):'1',
                'page_items_count' =>   (isset($_REQUEST['page_items_count'])) ? wpbc_clean_parameter( $_REQUEST['page_items_count'] ):$num_per_page_check,
	);
//debuge($args, $_REQUEST['wh_booking_type'] );
        return $args;
    }
    
    // Get Default params or from Request -- for admin    C a l e n d a r   V i e w    M o d e
    function wpdev_get_args_from_request_in_bk_overview_in_calendar(){

        // Reset
        $start_year = date("Y");            //2012
        $start_month = date("m");           //09
        $start_day = 1;//date("d");//1;     //31
        if (! empty($_REQUEST['scroll_start_date'])) {   // scroll_start_date=2013-07-01
            $scroll_start_date= explode('-',$_REQUEST['scroll_start_date']);
            
            $start_year     = $scroll_start_date[0];            //2012
            $start_month    = $scroll_start_date[1];           //09
            $start_day      = $scroll_start_date[2];    //date("d");//1;     //31
        } 
        
        $scroll_day     = 0;
        $scroll_month   = 0;        

        if (isset($_REQUEST['view_days_num']))  $view_days_num = $_REQUEST['view_days_num'];
        else                                    $view_days_num = get_bk_option( 'booking_view_days_num');
        
        if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
                $is_show_resources_matrix = true;
        else    $is_show_resources_matrix = false;

        if ($is_show_resources_matrix) {
            
            switch ($view_days_num) {
                
                case '1':
                    if (empty($_REQUEST['scroll_start_date']))  $start_day = date("d");
                    
                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                    
                    $real_date = mktime(0, 0, 0, $start_month,    ($start_day + $scroll_day) ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-11-29';

                    $real_date = mktime(0, 0, 0, $start_month,    ($start_day + 0 +$scroll_day) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2013-12-3';                    
                    break;
                    
                case '7':
                    if (empty($_REQUEST['scroll_start_date']))  $start_day = date("d");
                    $start_week_day_num = date("w");
                    $start_day_weeek  = get_bk_option( 'booking_start_day_weeek' ); //[0]:Sun .. [6]:Sut
                    if ($start_week_day_num != $start_day_weeek) {
                        for ($d_inc = 1; $d_inc < 8; $d_inc++) {                // Just get week  back
                            $real_date = mktime(0, 0, 0, $start_month, ($start_day-$d_inc ) , $start_year);
                            $start_week_day_num = date("w", $real_date);
                            if ($start_week_day_num == $start_day_weeek) {
                                $start_day = date("d", $real_date);
                                $start_year = date("Y", $real_date);
                                $start_month = date("m", $real_date);
                                $d_inc=9;
                            }
                        }
                    }
                    
                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];

                    $real_date = mktime(0, 0, 0, $start_month,    ( $start_day +$scroll_day) ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, $start_month,    ($start_day+7+$scroll_day) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2012-12-7';                    
                    break;
                    
                case '30':
                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];
                    
                    $real_date = mktime(0, 0, 0, ($start_month+$scroll_month),    ( $start_day ) ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, ($start_month+1+$scroll_month),    ($start_day-1) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2012-12-31';                    
                    break;
                    
                case '60':
                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];

                    $real_date = mktime(0, 0, 0, ($start_month+$scroll_month),    ( $start_day ) ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, ($start_month+2+$scroll_month),   ($start_day-1) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2013-02-31';                    
                    break;
                    
////////////////////////////////////////////////////////////////////////////////
                default:  // 30 - default
                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];                    
                    
                    $real_date = mktime(0, 0, 0, ($start_month+$scroll_month),    ( $start_day ) ,  $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, ($start_month+1+$scroll_month),    ($start_day-1) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2012-12-31';
                    break;
            }
            
        } else {   // Single resource
            
            switch ($view_days_num) {
                case '90':

                    if (empty($_REQUEST['scroll_start_date'])) $start_day = date("d");
                    $start_week_day_num = date("w");
                    $start_day_weeek  = get_bk_option( 'booking_start_day_weeek' ); //[0]:Sun .. [6]:Sut

                    if ($start_week_day_num != $start_day_weeek) {
                        for ($d_inc = 1; $d_inc < 8; $d_inc++) {                // Just get week  back
                            $real_date = mktime(0, 0, 0, $start_month, ($start_day-$d_inc ) , $start_year);
                            $start_week_day_num = date("w", $real_date);
                            if ($start_week_day_num == $start_day_weeek) {
                                $start_day = date("d", $real_date);
                                $start_year = date("Y", $real_date);
                                $start_month = date("m", $real_date);
                                $d_inc=9;
                                //break;
                            }
                        }
                    }

                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];

                    $real_date = mktime(0, 0, 0, $start_month,    ( $start_day +$scroll_day) ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, $start_month,    ($start_day+7*12+7+$scroll_day) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2013-12-31';
                    break;

                case '30':
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = date("d");

                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];

                    $real_date = mktime(0, 0, 0, $start_month,    ( $start_day +$scroll_day) ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, $start_month,    ($start_day+31+$scroll_day) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2013-12-31';
                    break;

                default:  // 365

                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];
                    else $scroll_month = 0;

                    $real_date = mktime(0, 0, 0, ($start_month+$scroll_month),     $start_day ,     $start_year);
                    $wh_booking_date  = date("Y-m-d", $real_date);                          // '2012-12-01';

                    $real_date = mktime(0, 0, 0, ($start_month+$scroll_month+13), ($start_day-1) ,  $start_year);
                    $wh_booking_date2 = date("Y-m-d", $real_date);                          // '2013-12-31';

                    break;
            }
        }
        
        
        $or_sort = get_bk_option( 'booking_sort_order') ;        

        $args = array(
		'wh_booking_type' =>    (isset($_REQUEST['wh_booking_type']))?$_REQUEST['wh_booking_type']:'',
                'wh_approved' =>        '',                                     // Any
		'wh_booking_id' =>      '',                                     // Any
                'wh_is_new' =>          '',         //(isset($_REQUEST['wh_is_new']))?$_REQUEST['wh_is_new']:'',                  // ?
		'wh_pay_status' =>      'all',      //(isset($_REQUEST['wh_pay_status']))?$_REQUEST['wh_pay_status']:'',          // ?
                'wh_keyword' =>         '',         //(isset($_REQUEST['wh_keyword']))?$_REQUEST['wh_keyword']:'',                // ?
		'wh_booking_date' =>    $wh_booking_date,
                'wh_booking_date2' =>   $wh_booking_date2, 
		'wh_modification_date' =>  '3',     //(isset($_REQUEST['wh_modification_date']))?$_REQUEST['wh_modification_date']:'',     // ?
                'wh_modification_date2' => '',      //(isset($_REQUEST['wh_modification_date2']))?$_REQUEST['wh_modification_date2']:'',   // ?
		'wh_cost' =>            '',         //(isset($_REQUEST['wh_cost']))?$_REQUEST['wh_cost']:'',                      // ?
                'wh_cost2' =>           '',         //(isset($_REQUEST['wh_cost2']))?$_REQUEST['wh_cost2']:'',                    // ?
		'or_sort' =>            $or_sort,
		'page_num' =>           '1',
                'page_items_count' =>   '100000'
	);

        return $args;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // S Q L    B o o k i n g    L i s t i n g
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpdev_sql_get_booking_listing( $args ){
	global $wpdb;
        $num_per_page_check = get_bk_option( 'bookings_num_per_page');
        if (empty( $num_per_page_check)) {
            $num_per_page_check = '10';
            update_bk_option( 'bookings_num_per_page', $num_per_page_check );
        }

        ////////////////////////////////////////////////////////////////////////
        // CONSTANTS
        ////////////////////////////////////////////////////////////////////////
	$defaults = array(
		'wh_booking_type' => '',    'wh_approved' => '',
		'wh_booking_id' => '',      'wh_is_new' => '',
		'wh_pay_status' => '',      'wh_keyword' => '',
		'wh_booking_date' => '',        'wh_booking_date2' => '',
		'wh_modification_date' => '',   'wh_modification_date2' => '',
		'wh_cost' => '',            'wh_cost2' => '',
		'or_sort' => get_bk_option( 'booking_sort_order'),
		'page_num' => '1',
                'page_items_count' => $num_per_page_check
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

        $page_start = ( $page_num - 1 ) * $page_items_count ;


        $posible_sorts = array('booking_id_asc','sort_date','sort_date_asc','booking_type','booking_type_asc','cost','cost_asc');
        if ( ($or_sort == '') || ($or_sort == 'id') || (! in_array($or_sort, $posible_sorts) ) ) $or_sort = 'booking_id';

        ////////////////////////////////////////////////////////////////////////
        // S Q L
        ////////////////////////////////////////////////////////////////////////
        // GET ONLY ROWS OF THE     B o o k i n g s    - So we can limit the requests
        $sql_start_select = " SELECT * " ;
        $sql_start_count  = " SELECT COUNT(*) as count" ;
        $sql = " FROM {$wpdb->prefix}booking as bk" ;
        $sql_where = " WHERE " .                                                      // Date (single) connection (Its required for the correct Pages in SQL: LIMIT Keyword)
               "       EXISTS (
                                SELECT *
                                FROM {$wpdb->prefix}bookingdates as dt
                                WHERE  bk.booking_id = dt.booking_id " ;
                if ($wh_approved !== '')
                    $sql_where.=           " AND approved = $wh_approved  " ;            // Approved or Pending

            $sql_where.= set_dates_filter_for_sql($wh_booking_date, $wh_booking_date2) ;

            $sql_where.=   "   ) " ;

        if ( $wh_is_new !== '' )    $sql_where .= " AND  bk.is_new = " . $wh_is_new . " ";

            // P
            $sql_where .= apply_bk_filter('get_bklist_sql_keyword', ''  , $wh_keyword );

        $sql_where.= set_creation_dates_filter_for_sql($wh_modification_date, $wh_modification_date2 ) ;

            // BS
            $sql_where .= apply_bk_filter('get_bklist_sql_paystatus', ''  , $wh_pay_status );
            $sql_where .= apply_bk_filter('get_bklist_sql_cost', ''  , $wh_cost, $wh_cost2 );

            // P  || BL
            $sql_where .= apply_bk_filter('get_bklist_sql_resources', ''  , $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 );

        if (! empty ($wh_booking_id) ) {
            if ( strpos($wh_booking_id, ',') !== false)
                $sql_where = " WHERE bk.booking_id IN (" . $wh_booking_id . ") ";
            else
                $sql_where = " WHERE bk.booking_id = " . $wh_booking_id . " ";
        }

        if (strpos($or_sort, '_asc') !== false) {                               // Order
               $or_sort = str_replace('_asc', '', $or_sort);
               $sql_order = " ORDER BY " .$or_sort ." ASC ";                                          
        } else $sql_order = " ORDER BY " .$or_sort ." DESC ";                                          // Order


        $sql_limit = $wpdb->prepare( " LIMIT %d, %d ", $page_start, $page_items_count ) ;
        return array( $sql_start_count, $sql_start_select , $sql , $sql_where , $sql_order , $sql_limit );        
    }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  SQL for the dates filtering      ///////////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // SQL - WHERE -  D a t e s  (BK)
        function set_dates_filter_for_sql($wh_booking_date, $wh_booking_date2, $pref = 'dt.') {
            
                $sql_where= '';
                if ($pref == 'dt.')  { $and_pre = ' AND '; $and_suf = ''; }
                else                 { $and_pre = ''; $and_suf = ' AND '; }

                                                                                    // Actual
                if (  ( ( $wh_booking_date  === '' ) && ( $wh_booking_date2  === '' ) ) || ($wh_booking_date  === '0') ) {
                    $sql_where =               $and_pre."( ".$pref."booking_date >= ( CURDATE() - INTERVAL 1 DAY ) ) ".$and_suf ;

                } else  if ($wh_booking_date  === '1') {                            // Today
                    $sql_where  =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."booking_date >= ( CURDATE() - INTERVAL 1 DAY ) ) ".$and_suf ;


                } else if ($wh_booking_date  === '2') {                             // Previous
                    $sql_where =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;

                } else if ($wh_booking_date  === '3') {                             // All
                    $sql_where =  '';

                } else if ($wh_booking_date  === '4') {                             // Next
                    $sql_where  =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL ". $wh_booking_date2 . " DAY ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."booking_date >= ( CURDATE() - INTERVAL 1 DAY ) ) ".$and_suf ;

                } else if ($wh_booking_date  === '5') {                             // Prior
                    $wh_booking_date2 = str_replace('-', '', $wh_booking_date2);
                    $sql_where  =               $and_pre."( ".$pref."booking_date >= ( CURDATE() - INTERVAL ". $wh_booking_date2 . " DAY ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;

                } else  if ($wh_booking_date  === '7') {                            // Check In date - Today/Tomorrow
//                    $sql_where  =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL '23:59:59' HOUR_SECOND ) ) ".$and_suf ;
//                    $sql_where .=               $and_pre."( ".$pref."booking_date >= ( CURDATE() ) ) ".$and_suf ;
                      $sql_where  =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL '1 23:59:59' DAY_SECOND ) ) ".$and_suf ;
                      $sql_where .=               $and_pre."( ".$pref."booking_date >= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;
                            
                } else  if ($wh_booking_date  === '8') {                            // Check Out date - Tomorrow
                    $sql_where  =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL '1 23:59:59' DAY_SECOND ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."booking_date >= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;
                    
                } else  if ($wh_booking_date  === '9') {                            // Check Out date - Tomorrow
                    $sql_where  =               $and_pre."( ".$pref."booking_date <= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."booking_date >= ( CURDATE() - INTERVAL 1 DAY ) ) ".$and_suf ;
                    
                } else {                                                            // Fixed

                    if ( $wh_booking_date  !== '' )
                        if ( strpos($wh_booking_date,':')===false ) // we are do not have the time in this date, so  set it
                             $sql_where.= $and_pre."( ".$pref."booking_date >= '" . $wh_booking_date . " 00:00:00' ) ".$and_suf;
                        else $sql_where.= $and_pre."( ".$pref."booking_date >= '" . $wh_booking_date . "' ) ".$and_suf;

                    if ( $wh_booking_date2  !== '' )
                        if ( strpos($wh_booking_date2,':')===false ) // we are do not have the time in this date, so  set it
                             $sql_where.=               $and_pre."( ".$pref."booking_date <= '" . $wh_booking_date2 . " 23:59:59' ) ".$and_suf;
                        else $sql_where.=               $and_pre."( ".$pref."booking_date <= '" . $wh_booking_date2 . "' ) ".$and_suf;
                }
                return $sql_where;
        }

        // SQL - WHERE -  D a t e s  (Modification)
        function set_creation_dates_filter_for_sql($wh_modification_date, $wh_modification_date2, $pref = 'bk.') {
                $sql_where= '';
                if ($pref == 'bk.')  { $and_pre = ' AND '; $and_suf = ''; }
                else                 { $and_pre = ''; $and_suf = ' AND '; }

                if ($wh_modification_date  === '1') {                               // Today
                    $sql_where  =               $and_pre."( ".$pref."modification_date <= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."modification_date >= ( CURDATE() - INTERVAL 1 DAY ) ) ".$and_suf ;

                } else if ($wh_modification_date  === '3') {                        // All
                    $sql_where =  '';

                } else if ($wh_modification_date  === '5') {                        // Prior
                    $wh_modification_date2 = str_replace('-', '', $wh_modification_date2);
                    $sql_where  =               $and_pre."( ".$pref."modification_date >= ( CURDATE() - INTERVAL ". $wh_modification_date2 . " DAY ) ) ".$and_suf ;
                    $sql_where .=               $and_pre."( ".$pref."modification_date <= ( CURDATE() + INTERVAL 1 DAY ) ) ".$and_suf ;

                } else {                                                            // Fixed

                    if ( $wh_modification_date  !== '' )
                        $sql_where.=               $and_pre."( ".$pref."modification_date >= '" . $wh_modification_date . "' ) ".$and_suf;

                    if ( $wh_modification_date2  !== '' )
                        $sql_where.=               $and_pre."( ".$pref."modification_date <= '" . $wh_modification_date2 . "' ) ".$and_suf;
                }
                return $sql_where;
        }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // E n g i n e     B o o k i n g    L i s t i n g
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpdev_get_bk_listing_structure_engine( $args ){
        global $wpdb;
///debuge($_REQUEST);
        $sql_boking_listing = wpdev_sql_get_booking_listing( $args );
//debuge($args, $sql_boking_listing);
        $sql_start_count    = $sql_boking_listing[0];
        $sql_start_select   = $sql_boking_listing[1];
        $sql       = $sql_boking_listing[2];
        $sql_where = $sql_boking_listing[3];
        $sql_order = $sql_boking_listing[4];
        $sql_limit = $sql_boking_listing[5];

        $num_per_page_check = get_bk_option( 'bookings_num_per_page') ;
        if (empty( $num_per_page_check)) {
            $num_per_page_check = '10';
        }
	$defaults = array(
		'wh_booking_type' => '',    'wh_approved' => '',
		'wh_booking_id' => '',      'wh_is_new' => '',
		'wh_pay_status' => '',      'wh_keyword' => '',
		'wh_booking_date' => '',        'wh_booking_date2' => '',
		'wh_modification_date' => '',   'wh_modification_date2' => '',
		'wh_cost' => '',            'wh_cost2' => '',
		'or_sort' => get_bk_option( 'booking_sort_order'),
		'page_num' => '1',
                'page_items_count' => $num_per_page_check
	);

	$r = wp_parse_args( $args, $defaults );
	extract( $r, EXTR_SKIP );

        $page_start = ( $page_num - 1 ) * $page_items_count ;
//debuge($sql_start_select . $sql . $sql_where . $sql_order . $sql_limit);
//debuge($sql_start_select . $sql . $sql_where . $sql_order . $sql_limit);
        // Get Bookings Array
        $bookings_res = $wpdb->get_results( $sql_start_select . $sql . $sql_where . $sql_order . $sql_limit );

        // Get Number of booking for the pages
        $bookings_count = $wpdb->get_results( $sql_start_count . $sql . $sql_where   );

        // Get NUMBER of Bookings
        if (count($bookings_count)>0)   $bookings_count = $bookings_count[0]->count ;
        else                            $bookings_count = 0;

        $booking_types = apply_bk_filter('wpdebk_get_keyed_all_bk_resources', array() );


        // Bookings array init                 - Get the ID list of ALL bookings
        $booking_id_list = array();

        $bookings = array();
        $short_days = array();
        $short_days_type_id = array();
        if ( count($bookings_res)>0 )
        foreach ($bookings_res as $booking ) {
            if ( ! in_array($booking->booking_id, $booking_id_list) ) $booking_id_list[] = $booking->booking_id;

            $bookings[$booking->booking_id] = $booking;
            $bookings[$booking->booking_id]->dates=array();
            $bookings[$booking->booking_id]->dates_short=array();

            $bk_list_type = (isset($booking->booking_type))?$booking->booking_type:'1';
            
            if ( ( isset($booking->sync_gid) ) && (! empty($booking->sync_gid)) ) {
                $booking->form .= "~text^sync_gid{$booking->booking_type}^{$booking->sync_gid}";
            }
            
            $cont = get_form_content($booking->form, $bk_list_type, '', array('booking_id'=> $booking->booking_id 
                                                                              , 'resource_title'=> (isset($booking_types[$booking->booking_type]))?$booking_types[$booking->booking_type]:''                                                                              
                                                                              )
                                                         );

            $search = array ("'(<br[ ]?[/]?>)+'si","'(<p[ ]?[/]?>)+'si","'(<div[ ]?[/]?>)+'si");
            $replace = array ("&nbsp;&nbsp;"," &nbsp; "," &nbsp; ");
            $cont['content'] = preg_replace($search, $replace, $cont['content']);
            $bookings[$booking->booking_id]->form_show = $cont['content'];
            unset($cont['content']);
            $bookings[$booking->booking_id]->form_data = $cont;
        }
        $booking_id_list = implode(",",$booking_id_list);
        $booking_id_list = wpbc_clean_string_for_db( $booking_id_list );
        
        if (! empty($booking_id_list)) {
            // Get Dates  for all our Bookings
            $sql = " SELECT *
            FROM {$wpdb->prefix}bookingdates as dt
            WHERE dt.booking_id in ( {$booking_id_list} ) ";

            if (class_exists('wpdev_bk_biz_l'))
                $sql .= " ORDER BY booking_id, type_id, booking_date   ";
            else
                $sql .= " ORDER BY booking_id, booking_date   ";

            $booking_dates = $wpdb->get_results( $sql );
        } else
            $booking_dates = array();


        $last_booking_id = '';
        // Add Dates to Bookings array
        foreach ($booking_dates as $date) {
            $bookings[$date->booking_id]->dates[] = $date;

                if ($date->booking_id != $last_booking_id) {
                    if (! empty($last_booking_id)) {
                        if($last_show_day != $dte) { $short_days[]= $dte; $short_days_type_id[] = $last_day_id;}

                        $bookings[ $last_booking_id ]->dates_short = $short_days;
                        $bookings[ $last_booking_id ]->dates_short_id = $short_days_type_id;
                    }
                    $last_day = '';
                    $last_day_id = '';
                    $last_show_day = '';
                    $short_days = array();
                    $short_days_type_id = array();
                }

                $last_booking_id = $date->booking_id;
                $dte = $date->booking_date;

                if (empty($last_day)) { // First date
                    $short_days[]= $dte; $short_days_type_id[] = (isset($date->type_id))?$date->type_id:'';
                    $last_show_day = $dte;
                } else {                // All other days
                    if ( wpdevbk_is_next_day( $dte ,$last_day) ) {
                        if ($last_show_day != '-') { $short_days[]= '-'; $short_days_type_id[] = ''; }
                        $last_show_day = '-';
                    } else {
                        if ($last_show_day !=$last_day) { $short_days[]= $last_day; $short_days_type_id[] = $last_day_id; }
                        $short_days[]= ','; $short_days_type_id[] = '';
                        $short_days[]= $dte; $short_days_type_id[] = (isset($date->type_id))?$date->type_id:'';
                        $last_show_day = $dte;
                    }
                }
                $last_day = $dte;
                $last_day_id = (isset($date->type_id))?$date->type_id:'';
        }

        if (isset($dte))
            if($last_show_day != $dte) { $short_days[]= $dte; $short_days_type_id[] = (isset($date->type_id))?$date->type_id:'';}
        if (isset($bookings[ $last_booking_id ]) )  {
            $bookings[ $last_booking_id ]->dates_short = $short_days;
            $bookings[ $last_booking_id ]->dates_short_id = $short_days_type_id;
        }

        
        // Showing only  bookings that  starting or ending during "Today"
        if ( (isset($args['wh_booking_date'])) && ($args['wh_booking_date'] == '9') ) {

            $today_mysql_format = date_i18n( 'Y-m-d',  time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + 0 * DAY_IN_SECONDS ); // Today day with gmt offset
            foreach ($bookings as $bc_id=>$bc_value) {              
                
                $check_in_date = $bc_value->dates_short[0];
                $check_in_date = explode(' ',$check_in_date);
                $check_in_date = $check_in_date[0];                             // 2014-02-25
                
                if ( count($bc_value->dates_short) == 1 )
                    $check_out_date = $bc_value->dates_short[0];
                else 
                    $check_out_date = $bc_value->dates_short[2];
                $check_out_date = explode(' ',$check_out_date);
                $check_out_date = $check_out_date[0]; // 2014-02-25

                if ( ( $today_mysql_format != $check_in_date ) &&  ( $today_mysql_format != $check_out_date ) ) {
                    unset($bookings[$bc_id]);
                    $bookings_count--;
                }
            }            
        }        
        
        
        // If we selected the Dates as "Check In - Today/Tommorow", then show only the bookings, where check in date is Today 
        if ( (isset($args['wh_booking_date'])) && ($args['wh_booking_date'] == '7') ) {
            //$today_mysql_format = date('Y-m-d');
            //$today_mysql_format = date('Y-m-d', time() +86400 );               //1 Day = 24*60*60 = 86400
            $today_mysql_format = date_i18n( 'Y-m-d',  time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + DAY_IN_SECONDS ); // Tommorow day with gmt offset
            foreach ($bookings as $bc_id=>$bc_value) {              
                $check_in_date = $bc_value->dates_short[0];
                $check_in_date = explode(' ',$check_in_date);
                $check_in_date = $check_in_date[0]; // 2014-02-25
                if ( $today_mysql_format != $check_in_date ) {
                    unset($bookings[$bc_id]);
                    $bookings_count--;
                }
            }
        }   
        
//debuge( 'Before filtering:',  date('Y-m-d', time() +86400 ), $_REQUEST, $bookings ) ;       
        // If we selected the Dates as "Check Out - Tomorow", then show only the bookings, where check out date is Tomorrow 
        if ( (isset($args['wh_booking_date'])) && ($args['wh_booking_date'] == '8') ) {            
            //$tomorrow_mysql_format = date('Y-m-d', time() +86400 );               //1 Day = 24*60*60 = 86400
            $tomorrow_mysql_format = date_i18n( 'Y-m-d',  time() + ( get_option( 'gmt_offset' ) * HOUR_IN_SECONDS ) + DAY_IN_SECONDS ); // Tommorow day with gmt offset
            foreach ($bookings as $bc_id=>$bc_value) {                
                if ( count($bc_value->dates_short) == 1 )
                    $check_out_date = $bc_value->dates_short[0];
                else 
                    $check_out_date = $bc_value->dates_short[2];
                $check_out_date = explode(' ',$check_out_date);
                $check_out_date = $check_out_date[0]; // 2014-02-25
                if ( $tomorrow_mysql_format != $check_out_date ) {
                    unset($bookings[$bc_id]);
                    $bookings_count--;
                }
            }
        }         
//debuge(  'After filtering:', $tomorrow_mysql_format, $bookings ) ;        
        //debuge(array($bookings , $booking_types, $bookings_count, $page_num,  $page_items_count));
        return array($bookings , $booking_types, $bookings_count, $page_num,  $page_items_count);
    }

    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //        B o o k i n g        P A G E s        ////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpdevbk_show_booking_page(){ 
        wpbc_welcome_panel(); 
        ////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Get from SETTINGS (if its not set in request yet) the "tab"  & "view_mode" and set to $_REQUEST        
        // If we have the "saved" filter set so LOAD it and set to REQUEST, if REQUEST was not set previously
        // & skip "wh_booking_type" from the saved filter set
        ////////////////////////////////////////////////////////////////////////////////////////////////////////
        wpdevbk_get_default_bk_listing_filter_set_to_params('default');         // Get saved filters set        DONE!
//debuge($_REQUEST);                
        // Setting $_REQUEST['wh_booking_type']
        if (function_exists('wpdevbk_check_wh_booking_type_param_in_request'))
            wpdevbk_check_wh_booking_type_param_in_request() ;                  // DONE!

        // If  "wh_booking_type" is not set, and current user  is not superadmin, then set to  - $_REQUEST['wh_booking_type'] - booking resource from regular user        
        make_bk_action('check_for_resources_of_notsuperadmin_in_booking_listing' ); // DONE!
                
        wpdevbk_booking_view_mode_buttons();                                    // Show switch calendar/listing buttons DONE!
        
//debuge($_REQUEST);        
        
        switch ($_REQUEST['view_mode']) {
            
            case 'vm_calendar':                                                 // vm_calendar
                 bookings_overview_in_calendar();
                break;            
            default:                                                            // vm_listing
                wpdevbk_show_booking_listings();
        }
        wpdevbk_booking_listing_write_js();                                     // Wtite inline  JS
        wpdevbk_booking_listing_write_css();                                    // Write inline  CSS        
    }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // B O O K I N G    L I S T I N G    P A G E        ////////////////////////////////////////////////////////////////////////////////////////
        function wpdevbk_show_booking_listings() {


            wpdevbk_booking_listings_interface_header() ;                            // Show Filters and Action tabs

            $args = wpdev_get_args_from_request_in_bk_listing();                     // Get safy PARAMS from REQUEST - its used for the booking engine function       
            ?><textarea id="bk_request_params" style="display:none;"><?php echo  serialize($args) ; ?></textarea><?php

            $bk_listing = wpdev_get_bk_listing_structure_engine( $args );           // Get Bookings structure
            $bookings       = $bk_listing[0];
            $booking_types  = $bk_listing[1];
            $bookings_count = $bk_listing[2];
            $page_num       = $bk_listing[3];
            $page_items_count= $bk_listing[4];
//debuge($args, count($bookings),$bookings, $booking_types, $_REQUEST);

            booking_listing_table($bookings , $booking_types);                      // Show the bookings listing table
            wpdevbk_show_pagination($bookings_count, $page_num, $page_items_count); // Show Pagination        
        }

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // C A L E N D A R    O V E R V I E W    P A G E    ////////////////////////////////////////////////////////////////////////////////////////
        function bookings_overview_in_calendar( ) { //return false;

            wpdevbk_booking_calendar_overview_interface_header() ;                            // Show Filters and Action tabs

            $args = wpdev_get_args_from_request_in_bk_overview_in_calendar();                 // Get safy PARAMS from REQUEST  - its used for the booking engine function
            ?><textarea id="bk_request_params" style="display:none;"><?php echo  serialize($args) ; ?></textarea><?php
//debuge($args);
            $bk_listing = wpdev_get_bk_listing_structure_engine( $args );           // Get Bookings structure
            $bookings       = $bk_listing[0];
            $booking_types  = $bk_listing[1];
            $bookings_count = $bk_listing[2];
            $page_num       = $bk_listing[3];
            $page_items_count= $bk_listing[4];
            //debuge($args, count($bookings),$bookings, $booking_types[$args['wh_booking_type']], $_REQUEST);
//debuge($booking_types);
            booking_calendar_overview_table($bookings , $booking_types);
        }
        

    // <editor-fold desc="  B o o k i n g     L i s t i n g     I N T E R F A C E  "  defaultstate="collapsed" >
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Show     T A B s    in      t o o l b a r /////////////////////////////////////////////////////////////
    function wpdevbk_booking_listings_tabs_in_top_menu_line() {

        $is_only_icons = ! true;
        if ($is_only_icons) echo '<style type="text/css"> #menu-wpdevplugin .nav-tab { padding:4px 2px 6px 32px !important; } </style>';

        if (! isset($_REQUEST['tab'])) $_REQUEST['tab'] = 'filter';
        $selected_title = $_REQUEST['tab'];

        ?>
         <div style="height:1px;clear:both;margin-top:30px;"></div>
         <div id="menu-wpdevplugin">
            <div class="nav-tabs-wrapper">
                <div class="nav-tabs">

                    <?php $title = __('Filter' ,'booking'); $my_icon = ''; $my_tab = 'filter';  $my_additinal_class= ''; ?>
                    <?php if ($_REQUEST['tab'] == 'filter') {  $slct_a = 'selected'; $selected_title = $title; $selected_icon = $my_icon; } else {  $slct_a = ''; } ?><a class="nav-tab <?php if ($slct_a == 'selected') { echo ' nav-tab-active '; } echo $my_additinal_class; ?>" title="<?php //echo __('Customization of booking form fields' ,'booking');  ?>"  href="javascript:void(0)" onclick="javascript:
                                jQuery('.visibility_container').hide(); 
                                jQuery('#<?php echo $my_tab; ?>').show();
                                jQuery('.nav-tab').removeClass('nav-tab-active');                                
                                jQuery(this).addClass('nav-tab-active');
                                jQuery('.nav-tab i.icon-white').removeClass('icon-white');
                                jQuery('.nav-tab-active i').addClass('icon-white');"
                       ><i class="<?php if ($slct_a == 'selected') echo 'icon-white '; ?>icon-cog"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>

                    <?php $title = __('Actions' ,'booking'); $my_icon = ''; $my_tab = 'actions';  $my_additinal_class= ''; ?>
                    <?php if ($_REQUEST['tab'] == 'actions') {  $slct_a = 'selected'; $selected_title = $title; $selected_icon = $my_icon; } else {  $slct_a = ''; } ?><a class="nav-tab <?php if ($slct_a == 'selected') { echo ' nav-tab-active '; } echo $my_additinal_class;  ?>" title="<?php //echo __('Customization of booking form fields' ,'booking');  ?>"  href="javascript:void(0)" onclick="javascript:
                                jQuery('.visibility_container').hide(); 
                                jQuery('#<?php echo $my_tab; ?>').show();
                                jQuery('.nav-tab').removeClass('nav-tab-active');                                
                                jQuery(this).addClass('nav-tab-active');
                                jQuery('.nav-tab i.icon-white').removeClass('icon-white');
                                jQuery('.nav-tab-active i').addClass('icon-white');"
                       ><i class="<?php if ($slct_a == 'selected') echo 'icon-white '; ?>icon-fire"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>

                    <?php wpdevbk_show_help_dropdown_menu_in_top_menu_line(); ?>

                </div>
            </div>
        </div>
        <?php
        
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Show    T O O L B A R   at top of page   /////////////////////////////////////////////////////////////
    function wpdevbk_booking_listings_interface_header() {
        ?><div id="booking_listings_interface_header"><?php
            wpdevbk_booking_listings_tabs_in_top_menu_line();

            if (! isset($_REQUEST['tab'])) $_REQUEST['tab'] = 'filter';
            $selected_title = $_REQUEST['tab'];

        ?>
            <div class="booking-submenu-tab-container" style="">
                <div class="nav-tabs booking-submenu-tab-insidecontainer">

                    <div class="visibility_container active" id="filter" style="<?php if ($selected_title == 'filter') { echo 'display:block;'; } else { echo 'display:none;'; }  ?>">
                        <?php wpdevbk_show_booking_filters(); ?>

                        <span id="show_link_advanced_booking_filter" class="tab-bottom tooltip_right" data-original-title="<?php _e('Expand Advanced Filter' ,'booking'); ?>"  rel="tooltip"><a href="javascript:void(0)" onclick="javascript:jQuery('.advanced_booking_filter').show();jQuery('#show_link_advanced_booking_filter').hide();jQuery('#hide_link_advanced_booking_filter').show();"><i class="icon-chevron-down"></i></a></span>
                        <span id="hide_link_advanced_booking_filter" style="display:none;" class="tab-bottom tooltip_right" data-original-title="<?php _e('Collapse Advanced Filter' ,'booking'); ?>" rel="tooltip" ><a href="javascript:void(0)"  onclick="javascript:jQuery('.advanced_booking_filter').hide(); jQuery('#hide_link_advanced_booking_filter').hide(); jQuery('#show_link_advanced_booking_filter').show();"><i class="icon-chevron-up"></i></a></span>
                    </div>

                    <div class="visibility_container" id="actions"  style="<?php if ($selected_title == 'actions') { echo 'display:block;'; } else { echo 'display:none;'; }  ?>">
                        <?php wpdev_show_booking_actions(); ?>
                    </div>

                    <div class="visibility_container" id="help"     style="<?php if ($selected_title == 'help') { echo 'display:block;'; } else { echo 'display:none;'; }  ?>">
                    </div>

                </div>
            </div>

            <div class="btn-group" style="position:absolute;right:20px;">
                <fieldset>
                    <label for="is_send_email_for_pending" style="display: inline-block;vertical-align: middle;margin: 10px 5px;color: #777777;"    >
                        <input type="checkbox" checked="CHECKED" id="is_send_email_for_pending" name="is_send_email_for_pending"  rel="tooltip" class="tooltip_top"  
                            data-original-title="<?php _e('Send email notification to customer after approval, cancellation or deletion of bookings' ,'booking'); ?>"
                        /><?php _e('Emails sending' ,'booking') ?>
                    </label>
                </fieldset>
            </div>


            <div style="height:1px;clear:both;margin-top:1px;"></div>
        </div>
        <div style="height:1px;clear:both;margin-top:40px;"></div>
        <?php        
    }


        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  Filters interface      ///////////////////////////////////////////////////////////////////////////////
        function wpdevbk_show_booking_filters(){
            ?>  <div style="clear:both;height:1px;"></div>
                <div class="wpdevbk-filters-section ">

                        <div class="wpbc-search-by-booking-id" >
                        <form  name="booking_filters_formID" action="" method="post" id="booking_filters_formID" class=" form-search">
                            <?php if (isset($_REQUEST['wh_booking_id']))  $wh_booking_id = $_REQUEST['wh_booking_id'];                  //  {'1', '2', .... }
                                  else                                    $wh_booking_id      = '';                    ?>
                            <input class="input-small" type="text" placeholder="<?php _e('Booking ID' ,'booking'); ?>" name="wh_booking_id" id="wh_booking_id" value="<?php echo $wh_booking_id; ?>" >
                            <button class="button button-secondary" type="submit"><?php _e('Go' ,'booking'); ?></button>
                        </form>
                        </div>

                        <form  name="booking_filters_form" action="" method="post" id="booking_filters_form"  class="form-inline">
                            <input type="hidden" name="page_num" id ="page_num" value="1" />
                            <div class="btn-group" style="float: left; margin-right: 15px;margin-bottom: 9px;">
                            <a class="tooltip_top button button-primary" style="float: left; "
                               data-original-title="<?php _e('Refresh booking listing' ,'booking'); ?>"  rel="tooltip" 
                                href="javascript:void(0)"
                                onclick="javascript:booking_filters_form.submit();"
                                ><?php _e('Apply' ,'booking'); ?> <i class="icon-refresh icon-white"></i></a><a 
                                data-original-title="<?php _e('Reset filter to default values' ,'booking'); ?>"  rel="tooltip" 
                                class="tooltip_top button button-secondary" 
                                href="<?php echo 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking&view_mode=vm_listing'; ?>"
                                style="border: 1px solid #aaa;border-left: none;"
                                ><i class="icon-remove"></i></a>
                            </div>

    <?php /** ?>
              <?php

              $wpdevbk_id= '';
              $wpdevbk_selectors='';
              $wpdevbk_control_label='';
              $wpdevbk_help_block='Booking Status';
              $wpdevbk_default_value = '';
              $wpdevbk_selector_default_value='';
              ?>
              <div class="control-group" style="float:left;">
                <label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?></label>
                <div class="inline controls">
                    <!--div class="btn-group" data-toggle="buttons-radio" id="radiobutton_<?php echo $wpdevbk_id; ?>"-->
                    <div class="btn-group" data-toggle="buttons-checkbox" id="radiobutton_<?php echo $wpdevbk_id; ?>">
                        <a  href="javascript:void(0)" class="btn">Approved</a>
                        <a  href="javascript:void(0)" class="btn">Pending</a>
                    </div>
                    <input type="hidden" value="<?php echo $wpdevbk_selector_default_value; ?>" id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>" />
                  <p class="help-block"><?php echo $wpdevbk_help_block; ?></p>
                </div>
              </div>
                <script type="text/javascript">
                    jQuery('#radiobutton_<?php echo $wpdevbk_id; ?> .btn').button();
                    <?php if (1) {  // Press the button ?>
                        jQuery('#radiobutton_<?php echo $wpdevbk_id; ?> .btn:first').button('toggle');
                    <?php } ?>
                </script>
    <?php /**/ ?>
                            <?php // Approved / Pending
                            $wpdevbk_id =              'wh_approved';                           //  {'', '0', '1' }
                            $wpdevbk_selectors = array(__('Pending' ,'booking')   =>'0',
                                                       __('Approved' ,'booking')  =>'1',
                                                       'divider0'=>'divider',
                                                       __('Any' ,'booking')       =>'');
                            $wpdevbk_control_label =   '';
                            $wpdevbk_help_block =      __('Status' ,'booking');
                            // Pending, Active, Suspended, Terminated, Cancelled, Fraud
                            wpdevbk_selectbox_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block);
                            ?>

                            <?php  // Booking Dates
                            $wpdevbk_id =              'wh_booking_date';
                            $wpdevbk_id2 =             'wh_booking_date2';
                            $wpdevbk_control_label =   '';
                            $wpdevbk_help_block =      __('Dates' ,'booking');
                            $wpdevbk_width =           'span2 wpdevbk-filters-section-calendar';
                            $wpdevbk_icon =            '<i class="icon-calendar"></i>' ;
                            wpdevbk_dates_selection_for_filter($wpdevbk_id, $wpdevbk_id2, $wpdevbk_control_label,  $wpdevbk_help_block,  $wpdevbk_width, $wpdevbk_icon );
                            ?>
                            <span style="display:none;" class="advanced_booking_filter">
                            <?php  // Read / Unread
//                            $wpdevbk_id =              'wh_is_new';                           //  {'',  '1' }
//                            $wpdevbk_selectors =        array('','1');
//                            $wpdevbk_control_label =   __('Unread' ,'booking');
//                            $wpdevbk_help_block =      __('Only New' ,'booking');
//
//                            wpdevbk_checkboxbutton_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block);
                            
                            
                            $wpdevbk_id =              'wh_is_new';                           //  {'', '0', '1' }
                            $wpdevbk_selectors = array(__('All bookings' ,'booking')   =>'',
                                                       __('New bookings' ,'booking')  =>'1',
                                                       );
                            $wpdevbk_control_label =   '';
                            $wpdevbk_help_block =      __('Show' ,'booking');
                            
                            wpdevbk_selectbox_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block);
                            
                            
                            ?>


                            <?php  // Creation Dates
                            $wpdevbk_id =              'wh_modification_date';
                            $wpdevbk_id2 =             'wh_modification_date2';
                            $wpdevbk_control_label =   '';
                            $wpdevbk_help_block =      __('Creation' ,'booking');
                            $wpdevbk_width =           'span2 wpdevbk-filters-section-calendar';
                            $wpdevbk_icon =            '<i class="icon-calendar"></i>' ;
                            $exclude_items = array(0, 2, 4, 7, 8, 9);
                            $default_item = 3 ;
                            wpdevbk_dates_selection_for_filter($wpdevbk_id, $wpdevbk_id2, $wpdevbk_control_label,  $wpdevbk_help_block,  $wpdevbk_width, $wpdevbk_icon, $exclude_items, $default_item );
                            ?>

                            <?php if (function_exists('wpdebk_filter_field_bk_keyword')) {
                                      wpdebk_filter_field_bk_keyword();
                            } ?>

                            <?php if (function_exists('wpdebk_filter_field_bk_paystatus')) {
                                      wpdebk_filter_field_bk_paystatus();
                            } ?>

                            <?php if (function_exists('wpdebk_filter_field_bk_costs')) {
                                      wpdebk_filter_field_bk_costs();
                            } ?>

                            </span>

                            <?php // Sort
                            $wpdevbk_id =              'or_sort';                           //  {'', '0', '1' }
                            $wpdevbk_selectors = array(__('ID' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'',
                                                       __('Dates' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'sort_date',
                                                       'divider0'=>'divider',
                                                       __('ID' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'booking_id_asc',
                                                       __('Dates' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'sort_date_asc'
                                                      );

                            $wpdevbk_selectors = apply_bk_filter('bk_filter_sort_options', $wpdevbk_selectors);

                            $wpdevbk_control_label =   '';
                            $wpdevbk_help_block =      __('Order by' ,'booking');

                            $wpdevbk_default_value = get_bk_option( 'booking_sort_order');
                            wpdevbk_selectbox_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block, $wpdevbk_default_value);
                            ?>
                            
                            <?php if (function_exists('wpdevbk_booking_resource_selection_for_booking_listing')) {
                                      wpdevbk_booking_resource_selection_for_booking_listing();
                            } ?>

                            <?php if (class_exists('wpdev_bk_personal')) { ?>
                            <div style="float:left;display:none;" class="advanced_booking_filter btn-group">

                                
                                <a data-original-title="<?php _e('Save filter settings as default template (Please, click Apply filter button, before saving!)' ,'booking'); ?>"  rel="tooltip"
                                   class="tooltip_top button button-secondary" 
                                    onclick="javascript:save_bk_listing_filter( '<?php echo get_bk_current_user_id(); ?>',  'default' , '<?php echo get_params_in_url( array('page_num','wh_booking_type') ); ?>' );"
                                    ><?php _e('Save as Default' ,'booking'); ?> <i class="icon-download"></i></a>
                                <?php 
                                $saved_tamplate_option = get_user_option( 'booking_listing_filter_' . 'default', get_bk_current_user_id());
                                if (false != $saved_tamplate_option ) {
                                ?>    
                                <a data-original-title="<?php _e('Delete your previously saved default filer template!' ,'booking'); ?>"  rel="tooltip"
                                   class="tooltip_top button button-secondary" 
                                    onclick="javascript:delete_bk_listing_filter( '<?php echo get_bk_current_user_id(); ?>',  'default' );"
                                    ><?php _e('Delete template' ,'booking'); ?> <i class="icon-trash"></i></a>
                            <?php } ?>

                            </div>
                            <?php } ?>
                          <div class="clear"></div>
                        </form>



                <!--div id="tooltipsinit" class="tooltip-demo well">
                    <p style="margin-bottom: 0;" class="muted">Tight pants next level keffiyeh
                        <a rel="tooltip" href="javascript:void(0)" data-original-title="first tooltip">you probably</a>

                        haven't heard of them. Photo booth beard raw denim letterpress vegan messenger bag stumptown. Farm-to-table seitan, mcsweeney's fixie sustainable quinoa 8-bit american apparel

                        <a rel="tooltip" href="javascript:void(0)" data-original-title="Another tooltip">have a</a>

                        terry richardson vinyl chambray. Beard stumptown, cardigans banh mi lomo thundercats. Tofu biodiesel williamsburg marfa, four loko mcsweeney's cleanse vegan chambray. A

                        <a title="Another one here too" rel="tooltip" href="javascript:void(0)">really ironic</a>

                        artisan whatever keytar, scenester farm-to-table banksy Austin

                        <a rel="tooltip" href="javascript:void(0)" data-original-title="The last tip!">twitter handle</a>

                        freegan cred raw denim single-origin coffee viral.
                    </p>
                </div>

                <script type="text/javascript">
                    jQuery('#tooltipsinit a').tooltip( {
                        animation: true
                      , delay: { show: 500, hide: 100 }
                      , selector: false
                      , placement: 'top'
                      , trigger: 'hover'
                      , title: ''
                      , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                    });
                </script>


                <div id="popover" class="well">
                    <a data-content="And here's some amazing content. It's very engaging. right?" rel="popover" class="btn btn-danger" href="javascript:void(0)" data-original-title="A Title">hover for popover</a>
                </div>


                <script type="text/javascript">
                    jQuery('#popover a').popover( {
                        placement: 'bottom'
                      , delay: { show: 100, hide: 100 }
                      , content: ''
                      , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                    });
                </script-->
                </div>
                <div style="clear:both;height:1px;"></div>
            <?php
        }


        //////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  Actions interface      ///////////////////////////////////////////////////////////////////////////////
        function wpdev_show_booking_actions(){
                $user = wp_get_current_user(); $user_bk_id = $user->ID;
                $is_can = true; // current_user_can( 'edit_posts' );
                if ($is_can) { ?>

                <div class="btn-toolbar">
                    <div class="btn-group">
                        <a     data-original-title="<?php _e('Approve selected bookings' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-primary"
                               onclick="javascript: 
                                                    approve_unapprove_booking( get_selected_bookings_id_in_booking_listing() ,
                                                          1, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1);
                               " /><?php _e('Approve' ,'booking'); ?> <i class="icon-ok-circle icon-white"></i></a>
                        <a     data-original-title="<?php _e('Set selected bookings as pending' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                               onclick="javascript: 
                                            if ( bk_are_you_sure('<?php echo esc_js(__('Do you really want to set booking as pending ?' ,'booking')); ?>') )
                                                    approve_unapprove_booking( get_selected_bookings_id_in_booking_listing() ,
                                                          0, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1);
                               " /><?php _e('Reject' ,'booking'); ?> <i class="icon-ban-circle"></i></a>
                    </div>
                    <div class="btn-group" style="width:340px">
                        <a  data-original-title="<?php _e('Delete selected bookings' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                            onclick="javascript: 
                                    if ( bk_are_you_sure('<?php echo esc_js(__('Do you really want to delete selected booking(s) ?' ,'booking')); ?>') )
                                        delete_booking( get_selected_bookings_id_in_booking_listing() ,
                                                        <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1  );
                                    " >
                            <?php _e('Delete' ,'booking'); ?> <i class="icon-trash"></i></a>
                        <input  type="text" placeholder="<?php echo __('Reason of cancellation' ,'booking'); ?>"
                                class="span2" value="" id="denyreason" name="denyreason" />
                    </div>

                    <div class="btn-group">
                        <a     data-original-title="<?php _e('Mark as read all bookings' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                               onclick="javascript:
                                                    mark_read_booking( 'all' ,
                                                          0, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' );
                               " /><?php _e('Read All' ,'booking'); ?> <i class="icon-eye-close"></i></a>
                        <a     data-original-title="<?php _e('Mark as read selected bookings' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                               onclick="javascript:
                                                    mark_read_booking( get_selected_bookings_id_in_booking_listing() ,
                                                          0, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' );
                               " /><?php _e('Read' ,'booking'); ?> <i class="icon-eye-close"></i></a>
                        <a     data-original-title="<?php _e('Mark as Unread selected bookings' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                               onclick="javascript:                                       
                                                    mark_read_booking( get_selected_bookings_id_in_booking_listing() ,
                                                          1, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' );
                               " /><?php _e('Unread' ,'booking'); ?> <i class="icon-eye-open"></i></a>
                    </div>

                    <?php make_bk_action('wpbc_extend_buttons_in_action_toolbar_booking_listing' ); ?>
                    
                    <?php if (function_exists('wpdebk_action_field_export_print')) {
                              wpdebk_action_field_export_print();
                    } ?>

                </div>
                <?php } ?>
              <div class="clear" style="height:1px;"></div>
              <div id="admin_bk_messages" style="margin:0px;"> </div>
              <div class="clear" style="height:1px;"></div>
            <?php
        }


    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   S H O W      B o o k i n g    L i s t i n g    T a b l e   //////////////////////////////////////////
    function booking_listing_table($bookings , $booking_types) {
        //debuge($_REQUEST);

        $user = wp_get_current_user(); $user_bk_id = $user->ID;
        
        $bk_url_listing     = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking' ;
        $bk_url_add         = $bk_url_listing . '-reservation' ;
        $bk_url_resources   = $bk_url_listing . '-resources' ;
        $bk_url_settings    = $bk_url_listing . '-option' ;

        $bk_admin_url = get_params_in_url( array('page_num', 'wh_booking_type')  );

        $booking_date_view_type = get_bk_option( 'booking_date_view_type');
        if ($booking_date_view_type == 'short') { $wide_days_class = ' hidden_items '; $short_days_class = ''; }
        else {                                    $wide_days_class = ''; $short_days_class = ' hidden_items '; }

        $version = get_bk_version();
        if ($version == 'free') $is_free = true;
        else                    $is_free = false;                    
        ?>
         <div id="listing_visible_bookings">
          <?php if (count($bookings)>0) { ?>
          <div class="row-fluid booking-listing-header">
              <div class="booking-listing-collumn span1 wpbc_column_1" style="text-align: left;">&nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" onclick="javascript:setCheckBoxInTable(this.checked, 'booking_list_item_checkbox');">&nbsp;&nbsp;&nbsp;<?php echo 'ID'; ?></div>
              <div class="booking-listing-collumn span<?php echo ($is_free?'1':'2'); ?> wpbc_column_2"><?php _e('Labels' ,'booking');  if ( ! $is_free ) { echo ' / '; _e('Actions' ,'booking'); } ?></div>
              <div class="booking-listing-collumn span<?php echo ($is_free?'5':'6'); ?> wpbc_column_4"><?php _e('Booking Data' ,'booking'); ?></div>
              <div class="booking-listing-collumn span3 wpbc_column_5"><?php _e('Booking Dates' ,'booking'); ?>&nbsp;&nbsp;&nbsp;
                  <a href="javascript:void(0)" id="booking_dates_full" onclick="javascript:
                            jQuery('#booking_dates_full').hide();
                            jQuery('#booking_dates_small').show();
                            jQuery('.booking_dates_small').hide();
                            jQuery('.booking_dates_full').show();" data-original-title="<?php _e('Show ALL dates of booking' ,'booking'); ?>"  rel="tooltip" class="tooltip_top <?php echo $short_days_class; ?> "><i class="icon-resize-full" style=" margin-top: 2px;"></i></a>
                  <a href="javascript:void(0)" id="booking_dates_small" onclick="javascript:
                            jQuery('#booking_dates_small').hide();
                            jQuery('#booking_dates_full').show();
                            jQuery('.booking_dates_small').show();
                            jQuery('.booking_dates_full').hide();" data-original-title="<?php _e('Show only check in/out dates' ,'booking'); ?>"  rel="tooltip" class="tooltip_top <?php echo $wide_days_class; ?> " ><i class="icon-resize-small" style=" margin-top: 2px;"></i></a>
              </div>
              <?php if ($is_free) { ?>
              <div class="booking-listing-collumn span2 wpbc_column_5"><?php _e('Actions' ,'booking'); ?></div>
              <?php } ?>
          </div>
          <?php } else {
                        echo '<center><h3>'.__('Nothing found!' ,'booking') .'</h3></center>';
                } ?>
        <?php

        // P
        $print_data = apply_bk_filter('get_bklist_print_header', array(array())  );

        $is_alternative_color = true;
        $id_of_new_bookings = array();

        $availbale_locales_in_system = get_available_languages();               //FixIn: 5.4.5
        
        foreach ($bookings as $bk) {
            $is_selected_color = 0;//rand(0,1);
            $is_alternative_color = ! $is_alternative_color;

            $booking_id             = $bk->booking_id;          // 100
            $is_new                 = (isset($bk->is_new))?$bk->is_new:'0';                           // 1
            $bk_modification_date   = (isset($bk->modification_date))?$bk->modification_date:'';    // 2012-02-29 16:01:58
            $bk_form                = $bk->form;                // select-one^rangetime5^10:00 - 12:00~text^name5^Jonny~text^secondname5^Smith~email^ ....
            $bk_form_show           = $bk->form_show;           // First Name:Jonny   Last Name:Smith   Email:email@server.com  Country:GB  ....
            $bk_form_data           = $bk->form_data;           // Array ([name] => Jonny... [_all_] => Array ( [rangetime5] => 10:00 - 12:00 [name5] => Jonny ... ) .... )
            $bk_dates               = $bk->dates;               // Array ( [0] => stdClass Object ( [booking_id] => 8 [booking_date] => 2012-04-16 10:00:01 [approved] => 0 [type_id] => )
            $bk_dates_short         = $bk->dates_short;         // Array ( [0] => 2012-04-16 10:00:01 [1] => - [2] => 2012-04-20 12:00:02 [3] => , [4] => 2012-04-16 10:00:01 ....

            //P
            $bk_booking_type        = (isset($bk->booking_type))?$bk->booking_type:'1';        // 3
            if (!class_exists('wpdev_bk_personal')) {
                $bk_booking_type_name = '<span class="label_resource_not_exist">'.__('Default' ,'booking').'</span>';
            } else if (isset($booking_types[$bk_booking_type]))   {
                $bk_booking_type_name   = $booking_types[$bk_booking_type]->title;        // Default
                if (strlen($bk_booking_type_name)>19) {
                    //$bk_booking_type_name = substr($bk_booking_type_name, 0,  13) . ' ... ' . substr($bk_booking_type_name, -3 );
                    $bk_booking_type_name = '<span style="cursor:pointer;" rel="tooltip" class="tooltip_top"  data-original-title="'.$bk_booking_type_name.'">'.substr($bk_booking_type_name, 0,  13) . ' ... ' . substr($bk_booking_type_name, -3 ).'</span>';
                }
            } else  {
                $bk_booking_type_name = '<span class="label_resource_not_exist">'.__('Resource not exist' ,'booking').'</span>';
            }

            $bk_hash                = (isset($bk->hash))?$bk->hash:'';                // 99c9c2bd4fd0207e4376bdbf5ee473bc
            $bk_remark              = (isset($bk->remark))?$bk->remark:'';            //
            //BS
            $bk_cost                = (isset($bk->cost))?$bk->cost:'';                // 150.00
            
            $bk_pay_status          = (isset($bk->pay_status))?$bk->pay_status:'';    // 30800
            $bk_pay_request         = (isset($bk->pay_request))?$bk->pay_request:'';  // 0
            $bk_status              = (isset($bk->status))?$bk->status:'';
            //BL
            $bk_dates_short_id = array(); if (count($bk->dates) > 0 ) $bk_dates_short_id      = (isset($bk->dates_short_id))?$bk->dates_short_id:array();      // Array ([0] => [1] => .... [4] => 6... [11] => [12] => 8 )

            $is_approved = 0;   if (count($bk->dates) > 0 )     $is_approved = $bk->dates[0]->approved ;
            //BS
            $is_paid = 0;
            $payment_status_titles_current = '';
            if (class_exists('wpdev_bk_biz_s')) {

                if ( is_payment_status_ok( trim($bk_pay_status) ) ) $is_paid = 1 ;
                
                $payment_status_titles = get_payment_status_titles();
                $payment_status_titles_current = array_search($bk_pay_status, $payment_status_titles);
                if ($payment_status_titles_current === FALSE ) $payment_status_titles_current = $bk_pay_status ;
            }

            if ( $is_new == 1) $id_of_new_bookings[] = $booking_id;


            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get SHORT Dates showing data ////////////////////////////////////////////////////////////////////////////////////////////////////
            //$short_dates_content = wpdevbk_get_str_from_dates_short($bk_dates_short, $is_approved , $bk_dates_short_id , $booking_types );
            $short_dates_content = '';
            $dcnt = 0;
            foreach ($bk_dates_short as $dt) {
                if ($dt == '-') {       $short_dates_content .= '<span class="date_tire"> - </span>';
                } elseif ($dt == ',') { $short_dates_content .= '<span class="date_tire">, </span>';
                } else {
                    $short_dates_content .= '<a href="javascript:void(0)" class="field-booking-date ';
                    if ($is_approved) $short_dates_content .= ' approved';
                    $short_dates_content .= '">';

                    $bk_date = wpdevbk_get_date_in_correct_format($dt);
                    $short_dates_content .= $bk_date[0];
                    $short_dates_content .= '<sup class="field-booking-time">'. $bk_date[1] .'</sup>';

                     // BL
                     if (class_exists('wpdev_bk_biz_l')) {
                         if (! empty($bk_dates_short_id[$dcnt]) ) {
                             $bk_booking_type_name_date   = $booking_types[$bk_dates_short_id[$dcnt]]->title;        // Default
                             if (strlen($bk_booking_type_name_date)>19) $bk_booking_type_name_date = substr($bk_booking_type_name_date, 0,  13) . '...' . substr($bk_booking_type_name_date, -3 );

                             $short_dates_content .= '<sup class="field-booking-time date_from_dif_type"> '.$bk_booking_type_name_date.'</sup>';
                         }
                     }
                    $short_dates_content .= '</a>';
                }
                $dcnt++;
            }


            // Get WIDE Dates showing data /////////////////////////////////////////////////////////////////////////////////////////////////////
            $wide_dates_content = '';
            $dates_count = count($bk_dates); $dcnt = 0;
            foreach ($bk_dates as $dt) { $dcnt++;
                $wide_dates_content .= '<a href="javascript:void(0)" class="field-booking-date ';
                if ($is_approved) $wide_dates_content .= ' approved';
                $wide_dates_content .= ' ">';

                $bk_date = wpdevbk_get_date_in_correct_format($dt->booking_date);
                $wide_dates_content .=  $bk_date[0];
                $wide_dates_content .= '<sup class="field-booking-time">' . $bk_date[1]. '</sup>';
                 // BL
                if (class_exists('wpdev_bk_biz_l')) {
                 if (($dt->type_id != '') && (isset($booking_types[$dt->type_id]))) {
                     $bk_booking_type_name_date   = $booking_types[$dt->type_id]->title;        // Default
                     if (strlen($bk_booking_type_name_date)>19) $bk_booking_type_name_date = substr($bk_booking_type_name_date, 0, 13) . '...' . substr($bk_booking_type_name_date, -3 );
                     $wide_dates_content .= '<sup class="field-booking-time date_from_dif_type"> '.$bk_booking_type_name_date.'</sup>';
                 }
                }
                 $wide_dates_content .= '</a>';
                 if ($dcnt<$dates_count) { $wide_dates_content .= '<span class="date_tire">, </span>'; }
            }
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


            // BS
            $pay_print_status = '';
            if (class_exists('wpdev_bk_biz_s')) {
                if ($is_paid) {
                    $pay_print_status = __('Paid OK' ,'booking');
                    if ($payment_status_titles_current == 'Completed') $pay_print_status = $payment_status_titles_current;
                } else if ( (is_numeric($bk_pay_status)) || ($bk_pay_status == '') )        {
                    $pay_print_status = __('Unknown' ,'booking');
                } else  {
                    $pay_print_status = $payment_status_titles_current;
                }
            }
            ///// Print data  //////////////////////////////////////////////////////////////////////////////
            $print_data[] = apply_bk_filter('get_bklist_print_row', array() ,
                                             $booking_id,
                                             $is_approved ,
                                             $bk_form_show,
                                             $bk_booking_type_name,
                                             $is_paid ,
                                             $pay_print_status,
                                             ($booking_date_view_type == 'short')?'<div class="booking_dates_small">' . $short_dates_content . '</div>':'<div class="booking_dates_full">' .$wide_dates_content . '</div>' ,
                                             $bk_cost
                    );

            //////////////////////////////////////////////////////////////////////////////////////////////
            ?>
          <div id="booking_mark_<?php echo $booking_id; ?>"  class="<?php if ( $is_new!= '1') echo ' hidden_items '; ?> new-label clearfix-height">
              <a href="javascript:void(0)"  class="tooltip_bottom approve_bk_link  <?php //if ($is_approved) echo ' hidden_items '; ?> "
                       onclick="javascript:mark_read_booking( '<?php echo $booking_id; ?>' ,
                                                      0, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' );"
                       data-original-title="<?php _e('Mark' ,'booking'); echo ' '; _e('Unread' ,'booking'); ?>"  rel="tooltip" >
                        <img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/label_new_blue.png" style="width:24px; height:24px;"></a>
          </div>
          <div id="booking_row_<?php echo $booking_id; ?>"  class="row-fluid booking-listing-row clearfix-height<?php
          if ($is_alternative_color) echo ' row_alternative_color ';
          if ($is_selected_color) echo ' row_selected_color ';
          //if ($is_new) echo ' row_unread_color ';
          
            $date_format = get_bk_option( 'booking_date_format');
            $time_format = get_bk_option( 'booking_time_format');
            if (empty($date_format)) $date_format = "m / d / Y, D";
            if (empty($time_format)) $time_format = 'h:i a';
            $cr_date = date_i18n($date_format  , mysql2date('U',$bk_modification_date));
            $cr_time = date_i18n($time_format  , mysql2date('U',$bk_modification_date));
          ?>" >

              <div class="wpbc_column_1 booking-listing-collumn bktextcenter span1">
                    <input type="checkbox" class="booking_list_item_checkbox" 
                           onclick="javascript: if (jQuery(this).attr('checked') !== undefined ) { jQuery(this).parent().parent().addClass('row_selected_color'); } else {jQuery(this).parent().parent().removeClass('row_selected_color');}"
                           <?php if ($is_selected_color) echo ' checked="CHECKED" '; ?>
                           id="booking_id_selected_<?php  echo $booking_id;  ?>"  name="booking_appr_<?php  $booking_id;  ?>"
                           /><span class="wpbc_mobile_legend clear" style="margin:0 -5px 0 25px"><?php _e('ID' ,'booking'); ?>: </span><span class="field-id"><?php echo $booking_id; ?></span>                  
              </div>

              <div class="wpbc_column_2 booking-listing-collumn bktextleft booking-labels span<?php echo ($is_free?'1':'2'); ?> ">
                  <?php make_bk_action('wpdev_bk_listing_show_resource_label', $bk_booking_type_name, $bk_admin_url .'&wh_booking_type='. $bk_booking_type );  ?>
                  <?php make_bk_action('wpdev_bk_listing_show_payment_label', $is_paid,  $pay_print_status, $payment_status_titles_current);  ?>
                  <span class="label label-pending <?php if ($is_approved) echo ' hidden_items '; ?> "><?php _e('Pending' ,'booking'); ?></span>
                  <span class="label label-approved <?php if (! $is_approved) echo ' hidden_items '; ?>"><?php _e('Approved' ,'booking'); ?></span>
              </div>

              <div class="wpbc_column_3 booking-listing-collumn bktextjustify span<?php echo ($is_free?'5':'6'); ?> ">
                    <div style="text-align:left"><?php echo $bk_form_show; ?></div>
              </div>

              <div class="wpbc_column_4 booking-listing-collumn bktextleft booking-dates span3">

                <div class="booking_dates_small <?php echo $short_days_class; ?>"><?php echo $short_dates_content; ?></div>
                <div class="booking_dates_full  <?php echo $wide_days_class; ?>" ><?php echo $wide_dates_content;  ?></div>

              </div>

              <?php $edit_booking_url = $bk_url_add . '&booking_type='.$bk_booking_type.'&booking_hash='.$bk_hash.'&parent_res=1' ; ?>

              <div class="wpbc_column_5 booking-listing-collumn booking-actions<?php echo ($is_free?'0 span2 bktextcenter':' bktextleft '); ?> ">

                <?php $is_can = true;//current_user_can( 'edit_posts' );
                if ($is_can) { ?>  
                  
                  <?php make_bk_action('wpdev_bk_listing_show_cost_btn', $booking_id, $bk_cost );  ?>
                  
                  <div class="actions-fields-group">
                    <?php
                    
                        make_bk_action('wpdev_bk_listing_show_edit_btn', $booking_id , $edit_booking_url, $bk_remark, $bk_booking_type );  
                        
                    ?><a    href="javascript:void(0)"  class="tooltip_top approve_bk_link button-secondary button  <?php if ($is_approved) echo ' hidden_items '; ?> "
                            onclick="javascript:approve_unapprove_booking(<?php echo $booking_id; ?>,1, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1  );"
                            data-original-title="<?php _e('Approve' ,'booking'); ?>"  rel="tooltip" 
                       ><i class="icon-ok-circle"></i><?php
                        /** ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/accept-24x24.gif" style="width:14px; height:14px;"><?php /**/ ?></a><a                     
                            href="javascript:void(0)"  class="tooltip_top pending_bk_link button-secondary button  <?php if (! $is_approved) echo ' hidden_items '; ?> "
                            onclick="javascript:if ( bk_are_you_sure('<?php echo esc_js(__('Do you really want to set booking as pending ?' ,'booking')); ?>') ) approve_unapprove_booking(<?php echo $booking_id; ?>,0, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1  );"
                            data-original-title="<?php _e('Reject' ,'booking'); ?>"  rel="tooltip" 
                        ><i class="icon-ban-circle"></i><?php
                        /** ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/remove-16x16.png" style="width:15px; height:15px;"><?php /**/ ?></a><a                     
                            href="javascript:void(0)" 
                            onclick="javascript:if ( bk_are_you_sure('<?php echo esc_js(__('Do you really want to delete this booking ?' ,'booking')); ?>') ) delete_booking(<?php echo $booking_id; ?>, <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1   );"
                            data-original-title="<?php _e('Delete' ,'booking'); ?>"  rel="tooltip" 
                            class="tooltip_top button-secondary button"
                        ><i class="icon-trash"></i><?php
                        /** ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/delete_type.png" style="width:13px; height:13px;"><?php /**/ ?></a><?php 
                        
                         make_bk_action('wpdev_bk_listing_show_print_btn', $booking_id );
                         
                         make_bk_action('wpdev_bk_listing_show_payment_status_btn', $booking_id );                           
                         
                        ?>                      
                    
                    <?php //FixIn: 5.4.5.1 ?>
                    <?php if (class_exists('wpdev_bk_personal')) { ?>  
                    <div class="locale-fields-group" style="float:right; margin:0 10px;">
                      <select id="locale_for_booking<?php echo $booking_id; ?>" name="locale_for_booking<?php echo $booking_id; ?>" style="width:120px;">
                          <option value=""><?php _e('Default Locale' ,'booking'); ?></option>
                          <option value="en_US" lang="en" data-installed="1">English (United States)</option>
                          <?php 
                          foreach ( $availbale_locales_in_system as $locale ) {
                                  printf(
                                          '<option value="%s" lang="%s" data-installed="1">%s</option>',
                                          esc_attr( $locale ),
                                          esc_attr( substr( $locale, 0 , 2 ) ),			
                                          esc_html( $locale )
                                  );
                          }
                          ?>
                      </select>
                    </div>  
                    <?php } ?>
                      
                    <div class="field-date" style="<?php echo ($is_free?'margin-top:5px;float: none;white-space: normal;':'margin:9px;'); ?>"><span ><?php _e('Created' ,'booking'); ?>:</span> <span><?php echo $cr_date, ' ', $cr_time; ?></span></div>

                  </div>
              <?php } ?>
              </div>
       
              <?php make_bk_action('wpdev_bk_listing_show_edit_fields', $booking_id , $bk_remark );  ?>

              <?php make_bk_action('wpdev_bk_listing_show_payment_status_cost_fields', $booking_id  , $bk_pay_status);  ?>
              
          </div>
        <?php } ?>
        </div>

        <?php  //if  ( wpbc_is_field_in_table_exists('booking','is_new') != 0 )  renew_NumOfNewBookings($id_of_new_bookings); // Update num status if supported  ?>

        <?php make_bk_action('wpdev_bk_listing_show_change_booking_resources', $booking_types);  ?>

        <?php if ( function_exists('wpdevbk_generate_print_loyout')) wpdevbk_generate_print_loyout( $print_data );
    }


    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  P a g i n a t i o n     of    Booking Listing   //////////////////////////////////////////////////////
    function wpdevbk_show_pagination($summ_number_of_items, $active_page_num, $num_items_per_page , $only_these_parameters = false ) {
        if (empty( $num_items_per_page)) {
            $num_items_per_page = '10';
        }

        $pages_number = ceil ( $summ_number_of_items / $num_items_per_page );
        if ( $pages_number < 2 ) return;
        
        //Fix: 5.1.4 - Just in case we are having tooo much  resources, then we need to show all resources - and its empty string
        if ( ( isset($_REQUEST['wh_booking_type'] ) ) && ( strlen($_REQUEST['wh_booking_type']) > 1000 ) ) {                   
            $_REQUEST['wh_booking_type'] = '';            
        }  
        
        $bk_admin_url = get_params_in_url( array('page_num') , $only_these_parameters );
        
        ?>
        <div class="pagination pagination-centered" style="height:auto;">
          <ul>
              
            <?php if ($pages_number>1) { ?>
                <li <?php if ($active_page_num == 1) echo ' class="disabled" '; ?> >
                    <a href="<?php echo $bk_admin_url; ?>&page_num=<?php if ($active_page_num == 1) { echo $active_page_num; } else { echo ($active_page_num-1); } ?>">
                        <?php _e('Prev' ,'booking'); ?>
                    </a>
                </li>
            <?php } ?>
            
            <?php for ($pg_num = 1; $pg_num <= $pages_number; $pg_num++) { ?>

              <li <?php if ($pg_num == $active_page_num ) echo ' class="active" '; ?> >
                  <a href="<?php echo $bk_admin_url; ?>&page_num=<?php echo $pg_num; ?>">
                    <?php echo $pg_num; ?>
                  </a>
              </li>

            <?php } ?>

            <?php if ($pages_number>1) { ?>
                <li <?php if ($active_page_num == $pages_number) echo ' class="disabled" '; ?> >
                    <a href="<?php echo $bk_admin_url; ?>&page_num=<?php  if ($active_page_num == $pages_number) { echo $active_page_num; } else { echo ($active_page_num+1); } ?>">
                        <?php _e('Next' ,'booking'); ?>
                    </a>
                </li>
            <?php } ?>

          </ul>
        </div>
        <?php
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    // </editor-fold>
    

    
    // <editor-fold defaultstate="collapsed" desc="  C a l e n d a r    O v e r v i e w    I N T E R F A C E  ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // S T R U C T U R E   of   T O O L B A R for TABS and Buttons
    function wpdevbk_booking_calendar_overview_interface_header(){
        ?><div id="booking_listings_interface_header"><?php
            
            $_REQUEST['tab_cvm'] = 'actions_cvm'; // In calendar vew mode, we are have only one tab so need to activate it.

            wpdevbk_booking_calendar_overview_tabs_in_top_menu_line();

            if (! isset($_REQUEST['tab_cvm'])) $_REQUEST['tab_cvm'] = 'filter';
            $selected_title = $_REQUEST['tab_cvm'];

        ?>
            <div class="booking-submenu-tab-container" style="">
                <div class="nav-tabs booking-submenu-tab-insidecontainer">


                    <div class="visibility_container" id="actions"  style="<?php if ($selected_title == 'actions_cvm') { echo 'display:block;'; } else { echo 'display:none;'; }  ?>">
                        <?php wpdev_show_calendar_overview_interface(); ?>
                    </div>

                    <div class="visibility_container" id="help"     style="<?php if ($selected_title == 'help') { echo 'display:block;'; } else { echo 'display:none;'; }  ?>">
                    </div>

                </div>
            </div>
            <div style="height:1px;clear:both;margin-top:1px;"></div>
        </div>
        <div style="height:1px;clear:both;margin-top:15px;"></div>
        <?php
    }

    //  T A B s    in   Calendar overview  t o o l b a r
    function wpdevbk_booking_calendar_overview_tabs_in_top_menu_line() {

        $is_only_icons = ! true;
        if ($is_only_icons) echo '<style type="text/css"> #menu-wpdevplugin .nav-tab { padding:4px 2px 6px 32px !important; } </style>';
        $selected_icon = 'bc-16x16.png';

        if (! isset($_REQUEST['tab_cvm'])) $_REQUEST['tab_cvm'] = 'actions_cvm';
        $selected_title = $_REQUEST['tab_cvm'];

        ?>
         <div style="height:1px;clear:both;margin-top:30px;"></div>
         <div id="menu-wpdevplugin">
            <div class="nav-tabs-wrapper">
                <div class="nav-tabs">

                    <?php $title = __('Actions' ,'booking'); $my_icon = 'bc-16x16.png'; $my_tab = 'actions';  $my_additinal_class= ''; ?>
                    <?php if ($_REQUEST['tab_cvm'] == 'actions_cvm') {  $slct_a = 'selected'; $selected_title = $title; $selected_icon = $my_icon; } else {  $slct_a = ''; } ?><a class="nav-tab <?php if ($slct_a == 'selected') { echo ' nav-tab-active '; } echo $my_additinal_class;  ?>" title="<?php //echo __('Customization of booking form fields' ,'booking');  ?>"  href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').hide(); jQuery('#<?php echo $my_tab; ?>').show();jQuery('.nav-tab').removeClass('nav-tab-active');jQuery(this).addClass('nav-tab-active');"
                       ><i class="<?php if ($slct_a == 'selected') echo 'icon-white '; ?>icon-fire"></i><span class="nav-tab-text"> <?php /** ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><?php /**/ ?><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>

                    <?php wpdevbk_show_help_dropdown_menu_in_top_menu_line(); ?>

                </div>
            </div>
        </div>
        <?php

    }

    // B U T T O N S   In   Actions TAB from Toolbar
    function wpdev_show_calendar_overview_interface(){
            $user = wp_get_current_user(); $user_bk_id = $user->ID;
            
            if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
                    $is_show_resources_matrix = true;        
            else    $is_show_resources_matrix = false;
                    
            if (! isset($_REQUEST['view_days_num'])) $_REQUEST['view_days_num'] = get_bk_option( 'booking_view_days_num');
            //We do not have the Year (365) and (90) view modes in the Matrix mode so  we are set to the closest variant. And the same backward.
            if ( ($is_show_resources_matrix) ) { // Switching from the Single to Matrix mode.
                if ($_REQUEST['view_days_num'] == '365')  {
                    $_REQUEST['view_days_num'] = 60;
                }
                if ($_REQUEST['view_days_num'] == '90')  {
                    $_REQUEST['view_days_num'] = 7;
                }
            } else { // Switching from the Matrix to Single  mode.
                if ($_REQUEST['view_days_num'] == '60')  {
                    $_REQUEST['view_days_num'] = 365;
                }
                if ( ($_REQUEST['view_days_num'] == '7') || ($_REQUEST['view_days_num'] == '1')  )  {
                    $_REQUEST['view_days_num'] = 30;
                }
            }
            
            $view_days_num = $_REQUEST['view_days_num'];
            
            $bk_admin_url = get_params_in_url( array('view_days_num') );
          
            wpdev_calendar_overview_buttons_view_mode($bk_admin_url, $view_days_num);
            
            wpdev_calendar_overview_buttons_navigations();
          
            if (function_exists('wpdevbk_booking_resource_selection_for_calendar_overview')) {    // Booking resource selections
               wpdevbk_booking_resource_selection_for_calendar_overview();
            } ?><div class="clear" style="height:1px;"></div>
          <div id="admin_bk_messages" style="margin:0px;"> </div>
          <div class="clear" style="height:1px;"></div>
        <?php
    }

        // View  mode of calendar Buttons
        function wpdev_calendar_overview_buttons_view_mode($bk_admin_url, $view_days_num) {
            
        if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
                $is_show_resources_matrix = true;        
        else    $is_show_resources_matrix = false;
        
          if (! $is_show_resources_matrix) {  
          ?>
            <div class="btn-toolbar">
                <div id="calendar_overview_number_of_days_to_show" class="btn-group" data-toggle="buttons-radio">
                    <a     data-original-title="<?php _e('Show month' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_30"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=30'; ?>"
                           /><?php _e('Month' ,'booking'); ?> <i class="icon-align-justify"></i></a>
                    <a     data-original-title="<?php _e('Show 3 months' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_90"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=90'; ?>"
                           /><?php _e('3 Months' ,'booking'); ?> <i class="icon-th-list"></i></a>
                    <a     data-original-title="<?php _e('Show year' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_365"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=365'; ?>"
                           /><?php _e('Year' ,'booking'); ?> <i class="icon-th"></i></a>
                </div>
                <script type="text/javascript">
                    jQuery('#calendar_overview_number_of_days_to_show .button').button();
                    jQuery('#calendar_overview_number_of_days_to_show .button.btn_dn_<?php echo $view_days_num; ?>').button('toggle');
                </script>
                <p class="help-block"><?php _e('Calendar view mode' ,'booking'); ?></p>
          </div>
          <?php
          } else {
          ?>
            <div class="btn-toolbar">
                <div id="calendar_overview_number_of_days_to_show" class="btn-group" data-toggle="buttons-radio">
                    <a     data-original-title="<?php _e('Show day' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_1"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=1'; ?>"
                           /><?php _e('Day' ,'booking'); ?> <i class="icon-stop"></i></a>
                    <a     data-original-title="<?php _e('Show week' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_7"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=7'; ?>"
                           /><?php _e('Week' ,'booking'); ?> <i class="icon-th-large"></i></a>
                    <a     data-original-title="<?php _e('Show month' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_30"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=30'; ?>"
                           /><?php _e('Month' ,'booking'); ?> <i class="icon-th"></i></a>
                    <a     data-original-title="<?php _e('Show 2 months' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button button-secondary btn_dn_60"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=60'; ?>"
                           /><?php _e('2 Months' ,'booking'); ?> <i class="icon-th-list"></i></a>
                    <?php /*                           
                    <a     data-original-title="<?php _e('Show year' ,'booking'); ?>"  rel="tooltip" class="tooltip_top btn btn btn_dn_365"
                           onclick="javascript:;"
                           href="<?php echo $bk_admin_url . '&view_days_num=365'; ?>"
                           /><?php _e('Year' ,'booking'); ?> <i class="icon-align-justify"></i></a>
                     */?>
                </div>
                <script type="text/javascript">
                    jQuery('#calendar_overview_number_of_days_to_show .button').button();
                    jQuery('#calendar_overview_number_of_days_to_show .button.btn_dn_<?php echo $view_days_num; ?>').button('toggle');
                </script>
                <p class="help-block"><?php _e('Calendar view mode' ,'booking'); ?></p>
          </div>
          <?php
          }
        }

        // Navigation  Buttons
        function wpdev_calendar_overview_buttons_navigations() {

            if (isset($_REQUEST['view_days_num'])) $view_days_num = $_REQUEST['view_days_num'];
            else $view_days_num = get_bk_option( 'booking_view_days_num');

            if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
                    $is_show_resources_matrix = true;
            else    $is_show_resources_matrix = false;
            
            if (! $is_show_resources_matrix) {  
                switch ($view_days_num) {
                    case '90':
                        if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                        else $scroll_day = 0;
                        $scroll_params = array( '&scroll_day='.intval($scroll_day-4*7),
                                                '&scroll_day='.intval($scroll_day-7),
                                                '&scroll_day=0',
                                                '&scroll_day='.intval($scroll_day+7 ),
                                                '&scroll_day='.intval($scroll_day+4*7) );
                        $scroll_titles = array(  __('Previous 4 weeks' ,'booking'),
                                                 __('Previous week' ,'booking'),
                                                 __('Current week' ,'booking'),
                                                 __('Next week' ,'booking'),
                                                 __('Next 4 weeks' ,'booking') );
                        break;
                    case '30':
                        if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                        else $scroll_day = 0;
                        $scroll_params = array( '&scroll_day='.intval($scroll_day-4*7),
                                                '&scroll_day='.intval($scroll_day-7),
                                                '&scroll_day=0',
                                                '&scroll_day='.intval($scroll_day+7 ),
                                                '&scroll_day='.intval($scroll_day+4*7) );
                        $scroll_titles = array(  __('Previous 4 weeks' ,'booking'),
                                                 __('Previous week' ,'booking'),
                                                 __('Current week' ,'booking'),
                                                 __('Next week' ,'booking'),
                                                 __('Next 4 weeks' ,'booking') );
                        break;
                    default:  // 365
                        if (! isset($_REQUEST['scroll_month'])) $_REQUEST['scroll_month'] = 0;
                        $scroll_month = $_REQUEST['scroll_month'];
                        $scroll_params = array( '&scroll_month='.intval($scroll_month-3),
                                                '&scroll_month='.intval($scroll_month-1),
                                                '&scroll_month=0',
                                                '&scroll_month='.intval($scroll_month+1 ),
                                                '&scroll_month='.intval($scroll_month+3) );
                        $scroll_titles = array(  __('Previous 3 months' ,'booking'),
                                                 __('Previous month' ,'booking'),
                                                 __('Current month' ,'booking'),
                                                 __('Next month' ,'booking'),
                                                 __('Next 3 months' ,'booking') );
                        break;
                }
            } else { // Matrix
                  
                switch ($view_days_num) {
                    case '1': //Day
                        if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                        else $scroll_day = 0;
                        $scroll_params = array( '&scroll_day='.intval($scroll_day-7),
                                                '&scroll_day='.intval($scroll_day-1),
                                                '&scroll_day=0',
                                                '&scroll_day='.intval($scroll_day+1 ),
                                                '&scroll_day='.intval($scroll_day+7) );
                        $scroll_titles = array(  __('Previous 7 days' ,'booking'),
                                                 __('Previous day' ,'booking'),
                                                 __('Current day' ,'booking'),
                                                 __('Next day' ,'booking'),
                                                 __('Next 7 days' ,'booking') );
                        break;
                        
                    case '7': //Week
                        if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                        else $scroll_day = 0;
                        $scroll_params = array( '&scroll_day='.intval($scroll_day-4*7),
                                                '&scroll_day='.intval($scroll_day-7),
                                                '&scroll_day=0',
                                                '&scroll_day='.intval($scroll_day+7 ),
                                                '&scroll_day='.intval($scroll_day+4*7) );
                        $scroll_titles = array(  __('Previous 4 weeks' ,'booking'),
                                                 __('Previous week' ,'booking'),
                                                 __('Current week' ,'booking'),
                                                 __('Next week' ,'booking'),
                                                 __('Next 4 weeks' ,'booking') );
                        break;
                        
                    case '30':
                    case '60':    
                    case '90': //3 months
                        if (! isset($_REQUEST['scroll_month'])) $_REQUEST['scroll_month'] = 0;
                        $scroll_month = $_REQUEST['scroll_month'];
                        $scroll_params = array( '&scroll_month='.intval($scroll_month-3),
                                                '&scroll_month='.intval($scroll_month-1),
                                                '&scroll_month=0',
                                                '&scroll_month='.intval($scroll_month+1 ),
                                                '&scroll_month='.intval($scroll_month+3) );
                        $scroll_titles = array(  __('Previous 3 months' ,'booking'),
                                                 __('Previous month' ,'booking'),
                                                 __('Current month' ,'booking'),
                                                 __('Next month' ,'booking'),
                                                 __('Next 3 months' ,'booking') );
                        break;

                    default:  // 30, 60, 90...
                        if (! isset($_REQUEST['scroll_month'])) $_REQUEST['scroll_month'] = 0;
                        $scroll_month = $_REQUEST['scroll_month'];
                        $scroll_params = array( '&scroll_month='.intval($scroll_month-3),
                                                '&scroll_month='.intval($scroll_month-1),
                                                '&scroll_month=0',
                                                '&scroll_month='.intval($scroll_month+1 ),
                                                '&scroll_month='.intval($scroll_month+3) );
                        $scroll_titles = array(  __('Previous 3 months' ,'booking'),
                                                 __('Previous month' ,'booking'),
                                                 __('Current month' ,'booking'),
                                                 __('Next month' ,'booking'),
                                                 __('Next 3 months' ,'booking') );
                        break;
                }                
            }
            $bk_admin_url = get_params_in_url( array('scroll_month', 'scroll_day') );
          ?><div class="btn-toolbar">
                <div class="btn-group">
                    <a     data-original-title="<?php echo $scroll_titles[0]; ?>"  rel="tooltip" class="tooltip_top button button-secondary "
                           href="<?php echo $bk_admin_url .$scroll_params[0].''; ?>"       /><i class="icon-backward"></i></a>
                    <a     data-original-title="<?php echo $scroll_titles[1]; ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                           href="<?php echo $bk_admin_url .$scroll_params[1].''; ?>"        /><i class="icon-chevron-left"></i></a>

                    <?php 
                    
                    $bk_admin_url_today = get_params_in_url( array('scroll_month', 'scroll_day', 'scroll_start_date') );
                    wpdevbk_date_selection_for_navigation( 'start_date_selection_in_navigation', $bk_admin_url_today, $scroll_titles, $scroll_params); ?>       
<!--                    <a     data-original-title="<?php echo $scroll_titles[2]; ?>"  rel="tooltip" class="tooltip_top btn btn"
                           href="<?php echo $bk_admin_url .$scroll_params[2]; ?>"        /><i class="icon-screenshot"></i></a>-->

                    <a     data-original-title="<?php echo $scroll_titles[3]; ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                           href="<?php echo $bk_admin_url .$scroll_params[3].''; ?>"       /><i class="icon-chevron-right"></i></a>
                    <a     data-original-title="<?php echo $scroll_titles[4]; ?>"  rel="tooltip" class="tooltip_top button button-secondary"
                           href="<?php echo $bk_admin_url .$scroll_params[4].''; ?>"       /><i class="icon-forward"></i></a>
                </div>
                <p class="help-block" style="margin:27px 0 0;"><?php _e('Calendar Navigation' ,'booking'); ?></p>
          </div>

          <?php
        }


    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   Bookings Calendar Overview  --  T A B L E      /////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get  D A T E S  and  T I M E S  from  the B o o k i n g s    
    function get_dates_and_times_arrays_from_bookings( $bookings ){
        
        
        $fixed_time_hours_array = array( );
        for ($tt = 0; $tt < 24; $tt++) {
            $fixed_time_hours_array[ $tt * 60 * 60 ] = array();
        } /* Example: Array (     [0] => array(), [3600] =>  array(), [7200] => array(), ..... [43200] => array(),.... [82800] => array()   )*/


        // Dates array: { '2012-12-24' => array( Booking ID 1, Booking ID 2, ....), ... }
        $dates_array = $time_array = array();
        foreach ($bookings as $bk) {
            foreach ($bk->dates as $dt) {

                // Transform from MySQL date to PHP date
                $dt->booking_date = trim($dt->booking_date);
                $dta = explode(' ',$dt->booking_date);
                $tms = $dta[1];
                $tms = explode(':' , $tms);             // array('13','30','40')
                $dta = $dta[0];
                $dta = explode('-',$dta);               // array('2012','12','30')
                $php_dt = mktime($tms[0], $tms[1], $tms[2], $dta[1], $dta[2], $dta[0]) ;

                if ( ( isset($dt->type_id) ) && (! empty($dt->type_id)) ) 
                     $date_bk_res_id = $dt->type_id;
                else $date_bk_res_id = $bk->booking_type;
                
                
                $my_date  = date("Y-m-d", $php_dt);    // '2012-12-01';
                if (! isset( $dates_array[$my_date] )) { $dates_array[$my_date] = array(array('id'=>$bk->booking_id,'resource'=>$date_bk_res_id)); }
                else                                   { $dates_array[$my_date][] = array('id'=>$bk->booking_id,'resource'=>$date_bk_res_id); }

                $my_time  = date("H:i:s", $php_dt);    // '21:55:01';

                $my_time_index = explode(':',$my_time);
                $my_time_index = (int)($my_time_index[0]*60*60 + $my_time_index[1]*60 + $my_time_index[2]);

                if (! isset( $time_array[$my_date] )) { $time_array[$my_date] = array( $my_time_index => array($my_time =>array('id'=>$bk->booking_id,'resource'=>$date_bk_res_id)) ); }
                else {
                    if (! isset( $time_array[$my_date][$my_time_index] ) )
                        $time_array[$my_date][$my_time_index] = array($my_time =>array('id'=>$bk->booking_id,'resource'=>$date_bk_res_id));
                    else {
                        if (! isset( $time_array[$my_date][$my_time_index][$my_time] ) )
                            $time_array[$my_date][$my_time_index][$my_time] = array('id'=>$bk->booking_id,'resource'=>$date_bk_res_id) ;
                        else {
                            $my_time_inc = 3;
                            while ( isset( $time_array[$my_date][$my_time_index][$my_time + $my_time_inc ] ) ) {
                                $my_time_inc++;
                            }
                            $time_array[$my_date][$my_time_index][($my_time+$my_time_inc)] = array('id'=>$bk->booking_id,'resource'=>$date_bk_res_id) ; //Just in case if we are have the booking in the same time, so we are
                        }
                    }
               }

            }
        }
//debuge($time_array);
        // Sorting ..........
        foreach ($time_array as $key=>$value_t) {   // Sort the times from lower to higher
            ksort($value_t);
            $time_array[$key]=$value_t;
        }
        ksort($time_array);                         // Sort array by dates from lower to higher.
//debuge($time_array);
        /* $time_array:
         $key_date     $value_t
        [2012-12-13] => Array ( $tt_index          $times_bk_id_array
                                [44401] => Array ( [12:20:01] => 19)
                              ),
        [2012-12-14] => Array (
                                [10802] => Array([03:00:02] => 19),
                                [43801] => Array([12:10:01] => 2)
                               ),
                .... */

        $time_array_new = array();
        foreach ($time_array as $key_date=>$value_t) {   // fill the $time_array_new - by bookings of full dates....

            $new_times_array = $fixed_time_hours_array;             // Array ( [0] => Array, [3600] => Array, [7200] => Array .....

            foreach ($value_t as $tt_index=>$times_bk_id_array) {   //  [44401] => Array ( [12:20:01] => 19 ), .....
                
                $tt_index_round = floor( ($tt_index/60)/60 ) * 60 * 60;         // 14400, 18000,
                $is_bk_for_full_date = $tt_index % 10;                          // 0, 1, 2

                switch ($is_bk_for_full_date) {
                    case 0:                                                         // Full date - fill every time slot
                        foreach ($new_times_array as $round_time_slot=>$bk_id_array) {
                            $new_times_array[$round_time_slot] = array_merge( $bk_id_array , array_values($times_bk_id_array) );
                        }
                        unset($time_array[$key_date][$tt_index]);
                        break;

                    case 1:     break;
                    case 2:     break;
                    default:    break;
                }

            }
            if (count($time_array[$key_date])==0) unset($time_array[$key_date]) ;

            $time_array_new[$key_date]=$new_times_array;
        }
//debuge($time_array_new);
        
//debuge($time_array);        
        foreach ($time_array as $key_date=>$value_t) {
            $new_times_array_for_day_start = $new_times_array_for_day_end = array();
            foreach ($value_t as $tt_index=>$times_bk_id_array) {   //  [44401] => Array ( [12:20:01] => 19 ), .....

                $tt_index_round = floor( ($tt_index/60)/60 ) * 60 * 60;         // 14400, 18000,
//debuge($tt_index, $tt_index_round);                
                $is_bk_for_full_date = $tt_index % 10;                          // 0, 1, 2

                if ($is_bk_for_full_date==1) {
                    if (! isset($new_times_array_for_day_start[$tt_index_round])) $new_times_array_for_day_start[$tt_index_round] = array();
                    $new_times_array_for_day_start[$tt_index_round] = array_merge($new_times_array_for_day_start[$tt_index_round] , array_values($times_bk_id_array) );
                }
                if ($is_bk_for_full_date==2) {
                    
                    // Its mean that  the booking is finished exactly  at  the beginig of this hour, 
                    // so  we will not fill the end of booking in this hour, but in previous
                    if ( ($tt_index_round - $tt_index) == -2 )  {  
                        $tt_index_round = $tt_index_round - 60*60;
                    }  
                    
                    if (! isset($new_times_array_for_day_end[$tt_index_round])) $new_times_array_for_day_end[$tt_index_round] = array();
                    $new_times_array_for_day_end[$tt_index_round]   = array_merge($new_times_array_for_day_end[$tt_index_round] , array_values($times_bk_id_array) );
                }
            }
            $time_array[$key_date] = array('start'=>$new_times_array_for_day_start, 'end'=>$new_times_array_for_day_end);
        }
//debuge($time_array);        
            /* $time_array
            [2012-12-24] => Array
                (
                    [start] => Array (
                                        [68400] => Array ( [0] => 15 ) )
                    [end] => Array (
                                        [64800] => Array ( [0] => 6 ) )

                )    */
        $fill_this_date = array();
//debuge($time_array_new);
        foreach ($time_array_new as $ddate=>$ttime_round_array ) {
            foreach ($ttime_round_array as $ttime_round => $bk_id_array ) {

                if ( isset( $time_array[$ddate] )) {

                    if ( isset( $time_array[$ddate]['start'][$ttime_round]  )) // array
                          $fill_this_date = array_merge($fill_this_date, array_values( $time_array[$ddate]['start'][$ttime_round] ) );
//debuge($fill_this_date);
                    $time_array_new[$ddate][$ttime_round] = array_merge($time_array_new[$ddate][$ttime_round], $fill_this_date );

//debuge($ddate, $ttime_round, $time_array_new[$ddate][$ttime_round]);

                    if ( isset( $time_array[$ddate]['end'][$ttime_round]  )) // array
                        foreach ($time_array[$ddate]['end'][$ttime_round] as $toDelete) {
                        
                            if ( ! empty($fill_this_date) ) { 
                                $fill_this_date=array_diff($fill_this_date, array($toDelete));
                            }
                          
                        }
                          

                }
            }
        }

        return array( $dates_array, $time_array_new   );

    }    
        
        
    // B o o k i n g     C a l e n d a r    O v e r v i e w    T a b l e
    function booking_calendar_overview_table($bookings , $booking_types) {

        $bookings_date_time = get_dates_and_times_arrays_from_bookings( $bookings );
        $dates_array        = $bookings_date_time[0];
        $time_array_new     = $bookings_date_time[1];
//debuge($time_array_new);
//debuge($dates_array, $bookings, $booking_types, $time_array_new );
        wpdev_bk_timeline( $dates_array, $bookings, $booking_types, $time_array_new );
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////        
    // </editor-fold>


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   J S   and   C S S   for the  B o o k i n g  pages   ///////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpdevbk_booking_listing_write_js(){
        ?>
            <script type="text/javascript">
              jQuery(document).ready( function(){
                  
                function applyCSStoDays(date ){
                    return [true, 'date_available']; 
                }
                jQuery('input.wpdevbk-filters-section-calendar').datepick(
                    {   beforeShowDay: applyCSStoDays,
                        showOn: 'focus',
                        multiSelect: 0,
                        numberOfMonths: 1,
                        stepMonths: 1,
                        prevText: '&laquo;',
                        nextText: '&raquo;',
                        dateFormat: 'yy-mm-dd',
                        changeMonth: false,
                        changeYear: false,
                        minDate: null, 
                        maxDate: null, //'1Y',
                        showStatus: false,
                        multiSeparator: ', ',
                        closeAtTop: false,
                        firstDay:<?php echo get_bk_option( 'booking_start_day_weeek' ); ?>,
                        gotoCurrent: false,
                        hideIfNoPrevNext:true,
                        useThemeRoller :false,
                        mandatory: true
                    }
                );

                jQuery('a.popover_here').popover( {
                    placement: 'top'
                  , delay: { show: 100, hide: 200 }
                  , content: ''
                  , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                });
                jQuery('.popover_left').popover( {
                    placement: 'left'
                  , delay: { show: 100, hide: 500 }
                  , content: ''
                  , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                });
                jQuery('.popover_right').popover( {
                    placement: 'right'
                  , delay: { show: 100, hide: 200 }
                  , content: ''
                  , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                });
                jQuery('.popover_top').popover( {
                    placement: 'top'
                  , delay: { show: 100, hide: 200 }
                  , content: ''
                  , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                });
                jQuery('.popover_bottom').popover( {
                    placement: 'bottom'
                  //, trigger:'click'  
                  , delay: { show: 100, hide: 800 }
                  , content: ''
                  , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
                });
                
                // Repositioning of PopOver, which out of Window
                jQuery( ".popover_bottom" ).on( "mouseenter", function() {
                    setTimeout(function(){
                        if ( jQuery( '.wpdevbk.popover.fade.bottom.in' ).length ) {
                            var right_pos = parseInt( jQuery( '.wpdevbk.popover.fade.bottom.in' ).css('right').replace('px', '') ); 
                            var left_pos  = parseInt( jQuery( '.wpdevbk.popover.fade.bottom.in' ).css('left').replace('px', '') );

                            if ( ( left_pos < 0 ) ) {
                                jQuery('.wpdevbk.popover.fade.bottom.in').css( {left: "10px"} );
                            }
                            if ( ( right_pos < 0 ) ) {
                                jQuery('.wpdevbk.popover.fade.bottom.in').css( {left: ( ( left_pos - Math.abs(right_pos) - 10) + "px" ) } );
                            }

                            setTimeout(function(){
                                var right_pos = parseInt( jQuery( '.wpdevbk.popover.fade.bottom.in' ).css('right').replace('px', '') ); 
                                var left_pos  = parseInt( jQuery( '.wpdevbk.popover.fade.bottom.in' ).css('left').replace('px', '') );

                                if ( ( left_pos < 0 ) || ( right_pos<0 ) ) {
                                    jQuery('.wpdevbk.popover.fade.bottom.in').css({'left':'10px','width':'95%'}) ;
                                    jQuery('.wpdevbk.popover.fade.bottom.in .popover-inner').css({'width':'95%'}) ;
                                } else {
                                    jQuery('.wpdevbk.popover.fade.bottom.in').css({'width':'auto'}) ;
                                    jQuery('.wpdevbk.popover.fade.bottom.in .popover-inner').css({'width':'350px'}) ;
                                }
                            },5);
                        }
                     },110);   
                });
            <?php
            $is_use_hints = get_bk_option( 'booking_is_use_hints_at_admin_panel'  );
            if ($is_use_hints == 'On')
                if (  ( ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.php')) !== false) &&
                      (   ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation'))  === false)
                   ) { ?>

                jQuery('.tooltip_right').tooltip( {
                    animation: true
                  , delay: { show: 500, hide: 100 }
                  , selector: false
                  , placement: 'right'
                  , trigger: 'hover'
                  , title: ''
                  , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                jQuery('.tooltip_left').tooltip( {
                    animation: true
                  , delay: { show: 500, hide: 100 }
                  , selector: false
                  , placement: 'left'
                  , trigger: 'hover'
                  , title: ''
                  , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                jQuery('.tooltip_top').tooltip( {
                    animation: true
                  , delay: { show: 500, hide: 100 }
                  , selector: false
                  , placement: 'top'
                  , trigger: 'hover'
                  , title: ''
                  , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                jQuery('.tooltip_bottom').tooltip( {
                    animation: true
                  , delay: { show: 500, hide: 100 }
                  , selector: false
                  , placement: 'bottom'
                  , trigger: 'hover'
                  , title: ''
                  , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                jQuery('.tooltip_top_slow').tooltip( {
                    animation: true
                  , delay: { show: 2500, hide: 100 }
                  , selector: false
                  , placement: 'top'
                  , trigger: 'hover'
                  , title: ''
                  , template: '<div class="wpdevbk tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner"></div></div>'
                });

                <?php } ?>
                //jQuery('.dropdown-toggle').dropdown();

               });
              </script>
        <?php
    }

    function wpdevbk_booking_listing_write_css(){
        ?>
            <style type="text/css">
                #datepick-div .datepick-header {
                       width: 172px !important;
                }
                #datepick-div {
                    -border-radius: 3px;
                    -box-shadow: 0 0 2px #888888;
                    -webkit-border-radius: 3px;
                    -webkit-box-shadow: 0 0 2px #888888;
                    -moz-border-radius: 3px;
                    -moz-box-shadow: 0 0 2px #888888;
                    width: 172px !important;
                }
                #datepick-div .datepick .datepick-days-cell a{
                    font-size: 12px;
                }
                #datepick-div table.datepick tr td {
                    border-top: 0 none !important;
                    line-height: 24px;
                    padding: 0 !important;
                    width: 24px;
                }
                #datepick-div .datepick-control {
                    font-size: 10px;
                    text-align: center;
                }
                #datepick-div .datepick-one-month {
                    height: 215px;
                }
            </style>
        <?php
    }            
?>