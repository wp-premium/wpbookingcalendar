<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/
if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


    require_once(WPDEV_BK_PLUGIN_DIR. '/inc/lib_p.php' );
if ( file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-booking-select-widget.php')) {  
    require_once(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-booking-select-widget.php' ); }    
if ( file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/form/class-wpbc-form-help.php') ) { 
    require_once(WPDEV_BK_PLUGIN_DIR. '/inc/form/class-wpbc-form-help.php' ); }
if ( file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/biz_s.php') ) { 
    require_once(WPDEV_BK_PLUGIN_DIR. '/inc/biz_s.php' ); }

if( is_admin() ) {     
    if ( file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-br-table-for-settings.php') ) {    // Booking resources Table for Settings.
        require_once(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-br-table-for-settings.php' ); }   
        
    if ( file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-check-updates.php') ) {            // Checking updates
        require_once(WPDEV_BK_PLUGIN_DIR. '/inc/wpbc-check-updates.php' ); 
        $wpbc_plugin_updater = new WPBC_Plugin_Updater(  WPDEV_BK_FILE, 'http://wpbookingcalendar.com/check-update/booking.json', array( 'version' => WP_BK_VERSION_NUM , 'plugin_html_id' => 'booking-calendar' ) );        
    }     
}


class wpdev_bk_personal   {

    var $current_booking_type;
    var $wpdev_bk_biz_s;
    var $current_edit_booking;
    var $countries_list;

    function __construct() {
        $this->current_booking_type = 1;
        $this->current_edit_booking = false;
                  
        add_bk_filter('wpbc_get_booking_data', array(&$this, 'get_booking_data'));  //FixIn: 5.4.5.11  
        add_bk_filter('get_bk_dates_sql', array(&$this, 'get_bk_dates_4_edit'));  // At hotel edition already edit it

        add_bk_action('show_remark_editing_field', array(&$this, 'show_remark_editing_field'));     // Show fields for editing
        add_bk_action('wpdev_updating_remark', array(&$this, 'wpdev_updating_remark'));             // Ajax POST request for updating remark
        add_bk_action('wpdev_make_update_of_remark', array(&$this, 'wpdev_make_update_of_remark')); // Ajax POST request for updating remark

        add_bk_action('wpdev_updating_bk_resource_of_booking', array(&$this, 'wpdev_updating_bk_resource_of_booking'));         // Ajax POST request for updating remark
        add_bk_action('wpbc_duplicate_booking_to_other_resource', array(&$this, 'wpbc_duplicate_booking_to_other_resource'));   // Ajax POST request for booking duplication

        add_bk_action('wpdev_delete_booking_by_visitor', array(&$this, 'delete_booking_by_visitor'));   // Ajax POST request for updating remark
        add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_menu_content'));

        add_bk_action('wpdev_bk_general_settings_edit_booking_url', array(&$this, 'settings_edit_booking_url'));
        add_bk_action('wpdev_bk_general_settings_set_default_booking_resource', array(&$this, 'settings_set_default_booking_resource'));
        
        add_bk_action('wpdev_booking_settings_top_menu_submenu_line', array(&$this, 'wpdev_booking_settings_top_menu_submenu_line'));
        add_bk_action('wpdev_booking_settings_top_menu_submenu_line', array(&$this, 'wpdev_booking_settings_top_menu_submenu_line_for_form_fields'));
        add_bk_action('wpdev_bk_general_settings_set_default_title_in_day', array(&$this, 'settings_set_default_title_in_day'));
        add_bk_action('wpdev_bk_general_settings_export_data_separator', array(&$this, 'wpdev_bk_general_settings_export_data_separator'));

        add_bk_action('wpdev_booking_resources_show_content', array(&$this, 'wpdev_booking_resources_show_content'));

        add_action('settings_advanced_set_update_hash_after_approve', array(&$this, 'settings_advanced_set_update_hash_after_approve'));    // Write General Settings
        add_bk_action('booking_aproved', array(&$this, 'booking_aproved_afteraction'));

        add_action('wpbc_define_js_vars',   array(&$this, 'wpbc_define_js_vars') );
        add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
        add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );
        
        
        add_bk_action('wpdev_booking_activation', array(&$this, 'pro_activate'));
        add_bk_action('wpdev_booking_deactivation', array(&$this, 'pro_deactivate'));

        add_bk_action('show_all_bookings_at_one_page', array(&$this, 'show_all_bookings_at_one_page'));


        add_bk_filter(   'wpdev_get_default_form', array(&$this, 'get_default_form'));
        add_bk_filter(   'wpdev_get_default_form_show', array(&$this, 'get_default_form_show'));

        add_bk_action('show_additional_translation_shortcode_help', array(&$this, 'show_additional_translation_shortcode_help'));

        if ( class_exists('wpdev_bk_biz_s')) {
                $this->wpdev_bk_biz_s = new wpdev_bk_biz_s();
        } else { $this->wpdev_bk_biz_s = false; }

        global $wpdev_booking_country_list;
        $this->countries_list = $wpdev_booking_country_list;

        add_bk_filter( 'wpdev_booking_get_hash_to_id', array(&$this, 'get_hash_to_id'));  //HASH_EDIT
        add_bk_filter( 'wpdev_booking_get_hash_using_booking_id', array(&$this, 'get_id_using_hash'));  //HASH_EDIT

        add_bk_filter( 'wpdev_booking_set_booking_edit_link_at_email', array(&$this, 'set_booking_edit_link_at_email'));
        
        add_bk_action( 'wpbc_update_booking_hash', array(&$this, 'wpbc_update_booking_hash'));     // HASH Update
        
        add_bk_filter( 'wpdev_is_booking_resource_exist', array(&$this, 'wpdev_is_booking_resource_exist'));    // Check if this booking resource exist or not exist anymore

        add_bk_filter( 'get_sql_for_checking_new_bookings', array(&$this, 'get_sql_for_checking_new_bookings'));

        add_bk_action('write_content_for_popups', array(&$this, 'premium_content_for_popups'));

        // Custom buttons functions
        add_bk_action('show_tabs_inside_insertion_popup_window', array(&$this, 'show_tabs_inside_insertion_popup_window'));
        add_bk_action('show_insertion_popup_css_for_tabs', array(&$this, 'show_insertion_popup_css_for_tabs'));
        add_bk_action('show_insertion_popup_shortcode_for_bookingedit', array(&$this, 'show_insertion_popup_shortcode_for_bookingedit'));
        add_bk_action('show_additional_arguments_for_shortcode', array(&$this, 'show_additional_arguments_for_shortcode'));

        add_bk_action('wpdev_ajax_export_bookings_to_csv', array($this, 'wpdev_ajax_export_bookings_to_csv'));
        add_bk_action('wpdev_ajax_save_bk_listing_filter', array($this, 'wpdev_ajax_save_bk_listing_filter'));
        add_bk_action('wpdev_ajax_delete_bk_listing_filter', array($this, 'wpdev_ajax_delete_bk_listing_filter'));

        add_bk_filter('recheck_version', array($this, 'recheck_version'));           // check Admin pages, if some user can be there.

        // Select booking resource
        add_bk_filter('wpdev_get_booking_select_form', array(&$this, 'wpdev_get_booking_select_form'));
        
        // Show booking resource Title or Cost
        add_bk_filter('wpbc_booking_resource_info', array(&$this, 'wpbc_booking_resource_info'));
    }


//  C u s t o m      b u t t o n s ///////////////////////////////////////////////////////////////////////////////////////////////////////

    function show_tabs_inside_insertion_popup_window(){
        $is_only_icons = false;
        ?>
         <div style="height:1px;clear:both;margin-top:0px;"></div>
         <div id="menu-wpdevplugin" class="wpdev-bk">
            <div class="nav-tabs-wrapper">
                <div class="nav-tabs" style="width:100%;">
                    <?php $title = __('Form / Calendar' ,'booking');
                    $my_icon = 'bc-16x16.png'; ?>
                    <a rel="tooltip" class="tooltip_bottom nav-tab  nav-tab-active" title="<?php echo __('Insertion of booking form or availability calendar shortcodes' ,'booking'); ?>"  href="javascript:void(0);"
                         onclick="javascript:
                                jQuery('.booking_configuration_dialog').css('display','none');
                                document.getElementById('popup_new_reservation').style.display='block';
                                jQuery('.nav-tab').removeClass('nav-tab-active');
                                jQuery(this).addClass('nav-tab-active');
                                selected_booking_shortcode = 'bookingform';
                         " style="padding:4px 14px 7px 33px;margin-right:5px;border:0;box-shadow: none;"
                    ><i class="icon-search"></i><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>


                    <?php $title = __('Selection of form' ,'booking');
                    $my_icon = ''; ?>
                    <a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Insertion selection of booking form for specific booking resource' ,'booking'); ?>" href="javascript:void(0);"
                        onclick="javascript:
                                jQuery('.booking_configuration_dialog').css('display','none');
                                document.getElementById('popup_selection_booking_form').style.display='block';
                                jQuery('.nav-tab').removeClass('nav-tab-active');
                                jQuery(this).addClass('nav-tab-active');
                                selected_booking_shortcode = 'bookingselect';
                                "
                    style="padding:4px 14px 7px 16px;margin-right:5px;border:0;box-shadow: none;"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>

                    <?php if  (class_exists('wpdev_bk_biz_l') ) { ?>
                    <?php $title = __('Search' ,'booking');
                    $my_icon = ''; ?>
                    <a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Insertion search form shortcode' ,'booking'); ?>" href="javascript:void(0);"
                        onclick="javascript:
                                jQuery('.booking_configuration_dialog').css('display','none');
                                document.getElementById('popup_search_reservation').style.display='block';
                                jQuery('.nav-tab').removeClass('nav-tab-active');
                                jQuery(this).addClass('nav-tab-active');
                                selected_booking_shortcode = 'bookingsearch';
                                "
                    style="padding:4px 14px 7px 16px;margin-right:5px;border:0;box-shadow: none;"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>
                    <?php } ?>


                    <?php $title = __('Other' ,'booking');
                    $my_icon = ''; ?>
                    <a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Other' ,'booking'); ?>" href="javascript:void(0);"
                        onclick="javascript:
                                jQuery('.booking_configuration_dialog').css('display','none');
                                document.getElementById('popup_other_shortcodes').style.display='block';
                                jQuery('.nav-tab').removeClass('nav-tab-active');
                                jQuery(this).addClass('nav-tab-active');
                                selected_booking_shortcode = 'bookingresource';
                                "
                    style="padding:4px 14px 7px 16px;float:right;margin-left:5px;border:0;box-shadow: none;"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>


                    <?php $title = __('Editing of booking' ,'booking');
                    $my_icon = ''; ?>
                    <a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php echo __('Insertion system shortcode for booking editing' ,'booking'); ?>" href="javascript:void(0);"
                        onclick="javascript:
                                jQuery('.booking_configuration_dialog').css('display','none');
                                document.getElementById('popup_edit_reservation').style.display='block';
                                jQuery('.nav-tab').removeClass('nav-tab-active');
                                jQuery(this).addClass('nav-tab-active');
                                selected_booking_shortcode = 'bookingedit';
                                "
                    style="padding:4px 14px 7px 16px;float:right;border:0;box-shadow: none;"><span class="nav-tab-text"><?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?></span></a>
                </div>
            </div>
        </div>
        <div style="height:1px;clear:both;border-top:1px solid #bbc;margin-bottom:10px;"></div>
        <?php
    }

    function show_additional_arguments_for_shortcode(){
        ?><div style="height:1px;clear:both;width:100%;margin-top:-5px;"></div>
        <div class="bk_help_message"><?php 
            printf(__('Please, read more about the shortcodes %shere%s or JavaScript customization of the specific shortcodes %shere%s' ,'booking'),'<a href="http://wpbookingcalendar.com/help/booking-calendar-shortcodes/" target="_blank">','</a>', '<a href="http://wpbookingcalendar.com/faq/advanced-js-shortcode/" target="_blank">','</a>'); 
        ?></div>
        <div style="height:1px;clear:both;width:100%;"></div><?php
    }

    function show_insertion_popup_shortcode_for_bookingedit() {
        ?>
        <div id="popup_other_shortcodes" class="booking_configuration_dialog">
            <p>
                <?php 
                    printf(__('You can use shortcode %s for showing title of booking resource ' ,'booking'), '<code>[bookingresource type=3 show="title"]</code>' ); 
                    echo ' <p>'; 
                    if ( class_exists('wpdev_bk_biz_s') ) 
                        printf(__('or cost of booking resource %s'  ,'booking'), '<code>[bookingresource type=3 show="cost"]</code>'); 
                    echo ' </p>';
                ?>
            </p>
        </div>
        <?php
        
        ?>
        <div id="popup_edit_reservation" class="booking_configuration_dialog">
            <p>
                <?php printf(__('This shortcode %s is used on a page, where visitors can %smodify%s their own booking(s), %scancel%s or make %spayment%s after receiving an admin email payment request' ,'booking'),'<code>[bookingedit]</code>','<strong>','</strong>','<strong>','</strong>','<strong>','</strong>'); ?>.
                <br /><br /> <?php printf(__('The content of field %sURL to edit bookings%s on the %sgeneral booking settings page%s must link to this page' ,'booking'), '<i>"','"</i>','<a href="admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking-option">','</a>'); ?>.
                <br /><br /> <?php printf(__('Email templates, which use shortcodes: %s, will be linked to this page' ,'booking'),  '<code>[visitorbookingediturl]</code>, <code>[visitorbookingcancelurl]</code>, <code>[visitorbookingpayurl]</code>'); ?>.
            </p>
        </div>
        <?php
        ?>
        <div id="popup_search_reservation" class="booking_configuration_dialog">

            <div class="field" style="border-bottom:1px solid #ccc;">
                <?php /* 
                  [bookingsearch

                     searchresults="http://server.com/custom-search-results/"
                     noresultstitle="Nothing Found"
                     searchresultstitle="Search results"
                     users="1,2"
                ]
                [bookingsearchresults]
                */ ?>
                <div style="clear:both;margin:10px 0;" >
                    <div style="float:left;margin:0 10px 0 0;"><?php _e('Select shortcode to insert' ,'booking'); ?></div>
                    <fieldset>
                        <legend class="screen-reader-text"><span><?php _e('Select shortcode to insert' ,'booking'); ?></span></legend>
                        <label for="bookingsearch_type_form" style="width: auto;">
                            <input value="bookingsearch" checked="checked" type="radio" 
                                id="bookingsearch_type_form"  name="bookingsearch_type"  
                                onclick="javascript: jQuery('.bookingsearch').slideDown('normal');jQuery('.bookingsearchresults').slideUp('normal');"
                           /> <span><?php _e('Search form' ,'booking');?></span>
                        </label>
                        <label for="bookingsearch_type_results" style="width: auto;">
                            <input value="bookingsearchresults" type="radio" 
                                   id="bookingsearch_type_results" name="bookingsearch_type" 
                                   onclick="javascript: jQuery('.bookingsearchresults').slideDown('normal');jQuery('.bookingsearch').slideUp('normal');"  
                                   /> <span><?php _e('Search results' ,'booking'); ?> </span>
                        </label>
                    </fieldset>
                </div>

            </div>


            <div class="booking_search_form_results bookingsearchresults" style="display:none;">
                <?php printf(__('This shortcode %s is using for showing the search results at specific page, if the search form is submit showing the search results at different page' ,'booking'),'<code>[bookingsearchresults]</code>' ); ?>.
            </div>


            <div class="booking_search_form_results bookingsearch">

                    <div class="field">                            
                        <label for="search_at_diff_page" style="width: auto;">
                            <input id="search_at_diff_page"  name="search_at_diff_page" checked="checked"  type="checkbox"  
                                   onchange="javascript:if(! this.checked){ jQuery('#bookingsearch_searchresults_section').css('display','none'); } else {jQuery('#bookingsearch_searchresults_section').css('display','block');}" 
                                   /> <span><?php _e('Check this box to show search results on other page' ,'booking'); ?> </span>
                        </label>
                    </div>

                    <div class="field wpbc_sub_options" id="bookingsearch_searchresults_section">
                        <fieldset>
                            <label for="bookingsearch_searchresults"><?php _e('URL of search results:' ,'booking'); ?></label>
                            <input id="bookingsearch_searchresults"  name="bookingsearch_searchresults" class="regular-text" type="text" value="" />
                            <p class="description"><?php _e('Type the URL of search results page.' ,'booking'); ?></p>
                        </fieldset>
                    </div>

                    <div class="field togle_titles_section_for_search">                            
                        <fieldset>
                            <label for="bookingsearch_searchresultstitle"><?php _e('Title of Search results:' ,'booking'); ?></label>
                            <input id="bookingsearch_searchresultstitle"  name="bookingsearch_searchresultstitle" 
                                   class="regular-text" type="text" value="<?php echo str_replace('"','',__('Search results:' ,'booking')); ?>" />                                
                            <p class="description"><?php _e('Type the title of Search results.' ,'booking'); ?></p>
                        </fieldset>
                    </div>

                    <div class="field togle_titles_section_for_search">
                        <fieldset>                                
                            <label for="bookingsearch_noresultstitle"><?php _e('Nothing Found Message:' ,'booking'); ?></label>
                            <input id="bookingsearch_noresultstitle"  name="bookingsearch_noresultstitle" 
                                   class="regular-text" type="text" value="<?php echo str_replace('"','',__('Nothing Found.' ,'booking')); ?>" />                                
                            <p class="description"><?php _e('Type the message, when nothing found.' ,'booking'); ?></p>
                        </fieldset>
                    </div>

                    <?php if ( class_exists('wpdev_bk_multiuser')) { ?>
                    <div class="field togle_titles_section_for_search">
                        <fieldset>                                
                            <label for="bookingsearch_users"><?php _e('Search only for users:' ,'booking'); ?></label>
                            <input id="bookingsearch_users"  name="bookingsearch_users" class="regular-text" type="text" value="" />
                            <br /><span class="description"><?php _e('Type IDs of the users (separated by comma ",") for searching availability  only for these users, or leave it blank for searching for all users.' ,'booking'); ?></span>
                        </fieldset>
                    </div>
                    <?php } ?>                    
            </div>                    
        </div>


        <div id="popup_selection_booking_form" class="booking_configuration_dialog">
           <?php /* <!--
                [bookingselect
                    type='2,3,4'
                    form_type='standard'
                    nummonths=1
                    label='Please select the resource: ']
            --> /**/ ?>

            <?php $types_list = $this->get_booking_types(false, false); ?>
            <div class="field">
                <fieldset>
                    <label for="bookingselect_resources" style="vertical-align: top;"><?php _e('Booking resource:' ,'booking'); ?></label>
                    <select id="bookingselect_resources" name="bookingselect_resources" multiple="MULTIPLE" style="height:90px;">
                        <option value="" style="font-weight:bold;" selected="SELECTED" ><?php echo __('All' ,'booking'); ?></option>
                        <?php foreach ($types_list as $tl) { ?>
                        <option value="<?php echo $tl->id; ?>"
                                    style="<?php if  (isset($tl->parent)) if ($tl->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                                ><?php echo $tl->title; ?></option>
                        <?php } ?>
                    </select>
                    <span class="description" 
                          style="display: block;
                                 float: right;
                                 line-height: 1.5em;
                                 text-align: left;
                                 vertical-align: top;
                                 width: 300px;"><?php printf(__('Select booking resources, for showing in selectbox. Please use CTRL to select multiple booking resources.' ,'booking'),'<br />'); ?></span>
                </fieldset>
            </div>
            
            <div class="field">
                <fieldset>
                    <label for="bookingselect_preselected_resource"><?php _e('Preselected resource' ,'booking'); ?>:</label>
                    <select id="bookingselect_preselected_resource" name="bookingselect_preselected_resource" >
                        <option value="" style="font-weight:bold;" selected="SELECTED" ><?php echo __('None' ,'booking'); ?></option>
                        <?php foreach ($types_list as $tl) { ?>
                        <option value="<?php echo $tl->id; ?>"
                                    style="<?php if  (isset($tl->parent)) if ($tl->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                                ><?php echo $tl->title; ?></option>
                        <?php } ?>
                    </select>
                    <span class="description"><?php _e('Define preselected resource.' ,'booking'); ?></span>
                </fieldset>
            </div>

                        

            <div class="field">
                <fieldset>
                    <label for="bookingselect_calendar_count"><?php _e('Visible months:' ,'booking'); ?></label>
                    <select  id="bookingselect_calendar_count"  name="bookingselect_calendar_count" >
                        <option value="1" <?php if (get_bk_option( 'booking_client_cal_count' )== '1') echo ' selected="SELECTED" ' ?> >1</option>
                        <option value="2" <?php if (get_bk_option( 'booking_client_cal_count' )== '2') echo ' selected="SELECTED" ' ?> >2</option>
                        <option value="3" <?php if (get_bk_option( 'booking_client_cal_count' )== '3') echo ' selected="SELECTED" ' ?> >3</option>
                        <option value="4" <?php if (get_bk_option( 'booking_client_cal_count' )== '4') echo ' selected="SELECTED" ' ?> >4</option>
                        <option value="5" <?php if (get_bk_option( 'booking_client_cal_count' )== '5') echo ' selected="SELECTED" ' ?> >5</option>
                        <option value="6" <?php if (get_bk_option( 'booking_client_cal_count' )== '6') echo ' selected="SELECTED" ' ?> >6</option>
                        <option value="7" <?php if (get_bk_option( 'booking_client_cal_count' )== '7') echo ' selected="SELECTED" ' ?> >7</option>
                        <option value="8" <?php if (get_bk_option( 'booking_client_cal_count' )== '8') echo ' selected="SELECTED" ' ?> >8</option>
                        <option value="9" <?php if (get_bk_option( 'booking_client_cal_count' )== '9') echo ' selected="SELECTED" ' ?> >9</option>
                        <option value="10" <?php if (get_bk_option( 'booking_client_cal_count' )== '10') echo ' selected="SELECTED" ' ?> >10</option>
                        <option value="11" <?php if (get_bk_option( 'booking_client_cal_count' )== '11') echo ' selected="SELECTED" ' ?> >11</option>
                        <option value="12" <?php if (get_bk_option( 'booking_client_cal_count' )== '12') echo ' selected="SELECTED" ' ?> >12</option>
                    </select>
                    <span class="description"><?php _e('Select number of month to show for calendar.' ,'booking'); ?></span>
                </fieldset>
            </div>

            <?php make_bk_action('wpdev_show_bk_form_selection','bookingselect_form_type') ?>
            
            <div class="field">
                <fieldset>
                    <label for="bookingselect_title"><?php _e('Label' ,'booking'); ?>:</label>
                    <input id="bookingselect_title"  name="bookingselect_title"  type="text" value="<?php echo str_replace('"','',__('Please select the resource:' ,'booking')); ?>" />
                    <span class="description"><?php _e('Title near your select box.' ,'booking'); ?></span>
                </fieldset>
            </div>

            <div class="field">
                <fieldset>
                    <label for="bookingselect_first_option_title"><?php _e('First option title' ,'booking'); ?>:</label>
                    <input id="bookingselect_first_option_title"  name="bookingselect_first_option_title"  type="text" value="<?php echo str_replace('"','',__('Please Select' ,'booking')); ?>" />
                    <span class="description"><?php _e('First option in dropdown list.' ,'booking'); ?></span>
                    <p class="description"><?php _e('Please leave it empty if you want to skip it.' ,'booking'); ?></p>
                </fieldset>
            </div>

            <div style="clear:both;width:100%;height:1px;"></div>

            <span class="description">
                <?php printf(__('This shortcode %s is using for selection of the booking form of specific booking resources in selectbox' ,'booking'),'' ); ?>.
            </span>
        </div>
        <?php
    }

    function show_insertion_popup_css_for_tabs(){
        ?>
                #menu-wpdevplugin {
                margin-right:0px;
                margin-top:-10px;
                margin-bottom:0px;
                position:relative;
                width:auto;
                }
                #menu-wpdevplugin .nav-tabs-wrapper {
                height:28px;
                margin-bottom:-1px;
                overflow:hidden;
                width:100%;
                }
                #menu-wpdevplugin .nav-tabs {
                float:left;
                margin-left:0;
                margin-right:-500px;
                padding-left:0px;
                padding-right:10px;
                }
                #menu-wpdevplugin .nav-tab {
                -moz-border-radius:5px 5px 0 0;
                -webkit-border-top-left-radius:5px;
                -webkit-border-top-right-radius:5px;
                border-color:#d5d5d5 #d5d5d5 #BBBBCC #d5d5d5;
                border-style:solid;
                border-width:1px 1px 0;
                color:#C1C1C1;
                display:inline-block;
                font-size:12px;
                line-height:16px;
                margin:0 0px -1px 0;
                padding:4px 14px 6px 32px;
                text-decoration:none;
                text-shadow:0 1px 0 #f1f1f1;
                background:none repeat scroll 0 0 #F4F4F4;
                background: #DFDFDF;
                color:#464646;
                font-weight:bold;
                margin-bottom:0;

                }
                * html #menu-wpdevplugin .nav-tab { padding:4px 14px 5px 32px; } /* IE6 */
                #menu-wpdevplugin a.nav-tab:hover{
                /*
                color:#d54e21 !important;
                background-color: #e7e7e7 !important;/**/
                }
                #menu-wpdevplugin .nav-tab-active {
                background:none repeat scroll 0 0 #ECECEC;

                border-color:#CCCCCC;
                border-bottom-color:#aab;
                background:none repeat scroll 0 0 #7A7A88;

                text-shadow:0 -1px 0 #111111;
                border-width:1px;
                color:#FFFFFF;
                }
                .menuicons{
                    position: absolute;
                    height: 20px;
                    width: 20px;
                    margin: -2px 0pt 0pt -24px;
                }
        <?php
    }

// S U P P O R T       F u n c t i o n s    //////////////////////////////////////////////////////////////////////////////////////////////////


    // Save the filter configuration for the booking listing in Ajax request
    function wpdev_ajax_save_bk_listing_filter(){ //get_user_option( 'booking_listing_filter_' . 'default' ) ;

        // Save filter of the booking listings
        update_user_option($_POST['user_id'], 'booking_listing_filter_' . $_POST['filter_name'] ,$_POST['filter_value']);

        ?>  <script type="text/javascript">
                document.getElementById('ajax_message').innerHTML = '<?php echo __('Saved' ,'booking');  ?>';
                jQuery('#ajax_message').fadeOut(1000);
            </script> <?php
        die();
    }

    // Delete the filter configuration for the booking listing in Ajax request
    function wpdev_ajax_delete_bk_listing_filter(){ //get_user_option( 'booking_listing_filter_' . 'default' ) ;

        // Delete Saved filter of the booking listings
        delete_user_option($_POST['user_id'], 'booking_listing_filter_' . $_POST['filter_name'] );

        ?>  <script type="text/javascript">
                document.getElementById('ajax_message').innerHTML = '<?php echo __('Deleted' ,'booking');  ?>';
                jQuery('#ajax_message').fadeOut(1000);
            </script> <?php
        die();
    }

    function get_sql_for_checking_new_bookings($sql_req){
        global $wpdb;
        $sql_req = "SELECT bk.booking_id FROM {$wpdb->prefix}booking as bk
                    INNER JOIN {$wpdb->prefix}bookingtypes as bt
                    ON  bk.booking_type = bt.booking_type_id WHERE bk.is_new = 1";
        return $sql_req;
    }



    //   M o d i f y   --   S Q L
    function get_bk_dates_4_edit($mysql, $bk_type, $approved) {

//TODO: Edited after hotel version corrections            if ( class_exists('wpdev_bk_biz_l') ) { return; } // Already exist at that class

        global $wpdb;
        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $my_booking_id = $my_booking_id_type[0];
                //$bk_type        = $my_booking_id_type[1];
            } else $my_booking_id = '-1';
            $skip_bookings = ' AND bk.booking_id <>' .$my_booking_id . ' ';
        } else { $skip_bookings = ''; }

        if ($approved == 'all')
              $sql_req =   "SELECT DISTINCT dt.booking_date

                 FROM {$wpdb->prefix}bookingdates as dt

                 INNER JOIN {$wpdb->prefix}booking as bk

                 ON    bk.booking_id = dt.booking_id

                 WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type  IN ($bk_type) ".$skip_bookings."

                 ORDER BY dt.booking_date";

        else
             $sql_req = "SELECT DISTINCT dt.booking_date

                 FROM {$wpdb->prefix}bookingdates as dt

                 INNER JOIN {$wpdb->prefix}booking as bk

                 ON    bk.booking_id = dt.booking_id

                 WHERE  dt.approved = $approved AND dt.booking_date >= CURDATE() AND bk.booking_type IN ($bk_type) ".$skip_bookings."

                 ORDER BY dt.booking_date" ;
//debuge($sql_req);
        return $sql_req;
    }

    // Check if this booking resource exist or not exist anymore
    function wpdev_is_booking_resource_exist($blank, $bk_type_id, $is_echo) {
        global $wpdb;
        $wp_q = $wpdb->prepare( "SELECT booking_type_id as id FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id = %d ",  $bk_type_id );
        $res = $wpdb->get_results( $wp_q );
        if (  count($res) == 0 ) {
            if ($is_echo) {
                ?> <script type="text/javascript">
                    if (document.getElementById('booking_form_div<?php echo $bk_type_id; ?>') !== null)
                        document.getElementById('booking_form_div<?php echo $bk_type_id; ?>').innerHTML = '<?php echo __('This booking resources does not exist' ,'booking'); ?>';
                </script> <?php
            }
            return false;
        } else {
            return true;
        }

    }





//  E x p o r t to CSV  ///////////////////////////////////////////////////////////////////////

    function wpdev_ajax_export_bookings_to_csv(){


        wpdev_bk_show_ajax_message(  __('Processing','booking') . '...' , 3000, false );
        $all_booking_types = wpdebk_get_keyed_all_bk_resources(array());

        $params = str_replace('\"', '"', $_POST['csv_data']) ;
        $export_type = str_replace('\"', '"', $_POST['export_type']) ;
        $args = unserialize($params);
        if ($export_type == 'all') {
            $args['page_num']         = 1;                                      // Start export from the first page
            $args['page_items_count'] = 100000;                                 // Expot ALL bookings - Maximum: 1 000 000
        }

        $selected_id = str_replace('\"', '"', $_POST['selected_id']) ;
        if ($selected_id != '' ) {
            $selected_id = explode('|',$selected_id);
        } else 
            $selected_id = array();
        
        $bk_listing = wpdev_get_bk_listing_structure_engine( $args );           // Get Bookings structure

        $bookings       = $bk_listing[0];
        $booking_types  = $bk_listing[1];
        $bookings_count = $bk_listing[2];
        $page_num       = $bk_listing[3];
        $page_items_count= $bk_listing[4];

        $export_collumn_titles = array();

        wpdev_bk_show_ajax_message(  __('Generating columns','booking') . '...' , 3000, false );

        foreach ($bookings as $key=>$value) {
            //unset($bookings[$key]->dates);
            //unset($bookings[$key]->dates_short);
            //unset($bookings[$key]->dates_short_id);
            //unset($bookings[$key]->form_show);
            
            // Set  here booking resoutrces for the dates of reservation, which in different sub resources
            for ($ibt = 0; $ibt < count($bookings[$key]->dates_short_id); $ibt++) {
                if (! empty($bookings[$key]->dates_short_id[$ibt]) ) {
                    $bookings[$key]->dates_short[$ibt] .= ' (' . $all_booking_types[ $bookings[$key]->dates_short_id[$ibt] ]->title . ') ';
                }
            }

            $bookings[$key]->dates_show = implode(' ',$bookings[$key]->dates_short);

            $fields = $bookings[$key]->form_data['_all_'];            
            if ( class_exists('wpdev_bk_multiuser') )  {                        //FixIn: 6.0.1.10
                // Get  the owner of this booking resource                                    
                $user_bk_id = apply_bk_filter('get_user_of_this_bk_resource', false, $bookings[$key]->booking_type );
                $user_data = get_userdata( $user_bk_id );
                if (  ( ! isset($fields['user' .  $bookings[$key]->booking_type ] ) ) && ( isset($user_data->data) ) && ( isset($user_data->data->display_name) )  )
                    $fields['user' .  $bookings[$key]->booking_type ] = $user_data->data->display_name;
                if ( ( isset($user_data->data) ) && ( isset($user_data->data->display_name) )  )
                    $bookings[$key]->form_data['_all_']['user' .  $bookings[$key]->booking_type ] = $user_data->data->display_name;
            }

            foreach ($fields as $field_key=>$field_value) {

                $field_key = str_replace('[', '', $field_key);
                $field_key = str_replace(']', '', $field_key);
                if ( substr($field_key,-1* (strlen($bookings[$key]->booking_type) )) == $bookings[$key]->booking_type ) {
                    $field_key = substr($field_key,0,-1* (strlen($bookings[$key]->booking_type) ));
                }
                if (! in_array($field_key, $export_collumn_titles))
                    $export_collumn_titles[] = $field_key;
            }
        }

        wpdev_bk_show_ajax_message(  __('Exporting booking data','booking') . '...' , 3000, false );
        $export_bookings = array();
        foreach ($bookings as $key=>$value) {
            
            if ( ! empty($selected_id ) ) {     // We was selected some bookings, so we need to export only these selected bookings.                                
                if (  in_array( $value->booking_id, $selected_id ) === false )
                        continue;
            }
            
            $export_bk_row = array();
            $export_bk_row['dates']=$value->dates_show ;
            $export_bk_row['id']=$value->booking_id ;
            $export_bk_row['modification_date']=$value->modification_date ;
            $export_bk_row['booking_type']= $all_booking_types[$value->booking_type]->title;
            $export_bk_row['remark']=$value->remark ;
            $export_bk_row['cost']=$value->cost ;
            $export_bk_row['pay_status']=$value->pay_status ;

            $is_approved = 0;   if (count($value->dates) > 0 )     $is_approved = $value->dates[0]->approved ;
            if ($is_approved) $bk_print_status =  __('Approved' ,'booking');
            else              $bk_print_status =  __('Pending' ,'booking');
            $export_bk_row['status']= $bk_print_status;

            foreach ($export_collumn_titles as $field_key=>$field_value) {
                if (isset($value->form_data['_all_'][ $field_value . $value->booking_type ]))
                    $export_bk_row[$field_value] = $value->form_data['_all_'][ $field_value . $value->booking_type ] ;
                else
                    $export_bk_row[$field_value] = '';
            }

            $export_bookings[]=$export_bk_row;
        }

        // Write this collumns to the begining
        array_unshift($export_collumn_titles,'id','booking_type','status','dates','modification_date','cost','pay_status');
        $export_collumn_titles[]='remark';

//debuge( $export_collumn_titles, $export_bookings);

       wpdev_bk_show_ajax_message(  __('Generating content of file' ,'booking') , 3000, false );

       $message = wp_upload_dir();
       if ( ! empty ($message['error']) ) {
           wpdev_bk_show_ajax_message( $message['error'] , 3000, true );
           die;
       }
       $bk_baseurl = $message['baseurl'];
       $bk_upload_dir = $message['basedir'];
     
       $line__separator = get_bk_option( 'booking_csv_export_separator' );
       if ( empty( $line__separator ) ) 
           $line__separator  = ';';
       
       $csv_file_content = '';
       $write_line = '';

        // Write Titles
       foreach ($export_collumn_titles as $line) { $write_line .= "\"".$line."\"". $line__separator; }
       $write_line=substr_replace($write_line,"",-1);    // replace last charcater "," in EOL
       $write_line.= "\r\n";
       $csv_file_content .= $write_line;

       // Write Values
       foreach ($export_bookings as $line) {
           $write_line = '';

           foreach ($export_collumn_titles as $key) {    // Because titles have all keys, we loop keys from titles and then get and write values
               $line[$key] = html_entity_decode( $line[$key], ENT_QUOTES, 'UTF-8' ); 
               if (isset( $line[$key] )) $write_line .= "\"".$line[$key]."\"". $line__separator;
               else                      $write_line .= "\"". "\"". $line__separator;

           }

           $write_line=substr_replace($write_line,"",-1);    // replace last charcater "," in EOL
           $write_line.= "\r\n";
           $csv_file_content .= $write_line;

       }

//debuge($csv_file_content);

       wpdev_bk_show_ajax_message(  __('Saving to file','booking') . '...' , 3000, false );

       $dir      = $bk_upload_dir; 
       $filename = 'bookings_export.csv';
       $fp =    fopen(  $dir . '/' .  $filename , 'w' );                        // Write File
       fwrite($fp, trim($csv_file_content) );
       fclose($fp);


       ?>




           <div id="exportBookingsModal" class="modal" >
              <div class="modal-header">
                  <a class="close" data-dismiss="modal">&times;</a>
                  <h3><?php _e('Export bookings' ,'booking'); ?></h3>
              </div>
              <div class="modal-body">
                <label class="help-block"><?php printf(__('Download the CSV file of exported booking data' ,'booking'),'<b>',',</b>');?></label>
              </div>
               <div class="modal-footer" style="text-align:center;">
                <?php 
                if ( file_exists( dirname(__FILE__) . '/../../../uploads/' . $filename ) ) $csv_position = '';
                else $csv_position = '?csv_dir=' . $bk_upload_dir ;                
                ?>   
                <a href="<?php echo WPDEV_BK_PLUGIN_URL . '/inc/wpdev-get-exported-csv.php' . $csv_position ; ?>" target="_blank" class="button button-primary"  style="float:none;" >
                    <?php _e('Download' ,'booking'); ?>
                </a>
                <a href="javascript:void(0)" class="button" style="float:none;" data-dismiss="modal"><?php _e('Close' ,'booking'); ?></a>
              </div>
           </div>
           <script type="text/javascript"> 
                    jQuery("#ajax_respond").after( jQuery("#ajax_respond #exportBookingsModal") );
                    jQuery("#exportBookingsModal").modal("show");
           </script>
       <?php
       wpdev_bk_show_ajax_message(  __('Done','booking') , 1000, true );

    }

// S H O R T C O D E    Select Booking form using the select box

    // Shortcode to  show cost  or Title of booking resource
    function wpbc_booking_resource_info( $return_data_info, $attr ) {
                
        if ( isset( $attr['type'] ) ) 
            $my_boook_type = $attr['type'];                    
        else 
            $my_boook_type = 1;
                
        if ( isset( $attr['show'] ) )  
            $show_info = $attr['show'];                    
        else
            $show_info = 'title';
              
        
        $booking_resource_attr = get_booking_resource_attr( $my_boook_type );
        
        if ( ! empty($booking_resource_attr) ) {
            
            switch ( $show_info ) {
                case 'title':
                    if ( isset( $booking_resource_attr->title ) ) {
                        $bk_res_title = apply_bk_filter('wpdev_check_for_active_language', $booking_resource_attr->title );
                        return $bk_res_title;
                    }
                    break;
                    
                case 'cost':
                    if (  ( class_exists('wpdev_bk_biz_s') ) && ( isset ($booking_resource_attr->cost ) )  ) {
                        $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
                        return $cost_currency . wpdev_bk_cost_number_format( $booking_resource_attr->cost );
                    }
                    break;
                    
                case 'capacity':
                    if (  ( class_exists('wpdev_bk_biz_l') )  ) {                        
                        $number_of_child_resources = apply_bk_filter('wpbc_get_number_of_child_resources', $my_boook_type );        
                        return  $number_of_child_resources ;
                    }
                    break;

                default:
                    break;
            }            
        }
        
        return $return_data_info;
    }
    
    
    // shortcode for Selection  of booking resources 
    function wpdev_get_booking_select_form($booking_select_form, $attr){    global $wpdb;

       if ( isset( $attr['nummonths'] ) ) { $my_boook_count = $attr['nummonths'];  }
       else $my_boook_count = 1;

       if ( isset( $attr['type'] ) )      { $my_boook_type = $attr['type'];        }

       if ( isset( $attr['form_type'] ) ) { $my_booking_form = $attr['form_type']; }
       else $my_booking_form = 'standard';

       if ( isset( $attr['selected_type'] ) ) { 
           $selected_booking_resource = $attr['selected_type'];            
       } else {
           $selected_booking_resource = '';
       }
       if ( isset($_GET['resource_id'] ) ) 
               $selected_booking_resource = $_GET['resource_id'];
       
       
       if ( isset( $attr['label'] ) ) { $label = $attr['label']; }
       else $label = '';

       if ( isset( $attr['first_option_title'] ) ) { $first_option_title = $attr['first_option_title']; }
       else $first_option_title = __('Please Select' ,'booking');

       $first_option_title = apply_bk_filter('wpdev_check_for_active_language',  $first_option_title );
       
       if (! empty($label))
            $booking_select_form  .= '<label for="calendar_type">'.$label.'</label>';
       
       $booking_select_form .= '<select name="active_booking_form" onchange="jQuery(\'.bk_forms\').css(\'display\', \'none\');';
       $booking_select_form .= 'document.getElementById(\'hided_booking_form\' + this.value).style.display=\'block\';" >';
       
       if ( ! empty($first_option_title) )
            $booking_select_form .= ' <option value="select" ' . ( ( $selected_booking_resource == '' )?' selected="selected" ':'' ) . '>' . $first_option_title . '</option> ';

       $my_selected_dates_without_calendar = ''; 
       $start_month_calendar = false; 
       $bk_otions=array();
        if ( isset( $attr['startmonth'] ) ) { // Set start month of calendar, fomrat: '2011-1'
            $start_month_calendar = explode( '-', $attr['startmonth'] );
            if ( (is_array($start_month_calendar))  && ( count($start_month_calendar) > 1) ) { }
            else $start_month_calendar = false;
        }
        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }


       // Select the booking resources
       if ( ! empty($my_boook_type) ) $where = ' WHERE booking_type_id IN ('.$my_boook_type.') ' ;
       else                           $where = ' ';

       $or_sort = 'title_asc';
       if ( class_exists('wpdev_bk_biz_l')) $or_sort = 'prioritet';

       if (strpos($or_sort, '_asc') !== false) {                            // Order
               $or_sort = str_replace('_asc', '', $or_sort);
               $sql_order = " ORDER BY " .$or_sort ." ASC ";
       } else $sql_order = " ORDER BY " .$or_sort ." DESC ";

       if ( class_exists('wpdev_bk_biz_m'))
           $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, default_form as form FROM {$wpdb->prefix}bookingtypes" . $where . $sql_order);
       else
           $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title FROM {$wpdb->prefix}bookingtypes" . $where . $sql_order);

       // Sort booking resources by order, which  set in the "type" parameter of bookingselect shortcode.
       if ( ! empty($my_boook_type) ) {
            $br_data_array = array();
            foreach ( $types_list as $br_data ) {
                $br_data_array[ $br_data->id ] = $br_data;
            }
            $br_ordered_array = array();
            $br_order = explode(',', $my_boook_type);
            foreach ( $br_order as $br_id ) {
                if ( isset( $br_data_array[ $br_id ] ) ) {
                    $br_ordered_array[] = $br_data_array[ $br_id ];
                }
            }
            $types_list = $br_ordered_array;
       }

       if ( ( empty($first_option_title) ) && empty( $selected_booking_resource) && (! empty($types_list)) ) {
           $selected_booking_resource = $types_list[0]->id;
       }
       
       foreach ($types_list as $tl) {
        if ( $selected_booking_resource == $tl->id ) 
             $is_res_selected = ' selected="SELECTED" ';
        else $is_res_selected = '';
        $bk_res_title = apply_bk_filter('wpdev_check_for_active_language', $tl->title );
        $booking_select_form .= ' <option '.$is_res_selected.' value="'.$tl->id.'">'.$bk_res_title.'</option>';
       }
       $booking_select_form .= ' </select><br/><br/>';

       foreach ($types_list as $tl) {
           
        if ( $selected_booking_resource == $tl->id )
             $is_res_selected = 'display: block;';
        else $is_res_selected = 'display: none;';
           
        $booking_select_form .= ' <div class="bk_forms" id="hided_booking_form'.$tl->id.'" style="'.$is_res_selected.'">';

         //$my_boook_type=1,$my_boook_count=1, $my_booking_form = 'standard',  $my_selected_dates_without_calendar = '', $start_month_calendar = false
        if ( ( isset($tl->form) ) && ( ! isset( $attr['form_type'] ) ) )
            $booking_select_form .= apply_bk_filter('wpdevbk_get_booking_form', $tl->id , $my_boook_count, $tl->form, $my_selected_dates_without_calendar, $start_month_calendar, $bk_otions );
        else
            $booking_select_form .= apply_bk_filter('wpdevbk_get_booking_form', $tl->id , $my_boook_count, $my_booking_form, $my_selected_dates_without_calendar, $start_month_calendar, $bk_otions );

        $booking_select_form .= '</div>';
       }

       return $booking_select_form;
    }


//     H   A   S   H                          //HASH_EDIT /////////////////////////////////////////////////////////////////////////////////////////

    // Get booking ID and type by booking HASH    // Edit exist booking - get ID of this booking
    function get_hash_to_id($blank, $booking_hash){

            if ($booking_hash != '') {
                global $wpdb;
                $sql = $wpdb->prepare( "SELECT booking_id as id, booking_type as type FROM {$wpdb->prefix}booking as bk  WHERE  bk.hash = %s", $booking_hash );
                $res = $wpdb->get_results( $sql );
                if (isset($res))
                    if( (count($res>0)) && (isset($res[0]->id)) && (isset($res[0]->type)) ){
                        return array($res[0]->id, $res[0]->type);
                    }
            }
            return false;
    }

    // Get booking ID and type by booking HASH    // Edit exist booking - get ID of this booking
    function get_id_using_hash($blank, $booking_id){

            if ($booking_id!= '') {
                global $wpdb;
                $sql = $wpdb->prepare( "SELECT hash, booking_type as type FROM {$wpdb->prefix}booking as bk  WHERE  bk.booking_id = %s", $booking_id );
                $res = $wpdb->get_results( $sql );

                if (count($res>0)) {
                    return array($res[0]->hash, $res[0]->type);
                }
            }
            return false;
    }

    function get_params_of_shortcode_in_string($shortcode, $subject) {
        $pos = strpos($subject, '['.$shortcode );
        if ( $pos !== false ) {
           $pos2 = strpos($subject, ']', ($pos+2));

           $my_params = substr($subject, $pos+strlen('['.$shortcode), ( $pos2-$pos-strlen('['.$shortcode) ) );

            $pattern_to_search = '%\s*([^=]*)=[\'"]([^\'"]*)[\'"]\s*%';
            preg_match_all($pattern_to_search, $my_params, $keywords, PREG_SET_ORDER);

            foreach ($keywords as $value) {
                if (count($value)>1) {
                    $shortcode_params[ $value[1] ] = trim($value[2]);
                }
            }
            $shortcode_params['start']=$pos+1;
            $shortcode_params['end']=$pos2;

            return $shortcode_params;
        } else
           return false;
    }


    // Check email body for booking editing link and replace this shortcode by link
    function set_booking_edit_link_at_email($mail_body,$booking_id ){


                $edit_url_for_visitors = get_bk_option( 'booking_url_bookings_edit_by_visitors');
                $edit_url_for_visitors =  apply_bk_filter('wpdev_check_for_active_language', $edit_url_for_visitors );

                $my_hash_start_parameter = '&booking_hash=';
                if (strpos($edit_url_for_visitors,'?')===false) {
                    $my_hash_start_parameter = '';
                    if (substr($edit_url_for_visitors,-1,1) != '/' ) $my_hash_start_parameter .= '/';
                    $my_hash_start_parameter .= '?booking_hash=';
                }
                $edit_url_for_visitors .= $my_hash_start_parameter;

                $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_using_booking_id',false, $booking_id );
                $my_edited_bk_hash = '';
                if ($my_booking_id_type !== false) {
                    $my_edited_bk_hash    = $my_booking_id_type[0];
                    $my_boook_type        = $my_booking_id_type[1];
                    $edit_url_for_visitors .= $my_edited_bk_hash;
                    //if ($my_boook_type == '') return __('Wrong booking hash in URL (probably expired)' ,'booking');
                } else { $edit_url_for_visitors = '';}

                $mail_body = str_replace('[visitorbookingediturl]', $edit_url_for_visitors /*'<a href= "'.$edit_url_for_visitors.'" >' . __('Edit booking' ,'booking') . '</a>' */ , $mail_body);

                $mail_body = str_replace('[visitorbookingcancelurl]', $edit_url_for_visitors . '&booking_cancel=1'   , $mail_body);

                $mail_body = str_replace('[visitorbookingpayurl]', 
                        ' <a href="'. $edit_url_for_visitors . '&booking_pay=1' .'" >' .__('link' ,'booking') .'</a> ' ,
                        $mail_body);

                $mail_body = str_replace('[bookinghash]',$my_edited_bk_hash,$mail_body);


                // Check for URL parameter in the shortcodes
                $shortcode_params = $this->get_params_of_shortcode_in_string('visitorbookingediturl', $mail_body);
                if (! empty($shortcode_params) ) {
                   if ( isset($shortcode_params[ 'url' ]) ) {
                      $shortcode_params[ 'url' ] = str_replace('"', '', $shortcode_params[ 'url' ]);
                      $shortcode_params[ 'url' ] = str_replace("'", '', $shortcode_params[ 'url' ]);

                      $my_hash_start_parameter = '&booking_hash=';
                      if (strpos($shortcode_params[ 'url' ],'?')===false) {
                            $my_hash_start_parameter = '';
                            if (substr($shortcode_params[ 'url' ],-1,1) != '/' ) $my_hash_start_parameter .= '/';
                            $my_hash_start_parameter .= '?booking_hash=';
                      }
                      $mail_body_temp = substr($mail_body, 0, ($shortcode_params['start']-1) );
                      if ($my_booking_id_type !== false) { // Check if the HASH Exist at all  there
                             $mail_body_temp .= $shortcode_params[ 'url' ] . $my_hash_start_parameter . $my_edited_bk_hash ;
                      }
                      $mail_body_temp .= substr($mail_body, ($shortcode_params['end']+1) );
                      $mail_body = $mail_body_temp;
                   }
                }

                // Check for URL parameter in the shortcodes
                $shortcode_params = $this->get_params_of_shortcode_in_string('visitorbookingcancelurl', $mail_body);
                if (! empty($shortcode_params) ) {
                   if ( isset($shortcode_params[ 'url' ]) ) {
                      $shortcode_params[ 'url' ] = str_replace('"', '', $shortcode_params[ 'url' ]);
                      $shortcode_params[ 'url' ] = str_replace("'", '', $shortcode_params[ 'url' ]);

                      $my_hash_start_parameter = '&booking_hash=';
                      if (strpos($shortcode_params[ 'url' ],'?')===false) {
                            $my_hash_start_parameter = '';
                            if (substr($shortcode_params[ 'url' ],-1,1) != '/' ) $my_hash_start_parameter .= '/';
                            $my_hash_start_parameter .= '?booking_hash=';
                      }
                      $mail_body_temp = substr($mail_body, 0, ($shortcode_params['start']-1) );
                      if ($my_booking_id_type !== false) { // Check if the HASH Exist at all  there
                             $mail_body_temp .= $shortcode_params[ 'url' ] . $my_hash_start_parameter . $my_edited_bk_hash . '&booking_cancel=1';
                      }
                      $mail_body_temp .= substr($mail_body, ($shortcode_params['end']+1) );
                      $mail_body = $mail_body_temp;
                   }
                }

                // Check for URL parameter in the shortcodes
                $shortcode_params = $this->get_params_of_shortcode_in_string('visitorbookingpayurl', $mail_body);
                if (! empty($shortcode_params) ) {
                   if ( isset($shortcode_params[ 'url' ]) ) {
                      $shortcode_params[ 'url' ] = str_replace('"', '', $shortcode_params[ 'url' ]);
                      $shortcode_params[ 'url' ] = str_replace("'", '', $shortcode_params[ 'url' ]);

                      $my_hash_start_parameter = '&booking_hash=';
                      if (strpos($shortcode_params[ 'url' ],'?')===false) {
                            $my_hash_start_parameter = '';
                            if (substr($shortcode_params[ 'url' ],-1,1) != '/' ) $my_hash_start_parameter .= '/';
                            $my_hash_start_parameter .= '?booking_hash=';
                      }
                      $mail_body_temp = substr($mail_body, 0, ($shortcode_params['start']-1) );
                      if ($my_booking_id_type !== false) { // Check if the HASH Exist at all  there
                             $mail_body_temp .= $shortcode_params[ 'url' ] . $my_hash_start_parameter . $my_edited_bk_hash . '&booking_pay=1';
                      }
                      $mail_body_temp .= substr($mail_body, ($shortcode_params['end']+1) );
                      $mail_body = $mail_body_temp;
                   }
                }


                return $mail_body;

    }

    // Function call after booking is inserted or modificated in post request
    function wpbc_update_booking_hash( $booking_id, $bktype = '1' ) {
           global $wpdb;

            $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.hash = MD5('". time() . '_' . rand(1000,1000000)."') WHERE bk.booking_id = %d", $booking_id );
            if ( false === $wpdb->query( $update_sql ) ) {
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $bktype; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during updating hash in BD',__FILE__,__LINE__); ?></div>'; </script> <?php
                die();
            }/**/

    }

    // chnage hash of booking after approval process
    function booking_aproved_afteraction ( $res, $booking_form_show) {
        $is_change_hash_after_approvement = get_bk_option( 'booking_is_change_hash_after_approvement');
        if( $is_change_hash_after_approvement == 'On' )
            $this->wpbc_update_booking_hash( $res->booking_id );
    }




//  P r i n t   l o y o u t  ///////////////////////////////////////////////////////////////////////

    // write print loyout
    function premium_content_for_popups(){
        ?><div id="printLoyoutModal" class="modal" >
              <div class="modal-header">
                  <!--a class="close" data-dismiss="modal">&times;</a-->

                  <div style="text-align:right;">

                        <a href="javascript:void(0);"
                           onclick="javascript:
                                       jQuery( '#print_loyout_content_action' ).print();
                                       //window.print();
                                       jQuery('#printLoyoutModal').modal('hide');
                                       jQuery('#print_loyout_content').html('');
                                           "
                                       class="button button-primary" >
                            <?php _e('Print' ,'booking'); ?>
                        </a>
                        <a href="javascript:void(0)" class="button" data-dismiss="modal"><?php _e('Close' ,'booking'); ?></a>
                  </div>
                  <h3 style="margin-top:-27px;"><?php _e('Print bookings' ,'booking'); ?></h3>
              </div>
              <div class="modal-body">
                  <div id="print_loyout_content_action" class="">
                    <div id="print_loyout_content" class="wpdevbk"> ------ </div>
                  </div>
              </div>
              <div class="modal-footer">
              </div>
            </div><?php
    }


// D e l e t e
    // Delete some bookings by visitor request of CAncellation (Ajax request)
    function delete_booking_by_visitor(){   global $wpdb;

        make_bk_action('check_multiuser_params_for_client_side', $_POST[ "bk_type"] );

        $booking_hash = $_POST[ "booking_hash" ];
        $my_boook_type= $_POST[ "bk_type" ];
        $denyreason = __('The booking was canceled by the visitor.' ,'booking');

        $my_edited_bk_id = false;
        $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $booking_hash );


        if ($my_booking_id_type !== false) {
            $my_edited_bk_id        = $my_booking_id_type[0];
            $my_boook_type_new      = $my_booking_id_type[1];

            if ( ($my_boook_type_new == '') || ($my_boook_type_new == false) ) {

                ?>
                <script type="text/javascript">
                    document.getElementById('submiting<?php echo $my_boook_type; ?>').innerHTML = '<div class=\"submiting_content\" ><?php echo __('Wrong booking hash in URL (probably expired)' ,'booking'); ?></div>';
                    document.getElementById("submiting<?php echo $my_boook_type; ?>" ).style.display="block";
                    jQuery('#submiting<?php echo $my_boook_type; ?>').fadeOut(<?php echo get_bk_option( 'booking_title_after_reservation_time'); ?>);
                </script>
                <?php
                die;
            }
            $my_boook_type = $my_boook_type_new;
        } else {
                ?>
                <script type="text/javascript">
                    document.getElementById('submiting<?php echo $my_boook_type; ?>').innerHTML = '<div class=\"submiting_content\" ><?php echo __('Wrong booking hash in URL (probably expired)' ,'booking'); ?></div>';
                    document.getElementById("submiting<?php echo $my_boook_type; ?>" ).style.display="block";
                    jQuery('#submiting<?php echo $my_boook_type; ?>').fadeOut(<?php echo get_bk_option( 'booking_title_after_reservation_time'); ?>);
                </script>
                <?php
                die();
        }


        if ( ($my_edited_bk_id !=false) && ($my_edited_bk_id !='')) {
            $approved_id_str = $my_edited_bk_id;
            $is_send_emeils = 1;

            sendDeclineEmails($approved_id_str, $is_send_emeils, $denyreason);

            if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}bookingdates WHERE booking_id IN ($approved_id_str)"  ) ){
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $my_boook_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting dates at DB',__FILE__,__LINE__ ); ?></div>'; </script> <?php
                die();
            }

            if ( false === $wpdb->query( "DELETE FROM {$wpdb->prefix}booking WHERE booking_id IN ($approved_id_str)"  ) ){
                ?> <script type="text/javascript"> document.getElementById('submiting<?php echo $my_boook_type; ?>').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php bk_error('Error during deleting booking at DB' ,__FILE__,__LINE__); ?></div>'; </script> <?php
                die();
            }


            // Visitor cancellation
            ?> <script type="text/javascript">
                document.getElementById('submiting<?php echo $my_boook_type; ?>').innerHTML = '<div class=\"submiting_content\" ><div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php echo __('The booking has been canceled successfully' ,'booking'); ?></div></div>';
                document.getElementById("booking_form_div<?php echo $my_boook_type; ?>" ).style.display="none";
                makeScroll('#booking_form<?php echo $my_boook_type; ?>' );
                jQuery('#submiting<?php echo $my_boook_type; ?>').fadeOut(<?php echo get_bk_option( 'booking_title_after_reservation_time'); ?>);
               </script>
            <?php
            die();
        }
    }


// Resources

    //TODO: make changes  and corrections here according all versions
    function wpdev_booking_resources_show_content(){ 
//debugq(); debuge($_REQUEST);
        global $wpdb;
      if (  (! isset($_GET['tab'])) || ( $_GET['tab'] == 'resource')  ) {

        if ((isset($_POST['submit_resources']))) {

            $bk_types = $this->get_booking_types();

            // Edit ////////////////////////////////////////////////////////
            if ( ($_POST['bulk_resources_action'] == 'blank' ) || ($_POST['bulk_resources_action'] == 'edit' ) ) {

                foreach ($bk_types as $bt) { 
                      $sql_res_cost = apply_bk_filter('get_sql_4_update_bk_resources_cost', ''  , $bt );
                      $sql_res = apply_bk_filter('get_sql_4_update_bk_resources', ''  , $bt );
                      $sql_def_form = apply_bk_filter('get_sql_4_update_def_form_in_resources', ''  , $bt );

                      if ( false === $wpdb->query( 
                                        $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET title = %s", $_POST[ 'type_title' . $bt->id ] )
                                      . $sql_res_cost . $sql_def_form . $sql_res
                                      . $wpdb->prepare( " WHERE booking_type_id = %d ", $bt->id ) 
                                                 )  
                          )  bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
                }

            }

            // Delete //////////////////////////////////////////////////////
            if  ($_POST['bulk_resources_action'] == 'delete' ) {

              $delete_bk_id = '';
              foreach ($bk_types as $bt) { // Delete - Get all ID for deletion
                  if (isset($_POST['resources_items_'.$bt->id]))
                      $delete_bk_id .= $bt->id . ',';
              }

              if (! empty($delete_bk_id)) {
                  $delete_bk_id = substr($delete_bk_id,0,-1);                 // Remove last Comma
                  $delete_sql = "DELETE FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id IN ({$delete_bk_id})";

                  if ( false === $wpdb->query( $delete_sql ) )  bk_error('Error during deleting booking resources',__FILE__,__LINE__ );
              }

            }

        }


        if ((isset($_POST['submit_add_resources']))) {

            // Add new res /////////////////////////////////////////////////
              if (isset($_POST['type_number_of_resources'])) $iter = $_POST['type_number_of_resources'];
              else $iter = 1;
              for ($i = 0; $i < $iter; $i++) {

                      if ($iter > 1) $sufix = '-'.($i+1);
                      else $sufix = '';

                      $update_fields = 'title ';
                      $update_values =  '"'. wpbc_clean_parameter( $_POST['type_title_new'] . $sufix ) . '"';

                      $update_fields .= apply_bk_filter('get_sql_4_insert_bk_resources_fields_p', ''    );
                      $update_values .= apply_bk_filter('get_sql_4_insert_bk_resources_values_p', '' , $i   );
                      $update_fields .= apply_bk_filter('get_sql_4_insert_bk_resources_fields_h', ''    );
                      $update_values .= apply_bk_filter('get_sql_4_insert_bk_resources_values_h', '' , $i   );
                      $update_fields .= apply_bk_filter('get_sql_4_insert_bk_resources_fields_m', ''    );
                      $update_values .= apply_bk_filter('get_sql_4_insert_bk_resources_values_m', '' , $i   );
                      
                      if ( false === $wpdb->query( 'INSERT INTO '.$wpdb->prefix .'bookingtypes ( '.$update_fields.' ) VALUES ( '. $update_values .') ' ) )
                           bk_error('Error during adding new booking resource into DB' ,__FILE__,__LINE__);
                       else
                           make_bk_action('insert_bk_resources_recheck_max_visitors' );

              }
        }

          $alternative_color = '0';
          $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&tab=resources';

          $bk_types = $this->get_booking_types(true);
          $bk_types_all_parents = get_booking_types_all_parents_and_single();
          $all_id = array(array('id'=>0,'title'=>' - '));
          foreach ($bk_types_all_parents as $btt) {
                if (isset($btt->parent)) if ($btt->parent==0)  $all_id[] = array('id'=>$btt->booking_type_id, 'title'=> $btt->title);
          }

        $advanced_params = array();

        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
        if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) {
            $booking_forms_extended = get_bk_option( 'booking_forms_extended');
            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) {
                    $booking_forms_extended = unserialize($booking_forms_extended);                   
                }
                 $advanced_params['custom_forms'] = $booking_forms_extended;
            }
        }

        make_bk_action('wpdev_bk_booking_resource_page_before');
        ?><div style="clear:both;width:100%;height:1px;"></div>

                <div style="position: absolute; right: 15px;top: 25px;" class="wpdevbk">
                <form  name="booking_filters_formID" action="" method="post" id="booking_filters_formID" class=" form-search">
                    <?php if (isset($_REQUEST['wh_resource_id']))  $wh_resource_id = $_REQUEST['wh_resource_id'];                  //  {'1', '2', .... }
                          else                                    $wh_resource_id      = '';                    ?>
                    <input class="input" type="text" placeholder="<?php _e('Resource ID or Title' ,'booking'); ?>" name="wh_resource_id" id="wh_resource_id" value="<?php echo $wh_resource_id; ?>" >
                    <input class="input" type="hidden"  name="page_num" id="page_num" value="1" >
                    <button class="button button-secondary" type="submit"><?php _e('Go' ,'booking'); ?></button>
                </form>
                </div><?php



        $max_num = apply_bk_filter('get_max_res_num_for_user_in_multiuser', false );
        $is_show_add_resource = 0;
        if (isset($_GET['wpdev_edit_rates'])) $is_show_add_resource = $_GET['wpdev_edit_rates'];
        if (isset($_GET['wpdev_edit_costs_from_days'])) $is_show_add_resource = $_GET['wpdev_edit_costs_from_days'];
        if (isset($_GET['wpdev_edit_avalaibility'])) $is_show_add_resource = $_GET['wpdev_edit_avalaibility'];
        if (isset($_GET['wpdev_edit_costs_deposit_payment'])) $is_show_add_resource = $_GET['wpdev_edit_costs_deposit_payment'];

        if (  ( ($max_num === false) || ($max_num > count($bk_types) ) ) && ($is_show_add_resource === 0)  ) { ?>

            <div class='meta-box' style="width:99%;margin-bottom:20px;">
                <div <?php $my_close_open_win_id = 'bk_resource_settings_add_new_resources'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                    <h3 class='hndle'><span><?php _e('Add New Booking Resource(s)' ,'booking'); ?></span></h3> <div class="inside">

                    <form  name="post_option_add_resources" action="" method="post" id="post_option_add_resources" >
                    <table class="form-table"><tbody>
                        <tr valign="top">
                            <th scope="row"><label for="type_title_new"><?php _e('New Resource' ,'booking'); ?>:</label></th>
                            <td>                                    
                                <input type="text" value="" id="type_title_new" name="type_title_new" 
                                       class="span3"  
                                       placeholder="<?php echo __('Enter title here' ,'booking'); ?>..."
                                       autocomplete="off"  tabindex="1" maxlength="200" />
                                <p  class="description"><?php _e('Enter name of booking resource' ,'booking'); ?></p>
                            </td>
                        </tr>

                        <?php make_bk_action('resources_settings_table_add_bottom_button',  $all_id  ); ?>

                        <tr>
                            <td style="border-top: 1px solid #ccc;" colspan="2" >
                                <!--div class="booking-advanced-shifter"> [ <span class="minus-plus-booking-advanced-shifter">-</span> ] &nbsp; <a href="javascript:void(0)"
                                     onclick="javascript:jQuery('#resource-add-new-advanced-options').slideToggle('slow');if (jQuery('.minus-plus-booking-advanced-shifter').html() == '-') jQuery('.minus-plus-booking-advanced-shifter').html('+'); else jQuery('.minus-plus-booking-advanced-shifter').html('-');"> <?php _e('Advanced Options' ,'booking'); ?></a></div-->
                                <input class="button-primary button" type="submit" value="+ <?php _e('Add new resource(s)' ,'booking'); ?>" name="submit_add_resources"/>
                            </td>
                        </tr>

                    </tbody></table>
                    </form>
                </div></div></div>

        <?php } ?>

        <div style="width:100%;">
            <form  name="post_option_resources" action="" method="post" id="post_option_resources" >
                
                <div class="clear" style="height:25px;width:100%;clear:both;"></div>
                <div style="height:auto;">

                    <select name="bulk_resources_action" id="bulk_resources_action" style="float:left;width:110px;margin-right:10px;" >
                        <option value="blank"><?php _e('Bulk Actions' ,'booking') ?></option>
                        <option value="edit"v><?php _e('Edit' ,'booking') ?></option>
                        <option value="delete"><?php _e('Delete' ,'booking') ?></option>
                    </select>
                    <input class="button-primary button" style="float:left;margin-top:1px;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_resources"/>
                    <?php if (isset($_REQUEST['page_num'])) { ?>
                        <input class="input" type="hidden"  name="page_num" id="page_num" value="<?php echo $_REQUEST['page_num']; ?>" >
                    <?php } if (isset($_REQUEST['wh_resource_id'])) { ?>
                        <input class="input" type="hidden"  name="wh_resource_id" id="wh_resource_id" value="<?php echo $_REQUEST['wh_resource_id']; ?>" >
                    <?php } ?>
                </div>
                <div class="clear" style="height:15px;width:100%;clear:both;"></div>
                
                <table style="width:99%;" class="resource_table booking_table" cellpadding="0" cellspacing="0">
                        <?php // Headers  ?>
                    <tr>
                        <th style="width:15px;"><input type="checkbox" onclick="javascript:jQuery('.resources_items').attr('checked', this.checked);" class="resources_items" id="resources_items_all"  name="resources_items_all" /></th>
                        <th style="width:10px;height:35px;border-left: 1px solid #BBBBBB;"> <?php _e('ID' ,'booking'); ?> </th>
                        <th style="height:35px;width:215px;"> <?php _e('Resource name' ,'booking'); ?> </th>
                        <?php make_bk_action('resources_settings_table_headers' ); ?>
                        <th style="text-align:center;"> <?php _e('Info' ,'booking'); ?> </th>
                    </tr>
                    <?php
                    if (! empty($bk_types))
                      foreach ($bk_types as $bt) {
                              if ( $alternative_color == '')    $alternative_color = ' class="alternative_color" ';
                              else                              $alternative_color = '';

                              if ($is_show_add_resource == $bt->id ) $alternative_color = ' class="resource_line_selected" ';
                           ?>
                           <tr>
                                <td <?php echo $alternative_color; ?> ><legend class="wpbc_mobile_legend"><?php _e('Selection' ,'booking'); ?>:</legend><input type="checkbox" class="resources_items" id="resources_items_<?php echo $bt->id; ?>"  name="resources_items_<?php echo $bt->id; ?>" /></td>
                                <td style="border-left:1px solid #ccc;text-align: center;" <?php echo $alternative_color; ?> ><legend class="wpbc_mobile_legend"><?php _e('ID' ,'booking'); ?>:</legend><?php echo $bt->id; ?></td>
                                <td style="<?php if (isset($bt->parent)) if ($bt->parent != 0 ) { echo 'padding-left:50px;'; } ?>" <?php echo $alternative_color; ?> >
                                    <legend class="wpbc_mobile_legend" style="<?php  if ( (isset($bt->parent)) && ($bt->parent == 0 ) ) { echo 'font-weight:bold;'; } ?>" ><?php _e('Resource name' ,'booking'); ?>:</legend>
                                    <input  maxlength="200" type="text"
                                        style="<?php  if (isset($bt->parent)) if ($bt->parent == 0 ) { echo 'width:210px;font-weight:bold;'; } else { echo 'width:170px;'; } ?>"
                                        value="<?php echo $bt->title; ?>"
                                        name="type_title<?php echo $bt->id; ?>" id="type_title<?php echo $bt->id; ?>" />
                                    <?php if (isset($bt->parent)) if ($bt->parent == 0 ) { make_bk_action('resources_settings_after_title', $bt, $all_id, $alternative_color ); } ?>
                                </td>

                                <?php make_bk_action('resources_settings_table_collumns', $bt, $all_id, $alternative_color , $advanced_params); ?>

                                <td style="border-right: 0px;border-left: 1px solid #ccc;text-align: center;" <?php echo $alternative_color; ?> >
                                    <legend class="wpbc_mobile_legend"><?php _e('Info' ,'booking'); ?>:</legend>
                                    <?php make_bk_action('resources_settings_table_info_collumns', $bt, $all_id, $alternative_color ); ?>
                                </td>

                           </tr>
                           <?php
                      }
                            ?>
                        <tr class="wpbc_table_footer">
                            <td></td>
                            <td></td>
                            <td></td>
                            <?php make_bk_action('resources_settings_table_footers' ); ?>
                            <td></td>
                        </tr>
                        </table>

                        <div class="clear" style="height:10px;width:100%;clear:both;"></div>
                        <input class="button-primary button" style="float:left;margin-top:1px;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_resources"/>
                        <div class="clear" style="height:5px;width:100%;clear:both;"></div>
                        <?php // Show Pagination
                        echo '<div class="wpdevbk" style="clear:both;">';
                        $active_page_num = (isset($_REQUEST['page_num']))?$_REQUEST['page_num']:1;
                        $items_count_in_page=get_bk_option( 'booking_resourses_num_per_page');
                        wpdevbk_show_pagination(get_booking_resources_count(), $active_page_num, $items_count_in_page, array('page','tab', 'wh_resource_id'));
                        echo '</div>';
                        ?>

                        <div class="clear" style="height:1px;"></div>

                    </form>

        </div>

        <div style="clear:both;width:100%;height:1px;"></div><?php
        make_bk_action('wpdev_bk_booking_resource_page_after');
      }
    }



// S e t t i n g s    

    // Settings for selecting default booking resource
    function settings_set_default_booking_resource(){
         if ( isset( $_POST['default_booking_resource'] ) ) {
              update_bk_option( 'booking_default_booking_resource', $_POST['default_booking_resource'] );
              update_bk_option( 'booking_resourses_num_per_page', $_POST['resourses_num_per_page'] );
         }
         $default_booking_resource = get_bk_option( 'booking_default_booking_resource');
         $resourses_num_per_page = get_bk_option( 'booking_resourses_num_per_page');
        ?>

        <tr valign="top"  class="ver_pro">
            <th scope="row"><label for="default_booking_resource" ><?php _e('Default booking resource' ,'booking'); ?>:</label></th>
            <td>
                <?php  $bk_resources = get_booking_types_all_parents_and_single(); ?>
                <select id="default_booking_resource" name="default_booking_resource" >

                    <option <?php if($default_booking_resource == '' ) echo "selected"; ?> value=""
                          style="font-weight:bold;border-bottom:1px solid #ccc;"
                        ><?php _e('All resources' ,'booking'); ?></option>

                    <?php foreach ($bk_resources as $mm) { ?>
                    <option <?php if($default_booking_resource == $mm->booking_type_id ) echo "selected"; ?> value="<?php echo $mm->booking_type_id; ?>"
                          style="<?php if  (isset($mm->parent)) if ($mm->parent == 0 ) { echo 'font-weight:normal;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                        ><?php echo $mm->title; ?></option>
                    <?php } ?>

                </select>
                <span class="description"><?php _e('Select your default booking resource.' ,'booking');?></span>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="resourses_num_per_page" ><?php _e('Resources number per page' ,'booking'); ?>:</label></th>
            <td>

                <?php  $order_array = array( 5, 10, 20, 25, 50, 75, 100, 500 ); ?>
                <select id="resourses_num_per_page" name="resourses_num_per_page">
                <?php foreach ($order_array as $mm) { ?>
                    <option <?php if($resourses_num_per_page == strtolower($mm) ) echo "selected"; ?> value="<?php echo strtolower($mm); ?>"><?php echo ($mm) ; ?></option>
                <?php } ?>
                </select>
                <span class="description"><?php _e('Select number of booking resources (single or parent) per page at Resource menu page' ,'booking');?></span>
            </td>
        </tr>
        <?php
    }

    // Set field for editing of URL to edit bookings by visitor.
    function settings_edit_booking_url(){
         if ( isset( $_POST['url_bookings_edit_by_visitors'] ) ) {
              update_bk_option( 'booking_url_bookings_edit_by_visitors',  esc_url_raw( $_POST['url_bookings_edit_by_visitors'] ) );
         }
         $url_bookings_edit_by_visitors = get_bk_option( 'booking_url_bookings_edit_by_visitors');
        ?>
         <tr valign="top" class="ver_pro">
            <th scope="row"><label for="url_bookings_edit_by_visitors" ><?php _e('URL to edit bookings' ,'booking'); ?>:</label></th>
            <td><input id="url_bookings_edit_by_visitors"  name="url_bookings_edit_by_visitors" class="large-text" type="text" size="145" value="<?php echo $url_bookings_edit_by_visitors; ?>" />
                <p class="description"><?php printf(__('Type URL for %svisitors%s to edit bookings. You must insert %s shortcode into this page.' ,'booking'),'<b>','</b>', '<code>[bookingedit]</code>');
                echo ' '; printf(__('Please read more info about configuration of this parameter %shere%s' ,'booking'),'<a href="http://wpbookingcalendar.com/faq/configure-editing-cancel-payment-bookings-for-visitors/" target="_blank">','</a>');
                ?></p>
            </td>
        </tr>
        <?php
    }

    // Set update or not of hash during approvemant of booking
    function settings_advanced_set_update_hash_after_approve(){
        if (isset($_POST['new_booking_title'])) {
                 if (isset( $_POST['is_change_hash_after_approvement'] ))       $is_change_hash_after_approvement = 'On';
                 else                                                           $is_change_hash_after_approvement = 'Off';
                 update_bk_option( 'booking_is_change_hash_after_approvement' , $is_change_hash_after_approvement );
        }
        $is_change_hash_after_approvement = get_bk_option( 'booking_is_change_hash_after_approvement');
        ?>  <tr valign="top" class="ver_premium">
                <th scope="row">
                    <?php _e('Change hash after the booking is approved' ,'booking'); ?>:
                </th>
                <td>
                    <fieldset>
                        <label for="is_change_hash_after_approvement" >
                            <input <?php if ($is_change_hash_after_approvement == 'On') echo "checked";/**/ ?>  value="<?php echo $is_change_hash_after_approvement; ?>" name="is_change_hash_after_approvement" id="is_change_hash_after_approvement" type="checkbox" />
                            <?php _e('Check this box if you want to change the booking hash after approval. When checked, visitor will not be able to edit or cancel the booking.' ,'booking');?>
                        </label>
                    </fieldset>
                </td>
            </tr>
        <?php
    }



    function show_additional_translation_shortcode_help(){ ?>          
      <div class="wpbc-help-message">
          <strong><?php printf(__('Configuration in several languages' ,'booking'));?>.</strong><br />
        <?php printf(__('%s - start new translation section, where %s - locale of translation' ,'booking'),'<code>[lang=LOCALE]</code>','<code>LOCALE</code>');?><br />
        <?php printf(__('Example #1: %s - start French translation section' ,'booking'),'<code>[lang=fr_FR]</code>');?><br/>
        <?php printf(__('Example #2: "%s" - English and French translation of some message' ,'booking'),'<code>Thank you for your booking.[lang=fr_FR]Je vous remercie de votre reservation.</code>');?>
      </div>          
      <?php
    }


    // Emails sub menu line
    function wpdev_booking_settings_top_menu_submenu_line(){

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

                    <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_email_modification').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                       rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php if ( get_bk_option( 'booking_is_email_modification_adress' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                       original-title="<?php _e('Customization of email template, which is sending after modification of booking' ,'booking');?>" >
                            <?php _e('Modified' ,'booking');?>
                        <input type="checkbox" <?php if ( get_bk_option( 'booking_is_email_modification_adress' ) == 'On' ) echo ' checked="CHECKED" '; ?>  name="booking_is_email_modification_adress_dublicated" id="booking_is_email_modification_adress_dublicated"
                               onchange="document.getElementById('is_email_modification_adress').checked=this.checked;"
                               >
                    </a>

                    <?php if ( class_exists('wpdev_bk_biz_s')) { ?>
                        <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_email_payment_request').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                           rel="tooltip" class="tooltip_bottom nav-tab  booking-submenu-tab <?php if ( get_bk_option( 'booking_is_email_payment_request_adress' ) != 'On' ) echo ' booking-submenu-tab-disabled '; ?>"
                           original-title="<?php _e('Customization of email template, which is sending to Visitor after payment request' ,'booking');?>" >
                                <?php _e('Payment request' ,'booking');?>
                            <input type="checkbox" <?php if ( get_bk_option( 'booking_is_email_payment_request_adress' ) == 'On' ) echo ' checked="CHECKED" '; ?>  name="booking_is_email_payment_request_adress_dublicated" id="booking_is_email_payment_request_adress_dublicated"
                                   onchange="document.getElementById('is_email_payment_request_adress').checked=this.checked;"
                                   >
                        </a>
                    <?php } ?>

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
                            recheck_active_itmes_in_top_menu('is_email_modification_adress', 'booking_is_email_modification_adress_dublicated');
                            <?php if ( class_exists('wpdev_bk_biz_s')) { ?>
                                recheck_active_itmes_in_top_menu('is_email_payment_request_adress', 'booking_is_email_payment_request_adress_dublicated');
                            <?php } ?>
                        });
                    </script>

                </div>
            </div>
          <?php
        }

        //$this->wpdev_booking_settings_top_menu_submenu_line_for_form_fields();
    }

    // Form fields sub menu line
    function wpdev_booking_settings_top_menu_submenu_line_for_form_fields(){

        if ( (isset($_GET['tab'])) && ( $_GET['tab'] == 'form') ) {
        ?>
            <div class="booking-submenu-tab-container">
                <div class="nav-tabs booking-submenu-tab-insidecontainer wpdevbk " style="margin:0px;">

                    <?php make_bk_action('wpdev_booking_fields_settings_top_toolbar');   //Toolbar   ?>
                    <div class="btn-group" style="margin:0 5px 5px;float: left; height: auto;">

                            <select name="select_reset_booking_form" id="select_reset_booking_form" style="margin:0px;">                                
                                    <option value="standard"><?php _e('Standard Form Template' ,'booking'); ?></option>
                                    <option value="2collumns"><?php _e('2 Columns Form Template' ,'booking'); ?></option>
                                    <option value="wizard"><?php _e('Wizard Form Template(several steps)' ,'booking'); ?></option>
                                    <?php  if ($this->wpdev_bk_biz_s !== false) { ?>
                                        <option value="payment"><?php _e('Payment Form Template' ,'booking'); ?></option>
                                        <option value="times"><?php _e('Time Slots Form Template' ,'booking'); ?></option>
                                        <?php  if (class_exists('wpdev_bk_biz_m')) { ?>
                                        <option value="timesweek"><?php _e('Time slots for different weekends Form Template' ,'booking'); ?></option>
                                        <option value="hints"><?php _e('Hints Form Template' ,'booking'); ?></option>
                                        <?php } ?>
                                    <?php } ?>
                            </select>

                            <a     data-original-title="<?php _e('Reset current Form' ,'booking'); ?>"  rel="tooltip" 
                                   class="tooltip_top button button-secondary"

                                   onclick="javascript: var sel_res_val = document.getElementById('select_reset_booking_form').options[ document.getElementById('select_reset_booking_form').selectedIndex ].value;
                                       if ( jQuery('#visibility_container_form_fields').css('display') == 'block' ) {
                                                reset_to_def_from( sel_res_val );
                                       } else { reset_to_def_from_show( sel_res_val ); } " ><?php 
                                   _e('Reset' ,'booking'); ?></a>
                            <a     data-original-title="<?php _e('Reset Booking Form and Content of Booking Fields Form' ,'booking'); ?>"  rel="tooltip" 
                                   class="tooltip_top button button-secondary" 

                                   onclick="javascript: var sel_res_val = document.getElementById('select_reset_booking_form').options[ document.getElementById('select_reset_booking_form').selectedIndex ].value; reset_to_def_from( sel_res_val ); reset_to_def_from_show( sel_res_val );" ><?php 
                                   _e('Both' ,'booking'); ?></a>

                    </div>

                    <a  class="button-primary button" 
                        style="float: right;font-size: 12px;margin: 0 5px 0 0;"
                        onclick="javascript:
                             if (jQuery('#booking_form_show').val()=='') {
                               //jQuery('.visibility_container').css('display','none');
                               jQuery('#visibility_container_form_content_data').css('display','block');
                               jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                               jQuery('.booking-submenu-tab-content').addClass('booking-submenu-tab-selected');
                               alert('<?php echo esc_js(__('Please configure the form for content of booking fields data!' ,'booking') ); ?>');
                               return;
                             };
                             if (jQuery('#booking_form').val()=='') {
                                 //jQuery('.visibility_container').css('display','none');
                                 jQuery('#visibility_container_form_fields').css('display','block');
                                 jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                                 jQuery('.booking-submenu-tab-form').addClass('booking-submenu-tab-selected');
                                 alert('<?php echo esc_js(__('Please configure the form fields!' ,'booking') ); ?>');
                                 return;
                             }; document.forms['post_settings_form_fields'].submit();" 
                        ><?php _e('Save Changes' ,'booking'); ?></a>                        
                    <div class="clear" style="height:0px;"></div>
                </div>
            </div>
            <?php /** ?>
            <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_form_fields').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
               rel="tooltip" class="tooltip_bottom nav-tab bottom-submenu-tab booking-submenu-tab booking-submenu-tab-selected booking-submenu-tab-form" original-title="<?php _e('Customization of booking form fields' ,'booking');?>"
               style="margin-left:10px;"
               >
                <?php _e('Booking Form' ,'booking');?></a>
            <a href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').css('display','none');jQuery('#visibility_container_form_content_data').css('display','block');jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');jQuery(this).addClass('booking-submenu-tab-selected');"
                rel="tooltip" class="tooltip_bottom nav-tab bottom-submenu-tab booking-submenu-tab booking-submenu-tab-content" original-title="<?php _e('Customization of fields, which showing in the Booking Listing page for the specific booking' ,'booking');?>"
                style=""
               >
                <?php _e('Content of Booking Fields' ,'booking');?></a><?php /**/ ?>
          <?php
        }
    }




    function settings_set_default_title_in_day() {

        if (isset($_POST['booking_default_title_in_day_for_calendar_view_mode'])) {
            update_bk_option( 'booking_default_title_in_day_for_calendar_view_mode', $_POST['booking_default_title_in_day_for_calendar_view_mode'] );
        }
        $booking_default_title_in_day_for_calendar_view_mode  =  get_bk_option( 'booking_default_title_in_day_for_calendar_view_mode' );
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_default_title_in_day_for_calendar_view_mode" ><?php _e('Default title of bookings' ,'booking'); ?>:</label></th>
            <td><input value="<?php echo $booking_default_title_in_day_for_calendar_view_mode; ?>" name="booking_default_title_in_day_for_calendar_view_mode" id="booking_default_title_in_day_for_calendar_view_mode" class="regular-text code" type="text" size="45" />
                <p class="description"><?php printf(__('Type %sdefault title of bookings%s in calendar view mode at Booking Listing page (You can use the shortcodes from the bottom form of Settings Fields page).' ,'booking'),'<b>','</b>');?></p>
            </td>
        </tr>
        <?php
    }

    
    /**
     * Settings for definition  of Separator for CSV exporting.
     */
    function wpdev_bk_general_settings_export_data_separator( ) {
        if (isset($_POST['booking_csv_export_separator'])) {
            update_bk_option( 'booking_csv_export_separator', $_POST['booking_csv_export_separator'] );
        }
        $booking_csv_export_separator = get_bk_option( 'booking_csv_export_separator' );
        ?>
        <tr valign="top" class="ver_premium_hotel">
             <th scope="row"><label for="booking_csv_export_separator" ><?php _e('CSV data separator' ,'booking'); ?>:</label></th>
             <td>
              <select id="booking_csv_export_separator" name="booking_csv_export_separator" style="width:110px;">
                  <?php
                  $possible_values       = array(';', ',');
                  $possible_descrittions = array(  '; - ' . __('semicolon' ,'booking') . ''
                                                 , ', - ' . __('comma' ,'booking') .''
                                                 );
                  foreach ( $possible_values as $key=>$value) { ?>
                    <option <?php if( get_bk_option( 'booking_csv_export_separator' ) == $value) echo "selected"; ?> value="<?php echo $value; ?>"><?php echo $possible_descrittions[$key]; ?></option>
                  <?php } ?>
              </select>
              <span class="description"><?php printf(__('Select separator of data for export bookings to CSV.' ,'booking'),'<b>','</b>');?></span>
             </td>
        </tr>
        <?php        
    }

// C l i e n t     s i d e     f u n c t i o n s     /////////////////////////////////////////////////////////////////////////////////////////
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){ 
        wp_localize_script('wpbc-global-vars', 'wpbc_global2', array(
              'message_time_error'  => esc_js(__('Incorrect date format' ,'booking'))
        ) );        
    }    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){         
        wp_enqueue_script( 'wpbc-meio-mask', WPDEV_BK_PLUGIN_URL . '/inc/js/jquery.meio.mask.min'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
        wp_enqueue_script( 'wpbc-personal',  WPDEV_BK_PLUGIN_URL . '/inc/js/personal'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){         
        
    }
            


// B o o k i n g     T y p e s              //////////////////////////////////////////////////////////////////////////////////////////////////


    // Get booking types from DB
    function get_booking_types($is_use_filter = false , $is_use_limit=true ) { global $wpdb;

        ////////////////////////////////////////////////////////////////////////
        // CONSTANTS
        ////////////////////////////////////////////////////////////////////////
        /*update_bk_option( 'booking_resourses_num_per_page',10);
        $defaults = array(
                'page_num' => '1',
                'page_items_count' => get_bk_option( 'booking_resourses_num_per_page')
        );

        $r = wp_parse_args( $args, $defaults );
        extract( $r, EXTR_SKIP );
        /**/
        $page_num         = (isset($_REQUEST['page_num']))?$_REQUEST['page_num']:1;         // Pagination
        $page_items_count = get_bk_option( 'booking_resourses_num_per_page');
        $page_start = ( $page_num - 1 ) * $page_items_count ;


        $sql = " SELECT * FROM {$wpdb->prefix}bookingtypes as bt" ;
        $or_sort = 'title_asc';
        //$or_sort = 'booking_type_id_asc';
        $where = '';                                                        // Where for the different situation: BL and MU
        $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
        if ($where != '') $where = ' WHERE ' . $where;
        if ( class_exists('wpdev_bk_biz_l')) {
            if ($where != '')   $where .= ' AND bt.parent = 0 ';
            else                $where .= ' WHERE bt.parent = 0 ';
            $or_sort = 'prioritet';
        }

        if (isset($_REQUEST['wh_resource_id'])) {
             if ($where == '') $where .= " WHERE " ;
             else $where .= " AND ";
             $where .= $wpdb->prepare( " ( (bt.booking_type_id = %s) ", $_REQUEST['wh_resource_id'] ) 
                       . "OR (bt.title like '%%". wpbc_clean_string_for_db( $_REQUEST['wh_resource_id'] ) ."%%') )  ";
        }

        if (strpos($or_sort, '_asc') !== false) {                            // Order
               $or_sort = str_replace('_asc', '', $or_sort);
               $sql_order = " ORDER BY " .$or_sort ." ASC ";
        } else $sql_order = " ORDER BY " .$or_sort ." DESC ";

        if ($is_use_limit) $sql_limit = $wpdb->prepare( " LIMIT %d, %d ", $page_start, $page_items_count ) ;
        else               $sql_limit = " ";

        $types_list = $wpdb->get_results(  $sql .  $where. $sql_order . $sql_limit  );



        $bk_type_id = array();                                              // Get all ID of booking resources.
        if (! empty($types_list))
        foreach ($types_list as $key=>$res) {
            $types_list[$key]->id = $res->booking_type_id;
            $bk_type_id[]=$res->booking_type_id;
        }

        // FIx: This fix do not show the "Child" booking resources at the Booking > Resources page.             
        if  (    ( ( isset($_GET['hide'] )) && ( $_GET['hide'] == 'child') ) 
//                  || ( ( isset($_GET['tab'] ) ) && ( $_GET['tab'] == 'cost') ) 
            ) {
            foreach ($types_list as $key=>$res) {
                $types_list[$key]->count = 1;
                $types_list[$key]->id = $res->booking_type_id;
            }
            return $types_list;
        }

        if ( ( class_exists('wpdev_bk_biz_l')) && (count($bk_type_id)>0) ) {

            $bk_type_id = implode(',',$bk_type_id);                         // Get all ID of PARENT or SINGLE Resources.

            $sql = " SELECT * FROM {$wpdb->prefix}bookingtypes as bt" ;

            $where = '';                                                        // Where for the different situation: BL and MU
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') $where = ' WHERE ' . $where;

            if ($where != '')   $where .= ' AND   bt.parent IN (' . $bk_type_id . ') ';
            else                $where .= ' WHERE bt.parent IN (' . $bk_type_id . ') ';

            $sql_order = 'ORDER BY parent, prioritet';                          // Order

            $linear_list_child_resources = $wpdb->get_results(  $sql . $where . $sql_order  );  // Get  child elements

            // Transfrom them into array for the future work
            $array_by_parents_child_resources = array();
            foreach ($linear_list_child_resources as $res) {
                if (! isset($array_by_parents_child_resources[$res->parent]))  $array_by_parents_child_resources[$res->parent] = array();
                $res->id = $res->booking_type_id;
                $array_by_parents_child_resources[$res->parent][] = $res;
            }


            $final_resource_array = array();
            foreach ($types_list as $key=>$res) {
                // check if exist child resources
                if ( isset($array_by_parents_child_resources[ $res->booking_type_id ])) {
                    $res->count = count( $array_by_parents_child_resources[ $res->booking_type_id ] )+1;
                } else
                    $res->count = 1;

                // Fill the parent resource
                $final_resource_array[] = $res;

                // Fill all child resources (its already sorted)
                if ( isset($array_by_parents_child_resources[ $res->booking_type_id ])) {
                    foreach ($array_by_parents_child_resources[ $res->booking_type_id ] as $child_obj) {
                        $child_obj->count = 1;
                        $final_resource_array[]  = $child_obj;
                    }
                }
            }
            $types_list = $final_resource_array;
        }

        return $types_list;

/*===========================================================================================================================================================*/

        if ( class_exists('wpdev_bk_biz_s'))  $mysql = "SELECT booking_type_id as id, title, cost FROM {$wpdb->prefix}bookingtypes  ORDER BY title";
        else                                  $mysql = "SELECT booking_type_id as id, title FROM {$wpdb->prefix}bookingtypes  ORDER BY title";

        if ( class_exists('wpdev_bk_biz_l')) {  // If Business Large then get resources from that
            $types_list = apply_bk_filter('get_booking_types_hierarhy_linear',array() );
            for ($i = 0; $i < count($types_list); $i++) {
                $types_list[$i]['obj']->count = $types_list[$i]['count'];
                $types_list[$i] = $types_list[$i]['obj'];
                //if ( ($booking_type_id != 0) && ($booking_type_id == $types_list[$i]->booking_type_id ) ) return $types_list[$i];
            }
        } else
            $types_list = $wpdb->get_results( $mysql );


        $types_list = apply_bk_filter('multiuser_resource_list', $types_list);

        return $types_list;

/**/
    }

    function get_default_booking_resource_id(){

        if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
            $bk_multiuser = apply_bk_filter('get_default_bk_resource_for_user',false);
            if ($bk_multiuser !== false) return $bk_multiuser;
        }

        global $wpdb;
        $mysql = "SELECT booking_type_id as id FROM  {$wpdb->prefix}bookingtypes ORDER BY id ASC LIMIT 1";
        $types_list = $wpdb->get_results( $mysql );
        if (count($types_list) > 0 ) $types_list = $types_list[0]->id;
        else $types_list =1;
        return $types_list;
    }

    // Show single menu Item
    function echoMenuItem( $title, $my_icon, $my_tab_id, $is_only_icons = 0){


        $my_style = '';
        if ($is_only_icons == 0){ $my_style = 'style="padding:4px 14px 6px;"';}
        if ($is_only_icons == 1){ $my_style = 'style="padding:4px 5px 6px 32px;"';}


        if (    ($_GET['booking_type'] == $my_tab_id) ||
                (  (! isset($_GET['booking_type'])) && ( (! isset($my_tab_id)) || ($my_tab_id==1)  )  )
           )  { $slct_a = 'selected'; }
        else  { $slct_a = ''; }


        //Start
        if ($slct_a == 'selected') {  $selected_title = $title;  $selected_icon = $my_icon;
            ?><span class="nav-tab nav-tab-active"  <?php echo $my_style; ?> ><?php
        } else {
            if ($my_tab_id == 'left')
              {  ?><span class="nav-tab" <?php echo $my_style;  ?> style="cursor:finger;" 
                 onclick="javascript:var marg = document.getElementById('menu_items_slide').style.marginLeft;
                     marg = marg.replace('px'  ,'');
                     marg = ( marg +10 ) + 'px';
                     document.getElementById('menu_items_slide').style.marginLeft = marg;"
                 ><?php }
            elseif ($my_tab_id == 'right')
              { ?><a class="nav-tab" <?php echo $my_style; ?> href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking&booking_type=<?php echo $my_tab_id; ?>"><?php }
            else
              { ?><a class="nav-tab" <?php echo $my_style; ?> href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking&booking_type=<?php echo $my_tab_id; ?>"><?php }
        }

        if ($is_only_icons !== 0) { // Image
            if ($is_only_icons == 1) echo '&nbsp;';
            ?><img class="menuicons" src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $my_icon; ?>"><?php
        }

        // Title
        if ($is_only_icons == 1) echo '&nbsp;';
        else echo $title;

        // End
        if (($slct_a == 'selected') || ($my_tab_id == 'left') || ($my_tab_id == 'right')) {
            ?></span><?php
        } else {
            ?></a><?php }
    }


    // Show line of adding new
    function booking_types_pages($is_edit = ''){

        $types_list = $this->get_booking_types(true, false);
        if ( $is_edit !== 'noedit' ) $link_base = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking&booking_type=' ;
        else                         $link_base = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-reservation&booking_type=' ;

        $link_base_plus = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-resources' ;


?><div class="booking-submenu-tab-container" style="margin-left:0px;border-radius:5px;-moz-border-radius:5px;-webkit-border-radius:5px;">
                <div style="margin: 10px 10px 0;" class="nav-tabs booking-submenu-tab-insidecontainer wpdevbk ">
<!--            <div style="
background:-moz-linear-gradient(center bottom , #EBEBEB, #F5F5F5) repeat scroll 0 0 transparent;
border-radius:5px;
-moz-border-radius:5px;
-webkit-border-radius:5px;
box-shadow:0 2px 3px #C8C7C7;
-moz-box-shadow:0 2px 3px #C8C7C7;
-webkit-box-shadow:0 2px 3px #C8C7C7;
height:30px;
margin-top:5px;
padding:3px 10px;">
            <div style="float:left;line-height: 32px;font-size:13px;font-weight: bold;text-shadow:0px -1px 0px #fff;color:#555;">-->
                <div style="float:left;margin:0px 10px;">
                    <fieldset>
                    <label for="calendar_type" style="vertical-align: top;"><?php _e('Booking Resource' ,'booking'); ?>:</label>
                    <select id="calendar_type" name="calendar_type"
                        onchange="javascript: if (this.value == '+') location.href='<?php echo $link_base_plus; ?>'; else location.href='<?php echo $link_base; ?>' + this.value;"
                        >
                    <?php
                         if ( $is_edit !== 'noedit') {  ?>

                                <option value="-1"
                                            style="<?php  echo 'font-weight:normal';  ?>"
                                    <?php  if (isset($_GET['booking_type'])   ) if ($_GET['booking_type'] ==  '-1' ) echo ' selected="SELECTED" ';  ?>
                                ><?php echo __('All bookings' ,'booking'); ?></option>
                                <option value="0"
                                            style="<?php  echo 'font-weight:normal';  ?>"
                                    <?php  if (isset($_GET['booking_type'])   ) if ($_GET['booking_type'] ==  '0' ) echo ' selected="SELECTED" ';  ?>
                                ><?php echo __('All incoming bookings' ,'booking'); ?></option><?php

                                ?><option value="0&bk_filter=today_new"
                                            style="<?php  echo 'font-weight:normal';  ?>"
                                    <?php  if (isset($_GET['bk_filter'])   ) if ($_GET['bk_filter'] ==  'today_new' ) echo ' selected="SELECTED" ';  ?>
                                ><?php echo __('New reservations made today' ,'booking'); ?></option><?php

                                ?><option value="0&bk_filter=today_all"
                                            style="<?php  echo 'font-weight:normal';  ?>"
                                    <?php  if (isset($_GET['bk_filter'])   ) if ($_GET['bk_filter'] ==  'today_all' ) echo ' selected="SELECTED" ';  ?>
                                ><?php echo __('Reservations for today' ,'booking'); ?></option>
                                <option value="0" >_____________________________</option><?php
                         }

                        foreach ($types_list as $tl) { 
                            // if  (! ( (isset($tl->parent)) && ($tl->parent == 0 )  &&  (isset($tl->count))  && ($tl->count > 1 ) ) ) 
                            ?>
                            <option value="<?php echo $tl->id; if  (isset($tl->parent)) if ($tl->parent == 0 )  if  (isset($tl->count))  if ($tl->count > 1 ) echo '&parent_res=1'; ?>"
                                        style="<?php if  (isset($tl->parent)) if ($tl->parent == 0 ) { echo 'font-weight:bold;padding:3px;'; } else { echo 'padding:3px 0 3px 20px;'; } ?>"
                                <?php  if (isset($_GET['booking_type'])) if ($_GET['booking_type'] ==  $tl->id ) echo ' selected="SELECTED" ';  ?>
                            ><?php echo $tl->title; ?></option>
                            <?php
                            if  (isset($tl->parent)) if ($tl->parent == 0 )  if ($tl->count > 1 ) { ?>
                                <option value="<?php echo $tl->id; ?>"
                                            style="<?php  echo 'padding:3px 0 3px 20px;';  ?>"
                                    <?php  if (isset($_GET['booking_type']) && (! isset($_GET['parent_res']))  ) if ($_GET['booking_type'] ==  $tl->id ) echo ' selected="SELECTED" ';  ?>
                                ><?php echo $tl->title; ?></option><?php
                            }

                        }
                        if ( $is_edit !== 'noedit') {  ?>
                              <option value="0" >_____________________________</option>
                              <option value="+" style="font-weight:bold" ><?php echo '+ ',__('Add new booking resource' ,'booking'); ?></option>
                        <?php } ?>
                    </select>
                    </fieldset>
                </div>

            <?php if ( $is_edit === 'noedit') {                 
                $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) {
                    if ( isset( $_GET['booking_type'] ) ) {
                        $my_booking_form_name = apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $_GET['booking_type'] );                        
                        if (! isset( $_GET['booking_form'] ) ) 
                            $_GET['booking_form'] = $my_booking_form_name;
                    }
                }
                make_bk_action('wpdev_show_booking_form_selection' );                   
            } ?>
            <?php if ( $is_edit === 'noedit') { make_bk_action('wpdev_show_autofill_button' );   } ?>
            <div class="clear" ></div>
           </div>         
        </div>
        <div class="clear topmenuitemseparatorv" style="height:0px;clear:both;border-bottom:1px solid #C5C5C5;
border-top:0 solid #EEEEEE;
margin:0 6px;" ></div>

    <?php
    }



// P A R S E   F o r m                      //////////////////////////////////////////////////////////////////////////////////////////////////
    function get_booking_form($my_boook_type, $my_booking_form = 'standard', $custom_params = array() ){    // FixIn:6.0.1.5


        $this->current_booking_type = $my_boook_type;
//debuge($_GET);
        // Get from the HASH booking data - BK RES  & BK ID ////////////////
        $my_edited_bk_id = false;
        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                if (   ($my_booking_id_type[1] == '') ){

                } else {            
                    $my_boook_type        = $my_booking_id_type[1];
                    $my_edited_bk_id = $my_booking_id_type[0];
                    $this->current_booking_type = $my_booking_id_type[1];
                }
            }
        } 
        ////////////////////////////////////////////////////////////////////

//debuge($my_booking_form);
        // Get the Booking Form content ////////////////////////////////////
        if ($my_booking_form == 'standard') {

            // S T A N D A R D
            $booking_form  = get_bk_option( 'booking_form' );

            // If we are have the name of booking form in the GET, so then  load it
            if (isset($_GET['booking_form'])) {
                $my_booking_form = $_GET['booking_form'];
                $booking_form  = apply_bk_filter('wpdev_get_booking_form', $booking_form, $my_booking_form);
            }
//debuge($my_booking_form);
            // If we are editing the specific booking, recheck  if this booking was used the custom form
            if (isset($_GET['booking_hash'])) { 

                $my_booking_form_name = apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $my_boook_type);
                $my_booking_form = $my_booking_form_name;                       //FixIn: 5.4.2
                if ( ! isset( $_GET['booking_form'] ) ) // Fix: on 2015-04-03
                    if ( ($my_booking_form_name != 'standard') && (!empty($my_booking_form_name)) )
                         $booking_form  = apply_bk_filter('wpdev_get_booking_form', $booking_form, $my_booking_form_name);
            }

        } else {  // C U S T O M
             $booking_form  = get_bk_option( 'booking_form' );
             $booking_form  = apply_bk_filter('wpdev_get_booking_form', $booking_form, $my_booking_form);
        }
        ////////////////////////////////////////////////////////////////////


        // Cehck  for the Active Language in the Booking Form.
        $booking_form =  apply_bk_filter('wpdev_check_for_active_language', $booking_form );
        // FixIn:6.0.1.5
        foreach ( $custom_params as $custom_params_key => $custom_params_value ) {
            $booking_form = str_replace( $custom_params_key, $custom_params_value, $booking_form );
        }
        // FixIn:6.0.1.5
        
        // Assign the current BK ID
        if ($my_edited_bk_id !== false)  $this->current_edit_booking = $this->get_booking_data($my_edited_bk_id);
        else                             $this->current_edit_booking =  false;

        // P A R S E     Booking Form
        $return_res = $this->form_elements($booking_form);
        //$return_res = wpdev_bk_form_elements($booking_form);

        // Parse additional elements in other versions
        $return_res = apply_bk_filter('wpdev_reapply_bk_form',$return_res, $this->current_booking_type, $my_booking_form);

        $return_res = $this->replace_bookingresource_info_in_form( $return_res, $this->current_booking_type );  //FixIn: 5.4.5.4

        // Set some custom elemtns:
        if ( $my_edited_bk_id !== false ) { $return_res .= '<input name="edit_booking_id"  id="edit_booking_id" type="hidden" value="'.$my_edited_bk_id.'">'; }

        if ( $my_booking_form != 'standard' ) { $return_res .= '<input name="booking_form_type'.$my_boook_type.'"  id="booking_form_type'.$my_boook_type.'" type="hidden" value="'.$my_booking_form.'">'; }

        // Select Dates in Calendar ////////////////////////////////////////
        if ( $my_edited_bk_id !== false ){                    
            ?><script type="text/javascript">
                jQuery(document).ready(function(){        
                    timeout_DSwindow=setTimeout("wpdevbk_select_days_in_calendar(<?php 
                                    echo $my_boook_type; ?>, <?php 
                                    echo 'new Array( ';
                                        foreach ($this->current_edit_booking['dates'] as $dt_key=>$dt) {
                                            $dt = trim($dt);
                                            $dta = explode(' ',$dt);
                                            $dta = explode('-',$dta[0]);
                                            echo 'new Array( ' . $dta[0].', '.$dta[1].', '.$dta[2] . ' ) ';
                                            if  ( $dt_key < (count($this->current_edit_booking['dates'])-1)  ) echo ', ';
                                        }
                                    echo ')'; ?> )", 1500); }); 
              </script><?php
        } //////////////////////////////////////////////////////////////////

        return $return_res;
    }

    
    /** Replace folowing shortcodes in the booking form at Booking > Settings > Fields page:
        [bookingresource show='id'] - to booking resource ID
        [bookingresource show='title'] - to booking resource Title
        [bookingresource show='cost'] - to  booking resource Cost
        [bookingresource show='capacity'] - to booking resource Capacity
        [bookingresource show='maxvisitors'] - to booking resource maximum  number of visitors per resource
     * @param string $return_form
     * @param int $bk_type
     * @return string
     */
    function replace_bookingresource_info_in_form( $return_form, $bk_type ) {   //FixIn: 5.4.5.4
        
        $patterns = array();
        
        $parameters = array( 'id', 'title', 'cost', 'capacity', 'maxvisitors' );
        foreach ( $parameters as $parameter ) {
            $patterns[] = '/\[bookingresource\s*show=\''. $parameter .'\'\\s*]/';
        }

        
        $replacements = array( $bk_type );
        
        $booking_resource_attr = get_booking_resource_attr( $bk_type );
        
        if ( ! empty($booking_resource_attr) ) {
            
            if ( isset( $booking_resource_attr->title ) ) {
                $bk_res_title = apply_bk_filter('wpdev_check_for_active_language', $booking_resource_attr->title );
                $replacements[] = $bk_res_title;
            } else $replacements[] = '';

            if (  ( class_exists('wpdev_bk_biz_s') ) && ( isset ($booking_resource_attr->cost ) )  ) {
                $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
                $cost_currency = str_replace('$','\$',$cost_currency);
                $replacements[] = $cost_currency . wpdev_bk_cost_number_format( $booking_resource_attr->cost );
            } else $replacements[] = '';

            if (  ( class_exists('wpdev_bk_biz_l') )  ) {                        
                $number_of_child_resources = apply_bk_filter('wpbc_get_number_of_child_resources', $bk_type );        
                $replacements[] = $number_of_child_resources ;
            } else $replacements[] = '';

            if (  ( class_exists('wpdev_bk_biz_l') )  ) {                        
                $max_number_of_visitors = apply_bk_filter('wpbc_get_max_visitors_for_bk_resources', $bk_type );        
                if ( isset( $max_number_of_visitors[ $bk_type ] ) )                
                    $max_number_of_visitors = $max_number_of_visitors[ $bk_type ];
                else 
                    $max_number_of_visitors = 1;
                $replacements[] = $max_number_of_visitors ;
            } else $replacements[] = '';

            
            
        }
        $replaced_form = preg_replace( $patterns, $replacements, $return_form);
             

        return $replaced_form;
    }
    
    
    function get_booking_data($booking_id){
        global $wpdb;

        if (isset($booking_id)) $booking_id = $wpdb->prepare( " WHERE  bk.booking_id = %d " , $booking_id );
        else                    $booking_id = ' ';

        $sql = "SELECT * FROM {$wpdb->prefix}booking as bk
                INNER JOIN {$wpdb->prefix}bookingdates as dt
                ON    bk.booking_id = dt.booking_id
                ". $booking_id .
                " ORDER BY dt.booking_date ASC ";

        $result = $wpdb->get_results( $sql );
        $return = array( 'dates'=>array());
        foreach ($result as $res) { $return['dates'][] = $res->booking_date; }
        $return['form'] = $res->form;
        $return['type'] = $res->booking_type;
        $return['approved'] = $res->approved;
        $return['id'] = $res->booking_id;

        // Parse data from booking form ////////////////////////////////////
        $bktype = $res->booking_type;
        $parsed_form = $res->form;
        $parsed_form = explode('~',$parsed_form);

        $parsed_form_results  = array();

        foreach ($parsed_form as $field) {
            $elemnts = explode('^',$field);
            $type = $elemnts[0];
            $element_name = $elemnts[1];
            $value = $elemnts[2];

            $count_pos = strlen( $bktype );
            //debuge(substr( $elemnts[1], 0, -1*$count_pos ))                ;
            $type_name = $elemnts[1];
            $type_name = str_replace('[]','',$type_name);
            if ($bktype == substr( $type_name,  -1*$count_pos ) ) $type_name = substr( $type_name, 0, -1*$count_pos );

            if ($type_name == 'email') { $email_adress = $value; }
            if ($type_name == 'name')  { $name_of_person = $value; }
            if ($type == 'checkbox') {
                if ($value == 'true')   { $value = 'on'; }
                else {
                    if (($value == 'false') || ($value == 'Off') || ( !isset($value) ) )  $value = '';
                }
            }
            $element_name = str_replace('[]','',$element_name);
            if ( isset($parsed_form_results[$element_name]) ) {
                if ($value !=='') $parsed_form_results[$element_name]['value'] .= ',' . $value;
            } else
                $parsed_form_results[$element_name] = array('value'=>$value, 'type'=> $type, 'element_name'=>$type_name );
        }
        $return['parsed_form'] = $parsed_form_results;
        ////////////////////////////////////////////////////////////////////
        if (isset($email_adress))   $return['email'] = $email_adress;
        if (isset($name_of_person)) $return['name']  = $name_of_person;

        return $return;
    }

            // Getted from script under GNU /////////////////////////////////////
            function form_elements($form, $replace = true) {
                    $types = 'text[*]?|email[*]?|coupon[*]?|time[*]?|textarea[*]?|select[*]?|checkbox[*]?|radio[*]?|acceptance|captchac|captchar|file[*]?|quiz';
                    $regex = '%\[\s*(' . $types . ')(\s+[a-zA-Z][0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)?((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
                    $regex_start_end_time = '%\[\s*(country[*]?|starttime[*]?|endtime[*]?)(\s*[a-zA-Z]*[0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)*((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
                    $submit_regex = '%\[\s*submit(\s[-0-9a-zA-Z:#_/\s]*)?(\s+(?:"[^"]*"|\'[^\']*\'))?\s*\]%';
                    if ($replace) {
                            $form = preg_replace_callback($regex, array(&$this, 'form_element_replace_callback'), $form);
                            // Start end time
                            $form = preg_replace_callback($regex_start_end_time, array(&$this, 'form_element_replace_callback'), $form);
                            // Submit button
                            $form = preg_replace_callback($submit_regex, array(&$this, 'submit_replace_callback'), $form);
                            return $form;
                    } else {
                            $results = array();
                            preg_match_all($regex, $form, $matches, PREG_SET_ORDER);
                            foreach ($matches as $match) {
                                    $results[] = (array) $this->form_element_parse($match);
                            }
                            return $results;
                    }
            }

            function form_element_replace_callback($matches) {
                    extract((array) $this->form_element_parse($matches)); // $type, $name, $options, $values, $raw_values
//debuge('1!!!!!', $type, $name, $options, $values, $raw_values);
                    if ( ($type == 'country') || ($type == 'country*') ) {
                        //debuge('$type, $name, $options, $values, $raw_values', $type, $name, $options, $values, $raw_values);
                        if ( empty($name) )
                            $name = $type ;
                    }
                    $name .= $this->current_booking_type ;


                    $my_edited_bk_id = false;
                    if (isset($_GET['booking_hash'])) {
                        $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
                        if ($my_booking_id_type !== false) {
                            $my_edited_bk_id = $my_booking_id_type[0];  //$bk_type        = $my_booking_id_type[1];
                        }
                    }
                    //if (isset($_GET['booking_id'])) $my_edited_bk_id = $_GET['booking_id'];
                    //else $my_edited_bk_id = false;


                    // Edit values
                    if ( $my_edited_bk_id !== false ) {
                          if (preg_match('/^(?:select|country|checkbox|radio)[*]?$/', $type)) {

                              if (isset($this->current_edit_booking['parsed_form'][$name]))
                                      if (isset($this->current_edit_booking['parsed_form'][$name]['value'])) {

                                          foreach ($options as $op_key=>$value) {
                                              if (strpos($value,'default')!==false) {   // Right now we are editing specific booking
                                                  unset($options[$op_key]);             // We are do not need th  default values, so erase it.
                                              }
                                          }  

                                        $multiple_selections = explode(',',$this->current_edit_booking['parsed_form'][$name]['value']);
                                        foreach ($multiple_selections as $s_key=>$s_value) {
                                            $options[] = 'default:' . $s_value;
                                        }                                                    
                                        //$options[0] = 'default:' . $this->current_edit_booking['parsed_form'][$name]['value'];
                                      }
                          } else {
                                $values[0] = '';
                                if ( ($type == 'starttime') || ($type == 'starttime*') || ($type == 'endtime') || ($type == 'endtime*') ) {
                                    if (isset(  $this->current_edit_booking['parsed_form'][$type . $this->current_booking_type ] ))
                                        $values[0] = $this->current_edit_booking['parsed_form'][$type . $this->current_booking_type ]['value'];
                                } elseif ( ($type == 'country') || ($type == 'country*') ) {
                                    $options[0] = $this->current_edit_booking['parsed_form'][$type . $this->current_booking_type ]['value'];
                                } else {
                                    $values[0] = '';
                                    if (isset($this->current_edit_booking['parsed_form'][$name]))
                                            if (isset($this->current_edit_booking['parsed_form'][$name]['value']))
                                                $values[0] = $this->current_edit_booking['parsed_form'][$name]['value'];
                                }
                          }

                    }
//debuge($values,$options);
                    if (isset($this->processing_unit_tag)) {
                        if ($this->processing_unit_tag == $_POST['wpdev_unit_tag']) {
                                $validation_error = $_POST['wpdev_validation_errors']['messages'][$name];
                                $validation_error = $validation_error ? '<span class="wpdev-not-valid-tip-no-ajax">' . $validation_error . '</span>' : '';
                        } else {
                                $validation_error = '';
                        }
                    } else  $validation_error = '';

                    $atts = '';
            $options = (array) $options;
//debuge($type, $options);
            $id_array = preg_grep('%^id:[-0-9a-zA-Z_]+$%', $options);
            if ($id = array_shift($id_array)) {
                preg_match('%^id:([-0-9a-zA-Z_]+)$%', $id, $id_matches);
                if ($id = $id_matches[1])
                    $atts .= ' id="' . $id . $this->current_booking_type .'"';
            }

            $placeholder_array = preg_grep('%^placeholder:[-0-9a-zA-Z_]+$%', $options);
            if ($placeholder = array_shift($placeholder_array)) {
                preg_match('%^placeholder:([-0-9a-zA-Z_]+)$%', $placeholder, $placeholder_matches);
                if ($placeholder = $placeholder_matches[1])
                    $atts .= ' placeholder="' . str_replace('_',' ',$placeholder)  .'"';
            }
//debuge($atts, $placeholder_array, $placeholder, $placeholder_matches);
            $class_att = "";
            $class_array = preg_grep('%^class:[-0-9a-zA-Z_]+$%', $options);
            foreach ($class_array as $class) {
                preg_match('%^class:([-0-9a-zA-Z_]+)$%', $class, $class_matches);
                if ($class = $class_matches[1])
                    $class_att .= ' ' . $class;
            }

            if (preg_match('/^email[*]?$/', $type))
                $class_att .= ' wpdev-validates-as-email';

            if (preg_match('/^coupon[*]?$/', $type))
                $class_att .= ' wpdev-validates-as-coupon';

            if (preg_match('/^time[*]?$/', $type))
                $class_att .= ' wpdev-validates-as-time';
            if (preg_match('/^starttime[*]?$/', $type))
                $class_att .= ' wpdev-validates-as-time';
            if (preg_match('/^endtime[*]?$/', $type))
                $class_att .= ' wpdev-validates-as-time';
            if (preg_match('/[*]$/', $type))
                $class_att .= ' wpdev-validates-as-required';

            if (preg_match('/^checkbox[*]?$/', $type))
                $class_att .= ' wpdev-checkbox';

            if (preg_match('/^radio[*]?$/', $type))
                $class_att .= ' wpdev-radio';

            if (preg_match('/^captchac$/', $type))
                $class_att .= ' wpdev-captcha-' . $name;

            if ('acceptance' == $type) {
                $class_att .= ' wpdev-acceptance';
                if (preg_grep('%^invert$%', $options))
                    $class_att .= ' wpdev-invert';
            }

            if ($class_att)
                $atts .= ' class="' . trim($class_att) . '"';

                    // Value.
                    if (   (isset($this->processing_unit_tag)) && ($this->processing_unit_tag == $_POST['wpdev_unit_tag']) ) {
                            if (isset($_POST['wpdev_mail_sent']) && $_POST['wpdev_mail_sent']['ok'])
                                    $value = '';
                            elseif ('captchar' == $type)
                                    $value = '';
                            else
                                    $value = $_POST[$name];
                    } else {
                        if (isset($values[0])) $value = $values[0];
                        else $value = '';
                    }

            // Default selected/checked for select/checkbox/radio
            if (preg_match('/^(?:select|checkbox|radio)[*]?$/', $type)) {
//debuge('$options',$options);
                $scr_defaults = array_values(preg_grep('/^default:/', $options));

                $scr_default = array();                                     // Firstly set  the selected options as Empty

                foreach ($scr_defaults as $scr_defaults_value) {            // Search  for selected options

                    preg_match('/^default:([^~]+)$/', $scr_defaults_value, $scr_default_matches);
//debuge($scr_default_matches[1]);                        
                    if (isset($scr_default_matches[1])) {

                         $scr_default_option = explode('_', $scr_default_matches[1]);                        
//debuge($scr_default_option);                             
                         $scr_default_option = str_replace( '&#37;','%', $scr_default_option[0] );
                         $scr_default[] = $scr_default_option;              // Add Selected Option
                    }
                }
            }
//debuge($scr_default);

            if (preg_match('/^(?:country)[*]?$/', $type)) {
                
                $scr_defaults = array_values(preg_grep('/^default:/', $options));

                if ( ( isset($scr_defaults) ) && ( count($scr_defaults) > 0 ) && ( isset($scr_defaults[0]) )  )
                    preg_match('/^default:([0-9a-zA-Z_:\s-]+)$/', $scr_defaults[0], $scr_default_matches);
                
                if ( ( isset($scr_default_matches) ) && ( count($scr_default_matches) > 1 ) && ( isset($scr_default_matches[1]) ) ) 
                    $scr_default = explode('_', $scr_default_matches[1]);
                else 
                    $scr_default = '';
            }


                    if ( ($type == 'starttime') || ($type == 'starttime*') )     $name = 'starttime' . $this->current_booking_type ;
                    if ( ($type == 'endtime') || ($type == 'endtime*') )         $name = 'endtime' . $this->current_booking_type ;

                    switch ($type) {
                            case 'starttime':  
                            case 'starttime*':
                            case 'endtime':
                            case 'endtime*':  
                            case 'time':
                            case 'time*':
                            case 'text':
                            case 'text*':
                            case 'email':
                            case 'email*':
                            case 'coupon':
                            case 'coupon*':
                            case 'captchar':
                                    if (is_array($options)) {
                                            $size_maxlength_array = preg_grep('%^[0-9]*[/x][0-9]*$%', $options);
                                            if ($size_maxlength = array_shift($size_maxlength_array)) {
                                                    preg_match('%^([0-9]*)[/x]([0-9]*)$%', $size_maxlength, $sm_matches);
                                                    if ($size = (int) $sm_matches[1])
                                                            $atts .= ' size="' . $size . '"';
                            else
                                $atts .= ' size="40"';
                                                    if ($maxlength = (int) $sm_matches[2])
                                                            $atts .= ' maxlength="' . $maxlength . '"';
                                            } else {
                            $atts .= ' size="40"';
                        }
                                    }

                                    if ( ($type=='coupon') || ($type=='coupon*'))
                                        $additional_js = ' onchange="javascript:if(typeof( showCostHintInsideBkForm ) == \'function\') {  showCostHintInsideBkForm('.$this->current_booking_type.');}" ';
                                    else
                                        $additional_js = '';

                                    $html = '<input type="text" name="' . $name . '" value="' . esc_attr($value) . '"' . $atts . $additional_js . ' />';
                                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
                                    return $html;
                                    break;
                            case 'textarea':
                            case 'textarea*':
                                    if (is_array($options)) {
                                            $cols_rows_array = preg_grep('%^[0-9]*[x/][0-9]*$%', $options);
                                            if ($cols_rows = array_shift($cols_rows_array)) {
                                                    preg_match('%^([0-9]*)[x/]([0-9]*)$%', $cols_rows, $cr_matches);
                                                    if ($cols = (int) $cr_matches[1])
                                                            $atts .= ' cols="' . $cols . '"';
                            else
                                $atts .= ' cols="40"';
                                                    if ($rows = (int) $cr_matches[2])
                                                            $atts .= ' rows="' . $rows . '"';
                            else
                                $atts .= ' rows="10"';
                                            } else {
                            $atts .= ' cols="40" rows="10"';
                        }
                                    }
                                    $html = '<textarea name="' . $name . '"' . $atts . '>' . $value . '</textarea>';
                                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
                                    return $html;
                                    break;
                            case 'country':
                            case 'country*':

                                    $html = '';
                                    //debuge($values, $empty_select);
                                    foreach ($this->countries_list as $key => $value_country) {
                                        $selected = '';
//debuge($key , $value, $scr_default, in_array($key , (array) $scr_default) );
                                        if ( in_array($key , (array) $scr_default)) $selected = ' selected="selected"';
                                        if ($value == $key ) { $selected = ' selected="selected"'; }
                                        //if ($this->processing_unit_tag == $_POST['wpdev_unit_tag'] && ( $multiple && in_array($value, (array) $_POST[$name]) || ! $multiple && $_POST[$name] == $value)) $selected = ' selected="selected"';
                                        $html .= '<option value="' . esc_attr($key) . '"' . $selected . '>' . $value_country . '</option>';
                                    }
                                    $html = '<select name="' . $name   . '"' . $atts . '>' . $html . '</select>';
                                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
                                    return $html;
                                    break;

                            case 'select':
                            case 'select*':
//debuge($options);                                    
                    $multiple = (preg_grep('%^multiple$%', $options)) ? true : false;
                    $include_blank = preg_grep('%^include_blank$%', $options);

                                    if ($empty_select = empty($values) || $include_blank)
                                            array_unshift($values, '---');

                                    $html = '';

                    if (preg_match('/^select[*]?$/', $type) &&  $multiple && ($name == 'rangetime' . $this->current_booking_type ) ) 
                            $onclick = ' wpdevExclusiveSelectbox(this); ';
                    else    $onclick = '';

//debuge($values, $empty_select);
                    foreach ($values as $key => $value) {

                        $selected = '';

                        $my_title = false;
                        if (strpos($value, '@@') !==false ) {
                            $my_title_value = explode('@@',$value);
                            $my_title = $my_title_value[0];
                            $value = $my_title_value[1];
                        }                                                        
//debuge( $value, $scr_default );
                        if ( in_array($value , (array) $scr_default))
                            $selected = ' selected="selected"';
                        if ( (isset($this->processing_unit_tag)) && ($this->processing_unit_tag == $_POST['wpdev_unit_tag']) && (
                                $multiple && in_array($value, (array) $_POST[$name]) ||
                                ! $multiple && $_POST[$name] == $value))
                            $selected = ' selected="selected"';

                       // debuge($name, $atts);
                        if ( ($name == 'rangetime' . $this->current_booking_type ) && (strpos($atts,'hideendtime')!== false ) )
                            $html .= '<option value="' . esc_attr($value) . '"' . $selected . '>' . ( (empty($my_title))?( substr($value,0, strpos($value,'-')) ):($my_title) ) . '</option>';
                        elseif  ($name == 'rangetime' . $this->current_booking_type ) {

                            $time_format = get_bk_option( 'booking_time_format');

                            $value_times = explode('-', $value);
                            $value_times[0] = trim($value_times[0]);
                            $value_times[1] = trim($value_times[1]);

                            $s_tm = explode(':', $value_times[0]);
                            $e_tm = explode(':', $value_times[1]);

                            $s_tm_value = $s_tm;
                            $e_tm_value = $e_tm;

                            $s_tm = date_i18n($time_format, mktime($s_tm[0], $s_tm[1]));
                            $e_tm = date_i18n($time_format, mktime($e_tm[0], $e_tm[1]));
                            $t_delimeter = ' - ';
                            if (strpos($atts,'hideendtime')!== false ) {
                               $e_tm = '';
                               $t_delimeter = '';
                            }

                            // Recheck for some errors in time formating of shortcode, like whitespace or empty zero before hours less then 10am
                            $s_tm_value[0] = trim($s_tm_value[0]);
                            $s_tm_value[1] = trim($s_tm_value[1]);
                            if ( ($s_tm_value[0] + 0) < 10 ) $s_tm_value[0] = '0' . ($s_tm_value[0] + 0);
                            if ( ($s_tm_value[1] + 0) < 10 ) $s_tm_value[1] = '0' . ($s_tm_value[1] + 0);
                            $e_tm_value[0] = trim($e_tm_value[0]);
                            $e_tm_value[1] = trim($e_tm_value[1]);
                            if ( ($e_tm_value[0] + 0) < 10 ) $e_tm_value[0] = '0' . ($e_tm_value[0] + 0);
                            if ( ($e_tm_value[1] + 0) < 10 ) $e_tm_value[1] = '0' . ($e_tm_value[1] + 0);

                            $value_time_range =  $s_tm_value[0] . ':' . $s_tm_value[1] . $t_delimeter . $e_tm_value[0] . ':' . $e_tm_value[1];

                            $html .= '<option value="' . esc_attr($value_time_range) . '"' . $selected . '>' . ( (empty($my_title))?($s_tm . $t_delimeter . $e_tm):($my_title) ) . '</option>';

                        } elseif  ($name == 'starttime' . $this->current_booking_type ) {
                            $time_format = get_bk_option( 'booking_time_format');
                            $s_tm = explode(':', $value);                            
                            $s_tm = date_i18n($time_format, mktime($s_tm[0], $s_tm[1]));
                            $html .= '<option value="' . esc_attr($value) . '"' . $selected . '>' . ( (empty($my_title))?($s_tm):($my_title) )   . '</option>';

                        } elseif  ($name == 'endtime' . $this->current_booking_type ) {
                            $time_format = get_bk_option( 'booking_time_format');
                            $s_tm = explode(':', $value);
                            $s_tm = date_i18n($time_format, mktime($s_tm[0], $s_tm[1]));
                            $html .= '<option value="' . esc_attr($value) . '"' . $selected . '>' . ( (empty($my_title))?($s_tm):($my_title) )   . '</option>';

                        } else {
                            if (strpos($value, '@@') !==false ) {
                                $my_title_value = explode('@@',$value);
                                $html .= '<option value="' . esc_attr($my_title_value[1]) . '"' . $selected . '>' . $my_title_value[0] . '</option>';
                            } else
                                $html .= '<option value="' . esc_attr($value) . '"' . $selected . '>' . ( (empty($my_title))?($value):($my_title) )  . '</option>';
                        }
                    }

                    if ($multiple)
                        $atts .= ' multiple="multiple"';

                                    $html = '<select onchange="javascript:'.$onclick.'if(typeof( showCostHintInsideBkForm ) == \'function\') {  showCostHintInsideBkForm('.$this->current_booking_type.');}" '
                                            .'name="' . $name . ($multiple ? '[]' : '') . '"' 
                                            . $atts 
                                            . '>' 
                                            . $html . '</select>';
                                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
//debuge($options, $values, $scr_default, $html);die;
                                    return $html;
                                    break;
                case 'checkbox':
                case 'checkbox*':
                case 'radio':
                case 'radio*':
                    $multiple = (preg_match('/^checkbox[*]?$/', $type) && ! preg_grep('%^exclusive$%', $options)) ? true : false;
                    $html = '';

                    if (preg_match('/^checkbox[*]?$/', $type) && ! $multiple) 
                            $onclick = ' onclick="wpdevExclusiveCheckbox(this);"';

                    $defaultOn = (bool) preg_grep('%^default:on$%', $options);
                    $defaultOn = $defaultOn ? ' checked="checked"' : '';

                    $input_type = rtrim($type, '*');

                    $id_attr_for_group    = '';   

                    foreach ($values as $key => $value) {
                        $checked = '';

                        // Check  if the Lables Titles different from the Values inside of the checkboxes.
                        $label_different = false;
                        if (strpos($value, '@@') !== false ) {
                            $my_title_value = explode('@@',$value);
                            $label_different    = $my_title_value[0];
                            $value              = $my_title_value[1];
                        } else $label_different = $value;
                        ////////////////////////////////////////////////////////////////////////////////////

                        // Get default selected options ////////////////////
                        $multi_values = array();
                        foreach ($options as $op_value) {
                            $multi_values[] = str_replace('default:', '', $op_value);
                        }                      
                        $multi_values = implode(',',$multi_values);
                        $multi_values_array = explode(',',$multi_values);
                        foreach ($multi_values_array as $mv) {
                            if ( ( trim($mv) == trim($value) ) && ($value !=='') ) 
                                $checked = ' checked="checked"';
                        }
                        ////////////////////////////////////////////////////

                        if (in_array($key + 1, (array) $scr_default))       //TODO: ??? Need to retest this parameter
                            $checked = ' checked="checked"';

                        if (                                                //TODO: ??? Need to retest this parameter
                                (isset($this->processing_unit_tag)) 
                                && ($this->processing_unit_tag == $_POST['wpdev_unit_tag']) 
                                && ( 
                                     $multiple 
                                     && in_array($value, (array) $_POST[$name] ) 
                                     || ! $multiple 
                                     && $_POST[$name] == $value
                                   )
                           ) 
                            $checked = ' checked="checked"';
                        ////////////////////////////////////////////////////

                        if (! isset($onclick)) $onclick = '';

                        $is_use_label       = ( preg_grep('%^use[_-]?label[_-]?element$%', $options) ) ? 'label' : 'span';                            
                        $is_use_label_first = ( preg_grep('%^label[_-]?first$%', $options) ) ? true : false;
//debuge($options, $is_use_label, $is_use_label_first, 0);


                        $id_attr_for_checkbox = ''; 
                        $label_for_parameter  = '';
                        // If we are using the LABELS instead of the SPAN so  we are need
                        // to remove the ID attribute from the INPUT elements (we can set it to the parent element)                            
                        if ( $is_use_label == 'label' ) {

                            preg_match('%id="([-0-9a-zA-Z_]+)"%', $atts , $id_matches);
                            if ( count($id_matches) > 0 ) {
                                $atts = str_replace($id_matches[0], '', $atts);
                                $id_attr_for_group = ' id="'.$id_matches[1].'" ';                                    
                                $id_attr_for_checkbox = $id_matches[1]. time().$key.rand(10,100);     //Uniq ID
                            } else {
                                $id_attr_for_checkbox = 'checkboxid'. time().$key.rand(10,100);       //Uniq ID
                            }

                            $label_for_parameter  = ' for="'. $id_attr_for_checkbox . '" ';
                            $id_attr_for_checkbox = ' id="' . $id_attr_for_checkbox . '" ';   
                        }

                        $item_label = '<'.$is_use_label . $label_for_parameter. ' class="wpdev-list-item-label">' . $label_different . '</'.$is_use_label.'>';

                        $item = '<input '
                                . $atts
                                . $id_attr_for_checkbox
                                .' onchange="javascript:if(typeof( showCostHintInsideBkForm ) == \'function\') {  showCostHintInsideBkForm('.$this->current_booking_type.');}" '
                                .' type="' . $input_type . '" '
                                .' name="' . $name . ($multiple ? '[]' : '') . '" '
                                .' value="' . esc_attr($value) . '"' 
                                . $checked 
                                . $onclick 
                                . $defaultOn 
                                . ' />';

                        if ( $is_use_label_first ) 
                            $item = $item_label . '&nbsp;' . $item;
                        else 
                            $item = $item . '&nbsp;' . $item_label;

                        $item = '<span class="wpdev-list-item">' . $item . '</span>';
                        $html .= $item;
                    }

                    $html = '<span' . $atts . $id_attr_for_group . '>' . $html . '</span>';
                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
                    return $html;
                    break;

                case 'quiz':
                    if (count($raw_values) == 0 && count($values) == 0) { // default quiz
                        $raw_values[] = '1+1=?|2';
                        $values[] = '1+1=?';
                    }

                    $pipes = $this->get_pipes($raw_values);

                    if (count($values) == 0) {
                        break;
                    } elseif (count($values) == 1) {
                        $value = $values[0];
                    } else {
                        $value = $values[array_rand($values)];
                    }

                    $answer = $this->pipe($pipes, $value);
                    $answer = $this->canonicalize($answer);

                                    if (is_array($options)) {
                                            $size_maxlength_array = preg_grep('%^[0-9]*[/x][0-9]*$%', $options);
                                            if ($size_maxlength = array_shift($size_maxlength_array)) {
                                                    preg_match('%^([0-9]*)[/x]([0-9]*)$%', $size_maxlength, $sm_matches);
                                                    if ($size = (int) $sm_matches[1])
                                                            $atts .= ' size="' . $size . '"';
                            else
                                $atts .= ' size="40"';
                                                    if ($maxlength = (int) $sm_matches[2])
                                                            $atts .= ' maxlength="' . $maxlength . '"';
                                            } else {
                            $atts .= ' size="40"';
                        }
                                    }

                    $html = '<span class="wpdev-quiz-label">' . $value . '</span>&nbsp;';
                    $html .= '<input type="text" name="' . $name . '"' . $atts . ' />';
                    $html .= '<input type="hidden" name="wpdev_quiz_answer_' . $name . '" value="' . wp_hash($answer, 'wpdev_quiz') . '" />';
                                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
                                    return $html;
                    break;
                case 'acceptance':
                    $invert = (bool) preg_grep('%^invert$%', $options);
                    $default = (bool) preg_grep('%^default:on$%', $options);

                    $onclick = ' onclick="wpdevToggleSubmit(this.form);"';
                    $checked = $default ? ' checked="checked"' : '';
                    $html = '<input type="checkbox" name="' . $name . '" value="1"' . $atts . $onclick . $checked . ' />';
                    return $html;
                    break;
                case 'captchac':
                    if (! class_exists('ReallySimpleCaptcha')) {
                        return '<em>' . 'To use CAPTCHA, you need <a href="http://wordpress.org/extend/plugins/really-simple-captcha/">Really Simple CAPTCHA</a> plugin installed.' . '</em>';
                        break;
                    }

                                    $op = array();
                                    // Default
                                    $op['img_size'] = array(72, 24);
                                    $op['base'] = array(6, 18);
                                    $op['font_size'] = 14;
                                    $op['font_char_width'] = 15;

                                    $op = array_merge($op, $this->captchac_options($options));

                                    if (! $filename = $this->generate_captcha($op)) {
                                            return '';
                                            break;
                                    }
                                    if (is_array($op['img_size']))
                                            $atts .= ' width="' . $op['img_size'][0] . '" height="' . $op['img_size'][1] . '"';
                                    $captcha_url = trailingslashit($this->captcha_tmp_url()) . $filename;
                                    $html = '<img alt="captcha" src="' . $captcha_url . '"' . $atts . ' />';
                                    $ref = substr($filename, 0, strrpos($filename, '.'));
                                    $html = '<input type="hidden" name="wpdev_captcha_challenge_' . $name . '" value="' . $ref . '" />' . $html;
                                    return $html;
                                    break;
                case 'file':
                case 'file*':
                    $html = '<input type="file" name="' . $name . '"' . $atts . ' value="1" />';
                    $html = '<span class="wpdev-form-control-wrap ' . $name . '">' . $html . $validation_error . '</span>';
                    return $html;
                    break;
                    }
            }

            function submit_replace_callback($matches) {
            $atts = '';
            $options = array();
            if (isset($matches[2]))            
                $options = preg_split('/[\s]+/', trim($matches[1]));

            $id_array = preg_grep('%^id:[-0-9a-zA-Z_]+$%', $options);
            if ($id = array_shift($id_array)) {
                preg_match('%^id:([-0-9a-zA-Z_]+)$%', $id, $id_matches);
                if ($id = $id_matches[1])
                    $atts .= ' id="' . $id . '"';
            }

            $class_att = '';
            $class_array = preg_grep('%^class:[-0-9a-zA-Z_]+$%', $options);
            foreach ($class_array as $class) {
                preg_match('%^class:([-0-9a-zA-Z_]+)$%', $class, $class_matches);
                if ($class = $class_matches[1])
                    $class_att .= ' ' . $class;
            }

            $html = '';
            if ($class_att)
                $atts .= ' class="' . trim($class_att) . '"';
                    if (isset($matches[2]))
                        if ($matches[2])   $value = $this->strip_quote($matches[2]);
                    if (empty($value)) $value = __('Send' ,'booking');
                    $ajax_loader_image_url =   WPDEV_BK_PLUGIN_URL . '/img/ajax-loader.gif';

                    if (isset($_GET['booking_hash'])) {
                        $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
                        if ($my_booking_id_type !== false) {
                            $my_edited_bk_id = $my_booking_id_type[0];  //$bk_type        = $my_booking_id_type[1];

                            $admin_uri = ltrim( str_replace( get_site_url( null, '', 'admin' ), '', admin_url('admin.php?') ), '/' ) ;    
                            if (  ( strpos($_SERVER['REQUEST_URI'], $admin_uri ) !== false ) && ( isset( $_SERVER['HTTP_REFERER'] ) )  )
                                $html .= '<input type="hidden" name="wpdev_http_referer" id="wpdev_http_referer" value="' . $_SERVER['HTTP_REFERER'] . '" />' ;

                            $value = __('Change your Booking' ,'booking');
                            if (isset($_GET['booking_cancel'])) {
                                $value = __('Cancel Booking' ,'booking');

                                $wpbc_nonce  = wp_nonce_field('DELETE_BY_VISITOR',  ("wpbc_nonce_delete" . $this->current_booking_type) ,  true , false );
                                $html .= $wpbc_nonce . '<input type="button" value="' . $value . '"' . $atts . ' onclick="bookingCancelByVisitor(\''.$_GET['booking_hash'].'\','.$this->current_booking_type.', \''.getBookingLocale().'\' );" />';                                                  
                                $html .= '<img class="ajax-loader" style="visibility: hidden;" alt="ajax loader" src="' . $ajax_loader_image_url . '" />';

                                return $html;
                            }
                        }
                    }


                    $html .= '<input type="button" value="' . $value . '"' . $atts . ' onclick="mybooking_submit(this.form,'.$this->current_booking_type.', \''.getBookingLocale().'\' );" />';
                    $html .= '<img class="ajax-loader" style="visibility: hidden;" alt="ajax loader" src="' . $ajax_loader_image_url . '" />';

                    return $html;
            }

            function form_element_parse($element) {
                    $type = trim($element[1]);
                    $name = trim($element[2]);
                    $options = preg_split('/[\s]+/', trim($element[3]));

                    preg_match_all('/"[^"]*"|\'[^\']*\'/', $element[4], $matches);
                    $raw_values = $this->strip_quote_deep($matches[0]);

                    if ( preg_match('/^(select[*]?|checkbox[*]?|radio[*]?)$/', $type) || 'quiz' == $type) {
                        $pipes = $this->get_pipes($raw_values);
                        $values = $this->get_pipe_ins($pipes);
                    } else {
                        $values =& $raw_values;
                    }

                    return compact('type', 'name', 'options', 'values', 'raw_values');
            }

            function strip_quote($text) {
                    $text = trim($text);
                    if (preg_match('/^"(.*)"$/', $text, $matches))
                            $text = $matches[1];
                    elseif (preg_match("/^'(.*)'$/", $text, $matches))
                            $text = $matches[1];
                    return $text;
            }

            function strip_quote_deep($arr) {
                    if (is_string($arr))
                            return $this->strip_quote($arr);
                    if (is_array($arr)) {
                            $result = array();
                            foreach ($arr as $key => $text) {
                                    $result[$key] = $this->strip_quote($text);
                            }
                            return $result;
                    }
            }

            function pipe($pipes, $value) {
                if (is_array($value)) {
                    $results = array();
                    foreach ($value as $k => $v) {
                        $results[$k] = $this->pipe($pipes, $v);
                    }
                    return $results;
                }

                foreach ($pipes as $p) {
                    if ($p[0] == $value)
                        return $p[1];
                }

                return $value;
            }

            function get_pipe_ins($pipes) {
                $ins = array();
                foreach ($pipes as $pipe) {
                    $in = $pipe[0];
                    if (! in_array($in, $ins))
                        $ins[] = $in;
                }
                return $ins;
            }

            function get_pipes($values) {
                $pipes = array();

                foreach ($values as $value) {
                    $pipe_pos = strpos($value, '|');
                    if (false === $pipe_pos) {
                        $before = $after = $value;
                    } else {
                        $before = substr($value, 0, $pipe_pos);
                        $after = substr($value, $pipe_pos + 1);
                    }

                    $pipes[] = array($before, $after);
                }

                return $pipes;
            }

            function pipe_all_posted($contact_form) {
                $all_pipes = array();

                $fes = $this->form_elements($contact_form['form'], false);
                foreach ($fes as $fe) {
                    $type = $fe['type'];
                    $name = $fe['name'];
                    $raw_values = $fe['raw_values'];

                    if (! preg_match('/^(select[*]?|checkbox[*]?|radio[*])$/', $type))
                        continue;

                    $pipes = $this->get_pipes($raw_values);

                    $all_pipes[$name] = array_merge($pipes, (array) $all_pipes[$name]);
                }

                foreach ($all_pipes as $name => $pipes) {
                    if (isset($this->posted_data[$name]))
                        $this->posted_data[$name] = $this->pipe($pipes, $this->posted_data[$name]);
                }
            }
            ////////////////////////////////////////////////////////////////////////



//  A d m i n   P a n e l  ->  B o o k i n g    p a g e           //////////////////////////////////////////////////////////////////////////////////////////////////
    function wpdev_updating_bk_resource_of_booking(){
        
                $booking_id  = intval( $_POST["booking_id"] );
                $resource_id = intval( $_POST["resource_id"] );
                global $wpdb;

               // 0.Get dates of specific booking
                $sql = $wpdb->prepare( "SELECT *
                        FROM  {$wpdb->prefix}booking as bk
                        WHERE booking_id = %d ", $booking_id );
                $res = $wpdb->get_row( $sql  );
                $formdata = $res->form;
                $bktype   = $res->booking_type;

                // 1.Get dates of specific booking
                $sql = $wpdb->prepare( "SELECT *
                        FROM  {$wpdb->prefix}bookingdates as dt
                        WHERE booking_id = %d
                        ORDER BY booking_date ASC ", $booking_id );
                $selected_dates_array = $wpdb->get_results( $sql );

                // Get dates in good format for SQL checking
                $dates_string = '';
                foreach ($selected_dates_array as $k=>$v) {
                    $dates_string .= " DATE('" . $v->booking_date . "'), ";
                }
                $dates_string = substr($dates_string,0,-2);


                //2. Get bookings of selected booking resource - checking if some dates there is booked or not
                $sql = $wpdb->prepare( "SELECT *
                            FROM {$wpdb->prefix}booking as bk
                            INNER JOIN {$wpdb->prefix}bookingdates as dt
                            ON    bk.booking_id = dt.booking_id
                            WHERE     bk.booking_type = %d ",  $resource_id ) ;
                $sql .=       " AND DATE(dt.booking_date) IN ( $dates_string )";
//              $sql .= apply_bk_filter('get_sql_4_dates_from_other_types', ''  , $resource_id, '0,1' ); // Select bk ID from other TYPES, if they partly exist inside of DATES
                //FixIn: 6.0.1.16
                if ( class_exists('wpdev_bk_biz_l')) {
                    $sql .= " OR  bk.booking_id IN ( SELECT DISTINCT booking_id FROM {$wpdb->prefix}bookingdates as dtt WHERE  dtt.approved IN ( 0,1 ) AND dtt.type_id = {$resource_id} "
                                                        . " AND DATE(dt.booking_date) IN ( $dates_string )"
                                                    .") ";
                }                
                $sql .= "   ORDER BY bk.booking_id DESC, dt.booking_date ASC ";

                $exist_dates_results = $wpdb->get_results( $sql );
//debuge($sql, $exist_dates_results);                
                //FixIn: 5.4.5 /////////////////////////////////////////////////              
                $is_date_time_booked = wpbc_check_dates_intersections( $selected_dates_array, $exist_dates_results );
                
                if ( ! $is_date_time_booked ) { // Possible to change
                ////////////////////////////////////////////////////////////////
                
                    // Chnage the booking form:

                    // Fix the booking form ID of elements /////////////////////////////////////////////////////////////////
                    $updated_type_id = $resource_id;
                    $formdata_new = '';
                    $formdata_array = explode('~',$formdata);
                    $formdata_array_count = count($formdata_array);
                    for ( $i=0 ; $i < $formdata_array_count ; $i++) {
                        $elemnts = explode('^',$formdata_array[$i]);

                        $type = $elemnts[0];
                        $element_name = $elemnts[1];
                        $value = $elemnts[2];

                        $element_sufix = '';
                        if (substr($element_name, -2  )=='[]') {
                            //$element_sufix = '[]';
                            //$element_name = substr($element_name, 0,  (strlen($element_name) - 1) ) ;
                            $element_name = str_replace('[]', '', $element_name);
                        }

                        $element_name = substr($element_name, 0, -1 * strlen($bktype) ) . $updated_type_id    ;  // Change bk RES. ID in elemnts of FORM

                        if ($formdata_new!='') $formdata_new.= '~';
                        $formdata_new .= $type . '^' . $element_name . '^' . $value;
                    } ////////////////////////////////////////////////////////////////////////////////////////////////

                    // Update
                    $update_sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.form=%s, bk.booking_type=%d WHERE bk.booking_id=%d;"
                            ,$formdata_new, $updated_type_id, $booking_id );
                    if ( false === $wpdb->query( $update_sql ) ) {
                         ?> <script type="text/javascript">
                            jQuery('#ajax_message').removeClass('info_message');
                            jQuery('#ajax_message').addClass('error_message');
                            document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php  bk_error('Error during updating booking reource type in BD',__FILE__,__LINE__ ); ?></div>';
                            jQuery('#ajax_message').fadeOut(10000);
                        </script> <?php
                        die();
                   }


                    if ( class_exists('wpdev_bk_biz_l')) {
                        $update_sql =  $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingdates SET type_id=NULL WHERE booking_id=%d ", $booking_id  );
                        if ( false === $wpdb->query( $update_sql ) ) {
                            ?> <script type="text/javascript">
                                jQuery('#ajax_message').removeClass('info_message');
                                jQuery('#ajax_message').addClass('error_message');
                                document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php  bk_error('Error during updating dates type in BD',__FILE__,__LINE__ ); ?></div>';
                                jQuery('#ajax_message').fadeOut(10000);
                            </script> <?php
                            die();
                        }
                    }
                    
                    if ( isset( $_POST["is_send_emeils"] ) ) $is_send_emeils = intval( $_POST["is_send_emeils"] );
                    else                                     $is_send_emeils = 1; 

                    // Send modification email about this
                    if ( $is_send_emeils )                                      //FixIn: 6.1.0.2
                        sendModificationEmails($booking_id, $resource_id, $formdata_new);

                    ?> <script type="text/javascript">
                        document.getElementById('ajax_message').innerHTML = '<?php echo __('Updated successfully' ,'booking'); ?>';
                        jQuery('#ajax_message').fadeOut(5000);
                        set_booking_row_resource_name('<?php echo $booking_id; ?>', '<?php
                                                        $bk_booking_type_name = get_booking_title($resource_id);
                                                        if (strlen($bk_booking_type_name)>19) $bk_booking_type_name = substr($bk_booking_type_name, 0, 16) . '...';
                                                        echo $bk_booking_type_name;
                                                      ?>');
                    </script> <?php

                } else {            // Already busy there, need to chnage to other resource

                    ?> <script type="text/javascript">
                        jQuery('#ajax_message').removeClass('info_message');
                        jQuery('#ajax_message').addClass('error_message');
                        document.getElementById('ajax_message').innerHTML = '<?php echo __('Warning! The resource was not changed. Current dates are already booked there.' ,'booking'); ?>';
                        jQuery('#ajax_message').fadeOut(10000999);
                    </script> <?php

                }
                die;
            }


    //FixIn: 5.4.5.1
    function wpbc_duplicate_booking_to_other_resource() {
        $booking_id  = intval( $_POST["booking_id"] );
        $resource_id = intval( $_POST["resource_id"] );
        $wpdev_active_locale = $_POST["wpdev_active_locale"];
        
        global $wpdb;

       // 0.Get dates of specific booking
        $sql = $wpdb->prepare( "SELECT *
                FROM  {$wpdb->prefix}booking as bk
                WHERE booking_id = %d ", $booking_id );
        $res = $wpdb->get_row( $sql  );
        $formdata = $res->form;
        $bktype   = $res->booking_type;

        // 1.Get dates of specific booking
        $sql = $wpdb->prepare( "SELECT *
                FROM  {$wpdb->prefix}bookingdates as dt
                WHERE booking_id = %d
                ORDER BY booking_date ASC ", $booking_id );
        $selected_dates_array = $wpdb->get_results( $sql );

        // Get dates in good format for SQL checking
        $dates_string = '';
        $simple_dates_array_of_exist_booking = array();
        foreach ($selected_dates_array as $k=>$v) {
            $dates_string .= " DATE('" . $v->booking_date . "'), ";
            
            $simple_dates_array_of_exist_booking[ strtotime( substr($v->booking_date, 0, 10) ) ] = substr($v->booking_date, 0, 10);
        }
        $dates_string = substr($dates_string,0,-2);


        //2. Get bookings of selected booking resource - checking if some dates there is booked or not
        $sql = $wpdb->prepare( "SELECT *
                    FROM {$wpdb->prefix}booking as bk
                    INNER JOIN {$wpdb->prefix}bookingdates as dt
                    ON    bk.booking_id = dt.booking_id
                    WHERE     bk.booking_type = %d ",  $resource_id ) ;
        $sql .=       " AND DATE(dt.booking_date) IN ( $dates_string )";
//      $sql .= apply_bk_filter('get_sql_4_dates_from_other_types', ''  , $resource_id, '0,1' ); // Select bk ID from other TYPES, if they partly exist inside of DATES
        //FixIn: 6.0.1.16
        if ( class_exists('wpdev_bk_biz_l')) {
            $sql .= " OR  bk.booking_id IN ( SELECT DISTINCT booking_id FROM {$wpdb->prefix}bookingdates as dtt WHERE  dtt.approved IN ( 0,1 ) AND dtt.type_id = {$resource_id} "
                                                . " AND DATE(dt.booking_date) IN ( $dates_string )"
                                            .") ";
        }                
        
        $sql .= "   ORDER BY bk.booking_id DESC, dt.booking_date ASC ";

        $exist_dates_results = $wpdb->get_results( $sql );

        //FixIn: 5.4.5 /////////////////////////////////////////////////              
        $is_date_time_booked = wpbc_check_dates_intersections( $selected_dates_array, $exist_dates_results );

        if ( ! $is_date_time_booked ) { // Possible to change
        
// debuge('Duplicate booking ' . $booking_id . ' to booking resource ' . $resource_id );
            
            
            // Fix the booking form ID of elements /////////////////////////////////////////////////////////////////
            $updated_type_id = $resource_id;
            $formdata_new = '';
            $formdata_array = explode('~',$formdata);
            $formdata_array_count = count($formdata_array);
            for ( $i=0 ; $i < $formdata_array_count ; $i++) {
                $elemnts = explode('^',$formdata_array[$i]);

                $type = $elemnts[0];
                $element_name = $elemnts[1];
                $value = $elemnts[2];

                $element_sufix = '';
                if (substr($element_name, -2  )=='[]') {
                    //$element_sufix = '[]';
                    //$element_name = substr($element_name, 0,  (strlen($element_name) - 1) ) ;
                    $element_name = str_replace('[]', '', $element_name);
                }

                $element_name = substr($element_name, 0, -1 * strlen($bktype) ) . $updated_type_id    ;  // Change bk RES. ID in elemnts of FORM

                if ($formdata_new!='') $formdata_new.= '~';
                
                $formdata_new .= $type . '^' . $element_name . '^' . $value;
            } ////////////////////////////////////////////////////////////////////////////////////////////////

            // Dates ///////////////////////////////////////////////////////////
            sort( $simple_dates_array_of_exist_booking );
            
            // Chnage dates from '2015-10-17' to '17.10.2015'
            $my_dates_for_sql = array();
            foreach ($simple_dates_array_of_exist_booking as $selected_date) {
                $selected_date = explode( '-', $selected_date );
                $my_dates_for_sql[]  = sprintf( "%02d.%02d.%04d", $selected_date[2], $selected_date[1], $selected_date[0] );
            }
            $my_dates_for_sql = implode( ', ', $my_dates_for_sql ); 
            ////////////////////////////////////////////////////////////////////
            
                        
            /*
            $params = array(
                ["bktype"] => 4
                ["dates"] => 24.09.2014, 25.09.2014, 26.09.2014
                ["form"] => select-one^rangetime4^14:00 - 16:00~text^name4^Costa~text^secondname4^Rika~email^email4^rika@cost.com~text^phone4^2423432~text^address4^Ferrari~text^city4^Rome~text^postcode4^2343~select-one^country4^IT~select-one^visitors4^1~select-one^children4^0~textarea^details4^dhfjksdhfkdhjs~checkbox^term_and_condition4[]^I Accept term and conditions
                ["is_send_emeils"] => 1
                ["booking_form_type"] => 
                      [wpdev_active_locale] => en_US
            
                      // Paramters for adding booking in the HTML:
                      ["skip_page_checking_for_updating"] = 0;
                      ["is_show_payment_form"] = 1;
              ); */ 
            // Params for creation  new booking
            $params = array(
                    'bktype'  => $updated_type_id
                    , 'dates' => $my_dates_for_sql                              // '27.08.2014, 28.08.2014, 29.08.2014'
                    , 'form'  => $formdata_new
                    , 'is_send_emeils' => 0
                    , 'booking_form_type' => ''
                    , 'wpdev_active_locale' => $wpdev_active_locale
            ); 
            
// debuge($params, $selected_dates_array, $formdata_new, $simple_dates_array_of_exist_booking , $my_dates_for_sql);            
            $booking_id = apply_bk_filter('wpbc_add_new_booking_filter' , $params ); 

            ?> <script type="text/javascript">
                document.getElementById('ajax_message').innerHTML = '<?php echo __('The booking has been duplicated successfully' ,'booking'); ?>';
                jQuery('#ajax_message').animate({opacity:1},1000).fadeOut(500);
                setTimeout(function ( ) {location.reload(true);} ,1500);
            </script> <?php
            
        ////////////////////////////////////////////////////////////////
        }  else {            // Already busy there, need to chnage to other resource

            ?> <script type="text/javascript">
                jQuery('#ajax_message').removeClass('info_message');
                jQuery('#ajax_message').addClass('error_message');
                document.getElementById('ajax_message').innerHTML = '<?php echo __('Warning! Operation failed. Current dates are already booked there.' ,'booking'); ?>';
                jQuery('#ajax_message').fadeOut(10000999);
            </script> <?php

        }
        die;
    }        
    //     R  E   M   A   R   K   S      /////////////////////////////////////////////////////////////////////////////////////////

        function wpdev_updating_remark(){
            $remark_id   = $_POST["remark_id"];
            $remark_text = $_POST["remark_text"];

            $remark_text = str_replace('%','&#37;',$remark_text);
            $my_remark = str_replace('"','',$remark_text);
            $my_remark = str_replace("'",'',$my_remark);
            $my_remark =trim($my_remark);

            global $wpdb;

            $update_sql =  $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.remark= %s WHERE bk.booking_id= %d ", $remark_text, $remark_id );
            if ( false === $wpdb->query( $update_sql ) ) {
                ?> <script type="text/javascript">
                    jQuery('#ajax_message').removeClass('info_message');
                    jQuery('#ajax_message').addClass('error_message');
                    document.getElementById('ajax_message').innerHTML = '<div style=&quot;height:20px;width:100%;text-align:center;margin:15px auto;&quot;><?php  bk_error('Error during updating remarks in BD',__FILE__,__LINE__ ); ?></div>';
                    jQuery('#ajax_message').fadeOut(10000);
                </script> <?php
                die();
            }

            ?> <script type="text/javascript">
                document.getElementById('ajax_message').innerHTML = '<?php echo __('Updated successfully' ,'booking'); ?>';
                jQuery('#ajax_message').fadeOut(5000);
                <?php  if (strlen($my_remark)>100) {$my_remark = esc_js(substr($my_remark,0,100)) . '...';}   ?>
                set_booking_row_remark_in_hint(<?php echo $remark_id; ?>, '<?php echo $my_remark; ?>') ; 
            </script> <?php
            die();
        }

        function wpdev_make_update_of_remark($remark_id, $remark_text, $is_append = false ){

             $my_remark = str_replace('"','',$remark_text);
             $my_remark = str_replace("'",'',$my_remark);
             $my_remark =trim(strip_tags($my_remark));
             //$my_remark = substr($my_remark,0,75) . '...';

            global $wpdb;

            if ( $is_append ) {
                $my_remark .= ' ' . $wpdb->get_var( $wpdb->prepare( "SELECT remark FROM {$wpdb->prefix}booking  WHERE booking_id = %d " , $remark_id ) );
            }

            $update_sql =  $wpdb->prepare( "UPDATE {$wpdb->prefix}booking AS bk SET bk.remark= %s WHERE bk.booking_id= %d ", $my_remark, $remark_id );
            if ( false === $wpdb->query( $update_sql  ) ) {
                   echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:bold;">';
                   bk_error('Error during updating remark of booking' ,__FILE__,__LINE__);
                   echo   '</div>';

            }

        }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


//  S e t t i n g s     p a g e s           //////////////////////////////////////////////////////////////////////////////////////////////////

    function settings_menu_content(){
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'not_low_level_user'); //Anxo customizarion
        if (! $is_can) return; //Anxo customizarion

        switch ($_GET['tab']) {

           case 'form':
            $this->compouse_form();
            return false;
            break;

           case 'email':
            $this->compouse_email();
            return false;
            break;

         default:
            return true;
            break;
        }
    }


    function compouse_email(){

         if ( isset( $_POST['email_reservation_adress'] ) ) {

             $email_reservation_adress      = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_adress']));
             $email_reservation_from_adress = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_from_adress']));
             $email_reservation_subject     = htmlspecialchars( str_replace('\"','"',$_POST['email_reservation_subject']));
             $email_reservation_content     = str_replace('\"','"',$_POST['email_reservation_content']);

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
             $email_approval_content =  str_replace('\"','"',$_POST['email_approval_content']) ;

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
             $email_deny_content = str_replace('\"','"',$_POST['email_deny_content']);

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

             $email_modification_adress  = htmlspecialchars( str_replace('\"','"',$_POST['email_modification_adress']));
             $email_modification_subject = htmlspecialchars( str_replace('\"','"',$_POST['email_modification_subject']));
             $email_modification_content = str_replace('\"','"',$_POST['email_modification_content']);

             $email_modification_adress      =  str_replace("\'","'",$email_modification_adress);
             $email_modification_subject     =  str_replace("\'","'",$email_modification_subject);
             $email_modification_content     =  str_replace("\'","'",$email_modification_content);


             if (isset( $_POST['is_email_modification_adress'] ))         $is_email_modification_adress = 'On';
             else                                        $is_email_modification_adress = 'Off';
             update_bk_option( 'booking_is_email_modification_adress' , $is_email_modification_adress );

             if (isset( $_POST['is_email_modification_send_copy_to_admin'] ))            $is_email_modification_send_copy_to_admin = 'On';
             else                                               $is_email_modification_send_copy_to_admin = 'Off';
             update_bk_option( 'booking_is_email_modification_send_copy_to_admin' , $is_email_modification_send_copy_to_admin );


             if ( get_bk_option( 'booking_email_modification_adress' ) !== false  )     update_bk_option( 'booking_email_modification_adress' , $email_modification_adress );
             else                                                                    add_bk_option( 'booking_email_modification_adress' , $email_modification_adress );
             if ( get_bk_option( 'booking_email_modification_subject' ) !== false  )    update_bk_option( 'booking_email_modification_subject' , $email_modification_subject );
             else                                                                    add_bk_option( 'booking_email_modification_subject' , $email_modification_subject );
             if ( get_bk_option( 'booking_email_modification_content' ) !== false  )    update_bk_option( 'booking_email_modification_content' , $email_modification_content );
             else                                                                    add_bk_option( 'booking_email_modification_content' , $email_modification_content );

         } 

             $email_reservation_adress      = get_bk_option( 'booking_email_reservation_adress') ;
             $email_reservation_from_adress = get_bk_option( 'booking_email_reservation_from_adress');
             $email_reservation_subject     = get_bk_option( 'booking_email_reservation_subject');
             $email_reservation_content     = wpbc_nl_after_br( get_bk_option( 'booking_email_reservation_content') );
             //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             $email_newbookingbyperson_adress      = get_bk_option( 'booking_email_newbookingbyperson_adress');
             $email_newbookingbyperson_subject     = get_bk_option( 'booking_email_newbookingbyperson_subject');
             $email_newbookingbyperson_content     = wpbc_nl_after_br( get_bk_option( 'booking_email_newbookingbyperson_content') );
             //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             $email_approval_adress      = get_bk_option( 'booking_email_approval_adress');
             $email_approval_subject     = get_bk_option( 'booking_email_approval_subject');
             $email_approval_content     = wpbc_nl_after_br( get_bk_option( 'booking_email_approval_content') );
             //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             $email_deny_adress      = get_bk_option( 'booking_email_deny_adress');
             $email_deny_subject     = get_bk_option( 'booking_email_deny_subject');
             $email_deny_content     = wpbc_nl_after_br( get_bk_option( 'booking_email_deny_content') );
             //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
             $email_modification_adress      = get_bk_option( 'booking_email_modification_adress');
             $email_modification_subject     = get_bk_option( 'booking_email_modification_subject');
             $email_modification_content     = wpbc_nl_after_br( get_bk_option( 'booking_email_modification_content') );

             $is_email_reservation_adress   = get_bk_option( 'booking_is_email_reservation_adress' );
             $is_email_newbookingbyperson_adress      = get_bk_option( 'booking_is_email_newbookingbyperson_adress' );
             $is_email_approval_adress      = get_bk_option( 'booking_is_email_approval_adress' );
             $is_email_approval_send_copy_to_admin = get_bk_option( 'booking_is_email_approval_send_copy_to_admin'  );
             $is_email_deny_adress          = get_bk_option( 'booking_is_email_deny_adress' );
             $is_email_deny_send_copy_to_admin = get_bk_option( 'booking_is_email_deny_send_copy_to_admin'  );
             $is_email_modification_adress          = get_bk_option( 'booking_is_email_modification_adress' );
             $is_email_modification_send_copy_to_admin = get_bk_option( 'booking_is_email_modification_send_copy_to_admin'  );

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
                                                   , 'textarea_rows' => 12
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /* ?> 
                                      <textarea id="email_reservation_content" name="email_reservation_content" style="width:100%;" rows="10"><?php echo ($email_reservation_content); ?></textarea>
                                      <?php /**/ ?>                                                                                                      
                                                  <p class="description"><?php printf(__('Type your %semail message content for checking booking%s in. ' ,'booking'),'<b>','</b>');  ?></p>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td></td>
                                            <td>
                                                  <?php 
                                                    $skip_shortcodes = array('denyreason', 'paymentreason','visitorbookingediturl' ,'visitorbookingcancelurl' , 'visitorbookingpayurl');
                                                    if ($this->wpdev_bk_biz_s == false) $skip_shortcodes[] = 'cost';
                                                    email_help_section($skip_shortcodes, sprintf(__('For example: "You have a new reservation %s on the following date(s): %s Contact information: %s You can approve or edit this booking at: %s Thank you, Reservation service."' ,'booking'),'[resource_title]','[dates]&lt;br/&gt;&lt;br/&gt;','&lt;br/&gt; [content]&lt;br/&gt;&lt;br/&gt;', htmlentities( ' <a href="[visitorbookingediturl]">'.__('here' ,'booking').'</a> ') . '&lt;br/&gt;&lt;br/&gt; ') );
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
                                                    <p class="description"><?php printf(__('Use these %s shortcodes.' ,'booking'),  '<code>[name]</code>, <code>[secondname]</code>');?></p>
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
                                                   , 'textarea_rows' => 14
                                                   //, 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_newbookingbyperson_content" name="email_newbookingbyperson_content" style="width:100%;" rows="10"><?php echo ($email_newbookingbyperson_content); ?></textarea> /**/ ?>
                                                  <p class="description"><?php printf(__('Type your %semail message for visitor after creating a new reservation%s' ,'booking'),'<b>','</b>');?></p>
                                            </td>
                                        </tr>
                                        <tr><td></td><td>                                                      <?php
                                                    $skip_shortcodes = array('moderatelink', 'denyreason', 'paymentreason', 'visitorbookingpayurl');
                                                    if ($this->wpdev_bk_biz_s == false) $skip_shortcodes[] = 'cost';
                                                    email_help_section($skip_shortcodes, sprintf(__('For example: "Your reservation %s on these date(s): %s is processing now! We will send confirmation by email. %s  You can edit the booking at this page: %s Thank you, Reservation service."' ,'booking'),'[bookingtype]', '[dates]','&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;', htmlentities( ' <a href="[visitorbookingediturl]">'.__('here' ,'booking').'</a> ') . '&lt;br/&gt;&lt;br/&gt; ') );
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
                                                   , 'textarea_rows' => 12
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_approval_content" name="email_approval_content" style="width:100%;" rows="10"><?php echo ($email_approval_content); ?></textarea> /**/ ?>
                                                  <p class="description"><?php printf(__('Type your %semail message for the approved booking%s from the website' ,'booking'),'<b>','</b>');?></p>
                                                </td>
                                        </tr>
                                        <tr valign="top">
                                            <td></td><td>
                                                  <?php
                                                    $skip_shortcodes = array('moderatelink', 'denyreason', 'paymentreason', 'visitorbookingpayurl');
                                                    if ($this->wpdev_bk_biz_s == false) $skip_shortcodes[] = 'cost';
                                                    email_help_section($skip_shortcodes, sprintf(__('For example: "Your reservation %s on these date(s): %s has been approved.%s  You can edit this booking on this page: %s . Thank you, Reservation service."' ,'booking'),'[bookingtype]', '[dates]','&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;', htmlentities( ' <a href="[visitorbookingediturl]">'.__('here' ,'booking').'</a> ') . '&lt;br/&gt;&lt;br/&gt; ') );
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
                                                   , 'textarea_rows' => 12
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_deny_content" name="email_deny_content" style="width:100%;" rows="10"><?php echo ($email_deny_content); ?></textarea> /**/ ?>
                                                    <p class="description"><?php printf(__('Type your %semail message for the denied booking%s from the website' ,'booking'),'<b>','</b>');?></p>
                                                </td>
                                        </tr>

                                        <tr valign="top">
                                            <td></td><td>
                                                  <?php
                                                    $skip_shortcodes = array('moderatelink', 'paymentreason', 'visitorbookingpayurl', 'visitorbookingediturl', 'visitorbookingcancelurl');
                                                    if ($this->wpdev_bk_biz_s == false) $skip_shortcodes[] = 'cost';
                                                    email_help_section($skip_shortcodes, sprintf(__('For example: "Your reservation %s on these date(s): %s has been canceled. Please contact us for more information. %s Thank you, Reservation service."' ,'booking'), '[bookingtype]' ,'[dates]' , '&lt;br/&gt;&lt;br/&gt;[denyreason]&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;') );
                                                  ?>

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                            </div> </div> </div>

                        </div>


                        <div id="visibility_container_email_modification" class="visibility_container" style="display:none;">

                            <div class='meta-box'> <div <?php $my_close_open_win_id = 'bk_settings_emails_to_person_after_modification'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');" ><br></div>
                                  <h3 class='hndle'><span><?php _e('Email to "Person" after booking is modified' ,'booking'); ?></span></h3> <div class="inside">


                                <table class="form-table email-table0" >
                                    <tbody>
                                        <tr>    
                                            <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                                            <td>
                                                <fieldset>
                                                    <label for="is_email_modification_adress">
                                                        <input id="is_email_modification_adress"  name="is_email_modification_adress" type="checkbox" 
                                                               <?php if ($is_email_modification_adress == 'On') echo "checked"; ?>  
                                                               value="<?php echo $is_email_modification_adress; ?>"
                                                               onchange="document.getElementById('booking_is_email_modification_adress_dublicated').checked=this.checked;"  
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
                                                    <label for="is_email_modification_send_copy_to_admin">
                                                        <input id="is_email_modification_send_copy_to_admin" name="is_email_modification_send_copy_to_admin" 
                                                               type="checkbox" <?php if ($is_email_modification_send_copy_to_admin == 'On') echo "checked"; ?>  
                                                               value="<?php echo $is_email_modification_send_copy_to_admin; ?>" 
                                                               />
                                                        <?php _e('Check this box to send copy of this email to Admin' ,'booking'); ?>
                                                    </label>
                                                </fieldset>   
                                            </td>
                                        </tr>                                            

                                        <tr valign="top">
                                            <th scope="row"><label for="email_modification_adress" ><?php _e('From' ,'booking'); ?>:</label></th>
                                            <td><input id="email_modification_adress"  name="email_modification_adress" class="regular-text code" type="text" size="45" value="<?php echo $email_modification_adress; ?>" />
                                                <span class="description"><?php printf(__('Type the default %sadmin email%s sending the booking confimation' ,'booking'),'<b>','</b>');?></span>
                                            </td>
                                        </tr>

                                        <tr valign="top">
                                                <th scope="row"><label for="email_modification_subject" ><?php _e('Subject' ,'booking'); ?>:</label></th>
                                                <td><input id="email_modification_subject"  name="email_modification_subject" class="regular-text code" type="text" size="45" value="<?php echo $email_modification_subject; ?>" />
                                                    <span class="description"><?php printf(__('Type your email subject for the %smodified booking%s. ' ,'booking'),'<b>','</b>');?></span>
                                                </td>
                                        </tr>

                                        <tr valign="top">
                                                <th scope="row"><label for="email_modification_content" ><?php _e('Content' ,'booking'); ?>:</label></th>
                                                <td>     <?php /**/
                                            wp_editor( $email_modification_content, 
                                               'email_modification_content',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'email_modification_content'
                                                   , 'textarea_rows' => 12
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*
                                                  <textarea id="email_modification_content" name="email_modification_content" style="width:100%;" rows="10"><?php echo ($email_modification_content); ?></textarea> /**/ ?>
                                                  <p class="description"><?php printf(__('Type your %semail message for the modified booking%s from the website' ,'booking'),'<b>','</b>');?></p>
                                                </td>
                                        </tr>

                                        <tr valign="top">
                                            <td></td>
                                            <td>
                                                  <?php
                                                    $skip_shortcodes = array('moderatelink', 'denyreason', 'paymentreason', 'visitorbookingpayurl');
                                                    if ($this->wpdev_bk_biz_s == false) $skip_shortcodes[] = 'cost';
                                                    email_help_section($skip_shortcodes, sprintf(__('For example: "The reservation %s on these date(s): %s has been modified. %s  You can edit this booking on this page: %s . Thank you, Reservation service."' ,'booking'), '[bookingtype]' ,'[dates]' , '&lt;br/&gt;&lt;br/&gt;[content]&lt;br/&gt;&lt;br/&gt;', htmlentities( ' <a href="[visitorbookingediturl]">'.__('here' ,'booking').'</a> ') . '&lt;br/&gt;&lt;br/&gt; ') );
                                                  ?>


                                            </td>
                                        </tr>
                                    </tbody>
                                </table>


                            </div> </div> </div>

                        </div>

                    <?php make_bk_action('wpdev_booking_emails_settings'); ?>


                    <input class="button-primary button" style="float:right;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="Submit"/>
                    <div class="clear" style="height:10px;"></div>

                    </form>

                </div>
        <?php
    }

    function compouse_form(){ 


         if ( ( isset( $_POST['booking_form'] ) )  && ( isset( $_POST['booking_form_show'] ) ) ){

             if (
                  (
                     ( ( isset($_POST['booking_form_new_name'])  )  && (! empty($_POST['booking_form_new_name']) ) )
                     ||
                     ( ( isset($_GET['booking_form'])  ) && ($_GET['booking_form'] !== 'standard')  )
                  )
                  /* && ($_POST['select_booking_form'] !== 'standard') /**/
                )
             {
                 make_bk_action('update_booking_form_at_settings');
             } else {
                 $booking_form =  ($_POST['booking_form']);
                 $booking_form = str_replace('\"','"',$booking_form);
                 $booking_form = str_replace("\'","'",$booking_form);
                 // $booking_form = sanitize_text_field( $booking_form );
                 //$booking_form = htmlspecialchars_decode($booking_form);
                 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 if ( get_bk_option( 'booking_form' ) !== false  )      update_bk_option(   'booking_form' , $booking_form );
                 else                                                   add_bk_option(      'booking_form' , $booking_form );

                 $booking_form_show =  ($_POST['booking_form_show']);
                 $booking_form_show = str_replace('\"','"',$booking_form_show);
                 $booking_form_show = str_replace("\'","'",$booking_form_show);
                 // $booking_form_show = sanitize_text_field( $booking_form_show );
                 ////////////////////////////////////////////////////////////////////////////////////////////////////////////
                 if ( get_bk_option( 'booking_form_show' ) !== false  )   update_bk_option( 'booking_form_show' , $booking_form_show );
                 else                                                     add_bk_option( 'booking_form_show' , $booking_form_show );
            }
         }


         $booking_form       =  get_bk_option( 'booking_form' );
         $booking_form_show  =  get_bk_option( 'booking_form_show' );

         $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
         if ( (isset($_GET['booking_form'])) && ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) ) {
            $my_booking_form_name = $_GET['booking_form'];
            $booking_form       = apply_bk_filter('wpdev_get_booking_form',         $booking_form,      $my_booking_form_name);
            $booking_form_show  = apply_bk_filter('wpdev_get_booking_form_content', $booking_form_show, $my_booking_form_name);

         }
        //$booking_form = wpbc_nl_after_br( $booking_form );
        //$booking_form_show = wpbc_nl_after_br( $booking_form_show );
        ?>
        <div class="clear" style="height:0px;"></div>
        <div id="ajax_working"></div>
        <div id="poststuff0" class="metabox-holder">
            <form  name="post_settings_form_fields" action="" method="post" id="post_settings_form_fields" >           
              <div id="visibility_container_form_fields" class="visibility_container" style="display:block;">
                <div class='meta-box'>
                    <div <?php $my_close_open_win_id = 'bk_settings_form_fields'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php _e('Form fields' ,'booking'); ?></span></h3><div class="inside">

                                <div class="booking_settings_row"  style="float:left;margin:10px 0px;width:58%;" >
                                    <?php /**/                                    
                                            wp_editor( $booking_form, 
                                               'booking_form',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'booking_form'
                                                   , 'textarea_rows' => 23
                                                   , 'tinymce' => false         // Remove Visual Mode from the Editor        
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*    <textarea 
                                        id="booking_form" name="booking_form" 
                                        class="darker-border" style="width:100%;" rows="33"><?php echo htmlspecialchars($booking_form, ENT_NOQUOTES ); ?></textarea> /**/ ?>
                                </div>

                                <?php                                     
                                if ( class_exists('WPBC_Form_Help') ) {

                                    $default_Form_Help = new WPBC_Form_Help(array(
                                                                                'id'=>'booking_form',
                                                                                'version'=> get_bk_version()
                                                                                )
                                                                           );
                                    $default_Form_Help->show();                                        
                                } ?>

                                <div class="clear" style="height:10px;"></div>


                 </div></div></div>
              </div>


              <div id="visibility_container_form_content_data" class="visibility_container" style="display:block;">
                <div class='meta-box'>
                    <div <?php $my_close_open_win_id = 'bk_settings_form_fields_show'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                        <h3 class='hndle'><span><?php printf(__('Content of booking fields data for email templates (%s-shortcode) and booking listing page' ,'booking'),'[content]'); ?></span></h3><div class="inside">

                                <div class="booking_settings_row"  style="float:left;margin:10px 0px;width:58%;" >
                                    <?php /**/                                    
                                            wp_editor( $booking_form_show, 
                                               'booking_form_show',  
                                               array(
                                                     'wpautop'       => false
                                                   , 'media_buttons' => false
                                                   , 'textarea_name' => 'booking_form_show'
                                                   , 'textarea_rows' => 12
                                                   , 'tinymce' => false         // Remove Visual Mode from the Editor        
                                                   // , 'default_editor' => 'html'
                                                   , 'editor_class'  => 'wpbc-textarea-tinymce'      // Any extra CSS Classes to append to the Editor textarea 
                                                   , 'teeny' => true            // Whether to output the minimal editor configuration used in PressThis 
                                                   , 'drag_drop_upload' => false //Enable Drag & Drop Upload Support (since WordPress 3.9) 
                                                   )
                                             ); /*    <textarea id="booking_form_show" name="booking_form_show" class="darker-border" style="width:100%;" rows="12"><?php echo htmlspecialchars($booking_form_show, ENT_NOQUOTES ); ?></textarea> /**/ ?>
                                </div>
                                <div class="booking_settings_row code_description"  style="float:right;margin:0;width:40%;" >
                                    <div  class="wpbc-help-message">
                                      <span class="description"><strong><?php printf(__('Use these shortcodes for customization: ' ,'booking'));?></strong></span><br/><br/>
                                      <span class="description"><?php printf(__('%s - inserting data from fields of booking form' ,'booking'),'<code>[field_name]</code>');?></span><br/>
                                      <span class="description"><?php printf(__('%s - inserting new line' ,'booking'),'<code>&lt;br/&gt;</code>');?></span><br/>
                                      <span class="description"><?php printf(__('Use any other HTML tags (carefully).' ,'booking'),'<code>','</code>');?></span>
                                      <?php make_bk_action('show_additional_translation_shortcode_help'); ?>
                                    </div>
                                </div>
                                <div class="clear" style="height:10px;"></div>

                 </div></div></div>
              </div>


              <input class="button-primary button" style="float:right;" type="button" value="<?php _e('Save Changes' ,'booking'); ?>" name="Submit"
                     onclick="javascript:
                             if (jQuery('#booking_form_show').val()=='') {
                               jQuery('.visibility_container').css('display','none');
                               jQuery('#visibility_container_form_content_data').css('display','block');
                               jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                               jQuery('.booking-submenu-tab-content').addClass('booking-submenu-tab-selected');
                               alert('<?php echo esc_js(__('Please configure the form for content of booking fields data!' ,'booking') ); ?>');
                               return;
                             };
                             if (jQuery('#booking_form').val()=='') {
                                 jQuery('.visibility_container').css('display','none');
                                 jQuery('#visibility_container_form_fields').css('display','block');
                                 jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                                 jQuery('.booking-submenu-tab-form').addClass('booking-submenu-tab-selected');
                                 alert('<?php echo esc_js(__('Please configure the form fields!' ,'booking') ); ?>');
                                 return;
                             }; document.forms['post_settings_form_fields'].submit();" />
              <div class="clear" style="height:10px;"></div>

            </form>

        </div>
     <?php
    }

            // Get default booking form
            function get_default_form(){

                if ($this->wpdev_bk_biz_s == false)  
                    return '[calendar] \n\
<div class="standard-form"> \n\
 <p>'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p>'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p>'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'.__('Phone' ,'booking').':<br />[text phone] </p> \n\
 <p>'.__('Adults' ,'booking').':  [select visitors class:span1 "1" "2" "3" "4"] '.__('Children' ,'booking').': [select children class:span1 "0" "1" "2" "3"]</p> \n\
 <p>'.__('Details' ,'booking').':<br /> [textarea details] </p> \n\
 <p>[checkbox* term_and_condition use_label_element "'.__('I Accept term and conditions' ,'booking').'"] </p> \n\
 <p>[captcha]</p> \n\
 <p>[submit class:btn "'.__('Send' ,'booking').'"]</p> \n\
</div>';                       
                else       
                    return '[calendar] \n\ 
<div class="times-form"> \n\
 <p>'.__('Select Times' ,'booking').':<br />[select* rangetime multiple "10:00 AM - 12:00 PM@@10:00 - 12:00" "12:00 PM - 02:00 PM@@12:00 - 14:00" "02:00 PM - 04:00 PM@@14:00 - 16:00" "04:00 PM - 06:00 PM@@16:00 - 18:00" "06:00 PM - 08:00 PM@@18:00 - 20:00"]</p>\n\
 <p>'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p>'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p>'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'.__('Phone' ,'booking').':<br />[text phone] </p> \n\
 <p>'.__('Adults' ,'booking').':  [select visitors class:span1 "1" "2" "3" "4"] '.__('Children' ,'booking').': [select children class:span1 "0" "1" "2" "3"]</p> \n\
 <p>'.__('Details' ,'booking').':<br /> [textarea details] </p> \n\
 <p>[checkbox* term_and_condition use_label_element "'.__('I Accept term and conditions' ,'booking').'"] </p> \n\
 <p>[captcha]</p> \n\
 <p>[submit class:btn "'.__('Send' ,'booking').'"]</p> \n\
</div>';                             
            }

            // Reset to Payment form
            function reset_to_default_form($form_type ){
                if ($form_type == 'payment')
                    return '[calendar] \n\
<div class="payment-form"> \n\
 <p>'.__('Select Times' ,'booking').':<br />[select rangetime "10:00 AM - 12:00 PM@@10:00 - 12:00" "12:00 PM - 02:00 PM@@12:00 - 14:00" "02:00 PM - 04:00 PM@@14:00 - 16:00" "04:00 PM - 06:00 PM@@16:00 - 18:00" "06:00 PM - 08:00 PM@@18:00 - 20:00"]</p>\n\
 <p>'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p>'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p>'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'.__('Phone' ,'booking').':<br />[text phone] </p> \n\
 <p>'.__('Address (required)' ,'booking').':<br />  [text* address] </p> \n\  
 <p>'.__('City (required)' ,'booking').':<br />  [text* city] </p> \n\
 <p>'.__('Post code (required)' ,'booking').':<br />  [text* postcode] </p> \n\  
 <p>'.__('Country (required)' ,'booking').':<br />  [country] </p> \n\
 <p>'.__('Adults' ,'booking').':  [select visitors class:span1 "1" "2" "3" "4"] '.__('Children' ,'booking').': [select children class:span1 "0" "1" "2" "3"]</p> \n\
 <p>'.__('Details' ,'booking').':<br /> [textarea details] </p> \n\
 <p>[checkbox* term_and_condition use_label_element "'.__('I Accept term and conditions' ,'booking').'"] </p> \n\
 <p>[captcha]</p> \n\
 <p>[submit class:btn "'.__('Send' ,'booking').'"]</p> \n\
</div>';
             }

            // Get default content form text
            function get_default_form_show(){
                if ($this->wpdev_bk_biz_s == false)
                    return '<div class="standard-content-form"> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Phone' ,'booking').'</strong>:<span class="fieldvalue">[phone]</span><br/> \n\
<strong>'. __('Adults' ,'booking').'</strong>:<span class="fieldvalue"> [visitors]</span><br/> \n\
<strong>'. __('Children' ,'booking').'</strong>:<span class="fieldvalue"> [children]</span><br/> \n\
<strong>'. __('Details' ,'booking').'</strong>:<br /><span class="fieldvalue"> [details]</span> \n\
</div>';
                else
                    return '<div class="times-content-form"> \n\
<strong>'. __('Times' ,'booking').'</strong>:<span class="fieldvalue">[rangetime]</span><br/> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Phone' ,'booking').'</strong>:<span class="fieldvalue">[phone]</span><br/> \n\
<strong>'. __('Adults' ,'booking').'</strong>:<span class="fieldvalue"> [visitors]</span><br/> \n\
<strong>'. __('Children' ,'booking').'</strong>:<span class="fieldvalue"> [children]</span><br/> \n\
<strong>'. __('Details' ,'booking').'</strong>:<br /><span class="fieldvalue"> [details]</span> \n\
</div>';
            }

            // Reset to default payment content show
            function reset_to_default_form_show($form_type ){
                if ($form_type == 'payment')
                   return '<div class="payment-content-form"> \n\
<strong>'. __('Times' ,'booking').'</strong>:<span class="fieldvalue">[rangetime]</span><br/> \n\
<strong>'. __('First Name' ,'booking').'</strong>:<span class="fieldvalue">[name]</span><br/> \n\
<strong>'. __('Last Name' ,'booking').'</strong>:<span class="fieldvalue">[secondname]</span><br/> \n\
<strong>'. __('Email' ,'booking').'</strong>:<span class="fieldvalue">[email]</span><br/> \n\
<strong>'. __('Phone' ,'booking').'</strong>:<span class="fieldvalue">[phone]</span><br/> \n\
<strong>'. __('Address' ,'booking').'</strong>:<span class="fieldvalue">[address]</span><br/> \n\
<strong>'. __('City' ,'booking').'</strong>:<span class="fieldvalue">[city]</span><br/> \n\
<strong>'. __('Post code' ,'booking').'</strong>:<span class="fieldvalue">[postcode]</span><br/> \n\
<strong>'. __('Country' ,'booking').'</strong>:<span class="fieldvalue">[country]</span><br/> \n\
<strong>'. __('Adults' ,'booking').'</strong>:<span class="fieldvalue"> [visitors]</span><br/> \n\
<strong>'. __('Children' ,'booking').'</strong>:<span class="fieldvalue"> [children]</span><br/> \n\
<strong>'. __('Details' ,'booking').'</strong>:<br /><span class="fieldvalue"> [details]</span> \n\
</div>';
            }


//   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ///////////////////////////////////////////////////

    // Activate
    function pro_activate() {

           global $wpdb;

           if ($this->wpdev_bk_biz_s == false) {
                add_bk_option( 'booking_form' , str_replace('\\n\\','',$this->get_default_form()));
                add_bk_option( 'booking_form_show' ,str_replace('\\n\\','',$this->get_default_form_show()));
           } else {
                add_bk_option( 'booking_form' , str_replace('\\n\\','', $this->reset_to_default_form('payment') ));
                add_bk_option( 'booking_form_show' ,str_replace('\\n\\','',$this->reset_to_default_form_show('payment') ));
           }
           update_bk_option( 'booking_skin',  '/css/skins/traditional.css');
           update_bk_option( 'booking_is_show_legend' , 'On' );
            if ( wpdev_bk_is_this_demo() ) {
                update_bk_option( 'booking_is_use_captcha' , 'On' );                    
            }

            $charset_collate = '';
            $wp_queries = array();


            if ( ( ! wpbc_is_table_exists('bookingtypes')  )) { // Cehck if tables not exist yet
                    //if ( $wpdb->has_cap( 'collation' ) ) {
                        if ( ! empty($wpdb->charset) )
                            $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                        if ( ! empty($wpdb->collate) )
                            $charset_collate .= " COLLATE $wpdb->collate";
                    //}
                    /** Create WordPress database tables SQL */
                    $wp_queries[] = "CREATE TABLE {$wpdb->prefix}bookingtypes (
                         booking_type_id bigint(20) unsigned NOT NULL auto_increment,
                         title varchar(200) NOT NULL default '',
                         PRIMARY KEY  (booking_type_id)
                        ) $charset_collate;";

                    $wp_queries[] = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}bookingtypes ( title ) VALUES ( %s );", __('Default' ,'booking') );
                    $wp_queries[] = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}bookingtypes ( title ) VALUES ( %s );", __('Apartment#1' ,'booking') );
                    $wp_queries[] = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}bookingtypes ( title ) VALUES ( %s );", __('Apartment#2' ,'booking') );
                    $wp_queries[] = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}bookingtypes ( title ) VALUES ( %s );", __('Apartment#3' ,'booking') );

                    if ( ! wpdev_bk_is_this_demo() ) {
                        $booking__text_description = trim( $wpdb->prepare('%s', __('Please, reserve an apartment with fresh flowers.' ,'booking') )
                               , "'^~" );
                        
                        $wp_queries[] = "INSERT INTO {$wpdb->prefix}booking ( form, modification_date ) VALUES (
                         'text^name1^Victoria~text^secondname1^Smith~email^email1^test.booking@wpbookingcalendar.com~text^phone1^(039)458-77-88~select-one^visitors1^2~select-one^children1^0~textarea^details1^".$booking__text_description."~checkbox^term_and_condition1[]^I Accept term and conditions', NOW() );";
                    }

                    foreach ($wp_queries as $wp_q) 
                        $wpdb->query( $wp_q );

                    if ( ! wpdev_bk_is_this_demo() ) {
                        $temp_id = $wpdb->insert_id;
                        $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                             booking_id,
                             booking_date
                            ) VALUES
                            ( ". $temp_id .", CURDATE()+ INTERVAL 6 day ),
                            ( ". $temp_id .", CURDATE()+ INTERVAL 7 day ),
                            ( ". $temp_id .", CURDATE()+ INTERVAL 8 day );";
                        $wpdb->query( $wp_queries_sub );
                    }
            }

            if ( class_exists('wpdev_bk_multiuser'))
                if  (wpbc_is_field_in_table_exists('bookingtypes','users') == 0){
                    $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD users BIGINT(20) DEFAULT '1'";
                    $wpdb->query( $simple_sql );
                }


            if  (wpbc_is_field_in_table_exists('booking','remark') == 0){ // Add remark field
                $simple_sql = "ALTER TABLE {$wpdb->prefix}booking ADD remark TEXT";
                $wpdb->query( $simple_sql );
            }

            if  (wpbc_is_field_in_table_exists('bookingtypes','import') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD import text";
                $wpdb->query( $simple_sql );
            }

            if  (wpbc_is_field_in_table_exists('booking','hash') == 0) {  //HASH_EDIT
                $simple_sql = "ALTER TABLE {$wpdb->prefix}booking ADD hash TEXT AFTER form";
                $wpdb->query( $simple_sql );

                $sql_check_table = "SELECT booking_id as id FROM {$wpdb->prefix}booking " ;
                $res = $wpdb->get_results( $sql_check_table  );
                foreach ($res as $l) {
                     $wpdb->query( "UPDATE {$wpdb->prefix}booking SET hash = MD5('".time() . '_' .rand(1000,1000000)."') WHERE booking_id = " . $l->id );
                }

            }

            if ( wpdev_bk_is_this_demo() ) {
                $remark_text = 'Here can be some note about this booking...';
                $update_sql = "UPDATE {$wpdb->prefix}booking AS bk SET bk.remark='$remark_text' WHERE bk.booking_id=1;";
                $wpdb->query( $update_sql );
            }


        // TODO: Create Booking Edit post, if its not exist yet.
        //
        // Create post object
        //$my_post = array(
        //  'ID'             => [ <post id> ] //Are you updating an existing post?
        //  'menu_order'     => [ <order> ] //If new post is a page, it sets the order in which it should appear in the tabs.
        //  'comment_status' => [ 'closed' | 'open' ] // 'closed' means no comments.
        //  'ping_status'    => [ 'closed' | 'open' ] // 'closed' means pingbacks or trackbacks turned off
        //  'pinged'         => [ ? ] //?
        //  'post_author'    => [ <user ID> ] //The user ID number of the author.
        //  'post_category'  => [ array(<category id>, <...>) ] //post_category no longer exists, try wp_set_post_terms() for setting a post's categories
        //  'post_content'   => [ <the text of the post> ] //The full text of the post.
        //  'post_date'      => [ Y-m-d H:i:s ] //The time post was made.
        //  'post_date_gmt'  => [ Y-m-d H:i:s ] //The time post was made, in GMT.
        //  'post_excerpt'   => [ <an excerpt> ] //For all your post excerpt needs.
        //  'post_name'      => [ <the name> ] // The name (slug) for your post
        //  'post_parent'    => [ <post ID> ] //Sets the parent of the new post.
        //  'post_password'  => [ ? ] //password for post?
        //  'post_status'    => [ 'draft' | 'publish' | 'pending'| 'future' | 'private' | 'custom_registered_status' ] //Set the status of the new post.
        //  'post_title'     => [ <the title> ] //The title of your post.
        //  'post_type'      => [ 'post' | 'page' | 'link' | 'nav_menu_item' | 'custom_post_type' ] //You may want to insert a regular post, page, link, a menu item or some custom post type
        //  'tags_input'     => [ '<tag>, <tag>, <...>' ] //For tags.
        //  'to_ping'        => [ ? ] //?
        //  'tax_input'      => [ array( 'taxonomy_name' => array( 'term', 'term2', 'term3' ) ) ] // support for custom taxonomies. 
        //);  
        //// Insert the post into the database
        //wp_insert_post( $my_post );    


        if ( wpdev_bk_is_this_demo() ) {
                add_bk_option( 'booking_url_bookings_edit_by_visitors', site_url() .'/booking/edit/' );
                update_bk_option( 'booking_type_of_day_selections' , 'multiple' );
        } else
                add_bk_option( 'booking_url_bookings_edit_by_visitors', site_url() );



        add_bk_option( 'booking_default_booking_resource', '' );  // All resources
        //add_bk_option( 'booking_default_booking_resource', $this->get_default_booking_resource_id() );  // Default resource
        add_bk_option( 'booking_is_change_hash_after_approvement', 'Off');


        $blg_title = get_option('blogname'); 
        $blg_title = str_replace('"', '', $blg_title);
        $blg_title = str_replace("'", '', $blg_title);

        add_bk_option( 'booking_email_modification_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_modification_subject',__('The reservation has been modified' ,'booking'));        
        add_bk_option( 'booking_email_modification_content',htmlspecialchars(sprintf(__('The reservation %s for: %s has been modified. %sYou can edit this booking on this page: %s  Thank you, %s' ,'booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>', '[visitorbookingediturl]<br/><br/>' , $blg_title.'<br/>[siteurl]')));
        add_bk_option( 'booking_is_email_modification_adress', 'On' );
        add_bk_option( 'booking_is_email_modification_send_copy_to_admin' , 'Off'  );
        
        add_bk_option( 'booking_resourses_num_per_page' , '10'  );

        add_bk_option( 'booking_default_title_in_day_for_calendar_view_mode', '[id]:[name]');
        
        add_bk_option( 'booking_csv_export_separator' , ';');
    }

    //Decativate
    function pro_deactivate(){
        global $wpdb;

        delete_bk_option( 'booking_form');
        delete_bk_option( 'booking_form_show');

        delete_bk_option( 'booking_default_booking_resource',1);

        

        delete_bk_option( 'booking_email_modification_adress' );
        delete_bk_option( 'booking_email_modification_subject');
        delete_bk_option( 'booking_email_modification_content');
        delete_bk_option( 'booking_is_email_modification_adress');
        delete_bk_option( 'booking_is_email_modification_send_copy_to_admin'  );


        delete_bk_option( 'booking_is_change_hash_after_approvement');
        delete_bk_option( 'booking_url_bookings_edit_by_visitors');
        delete_bk_option( 'booking_resourses_num_per_page'   );
        delete_bk_option( 'booking_default_title_in_day_for_calendar_view_mode');

        delete_bk_option( 'booking_csv_export_separator' );

        $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bookingtypes" );
    }

    // Check if user can be at some admin panel, which belong to specific booking resource
    function recheck_version($blank){ 

        $ver = get_bk_option('bk_version_data');      
        if ( $ver === false ) {
        ?>
                <div id="recheck_version">
                    <div class="clear" style="height:10px;"></div>
                        <script type="text/javascript">
                        function sendRecheck(order_num){


                            document.getElementById('ajax_working').innerHTML =
                            '<div class="updated ajax_message" id="ajax_message">\n\
                                <div style="float:left;">'+'<?php _e('Sending request...' ,'booking') ?>'+'</div> \n\
                                <div class="wpbc_spin_loader">\n\
                                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                                </div>\n\
                            </div>';

                            jQuery.ajax({                                           // Start Ajax Sending
                                // url: '<?php echo WPDEV_BK_PLUGIN_URL , '/' ,  WPDEV_BK_PLUGIN_FILENAME ; ?>' ,
                                url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                                type:'POST',
                                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                                // beforeSend: someFunction,
                                data:{
                                    // ajax_action : 'CHECK_BK_VERSION',
                                    action : 'CHECK_BK_VERSION',
                                    order_num:order_num,
                                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                                }
                            });
                        }
                        </script>

                    <div style="margin:15px auto;" class="code_description wpdevbk">
                        <div class="shortcode_help_section well">

                        <div style="width:auto;text-align: center;padding:10px;">
                            <span style="font-weight:bold;font-size:1.1em;line-height:24px;margin-right:10px;" ><?php _e('Order number' ,'booking'); ?>:</span>

                            <input type="text" maxlength="20" value="" style="width:170px;" id="bk_order_number" name="bk_order_number" />
                            <input class="button" style="" type="button" value="<?php _e('Register' ,'booking'); ?>" name="submit_advanced_resources_settings" onclick="javascript:sendRecheck(document.getElementById('bk_order_number').value);" />
                            <div class="clear" style="height:10px;"></div>
                            <span style="font-style: italic;text-shadow:0 1px 0 #fff;font-size:1em;"><?php _e('Please, enter order number of your purchased version, which you received to your billing email.' ,'booking');?></span>


                            <div class="clear" style="height:20px;"></div>
                          <span class="description" style="font-style: italic;font-size:1em;"><?php printf(__('If you will get any difficulties or have a questions, please contact by email %s' ,'booking'),'<code><a href="mailto:activate@wpbookingcalendar.com">activate@wpbookingcalendar.com</a></code>');?></span><br/>

                          </div>
                        </div>
                    </div>

                </div>

            </div>
        <?php return false;
        }
        return true;
    }

}
