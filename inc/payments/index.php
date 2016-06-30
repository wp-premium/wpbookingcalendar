<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/bank-transfer.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/bank-transfer.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/pay-cash.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/pay-cash.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/paypal.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/paypal.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/authorizenet.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/authorizenet.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/sage.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/sage.php' ); }
    if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/ipay88.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/ipay88.php' ); }
    // if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/payments/sermepa.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/payments/sermepa.php' ); }

    function wpdev_bk_define_payment_forms($blank, $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit=false, $additional_calendars = array() ){
        
        $output = '';

        $payment_varriants = array();
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Show both deposit and total cost payment forms, after visitor submit booking. 
        // Important! Please note, in this case at admin panel for booking will be saved deposit cost and notes about deposit, 
        // do not depend from the visitor choise of this payment. So you need to check each such payment manually.
        if (  ( WP_BK_SHOW_DEPOSIT_AND_TOTAL_PAYMENT ) && ( $is_deposit ) && ( function_exists( 'wpbc_get_cost_of_booking' ) )  ){
            
            $total_cost_of_booking = wpbc_get_cost_of_booking( array(
                  'form' =>  $bkform, 
                  'all_dates' => $booking_days_count, 
                  'bk_type' => $booking_type, 
                  'booking_form_type' => apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $booking_type )
              ) ) ;

            $payment_varriants[] = array( 'sum' => $total_cost_of_booking['total_orig'], 'is_deposit' => false );
        } ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        
        $payment_varriants[] = array( 'sum' => $summ, 'is_deposit' => $is_deposit );
        
        foreach ( $payment_varriants as $payment_varriant ) {
            
            $summ       = $payment_varriant['sum'];
            $is_deposit = $payment_varriant['is_deposit'];
                    
            $output .= apply_bk_filter('wpdev_bk_define_payment_form_bank_transfer', '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
            $output .= apply_bk_filter('wpdev_bk_define_payment_form_pay_cash', '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
            $output .= apply_bk_filter('wpdev_bk_define_payment_form_paypal',       '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
            $output .= apply_bk_filter('wpdev_bk_define_payment_form_authorizenet', '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
            $output .= apply_bk_filter('wpdev_bk_define_payment_form_sage',         '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
            $output .= apply_bk_filter('wpdev_bk_define_payment_form_ipay88',       '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
            // $output .= apply_bk_filter('wpdev_bk_define_payment_form_sermepa',       '', $booking_id, $summ,$bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit );
        }
        
        $output = str_replace("'",'"',$output);
        $output = str_replace('\"','"',$output);

        $is_show_booking_summary_in_payment_form = get_bk_option( 'booking_is_show_booking_summary_in_payment_form'  );
        if ($is_show_booking_summary_in_payment_form == 'On')
            echo get_booking_summary_info($booking_id, $bkform, $booking_type, $additional_calendars);
        
        return $output;
    }
    add_bk_filter('wpdev_bk_define_payment_forms', 'wpdev_bk_define_payment_forms');

    function wpdev_bk_is_payment_forms_off($blank){

        $is_active =  get_bk_option( 'booking_bank_transfer_is_active' ) ;
        if ($is_active == 'On') return  false;
        
        $is_active =  get_bk_option( 'booking_pay_cash_is_active' ) ;
        if ($is_active == 'On') return  false;
        
        $is_active =  get_bk_option( 'booking_paypal_is_active' );
        if ($is_active == 'On') return  false;

        $is_active =  get_bk_option( 'booking_authorizenet_is_active' );
        if ($is_active == 'On') return  false;

        $is_active =  get_bk_option( 'booking_sage_is_active' );
        if ($is_active == 'On') return  false;

        $is_active =  get_bk_option( 'booking_ipay88_is_active' ) ;
        if ($is_active == 'On') return  false;
        
        // $is_active =  get_bk_option( 'booking_sermepa_is_active' ) ;
        // if ($is_active == 'On') return  false;
        
        return true;
    }
    add_bk_filter('is_payment_forms_off', 'wpdev_bk_is_payment_forms_off');


    
    function get_booking_summary_info($booking_id, $bkform, $booking_type, $additional_calendars=array()) {


        if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $booking_type );
        else $bk_title = '';

        if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( get_dates_str($booking_id) );
        else                                                     $my_dates_4_send = change_date_format(get_dates_str($booking_id));

        $check_in_out = explode(',',get_dates_str($booking_id));

        $my_check_in_date  = change_date_format($check_in_out[0] );
        $my_check_out_date = change_date_format($check_in_out[ count($check_in_out)-1 ] );
//debuge($bk_title, apply_bk_filter('wpdev_check_for_active_language', $bk_title ), $bkform );
        $booking_form_show = get_form_content( $bkform,
                                               $booking_type,
                                               '',
                                               array('booking_id'=> $booking_id ,
                                                     'id'=> $booking_id ,
                                                     'dates'=> $my_dates_4_send,
                                                     'check_in_date' => $my_check_in_date,
                                                     'check_out_date' => $my_check_out_date,
                                                     'dates_count' => count($check_in_out),
                                                     // 'cost' => (isset($res->cost))?wpdev_bk_cost_number_format($res->cost):'',
                                                     'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                     'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'remote_ip'     => $_SERVER['REMOTE_ADDR'],           // The IP address from which the user is viewing the current page. 
                                                     'user_agent'    => $_SERVER['HTTP_USER_AGENT'],       // Contents of the User-Agent: header from the current request, if there is one. 
                                                     'request_url'   => $_SERVER['HTTP_REFERER'],          // The address of the page (if any) where action was occured. Because we are sending it in Ajax request, we need to use the REFERER HTTP
                                                     'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                     'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                                                                           
                                                   )
                                             );

    
//        if (function_exists ('get_booking_title')) 
//             $bk_title = get_booking_title( $booking_type );
//        else $bk_title = '';
//
//        $booking_form_show = get_form_content(  $bkform, 
//                                                $booking_type, 
//                                                '', 
//                                                array(
//                                                        'booking_id'=> $booking_id ,
//                                                        'resource_title'=> $bk_title 
//                                                      ) 
//                                             );
//
//        if (get_bk_option( 'booking_date_view_type') == 'short') 
//            $my_dates_4_send = get_dates_short_format( get_dates_str($booking_id) );
//        else                                                     
//            $my_dates_4_send = change_date_format(get_dates_str($booking_id));

        
        if ( count($additional_calendars) > 0 ) {
            
            $additional_calendars_description = '';
            foreach ($additional_calendars as $bk_id=>$bk_dates) {

                if (function_exists ('get_booking_title')) $bk_title_add =  get_booking_title( $bk_id );
                else $bk_title_add = '';

                        $my_dates = explode(",",$bk_dates);

                        $i = 0;
                        foreach ($my_dates as $md) { // Set in dates in such format: yyyy.mm.dd
                            if ($md != '') {
                                $md = explode('.',$md);
                                $my_dates[$i] = $md[2] . '.' . $md[1] . '.' . $md[0] ;
                            } else { unset($my_dates[$i]) ; } // If some dates is empty so remove it   // This situation can be if using several bk calendars and some calendars is not checked
                            $i++;
                        }
                        sort($my_dates); // Sort dates

                        $my_dates4info = '';
                        foreach ($my_dates as $my_date) {
                            $my_date = explode('.',$my_date);
                            $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], 0,0,0 );
                            $my_dates4info .= $date . ',';
                        }
                        $my_dates4info2 =  substr($my_dates4info, 0, -1);

                if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send_add = get_dates_short_format( $my_dates4info2 );
                else                                                     $my_dates_4_send_add = change_date_format( $my_dates4info2 );

                $additional_calendars_description .= $bk_title_add . ': ' . $my_dates_4_send_add . '; '  ;
            }
            $additional_calendars_description2 = substr($additional_calendars_description, 0, -2);
            $my_dates_4_send = $bk_title . ': ' . $my_dates_4_send . '; ' . $additional_calendars_description2;
        }


        $booking_content_for_showing = $booking_form_show['content'] ;
        /*
        $search = array ("'(<br[ ]?[/]?>)+'si","'(<p[ ]?[/]?>)+'si","'(<div[ ]?[/]?>)+'si");
        $replace = array ("&nbsp;&nbsp;"," &nbsp; "," &nbsp; ");
        $booking_content_for_showing = preg_replace($search, $replace, $booking_content_for_showing);
       /**/

        $booking_content_for_showing = esc_js( $booking_content_for_showing );  
        $booking_content_for_showing = html_entity_decode( $booking_content_for_showing );
        $booking_content_for_showing = str_replace("\\n",'',$booking_content_for_showing);
        
        
        $output = '<div  class="booking_summary" >';
            $output .= '<div  class="booking_summary_dates" style="margin:10px 0px;"><label class="booking_summary_dates_title" style="font-weight:bold;">' . __('Dates:' ,'booking') .'</label> ' . $my_dates_4_send . '</div>';
            $output .= '<div  class="booking_summary_data_title" style="font-weight:bold;" >' . __('Booking Details:' ,'booking') . '</div>';
            $output .= '<div  class="booking_summary_data" >' . $booking_content_for_showing . '</div>';
        $output .= '</div>';

        $output .= '<script type="text/javascript">';
        $output .=   'jQuery("#ajax_respond_insert'.$booking_type.'").after( jQuery("#ajax_respond_insert'.$booking_type.' .booking_summary") );';
        $output .=   'setTimeout(function ( ) {makeScroll("#booking_form'.$booking_type.' .booking_summary" );} ,750);';
        $output .= '</script>';

        return $output;
    }

    

    // Payments Help Section for description
    function wpbc_payment_help_section( $skip_shortcodes = array(), $extend_shortcodes = array() ) {
        ?>
            <div class="wpbc-help-message" style="margin-top:10px;">
                
                <p class="description"><strong><?php printf(__('You can use following shortcodes in content of this template' ,'booking'));?></strong>: </p>

                
                <?php if ( in_array('account_details', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting all bank accounts details' ,'booking'),'<code>[account_details]</code>');?>, </p>
                <?php } ?>                
                <?php if ( in_array('account_name', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting account name' ,'booking'),'<code>[account_name]</code>');?>, </p>
                <?php } ?>
                <?php if ( in_array('account_number', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting account number' ,'booking'),'<code>[account_number]</code>');?>, </p>
                <?php } ?>
                <?php if ( in_array('bank_name', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting bank name ' ,'booking'),'<code>[bank_name]</code>');?>, </p>
                <?php } ?>
                <?php if ( in_array('sort_code', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting sort code ' ,'booking'),'<code>[sort_code]</code>');?>, </p>
                <?php } ?>
                <?php if ( in_array('iban', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting IBAN ' ,'booking'),'<code>[iban]</code>');?>, </p>
                <?php } ?>
                <?php if ( in_array('bic', $extend_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting BIC ' ,'booking'),'<code>[bic]</code>');?>, </p><hr/>
                <?php } ?>
                
                <p class="description" style="font-weight:normal;"><?php                     
                        printf(__('%s - inserting data info about the booking, which you configured in the content form at Settings Fields page' ,'booking'),'<code>[content]</code>');
                ?>, </p>
                <p class="description"><?php printf(__('%s - inserting ID of booking ' ,'booking'),'<code>[id]</code>');?>, </p>
                <?php if ( ! in_array('bookingtype', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s or %s - inserting the title of the booking resource ' ,'booking'),'<code>[resource_title]</code>','<code>[bookingtype]</code>');?>, </p>
                <?php } if ( ! in_array('cost', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting the cost of  booking ' ,'booking'),'<code>[cost]</code>');?>, </p>
                <?php } ?>
                                                                 

                <p class="description"><?php printf(__('%s - inserting the dates of booking' ,'booking'),'<code>[dates]</code>');?>, </p>
                <?php if ( ! in_array('check_in_date', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting check-in date (first day of reservation),' ,'booking'),'<code>[check_in_date]</code>');?>, </p>
                <?php } if ( ! in_array('check_out_date', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting check-out date (last day of reservation),' ,'booking'),'<code>[check_out_date]</code>');?>, </p>
                <?php } if ( ! in_array('dates_count', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting the number of booking dates ' ,'booking'),'<code>[dates_count]</code>');?>, </p>
                <?php } ?>

                
                <br/>
                <p class="description"><strong><?php _e('HTML tags is accepted.' ,'booking');?></strong></p>
                <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
            </div>
        <?php
    }    
?>