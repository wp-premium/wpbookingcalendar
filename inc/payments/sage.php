<?php
/**
 * @version 2.0
 * @package Booking Calendar 
 * @subpackage SagePay payment gateway
 * @category Payment Gateways
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.05.06
 * @description_of_chnages: support of new Protocal v3.00
 * 
 *
 *  This is COMMERCIAL SCRIPT
 *  We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_payment_show_tab_in_top_settings_sage(){
        ?><a href="javascript:void(0)"
           onclick="javascript:
                   jQuery('.visibility_container').css('display','none');
                   jQuery('#visibility_container_sage').css('display','block');
                   jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                   jQuery(this).addClass('booking-submenu-tab-selected');"
               rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php
                                     if ( get_bk_option( 'booking_sage_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                                     original-title="<?php _e('Integration of Sage payment system' ,'booking');?>">
            <?php _e('Sage' ,'booking');?>
            <input type="checkbox"  <?php if ( get_bk_option( 'booking_sage_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
                   name="sage_is_active_dublicated" id="sage_is_active_dublicated"
                   onchange="document.getElementById('sage_is_active').checked=this.checked;" >
        </a>
        <script type="text/javascript">
            jQuery(document).ready( function(){
                recheck_active_itmes_in_top_menu('sage_is_active',   'sage_is_active_dublicated');
            });
        </script>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_sage');


    // Settings page for   S a g e
    function wpdev_bk_payment_show_settings_content_sage(){
            if ( isset( $_POST['sage_curency'] ) ) {
                  if (isset( $_POST['sage_is_active'] ))     $sage_is_active = 'On';
                  else                                       $sage_is_active = 'Off';
                  update_bk_option( 'booking_sage_is_active', $sage_is_active );
                  update_bk_option( 'booking_sage_subject', $_POST['sage_subject'] );
                  
                  update_bk_option( 'booking_sage_order_successful', wpbc_make_link_relative( $_POST['sage_order_successful'] ) );
                  update_bk_option( 'booking_sage_order_failed', wpbc_make_link_relative( $_POST['sage_order_failed'] ) );
                  
                  if ( ! wpdev_bk_is_this_demo() ) {
                    update_bk_option( 'booking_sage_test', $_POST['sage_test'] );  
                    update_bk_option( 'booking_sage_vendor_name', $_POST['sage_vendor_name'] );
                    update_bk_option( 'booking_sage_encryption_password', $_POST['sage_encryption_password'] );
                  }
                  update_bk_option( 'booking_sage_curency', $_POST['sage_curency'] );
                  update_bk_option( 'booking_sage_transaction_type', $_POST['sage_transaction_type'] );
                  update_bk_option( 'booking_sage_payment_button_title', $_POST['sage_payment_button_title'] );
                 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 if (isset( $_POST['sage_is_description_show'] ))     $sage_is_description_show = 'On';
                 else                                                   $sage_is_description_show = 'Off';
                 update_bk_option( 'booking_sage_is_description_show' , $sage_is_description_show );

                 if (isset( $_POST['sage_is_auto_approve_cancell_booking'] ))     $sage_is_auto_approve_cancell_booking = 'On';
                 else                                                   $sage_is_auto_approve_cancell_booking = 'Off';
                 update_bk_option( 'booking_sage_is_auto_approve_cancell_booking' , $sage_is_auto_approve_cancell_booking );

            }

            $sage_is_active         =  get_bk_option( 'booking_sage_is_active' );
            $sage_subject           =  get_bk_option( 'booking_sage_subject' );
            $sage_test              =  get_bk_option( 'booking_sage_test' );
            if ($sage_test == 'SIMULATOR')                                      //FixIn: 5.4.2
                $sage_test = 'TEST';
            $sage_order_successful  =  get_bk_option( 'booking_sage_order_successful' );
            $sage_order_failed      =  get_bk_option( 'booking_sage_order_failed' );
            $sage_vendor_name       =  get_bk_option( 'booking_sage_vendor_name' );
            $sage_encryption_password =  get_bk_option( 'booking_sage_encryption_password' );
            $sage_curency           =  get_bk_option( 'booking_sage_curency' );
            $sage_transaction_type  =  get_bk_option( 'booking_sage_transaction_type' );
            $sage_payment_button_title=  get_bk_option( 'booking_sage_payment_button_title' );
            $sage_is_description_show = get_bk_option( 'booking_sage_is_description_show' );
            $sage_is_auto_approve_cancell_booking  = get_bk_option( 'booking_sage_is_auto_approve_cancell_booking' );
            ?>
            <div id="visibility_container_sage" class="visibility_container" style="display:none;">
                    <div class='meta-box'>
                      <div <?php $my_close_open_win_id = 'bk_settings_costs_sage_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                            <h3 class='hndle'><span><?php _e('Sage payment customization' ,'booking'); ?></span></h3> <div class="inside">
                        <!--form  name="post_option_sage" action="" method="post" id="post_option_sage" -->
                            <div class="wpbc-success-message"><?php printf(__('If you have no account on this system, please visit %s to create one.' ,'booking')
                                    , '<a href="https://test.sagepay.com/mysagepay/login.msp"  target="_blank">sagepay.com</a>'); //FixIn: 5.4.2
                                    // , '<a href="https://support.sagepay.com/apply/RequestSimAccount.aspx"  target="_blank">sagepay.com</a>'); 
                                    ?></div>
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Active Sage Pay' ,'booking'); ?>:</th>
                                        <td>
                                            <fieldset>
                                                <label for="sage_is_active" >
                                                    <input <?php if ($sage_is_active == 'On') echo "checked"; ?>  value="<?php echo $sage_is_active; ?>" name="sage_is_active" id="sage_is_active" type="checkbox"
                                                           onchange="document.getElementById('sage_is_active_dublicated').checked=this.checked;"
                                                                                                                  />
                                                    <?php _e(' Check this box to use Sage Pay payment.' ,'booking');?>
                                                </label>
                                            </fieldset>                                            
                                        </td>
                                    </tr>


                                    <tr class="wpdevbk">
                                      <th class="well" style="padding:10px;">
                                        <label for="sage_vendor_name" ><?php _e('Vendor Name' ,'booking'); ?>:</label>
                                      </th>
                                      <td class="well" style="padding:10px;">
                                          <input value="<?php echo $sage_vendor_name; ?>" name="sage_vendor_name" id="sage_vendor_name" class="regular-text code" type="text" size="45" />
                                          <p class="description"><?php printf(__('Set this value to the Vendor Name assigned to you by Sage Pay or chosen when you applied.' ,'booking'),'<b>','</b>');?></p>
                                          <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                      </td>
                                    </tr>

                                    <tr class="wpdevbk">
                                      <th class="well" style="padding:10px;">
                                        <label for="sage_encryption_password" ><?php _e('XOR Encryption password' ,'booking'); ?>:</label>
                                      </th>
                                      <td class="well" style="padding:10px;">
                                          <input value="<?php echo $sage_encryption_password; ?>" name="sage_encryption_password" id="sage_encryption_password" class="regular-text code" type="text" size="45" />
                                          <p class="description"><?php printf(__('Set this value to the XOR Encryption password assigned to you by Sage Pay' ,'booking'),'<b>','</b>');?></p>
                                          <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                      </td>
                                    </tr>
                                    
                                    <tr valign="top"><td style="padding:10px 0px; " colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                                    
                                    
                                    <tr valign="top">
                                      <th scope="row"><label for="sage_test" ><?php _e('Chose payment mode' ,'booking'); ?>:</label></th>
                                      <td>
                                         <select id="sage_test" name="sage_test">
                                             <?php //FixIn: 5.4.2 - SIMULATOR does not exist  for protocal 3.00
                                             /*
                                            <option <?php if($sage_test == 'SIMULATOR') echo "selected"; ?> value="SIMULATOR"><?php _e('SIMULATOR' ,'booking'); ?></option>
                                              */ ?>
                                            <option <?php if($sage_test == 'TEST') echo "selected"; ?> value="TEST"><?php _e('TEST' ,'booking'); ?></option>
                                            <option <?php if($sage_test == 'LIVE') echo "selected"; ?> value="LIVE"><?php _e('LIVE' ,'booking'); ?></option>
                                         </select>
                                         <span class="description"><?php printf(__('Select TEST for the Test Server and LIVE in the live environment' ,'booking'),'<b>','</b>');?></span>
                                         <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                      </td>
                                    </tr>
                                    
                                    <tr valign="top">
                                      <th scope="row"><label for="sage_transaction_type" ><?php _e('Transaction type' ,'booking'); ?>:</label></th>
                                      <td>
                                         <select id="sage_transaction_type" name="sage_transaction_type">
                                            <option <?php if($sage_transaction_type == 'PAYMENT') echo "selected"; ?> value="PAYMENT"><?php _e('PAYMENT' ,'booking'); ?></option>
                                            <option <?php if($sage_transaction_type == 'DEFERRED') echo "selected"; ?> value="DEFERRED"><?php _e('DEFERRED' ,'booking'); ?></option>
                                            <option <?php if($sage_transaction_type == 'AUTHENTICATE') echo "selected"; ?> value="AUTHENTICATE"><?php _e('AUTHENTICATE' ,'booking'); ?></option>
                                         </select>
                                         <span class="description"><?php printf(__('This can be DEFERRED or AUTHENTICATED if your Sage Pay account supports those payment types' ,'booking'),'<b>','</b>');?></span>
                                      </td>
                                    </tr>
                                    
                                    <tr valign="top">
                                      <th scope="row"><label for="sage_curency" ><?php _e('Accepted Currency' ,'booking'); ?>:</label></th>
                                      <td>
                                         <select id="sage_curency" name="sage_curency">
                                            <option <?php if($sage_curency == 'USD') echo "selected"; ?> value="USD"><?php _e('U.S. Dollars' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'EUR') echo "selected"; ?> value="EUR"><?php _e('Euros' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'GBP') echo "selected"; ?> value="GBP"><?php _e('Pounds Sterling' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'JPY') echo "selected"; ?> value="JPY"><?php _e('Yen' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'AUD') echo "selected"; ?> value="AUD"><?php _e('Australian Dollars' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'CAD') echo "selected"; ?> value="CAD"><?php _e('Canadian Dollars' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'NZD') echo "selected"; ?> value="NZD"><?php _e('New Zealand Dollar' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'CHF') echo "selected"; ?> value="CHF"><?php _e('Swiss Franc' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'HKD') echo "selected"; ?> value="HKD"><?php _e('Hong Kong Dollar' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'SGD') echo "selected"; ?> value="SGD"><?php _e('Singapore Dollar' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'SEK') echo "selected"; ?> value="SEK"><?php _e('Swedish Krona' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'DKK') echo "selected"; ?> value="DKK"><?php _e('Danish Krone' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'PLN') echo "selected"; ?> value="PLN"><?php _e('Polish Zloty' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'NOK') echo "selected"; ?> value="NOK"><?php _e('Norwegian Krone' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'HUF') echo "selected"; ?> value="HUF"><?php _e('Hungarian Forint' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'CZK') echo "selected"; ?> value="CZK"><?php _e('Czech Koruna' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'ILS') echo "selected"; ?> value="ILS"><?php _e('Israeli Shekel' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'MXN') echo "selected"; ?> value="MXN"><?php _e('Mexican Peso' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'BRL') echo "selected"; ?> value="BRL"><?php _e('Brazilian Real (only for Brazilian users)' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'MYR') echo "selected"; ?> value="MYR"><?php _e('Malaysian Ringgits (only for Malaysian users)' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'PHP') echo "selected"; ?> value="PHP"><?php _e('Philippine Pesos' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'TWD') echo "selected"; ?> value="TWD"><?php _e('Taiwan New Dollars' ,'booking'); ?></option>
                                            <option <?php if($sage_curency == 'THB') echo "selected"; ?> value="THB"><?php _e('Thai Baht' ,'booking'); ?></option>
                                         </select>
                                         <span class="description"><?php printf(__('The currency code that gateway will process the payment in.' ,'booking'),'<b>','</b>');?></span>
                                      </td>
                                    </tr>
                                    
                                    <tr>
                                      <th><label for="sage_payment_button_title" ><?php _e('Payment button title' ,'booking'); ?>:</label></th>
                                      <td>
                                          <input value="<?php echo $sage_payment_button_title; ?>" name="sage_payment_button_title" id="sage_payment_button_title" class="regular-text code" type="text" size="45" />
                                          <p class="description"><?php printf(__('Enter the title of the payment button' ,'booking'),'Authorize.Net');?></p>
                                      </td>
                                    </tr>
                                    
                                    

                                <tr valign="top">
                                  <th scope="row" ><?php _e('Show Payment description' ,'booking'); ?>:</th>
                                  <td>
                                    <fieldset>
                                        <label for="sage_is_description_show">
                                            <input name="sage_is_description_show" id="sage_is_description_show" type="checkbox" 
                                                <?php if ($sage_is_description_show == 'On') echo "checked";/**/ ?>  
                                                value="<?php echo $sage_is_description_show; ?>" 
                                                onclick="javascript: if (this.checked) jQuery('#togle_settings_sage_subject').slideDown('normal'); else  jQuery('#togle_settings_sage_subject').slideUp('normal');"
                                             /><?php _e('Check this box to show payment description in payment form' ,'booking');?>
                                        </label>
                                    </fieldset>
                                  </td>
                                </tr>
                                

                                <tr valign="top"><td colspan="2" style="padding:0px;">
                                    <div style="margin: 0px 0 10px 50px;">    
                                    <table id="togle_settings_sage_subject" style="width:100%;<?php if ($sage_is_description_show != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                        <tr>
                                        <th scope="row"><label for="sage_subject" ><?php _e('Payment description' ,'booking'); ?>:</label></th>
                                            <td>
                                                <textarea  id="sage_subject" name="sage_subject" class="regular-text code" ><?php echo $sage_subject; ?></textarea>
                                                <p class="description"><?php printf(__('Enter the service name or the reason for the payment here.' ,'booking'),'<br/>','</b>');?></p>
                                            </td>
                                        </tr>
                                        <tr><th></th>
                                            <td >                         
                                                <div  class="wpbc-help-message"  style="margin-top:-10px;">
                                                    <p class="description">
                                                        <strong>&nbsp;<?php printf(__('Use these shortcodes for customization: ' ,'booking'));?></strong><br/>
                                                        <?php printf(__('%s[bookingname]%s - inserting name of booking resource, ' ,'booking'),'<code>','</code>');?><br/>
                                                        <?php printf(__('%s[dates]%s - inserting list of reserved dates ' ,'booking'),'<code>','</code>');?><br/>
                                                        <?php printf(__('%s[datescount]%s - inserting number of reserved dates ' ,'booking'),'<code>','</code>');?><br/>
                                                        <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
                                                    </p>
                                                </div>
                                          </td>
                                        </tr>                            
                                    </table>
                                    </div>
                                </td></tr>                    
                                
                                    
                                <tr><td colspan="2" style="border-bottom:1px solid #ccc"></td></tr>

                                <tr>
                                  <th><label for="sage_order_successful" ><?php _e('Return URL after Successful order' ,'booking'); ?>:</label></th>
                                  <td class="wpbc-settings-data-field0">  
                                      <fieldset>
                                        <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $sage_order_successful; ?>" name="sage_order_successful" id="sage_order_successful" class="regular-text code" type="text" size="45" />
                                      </fieldset>                                        
                                      <p class="description"><?php printf(__('Enter a return relative Successful URL. %s will redirect visitors to this page after Successful Payment' ,'booking'),'Sage Pay');?><br/>
                                       <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo  get_option('siteurl') . $sage_order_successful; ?>" target="_blank"><?php echo  get_option('siteurl') . $sage_order_successful; ?></a></p>
                                  </td>
                                </tr>

                                <tr    >
                                  <th><label for="sage_order_failed" ><?php _e('Return URL after Failed order' ,'booking'); ?>:</label></th>
                                  <td class="wpbc-settings-data-field0">   
                                      <fieldset>
                                        <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $sage_order_failed; ?>" name="sage_order_failed" id="sage_order_failed" class="regular-text code" type="text" size="45" />
                                      </fieldset>                                        
                                      <p class="description"><?php printf(__('Enter a return relative Failed URL. %s will redirect visitors to this page after Failed Payment' ,'booking'),'Sage Pay');?><br/>
                                       <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo   get_option('siteurl') . $sage_order_failed; ?>" target="_blank"><?php  echo get_option('siteurl') . $sage_order_failed; ?></a></p>
                                  </td>
                                </tr>

                                <tr>
                                  <th><?php _e('Automatically approve/cancel booking' ,'booking'); ?>:</th>
                                  <td class="wpbc-settings-data-field0">  
                                      <fieldset>
                                      <label for="sage_is_auto_approve_cancell_booking" class="description">   
                                        <input name="sage_is_auto_approve_cancell_booking" id="sage_is_auto_approve_cancell_booking" type="checkbox"
                                            <?php if ($sage_is_auto_approve_cancell_booking == 'On') echo "checked";/**/ ?>  
                                            value="<?php echo $sage_is_auto_approve_cancell_booking; ?>" 
                                             />
                                        <?php _e('Check this box to automatically approve bookings when visitor makes a successful payment, or automatically cancel the booking when visitor makes a payment cancellation.' ,'booking');?>
                                      </label>
                                      <p class="wpbc-info-message" style="text-align:left;"><strong><?php _e('Warning' ,'booking');?>!</strong> <?php _e('This will not work, if the visitor leaves the payment page.' ,'booking');?><p>    
                                      </fieldset>
                                    </td>
                                </tr>

                                <?php
                                $strCustomerEMail      = "";

                                $strBillingFirstnames  = "John";
                                $strBillingSurname     = "Smith";
                                $strBillingAddress1    = "Street";
                                $strBillingAddress2    = "";
                                $strBillingCity        = "London";
                                $strBillingPostCode    = "32432";
                                $strBillingCountry     = "UK";
                                $strBillingState       = "";
                                $strBillingPhone       = "";

                                $strConnectTo="SIMULATOR";                                  //Set to SIMULATOR for the Simulator expert system, TEST for the Test Server and LIVE in the live environment
                                $orderSuccessful = 'good.php';
                                $orderFailed = 'bad.php';
                                $strYourSiteFQDN= site_url() . "/";              //"http://wp/";  // IMPORTANT.  Set the strYourSiteFQDN value to the Fully Qualified Domain Name of your server. **** This should start http:// or https:// and should be the name by which our servers can call back to yours **** i.e. it MUST be resolvable externally, and have access granted to the Sage Pay servers **** examples would be https://www.mysite.com or http://212.111.32.22/ **** NOTE: You should leave the final / in place.
                                //TODO: Define from settings page
                                $strVendorName="";                                 // Set this value to the Vendor Name assigned to you by Sage Pay or chosen when you applied **/
                                $strEncryptionPassword="";                  // Set this value to the XOR Encryption password assigned to you by Sage Pay **/
                                $strCurrency="USD";                                         // Set this to indicate the currency in which you wish to trade. You will need a merchant number in this currency **/
                                $strTransactionType="PAYMENT";                              // This can be DEFERRED or AUTHENTICATED if your Sage Pay account supports those payment types **/
                                $strPartnerID="";                                           // Optional setting. If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id set it here. **/
                                $bSendEMail=0;                                              // Optional setting. ** 0 = Do not send either customer or vendor e-mails, ** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). ** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided.
                                $strVendorEMail="";                                         // Optional setting. Set this to the mail address which will receive order confirmations and failures
                                $strProtocol="2.23";

                                ?>

                                </tbody>
                            </table>
                            
                            <div class="wpbc-error-message" style="text-align:left;">                            
                                <strong><?php printf(__('Important!' ,'booking') );?></strong><br/>
                                <?php printf(__('Please configure %s fields inside the %sBilling form fields%s TAB at this page, this is necessary for the %s.' ,'booking'),'<b>' .__('ALL' ,'booking') . '</b>', '<b>"', '"</b>','Sage Pay' ); ?>
                            </div>   

                            <div class="clear" style="height:10px;"></div>
                            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="sagesubmit"/>
                            <div class="clear" style="height:10px;"></div>

                        <!--/form-->
                   </div> </div> </div>
            </div>
              <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_sage');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_define_payment_form_sage($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ){
        $output = '';
        if( (get_bk_option( 'booking_sage_is_active' ) == 'On')   ) {
                // Need to set status of bookings
                // Pending, Aproved, Payed

                $form_fields = get_form_content ($bkform, $booking_type, '', array('booking_id'=> $booking_id ,
                                                                              'resource_title'=> $bk_title ) );
                $form_fields = $form_fields['_all_'];

                $sage_is_active         =  get_bk_option( 'booking_sage_is_active' );
                if ($sage_is_active != 'On')  return '';

                $sage_subject           =  get_bk_option( 'booking_sage_subject' );
                $sage_subject           =  apply_bk_filter('wpdev_check_for_active_language', $sage_subject );
                $sage_subject = str_replace('[bookingname]',$bk_title[0]->title,$sage_subject);
                $sage_subject = str_replace('[dates]',$booking_days_count,$sage_subject); //$paypal_subject .= ' Booking type: ' . $bk_title[0]->title . '. For period: ' . $booking_days_count;
                    $my_d_c = explode(',', $booking_days_count);
                    $my_d_c = count($my_d_c);
                    $sage_subject = str_replace('[datescount]',$my_d_c,$sage_subject);

                $subject_payment = $sage_subject;



                $sage_test              =  get_bk_option( 'booking_sage_test' );
                if ($sage_test == 'SIMULATOR')                                      //FixIn: 5.4.2
                    $sage_test = 'TEST';
                
                $sage_order_successful  =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=sage&stats=OK' ;   //get_bk_option( 'booking_sage_order_successful' );
                $sage_order_failed      =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=sage&stats=FAILED' ;   //get_bk_option( 'booking_sage_order_failed' );
                $sage_vendor_name       =  get_bk_option( 'booking_sage_vendor_name' );
                $sage_encryption_password =  get_bk_option( 'booking_sage_encryption_password' );
                $sage_curency           =  get_bk_option( 'booking_sage_curency' );
                $sage_transaction_type  =  get_bk_option( 'booking_sage_transaction_type' );
                $sage_payment_button_title  =  get_bk_option( 'booking_sage_payment_button_title' );
                $sage_payment_button_title  =  apply_bk_filter('wpdev_check_for_active_language', $sage_payment_button_title );

                if ( empty( $sage_test ) ) return '';
                if ( empty( $sage_order_successful ) ) return '';
                if ( empty( $sage_order_failed ) ) return '';
                if ( empty( $sage_vendor_name ) ) return '';
                if ( empty( $sage_encryption_password ) ) return '';
                if ( empty( $sage_curency ) ) return '';
                if ( empty( $sage_transaction_type ) ) return '';

                // Get all fields for biling info
                $sage_billing_customer_email  = (string) trim(get_bk_option( 'booking_billing_customer_email' ) . $booking_type );
                $sage_billing_firstnames      = (string) trim( get_bk_option( 'booking_billing_firstnames' ) . $booking_type );
                $sage_billing_surname         = (string) trim( get_bk_option( 'booking_billing_surname' ) . $booking_type );
                $sage_billing_address1        = (string) trim( get_bk_option( 'booking_billing_address1' ) . $booking_type) ;
                $sage_billing_city            = (string) trim( get_bk_option( 'booking_billing_city' ) . $booking_type );
                $sage_billing_country         = (string) trim( get_bk_option( 'booking_billing_country' ) . $booking_type );
                $sage_billing_post_code       = (string) trim( get_bk_option( 'booking_billing_post_code' ) . $booking_type );
                $sage_billing_state           = (string) trim( get_bk_option( 'booking_billing_state' ) . $booking_type );

                // Check if all fields set, if no so then return empty
                if ( isset($form_fields[$sage_billing_customer_email]) === false ) return '';
                if ( isset($form_fields[$sage_billing_firstnames]) === false ) return '';
                if ( isset($form_fields[$sage_billing_surname]) === false ) return '';
                if ( isset($form_fields[$sage_billing_address1]) === false ) return '';
                if ( isset($form_fields[$sage_billing_city]) === false ) return '';
                if ( isset($form_fields[$sage_billing_country]) === false ) return '';
                if ( isset($form_fields[$sage_billing_post_code]) === false ) return '';


                    $strConnectTo=$sage_test;                                   //Set to SIMULATOR for the Simulator expert system, TEST for the Test Server and LIVE in the live environment
                    $orderSuccessful = $sage_order_successful;
                    $orderFailed     = $sage_order_failed ;
                    $strYourSiteFQDN= site_url() . "/";              //"http://wp/";  // IMPORTANT.  Set the strYourSiteFQDN value to the Fully Qualified Domain Name of your server. **** This should start http:// or https:// and should be the name by which our servers can call back to yours **** i.e. it MUST be resolvable externally, and have access granted to the Sage Pay servers **** examples would be https://www.mysite.com or http://212.111.32.22/ **** NOTE: You should leave the final / in place.


                    $strVendorName=$sage_vendor_name;                           // Set this value to the Vendor Name assigned to you by Sage Pay or chosen when you applied **/
                    $strEncryptionPassword=$sage_encryption_password;           // Set this value to the XOR Encryption password assigned to you by Sage Pay **/
                    $strCurrency=$sage_curency;                                 // Set this to indicate the currency in which you wish to trade. You will need a merchant number in this currency **/
                    $strTransactionType=$sage_transaction_type;                 // This can be DEFERRED or AUTHENTICATED if your Sage Pay account supports those payment types **/
                    $strPartnerID="";                                           // Optional setting. If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id set it here. **/
                    $bSendEMail=0;                                              // Optional setting. ** 0 = Do not send either customer or vendor e-mails, ** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). ** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided.
                    $strVendorEMail="";                                         // Optional setting. Set this to the mail address which will receive order confirmations and failures
                    //FixIn: 5.4.2
                    $strProtocol="3.00";

                    //FixIn: 5.4.2 - Protocol 3.00 does not support simulator
                    if ($strConnectTo=="LIVE")      $strPurchaseURL="https://live.sagepay.com/gateway/service/vspform-register.vsp";
                    //elseif ($strConnectTo=="TEST")  $strPurchaseURL="https://test.sagepay.com/gateway/service/vspform-register.vsp";
                    //else                            $strPurchaseURL="https://test.sagepay.com/simulator/vspformgateway.asp";
                    else $strPurchaseURL="https://test.sagepay.com/gateway/service/vspform-register.vsp";
                                                                    
//TODO: get from booking form (or from other form ?



$strCustomerEMail      = $form_fields[$sage_billing_customer_email] ;

$strBillingFirstnames  = $form_fields[$sage_billing_firstnames];
$strBillingSurname     = $form_fields[$sage_billing_surname];
$strBillingAddress1    = $form_fields[$sage_billing_address1];
$strBillingAddress2    = "";
$strBillingCity        = $form_fields[$sage_billing_city];
$strBillingPostCode    = $form_fields[$sage_billing_post_code];
$strBillingCountry     = $form_fields[$sage_billing_country];
$strBillingState       = "";
if ( ( $strBillingCountry == 'US') &&  ( ! empty( $form_fields[$sage_billing_state] ) ) )
    $strBillingState = $form_fields[$sage_billing_state];
        
$strBillingPhone       = "";

                        $bIsDeliverySame       = true;//$_SESSION["bIsDeliverySame"];
                        if ($bIsDeliverySame == true) {
                            $strDeliveryFirstnames = $strBillingFirstnames;
                            $strDeliverySurname    = $strBillingSurname;
                            $strDeliveryAddress1   = $strBillingAddress1;
                            $strDeliveryAddress2   = $strBillingAddress2;
                            $strDeliveryCity       = $strBillingCity;
                            $strDeliveryPostCode   = $strBillingPostCode;
                            $strDeliveryCountry    = $strBillingCountry;
                            $strDeliveryState      = $strBillingState;
                            $strDeliveryPhone      = $strBillingPhone;
                        } else {
                            $strDeliveryFirstnames = "";//$_SESSION["strDeliveryFirstnames"];
                            $strDeliverySurname    = "";//$_SESSION["strDeliverySurname"];
                            $strDeliveryAddress1   = "";//$_SESSION["strDeliveryAddress1"];
                            $strDeliveryAddress2   = "";//$_SESSION["strDeliveryAddress2"];
                            $strDeliveryCity       = "";//$_SESSION["strDeliveryCity"];
                            $strDeliveryPostCode   = "";//$_SESSION["strDeliveryPostCode"];
                            $strDeliveryCountry    = "";//$_SESSION["strDeliveryCountry"];
                            $strDeliveryState      = "";//$_SESSION["strDeliveryState"];
                            $strDeliveryPhone      = "";//$_SESSION["strDeliveryPhone"];
                        }
                        $intRandNum = rand(0,32000)*rand(0,32000);                  // Okay, build the crypt field for Form using the information in our session ** First we need to generate a unique VendorTxCode for this transaction **  We're using VendorName, time stamp and a random element.  You can use different methods if you wish *  but the VendorTxCode MUST be unique for each transaction you send to Server
                        $strVendorTxCode=$strVendorName . $intRandNum;

                        $subject_payment = str_replace(':','.',$subject_payment);
                        $summ = str_replace(',','.',$summ);
                        $strBasket = '1:'.$subject_payment.':::::'.$summ;

                        $strPost="VendorTxCode=" . $strVendorTxCode;                                    // Now to build the Form crypt field.  For more details see the Form Protocol 2.23 As generated above

                        if (strlen($strPartnerID) > 0) $strPost=$strPost . "&ReferrerID=" . $strPartnerID;      // Optional: If you are a Sage Pay Partner and wish to flag the transactions with your unique partner id, it should be passed here
                        $strPost=$strPost . "&Amount=" . number_format($summ,2); // Formatted to 2 decimal places with leading digit
                        $strPost=$strPost . "&Currency=" . $strCurrency;
                        $strPost=$strPost . "&Description=" . substr($subject_payment,0,100);                         // Up to 100 chars of free format description
                        $strPost=$strPost . "&SuccessURL=" . /*$strYourSiteFQDN .*/ $orderSuccessful  ;    // The SuccessURL is the page to which Form returns the customer if the transaction is successful. You can change this for each transaction, perhaps passing a session ID or state flag if you wish
                        $strPost=$strPost . "&FailureURL=" . /*$strYourSiteFQDN .*/ $orderFailed      ;    // The FailureURL is the page to which Form returns the customer if the transaction is unsuccessful You can change this for each transaction, perhaps passing a session ID or state flag if you wish
                        $strPost=$strPost . "&CustomerName=" . $strBillingFirstnames . " " . $strBillingSurname;        // This is an Optional setting. Here we are just using the Billing names given.
                        $strPost=$strPost . "&SendEMail=1";
                        /* Email settings:
                        ** Flag 'SendEMail' is an Optional setting.
                        ** 0 = Do not send either customer or vendor e-mails,
                        ** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT).
                        ** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided. **

                        */
                        $strPost=$strPost . "&CustomerEMail=".$strCustomerEMail;
                        $strPost=$strPost . "&VendorEMail=".get_bk_option( 'booking_paypal_emeil' );
$strPost=$strPost . "&BillingFirstnames=" . $strBillingFirstnames;              // Billing Details:
$strPost=$strPost . "&BillingSurname=" . $strBillingSurname;
$strPost=$strPost . "&BillingAddress1=" . $strBillingAddress1;
if (strlen($strBillingAddress2) > 0) $strPost=$strPost . "&BillingAddress2=" . $strBillingAddress2;
$strPost=$strPost . "&BillingCity=" . $strBillingCity;
$strPost=$strPost . "&BillingPostCode=" . $strBillingPostCode;
$strPost=$strPost . "&BillingCountry=" . $strBillingCountry;
if (strlen($strBillingState) > 0) $strPost=$strPost . "&BillingState=" . $strBillingState;
if (strlen($strBillingPhone) > 0) $strPost=$strPost . "&BillingPhone=" . $strBillingPhone;


$strPost=$strPost . "&DeliveryFirstnames=" . $strDeliveryFirstnames;            // Delivery Details:
$strPost=$strPost . "&DeliverySurname=" . $strDeliverySurname;
$strPost=$strPost . "&DeliveryAddress1=" . $strDeliveryAddress1;
if (strlen($strDeliveryAddress2) > 0) $strPost=$strPost . "&DeliveryAddress2=" . $strDeliveryAddress2;
$strPost=$strPost . "&DeliveryCity=" . $strDeliveryCity;
$strPost=$strPost . "&DeliveryPostCode=" . $strDeliveryPostCode;
$strPost=$strPost . "&DeliveryCountry=" . $strDeliveryCountry;
if (strlen($strDeliveryState) > 0) $strPost=$strPost . "&DeliveryState=" . $strDeliveryState;
if (strlen($strDeliveryPhone) > 0) $strPost=$strPost . "&DeliveryPhone=" . $strDeliveryPhone;


                        $strPost=$strPost . "&Basket=" . $strBasket; // As created above
                        $strPost=$strPost . "&AllowGiftAid=0";                                          // For charities registered for Gift Aid, set to 1 to display the Gift Aid check box on the payment pages
                        if ($strTransactionType!=="AUTHENTICATE") $strPost=$strPost . "&ApplyAVSCV2=0"; // Allow fine control over AVS/CV2 checks and rules by changing this value. 0 is Default. It can be changed dynamically, per transaction, if you wish.  See the Server Protocol document
                        $strPost=$strPost . "&Apply3DSecure=0";                                         // Allow fine control over 3D-Secure checks and rules by changing this value. 0 is Default. It can be changed dynamically, per transaction, if you wish.  See the Form Protocol document
                        
                        $strCrypt = WPBC_SagepayUtil::encryptAes( $strPost,$strEncryptionPassword );                               //FixIn: 5.4.2     

                        $output = '<div style="width:100%;clear:both;margin-top:20px;"></div><div class="sage_div wpbc-payment-form" style="text-align:left;clear:both;">';   // This form is all that is required to submit the payment information to the system -->
                        $output .= '<form action=\"'.$strPurchaseURL.'\" method=\"POST\" id=\"SagePayForm\" name=\"SagePayForm\" style=\"text-align:left;\" class=\"booking_SagePayForm\">';
                        $output .= '<input type=\"hidden\" name=\"navigate\" value=\"\" />';
                        $output .= '<input type=\"hidden\" name=\"VPSProtocol\" value=\"'.$strProtocol.'\">';
                        $output .= '<input type=\"hidden\" name=\"TxType\" value=\"'.$strTransactionType.'\">';
                        $output .= '<input type=\"hidden\" name=\"Vendor\" value=\"'.$strVendorName.'\">';
                        $output .= '<input type=\"hidden\" name=\"Crypt\" value=\"'.$strCrypt.'\">';
                        $is_show_it = get_bk_option( 'booking_sage_is_description_show' );
                        if ($is_show_it == 'On')
                            $output .= $sage_subject . '<br />';

                        
                        $cost_currency = apply_bk_filter('get_currency_info', 'sage');
                        $summ_show = wpdev_bk_cost_number_format ( $summ );
                        if ($is_deposit) $cost__title = __('Deposit' ,'booking')." : ";
                        else             $cost__title = __('Cost' ,'booking')." : ";

                        if ($cost_currency == $sage_curency) $cost_summ_with_title = "<strong>".$cost__title. $summ_show ." " . $sage_curency ."</strong><br/>";
                        else                                 $cost_summ_with_title = "<strong>".$cost__title. $cost_currency ." " . $summ_show ."</strong><br />";

                        $output .= $cost_summ_with_title;
                        /*
                        if ($is_deposit) {
                            $today_day = date('m.d.Y')  ;
                            $cost_summ_with_title .= ' ('  . $today_day .')';
                            make_bk_action('wpdev_make_update_of_remark' , $booking_id , $cost_summ_with_title , true );
                        }/**/


                        $output .= '<input type=\"submit\" name=\"submitsagebutton\" value=\"'.$sage_payment_button_title.'\" class=\"btn\">';
                        $output .= "<br/><span style=\"font-size:11px;\">".sprintf(__('Pay using %s payment service' ,'booking'), '<a href="http://www.sagepay.com/" target="_blank">Sage Pay</a>').".</span>";
                        //$output .= '<a href=\"javascript:SagePayForm.submit();\" title=\"Proceed to Form registration\"><img src=\"images/proceed.gif\" alt=\"Proceed to Form registration\" border=\"0\"></a>';
                        $output .= '</form></div>';
                        // Auto redirect to the Payment website,  after visitor clicked on "Send" button.
                        /*
                        ?><script type='text/javascript'> 
                            setTimeout(function() { 
                               jQuery("#paypalbooking_form<?php echo $booking_type;?> .sage_div.wpbc-payment-form form").submit(); 
                            }, 500);                        
                        </script><?php /**/


        }
        return $output ;

    }
    add_bk_filter('wpdev_bk_define_payment_form_sage', 'wpdev_bk_define_payment_form_sage');


                
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                
    // OK            
    function wpbc_add_payment_status_ok__sage( $payment_status ){
       $payment_status = array_merge( $payment_status, 
                                        array(
                                            'Sage:OK'
                                           ) 
                            );        
        return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_ok',  'wpbc_add_payment_status_ok__sage');
    
    // Pending
    function wpbc_add_payment_status_pending__sage( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_pending',  'wpbc_add_payment_status_pending__sage');
    
    // Unknown
    function wpbc_add_payment_status_unknown__sage( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_unknown',  'wpbc_add_payment_status_unknown__sage');
    
    // Error
    function wpbc_add_payment_status_error__sage( $payment_status ){
        
       $payment_status = array_merge( $payment_status, array( 'Sage:Failed'
                                                            , 'Sage:REJECTED' 
                                                            , 'Sage:NOTAUTHED'
                                                            , 'Sage:MALFORMED'
                                                            , 'Sage:INVALID'
                                                            , 'Sage:ABORT'
                                                            , 'Sage:ERROR'
                                                            )  );
       
       return  $payment_status;        
    }    
    add_filter('wpbc_add_payment_status_error',    'wpbc_add_payment_status_error__sage');    
    
    
                
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Checking response from  payment system
    function wpbc_check_response_status__sage($response_status , $pay_system, $status, $booking_id, $wp_nonce) {
    
        if ($pay_system == 'sage') {
            
            if  ( isset($_REQUEST["crypt"] ) )   {
            
                $strCrypt=$_REQUEST["crypt"];
                $strEncryptionPassword =  get_bk_option( 'booking_sage_encryption_password' );
                
                //FixIn: 5.4.2
                $strDecoded = WPBC_SagepayUtil::decryptAes( $strCrypt, $strEncryptionPassword );                
                $values = WPBC_SagepayUtil::queryStringToArray($strDecoded);
                if ( !$strDecoded || empty($values) ) {
                    throw new WPBC_SagepayApiException('Invalid crypt input');
                }
                
                
                $status = 'Sage:' . $values['Status'];
            } else
                $status = 'Sage:Failed';
            
            return $status;
        } else 
            return $response_status;        
    }
    add_filter('wpbc_check_response_status',    'wpbc_check_response_status__sage', 10, 5 );
    
    
    function wpbc_auto_approve_or_cancell_and_redirect__sage($pay_system, $status, $booking_id) {
     
        if ($pay_system == 'sage') {
            
            $auto_approve = get_bk_option( 'booking_sage_is_auto_approve_cancell_booking'  );
            
            if (($status == 'OK') || ($status == 'Sage:OK') ) {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, true );
                wpdev_redirect( get_bk_option( 'booking_sage_order_successful' ) )   ;
                
            } else {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, false );
                wpdev_redirect( get_bk_option( 'booking_sage_order_failed' ) )   ;
            }
        }
    }    
    add_bk_action( 'wpbc_auto_approve_or_cancell_and_redirect', 'wpbc_auto_approve_or_cancell_and_redirect__sage');
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Activate
    function wpdev_bk_payment_activate_system_sage() {
        global $wpdb;

        // Sage Account /////////////////////////////////////////////////////////////////////////////////////////////
        add_bk_option( 'booking_sage_is_active', 'Off' );
        add_bk_option( 'booking_sage_subject', sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[bookingname]','[dates]'));
        add_bk_option( 'booking_sage_test', 'TEST' );                      //FixIn: 5.4.2   previous value:  'SIMULATOR' );
        add_bk_option( 'booking_sage_order_successful', '/successful' );
        add_bk_option( 'booking_sage_order_failed', '/failed' );
        add_bk_option( 'booking_sage_payment_button_title' , __('Pay via' ,'booking') .' Sage Pay');
        if ( wpdev_bk_is_this_demo() ) {
            add_bk_option( 'booking_sage_vendor_name', '' );                    //FixIn: 5.4.2   previous value:  'wpdevelop' );
            add_bk_option( 'booking_sage_encryption_password', '' );            //FixIn: 5.4.2   previous value:  'FfCDQjLiM524VtE7' );
            add_bk_option( 'booking_sage_curency', 'USD' );
            add_bk_option( 'booking_sage_transaction_type', 'PAYMENT' );
        } else {
            add_bk_option( 'booking_sage_vendor_name', '' );
            add_bk_option( 'booking_sage_encryption_password', '' );
            add_bk_option( 'booking_sage_curency', '' );
            add_bk_option( 'booking_sage_transaction_type', '' );
        }
        add_bk_option( 'booking_sage_is_description_show', 'Off' );
        add_bk_option( 'booking_sage_is_auto_approve_cancell_booking' , 'Off' );

    }
    add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_sage');


    // Activate
    function wpdev_bk_payment_deactivate_system_sage() {
        global $wpdb;
        // Sage account
        delete_bk_option( 'booking_sage_is_active' );
        delete_bk_option( 'booking_sage_subject' );
        delete_bk_option( 'booking_sage_test' );
        delete_bk_option( 'booking_sage_order_successful' );
        delete_bk_option( 'booking_sage_order_failed' );
        delete_bk_option( 'booking_sage_payment_button_title' );
        delete_bk_option( 'booking_sage_vendor_name' );
        delete_bk_option( 'booking_sage_encryption_password' );
        delete_bk_option( 'booking_sage_curency' );
        delete_bk_option( 'booking_sage_transaction_type' );
        delete_bk_option( 'booking_sage_is_description_show' );
        delete_bk_option( 'booking_sage_is_auto_approve_cancell_booking' );

    }
    add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_sage');

    
    define("WPBC_MASK_FOR_HIDDEN_FIELDS", "...");  

    
    /**
     * Common utilities shared by all Integration methods
     *
     * @category  Payment
     * @package   Sagepay

     * @copyright (c) 2013, Sage Pay Europe Ltd.
     */
    class WPBC_SagepayUtil
    {

        /**
         * The associated array containing card types and values
         *
         * @return array Array of card codes.
         */
        static protected $cardNames = array(
            'visa' => 'Visa',
            'visaelectron' => 'Visa Electron',
            'mastercard' => 'Mastercard',
            'amex' => 'American Express',
            'delta' => 'Delta',
            'dc' => 'Diners Club',
            'jcb' => 'JCB',
            'laser' => 'Laser',
            'maestro' => 'Maestro',
        );

        /**
         * The card types that SagePay supports.
         *
         * @return array Array of card codes.
         */
        static public function cardTypes()
        {
            return array_keys(self::$cardNames);
        }

        /**
         * Populate the card names in to a usable array.
         *
         * @param array $availableCards Available card codes.
         *
         * @return array Array of card codes and names.
         */
        static public function availableCards(array $availableCards)
        {
            $cardArr = array();

            // Filter input card types
            foreach ($availableCards as $code)
            {
                $code = strtolower($code);
                if ((array_key_exists($code, self::$cardNames)))
                {
                    $cardArr[$code] = self::$cardNames[$code];
                }
            }

            return $cardArr;
        }

        /**
         * PHP's mcrypt does not have built in PKCS5 Padding, so we use this.
         *
         * @param string $input The input string.
         *
         * @return string The string with padding.
         */
        static protected function addPKCS5Padding($input)
        {
            $blockSize = 16;
            $padd = "";

            // Pad input to an even block size boundary.
            $length = $blockSize - (strlen($input) % $blockSize);
            for ($i = 1; $i <= $length; $i++)
            {
                $padd .= chr($length);
            }

            return $input . $padd;
        }

        /**
         * Remove PKCS5 Padding from a string.
         *
         * @param string $input The decrypted string.
         *
         * @return string String without the padding.
         * @throws WPBC_SagepayApiException
         */
        static protected function removePKCS5Padding($input)
        {
            $blockSize = 16;
            $padChar = ord($input[strlen($input) - 1]);

            /* Check for PadChar is less then Block size */
            if ($padChar > $blockSize)
            {
                throw new WPBC_SagepayApiException('Invalid encryption string');
            }
            /* Check by padding by character mask */
            if (strspn($input, chr($padChar), strlen($input) - $padChar) != $padChar)
            {
                throw new WPBC_SagepayApiException('Invalid encryption string');
            }

            $unpadded = substr($input, 0, (-1) * $padChar);
            /* Chech result for printable characters */
            if (preg_match('/[[:^print:]]/', $unpadded))
            {
                throw new WPBC_SagepayApiException('Invalid encryption string');
            }
            return $unpadded;
        }

        /**
         * Encrypt a string ready to send to SagePay using encryption key.
         *
         * @param  string  $string  The unencrypyted string.
         * @param  string  $key     The encryption key.
         *
         * @return string The encrypted string.
         */
        static public function encryptAes($string, $key)
        {
            // AES encryption, CBC blocking with PKCS5 padding then HEX encoding.
            // Add PKCS5 padding to the text to be encypted.
            $string = self::addPKCS5Padding($string);

            // Perform encryption with PHP's MCRYPT module.
            $crypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $string, MCRYPT_MODE_CBC, $key);

            // Perform hex encoding and return.
            return "@" . strtoupper(bin2hex($crypt));
        }

        /**
         * Decode a returned string from SagePay.
         *
         * @param string $strIn         The encrypted String.
         * @param string $password      The encyption password used to encrypt the string.
         *
         * @return string The unecrypted string.
         * @throws WPBC_SagepayApiException
         */
        static public function decryptAes($strIn, $password)
        {
            // HEX decoding then AES decryption, CBC blocking with PKCS5 padding.
            // Use initialization vector (IV) set from $str_encryption_password.
            $strInitVector = $password;

            // Remove the first char which is @ to flag this is AES encrypted and HEX decoding.
            $hex = substr($strIn, 1);

            // Throw exception if string is malformed
            if (!preg_match('/^[0-9a-fA-F]+$/', $hex))
            {
                throw new WPBC_SagepayApiException('Invalid encryption string');
            }
            $strIn = pack('H*', $hex);

            // Perform decryption with PHP's MCRYPT module.
            $string = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $password, $strIn, MCRYPT_MODE_CBC, $strInitVector);
            return self::removePKCS5Padding($string);
        }

        /**
         * Convert a data array to a query string ready to post.
         *
         * @param  array   $data        The data array.
         * @param  string  $delimeter   Delimiter used in query string
         * @param  boolean $urlencoded  If true encode the final query string
         *
         * @return string The array as a string.
         */
        static public function arrayToQueryString(array $data, $delimiter = '&', $urlencoded = false)
        {
            $queryString = '';
            $delimiterLength = strlen($delimiter);

            // Parse each value pairs and concate to query string
            foreach ($data as $name => $value)
            {   
                // Apply urlencode if it is required
                if ($urlencoded)
                {
                    $value = urlencode($value);
                }
                $queryString .= $name . '=' . $value . $delimiter;
            }

            // remove the last delimiter
            return substr($queryString, 0, -1 * $delimiterLength);
        }

        static public function arrayToQueryStringRemovingSensitiveData(array $data,array $nonSensitiveDataKey, $delimiter = '&', $urlencoded = false)
        {
            $queryString = '';
            $delimiterLength = strlen($delimiter);

            // Parse each value pairs and concate to query string
            foreach ($data as $name => $value)
            {
               if (!in_array($name, $nonSensitiveDataKey)){
                                    $value=WPBC_MASK_FOR_HIDDEN_FIELDS;
                       }
                       else if ($urlencoded){
                                    $value = urlencode($value);
                       }
                    // Apply urlencode if it is required

               $queryString .= $name . '=' . $value . $delimiter;
            }

            // remove the last delimiter
            return substr($queryString, 0, -1 * $delimiterLength);
        }
        /**
         * Convert string to data array.
         *
         * @param string  $data       Query string
         * @param string  $delimeter  Delimiter used in query string
         *
         * @return array
         */
        static public function queryStringToArray($data, $delimeter = "&")
        {
            // Explode query by delimiter
            $pairs = explode($delimeter, $data);
            $queryArray = array();

            // Explode pairs by "="
            foreach ($pairs as $pair)
            {
                $keyValue = explode('=', $pair);

                // Use first value as key
                $key = array_shift($keyValue);

                // Implode others as value for $key
                $queryArray[$key] = implode('=', $keyValue);
            }
            return $queryArray;
        }

       static public function queryStringToArrayRemovingSensitiveData($data, $delimeter = "&", $nonSensitiveDataKey)
        {  
            // Explode query by delimiter
            $pairs = explode($delimeter, $data);
            $queryArray = array();

            // Explode pairs by "="
            foreach ($pairs as $pair)
            {
                $keyValue = explode('=', $pair);
                // Use first value as key
                $key = array_shift($keyValue);
                if (in_array($key, $nonSensitiveDataKey)){
                              $keyValue = explode('=', $pair);
                            }
                            else{
                              $keyValue = array(WPBC_MASK_FOR_HIDDEN_FIELDS);
                            }
                        // Implode others as value for $key
                            $queryArray[$key] = implode('=', $keyValue);

            }
            return $queryArray;
        }
        /**
         * Logging the debugging information to "debug.log"
         *
         * @param  string  $message
         * @return boolean
         */
        /*
        static public function log($message)
        {
            $settings = SagepaySettings::getInstance();
            if ($settings->getLogError())
            {
                $filename = SAGEPAY_SDK_PATH . '/debug.log';
                $line = '[' . date('Y-m-d H:i:s') . '] :: ' . $message;
                try
                {
                    $file = fopen($filename, 'a+');
                    fwrite($file, $line . PHP_EOL);
                    fclose($file);
                } catch (Exception $ex)
                {
                    return false;
                }
            }
            return true;
        }*/

        /**
         * Extract last 4 digits from card number;
         *
         * @param string $cardNr
         *
         * @return string
         */
        static public function getLast4Digits($cardNr)
        {
            // Apply RegExp to extract last 4 digits
            $matches = array();
            if (preg_match('/\d{4}$/', $cardNr, $matches))
            {
                return $matches[0];
            }
            return '';
        }

    }


    /**
     * SagepayApi exceptions type
     *
     * @category  Payment
     * @package   Sagepay
     * @copyright (c) 2013, Sage Pay Europe Ltd.
     */
    class WPBC_SagepayApiException extends Exception
    {

    }