<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Top toolbar
    function wpdev_bk_payment_show_tab_in_top_settings_ipay88(){
        ?>
            <a href="javascript:void(0)"
               onclick="javascript:
                       jQuery('.visibility_container').css('display','none');
                       jQuery('#visibility_container_ipay88').css('display','block');
                       jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                       jQuery(this).addClass('booking-submenu-tab-selected');"
               rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php
                    if ( get_bk_option( 'booking_ipay88_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
               original-title="<?php _e('Integration of iPay88 payment system' ,'booking');?>" >
             <?php _e('iPay88' ,'booking');?>
             <input type="checkbox" <?php if ( get_bk_option( 'booking_ipay88_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
                    name="ipay88_is_active_dublicated" id="ipay88_is_active_dublicated"
                       onchange="document.getElementById('ipay88_is_active').checked=this.checked;" >
            </a>
        <script type="text/javascript">
            jQuery(document).ready( function(){
                recheck_active_itmes_in_top_menu('ipay88_is_active', 'ipay88_is_active_dublicated');
            });
        </script>           
        <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_ipay88');


    // Settings
    function wpdev_bk_payment_show_settings_content_ipay88(){


        if( isset($_POST['ipay88_curency']) ) {
                 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 if (isset( $_POST['ipay88_is_active'] ))     $ipay88_is_active = 'On';
                 else                                         $ipay88_is_active = 'Off';
                 update_bk_option( 'booking_ipay88_is_active' , $ipay88_is_active );

                 update_bk_option( 'booking_ipay88_merchant_code' , $_POST['ipay88_merchant_code'] );
                 update_bk_option( 'booking_ipay88_merchant_key' , $_POST['ipay88_merchant_key'] );

                 if (isset( $_POST['ipay88_is_auto_approve_cancell_booking'] ))     $ipay88_is_auto_approve_cancell_booking = 'On';
                 else                                                               $ipay88_is_auto_approve_cancell_booking = 'Off';
                 update_bk_option( 'booking_ipay88_is_auto_approve_cancell_booking' , $ipay88_is_auto_approve_cancell_booking );
                 update_bk_option( 'booking_ipay88_return_url' ,        wpbc_make_link_relative( $_POST['ipay88_return_url'] ) );
                 update_bk_option( 'booking_ipay88_cancel_return_url' , wpbc_make_link_relative( $_POST['ipay88_cancel_return_url'] ) );
                 update_bk_option( 'booking_ipay88_curency'  , $_POST['ipay88_curency'] );
                 update_bk_option( 'booking_ipay88_subject' , $_POST['ipay88_subject'] );
                 update_bk_option( 'booking_ipay88_payment_button_title', $_POST['ipay88_payment_button_title'] );

                 if (isset( $_POST['ipay88_is_description_show'] ))     $ipay88_is_description_show = 'On';
                 else                                                   $ipay88_is_description_show = 'Off';
                 update_bk_option( 'booking_ipay88_is_description_show' , $ipay88_is_description_show );

        }

        $ipay88_is_active     = get_bk_option( 'booking_ipay88_is_active' );
        $ipay88_merchant_code = get_bk_option( 'booking_ipay88_merchant_code' );
        $ipay88_merchant_key = get_bk_option( 'booking_ipay88_merchant_key' );
        $ipay88_curency = get_bk_option( 'booking_ipay88_curency' );
        $ipay88_subject = get_bk_option( 'booking_ipay88_subject' );
        $ipay88_is_description_show = get_bk_option( 'booking_ipay88_is_description_show' );
        $ipay88_payment_button_title=  get_bk_option( 'booking_ipay88_payment_button_title' );

        $ipay88_is_auto_approve_cancell_booking =  get_bk_option( 'booking_ipay88_is_auto_approve_cancell_booking' );
        $ipay88_return_url                      =  get_bk_option( 'booking_ipay88_return_url' );
        $ipay88_cancel_return_url               =  get_bk_option( 'booking_ipay88_cancel_return_url' );

            /*
            Merchant Code
            Payment Method
            Merchant Reference Number

            Signature (refer to 1.1.11)

            Currency

            Payment Amount
            Response URL
            Product Description
            Merchant Remark


            Customer Name
            Customer Email
            Customer Contact
            /**/



        ?>
            <div id="visibility_container_ipay88" class="visibility_container" style="display:none;">

        <div class='meta-box'>
          <div <?php $my_close_open_win_id = 'bk_settings_costs_ipay88_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                <h3 class='hndle'><span><?php _e('iPay88 customization' ,'booking'); ?></span></h3> <div class="inside">

            <!--form  name="post_option" action="" method="post" id="post_option" -->

                <table class="form-table">
                    <tbody>

                        <tr valign="top">
                            <th scope="row"><?php _e('Active iPay88' ,'booking'); ?>:</th>
                            <td>
                                <fieldset>
                                    <label for="ipay88_is_active" >
                                        <input name="ipay88_is_active" id="ipay88_is_active" type="checkbox" 
                                            <?php if ($ipay88_is_active == 'On') echo "checked";/**/ ?>  
                                            value="<?php echo $ipay88_is_active; ?>" 
                                            onchange="document.getElementById('ipay88_is_active_dublicated').checked=this.checked;"
                                        />
                                        <?php _e(' Check this box to use iPay88 payment system.' ,'booking');?>
                                    </label>
                                </fieldset>                                            
                            </td>
                        </tr>

                        <tr class="wpdevbk">
                          <th class="well" style="padding:10px;">
                            <label for="ipay88_merchant_code" ><?php _e('Merchant Code' ,'booking'); ?>:</label>
                          </th>
                          <td class="well" style="padding:10px;">
                              <input value="<?php echo $ipay88_merchant_code; ?>"
                                name="ipay88_merchant_code" id="ipay88_merchant_code" 
                                class="regular-text code" type="text" size="45" 
                              />
                              <p class="description"><?php printf(__('Enter your iPay88 Merchant Code.' ,'booking'),'<b>','</b>');?></p>                                          
                          </td>
                        </tr>

                        <tr class="wpdevbk">
                          <th class="well" style="padding:10px;">
                            <label for="ipay88_merchant_key" ><?php _e('Merchant Key' ,'booking'); ?>:</label>
                          </th>
                          <td class="well" style="padding:10px;">
                              <input value="<?php echo $ipay88_merchant_key; ?>"
                                name="ipay88_merchant_key"
                                id="ipay88_merchant_key" class="regular-text code" type="text" size="45" />
                              <p class="description"><?php printf(__('Enter your iPay88 Merchant Key.' ,'booking'),'<b>','</b>');?></p>
                          </td>
                        </tr>

                        <tr valign="top"><td style="padding:10px 0px; " colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                        
                        <?php /*
                        Australian Dollar  AUD
                        Canadian Dollar CAD
                        Euro  EUR
                        Hong Kong Dollar HKD
                        Indian Rupee  INR
                        Indonesian Rupiah IDR
                        Malaysian Ringgit  MYR
                        Philippines Peso PHP
                        Pound Sterling  GBP
                        Singapore Dollar SGD
                        Thai Baht  THB
                        United States Dollar USD
                        New Taiwan dollar  TWD

                        United States Dollar USD
                        /**/
                        ?>
                        <tr valign="top">
                          <th scope="row"><label for="ipay88_curency" ><?php _e('Accepted Currency' ,'booking'); ?>:</label></th>
                          <td>
                             <select id="ipay88_curency" name="ipay88_curency">
                                <option <?php if($ipay88_curency == 'MYR') echo "selected"; ?> value="MYR"><?php _e('Malaysian Ringgit' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'USD') echo "selected"; ?> value="USD"><?php _e('U.S. Dollars' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'PHP') echo "selected"; ?> value="PHP"><?php _e('Philippines Peso' ,'booking'); ?></option>
                                
                              <?php /* ?>
                                <option <?php if($ipay88_curency == 'EUR') echo "selected"; ?> value="EUR"><?php _e('Euros' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'GBP') echo "selected"; ?> value="GBP"><?php _e('Pounds Sterling' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'JPY') echo "selected"; ?> value="JPY"><?php _e('Yen' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'AUD') echo "selected"; ?> value="AUD"><?php _e('Australian Dollars' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'CAD') echo "selected"; ?> value="CAD"><?php _e('Canadian Dollars' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'NZD') echo "selected"; ?> value="NZD"><?php _e('New Zealand Dollar' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'CHF') echo "selected"; ?> value="CHF"><?php _e('Swiss Franc' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'HKD') echo "selected"; ?> value="HKD"><?php _e('Hong Kong Dollar' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'SGD') echo "selected"; ?> value="SGD"><?php _e('Singapore Dollar' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'SEK') echo "selected"; ?> value="SEK"><?php _e('Swedish Krona' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'DKK') echo "selected"; ?> value="DKK"><?php _e('Danish Krone' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'PLN') echo "selected"; ?> value="PLN"><?php _e('Polish Zloty' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'NOK') echo "selected"; ?> value="NOK"><?php _e('Norwegian Krone' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'HUF') echo "selected"; ?> value="HUF"><?php _e('Hungarian Forint' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'CZK') echo "selected"; ?> value="CZK"><?php _e('Czech Koruna' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'ILS') echo "selected"; ?> value="ILS"><?php _e('Israeli Shekel' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'MXN') echo "selected"; ?> value="MXN"><?php _e('Mexican Peso' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'BRL') echo "selected"; ?> value="BRL"><?php _e('Brazilian Real (only for Brazilian users)' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'MYR') echo "selected"; ?> value="MYR"><?php _e('Malaysian Ringgits (only for Malaysian users)' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'PHP') echo "selected"; ?> value="PHP"><?php _e('Philippine Pesos' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'TWD') echo "selected"; ?> value="TWD"><?php _e('Taiwan New Dollars' ,'booking'); ?></option>
                                <option <?php if($ipay88_curency == 'THB') echo "selected"; ?> value="THB"><?php _e('Thai Baht' ,'booking'); ?></option>
                             <?php /**/ ?>
                             </select>
                             <span class="description"><?php printf(__('The currency code that gateway will process the payment in.' ,'booking'),'<b>','</b>');?></span>
                          </td>
                        </tr>

                        <tr>
                          <th><label for="ipay88_payment_button_title" ><?php _e('Payment button title' ,'booking'); ?>:</label></th>
                          <td>
                              <input value="<?php echo $ipay88_payment_button_title; ?>" 
                                     name="ipay88_payment_button_title" id="ipay88_payment_button_title" 
                                     class="regular-text code" type="text" size="45" />
                              <p class="description"><?php printf(__('Enter the title of the payment button' ,'booking'));?></p>
                          </td>
                        </tr>
                                    
                                    

                                <tr valign="top">
                                  <th scope="row" ><?php _e('Show Payment description' ,'booking'); ?>:</th>
                                  <td>
                                    <fieldset>
                                        <label for="ipay88_is_description_show">
                                            <input name="ipay88_is_description_show" id="ipay88_is_description_show" type="checkbox" 
                                                <?php if ($ipay88_is_description_show == 'On') echo "checked";/**/ ?>  
                                                value="<?php echo $ipay88_is_description_show; ?>" 
                                                onclick="javascript: if (this.checked) jQuery('#togle_settings_ipay88_subject').slideDown('normal'); else  jQuery('#togle_settings_ipay88_subject').slideUp('normal');"
                                             /><?php _e('Check this box to show payment description in payment form' ,'booking');?>
                                        </label>
                                    </fieldset>
                                  </td>
                                </tr>
                                

                                <tr valign="top"><td colspan="2" style="padding:0px;">
                                    <div style="margin: 0px 0 10px 50px;">    
                                    <table id="togle_settings_ipay88_subject" style="width:100%;<?php if ($ipay88_is_description_show != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                                        <tr>
                                        <th scope="row"><label for="ipay88_subject" ><?php _e('Payment description' ,'booking'); ?>:</label></th>
                                            <td>
                                                <textarea  id="ipay88_subject" name="ipay88_subject" class="regular-text code" ><?php echo $ipay88_subject; ?></textarea>
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
                                  <th><label for="ipay88_return_url" ><?php _e('Return URL after Successful order' ,'booking'); ?>:</label></th>
                                  <td>  
                                      <fieldset>
                                        <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $ipay88_return_url; ?>" name="ipay88_return_url" id="ipay88_return_url" class="regular-text code" type="text" size="45" />
                                      </fieldset>                                        
                                      <p class="description"><?php printf(__('Enter a return relative Successful URL. %s will redirect visitors to this page after Successful Payment' ,'booking'),'iPay88');?><br/>
                                       <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo  get_option('siteurl') . $ipay88_return_url; ?>" target="_blank"><?php echo  get_option('siteurl') . $ipay88_return_url; ?></a></p>
                                  </td>
                                </tr>

                                <tr    >
                                  <th><label for="ipay88_cancel_return_url" ><?php _e('Return URL after Failed order' ,'booking'); ?>:</label></th>
                                  <td>   
                                      <fieldset>
                                        <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $ipay88_cancel_return_url; ?>" name="ipay88_cancel_return_url" id="ipay88_cancel_return_url" class="regular-text code" type="text" size="45" />
                                      </fieldset>                                        
                                      <p class="description"><?php printf(__('Enter a return relative Failed URL. %s will redirect visitors to this page after Failed Payment' ,'booking'),'iPay88');?><br/>
                                       <?php printf(__('Please test this URL, it must be a valid address' ,'booking'),'<b>','</b>');?> <a href="<?php echo   get_option('siteurl') . $ipay88_cancel_return_url; ?>" target="_blank"><?php  echo get_option('siteurl') . $ipay88_cancel_return_url; ?></a></p>
                                  </td>
                                </tr>

                                <tr><td colspan="2" style="border-bottom:1px solid #ccc"></td></tr>
                                
                                <tr>
                                  <th><?php _e('Automatically approve/cancel booking' ,'booking'); ?>:</th>
                                  <td>  
                                      <fieldset>
                                      <label for="ipay88_is_auto_approve_cancell_booking" class="description">   
                                        <input name="ipay88_is_auto_approve_cancell_booking" id="ipay88_is_auto_approve_cancell_booking" type="checkbox"
                                            <?php if ($ipay88_is_auto_approve_cancell_booking == 'On') echo "checked";/**/ ?>  
                                            value="<?php echo $ipay88_is_auto_approve_cancell_booking; ?>" 
                                             />
                                        <?php _e('Check this box to automatically approve bookings when visitor makes a successful payment, or automatically cancel the booking when visitor makes a payment cancellation.' ,'booking');?>
                                      </label><?php /*
                                      <p class="wpbc-info-message" style="text-align:left;"><strong><?php _e('Warning' ,'booking');?>!</strong> <?php _e('This will not work, if the visitor leaves the payment page.' ,'booking');?><p>    */?>
                                      </fieldset>
                                    </td>
                                </tr>


                    </tbody>
                </table>

                <div class="wpbc-error-message" style="text-align:left;">                            
                    <strong><?php printf(__('Important!' ,'booking') );?></strong><br/>
                    <?php printf(__('Please configure %s fields inside the %sBilling form fields%s TAB at this page, this is necessary for the %s.' ,'booking'),'<b>' . __('Customer Email' ,'booking') . ', ' . __('First Name(s)' ,'booking') . ', ' . __('Last name' ,'booking') . ', ' . __('Phone' ,'booking') . '</b>', '<b>"', '"</b>','iPay88' ); ?>
                </div>   
            
                <div class="clear" style="height:10px;"></div>
                <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="ipay88submit"/>
                <div class="clear" style="height:10px;"></div>

            <!--/form-->

       </div> </div> </div>
            </div>
       <?php
    }
    add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_ipay88');



    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_bk_define_payment_form_ipay88($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ){
        $output = '';
        if( (get_bk_option( 'booking_ipay88_is_active' ) == 'On')   ) {
            $ipay88_is_active     = get_bk_option( 'booking_ipay88_is_active' );
            if ($ipay88_is_active == 'Off') return '';

            $ipay88_merchant_code   = get_bk_option( 'booking_ipay88_merchant_code' );
            $ipay88_merchant_key    = get_bk_option( 'booking_ipay88_merchant_key' );
            $ipay88_curency         = get_bk_option( 'booking_ipay88_curency' );
            $ipay88_subject         = get_bk_option( 'booking_ipay88_subject' );
            $ipay88_subject           =  apply_bk_filter('wpdev_check_for_active_language', $ipay88_subject );

            $ipay88_payment_button_title  =  get_bk_option( 'booking_ipay88_payment_button_title' );
            $ipay88_payment_button_title  =  apply_bk_filter('wpdev_check_for_active_language', $ipay88_payment_button_title );
            
            // Response page URL is the page at merchant website that will receive payment status from iPay88 OPSG. 
            $ipay88_order_Successful  =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking='  . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=ipay88&stats=OK' ;
            $ipay88_BackendURL        =  WPDEV_BK_PLUGIN_URL .'/inc/payments/ipay88-backend.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=ipay88&stats=OK' ;
            // This payment system  do  not use the "Failed" url parameter. We can  detect if this order success or not from the Success url page respose.
            // $ipay88_order_Failed      =  WPDEV_BK_PLUGIN_URL .'/inc/payments/wpbc-response.php?payed_booking=' . $booking_id .'&wp_nonce=' . $wp_nonce . '&pay_sys=ipay88&stats=FAILED' ;   


            $cost_currency = apply_bk_filter('get_currency_info', 'ipay88');
            // iPay Description of this payment /////////////////////////////////////////////////////////
            $ipay88_subject = str_replace('[bookingname]',$bk_title[0]->title,$ipay88_subject);

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


            $ipay88_subject = str_replace('[dates]',$my_short_dates,$ipay88_subject);

            $my_d_c = explode(',', $booking_days_count);
            $my_d_c = count($my_d_c);
            $ipay88_subject = str_replace('[datescount]',$my_d_c,$ipay88_subject);
            $ipay88_subject = str_replace('"','',$ipay88_subject);
            //////////////////////////////////////////////////////////////////////////////////////////////


            // Prepopulate some fields : ////////////////////////////////////////////////////////////////
            $form_fields = get_form_content ($bkform, $booking_type, '', array('booking_id'=> $booking_id ,
                                                                              'resource_title'=> $bk_title ) );

//debuge($form_fields, $bkform, $booking_type);
            $form_fields = $form_fields['_all_'];
//debuge($form_fields);
            $email = '';
            if (  get_bk_option( 'booking_billing_customer_email' )  !== false ) {
              $billing_customer_email  = (string) trim( get_bk_option( 'booking_billing_customer_email' ) . $booking_type );
              if ( isset($form_fields[$billing_customer_email]) !== false ){
                  $email      = substr($form_fields[$billing_customer_email], 0, 100);
              }
            }
            $email =  substr($email, 0, 100);
            $first_name = '';
            if ( get_bk_option( 'booking_billing_firstnames' )  !== false ) {
              $billing_firstnames      = (string) trim( get_bk_option( 'booking_billing_firstnames' ) . $booking_type );
              if ( isset($form_fields[$billing_firstnames]) !== false ){
                  $first_name = substr($form_fields[$billing_firstnames], 0, 32);
              }
            }
            $last_name = '';
            if ( get_bk_option( 'booking_billing_surname' )  !== false ) {
              $billing_surname         = (string) trim( get_bk_option( 'booking_billing_surname' ) . $booking_type );
              if ( isset($form_fields[$billing_surname]) !== false ){
                  $last_name  = substr($form_fields[$billing_surname], 0, 64);
              }
            }
            $firstlast_name =  substr($first_name . ' ' . $last_name, 0, 100);

            $phone = ' ';
            if ( get_bk_option( 'booking_billing_phone' )  !== false ) {
              $billing_phone         = (string) trim( get_bk_option( 'booking_billing_phone' ) . $booking_type );
              if ( isset($form_fields[$billing_phone]) !== false ){
                  $phone  = substr($form_fields[$billing_phone], 0, 20);
              }
            }

            //////////////////////////////////////////////////////////////////////////////////////////////

            // Amount  Currency- Payment amount with two decimals and thousand symbols.  Example: 1,278.99 
            // Check  iPay88 Technical Spec v.1.6.1 on page #7
            $summ = number_format ( $summ , 2 , '.' , ',' );
            //$summ = str_replace(',', '.', $summ);
            $ref_no = substr('A0' . $booking_id, 0 , 20);




            $summ_sing = str_replace('.', '', $summ);
            $summ_sing = str_replace(',', '', $summ_sing);
            $signature = $ipay88_merchant_key . $ipay88_merchant_code . $ref_no . $summ_sing . $ipay88_curency;
            $signature = iPay88_signature($signature);



//debuge($firstlast_name, $email, $phone, $ipay88_merchant_code);
            if ( (! empty($firstlast_name)) && (! empty($email)) && (! empty($phone)) && (! empty($ipay88_merchant_code)) ) {

            // Show payment cost and some description //////////////////////////


                $is_show_it = get_bk_option( 'booking_ipay88_is_description_show' );
                if ($is_show_it == 'On') $output .= $ipay88_subject . '<br />';

                $ipay88_subject =  substr($ipay88_subject, 0, 100);
                $summ_show = wpdev_bk_cost_number_format ( $summ  );
                if ($is_deposit) $cost__title = __('Deposit' ,'booking')." : ";
                else             $cost__title = __('Cost' ,'booking')." : ";
                if ($cost_currency == $ipay88_curency) $cost_summ_with_title = "<strong>".$cost__title. $summ_show ." " . $cost_currency ."</strong><br />";
                else                                   $cost_summ_with_title = "<strong>".$cost__title. $cost_currency ." " . $summ_show ."</strong><br />";

                
                /*
                if ($is_deposit) {
                    $today_day = date('m.d.Y')  ;
                    $cost_summ_with_title .= ' ('  . $today_day .')';
                    make_bk_action('wpdev_make_update_of_remark' , $booking_id , $cost_summ_with_title , true );
                }/**/


            // Generate iPay88 form /////////////////////////////////////////////////////////////////////////////////////////
                $output .= '<div style="width:100%;clear:both;margin-top:20px;"></div><div class="ipay88_div wpbc-payment-form" style="text-align:left;clear:both;">';   
                
                $output .= $cost_summ_with_title;

                $output .= "<FORM method=\"post\" name=\"ePayment\" action=\"https://www.mobile88.com/ePayment/entry.asp\">";

                $output .= "<INPUT type=\"hidden\" name=\"MerchantCode\"  value=\"".$ipay88_merchant_code."\">";
                $output .= "<INPUT type=\"hidden\" name=\"PaymentId\" value=\"\">";  // Payment Gateway get  here default value - Payment ID is getted reffear to the Appendix 1 from ipay88 API
                $output .= "<INPUT type=\"hidden\" name=\"RefNo\"  value=\"".$ref_no."\">";
                $output .= "<INPUT type=\"hidden\" name=\"Amount\"  value=\"".$summ."\">";
                $output .= "<INPUT type=\"hidden\" name=\"Currency\"  value=\"".$ipay88_curency."\">";
                $output .= "<INPUT type=\"hidden\" name=\"ProdDesc\"  value=\"".$ipay88_subject."\">";
                $output .= "<INPUT type=\"hidden\" name=\"UserName\"  value=\"".$firstlast_name."\">";
                $output .= "<INPUT type=\"hidden\" name=\"UserEmail\"  value=\"".$email."\">";
                $output .= "<INPUT type=\"hidden\" name=\"UserContact\"  value=\"".$phone."\">";
                $output .= "<INPUT type=\"hidden\" name=\"Remark\"  value=\"\">";
                $output .= "<INPUT type=\"hidden\" name=\"Lang\"   value=\"UTF-8\">";
                $output .= "<INPUT type=\"hidden\" name=\"Signature\"  value=\"".$signature."\">";
            $output .= "<INPUT type=\"hidden\" name=\"ResponseURL\" value=\"". $ipay88_order_Successful ."\">";            
            $output .= "<INPUT type=\"hidden\" name=\"BackendURL\" value=\"". $ipay88_BackendURL ."\">";        //Fix: 5.3  New.
                $output .= "<INPUT class=\"btn\" type=\"submit\" value=\"".$ipay88_payment_button_title."\" name=\"Submit\">";

                $output .= "</FORM>";
                $output .= "</div>";
                               
            //////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                // Auto redirect to the Payment website,  after visitor clicked on "Send" button.
                /*
                ?><script type='text/javascript'> 
                    setTimeout(function() { 
                       jQuery("#paypalbooking_form<?php echo $booking_type;?> .ipay88_div.wpbc-payment-form form").submit(); 
                    }, 500);                        
                </script><?php /**/
                
            }

        }
        return $output ;

    }
    add_bk_filter('wpdev_bk_define_payment_form_ipay88', 'wpdev_bk_define_payment_form_ipay88');

    
                
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // OK
    function wpbc_add_payment_status_ok__ipay88( $payment_status ){
       $payment_status = array_merge( $payment_status, 
                                        array(
                                            'ipay88:OK'
                                           ) 
                            );        
        return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_ok',  'wpbc_add_payment_status_ok__ipay88');
            
    // Pending
    function wpbc_add_payment_status_pending__ipay88( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_pending',  'wpbc_add_payment_status_pending__ipay88');
    
    // Unknown
    function wpbc_add_payment_status_unknown__ipay88( $payment_status ){
        
       // $payment_status = array_merge( $payment_status,  array(  )  );
       
       return  $payment_status;        
    }
    add_filter('wpbc_add_payment_status_unknown',  'wpbc_add_payment_status_unknown__ipay88');
    
    // Error
    function wpbc_add_payment_status_error__ipay88( $payment_status ){
        
       $payment_status = array_merge( $payment_status,  array( 'ipay88:Failed' )  );
       
       return  $payment_status;        
    }    
    add_filter('wpbc_add_payment_status_error',    'wpbc_add_payment_status_error__ipay88');    
    

    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Checking response from  payment system
    function wpbc_check_response_status__ipay88($response_status , $pay_system, $status, $booking_id, $wp_nonce) {
    
        if ($pay_system == 'ipay88') {
            
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
                            debuge( 'Respond not from correct payment site !' );
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
                        debuge($_REQUEST['ErrDesc'], $iPayStatusMessage );
                    }
                }

                if(0){ //Disabled check
                    // Check payment ammount
                    if ($status == '')
                        if ($slct_sql_results[0]->cost != $Amount ) {
                            debuge( 'Payment amount is different from original !' );
                            $status = 'ipay88:Failed';
                        }
                }
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
                        debuge( 'Signature is different from original !' );
                        $status = 'ipay88:Failed';
                    }
                }

                if ($status == '') $status = 'ipay88:OK';

            } else {
                $status = 'ipay88:Failed';
                if ( isset($_REQUEST['ErrDesc']) )
                    debuge($_REQUEST['ErrDesc']);
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
        } else 
            return $response_status;        
    }
    add_filter('wpbc_check_response_status',    'wpbc_check_response_status__ipay88', 10, 5 );
    
    
    function wpbc_auto_approve_or_cancell_and_redirect__ipay88($pay_system, $status, $booking_id) {

        
        if ($pay_system == 'ipay88') {

            // Fix: 5.3
            // We can  auto approve or decline the booking based on respond at backend POST Feature
            // Here we just  open Success or Failed URL
            // 
            // $auto_approve = get_bk_option( 'booking_ipay88_is_auto_approve_cancell_booking'  );
            $auto_approve = '';
            
            if ( ($status == 'OK') || ($status == 'ipay88:OK') ) {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, true );
                wpdev_redirect( get_bk_option( 'booking_ipay88_return_url' ) )   ;
                
            } else {
                if ($auto_approve == 'On')                 
                    check_auto_approve_or_cancell($booking_id, false );
                wpdev_redirect( get_bk_option( 'booking_ipay88_cancel_return_url' ) )   ;
            }
        }
    }    
    add_bk_action( 'wpbc_auto_approve_or_cancell_and_redirect', 'wpbc_auto_approve_or_cancell_and_redirect__ipay88');
    
    
    
        //////////////////////////////////////////////////////////////////////////////////////////////
        //  S u p p o r t   F u n c t i o n s      ///////////////////////////////////////////////////
        //////////////////////////////////////////////////////////////////////////////////////////////

        // iPay88
        function iPay88_signature($source) {
                return base64_encode(iPay88_hex2bin(sha1($source)));
        }

        function iPay88_hex2bin($hexSource) {
            $bin='';
             for ($i=0;$i<strlen($hexSource);$i=$i+2) {
              $bin .= chr(hexdec(substr($hexSource,$i,2)));
             }
             return $bin;
        }

        function iPay88_Requery($MerchantCode, $RefNo, $Amount){

            // $query = "http://www.mobile88.com/epayment/enquiry.asp?MerchantCode=" . $MerchantCode . "&RefNo=" . $RefNo . "&Amount=" . $Amount;
            //             
                                                                                //Fix: 5.3
            $query = "https://www.mobile88.com/epayment/enquiry.asp?MerchantCode=" . $MerchantCode . "&RefNo=" . str_replace(" ","%20",$RefNo) . "&Amount=" . $Amount;
            
            $url = parse_url($query);
            $host = $url["host"];
            $path = $url["path"] . "?" . $url["query"];
            $timeout = 1;
            $fp = fsockopen ($host, 80, $errno, $errstr, $timeout);
            if ($fp) {
              fputs ($fp, "GET $path HTTP/1.0\nHost: " . $host . "\n\n");
              while (!feof($fp)) {
                $buf .= fgets($fp, 128);
              }
              //$lines = split("\n", $buf);
              $lines = preg_split("/\n/", $buf);                                //Fix: 5.3
              $Result = $lines[count($lines)-1];
              fclose($fp);
            } else {
              # enter error handing code here
              $Result = 'Connection Error';
            }
            return $Result;	
        }
    
        
        
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Activate
    function wpdev_bk_payment_activate_system_ipay88() {
        global $wpdb;
        
        add_bk_option( 'booking_ipay88_payment_button_title' , __('Pay via' ,'booking') .' iPay88');
        add_bk_option( 'booking_ipay88_is_active' , 'Off');
        add_bk_option( 'booking_ipay88_merchant_code', '' );
        add_bk_option( 'booking_ipay88_merchant_key', '' );
        add_bk_option( 'booking_ipay88_curency'  , 'MYR' );
        add_bk_option( 'booking_ipay88_subject', sprintf(__('Payment for booking %s on these day(s): %s'  ,'booking'),'[bookingname]','[dates]'));
        add_bk_option( 'booking_ipay88_is_description_show', 'Off' );
        add_bk_option( 'booking_ipay88_is_auto_approve_cancell_booking', 'Off' );
        add_bk_option( 'booking_ipay88_return_url', '/successful' );
        add_bk_option( 'booking_ipay88_cancel_return_url', '/failed' );

    }
    add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_ipay88');


    // Activate
    function wpdev_bk_payment_deactivate_system_ipay88() {
        global $wpdb;

        delete_bk_option( 'booking_ipay88_payment_button_title' );
        delete_bk_option( 'booking_ipay88_is_active' );
        delete_bk_option( 'booking_ipay88_merchant_code' );
        delete_bk_option( 'booking_ipay88_merchant_key' );
        delete_bk_option( 'booking_ipay88_curency'   );
        delete_bk_option( 'booking_ipay88_subject' );
        delete_bk_option( 'booking_ipay88_is_description_show' );
        delete_bk_option( 'booking_ipay88_is_auto_approve_cancell_booking' );
        delete_bk_option( 'booking_ipay88_return_url' );
        delete_bk_option( 'booking_ipay88_cancel_return_url' );
    }
    add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_ipay88');
?>