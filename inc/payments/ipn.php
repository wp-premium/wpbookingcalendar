<?php
/*
Getting Started
---------------

You should understand how the IPN
process works conceptually and you should understand when and why you would be
using IPN. Reading the [PayPal Instant Payment Notification Guide][1] is a good
place to start.

You should also have a [PayPal Sandbox Account][2] with a test buyer account and
a test seller account. When logged into your sandbox account there is an IPN
simulator under the 'Test Tools' menu which you can used to test your IPN
listener.

[1]: https://cms.paypal.com/cms_content/US/en_US/files/developer/IPNGuide.pdf
[2]: https://developer.paypal.com

Known Issues
------------

__Problem__

The `processIpn()` method throws the following exception:

    cURL error: [52] GnuTLS recv error (-9): A TLS packet with unexpected length was received.

__Solution__

When cURL is compiled with GnuTLS the call to PayPal will fail if the SSL version
is not explicitly set as a cURL option. Set the `force_ssl_v3` property to force
SSL 3:

    $listener = new IpnListener();
    $listener->force_ssl_v3 = true;

_Note: force_ssl_v3 is now true by default_



__Problem__

     PHP Warning: curl_setopt() [function.curl-setopt]: CURLOPT_FOLLOWLOCATION
     cannot be activated when in safe_mode or an open_basedir is set in ...

__Solution__

If you need PHP safe mode, you can disable CURLOPT_FOLLOWLOCATION using the
`follow_location` property.

    $listener = new IpnListener();
    $listener->follow_location = false;

_Note: follow_location is now false enabled by default_

*/
error_reporting(E_ALL ^ E_NOTICE);
$_GET['wpdev_bkpaypal_ipn'] = 1;

define('WP_BK_RESPONSE_IPN_MODE', true );
// Load the main libraries
require_once( dirname(__FILE__) . '/../../inc/payments/wpbc-response.php' );
/* Set errors recording (Only in DEBUG mode) ///////////////////////////////////
 Catch these fatal errors and log to the ipn_errors.log.
 By default this file is not exist in the production version.
 So if you are need to make debug, firstly you are need to create the ipn_errors.log
 at te same folder as this file with correct permission.
 After you are finish debug process, please delete ipn_errors.log file!!! /**/
define('WPDEV_BK_IPN_DEBUG_MODE', false );
if (WPDEV_BK_IPN_DEBUG_MODE) {
    ini_set('log_errors', true);
    ini_set('error_log', dirname(__FILE__).'/ipn_errors.log');
}


// instantiate the IpnListener class ///////////////////////////////////////////
include('ipnlistener.php');
$listener = new IpnListener();


// SandBox
$paypal_is_sandbox  =  get_bk_option( 'booking_paypal_is_sandbox' );
if ($paypal_is_sandbox == 'On') $listener->use_sandbox = true;
else                            $listener->use_sandbox = false;


/*
By default the IpnListener object is going to post the data back to PayPal using cURL over a secure SSL connection.
This is the recommended way to post the data back, however, some people may have connections problems using this method. */

//To post over standard HTTP connection, or use SSL
$paypal_ipn_use_ssl     =  get_bk_option( 'booking_paypal_ipn_use_ssl' );
if ($paypal_ipn_use_ssl == 'On')    $listener->use_ssl = true;                  // Default
else                                $listener->use_ssl = false;

//To post using the fsockopen() function or use cURL
$paypal_ipn_use_curl    =  get_bk_option( 'booking_paypal_ipn_use_curl' );
if ($paypal_ipn_use_curl == 'On')   $listener->use_curl = true;
else                                $listener->use_curl = false;                // Default

/*
The processIpn() method will encode the POST variables sent by PayPal and then
POST them back to the PayPal server. An exception will be thrown if there is 
a fatal error (cannot connect, your server is not configured properly, etc.).

The processIpn() method will send the raw data on 'php://input' to PayPal.
Optionally possible to pass the data to processIpn():
$verified = $listener->processIpn($my_post_data);
*/
try {
    $listener->requirePostMethod();
    $verified = $listener->processIpn();
} catch (Exception $e) {

   if (WPDEV_BK_IPN_DEBUG_MODE) error_log($e->getMessage());

   $paypal_ipn_is_send_error_email  =  get_bk_option( 'booking_paypal_ipn_is_send_error_email' );
   if ($paypal_ipn_is_send_error_email == 'On')  {
        $paypal_ipn_error_email          =  get_bk_option( 'booking_paypal_ipn_error_email' );
        mail($paypal_ipn_error_email , __('Error IPN' ,'booking'), $e->getMessage() );
   }
   exit(0);
}



// The processIpn() method returned true if the IPN was "VERIFIED" and false if it was "INVALID".
if ($verified) {

    /*
    Once you have a verified IPN you need to do a few more checks on the POST
    fields--typically against data you stored in your database during when the
    end user made a purchase (such as in the "success" page on a web payments
    standard button). The fields PayPal recommends checking are:
    
        1. Check the $_POST['payment_status'] is "Completed"
	2. Check that $_POST['txn_id'] has not been previously processed 
	3. Check that $_POST['receiver_email'] is your Primary PayPal email 
	4. Check that $_POST['payment_amount'] and $_POST['payment_currency'] 
	       are correct
    
    Since implementations on this varies, I will leave these checks out of this
    example and just send an email using the getTextReport() method to get all
    of the details about the IPN.  
    */

   $is_ok_tranzaction = true;

   //1. Check the HASH for the specific booking
   if (isset($_POST['custom']) ){
       $booking_hash = $_POST['custom'];
       
       $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $booking_hash );
        if ($my_booking_id_type !== false) {
            $my_boooking_id        = $my_booking_id_type[0];
            $my_boooking_resource_id      = $my_booking_id_type[1];
        } else {
           $is_ok_tranzaction = false;
           $tranzaction_error_description = 'There are no booking with specific HASH from IPN request.';            
        }
   } else {
           $is_ok_tranzaction = false;
           $tranzaction_error_description = 'There are no HASH parameter in IPN request.';
   }

   //2. Check booking ID
   if (isset($_POST['item_number']) ){
       $booking_id = $_POST['item_number'];
       if ($my_boooking_id != $booking_id) {
           $is_ok_tranzaction = false;
           $tranzaction_error_description = 'The booking HASH parameter in IPN request is relate to the different booking ID in the same request.';
       }
   }

    //3. Check Receiver Email or ID
    $paypal_pro_hosted_solution =  get_bk_option( 'booking_paypal_pro_hosted_solution' );
    if ($paypal_pro_hosted_solution == 'On') {
       $paypal_secure_merchant_id  =  get_bk_option( 'booking_paypal_secure_merchant_id'  );
       if ($_POST['business'] != $paypal_secure_merchant_id) {
           $is_ok_tranzaction = false;
           $tranzaction_error_description = 'The merchant ID is different from the ID in settings';
       }        
    } else {
       $paypal_emeil               =  get_bk_option( 'booking_paypal_emeil' );
       if ($_POST['receiver_email'] != $paypal_emeil) {
           $is_ok_tranzaction = false;
           $tranzaction_error_description = 'The receiver email is different from the Primary PayPal email';
       }
        
    }

   //4. Check the payment currency
   $paypal_curency             =  get_bk_option( 'booking_paypal_curency' );
   if ($_POST['mc_currency'] != $paypal_curency) {
       $is_ok_tranzaction = false;
       $tranzaction_error_description = 'The currency of payment for booking is different';
   }


   // Get the info [cost] about the specific booking - $my_boooking_id
   $booking_cost = apply_bk_filter('get_booking_cost_from_db', '', $my_boooking_id);

   //5. Check the payment amount
   if ($_POST['mc_gross'] != $booking_cost) {
       $is_ok_tranzaction = false;
       $tranzaction_error_description = 'The cost of the booking is different';
   }


   // $txn_id = $_POST['txn_id']; // TODO: set recechecking of this parmeter also, for this is need to record history of these requests.


   if ($is_ok_tranzaction) {  // All checking is PASS

       // Update the booking status here
       $payment_status = $_POST['payment_status'];
       make_bk_action('wpdev_change_payment_status', $my_boooking_id, $payment_status);

       // Auto approve or cancel the specific booking
       $auto_approve = get_bk_option( 'booking_paypal_is_auto_approve_cancell_booking'  );
       if ($auto_approve == 'On') {
           
            if ($payment_status == 'Completed')            
                    check_auto_approve_or_cancell($my_boooking_id, true );

            if ( ($payment_status == 'Denied') ||  ($payment_status == 'Failed' ) ||  ($payment_status == 'Refunded') )        
                    check_auto_approve_or_cancell($my_boooking_id, false );
       }
       if (WPDEV_BK_IPN_DEBUG_MODE) error_log($listener->getTextReport());

       $paypal_ipn_is_send_verified_email  =  get_bk_option( 'booking_paypal_ipn_is_send_verified_email' );
       if ($paypal_ipn_is_send_verified_email == 'On')  {
            $paypal_ipn_verified_email          =  get_bk_option( 'booking_paypal_ipn_verified_email' );
            mail($paypal_ipn_verified_email,  __('Verified IPN' ,'booking'), $listener->getTextReport());
       }

   } else { // Some checking is FAIL

       $paypal_ipn_is_send_error_email  =  get_bk_option( 'booking_paypal_ipn_is_send_error_email' );
       if ($paypal_ipn_is_send_error_email == 'On')  {
            $paypal_ipn_error_email          =  get_bk_option( 'booking_paypal_ipn_error_email' );
            mail($paypal_ipn_error_email , $tranzaction_error_description , $listener->getTextReport() );
       }

   }

} else {

    /* An Invalid IPN *may* be caused by a fraudulent transaction attempt. It's
    a good idea to have a developer or sys admin manually investigate any  invalid IPN. */

   if (WPDEV_BK_IPN_DEBUG_MODE) error_log($listener->getTextReport());

   $paypal_ipn_is_send_invalid_email  =  get_bk_option( 'booking_paypal_ipn_is_send_invalid_email' );
   if ($paypal_ipn_is_send_invalid_email == 'On')  {
        $paypal_ipn_invalid_email          =  get_bk_option( 'booking_paypal_ipn_invalid_email' );
        mail($paypal_ipn_invalid_email ,   __('Invalid IPN' ,'booking'), $listener->getTextReport());
   }

}

?>