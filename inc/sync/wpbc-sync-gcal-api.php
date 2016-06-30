<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.

*/
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Google Calendar API Sync
 * @category Data Sync
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.06.27
 * @since 5.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

 
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S e t t i n g s     
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////
// Settings - Definition of Sync Menu
////////////////////////////////////////////////////////////////////////////////
function wpbc_define_top_menu_settings_for_sync_gcal_api() {
    
    global $wpbc_settings;
    
    if ( isset($wpbc_settings[50]) ) {
        
        // Add "TAB" section
        $wpbc_settings[50]->add_sub_menu( array(
                                            'selected' => false
                                          , 'title' => __('Google Calendar' ,'booking')  .' ' . __('API' ,'booking')  . ' (Beta)'
                                          , 'description' => __('Customization of synchronization with Google Calendar' ,'booking')
                                          , 'visibility_container' => 'visibility_container_sync_gcal_api'
                                          , 'active_status' => 'booking_is_sync_gcal_api'          
                                          , 'settings_content' => 'wpbc_gcal_api_settings_content'          // Function for show settings content
                                          , 'settings_submit'  => 'wpbc_gcal_api_settings_submit'           // Function for aving submit data
                            ) );
    }
     
    
}

add_bk_action('wpbc_define_top_menu_settings' , 'wpbc_define_top_menu_settings_for_sync_gcal_api' ); 


////////////////////////////////////////////////////////////////////////////////
// Submit Settings
////////////////////////////////////////////////////////////////////////////////
function wpbc_gcal_api_settings_submit() {
  //  debuge('wpbc_gcal_api_settings_submit_api');
    update_bk_option( 'booking_is_sync_gcal_api' , ( ( isset( $_POST['booking_is_sync_gcal_api'] ) ) ? 'On' : 'Off' )  );
}


////////////////////////////////////////////////////////////////////////////////
// Content of Settings
////////////////////////////////////////////////////////////////////////////////
function wpbc_gcal_api_settings_content() {
  //  debuge('wpbc_gcal_api_settings_content_api');
  $booking_is_sync_gcal_api  = get_bk_option('booking_is_sync_gcal_api');
    ?>
       
     <div class='meta-box' style="">                
        <div <?php $my_close_open_win_id = 'bk_settings_gcal_sync_api_general'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php _e('General Settings' ,'booking'); ?></span></h3>          
       <div class="inside" style="margin:0px;">
        
           <table class="visibility_gcal_api_settings_general form-table settings-table">
            <tbody>
                
                <tr>
                    <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                    <td>
                        <fieldset>
                            <label for="booking_is_sync_gcal_api">
                                <input id="booking_is_sync_gcal_api" name="booking_is_sync_gcal_api" type="checkbox" 
                                    <?php if ($booking_is_sync_gcal_api == 'On') echo "checked"; ?>  
                                    value="<?php echo $booking_is_sync_gcal_api; ?>" 
                                    onchange="document.getElementById('booking_is_sync_gcal_api_dublicated').checked=this.checked;"
                                    />
                                <?php _e('Active' ,'booking'); ?>
                            </label>
                        </fieldset>   
                    </td>
                </tr>                
                
                
            </tbody>
        </table>
           
       </div>
      </div>
     </div> 
    
    <?php    
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Activation
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpbc_sync_gcal_api_activate() {
        
    add_bk_option( 'booking_is_sync_gcal_api' , 'Off'  );
}
add_bk_action('wpdev_booking_activation',   'wpbc_sync_gcal_api_activate' );

////////////////////////////////////////////////////////////////////////////////
// Deactivation
////////////////////////////////////////////////////////////////////////////////
function wpbc_sync_gcal_api_deactivate() {
    
    delete_bk_option( 'booking_is_sync_gcal_api');

}
add_bk_action('wpdev_booking_deactivation', 'wpbc_sync_gcal_api_deactivate' );

?>