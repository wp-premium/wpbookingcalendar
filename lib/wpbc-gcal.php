<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Google Calendar Import
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
//  A J A X
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function wpbc_import_gcal_events(){ global $wpdb;
/*   $_POST
 * 
 *   [action] => WPBC_IMPORT_GCAL_EVENTS
            [user_id] => 1
            [booking_gcal_events_from] => Array
                (
                    [0] => date
                    [1] => 2014-07-01
                    [2] => hour
                )

            [booking_gcal_events_until] => Array
                (
                    [0] => month-start
                    [1] => 
                    [2] => minute
                )

            [booking_gcal_events_max] => 5
            [wpbc_booking_resource] => 
 * 
 */       
    $user_id = (int) $_POST['user_id'];

    
    $wpbc_Google_Calendar = new WPBC_Google_Calendar();
    
    $wpbc_Google_Calendar->set_timezone( get_bk_option('booking_gcal_timezone') );
    
    $wpbc_Google_Calendar->set_events_from_with_array( $_POST['booking_gcal_events_from'] ); 
    
    $wpbc_Google_Calendar->set_events_until_with_array( $_POST['booking_gcal_events_until'] ); 
    
    $wpbc_Google_Calendar->set_events_max(   $_POST['booking_gcal_events_max']  );
        

    if ( ( isset($_POST['wpbc_booking_resource']) ) && ( empty($_POST['wpbc_booking_resource']) ) ) {
        
        $wpbc_Google_Calendar->setUrl( get_bk_option( 'booking_gcal_feed') );
        $import_result = $wpbc_Google_Calendar->run();
        
    } else {
        
        if ( $_POST['wpbc_booking_resource'] != 'all' ) {                             // One resource
            
            $wpbc_booking_resource_id = intval( $_POST['wpbc_booking_resource'] );    
            
            $wpbc_Google_Calendar->setResource($wpbc_booking_resource_id);
            
            $wpbc_booking_resource_feed = get_booking_resource_attr( $wpbc_booking_resource_id );      
            $wpbc_booking_resource_feed = $wpbc_booking_resource_feed->import;
            $wpbc_Google_Calendar->setUrl($wpbc_booking_resource_feed);
            
            $import_result = $wpbc_Google_Calendar->run();
        } else {                                                                // All  resources
            
            
            $where = '';                                                        // Where for the different situation: BL and MU
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') 
                $where = ' WHERE ' . $where;
            $my_sql = "SELECT booking_type_id, import FROM {$wpdb->prefix}bookingtypes {$where}";

            $types_list = $wpdb->get_results( $my_sql );

            foreach ($types_list as $wpbc_booking_resource) {
                $wpbc_booking_resource_id = $wpbc_booking_resource->booking_type_id;
                $wpbc_booking_resource_feed = $wpbc_booking_resource->import;
                if ( (! empty($wpbc_booking_resource_feed) ) && ($wpbc_booking_resource_feed != NULL ) && ( $wpbc_booking_resource_feed != '/' ) ) {
                    
                    $wpbc_Google_Calendar->setUrl($wpbc_booking_resource_feed);
                    $wpbc_Google_Calendar->setResource($wpbc_booking_resource_id);
                    $import_result = $wpbc_Google_Calendar->run();                
                }
            }            
        }        
    }
    if ( (isset($import_result)) && ( $import_result!= false ) )
        $wpbc_Google_Calendar->show_message( __('Done' ,'booking') );
    else $wpbc_Google_Calendar->show_message( __('Imported 0 events.' ,'booking') );
    ?> <script type="text/javascript">        
            jQuery('#ajax_message').animate({opacity:1},5000).fadeOut(1000);
       </script> <?php        
}
add_bk_action('wpbc_import_gcal_events' , 'wpbc_import_gcal_events' ); 


function wpbc_silent_import_all_events() {

    global $wpdb;
//    debuge(1);
    $wpbc_Google_Calendar = new WPBC_Google_Calendar();
    
    $wpbc_Google_Calendar->setSilent();
            
    $wpbc_Google_Calendar->set_timezone( get_bk_option('booking_gcal_timezone') );
    
    $wpbc_Google_Calendar->set_events_max( get_bk_option( 'booking_gcal_events_max') );
    
    $wpbc_Google_Calendar->set_events_from_with_array( 
                                                        array(  get_bk_option( 'booking_gcal_events_from')
                                                                , get_bk_option( 'booking_gcal_events_from_offset' )
                                                                , get_bk_option( 'booking_gcal_events_from_offset_type' ) ) 
                                                    ); 
    
    $wpbc_Google_Calendar->set_events_until_with_array( 
                                                        array(  get_bk_option( 'booking_gcal_events_until')
                                                                , get_bk_option( 'booking_gcal_events_until_offset' )
                                                                , get_bk_option( 'booking_gcal_events_until_offset_type' ) ) 
                                                    );
    
    if ( ! class_exists('wpdev_bk_personal') ) { 
        
        $wpbc_Google_Calendar->setUrl( get_bk_option( 'booking_gcal_feed') );
        $import_result = $wpbc_Google_Calendar->run();
        
    } else {
        
        $types_list = $wpdb->get_results( "SELECT booking_type_id, import FROM {$wpdb->prefix}bookingtypes" );

        foreach ($types_list as $wpbc_booking_resource) {
            $wpbc_booking_resource_id = $wpbc_booking_resource->booking_type_id;
            $wpbc_booking_resource_feed = $wpbc_booking_resource->import;
            if ( (! empty($wpbc_booking_resource_feed) ) && ($wpbc_booking_resource_feed != NULL ) && ( $wpbc_booking_resource_feed != '/' ) ) {

                $wpbc_Google_Calendar->setUrl($wpbc_booking_resource_feed);
                $wpbc_Google_Calendar->setResource($wpbc_booking_resource_id);
                $import_result = $wpbc_Google_Calendar->run();                
            }
        }                    
    }
//    debuge(2);
}
add_bk_action('wpbc_silent_import_all_events' , 'wpbc_silent_import_all_events' ); 

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S e t t i n g s     
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Settings - Definition of Sync Menu
function wpbc_define_top_menu_settings_for_sync_gcal() {
    
    global $wpbc_settings;
    
    // Create New Menu Settings Page
    $wpbc_settings[50] = new WPBC_Settings( array( 'term'        => 'sync' 
                                                 , 'title'       => __('Import' ,'booking')
                                                 , 'description' => __('Synchronization Bookings Settings' ,'booking')
                                                 , 'icon'        => 'icon-download'
                                                 , 'submit_form' => 'post_settings_sync'
                                                 , 'submit_in_toolbar' => true
        
                                                 // , 'settings_content' => 'wpbc_gcal_settings_content_general_if_no_tabs'
                                                 // , 'settings_submit'  => 'wpbc_gcal_settings_submit_general'

                                                  ) );

    // Add "TAB" section
    $wpbc_settings[50]->add_sub_menu( array(
                                            'selected' => true
                                          , 'title' => __('Google Calendar' ,'booking')  .'  - ' . __('Events Import' ,'booking')   
                                          , 'description' => __('Customization of synchronization with Google Calendar' ,'booking')
                                          , 'visibility_container' => 'visibility_container_sync_google_calendar'
                                         // , 'active_status' => 'booking_gcal_is_active'          
                                          , 'settings_content' => 'wpbc_gcal_settings_content'          // Function for show settings content
                                          , 'settings_submit'  => 'wpbc_gcal_settings_submit'           // Function for aving submit data
                                          ) );
    
    // Action  for showing Search  booking resources form
    if ( (isset($_GET['tab'])) && ($_GET['tab'] == 'sync') )  
        make_bk_action('wpbc_find_booking_resource_form'); 
}

add_bk_action('wpbc_define_top_menu_settings' , 'wpbc_define_top_menu_settings_for_sync_gcal' ); 


// HOOK: on submit of settings form
function wpbc_gcal_settings_submit() {

    
    if (! class_exists('wpdev_bk_personal')) 
        update_bk_option( 'booking_gcal_feed',  $_POST['booking_gcal_feed']  );    //update_bk_option( 'booking_gcal_feed', wpbc_set_relative_url( $_POST['booking_gcal_feed'] ) );    
    
    update_bk_option( 'booking_gcal_events_from', $_POST['booking_gcal_events_from'] );
    if( $_POST['booking_gcal_events_from'] != 'date' ) {
        $_POST['booking_gcal_events_from_offset'] = intval($_POST['booking_gcal_events_from_offset']);
        if (empty($_POST['booking_gcal_events_from_offset']))
            $_POST['booking_gcal_events_from_offset'] = "-0";
        update_bk_option( 'booking_gcal_events_from_offset', ($_POST['booking_gcal_events_from_offset']) );         
    } else {   
        update_bk_option( 'booking_gcal_events_from_offset', $_POST['booking_gcal_events_from_offset'] );
    }
    update_bk_option( 'booking_gcal_events_from_offset_type', $_POST['booking_gcal_events_from_offset_type'] );

    update_bk_option( 'booking_gcal_events_until', $_POST['booking_gcal_events_until'] );
    if( $_POST['booking_gcal_events_until'] != 'date' ) { 
        $_POST['booking_gcal_events_until_offset'] = intval($_POST['booking_gcal_events_until_offset']);
        if (empty($_POST['booking_gcal_events_from_offset']))
            $_POST['booking_gcal_events_from_offset'] = "-0";        
        update_bk_option( 'booking_gcal_events_until_offset', $_POST['booking_gcal_events_until_offset'] );         
    } else {
        update_bk_option( 'booking_gcal_events_until_offset', $_POST['booking_gcal_events_until_offset'] );
    }
    update_bk_option( 'booking_gcal_events_until_offset_type', $_POST['booking_gcal_events_until_offset_type'] );

    
    update_bk_option( 'booking_gcal_events_max', $_POST['booking_gcal_events_max'] );
    update_bk_option( 'booking_gcal_api_key', $_POST['booking_gcal_api_key'] );
    
    update_bk_option( 'booking_gcal_timezone', $_POST['booking_gcal_timezone'] );
    
    // update_bk_option( 'booking_gcal_is_send_email' , ( ( isset( $_POST['booking_gcal_is_send_email'] ) ) ? 'On' : 'Off' )  );

    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin'); 
    if ( $is_can ) {    
        update_bk_option( 'booking_gcal_auto_import_is_active' , ( ( isset( $_POST['booking_gcal_auto_import_is_active'] ) ) ? 'On' : 'Off' )  );
        
        // Update Cron //
        if ( isset( $_POST['booking_gcal_auto_import_is_active'] ) ) {
            
            update_bk_option( 'booking_gcal_auto_import_time', intval( $_POST['booking_gcal_auto_import_time'] ) );
            
            // add
            wpbookingcalendar()->cron->update( 'wpbc_import_gcal'
                                        , array(     
                                               'action' => array( 'wpbc_silent_import_all_events' )                 // Action and parameters
                                             , 'start_time' => time()                                               // Now
                                             , 'recurrence' => intval( $_POST['booking_gcal_auto_import_time'] )    // Set  time in Hours
                                                ) 
                                             );

        } else {
            // delete
            wpbookingcalendar()->cron->delete( 'wpbc_import_gcal' );
        }
    }
    
    
    $event_fields = wpbc_gcal_get_events_fields_parameters();        
    $event_fields_array = array();
    foreach ($event_fields as $event_fields_key => $event_fields_value) {

        if ( isset($_POST[ "booking_gcal_events_form_fields_" . $event_fields_key ]) ) {
            $event_fields_array[ $event_fields_key ] = $_POST[ "booking_gcal_events_form_fields_" . $event_fields_key ];
        } else {
            $event_fields_array[ $event_fields_key ] = '';
        }
    }
//debuge($event_fields_array, serialize($event_fields_array));    
    update_bk_option( 'booking_gcal_events_form_fields', serialize($event_fields_array) );

    
    // Hook 
    make_bk_action('wpbc_gcal_settings_content_submit_booking_resources_table');     
}


// Content of Settings page    
function wpbc_gcal_settings_content() {

    
    $booking_gcal_feed = get_bk_option( 'booking_gcal_feed');
    
    $booking_gcal_events_from = get_bk_option( 'booking_gcal_events_from');
    $booking_gcal_events_from_offset = get_bk_option( 'booking_gcal_events_from_offset' );
    $booking_gcal_events_from_offset_type = get_bk_option( 'booking_gcal_events_from_offset_type' );
   
    $booking_gcal_events_until = get_bk_option( 'booking_gcal_events_until');
    $booking_gcal_events_until_offset = get_bk_option( 'booking_gcal_events_until_offset' );
    $booking_gcal_events_until_offset_type = get_bk_option( 'booking_gcal_events_until_offset_type' );
    
    $booking_gcal_events_max = get_bk_option( 'booking_gcal_events_max');
    $booking_gcal_api_key = get_bk_option( 'booking_gcal_api_key');
    
    $booking_gcal_timezone = get_bk_option( 'booking_gcal_timezone');
    $booking_gcal_is_send_email = get_bk_option( 'booking_gcal_is_send_email');
    
    $booking_gcal_auto_import_is_active = get_bk_option( 'booking_gcal_auto_import_is_active'); 
    $booking_gcal_auto_import_time = get_bk_option( 'booking_gcal_auto_import_time'); 
    
    $booking_gcal_events_form_fields = get_bk_option( 'booking_gcal_events_form_fields'); 
    if ( is_serialized( $booking_gcal_events_form_fields ) )   
        $booking_gcal_events_form_fields = unserialize( $booking_gcal_events_form_fields );    
    
    ?>
       
     <div class='meta-box' style="">                
        <div <?php $my_close_open_win_id = 'bk_settings_gcal_sync_form_fields'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php _e('Assign events fields to specific booking form field' ,'booking'); ?></span></h3>          
       <div class="inside" style="margin:0px;">
        
           <table class="visibility_gcal_feeds_settings form-table settings-table">
            <tbody>
                <?php 
                    wpbc_gcal_settings_content_form_fields($booking_gcal_events_form_fields);
                ?>
            </tbody>
        </table>
           
       </div>
      </div>
     </div> 
           
     <?php $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin'); 
     if ( $is_can ) { ?>  
       
     <div class='meta-box' style="">                
        <div <?php $my_close_open_win_id = 'bk_settings_gcal_sync_auto_import'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php _e('Auto import events' ,'booking'); ?></span></h3>          
       <div class="inside" style="margin:0px;">
        
           <table class="visibility_gcal_feeds_settings form-table settings-table">
            <tbody>
                <?php 
                    wpbc_gcal_settings_content_field_auto_import($booking_gcal_auto_import_is_active, $booking_gcal_auto_import_time); 
                ?>
            </tbody>
        </table>
           
       </div>
      </div>
     </div> 
     <?php } ?>       
       
     <div class='meta-box' style="">                
        <div <?php $my_close_open_win_id = 'bk_settings_gcal_sync'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php _e('Google Calendar - General Settings' ,'booking'); ?></span></h3>          
       <div class="inside" style="margin:0px;">
           
        <table class="visibility_gcal_feeds_settings form-table settings-table">
            <tbody>
                <?php 
                    wpbc_gcal_settings_content_api_key( $booking_gcal_api_key ); 
                
                    wpbc_gcal_settings_content_field_timezone($booking_gcal_timezone);
                    
                    
                    /*
                ?>
                <tr valign="top">
                    <th scope="row"><?php _e('Send emails' ,'booking'); ?>:</th>
                    <td>
                        <fieldset>
                            <label for="booking_gcal_is_send_email" >                                                                                                
                                <input <?php if ($booking_gcal_is_send_email == 'On') echo "checked"; ?>  value="<?php echo $booking_gcal_is_send_email; ?>" name="booking_gcal_is_send_email" id="booking_gcal_is_send_email" type="checkbox"   />
                                <?php printf(__('Check this box to %ssend emails about creation new bookings%s after events requesting.' ,'booking'),'<b>','</b>');?>
                            </label>
                        </fieldset>                                                                                                        
                    </td>
                </tr>
                <?php /**/ ?>
                <tr valign="top">
                    <td colspan="2"><hr/>
                        <p class="description" style="text-align:right;"><strong><?php _e('Default settings for retrieving events' ,'booking'); ?></strong></p>
                    </td>
                </tr>

                <?php    
                wpbc_gcal_settings_content_field_max_feeds( $booking_gcal_events_max ); 
                wpbc_gcal_settings_content_field_from( $booking_gcal_events_from, $booking_gcal_events_from_offset , $booking_gcal_events_from_offset_type ); 
                wpbc_gcal_settings_content_field_until( $booking_gcal_events_until, $booking_gcal_events_until_offset , $booking_gcal_events_until_offset_type );                       
                ?>
                
                <tr valign="top"><td colspan="2" style=""><hr/></td></tr>
                
                <?php if ( ! class_exists('wpdev_bk_personal') ) { ?>
                <tr valign="top">
                    <th scope="row"><label for="booking_gcal_feed" ><?php _e('Google Calendar ID' ,'booking'); ?>:</label></th>
                    <td class="wpdevbk">
                        <div class="control-group">
                        <div class="inline controls">
<!--                            <div class="input-prepend">-->
<!--                                <span class="add-on" style="float:left!important;box-sizing: content-box;font-size: 14px;padding:9px 0;" >&nbsp;https://www.google.com&nbsp;</span>-->
                                <input style="box-sizing: content-box;font-size: 14px;width: 50%;" 
                                       id="booking_gcal_feed" name="booking_gcal_feed" 
                                       type="text" value="<?php echo $booking_gcal_feed; ?>"

                                       />
<!--                                       placeholder="calendar/feeds/your-email@group.calendar.google.com/public/basic"-->                                
<!--                            </div>-->
                        </div>                   
                        </div>
                        <p class="description"><?php ?></p>
                    </td>
                </tr>  
                <?php } ?>               
                
            </tbody>
        </table>

       </div>
      </div>
     </div> 
       
       
     <div class='meta-box' style="">                
        <div <?php $my_close_open_win_id = 'bk_settings_gcal_sync_help'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php _e('Google Calendar - Help Info' ,'booking'); ?></span></h3>          
       <div class="inside" style="margin:0px;">
        
            <div class="wpbc-help-message" style="margin-top:10px;">                
                <table class="resource_table booking_table" style="border:none !important;background: none !important;box-shadow: 0 0 0 #fff !important;">
                    <tbody>
                    <tr>
                        <td style="vertical-align: top;border:none;">
                            
                <h4>01. <?php _e('To get Google Calendar API key please follow this instruction' ,'booking');?>:</h4>                                
                <ol style="list-style-type: decimal !important;margin-left: 20px;">
                    <li><?php printf(__('Go to Google Developer Console: %s.' ,'booking'),'<a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>');?></li>
                    <li><?php printf(__('Give your project a name and click "Create".' ,'booking'));?></li>
                    <li><?php printf(__('In the sidebar click on "APIs & auth".' ,'booking'));?></li>
                    <li><?php printf(__('Click APIs and make sure "Calendar API" is set to ON.' ,'booking'));?></li>
                    <li><?php printf(__('Now click on "Credentials" in the sidebar.' ,'booking'));?></li>
                    <li><?php printf(__('Under the section "Public API access" click the button "Create new Key".' ,'booking'));?></li>
                    <li><?php printf(__('On the popup click the button "Server Key" and click "Create".' ,'booking'));?></li>
                    <li><?php printf(__('You will now see a table loaded with the top item being the API Key. Copy this and paste it into %sGoogle API Key%s field at this page.' ,'booking'),'<strong>','</strong>');?></li>
                </ol>                    
                            
                        </td>
                        <td style="vertical-align: top;border:none;">
                            
                <h4>02. <?php _e('Set Your Calendar to Public' ,'booking');?>:</h4>                                
                <ol style="list-style-type: decimal !important;margin-left: 20px;">
                    <li><?php printf(__('Navigate to your Google calendars.' ,'booking'),'<a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>');?></li>
                    <li><?php printf(__('Open the settings for the calendar.' ,'booking'));?></li>
                    <li><?php printf(__('Click the "Share this Calendar" link.' ,'booking'));?></li>
                    <li><?php printf(__('Click the checkbox to make calendar public. Do not check the other option.' ,'booking'));?></li>
                </ol>                    

                <h4>03. <?php _e('Find Your Calendar ID' ,'booking');?>:</h4>                                
                <ol style="list-style-type: decimal !important;margin-left: 20px;">
                    <li><?php printf(__('Navigate to your Google calendars.' ,'booking'),'<a href="https://console.developers.google.com" target="_blank">https://console.developers.google.com</a>');?></li>
                    <li><?php printf(__('Open the settings for the calendar.' ,'booking'));?></li>
                    <li><?php printf(__('Now copy the Calendar ID to use in the plugin settings in your WordPress admin. Make sure to %suse the Calendar ID only, not the entire XML feed URL%s.' ,'booking'),'<strong>','</strong>');?></li>
                </ol>                    
                            
                        </td>
                    </tr>
                    </tbody>
                </table>


            </div>  
           
       </div>
      </div>
     </div> 
       

    <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>   
    <?php

    make_bk_action('wpbc_gcal_settings_content_show_booking_resources_table');     
}


    function wpbc_gcal_settings_content_form_fields( $booking_gcal_events_form_fields ){


        $event_fields = wpbc_gcal_get_events_fields_parameters();
        
        foreach ($event_fields as $event_fields_key => $event_fields_value) {

            if ( isset ($booking_gcal_events_form_fields[ $event_fields_key ] ) ) 
                 $saved_option = $booking_gcal_events_form_fields[ $event_fields_key ];
            else $saved_option = false;
            
          ?><tr valign="top">
                <th scope="row">
                    <label for="booking_gcal_events_form_fields_<?php echo $event_fields_key; ?>" ><?php echo $event_fields_value; ?>:</label>
                </th>
                <td>
<?php
//$booking_forms = apply_bk_filter('wpbc_get_fields_list_in_booking_form');
//debuge($booking_forms); die;
?>
                    <select id="booking_gcal_events_form_fields_<?php echo $event_fields_key; ?>" 
                            name="booking_gcal_events_form_fields_<?php echo $event_fields_key; ?>">
                        <?php 
                        
                        
                        
                            // Get here values for the selectbox        
                            $selectbox_options = '';

                            if (  class_exists('wpdev_bk_personal') ) {

                                if ( $saved_option == 'text^' )
                                     $is_selected = " selected='SELECTED' ";
                                else $is_selected = '';

                                $selectbox_options .= "<option value='text^' {$is_selected}>" ;        
                                $selectbox_options .= __('None' ,'booking');
                                $selectbox_options .= "</option>" ;
                                
                                $booking_forms = apply_bk_filter('wpbc_get_fields_list_in_booking_form');
                                foreach ($booking_forms as $booking_form_fields) {

                                    $selectbox_options .= "<optgroup label='".$booking_form_fields['name']."'>";
                                    for ($i = 0; $i < $booking_form_fields['num']; $i++) {
                                        if ( $saved_option == trim($booking_form_fields['listing']['fields_type'][$i]). '^' .trim($booking_form_fields['listing']['fields'][$i]) )
                                             $is_selected = " selected='SELECTED' ";
                                        else $is_selected = '';
//                                        $selectbox_options .= "<option value='" .$booking_form_fields['listing'][2][$i] ."' {$is_selected}>" ;        
//                                        $selectbox_options .= ucfirst($booking_form_fields['listing'][2][$i]);
//                                        $selectbox_options .= "</option>" ;
                                    $selectbox_options .= "<option value='" .trim($booking_form_fields['listing']['fields_type'][$i]). '^' .trim($booking_form_fields['listing']['fields'][$i]) ."' {$is_selected}>" ;        
                                    $selectbox_options .= ucfirst(trim($booking_form_fields['listing']['labels'][$i]));
                                    $selectbox_options .= "</option>" ;
                                        
                                    } 
                                    $selectbox_options .= "</optgroup>";
                                }
                            } else {
                                $booking_form_fields_free =  wpbc_get_fields_list_in_booking_form_free();

                                for ($i = 0; $i < $booking_form_fields_free['num']; $i++) {
                                        if ( $saved_option == trim($booking_form_fields_free['listing']['fields_type'][$i]). '^' .trim($booking_form_fields_free['listing']['fields'][$i]) )
                                             $is_selected = " selected='SELECTED' ";
                                        else $is_selected = '';

                                    $selectbox_options .= "<option value='" .trim($booking_form_fields_free['listing']['fields_type'][$i]). '^' .trim($booking_form_fields_free['listing']['fields'][$i]) ."' {$is_selected}>" ;        
                                    $selectbox_options .= ucfirst(trim($booking_form_fields_free['listing']['labels'][$i]));
                                    $selectbox_options .= "</option>" ;
                                }    
                            }
                        
                        
                        
                        
                        echo $selectbox_options; ?>
                    </select>
                    <span class="description">
                    <?php printf(__('Select field for assigning to %sevent property%s' ,'booking'),'<b>','</b>');?>
                    </span>
                </td>
            </tr>
            <?php    
        }  
    }

        function wpbc_gcal_get_events_fields_parameters(){
            return array(
                        'title' => __('Event Title' ,'booking')
                        , 'description' => __('Event Description (optional field)' ,'booking')
                        , 'where' => __('Location' ,'booking')
                    );
        }
    
    function wpbc_gcal_settings_content_field_from( $booking_gcal_events_from, $booking_gcal_events_from_offset = '', $booking_gcal_events_from_offset_type = '' ) {
        if ($booking_gcal_events_from == "date") {
            echo '<style type="text/css"> .booking_gcal_events_from .wpbc_offset_value { display:none; } </style>';
        } else {
            echo '<style type="text/css"> .booking_gcal_events_from .wpbc_offset_datetime { display:none; } </style>';            
        }        
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_gcal_events_from" ><?php _e('From' ,'booking'); ?>:</label></th>
            <td class="booking_gcal_events_from">                        
                <select id="booking_gcal_events_from" name="booking_gcal_events_from"
                        onchange="javascript: if(this.value=='date') {
                            jQuery('.booking_gcal_events_from .wpbc_offset_value').hide();
                            jQuery('.booking_gcal_events_from .wpbc_offset_datetime').show();
                        } else {
                            jQuery('.booking_gcal_events_from .wpbc_offset_value').show();
                            jQuery('.booking_gcal_events_from .wpbc_offset_datetime').hide();                               
                        }
                        jQuery('#booking_gcal_events_from_offset').val('');" >                        
                    <?php 
                    $wpbc_options = array(
                                            "now" => __('Now' ,'booking')
                                          , "today" => __('00:00 today' ,'booking')
                                          , "week" => __('Start of current week' ,'booking')
                                          , "month-start" => __('Start of current month' ,'booking')
                                          , "month-end" => __('End of current month' ,'booking')
                                          , "any" => __('The start of time' ,'booking')
                                          , "date" => __('Specific date / time' ,'booking')
                                    );
                    foreach ($wpbc_options as $key => $value) {
                        ?><option <?php if( $booking_gcal_events_from == $key ) echo "selected"; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                    }
                    ?>
                </select>
                <span class="description"><?php _e('Select option, when to start retrieving events.' ,'booking');?></span>                
                <div class="booking_gcal_events_from_offset" style="margin:10px 0 0;">
                    <label for="booking_gcal_events_from_offset"> <span class="wpbc_offset_value"><?php _e('Offset' ,'booking'); ?></span><span class="wpbc_offset_datetime" ><?php _e('Enter date / time' ,'booking'); ?></span>: </label>
                    <input type="text"  id="booking_gcal_events_from_offset" name="booking_gcal_events_from_offset" value="<?php echo $booking_gcal_events_from_offset; ?>" style="width:100px;text-align: right;" />
                    <span class="wpbc_offset_value">
                        <select id="booking_gcal_events_from_offset_type" name="booking_gcal_events_from_offset_type" style="margin-top: -2px;width: 99px;">
                            <?php 
                            $wpbc_options = array(
                                                    "second" => __('seconds' ,'booking')
                                                  , "minute" => __('minutes' ,'booking')
                                                  , "hour" => __('hours' ,'booking')
                                                  , "day" => __('days' ,'booking')
                                            );
                            foreach ($wpbc_options as $key => $value) {
                                ?><option <?php if( $booking_gcal_events_from_offset_type == $key ) echo "selected"; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                            }
                            ?>
                        </select>
                        <span class="description"><?php _e('You can specify an additional offset from you chosen start point. The offset can be negative.' ,'booking');?></span>
                    </span>
                    <span class="wpbc_offset_datetime">
                        <em><?php printf(__('Type your date in format %s. Example: %s' ,'booking'),'Y-m-d','2014-08-01'); ?></em>
                    </span>
                </div>
            </td>
        </tr>
        <?php
    }

    
    function wpbc_gcal_settings_content_field_until( $booking_gcal_events_until, $booking_gcal_events_until_offset = '', $booking_gcal_events_until_offset_type = '' ) {  
        if ($booking_gcal_events_until == "date") {
            echo '<style type="text/css"> .booking_gcal_events_until .wpbc_offset_value { display:none; } </style>';
        } else {
            echo '<style type="text/css"> .booking_gcal_events_until .wpbc_offset_datetime { display:none; } </style>';            
        }
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_gcal_events_until" ><?php _e('Until' ,'booking'); ?>:</label></th>
            <td class="booking_gcal_events_until">                                
                <select id="booking_gcal_events_until" name="booking_gcal_events_until"
                        onchange="javascript: if(this.value=='date') {
                            jQuery('.booking_gcal_events_until .wpbc_offset_value').hide();
                            jQuery('.booking_gcal_events_until .wpbc_offset_datetime').show();
                        } else {
                            jQuery('.booking_gcal_events_until .wpbc_offset_value').show();
                            jQuery('.booking_gcal_events_until .wpbc_offset_datetime').hide();                            
                        }
                        jQuery('#booking_gcal_events_until_offset').val('');" >
                    <?php 
                    $wpbc_options = array(
                                            "now" => __('Now' ,'booking')
                                          , "today" => __('00:00 today' ,'booking')
                                          , "week" => __('Start of current week' ,'booking')
                                          , "month-start" => __('Start of current month' ,'booking')
                                          , "month-end" => __('End of current month' ,'booking')
                                          , "any" => __('The end of time' ,'booking')
                                          , "date" => __('Specific date / time' ,'booking')
                                    );
                    foreach ($wpbc_options as $key => $value) {
                        ?><option <?php if( $booking_gcal_events_until == $key ) echo "selected"; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                    }
                    ?>
                </select>
                <span class="description"><?php _e('Select option, when to stop retrieving events.' ,'booking');?></span>
                <div class="booking_gcal_events_until_offset" style="margin:10px 0 0;">
                    <label for="booking_gcal_events_until_offset" > <span class="wpbc_offset_value"><?php _e('Offset' ,'booking'); ?></span><span class="wpbc_offset_datetime" ><?php _e('Enter date / time' ,'booking'); ?></span>: </label>
                    <input type="text" id="booking_gcal_events_until_offset" name="booking_gcal_events_until_offset" value="<?php echo $booking_gcal_events_until_offset; ?>" style="width:100px;text-align: right;" />
                    <span class="wpbc_offset_value">
                        <select id="booking_gcal_events_until_offset_type" name="booking_gcal_events_until_offset_type" style="margin-top: -2px;width: 99px;">
                            <?php 
                            $wpbc_options = array(
                                                    "second" => __('seconds' ,'booking')
                                                  , "minute" => __('minutes' ,'booking')
                                                  , "hour" => __('hours' ,'booking')
                                                  , "day" => __('days' ,'booking')
                                            );
                            foreach ($wpbc_options as $key => $value) {
                                ?><option <?php if( $booking_gcal_events_until_offset_type == $key ) echo "selected"; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                            }
                            ?>
                        </select>
                        <span class="description"><?php _e('You can specify an additional offset from you chosen end point. The offset can be negative.' ,'booking');?></span>
                    </span>
                    <span class="wpbc_offset_datetime">
                        <em><?php  printf(__('Type your date in format %s. Example: %s' ,'booking'),'Y-m-d','2014-08-30'); ?></em>
                    </span>
                    
                </div>
            </td>
        </tr>                        
        <?php
    }

    
    function wpbc_gcal_settings_content_field_max_feeds($booking_gcal_events_max) {
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_gcal_events_max" ><?php _e('Maximum number' ,'booking'); ?>:</label></th>
            <td><input id="booking_gcal_events_max"  name="booking_gcal_events_max" class="regular-text" type="text" value="<?php echo $booking_gcal_events_max; ?>" />
                <span class="description"><?php 
                    _e('You can specify the maximum number of events to import during one session.' ,'booking');
              ?></span>
            </td>
        </tr>                
        <?php
    }
    
    
    function wpbc_gcal_settings_content_api_key($booking_gcal_api_key) {
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_gcal_api_key" ><?php _e('Google API Key' ,'booking'); ?>:</label></th>
            <td><input id="booking_gcal_api_key"  name="booking_gcal_api_key" class="regular-text" type="text" style="width:350px;" value="<?php echo $booking_gcal_api_key; ?>" />
                <span class="description"><?php _e('Please enter your Google API key. This field required to import events.' ,'booking'); ?></span>  
                <p class="description"><?php 
                    printf(__('You can check in this %sinstruction how to generate and use your Google API key%s.' ,'booking')
                            , '<a href="http://wpbookingcalendar.com/faq/import-gc-events/">'
                            ,'</a>'
                      );
              ?></p>
            </td>
        </tr>                
        <?php
    }
    
    
    function wpbc_gcal_settings_content_field_timezone($booking_gcal_timezone) {
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_gcal_timezone" ><?php _e('Timezone' ,'booking'); ?>:</label></th>
            <td>                                
                <select id="booking_gcal_timezone" name="booking_gcal_timezone">
                    <?php 
                    $wpbc_options = array(
                                            "" => __('Default' ,'booking')
                                    );
                    foreach ($wpbc_options as $key => $value) {
                        ?><option <?php if( $booking_gcal_timezone == $key ) echo "selected"; ?> value="<?php echo $key; ?>"><?php echo $value; ?></option><?php
                    }
                    
                    
                    global $wpbc_booking_region_cities_list;                    // structure: $wpbc_booking_region_cities_list["Pacific"]["Fiji"] = "Fiji";
                    
                    foreach ($wpbc_booking_region_cities_list as $region => $region_cities) {
                        
                        echo '<optgroup label="'. $region .'">';
                        
                        foreach ($region_cities as $city_key => $city_title) {
                            
                            if( $booking_gcal_timezone == $region .'/'. $city_key ) 
                                $is_selected = 'selected'; 
                            else 
                                $is_selected = '';
                            
                            echo '<option '.$is_selected.' value="'. $region .'/'. $city_key .'">' . $city_title . '</option>';
                            
                        }
                        echo '</optgroup>';
                    }
                    
                    
                    ?>
                </select>
                <span class="description"><?php _e('Select a city in your required timezone, if you are having problems with dates and times.' ,'booking');?></span>
            </td>
        </tr>                        
        <?php
    }
    
    
    function wpbc_gcal_settings_content_field_auto_import( $booking_gcal_auto_import_is_active, $booking_gcal_auto_import_time) {
        ?>
        <tr valign="top">
            <th scope="row"><?php _e('Activate auto import' ,'booking'); ?>:</th>
            <td>
                <fieldset>
                    <label for="booking_gcal_auto_import_is_active" >                                                
                        <input <?php if ($booking_gcal_auto_import_is_active == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_gcal_auto_import_is_active; ?>" name="booking_gcal_auto_import_is_active" id="booking_gcal_auto_import_is_active" type="checkbox" 
                            onchange="javascript:  document.getElementById('booking_gcal_auto_import_time').disabled=! this.checked; "   />
                        <?php printf(__('Check this box to %sactivate%s auto import events and creation bookings from them' ,'booking'),'<b>','</b>');?>
                    </label>
                </fieldset>                                                
            </td>
        </tr>
        <tr valign="top">
          <th scope="row">
            <label for="booking_gcal_auto_import_time" ><?php _e('Import events every' ,'booking'); ?>:</label>
          </th>
          <td>
              <select id="booking_gcal_auto_import_time" name="booking_gcal_auto_import_time" <?php if ($booking_gcal_auto_import_is_active != 'On') echo ' disabled="DISABLED" '; ?> >

                  <option <?php if($booking_gcal_auto_import_time == '1') echo "selected"; ?> value="1"><?php echo '1 '; _e('hour' ,'booking'); ?></option>
                  <?php
                    for ($i = 2; $i < 24; $i++) {
                      ?> <option <?php if($booking_gcal_auto_import_time == $i) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i,' ';  _e('hours' ,'booking'); ?></option> <?php
                    }
                  ?>
                  <option <?php if($booking_gcal_auto_import_time == '24') echo "selected"; ?> value="24"><?php echo '1 '; _e('day' ,'booking'); ?></option>
                  <?php
                    for ($i = 2; $i < 32; $i++) {
                      ?> <option <?php if($booking_gcal_auto_import_time == ($i*24) ) echo "selected"; ?> value="<?php echo ($i*24); ?>"><?php echo $i,' ';  _e('days' ,'booking'); ?></option> <?php
                    }
                  ?>
             </select>
             <span class="description"><?php _e('Select time duration of import requests.' ,'booking');?></span>
          </td>
        </tr>
        <?php
    }
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Actions Toolbar Import Buttons
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpbc_gcal_extend_buttons_in_action_toolbar_booking_listing() {

    
    $booking_gcal_feed = get_bk_option( 'booking_gcal_feed');
    $is_this_btn_disabled = false;

    if ( ( ! class_exists('wpdev_bk_personal') ) && ( $booking_gcal_feed == '' ) ) {

        $is_this_btn_disabled = true;                              
        $settigns_link = "admin.php?page=".WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ."wpdev-booking-option&tab=sync" ;
    } else {
        $booking_gcal_events_from = get_bk_option( 'booking_gcal_events_from');
        $booking_gcal_events_from_offset = get_bk_option( 'booking_gcal_events_from_offset' );
        $booking_gcal_events_from_offset_type = get_bk_option( 'booking_gcal_events_from_offset_type' );
        
        $booking_gcal_events_until = get_bk_option( 'booking_gcal_events_until');
        $booking_gcal_events_until_offset = get_bk_option( 'booking_gcal_events_until_offset' );
        $booking_gcal_events_until_offset_type = get_bk_option( 'booking_gcal_events_until_offset_type' );
        
        $booking_gcal_events_max = get_bk_option( 'booking_gcal_events_max');
        // $booking_gcal_timezone = get_bk_option( 'booking_gcal_timezone');


    }
    ?>
    <div id="wpbc_gcal_import_events" class="modal" style="display:none;width:59%;" >
       <div class="modal-header">
           <a class="close" data-dismiss="modal">&times;</a>
           <h3><?php 
           if ($is_this_btn_disabled) 
                _e('Warning!' ,'booking'); 
           else 
               _e('Retrieve Google Calendar Events ' ,'booking'); 
           ?></h3>
       </div>
       <div class="modal-body">
         <?php if ($is_this_btn_disabled) { ?>   
            <label class="help-block" style="display:block;">
                <?php printf(__('Please configure settings for import Google Calendar events' ,'booking'),'<b>',',</b>'); ?> 
                <a href="<?php echo $settigns_link; ?>"><?php _e('here' ,'booking');?></a>
            </label>
          <?php } else { ?>

                <table class="visibility_gcal_feeds_settings form-table settings-table"  >
                    <tbody>
                    <?php 
                        if ( function_exists('wpbc_gcal_settings_content_field_selection_booking_resources') ) 
                            wpbc_gcal_settings_content_field_selection_booking_resources(); 
                        else {                                                     
                            ?><input type="hidden" name="wpbc_booking_resource" id="wpbc_booking_resource" value="" /><?php
                        }
                        wpbc_gcal_settings_content_field_from( $booking_gcal_events_from, $booking_gcal_events_from_offset, $booking_gcal_events_from_offset_type ); 
                        wpbc_gcal_settings_content_field_until( $booking_gcal_events_until, $booking_gcal_events_until_offset, $booking_gcal_events_until_offset_type ); 
                        wpbc_gcal_settings_content_field_max_feeds( $booking_gcal_events_max ); 
                        // wpbc_gcal_settings_content_field_timezone($booking_gcal_timezone);

                    ?>  
                    </tbody>
                </table>

          <?php }  ?> 
       </div>
        <div class="modal-footer" style="text-align:center;"> 
         <?php if ($is_this_btn_disabled) { ?>   
         <a href="<?php  echo $settigns_link; ?>" 
            class="button button-primary"  style="float:none;" >
             <?php _e('Configure' ,'booking'); ?>
         </a>
         <?php } else { ?>
         <a href="javascript:void(0)" class="button button-primary"  style="float:none;"                                        
            onclick="javascript:wpbc_import_gcal_events('<?php echo get_bk_current_user_id(); ?>'
                                                            , [ jQuery('#booking_gcal_events_from').val(), jQuery('#booking_gcal_events_from_offset').val(), jQuery('#booking_gcal_events_from_offset_type').val() ]
                                                        , [ jQuery('#booking_gcal_events_until').val(), jQuery('#booking_gcal_events_until_offset').val(), jQuery('#booking_gcal_events_until_offset_type').val() ]
                                                        , jQuery('#booking_gcal_events_max').val()
                                                        , jQuery('#wpbc_booking_resource').val()
                 );jQuery('#wpbc_gcal_import_events').modal('hide');"
            ><?php _e('Import Google Calendar Events' ,'booking'); ?></a>
         <?php } ?>   
         <a href="javascript:void(0)" class="button" style="float:none;" data-dismiss="modal"><?php _e('Close' ,'booking'); ?></a>
       </div>
    </div>
    <div class="btn-group">                            
        <a  data-original-title="<?php _e('Import Google Calendar Events' ,'booking'); ?>"  rel="tooltip"
            class="tooltip_top button button-secondary<?php if ($is_this_btn_disabled) {echo ' disabled';} ?>" 
            <?php if ( true ) { ?>
                onclick='javascript:jQuery("#wpbc_gcal_import_events").modal("show");'
            <?php } else { ?>
                onclick="javascript:wpbc_import_gcal_events('<?php echo get_bk_current_user_id(); ?>' );"
            <?php } ?>                            
           /><?php _e('Import' ,'booking'); ?> <i class="icon-download"></i></a>
        <!--a data-original-title="<?php _e('Export only current page of bookings to CSV format' ,'booking'); ?>"  rel="tooltip" 
           class="tooltip_top  button button-secondary" onclick='javascript:export_booking_listing("page", "<?php echo getBookingLocale(); ?>");'
           /> <i class="icon-chevron-down"></i></a-->
    </div>
    <?php
}
add_bk_action('wpbc_extend_buttons_in_action_toolbar_booking_listing', 'wpbc_gcal_extend_buttons_in_action_toolbar_booking_listing' ); 


// Get the List  of Active Booking Form Fields
function wpbc_get_fields_list_in_booking_form_free() {
        
        $booking_form_fields_names = array( "name", "secondname", "email", "phone", "details" ) ;
        
        $booking_form_fields = array('num' => 1,  'listing' => array('labels'=>array(), 'fields'=>array() ) , 'name' => 'free' ); 
        
        $booking_form_fields['listing']['labels'][] = __('None' ,'booking');
        $booking_form_fields['listing']['fields'][] = '';
        $booking_form_fields['listing']['fields_type'][] = 'text';
        
        $key = 0;
        
        foreach ($booking_form_fields_names as $name) {
            $key++;
            if ( get_bk_option( "booking_form_field_active{$key}") != 'Off') {
                
                $booking_form_field_label      = get_bk_option( "booking_form_field_label{$key}");
                $booking_form_field_label      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label );
                
                $booking_form_fields['listing']['labels'][] = $booking_form_field_label;
                $booking_form_fields['listing']['fields'][] = $name;
                $booking_form_fields['listing']['fields_type'][] = 'text';
                $booking_form_fields['num']++;
            }            
        }
    if ($booking_form_fields['num'] == 0)    
        return  false;
    else
        return $booking_form_fields;
}



////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Activation
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpbc_sync_gcal_activate() {
        
    add_bk_option( 'booking_gcal_feed' , '' );
    add_bk_option( 'booking_gcal_events_from', 'month-start');
    add_bk_option( 'booking_gcal_events_from_offset' , '' );
    add_bk_option( 'booking_gcal_events_from_offset_type' , '' );
    add_bk_option( 'booking_gcal_events_until', 'any');
    add_bk_option( 'booking_gcal_events_until_offset' , '' );
    add_bk_option( 'booking_gcal_events_until_offset_type' , '' );
    add_bk_option( 'booking_gcal_events_max', '25');
    add_bk_option( 'booking_gcal_api_key', '');
    add_bk_option( 'booking_gcal_timezone','');
    add_bk_option( 'booking_gcal_is_send_email' , 'Off' );
    add_bk_option( 'booking_gcal_auto_import_is_active' , 'Off'  );
    add_bk_option( 'booking_gcal_auto_import_time', '24' );
    
    add_bk_option( 'booking_gcal_events_form_fields', 'a:3:{s:5:"title";s:9:"text^name";s:11:"description";s:12:"text^details";s:5:"where";s:5:"text^";}');
}
add_bk_action('wpdev_booking_activation',   'wpbc_sync_gcal_activate' );

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Deactivation
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function wpbc_sync_gcal_deactivate() {
    
    delete_bk_option( 'booking_gcal_feed' );
    delete_bk_option( 'booking_gcal_events_from');
    delete_bk_option( 'booking_gcal_events_from_offset' );
    delete_bk_option( 'booking_gcal_events_from_offset_type' );
    
    delete_bk_option( 'booking_gcal_events_until');
    delete_bk_option( 'booking_gcal_events_until_offset' );
    delete_bk_option( 'booking_gcal_events_until_offset_type' );
    
    delete_bk_option( 'booking_gcal_events_max' );    
    delete_bk_option( 'booking_gcal_api_key' );    
    delete_bk_option( 'booking_gcal_timezone');
    delete_bk_option( 'booking_gcal_is_send_email' );
    delete_bk_option( 'booking_gcal_auto_import_is_active' );
    delete_bk_option( 'booking_gcal_auto_import_time' );
    
    delete_bk_option( 'booking_gcal_events_form_fields');

}
add_bk_action('wpdev_booking_deactivation', 'wpbc_sync_gcal_deactivate' );

?>