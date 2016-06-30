<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S u p p o r t    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // List of possible Payments statuses from the different payment systems.
        function get_payment_status_ok(){
           $payment_status = array(
                       'OK',
                       'Completed',                                      
                       'success',
                       'Paid OK'                   
                      );
            $payment_status = apply_filters( 'wpbc_add_payment_status_ok'  , $payment_status );
            return  $payment_status;
        }

        function get_payment_status_pending(){
           $payment_status = array(
                                   'Not_Completed',
                                   'Not Completed',
                                   'Pending',
                                   'Processed',
                                   'In-Progress',
                                   'partially',
                                   'Partially paid'                               
                                   );
            $payment_status = apply_filters( 'wpbc_add_payment_status_pending'  , $payment_status );
            return  $payment_status;
        }

        function get_payment_status_unknown(){
           $payment_status = array(
                                   '1',
                                   'Canceled_Reversal',
                                   'Voided',
                                   'Created'                               
                                   );
            $payment_status = apply_filters( 'wpbc_add_payment_status_unknown'  , $payment_status );
            return  $payment_status;
        }

        function get_payment_status_error(){
           $payment_status = array(
                                   'Denied',
                                   'Expired',
                                   'Failed',
                                   'Reversed',
                                   'Partially_Refunded',
                                   'Refunded',
                                   'not-authed',
                                   'malformed',
                                   'invalid',
                                   'abort',
                                   'rejected',
                                   'fraud',
                                   'Cancelled',
                                   'error'                               
                                   );
            $payment_status = apply_filters( 'wpbc_add_payment_status_error'  , $payment_status );
            return  $payment_status;
        }

    
        // Get type of the payment status 
        function wpdev_bk_get_type_of_payment_status($payment_status){

           $payment_type = 'unknown';               // Default payment status type

           $payment_success = get_payment_status_ok();
           $payment_pending = get_payment_status_pending();
           $payment_unknown = get_payment_status_unknown();
           $payment_error   = get_payment_status_error();

           // Check  LOWERCASE for the any payemtn status
           if (in_array( strtolower($payment_status), wpdev_bk_arraytolower($payment_success) ) !== false) $payment_type = 'success';
           if (in_array( strtolower($payment_status), wpdev_bk_arraytolower($payment_pending) ) !== false) $payment_type = 'pending';
           if (in_array( strtolower($payment_status), wpdev_bk_arraytolower($payment_unknown) ) !== false) $payment_type = 'unknown';
           if (in_array( strtolower($payment_status), wpdev_bk_arraytolower($payment_error) ) !== false) $payment_type = 'error';

           return  $payment_type;
           
        }

        // Check  if Payment Status - SUCCESS
        function is_payment_status_ok($payment_status){

            if (wpdev_bk_get_type_of_payment_status($payment_status) == 'success')  return  true;
            else                                                                    return false;
        }

        // Check  if Payment Status - PENDING
        function is_payment_status_pending($payment_status){

            if (wpdev_bk_get_type_of_payment_status($payment_status) == 'pending')  return  true;
            else                                                                    return false;
        }

        // Check  if Payment Status - UNKNOWN
        function is_payment_status_unknown($payment_status){

            if (wpdev_bk_get_type_of_payment_status($payment_status) == 'unknown')  return  true;
            else                                                                    return false;
        }

        // Check  if Payment Status - ERROR
        function is_payment_status_error($payment_status){

            if (wpdev_bk_get_type_of_payment_status($payment_status) == 'error')  return  true;
            else                                                                    return false;
        }
        

        function get_payment_status_titles(){
            
            $payment_status_titles = array(
                                    __('Completed' ,'booking')  =>'Completed',

                                    __('In-Progress' ,'booking')  =>'In-Progress',

                                    __('Unknown' ,'booking')   =>'1',

                                    __('Partially paid' ,'booking')  =>'partially',
                                    __('Cancelled' ,'booking')  =>'canceled',
                                    __('Failed' ,'booking')  =>'Failed',
                                    __('Refunded' ,'booking')  =>'Refunded',

                                    __('Fraud' ,'booking')  =>'fraud'
                                   );

            return $payment_status_titles;


            $payment_status_titles = array(
                                    __('!Paid OK' ,'booking')          =>'OK',
                                    __('Unknown status' ,'booking')   =>'1',
                                    __('Not Completed' ,'booking')   =>'Not_Completed',

                                    // PayPal statuses
                                    __('Completed' ,'booking')  =>'Completed',

                                    __('Pending' ,'booking')  =>'Pending',
                                    __('Processed' ,'booking')  =>'Processed',
                                    __('In-Progress' ,'booking')  =>'In-Progress',

                                    __('Canceled_Reversal' ,'booking')  =>'Canceled_Reversal',

                                    __('Denied' ,'booking')  =>'Denied',
                                    __('Expired' ,'booking')  =>'Expired',
                                    __('Failed' ,'booking')  =>'Failed',

                                    __('Partially_Refunded' ,'booking')  =>'Partially_Refunded',
                                    __('Refunded' ,'booking')  =>'Refunded',
                                    __('Reversed' ,'booking')  =>'Reversed',
                                    __('Voided' ,'booking')  =>'Voided',
                                    __('Created' ,'booking')  =>'Created',

                                    // Sage Statuses
                                    __('Not authed' ,'booking')  =>'not-authed',
                                    __('Malformed' ,'booking')  =>'malformed',
                                    __('Invalid' ,'booking')  =>'invalid',
                                    __('Abort' ,'booking')  =>'abort',
                                    __('Rejected' ,'booking')  =>'rejected',
                                    __('Error' ,'booking')  =>'error' ,

                                    __('Partially paid' ,'booking')  =>'partially',
                                    __('Cancelled' ,'booking')  =>'canceled',
                                    __('Fraud' ,'booking')  =>'fraud',
                                    __('Suspended' ,'booking')  =>'suspended'
                                   );
            return $payment_status_titles;
        }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
        
        //Function to redirect browser to a specific page
        function wpdev_redirect($url) {
            $url = wpbc_make_link_absolute( $url );
           //if (!headers_sent()) header('Location: '.$url . '/');
           //else
               {
               echo '<script type="text/javascript">';
               echo 'window.location.href="'.$url.'";';
               echo '</script>';
               echo '<noscript>';
               echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
               echo '</noscript>';
           }
        }

        
        function rangeNumListToCommaNumList( $specific_selected_dates ){
            $specific_selected_dates = explode(',',$specific_selected_dates);
            $js_specific_selected_dates = array();
            foreach ($specific_selected_dates as $value) {
               $is_range = strpos($value, '-');
               if ($is_range>0){
                   $value=explode('-',$value);
                   for ($ii = $value[0]; $ii <= $value[1]; $ii++) {
                      $js_specific_selected_dates[] = $ii; 
                   }
               } else $js_specific_selected_dates[] = $value;
            }
            $js_specific_selected_dates = implode(',',$js_specific_selected_dates);
            return $js_specific_selected_dates;
        }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Filters interface     Controll elements  ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Payment status
        function wpdebk_filter_field_bk_paystatus(){
            // Pay status
            $wpdevbk_id =              'wh_pay_status';
            //$wpdevbk_control_label =   __('OK' ,'booking');
            //$wpdevbk_help_block =      __('Payment status' ,'booking');
            //wpdevbk_text_filter($wpdevbk_id, $wpdevbk_control_label, $wpdevbk_help_block)

            $wpdevbk_selectors = array( __('Any Status' ,'booking')       =>'all',
                                'divider0'=>'divider',
                                       __('Paid OK' ,'booking') =>'group_ok',
                                       __('Unknown Status' ,'booking')    =>'group_unknown',
                                       __('Not Completed' ,'booking')     =>'group_pending',
                                       __('Failed' ,'booking')            =>'group_failed'
                /*
                                            // PayPal statuses
                                            __('Completed' ,'booking')  =>'Completed',

                                            __('Pending' ,'booking')  =>'Pending',
                                            __('Processed' ,'booking')  =>'Processed',
                                            __('In-Progress' ,'booking')  =>'In-Progress',

                                            __('Canceled_Reversal' ,'booking')  =>'Canceled_Reversal',

                                            __('Denied' ,'booking')  =>'Denied',
                                            __('Expired' ,'booking')  =>'Expired',
                                            __('Failed' ,'booking')  =>'Failed',

                                            __('Partially_Refunded' ,'booking')  =>'Partially_Refunded',
                                            __('Refunded' ,'booking')  =>'Refunded',
                                            __('Reversed' ,'booking')  =>'Reversed',
                                            __('Voided' ,'booking')  =>'Voided',
                                            __('Created' ,'booking')  =>'Created',
                                'divider2'=>'divider',
                                            // Sage Statuses
                                            __('Not authed' ,'booking')  =>'not-authed',
                                            __('Malformed' ,'booking')  =>'malformed',
                                            __('Invalid' ,'booking')  =>'invalid',
                                            __('Abort' ,'booking')  =>'abort',
                                            __('Rejected' ,'booking')  =>'rejected',
                                            __('Error' ,'booking')  =>'error' ,
                                'divider3'=>'divider',
                                       __('Partially paid' ,'booking')  =>'partially',
                                       __('Cancelled' ,'booking')  =>'canceled',
                                       __('Fraud' ,'booking')  =>'fraud',
                                       __('Suspended' ,'booking')  =>'suspended'
/**/
                                       );




            $wpdevbk_control_label =   '';
            $wpdevbk_help_block =      __('Payment' ,'booking');

            wpdevbk_selection_and_custom_text_for_filter($wpdevbk_id, $wpdevbk_selectors, $wpdevbk_control_label, $wpdevbk_help_block, 'all');

        }


        // Cost filter page
        function wpdebk_filter_field_bk_costs(){
            // Costs
            $wpdevbk_id =              'wh_cost';
            $wpdevbk_control_label =   __('Cost' ,'booking') . ': ';
            $wpdevbk_placeholder =     '0';
            $wpdevbk_help_block =      __('Min. cost' ,'booking');

            $wpdevbk_id2 =              'wh_cost2';
            $wpdevbk_control_label2 =  '-';
            $wpdevbk_placeholder2 =     '100000';
            $wpdevbk_help_block2 =      __('Max. cost' ,'booking');

            $wpdevbk_width = 'span1';
            $input_append =  __('min' ,'booking');
            $input_append2 =  __('max' ,'booking');

            wpdevbk_text_from_to_filter($wpdevbk_id, $wpdevbk_control_label, $wpdevbk_placeholder, $wpdevbk_help_block, $wpdevbk_id2, $wpdevbk_control_label2, $wpdevbk_placeholder2, $wpdevbk_help_block2, $wpdevbk_width, $input_append, $input_append2 );
        }


        // Get the sort options for the filter at the booking listing page
        function get_s_bk_filter_sort_options($wpdevbk_selectors_def){
              $wpdevbk_selectors = array(__('ID' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'',
                               __('Dates' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'sort_date',
                               __('Resource' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'booking_type',
                               __('Cost' ,'booking').'&nbsp;<i class="icon-arrow-up "></i>' =>'cost',
                               'divider0'=>'divider',
                               __('ID' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'booking_id_asc',
                               __('Dates' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'sort_date_asc',
                               __('Resource' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'booking_type_asc',
                               __('Cost' ,'booking').'&nbsp;<i class="icon-arrow-down "></i>' =>'cost_asc'
                              );
              return $wpdevbk_selectors;
        }
        add_bk_filter('bk_filter_sort_options', 'get_s_bk_filter_sort_options');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S Q L   Modifications  for  Booking Listing  ///////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        // Pay status
        function get_s_bklist_sql_paystatus($blank, $wh_pay_status ){
            $sql_where = '';

            if ( (isset($_REQUEST['wh_pay_status']) ) && ( $_REQUEST['wh_pay_status'] != 'all') ) {

                $sql_where .= " AND ( ";

                // Check  firstly if we are selected some goup of payment status
                if ($_REQUEST['wh_pay_status'] == 'group_ok' ) {                // SUCCESS

                   $payment_status = get_payment_status_ok();

                   foreach ($payment_status as $label) {
                       $sql_where .= " ( bk.pay_status = '". $label ."' ) OR";
                   }
                   $sql_where = substr($sql_where, 0, -2);

                } else if ( ($_REQUEST['wh_pay_status'] == 'group_unknown' ) || (is_numeric($wh_pay_status)) || ($wh_pay_status == '') ) {     // UNKNOWN

                   $payment_status = get_payment_status_unknown();
                   foreach ($payment_status as $label) {
                       $sql_where .= " ( bk.pay_status = '". $label ."' ) OR";
                   }
                   //$sql_where = substr($sql_where, 0, -2);
                   $sql_where .= " ( bk.pay_status = '' ) OR ( bk.pay_status regexp '^[0-9]') ";

                } else if ($_REQUEST['wh_pay_status'] == 'group_pending' ){     // Pending

                   $payment_status = get_payment_status_pending();
                   foreach ($payment_status as $label) {
                       $sql_where .= " ( bk.pay_status = '". $label ."' ) OR";
                   }
                   $sql_where = substr($sql_where, 0, -2);

                } else if ($_REQUEST['wh_pay_status'] == 'group_failed' ) {     // Failed

                   $payment_status   = get_payment_status_error();
                   foreach ($payment_status as $label) {
                       $sql_where .= " ( bk.pay_status = '". $label ."' ) OR";
                   }
                   $sql_where = substr($sql_where, 0, -2);

                } else {                                                        // CUSTOM Payment Status
                    $sql_where .= " bk.pay_status = '" . $wh_pay_status . "' ";
                }

                $sql_where .= " ) ";
            }

            return $sql_where;
        }
        add_bk_filter('get_bklist_sql_paystatus', 'get_s_bklist_sql_paystatus');

        // Cost
        function get_s_bklist_sql_cost($blank, $wh_cost, $wh_cost2  ){
            $sql_where = '';

            if ( $wh_cost   !== '' )    $sql_where.=   " AND (  bk.cost >= " . $wh_cost . " ) ";
            if ( $wh_cost2  !== '' )    $sql_where.=   " AND (  bk.cost <= " . $wh_cost2 . " ) ";

            return $sql_where;
        }
        add_bk_filter('get_bklist_sql_cost', 'get_s_bklist_sql_cost');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  H T M L   &  E l e m e n t s   in   Booking   L i s t i n g  Table  ////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        function wpdev_bk_listing_show_cost_btn( $booking_id, $bk_cost ){
          $currency = apply_bk_filter( 'get_currency_info' );
          ?><div class="cost-fields-group">

                  <div class="field-currency"><?php echo $currency; ?></div>
                  <input type="text" id="booking_cost<?php echo $booking_id; ?>" name="booking_cost<?php echo $booking_id; ?>"
                         value="<?php echo $bk_cost; ?>" class="field-booking-cost"
                         onkeydown="javascript:document.getElementById('booking_save_cost<?php echo $booking_id; ?>').style.display='block';"
                         
                  /><?php                                                       // FixIn: 5.4.5.11
                  /*
                  $booking_data = apply_bk_filter('wpbc_get_booking_data',  $booking_id);
                  // debuge($booking_data);                                     // Show Booking details
                  if ( isset( $booking_data['parsed_form'] ) && isset( $booking_data['type'] ) ) {
                      if (isset(  $booking_data['parsed_form'][ 'new_deposit' . $booking_data['type'] ] )) {
                          $new_deposit = $booking_data['parsed_form'][ 'new_deposit' . $booking_data['type'] ]['value'];
                          ?><input type="text" id="deposit_cost<?php echo $booking_id; ?>" name="deposit_cost<?php echo $booking_id; ?>"
                         value="<?php echo $new_deposit; ?>" class="field-booking-cost"                         
                  /><?php
                      }
                      if (isset(  $booking_data['parsed_form'][ 'new_balance' . $booking_data['type'] ] )) {
                          $new_balance = $booking_data['parsed_form'][ 'new_balance' . $booking_data['type'] ]['value'];
                          ?><input type="text" id="balance_cost<?php echo $booking_id; ?>" name="balance_cost<?php echo $booking_id; ?>"
                                    value="<?php echo $new_balance; ?>" class="field-booking-cost"                         
                             /><?php
                      }
                  } */
                  ?><a 
                        href="javascript:void(0)" data-original-title="<?php _e('Send payment request to visitor' ,'booking'); ?>"  rel="tooltip" 
                        class="tooltip_top button-secondary button wpbc_send-payment-request-button"
                        id="send_payment_request<?php echo $booking_id; ?>"
                        onclick='javascript:payment_request_id = <?php echo $booking_id; ?>;
                         document.getElementById("payment_request_reason").value = "";
                         jQuery("#sendPaymentRequestModal").modal("show");'
                  ><i class="icon-envelope"></i><?php /* ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/credit_card_24x24.png" /><?php /**/ ?></a>
          </div><?php

        }
        add_bk_action( 'wpdev_bk_listing_show_cost_btn', 'wpdev_bk_listing_show_cost_btn');

        function wpdev_bk_listing_show_payment_status_btn($booking_id){
            ?><a href="javascript:void(0)" data-original-title="<?php _e('Payment status' ,'booking'); ?>"  rel="tooltip" 
               class="tooltip_top payment_status_bk_link button-secondary button"
               onclick='javascript:
               if (document.getElementById(&quot;payment_status_row<?php echo $booking_id;?>&quot;).style.display==&quot;block&quot;) {
                   document.getElementById(&quot;payment_status_row<?php echo $booking_id;?>&quot;).style.display=&quot;none&quot;; }
               else { document.getElementById(&quot;payment_status_row<?php echo $booking_id;?>&quot;).style.display=&quot;block&quot;; }'
               ><i class="icon-bookmark"></i><?php                              // FixIn: 5.4.5.1
              /** ?><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/payment-status.png" style="width:16px; height:16px;margin:-2px 0px;"><?php /**/ ?></a><?php
        }
        add_bk_action( 'wpdev_bk_listing_show_payment_status_btn', 'wpdev_bk_listing_show_payment_status_btn');


        function wpdev_bk_listing_show_print_btn($booking_id){
            ?><a href="javascript:void(0)" data-original-title="<?php _e('Print' ,'booking'); ?>"  rel="tooltip" 
               class="tooltip_top button-secondary button"
               onclick='javascript:wpbc_print_specific_booking(<?php echo $booking_id; ?>);'
               ><i class="icon-print"></i><?php
              ?></a><?php
        }
        add_bk_action( 'wpdev_bk_listing_show_print_btn', 'wpdev_bk_listing_show_print_btn');

        
        function wpdev_bk_listing_show_payment_status_cost_fields($booking_id, $bk_pay_status){
            $payment_status_titles = get_payment_status_titles();
            ?>
           <?php //BS : Save cost  ?>
           <div class="booking_row_modification_element" id="booking_save_cost<?php echo $booking_id; ?>" >
                <a href="javascript:void(0)" class="button button-primary btn-save-cost" 
                       name="btn_booking_save_cost<?php echo $booking_id; ?>" id="btn_booking_save_cost<?php echo $booking_id; ?>"
                       onclick="javascript:
                              document.getElementById('booking_save_cost<?php echo $booking_id; ?>').style.display='none';
                              save_this_booking_cost(<?php echo $booking_id; ?>, document.getElementById('booking_cost<?php echo $booking_id; ?>').value, '<?php echo getBookingLocale(); ?>' );"
                ><?php _e('Save cost' ,'booking'); ?></a>
               <div class="clear"></div>
           </div>

           <?php  //BS : Payment status  ?>
           <div class="booking_row_modification_element_payment_status booking_row_modification_element " id="payment_status_row<?php echo $booking_id; ?>" >
                <select id="select_payment_status_row<?php echo $booking_id; ?>" name="select_payment_status_row<?php echo $booking_id; ?>"
                        >
                    <?php
                    $wpdevbk_selectors = $payment_status_titles ;
                    foreach ($wpdevbk_selectors as $kk=>$vv) { ?>
                    <option <?php if ( ( $bk_pay_status == $vv ) || ( (is_numeric($bk_pay_status)) && ($vv == '1') ) ) echo "selected='SELECTED'"; ?> value="<?php echo $vv; ?>"
                        ><?php echo $kk ; ?></option>
                    <?php } ?>
                </select>
                <a href="javascript:void(0)" class="button button-primary btn-save-cost"  
                       name="btn_booking_chnage_status<?php echo $booking_id; ?>" id="btn_booking_chnage_status<?php echo $booking_id; ?>"
                       onclick="javascript:
                              document.getElementById('payment_status_row<?php echo $booking_id; ?>').style.display='none';
                              chnage_booking_payment_status(<?php echo $booking_id; ?>,
                              document.getElementById('select_payment_status_row<?php echo $booking_id; ?>').value,
                              document.getElementById('select_payment_status_row<?php echo $booking_id; ?>').options[document.getElementById('select_payment_status_row<?php echo $booking_id; ?>').selectedIndex].text
                          );"
                 ><?php _e('Change status' ,'booking'); ?></a>
                 <div class="clear"></div>
           </div>
           <?php
        }
        add_bk_action( 'wpdev_bk_listing_show_payment_status_cost_fields', 'wpdev_bk_listing_show_payment_status_cost_fields');


        function wpdev_bk_listing_show_payment_label(  $is_paid, $pay_print_status , $real_payment_status_label){

            $css_payment_label = 'payment-label-' . wpdev_bk_get_type_of_payment_status($real_payment_status_label);
            if ($is_paid) { ?><span class="label label-payment-status label-success <?php echo $css_payment_label; ?> "><?php echo '<span style="font-size:07px;">'.__('Payment' ,'booking') .'</span> '.$pay_print_status ; ?></span><?php     }
            else          {               
                ?><span class="label label-payment-status <?php echo $css_payment_label; ?> "><?php  echo '<span style="font-size:07px;">'.__('Payment' ,'booking') .'</span> '. $pay_print_status; ; ?></span><?php
           }
        }
        add_bk_action( 'wpdev_bk_listing_show_payment_label', 'wpdev_bk_listing_show_payment_label');



        function wpdev_bk_get_payment_status_simple($bk_pay_status) {

            if ( is_payment_status_ok( trim($bk_pay_status) ) ) $is_paid = 1 ;
            else $is_paid = 0 ;

            $payment_status_titles = get_payment_status_titles();
            $payment_status_titles_current = array_search($bk_pay_status, $payment_status_titles);
            if ($payment_status_titles_current === FALSE ) $payment_status_titles_current = $bk_pay_status ;

            $pay_print_status = '';

            if ($is_paid) {
                $pay_print_status = __('Paid OK' ,'booking');
                if ($payment_status_titles_current == 'Completed') $pay_print_status = $payment_status_titles_current;
            } else if ( (is_numeric($bk_pay_status)) || ($bk_pay_status == '') )        {
                $pay_print_status = __('Unknown' ,'booking');
            } else  {
                $pay_print_status = $payment_status_titles_current;
            }

            return $pay_print_status;

        }
?>