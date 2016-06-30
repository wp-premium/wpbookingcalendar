<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

      
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Toolbar tab
    function wpdev_bk_payment_show_tab_in_top_settings_bank_transfer(){
        ?><a href="javascript:void(0)" onclick="javascript:
                jQuery('.visibility_container').css('display','none');
                jQuery('#visibility_container_bank_transfer').css('display','block');
                jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                jQuery(this).addClass('booking-submenu-tab-selected');"
           rel="tooltip"
           class="tooltip_bottom nav-tab  booking-submenu-tab booking-submenu-tab-selected <?php
                   if ( get_bk_option( 'booking_bank_transfer_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
           original-title="<?php _e('Integration of Bank Transfer payment system' ,'booking');?>" >
           <?php _e('Bank Transfer' ,'booking');?>
           <input type="checkbox" <?php if ( get_bk_option( 'booking_bank_transfer_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
                   name="bank_transfer_is_active_dublicated" id="bank_transfer_is_active_dublicated"
                   onchange="document.getElementById('bank_transfer_is_active').checked=this.checked;" >
        </a>
        <script type="text/javascript">
            jQuery(document).ready( function(){
                recheck_active_itmes_in_top_menu('bank_transfer_is_active', 'bank_transfer_is_active_dublicated');
            });
        </script>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_bank_transfer');


    // Settings page for Bank Transfer
    function wpdev_bk_payment_show_settings_content_bank_transfer(){
        
        $bank_transfer_account_fields = array( 'bank_transfer_account_name' => '', 
                                               'bank_transfer_account_number' => '', 
                                               'bank_transfer_bank_name' => '', 
                                               'bank_transfer_sort_code' => '', 
                                               'bank_transfer_iban' => '', 
                                               'bank_transfer_bic' => '' 
                                             );
        
        if ( ( isset( $_POST['bank_transfer_description'] ) )  ) {

            $accounts_number = 0;
            
            // Reset sort order of "Bank Accounts" - to have correct keys of array.            
            foreach ( $bank_transfer_account_fields as $account_field => $field_value) {
                if ( isset( $_POST[ $account_field ] ) ) {
                    $_POST[ $account_field ] = array_values( $_POST[ $account_field ] );
                    $accounts_number = count( $_POST[ $account_field ] );
                }
            }
            
            $booking_bank_transfer_accounts = array( );
            for ( $i = 0; $i < $accounts_number; $i++ ) {
                $booking_bank_transfer_accounts[$i] = array();
                foreach ( $bank_transfer_account_fields as $account_field => $field_value) {
                    if (  ( isset( $_POST[ $account_field ] ) ) && ( isset( $_POST[ $account_field ][$i] ) )  ){                        
                        $booking_bank_transfer_accounts[$i][ $account_field ] = $_POST[ $account_field ][$i];
                    }
                }                
            }

            update_bk_option( 'booking_bank_transfer_is_active'     , ( (isset( $_POST['bank_transfer_is_active'] ))?'On':'Off' ) );
            update_bk_option( 'booking_bank_transfer_description'   , $_POST['bank_transfer_description'] );
            update_bk_option( 'booking_bank_transfer_account_name_title'    , $_POST['bank_transfer_account_name_title'] );
            update_bk_option( 'booking_bank_transfer_account_number_title'  , $_POST['bank_transfer_account_number_title'] );
            update_bk_option( 'booking_bank_transfer_bank_name_title'       , $_POST['bank_transfer_bank_name_title'] );
            update_bk_option( 'booking_bank_transfer_sort_code_title'       , $_POST['bank_transfer_sort_code_title'] );
            update_bk_option( 'booking_bank_transfer_iban_title'            , $_POST['bank_transfer_iban_title'] );
            update_bk_option( 'booking_bank_transfer_bic_title'             , $_POST['bank_transfer_bic_title'] );
            
            update_bk_option( 'booking_bank_transfer_accounts'      , serialize( $booking_bank_transfer_accounts ) );
        }
        
        $bank_transfer_is_active    =  get_bk_option( 'booking_bank_transfer_is_active' );
        $bank_transfer_description  =  get_bk_option( 'booking_bank_transfer_description' );
        // Replace <br> to  <br> with  new line
        $bank_transfer_description = preg_replace( array( "@(&lt;|<)br/?(&gt;|>)(\r\n)?@" )
                                                        , array( "<br/>" )
                                                        , $bank_transfer_description );
        
        $bank_transfer_account_name_title   =  get_bk_option( 'booking_bank_transfer_account_name_title' );
        $bank_transfer_account_number_title =  get_bk_option( 'booking_bank_transfer_account_number_title' );
        $bank_transfer_bank_name_title      =  get_bk_option( 'booking_bank_transfer_bank_name_title' );
        $bank_transfer_sort_code_title      =  get_bk_option( 'booking_bank_transfer_sort_code_title' );
        $bank_transfer_iban_title           =  get_bk_option( 'booking_bank_transfer_iban_title' );
        $bank_transfer_bic_title            =  get_bk_option( 'booking_bank_transfer_bic_title' );        
        
        $booking_bank_transfer_accounts  = get_bk_option( 'booking_bank_transfer_accounts' );
                 

        if ( is_serialized( $booking_bank_transfer_accounts ) )   
            $booking_bank_transfer_accounts = unserialize( $booking_bank_transfer_accounts );

        if ( empty( $booking_bank_transfer_accounts ) ) {
            $booking_bank_transfer_accounts = array( $bank_transfer_account_fields );   // Default values
        }
        
        ?>
        <div id="visibility_container_bank_transfer" class="visibility_container" style="display:block;">
         <div class='meta-box'>
          <div <?php $my_close_open_win_id = 'bk_settings_costs_bank_transfer_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <!--div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div-->
           <h3 class='hndle'><span><?php _e('Bank Transfer customization' ,'booking'); ?></span></h3>
           <div class="inside" style="margin:0px;">
               
            <table class="visibility_bank_transfer_account_settings form-table settings-table0">
                <tbody>

                    <tr>
                        <td colspan="2">                          
                          <div  class="wpbc-help-message">
                            <?php printf(__('Allow payments by %sdirect bank / wire transfer%s' ,'booking'),'<b>','</b>');?>                            
                          </div>                          
                        </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Bank Transfer active' ,'booking'); ?>:</th>
                        <td>
                            <fieldset>
                                <label for="bank_transfer_is_active" >
                                    <input <?php if ($bank_transfer_is_active == 'On') echo "checked"; ?>
                                        value="<?php echo $bank_transfer_is_active; ?>" name="bank_transfer_is_active" id="bank_transfer_is_active" type="checkbox"
                                        onchange="document.getElementById('bank_transfer_is_active_dublicated').checked=this.checked;"
                                  /><?php _e('Check this box to use Bank Transfer' ,'booking');?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                   
                    
                    <tr valign="top">
                        <th scope="row">
                            <label for="bank_transfer_paymentaction" ><?php _e('Account details' ,'booking'); ?>:</label>
                        </th>

                        <td class="forminp" id="wpbc_bank_transfer_accounts">
                            <table class="widefat wpbc_input_table sortable" cellspacing="0" cellpadding="0">
                                <thead>
                                    <tr>
                                        <th class="sort">&nbsp;</th>
                                        <th><input type="text" value="<?php echo esc_js( $bank_transfer_account_name_title ); ?>" name="bank_transfer_account_name_title" /></th>
                                        <th><input type="text" value="<?php echo esc_js( $bank_transfer_account_number_title ); ?>" name="bank_transfer_account_number_title" /></th>
                                        <th><input type="text" value="<?php echo esc_js( $bank_transfer_bank_name_title ); ?>" name="bank_transfer_bank_name_title" /></th>
                                        <th><input type="text" value="<?php echo esc_js( $bank_transfer_sort_code_title ); ?>" name="bank_transfer_sort_code_title" /></th>
                                        <th><input type="text" value="<?php echo esc_js( $bank_transfer_iban_title ); ?>" name="bank_transfer_iban_title" /></th>
                                        <th><input type="text" value="<?php echo esc_js( $bank_transfer_bic_title ); ?>" name="bank_transfer_bic_title" /></th>
                                    </tr>
                                </thead>
                                <tbody class="accounts">
                                    <?php
                                    $i = -1;
                                    if ( isset( $booking_bank_transfer_accounts ) ) {
                                      
                                        foreach ( $booking_bank_transfer_accounts as $account ) {                                            
                                            $i++;

                                            echo '<tr class="account">
                                                    <td class="sort"></td>
                                                    <td><legend class="wpbc_mobile_legend">'. esc_js( $bank_transfer_account_name_title ) .':</legend><input type="text" value="' . esc_attr( wp_unslash( $account['bank_transfer_account_name'] ) ) . '" name="bank_transfer_account_name[' . $i . ']" /></td>
                                                    <td><legend class="wpbc_mobile_legend">'. esc_js( $bank_transfer_account_number_title ) .':</legend><input type="text" value="' . esc_attr( $account['bank_transfer_account_number'] ) . '" name="bank_transfer_account_number[' . $i . ']" /></td>
                                                    <td><legend class="wpbc_mobile_legend">'. esc_js( $bank_transfer_bank_name_title ) .':</legend><input type="text" value="' . esc_attr( wp_unslash( $account['bank_transfer_bank_name'] ) ) . '" name="bank_transfer_bank_name[' . $i . ']" /></td>
                                                    <td><legend class="wpbc_mobile_legend">'. esc_js( $bank_transfer_sort_code_title ) .':</legend><input type="text" value="' . esc_attr( $account['bank_transfer_sort_code'] ) . '" name="bank_transfer_sort_code[' . $i . ']" /></td>
                                                    <td><legend class="wpbc_mobile_legend">'. esc_js( $bank_transfer_iban_title ) .':</legend><input type="text" value="' . esc_attr( $account['bank_transfer_iban'] ) . '" name="bank_transfer_iban[' . $i . ']" /></td>
                                                    <td><legend class="wpbc_mobile_legend">'. esc_js( $bank_transfer_bic_title ) .':</legend><input type="text" value="' . esc_attr( $account['bank_transfer_bic'] ) . '" name="bank_transfer_bic[' . $i . ']" /></td>
                                            </tr>';
                                        }
                                    }
                                    ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th colspan="7"><a href="#" class="add button"><?php _e( '+ Add Account' ,'booking'); ?></a> <a href="#" class="remove_rows button"><?php _e( 'Remove selected account(s)' ,'booking'); ?></a></th>
                                    </tr>
                                </tfoot>
                            </table>
                            <script type="text/javascript">
                                ( function( $ ){
                                    jQuery('#wpbc_bank_transfer_accounts').on( 'click', 'a.add', function(){

                                        var size = jQuery('#wpbc_bank_transfer_accounts tbody .account').size();

                                        jQuery('<tr class="account">\
                                                    <td class="sort"></td>\
                                                    <td><legend class="wpbc_mobile_legend"><?php echo esc_js( $bank_transfer_account_name_title ); ?>:</legend><input type="text" name="bank_transfer_account_name[' + size + ']" /></td>\
                                                    <td><legend class="wpbc_mobile_legend"><?php echo esc_js( $bank_transfer_account_number_title ); ?>:</legend><input type="text" name="bank_transfer_account_number[' + size + ']" /></td>\
                                                    <td><legend class="wpbc_mobile_legend"><?php echo esc_js( $bank_transfer_bank_name_title ); ?>:</legend><input type="text" name="bank_transfer_bank_name[' + size + ']" /></td>\
                                                    <td><legend class="wpbc_mobile_legend"><?php echo esc_js( $bank_transfer_sort_code_title ); ?>:</legend><input type="text" name="bank_transfer_sort_code[' + size + ']" /></td>\
                                                    <td><legend class="wpbc_mobile_legend"><?php echo esc_js( $bank_transfer_iban_title ); ?>:</legend><input type="text" name="bank_transfer_iban[' + size + ']" /></td>\
                                                    <td><legend class="wpbc_mobile_legend"><?php echo esc_js( $bank_transfer_bic_title ); ?>:</legend><input type="text" name="bank_transfer_bic[' + size + ']" /></td>\
                                                </tr>').appendTo('#wpbc_bank_transfer_accounts table tbody');
                                                            
                                        jQuery('.wpbc_input_table tbody th, #wpbc_bank_transfer_accounts tbody td').css('cursor','move');
                                        return false;
                                    });
                                    
                                    $( document ).ready(function(){
                                        
                                        $('.wpbc_input_table tbody th, #wpbc_bank_transfer_accounts tbody td').css('cursor','move');

                                        $('.wpbc_input_table tbody td.sort').css('cursor','move');
                                        
                                        $('.wpbc_input_table.sortable tbody').sortable({
                                                items:'tr',
                                                cursor:'move',
                                                axis:'y',
                                                scrollSensitivity:40,
                                                forcePlaceholderSize: true,
                                                helper: 'clone',
                                                opacity: 0.65,
                                                placeholder: '#wpbc_bank_transfer_accounts .sort',
                                                start:function(event,ui){
                                                        ui.item.css('background-color','#f6f6f6');
                                                },
                                                stop:function(event,ui){
                                                        ui.item.removeAttr('style');
                                                }
                                        });
                                    });
                                    
                                    $('.wpbc_input_table .remove_rows').click(function() {
                                            var $tbody = $(this).closest('.wpbc_input_table').find('tbody');
                                            if ( $tbody.find('tr.current').size() > 0 ) {
                                                    $current = $tbody.find('tr.current');

                                                    $current.each(function(){
                                                            $(this).remove();
                                                    });
                                            }
                                            return false;
                                    });
                                    
                                    

                                    var controlled = false;
                                    var shifted = false;
                                    var hasFocus = false;

                                    $(document).bind('keyup keydown', function(e){ shifted = e.shiftKey; controlled = e.ctrlKey || e.metaKey } );

                                    $('.wpbc_input_table').on( 'focus click', 'input', function( e ) {

                                            $this_table = $(this).closest('table');
                                            $this_row   = $(this).closest('tr');

                                            if ( ( e.type == 'focus' && hasFocus != $this_row.index() ) || ( e.type == 'click' && $(this).is(':focus') ) ) {

                                                    hasFocus = $this_row.index();

                                                    if ( ! shifted && ! controlled ) {
                                                            $('tr', $this_table).removeClass('current').removeClass('last_selected');
                                                            $this_row.addClass('current').addClass('last_selected');
                                                    } else if ( shifted ) {
                                                            $('tr', $this_table).removeClass('current');
                                                            $this_row.addClass('selected_now').addClass('current');

                                                            if ( $('tr.last_selected', $this_table).size() > 0 ) {
                                                                    if ( $this_row.index() > $('tr.last_selected, $this_table').index() ) {
                                                                            $('tr', $this_table).slice( $('tr.last_selected', $this_table).index(), $this_row.index() ).addClass('current');
                                                                    } else {
                                                                            $('tr', $this_table).slice( $this_row.index(), $('tr.last_selected', $this_table).index() + 1 ).addClass('current');
                                                                    }
                                                            }

                                                            $('tr', $this_table).removeClass('last_selected');
                                                            $this_row.addClass('last_selected');
                                                    } else {
                                                            $('tr', $this_table).removeClass('last_selected');
                                                            if ( controlled && $(this).closest('tr').is('.current') ) {
                                                                    $this_row.removeClass('current');
                                                            } else {
                                                                    $this_row.addClass('current').addClass('last_selected');
                                                            }
                                                    }

                                                    $('tr', $this_table).removeClass('selected_now');

                                            }
                                    }).on( 'blur', 'input', function( e ) {
                                            hasFocus = false;
                                    });

                                    
                                }( jQuery ) );

                            </script>
                        </td>

                    </tr>
                                          
                    <tr valign="top">
                        <th scope="row">
                            <label for="bank_transfer_description" ><?php _e( 'Description' ,'booking'); ?>:</label>
                        </th>
                        <td>
                            <?php /**/
                                    wp_editor( $bank_transfer_description, 
                                       'bank_transfer_description',  
                                       array(
                                             'wpautop'       => false
                                           , 'media_buttons' => false
                                           , 'textarea_name' => 'bank_transfer_description'
                                           , 'textarea_rows' => 5
                                           , 'default_editor' => 'html'
                                           , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                           , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                           , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                           )
                                     ); /** ?>                                    
                            <textarea id="bank_transfer_description" name="bank_transfer_description"
                                      rows="5" style="width:100%" ><?php echo $bank_transfer_description; ?></textarea> <?php /**/ ?>
                            <p class="description"> <?php _e( 'Payment method description that the customer will see on your payment page.' ,'booking'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <td></td>
                        <td>
                        <?php 
                            $skip_shortcodes = array(); 
                            $extend_shortcodes = array( 'account_details', 'account_name', 'account_number', 'bank_name', 'sort_code', 'iban', 'bic' );
                            
                            wpbc_payment_help_section( $skip_shortcodes, $extend_shortcodes );
                        ?>                                                      
                        </td>
                    </tr>                            
                    
                </tbody>
            </table>

            <div class="clear" style="height:10px;"></div>
            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_form"/>
            <div class="clear" style="height:10px;"></div>

           </div>
          </div>
         </div>
        </div>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_bank_transfer');

    
        function wpbc_get_sort_code_label() {
            
            return array(
                           'AU' => __( 'BSB' ,'booking') ,
                           'CA' => __( 'Bank Transit Number' ,'booking'),
                           'IN' => __( 'IFSC' ,'booking'),
                           'IT' => __( 'Branch Sort' ,'booking'), 
                           'NZ' => __( 'Bank Code' ,'booking'), 
                           'SE' => __( 'Bank Code' ,'booking'), 
                           'US' => __( 'Routing Number' ,'booking'),
                           'ZA' => __( 'Branch Code' ,'booking')
                       );
        }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_define_payment_form_bank_transfer($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ){
        
        $output = '';

        $is_show_in_payment_request = false;
    //        if (       (get_bk_option( 'booking_authorizenet_is_active' ) != 'On') 
    //                && (get_bk_option( 'booking_sage_is_active' ) != 'On')
    //                && (get_bk_option( 'booking_ipay88_is_active' ) != 'On')
    //                && (isset($_GET['booking_pay']))
    //           ) $is_show_in_payment_request = true;

        if ( ( get_bk_option( 'booking_bank_transfer_is_active' ) == 'On' ) || ( $is_show_in_payment_request ) ) {

            
            // Accounts ////////////////////////////////////////////////////////
            $bank_transfer_account_name_title   =  get_bk_option( 'booking_bank_transfer_account_name_title' );
            $bank_transfer_account_number_title =  get_bk_option( 'booking_bank_transfer_account_number_title' );
            $bank_transfer_bank_name_title      =  get_bk_option( 'booking_bank_transfer_bank_name_title' );
            $bank_transfer_sort_code_title      =  get_bk_option( 'booking_bank_transfer_sort_code_title' );
            $bank_transfer_iban_title           =  get_bk_option( 'booking_bank_transfer_iban_title' );
            $bank_transfer_bic_title            =  get_bk_option( 'booking_bank_transfer_bic_title' );        

            
            $booking_bank_transfer_accounts  = get_bk_option( 'booking_bank_transfer_accounts' );
            if ( is_serialized( $booking_bank_transfer_accounts ) )   
                $booking_bank_transfer_accounts = unserialize( $booking_bank_transfer_accounts );

            if ( empty( $booking_bank_transfer_accounts ) ) {
                $booking_bank_transfer_accounts = array( $bank_transfer_account_fields );   // Default values
            }
            list( $account_name, $account_number, $bank_name, $sort_code, $iban, $bic ) = array('','','','','','');
            $bank_transfer_accounts = 
                '<table class="wpbc_bank_transfer_accounts" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>' . esc_js( $bank_transfer_bank_name_title ) . '</th>
                        <th>' . esc_js( $bank_transfer_account_number_title ) . '</th>
                        <th>' . esc_js( $bank_transfer_sort_code_title ) . '</th>
                        <th>' . esc_js( $bank_transfer_iban_title ) . '</th>
                        <th>' . esc_js( $bank_transfer_bic_title ) . '</th>
                    </tr>
                </thead>
                <tbody class="accounts">';
                $i = -1;
                if ( isset( $booking_bank_transfer_accounts ) ) {

                    foreach ( $booking_bank_transfer_accounts as $account ) {                                            
                        $i++;

                        $bank_transfer_accounts .=  
                        '<tr class="account">
                                <td>' . esc_attr( wp_unslash( $account['bank_transfer_bank_name'] ) ) . '</td>
                                <td>' . esc_attr( $account['bank_transfer_account_number'] ) . '</td>
                                <td>' . esc_attr( $account['bank_transfer_sort_code'] ) . '</td>
                                <td>' . esc_attr( $account['bank_transfer_iban'] ) . '</td>
                                <td>' . esc_attr( $account['bank_transfer_bic'] ) . '</td>
                        </tr>';
                        if ( empty( $account_number ) )
                            list( $account_name, $account_number, $bank_name, $sort_code, $iban, $bic ) = array_values( $account );
                    }
                }
            
            $bank_transfer_accounts .= '</tbody></table>';

            $bank_transfer_accounts = esc_js( $bank_transfer_accounts );  
            $bank_transfer_accounts = html_entity_decode( $bank_transfer_accounts );
            $bank_transfer_accounts = str_replace( "\\n", '', $bank_transfer_accounts );

            ////////////////////////////////////////////////////////////////////
            
            $bank_transfer_description = get_bk_option( 'booking_bank_transfer_description' );

            // Dates Shortcodes
            $booking_dates_str = get_dates_str( $booking_id );

            if ( get_bk_option( 'booking_date_view_type') == 'short' ) 
                $my_dates_4_send = get_dates_short_format( $booking_dates_str );
            else                                                     
                $my_dates_4_send = change_date_format( $booking_dates_str );

            $my_dates4emeil_check_in_out = explode(',', $booking_dates_str );

            $my_check_in_date  = change_date_format( $my_dates4emeil_check_in_out[0] );
            $my_check_out_date = change_date_format( $my_dates4emeil_check_in_out[ count( $my_dates4emeil_check_in_out )-1 ] );


            // Cost Shortcodes
            // $cost_currency = apply_bk_filter( 'get_currency_info', 'bank_transfer' );        
            $summ_show = wpdev_bk_cost_number_format( $summ );

            // Resource title
            $booking_resource_title = '';
            if ( is_array( $bk_title ) && ( count( $bk_title ) > 0 ) && is_object( $bk_title[0] )  ) {
                $booking_resource_title = $bk_title[0]->title;
            }

            $booking_form_show = get_form_content(  $bkform,
                                                    $booking_type,
                                                    '',
                                                    array(
                                                        'booking_id'=> $booking_id ,
                                                        'id'=> $booking_id ,
                                                        'dates'=> $my_dates_4_send,
                                                        'check_in_date' => $my_check_in_date,
                                                        'check_out_date' => $my_check_out_date,
                                                        'dates_count' => count( $my_dates4emeil_check_in_out ),
                                                        'cost' => $summ_show, // (isset($res->cost))?$res->cost:'',
                                                        'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $booking_resource_title ),
                                                        'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $booking_resource_title ),
                                                        'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                        'current_time' => date_i18n(get_bk_option( 'booking_time_format') ),
                                                        'account_details' => $bank_transfer_accounts,
                                                        'account_name'  => $account_name, 
                                                        'account_number'  => $account_number, 
                                                        'bank_name'     => $bank_name, 
                                                        'sort_code'     => $sort_code, 
                                                        'iban'          => $iban, 
                                                        'bic'           => $bic 
                                                        )
                                                  );

            $bank_transfer_description =  apply_bk_filter('wpdev_check_for_active_language', $bank_transfer_description ); 

            $bank_transfer_description = str_replace( '[content]', $booking_form_show['content'], $bank_transfer_description );

            $bank_transfer_description = replace_bk_shortcodes_in_form( $bank_transfer_description, $booking_form_show['_all_fields_'], true );

            $bank_transfer_description = esc_js( $bank_transfer_description );  
            $bank_transfer_description = html_entity_decode( $bank_transfer_description );
            $bank_transfer_description = str_replace( "\\n", '', $bank_transfer_description );

            
            
            $output  = '<div style="width:100%;clear:both;margin-top:20px;"></div>';
            $output .= '<div class="bank_transfer_div wpbc-payment-form" style="text-align:left;clear:both;">';
            
            $output .= $bank_transfer_description ;
            


            $output .= '</div>';
        }
        return $output;

    }
    add_bk_filter('wpdev_bk_define_payment_form_bank_transfer', 'wpdev_bk_define_payment_form_bank_transfer');
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // OK
    function wpbc_add_payment_status_ok__bank_transfer( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array( 'BankTransfer:OK' )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_ok',  'wpbc_add_payment_status_ok__bank_transfer');
    
    // Pending
    function wpbc_add_payment_status_pending__bank_transfer( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_pending',  'wpbc_add_payment_status_pending__bank_transfer');
    
    // Unknown
    function wpbc_add_payment_status_unknown__bank_transfer( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_unknown',  'wpbc_add_payment_status_unknown__bank_transfer');
    
    // Error
    function wpbc_add_payment_status_error__bank_transfer( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array( 'BankTransfer:Failed' )  );
       
       return  $payment_status;        
    }    
    add_filter('wpbc_add_payment_status_error',    'wpbc_add_payment_status_error__bank_transfer');    
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    // None
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Activate
    function wpdev_bk_payment_activate_system_bank_transfer() {
        
        $locale = apply_filters( 'plugin_locale',  get_locale() ,'booking');
        if ( strpos( $locale, '_' ) !== false ) {
            $locale = substr($locale, ( strpos( $locale, '_' ) + 1 ) );
        }
        $sort_code = wpbc_get_sort_code_label();
        if ( isset( $sort_code[$locale] ) )
            $sort_code = $sort_code[$locale];
        else 
            $sort_code = __('Sort Code' ,'booking');
        
        
        add_bk_option( 'booking_bank_transfer_is_active'            , 'Off' );
        add_bk_option( 'booking_bank_transfer_description', 
                        sprintf( __( 'Dear %sMake your payment %s directly into our bank account. %sPlease use your Booking ID %s as the payment reference! %s %s: %s %s: %s %s: %s %s: %s' ,'booking'),
                                '[name]<br/>' ,
                                '<strong>$[cost]</strong>',
                                '<br/>',
                                '<strong>[id]</strong>',
                                '<br/><br/><strong>[bank_name]</strong><br/>',
                                __('Account Number' ,'booking'), '<strong>[account_number]</strong><br/>', 
                                $sort_code, '<strong>[sort_code]</strong><br/>',
                                __('IBAN' ,'booking'), '<strong>[iban]</strong><br/>', 
                                __('BIC / Swift' ,'booking'), '<strong>[bic]</strong><br/><br/>'
                                )
                     );    
                       //, __('Make your payment directly into our bank account. Please use your Booking ID as the payment reference.' ,'booking') );
        
        add_bk_option( 'booking_bank_transfer_account_name_title'   , __('Account Name' ,'booking')  );
        add_bk_option( 'booking_bank_transfer_account_number_title' , __('Account Number' ,'booking')  );
        add_bk_option( 'booking_bank_transfer_bank_name_title'      , __('Bank Name' ,'booking')  );
        add_bk_option( 'booking_bank_transfer_sort_code_title'      , $sort_code  );
        add_bk_option( 'booking_bank_transfer_iban_title'           , __('IBAN' ,'booking')  );
        add_bk_option( 'booking_bank_transfer_bic_title'            , __('BIC / Swift' ,'booking')  );

        add_bk_option( 'booking_bank_transfer_accounts'      , serialize( array() ) );
    }
    add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_bank_transfer');


    // Activate
    function wpdev_bk_payment_deactivate_system_bank_transfer() {
        
        delete_bk_option( 'booking_bank_transfer_is_active' );
        delete_bk_option( 'booking_bank_transfer_description' );
        
        delete_bk_option( 'booking_bank_transfer_account_name_title' );
        delete_bk_option( 'booking_bank_transfer_account_number_title' );
        delete_bk_option( 'booking_bank_transfer_bank_name_title' );
        delete_bk_option( 'booking_bank_transfer_sort_code_title' );
        delete_bk_option( 'booking_bank_transfer_iban_title' );
        delete_bk_option( 'booking_bank_transfer_bic_title' );

        delete_bk_option( 'booking_bank_transfer_accounts' );
    }
    add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_bank_transfer');
?>