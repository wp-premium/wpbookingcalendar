<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

    if (!defined('WP_BK_PAYPAL_URL')) define('WP_BK_PAYPAL_URL',  'https://www.paypal.com' );    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Toolbar tab
    function wpdev_bk_payment_show_tab_in_top_settings_paypal(){
        ?><a href="javascript:void(0)" onclick="javascript:
                jQuery('.visibility_container').css('display','none');
                jQuery('#visibility_container_paypal').css('display','block');
                jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                jQuery(this).addClass('booking-submenu-tab-selected');"
           rel="tooltip"
           class="tooltip_bottom nav-tab  booking-submenu-tab <?php
                   if ( get_bk_option( 'booking_paypal_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
           original-title="<?php _e('Integration of Paypal payment system' ,'booking');?>" >
           <?php _e('PayPal' ,'booking');?>
           <input type="checkbox" <?php if ( get_bk_option( 'booking_paypal_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
                   name="paypal_is_active_dublicated" id="paypal_is_active_dublicated"
                   onchange="document.getElementById('paypal_is_active').checked=this.checked;" >
        </a>
        <script type="text/javascript">
            jQuery(document).ready( function(){
                recheck_active_itmes_in_top_menu('paypal_is_active', 'paypal_is_active_dublicated');
            });
        </script>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_paypal');


    // Settings page for    P a y P a l
    function wpdev_bk_payment_show_settings_content_paypal(){
        if ( ( isset( $_POST['paypal_curency'] ) )  ) {
            
            if ( wpdev_bk_is_this_demo() ) { 
                $_POST['paypal_emeil'] = 'Seller_1335004986_biz@wpdevelop.com';
                $_POST['paypal_is_sandbox'] = 'On';
            }

            ////////////////////////////////////////////////////////////////////////////////////////////////////////////
            update_bk_option( 'booking_paypal_is_sandbox' ,    $_POST['paypal_is_sandbox'] );
            if (isset($_POST['paypal_emeil']))
               update_bk_option( 'booking_paypal_emeil' ,         $_POST['paypal_emeil'] );
            if (isset($_POST['paypal_secure_merchant_id']))
                update_bk_option( 'booking_paypal_secure_merchant_id' , $_POST['paypal_secure_merchant_id'] );
            update_bk_option( 'booking_paypal_curency' ,       $_POST['paypal_curency'] );
            update_bk_option( 'booking_paypal_subject' ,       $_POST['paypal_subject'] );
            
            update_bk_option( 'booking_paypal_return_url' ,         wpbc_make_link_relative($_POST['paypal_return_url'] )         );
            update_bk_option( 'booking_paypal_cancel_return_url' ,  wpbc_make_link_relative($_POST['paypal_cancel_return_url'] )  );
            
            update_bk_option( 'booking_paypal_button_type' ,        str_replace( WP_BK_PAYPAL_URL, '', $_POST['paypal_button_type'] ) );
            update_bk_option( 'booking_paypal_paymentaction' ,   $_POST['paypal_paymentaction'] );
            update_bk_option( 'booking_paypal_payment_button_title', $_POST['paypal_payment_button_title'] );

            if (isset($_POST['paypal_reference_title_box']))
                update_bk_option( 'booking_paypal_reference_title_box' , $_POST['paypal_reference_title_box'] );

            if (isset( $_POST['paypal_is_active'] ))     $paypal_is_active = 'On';
            else                                         $paypal_is_active = 'Off';
            update_bk_option( 'booking_paypal_is_active' , $paypal_is_active );

            if (  $_POST['paypal_pro_hosted_solution'] == 'On' )     $paypal_pro_hosted_solution = 'On';
            else                                                   $paypal_pro_hosted_solution = 'Off';
            update_bk_option( 'booking_paypal_pro_hosted_solution' , $paypal_pro_hosted_solution );

            if (isset( $_POST['paypal_is_reference_box'] ))     $paypal_is_reference_box = 'On';
            else                                                $paypal_is_reference_box = 'Off';
            update_bk_option( 'booking_paypal_is_reference_box' , $paypal_is_reference_box );

            if (isset( $_POST['paypal_is_description_show'] ))     $paypal_is_description_show = 'On';
            else                                                   $paypal_is_description_show = 'Off';
            update_bk_option( 'booking_paypal_is_description_show' , $paypal_is_description_show );

            if (isset( $_POST['paypal_is_auto_approve_cancell_booking'] ))     $paypal_is_auto_approve_cancell_booking = 'On';
            else                                                               $paypal_is_auto_approve_cancell_booking = 'Off';
            update_bk_option( 'booking_paypal_is_auto_approve_cancell_booking',$paypal_is_auto_approve_cancell_booking );

            if (isset( $_POST['paypal_ipn_is_send_verified_email'] ))      $paypal_ipn_is_send_verified_email = 'On';
            else                                                           $paypal_ipn_is_send_verified_email = 'Off';
            update_bk_option( 'booking_paypal_ipn_is_send_verified_email' , $paypal_ipn_is_send_verified_email );
            if (isset( $_POST['paypal_ipn_verified_email'] )) update_bk_option( 'booking_paypal_ipn_verified_email' , $_POST['paypal_ipn_verified_email']  );

            if (isset( $_POST['paypal_ipn_is_send_invalid_email'] ))      $paypal_ipn_is_send_invalid_email = 'On';
            else                                                           $paypal_ipn_is_send_invalid_email = 'Off';
            update_bk_option( 'booking_paypal_ipn_is_send_invalid_email' , $paypal_ipn_is_send_invalid_email );
            if (isset( $_POST['paypal_ipn_invalid_email'] )) update_bk_option( 'booking_paypal_ipn_invalid_email' , $_POST['paypal_ipn_invalid_email']  );

            if (isset( $_POST['paypal_ipn_is_send_error_email'] ))      $paypal_ipn_is_send_error_email = 'On';
            else                                                           $paypal_ipn_is_send_error_email = 'Off';
            update_bk_option( 'booking_paypal_ipn_is_send_error_email' , $paypal_ipn_is_send_error_email );
            if (isset( $_POST['paypal_ipn_error_email'] )) update_bk_option( 'booking_paypal_ipn_error_email' , $_POST['paypal_ipn_error_email']  );

            if (isset( $_POST['paypal_ipn_use_ssl'] ))                     $paypal_ipn_use_ssl = 'On';
            else                                                           $paypal_ipn_use_ssl = 'Off';
            update_bk_option( 'booking_paypal_ipn_use_ssl' , $paypal_ipn_use_ssl );
            if (isset( $_POST['paypal_ipn_use_curl'] ))                    $paypal_ipn_use_curl = 'On';
            else                                                           $paypal_ipn_use_curl = 'Off';
            update_bk_option( 'booking_paypal_ipn_use_curl' , $paypal_ipn_use_curl );
        }
        $paypal_emeil               =  get_bk_option( 'booking_paypal_emeil' );
        $paypal_secure_merchant_id  =  get_bk_option( 'booking_paypal_secure_merchant_id'  );
        $paypal_pro_hosted_solution =  get_bk_option( 'booking_paypal_pro_hosted_solution' );
        $paypal_curency             =  get_bk_option( 'booking_paypal_curency' );
        $paypal_subject             =  get_bk_option( 'booking_paypal_subject' );
        $paypal_is_active           =  get_bk_option( 'booking_paypal_is_active' );
        $paypal_pro_hosted_solution =  get_bk_option( 'booking_paypal_pro_hosted_solution' );
        $paypal_is_reference_box    =  get_bk_option( 'booking_paypal_is_reference_box' );           // checkbox
        $paypal_reference_title_box =  get_bk_option( 'booking_paypal_reference_title_box' );
        $paypal_paymentaction       =  get_bk_option( 'booking_paypal_paymentaction' );
        $paypal_payment_button_title=  get_bk_option( 'booking_paypal_payment_button_title' );
        $paypal_return_url          =  get_bk_option( 'booking_paypal_return_url' );
        $paypal_cancel_return_url   =  get_bk_option( 'booking_paypal_cancel_return_url' );
        $paypal_button_type         =  get_bk_option( 'booking_paypal_button_type' );  // radio
        $paypal_button_type         = str_replace( WP_BK_PAYPAL_URL, '', $paypal_button_type);
        
        $paypal_is_sandbox          =  get_bk_option( 'booking_paypal_is_sandbox' );  // radio
        $paypal_is_description_show =  get_bk_option( 'booking_paypal_is_description_show' );  // radio
        $paypal_is_auto_approve_cancell_booking =  get_bk_option( 'booking_paypal_is_auto_approve_cancell_booking' );  // radio

        $paypal_ipn_is_send_verified_email  =  get_bk_option( 'booking_paypal_ipn_is_send_verified_email' );
        $paypal_ipn_verified_email          =  get_bk_option( 'booking_paypal_ipn_verified_email' );
        $paypal_ipn_is_send_invalid_email  =  get_bk_option( 'booking_paypal_ipn_is_send_invalid_email' );
        $paypal_ipn_invalid_email          =  get_bk_option( 'booking_paypal_ipn_invalid_email' );
        $paypal_ipn_is_send_error_email  =  get_bk_option( 'booking_paypal_ipn_is_send_error_email' );
        $paypal_ipn_error_email          =  get_bk_option( 'booking_paypal_ipn_error_email' );
        $paypal_ipn_use_ssl     =  get_bk_option( 'booking_paypal_ipn_use_ssl' );
        $paypal_ipn_use_curl    =  get_bk_option( 'booking_paypal_ipn_use_curl' );
        ?>
        <div id="visibility_container_paypal" class="visibility_container" style="display:none;">
         <div class='meta-box'>
          <div <?php $my_close_open_win_id = 'bk_settings_costs_paypal_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <!--div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div-->
           <h3 class='hndle'><span><?php _e('PayPal customization' ,'booking'); ?></span></h3>
           <div class="inside" style="margin:0px;">

            <a data-original-title="<?php _e('Configuration of PayPal Standard payment form' ,'booking'); ?>" class="tooltip_bottom nav-tab  booking-submenu-tab top-to-bottom  <?php if ($paypal_pro_hosted_solution == 'Off') echo ' booking-submenu-tab-selected '; ?> " rel="tooltip" onclick="javascript:
                       jQuery('.visibility_sub_container_paypal').addClass('hidden_items');
                       jQuery('.visibility_sub_container_paypal_standard').removeClass('hidden_items');
                       jQuery('.visibility_paypal_account_settings').removeClass('hidden_items');
                       jQuery('.visibility_paypal_ipn_settings').addClass('hidden_items');           
                       jQuery('.nav-tab.top-to-bottom').removeClass('booking-submenu-tab-selected');
                       jQuery(this).addClass('booking-submenu-tab-selected');" href="javascript:void(0)" >
                <?php _e('Paypal Standard' ,'booking');?>
                <input <?php if ($paypal_pro_hosted_solution == 'Off') echo ' checked="checked" '; ?> type="radio" name="paypal_pro_hosted_solution" value="Off" style=""
                 onMouseDown="javascript: document.getElementById('paypal_secure_merchant_id').disabled=! this.checked; document.getElementById('paypal_emeil').disabled= this.checked; "
                 data-original-title="Set Active of PayPal Standard account"  rel="tooltip" class="tooltip_right"
                                                                                                      />
            </a>
            <a data-original-title="<?php _e('Configuration of PayPal Pro Hosted Solution payment form' ,'booking'); ?>" class="tooltip_bottom nav-tab  booking-submenu-tab top-to-bottom  <?php if ($paypal_pro_hosted_solution == 'On') echo ' booking-submenu-tab-selected '; ?> " rel="tooltip" onclick="javascript:
                       jQuery('.visibility_sub_container_paypal').addClass('hidden_items');
                       jQuery('.visibility_sub_container_paypal_pro').removeClass('hidden_items');
                       jQuery('.visibility_paypal_account_settings').removeClass('hidden_items');
                       jQuery('.visibility_paypal_ipn_settings').addClass('hidden_items');
                       jQuery('.nav-tab.top-to-bottom').removeClass('booking-submenu-tab-selected');
                       jQuery(this).addClass('booking-submenu-tab-selected');" href="javascript:void(0)" >
                <?php _e('Paypal Pro Hosted Solution' ,'booking');?>
                    <input <?php if ($paypal_pro_hosted_solution == 'On') echo ' checked="checked" '; ?> type="radio" name="paypal_pro_hosted_solution" value="On" style=""
                    onMouseDown="javascript: document.getElementById('paypal_secure_merchant_id').disabled=this.checked; document.getElementById('paypal_emeil').disabled=! this.checked; "
                    data-original-title="Set Active of PayPal Pro Hosted Solution account"  rel="tooltip" class="tooltip_right"
                                                                                                         />
            </a>
            <a data-original-title="<?php _e('Instant Payment Notification (IPN) is a message service that notifies you of events related to PayPal transactions' ,'booking'); ?>" class="tooltip_bottom nav-tab  booking-submenu-tab top-to-bottom" rel="tooltip" onclick="javascript:
                       jQuery('.visibility_paypal_account_settings').addClass('hidden_items');
                       jQuery('.visibility_paypal_ipn_settings').removeClass('hidden_items');
                       jQuery('.nav-tab.top-to-bottom').removeClass('booking-submenu-tab-selected');
                       jQuery(this).addClass('booking-submenu-tab-selected');" href="javascript:void(0)" >
                    <?php _e('IPN' ,'booking');?>
            </a>
               
            <table class="visibility_paypal_ipn_settings form-table settings-table hidden_items">
                <tbody>
                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_ipn_verified_email" ><?php _e('Sending email for verified transaction' ,'booking'); ?>:</label>
                      </th>
                      <td>
                        <fieldset>
                            <label for="paypal_ipn_is_send_verified_email">
                                <input  type="checkbox" name="paypal_ipn_is_send_verified_email" id="paypal_ipn_is_send_verified_email" 
                                        <?php if ($paypal_ipn_is_send_verified_email == 'On') echo "checked";/**/ ?>
                                        value="<?php echo $paypal_ipn_is_send_verified_email; ?>"                                         
                                        onMouseDown="javascript: document.getElementById('paypal_ipn_verified_email').disabled=this.checked; "
                                /><?php _e('Active' ,'booking'); ?>
                            </label>&nbsp;&nbsp;&nbsp;
                            <input <?php if ($paypal_ipn_is_send_verified_email !== 'On') echo " disabled "; ?>
                                  value="<?php echo $paypal_ipn_verified_email; ?>" name="paypal_ipn_verified_email" id="paypal_ipn_verified_email"
                                  class="regular-text code" type="text" size="145" />
                            <p class="description"><?php printf(__('Email for getting report for %sverified%s transactions.' ,'booking'),'<b>','</b>');?></p>
                        </fieldset>
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_ipn_invalid_email" ><?php _e('Sending email for invalid transaction' ,'booking'); ?>:</label>
                      </th>
                      <td>
                        <fieldset>
                            <label for="paypal_ipn_is_send_invalid_email">
                                <input <?php if ($paypal_ipn_is_send_invalid_email == 'On') echo "checked";/**/ ?>
                                    value="<?php echo $paypal_ipn_is_send_invalid_email; ?>" name="paypal_ipn_is_send_invalid_email" id="paypal_ipn_is_send_invalid_email" type="checkbox"
                                    onMouseDown="javascript: document.getElementById('paypal_ipn_invalid_email').disabled=this.checked; "
                                    /><?php _e('Active' ,'booking'); ?>
                            </label>&nbsp;&nbsp;&nbsp;
                            <input <?php if ($paypal_ipn_is_send_invalid_email !== 'On') echo " disabled "; ?>
                              value="<?php echo $paypal_ipn_invalid_email; ?>" name="paypal_ipn_invalid_email" id="paypal_ipn_invalid_email"
                              class="regular-text code" type="text" size="145" />
                            <p class="description"><?php printf(__('Email for getting report for %sinvalid%s transactions.' ,'booking'),'<b>','</b>');?></p>
                        </fieldset>                            
                      </td>
                    </tr>


                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_ipn_error_email" ><?php _e('Sending email if error occur during verification' ,'booking'); ?>:</label>
                      </th>
                      <td>
                        <fieldset>
                            <label for="paypal_ipn_is_send_error_email">
                                <input <?php if ($paypal_ipn_is_send_error_email == 'On') echo "checked";/**/ ?>
                                    value="<?php echo $paypal_ipn_is_send_error_email; ?>" name="paypal_ipn_is_send_error_email" id="paypal_ipn_is_send_error_email" type="checkbox"
                                    onMouseDown="javascript: document.getElementById('paypal_ipn_error_email').disabled=this.checked; "
                                    /><?php _e('Active' ,'booking'); ?>
                            </label>&nbsp;&nbsp;&nbsp;
                            <input <?php if ($paypal_ipn_is_send_error_email !== 'On') echo " disabled "; ?>
                                value="<?php echo $paypal_ipn_error_email; ?>" name="paypal_ipn_error_email" id="paypal_ipn_error_email"
                                class="regular-text code" type="text" size="145" />
                            <p class="description"><?php printf(__('Email for getting report for %ssome errors in  verification process%s.' ,'booking'),'<b>','</b>');?></p>
                        </fieldset>                            
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row"><?php _e('Use SSL connection' ,'booking'); ?>:</th>
                      <td>
                          <label for="paypal_ipn_use_ssl" >
                            <input <?php if ($paypal_ipn_use_ssl == 'On') echo "checked"; ?> value="<?php echo $paypal_ipn_use_ssl; ?>"
                                name="paypal_ipn_use_ssl" id="paypal_ipn_use_ssl" type="checkbox" 
                                /><?php _e('Use the SSL connection for posting data, instead of standard HTTP connection' ,'booking'); ?>
                          </label>
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row"><?php _e('Use cURL posting' ,'booking'); ?>:</th>
                      <td>
                          <label for="paypal_ipn_use_curl" >
                            <input <?php if ($paypal_ipn_use_curl == 'On') echo "checked"; ?> value="<?php echo $paypal_ipn_use_curl; ?>"
                                name="paypal_ipn_use_curl" id="paypal_ipn_use_curl" type="checkbox"
                                /><?php _e('Use the cURL for posting data, instead of fsockopen() function' ,'booking'); ?>
                          </label>
                      </td>
                    </tr>

                    <tr>
                      <td colspan="2" >                            
                        <div  class="wpbc-help-message">                                  
                            <strong><?php _e(' Follow these instructions to set up your listener at your PayPal account:' ,'booking');?></strong>
                            <ol>
                                <li><?php _e('Click Profile on the My Account tab.' ,'booking');?></li>
                                <li><?php _e('Click Instant Payment Notification Preferences in the Selling Preferences column.' ,'booking');?></li>
                                <li><?php _e('Click Choose IPN Settings to specify your listeners URL and activate the listener.' ,'booking');?></li>
                                <li><?php _e('Specify the URL for your listener in the Notification URL field as:' ,'booking');?><br /><code><?php echo WPDEV_BK_PLUGIN_URL .'/inc/payments/ipn.php';?></code></li>
                                <li><?php _e('Click Receive IPN messages (Enabled) to enable your listener.' ,'booking');?></li>
                                <li><?php _e('Click Save.' ,'booking');?></li>
                                <li><?php _e('Click Back to Profile Summary to return to the Profile after activating your listener.' ,'booking');?></li>
                            </ol>
                        </div>                            
                      </td>
                    </tr>
            </table>

            <table class="visibility_paypal_account_settings form-table settings-table0">
                <tbody>

                    <tr valign="top">
                        <th scope="row"><?php _e('PayPal active' ,'booking'); ?>:</th>
                        <td>
                            <fieldset>
                                <label for="paypal_is_active" >
                                    <input <?php if ($paypal_is_active == 'On') echo "checked"; ?>
                                        value="<?php echo $paypal_is_active; ?>" name="paypal_is_active" id="paypal_is_active" type="checkbox"
                                        onchange="document.getElementById('paypal_is_active_dublicated').checked=this.checked;"
                                  /><?php _e('Check this box to use PayPal' ,'booking');?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>

                    <tr valign="top" class="wpdevbk visibility_sub_container_paypal visibility_sub_container_paypal_pro   <?php if ($paypal_pro_hosted_solution == 'Off') echo ' hidden_items '; ?> " >
                      <th scope="row"class="well" style="padding: 13px 10px 0;">
                          <label for="paypal_secure_merchant_id" ><?php _e('Secure Merchant ID' ,'booking'); ?>:</label>
                      </th>
                      <td class="well" style="padding: 10px;">
                          <input  <?php if ($paypal_pro_hosted_solution !== 'On') echo " disabled "; ?>
                              value="<?php echo $paypal_secure_merchant_id; ?>" name="paypal_secure_merchant_id" id="paypal_secure_merchant_id"
                              class="regular-text code" type="text" size="45" />
                          <p class="description"><?php printf(__('This is the Secure Merchant ID, which can be found on the profile page' ,'booking'),'<b>','</b>');?></p>
                          <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                      </td>
                    </tr>

                    <tr valign="top" class="wpdevbk visibility_sub_container_paypal visibility_sub_container_paypal_standard <?php if ($paypal_pro_hosted_solution == 'On') echo ' hidden_items '; ?> " >
                      <th scope="row" class="well" style="padding: 13px 10px 0;">
                        <label for="paypal_emeil" ><?php _e('Paypal Email address to receive payments' ,'booking'); ?>:</label>
                      </th>
                      <td class="well" style="padding: 10px;">
                          <input  <?php if ($paypal_pro_hosted_solution == 'On') echo " disabled "; ?>
                              value="<?php echo $paypal_emeil; ?>" name="paypal_emeil" id="paypal_emeil" class="regular-text code" type="text" size="45" />
                          <p class="description"><?php printf(__('This is the Paypal Email address where payments will be sent' ,'booking'),'<b>','</b>');?></p>
                          <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                      </td>
                    </tr>
                    
                    <tr valign="top"><td style="padding:10px 0px; " colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                    
                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_is_sandbox" ><?php _e('Chose payment mode' ,'booking'); ?>:</label>
                      </th>
                      <td>
                         <select id="paypal_is_sandbox" name="paypal_is_sandbox">
                            <option <?php if($paypal_is_sandbox == 'Off') echo "selected"; ?> value="Off"><?php _e('Live' ,'booking'); ?></option>
                            <option <?php if($paypal_is_sandbox == 'On')  echo "selected"; ?> value="On"><?php _e('Sandbox' ,'booking'); ?></option>
                         </select>
                         <span class="description"><?php _e(' Select using test (Sandbox Test Environment) or live PayPal payment.' ,'booking');?></span>
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_paymentaction" ><?php _e('Transaction type' ,'booking'); ?>:</label>
                      </th>
                      <td>
                         <select id="paypal_paymentaction" name="paypal_paymentaction">
                            <option <?php if($paypal_paymentaction == 'sale') echo "selected"; ?> value="sale"><?php _e('Sale' ,'booking'); ?></option>
                            <option <?php if($paypal_paymentaction == 'authorization')  echo "selected"; ?> value="authorization"><?php _e('Authorization' ,'booking'); ?></option>
                         </select>
                         <span class="description"><?php _e(' Indicates whether the transaction is payment on a final sale or an authorization for a final sale, to be captured later. ' ,'booking');?></span>
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_curency" ><?php _e('Accepted Currency' ,'booking'); ?>:</label>
                      </th>
                      <td>
                         <select id="paypal_curency" name="paypal_curency">
                            <?php /* <option <?php if($paypal_curency == 'ZAR') echo "selected"; ?> value="ZAR"><?php _e('South African Rand' ,'booking'); ?></option> /**/ ?>
                            <option <?php if($paypal_curency == 'USD') echo "selected"; ?> value="USD"><?php _e('U.S. Dollars' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'EUR') echo "selected"; ?> value="EUR"><?php _e('Euros' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'GBP') echo "selected"; ?> value="GBP"><?php _e('British Pound' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'JPY') echo "selected"; ?> value="JPY"><?php _e('Japanese Yen' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'AUD') echo "selected"; ?> value="AUD"><?php _e('Australian Dollars' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'CAD') echo "selected"; ?> value="CAD"><?php _e('Canadian Dollars' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'NZD') echo "selected"; ?> value="NZD"><?php _e('New Zealand Dollar' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'CHF') echo "selected"; ?> value="CHF"><?php _e('Swiss Franc' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'HKD') echo "selected"; ?> value="HKD"><?php _e('Hong Kong Dollar' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'SGD') echo "selected"; ?> value="SGD"><?php _e('Singapore Dollar' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'SEK') echo "selected"; ?> value="SEK"><?php _e('Swedish Krona' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'DKK') echo "selected"; ?> value="DKK"><?php _e('Danish Krone' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'PLN') echo "selected"; ?> value="PLN"><?php _e('Polish Zloty' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'NOK') echo "selected"; ?> value="NOK"><?php _e('Norwegian Krone' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'HUF') echo "selected"; ?> value="HUF"><?php _e('Hungarian Forint' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'CZK') echo "selected"; ?> value="CZK"><?php _e('Czech Koruna' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'ILS') echo "selected"; ?> value="ILS"><?php _e('Israeli New Shekel' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'MXN') echo "selected"; ?> value="MXN"><?php _e('Mexican Peso' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'BRL') echo "selected"; ?> value="BRL"><?php _e('Brazilian Real (only for Brazilian users)' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'MYR') echo "selected"; ?> value="MYR"><?php _e('Malaysian Ringgits (only for Malaysian users)' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'PHP') echo "selected"; ?> value="PHP"><?php _e('Philippine Pesos' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'TWD') echo "selected"; ?> value="TWD"><?php _e('Taiwan New Dollars' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'THB') echo "selected"; ?> value="THB"><?php _e('Thai Baht' ,'booking'); ?></option>
                            <option <?php if($paypal_curency == 'TRY') echo "selected"; ?> value="TRY"><?php _e('Turkish Lira (only for Turkish members)' ,'booking'); ?></option>
                         </select>
                         <span class="description"><?php printf(__('The currency code that gateway will process the payment in.' ,'booking'),'<b>','</b>');?></span>
                      </td>
                    </tr>

                    
                    
                    <tr valign="top">
                      <th scope="row"><?php _e('Payment Button type' ,'booking'); ?>:</th>
                      <td>
                          <fieldset>
                              <legend class="screen-reader-text">
                                <span><?php _e('Payment Button type' ,'booking'); ?></span>
                              </legend>
                                <label for="paypal_button_type_1" style="text-align: center;vertical-align: top;padding: 0 10px;">
                                    <img src="<?php echo WP_BK_PAYPAL_URL; ?>/en_US/i/btn/btn_paynowCC_LG.gif" style="margin: 0;"/><br />
                                    <input type="radio" name="paypal_button_type" id="paypal_button_type_1" 
                                           value="/en_US/i/btn/btn_paynowCC_LG.gif" 
                                           onclick="javascript:if(this.value!='custom') jQuery('#togle_settings_paypal_payment_button_title').hide();" 
                                           <?php if ($paypal_button_type == '/en_US/i/btn/btn_paynowCC_LG.gif') echo ' checked="checked" '; ?> 
                                           />                                    
                                </label>
                                <label for="paypal_button_type_2" style="text-align: center;vertical-align: top;padding: 0 10px;">
                                    <img src="<?php echo WP_BK_PAYPAL_URL; ?>/en_US/i/btn/btn_paynow_LG.gif" style="margin: 0 0 20px;"/><br/>
                                    <input type="radio" name="paypal_button_type" id="paypal_button_type_2" 
                                           value="/en_US/i/btn/btn_paynow_LG.gif" 
                                           onclick="javascript:if(this.value!='custom') jQuery('#togle_settings_paypal_payment_button_title').hide();" 
                                           <?php if ($paypal_button_type == '/en_US/i/btn/btn_paynow_LG.gif') echo ' checked="checked" '; ?> 
                                           />                                    
                                </label>
                                <label for="paypal_button_type_3" style="text-align: center;vertical-align: top;padding: 0 10px;">
                                    <img src="<?php echo WP_BK_PAYPAL_URL; ?>/en_US/i/btn/btn_paynow_SM.gif" style="margin: 0 0 25px;"/><br />
                                    <input type="radio" name="paypal_button_type" id="paypal_button_type_3" 
                                           value="/en_US/i/btn/btn_paynow_SM.gif" 
                                           onclick="javascript:if(this.value!='custom') jQuery('#togle_settings_paypal_payment_button_title').hide();"  
                                           <?php if ($paypal_button_type == '/en_US/i/btn/btn_paynow_SM.gif') echo ' checked="checked" '; ?> 
                                           />                                    
                                </label>
                                <label for="paypal_button_type_4" style="text-align: center;vertical-align: top;padding: 0 10px;">
                                    <div style="margin: 0 0 12px;"><?php _e('Custom button title' ,'booking'); ?></div><br/>
                                    <input type="radio" name="paypal_button_type" id="paypal_button_type_4" 
                                           value="custom" 
                                           onclick="javascript:if(this.value=='custom') jQuery('#togle_settings_paypal_payment_button_title').show();" 
                                           <?php if ($paypal_button_type == 'custom') echo ' checked="checked" '; ?> 
                                           />                                    
                                </label>
                          </fieldset>                                                      
                      </td>
                    </tr>
                    
                    <tr valign="top"><td colspan="2" style="padding:0px;">
                        <div style="margin: -10px 0 10px 50px;">    
                        <table id="togle_settings_paypal_payment_button_title" style="width:100%;<?php if ($paypal_button_type != 'custom') echo "display:none;";/**/ ?>" class="hided_settings_table">
                            <tr>
                            <th scope="row"><label for="paypal_payment_button_title" ><?php _e('Payment button title' ,'booking'); ?>:</label></th>
                                <td>
                                    <input value="<?php echo $paypal_payment_button_title; ?>" name="paypal_payment_button_title" id="paypal_payment_button_title"  type="text"   />
                                    <p class="description"><?php _e('Enter the title of the payment button' ,'booking');?></p>
                                </td>
                            </tr>
                        </table>
                        </div>
                    </td></tr>                    
                                                            
                    
                    <tr valign="top">
                      <th scope="row" ><?php _e('Show Payment description' ,'booking'); ?>:</th>
                      <td>
                        <fieldset>
                            <label for="paypal_is_description_show">
                                <input name="paypal_is_description_show" id="paypal_is_description_show" type="checkbox" 
                                    <?php if ($paypal_is_description_show == 'On') echo "checked";/**/ ?>  
                                    value="<?php echo $paypal_is_description_show; ?>" 
                                    onclick="javascript: if (this.checked) jQuery('#togle_settings_paypal_subject').slideDown('normal'); else  jQuery('#togle_settings_paypal_subject').slideUp('normal');"
                                 /><?php _e('Check this box to show payment description in payment form' ,'booking');?>
                            </label>
                        </fieldset>
                                                                            
                      </td>
                    </tr>
                    
                    <tr valign="top"><td colspan="2" style="padding:0px;">
                        <div style="margin: -10px 0 10px 50px;">    
                        <table id="togle_settings_paypal_subject" style="width:100%;<?php if ($paypal_is_description_show != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                            <tr>
                            <th scope="row"><label for="paypal_subject" ><?php _e('Payment description' ,'booking'); ?>:</label></th>
                                <td>
                                    <textarea id="paypal_subject" name="paypal_subject" ><?php echo $paypal_subject; ?></textarea>
                                    <p class="description"><?php printf(__('Enter the service name or the reason for the payment here.' ,'booking'),'<br/>','</b>');?></p>
                                    <div class="wpbc-info-message" style="text-align:left;"><?php 
                                        echo '<strong>';_e('Note:' ,'booking'); echo '</strong> ';
                                        printf(__('This field support only up to 70 characters by payment system.' ,'booking') );
                                    ?></div>
                                </td>
                            </tr>
                            <tr><th></th>
                                <td >                         
                                    <div  class="wpbc-help-message"  style="margin-top:-10px;">
                                      <p class="description"><strong><?php printf(__(' Use these shortcodes for customization: ' ,'booking'));?></strong></p>
                                      <p class="description">
                                        <?php printf(__('%s[bookingname]%s - inserting name of booking resource, ' ,'booking'),'<code>','</code>');?><br/>
                                        <?php printf(__('%s[dates]%s - inserting list of reserved dates ' ,'booking'),'<code>','</code>');?><br/>
                                        <?php printf(__('%s[datescount]%s - inserting number of reserved dates ' ,'booking'),'<code>','</code>');?>
                                      </p>
                                    <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
                                    </div>
                              </td>
                            </tr>                            
                        </table>
                        </div>
                    </td></tr>                    
                    
                    
                    <tr valign="top">
                      <th scope="row"><?php _e('Show Reference Text Box' ,'booking'); ?>:</th>
                      <td>
                        <fieldset>
                            <label for="paypal_is_reference_box">
                              <input <?php if ($paypal_is_reference_box == 'On') echo "checked";/**/ ?>
                                value="<?php echo $paypal_is_reference_box; ?>" name="paypal_is_reference_box" id="paypal_is_reference_box" type="checkbox"
                                onclick="javascript: if (this.checked) jQuery('#togle_settings_paypal_reference_title_box').slideDown('normal'); else  jQuery('#togle_settings_paypal_reference_title_box').slideUp('normal');"
                                /><?php _e('Check this box to show Reference Text Box' ,'booking'); ?>
                            </label>
                        </fieldset>                            
                      </td>
                    </tr>
                    
                    <tr valign="top"><td colspan="2" style="padding:0px;">
                        <div style="margin: -10px 0 10px 50px;">    
                        <table id="togle_settings_paypal_reference_title_box" style="width:100%;<?php if ($paypal_is_reference_box !== 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                            <tr>
                            <th scope="row"><label for="paypal_reference_title_box" ><?php _e('Reference Text Box Title' ,'booking'); ?>:</label></th>
                                <td>
                                    <input  name="paypal_reference_title_box" id="paypal_reference_title_box" 
                                        value="<?php echo $paypal_reference_title_box; ?>"
                                        class="regular-text code" type="text" size="45" />
                                    <p class="description"><?php printf(__('Enter a title for the Reference text box (i.e. Your email address). Visitors will see this text.' ,'booking'),'<b>','</b>');?></p>
                                </td>
                            </tr>
                        </table>
                        </div>
                    </td></tr>                    
                    
                    
                    <tr valign="top"><td style="padding:10px 0px; " colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>

                    <tr>
                        <td colspan="2">                          
                            <div  class="wpbc-help-message">
                            <p><?php printf(__('To use this feature you %smust activate auto-return link%s at your Paypal account.' ,'booking'),'<b>','</b>');?></p>
                            <strong><?php _e('Follow these steps to configure it:' ,'booking');?></strong>
                            <ol>
                                <li><?php _e('Log in to your PayPal account.' ,'booking');?></li>
                                <li><?php _e('Click the Profile subtab.' ,'booking');?></li>
                                <li><?php _e('Click Website Payment Preferences in the Seller Preferences column.' ,'booking');?></li>
                                <li><?php _e('Under Auto Return for Website Payments, click the On radio button.' ,'booking');?></li>
                                <li><?php _e('For the Return URL, enter the Return URL from PayPal on your site for successfull payment.' ,'booking');?></li>
                            </ol>
                          </div>                          
                        </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_return_url" ><?php _e('Return URL from PayPal' ,'booking'); ?>:</label>
                      </th>
                      <td>
                          <fieldset>
                            <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $paypal_return_url; ?>" name="paypal_return_url" id="paypal_return_url" class="regular-text code" type="text" size="45" />
                          </fieldset>                          
                          <p class="description"><?php 
                            _e('The URL where visitor will be redirected after completing payment.' ,'booking');?><br/><?php 
                            printf(__('For example, a URL to your site that displays a %s"Thank you for the payment"%s.' ,'booking'),'<b>','</b>');
                            ?></p>                          
                      </td>
                    </tr>

                    <tr valign="top">
                      <th scope="row">
                        <label for="paypal_cancel_return_url" ><?php _e('Cancel Return URL from PayPal' ,'booking'); ?>:</label>
                      </th>
                      <td>
                          <fieldset>
                            <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $paypal_cancel_return_url; ?>" name="paypal_cancel_return_url" id="paypal_cancel_return_url" class="regular-text code" type="text" size="45" />
                          </fieldset>                                                                              
                          <p class="description"><?php 
                            _e('The URL where the visitor will be redirected after completing payment.' ,'booking');?><br/><?php
                            printf(__('For example, the URL to your website that displays a %s"Payment Canceled"%s page.' ,'booking'),'<b>','</b>');?>
                          </p>
                      </td>
                    </tr>

                    <tr valign="top">
                        <th scope="row"><?php _e('Automatically approve/cancel booking' ,'booking'); ?>:</th>
                        <td>
                        <fieldset>
                            <label for="paypal_is_auto_approve_cancell_booking">                            
                                <input name="paypal_is_auto_approve_cancell_booking" id="paypal_is_auto_approve_cancell_booking" type="checkbox" 
                                    <?php if ($paypal_is_auto_approve_cancell_booking == 'On') echo "checked";/**/ ?>  
                                    value="<?php echo $paypal_is_auto_approve_cancell_booking; ?>" 
                                    /><?php _e('Check this box to automatically approve bookings, when visitor makes a successful payment, or automatically cancel the booking, when visitor makes a payment cancellation.' ,'booking'); ?>                                 
                            </label>
                            <p class="wpbc-info-message" style="text-align:left;"><strong><?php _e('Warning' ,'booking');?>!</strong> <?php _e('This will not work, if the visitor leaves the payment page.' ,'booking');?><p>
                        </fieldset>
                        </td>
                    </tr>

                    
                    
                </tbody>
            </table>

            <div class="clear" style="height:10px;"></div>
            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_form"/>
            <div class="clear" style="height:10px;"></div>

           </div>
          </div>
         </div>
        </div>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_paypal');

    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_define_payment_form_paypal($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ){
        $output = '';
        
        $is_show_in_payment_request = false;
        if (       (get_bk_option( 'booking_authorizenet_is_active' ) != 'On') 
                && (get_bk_option( 'booking_sage_is_active' ) != 'On')
                && (get_bk_option( 'booking_ipay88_is_active' ) != 'On')
                && (get_bk_option( 'booking_bank_transfer_is_active' ) != 'On')
                && (get_bk_option( 'booking_pay_cash_is_active' ) != 'On')
                && (isset($_GET['booking_pay']))
           ) $is_show_in_payment_request = true;
        
        if( ( get_bk_option( 'booking_paypal_is_active' ) == 'On' ) || ( $is_show_in_payment_request ) ) {



                   // $is_sand_box = true;
                    $is_sand_box = get_bk_option( 'booking_paypal_is_sandbox');

                    if ($is_sand_box == 'On') $is_sand_box = true;
                    else                      $is_sand_box = false;

                    $paypal_emeil               =  get_bk_option( 'booking_paypal_emeil' );
                    $paypal_curency             =  get_bk_option( 'booking_paypal_curency' );
                    $paypal_subject             =  get_bk_option( 'booking_paypal_subject' );
                    $paypal_subject           =  apply_bk_filter('wpdev_check_for_active_language', $paypal_subject );
                    $paypal_is_reference_box    =  get_bk_option( 'booking_paypal_is_reference_box' );           // checkbox
                    $paypal_reference_title_box =  get_bk_option( 'booking_paypal_reference_title_box' );
                    $paypal_return_url          =  get_bk_option( 'booking_paypal_return_url' );
                    $paypal_cancel_return_url   =  get_bk_option( 'booking_paypal_cancel_return_url' );
                    $paypal_button_type         =  get_bk_option( 'booking_paypal_button_type' );  // radio
                    $paypal_button_type         = str_replace( WP_BK_PAYPAL_URL, '', $paypal_button_type);
                    
                    $paypal_secure_merchant_id  =  get_bk_option( 'booking_paypal_secure_merchant_id'  );
                    $paypal_pro_hosted_solution =  get_bk_option( 'booking_paypal_pro_hosted_solution' );
                    $paypal_paymentaction       =  get_bk_option( 'booking_paypal_paymentaction' );
                    $paypal_subject = str_replace('[bookingname]',$bk_title[0]->title,$paypal_subject);
                    $paypal_subject = str_replace('[id]',$booking_id,$paypal_subject);
                    
                    $paypal_payment_button_title  =  get_bk_option( 'booking_paypal_payment_button_title' );
                    $paypal_payment_button_title  =  apply_bk_filter('wpdev_check_for_active_language', $paypal_payment_button_title );

                    
                    $booking_days_new_string = '';

                    if (! empty($booking_days_count)) {
                        $booking_days_new = explode(',',$booking_days_count);
                        foreach ($booking_days_new as $new_day) {
                            $new_day=trim($new_day);
                            if (strpos($new_day, '.')!==false) $new_day = explode('.',$new_day);
                            else                               $new_day = explode('-',$new_day);
                            $booking_days_new_string .= $new_day[2] .'-' . $new_day[1] .'-' . $new_day[0] . ' 00:00:00,';
                        }
                        $booking_days_new_string = substr($booking_days_new_string ,0,-1);
                    }

                    $my_short_dates = get_dates_short_format($booking_days_new_string );




                    $paypal_subject = str_replace('[dates]',$my_short_dates,$paypal_subject); //$paypal_subject .= ' Booking type: ' . $bk_title[0]->title . '. For period: ' . $booking_days_count;

                    $my_d_c = explode(',', $booking_days_count);
                    $my_d_c = count($my_d_c);
                    $paypal_subject = str_replace('[datescount]',$my_d_c,$paypal_subject);

                    $output = '<div style="width:100%;clear:both;margin-top:20px;"></div><div  class="paypal_div wpbc-payment-form" style="text-align:left;clear:both;">';
                    if ($paypal_pro_hosted_solution != 'On') {
                        if (! $is_sand_box ) { // Live
                            $output .= '<form action=\"https://www.paypal.com/cgi-bin/webscr\" method=\"post\" style="text-align:left;">';
                            $output .= '<input type=\"hidden\" name=\"rm\" value=\"2\">';

                        } else {               // Sandbox
                            $output .= '<form action=\"https://www.sandbox.paypal.com/cgi-bin/webscr\" method=\"post\" style="text-align:left;">';                            
                            $output .= '<input type=\"hidden\" name=\"rm\" value=\"1\">';
                                // Text which show at return merchant button and url
                                // $output .= ' <input type=\"hidden\" name=\"cbt\" value=\"'.'?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=paypal&stats=OK'.'\">';
                        }
                        $output .= '<input type=\"hidden\" name=\"cmd\" value=\"_xclick\" /> ';
                        $output .= '<input type=\"hidden\" name=\"amount\" size=\"10\" title=\"Cost\" value=\"'. $summ .'\" />';
                        $output .= '<input type=\"hidden\" name=\"business\" value=\"'.$paypal_emeil.'\" />';
                        $output .= '<input type=\"hidden\" name=\"no_shipping\" value=\"1\" /> <input type=\"hidden\" name=\"no_note\" value=\"1\" />  ';
                        
                    } else {    // Pro hosted sollution

                        if (! $is_sand_box ) { // Live
                            $output .= '<form action=\"https://securepayments.paypal.com/acquiringweb?cmd=_hosted-payment\" method=\"post\">';
                        } else {               // Sandbox
                            $output .= '<form action=\"https://securepayments.sandbox.paypal.com/acquiringweb?cmd=_hosted-payment\" method=\"post\">';
                        }
                        $output .='<input type=\"hidden\" name=\"cmd\" value=\"_hosted-payment\">';
                        $output .='<input type=\"hidden\" name=\"subtotal\" value=\"'. $summ .'\">';
                        $output .= '<input type=\"hidden\" name=\"business\" value=\"'.$paypal_secure_merchant_id.'\" />';
                    }
                    $output .= '<input type=\"hidden\" name=\"paymentaction\" value=\"'.$paypal_paymentaction.'\" />';
                    $locale = getBookingLocale();  //$locale = 'fr_FR'; // Load translation for calendar
                    if ( ( !empty( $locale ) ) && ( substr($locale,0,2) !== 'en')  )
                        $output .= '<input type=\"hidden\" name=\"lc" value=\"'.substr($locale,0,2).'\" />';
                    /**
                        AU – Australia
                        AT – Austria
                        BE – Belgium
                        BR – Brazil
                        CA – Canada
                        CH – Switzerland
                        CN – China
                        DE – Germany
                        ES – Spain
                        GB – United Kingdom
                        FR – France
                        IT – Italy
                        NL – Netherlands
                        PL – Poland
                        PT – Portugal
                        RU – Russia
                        US – United States
                        The following 5-character codes are also supported for languages in specific countries:
                            da_DK – Danish (for Denmark only)
                            he_IL – Hebrew (all)
                            id_ID – Indonesian (for Indonesia only)
                            ja_JP – Japanese (for Japan only)
                            no_NO – Norwegian (for Norway only)
                            pt_BR – Brazilian Portuguese (for Portugal and Brazil only)
                            ru_RU – Russian (for Lithuania, Latvia, and Ukraine only)
                            sv_SE – Swedish (for Sweden only)
                            th_TH – Thai (for Thailand only)
                            tr_TR – Turkish (for Turkey only)
                            zh_CN – Simplified Chinese (for China only)
                            zh_HK – Traditional Chinese (for Hong Kong only)
                            zh_TW – Traditional Chinese (for Taiwan only)
                     */

                    if ( strlen( WPDEV_BK_PLUGIN_URL .'/inc/payments/ipn.php' ) < 255 ) // Check for the PayPal 255 symbol restriction
                        $output .= '<input type=\"hidden\" name=\"notify_url\" value=\"'. WPDEV_BK_PLUGIN_URL .'/inc/payments/ipn.php' . '\" /> ';

                    $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_using_booking_id',false, $booking_id );
                    if ($my_booking_id_type !== false) {
                        $my_edited_bk_hash    = $my_booking_id_type[0];
                        $my_boook_type        = $my_booking_id_type[1];
                        $output .= '<input type=\"hidden\" name=\"custom\" value=\"'. $my_edited_bk_hash . '\" /> ';
                    }

                    if ($paypal_pro_hosted_solution != 'On')
                        $output .= '<input type=\"hidden\" name=\"item_number\" value=\"'. $booking_id . '\" /> ';

                    $cost_currency = apply_bk_filter('get_currency_info', 'paypal');

                    //$output .= "<strong>". get_booking_title($booking_type) .': ' . $my_d_c .' ' . ( ($my_d_c==1)?__('day' ,'booking'):  __('days' ,'booking')) ."</strong> ". '<br />';
                    $is_show_it = get_bk_option( 'booking_paypal_is_description_show' );
                    if ($is_show_it == 'On') $output .= $paypal_subject . '<br />';                    
                    
                    $summ_show = wpdev_bk_cost_number_format ( $summ  );
                    if ($is_deposit) $cost__title = __('Deposit' ,'booking')." : ";
                    else             $cost__title = __('Cost' ,'booking')." : ";
                    if ($cost_currency == $paypal_curency) $cost_summ_with_title = "<strong>".$cost__title . $summ_show ." " . $cost_currency ."</strong><br />";
                    else                                   $cost_summ_with_title = "<strong>".$cost__title . $cost_currency ." " . $summ_show ."</strong><br />";
                    $output .= $cost_summ_with_title;
                    /*
                    if ($is_deposit) {
                        $today_day = date('m.d.Y')  ;
                        $cost_summ_with_title .= ' ('  . $today_day .')';
                        make_bk_action('wpdev_make_update_of_remark' , $booking_id , $cost_summ_with_title , true );
                    }/**/
                    // Get all fields for biling info
                    $form_fields = get_form_content ($bkform, $booking_type, '', array('booking_id'=> $booking_id ,
                                                                              'resource_title'=> $bk_title ) );
                    $form_fields = $form_fields['_all_'];

                    if ($paypal_pro_hosted_solution != 'On')
                        if (  get_bk_option( 'booking_billing_customer_email' )  !== false ) {
                          $billing_customer_email  = (string) trim( get_bk_option( 'booking_billing_customer_email' ) . $booking_type );
                          if ( isset($form_fields[$billing_customer_email]) !== false ){
                              $email      = substr($form_fields[$billing_customer_email], 0, 127);
                              $output .= "<input type=\"hidden\" name=\"email\" value=\"$email\" />";
                          }
                        }

                    if ($paypal_pro_hosted_solution != 'On') $billing_prefix = '';
                    else                                     $billing_prefix = 'billing_';
                    if ( get_bk_option( 'booking_billing_firstnames' )  !== false ) {
                      $billing_firstnames      = (string) trim( get_bk_option( 'booking_billing_firstnames' ) . $booking_type );
                      if ( isset($form_fields[$billing_firstnames]) !== false ){
                          $first_name = substr($form_fields[$billing_firstnames], 0, 32);
                          $output .= "<input type=\"hidden\" name=\"".$billing_prefix."first_name\" value=\"$first_name\" />";
                      }
                    }
                    if ( get_bk_option( 'booking_billing_surname' )  !== false ) {
                      $billing_surname         = (string) trim( get_bk_option( 'booking_billing_surname' ) . $booking_type );
                      if ( isset($form_fields[$billing_surname]) !== false ){
                          $last_name  = substr($form_fields[$billing_surname], 0, 64);
                          $output .= "<input type=\"hidden\" name=\"".$billing_prefix."last_name\" value=\"$last_name\" />";
                      }
                    }
                    if ( get_bk_option( 'booking_billing_address1' )  !== false ) {
                      $billing_address1        = (string) trim( get_bk_option( 'booking_billing_address1' ) . $booking_type) ;
                      if ( isset($form_fields[$billing_address1]) !== false ){
                          $address1   = substr($form_fields[$billing_address1], 0, 100);
                          $output .= "<input type=\"hidden\" name=\"".$billing_prefix."address1\" value=\"$address1\" />";
                      }
                    }
                    if ( get_bk_option( 'booking_billing_city' )  !== false ) {
                      $billing_city            = (string) trim( get_bk_option( 'booking_billing_city' ) . $booking_type );
                      if ( isset($form_fields[$billing_city]) !== false ){
                          $city       = substr($form_fields[$billing_city], 0, 40);
                          $output .= "<input type=\"hidden\" name=\"".$billing_prefix."city\" value=\"$city\" />";
                      }
                    }
                    if ( get_bk_option( 'booking_billing_country' )  !== false ) {
                      $billing_country         = (string) trim( get_bk_option( 'booking_billing_country' ) . $booking_type );
                      if ( isset($form_fields[$billing_country]) !== false ){
                          $country    = substr($form_fields[$billing_country], 0, 2);
                          $output .= "<input type=\"hidden\" name=\"".$billing_prefix."country\" value=\"$country\" />";
                      }
                    }
                    if ( get_bk_option( 'booking_billing_post_code' )  !== false ) {
                          $billing_post_code       = (string) trim( get_bk_option( 'booking_billing_post_code' ) . $booking_type );
                          if ( isset($form_fields[$billing_post_code]) !== false ){
                              $zip        = substr($form_fields[$billing_post_code], 0, 32);
                              $output .= "<input type=\"hidden\" name=\"".$billing_prefix."zip\" value=\"$zip\" />";
                          }
                    }
                                // P a y P a l      f o r m  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                    $output .= "<input type=\"hidden\" name=\"item_name\" value=\"".substr($paypal_subject,0,127)."\" />";
                    $output .= "<input type=\"hidden\" name=\"currency_code\" value=\"$paypal_curency\" />";
                    //$output .= "<span style=\"font-size:10.0pt\"><strong> $paypal_subject</strong></span><br /><br />";


                    // Show the reference text box
                    if ($paypal_is_reference_box == 'On') {
                        $output .= "<br/><strong> $paypal_reference_title_box :</strong>";
                        $output .= '<input type=\"hidden\" name=\"on0\" value=\"Reference\" />';
                        $output .= '<input type=\"text\" name=\"os0\" maxlength=\"60\" /><br/><br/>';
                    }


                    $paypal_order_Successful  =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=paypal&stats=OK' ;
                    $output .= '<input type=\"hidden\" name=\"return\" value=\"'.$paypal_order_Successful.'\" />';

                    $paypal_order_Failed      =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=paypal&stats=FAILED' ;   //get_bk_option( 'booking_sage_order_failed' );
                    $output .= '<input type=\"hidden\" name=\"cancel_return\" value=\"'.$paypal_order_Failed.'\" />';

                    if ($paypal_button_type == 'custom')                        
                        $output .= "<input type=\"submit\" class=\"btn\" name=\"submit\"  value=\"".$paypal_payment_button_title."\" />";
                    else    
                        $output .= "<input type=\"image\" src=\"". WP_BK_PAYPAL_URL . $paypal_button_type . "\" name=\"submit\" style=\"border:none;\" alt=\"".__('Make payments with payPal - its fast, free and secure!' ,'booking')."\" />";
                    

        
        
                    $output .= '</form></div>';
                    // Auto redirect to the PayPal website,  after visitor clicked on "Send" button.
                    /*
                    ?><script type='text/javascript'> 
                        setTimeout(function() { 
                           jQuery("#paypalbooking_form<?php echo $booking_type;?> .paypal_div.wpbc-payment-form form").submit(); 
                        }, 500);                        
                    </script><?php /**/
                    // P a y P a l      f o r m  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        }        
        return   $output ;

    }
    add_bk_filter('wpdev_bk_define_payment_form_paypal', 'wpdev_bk_define_payment_form_paypal');
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // OK
    function wpbc_add_payment_status_ok__paypal( $payment_status ){
        
       $payment_status = array_merge( $payment_status,  array( 'PayPal:OK' )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_ok',  'wpbc_add_payment_status_ok__paypal');
    
    // Pending
    function wpbc_add_payment_status_pending__paypal( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_pending',  'wpbc_add_payment_status_pending__paypal');
    
    // Unknown
    function wpbc_add_payment_status_unknown__paypal( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_unknown',  'wpbc_add_payment_status_unknown__paypal');
    
    // Error
    function wpbc_add_payment_status_error__paypal( $payment_status ){
        
       $payment_status = array_merge( $payment_status,  array( 'PayPal:Failed' )  );
       
       return  $payment_status;        
    }    
    add_filter('wpbc_add_payment_status_error',    'wpbc_add_payment_status_error__paypal');    
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Checking response from  payment system
    function wpbc_check_response_status__paypal($response_status , $pay_system, $status, $booking_id, $wp_nonce) {
    
        if ($pay_system == 'paypal') { 
            $status = 'PayPal:' . $status; 
            return $status;
        } else 
            return $response_status;        
    }
    add_filter('wpbc_check_response_status',    'wpbc_check_response_status__paypal', 10, 5 );
    
    
    function wpbc_auto_approve_or_cancell_and_redirect__paypal($pay_system, $status, $booking_id) {
     
        if ($pay_system == 'paypal') {
            
            $auto_approve = get_bk_option( 'booking_paypal_is_auto_approve_cancell_booking'  );
            
            if ($status == 'PayPal:OK') {
                if ($auto_approve == 'On') 
                    check_auto_approve_or_cancell($booking_id, true );
                wpdev_redirect( get_bk_option( 'booking_paypal_return_url' ) )   ;
                
            } else {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, false );
                wpdev_redirect( get_bk_option( 'booking_paypal_cancel_return_url' ) )   ;
            }
        }
    }    
    add_bk_action( 'wpbc_auto_approve_or_cancell_and_redirect', 'wpbc_auto_approve_or_cancell_and_redirect__paypal');
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Activate
    function wpdev_bk_payment_activate_system_paypal() {
        global $wpdb;

        if ( wpdev_bk_is_this_demo() ) 
            add_bk_option( 'booking_paypal_emeil', 'Seller_1335004986_biz@wpdevelop.com');
        else
            add_bk_option( 'booking_paypal_emeil', get_option('admin_email') );
        add_bk_option( 'booking_paypal_secure_merchant_id' , '' );
        add_bk_option( 'booking_paypal_curency', 'USD' );
        add_bk_option( 'booking_paypal_subject', sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[bookingname]','[dates]'));
        add_bk_option( 'booking_paypal_payment_button_title' , __('Pay via' ,'booking') .' PayPal');

        add_bk_option( 'booking_paypal_is_active','On' );
        add_bk_option( 'booking_paypal_pro_hosted_solution','Off' );
        add_bk_option( 'booking_paypal_is_reference_box', 'Off' );           // checkbox
        add_bk_option( 'booking_paypal_reference_title_box', __('Enter your phone number'  ,'booking'));
        add_bk_option( 'booking_paypal_paymentaction', 'sale');
        add_bk_option( 'booking_paypal_return_url',         '/successful' );
        add_bk_option( 'booking_paypal_cancel_return_url',  '/failed' );
        add_bk_option( 'booking_paypal_button_type', '/en_US/i/btn/btn_paynowCC_LG.gif' );  // radio
        add_bk_option( 'booking_paypal_price_period' , 'day' );
        if ( wpdev_bk_is_this_demo() ) 
            add_bk_option( 'booking_paypal_is_sandbox','On');
        else
            add_bk_option( 'booking_paypal_is_sandbox','Off');
        add_bk_option( 'booking_paypal_is_description_show', 'Off' );
        add_bk_option( 'booking_paypal_is_auto_approve_cancell_booking', 'Off' );



        add_bk_option( 'booking_paypal_ipn_is_send_verified_email' , 'On');
        add_bk_option( 'booking_paypal_ipn_verified_email' ,get_option('admin_email'));
        add_bk_option( 'booking_paypal_ipn_is_send_invalid_email' , 'On');
        add_bk_option( 'booking_paypal_ipn_invalid_email' , get_option('admin_email') );
        add_bk_option( 'booking_paypal_ipn_is_send_error_email' , 'Off');
        add_bk_option( 'booking_paypal_ipn_error_email' , get_option('admin_email') );

        add_bk_option( 'booking_paypal_ipn_use_ssl' , 'On');
        add_bk_option( 'booking_paypal_ipn_use_curl' , 'Off');
    }
    add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_paypal');


    // Activate
    function wpdev_bk_payment_deactivate_system_paypal() {
        global $wpdb;
        
        delete_bk_option( 'booking_paypal_emeil' );
        delete_bk_option( 'booking_paypal_secure_merchant_id'  );
        delete_bk_option( 'booking_paypal_curency' );
        delete_bk_option( 'booking_paypal_subject' );
        delete_bk_option( 'booking_paypal_payment_button_title' );
        
        delete_bk_option( 'booking_paypal_is_active' );
        
        delete_bk_option( 'booking_paypal_pro_hosted_solution' );
        delete_bk_option( 'booking_paypal_is_reference_box' );           // checkbox
        delete_bk_option( 'booking_paypal_reference_title_box' );
        delete_bk_option( 'booking_paypal_paymentaction' );
        delete_bk_option( 'booking_paypal_return_url' );
        delete_bk_option( 'booking_paypal_cancel_return_url' );
        delete_bk_option( 'booking_paypal_button_type' );  // radio
        delete_bk_option( 'booking_paypal_price_period' );
        delete_bk_option( 'booking_paypal_is_sandbox');
        delete_bk_option( 'booking_paypal_is_description_show' );
        delete_bk_option( 'booking_paypal_is_auto_approve_cancell_booking'  );


        delete_bk_option( 'booking_paypal_ipn_is_send_verified_email' );
        delete_bk_option( 'booking_paypal_ipn_verified_email' );
        delete_bk_option( 'booking_paypal_ipn_is_send_invalid_email' );
        delete_bk_option( 'booking_paypal_ipn_invalid_email' );
        delete_bk_option( 'booking_paypal_ipn_is_send_error_email' );
        delete_bk_option( 'booking_paypal_ipn_error_email' );
        delete_bk_option( 'booking_paypal_ipn_use_ssl' );
        delete_bk_option( 'booking_paypal_ipn_use_curl' );

    }
    add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_paypal');
?>