<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
require_once(WPDEV_BK_PLUGIN_DIR. '/inc/lib_l.php' );
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/wpdev-booking-search-widget.php')) {  require_once(WPDEV_BK_PLUGIN_DIR. '/inc/wpdev-booking-search-widget.php' ); }
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/multiuser.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/multiuser.php' ); }


class wpdev_bk_biz_l {

    var $wpdev_bk_multiuser;

    function __construct(){

        // Activation
        add_bk_action('wpdev_booking_activation', array($this, 'pro_activate'));
        add_bk_action('wpdev_booking_deactivation', array($this, 'pro_deactivate'));

        
        add_action('wpbc_define_js_vars', array(&$this, 'wpbc_define_js_vars') );
        add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
        add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );

        // Coupons advanced cost customization option.
        add_bk_filter('coupons_discount_apply', array(&$this, 'coupons_discount_apply'));
        add_bk_filter('get_coupons_discount_info', array(&$this, 'get_coupons_discount_info'));
        add_bk_filter('wpdev_get_additional_description_about_coupons', array(&$this, 'wpdev_get_additional_description_about_coupons'));
        add_bk_action('wpbc_set_coupon_inactive', array(&$this, 'wpbc_set_coupon_inactive'));

        // JS - Tooltip
        add_filter('wpdev_booking_show_availability_at_calendar', array(&$this, 'show_availability_at_calendar') , 10, 2 );                // Write JS files

        // INSERT - UPDATE    --   ID   or  Dates
        add_bk_action('wpdev_booking_reupdate_bk_type_to_childs', array(&$this, 'reupdate_bk_type_to_childs')); // Main function

        // Filters for changing view of Dates...
        add_bk_filter('get_bk_dates_sql', array(&$this, 'get_sql_bk_dates_for_all_resources'));  // Modify SQL
        add_bk_filter('get_bk_dates', array(&$this,     'get_bk_dates_for_all_resources'));  // Modify Result of dates

        add_bk_filter('cancel_pending_same_resource_bookings_for_specific_dates', array(&$this, 'cancel_pending_same_resource_bookings_for_specific_dates'));  // Modify Result of dates

        //Booking Table Admin Page -- Show also bookins, where SOME dates belong to this Type
        // SQL Modification for Admin Panel dates:  (situation, when some bookings dates exist at several resources )
        add_bk_filter('get_sql_4_dates_from_other_types', array(&$this,     'get_sql_4_dates_from_other_types'));


        // For some needs
        add_bk_filter('get_booking_types_hierarhy_linear', array(&$this,     'get_booking_types_hierarhy_linear'));  // Modify Result of dates


        // Admin panel, show resource nearly dates
        add_bk_action('show_diferent_bk_resource_of_this_date', array(&$this, 'show_diferent_bk_resource_of_this_date'));            

        // Show childs count at top line of selection booking resources
        add_bk_filter('showing_capacity_of_bk_res_in_top_line', array(&$this, 'showing_capacity_of_bk_res_in_top_line'));



        // If number = 1 - its means that  booking resource - single
        add_bk_filter( 'wpbc_get_number_of_child_resources', array(&$this, 'get_max_available_items_for_resource'));
        add_bk_filter( 'wpbc_get_max_visitors_for_bk_resources', array(&$this, 'get_max_visitors_for_bk_resources'));             // FixIn: 5.4.5.4


        // Booking Page  - Show only for PARENT booking resource
        add_bk_action('show_all_bookings_for_parent_resource', array(&$this, 'show_all_bookings_for_parent_resource'));
        add_bk_action('check_if_bk_res_parent_with_childs_set_parent_res', array(&$this, 'check_if_bk_res_parent_with_childs_set_parent_res'));

        // Settings Page
        add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_menu_content')); // Settings
        add_bk_action('wpdev_booking_settings_show_coupons', array(&$this, 'settings_show_coupons')); // Settings

        add_bk_action('show_additional_shortcode_help_for_form', array($this, 'show_additional_shortcode_help_for_form'));

        add_bk_action('wpdev_booking_settings_top_menu_submenu_line',   array($this, 'wpbc_general_settings_top_menu_submenu_line' ));
        

        // Resources settings //
        add_bk_action('resources_settings_after_title', array($this, 'resources_settings_after_title'));
        add_bk_action('resources_settings_table_headers', array($this, 'resources_settings_table_headers'));
        add_bk_action('resources_settings_table_footers', array($this, 'resources_settings_table_footers'));
        add_bk_action('resources_settings_table_collumns', array($this, 'resources_settings_table_collumns'));
        add_bk_action('resources_settings_table_info_collumns', array($this, 'resources_settings_table_info_collumns'));
        add_bk_action('resources_settings_table_add_bottom_button', array($this, 'resources_settings_table_add_bottom_button'));
        add_bk_filter('get_sql_4_update_bk_resources', array(&$this, 'get_sql_4_update_bk_resources'));
        add_bk_filter('get_sql_4_insert_bk_resources_fields_h', array(&$this, 'get_sql_4_insert_bk_resources_fields'));
        add_bk_filter('get_sql_4_insert_bk_resources_values_h', array(&$this, 'get_sql_4_insert_bk_resources_values'));
        add_bk_action('insert_bk_resources_recheck_max_visitors', array($this, 'insert_bk_resources_recheck_max_visitors'));

        // Search functionality
        add_bk_filter('wpdev_get_booking_search_form', array(&$this, 'wpdev_get_booking_search_form'));
        add_bk_filter('wpdev_get_booking_search_results', array(&$this, 'wpdev_get_booking_search_results'));
        add_bk_action('wpdev_ajax_booking_search', array($this, 'show_booking_search_results'));


        add_action('wpdev_bk_general_settings_advanced_section', array(&$this, 'show_advanced_settings_in_general_settings_menu') );
        add_action('settings_set_show_availability_in_tooltips', array(&$this, 'settings_set_show_availability_in_tooltips') );
        add_action('settings_advanced_set_fixed_time', array(&$this, 'settings_advanced_set_fixed_time'));                      // Write General Settings

        add_bk_action('regenerate_booking_search_cache', array($this, 'regenerate_booking_search_cache'));


        

        if ( class_exists('wpdev_bk_multiuser')) {  $this->wpdev_bk_multiuser = new wpdev_bk_multiuser();
        } else {                                $this->wpdev_bk_multiuser = false; }

    }


// <editor-fold defaultstate="collapsed" desc=" S U P P O R T       F u n c t i o n s ">

// S U P P O R T       F u n c t i o n s    //////////////////////////////////////////////////////////////////////////////////////////////////

    // Reset to Payment form
    function reset_to_default_form($form_type ){
           return '[calendar] \n\
<div class="payment-form"><br /> \n\
 <div class="form-hints"> \n\
      '. __('Dates' ,'booking').': [selected_short_timedates_hint]<br><br> \n\
      '. __('Full cost of the booking' ,'booking').': [cost_hint] <br> \n\
 </div><hr/> \n\
 <p>'. __('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p>'. __('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p>'. __('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'. __('Phone' ,'booking').':<br />[text phone] </p> \n\
 <p>'. __('Address (required)' ,'booking').':<br />  [text* address] </p> \n\
 <p>'. __('City (required)' ,'booking').':<br />  [text* city] </p> \n\
 <p>'. __('Post code (required)' ,'booking').':<br />  [text* postcode] </p> \n\
 <p>'. __('Country (required)' ,'booking').':<br />  [country] </p> \n\
 <p>'. __('Visitors' ,'booking').':<br />  [select visitors "1" "2" "3" "4"] </p> \n\
 <p>'. __('Details' ,'booking').':<br /> [textarea details] </p> \n\
 <p>'. __('Coupon' ,'booking').':<br /> [coupon coupon] </p> \n\
 <p>[checkbox* term_and_condition use_label_element "'. __('I Accept term and conditions' ,'booking').'"] </p> \n\
 <p>[captcha]</p> \n\
 <p>[submit class:btn "'. __('Send' ,'booking').'"]</p> \n\
</div>';   
     }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){ 
        
        $my_page = 'client';                                            // Get a page
        $parent_booking_resources_values = '';
        if (      strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation') !== false )  $my_page = 'add';
        else if ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking')!==false)                 $my_page = 'booking';

        if (
                ($my_page != 'add') ||
                (isset($_GET['parent_res'])) ||

                (   ($my_page == 'add') &&                  // For situation, when default bk resource is not set and this is parent resource
                    ( ! isset($_GET['booking_type']) )  &&
                    (  $this->check_if_bk_res_have_childs(  get_bk_option( 'booking_default_booking_resource') ) )  )
            ) {
            $parent_booking_resources_values = '';
            $arr = $this->get_booking_types_hierarhy_linear();  // Define Parent BK Resources (types) for JS
            foreach ($arr as $bk_res) {
                if ($bk_res['count'] > 1 )
                    if (isset($bk_res['obj']->id))
                        $parent_booking_resources_values .= $bk_res['obj']->id .',';
            }
            if (strlen($parent_booking_resources_values)>0) $parent_booking_resources_values = substr($parent_booking_resources_values, 0,-1);
        }
        
        wp_localize_script('wpbc-global-vars', 'wpbc_global5', array(
              'max_visitors_4_bk_res' => '[]'
            , 'message_verif_visitors_more_then_available' => esc_js(__('Try selecting fewer visitors. The number of visitors may be more than the number of available units on selected day(s)!' ,'booking'))
            , 'is_use_visitors_number_for_availability' => ( (get_bk_option( 'booking_is_use_visitors_number_for_availability') == 'On')?'true':'false' )
            , 'availability_based_on' => get_bk_option( 'booking_availability_based_on'  )
            , 'parent_booking_resources' => '[' . $parent_booking_resources_values . ']'                 
        ) );                
        
    }    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){ 
        wp_enqueue_script( 'wpbc-bl', WPDEV_BK_PLUGIN_URL . '/inc/js/biz_l'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){  
        if ( ( $where_to_load == 'client' ) || ( $where_to_load == 'both' ) ) {
            wp_enqueue_style('wpbc-css-search-form', WPDEV_BK_PLUGIN_URL . '/inc/css/search-form'.((WP_BK_MIN)?'.min':'').'.css', array( ), '1.0');                
        }
    }
    



    function get_available_spots_for_bk_res( $type_id ){

        $availability_based_on_visitors   = get_bk_option( 'booking_availability_based_on');

        if ($availability_based_on_visitors == 'visitors') {                // Based on Visitors
            // $max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($type_id);
            $max_visitors_in_bk_res_summ=$this->get_summ_max_visitors_for_bk_resources($type_id);
            return $max_visitors_in_bk_res_summ;
        } else {                                                            // Based on Items.
            $max_visit_std         = $this->get_max_available_items_for_resource($type_id);
            return $max_visit_std;
        }

    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" C O U P O N S  ">

        // Apply advanced cost to the cost from paypal form
        function coupons_discount_apply( $summ , $form , $bktype  ){
//debuge('check coupon'  , $summ);
            $original_summ = $summ;                                         // Original cost for booking

            $this->delete_expire_coupons();  // Delete some coupons if they are expire already

            $coupons = $this->get_coupons_for_this_resource($bktype);

            if ( count($coupons) <= 0) return $original_summ;               // No coupons so return as it is

            $booking_form_show = get_form_content ($form, $bktype);

            if (isset($booking_form_show['coupon']))
                if (! empty($booking_form_show['coupon'])) {

                        $entered_code =$booking_form_show['coupon'];
                        foreach ($coupons as $coupon) {

                          if ($entered_code == $coupon->coupon_code)
                            if ( ( $summ >= $coupon->coupon_min_sum ) && ( ! empty($coupon->coupon_active) ) ) {        //FixIn: 5.4.2               
                                if ($coupon->coupon_type == 'fixed') {      // Fixed discount
                                    if ($coupon->coupon_value < $summ) {
                                        return ($original_summ - $coupon->coupon_value);
                                    }
                                }
                                if ($coupon->coupon_type == '%') {          // Procent of
                                    if ($coupon->coupon_value <= 100) {
                                        return ($original_summ - $coupon->coupon_value * $original_summ / 100 );
                                    }
                                }
                            }

                        }

                }

            return  $original_summ ;
        }

        
        // FixIn: 5.4.2
        /** Set coupons inactive after specific number of usage
         * 
         * @global type $wpdb
         * @param type $booking_id
         * @param type $bktype
         * @param type $booking_days_count
         * @param type $times_array
         * @param type $form
         * @return boolean
         */
        function wpbc_set_coupon_inactive($booking_id, $bktype, $booking_days_count, $times_array, $form = false){
            global $wpdb;
            if ($form === false) {
               $form = escape_any_xss($_POST["form"]);
            }
            $coupons = $this->get_coupons_for_this_resource($bktype);

            if ( count($coupons) <= 0) return false;                            // No coupons so return as it is

            $booking_form_show = get_form_content ($form, $bktype);
            if (isset($booking_form_show['coupon']))
                if (! empty($booking_form_show['coupon'])) {

                        $entered_code = $booking_form_show['coupon'];
                        foreach ($coupons as $coupon) {

                          if ($entered_code == $coupon->coupon_code)
                            if ( /*( $summ >= $coupon->coupon_min_sum ) &&*/ ( ! empty($coupon->coupon_active) ) ) {        
                               // Set  coupon one time lower
                               $coupon_active = ( (int) $coupon->coupon_active ) - 1; 
//debuge($coupon_active);                               
                               $wp_query = "UPDATE {$wpdb->prefix}booking_coupons SET coupon_active = {$coupon_active} WHERE coupon_id = {$coupon->coupon_id}";          
                               $wpdb->query( $wp_query );
                               return  true;
                            }
                        }
                }
            return  false;
        }
                   

        // Get > Array discount info,   if it can be apply to the specific bk_resource and summ or return FALSE
        function get_coupons_discount_info( $summ , $form , $bktype  ){

            $original_summ = $summ;                                         // Original cost for booking

            $coupons = $this->get_coupons_for_this_resource($bktype);

            if ( count($coupons) <= 0) return false;               // No coupons so return as it is

            $booking_form_show = get_form_content ($form, $bktype);

            if (isset($booking_form_show['coupon']))
                if (! empty($booking_form_show['coupon'])) {

                        $entered_code =$booking_form_show['coupon'];
                        foreach ($coupons as $coupon) {

                          if ($entered_code == $coupon->coupon_code)
                            if ( ( $summ >= $coupon->coupon_min_sum ) && ( ! empty($coupon->coupon_active) ) ) {          //FixIn: 5.4.2               
                                if ($coupon->coupon_type == 'fixed') {      // Fixed discount
                                    if ($coupon->coupon_value < $summ) {
                                        $currency = apply_bk_filter('get_currency_info', 'paypal');
                                        return (array($original_summ, $coupon->coupon_value, $coupon->coupon_code ,   $currency  . $coupon->coupon_value ));
                                    }
                                }
                                if ($coupon->coupon_type == '%') {          // Procent of
                                    if ($coupon->coupon_value < 100) {
                                        return (array($original_summ , $coupon->coupon_value * $original_summ / 100,  $coupon->coupon_code,    round($coupon->coupon_value,0)  . '%' ));
                                    }
                                }
                            }

                        }

                }

            return  false ;
        }

        // Get Line with description according Coupon Discount, which is apply
        function wpdev_get_additional_description_about_coupons($blank, $bk_type , $dates,  $time_array, $form_post ){

            // get COST without discount
            $summ_without_discounts   = apply_bk_filter('wpdev_get_bk_booking_cost', $bk_type , $dates, $time_array , $form_post , false );

            // Get Array with info according discount
            $additional_discount_info = $this->get_coupons_discount_info( $summ_without_discounts , $form_post , $bk_type  );

            if ($additional_discount_info !== false) {                      // If discount is exist

                $currency = apply_bk_filter('get_currency_info', 'paypal'); // Get currency

                if (strpos($additional_discount_info[3], '%') !== false)    // % or $
                     $coupon_value = $additional_discount_info[3] . ' (' . $currency  . $additional_discount_info[1] . ') '; //  % with currency
                else $coupon_value = $additional_discount_info[3] ;                                                          // Only currency

                $blank = ' <span style="font-style:italic;font-size:85%;" class="coupon_description">[' .
                                      __('coupon' ,'booking') .  ' <strong>' . $additional_discount_info[2] .'</strong>: ' .
                                       $coupon_value .
                                      ' ' . __('discount' ,'booking') .
                          ']</span>';
            }

            return $blank;
        }



              // Delete all expire coupons
              function delete_expire_coupons() {
                 global $wpdb;
                 $wpbc_bdtb_coupons = $wpdb->prefix . "booking_coupons";
                 $sql = "DELETE FROM $wpbc_bdtb_coupons WHERE expiration_date < CURDATE() ";
                 if ( false === $wpdb->query( $sql ) ){
                       echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:bold;">';
                       bk_error('Error during deleting from DB coupon' ,__FILE__,__LINE__); echo  '</div>';
                 }
              }

              // Check if some coupons exist or not
              function is_exist_coupons($my_bk_type_id=''){
                  global $wpdb;
                  $wpbc_bdtb_coupons = $wpdb->prefix . "booking_coupons";
                  $sql = "SELECT * FROM $wpbc_bdtb_coupons WHERE expiration_date >= CURDATE()";

                  if ($my_bk_type_id != '' )
                     $additional_where = " AND (support_bk_types='all' OR support_bk_types LIKE '%,".$my_bk_type_id.",%' ) LIMIT 0,1" ;
                  else
                     $additional_where = " AND (support_bk_types='all' ) LIMIT 0,1" ;

                  if ($my_bk_type_id == 'any' ) $additional_where = "  LIMIT 0,1" ;

                  $result = $wpdb->get_results( $sql . $additional_where );

                  if ( count($result) > 0 ) return true;
                  else return false;
              }

              // Get coupons for specific resource
              function get_coupons_for_this_resource($my_bk_type_id=''){
                  global $wpdb;
                  $wpbc_bdtb_coupons = $wpdb->prefix . "booking_coupons";
                  $sql = "SELECT * FROM $wpbc_bdtb_coupons WHERE expiration_date >= CURDATE()";
                  $result = $wpdb->get_results( $sql .  " AND (support_bk_types='all' OR support_bk_types LIKE '%,".$my_bk_type_id.",%')" );
                  return $result;
              }

 // </editor-fold>


//<editor-fold defaultstate="collapsed" desc=" S E A R C H  ">


    function show_booking_search_results( $bk_custom_fields = array() ){ 
          
        ////////////////////////////////////////////////////////////////////////
        // Prepare parameters for search                                        //FixIn: 6.0.1.1
        ////////////////////////////////////////////////////////////////////////
        
        $booking_cache_content = get_bk_option( 'booking_cache_content');
        if ( ( empty($booking_cache_content) ) || ( $this->is_booking_search_cache_expire() ) ) {
            $this->regenerate_booking_search_cache();
            $booking_cache_content = get_bk_option( 'booking_cache_content');
        }
        if ( is_serialized( $booking_cache_content ) ) 
            $booking_cache_content = unserialize( $booking_cache_content );

        if ( ! empty( $_REQUEST[ 'bk_users' ] ) ) $sql_req_where = ' users IN (' . $_REQUEST['bk_users'] . ') ';    //FixIn:6.1.0.3
        else                                      $sql_req_where = ' 1=1 ' ;

        // ID of booking resources.
        $booking_types1      = $this->get_booking_types( 0, $sql_req_where );
        $booking_types2      = $this->get_booking_types_hierarhy( $booking_types1 );
        $booking_types       = $this->get_booking_types_hierarhy_linear( $booking_types2 );

        // Get only parents and single booking resources.
        $parents_or_single = $this->get_booking_types_hierarhy( $booking_types1 );
        
        ////////////////////////////////////////////////////////////////////////
        // Search
        ////////////////////////////////////////////////////////////////////////
        
        $search_availability = new WPBC_Search_Availability();
        
        // Set parameters
        $search_availability->set_custom_fields( $bk_custom_fields );
        $search_availability->set_booking_types( $booking_types );
        $search_availability->set_parents_or_single( $parents_or_single );
        $search_availability->set_cache_content( $booking_cache_content );
        $search_availability->define_parameters();

        // Search
        $search_availability->searching();
        
        return;  //FixIn: 6.0.1.1
        
        
        // <editor-fold     defaultstate="collapsed"                        desc=" OLD Search functionality "  >
        
        global $wpdb ;
        
        //debuge($_REQUEST);

        // Initial Search Parameters ///////////////////////////////////////
        $bk_types_id_include = array();
        $bk_types_id_exclude = array();
        $bk_category = '';
        $bk_tag = '';
        $bk_users_limit = '';

        $bk_no_results_title = $bk_search_results_title = '';

        if (isset($_REQUEST['bk_check_in']))    $date_start     = $_REQUEST['bk_check_in']  . ' 00:00:00';   //'2010-12-05 00:00:00';
        else $date_start = '1980-01-01 00:00:00'; // if no value set
        if (isset($_REQUEST['bk_check_out']))   $date_finish    = $_REQUEST['bk_check_out'] . ' 23:59:59';   //'2011-01-30 00:00:00';
        else $date_finish = '1980-01-01 23:59:59'; // if no value set
        if (isset($_REQUEST['bk_visitors']))    $min_free_items = $_REQUEST['bk_visitors'];
        else $min_free_items = 1;
        if (isset($_REQUEST['bk_category']))    $bk_category = $_REQUEST['bk_category'];
        if (isset($_REQUEST['bk_tag']))         $bk_tag = $_REQUEST['bk_tag'];
        if (isset($_REQUEST['bk_users']))       $bk_users_limit = $_REQUEST['bk_users'];

        if (isset($_REQUEST['bk_no_results_title']))       $bk_no_results_title = $_REQUEST['bk_no_results_title'];
        if (isset($_REQUEST['bk_search_results_title']))   $bk_search_results_title = $_REQUEST['bk_search_results_title'];

        // Custom fields in the search  request ////           
        if (isset($_REQUEST['bk_search_params']))  {
           $bk_search_params = $_REQUEST['bk_search_params'];
           $bk_search_params = explode('~', $bk_search_params);
           foreach ($bk_search_params as $custom_value) {
               if (! empty($custom_value)) {
                   $custom_value = explode('^',$custom_value);
                   if ( (!empty($custom_value))  &&  (strpos($custom_value[1], 'booking_') === 0) ) {
                       $bk_custom_fields[$custom_value[1]] = $custom_value[2];
                   }
               }
           }
        } // Example: $bk_custom_fields = Array ( [booking_width] => 50 )



        ////////////////////////////////////////////////////////////////////
        // Get all Booking types ///////////////////////////////////////////

        // Include  IDs
        $bk_type_additional_id = '';
        foreach ($bk_types_id_include as $bk_t)  $bk_type_additional_id .= $bk_t . ',';

        $sql_req_where = ' 1=1 ' ;
        if ($bk_users_limit!="")
            $sql_req_where = "  users IN ($bk_users_limit) ";      // Bk. Dates from OTHER TYPEs, which belong to This TYPE


        // ALL    IDs
        $booking_types1      = $this->get_booking_types(0, $sql_req_where);
        $booking_types2      = $this->get_booking_types_hierarhy($booking_types1);
        $booking_types       = $this->get_booking_types_hierarhy_linear($booking_types2) ;

        // Get All booking resource ID
        foreach ($booking_types as $bk_t) {
            if ( in_array($bk_t['obj']->id, $bk_types_id_exclude ) === false )
                $bk_type_additional_id .= $bk_t['obj']->id . ',';
        }

        $bk_type_additional_id = substr($bk_type_additional_id, 0, -1);

        ////////////////////////////////////////////////////////////////////
        $wpbc_bdtb_booking      = $wpdb->prefix . "booking";
        $wpbc_bdtb_dates        = $wpdb->prefix . "bookingdates";
        $wpbc_bdtb_resources    = $wpdb->prefix . "bookingtypes";

        //    G e t    B U S Y    D a t e s   //////////////////////////////
        $sql_req = $wpdb->prepare( 
                    "SELECT DISTINCT dt.booking_date, dt.type_id as date_res_type, dt.booking_id, dt.approved, bk.form, bt.parent, bt.prioritet, bt.booking_type_id as type, bk.cost
                    FROM {$wpbc_bdtb_dates} as dt
                        INNER JOIN {$wpbc_bdtb_booking} as bk
                        ON    bk.booking_id = dt.booking_id
                            INNER JOIN {$wpbc_bdtb_resources} as bt
                            ON    bk.booking_type = bt.booking_type_id
                     WHERE dt.booking_date >= %s  AND dt.booking_date <= %s AND
                            ( bk.booking_type IN ({$bk_type_additional_id}) " .              // All bookings from PARENT TYPE
                              "OR bt.parent  IN ({$bk_type_additional_id}) " .               // Bookings from CHILD Type
                              "OR dt.type_id IN ({$bk_type_additional_id}) " .               // Bk. Dates from OTHER TYPEs, which belong to This TYPE
                            ") " .
                  " ORDER BY dt.booking_date " 
                  , $date_start, $date_finish );

        $booking_dates = $wpdb->get_results( $sql_req );

        ////////////////////////////////////////////////////////////////////

        // Create BK Resources ID array for future assigning busy dates.
        $booked_dates_of_bk_resources = array();
        $bk_type_additional_id_arr = explode(',', $bk_type_additional_id);
        foreach ($bk_type_additional_id_arr as $bk_id) { $booked_dates_of_bk_resources[$bk_id] = array(); }

        // Assign busy dates to the BK Res array
        $simple_date_start  = substr( $date_start  ,0,10);
        $simple_date_finish = substr( $date_finish ,0,10);

        $is_skip_check_in = $is_skip_check_out = false;

        // Show the Cehck In/Out date as available for the booking resources with  capcity > 1
        if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') && 
             (get_bk_option( 'booking_check_out_available_for_parents') == 'On') )
             $is_skip_check_out = true;

        if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') && 
             (get_bk_option( 'booking_check_in_available_for_parents') == 'On') )
             $is_skip_check_in = true;


        foreach ($booking_dates as $dt_obj) {
            $bk_time = substr($dt_obj->booking_date,11);
            $bk_date = substr($dt_obj->booking_date,0,10);
            if ( ($bk_time  == '00:00:00')  ) {
                if (! empty($dt_obj->date_res_type) )  $booked_dates_of_bk_resources[ $dt_obj->date_res_type ][] = $dt_obj->booking_date;
                else                                   $booked_dates_of_bk_resources[ $dt_obj->type ][]          = $dt_obj->booking_date;
            } else {

                $is_start_time = substr($bk_time,7);
                if ($is_start_time == '1') $is_start_time = 1;
                else $is_start_time = 0;

                if (  ( ($bk_date == $simple_date_start) &&  ( $is_start_time ) && (! $is_skip_check_in) ) ||       // Start search date is at date with start time, so this day is BUSY
                      ( ($bk_date == $simple_date_finish) && (! $is_start_time) && (! $is_skip_check_out) ) ||       // Finish search date is at date with check-out time, so this day is BUSY
                      ( ($bk_date != $simple_date_start) && ($bk_date != $simple_date_finish) )   // Some day is busy inside of search days interval, so this day is BUSY
                    ) {

                        if (! empty($dt_obj->date_res_type) )  $booked_dates_of_bk_resources[ $dt_obj->date_res_type ][] = $dt_obj->booking_date;
                        else                                   $booked_dates_of_bk_resources[ $dt_obj->type ][]          = $dt_obj->booking_date;
                }

            }
        }

        // Recehck  Dates for the availability based on the season filters.
        $search_dates = wpdevbkGetDaysBetween($date_start, $date_finish);                

        $cached_season_filters = array();

        foreach ($booked_dates_of_bk_resources as $bk_type_id=>$value) {

            $cached_season_filters[ $bk_type_id ] = apply_bk_filter('get_available_days', $bk_type_id );

            foreach ($search_dates as $search_date) {                    
                $is_date_available = is_this_day_available_on_season_filters( $search_date, $bk_type_id, $cached_season_filters[ $bk_type_id ] );    // Get availability

                if (! $is_date_available) {
//debuge($booked_dates_of_bk_resources, $search_date, $bk_type_id);                    
                    $booked_dates_of_bk_resources[ $bk_type_id ][] = date_i18n( 'Y-m-d H:i:s' , strtotime($search_date)   ) ;
                }
            }
        }

        // Get only parents and single BK Resources:
        $parents_or_single = $this->get_booking_types_hierarhy( $booking_types1 );
//debuge($parents_or_single);
        // Remove all busy elements ////////////////////////////////////////////////////////////////////////////////////
        $free_objects = array();
        foreach ($parents_or_single as $key=>$value) {

            //check all CHILDS objects, if its booked in this dates interval or not
            if (count($value['child'])>0) { 
                foreach ($value['child'] as $ch_key=>$ch_value) {
                    if ( count($booked_dates_of_bk_resources[$ch_value->id])> 0 ) { // Some dates are booked for this booking resource at search date interval
                        unset($parents_or_single[$key]['child'][$ch_key]);  // Remove this child oject
                        $parents_or_single[$key]['count']--;                // Reduce the count of child objects
                    }
                }
            }

            // Check PARENT object if its booked or not
            if ( count($booked_dates_of_bk_resources[  $parents_or_single[$key]['obj']->id  ])> 0 ) { // This item is also booked
                    $parents_or_single[$key]['obj']->is_booked = 1;         // Its booked
                    $parents_or_single[$key]['count']--;                    // Reduce items count
            } else  { 
                if ( empty($parents_or_single[$key]['obj'] ) ) {  $parents_or_single[$key]['obj'] = new StdClass; }
                $parents_or_single[$key]['obj']->is_booked = 0;         // Free
            }

            // Set number of available items
            $parents_or_single[$key]['obj']->items_count = $parents_or_single[$key]['count'];

            // If this bk res. available so then add it to new free archive
            if ( ($parents_or_single[$key]['obj']->is_booked != 1) || ($parents_or_single[$key]['obj']->items_count>0) )
                $free_objects[$key] = $parents_or_single[$key]['obj'];
        }


        // Get SETTINGS, how visitors apply to availability number.
        $is_vis_apply       = get_bk_option( 'booking_is_use_visitors_number_for_availability');  // On  | Off
        $availability_for   = get_bk_option( 'booking_availability_based_on'  );                  // items | visitors

        if ($is_vis_apply == 'On') {
            if ($availability_for == 'items') { // items
                $availability_base = 'items';
            } else {                            // visitors
                $availability_base = 'visitors';
            }
        } else { // visitors = 'Off'
                $availability_base = 'off';
        }


        // Remove some items, if availabilty less then number of visitors in search form
        if ( $availability_base !== 'off' ) // check only if visitors apply to availability
            foreach ($free_objects as $key=>$value) {
                if ($availability_base == 'visitors') {     // visitors
                    if ( ($value->items_count * $value->visitors) < $min_free_items ) {
                        // Total number of VISITORS in all available ITEMS less then num of visitors in search form
                        // So remove this item
                        unset($free_objects[$key]);
                    }

                } else {                                    // items
                    if ( ( $value->items_count <= 0 ) || ($value->visitors < $min_free_items ) ) {
                        // we have that items have capacity of visitors less then in search form
                        // or
                        // all items booked
                        // So remove this item
                        unset($free_objects[$key]);
                    }
                }
            }


        // Show results ///////////////////////////////////////////////////////////////////////////////////////////////

        $booking_cache_content = get_bk_option( 'booking_cache_content');
        if ( ( empty($booking_cache_content) ) || ( $this->is_booking_search_cache_expire() ) ) {
            $this->regenerate_booking_search_cache();
            $booking_cache_content = get_bk_option( 'booking_cache_content');
        }

        // $booking_cache_content = 'a:1:{i:14;O:8:"stdClass":10:{s:2:"ID";s:3:"375";s:10:"post_title";s:23:"Test booking page (de).";s:4:"guid";s:44:"http://holidayformentera.com/wp/?page_id=375";s:12:"post_content";s:29:"[booking type=14 nummonths=1]";s:12:"post_excerpt";s:0:"";s:7:"booking";a:2:{s:4:"type";s:2:"14";s:9:"nummonths";s:1:"1";}s:16:"booking_resource";s:2:"14";s:7:"picture";i:0;s:8:"category";a:0:{}s:4:"tags";a:0:{}}}' ;           ;
        // $booking_cache_content = stripslashes($booking_cache_content);
        // $booking_cache_content = str_replace("\n","",$booking_cache_content);

        if ( is_serialized( $booking_cache_content ) ) $booking_cache_content = unserialize( $booking_cache_content );

        // Check  according users restrictions if its exist
        if (! empty($bk_users_limit)) {
            $bk_users_limit = explode(',',$bk_users_limit);
            foreach ($booking_cache_content as $key_c=>$value_c) {
                $is_exist = false;
                if ( ( isset($value_c->user) ) && ( in_array($value_c->user, $bk_users_limit) ) )
                        $is_exist = true;
                if (!  $is_exist )
                    unset($booking_cache_content[$key_c]);
            }
        }

        // In Category search functionality
        if (! empty($bk_category))
            foreach ($booking_cache_content as $key_c=>$value_c) {
                $cats = $value_c->category;
                 $is_exist = false;
                foreach ($cats as $cats_c) {
                    if ( strtolower(trim($cats_c['category'])) == strtolower(trim($bk_category))) $is_exist = true;
                }
                if (!  $is_exist ){
                    unset($booking_cache_content[$key_c]);
                }
            }


        // In TAGS search functionality
        if (! empty($bk_tag))
            foreach ($booking_cache_content as $key_c=>$value_c) {
                $cats = $value_c->tags;
                 $is_exist = false;
                foreach ($cats as $cats_c) {
                    if (strtolower(trim($cats_c['tag'])) == strtolower(trim($bk_tag))) $is_exist = true;
                }
                if (!  $is_exist ){
                    unset($booking_cache_content[$key_c]);
                }
            }

            // Custom fields
            // $bk_custom_fields = Array ( [booking_width] => 50 )

            foreach ($bk_custom_fields as $custom_f_key => $custom_f_value) {                   // Custom options in search form
              if (! empty($custom_f_value) ){

                // Normilize the Custom Search option to Array (in case if we was using any comma separated option)                  
                $custom_f_value = explode(',', $custom_f_value);
                foreach ($custom_f_value as $key_v => $value_v) {
                    $custom_f_value[$key_v] = strtolower( trim ( $value_v ) );
                }

                foreach ($booking_cache_content as $key_c=>$value_c) {                          // Get Posts cache content
                    $custom_fields = $value_c->custom_fields;

                    if ( isset($custom_fields[$custom_f_key]) ) {
                        
                        $custom_field_in_post = $custom_fields[$custom_f_key] ;
                        // Normilize the Custom Fields in our POST Content
                        foreach ($custom_field_in_post as $key_v => $value_v) {
                            $custom_field_in_post[$key_v] = strtolower( trim ( $value_v ) );
                        }

//debuge($custom_f_value , $custom_field_in_post);die;                       
                        $is_exist = false;
                        $custom_field_difference = array_diff( $custom_f_value , $custom_field_in_post );
                        if ( ( count($custom_field_difference) == 0 ) && ( count($custom_field_in_post) == count($custom_f_value) ) )
                            $is_exist = true; 
                        
                        // Checking Custom fields,  if its lower or equal  to  search custom parameter
                        if (( count($custom_field_in_post) == count($custom_f_value) )) {
                            foreach ( $custom_field_in_post as $temp_key_post => $temp_value_post ) {
                                if ( $custom_f_value[$temp_key_post] >= $temp_value_post )
                                    $is_exist = true; 
                            }
                        }
                        
                        
                        if (!  $is_exist ) unset($booking_cache_content[$key_c]);

                    } else { //this custom field is not exist inside of the post, so we will remove this post  from the search  results
                        unset($booking_cache_content[$key_c]);
                    }
                }
              }
            }

        $booking_found_search_item = get_bk_option( 'booking_found_search_item');
        $booking_found_search_item =  apply_bk_filter('wpdev_check_for_active_language', $booking_found_search_item );

        if (empty($bk_no_results_title))     $bk_no_results_title     = __('Nothing found' ,'booking');
        if (empty($bk_search_results_title)) $bk_search_results_title = __('Search results' ,'booking');

        
        $my_search_title = '<center><h2>'. $bk_no_results_title . '</h2></center>';
        //if ( ($date_start == ' 00:00:00') && ( $date_finish == ' 23:59:59') ) $free_objects = array();    // Show No search  results, if the visitor do not select  the check in/out dates in search  form

        //FixIn:6.0.1
        $search_results_found = 0;
        foreach ($free_objects as $key=>$value) {
            if (isset($booking_cache_content[ $value->id ])) {
                $search_results_found++;
                //$my_search_title = '<center><h2>'.$bk_search_results_title . '</h2></center>';
                //$my_search_title = '<center><h2>'  . sprintf( __('%d Results Found' , 'booking'), $search_results_found ) . '</h2></center>';
            }
        }        
        if ( $search_results_found > 0 ) {
            $bk_search_results_title = str_replace( '{searchresults}', $search_results_found, $bk_search_results_title );
            $my_search_title = '<center><h2>' . $bk_search_results_title  . '</h2></center>';
        }
        
        if (  is_admin() && ( defined( 'DOING_AJAX' ) ) && ( DOING_AJAX )  ) 
            $my_search_title = '<div class="booking_search_ajax_container">' . $my_search_title;
        
        echo $my_search_title;


        $bk_date_start  = explode(' ', $date_start);   $bk_date_start = $bk_date_start[0];
        $bk_date_finish = explode(' ', $date_finish);  $bk_date_finish = $bk_date_finish[0];
        
        // Sort the booking resources array with priority descending ///////////
        $sort_free_objects = array();
        foreach ($free_objects as $key=>$value) {
            $sort_free_objects[] = $value->prioritet;
        }        
        array_multisort($sort_free_objects, SORT_DESC, SORT_NUMERIC, $free_objects );
        ////////////////////////////////////////////////////////////////////////
        
        foreach ($free_objects as $key=>$value) {

                $booking_found_search_item_echo = $booking_found_search_item;


                // GUID
                if (function_exists('icl_object_id')) {
                  $bc_post_type = get_post_type( $booking_cache_content[ $value->id ]->ID );  
                  $post_translated_id = icl_object_id( $booking_cache_content[ $value->id ]->ID , $bc_post_type,  true, substr(WPDEV_BK_LOCALE_RELOAD,0,2));
                  if (! empty($post_translated_id )) {
                      $my_translated_guid = get_permalink($post_translated_id); //$my_translated_post->guid;
                  } 
                }


                if ( (isset( $booking_cache_content[ $value->id ]->post_excerpt)) && ( $booking_cache_content[ $value->id ]->post_excerpt != '' ) ) {
                    $booking_info = $booking_cache_content[ $value->id ]->post_excerpt;
                    if (function_exists('icl_object_id')) {
                        $bc_post_type = get_post_type( $booking_cache_content[ $value->id ]->ID );  
                        $post_translated_id = icl_object_id( $booking_cache_content[ $value->id ]->ID , $bc_post_type,  true, substr(WPDEV_BK_LOCALE_RELOAD,0,2));
                      
                      if (! empty($post_translated_id )) {
                          $my_translated_post = get_post($post_translated_id);
                          $booking_info = $my_translated_post->post_excerpt;
                      }
                    }

                    $booking_info = str_replace('"','',$booking_info);
                    $booking_info = str_replace("'",'',$booking_info);
                    $booking_info = html_entity_decode($booking_info);
                    $booking_info =  apply_bk_filter('wpdev_check_for_active_language', $booking_info );
                    $booking_found_search_item_echo = str_replace('[booking_info]', '<div class="booking_search_result_info">'.$booking_info.'</div>', $booking_found_search_item_echo);
                } else
                    $booking_found_search_item_echo = str_replace('[booking_info]', '', $booking_found_search_item_echo);

                if ( isset($booking_cache_content[ $value->id ]) ) {
                    $booking_cache_title = $booking_cache_content[ $value->id ]->post_title;
                    if (function_exists('icl_object_id')) {
                        $bc_post_type = get_post_type( $booking_cache_content[ $value->id ]->ID );  
                        $post_translated_id = icl_object_id( $booking_cache_content[ $value->id ]->ID , $bc_post_type,  true, substr(WPDEV_BK_LOCALE_RELOAD,0,2));
                      
                      if (! empty($post_translated_id )) {
                        $my_translated_post = get_post($post_translated_id);
                        $booking_cache_title = $my_translated_post->post_title;
                      }
                    }
                    $booking_cache_title = str_replace('"','',$booking_cache_title);
                    $booking_cache_title = str_replace("'",'',$booking_cache_title);
                    $booking_cache_title = html_entity_decode($booking_cache_title);
                    $booking_cache_title =  apply_bk_filter('wpdev_check_for_active_language', $booking_cache_title );
                } else $booking_cache_title = '';
                //FixIn: 6.0.1
                $booking_found_search_item_echo = str_replace( '[search_check_in]',  date_i18n( get_bk_option( 'booking_date_format'), mysql2date('U', $date_start )), $booking_found_search_item_echo );
                $booking_found_search_item_echo = str_replace( '[search_check_out]', date_i18n( get_bk_option( 'booking_date_format'), mysql2date('U', $date_finish)), $booking_found_search_item_echo );

                $booking_found_search_item_echo = str_replace('[num_available_resources]', '<span class="booking_search_result_items_num">'.$value->items_count .'</span>', $booking_found_search_item_echo);
                $booking_found_search_item_echo = str_replace('[max_visitors]', '<span class="booking_search_result_visitors_num">'.$value->visitors .'</span>', $booking_found_search_item_echo);
                $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
                $booking_found_search_item_echo = str_replace('[standard_cost]', '<span class="booking_search_result_cost">'.$cost_currency .  $value->cost .'</span>', $booking_found_search_item_echo);

                // if this bk rsource is inserted in some page so then show it
                if (isset($booking_cache_content[ $value->id ])) {

                    $my_link = get_permalink($booking_cache_content[ $value->id ]->ID); // $booking_cache_content[ $value->id ]->ID -- ID of the post

                    if (! empty($my_translated_guid)) $my_link = $my_translated_guid;

                    if (function_exists('qtrans_convertURL')) {
                        $q_lang = getBookingLocale();
                        if (strlen($q_lang)>2) {
                            $q_lang = substr($q_lang, 0 ,2);
                        }
                        $my_link  = qtrans_convertURL($my_link, $q_lang);
                    }

                    if (strpos($my_link,'?')=== false) $my_link .= '?';
                    else                               $my_link .= '&';

                    if (strpos($booking_found_search_item_echo, '[link_to_booking_resource]') === false) {
                        $start_x_pos = strpos($booking_found_search_item_echo, '[link_to_booking_resource' ) ;

                        if ($start_x_pos !== false) {
                            $end_y_pos = strpos($booking_found_search_item_echo, ']',  $start_x_pos ) ;
                            $get_button_title = substr ( $booking_found_search_item_echo , $start_x_pos , ( $end_y_pos - $start_x_pos) ) ;
                            $get_button_title = str_replace('[link_to_booking_resource', '', $get_button_title);
                            $get_button_title = trim($get_button_title);
                            $get_button_title = substr($get_button_title, 1, -1 );

                            $first_part = substr ( $booking_found_search_item_echo , 0, $start_x_pos ) ;
                            $last_part = substr ( $booking_found_search_item_echo , ( $end_y_pos + 1) ) ;

                            $booking_found_search_item_echo = $first_part .
                                '<a class="btn" href="'.$my_link.'bk_check_in='.$bk_date_start.'&bk_check_out='.$bk_date_finish.'&bk_visitors='.$min_free_items.'&bk_type='.$value->id.'#bklnk'.$value->id.'" >'.trim($get_button_title).'</a>' .
                                $last_part;
                        }
                    } else {
                    $booking_found_search_item_echo = str_replace('[link_to_booking_resource]', 
                            '<a class="btn" href="'.$my_link.'bk_check_in='.$bk_date_start.'&bk_check_out='.$bk_date_finish.'&bk_visitors='.$min_free_items.'&bk_type='.$value->id.'#bklnk'.$value->id.'" >'.__('Book now' ,'booking').'</a>', $booking_found_search_item_echo);
                    }
                    
                    $full_link = $my_link.'bk_check_in='.$bk_date_start.'&bk_check_out='.$bk_date_finish.'&bk_visitors='.$min_free_items.'&bk_type='.$value->id.'#bklnk'.$value->id;                    
                    $booking_found_search_item_echo = str_replace( '[book_now_link]',  $full_link, $booking_found_search_item_echo );   //FixIn:6.0.1
                    
                    if ( true ) {   // Show image and title as not links
                        if ( (isset( $booking_cache_content[ $value->id ]->picture)) && ( $booking_cache_content[ $value->id ]->picture != 0) ){
                            $image_src = $booking_cache_content[ $value->id ]->picture[0];
                            $image_w   = $booking_cache_content[ $value->id ]->picture[1];
                            $image_h   = $booking_cache_content[ $value->id ]->picture[2];

                            $booking_found_search_item_echo = str_replace('[booking_featured_image]', '<img class="booking_featured_image" src="'.$image_src.'" />', $booking_found_search_item_echo);
                        } else
                            $booking_found_search_item_echo = str_replace('[booking_featured_image]', '', $booking_found_search_item_echo);

                        $booking_found_search_item_echo = str_replace('[booking_resource_title]', '<div class="booking_search_result_title">' . $booking_cache_title . '</div>', $booking_found_search_item_echo);                    
                        
                    } else {
                        
                        if ( (isset( $booking_cache_content[ $value->id ]->picture)) && ( $booking_cache_content[ $value->id ]->picture != 0) ){
                            $image_src = $booking_cache_content[ $value->id ]->picture[0];
                            $image_w   = $booking_cache_content[ $value->id ]->picture[1];
                            $image_h   = $booking_cache_content[ $value->id ]->picture[2];

                            $booking_found_search_item_echo = str_replace('[booking_featured_image]'
                                                                          ,  '<a  style="float:none; font-size:1em !important; border: none;background: transparent !important;" href="'.$full_link.'" >'
                                                                             . '<img class="booking_featured_image" src="'.$image_src.'" /></a>'
                                                                          , $booking_found_search_item_echo);
                        } else
                            $booking_found_search_item_echo = str_replace('[booking_featured_image]', '', $booking_found_search_item_echo);
                        
                        
                        $booking_found_search_item_echo = str_replace('[booking_resource_title]', '<div class="booking_search_result_title">' 
                                    . '<a  style="float:none; font-size:1em !important; border: none;background: transparent !important;" href="'.$full_link.'" >'
                                        . $booking_cache_title 
                                    .'</a></div>', $booking_found_search_item_echo);
                    }
                    
                    /**
                      Show the total cost  of the booking,
                      based on  the check in/out dates, number of selected visitors
                      and default form  for booking resource - for correct calculation of "Advanced cost" based on number of visitors.
                    */
                    $total_cost_of_booking = wpbc_get_cost_of_booking( array(
                            'form' => 'select-one^visitors'.$value->id.'^'.$min_free_items, 
                            'all_dates' => createDateRangeArray( date_i18n("d.m.Y", strtotime($bk_date_start) ), date_i18n("d.m.Y", strtotime($bk_date_finish) ) ), 
                            'bk_type' => $value->id, 
                            'booking_form_type' => apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $value->id )
                        ) ) ;
                    
                    $booking_found_search_item_echo = str_replace( '[cost_hint]'
                                                                    , '<span class="booking_search_result_cost_hint">' 
                                                                    . $cost_currency . $total_cost_of_booking['cost_hint'] 
                                                                    . '</span>'
                                                                    , $booking_found_search_item_echo );                    
                    $booking_found_search_item_echo = str_replace( '[original_cost_hint]'
                                                                    , '<span class="booking_search_result_original_cost_hint">' 
                                                                    . $cost_currency . $total_cost_of_booking['original_cost_hint'] 
                                                                    . '</span>'
                                                                    , $booking_found_search_item_echo );                    
                    $booking_found_search_item_echo = str_replace( '[additional_cost_hint]'
                                                                    , '<span class="booking_search_result_additional_cost_hint">' 
                                                                    . $cost_currency . $total_cost_of_booking['additional_cost_hint'] 
                                                                    . '</span>'
                                                                    , $booking_found_search_item_echo );                    
                    $booking_found_search_item_echo = str_replace( '[deposit_hint]'
                                                                    , '<span class="booking_search_result_deposit_hint">' 
                                                                    . $cost_currency . $total_cost_of_booking['deposit_hint'] 
                                                                    . '</span>'
                                                                    , $booking_found_search_item_echo );                    
                    $booking_found_search_item_echo = str_replace( '[balance_hint]'
                                                                    , '<span class="booking_search_result_balance_hint">' 
                                                                    . $cost_currency . $total_cost_of_booking['balance_hint'] 
                                                                    . '</span>'
                                                                    , $booking_found_search_item_echo );
                    
                    echo '<div  class="booking_search_result_item">' . $booking_found_search_item_echo.'</div>';                    
                }                
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        ?><script type="text/javascript" >
            if (document.getElementById('booking_search_results' ) != null ) {
            document.getElementById('booking_search_results' ).innerHTML = '';}
        </script> <?php
        
        if (  is_admin() && ( defined( 'DOING_AJAX' ) ) && ( DOING_AJAX )  ) {
           ?></div><script type="text/javascript" >
                  jQuery("#booking_search_ajax").after( jQuery("#booking_search_ajax .booking_search_ajax_container") );
                  jQuery("#booking_search_ajax").hide();
        </script> <?php
        }
        
        // </editor-fold>
    }


    function wpdev_get_booking_search_results($search_results, $attr){

        if ( isset($_GET['check_in']) )  { $_REQUEST['bk_check_in'] = $_GET['check_in']; }
        if ( isset($_GET['check_out']) ) { $_REQUEST['bk_check_out'] = $_GET['check_out']; }
        if ( isset($_GET['visitors']) )  { $_REQUEST['bk_visitors'] = $_GET['visitors']; }
        if ( isset($_GET['category']) )  { $_REQUEST['bk_category'] = $_GET['category']; }
        if ( isset($_GET['tag']) )       { $_REQUEST['bk_tag'] = $_GET['tag']; }
        if ( isset($_GET['bk_users']) )  { $_REQUEST['bk_users'] = $_GET['bk_users']; }

        if ( isset($_GET['bk_no_results_title']) )  { $_REQUEST['bk_no_results_title'] = $_GET['bk_no_results_title']; }
        if ( isset($_GET['bk_search_results_title']) )  { $_REQUEST['bk_search_results_title'] = $_GET['bk_search_results_title']; }

        if ( isset($_GET['additional_search']) )  { $_REQUEST['additional_search'] = $_GET['additional_search']; }  //FixIn: 6.0.1.1

        // Custom fields in the search  request ////
        $bk_custom_fields = array();
        foreach ($_REQUEST as $key => $value) {
           if ( (!empty($value))  &&  (strpos($key, 'booking_') === 0) ) {
               $bk_custom_fields[$key] = $value;
           }
        }


        $this->show_booking_search_results( $bk_custom_fields );

    }

    // Get Search form results
    function wpdev_get_booking_search_form($search_form, $attr){ global $wpdb;

        $searchresults = false;
        $noresultstitle = $searchresultstitle = '';
        if (! empty($attr)) {
            if (isset($attr['searchresults'])) {
                $searchresults = $attr['searchresults'];
                $searchresults =  apply_bk_filter('wpdev_check_for_active_language', $searchresults );
                
            }
            if (isset($attr['searchresultstitle'])) {
                $searchresultstitle = $attr['searchresultstitle'];
                $searchresultstitle =  apply_bk_filter('wpdev_check_for_active_language', $searchresultstitle );
            }
            if (isset($attr['noresultstitle'])) {
                $noresultstitle = $attr['noresultstitle'];
                $noresultstitle =  apply_bk_filter('wpdev_check_for_active_language', $noresultstitle );
            }
        }
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
        z-index: 2147483647;
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

</style>
<script type="text/javascript" >
var search_emty_days_warning = '<?php echo esc_js(__('Please select check-in and check-out days!' ,'booking')); ?>';

function getMinRangeDaysSelections(){
   if (bk_days_selection_mode == 'dynamic')    return  bk_2clicks_mode_days_min;
   if (bk_days_selection_mode == 'fixed')      return  bk_1click_mode_days_num;
   return 0;
}

function selectCheckInDay(date) {

    if (document.getElementById('booking_search_check_out') != null) {

       var start_bk_month_4_check_out = document.getElementById('booking_search_check_in').value.split('-');
       var myDate = new Date();
       myDate.setFullYear( (1*start_bk_month_4_check_out[0]+0), (1*start_bk_month_4_check_out[1]-1) ,  ( (1*start_bk_month_4_check_out[2] ) ) );
       var days_interval = getMinRangeDaysSelections();
       if (days_interval>0) days_interval--;
       myDate.setDate(myDate.getDate() + days_interval );
       var my_date = myDate.getDate() ; if (my_date < 10 ) my_date = '0' + my_date;

       var my_month = (myDate.getMonth()+1); if (my_month < 10 ) my_month = '0' + my_month;
       document.getElementById('booking_search_check_out').value = myDate.getFullYear() + '-' + my_month + '-' + my_date ;
   }

}

function setDaysForCheckOut(date){

   var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
   var additional_class = 'date_available ';

   for (var i=0; i<user_unavilable_days.length;i++) {
       if (date.getDay()==user_unavilable_days[i])   return [false, 'cal4date-' + class_day +' date_user_unavailable' ];
   }

   var my_test_date = new Date();  
   my_test_date.setFullYear(wpdev_bk_today[0],(wpdev_bk_today[1]-1), wpdev_bk_today[2] ,0,0,0); //Get today   
   if ( (days_between( date, my_test_date)+1) < block_some_dates_from_today ) return [false, 'cal4date-' + class_day +' date_user_unavailable']; 


   if ( (document.getElementById('booking_search_check_in') != null) && (document.getElementById('booking_search_check_in').value != '') ) {

        var value = document.getElementById('booking_search_check_in').value;
        var year_m_d = value.split("-");
        var checkInDate = new Date();
        checkInDate.setFullYear( year_m_d[0], (year_m_d[1]-1) , (year_m_d[2]-1) );
        var days_interval = getMinRangeDaysSelections();
        if (days_interval>0) days_interval--;
        checkInDate.setDate(checkInDate.getDate() + days_interval);

        if(checkInDate <= date ) {                                         
           return [true, 'cal4date-' + class_day + ' ' + additional_class+ ' ' ]; // Available
        } else                     return [false, ''];    // Unavailable

   } else return [true, 'cal4date-' + class_day + ' ' + additional_class+ ' ' ]; // Available
}


function applyCSStoDays4CheckInOut(date ){
   var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
   var additional_class = 'date_available ';

   for (var i=0; i<user_unavilable_days.length;i++) {
       if (date.getDay()==user_unavilable_days[i])   return [false, 'cal4date-' + class_day +' date_user_unavailable' ];
   }

   var my_test_date = new Date();  
   my_test_date.setFullYear(wpdev_bk_today[0],(wpdev_bk_today[1]-1), wpdev_bk_today[2] ,0,0,0); //Get today   
   if ( (days_between( date, my_test_date)+1) < block_some_dates_from_today ) return [false, 'cal4date-' + class_day +' date_user_unavailable'];

   return [true, 'cal4date-' + class_day + ' ' + additional_class+ ' ' ]; // Available
}

jQuery(document).ready( function(){
    
    jQuery('#booking_search_check_in').datepick(
        {   onSelect: selectCheckInDay,
            beforeShowDay: applyCSStoDays4CheckInOut,
            showOn: 'focus',
            multiSelect: 0,
            numberOfMonths: 1,
            stepMonths: 1,
            prevText: '&laquo;',
            nextText: '&raquo;',
            dateFormat: 'yy-mm-dd',
            changeMonth: false,
            changeYear: false,
            minDate: 0, maxDate: booking_max_monthes_in_calendar, //'1Y',
            showStatus: false,
            multiSeparator: ', ',
            closeAtTop: false,
            firstDay:<?php echo get_bk_option( 'booking_start_day_weeek' ); ?>,
            gotoCurrent: false,
            hideIfNoPrevNext:true,
            useThemeRoller :false,
            mandatory: true/**/,
            _mainDivId:  ['datepick-div', 'ui-datepicker-div','widget_wpdev_booking']
            <?php
            if (! empty($_GET['check_in'])) {
                echo ", defaultDate: '".$_GET['check_in']."', showDefault:true";
            } ?>
        }
    );
    jQuery('#booking_search_check_out').datepick(
        {   beforeShowDay: setDaysForCheckOut,
            showOn: 'focus',
            multiSelect: 0,
            numberOfMonths: 1,
            stepMonths: 1,
            prevText: '&laquo;',
            nextText: '&raquo;',
            dateFormat: 'yy-mm-dd',
            changeMonth: false,
            changeYear: false,
            minDate: 0, maxDate: booking_max_monthes_in_calendar, //'1Y',
            showStatus: false,
            multiSeparator: ', ',
            closeAtTop: false,
            firstDay:<?php echo get_bk_option( 'booking_start_day_weeek' ); ?>,
            gotoCurrent: false,
            hideIfNoPrevNext:true,
            useThemeRoller :false,
            mandatory: true
            <?php
            if (! empty($_GET['check_out'])) {
                echo ", defaultDate: '".$_GET['check_out']."', showDefault:true";
            } ?>
        }
    );
});
</script>
            <?php
        // Get   shortcode   parameters ////////////////////////////////////
        //if ( isset( $attr['param'] ) )   { $my_boook_count = $attr['param'];  }

        $booking_search_form_show = get_bk_option( 'booking_search_form_show');
        $booking_search_form_show =  apply_bk_filter('wpdev_check_for_active_language', $booking_search_form_show );


        $booking_search_form_show = str_replace( '[search_category]',
                  '<input type="text" size="10" value="" name="category" id="booking_search_category" >',
                                                   $booking_search_form_show);

        $booking_search_form_show = str_replace( '[search_tag]',
                  '<input type="text" size="10" value="" name="tag" id="booking_search_tag" >',
                                                   $booking_search_form_show);



        $booking_search_form_show = str_replace( '[search_check_in]',
                  '<input type="text" size="10" value="" name="check_in" id="booking_search_check_in" >',
                                                   $booking_search_form_show);
        $booking_search_form_show = str_replace( '[search_check_out]',
                  '<input type="text" size="10" value=""  name="check_out"  id="booking_search_check_out">',
                                                   $booking_search_form_show);


        if (isset($attr['users'])) {
            $booking_search_form_show .=  '<input type="hidden" size="10" value="'.$attr['users'].'"   name="bk_users"  id="booking_bk_users">';
        }

        $booking_search_form_show .=  '<input type="hidden" value="'.$noresultstitle.'"   name="bk_no_results_title"  id="bk_no_results_title">';
        $booking_search_form_show .=  '<input type="hidden" value="'.$searchresultstitle.'"   name="bk_search_results_title"  id="bk_search_results_title">';

        //FixIn: 6.0.1.1
        ////////////////////////////////////////////////////////////////////////
        $search_shortcode = 'search_visitors';
        $find_search_visitors = preg_match_all('/\['.$search_shortcode.'[^\]]*\]/', $booking_search_form_show, $found_matches  );
      
        if ( count($found_matches) > 0 )
            foreach ( $found_matches[0] as $key => $found_shortcode ) {
            
                $found_shortcode_params = str_replace( array( '[' . $search_shortcode , ']' ), '', $found_shortcode );

                $found_shortcode_params = trim( $found_shortcode_params );
                
                if ( empty( $found_shortcode_params ) ) $found_shortcode_params = array( "1", "2", "3", "4", "5", "6" );
                else                                    $found_shortcode_params = explode( ' ', $found_shortcode_params );
                
                $code_to_insert = "<select style='width:50px;'  name='visitors'>";
                
                foreach ( $found_shortcode_params as $v ) {
                    
                    $v = str_replace( array( "'", '"' ), '', $v );
                    
                    $code_to_insert .= "<option value='{$v}' " . selected( isset( $_GET['visitors'] ) ? $_GET['visitors'] : '', $v, false  ) . ">{$v}</option>";
                }
                
                $code_to_insert .= "</select>";
                
                $booking_search_form_show = str_replace( $found_shortcode, $code_to_insert, $booking_search_form_show );            
            }
        ////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////
        $search_shortcode = 'additional_search';
        $find_search_visitors = preg_match_all( '/\[' . $search_shortcode . '[^\]]*\]/', $booking_search_form_show, $found_matches );
      
        if ( count($found_matches) > 0 )
            foreach ( $found_matches[0] as $key => $found_shortcode ) {
            
                $found_shortcode_param = str_replace( array( '[' . $search_shortcode , ']' ), '', $found_shortcode );

                $found_shortcode_param = trim( $found_shortcode_param );
                
                if ( empty( $found_shortcode_param ) )  $found_shortcode_param = "2";
                else                                    $found_shortcode_param = str_replace( array( "'", '"' ), '', $found_shortcode_param );
                
                $code_to_insert = "<input type='checkbox' name='additional_search' value='{$found_shortcode_param}' " 
                                    . checked( isset( $_GET['additional_search'] ) ? $_GET['additional_search'] : '', $found_shortcode_param, false  ) 
                                    . "/>";
                                
                $booking_search_form_show = str_replace( $found_shortcode, $code_to_insert, $booking_search_form_show );            
            }
        ////////////////////////////////////////////////////////////////////////
            
                
        /*        
        $st = strpos($booking_search_form_show, '[search_visitors');
        if ( $st !== false ) {

            $search_visitors_options = '';
            $fin = strpos($booking_search_form_show, ']', $st+16) ;


            $selected_values = substr($booking_search_form_show, $st+16, $fin - $st - 16);
            $selected_values = trim($selected_values);

            if (empty($selected_values) )     $selected_values = array("1", "2", "3", "4", "5", "6");
            else                              $selected_values = explode(' ',$selected_values);

            foreach ($selected_values as $v)  {
                $v = str_replace('"', '', $v);$v = str_replace("'", '', $v);
                if ( (isset($_GET['visitors'])) && ($_GET['visitors'] == $v) ) $is_selected = ' selected="SELECTED" ';
                else $is_selected= '';
                $search_visitors_options .= '<option value="'.$v.'"'.$is_selected.'>'.$v.'</option>';
            }


            $booking_search_form_show = substr($booking_search_form_show, 0, $st) .
                                        '<select style="width:50px;"  name="visitors">'.$search_visitors_options.'</select>' .
                                        substr($booking_search_form_show, $fin+1);
            //$booking_search_form_show = str_replace( '[search_visitors]',
              //    '<select style="width:50px;"  name="visitors">'.$search_visitors_options.'</select>',
                //                                   $booking_search_form_show);
        }*/
        //FixIn: 6.0.1.1
            
            
        if ($searchresults === false) {

            $wpbc_ajax_search_nonce = wp_nonce_field('BOOKING_SEARCH',  "wpbc_search_nonce" ,  true , false );            
            $booking_search_form_show = str_replace( '[search_button]'
                                                     , $wpbc_ajax_search_nonce . 
                                                            '<input type="button" onclick="searchFormClck(this.form, \''. 
                                                            getBookingLocale(). '\');" value="'.__('Search' ,'booking').'" class="search_booking btn">'
                                                     , $booking_search_form_show);                                                  

            $search_form = '<div  id="booking_search_form" class="booking_form_div0 booking_search_form">
                    <form name="booking_search_form" action="" method="post">'.
                         $booking_search_form_show .
                        '<div style="clear:both;"></div>
                    </form>
                </div>
                <div id="booking_search_ajax"></div>
                <div id="booking_search_results"></div>';
        } else {
            $booking_search_form_show = str_replace( '[search_button]',
                      '<input type="submit" onclick="if ( (this.form.check_in.value == \'\') || (this.form.check_out.value == \'\') ) { alert(search_emty_days_warning); return false; }" " value="'.__('Search' ,'booking').'" class="search_booking btn">', $booking_search_form_show);

            $search_form = '<div  id="booking_search_form" class="booking_form_div0 booking_search_form">
                    <form name="booking_search_form" action="'.$searchresults.'" method="get">'.
                         $booking_search_form_show .
                        '<div style="clear:both;"></div>
                    </form>
                </div>';
        }
        return $search_form;
    }


              // Generate NEW booking search cache
              function regenerate_booking_search_cache(){

                        //wp_cache_flush(); //FixIn: 5.4.5.10
                        $available_booking_resources = array();
                        global $wpdb;
                        $sql = "SELECT ID, post_title, guid, post_content, post_excerpt 
                                FROM {$wpdb->posts}  
                                WHERE post_status = 'publish' AND ( post_type != 'revision' ) AND post_content LIKE '%[booking %'";
                        $postss = $wpdb->get_results($sql);

                        if( !empty($postss))
                          foreach ($postss as $value) {

                              $post_id = $value->ID;

                              $post_custom_fields = array();
                              $post_meta = get_post_meta($post_id, '' , false ) ;

                              foreach ($post_meta as $meta_key=>$meta_value) {
                                  if (strpos($meta_key, 'booking_') === 0 ) {
                                      $post_custom_fields[$meta_key] = $meta_value;
                                  }
                              }
                              $value->custom_fields = $post_custom_fields;
//debuge($value );
                              $image_src = false;
                              if ( 	$post_id &&
                                    function_exists('has_post_thumbnail') &&
                                    has_post_thumbnail( $post_id ) &&
                                    ($image = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'post-thumbnail' ) )
                                 )
                                  {
                                      if (count($image)>2) {
                                          $image_src = $image[0];
                                          $image_w   = $image[1];
                                          $image_h   = $image[2];
                                      }
                                  }


                              $shortcode_start   = strpos($value->post_content,     '[booking ');
                              $shortcode_end     = strpos($value->post_content, ']',$shortcode_start);
                              $shortcode_content = substr($value->post_content, $shortcode_start+9, $shortcode_end - $shortcode_start-9);

                              $shortcode_content_attr = explode(' ', $shortcode_content);
                              $shortcode_attributes = array();

                              foreach ($shortcode_content_attr as $attr) {
                                  $attr_key_value = explode('=', $attr);
                                  if (count($attr_key_value)>1)
                                        $shortcode_attributes[ $attr_key_value[0] ] = $attr_key_value[1];
                              }

                              if (! isset($shortcode_attributes['type'])) $shortcode_attributes['type']=1;
                              else $shortcode_attributes['type'] = intval( $shortcode_attributes['type'] );

                              $value->booking = $shortcode_attributes;
                              $value->booking_resource = $shortcode_attributes['type'];

                              if ($image_src !== false)
                                $value->picture = array($image_src,$image_w,$image_h);
                              else $value->picture = 0;


                              $us_id = apply_bk_filter('get_user_of_this_bk_resource', false, $value->booking_resource );
                              if ($us_id !== false) {
                                  $value->user = $us_id;
                              }

                              $categories = get_the_terms($post_id,'category');
                              $post_cats = array();
                              if (! empty($categories))
                                  foreach ($categories as $cat) {
                                      $post_cats[]=array('category'=>$cat->name, 'slug'=>$cat->slug, 'ID'=>$cat->term_id);
                                  }
                              $value->category = $post_cats;

                              $tags = get_the_terms($post_id,'post_tag');
                              $post_tags = array();
                              if (! empty($tags))
                                  foreach ($tags as $cat) {
                                      $post_tags[]=array('tag'=>$cat->name, 'slug'=>$cat->slug, 'ID'=>$cat->term_id);
                                  }
                              $value->tags = $post_tags;

                              $value->post_title    = htmlspecialchars($value->post_title, ENT_QUOTES);
                              $value->post_content  = '';   // htmlspecialchars($value->post_content, ENT_QUOTES);      //FixIn: 5.4.5.10
                              $value->post_excerpt  = htmlspecialchars($value->post_excerpt, ENT_QUOTES);


                              if (! isset($available_booking_resources[$shortcode_attributes['type']])) {
                                  $available_booking_resources[$shortcode_attributes['type']] = $value;
                              }


                          }
//debuge($available_booking_resources);
                          $available_booking_resources_serilized = serialize($available_booking_resources);
                          update_bk_option( 'booking_cache_content' ,  $available_booking_resources_serilized );
                          update_bk_option( 'booking_cache_created' ,    date_i18n('Y-m-d H:i:s'   ) );

              }


              function is_booking_search_cache_expire(){


                  $previos = get_bk_option( 'booking_cache_created'     );
                  $previos = explode(' ',$previos);
                  $previos_time = explode(':',$previos[1]);
                  $previos_date = explode('-',$previos[0]);

                  $previos_sec = mktime($previos_time[0], $previos_time[1], $previos_time[2], $previos_date[1], $previos_date[2], $previos_date[0]);
                  $now_sec = mktime();

                  $period =  get_bk_option( 'booking_cache_expiration'     );

                if (substr($period,-1,1) == 'd' ) {
                    $period = substr($period,0,-1);
                    $period = $period * 24 * 60 * 60;
                }

                if (substr($period,-1,1) == 'h' ) {
                    $period = substr($period,0,-1);
                    $period = $period * 60 * 60;
                }

                  $now_tm = explode(' ',date_i18n('Y-m-d H:i:s'   ) );
                  $now_tm_time = explode(':',$now_tm[1]);
                  $now_tm_date = explode('-',$now_tm[0]);
                  $now_tm_sec = mktime($now_tm_time[0], $now_tm_time[1], $now_tm_time[2], $now_tm_date[1], $now_tm_date[2], $now_tm_date[0]);

                if( ($previos_sec + $period ) > $now_tm_sec )
                    return  0;
                else return  1;
              }

 //</editor-fold>



// <editor-fold defaultstate="collapsed" desc=" C L I E N T   S I D E ">

//   C L I E N T   S I D E        //////////////////////////////////////////////////////////////////////////////////////////////////

    // JavaScript TOOLTIP - Availability  arrays with variables
    function show_availability_at_calendar($blank, $type_id, $max_days_count = 365 ) {

        if ($max_days_count == 365) {

            $max_monthes_in_calendar = get_bk_option( 'booking_max_monthes_in_calendar');

            if (strpos($max_monthes_in_calendar, 'm') !== false) {
                $max_days_count = str_replace('m', '', $max_monthes_in_calendar) * 31 +5;
            } else {
                $max_days_count = str_replace('y', '', $max_monthes_in_calendar) * 365+15 ;
            }

        }
        $start_script_code = '';

        $skip_booking_id = '';  // Id of booking to skip in calendar
        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $skip_booking_id = $my_booking_id_type[0];  
            }
        }


        // Save at the Advnaced settings these 3 parameters
        $is_show_availability_in_tooltips =    get_bk_option( 'booking_is_show_availability_in_tooltips' );
        $highlight_availability_word      =    get_bk_option( 'booking_highlight_availability_word');
        $highlight_availability_word      =  apply_bk_filter('wpdev_check_for_active_language', $highlight_availability_word );

        $is_bookings_depends_from_selection_of_number_of_visitors = get_bk_option( 'booking_is_use_visitors_number_for_availability');

        global $wpdb;
        if (get_bk_option( 'booking_is_show_pending_days_as_available') == 'On')
             $sql_req = $this->get_sql_bk_dates_for_all_resources('', $type_id, '1',  $skip_booking_id) ;
        else $sql_req = $this->get_sql_bk_dates_for_all_resources('', $type_id, 'all',  $skip_booking_id) ;
        $dates_approve   = $wpdb->get_results( $sql_req );


        $busy_dates = array();          // Busy dates and booking ID as values for each day
        $busy_dates_bk_type = array();  // Busy dates and booking TYPE ID as values for each day
        
        $check_in_dates  = array();     // Number of  Bookings with Check - In  Date for this specific date
        $check_out_dates = array();     // Number of  Bookings with Check - Out Date for this specific date
        $is_check_in_day_approved  = array();     // Last Status of Check - In  day  1 - approved, 0 - pending
        $is_check_out_day_approved = array();     // Last Status of Check - Out day  1 - approved, 0 - pending
        
        $temp_time_checking_arr = array();

        // Get DAYS Array with bookings ID inside of each day. So COUNT of day will be number of booked childs
        foreach ($dates_approve as $date_object) {
            $date_without_time = explode(' ', $date_object->booking_date);
            $date_only_time    = $date_without_time[1];
            $date_without_time = $date_without_time[0];

            // Show the Cehck In/Out date as available for the booking resources with  capcity > 1 ///////////////////////////////////////////
            if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') && 
//                 (get_bk_option( 'booking_check_out_available_for_parents') == 'On') &&
                 ( substr($date_only_time,-2) == '02') )  { 
                
                if ( isset( $check_out_dates[ $date_without_time ] ) )
                     $check_out_dates[ $date_without_time ][] = $date_object->type ;                            // $check_out_dates[ $date_without_time ] + 1;
                else $check_out_dates[ $date_without_time ]   = array( $date_object->type );                    // 1
                
                $is_check_out_day_approved[ $date_without_time ] = $date_object->approved ;
                
                continue;  
            }
            if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') && 
//                 (get_bk_option( 'booking_check_in_available_for_parents') == 'On') &&
                 ( substr($date_only_time,-2) == '01') )  { 
                
                if ( isset( $check_in_dates[ $date_without_time ] ) )
                     $check_in_dates[ $date_without_time ][] = $date_object->type ;                         // $check_in_dates[ $date_without_time ] + 1;
                else $check_in_dates[ $date_without_time ]   = array( $date_object->type );                 // 1
                
                $is_check_in_day_approved[ $date_without_time ] = $date_object->approved ;
                
                continue;  
            } /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

            if (!isset( $busy_dates[ $date_without_time ] )) {
                $temp_time_checking_arr[$date_without_time][$date_object->booking_id] = $date_only_time; // For checking single day selection
                $busy_dates[ $date_without_time ] = array($date_object->booking_id);

                if (! empty($date_object->date_res_type)) $busy_dates_bk_type[ $date_without_time ] = array($date_object->date_res_type);
                else                                      $busy_dates_bk_type[ $date_without_time ] = array($date_object->type);


            } else {

                if (   ( isset($temp_time_checking_arr[$date_without_time][$date_object->booking_id])  ) &&
                       (  $temp_time_checking_arr[$date_without_time][$date_object->booking_id]  != $date_only_time )
                   ){
                    // Skip Here is situation, when same booking at the same day and in dif time, so skip it, we are leave only start date
                } else {
                    $busy_dates[ $date_without_time ][] = $date_object->booking_id ;
                    $temp_time_checking_arr[$date_without_time][$date_object->booking_id] = $date_only_time;

                    if (! empty($date_object->date_res_type)) $busy_dates_bk_type[ $date_without_time ][] = $date_object->date_res_type ;
                    else                                      $busy_dates_bk_type[ $date_without_time ][] = $date_object->type ;

                }
            }
        }

        $max_visit_std         = $this->get_max_available_items_for_resource($type_id);
        $is_availability_based_on_items_not_visitors = true;

        $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');
        $availability_based_on_visitors   = get_bk_option( 'booking_availability_based_on');
        if ($is_use_visitors_number_for_availability == 'On')
            if ($availability_based_on_visitors == 'visitors')
                $is_availability_based_on_items_not_visitors = false;
        $max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($type_id);
        $max_visitors_in_bk_res_summ=$this->get_summ_max_visitors_for_bk_resources($type_id);

//debuge($check_in_dates, $check_out_dates, $busy_dates, $max_visitors_in_bk_res,$max_visitors_in_bk_res_summ);

        if ( ($is_show_availability_in_tooltips !== 'On')  )  $start_script_code .= ' is_show_availability_in_tooltips = false; ';
        else                                                  $start_script_code .= ' is_show_availability_in_tooltips = true; ';

        $start_script_code .= " highlight_availability_word =  '". esc_js($highlight_availability_word) .  " '; ";

        $start_script_code .= "  availability_per_day[{$type_id}] = [];  ";
        $start_script_code .= "  wpbc_check_in_dates[{$type_id}] = [];  ";
        $start_script_code .= "  wpbc_check_out_dates[{$type_id}] = [];  ";
        $start_script_code .= "  wpbc_check_in_out_closed_dates[{$type_id}] = [];  ";

        $my_day =  date('m.d.Y' );          // Start days from TODAY
        $type_id_childs =  $this->get_booking_types($type_id);          // Get ID of the all childs elements of this parent resource.

        if (count($type_id_childs)<=1)                  
            $is_single = true;
        else 
            $is_single = false;


        $cached_season_filters = array();
        foreach ($type_id_childs as $bk_type_id_child) { 
            $cached_season_filters[ $bk_type_id_child->id ] = apply_bk_filter('get_available_days', $bk_type_id_child->id );
        }

        for ($i = 0; $i < $max_days_count; $i++) {

            $my_day_arr = explode('.',$my_day);

            $day0 = $day = ($my_day_arr[1]+0);
            $month0 = $month= ($my_day_arr[0]+0);
            $year0 = $year = ($my_day_arr[2]+0);

            if  ($day< 10) $day0 = '0' . $day;
            if  ($month< 10) $month0 = '0' . $month;

            $my_day_tag  =  $month . '-' . $day . '-' . $year ;
            $my_day_tag0 =  $month . '-' . $day0 .'-' . $year0 ;

            // Set rechecking availability based on the season filters of the booking resources:
            $search_date = $year . '-' . $month0 . '-' . $day0 ;

            foreach ($type_id_childs as $bk_type_id_child) {                // Loop in IDs
                if ( $bk_type_id_child->parent != 0 ) {}
                $bk_type_id_child = $bk_type_id_child->id;

                $is_date_available = is_this_day_available_on_season_filters( $search_date, $bk_type_id_child, $cached_season_filters[ $bk_type_id_child ] );    // Get availability

                if (! $is_date_available) {

                    if (!isset( $busy_dates[ $search_date ] )) {
                        $busy_dates[ $search_date ] = array('filter');
                    } else {
                        $busy_dates[ $search_date ][] = 'filter';
                    }

                    if (!isset( $busy_dates_bk_type[ $search_date ] )) {
                        $busy_dates_bk_type[ $search_date ] = array($bk_type_id_child);
                    } else {
                        $busy_dates_bk_type[ $search_date ][] = $bk_type_id_child ;
                    }

                }
            }


            if ($is_availability_based_on_items_not_visitors) { // Calculate availability based on ITEMS

                if (isset($busy_dates[  $year . '-' . $month0 . '-' . $day0   ]))   $my_max_visit = $max_visit_std - count($busy_dates[  $year . '-' . $month0 . '-' . $day0   ]);
                else                                                                $my_max_visit = $max_visit_std;

            } else {                                             // Calculate availability based on VISITORS

                if (isset($busy_dates_bk_type[ $year.'-'.$month0.'-'. $day0   ])) {

                    if ($is_single) { // For single bk res
                        $my_max_visit = $max_visitors_in_bk_res_summ;
                        if ( isset($temp_time_checking_arr[ $year.'-'.$month0.'-'. $day0   ]) ) {
                            foreach ($temp_time_checking_arr[ $year.'-'.$month0.'-'. $day0   ] as $bk_id=>$bk_time) {
                                $bk_time= explode(':',$bk_time);
                                if ( $bk_time[2]=='00' )
                                    $my_max_visit = 0;
                            }
                        }

                    } else {  // For Parent bk res
                        $already_busy_visitors_summ = 0 ;
                        foreach ($busy_dates_bk_type[ $year.'-'.$month0.'-'. $day0   ] as $busy_type_id) {
                            if (isset($max_visitors_in_bk_res[ $busy_type_id ]))
                                $already_busy_visitors_summ += $max_visitors_in_bk_res[ $busy_type_id ];
                        }
//if ( $my_day_tag == '1-24-2016' ) debuge($busy_dates_bk_type, $max_visitors_in_bk_res_summ , $already_busy_visitors_summ)    ;                    
                        $my_max_visit = $max_visitors_in_bk_res_summ - $already_busy_visitors_summ;
                    }
                } else  $my_max_visit = $max_visitors_in_bk_res_summ;

            }
//if ( $my_day_tag == '1-24-2016' )  debuge($my_day_tag, $my_max_visit);
//if ( $my_day_tag == '1-24-2016' )  debuge($check_in_dates, $check_out_dates);
            $start_script_code .= "  availability_per_day[". $type_id ."]['".$my_day_tag."'] = '".$my_max_visit."' ;  ";
            
            // check for the CLOSED days (where exist  check in and check out dates of the same Child resources
            $check_in_out_closed_dates = 0;
            if (  ( isset( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) ) && ( isset( $check_out_dates[ "{$year}-{$month0}-{$day0}" ] ) )  ){
                                  
                $check_in_out_closed_dates = array_intersect($check_in_dates[ "{$year}-{$month0}-{$day0}" ], $check_out_dates[ "{$year}-{$month0}-{$day0}" ] );            
//if ( $my_day_tag == '1-24-2016' )  debuge($check_in_out_closed_dates)                ;
                $check_in_out_closed_dates = count( $check_in_out_closed_dates );
                $start_script_code .= " wpbc_check_in_out_closed_dates[{$type_id}]['{$my_day_tag}'] = {$check_in_out_closed_dates}; ";
            }            
            if ( isset( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) ){
                $start_script_code .= " wpbc_check_in_dates[{$type_id}]['{$my_day_tag}'] = ["  
                            . "[" . ( count( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) - $check_in_out_closed_dates ) . "]"
                            . ',' .$is_check_in_day_approved[ "{$year}-{$month0}-{$day0}" ] 
                            . "]; " ;
            }             
            if ( isset( $check_out_dates[ "{$year}-{$month0}-{$day0}" ] ) ){
                $start_script_code .= " wpbc_check_out_dates[{$type_id}]['{$my_day_tag}'] = [" 
                            . "[" . ( count( $check_out_dates[ "{$year}-{$month0}-{$day0}" ]  ) - $check_in_out_closed_dates ) . "]"
                            . ',' .$is_check_out_day_approved[ "{$year}-{$month0}-{$day0}" ] 
                            . "]; " ;
            }
            
//if ( $my_day_tag == '1-24-2016' )          
//   debuge(  
//              'wpbc_check_in_dates',  ( count( $check_in_dates[ "{$year}-{$month0}-{$day0}" ] ) - $check_in_out_closed_dates ), $is_check_in_day_approved[ "{$year}-{$month0}-{$day0}" ] 
//            , 'wpbc_check_out_dates',  ( count( $check_out_dates[ "{$year}-{$month0}-{$day0}" ]  ) - $check_in_out_closed_dates ), $is_check_out_day_approved[ "{$year}-{$month0}-{$day0}" ] 
//            , 'availability_per_day', $my_max_visit
//            , 'wpbc_check_in_out_closed_dates', $check_in_out_closed_dates
//   );            
   
   
   
            $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));   // Next day
        }

        //$max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($type_id);
        foreach ($max_visitors_in_bk_res as $key=>$value) {
            if(! empty($key))
             $start_script_code .= "  max_visitors_4_bk_res[". $key ."] = ".$value." ;  ";
        }           
//die;        
//debugq();            
        return $start_script_code;
    }

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" S U P P O R T     A D M I N     F u n c t i o n s ">

// S U P P O R T     A D M I N     F u n c t i o n s    ///////////////////////////////////////////////////////////////////////////////////

        // Just Get ALL booking types from DB
        function get_booking_types($booking_type_id = 0, $where = '') {
            global $wpdb;                
            $additional_fields = '';

            if ($where === '') {
                $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                $us_id = apply_bk_filter('get_user_of_this_bk_resource', false, $booking_type_id );
                if ($us_id !== false)
                    $where =  $wpdb->prepare( " users = %d " , $us_id );
            }

            if ($booking_type_id != 0 ) {

                $where1 = $wpdb->prepare( " WHERE ( booking_type_id = %d OR parent = %d ) ", $booking_type_id, $booking_type_id);

                if ($where != '')   $where = $where1 . ' AND ' . $where;
                else                $where = $where1;

            } else {
                if ($where != '') $where = ' WHERE ' . $where;
            }

            if ( class_exists('wpdev_bk_multiuser')) {  // If Business Large then get resources from that
                $additional_fields = ', users ';
            }

            $wpbc_sql = "SELECT booking_type_id as id, title, parent, prioritet, cost, visitors {$additional_fields} 
                         FROM {$wpdb->prefix}bookingtypes {$where} ORDER BY parent, prioritet" ;

            $types_list = $wpdb->get_results( $wpbc_sql );
            return $types_list;
        }

                // Get hierarhy structure TREE of booking resources
                function get_booking_types_hierarhy($bk_types=array()) {

                    if ( count($bk_types)==0) $bk_types = $this->get_booking_types();

                    $res= array( );

                    foreach ($bk_types as $bt) {
                        if ( $bt->parent == '0' ) {
                            $res[$bt->id] = array( 'obj'=> $bt,  'child'=>array() , 'count'=>1 );
                        }
                    }

                    foreach ($bk_types as $bt) {
                        if ( $bt->parent != '0' ) {
                            if (! isset($res[$bt->parent]['child'][$bt->prioritet])) $res[$bt->parent]['child'][$bt->prioritet] = $bt;
                            else $res[$bt->parent]['child'][ 100* count($res[$bt->parent]['child']) ] = $bt;
                            $res[$bt->parent]['count'] = count($res[$bt->parent]['child'])+1;
                        }
                    }
                    return $res;
                }

                        // FUNCTION  FOR SETTINGS ////////////////////////////////////////////////////////////
                        // Get linear structure of resources from hierarhy for showing it at the settings page
                        function get_booking_types_hierarhy_linear($bk_types=array()) {
                            if ( count($bk_types)==0) $bk_types = $this->get_booking_types_hierarhy();

                            $res= array();

                            foreach ($bk_types as $bt) {
                                if (isset($bt['obj']))
                                    $res[] = array( 'obj' => $bt['obj'], 'count' => $bt['count'] );
                                foreach ($bt['child'] as $b) {
                                    $res[] = array( 'obj' => $b, 'count' => '1' );
                                }
                            }

                            return $res;
                        }


        // Get Maximum available of items for this resource. Based on capacity.
        function get_max_available_items_for_resource($bk_type) {
            $bk_types =  $this->get_booking_types($bk_type);
            $bk_types =  $this->get_booking_types_hierarhy($bk_types);
            if (isset($bk_types[$bk_type]))
                if (isset($bk_types[$bk_type]['count']))
                    $max_available_items = $bk_types[$bk_type]['count']  ;

            if (isset($max_available_items))
                return $max_available_items;
            else
                return 1;
        }


        // Get NUM of Visitors, which was filled at booking form, if USE VISITORS NUM is Active
        function get_num_visitors_from_form($formdata, $bktype){

            if (get_bk_option( 'booking_is_use_visitors_number_for_availability') == 'On')
                 $is_use_visitors_number_for_availability =  true;
            else $is_use_visitors_number_for_availability =  false;

            $visitors_number = 1;

            if ($is_use_visitors_number_for_availability) {
                if (isset($formdata)) {
                    $form_data =  get_form_content($formdata, $bktype) ;
                    if ( isset($form_data['visitors']) ) {
                        $visitors_number = $form_data['visitors'];
                    }
                }
                return $visitors_number;
            } else return 1;


        }


        // Get Array with ID of booking resources and MAX visitors for each of BK Resources
        function get_max_visitors_for_bk_resources($booking_type_id = 0){

                $bk_types = $this->get_booking_types($booking_type_id);
                $bk_types = $this->get_booking_types_hierarhy($bk_types);
                $bk_types = $this->get_booking_types_hierarhy_linear($bk_types);        // Get linear array sorted by Priority

                $max_visitors_for_bk_types = array();
                foreach ($bk_types as $value) {
                    if (isset($value['obj']->visitors))
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = $value['obj']->visitors ;
                    else
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = 1;
                }

                return $max_visitors_for_bk_types;
        }

        // Just MAX Number of visitors
        function get_summ_max_visitors_for_bk_resources($booking_type_id = 0){
            $max_visitors_in_bk_res = $this->get_max_visitors_for_bk_resources($booking_type_id);
            $max_visitors_in_bk_res_summ=0;
            foreach ($max_visitors_in_bk_res as $value_element) {
                $max_visitors_in_bk_res_summ += $value_element;
            }
            return $max_visitors_in_bk_res_summ;
        }
// </editor-fold>



// <editor-fold defaultstate="collapsed" desc="A d m i n   D A T E S    F u n c t i o n s">

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// A d m i n   D A T E S    F u n c t i o n s     ////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //
        //    Check at which child BK RES this Booking resource have to be  - SQL UPDATE resource
        //
        // Params: 'wpdev_booking_reupdate_bk_type_to_childs', $booking_id, $bktype, str_replace('|',',',$dates),  array($start_time, $end_time )
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //      TODO: 2. May be We need to set for each item (child resource) maximum support of visitors
        function reupdate_bk_type_to_childs($booking_id, $bktype, $dates, $start_end_time_arr , $formdata, $skip_page_checking_for_updating = false  ) {

            // Skip Reupdate is the Show Pending as available is activated.
            $is_show_pending_days_as_available                          = get_bk_option( 'booking_is_show_pending_days_as_available');
            if  ($is_show_pending_days_as_available == 'On')   return false;


            global $wpdb;
//debuge($booking_id, $bktype, $dates, $start_end_time_arr , $formdata );
//TODO: DEBUG
//REMOVE TODAT DEBUG
//$booking_id = 8;
//$bktype =2;
//$dates = '15.02.2011, 16.02.2011, 17.02.2011';
//$start_end_time_arr = array( array('00','00','00') , array('00','00','00') );
//$formdata = 'text^name2^fsdfs~text^secondname2^SDD~email^email2^email@server.com~text^address2^adress~text^city2^city~text^postcode2^post code~select-one^country2^GB~select-one^visitors2^3';
//debuge($booking_id, $bktype, $dates, $formdata );


            $bk_types =  $this->get_booking_types($bktype);                 // Get Hierarhy structure of BK Resource
            $bk_types =  $this->get_booking_types_hierarhy($bk_types);
            $max_available_items = 0;

            if (isset($bk_types[$bktype]))
                if (isset($bk_types[$bktype]['count']))
                    $max_available_items = $bk_types[$bktype]['count']  ;           // Max Childs count


            //Sort the Dates in the correct order:    
            $my_dates = explode(',',$dates);                                   
            $i=0;
            foreach ($my_dates as $md) { // Set in dates in such format: yyyy.mm.dd
                if ($md != '') {
                    $md = trim($md);
                    $md = explode('.',$md);
                    $my_dates[$i] = $md[2] . '.' . ( (intval($md[1])<10) ? ('0'.intval($md[1])) : $md[1] ) . '.' . ( (intval($md[0])<10) ? ('0'.intval($md[0])) : $md[0] ) ;
                    //$my_dates[$i] = $md[2] . '.' . $md[1] . '.' . $md[0] ;
                } else { unset($my_dates[$i]) ; } // If some dates is empty so remove it   // This situation can be if using several bk calendars and some calendars is not checked
                $i++;
            }
            sort($my_dates); // Sort dates


//debuge($my_dates);                


            //if (count($dates)==1)   // We have only 1 day and DIF Time, so duplicate this one day
                //if ($start_end_time_arr[0]!=$start_end_time_arr[1])
                    //$dates[] = $dates[0]  ;
//debuge($bktype, $bk_types, $max_available_items);
//debuge($dates);

            $dates_new =  array();
            foreach ($my_dates as $d) {
                $d1 = explode('.',trim($d));
                $day =    $d1[2];
                $month =  $d1[1];
                $year =   $d1[0];
                $dates_new[] = intval($month) .'-'. intval($day) .'-'. intval($year)  ;// Month-Day-Year   Dates for normal using inside of this Funcion
            }
//REMOVE TODAT DEBUG debuge($dates_new);

            $my_page = 'client';                                            // Get a page
            if ( ! $skip_page_checking_for_updating ) {
                if (        strpos($_SERVER['HTTP_REFERER'],'wpdev-booking.phpwpdev-booking-reservation') !== false )  
                    $my_page = 'add';
                else if ( strpos($_SERVER['HTTP_REFERER'],'wpdev-booking.phpwpdev-booking')!==false)                   
                    $my_page = 'booking';
            }
        if ( isset($_SERVER['HTTP_REFERER'] ) )
            if ( strpos($_SERVER['HTTP_REFERER'],'parent_res') !== false  ) $my_page = 'client'; // admin add page and we add at parent res
        if ($my_page == 'add') if ( $this->check_if_bk_res_have_childs($bktype) ) { $my_page = 'client'; }
//REMOVE TODAT DEBUG debuge($my_page);

        if ( ($my_page == 'client')  ) // If client so go on

            if ($max_available_items > 1) {                                 // Make change only if we have some childs - capacity

               $visitors_number = $this->get_num_visitors_from_form($formdata, $bktype);     // Get NUM Visitors from bk form, if use visitors num for availability is active else return false
               $updated_type_id = $bktype;                                  // Bk TYPE ID
               $bk_types        = $this->get_booking_types_hierarhy_linear($bk_types);       // Get linear array sorted by Priority

//REMOVE TODAT DEBUG
//debuge($visitors_number, $dates_new, $bk_types);
//die;
//REMOVE TODAT DEBUG
//debuge('O - Get Matrix for Bk Types');
               // 0. Get Matrix for Bk Types with busy days for each types.
               // Example: [ [TYPE_ID] => [  [DATE]=>BK_ID, [10-23-2010]=>22  ], [5] => .....  ]
               /*     [1] => Array (
                            [1-12-2011] => 5
                            [1-13-2011] => 5
                            [1-14-2011] => 5
                            [1-17-2011] => 2
                            [1-18-2011] => 2 )
                      [6] => Array (
                            [1-13-2011] => 7
                            [1-14-2011] => 7
                            [1-15-2011] => 7
                            [1-18-2011] => 4
                            [1-19-2011] => 4 )
               /**/
               $bk_types_busy_dates_matrix = array();
               foreach ($bk_types as $res_obj) {
                   $r_id = $res_obj['obj']->id;
                   $bk_types_busy_dates_matrix[$r_id] = $this->get_all_reserved_day_for_bk_type('all', $r_id, $booking_id );
               }
//REMOVE TODAT DEBUG
//debuge('$bk_types_busy_dates_matrix',$bk_types_busy_dates_matrix);

// $dates_for_each_visitors - reduce the size of this archive based on visitors number of specific type


                // 0. Create INIT arrays for visitors:
                // Example: $is_this_visitor_setup  = [ [0]=false, [1]=false ...]
                //          $dates_for_each_visitors= [ [0]=>['11-20-2010'=>false, '11-20-2010'=>false], [1]=>['11-20-2010'=>false, '11-20-2010'=>false]...]
                $dates_for_each_visitors = array();
                $is_this_visitor_setup = array();
                for ($i = 0; $i < $visitors_number; $i++) {
                    $dates_for_each_visitors[$i]=array();
                    foreach ($dates_new as $selected_date) {
                        $dates_for_each_visitors[$i][$selected_date] = false;               // this date is not set up to some room (bk type)
                    }
                    $is_this_visitor_setup[$i]=false;                       // this visitor is not SETUP yet
                }
//REMOVE TODAT DEBUG
//debuge( '$is_this_visitor_setup, $dates_for_each_visitors, $visitors_number',  $is_this_visitor_setup, $dates_for_each_visitors, $visitors_number);



                // Get Max number of visitors for each booking type from linear array
                // Exmaple: [bk_ID] => MAX_VISITORS
                /*          [1] => 1
                            [6] => 1
                            [7] => 3
                            [8] => 2
                /**/
                $max_visitors_for_bk_types = array();
                foreach ($bk_types as $value) {
                    if (isset($value['obj']->visitors))
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = $value['obj']->visitors ;
                    else
                        $max_visitors_for_bk_types[  $value['obj']->id  ] = 1;
                }

//REMOVE TODAT DEBUG
//debuge('$max_visitors_for_bk_types',$max_visitors_for_bk_types);
//die;
//debuge('$bk_types_busy_dates_matrix',$bk_types_busy_dates_matrix);
                // 1. Check availability of days in BK Resource, WITHOUT JUMPING ONE BOOKING TO DIF Resources
                $vis_num = 0;                                                     // Visitor NUMber
                foreach ($bk_types_busy_dates_matrix as $bk_type_id => $busy_dates) {       //Example: [ [5] => [  [DATE]=>BK_ID, [10-23-2010]=>22  ], .....  ]


                    if ($vis_num>= count($is_this_visitor_setup)) break;         //  All visitor -> Return
                    while ($is_this_visitor_setup[$vis_num] !== false) {          // Next visitor. This visitor is setuped.
                          $vis_num++;
                          if ($vis_num>= count($is_this_visitor_setup)) break;   //  All visitor -> Return
                    }
                    if ($vis_num>= count($is_this_visitor_setup)) break;         // All visitor -> Return



                    $is_some_dates_busy_in_this_type = false;               // Check all SELECTED dates  line inside of this TYPE (room)
                    foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {
                        if (isset($busy_dates[$selected_date_for_visitor]))  { $is_some_dates_busy_in_this_type = true ; break;  } // Some day  is busy, get next bk type
                    }

                    if ($is_some_dates_busy_in_this_type === false ) { // All days is FREE inside this type
                        $is_this_visitor_setup[$vis_num] = 1;                                 // This visitor is SETUPED
                        foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {
                            $dates_for_each_visitors[$vis_num][$selected_date_for_visitor] = $bk_type_id;

                            $bk_types_busy_dates_matrix[$bk_type_id][$selected_date_for_visitor] =  $booking_id; // MARK ALSO MATRIX
                        }

                        // Reduce the number of visitors based on visitor capacity for this booking resource (MAX VIS NUMBER)
                        $reduce_value_based_on_max_visitors = $max_visitors_for_bk_types[$bk_type_id] - 1 ;
                        for ($re = 0; $re < $reduce_value_based_on_max_visitors; $re++) {
                           // array_pop( $dates_for_each_visitors );          //decrese the number of visitors
                           // array_pop( $is_this_visitor_setup );
                            $vis_num++;                                                           // Next visitor
                            if ($vis_num>= count($is_this_visitor_setup)) break;
                            $is_this_visitor_setup[$vis_num] = 1;                                 // This visitor is SETUPED
                            $dates_for_each_visitors[$vis_num] = array();
                        }

                        $vis_num++;                                                           // Next visitor
                    }
                }
//array_pop( $dates_for_each_visitors );
//REMOVE TODAT DEBUG
//debuge($is_this_visitor_setup, $dates_for_each_visitors, $bk_types_busy_dates_matrix);
//REMOVE TODAT DEBUG
//die;



                // Continue Check availability of days in BK Resource, WITH JUMPING
                // (  One visitor can be start in one resource then go to other resource )
                while ($vis_num< count($is_this_visitor_setup)) {                    // Check if we proceed all visitors if not so go inside

                        if ($vis_num>= count($is_this_visitor_setup)) break;         // We are proceed all visitor, so return
                        while ($is_this_visitor_setup[$vis_num] !== false) {          // This visitor is setuped so get next one
                              $vis_num++;
                              if ($vis_num>= count($is_this_visitor_setup)) break;   // We are proceed all visitor, so return
                        }
                        if ($vis_num>= count($is_this_visitor_setup)) break;         // We are proceed all visitor, so return



                        foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {

                            foreach ($bk_types_busy_dates_matrix as $bk_type_id => $busy_dates) {  //Example: [ [5] => [  [DATE]=>BK_ID, [10-23-2010]=>22  ], .....  ]

                                if (! isset($busy_dates[$selected_date_for_visitor]))  { // DATE is FREE in This Resource

                                    if (isset($dates_for_each_visitors[$vis_num][$selected_date_for_visitor])) {

                                        $dates_for_each_visitors[$vis_num][$selected_date_for_visitor] = $bk_type_id;        // Set Room for selected day of visitor
                                        $bk_types_busy_dates_matrix[$bk_type_id][$selected_date_for_visitor] = $booking_id;  // MARK MATRIX

                                    }

                                    // Reduce the number of visitors based on visitor capacity for this booking resource (MAX VIS NUMBER)
                                    $reduce_value_based_on_max_visitors = $max_visitors_for_bk_types[$bk_type_id] - 1 ;
                                    for ($re = 1; $re <= $reduce_value_based_on_max_visitors; $re++) {

                                        if ( isset($dates_for_each_visitors[ $vis_num + $re ]) )  // Check if this visitor is exist
                                            if ( isset( $dates_for_each_visitors[ $vis_num + $re ][$selected_date_for_visitor] ) ) {  // Check if this date is exist
                                                //unset( $dates_for_each_visitors[ $vis_num + $re ][$selected_date_for_visitor] );     // Unset

                                                if ( ($vis_num + $re) >= count($is_this_visitor_setup)) break;
                                                $is_this_visitor_setup[$vis_num + $re] = 1;                                 // This visitor is SETUPED
                                                $dates_for_each_visitors[$vis_num + $re] = array();

                                            }
                                    }


                                    break; // Get next date of visitor
                                }

                            }
                        } // Process all days from visitor

                        $is_this_visitor_setup[$vis_num] = 1; // Mark this visitor as setuped and recheck below in loop this
                        foreach ( $dates_for_each_visitors[$vis_num] as $selected_date_for_visitor=>$is_day_setup ) {
                            if ($is_day_setup === false ) $is_this_visitor_setup[$vis_num] = false;
                        }

                        $vis_num++; // Get next visitor
                }


//debuge( $updated_type_id);
                    ////////////////////////////////////////////////////////
                    // MAKE UPDATE OF    DB
                    ////////////////////////////////////////////////////////

                    // Get default bk Resource  - Type   (  first visitor, first day type)
                if ( (count($dates_for_each_visitors) > 0 ) && (is_array($dates_for_each_visitors[0])) )
                    foreach ($dates_for_each_visitors[0] as $value) { if (!empty($value)) {$updated_type_id=$value;} break; }

//debuge($dates_for_each_visitors, $updated_type_id);
                   //  Updated ID with NEW - UPDATE Booking TABLE    with new bk. res. type
                   if ( $updated_type_id != $bktype ) {

                        // Fix the booking form ID of elements /////////////////////////////////////////////////////////////////
                        $formdata_new = '';
                        $formdata_array = explode('~',$formdata);
                        $formdata_array_count = count($formdata_array);
                        for ( $i=0 ; $i < $formdata_array_count ; $i++) {
                            $elemnts = explode('^',$formdata_array[$i]);

                            $type = $elemnts[0];
                            $element_name = $elemnts[1];
                            $value = $elemnts[2];

                            if ( substr($element_name, -2 ) == '[]' )
                                $element_name = substr($element_name, 0, -1 * (strlen($bktype)+2) ) . $updated_type_id . '[]' ;  // Change bk RES. ID in elemnts of FORM
                            else
                                $element_name = substr($element_name, 0, -1 * strlen($bktype) ) . $updated_type_id  ;  // Change bk RES. ID in elemnts of FORM

                            if ($formdata_new!='') $formdata_new.= '~';
                            $formdata_new .= $type . '^' . $element_name . '^' . $value;
                        } ////////////////////////////////////////////////////////////////////////////////////////////////

                        // Update
                        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.form = %s, bk.booking_type=%d WHERE bk.booking_id = %d ;"
                                , $formdata_new , $updated_type_id, $booking_id );

                        if ( false === $wpdb->query( $update_sql ) ){
                            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating exist booking type in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                            die();
                        }
                   }/////////////////////////////////////////////////////////////

                   // Update Dates:

                   // Firstly delete all dates, from Basic insert for future clean work
                   if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id = %d ", $booking_id ) ) ){
                         ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during booking dates cleaning in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                        die();
                   }


                   // If we have situation with bookings in diferent resource so we are delete current booking and need to show error message. ////////
                   $booking_is_dissbale_booking_for_different_sub_resources = get_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources');
                   if( $booking_is_dissbale_booking_for_different_sub_resources == 'On') {

                           $is_dates_inside_one_resource = true;            // We will recheck if all days inside of one resource, or there is exist some jumping.
                           foreach ($dates_for_each_visitors as $vis_num => $array_dates_for_each_visitors) {
                               foreach ($array_dates_for_each_visitors as $k_day => $v_type_id) {
                                   $type_id_for_this_user = $v_type_id;
                                   break;
                               }
                               foreach ($array_dates_for_each_visitors as $k_day => $v_type_id) {
                                  if ($v_type_id != $type_id_for_this_user) {
                                      $is_dates_inside_one_resource = false;
//debuge( '$updated_type_id, $k_day,  $v_type_id,  $is_dates_inside_one_resource', $updated_type_id, $k_day,  $v_type_id,  $is_dates_inside_one_resource)                                      ;
                                      break;
                                  }
                               }
                           }
                           if ( ! $is_dates_inside_one_resource ) {
                               if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id = %d ", $booking_id ) ) ){
                                     ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during booking dates cleaning in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                                    die();
                               }
                               echo ' ';
                               ?> <script type="text/javascript">
                                         if (type_of_thank_you_message == 'page') {      // Page
                   //                         thank_you_page_URL = window.location.href;
                   //                         location.href= thank_you_page_URL;
                                              clearTimeout(timeoutID_of_thank_you_page)
                                         }
                                         document.getElementById('paypalbooking_form<?php echo $bktype; ?>').style.display='none';
                                         document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div class="wpdev-help-message alert alert-error" style="height:auto;width:90%;text-align:center;margin:1px auto;"><?php printf(__('Sorry, the reservation was not made because these days are already booked!!! %s (Its not possible to store this sequence of the dates into the one resource.) %s Please %srefresh%s the page and try other days.' ,'booking') ,'<br />','<br />','<a href="javascript:void(0)" onclick="javascript:location.reload();">','</a>'); ?></div>';
                                         jQuery('.booking_summary').hide();
                                    </script>
                               <?php
                               exit;
                           }
                   }

                   // Now insert all new dates
                   $insert='';
                   $start_time = $start_end_time_arr[0];
                   $end_time   = $start_end_time_arr[1];
                    $is_approved_dates = '0';
                    $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
                    if ( trim($auto_approve_new_bookings_is_active) == 'On')
                        $is_approved_dates = '1';

//debuge($dates_for_each_visitors);
                   foreach ($dates_for_each_visitors as $vis_num => $value_dates) {

                       // We have selection only one day and times is diferent
                       if ( ( count($value_dates)==1 ) && ( $start_time != $end_time ) ) $value_dates[]='previos_day';


                       $i=0;
                       foreach ($value_dates as $my_date_init => $bk_type_for_date_init) { $i++;

                            if ($bk_type_for_date_init != 'previos_day' ) {              // Checking for one day selection situation
                                $my_date          = $my_date_init;
                                $my_date = explode('-',$my_date);
                                $bk_type_for_date = $bk_type_for_date_init;
                            }

                            if ( get_bk_option( 'booking_recurrent_time' ) !== 'On') {

                                if ($i == 1) {
                                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $start_time[0], $start_time[1], $start_time[2] );
                                }elseif ($i == count($value_dates)) {
                                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $end_time[0], $end_time[1], $end_time[2] );
                                }else {
                                    $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], '00', '00', '00' );
                                }

                                if ( !empty($insert) ) $insert .= ', ';
                                if ($bk_type_for_date !== $updated_type_id) $insert .= $wpdb->prepare( "(%d, %s, %d, %d)", $booking_id, $date, $is_approved_dates, $bk_type_for_date );
                                else                                        $insert .= $wpdb->prepare( "(%d, %s, %d, NULL)",$booking_id,$date, $is_approved_dates );

                            } else {

                                //if ($my_date_previos  == $my_date) continue; // escape for single day selections.
                                $my_date_previos = $my_date;

                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $start_time[0], $start_time[1], $start_time[2] );
                                if ( !empty($insert) ) $insert .= ', ';
                                if ($bk_type_for_date !== $updated_type_id) $insert .= $wpdb->prepare( "(%d, %s, %d, %d)", $booking_id, $date, $is_approved_dates, $bk_type_for_date );
                                else                                        $insert .= $wpdb->prepare( "(%d, %s, %d, NULL)",$booking_id,$date, $is_approved_dates );

                                $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[2], $my_date[0], $my_date[1], $end_time[0], $end_time[1], $end_time[2] );
                                if ( !empty($insert) ) $insert .= ', ';
                                if ($bk_type_for_date !== $updated_type_id) $insert .= $wpdb->prepare( "(%d, %s, %d, %d)", $booking_id, $date, $is_approved_dates, $bk_type_for_date );
                                else                                        $insert .= $wpdb->prepare( "(%d, %s, %d, NULL)",$booking_id,$date, $is_approved_dates );
                            }

                       }
                   }

                   if ( !empty($insert) )
                        if ( false === $wpdb->query( "INSERT INTO {$wpdb->prefix}bookingdates (booking_id, booking_date, approved, type_id) VALUES {$insert}"  ) ){
                            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during inserting into BD - Dates' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                            die();
                        }

             } // end if of $max_available_items > 1

        }

                // Get Array with busy dates of BK Resource with values as bk_IDs  [9-21-2010] => bk_id, ...
                function get_all_reserved_day_for_bk_type($approved = 'all', $bk_type = 1, $skip_booking_id = '') {
                   global $wpdb;
                    $dates_array = $time_array = array();

                    // Get all reserved dates for $bk_type, including childs But skiped this booking: $skip_booking_id
                    $sql_req = $this->get_sql_bk_dates_for_all_resources('', $bk_type, $approved, $skip_booking_id) ;
//debuge($skip_booking_id, $sql_req);                        
                    $dates_approve   = $wpdb->get_results( $sql_req );

                    // Get Array with MAX available days
                    // $available_dates = $this->get_bk_dates_for_all_resources( $dates_approve, $approved, 1, $bk_type) ;
                    $return_dates = array();

                    foreach ($dates_approve as $my_date) {
                        if (  ($my_date->date_res_type == $bk_type) || ( is_null($my_date->date_res_type) )   ){ // Dates belong only to this BK Res (type)
                            $my_dat = explode(' ',$my_date->booking_date);


                            // Show the Cehck In/Out date as available for the booking resources with  capcity > 1 //Bence
                            if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') && 
                                 (get_bk_option( 'booking_check_out_available_for_parents') == 'On') &&
                                 (substr($my_dat[1],-2) == '02') ) continue;
                            if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') && 
                                 (get_bk_option( 'booking_check_in_available_for_parents') == 'On') &&
                                 (substr($my_dat[1],-2) == '01') ) continue;                                
                            /**/
                            $my_dt = explode('-',$my_dat[0]);
                            $my_key =  $my_dt[0].'-'.$my_dt[1].'-'.$my_dt[2] ;
                            $my_key_new =  ($my_dt[1]+0).'-'.($my_dt[2]+0).'-'.($my_dt[0]+0) ;

                            $return_dates[$my_key_new]  =  $my_date->booking_id;// $available_dates[$my_key]['max'];
                        }
                    }
                    // TODO, later booking ID have to be NUM of availabe seats at this type
                    return $return_dates;       // Return array each KEY - its Day, Value - booking ID
                }


        // Get UnAvailable days (availability == 0) from - $dates_approve and return only them for client side
        // OR  return availability array (MAX available items array) if $is_return_available_days_array = 1 at client side page
        function get_bk_dates_for_all_resources($dates_approve, $approved, $is_return_available_days_array = 0, $bk_type = 1) {  //return $dates_approve;

            if (count($dates_approve) == 0 )  return array();               // If emty so then return empty


            $max_available_items = $this->get_max_available_items_for_resource($bk_type);   // Get MAX aavailable Number

            $my_page = 'client';                                            // Get a page
            if (        strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation') !== false )  $my_page = 'add';
            else if ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking')!==false)                   $my_page = 'booking';


            if  ( ( $my_page == 'add' ) && ( isset($_GET['parent_res'])) ) $my_page = 'client';

            // If NOT Client page so then return this dates
            if (
                    ( $my_page == 'booking' ) ||
                    ( ( $my_page == 'add' ) && (! isset($_GET['parent_res'])) )
               ) {
                return $dates_approve;
               }




            $available_dates = array();
            $return_dates = array();



            // check correct sort of dates with times: /////////////////////
            // For exmaple if we have 2 bookings for same date at [1]09:00-10:00 and [2]10:00-11:00 the sort we will have:
            // [1]09:00 , [2]10:00 , [1]10:00 , [2]11:00 
            // but need to have
            // [1]09:00 , [1]10:00 , [2]10:00 , [2]11:00 
            ////////////////////////////////////////////////////////////////
//if ($approved=='0') debuge($dates_approve);
            $dates_correct_sort = array();
            foreach ($dates_approve as $my_date) {

                $formated_date = $my_date->booking_date;                                            // Nice Time & Date
                $formated_date = explode(' ',$formated_date);
                $curr_nice_date = $formated_date[0];
                $curr_nice_time = $formated_date[1];
                $formated_date[0] = explode('-',$formated_date[0]);
                $formated_date[1] = explode(':',$formated_date[1]);

                //Set this check in/out availble only if ht emax availbvale items > 1 - for the parents elements
                if (($my_page == 'client') && ($max_available_items>1)){   // Bence
                    // Show the Cehck In/Out date as available for the booking resources with  capcity > 1
                    if ( (get_bk_option( 'booking_range_selection_time_is_active')  == 'On') && 
                         (get_bk_option( 'booking_check_out_available_for_parents') == 'On') &&
                         ($formated_date[1][2] == '2') ) continue;
                    if ( (get_bk_option( 'booking_range_selection_time_is_active') == 'On') && 
                         (get_bk_option( 'booking_check_in_available_for_parents') == 'On') &&
                         ($formated_date[1][2] == '1') ) continue;                                                    
                }/**/

//debuge($formated_date[1][2]);

                if ( empty($my_date->date_res_type) )   $curr_bk_type = $my_date->type;             // Nice Type
                else                                    $curr_bk_type = $my_date->date_res_type;

                $curr_bk_id = $my_date->booking_id;                                                 //Nice bk ID


                if (! isset($dates_correct_sort[ $curr_bk_type ]))          // Type
                    $dates_correct_sort[ $curr_bk_type ] = array();

                if (! isset($dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ]))          // Date
                    $dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ] = array();

                if (! isset($dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ][ $curr_bk_id ]))          // ID
                    $dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ][ $curr_bk_id ] = array();

                $dates_correct_sort[ $curr_bk_type ][ $curr_nice_date ][ $curr_bk_id ][ $curr_nice_time ] = $my_date;    // Time
            }


            // Change ID key to Time key
            foreach ($dates_correct_sort as $k_type=>$bt_value) {
                foreach ($bt_value as $k_date=>$bd_value) {

                    foreach ($bd_value as $k_id=>$bid_value) {
                        ksort($dates_correct_sort[ $k_type ][ $k_date ][ $k_id ]);  // Sort time inside of single booking
                        foreach ($bid_value as $k_start_time => $date_finish_value) {
                            $dates_correct_sort[ $k_type ][ $k_date ][ $k_start_time ] = $dates_correct_sort[ $k_type ][ $k_date ][ $k_id ];
                            unset($dates_correct_sort[ $k_type ][ $k_date ][ $k_id ]);
                            break;
                        }
                    }
                  ksort($dates_correct_sort[ $k_type ][ $k_date ]);         // Sort inside of date by time
                }
            }


            // Compress to linear array
            $linear_dates_array = array();
            foreach ($dates_correct_sort as $bt_value) {
                foreach ($bt_value as  $bd_value) {
                    foreach ($bd_value as $bstarttime_value) {
                        foreach ($bstarttime_value as $bstart_end_time_value) {
                            $linear_dates_array[] = $bstart_end_time_value;
                        }
                    }
                }
            }
            $dates_approve = $linear_dates_array;

//debuge($dates_approve, $dates_correct_sort);
            if ($max_available_items == 1) {

                 $booking_id_arr = array();
                 foreach ($dates_approve as $my_date) {

                        if ($my_date->approved == $approved) {
                            $booking_id_arr[]=$my_date->booking_id;
                            array_push($return_dates, $my_date);
                        }
                 }
//debuge($return_dates);
                return $return_dates;
            }




            // Get max available items for specific date.
            // $max_available_items

//debuge($max_available_items);

            // Sort all bookings by dates
            $bookings_in_dates = array();
            foreach ($dates_approve as $my_date) {
                $formated_date = $my_date->booking_date;                                            // Nice Time & Date
                $formated_date = explode(' ',$formated_date);
                $curr_nice_date = $formated_date[0];
                $curr_nice_time = $formated_date[1];
                $formated_date[0] = explode('-',$formated_date[0]);
                $formated_date[1] = explode(':',$formated_date[1]);

                if (! isset($bookings_in_dates[ $curr_nice_date ])) $bookings_in_dates[ $curr_nice_date ] = array();
                if (! isset($bookings_in_dates[ $curr_nice_date ][ 'id' ])) $bookings_in_dates[ $curr_nice_date ][ 'id' ] = array();

                if (! isset($bookings_in_dates[ $curr_nice_date ][ 'id' ][ $my_date->booking_id ]))
                    $bookings_in_dates[ $curr_nice_date ][ 'id' ][ $my_date->booking_id ] = array();

                $bookings_in_dates[ $curr_nice_date ][ 'id' ][ $my_date->booking_id ][] = $curr_nice_time;

            }

//debuge($bookings_in_dates);
            // check time intersections

            // Set for dates $available_dates -> MAX number of available ITEMS per day inside of loop
            foreach ($dates_approve as $my_date) {

                        // Date KEY ////////////////////////////////////
                        $my_dat = explode(' ',$my_date->booking_date);
                        $my_dt = explode('-',$my_dat[0]);
                        $my_tm = explode(':',$my_dat[1]);
                        $my_key =  $my_dt[0].'-'.$my_dt[1].'-'.$my_dt[2] ;

                        // GET AVAILABLE DAYS ARRAY ////////////////////
                        if ( isset($available_dates[$my_key]) )  {          // Get all booked days in array and add id and last id (its will show)

                            if ( ! in_array($my_date->booking_id, $available_dates[$my_key]['id']) ) {

                                $available_dates[$my_key]['max']--;

                                array_push( $available_dates[$my_key]['id'], $my_date->booking_id);
                                $available_dates[$my_key]['last_id'] = $my_date->booking_id;
                                $available_dates[$my_key]['approved'] += $my_date->approved;

                            } elseif ( 
                                    //($my_date->date_res_type > 0 ) &&                     //Fixed: 2013.07.21 23:26       
                                    ($my_date->type !== $my_date->date_res_type ) ) {
                                $available_dates[$my_key]['max']--;
                            }

                        } else {
                            $my_max_show = $max_available_items - 1;                                
                            $available_dates[$my_key] = array(  'id' => array($my_date->booking_id), 
                                                                'max' => $my_max_show, 
                                                                'last_id' => $my_date->booking_id, 
                                                                'approved' => $my_date->approved);
                        }
             }  // Date loop

//debuge($available_dates);

            // If need just return Array with MAX available ITEMS per day so then return it
            if ( $is_return_available_days_array == 1) {return $available_dates;}

            // Get Unavailable days and return them
            foreach ($dates_approve as $my_date) {
                $my_dat = explode(' ',$my_date->booking_date);
                $my_dt = explode('-',$my_dat[0]);
                $my_key =  $my_dt[0].'-'.$my_dt[1].'-'.$my_dt[2] ;

                // Get Unavailable days, based on MAX availability
                if (    ( $available_dates[$my_key]['max'] <= 0 ) 
                        && ($available_dates[$my_key]['last_id'] == $my_date->booking_id )
                        ) {
                    if ($available_dates[$my_key]['approved'] > 0 ) $available_dates[$my_key]['approved'] = 1;
                    if ($approved == $available_dates[$my_key]['approved'] )
                        array_push($return_dates, $my_date);
                }
            }
//debuge($approved, $return_dates);
            return $return_dates;
        }


        // S Q L    Modify SQL request according Dates - Get rows, from resource of childs and other dates, which partly belong to bk_type
        function get_sql_bk_dates_for_all_resources($mysql, $bk_type, $approved, $skip_booking_id = '' ) {
             global $wpdb;
             $skip_bookings = '';
             $my_page = 'client';
             if (        strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation') !== false ) {
                 $my_page = 'add';
             } else if ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking')!==false) {
                 $my_page = 'booking';
             }


             if  ( ( $my_page == 'add' ) && ( isset($_GET['parent_res'])) ) $my_page = 'client';


            if (  (isset($_GET['booking_hash'])) ||  ($skip_booking_id != '')   ){

                if (($skip_booking_id != '')) { $my_booking_id = $skip_booking_id;
                } else {
                    $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
                    if ($my_booking_id_type !== false)  $my_booking_id = $my_booking_id_type[0];
                }

            } else { $skip_bookings = ''; }

            $my_approve_rule = '';
            if ( ( ($my_page == 'booking') || ( $my_page=='add') )            // For client side this checking DISABLE coloring of dates in CAPACITY DATES
                 || (get_bk_option( 'booking_is_show_pending_days_as_available') == 'On')  
                )
                if ($approved == 'all') $my_approve_rule = '';              // Otherwize, if booking will approved it will not calculate those days, and during availability = 0 , the days will be possible to book and this is WRONG
                else                    $my_approve_rule = 'dt.approved = '.$approved.' AND ';


            $sql_req = "SELECT DISTINCT dt.booking_date, dt.type_id as date_res_type, dt.booking_id, dt.approved, bk.form, bt.parent, bt.prioritet, bt.booking_type_id as type
                        FROM {$wpdb->prefix}bookingdates as dt
                                 INNER JOIN {$wpdb->prefix}booking as bk
                                 ON    bk.booking_id = dt.booking_id
                                         INNER JOIN {$wpdb->prefix}bookingtypes as bt
                                         ON    bk.booking_type = bt.booking_type_id

                     WHERE ".$my_approve_rule." dt.booking_date >= CURDATE() AND
                                              (      bk.booking_type IN ( {$bk_type} ) ";   // All bookings from PARENT TYPE
            if (($skip_bookings == '') && ($my_page =='client') && ($skip_booking_id=='') )
                $sql_req .=                           "OR bt.parent  IN ( {$bk_type} ) ";   // Bookings from CHILD Type
            $sql_req .=                               "OR dt.type_id IN ( {$bk_type} ) ";   // Bk. Dates from OTHER TYPEs, which belong to This TYPE
            $sql_req .=                        ") "
                     .$skip_bookings ;
            if ($skip_booking_id != '')
            $sql_req .=         "   AND dt.booking_id NOT IN ( {$skip_booking_id} ) ";
            $sql_req .=         " ORDER BY dt.booking_date" ;
//debuge($sql_req);
            return $sql_req;

          }

        // Booking Table Admin Page -- Show also bookins, where SOME dates belong to this Type
        // S Q L    Modification for Admin Panel dates:  (situation, when some bookings dates exist at several resources ) - Booking Tables
        function get_sql_4_dates_from_other_types($blank_sql  , $bk_type, $approved ){
            global $wpdb;

            $sql = " OR  bk.booking_id IN ( SELECT DISTINCT booking_id FROM {$wpdb->prefix}bookingdates as dtt WHERE  dtt.approved IN ( {$approved} ) AND dtt.type_id = {$bk_type} ) ";

            return $sql;
        }


   // Cancel Pending bookings for the specific dates of bookings list, of the same booking resource     
   function cancel_pending_same_resource_bookings_for_specific_dates($blank, $approved_id_str){

        $is_show_pending_days_as_available                          = get_bk_option( 'booking_is_show_pending_days_as_available');
        $booking_auto_cancel_pending_bookings_for_approved_date     = get_bk_option( 'booking_auto_cancel_pending_bookings_for_approved_date');

       // If the show pending as available AND auto cancellation is not activated so  then SKIP
       if ( ($is_show_pending_days_as_available != 'On') || 
            ($booking_auto_cancel_pending_bookings_for_approved_date !='On')
          ) return $blank;

       global $wpdb;

       $approved_id_str_array = explode(',',$approved_id_str);

       $my_bk_array = array();

       // Because we can have the several ID from the different Booking resources,
       // So thats why we are need to work seperately with  each booking, because we
       // are need to cancel  only the bookings from the same Booking Resource
       foreach ($approved_id_str_array as $approved_id_str) {

           $approved_id_str =(int) $approved_id_str;
            // Select the Dates and Booking Resources of the Bookings, what  was APPROVED
            $mysql = "SELECT DISTINCT (dt.booking_date) AS date, bk.booking_type
                      FROM {$wpdb->prefix}bookingdates as dt
                         INNER JOIN {$wpdb->prefix}booking as bk
                         ON    bk.booking_id = dt.booking_id                                     
                       WHERE dt.booking_id = {$approved_id_str} 
                      ORDER BY date ASC";

            $my_dates = $wpdb->get_results( $mysql );

            if (count($my_dates)==0) break;
//debuge($my_dates);                
            // Get Start and Last dates - its because we was order by dates
            $wh_booking_date = $my_dates[0]->date;
            $wh_booking_date2= $my_dates[ (count($my_dates)-1) ]->date;


            //Ceck  times - If we have the FULL date booking, so then set start and the end times in correct way as FULL
            $check_start_time = substr($wh_booking_date, 11);
            if ( $check_start_time == '00:00:01')  {
                $wh_booking_date = substr($wh_booking_date, 0, 11 ) . '00:00:00';
            }                
            $check_end_time = substr($wh_booking_date2, 11);
            if ( ( $check_end_time == '00:00:00') || ( $check_end_time == '00:00:02') ) {
                $wh_booking_date2 = substr($wh_booking_date2, 0, 11 ) . '23:59:59';
            }
//debuge($wh_booking_date2);                

            // Booking resource
            $wh_booking_type = $my_dates[0]->booking_type;
                        /*
                        // Get DISTINCT booking resource
                        $wh_booking_type = array();
                        foreach ($my_dates as $value) {
                            if (! in_array($value->booking_type, $wh_booking_type)) {
                                $wh_booking_type[]=$value->booking_type;
                            }
                        }        
                        $wh_booking_type = implode(',',$wh_booking_type);/**/

            // Pending
            $wh_approved = '0';

//debuge($wh_booking_type, $wh_booking_date, $wh_booking_date2);


             // Get Pending bookings ID of the same Booking Resource
             $sql_start_select = " SELECT bk.booking_id as id " ;        
             $sql = " FROM {$wpdb->prefix}booking as bk" ;
             $sql_where = " WHERE " .                                                      // Date (single) connection (Its required for the correct Pages in SQL: LIMIT Keyword)
                    "       EXISTS (
                                     SELECT *
                                     FROM {$wpdb->prefix}bookingdates as dt
                                     WHERE  bk.booking_id = dt.booking_id " ;                
                         $sql_where.=        " AND dt.approved = ".$wh_approved." " ;            // Pending
                         $sql_where.=        " AND ( dt.booking_date >= '" . $wh_booking_date . "' ) ";
                         $sql_where.=        " AND ( dt.booking_date <= '" . $wh_booking_date2 . "' ) ";
                         $sql_where.=   " AND (  " ;
                         $sql_where.=   "       ( bk.booking_type IN  ( ". $wh_booking_type ." ) ) " ;     // BK Resource conections
                         $sql_where .= apply_bk_filter('get_l_bklist_sql_resources', ''  , $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 );
                         $sql_where.=   "     )  " ;
             $sql_where.=   "     )  " ;
//debuge($sql_where);
             $my_bk = $wpdb->get_results( $sql_start_select . $sql . $sql_where );
//debuge($my_bk);            
             foreach ($my_bk as $value) {
                if (! in_array($value->id, $my_bk_array)) {
                    $my_bk_array[]=$value->id;
                }
             }
       }

       if (isset($_POST['user_id'])) {
            $user_bk_id = $_POST['user_id'];               
       } else {                       
            $user = wp_get_current_user(); 
            $user_bk_id = $user->ID;
       }

       $all_bk_id = implode('|',$my_bk_array);
//debuge($all_bk_id);           
       $bk_url_listing     = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking' ;
       $bk_url_listing    .= '&wh_booking_id='.str_replace('|',',',$all_bk_id).'&view_mode=vm_listing&tab=actions';
       if (count($my_bk_array)>0) {
            // Delete all other Pending bookings
            ?>  <script type="text/javascript">  
                                                                                // Show the bookings, which  we are need to Decline  
                  //window.location.href='<?php echo $bk_url_listing; ?>';
                                                                                // Delete the pending bookings for the same dates
                  delete_booking('<?php echo $all_bk_id; ?>' , <?php echo $user_bk_id; ?>, '<?php echo getBookingLocale(); ?>' , 1);
                  document.getElementById('ajax_message').innerHTML = '<?php echo printf(__('The folowing pending booking(s): %s deleted.' ,'booking'), str_replace('|', ',', $all_bk_id) );  ?>';
                  jQuery('#ajax_message').animate({opacity:1},5000).fadeOut(2000);                     
            </script> <?php
       }
       return $all_bk_id;
   }     
// </editor-fold>




// <editor-fold defaultstate="collapsed" desc=" A d m i n   B O O K I N G   P a g e ">

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// A d m i n   B O O K I N G   P a g e     ////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // at Admin panel, show RESOURCE links nearly Dates    // $bk['type_id'][$ad], $bk['booking_type'], $bk_type
        function show_diferent_bk_resource_of_this_date( $bk_type_id_of_date, $current_booking_type, $bk_type_original , $type_items, $outColorClass ){
            //debuge($bk_type_id_of_date, $current_booking_type, $bk_type_original , $type_items, $outColorClass);//die;
            if (
                 (! empty($bk_type_id_of_date)) ||
                 ( (  $current_booking_type !== $bk_type_original ) && ($bk_type_original>0) )
               ) {

                if  ( ! (  (! empty($bk_type_id_of_date)) &&  ($bk_type_id_of_date == $bk_type_original) ) )

                    if (! empty($bk_type_id_of_date))
                        echo '<a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking'.'&booking_type='.$bk_type_id_of_date.'"
                            class="bktypetitle0  bk_res_link_from_dates booking_overmause'. $outColorClass.'"  >' . $type_items[ $bk_type_id_of_date ]  . '</a>';
                    else
                        echo '<a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking'.'&booking_type='.$current_booking_type.'"
                            class="bktypetitle0  bk_res_link_from_dates booking_overmause'. $outColorClass.'"  >' . $type_items[ $current_booking_type ]  . '</a>';

               }
        }


        // Show types with max counts of items for this types
        function showing_capacity_of_bk_res_in_top_line($title, $bk_type, $count){
            return ' <span class="bktypecount"><a  href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&tab=resources" title="'.__('Maximum available booking resource items' ,'booking').'" class="" >' . $count . '</a></span>'
                     . '<style type="text/css"> #parents_bk_resources #bktype'.$bk_type->id.' {padding:3px 0px 3px 4px;}</style>' ;
        }


        //Show booking page for PARENT booking resource
        function show_all_bookings_for_parent_resource($parent_id){

            // Get all exist booking resources into array in format: stdClass Object(   [id] => 13, [title] => test, [parent] => 0, [prioritet] => 0, [cost] => 115, [visitors] => 1, [users] => 1 )
            $bk_resources = $this->get_booking_types();

//debuge('$bk_resources, $parent_id',$parent_id);

            $resource_list_sort_by_priority = array();

            foreach ($bk_resources as $value) {
                if (  ($value->id != $parent_id) && ($value->parent != $parent_id) )  continue;         // Skip this resource
                                                                            // Sort booking resources by priority
                if (($value->id == $parent_id)) {
                    if (! isset($resource_list_sort_by_priority[ 0 ]))
                         $resource_list_sort_by_priority[ 0 ]   = array($value);
                    else $resource_list_sort_by_priority[ 0 ]   = array_merge( array($value) , $resource_list_sort_by_priority[ 0 ] ) ;
                } else {
                    if (! isset($resource_list_sort_by_priority[ $value->prioritet ]))
                         $resource_list_sort_by_priority[ $value->prioritet ]   = array($value);
                    else $resource_list_sort_by_priority[ $value->prioritet ][] = $value;
                }
            }
            $resource_list_sorted_by_priority = array();                    // Get final sorted by priority array of resources
            foreach ($resource_list_sort_by_priority as $value) {
                foreach ($value as $v) {
                    $resource_list_sorted_by_priority[]=$v;
                }
            }

//debuge($resource_list_sorted_by_priority);

            ?>
          <table style="width:100%;margin:10px 0px;border:0px solid #ccc;" cellpadding="0" cellspacing="0">
              <tr>
                  <td style="width:100px;"></td>
                  <td>
                      <table style="width:100%;margin:10px 0px 0px;" cellpadding="0" cellspacing="0">
                          <tr>
                              <th colspan="1" style="font-size:24px;font-weight: normal;text-shadow:0px 1px 2px #bbb;padding:10px;height:30px;">
                                  &lt;&lt;
                              </th>
                              <th colspan="29" style="font-size:26px;font-weight: normal;text-shadow:0px 1px 2px #bbb;padding:10px;">
                                  September, 2011
                              </th>
                              <th colspan="1"style="font-size:24px;font-weight: normal;text-shadow:0px 1px 2px #bbb;padding:10px;">
                                  &gt;&gt;
                              </th>
                          </tr>
                          <tr>
                              <?php for ($i = 1; $i < 32; $i++) { ?>
                              <td style="background: #eee;border:1px solid #ccc; text-align: center; padding:2px;width:3.22%;    font-size:22px;font-weight: bold;text-shadow:0px 1px 2px #bbb;padding:5px 5px 7px;" >
                                  <span style="font-size:12px;"><?php echo 'Su'; ?></span>
                                  <br/> <?php echo $i; ?>


                              </td>
                              <?php } ?>
                          </tr>
                      </table>
                  </td>
              </tr>
              <?php foreach ($resource_list_sorted_by_priority as $value) { ?>
              <tr>
                  <th><?php echo $value->title; ?></th>

                  <td>

                      <table style="width:100%;margin:0px;" cellpadding="0" cellspacing="0">
                          <tr>
                              <?php for ($i = 1; $i < 32; $i++) { ?>
                              <td style="border:1px solid #ccc; text-align: center; padding:2px;width:3.22%;padding:5px 5px 7px;" >
                                  <?php if ( rand(0,1) ) echo ' '; else echo 'X'; ?>
                              </td>
                              <?php } ?>
                          </tr>
                      </table>

                  </td>
              </tr>
              <?php } ?>
          </table>
            <?php
        }


        function check_if_bk_res_have_childs($bk_type_id) {
            if ($bk_type_id<1) return false;
            global $wpdb;
            $mysql=  $wpdb->prepare( "SELECT booking_type_id as id, prioritet  FROM {$wpdb->prefix}bookingtypes WHERE ( parent= %d )  ORDER BY prioritet", $bk_type_id );
            $types_list = $wpdb->get_results( $mysql );
            if (count($types_list)>0)  return count($types_list);
            else return false;
        }
        // check if this resource Parent and have some childs, so then assign to $_GET['parent_res'] = 1
        function check_if_bk_res_parent_with_childs_set_parent_res($bk_type_id) {

            if ( $this->check_if_bk_res_have_childs($bk_type_id) ) {
                $_GET['parent_res'] = 1;
            }
        }
 // </editor-fold>


// <editor-fold defaultstate="collapsed" desc=" A d m i n   S E T T I N G S    M E N U ">

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// A d m i n   S E T T I N G S    M E N U     ////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

      function settings_menu_content() {
$is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'not_low_level_user'); //Anxo customizarion
if (! $is_can) return; //Anxo customizarion

          switch ($_GET['tab']) {

              case 'resources': return;
                  ?> <div id="ajax_working" class="clear" style="height:0px;"></div>
                           <div id="poststuff0" class="metabox-holder"> <?php
                  $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                  if ($is_can) $this->show_resources_advanced_settings(true);         // Make only UPDATE

                  $this->show_resources_settings_page();

                  if ($is_can) $this->show_resources_advanced_settings();

                  ?> </div> <?php
                  return false;
              case 'search':
                  ?> <div id="ajax_working" class="clear" style="height:0px;"></div>
                     <div id="poststuff0" class="metabox-holder"> <?php
                  $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                  if ($is_can) {
                      ?><form  name="post_search_option" action="" method="post" id="post_search_option" ><?php
                            $this->show_search_settings();                            
                      ?>
                            <input class="button-primary button" style="float:left;margin:0px;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="Submit"/>
                            <div class="clear" style="height:5px;"></div>                      
                      </form><?php
                  }
                  ?> </div> <?php
                  return false;
              default:
                  return true;
                  break;
          }

      }

              // Show Settings page for resources
              function show_resources_settings_page() {

                  global $wpdb;
                  $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');

                  if ( ( isset( $_POST['submit_resources'] ) )  ||  (isset($_POST['submit_resources_button']))  ) {

                      if ($_POST['type_title_new'] != '') { // Insert

                          $cost = $this->get_booking_types( $_POST['type_parent_new'] ) ;
                          if (count($cost)>0) $cost = $cost[0]->cost;
                          else $cost = '0';

                           $useres_title = '';
                           $users_values = '';
                           if ( class_exists('wpdev_bk_multiuser')) {  // If Business Large then get resources from that
                                $useres_title = ', users';
                                $user = wp_get_current_user();
                                $u_id = $user->ID;
                                $users_values = ', ' . $u_id;
                           }

                          $wpbc_sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}bookingtypes 
                                                       ( title, parent, cost, prioritet {$useres_title} ) 
                                                        VALUES (%s, %d, %s, %d  {$users_values} )"
                                                        , $_POST['type_title_new']
                                                        , $_POST['type_parent_new']
                                                        , $cost
                                                        , $_POST['type_prioritet_new']
                                                    );

                          if ( false === $wpdb->query( $wpbc_sql ) ){
                              bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
                          } else {
                              if (isset($_POST['type_max_visitors' . $_POST['type_parent_new'] ])) {
                                  $booking_id = (int) $wpdb->insert_id;       //Get ID
                                  $booking_visitor_num = $_POST['type_max_visitors' . $_POST['type_parent_new'] ] ;
                                  if ( false === $wpdb->query($wpdb->prepare(
                                          "UPDATE {$wpdb->prefix}bookingtypes SET visitors = %s WHERE booking_type_id = %d ", $booking_visitor_num,  $booking_id) ) ){
                                      bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
                                  }
                              }
                          }


                      } else {

                          $bk_types = $this->get_booking_types();
                          $is_deleted = false;

                          foreach ($bk_types as $bt) { // Delete
                              if (isset($_POST['type_delete'.$bt->id])) {
                                  $is_deleted = true;
                                  $delete_sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d ", $bt->id );
                                  if ( false === $wpdb->query( $delete_sql ) ){
                                      bk_error('Error during deleting booking resources',__FILE__,__LINE__ );
                                  }
                              }
                          }

                          if ($is_deleted == false)
                              foreach ($bk_types as $bt) { // Update

                                  if ($is_use_visitors_number_for_availability == 'On') {

                                      if ( $_POST['type_parent'.$bt->id] != 0 )     // Set for Child objects, value of Parent objects
                                          $vis_update_string = $wpdb->prepare(" , visitors = %s ", $_POST['type_max_visitors'. $_POST['type_parent'.$bt->id] ] ) ;
                                      else                                          // Set for Parent objects - normal value
                                          $vis_update_string = $wpdb->prepare(" , visitors = %s ", $_POST['type_max_visitors'.$bt->id] ) ;

                                  } else  $vis_update_string = '';

                                  $wpbc_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s, parent = %s , prioritet = %s {$vis_update_string} WHERE booking_type_id = %d " 
                                          , $_POST['type_title'.$bt->id] 
                                          , $_POST['type_parent'.$bt->id]
                                          , $_POST['type_prioritet'.$bt->id]
                                          , $bt->id  );
                                  if ( false === $wpdb->query( $wpbc_sql ) ){
                                      bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
                                  }

                              }
                      }

                  }

                  ?>
                                    <div class='meta-box'>
                                      <div <?php $my_close_open_win_id = 'bk_settings_resource_management'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                            <h3 class='hndle'><span><?php _e('Booking resources management' ,'booking'); ?></span></h3> <div class="inside">

                                        <form  name="post_option_resources" action="" method="post" id="post_option_resources" >
                                            <table style="width:100%;" class="resource_table0 booking_table" cellpadding="0" cellspacing="0">
                                                <tr>
                                                    <th style="width:10px;height:35px;"> <?php _e('ID' ,'booking'); ?> </th>

                                                    <th style="width:220px;height:35px;"> <?php _e('Resource name' ,'booking'); ?> </th>

                                                    <th style="width:50px; " rel="tooltip" class="tooltip_bottom"  title="<?php _e('Number of resource items inside of parent resource' ,'booking');?>"> <?php _e('Capacity' ,'booking'); ?>  </th>
                                                    <th style="width:100px;text-align: center; "> <?php _e('Parent' ,'booking');   ?>  </th>
                                                    <th style="width:50px; "> <?php _e('Priority' ,'booking'); ?> </th>
                                                    <?php if ($is_use_visitors_number_for_availability == 'On') { ?>
                                                    <th style="width:50px;white-space: nowrap; " rel="tooltip" class="tooltip_bottom"  title="<?php _e('Maximum number of visitors for resource' ,'booking');?>"> <?php _e('Max' ,'booking'); echo ' '; _e('visitors' ,'booking'); ?> </th>
                                                    <?php } ?>                                                                                                                
                                                    <th style="text-align: center;"> <?php _e('Actions' ,'booking'); ?> </th>
                                                    <?php make_bk_action('show_users_header_at_settings' ); ?>
                                                </tr>



                  <?php
                  $alternative_color = '0';
                  $bk_types =  $this->get_booking_types();
                  $all_id = array(array('id'=>0,'title'=>' - '));
                  foreach ($bk_types as $bt) {
                      if ($bt->parent==0)
                          $all_id[] = array('id'=>$bt->id, 'title'=> $bt->title);
                  }
                  $bk_types =  $this->get_booking_types_hierarhy($bk_types);
                  $bk_types =  $this->get_booking_types_hierarhy_linear($bk_types);

                  $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&tab=resources';

                  foreach ($bk_types as $bt) {

                      $my_count = $bt['count'];
                      $bt = $bt['obj'];
                      if ( $alternative_color == '')  $alternative_color = ' class="alternative_color" ';
                      else $alternative_color = '';
                      ?>
                                                    <tr>
                                                        <th style="font-size:11px;border-top: 1px solid #aaa;text-align: center;" <?php echo $alternative_color; ?> ><?php echo $bt->id; ?></th>
                                                        <td style="font-size:11px;<?php if ($bt->parent != 0 ) { 
                          echo 'padding-left:50px;';
                      } ?>" <?php echo $alternative_color; ?> >
                                                            <input  style="<?php if ($bt->parent == 0 ) { 
                          echo 'width:210px;font-weight:bold;';
                      } else {
                          echo 'width:170px;font-size:11px;';
                      } ?>" maxlength="17" type="text" value="<?php echo $bt->title; ?>" name="type_title<?php echo $bt->id; ?>" id="type_title<?php echo $bt->id; ?>">
                                                        </td>

                                                        <td style="text-align:center;font-weight: bold;" <?php echo $alternative_color; ?> ><?php if ($bt->parent == 0 ) { 
                          echo $my_count;
                      }?></td>
                                                        <td style="text-align:center;" <?php echo $alternative_color; ?> >
                                                            <select  style="width:90px;"  name="type_parent<?php echo $bt->id; ?>" id="type_parent<?php echo $bt->id; ?>">
                      <?php foreach ($all_id as $m_id) { ?>
                                                                <option <?php if ( $bt->parent==$m_id['id']) echo 'selected="SELECTED"' ?> value="<?php echo $m_id['id']; ?>"><?php echo $m_id['title'] ?></option>
                          <?php } ?>
                                                            </select>
                                                            <!--input  style="width:40px;" maxlength="17" type="text" value="<?php echo $bt->parent; ?>" name="type_parent<?php echo $bt->id; ?>" id="type_parent<?php echo $bt->id; ?>" -->
                                                        </td>
                                                        <td style="text-align:center;" <?php echo $alternative_color; ?> >
                                                            <select  style="width:50px;"   name="type_prioritet<?php echo $bt->id; ?>" id="type_prioritet<?php echo $bt->id; ?>">
                      <?php for ($m_id = 0; $m_id < 50; $m_id++) { ?>
                                                                <option <?php if ( $bt->prioritet==$m_id) echo 'selected="SELECTED"' ?> value="<?php echo $m_id; ?>"><?php echo $m_id ?></option>
                          <?php } ?>
                                                            </select>
                                                            <!--input  style="width:40px;" maxlength="17" type="text" value="<?php echo $bt->prioritet; ?>" name="type_prioritet<?php echo $bt->id; ?>" id="type_prioritet<?php echo $bt->id; ?>" -->
                                                        </td>
                                                        <?php if ($is_use_visitors_number_for_availability == 'On') { ?>
                                                        <td style="text-align:center;" <?php echo $alternative_color; ?> >
                                                            <?php if ($bt->parent == 0 ) { ?>
                                                            <select <?php if ($bt->parent != 0 ) { echo ' disabled="DISABLED" '; } ?>  style="width:50px;"   name="type_max_visitors<?php echo $bt->id; ?>" id="type_max_visitors<?php echo $bt->id; ?>">
                                                                <?php for ($m_id = 1; $m_id < 21; $m_id++) { ?>
                                                                    <option <?php if ( $bt->visitors==$m_id) echo 'selected="SELECTED"' ?> value="<?php echo $m_id; ?>"><?php echo $m_id ?></option>
                                                                <?php } ?>
                                                            </select>
                                                            <?php } else { ?>
                                                            <span style="font-size:10px;font-weight:bold;"><?php echo $bt->visitors; ?></span>
                                                            <?php } ?>
                                                        </td>
                                                        <?php } ?>
                                                        <td style="font-size:11px;text-align: center;" <?php echo $alternative_color; ?>
                      <?php

                    $max_num = apply_bk_filter('get_max_res_num_for_user_in_multiuser', false );
                    if ( ($max_num === false) || ($max_num > count($bk_types) ) )

                      if ($bt->parent==0) { ?>
                                                            <div style="height:20px;">
                                                            <input class="button" style="margin:0px 10px;" type="button" value="+ <?php _e('Add' ,'booking'); ?>"
                                                               onclick="javascript:
                                                                       document.getElementById('type_title_new').value='<?php echo $bt->title . '-' . ($my_count+1) ; ?>';
                                                                   document.getElementById('type_parent_new').value='<?php echo $bt->id  ; ?>';
                                                                   document.getElementById('type_prioritet_new').value='<?php echo  ($my_count+1)   ; ?>';
                                                                   document.getElementById('submit_resources_button').click();
                                                                       " /> </div>
                          <?php } ?>

                                                            <span style="line-height:25px;"><?php _e('Delete' ,'booking'); ?>: </span><input class="checkbox"  type="checkbox"   name="type_delete<?php echo $bt->id; ?>" id="type_delete<?php echo $bt->id; ?>"/>
                                                        </td>
                                                        <?php make_bk_action('show_users_collumn_at_settings', $bt , $alternative_color ); ?>
                                                   </tr>
                      <?php } 

                      if ( ($max_num === false) || ($max_num > count($bk_types) ) ) {
                      ?>
                                                <tr>
                                                    <td colspan="<?php if ($is_use_visitors_number_for_availability == 'On')  echo '7'; else echo '6'; ?>" style=" height:35px;padding:0px 35px;border-top:1px solid #999;">
                                                        <div style="float:left;line-height: 25px;font-weight:bold;margin:0px 5px;" ><?php _e('Title' ,'booking'); ?>:&nbsp;</div>
                                                        <input  style="float:left;width:125px;" maxlength="17" type="text" value="" name="type_title_new" id="type_title_new">
                                                        <div style="float:left;line-height: 25px;font-weight:normal;margin:0px 0px 0px 7px;" ><?php _e('Parent' ,'booking'); ?>:&nbsp;</div>
                                                        <select  style="float:left;width:90px;"  name="type_parent_new" id="type_parent_new">
                  <?php foreach ($all_id as $m_id) { ?>
                                                                <option  value="<?php echo $m_id['id']; ?>"><?php echo $m_id['title'] ?></option>
                      <?php } ?>
                                                        </select>
                                                        <!--input  style="float:left;width:30px;" maxlength="17" type="text" value="0" name="type_parent_new" id="type_parent_new"-->
                                                        <div style="float:left;line-height: 25px;font-weight:normal;margin:0px 0px 0px 7px;" ><?php _e('Priority' ,'booking'); ?>:&nbsp;</div>
                                                        <select  style="float:left;width:50px;"   name="type_prioritet_new" id="type_prioritet_new">
                                                            <?php for ($m_id = 0; $m_id < 50; $m_id++) { ?>
                                                                <option value="<?php echo $m_id; ?>"><?php echo $m_id ?></option>
                                                            <?php } ?>
                                                        </select>
                                                        <!--input  style="float:left;width:30px;" maxlength="17" type="text" value="0" name="type_prioritet_new" id="type_prioritet_new"-->

                                                        <input class="button" style="float:left;margin:0px 20px;" type="submit" value="+ <?php _e('Add new resource' ,'booking'); ?>" name="submit_resources_button" id="submit_resources_button"/>
                                                    </td>
                                                    <?php make_bk_action('show_users_collumn_at_settings', 'blank' ); ?>
                                                </tr>
                       <?php } ?>
                                            </table>
                                            <div class="clear" style="height:10px;"></div>
                                            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save' ,'booking'); ?>" name="submit_resources"/>
                                            <div class="clear" style="height:10px;"></div>

                                        </form>

                                   </div> </div> </div>
                  <?php
              }

              // Show Advanced settings at the bootom of Resource Settings page
              function show_resources_advanced_settings( $is_only_post = false ) {

                  if ($is_only_post) {
                      if(isset($_POST['submit_advanced_resources_settings'])) {
                          if (isset( $_POST['booking_is_use_visitors_number_for_availability'] ))     $is_use_visitors_number_for_availability = 'On';
                          else                                                                        $is_use_visitors_number_for_availability = 'Off';
                          update_bk_option( 'booking_is_use_visitors_number_for_availability' ,  $is_use_visitors_number_for_availability );

                          if (isset( $_POST['booking_is_show_availability_in_tooltips'] ))     $booking_is_show_availability_in_tooltips = 'On';
                          else                                                         $booking_is_show_availability_in_tooltips = 'Off';
                          update_bk_option( 'booking_is_show_availability_in_tooltips' ,  $booking_is_show_availability_in_tooltips );

                          if (isset( $_POST['booking_is_dissbale_booking_for_different_sub_resources'] ))
                               update_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources', 'On' );
                          else update_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources', 'Off' );

                          update_bk_option( 'booking_highlight_availability_word' ,  $_POST['booking_highlight_availability_word'] );
                          //update_bk_option( 'booking_maximum_selection_days_for_one_resource' ,  $_POST['maximum_selection_days_for_one_resource'] );
                          update_bk_option( 'booking_availability_based_on' ,  $_POST['availability_based_on'] );
                      }
                      return ;
                  }

                  $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');
                  $booking_is_show_availability_in_tooltips   = get_bk_option( 'booking_is_show_availability_in_tooltips');
                  $booking_highlight_availability_word        = get_bk_option( 'booking_highlight_availability_word');
                  //$maximum_selection_days_for_one_resource    = get_bk_option( 'booking_maximum_selection_days_for_one_resource');
                  $availability_based_on_visitors   = get_bk_option( 'booking_availability_based_on');
                  $booking_is_dissbale_booking_for_different_sub_resources = get_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources');
                  ?>
                                    <div class='meta-box'>
                                      <div <?php $my_close_open_win_id = 'bk_settings_resources_advanced_options'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                            <h3 class='hndle'><span><?php _e('Advanced Settings' ,'booking'); ?></span></h3> <div class="inside">
                                            <form  name="post_option_resources_adv" action="" method="post" id="post_option_resources_adv" >
                                                <table class="form-table"><tbody>
                                                    <?php /* ?>
                                                    <tr valign="top">
                                                        <th scope="row"><label for="admin_cal_count" ><?php _e('Max days for booking inside of one resource' ,'booking'); ?>:</label></th>
                                                        <td><input id="maximum_selection_days_for_one_resource"  name="maximum_selection_days_for_one_resource" class="regular-text code" type="text" size="145" value="<?php echo $maximum_selection_days_for_one_resource; ?>" />
                                                            <div class="description"><?php printf(__('Type the %smaximum number days%s of selection,  which garanteed will be storeed inside of one booking sub resource' ,'booking'),'<b>','</b>');?></div>
                                                        </td>
                                                    </tr>
                                                    <?php /**/ ?>


                                                   <tr valign="top" class="ver_premium_hotel">
                                                        <th scope="row">
                                                            <label for="is_use_visitors_number_for_availability" ><?php _e('Set capacity based on number of visitors' ,'booking'); ?>:</label>
                                                        </th>
                                                        <td>
                                                            <input <?php if ($is_use_visitors_number_for_availability == 'On') echo "checked"; ?>  value="<?php echo $is_use_visitors_number_for_availability; ?>" name="booking_is_use_visitors_number_for_availability" id="booking_is_use_visitors_number_for_availability" type="checkbox"
                                                                    onclick="javascript: if (this.checked) jQuery('#togle_settings_availability_based_on_visitors').slideDown('normal'); else  jQuery('#togle_settings_availability_based_on_visitors').slideUp('normal');"
                                                             />
                                                            <div class="description"> <?php printf(__('Check this box if you want total availability (daily capacity) to depend on the number of selected visitors %s.' ,'booking'), '<code>[select visitors "1" "2" 3" "4"]</code>');
                                                            echo ' '; printf(__('Please read more info about configuration of this parameter %shere%s' ,'booking'),'<a href="http://wpbookingcalendar.com/help/booking-resource/" target="_blank">','</a>');
                                                            ?></div>
                                                        </td>
                                                    </tr>


                                                    <tr valign="top" class="ver_premium_hotel"><td colspan="2">
                                                        <table id="togle_settings_availability_based_on_visitors" style="width:100%;<?php if ($is_use_visitors_number_for_availability != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                                            <tr>
                                                            <td scope="row">
                                                                <div style="width:100%;">

                                                                    <div style="margin:10px 25px 10px 0px; font-weight: normal;"><label for="availability_based_on_items" ><?php
                                                                    printf(__(
                                                                              "Add tooltip on calendar(s) to show availability based on the number of available booking resource items remaining for each day. %s".
                                                                              "Be sure to match the maximum number of visitors for the %sone booking resource%s with the number of visitors specified on the booking form."
                                                                     ,'booking'),'<br />','<strong>','</strong>'); ?>: </label>
                                                                        <input style="margin:-25px 50px 0px;"  <?php if ($availability_based_on_visitors == 'items') echo 'checked="checked"';/**/ ?> value="items" type="radio" id="availability_based_on_items"  name="availability_based_on"    />
                                                                    </div>
                                                                    <div style="border-bottom: 1px solid #ccc;"></div>
                                                                    <div style="margin:10px 25px 10px 0px; font-weight: normal;"><label for="availability_based_on_visitors" ><?php
                                                                    printf(__(
                                                                     "Display tooltip on calendar(s) to show availability based on total (fixed) number of visitors for the resource, which can be at free booking resource items. %s" .
                                                                     "Be sure to match the maximum number of visitors for %sall booking resources%s with the number of visitors specified on the booking form."
                                                                     ,'booking'),'<br />','<strong>', '</strong>'); ?>: </label>
                                                                        <input style="margin:-25px 50px 0px;"    <?php if ($availability_based_on_visitors == 'visitors') echo 'checked="checked"';/**/ ?> value="visitors" type="radio" id="availability_based_on_visitors"  name="availability_based_on"    />
                                                                    </div>

                                                                </div>
                                                            </td>
                                                            </tr>
                                                        </table>
                                                    </td></tr>


                                                   <tr valign="top" class="ver_premium_hotel">
                                                        <th scope="row">
                                                            <?php _e('Show availability' ,'booking'); ?>:
                                                        </th>
                                                        <td>
                                                            <label for="booking_is_show_availability_in_tooltips" >
                                                                <input <?php if ($booking_is_show_availability_in_tooltips == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_show_availability_in_tooltips; ?>" name="booking_is_show_availability_in_tooltips" id="booking_is_show_availability_in_tooltips" type="checkbox"
                                                                        onclick="javascript: if (this.checked) jQuery('#togle_settings_availability_day_show').slideDown('normal'); else  jQuery('#togle_settings_availability_day_show').slideUp('normal');"
                                                                                                                                              />
                                                                <?php _e('Check this box to display the available number of booking resources with a tooltip, when mouse hovers over each day on the calendar(s).' ,'booking');?>
                                                            </label>
                                                        </td>
                                                    </tr>

                                                    <tr valign="top" class="ver_premium_hotel"><td colspan="2">
                                                        <table id="togle_settings_availability_day_show" style="<?php if ($booking_is_show_availability_in_tooltips != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                                            <tr>
                                                            <th scope="row"><label for="booking_highlight_availability_word" ><?php _e('Availability description' ,'booking'); ?>:</label></th>
                                                                <td><input value="<?php echo $booking_highlight_availability_word; ?>" name="booking_highlight_availability_word" id="booking_highlight_availability_word"  type="text"    />
                                                                    <div class="description"><?php printf(__('Type your %savailability%s description' ,'booking'),'<b>','</b>');?></div>                                                                        
                                                                </td>
                                                            </tr>
                                                            <tr><td colspan="2" style="padding:0px;"><div style="margin-top:-15px;"><?php make_bk_action('show_additional_translation_shortcode_help'); ?></div></td></tr>
                                                        </table>
                                                    </td></tr>


                                                   <tr valign="top" class="ver_premium_hotel">
                                                        <th scope="row">
                                                            <label for="booking_is_dissbale_booking_for_different_sub_resources" ><?php _e('Disable bookings in different booking resources' ,'booking'); ?>:</label>
                                                        </th>
                                                        <td>
                                                            <input <?php if ($booking_is_dissbale_booking_for_different_sub_resources == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_dissbale_booking_for_different_sub_resources; ?>" name="booking_is_dissbale_booking_for_different_sub_resources" id="booking_is_dissbale_booking_for_different_sub_resources" type="checkbox" />
                                                            <div class="description"> <?php _e('Check this box to dissable reservations, which can be stored in different booking resources. When checked, all reserved days must be at same booking resource otherwise error message will show.' ,'booking');?></div>
                                                        </td>
                                                    </tr>


                                                </tbody></table>
                                            <div class="clear" style="height:10px;"></div>
                                            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save' ,'booking'); ?>" name="submit_advanced_resources_settings"/>
                                            <div class="clear" style="height:10px;"></div>

                                        </form>

                                   </div> </div> </div>

                  <?php
              }


      // Show Advanced settings at the bootom of Resource Settings page
      function show_advanced_settings_in_general_settings_menu(  ) {

              if(isset($_POST['availability_based_on'])) {
                  if (isset( $_POST['booking_is_use_visitors_number_for_availability'] ))     $is_use_visitors_number_for_availability = 'On';
                  else                                                                        $is_use_visitors_number_for_availability = 'Off';
                  update_bk_option( 'booking_is_use_visitors_number_for_availability' ,  $is_use_visitors_number_for_availability );


                  if (isset( $_POST['booking_is_dissbale_booking_for_different_sub_resources'] ))
                       update_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources', 'On' );
                  else update_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources', 'Off' );


                  update_bk_option( 'booking_availability_based_on' ,  $_POST['availability_based_on'] );
              }

          $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');

          $availability_based_on_visitors   = get_bk_option( 'booking_availability_based_on');
          $booking_is_dissbale_booking_for_different_sub_resources = get_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources');
          ?>
              <?php /** ?><div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_settings_resources_advanced_options'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                      <h3 class='hndle'><span><?php _e('Advanced' ,'booking'); ?></span></h3> <div class="inside">

                          <table class="form-table"><tbody><?php /**/ ?>

                             <?php $this->settings_show_pending_days_as_available(); ?>     

                             <tr valign="top" class="ver_premium_hotel">
                                  <th scope="row"><?php _e('Set capacity based on number of visitors' ,'booking'); ?></th>
                                  <td>
                                      <fieldset>
                                        <label for="booking_is_use_visitors_number_for_availability" >
                                            <input <?php if ($is_use_visitors_number_for_availability == 'On') echo "checked"; ?>  value="<?php echo $is_use_visitors_number_for_availability; ?>" name="booking_is_use_visitors_number_for_availability" id="booking_is_use_visitors_number_for_availability" type="checkbox"
                                                onclick="javascript: if (this.checked) jQuery('#togle_settings_availability_based_on_visitors').slideDown('normal'); else  jQuery('#togle_settings_availability_based_on_visitors').slideUp('normal');"
                                            />
                                            <?php printf(__('Check this box if you want total availability (daily capacity) to depend on the number of selected visitors.' ,'booking'),  '<code>[select visitors "1" "2" 3" "4"]</code>'); ?>
                                        </label>
                                      </fieldset>
                                      <p class="description"><strong><?php _e('Important!' ,'booking');?></strong> <?php printf(__('Please read more info about configuration of this parameter %shere%s' ,'booking'),'<a href="http://wpbookingcalendar.com/help/booking-resource/" target="_blank">','</a>'); ?></p>
                                  </td>
                              </tr>

                              <tr valign="top" class="ver_premium_hotel"><td style="padding:0px;" colspan="2">
                                <div style="margin: 0px 0 10px 50px;">                                        
                                  <table id="togle_settings_availability_based_on_visitors" style="width:100%;<?php if ($is_use_visitors_number_for_availability != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                      <tr>
                                      <td scope="row" colspan="2">

                                            <fieldset>
                                                <label for="availability_based_on_items">
                                                    <input style=""  <?php if ($availability_based_on_visitors == 'items') echo 'checked="checked"';/**/ ?> 
                                                           value="items" type="radio" 
                                                           id="availability_based_on_items"  name="availability_based_on"    />
                                                    <span><?php _e( "Add tooltip on calendar(s) to show availability based on the number of available booking resource items remaining for each day."  ,'booking'); ?></span>
                                                </label>
                                                <p class="description" style="padding: 0px 0 20px 50px;"><strong><?php _e('Note' ,'booking'); ?>:</strong> <?php printf(__( "Be sure to match the maximum number of visitors for the %sone booking resource%s with the number of visitors specified on the booking form." ,'booking'),'<strong>','</strong>'); ?></p>


                                                <label for="availability_based_on_visitors">
                                                    <input style=""    <?php if ($availability_based_on_visitors == 'visitors') echo 'checked="checked"';/**/ ?> 
                                                           value="visitors" type="radio" 
                                                           id="availability_based_on_visitors"  name="availability_based_on"    />
                                                    <span><?php _e( "Display tooltip on calendar(s) to show availability based on total (fixed) number of visitors for the resource, which can be at free booking resource items." ,'booking'); ?></span>
                                                </label>                                                    
                                                <p class="description" style="padding: 0px 0 0px 50px;"><strong><?php _e('Note' ,'booking'); ?>:</strong> <?php printf(__( "Be sure to match the maximum number of visitors for %sall booking resources%s with the number of visitors specified on the booking form."  ,'booking'),'<strong>','</strong>'); ?></p>

                                            </fieldset>

                                        </td>
                                      </tr>

                                      <tr valign="top"><td colspan="2" style="padding:10px"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>

                                      <tr valign="top" class="ver_premium_hotel" >
                                        <th scope="row"><?php _e('Disable bookings in different booking resources' ,'booking'); ?>:</th>
                                        <td>
                                            <fieldset>
                                                <label for="booking_is_dissbale_booking_for_different_sub_resources" >
                                                  <input <?php if ($booking_is_dissbale_booking_for_different_sub_resources == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_dissbale_booking_for_different_sub_resources; ?>" name="booking_is_dissbale_booking_for_different_sub_resources" id="booking_is_dissbale_booking_for_different_sub_resources" type="checkbox" />
                                                  <?php _e('Check this box to dissable reservations, which can be stored in different booking resources.' ,'booking');?>                                                           
                                                </label>
                                            </fieldset>
                                            <p class="description"><?php _e('When checked, all reserved days must be at same booking resource otherwise error message will show.' ,'booking') ?></p>
                                        </td>
                                      </tr>

                                  </table>
                                </div>
                              </td></tr>

                          <?php /** ?></tbody></table>
             </div> </div> </div><?php /**/ ?>
        <?php
      }

      // Settings show the pending dates as available 
      function settings_show_pending_days_as_available(){

        if(isset($_POST['availability_based_on'])) {
            if (isset( $_POST['booking_is_show_pending_days_as_available'] ))     $is_show_pending_days_as_available = 'On';
            else                                                                  $is_show_pending_days_as_available = 'Off';
            update_bk_option( 'booking_is_show_pending_days_as_available' ,  $is_show_pending_days_as_available );

            if (isset( $_POST['booking_auto_cancel_pending_bookings_for_approved_date'] ))  $booking_auto_cancel_pending_bookings_for_approved_date = 'On';
            else                                                                            $booking_auto_cancel_pending_bookings_for_approved_date = 'Off';
            update_bk_option( 'booking_auto_cancel_pending_bookings_for_approved_date' ,  $booking_auto_cancel_pending_bookings_for_approved_date );
        }
        $is_show_pending_days_as_available                          = get_bk_option( 'booking_is_show_pending_days_as_available');
        $booking_auto_cancel_pending_bookings_for_approved_date     = get_bk_option( 'booking_auto_cancel_pending_bookings_for_approved_date');
      ?>
        <tr valign="top" class="ver_premium_hotel">
             <th scope="row"><?php _e('Use pending days as available' ,'booking'); ?>:</th>
             <td>
                <fieldset>
                    <label for="booking_is_show_pending_days_as_available" >                     
                        <input <?php if ($is_show_pending_days_as_available == 'On') echo "checked"; ?>  
                            value="<?php echo $is_show_pending_days_as_available; ?>"  type="checkbox" 
                            name="booking_is_show_pending_days_as_available" id="booking_is_show_pending_days_as_available" 
                            onclick="javascript: if (this.checked) { jQuery('#togle_settings_show_pending_days_as_available').slideDown('normal'); jQuery('#booking_is_days_always_available').prop('checked', false ); } else  jQuery('#togle_settings_show_pending_days_as_available').slideUp('normal');"
                          />
                        <?php printf(__('Check this box if you want to show the pending days as available in calendars' ,'booking') );?>
                    </label>
                </fieldset>
             </td>
         </tr>

        <tr>
            <td style="padding:0px;" colspan="2">
                <div style="margin: 0px 0 10px 50px;">
                    <table id="togle_settings_show_pending_days_as_available" style="width:100%;<?php if ($is_show_pending_days_as_available != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">                                       
                        <tr valign="top">
                            <td scope="row">
                                <label for="booking_auto_cancel_pending_bookings_for_approved_date">

                                    <input <?php if ($booking_auto_cancel_pending_bookings_for_approved_date == 'On') echo "checked"; ?>  
                                           value="<?php echo $booking_auto_cancel_pending_bookings_for_approved_date; ?>"  type="checkbox" 
                                           name="booking_auto_cancel_pending_bookings_for_approved_date" id="booking_auto_cancel_pending_bookings_for_approved_date" 
                                           onclick="javascript: if (this.checked) alert('<?php printf(__('Warning!!! After you approved the specific booking(s), all your pending bookings of the same booking resource as an approved booking for the dates, which are intersect with dates of approved booking, will be automatically canceled!' ,'booking') );?>');"                                   
                                     />                                 
                                     <?php _e('Auto Cancel all pending bookings for the specific date(s), if some booking is approved for these date(s)' ,'booking'); ?>
                                </label>                                 
                            </td>
                        </tr>
                    </table>
                </div>                
            </td>
         </tr>
         <?php
      }



      function settings_set_show_availability_in_tooltips(){
              if(isset($_POST['booking_highlight_availability_word'])) {
                  if (isset( $_POST['booking_is_show_availability_in_tooltips'] ))     $booking_is_show_availability_in_tooltips = 'On';
                  else                                                         $booking_is_show_availability_in_tooltips = 'Off';
                  update_bk_option( 'booking_is_show_availability_in_tooltips' ,  $booking_is_show_availability_in_tooltips );
                  update_bk_option( 'booking_highlight_availability_word' ,  $_POST['booking_highlight_availability_word'] );


              }
          $booking_is_show_availability_in_tooltips   = get_bk_option( 'booking_is_show_availability_in_tooltips');
          $booking_highlight_availability_word        = get_bk_option( 'booking_highlight_availability_word');

       ?>

           <tr valign="top" class="ver_premium_hotel">
                <th scope="row">
                    <?php _e('Show availability in tooltip' ,'booking'); ?>:
                </th>
                <td>
                    <label for="booking_is_show_availability_in_tooltips" >
                        <input <?php if ($booking_is_show_availability_in_tooltips == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_show_availability_in_tooltips; ?>" name="booking_is_show_availability_in_tooltips" id="booking_is_show_availability_in_tooltips" type="checkbox"
                                 onclick="javascript: if (this.checked) jQuery('#togle_settings_availability_day_show').slideDown('normal'); else  jQuery('#togle_settings_availability_day_show').slideUp('normal');"
                                                                                                      />
                        <?php _e('Check this box to display the available number of booking resources with a tooltip, when mouse hovers over each day on the calendar(s).' ,'booking');?>
                    </label>
                </td>
            </tr>

            <tr valign="top" class="ver_premium_hotel"><td colspan="2"  style="padding:0px;">
                <div style="margin: -10px 0 10px 50px;">
                <table id="togle_settings_availability_day_show" style="width:100%;<?php if ($booking_is_show_availability_in_tooltips != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                    <tr>
                    <th scope="row"><label for="booking_highlight_availability_word" ><?php _e('Availability Title' ,'booking'); ?>:</label></th>
                        <td><input value="<?php echo $booking_highlight_availability_word; ?>" name="booking_highlight_availability_word" id="booking_highlight_availability_word"  type="text"    />
                            <div class="description"><?php printf(__('Type your %savailability%s description' ,'booking'),'<b>','</b>');?></div>
                        </td>
                    </tr>
                </table>
                </div>
            </td></tr>
            <?php
      }


      // Settings: Configure the available dates for the Check In/Out dates of booking resources withe capcity > 1 // Bence
      function settings_advanced_set_fixed_time(){
        if ( isset( $_POST['range_selection_start_time'] ) ) {

            if (isset( $_POST['booking_check_out_available_for_parents'] )) $booking_check_out_available_for_parents = 'On';
            else                                                            $booking_check_out_available_for_parents = 'Off';
            update_bk_option( 'booking_check_out_available_for_parents' ,   $booking_check_out_available_for_parents );

            if (isset( $_POST['booking_check_in_available_for_parents'] ))  $booking_check_in_available_for_parents = 'On';
            else                                                            $booking_check_in_available_for_parents = 'Off';
            update_bk_option( 'booking_check_in_available_for_parents' ,    $booking_check_in_available_for_parents );
        }              
        $range_selection_time_is_active = get_bk_option('booking_range_selection_time_is_active');

        $booking_check_out_available_for_parents = get_bk_option('booking_check_out_available_for_parents') ;
        $booking_check_in_available_for_parents  = get_bk_option('booking_check_in_available_for_parents');                          
        ?>
            <tr valign="top" class="ver_premium booking_time_advanced_config"  style="<?php if ( get_bk_option( 'booking_type_of_day_selections') == 'single' ) { echo 'display:none;'; } ?>"> 
                <td colspan="2" style="padding-top:0px;padding-bottom:0px;">
                <div style="margin-left:40px;margin-top: -20px;">    
                <table id="togle_settings_availble_for_cehck_in_out" style="width:100%;<?php if ($range_selection_time_is_active != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table togle_settings_range_times">
                    <tr><td>
                            <input <?php if ($booking_check_in_available_for_parents == 'On') echo "checked"; ?> type="checkbox" 
                                value="<?php echo $booking_check_in_available_for_parents; ?>" 
                                name="booking_check_in_available_for_parents"  id="booking_check_in_available_for_parents" />
                            <label for="booking_check_in_available_for_parents"class="description"> <?php _e('Use "Check In" date as available in calendar for booking resources with capacity higher then 1 for search results' ,'booking');?></label>
                        </td>
                    </tr>                        
                    <tr><td>
                            <input <?php if ($booking_check_out_available_for_parents == 'On') echo "checked"; ?> type="checkbox" 
                                value="<?php echo $booking_check_out_available_for_parents; ?>" 
                                name="booking_check_out_available_for_parents"  id="booking_check_out_available_for_parents" />
                            <label for="booking_check_out_available_for_parents"class="description"> <?php _e('Use "Check Out" date as available in calendar for booking resources with capacity higher then 1 search results' ,'booking');?></label>
                        </td>
                    </tr>                        
                </table>
                </div>
                </td>
            </tr>                
        <?php                
      }


      // Show coupons settings block
      function settings_show_coupons() {

          global $wpdb;
          $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=coupons';
          $this->delete_expire_coupons();

          
          if (isset($_GET['delete_coupon'])) {
            $sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking_coupons WHERE coupon_id = %d",  $_GET['delete_coupon'] );
            if ( false === $wpdb->query( $sql ) ){
               echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:bold;">'. bk_error('Error during deleting from DB coupon',__FILE__,__LINE__ ). '</div>';
            }
            ?>
              <script type="text/javascript">
                document.getElementById('ajax_working').innerHTML = '<div class="updated"><?php echo __('Coupon Deleted' ,'booking'); ?></div>';
                jQuery('#ajax_working').fadeOut(3000);
                window.location.href='<?php echo $link ;?>';
            </script>
            <?php
            return;
          }

          $edit_coupon_data = false;
          if ( isset($_GET['edit_coupon'] ) ) {
            $sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}booking_coupons WHERE coupon_id = %d",  $_GET['edit_coupon'] );
            $result = $wpdb->get_results( $sql );
            if ( count($result) == 0 ) { ?>
               <script type="text/javascript">
                    document.getElementById('ajax_working').innerHTML = '<div class="updated"><?php echo __('Coupon does not exist' ,'booking'); ?></div>';
                    jQuery('#ajax_working').fadeOut(3000);
                    window.location.href='<?php echo $link ;?>';
               </script>
               <?php                
            } else {
//debuge($result);
                $result[0]->expiration_date = explode(' ', $result[0]->expiration_date);
                $result[0]->expiration_date = explode('-', $result[0]->expiration_date[0]);
                $edit_coupon_data = array(
                    'coupon_id' => $result[0]->coupon_id
                        , 'coupon_active' => $result[0]->coupon_active  //1
                        , 'coupon_code' => $result[0]->coupon_code //149
                        , 'coupon_value' => $result[0]->coupon_value //149.00
                        , 'coupon_type' => $result[0]->coupon_type //fixed
                        , 'expiration_date' => $result[0]->expiration_date //2016-06-07 00:00:00
                        , 'coupon_min_sum' => $result[0]->coupon_min_sum //0.00
                    , 'support_bk_types' => explode( ',', $result[0]->support_bk_types ) //all
                    , 'users' => $result[0]->users //1      
                );
//debuge($edit_coupon_data);
            }
              
          }
          
          
          if ( isset( $_POST['add_coupon_button'] ) ) {

            $users_values = '';
            $useres_title = '';

            $my_date = $_POST["year_coupon_new"] . '-' .$_POST["month_coupon_new"] . '-' .$_POST["day_coupon_new"];
            $my_resources = implode(',',  $_POST['coupon_resources_new']);
            if ($my_resources != 'all') $my_resources = ','.$my_resources.',';
            else {
                // if multiuser and its not superadmin so then chnage all to ,9,8,....
                if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
                    $is_superadmin = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                    if (! $is_superadmin) { // User not superadmin
                        $bk_ids = apply_bk_filter('get_bk_resources_of_user',false);
                        if ($bk_ids !== false) {
                          $my_resources = ',';
                          foreach ($bk_ids as $bk_id) {
                              $my_resources .= $bk_id->ID . ',';
                          }
                        } else {
                          return; // Need to create booking resources for this user firstly!
                        }
                    }
                }
            }

            //FixIn: 5.4.2
            if ( $_POST["coupon_id"] == '0' ) {                                 // Add new
                
                $my_sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}booking_coupons 
                                                 ( coupon_code, coupon_value, coupon_type, expiration_date, coupon_min_sum, coupon_active, support_bk_types  {$useres_title} ) 
                                           VALUES (%s, %f, %s, %s, %f, %d, %s {$users_values} ) "
                                         , $_POST["coupon_name_new"], $_POST["coupon_value_new"], $_POST["coupon_type_new"]
                                         , $my_date, $_POST["coupon_minimum_new"], $_POST["coupon_active"],  $my_resources );

                if ( false === $wpdb->query( $my_sql )  ){
                    bk_error('Error during updating to DB coupons' ,__FILE__,__LINE__);
                } else {
                    $newid = (int) $wpdb->insert_id;
                    make_bk_action('added_new_coupon',$newid);
                    echo '<div class="updated ajax_message textleft">'.__('Coupon created' ,'booking').'</div>';
                }
                
            } else {                                                            // Edit

                $my_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking_coupons SET "
                                            . "coupon_code = %s "
                                            . ", coupon_value =  %f "
                                            . ", coupon_type = %s "
                                            . ", expiration_date = %s "
                                            . ", coupon_min_sum = %f "
                                            . ", coupon_active = %d "
                                            . ", support_bk_types = %s "
                                          . " WHERE coupon_id = %d ;"  
                                        , $_POST["coupon_name_new"]
                                        , $_POST["coupon_value_new"]
                                        , $_POST["coupon_type_new"]
                                        , $my_date
                                        , $_POST["coupon_minimum_new"]
                                        , $_POST["coupon_active"]
                                        ,  $my_resources
                                        , $_POST["coupon_id"]
                                    );
                
                if ( false === $wpdb->query( $my_sql )  ){
                    bk_error('Error during updating to DB coupon data' ,__FILE__,__LINE__);
                } else {
                    $newid = $_POST["coupon_id"];                    
                    ?>
                   <script type="text/javascript">
                        document.getElementById('ajax_working').innerHTML = '<div class="updated"><?php echo __('Saved' ,'booking'); ?></div>';
                        jQuery('#ajax_working').fadeOut(3000);
                        window.location.href='<?php echo $link ;?>';
                   </script>
                   <?php                
                }
                
            }
          }
          $alternative_color = ' alternative_color ';
      ?>
        <div class='meta-box'>
          <div <?php $my_close_open_win_id = 'bk_settings_coupons_addnew'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php 
            if ( $edit_coupon_data === false ) 
                _e('Add New Discount Coupon' ,'booking'); 
            else 
                _e('Edit' ,'booking'); 
                                        ?></span></h3> <div class="inside">                    
            <form  name="post_coupons_management" action="" method="post" id="post_coupons_management" >

              <div class="booking_settings_row"  style="width:auto; float:left;margin-right:10px;">
                <table class="form-table" >

                   <tr valign="top" class="ver_pro">
                       <th scope="row"><label for="coupon_name_new" ><?php _e('Coupon Code' ,'booking'); ?>:</label></th>
                       <td>
                           <input id="coupon_name_new" name="coupon_name_new" type="text" value="<?php echo (! $edit_coupon_data)?'':$edit_coupon_data['coupon_code']; ?>" maxlength="50" />
                           <p class="description"><?php printf(__('Enter coupon code.' ,'booking'),'<b>','</b>'); ?></p>
                       </td>
                   </tr>

                   <tr valign="top" class="ver_pro">
                       <th scope="row"><label for="coupon_value_new" ><?php _e('Savings' ,'booking'); ?>:</label></th>
                       <td>
                            <input id="coupon_value_new" name="coupon_value_new" type="text" value="<?php echo (! $edit_coupon_data)?'':$edit_coupon_data['coupon_value']; ?>" style="width:48%;float:left;margin-right:1%" maxlength="10"  >
                            <select id="coupon_type_new" name="coupon_type_new" style="width:auto;float:left;" >
                                 <option <?php if ( (! $edit_coupon_data) && ( $edit_coupon_data['coupon_type'] == 'fixed' ) ) { echo ' selected="SELECTED" ';} ?> 
                                         value="fixed"><?php _e('Fixed Amount' ,'booking'); ?></option>
                                 <option <?php if ( (! $edit_coupon_data) && ( $edit_coupon_data['coupon_type'] == '%' ) ) { echo ' selected="SELECTED" ';} ?> 
                                         value="%"><?php _e('Percentage Off' ,'booking'); ?></option>
                            </select>
                            <div class="clear"></div>
                           <p class="description"><?php printf(__('Enter number of fixed or percentage savings.' ,'booking'),'<b>','</b>'); ?></p>
                       </td>
                   </tr>                        

                   <tr valign="top" class="ver_pro">
                       <th scope="row"><label for="coupon_value_new" ><?php _e('Expiration Date' ,'booking'); ?>:</label></th>
                       <td>
                            <?php $cur_date = date('Y-m-d', strtotime("+1 month") ); $cur_date = explode('-',$cur_date); ?>
                            <select  id="year_coupon_new"  name="year_coupon_new" style="width:65px;margin-right:5px;" > <?php    for ($mi = $cur_date[0]; $mi < 2030; $mi++) {   
                                if (! $edit_coupon_data) {
                                    echo '<option value="'.$mi.'" >'.$mi.'</option>';   
                                } else { //Edit
                                    echo '<option value="'.$mi.'"  '; if ($mi == intval( $edit_coupon_data['expiration_date'][0] ) ) echo ' selected="SELECTED" ';  echo ' >'.$mi.'</option>';     
                                }
                            } ?> </select>
                            <strong> / </strong>
                            <select  id="month_coupon_new"  name="month_coupon_new" style="width:50px;margin-right:7px;" > <?php for ($mi = 1; $mi < 13; $mi++) { if ($mi<10) {$mi ='0'.$mi;}  
                                if (! $edit_coupon_data) {
                                    echo '<option value="'.$mi.'"  '; if ($mi ==$cur_date[1]) echo ' selected="SELECTED" ';  echo ' >'.$mi.'</option>';   
                                } else { //Edit
                                    echo '<option value="'.$mi.'"  '; if ( intval($mi) == intval( $edit_coupon_data['expiration_date'][1] ) ) echo ' selected="SELECTED" ';  echo ' >'.$mi.'</option>';   
                                }                                
                            } ?> </select>
                            <strong> / </strong>
                            <select  id="day_coupon_new"  name="day_coupon_new" style="width:50px;" > <?php for ($mi = 1; $mi < 32; $mi++) { if ($mi<10) {$mi ='0'.$mi;}   
                                if (! $edit_coupon_data) {
                                    echo '<option value="'.$mi.'"  '; if ($mi ==$cur_date[2]) echo ' selected="SELECTED" ';  echo ' >'.$mi.'</option>';   
                                } else { //Edit
                                    echo '<option value="'.$mi.'" ';  if ( intval($mi) ==  intval($edit_coupon_data['expiration_date'][2] ) ) echo ' selected="SELECTED" ';  echo '>'.$mi.'</option>';   
                                }                                
                            } ?> </select>
                           <p class="description"><?php printf(__('Select Expiration Date of the coupon.' ,'booking'),'<b>','</b>'); ?></p>
                       </td>
                   </tr>                        


                   <tr valign="top" class="ver_pro">
                       <th scope="row"><label for="coupon_minimum_new" ><?php _e('Minimum Booking Cost' ,'booking'); ?>:</label></th>
                       <td>
                            <input id="coupon_minimum_new" name="coupon_minimum_new" type="text" value="<?php echo (! $edit_coupon_data)?'0':$edit_coupon_data['coupon_min_sum']; ?>" maxlength="10"  >
                           <p class="description"><?php printf(__('Enter minimum booking cost, when coupon is applicable.' ,'booking'),'<b>','</b>'); ?></p>
                       </td>
                   </tr>                        


                   <tr valign="top" class="ver_pro">
                       <th scope="row"><label for="coupon_active" ><?php _e('Maximum number of usage' ,'booking'); ?>:</label></th>
                       <td>
                            <input id="coupon_active" name="coupon_active" type="text" value="<?php echo (! $edit_coupon_data)?'1000':$edit_coupon_data['coupon_active']; ?>" maxlength="10"  >
                           <p class="description"><?php printf(__('Enter maximum number of times, when coupon is applicable.' ,'booking'),'<b>','</b>'); ?></p>
                       </td>
                   </tr>                        
                   
                </table>
              </div>

              <div class="booking_settings_row" style="float:left;">
                <table class="form-table" >
                   <tr valign="top">
                       <td>
                            <strong><label for="coupon_resources_new"><?php _e('Resources' ,'booking'); ?>:</label></strong>
                            <div class="clear" style="height:9px;"></div>
                            <select id="coupon_resources_new" name="coupon_resources_new[]" multiple="MULTIPLE" style="height:225px;width:auto;float:left;" >
                                <option value="all" 
                                        <?php 
                                        if ( $edit_coupon_data !== false ) {
                                            if ( in_array( 'all', $edit_coupon_data['support_bk_types']) )  { 
                                                echo ' selected="SELECTED" ';
                                            } 
                                        } else  
                                            echo ' selected="SELECTED" '; ?>    
                                        
                                        ><?php _e('All' ,'booking'); ?></option><?php  

                                $bk_resources = $this->get_booking_types_hierarhy_linear();

                                foreach ($bk_resources as $mm) { 
                                    $mm = $mm['obj'];
                                ?><option 
                                        <?php if (  ($edit_coupon_data !== false ) && ( in_array( $mm->id, $edit_coupon_data['support_bk_types']) ) ) { echo ' selected="SELECTED" ';} ?>
                                        value="<?php echo $mm->id; ?>"
                                        style="<?php if  (isset($mm->parent)) if ($mm->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                                    ><?php echo $mm->title; ?></option>
                                <?php } ?>
                            </select>
                            <div class="clear"></div>
                            <p class="description"><?php printf(__('Select booking resources, where is possible to apply this coupon code.' ,'booking'),'<b>','</b>'); ?></p>
                       </td>
                   </tr>
                </table>
              </div>  

              <div class="clear" style="height:10px;"></div>
              <input class="button button-primary"  style="margin-top:20px" type="submit" value="<?php 
                if ( $edit_coupon_data !== false ) 
                    _e('Save Changes' ,'booking'); 
                else
                    _e('Add New Coupon' ,'booking'); 
                                                                                                ?>" name="add_coupon_button" id="add_coupon_button"/>
              <div class="clear" style="height:10px;"></div>
              
            <input id="coupon_id" name="coupon_id" type="hidden" value="<?php echo ($edit_coupon_data === false)?'0':$edit_coupon_data['coupon_id']; ?>" />
              

            </form>
       </div> </div> </div> <?php

      $is_exist_coupons = $this->is_exist_coupons('any');

      // List of all coupons 
      if( $is_exist_coupons ) { 

        $where = '';
        $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
        if ($where != '') $where = ' WHERE ' . $where;

        $sql = "SELECT
                         bc.coupon_id AS id,
                         bc.coupon_active AS active,
                         bc.coupon_code AS code,
                         bc.coupon_value AS value,
                         bc.coupon_type AS type,
                         bc.expiration_date AS date,
                         bc.coupon_min_sum AS min,
                         bc.support_bk_types  AS resource
               FROM {$wpdb->prefix}booking_coupons as bc   ".$where." 
               ORDER BY  bc.expiration_date  ";
        $result = $wpdb->get_results( $sql );
        ?>
        <table style="width:100%;" class="resource_table booking_table wpbc_coupons_table" cellpadding="0" cellspacing="0">

            <tr>
                <th class="wpbc_column_1"> <?php _e('ID' ,'booking'); ?> </th>
                <th rel="tooltip" class="wpbc_column_2 tooltip_bottom"  title='<?php echo __('The coupon code your customers will be using to receive the discount.' ,'booking'); ?>'> <?php _e('Coupon Code' ,'booking'); ?></th>
                <th rel="tooltip" class="wpbc_column_3 tooltip_bottom"  title='<?php echo __('The amount which will be saved. Enter only digits.' ,'booking'); ?>'> <?php _e('Savings' ,'booking'); ?></th>
                <th rel="tooltip" class="wpbc_column_4 tooltip_bottom"  title='<?php echo __('The minimum total cost required to use the coupon' ,'booking'); ?>'> <?php _e('Minimum Purchase' ,'booking'); ?></th>
                <th rel="tooltip" class="wpbc_column_5 tooltip_bottom"  title='<?php echo __('The date your coupon will expire' ,'booking'); ?>'> <?php _e('Expiration Date' ,'booking'); ?></th>
                <th rel="tooltip" class="wpbc_column_2 tooltip_bottom"  title='<?php echo __('Maximum number of usage' ,'booking'); ?>'> <?php _e('Number of usage' ,'booking'); ?></th>
                <th rel="tooltip" class="wpbc_column_6 tooltip_bottom"  title='<?php echo __('Resource list, which supports this coupon' ,'booking'); ?>'> <?php _e('Resources' ,'booking'); ?></th>
                <th class="wpbc_column_7" ><?php _e('Actions' ,'booking'); ?></th>
            </tr>

            <?php
            foreach ($result as $res) {
                if ( $alternative_color == '')  $alternative_color = ' alternative_color ';
                else $alternative_color = '';
                $coupon_type = $res->type;
                $coupon_date = explode(' ',  $res->date);
                $coupon_date = $coupon_date[0];
                $coupon_date = explode('-',  $coupon_date);

                $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
                if (strlen($cost_currency) == 3 ) 
                    $init_cost_currency = true;
                else 
                    $init_cost_currency = false;

                $my_res = $res->resource;
                if ($my_res == 'all') 
                    $my_res = '<span style="font-size:13px;">' . __('All' ,'booking') . '</span>';
                else {
                    $my_res_ids = explode(',',$my_res);
                    $my_res = '';

                    foreach ($my_res_ids as $res_id) {
                        if ($res_id !='') 
                            $my_res .= get_booking_title($res_id) . ', ';
                    }
                    if (strlen($my_res)>1) 
                        $my_res = substr($my_res, 0, -2);
                }
            ?>
            <tr>
                <td class="wpbc_column_1 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('ID' ,'booking'); ?>:</legend> <?php 
                    echo $res->id; ?></td>
                <td class="wpbc_column_2 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('Coupon Code' ,'booking'); ?>:</legend> 
                    <span class="wpbc_label wpbc_label_blue"><?php 
                    echo $res->code; ?></span></td>
                <td class="wpbc_column_3 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('Savings' ,'booking'); ?>:</legend> <strong><?php
                        if( ($coupon_type == 'fixed') && (! $init_cost_currency) ) echo $cost_currency;
                        echo $res->value , ' ';
                        if( ($coupon_type == 'fixed') && ($init_cost_currency) ) echo $cost_currency;
                        if( $coupon_type == '%') echo '%'
                ?></strong> </td>
                <td class="wpbc_column_4 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('Minimum Purchase' ,'booking'); ?>:</legend> <?php
                        if (! $init_cost_currency) echo $cost_currency;
                        echo $res->min , ' ';
                        if  ($init_cost_currency) echo $cost_currency;
                ?> </td>
                <td class="wpbc_column_5 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('Expiration Date' ,'booking'); ?>:</legend> <?php 
                    echo $coupon_date[0] . ' / ' . $coupon_date[1] . ' / '.$coupon_date[2] ; ?></td>
                <td class="wpbc_column_2 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('Number of usage' ,'booking'); ?>:</legend> 
                    <strong><?php echo $res->active; ?></strong></td>        
                <td class="wpbc_column_6 <?php echo $alternative_color; ?>"><legend class="wpbc_mobile_legend"><?php _e('Resources' ,'booking'); ?>:</legend> <?php 
                    echo $my_res; ?></td>
                <td class="wpbc_column_7 <?php echo $alternative_color; ?> wpdevbk">
<!--                    <input type="button" 
                           onclick="javascript:var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("Do you really want to delete this item?" ,'booking'); ?>'); 
                                               if ( answer){ 
                                                    window.location.href='<?php echo $link . '&delete_coupon=' . $res->id; ?>'; }"  
                            value="Delete" class="button-secondary button" 
                            name="coupon_is_delete<?php echo $res->id; ?>" 
                            id="coupon_is_delete<?php echo $res->id; ?>" >-->
                    <a class="tooltip_top button-secondary button" rel="tooltip" data-original-title="<?php _e('Edit' ,'booking'); ?>" 
                       onclick="javascript: window.location.href='<?php echo $link . '&edit_coupon=' . $res->id; ?>';" 
                       href="javascript:void(0)"><i class="icon-edit"></i> <?php //_e('Edit' ,'booking'); ?></a>                    
                    <a class="tooltip_top button-secondary button" rel="tooltip" data-original-title="<?php _e('Delete' ,'booking'); ?>" 
                       onclick="javascript: var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("Do you really want to delete this item?" ,'booking'); ?>'); 
                                            if ( answer){ 
                                                 window.location.href='<?php echo $link . '&delete_coupon=' . $res->id; ?>'; }" 
                       href="javascript:void(0)"><i class="icon-trash"></i> <?php //_e('Delete' ,'booking'); ?></a>                    
                </td>
            </tr>
            <?php } ?>

        </table>

      <?php } 

      }

            // Show help hint of shortcode at the admin panel
            function show_additional_shortcode_help_for_form(){
                ?><span class="description"><?php printf(__('%s - coupon field, ' ,'booking'),'<code>[coupon]</code>');?></span>
                  <span class="description example-code"><?php printf(__('Example: %s ' ,'booking'),'[coupon* my_coupon]');?></span><br/><?php
            }


      //Show Search settings //
      function show_search_settings(){

                    if ( isset( $_POST['booking_search_form_show'] ) ) {
                         $_POST['booking_search_form_show'] = str_replace('\"', '"', $_POST['booking_search_form_show']);
                         $_POST['booking_search_form_show'] = str_replace("\'", "'", $_POST['booking_search_form_show']);
                         update_bk_option( 'booking_search_form_show' , $_POST['booking_search_form_show'] );
                    }
                    $booking_search_form_show = get_bk_option( 'booking_search_form_show');

                    if ( isset( $_POST['booking_found_search_item'] ) ) {
                         $_POST['booking_found_search_item'] = str_replace('\"', '"', $_POST['booking_found_search_item']);
                         $_POST['booking_found_search_item'] = str_replace("\'", "'", $_POST['booking_found_search_item']);
                         update_bk_option( 'booking_found_search_item' , $_POST['booking_found_search_item'] );
                    }
                    $booking_found_search_item = get_bk_option( 'booking_found_search_item');
                    
                                                                                //FixIn:6.1.0.1
                ?>
                <script type="text/javascript">
                    function reset_to_def_search_form( form_type ) {
                        var search_form_content = '';
                        if (form_type == 'horizontal')    
                            search_form_content = '<?php echo str_replace( '\\n\\r', '\n', $this->get_default_booking_search_form_show('horizontal') ); ?>';
                        if (form_type == 'inline')    
                            search_form_content = '<?php echo str_replace( '\\n\\r', '\n', $this->get_default_booking_search_form_show('inline') ); ?>';
                        if ( (form_type == 'standard') || (form_type == '') )    
                            search_form_content = '<?php echo str_replace( '\\n\\r', '\n', $this->get_default_booking_search_form_show('standard') ); ?>';
                        if (form_type == 'advanced')    
                            search_form_content = '<?php echo str_replace( '\\n\\r', '\n', $this->get_default_booking_search_form_show('advanced') ); ?>';
                        reset_wp_editor_content('booking_search_form_show', search_form_content )
                    }
                    function reset_to_def_found_search_item( form_type ) {                        
                        var search_form_content = '';                        
                        if ( (form_type == 'standard') || (form_type == '') )                            
                            search_form_content = '<?php echo str_replace( '\\n\\r', '\n', $this->get_default_booking_found_search_item('standard') ); ?>';
                        if (form_type == 'advanced')    
                            search_form_content = '<?php echo str_replace( '\\n\\r', '\n', $this->get_default_booking_found_search_item('advanced') ); ?>';                        
                        reset_wp_editor_content('booking_found_search_item', search_form_content )
                    }
                </script>

                <div class='meta-box'>
                    <div <?php $my_close_open_win_id = 'bk_settings_search_form_fields'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php _e('Search Availability Form' ,'booking'); ?></span></h3><div class="inside">
                        

<div class="btn-group" style="margin:0px;float: left; height: auto;">
        <select name="select_reset_booking_form" id="select_reset_booking_form" style="margin-top:0px; height: auto;">                                
            <option value="inline"><?php _e('Inlinee Search Form Template' ,'booking'); ?></option>
            <option value="horizontal"><?php _e('Horizontal Search Form Template' ,'booking'); ?></option>
            <option value="standard"><?php _e('Standard Search Form Template' ,'booking'); ?></option>
            <option value="advanced"><?php _e('Advanced' ,'booking'); ?></option>
        </select>
        <a     data-original-title="<?php _e('Reset current Form' ,'booking'); ?>"  rel="tooltip" 
               class="tooltip_top button button-secondary"

               onclick="javascript: var sel_res_val = document.getElementById('select_reset_booking_form').options[ document.getElementById('select_reset_booking_form').selectedIndex ].value;
                   reset_to_def_search_form( sel_res_val ); " ><?php 
               _e('Reset' ,'booking'); ?></a>
</div>
<div class="clear" style="height:1px;"></div>                            
                                <div class="booking_settings_row"  style="float:left;margin:10px 0px;width:48%;" >
                                    <?php /**/
                                    wp_editor( $booking_search_form_show, 
                                               'booking_search_form_show',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'booking_search_form_show'
                                                   , 'textarea_rows' => 9
                                                   , 'default_editor' => 'html'
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /* ?> <textarea id="booking_search_form_show" name="booking_search_form_show" class="darker-border" style="width:100%;" rows="22"><?php echo htmlspecialchars($booking_search_form_show, ENT_NOQUOTES ); ?></textarea> /**/ ?>
                                </div>
                                <div class="booking_settings_row code_description"  style="float:right;margin:0;width:50%;" >
                                    <div  class="wpbc-help-message">
                                      <p class="description"><strong><?php printf(__('Use these shortcodes for customization: ' ,'booking'));?></strong></p>
                                      <p class="description"><?php printf(__('%s - search inside posts/pages which are part of this category, ' ,'booking'),'<code>[search_category]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - search inside posts/pages which have this tag, ' ,'booking'),'<code>[search_tag]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - check-in date, ' ,'booking'),'<code>[search_check_in]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - check-out date, ' ,'booking'),'<code>[search_check_out]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - default selection number of visitors, ' ,'booking'),'<code>[search_visitors]</code>');?></span></br>
                                      <p class="description example-code"><?php echo (sprintf(__('Example: %s - custom number of visitor selections"' ,'booking'),'<code>[search_visitors "1" "2" "3" "4" "5" "6" "7" "8" "9" "10"]</code>')); ?></p>
                                      <p class="description"><?php printf(__('%s - search button, ' ,'booking'),'<code>[search_button]</code>');?></p>
                                      
                                      <p class="description"><strong><?php _e('HTML tags is accepted.' ,'booking');?></strong></p>
                                      <?php // make_bk_action('show_additional_translation_shortcode_help'); ?>
                                    </div>
                                </div>

                                <div class="clear" style="height:1px;"></div>
                                                        
                 </div></div></div>


                <div class='meta-box'>
                    <div <?php $my_close_open_win_id = 'bk_settings_search_form_fields_show'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php printf(__('Search Results' ,'booking')); ?></span></h3><div class="inside">

                                <div class="clear" style="height:1px;"></div>
                                
<div class="btn-group" style="margin:0px;float: left; height: auto;">
        <select name="select_reset_search_results" id="select_reset_search_results" style="margin-top:0px; height: auto;">                                
            <option value="standard"><?php _e('Standard' ,'booking'); ?></option>
            <option value="advanced"><?php _e('Advanced' ,'booking'); ?></option>
        </select>
        <a     data-original-title="<?php _e('Reset current Form' ,'booking'); ?>"  rel="tooltip" 
               class="tooltip_top button button-secondary"

               onclick="javascript: var sel_res_val = document.getElementById('select_reset_search_results').options[ document.getElementById('select_reset_search_results').selectedIndex ].value;
                   reset_to_def_found_search_item( sel_res_val ); " ><?php 
               _e('Reset' ,'booking'); ?></a>
</div>
                                
                                <div class="clear" style="height:1px;"></div>

                                <div class="booking_settings_row"  style="float:left;margin:10px 0px;width:48%;" >
                                    <?php /**/
                                    wp_editor( $booking_found_search_item, 
                                               'booking_found_search_item',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'booking_found_search_item'
                                                   , 'textarea_rows' => 14
                                                   , 'default_editor' => 'html'
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /* ?> <textarea id="booking_found_search_item" name="booking_found_search_item" class="darker-border" style="width:100%;" rows="22"><?php echo htmlspecialchars($booking_found_search_item, ENT_NOQUOTES ); ?></textarea> /**/ ?>
                                </div>
                                <div class="booking_settings_row code_description"  style="float:right;margin:0;width:50%;" >
                                    <div  class="wpbc-help-message">
                                      <p class="description"><strong><?php printf(__('Use these shortcodes for customization: ' ,'booking'));?></strong></p>
                                      <p class="description"><?php printf(__('%s - resource title, ' ,'booking'),'<code>[booking_resource_title]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - link to the page with booking form, ' ,'booking'),'<code>[link_to_booking_resource "Book now"]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - availability of booking resource, ' ,'booking'),'<code>[num_available_resources]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - maximum number of visitors for the booking resource, ' ,'booking'),'<code>[max_visitors]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - cost of booking the resource, ' ,'booking'),'<code>[standard_cost]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - featured image, taken from the featured image associated with the post, ' ,'booking'),'<code>[booking_featured_image]</code>');?></p>
                                      <p class="description"><?php printf(__('%s - booking info, taken from the excerpt associated with the post, ' ,'booking'),'<code>[booking_info]</code>');?></p>
                                     <?php 
                                        echo  '<hr><p class="description"><code>'.'[cost_hint]'.'</code> - ' . __('Full cost of the booking.' ,'booking') . '</p>'
                                            . '<p class="description"><code>'.'[original_cost_hint]'.'</code> - ' . __('Cost of the booking for the selected dates only.' ,'booking') . '</p>'
                                            . '<p class="description"><code>'.'[additional_cost_hint]'.'</code> - ' . __('Additional cost, which depends on the fields selection in the form.' ,'booking') . '</p>'
                                            . '<p class="description"><code>'.'[deposit_hint]'.'</code> - ' . __('The deposit cost of the booking.' ,'booking') . '</p>'
                                            . '<p class="description"><code>'.'[balance_hint]'.'</code> - ' . __('Balance cost of the booking - difference between deposit and full cost.' ,'booking') . '</p>';
                                      ?>

                                      
                                      <p class="description"><strong><?php _e('HTML tags is accepted.' ,'booking');?></strong></p>
                                      <?php // make_bk_action('show_additional_translation_shortcode_help'); ?>
                                    </div>
                                </div>

                                <div class="clear" style="height:1px;"></div>
                 </div></div></div>
                
                <?php $this->show_search_cache_settings(); ?>
                
                <div class='meta-box' style="">                
                   <div <?php $my_close_open_win_id = 'bk_settings_search_form_help_info'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                       <h3 class='hndle'><span><?php _e('Help Info' ,'booking'); ?></span></h3>          
                        <div class="inside" style="margin:0px;">
<?php /*
                             <div class="wpbc-help-message" style="margin-top:10px;">                
                                 <ol>
                                    <li><?php _e('Configure search form and search results' ,'booking');?></li>
                                    <li><?php _e('Insert into different posts booking forms for the different booking resources. Please note, search will not work for "child" booking resources.' ,'booking');?></li>
                                    <li><?php _e('Reset search cache,  by clicking on specific button at this page.' ,'booking');?></li>
                                    <li><?php _e('You have to see how many posts with  booking forms are found.' ,'booking');?></li>
                                    <li><?php _e('Insert search form shortcode into the post and make searching.' ,'booking');?></li>
                                </ol>
                             </div>  */ ?>
                            <div class="clear" style="height:10px;"></div>                                
                            <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
                            <div class="clear" style="height:10px;"></div> 
                             <div class="wpbc-help-message" style="margin-top:10px;">                
                                <span class="wpdevbk" style="text-align:left;"><?php _e('CSS customization of search form and search results you can make at this file' ,'booking'); echo ': <code>', WPDEV_BK_PLUGIN_URL, '/inc/css/search-form.css</code>'; ?></span>
                             </div>  
                            <div class="clear" style="height:10px;"></div>                                
                            <div class="wpbc-error-message" style="text-align:left;">                
                                <span class="wpdevbk" style="text-align:left;"> 
                                <strong><?php _e('Note' ,'booking'); ?>!</strong> <?php
                                printf(__('If you do not see search results at front-end side of your website, please check troubleshooting instruction %shere%s' ,'booking'),'<a href="http://wpbookingcalendar.com/faq/no-search-results/" target="_blank">','</a>');  ?></span>
                            </div>  
                        </div>
                 </div>
                </div>                 
                <?php

      }

              // Get default search forms 
              function get_default_booking_search_form_show( $search_form_type = '' ){     //FixIn:6.1.0.1
                  
                switch ( $search_form_type ) {
              
                    case 'inline':
                        return   '<div class="wpdevbk">' . '\n\r'
                               . '    <div class="form-inline well">' . '\n\r'
                               . '        <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                               . '        <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                               . '        <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                               . '        [search_button]' . '\n\r'
                               . '    </div>' . '\n\r'
                               . '</div>';

                    case 'horizontal':
                        return   '<div class="wpdevbk">' . '\n\r'
                               . '    <div class="form-horizontal well">' . '\n\r'
                               . '        <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                               . '        <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                               . '        <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                               . '        <hr/>\n\        [search_button]' . '\n\r'
                               . '    </div>' . '\n\r'
                               . '</div>';
                        
                    case 'advanced':                                            
                        return   '<div class="wpdevbk">' . '\n\r'
                               . '    <div class="form-inline well">' . '\n\r'
                               . '        <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                               . '        <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                               . '        <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                               . '        [search_button]' . '\n\r'
                               . '        <br/><label>[additional_search "3"] +/- 2 '.__('days' ,'booking').'</label>' . '\n\r'
                               . '    </div>' . '\n\r'
                               . '</div>';
                    default:
                        return 
                               
                                 ' <label>'.__('Check in' ,'booking').':</label> [search_check_in]' . '\n\r'
                               . ' <label>'.__('Check out' ,'booking').':</label> [search_check_out]' . '\n\r'
                               . ' <label>'.__('Guests' ,'booking').':</label> [search_visitors]' . '\n\r'
                               . ' [search_button] ';                        
                }
                  
              }

              function get_default_booking_found_search_item( $search_form_type = '' ){     //FixIn:6.1.0.1
                                
                  switch ($search_form_type) {                    
                        
                    case 'advanced':                                           
                        return   '<div class="wpdevbk">' . '\n\r'
                               . '  ' . '<div style="float:right;"><div>Cost: <strong>[cost_hint]</strong></div>' . '\n\r'
                               . '  ' . '[link_to_booking_resource "Book now"]</div>' . '\n\r'
                               . '  ' . '<a href="[book_now_link]" class="wpbc_book_now_link">' . '\n\r'
                               . '  ' . '    ' .'[booking_resource_title]' . '\n\r'
                               . '  ' . '</a>' . '\n\r'
                               . '  ' . '[booking_featured_image]' . '\n\r'
                               . '  ' . '[booking_info]' . '\n\r'
                               . '  ' . '<div>' . '\n\r'
                               . '  ' . '  ' . __('Availability' ,'booking').': [num_available_resources] item(s).' . '\n\r'
                               . '  ' . '  ' . __('Max. persons' ,'booking').': [max_visitors]' . '\n\r'
                               . '  ' . '  ' . 'Check in/out: <strong>[search_check_in]</strong> - ' . '\n\r'
                               . '  ' . '                ' . '<strong>[search_check_out]</strong>' . '\n\r'
                               . '  ' . '</div>' . '\n\r'
                               . '</div>';
                        
                    default:
                        return   '<div class="wpdevbk">' . '\n\r'
                               . '    <div style="float:right;">' . '\n\r'
                               . '        ' . '<div>From [standard_cost]</div>' . '\n\r'
                               . '        ' . '[link_to_booking_resource "Book now"]' . '\n\r'
                               . '    </div>' . '\n\r'
                               . '    [booking_resource_title]' . '\n\r'
                               . '    [booking_featured_image]' . '\n\r'
                               . '    [booking_info]' . '\n\r'
                               . '    <div>' . '\n\r'
                               . '        ' . __('Availability' ,'booking').': [num_available_resources] item(s).' . '\n\r'
                               . '        ' . __('Max. persons' ,'booking').': [max_visitors]' . '\n\r'
                               . '    </div>' . '\n\r'                            
                               . '</div>';
                  }                      
              }



              // Show Advanced settings at the bootom of Resource Settings page
              function show_search_cache_settings() {

                  global $wpdb;

                  if(isset($_POST['cache_expiration'])) {
                      update_bk_option( 'booking_cache_expiration' ,  $_POST['cache_expiration'] );
                  }

                  $cache_expiration   = get_bk_option( 'booking_cache_expiration');


                  // Cache Reset                      
                  if (isset($_GET['cache_reset']))
                      if ($_GET['cache_reset'] == '1') 
                        $this->regenerate_booking_search_cache();

                  $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&tab=search';
                  ?>
                                    <div class='meta-box'>
                                      <div <?php $my_close_open_win_id = 'bk_settings_search_cache_settings'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                            <h3 class='hndle'><span><?php _e('Search Cache' ,'booking'); ?></span></h3> <div class="inside">
                                            
                                            <div class="clear" style="height:1px;"></div>
                                            <input class="button-primary button" style="float:left;margin:10px;" type="button" value="<?php _e('Reset Search Cache' ,'booking'); ?>" onclick="javascript:window.location.href='<?php echo $link ;?>&cache_reset=1';" name="reset_form"/>                                            
                                            <div class="clear" style="height:1px;"></div>
                                            
                                            <table class="form-table"><tbody>

                                            <tr valign="top">
                                                <th scope="row"><label for="cache_expiration" ><?php _e('Cache expiration' ,'booking'); ?>:</label></th>
                                                <td>
                                                    <select id="cache_expiration" name="cache_expiration">

                        <?php for ($mm = 1; $mm < 25; $mm++) { ?>
                                                        <option <?php if($cache_expiration == $mm .'h') echo "selected"; ?> value="<?php echo $mm; ?>h"><?php echo $mm ,' ';
                            _e('hour(s)' ,'booking'); ?></option>
                            <?php } ?>

                        <?php for ($mm = 1; $mm < 32; $mm++) { ?>
                                                        <option <?php if($cache_expiration == $mm .'d') echo "selected"; ?> value="<?php echo $mm; ?>d"><?php echo $mm ,' ';
                            _e('day(s)' ,'booking'); ?></option>
                            <?php } ?>

                                                    </select>
                                                    <span class="description"><?php _e('Select time of cache expiration' ,'booking');?></span>
                                                </td>
                                            </tr>

                                            </tbody></table>


                                <p class="wpbc-error-message" style="text-align:left;">
                                      <span class="description"><?php printf(__('Cache will expire:' ,'booking'));?> </span><?php

                                        $period =  get_bk_option( 'booking_cache_expiration' );
                                        if (substr($period,-1,1) == 'd' ) {
                                            $period = substr($period,0,-1);
                                            $period = $period * 24 * 60 * 60;
                                        }                                            
                                        if (substr($period,-1,1) == 'h' ) {
                                            $period = substr($period,0,-1);
                                            $period = $period * 60 * 60;
                                        }
                                        $previos = get_bk_option( 'booking_cache_created' );
                                        $previos = explode(' ',$previos);
                                        $previos_time = explode(':',$previos[1]);
                                        $previos_date = explode('-',$previos[0]);
                                        $previos_sec = mktime($previos_time[0], $previos_time[1], $previos_time[2], $previos_date[1], $previos_date[2], $previos_date[0]);

                                        $expire_sec = ($previos_sec+$period);
                                        $cache_epire_on = date_i18n('Y-m-d H:i:s T', $expire_sec  ) ;

                                        echo '<code>' . $cache_epire_on . '</code>';

                                        //FixIn: 6.0.1.15
                                        $found_records =  get_bk_option( 'booking_cache_content');
                                        if (! empty($found_records)) {
                                                if (is_serialized($found_records)) $found_records = @unserialize($found_records);
                                                $found_records_num = count($found_records);
                                        } else  $found_records_num = 0;
                                      ?>
                                      <br/><span class="description"><?php printf(__('Found: %s booking forms inside of posts or pages ' ,'booking'),'<code>'.$found_records_num.'</code>');?></span>
                                      <?php  
                                      foreach ( $found_records as $found_record ) {
                                          echo '<br/><code>';
                                          echo '[ Page ID=',  $found_record->ID, '] ', ' [', __('Resource', 'booking') , ' ID=',   $found_record->booking_resource, '] ', $found_record->guid ;
                                          echo '</code>';
                                      }
                                      ?>
                                </p>

                                            <div class="clear" style="height:10px;"></div>

                                   </div> </div> </div>

                  <?php
              }

              
      function wpbc_general_settings_top_menu_submenu_line() {
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');

            if (  ( $is_can ) && ( ( isset($_GET['tab']) ) && ($_GET['tab'] == 'search') )  ) {
            ?>
                <div class="booking-submenu-tab-container">
                    <div class="nav-tabs booking-submenu-tab-insidecontainer">

                        <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_search_form_fields' );"
                           class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Search Form' ,'booking');?></span></a>

                        <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_search_form_fields_show' );"
                           class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Search Results' ,'booking');?></span></a>

                        <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_search_cache_settings' );"
                           class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Search Cache' ,'booking');?></span></a>

                        <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_search_form_help_info' );"
                           class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Help Info' ,'booking');?></span></a>

                           
                           
                        <input type="button" class="button-primary button" value="<?php _e('Save Changes' ,'booking'); ?>" 
                           style="float:right;"
                           onclick="document.forms['post_search_option'].submit();">

                        <div class="clear" style="height:0px;"></div>

                    </div>
                </div>
              <?php
            }

      }

       // Resources settings MENU //
       function resources_settings_after_title( $bt, $all_id, $alternative_color ){


       }

       // Show headers collumns
       function resources_settings_table_headers(){
            if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability') || ($_GET['tab']=='cost')  ) return ;
            $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');
          ?>
            <!--th style="width:50px; " rel="tooltip" class="tooltip_bottom"  title="<?php _e('Number of resource items inside of parent resource' ,'booking');?>"> <?php _e('Capacity' ,'booking'); ?>  </th-->
            <th style="width:100px;text-align: center; "> <?php _e('Parent' ,'booking');   ?>  </th>
            <th style="width:50px; "> <?php _e('Priority' ,'booking'); ?> </th>
            <?php if ($is_use_visitors_number_for_availability == 'On') { ?>
            <th style="width:50px;white-space: nowrap; " rel="tooltip" class="tooltip_bottom"  title="<?php _e('Maximum number of visitors for resource' ,'booking');?>"> <?php _e('Max' ,'booking'); echo ' '; _e('visitors' ,'booking'); ?> </th>
            <?php } ?>
            <!--th style="text-align: center;"> <?php _e('Actions' ,'booking'); ?> </th-->
            <?php make_bk_action('show_users_header_at_settings' ); ?>

          <?php
       }

       // Show headers collumns
       function resources_settings_table_footers(){
            if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability') || ($_GET['tab']=='cost')  ) return ;
            $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');
          ?>
            <td></td>
            <td></td>
            <?php if ($is_use_visitors_number_for_availability == 'On') { ?>
                <td></td>
            <?php } 
            make_bk_action('show_users_footer_at_settings' ); 
            
            $is_superadmin = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
            if ($is_superadmin) {
                ?> <td></td> <?php             
            }
       }

       // Show Resources Collumns
       function resources_settings_table_collumns( $bt, $all_id, $alternative_color, $advanced_params = array() ){
           if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability') || ($_GET['tab']=='cost')  ) return ;
            $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');
            $my_count = $bt->count;
            ?>

                <?php // Show CAPACITY  ?>
                <!--td style="text-align:center;font-weight: bold;" <?php echo $alternative_color; ?> ><?php if ($bt->parent == 0 ) { echo $my_count; }?></td-->

                <?php // Show Parent selection  ?>
                <td style="text-align:center;border-left:1px solid #ccc;" <?php echo $alternative_color; ?> >
                    <legend class="wpbc_mobile_legend"><?php _e('Parent Resource' ,'booking'); ?>:</legend>
                    <select  style="width:90px;"  name="type_parent<?php echo $bt->id; ?>" id="type_parent<?php echo $bt->id; ?>">
                        <?php foreach ($all_id as $m_id) { ?>
                            <?php if($bt->id != $m_id['id']) { ?>
                            <option <?php if ( $bt->parent==$m_id['id']) echo 'selected="SELECTED"' ?> value="<?php echo $m_id['id']; ?>"><?php echo $m_id['title'] ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </td>


                <?php // Show Priority  ?>
                <td style="text-align:center;" <?php echo $alternative_color; ?> >
                    <legend class="wpbc_mobile_legend"><?php _e('Resource name' ,'booking'); ?>:</legend>
                    <input  maxlength="17" type="text"
                                    style="width:50px;"
                                    value="<?php echo $bt->prioritet; ?>"
                                    name="type_prioritet<?php echo $bt->id; ?>" id="type_prioritet<?php echo $bt->id; ?>" />
                </td>


                <?php // Show MAX Visitors  ?>
                <?php if ($is_use_visitors_number_for_availability == 'On') { ?>

                <td style="text-align:center;" <?php echo $alternative_color; ?> >
                    <legend class="wpbc_mobile_legend"><?php _e('Max visitors' ,'booking'); ?>:</legend>
                    <?php if ($bt->parent == 0 ) { ?>
                        <select <?php if ($bt->parent != 0 ) { echo ' disabled="DISABLED" '; } ?>  style="width:50px;"   name="type_max_visitors<?php echo $bt->id; ?>" id="type_max_visitors<?php echo $bt->id; ?>">
                            <?php for ($m_id = 1; $m_id < 21; $m_id++) { ?>
                                <option <?php if ( $bt->visitors==$m_id) echo 'selected="SELECTED"' ?> value="<?php echo $m_id; ?>"><?php echo $m_id ?></option>
                            <?php } ?>
                        </select>
                    <?php } else { ?>
                        <span><?php echo $bt->visitors; ?></span>
                    <?php } ?>
                </td>

                <?php } ?>


                <?php /*/ Show Add / Delete Button  ?>
                <td style="font-size:11px;text-align: center;" <?php echo $alternative_color; ?>

                    <?php
                    $max_num = apply_bk_filter('get_max_res_num_for_user_in_multiuser', false );
                    if ( ($max_num === false) || ($max_num > count($bk_types) ) )
                        if ($bt->parent==0) { ?>

                            <div style="height:20px;">
                                <input class="button" style="margin:0px 10px;" type="button" value="+ <?php _e('Add' ,'booking'); ?>"
                                   onclick="javascript:
                                           document.getElementById('type_title_new').value='<?php echo $bt->title . '-' . ($my_count+1) ; ?>';
                                       document.getElementById('type_parent_new').value='<?php echo $bt->id  ; ?>';
                                       document.getElementById('type_prioritet_new').value='<?php echo  ($my_count+1)   ; ?>';
                                       document.getElementById('submit_resources_button').click();
                                           " />
                            </div>
                          <?php } ?>

                    <span style="line-height:25px;"><?php _e('Delete' ,'booking'); ?>: </span>
                    <input class="checkbox"  type="checkbox"   name="type_delete<?php echo $bt->id; ?>" id="type_delete<?php echo $bt->id; ?>"/>

                </td>
                <?php /**/ ?>
                <?php make_bk_action('show_users_collumn_at_settings', $bt , $alternative_color ); ?>

                <?php
       }

       function resources_settings_table_info_collumns( $bt, $all_id, $alternative_color ){
            //if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability') || ($_GET['tab']=='cost')  ) return ;
            if ( ($bt->parent == 0 ) && ($bt->count>1) ) {
                ?><span class="wpbc-info-label"><?php _e('Capacity: ' ,'booking'); echo $bt->count; ?></span><?php
            }

       }

       // Show fields for ADD Button at the bottom of table
       function resources_settings_table_add_bottom_button($all_id){ ?>
            <tr valign="top" class="ver_premium_hotel">
                <td style="padding:0px;" colspan="2">
                    <div style="margin: -10px 0 10px 50px;">
                        <table class="hided_settings_table" style="width:100%;">
                            <tbody>              
                                <tr>
                                    <th scope="row" ><label for="type_parent_new"><?php _e('Parent' ,'booking'); ?>:</label></th>
                                    <td> <select name="type_parent_new" id="type_parent_new" style="width:150px;">
                                            <?php foreach ($all_id as $m_id) { ?>
                                                <option  value="<?php echo $m_id['id']; ?>"><?php echo $m_id['title'] ?></option>
                                            <?php } ?>
                                          </select>
                                          <span class="description"><?php _e('Select parent resource, if you want that parent resource to increase capacity.' ,'booking'); ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <th scope="row" ><label for="type_prioritet_new"><?php _e('Priority' ,'booking'); ?>:</label></th>
                                    <td>
                                        <select name="type_prioritet_new" id="type_prioritet_new" style="width:150px;">
                                            <?php for ($m_id = 0; $m_id < 500; $m_id++) { ?>
                                                <option value="<?php echo $m_id; ?>"><?php echo $m_id ?></option>
                                            <?php } ?>
                                        </select>
                                        <span class="description"><?php _e('Set priority of resource - resource with higher priority will be reserved firstly.' ,'booking'); ?></span>  
                                     </td>
                                </tr>
                                <tr>    <?php
                                        $types_list = $this->get_booking_types();
                                        $max_num = apply_bk_filter('get_max_res_num_for_user_in_multiuser', false );
                                        if  ($max_num === false) $max_num = 101;
                                        else {
                                            $max_num = $max_num - count($types_list)+1;
                                        } ?>

                                      <th scope="row" ><label for="type_number_of_resources"><?php _e('Resources count' ,'booking'); ?>:</label></th>
                                      <td>
                                        <select name="type_number_of_resources" id="type_number_of_resources" style="width:150px;">
                                            <?php for ($m_id = 1; $m_id < $max_num; $m_id++) { ?>
                                                <option value="<?php echo $m_id; ?>"><?php echo $m_id ?></option>
                                            <?php } ?>
                                        </select>
                                        <span  class="description"><?php _e('Create several booking resources for one time' ,'booking'); ?></span>
                                      </td>
                                </tr>
                                  <?php
                                  $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                                  if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) {
                                      $booking_forms_extended = get_bk_option( 'booking_forms_extended');
                                      if ($booking_forms_extended !== false) {
                                          if ( is_serialized( $booking_forms_extended ) ) {
                                              $booking_forms_extended = unserialize($booking_forms_extended);
                                          } // else $booking_forms_extended = false;
                                      } 
                                      ?>
                                      <tr>
                                          <th scope="row" ><label for="booking_default_form_new"><?php _e('Default form' ,'booking'); ?>:</label></th>
                                          <td>
                                            <?php
                                              if ($booking_forms_extended !== false) { ?>
                                                  <select id="booking_default_form_<?php echo 'new' ; ?>" name="booking_default_form_<?php echo 'new' ; ?>" style="width:150px;" >
                                                      <option value="standard" ><?php _e('Standard' ,'booking'); ?></option>
                                                      <?php foreach ($booking_forms_extended as $value) { ?>
                                                      <option value="<?php echo $value['name']; ?>" ><?php echo $value['name']; ?></option>
                                                      <?php } ?>
                                                  </select>
                                                  <span  class="description"><?php _e('Select default custom booking form' ,'booking'); ?></span>
                                              <?php } ?>
                                          </td>
                                     </tr>
                                  <?php } ?> 
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr><?php                 
       }


                // Update SQL dfor editing bk resources
                function get_sql_4_update_bk_resources($blank, $bt){
                    global $wpdb;

                    $sql_res = '';
                    $is_use_visitors_number_for_availability   = get_bk_option( 'booking_is_use_visitors_number_for_availability');

                    if ($is_use_visitors_number_for_availability == 'On') {

                      if ( $_POST['type_parent'.$bt->id] != 0 ) {     // Set for Child objects, value of Parent objects
                          $vis_update_string = $wpdb->prepare( " , visitors = %s ",  isset($_POST['type_max_visitors'. $_POST['type_parent'.$bt->id] ]) ? ($_POST['type_max_visitors'. $_POST['type_parent'.$bt->id] ]) : '1' );
                      } else if ( isset($_POST['type_max_visitors'.$bt->id] ) )                                         // Set for Parent objects - normal value
                            $vis_update_string = $wpdb->prepare( " , visitors = %s ", $_POST['type_max_visitors'.$bt->id] ); 
                      else  $vis_update_string =  " , visitors = '1' ";

                    } else  $vis_update_string = '';

                    $sql_res = $wpdb->prepare( " , parent = %s , prioritet = %s {$vis_update_string} "
                                                ,$_POST['type_parent'.$bt->id], $_POST['type_prioritet'.$bt->id] );

                    return $sql_res;
                }

                // Get Fields and Values for Insert new resource
                function get_sql_4_insert_bk_resources_fields( $blank ){
                  return ', parent, prioritet, default_form ';
                }
                function get_sql_4_insert_bk_resources_values( $blank  , $sufix){

                  if ( empty( $_POST['booking_default_form_new'] ) ) 
                      $_POST['booking_default_form_new'] = 'standard';

                  if (empty($sufix)) {
                    return  ' , '. intval($_POST['type_parent_new']) .' , ' . intval($_POST['type_prioritet_new']) .' , "' . wpbc_clean_parameter($_POST['booking_default_form_new']) .'"';
                  } else {

                      $prio = $_POST['type_prioritet_new'] + $sufix;
                      return  ' , '. intval($_POST['type_parent_new']) .' , ' . intval($prio) . ' , "' . wpbc_clean_parameter($_POST['booking_default_form_new']) .'"';
                  }

                }

                function insert_bk_resources_recheck_max_visitors(){
                      global $wpdb;
                      if (isset(  $_POST['type_parent_new'] )) {
                          $booking_id = (int) $wpdb->insert_id;       //Get ID

                          $booking_visitor_num = $this->get_max_visitors_for_bk_resources($_POST['type_parent_new']);
                          if (isset($booking_visitor_num[$_POST['type_parent_new']]))
                              $booking_visitor_num  = $booking_visitor_num[$_POST['type_parent_new']];
                          else $booking_visitor_num =1;

                          if ( false === $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET visitors = %s WHERE booking_type_id = %d " 
                                                        , $booking_visitor_num,  $booking_id) ) ){
                              bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
                          }
                      }

                }
    /////////////////////////////////////////////////////////////////////////////////////

// </editor-fold>


// <editor-fold defaultstate="collapsed" desc="A C T I V A T I O N   A N D   D E A C T I V A T I O N">

//   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ///////////////////////////////////////////////////

    // Activate
    function pro_activate() {

           // add_bk_option( 'booking_maximum_selection_days_for_one_resource', 'Off');
           // add_bk_option( 'booking_maximum_selection_days_for_one_resource', 4);
            add_bk_option( 'booking_check_out_available_for_parents','On');
            add_bk_option( 'booking_check_in_available_for_parents','Off');
            add_bk_option( 'booking_is_show_pending_days_as_available','Off');
            add_bk_option( 'booking_auto_cancel_pending_bookings_for_approved_date','Off');
            add_bk_option( 'booking_is_use_visitors_number_for_availability','Off');
            add_bk_option( 'booking_is_show_availability_in_tooltips','Off');
            add_bk_option( 'booking_highlight_availability_word' , __('Available: ' ,'booking')  );
            add_bk_option( 'booking_availability_based_on' ,  'items' );
            add_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources', 'Off' );

            add_bk_option( 'booking_search_form_show' , str_replace( '\\n\\r', "\n", $this->get_default_booking_search_form_show('inline') ) );     //FixIn:6.1.0.1
            add_bk_option( 'booking_found_search_item', str_replace( '\\n\\r', "\n", $this->get_default_booking_found_search_item( ) ) );           //FixIn:6.1.0.1
            
            
            add_bk_option( 'booking_cache_expiration', '2d');

            $this->regenerate_booking_search_cache();

            if ( wpdev_bk_is_this_demo() )
                update_bk_option( 'booking_form', str_replace('\\n\\','', $this->reset_to_default_form('payment') ) );

            global $wpdb;
            $charset_collate = '';
            //if ( $wpdb->has_cap( 'collation' ) ) {
                        if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                        if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
            //}

           if  (wpbc_is_field_in_table_exists('bookingtypes','prioritet') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD prioritet INT(4) DEFAULT '0'";
                $wpdb->query( $simple_sql );
           }
           if  (wpbc_is_field_in_table_exists('bookingtypes','parent') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD parent bigint(20) DEFAULT '0'";
                $wpdb->query( $simple_sql );
           }
           if  (wpbc_is_field_in_table_exists('bookingtypes','visitors') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD visitors bigint(20) DEFAULT '1'";
                $wpdb->query( $simple_sql );
           }
           if  (wpbc_is_field_in_table_exists('bookingdates','type_id') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingdates ADD type_id bigint(20)";
                $wpdb->query( $simple_sql );
           }
// FixIn:5.4.1           
//            if  (wpbc_is_index_in_table_exists('bookingdates','booking_id_dates_types') == 0) {
//                $simple_sql = "CREATE UNIQUE INDEX booking_id_dates_types ON {$wpdb->prefix}bookingdates (booking_id, booking_date, type_id);";
//                $wpdb->query( $simple_sql );
//            }

            // Booking Types   M E T A  table
            if ( ( ! wpbc_is_table_exists('booking_coupons')  )) { // Cehck if tables not exist yet

                    $wp_queries=array();
                    $wp_queries[] = "CREATE TABLE {$wpdb->prefix}booking_coupons (
                         coupon_id bigint(20) unsigned NOT NULL auto_increment,
                         coupon_active int(10) NOT NULL default 1,
                         coupon_code varchar(200) NOT NULL default '',
                         coupon_value FLOAT(7,2) NOT NULL DEFAULT 0.00,
                         coupon_type varchar(200) NOT NULL default '',
                         expiration_date datetime,
                         coupon_min_sum FLOAT(7,2) NOT NULL DEFAULT 0.00,
                         support_bk_types text ,
                         PRIMARY KEY  (coupon_id)
                        ) $charset_collate;";

                    foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );
            }



        // Demo settings
        if ( wpdev_bk_is_this_demo() )          {

                update_bk_option( 'booking_is_show_availability_in_tooltips' , 'On' );

                update_bk_option( 'booking_skin',  '/inc/skins/premium-marine.css');

                update_bk_option( 'booking_type_of_day_selections' , 'multiple' );

                update_bk_option( 'booking_range_selection_type', 'dynamic');
                update_bk_option( 'booking_range_selection_days_count','7');
                update_bk_option( 'booking_range_selection_days_max_count_dynamic',30);
                update_bk_option( 'booking_range_selection_days_specific_num_dynamic','');
                update_bk_option( 'booking_range_start_day' , '-1' );
                update_bk_option( 'booking_range_selection_days_count_dynamic','1');
                update_bk_option( 'booking_range_start_day_dynamic' , '-1' );
                update_bk_option( 'booking_range_selection_time_is_active', 'Off');
                update_bk_option( 'booking_range_selection_start_time','14:00');
                update_bk_option( 'booking_range_selection_end_time','12:00');/**/
                update_bk_option( 'booking_is_show_legend' , 'Off' );

                update_bk_option( 'booking_is_use_visitors_number_for_availability','On');
                //update_bk_option( 'booking_availability_based_on' ,  'visitors' );
                
                update_bk_option( 'booking_is_show_cost_in_tooltips',  'On');
                update_bk_option( 'booking_is_show_cost_in_date_cell',  'Off');                                
                

                 update_bk_option( 'booking_form_show',  '<div class="payment-content-form"> 
<strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/> 
<strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/> 
<strong>Email</strong>:<span class="fieldvalue">[email]</span><br/> 
<strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/> 
<strong>Address</strong>:<span class="fieldvalue">[address]</span><br/> 
<strong>City</strong>:<span class="fieldvalue">[city]</span><br/> 
<strong>Post code</strong>:<span class="fieldvalue">[postcode]</span><br/> 
<strong>Country</strong>:<span class="fieldvalue">[country]</span><br/> 
<strong>Visitors</strong>:<span class="fieldvalue"> [visitors]</span><br/> 
<strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span><br/> 
<strong>Coupon</strong>:<br /><span class="fieldvalue"> [coupon]</span>
</div>' );

                 
                update_bk_option( 'booking_search_form_show' , str_replace( '\\n\\r', "\n", $this->get_default_booking_search_form_show('advanced') ) );    //FixIn:6.1.0.1
                update_bk_option( 'booking_found_search_item', str_replace( '\\n\\r', "\n", $this->get_default_booking_found_search_item('advanced') ) );   //FixIn:6.1.0.1
                 

                $wp_queries = array();
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Standard' ,'booking'), __('Default' ,'booking') );
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Superior' ,'booking'), __('Resource #1' ,'booking') );
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Presidential Suite' ,'booking'), __('Resource #2' ,'booking') );
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;" , __('Royal Villa' ,'booking'), __('Resource #3' ,'booking') );


                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '2' WHERE title = %s ;" , __('Standard' ,'booking') );
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '3' WHERE title = %s ;" , __('Superior' ,'booking') );
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '150', visitors = '4' WHERE title = %s ;" , __('Presidential Suite' ,'booking') );
                $wp_queries[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '500', visitors = '5' WHERE title = %s ;" , __('Royal Villa' ,'booking') );

                $wp_queries[] = 'DELETE FROM '.$wpdb->prefix .'booking_types_meta ; ';

                $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 4, "rates", "a:3:{s:6:\"filter\";a:3:{i:3;s:3:\"Off\";i:2;s:3:\"Off\";i:1;s:2:\"On\";}s:4:\"rate\";a:3:{i:3;s:1:\"0\";i:2;s:1:\"0\";i:1;s:3:\"200\";}s:9:\"rate_type\";a:3:{i:3;s:1:\"%\";i:2;s:1:\"%\";i:1;s:1:\"%\";}}" );';
                $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 3, "costs_depends", "a:3:{i:0;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\">\";s:4:\"from\";s:1:\"1\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:3:\"250\";s:13:\"cost_apply_to\";N;}i:1;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\"=\";s:4:\"from\";s:1:\"3\";s:2:\"to\";s:1:\"3\";s:4:\"cost\";s:3:\"200\";s:13:\"cost_apply_to\";s:5:\"fixed\";}i:2;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:4:\"summ\";s:4:\"from\";s:1:\"4\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:3:\"875\";s:13:\"cost_apply_to\";s:5:\"fixed\";}}" );';

                foreach ($wp_queries as $wp_q) $wpdb->query($wp_q);
        }




         // Insert Default child objects

         $my_sql = array();

         $child_resources = $wpdb->get_results( "SELECT booking_type_id FROM {$wpdb->prefix}bookingtypes  WHERE parent = 1" );
         $child_1 = $wpdb->get_results( "SELECT title FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = 1" );

         if ( (count($child_resources)==0) && (count($child_1)>0)  )
            for ($i = 1; $i < 6; $i++)
                $my_sql[] = 'INSERT INTO '.$wpdb->prefix .'bookingtypes ( title, parent, cost, prioritet ) VALUES ( "'.
                                                              $child_1[0]->title .'-'.$i  .'", "1", "25", "'.$i.'") ' ;


         $child_resources = $wpdb->get_results( "SELECT booking_type_id FROM {$wpdb->prefix}bookingtypes  WHERE parent = 2" );
         $child_2 = $wpdb->get_results( "SELECT title FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = 2" );

         if ( (count($child_resources)==0) && (count($child_2)>0)  ){
            for ($i = 1; $i < 4; $i++)
                $my_sql[] = 'INSERT INTO '.$wpdb->prefix .'bookingtypes ( title, parent, cost, prioritet ) VALUES ( "'.
                                                              $child_2[0]->title .'-'.$i  .'", "2", "50", "'.$i.'") ' ;
            if ( $child_2[0]->title == __('Superior' ,'booking') )
                $my_sql[] = $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '50' WHERE title = %s ", __('Superior' ,'booking') );

         }

         foreach ($my_sql as $wp_q)
            if ( false === $wpdb->query( $wp_q ) ) { bk_error('Error during updating to DB booking resources',__FILE__,__LINE__ ); }


         // Set default number of support visitors at child objects for demo site
         if ( wpdev_bk_is_this_demo() )         {
                $wp_queries = array();
                $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '2' WHERE title LIKE '". __('Standard' ,'booking') ."-%' ;";
                $wp_queries[] = "UPDATE {$wpdb->prefix}bookingtypes SET visitors = '3' WHERE title LIKE '". __('Superior' ,'booking') ."-%' ;";
                foreach ($wp_queries as $wp_q) $wpdb->query($wp_q);
          }

          
            $booking_version_num = get_option( 'booking_version_num');        
            if ($booking_version_num === false ) $booking_version_num = '0';
            if ( version_compare('5.4.3', $booking_version_num) > 0 ){  //Update,  if we have version 5.4.2 or loweer
                // Update all coupons usage number
                $wp_query = "UPDATE {$wpdb->prefix}booking_coupons SET coupon_active = 1000000 WHERE coupon_active = 1";          
                $wpdb->query( $wp_query );                
            }                    
    }

    //Decativate
    function pro_deactivate(){
        global $wpdb;
       // delete_bk_option( 'booking_maximum_selection_days_for_one_resource');
            delete_bk_option( 'booking_check_out_available_for_parents' );
            delete_bk_option( 'booking_check_in_available_for_parents' );

            delete_bk_option( 'booking_is_show_pending_days_as_available');
            delete_bk_option( 'booking_auto_cancel_pending_bookings_for_approved_date');
            delete_bk_option( 'booking_is_use_visitors_number_for_availability');
            delete_bk_option( 'booking_is_show_availability_in_tooltips');
            delete_bk_option( 'booking_highlight_availability_word');
            delete_bk_option( 'booking_availability_based_on'  );
            delete_bk_option( 'booking_is_dissbale_booking_for_different_sub_resources'  );

            delete_bk_option( 'booking_search_form_show' );
            delete_bk_option( 'booking_found_search_item' );
            delete_bk_option( 'booking_cache_expiration');

            delete_bk_option( 'booking_cache_content'  );
            delete_bk_option( 'booking_cache_created'  );

            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}booking_coupons" );
   }

// </editor-fold>

}
