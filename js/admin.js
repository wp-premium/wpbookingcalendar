/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage BackEnd Main Script Lib
 * @category Scripts
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.09.10
 */

////////////////////////////////////////////////////////////////////////////////

/**  Show Yes/No dialog
 * 
 * @param {type} message_question
 * @returns {Boolean}
 */
function bk_are_you_sure( message_question ){
    var answer = confirm( message_question );
    if ( answer) { return true; }
    else         { return false;}
}

/** Show Alert Messages
 * 
 * @param {type} message
 * @param {type} m_type
 * @param {type} m_delay
 * @returns {undefined}
 */
function bk_admin_show_message( message, m_type, m_delay ){

    document.getElementById('ajax_working').innerHTML = '<div id="bk_alert_message" class="bk_alert_message"></div>';

    var alert_class = 'alert ';
    if (m_type == 'error')      alert_class += 'alert-error '; 
    if (m_type == 'info')       alert_class += 'alert-info '; 
    if (m_type == 'success')    alert_class += 'alert-success '; 

    document.getElementById('bk_alert_message').innerHTML =   '<div class="'+alert_class+'"> ' +
                                                                    '<a class="close" data-dismiss="alert">&times;</a> ' + 
                                                                    message + 
                                                                 '</div>';
    jQuery('#bk_alert_message').animate( {opacity: 1}, m_delay ).fadeOut(500);        
}


/** Set Booking listing row as   R e a d
 * 
 * @param {type} booking_id
 * @returns {undefined}
 */
function set_booking_row_read(booking_id){
    if (booking_id == 0) {
        jQuery('.new-label').addClass('hidden_items');
        jQuery('.bk-update-count').html( '0' );
    } else {
        jQuery('#booking_mark_'+booking_id + '').addClass('hidden_items');
        decrese_new_counter();
    }
}

/** Set Booking listing row as   U n R e a d
 * 
 * @param {type} booking_id
 * @returns {undefined}
 */
function set_booking_row_unread(booking_id){
    jQuery('#booking_mark_'+booking_id + '').removeClass('hidden_items');
    increase_new_counter();
}


/** Increase counter about new bookings
 * 
 * @returns {undefined}
 */
function increase_new_counter () {
    var my_num = parseInt(jQuery('.bk-update-count').html());
    my_num = my_num + 1;
    jQuery('.bk-update-count').html(my_num);
}

/** Decrease counter about new bookings
 * 
 * @returns {undefined}
 */
function decrese_new_counter () {
    var my_num = parseInt(jQuery('.bk-update-count').html());
    if (my_num>0){
        my_num = my_num - 1;
        jQuery('.bk-update-count').html(my_num);
    }
}


/** Functions for the TimeLine
 * 
 * @param {type} booking_id
 * @returns {undefined}
 */
function set_booking_row_approved_in_timeline(booking_id){  
    ////Approve   Add    to   [cell_bk_id_9] class [approved]    -- TODO: Also  in the [a_bk_id_9] - chnaged "data-content" attribute
    jQuery('.cell_bk_id_'+booking_id).addClass('approved');
    jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .approve_bk_link').addClass('hidden_items');
    jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .pending_bk_link').removeClass('hidden_items');
}

function set_booking_row_pending_in_timeline(booking_id){
    //Remove    Remove from [cell_bk_id_9] class [approved]      -- TODO: Also  in the [a_bk_id_9] - chnaged "data-content" attribute
    jQuery('.cell_bk_id_'+booking_id).removeClass('approved');
    jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .pending_bk_link').addClass('hidden_items');
    jQuery('.timeline_info_bk_actionsbar_' + booking_id + ' .approve_bk_link').removeClass('hidden_items');
}

function set_booking_row_deleted_in_timeline(booking_id){
    //          Remove in [cell_bk_id_9]   classes [time_booked_in_day]
    //          Delete element: [a_bk_id_]
    // TODO: Here is possible issue, if we are have several bookings per the same date and deleted only one

    // make actions on the elements, which are not have CLASS: "here_several_bk_id"
    // And have CLASS a_bk_id_ OR cell_bk_id_        
    jQuery(':not(.here_several_bk_id).a_bk_id_'+booking_id).fadeOut(1000);
    jQuery(':not(.here_several_bk_id).cell_bk_id_'+booking_id).removeClass('time_booked_in_day');
}


// Set Booking listing   R O W   Approved
function set_booking_row_approved(booking_id){
    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-approved').removeClass('hidden_items');
    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-pending').addClass('hidden_items');

    jQuery('#booking_row_'+booking_id + ' .booking-dates .field-booking-date').addClass('approved');

    jQuery('#booking_row_'+booking_id + ' .booking-actions .approve_bk_link').addClass('hidden_items');
    jQuery('#booking_row_'+booking_id + ' .booking-actions .pending_bk_link').removeClass('hidden_items');

}

// Set Booking listing   R O W   Pending
function set_booking_row_pending(booking_id){
    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-approved').addClass('hidden_items');
    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-pending').removeClass('hidden_items');

    jQuery('#booking_row_'+booking_id + ' .booking-dates .field-booking-date').removeClass('approved');

    jQuery('#booking_row_'+booking_id + ' .booking-actions .approve_bk_link').removeClass('hidden_items');
    jQuery('#booking_row_'+booking_id + ' .booking-actions .pending_bk_link').addClass('hidden_items');

}

// Remove  Booking listing   R O W
function set_booking_row_deleted(booking_id){
    jQuery('#booking_row_'+booking_id).fadeOut(1000);        
    jQuery('#gcal_imported_events_id_'+booking_id).remove();
}

// Set in Booking listing   R O W   Resource title
function set_booking_row_resource_name(booking_id, resourcename){
    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-resource').html(resourcename);
}

// Set in Booking listing   R O W   new Remark in hint
function set_booking_row_remark_in_hint(booking_id, new_remark){
    jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link').attr('data-original-title', new_remark);

    var my_img = jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link img').attr('src');
    var check_my_img = my_img.substr( my_img.length - 7);
    if (check_my_img !== '_rd.png') {
        my_img = my_img.substr(0, my_img.length - 4);
        jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link img').attr('src', my_img+'_rd.png');
    } else {
        my_img = my_img.substr(0, my_img.length - 7);
        jQuery('#booking_row_'+booking_id + ' .booking-actions .remark_bk_link img').attr('src', my_img+'.png');
    }


}

// Set in Booking listing   R O W   new Remark in hint
function set_booking_row_payment_status(booking_id, payment_status, payment_status_show){

    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').removeClass('label-important');
    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').removeClass('label-success');

    jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').html(payment_status_show);

    if (payment_status == 'OK') {
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').addClass('label-success');
    } else if (payment_status == '') {
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').addClass('label-important');
    } else {
        jQuery('#booking_row_'+booking_id + ' .booking-labels .label-payment-status').addClass('label-important');
    }
}



// Interface Element
function showSelectedInDropdown(selector_id, title, value){
    jQuery('#' + selector_id + '_selector .wpbc_selected_in_dropdown').html( title );
    jQuery('#' + selector_id ).val( value );
    jQuery('#' + selector_id + '_container').hide();
}

//Admin function s for checking all checkbos in one time
function setCheckBoxInTable(el_stutus, el_class){
     jQuery('.'+el_class).attr('checked', el_stutus);

     if ( el_stutus ) {
         jQuery('.'+el_class).parent().parent().addClass('row_selected_color');
     } else {
         jQuery('.'+el_class).parent().parent().removeClass('row_selected_color');
     }
}


// FixIn: 5.4.5
function wpbc_get_selected_locale( booking_id, wpdev_active_locale ) {
    
    var id_to_check = "" + booking_id;
    if ( id_to_check.indexOf('|') == -1 ) {
        var selected_locale = jQuery('#locale_for_booking' + booking_id).val();

        if (  ( selected_locale != '' ) && ( typeof(selected_locale) !== 'undefined' )  ) {
            wpdev_active_locale = selected_locale;
        } 
    }
    return wpdev_active_locale;
}

// Approve or set Pending  booking
function approve_unapprove_booking(booking_id, is_approve_or_pending, user_id, wpdev_active_locale, is_send_emeils ) {

    // FixIn: 5.4.5
    wpdev_active_locale = wpbc_get_selected_locale(booking_id,  wpdev_active_locale );
    
    if ( booking_id !='' ) {

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/'+wpdev_bk_plugin_filename;
        var ajax_type_action    = 'UPDATE_APPROVE';
        var ajax_bk_message     = 'Updating...';
        //var is_send_emeils      = 1;
        var denyreason          = '';
        if (is_send_emeils == 1) {
            if ( jQuery('#is_send_email_for_pending').length ) {
                is_send_emeils = jQuery('#is_send_email_for_pending').attr('checked');
                if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
                else                             {is_send_emeils = 1 ;}                
            }
            if ( jQuery('#denyreason').length )
                denyreason = jQuery('#denyreason').val();
        } else {
            is_send_emeils = 0;
        }


        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({                                           // Start Ajax Sending
            // url: wpdev_ajax_path,
            url: wpbc_ajaxurl, 
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
            // beforeSend: someFunction,
            data:{
                // ajax_action : ajax_type_action,         // Action
                action : ajax_type_action,         // Action
                booking_id : booking_id,                  // ID of Booking  - separator |
                is_approve_or_pending : is_approve_or_pending,           // Approve: 1, Reject: 0
                is_send_emeils : is_send_emeils,
                denyreason: denyreason,
                user_id: user_id,
                wpdev_active_locale:wpdev_active_locale,
                wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
            }
        });
        return false;  
    }

    return true;
}


// Delete booking
function delete_booking(booking_id, user_id, wpdev_active_locale, is_send_emeils ) {

    // FixIn: 5.4.5
    wpdev_active_locale = wpbc_get_selected_locale(booking_id,  wpdev_active_locale );

    if ( booking_id !='' ) {

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action    = 'DELETE_APPROVE';
        var ajax_bk_message     = 'Updating...';
        //var is_send_emeils      = 1;
        var denyreason          = '';
        if (is_send_emeils == 1) {
            if ( jQuery('#is_send_email_for_pending').length ) {
                is_send_emeils = jQuery('#is_send_email_for_pending').attr('checked');
                if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
                else                             {is_send_emeils = 1 ;}                
            }
            if ( jQuery('#denyreason').length )
                denyreason = jQuery('#denyreason').val();
        } else {
            is_send_emeils = 0;
        }

        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({                                           // Start Ajax Sending
            // url: wpdev_ajax_path,
            url: wpbc_ajaxurl, 
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
            // beforeSend: someFunction,
            data:{
                //ajax_action : ajax_type_action,         // Action
                action : ajax_type_action,         // Action
                booking_id : booking_id,                  // ID of Booking  - separator |
                is_send_emeils : is_send_emeils,
                denyreason: denyreason,
                user_id: user_id,
                wpdev_active_locale:wpdev_active_locale,
                wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
            }
        });
        return false;
    }

    return true;
}


// Mark as Read or Unread selected bookings
function mark_read_booking(booking_id, is_read_or_unread, user_id, wpdev_active_locale ) {

    // FixIn: 5.4.5
    wpdev_active_locale = wpbc_get_selected_locale(booking_id,  wpdev_active_locale );

    if ( booking_id !='' ) {

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action    = 'UPDATE_READ_UNREAD';
        var ajax_bk_message     = 'Updating...';

        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({                                           // Start Ajax Sending
            //url: wpdev_ajax_path,
            url: wpbc_ajaxurl, 
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
            // beforeSend: someFunction,
            data:{
                //ajax_action : ajax_type_action,           // Action
                action : ajax_type_action,                  // Action
                booking_id : booking_id,                    // ID of Booking  - separator |
                is_read_or_unread : is_read_or_unread,      // Read: 1, Unread: 0
                user_id: user_id,
                wpdev_active_locale:wpdev_active_locale,
                wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
            }
        });
        return false;
    }

    return true;
}


// Get Selected rows in imported Events list
function get_selected_bookings_id_in_this_list( list_tag, skip_id_length ) {

    var checkedd = jQuery( list_tag + ":checked" );
    var id_for_approve = "";

    // get all IDs
    checkedd.each(function(){
        var id_c = jQuery(this).attr('id');
        id_c = id_c.substr(skip_id_length,id_c.length-skip_id_length);
        id_for_approve += id_c + "|";
    });

    if ( id_for_approve.length > 1 )
        id_for_approve = id_for_approve.substr(0,id_for_approve.length-1);      //delete last "|"

    return id_for_approve ;

}

// Get the list of ID in selected bookings from booking listing
function get_selected_bookings_id_in_booking_listing(){

    var checkedd = jQuery(".booking_list_item_checkbox:checked");
    var id_for_approve = "";

    // get all IDs
    checkedd.each(function(){
        var id_c = jQuery(this).attr('id');
        id_c = id_c.substr(20,id_c.length-20);
        id_for_approve += id_c + "|";
    });

    if ( id_for_approve.length > 1 )
        id_for_approve = id_for_approve.substr(0,id_for_approve.length-1);      //delete last "|"

    return id_for_approve ;
}


function showwidedates_at_admin_side(){
    jQuery('.short_dates_view').addClass('hide_dates_view');
    jQuery('.short_dates_view').removeClass('show_dates_view');
    jQuery('.wide_dates_view').addClass('show_dates_view');
    jQuery('.wide_dates_view').removeClass('hide_dates_view');
    jQuery('#showwidedates').addClass('hide_dates_view');

    jQuery('.showwidedates').addClass('hide_dates_view');
    jQuery('.showshortdates').addClass('show_dates_view');
    jQuery('.showshortdates').removeClass('hide_dates_view');
    jQuery('.showwidedates').removeClass('show_dates_view');
}

function showshortdates_at_admin_side(){
    jQuery('.wide_dates_view').addClass('hide_dates_view');
    jQuery('.wide_dates_view').removeClass('show_dates_view');
    jQuery('.short_dates_view').addClass('show_dates_view');
    jQuery('.short_dates_view').removeClass('hide_dates_view');

    jQuery('.showshortdates').addClass('hide_dates_view');
    jQuery('.showwidedates').addClass('show_dates_view');
    jQuery('.showwidedates').removeClass('hide_dates_view');
    jQuery('.showshortdates').removeClass('show_dates_view');
}



//<![CDATA[
function verify_window_opening(us_id,  window_id ){

        var is_closed = 0;

        if (jQuery('#' + window_id ).hasClass('closed') == true){
            jQuery('#' + window_id ).removeClass('closed');
        } else {
            jQuery('#' + window_id ).addClass('closed');
            is_closed = 1;
        }


        jQuery.ajax({                                           // Start Ajax Sending
                // url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    //ajax_action : 'USER_SAVE_WINDOW_STATE',
                    action : 'USER_SAVE_WINDOW_STATE',
                    user_id: us_id ,
                    window: window_id,
                    is_closed: is_closed,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
        });

}
//]]>


//<![CDATA[
function save_bk_listing_filter(us_id,  filter_name, filter_value ){

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action    = 'SAVE_BK_LISTING_FILTER';
        var ajax_bk_message     = 'Saving...';

        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({
                // url: wpdev_ajax_path,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action,        
                    user_id: us_id ,
                    filter_name: filter_name ,
                    filter_value: filter_value,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 

                }
        });
}
//]]>


//<![CDATA[
function delete_bk_listing_filter(us_id,  filter_name ){

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action    = 'DELETE_BK_LISTING_FILTER';
        var ajax_bk_message     = 'Deleting...';

        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({
                // url: wpdev_ajax_path,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action,
                    user_id: us_id ,
                    filter_name: filter_name,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
        });
}
//]]>

//<![CDATA[
function wpbc_import_gcal_events( us_id
                                , booking_gcal_events_from
                                , booking_gcal_events_until
                                , booking_gcal_events_max
                                , wpbc_booking_resource             
                                ){
            
        var ajax_type_action    = 'WPBC_IMPORT_GCAL_EVENTS';
        var ajax_bk_message     = 'Importing...';
            
        document.getElementById('ajax_working').innerHTML =
        '<div class="updated ajax_message" id="ajax_message">\n\
            <div style="float:left;">'+ajax_bk_message+'</div> \n\
            <div class="wpbc_spin_loader">\n\
                   <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
            </div>\n\
        </div>';

        jQuery.ajax({
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action
                    , user_id: us_id 
                    , booking_gcal_events_from:booking_gcal_events_from 
                    , booking_gcal_events_until:booking_gcal_events_until
                    , booking_gcal_events_max:booking_gcal_events_max
                    , wpbc_booking_resource:wpbc_booking_resource
                    , wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
        });
}
//]]>


////////////////////////////////////////////////////////////////////////////
// Support Functions
////////////////////////////////////////////////////////////////////////////

// Scroll to script
function makeScrollInAdminPanel(object_name) {
     var targetOffset = jQuery( object_name ).offset().top;
     //targetOffset = targetOffset - 50;
     if (targetOffset<0) targetOffset = 0;
     if ( jQuery('#wpadminbar').length > 0 ) targetOffset = targetOffset - 50;
     else  targetOffset = targetOffset - 20;
     jQuery('html,body').animate({scrollTop: targetOffset}, 500);
}


/**
 * Reset of WP Editor or TextArea Content
 * @param {string} editor_textarea_id - ID of element
 * @param {string} editor_textarea_content - Content
 */
function reset_wp_editor_content( editor_textarea_id, editor_textarea_content ) {
    if( typeof tinymce != "undefined" ) {
        var editor = tinymce.get( editor_textarea_id );
        if( editor && editor instanceof tinymce.Editor ) {
            editor.setContent( editor_textarea_content );
            editor.save( { no_events: true } );
        } else {
            jQuery( '#' + editor_textarea_id ).val( editor_textarea_content );
        }
    } else {
        jQuery( '#' + editor_textarea_id ).val( editor_textarea_content );
    }
}