<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
require_once(WPDEV_BK_PLUGIN_DIR. '/inc/lib_s.php' );
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/index.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/index.php' ); }
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/biz_m.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/biz_m.php' ); }


class wpdev_bk_biz_s {

    var $wpdev_bk_biz_m;

    // Constructor
    function __construct() {

        add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_menu_content'));
        add_bk_action('wpdev_booking_settings_top_menu_submenu_line', array(&$this, 'wpdev_booking_settings_top_menu_submenu_line'));



        add_filter('wpdev_booking_form', array(&$this, 'add_paypal_form'));                     // Filter for inserting paypal form
        
        add_action('wpdev_new_booking', array(&$this, 'show_paypal_form_in_ajax_request'),1,5); // Make showing Paypal in Ajax
        add_action('wpbc_update_cost_of_new_booking', array(&$this, 'wpbc_update_cost_of_new_booking'),1,5); // Make showing Paypal in Ajax


        add_action('settings_advanced_set_time_format', array(&$this, 'settings_advanced_set_time_format'));    // Write General Settings
        add_action('settings_advanced_set_range_selections', array(&$this, 'settings_advanced_set_range_selections'));    // Write General Settings
        add_action('settings_advanced_set_fixed_time', array(&$this, 'settings_advanced_set_fixed_time'));    // Write General Settings
        add_action('settings_set_show_time_in_tooltips', array(&$this, 'settings_set_show_time_in_tooltips'));    // Write General Settings
       

        add_bk_action('wpdev_bk_general_settings_cost_section', array(&$this, 'wpdev_bk_general_settings_cost_section'));          // Section of settings in general settings page
        add_bk_action('wpdev_bk_general_settings_pending_auto_cancelation', array(&$this, 'wpdev_bk_general_settings_pending_auto_cancelation'));          // Section of settings in general settings page


        // Resources settings //
        add_bk_action('resources_settings_table_headers', array($this, 'resources_settings_table_headers'));
        add_bk_action('resources_settings_table_footers', array($this, 'resources_settings_table_footers'));
        add_bk_action('resources_settings_table_collumns', array($this, 'resources_settings_table_collumns'));
        add_bk_filter('get_sql_4_update_bk_resources_cost', array(&$this, 'get_sql_4_update_bk_resources'));

        add_bk_filter('get_bk_currency_format', array(&$this, 'get_bk_currency_format'));


        add_action('wpbc_define_js_vars', array(&$this, 'wpbc_define_js_vars') );
        add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
        add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );

        
        add_filter('wpdev_booking_form_content', array(&$this, 'wpdev_booking_form_content'),10,2 );


        add_filter('wpdev_get_booking_cost', array(&$this, 'get_booking_cost'),10,4 );
        add_bk_filter('wpdev_get_bk_booking_cost', array(&$this, 'get_booking_cost'));

        add_filter('wpdev_booking_get_additional_info_to_dates', array(&$this, 'wpdev_booking_get_additional_info_to_dates'),10,2 );



        add_bk_action('wpdev_booking_activation', array(&$this, 'pro_activate'));
        add_bk_action('wpdev_booking_deactivation', array(&$this, 'pro_deactivate'));


        add_bk_action('wpdev_booking_post_inserted', array(&$this, 'booking_post_inserted'));
        add_bk_filter('get_booking_cost_from_db', array(&$this, 'get_booking_cost_from_db'));
        add_bk_filter('wpdev_get_payment_form', array(&$this, 'get_payment_form') );
        add_bk_filter('get_currency_info', array(&$this, 'get_currency_info') );



        add_bk_action('wpdev_show_autofill_button', array(&$this, 'wpdev_show_autofill_button'));          // Ajax POST request for updating remark

        add_bk_action('write_content_for_popups', array(&$this, 'premium_content_for_popups'));
        add_bk_action('wpdev_booking_emails_settings', array(&$this, 'wpdev_booking_emails_settings'));

        add_bk_action('wpdev_save_bk_cost', array(&$this, 'wpdev_save_bk_cost'));          // Ajax POST request for updating cost
        add_bk_action('wpdev_send_payment_request', array(&$this, 'wpdev_send_payment_request'));          // Ajax POST request for email sending payment request
        add_bk_action('wpdev_change_payment_status', array(&$this, 'wpdev_change_payment_status'));          // Ajax POST request for email sending payment request


        add_bk_action('check_pending_not_paid_auto_cancell_bookings', array(&$this, 'check_pending_not_paid_auto_cancell_bookings'));          //Check and delete all Pending not paid bookings, which older then a 1-n days


        add_bk_filter('get_sql_4_insert_bk_resources_fields_p', array(&$this, 'get_sql_4_insert_bk_resources_fields'));
        add_bk_filter('get_sql_4_insert_bk_resources_values_p', array(&$this, 'get_sql_4_insert_bk_resources_values'));



         if ( class_exists('wpdev_bk_biz_m')) {
                $this->wpdev_bk_biz_m = new wpdev_bk_biz_m();
        } else { $this->wpdev_bk_biz_m = false; } 

    }


 //   S U P P O R T     F U N C T I O N S    //////////////////////////////////////////////////////////////////////////////////////////////////


            // Reset to Payment form
            function reset_to_default_form($form_type ){
                    return '[calendar] \n\
<div class="payment-form"> \n\
 <p>'.__('Select Times' ,'booking').':<br />[select rangetime "10:00 AM - 12:00 PM@@10:00 - 12:00" "12:00 PM - 02:00 PM@@12:00 - 14:00" "02:00 PM - 04:00 PM@@14:00 - 16:00" "04:00 PM - 06:00 PM@@16:00 - 18:00" "06:00 PM - 08:00 PM@@18:00 - 20:00"]</p>\n\
 <p>'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p>'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p>'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'.__('Phone' ,'booking').':<br />[text phone] </p> \n\
 <p>'.__('Address (required)' ,'booking').':<br />  [text* address] </p> \n\  
 <p>'.__('City (required)' ,'booking').':<br />  [text* city] </p> \n\
 <p>'.__('Post code (required)' ,'booking').':<br />  [text* postcode] </p> \n\  
 <p>'.__('Country (required)' ,'booking').':<br />  [country] </p> \n\
 <p>'.__('Adults' ,'booking').':  [select visitors class:span1 "1" "2" "3" "4"] '.__('Children' ,'booking').': [select children class:span1 "0" "1" "2" "3"]</p> \n\
 <p>'.__('Details' ,'booking').':<br /> [textarea details] </p> \n\
 <p>[checkbox* term_and_condition use_label_element "'.__('I Accept term and conditions' ,'booking').'"] </p> \n\
 <p>[captcha]</p> \n\
 <p>[submit class:btn "'.__('Send' ,'booking').'"]</p> \n\
</div>';
             }



    // Get booking types from DB
    function get_booking_type($booking_id) {
        global $wpdb;
        $types_list = $wpdb->get_results($wpdb->prepare( "SELECT title, cost FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d" , $booking_id ));
        return $types_list;
    }

    // Get cost of booking resource
    function get_cost_of_booking_resource($bk_type_id) {
        global $wpdb;
        $cost = $wpdb->get_var($wpdb->prepare( "SELECT cost FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d" , $bk_type_id ));
        return (isset( $cost) ) ? $cost : 0 ;
    }

    // Get booking types from DB
    function get_booking_types() {
        global $wpdb;

        if ( class_exists('wpdev_bk_biz_l')) {  // If Business Large then get resources from that
            $types_list = apply_bk_filter('get_booking_types_hierarhy_linear',array() );
            for ($i = 0; $i < count($types_list); $i++) {
                $types_list[$i]['obj']->count = $types_list[$i]['count'];
                $types_list[$i] = $types_list[$i]['obj'];
                //if ( ($booking_type_id != 0) && ($booking_type_id == $types_list[$i]->booking_type_id ) ) return $types_list[$i];
            }
            //if ($booking_type_id == 0)

        } else $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, cost FROM {$wpdb->prefix}bookingtypes  ORDER BY title" );

        $types_list = apply_bk_filter('multiuser_resource_list', $types_list);
        return $types_list;
    }

    


    // Get currency description
    function get_currency_info( $payment_system = 'paypal'){

        if ($payment_system == 'paypal')
             $cost_currency = get_bk_option( 'booking_paypal_curency' );
        elseif ($payment_system == 'sage')
             $cost_currency = get_bk_option( 'booking_sage_curency' );
        elseif ($payment_system == 'ipay88')
             $cost_currency = get_bk_option( 'booking_ipay88_curency' );
        elseif ($payment_system == 'authorizenet')
             $cost_currency = get_bk_option( 'booking_authorizenet_curency' );
        else $cost_currency = get_bk_option( 'booking_paypal_curency' );

        if ($cost_currency == 'USD' ) $cost_currency = '$';
        elseif ($cost_currency == 'EUR' ) $cost_currency = '&euro;';
        elseif ($cost_currency == 'GBP' ) $cost_currency = '&#163;';
        elseif ($cost_currency == 'JPY' ) $cost_currency = '&#165;';
        else  $cost_currency = ' ' . $cost_currency . ' ';

        return $cost_currency;
    }



    // Get Fields and Values for Insert new resource
    function get_sql_4_insert_bk_resources_fields( $blank ){
      return ', cost ';
    }
    function get_sql_4_insert_bk_resources_values( $blank , $sufix ){
        $cost = 0;

        if (isset($_POST['type_parent_new'])){
           $cost = $this->get_booking_type( $_POST['type_parent_new'] ) ; // Get cost of parent element
          if (count($cost)>0) $cost = $cost[0]->cost;
          else                $cost = '0';
          if ( empty($cost) )                   // Recheck  if the cost  just empty "" space its cangenerate error.
              $cost = '0';
        }

        $update_values =  ' , '. $cost . ' ';

        return  $update_values;
    }

 //  C O S T    I n s e r t i n g    ///////////////////////////////////////////////////////////////////////////////////////

    //  Update C O S T    ---  Function call after booking is inserted or modificated in post request
    function booking_post_inserted($booking_id, $booking_type, $booking_days_count, $times_array, $post_form = false){
           global $wpdb;

           if ($post_form === false) {
               $post_form = escape_any_xss($_POST["form"]);
           }
           // Check if total cost field exist and get cost from that field
           $fin_summ = apply_bk_filter('check_if_cost_exist_in_field', false, $post_form, $booking_type );

           if ($fin_summ == false)
                $summ = $this->get_booking_cost( $booking_type, $booking_days_count, $times_array , $post_form );
           else $summ = $fin_summ;

           $summ = str_replace(' ', '', $summ);
           $summ = floatval(  $summ);
           $summ = round($summ,2);

            $update_sql =  $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.cost = %f WHERE bk.booking_id = %d ", $summ, $booking_id );
            if ( false === $wpdb->query( $update_sql  ) ){
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating cost in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                die();
            }/**/

    }


    //  Update C O S T
    function update_booking_cost($booking_id, $cost){
           global $wpdb;

           $summ = floatval($cost);
           $summ = round($summ,2);

            $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.cost=%f WHERE bk.booking_id= %d ", $summ, $booking_id );
            if ( false === $wpdb->query( $update_sql ) ){
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating cost in BD',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                die();
            }/**/

    }



    // Get Cost from DB
    function get_booking_resorce_cost($resource_id) {
        global $wpdb;
        $slct_sql = $wpdb->prepare( "SELECT cost FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d", $resource_id );
        $slct_sql_results  = $wpdb->get_results( $slct_sql );
        if ( count($slct_sql_results) > 0 ) { 
            return $slct_sql_results[0]->cost;                 
        } else {
            return '';
        }
    }


    // Get Cost from DB
    function get_booking_cost_from_db($booking_cost, $booking_id) {
        global $wpdb;
        $slct_sql = $wpdb->prepare( "SELECT cost FROM {$wpdb->prefix}booking WHERE booking_id = %d LIMIT 0,1", $booking_id );
        $slct_sql_results  = $wpdb->get_results( $slct_sql );
        if ( count($slct_sql_results) > 0 ) { return $slct_sql_results[0]->cost; }
        return '';
    }

    // Check and delete all Pending not paid bookings, which older then a 1-n days
    function check_pending_not_paid_auto_cancell_bookings($bk_type) {

            if ( defined('WP_ADMIN') ) if ( WP_ADMIN === true )  return;
            $is_check_active   =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );   // Is this function Active
            if ($is_check_active != 'On') return;

            global $wpdb;
            $num_of_hours_ago  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );        // Num of hours ago for specific booking

            // TODO: add here in a future possibility to cancel not ALL, but specific bookings with booking payment type: Error or Failed
            // Right now all bookings, which  have no successfully payed status or pending are canceled.
            $labels_payment_status_ok = get_payment_status_ok();
            $labels_payment_status_ok = implode( "', '" , $labels_payment_status_ok);           
            $labels_payment_status_ok = "'" . $labels_payment_status_ok;

            $labels_payment_status_pending = get_payment_status_pending();
            $labels_payment_status_pending = implode( "', '", $labels_payment_status_pending);
            $labels_payment_status_ok .= "', '" . $labels_payment_status_pending . "'";

            // Cancell only Pending, Old (hours) and not Paid bookings
            $slct_sql = $wpdb->prepare("SELECT DISTINCT bk.booking_id as id, bk.modification_date as date,  dt.approved AS approved, bk.pay_status AS pay_status
                         FROM {$wpdb->prefix}booking AS bk

                         INNER JOIN {$wpdb->prefix}bookingdates as dt
                         ON    bk.booking_id = dt.booking_id

                          WHERE bk.pay_status NOT IN ( {$labels_payment_status_ok} ) AND
                                dt.approved=0 AND
                                bk.modification_date < ( NOW() - INTERVAL %d HOUR ) ", $num_of_hours_ago );

            $pending_not_paid  = $wpdb->get_results( $slct_sql );
            $approved_id = array();
            foreach ($pending_not_paid as $value) {
               $approved_id []= $value->id;
            }
            $approved_id_str = join( ',', $approved_id);

            if ( count($approved_id)>0 ) {

                // Send decline emails
                $auto_cancel_pending_unpaid_bk_is_send_email =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
                if ($auto_cancel_pending_unpaid_bk_is_send_email == 'On') {
                    $auto_cancel_pending_unpaid_bk_email_reason  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );
                    foreach ($approved_id as $booking_id) {
                        sendDeclineEmails($booking_id,1, $auto_cancel_pending_unpaid_bk_email_reason );
                    }
                }

                // Auto cancellation
                if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id IN ({$approved_id_str})" ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error during auto deleting dates at DB of pending bookings'; ?></div>'; </script> <?php
                    die();
                }
                if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id IN ({$approved_id_str})" ) ){
                    ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bk_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo 'Error auto deleting booking at DB of pending bookings' ; ?></div>'; </script> <?php
                    die();
                }
            }

    }


 //  R E S O U R C E     T A B L E     C O S T    C o l l  u m n    ////////////////////////////////////////////////////////////////////////////

       // Show headers collumns
       function resources_settings_table_headers(){

          ?>
            <th style="width:80px;text-align:center;" rel="tooltip" class="tooltip_bottom"  title="<?php _e('Setting cost for the resource' ,'booking');?>">
             <?php  _e('Cost' ,'booking'); ?>
                    <?php echo ' <span style="font-weight:bold;color:#8F340E;">'; ?>
                      <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day')    _e('/ day' ,'booking');    ?>
                      <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night')  _e('/ night' ,'booking');  ?>
                      <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed')  _e('fixed' ,'booking');?>
                      <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour')   _e('/ hour' ,'booking');   ?>
                    <?php echo "</span>"; ?>
            </th>
          <?php
       }

       // Show footers collumns
       function resources_settings_table_footers(){
//                if ((isset($_POST['submit_resources']))) {
//                    update_bk_option( 'booking_paypal_price_period' , $_POST['paypal_price_period'] );
//                }
          ?>
            <td>&nbsp;<?php /*
                <label for="paypal_price_period"><?php _e('Setting cost' ,'booking'); ?>:</label> 
                <select id="paypal_price_period" name="paypal_price_period" style="width:75px;">
                    <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day') echo "selected"; ?> value="day"><?php _e('per day' ,'booking'); ?></option>
                    <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night') echo "selected"; ?> value="night"><?php _e('per night' ,'booking'); ?></option>
                    <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed') echo "selected"; ?> value="fixed"><?php _e('fixed' ,'booking'); ?></option>                        
                    <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour') echo "selected"; ?> value="hour"><?php _e('per hour' ,'booking'); ?></option>                        
                </select>  <?php /**/?>                   
            </td>
          <?php
       }


       // Show Resources Collumns
       function resources_settings_table_collumns( $bt, $all_id, $alternative_color, $advanced_params = array() ){
       // Show Costs  ?>
                <td style="text-align:center;<?php if ($bt->cost<=0) { echo 'color:#ccc;'; } ?>" <?php echo $alternative_color; ?> >
                    <legend class="wpbc_mobile_legend"><?php _e('Cost' ,'booking'); ?>:</legend>
                    <?php echo ' <span style="font-weight:normal;">';  echo $this->get_currency_info(); echo "</span>"; ?>
                    <input  maxlength="17" type="text"
                                    style="width:50px;<?php if ($bt->cost<=0) { echo 'border-color:#C1BDBD;color:#ccc;'; } ?>"
                                    value="<?php echo $bt->cost; ?>"
                                    name="resource_cost<?php echo $bt->id; ?>" id="resource_cost<?php echo $bt->id; ?>" />
                </td>
            <?php
       }

                // Update SQL dfor editing bk resources
                function get_sql_4_update_bk_resources($blank, $bt){
                    global $wpdb;    
                    $sql_res = $wpdb->prepare( " , cost = %s ", $_POST['resource_cost'.$bt->id] );
                    return $sql_res;
                }


 //   C L I E N T     S I D E    //////////////////////////////////////////////////////////////////////////////////////////////////////////////
 
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){ 
        
        $specific_selected_dates = get_bk_option( 'booking_range_selection_days_specific_num_dynamic');
        $js_specific_selected_dates = rangeNumListToCommaNumList( $specific_selected_dates );
        $booking_highlight_timeslot_word = get_bk_option( 'booking_highlight_timeslot_word');
        $booking_highlight_timeslot_word =  apply_bk_filter('wpdev_check_for_active_language', $booking_highlight_timeslot_word );
        if (empty($booking_highlight_timeslot_word)) $booking_highlight_timeslot_word = '';
        
        wp_localize_script('wpbc-global-vars', 'wpbc_global3', array(              
             'bk_1click_mode_days_num' => intval( get_bk_option('booking_range_selection_days_count') )            /* Number of days selection with 1 mouse click */
             ,'bk_1click_mode_days_start' => '['. get_bk_option('booking_range_start_day') .']'                     /* { -1 - Any | 0 - Su,  1 - Mo,  2 - Tu, 3 - We, 4 - Th, 5 - Fr, 6 - Sat } */
             ,'bk_2clicks_mode_days_min' => intval( get_bk_option('booking_range_selection_days_count_dynamic') )   /* Min. Number of days selection with 2 mouse clicks */
             ,'bk_2clicks_mode_days_max' => intval( get_bk_option('booking_range_selection_days_max_count_dynamic'))/* Max. Number of days selection with 2 mouse clicks */
             ,'bk_2clicks_mode_days_specific' => '['. $js_specific_selected_dates . ']'                             /* Exmaple [5,7] */
             ,'bk_2clicks_mode_days_start' => '[' . get_bk_option('booking_range_start_day_dynamic') . ']'          /* { -1 - Any | 0 - Su,  1 - Mo,  2 - Tu, 3 - We, 4 - Th, 5 - Fr, 6 - Sat } */
             ,'message_starttime_error' => esc_js(__('Start Time is invalid. The date or time may be booked, or already in the past! Please choose another date or time.' ,'booking') ) 
             ,'message_endtime_error' => esc_js(__('End Time is invalid. The date or time may be booked, or already in the past. The End Time may also be earlier that the start time, if only 1 day was selected! Please choose another date or time.' ,'booking') ) 
             ,'message_rangetime_error' => esc_js(__('The time(s) may be booked, or already in the past!' ,'booking') )
             ,'message_durationtime_error' => esc_js(__('The time(s) may be booked, or already in the past!' ,'booking') ) 
             ,'bk_highlight_timeslot_word' => esc_js( $booking_highlight_timeslot_word ) 
             ,'is_booking_recurrent_time' => ( ( get_bk_option( 'booking_recurrent_time' ) !== 'On')?'false':'true' )         
             ,'is_booking_used_check_in_out_time' => ( ( get_bk_option( 'booking_range_selection_time_is_active' ) !== 'On')?'false':'true' ) 
             ,'bk_show_info_in_form' => ( ( (!defined('WP_BK_SHOW_INFO_IN_FORM')) || (! WP_BK_SHOW_INFO_IN_FORM) )?'false':'true' )                        
        ) );        
    }    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){ 
        wp_enqueue_script( 'wpbc-bs', WPDEV_BK_PLUGIN_URL . '/inc/js/biz_s'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){ 
        
    }


    //    A d d    E l e m e n t s     t o     B o o k  i n g     F o r m   //
        // Add Paypal place for inserting to the Booking FORM ////////////////////
        function add_paypal_form($form_content) {

            $is_turned_off = apply_bk_filter('is_payment_forms_off', true);
            if ($is_turned_off)  return $form_content ;

            if (strpos($_SERVER['REQUEST_URI'],'booking.php')!==false) return $form_content ;

            $str_start = strpos($form_content, 'booking_form');
            $str_fin = strpos($form_content, '"', $str_start);

            $my_boook_type = substr($form_content,$str_start, ($str_fin-$str_start) );

            $form_content .= '<div  id="paypal'.$my_boook_type.'"></div>';
            return $form_content;
        }


        // Add  F I X E D   R a n g e    T I M E   to   Form        //////////////
        function wpdev_booking_form_content ($my_form_content, $bk_type){
            if( get_bk_option( 'booking_range_selection_time_is_active') == 'On' )  {
                if ( strpos($my_form_content, 'name="starttime') !== false )  $my_form_content = str_replace( 'name="starttime', 'name="advanced_stime', $my_form_content);
                if ( strpos($my_form_content, 'name="endtime') !== false )  $my_form_content = str_replace( 'name="endtime', 'name="advanced_etime', $my_form_content);

                $my_form_content .= '<input name="starttime'.$bk_type.'"  id="starttime'.$bk_type.'" type="text" value="'.get_bk_option( 'booking_range_selection_start_time').'" style="display:none;">';
                $my_form_content .= '<input name="endtime'.$bk_type.'"  id="endtime'.$bk_type.'" type="text" value="'.get_bk_option( 'booking_range_selection_end_time').'"  style="display:none;">';
            }
            return $my_form_content;
        }


    // A D V A N C E D     I N F O      I N T O      F O R M   ///////////////////////////////////////////////////
    function wpdev_booking_get_additional_info_to_dates($blank, $type_id ) { 

        if ( (!defined('WP_BK_SHOW_INFO_IN_FORM')) || (! WP_BK_SHOW_INFO_IN_FORM) ) 
            return '';


        // TODO: stop working here according names in tooltips
        global $wpdb;
        $start_script_code = '';

         $sql_req =  $wpdb->prepare( "SELECT DISTINCT dt.booking_date, bk.*
                      FROM {$wpdb->prefix}bookingdates as dt
                      INNER JOIN {$wpdb->prefix}booking as bk
                     ON    bk.booking_id = dt.booking_id
                     WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type = %d
                     ORDER BY dt.booking_date", $type_id ) ;
         $results = $wpdb->get_results( $sql_req );
//debuge($results)     ;
         $return_array = array();
         foreach ($results as $value) {


             if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $type_id );
             else $bk_title = '';

             $form_data = get_form_content($value->form, $type_id, '', array('booking_id'=> $value->booking_id ,
                                                                          'resource_title'=> $bk_title ) );

             $single_day_info =array();
             foreach ($form_data['_all_'] as $kkey => $vvalue) {
                 $kkey = substr($kkey, 0 , -1*strlen( $type_id . '' ));
                 $single_day_info[$kkey] = $vvalue;
             }
             //$return_array[$value->booking_date] = $single_day_info;
             //$return_array[$value->booking_date]['id'] = $value->booking_id;
             //$return_array[$value->booking_date]['cost'] = $value->cost ;


             $key_a = explode(' ', $value->booking_date);
             $date_key =  $key_a[0];
             if (isset($return_array[$date_key .':' .$value->booking_id ]['id']))
                     $return_array[$date_key .':' .$value->booking_id ]['dates'] .= $value->booking_date . ',';
             else {
                 $return_array[$date_key .':' .$value->booking_id ] = $single_day_info;
                 $return_array[$date_key .':' .$value->booking_id ]['id'] = $value->booking_id;
                 $return_array[$date_key .':' .$value->booking_id ]['cost'] = $value->cost ;
                 $return_array[$date_key .':' .$value->booking_id ]['dates'] = $value->booking_date . ',';
             }

             $my_time_tag = explode(':', $key_a[1]);
             if ($my_time_tag[2] == '01') $return_array[$date_key .':' .$value->booking_id ]['starttime'] = $my_time_tag[0] . ':' . $my_time_tag[1];
             if ($my_time_tag[2] == '02') $return_array[$date_key .':' .$value->booking_id ]['endtime'] = $my_time_tag[0] . ':' . $my_time_tag[1];

         }
//debuge($return_array)     ;die;
         $start_script_code .= " dates_additional_info[". $type_id ."] = []; ";

         foreach ($return_array as $key=>$value) {
             $key_a = explode(':', $key);
             $date_key = explode('-', $key_a[0]);
             //$my_time_tag = explode(':', $key_a[1]);

             $my_day_tag =   ($date_key[1]+0)."-".($date_key[2]+0)."-".($date_key[0]);

             $start_script_code .= " if ( dates_additional_info[". $type_id ."]['".$my_day_tag."'] == undefined ) ";
                   $start_script_code .= "dates_additional_info[". $type_id ."]['".$my_day_tag."'] = [];   ";

             $start_script_code .= " numbb = dates_additional_info[". $type_id ."]['".$my_day_tag."'].length; ";
             $start_script_code .= " dates_additional_info[". $type_id ."]['".$my_day_tag."'][ numbb ] = [] ;   ";

             //$start_script_code .= " dates_additional_info[". $type_id ."]['".$my_day_tag."'][ numbb ]['time'] = '".$key_a[1]."' ;  ";
             foreach ($value as $kkey=>$vvalue) {
                 $vvalue = esc_js($vvalue);
                 $vvalue = str_replace('"', '', $vvalue);
                 $kkey = str_replace("'", '', $kkey);
                 $start_script_code .= "  dates_additional_info[". $type_id ."]['".$my_day_tag."'][ numbb ]['".$kkey."'] = '".$vvalue."' ;  ";
             }
         }

//debuge($start_script_code); die;

        return $start_script_code;
    }



//  A d m i n    p a n e l   ->   Booking   ///////////////////////////////////////////////////////////////////////


    // Save booking cost, after direct edit at admin panel from Ajax request
    function wpdev_save_bk_cost(){ global $wpdb;

           $booking_id = $_POST[ "booking_id" ];
           $cost = $_POST[ "cost" ];

           $summ = floatval(  $cost );
           $summ = round($summ,2);

           if ( $summ >= 0 ) {
               $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.cost=%f WHERE bk.booking_id= %d ", $summ, $booking_id );

                if ( false === $wpdb->query( $update_sql ) ){
                    ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during cost saving' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                    die();
                }
                if ( WP_BK_IS_SEND_EMAILS_ON_COST_CHANGE ) {                    //FixIn: 6.0.1.7
                    $booking_data = apply_bk_filter('wpbc_get_booking_data',  $booking_id);
                    sendModificationEmails($booking_id, $booking_data['type'], $booking_data['form']);
                }
                ?>
                    <script type="text/javascript">
                        document.getElementById('ajax_message').innerHTML = '<?php echo __('Cost saved successfully' ,'booking'); ?>';
                        jQuery('#ajax_message').fadeOut(3000);
                    </script>
                <?php
           } else {
                ?>
                    <script type="text/javascript">
                        document.getElementById('ajax_message').innerHTML = '<?php echo __('Cost is not correct. It must be greater than 0' ,'booking'); ?>';
                        jQuery('#ajax_message').fadeOut(5000);
                    </script>
                <?php
           }
    }



//  P a y m e n t     r e q u e s t  //HASH_EDIT  ///////////////////////////////////////////////////////////////////////

    // Show   P a y m e n t   R E Q U E  S T    request
    function premium_content_for_popups(){
        $user = wp_get_current_user(); $user_bk_id = $user->ID;                 //FixIn:5.4.5.6  
        ?><div id="sendPaymentRequestModal" class="modal" >
              <div class="modal-header">
                  <a class="close" data-dismiss="modal">&times;</a>
                  <h3><?php _e('Send payment request to customer' ,'booking'); ?></h3>
              </div>
              <div class="modal-body">
                <textarea cols="87" rows="5" id="payment_request_reason"  name="payment_request_reason"></textarea>
                <label class="help-block"><?php printf(__('Type your %sreason for payment%s request' ,'booking'),'<b>',',</b>');?></label>
              </div>
              <div class="modal-footer">
                <a href="javascript:void(0);" class="button button-primary" 
                   onclick="javascript:
                               sendPaymentRequestByEmail(payment_request_id , document.getElementById('payment_request_reason').value, <?php echo $user_bk_id; ?>, 
                               '<?php echo getBookingLocale(); ?>' );
                               jQuery('#sendPaymentRequestModal').modal('hide');"
                  ><?php _e('Send Request' ,'booking'); ?></a><?php //FixIn:5.4.5.6  -  echo $user_bk_id; ?>
                <a href="javascript:void(0)" class="button button-secondary" data-dismiss="modal"><?php _e('Close' ,'booking'); ?></a>
              </div>
            </div><?php
    }

    // P A Y M E N T    R E Q U E S T    -->  Show Paypal form in

    function get_bk_currency_format( $sum ){
        $cost_currency_format_decimal_separator   = get_bk_option( 'booking_cost_currency_format_decimal_separator'  );
        $cost_currency_format_thousands_separator = get_bk_option( 'booking_cost_currency_format_thousands_separator' );
        $cost_currency_format_decimal_number = get_bk_option( 'booking_cost_currency_format_decimal_number'  );
        if ( ! isset($cost_currency_format_decimal_number)) $cost_currency_format_decimal_number = 2;
        $cost_currency_format_decimal_number = intval($cost_currency_format_decimal_number);

        $sum = round($sum,  $cost_currency_format_decimal_number);

        $sum = number_format($sum, $cost_currency_format_decimal_number, $cost_currency_format_decimal_separator, $cost_currency_format_thousands_separator);

        return $sum;
    }

    // Payment request.
    function get_payment_form($booking_id, $booking_type ){//, $booking_days_count, $times_array , $booking_form ){

        global $wpdb;

        $bk_title    = $this->get_booking_type( $booking_type );
        $summ        = $this->get_booking_cost_from_db( '', $booking_id );

         $sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id = %d", $booking_id );
         $result_bk = $wpdb->get_results( $sql );

         if (  ( count($result_bk)>0 )  ) {

            $sdform = $result_bk[0]->form;

            $dates = get_dates_str($result_bk[0]->booking_id);
            //    $my_dates_4_send = change_date_format($dates);

            $my_d_c = explode(',', $dates);
            $my_dates_4_send = '';
            foreach ($my_d_c as $value) {

                $my_single_date = substr(trim($value),0,10);
                if( strpos($my_single_date, '-') !== false)     $my_single_date = explode('-',$my_single_date);
                else                                            $my_single_date = explode('.',$my_single_date);
                $my_dates_4_send .=  $my_single_date[2].'-'.$my_single_date[1].'-'.$my_single_date[0].  ', ' ;

            }
            $dates = substr($my_dates_4_send,0,-2) ;
            $booking_days_count = $dates;

            $start_time = trim($my_d_c[0]);
            $end_time   = trim($my_d_c[count($my_d_c)-1]);
            $start_time = substr($start_time,-8,5);
            $end_time = substr($end_time,-8,5);

         } else { return ''; }

        ///////////////////////////////////////////////////////////////////////////


        $wp_nonce = ceil( time() / ( 86400 / 2 ));

        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status='$wp_nonce' WHERE bk.booking_id= %d ", $booking_id );
        if ( false === $wpdb->query( $update_sql  ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

        //get_bk_option( 'booking_cost_currency_format_decimal_separator'  );
        //get_bk_option( 'booking_cost_currency_format_thousands_separator' );
        $cost_currency_format_decimal_number = get_bk_option( 'booking_cost_currency_format_decimal_number'  );
        if ( ! isset($cost_currency_format_decimal_number)) $cost_currency_format_decimal_number = 2;
        $cost_currency_format_decimal_number = intval($cost_currency_format_decimal_number);

        $summ = round($summ,  $cost_currency_format_decimal_number);

        if ( ($summ + 0) == 0)  $real_payment_form = '';
        else {

            $output = apply_bk_filter('wpdev_bk_define_payment_forms', '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $sdform, $wp_nonce );
            
            $original_symbols  = array ('&euro;', '&#163;', '&#165;' );
            $temporary_symbols = array ('^euro^', '^gbp^', '^jpy^' );
            $output = str_replace( $original_symbols, $temporary_symbols, $output);
            
            $output = esc_js($output);  
            $output = html_entity_decode($output);
            
            $output = str_replace( $temporary_symbols, $original_symbols, $output);

            
            $real_payment_form = '<script type="text/javascript">';
            $real_payment_form .=   'document.getElementById("booking_form_div'.$booking_type.'" ).style.display="none";';
            $real_payment_form .=   'makeScroll("#paypalbooking_form'.$booking_type.'" );';
            $real_payment_form .=   'document.getElementById("submiting'.$booking_type.'").innerHTML ="";';
            $real_payment_form .= '</script>';
            $real_payment_form .=  $output;
        }

        return $real_payment_form ;
    }

    function update_payment_request_count($booking_id, $value){
        global $wpdb;
        $value++;
        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_request= %d WHERE bk.booking_id= %d ", $value, $booking_id );
        if ( false === $wpdb->query( $update_sql ) ){
            ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_payment_request_count status in BD',__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

    }

    // Send Email request to customer for payment
    function wpdev_send_payment_request(){ global $wpdb;

        $booking_id = intval( $_POST[ "booking_id" ] );
        $reason = $_POST[ "reason" ];

        $sql = "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id = $booking_id ";
        $result_bk = $wpdb->get_results( $sql );

        if (  ( count($result_bk)>0 )  ) {

          $is_email_payment_request_adress   = get_bk_option( 'booking_is_email_payment_request_adress' );

          $reason  = htmlspecialchars( str_replace('\"','"', $reason ));
          $reason  =  str_replace("\'","'",$reason );

          foreach ($result_bk as $res) {

             if ( $is_email_payment_request_adress != 'Off') {
                 $is_send = sendPaymentRequestEmail($res->booking_id, $res->booking_type , $res->form , $reason );

                 if ( $is_send ) $this->update_payment_request_count($res->booking_id, ($res->pay_request) );
             }
          }
          ?>
             <script type="text/javascript">
                 document.getElementById('ajax_message').innerHTML = '<?php echo __('Request has been sent' ,'booking'); ?>';
                 jQuery('#ajax_message').fadeOut(3000);
             </script>
          <?php

        } else {
             ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<?php echo __('Request has failed' ,'booking'); ?>'; jQuery('#ajax_message').fadeOut(3000); </script> <?php
        }
    }

    // Chnage the status of payment
    function wpdev_change_payment_status($booking_id = '', $payment_status = '', $payment_status_show = false  ){ global $wpdb;

        if ($booking_id === '') {
            $booking_id      = $_POST[ "booking_id" ];
            $payment_status  = $_POST[ "payment_status" ];
            $payment_status_show  = $_POST[ "payment_status_show" ];
        }

        $sql =  $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id= %d ", $booking_id );
        $result_bk = $wpdb->get_results( $sql );

        if (  ( count($result_bk)>0 )  ) {

            $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status= %s WHERE bk.booking_id= %d ", $payment_status, $booking_id );
            if ( false === $wpdb->query( $update_sql  ) ){
                 ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                 die();
            }
            if ($payment_status_show !== false ) {
                ?><script type="text/javascript">
                     document.getElementById('ajax_message').innerHTML = '<?php echo __('The payment status is changed successfully' ,'booking'); ?>';
                     jQuery('#ajax_message').fadeOut(3000);
                     set_booking_row_payment_status('<?php echo $booking_id; ?>','<?php echo $payment_status; ?>','<?php echo $payment_status_show; ?>');
                  </script><?php
            }
        } else {
            if ($payment_status_show !== false ) {
                 ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<?php echo __('The changing of payment status is failed' ,'booking'); ?>'; jQuery('#ajax_message').fadeOut(3000); </script> <?php
            }
        }

    }

    // Show   S e t t i n g s   of   E m a i l - send payment request email to customer
    function wpdev_booking_emails_settings() {
        if( isset($_POST['email_payment_request_adress']) ){

             $email_payment_request_adress  = htmlspecialchars( str_replace('\"','"',$_POST['email_payment_request_adress']));
             $email_payment_request_subject = htmlspecialchars( str_replace('\"','"',$_POST['email_payment_request_subject']));
             $email_payment_request_content =  str_replace('\"','"',$_POST['email_payment_request_content']) ;

             $email_payment_request_adress      =  str_replace("\'","'",$email_payment_request_adress);
             $email_payment_request_subject     =  str_replace("\'","'",$email_payment_request_subject);
             $email_payment_request_content     =  str_replace("\'","'",$email_payment_request_content);


             if (isset( $_POST['is_email_payment_request_adress'] ))         $is_email_payment_request_adress = 'On';
             else                                                   $is_email_payment_request_adress = 'Off';
             update_bk_option( 'booking_is_email_payment_request_adress' , $is_email_payment_request_adress );

             if (isset( $_POST['is_email_payment_request_send_copy_to_admin'] ))            $is_email_payment_request_send_copy_to_admin = 'On';
             else                                               $is_email_payment_request_send_copy_to_admin = 'Off';
             update_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' , $is_email_payment_request_send_copy_to_admin );


             update_bk_option( 'booking_email_payment_request_adress' , $email_payment_request_adress );
             update_bk_option( 'booking_email_payment_request_subject' , $email_payment_request_subject );
             update_bk_option( 'booking_email_payment_request_content' , $email_payment_request_content );


        }
         $email_payment_request_adress      = get_bk_option( 'booking_email_payment_request_adress');
         $email_payment_request_subject     = get_bk_option( 'booking_email_payment_request_subject');
         $email_payment_request_content     = wpbc_nl_after_br( get_bk_option( 'booking_email_payment_request_content') );
         $is_email_payment_request_adress   = get_bk_option( 'booking_is_email_payment_request_adress' );
         $is_email_payment_request_send_copy_to_admin = get_bk_option( 'booking_is_email_payment_request_send_copy_to_admin'  );
        ?>

                <div id="visibility_container_email_payment_request" class="visibility_container" style="display:none;">

                    <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_person_with_pay_request'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                          <h3 class='hndle'><span><?php _e('Email to "Person" with payment request' ,'booking'); ?></span></h3> <div class="inside">

        <table class="form-table email-table0" >
            <tbody>
                    <tr>    
                        <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                        <td>
                            <fieldset>
                                <label for="is_email_payment_request_adress">
                                    <input id="is_email_payment_request_adress" name="is_email_payment_request_adress"   type="checkbox" 
                                           <?php if ($is_email_payment_request_adress == 'On') echo "checked"; ?>  
                                           value="<?php echo $is_email_payment_request_adress; ?>" 
                                           onchange="document.getElementById('booking_is_email_payment_request_adress_dublicated').checked=this.checked;" 
                                           />
                                    <?php _e('Active' ,'booking'); ?>
                                </label>
                            </fieldset>   
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e('Send to Admin' ,'booking'); ?>:</th>
                        <td>
                            <fieldset>
                                <label for="is_email_payment_request_send_copy_to_admin">
                                    <input id="is_email_payment_request_send_copy_to_admin"  name="is_email_payment_request_send_copy_to_admin"
                                           type="checkbox" <?php if ($is_email_payment_request_send_copy_to_admin == 'On') echo "checked"; ?>  
                                           value="<?php echo $is_email_payment_request_send_copy_to_admin; ?>"
                                           />
                                    <?php _e('Check this box to send copy of this email to Admin' ,'booking'); ?>
                                </label>
                            </fieldset>   
                        </td>
                    </tr>                                            

                <tr valign="top">
                    <th scope="row"><label for="email_payment_request_adress" ><?php _e('From' ,'booking'); ?>:</label></th>
                    <td><input id="email_payment_request_adress"  name="email_payment_request_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_payment_request_adress; ?>" />
                        <span class="description"><?php printf(__('Type the default %sadmin email%s sending the booking confimation' ,'booking'),'<b>','</b>');?></span>
                    </td>
                </tr>

                <tr valign="top">
                        <th scope="row"><label for="email_payment_request_subject" ><?php _e('Subject' ,'booking'); ?>:</label></th>
                        <td><input id="email_payment_request_subject"  name="email_payment_request_subject" class="regular-text code" type="text" size="45" value="<?php echo $email_payment_request_subject; ?>" />
                            <span class="description"><?php printf(__('Type email subject for %spayment request%s.' ,'booking'),'<b>','</b>');?></span>
                        </td>
                </tr>

                <tr valign="top">
                        <th scope="row"><label for="email_payment_request_subject" ><?php _e('Content' ,'booking'); ?>:</label></th>
                        <td>     <?php /**/
                                            wp_editor( $email_payment_request_content, 
                                               'email_payment_request_content',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'email_payment_request_content'
                                                   , 'textarea_rows' => 10
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_payment_request_content" name="email_payment_request_content" style="width:100%;" rows="10"><?php echo ($email_payment_request_content); ?></textarea> /**/ ?>
                            <p class="description"><?php printf(__('Type your %semail message for payment request%s' ,'booking'),'<b>','</b>');?></p>
                        </td>
                </tr>

                <tr valign="top">
                    <td></td>
                    <td>
                          <?php
                            $skip_shortcodes = array('moderatelink', 'denyreason' );                                
                            email_help_section($skip_shortcodes, sprintf(__('You need to make payment %s for booking %s at %s. %s You can make payment at this %s  Thank you, booking service.' ,'booking'),'[cost]','[bookingtype]','[dates]','&lt;br/&gt;&lt;br/&gt;[paymentreason]&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;', htmlentities( ' <a href="[visitorbookingpayurl]">'.__('page' ,'booking').'</a> ') . '&lt;br/&gt;&lt;br/&gt;'  ) );
                          ?>
                    </td>
                </tr>
            </tbody>
        </table>

                    </div> </div> </div>

                </div>
        <?php
    }



// P A Y M E N T    A J A X   F O R M   //////////////////////////////////////////////////////////////////////

    // Claculate the cost for specific days(times) based on base_cost for specific period
    function get_cost_for_period($period, $base_cost, $days, $times = array(array('00','00','01'), array('24','00','02')) ) {

            $fin_cost = 0 ;

            $is_time_apply_to_cost  = get_bk_option( 'booking_is_time_apply_to_cost'  );

            if ($is_time_apply_to_cost == 'On') {                           // Make some corrections if TIME IS APPLY TO THE COST
                if ($period == 'day') {
                    $period = 'hour';
                    $base_cost = $base_cost / 24 ;
                } else if ($period == 'night') {
                    $period = 'hour';
                    $base_cost = $base_cost / 24 ;
                } else if ($period == 'hour') {                             // Skip here evrything fine
                } else {                                                    // Skip here evrything fine
                }
            }

            if ($period == 'day') {

                $fin_cost = count($days) * $base_cost;

            } else if ($period == 'night') {

                $night_count = (count($days)>1) ? (count($days)-1) : 1;
                $fin_cost = $night_count * $base_cost;

            } else if ($period == 'hour') {

                $start_time = $times[0];
                $end_time   = $times[1];
                if ($end_time == array('00','00','00')) $end_time = array('24','00','00');

                if (count($days)<=1) {

                        $m_dif =  ($end_time[0] * 60 + intval($end_time[1]) ) - ($start_time[0] * 60 + intval($start_time[1]) ) ;
                        $fin_cost =   $m_dif * $base_cost / 60;

                } else {
                    $full_days_count = count($days) - 2;

                    $full_days_cost =   $full_days_count* 24 * 60 * $base_cost / 60;
                    $check_in_cost  = ( 24 * 60  - ($start_time[0] * 60 + intval($start_time[1]) ) ) * $base_cost / 60;
                    $check_out_cost = ( $end_time[0] * 60 + intval($end_time[1]) )  * $base_cost / 60;
                    $fin_cost = $check_in_cost + $full_days_cost + $check_out_cost ;
                }

            } else { // Fixed

                $fin_cost = $base_cost;
            }

            $fin_cost = round( $fin_cost ,2 );

            return  $fin_cost;
    }


    // C A L C U L A T E     C O S T     f o r      B o o k i n g
    function get_booking_cost($booking_type, $booking_days_count, $times_array, $post_form, $is_discount_calculate = true, $is_only_original_cost = false){

                $paypal_price_period    = get_bk_option( 'booking_paypal_price_period' );
                $is_time_apply_to_cost  = get_bk_option( 'booking_is_time_apply_to_cost'  );
                if ( ($is_time_apply_to_cost == 'Off') && ($paypal_price_period != 'hour') ) $times_array = array(array('00','00','01'), array('24','00','02'));

                $days_array     = explode(',', $booking_days_count);
                $days_count     = count($days_array);

                $paypal_dayprice        = $this->get_cost_of_booking_resource( $booking_type ) ;
                $paypal_dayprice_orig   = $paypal_dayprice;


                if ( ( get_bk_option( 'booking_recurrent_time' ) !== 'On') || 
                     ( ( $times_array[0][0]=='00' ) && ( $times_array[0][1]=='00' ) && ( $times_array[1][0]=='00' ) && ( $times_array[1][1]=='00' ) )
                    ) {
                    if ( ! class_exists('wpdev_bk_biz_m') ) {

                        $summ = $this->get_cost_for_period(
                                                            get_bk_option( 'booking_paypal_price_period' ),
                                                            $this->get_cost_of_booking_resource( $booking_type ) ,
                                                            $days_array,
                                                            $times_array
                            );

                    } else  {

                        $paypal_dayprice        = apply_bk_filter('wpdev_season_rates', $paypal_dayprice, $days_array, $booking_type, $times_array,$post_form);  // Its return array with day costs
//debuge($paypal_dayprice);
                        if (is_array($paypal_dayprice)) {
                            $summ = 0.0;
                            for ($ki = 0; $ki < count($paypal_dayprice); $ki++) { $summ += $paypal_dayprice[$ki]; }
                        } else {
                            $summ = (1* $paypal_dayprice * $days_count );
                        }

                    }

                } else { // Recurent time in evry days calculation

                    $final_summ = 0;
                    $temp_days = $days_array;
                    $temp_paypal_dayprice = $paypal_dayprice;

                    foreach ($temp_days as $days_array) {  // lOOP EACH DAY

                        $days_array = array($days_array);
                        $paypal_dayprice = $temp_paypal_dayprice;


                        if ( ! class_exists('wpdev_bk_biz_m') ) {

                            $summ = $this->get_cost_for_period(
                                                                get_bk_option( 'booking_paypal_price_period' ),
                                                                $this->get_cost_of_booking_resource( $booking_type ) ,
                                                                $days_array,
                                                                $times_array
                                );

                            if (get_bk_option( 'booking_paypal_price_period' ) == 'fixed')          $final_summ = 0; // if we are have fixed cost calculation so we will not gathering all costs but get just last one.

                            // Set first day as 0, if we have true all these conditions
                            if (   (get_bk_option( 'booking_paypal_price_period' ) == 'night')
                                && (get_bk_option( 'booking_is_time_apply_to_cost' ) != 'On' )
                                && ( count($temp_days)>1 ) && ($final_summ == 0 ) && ($summ > 0) )  $final_summ = -1*$summ + 0.000001;  // last number is need for definition its only for first day and make its little more than 0, then at final cost there is ROUND to the 2 nd number after comma.



                        } else  {

                            $paypal_dayprice        = apply_bk_filter('wpdev_season_rates', $paypal_dayprice, $days_array, $booking_type, $times_array,$post_form);  // Its return array with day costs

                            if (is_array($paypal_dayprice)) {
                                $summ = 0.0;
                                for ($ki = 0; $ki < count($paypal_dayprice); $ki++) { $summ += $paypal_dayprice[$ki]; }
                            } else {
                                $summ = (1* $paypal_dayprice * $days_count );
                            }

                        }

                        $final_summ += $summ;
                        $summ = 0.0;
                    }

                    $paypal_dayprice = $temp_paypal_dayprice;
                    $days_array = $temp_days;
                    $summ = $final_summ;
                }

                if (get_bk_option( 'booking_paypal_price_period' ) == 'fixed') {                        
                        if (is_array($paypal_dayprice) )  $summ = $paypal_dayprice[0] ;
                        else                             $summ = $paypal_dayprice ;
                }


                $summ = round($summ,2);
                $summ_original_without_additional = $summ ;

                if ($is_only_original_cost) {
                    if ($is_discount_calculate) {
                        $summ_original_without_additional = apply_bk_filter('coupons_discount_apply', $summ_original_without_additional, $post_form, $booking_type ); // Apply discounts coupons
                    }
                    return $summ_original_without_additional;
                }


//debuge('advanced_cost_apply', $summ , $post_form, $booking_type, $days_array );
                $summ = apply_bk_filter('advanced_cost_apply', $summ , $post_form, $booking_type, $days_array );    // Apply advanced cost managemnt
//debuge($summ);
                if ($is_discount_calculate)
                    $summ = apply_bk_filter('coupons_discount_apply', $summ , $post_form, $booking_type ); // Apply discounts based on coupons

                $summ = round($summ,2);
                return $summ;
    }

    
    // Update Cost and Cost_Nonce in DB for the new booking
    function wpbc_update_cost_of_new_booking( $booking_id, $booking_type, $booking_days_count, $times_array , $booking_form ) {
        
        
        $summ = $this->get_booking_cost( $booking_type, $booking_days_count, $times_array, $booking_form );

        $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ , $booking_form, $booking_type, $booking_days_count ); // Apply fixed deposit

        $is_deposit = false;
        if ($summ_deposit != $summ ) {
            $is_deposit = true;
            $summ__full = $summ;
            $summ       = $summ_deposit;
        }

        // Check for additional calendars
        $additional_calendars = array();
        $summ_additional_calendars = apply_bk_filter('check_cost_for_additional_calendars', $summ, $booking_form, $booking_type,  $times_array  ); // Apply cost according additional calendars
        if (isset($summ_additional_calendars))
            if( is_array($summ_additional_calendars) ) {
                $summ = $summ_additional_calendars[0];
                $additional_calendars = $summ_additional_calendars[2];
            }

        ///////////////////////////////////////////////////////////////////////////

        global $wpdb;
        $wp_nonce = ceil( time() / ( 86400 / 2 ));

        $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status='$wp_nonce' WHERE bk.booking_id= %d", $booking_id ) ;
        if ( false === $wpdb->query( $update_sql  ) ){
            ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating wp_nonce status in BD' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            return  false;            
        }
        
        return array(
                        'total_cost' => ( $is_deposit ? $summ__full : $summ )
                      , 'deposit_cost' => $summ_deposit
                      , 'wp_nonce' => $wp_nonce
                      , 'is_deposit' => $is_deposit
                      , 'additional_calendars' => $additional_calendars
                );
    }

    
    // Show Paypal form from Ajax request
    function show_paypal_form_in_ajax_request($booking_id, $booking_type, $booking_days_count, $times_array , $booking_form ){

        $respond_array = $this->wpbc_update_cost_of_new_booking($booking_id, $booking_type, $booking_days_count, $times_array, $booking_form);
        
        if (! empty($respond_array) ) {
            
            $wp_nonce = $respond_array['wp_nonce'];
            $is_deposit = $respond_array['is_deposit'];
            $additional_calendars = $respond_array['additional_calendars'];  
            
            $summ_deposit = $respond_array['deposit_cost'];
            if ( $is_deposit ) {
                $summ__full = $respond_array['total_cost'];
                $summ       = $respond_array['deposit_cost'];                
            } else {
                $summ = $respond_array['total_cost'];
            }
            
        } else {            
            die;    // Something was wrong !
        }
        
        $bk_title    = $this->get_booking_type( $booking_type );
        
        $summ = round($summ,2);
        $output = apply_bk_filter('wpdev_bk_define_payment_forms', '', $booking_id, $summ, $bk_title, $booking_days_count, $booking_type, $_POST["form"], $wp_nonce,$is_deposit, $additional_calendars );

        // Just make some Notes about deposit and balances
        if ($is_deposit)
            if (($summ__full-$summ_deposit)>0) {

                $summ_show           = wpdev_bk_cost_number_format ( $summ_deposit );
                $full_summ_show      = wpdev_bk_cost_number_format ( $summ__full );
                $balance_summ_show   = wpdev_bk_cost_number_format ( ($summ__full-$summ_deposit) );

                $cost__title_deposit  = __('deposit' ,'booking').": ";
                $cost__title_total    = __('Total cost' ,'booking').": ";
                $cost__title_balace   = __('balance' ,'booking').": ";

                $today_day = date_i18n(get_bk_option( 'booking_date_format') ); //date('m.d.Y')  ; //FixIn:5.4.5.7

                $paypal_curency =  get_bk_option( 'booking_paypal_curency' );
                $cost_currency  = $this->get_currency_info();
                if ($cost_currency == $paypal_curency) {
                    $cost_currency_1 = '';
                    $cost_currency_2 = " " . $cost_currency;
                } else {
                    $cost_currency_1 = $cost_currency . '';
                    $cost_currency_2 = "" ;
                }
                $cost_summ_with_title='';
                $cost_summ_with_title .= $cost__title_total . $cost_currency_1 . $full_summ_show . $cost_currency_2 . " /";
                $cost_summ_with_title .= $cost__title_deposit . $cost_currency_1 . $summ_show . $cost_currency_2 . ", ";
                $cost_summ_with_title .= $cost__title_balace . $cost_currency_1 . $balance_summ_show . $cost_currency_2 . "/";
                $cost_summ_with_title .= ' - '  . $today_day .'';

                make_bk_action('wpdev_make_update_of_remark' , $booking_id , $cost_summ_with_title , true );

                $this->update_booking_cost($booking_id, $summ_deposit );
        } // fin. notes.



        $is_turned_off = apply_bk_filter('is_payment_forms_off', true);
        if ($is_turned_off)  return;

        if ( ($summ + 0) > 0){  
                                                       
            make_bk_action('wpbc_set_coupon_inactive', $booking_id, $booking_type, $booking_days_count, $times_array , $booking_form );
            
            $original_symbols  = array ('&euro;', '&#163;', '&#165;' );
            $temporary_symbols = array ('^euro^', '^gbp^', '^jpy^' );
            $output = str_replace( $original_symbols, $temporary_symbols, $output);
            
            $output = esc_js($output);  
            $output = html_entity_decode($output);
            
            $output = str_replace( $temporary_symbols, $original_symbols, $output);
                          
            ?>
            <script type="text/javascript">
               document.getElementById('submiting<?php echo $booking_type; ?>').innerHTML ='';
               if (document.getElementById('paypalbooking_form<?php echo $booking_type; ?>') != null) {
                  document.getElementById('paypalbooking_form<?php echo $booking_type; ?>').innerHTML = '<div class=\"wpdevbk\" style=\"height:auto;margin:20px 0px;\" ><?php echo $output; ?></div>';
                  setTimeout(function() { makeScroll("#paypalbooking_form<?php echo $booking_type; ?>" ); }, 500);
                  jQuery("label[for='calendar_type']").hide();
                  jQuery("select[name='active_booking_form']").hide();
              }
            </script>
            <?php
        }
    }

//   A D M I N     S I D E    //////////////////////////////////////////////////////////////////////////////////////////////////////////////


  //  A d m i n    p a n e l   ->   Settings -> Add Booking   ///////////////////////////////////////////////////////////////////////
  //__________________________________________________________//
    // Show button for autofill form at the admin panel
    function wpdev_show_autofill_button(){
        ?>
            <div id="autofillform" class="topmenuitemborder" style="float:right;border:none;background:none;margin:0px;">
                <a class="button-primary button" onclick="javascript:autofill_bk_form();"><?php _e('Auto-fill form' ,'booking'); ?></a>
            </div>
             <script type="text/javascript">

            function autofill_bk_form(){

                var my_element_value = 'admin';
                var form_elements = jQuery('.booking_form_div input');

                jQuery.each(form_elements, function(){

                    if (  (this.type !== 'button') && (this.type !== 'hidden') && (this.name.search('starttime') == -1 ) && (this.name.search('endtime') == -1 ) ) {        //FixIn:6.0.1.12

                        this.value = my_element_value;
                        if (this.name.search('email') != -1 ) {
                            this.value = my_element_value + '@blank.com';
                        }
                        if (this.name.search('starttime') != -1 ) { this.name = 'temp'; this.value=''; } // set name of time to someother name
                        if (this.name.search('endtime') != -1 ) { this.name = 'temp2'; this.value=''; }  // set name of time to someother name
                    }
                });

                //jQuery('.booking_form').submit();
                //var form_elements_text = jQuery('.booking_form_div textarea');
                //jQuery.each(form_elements_text, function(){ this.value = my_element_value; });
            }
            </script>
            <?php

    }


  //  A d m i n    p a n e l   ->   Settings -> Payment       ///////////////////////////////////////////////////////////////////////
  //__________________________________________________________//
    //Show settings page depends from selecting TAB
    function settings_menu_content(){
        //$is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'not_low_level_user'); //Anxo customizarion
        if (! $is_can) return; //Anxo customizarion

        switch ($_GET['tab']) {

         case 'payment':
            $this->show_settings_content();
            return false;
            break;

         default:
            return true;
            break;
        }

    }

    //Show Settings page
    function show_settings_content() { 
        ?>
        <div class="clear" style="height:0px;"></div>
        <div id="ajax_working"></div>
        <div id="poststuff0" class="metabox-holder">
            <form  name="post_settings_payment_integration" action="" method="post" id="post_settings_payment_integration" >
                <?php $this->show_billing_settings();      ?>
                <?php make_bk_action('wpdev_bk_payment_show_settings_content' );  ?>
            </form>
        </div>
        <?php
    }


    // Show settings for autofill options at the Payment form.
    function show_billing_settings(){

            if ( isset( $_POST['sage_billing_customer_email'] ) ) {
                  update_bk_option( 'booking_billing_customer_email', $_POST['sage_billing_customer_email'] );
                  update_bk_option( 'booking_billing_firstnames', $_POST['sage_billing_firstnames'] );
                  update_bk_option( 'booking_billing_surname', $_POST['sage_billing_surname'] );
                  update_bk_option( 'booking_billing_phone', $_POST['sage_billing_phone'] );
                  update_bk_option( 'booking_billing_address1', $_POST['sage_billing_address1'] );
                  update_bk_option( 'booking_billing_city', $_POST['sage_billing_city'] );
                  update_bk_option( 'booking_billing_country', $_POST['sage_billing_country'] );
                  update_bk_option( 'booking_billing_post_code', $_POST['sage_billing_post_code'] );

                  update_bk_option( 'booking_billing_state', $_POST['sage_billing_state'] );

            }

            $sage_billing_customer_email =  get_bk_option( 'booking_billing_customer_email' );
            $sage_billing_firstnames =  get_bk_option( 'booking_billing_firstnames' );
            $sage_billing_surname    =  get_bk_option( 'booking_billing_surname' );
            $sage_billing_phone     =  get_bk_option( 'booking_billing_phone' );
            $sage_billing_address1  =  get_bk_option( 'booking_billing_address1' );
            $sage_billing_city      =  get_bk_option( 'booking_billing_city' );
            $sage_billing_country   =  get_bk_option( 'booking_billing_country' );
            $sage_billing_post_code =  get_bk_option( 'booking_billing_post_code' );

            $sage_billing_state   =  get_bk_option( 'booking_billing_state' );
        ?>
            <div id="visibility_container_billing" class="visibility_container" style="display:none;">
            <div class='meta-box'>  
                 <div <?php $my_close_open_win_id = 'bk_settings_costs_billing'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Billing form fields customization' ,'booking'); ?></span></h3> <div class="inside">
                        <!--form  name="post_option_billing_form" action="" method="post" id="post_option_billing_form" -->
                            <table class="form-table">
                                <tbody>

                                    <?php $all_form_fields = $this->get_fields_from_booking_form();
                                    //debuge($all_form_fields[1][2]);
                                    $fields_orig_names = $all_form_fields[1][2];
                                    ?>

                                    <tr valign="top">
                                      <th scope="row" colspan="2">
                                        <p class="wpbc-info-message" style="text-align:left;"><?php printf(__('Please select a field from your booking form. This field will be automatically assigned to the current field in the billing form.' ,'booking'),'<b>','</b>');?></p>
                                      </th>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_customer_email" ><?php _e('Customer Email' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_customer_email" name="sage_billing_customer_email">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_customer_email == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_firstnames" ><?php _e('First Name(s)' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_firstnames" name="sage_billing_firstnames">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_firstnames == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_surname" ><?php _e('Last name' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_surname" name="sage_billing_surname">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_surname == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>


                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_phone" ><?php _e('Phone' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_phone" name="sage_billing_phone">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_phone == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                        <td scope="row" colspan="2">
                                          <div style="height: 0px; clear: both;" class="clear topmenuitemseparatorv"></div>
                                        </td>
                                    </tr>


                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_address1" ><?php _e('Billing Address' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_address1" name="sage_billing_address1">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_address1 == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_city" ><?php _e('Billing City' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_city" name="sage_billing_city">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_city == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_post_code" ><?php _e('Post Code' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_post_code" name="sage_billing_post_code">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_post_code == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_country" ><?php _e('Country' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_country" name="sage_billing_country">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_country == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>

                                    <tr valign="top">
                                        <td scope="row" colspan="2">
                                          <div style="height: 0px; clear: both;" class="clear topmenuitemseparatorv"></div>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                      <th scope="row">
                                        <label for="sage_billing_state" ><?php _e('State' ,'booking'); ?>:</label>
                                      </th>
                                      <td>
                                         <select id="sage_billing_state" name="sage_billing_state">
                                            <?php foreach ( $fields_orig_names as $key => $field_names) { ?>
                                              <option <?php if($sage_billing_state == $field_names) echo "selected"; ?> value="<?php echo $field_names; ?>"><?php echo $field_names; ?></option>
                                            <?php } ?>
                                         </select>
                                      </td>
                                    </tr>




                                    <?php if (get_bk_option( 'booking_sage_is_active' ) == 'On') { ?>
                                    <tr valign="top">
                                      <th scope="row" colspan="2">
                                          <p class="wpbc-info-message" style="text-align:left;"><?php printf(__('Configuring these %sfields is required for the some payment%s systems!' ,'booking'),'<b>','</b>');?></p>
                                      </th>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                            <div class="clear" style="height:10px;"></div>
                            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="billing_form_submit"/>
                            <div class="clear" style="height:10px;"></div>
                        <!--/form-->
           </div> </div> </div>
            </div>
                            <?php
    }


        // Get fields from booking form at the settings page or return false if no fields
        function get_fields_from_booking_form(){
            $booking_form  = get_bk_option( 'booking_form' );
            $types = 'text[*]?|email[*]?|time[*]?|textarea[*]?|select[*]?|checkbox[*]?|radio|acceptance|captchac|captchar|file[*]?|quiz';
            $regex = '%\[\s*(' . $types . ')(\s+[a-zA-Z][0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)?((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
            $regex2 = '%\[\s*(country[*]?|starttime[*]?|endtime[*]?)(\s*[a-zA-Z]*[0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)*((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
            $fields_count = preg_match_all($regex, $booking_form, $fields_matches) ;
            $fields_count2 = preg_match_all($regex2, $booking_form, $fields_matches2) ;

            //Gathering Together 2 arrays $fields_matches  and $fields_matches2
            foreach ($fields_matches2 as $key => $value) {
                if ($key == 2) $value = $fields_matches2[1];
                foreach ($value as $v) {
                    $fields_matches[$key][count($fields_matches[$key])]  = $v;
                }
            }
            $fields_count += $fields_count2;

            if ($fields_count>0) return array($fields_count, $fields_matches);
            else return false;
        }




// S e t t i n g s


    // S e t t i n g s /////////////////////////////////////////////////////
    //
    // Settings for selecting default booking resource
    function settings_set_show_time_in_tooltips(){
        if (isset($_POST['booking_highlight_timeslot_word'])) {
             update_bk_option( 'booking_highlight_timeslot_word' ,  $_POST['booking_highlight_timeslot_word'] );
        }
        $booking_highlight_timeslot_word        = get_bk_option( 'booking_highlight_timeslot_word');
        ?>
        <tr valign="top" class="ver_premium_plus">
             <th scope="row">
                 <label for="booking_highlight_timeslot_word" ><?php _e('Title of booked timeslot(s)' ,'booking'); ?>:</label>
             </th>
             <td>
                 <input value="<?php echo $booking_highlight_timeslot_word; ?>" name="booking_highlight_timeslot_word" id="booking_highlight_timeslot_word"  type="text"    />
                <p class="description"><?php printf(__('Type your %stitle%s, what will show in mouseover tooltip near booked timeslot(s)' ,'booking'),'<b>','</b>');?></p>                    
             </td>
         </tr>
         <tr><td colspan="2" style="padding:0px;"><div style="margin-top:-15px;"><?php make_bk_action('show_additional_translation_shortcode_help'); ?></div></td></tr>
        <?php
    }



    // Set Advanced Settings - list of function for each row
    function settings_advanced_set_time_format(){
         if ( isset( $_POST['booking_time_format'] ) ) {
             update_bk_option( 'booking_time_format' , $_POST['booking_time_format'] );
         }
         $booking_time_format = get_bk_option( 'booking_time_format');
        ?>
            <tr valign="top" class="ver_premium">
            <th scope="row"><label for="booking_time_format" ><?php _e('Time Format' ,'booking'); ?>:</label><br/>
            </th>
                <td>
                    <fieldset>
                    <?php
                            $time_formats =  array( 'g:i a', 'g:i A', 'H:i' ) ;
                            $custom = TRUE;
                            foreach ( $time_formats as $format ) {
                                    echo "\t<label title='" . esc_attr($format) . "'>";
                                    echo "<input type='radio' name='booking_time_format' value='" . esc_attr($format) . "'";
                                    if ( get_bk_option( 'booking_time_format') === $format ) {  echo " checked='checked'"; $custom = FALSE; }
                                    echo ' /> ' . date_i18n( $format ) . "</label> &nbsp;&nbsp;&nbsp; \n";
                            }
                            echo '	<label><input type="radio" name="booking_time_format" id="time_format_custom_radio" value="'.$booking_time_format.'"';
                            if ( $custom )  echo ' checked="checked"';
                            echo '/> ' . __('Custom' ,'booking') . ': </label>';?>
                                <input id="booking_time_format_custom" class="regular-text code" type="text" size="45" value="<?php echo $booking_time_format; ?>" name="booking_time_format_custom"
                                       onchange="javascript:document.getElementById('time_format_custom_radio').value = this.value;document.getElementById('time_format_custom_radio').checked=true;"
                                       />
                   <?php
                            echo ' ' . date_i18n( $booking_time_format ) . "\n";
                            echo '&nbsp;&nbsp;&nbsp;&nbsp;';
                    ?>
                            <p class="description"><?php printf(__('Type your time format for emails and the booking table. %sDocumentation on time formatting%s' ,'booking'),'<a href="http://php.net/manual/en/function.date.php" target="_blank">','</a>');?></p>
                    </fieldset>

                </td>
            </tr>

        <?php
    }



    /** Settings > General > Calendar (section) > Range days selection*/
    function settings_advanced_set_range_selections(){

        /** Update -> Range days selection*/
        if (isset($_POST['range_selection_days_count'])) {
            //update_bk_option('booking_range_selection_is_active', ((isset($_POST['range_selection_is_active'])) ? 'On' : 'Off'));
            update_bk_option('booking_range_selection_days_count', $_POST['range_selection_days_count']);

            if ($_POST['range_start_day'] == '-1') {

                $range_start_day = $_POST['range_start_day'];
            } else {
               $range_start_day = '';

                if (isset( $_POST['range_start_day0'] ))    $range_start_day .= '0,';
                if (isset( $_POST['range_start_day1'] ))    $range_start_day .= '1,';
                if (isset( $_POST['range_start_day2'] ))    $range_start_day .= '2,';
                if (isset( $_POST['range_start_day3'] ))    $range_start_day .= '3,';
                if (isset( $_POST['range_start_day4'] ))    $range_start_day .= '4,';
                if (isset( $_POST['range_start_day5'] ))    $range_start_day .= '5,';
                if (isset( $_POST['range_start_day6'] ))    $range_start_day .= '6,';

                if (strlen($range_start_day)>0) $range_start_day = substr($range_start_day,0,-1);
                else                            $range_start_day = '-1';
            }
            update_bk_option('booking_range_start_day', $range_start_day);

            update_bk_option('booking_range_selection_days_count_dynamic', $_POST['range_selection_days_count_dynamic']);
            update_bk_option('booking_range_selection_days_max_count_dynamic', $_POST['range_selection_days_count_dynamic_max']);
            update_bk_option('booking_range_selection_days_specific_num_dynamic', $_POST['range_selection_days_count_dynamic_specific']);
            update_bk_option('booking_range_selection_type', $_POST['range_selection_type']);

            if ($_POST['range_start_day_dynamic'] == '-1') {

                $range_start_day_dynamic = $_POST['range_start_day_dynamic'];
            } else {
                $range_start_day_dynamic = '';

                if (isset( $_POST['range_start_day_dynamic0'] ))    $range_start_day_dynamic .= '0,';
                if (isset( $_POST['range_start_day_dynamic1'] ))    $range_start_day_dynamic .= '1,';
                if (isset( $_POST['range_start_day_dynamic2'] ))    $range_start_day_dynamic .= '2,';
                if (isset( $_POST['range_start_day_dynamic3'] ))    $range_start_day_dynamic .= '3,';
                if (isset( $_POST['range_start_day_dynamic4'] ))    $range_start_day_dynamic .= '4,';
                if (isset( $_POST['range_start_day_dynamic5'] ))    $range_start_day_dynamic .= '5,';
                if (isset( $_POST['range_start_day_dynamic6'] ))    $range_start_day_dynamic .= '6,';

                if (strlen($range_start_day_dynamic)>0) $range_start_day_dynamic = substr($range_start_day_dynamic,0,-1);
                else                                    $range_start_day_dynamic = '-1';
            }
            update_bk_option('booking_range_start_day_dynamic', $range_start_day_dynamic);
        }

        $range_selection_type = get_bk_option('booking_range_selection_type');
        if (get_bk_option('booking_range_selection_type') == false) $range_selection_type = 'fixed';
        //$range_selection_is_active = get_bk_option('booking_range_selection_is_active');
        $range_selection_days_count = get_bk_option('booking_range_selection_days_count');
        $range_start_day = get_bk_option('booking_range_start_day');

        $range_selection_days_count_dynamic = get_bk_option('booking_range_selection_days_count_dynamic');
        $range_start_day_dynamic = get_bk_option('booking_range_start_day_dynamic');

        $range_selection_days_count_dynamic_max = get_bk_option('booking_range_selection_days_max_count_dynamic');
        $range_selection_days_count_dynamic_specific = get_bk_option('booking_range_selection_days_specific_num_dynamic');

        $type_of_day_selections =  get_bk_option( 'booking_type_of_day_selections');

                 ?>
            <tr valign="top" class="ver_premium"><td colspan="2" style="padding-top:0px; padding-bottom:0px;">

                <div id="togle_settings_range_type_selection" style="<?php if ($type_of_day_selections != 'range') echo "display:none;";/**/ ?>" class="hided_settings_table">                            
                    <div class="wpbc-secondary-suboptions">
                        <fieldset>
                            <label for="range_selection_type_fixed">
                                <input  <?php if ($range_selection_type == 'fixed') echo 'checked="checked"'; ?> 
                                    value="fixed" type="radio" id="range_selection_type_fixed"  name="range_selection_type" 
                                    onclick="javascript: jQuery('#togle_settings_range').slideDown('normal');jQuery('#togle_settings_range_dynamic').slideUp('normal');"  
                                    />
                                <span><?php printf(__('Select a %sFIXED%s number of days with %s1 mouse click%s' ,'booking'),'<strong>','</strong>','<strong>','</strong>'); ?></span>
                            </label><br/>

                            <label for="range_selection_type_dynamic">
                                <input  <?php if ($range_selection_type == 'dynamic') echo 'checked="checked"';/**/ ?> 
                                    value="dynamic" type="radio" id="range_selection_type_dynamic"  name="range_selection_type"  
                                    onclick="javascript: jQuery('#togle_settings_range').slideUp('normal');jQuery('#togle_settings_range_dynamic').slideDown('normal');"  
                                    />
                                <span><?php printf(__('Select a %sDYNAMIC%s range of days with %s2 mouse clicks%s' ,'booking'),'<strong>','</strong>','<strong>','</strong>'); ?></span>
                            </label>
                        </fieldset>
                    </div>
                        <div class="clear-line"></div>

                        <table id="togle_settings_range" style="<?php if ($range_selection_type != 'fixed') echo 'display:none;';?>" class="hided_settings_table">
                            <tr valign="top">
                                <th scope="row"><label for="range_selection_days_count" ><?php _e('Days selection number' ,'booking'); ?>:</label></th>
                                <td>
                                    <select style="width:60px;" name="range_selection_days_count" id="range_selection_days_count">
                                        <?php for ($i=1; $i<181; $i++) { ?>
                                            <option <?php if ( $i == $range_selection_days_count ) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    <span class="description"><?php printf(__('Type your %snumber of days for range selection%s' ,'booking'),'<b>','</b>');?></span>
                                </td>
                            </tr>
                            <tr valign="top">
                                <?php /** Selection  Start  Day of Range selection  */ ?>
                                <td colspan="2">
                                    <div class="wpbc-settings-internal-ui-tabs-section-header">
                                        <span for="range_start_day"><?php _e('Start day of range' ,'booking'); ?>: </span>&nbsp;&nbsp;
                                        <label for="range_fixed_start_day_any_day">
                                            <input type="radio"
                                               name="range_start_day"
                                               id="range_fixed_start_day_any_day"
                                               value="-1"
                                               onclick="javascript:jQuery('.range_start_day_week_days').slideUp('normal');"
                                               <?php if($range_start_day == '-1') echo ' checked="CHECKED" '; ?>
                                               />
                                            <?php _e('Any day of week' ,'booking'); ?>
                                        </label>
                                        <span style="border:1px solid #bbb;height:100%; width:1px;"></span>
                                        <label for="range_fixed_start_day_specific_day">
                                        <input type="radio"
                                               name="range_start_day"
                                               id="range_fixed_start_day_specific_day"
                                               value="specific"
                                               onclick="javascript:jQuery('.range_start_day_week_days').slideDown('normal');"
                                               <?php if($range_start_day != '-1') echo ' checked="CHECKED" '; ?>
                                            />
                                            <?php _e('Specific day(s) of week' ,'booking'); ?>
                                        </label>
                                    </div>

                                    <div class="wpbc-settings-internal-ui-tabs-section-body">
                                        <div style="<?php if($range_start_day == '-1') echo ' display:none; '; ?>border-bottom: 1px solid #CCCCCC;padding:15px 5px;" class="range_start_day_week_days" >
                                            <?php $range_start_day = explode(',', $range_start_day); ?>
                                            <label for="range_start_day0" class="wpbc-single-checkbox"><input id="range_start_day0" name="range_start_day0" <?php if (in_array('0', $range_start_day)) echo "checked"; ?> value="0" type="checkbox"/>
                                            <span><?php _e('Sunday' ,'booking'); ?></span></label>
                                            <label for="range_start_day1" class="wpbc-single-checkbox"><input id="range_start_day1" name="range_start_day1" <?php if (in_array('1', $range_start_day)) echo "checked"; ?> value="1" type="checkbox"/>
                                            <span><?php _e('Monday' ,'booking'); ?></span></label>
                                            <label for="range_start_day2" class="wpbc-single-checkbox"><input id="range_start_day2" name="range_start_day2" <?php if (in_array('2', $range_start_day)) echo "checked"; ?> value="2" type="checkbox"/>
                                            <span><?php _e('Tuesday' ,'booking'); ?></span></label>
                                            <label for="range_start_day3" class="wpbc-single-checkbox"><input id="range_start_day3" name="range_start_day3" <?php if (in_array('3', $range_start_day)) echo "checked"; ?> value="3" type="checkbox"/>
                                            <span><?php _e('Wednesday' ,'booking'); ?></span></label>
                                            <label for="range_start_day4" class="wpbc-single-checkbox"><input id="range_start_day4" name="range_start_day4" <?php if (in_array('4', $range_start_day)) echo "checked"; ?> value="4" type="checkbox"/>
                                            <span><?php _e('Thursday' ,'booking'); ?></span></label>
                                            <label for="range_start_day5" class="wpbc-single-checkbox"><input id="range_start_day5" name="range_start_day5" <?php if (in_array('5', $range_start_day)) echo "checked"; ?> value="5" type="checkbox"/>
                                            <span><?php _e('Friday' ,'booking'); ?></span></label>
                                            <label for="range_start_day6" class="wpbc-single-checkbox"><input id="range_start_day6" name="range_start_day6" <?php if (in_array('6', $range_start_day)) echo "checked"; ?> value="6" type="checkbox"/>
                                            <span><?php _e('Saturday' ,'booking'); ?></span></label>
                                        </div>
                                        <div class="description"><?php _e('Select your start day of range selection at week' ,'booking');?></div>
                                    </div>
                                </td>
                                <?php /** End  Start  Day of Range selection  */ ?>

                            </tr>
                        </table>

                        <table id="togle_settings_range_dynamic" style="<?php if ($range_selection_type != 'dynamic') echo 'display:none;';/**/ ?>" class="hided_settings_table">
                            <tr valign="top">
                                <th><?php _e('Days selection number' ,'booking'); ?>:</th>
                                <td style="padding-top:5px;">
                                    <label for="range_selection_days_count_dynamic"><?php _e('Min' ,'booking'); ?>:</label>
                                    <select style="width:60px;" name="range_selection_days_count_dynamic" id="range_selection_days_count_dynamic">
                                        <?php for ($i=1; $i<181; $i++) { ?>
                                            <option <?php if ( $i == $range_selection_days_count_dynamic ) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    &nbsp;&nbsp;&nbsp;

                                    <label for="range_selection_days_count_dynamic_max"><?php _e('Max' ,'booking'); ?>:</label>
                                    <select style="width:60px;" name="range_selection_days_count_dynamic_max" id="range_selection_days_count_dynamic_max">
                                        <?php for ($i=1; $i<181; $i++) { ?>
                                            <option <?php if ( $i == $range_selection_days_count_dynamic_max ) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php } ?>
                                    </select>
                                    <p class="description"><?php printf(__('Select your %sminimum and maximum number of days for range selection%s' ,'booking'),'<b>','</b>');?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="range_selection_days_count_dynamic_specific" ><?php _e('Specific days selections' ,'booking'); ?>:</label>
                                </th>
                                <td>
                                    <input value="<?php echo $range_selection_days_count_dynamic_specific; ?>" name="range_selection_days_count_dynamic_specific" id="range_selection_days_count_dynamic_specific" class="large-text" type="text"   />
                                    <p class="description"><?php printf(__('Type your %sspecific%s days, which can be selected by visitors, or leave this value empty. It can be several days separated by comma (example: %s) or by dash (example: %s, its the same like this: %s) or combination (example:%s, its the same like this: %s)' ,'booking')
                                            ,'<b>','</b>', '<code>7,14,21,28</code>', '<code>3-5</code>', '<code>3,4,5</code>', '<code>3-5,7,14</code>', '<code>3,4,5,7,14</code>');?></p>
                                </td>
                            </tr>

                            <tr valign="top">
                                <?php /** Selection  Start  Day of Range selection  */ ?>
                                <td colspan="2">

                                    <div class="wpbc-settings-internal-ui-tabs-section-header">
                                        <span for="range_start_day"><?php _e('Start day of range' ,'booking'); ?>: </span>&nbsp;&nbsp;
                                        <label for="range_dynamic_start_day_any_day">
                                            <input type="radio"
                                               name="range_start_day_dynamic"
                                               id="range_dynamic_start_day_any_day"
                                               value="-1"
                                               onclick="javascript:jQuery('.range_start_day_week_days_dynamic').slideUp('normal');"
                                            <?php if($range_start_day_dynamic == '-1') echo ' checked="CHECKED" '; ?>
                                            />                                            
                                            <?php _e('Any day of week' ,'booking'); ?>
                                        </label>
                                        <span style="border:1px solid #bbb;height:100%; width:1px;"></span>
                                        <label for="range_dynamic_start_day_specific_day">
                                            <input type="radio"
                                               name="range_start_day_dynamic"
                                               id="range_dynamic_start_day_specific_day"
                                               value="specific"
                                               onclick="javascript:jQuery('.range_start_day_week_days_dynamic').slideDown('normal');"
                                            <?php if($range_start_day_dynamic != '-1') echo ' checked="CHECKED" '; ?>
                                            />                                            
                                            <?php _e('Specific day(s) of week' ,'booking'); ?>
                                        </label>
                                    </div>

                                    <div class="wpbc-settings-internal-ui-tabs-section-body">
                                        <div style="<?php if($range_start_day_dynamic == '-1') echo ' display:none; '; ?>border-bottom: 1px solid #CCCCCC;padding:15px 5px;" class="range_start_day_week_days_dynamic" >
                                            <?php $range_start_day_dynamic = explode(',', $range_start_day_dynamic); ?>
                                            <label for="range_start_day_dynamic0" class="wpbc-single-checkbox">
                                                <input id="range_start_day_dynamic0" name="range_start_day_dynamic0" <?php if (in_array('0', $range_start_day_dynamic)) echo "checked"; ?> value="0" type="checkbox"/>
                                                <?php _e('Sunday' ,'booking'); ?>
                                            </label>
                                            <label for="range_start_day_dynamic1" class="wpbc-single-checkbox">                                                
                                                <input id="range_start_day_dynamic1" name="range_start_day_dynamic1" <?php if (in_array('1', $range_start_day_dynamic)) echo "checked"; ?> value="1" type="checkbox"/>
                                                <?php _e('Monday' ,'booking'); ?>
                                            </label>
                                            <label for="range_start_day_dynamic2" class="wpbc-single-checkbox">                                                
                                                <input id="range_start_day_dynamic2" name="range_start_day_dynamic2" <?php if (in_array('2', $range_start_day_dynamic)) echo "checked"; ?> value="2" type="checkbox"/>
                                                <?php _e('Tuesday' ,'booking'); ?>
                                            </label>
                                            <label for="range_start_day_dynamic3" class="wpbc-single-checkbox">                                                
                                                <input id="range_start_day_dynamic3" name="range_start_day_dynamic3" <?php if (in_array('3', $range_start_day_dynamic)) echo "checked"; ?> value="3" type="checkbox"/>
                                                <?php _e('Wednesday' ,'booking'); ?>
                                            </label>
                                            <label for="range_start_day_dynamic4" class="wpbc-single-checkbox">                                                
                                                <input id="range_start_day_dynamic4" name="range_start_day_dynamic4" <?php if (in_array('4', $range_start_day_dynamic)) echo "checked"; ?> value="4" type="checkbox"/>
                                                <?php _e('Thursday' ,'booking'); ?>
                                            </label>
                                            <label for="range_start_day_dynamic5" class="wpbc-single-checkbox">                                                
                                                <input id="range_start_day_dynamic5" name="range_start_day_dynamic5" <?php if (in_array('5', $range_start_day_dynamic)) echo "checked"; ?> value="5" type="checkbox"/>
                                                <?php _e('Friday' ,'booking'); ?>
                                            </label>
                                            <label for="range_start_day_dynamic6" class="wpbc-single-checkbox">                                                
                                                <input id="range_start_day_dynamic6" name="range_start_day_dynamic6" <?php if (in_array('6', $range_start_day_dynamic)) echo "checked"; ?> value="6" type="checkbox"/>
                                                <?php _e('Saturday' ,'booking'); ?>
                                            </label>
                                        </div>
                                        <div class="description"><?php _e('Select your start day of range selection at week' ,'booking');?></div>
                                    </div>
                                </td>
                                <?php /** End  Start  Day of Range selection  */ ?>
                            </tr>
                        </table>

                </div>

            </td></tr>
            <tr><td colspan="2" style="padding:0px 0px 5px;border-bottom:1px solid #cccccc; "></td></tr>

        <?php
    }

    function settings_advanced_set_fixed_time(){
         if ( isset( $_POST['range_selection_start_time'] ) ) {

                 if (isset( $_POST['booking_recurrent_time'] ))     $booking_recurrent_time = 'On';
                 else                                               $booking_recurrent_time = 'Off';
                 update_bk_option( 'booking_recurrent_time' ,  $booking_recurrent_time );

                 if (isset( $_POST['range_selection_time_is_active'] ))     $range_selection_time_is_active = 'On';
                 else                                                  $range_selection_time_is_active = 'Off';
                 update_bk_option( 'booking_range_selection_time_is_active' ,  $range_selection_time_is_active );

                 $range_selection_start_time =  $_POST['range_selection_start_time'];
                 update_bk_option( 'booking_range_selection_start_time' , $range_selection_start_time );

                 $range_selection_end_time =  $_POST['range_selection_end_time'];
                 update_bk_option( 'booking_range_selection_end_time' , $range_selection_end_time );

         }
            $range_selection_time_is_active = get_bk_option( 'booking_range_selection_time_is_active');
            $range_selection_start_time  = get_bk_option( 'booking_range_selection_start_time');
            $range_selection_end_time  = get_bk_option( 'booking_range_selection_end_time');

            $booking_recurrent_time  = get_bk_option( 'booking_recurrent_time' ) ;
            $type_of_day_selections =  get_bk_option( 'booking_type_of_day_selections');
        ?>

            <tr valign="top" class="ver_premium booking_time_advanced_config" style="<?php if ( get_bk_option( 'booking_type_of_day_selections') == 'single' ) { echo 'display:none;'; } ?>">
                <th scope="row">
                    <?php _e('Use time selections as recurrent time slots' ,'booking'); ?>:
                </th>
                <td>
                    <fieldset>
                        <label for="booking_recurrent_time" >
                            <input <?php if ($booking_recurrent_time == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_recurrent_time; ?>" name="booking_recurrent_time" id="booking_recurrent_time" type="checkbox" 
                                    onclick="javascript: if (this.checked) { 
                                        jQuery('#range_selection_time_is_active').attr('checked', false); 
                                        jQuery('.togle_settings_range_times').slideUp('normal');
                                    } "                                                                                                          
                                    />
                            <?php _e('Check this box if you want to use recurrent time to reserve several days. This means that middle days will be partially booked by actual times, otherwise the time in the booking form will be used as check-in/check-out time for the first and last day of the reservation.' ,'booking');?>
                        </label>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top" class="ver_premium booking_time_advanced_config"  style="<?php if ( get_bk_option( 'booking_type_of_day_selections') == 'single' ) { echo 'display:none;'; } ?>">
                <th scope="row">
                    <?php _e('Use check in/out time' ,'booking'); ?>:
                </th>
                <td>
                    <fieldset>
                        <label for="range_selection_time_is_active" >
                            <input <?php if ($range_selection_time_is_active == 'On') echo "checked";/**/ ?>  value="<?php echo $range_selection_time_is_active; ?>" name="range_selection_time_is_active" id="range_selection_time_is_active" type="checkbox"
                                onclick="javascript: if (this.checked) { 
                                    alert('<?php _e('Warning' ,'booking'); echo '! '; _e('This option will overwrite any times selection in your booking form.' ,'booking');?> '); 
                                    jQuery('.togle_settings_range_times').slideDown('normal');
                                    jQuery('#booking_recurrent_time').attr('checked', false);
                                } else  jQuery('.togle_settings_range_times').slideUp('normal');"
                            />
                            <?php _e('Check this option, to use check in/out time during booking process. ' ,'booking');?>
                        </label>
                    </fieldset>                        
                    <p class="description"><?php printf(__('%s Important!%s This will overwrite any times selection in your booking form.' ,'booking'),'<b>','</b>');?></p>
                </td>
            </tr>

            <tr valign="top" class="ver_premium booking_time_advanced_config"  style="<?php if ( get_bk_option( 'booking_type_of_day_selections') == 'single' ) { echo 'display:none;'; } ?>"> 
                <td colspan="2" style="padding-top:0px;padding-bottom:0px;">
                <div style="margin-left:40px;">    
                <table id="togle_settings_range_times" style="width:100%;<?php if ($range_selection_time_is_active != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table togle_settings_range_times">
                    <tr>
                    <th scope="row"><label for="range_selection_start_time" ><?php _e('Check-in time' ,'booking'); ?>:</label><br><?php printf(__('%sstart booking time%s' ,'booking'),'<span style="color:#888;font-weight:bold;">','</span>'); ?></th>
                        <td><input value="<?php echo $range_selection_start_time; ?>" name="range_selection_start_time" id="range_selection_start_time" class="wpdev-validates-as-time" type="text" size="5"  />
                            <p class="description"><?php printf(__('Type your %sCheck-in%s time of booking' ,'booking'),'<b>','</b>');?></p>
                        </td>
                    </tr>

                    <tr>
                    <th scope="row"><label for="range_selection_end_time" ><?php _e('Check-Out time' ,'booking'); ?>:</label><br><?php printf(__('%send booking time%s' ,'booking'),'<span style="color:#888;font-weight:bold;">','</span>'); ?></th>
                    <td><input value="<?php echo $range_selection_end_time; ?>" name="range_selection_end_time" id="range_selection_end_time" class="wpdev-validates-as-time" type="text" size="5"   />
                            <p class="description"><?php printf(__('Type your %sCheck-Out%s time of booking' ,'booking'),'<b>','</b>');?></p>
                        </td>
                    </tr>
                </table>
                </div>
                </td>
            </tr>
         <?php
    }


    // Show Cost Section in General booking Settings page
    function wpdev_bk_general_settings_cost_section() {

             if ( isset( $_POST['paypal_price_period'] ) ) {
                 update_bk_option( 'booking_paypal_price_period' , $_POST['paypal_price_period'] );

                 if (isset( $_POST['is_time_apply_to_cost'] ))     $is_time_apply_to_cost = 'On';
                 else                                              $is_time_apply_to_cost = 'Off';
                 update_bk_option( 'booking_is_time_apply_to_cost' , $is_time_apply_to_cost );

                 if (isset( $_POST['is_show_booking_summary_in_payment_form'] ))     $is_show_booking_summary_in_payment_form = 'On';
                 else                                                                $is_show_booking_summary_in_payment_form = 'Off';
                 update_bk_option( 'booking_is_show_booking_summary_in_payment_form' , $is_show_booking_summary_in_payment_form );

                 update_bk_option( 'booking_cost_currency_format_decimal_number',  $_POST['cost_currency_format_decimal_number'] );
                 update_bk_option( 'booking_cost_currency_format_decimal_separator',  $_POST['cost_currency_format_decimal_separator'] );
                 update_bk_option( 'booking_cost_currency_format_thousands_separator',  $_POST['cost_currency_format_thousands_separator'] );
             }
             $is_time_apply_to_cost = get_bk_option( 'booking_is_time_apply_to_cost'  );
             $is_show_booking_summary_in_payment_form = get_bk_option( 'booking_is_show_booking_summary_in_payment_form'  );
          ?>
                            <div class='meta-box'>
                              <div <?php $my_close_open_win_id = 'bk_settings_general_cost_options'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                                    <h3 class='hndle'><span><?php _e('Costs' ,'booking'); ?></span></h3> <div class="inside">

                                        <table class="form-table"><tbody>

                                           <tr valign="top" class="ver_premium_hotel">
                                                <th scope="row"><label for="paypal_price_period" ><?php _e('Set the cost' ,'booking'); ?>:</label></th>
                                                <td>
                                                 <select id="paypal_price_period" name="paypal_price_period">
                                                     <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day') echo "selected"; ?> value="day"><?php _e('for 1 day' ,'booking'); ?></option>
                                                     <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night') echo "selected"; ?> value="night"><?php _e('for 1 night' ,'booking'); ?></option>
                                                     <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed') echo "selected"; ?> value="fixed"><?php _e('fixed sum' ,'booking'); ?></option>
                                                     <option <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour') echo "selected"; ?> value="hour"><?php _e('for 1 hour' ,'booking'); ?></option>
                                                 </select>
                                                 <span class="description"><?php _e(' Select your cost configuration.' ,'booking');?></span>
                                                </td>
                                            </tr>

                                           <tr valign="top" class="ver_premium_hotel">
                                                <th scope="row"><label for="cost_currency_format" ><?php _e('Currency format' ,'booking'); ?>:</label></th>
                                                <td>

                                                 <label for="cost_currency_format" ><?php _e('Number of decimal points' ,'booking'); ?>:</label>
                                                 <select id="cost_currency_format_decimal_number" name="cost_currency_format_decimal_number" style="width:50px;">
                                                     <?php for ($dn = 0; $dn < 4; $dn++) { ?>
                                                     <option <?php if( get_bk_option( 'booking_cost_currency_format_decimal_number' ) == $dn) echo "selected"; ?> value="<?php echo $dn; ?>"><?php echo $dn; ?></option>
                                                     <?php } ?>
                                                 </select><br/>

                                                 <label for="cost_currency_format" ><?php _e('Separator for the decimal point' ,'booking'); ?>:</label>
                                                 <select id="cost_currency_format_decimal_separator" name="cost_currency_format_decimal_separator" style="width:110px;">
                                                     <?php
                                                     $possible_values       = array('', ' ', '.', ',');
                                                     $possible_descrittions = array(  __('No separator' ,'booking')
                                                                                    , __('Space' ,'booking')
                                                                                    , __('Dot' ,'booking')
                                                                                    , __('Comma' ,'booking') 
                                                                                    );
                                                     foreach ( $possible_values as $key=>$value) { ?>
                                                     <option <?php if( get_bk_option( 'booking_cost_currency_format_decimal_separator' ) == $value) echo "selected"; ?> value="<?php echo $value; ?>"><?php echo $possible_descrittions[$key]; ?></option>
                                                     <?php } ?>
                                                 </select><br/>

                                                 <label for="cost_currency_format" ><?php _e('Thousands separator' ,'booking'); ?>:</label>
                                                 <select id="cost_currency_format_thousands_separator" name="cost_currency_format_thousands_separator" style="width:110px;">
                                                     <?php
                                                     $possible_values       = array('', ' ', '.', ',');
                                                     $possible_descrittions = array(  __('No separator' ,'booking')
                                                                                    , __('Space' ,'booking')
                                                                                    , __('Dot' ,'booking')
                                                                                    , __('Comma' ,'booking') 
                                                                                    );
                                                     foreach ( $possible_values as $key=>$value) { ?>                                                                        } ?>
                                                     <option <?php if( get_bk_option( 'booking_cost_currency_format_thousands_separator' ) == $value) echo "selected"; ?> value="<?php echo $value; ?>"><?php echo $possible_descrittions[$key]; ?></option>
                                                     <?php } ?>
                                                 </select>

                                                </td>
                                            </tr>

                                           <tr valign="top" class="ver_premium_hotel">
                                                <th scope="row"><?php _e('Time impact to cost' ,'booking'); ?>:</th>
                                                <td>
                                                    <fieldset>
                                                        <label for="is_time_apply_to_cost" >
                                                            <input <?php if ($is_time_apply_to_cost == 'On') echo "checked";/**/ ?>  value="<?php echo $is_time_apply_to_cost; ?>" name="is_time_apply_to_cost" id="is_time_apply_to_cost" type="checkbox" style="margin:-3px 3px 0 0;" />
                                                            <?php printf(__('Check this box if you want the %stime selection%s on the booking form %sapplied to the cost calculation%s.' ,'booking'),'<strong>','</strong>','<strong>','</strong>');?>
                                                        </label>
                                                    </fieldset>
                                                </td>
                                            </tr>

                                            <?php make_bk_action('show_settings_for_activating_fixed_deposit'); ?>

                                           <tr valign="top" class="ver_premium_hotel">
                                                <th scope="row"><?php _e('Show booking details in payment form' ,'booking'); ?>:
                                                </th>
                                                <td>
                                                    <fieldset>
                                                        <label for="is_show_booking_summary_in_payment_form" >
                                                            <input <?php if ($is_show_booking_summary_in_payment_form == 'On') echo "checked";/**/ ?>  value="<?php echo $is_show_booking_summary_in_payment_form; ?>" name="is_show_booking_summary_in_payment_form" id="is_show_booking_summary_in_payment_form" type="checkbox" style="margin:-3px 3px 0 0;" />
                                                            <?php printf(__(' Check this checkbox if you want to show the %sbooking details summary%s  above the payment form' ,'booking'),'<strong>','</strong>','<strong>','</strong>');?>
                                                        </label>
                                                    </fieldset>
                                                </td>
                                            </tr>

                                        </tbody></table>

                           </div> </div> </div>
          <?php
    }

    // Show Auto Cancel Pending Section in General booking Settings page
    function wpdev_bk_general_settings_pending_auto_cancelation(){

            //if ( isset( $_POST['Submit'] ) ) {
            if ( isset( $_POST['start_day_weeek'] ) ) {                         //FixIn: 5.4.5


                  if (isset( $_POST['auto_approve_new_bookings_is_active'] ))      $auto_approve_new_bookings_is_active = 'On';
                  else                                                             $auto_approve_new_bookings_is_active = 'Off';
                  update_bk_option( 'booking_auto_approve_new_bookings_is_active', $auto_approve_new_bookings_is_active );

                  if (isset( $_POST['auto_cancel_pending_unpaid_bk_is_active'] ))      $auto_cancel_pending_unpaid_bk_is_active = 'On';
                  else                                                                 $auto_cancel_pending_unpaid_bk_is_active = 'Off';
                  update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active', $auto_cancel_pending_unpaid_bk_is_active );
                  if (isset($_POST['auto_cancel_pending_unpaid_bk_time']))
                    update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time', $_POST['auto_cancel_pending_unpaid_bk_time'] );

                  if (isset( $_POST['auto_cancel_pending_unpaid_bk_is_send_email'] ))      $auto_cancel_pending_unpaid_bk_is_send_email = 'On';
                  else                                                                     $auto_cancel_pending_unpaid_bk_is_send_email = 'Off';
                  update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email', $auto_cancel_pending_unpaid_bk_is_send_email );
                  if (isset($_POST['auto_cancel_pending_unpaid_bk_email_reason']))
                    update_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason', $_POST['auto_cancel_pending_unpaid_bk_email_reason'] );

            }
            $auto_approve_new_bookings_is_active       =  get_bk_option( 'booking_auto_approve_new_bookings_is_active' );
            $auto_cancel_pending_unpaid_bk_is_active   =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );
            $auto_cancel_pending_unpaid_bk_time        =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );

            $auto_cancel_pending_unpaid_bk_is_send_email =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
            $auto_cancel_pending_unpaid_bk_email_reason  =  get_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );
        ?>
            <div class='meta-box'>
                 <div <?php $my_close_open_win_id = 'bk_settings_auto_cancel_pending_nk'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Auto cancellation / auto approval of bookings' ,'booking'); ?></span></h3> <div class="inside">

                            <table class="form-table settings-table">
                                <tbody>

                                    <tr valign="top">
                                        <th scope="row"><?php _e('Auto approve all new bookings' ,'booking'); ?>:
                                        </th>
                                        <td> 
                                            <fieldset>
                                                <label for="auto_approve_new_bookings_is_active" >
                                                    <input <?php if ($auto_approve_new_bookings_is_active == 'On') echo "checked"; ?>
                                                           value="<?php echo $auto_approve_new_bookings_is_active; ?>"
                                                           name="auto_approve_new_bookings_is_active"
                                                           id="auto_approve_new_bookings_is_active" type="checkbox" />
                                                    <?php printf(__('Check this checkbox to %sactivate%s auto approve of all new pending bookings.' ,'booking'),'<b>','</b>');?>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>

                                    <tr valign="top">
                                        <th scope="row"><?php _e('Auto-cancel bookings' ,'booking'); ?>:</th>
                                        <td>
                                            <fieldset>
                                                <label for="auto_cancel_pending_unpaid_bk_is_active" >                                                
                                                    <input <?php if ($auto_cancel_pending_unpaid_bk_is_active == 'On') echo "checked";/**/ ?>  value="<?php echo $auto_cancel_pending_unpaid_bk_is_active; ?>" name="auto_cancel_pending_unpaid_bk_is_active" id="auto_cancel_pending_unpaid_bk_is_active" type="checkbox" 
                                                        onchange="javascript: 
                                                            document.getElementById('auto_cancel_pending_unpaid_bk_time').disabled=! this.checked;
                                                            document.getElementById('auto_cancel_pending_unpaid_bk_is_send_email').disabled=! this.checked;
                                                            //document.getElementById('auto_cancel_pending_unpaid_bk_email_reason').disabled=! this.checked;
                                                               "   />
                                                    <?php printf(__('Check this box to %sactivate%s auto-cancellation for pending, unpaid bookings.' ,'booking'),'<b>','</b>');?>
                                                </label>
                                            </fieldset>                                                
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding:0px;" colspan="2">
                                            <div style="margin: 0px 0 10px 50px;">
                                                <table  class="hided_settings_table" style="">                                        
                                                    <tr valign="top">
                                                      <th scope="row">
                                                        <label for="auto_cancel_pending_unpaid_bk_time" ><?php _e('Cancel bookings older' ,'booking'); ?>:</label>
                                                      </th>
                                                      <td>
                                                          <select id="auto_cancel_pending_unpaid_bk_time" name="auto_cancel_pending_unpaid_bk_time" <?php if ($auto_cancel_pending_unpaid_bk_is_active != 'On') echo ' disabled="DISABLED" '; ?> >

                                                              <option <?php if($auto_cancel_pending_unpaid_bk_time == '1') echo "selected"; ?> value="1"><?php echo '1 '; _e('hour' ,'booking'); ?></option>
                                                              <?php
                                                                for ($i = 2; $i < 24; $i++) {
                                                                  ?> <option <?php if($auto_cancel_pending_unpaid_bk_time == $i) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i,' ';  _e('hours' ,'booking'); ?></option> <?php
                                                                }
                                                              ?>
                                                              <option <?php if($auto_cancel_pending_unpaid_bk_time == '24') echo "selected"; ?> value="24"><?php echo '1 '; _e('day' ,'booking'); ?></option>
                                                              <?php
                                                                for ($i = 2; $i < 32; $i++) {
                                                                  ?> <option <?php if($auto_cancel_pending_unpaid_bk_time == ($i*24) ) echo "selected"; ?> value="<?php echo ($i*24); ?>"><?php echo $i,' ';  _e('days' ,'booking'); ?></option> <?php
                                                                }
                                                              ?>
                                                         </select>
                                                         <p class="description"><?php _e('Cancel only pending, unpaid bookings, which are older than this selection.' ,'booking');?></p>
                                                      </td>
                                                    </tr>

                                                    <tr valign="top">
                                                        <th scope="row"><?php _e('Cancellation email sent' ,'booking'); ?>:</th>
                                                        <td>
                                                            <fieldset>
                                                                <label for="auto_cancel_pending_unpaid_bk_is_send_email" >                                                                                                
                                                                    <input onchange="javascript: document.getElementById('auto_cancel_pending_unpaid_bk_email_reason').disabled=! this.checked; "  <?php if ($auto_cancel_pending_unpaid_bk_is_send_email == 'On') echo "checked";/**/ ?>  value="<?php echo $auto_cancel_pending_unpaid_bk_is_send_email; ?>" name="auto_cancel_pending_unpaid_bk_is_send_email" id="auto_cancel_pending_unpaid_bk_is_send_email" type="checkbox"  <?php if ($auto_cancel_pending_unpaid_bk_is_active != 'On') echo ' disabled="DISABLED" '; ?>  />
                                                                    <?php printf(__('Check this box to %ssend%s cancellation email for this resource.' ,'booking'),'<b>','</b>');?>
                                                                </label>
                                                            </fieldset>                                                                                                        
                                                        </td>
                                                    </tr>

                                                    <tr>
                                                        <td style="padding:0px;" colspan="2">
                                                            <div style="margin: 0px 0 10px 50px;">
                                                                <table  class="hided_settings_table" style="">                                        
                                                                    <tr valign="top">

                                                                    <th scope="row""><label for="auto_cancel_pending_unpaid_bk_email_reason" ><?php _e('Reason for cancellation' ,'booking'); ?>:</label></th>
                                                                        <td><input value="<?php echo $auto_cancel_pending_unpaid_bk_email_reason; ?>" name="auto_cancel_pending_unpaid_bk_email_reason" id="auto_cancel_pending_unpaid_bk_email_reason" class="regular-text code" type="text" size="45"  style="width:300px;" <?php if ( ($auto_cancel_pending_unpaid_bk_is_active != 'On') || ($auto_cancel_pending_unpaid_bk_is_send_email != 'On') ) echo ' disabled="DISABLED" '; ?>  />
                                                                            <p class="description"><?php printf(__('Type the reason for %scancellation%s for the email template.' ,'booking'),'<b>','</b>');?></p>
                                                                        </td>
                                                                    </tr>
                                                                </table>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
           </div> </div> </div>
        <?php

    }


    function wpdev_booking_settings_top_menu_submenu_line(){

        if ( (isset($_GET['tab'])) && ( $_GET['tab'] == 'payment') ) {
        ?>
            <div class="booking-submenu-tab-container">
                <div class="nav-tabs booking-submenu-tab-insidecontainer">                        
                    <script type="text/javascript">
                        function recheck_active_itmes_in_top_menu( internal_checkbox, top_checkbox ){
                            if (document.getElementById( internal_checkbox ).checked != document.getElementById( top_checkbox ).checked ) {
                                document.getElementById( top_checkbox ).checked = document.getElementById( internal_checkbox ).checked;
                                if ( document.getElementById( top_checkbox ).checked )
                                    jQuery('#' + top_checkbox ).parent().removeClass('booking-submenu-tab-disabled');
                                else
                                    jQuery('#' + top_checkbox ).parent().addClass('booking-submenu-tab-disabled');
                            }
                        }
                    </script><?php /** ?>
                    <a href="javascript:void(0)" 
                       onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_billing').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                       rel="tooltip" class="tooltip_bottom nav-tab booking-submenu-tab booking-submenu-tab-selected" 
                       original-title="<?php _e('General payment system settings' ,'booking');?>" >
                        <?php _e('General' ,'booking');?></a>
                    <span class="booking-submenu-tab-separator-vertical"></span><?php /**/ ?>
                    <?php make_bk_action('wpdev_bk_payment_show_tab_in_top_settings' );  ?>

                    <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_billing').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                       rel="tooltip" class="tooltip_bottom nav-tab booking-submenu-tab" original-title="<?php _e('Customization of billing fields, which automatically assign from booking form to billing form' ,'booking');?>" >
                        <?php _e('Billing form fields' ,'booking');?></a>

                    <input type="button" class="button-primary button" value="<?php _e('Save Changes' ,'booking'); ?>" 
                           style="float:right;margin:0;"
                           onclick="document.forms['post_settings_payment_integration'].submit();">
                    <div class="clear" style="height:0px;"></div>
                </div>
            </div>
          <?php
        }
    }

 //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ///////////////////////////////////////////////////

        // Activate
        function pro_activate() {
            global $wpdb;

            update_bk_option( 'booking_skin',  '/inc/skins/premium-marine.css');

            add_bk_option( 'booking_recurrent_time' , 'Off' );

            make_bk_action( 'wpdev_bk_payment_activate_system');



            add_bk_option( 'booking_highlight_timeslot_word', __('Booked Times:' ,'booking') );
            add_bk_option( 'is_show_booking_summary_in_payment_form', 'Off');
            add_bk_option( 'booking_is_time_apply_to_cost','Off' );

            add_bk_option( 'booking_billing_customer_email', '' );
            add_bk_option( 'booking_billing_firstnames', '' );
            add_bk_option( 'booking_billing_surname', '' );
            add_bk_option( 'booking_billing_phone', '' );
            add_bk_option( 'booking_billing_address1', '' );
            add_bk_option( 'booking_billing_city', '' );
            add_bk_option( 'booking_billing_country', '' );
            add_bk_option( 'booking_billing_state', '' );
            add_bk_option( 'booking_billing_post_code', '' );
            /////////////////////////////////////////////////////////////////////////////////////////////////////////////
            add_bk_option( 'booking_cost_currency_format_decimal_number',  2 );
            add_bk_option( 'booking_cost_currency_format_decimal_separator',  '.' );
            add_bk_option( 'booking_cost_currency_format_thousands_separator',  ' ' );


            add_bk_option( 'booking_auto_approve_new_bookings_is_active' , 'Off' );
            add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' , 'Off' );
            add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' ,'24');
            add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' , 'On' );
            add_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason', __('This booking canceled because we did not receive payment and the administrator did not approve it.' ,'booking') );


            add_bk_option( 'booking_range_selection_type', 'fixed');
            //add_bk_option( 'booking_range_selection_is_active', 'Off');
            add_bk_option( 'booking_range_selection_days_count','3');
            add_bk_option( 'booking_range_selection_days_max_count_dynamic',30);
            add_bk_option( 'booking_range_selection_days_specific_num_dynamic','');
            add_bk_option( 'booking_range_start_day' , '-1' );
            add_bk_option( 'booking_range_selection_days_count_dynamic','1');
            add_bk_option( 'booking_range_start_day_dynamic' , '-1' );
            add_bk_option( 'booking_range_selection_time_is_active', 'Off');
            add_bk_option( 'booking_range_selection_start_time','12:00');
            add_bk_option( 'booking_range_selection_end_time','10:00');

            add_bk_option( 'booking_time_format', 'H:i');


            add_bk_option( 'booking_email_payment_request_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
            add_bk_option( 'booking_email_payment_request_subject',__('You need to make payment for this reservation' ,'booking'));
            $blg_title = get_option('blogname'); $blg_title = str_replace('"', '', $blg_title);$blg_title = str_replace("'", '', $blg_title);
            add_bk_option( 'booking_email_payment_request_content',htmlspecialchars(sprintf(__('You need to make payment %s for reservation %s at %s. %s Please make payment on this page: %s  Thank you, %s' ,'booking'),'[cost]','[bookingtype]','[dates]','<br/><br/>[paymentreason]<br/><br/>[content]<br/><br/>', '[visitorbookingpayurl]<br/><br/>' , $blg_title.'<br/>[siteurl]')));

            add_bk_option( 'booking_is_email_payment_request_adress', 'On' );
            add_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' , 'Off' );

          if ( wpdev_bk_is_this_demo() )
             update_bk_option( 'booking_form', str_replace('\\n\\','', $this->reset_to_default_form('payment') ) );


            if  (wpbc_is_field_in_table_exists('bookingtypes','cost') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD cost VARCHAR(100) NOT NULL DEFAULT '0'";
                $wpdb->query( $simple_sql );
                $wpdb->query( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '25'" );
            }


            if  (wpbc_is_field_in_table_exists('booking','cost') == 0){ // Add remark field
                $simple_sql = "ALTER TABLE {$wpdb->prefix}booking ADD cost FLOAT(15,2) NOT NULL DEFAULT 0.00";
                $wpdb->query( $simple_sql );
            }

            if  (wpbc_is_field_in_table_exists('booking','pay_status') == 0){ // Add remark field
                $simple_sql = "ALTER TABLE {$wpdb->prefix}booking ADD pay_status VARCHAR(200) NOT NULL DEFAULT ''";
                $wpdb->query( $simple_sql );
            }

            if  (wpbc_is_field_in_table_exists('booking','pay_request') == 0){ // Add remark field
                $simple_sql = "ALTER TABLE {$wpdb->prefix}booking ADD pay_request SMALLINT(3) NOT NULL DEFAULT 0";
                $wpdb->query( $simple_sql );
            }


            if ( wpdev_bk_is_this_demo() )        {
                update_bk_option( 'booking_skin',  '/inc/skins/premium-steel.css');
                update_bk_option( 'booking_is_use_captcha' , 'Off' );
                update_bk_option( 'booking_is_show_legend' , 'On' );
                update_bk_option( 'booking_type_of_day_selections' , 'single' );

                update_bk_option( 'booking_billing_customer_email', ' email' );
                update_bk_option( 'booking_billing_firstnames', ' name' );
                update_bk_option( 'booking_billing_surname', ' secondname' );
                update_bk_option( 'booking_billing_address1', ' address' );
                update_bk_option( 'booking_billing_city', ' city' );
                update_bk_option( 'booking_billing_country', 'country' );
                update_bk_option( 'booking_billing_state', 'country' );
                update_bk_option( 'booking_billing_post_code', ' postcode' );
                update_bk_option( 'booking_billing_phone', ' phone' );
//  FixIn: 5.4.2
//                update_bk_option( 'booking_sage_vendor_name', 'wpdevelop' );
//                update_bk_option( 'booking_sage_encryption_password', 'FfCDQjLiM524VtE7' );
//                update_bk_option( 'booking_sage_curency', 'USD' );
//                update_bk_option( 'booking_sage_transaction_type', 'PAYMENT' );
//                update_bk_option( 'booking_sage_is_active', 'On' );

                update_bk_option( 'booking_view_days_num','7');

                $wpdb->query( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '30' WHERE 	booking_type_id=1" );
                $wpdb->query( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '35' WHERE 	booking_type_id=2" );
                $wpdb->query( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '40' WHERE 	booking_type_id=3" );
                $wpdb->query( "UPDATE {$wpdb->prefix}bookingtypes SET cost = '50' WHERE 	booking_type_id=4" );



                //form fields setting
                 update_bk_option( 'booking_form',  '[calendar] 
<div class="payment-form"> 
 <p>Select Times:<br />[select* rangetime multiple "10:00 AM - 12:00 PM@@10:00 - 12:00" "12:00 PM - 02:00 PM@@12:00 - 14:00" "02:00 PM - 04:00 PM@@14:00 - 16:00" "04:00 PM - 06:00 PM@@16:00 - 18:00" "06:00 PM - 08:00 PM@@18:00 - 20:00"]</p> 
 <p>First Name (required):<br />[text* name] </p> 
 <p>Last Name (required):<br />[text* secondname] </p> 
 <p>Email (required):<br />[email* email] </p> 
 <p>Phone:<br />[text phone] </p> 
 <p>Address (required):<br />  [text* address] </p> 
 <p>City (required):<br />  [text* city] </p> 
 <p>Post code (required):<br />  [text* postcode] </p> 
 <p>Country (required):<br />  [country] </p> 
 <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> 
 <p>Details:<br /> [textarea details] </p> 
 <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p> 
 <p>[captcha]</p> 
 <p>[submit class:btn "Send"]</p> 
</div>' );

                 update_bk_option( 'booking_form_show',  '<div class="payment-content-form"> 
<strong>Times</strong>:<span class="fieldvalue">[rangetime]</span><br/> 
<strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/> 
<strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/> 
<strong>Email</strong>:<span class="fieldvalue">[email]</span><br/> 
<strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/> 
<strong>Address</strong>:<span class="fieldvalue">[address]</span><br/> 
<strong>City</strong>:<span class="fieldvalue">[city]</span><br/> 
<strong>Post code</strong>:<span class="fieldvalue">[postcode]</span><br/> 
<strong>Country</strong>:<span class="fieldvalue">[country]</span><br/> 
<strong>Adults</strong>:<span class="fieldvalue"> [visitors]</span><br/> 
<strong>Children</strong>:<span class="fieldvalue"> [children]</span><br/> 
<strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span> 
</div>' );

            update_bk_option( 'booking_paypal_return_url', '/successful' );
            update_bk_option( 'booking_paypal_cancel_return_url',  '/failed' );
            update_bk_option( 'booking_sage_order_successful', '/successful' );
            update_bk_option( 'booking_sage_order_failed',  '/failed' );
            update_bk_option( 'booking_sage_is_auto_approve_cancell_booking' , 'On' );

                    $wp_queries = array();
                    $wp_queries[] = $wpdb->prepare("UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;", __('Resource #1' ,'booking'), __('Apartment#1' ,'booking') );
                    $wp_queries[] = $wpdb->prepare("UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;", __('Resource #2' ,'booking'), __('Apartment#2' ,'booking') );
                    $wp_queries[] = $wpdb->prepare("UPDATE {$wpdb->prefix}bookingtypes SET title = %s WHERE title = %s ;", __('Resource #3' ,'booking'), __('Apartment#3' ,'booking') );
                    foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );

                    update_bk_option( 'booking_time_format' , 'g:i a' );
            }



        }

        //Decativate
        function pro_deactivate(){


            delete_bk_option( 'booking_recurrent_time' );

            make_bk_action( 'wpdev_bk_payment_deactivate_system');


            
            delete_bk_option( 'booking_highlight_timeslot_word');
            delete_bk_option( 'is_show_booking_summary_in_payment_form');
            delete_bk_option( 'booking_is_time_apply_to_cost' );

            delete_bk_option( 'booking_cost_currency_format_decimal_number');
            delete_bk_option( 'booking_cost_currency_format_decimal_separator');
            delete_bk_option( 'booking_cost_currency_format_thousands_separator');

            delete_bk_option( 'booking_email_payment_request_adress');
            delete_bk_option( 'booking_email_payment_request_subject');
            delete_bk_option( 'booking_email_payment_request_content');
            delete_bk_option( 'booking_is_email_payment_request_adress' );
            delete_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' );

            delete_bk_option( 'booking_billing_customer_email' );
            delete_bk_option( 'booking_billing_firstnames' );
            delete_bk_option( 'booking_billing_surname' );
            delete_bk_option( 'booking_billing_phone' );
            delete_bk_option( 'booking_billing_address1' );
            delete_bk_option( 'booking_billing_city' );
            delete_bk_option( 'booking_billing_country' );
            delete_bk_option( 'booking_billing_state' );
            delete_bk_option( 'booking_billing_post_code' );


            delete_bk_option( 'booking_auto_approve_new_bookings_is_active'  );
            delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_active' );
            delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_time' );
            delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_is_send_email' );
            delete_bk_option( 'booking_auto_cancel_pending_unpaid_bk_email_reason' );


            delete_bk_option( 'booking_range_selection_type');
            delete_bk_option( 'booking_range_selection_is_active');
            delete_bk_option( 'booking_range_selection_days_count');
            delete_bk_option( 'booking_range_selection_days_max_count_dynamic');
            delete_bk_option( 'booking_range_selection_days_specific_num_dynamic');

            delete_bk_option( 'booking_range_start_day'   );
            delete_bk_option( 'booking_range_selection_days_count_dynamic');
            delete_bk_option( 'booking_range_start_day_dynamic'   );
            delete_bk_option( 'booking_range_selection_time_is_active');
            delete_bk_option( 'booking_range_selection_start_time');
            delete_bk_option( 'booking_range_selection_end_time');

            delete_bk_option( 'booking_time_format');
        }

}

