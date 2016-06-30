<?php 
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Buttons for Toolbar 
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
//  C u s t o m      b u t t o n s ///////////////////////////////////////////////////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

if (!defined('WP_BK_PREFIX'))       define('WP_BK_PREFIX',  'wpdev_bk' );
if (!defined('WP_BK_ICON_URL'))     define('WP_BK_ICON_URL',  WPDEV_BK_PLUGIN_URL . '/img/bc_black-16x16.png' );
if (!defined('WP_BK_SETTINGS_CUSTOM_BUTTONS_FUNC_NAME_FROM_JS_FILE')) define('WP_BK_SETTINGS_CUSTOM_BUTTONS_FUNC_NAME_FROM_JS_FILE', 'set_bk_buttons');    //Edit this name at the JS file of custom buttons
if (!defined('WP_BK_SETTINGS_CUSTOM_EDITOR_BUTTON_ROW'))              define('WP_BK_SETTINGS_CUSTOM_EDITOR_BUTTON_ROW',  1 );

function wpdev_bk_get_custom_buttons_settings() {
    return  array(
                'booking_insert' => array(
                    'hint' => __('Insert booking calendar' ,'booking'),
                    'title'=> __('Booking calendar' ,'booking'),
                    'img'=> WP_BK_ICON_URL,
                    'js_func_name_click' => 'booking_click',
                    'bookmark' => 'booking',
                    'class' => 'bookig_buttons',
                    'is_close_bookmark' => 0
                    )
              );
}


// C u s t o m   b u t t o n s  /////////////////////////////////////////////////////////////////////
function wpdev_bk_add_custom_buttons() {
    
    // Don't bother doing this stuff if the current user lacks permissions
    if ( ! current_user_can('edit_posts') && ! current_user_can('edit_pages') ) return;

    // Add only in Rich Editor mode
    if (  ( in_array( basename($_SERVER['PHP_SELF']),  array('post-new.php', 'page-new.php', 'post.php', 'page.php') ) ) /*&& ( get_user_option('rich_editing') == 'true')*/  ) {

        add_filter( 'mce_external_plugins',  'wpdev_bk_mce_external_plugins' );

        add_action( 'edit_form_advanced',  'wpdev_bk_add_custom_button_function' );
        add_action( 'edit_page_form',  'wpdev_bk_add_custom_button_function' );

        if ( 1 == WP_BK_SETTINGS_CUSTOM_EDITOR_BUTTON_ROW )
            add_filter( 'mce_buttons',   'wpdev_bk_mce_buttons' );
        else
            add_filter( 'mce_buttons_' . WP_BK_SETTINGS_CUSTOM_EDITOR_BUTTON_ROW ,   'wpdev_bk_mce_buttons' );

        add_action( 'admin_head',   'wpdev_bk_custom_button_dialog_CSS' );
        add_action( 'admin_footer', 'wpdev_bk_custom_button_dalog_structure_DIV' );

        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style(  'wpdev-bk-jquery-ui', WPDEV_BK_PLUGIN_URL. '/css/jquery-ui.css', array(), 'wpdev-bk', 'screen' );
    }
}

add_action( 'init', 'wpdev_bk_add_custom_buttons') ;

// Add button code to the tiny editor
function wpdev_bk_insert_wpdev_button() {

    if ( count(wpdev_bk_get_custom_buttons_settings() ) > 0) {
        ?>  <script type="text/javascript"> <?php

        echo '      function '. WP_BK_SETTINGS_CUSTOM_BUTTONS_FUNC_NAME_FROM_JS_FILE.'(ed, url) {';

        $custom_buttons_settings = wpdev_bk_get_custom_buttons_settings();
        foreach ( $custom_buttons_settings as $type => $props ) {
            echo "if ( typeof ".$props['js_func_name_click']." == 'undefined' ) return;";
            echo "  ed.addButton('".  WP_BK_PREFIX . '_' . $type ."', {";
            echo "		title : '". $props['hint'] ."',";
            echo "		image : '". $props['img'] ."',";
            echo "		onclick : function() {";
            echo "			". $props['js_func_name_click'] ."('". $type ."');";
            echo "		}";
            echo "	});";
        }
        echo '}';

        ?> </script> <?php 
    }
}

add_action( 'admin_head', 'wpdev_bk_insert_wpdev_button');

// Load the custom TinyMCE plugin
function wpdev_bk_mce_external_plugins( $plugins ) {
    $plugins[WP_BK_PREFIX . '_quicktags'] = WPDEV_BK_PLUGIN_URL.'/js/custom_buttons/editor_plugin.js';
    return $plugins;
}

// Add the custom TinyMCE buttons
function wpdev_bk_mce_buttons( $buttons ) {
    //array_push( $buttons, "separator", 'wpdev_booking_insert', "separator" );
    array_push( $buttons, "separator");
    $custom_buttons_settings = wpdev_bk_get_custom_buttons_settings();
    foreach ( $custom_buttons_settings as $type => $strings ) {
        array_push( $buttons, WP_BK_PREFIX . '_' . $type );
    }

    return $buttons;
}

// Add the old style buttons to the non-TinyMCE editor views and output all of the JS for the button function + dialog box
function wpdev_bk_add_custom_button_function() {
    $buttonshtml = '';
    $datajs='';
    $custom_buttons_settings = wpdev_bk_get_custom_buttons_settings();
    foreach ( $custom_buttons_settings as $type => $props ) {

        $buttonshtml .= '<input type="button" class="ed_button button button-small" onclick="'.$props['js_func_name_click'].'(\'' . $type . '\')" title="' . $props['hint'] . '" value="' . $props['title'] . '" />';

        $datajs.= " wpdev_bk_Data['$type'] = {\n";
        $datajs.= '		title: "' . esc_js( $props['title'] ) . '",' . "\n";
        $datajs.= '		tag: "' . esc_js( $props['bookmark'] ) . '",' . "\n";
        $datajs.= '		tag_close: "' . esc_js( $props['is_close_bookmark'] ) . '",' . "\n";
        $datajs.= '		cal_count: "' . get_bk_option( 'booking_client_cal_count' )  . '"' . "\n";
        $datajs.=  "\n	};\n";
    }
    ?><script type="text/javascript">
        // <![CDATA[
        var wpdev_bk_Data={};
        <?php echo $datajs; ?>
        var selected_booking_shortcode = 'bookingform';
        // Set default heights (IE sucks) if ( jQuery.browser.msie ) {   }

        // This function is run when a button is clicked. It creates a dialog box for the user to input the data.
        function booking_click( tag ) {

            // Open the dialog while setting the width, height, title, buttons, etc. of it
            var buttons = { "<?php echo esc_js(__('Ok' ,'booking')); ?>": wpdev_bk_ButtonOk,
                "<?php echo esc_js(__('Cancel' ,'booking')); ?>": wpdev_bk_DialogClose
            };
            var title = wpdev_bk_Data[tag]["title"]; //'&lt;img src="<?php echo WP_BK_ICON_URL; ?>" /&ht; ' + 

            jQuery("#wpdev_bk-dialog").dialog({
                autoOpen: false,
                width: 700,
                buttons:buttons,
                draggable:false,
                hide: 'slide',
                resizable: false,
                modal: true,
                title: title
            });/**/
            // Reset the dialog box incase it's been used before
            //jQuery("#wpdev_bk-dialog input").val("");
            jQuery("#calendar_tag_name").val(wpdev_bk_Data[tag]['tag']);
            jQuery("#calendar_tag_close").val(wpdev_bk_Data[tag]['tag_close']);
            jQuery("#calendar_count").val(wpdev_bk_Data[tag]['cal_count']);
            // Style the jQuery-generated buttons by adding CSS classes and add second CSS class to the "Okay" button
            jQuery(".ui-dialog button").addClass("button").each(function(){
                if ( "<?php echo esc_js(__('Ok' ,'booking')); ?>" == jQuery(this).html() ) jQuery(this).addClass("button-highlighted");
            });

            // Do some hackery on any links in the message -- jQuery(this).click() works weird with the dialogs, so we can't use it
            jQuery("#wpdev_bk-dialog-content a").each(function(){
                jQuery(this).attr("onclick", 'window.open( "' + jQuery(this).attr("href") + '", "_blank" );return false;' );
            });

            // Show the dialog now that it's done being manipulated
            jQuery("#wpdev_bk-dialog").dialog("open");

            // Focus the input field
            jQuery("#wpdev_bk-dialog-input").focus();
        }

        // Close + reset
        function wpdev_bk_DialogClose() {
            jQuery("#wpdev_bk-dialog").dialog("close");
        }

        // Callback function for the "Okay" button
        function wpdev_bk_ButtonOk() {

            var cal_tag = selected_booking_shortcode;
            if ( cal_tag == '' ) return wpdev_bk_DialogClose();
            var text = '';
            if (cal_tag == 'bookingform') {                             // Select  the specific shortcode, depence from selection in shortcode section.

                if (jQuery("#calendar_or_form").val() == 'form')     cal_tag = 'booking';
                if (jQuery("#calendar_or_form").val() == 'calendar') cal_tag = 'bookingcalendar';
                if (jQuery("#calendar_or_form").val() == 'onlyform') cal_tag = 'bookingform';

                text += '[' + cal_tag;

                // Parameters Start:
                if (jQuery("#calendar_type").length != 0 )      text += ' ' + 'type='        + jQuery("#calendar_type").val() ;

                if (jQuery("#booking_form_type").length != 0 )  text += ' ' + 'form_type=\'' + jQuery("#booking_form_type").val() + '\'';

                if (cal_tag != 'bookingform') {

                    if ( jQuery("#calendar_count").length != 0 ) text += ' ' + 'nummonths=' + jQuery("#calendar_count").val();

                    if ( jQuery("#start_month_active").attr('checked') ) text += ' ' + 'startmonth=\''+ jQuery("#year_start_month").val() +'-'+ jQuery("#month_start_month").val() + '\'';

                } else { // Booking Form
                    text += ' ' + 'selected_dates=\''+ jQuery("#day_popup").val() +'.'+ jQuery("#month_popup").val()+'.'+ jQuery("#year_popup").val() +'\'';
                }

                <?php //if ( class_exists('wpdev_bk_biz_m')) { ?>
                    if ( ( jQuery("#bookingcalendar_options").length != 0 ) && (jQuery("#bookingcalendar_options").val()!='') ) text += ' ' + 'options=\'' + jQuery("#bookingcalendar_options").val() + '\'';
                <?php //} ?>
            // Parameters End !

            } else if (cal_tag == 'bookingsearch') {                           // Select search  form or search  results

                var selected_bookingsearch_type = jQuery('[name="bookingsearch_type"]:checked');
                if (selected_bookingsearch_type.val() == 'bookingsearch')         cal_tag = 'bookingsearch';
                if (selected_bookingsearch_type.val() == 'bookingsearchresults')  cal_tag = 'bookingsearchresults';

                text += '[' + cal_tag;


                if (cal_tag == 'bookingsearch' ) {
                    // Parameters Start:
                    if ( jQuery("#search_at_diff_page").attr('checked') ) {
                        text += ' ' + 'searchresults=\''+ jQuery("#bookingsearch_searchresults").val() + '\'';
                    }

                    if (jQuery("#bookingsearch_noresultstitle").length != 0 )      text += ' ' + 'noresultstitle=\''     + jQuery("#bookingsearch_noresultstitle").val() + '\'';
                    if (jQuery("#bookingsearch_searchresultstitle").length != 0 )  text += ' ' + 'searchresultstitle=\'' + jQuery("#bookingsearch_searchresultstitle").val() + '\'';

                    <?php if ( class_exists('wpdev_bk_multiuser')) { ?>
                        if ( ( jQuery("#bookingsearch_users").length != 0 ) && (jQuery("#bookingsearch_users").val()!='') ) text += ' ' + 'users=\'' + jQuery("#bookingsearch_users").val() + '\'';
                    <?php } ?>
                    // Parameters End !
                }

            } else if (cal_tag == 'bookingselect') {                           // Select search  form or search  results
                text += '[' + cal_tag;

                // Parameters Start:
                if ( jQuery("#bookingselect_resources").length != 0 ) {
                    var selectedOptions = jQuery('#bookingselect_resources option:selected');
                    var selectedValues = jQuery.map(selectedOptions ,function(option) { if (option.value != '') {return option.value;} }).join(',');
                    text += ' ' + 'type=\'' + selectedValues+ '\'';
                }
                
                if ( jQuery("#bookingselect_preselected_resource").length != 0 ) 
                    text += ' ' + 'selected_type=\'' + jQuery("#bookingselect_preselected_resource").val()+ '\'';
                
                if ( jQuery("#bookingselect_calendar_count").length != 0 ) 
                    text += ' ' + 'nummonths=' + jQuery("#bookingselect_calendar_count").val();

                if ( jQuery("#bookingselect_form_type").length != 0 )   
                    text += ' ' + 'form_type=\'' + jQuery("#bookingselect_form_type").val()+ '\'';

                if ( jQuery("#bookingselect_title").length != 0 )       
                    text += ' ' + 'label=\'' + jQuery("#bookingselect_title").val()+ '\'';
                
                if ( jQuery("#bookingselect_first_option_title").length != 0 )       
                    text += ' ' + 'first_option_title=\'' + jQuery("#bookingselect_first_option_title").val()+ '\'';                                
                // Parameters End !

            } else {
                text += '[' + cal_tag;
            }
            text += ']';

            if ( jQuery("#calendar_tag_close").length != 0 )
                if ( jQuery("#calendar_tag_close").val() != 0)
                    text += '[/' + cal_tag + ']';

            wpdev_bk_send_to_editor(text);                
            wpdev_bk_DialogClose();
        }

        function wpdev_bk_send_to_editor(h) {
                var ed, mce = typeof(tinymce) != 'undefined', qt = typeof(QTags) != 'undefined';

                if ( !wpActiveEditor ) {
                        if ( mce && tinymce.activeEditor ) {
                                ed = tinymce.activeEditor;
                                wpActiveEditor = ed.id;
                        } else if ( !qt ) {
                                return false;
                        }
                } else if ( mce ) {
                        if ( tinymce.activeEditor && (tinymce.activeEditor.id == 'mce_fullscreen' || tinymce.activeEditor.id == 'wp_mce_fullscreen') )
                                ed = tinymce.activeEditor;
                        else
                                ed = tinymce.get(wpActiveEditor);
                }

                if ( ed && !ed.isHidden() ) {
                        // restore caret position on IE
                        if ( tinymce.isIE && ed.windowManager.insertimagebookmark )
                                ed.selection.moveToBookmark(ed.windowManager.insertimagebookmark);

                        if ( h.indexOf('[caption') !== -1 ) {
                                if ( ed.wpSetImgCaption )
                                        h = ed.wpSetImgCaption(h);
                        } else if ( h.indexOf('[gallery') !== -1 ) {
                                if ( ed.plugins.wpgallery )
                                        h = ed.plugins.wpgallery._do_gallery(h);
                        } else if ( h.indexOf('[embed') === 0 ) {
                                if ( ed.plugins.wordpress )
                                        h = ed.plugins.wordpress._setEmbed(h);
                        }

                        ed.execCommand('mceInsertContent', false, h);
                } else if ( qt ) {
                        QTags.insertContent(h);
                } else {
                        document.getElementById(wpActiveEditor).value += h;
                }

                try{tb_remove();}catch(e){};
        }

        function add_booking_html_button(){ // Add the buttons to the HTML view
            if (jQuery("#ed_toolbar").length == 0) setTimeout("add_booking_html_button()",100);
            else jQuery("#ed_toolbar").append('<?php echo wp_specialchars_decode(esc_js( $buttonshtml ), ENT_COMPAT); ?>');
        }

        // On page load...
        jQuery(document).ready(function(){

            setTimeout("add_booking_html_button()",100);

            // If the Enter key is pressed inside an input in the dialog, do the "Okay" button event
            jQuery("#wpdev_bk-dialog :input").keyup(function(event){
                if ( 13 == event.keyCode ) // 13 == Enter
                    wpdev_bk_ButtonOkay();
            });

            // Make help links open in a new window to avoid loosing the post contents
            jQuery("#wpdev_bk-dialog-slide a").each(function(){
                jQuery(this).click(function(){
                    window.open( jQuery(this).attr("href"), "_blank" );
                    return false;
                });
            });
        });
        // ]]>
    </script><?php
}

// Output the <div> used to display the dialog box
function wpdev_bk_custom_button_dalog_structure_DIV() { ?>
    <div class="hidden">
        <div id="wpdev_bk-dialog" style="width:90%;height:90%;">
            <div class="wpdev_bk-dialog-content">
                <div class="wpdev_bk-dialog-inputs">
                    <?php 
                        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                        if ( $is_can ) make_bk_action('show_tabs_inside_insertion_popup_window');
                    ?>
                    <div id="popup_new_reservation" style="display:block;" class="booking_configuration_dialog">

                      <div id="popup_new_reservation_main_content"><?php

                        if( get_bk_version() !== 'free' ) {

                            $types_list  = get_bk_types(false, false); 

                            ?><div class="field">
                                  <fieldset>
                                        <label for="calendar_type"><?php _e('Booking resource:' ,'booking'); ?></label>
                                        <select id="calendar_type" name="calendar_type">
                                            <?php foreach ($types_list as $tl) { ?>
                                            <option value="<?php echo $tl->id; ?>"
                                                        style="<?php if  (isset($tl->parent)) if ($tl->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                                                    ><?php echo $tl->title; ?></option>
                                            <?php } ?>
                                        </select>
                                      <span class="description"><?php _e('Select booking resource' ,'booking'); ?></span>
                                  </fieldset>
                              </div>                              
                        <?php } ?>

                        <?php                                 
                            if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) )    
                                make_bk_action('wpdev_show_bk_form_selection') ;
                        ?>

                        <div class="field">
                            <fieldset>
                                <label for="calendar_count"><?php _e('Visible months:' ,'booking'); ?></label>
                                <select  id="calendar_count"  name="calendar_count" >
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

                        <div class="field">
                            <fieldset>
                                <label for="start_month_active"><?php _e('Start month:' ,'booking'); ?></label>
                                <input id="start_month_active"  name="start_month_active" type="checkbox" 
                                       onchange="javascript:if(! this.checked){ jQuery('select.start_month').attr('disabled', 'disabled'); } else {jQuery('select.start_month').removeAttr('disabled');}" 
                                        />
                                <?php 
                                $start_year = date('Y') ;  
                                $start_month = date('m') ;
                                ?>
                                <select class="start_month" id="year_start_month" disabled="DISABLED" name="year_start_month" style="width:65px;" > <?php for ($mi = ($start_year); $mi < ($start_year+11); $mi++) {   echo '<option value="'.$mi.'" >'.$mi.'</option>';   } ?> </select> /
                                <select class="start_month"  id="month_start_month" disabled="DISABLED"  name="month_start_month" style="width:50px;" > <?php for ($mi = 1; $mi < 13; $mi++) { if ($mi<10) {$mi ='0'.$mi;}  echo '<option value="'.$mi.'" '.(($start_month==$mi)?' selected="SELECTED" ':'').' >'.$mi.'</option>';   } ?> </select>
                                <span class="description"><?php _e('Select start month of calendar' ,'booking'); ?></span>
                            </fieldset>
                        </div>

                        <div class="field">
                            <fieldset>
                                <div style="float:left;">
                                <label for="calendar_or_form"><?php _e('Show in the page:' ,'booking'); ?></label>
                                </div>
                                <div style="float:left;">
                                <select id="calendar_or_form"  name="calendar_or_form" onchange="
                                javascript: if(this.value=='onlyform') document.getElementById('dates_for_form').style.display='block'; else  document.getElementById('dates_for_form').style.display='none';
                                ">
                                    <option value="form"><?php _e('Booking form with calendar' ,'booking'); ?></option>
                                    <option value="calendar"><?php _e('Only availability calendar' ,'booking'); ?></option>
                                    <?php if (class_exists('wpdev_bk_biz_l')) { ?><option value="onlyform"><?php _e('Only booking form' ,'booking'); ?></option><?php } ?>
                                </select>
                                </div>
                                <div style="float:left;">
                                <?php if (class_exists('wpdev_bk_biz_l')) {  
                                  ?><span style="margin-left:5px;display:none;" id="dates_for_form"> <?php _e('for' ,'booking'); ?>
                                        <select  id="year_popup"  name="year_popup" style="width:65px;" > <?php for ($mi = ($start_year); $mi < ($start_year+11); $mi++) {   echo '<option value="'.$mi.'" >'.$mi.'</option>';   } ?> </select> /
                                        <select  id="month_popup"  name="month_popup" style="width:50px;" > <?php for ($mi = 1; $mi < 13; $mi++) { if ($mi<10) {$mi ='0'.$mi;}  echo '<option value="'.$mi.'" '.(($start_month==$mi)?' selected="SELECTED" ':'').'  >'.$mi.'</option>';   } ?> </select> /
                                        <select  id="day_popup"  name="day_popup" style="width:50px;" > <?php for ($mi = 1; $mi < 32; $mi++) { if ($mi<10) {$mi ='0'.$mi;}   echo '<option value="'.$mi.'" >'.$mi.'</option>';   } ?> </select> <?php _e('date' ,'booking'); ?>.
                                    </span><?php } ?>
                                </div>
                                <div class="clear"></div>
                                <p class="description"><?php _e('Select to show the entire booking form or the availability calendar only.' ,'booking'); ?></p>
                            </fieldset>
                        </div>                              
                        <?php make_bk_action('show_additional_arguments_for_shortcode'); ?>  

                      </div>

                      <div style="height:1px;clear:both;width:100%;"></div>


                        <div style="color:#21759B;cursor: pointer;font-weight: bold;float:left;"
                           onclick="javascript: jQuery('.bk_show_options_parameter').toggle(1);
                                                jQuery('#togle_options_parameter').slideToggle('normal');
                                                jQuery('#popup_new_reservation_main_content').slideToggle('normal');"
                           style="text-decoration: none;font-weight: bold;font-size: 11px;">
                            <span class="bk_show_options_parameter">+ <span style="border-bottom:1px dashed #21759B;"><?php _e('Show advanced settings' ,'booking'); ?></span></span>
                            <span class="bk_show_options_parameter" style="display:none;">- <span style="border-bottom:1px dashed #21759B;"><?php _e('Hide advanced settings' ,'booking'); ?></span></span>
                        </div>

                        <div class="bk_show_options_parameter description" style="color: #777777;float: right;width: 475px;"><?php 
                        printf(__('Setting advanced parameters of the calendar. %sLike width, height and structure %s' ,'booking'),'<em>','</em>');
                        if ( class_exists('wpdev_bk_biz_m')) 
                            printf(__('%s or minimum and fixed number of days selection for the specific day of week or season.%s' ,'booking'),'<em>','</em>'); 
                        ?></div>

                      <div style="height:1px;clear:both;width:100%;"></div>

                      <div class="field0" id="togle_options_parameter" style="display:none;margin:10px;">
                            <div class="bk_help_message" style="margin:5px 0px;"><?php printf(__('Please read more about the possible customizations of these %soptions%s %shere%s' ,'booking'),'<strong>','</strong>','<a href="http://wpbookingcalendar.com/help/booking-calendar-shortcodes/" target="_blank">','</a>'); ?></div>                                     
                            <strong><span for="bookingcalendar_options"><?php _e('Options:' ,'booking'); ?></span></strong><br/>
                            <textarea id="bookingcalendar_options"  name="bookingcalendar_options" style="width:100%; height:50px;"></textarea>
                            <span class="description" style="width:99%;">
                                <?php printf(__('Specify the full width of calendar, height of date cell and number of months in one row. ' ,'booking')); ?><br/>
                                <div style="margin-left:35px;">
                                    <strong><?php _e('Description' ,'booking'); ?>: </strong>
                                        "<?php echo(__('Calendar have 2 months in a row, the cell height is 30px and calendar full width 568px (possible to use percentage for width: 100%)' ,'booking')); ?>"<br/>
                                    <strong><?php _e('Code Example' ,'booking'); ?>: </strong>
                                        <?php echo '<code>{calendar months_num_in_row=2 width=568px cell_height=30px}</code>'; ?>
                                </div><br/>
                                <?php if ( class_exists('wpdev_bk_biz_m')) { ?>
                                    <?php printf(__('Specify that during certain seasons (or days of week), the specific minimum number of days must be booked. ' ,'booking')); ?><br/>
                                    <div style="margin-left:35px;">
                                        <strong><?php _e('Description' ,'booking'); ?>: </strong>
                                            "<?php printf(__('Visitor can select only 4 days starting at Monday, 3 or 7 days – Friday, 2 days – Saturday, etc…' ,'booking')); ?>"<br/>
                                        <strong><?php _e('Code Example' ,'booking'); ?>: </strong>
                                            <?php echo '<code>{select-day condition="weekday" for="1" value="4"},{select-day condition="weekday" for="5" value="3,7"},{select-day condition="weekday" for="6" value="2"}</code>'; ?>
                                    </div>
                                <?php } ?>
                            </span>                                
                      </div>

                    </div>

                    <?php make_bk_action('show_insertion_popup_shortcode_for_bookingedit'); ?>

                    <input id="calendar_tag_name"   name="calendar_tag_name"    type="hidden" >
                    <input id="calendar_tag_close"  name="calendar_tag_close"   type="hidden" >
                </div>
            </div>
        </div>
    </div>
    <?php
}

// Hide TinyMCE buttons the user doesn't want to see + some misc editor CSS
function wpdev_bk_custom_button_dialog_CSS() {
    global $user_ID;
    // Attempt to match the dialog box to the admin colors
    if ( 'classic' == get_user_option('admin_color', $user_ID) ) {
        $color = '#fff';
        $background = '#777';
    } else {
        $color = '#fff';
        $background = '#777';
    }?>
    <style type='text/css'>
        .ui-dialog-titlebar {
            color: <?php  echo $color; ?>;
            background: <?php  echo $background; ?>;
        }
        <?php 
        $custom_buttons_settings = wpdev_bk_get_custom_buttons_settings();
        foreach ($custom_buttons_settings as $type => $props) {
            echo  '#content_' . WP_BK_PREFIX  . '_' . $type  . ' img.mceIcon{
                                                    width:16px;
                                                    height:16px;
                                                    margin:2px auto;0
                                               }';
        }
        ?>
        .ui-dialog-title img{
            margin:3px auto;
            width:16px;
            height:16px;
        }
        #wpdev_bk-dialog .field {
            margin:10px 0px;
            display:block;
            clear:both;
        }
        #wpdev_bk-dialog .field label {
            display: inline-block;
            font-weight: bold;
            padding-right: 10px;
            text-align: left;
            vertical-align: baseline;
            width: 170px;                
        }
        #wpdev_bk-dialog .wpdev_bk-dialog-inputs {float:left;}
        #wpdev_bk-dialog input[type="text"], 
        #wpdev_bk-dialog select {  
/*                width:120px;  */
        }
        #wpdev_bk-dialog .input_check {width:10px; margin:5px 10px;text-align:center;}
        #wpdev_bk-dialog .dialog-wraper {float:left;width:100%;}
        #wpdev_bk-dialog p.description {
            margin-left:180px;
        }
        #wpdev_bk-dialog .description {
            vertical-align: middle;
        }
        .ui-dialog-buttonset button { margin:0 5px !important}
        .bk_help_message{
            background-color: #FFFFE0;
            border: 1px solid #E6DB55;
            border-radius: 3px;
            margin:0 0 10px;
            padding: 5px;
            width: 98%;
            color:#777;
        }
        #wpdev_bk-dialog .wpbc_sub_options {
            background: none repeat scroll 0 0 #F3F3F3;
            border: 1px solid #DDDDDD;
            border-radius: 3px;
            box-shadow: 0 0 1px #FFFFFF;
            margin: 0;
            padding: 5px 10px;
            width: auto;
        }
        .booking_configuration_dialog {
            width:650px;
            height:110px;
            display:none;
        }
        <?php make_bk_action('show_insertion_popup_css_for_tabs'); ?>
    </style>
    <?php
}
/*
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

function wpbc_booking_insert_button($editor_id = 'content') {
    $post = get_post();
    if ( ! $post && ! empty( $GLOBALS['post_ID'] ) )
            $post = $GLOBALS['post_ID'];

    $img = '<img src="'.WP_BK_ICON_URL.'" style="width:16px;height:16px; margin-top: -4px; padding: 0 4px 0 0;" />'; //'<span class="wp-media-buttons-icon"></span> ';

    echo '<a href="javascript:void(0)" onclick="javascript:booking_click2(\'booking_insert\');" id="insert-wpbc-booking-calendar-button" class="button button-secondary" data-editor="' . esc_attr( $editor_id ) . '" title="' . esc_attr__( 'Add Booking' ) . '">' . $img . __( 'Add Booking' ) . '</a>';

}
add_action('media_buttons', 'wpbc_booking_insert_button'); /**/
?>