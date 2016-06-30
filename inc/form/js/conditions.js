
/** Get elemnt ID of Conditional section,  relative to  specific day or false
 * 
 * @param string td_class  - day  in format: td_class = 11-23-2015
 * @param string bk_type   - ID of booking resources  
 * @returns bool | string
 */
function wpbc_get_conditional_section_id_for_weekday( td_class, bk_type ){      // FixIn: 5.4.5.2
    
    var date_sections = td_class.split("-");     
    var check_date = new Date;        
    check_date.setFullYear( parseInt(date_sections[2]-0) ,parseInt(date_sections[0]-1), parseInt(date_sections[1]-0) );

    
    var garbage_divs = jQuery( '#booking_form_garbage' + bk_type + ' div' );                // Check  for the    W E E K    D A Y S    conditions     
    var index;    
    var garbage_element = false;
    
    for (index = 0; index < garbage_divs.length; ++index) {

        garbage_element = jQuery( '#' + garbage_divs[index].id );                           // Get Garbage DIV as jQuery element
        
        if ( garbage_element.hasClass( 'wpdevbk_weekday_' + check_date.getDay() ) ) {       // Found our filter element of this date in garbage
            
            return '#' + garbage_divs[index].id ;                                           // ID of garbage_element  
        }
    }

    // Check in booking form - its required for situation, when we selected date,  and some conditional section  already in booking form.
    var booking_form_element = jQuery( '#booking_form_div' + bk_type + ' .wpdevbk_optional_condition');    // .conditional_section_element_weekday-condition.wpdevbk_optional_condition

    if ( booking_form_element.hasClass( 'wpdevbk_weekday_' + check_date.getDay() ) ) {                     // Found our filter element of this date in garbage

        return '#booking_form_div' + bk_type + ' .wpdevbk_optional_condition' ;                                   
    }
    
    return false;
}


// // Show / hide  Fields  in the booking form, 
// depend from week day or season filter
function check_condition_sections_in_bkform(date, bk_type){                     
    
    // Reseting
    moveOptionalElementsToGarbage( bk_type );
    moveDefaultElementsToForm(     bk_type );
    
    if (date == '') return;                                                     // If no days selections so then skip all.
    
    date = get_first_day_of_selection(date);                                    // Get the  10.06.2013 from different dates selection Varibale : 11.06.2013, 12.06.2013, 13.06.2013, 10.06.2013 || 10.06.2013  || 10.06.2013 - 18.06.2013

    var date_sections = date.split("."); 
    var selceted_first_day = new Date;       
    selceted_first_day.setFullYear( parseInt(date_sections[2]-0) ,parseInt(date_sections[1]-1), parseInt(date_sections[0]-0) );
    
    
    var class_day = (selceted_first_day.getMonth()+1) + '-' + selceted_first_day.getDate() + '-' + selceted_first_day.getFullYear();
    if (jQuery('#calendar_booking'+bk_type+' .datepick-days-cell.cal4date-'+class_day).length <= 0) return;  // We are chnaged the month (hided previos with first selected date), so  this date cell is not exist  now, we are need to exist
    var calendarDateClassList =jQuery('#calendar_booking'+bk_type+' .datepick-days-cell.cal4date-'+class_day).attr('class').split(/\s+/);

    var formElementClassName = '';
    var garbageElement = false;
    
    jQuery.each( calendarDateClassList, function(index, singleClassCSS){
        
        // S E A S O N    F I L T E R    C O N D I T I O N S     - checking
        if ( singleClassCSS.indexOf("wpdevbk_season_") >= 0 ) {
            
            // 1.Get this element from the garbage - <div class="conditional_section_element_times  wpdevbk_optional_condition wpdevbk_season_high_season"> [...] </div>
            
            jQuery.each( jQuery('#booking_form_garbage'+bk_type + ' div') , function(index, conditionGarbageDIV){  // LOOP in the Garbage
                
                garbageElement = jQuery('#'+conditionGarbageDIV.id);            // Get Garbage DIV as jQuery element
                
                if ( garbageElement.hasClass(singleClassCSS) ) {                // We found our season filter element       in garbage with CSS class of that SEASON

                    // Get the ALL CSS classes of the DIV garbage element. Example: conditional_section_element_times  wpdevbk_optional_condition wpdevbk_season_high_season
                    var garbageElementClassList = garbageElement.attr('class').split(/\s+/);   
                    
                    jQuery.each( garbageElementClassList, function(index, garbageElementClassName ){
                        
                        //Get the name of CSS, like this: conditional_section_element_times
                        if ( garbageElementClassName.indexOf("conditional_section_element_") >= 0 ) { 
                            
            // 2.Get the class name in this element of the DIV section in the form
                            formElementClassName = garbageElementClassName.replace('_element', '');
                            
            // 3.Remove all elements from the form in that DIV into the Garbage
                            jQuery('#booking_form_div' + bk_type + ' div.' + formElementClassName + ' div').appendTo( '#booking_form_garbage' + bk_type );
                            
            // 4.Insert this element into the form
                            garbageElement.appendTo( '#booking_form_div' + bk_type + ' div.' + formElementClassName );
                        }
                    });
                }
            });  
        }
        
    });
    
    
    
    // Check  for the    W E E K    D A Y S    conditions //////////////////////////////////////////////////////////////////////////////////////
    jQuery.each( jQuery('#booking_form_garbage'+bk_type + ' div') , function(index, conditionGarbageDIV){  // LOOP in the Garbage

        garbageElement = jQuery('#'+conditionGarbageDIV.id);            // Get Garbage DIV as jQuery element

        if ( garbageElement.hasClass( 'wpdevbk_weekday_' + selceted_first_day.getDay() ) ) {                // We found our filter element od this date      in garbage with CSS class of that SEASON
    
    
            // Get the ALL CSS classes of the DIV garbage element. Example: conditional_section_element_times  wpdevbk_optional_condition wpdevbk_season_high_season
                    var garbageElementClassList = garbageElement.attr('class').split(/\s+/);   
                    
                    jQuery.each( garbageElementClassList, function(index, garbageElementClassName ){
                        
                        //Get the name of CSS, like this: conditional_section_element_times
                        if ( garbageElementClassName.indexOf("conditional_section_element_") >= 0 ) { 
                            
            // 2.Get the class name in this element of the DIV section in the form
                            formElementClassName = garbageElementClassName.replace('_element', '');
                            
            // 3.Remove all elements from the form in that DIV into the Garbage
                            jQuery('#booking_form_div' + bk_type + ' div.' + formElementClassName + ' div').appendTo( '#booking_form_garbage' + bk_type );
                            
            // 4.Insert this element into the form
                            garbageElement.appendTo( '#booking_form_div' + bk_type + ' div.' + formElementClassName );
                        }
                    });    
    
        }
    });
    
}


//  Optionals:  Form  ->  Garbage
function moveOptionalElementsToGarbage( bk_type ){
    jQuery('#booking_form_div' + bk_type + ' .wpdevbk_optional_condition').appendTo( '#booking_form_garbage'+bk_type ); 
    jQuery('#booking_form_garbage' + bk_type + ' .wpdevbk_optional_condition').show();                               // We are need to  show this elements, because by default they are hided
}


//  Defaults:  Garbage  ->  Form
function moveDefaultElementsToForm( bk_type ){
    
    var formElementClassName = '';
    var garbageElement = false;

    jQuery.each( jQuery('#booking_form_garbage'+bk_type + ' div') , function(index, conditionGarbageDIV){  // LOOP in the Garbage

        garbageElement = jQuery('#'+conditionGarbageDIV.id);                    // Get Garbage DIV as jQuery element

        if ( garbageElement.hasClass( 'wpdevbk_default_condition' ) ) {         // We found our Default Element
    
            // Get the ALL CSS classes of this DIV garbage element.
            var garbageElementClassList = garbageElement.attr('class').split(/\s+/);   

            jQuery.each( garbageElementClassList, function(index, garbageElementClassName ){

                //Get the name of CSS, like this: conditional_section_element_times
                if ( garbageElementClassName.indexOf("conditional_section_element_") >= 0 ) { 

                    //Get the CSS class   of the DIV section in the form
                    formElementClassName = garbageElementClassName.replace('_element', '');

                    // Move this Default Garbage Element  ->  Form    
                    garbageElement.appendTo( '#booking_form_div' + bk_type + ' div.' + formElementClassName );
                }
            });    
    
        }
    });   
}