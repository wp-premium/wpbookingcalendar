<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.

*/
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Sync API
 * @category Data Sync Google Calendar Feeds
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.08.15
 * @since 5.2.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S e t t i n g s     
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// HOOK: on submit of settings form
function wpbc_gcal_settings_content_submit_booking_resources_table() {
    if ( wpdev_bk_is_this_demo() )
        return;
    global $wpdb;
    $bk_types = get_bk_types(true);
    
    foreach ($bk_types as $bt) {
          if ( false === $wpdb->query( 
                  $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET import = %s WHERE booking_type_id = %d " 
                                  ,$_POST['booking_gcal_feed'.$bt->id], $bt->id )
                                  //,wpbc_set_relative_url($_POST['booking_gcal_feed'.$bt->id]), $bt->id )
                                     )  
             )  bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
    }    

}
add_bk_action('wpbc_gcal_settings_content_submit_booking_resources_table', 'wpbc_gcal_settings_content_submit_booking_resources_table' ); 


// Content of Settings page  
function wpbc_gcal_settings_content_show_booking_resources_table() {
    make_bk_action('wpbc_show_booking_resources_settings_table');   
}
add_bk_action('wpbc_gcal_settings_content_show_booking_resources_table', 'wpbc_gcal_settings_content_show_booking_resources_table' ); 



// Show additional COLUMNS near each  booking resources
function wpbc_gcal_settings_content_resources_collumns( $bt, $alternative_color ){
    ?>
    <td style="border-left: 1px solid #ccc;" <?php echo $alternative_color; ?> >
        <legend class="wpbc_mobile_legend"><?php _e('Google Calendar ID' ,'booking'); ?>:</legend>
        <span class="wpdevbk">
        <div class="control-group" style="margin:5px 0 0 0;">
            <div class="inline controls">
<!--                <div class="input-prepend">-->
                    <?php /* <span class="add-on" style="width:auto;" >&nbsp;https://www.google.com&nbsp;</span> */?>
                    <input  type="text" style="width:100%;" class="large-text"            
                        name="booking_gcal_feed<?php echo $bt->id; ?>" id="booking_gcal_feed<?php echo $bt->id; ?>"
                        value="<?php echo $bt->import; ?>"
                        />        
<!--                </div>-->
            </div>                   
        </div>  
        </span>    
    </td>    
    <?php
}
add_bk_action('wpbc_booking_resources_settings_table_columns', 'wpbc_gcal_settings_content_resources_collumns' ); 


function wpbc_gcal_booking_resources_settings_table_headers() {
    ?><th  style="width:55%"><?php  _e('Google Calendar ID' ,'booking'); ?></th><?php
}
add_bk_action('wpbc_booking_resources_settings_table_headers', 'wpbc_gcal_booking_resources_settings_table_headers' ); 

function wpbc_gcal_booking_resources_settings_table_footers() {
    ?><td></td><?php
}
add_bk_action('wpbc_booking_resources_settings_table_footers', 'wpbc_gcal_booking_resources_settings_table_footers' ); 


function wpbc_gcal_settings_content_field_selection_booking_resources() {

    $types_list  = get_bk_types(false, false); 

    ?>
    <tr valign="top">
        <th scope="row"><label for="wpbc_booking_resource"><?php _e('Booking resource' ,'booking'); ?>:</label></th>
        <td>                        
            <select id="wpbc_booking_resource" name="wpbc_booking_resource">
                <option value="all" style="font-weight:bold;border-bottom:1px solid #ccc;padding:5px;" ><?php _e('All' ,'booking'); ?></option>
                <?php foreach ($types_list as $tl) { ?>
                <option value="<?php echo $tl->id; ?>"
                            style="<?php if  (isset($tl->parent)) if ($tl->parent == 0 ) { echo 'padding:3px;font-weight:bold;'; } else { echo 'padding:3px;font-size:11px;padding-left:20px;'; } ?>"
                        ><?php echo $tl->title; ?></option>
                <?php } ?>
            </select>
          <span class="description"><?php _e('Select booking resource' ,'booking'); ?></span>
        </td>
    </tr>
    <?php
}

// Get fields from booking form at the settings page or return false if no fields
function wpbc_get_fields_list_in_booking_form () {
    
    
    $booking_forms   = array();
    $booking_forms[] = array( 'name' => 'standard', 'form' => get_bk_option( 'booking_form' ), 'content' => get_bk_option( 'booking_form_show' ) );

    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
    if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) {    
        $booking_forms_extended = get_bk_option( 'booking_forms_extended');
        if ($booking_forms_extended !== false) {
            if ( is_serialized( $booking_forms_extended ) ) {
                $booking_forms_extended = unserialize($booking_forms_extended);
                foreach ($booking_forms_extended as $form_extended) {
                    $booking_forms[] = $form_extended;
                }
            }
        }
    }    
    
    foreach ($booking_forms as $form_key => $booking_form_element) {
        $booking_form  = $booking_form_element['form'];


        // $booking_form  = get_bk_option( 'booking_form' );
        $types = 'text[*]?|email[*]?|time[*]?|textarea[*]?|select[*]?|checkbox[*]?|radio|acceptance|captchac|captchar|file[*]?|quiz';
        $regex = '%\[\s*(' . $types . ')(\s+[a-zA-Z][0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)?((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
        $regex2 = '%\[\s*(country[*]?|starttime[*]?|endtime[*]?)(\s*[a-zA-Z]*[0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)*((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
        $fields_count = preg_match_all($regex, $booking_form, $fields_matches) ;
        $fields_count2 = preg_match_all($regex2, $booking_form, $fields_matches2) ;

        //Gathering Together 2 arrays $fields_matches  and $fields_matches2
        foreach ($fields_matches2 as $key => $value) {
            if ($key == 2) $value = $fields_matches2[1];
            foreach ($value as $v) {
                $fields_matches[$key][count($fields_matches[$key])]  = $v;
            }
        }
        $fields_count += $fields_count2;
        
        $booking_forms[$form_key]['num'] = $fields_count;
        $booking_forms[$form_key]['listing'] = array();//$fields_matches;
        
        $booking_forms[$form_key]['listing']['labels'] = $fields_matches[2];
        $booking_forms[$form_key]['listing']['fields'] = $fields_matches[2];
        
        foreach ($fields_matches[1] as $key_fm=>$value_fm) {
            $fields_matches[1][$key_fm] = trim(str_replace('*','',$value_fm));
        }
        
        $booking_forms[$form_key]['listing']['fields_type'] = $fields_matches[1];

//        if ($booking_form_element['name'] == 'standard') {            
//            array_unshift($booking_forms[$form_key]['listing']['labels'], __('None' ,'booking') );
//            array_unshift($booking_forms[$form_key]['listing']['fields'], '' );
//            array_unshift($booking_forms[$form_key]['listing']['fields_type'], 'text' );
//            $booking_forms[$form_key]['num']++;
//        }
        
        // Reset
        unset( $booking_forms[$form_key]['form'] );
        unset( $booking_forms[$form_key]['content'] );
    }
    
    return $booking_forms;
        
}

add_bk_filter('wpbc_get_fields_list_in_booking_form',  'wpbc_get_fields_list_in_booking_form' );

?>