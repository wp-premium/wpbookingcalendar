<?php
/*
Plugin Name: Booking Calendar
Plugin URI: http://wpbookingcalendar.com/demo/
Description: Online reservation and availability checking service for your site.
Author: wpdevelop
Author URI: http://wpbookingcalendar.com/
Text Domain: booking 
Domain Path: /languages/
Version: 9.Business.Large.SingleSite.6.1
*/

/*  Copyright 2009 - 2015  www.wpbookingcalendar.com  (email: info@wpbookingcalendar.com),

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>
*/

/*
-----------------------------------------------
Change Log and Features for Future Releases :
-----------------------------------------------
 * Updated Files:
 * ====================================
 * ====================================
 * New Files:
 * ====================================
 *
 * ====================================
 * 
 * Removed Files:
 * ====================================
 * 
 * ====================================
*/
    
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) die('<h3>Direct access to this file do not allow!</h3>');

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// PRIMARY URL CONSTANTS                        //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    

// ..\home\siteurl\www\wp-content\plugins\plugin-name\wpdev-booking.php
if ( ! defined( 'WPDEV_BK_FILE' ) )             define( 'WPDEV_BK_FILE', __FILE__ ); 

// wpdev-booking.php
if ( ! defined('WPDEV_BK_PLUGIN_FILENAME' ) )   define('WPDEV_BK_PLUGIN_FILENAME', basename( __FILE__ ) );                     

// plugin-name    
if ( ! defined('WPDEV_BK_PLUGIN_DIRNAME' ) )    define('WPDEV_BK_PLUGIN_DIRNAME',  plugin_basename( dirname( __FILE__ ) )  );  

// ..\home\siteurl\www\wp-content\plugins\plugin-name
if ( ! defined('WPDEV_BK_PLUGIN_DIR' ) )        define('WPDEV_BK_PLUGIN_DIR', untrailingslashit( plugin_dir_path( WPDEV_BK_FILE ) )  );

// http: //website.com/wp-content/plugins/plugin-name
if ( ! defined('WPDEV_BK_PLUGIN_URL' ) )        define('WPDEV_BK_PLUGIN_URL', untrailingslashit( plugins_url( '', WPDEV_BK_FILE ) )  );     

require_once WPDEV_BK_PLUGIN_DIR . '/lib/wpbc.php'; 