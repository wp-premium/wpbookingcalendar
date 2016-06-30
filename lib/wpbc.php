<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Core
 * @category Bookings
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.07.29
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


if ( ! class_exists( 'Booking_Calendar' ) ) :

    
// General Init Class    
final class Booking_Calendar {
    
    
    static private $instance = NULL;

    public $cron;
    public $notice;
    
    // Get Single Instance of this Class
    static public function getInstance() {
        
        if (  self::$instance == NULL ) {
            
            global $wpbc_settings;
            $wpbc_settings = array();

            self::$instance = new Booking_Calendar();
            
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->define_version();
            self::$instance->init();
            

            // TODO: Finish here
            // add_action('plugins_loaded', array(self::$instance, 'load_textdomain') );    // T r a n s l a t i o n            
        }
        
        return self::$instance;
    }

    
    // Define constants
    private function constants() {
        require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-constants.php' ; 
    }
    
    
    // Include Files
    private function includes() {
        require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-include.php' ; 
    }
    
        
    private function define_version() {
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // GET VERSION NUMBER                         //////////////////////////////////////////////////////////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $plugin_data = get_file_data_wpdev(  WPDEV_BK_FILE , array( 'Name' => 'Plugin Name', 'PluginURI' => 'Plugin URI', 'Version' => 'Version', 'Description' => 'Description', 'Author' => 'Author', 'AuthorURI' => 'Author URI', 'TextDomain' => 'Text Domain', 'DomainPath' => 'Domain Path' ) , 'plugin' );
        if (!defined('WPDEV_BK_VERSION'))    define('WPDEV_BK_VERSION',   $plugin_data['Version'] );                             // 0.1

    }


    // TODO: Finish here
    public function load_textdomain() {
        // Set filter for plugin's languages directory
        $iwpdev_pp_lang_dir = WPDEV_BK_PLUGIN_DIR . '/i18n/languages/';
        $iwpdev_pp_lang_dir = apply_filters( 'wpbc~languages_directory', $iwpdev_pp_lang_dir );

        // Plugin locale filter
        $locale        = apply_filters( 'plugin_locale',  get_locale() ,'booking');
        $mofile        = sprintf( '%1$s-%2$s.mo', 'booking', $locale );

        // Setup paths to current locale file
        $mofile_local  = $iwpdev_pp_lang_dir . $mofile;
        $mofile_global = WP_LANG_DIR . '/iwpdev_pp/' . $mofile;

        if ( file_exists( $mofile_global ) ) {
                // Look in global /wp-content/languages/iwpdev_pp folder
                load_textdomain( 'booking', $mofile_global );
        } elseif ( file_exists( $mofile_local ) ) {
                // Look in local /wp-content/plugins/wpfiledownload/i18n/languages/ folder
                load_textdomain( 'booking', $mofile_local );
        } else {
                // Load the default language files
                load_plugin_textdomain( 'booking', false, $iwpdev_pp_lang_dir );
        }
    }    
    
    
    // Initialization
    private function init(){
        
        if (  ( defined( 'DOING_AJAX' ) )  && ( DOING_AJAX )  ){                        // New A J A X    R e s p o n d e r

            if ( class_exists('wpdev_bk_personal')) { $wpdev_bk_personal_in_ajax = new wpdev_bk_personal(); }
            require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-ajax.php';                        // NT - Ajax 

        } else {                                                                        // Usual Loading of plugin

            // We are having Response, its executed in other file: wpbc-response.php
            if ( WP_BK_RESPONSE )
                return;

            if( is_admin() ) {
                // Define Notices System
                self::$instance->notice = new WPBC_Notices();
            }
            
            // Normal Start
            $wpdev_bk = new wpdev_booking();                                    // GO
            
            // Cron Jobs ..... /////////////////////////////////////////////////
            self::$instance->cron = new WPBC_Cron();
            ////////////////////////////////////////////////////////////////////
        }
    }
    
}

endif;


// Get Instance of Booking Calendar CLASS
function wpbookingcalendar() {
    return Booking_Calendar::getInstance();
}


// Start
wpbookingcalendar();
?>