<?php 
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
 * @modified 2014.05.18
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S e t t i n g s                ///////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




function wpbc_general_settings_top_menu_submenu_line(){

    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');

    if (  ( $is_can ) && ( (! isset($_GET['tab']) ) || ($_GET['tab'] == 'main') || ($_GET['tab'] == '') )  ) {
    ?>
        <div class="booking-submenu-tab-container">
            <div class="nav-tabs booking-submenu-tab-insidecontainer">

                <?php /** ?><a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_main' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Main' ,'booking');?></span></a><?php /**/ ?>

                <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_calendar' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Calendar' ,'booking');?></span></a>

                <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_form' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Form' ,'booking');?></span></a>
                
                <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_bktable' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Booking Listing' ,'booking');?></span></a>


                <?php if ( class_exists('wpdev_bk_biz_s')) { ?>
                
                    <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_general_cost_options' );"
                        class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Costs' ,'booking');?></span></a>

                    <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_auto_cancel_pending_nk' );"
                        class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Auto cancellation / approval' ,'booking');?></span></a>

                    <?php /*if ( class_exists('wpdev_bk_biz_l')) { ?>
                
                        <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_resources_advanced_options' );"
                            class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Advanced' ,'booking');?></span></a>

                    <?php }/**/ ?>
                <?php } ?>
                        <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_settings_resources_advanced_options' );"
                            class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Advanced' ,'booking');?></span></a>

                
                <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_users_permissions' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Menu access' ,'booking');?></span></a>
                
                <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_uninstall' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Uninstall' ,'booking');?></span></a>
                
                <a href="javascript:void(0)" onclick="javascript:makeScrollInAdminPanel('#bk_general_settings_technical_section' );"
                   class="nav-tab booking-submenu-tab go-to-link" ><span><?php _e('Technical' ,'booking');?></span></a>
                
                <input type="button" class="button-primary button" value="<?php _e('Save Changes' ,'booking'); ?>" 
                   style="float:right;"
                   onclick="document.forms['post_option'].submit();">
                
                <div class="clear" style="height:0px;"></div>

            </div>
        </div>
      <?php
    }

}
add_bk_action('wpdev_booking_settings_top_menu_submenu_line',   'wpbc_general_settings_top_menu_submenu_line' );

// Show Settings content of main page
function wpdev_bk_settings_general() {

    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
    if ($is_can===false) return;


    if ( isset( $_POST['start_day_weeek'] ) ) {
        $booking_skin  = $_POST['booking_skin'];

//        $email_reservation_adress      = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_adress']));
//        $email_reservation_adress      = str_replace("\'","'",$email_reservation_adress);

        $bookings_num_per_page = $_POST['bookings_num_per_page'];
        $booking_sort_order = $_POST['booking_sort_order'];
        $booking_default_toolbar_tab = $_POST['booking_default_toolbar_tab'];
        $bookings_listing_default_view_mode = $_POST['bookings_listing_default_view_mode'];
        $booking_view_days_num = $_POST['booking_view_days_num'];

        //$booking_sort_order_direction = $_POST['booking_sort_order_direction'];

        $max_monthes_in_calendar =  $_POST['max_monthes_in_calendar'];
        if (isset($_POST['admin_cal_count'])) $admin_cal_count  = $_POST['admin_cal_count'];
        if (isset($_POST['client_cal_count'])) $client_cal_count = $_POST['client_cal_count'];
        $start_day_weeek  = $_POST['start_day_weeek'];
        $new_booking_title= $_POST['new_booking_title'];
        $new_booking_title_time= $_POST['new_booking_title_time'];
        $type_of_thank_you_message = $_POST['type_of_thank_you_message'];//get_bk_option( 'booking_type_of_thank_you_message' ); //= 'message'; = 'page';

        $thank_you_page_URL = wpbc_make_link_relative( $_POST['thank_you_page_URL'] );  //get_bk_option( 'booking_thank_you_page_URL' ); //= 'message'; = 'page';

        $booking_date_format = $_POST['booking_date_format'];
        $booking_date_view_type = $_POST['booking_date_view_type'];
        //$is_dif_colors_approval_pending = $_POST['is_dif_colors_approval_pending'];
        if (isset($_POST['is_use_hints_at_admin_panel']))
            $is_use_hints_at_admin_panel = $_POST['is_use_hints_at_admin_panel'];

        $type_of_day_selections = $_POST[ 'type_of_day_selections' ];


        if (isset($_POST['is_delete_if_deactive'])) $is_delete_if_deactive =  $_POST['is_delete_if_deactive']; // check
        if (isset($_POST['wpdev_copyright_adminpanel'])) $wpdev_copyright_adminpanel  = $_POST['wpdev_copyright_adminpanel'];             // check
        if (isset($_POST['booking_is_show_powered_by_notice'])) $booking_is_show_powered_by_notice  = $_POST['booking_is_show_powered_by_notice'];             // check

        if (isset($_POST['is_use_captcha'])) $is_use_captcha  = $_POST['is_use_captcha'];             // check
        if (isset($_POST['is_use_autofill_4_logged_user'])) $is_use_autofill_4_logged_user  = $_POST['is_use_autofill_4_logged_user'];             // check

        if (isset($_POST['unavailable_day0']))  $unavailable_day0  = $_POST['unavailable_day0'];
        if (isset($_POST['unavailable_day1']))  $unavailable_day1  = $_POST['unavailable_day1'];
        if (isset($_POST['unavailable_day2']))  $unavailable_day2  = $_POST['unavailable_day2'];
        if (isset($_POST['unavailable_day3']))  $unavailable_day3  = $_POST['unavailable_day3'];
        if (isset($_POST['unavailable_day4']))  $unavailable_day4  = $_POST['unavailable_day4'];
        if (isset($_POST['unavailable_day5']))  $unavailable_day5  = $_POST['unavailable_day5'];
        if (isset($_POST['unavailable_day6']))  $unavailable_day6  = $_POST['unavailable_day6'];

        if ( isset( $_POST['user_role_booking'] ) )
            $user_role_booking          = $_POST['user_role_booking'];
        if ( isset( $_POST['user_role_addbooking'] ) )
            $user_role_addbooking       = $_POST['user_role_addbooking'];
        if ( isset( $_POST['user_role_settings'] ) )
            $booking_user_role_settings = $_POST['user_role_settings'];
        if ( isset( $_POST['user_role_resources'] ) )
            $user_role_resources    = $_POST['user_role_resources'];
        
        if ( wpdev_bk_is_this_demo() ) {
            $user_role_booking          = 'subscriber';
            $user_role_addbooking       = 'subscriber';
            $booking_user_role_settings = 'subscriber';
            $user_role_resources        = 'subscriber';
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////


        update_bk_option( 'booking_user_role_booking', $user_role_booking );
        update_bk_option( 'booking_user_role_addbooking', $user_role_addbooking );
        if (isset($user_role_resources))
            update_bk_option( 'booking_user_role_resources', $user_role_resources );
        update_bk_option( 'booking_user_role_settings', $booking_user_role_settings );


        update_bk_option( 'bookings_num_per_page',$bookings_num_per_page);
        update_bk_option( 'booking_sort_order',$booking_sort_order);
        update_bk_option( 'booking_default_toolbar_tab',$booking_default_toolbar_tab);
        update_bk_option( 'bookings_listing_default_view_mode',$bookings_listing_default_view_mode);
        update_bk_option( 'booking_view_days_num',$booking_view_days_num);


        //update_bk_option( 'booking_sort_order_direction',$booking_sort_order_direction);

        update_bk_option( 'booking_skin',$booking_skin);
//        update_bk_option( 'booking_email_reservation_adress' , $email_reservation_adress );
//
//        if ( get_bk_version() == 'free' ) { // Update admin from adresses at free version
//            //update_bk_option( 'booking_email_reservation_from_adress', $email_reservation_adress );
//            update_bk_option( 'booking_email_approval_adress', $email_reservation_adress );
//            update_bk_option( 'booking_email_deny_adress', $email_reservation_adress );
//        }

        update_bk_option( 'booking_max_monthes_in_calendar' , $max_monthes_in_calendar );

        if (! isset($admin_cal_count)) $admin_cal_count = 2;
        if (! isset($client_cal_count)) $client_cal_count = 1;

        if (1*$admin_cal_count>12) $admin_cal_count = 12;
        if (1*$admin_cal_count< 1) $admin_cal_count = 1;
        update_bk_option( 'booking_admin_cal_count' , $admin_cal_count );
        if (1*$client_cal_count>12) $client_cal_count = 12;
        if (1*$client_cal_count< 1) $client_cal_count = 1;
        update_bk_option( 'booking_client_cal_count' , $client_cal_count );
        update_bk_option( 'booking_start_day_weeek' , $start_day_weeek );
        update_bk_option( 'booking_title_after_reservation' , $new_booking_title );
        update_bk_option( 'booking_title_after_reservation_time' , $new_booking_title_time );
        update_bk_option( 'booking_type_of_thank_you_message' , $type_of_thank_you_message );
        update_bk_option( 'booking_thank_you_page_URL' , $thank_you_page_URL );



        update_bk_option( 'booking_date_format' , $booking_date_format );
        update_bk_option( 'booking_date_view_type' , $booking_date_view_type);
        // if (isset( $is_dif_colors_approval_pending ))   $is_dif_colors_approval_pending = 'On';
        // else                                            $is_dif_colors_approval_pending = 'Off';
        // update_bk_option( 'booking_dif_colors_approval_pending' , $is_dif_colors_approval_pending );

        if (isset( $is_use_hints_at_admin_panel ))   $is_use_hints_at_admin_panel = 'On';
        else                                            $is_use_hints_at_admin_panel = 'Off';
        update_bk_option( 'booking_is_use_hints_at_admin_panel' , $is_use_hints_at_admin_panel );

        if (! wpdev_bk_is_this_demo() ) { // Do not allow to chnage it in  the demo
            if (isset( $_POST['is_not_load_bs_script_in_client'] ))   $is_not_load_bs_script_in_client = 'On';
            else                                                      $is_not_load_bs_script_in_client = 'Off';
            update_bk_option( 'booking_is_not_load_bs_script_in_client' , $is_not_load_bs_script_in_client );
            if (isset( $_POST['is_not_load_bs_script_in_admin'] ))   $is_not_load_bs_script_in_admin = 'On';
            else                                                      $is_not_load_bs_script_in_admin = 'Off';
            update_bk_option( 'booking_is_not_load_bs_script_in_admin' , $is_not_load_bs_script_in_admin );
            
            if (isset( $_POST['is_load_js_css_on_specific_pages'] ))   $is_load_js_css_on_specific_pages = 'On';
            else                                                      $is_load_js_css_on_specific_pages = 'Off';
            update_bk_option( 'booking_is_load_js_css_on_specific_pages' , $is_load_js_css_on_specific_pages );
            
            update_bk_option( 'booking_pages_for_load_js_css' , $_POST['booking_pages_for_load_js_css'] );                        
            $booking_pages_for_load_js_css = get_bk_option( 'booking_pages_for_load_js_css'  );
        }


        update_bk_option( 'booking_type_of_day_selections' , $type_of_day_selections );

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
        update_bk_option( 'booking_is_days_always_available', ( (isset( $_POST['booking_is_days_always_available'] ))?'On':'Off') );
        update_bk_option( 'booking_check_on_server_if_dates_free', ( (isset( $_POST['booking_check_on_server_if_dates_free'] ))?'On':'Off') );
        
        
        $unavailable_days_num_from_today     = $_POST['unavailable_days_num_from_today'];
        update_bk_option( 'booking_unavailable_days_num_from_today' , $unavailable_days_num_from_today );




        if (isset( $unavailable_day0 ))            $unavailable_day0 = 'On';
        else                                       $unavailable_day0 = 'Off';
        update_bk_option( 'booking_unavailable_day0' , $unavailable_day0 );
        if (isset( $unavailable_day1 ))            $unavailable_day1 = 'On';
        else                                       $unavailable_day1 = 'Off';
        update_bk_option( 'booking_unavailable_day1' , $unavailable_day1 );
        if (isset( $unavailable_day2 ))            $unavailable_day2 = 'On';
        else                                       $unavailable_day2 = 'Off';
        update_bk_option( 'booking_unavailable_day2' , $unavailable_day2 );
        if (isset( $unavailable_day3 ))            $unavailable_day3 = 'On';
        else                                       $unavailable_day3 = 'Off';
        update_bk_option( 'booking_unavailable_day3' , $unavailable_day3 );
        if (isset( $unavailable_day4 ))            $unavailable_day4 = 'On';
        else                                       $unavailable_day4 = 'Off';
        update_bk_option( 'booking_unavailable_day4' , $unavailable_day4 );
        if (isset( $unavailable_day5 ))            $unavailable_day5 = 'On';
        else                                       $unavailable_day5 = 'Off';
        update_bk_option( 'booking_unavailable_day5' , $unavailable_day5 );
        if (isset( $unavailable_day6 ))            $unavailable_day6 = 'On';
        else                                       $unavailable_day6 = 'Off';
        update_bk_option( 'booking_unavailable_day6' , $unavailable_day6 );

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (isset( $is_delete_if_deactive ))            $is_delete_if_deactive = 'On';
        else                                            $is_delete_if_deactive = 'Off';
        update_bk_option( 'booking_is_delete_if_deactive' , $is_delete_if_deactive );

        if (isset( $booking_is_show_powered_by_notice ))                  $booking_is_show_powered_by_notice = 'On';
        else                                            $booking_is_show_powered_by_notice = 'Off';
        update_bk_option( 'booking_is_show_powered_by_notice' , $booking_is_show_powered_by_notice );
        if (isset( $wpdev_copyright_adminpanel ))                  $wpdev_copyright_adminpanel = 'On';
        else                                            $wpdev_copyright_adminpanel = 'Off';
        update_bk_option( 'booking_wpdev_copyright_adminpanel' , $wpdev_copyright_adminpanel );
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
        if (isset( $is_use_captcha ))                  $is_use_captcha = 'On';
        else                                           $is_use_captcha = 'Off';
        update_bk_option( 'booking_is_use_captcha' , $is_use_captcha );

        if (isset( $is_use_autofill_4_logged_user ))                    $is_use_autofill_4_logged_user = 'On';
        else                                                            $is_use_autofill_4_logged_user = 'Off';
        update_bk_option( 'booking_is_use_autofill_4_logged_user' , $is_use_autofill_4_logged_user );



        //if (isset( $is_show_legend ))                  $is_show_legend = 'On';
        //else                                           $is_show_legend = 'Off';
        //update_bk_option( 'booking_is_show_legend' , $is_show_legend );

    } else {
        $booking_skin = get_bk_option( 'booking_skin');
        //$email_reservation_adress      = get_bk_option( 'booking_email_reservation_adress') ;
        $max_monthes_in_calendar =  get_bk_option( 'booking_max_monthes_in_calendar' );

        $bookings_num_per_page =  get_bk_option( 'bookings_num_per_page');
        $booking_sort_order = get_bk_option( 'booking_sort_order');
        $booking_default_toolbar_tab = get_bk_option( 'booking_default_toolbar_tab');
        $bookings_listing_default_view_mode = get_bk_option( 'bookings_listing_default_view_mode');
        $booking_view_days_num = get_bk_option( 'booking_view_days_num');
        //$booking_sort_order_direction = get_bk_option( 'booking_sort_order_direction');


        $admin_cal_count  = get_bk_option( 'booking_admin_cal_count' );
        $new_booking_title= get_bk_option( 'booking_title_after_reservation' );
        $new_booking_title_time= get_bk_option( 'booking_title_after_reservation_time' );

        $type_of_thank_you_message = get_bk_option( 'booking_type_of_thank_you_message' ); //= 'message'; = 'page';
        $thank_you_page_URL = get_bk_option( 'booking_thank_you_page_URL' ); //= 'message'; = 'page';


        $booking_date_format = get_bk_option( 'booking_date_format');
        $booking_date_view_type = get_bk_option( 'booking_date_view_type');
        $client_cal_count = get_bk_option( 'booking_client_cal_count' );
        $start_day_weeek  = get_bk_option( 'booking_start_day_weeek' );
        $is_use_hints_at_admin_panel    = get_bk_option( 'booking_is_use_hints_at_admin_panel' );
        $is_not_load_bs_script_in_client = get_bk_option( 'booking_is_not_load_bs_script_in_client'  );
        $is_not_load_bs_script_in_admin = get_bk_option( 'booking_is_not_load_bs_script_in_admin'  );        
        $is_load_js_css_on_specific_pages = get_bk_option( 'booking_is_load_js_css_on_specific_pages'  );
        $booking_pages_for_load_js_css = get_bk_option( 'booking_pages_for_load_js_css'  );

        $type_of_day_selections =  get_bk_option( 'booking_type_of_day_selections');



        $is_delete_if_deactive =  get_bk_option( 'booking_is_delete_if_deactive' ); // check
        $wpdev_copyright_adminpanel  = get_bk_option( 'booking_wpdev_copyright_adminpanel' );             // check
        $booking_is_show_powered_by_notice = get_bk_option( 'booking_is_show_powered_by_notice' );             // check
        $is_use_captcha  = get_bk_option( 'booking_is_use_captcha' );             // check
        $is_use_autofill_4_logged_user  = get_bk_option( 'booking_is_use_autofill_4_logged_user' );             // check

        $unavailable_days_num_from_today = get_bk_option( 'booking_unavailable_days_num_from_today'  );        
        $unavailable_day0 = get_bk_option( 'booking_unavailable_day0' );
        $unavailable_day1 = get_bk_option( 'booking_unavailable_day1' );
        $unavailable_day2 = get_bk_option( 'booking_unavailable_day2' );
        $unavailable_day3 = get_bk_option( 'booking_unavailable_day3' );
        $unavailable_day4 = get_bk_option( 'booking_unavailable_day4' );
        $unavailable_day5 = get_bk_option( 'booking_unavailable_day5' );
        $unavailable_day6 = get_bk_option( 'booking_unavailable_day6' );
        

        $user_role_booking      = get_bk_option( 'booking_user_role_booking' );
        $user_role_addbooking   = get_bk_option( 'booking_user_role_addbooking' );
        $user_role_resources   = get_bk_option( 'booking_user_role_resources');
        $booking_user_role_settings     = get_bk_option( 'booking_user_role_settings' );
        
        
    }
        $booking_is_days_always_available = get_bk_option( 'booking_is_days_always_available' );
        $booking_check_on_server_if_dates_free = get_bk_option( 'booking_check_on_server_if_dates_free' );

    if (empty($type_of_thank_you_message)) $type_of_thank_you_message = 'message';
    
    
    if ( isset( $_POST['start_day_weeek'] ) ) {
        $wpbc = wpbookingcalendar();
        if ( isset($wpbc->notice) )
            $wpbc->notice->show_message( __('Settings saved' ,'booking'), 15 );    
    }
    ?>
    <div  class="clear" style="height:10px;"></div>
    <div class="wpdevbk-not-now">
    <form  name="post_option" action="" method="post" id="post_option" class="form-horizontal">

        <div class="booking_settings_row"  style="width:64%; float:left;margin-right:1%;">
            <?php /** ?>
            <div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_general_settings_main'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Main' ,'booking'); ?></span></h3> <div class="inside">
                        <table class="form-table"><tbody>
                        
                        <tr valign="top">
                            <th scope="row"><label for="email_reservation_adress" ><?php _e('Admin email' ,'booking'); ?>:</label></th>
                            <td><input id="email_reservation_adress"  name="email_reservation_adress" class="large-text" type="text" value="<?php echo $email_reservation_adress; ?>" />
                                <p class="description"><?php printf(__('Type default %sadmin email%s for booking confirmation' ,'booking'),'<b>','</b>');?></p>
                            </td>
                        </tr>

                </tbody></table>
    </div></div></div><?php /**/ ?>

    <div class='meta-box'>
        <div <?php $my_close_open_win_id = 'bk_general_settings_calendar'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
            <h3 class='hndle'><span><?php _e('Calendar' ,'booking'); ?></span></h3> <div class="inside">
                <table class="form-table"><tbody>

                        <tr valign="top">
                            <th scope="row"><label for="booking_skin" ><?php _e('Calendar Skin' ,'booking'); ?>:</label></th>
                            <td>
                                <?php 
                                // Check  for the skins in the Custom User Skins folderm  that do not ovveriden during update of plugin
                                // Exmaple: http://example.com/wp-content/uploads/wpbc_skins/
                                // User  need to create it manually.
                                $upload_dir = wp_upload_dir(); 
                                $custom_user_skin_folder = $upload_dir['basedir'] . '/wpbc_skins/';
                                
                                $dir_list = wpdev_bk_dir_list( array(  '/css/skins/', '/inc/skins/', $custom_user_skin_folder ) ); 
//debuge($dir_list);
                                ?>
                                <select id="booking_skin" name="booking_skin" style="text-transform:capitalize;">
                                <?php 
                                    foreach ($dir_list as $value) {
                                        
                                        $value[1] = str_replace( array( WPDEV_BK_PLUGIN_URL, $upload_dir['basedir'] ), '', $value[1] );
                                        
                                        if($booking_skin == $value[1]) $selected_item =  'selected="SELECTED"';
                                        else $selected_item='';
                                        if (  strpos( str_replace( array(WPDEV_BK_PLUGIN_URL, $upload_dir['basedir'] ) , '', $booking_skin) , $value[0]) !== false ) $selected_item =  'selected="SELECTED"';
                                        echo '<option '.$selected_item.' value="'.$value[1].'" >' .  $value[2] . '</option>';
                                    } ?>
                                </select>
                                <span class="description"><?php _e('Select the skin of the booking calendar' ,'booking');?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="max_monthes_in_calendar" ><?php _e('Number of months' ,'booking'); ?>:</label></th>
                            <td>
                                <select id="max_monthes_in_calendar" name="max_monthes_in_calendar">
                                    <?php for ($mm = 1; $mm < 12; $mm++) { ?>
                                        <option <?php if($max_monthes_in_calendar == $mm .'m') echo "selected"; ?> value="<?php echo $mm; ?>m"><?php echo $mm ,' ';
                                          _e('month(s)' ,'booking'); ?></option>
                                    <?php } ?>

                                    <?php for ($mm = 1; $mm < 11; $mm++) { ?>
                                        <option <?php if($max_monthes_in_calendar == $mm .'y') echo "selected"; ?> value="<?php echo $mm; ?>y"><?php echo $mm ,' ';
                                          _e('year(s)' ,'booking'); ?></option>
                                    <?php } ?>
                                </select>
                                <span class="description"><?php _e('Select the maximum number of months to show (scroll)' ,'booking');?></span>
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="start_day_weeek" ><?php _e('Start Day of the week' ,'booking'); ?>:</label></th>
                            <td>
                                <select id="start_day_weeek" name="start_day_weeek">
                                    <option <?php if($start_day_weeek == '0') echo "selected"; ?> value="0"><?php _e('Sunday' ,'booking'); ?></option>
                                    <option <?php if($start_day_weeek == '1') echo "selected"; ?> value="1"><?php _e('Monday' ,'booking'); ?></option>
                                    <option <?php if($start_day_weeek == '2') echo "selected"; ?> value="2"><?php _e('Tuesday' ,'booking'); ?></option>
                                    <option <?php if($start_day_weeek == '3') echo "selected"; ?> value="3"><?php _e('Wednesday' ,'booking'); ?></option>
                                    <option <?php if($start_day_weeek == '4') echo "selected"; ?> value="4"><?php _e('Thursday' ,'booking'); ?></option>
                                    <option <?php if($start_day_weeek == '5') echo "selected"; ?> value="5"><?php _e('Friday' ,'booking'); ?></option>
                                    <option <?php if($start_day_weeek == '6') echo "selected"; ?> value="6"><?php _e('Saturday' ,'booking'); ?></option>
                                </select>
                                <span class="description"><?php _e('Select your start day of the week' ,'booking');?></span>
                            </td>
                        </tr>

                        <tr valign="top"><td colspan="2" style="padding:10px 0px; "><div style="border-bottom:1px solid #cccccc;"></div></td></tr>

                        <tr valign="top">
                            <th scope="row"><label for="unavailable_days_num_from_today" ><?php _e('Unavailable days from today' ,'booking'); ?>:</label></th>
                            <td>
                                <select id="unavailable_days_num_from_today" name="unavailable_days_num_from_today">
                                    <?php  for ($i = 0; $i < 32; $i++) { ?>
                                    <option <?php if($unavailable_days_num_from_today == $i) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                    <?php      } ?>
                                </select>
                                <span class="description"><?php _e('Select number of unavailable days in calendar start from today.' ,'booking');?></span>
                            </td>
                        </tr>

                        <?php do_action('settings_calendar_unavailable_days'); ?>

                        <tr valign="top">
                            <th scope="row"><?php _e('Unavailable week days' ,'booking'); ?>:</th>
                            <td>    
                                <label for="unavailable_day0" class="wpbc-single-checkbox">
                                    <input id="unavailable_day0" name="unavailable_day0" <?php if ($unavailable_day0 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day0; ?>"  type="checkbox" />
                                    <?php _e('Sunday' ,'booking'); ?>
                                </label>
                                <label for="unavailable_day1" class="wpbc-single-checkbox">
                                    <input id="unavailable_day1" name="unavailable_day1" <?php if ($unavailable_day1 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day1; ?>"  type="checkbox" />
                                    <?php _e('Monday' ,'booking'); ?>
                                </label>
                                <label for="unavailable_day2" class="wpbc-single-checkbox">
                                    <input id="unavailable_day2" name="unavailable_day2" <?php if ($unavailable_day2 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day2; ?>"  type="checkbox" />
                                    <?php _e('Tuesday' ,'booking'); ?>
                                </label>
                                <label for="unavailable_day3" class="wpbc-single-checkbox">
                                    <input id="unavailable_day3" name="unavailable_day3" <?php if ($unavailable_day3 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day3; ?>"  type="checkbox" />
                                    <?php _e('Wednesday' ,'booking'); ?>
                                </label>
                                <label for="unavailable_day4" class="wpbc-single-checkbox">
                                    <input id="unavailable_day4" name="unavailable_day4" <?php if ($unavailable_day4 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day4; ?>"  type="checkbox" />
                                    <?php _e('Thursday' ,'booking'); ?>
                                </label>
                                <label for="unavailable_day5" class="wpbc-single-checkbox">
                                    <input id="unavailable_day5" name="unavailable_day5" <?php if ($unavailable_day5 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day5; ?>"  type="checkbox" />
                                    <?php _e('Friday' ,'booking'); ?>
                                </label>
                                <label for="unavailable_day6" class="wpbc-single-checkbox">
                                    <input id="unavailable_day6" name="unavailable_day6" <?php if ($unavailable_day6 == 'On') echo "checked"; ?>  value="<?php echo $unavailable_day6; ?>"  type="checkbox" />
                                    <?php _e('Saturday' ,'booking'); ?>
                                </label>
                                <p class="description"><?php _e('Check unavailable days in calendars. This option will overwrite all other settings.' ,'booking');?></p>
                            </td>
                        </tr>

<?php /* Allow multiple bookings per same day previusly  was here  */ ?>                                                
                        
                        
                        <tr valign="top"><td colspan="2" style="padding:10px 0px; "><div style="border-bottom:1px solid #cccccc;"></div></td></tr>

                        <tr valign="top">
                            <th scope="row"><?php _e('Type of days selection in calendar' ,'booking'); ?>:</th>
                            <td>
                                <fieldset>
                                    <legend class="screen-reader-text"><span><?php _e('Type of days selection' ,'booking'); ?></span></legend>
                                    <label for="type_of_day_selections_single">
                                        <input  value="single" <?php if ( ($type_of_day_selections == 'single') || (empty($type_of_day_selections)) ) echo 'checked="CHECKED"'; ?>
                                            onclick="javascript: jQuery('#togle_settings_range_type_selection').slideUp('normal');
                                                jQuery('.booking_time_advanced_config').slideUp('normal');
                                                if ( jQuery('#range_selection_time_is_active').length > 0 ) { jQuery('#range_selection_time_is_active').attr('checked', false); }
                                                if ( jQuery('#booking_recurrent_time').length > 0 )         { jQuery('#booking_recurrent_time').attr('checked', false); }
                                                if ( jQuery('#togle_settings_range_times').length > 0 )     { jQuery('#togle_settings_range_times').slideUp('normal'); }
                                                if ( jQuery('#togle_settings_availble_for_cehck_in_out').length > 0 )     { jQuery('#togle_settings_availble_for_cehck_in_out').slideUp('normal'); }

                                                    "
                                            name="type_of_day_selections" id="type_of_day_selections_single" type="radio" 
                                             />
                                        <span><?php _e('Single day' ,'booking');?></span>
                                    </label><br />

                                    <label for="type_of_day_selections_multiple">
                                        <input  value="multiple" <?php if ($type_of_day_selections == 'multiple')  echo 'checked="CHECKED"'; ?> 
                                            onclick="javascript: jQuery('#togle_settings_range_type_selection').slideUp('normal');
                                                jQuery('.booking_time_advanced_config').slideDown('normal');
                                                "
                                            name="type_of_day_selections" id="type_of_day_selections_multiple"  type="radio" 
                                             />
                                        <span><?php _e('Multiple days' ,'booking');?></span>
                                    </label><br />

                                    <?php if (class_exists('wpdev_bk_biz_s')) { ?>
                                    <label for="type_of_day_selections_range">
                                        <input  value="range" <?php if ($type_of_day_selections == 'range')  echo 'checked="CHECKED"'; ?>
                                            onclick="javascript: jQuery('#togle_settings_range_type_selection').slideDown('normal');
                                                jQuery('.booking_time_advanced_config').slideDown('normal'); 
                                                "
                                            name="type_of_day_selections" id="type_of_day_selections_range"  type="radio" 
                                             />
                                        <span><?php _e('Range days' ,'booking');?></span>
                                    </label>
                                    <?php } ?>

                                </fieldset>
                            </td>
                        </tr>

                        <?php do_action('settings_advanced_set_range_selections'); ?>
                        <?php do_action('settings_advanced_set_fixed_time'); ?>
                        <?php if (class_exists('wpdev_bk_biz_s')) {  ?>
                        <tr valign="top" class="booking_time_advanced_config"  style="<?php if ( get_bk_option( 'booking_type_of_day_selections') == 'single' ) { echo 'display:none;'; } ?>"> 
                            <td colspan="2" style="padding:0px 0px 10px; "><div style="border-bottom:1px solid #cccccc;"></div>
                            </td>
                        </tr> 
                        <?php } ?>
                        <?php do_action('settings_set_show_cost_in_tooltips'); ?>
                        <?php do_action('settings_set_show_availability_in_tooltips');  ?>
                        <?php do_action('settings_set_show_time_in_tooltips'); ?>


                    </tbody></table>
            </div></div></div>

            <div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_general_settings_form'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Form' ,'booking'); ?></span></h3> <div class="inside">

                        <table class="form-table"><tbody>
                            <?php // Is using the BootStrap CSS //////////////////////////////////////////////////////////////////////////
                            if (isset( $_POST['start_day_weeek'] )) {
                                if (isset( $_POST['booking_form_is_using_bs_css'] )) $booking_form_is_using_bs_css = 'On';
                                else                                                 $booking_form_is_using_bs_css = 'Off';
                                update_bk_option( 'booking_form_is_using_bs_css',    $booking_form_is_using_bs_css );
                            }
                            $booking_form_is_using_bs_css = get_bk_option( 'booking_form_is_using_bs_css');
                            ?> 
                            <tr valign="top">
                                <th scope="row"><?php _e('CAPTCHA' ,'booking'); ?>:</th>
                                <td><fieldset><label for="is_use_captcha">                                            
                                        <input id="is_use_captcha" type="checkbox" <?php if ($is_use_captcha == 'On') echo "checked"; ?>  value="<?php echo $is_use_captcha; ?>" name="is_use_captcha"/>
                                        <?php _e('Check the box to activate CAPTCHA inside the booking form.' ,'booking');?>
                                    </label>
                                    </fieldset>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php _e('Auto-fill fields' ,'booking'); ?>:</th>
                                <td><fieldset><label for="is_use_autofill_4_logged_user" >
                                        <input id="is_use_autofill_4_logged_user" type="checkbox" <?php if ($is_use_autofill_4_logged_user == 'On') echo "checked"; ?>  value="<?php echo $is_use_autofill_4_logged_user; ?>" name="is_use_autofill_4_logged_user"/>
                                        <?php _e('Check the box to activate auto-fill form fields for logged in users.' ,'booking');?>
                                    </label>
                                    </fieldset>    
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><?php _e('Use CSS BootStrap' ,'booking'); ?>:</th>
                                <td><fieldset><label for="booking_form_is_using_bs_css">
                                        <input type="checkbox" name="booking_form_is_using_bs_css" id="booking_form_is_using_bs_css"
                                            <?php if ($booking_form_is_using_bs_css == 'On') {echo ' checked="checked" ';} ?>  
                                            value="<?php echo $booking_form_is_using_bs_css; ?>" >
                                        <?php _e('Using BootStrap CSS for the form fields' ,'booking'); ?>
                                     </label>
                                     </fieldset>   
                                     <p class="description"><strong><?php _e('Note' ,'booking'); ?>:</strong> <?php _e('You must not deactivate loading BootStrap files at advanced section of these settings!' ,'booking'); ?></p>
                                </td>
                            </tr>                                    


                                    <?php
                                    wpdev_bk_settings_legend_section();
                                    /** ?>

                                    <tr valign="top">
                                        <th scope="row"><label for="is_show_legend" ><?php _e('Show legend' ,'booking'); ?>:</label><br><?php _e('at booking calendar' ,'booking'); ?></th>
                                        <td><input id="is_show_legend" type="checkbox" <?php if ($is_show_legend == 'On') echo "checked"; ?>  value="<?php echo $is_show_legend; ?>" name="is_show_legend"/>
                                            <span class="description"> <?php _e('Check this box to display a legend of dates below the booking calendar.' ,'booking');?></span>
                                        </td>
                                    </tr><?php /**/ ?>

                            <tr valign="top" style="padding: 0px;">
                                <th scope="row"><?php _e('Action after booking is done' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                    <label for="type_of_thank_you_message_message">
                                        <input  <?php if ($type_of_thank_you_message == 'message') echo 'checked="checked"';/**/ ?> 
                                            value="message" type="radio" 
                                            id="type_of_thank_you_message_message"  name="type_of_thank_you_message"  
                                            onclick="javascript: jQuery('#togle_settings_thank-you_page').slideUp('normal');jQuery('#togle_settings_thank-you_message').slideDown('normal');"  />
                                        <span><?php _e('Show "Thank You" message' ,'booking'); ?></span>
                                    </label><br />
                                    <label for="type_of_thank_you_message_page">
                                        <input  <?php if ($type_of_thank_you_message == 'page') echo 'checked="checked"';/**/ ?> 
                                            value="page" type="radio" 
                                            id="type_of_thank_you_message_page"  name="type_of_thank_you_message"  
                                            onclick="javascript: jQuery('#togle_settings_thank-you_page').slideDown('normal');jQuery('#togle_settings_thank-you_message').slideUp('normal');"  />
                                        <span><?php _e('Redirect visitor to a new "Thank You" page' ,'booking'); ?></span>
                                    </label>
                                    </fieldset>
                                    <?php if ( class_exists('wpdev_bk_biz_s') ) { ?>
                                    <p class="description"><strong><?php _e('Note' ,'booking'); ?>:</strong> <?php _e('This action will have no effect, if the payment form(s) is active!' ,'booking'); ?></p>
                                    <?php } ?>
                                </td>
                            </tr>
                            <tr valign="top" style="padding: 0px;"><td colspan="2"  style="padding:0px;">
                                <div style="margin: -10px 0 10px 50px;">

                                <table id="togle_settings_thank-you_message" style="width:100%;<?php if ($type_of_thank_you_message != 'message') echo 'display:none;';/**/ ?>" class="hided_settings_table">
                                    <tr valign="top">
                                        <th><label for="new_booking_title"><?php _e('Message title' ,'booking'); ?>:</label></th>
                                        <td>
                                            <input id="new_booking_title" class="large-text" type="text" value="<?php echo $new_booking_title; ?>" name="new_booking_title" />
                                            <p class="description"><?php printf(__('Type title of message %safter booking has done by user%s' ,'booking'),'<b>','</b>');?></p>                                                
                                        </td>
                                    </tr>
                                    <tr><td colspan="2" style="padding:0px;"><div style="margin-top:-15px;"><?php make_bk_action('show_additional_translation_shortcode_help'); ?></div></td></tr>
                                    <tr>
                                        <th><label for="new_booking_title_time"><?php _e('Time of message showing' ,'booking'); ?>:</label></th>
                                        <td>
                                            <input id="new_booking_title_time" class="small-text" type="text" size="45" value="<?php echo $new_booking_title_time; ?>" name="new_booking_title_time" />
                                            <p class="description"><?php printf(__('Set duration of time (milliseconds) to show this message' ,'booking'),'<b>','</b>');?></p>
                                        </td>
                                    </tr>
                                </table>

                                <table id="togle_settings_thank-you_page" style="width:100%;<?php if ($type_of_thank_you_message != 'page') echo 'display:none;';/**/ ?>" class="hided_settings_table">
                                    <tr valign="top">
                                    <th scope="row"><label for="thank_you_page_URL" ><?php _e('URL of "thank you" page' ,'booking'); ?>:</label></th>
                                        <td>
                                            <fieldset>
                                                <code style="font-size:14px;"><?php echo get_option('siteurl'); ?></code><input value="<?php echo $thank_you_page_URL; ?>" name="thank_you_page_URL" id="thank_you_page_URL" class="large-text" type="text" />
                                            </fieldset>
                                            <p class="description"><?php printf(__('Type URL of %s"Thank You" page%s' ,'booking'),'<b>','</b>');?></p>
                                        </td>
                                    </tr>
                                </table>

                                </div>
                            </td></tr>


                        </tbody></table>

            </div></div></div>

            <div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_general_settings_bktable'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Listing of bookings' ,'booking'); ?></span></h3> <div class="inside">
                        <table class="form-table"><tbody>
                            <tr valign="top">
                                <th scope="row"><label for="bookings_listing_default_view_mode" ><?php _e('Default booking admin page' ,'booking'); ?>:</label></th>
                                <td>

                                    <?php   $wpdevbk_selectors = array(__('Bookings Listing' ,'booking') =>'vm_listing',
                                                                       __('Calendar Overview' ,'booking') =>'vm_calendar'
                                                                      ); ?>
                                    <select id="bookings_listing_default_view_mode" name="bookings_listing_default_view_mode">
                                    <?php foreach ($wpdevbk_selectors as $kk=>$mm) { ?>
                                        <option <?php if($bookings_listing_default_view_mode == strtolower($mm) ) echo "selected"; ?> value="<?php echo strtolower($mm); ?>"><?php echo ($kk) ; ?></option>
                                    <?php } ?>
                                    </select>
                                    <span class="description"><?php _e('Select your default view mode of bookings at the booking listing page' ,'booking');?></span>
                                </td>
                            </tr>

                            <?php make_bk_action('wpdev_bk_general_settings_set_default_booking_resource'); ?>

                            <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>


                            <tr valign="top">
                                <th scope="row"><label for="booking_view_days_num" ><?php _e('Default calendar view mode' ,'booking'); ?>:</label></th>
                                <td><?php   
                                    if (class_exists('wpdev_bk_personal')) {                                                
                                         $wpdevbk_selectors = array(
                                                                    __('Day' ,'booking') =>'1',                                                                                
                                                                    __('Week' ,'booking') =>'7',

                                                                    __('Month' ,'booking') =>'30',

                                                                    __('2 Months' ,'booking') =>'60',

                                                                    __('3 Months' ,'booking') =>'90',
                                                                    __('Year' ,'booking') =>'365'
                                                              );                                                      
                                    } else {
                                         $wpdevbk_selectors = array(
                                                                    __('Month' ,'booking') =>'30',
                                                                    __('3 Months' ,'booking') =>'90',
                                                                    __('Year' ,'booking') =>'365'
                                                              );                                                     
                                    }
                                     ?>
                                    <select id="booking_view_days_num" name="booking_view_days_num" onfocus="javascript:wpdev_bk_recheck_disabled_options();">
                                    <?php foreach ($wpdevbk_selectors as $kk=>$mm) { ?>
                                        <option <?php if($booking_view_days_num == strtolower($mm) ) echo "selected"; ?> value="<?php echo strtolower($mm); ?>"><?php echo ($kk) ; ?></option>
                                    <?php } ?>
                                    </select>
                                    <script type="text/javascript">
                                        // Set the correct  value of this selectbox, depend from the Matrix or Single Calendar Overview 
                                        function wpdev_bk_recheck_disabled_options() {
                                            if ( jQuery('#default_booking_resource').length>0 ) {
                                                jQuery('#default_booking_resource').bind('change', function() {
                                                    jQuery('#booking_view_days_num option:eq(2)').prop("selected", true);
                                                });
                                                if ( jQuery('#default_booking_resource').val() == '' ) { //All resources selected
                                                    jQuery('#booking_view_days_num option:eq(0)').prop("disabled", false);
                                                    jQuery('#booking_view_days_num option:eq(1)').prop("disabled", false);
                                                    jQuery('#booking_view_days_num option:eq(2)').prop("disabled", false);
                                                    jQuery('#booking_view_days_num option:eq(3)').prop("disabled", false);
                                                    jQuery('#booking_view_days_num option:eq(4)').prop("disabled", true);
                                                    jQuery('#booking_view_days_num option:eq(5)').prop("disabled", true);
                                                } else {
                                                    jQuery('#booking_view_days_num option:eq(0)').prop("disabled", true);
                                                    jQuery('#booking_view_days_num option:eq(1)').prop("disabled", true);
                                                    jQuery('#booking_view_days_num option:eq(2)').prop("disabled", false);
                                                    jQuery('#booking_view_days_num option:eq(3)').prop("disabled", true);
                                                    jQuery('#booking_view_days_num option:eq(4)').prop("disabled", false);
                                                    jQuery('#booking_view_days_num option:eq(5)').prop("disabled", false);                                                                
                                                }
                                            }
                                        }
                                    </script>                                                
                                    <span class="description"><?php _e('Select your default calendar view mode at booking calendar overview page' ,'booking');?></span>
                                </td>
                            </tr>

                            <?php make_bk_action('wpdev_bk_general_settings_set_default_title_in_day'); ?>

                            <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>


                            <tr valign="top">
                                <th scope="row"><label for="booking_default_toolbar_tab" ><?php _e('Default toolbar tab' ,'booking'); ?>:</label></th>
                                <td>
                                    <?php   $wpdevbk_selectors = array(__('Filter tab' ,'booking') =>'filter',
                                                                       __('Actions tab' ,'booking') =>'actions'
                                                                      ); ?>
                                    <select id="booking_default_toolbar_tab" name="booking_default_toolbar_tab">
                                    <?php foreach ($wpdevbk_selectors as $kk=>$mm) { ?>
                                        <option <?php if($booking_default_toolbar_tab == strtolower($mm) ) echo "selected"; ?> value="<?php echo strtolower($mm); ?>"><?php echo ($kk) ; ?></option>
                                    <?php } ?>
                                    </select>
                                    <span class="description"><?php _e('Select your default opened tab in toolbar at booking listing page' ,'booking');?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="bookings_num_per_page" ><?php _e('Bookings number per page' ,'booking'); ?>:</label></th>
                                <td>

                                    <?php  $order_array = array( 5, 10, 20, 25, 50, 75, 100 ); ?>
                                    <select id="bookings_num_per_page" name="bookings_num_per_page">
                                    <?php foreach ($order_array as $mm) { ?>
                                        <option <?php if($bookings_num_per_page == strtolower($mm) ) echo "selected"; ?> value="<?php echo strtolower($mm); ?>"><?php echo ($mm) ; ?></option>
                                    <?php } ?>
                                    </select>
                                    <span class="description"><?php _e('Select number of bookings per page in booking listing' ,'booking');?></span>
                                </td>
                            </tr>


                            <tr valign="top">
                                <th scope="row"><label for="booking_sort_order" ><?php _e('Bookings default order' ,'booking'); ?>:</label></th>
                                <td><?php
                                    $order_array = array('ID');
                                    $wpdevbk_selectors = array(__('ID' ,'booking').'&nbsp;'.__('ASC' ,'booking') =>'',
                                                               __('ID' ,'booking').'&nbsp;'.__('DESC' ,'booking') =>'booking_id_asc',
                                       __('Dates' ,'booking').'&nbsp;'.__('ASC' ,'booking') =>'sort_date',
                                       __('Dates' ,'booking').'&nbsp;'.__('DESC' ,'booking') =>'sort_date_asc',
                                      /* __('Cost' ,'booking').'&nbsp;'.__('ASC' ,'booking') =>'cost',
                                       __('Cost' ,'booking').'&nbsp;'.__('DESC' ,'booking') =>'cost_asc',

                                        */
                                      );
                                    if (class_exists('wpdev_bk_personal')) {
                                        $order_array[]= 'Resource';

                                        $wpdevbk_selectors[__('Resource' ,'booking').'&nbsp;'.__('ASC' ,'booking') ] = 'booking_type';
                                        $wpdevbk_selectors[__('Resource' ,'booking').'&nbsp;'.__('DESC' ,'booking')] = 'booking_type_asc';
                                    }
                                    if (class_exists('wpdev_bk_biz_s')) {
                                        $order_array[]= 'Cost';
                                       $wpdevbk_selectors[__('Cost' ,'booking').'&nbsp;'.__('ASC' ,'booking')  ] ='cost';
                                       $wpdevbk_selectors[__('Cost' ,'booking').'&nbsp;'.__('DESC' ,'booking') ] ='cost_asc';
                                    }
                                    ?>
                                    <select id="booking_sort_order" name="booking_sort_order">
                                    <?php foreach ($wpdevbk_selectors as $kk=>$mm) { ?>
                                        <option <?php if($booking_sort_order == strtolower($mm) ) echo "selected"; ?> value="<?php echo strtolower($mm); ?>"><?php echo ($kk) ; ?></option>
                                    <?php } ?>
                                    </select>
                                    <span class="description"><?php _e('Select your default order of bookings in the booking listing' ,'booking');?></span>
                                </td>
                            </tr>

                            <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                            
                            <?php make_bk_action('wpdev_bk_general_settings_export_data_separator'); 
                            
                            if (class_exists('wpdev_bk_personal')) { ?><tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr><?php } ?>
                            
                            <tr valign="top">
                                <th scope="row"><label for="booking_date_format" ><?php _e('Date Format' ,'booking'); ?>:</label></th>
                                <td>
                                <fieldset>
                                    <?php
                                    $date_formats =   array( __('F j, Y'), 'Y/m/d', 'm/d/Y', 'd/m/Y' ) ;
                                    $custom = TRUE;
                                    foreach ( $date_formats as $format ) {
                                        echo "\t<label title='" . esc_attr($format) . "'>";
                                        echo "<input type='radio' name='booking_date_format' value='" . esc_attr($format) . "'";
                                        if ( get_bk_option( 'booking_date_format') === $format ) {
                                            echo " checked='checked'";
                                            $custom = FALSE;
                                        }
                                        echo ' /> ' . date_i18n( $format ) . "</label> &nbsp;&nbsp;&nbsp;\n";
                                    }
                                    echo '<div style="height:7px;"></div>';
                                    echo '<label><input type="radio" name="booking_date_format" id="date_format_custom_radio" value="'. $booking_date_format .'"';
                                    if ( $custom )  echo ' checked="checked"';
                                    echo '/> ' . __('Custom' ,'booking') . ': </label>';?>
                                                                    <input id="booking_date_format_custom" class="regular-text" type="text" size="45" value="<?php echo $booking_date_format; ?>" name="booking_date_format_custom" style="line-height:35px;"
                                                                           onchange="javascript:document.getElementById('date_format_custom_radio').value = this.value;document.getElementById('date_format_custom_radio').checked=true;"
                                                                           />
                                    <?php
                                    echo ' '. date_i18n( $booking_date_format ) . "\n";
                                    echo '&nbsp;&nbsp;';
                                    ?>
                                    <p class="description"><?php printf(__('Type your date format for emails and the booking table. %sDocumentation on date formatting%s' ,'booking'),'<a href="http://codex.wordpress.org/Formatting_Date_and_Time" target="_blank">','</a>');?></p>
                                </fieldset>
                                </td>
                            </tr>

                            <?php do_action('settings_advanced_set_time_format'); ?>

                            <tr valign="top">
                                <th scope="row"><label for="booking_date_view_type" ><?php _e('Dates view' ,'booking'); ?>:</label></th>
                                <td>
                                    <select id="booking_date_view_type" name="booking_date_view_type">
                                        <option <?php if($booking_date_view_type == 'short') echo "selected"; ?> value="short"><?php _e('Short days view' ,'booking'); ?></option>
                                        <option <?php if($booking_date_view_type == 'wide') echo "selected"; ?> value="wide"><?php _e('Wide days view' ,'booking'); ?></option>
                                    </select>
                                    <span class="description"><?php _e('Select the default view for dates on the booking tables' ,'booking');?></span>
                                </td>
                            </tr>

                            <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>

                            <tr valign="top">
                                <th scope="row">
                                    <?php _e('Show / hide hints' ,'booking'); ?>:
                                </th>
                                <td>
                                    <fieldset>
                                        <label for="is_use_hints_at_admin_panel" >
                                            <input id="is_use_hints_at_admin_panel" type="checkbox" <?php if ($is_use_hints_at_admin_panel == 'On') echo "checked"; ?>  value="<?php echo $is_use_hints_at_admin_panel; ?>" name="is_use_hints_at_admin_panel"/>
                                            <?php _e('Check this box if you want to show help hints on the admin panel.' ,'booking');?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>
                            
                        </tbody></table>

            </div></div></div>

            <?php make_bk_action('wpdev_bk_general_settings_cost_section') ?>

            <?php make_bk_action('wpdev_bk_general_settings_pending_auto_cancelation') ?>
            
            <div class='meta-box'>
                <?php /*  // Closed by default ?><div <?php $my_close_open_win_id = 'bk_settings_resources_advanced_options'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '0' !== get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div> <?php /**/ ?>
                <div <?php $my_close_open_win_id = 'bk_settings_resources_advanced_options'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                      <h3 class='hndle'><span><?php _e('Advanced' ,'booking'); ?></span></h3> <div class="inside">
                          <table class="form-table"><tbody>
                                  
                            <?php make_bk_action('wpdev_bk_general_settings_edit_booking_url'); ?>

                            <?php do_action('settings_advanced_set_update_hash_after_approve'); ?>

                            <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                            
                            
                                  
                            <tr valign="top">
                                 <th scope="row"><?php 
                                     $show_untill_version_update = '5.4';  $wpbc_settings_element = 'dismiss_new_booking_check_on_server_if_dates_free'; if ( ( version_compare(WP_BK_VERSION_NUM, $show_untill_version_update ) < 0 ) && ( '1' != get_user_option( 'booking_win_' . $wpbc_settings_element ) ) ) { ?><div id="<?php echo $wpbc_settings_element; ?>"  class="new-label clearfix-height new-label-settings"><a class="tooltip_bottom" data-original-title="<?php _e('Hide' ,'booking'); ?>" rel="tooltip" href="javascript:void(0)"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $wpbc_settings_element; ?>');jQuery('#<?php echo $wpbc_settings_element; ?>').hide();" ><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/label_new_blue.png" style="width:24px; height:24px;"></a></div><?php } /**/ ?> 
                                     <?php _e('Checking to prevent double booking, during submitting booking' ,'booking'); ?>:</th>
                                 <td>
                                    <fieldset>
                                        <label for="booking_check_on_server_if_dates_free" >                     
                                            <input <?php if ($booking_check_on_server_if_dates_free == 'On') echo "checked"; ?>  
                                                value="<?php echo $booking_check_on_server_if_dates_free; ?>"  type="checkbox" 
                                                name="booking_check_on_server_if_dates_free" id="booking_check_on_server_if_dates_free"                             
                                                onclick="javascript: if (this.checked) { var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("This feature can impact to speed of submitting booking. Do you really want to do this?" ,'booking'); ?>'); if ( answer){ this.checked = true; jQuery('#booking_is_days_always_available').prop('checked', false ); } else {this.checked = false;} }"                            
                                              />
                                            <?php printf(__('Check this box, if you want to %sre-check if the selected dates available during submitting booking%s.' ,'booking'), '<strong>', '</strong>' , '<strong>', '</strong>' );?>
                                        </label>
                                    </fieldset>
                                    <span class="description" style="padding:0;"><strong><?php _e('Note' ,'booking'); ?>!</strong> <?php 
                                        _e('This feature useful to prevent double booking of the same date(s) or time(s), if several visitors try to book the same date(s) in same calendar during the same time.' ,'booking');
                                        if ( class_exists('wpdev_bk_biz_l')) { 
                                            echo ' ';
                                            _e('This feature does not work for booking resources with capacity higher than one.' ,'booking');
                                        } ?>                                        
                                    </span>
                                 </td>
                             </tr>
                                  
                            <tr valign="top">
                                 <th scope="row"><?php 
                                        /* $show_untill_version_update = '5.3';  $wpbc_settings_element = 'dismiss_new_booking_is_days_always_available'; if ( ( version_compare(WP_BK_VERSION_NUM, $show_untill_version_update ) < 0 ) && ( '1' != get_user_option( 'booking_win_' . $wpbc_settings_element ) ) ) { ?><div id="<?php echo $wpbc_settings_element; ?>"  class="new-label clearfix-height new-label-settings"><a class="tooltip_bottom" data-original-title="<?php _e('Hide' ,'booking'); ?>" rel="tooltip" href="javascript:void(0)"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $wpbc_settings_element; ?>');jQuery('#<?php echo $wpbc_settings_element; ?>').hide();" ><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/label_new_blue.png" style="width:24px; height:24px;"></a></div><?php } */ ?> 
                                     <?php _e('Allow unlimited bookings per same day(s)' ,'booking'); ?>:</th>
                                 <td>
                                    <fieldset>
                                        <label for="booking_is_days_always_available" >                     
                                            <input <?php if ($booking_is_days_always_available == 'On') echo "checked"; ?>  
                                                value="<?php echo $booking_is_days_always_available; ?>"  type="checkbox" 
                                                name="booking_is_days_always_available" id="booking_is_days_always_available"                             
                                                onclick="javascript: if (this.checked) { var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("You allow unlimited number of bookings per same dates, its can be a reason of double bookings on the same date. Do you really want to do this?" ,'booking'); ?>'); if ( answer){ this.checked = true; jQuery('#booking_check_on_server_if_dates_free').prop('checked', false );jQuery('#booking_is_show_pending_days_as_available').prop('checked', false );jQuery('#togle_settings_show_pending_days_as_available').slideUp('normal'); } else {this.checked = false;} }"                            
                                              />
                                            <?php printf(__('Check this box, if you want to %sset any days as available%s in calendar. Your visitors will be able to make %sunlimited bookings per same date(s) in calendar and do not see any booked date(s)%s of other visitors.' ,'booking'), '<strong>', '</strong>' , '<strong>', '</strong>' );?>
                                        </label>
                                    </fieldset>
                                 </td>
                             </tr>
                                  
                             <?php do_action('wpdev_bk_general_settings_advanced_section') ?>
                             
                             <tr valign="top"><td colspan="2"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
                                 

                            <tr valign="top"> <td colspan="2">
                                <div style="width:100%;">
                                    <span style="color:#21759B;cursor: pointer;font-weight: bold;"
                                       onclick="javascript: jQuery('#togle_settings_javascriptloading').slideToggle('normal');jQuery('.bk_show_advanced_settings_js').toggle('normal');"
                                       style="text-decoration: none;font-weight: bold;font-size: 11px;">
                                         <span class="bk_show_advanced_settings_js">+ <span style="border-bottom:1px dashed #21759B;"><?php _e('Show advanced settings of JavaScript loading' ,'booking'); ?></span></span>
                                         <span class="bk_show_advanced_settings_js" style="display:none;">- <span style="border-bottom:1px dashed #21759B;"><?php _e('Hide advanced settings of JavaScript loading' ,'booking'); ?></span></span>

                                    </span>
                                </div>

                                <table id="togle_settings_javascriptloading" style="display:none;" class="hided_settings_table">

                                    <tr valign="top">
                                        <th scope="row">
                                            <?php _e('Disable Bootstrap loading on Front-End' ,'booking'); ?>:
                                        </th>
                                        <td>
                                            <fieldset>
                                                <label for="is_not_load_bs_script_in_client" >
                                                    <input id="is_not_load_bs_script_in_client" type="checkbox" <?php if ($is_not_load_bs_script_in_client == 'On') echo "checked"; ?>  value="<?php echo $is_not_load_bs_script_in_client; ?>" name="is_not_load_bs_script_in_client"
                                                                                                        onclick="javascript: if (this.checked) { var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("You are need to be sure what you are doing. You are disable of loading some JavaScripts Do you really want to do this?" ,'booking'); ?>'); if ( answer){ this.checked = true; } else {this.checked = false;} }"
                                                           />
                                                    <?php _e(' If your theme or some other plugin is load the BootStrap JavaScripts, you can disable  loading of this script by this plugin.' ,'booking');?>                                                                                                                
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    <tr valign="top">
                                        <th scope="row">
                                            <?php _e('Disable Bootstrap loading on Back-End' ,'booking'); ?>:
                                        </th>
                                        <td>
                                            <fieldset>
                                                <label for="is_not_load_bs_script_in_admin" >
                                                    <input id="is_not_load_bs_script_in_admin" type="checkbox" <?php if ($is_not_load_bs_script_in_admin == 'On') echo "checked"; ?>  value="<?php echo $is_not_load_bs_script_in_admin; ?>" name="is_not_load_bs_script_in_admin"
                                                    onclick="javascript: if (this.checked) { var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("You are need to be sure what you are doing. You are disable of loading some JavaScripts Do you really want to do this?" ,'booking'); ?>'); if ( answer){ this.checked = true; } else {this.checked = false;} }"
                                                       />
                                                    <?php _e(' If your theme or some other plugin is load the BootStrap JavaScripts, you can disable  loading of this script by this plugin.' ,'booking');?>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>

                                    <tr valign="top" style="border-top:1px solid #ccc;">
                                        <th scope="row">
                                            <?php /* $show_untill_version_update = '5.5';  $wpbc_settings_element = 'dismiss_new_booking_is_load_js_css_on_specific_pages'; if ( ( version_compare(WP_BK_VERSION_NUM, $show_untill_version_update ) < 0 ) && ( '1' != get_user_option( 'booking_win_' . $wpbc_settings_element ) ) ) { ?><div id="<?php echo $wpbc_settings_element; ?>"  class="new-label clearfix-height new-label-settings"><a class="tooltip_bottom" data-original-title="<?php _e('Hide' ,'booking'); ?>" rel="tooltip" href="javascript:void(0)"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $wpbc_settings_element; ?>');jQuery('#<?php echo $wpbc_settings_element; ?>').hide();" ><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/label_new_blue.png" style="width:24px; height:24px;"></a></div><?php } /**/ ?> 
                                            <?php _e('Load JS and CSS files only on specific pages' ,'booking'); ?>:
                                        </th>
                                        <td>
                                            <fieldset>
                                                <label for="is_load_js_css_on_specific_pages" >
                                                    <input id="is_load_js_css_on_specific_pages" type="checkbox" <?php if ($is_load_js_css_on_specific_pages == 'On') echo "checked"; ?>  value="<?php echo $is_load_js_css_on_specific_pages; ?>" name="is_load_js_css_on_specific_pages"
                                                    onclick="javascript: if (this.checked) { var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("You are need to be sure what you are doing. You are disable of loading some JavaScripts Do you really want to do this?" ,'booking'); ?>'); if ( answer){ this.checked = true; jQuery('#togle_settings_load_js_css_on_specific_pages').slideDown('normal'); } else {this.checked = false; } } else { jQuery('#togle_settings_load_js_css_on_specific_pages').slideUp('normal'); }"
                                                       />
                                                    <?php _e('Activate loading of CSS and JavaScript files of plugin only at specific pages.' ,'booking'); ?>
                                                </label>
                                            </fieldset>
                                        </td>
                                    </tr>
                                    
                                    <tr valign="top" style="padding: 0px;"><td colspan="2"  style="padding:0px;">                                         
                                        <div style="margin: -10px 0 10px 50px;">
                                            
                                        <?php  if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>    

                                        <table id="togle_settings_load_js_css_on_specific_pages" style="width:100%;<?php 
                                               if ($is_load_js_css_on_specific_pages != 'On') echo 'display:none;';/**/ ?>" class="hided_settings_table">
                                            <tr valign="top" colspan="2">
                                                <th><label for="booking_pages_for_load_js_css"><?php _e('Relative URLs of pages, where to load plugin CSS and JS files' ,'booking'); ?>:</label></th>
                                            </tr>
                                            <tr>
                                                <td colspan="2">
                                                    <textarea id="booking_pages_for_load_js_css" name="booking_pages_for_load_js_css" style="width:100%;" rows="5" ><?php echo $booking_pages_for_load_js_css; ?></textarea>
                                                    <p class="description"><?php printf(__('Enter relative URLs of pages, where you have Booking Calendar elements (booking forms or availability calendars). Please enter one URL per line. Example: %s' ,'booking'),'<code>/booking-form/</code>');?></p>                                                
                                                </td>
                                            </tr>
                                        </table>

                                        </div>
                                    </td></tr>
                                    
                                    
                                </table>
                            </td></tr>


                            <tr valign="top"> <td colspan="2">
                                <div style="width:100%;">
                                    <span style="color:#21759B;cursor: pointer;font-weight: bold;"
                                       onclick="javascript: jQuery('.bk_show_advanced_settings_powered').toggle('normal'); jQuery('#togle_settings_powered').slideToggle('normal');"
                                       style="text-decoration: none;font-weight: bold;font-size: 11px;">
                                         <span class="bk_show_advanced_settings_powered">+ <span style="border-bottom:1px dashed #21759B;"><?php _e('Show settings of powered by notice' ,'booking'); ?></span></span>
                                         <span class="bk_show_advanced_settings_powered" style="display:none;">- <span style="border-bottom:1px dashed #21759B;"><?php _e('Hide settings of powered by notice' ,'booking'); ?></span></span>
                                    </span>
                                </div>

                                <table id="togle_settings_powered" style="display:none;" class="hided_settings_table">

                                        <tr valign="top">
                                            <th scope="row">
                                                <?php _e('Powered by notice' ,'booking'); ?>:</th>
                                            <td>
                                                <fieldset>
                                                    <label for="booking_is_show_powered_by_notice" >
                                                        <input id="booking_is_show_powered_by_notice" type="checkbox" <?php if ($booking_is_show_powered_by_notice == 'On') echo "checked"; ?>  value="<?php echo $booking_is_show_powered_by_notice; ?>" name="booking_is_show_powered_by_notice"/>
                                                        <?php printf(__(' Turn On/Off powered by "Booking Calendar" notice under the calendar.' ,'booking'),'wpbookingcalendar.com');?>
                                                    </label>
                                                </fieldset>

                                            </td>
                                        </tr>


                                        <tr valign="top">
                                            <th scope="row">
                                                <?php _e('Help and info notices' ,'booking'); ?>:
                                            </th>
                                            <td>
                                                <fieldset>
                                                    <label for="wpdev_copyright_adminpanel" >
                                                        <input id="wpdev_copyright_adminpanel" type="checkbox" <?php if ($wpdev_copyright_adminpanel == 'On') echo "checked"; ?>  value="<?php echo $wpdev_copyright_adminpanel; ?>" name="wpdev_copyright_adminpanel"/>
                                                        <?php printf(__(' Turn On/Off version notice and help info links at booking admin panel.' ,'booking'),'wpbookingcalendar.com');?>
                                                    </label>
                                                </fieldset>

                                            </td>
                                        </tr>

                                </table>
                            </td></tr>

                                 
                          </tbody></table>
             </div> </div> </div>
                                  
                                  
                                  
                                  

        </div>
        <div class="booking_settings_row" style="width:35%; float:left;">

            <?php  $version = get_bk_version();
            if ( wpdev_bk_is_this_demo() ) $version = 'free';


            //if ( ($version !== 'free') && ($version!== 'biz_l') ) { wpdev_bk_upgrade_window($version); } ?>

            <div class='meta-box'>
                    <div <?php $my_close_open_win_id = 'bk_general_settings_info'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="gdrgrid postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Information' ,'booking'); ?></span></h3>
                    <div class="inside">
                        <?php make_bk_action('dashboard_bk_widget_show'); ?>
                    </div>
                    </div>
            </div>

            <?php /* if ( (!class_exists('wpdev_crm')) &&  ($version != 'free') ){ ?>
                <div class='meta-box'>
                    <div <?php $my_close_open_win_id = 'bk_general_settings_recomended_plugins'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="gdrgrid postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php _e('Recommended WordPress Plugins' ,'booking'); ?></span></h3>
                        <div class="inside">
                            <h2 style="margin:10px;"><?php _e('Booking Manager - show all old bookings'); ?> </h2>                                

                            <p style="margin:0px;">
                        <?php printf(__('This wordpress plugin is  %sshow all approved and pending bookings from past%s. Show how many each customer is made bookings. Paid versions support %sexport to CSV, print layout, advanced filter%s. ' ,'booking'),'<strong>','</strong>','<strong>','</strong>'); ?> <br/>
                            </p>
                            <p style="text-align:center;padding:10px 0px;">
                                <a href="https://wordpress.org/plugins/booking-manager" class="button-primary" target="_blank">Download from wordpress</a>
                                <a href="http://wpbookingmanager.com" class="button-primary" target="_blank">Demo site</a>
                            </p>
                        </div>
                    </div>
                </div>
            <?php } */ ?>


            <div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_general_settings_users_permissions'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('User permissions for plugin menu pages' ,'booking'); ?></span></h3> <div class="inside">
                    <table class="form-table"><tbody>

                        <tr valign="top">
                            <th scope="row"><label for="user_role_booking" ><?php _e('Bookings' ,'booking'); ?>:</label></th>
                            <td>
                                <select id="user_role_booking" name="user_role_booking">
                                    <option <?php if($user_role_booking == 'subscriber') echo "selected"; ?> value="subscriber" ><?php echo translate_user_role('Subscriber'); ?></option>
                                    <option <?php if($user_role_booking == 'administrator') echo "selected"; ?> value="administrator" ><?php echo translate_user_role('Administrator'); ?></option>
                                    <option <?php if($user_role_booking == 'editor') echo "selected"; ?> value="editor" ><?php echo translate_user_role('Editor'); ?></option>
                                    <option <?php if($user_role_booking == 'author') echo "selected"; ?> value="author" ><?php echo translate_user_role('Author'); ?></option>
                                    <option <?php if($user_role_booking == 'contributor') echo "selected"; ?> value="contributor" ><?php echo translate_user_role('Contributor'); ?></option>
                                </select>                                                
                            </td>
                        </tr>

                        <tr valign="top">
                            <th scope="row"><label for="user_role_addbooking" ><?php _e('Add booking' ,'booking'); ?>:</label></th>
                            <td>
                                <select id="user_role_addbooking" name="user_role_addbooking">
                                    <option <?php if($user_role_addbooking == 'subscriber') echo "selected"; ?> value="subscriber" ><?php echo translate_user_role('Subscriber'); ?></option>
                                    <option <?php if($user_role_addbooking == 'administrator') echo "selected"; ?> value="administrator" ><?php echo translate_user_role('Administrator'); ?></option>
                                    <option <?php if($user_role_addbooking == 'editor') echo "selected"; ?> value="editor" ><?php echo translate_user_role('Editor'); ?></option>
                                    <option <?php if($user_role_addbooking == 'author') echo "selected"; ?> value="author" ><?php echo translate_user_role('Author'); ?></option>
                                    <option <?php if($user_role_addbooking == 'contributor') echo "selected"; ?> value="contributor" ><?php echo translate_user_role('Contributor'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <?php if  ($version !== 'free') { ?>
                            <tr valign="top">
                                <th scope="row"><label for="user_role_resources" ><?php _e('Resources' ,'booking'); ?>:</label></th>
                                <td>
                                    <select id="user_role_resources" name="user_role_resources">
                                        <option <?php if($user_role_resources == 'subscriber') echo "selected"; ?> value="subscriber" ><?php echo translate_user_role('Subscriber'); ?></option>
                                        <option <?php if($user_role_resources == 'administrator') echo "selected"; ?> value="administrator" ><?php echo translate_user_role('Administrator'); ?></option>
                                        <option <?php if($user_role_resources == 'editor') echo "selected"; ?> value="editor" ><?php echo translate_user_role('Editor'); ?></option>
                                        <option <?php if($user_role_resources == 'author') echo "selected"; ?> value="author" ><?php echo translate_user_role('Author'); ?></option>
                                        <option <?php if($user_role_resources == 'contributor') echo "selected"; ?> value="contributor" ><?php echo translate_user_role('Contributor'); ?></option>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>

                        <tr valign="top">
                            <th scope="row"><label for="user_role_settings" ><?php _e('Settings' ,'booking'); ?>:</label></th>
                            <td>
                                <select id="user_role_settings" name="user_role_settings">
                                    <option <?php if($booking_user_role_settings == 'subscriber') echo "selected"; ?> value="subscriber" ><?php echo translate_user_role('Subscriber'); ?></option>
                                    <option <?php if($booking_user_role_settings == 'administrator') echo "selected"; ?> value="administrator" ><?php echo translate_user_role('Administrator'); ?></option>
                                    <option <?php if($booking_user_role_settings == 'editor') echo "selected"; ?> value="editor" ><?php echo translate_user_role('Editor'); ?></option>
                                    <option <?php if($booking_user_role_settings == 'author') echo "selected"; ?> value="author" ><?php echo translate_user_role('Author'); ?></option>
                                    <option <?php if($booking_user_role_settings == 'contributor') echo "selected"; ?> value="contributor" ><?php echo translate_user_role('Contributor'); ?></option>
                                </select>                                    
                            </td>
                        </tr>

                        <tr valign="top">
                            <td colspan="2">
                                <?php if ( wpdev_bk_is_this_demo() ) { ?> <div class="wpbc-error-message" style="text-align:left;"> <span class="wpbc-demo-alert-not-allow"><strong>Warning!</strong> Demo test version does not allow changes to these items.</span></div> <?php } ?>
                                <p class="description"><?php _e('Select user access level for the menu pages of plugin' ,'booking');?></p>
                            </td>
                        </tr>

                    </tbody></table>
            </div></div></div>


            <div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_general_settings_uninstall'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Uninstall / deactivation' ,'booking'); ?></span></h3> <div class="inside">
                        <table class="form-table"><tbody>

                            <tr valign="top">
                                <th scope="row"><?php _e('Delete booking data, when plugin deactivated' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_delete_if_deactive">
                                            <input id="is_delete_if_deactive" type="checkbox" <?php if ($is_delete_if_deactive == 'On') echo "checked"; ?>  value="<?php echo $is_delete_if_deactive; ?>" name="is_delete_if_deactive"
                                                onclick="javascript: if (this.checked) { var answer = confirm('<?php  _e('Warning' ,'booking'); echo '! '; _e("If you check this option, all booking data will be deleted when you uninstall this plugin. Do you really want to do this?" ,'booking'); ?>'); if ( answer){ this.checked = true; } else {this.checked = false;} }"
                                                   />
                                            <?php _e('Check this box to delete all booking data when you uninstal this plugin.' ,'booking');?>
                                        </label>
                                    </fieldset>
                                </td>
                            </tr>

                        </tbody></table>
            </div></div></div>

            <?php make_bk_action('wpdev_booking_technical_booking_section'); ?>

        </div>

        <div class="clear" style="height:10px;"></div>
        <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="Submit"/>
        <div class="clear" style="height:10px;"></div>
    </form>
    </div>    
    <?php    
}

// Show window for upgrading
function wpdev_bk_upgrade_window($version) {
    if ( ! wpdev_bk_is_this_demo() ) {
    ?>
        <div class='meta-box'>
            <div <?php $my_close_open_win_id = 'bk_general_settings_upgrade'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="gdrgrid postbox <?php //if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <?php /*<div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>*/ ?>
            <h3 class='hndle'><span><span><?php echo 'Upgrade to '; if ( ($version == 'personal') ) { echo 'Business Small /'; } if (in_array($version, array('personal','biz_s') )) { echo 'Business Medium /'; } if (in_array($version, array('personal','biz_s','biz_m') )) { echo 'Business Large /'; } echo ' MultiUser'; ?></span></span></h3>
            <div class="inside">

                <div style="width:100%;border:none; clear:both;margin:10px 0px;" id="bk_news_section"> 
                        <div id="bk_news"><span style="font-size:11px;text-align:center;">Loading...</span></div>
                        <div id="ajax_bk_respond" style="display:none;"></div>
                                <?php /*
                                $response = wp_remote_post( OBC_CHECK_URL . 'info/', array() );

                                if (! ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) ) {

                                    $body_to_show = json_decode( wp_remote_retrieve_body( $response ) );

                                    ?><!--style type="text/css" media="screen">#bk_news_loaded{display:block !important;}</style--><?php

                                    echo $body_to_show ;
                                }*/
                                ?>                    
                        <script type="text/javascript">
                            jQuery.ajax({                                           // Start Ajax Sending
                                // url: '<?php echo WPDEV_BK_PLUGIN_URL , '/' ,  WPDEV_BK_PLUGIN_FILENAME ; ?>' ,
                                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                                type:'POST',
                                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_bk_respond').html( data );},
                                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;
                                },                            
                                data:{
                                    // ajax_action : 'CHECK_BK_FEATURES',
                                    action : 'CHECK_BK_FEATURES',
                                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value         
                                }
                            });
                        </script>                           
                </div>
                <p style="line-height:25px;text-align:center;padding-top:15px;" class="wpdevbk">                    
                    <a class="button button-primary" style="font-size: 1.1em;font-weight: bold;height: 2.5em;line-height: 1.1em;padding: 8px 25px;"  href="<?php echo wpbc_up_link(); ?>" target="_blank"><?php if ( wpbc_get_ver_sufix() == '' ) { _e('Purchase' ,'booking'); } else { _e('Upgrade Now' ,'booking'); } ?></a>
                </p>    

            </div>
            </div>
        </div>
     <?php
    }
}

// Settings for selecting default booking resource
function wpdev_bk_settings_legend_section(){
        if (isset($_POST['booking_legend_text_for_item_available'])) {

            if (isset( $_POST['booking_is_show_legend'] ))      $booking_is_show_legend = 'On';
            else                                                $booking_is_show_legend = 'Off';
            update_bk_option( 'booking_is_show_legend' ,        $booking_is_show_legend );

            if (isset( $_POST['booking_legend_is_show_item_available'] ))   $booking_legend_is_show_item_available = 'On';
            else                                                            $booking_legend_is_show_item_available = 'Off';
            update_bk_option( 'booking_legend_is_show_item_available' ,     $booking_legend_is_show_item_available );
            update_bk_option( 'booking_legend_text_for_item_available' ,  $_POST['booking_legend_text_for_item_available'] );

            if (isset( $_POST['booking_legend_is_show_item_pending'] ))   $booking_legend_is_show_item_pending = 'On';
            else                                                            $booking_legend_is_show_item_pending = 'Off';
            update_bk_option( 'booking_legend_is_show_item_pending' ,     $booking_legend_is_show_item_pending );
            update_bk_option( 'booking_legend_text_for_item_pending' ,  $_POST['booking_legend_text_for_item_pending'] );

            if (isset( $_POST['booking_legend_is_show_item_approved'] ))   $booking_legend_is_show_item_approved = 'On';
            else                                                            $booking_legend_is_show_item_approved = 'Off';
            update_bk_option( 'booking_legend_is_show_item_approved' ,     $booking_legend_is_show_item_approved );
            update_bk_option( 'booking_legend_text_for_item_approved' ,  $_POST['booking_legend_text_for_item_approved'] );

            
            update_bk_option( 'booking_legend_is_show_numbers' ,    (isset( $_POST['booking_legend_is_show_numbers'] )) ? 'On' : 'Off' );   //FixIn:6.0.1.4
            
            
            if ( class_exists('wpdev_bk_biz_s') ) {
                if (isset( $_POST['booking_legend_is_show_item_partially'] ))   $booking_legend_is_show_item_partially = 'On';
                else                                                            $booking_legend_is_show_item_partially = 'Off';
                update_bk_option( 'booking_legend_is_show_item_partially' ,     $booking_legend_is_show_item_partially );
                update_bk_option( 'booking_legend_text_for_item_partially' ,  $_POST['booking_legend_text_for_item_partially'] );
            }
        }
        
        $booking_legend_is_show_numbers = get_bk_option( 'booking_legend_is_show_numbers');                              //FixIn:6.0.1.4
        
        $booking_is_show_legend   = get_bk_option( 'booking_is_show_legend');

        $booking_legend_is_show_item_available    = get_bk_option( 'booking_legend_is_show_item_available');
        $booking_legend_text_for_item_available   = get_bk_option( 'booking_legend_text_for_item_available');

        $booking_legend_is_show_item_pending    = get_bk_option( 'booking_legend_is_show_item_pending');
        $booking_legend_text_for_item_pending   = get_bk_option( 'booking_legend_text_for_item_pending');

        $booking_legend_is_show_item_approved    = get_bk_option( 'booking_legend_is_show_item_approved');
        $booking_legend_text_for_item_approved   = get_bk_option( 'booking_legend_text_for_item_approved');

        if ( class_exists('wpdev_bk_biz_s') ) {                
            $booking_legend_is_show_item_partially    = get_bk_option( 'booking_legend_is_show_item_partially');
            $booking_legend_text_for_item_partially   = get_bk_option( 'booking_legend_text_for_item_partially');
        }
     ?>
           <tr valign="top" class="ver_premium_plus">
                <th scope="row">
                    <?php _e('Show legend below calendar' ,'booking'); ?>:
                </th>
                <td>
                    <fieldset>
                    <label for="booking_is_show_legend" >
                        <input <?php if ($booking_is_show_legend == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_show_legend; ?>" name="booking_is_show_legend" id="booking_is_show_legend" type="checkbox"
                             onclick="javascript: if (this.checked) jQuery('#togle_settings_show_legend').slideDown('normal'); else  jQuery('#togle_settings_show_legend').slideUp('normal');"
                                                                                                      />
                        <?php _e('Check this box to display a legend of dates below the booking calendar.' ,'booking');?>
                    </label>
                    </fieldset>    
                </td>
            </tr>

            <tr valign="top" class="ver_premium_plus"><td colspan="2"  style="padding:0px;">
                <div style="margin: 0px 0 10px 50px;">
                <table id="togle_settings_show_legend" style="<?php if ($booking_is_show_legend != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                    <tr>
                        <th scope="row"><label for="booking_legend_is_show_item_available" ><?php _e('Available item' ,'booking'); ?>:</label></th>
                        <td>
                            <input <?php if ($booking_legend_is_show_item_available == 'On') echo "checked"; ?>  type="checkbox"
                                value="<?php echo $booking_legend_is_show_item_available; ?>"
                                name="booking_legend_is_show_item_available"  id="booking_legend_is_show_item_available"  />&nbsp;
                            <input value="<?php echo $booking_legend_text_for_item_available; ?>" name="booking_legend_text_for_item_available" id="booking_legend_text_for_item_available" type="text"    />
                            <span class="description"><?php printf(__('Activate and type your %stitle of available%s item in legend' ,'booking'),'<b>','</b>');?></span>
                            <?php //make_bk_action('show_additional_translation_shortcode_help'); ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="booking_legend_is_show_item_pending" ><?php _e('Pending item' ,'booking'); ?>:</label></th>
                        <td>
                            <input <?php if ($booking_legend_is_show_item_pending == 'On') echo "checked"; ?>  type="checkbox"
                                value="<?php echo $booking_legend_is_show_item_pending; ?>"
                                name="booking_legend_is_show_item_pending"  id="booking_legend_is_show_item_pending"  />&nbsp;
                            <input value="<?php echo $booking_legend_text_for_item_pending; ?>" name="booking_legend_text_for_item_pending" id="booking_legend_text_for_item_pending" type="text"    />
                            <span class="description"><?php printf(__('Activate and type your %stitle of pending%s item in legend' ,'booking'),'<b>','</b>');?></span>
                            <?php //make_bk_action('show_additional_translation_shortcode_help'); ?>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><label for="booking_legend_is_show_item_approved" ><?php _e('Approved item' ,'booking'); ?>:</label></th>
                        <td>
                            <input <?php if ($booking_legend_is_show_item_approved == 'On') echo "checked"; ?>  type="checkbox"
                                value="<?php echo $booking_legend_is_show_item_approved; ?>"
                                name="booking_legend_is_show_item_approved"  id="booking_legend_is_show_item_approved"  />&nbsp;
                            <input value="<?php echo $booking_legend_text_for_item_approved; ?>" name="booking_legend_text_for_item_approved" id="booking_legend_text_for_item_approved" type="text"    />
                            <span class="description"><?php printf(__('Activate and type your %stitle of approved%s item in legend' ,'booking'),'<b>','</b>');?></span>
                            <?php //make_bk_action('show_additional_translation_shortcode_help'); ?>
                        </td>
                    </tr>
                    <?php if ( class_exists('wpdev_bk_biz_s') ) { ?>
                        <tr>
                            <th scope="row"><label for="booking_legend_is_show_item_partially" ><?php _e('Partially booked item' ,'booking'); ?>:</label></th>
                            <td>
                                <input <?php if ($booking_legend_is_show_item_partially == 'On') echo "checked"; ?>  type="checkbox"
                                    value="<?php echo $booking_legend_is_show_item_partially; ?>"
                                    name="booking_legend_is_show_item_partially"  id="booking_legend_is_show_item_partially"  />&nbsp;
                                <input value="<?php echo $booking_legend_text_for_item_partially; ?>" name="booking_legend_text_for_item_partially" id="booking_legend_text_for_item_partially" type="text"    />
                                <span class="description"><?php printf(__('Activate and type your %stitle of partially booked%s item in legend' ,'booking'),'<b>','</b>');?></span>
                                <p class="description"><strong><?php _e('Note' ,'booking'); ?>:</strong> <?php printf(__('Partially booked item - day, which is booked for the specific time-slot(s).' ,'booking'),'<b>','</b>');?></p>
                                <?php //make_bk_action('show_additional_translation_shortcode_help'); ?>
                            </td>
                        </tr>
                    <?php } ?>
                    <tr><td colspan="2" style="padding:0px;"><div style="margin-top:-15px;"><?php make_bk_action('show_additional_translation_shortcode_help'); ?></div></td></tr>
                    <tr><?php //FixIn:6.0.1.4 ?>
                        <th scope="row"><label for="booking_legend_is_show_numbers" ><?php _e('Show date number in legend' ,'booking'); ?>:</label></th>
                        <td>
                            <input <?php if ($booking_legend_is_show_numbers != 'Off') echo "checked"; ?>  type="checkbox"
                                value="<?php echo $booking_legend_is_show_numbers; ?>"
                                name="booking_legend_is_show_numbers"  id="booking_legend_is_show_numbers"  />
                            <span class="description"><?php printf(__('Check this box to display today date number in legend cells. ' ,'booking'),'<b>','</b>');?></span>
                        </td>
                    </tr>
                    
                </table>
                </div>
            </td></tr>
            <tr valign="top"><td colspan="2" style="padding:10px"><div style="border-bottom:1px solid #cccccc;"></div></td></tr>
    <?php
}


function wpdev_bk_settings_form_labels(){ 
    ?>
    <div class="clear" style="height:0px;"></div>
    <div id="ajax_working"></div>
    <div id="poststuff0" class="metabox-holder">
        <form  name="post_settings_form_fields" action="" method="post" id="post_settings_form_fields" class="form-horizontal">

          <div id="visibility_container_form_fields" class="visibility_container wpdevbk wpbc_settings_form_fields_free" style="display:block;">
            <div class='meta-box'>
                <div <?php $my_close_open_win_id = 'bk_settings_form_fields'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Form fields labels' ,'booking'); ?></span></h3><div class="inside">


                        <?php // FIELD # 1 //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (isset( $_POST['Submit'] )) {

                            if (isset( $_POST['booking_form_field_active1'] )) $booking_form_field_active1 = 'On';
                            else                                               $booking_form_field_active1 = 'Off';
                            update_bk_option( 'booking_form_field_active1',    $booking_form_field_active1 );

                            if (isset( $_POST['booking_form_field_required1'] )) $booking_form_field_required1 = 'On';
                            else                                                 $booking_form_field_required1 = 'Off';
                            update_bk_option( 'booking_form_field_required1',    $booking_form_field_required1 );

                            update_bk_option( 'booking_form_field_label1',    $_POST['booking_form_field_label1'] );
                        }
                        $booking_form_field_active1 = get_bk_option( 'booking_form_field_active1');
                        $booking_form_field_required1 = get_bk_option( 'booking_form_field_required1');
                        $booking_form_field_label1 = get_bk_option( 'booking_form_field_label1');

                        ?>
                        
                        
                        <div class="control-group">
                          <label for="name" class="control-label" style="font-weight:bold;"><?php _e('Field Label' ,'booking'); ?> #1:</label>
                          <div class="controls"> 

                            <input type="text" class="large-text" 
                                   name="booking_form_field_label1" value="<?php echo $booking_form_field_label1; ?>">&nbsp;&nbsp;&nbsp;

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_active1"
                                    <?php if ($booking_form_field_active1 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_active1; ?>" >
                                <?php _e('Active' ,'booking'); ?>
                            </label> 

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_required1"
                                    <?php if ($booking_form_field_required1 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_required1; ?>" >
                                <?php _e('Required' ,'booking'); ?>
                            </label> 

                            <p class="help-block"><?php _e('Activate or deactivate field and change the label title' ,'booking'); ?></p>
                          </div>
                        </div>

                        <?php // FIELD # 2 //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (isset( $_POST['Submit'] )) {

                            if (isset( $_POST['booking_form_field_active2'] )) $booking_form_field_active2 = 'On';
                            else                                               $booking_form_field_active2 = 'Off';
                            update_bk_option( 'booking_form_field_active2',    $booking_form_field_active2 );

                            if (isset( $_POST['booking_form_field_required2'] )) $booking_form_field_required2 = 'On';
                            else                                                 $booking_form_field_required2 = 'Off';
                            update_bk_option( 'booking_form_field_required2',    $booking_form_field_required2 );

                            update_bk_option( 'booking_form_field_label2',    $_POST['booking_form_field_label2'] );
                        }
                        $booking_form_field_active2 = get_bk_option( 'booking_form_field_active2');
                        $booking_form_field_required2 = get_bk_option( 'booking_form_field_required2');
                        $booking_form_field_label2 = get_bk_option( 'booking_form_field_label2');

                        ?>
                        <div class="control-group">
                          <label for="name" class="control-label" style="font-weight:bold;"><?php _e('Field Label' ,'booking'); ?> #2:</label>
                          <div class="controls"> 

                            <input type="text" class="large-text" 
                                   name="booking_form_field_label2" value="<?php echo $booking_form_field_label2; ?>">&nbsp;&nbsp;&nbsp;

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_active2"
                                    <?php if ($booking_form_field_active2 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_active2; ?>" >
                                <?php _e('Active' ,'booking'); ?>
                            </label> 

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_required2"
                                    <?php if ($booking_form_field_required2 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_required2; ?>" >
                                <?php _e('Required' ,'booking'); ?>
                            </label> 

                            <p class="help-block"><?php _e('Activate or deactivate field and change the label title' ,'booking'); ?></p>
                          </div>
                        </div>

                        <?php // FIELD # 3 //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (isset( $_POST['Submit'] )) {

                            //if (isset( $_POST['booking_form_field_active3'] )) $booking_form_field_active3 = 'On';
                            //else                                               $booking_form_field_active3 = 'Off';
                            $booking_form_field_active3 = 'On';
                            update_bk_option( 'booking_form_field_active3',    $booking_form_field_active3 );

                            //if (isset( $_POST['booking_form_field_required3'] )) $booking_form_field_required3 = 'On';
                            //else                                                 $booking_form_field_required3 = 'Off';
                            $booking_form_field_required3 = 'On';
                            update_bk_option( 'booking_form_field_required3',    $booking_form_field_required3 );

                            update_bk_option( 'booking_form_field_label3',    $_POST['booking_form_field_label3'] );
                        }
                        $booking_form_field_active3 = get_bk_option( 'booking_form_field_active3');
                        $booking_form_field_required3 = get_bk_option( 'booking_form_field_required3');
                        $booking_form_field_label3 = get_bk_option( 'booking_form_field_label3');

                        ?>
                        <div class="control-group">
                          <label for="name" class="control-label" style="font-weight:bold;"><?php _e('Email Label' ,'booking'); ?>:</label>
                          <div class="controls"> 

                            <input type="text" class="large-text" 
                                   name="booking_form_field_label3" value="<?php echo $booking_form_field_label3; ?>">&nbsp;&nbsp;&nbsp;

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_active3" disabled=""
                                    <?php if (($booking_form_field_active3 == 'On') || (true)) {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_active3; ?>" >
                                <?php _e('Active' ,'booking'); ?>
                            </label> 

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_required3" disabled=""
                                    <?php if (($booking_form_field_required3 == 'On') || (true)) {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_required3; ?>" >
                                <?php _e('Required' ,'booking'); ?>
                            </label> 

                            <p class="help-block"><?php _e('Change the label title of this field. Email is obligatory field in booking form.' ,'booking'); ?></p>
                          </div>
                        </div>

                        <?php // FIELD # 6 - SELECT BOX /////////////////////////////////////////////////////////////////////////////////////////////
                        if (isset( $_POST['Submit'] )) {

                            if (isset( $_POST['booking_form_field_active6'] )) $booking_form_field_active6 = 'On';
                            else                                               $booking_form_field_active6 = 'Off';
                            update_bk_option( 'booking_form_field_active6',    $booking_form_field_active6 );

                            if (isset( $_POST['booking_form_field_required6'] )) $booking_form_field_required6 = 'On';
                            else                                                 $booking_form_field_required6 = 'Off';
                            update_bk_option( 'booking_form_field_required6',    $booking_form_field_required6 );

                            update_bk_option( 'booking_form_field_label6',    $_POST['booking_form_field_label6'] );
                            update_bk_option( 'booking_form_field_values6',   $_POST['booking_form_field_values6'] );
                        }
                        $booking_form_field_active6 = get_bk_option( 'booking_form_field_active6');
                        $booking_form_field_required6 = get_bk_option( 'booking_form_field_required6');
                        $booking_form_field_label6 = get_bk_option( 'booking_form_field_label6');
                        $booking_form_field_values6 = get_bk_option( 'booking_form_field_values6');
                        
                        
                        $show_untill_version_update = '5.4';  $wpbc_settings_element = 'dismiss_new_selectbox_field_free'; if ( ( version_compare(WP_BK_VERSION_NUM, $show_untill_version_update ) < 0 ) && ( '1' != get_user_option( 'booking_win_' . $wpbc_settings_element ) ) ) { ?><div id="<?php echo $wpbc_settings_element; ?>"  class="new-label clearfix-height new-label-settings" style="margin-left: -31px;"><a class="tooltip_bottom" data-original-title="<?php _e('Hide' ,'booking'); ?>" rel="tooltip" href="javascript:void(0)"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $wpbc_settings_element; ?>');jQuery('#<?php echo $wpbc_settings_element; ?>').hide();" ><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/label_new_blue.png" style="width:24px; height:24px;"></a></div><?php } /**/ ?> 
                        <div class="control-group">
                          <label for="booking_form_field_label6" class="control-label" style="font-weight:bold;"><?php _e('Selectbox Label' ,'booking'); ?> :</label>
                          <div class="controls"> 
                            <div style="float:left;">  
                                <input type="text" class="large-text" 
                                       name="booking_form_field_label6" id="booking_form_field_label6" value="<?php echo $booking_form_field_label6; ?>">&nbsp;&nbsp;&nbsp;
                                <br/>
                                <label class="checkbox inline">
                                    <input type="checkbox"  name="booking_form_field_active6"
                                        <?php if ($booking_form_field_active6 == 'On') {echo ' checked="checked" ';} ?>  
                                        value="<?php echo $booking_form_field_active6; ?>" >
                                    <?php _e('Active' ,'booking'); ?>
                                </label> 

                                <label class="checkbox inline">
                                    <input type="checkbox"  name="booking_form_field_required6"
                                        <?php if ($booking_form_field_required6 == 'On') {echo ' checked="checked" ';} ?>  
                                        value="<?php echo $booking_form_field_required6; ?>" >
                                    <?php _e('Required' ,'booking'); ?>
                                </label> 
                                
                            </div>    
                            <div style="float:left;margin-top:-23px;">  
                                <label for="booking_form_field_values6" class="control-label" style="font-weight:bold;"><?php 
                                    _e('Selectbox Values' ,'booking');  ?> :
                                </label>
                                <textarea name="booking_form_field_values6" id="booking_form_field_values6" rows="3" ><?php echo $booking_form_field_values6; ?></textarea>
                                
                            </div>
                            <div class="clear"></div>
                            <p class="help-block"><?php _e('Activate or deactivate field and change the label title' ,'booking'); ?></p>
                            <p class="help-block"><?php _e('Enter dropdown options. One option per line.' ,'booking'); ?></p>
                          </div>
                        </div>
                        
                        
                        <?php // FIELD # 4 //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (isset( $_POST['Submit'] )) {

                            if (isset( $_POST['booking_form_field_active4'] )) $booking_form_field_active4 = 'On';
                            else                                               $booking_form_field_active4 = 'Off';
                            update_bk_option( 'booking_form_field_active4',    $booking_form_field_active4 );

                            if (isset( $_POST['booking_form_field_required4'] )) $booking_form_field_required4 = 'On';
                            else                                                 $booking_form_field_required4 = 'Off';
                            update_bk_option( 'booking_form_field_required4',    $booking_form_field_required4 );

                            update_bk_option( 'booking_form_field_label4',    $_POST['booking_form_field_label4'] );
                        }
                        $booking_form_field_active4 = get_bk_option( 'booking_form_field_active4');
                        $booking_form_field_required4 = get_bk_option( 'booking_form_field_required4');
                        $booking_form_field_label4 = get_bk_option( 'booking_form_field_label4');

                        ?>
                        <div class="control-group">
                          <label for="name" class="control-label" style="font-weight:bold;"><?php _e('Field Label' ,'booking'); ?> #3:</label>
                          <div class="controls"> 

                            <input type="text" class="large-text" 
                                   name="booking_form_field_label4" value="<?php echo $booking_form_field_label4; ?>">&nbsp;&nbsp;&nbsp;

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_active4"
                                    <?php if ($booking_form_field_active4 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_active4; ?>" >
                                <?php _e('Active' ,'booking'); ?>
                            </label> 

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_required4"
                                    <?php if ($booking_form_field_required4 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_required4; ?>" >
                                <?php _e('Required' ,'booking'); ?>
                            </label> 

                            <p class="help-block"><?php _e('Activate or deactivate field and change the label title' ,'booking'); ?></p>
                          </div>
                        </div>

                        <?php // FIELD # 5 //////////////////////////////////////////////////////////////////////////////////////////////////////////
                        if (isset( $_POST['Submit'] )) {

                            if (isset( $_POST['booking_form_field_active5'] )) $booking_form_field_active5 = 'On';
                            else                                               $booking_form_field_active5 = 'Off';
                            update_bk_option( 'booking_form_field_active5',    $booking_form_field_active5 );

                            if (isset( $_POST['booking_form_field_required5'] )) $booking_form_field_required5 = 'On';
                            else                                                 $booking_form_field_required5 = 'Off';
                            update_bk_option( 'booking_form_field_required5',    $booking_form_field_required5 );

                            update_bk_option( 'booking_form_field_label5',    $_POST['booking_form_field_label5'] );
                        }
                        $booking_form_field_active5 = get_bk_option( 'booking_form_field_active5');
                        $booking_form_field_required5 = get_bk_option( 'booking_form_field_required5');
                        $booking_form_field_label5 = get_bk_option( 'booking_form_field_label5');

                        ?>
                        <div class="control-group">
                          <label for="name" class="control-label" style="font-weight:bold;"><?php _e('Textarea Label' ,'booking'); ?>:</label>
                          <div class="controls"> 

                            <input type="text" class="large-text" 
                                   name="booking_form_field_label5" value="<?php echo $booking_form_field_label5; ?>">&nbsp;&nbsp;&nbsp;

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_active5"
                                    <?php if ($booking_form_field_active5 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_active5; ?>" >
                                <?php _e('Active' ,'booking'); ?>
                            </label> 

                            <label class="checkbox inline">
                                <input type="checkbox"  name="booking_form_field_required5"
                                    <?php if ($booking_form_field_required5 == 'On') {echo ' checked="checked" ';} ?>  
                                    value="<?php echo $booking_form_field_required5; ?>" >
                                <?php _e('Required' ,'booking'); ?>
                            </label> 

                            <p class="help-block"><?php _e('Activate or deactivate field and change the label title' ,'booking'); ?></p>
                          </div>
                        </div>


             </div></div></div>

            <div <?php $my_close_open_alert_id = 'bk_alert_settings_form_in_free'; ?>
                class="alert alert-block alert-info <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_alert_id ) ) echo 'closed'; ?>"                             
                id="<?php echo $my_close_open_alert_id; ?>">
              <a class="close tooltip_left" rel="tooltip" title="Don't show the message anymore" data-dismiss="alert" href="javascript:void(0)"
                 onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_alert_id; ?>');"
                 >&times;</a>
              <strong class="alert-heading">Note!</strong>
                  Check how in <a href="http://wpbookingcalendar.com/overview/" target="_blank" style="text-decoration:underline;">other versions of Booking Calendar</a> possible fully <a href="http://wpbookingcalendar.com/help/booking-form-fields/" target="_blank" style="text-decoration:underline;">customize the booking form</a> <em>(add or remove fields, configure time-slots, change structure of booking form, etc...)</em>
            </div>

          </div>

          <input class="button-primary button" style="" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="Submit" />
          <div class="clear" style="height:10px;"></div>                  
        </form>
    </div>
 <?php
}    


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  Email settings section                   ///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Emails Settings
function wpbc_settings_emails(){

     if ( isset( $_POST['email_reservation_adress'] ) ) {

         $email_reservation_adress      = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_adress']));
         $email_reservation_from_adress = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_from_adress']));
         $email_reservation_subject     = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_subject']));
         $email_reservation_content     =  str_replace('\"','"',$_POST['email_reservation_content']) ;

         $email_reservation_adress      =  str_replace("\'","'",$email_reservation_adress);
         $email_reservation_from_adress =  str_replace("\'","'",$email_reservation_from_adress);
         $email_reservation_subject     =  str_replace("\'","'",$email_reservation_subject);
         $email_reservation_content     =  str_replace("\'","'",$email_reservation_content);


         if (isset( $_POST['is_email_reservation_adress'] ))         $is_email_reservation_adress = 'On';
         else                                                        $is_email_reservation_adress = 'Off';
         update_bk_option( 'booking_is_email_reservation_adress' , $is_email_reservation_adress );

         if ( get_bk_option( 'booking_email_reservation_adress' ) !== false  )      update_bk_option( 'booking_email_reservation_adress' , $email_reservation_adress );
         else                                                                    add_bk_option( 'booking_email_reservation_adress' , $email_reservation_adress );
         if ( get_bk_option( 'booking_email_reservation_from_adress' ) !== false  ) update_bk_option( 'booking_email_reservation_from_adress' , $email_reservation_from_adress );
         else                                                                    add_bk_option( 'booking_email_reservation_from_adress' , $email_reservation_from_adress );
         if ( get_bk_option( 'booking_email_reservation_subject' ) !== false  )     update_bk_option( 'booking_email_reservation_subject' , $email_reservation_subject );
         else                                                                    add_bk_option( 'booking_email_reservation_subject' , $email_reservation_subject );
         if ( get_bk_option( 'booking_email_reservation_content' ) !== false  )     update_bk_option( 'booking_email_reservation_content' , $email_reservation_content );
         else                                                                    add_bk_option( 'booking_email_reservation_content' , $email_reservation_content );
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         $email_approval_adress  = htmlspecialchars( str_replace('\"','"',$_POST['email_approval_adress']));
         $email_approval_subject = htmlspecialchars( str_replace('\"','"',$_POST['email_approval_subject']));
         $email_approval_content =  str_replace('\"','"',$_POST['email_approval_content']);

         $email_approval_adress      =  str_replace("\'","'",$email_approval_adress);
         $email_approval_subject     =  str_replace("\'","'",$email_approval_subject);
         $email_approval_content     =  str_replace("\'","'",$email_approval_content);



         if (isset( $_POST['is_email_approval_adress'] ))            $is_email_approval_adress = 'On';
         else                                               $is_email_approval_adress = 'Off';
         update_bk_option( 'booking_is_email_approval_adress' , $is_email_approval_adress );

         if (isset( $_POST['is_email_approval_send_copy_to_admin'] ))            $is_email_approval_send_copy_to_admin = 'On';
         else                                               $is_email_approval_send_copy_to_admin = 'Off';
         update_bk_option( 'booking_is_email_approval_send_copy_to_admin' , $is_email_approval_send_copy_to_admin );



         if ( get_bk_option( 'booking_email_approval_adress' ) !== false  )         update_bk_option( 'booking_email_approval_adress' , $email_approval_adress );
         else                                                                    add_bk_option( 'booking_email_approval_adress' , $email_approval_adress );
         if ( get_bk_option( 'booking_email_approval_subject' ) !== false  )        update_bk_option( 'booking_email_approval_subject' , $email_approval_subject );
         else                                                                    add_bk_option( 'booking_email_approval_subject' , $email_approval_subject );
         if ( get_bk_option( 'booking_email_approval_content' ) !== false  )        update_bk_option( 'booking_email_approval_content' , $email_approval_content );
         else                                                                    add_bk_option( 'booking_email_approval_content' , $email_approval_content );
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         $email_newbookingbyperson_adress  = htmlspecialchars( str_replace('\"','"',$_POST['email_newbookingbyperson_adress']));
         $email_newbookingbyperson_subject = htmlspecialchars( str_replace('\"','"',$_POST['email_newbookingbyperson_subject']));
         $email_newbookingbyperson_content =  str_replace('\"','"',$_POST['email_newbookingbyperson_content']);

         $email_newbookingbyperson_adress      =  str_replace("\'","'",$email_newbookingbyperson_adress);
         $email_newbookingbyperson_subject     =  str_replace("\'","'",$email_newbookingbyperson_subject);
         $email_newbookingbyperson_content     =  str_replace("\'","'",$email_newbookingbyperson_content);



         if (isset( $_POST['is_email_newbookingbyperson_adress'] ))            $is_email_newbookingbyperson_adress = 'On';
         else                                               $is_email_newbookingbyperson_adress = 'Off';
         update_bk_option( 'booking_is_email_newbookingbyperson_adress' , $is_email_newbookingbyperson_adress );

         if ( get_bk_option( 'booking_email_newbookingbyperson_adress' ) !== false  )         update_bk_option( 'booking_email_newbookingbyperson_adress' , $email_newbookingbyperson_adress );
         else                                                                    add_bk_option( 'booking_email_newbookingbyperson_adress' , $email_newbookingbyperson_adress );
         if ( get_bk_option( 'booking_email_newbookingbyperson_subject' ) !== false  )        update_bk_option( 'booking_email_newbookingbyperson_subject' , $email_newbookingbyperson_subject );
         else                                                                    add_bk_option( 'booking_email_newbookingbyperson_subject' , $email_newbookingbyperson_subject );
         if ( get_bk_option( 'booking_email_newbookingbyperson_content' ) !== false  )        update_bk_option( 'booking_email_newbookingbyperson_content' , $email_newbookingbyperson_content );
         else                                                                    add_bk_option( 'booking_email_newbookingbyperson_content' , $email_newbookingbyperson_content );
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         $email_deny_adress  = htmlspecialchars( str_replace('\"','"',$_POST['email_deny_adress']));
         $email_deny_subject = htmlspecialchars( str_replace('\"','"',$_POST['email_deny_subject']));
         $email_deny_content =  str_replace('\"','"',$_POST['email_deny_content']);

         $email_deny_adress      =  str_replace("\'","'",$email_deny_adress);
         $email_deny_subject     =  str_replace("\'","'",$email_deny_subject);
         $email_deny_content     =  str_replace("\'","'",$email_deny_content);



         if (isset( $_POST['is_email_deny_adress'] ))         $is_email_deny_adress = 'On';
         else                                        $is_email_deny_adress = 'Off';
         update_bk_option( 'booking_is_email_deny_adress' , $is_email_deny_adress );


         if (isset( $_POST['is_email_deny_send_copy_to_admin'] ))            $is_email_deny_send_copy_to_admin = 'On';
         else                                               $is_email_deny_send_copy_to_admin = 'Off';
         update_bk_option( 'booking_is_email_deny_send_copy_to_admin' , $is_email_deny_send_copy_to_admin );



         if ( get_bk_option( 'booking_email_deny_adress' ) !== false  )             update_bk_option( 'booking_email_deny_adress' , $email_deny_adress );
         else                                                                    add_bk_option( 'booking_email_deny_adress' , $email_deny_adress );
         if ( get_bk_option( 'booking_email_deny_subject' ) !== false  )            update_bk_option( 'booking_email_deny_subject' , $email_deny_subject );
         else                                                                    add_bk_option( 'booking_email_deny_subject' , $email_deny_subject );
         if ( get_bk_option( 'booking_email_deny_content' ) !== false  )            update_bk_option( 'booking_email_deny_content' , $email_deny_content );
         else                                                                    add_bk_option( 'booking_email_deny_content' , $email_deny_content );

         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

     } 

         $email_reservation_adress      = get_bk_option( 'booking_email_reservation_adress') ;
         $email_reservation_from_adress = get_bk_option( 'booking_email_reservation_from_adress');
         $email_reservation_subject     = get_bk_option( 'booking_email_reservation_subject');
         $email_reservation_content     = get_bk_option( 'booking_email_reservation_content');
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         $email_newbookingbyperson_adress      = get_bk_option( 'booking_email_newbookingbyperson_adress');
         $email_newbookingbyperson_subject     = get_bk_option( 'booking_email_newbookingbyperson_subject');
         $email_newbookingbyperson_content     = get_bk_option( 'booking_email_newbookingbyperson_content');
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         $email_approval_adress      = get_bk_option( 'booking_email_approval_adress');
         $email_approval_subject     = get_bk_option( 'booking_email_approval_subject');
         $email_approval_content     = get_bk_option( 'booking_email_approval_content');
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
         $email_deny_adress      = get_bk_option( 'booking_email_deny_adress');
         $email_deny_subject     = get_bk_option( 'booking_email_deny_subject');
         $email_deny_content     = get_bk_option( 'booking_email_deny_content');
         //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         $is_email_reservation_adress   = get_bk_option( 'booking_is_email_reservation_adress' );
         $is_email_newbookingbyperson_adress      = get_bk_option( 'booking_is_email_newbookingbyperson_adress' );
         $is_email_approval_adress      = get_bk_option( 'booking_is_email_approval_adress' );
         $is_email_approval_send_copy_to_admin = get_bk_option( 'booking_is_email_approval_send_copy_to_admin'  );
         $is_email_deny_adress          = get_bk_option( 'booking_is_email_deny_adress' );
         $is_email_deny_send_copy_to_admin = get_bk_option( 'booking_is_email_deny_send_copy_to_admin'  );

         // Replace <br> to  <br> with  new line
         $email_reservation_content = preg_replace( array(     
                                                            "@(&lt;|<)br/?(&gt;|>)(\r\n)?@"
                                                          , "/\[bookingtype\]/")
                                                  , array(
                                                            "<br/>"
                                                           ,"")
                                                  , $email_reservation_content );
         
         $email_newbookingbyperson_content = preg_replace( array(     
                                                            "@(&lt;|<)br/?(&gt;|>)(\r\n)?@"
                                                          , "/\[bookingtype\]/")
                                                  , array(
                                                            "<br/>"
                                                           ,"")
                                                  , $email_newbookingbyperson_content );
         
         $email_approval_content = preg_replace( array(     
                                                            "@(&lt;|<)br/?(&gt;|>)(\r\n)?@"
                                                          , "/\[bookingtype\]/")
                                                  , array(
                                                            "<br/>"
                                                           ,"")
                                                  , $email_approval_content );
         
         $email_deny_content = preg_replace( array(     
                                                            "@(&lt;|<)br/?(&gt;|>)(\r\n)?@"
                                                          , "/\[bookingtype\]/")
                                                  , array(
                                                            "<br/>"
                                                           ,"")
                                                  , $email_deny_content );                  
    ?>
    <div class="clear" style="height:0px;"></div>
    <div id="ajax_working"></div>
    <div id="poststuff0" class="metabox-holder">
        <form  name="post_settings_email_templates" action="" method="post" id="post_settings_email_templates" >

            <div id="visibility_container_email_new_to_admin" class="visibility_container" style="display:block;">

                <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_admin'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                      <h3 class='hndle'><span><?php _e('Email to "Admin" after a new booking' ,'booking'); ?></span></h3> <div class="inside">

                    <table class="form-table email-table0" >
                        <tbody>
                            <tr>
                                <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_email_reservation_adress">
                                            <input id="is_email_reservation_adress" name="is_email_reservation_adress" type="checkbox" 
                                                <?php if ($is_email_reservation_adress == 'On') echo "checked"; ?>  
                                                value="<?php echo $is_email_reservation_adress; ?>" 
                                                onchange="document.getElementById('booking_is_email_reservation_adress_dublicated').checked=this.checked;"
                                                />
                                            <?php _e('Active' ,'booking'); ?>
                                        </label>
                                    </fieldset>   
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="email_reservation_adress" ><?php _e('To' ,'booking'); ?>:</label></th>
                                <td><input id="email_reservation_adress"  name="email_reservation_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_reservation_adress; ?>" />
                                    <span class="description"><?php printf(__('Type default %sadmin email%s for booking confirmation' ,'booking'),'<b>','</b>');?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="email_reservation_from_adress" ><?php _e('From' ,'booking'); ?>:</label></th>
                                <td><input id="email_reservation_from_adress" name="email_reservation_from_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_reservation_from_adress; ?>" />
                                    <span class="description"><?php printf(__('Type the default %sadmin email%s sending the booking confimation. You can use this %s shortcode.' ,'booking'),'<b>','</b>', '<code>[visitoremail]</code>');?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row"><label for="email_reservation_subject" ><?php _e('Subject' ,'booking'); ?>:</label></th>
                                    <td><input id="email_reservation_subject" name="email_reservation_subject"  class="regular-text code" type="text" size="45" value="<?php echo $email_reservation_subject; ?>" />
                                        <span class="description"><?php printf(__('Type your email %ssubject%s for the booking confimation message.' ,'booking'),'<b>','</b>');?></span>
                                    </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row"><label for="email_reservation_content" ><?php _e('Content' ,'booking'); ?>:</label></th>
                                <td>     <?php /**/
                                            wp_editor( $email_reservation_content, 
                                               'email_reservation_content',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'email_reservation_content'
                                                   , 'textarea_rows' => 10
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_reservation_content" name="email_reservation_content" style="width:100%;" rows="10"><?php echo ($email_reservation_content); ?></textarea> /**/ ?>
                                      <p class="description"><?php printf(__('Type your %semail message content for checking booking%s in. ' ,'booking'),'<b>','</b>');  ?></p>
                                </td>
                            </tr>
                            <tr><td></td>
                                <td>
                                      <?php 
                                        $skip_shortcodes = array('denyreason', 'paymentreason','visitorbookingediturl' ,'visitorbookingcancelurl' , 'visitorbookingpayurl'
                                                                 , 'cost', 'bookingtype', 'check_in_date', 'check_out_date', 'dates_count'
                                                                 );                                        
                                        email_help_section($skip_shortcodes, sprintf(__('For example: "You have a new reservation %s on the following date(s): %s Contact information: %s You can approve or cancel this booking at: %s Thank you, Reservation service."' ,'booking'),'','[dates]&lt;br/&gt;&lt;br/&gt;','&lt;br/&gt; [content]&lt;br/&gt;&lt;br/&gt;', htmlentities( ' <a href="[moderatelink]">'.__('here' ,'booking').'</a> ') . '&lt;br/&gt;&lt;br/&gt; ') );
                                      ?>                                                      
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div> </div> </div>

            </div>


            <div id="visibility_container_email_new_to_visitor" class="visibility_container" style="display:none;">

                <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_person_after_new'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                      <h3 class='hndle'><span><?php _e('Email to "Person" after they make a new reservation' ,'booking'); ?></span></h3> <div class="inside">


                    <table class="form-table email-table0" >
                        <tbody>
                            <tr>
                                <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_email_newbookingbyperson_adress">
                                            <input id="is_email_newbookingbyperson_adress" name="is_email_newbookingbyperson_adress" 
                                                   type="checkbox" <?php if ($is_email_newbookingbyperson_adress == 'On') echo "checked"; ?>  
                                                   value="<?php echo $is_email_newbookingbyperson_adress; ?>" 
                                                   onchange="document.getElementById('booking_is_email_newbookingbyperson_adress_dublicated').checked=this.checked;"  
                                                   />
                                            <?php _e('Active' ,'booking'); ?>
                                        </label>
                                    </fieldset>   
                                </td>
                            </tr>
                            <tr valign="top">
                                <th scope="row"><label for="email_newbookingbyperson_adress" ><?php _e('From' ,'booking'); ?>:</label></th>
                                <td><input id="email_newbookingbyperson_adress"  name="email_newbookingbyperson_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_newbookingbyperson_adress; ?>" />
                                    <span class="description"><?php printf(__('Type the default %sadmin email%s sending the booking confimation' ,'booking'),'<b>','</b>');?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row"><label for="email_newbookingbyperson_subject" ><?php _e('Subject' ,'booking'); ?>:</label></th>
                                    <td><input id="email_newbookingbyperson_subject"  name="email_newbookingbyperson_subject" class="regular-text code" type="text" size="45" value="<?php echo $email_newbookingbyperson_subject; ?>" />
                                        <span class="description"><?php printf(__('Type email subject for %svisitor after creating a new reservation%s.' ,'booking'),'<b>','</b>');?></span>
                                    </td>
                            </tr>

                            <tr valign="top">
                                <th scope="row">
                                    <label for="email_newbookingbyperson_content" ><?php _e('Content' ,'booking'); ?>:</label></th>
                                <td>     <?php /**/
                                            wp_editor( $email_newbookingbyperson_content, 
                                               'email_newbookingbyperson_content',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'email_newbookingbyperson_content'
                                                   , 'textarea_rows' => 10
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_newbookingbyperson_content" name="email_newbookingbyperson_content" style="width:100%;" rows="10"><?php echo ($email_newbookingbyperson_content); ?></textarea> /**/ ?>
                                      <p class="description"><?php printf(__('Type your %semail message for visitor after creating a new reservation%s' ,'booking'),'<b>','</b>');?></p>
                                </td>
                            </tr>
                            <tr><td></td>
                                <td>                                                      <?php
                                        $skip_shortcodes = array('moderatelink', 'denyreason', 'paymentreason', 'visitorbookingpayurl'
                                                                 , 'cost', 'bookingtype', 'check_in_date', 'check_out_date', 'dates_count'
                                                                 );                                        
                                        email_help_section($skip_shortcodes, sprintf(__('For example: "Your reservation %s on these date(s): %s is processing now! We will send confirmation by email. %s Thank you, Reservation service."' ,'booking'),'', '[dates]','&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;' ) );
                                      ?>
                            </td></tr>
                        </tbody>
                    </table>

                </div> </div> </div>

            </div>


            <div id="visibility_container_email_approved" class="visibility_container" style="display:none;">

                <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_person_after_approval'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                      <h3 class='hndle'><span><?php _e('Email to "Person" after booking is approved' ,'booking'); ?></span></h3> <div class="inside">

                    <table class="form-table email-table0" >
                        <tbody>
                            <tr>    
                                <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_email_approval_adress">
                                            <input id="is_email_approval_adress" name="is_email_approval_adress" type="checkbox" 
                                                   <?php if ($is_email_approval_adress == 'On') echo "checked"; ?>  
                                                   value="<?php echo $is_email_approval_adress; ?>" 
                                                   onchange="document.getElementById('booking_is_email_approval_adress_dublicated').checked=this.checked;" 
                                                   />
                                            <?php _e('Active' ,'booking'); ?>
                                        </label>
                                    </fieldset>   
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Send to Admin' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_email_approval_send_copy_to_admin">
                                            <input id="is_email_approval_send_copy_to_admin" name="is_email_approval_send_copy_to_admin" 
                                                   type="checkbox" <?php if ($is_email_approval_send_copy_to_admin == 'On') echo "checked"; ?>  
                                                   value="<?php echo $is_email_approval_send_copy_to_admin; ?>"                                                                    
                                                   />
                                            <?php _e('Check this box to send copy of this email to Admin' ,'booking'); ?>
                                        </label>
                                    </fieldset>   
                                </td>
                            </tr>                                            
                            <tr valign="top">
                                <th scope="row"><label for="email_approval_adress" ><?php _e('From' ,'booking'); ?>:</label></th>
                                <td><input id="email_approval_adress"  name="email_approval_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_approval_adress; ?>" />
                                    <span class="description"><?php printf(__('Type the default %sadmin email%s sending the booking confimation' ,'booking'),'<b>','</b>');?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row"><label for="email_approval_subject" ><?php _e('Subject' ,'booking'); ?>:</label></th>
                                    <td><input id="email_approval_subject"  name="email_approval_subject" class="regular-text code" type="text" size="45" value="<?php echo $email_approval_subject; ?>" />
                                        <span class="description"><?php printf(__('Type your email subject for the %sapproved booking%s.' ,'booking'),'<b>','</b>');?></span>
                                    </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row"><label for="email_approval_content" ><?php _e('Content' ,'booking'); ?>:</label></th>
                                    <td>     <?php /**/
                                            wp_editor( $email_approval_content, 
                                               'email_approval_content',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'email_approval_content'
                                                   , 'textarea_rows' => 10
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_approval_content" name="email_approval_content" style="width:100%;" rows="10"><?php echo ($email_approval_content); ?></textarea> /**/ ?>
                                      <p class="description"><?php printf(__('Type your %semail message for the approved booking%s from the website' ,'booking'),'<b>','</b>');?></p>
                                    </td>
                            </tr>
                            <tr valign="top"><td></td>
                                <td>
                                      <?php
                                        $skip_shortcodes = array('moderatelink', 'denyreason', 'paymentreason', 'visitorbookingpayurl'
                                                                 , 'cost', 'bookingtype', 'check_in_date', 'check_out_date', 'dates_count'
                                                                 );                                        
                                        email_help_section($skip_shortcodes, sprintf(__('For example: "Your reservation %s on these date(s): %s has been approved.%s Thank you, Reservation service."' ,'booking'),'', '[dates]','&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;') );
                                      ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div> </div> </div>

            </div>

            
            <div id="visibility_container_email_declined" class="visibility_container" style="display:none;">

                <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_person_after_deny'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                      <h3 class='hndle'><span><?php _e('Email to "Person" after their booking has been denied' ,'booking'); ?></span></h3> <div class="inside">


                    <table class="form-table email-table0" >
                        <tbody>

                            <tr>    
                                <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_email_deny_adress">
                                            <input id="is_email_deny_adress" name="is_email_deny_adress" type="checkbox" 
                                                <?php if ($is_email_deny_adress == 'On') echo "checked"; ?>  
                                                   value="<?php echo $is_email_deny_adress; ?>" 
                                                   onchange="document.getElementById('booking_is_email_declined_adress_dublicated').checked=this.checked;"  />
                                            <?php _e('Active' ,'booking'); ?>
                                        </label>
                                    </fieldset>   
                                </td>
                            </tr>
                            <tr>
                                <th scope="row"><?php _e('Send to Admin' ,'booking'); ?>:</th>
                                <td>
                                    <fieldset>
                                        <label for="is_email_deny_send_copy_to_admin">
                                            <input id="is_email_deny_send_copy_to_admin" name="is_email_deny_send_copy_to_admin" 
                                                   type="checkbox" <?php if ($is_email_deny_send_copy_to_admin == 'On') echo "checked"; ?>  
                                                   value="<?php echo $is_email_deny_send_copy_to_admin; ?>" 
                                                   />
                                            <?php _e('Check this box to send copy of this email to Admin' ,'booking'); ?>
                                        </label>
                                    </fieldset>   
                                </td>
                            </tr>                                            

                            <tr valign="top">
                                <th scope="row"><label for="email_deny_adress" ><?php _e('From' ,'booking'); ?>:</label></th>
                                <td><input id="email_deny_adress"  name="email_deny_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_deny_adress; ?>" />
                                    <span class="description"><?php printf(__('Type the default %sadmin email%s sending the booking confimation' ,'booking'),'<b>','</b>');?></span>
                                </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row"><label for="email_deny_subject" ><?php _e('Subject' ,'booking'); ?>:</label></th>
                                    <td><input id="email_deny_subject"  name="email_deny_subject" class="regular-text code" type="text" size="45" value="<?php echo $email_deny_subject; ?>" />
                                        <span class="description"><?php printf(__('Type your email subject for the %sdenied booking%s.' ,'booking'),'<b>','</b>');?></span>
                                    </td>
                            </tr>

                            <tr valign="top">
                                    <th scope="row"><label for="email_deny_content" ><?php _e('Content' ,'booking'); ?>:</label></th>
                                    <td>     <?php /**/
                                            wp_editor( $email_deny_content, 
                                               'email_deny_content',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'email_deny_content'
                                                   , 'textarea_rows' => 10
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_deny_content" name="email_deny_content" style="width:100%;" rows="10"><?php echo ($email_deny_content); ?></textarea> /**/ ?>
                                        <p class="description"><?php printf(__('Type your %semail message for the denied booking%s from the website' ,'booking'),'<b>','</b>');?></p>
                                    </td>
                            </tr>

                            <tr valign="top"><td></td>
                                <td>
                                      <?php
                                        $skip_shortcodes = array('moderatelink', 'paymentreason', 'visitorbookingpayurl', 'visitorbookingediturl', 'visitorbookingcancelurl'
                                                                 , 'cost', 'bookingtype', 'check_in_date', 'check_out_date', 'dates_count'
                                                                 );                                        
                                        email_help_section($skip_shortcodes, sprintf(__('For example: "Your reservation %s on these date(s): %s has been canceled. Please contact us for more information. %s Thank you, Reservation service."' ,'booking'), '' ,'[dates]' , '&lt;br/&gt;&lt;br/&gt;[denyreason]&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;') );
                                      ?>

                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div> </div> </div>

            </div>
            <span class="wpdevbk">
                <div <?php $my_close_open_alert_id = 'bk_alert_settings_email_in_free'; ?>
                    class="alert alert-block alert-info <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_alert_id ) ) echo 'closed'; ?>"                             
                    id="<?php echo $my_close_open_alert_id; ?>">
                  <a class="close tooltip_left" rel="tooltip" title="Don't show the message anymore" data-dismiss="alert" href="javascript:void(0)"
                     onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_alert_id; ?>');"
                     >&times;</a>
                  <strong class="alert-heading">Note!</strong>
                      Check how in <a href="http://wpbookingcalendar.com/demo/" target="_blank" style="text-decoration:underline;">other versions of Booking Calendar</a> possible to customize email templates with new additional shortcodes</em>
                </div>
            </span>        
        <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="Submit"/>
        <div class="clear" style="height:10px;"></div>

        </form>
    </div>
    <?php
}


// Emails Help sections
function email_help_section( $skip_shortcodes = array() , $email_example = '') {
        ?>
            <div class="wpbc-help-message" style="margin-top:10px;">
                <?php if ( class_exists('wpdev_bk_personal') ) { ?>
                <p class="description" style="font-weight:normal;"><?php printf(__('You can use (in subject and content of email template) any shortcodes, which you used in the booking form. Use the shortcodes in the same way as you used them in the content form at Settings Fields page.' ,'booking'));?></p>
                <br/>
                <p class="description"><strong><?php printf(__('You can use following shortcodes in content of this template' ,'booking'));?></strong>: </p>
                <?php } else { ?>
                <p class="description"><strong><?php printf(__('You can use following shortcodes in content of this template' ,'booking'));?></strong>: </p>
                <?php }  ?>
                <p class="description" style="font-weight:normal;"><?php 
                    if ( ! class_exists('wpdev_bk_personal') ) 
                        printf(__('%s - inserting data info about the booking' ,'booking'),'<code>[content]</code>');
                    else
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
                <p class="description"><?php printf(__('%s - inserting check-out date (last day of reservation),' ,'booking'),'<code>[check_out_plus1day]</code>'); echo ' + 1 ' . __('day', 'booking'); ?>, </p>  <?php //FixIn: 6.0.1.11 ?>
                <?php } if ( ! in_array('dates_count', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting the number of booking dates ' ,'booking'),'<code>[dates_count]</code>');?>, </p>
                <?php } ?>
                <?php if ( class_exists('wpdev_bk_personal') ) { ?>
                <hr />
                <p class="description"><?php printf(__('%s - inserting your site URL ' ,'booking'),'<code>[siteurl]</code>');?>, </p>                    
                <p class="description"><?php printf(__('%s - inserting IP address of the user who made this action ' ,'booking'),'<code>[remote_ip]</code>');?>, </p>                                    
                <p class="description"><?php printf(__('%s - inserting contents of the User-Agent: header from the current request, if there is one ' ,'booking'),'<code>[user_agent]</code>');?>, </p>                    
                <p class="description"><?php printf(__('%s - inserting address of the page (if any), where visitor make this action ' ,'booking'),'<code>[request_url]</code>');?>, </p>                    
                <p class="description"><?php printf(__('%s - inserting date of this action ' ,'booking'),'<code>[current_date]</code>');?>, </p>                    
                <p class="description"><?php printf(__('%s - inserting time of this action ' ,'booking'),'<code>[current_time]</code>');?>, </p>                    
                <hr />
                <?php } else { ?>
                <p class="description"><?php printf(__('%s - inserting your site URL ' ,'booking'),'<code>[siteurl]</code>');?>, </p>                    
                <?php } ?>

                <?php if ( ! in_array('moderatelink', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - inserting moderate link of new booking ' ,'booking'),'<code>[moderatelink]</code>');?>, </p>
                <?php } ?>
                <?php if ( class_exists('wpdev_bk_personal') ) { ?>
                    <?php if ( ! in_array('visitorbookingediturl', $skip_shortcodes)) { ?>
                    <p class="description"><?php printf(__('%s - inserting link to the page where visitor can edit the reservation,  (possible to use the %s parameter for setting different %s of this page. Example: %s )' ,'booking'),'<code>[visitorbookingediturl]</code>', '"url"', 'URL', '<em>[visitorbookingediturl url="http://www.server.com/custom-page/"]</em>');?>, </p>
                    <?php } if ( ! in_array('visitorbookingcancelurl', $skip_shortcodes)) { ?>
                    <p class="description"><?php printf(__('%s - inserting link to the page where visitor can cancel the reservation, (possible to use the %s parameter for setting different %s of this page. Example: %s )' ,'booking'),'<code>[visitorbookingcancelurl]</code>', '"url"', 'URL', '<em>[visitorbookingcancelurl url="http://www.server.com/custom-page/"]</em>');?>, </p>
                    <?php } if ( ! in_array('visitorbookingpayurl', $skip_shortcodes)) { ?>
                    <p class="description"><?php printf(__('%s - inserting link to payment page where visitor can pay for the reservation  (possible to use the %s parameter for setting different %s of this page. Example: %s )' ,'booking'),'<code>[visitorbookingpayurl]</code>', '"url"', 'URL', '<em>[visitorbookingpayurl url="http://www.server.com/custom-page/"]</em>');?>, </p>
                    <?php } ?>

                    <?php if ( ! in_array('paymentreason', $skip_shortcodes)) { ?>
                    <p class="description"><?php printf(__('%s - add the reason for booking payment, you can enter it before sending email, ' ,'booking'),'<code>[paymentreason]</code>');?>, </p>
                    <?php } ?> 
                <?php } ?>
                <?php if ( ! in_array('denyreason', $skip_shortcodes)) { ?>
                <p class="description"><?php printf(__('%s - add the reason booking was cancelled, you can enter it before sending email, ' ,'booking'),'<code>[denyreason]</code>');?>, </p>
                <?php } ?>
                <br/>
                <p class="description"><strong><?php _e('HTML tags is accepted.' ,'booking');?></strong></p>
                <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
                <?php // echo ($email_example); ?>
            </div>
        <?php
    }


// Emails sub menu line
function wpbc_booking_settings_top_menu_submenu_line(){

    if ( (isset($_GET['tab'])) && ( $_GET['tab'] == 'email') ) {
    ?>
        <div class="booking-submenu-tab-container">
            <div class="nav-tabs booking-submenu-tab-insidecontainer">

                <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_email_new_to_admin').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                   rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab booking-submenu-tab-selected <?php if ( get_bk_option( 'booking_is_email_reservation_adress' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                   original-title="<?php _e('Customization of email template, which is sending to Admin after new booking' ,'booking');?>" >
                        <?php _e('New for Admin' ,'booking');?>
                    <input type="checkbox" <?php if ( get_bk_option( 'booking_is_email_reservation_adress' ) == 'On' ) echo ' checked="CHECKED" '; ?>  name="booking_is_email_reservation_adress_dublicated" id="booking_is_email_reservation_adress_dublicated"
                           onchange="document.getElementById('is_email_reservation_adress').checked=this.checked;"
                           >
                </a>

                <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_email_new_to_visitor').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                   rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php if ( get_bk_option( 'booking_is_email_newbookingbyperson_adress' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                   original-title="<?php _e('Customization of email template, which is sending to Visitor after new booking' ,'booking');?>" >
                        <?php _e('New for Visitor' ,'booking');?>
                    <input type="checkbox" <?php if ( get_bk_option( 'booking_is_email_newbookingbyperson_adress' ) == 'On' ) echo ' checked="CHECKED" '; ?>  name="booking_is_email_newbookingbyperson_adress_dublicated" id="booking_is_email_newbookingbyperson_adress_dublicated"
                           onchange="document.getElementById('is_email_newbookingbyperson_adress').checked=this.checked;"
                           >
                </a>

                <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_email_approved').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                   rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php if ( get_bk_option( 'booking_is_email_approval_adress' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                   original-title="<?php _e('Customization of email template, which is sending to Visitor after approval of booking' ,'booking');?>" >
                        <?php _e('Approved' ,'booking');?>
                    <input type="checkbox" <?php if ( get_bk_option( 'booking_is_email_approval_adress' ) == 'On' ) echo ' checked="CHECKED" '; ?>  name="booking_is_email_approval_adress_dublicated" id="booking_is_email_approval_adress_dublicated"
                           onchange="document.getElementById('is_email_approval_adress').checked=this.checked;"
                           >
                </a>

                <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_email_declined').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                   rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php if ( get_bk_option( 'booking_is_email_deny_adress' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                   original-title="<?php _e('Customization of email template, which is sending to Visitor after Cancellation of booking' ,'booking');?>" >
                        <?php _e('Declined' ,'booking');?>
                    <input type="checkbox" <?php if ( get_bk_option( 'booking_is_email_deny_adress' ) == 'On' ) echo ' checked="CHECKED" '; ?>  name="booking_is_email_declined_adress_dublicated" id="booking_is_email_declined_adress_dublicated"
                           onchange="document.getElementById('is_email_deny_adress').checked=this.checked;"
                           >
                </a>

                <input type="button" class="button-primary button" value="<?php _e('Save Changes' ,'booking'); ?>" 
                       style="float:right;"
                       onclick="document.forms['post_settings_email_templates'].submit();">
                <div class="clear" style="height:0px;"></div>
                <script type="text/javascript">
                    function recheck_active_itmes_in_top_menu( internal_checkbox, top_checkbox ){
                        if (document.getElementById( internal_checkbox ).checked != document.getElementById( top_checkbox ).checked ) {
                            document.getElementById( top_checkbox ).checked = document.getElementById( internal_checkbox ).checked;
                            if ( document.getElementById( top_checkbox ).checked )
                                jQuery('#' + top_checkbox ).parent().removeClass('booking-submenu-tab-disabled');
                            else
                                jQuery('#' + top_checkbox ).parent().addClass('booking-submenu-tab-disabled');
                        }
                    }

                    jQuery(document).ready( function(){
                        recheck_active_itmes_in_top_menu('is_email_reservation_adress', 'booking_is_email_reservation_adress_dublicated');
                        recheck_active_itmes_in_top_menu('is_email_newbookingbyperson_adress', 'booking_is_email_newbookingbyperson_adress_dublicated');
                        recheck_active_itmes_in_top_menu('is_email_approval_adress', 'booking_is_email_approval_adress_dublicated');
                        recheck_active_itmes_in_top_menu('is_email_deny_adress', 'booking_is_email_declined_adress_dublicated');
                    });
                </script>

            </div>
        </div>
      <?php
    }

    //$this->wpdev_booking_settings_top_menu_submenu_line_for_form_fields();
}

if ( ! class_exists('wpdev_bk_personal') ) 
    add_bk_action('wpdev_booking_settings_top_menu_submenu_line', 'wpbc_booking_settings_top_menu_submenu_line');    
?>