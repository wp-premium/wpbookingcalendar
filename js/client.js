var is_booking_without_payment = false;
var date_approved = [];
var date2approve = [];
var date_admin_blank = [];
var dates_additional_info = [];
var is_all_days_available = [];
var avalaibility_filters = [];
var is_show_cost_in_tooltips = false;
var is_show_cost_in_date_cell = false;
var is_show_availability_in_tooltips = false;
var global_avalaibility_times = [];   
var numbb = 0;
//var is_use_visitors_number_for_availability;
var timeoutID_of_thank_you_page = null;    


// Calendar Initialisation /////////////////////////////////////////////////
function init_datepick_cal(bk_type,  date_approved_par, my_num_month, start_day_of_week, start_bk_month  ){

        if ( jQuery('#calendar_booking'+ bk_type).hasClass('hasDatepick') == true ) { // If the calendar with the same Booking resource is activated already, then exist.
            return false;
        }

        var cl = document.getElementById('calendar_booking'+ bk_type);if (cl === null) return; // Get calendar instance and exit if its not exist

        date_approved[ bk_type ] = date_approved_par;

        var isRangeSelect = false;
        var bkMultiDaysSelect   = 365;
        if ( bk_days_selection_mode==='dynamic' ) { isRangeSelect     = true; bkMultiDaysSelect = 0; }
        if ( bk_days_selection_mode==='single' )    bkMultiDaysSelect = 0;

        var bkMinDate = 0;
        var bkMaxDate = booking_max_monthes_in_calendar;

        var is_this_admin = false;
        if ( 
                ( location.href.indexOf('wpdev-booking.phpwpdev-booking-reservation') != -1 ) 
             && ( location.href.indexOf('booking_hash') != -1 ) 
            ) {
            is_this_admin = true; 
            bkMinDate = null;
            bkMaxDate = null;
        }

        function click_on_cal_td(){
            if(typeof( selectDayPro ) == 'function') {selectDayPro(  bk_type);}
        }

        function selectDay(date) { 

            if(typeof( bkRangeDaysSelection ) == 'function') { // Check if this minimum BS version, and then proced
                jQuery('.datepick-days-cell' ).popover('hide');
            }
            jQuery('#date_booking' + bk_type).val(date);              
            if(typeof( selectDayPro ) == 'function') {selectDayPro( date, bk_type);}
            jQuery( ".booking_form_div" ).trigger( "date_selected" , [ bk_type, date ] );
        }

        function hoverDay(value, date){ 

            if(typeof( hoverDayTime ) == 'function') {hoverDayTime(value, date, bk_type);}

            if ( (location.href.indexOf('wpdev-booking.phpwpdev-booking')==-1) ||
                 (location.href.indexOf('wpdev-booking.phpwpdev-booking-reservation')>0) )
            { // Do not show it (range) at the main admin page
                if(typeof( hoverDayPro ) == 'function')  {hoverDayPro(value, date, bk_type);}
            }
            //if(typeof( hoverAdminDay ) == 'function')  { hoverAdminDay(value, date, bk_type); }
         }

        function applyCSStoDays(date ){
            var class_day = (date.getMonth()+1) + '-' + date.getDate() + '-' + date.getFullYear();
            var additional_class = ' wpbc_weekday_' + date.getDay() + '  ';
            if(typeof( prices_per_day  ) !== 'undefined')
                if(typeof(  prices_per_day[bk_type] ) !== 'undefined')
                    if(typeof(  prices_per_day[bk_type][class_day] ) !== 'undefined') {
                        additional_class += ' rate_'+prices_per_day[bk_type][class_day];
                    }

            // define season filter names as classes
            if(typeof( wpdev_bk_season_filter  ) !== 'undefined')
                    if(typeof(  wpdev_bk_season_filter[class_day] ) !== 'undefined') {
                        additional_class += ' '+wpdev_bk_season_filter[class_day].join(' ');
                    }

            if (is_this_admin == false) {
                var my_test_date = new Date( wpdev_bk_today[0],(wpdev_bk_today[1]-1), wpdev_bk_today[2] ,0,0,0 );  //Get today           
                if ( (days_between( date, my_test_date)) < block_some_dates_from_today ) 
                    return [false, 'cal4date-' + class_day +' date_user_unavailable']; 
                
                if( typeof( wpbc_available_days_num_from_today  ) !== 'undefined')
                    if ( parseInt( '0' + wpbc_available_days_num_from_today ) > 0 )
                        if ( (days_between( date, my_test_date)) > parseInt( '0' + wpbc_available_days_num_from_today ) ) 
                            return [false, 'cal4date-' + class_day +' date_user_unavailable']; 
            }

            if (typeof( is_this_day_available ) == 'function') {
                var is_day_available = is_this_day_available( date, bk_type);
              //if (! is_day_available) {return [false, 'cal4date-' + class_day +' date_user_unavailable date_approved'];}
                var season_filter = '';                                         //FixIn: 6.0.1.8
                if ( is_day_available instanceof Array ) { 
                    season_filter = ' season_filter_id_' + is_day_available[1]; 
                    is_day_available = is_day_available[0];                     
                }
                if (! is_day_available) {return [false, 'cal4date-' + class_day +' date_user_unavailable ' + season_filter ];}
                
            }

            // Time availability
            if (typeof( check_global_time_availability ) == 'function') {check_global_time_availability( date, bk_type );}

            var blank_admin_class_day = '';
            if(typeof(date_admin_blank[ bk_type ]) !== 'undefined')
                if(typeof(date_admin_blank[ bk_type ][ class_day ]) !== 'undefined') {
                    blank_admin_class_day = ' date_admin_blank ';
                }

            // Check availability per day for BL
            var reserved_days_count = 1;
            if(typeof(availability_per_day) !== 'undefined')
            if(typeof(availability_per_day[ bk_type ]) !== 'undefined')
               if(typeof(availability_per_day[ bk_type ][ class_day ]) !== 'undefined') {
                  reserved_days_count = parseInt( availability_per_day[ bk_type ][ class_day ] );}

            // Number of Check In Dates for BL      
            var checkin_days_count = [0 ,0];
            if(typeof(wpbc_check_in_dates) !== 'undefined')
            if(typeof(wpbc_check_in_dates[ bk_type ]) !== 'undefined')
               if(typeof(wpbc_check_in_dates[ bk_type ][ class_day ]) !== 'undefined') {
                   // [ Number of check in bookings, Pending or Approved status ]
                  checkin_days_count = [ wpbc_check_in_dates[ bk_type ][ class_day ][ 0 ] ,  wpbc_check_in_dates[ bk_type ][ class_day ][ 1 ] ];
              }

            // Number of Check Out Dates for BL 
            var checkout_days_count = [0 ,0];
            if(typeof(wpbc_check_out_dates) !== 'undefined')
            if(typeof(wpbc_check_out_dates[ bk_type ]) !== 'undefined')
               if(typeof(wpbc_check_out_dates[ bk_type ][ class_day ]) !== 'undefined') {
                   // [ Number of check Out bookings, Pending or Approved status ]
                  checkout_days_count = [ wpbc_check_out_dates[ bk_type ][ class_day ][ 0 ] , wpbc_check_out_dates[ bk_type ][ class_day ][ 1 ] ];
              }

            // Booked both  check  in/out dates in the same child resources  
            var both_check_in_out_num = 0;
            if ( typeof( getNumberClosedCheckInOutDays ) == 'function' ) {                       
                  both_check_in_out_num =  getNumberClosedCheckInOutDays( bk_type, class_day );
            }


            // we have 0 available at this day - Only for resources, which have childs
            if (  wpdev_in_array( parent_booking_resources, bk_type ) )
                    if (reserved_days_count <= 0) {
//                      if ( ( reserved_days_count - both_check_in_out_num ) <= 0) { // This line does not check  about pending or approved. 
                            if(typeof(date2approve[ bk_type ]) !== 'undefined')
                               if(typeof(date2approve[ bk_type ][ class_day ]) !== 'undefined')
                                 return [false, 'cal4date-' + class_day +' date2approve date_unavailable_for_all_childs ' + blank_admin_class_day];
                             return [false, 'cal4date-' + class_day +' date_approved date_unavailable_for_all_childs ' + blank_admin_class_day];
                    }

            var th=0;
            var tm=0;
            var ts=0;
            var time_return_value = false;
            // Select dates which need to approve, its exist only in Admin
            if(typeof(date2approve[ bk_type ]) !== 'undefined')
               if(typeof(date2approve[ bk_type ][ class_day ]) !== 'undefined') {

                  for (var ia=0;ia<date2approve[ bk_type ][ class_day ].length;ia++) {

                      th = date2approve[ bk_type ][ class_day ][ia][3];
                      tm = date2approve[ bk_type ][ class_day ][ia][4];
                      ts = date2approve[ bk_type ][ class_day ][ia][5];
                      if ( ( th == 0 ) && ( tm == 0 ) && ( ts == 0 ) )
                          return [false, 'cal4date-' + class_day +' date2approve' + blank_admin_class_day]; // Orange
                      else {
                          if ( is_booking_used_check_in_out_time === true ) {
                              if (ts == '1')  additional_class += ' check_in_time' + ' check_in_time_date2approve';         //FixIn: 6.0.1.2
                              if (ts == '2')  additional_class += ' check_out_time'+ ' check_out_time_date2approve';        //FixIn: 6.0.1.2
                          }
                          time_return_value = [true, 'date_available cal4date-' + class_day +' date2approve timespartly' + additional_class]; // Times
                          if(typeof( isDayFullByTime ) == 'function') {
                              if ( isDayFullByTime(bk_type, class_day ) ) return [false, 'cal4date-' + class_day +' date2approve' + blank_admin_class_day]; // Orange
                          }
                      }

                  }

               }

            //select Approved dates
            if(typeof(date_approved[ bk_type ]) !== 'undefined')
              if(typeof(date_approved[ bk_type ][ class_day ]) !== 'undefined') {

                  for (var ia=0;ia<date_approved[ bk_type ][ class_day ].length;ia++) {

                      th = date_approved[ bk_type ][ class_day ][ia][3];
                      tm = date_approved[ bk_type ][ class_day ][ia][4];
                      ts = date_approved[ bk_type ][ class_day ][ia][5];
                      if ( ( th == 0 ) && ( tm == 0 ) && ( ts == 0 ) )
                        return [false, 'cal4date-' + class_day +' date_approved' + blank_admin_class_day]; //Blue or Grey in client
                      else {
                          if ( is_booking_used_check_in_out_time === true ) {
                              if (ts == '1')  additional_class += ' check_in_time' + ' check_in_time_date_approved';        //FixIn: 6.0.1.2
                              if (ts == '2')  additional_class += ' check_out_time'+ ' check_out_time_date_approved';       //FixIn: 6.0.1.2
                          }
                        time_return_value = [true,  'date_available cal4date-' + class_day +' date_approved timespartly' + additional_class]; // Times
                        if(typeof( isDayFullByTime ) == 'function') {
                            if ( isDayFullByTime(bk_type, class_day ) ) return [false, 'cal4date-' + class_day +' date_approved' + blank_admin_class_day]; // Blue or Grey in client
                        }
                      }

                  }
              }


            for (var i=0; i<user_unavilable_days.length;i++) {
                if (date.getDay()==user_unavilable_days[i])   return [false, 'cal4date-' + class_day +' date_user_unavailable' ];
            }

            var is_datepick_unselectable = '';
            var is_calendar_booking_unselectable= jQuery('#calendar_booking_unselectable' + bk_type);
            if (is_calendar_booking_unselectable.length != 0 ) {
                is_datepick_unselectable = 'datepick-unselectable'; //  //datepick-unselectable
            }




            var is_exist_check_in_out_for_parent_resource = Math.max( checkin_days_count[0], checkout_days_count[0] );                        

            if ( ( time_return_value !== false ) && ( is_exist_check_in_out_for_parent_resource == 0 ) ) { // Check  this only for single booking resources - is_exist_check_in_out_for_parent_resource == 0
                if ( is_booking_used_check_in_out_time === true ) {
                    // If the date is cehck in/out and the check in/out time is activated so  then  this date is unavailbale
                    if ( ( additional_class.indexOf('check_in_time') != -1 ) && ( additional_class.indexOf('check_out_time') != -1 ) ){ 
                        // Make this date unavailbale
                        time_return_value[0] = false;                             
                        //FixIn: 6.0.1.2                                                 
                        if ( ! (
                                    ( ( additional_class.indexOf('check_in_time_date_approved') != -1 ) && ( additional_class.indexOf('check_out_time_date2approve') != -1 ) )
                                  || ( ( additional_class.indexOf('check_out_time_date_approved') != -1 ) && ( additional_class.indexOf('check_in_time_date2approve') != -1 ) )
                            ) ) { 
                            // Remove CSS classes from this date
                            time_return_value[1]=time_return_value[1].replace("check_in_time",""); 
                            time_return_value[1]=time_return_value[1].replace("check_out_time",""); 
                            time_return_value[1]=time_return_value[1].replace("timespartly","");        
                        }
                        time_return_value[1]=time_return_value[1].replace("date_available",""); 
                    }
                }
                if (  (  wpdev_in_array( parent_booking_resources, bk_type ) ) && ( (reserved_days_count - both_check_in_out_num ) <= 0 ) ) {    //FixIn: 6.0.1.2
                    time_return_value[0] = false;  
                    time_return_value[1]=time_return_value[1].replace("check_in_time",""); 
                    time_return_value[1]=time_return_value[1].replace("check_out_time",""); 
                    time_return_value[1]=time_return_value[1].replace("timespartly","");                                
                    time_return_value[1]=time_return_value[1].replace("date_available","");                     
                }
                return time_return_value;

            } else { 

//                if ( ( is_booking_used_check_in_out_time === true ) && ( is_exist_check_in_out_for_parent_resource > 0 ) ) { // Check  Check  In / Out dates for the parent resources.
                  if ( 
                          ( is_booking_used_check_in_out_time === true ) 
                       && (  ( is_exist_check_in_out_for_parent_resource > 0 ) || ( (reserved_days_count - both_check_in_out_num ) <= 0 )  )
                    ) { // Check  Check  In / Out dates for the parent resources. // reserved_days_count - number of available items,  including check in/out dates ||  both_check_in_out_num number of items with both  check in/out   //FixIn: 6.0.1.12
                
                    // Unavailable 
                    if ( (reserved_days_count - both_check_in_out_num ) <= 0 ) {
                        // Check  Pending or Approved by the Check In date
                        if ( checkin_days_count[1] == 1 )   additional_class = ' date_approved';    
                        else                                additional_class = ' date2approve';                                                       
                        return [false, 'cal4date-' + class_day + additional_class + blank_admin_class_day]; 
                    }

                    // Recheck  if this date check in/out
                    if ( (reserved_days_count - both_check_in_out_num - checkin_days_count[0]) <= 0 ) {
                        if ( checkin_days_count[1] == 1 )   additional_class += ' date_approved';
                        else                                additional_class += ' date2approve';                           
                        additional_class += ' timespartly check_in_time';
                    }
                    if ( (reserved_days_count - both_check_in_out_num  - checkout_days_count[0]) <= 0 ) {
                        if ( checkout_days_count[1] == 1 )  additional_class += ' date_approved';
                        else                                additional_class += ' date2approve';
                        additional_class += ' timespartly check_out_time';
                    }                                               
                }

                return [true, 'date_available cal4date-' + class_day +' reserved_days_count' + reserved_days_count + ' '  + is_datepick_unselectable + additional_class+ ' '];
            }
        }

        function changeMonthYear(year, month){ 
            if(typeof( bkRangeDaysSelection ) == 'function') { // Check if this minimum BS version, and then proced
                if(typeof( prepare_tooltip ) == 'function') {
                    setTimeout("prepare_tooltip("+bk_type+");",1000);
                }
            }
            if(typeof( prepare_highlight ) == 'function') {
             setTimeout("prepare_highlight();",1000);
            }
        }
        // Configure and show calendar
        jQuery('#calendar_booking'+ bk_type).text('');
        jQuery('#calendar_booking'+ bk_type).datepick(
                {beforeShowDay: applyCSStoDays,
                    onSelect: selectDay,
                    onHover:hoverDay,
                    onChangeMonthYear:changeMonthYear,
                    showOn: 'both',
                    multiSelect: bkMultiDaysSelect,
                    numberOfMonths: my_num_month,
                    stepMonths: 1,
                    prevText: '&laquo;',
                    nextText: '&raquo;',
                    dateFormat: 'dd.mm.yy',
                    changeMonth: false, 
                    changeYear: false,
                    minDate: bkMinDate, maxDate: bkMaxDate, //'1Y',
                    //minDate: '01.01.2016', maxDate: '31.12.2016',             // Ability to set any  start and end date in calendar
                    showStatus: false,
                    multiSeparator: ', ',
                    closeAtTop: false,
                    firstDay:start_day_of_week,
                    gotoCurrent: false,
                    hideIfNoPrevNext:true,
                    rangeSelect:isRangeSelect,
                    // showWeeks: true, 
                    useThemeRoller :false // ui-cupertino.datepick.css
                }
        );


        if ( start_bk_month != false ) {
            var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
            inst.cursorDate = new Date();
            inst.cursorDate.setFullYear( start_bk_month[0], (start_bk_month[1]-1) ,  1 );
            inst.drawMonth = inst.cursorDate.getMonth();
            inst.drawYear = inst.cursorDate.getFullYear();

            jQuery.datepick._notifyChange(inst);
            jQuery.datepick._adjustInstDate(inst);
            jQuery.datepick._showDate(inst);
            jQuery.datepick._updateDatepick(inst);
        }


        if(typeof( bkRangeDaysSelection ) == 'function') { // Check if this minimum BS version, and then proced
            if(typeof( prepare_tooltip ) == 'function') {setTimeout("prepare_tooltip("+bk_type+");",1000);}
        }
}


////////////////////////////////////////////////////////////////////////////
// Days Selections - support functions 
////////////////////////////////////////////////////////////////////////////

// Get fisrst day of selection
function get_first_day_of_selection(dates) {

    // Multiple days selections
    if ( dates.indexOf(',') != -1 ){                  
        var dates_array =dates.split(/,\s*/);
        var length = dates_array.length;
        var element = null;
        var new_dates_array = [];

        for (var i = 0; i < length; i++) {

          element = dates_array[i].split(/\./);

          new_dates_array[new_dates_array.length] = element[2]+'.' + element[1]+'.' + element[0];       //2013.12.20
        }        
        new_dates_array.sort();

        element = new_dates_array[0].split(/\./);

        return element[2]+'.' + element[1]+'.' + element[0];                    //20.12.2013
    }

    // Range days selection
    if ( dates.indexOf(' - ') != -1 ){                  
        var start_end_date = dates.split(" - ");
        return start_end_date[0];
    }

    // Single day selection 
    return dates;                                                               //20.12.2013
}


// Get fisrst day of selection
function get_last_day_of_selection(dates) {

    // Multiple days selections
    if ( dates.indexOf(',') != -1 ){                  
        var dates_array =dates.split(/,\s*/);
        var length = dates_array.length;
        var element = null;
        var new_dates_array = [];

        for (var i = 0; i < length; i++) {

          element = dates_array[i].split(/\./);

          new_dates_array[new_dates_array.length] = element[2]+'.' + element[1]+'.' + element[0];       //2013.12.20
        }        
        new_dates_array.sort();

        element = new_dates_array[(new_dates_array.length-1)].split(/\./);

        return element[2]+'.' + element[1]+'.' + element[0];                    //20.12.2013
    }

    // Range days selection
    if ( dates.indexOf(' - ') != -1 ){                  
        var start_end_date = dates.split(" - ");
        return start_end_date[(start_end_date.length-1)];
    }

    // Single day selection 
    return dates;                                                               //20.12.2013
}


// Set selected days at calendar as UnAvailable
function setUnavailableSelectedDays( bk_type ){
    var sel_dates = jQuery('#calendar_booking'+bk_type).datepick('getDate');
    var class_day2;
    for( var i =0; i <sel_dates.length; i++) {
      class_day2 = (sel_dates[i].getMonth()+1) + '-' + sel_dates[i].getDate() + '-' + sel_dates[i].getFullYear();
      date_approved[ bk_type ][ class_day2 ] = [ (sel_dates[i].getMonth()+1) ,  sel_dates[i].getDate(),  sel_dates[i].getFullYear(),0,0,0];
      jQuery('#calendar_booking'+bk_type+' td.cal4date-'+class_day2).html(sel_dates[i].getDate());
      // jQuery('#calendar_booking'+bk_type).datepick('refresh');
    }
}


// Aftre reservation action is done
function setReservedSelectedDates( bk_type ){

    var is_pay_now = false;

    if (document.getElementById('calendar_booking'+bk_type) === null )  {
        document.getElementById('submiting' + bk_type).innerHTML = '';
        document.getElementById("booking_form_div"+bk_type).style.display="none";
        makeScroll('#ajax_respond_insert'+bk_type);

        if ( ( document.getElementById('paypalbooking_form'+bk_type) != null ) && 
             ( document.getElementById('paypalbooking_form'+bk_type).innerHTML != '' ) )
                is_pay_now = true;

        if ( (! is_pay_now) || ( is_booking_without_payment == true ) )             
            if (type_of_thank_you_message == 'page') {      // Page
                timeoutID_of_thank_you_page = setTimeout(function ( ) {location.href= thank_you_page_URL;} ,1000);
            } else {                                        // Message
                document.getElementById('submiting'+bk_type).innerHTML = '<div class=\"submiting_content wpdev-help-message alert alert-success\" >'+new_booking_title+'</div>';
                jQuery('.submiting_content').fadeOut( new_booking_title_time );
            }

    } else {

        setUnavailableSelectedDays(bk_type);                            // Set days as unavailable
        document.getElementById('date_booking'+bk_type).value = '';     // Set textarea date booking to ''

        jQuery('#calendar_booking'+bk_type+', .block_hints').hide();

        var is_admin = 0;
        if (location.href.indexOf('booking.php') != -1 ) {is_admin = 1;}
        if (is_admin == 0) {
            // Get calendar from the html and insert it before form div, which will hide after btn click
            jQuery('#calendar_booking'+bk_type).insertBefore("#booking_form_div"+bk_type);
            document.getElementById("booking_form_div"+bk_type).style.display="none";

            makeScroll('#ajax_respond_insert'+bk_type);

            if ( ( document.getElementById('paypalbooking_form'+bk_type) != null ) && 
                 ( document.getElementById('paypalbooking_form'+bk_type).innerHTML != '' ) )
                    is_pay_now = true;

            if ( (! is_pay_now) || ( is_booking_without_payment == true ) ) {
                if (type_of_thank_you_message == 'page') {      // Page
                    timeoutID_of_thank_you_page = setTimeout(function ( ) {location.href= thank_you_page_URL;} ,1000);
                } else {                                        // Message
                    document.getElementById('submiting'+bk_type).innerHTML = '<div class=\"submiting_content wpdev-help-message alert alert-success\" >'+new_booking_title+'</div>' ;
//                    + '<center><a class=\"btn\" href=\"javascript:boid(0);\" onclick=\"javascript:location.reload(true);\">Reload</a></center>';                    
//                    var my_summary = jQuery('.booking_summary').detach();
//                    my_summary.appendTo('#booking_form_div'+bk_type);
                                        
                    makeScroll('#submiting'+bk_type);
                    jQuery('.submiting_content').fadeOut( new_booking_title_time );
                }
            }

        } else {
            setTimeout(function ( ) {location.reload(true);} ,1000);
        }
    }
}


////////////////////////////////////////////////////////////////////////////
// Submit Booking Data 
////////////////////////////////////////////////////////////////////////////

// Check fields at form and then send request
function mybooking_submit( submit_form , bk_type, wpdev_active_locale){


    //Show message if no selected days
    if (document.getElementById('date_booking' + bk_type).value == '')  {

        if ( document.getElementById('additional_calendars' + bk_type) != null ) { // Checking according additional calendars.

            var id_additional_str = document.getElementById('additional_calendars' + bk_type).value; //Loop have to be here based on , sign
            var id_additional_arr = id_additional_str.split(',');
            var is_all_additional_days_unselected = true;
            for (var ia=0;ia<id_additional_arr.length;ia++) {
                if (document.getElementById('date_booking' + id_additional_arr[ia] ).value != '' ) {
                    is_all_additional_days_unselected = false;
                }
            }

            if (is_all_additional_days_unselected) {
                
                showMessageUnderElement( '#date_booking' + bk_type, message_verif_selectdts, '');                             
                makeScroll('#calendar_booking' + bk_type);            // Scroll to the calendar    
                
                //alert(message_verif_selectdts);
                return;
            }

        } else {
            //alert(message_verif_selectdts);
            showMessageUnderElement( '#date_booking' + bk_type, message_verif_selectdts, '');                             
            makeScroll('#calendar_booking' + bk_type);            // Scroll to the calendar    
            return;
        }
    }
    
    
    var count = submit_form.elements.length;
    var formdata = '';
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

                if (element.checked) 
                    inp_value = element.value; 
                else 
                    continue;

            } else {
                inp_value = element.value;
            }                      

            // Get value in selectbox of multiple selection
            if (element.type =='select-multiple') {
                inp_value = jQuery('[name="'+element.name+'"]').val() ;
                if (( inp_value == null ) || (inp_value.toString() == '' ))
                    inp_value='';
            }


            // Recheck for max num. available visitors selection
            if ( element.name == ('visitors'+bk_type) )
                if( typeof( is_max_visitors_selection_more_than_available ) == 'function' )
                    if ( is_max_visitors_selection_more_than_available( bk_type, inp_value, element ) )
                        return;

            // Recheck for max num. available visitors selection
            /*if ( element.name == ('phone'+bk_type) ) {
                // we validate a phone number of 10 digits with no comma, no spaces, no punctuation and there will be no + sign in front the number - See more at: http://www.w3resource.com/javascript/form/phone-no-validation.php#sthash.U9FHwcdW.dpuf
                var reg =  /^\d{10}$/;
                var message_verif_phone = "Please enter correctly phone number";
                if ( inp_value != '' )
                    if(reg.test(inp_value) == false) {showErrorMessage( element , message_verif_phone);return;}
            }*/

            // Validation Check --- Requred fields
            if ( element.className.indexOf('wpdev-validates-as-required') !== -1 ){             
                if  ((element.type =='checkbox') && ( element.checked === false)) {
                    if ( ! jQuery(':checkbox[name="'+element.name+'"]', submit_form).is(":checked") ) {
                        showErrorMessage( element , message_verif_requred_for_check_box);
                        return;                            
                    }
                }
                if  (element.type =='radio') {
                    if ( ! jQuery(':radio[name="'+element.name+'"]', submit_form).is(":checked") ) {
                        showErrorMessage( element , message_verif_requred_for_radio_box);
                        return;                            
                    }
                }
                if  ((element.type !='checkbox') && ( inp_value === '')) {
                    showErrorMessage( element , message_verif_requred);
                    return;
                }
            }

            // Validation Check --- Email correct filling field
            if ( element.className.indexOf('wpdev-validates-as-email') !== -1 ){   
                inp_value = inp_value.replace(/^\s+|\s+$/gm,'');                // Trim  white space //FixIn: 5.4.5
                var reg = /^([A-Za-z0-9_\-\.\+])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,})$/;
                if ( inp_value != '' )
                    if(reg.test(inp_value) == false) {showErrorMessage( element , message_verif_emeil);return;}
            }

            // Validation Check --- Same Email Field
            if ( ( element.className.indexOf('wpdev-validates-as-email') !== -1 ) && ( element.className.indexOf('same_as_') !== -1 ) ) { 

                // Get  the name of Primary Email field from the "same_as_NAME" class                    
                var primary_email_name = element.className.match(/same_as_([^\s])+/gi); 
                if (primary_email_name != null) { // We found
                    primary_email_name = primary_email_name[0].substr(8);

                    // Recehck if such primary email field exist in the booking form
                    if (jQuery('[name="' + primary_email_name + bk_type + '"]').length > 0) {

                        // Recheck the values of the both emails, if they do  not equla show warning                    
                        if ( jQuery('[name="' + primary_email_name + bk_type + '"]').val() !== inp_value ) {
                            showErrorMessage( element , message_verif_same_emeil );return;
                        }
                    }
                }
                // Skip one loop for the email veryfication field
                continue;
            }

            // Get Form Data
            if ( element.name !== ('captcha_input' + bk_type) ) {
                if (formdata !=='') formdata +=  '~';                                                // next field element

                el_type = element.type;
                if ( element.className.indexOf('wpdev-validates-as-email') !== -1 )  el_type='email';
                if ( element.className.indexOf('wpdev-validates-as-coupon') !== -1 ) el_type='coupon';

                inp_value = inp_value + '';
                inp_value = inp_value.replace(new RegExp("\\^",'g'), '&#94;'); // replace registered characters
                inp_value = inp_value.replace(new RegExp("~",'g'), '&#126;'); // replace registered characters

                inp_value = inp_value.replace(/"/g, '&#34;'); // replace double quot
                inp_value = inp_value.replace(/'/g, '&#39;'); // replace single quot

                formdata += el_type + '^' + element.name + '^' + inp_value ;                    // element attr
            }
        }

    }  // End Fields Loop


    // Recheck Times
    if( typeof( is_this_time_selections_not_available ) == 'function' )
        if ( is_this_time_selections_not_available( bk_type, submit_form.elements ) )
            return;


    if (document.getElementById('calendar_booking'+bk_type) != null ) {
        var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
        if (bk_days_selection_mode == 'dynamic') 
            if (bk_2clicks_mode_days_min != undefined) {
                if(typeof( check_conditions_for_range_days_selection_for_check_in ) == 'function') { 
                    var first_date  = get_first_day_of_selection(document.getElementById('date_booking' + bk_type).value);                  
                    var date_sections = first_date.split("."); 
                    var selceted_first_day = new Date;       
                    selceted_first_day.setFullYear( parseInt(date_sections[2]-0) ,parseInt(date_sections[1]-1), parseInt(date_sections[0]-0) );
                    check_conditions_for_range_days_selection_for_check_in(selceted_first_day, bk_type); 
                } 
                if (inst.dates.length < bk_2clicks_mode_days_min ) {
                    alert(message_verif_selectdts);
                    return;
                }
            }
    }

    // Cpatch  verify
    var captcha = document.getElementById('wpdev_captcha_challenge_' + bk_type);

    //Disable Submit button
    jQuery('#booking_form_div' + bk_type + ' input[type=button]').prop("disabled", true);
    if (captcha != null)  form_submit_send( bk_type, formdata, captcha.value, document.getElementById('captcha_input' + bk_type).value ,wpdev_active_locale);
    else                  form_submit_send( bk_type, formdata, '',            '' ,                                                      wpdev_active_locale);
    return;
}


// Gathering params for sending Ajax request and then send it
function form_submit_send( bk_type, formdata, captcha_chalange, user_captcha ,wpdev_active_locale){

    document.getElementById('submiting' + bk_type).innerHTML = '<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>';

    var my_booking_form = '';
    var my_booking_hash = '';
    if (document.getElementById('booking_form_type' + bk_type) != undefined)
        my_booking_form =document.getElementById('booking_form_type' + bk_type).value;

    if (wpdev_bk_edit_id_hash != '') my_booking_hash = wpdev_bk_edit_id_hash;

    var is_send_emeils= jQuery('#is_send_email_for_new_booking');
    if (is_send_emeils.length == 0 ) {
        is_send_emeils = 1;
    } else {
        is_send_emeils = is_send_emeils.attr('checked' );
        if (is_send_emeils == undefined) {is_send_emeils = 0 ;}
        if (is_send_emeils) is_send_emeils = 1;
        else                is_send_emeils = 0;
    }
    send_ajax_submit(bk_type,formdata,captcha_chalange,user_captcha,is_send_emeils,my_booking_hash,my_booking_form,wpdev_active_locale   ); // Ajax sending request

    var formdata_additional_arr;
    var formdata_additional;
    var my_form_field;
    var id_additional;
    var id_additional_str;
    var id_additional_arr;
    if (document.getElementById('additional_calendars' + bk_type) != null ) {

        id_additional_str = document.getElementById('additional_calendars' + bk_type).value; //Loop have to be here based on , sign
        id_additional_arr = id_additional_str.split(',');

        for (var ia=0;ia<id_additional_arr.length;ia++) {
            formdata_additional_arr = formdata;
            formdata_additional = '';
            id_additional = id_additional_arr[ia];


            formdata_additional_arr = formdata_additional_arr.split('~');
            for (var j=0;j<formdata_additional_arr.length;j++) {
                my_form_field = formdata_additional_arr[j].split('^');
                if (formdata_additional !=='') formdata_additional +=  '~';

                if (my_form_field[1].substr( (my_form_field[1].length -2),2)=='[]')
                  my_form_field[1] = my_form_field[1].substr(0, (my_form_field[1].length - (''+bk_type).length ) - 2 ) + id_additional + '[]';
                else
                  my_form_field[1] = my_form_field[1].substr(0, (my_form_field[1].length - (''+bk_type).length ) ) + id_additional ;


                formdata_additional += my_form_field[0] + '^' + my_form_field[1] + '^' + my_form_field[2];
            }


            if (document.getElementById('date_booking' + id_additional).value != '' ) {
                setUnavailableSelectedDays(id_additional);                                              // Set selected days unavailable in this calendar
                jQuery('#calendar_booking'+id_additional).insertBefore("#booking_form_div"+bk_type);    // Insert calendar before form to do not hide it
                if (document.getElementById('paypalbooking_form'+id_additional) != null)
                    jQuery('#paypalbooking_form'+id_additional).insertBefore("#booking_form_div"+bk_type);    // Insert payment form to do not hide it
                else {
                    jQuery("#booking_form_div"+bk_type).append('<div id="paypalbooking_form'+id_additional+'" ></div>');
                    jQuery("#booking_form_div"+bk_type).append('<div id="ajax_respond_insert'+id_additional+'" ></div>');
                }
                send_ajax_submit( id_additional ,formdata_additional,captcha_chalange,user_captcha,is_send_emeils,my_booking_hash,my_booking_form ,wpdev_active_locale  );
            }
        }
    }
}


//<![CDATA[
function send_ajax_submit(bk_type,formdata,captcha_chalange,user_captcha,is_send_emeils,my_booking_hash,my_booking_form  ,wpdev_active_locale ) {
        // Ajax POST here

        var my_bk_res = bk_type;
        if ( document.getElementById('bk_type' + bk_type) != null ) my_bk_res = document.getElementById('bk_type' + bk_type).value;

        jQuery.ajax({                                           // Start Ajax Sending
            // url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
            url: wpbc_ajaxurl, 
            type:'POST',
            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond_insert' + bk_type).html( data ) ;},
            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
            // beforeSend: someFunction,
            data:{
                // ajax_action : 'INSERT_INTO_TABLE',
                action : 'INSERT_INTO_TABLE',
                bktype: my_bk_res ,
                dates: document.getElementById('date_booking' + bk_type).value ,
                form: formdata,
                captcha_chalange:captcha_chalange,
                captcha_user_input: user_captcha,
                is_send_emeils : is_send_emeils,
                my_booking_hash:my_booking_hash,
                booking_form_type:my_booking_form,
                wpdev_active_locale:wpdev_active_locale,
                wpbc_nonce: document.getElementById('wpbc_nonce' + bk_type).value 
            }
        });
}
//]]>

////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////

// Show Error Message in Booking Form  at Front End
function showErrorMessage( element , errorMessage) {
    
    if(typeof( bk_form_step_click ) == 'function') {
        bk_form_step_click();                                                   // rollback  to 1st  step,  if system  will show warning and booking form  is using this customization: in the Exmaple #2 here: http://wpbookingcalendar.com/faq/customize-booking-form-for-having-several-steps-of-reservation/ 
    }
    
    makeScroll( element );

    jQuery("[name='"+ element.name +"']")
            .fadeOut( 350 ).fadeIn( 300 )
            .fadeOut( 350 ).fadeIn( 400 )
            .fadeOut( 350 ).fadeIn( 300 )
            .fadeOut( 350 ).fadeIn( 400 )
            .animate( {opacity: 1}, 4000 )
    ;  // mark red border
    if (jQuery("[name='"+ element.name +"']").attr('type') == "radio") {
        jQuery("[name='"+ element.name +"']").parent().parent().parent()
                .after('<span class="wpdev-help-message alert">'+ errorMessage +'</span>'); // Show message

    } else if (jQuery("[name='"+ element.name +"']").attr('type') == "checkbox") {
        jQuery("[name='"+ element.name +"']").parent()
                .after('<span class="wpdev-help-message alert">'+ errorMessage +'</span>'); // Show message

    } else {
        jQuery("[name='"+ element.name +"']")
                .after('<span class="wpdev-help-message alert">'+ errorMessage +'</span>'); // Show message
    }
    jQuery(".wpdev-help-message")
            .css( {'padding' : '5px 5px 4px', 'margin' : '2px', 'vertical-align': 'top', 'line-height': '32px' } );
    
    if ( element.type == 'checkbox' )
        jQuery(".wpdev-help-message").css( { 'vertical-align': 'middle'} );
            
    jQuery(".widget_wpdev_booking .booking_form .wpdev-help-message")
            .css( {'vertical-align': 'sub' } ) ;
    jQuery(".wpdev-help-message")
            .animate( {opacity: 1}, 10000 )
            .fadeOut( 2000 );   
    element.focus();    // make focus to elemnt
    return;

}

/**
 * Show message under specific element
 * 
 * @param {type} element - jQuery definition  of the element
 * @param {type} errorMessage - String message
 * @param {type} message_type "" | "alert-error" | "alert-success" | "alert-info"
 */
function showMessageUnderElement( element , errorMessage , message_type) {
    
    if(typeof( bk_form_step_click ) == 'function') {
        bk_form_step_click();                                                   // rollback  to 1st  step,  if system  will show warning and booking form  is using this customization: in the Exmaple #2 here: http://wpbookingcalendar.com/faq/customize-booking-form-for-having-several-steps-of-reservation/ 
    }
    
     makeScroll( element );
    
     if ( jQuery( element ).attr('type') == "radio" ) {
        jQuery( element ).parent().parent().parent()
                .after('<span class="wpdev-help-message wpdev-element-message alert '+ message_type +'">'+ errorMessage +'</span>'); // Show message

    } else if (jQuery( element ).attr('type') == "checkbox") {
        jQuery( element ).parent()
                .after('<span class="wpdev-help-message wpdev-element-message alert '+ message_type +'">'+ errorMessage +'</span>'); // Show message

    } else {
        jQuery( element )
                .after('<span class="wpdev-help-message wpdev-element-message alert '+ message_type +'">'+ errorMessage +'</span>'); // Show message
    }
    //    jQuery(".wpdev-help-message")
    //            .css( {'padding' : '5px 5px 4px', 'margin' : '10px 2px', 'vertical-align': 'middle' } );
    jQuery(".widget_wpdev_booking .booking_form .wpdev-help-message")
            .css( {'vertical-align': 'sub' } ) ;
    jQuery(".wpdev-help-message")
            .animate( {opacity: 1}, 10000 )
            .fadeOut( 2000 ); 
}

// Hint labels inside of input boxes
jQuery(document).ready( function(){

    jQuery('div.inside_hint').click(function(){
            jQuery(this).css('visibility', 'hidden').siblings('.has-inside-hint').focus();
    });

    jQuery('input.has-inside-hint').blur(function() {
        if ( this.value == '' )
            jQuery(this).siblings('.inside_hint').css('visibility', '');
    }).focus(function(){
            jQuery(this).siblings('.inside_hint').css('visibility', 'hidden');
    });

    jQuery('.booking_form_div input[type=button]').prop("disabled", false);
});


////////////////////////////////////////////////////////////////////////////
// Support Functions
////////////////////////////////////////////////////////////////////////////

// Scroll to script
function makeScroll(object_name) {
     var targetOffset = jQuery( object_name ).offset().top;
     //targetOffset = targetOffset - 50;
     if (targetOffset<0) targetOffset = 0;
     if ( jQuery('#wpadminbar').length > 0 ) targetOffset = targetOffset - 50;
     else  targetOffset = targetOffset - 20;
     jQuery('html,body').animate({scrollTop: targetOffset}, 500);
}


function wpdev_in_array (array_here, p_val) {
   for(var i = 0, l = array_here.length; i < l; i++) {
       if(array_here[i] == p_val) {
           return true;
       }
   }
   return false;
}


function days_between(date1, date2) {

    // The number of milliseconds in one day
    var ONE_DAY = 1000 * 60 * 60 * 24;

    // Convert both dates to milliseconds
    var date1_ms = date1.getTime();
    var date2_ms = date2.getTime();

    // Calculate the difference in milliseconds
    var difference_ms =  date1_ms - date2_ms;

    // Convert back to days and return
    return Math.round(difference_ms/ONE_DAY);

}


function daysInMonth(month,year) {
    var m = [31,28,31,30,31,30,31,31,30,31,30,31];
    if (month != 2) return m[month - 1];
    if (year%4 != 0) return m[1];
    if (year%100 == 0 && year%400 != 0) return m[1];
    return m[1] + 1;
}