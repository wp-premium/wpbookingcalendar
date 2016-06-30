<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.

*/
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Sync API
 * @category Data Sync
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.08.08
 * @since 5.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/sync/wpbc-sync-gcal-feed.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/sync/wpbc-sync-gcal-feed.php' ); } 
// if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/sync/wpbc-sync-gcal-api.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/sync/wpbc-sync-gcal-api.php' ); } 
// if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-sync-ical.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-sync-ical.php' ); } 

?>