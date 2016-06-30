<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S u p p o r t    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Get COUNT of booking resources.
        function get_booking_resources_count(){
            global $wpdb;
            $sql_count  = " SELECT COUNT(*) as count FROM {$wpdb->prefix}bookingtypes as bt" ;

            $where = '';
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') $where = ' WHERE ' . $where;
            if ( class_exists('wpdev_bk_biz_l')) {
                if ($where != '')   $where .= ' AND bt.parent = 0 ';
                else                $where .= ' WHERE bt.parent = 0 ';
            }
            if (isset($_REQUEST['wh_resource_id'])) {
                 if ($where == '') $where .= " WHERE " ;
                 else $where .= " AND ";
                 
                 
                 $sql_wh_resource_id    = intval( $_REQUEST['wh_resource_id'] );
                 $sql_wh_resource_title = wpbc_clean_string_for_db( $_REQUEST['wh_resource_id'] );
                 $where .= " ( (bt.booking_type_id = '{$sql_wh_resource_id}') OR (bt.title like '%%{$sql_wh_resource_title}%%') ) ";
            }

            $booking_resources_count = $wpdb->get_results(  $sql_count . $where  );
            return $booking_resources_count[0]->count;
        }

        function get_booking_types_all_parents_and_single(){ global $wpdb;

            $sql = " SELECT * FROM {$wpdb->prefix}bookingtypes as bt" ;
            $or_sort = 'title';
            //$or_sort = 'booking_type_id_asc';
            $where = '';                                                        // Where for the different situation: BL and MU
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') $where = ' WHERE ' . $where;
            if ( class_exists('wpdev_bk_biz_l')) {
                if ($where != '')   $where .= ' AND bt.parent = 0 ';
                else                $where .= ' WHERE bt.parent = 0 ';
                $or_sort = 'prioritet';
            }

            if (strpos($or_sort, '_asc') !== false) {                            // Order
                   $or_sort = str_replace('_asc', '', $or_sort);
                   $sql_order = " ORDER BY " .$or_sort ." ASC ";
            } else $sql_order = " ORDER BY " .$or_sort ." DESC ";

            $types_list = $wpdb->get_results(  $sql .  $where. $sql_order  );
            return  $types_list;
        }

        // Get booking types from DB
        function get_bk_types($is_use_filter = false , $is_use_limit = true ) { global $wpdb;

            ////////////////////////////////////////////////////////////////////////
            // CONSTANTS
            ////////////////////////////////////////////////////////////////////////
            /*update_bk_option( 'booking_resourses_num_per_page',3);
            $defaults = array(
                    'page_num' => '1',
                    'page_items_count' => get_bk_option( 'booking_resourses_num_per_page')
            );

            $r = wp_parse_args( $args, $defaults );
            extract( $r, EXTR_SKIP );
            /**/
            $page_num         = (isset($_REQUEST['page_num']))?$_REQUEST['page_num']:1;         // Pagination
            $page_items_count = get_bk_option( 'booking_resourses_num_per_page');
            $page_start = ( $page_num - 1 ) * $page_items_count ;


            $sql = " SELECT * FROM {$wpdb->prefix}bookingtypes as bt" ;
            $or_sort = 'title';
            $or_sort = 'title_asc';
            $where = '';                                                        // Where for the different situation: BL and MU
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') $where = ' WHERE ' . $where;
            if ( class_exists('wpdev_bk_biz_l')) {
                if ($where != '')   $where .= ' AND bt.parent = 0 ';
                else                $where .= ' WHERE bt.parent = 0 ';
                $or_sort = 'prioritet';
            }

            if (isset($_REQUEST['wh_resource_id'])) {
                 if ($where == '') $where .= " WHERE " ;
                 else $where .= " AND ";
                 $where .= " ( (bt.booking_type_id = '" . $_REQUEST['wh_resource_id'] . "') OR (bt.title like '%%".$_REQUEST['wh_resource_id']."%%') )  ";
            }

            if (strpos($or_sort, '_asc') !== false) {                            // Order
                   $or_sort = str_replace('_asc', '', $or_sort);
                   $sql_order = " ORDER BY " .$or_sort ." ASC ";
            } else $sql_order = " ORDER BY " .$or_sort ." DESC ";

            if ($is_use_limit) $sql_limit = $wpdb->prepare(" LIMIT %d, %d", $page_start, $page_items_count );              // Pages
            else               $sql_limit = '';
            $types_list = $wpdb->get_results(  $sql .  $where. $sql_order . $sql_limit ) ;

            // FIx: This fix do not show the "Child" booking resources at the Booking > Resources > Cost and rates page.             
            if  (    ( ( isset($_GET['hide'] )) && ( $_GET['hide'] == 'child') ) 
//                  || ( ( isset($_GET['tab'] ) ) && ( $_GET['tab'] == 'cost') ) 
                ) {
                foreach ($types_list as $key=>$res) {
                    $types_list[$key]->count = 1;
                    $types_list[$key]->id = $res->booking_type_id;
                }
                return $types_list;
            }

            $bk_type_id = array();                                              // Get all ID of booking resources.
            if (! empty($types_list))
            foreach ($types_list as $key=>$res) {
                $types_list[$key]->id = $res->booking_type_id;
                $bk_type_id[]=$res->booking_type_id;
            }


    
            if ( ( class_exists('wpdev_bk_biz_l')) && (count($bk_type_id)>0) ) {

                $bk_type_id = implode(',',$bk_type_id);                         // Get all ID of PARENT or SINGLE Resources.

                $sql = " SELECT * FROM {$wpdb->prefix}bookingtypes as bt" ;

                $where = '';                                                        // Where for the different situation: BL and MU
                $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                if ($where != '') $where = ' WHERE ' . $where;

                if ($where != '')   $where .= ' AND   bt.parent IN (' . $bk_type_id . ') ';
                else                $where .= ' WHERE bt.parent IN (' . $bk_type_id . ') ';

                $sql_order = 'ORDER BY parent, prioritet';                          // Order
                $linear_list_child_resources = $wpdb->get_results(  $sql .  $where. $sql_order  );  // Get  child elements

                // Transfrom them into array for the future work
                $array_by_parents_child_resources = array();
                foreach ($linear_list_child_resources as $res) {
                    if (! isset($array_by_parents_child_resources[$res->parent]))  $array_by_parents_child_resources[$res->parent] = array();
                    $res->id = $res->booking_type_id;
                    $array_by_parents_child_resources[$res->parent][] = $res;
                }


                $final_resource_array = array();
                foreach ($types_list as $key=>$res) {
                    // check if exist child resources
                    if ( isset($array_by_parents_child_resources[ $res->booking_type_id ])) {
                        $res->count = count( $array_by_parents_child_resources[ $res->booking_type_id ] )+1;
                    } else
                        $res->count = 1;

                    // Fill the parent resource
                    $final_resource_array[] = $res;

                    // Fill all child resources (its already sorted)
                    if ( isset($array_by_parents_child_resources[ $res->booking_type_id ])) {
                        foreach ($array_by_parents_child_resources[ $res->booking_type_id ] as $child_obj) {
                            $child_obj->count = 1;
                            $final_resource_array[]  = $child_obj;
                        }
                    }
                }
                $types_list = $final_resource_array;
            }
//debuge($types_list);
            return $types_list;
        }

        function get__default_type(){
                    global $wpdb;
                    $mysql = "SELECT booking_type_id as id FROM  {$wpdb->prefix}bookingtypes ORDER BY id ASC LIMIT 1";
                    $types_list = $wpdb->get_results( $mysql );
                    if (count($types_list) > 0 ) $types_list = $types_list[0]->id;
                    else $types_list =1;
                    return $types_list;

        }

        function get_booking_title( $type_id = 1){
            global $wpdb;
            $type_id = intval($type_id);
            $types_list = $wpdb->get_results( "SELECT title FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = {$type_id}" );
            if ($types_list)
                return $types_list[0]->title;
            else
                return '';
        }


        function get_booking_resource_attr( $type_id = '' ){
            global $wpdb;
            $type_id = intval($type_id);
            if (! empty($type_id) ) {
                $types_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = {$type_id}" );
                if ($types_list)
                    return $types_list[0];
                else
                    return false;
            } else {
                $types_list = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}bookingtypes" );
                if ($types_list)
                    return $types_list;
                else
                    return false;                
            }
        }




        function wpdebk_get_keyed_all_bk_resources($blank){
            // Get All Booking types in array with Keys using bk res ID
            $booking_types = array();
            $booking_types_res = get_bk_types(false, false);  // All types
            foreach ($booking_types_res as $value) {
                $booking_types[$value->id] = $value;
            }
            return $booking_types;
        }
        add_bk_filter('wpdebk_get_keyed_all_bk_resources', 'wpdebk_get_keyed_all_bk_resources');


        // A J A X     R e s p o n d e r   Real Ajax with jQuery sender     //////////////////////////////////////////////////////////////////////////////////
        function wpdev_pro_bk_ajax(){

        }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Filters interface     Controll elements  ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Keyword Filter field
        function wpdebk_filter_field_bk_keyword(){

            $wpdevbk_id =              'wh_keyword';                           //  {'',  '1' }
            $wpdevbk_control_label =   __('Enter Keyword here' ,'booking');
            $wpdevbk_help_block =      __('Keyword' ,'booking');

            wpdevbk_text_filter($wpdevbk_id, $wpdevbk_control_label, $wpdevbk_help_block);
        }

        // Get the sort options for the filter at the booking listing page
        function get_p_bk_filter_sort_options($wpdevbk_selectors_def){

              $wpdevbk_selectors = array(__('ID' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'',
                               __('Dates' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'sort_date',
                               __('Resource' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'booking_type',
                               'divider0'=>'divider',
                               __('ID' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'booking_id_asc',
                               __('Dates' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'sort_date_asc',
                               __('Resource' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'booking_type_asc'
                              );
              return $wpdevbk_selectors;
        }
        add_bk_filter('bk_filter_sort_options', 'get_p_bk_filter_sort_options');


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Actions interface     Controll elements  ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Keyword Filter field
        function wpdebk_action_field_export_print(){
            ?>
            <div class="btn-group">
                <a  data-original-title="<?php _e('Print bookings listing' ,'booking'); ?>"  rel="tooltip"
                    class="tooltip_top  button button-secondary" onclick='javascript:print_booking_listing();'
                   /><?php _e('Print' ,'booking'); ?> <i class="icon-print"></i></a>
                <a data-original-title="<?php _e('Export only current page of bookings to CSV format' ,'booking'); ?>"  rel="tooltip" 
                   class="tooltip_top  button button-secondary" onclick='javascript:export_booking_listing("page", "<?php echo getBookingLocale(); ?>" );'
                   /><?php _e('Export' ,'booking'); ?> <i class="icon-list"></i></a>
                <a data-original-title="<?php _e('Export All bookings to CSV format' ,'booking'); ?>"  rel="tooltip"
                   class="tooltip_top  button button-secondary" onclick='javascript:export_booking_listing("all", "<?php echo getBookingLocale(); ?>");'
                   /><?php _e('Export All' ,'booking'); ?> <i class="icon-list"></i></a>
            </div>
            <?php
        }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S Q L   Modifications  for  Booking Listing  ///////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Keyword
        function get_p_bklist_sql_keyword($blank, $wh_keyword ){
            $sql_where = '';

            if ( $wh_keyword !== '' )
                $sql_where .= " AND  bk.form LIKE '%%" . $wh_keyword . "%%' ";

            return $sql_where;
        }
        add_bk_filter('get_bklist_sql_keyword', 'get_p_bklist_sql_keyword');


        // Resources
        function get_p_bklist_sql_resources($blank, $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 ){
            global $wpdb;
            $sql_where = '';

            if ( ! empty($wh_booking_type) )  {
                // P
                $sql_where.=   " AND (  " ;
                $sql_where.=   "       ( bk.booking_type IN  ( ". $wh_booking_type ." ) ) " ;     // BK Resource conections

                if ( ( isset($_REQUEST['view_mode']) ) && ( $_REQUEST['view_mode']== 'vm_calendar' ) ) {
                    // Skip the bookings from the children  resources, if we are in the Calendar view mode at the admin panel
                    $sql_where .= apply_bk_filter('get_l_bklist_sql_resources_for_calendar_view', ''  , $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 );
                } else {
                    //  BL
                    $sql_where .= apply_bk_filter('get_l_bklist_sql_resources', ''  , $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 );
                }
                // P
                $sql_where.=   "     )  " ;
            }

            return $sql_where;
        }
        add_bk_filter('get_bklist_sql_resources', 'get_p_bklist_sql_resources');


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  P r i n t    L o y o u t     ///////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        function wpdevbk_generate_print_loyout( $print_data ) {
            ?>
              <div style="display:none;">
                  <div id="booking_print_loyout">
                      <table style="width:100%;" >
                          <thead>
                              <tr class="booking-listing-header">
                                  <th style="width:10%"><?php echo $print_data[0][0]; ?></th>
                                  <th style="width:10%"><?php echo $print_data[0][1]; ?></th>
                                  <th ><?php echo $print_data[0][2]; ?></th>
                                  <th style="width:20%"><?php echo $print_data[0][3]; ?></th>
                                  <th style="width:10%"><?php echo $print_data[0][4]; ?></th>
                              </tr>
                          </thead>
                          <tbody>
                              <?php
                              $is_alternative_color = true;
                              for ($i = 1; $i < count($print_data); $i++) {
                                      $print_item = $print_data[$i] ;
                                      $is_alternative_color = ! $is_alternative_color;
                              ?>
                              <tr id="wpbc_print_row<?php echo $print_item[0]; ?>" class="wpbc_print_rows booking-listing-row <?php if ($is_alternative_color) echo ' row_alternative_color ';?>" >
                                  <td class=" bktextcenter"><?php echo $print_item[0]; ?></td>
                                  <td class=" bktextcenter"><?php echo '<span class="label">'.$print_item[1][0] . '</span>, <span class="label">' . $print_item[1][1] . '</span>, <span class="label">' . $print_item[1][2] .'</span>'; ?></td>
                                  <td class=" bktextcenter"><?php echo $print_item[2]; ?></td>
                                  <td class=" bktextcenter"><?php echo strip_tags($print_item[3]); ?></td>
                                  <td class=" bktextcenter"><span  class="label"><?php echo $print_item[4][0] . ' ' . $print_item[4][1]; ?></span></td>
                              </tr>
                              <?php } ?>
                          </tbody>
                      </table>
                  </div>
              </div>
            <?php
        }

        function get_bklist_print_header($blank){
            return array(
                             array(
                                    __('ID' ,'booking'),
                                    __('Labels' ,'booking'),
                                    __('Data' ,'booking'),
                                    __('Dates' ,'booking'),
                                    __('Cost' ,'booking'),
                                   )
                            );
        }
        add_bk_filter('get_bklist_print_header', 'get_bklist_print_header');

        function get_bklist_print_row($blank, $booking_id,
                                             $is_approved ,
                                             $bk_form_show,
                                             $bk_booking_type_name,
                                             $is_paid ,
                                             $pay_print_status ,
                                             $print_dates,
                                             $bk_cost ){

            if ($is_approved) $bk_print_status =  __('Approved' ,'booking');
            else              $bk_print_status =  __('Pending' ,'booking');

            //BS
            $currency = apply_bk_filter('get_currency_info', 'paypal');

            return array(  $booking_id,
                                    array($bk_print_status, $bk_booking_type_name, $pay_print_status),
                                    $bk_form_show,
                                    $print_dates,
                                    array($currency, $bk_cost)                                  //BS
                                  );
        }
        add_bk_filter('get_bklist_print_row', 'get_bklist_print_row');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  H T M L   &  E l e m e n t s   in   Booking   L i s t i n g  Table  ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        function wpdev_bk_listing_show_edit_btn( $booking_id , $edit_booking_url, $bk_remark, $bk_booking_type ){

          ?><a href="<?php echo $edit_booking_url; ?>" onclick="" data-original-title="<?php _e('Edit Booking' ,'booking'); ?>"  rel="tooltip" 
               class="tooltip_top button-secondary button"
            ><i class="icon-edit"></i><?php
                 /** ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/edit_type.png" style="width:12px; height:13px;"><?php /**/ ?></a><a 
                href="javascript:void(0)"
                data-original-title="<?php if ($bk_remark=='') { _e('Edit Note' ,'booking'); } else {echo esc_js(substr($bk_remark,0,100)); if (strlen($bk_remark)>100) {echo '...';}  } ?>"
                rel="tooltip" class="tooltip_top button-secondary button remark_bk_link"
                onclick='javascript: if (document.getElementById(&quot;remark_row<?php echo $booking_id;?>&quot;).style.display==&quot;block&quot;) document.getElementById(&quot;remark_row<?php echo $booking_id;?>&quot;).style.display=&quot;none&quot;; else document.getElementById(&quot;remark_row<?php echo $booking_id;?>&quot;).style.display=&quot;block&quot;; ' 
            ><i class="icon-comment"></i><?php /** ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/notes<?php if ($bk_remark!='') echo '_rd' ?>.png" style="width:16px; height:16px;"><?php /**/ ?></a><a 
                href="javascript:void(0)"  data-original-title="<?php _e('Change Resource' ,'booking'); ?>"  rel="tooltip" 
                class="tooltip_top button-secondary button"
                onclick='javascript:
                     document.getElementById("new_booking_resource_booking_id").value = "<?php echo $booking_id; ?>";
                     setSelectBoxByValue("new_booking_resource", <?php echo $bk_booking_type; ?> );
                     var cbr;
                     cbr = jQuery("#change_booking_resource_controll_elements").detach();
                     cbr.appendTo(jQuery("#changing_bk_res_in_booking<?php echo $booking_id; ?>"));
                     cbr = null;
                     jQuery(".booking_row_modification_element_changing_resource").hide();
                     jQuery("#changing_bk_res_in_booking<?php echo $booking_id; ?>").show();
               ' ><i class="icon-random"></i><?php /* ?>
                <img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/exchange.png" style="width:16px; height:16px;"><?php /**/ ?></a><?php
                
                //FixIn:5.4.5.1
                ?><a 
                href="javascript:void(0)"  data-original-title="<?php _e('Duplicate Booking' ,'booking'); ?>"  rel="tooltip" 
                class="tooltip_top button-secondary button"
                onclick='javascript:
                     document.getElementById("duplicate_booking_resource_booking_id").value = "<?php echo $booking_id; ?>";
                     setSelectBoxByValue("duplicate_booking_resource", <?php echo $bk_booking_type; ?> );
                     var cbr;
                     cbr = jQuery("#duplicate_booking_resource_controll_elements").detach();
                     cbr.appendTo(jQuery("#changing_bk_res_in_booking<?php echo $booking_id; ?>"));
                     cbr = null;
                     jQuery(".booking_row_modification_element_changing_resource").hide();
                     jQuery("#changing_bk_res_in_booking<?php echo $booking_id; ?>").show();
               ' ><i class="icon-tags"></i><?php /* ?>
                <img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/exchange.png" style="width:16px; height:16px;"><?php /**/ ?></a><?php
        }
        add_bk_action( 'wpdev_bk_listing_show_edit_btn', 'wpdev_bk_listing_show_edit_btn');



        function wpdev_bk_listing_show_edit_fields( $booking_id , $bk_remark ){
          ?>
              <?php //P : Edit Note  ?>
              <div class="booking_row_modification_element booking_edit_note" id="remark_row<?php echo $booking_id; ?>" <?php if ( WP_BK_SHOW_BOOKING_NOTES && (! empty($bk_remark)) ) { echo 'style="display:block;"'; } ?> >
                <textarea id="remark_text<?php echo $booking_id; ?>"  name="remark_text<?php echo $booking_id; ?>" cols="2" rows="2" style="width:99%;margin:5px;"><?php echo $bk_remark; ?></textarea>
                <a class="button button-primary"
                           href="javascript:void(0)" onclick='javascript:wpdev_add_remark(<?php echo $booking_id; ?>, document.getElementById("remark_text<?php echo $booking_id; ?>").value);'
                           ><?php _e('Save' ,'booking'); ?></a>
                <a class="button button-secondary" style="margin:0px 8px;"
                       href="javascript:void(0)" onclick='javascript:document.getElementById("remark_row<?php echo $booking_id; ?>").style.display="none";' 
                       ><?php _e('Cancel' ,'booking'); ?></a>                    
               </div>

               <?php //P : Chnage Resources  ?>
               <div id="changing_bk_res_in_booking<?php echo $booking_id; ?>" class="booking_row_modification_element_changing_resource booking_row_modification_element" ></div>
          <?php
        }
        add_bk_action( 'wpdev_bk_listing_show_edit_fields', 'wpdev_bk_listing_show_edit_fields');




        function wpdev_bk_listing_show_change_booking_resources(  $booking_types ){
          ?>
          <div id="hided_boking_modifications_elements">
            <div id="change_booking_resource_controll_elements">
                <input type="hidden" value="" id="new_booking_resource_booking_id" />
                <select id="new_booking_resource" name="new_booking_resource" style="margin:3px 5px;">
                    <?php
                    foreach ($booking_types as $mm) { ?>
                    <option value="<?php echo $mm->id; ?>"
                          style="<?php if  (isset($mm->parent)) if ($mm->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                        ><?php echo $mm->title; ?></option>
                    <?php } ?>
                </select>
                <a href="javascript:void(0)" class="button button-primary"   style="margin:3px 7px 7px 5px;"
                       onclick='javascript:wpdev_change_bk_resource(document.getElementById("new_booking_resource_booking_id").value, document.getElementById("new_booking_resource").value);
                         var cbrce;
                         cbrce = jQuery("#change_booking_resource_controll_elements").detach();
                         cbrce.appendTo(jQuery("#hided_boking_modifications_elements"));
                         cbrce = null;
                         jQuery(".booking_row_modification_element_changing_resource").hide();
                       ' ><?php _e('Change' ,'booking'); ?></a>
                <a href="javascript:void(0)" class="button button-secondary" style="margin:3px 7px 7px 2px;"
                       onclick='javascript:
                         var cbrce;
                         cbrce = jQuery("#change_booking_resource_controll_elements").detach();
                         cbrce.appendTo(jQuery("#hided_boking_modifications_elements"));
                         cbrce = null;
                         jQuery(".booking_row_modification_element_changing_resource").hide();
                     ' ><?php _e('Cancel' ,'booking'); ?></a>
                <div class="clear"></div>
            </div>
          </div>
          <?php
          //FixIn:5.4.5.1
          ?>
          <div id="hided_boking_duplication_elements" class="hided_boking_modifications_elements">
            <div id="duplicate_booking_resource_controll_elements">
                <input type="hidden" value="" id="duplicate_booking_resource_booking_id" />
                <select id="duplicate_booking_resource" name="duplicate_booking_resource" style="margin:3px 5px;">
                    <?php
                    foreach ($booking_types as $mm) { ?>
                    <option value="<?php echo $mm->id; ?>"
                          style="<?php if  (isset($mm->parent)) if ($mm->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                        ><?php echo $mm->title; ?></option>
                    <?php } ?>
                </select>
                <a href="javascript:void(0)" class="button button-primary"   style="margin:3px 7px 7px 5px;"
                       onclick='javascript:wpbc_duplicate_booking_to_resource(document.getElementById("duplicate_booking_resource_booking_id").value, document.getElementById("duplicate_booking_resource").value);
                         var cbrce;
                         cbrce = jQuery("#duplicate_booking_resource_controll_elements").detach();
                         cbrce.appendTo(jQuery("#hided_boking_duplication_elements"));
                         cbrce = null;
                         jQuery(".booking_row_modification_element_changing_resource").hide();
                       ' ><?php _e('Duplicate Booking' ,'booking'); ?></a>
                <a href="javascript:void(0)" class="button button-secondary" style="margin:3px 7px 7px 2px;"
                       onclick='javascript:
                         var cbrce;
                         cbrce = jQuery("#duplicate_booking_resource_controll_elements").detach();
                         cbrce.appendTo(jQuery("#hided_boking_duplication_elements"));
                         cbrce = null;
                         jQuery(".booking_row_modification_element_changing_resource").hide();
                     ' ><?php _e('Cancel' ,'booking'); ?></a>
                <div class="clear"></div>
            </div>
          </div>     
          <?php 
          
        }
        add_bk_action( 'wpdev_bk_listing_show_change_booking_resources', 'wpdev_bk_listing_show_change_booking_resources');



        function wpdev_bk_listing_show_resource_label(  $bk_booking_type_name , $link_show_bookings_of_this_resource = ''){
            
           ?><span class="label label-resource label-info"><?php 
           if (! empty($link_show_bookings_of_this_resource))
               echo "<a href='{$link_show_bookings_of_this_resource}'>" .  $bk_booking_type_name . "</a>";
           else    
                echo $bk_booking_type_name; 
           ?></span><?php
        }
        add_bk_action( 'wpdev_bk_listing_show_resource_label', 'wpdev_bk_listing_show_resource_label');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  A D M I N    B O O K I N G     C A L E N D A R      O V E R V I E W     P A N E L     //////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Get inline title for days in admin panel at calendar
        function get_title_for_showing_in_day( $bk_id, $bookings, $what_show_in_day_template='[id]'){
            /* ?></div></div></div></div><?php
            debuge($bk_id, $bookings[$bk_id]->form_data['_all_fields_']);/**/

            $x_pos = $y_pos = 0;
            $x_pos = strpos($what_show_in_day_template,'[' ) ;
            $y_pos = strpos($what_show_in_day_template,']' ) ;

            while ($x_pos !== false) {

                $what_show_in_day_title = substr( $what_show_in_day_template, ($x_pos+1), ($y_pos- $x_pos-1) ) ;
                switch ($what_show_in_day_title) {
                  case 'id':
                      $title_in_day =  $bk_id ; break;
                  default:
                     //$title_in_day  =   $bookings[$bk_id]->form_data['_all_'][ $what_show_in_day_title . $bookings[$bk_id]->booking_type ] ;    break;
                     if ( isset($bookings[$bk_id]->form_data['_all_fields_'][ $what_show_in_day_title ]) ) 
                            $title_in_day  =   $bookings[$bk_id]->form_data['_all_fields_'][ $what_show_in_day_title ] ;    
                     else   $title_in_day  =  '';
                     break;

                }

                $what_show_in_day_template = substr( $what_show_in_day_template, 0, $x_pos) . $title_in_day . substr( $what_show_in_day_template, ($y_pos+1) );

                if ( ($x_pos !== false) && ($x_pos<= strlen($what_show_in_day_template))  )
                            $x_pos = strpos($what_show_in_day_template,'[', $x_pos) ;
                else        $x_pos = false;
                if ($x_pos !== false)  $y_pos = strpos($what_show_in_day_template,']', $x_pos) ;

            }
            return  $what_show_in_day_template;
        }

    // <editor-fold defaultstate="collapsed" desc="  B O O K I N G    R e s o u r c e s   S E L E C T O R    [C H O O S E N]  ">
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  Control Element                          ///////////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        function wpdevbk_selectbox_normal_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block){
            if (isset($_REQUEST[$wpdevbk_id]))    $wpdevbk_value = $_REQUEST[$wpdevbk_id];
            else                                  $wpdevbk_value = '';
            if ( strpos($wpdevbk_value,',') !== false )
                $wpdevbk_value_array = explode (',', $wpdevbk_value);
            else
                $wpdevbk_value_array = array();
            $wpdevbk_selector_default = array_search($wpdevbk_value, $wpdevbk_selectors);
            if ($wpdevbk_selector_default === false) $wpdevbk_selector_default = current($wpdevbk_selectors);
              ?>
              <div class="control-group" style="float:left;">
                <!--label for="<?php echo $wpdevbk_id; ?>" class="control-label"><?php echo $wpdevbk_control_label; ?></label-->
                <div class="inline controls">
                    <div class="btn-group">
                        <select multiple="multiple" class="span8 chzn-select" 
                                id="<?php echo $wpdevbk_id; ?>" name="<?php echo $wpdevbk_id; ?>[]" data-placeholder="<?php echo $wpdevbk_help_block; ?>"                       
                                 >
                          <?php
                          $is_all_resources_selected = false;
                          foreach ($wpdevbk_selectors as $key=>$value) {
                            if ($value != 'divider') {
                                $is_in_array = in_array($value, $wpdevbk_value_array);
                                ?><option <?php if ( ( ($wpdevbk_value == $value ) || ($is_in_array)  ) && (! $is_all_resources_selected) ) { echo ' selected="SELECTED" ';
                                                if ( strpos($value,',') !== false ) {
                                                    $is_all_resources_selected = true;
                                                }
                                           } ?> 
                                    <?php if (strpos($key , '&nbsp;') === false) echo ' style="font-weight:bold;" '; ?>
                                    value="<?php echo $value; ?>"><?php echo $key; ?></option><?php
                            } else {
                                ?><?php
                            }
                          } ?>
                      </select>
                      <div class="chzn-right-buttons btn-group">  
                            <input type="hidden" name="blank_field__this_field_only_for_formatting_buttons" value=""> 
                            <a  data-original-title="<?php _e('Clear booking resources selection' ,'booking'); ?>"  rel="tooltip" 
                                class="tooltip_top button button-secondary wpbc_stick_left wpbc_stick_right"
                                onclick="javascript:remove_all_options_from_choozen('#<?php echo $wpdevbk_id; ?>');"
                                style="border: 1px solid #aaa;margin: 0 0 0 -1px;"><i class="icon-remove icon"></i></a>
                            <a data-original-title="<?php _e('Apply booking resources selection' ,'booking'); ?>"  rel="tooltip" 
                               class="tooltip_top button button-primary wpbc_stick_left"
                               onclick="javascript:reload_booking_calendar_oveview_page();"
                               style="box-shadow: 0 0 0;margin: 0 0 0 -4px;"><i class="icon-refresh icon-white"></i></a>                  
                      </div>
                    </div>                
                    <!--p class="help-block" style="margin-top: 0px;"><?php echo $wpdevbk_help_block; ?></p-->
                </div>
              </div>
              <script type="text/javascript">

                  function remove_all_options_from_choozen( selectbox_id ){
                    jQuery( selectbox_id +' option').removeAttr('selected');    // Disable selection in the real selectbox
                    jQuery( selectbox_id ).trigger('liszt:updated');            // Remove all fields from the Choozen field              
                  } 

                  jQuery(document).ready( function(){

                    jQuery("#<?php echo $wpdevbk_id; ?>").chosen({no_results_text: "No results matched"});

                    // Catch any selections in the Choozen
                    jQuery("#wh_booking_type").chosen().change( function(va){ 

                        if( jQuery("#wh_booking_type").val() != null ) {
                            //So we are having aready values
                            jQuery.each( jQuery("#wh_booking_type").val() , function(index, value) {

                                if (value.indexOf(',')>0) { // Ok we are have array with  all booking resources ID
                                    jQuery( '#wh_booking_type' +' option').removeAttr('selected');    // Disable selection in the real selectbox
                                    jQuery( '#wh_booking_type' +' option:first-child').prop("selected", true);    // Disable selection in the real selectbox
                                    jQuery( '#wh_booking_type' ).trigger('liszt:updated');            // Update all fields from the Choozen field              
                                    var my_message = '<?php echo html_entity_decode( esc_js( __('Please note, its not possible to add new resources, if "All resources" option is selected. Please clear the selection, then add new resources.' ,'booking') ),ENT_QUOTES) ; ?>';
                                    bk_admin_show_message( my_message, 'warning', 10000 );
                                }
                            });
                        } 
                    });
                  });
              </script>
              <style type="text/css">            
                    .chzn-right-buttons {
                        float:left;
                        margin:0 0 0 -100px;
                    }
                    .bookingpage .wpdevbk a.chzn-single {
                        height: 23px;
                        margin-top: 2px;
                    }
                    #<?php echo $wpdevbk_id; ?>, 
                    .chzn-container-multi  {
                        width:auto !important;
                        margin-right: 100px;
                        float:left;
                    }
                    .chzn-container .chzn-drop,
                    #<?php echo $wpdevbk_id; ?>, 
                    .chzn-container-multi {
                        min-width:150px;
                    }
                    /* LI options */
                    .chzn-container-multi .chzn-choices {
                        height:auto !important;
                    }
                    /* Search  hidden button */
                    .bookingpage .wpdevbk .wpdevbk-filters-section .chzn-choices .search-field input, 
                    .chzn-container-multi .chzn-choices .search-field input{
/*                        height:auto !important;*/
                        height: 24px;
                        line-height:14px;
                        font-size:12px;
                        margin:1px 0;
                        float:left;
                    }
                    .chzn-container-multi .chzn-choices .search-choice {
                        margin: 3px 0 2px 5px;
                        white-space: nowrap;
                        background: #eee;
                    }
                    .chzn-container-multi .chzn-choices .search-choice span {
                        font-weight:normal;
                    }
                    .chzn-container {
                        font-size: 12px;
                        font-weight: bold;
                    }
                    .chzn-container .chzn-results .highlighted {
                        background: #08c;
                    }
                    @media (max-width: 782px) {
                        .bookingpage .wpdevbk .wpdevbk-filters-section .chzn-choices .search-field input, 
                        .chzn-container-multi .chzn-choices .search-field input {
                            height: 26px !important;
                            line-height: 15px !important;
                            margin: 1px 0 !important;
                            padding: 2px 5px 1px !important;
                        }
                        .chzn-container-multi .chzn-choices {
                            padding-bottom: 0;
                            padding-top: 1px;
                        }
                        .chzn-container-multi .chzn-choices .search-choice {
                            margin: 4px 0 4px 5px;
                        }                        
                    }
              </style>
            <?php
        }


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //  Get data for the    Controll element   -  R e s o u r c e s Filter field     ///////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        function wpdebk_filter_field_bk_resources(){

            $types_list = get_bk_types(false, false);
            $wpdevbk_id =              'wh_booking_type';                           //  {'', '1', '4,7,5', .... }
            $wpdevbk_selectors = array();
            $all_ids = array();
            foreach ($types_list as $bkr) {
                $all_ids[] = $bkr->id;
            }
            if (count($all_ids)>1)
            $wpdevbk_selectors['<strong>'.__('All resources' ,'booking').'</strong>']=implode(',',$all_ids);

            foreach ($types_list as $bkr) {
                $bkr_title = $bkr->title;
                if (isset($bkr->parent)) {
                    if ($bkr->parent == 0)
                        $bkr_title = $bkr_title;
                    else
                        $bkr_title = '&nbsp;&nbsp;&nbsp;' . $bkr_title ;
                }
                $wpdevbk_selectors[$bkr_title  ] = $bkr->id;
            }

            $wpdevbk_control_label =   '';
            $wpdevbk_help_block =      __('Booking resources' ,'booking');

            wpdevbk_selectbox_normal_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block);
        }


        ////////////////////////////////////////////////////////////////////////////////
        // SELECTION for Calendar Overview
        function wpdevbk_booking_resource_selection_for_calendar_overview(){    
            
            ?><div class="clear" style="height:1px;"></div><?php

            wpdebk_filter_field_bk_resources();
            
            ?><script type="text/javascript">
                function reload_booking_calendar_oveview_page(){
                    window.location.assign("<?php $bk_admin_url = get_params_in_url( array('wh_booking_type') );
                                                echo $bk_admin_url . '&wh_booking_type='; ?>" + jQuery( '#wh_booking_type' ).val() );
                }        
            </script><?php
        }

        ////////////////////////////////////////////////////////////////////////////////
        // SELECTION for Listing
        function wpdevbk_booking_resource_selection_for_booking_listing(){
            ?><div class="clear" style="height:1px;"></div><?php                /* FixIn: 5.4.5.14 */
            wpdebk_filter_field_bk_resources();
            ?>
            <script type="text/javascript">
                function reload_booking_calendar_oveview_page(){
                    booking_filters_form.submit();
                }
            </script><?php
        }


        ////////////////////////////////////////////////////////////////////////////////
        // Reconfigure   $_REQUEST['wh_booking_type']    parameter
        ////////////////////////////////////////////////////////////////////////////////
        function wpdevbk_check_wh_booking_type_param_in_request() {

            //debuge($_REQUEST);        

            if ( isset($_REQUEST['wh_booking_type'])) {
                /*
                Calendar - GET           
                             null  - Empty   - OLD ALL view 
                             56    - Single  - One Res View
                             56,5  - SEVERAL - Matrix View
                            56,....55 - All resources - Matrix      -- PROBLEM that in the field is listied all resources and "All resource" item - Next clik twice this view
                 Listing - POST  
                             NOT SET - load default resource.
                             Array(56 )   - SINGLE - 
                             Array(56, 5) - SEVERAL -
                             Array([0] => 56,1,5,6,7,8,9,13,24,25,26,4,3,2,10,11,12,55
                                   [1] => 56
                                   [2] => 1
                                      All resources - Matrix      -- PROBLEM that in the field is listied all resources and "All resource" item - Next clik twice this view       
                             Array([0] => 56
                                    ...
                                   [17] => 55 )
                                      All resources 
                */

                // Firstly  we are get ARRAY if we are had the String            
                if (is_string($_REQUEST['wh_booking_type'])) {
                    if  ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) {
                        $_REQUEST['wh_booking_type'] = explode(',', $_REQUEST['wh_booking_type']);
                    }
                }    

                // Now transform array  to  the String
                if ( is_array($_REQUEST['wh_booking_type'])) {
                    $wh_booking_type_array = $_REQUEST['wh_booking_type'] ;                    
                    foreach ($wh_booking_type_array as $key=>$value) {
                        if (empty($value)) 
                            unset($wh_booking_type_array[$key]);
                    }
                    // If we are had some array and in array element was like this [0] => 56,1,5,55 ; [1] => 56, so now we are have this: => 56,1,5,55,56
                    $_REQUEST['wh_booking_type'] = implode(',', $wh_booking_type_array);
                }

                //Remove dupplicates -  Now trasform to Array again; Remove dubplicates and Get Array again, its because issue if we are have ALL Resources option.
                $_REQUEST['wh_booking_type'] = explode(',', $_REQUEST['wh_booking_type']);
                $_REQUEST['wh_booking_type'] = array_unique($_REQUEST['wh_booking_type']);
                $_REQUEST['wh_booking_type'] = implode(',', $_REQUEST['wh_booking_type']);

                // If No any selections, its mean that  we are received NULL, so  then we are set  the value to "" and we are show all bookings in old view mode.
                if  ($_REQUEST['wh_booking_type']=='null') {
                    $_REQUEST['wh_booking_type'] = '';
                }

            } else {  //E M P T Y     -  Load default parameter

                $_REQUEST['wh_booking_type'] = get_bk_option( 'booking_default_booking_resource');

                // If default selection  is Empty so its mean load All resources.
                if (empty($_REQUEST['wh_booking_type'])) {
                    $types_list = get_bk_types(false, false); 
                    $types_list_id = array();
                    foreach ($types_list as $tl) { 
                        $types_list_id[] = $tl->id;
                    }
                    $_REQUEST['wh_booking_type'] = implode(',',$types_list_id);
                }

            }
        }
    // </editor-fold>
           
        
?>