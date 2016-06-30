<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage JS and CSS
 * @category Scripts
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.05.17
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  JavaScript                                //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

// Define JavaScript Variables /////////////////////////////////////////////////
function wpbc_define_js_vars( $where_to_load = 'both' ) {
    
    // Blank JS File                             //////////////////////////////////////////////////////////////////////////////////////////////////
    wp_enqueue_script('wpbc-global-vars', WPDEV_BK_PLUGIN_URL . '/js/wpbc_vars'.((WP_BK_MIN)?'.min':'').'.js'
                                        , array( 'jquery' )
                                        , '1.0' );
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
    wp_localize_script('wpbc-global-vars', 'wpbc_global1', array(
          'wpbc_ajaxurl'         => admin_url( 'admin-ajax.php' )
        , 'wpdev_bk_plugin_url'  => plugins_url( '' , WPDEV_BK_FILE )                                                     
        , 'wpdev_bk_today'       => '['     . intval(date_i18n('Y'))            //FixIn:6.1
                                        .','. intval(date_i18n('m')) 
                                        .','. intval(date_i18n('d'))
                                        .','. intval(date_i18n('H'))
                                        .','. intval(date_i18n('i'))
                                    .']'
        , 'visible_booking_id_on_page' => '[]'
        , 'booking_max_monthes_in_calendar' => get_bk_option( 'booking_max_monthes_in_calendar')
        , 'user_unavilable_days' => '['. ( ( get_bk_option( 'booking_unavailable_day0') == 'On' ) ? '0,' : '' )
                                       . ( ( get_bk_option( 'booking_unavailable_day1') == 'On' ) ? '1,' : '' )
                                       . ( ( get_bk_option( 'booking_unavailable_day2') == 'On' ) ? '2,' : '' )
                                       . ( ( get_bk_option( 'booking_unavailable_day3') == 'On' ) ? '3,' : '' )
                                       . ( ( get_bk_option( 'booking_unavailable_day4') == 'On' ) ? '4,' : '' )
                                       . ( ( get_bk_option( 'booking_unavailable_day5') == 'On' ) ? '5,' : '' )
                                       . ( ( get_bk_option( 'booking_unavailable_day6') == 'On' ) ? '6,' : '' )
                                       .'999]' // 999 - blank day, which will not impact  to the checking of the week days. Required for correct creation of this array.
        , 'wpdev_bk_edit_id_hash' => ( ( isset( $_GET['booking_hash'] ) ) ? $_GET['booking_hash'] : '' )
        , 'wpdev_bk_plugin_filename' => WPDEV_BK_PLUGIN_FILENAME 
        , 'bk_days_selection_mode' => ( ( get_bk_option('booking_type_of_day_selections') == 'range' ) ? get_bk_option('booking_range_selection_type') : get_bk_option( 'booking_type_of_day_selections') )     /* {'single', 'multiple', 'fixed', 'dynamic'} */
        , 'wpdev_bk_personal' =>  ( (class_exists('wpdev_bk_personal')) ? '1' : '0' )
        , 'block_some_dates_from_today' =>  get_bk_option('booking_unavailable_days_num_from_today') 
        , 'message_verif_requred' => esc_js(__('This field is required' ,'booking'))
        , 'message_verif_requred_for_check_box' => esc_js(__('This checkbox must be checked' ,'booking'))
        , 'message_verif_requred_for_radio_box' => esc_js(__('At least one option must be selected' ,'booking'))
        , 'message_verif_emeil' => esc_js(__('Incorrect email field' ,'booking'))
        , 'message_verif_same_emeil' => esc_js(__('Your emails do not the same' ,'booking'))          // Email Addresses Do Not Match
        , 'message_verif_selectdts' =>  esc_js(__('Please, select booking date(s) at Calendar.' ,'booking'))
        , 'parent_booking_resources' => '[]'
        , 'new_booking_title' => esc_js( apply_bk_filter('wpdev_check_for_active_language', get_bk_option( 'booking_title_after_reservation' ) ) )
        , 'new_booking_title_time' => get_bk_option('booking_title_after_reservation_time')
        , 'type_of_thank_you_message' => esc_js( get_bk_option( 'booking_type_of_thank_you_message' ) )        
        , 'thank_you_page_URL' => wpbc_make_link_absolute( apply_bk_filter('wpdev_check_for_active_language', get_bk_option( 'booking_thank_you_page_URL' ) ) )        
        , 'is_am_pm_inside_time' => ( ( (strpos(get_bk_option('booking_time_format'), 'a')!== false) || (strpos(get_bk_option('booking_time_format'), 'A')!== false) ) ?  'true': 'false' )
        , 'is_booking_used_check_in_out_time' => 'false'
        , 'wpbc_active_locale' => getBookingLocale()        
    ));
    
    do_action( 'wpbc_define_js_vars', $where_to_load );     
}


// Load JavaScripts Files                     //////////////////////////////////
function wpbc_load_js_on_admin_side() {
    wpbc_load_js('admin');
}

function wpbc_load_js_on_client_side() {
    
    if ( ! wpbc_is_load_CSS_JS_on_this_page() ) return;                         // Check if we activated loading of JS/CSS only on specific pages and then load or no it
    
    wpbc_load_js('client');
}

function wpbc_load_js( $where_to_load = 'both' ) {

    // Load the jQuery  and jQuery Migrate
    if ( ( $where_to_load == 'client' ) || ( $where_to_load == 'both' ) ) {
        
        wp_enqueue_script('jquery');                                            // Required for the Client Side 
        
        global $wp_scripts;
        if (  is_a( $wp_scripts, 'WP_Scripts' ) ) {
            if (isset( $wp_scripts->registered['jquery'] )) {
                $version = $wp_scripts->registered['jquery']->ver;
                // Load the jQuery 1.7.1 if the them load the older jQuery and version of booking Calendar is BS or higher
                if ( version_compare( $version, '1.7.1', '<' ) ) {
                    wp_deregister_script('jquery');
                    wp_register_script('jquery', ("http://code.jquery.com/jquery-1.7.1.min.js"), false, '1.7.1');   // wp_register_script('jquery', ("http://code.jquery.com/jquery-latest.min.js"), false, false);                
                    wp_enqueue_script('jquery');
                }
                // Load the "jquery-migrate" if the jQuery version newer then 1.9
                if ( version_compare( $version, '1.9', '>=' ) ) {
                    wp_register_script('jquery-migrate', ("http://code.jquery.com/jquery-migrate-1.0.0.js"), false, '1.0.0');
                    wp_enqueue_script('jquery-migrate');
                }
            }
        }
    }
        
    if (  
            ( ( $where_to_load == 'admin' ) || ( $where_to_load == 'both' ) ) 
          && 
            ( ( is_admin() ) &&
              ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking') !== false ) &&
              ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation') === false )
            )  
       ) wp_enqueue_script( 'jquery-ui-dialog' );                               // Requireed for the Payment request dialog                                                
    
    if ( $where_to_load == 'admin' )
        wp_enqueue_script( 'jquery-ui-sortable' );
    
    // Define JS Variables
    wpbc_define_js_vars( $where_to_load );
    
    // Datepick
    wp_enqueue_script( 'wpbc-datepick', WPDEV_BK_PLUGIN_URL . '/js/datepick/jquery.datepick'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
    
    // Localization
    $locale = getBookingLocale();                                               // Load translation for calendar. Exmaple: $locale = 'fr_FR';   
    if ( ( ! empty( $locale ) ) && ( substr($locale,0,2) !== 'en') && ( file_exists( WPDEV_BK_PLUGIN_DIR . '/js/datepick/jquery.datepick-'. substr($locale,0,2) . '.js' ) ) ) {        
        wp_enqueue_script( 'wpbc-datepick-localize', WPDEV_BK_PLUGIN_URL . '/js/datepick/jquery.datepick-' . substr($locale,0,2) . ((WP_BK_MIN)?'.min':'') . '.js', array( 'wpbc-datepick' ), '1.0');            
    } else if ( ( ! empty( $locale ) ) 
             && ( ! in_array( $locale, array( 'en_US', 'en_CA', 'en_GB', 'en_AU' ) ) )     // Exceptions 
             && ( file_exists( WPDEV_BK_PLUGIN_DIR . '/js/datepick/jquery.datepick-'. strtolower( substr($locale,3) ) . '.js' ) ) ) 
           {        
        wp_enqueue_script( 'wpbc-datepick-localize', WPDEV_BK_PLUGIN_URL . '/js/datepick/jquery.datepick-' . strtolower( substr($locale,3) ) . ((WP_BK_MIN)?'.min':'') . '.js', array( 'wpbc-datepick' ), '1.0');            
    } 
    
    // Main  Script
    // Old wp_enqueue_script( 'wpbc-main', WPDEV_BK_PLUGIN_URL . '/js/wpdev.bk'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-datepick' ), '1.0');
    if ( ( $where_to_load == 'client' ) || ( $where_to_load == 'both' ) ) 
        wp_enqueue_script( 'wpbc-main-client', WPDEV_BK_PLUGIN_URL . '/js/client'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-datepick' ), '1.0');
    
    if ( ( $where_to_load == 'admin' ) || ( $where_to_load == 'both' ) ) 
        wp_enqueue_script( 'wpbc-main-admin', WPDEV_BK_PLUGIN_URL . '/js/admin'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
    
    // Load Bootstrap
    if ( is_admin() ) {
        if ( get_bk_option( 'booking_is_not_load_bs_script_in_admin' ) !== 'On') {
            wp_enqueue_script( 'wpbc-bts', WPDEV_BK_PLUGIN_URL . '/interface/bs/js/bs.min'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
        }
    } else {
        if ( ( class_exists('wpdev_bk_biz_s' ) ) && ( get_bk_option( 'booking_is_not_load_bs_script_in_client' ) !== 'On' ) ) {
            wp_enqueue_script( 'wpbc-bts', WPDEV_BK_PLUGIN_URL . '/interface/bs/js/bs.min'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
        }
    }
        
    if ( ( $where_to_load == 'admin' ) || ( $where_to_load == 'both' ) ) {
        // Choozen
        wp_enqueue_script( 'wpbc-chosen', WPDEV_BK_PLUGIN_URL . '/interface/chosen/chosen.jquery.min'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
    } 
    
    do_action( 'wpbc_enqueue_js_files', $where_to_load );    
}

/*
 add_action( 'admin_enqueue_scripts',    array($this, 'registerScripts') );        // Enqueue Scripts to All Admin pages
 add_action( 'login_enqueue_scripts',    array($this, 'registerScripts') );        // Enqueue Scripts to Login page
/**/
add_action( 'wp_enqueue_scripts',                     'wpbc_load_js_on_client_side', 1000000000);   // Enqueue Scripts to All Client pages 
add_action( 'wpbc_load_admin_scripts_on_page',        'wpbc_load_js_on_admin_side', 1000000000);   // Enqueue Scripts to Admin pages,  where executed this action: "wpbc_load_admin_scripts_on_page"
add_action( 'wpbc_load_admin_client_scripts_on_page', 'wpbc_load_js', 1000000000);   // Enqueue Scripts to Booking > Add booking page



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// CSS                                        //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
function wpbc_load_css_on_admin_side() {
    wpbc_load_css('admin');
}


function wpbc_load_css_on_client_side() {
    
    if ( ! wpbc_is_load_CSS_JS_on_this_page() ) return;                         // Check if we activated loading of JS/CSS only on specific pages and then load or no it
        
    wpbc_load_css('client');        
}

function wpbc_load_css_skip_client_css() {
    wpbc_load_css('both', array('client') );
}


function wpbc_load_css( $where_to_load = 'both', $skip_files = array() ) {

    wp_enqueue_style('wpbc-css-bts', WPDEV_BK_PLUGIN_URL . '/interface/bs/css/bs.min'.((WP_BK_MIN)?'.min':'').'.css', array( ), '2.0.0');
    
    if ( ( $where_to_load == 'admin' ) || ( $where_to_load == 'both' ) ) {
        // Admin            
        wp_enqueue_style('wpbc-css-chosen',    WPDEV_BK_PLUGIN_URL . '/interface/chosen/chosen'.((WP_BK_MIN)?'.min':'').'.css', array( ), '1.0');
        wp_enqueue_style('wpbc-css-admin', WPDEV_BK_PLUGIN_URL . '/css/admin'.((WP_BK_MIN)?'.min':'').'.css', array( ), '1.1');
        wp_enqueue_style('wpbc-css-admin-booking-listing', WPDEV_BK_PLUGIN_URL . '/css/admin-booking-listing'.((WP_BK_MIN)?'.min':'').'.css', array( 'wpbc-css-admin'  ), '1.1');
        wp_enqueue_style('wpbc-css-admin-mobile', WPDEV_BK_PLUGIN_URL . '/css/admin-mobile'.((WP_BK_MIN)?'.min':'').'.css', array( 'wpbc-css-admin' ), '1.1');        
        // wp_enqueue_style(  'wpdev-bk-jquery-ui', WPDEV_BK_PLUGIN_URL. '/css/jquery-ui.css', array(), false, 'screen' );        
    }
    
    if ( ( $where_to_load == 'client' ) || ( $where_to_load == 'both' ) ) {
        // Client    
        if (! in_array('client', $skip_files) )
            wp_enqueue_style('wpbc-css-client', WPDEV_BK_PLUGIN_URL . '/css/client'.((WP_BK_MIN)?'.min':'').'.css', array( ), '1.1');    
        wp_enqueue_style('wpbc-css-calendar', WPDEV_BK_PLUGIN_URL . '/css/calendar'.((WP_BK_MIN)?'.min':'').'.css', array( ), '1.0');
        
        // Calendar Skin ///////////////////////////////////////////////////////
        $calendar_skin_path = false;
        
        // Check if this skin exist in the plugin  folder //////////////////////
        if ( file_exists( WPDEV_BK_PLUGIN_DIR . str_replace( WPDEV_BK_PLUGIN_URL, '', get_bk_option( 'booking_skin') ) ) ) {
            $calendar_skin_path = WPDEV_BK_PLUGIN_URL . str_replace( WPDEV_BK_PLUGIN_URL, '', get_bk_option( 'booking_skin') );
        }
        
        // Check  if this skin exist  int he Custom User folder at  the http://example.com/wp-content/uploads/wpbc_skins/
        $upload_dir = wp_upload_dir(); 
        $custom_user_skin_folder = $upload_dir['basedir'] ;
        $custom_user_skin_url    = $upload_dir['baseurl'] ;
        if ( file_exists( $custom_user_skin_folder . str_replace(  array( WPDEV_BK_PLUGIN_URL , $custom_user_skin_url ), '', get_bk_option( 'booking_skin') ) ) ) {
            $calendar_skin_path = $custom_user_skin_url . str_replace( array(WPDEV_BK_PLUGIN_URL, $custom_user_skin_url ), '', get_bk_option( 'booking_skin') );
        }
        
        if ( ! empty($calendar_skin_path) )
            wp_enqueue_style('wpbc-css-calendar-skin', $calendar_skin_path , array( ), '1.0');
    }
    
    do_action( 'wpbc_enqueue_css_files', $where_to_load );
}

add_action( 'wp_enqueue_scripts',                      'wpbc_load_css_on_client_side' , 1000000000);    // Enqueue Scripts to All Client pages 
add_action( 'wpbc_load_admin_styles_on_page',          'wpbc_load_css_on_admin_side', 1000000000);      // Enqueue Scripts to Admin pages,  where executed this action: "wpbc_load_admin_scripts_on_page"           
add_action( 'wpbc_load_admin_client_styles_on_page',   'wpbc_load_css', 1000000000);                    // Enqueue Scripts to Booking > Add booking page

add_action( 'wpbc_load_css_skip_client_css',           'wpbc_load_css_skip_client_css', 1000000000);    // Enqueue CSS to Booking admin page.

/*
 add_action( 'admin_enqueue_scripts',    array($this, 'registerScripts') );        // Enqueue Scripts to All Admin pages
 add_action( 'login_enqueue_scripts',    array($this, 'registerScripts') );        // Enqueue Scripts to Login page 
/**/
/* Example of loading Hook  in plugin files:
        $pagehook1 = add_menu_page( __('Booking calendar' ,'booking'),  $update_title , $users_roles[0],
        WPDEV_BK_FILE . 'wpdev-booking', array(&$this, 'on_show_booking_page_main'),  WPDEV_BK_PLUGIN_URL . '/img/bc-16x16.png'  );
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'on_add_admin_js_files'));         
   This hook: "wpbc_load_admin_scripts_on_page" ( function on_add_admin_js_files { do_action('wpbc_load_admin_scripts_on_page', $pagehook1); } ) must be executed on the every Admin Page of current plugin (Menu pages, Custom posts, etc...)
*/



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Remove Conflict Scripts                    //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
function wpbc_remove_conflict_scripts(){
    if (strpos($_SERVER['REQUEST_URI'], 'wpdev-booking.phpwpdev-booking') !== false) {
        if (function_exists('wp_dequeue_script')) {
           wp_dequeue_script( 'cgmp-jquery-tools-tooltip' );                               // Remove this script jquery.tools.tooltip.min.js, which is load by the "Comprehensive Google Map Plugin"
        }
    }
    
    if (strpos($_SERVER['REQUEST_URI'], 'wpdev-booking.phpwpdev-booking') !== false) {
        if (function_exists('wp_dequeue_style')) {
           wp_dequeue_style( 'toolset-font-awesome-css' );                               // Remove this script sitepress-multilingual-cms/res/css/font-awesome.min.css?ver=3.1.6, which is load by the "sitepress-multilingual-cms"                      
           wp_dequeue_style( 'toolset-font-awesome' );                          //FixIn: 5.4.5.8
        }
    }
    
}

// Remove the scripts, which generated conflicts
add_action('admin_init', 'wpbc_remove_conflict_scripts', 999);


function wpbc_remove_conflict_scripts_on_admin_enqueue(){
    if (strpos($_SERVER['REQUEST_URI'], 'wpdev-booking.phpwpdev-booking') !== false) {
        if (function_exists('wp_dequeue_script')) {
           
            wp_dequeue_style( 'chosen');                          
//            wp_dequeue_style( 'cs-alert' );
//            wp_dequeue_style( 'cs-framework' );
//            wp_dequeue_style( 'cs-font-awesome' );
//            wp_dequeue_style( 'icomoon' );           
            
//            wp_dequeue_script( 'jquery.cookie' );
//            wp_dequeue_script( 'jquery-interdependencies' );
            wp_dequeue_script( 'chosen' );
            wp_dequeue_script( 'cs-framework' );
            
        }
    }
}

add_action('admin_enqueue_scripts', 'wpbc_remove_conflict_scripts_on_admin_enqueue', 999);


/**
 * Check if we activated loading of JS/CSS only on specific pages and then load or no it
 *
 * @return TRUE | FALSE
 */
function wpbc_is_load_CSS_JS_on_this_page() {
    
    $booking_is_load_js_css_on_specific_pages = get_bk_option( 'booking_is_load_js_css_on_specific_pages'  );
    if ( $booking_is_load_js_css_on_specific_pages == 'On' ) {
        $booking_pages_for_load_js_css = get_bk_option( 'booking_pages_for_load_js_css' );
        
        $booking_pages_for_load_js_css = preg_split('/[\r\n]+/', $booking_pages_for_load_js_css, -1, PREG_SPLIT_NO_EMPTY);

        $request_uri = $_SERVER['REQUEST_URI'];                                 // FixIn:5.4.1
        if ( strpos( $request_uri, 'booking_hash=') !== false ) {
            $request_uri = parse_url($request_uri);
            if (  ( ! empty($request_uri ) ) && ( isset($request_uri['path'] ) )  ){
                $request_uri = $request_uri['path'];
            } else {
                $request_uri = $_SERVER['REQUEST_URI'];
            }
        }

        if (  ( ! empty($booking_pages_for_load_js_css ) ) && ( ! in_array( $request_uri, $booking_pages_for_load_js_css ) )  )
                return false;
    }

    return true;
}

?>