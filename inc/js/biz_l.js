var availability_per_day = [];
var wpbc_check_in_dates  = [];
var wpbc_check_out_dates = [];
var wpbc_check_in_out_closed_dates = [];
var highlight_availability_word = '';


function getDayAvailability4Show(bk_type, tooltip_time, td_class){

    if (  wpdev_in_array( parent_booking_resources, bk_type ) )
        if (is_show_availability_in_tooltips) {
           if(typeof(  availability_per_day[bk_type] ) !== 'undefined')
               if(typeof(  availability_per_day[bk_type][td_class] ) !== 'undefined') {
                    if (tooltip_time!== '') tooltip_time = tooltip_time + '<br/>';
                
                    var both_check_in_out_num = getNumberClosedCheckInOutDays( bk_type, td_class ) ;    
                    
                    return  tooltip_time + highlight_availability_word + parseInt( availability_per_day[bk_type][td_class] - both_check_in_out_num ) ;
               }
        }
    return  tooltip_time;
}


function getNumberClosedCheckInOutDays( bk_type, td_class ){
    var both_check_in_out_num = 0;    
    if(typeof(wpbc_check_in_out_closed_dates) !== 'undefined')
        if(typeof(wpbc_check_in_out_closed_dates[ bk_type ]) !== 'undefined')
            if(typeof(wpbc_check_in_out_closed_dates[ bk_type ][ td_class ]) !== 'undefined') {
                // [ Number of check In / Out bookings both  in the same child resource ]
                both_check_in_out_num =  wpbc_check_in_out_closed_dates[ bk_type ][ td_class ] ;
            }
    return both_check_in_out_num;
}


function checkDayAvailability4Visitors(bk_type, inp_value, my_dates_array) {
    
    if ( ( is_use_visitors_number_for_availability ) && (my_dates_array != '') ) {

        var my_single_data = '';
        var td_class1 = '';

        if (  (availability_based_on == 'visitors') && ( wpdev_in_array( parent_booking_resources, bk_type ) )  ) {                              // Visitors

                my_dates_array = my_dates_array.split(',');

                for (var i = 0;  i < my_dates_array.length; i++) {
                    if (my_dates_array[i]== '') return true;
                    my_single_data = my_dates_array[i].split('.');

                    my_single_data[0] = my_single_data[0].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                    my_single_data[1] = my_single_data[1].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                    my_single_data[2] = my_single_data[2].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                    my_single_data[0] = my_single_data[0].replace(/(^0+)|(\s+$)/g, ""); // TRim
                    my_single_data[1] = my_single_data[1].replace(/(^0+)|(\s+$)/g, ""); // TRim
                    my_single_data[2] = my_single_data[2].replace(/(^0+)|(\s+$)/g, ""); // TRim
                    td_class1 =  parseInt(my_single_data[1]) + '-' + parseInt(my_single_data[0]) + '-' + parseInt(my_single_data[2]);
                    if ( parseInt( availability_per_day[bk_type][td_class1] ) < parseInt( inp_value ) )
                        return true;

                }
        // availability based on items, so we will check visitors for maximum support of them for specific item
        } else {                                                                // Items

            if ( parseInt( max_visitors_4_bk_res[bk_type] ) < parseInt( inp_value ) )
                return true;

                my_dates_array = my_dates_array.split(',');

                for (var i = 0;  i < my_dates_array.length; i++) {
                    if (my_dates_array[i]== '') return true;
                    my_single_data = my_dates_array[i].split('.');

                    my_single_data[0] = my_single_data[0].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                    my_single_data[1] = my_single_data[1].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                    my_single_data[2] = my_single_data[2].replace(/(^\s+)|(\s+$)/g, ""); // TRim
                    my_single_data[0] = my_single_data[0].replace(/(^0+)|(\s+$)/g, ""); // TRim
                    my_single_data[1] = my_single_data[1].replace(/(^0+)|(\s+$)/g, ""); // TRim
                    my_single_data[2] = my_single_data[2].replace(/(^0+)|(\s+$)/g, ""); // TRim
                    td_class1 =  parseInt(my_single_data[1]) + '-' + parseInt(my_single_data[0]) + '-' + parseInt(my_single_data[2]);
                    if ( parseInt( availability_per_day[bk_type][td_class1] ) < 1  ) //parseInt( inp_value ) )
                        return true;

                }

        }




        return false;

    } else {                                                                    // No apply of visitors
        return false;
    }
}

    ////////////////////////////////////////////////////////////////////////////
    // Booking Search functionality

    function searchFormClck( search_form, wpdev_active_locale ){

        if ( (search_form.check_in.value == '') || (search_form.check_out.value == '') ) {
            alert(search_emty_days_warning);
            return;
        }
        document.getElementById('booking_search_results' ).innerHTML = '<div style="height:20px;width:100%;text-align:center;margin:15px auto;"><img src="'+wpdev_bk_plugin_url+'/img/ajax-loader.gif"><//div>';
        ajax_search_submit( search_form, wpdev_active_locale );

    }


    //<![CDATA[
    function ajax_search_submit( search_form, wpdev_active_locale ) {           //FixIn: 6.0.1.1
            // Ajax POST here

            var my_bk_category = '';
            var my_bk_tag = '';
            var my_bk_users = '';
            var my_bk_advanced = '';

            var elm1 = document.getElementById("booking_search_category");
            if( elm1 !== null) my_bk_category = search_form.category.value

            var elm2 = document.getElementById("booking_search_tag");
            if( elm2 !== null) my_bk_tag = search_form.tag.value

            var elm3 = document.getElementById("booking_bk_users");
            if( elm3 !== null) my_bk_users = search_form.bk_users.value
                        
            if ( jQuery("input[name='additional_search']:checked").length > 0 )
                my_bk_advanced = jQuery("input[name='additional_search']").val();

            var all_paramas = '';
            for(var i = 0; i < search_form.length; i++) {                       // FixIn:6.0.1
                if ( ( search_form[i].type == 'checkbox' ) && ( ! search_form[i].checked ) )
                    continue;
                all_paramas += search_form[i].type + "^";
                all_paramas += search_form[i].name + "^";
                all_paramas += search_form[i].value + "~";
            }

            jQuery(".booking_search_ajax_container").remove();
            
            jQuery.ajax({                                           // Start Ajax Sending
                // url: wpdev_bk_plugin_url+ '/' + wpdev_bk_plugin_filename,
                url: wpbc_ajaxurl,
                type:'POST',
                success: function (data, textStatus){if( textStatus == 'success')   jQuery('#booking_search_ajax' ).html( data ) ;},
                error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax search Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                // beforeSend: someFunction,
                data:{
                    //ajax_action : 'BOOKING_SEARCH',
                    action : 'BOOKING_SEARCH',
                    bk_check_in: search_form.check_in.value ,
                    bk_check_out: search_form.check_out.value ,
                    bk_visitors: search_form.visitors.value,
                    bk_no_results_title: search_form.bk_no_results_title.value,
                    bk_search_results_title: search_form.bk_search_results_title.value,
                    bk_category:my_bk_category,
                    bk_tag:my_bk_tag,
                    bk_users:my_bk_users,
                    bk_search_params: all_paramas,
                    additional_search: my_bk_advanced,
                    wpdev_active_locale:wpdev_active_locale,
                    wpbc_nonce: document.getElementById('wpbc_search_nonce').value 
                }
            });
    }
    //]]>




    function setDaysSelectionsInCalendar(bk_type, check_in, check_out){

        clearTimeout(timeout_DSwindow);
        

        var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
        inst.dates = [];

        var original_array = []; var date;
        /*
            <?php foreach ($this->current_edit_booking['dates'] as $dt) {
                    $dt = trim($dt);
                    $dta = explode(' ',$dt);
                    $tms = $dta[1];
                    $tms = explode(':' , $tms);
                    $dta = $dta[0];
                    $dta = explode('-',$dta);
             ?>
                    date=new Date();
                    date.setFullYear( <?php echo $dta[0].', '.($dta[1]-1).', '.$dta[2]; ?> );    // get date
                    original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, date, null))  ); //add date
        <?php     } ?>
    /**/

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
    }



function is_max_visitors_selection_more_than_available( bk_type, visitors_selection , element ) {

    if  (  ( wpdev_in_array( parent_booking_resources, bk_type ) ) ||   // Item have some capacity
           ( is_use_visitors_number_for_availability === true   )      // Item single, but checking for MAX visitors in situatio, when visitors apply to capacity
    ) {

                var my_dates_v = document.getElementById('date_booking' + bk_type).value;
                if(typeof( checkDayAvailability4Visitors ) == 'function') {
                    var is_visitors_more_then_need = checkDayAvailability4Visitors(bk_type, visitors_selection, my_dates_v);
                    if (is_visitors_more_then_need) {
                        showErrorMessage( element , message_verif_visitors_more_then_available);
                        return true;
                    }
                }



    }

    return false;

}


    jQuery(document).ready(function(){

       if (
             (location.href.indexOf('bk_check_in=')>0) &&
             (location.href.indexOf('bk_check_out=')>0) &&
             (location.href.indexOf('bk_type=')>0)
           ) {
            timeout_SelectDaysInCalendar=setTimeout("setDaySelectionsInCalendar()",1500);
       }
    });


    function setDaySelectionsInCalendar(){
        clearTimeout(timeout_SelectDaysInCalendar);

        // Parse a URL
        var myURLParams = location.href.split('?');
        myURLParams = myURLParams[1].split('&');
        for (myParam in myURLParams) {
            myParam = myURLParams[myParam].split('=');
            if (myParam[0] == 'bk_check_in') var check_in_date = myParam[1];
            if (myParam[0] == 'bk_check_out') var check_out_date = myParam[1];
            if (myParam[0] == 'bk_visitors') var bk_visitors_num = myParam[1];
            if (myParam[0] == 'bk_type') var my_bk_type = myParam[1].split('#')[0];
        }
        jQuery('select[name=visitors'+ my_bk_type + ']').val( bk_visitors_num );
         

        check_in_date = check_in_date.split('-');
        check_out_date = check_out_date.split('-');
        var bk_type = my_bk_type;

        var inst = jQuery.datepick._getInst(document.getElementById('calendar_booking'+bk_type));
        inst.dates = [];
        var original_array = [];
        var date;
        var bk_inputing = document.getElementById('date_booking' + bk_type);
        var bk_distinct_dates = [];

        date=new Date();
        date.setFullYear( check_in_date[0], (check_in_date[1]-1), check_in_date[2] );                                    // year, month, date
        var original_check_in_date = date;
        original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, date, null))  ); //add date
        if ( !  wpdev_in_array(bk_distinct_dates, (check_in_date[2]+'.'+check_in_date[1]+'.'+check_in_date[0]) ) ) {
            bk_distinct_dates.push(check_in_date[2]+'.'+check_in_date[1]+'.'+check_in_date[0]);
        }

        var date_out=new Date();
        date_out.setFullYear( check_out_date[0], (check_out_date[1]-1), check_out_date[2] );                                    // year, month, date
        var original_check_out_date = date_out;

        var mewDate=new Date(original_check_in_date.getFullYear(), original_check_in_date.getMonth(), original_check_in_date.getDate() );
        mewDate.setDate(original_check_in_date.getDate()+1);

        while(
                (original_check_out_date > date ) &&
                (original_check_in_date != original_check_out_date ) )
             {
            date=new Date(mewDate.getFullYear(), mewDate.getMonth(), mewDate.getDate() );

            original_array.push( jQuery.datepick._restrictMinMax(inst, jQuery.datepick._determineDate(inst, date, null))  ); //add date
            if ( !  wpdev_in_array(bk_distinct_dates, (date.getDate()+'.'+parseInt(date.getMonth()+1)+'.'+date.getFullYear()) ) ) {
                bk_distinct_dates.push((date.getDate()+'.'+parseInt(date.getMonth()+1)+'.'+date.getFullYear()));
            }

            mewDate=new Date(date.getFullYear(), date.getMonth(), date.getDate() );
            mewDate.setDate(mewDate.getDate()+1);
        }
        original_array.pop();
        bk_distinct_dates.pop();

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
                
        check_condition_sections_in_bkform( jQuery('#date_booking' + bk_type).val() , bk_type);

        // HERE WE WILL DISABLE ALL OPTIONS IN RANGE TIME INTERVALS FOR SINGLE DAYS SELECTIONS FOR THAT DAYS WHERE HOURS ALREADY BOOKED
        bkDisableBookedTimeSlots( jQuery('#date_booking' + bk_type).val() , bk_type);
        
        showCostHintInsideBkForm(bk_type);
    }