<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

error_reporting(E_ALL ^ E_NOTICE);
$_GET['wpdev_bkpaypal_ipn'] = 1;                                                // This parmeter  we need for the wpbc-response.php file. Its the same as with  PayPal IPN.

define('WP_BK_RESPONSE_IPN_MODE', true );                                       // This parmeter  we need for the wpbc-response.php file. Its the same as with  PayPal IPN.
// Load the main libraries
require_once( dirname(__FILE__) . '/../../inc/payments/wpbc-response.php' );


// Checking response from  payment system
function wpbc_check_response_status__ipay88_for_backendpost( $status, $booking_id, $wp_nonce) {

    if ( ( isset($_REQUEST['Status']) )&& ($_REQUEST['Status'] == 1 ) ){

        $MerchantCode = $_REQUEST['MerchantCode'];
        $RefNo = $_REQUEST['RefNo'];
        // Amount  Currency- Payment amount with two decimals and thousand symbols.  Example: 1,278.99 
        // Check  iPay88 Technical Spec v.1.6.1 on page #9
        $Amount = $_REQUEST['Amount'];

        $status = '';

        // Check the REFERER site
        if ($status == '')
            if(isset($_SERVER['HTTP_REFERER'])) {
                $pos1 = strpos($_SERVER['HTTP_REFERER'], 'https://www.mobile88.com');
                $pos2 = strpos($_SERVER['HTTP_REFERER'], 'http://www.mobile88.com/');

                if (( $pos1 === false) && ($pos2 === false)) {
//                    debuge( 'Respond not from correct payment site !' );
                    die('Respond not from correct payment site !');
                    $status = 'ipay88:Failed';
                }
            }
        // Requery
        if ($status == '') {
            $result = iPay88_Requery($MerchantCode, $RefNo, $Amount);
            if ( $result === '00') {
                $iPayStatusMessage = __('Successful payment' ,'booking');
            } else {
                if ( $result == 'Invalid parameters') $iPayStatusMessage = __(' Parameters are incorrect,' ,'booking');
                else if ( $result == 'Record not found') $iPayStatusMessage = __('Cannot find the record' ,'booking');
                else if ( $result == 'Incorrect amount') $iPayStatusMessage = __('Amount different' ,'booking');
                else if ( ($result == 'Payment fail') || ($result =='Payment failed') )$iPayStatusMessage = __('Payment failed' ,'booking');
                else if ( $result == 'M88Admin') $iPayStatusMessage = __('Payment status updated by Mobile88 Admin(Fail)' ,'booking');
                else if ( $result == 'Connection Error') $iPayStatusMessage = __('Connection Error' ,'booking');

                $status = 'ipay88:Failed';
//                debuge($_REQUEST['ErrDesc'], $iPayStatusMessage );
                die($result);                
            }
        }

//        if(0){ //Disabled check
//            // Check payment ammount
//            if ($status == '')
//                if ($slct_sql_results[0]->cost != $Amount ) {
////                    debuge( 'Payment amount is different from original !' );
//                    die('Payment amount is different from original !');
//                    $status = 'ipay88:Failed';
//                }
//        }
        // Check signature
        if ($status == '') {

            $summ_sing = str_replace('.', '', $Amount /*$slct_sql_results[0]->cost*/);
            $summ_sing = str_replace(',', '', $summ_sing );
            $ipay88_merchant_code = get_bk_option( 'booking_ipay88_merchant_code' );
            $ipay88_merchant_key = get_bk_option( 'booking_ipay88_merchant_key' );
            // $signature = $ipay88_merchant_key . $ipay88_merchant_code . $_REQUEST['RefNo'] . $summ_sing .  $_REQUEST['Currency'] ;
            $signature = $ipay88_merchant_key . $ipay88_merchant_code . $_REQUEST['PaymentId']. $_REQUEST['RefNo'] . $summ_sing .  $_REQUEST['Currency'] . $_REQUEST['Status'];

            $signature = iPay88_signature($signature);

            if ($_REQUEST["Signature"] != $signature ) {
//                debuge( 'Signature is different from original !' );
                die('Signature is different from original !');
                $status = 'ipay88:Failed';
            }
        }

        if ($status == '') $status = 'ipay88:OK';

    } else {
        $status = 'ipay88:Failed';
//        if ( isset($_REQUEST['ErrDesc']) )
//            debuge($_REQUEST['ErrDesc']);

        //debuge($booking_id, $status);die;
        /* // Parameters in Respond
        [payed_booking] => 44
        [wp_nonce] => 30068
        [pay_sys] => ipay88
        [stats] => OK
        [MerchantCode] => 1111111
        [PaymentId] => 0
        [RefNo] => A044
        [Amount] => 240
        [Currency] => PHP
        [Remark] =>
        [TransId] => T0203282500
        [AuthCode] =>
        [Status] => 0
        [ErrDesc] => Invalid parameters(Currency Not Supported By Merchant Account)
        [Signature] =>
        /**/
    }

    return $status;

}


function wpbc_ipay88_backend_update_pay_status(){

    global $wpdb;
    $status = '';  $booking_id = '';  $pay_system = ''; $wp_nonce = '';

    if (isset($_GET['payed_booking']))  $booking_id = intval( $_GET['payed_booking'] );
    if (isset($_GET['stats']))          $status = $_GET['stats'];
    if (isset($_GET['pay_sys']))        $pay_system = $_GET['pay_sys'];
    if (isset($_GET['wp_nonce']))       $wp_nonce   = $_GET['wp_nonce'];
    
    if ($pay_system != 'ipay88') 
        die();
    
    $status = wpbc_check_response_status__ipay88_for_backendpost( $status, $booking_id, $wp_nonce );

    if ( ($booking_id =='') || ($status =='')  || ($wp_nonce =='') ) die() ;

    $update_sql = "UPDATE {$wpdb->prefix}booking AS bk SET bk.pay_status='$status' WHERE bk.booking_id=$booking_id;";
    if ( false === $wpdb->query( $update_sql  ) ){
        $status = 'Failed';  
    }
    
    $auto_approve = get_bk_option( 'booking_ipay88_is_auto_approve_cancell_booking'  );

    if ( ($status == 'OK') || ($status == 'ipay88:OK') ) {
        if ($auto_approve == 'On')                 
            check_auto_approve_or_cancell($booking_id, true );
        

    } else {
        if ($auto_approve == 'On')                 
            check_auto_approve_or_cancell($booking_id, false );
        
    }
    
    echo "RECEIVEOK";    
}

wpbc_ipay88_backend_update_pay_status();
?>