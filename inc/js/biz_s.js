//Customization of bufer time for DAN
var time_buffer_value = 0;

// Highlighting range days at calendar
var payment_request_id = 0;

// Check  start time or end time for the time, which is gone already TODAY.
var is_check_start_time_gone = false;



// Prepare to show tooltips
function prepare_tooltip(myParam){   
       var tooltip_day_class_4_show = " .timespartly";
       if (is_show_availability_in_tooltips) {
           if (  wpdev_in_array( parent_booking_resources , myParam ) )
                tooltip_day_class_4_show = " .datepick-days-cell";//" .datepick-days-cell a";  // each day
       }
       if (is_show_cost_in_tooltips) {
            tooltip_day_class_4_show =  " .datepick-days-cell";//" .datepick-days-cell a";  // each day
       }

      // Show tooltip at each day if time availability filter is set
      if(typeof( global_avalaibility_times[myParam]) != "undefined") {
          if (global_avalaibility_times[myParam].length>0)  tooltip_day_class_4_show = " .datepick-days-cell";  // each day
      }


    jQuery("#calendar_booking" + myParam + tooltip_day_class_4_show ).popover( {
        placement: 'top'
      , delay: { show: 500, hide: 1 }
      , content: ''
      , template: '<div class="wpdevbk popover"><div class="arrow"></div><div class="popover-inner"><h3 class="popover-title"></h3><div class="popover-content"><p></p></div></div></div>'
    });

}


// Check is this day booked or no
function is_this_day_booked(bk_type, td_class, i){ // is is not obligatory parameter

    if (    ( jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).hasClass('date_user_unavailable') ) 
         || ( jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).hasClass('datepick-unselectable') )
         || (  ( jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).hasClass('check_out_time') )  && ( i!=0 )  )
       ){ // If we find some unselect option so then make no selection at all in this range
                jQuery('#calendar_booking'+bk_type+' .cal4date-' + td_class).removeClass('datepick-current-day');
                document.body.style.cursor = 'default';return true;
    }

    //Check if in selection range are reserved days, if so then do not make selection
    if(typeof(date_approved[ bk_type ]) !== 'undefined')
        if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined') { //alert(date_approved[ bk_type ][ td_class ][0][5]);
              for (var j=0; j < date_approved[ bk_type ][ td_class ].length ; j++) {
                    if ( ( date_approved[ bk_type ][ td_class ][j][3] == 0) &&  ( date_approved[ bk_type ][ td_class ][j][4] == 0) )  {document.body.style.cursor = 'default';return true;}
                    //Fixed on 04/02/15; ver. 5.3.1
                    if ( ( (date_approved[ bk_type ][ td_class ][j][5] * 1) == 2 ) && ( i != 0 ) && ( j==0 ) ) {document.body.style.cursor = 'default';return true;}
              }
        }

    if(typeof( date2approve[ bk_type ]) !== 'undefined')
        if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined') {
              for ( j=0; j < date2approve[ bk_type ][ td_class ].length ; j++) {
                    if ( ( date2approve[ bk_type ][ td_class ][j][3] == 0) &&  ( date2approve[ bk_type ][ td_class ][j][4] == 0) )  {document.body.style.cursor = 'default';return true;}
                    //Fixed on 04/02/15; ver. 5.3.1
                    if ( ( (date2approve[ bk_type ][ td_class ][j][5] * 1) == 2 ) && ( i != 0 ) && ( j==0 ) ) {document.body.style.cursor = 'default';return true;}
              }
        }

    return false;
}


// Get the closets ABS value of element in array to the current myValue
function getAbsClosestValue(myValue, myArray){

    if (myArray.length == 0 ) return myValue;       // If the array is empty -> return  the myValue

    var obj = myArray[0];
    var diff = Math.abs(myValue - obj);             // Get distance between  1st element
    var closetValue = myArray[0];                   // Save 1st element

    for (var i = 1; i < myArray.length; i++) {
        obj = myArray[i];

        if ( Math.abs(myValue - obj) < diff ) {     // we found closer value -> save it
            diff = Math.abs(myValue - obj);
            closetValue = obj;
        }
    }

    return closetValue;
}


// Highligt selectable date in Calendar
function hoverDayPro(value, date, bk_type) {

    if (date == null) {
        jQuery('.datepick-days-cell-over').removeClass('datepick-days-cell-over');                          // clear all highlight days selections
        return false;
    }

    var inst = jQuery.datepick._getInst( document.getElementById( 'calendar_booking' + bk_type ) );
    
    var i=0 ; var td_class; var td_overs = [];                                                              // local variables
    
    if(typeof( check_conditions_for_range_days_selection ) == 'function') {check_conditions_for_range_days_selection( date , bk_type);} // Highlight dates based on the conditions

    // Fixed Days Selection mode - 1 mouse click
    if (bk_days_selection_mode == 'fixed') {

        jQuery('.datepick-days-cell-over').removeClass('datepick-days-cell-over');                          // clear all selections

        if(typeof( check_conditions_for_start_day_selection ) == 'function') 
            check_conditions_for_start_day_selection(bk_type, date, 'start');
        if (bk_1click_mode_days_start != -1) {                                                              // find the Closest start day to the hover day

            var startDay = getAbsClosestValue(date.getDay(), bk_1click_mode_days_start);
            date.setDate( date.getDate() -  ( date.getDay() - startDay )  );
            
            if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') {check_conditions_for_range_days_selection_for_check_in( date , bk_type);} // Highlight dates based on the conditions
        }
        if(typeof( check_conditions_for_start_day_selection ) == 'function') 
            check_conditions_for_start_day_selection(bk_type, date, 'end');
        
        // We are mouseover the date,  that selected. Do not highlight it.
        for ( var date_index in inst.dates ) {
            if ( ( inst.dates[ date_index ].getFullYear() === date.getFullYear() ) &&
                 ( inst.dates[ date_index ].getMonth() === date.getMonth() ) &&
                 ( inst.dates[ date_index ].getDate() === date.getDate() ) ) {
                    return false;
            }
        }

        for( i=0; i < bk_1click_mode_days_num ; i++) {                                                      // recheck  if all days are available for the booking
            td_class =  (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();

            if (  is_this_day_booked(bk_type, td_class, i)  ) return false;                                 // check if day is booked

            td_overs[td_overs.length] = '#calendar_booking'+bk_type+ ' .cal4date-' + td_class;              // add to array for later make selection by class
            date.setDate(date.getDate() + 1);                                                               // set next date
        }

        for ( i=0; i < td_overs.length ; i++) {                                                             // add class to all elements
            jQuery( td_overs[i] ).addClass('datepick-days-cell-over');
        }
        return true;
    }


    // Dynamic Days Selection mode - 2 mouse clicks
    if (bk_days_selection_mode == 'dynamic') {

        jQuery('.datepick-days-cell-over').removeClass('datepick-days-cell-over');                          // clear all highlight days selections        

        // Highligh days Before Selection
        if ( ( inst.dates.length == 0 ) || ( inst.dates.length > 1 ) ) {                                    // We are not clicked yet on days, or the selection was done and we are need to make new selection
            var selceted_first_day = new Date();
            selceted_first_day.setFullYear(date.getFullYear(),(date.getMonth()), (date.getDate() ) );
            
            // We are mouseover the date,  that selected. Do not highlight it.
            for ( var date_index in inst.dates ) {
                if ( ( inst.dates[ date_index ].getFullYear() === selceted_first_day.getFullYear() ) &&
                     ( inst.dates[ date_index ].getMonth() === selceted_first_day.getMonth() ) &&
                     ( inst.dates[ date_index ].getDate() === selceted_first_day.getDate() ) ) {
                        return false;
                }
            }
            
            if(typeof( check_conditions_for_start_day_selection ) == 'function') 
                check_conditions_for_start_day_selection(bk_type, date, 'start');
            if (bk_2clicks_mode_days_start != -1) {

                var startDay = getAbsClosestValue(date.getDay(), bk_2clicks_mode_days_start);
                selceted_first_day.setDate( date.getDate() -  ( date.getDay() - startDay )  );

                if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') {check_conditions_for_range_days_selection_for_check_in( selceted_first_day , bk_type);} // Highlight dates based on the conditions                
            }
            if(typeof( check_conditions_for_start_day_selection ) == 'function') 
                check_conditions_for_start_day_selection(bk_type, date, 'end');
            
            i=0;
            while( ( i < bk_2clicks_mode_days_min) ) {
               i++;
               td_class =  (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
               if (   is_this_day_booked(bk_type, td_class, (i-1))   ) return false;                         // check if day is booked
               td_overs[td_overs.length] = '#calendar_booking'+bk_type+ ' .cal4date-' + td_class;            // add to array for later make selection by class
               selceted_first_day.setFullYear(selceted_first_day.getFullYear(),(selceted_first_day.getMonth()), (selceted_first_day.getDate() + 1) );
            }
        }

        // First click on days
        if (inst.dates.length == 1) {                                                                       // select start date in Dynamic range selection, after first days is selected
            var selceted_first_day = new Date();
            selceted_first_day.setFullYear(inst.dates[0].getFullYear(),(inst.dates[0].getMonth()), (inst.dates[0].getDate() ) ); //Get first Date
            var is_check = true;
            i=0;

            while(  (is_check ) || ( i < bk_2clicks_mode_days_min ) ) {                                         // Untill rich MIN days number.
               i++;
               td_class =  (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();

                if (  is_this_day_booked(bk_type, td_class, (i-1))  ) return false;                             // check if day is booked

                td_overs[td_overs.length] = '#calendar_booking'+bk_type+ ' .cal4date-' + td_class;              // add to array for later make selection by class

                var is_discreet_ok = true;
                if (bk_2clicks_mode_days_specific.length>0) {              // check if we set some discreet dates
                    is_discreet_ok = false;
                    for (var di = 0; di < bk_2clicks_mode_days_specific.length; di++) {   // check if current number of days inside of discreet one
                         if ( (  i == bk_2clicks_mode_days_specific[di] )  ) {
                             is_discreet_ok = true;
                             di = (bk_2clicks_mode_days_specific.length + 1);
                         }
                    }
                }

                if (   ( date.getMonth() == selceted_first_day.getMonth() )  &&
                       ( date.getDate() == selceted_first_day.getDate() )  &&
                       ( date.getFullYear() == selceted_first_day.getFullYear() )  && ( is_discreet_ok )  )
                {is_check =  false;}

                if ((selceted_first_day > date ) && ( i >= bk_2clicks_mode_days_min ) && ( i < bk_2clicks_mode_days_max )  && (is_discreet_ok)  )   {
                    is_check =  false;
                }
                if ( i >= bk_2clicks_mode_days_max ) is_check =  false;
                selceted_first_day.setFullYear(selceted_first_day.getFullYear(),(selceted_first_day.getMonth()), (selceted_first_day.getDate() + 1) );
            }
        }

        // Highlight Days
        for ( i=0; i < td_overs.length ; i++) {                                                             // add class to all elements
            jQuery( td_overs[i] ).addClass('datepick-days-cell-over');
        }
        return true;
    }
}

// select a day
function selectDayPro(all_dates,   bk_type){

    if(typeof( prepare_tooltip ) == 'function') {setTimeout("prepare_tooltip("+bk_type+");",1000);}

    if(typeof( check_conditions_for_range_days_selection ) == 'function') {check_conditions_for_range_days_selection( all_dates , bk_type);}
    
    // Help with range selection
    bkRangeDaysSelection(all_dates,   bk_type);

    // Conditional showing form elements
    // We are need to  get the dates from  the textarea and not from  all_dates variable
    // because in the range days selection  the dates can be changed
   if(typeof( check_condition_sections_in_bkform ) == 'function') {check_condition_sections_in_bkform( jQuery('#date_booking' + bk_type).val() , bk_type);}

    // HERE WE WILL DISABLE ALL OPTIONS IN RANGE TIME INTERVALS FOR SINGLE DAYS SELECTIONS FOR THAT DAYS WHERE HOURS ALREADY BOOKED
    bkDisableBookedTimeSlots( jQuery('#date_booking' + bk_type).val() , bk_type);

    //Calculate the cost and show inside of form
    if(typeof( showCostHintInsideBkForm ) == 'function') {  showCostHintInsideBkForm( bk_type); }

    if (false)
    if (bk_days_selection_mode == 'dynamic') {                                  // Check if range days selection with 2 mouse clicks active
        // Check if we made first click and show message
        if ( jQuery('#booking_form_div'+bk_type+' input[type="button"]').prop('disabled' ) ) {
            var message_verif_select_checkout = "Please, select 'Check Out' date at Calendar.";
            jQuery( '#date_booking' + bk_type  )
                    .after('<span class="wpbc_message_select_checkout wpdev-help-message wpdev-element-message alert">'+ message_verif_select_checkout +'</span>'); // Show message
            jQuery(".wpbc_message_select_checkout")
                    .animate( {opacity: 1}, 10000 )
                    .fadeOut( 2000 );               
        } else {    // Check  if we clicked second time and remove message.
            jQuery(".wpbc_message_select_checkout").remove();
        }
    }
}

// Check if this IE and get version of IE otherwise setversion of IE to 0
var isIE_4_bk = (navigator.appName=="Microsoft Internet Explorer");
var IEversion_4_bk = navigator.appVersion;
if(isIE_4_bk) { IEversion_4_bk = parseInt(IEversion_4_bk.substr(IEversion_4_bk.indexOf("MSIE")+4));
} else { IEversion_4_bk = 0; }


// Make range select
function bkRangeDaysSelection(all_dates,   bk_type){

     var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
     var td_class;

     if ( (bk_days_selection_mode == 'fixed') || (bk_days_selection_mode == 'dynamic') ) {  // Start range selections checking

        var internal_bk_1click_mode_days_num = bk_1click_mode_days_num;

        if ( all_dates.indexOf(' - ') != -1 ){                  // Dynamic selections
            var start_end_date = all_dates.split(" - ");

            var is_dynamic_startdayequal_to_last = true;
            if (inst.dates.length>1){
                if (bk_days_selection_mode == 'dynamic') { // Dinamic
                    is_dynamic_startdayequal_to_last = false;
                }
            }


            if ( ( start_end_date[0] == start_end_date[1] ) && (is_dynamic_startdayequal_to_last ===true)   ) {    // First click at day
              if(typeof( check_conditions_for_start_day_selection ) == 'function') {
                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date of click                  
                    check_conditions_for_start_day_selection(bk_type, real_start_dynamic_date, 'start');
              }
              if (bk_2clicks_mode_days_start != -1) {             // Activated some specific week day start range selectiosn
                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date of click

                    if (real_start_dynamic_date.getDay() !=  bk_2clicks_mode_days_start) {  

                        var startDay = getAbsClosestValue(real_start_dynamic_date.getDay(), bk_2clicks_mode_days_start);
                        real_start_dynamic_date.setDate( real_start_dynamic_date.getDate() -  ( real_start_dynamic_date.getDay() - startDay )  );


                        all_dates = jQuery.datepick._formatDate(inst, real_start_dynamic_date );
                        all_dates += ' - ' + all_dates ;
                        jQuery('#date_booking' + bk_type).val(all_dates); // Fill the input box
            
                        if(typeof( check_conditions_for_range_days_selection ) == 'function') {check_conditions_for_range_days_selection( all_dates , bk_type);} // Highlight dates based on the conditions                

                        // check this day for already booked
                        var selceted_first_day = new Date;
                        selceted_first_day.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() ) );
                        i=0;
                        while(    ( i < bk_2clicks_mode_days_min ) ) {
                           
                           td_class =  (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
                           if (   is_this_day_booked(bk_type, td_class, (i))   ) {
                                // Unselect all dates and set  properties of Datepick
                                jQuery('#date_booking' + bk_type).val('');      //FixIn: 5.4.3
                                inst.stayOpen = false;                          //FixIn: 5.4.3
                                inst.dates=[];                                
                                jQuery.datepick._updateDatepick(inst);
                                return false;   // check if day is booked
                           }
                           selceted_first_day.setFullYear(selceted_first_day.getFullYear(),(selceted_first_day.getMonth()), (selceted_first_day.getDate() + 1) );
                           i++;
                        }

                        // Selection of the day
                        inst.cursorDate.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() ) );
                        inst.dates=[inst.cursorDate];
                        jQuery.datepick._updateDatepick(inst);
                     } 
              } else { // Set correct date, if only single date is selected, and possible press send button then.
                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date of click
                    inst.cursorDate.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() ) );
                    inst.dates=[inst.cursorDate];
                    jQuery.datepick._updateDatepick(inst);
                    jQuery('#date_booking' + bk_type).val(start_end_date[0]);
              }
              if(typeof( check_conditions_for_start_day_selection ) == 'function') 
                check_conditions_for_start_day_selection(bk_type, '', 'end');              
              submit_bk_color = jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color');

              if (bk_2clicks_mode_days_min>1) {
                jQuery('#booking_form_div'+bk_type+' input[type="button"]').attr('disabled', 'disabled'); // Disbale the submit button
                jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color', '#aaa');
              }
              setTimeout(function ( ) {jQuery('#calendar_booking' + bk_type + ' .datepick-unselectable.timespartly.check_out_time,#calendar_booking' + bk_type + ' .datepick-unselectable.timespartly.check_in_time').removeClass('datepick-unselectable');} ,500);              
              return false;
            } else {  // Last day click

                    jQuery('#booking_form_div'+bk_type+' input[type="button"]').removeAttr('disabled');  // Activate the submit button
                    jQuery('#booking_form_div'+bk_type+' input[type="button"]').css('color',  submit_bk_color );

                    var start_dynamic_date = start_end_date[0].split(".");
                    var real_start_dynamic_date=new Date();
                    real_start_dynamic_date.setFullYear( start_dynamic_date[2],  start_dynamic_date[1]-1,  start_dynamic_date[0] );    // get date

                    var end_dynamic_date = start_end_date[1].split(".");
                    var real_end_dynamic_date=new Date();
                    real_end_dynamic_date.setFullYear( end_dynamic_date[2],  end_dynamic_date[1]-1,  end_dynamic_date[0] );    // get date

                    internal_bk_1click_mode_days_num = 1; // need to count how many days right now

                    var temp_date_for_count = new Date();

                    for( var j1=0; j1 < 365 ; j1++) {
                        temp_date_for_count = new Date();
                        temp_date_for_count.setFullYear(real_start_dynamic_date.getFullYear(),(real_start_dynamic_date.getMonth()), (real_start_dynamic_date.getDate() + j1) );

                        if ( (temp_date_for_count.getFullYear() == real_end_dynamic_date.getFullYear()) && (temp_date_for_count.getMonth() == real_end_dynamic_date.getMonth()) && (temp_date_for_count.getDate() == real_end_dynamic_date.getDate()) )  {
                            internal_bk_1click_mode_days_num = j1;
                            j1=1000;
                        }
                    }
                    internal_bk_1click_mode_days_num++;
                    all_dates =  start_end_date[0];
                    if (internal_bk_1click_mode_days_num < bk_2clicks_mode_days_min ) internal_bk_1click_mode_days_num = bk_2clicks_mode_days_min;

                    var is_backward_direction = false;
                    if (bk_2clicks_mode_days_specific.length>0) {              // check if we set some discreet dates

                        var is_discreet_ok = false;
                        while (  is_discreet_ok === false ) {

                            for (var di = 0; di < bk_2clicks_mode_days_specific.length; di++) {   // check if current number of days inside of discreet one
                                 if ( 
                                    ( (  internal_bk_1click_mode_days_num == bk_2clicks_mode_days_specific[di] )  ) &&
                                      (internal_bk_1click_mode_days_num <= bk_2clicks_mode_days_max) ) {
                                     is_discreet_ok = true;
                                     di = (bk_2clicks_mode_days_specific.length + 1);
                                 }
                            }
                            if (is_backward_direction === false)
                                if (  is_discreet_ok === false )
                                    internal_bk_1click_mode_days_num++;

                            // BackWard directions, if we set more than maximum days
                            if (internal_bk_1click_mode_days_num >= bk_2clicks_mode_days_max) is_backward_direction = true;

                            if (is_backward_direction === true)
                                if (  is_discreet_ok === false )
                                    internal_bk_1click_mode_days_num--;

                            if (internal_bk_1click_mode_days_num < bk_2clicks_mode_days_min )  is_discreet_ok = true;
                        }

                    } else {
                        if (internal_bk_1click_mode_days_num > bk_2clicks_mode_days_max) internal_bk_1click_mode_days_num = bk_2clicks_mode_days_max;
                    }

                    
            }
        } // And Range selections checking

        var temp_bk_days_selection_mode = bk_days_selection_mode ;
        bk_days_selection_mode = 'multiple';

        inst.dates = [];                                        // Emty dates in datepicker
        var all_dates_array;
        var date_array;
        var date;
        var date_to_ins;

        // Get array of dates
        if ( all_dates.indexOf(',') == -1 ) {all_dates_array = [all_dates];}
        else                                {all_dates_array = all_dates.split(",");}

        var original_array = [];
        var isMakeSelection = false;

        if ( temp_bk_days_selection_mode != 'dynamic' ) {
            // Gathering original (already selected dates) date array
            for( var j=0; j < all_dates_array.length ; j++) {                           //loop array of dates
                all_dates_array[j] = all_dates_array[j].replace(/(^\s+)|(\s+$)/g, "");  // trim white spaces in date string

                date_array = all_dates_array[j].split(".");                             // get single date array

                date=new Date();
                date.setFullYear( date_array[2],  date_array[1]-1,  date_array[0] );    // get date

                if ( (date.getFullYear() == inst.cursorDate.getFullYear()) && (date.getMonth() == inst.cursorDate.getMonth()) && (date.getDate() == inst.cursorDate.getDate()) )  {
                    isMakeSelection = true;
                    if(typeof( check_conditions_for_start_day_selection ) == 'function') 
                        check_conditions_for_start_day_selection(bk_type, inst.cursorDate, 'start');                    
                    if (bk_1click_mode_days_start != -1) {
                        var startDay = getAbsClosestValue(inst.cursorDate.getDay(), bk_1click_mode_days_start);
                        inst.cursorDate.setDate( inst.cursorDate.getDate() -  ( inst.cursorDate.getDay() - startDay )  );
      
                        bk_days_selection_mode = temp_bk_days_selection_mode;
                        if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') {check_conditions_for_range_days_selection_for_check_in( inst.cursorDate , bk_type);} // Highlight dates based on the conditions                                        
                        temp_bk_days_selection_mode = bk_days_selection_mode ;
                        bk_days_selection_mode = 'multiple';
                        internal_bk_1click_mode_days_num = bk_1click_mode_days_num;

                    }
                    if(typeof( check_conditions_for_start_day_selection ) == 'function') 
                        check_conditions_for_start_day_selection(bk_type, inst.cursorDate, 'end');
                    
                }
            }
        } else {
            isMakeSelection = true;                                                         // dynamic range selection
        }

        var isEmptySelection = false;
        if (isMakeSelection) {
            var date_start_range = inst.cursorDate;

            if ( temp_bk_days_selection_mode != 'dynamic' ) {
                original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, inst.cursorDate , null))  ); //add date
            } else {
                original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, real_start_dynamic_date , null))  ); //set 1st date from dynamic range
                date_start_range = real_start_dynamic_date;
            }
            var dates_array = [];
            var range_array = [];
            var td;
            // Add dates to the range array
            for( var i=1; i < internal_bk_1click_mode_days_num ; i++) {

                dates_array[i] = new Date();
                // dates_array[i].setDate( (date_start_range.getDate() + i) );

                dates_array[i].setFullYear(date_start_range.getFullYear(),(date_start_range.getMonth()), (date_start_range.getDate() + i) );

                td_class =  (dates_array[i].getMonth()+1) + '-'  +  dates_array[i].getDate() + '-' + dates_array[i].getFullYear();
                td =  '#calendar_booking'+bk_type+' .cal4date-' + td_class;
                 if (jQuery(td).hasClass('datepick-unselectable') ){ // If we find some unselect option so then make no selection at all in this range
                     jQuery(td).removeClass('datepick-current-day');
                     isEmptySelection = true;
                }

                //Check if in selection range are reserved days, if so then do not make selection
                if (   is_this_day_booked(bk_type, td_class, i)   ) isEmptySelection = true;
                /////////////////////////////////////////////////////////////////////////////////////

                date_to_ins =  jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, dates_array[i], null));

                range_array.push( date_to_ins );
            }

            // check if some dates are the same in the arrays so the remove them from both
            for( i=0; i < range_array.length ; i++) {
                for( j=0; j < original_array.length ; j++) {       //loop array of dates

                if ( (original_array[j] != -1) && (range_array[i] != -1) )
                    if ( (range_array[i].getFullYear() == original_array[j].getFullYear()) && (range_array[i].getMonth() == original_array[j].getMonth()) && (range_array[i].getDate() == original_array[j].getDate()) )  {
                        range_array[i] = -1;
                        original_array[j] = -1;
                    }
                }
            }

            // Add to the dates array
            for( j=0; j < original_array.length ; j++) {       //loop array of dates
                    if (original_array[j] != -1) inst.dates.push(original_array[j]);
            }
            for( i=0; i < range_array.length ; i++) {
                    if (range_array[i] != -1) inst.dates.push(range_array[i]);
            }
        }
        if (! isEmptySelection) isEmptySelection = checkIfSomeDaysUnavailable(inst.dates, bk_type);
        if (isEmptySelection) inst.dates=[];

        //jQuery.datepick._setDate(inst, dates_array);
        if ( temp_bk_days_selection_mode != 'dynamic' ) {
            jQuery.datepick._updateInput('#calendar_booking'+bk_type);
        } else {
           if (isEmptySelection) jQuery.datepick._updateInput('#calendar_booking'+bk_type);
           else {       // Dynamic range selections, transform days from jQuery.datepick
               dateStr = (inst.dates.length == 0 ? '' : jQuery.datepick._formatDate(inst, inst.dates[0])); // Get first date
                for ( i = 1; i < inst.dates.length; i++)
                     dateStr += jQuery.datepick._get(inst, 'multiSeparator') +  jQuery.datepick._formatDate(inst, inst.dates[i]);  // Gathering all dates
                jQuery('#date_booking' + bk_type).val(dateStr); // Fill the input box
           }
        }
        if ( ( is_dynamic_startdayequal_to_last === false ) && ( start_end_date[0] == start_end_date[1] ) )  {
            if ( inst.dates.length == 1 ) {
                inst.dates.push(inst.dates[0]);
                //jQuery.datepick._updateDatepick(inst);
            }            
        }
        jQuery.datepick._notifyChange(inst);
        jQuery.datepick._adjustInstDate(inst);
        jQuery.datepick._showDate(inst);

        bk_days_selection_mode = temp_bk_days_selection_mode ;
     }
 }

// Disable Booked Time Slots in selectbox
function bkDisableBookedTimeSlots(all_dates, bk_type){

    var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
    var td_class;

    var time_slot_field_name = 'select[name="rangetime' + bk_type + '"]';
    var time_slot_field_name2 = 'select[name="rangetime' + bk_type + '[]"]';
    
    var start_time_slot_field_name = 'select[name="starttime' + bk_type + '"]';
    var start_time_slot_field_name2 = 'select[name="starttime' + bk_type + '[]"]';
    
    // HERE WE WILL DISABLE ALL OPTIONS IN RANGE TIME INTERVALS FOR SINGLE DAYS SELECTIONS FOR THAT DAYS WHERE HOURS ALREADY BOOKED
    //here is not range selections
    all_dates = get_first_day_of_selection(all_dates);
    if ( (bk_days_selection_mode == 'single') || (true) ) {   // Only single day selections here

        var current_single_day_selections  = all_dates.split('.');
        td_class =  (current_single_day_selections[1]*1) + '-' + (current_single_day_selections[0]*1) + '-' + (current_single_day_selections[2]*1);
        var times_array = [];

        jQuery( time_slot_field_name + ' option:disabled,' + time_slot_field_name2 + ' option:disabled,' + start_time_slot_field_name + ' option:disabled,' + start_time_slot_field_name2 + ' option:disabled').removeClass('booked');   // Remove class "booked"
        jQuery( time_slot_field_name + ' option:disabled,' + time_slot_field_name2 + ' option:disabled,' + start_time_slot_field_name + ' option:disabled,' + start_time_slot_field_name2 + ' option:disabled').removeAttr('disabled');  // Make active all times
        

        if ( jQuery( time_slot_field_name+','+time_slot_field_name2 + ',' + start_time_slot_field_name+','+start_time_slot_field_name2 ).length == 0 ) return;  // WE DO NOT HAVE RANGE SELECTIONS AT THIS FORM SO JUST RETURN

        var range_time_object = jQuery( time_slot_field_name + ' option:first,'+time_slot_field_name2 + ' option:first,' + start_time_slot_field_name + ' option:first,'+start_time_slot_field_name2 + ' option:first' ) ;
        if (range_time_object == undefined) return;  // WE DO NOT HAVE RANGE SELECTIONS AT THIS FORM SO JUST RETURN

        // Get dates and time from aproved dates
        if(typeof(date_approved[ bk_type ]) !== 'undefined')
            if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined') {
                if( ( date_approved[ bk_type ][ td_class ][0][3] != 0) ||  ( date_approved[ bk_type ][ td_class ][0][4] != 0) ) {
                    for ( i=0; i< date_approved[ bk_type ][ td_class ].length; i++){
                        h = date_approved[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
                        m = date_approved[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
                        s = date_approved[ bk_type ][ td_class ][i][5];if (s == 2) s = '02';
                        times_array[ times_array.length ] = [h,m,s];
                    }
                }
            }

        // Get dates and time from pending dates
        if(typeof( date2approve[ bk_type ]) !== 'undefined')
            if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined')
                if( ( date2approve[ bk_type ][ td_class ][0][3] != 0) ||  ( date2approve[ bk_type ][ td_class ][0][4] != 0) ) //check for time here
                {for ( i=0; i< date2approve[ bk_type ][ td_class ].length; i++){
                    h = date2approve[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
                    m = date2approve[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
                    s = date2approve[ bk_type ][ td_class ][i][5];if (s == 2) s = '02';
                    times_array[ times_array.length ] = [h,m,s];
                }
                }
                
       // Check about situations, when  we have end time without start  time (... - 09:00) or start  time without end time (21:00 - ...)
       times_array.sort();
       if (times_array.length > 0 ) {
           s = parseInt( times_array[0][2] );
           if ( s == 2 ) {
               times_array[ times_array.length ] = ['00','00','01'];
               times_array.sort();
           }
           s = parseInt( times_array[ ( times_array.length - 1 ) ][2] );
           if ( s == 1 ) {
               times_array[ times_array.length ] = ['23','59','02'];
               times_array.sort();
           }
       }

        var removed_time_slots   = is_time_slot_booked_for_this_time_array( bk_type, times_array );
        var my_time_value        = jQuery( time_slot_field_name + ' option,'+time_slot_field_name2 + ' option,' + start_time_slot_field_name + ' option,'+start_time_slot_field_name2 + ' option');

        for ( j=0; j< my_time_value.length; j++){
            if (  wpdev_in_array( removed_time_slots, j ) ) {
                jQuery( time_slot_field_name + ' option:eq('+j+'),'+time_slot_field_name2 + ' option:eq('+j+'),' +  start_time_slot_field_name + ' option:eq('+j+'),'+start_time_slot_field_name2 + ' option:eq('+j+')').attr('disabled', 'disabled'); // Make disable some options
                jQuery( time_slot_field_name + ' option:eq('+j+'),'+time_slot_field_name2 + ' option:eq('+j+'),' +  start_time_slot_field_name + ' option:eq('+j+'),'+start_time_slot_field_name2 + ' option:eq('+j+')').addClass('booked');           // Add "booked" CSS class 
                if(  jQuery( time_slot_field_name + ' option:eq('+j+'),'+time_slot_field_name2 + ' option:eq('+j+'),' + start_time_slot_field_name + ' option:eq('+j+'),'+start_time_slot_field_name2 + ' option:eq('+j+')' ).attr('selected')  ){  // iF THIS ELEMENT IS SELECTED SO REMOVE IT FROM THIS TIME
                    jQuery(  time_slot_field_name + ' option:eq('+j+'),'+time_slot_field_name2 + ' option:eq('+j+'),' + start_time_slot_field_name + ' option:eq('+j+'),'+start_time_slot_field_name2 + ' option:eq('+j+')' ).removeAttr('selected');

                    if (IEversion_4_bk == 7) { // Emulate disabling option in selectboxes for IE7 - its set selected option, which is not disabled
                        
                        var rangetime_element =  document.getElementsByName("rangetime" + bk_type );
                        if (typeof(rangetime_element) != 'undefined' && rangetime_element != null) {
                            set_selected_first_not_disabled_option_IE7(document.getElementsByName("rangetime" + bk_type )[0] );
                        }
                        
                        var start_element =  document.getElementsByName("starttime" + bk_type );
                        if (typeof(start_element) != 'undefined' && start_element != null) {
                            set_selected_first_not_disabled_option_IE7(document.getElementsByName("starttime" + bk_type )[0] );
                        }
                        
                    }
                }
            }
        }

        if (IEversion_4_bk == 7) { // Emulate disabling option in selectboxes for IE7 - its set grayed text options, which is disabled
            emulate_disabled_options_to_gray_IE7( "rangetime" + bk_type );
            emulate_disabled_options_to_gray_IE7( "starttime" + bk_type );
        }
    }

}


function checkIfSomeDaysUnavailable(selected_dates, bk_type) {

    var i, j, td_class;

    for ( j=0; j< selected_dates.length; j++){
         // Check among availbaility filters
         if (typeof( is_this_day_available ) == 'function') {
            var is_day_available = is_this_day_available( selected_dates[j], bk_type);
            if ( is_day_available instanceof Array ) is_day_available = is_day_available[0];        //FixIn: 6.0.1.8
            if (! is_day_available) {return true;}
        }

       td_class =  (selected_dates[j].getMonth()+1) + '-' + selected_dates[j].getDate() + '-' + selected_dates[j].getFullYear();

       // Get dates and time from pending dates
       if(typeof( date2approve[ bk_type ]) !== 'undefined')
       if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined')
         if( ( date2approve[ bk_type ][ td_class ][0][3] == 0) &&  ( date2approve[ bk_type ][ td_class ][0][4] == 0) ) //check for time here
               {return true;} // day fully booked

       // Get dates and time from aproved dates
       if(typeof(date_approved[ bk_type ]) !== 'undefined')
       if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined')
         if( ( date_approved[ bk_type ][ td_class ][0][3] == 0) &&  ( date_approved[ bk_type ][ td_class ][0][4] == 0) )
               {return true;} // day fully booked

    }

    return  false;
}


// IE7 select box emulate functions for disabling select boxes:
if (IEversion_4_bk == 7) {

            window.onload = function() {
                    if (document.getElementsByTagName) {
                            var s = document.getElementsByTagName("select");

                            if (s.length > 0) {
                                    window.select_current = new Array();

                                    for (var i=0, select; select = s[i]; i++) {
                                            select.onfocus = function(){ window.select_current[this.id] = this.selectedIndex; }
                                            select.onchange = function(){ set_selected_previos_selected_option_IE7(this); }
                                            emulate_disabled_options_to_gray_IE7(select.name);
                                    }
                            }
                    }
            }

            function set_selected_previos_selected_option_IE7(e) {
                    if (e.options[e.selectedIndex].disabled) {
                            e.selectedIndex = window.select_current[e.id];
                    }
            }

            function set_selected_first_not_disabled_option_IE7(e) {

                    if (e.options[e.selectedIndex].disabled) {
                        for (var i=0, option; option = e.options[i]; i++) {
                                if (! option.disabled) {
                                    e.selectedIndex = i;
                                    return 0;
                                }
                        }
                    }
                    return 0;
            }

            function emulate_disabled_options_to_gray_IE7(ename) {
                    
                    jQuery('select[name="'+ename+'"] option,select[name="'+ename+'[]"] option').each(function( index ) {
                        if (jQuery(this).prop('disabled')){
                            jQuery(this).css('color','graytext');
                        } else {
                            jQuery(this).css('color','menutext');
                        }
                    });                    
                    /*
                    for (var i=0, option; option = e.options[i]; i++) {

                            if (option.disabled) { option.style.color = "graytext";}
                            else {                 option.style.color = "menutext";}
                    }*/
            }
}



// Times
function isDayFullByTime(bk_type, td_class ) { 

   var times_array = [];
    var time_slot_field_name = 'select[name="rangetime' + bk_type + '"]';
    var time_slot_field_name2 = 'select[name="rangetime' + bk_type + '[]"]';
    
    // Get rangetime element from possible conditional section                  //FixIn: 5.4.5.2
    if( typeof( wpbc_get_conditional_section_id_for_weekday ) == 'function' ) { 
        var conditional_field_element_id = wpbc_get_conditional_section_id_for_weekday( td_class, bk_type );              
        if ( conditional_field_element_id !== false ) {            
            time_slot_field_name  = conditional_field_element_id + ' ' + 'select[name="rangetime' + bk_type + '"]';
            time_slot_field_name2 = conditional_field_element_id + ' ' + 'select[name="rangetime' + bk_type + '[]"]';       
        }
    }
    
   // Get dates and time from aproved dates
   if(typeof(date_approved[ bk_type ]) !== 'undefined')
   if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined') {
      for ( i=0; i< date_approved[ bk_type ][ td_class ].length; i++){
         if( ( date_approved[ bk_type ][ td_class ][0][3] != 0) ||  ( date_approved[ bk_type ][ td_class ][0][4] != 0) ) {
            h = date_approved[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
            m = date_approved[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
            s = date_approved[ bk_type ][ td_class ][i][5];if (s == 2) s = '02';
            times_array[ times_array.length ] = [h,m,s];
         }
     }
   }

   // Get dates and time from pending dates
   if(typeof( date2approve[ bk_type ]) !== 'undefined')
   if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined')
      for ( i=0; i< date2approve[ bk_type ][ td_class ].length; i++){
        if( ( date2approve[ bk_type ][ td_class ][0][3] != 0) ||  ( date2approve[ bk_type ][ td_class ][0][4] != 0) ) {
            h = date2approve[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
            m = date2approve[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
            s = date2approve[ bk_type ][ td_class ][i][5];if (s == 2) s = '02';
            times_array[ times_array.length ] = [h,m,s];
          }
       }

    times_array.sort();

    //Customization Bence - make day with start and end time - unavailable
    //var is_start_here = false;
    //var is_end_here = false;
    //for (var jj=0; jj< times_array.length; jj++){
    //    if (times_array[jj][2]=='01' ) is_start_here = true;
    //    if (times_array[jj][2]=='02' ) is_end_here = true;
    //}
    //if ( (is_start_here) && (is_end_here) ) return true;

// check here according time ranges selection
// and check all slots for reserVATION.
// IF ALL SLOTS ARE RESERVED, INSIDE OF times_array
// SO THEN RETURN TRUE

    var is_element_exist = jQuery( time_slot_field_name+','+time_slot_field_name2 ).length;
    if (is_element_exist) {
        var my_timerange_value = jQuery( time_slot_field_name + ' option,'+time_slot_field_name2 + ' option');
        var my_st_en_times;
        var my_temp_time;
        var times_ranges_array=[];

        for (var j=0; j< my_timerange_value.length; j++){

            my_st_en_times = my_timerange_value[j].value.split(' - ');

            my_temp_time = my_st_en_times[0].split(':');
            times_ranges_array[ times_ranges_array.length ] = [ my_temp_time[0], my_temp_time[1], '01' ]; //Start time

            my_temp_time = my_st_en_times[1].split(':');
            times_ranges_array[ times_ranges_array.length ] = [ my_temp_time[0], my_temp_time[1], '02' ]; //End time
        }

        // check if all time slots from the selectbox are the booked inside of this day. Simple checking for the same
        if (times_array.length ==  times_ranges_array.length) {
            var is_all_same = true;
            for ( var i=0; i< times_array.length; i++){
                 if (
                      ( times_array[i][0] != times_ranges_array[i][0] ) ||
                      ( times_array[i][1] != times_ranges_array[i][1] ) ||
                      ( times_array[i][2] != times_ranges_array[i][2] )
                    )
                  is_all_same = false;
            }
            if ( is_all_same) return true;
        }

        //Check may be its not possible to select any other time slots from the selectbox, because its already booked, sothen mark this day as booked.
        if ((my_timerange_value.length > 0 ) && ( bk_days_selection_mode=='single' )  ){  // Only if range selections exist and we are have single days selections
           var removed_time_slots = is_time_slot_booked_for_this_time_array( bk_type, times_array );
           var some_exist_time_slots = [];
           var my_time_value = jQuery( time_slot_field_name + ' option,'+time_slot_field_name2 + ' option');

           for ( j=0; j< my_time_value.length; j++){

               if (  wpdev_in_array( removed_time_slots, j ) ) {

               } else {
                   some_exist_time_slots[some_exist_time_slots.length] = j;
               }
           }
           if (some_exist_time_slots.length == 0 ) return true;
        }

    }

    for ( var i=0; i< times_array.length; i++){  // s = 2 - end time,   s = 1 - start time
       s = parseInt( times_array[i][2] );

       if  (i == 0)
            if  (s !== 2)  {return false;} // Its not start at the start of day

       if ( i > 0 ) {

            if ( s == 1 )
                if  ( !( ( times_array[i-1][0] == times_array[i][0] ) &&  ( times_array[i-1][1] == times_array[i][1] ) ) ) {
                        return false; // previos time is not equal to current so we have some free interval
                }

       }

       if (i == ( times_array.length-1))
               if (s !== 1)   {return false;} // Its not end  at the end of day

    }
    return true;
}


function is_time_slot_booked_for_this_time_array( bk_type, times_array ){

    var time_slot_field_name = 'select[name="rangetime' + bk_type + '"]';
    var time_slot_field_name2 = 'select[name="rangetime' + bk_type + '[]"]';
    
    var start_time_slot_field_name = 'select[name="starttime' + bk_type + '"]';
    var start_time_slot_field_name2 = 'select[name="starttime' + bk_type + '[]"]';
        times_array.sort();
        var my_time_value = '';var j; var bk_time_slot_selection = ''; var minutes_booked; var minutes_slot; var my_range_time;

        var removed_time_slots = [];

        for ( var i=0; i< times_array.length; i++){  // s = 2 - end time,   s = 1 - start time
           s = parseInt( times_array[i][2] );

           if ( i > 0 ) {

                if ( s == 2 )
                    {
                       my_range_time = times_array[i-1][0] + ':' + times_array[i-1][1] + ' - ' + times_array[i][0] + ':' + times_array[i][1]  ;
                       my_time_value = jQuery( time_slot_field_name + ' option,'+time_slot_field_name2 + ' option,' + start_time_slot_field_name + ' option,'+start_time_slot_field_name2 + ' option');

                       for ( j=0; j< my_time_value.length; j++){

                          if (my_time_value[j].value == my_range_time ) {  // Mark as disable this option

                            removed_time_slots[ removed_time_slots.length ] = j;
                            //return  true;

                          } else {
                              // We will recheck here if, may  be some interval here inside of already booked intervals, so then we need to disable it.
                              bk_time_slot_selection = my_time_value[j].value;
                              var is_time_range = bk_time_slot_selection.indexOf("-");
                              
                              if ( is_time_range > -1 ) { // Timeslots
                                    bk_time_slot_selection = bk_time_slot_selection.split('-');
                                    bk_time_slot_selection[0] = jQuery.trim(bk_time_slot_selection[0]);
                                    bk_time_slot_selection[1] = jQuery.trim(bk_time_slot_selection[1]);

                                    bk_time_slot_selection[0] = bk_time_slot_selection[0].split(':');
                                    bk_time_slot_selection[1] = bk_time_slot_selection[1].split(':');

                                    // Get only minutes
                                    minutes_booked = [ (parseInt(times_array[i-1][0]*60) +  parseInt(times_array[i-1][1] )) ,                  (parseInt( times_array[i][0]*60) +  parseInt(times_array[i][1] ) ) ] ;
                                    minutes_slot   = [ (parseInt(bk_time_slot_selection[0][0]*60) +  parseInt(bk_time_slot_selection[0][1] )), (parseInt(bk_time_slot_selection[1][0]*60) +  parseInt(bk_time_slot_selection[1][1] ) ) ] ;


                                    if (
                                         ( ( minutes_booked[0] >= minutes_slot[0] ) && ( minutes_booked[0] < minutes_slot[1] ) ) ||
                                         ( ( minutes_booked[1] > minutes_slot[0] ) && ( minutes_booked[1] <= minutes_slot[1] ) )
                                     ||
                                         ( ( minutes_slot[0] >= minutes_booked[0] ) && ( minutes_slot[0] < minutes_booked[1] ) ) ||
                                         ( ( minutes_slot[1] > minutes_booked[0] ) && ( minutes_slot[1] <= minutes_booked[1] ) )
                                       )
                                    {
                                        removed_time_slots[ removed_time_slots.length ] = j;
                                        //return  true;
                                    }
                              } else { // Just  some time (like start time)
                                    bk_time_slot_selection = bk_time_slot_selection.split(':');
                                  
                                    // Get only minutes
                                    minutes_booked = [ (parseInt(times_array[i-1][0]*60) +  parseInt(times_array[i-1][1] )) ,                  (parseInt( times_array[i][0]*60) +  parseInt(times_array[i][1] ) ) ] ;
                                    minutes_slot   = [ (parseInt(bk_time_slot_selection[0]*60) +  parseInt(bk_time_slot_selection[1] )) ] ;
                                    if (
                                         ( ( minutes_slot[0] >= minutes_booked[0] ) && ( minutes_slot[0] < minutes_booked[1] ) )
                                       )
                                    {
                                        removed_time_slots[ removed_time_slots.length ] = j;
                                        //return  true;
                                    }
                                  
                              }

                          }


                       }
                    }

           }

        }



    return  removed_time_slots ;


}


function hoverDayTime(value, date, bk_type) {

    if (date == null) return;

    var i=0 ;var h ='' ;var m ='' ;var s='';
    var td_class;


   // Gathering information hint for tooltips ////////////////////////////////
   var tooltip_time = '';
   var times_array = [];
   td_class =  (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();

   // Get dates and time from aproved dates
   if(typeof(date_approved[ bk_type ]) !== 'undefined')
   if(typeof(date_approved[ bk_type ][ td_class ]) !== 'undefined') {
     if( ( date_approved[ bk_type ][ td_class ][0][3] != 0) ||  ( date_approved[ bk_type ][ td_class ][0][4] != 0) ) {
         for ( i=0; i< date_approved[ bk_type ][ td_class ].length; i++){
            h = date_approved[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
            m = date_approved[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
            s = date_approved[ bk_type ][ td_class ][i][5];if (s == 2) s = '02';
            times_array[ times_array.length ] = [h,m,s];
         }
     }
   }

   // Get dates and time from pending dates
   if(typeof( date2approve[ bk_type ]) !== 'undefined')
   if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined')
     if( ( date2approve[ bk_type ][ td_class ][0][3] != 0) ||  ( date2approve[ bk_type ][ td_class ][0][4] != 0) ) //check for time here
       {for ( i=0; i< date2approve[ bk_type ][ td_class ].length; i++){
            h = date2approve[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
            m = date2approve[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
            s = date2approve[ bk_type ][ td_class ][i][5];if (s == 2) s = '02';
            times_array[ times_array.length ] = [h,m,s];
          }
       }

//alert(times_array);
   // Time availability
   if (typeof( hover_day_check_global_time_availability ) == 'function') {times_array = hover_day_check_global_time_availability( date, bk_type ,times_array);}

    times_array.sort();
// if (times_array.length>0) alert(times_array);
    for ( i=0; i< times_array.length; i++){  // s = 2 - end time,   s = 1 - start time
       s = parseInt( times_array[i][2] );
       if (s == 2) {if (tooltip_time == '') tooltip_time = '&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp; - ';}      // End time and before was no dates so its start from start of date
       if ( (tooltip_time == '') && (times_array[i][0]=='00') && (times_array[i][1]=='00') )
           tooltip_time = '&nbsp;&nbsp;&nbsp;&nbsp;...&nbsp;&nbsp;&nbsp;';  //start date at the midnight
       else if ( (i == ( times_array.length-1)) && (times_array[i][0]=='23') && (times_array[i][1]=='59') )
        tooltip_time += ' &nbsp;&nbsp;&nbsp;&nbsp;... ';
       else {
        var hours_show = times_array[i][0];
        var hours_show_sufix = '';
        if (is_am_pm_inside_time) {
            if (hours_show>=12) {
                hours_show = hours_show - 12;
                if (hours_show==0) hours_show = 12;
                hours_show_sufix = ' pm';
            } else {
                hours_show_sufix = ' am';
            }
        }
//Customization of bufer time for DAN
if (times_array[i][2] == '02' ) {
    times_array[i][1] = ( times_array[i][1]*1)  + time_buffer_value ;
    if (times_array[i][1] > 59 ) {
        times_array[i][1] = times_array[i][1] - 60;
        hours_show = (hours_show*1) + 1;
    }
    if (times_array[i][1] < 10 ) times_array[i][1] = '0'+times_array[i][1];
}

        tooltip_time += hours_show + ':' + times_array[i][1] + hours_show_sufix;
       }


       if (s == 1) {tooltip_time += ' - ';if (i == ( times_array.length-1)) tooltip_time += ' &nbsp;&nbsp;&nbsp;&nbsp;... ';}
       if (s == 2) {
           tooltip_time += get_additional_info_for_tooltip( bk_type , td_class , times_array[i][0] + ':' + times_array[i][1] );
           tooltip_time += '<br />';
       } /**/
    }

    // jQuery( '#calendar_booking'+bk_type+' td.cal4date-'+td_class )  // TODO: continue working here, check unshow times at full booked days
    if ( tooltip_time.indexOf("undefined") > -1 ) {tooltip_time = '';}
    else {
        if ( (tooltip_time != '') && (bk_highlight_timeslot_word !='') ) {
            if ( is_booking_used_check_in_out_time === true )                   // Disable showing time tooltip,  if we are using check in/out times
                tooltip_time = '';
            else
                tooltip_time = '<span class="wpbc_booked_times_word">'+bk_highlight_timeslot_word + '</span><br />' + tooltip_time ;
        }
    }
    if(typeof( getDayPrice4Show ) == 'function') {tooltip_time = getDayPrice4Show(bk_type, tooltip_time, td_class);}  
    if(typeof( getDayAvailability4Show ) == 'function') {tooltip_time = getDayAvailability4Show(bk_type, tooltip_time, td_class);}  

    //tooltip_time = 'Already booked time slots: </br>' + tooltip_time ;
    jQuery( '#calendar_booking'+bk_type+' td.cal4date-'+td_class ).attr('data-content', tooltip_time ) ;
    
    ////////////////////////////////////////////////////////////////////////

}

function get_additional_info_for_tooltip( bk_type , td_class , times_array ){
    
    if ( (bk_show_info_in_form == undefined) || (! bk_show_info_in_form) )
            return '';
    // TODO: stop working here according names in tooltips
    //var id_was_here = [];

    var return_variable = '<span style=\"font-weight:normal !important;font-size:11px !important;\">';
    var posi = 0;
    var this_booking_end_time = '';

    for(var ik=0 ; ik< dates_additional_info[ bk_type ][ td_class ].length; ik++) {

        
        if (dates_additional_info[ bk_type ][ td_class ][ik][ 'endtime' ] != undefined ) {              // if ENDTIME shortcode is used in the Booking Form
            
            this_booking_end_time = dates_additional_info[ bk_type ][ td_class ][ik][ 'endtime' ];
           
        } else if (dates_additional_info[ bk_type ][ td_class ][ik][ 'rangetime' ] != undefined ) {     // if in Booking form was RANGETIME shortcode
            
            posi = dates_additional_info[ bk_type ][ td_class ][ik][ 'rangetime' ].indexOf( ' - ' );
            this_booking_end_time = dates_additional_info[ bk_type ][ td_class ][ik][ 'rangetime' ].substr(posi + 3 );
        }
            
        if ( this_booking_end_time == times_array ) {


           return_variable +=  ' - ';
           if (dates_additional_info[ bk_type ][ td_class ][ik][ 'name' ] != undefined)
                return_variable +=  dates_additional_info[ bk_type ][ td_class ][ik][ 'name' ] ;

           if (dates_additional_info[ bk_type ][ td_class ][ik][ 'secondname' ] != undefined)
                return_variable += ' ' + dates_additional_info[ bk_type ][ td_class ][ik][ 'secondname' ] ;


           if (dates_additional_info[ bk_type ][ td_class ][ik] [ 'details2' ] != undefined)
                return_variable +='<br /> ' + dates_additional_info[ bk_type ][ td_class ][ik] [ 'details2' ] + '';
            
           return_variable += '</span>'


           return return_variable;
       }
       /* if ( ! wpdev_in_array(id_was_here, dates_additional_info[ bk_type ][ td_class ][ik] [ 'id' ] ) ) {
         id_was_here[id_was_here.length] =  dates_additional_info[ bk_type ][ td_class ][ik] [ 'id' ];
         tooltip_time +=  dates_additional_info[ bk_type ][ td_class ][ik] [ 'name' ] + '>' + dates_additional_info[ bk_type ][ td_class ][ik] [ 'endtime' ];
       }/**/
    }
    return '';
}

function isTimeTodayGone(myTime, sort_date_array){
    var date_to_check = sort_date_array[0];
    if (is_check_start_time_gone == false) {
        date_to_check = sort_date_array[ (sort_date_array.length-1) ];
    }
    
    if (parseInt(date_to_check[0]) < parseInt(wpdev_bk_today[0])) return true;
    if (( parseInt(date_to_check[0]) == parseInt(wpdev_bk_today[0])  ) && ( parseInt(date_to_check[1]) < parseInt(wpdev_bk_today[1])  ) )
        return true;
    if (( parseInt(date_to_check[0]) == parseInt(wpdev_bk_today[0])  ) && ( parseInt(date_to_check[1]) == parseInt(wpdev_bk_today[1])  ) && ( parseInt(date_to_check[2]) < parseInt(wpdev_bk_today[2])  ) )
        return true;
    if (( parseInt(date_to_check[0]) == parseInt(wpdev_bk_today[0])  ) &&
        ( parseInt(date_to_check[1]) == parseInt(wpdev_bk_today[1])  ) &&
        ( parseInt(date_to_check[2]) == parseInt(wpdev_bk_today[2])  )) {
        var mytime_value = myTime.split(":");
        mytime_value = mytime_value[0]*60 + parseInt(mytime_value[1]);

        var current_time_value = wpdev_bk_today[3]*60 + parseInt(wpdev_bk_today[4]);

        if ( current_time_value  > mytime_value ) return true;

    }
    return false;
}


var start_time_checking_index;

function checkTimeInside( mytime, is_start_time, bk_type ) {

        // Check time availability for global filters
        if(typeof( check_entered_time_to_global_availability_time ) == 'function') {if (! check_entered_time_to_global_availability_time(mytime, is_start_time, bk_type) ) return false;}

        var my_dates_str = document.getElementById('date_booking'+ bk_type ).value;                 // GET DATES From TEXTAREA

        return checkTimeInsideProcess( mytime, is_start_time, bk_type, my_dates_str );

}


function checkRecurentTimeInside( my_rangetime,  bk_type ) {

   var valid_time = true;
   var my_dates_str = document.getElementById('date_booking'+ bk_type ).value;                 // GET DATES From TEXTAREA
    // recurrent time check for all days in loop

    var date_array = my_dates_str.split(", ");
    if (date_array.length == 2) { // This recheck is need for editing booking, with single day
        if (date_array[0]==date_array[1]) {
            date_array = [ date_array[0] ];
        }
    }
    var temp_date_str = '';
    for (var i=0; i< date_array.length; i++) {  // Get SORTED selected days array
            temp_date_str = date_array[i];
            if ( checkTimeInsideProcess( my_rangetime[0], true, bk_type, temp_date_str ) == false )   valid_time = false;
            if ( checkTimeInsideProcess( my_rangetime[1], false, bk_type, temp_date_str ) == false )  valid_time = false;

    }

    return valid_time;
}


// Function check start and end time at selected days
function checkTimeInsideProcess( mytime, is_start_time, bk_type, my_dates_str ) {


    var date_array = my_dates_str.split(", ");
    if (date_array.length == 2) { // This recheck is need for editing booking, with single day
        if (date_array[0]==date_array[1]) {
            date_array = [ date_array[0] ];
        }
    }

    var temp_elemnt;var td_class;var sort_date_array = [];var work_date_array = [];var times_array = [];var is_check_for_time;

    for (var i=0; i< date_array.length; i++) {  // Get SORTED selected days array
        temp_elemnt = date_array[i].split(".");
        sort_date_array[i] = [ temp_elemnt[2], temp_elemnt[1] + '', temp_elemnt[0] + '' ]; // [2009,7,1],...
    }
    sort_date_array.sort();                                                                   // SORT    D a t e s
    for (i=0; i< sort_date_array.length; i++) {                                  // trnasform to integers
        sort_date_array[i] = [ parseInt(sort_date_array[i][0]*1), parseInt(sort_date_array[i][1]*1), parseInt(sort_date_array[i][2]*1) ]; // [2009,7,1],...
    }

    if (  ((is_check_start_time_gone) && (is_start_time)) || 
          ((! is_check_start_time_gone) && (! is_start_time)) ) {

        if ( isTimeTodayGone(mytime, sort_date_array) )  return false;
    }
    //  CHECK FOR BOOKING INSIDE OF     S E L E C T E D    DAY RANGE AND FOR TOTALLY BOOKED DAYS AT THE START AND END OF RANGE
    work_date_array =  sort_date_array;
    for (var j=0; j< work_date_array.length; j++) {
        td_class =  work_date_array[j][1] + '-' + work_date_array[j][2] + '-' + work_date_array[j][0];

        if ( (j==0) || (j == (work_date_array.length-1)) ) is_check_for_time = true;         // Check for time only start and end time
        else                                               is_check_for_time = false;

        // Get dates and time from pending dates
        if(typeof( date2approve[ bk_type ]) !== 'undefined') {
          if ( (typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined') ) {
             if (! is_check_for_time) {return false;} // its mean that this date is booked inside of range selected dates
             if( ( date2approve[ bk_type ][ td_class ][0][3] != 0) ||  ( date2approve[ bk_type ][ td_class ][0][4] != 0) ) {
                 // Evrything good - some time is booked check later
             } else {return false;} // its mean that this date tottally booked
          }
        }

        // Get dates and time from pending dates
        if(typeof( date_approved[ bk_type ]) !== 'undefined') {
          if ( (typeof( date_approved[ bk_type ][ td_class ]) !== 'undefined') ) {
             if (! is_check_for_time) {return false;} // its mean that this date is booked inside of range selected dates
             if( ( date_approved[ bk_type ][ td_class ][0][3] != 0) ||  ( date_approved[ bk_type ][ td_class ][0][4] != 0) ) {
                 // Evrything good - some time is booked check later
             } else {return false;} // its mean that this date tottally booked
          }
        }
    }  ///////////////////////////////////////////////////////////////////////////////////////////////////////


     // Check    START   OR    END   time for time no in correct fee range
     if (is_start_time ) work_date_array =  sort_date_array[0] ;
     else                work_date_array =  sort_date_array[sort_date_array.length-1] ;

     td_class =  work_date_array[1] + '-' + work_date_array[2] + '-' + work_date_array[0];

        // Get dates and time from pending dates
        if(typeof( date2approve[ bk_type ]) !== 'undefined')
          if(typeof( date2approve[ bk_type ][ td_class ]) !== 'undefined')
              for ( i=0; i< date2approve[ bk_type ][ td_class ].length; i++){
                h = date2approve[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
                m = date2approve[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
                s = date2approve[ bk_type ][ td_class ][i][5];

//Customization of bufer time for DAN
if (s == '02') {
    m = ( m*1 )  + time_buffer_value ;
    if (m > 59 ) {
        m = m - 60;
        h = (h*1) + 1;
    }
    if (m < 10 ) m = '0'+m;
}

                times_array[ times_array.length ] = [h,m,s];
              }

        // Get dates and time from pending dates
        if(typeof( date_approved[ bk_type ]) !== 'undefined')
          if(typeof( date_approved[ bk_type ][ td_class ]) !== 'undefined')
              for ( i=0; i< date_approved[ bk_type ][ td_class ].length; i++){
                h = date_approved[ bk_type ][ td_class ][i][3];if (h < 10) h = '0' + h;if (h == 0) h = '00';
                m = date_approved[ bk_type ][ td_class ][i][4];if (m < 10) m = '0' + m;if (m == 0) m = '00';
                s = date_approved[ bk_type ][ td_class ][i][5];

//Customization of bufer time for DAN
if (s == '02') {
    m = ( m*1 )  + time_buffer_value ;
    if (m > 59 ) {
        m = m - 60;
        h = (h*1) + 1;
    }
    if (m < 10 ) m = '0'+m;
}


                times_array[ times_array.length ] = [h,m,s];
              }


        times_array.sort();                     // SORT TIMES

        var times_in_day = [];                  // array with all times
        var times_in_day_interval_marks = [];   // array with time interval marks 1- stsrt time 2 - end time


        for ( i=0; i< times_array.length; i++){s = times_array[i][2];         // s = 2 - end time,   s = 1 - start time
           // Start close interval
           if ( (s == 2) &&  (i == 0) ) {times_in_day[ times_in_day.length ] = 0;times_in_day_interval_marks[times_in_day_interval_marks.length]=1;}
           // Normal
           times_in_day[ times_in_day.length ] = times_array[i][0] * 60 + parseInt(times_array[i][1]);
           times_in_day_interval_marks[times_in_day_interval_marks.length]=s;
           // End close interval
           if ( (s == 1) &&  (i == (times_array.length-1)) ) {times_in_day[ times_in_day.length ] = (24*60);times_in_day_interval_marks[times_in_day_interval_marks.length]=2;}
        }

        // Get time from entered time
        var mytime_value = mytime.split(":");
        mytime_value = mytime_value[0]*60 + parseInt(mytime_value[1]);

//alert('My time:'+ mytime_value + '  List of times: '+ times_in_day + '  Saved indexes: ' + start_time_checking_index + ' Days: ' + sort_date_array ) ;

        var start_i = 0;
        if (start_time_checking_index != undefined)
            if (start_time_checking_index[0] != undefined)
                if ( (! is_start_time) && (sort_date_array.length == 1) ) {start_i = start_time_checking_index[0]; /*start_i++;*/}
        i=start_i;

        // Main checking inside a day
        for ( i=start_i; i< times_in_day.length; i++){
            times_in_day[i] = parseInt(times_in_day[i]);
            mytime_value = parseInt(mytime_value);
            if (is_start_time ) {
                if ( mytime_value > times_in_day[i] ){
                    // Its Ok, lets Loop to next item
                } else if ( mytime_value == times_in_day[i] ) {
                    if (times_in_day_interval_marks[i] == 1 ) {return false;     //start time is begin with some other interval
                    } else {
                        if ( (i+1) <= (times_in_day.length-1) ) {
                            if ( times_in_day[i+1] <= mytime_value ) return false;  //start time  is begin with next elemnt interval
                            else  {                                                 // start time from end of some other
                                if (sort_date_array.length > 1)
                                    if ( (i+1) <= (times_in_day.length-1) ) return false;   // Its mean that we make end booking at some other day then this and we have some booking time at this day after start booking  - its wrong
                                start_time_checking_index = [i, td_class,mytime_value];
                                return true;
                            }
                        }
                        if (sort_date_array.length > 1)
                            if ( (i+1) <= (times_in_day.length-1) ) return false;   // Its mean that we make end booking at some other day then this and we have some booking time at this day after start booking  - its wrong
                        start_time_checking_index = [i, td_class,mytime_value];
                        return true;                                            // start time from end of some other
                    }
                } else if ( mytime_value < times_in_day[i] ) {
                    if (times_in_day_interval_marks[i] == 2 ){return false;     // start time inside of some interval
                    } else {
                        if (sort_date_array.length > 1)
                            if ( (i+1) <= (times_in_day.length-1) ) return false;   // Its mean that we make end booking at some other day then this and we have some booking time at this day after start booking  - its wrong
                        start_time_checking_index = [i, td_class,mytime_value];
                        return true;
                    }
                }
            } else {
                if (sort_date_array.length == 1) {

                   if (start_time_checking_index !=undefined)
                       if (start_time_checking_index[2]!=undefined)

                            if ( ( start_time_checking_index[2] == times_in_day[i] ) && ( times_in_day_interval_marks[i] == 2) ) {    // Good, because start time = end of some other interval and we need to get next interval for current end time.
                            } else if ( times_in_day[i] < mytime_value ) return false;                 // some interval begins before end of curent "end time"
                            else {
                                if (start_time_checking_index[2]>= mytime_value) return false;  // we are select only one day and end time is earlythe starttime its wrong
                                return true;                                                    // if we selected only one day so evrything is fine and end time no inside some other intervals
                            }
                } else {
                    if ( times_in_day[i] < mytime_value ) return false;                 // Some other interval start before we make end time in the booking at the end day selection
                    else                                  return true;
                }

            }
        }

        if (is_start_time )  start_time_checking_index = [i, td_class,mytime_value];
        else {
           if (start_time_checking_index !=undefined)
               if (start_time_checking_index[2]!=undefined)
                    if ( (sort_date_array.length == 1) && (start_time_checking_index[2]>= mytime_value) ) return false;  // we are select only one day and end time is earlythe starttime its wrong
        }
        return true;
}


function save_this_booking_cost(booking_id, cost, wpdev_active_locale){

    if (cost!='') {
            var ajax_bk_message = 'Updating...';
            
            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;
            var ajax_type_action='SAVE_BK_COST';

            jQuery.ajax({                                           // Start Ajax Sending
                // url: wpdev_ajax_path,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond' ).html( data ) ;},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action,
                    booking_id : booking_id,
                    cost : cost,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
            });
            return false;
        }
        return true;
}


function sendPaymentRequestByEmail(payment_request_id , request_reason, user_id, wpdev_active_locale) {     //FixIn:5.4.5.6 - user_id: user_id,
 
            // FixIn: 5.4.5
           wpdev_active_locale = wpbc_get_selected_locale(payment_request_id,  wpdev_active_locale );

            var ajax_bk_message = 'Sending...';

            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;
            var ajax_type_action='SEND_PAYMENT_REQUEST';

            jQuery.ajax({                                           // Start Ajax Sending
                // url: wpdev_ajax_path,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond' ).html( data ) ;},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action,
                    booking_id : payment_request_id,
                    reason : request_reason,
                    user_id: user_id,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
            });
            return false;


}


// Chnage the booking status of booking
function chnage_booking_payment_status(booking_id, payment_status, payment_status_show) {

            var ajax_bk_message = 'Updating...';

            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+ajax_bk_message+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            var wpdev_ajax_path = wpdev_bk_plugin_url+'/' + wpdev_bk_plugin_filename ;
            var ajax_type_action='CHANGE_PAYMENT_STATUS';

            jQuery.ajax({                                           // Start Ajax Sending
                // url: wpdev_ajax_path,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond' ).html( data ) ;},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    // ajax_action : ajax_type_action,
                    action : ajax_type_action,
                    booking_id : booking_id,
                    payment_status : payment_status,
                    payment_status_show: payment_status_show,
                    wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce').value 
                }
            });
}


function wpbc_print_specific_booking( booking_id ){
    jQuery("#print_loyout_content").html( jQuery("#booking_print_loyout").html()  ) ;
    
    jQuery(".wpbc_print_rows").hide();
    jQuery("#wpbc_print_row" + booking_id ).show();
    jQuery(".modal-footer").hide();
    
    jQuery("#printLoyoutModal").modal("show");    
}
