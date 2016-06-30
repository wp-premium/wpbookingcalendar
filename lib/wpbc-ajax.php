<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Ajax Responder
 * @category Bookings
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.05.26
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

////////////////////////////////////////////////////////////////////////////////
//    S u p p o r t    f u n c t i o n s    f o r     A j a x    ///////////////
////////////////////////////////////////////////////////////////////////////////

// Verify the nonce.    
function wpdev_check_nonce_in_admin_panel( $action_check = 'wpbc_ajax_admin_nonce' ){    
    
    $nonce = ( isset($_REQUEST['wpbc_nonce']) ) ? $_REQUEST['wpbc_nonce'] : '';
    
    if ( ! wp_verify_nonce( $nonce, $action_check ) ) {                         // This nonce is not valid.     
        ?>
        <script type="text/javascript">
           jQuery("#ajax_respond").after( "<div class='wpdevbk'><div class='alert alert-danger'><?php 
            printf(__('%sError!%s Request do not pass security check! Please refresh the page and try one more time.' ,'booking'),'<strong>','</strong>');
            ?></div></div>" );  
           if ( jQuery("#ajax_message").length )     
            jQuery("#ajax_message").slideUp();
        </script>
        <?php
        die;                
    } 
}


// Check and (re)Load specific Locale for the Ajax request - based on "admin_init" hook
function wpbc_check_locale_for_ajax() {
        
    add_bk_filter('wpdev_check_for_active_language', 'wpdev_check_for_active_language');   // Add Hook for ability  to check  the content for active lanaguges
        
    if  (isset($_POST['wpdev_active_locale'])) {    // Reload locale according request parameter
        global  $l10n;
        if (isset($l10n['booking'])) unset($l10n['booking']);

        if(! defined('WPDEV_BK_LOCALE_RELOAD') ) define('WPDEV_BK_LOCALE_RELOAD', $_POST['wpdev_active_locale']);

        // Reload locale settings, its required for the correct  dates format
        if (isset($l10n['default'])) unset($l10n['default']);               // Unload locale     
        add_filter('locale', 'getBookingLocale',999);                       // Set filter to load the locale of the Booking Calendar
        load_default_textdomain();                                          // Load default locale            
        global $wp_locale;
        $wp_locale = new WP_Locale();                                       // Reload class

        loadLocale(WPDEV_BK_LOCALE_RELOAD);
    }
}
 

////////////////////////////////////////////////////////////////////////////////
//    A j a x    H o o k s    f o r    s p e c i f i c    A c t i o n s    /////
////////////////////////////////////////////////////////////////////////////////

function wpbc_ajax_CALCULATE_THE_COST() {
    
        wpdev_check_nonce_in_admin_panel( $_POST['action'] );            
        make_bk_action('wpdev_ajax_show_cost');
}


function wpbc_ajax_INSERT_INTO_TABLE() {
 
    wpdev_check_nonce_in_admin_panel( $_POST['action'] );            
    wpdev_bk_insert_new_booking();        
}


function wpbc_ajax_UPDATE_READ_UNREAD () {

    wpdev_check_nonce_in_admin_panel();
    
    make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

    if ( $_POST[ "is_read_or_unread" ] == 1)    $is_new = '1';
    else                                        $is_new = '0';

    $id_of_new_bookings  = $_POST[ "booking_id" ];
    $arrayof_bookings_id = explode('|',$id_of_new_bookings);
    $user_id             = $_POST[ "user_id" ];

    renew_NumOfNewBookings(  $arrayof_bookings_id, $is_new , $user_id );

    ?>  <script type="text/javascript"> <?php 
            foreach ($arrayof_bookings_id as $bk_id) {

                if ( $bk_id == 'all' ) 
                        $bk_id = 0;

                if ($is_new == '1') { ?>
                    set_booking_row_unread(<?php echo $bk_id ?>);
                <?php } else { ?>
                    set_booking_row_read(<?php echo $bk_id ?>);                                
                <?php }                    
            } ?>
            document.getElementById('ajax_message').innerHTML = '<?php if ($is_new == '1') { echo __('Set as Read' ,'booking'); } else { echo __('Set as Unread' ,'booking'); } ?>';
            jQuery('#ajax_message').fadeOut(1000);
    </script> <?php
}


function wpbc_ajax_UPDATE_APPROVE() {
                    
    global $wpdb;
    
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

    // Approve or Reject
    $is_approve_or_pending = $_POST[ "is_approve_or_pending" ];
    if ($is_approve_or_pending == 1) 
        $is_approve_or_pending = '1';
    else                             
        $is_approve_or_pending = '0';

    $booking_id         = $_POST[ "booking_id" ];
    $approved_id        = explode('|',$booking_id);
    if (! isset($_POST["denyreason"])) 
        $_POST["denyreason"] = '';
    $denyreason     = $_POST["denyreason"];
    $is_send_emeils = $_POST["is_send_emeils"];


    if ( ( count($approved_id) > 0 ) && ( $approved_id !== false ) ) {

        $approved_id_str = join( ',', $approved_id);
        $approved_id_str = wpbc_clean_string_for_db( $approved_id_str );

        if ( false === $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingdates SET approved = %s WHERE booking_id IN ({$approved_id_str})", $is_approve_or_pending ) ) ){
            ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating to DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

        renew_NumOfNewBookings( explode(',', $approved_id_str) );

        if ($is_approve_or_pending == '1') {
            sendApproveEmails($approved_id_str, $is_send_emeils,$denyreason);
            $all_bk_id_what_canceled = apply_bk_filter('cancel_pending_same_resource_bookings_for_specific_dates', false, $approved_id_str );         
        } else
            sendDeclineEmails($approved_id_str, $is_send_emeils,$denyreason);

        ?>  <script type="text/javascript">
                <?php foreach ($approved_id as $bk_id) {
                        if ($is_approve_or_pending == '1') { ?>
                            set_booking_row_approved_in_timeline(<?php echo $bk_id ?>);
                            set_booking_row_approved(<?php echo $bk_id ?>);
                            set_booking_row_read(<?php echo $bk_id ?>);
                        <?php } else { ?>
                            set_booking_row_pending_in_timeline(<?php echo $bk_id ?>);
                            set_booking_row_pending(<?php echo $bk_id ?>);
                        <?php }?>
                <?php } ?>
                document.getElementById('ajax_message').innerHTML = '<?php if ($is_approve_or_pending == '1') { echo esc_js(__('Set as Approved' ,'booking')); } else { echo esc_js(__('Set as Pending' ,'booking')); } ?>';
                jQuery('#ajax_message').fadeOut(1000);
            </script> <?php
    }
}


function wpbc_ajax_DELETE_APPROVE() {
        
    global $wpdb;
    
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );

    $booking_id = $_POST[ "booking_id" ];         // Booking ID
    if ( ! isset($_POST["denyreason"] ) ) 
        $_POST["denyreason"] = '';
    $denyreason = $_POST["denyreason"];
    if (       ( $denyreason == __('Reason for cancellation here' ,'booking')) 
            || ( $denyreason == __('Reason of cancellation here' ,'booking')) 
            || ( $denyreason == 'Reason of cancel here') 
        ) $denyreason = '';
    $is_send_emeils = $_POST["is_send_emeils"];
    $approved_id    = explode('|',$booking_id);

    if ( (count($approved_id)>0) && ($approved_id !=false) && ($approved_id !='')) {

        $approved_id_str = join( ',', $approved_id);
        $approved_id_str = wpbc_clean_string_for_db( $approved_id_str );

        sendDeclineEmails($approved_id_str, $is_send_emeils,$denyreason);

        if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id IN ({$approved_id_str})" ) ){
            ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting dates at DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
            die();
        }

        if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id IN ({$approved_id_str})" ) ){
            ?> <script type="text/javascript"> document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting reservation at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php
            die();
        }
        ?>
            <script type="text/javascript">
                <?php foreach ($approved_id as $bk_id) { ?>
                    set_booking_row_deleted_in_timeline(<?php echo $bk_id ?>);
                    set_booking_row_deleted(<?php echo $bk_id ?>);
                <?php } ?>
                document.getElementById('ajax_message').innerHTML = '<?php echo __('Deleted' ,'booking'); ?>';
                jQuery('#ajax_message').fadeOut(1000);
            </script>
        <?php        
    }
}


function wpbc_ajax_DELETE_BY_VISITOR() {
        
    wpdev_check_nonce_in_admin_panel( $_POST['action'] );            
    make_bk_action('wpdev_delete_booking_by_visitor');
        
}


function wpbc_ajax_SAVE_BK_COST() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_save_bk_cost');        
}


function wpbc_ajax_SEND_PAYMENT_REQUEST() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );    //FixIn: 5.4.5.6
    make_bk_action('wpdev_send_payment_request');
}


function wpbc_ajax_CHANGE_PAYMENT_STATUS() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_change_payment_status');
}


function wpbc_ajax_UPDATE_BK_RESOURCE_4_BOOKING() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_updating_bk_resource_of_booking');         
}


//FixIn:5.4.5.1
function wpbc_ajax_DUPLICATE_BOOKING_TO_OTHER_RESOURCE() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpbc_duplicate_booking_to_other_resource');         
}




function wpbc_ajax_UPDATE_REMARK() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_updating_remark');
}


function wpbc_ajax_DELETE_BK_FORM() {
        
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );
    make_bk_action('wpdev_delete_booking_form');          
}


function wpbc_ajax_USER_SAVE_WINDOW_STATE() {
        
    wpdev_check_nonce_in_admin_panel();
    update_user_option($_POST['user_id'],'booking_win_' . $_POST['window'] ,$_POST['is_closed']);
}


function wpbc_ajax_BOOKING_SEARCH() {
        
    wpdev_check_nonce_in_admin_panel( $_POST['action'] );      
    make_bk_action('wpdev_ajax_booking_search');        
}


function wpbc_ajax_CHECK_BK_NEWS() {
        
    wpdev_check_nonce_in_admin_panel();
    wpdev_ajax_check_bk_news();
}


function wpbc_ajax_CHECK_BK_FEATURES() {
        
    wpdev_check_nonce_in_admin_panel();
    wpdev_ajax_check_bk_news('info/features/');
}


function wpbc_ajax_CHECK_BK_VERSION() {
    
    wpdev_check_nonce_in_admin_panel();
    wpdev_ajax_check_bk_version();
}


function wpbc_ajax_SAVE_BK_LISTING_FILTER() {
    
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_ajax_save_bk_listing_filter');
}


function wpbc_ajax_DELETE_BK_LISTING_FILTER() {
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_ajax_delete_bk_listing_filter');
}


function wpbc_ajax_EXPORT_BOOKINGS_TO_CSV() {
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('wpdev_ajax_export_bookings_to_csv');
}


function wpbc_ajax_WPBC_IMPORT_GCAL_EVENTS() {
    wpdev_check_nonce_in_admin_panel();
    make_bk_action('check_multiuser_params_for_client_side_by_user_id', $_POST['user_id'] );
    make_bk_action('wpbc_import_gcal_events');    
}

////////////////////////////////////////////////////////////////////////////////
//    R u n     A j a x                       //////////////////////////////////
////////////////////////////////////////////////////////////////////////////////
if (  is_admin() && ( defined( 'DOING_AJAX' ) ) && ( DOING_AJAX )  ) {

    // Reload Locale if its required
    add_action( 'admin_init', 'wpbc_check_locale_for_ajax' );    

    // Hooks list
    $actions_list = array( 
                             'CALCULATE_THE_COST'                   => 'both'
                            ,'INSERT_INTO_TABLE'                    => 'both'
                            ,'UPDATE_READ_UNREAD'           => 'admin'
                            ,'UPDATE_APPROVE'               => 'admin'
                            ,'DELETE_APPROVE'               => 'admin'
                            ,'DELETE_BY_VISITOR'                    => 'both'
                            ,'SAVE_BK_COST'                 => 'admin'
                            ,'SEND_PAYMENT_REQUEST'         => 'admin'
                            ,'CHANGE_PAYMENT_STATUS'                => 'both'   // Only Admin for Ajax requests (also exist exectution  of the changing status for IPN)
                            ,'UPDATE_BK_RESOURCE_4_BOOKING' => 'admin'
                            ,'DUPLICATE_BOOKING_TO_OTHER_RESOURCE' => 'admin'   //FixIn:5.4.5.1
                            ,'UPDATE_REMARK'                => 'admin'
                            ,'DELETE_BK_FORM'               => 'admin'
                            ,'USER_SAVE_WINDOW_STATE'       => 'admin'
                            ,'BOOKING_SEARCH'                       => 'both'
                            ,'CHECK_BK_NEWS'                => 'admin'
                            ,'CHECK_BK_FEATURES'            => 'admin'
                            ,'CHECK_BK_VERSION'             => 'admin'
                            ,'SAVE_BK_LISTING_FILTER'       => 'admin'
                            ,'DELETE_BK_LISTING_FILTER'     => 'admin'
                            ,'EXPORT_BOOKINGS_TO_CSV'       => 'admin'
                            ,'WPBC_IMPORT_GCAL_EVENTS'      => 'admin'          // Version:5.2
                         );
    
    foreach ($actions_list as $action_name => $action_where) {
        
        if ( ( isset($_POST['action']) ) && ( $_POST['action'] == $action_name ) ){
            
            if ( ( $action_where == 'admin' ) || ( $action_where == 'both' ) ) 
                add_action( 'wp_ajax_'        . $action_name, 'wpbc_ajax_' . $action_name);      // Admin & Client (logged in usres)
            
            if ( ( $action_where == 'both' ) || ( $action_where == 'client' ) ) 
                add_action( 'wp_ajax_nopriv_' . $action_name, 'wpbc_ajax_' . $action_name);      // Client         (not logged in)        
        }
    }    
} 
?>