<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 * 
 * Modified by Anton anseme@gmail.com from original file authorizenet.php
 * 01.07.2014
 * 
*/

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_payment_show_tab_in_top_settings_sermepa(){
        ?><a href="javascript:void(0)"
           onclick="javascript:
                   jQuery('.visibility_container').css('display','none');
                   jQuery('#visibility_container_sermepa').css('display','block');
                   jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                   jQuery(this).addClass('booking-submenu-tab-selected');"
               rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php
                                     if ( get_bk_option( 'booking_sermepa_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                                     original-title="<?php _e('Integration of Sermepa payment system' ,'booking');?>">
            <?php _e('Sermepa' ,'booking');?>
            <input type="checkbox"  <?php if ( get_bk_option( 'booking_sermepa_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
                   name="sermepa_is_active_dublicated" id="sermepa_is_active_dublicated"
                   onchange="document.getElementById('sermepa_is_active').checked=this.checked;" >
        </a>
        <script type="text/javascript">
            jQuery(document).ready( function(){
                recheck_active_itmes_in_top_menu('sermepa_is_active',   'sermepa_is_active_dublicated');
            });
        </script>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_sermepa');


    // Settings page for   S a g e
    function wpdev_bk_payment_show_settings_content_sermepa(){
            if ( isset( $_POST['sermepa_curency'] ) ) {
                  if (isset( $_POST['sermepa_is_active'] ))     $sermepa_is_active = 'On';
                  else                                       $sermepa_is_active = 'Off';
                  update_bk_option( 'booking_sermepa_is_active', $sermepa_is_active );
                  update_bk_option( 'booking_sermepa_subject', $_POST['sermepa_subject'] );
                  
                  update_bk_option( 'booking_sermepa_order_successful', wpbc_make_link_relative( $_POST['sermepa_order_successful'] ) );
                  update_bk_option( 'booking_sermepa_order_failed', wpbc_make_link_relative( $_POST['sermepa_order_failed'] ) );
                  
                  if ( ! wpdev_bk_is_this_demo() ) {
                    update_bk_option( 'booking_sermepa_test', $_POST['sermepa_test'] );  
                    update_bk_option( 'booking_sermepa_vendor_name', $_POST['sermepa_vendor_name'] );
                    update_bk_option( 'booking_sermepa_encryption_password', $_POST['sermepa_encryption_password'] );
                  }
                  update_bk_option( 'booking_sermepa_curency', $_POST['sermepa_curency'] );
                  update_bk_option( 'booking_sermepa_transaction_type', $_POST['sermepa_transaction_type'] );
                  update_bk_option( 'booking_sermepa_payment_button_title', $_POST['sermepa_payment_button_title'] );
                 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 if (isset( $_POST['sermepa_is_description_show'] ))     $sermepa_is_description_show = 'On';
                 else                                                   $sermepa_is_description_show = 'Off';
                 update_bk_option( 'booking_sermepa_is_description_show' , $sermepa_is_description_show );

                 if (isset( $_POST['sermepa_is_auto_approve_cancell_booking'] ))     $sermepa_is_auto_approve_cancell_booking = 'On';
                 else                                                   $sermepa_is_auto_approve_cancell_booking = 'Off';
                 update_bk_option( 'booking_sermepa_is_auto_approve_cancell_booking' , $sermepa_is_auto_approve_cancell_booking );

            }

            $sermepa_is_active         =  get_bk_option( 'booking_sermepa_is_active' );
            $sermepa_subject           =  get_bk_option( 'booking_sermepa_subject' );
            $sermepa_test              =  get_bk_option( 'booking_sermepa_test' );
            $sermepa_order_successful  =  get_bk_option( 'booking_sermepa_order_successful' );
            $sermepa_order_failed      =  get_bk_option( 'booking_sermepa_order_failed' );
            $sermepa_vendor_name       =  get_bk_option( 'booking_sermepa_vendor_name' );
            $sermepa_encryption_password =  get_bk_option( 'booking_sermepa_encryption_password' );
            $sermepa_curency           =  get_bk_option( 'booking_sermepa_curency' );
            $sermepa_transaction_type  =  get_bk_option( 'booking_sermepa_transaction_type' );
            $sermepa_payment_button_title=  get_bk_option( 'booking_sermepa_payment_button_title' );
            $sermepa_is_description_show = get_bk_option( 'booking_sermepa_is_description_show' );
            $sermepa_is_auto_approve_cancell_booking  = get_bk_option( 'booking_sermepa_is_auto_approve_cancell_booking' );
            ?>
            <div id="visibility_container_sermepa" class="visibility_container" style="display:none;">
                    <div class='meta-box'>
                      <div <?php $my_close_open_win_id = 'bk_settings_costs_sermepa_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                            <h3 class='hndle'><span><?php _e('Sermepa payment customization' ,'booking'); ?></span></h3> <div class="inside">
                        <!--form  name="post_option_sermepa" action="" method="post" id="post_option_sermepa" -->
                            <div class="wpbc-success-messermepa"><?php printf(__('If you have no account on this system, please visit %s to create one. Simulator account emulates the Sermepa Pay account as well as a Test and Live account.' ,'booking'), '<a href="https://support.sermepapay.com/apply/RequestSimAccount.aspx"  target="_blank">sermepapay.com</a>'); ?></div>
                            <table class="form-table">
                                <tbody>
                                    <tr valign="top">
                                        <th scope="row"><?php _e('Active Sermepa Pay' ,'booking'); ?>:</th>
                                        <td>
                                            <fieldset>
                                                <label for="sermepa_is_active" >
                                                    <input <?php if ($sermepa_is_active == 'On') echo "checked"; ?>  value="<?php echo $sermepa_is_active; ?>" name="sermepa_is_active" id="sermepa_is_active" type="checkbox"
                                                           onchange="document.getElementById('sermepa_is_active_dublicated').checked=this.checked;"
                                                                                                                  />
                                                    <?php _e(' Check this box to use Sermepa Pay payment.' ,'booking');?>
                                                </label>
                                            </fieldset>                                            
                                        </td>
                                    </tr>


                                    <tr class="wpdevbk">
                                      <th class="well" style="padding:10px;">
                                        <label for="sermepa_vendor_name" ><?php _e('FUC' ,'booking'); ?>:</label>
                                      </th>
                                      <td class="well" style="padding:10px;">
                                          <input value="<?php echo $sermepa_vendor_name; ?>" name="sermepa_vendor_name" id="sermepa_vendor_name" class="regular-text code" type="text" size="45" />
                                          <p class="description"><?php printf(__('Set this value to the FUC assigned to you by Sermepa Pay or chosen when you applied.' ,'booking'),'<b>','</b>');?></p>
                                          <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-messermepa" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                      </td>
                                    </tr>

                                    <tr class="wpdevbk">
                                      <th class="well" style="padding:10px;">
                                        <label for="sermepa_encryption_password" ><?php _e('Clave' ,'booking'); ?>:</label>
                                      </th>
                                      <td class="well" style="padding:10px;">
                                          <input value="<?php echo $sermepa_encryption_password; ?>" name="sermepa_encryption_password" id="sermepa_encryption_password" class="regular-text code" type="text" size="45" />
                                          <p class="description"><?php printf(__('Set this value to the Clave assigned to you by Sermepa Pay' ,'booking'),'<b>','</b>');?></p>
                                          <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-messermepa" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                      </td>
                                    </tr>
                                    
                                    <tr valign="top"><td style="padding:10px 0px; " colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                                    
                                    
                                    <tr valign="top">
                                      <th scope="row"><label for="sermepa_test" ><?php _e('Chose payment mode' ,'booking'); ?>:</label></th>
                                      <td>
                                         <select id="sermepa_test" name="sermepa_test">
                                            
                                            <option <?php if($sermepa_test == 'TEST') echo "selected"; ?> value="TEST"><?php _e('TEST' ,'booking'); ?></option>
                                            <option <?php if($sermepa_test == 'LIVE') echo "selected"; ?> value="LIVE"><?php _e('LIVE' ,'booking'); ?></option>
                                         </select>
                                         <span class="description"><?php printf(__('Select TEST for the Test Server and LIVE in the live environment' ,'booking'),'<b>','</b>');?></span>
                                         <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-messermepa" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                      </td>
                                    </tr>
                                    
                                    <tr valign="top">
                                      <th scope="row"><label for="sermepa_transaction_type" ><?php _e('Transaction type' ,'booking'); ?>:</label></th>
                                      <td>
                                         <select id="sermepa_transaction_type" name="sermepa_transaction_type">
                                            <option <?php if($sermepa_transaction_type == 'AUTHORIZE') echo "selected"; ?> value="AUTHORIZE"><?php _e('Authorization' ,'booking'); ?></option>
                                            <option <?php if($sermepa_transaction_type == 'DEFERRED') echo "selected"; ?> value="DEFERRED"><?php _e('DEFERRED' ,'booking'); ?></option>
                                            <option <?php if($sermepa_transaction_type == 'AUTHENTICATE') echo "selected"; ?> value="AUTHENTICATE"><?php _e('AUTHENTICATE' ,'booking'); ?></option>
                                         </select>
                                         <span class="description"><?php printf(__('This can be DEFERRED or AUTHENTICATED if your Sermepa Pay account supports those payment types' ,'booking'),'<b>','</b>');?></span>
                                      </td>
                                    </tr>
                                    
                                    <tr valign="top">
                                      <th scope="row"><label for="sermepa_curency" ><?php _e('Accepted Currency' ,'booking'); ?>:</label></th>
                                      <td>
                                         <select id="sermepa_curency" name="sermepa_curency">
                                            <option <?php if($sermepa_curency == 'USD') echo "selected"; ?> value="USD"><?php _e('U.S. Dollars' ,'booking'); ?></option>
                                            <option <?php if($sermepa_curency == 'EUR') echo "selected"; ?> value="EUR"><?php _e('Euros' ,'booking'); ?></option>
                                         </select>
                                         <span class="description"><?php printf(__('The currency code that gateway will process the payment in.' ,'booking'),'<b>','</b>');?></span>
                                      </td>
                                    </tr>
                                    
                                    <tr>
                                      <th><label for="sermepa_payment_button_title" ><?php _e('Payment button title' ,'booking'); ?>:</label></th>
                                      <td>
                                          <input value="<?php echo $sermepa_payment_button_title; ?>" name="sermepa_payment_button_title" id="sermepa_payment_button_title" class="regular-text code" type="text" size="45" />
                                          <p class="description"><?php printf(__('Enter the title of the payment button' ,'booking'),'Sermepa');?></p>
                                      </td>
                                    </tr>
                                    
                                    

                                <tr valign="top">
                                  <th scope="row" ><?php _e('Show Payment description' ,'booking'); ?>:</th>
                                  <td>
                                    <fieldset>
                                        <label for="sermepa_is_description_show">
                                            <input name="sermepa_is_description_show" id="sermepa_is_description_show" type="checkbox" 
                                                <?php if ($sermepa_is_description_show == 'On') echo "checked";/**/ ?>  
                                                value="<?php echo $sermepa_is_description_show; ?>" 
                                                onclick="javascript: if (this.checked) jQuery('#togle_settings_sermepa_subject').slideDown('normal'); else  jQuery('#togle_settings_sermepa_subject').slideUp('normal');"
                                             /><?php _e('Check this box to show payment description in payment form' ,'booking');?>
                                        </label>
                                    </fieldset>
                                  </td>
                                </tr>
                                

                                <tr valign="top"><td colspan="2" style="padding:0px;">
                                    <div style="margin: 0px 0 10px 50px;">    
                                    <table id="togle_settings_sermepa_subject" style="width:100%;<?php if ($sermepa_is_description_show != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                        <tr>
                                        <th scope="row"><label for="sermepa_subject" ><?php _e('Payment description' ,'booking'); ?>:</label></th>
                                            <td>
                                                <textarea  id="sermepa_subject" name="sermepa_subject" class="regular-text code" ><?php echo $sermepa_subject; ?></textarea>
                                                <p class="description"><?php printf(__('Enter the service name or the reason for the payment here.' ,'booking'),'<br/>','</b>');?></p>
                                            </td>
                                        </tr>
                                        <tr><th></th>
                                            <td >                         
                                                <div  class="wpbc-help-messermepa"  style="margin-top:-10px;">
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
                                  <th><label for="sermepa_order_successful" ><?php _e('Return URL after Successful order' ,'booking'); ?>:</label></th>
                                  <td class="wpbc-settings-data-field0">  
                                      <fieldset>
                                        <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $sermepa_order_successful; ?>" name="sermepa_order_successful" id="sermepa_order_successful" class="regular-text code" type="text" size="45" />
                                      </fieldset>                                        
                                      <p class="description"><?php printf(__('Enter a return relative Successful URL. %s will redirect visitors to this page after Successful Payment' ,'booking'),'Sermepa Pay');?><br/>
                                       <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo  get_option('siteurl') . $sermepa_order_successful; ?>" target="_blank"><?php echo  get_option('siteurl') . $sermepa_order_successful; ?></a></p>
                                  </td>
                                </tr>

                                <tr    >
                                  <th><label for="sermepa_order_failed" ><?php _e('Return URL after Failed order' ,'booking'); ?>:</label></th>
                                  <td class="wpbc-settings-data-field0">   
                                      <fieldset>
                                        <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $sermepa_order_failed; ?>" name="sermepa_order_failed" id="sermepa_order_failed" class="regular-text code" type="text" size="45" />
                                      </fieldset>                                        
                                      <p class="description"><?php printf(__('Enter a return relative Failed URL. %s will redirect visitors to this page after Failed Payment' ,'booking'),'Sermepa Pay');?><br/>
                                       <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo   get_option('siteurl') . $sermepa_order_failed; ?>" target="_blank"><?php  echo get_option('siteurl') . $sermepa_order_failed; ?></a></p>
                                  </td>
                                </tr>

                                <tr>
                                  <th><?php _e('Automatically approve/cancel booking' ,'booking'); ?>:</th>
                                  <td class="wpbc-settings-data-field0">  
                                      <fieldset>
                                      <label for="sermepa_is_auto_approve_cancell_booking" class="description">   
                                        <input name="sermepa_is_auto_approve_cancell_booking" id="sermepa_is_auto_approve_cancell_booking" type="checkbox"
                                            <?php if ($sermepa_is_auto_approve_cancell_booking == 'On') echo "checked";/**/ ?>  
                                            value="<?php echo $sermepa_is_auto_approve_cancell_booking; ?>" 
                                             />
                                        <?php _e('Check this box to automatically approve bookings when visitor makes a successful payment, or automatically cancel the booking when visitor makes a payment cancellation.' ,'booking');?>
                                      </label>
                                      <p class="wpbc-info-messermepa" style="text-align:left;"><strong><?php _e('Warning' ,'booking');?>!</strong> <?php _e('This will not work, if the visitor leaves the payment page.' ,'booking');?><p>    
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

                                $strConnectTo="TEST";                                  //Set to SIMULATOR for the Simulator expert system, TEST for the Test Server and LIVE in the live environment
                                $orderSuccessful = 'good.php';
                                $orderFailed = 'bad.php';
                                $strYourSiteFQDN= site_url() . "/";              //"http://wp/";  // IMPORTANT.  Set the strYourSiteFQDN value to the Fully Qualified Domain Name of your server. **** This should start http:// or https:// and should be the name by which our servers can call back to yours **** i.e. it MUST be resolvable externally, and have access granted to the Sermepa Pay servers **** examples would be https://www.mysite.com or http://212.111.32.22/ **** NOTE: You should leave the final / in place.
                                //TODO: Define from settings page
                                $strVendorName="";                                 // Set this value to the Vendor Name assigned to you by Sermepa Pay or chosen when you applied **/
                                $strEncryptionPassword="";                  // Set this value to the XOR Encryption password assigned to you by Sermepa Pay **/
                                $strCurrency="USD";                                         // Set this to indicate the currency in which you wish to trade. You will need a merchant number in this currency **/
                                $strTransactionType="AUTHORIZE";                              // This can be DEFERRED or AUTHENTICATED if your Sermepa Pay account supports those payment types **/
                                $strPartnerID="";                                           // Optional setting. If you are a Sermepa Pay Partner and wish to flag the transactions with your unique partner id set it here. **/
                                $bSendEMail=0;                                              // Optional setting. ** 0 = Do not send either customer or vendor e-mails, ** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). ** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided.
                                $strVendorEMail="";                                         // Optional setting. Set this to the mail address which will receive order confirmations and failures
                                $strProtocol="2.23";

                                ?>

                                </tbody>
                            </table>
                            
                            <div class="wpbc-error-messermepa" style="text-align:left;">                            
                                <strong><?php printf(__('Important!' ,'booking') );?></strong><br/>
                                <?php printf(__('Please configure %s fields inside the %sBilling form fields%s TAB at this page, this is necessary for the %s.' ,'booking'),'<b>' .__('ALL' ,'booking') . '</b>', '<b>"', '"</b>','Sermepa Pay' ); ?>
                            </div>   

                            <div class="clear" style="height:10px;"></div>
                            <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="sermepasubmit"/>
                            <div class="clear" style="height:10px;"></div>

                        <!--/form-->
                   </div> </div> </div>
            </div>
              <?php
    }
    
    add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_sermepa');
   



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_define_payment_form_sermepa($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ){
        $output = '';
        if( (get_bk_option( 'booking_sermepa_is_active' ) == 'On')   ) {
                // Need to set status of bookings
                // Pending, Aproved, Payed

                $form_fields = get_form_content ($bkform, $booking_type, '', array('booking_id'=> $booking_id ,
                                                                              'resource_title'=> $bk_title ) );
                $form_fields = $form_fields['_all_'];

                $sermepa_is_active         =  get_bk_option( 'booking_sermepa_is_active' );
                if ($sermepa_is_active != 'On')  return '';
            
                $sermepa_subject           =  get_bk_option( 'booking_sermepa_subject' );
                /*
                $sermepa_subject           =  apply_bk_filter('wpdev_check_for_active_language', $sermepa_subject );
                $sermepa_subject = str_replace('[bookingname]',$bk_title[0]->title,$sermepa_subject);
                $sermepa_subject = str_replace('[dates]',$booking_days_count,$sermepa_subject); //$paypal_subject .= ' Booking type: ' . $bk_title[0]->title . '. For period: ' . $booking_days_count;
                    $my_d_c = explode(',', $booking_days_count);
                    $my_d_c = count($my_d_c);
                    $sermepa_subject = str_replace('[datescount]',$my_d_c,$sermepa_subject);
*/
                $subject_payment = $sermepa_subject;
                


                $sermepa_test              =  get_bk_option( 'booking_sermepa_test' );
                $sermepa_order_successful  =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=sermepa&stats=OK' ;   //get_bk_option( 'booking_sermepa_order_successful' );
                $sermepa_order_failed      =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=sermepa&stats=FAILED' ;   //get_bk_option( 'booking_sermepa_order_failed' );
                //$sermepa_order_successful  = site_url(). get_bk_option( 'booking_sermepa_order_successful' );
                //$sermepa_order_failed  =  site_url(). get_bk_option( 'booking_sermepa_order_failed ' );
                $sermepa_vendor_name       =  get_bk_option( 'booking_sermepa_vendor_name' );
                $sermepa_encryption_password =  get_bk_option( 'booking_sermepa_encryption_password' );
                $sermepa_curency           =  get_bk_option( 'booking_sermepa_curency' );
                $sermepa_transaction_type  =  get_bk_option( 'booking_sermepa_transaction_type' );
                $sermepa_payment_button_title  =  get_bk_option( 'booking_sermepa_payment_button_title' );
                $sermepa_payment_button_title  =  apply_bk_filter('wpdev_check_for_active_language', $sermepa_payment_button_title );

//                if ( empty( $sermepa_test ) ) return '';
//                if ( empty( $sermepa_order_successful ) ) return '';
//                if ( empty( $sermepa_order_failed ) ) return '';
//                if ( empty( $sermepa_vendor_name ) ) return '';
//                if ( empty( $sermepa_encryption_password ) ) return '';
//                if ( empty( $sermepa_curency ) ) return '';
//                if ( empty( $sermepa_transaction_type ) ) return '';

                // Get all fields for biling info
                $sermepa_billing_customer_email  = (string) trim(get_bk_option( 'booking_billing_customer_email' ) . $booking_type );
                $sermepa_billing_firstnames      = (string) trim( get_bk_option( 'booking_billing_firstnames' ) . $booking_type );
                $sermepa_billing_surname         = (string) trim( get_bk_option( 'booking_billing_surname' ) . $booking_type );
                $sermepa_billing_address1        = (string) trim( get_bk_option( 'booking_billing_address1' ) . $booking_type) ;
                $sermepa_billing_city            = (string) trim( get_bk_option( 'booking_billing_city' ) . $booking_type );
                $sermepa_billing_country         = (string) trim( get_bk_option( 'booking_billing_country' ) . $booking_type );
                $sermepa_billing_post_code       = (string) trim( get_bk_option( 'booking_billing_post_code' ) . $booking_type );
                $sermepa_billing_state           = (string) trim( get_bk_option( 'booking_billing_state' ) . $booking_type );

                // Check if all fields set, if no so then return empty
//                if ( isset($form_fields[$sermepa_billing_customer_email]) === false ) return '';
//                if ( isset($form_fields[$sermepa_billing_firstnames]) === false ) return '';
//                if ( isset($form_fields[$sermepa_billing_surname]) === false ) return '';
//                if ( isset($form_fields[$sermepa_billing_address1]) === false ) return '';
//                if ( isset($form_fields[$sermepa_billing_city]) === false ) return '';
//                if ( isset($form_fields[$sermepa_billing_country]) === false ) return '';
//                if ( isset($form_fields[$sermepa_billing_post_code]) === false ) return '';


                    $strConnectTo=$sermepa_test;                                   //Set to SIMULATOR for the Simulator expert system, TEST for the Test Server and LIVE in the live environment
                    $orderSuccessful = $sermepa_order_successful;
                    $orderFailed     = $sermepa_order_failed ;
                    $strYourSiteFQDN= site_url() . "/";              //"http://wp/";  // IMPORTANT.  Set the strYourSiteFQDN value to the Fully Qualified Domain Name of your server. **** This should start http:// or https:// and should be the name by which our servers can call back to yours **** i.e. it MUST be resolvable externally, and have access granted to the Sermepa Pay servers **** examples would be https://www.mysite.com or http://212.111.32.22/ **** NOTE: You should leave the final / in place.


                    $strVendorName=$sermepa_vendor_name;                           // Set this value to the Vendor Name assigned to you by Sermepa Pay or chosen when you applied **/
                    $strEncryptionPassword=$sermepa_encryption_password;           // Set this value to the XOR Encryption password assigned to you by Sermepa Pay **/
                    $strCurrency=$sermepa_curency;                                 // Set this to indicate the currency in which you wish to trade. You will need a merchant number in this currency **/
                    $strTransactionType=$sermepa_transaction_type;                 // This can be DEFERRED or AUTHENTICATED if your Sermepa Pay account supports those payment types **/
                    $strPartnerID="";                                           // Optional setting. If you are a Sermepa Pay Partner and wish to flag the transactions with your unique partner id set it here. **/
                    $bSendEMail=0;                                              // Optional setting. ** 0 = Do not send either customer or vendor e-mails, ** 1 = Send customer and vendor e-mails if address(es) are provided(DEFAULT). ** 2 = Send Vendor Email but not Customer Email. If you do not supply this field, 1 is assumed and e-mails are sent if addresses are provided.
                    $strVendorEMail="";                                         // Optional setting. Set this to the mail address which will receive order confirmations and failures
                    $strProtocol="2.23";
/**********************************MY STUFFFFFFFFF *************************************************************/
                    if ($strConnectTo=="LIVE")      $strPurchaseURL="https://sis-t.sermepa.es/sis/realizarPago";
                    elseif ($strConnectTo=="TEST")  $strPurchaseURL="https://sis-t.sermepa.es:25443/sis/realizarPago";
                    else $strPurchaseURL="https://sis-t.sermepa.es:25443/sis/realizarPago";
                    
                    if ($sermepa_transaction_type=="AUTHORIZE") 
                        $Ds_Merchant_TransactionType="0";
                    elseif ($sermepa_transaction_type=="DEFERRED") 
                        $Ds_Merchant_TransactionType="1"; //1
                    elseif ($sermepa_transaction_type=="AUTHENTICATE") 
                        $Ds_Merchant_TransactionType="O"; // O
                    if ($sermepa_curency=="USD") 
                        $Ds_Merchant_Currency="840";
                    elseif ($sermepa_curency=="EUR") 
                        $Ds_Merchant_Currency="978";
                    if ($sermepa_is_description_show == 'On')
                        $Ds_Merchant_ProductDescription = $subject_payment;                    
                    else 
                        $Ds_Merchant_ProductDescription = 'Bookingid: '.$booking_id;
                    //$Ds_Merchant_Titular = get_bk_option( 'booking_sermepa_titular' );
                    $Firstnames  = $form_fields[$sermepa_billing_firstnames];
                    $Surname     = $form_fields[$sermepa_billing_surname];
                    $Ds_Merchant_Titular = $Firstnames.' '.$Surname;
                    if ($strConnectTo=="TEST") 
                        $Ds_Merchant_MerchantName = 'Pruebas';
                    else 
                        $Ds_Merchant_MerchantName = 'moreryadom.net';
                    $Ds_Merchant_Terminal = "1";
                    $Ds_Merchant_MerchantURL = site_url();
                    $Ds_Merchant_Amount = $summ*100;
                    $Ds_Merchant_MerchantCode = $sermepa_vendor_name;

$message = $Ds_Merchant_Amount.$booking_id.$Ds_Merchant_MerchantCode.$Ds_Merchant_Currency.$Ds_Merchant_TransactionType.$Ds_Merchant_MerchantURL.$sermepa_encryption_password;
$signature = strtoupper(sha1($message));

$output = '<div style="width:100%;clear:both;margin-top:20px;"></div>';
$output .= '<div class="sermepa_div wpbc-payment-form" style="text-align:left;clear:both;">';
$output .= '<form action=\"'.$strPurchaseURL.'\" method=\"POST\" id=\"SermepaPayForm\" name=\"SermepaPayForm\" style=\"text-align:left;\" class=\"booking_SermepaPayForm\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_Amount\" value=\"'.$Ds_Merchant_Amount.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_Currency\" value=\"'.$Ds_Merchant_Currency.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_Order\" value=\"'.$booking_id.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_ProductDescription\" value=\"'.$Ds_Merchant_ProductDescription.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_Titular\" value=\"'.$Ds_Merchant_Titular.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_MerchantCode\" value=\"'.$Ds_Merchant_MerchantCode.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_Terminal\" value=\"'.$Ds_Merchant_Terminal.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_TransactionType\" value=\"'.$Ds_Merchant_TransactionType.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_MerchantURL\" value=\"'.$Ds_Merchant_MerchantURL.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_UrlOK\" value=\"'.$sermepa_order_successful.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_UrlKO\" value=\"'.$sermepa_order_failed.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_MerchantSignature\" value=\"'.$signature.'\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_ConsumerLanguage\" value="002\">';
$output .= '<input type=\"hidden\" name=\"Ds_Merchant_MerchantName\" value=\"'.$Ds_Merchant_MerchantName.'\">';
$output .= '<input type=\"submit\" name=\"submitsermepabutton\" value=\"'.$sermepa_payment_button_title.'\" class=\"btn\">';
$output .= '</form></div>';
        }
        return $output;

    }
    add_bk_filter('wpdev_bk_define_payment_form_sermepa', 'wpdev_bk_define_payment_form_sermepa');


                
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                
    // OK            
    function wpbc_add_payment_status_ok__sermepa( $payment_status ){
       $payment_status = array_merge( $payment_status, 
                                        array(
                                            'Sermepa:OK'
                                           ) 
                            );        
        return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_ok',  'wpbc_add_payment_status_ok__sermepa');
    
    // Pending
    function wpbc_add_payment_status_pending__sermepa( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_pending',  'wpbc_add_payment_status_pending__sermepa');
    
    // Unknown
    function wpbc_add_payment_status_unknown__sermepa( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_unknown',  'wpbc_add_payment_status_unknown__sermepa');
    
    // Error
    function wpbc_add_payment_status_error__sermepa( $payment_status ){
        
       $payment_status = array_merge( $payment_status, array( 'Sermepa:Failed'
                                                            , 'Sermepa:REJECTED' 
                                                            , 'Sermepa:NOTAUTHED'
                                                            , 'Sermepa:MALFORMED'
                                                            , 'Sermepa:INVALID'
                                                            , 'Sermepa:ABORT'
                                                            , 'Sermepa:ERROR'
                                                            )  );
       
       return  $payment_status;        
    }    
    add_filter('wpbc_add_payment_status_error',    'wpbc_add_payment_status_error__sermepa');    
    
    
                
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Checking response from  payment system
    function wpbc_check_response_status__sermepa($response_status , $pay_system, $status, $booking_id, $wp_nonce) {
        return $status;
        if ($pay_system == 'sermepa') {
            return $status;
            if  ( isset($_REQUEST["crypt"] ) )   {
            
                $strCrypt=$_REQUEST["crypt"];
                $strEncryptionPassword =  get_bk_option( 'booking_sermepa_encryption_password' );
                $strDecoded=wpdev_simpleXor1(wpdev_Base64Decode($strCrypt),$strEncryptionPassword);
                $values = wpdev_getToken1($strDecoded);
                $status = 'Sermepa:' . $values['Status'];
                // debuge($values, $booking_id, $status, $pay_system, get_bk_option( 'booking_sermepa_order_successful' ), get_bk_option( 'booking_sermepa_order_failed' ));
            } else
                $status = 'Sermepa:Failed';

            return $status;
        } else 
            return $response_status;        
    }
    add_filter('wpbc_check_response_status',    'wpbc_check_response_status__sermepa', 10, 5 );
    
    
    function wpbc_auto_approve_or_cancell_and_redirect__sermepa($pay_system, $status, $booking_id) {
     
        if ($pay_system == 'sermepa') {
            
            $auto_approve = get_bk_option( 'booking_sermepa_is_auto_approve_cancell_booking'  );
            
            if (($status == 'OK') || ($status == 'Sermepa:OK') ) {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, true );
                wpdev_redirect( get_bk_option( 'booking_sermepa_order_successful' ) )   ;
                
            } else {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, false );
                wpdev_redirect( get_bk_option( 'booking_sermepa_order_failed' ) )   ;
            }
        }
    }    
    add_bk_action( 'wpbc_auto_approve_or_cancell_and_redirect', 'wpbc_auto_approve_or_cancell_and_redirect__sermepa');
    
    
    
        //////////////////////////////////////////////////////////////////////////////////////////////
        //  S u p p o r t   F u n c t i o n s      ///////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////////
/*
        function wpdev_getToken1($thisString) {

        // List the possible tokens
        $Tokens = array(
        "Status",
        "StatusDetail",
        "VendorTxCode",
        "VPSTxId",
        "TxAuthNo",
        "Amount",
        "AVSCV2",
        "AddressResult",
        "PostCodeResult",
        "CV2Result",
        "GiftAid",
        "3DSecureStatus",
        "CAVV",
            "AddressStatus",
            "CardType",
            "Last4Digits",
            "PayerStatus","CardType");



        // Initialise arrays
        $output = array();
        $resultArray = array();

        // Get the next token in the sequence
        for ($i = count($Tokens)-1; $i >= 0 ; $i--){
        // Find the position in the string
        $start = strpos($thisString, $Tokens[$i]);
            // If it's present
        if ($start !== false){
          // Record position and token name
          $resultArray[$i]->start = $start;
          $resultArray[$i]->token = $Tokens[$i];
        }
        }

        // Sort in order of position
        sort($resultArray);
            // Go through the result array, getting the token values
        for ($i = 0; $i<count($resultArray); $i++){
        // Get the start point of the value
        $valueStart = $resultArray[$i]->start + strlen($resultArray[$i]->token) + 1;
            // Get the length of the value
        if ($i==(count($resultArray)-1)) {
          $output[$resultArray[$i]->token] = substr($thisString, $valueStart);
        } else {
          $valueLength = $resultArray[$i+1]->start - $resultArray[$i]->start - strlen($resultArray[$i]->token) - 2;
              $output[$resultArray[$i]->token] = substr($thisString, $valueStart, $valueLength);
        }

        }

        // Return the ouput array
        return $output;
        }

        function wpdev_base64Decode1($scrambled) {
          // Initialise output variable
          $output = "";

          // Fix plus to space conversion issue
          $scrambled = str_replace(" ","+",$scrambled);

          // Do encoding
          $output = base64_decode($scrambled);

          // Return the result
          return $output;
        }

        //  The SimpleXor encryption algorithm **  NOTE: This is a placeholder really.  Future releases of Form will use AES or TwoFish.  Proper encryption **  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering **  It won't stop a half decent hacker though, but the most they could do is change the amount field to something **  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still **  more secure than the other PSPs who don't both encrypting their forms at all
        function wpdev_simpleXor1($InString, $Key) {
          // Initialise key array
          $KeyList = array();
          // Initialise out variable
          $output = "";

          // Convert $Key into array of ASCII values
          for($i = 0; $i < strlen($Key); $i++){
            $KeyList[$i] = ord(substr($Key, $i, 1));
          }

          // Step through string a character at a time
          for($i = 0; $i < strlen($InString); $i++) {
            // Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
            // % is MOD (modulus), ^ is XOR
            $output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
          }

          // Return the result
          return $output;
        }    

        // Base 64 Encoding function ** PHP does it natively but just for consistency and ease of maintenance, let's declare our own function
        function wpdev_bk_sermepa_base64Encode1($plain) {
          // Initialise output variable
          $output = "";

          // Do encoding
          $output = base64_encode($plain);

          // Return the result
          return $output;
        }

        //  The SimpleXor encryption algorithm **  NOTE: This is a placeholder really.  Future releases of Form will use AES or TwoFish.  Proper encryption **  This simple function and the Base64 will deter script kiddies and prevent the "View Source" type tampering **  It won't stop a half decent hacker though, but the most they could do is change the amount field to something **  else, so provided the vendor checks the reports and compares amounts, there is no harm done.  It's still **  more secure than the other PSPs who don't both encrypting their forms at all
        function wpdev_sermepa_simpleXor1($InString, $Key) {
          // Initialise key array
          $KeyList = array();
          // Initialise out variable
          $output = "";

          // Convert $Key into array of ASCII values
          for($i = 0; $i < strlen($Key); $i++){
            $KeyList[$i] = ord(substr($Key, $i, 1));
          }

          // Step through string a character at a time
          for($i = 0; $i < strlen($InString); $i++) {
            // Get ASCII code from string, get ASCII code from key (loop through with MOD), XOR the two, get the character from the result
            // % is MOD (modulus), ^ is XOR
            $output.= chr(ord(substr($InString, $i, 1)) ^ ($KeyList[$i % strlen($Key)]));
          }

          // Return the result
          return $output;
        }

     */
        
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Activate
    function wpdev_bk_payment_activate_system_sermepa() {
        global $wpdb;

        // Sermepa Account /////////////////////////////////////////////////////////////////////////////////////////////
        add_bk_option( 'booking_sermepa_is_active', 'Off' );
        add_bk_option( 'booking_sermepa_subject', sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[bookingname]','[dates]'));
        add_bk_option( 'booking_sermepa_test', 'SIMULATOR' );
        add_bk_option( 'booking_sermepa_order_successful', '/successful' );
        add_bk_option( 'booking_sermepa_order_failed', '/failed' );
        add_bk_option( 'booking_sermepa_payment_button_title' , __('Pay via' ,'booking') .' Sermepa Pay');
        if ( wpdev_bk_is_this_demo() ) {
            add_bk_option( 'booking_sermepa_vendor_name', 'wpdevelop' );
            add_bk_option( 'booking_sermepa_encryption_password', 'FfCDQjLiM524VtE7' );
            add_bk_option( 'booking_sermepa_curency', 'USD' );
            add_bk_option( 'booking_sermepa_transaction_type', 'PAYMENT' );
        } else {
            add_bk_option( 'booking_sermepa_vendor_name', '' );
            add_bk_option( 'booking_sermepa_encryption_password', '' );
            add_bk_option( 'booking_sermepa_curency', '' );
            add_bk_option( 'booking_sermepa_transaction_type', '' );
        }
        add_bk_option( 'booking_sermepa_is_description_show', 'Off' );
        add_bk_option( 'booking_sermepa_is_auto_approve_cancell_booking' , 'Off' );

    }
    add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_sermepa');


    // Activate
    function wpdev_bk_payment_deactivate_system_sermepa() {
        global $wpdb;
        // Sermepa account
        delete_bk_option( 'booking_sermepa_is_active' );
        delete_bk_option( 'booking_sermepa_subject' );
        delete_bk_option( 'booking_sermepa_test' );
        delete_bk_option( 'booking_sermepa_order_successful' );
        delete_bk_option( 'booking_sermepa_order_failed' );
        delete_bk_option( 'booking_sermepa_payment_button_title' );
        delete_bk_option( 'booking_sermepa_vendor_name' );
        delete_bk_option( 'booking_sermepa_encryption_password' );
        delete_bk_option( 'booking_sermepa_curency' );
        delete_bk_option( 'booking_sermepa_transaction_type' );
        delete_bk_option( 'booking_sermepa_is_description_show' );
        delete_bk_option( 'booking_sermepa_is_auto_approve_cancell_booking' );

    }
    add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_sermepa');
?>