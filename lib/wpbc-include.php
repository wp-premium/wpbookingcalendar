<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Files Loading
 * @category Bookings
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.07.29
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   L O A D   F I L E S                      //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-booking-functions.php';          // S u p p o r t    f u n c t i o n s
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-booking-widget.php';             // W i d g e t s
require_once WPDEV_BK_PLUGIN_DIR . '/js/captcha/captcha.php';                   // C A P T C H A
require_once WPDEV_BK_PLUGIN_DIR . '/languages/wpdev-country-list.php';         // Load Country list

if(1) if (file_exists(WPDEV_BK_PLUGIN_DIR.'/inc/personal.php')){                // O t h e r
require_once WPDEV_BK_PLUGIN_DIR . '/inc/personal.php'; } 
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-bk-lib.php';                     // S u p p o r t    l i b  
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-bk-timeline.php';                // T i m e l i n e    l i b
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-booking-class.php';              // C L A S S    B o o k i n g
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-booking-new.php';                 // N e w    
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-scripts.php';                     // Load CSS and JS
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-cron.php';                        // CRON  @since: 5.2.0

if( is_admin() ) {
    require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-class-settings.php';         // S e t t i n g s  Class
    
    require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-settings-general.php';       // S e t t i n g s        
    require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpdev-bk-edit-toolbar-buttons.php';// B o o k i n g    B u t t o n s   in   E d i t   t o o l b a r    
    
    require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-class-dismiss.php';           // C L A S S  -  Dismiss                 
    require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-welcome.php';                 // W E L C O M E     Page        
    require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-notices.php';                 // Showing different messages and alerts
    
} else {
    
}

require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-gcal-class.php';                  // Google Calendar Feeds Import @since: 5.2.0  - v.3.0 API support @since: 5.4.0
require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc-gcal.php';                        // Sync Google Calendar Events with  WPBC @since: 5.2.0  - v.3.0 API support @since: 5.4.0
if(1) if (file_exists(WPDEV_BK_PLUGIN_DIR.'/inc/sync/index.php')){              // Other Sync(s)
require_once WPDEV_BK_PLUGIN_DIR . '/inc/sync/index.php'; } 
?>