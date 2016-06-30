jQuery(document).ready( function(){   
   if( jQuery('.wpdev-validates-as-time').length > 0 ) {
       jQuery('.wpdev-validates-as-time').attr('alt','time');
       jQuery('.wpdev-validates-as-time').setMask();
   }
});


// Send booking Cacel by visitor
function bookingCancelByVisitor(booking_hash, bk_type, wpdev_active_locale){


    if (booking_hash!='') {


        document.getElementById('submiting' + bk_type).innerHTML =
            '<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>';

        var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
        var ajax_type_action='DELETE_BY_VISITOR';

        jQuery.ajax({                                           // Start Ajax Sending
            // url: wpdev_ajax_path,
            url: wpbc_ajaxurl,
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond_insert' + bk_type).html( data ) ;},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
            // beforeSend: someFunction,
            data:{
                // ajax_action : ajax_type_action,
                action : ajax_type_action,
                booking_hash : booking_hash,
                bk_type : bk_type,
                wpdev_active_locale:wpdev_active_locale,
                wpbc_nonce: document.getElementById('wpbc_nonce_delete'+bk_type).value 
            }
        });
        return false;
    }
    return true;
}


////////////////////////////////////////////////////////////////////////////////

// Set cehckbox in booking form Exclusive on click
function wpdevExclusiveCheckbox(element){

    jQuery('[name="'+element.name+'"]').prop("checked", false);             // Uncheck  all checkboxes with  this name

    element.checked = true;
}

// Set selectbox with multiple selections - Exclusive
function wpdevExclusiveSelectbox(element){

    // Get all selected elements.
    var selectedOptions = jQuery.find('[name="'+element.name+'"] option:selected');

    // Check if we are have more than 1 selection
    if ( selectedOptions.length > 1 ) {

        var ind = selectedOptions[0].index;                                             // Get index of the first  selected element
        jQuery('[name="'+element.name+'"] option').prop("selected", false);             // Uncheck  all checkboxes with  this name
        jQuery('[name="'+element.name+'"] option:eq('+ind+')').prop("selected", true);  // Set the first element selected
    }        
}

////////////////////////////////////////////////////////////////////////////////

function showErrorTimeMessage(my_message, element){
            var element_name = element.name;
            makeScroll( element );
            jQuery("[name='"+ element_name +"']")
                    .css( {'border' : '1px solid red'} )
                    .fadeOut( 350 )
                    .fadeIn( 500 )
                    .animate( {opacity: 1}, 4000 )
                    .animate({border : '1px solid #DFDFDF'},100)
            ;  // mark red border
            jQuery("[name='"+ element_name +"']")
                    .after('<div class="wpdev-help-message alert">'+ my_message +'</div>'); // Show message
            jQuery(".wpdev-help-message")
//                    .css( {'color' : 'red'} )
                    .animate( {opacity: 1}, 10000 )
                    .fadeOut( 2000 );   // hide message
            element.focus();    // make focus to elemnt
            return true;
}


function isValidTimeTextField(timeStr) {
        // Checks if time is in HH:MM AM/PM format.
        // The seconds and AM/PM are optional.

        var timePat = /^(\d{1,2}):(\d{2})(\s?(AM|am|PM|pm))?$/;

        var matchArray = timeStr.match(timePat);
        if (matchArray == null) {
            return false; //("<?php _e('Time is not in a valid format. Use this format HH:MM or HH:MM AM/PM'); ?>");
        }
        var hour = matchArray[1];
        var minute = matchArray[2];
        var ampm = matchArray[4];

        if (ampm=="") {ampm = null}

        if (hour < 0  || hour > 23) {
            return  false; //("<?php _e('Hour must be between 1 and 12. (or 0 and 23 for military time)'); ?>");
        }
        if  (hour > 12 && ampm != null) {
            return  false; //("<?php _e('You can not specify AM or PM for military time.'); ?>");
        }
        if (minute<0 || minute > 59) {
            return  false; //("<?php _e('Minute must be between 0 and 59.'); ?>");
        }
        return true;
    }


function is_this_time_selections_not_available(bk_type,  form_elements ) {
    
    if (location.href.indexOf('wpdev-booking.phpwpdev-booking-reservation')>0)  return false; //Skip this checking if we are in the Admin  panel at Add booking page
    
    var count = form_elements.length;
    var start_time = false;
    var end_time   = false;
    var element; var element_start=false; var element_end=false; var element_duration=false; var element_rangetime=false;
    var duration = false;


    // Get Start and End time from this form, if they exist.
    for (var i=0; i<count; i++)   {

        element = form_elements[i];
        if (element.name != undefined ) {
            var my_element = element.name; //.toString();
            if (my_element.indexOf('rangetime') !== -1 ){                         // Range - time selectbox
                   var my_rangetime = element.value.split('-');
                   start_time = my_rangetime[0].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                   end_time   = my_rangetime[1].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                   element_rangetime  = element;
            }

            if ( (my_element.indexOf('durationtime') !== -1 )   ){                // Duration
                    duration = element.value;
                    element_duration = element;
            }

            if (my_element.indexOf('starttime') !== -1 ) {                        // Start Time
                    start_time    = element.value;
                    element_start = element;
            }

            if (my_element.indexOf('endtime') !== -1 )   {                        // End Time
                    end_time     =  element.value;
                    element_end  = element;
            }
        }
    } // End form elemnts loop





    // Duration get Values
    if ( (duration !== false) && (start_time !== false) ) {  // we have Duration and Start time so  try to get End time

        var mylocalstarttime = start_time.split(':');
        var d = new Date(1980, 1, 1, mylocalstarttime[0], mylocalstarttime[1], 0);

        var my_duration = duration.split(':');
        my_duration = my_duration[0]*60*60*1000 + my_duration[1]*60*1000;
        d.setTime(d.getTime() + my_duration);

        var my_hours   = d.getHours();   if (my_hours < 10)   my_hours =   '0' + ( my_hours + '' );
        var my_minutes = d.getMinutes(); if (my_minutes < 10) my_minutes = '0' + ( my_minutes + '' );

        // We are get end time
        end_time = ( my_hours + '' ) + ':' + ( my_minutes + '' ) ;
        if (end_time == '00:00') end_time = '23:59';
    }



    if ( (start_time === false) || (end_time === false) ) {                     // We do not have Start or End time or Both of them, so do not check it

           return false ;

    } else {

           var valid_time = true;
           if ( (start_time == '') || (end_time == '') ) valid_time = false;

           if (! isValidTimeTextField(start_time) )  valid_time = false;
           if (! isValidTimeTextField(end_time  ) )  valid_time = false;

           if( valid_time === true )
               if (
                     ( typeof( checkRecurentTimeInside ) == 'function' )  &&
                     (typeof( is_booking_recurrent_time) !== 'undefined') &&
                     (is_booking_recurrent_time == true)
                   ) {                                                                // Recheck Time here !!!
                       valid_time = checkRecurentTimeInside( [ start_time , end_time ],  bk_type );
               } else {

                       if( typeof( checkTimeInside ) == 'function' ) { valid_time = checkTimeInside( start_time , true, bk_type) ; }

                       if( valid_time === true ) {
                           if(typeof( checkTimeInside ) == 'function') {valid_time = checkTimeInside( end_time , false, bk_type) ;}
                       }
               }

           if( valid_time !== true ) {


               if (element_rangetime !== false ) showErrorTimeMessage(message_rangetime_error,    element_rangetime);
               if (element_duration !== false )  showErrorTimeMessage(message_durationtime_error, element_duration);
               if (element_start !== false )     showErrorTimeMessage(message_starttime_error,    element_start);
               if (element_end !== false )       showErrorTimeMessage(message_endtime_error,      element_end);

               return true;
               
           } else  {
               return false;
           }

    }


}


function wpdev_add_remark(id, text){
    document.getElementById("remark_row" + id ).style.display="none";

    var ajax_bk_message = 'Adding remark...';

    document.getElementById('ajax_working').innerHTML =
    '<div class="updated ajax_message" id="ajax_message">\n\
        <div style="float:left;">'+ajax_bk_message+'</div> \n\
        <div class="wpbc_spin_loader">\n\
               <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
        </div>\n\
    </div>';

    var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;
    
    jQuery.ajax({                                           // Start Ajax Sending
        // url: wpdev_ajax_path,
        url: wpbc_ajaxurl,        
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus; alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText); if (XMLHttpRequest.status == 500) { alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error'); } },
        // beforeSend: someFunction,
        data:{
            // ajax_action : 'UPDATE_REMARK',
            action : 'UPDATE_REMARK',
            remark_id : id,
            remark_text : text,
            wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
        }
    });
    return false;

}


function wpdev_change_bk_resource( booking_id, resource_id ){
    document.getElementById("changing_bk_res_in_booking" + booking_id ).style.display="none";

    var ajax_bk_message = 'Changing resource...';

    document.getElementById('ajax_working').innerHTML =
    '<div class="updated ajax_message" id="ajax_message">\n\
        <div style="float:left;">'+ajax_bk_message+'</div> \n\
        <div class="wpbc_spin_loader">\n\
               <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
        </div>\n\
    </div>';

    var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;

    var is_send_emeils = 1;                                                     //FixIn: 6.1.0.2
    if ( jQuery('#is_send_email_for_pending').length ) {
        is_send_emeils = jQuery('#is_send_email_for_pending').attr('checked');
        if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
        else                             {is_send_emeils = 1 ;}                
    }


    jQuery.ajax({                                           // Start Ajax Sending
        // url: wpdev_ajax_path,
        url: wpbc_ajaxurl,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus; alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText); if (XMLHttpRequest.status == 500) { alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error'); } },
        // beforeSend: someFunction,
        data:{
            // ajax_action : 'UPDATE_BK_RESOURCE_4_BOOKING',
            action : 'UPDATE_BK_RESOURCE_4_BOOKING',
            booking_id : booking_id,
            resource_id : resource_id,
            is_send_emeils:is_send_emeils,                                      //FixIn: 6.1.0.2
            wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value
        }
    });
    return false;

}


//FixIn: 5.4.5.1
/** Duplicate booking
 * 
 * @param {type} booking_id - Id of booking to  duplicate
 * @param {type} resource_id - destination  booking resource
 * @returns {Boolean}
 */
function wpbc_duplicate_booking_to_resource( booking_id, resource_id ){
    document.getElementById("changing_bk_res_in_booking" + booking_id ).style.display="none";

    var ajax_bk_message = 'Duplicate of booking ...';

    var wpdev_active_locale = wpbc_get_selected_locale(booking_id,  '' );

    document.getElementById('ajax_working').innerHTML =
    '<div class="updated ajax_message" id="ajax_message">\n\
        <div style="float:left;">'+ajax_bk_message+'</div> \n\
        <div class="wpbc_spin_loader">\n\
               <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
        </div>\n\
    </div>';

    var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;

    jQuery.ajax({                                           // Start Ajax Sending
        // url: wpdev_ajax_path,
        url: wpbc_ajaxurl,
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
        error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus; alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText); if (XMLHttpRequest.status == 500) { alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error'); } },
        // beforeSend: someFunction,
        data:{
            // ajax_action : 'UPDATE_BK_RESOURCE_4_BOOKING',
            action : 'DUPLICATE_BOOKING_TO_OTHER_RESOURCE',
            booking_id : booking_id,
            resource_id : resource_id,
            wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value,
            wpdev_active_locale: wpdev_active_locale,
        }
    });
    return false;

}


//Print
function print_booking_listing(){
    jQuery("#print_loyout_content").html( jQuery("#booking_print_loyout").html()  ) ;
    
    jQuery(".modal-footer").show();
    var selected_id = get_selected_bookings_id_in_booking_listing();
    // Show only selected
    if ( selected_id !='' ) {
        selected_id = selected_id.split('|');
        jQuery(".wpbc_print_rows").hide();
        for (var i = 0; i < selected_id.length; ++i) {
            jQuery("#wpbc_print_row" + selected_id[i] ).show();
        }
    } else {    // Show all
        jQuery(".wpbc_print_rows").show();   
    }
    jQuery("#printLoyoutModal").modal("show");
}


jQuery.fn.print = function(){
	// NOTE: We are trimming the jQuery collection down to the
	// first element in the collection.
	if (this.size() > 1){
		this.eq( 0 ).print();
		return;
	} else if (!this.size()){
		return;
	}

	// ASSERT: At this point, we know that the current jQuery
	// collection (as defined by THIS), contains only one
	// printable element.

	// Create a random name for the print frame.
	var strFrameName = ("printer-" + (new Date()).getTime());

	// Create an iFrame with the new name.
	var jFrame = jQuery( "<iframe name='" + strFrameName + "'>" );

	// Hide the frame (sort of) and attach to the body.
	jFrame
		.css( "width", "1px" )
		.css( "height", "1px" )
		.css( "position", "absolute" )
		.css( "left", "-9999px" )
		.appendTo( jQuery( "body:first" ) )
	;

	// Get a FRAMES reference to the new frame.
	var objFrame = window.frames[ strFrameName ];

	// Get a reference to the DOM in the new frame.
	var objDoc = objFrame.document;

	// Grab all the style tags and copy to the new
	// document so that we capture look and feel of
	// the current document.

	// Create a temp document DIV to hold the style tags.
	// This is the only way I could find to get the style
	// tags into IE.
	var jStyleDiv = jQuery( "<div>" ).append(
		jQuery( "style" ).clone()
		);

	// Write the HTML for the document. In this, we will
	// write out the HTML of the current element.
	objDoc.open();
	objDoc.write( "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">" );
	objDoc.write( "<html>" );

	objDoc.write( "<head>" );
	objDoc.write( "<title>" );
	objDoc.write( document.title );
	objDoc.write( "</title>" );

        // objDoc.write( jStyleDiv.html() );
        objDoc.write(  "<link href='" + wpdev_bk_plugin_url + "/interface/bs/css/bs.min.css' rel='stylesheet' type='text/css' />" );
        objDoc.write(  "<link href='" + wpdev_bk_plugin_url + "/css/admin.css' rel='stylesheet' type='text/css' />" );

	objDoc.write( "</head>" );
        objDoc.write( "<body>" );
	objDoc.write( this.html() );
	objDoc.write( "</body>" );
	objDoc.write( "</html>" );
	objDoc.close();

	// Print the document.
	objFrame.focus();
	objFrame.print();

	// Have the frame remove itself in about a minute so that
	// we don't build up too many of these frames.
	setTimeout(
		function(){
			jFrame.remove();
		},
		(60 * 1000)
		);
}


// Export 
var csv_content;
//<![CDATA[
function export_booking_listing(export_type, wpdev_active_locale){

        var wpdev_ajax_path     = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;
        var ajax_type_action    = 'EXPORT_BOOKINGS_TO_CSV';
        var ajax_bk_message     = 'Start exporting...';
        var bk_request_params     = document.getElementById('bk_request_params').value;

        
        // Export only selected,  if making export not all bookings
        var selected_id = get_selected_bookings_id_in_booking_listing();
        if (export_type != 'page') {
            selected_id = '';
        }

            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';


            // Ajax POST here
            jQuery.ajax({                                           // Start Ajax Sending
                // url: wpdev_ajax_path,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){  if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                error:function (XMLHttpRequest, textStatus, errorThrown){ window.status = 'Ajax sending Error status:'+ textStatus; alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText); if (XMLHttpRequest.status == 500) { alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error'); } },
                // beforeSend: someFunction,
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action,
                    csv_data:bk_request_params,
                    export_type:export_type,
                    selected_id:selected_id,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                    ,wpdev_active_locale:wpdev_active_locale
                }
            });
}
//]]>



function reset_to_def_from(type) {
    // document.getElementById('booking_form').value = reset_booking_form(type);
    var editor_textarea_id = 'booking_form';
    var editor_textarea_content = reset_booking_form(type);
    
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

function reset_to_def_from_show(type) {
    // document.getElementById('booking_form_show').value = reset_booking_content_form(type);
    var editor_textarea_id = 'booking_form_show';
    var editor_textarea_content = reset_booking_content_form(type);
    
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


function reset_booking_form(form_type) {
    var form_content = '';
    
    if (form_type == 'times'){
           form_content = '';
           form_content +='[calendar] \n'; 
           form_content +='<div class="times-form"> \n';
           form_content +='     <p>Select Times:<br />[select* rangetime multiple "10:00 AM - 12:00 PM@@10:00 - 12:00" "12:00 PM - 02:00 PM@@12:00 - 14:00" "02:00 PM - 04:00 PM@@14:00 - 16:00" "04:00 PM - 06:00 PM@@16:00 - 18:00" "06:00 PM - 08:00 PM@@18:00 - 20:00"]</p> \n';
           form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
           form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n';
           form_content +='     <p>Email (required):<br />[email* email] </p>   \n';
           form_content +='     <p>Phone:<br />[text phone] </p> \n';
           form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
           form_content +='     <p>Details:<br /> [textarea details] </p> \n';
           form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p>\n';
           form_content +='     <p>[captcha]</p> \n';
           form_content +='     <p>[submit class:btn "Send"]</p> \n';
           form_content +='</div> \n';        
    }

    if (form_type == 'timesweek'){
           form_content = '';
           form_content +='[calendar] \n'; 
           form_content +='<div class="times-form"> \n';
           form_content +='<p> \n';
           form_content +='    [condition name="weekday-condition" type="weekday" value="*"] \n';
           form_content +='        Select Time Slot:<br/> [select rangetime multiple "10:00 - 12:00" "12:00 - 14:00" "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00"] \n';
           form_content +='    [/condition] \n';
           form_content +='    [condition name="weekday-condition" type="weekday" value="1,2"] \n';
           form_content +='        Select Time Slot available on Monday, Tuesday:<br/>    [select rangetime multiple "10:00 - 12:00" "12:00 - 14:00"] \n';
           form_content +='    [/condition] \n';
           form_content +='    [condition name="weekday-condition" type="weekday" value="3,4"] \n';
           form_content +='        Select Time Slot available on Wednesday, Thursday:<br/>  [select rangetime multiple "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00"] \n';
           form_content +='    [/condition] \n';
           form_content +='    [condition name="weekday-condition" type="weekday" value="5,6,0"] \n';
           form_content +='        Select Time Slot available on Friday, Saturday, Sunday:<br/> [select rangetime multiple "12:00 - 14:00" "14:00 - 16:00" "16:00 - 18:00"] \n';
           form_content +='    [/condition] \n';
           form_content +='</p> \n';
           form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
           form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n';
           form_content +='     <p>Email (required):<br />[email* email] </p>   \n';
           form_content +='     <p>Phone:<br />[text phone] </p> \n';
           form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
           form_content +='     <p>Details:<br /> [textarea details] </p> \n';
           form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p>\n';
           form_content +='     <p>[captcha]</p> \n';
           form_content +='     <p>[submit class:btn "Send"]</p> \n';
           form_content +='</div> \n';        
    }

    if (form_type == 'hints'){
           form_content = '';
           form_content +='[calendar] \n'; 
           form_content +='<div class="standard-form"> \n';
           form_content +='     <div class="form-hints"> \n';
           form_content +='          Dates:[selected_short_timedates_hint]  ([nights_number_hint] - night(s))<br><br> \n';
           form_content +='          Full cost of the booking: [cost_hint] <br> \n';
           form_content +='     </div><hr/> \n';
           form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
           form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n';
           form_content +='     <p>Email (required):<br />[email* email] </p>   \n';
           form_content +='     <p>Phone:<br />[text phone] </p> \n';
           form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
           form_content +='     <p>Details:<br /> [textarea details] </p> \n';
           form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p>\n';
           form_content +='     <p>[captcha]</p> \n';
           form_content +='     <p>[submit class:btn "Send"]</p> \n';
           form_content +='</div> \n';        
    }

    if (form_type == 'payment')  {
        form_content = '';
        form_content +='[calendar] \n';
        form_content +='<div class="payment-form"> \n';
        form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
        form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n';
        form_content +='     <p>Email (required):<br />[email* email] </p> \n';
        form_content +='     <p>Phone:<br />[text phone] </p> \n';
        form_content +='     <p>Address (required):<br />  [text* address] </p> \n';  
        form_content +='     <p>City (required):<br />  [text* city] </p> \n';
        form_content +='     <p>Post code (required):<br />  [text* postcode] </p> \n';  
        form_content +='     <p>Country (required):<br />  [country] </p> \n';
        form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
        form_content +='     <p>Details:<br /> [textarea details] </p> \n';
        form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p> \n';
        form_content +='     <p>[captcha]</p> \n';
        form_content +='     <p>[submit class:btn "Send"]</p> \n';
        form_content +='</div> \n';
    }
      
    if (form_type == 'wizard')  {
        form_content = '';          
        form_content +='<div class="bk_calendar_step"> \n';
        form_content +='     [calendar] \n';
        form_content +='     <a href="javascript:void(0)" onclick="javascript:bk_calendar_step_click();" class="btn">Continue to step 2</a> \n';
        form_content +='</div> \n\n';
        form_content +='<div class="bk_form_step" style="display:none;clear:both;"> \n';
        form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
        form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n'; 
        form_content +='     <p>Email (required):<br />[email* email] </p> \n';
        form_content +='     <p>Phone:<br />[text phone] </p> \n';
        form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
        form_content +='     <p>Details:<br /> [textarea details] </p> \n';
        form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p> \n';
        form_content +='     <p>[captcha]</p> \n';
        form_content +='     <hr/> \n';
        form_content +='    <div style="text-align:right;">[submit class:btn "Send"] <a href="javascript:void(0)" onclick="javascript:bk_form_step_click();" class="btn">Back to step 1</a></div> \n';
        form_content +='</div> \n\n';
        form_content +='<script type="text/javascript"> \n';
        form_content +='     function bk_calendar_step_click(){ \n';
        form_content +='          jQuery(".bk_calendar_step" ).css({"display":"none"}); \n';
        form_content +='          jQuery(".bk_form_step" ).css({"display":"block"}); \n';
        form_content +='     } \n';
        form_content +='     function bk_form_step_click(){ \n';
        form_content +='          jQuery(".bk_calendar_step" ).css({"display":"block"}); \n';
        form_content +='          jQuery(".bk_form_step" ).css({"display":"none"}); \n';
        form_content +='     } \n';
        form_content +='</script> \n';
    }

    if (form_type == '2collumns')  { // 2 collumns form
        form_content = '';
        form_content +='<div style="float:left;margin-right:10px;   " >  [calendar]  </div> \n';
        form_content +='<div style="float:left;" > \n';
        form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
        form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n';
        form_content +='     <p>Email (required):<br />[email* email] </p> \n';
        form_content +='     <p>Phone:<br />[text phone] </p> \n';
        form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"]  Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
        form_content +='</div> \n';
        form_content +='<div  style="clear:both"> \n';
        form_content +='     <p>Details:<br /> [textarea details 100x5 class:span6]</p> \n';
        form_content +='      [captcha]\n';
        form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"]</p> \n';
        form_content +='     <hr/><p>[submit class:btn "Send"] </p> \n';
        form_content +='</div> \n';
    }

    if (form_content == '') { // Default Form.
           form_content = '';
           form_content +='[calendar] \n'; 
           form_content +='<div class="standard-form"> \n';
           form_content +='     <p>First Name (required):<br />[text* name] </p> \n';
           form_content +='     <p>Last Name (required):<br />[text* secondname] </p> \n';
           form_content +='     <p>Email (required):<br />[email* email] </p>   \n';
           form_content +='     <p>Phone:<br />[text phone] </p> \n';
           form_content +='     <p>Adults:  [select visitors class:span1 "1" "2" "3" "4"] Children: [select children class:span1 "0" "1" "2" "3"]</p> \n';
           form_content +='     <p>Details:<br /> [textarea details] </p> \n';
           form_content +='     <p>[checkbox* term_and_condition use_label_element "I Accept term and conditions"] </p>\n';
           form_content +='     <p>[captcha]</p> \n';
           form_content +='     <p>[submit class:btn "Send"]</p> \n';
           form_content +='</div> \n';
    }
    
    return form_content;
}

function reset_booking_content_form(form_type){
    var form_content = '';
        
    if (form_type == 'payment')  {
        form_content = '';
        form_content += '<div class="payment-content-form"> \n';
        form_content += '<strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/> \n';
        form_content += '<strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/> \n';
        form_content += '<strong>Email</strong>:<span class="fieldvalue">[email]</span><br/> \n';
        form_content += '<strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/> \n';
        form_content += '<strong>Address</strong>:<span class="fieldvalue">[address]</span><br/> \n';
        form_content += '<strong>City</strong>:<span class="fieldvalue">[city]</span><br/> \n';
        form_content += '<strong>Post code</strong>:<span class="fieldvalue">[postcode]</span><br/> \n';
        form_content += '<strong>Country</strong>:<span class="fieldvalue">[country]</span><br/> \n';
        form_content += '<strong>Adults</strong>:<span class="fieldvalue"> [visitors]</span><br/> \n';
        form_content += '<strong>Children</strong>:<span class="fieldvalue"> [children]</span><br/> \n';
        form_content += '<strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span> \n';
        form_content += '</div> \n';
    }

    if ( (form_type == 'times') || ( form_type == 'timesweek') ){
        form_content = '';
        form_content +='<div class="times-content-form"> \n';
        form_content +='<strong>Times</strong>:<span class="fieldvalue">[rangetime]</span><br/> \n';
        form_content +='<strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/> \n';
        form_content +='<strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/> \n';
        form_content +='<strong>Email</strong>:<span class="fieldvalue">[email]</span><br/> \n';
        form_content +='<strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/> \n';
        form_content +='<strong>Adults</strong>:<span class="fieldvalue"> [visitors]</span><br/> \n';
        form_content +='<strong>Children</strong>:<span class="fieldvalue"> [children]</span><br/> \n';
        form_content +='<strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span> \n';
        form_content +='</div> \n';
    }

    if (  (form_type == 'wizard') || (form_type == '2collumns') || (form_content == 'hints') || (form_content == '') ){
        form_content = '';
        form_content +='<div class="standard-content-form"> \n';
        form_content +='<strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/> \n';
        form_content +='<strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/> \n';
        form_content +='<strong>Email</strong>:<span class="fieldvalue">[email]</span><br/> \n';
        form_content +='<strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/> \n';
        form_content +='<strong>Adults</strong>:<span class="fieldvalue"> [visitors]</span><br/> \n';
        form_content +='<strong>Children</strong>:<span class="fieldvalue"> [children]</span><br/> \n';
        form_content +='<strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span> \n';
        form_content +='</div> \n';
    }
    return form_content;
}



function wpdevbk_select_days_in_calendar( bk_type, selected_dates ){


    clearTimeout(timeout_DSwindow);
    
    var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type)); 
    inst.dates = [];  
    var original_array = []; var date;

    var bk_inputing = document.getElementById('date_booking' + bk_type);
    var bk_distinct_dates = [];
    

    for (var i=0; i< selected_dates.length ; i++)   {
        
                var dta = selected_dates[i];
                
                date=new Date();
                date.setFullYear( dta[0] , (dta[1]-1) , dta[2] );    // get date
                original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, date, null))  ); //add date

                if ( !  wpdev_in_array(bk_distinct_dates, dta[2]+'.'+dta[1]+'.'+dta[0] ) ) 
                    bk_distinct_dates.push( dta[2]+'.'+dta[1]+'.'+dta[0] );                
    }

    for(var j=0; j < original_array.length ; j++) {       //loop array of dates
        if (original_array[j] != -1) inst.dates.push(original_array[j]);
    }
    dateStr = (inst.dates.length == 0 ? '' : jQuery.datepick._formatDate(inst, inst.dates[0])); // Get first date
    for ( i = 1; i < inst.dates.length; i++)
         dateStr += jQuery.datepick._get(inst, 'multiSeparator') +  jQuery.datepick._formatDate(inst, inst.dates[i]);  // Gathering all dates
    jQuery('#date_booking' + bk_type).val(dateStr); // Fill the input box

    if (original_array.length>0) { // Set showing of start month
        inst.cursorDate = original_array[0];
        inst.drawMonth = inst.cursorDate.getMonth();
        inst.drawYear = inst.cursorDate.getFullYear();
    }

    // Update calendar
    jQuery.datepick._notifyChange(inst);
    jQuery.datepick._adjustInstDate(inst);
    jQuery.datepick._showDate(inst);
    jQuery.datepick._updateDatepick(inst);

    if (bk_inputing != null)
        bk_inputing.value = bk_distinct_dates.join(', ');

    
    if(typeof( check_condition_sections_in_bkform ) == 'function') {check_condition_sections_in_bkform( jQuery('#date_booking' + bk_type).val() , bk_type);}
    
    if(typeof( bkDisableBookedTimeSlots ) == 'function') { bkDisableBookedTimeSlots( jQuery('#date_booking' + bk_type).val() , bk_type); } /* HERE WE WILL DISABLE ALL OPTIONS IN RANGE TIME INTERVALS FOR SINGLE DAYS SELECTIONS FOR THAT DAYS WHERE HOURS ALREADY BOOKED */
    
    if(typeof( showCostHintInsideBkForm ) == 'function') { showCostHintInsideBkForm(bk_type); }
}


function setSelectBoxByValue(el_id, el_value) {

    for (var i=0; i < document.getElementById(el_id).length; i++) {
        if (document.getElementById(el_id)[i].value == el_value) {
            document.getElementById(el_id)[i].selected = true;
        }
    }
}