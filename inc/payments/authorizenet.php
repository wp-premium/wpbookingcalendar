<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

/*
 * Authorize.Net Server Integration Method(SIM).
 * 
 * Integration  was done on July of 2013
 * 
 * Based on guide: http://www.authorize.net/support/SIM_guide.pdf of May 2013
 * 
 */

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_payment_show_tab_in_top_settings_authorizenet(){
        ?><a    href="javascript:void(0)"
                onclick="javascript:
                        jQuery('.visibility_container').css('display','none');
                        jQuery('#visibility_container_authorizenet').css('display','block');
                        jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                        jQuery(this).addClass('booking-submenu-tab-selected');"
                rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php
                                     if ( get_bk_option( 'booking_authorizenet_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                                     original-title="<?php _e('Integration of authorizenet payment system' ,'booking');?>">
            <?php echo 'Authorize.Net';?>
            <input type="checkbox"  <?php if ( get_bk_option( 'booking_authorizenet_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
                   name="authorizenet_is_active_dublicated" id="authorizenet_is_active_dublicated"
                   onchange="document.getElementById('authorizenet_is_active').checked=this.checked;" >
        </a>
        <script type="text/javascript">
            jQuery(document).ready( function(){
                recheck_active_itmes_in_top_menu('authorizenet_is_active',   'authorizenet_is_active_dublicated');
            });
        </script>
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_authorizenet');


    // S E T T I N G S
    function wpdev_bk_payment_show_settings_content_authorizenet(){
        
            if ( isset( $_POST['authorizenet_curency'] ) ) {
                  if (isset( $_POST['authorizenet_is_active'] ))     $authorizenet_is_active = 'On';
                  else                                               $authorizenet_is_active = 'Off';
                  update_bk_option( 'booking_authorizenet_is_active', $authorizenet_is_active );
                  
                  if (isset( $_POST['authorizenet_relay_response_is_active'] )) $authorizenet_relay_response_is_active = 'On';
                  else                                                          $authorizenet_relay_response_is_active = 'Off';
                  update_bk_option( 'booking_authorizenet_relay_response_is_active', $authorizenet_relay_response_is_active );
                  
                  update_bk_option( 'booking_authorizenet_subject', $_POST['authorizenet_subject'] );
                  update_bk_option( 'booking_authorizenet_test', $_POST['authorizenet_test'] );
                  
                  update_bk_option( 'booking_authorizenet_order_successful', wpbc_make_link_relative( $_POST['authorizenet_order_successful'] ) );                                    
                  update_bk_option( 'booking_authorizenet_order_failed',     wpbc_make_link_relative( $_POST['authorizenet_order_failed'])  );
                  
                  if (! wpdev_bk_is_this_demo() ) {
                    update_bk_option( 'booking_authorizenet_api_login_id', $_POST['authorizenet_api_login_id'] );
                    update_bk_option( 'booking_authorizenet_transaction_key', $_POST['authorizenet_transaction_key'] );
                    update_bk_option( 'booking_authorizenet_md5_hash_value', $_POST['authorizenet_md5_hash_value'] );
                  }
                  update_bk_option( 'booking_authorizenet_payment_button_title', $_POST['authorizenet_payment_button_title'] );
                  
                  update_bk_option( 'booking_authorizenet_curency', $_POST['authorizenet_curency'] );
                  update_bk_option( 'booking_authorizenet_transaction_type', $_POST['authorizenet_transaction_type'] );
                 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 if (isset( $_POST['authorizenet_is_description_show'] ))     $authorizenet_is_description_show = 'On';
                 else                                                   $authorizenet_is_description_show = 'Off';
                 update_bk_option( 'booking_authorizenet_is_description_show' , $authorizenet_is_description_show );

                 if (isset( $_POST['authorizenet_is_auto_approve_booking'] ))     $authorizenet_is_auto_approve_booking = 'On';
                 else                                                   $authorizenet_is_auto_approve_booking = 'Off';
                 update_bk_option( 'booking_authorizenet_is_auto_approve_booking' , $authorizenet_is_auto_approve_booking );

            }

            $authorizenet_is_active         =  get_bk_option( 'booking_authorizenet_is_active' );            
            $authorizenet_subject           =  get_bk_option( 'booking_authorizenet_subject' );
            $authorizenet_test              =  get_bk_option( 'booking_authorizenet_test' );
            $authorizenet_order_successful  =  get_bk_option( 'booking_authorizenet_order_successful' );
            $authorizenet_md5_hash_value  =  get_bk_option( 'booking_authorizenet_md5_hash_value' );
            $authorizenet_order_failed      =  get_bk_option( 'booking_authorizenet_order_failed' );
            $authorizenet_api_login_id       =  get_bk_option( 'booking_authorizenet_api_login_id' );
            $authorizenet_transaction_key =  get_bk_option( 'booking_authorizenet_transaction_key' );
            $authorizenet_payment_button_title=  get_bk_option( 'booking_authorizenet_payment_button_title' );
            $authorizenet_curency           =  get_bk_option( 'booking_authorizenet_curency' );
            $authorizenet_transaction_type  =  get_bk_option( 'booking_authorizenet_transaction_type' );
            $authorizenet_is_description_show = get_bk_option( 'booking_authorizenet_is_description_show' );
            $authorizenet_is_auto_approve_booking  = get_bk_option( 'booking_authorizenet_is_auto_approve_booking' );
            $authorizenet_relay_response_is_active =  get_bk_option( 'booking_authorizenet_relay_response_is_active' );            
            ?>
            <div id="visibility_container_authorizenet" class="visibility_container" style="display:none;">
                <div class="meta-box wpbc-settings">
                  <div <?php $my_close_open_win_id = 'bk_settings_costs_authorizenet_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span>Authorize.Net - Server Integration Method (SIM)</span></h3> <div class="inside">
                        <!--form  name="post_option_authorizenet" action="" method="post" id="post_option_authorizenet" -->
                        <div class="wpbc-success-message"><?php printf(__('If you have no account on this system, please sign up for a %sdeveloper test account%s to obtain an API Login ID and Transaction Key. These keys will authenticate requests to the payment gateway.' ,'booking'), '<a href="http://developer.authorize.net/testaccount/"  target="_blank">','</a>');?></div>
                        <table class="wpbc-settings-table0 form-table settings-table0">
                            <tbody>
                                <tr>
                                    <th><?php _e('Active' ,'booking'); echo ' Authorize.Net:';  ?></th>
                                    <td class="wpbc-settings-data-field0">
                                        <input <?php if ($authorizenet_is_active == 'On') echo "checked"; ?>  value="<?php echo $authorizenet_is_active; ?>" name="authorizenet_is_active" id="authorizenet_is_active" type="checkbox"
                                               onchange="document.getElementById('authorizenet_is_active_dublicated').checked=this.checked;"
                                                                                                      />
                                        <label for="authorizenet_is_active" class="description"><?php printf(__(' Check this box to use %s payment gateway.' ,'booking'),'Authorize.Net');?></label>
                                    </td>
                                </tr>
                                <tr class="wpdevbk">
                                  <th class="wpbc-settings-label-field0 well" style="padding:10px;">
                                    <label for="authorizenet_api_login_id" ><?php _e('API Login ID' ,'booking'); ?>:</label>
                                  </th>
                                  <td class="wpbc-settings-data-field0 well" style="padding:10px;">
                                      <input value="<?php echo $authorizenet_api_login_id; ?>" name="authorizenet_api_login_id" id="authorizenet_api_login_id" class="regular-text code" type="text" size="45" />
                                      <p class="description"><?php printf(__('The merchant API Login ID is provided in the Merchant Interface of %s' ,'booking'),'Authorize.Net');?></p>
                                      <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                  </td>
                                </tr>
                                <tr class="wpdevbk">
                                  <th class="wpbc-settings-label-field0 well" style="padding:10px;">
                                    <label for="authorizenet_transaction_key" ><?php _e('Transaction Key' ,'booking'); ?>:</label>
                                  </th>
                                  <td class="wpbc-settings-data-field0 well" style="padding:10px;">
                                      <input value="<?php echo $authorizenet_transaction_key; ?>" name="authorizenet_transaction_key" id="authorizenet_transaction_key" class="regular-text code" type="text" size="45" />
                                      <p class="description"><?php printf(__('This parameter have to assigned to you by %s' ,'booking'),'Authorize.Net');?></p>
                                      <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                  </td>
                                </tr>
                                
                                <tr valign="top"><td style="padding:10px 0px; " colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                                
                                <tr>
                                  <th>
                                    <label for="authorizenet_test" ><?php _e('Chose payment mode' ,'booking'); ?>:</label>
                                  </th>
                                  <td class="wpbc-settings-data-field0">
                                     <select id="authorizenet_test" name="authorizenet_test">
                                        <option <?php if($authorizenet_test == 'SANDBOX') echo "selected"; ?> value="SANDBOX"><?php _e('Developer Test' ,'booking'); ?></option>
                                        <option <?php if($authorizenet_test == 'TEST') echo "selected"; ?> value="TEST"><?php _e('Live Test' ,'booking'); ?></option>
                                        <option <?php if($authorizenet_test == 'LIVE') echo "selected"; ?> value="LIVE"><?php _e('Live' ,'booking'); ?></option>
                                     </select>
                                     <span class="description"><?php printf(__('Select "Live test" or "Live" environment for using Merchant account or "Developer Test" for using Developer account.' ,'booking'),'<b>','</b>');?></span>
                                     <div class="wpbc-info-message" style="text-align:left;"><?php 
                                        echo '<strong>';_e('Note:' ,'booking'); echo '</strong> ';
                                        printf(__('Transactions posted against live merchant accounts using either of the above testing methods are not submitted to financial institutions for authorization and are not stored in the Merchant Interface.' ,'booking'),'<b>','</b>');
                                    ?></div>
                                  </td>
                                </tr>

                                <tr>
                                  <th>
                                    <label for="authorizenet_transaction_type" ><?php _e('Transaction type' ,'booking'); ?>:</label>
                                  </th>
                                  <td class="wpbc-settings-data-field0">                                                                                    
                                     <select id="authorizenet_transaction_type" name="authorizenet_transaction_type">
                                        <option <?php if($authorizenet_transaction_type == 'AUTH_CAPTURE') echo "selected"; ?> value="AUTH_CAPTURE"><?php _e('Authorization and Capture' ,'booking'); ?></option>
                                        <option <?php if($authorizenet_transaction_type == 'AUTH_ONLY') echo "selected"; ?> value="AUTH_ONLY"><?php _e('Authorization Only' ,'booking'); ?></option>
                                     </select>
                                     <span class="description"><?php printf(__('Select transaction type, which supported by the payment gateway.' ,'booking'),'<b>','</b>');?></span>
                                  </td>
                                </tr>


                                <tr>
                                  <th>
                                    <label for="authorizenet_curency" ><?php _e('Accepted Currency' ,'booking'); ?>:</label>
                                  </th>
                                  <td class="wpbc-settings-data-field0">                                          
                                     <select id="authorizenet_curency" name="authorizenet_curency">
                                        <option <?php if($authorizenet_curency == 'USD') echo "selected"; ?> value="USD"><?php _e('U.S. Dollars' ,'booking'); ?></option>
                                        <option <?php if($authorizenet_curency == 'CAD') echo "selected"; ?> value="CAD"><?php _e('Canadian Dollars' ,'booking'); ?></option>                                            
                                        <option <?php if($authorizenet_curency == 'GBP') echo "selected"; ?> value="GBP"><?php _e('Pounds Sterling' ,'booking'); ?></option>
                                        <option <?php if($authorizenet_curency == 'EUR') echo "selected"; ?> value="EUR"><?php _e('Euros' ,'booking'); ?></option>
                                     </select>
                                     <span class="description"><strong><?php _e('The currency code that gateway will process the payment in.' ,'booking');echo ' '; _e('Setting the currency that is not supported by the payment processor will result in an error.' ,'booking') ;?></strong></span>
                                  </td>
                                </tr>

                                <tr>
                                  <th>
                                    <label for="authorizenet_payment_button_title" ><?php _e('Payment button title' ,'booking'); ?>:</label>
                                  </th>
                                  <td class="wpbc-settings-data-field0">
                                      <input value="<?php echo $authorizenet_payment_button_title; ?>" name="authorizenet_payment_button_title" id="authorizenet_payment_button_title" class="regular-text code" type="text" size="45" />
                                      <p class="description"><?php printf(__('Enter the title of the payment button' ,'booking'),'Authorize.Net');?></p>
                                  </td>
                                </tr>

                                <tr valign="top">
                                  <th scope="row" ><?php _e('Show Payment description' ,'booking'); ?>:</th>
                                  <td>
                                    <fieldset>
                                        <label for="authorizenet_is_description_show">
                                            <input name="authorizenet_is_description_show" id="authorizenet_is_description_show" type="checkbox" 
                                                <?php if ($authorizenet_is_description_show == 'On') echo "checked";/**/ ?>  
                                                value="<?php echo $authorizenet_is_description_show; ?>"                                                 
                                                onclick="javascript: if (this.checked) jQuery('#togle_settings_authorizenet_subject').slideDown('normal'); else  jQuery('#togle_settings_authorizenet_subject').slideUp('normal');"
                                             /><?php _e('Check this box to show payment description in payment form' ,'booking');?>
                                        </label>
                                    </fieldset>
                                  </td>
                                </tr>
                                

                                <tr valign="top"><td colspan="2" style="padding:0px;">
                                    <div style="margin: 0px 0 10px 50px;">    
                                    <table id="togle_settings_authorizenet_subject" style="width:100%;<?php if ($authorizenet_is_description_show != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                        <tr>
                                        <th scope="row"><label for="authorizenet_subject" ><?php _e('Payment description' ,'booking'); ?>:</label></th>
                                            <td>
                                                <textarea  id="authorizenet_subject" name="authorizenet_subject" class="regular-text code" ><?php echo $authorizenet_subject; ?></textarea>
                                                <p class="description"><?php printf(__('Format: Up to 255 characters (no symbols). Also, in order to be displayed, the View attribute must be configured for this field in the Merchant Interface payment form settings.' ,'booking') );?></p>
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
                                
                                
                                <tr>
                                    <th><?php _e('Activate Relay Response' ,'booking'); ?>:</th>
                                    <td class="wpbc-settings-data-field0">
                                        <fieldset>
                                            <label for="authorizenet_relay_response_is_active"> 
                                                <input name="authorizenet_relay_response_is_active" id="authorizenet_relay_response_is_active" type="checkbox" 
                                                    <?php if ($authorizenet_relay_response_is_active == 'On') echo "checked"; ?>  
                                                    value="<?php echo $authorizenet_relay_response_is_active; ?>"                                                                                                 
                                                    onclick="javascript: if (this.checked) jQuery('#authorizenet_relay_response_depend').slideDown('normal'); else  jQuery('#authorizenet_relay_response_depend').slideUp('normal');"
                                                    />
                                                <?php printf(__('Indicate to the payment gateway that you would like to receive the transaction response to your site.' ,'booking') );?>
                                            </label>
                                        </fieldset>
                                    </td>
                                </tr>

                                <tr valign="top"><td colspan="2" style="padding:0px;">
                                    <div style="margin: -10px 0 10px 50px;">    
                                    <table id="authorizenet_relay_response_depend" style="width:100%;<?php if ($authorizenet_relay_response_is_active != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                
                                        <tr><td colspan="2">                                            
                                            <div class="wpbc-info-message" style="text-align:left;"><?php 
                                                echo '<strong>';_e('Note:' ,'booking'); echo '</strong> ';
                                                printf(__('You should leave empty the Relay Response URL and Receipt Link URL/Text in the Merchant Interface, if a Relay Response is activated here.' ,'booking') );
                                            ?></div>                                                                                        
                                        </td></tr>                                

                                        <tr>
                                          <th><label for="authorizenet_md5_hash_value" ><?php _e('MD5 Hash value' ,'booking'); ?>:</label></th>
                                          <td class="wpbc-settings-data-field0">                                                                                    
                                              <input value="<?php echo $authorizenet_md5_hash_value; ?>" name="authorizenet_md5_hash_value" id="authorizenet_md5_hash_value" class="regular-text code" type="text" size="45" />
                                              <p class="description"><?php printf(__('Please enter the MD5 Hash value, which you configured in the settings of Merchant Interface.' ,'booking'),'Authorize.Net');?></p>
                                              <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                          </td>
                                        </tr>

                                        <tr><th></th>
                                            <td >                         
                                                <div  class="wpbc-help-message"  style="margin-top:-10px;">
                                                  <p class="description"><strong><?php _e('To configure MD5 Hash value in Relay Response for your transactions' ,'booking'); ?> :</strong></p>
                                                  
                                                  <ul style="list-style: decimal;line-height: 1.5em;margin: 5px;padding: 0 30px;">
                                                    <li><?php _e('Log on to the Merchant Interface' ,'booking'); ?></li>
                                                    <li><?php _e('Click Settings under Account in the main menu on the left' ,'booking'); ?></li>
                                                    <li><?php _e('Click MD5-Hash in the Security Settings section' ,'booking'); ?></li>
                                                    <li><?php _e('Enter this value' ,'booking'); ?></li>
                                                    <li><?php _e('Click Submit' ,'booking'); ?></li>
                                                  </ul>                                                  
                                                  <div  style="text-align:left;"><?php printf(__('For more information about configuring Relay Response in the Merchant Interface, please see the %sMerchant Integration Guide%s' ,'booking'), '<a href="http://www.authorize.net/support/merchant/"  target="_blank">','</a>');?></div>
                                                </div>
                                          </td>
                                        </tr>                            

                                        <tr><td colspan="2" style="border-bottom:1px solid #ccc"></td></tr>

                                        <tr>
                                          <th><label for="authorizenet_order_successful" ><?php _e('Return URL after Successful order' ,'booking'); ?>:</label></th>
                                          <td class="wpbc-settings-data-field0">  
                                              <fieldset>
                                                <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $authorizenet_order_successful; ?>" name="authorizenet_order_successful" id="authorizenet_order_successful" class="regular-text code" type="text" size="45" />
                                              </fieldset>                                        
                                              <p class="description"><?php printf(__('Enter a return relative Successful URL. %s will redirect visitors to this page after Successful Payment' ,'booking'),'Authorize.Net');?><br/>
                                               <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo  get_option('siteurl') . $authorizenet_order_successful; ?>" target="_blank"><?php echo  get_option('siteurl') . $authorizenet_order_successful; ?></a></p>
                                          </td>
                                        </tr>

                                        <tr    >
                                          <th><label for="authorizenet_order_failed" ><?php _e('Return URL after Failed order' ,'booking'); ?>:</label></th>
                                          <td class="wpbc-settings-data-field0">   
                                              <fieldset>
                                                <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $authorizenet_order_failed; ?>" name="authorizenet_order_failed" id="authorizenet_order_failed" class="regular-text code" type="text" size="45" />
                                              </fieldset>                                        
                                              <p class="description"><?php printf(__('Enter a return relative Failed URL. %s will redirect visitors to this page after Failed Payment' ,'booking'),'Authorize.Net');?><br/>
                                               <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo   get_option('siteurl') . $authorizenet_order_failed; ?>" target="_blank"><?php  echo get_option('siteurl') . $authorizenet_order_failed; ?></a></p>
                                          </td>
                                        </tr>

                                        <tr>
                                          <th><?php _e('Automatically approve booking' ,'booking'); ?>:</th>
                                          <td class="wpbc-settings-data-field0">  
                                              <fieldset>
                                              <label for="authorizenet_is_auto_approve_booking" class="description">   
                                                <input name="authorizenet_is_auto_approve_booking" id="authorizenet_is_auto_approve_booking" type="checkbox"
                                                    <?php if ($authorizenet_is_auto_approve_booking == 'On') echo "checked";/**/ ?>  
                                                    value="<?php echo $authorizenet_is_auto_approve_booking; ?>" 
                                                     />
                                                <?php _e('Check this box to automatically approve booking, when visitor makes a successful payment.' ,'booking');?>
                                              </label>
                                              <p class="wpbc-info-message" style="text-align:left;"><strong><?php _e('Warning' ,'booking');?>!</strong> <?php _e('This will not work, if the visitor leaves the payment page.' ,'booking');?><p>    
                                              </fieldset>
                                            </td>
                                        </tr>
                                    </table>
                                    </div>
                            </td></tr>                    


                            </tbody>
                        </table>
                        
                        <div class="wpbc-error-message" style="text-align:left;">                            
                            <strong><?php printf(__('Note!' ,'booking') );?></strong><br/>
                            <?php printf(__('Be sure that the merchant server system clock is set to the proper time and time zone.' ,'booking') );?><br/>
                            <?php printf(__('Please configure all fields inside the Billing form fields tab at this page, when using a European payment processor' ,'booking') );?>
                        </div>   
                        
                        <div class="clear" style="height:10px;"></div>
                        <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="authorizenetsubmit"/>
                        <div class="clear" style="height:10px;"></div>

                    <!--/form-->
                   </div> </div> </div>
            </div>
              <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_authorizenet');


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_define_payment_form_authorizenet($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ){
        
        $output = '';
        if( (get_bk_option( 'booking_authorizenet_is_active' ) == 'On')   ) {
            
            $my_d_c = explode(',', $booking_days_count);
            
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
            // $my_short_dates = change_date_format($booking_days_new_string );
            $booking_days_count = $my_short_dates;


            $booking_form_show = get_form_content( $bkform,
                                                   $booking_type,
                                                   '',
                                                   array('booking_id'=> $booking_id ,
                                                         'id'=> $booking_id ,
                                                         'dates'=> $booking_days_count,
                                                         'datescount'=>count($my_d_c),
                                                         //'check_in_date' => $my_check_in_date,
                                                         //'check_out_date' => $my_check_out_date,
                                                         //'dates_count' => count($my_dates4emeil_check_in_out),
                                                         'cost' => (isset($summ))?$summ:'',
                                                         'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                         'resource_title'=> $bk_title[0]->title,
                                                         'bookingtype' => $bk_title[0]->title,
                                                         'bookingname' => $bk_title[0]->title
                                                       )
                                                 );
            $form_fields = $booking_form_show['_all_'];
            
            $authorizenet_subject           =  get_bk_option( 'booking_authorizenet_subject' );
            $authorizenet_subject           =  apply_bk_filter('wpdev_check_for_active_language', $authorizenet_subject );
            $authorizenet_subject           = replace_bk_shortcodes_in_form($authorizenet_subject, $booking_form_show['_all_fields_'], true);
            $subject_payment = $authorizenet_subject;            
            
            $authorizenet_is_description_show      = get_bk_option( 'booking_authorizenet_is_description_show' );
                        
//            $authorizenet_order_successful  =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=authorizenet&stats=OK' ;      
//            $authorizenet_order_failed      =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=authorizenet&stats=FAILED' ;  
                        
            //$authorizenet_order_successful  =  get_bk_option( 'booking_authorizenet_order_successful' );
            //$authorizenet_order_failed      =  get_bk_option( 'booking_authorizenet_order_failed' );
            //$authorizenet_is_auto_approve_booking  = get_bk_option( 'booking_authorizenet_is_auto_approve_booking' );


            
            $authorizenet_test              =  get_bk_option( 'booking_authorizenet_test' );
            if ($authorizenet_test == 'SANDBOX')    $post_URL = 'https://test.authorize.net/gateway/transact.dll';
            else                                    $post_URL = 'https://secure.authorize.net/gateway/transact.dll';
                
            $authorizenet_transaction_type  =  get_bk_option( 'booking_authorizenet_transaction_type' );
            $authorizenet_curency           =  get_bk_option( 'booking_authorizenet_curency' );
            
            $authorizenet_api_login_id      =  get_bk_option( 'booking_authorizenet_api_login_id' );
            $authorizenet_transaction_key   =  get_bk_option( 'booking_authorizenet_transaction_key' );
            $authorizenet_payment_button_title  =  get_bk_option( 'booking_authorizenet_payment_button_title' );
            $authorizenet_payment_button_title  =  apply_bk_filter('wpdev_check_for_active_language', $authorizenet_payment_button_title );
            $fp_timestamp = time();
            $fp_sequence = $booking_id . time();                                // Enter an invoice or other unique number.
            $fingerprint = getFingerPrintForAuthorizenet($authorizenet_api_login_id, $authorizenet_transaction_key, $summ, $fp_sequence, $fp_timestamp, $authorizenet_curency);

            
            $output = '<div style="width:100%;clear:both;margin-top:20px;"></div><div class="authorizenet_div wpbc-payment-form" style="text-align:left;clear:both;">';   
            $output .= '<form  method=\"post\" action=\"'.$post_URL.'\" id=\"authorizenetPayForm\" name=\"authorizenetPayForm\" style=\"text-align:left;\" class=\"booking_authorizenetPayForm\" >';
            
            
            $cost_currency = apply_bk_filter('get_currency_info', 'authorizenet');
            if ($authorizenet_is_description_show == 'On') $output .= $subject_payment . '<br />';

            $summ_show = wpdev_bk_cost_number_format ( $summ  );
            
            if ($is_deposit) $cost__title = __('Deposit' ,'booking')." : ";
            else             $cost__title = __('Cost' ,'booking')." : ";
            
            if ($cost_currency == $authorizenet_curency) $cost_summ_with_title = "<strong>".$cost__title . $summ_show ." " . $cost_currency ."</strong><br />";
            else                                         $cost_summ_with_title = "<strong>".$cost__title . $cost_currency ." " . $summ_show ."</strong><br />";
            
            $output .= $cost_summ_with_title;            
            
            
            // Merchant
            $output .= '<input type=\"hidden\" name=\"x_login\" value=\"'.$authorizenet_api_login_id.'\" />';
            // Fingerprint
            $output .= '<input type=\"hidden\" name=\"x_fp_hash\" value=\"'.$fingerprint.'\" />';
            $output .= '<input type=\"hidden\" name=\"x_fp_sequence\" value=\"'.$fp_sequence.'\" />';
            $output .= '<input type=\"hidden\" name=\"x_fp_timestamp\" value=\"'.$fp_timestamp.'\" />';
            // Transaction
            $output .= '<input type=\"hidden\" name=\"x_type\" value=\"'.$authorizenet_transaction_type.'\" />';
            // Payment
            $output .= '<input type=\"hidden\" name=\"x_amount\" value=\"'.$summ.'\" />';
            // Payment Form Configuration
            $output .= '<input type=\"hidden\" name=\"x_show_form\" value=\"payment_form\">';
            // Best Practice Fields
            $output .= '<input type=\"hidden\" name=\"x_version\" value=\"3.1\">';            
            $output .= '<input type=\"hidden\" name=\"x_method\" value=\"cc\">';    // Format: CC or ECHECK   Notes: The method of payment for the transaction, CC (credit card) or ECHECK (electronic check). If left blank, this value defaults to CC. 
            
            // SANDBOX Environment is not require this parameter in the payment form.
            if ($authorizenet_test == 'TEST') 
                $output .= '<input type=\"hidden\" name=\"x_test_request\" value=\"true\" />';
            if ($authorizenet_test == 'LIVE') 
                $output .= '<input type=\"hidden\" name=\"x_test_request\" value=\"false\" />';
            
            $output .= '<input type=\"hidden\" name=\"x_currency_code\" value=\"'.$authorizenet_curency.'\" />';
         
            $output .= '<input type=\"hidden\" name=\"x_description\" value=\"'.substr($subject_payment,0,255).'\" />';
            
            $output .= '<input type=\"hidden\" name=\"x_invoice_num\" value=\"booking'.$booking_id.'\" />';            
            $output .= '<input type=\"hidden\" name=\"x_po_num" value=\"order'.$wp_nonce.'\" />';
            
            
            // BILLING INFORMATION  -- Required only when using a European Payment Processor                
            $authorizenet_billing_customer_email  = (string) trim( get_bk_option( 'booking_billing_customer_email' ) . $booking_type );
            $authorizenet_billing_firstnames      = (string) trim( get_bk_option( 'booking_billing_firstnames' ) . $booking_type );
            $authorizenet_billing_surname         = (string) trim( get_bk_option( 'booking_billing_surname' ) . $booking_type );
            $authorizenet_billing_address1        = (string) trim( get_bk_option( 'booking_billing_address1' ) . $booking_type) ;
            $authorizenet_billing_city            = (string) trim( get_bk_option( 'booking_billing_city' ) . $booking_type );
            $authorizenet_billing_country         = (string) trim( get_bk_option( 'booking_billing_country' ) . $booking_type );
            $authorizenet_billing_post_code       = (string) trim( get_bk_option( 'booking_billing_post_code' ) . $booking_type );            
            $authorizenet_billing_state           = (string) trim( get_bk_option( 'booking_billing_state' ) . $booking_type );

            if ( isset($form_fields[$authorizenet_billing_firstnames]) )        // First Name
                $output .= '<input type=\"hidden\" name=\"x_first_name\" value=\"'.substr($form_fields[$authorizenet_billing_firstnames],0,50).'\" />';
            
            if ( isset($form_fields[$authorizenet_billing_surname]) )           // Last Name
                $output .= '<input type=\"hidden\" name=\"x_last_name\" value=\"'.substr($form_fields[$authorizenet_billing_surname],0,50).'\" />';
            
            if ( isset($form_fields[$authorizenet_billing_address1]) )          // Address
                $output .= '<input type=\"hidden\" name=\"x_address\" value=\"'.substr($form_fields[$authorizenet_billing_address1],0,60).'\" />';
                
            if ( isset($form_fields[$authorizenet_billing_city]) )              // City
                $output .= '<input type=\"hidden\" name=\"x_city\" value=\"'.substr($form_fields[$authorizenet_billing_city],0,40).'\" />';
            
            if ( isset($form_fields[$authorizenet_billing_country]) )           // Country
                $output .= '<input type=\"hidden\" name=\"x_country\" value=\"'.substr($form_fields[$authorizenet_billing_country],0,60).'\" />';
            
            if ( isset($form_fields[$authorizenet_billing_post_code]) )         // ZIP Code
                $output .= '<input type=\"hidden\" name=\"x_zip" value=\"'.substr($form_fields[$authorizenet_billing_post_code],0,20).'\" />';
            
            if ( isset($form_fields[$authorizenet_billing_customer_email]) )    // Email
                $output .= '<input type=\"hidden\" name=\"x_email\" value=\"'.substr($form_fields[$authorizenet_billing_customer_email],0,255).'\" />';
                        
            if ( isset($form_fields[$authorizenet_billing_state]) )             // State
                $output .= '<input type=\"hidden\" name=\"x_state\" value=\"'.substr($form_fields[$authorizenet_billing_state],0,40).'\" />';
            
                   
            // Relay Response Configuration
            $authorizenet_relay_response_URL  =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?pay_sys=authorizenet' ;      
            if ( get_bk_option( 'booking_authorizenet_relay_response_is_active' ) == 'On') {
                $output .= '<input type=\"hidden\" name=\"x_relay_response\" value=\"true\">';        
                $output .= '<input type=\"hidden\" name=\"x_relay_always\" value=\"true\">';        
                $output .= '<input type=\"hidden\" name=\"x_relay_url\" value=\"'.$authorizenet_relay_response_URL.'\">';
            }            
            $output .= '<input type=\"submit\" class=\"btn\" value=\"'.$authorizenet_payment_button_title.'\">';
            $output .= '</form>';
            $output .= '</div>';
            
            // Auto redirect to the Payment website,  after visitor clicked on "Send" button.
            /*
            ?><script type='text/javascript'> 
                setTimeout(function() { 
                   jQuery("#paypalbooking_form<?php echo $booking_type;?> .authorizenet_div.wpbc-payment-form form").submit(); 
                }, 500);                        
            </script><?php /**/
            
        }        
        return $output;        
    }
    add_bk_filter('wpdev_bk_define_payment_form_authorizenet', 'wpdev_bk_define_payment_form_authorizenet');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            
    // OK        
    function wpbc_add_payment_status_ok__authorizenet( $payment_status ){
       $payment_status = array_merge( $payment_status, 
                                        array(
                                            'Authorize.Net:Approved'
                                           ) 
                            );        
        return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_ok',  'wpbc_add_payment_status_ok__authorizenet');
    
    // Pending
    function wpbc_add_payment_status_pending__authorizenet( $payment_status ){
        
        $payment_status = array_merge( $payment_status,  array( 'Authorize.Net:Held for Review' )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_pending',  'wpbc_add_payment_status_pending__authorizenet');
    
    // Unknown
    function wpbc_add_payment_status_unknown__authorizenet( $payment_status ){
        
       $payment_status = array_merge( $payment_status,  array( 'Authorize.Net:Unknown' )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_unknown',  'wpbc_add_payment_status_unknown__authorizenet');
    
    // Error
    function wpbc_add_payment_status_error__authorizenet( $payment_status ){
        
       $payment_status = array_merge( $payment_status,  array( 'Authorize.Net:Error' , 'Authorize.Net:Declined' )  );
       
       return  $payment_status;        
    }    
    add_filter('wpbc_add_payment_status_error',    'wpbc_add_payment_status_error__authorizenet');
    
    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Checking response from  payment system
    function wpbc_check_response_status_with_crypted_paramaters__authorizenet( $response_status , $pay_system = '', $status = '', $booking_id = '', $wp_nonce = '' ) {
    
        if ($pay_system == 'authorizenet') {        
            
            // Authorize.Net ///////////////////////////////////////////////////

            $response = array();
            foreach ($_POST as $key => $value) {
                $name = substr($key, 2);
                $response[$name] = $value;
            }

            //Authenticate response from the Authorize.Net system here.                
            if (        
                    isset( $response['MD5_Hash'] ) &&
                    isset( $response['trans_id'] ) &&
                    isset( $response['amount'] ) 
               ) {                        
                    if ( ! wpbc_response_isAuthorizeNet($response['MD5_Hash'], $response['trans_id'], $response['amount']) ){
                        debuge( 'Authorize.Net response NOT Authenticated' ); die; }
            } else {    debuge ('Some parameters is not set for the Authorize.Net Authentication !!!'); die;}

            // Get parametrs about the booking
            if (isset($response['invoice_num'])) 
                $booking_id = trim( str_replace('booking','',$response['invoice_num']) );

            if (isset($response['po_num'])) 
                $wp_nonce = trim( str_replace('order','',$response['po_num']) );

            if (isset($response['response_code'])) {
                $status = trim( $response['response_code']  );
                if ( $status == 1) $status = 'Authorize.Net:Approved';
                else if ( $status == 2) $status = 'Authorize.Net:Declined'; 
                else if ( $status == 3) $status = 'Authorize.Net:Error';
                else if ( $status == 4) $status = 'Authorize.Net:Held for Review';
                else                    $status = 'Authorize.Net:Unknown';
            }
            if ( ($booking_id == '') || ($wp_nonce == '') ) {
                debuge( 'Can not detect the booking of this response' ); die;
            }
            //////////////////////////////////////////////////////////////////


            return array( 'pay_system'   => $pay_system
                          , 'status'     => $status
                          , 'booking_id' => $booking_id
                          , 'wp_nonce'   => $wp_nonce 
                        );
        } else 
            return $response_status;        
    }
    add_filter('wpbc_check_response_status_with_crypted_paramaters', 'wpbc_check_response_status_with_crypted_paramaters__authorizenet', 10, 5);
    
        
    function wpbc_auto_approve_or_cancell_and_redirect__authorizenet($pay_system, $status, $booking_id) {
     
        if ($pay_system == 'authorizenet') {
            
            $auto_approve = get_bk_option( 'booking_authorizenet_is_auto_approve_booking'  );
            
            if ( ($status == 'Authorize.Net:Approved') || ($status == 'Authorize.Net:Held for Review') ) {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, true );
                wpdev_redirect( get_bk_option( 'booking_authorizenet_order_successful' ) )   ;
                
            } else {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, false );
                wpdev_redirect( get_bk_option( 'booking_authorizenet_order_failed' ) )   ;
            }
        }
    }    
    add_bk_action( 'wpbc_auto_approve_or_cancell_and_redirect', 'wpbc_auto_approve_or_cancell_and_redirect__authorizenet');
    
    
    
        //////////////////////////////////////////////////////////////////////////////////////////////
        //  S u p p o r t   F u n c t i o n s      ///////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////////

        function wpbc_response_isAuthorizeNet($md5_hash, $transaction_id, $amount) {

            $api_login_id   = get_bk_option( 'booking_authorizenet_api_login_id');
            $md5_setting  =  get_bk_option( 'booking_authorizenet_md5_hash_value' );

            return  count($_POST) && 
                    $md5_hash && 
                    (wpbc_response_generateHash($api_login_id, $md5_setting , $transaction_id, $amount) == $md5_hash);
        }


        function wpbc_response_generateHash($api_login_id, $md5_setting , $transaction_id, $amount) {

            if ( empty($amount) ) $amount = "0.00";

            return strtoupper(md5($md5_setting . $api_login_id . $transaction_id . $amount));
        }
        

        // Generates a fingerprint needed for a hosted order form
        function getFingerPrintForAuthorizenet($api_login_id, $transaction_key, $amount, $fp_sequence, $fp_timestamp, $fp_curency) {
            //$api_login_id = ($api_login_id ? $api_login_id : (defined('AUTHORIZENET_API_LOGIN_ID') ? AUTHORIZENET_API_LOGIN_ID : ""));
            //$transaction_key = ($transaction_key ? $transaction_key : (defined('AUTHORIZENET_TRANSACTION_KEY') ? AUTHORIZENET_TRANSACTION_KEY : ""));
            if (function_exists('hash_hmac')) {
                return hash_hmac("md5", $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^" . $fp_curency, $transaction_key); 
            }
            return bin2hex(mhash(MHASH_MD5, $api_login_id . "^" . $fp_sequence . "^" . $fp_timestamp . "^" . $amount . "^" . $fp_curency, $transaction_key));
        }
        
        
        
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Activate
    function wpdev_bk_payment_activate_system_authorizenet() {

        // authorizenet Account /////////////////////////////////////////////////////////////////////////////////////////////
        add_bk_option( 'booking_authorizenet_is_active', 'Off' );
        add_bk_option( 'booking_authorizenet_relay_response_is_active', 'Off' );
        
        add_bk_option( 'booking_authorizenet_subject', sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[bookingname]','[dates]'));
        add_bk_option( 'booking_authorizenet_test', 'SANDBOX' );
        add_bk_option( 'booking_authorizenet_payment_button_title' , __('Pay via' ,'booking') .' Authorize.Net');
        
        if ( wpdev_bk_is_this_demo() ) {
            add_bk_option( 'booking_authorizenet_api_login_id', '29bzABJRJB7B' );
            add_bk_option( 'booking_authorizenet_transaction_key', '97NMkURkn84v6J46' );
            add_bk_option( 'booking_authorizenet_md5_hash_value', 'myhashvalue' );
            add_bk_option( 'booking_authorizenet_curency', 'USD' );
            add_bk_option( 'booking_authorizenet_transaction_type', 'AUTH_CAPTURE' );
            add_bk_option( 'booking_authorizenet_order_successful',  '/successful' );
            add_bk_option( 'booking_authorizenet_order_failed',  '/failed');
            update_bk_option( 'booking_authorizenet_is_active', 'On' );
            
        } else {
            add_bk_option( 'booking_authorizenet_api_login_id', '' );
            add_bk_option( 'booking_authorizenet_transaction_key', '' );
            add_bk_option( 'booking_authorizenet_curency', 'USD' );
            add_bk_option( 'booking_authorizenet_transaction_type', 'AUTH_CAPTURE' );
            add_bk_option( 'booking_authorizenet_md5_hash_value', '' );
            add_bk_option( 'booking_authorizenet_order_successful',  '/successful' );
            add_bk_option( 'booking_authorizenet_order_failed',  '/failed');
            
        }
        add_bk_option( 'booking_authorizenet_is_description_show', 'Off' );
        add_bk_option( 'booking_authorizenet_is_auto_approve_booking' , 'Off' );
    
    }
    add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_authorizenet');


    // Activate
    function wpdev_bk_payment_deactivate_system_authorizenet() {
        
        // authorizenet account
        delete_bk_option( 'booking_authorizenet_is_active' );
        delete_bk_option( 'booking_authorizenet_relay_response_is_active' );
        delete_bk_option( 'booking_authorizenet_subject' );
        delete_bk_option( 'booking_authorizenet_test' );
        delete_bk_option( 'booking_authorizenet_payment_button_title' );
        delete_bk_option( 'booking_authorizenet_md5_hash_value' );
        delete_bk_option( 'booking_authorizenet_order_successful' );
        delete_bk_option( 'booking_authorizenet_order_failed' );
        delete_bk_option( 'booking_authorizenet_api_login_id' );
        delete_bk_option( 'booking_authorizenet_transaction_key' );
        delete_bk_option( 'booking_authorizenet_curency' );
        delete_bk_option( 'booking_authorizenet_transaction_type' );
        delete_bk_option( 'booking_authorizenet_is_description_show' );
        delete_bk_option( 'booking_authorizenet_is_auto_approve_booking' );

    }
    add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_authorizenet');    
?>