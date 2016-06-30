<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.

*/
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Settings
 * @category Interface
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.08.08
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


// Show Search booking resources form at top of Settings page
function wpbc_find_booking_resource_form(){ 
    ?>    
    <div style="position: absolute; right: 15px;top: 10px;" class="wpdevbk">
        <form  name="booking_filters_formID" action="" method="post" id="booking_filters_formID" class=" form-search"><?php 
            if (isset($_REQUEST['wh_resource_id']))  
                $wh_resource_id = $_REQUEST['wh_resource_id'];                  //  {'1', '2', .... }
            else                                     
                $wh_resource_id      = '';                    
            ?>
            <input class="input" type="text" placeholder="<?php _e('Resource ID or Title' ,'booking'); ?>" name="wh_resource_id" id="wh_resource_id" value="<?php echo $wh_resource_id; ?>" >
            <input class="input" type="hidden"  name="page_num" id="page_num" value="1" >
            <button class="button button-secondary" type="submit"><?php _e('Go' ,'booking'); ?></button>
        </form>
    </div><?php    
}
add_bk_action('wpbc_find_booking_resource_form' , 'wpbc_find_booking_resource_form' ); 

// Show Availability and Rates resource content list in selected tab menu
function wpbc_show_booking_resources_settings_table(){ 
        
    $alternative_color = '0';

    $bk_types = get_bk_types(true);

    $all_id = array(array('id'=>0,'title'=>' - '));
    
    foreach ($bk_types as $btt) {
        if ( ( isset($btt->parent) ) && ($btt->parent==0) ) 
            $all_id[] = array('id'=>$btt->id, 'title'=> $btt->title);
    }                
    
    ?><div style="clear:both;width:100%;height:1px;"></div><?php 
    
    make_bk_action('wpbc_before_booking_resources_settings_table'); 
    
    ?><div style="width:100%;">

        <div style="height:auto;display:none;">

            <?php if (isset($_REQUEST['page_num'])) { ?>
            <input class="input" type="hidden"  name="page_num" id="page_num" value="<?php echo intval($_REQUEST['page_num']); ?>" >
            <?php } if (isset($_REQUEST['wh_resource_id'])) { ?>
                <input class="input" type="hidden"  name="wh_resource_id" id="wh_resource_id" value="<?php echo intval($_REQUEST['wh_resource_id']); ?>" >
            <?php } ?>

        </div>
        <div class="clear" style="height:15px;width:100%;clear:both;"></div>

        <table style="width:99%;" class="resource_table booking_table" cellpadding="0" cellspacing="0">
            <?php // Headers  ?>
            <tr>
                <th style="width:15px;"><input type="checkbox" onclick="javascript:jQuery('.resources_items').attr('checked', this.checked);" class="resources_items" id="resources_items_all"  name="resources_items_all" /></th>
                <th style="width:10px;height:35px;border-left: 1px solid #BBBBBB;"> <?php _e('ID' ,'booking'); ?> </th>
                <th style="height:35px;width:220px;"> <?php _e('Resource name' ,'booking'); ?> </th>
                <?php  make_bk_action('wpbc_booking_resources_settings_table_headers' ); ?>
                <th style="text-align:center;"> <?php _e('Info' ,'booking'); ?> </th>
            </tr>
            <?php
            if (! empty($bk_types))
              foreach ($bk_types as $bt) {
                      
                if ( $alternative_color == '')    
                    $alternative_color = ' class="alternative_color" ';
                else                              
                    $alternative_color = '';

                ?>
                   <tr>
                        <td <?php echo $alternative_color; ?> ><legend class="wpbc_mobile_legend"><?php _e('Selection' ,'booking'); ?>:</legend><input type="checkbox" class="resources_items" id="resources_items_<?php echo $bt->id; ?>" value="<?php echo $bt->id; ?>"  name="resources_items_<?php echo $bt->id; ?>" /></td>
                        <td style="border-left: 1px solid #ccc;text-align: center;" <?php echo $alternative_color; ?> ><legend class="wpbc_mobile_legend"><?php _e('ID' ,'booking'); ?>:</legend><?php echo $bt->id; ?></td>
                        <td style="<?php if (isset($bt->parent)) if ($bt->parent != 0 ) { echo 'padding-left:50px;'; } ?>" <?php echo $alternative_color; ?> >
                            <legend class="wpbc_mobile_legend"><?php _e('Resource Name' ,'booking'); ?>:</legend>
                            <span style="<?php if (isset($bt->parent))  if ( ($bt->parent == 0 ) && ( $bt->count > 1 ) )  { echo 'font-weight:bold;'; }?>"><?php echo $bt->title; ?></span>
                            <!--input  maxlength="17" type="text"
                                style="<?php  if (isset($bt->parent))  if ( ($bt->parent == 0 ) && ( $bt->count > 1 ) ) { echo 'width:210px;font-weight:bold;'; } else { echo 'width:170px;font-size:11px;'; } ?>"
                                value="<?php echo $bt->title; ?>"
                                name="type_title<?php echo $bt->id; ?>" id="type_title<?php echo $bt->id; ?>" /-->
                            <?php if (isset($bt->parent)) if ($bt->parent == 0 ) { make_bk_action('resources_settings_after_title', $bt, $all_id, $alternative_color ); } ?>
                        </td>
                        <?php   make_bk_action('wpbc_booking_resources_settings_table_columns', $bt , $alternative_color  ); ?>
                        <td style="border-right: 0px;border-left: 1px solid #ccc;text-align: center;" <?php echo $alternative_color; ?> >
                            <legend class="wpbc_mobile_legend"><?php _e('Info' ,'booking'); ?>:</legend>
                            <?php 
                                make_bk_action('resources_settings_table_info_collumns', $bt, $all_id, $alternative_color );  
                                make_bk_action('show_users_info_label', $bt , $alternative_color ); 
                             ?>
                        </td>

                   </tr>                   
            <?php } ?>

            <tr class="wpbc_table_footer">
                <td></td>
                <td></td>
                <td></td>
                <?php  make_bk_action('wpbc_booking_resources_settings_table_footers' ); ?>
                <td></td>
            </tr>

        </table>
        
        <div class="wpdevbk">
        <?php 
            // Pagination

            $active_page_num = (isset($_REQUEST['page_num'])) ? $_REQUEST['page_num'] : 1;

            $items_count_in_page=get_bk_option( 'booking_resourses_num_per_page');

            wpdevbk_show_pagination(
                                      get_booking_resources_count()
                                    , $active_page_num, $items_count_in_page
                                    , array('page','tab', 'wh_resource_id')
                                    //,'wpdev_edit_costs_from_days','wpdev_edit_rates', 'wpdev_edit_costs_deposit_payment','wpdev_edit_avalaibility')
                                   );        
        ?>
        </div>
        <div class="clear" style="height:1px;"></div>        
    </div>

    <div style="clear:both;width:100%;height:1px;"></div> <?php
    
    make_bk_action('wpbc_after_booking_resources_settings_table'); 
}

add_bk_action('wpbc_show_booking_resources_settings_table' , 'wpbc_show_booking_resources_settings_table' ); 
?>