var list_booking_id_for_show = [];
var prices_per_day = [];
var cost_curency = '';
var wpbc_curency_symbol = '$';

var wpdev_bk_weekday_conditions_for_range_selection = []; 
var wpdev_bk_seasons_conditions_for_range_selection = []; 
var wpdev_bk_seasons_conditions_for_start_day = []; 
var bk_2clicks_mode_days_selection__saved_variables = [];                       // Saved initial values of varibales: bk_2clicks_mode_days_specific , bk_2clicks_mode_days_min, bk_2clicks_mode_days_max     

function addBKForm(param){
    document.getElementById('bk_form_plus').style.display='none';
    document.getElementById('bk_form_addbutton').style.display='block';
    setTimeout(function ( ) {
                jQuery('#booking_form_new_name').focus();
                }
                ,100);

}


function delete_bk_form( form_name, user_id ){
    var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;

    //Ajax adding new type to the DB
    document.getElementById('ajax_working').innerHTML =
    '<div class="updated ajax_message" id="ajax_message" >\n\
        <div style="float:left;"> Deleting... </div> \n\
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
            // ajax_action : 'DELETE_BK_FORM',
            action : 'DELETE_BK_FORM',        
            formname : form_name,
            user_id: user_id,
            wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
        }
    });
    return false;
}



function changeBookingFormForAdvancedCost(selectObj){
    var idx = selectObj.selectedIndex;     
    var my_form = selectObj.options[idx].value;
     
    jQuery('.wpdev_forms_div').hide();
    jQuery( '#' + my_form ).show();
}

function changeBookingForm(selectObj){

     var idx = selectObj.selectedIndex;     
     var my_form = selectObj.options[idx].value;

     var loc = location.href;
     if (loc.substr((loc.length-1),1)=='#') {
         loc = loc.substr(0,(loc.length-1) );
     }
          
     if ( loc.indexOf('booking_form=') == -1 ) {
        loc = loc + '&booking_form=' +my_form;}
     else { // Alredy have this paremeter at URL
         var start = loc.indexOf('&booking_form=');
         var fin = loc.indexOf('&', (start+15));
         if (fin == -1) {loc = loc.substr(0,start) + '&booking_form=' +my_form;} // at the end of row
         else { // at the middle of the row
              var loc1 = loc.substr(0,start) + '&booking_form=' +my_form;//alert(loc)
              loc = loc1 + loc.substr(fin);
         }
     }
     location.href = loc;

}


function changeFilter(selectObj){
     var idx = selectObj.selectedIndex;
     // get the value of the selected option
     var which = selectObj.options[idx].value;
     var loc = location.href;
     if ( loc.indexOf('sybtypefilter=') == -1 ) {
        loc = location.href + '&sybtypefilter=' +which;}
     else { // Alredy have this paremeter at URL
         var start = loc.indexOf('&sybtypefilter=');
         var fin = loc.indexOf('&', (start+15));
         if (fin == -1) {loc = loc.substr(0,start) + '&sybtypefilter=' +which;} // at the end of row
         else { // at the middle of the row
              var loc1 = loc.substr(0,start) + '&sybtypefilter=' +which;//alert(loc)
              loc = loc1 + loc.substr(fin);
         }
     }
     location.href = loc;
}


function filterBookingRowsApply(){
    //  alert(list_booking_id_for_show);
    hide_bk_rows = [];
    for(var i=0; i<list_booking_id_for_show.length;i++){
        if (list_booking_id_for_show[i] == 'hide') {
            hide_bk_rows[hide_bk_rows.length] = 'booking_row'+i;
            jQuery('#booking_appr_'+ i).removeClass('booking_appr0');
            jQuery('#booking_appr_'+ i).removeClass('booking_appr1');
            jQuery('#booking_row'+i).hide();
        }
        // alert(i + '  ' + list_booking_id_for_show[i])
    }
    // alert(hide_bk_rows);
}

if (location.href.indexOf( 'sybtypefilter=') > 0 ) jQuery(document).ready(filterBookingRowsApply);

function setavailabilitycontent(contnt){
    document.getElementById('selectword').innerHTML = contnt;
}


function is_this_day_available( date, bk_type){  //TODO continue here according time

                    function in_array(what, where) {
                        var a=false;
                        for(var i=0; i<where.length; i++) {
                            if(what == where[i]) {
                                a=true;
                                break;
                            }
                        }
                        return a;
                    }

                    var filters_cnt = avalaibility_filters[ bk_type ].length;
                    var filter_week_days = [];
                    var filter_days = [];
                    var filter_monthes = [];
                    var filter_years = [];

                    var d_w = date.getDay();
                    var d_m = ( date.getMonth()+1 );
                    var d_d = date.getDate();
                    var d_y = date.getFullYear();
//alert(date+ ' ' +d_w+ ' ' +d_m+ ' ' +d_d+ ' ' +d_y);
                    var is_day_inside_filters = 0;

                    var filter_id = 0;                                          // FixIn: 6.0.1.8

                    for(var k=0; k<filters_cnt; k++ ) {

                        if ( ((typeof(avalaibility_filters[ bk_type ][k][0]) == 'object')) && (avalaibility_filters[ bk_type ][k][0][0] === "2.0")) { // Version 2.0 of Filters

                            if ( avalaibility_filters[ bk_type ][k].length > 2 )        // FixIn: 6.0.1.8
                                filter_id = avalaibility_filters[ bk_type ][k][2];

                            is_day_inside_filters = '';
                            filter_days = avalaibility_filters[ bk_type ][k][1];
                            if ( in_array( (d_y +'-'+ d_m +'-'+ d_d) , filter_days ) ) {
                                is_day_inside_filters = 'week day month year ';
                                break;
                            }


                        } else {
                            filter_week_days = avalaibility_filters[ bk_type ][k][0];
                            filter_days = avalaibility_filters[ bk_type ][k][1];
                            filter_monthes = avalaibility_filters[ bk_type ][k][2];
                            filter_years = avalaibility_filters[ bk_type ][k][3];

                            if ( avalaibility_filters[ bk_type ][k].length > 4 )        // FixIn: 6.0.1.8
                                filter_id = avalaibility_filters[ bk_type ][k][4];


                            is_day_inside_filters = '';
                            if ( in_array( d_w , filter_week_days ) ) {is_day_inside_filters += 'week ';}
                            if ( in_array( d_d , filter_days ) )      {is_day_inside_filters += 'day ';}
                            if ( in_array( d_m , filter_monthes ) )   {is_day_inside_filters += 'month ';}
                            if ( in_array( d_y , filter_years ) )     {is_day_inside_filters += 'year ';}

                            if (is_day_inside_filters == 'week day month year ') {break;} // the days is apply to filter (apply to week days monthes and years of filter)
                        }
                    }

                    if (is_day_inside_filters == 'week day month year ') {is_day_inside_filters = true;} else {is_day_inside_filters = false;}

                    var is_this_day_available = true;

                    if (is_day_inside_filters) {
                        if ( is_all_days_available[ bk_type ] ) is_this_day_available = false;
                        else                                    is_this_day_available = true;
                    } else {
                        if ( is_all_days_available[ bk_type ] ) is_this_day_available = true;
                        else                                    is_this_day_available = false;
                    }

                    // return is_this_day_available;                                        
                    return [ is_this_day_available, filter_id ];                //FixIn: 6.0.1.8
}



// Time availability
function check_global_time_availability(date, bk_type){

    if (typeof( global_avalaibility_times[bk_type] ) !== 'undefined'){
/*
        var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
        if (typeof( date2approve[bk_type][ class_day ] ) == 'undefined'){  date2approve[bk_type][ class_day ] = []; }

        for (var i=0;i<global_avalaibility_times[bk_type].length;i++) {
            date2approve[bk_type][ class_day ][ date2approve[bk_type][class_day].length ] = [(date.getMonth()+1), date.getDate(), date.getFullYear(),
            global_avalaibility_times[bk_type][i][0][0], global_avalaibility_times[bk_type][i][0][1], 1];
            date2approve[bk_type][ class_day ][ date2approve[bk_type][class_day].length ] = [(date.getMonth()+1), date.getDate(), date.getFullYear(),
            global_avalaibility_times[bk_type][i][1][0], global_avalaibility_times[bk_type][i][1][1], 2];
        }/**/
    }

}

// Time availability
function hover_day_check_global_time_availability( date, bk_type ,times_array ) {
   new_times_array = times_array;
   if(typeof( global_avalaibility_times[ bk_type ]) !== 'undefined') {

       var td_class =  (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();

       for (var iii=0;iii<global_avalaibility_times[bk_type].length;iii++) {

            h = global_avalaibility_times[bk_type][iii][0][0];if (h < 10) h = '0' + h;if (h == 0) h = '00';
            m = global_avalaibility_times[bk_type][iii][0][1];if (m < 10) m = '0' + m;if (m == 0) m = '00';
            s = '01';
            times_array[ times_array.length ] = [h,m,s];

            h = global_avalaibility_times[bk_type][iii][1][0];if (h < 10) h = '0' + h;if (h == 0) h = '00';
            m = global_avalaibility_times[bk_type][iii][1][1];if (m < 10) m = '0' + m;if (m == 0) m = '00';
            s = '02';

            times_array[ times_array.length ] = [h,m,s];
       }

       times_array.sort();
    //   alert(times_array);
       var new_times_array=[];
       previos_is_start_or_end_time = 0;
       for (iii=0;iii<times_array.length;iii++) {
           if (( previos_is_start_or_end_time != parseInt(times_array[iii][2]) ) ) {
            previos_is_start_or_end_time = parseInt(times_array[iii][2]);
            new_times_array[new_times_array.length] = times_array[iii];
           } else if ( ( parseInt(previos_is_start_or_end_time) == 2 ) ) {
            previos_is_start_or_end_time = parseInt(times_array[iii][2]);
            new_times_array[new_times_array.length-1] = times_array[iii];
           }
       }
    //   alert(new_times_array);
   }

   return new_times_array;
}

// Time availability
// check availability of entered time by visitor according global available time only
var global_start_time_checking = false;
function check_entered_time_to_global_availability_time(mytime, is_start_time, bk_type){

       if ( typeof(global_avalaibility_times[bk_type]) !== 'undefined' ) {
     //      mytime
           var times_array = [ mytime.split(":")  ];
           times_array[0][2] = 'check';

           if (is_start_time) {global_start_time_checking = times_array[0];global_start_time_checking[2] = 'starttime';}
           else {times_array[1] = [global_start_time_checking[0],global_start_time_checking[1],global_start_time_checking[2]];global_start_time_checking = false;}

           for (var iii=0;iii<global_avalaibility_times[bk_type].length;iii++) {
                //Start time
                h = global_avalaibility_times[bk_type][iii][0][0];if (h < 10) h = '0' + h;if (h == 0) h = '00';
                m = global_avalaibility_times[bk_type][iii][0][1];if (m < 10) m = '0' + m;if (m == 0) m = '00';
                times_array[ times_array.length ] = [h,m,true];
                //End Time
                h = global_avalaibility_times[bk_type][iii][1][0];if (h < 10) h = '0' + h;if (h == 0) h = '00';
                m = global_avalaibility_times[bk_type][iii][1][1];if (m < 10) m = '0' + m;if (m == 0) m = '00';
                times_array[ times_array.length ] = [h,m,false];
           }
           times_array.sort();

           var is_previos_time_start = false;
           for ( iii=0;iii<times_array.length;iii++) {
               if (is_start_time) { // START TIME
                   if (times_array[iii][2] == 'check') {
                        // check  here
                        if (is_previos_time_start) return false; // Wrong Time
                        else                       return true; // Good  Time
                   }
               } else { // END TIME //during the same day
                   if (times_array[iii][2] == 'check') {
                        // check  here
                        if (is_previos_time_start == 'starttime') return true; // Good
                        else                                      return false; // Wrong
                   }
               }
               is_previos_time_start = times_array[iii][2];
           }
       }
       return true; // Good  Time
}



function getDayPrice4Show(bk_type, tooltip_time, td_class){

    if (is_show_cost_in_tooltips) {

       if(typeof(  prices_per_day[bk_type] ) !== 'undefined')
           if(typeof(  prices_per_day[bk_type][td_class] ) !== 'undefined') {
                if (tooltip_time!== '') tooltip_time = tooltip_time + '<br/>';
                return  tooltip_time + cost_curency + prices_per_day[bk_type][td_class] ;

           }

    }

    return  tooltip_time   ;

}



// Admin panel - Add additional row with cost, which is depends from number of selected days
function addRowForCustomizationCostDependsFromNumSellDays(row__id) {
   jQuery('#cost_days_row_help'+row__id ).html( getRowForCustomizationCostDependsFromNumSellDays(row__id)) ;
}
function getRowForCustomizationCostDependsFromNumSellDays(row__id) {
    return '<select name="cost_apply_to'+row__id+'" id="cost_apply_to'+row__id+'" style="width:220px;padding:3px 1px 1px 1px !important;" >\n\
     <option value="%">'+bk_cost_depends_from_selection_line2+'</option>\n\
     <option value="fixed">'+bk_cost_depends_from_selection_line1+'</option>\n\
     <option value="add">'+bk_cost_depends_from_selection_line3+'</option>\n\
     </select>';
}

function addRowForCustomizationCostDependsFromNumSellDays4Summ(row__id) {
   jQuery('#cost_days_row_help'+row__id ).html( getRowForCustomizationCostDependsFromNumSellDays4Summ(row__id)) ;
}
function getRowForCustomizationCostDependsFromNumSellDays4Summ(row__id) {
    return '<select name="cost_apply_to'+row__id+'" id="cost_apply_to'+row__id+'" style="width:220px;padding:3px 1px 1px 1px !important;" >\n\
     <option value="%">'+bk_cost_depends_from_selection_line24summ+'</option>\n\
     <option value="fixed">'+bk_cost_depends_from_selection_line14summ+'</option>\n\
     </select>';
}


function setBookingFormElementsWheelScroll(bk_type){

        var submit_form = document.getElementById('booking_form' +  bk_type );
        var formdata = '';

        if (submit_form != null ) {
                var count = submit_form.elements.length;
                var inp_value;
                var element;
                var el_type;
                var element_to_check;
                // Serialize form here
                for (i=0; i<count; i++)   {
                    element = submit_form.elements[i];
                    element_to_check = document.getElementById('bookinghint_' +  element.id );
                    
                    if  ( element_to_check != null ) {
                        
                        document.getElementById( 'bookinghint_' +  element.id  ).innerHTML =
                            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

                    }
                }
        }
}


function getBookingFormElements(bk_type){

        var submit_form = document.getElementById('booking_form' +  bk_type );
        var formdata = '';

        if (submit_form != null) {
                var count = submit_form.elements.length;
                var inp_value;
                var element;
                var el_type;
                // Serialize form here
                for (i=0; i<count; i++)   {
                    element = submit_form.elements[i];

                    if ( (element.type !=='button') && (element.type !=='hidden') && ( element.name !== ('date_booking' + bk_type) )   ) {           // Skip buttons and hidden element - type

                        // Get Element Value
                        if ( element.type == 'checkbox' ){

                            if (element.value == '') {
                                inp_value = element.checked;
                            } else {
                                if (element.checked) inp_value = element.value;
                                else inp_value = '';
                            }

                        } else if ( element.type == 'radio' ) {

                            if (element.checked) inp_value = element.value;
                            else continue;

                        } else {
                            inp_value = element.value;
                        }                      

                        // Get value in selectbox of multiple selection
                        if (element.type =='select-multiple') {
                            inp_value = jQuery('[name="'+element.name+'"]').val() ;
                            if (( inp_value == null ) || (inp_value.toString() == '' ))
                                inp_value='';
                        }


                        if ( element.name !== ('captcha_input' + bk_type) ) {
                            if (formdata !=='') formdata +=  '~';                                                // next field element

                            el_type = element.type;
                            if ( element.className.indexOf('wpdev-validates-as-email') !== -1 )  el_type='email';
                            if ( element.className.indexOf('wpdev-validates-as-coupon') !== -1 ) el_type='coupon';

                            formdata +=  el_type + '^' + element.name + '^' + inp_value ;                    // element attr
                        }
                    }
                }
        }
        return formdata;

}


function showCostHintInsideBkForm( bk_type ){

    ////////////////////////////////////////////////////////////////////

    // Disable updating cost hint during first click, if range days selection with  2 mouse clicks is active
    // if ( jQuery('#booking_form_div'+bk_type+' input[type="button"]').prop('disabled' ) ) {
    //     return;
    // }

    ////////////////////////////////////////////////////////////////////

    // submit_bk_color = jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color');
    // jQuery('#booking_form_div'+bk_type+' input[type="button"]').attr('disabled', 'disabled'); // Disable the submit button
    // jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color', '#aaa');

    ////////////////////////////////////////////////////////////////////

    if (document.getElementById('parent_of_additional_calendar' + bk_type) != null) { // Its mean that we get cost hint clicking at additional calendar
        bk_type = document.getElementById('parent_of_additional_calendar' + bk_type).value; // Get parent bk type from additional calendar
    }

    // if (document.getElementById('booking_hint' + bk_type) == null) return false;


    var all_dates = jQuery('#date_booking' + bk_type).val();
    var formdata  = getBookingFormElements(bk_type);

    setBookingFormElementsWheelScroll(bk_type);

    // if user select a date so need to show calculation in process
    if (document.getElementById('booking_hint' + bk_type) !== null)
        document.getElementById('booking_hint' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('additional_booking_hint' + bk_type) !== null)
        document.getElementById('additional_booking_hint' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('original_booking_hint' + bk_type) !== null)
        document.getElementById('original_booking_hint' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('original_booking_hint' + bk_type) !== null)
        document.getElementById('original_booking_hint' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('deposit_booking_hint' + bk_type) !== null)
        document.getElementById('deposit_booking_hint' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('balance_booking_hint' + bk_type) !== null)
        document.getElementById('balance_booking_hint' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';


    // Dates and Times shortcodes
    if (document.getElementById('check_in_date_hint_tip' + bk_type) !== null)
        document.getElementById('check_in_date_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('check_out_date_hint_tip' + bk_type) !== null)
        document.getElementById('check_out_date_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('start_time_hint_tip' + bk_type) !== null)
        document.getElementById('start_time_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('end_time_hint_tip' + bk_type) !== null)
        document.getElementById('end_time_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('selected_dates_hint_tip' + bk_type) !== null)
        document.getElementById('selected_dates_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('selected_timedates_hint_tip' + bk_type) !== null)
        document.getElementById('selected_timedates_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('selected_short_dates_hint_tip' + bk_type) !== null)
        document.getElementById('selected_short_dates_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('selected_short_timedates_hint_tip' + bk_type) !== null)
        document.getElementById('selected_short_timedates_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('days_number_hint_tip' + bk_type) !== null)
        document.getElementById('days_number_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    if (document.getElementById('nights_number_hint_tip' + bk_type) !== null)
        document.getElementById('nights_number_hint_tip' + bk_type).innerHTML =
            '<span class="wpbc_ajax_loader"><img style="vertical-align:middle;" src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//span>';

    // Prevent of showing any hints,  if selected only Check In day if we are using range days selection mode using 2 mouse clicks
    if (bk_days_selection_mode == 'dynamic') {                                  //FixIn: 5.4.3
        var inst = jQuery.datepick._getInst( document.getElementById( 'calendar_booking' + bk_type ) );
        if ( inst.stayOpen == true ) {
            
            jQuery('.wpbc_ajax_loader').html('...');            
            return false;
        }                          
    }

    var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename;
    var ajax_type_action='CALCULATE_THE_COST';
    var my_booking_form='';
    if (document.getElementById('booking_form_type' + bk_type) != undefined)
        my_booking_form =document.getElementById('booking_form_type' + bk_type).value;


    jQuery.ajax({                                           // Start Ajax Sending
        //  url: wpdev_ajax_path,
        url: wpbc_ajaxurl, 
        type:'POST',
        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond_insert' + bk_type).html( data ) ;},
        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
        // beforeSend: someFunction,
        data:{
            action: ajax_type_action,
            //  ajax_action : ajax_type_action,
            form: formdata,
            all_dates : all_dates,
            bk_type : bk_type,
            booking_form_type:my_booking_form,
            wpdev_active_locale:wpbc_active_locale,
            wpbc_nonce: document.getElementById('wpbc_nonce'+ ajax_type_action + bk_type).value 
        }
    });
    return false;
}

var temp_click_mode_days_start = [];

function check_conditions_for_start_day_selection(bk_type, selceted_first_day, is_start){
    
    if (is_start == 'start') {
        temp_click_mode_days_start = [];
        

        // S E A S O N     C O N D I T I O N S
        if ( wpdev_bk_seasons_conditions_for_start_day.length > 0 )
            if ( typeof( wpdev_bk_seasons_conditions_for_start_day[bk_type] ) != 'undefined' )
                if ( wpdev_bk_seasons_conditions_for_start_day[bk_type].length > 0 ) {
                        
                    var class_day = (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
                    if (jQuery('#calendar_booking'+bk_type+' .datepick-days-cell.cal4date-'+class_day).length <= 0) return;  // This date is not exist
                    var calendarDateClassList =jQuery('#calendar_booking'+bk_type+' .datepick-days-cell.cal4date-'+class_day).attr('class').split(/\s+/);    

                    // Check for the Season filters conditions for the range days selection 
                    jQuery.each( calendarDateClassList, function(index, singleClassCSS){

                        // S E A S O N    F I L T E R    C O N D I T I O N S     - checking
                        if ( singleClassCSS.indexOf("wpdevbk_season_") >= 0 ) {

                            singleClassCSS = singleClassCSS.replace('wpdevbk_season_', '');

                            jQuery.each( wpdev_bk_seasons_conditions_for_start_day[bk_type], function(ind, seasons_condition){
                                if ( (singleClassCSS == seasons_condition[0] ) && ( temp_click_mode_days_start.length == 0 ) ){                                    
                                    temp_click_mode_days_start = [bk_2clicks_mode_days_start, bk_1click_mode_days_start];                                    
                                    bk_2clicks_mode_days_start = seasons_condition[1] ;
                                    bk_1click_mode_days_start = seasons_condition[1];
                                }
                            });            
                        }        
                    });

                }
        
    }
    
    if (is_start == 'end') {
        if ( temp_click_mode_days_start.length > 0 ) {
            bk_2clicks_mode_days_start = temp_click_mode_days_start[0];
            if ( temp_click_mode_days_start.length > 1 ) {
                bk_1click_mode_days_start = temp_click_mode_days_start[1];
                temp_click_mode_days_start = [];
            }
        }
    }
}

// TODO:3 Interface in the popup dialog for possibility to enter these conditions
// TODO:5 Refactoring structure
function check_conditions_for_range_days_selection(all_dates, bk_type){
    
    if ( ( bk_days_selection_mode != 'dynamic') && 
         ( bk_days_selection_mode != 'fixed') ) return false;                   // This conditional logic is possible only if the rnage days selection using 2 mouse clicks is activated
    if (all_dates == '')                        return false;                   // If no days selections so then skip all.    
    
    if ( typeof(all_dates) == 'object' ) {  // H I G H L I G H T                // If this paramater is object (Date), so  its mean that we are highlight the dates             
        var selceted_first_day = all_dates;       
        all_dates = document.getElementById('date_booking'+bk_type).value;      // Get dates from the textarea date booking to ''
        if (all_dates != '') {                                                  // If some date is selected
            var first_date  = get_first_day_of_selection(all_dates);            // So we are DO NOT MAKE chnaging of highlighting if its was first click.
            var last_date   = get_last_day_of_selection(all_dates);
            if ( first_date == last_date ) return false;                              
        }
    } else {                                // S E L E C T 
        var first_date  = get_first_day_of_selection(all_dates);                  
        var last_date   = get_last_day_of_selection(all_dates);
        if ( first_date != last_date ) return false;                            // We are clicked second time
        var date_sections = first_date.split("."); 
        var selceted_first_day = new Date;       
        selceted_first_day.setFullYear( parseInt(date_sections[2]-0) ,parseInt(date_sections[1]-1), parseInt(date_sections[0]-0) );
    }  
    

    check_conditions_for_range_days_selection_for_check_in(selceted_first_day, bk_type);
}   

function check_conditions_for_range_days_selection_for_check_in(selceted_first_day, bk_type){ 

  
    if ( bk_2clicks_mode_days_selection__saved_variables.length == 0) {         // Saved ONLY ONCE initial values of varibales: bk_2clicks_mode_days_specific , bk_2clicks_mode_days_min, bk_2clicks_mode_days_max     
        bk_2clicks_mode_days_selection__saved_variables = [ bk_2clicks_mode_days_specific, 
                                                            bk_2clicks_mode_days_min, 
                                                            bk_2clicks_mode_days_max,
                                                            bk_1click_mode_days_num
                                                          ];
    }
    
    var is_condition_applied = false;
    
    // S E A S O N     C O N D I T I O N S
    if ( wpdev_bk_seasons_conditions_for_range_selection.length > 0 )
        if ( typeof( wpdev_bk_seasons_conditions_for_range_selection[bk_type] ) != 'undefined' )
            if ( wpdev_bk_seasons_conditions_for_range_selection[bk_type].length > 0 ) {
                
                var class_day = (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
                if (jQuery('#calendar_booking'+bk_type+' .datepick-days-cell.cal4date-'+class_day).length <= 0) return;  // This date is not exist
                var calendarDateClassList =jQuery('#calendar_booking'+bk_type+' .datepick-days-cell.cal4date-'+class_day).attr('class').split(/\s+/);    
                
                // Check for the Season filters conditions for the range days selection 
                jQuery.each( calendarDateClassList, function(index, singleClassCSS){

                    // S E A S O N    F I L T E R    C O N D I T I O N S     - checking
                    if ( singleClassCSS.indexOf("wpdevbk_season_") >= 0 ) {

                        singleClassCSS = singleClassCSS.replace('wpdevbk_season_', '');

                        jQuery.each( wpdev_bk_seasons_conditions_for_range_selection[bk_type], function(ind, seasons_condition){
                            if (singleClassCSS == seasons_condition[0] ) {
                                bk_2clicks_mode_days_specific = seasons_condition[1] ;
                                bk_2clicks_mode_days_min = seasons_condition[1][0];
                                bk_2clicks_mode_days_max = seasons_condition[1][ (seasons_condition[1].length-1) ];                
                                bk_1click_mode_days_num  = seasons_condition[1][0];
                                is_condition_applied = true;
                            }
                        });            
                    }        
                });

            }
            
            
    // W E E K D A Y S     C O N D I T I O N S
    if ( wpdev_bk_weekday_conditions_for_range_selection.length > 0 )
        if ( typeof( wpdev_bk_weekday_conditions_for_range_selection[bk_type] ) != 'undefined' )
            if ( wpdev_bk_weekday_conditions_for_range_selection[bk_type].length > 0 ) {
            
                // Check for the WEEK DAY filters conditions for the range days selection 
                jQuery.each( wpdev_bk_weekday_conditions_for_range_selection[bk_type], function(ind, weekday_condition){
                    if ( selceted_first_day.getDay() == weekday_condition[0] ) {
                                bk_2clicks_mode_days_specific = weekday_condition[1] ;
                                bk_2clicks_mode_days_min = weekday_condition[1][0];
                                bk_2clicks_mode_days_max = weekday_condition[1][ (weekday_condition[1].length-1) ]; 
                                bk_1click_mode_days_num = weekday_condition[1][0];
                                is_condition_applied = true;
                    }            
                });
            }
            
    if ( is_condition_applied == false ) {
        // Reset to the global initial conditions
        bk_2clicks_mode_days_specific = bk_2clicks_mode_days_selection__saved_variables[0];
        bk_2clicks_mode_days_min      = bk_2clicks_mode_days_selection__saved_variables[1];
        bk_2clicks_mode_days_max      = bk_2clicks_mode_days_selection__saved_variables[2];
        bk_1click_mode_days_num       = bk_2clicks_mode_days_selection__saved_variables[3];
    }
}


function wpdevbk_get_selected_checkboxes_id(my_link, my_parameter, my_current_id, my_checkboxes){

    var all_selected_id = my_current_id + ',';
    
    // Get all selecyed IDs
    jQuery(my_checkboxes).each(function () {
        if (this.checked) {
            if (jQuery(this).val() != my_current_id )
            all_selected_id += jQuery(this).val() + ',';
        }
    });
    
    // Remove last ","
    all_selected_id = all_selected_id.substring(0,(all_selected_id.length-1) );
    
    window.location.href= my_link + '&' + my_parameter + '=' + all_selected_id ;
}


function wpbc_show_date_info_bottom( param_calendar_id, my_thisDateTime ) {
    
    if( typeof( is_show_cost_in_date_cell ) !== 'undefined' )
        if ( is_show_cost_in_date_cell ) {
            var my_calendar_id = param_calendar_id;
            my_calendar_id = parseInt( my_calendar_id.replace("calendar_booking","") );

            var my_day = new Date(my_thisDateTime);

            my_day = parseInt( my_day.getMonth() + 1 ) + '-' + parseInt( my_day.getDate() ) + '-' + parseInt( my_day.getFullYear() );

             if(typeof(  prices_per_day[my_calendar_id] ) !== 'undefined')
                   if(typeof(  prices_per_day[my_calendar_id][my_day] ) !== 'undefined') {
                           return  wpbc_curency_symbol + prices_per_day[ my_calendar_id ][ my_day ];
                   }    
        }
    return '';
//'&#36;' 	dollar symbol
//'&#163;' 	Pound
//'&#165;' 	Yen
//'&#8364;' 	Euro symbol
        
}