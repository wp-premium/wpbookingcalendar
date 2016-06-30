<?php
/*
  This is COMMERCIAL SCRIPT
  We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
 */


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S e t t i n g s    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Toolbar tab
function wpdev_bk_payment_show_tab_in_top_settings_pay_cash() {
    ?><a href="javascript:void(0)" onclick="javascript:
                jQuery( '.visibility_container' ).css( 'display', 'none' );
                jQuery( '#visibility_container_pay_cash' ).css( 'display', 'block' );
                jQuery( '.nav-tab' ).removeClass( 'booking-submenu-tab-selected' );
                jQuery( this ).addClass( 'booking-submenu-tab-selected' );"
       rel="tooltip"
       class="tooltip_bottom nav-tab  booking-submenu-tab <?php 
              if ( get_bk_option( 'booking_pay_cash_is_active' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
       original-title="<?php _e( 'Integration of Pay in Cash payment system' ,'booking'); ?>" >
       <?php _e( 'Pay in Cash' ,'booking'); ?>
        <input type="checkbox" <?php if ( get_bk_option( 'booking_pay_cash_is_active' ) == 'On' ) echo ' checked="CHECKED" '; ?>
               name="pay_cash_is_active_dublicated" id="pay_cash_is_active_dublicated"
               onchange="document.getElementById( 'pay_cash_is_active' ).checked = this.checked;" >
    </a>
    <script type="text/javascript">
            jQuery( document ).ready( function() {
                recheck_active_itmes_in_top_menu( 'pay_cash_is_active', 'pay_cash_is_active_dublicated' );
            } );
    </script>
    <?php
}
add_bk_action( 'wpdev_bk_payment_show_tab_in_top_settings', 'wpdev_bk_payment_show_tab_in_top_settings_pay_cash' );


// Settings page for Pay in Cash
function wpdev_bk_payment_show_settings_content_pay_cash() {

    if ( ( isset( $_POST[ 'pay_cash_description' ] ) ) ) {

        update_bk_option( 'booking_pay_cash_is_active', ( (isset( $_POST[ 'pay_cash_is_active' ] )) ? 'On' : 'Off' ) );
        update_bk_option( 'booking_pay_cash_description', $_POST[ 'pay_cash_description' ] );
    }

    $pay_cash_is_active = get_bk_option( 'booking_pay_cash_is_active' );
    $pay_cash_description = get_bk_option( 'booking_pay_cash_description' );
    // Replace <br> to  <br> with  new line
    $pay_cash_description = preg_replace( array( "@(&lt;|<)br/?(&gt;|>)(\r\n)?@" )
                                        , array( "<br/>" )
                                        , $pay_cash_description );
    
    ?>
    <div id="visibility_container_pay_cash" class="visibility_container" style="display:none;">
        <div class='meta-box'>
            <div <?php $my_close_open_win_id = 'bk_settings_costs_pay_cash_payment'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <!--div title="<?php _e( 'Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div-->
                <h3 class='hndle'><span><?php _e( 'Pay in Cash customization' ,'booking'); ?></span></h3>
                <div class="inside" style="margin:0px;">

                    <table class="visibility_pay_cash_account_settings form-table settings-table0">
                        <tbody>

                            <tr>
                                <td colspan="2">                          
                                    <div  class="wpbc-help-message">
                                        <?php printf( __( 'If you accept %scash payment%s, you can write details about it here' ,'booking'), '<b>', '</b>' );
                                        ?>                            
                                    </div>                          
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php _e( 'Pay in Cash active' ,'booking');
                                        ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="pay_cash_is_active" >
                                            <input <?php if ( $pay_cash_is_active == 'On' ) echo "checked"; ?>
                                                value="<?php echo $pay_cash_is_active; ?>" name="pay_cash_is_active" id="pay_cash_is_active" type="checkbox"
                                                onchange="document.getElementById( 'pay_cash_is_active_dublicated' ).checked = this.checked;"
                                                /><?php _e( 'Check this box to use Pay in Cash' ,'booking');
                                        ?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="pay_cash_description" ><?php _e( 'Description' ,'booking'); ?>:</label>
                                </th>
                                <td>
                                    <?php /**/
                                            wp_editor( $pay_cash_description, 
                                               'pay_cash_description',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'pay_cash_description'
                                                   , 'textarea_rows' => 5
                                                   , 'default_editor' => 'html'
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /** ?>                                    
                                    <textarea id="pay_cash_description" name="pay_cash_description"
                                              rows="5" style="width:100%" ><?php echo $pay_cash_description; ?></textarea> <?php /**/ ?>
                                    <p class="description"> <?php _e( 'Payment method description that the customer will see on your payment page.' ,'booking'); ?></p>
                                </td>
                            </tr>

                            <tr>
                                <td></td>
                                <td>
                                <?php 
                                    $skip_shortcodes = array();                                        
                                    wpbc_payment_help_section( $skip_shortcodes );
                                ?>                                                      
                                </td>
                            </tr>                            
                        </tbody>
                    </table>

                    <div class="clear" style="height:10px;"></div>
                    <input class="button-primary button" style="float:right;" type="submit" value="<?php _e( 'Save Changes' ,'booking'); ?>" name="submit_form"/>
                    <div class="clear" style="height:10px;"></div>

                </div>
            </div>
        </div>
    </div>
    <?php
}
add_bk_action( 'wpdev_bk_payment_show_settings_content', 'wpdev_bk_payment_show_settings_content_pay_cash' );



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   P a y m e n t    f o r m    d e f i n i t i o n      //////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function wpdev_bk_define_payment_form_pay_cash( $blank, $booking_id, $summ, $bk_title, $booking_days_count, $booking_type, $bkform, $wp_nonce, $is_deposit ) {
    $output = '';

    $is_show_in_payment_request = false;
//        if (       (get_bk_option( 'booking_authorizenet_is_active' ) != 'On') 
//                && (get_bk_option( 'booking_sage_is_active' ) != 'On')
//                && (get_bk_option( 'booking_ipay88_is_active' ) != 'On')
//                && (isset($_GET['booking_pay']))
//           ) $is_show_in_payment_request = true;

    if ( ( get_bk_option( 'booking_pay_cash_is_active' ) == 'On' ) || ( $is_show_in_payment_request ) ) {

        $pay_cash_description = get_bk_option( 'booking_pay_cash_description' );
        
        // Dates Shortcodes
        $booking_dates_str = get_dates_str( $booking_id );
        
        if ( get_bk_option( 'booking_date_view_type') == 'short' ) 
            $my_dates_4_send = get_dates_short_format( $booking_dates_str );
        else                                                     
            $my_dates_4_send = change_date_format( $booking_dates_str );

        $my_dates4emeil_check_in_out = explode(',', $booking_dates_str );

        $my_check_in_date  = change_date_format( $my_dates4emeil_check_in_out[0] );
        $my_check_out_date = change_date_format( $my_dates4emeil_check_in_out[ count( $my_dates4emeil_check_in_out )-1 ] );
        
        
        // Cost Shortcodes
        // $cost_currency = apply_bk_filter( 'get_currency_info', 'pay_cash' );        
        $summ_show = wpdev_bk_cost_number_format( $summ );

        // Resource title
        $booking_resource_title = '';
        if ( is_array( $bk_title ) && ( count( $bk_title ) > 0 ) && is_object( $bk_title[0] )  ) {
            $booking_resource_title = $bk_title[0]->title;
        }
        
        $booking_form_show = get_form_content(  $bkform,
                                                $booking_type,
                                                '',
                                                array(
                                                    'booking_id'=> $booking_id ,
                                                    'id'=> $booking_id ,
                                                    'dates'=> $my_dates_4_send,
                                                    'check_in_date' => $my_check_in_date,
                                                    'check_out_date' => $my_check_out_date,
                                                    'dates_count' => count( $my_dates4emeil_check_in_out ),
                                                    'cost' => $summ_show, // (isset($res->cost))?$res->cost:'',
                                                    'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $booking_resource_title ),
                                                    'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $booking_resource_title ),
                                                    'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                    'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                    
                                                    )
                                              );
        
        $pay_cash_description =  apply_bk_filter('wpdev_check_for_active_language', $pay_cash_description ); 
        
        $pay_cash_description = str_replace( '[content]', $booking_form_show['content'], $pay_cash_description );

        $pay_cash_description = replace_bk_shortcodes_in_form( $pay_cash_description, $booking_form_show['_all_fields_'], true );
        
        $pay_cash_description = esc_js( $pay_cash_description );  
        $pay_cash_description = html_entity_decode( $pay_cash_description );
        $pay_cash_description = str_replace( "\\n", '', $pay_cash_description );
             
        $output  = '<div style="width:100%;clear:both;margin-top:20px;"></div>';
        $output .= '<div class="pay_cash_div wpbc-payment-form" style="text-align:left;clear:both;">';

        $output .= $pay_cash_description ;

        $output .= '</div>';
    }
    return $output;
}
add_bk_filter( 'wpdev_bk_define_payment_form_pay_cash', 'wpdev_bk_define_payment_form_pay_cash' );


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   D e f i n e    p a y m e n t    s t a t u s e s      //////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// OK
function wpbc_add_payment_status_ok__pay_cash( $payment_status ) {

    // $payment_status = array_merge( $payment_status,  array( 'BankTransfer:OK' )  );

    return $payment_status;
}
add_filter( 'wpbc_add_payment_status_ok', 'wpbc_add_payment_status_ok__pay_cash' );


// Pending
function wpbc_add_payment_status_pending__pay_cash( $payment_status ) {

    // $payment_status = array_merge( $payment_status,  array(  )  );

    return $payment_status;
}
add_filter( 'wpbc_add_payment_status_pending', 'wpbc_add_payment_status_pending__pay_cash' );


// Unknown
function wpbc_add_payment_status_unknown__pay_cash( $payment_status ) {

    // $payment_status = array_merge( $payment_status,  array(  )  );

    return $payment_status;
}
add_filter( 'wpbc_add_payment_status_unknown', 'wpbc_add_payment_status_unknown__pay_cash' );


// Error
function wpbc_add_payment_status_error__pay_cash( $payment_status ) {

    // $payment_status = array_merge( $payment_status,  array( 'BankTransfer:Failed' )  );

    return $payment_status;
}
add_filter( 'wpbc_add_payment_status_error', 'wpbc_add_payment_status_error__pay_cash' );


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   R E S P O N S E     ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////    
// None
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N    ///////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Activate
function wpdev_bk_payment_activate_system_pay_cash() {

    add_bk_option( 'booking_pay_cash_is_active', 'Off' );
    add_bk_option( 'booking_pay_cash_description', 
                    sprintf( __( 'Dear %sPay in cash %s for your booking %s on check in %sFor reference your booking ID: %s' ,'booking'),  
                                '[name]<br/>' ,
                                '<strong>$[cost]</strong>',
                                '<strong>[resource_title]</strong>',
                                '<strong>[check_in_date]</strong>.<br/>', 
                                '<strong>[id]</strong>'
                          )
                    // __( 'Pay with cash on check in.' ,'booking') 
                 );
}
add_bk_action( 'wpdev_bk_payment_activate_system', 'wpdev_bk_payment_activate_system_pay_cash' );


// Activate
function wpdev_bk_payment_deactivate_system_pay_cash() {

    delete_bk_option( 'booking_pay_cash_is_active' );
    delete_bk_option( 'booking_pay_cash_description' );
}
add_bk_action( 'wpdev_bk_payment_deactivate_system', 'wpdev_bk_payment_deactivate_system_pay_cash' );
?>