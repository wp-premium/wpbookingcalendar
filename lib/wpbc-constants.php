<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Define Constants
 * @category Bookings
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.05.17
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   USERS  CONFIGURABLE  CONSTANTS           //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!defined('WP_BK_SHOW_INFO_IN_FORM'))                define('WP_BK_SHOW_INFO_IN_FORM',  false );          // This feature can impact to the performace
if (!defined('WP_BK_SHOW_BOOKING_NOTES'))               define('WP_BK_SHOW_BOOKING_NOTES', false );         // Set notes of the specific booking visible by default.
if (!defined('WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS'))   define('WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS',  false );
if (!defined('WP_BK_SHOW_DEPOSIT_AND_TOTAL_PAYMENT'))   define('WP_BK_SHOW_DEPOSIT_AND_TOTAL_PAYMENT',  false ); // Show both deposit and total cost payment forms, after visitor submit booking. Important! Please note, in this case at admin panel for booking will be saved deposit cost and notes about deposit, do not depend from the visitor choise of this payment. So you need to check each such payment manually.
if (!defined('WP_BK_STRICTLY_FROM_EMAILS'))             define('WP_BK_STRICTLY_FROM_EMAILS',  true );            // If true, plugin will send emails with "From" address that  defined in "From" field at Booking > Settings > Emails page. Otherwise (if false), when sending the copy of Confirmation email to admin, sends a "from" field of email not the email of server, but email from the person, who made reservation. Its useful for "reply to this emails", but when receiving such email, Yahoo mail for instance rejects it, and google mail puts a warning about fishing etc.
if (!defined('WP_BK_IS_SEND_EMAILS_ON_COST_CHANGE'))    define('WP_BK_IS_SEND_EMAILS_ON_COST_CHANGE',  false );  //FixIn: 6.0.1.7   // Is send modification email, if cost  was changed in admin panel
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   SYSTEM  CONSTANTS                        //////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
if (!defined('WP_BK_VERSION_NUM'))      define('WP_BK_VERSION_NUM',     '6.1' );
if (!defined('WP_BK_MINOR_UPDATE'))     define('WP_BK_MINOR_UPDATE',    ! true );    
if (!defined('IS_USE_WPDEV_BK_CACHE'))  define('IS_USE_WPDEV_BK_CACHE', true );    
if (!defined('WP_BK_DEBUG_MODE'))       define('WP_BK_DEBUG_MODE',      false );
if (!defined('WP_BK_MIN'))              define('WP_BK_MIN',             false );//TODO: Finish  with  this contstant, right now its not working correctly with TRUE status
if (!defined('WP_BK_RESPONSE'))         define('WP_BK_RESPONSE',        false );
?>