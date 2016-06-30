<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  S u p p o r t    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


// Check  if this date available for specific booking resource, depend from the season filter.
function is_this_day_available_on_season_filters( $date, $bk_type, $season_filters = array() ){

    if ( empty($season_filters) ) {
        $season_filters = apply_bk_filter('get_available_days',  $bk_type );
    }

    $is_day_inside_filters = false ;
    $is_all_days_available = $season_filters['available'];
    $season_filters_dates_count = count( $season_filters['days'] );
    
    if ($season_filters_dates_count > 0) {

        $season_filters_dates = $season_filters['days'];
        

        $date_arr = explode( '-', $date );
        $d_mday = (int) $date_arr[2];
        $d_mon  = (int) $date_arr[1];
        $d_year = (int) $date_arr[0];    

        foreach ( $season_filters_dates as $filter_num => $season_filters_dates_value ) {           //FixIn: 6.0.1.13                            
            $version = '1.0';
            if (isset($season_filters_dates[$filter_num]['version']))                               // Version 2.0
                if ($season_filters_dates[$filter_num]['version'] == '2.0')   {

                    $version = '2.0';
                    if (isset($season_filters_dates[$filter_num][ $d_year ]))
                        if (isset($season_filters_dates[$filter_num][ $d_year ][ $d_mon ]))
                            if (isset($season_filters_dates[$filter_num][ $d_year ][ $d_mon ][ $d_mday ]))
                                if ($season_filters_dates[$filter_num][ $d_year ][ $d_mon ][ $d_mday ] == 1 ) {
                                    $is_day_inside_filters = true;
                                    break;
                                }
                }

            if ($version == '1.0') {                                    // Version 1.0

                $is_day_inside_filter = '';
                if (  $season_filters_dates[$filter_num]['days'][  $d_mday  ] == 'On' )     $is_day_inside_filter .= 'day ';
                if (  $season_filters_dates[$filter_num]['monthes'][  $d_mon  ] == 'On' )   $is_day_inside_filter .= 'month ';
                if (  $season_filters_dates[$filter_num]['year'][  $d_year  ] == 'On' )     $is_day_inside_filter .= 'year ';
                $d_wday = (int) date( "w", mktime(0, 0, 0, $d_mon, $d_mday, $d_year ) );
                if ($is_day_inside_filter == 'day month year ') {
                    if (  $season_filters_dates[$filter_num]['weekdays'][ $d_wday ] == 'On' ) $is_day_inside_filter .= 'week ';
                    if ($is_day_inside_filter == 'day month year week ') {$is_day_inside_filters = true; break;}
                }
            }
            
        }        
    }
    
      
    
    if ($is_day_inside_filters) {
        if ($is_all_days_available) return false;
        else                        return true;
    } else {
        if ($is_all_days_available) return true;
        else                        return false;
    }
        
}

// Check if this day inside of filter  , return TRUE  or FALSE   or   array( 'hour', 'start_time', 'end_time']) if HOUR filter this FILTER ID
function wpdev_bk_is_day_inside_of_filter($day , $month, $year, $filter){

    if (isset($filter)>0){

        if ( is_serialized( $filter ) ) $filter = unserialize($filter);

        if ( (isset($filter['version'])) && ($filter['version'] == '2.0') ){ // Good this is new filter
            // Its FILTER 2.0

            if (isset($filter[$year]))
                if (isset($filter[$year][$month]))
                    if (isset($filter[$year][$month][$day]))
                        if ($filter[$year][$month][$day] == 1 )
                            return  true;
            return  false;

        } else {
             // Its FILTER 1.0

            $week_day_num =  date('w', mktime(0, 0, 0, $month, $day, $year) );
            $weekdays = array(); $days = array(); $monthes =  array(); $years = array();

            foreach ($filter['weekdays'] as $key => $value) {
                if ($value == 'On')  $weekdays[] = $key;
            }
            foreach ($filter['days'] as $key => $value) {
                if ($value == 'On')  $days[] = $key;
            }
            foreach ($filter['monthes'] as $key => $value) {
                if ($value == 'On')  $monthes[] = $key;
            }
            foreach ($filter['year'] as $key => $value) {
                if ($value == 'On')  $years[] = $key;
            }
            if ( ( ! empty($filter['start_time'])) && ( ! empty($filter['end_time'])) ) {
                // Its hourly filter, so its apply to all days
                return array( 'hour', $filter['start_time'], $filter['end_time']);
            }
            if ( ! in_array($week_day_num, $weekdays) ) return false;  // there are no in filter
            if ( ! in_array($day, $days) )              return false;
            if ( ! in_array($month, $monthes) )         return false;
            if ( ! in_array($year, $years) )            return false;

            return true; // Its inside of filter
        }
    }
    return false;    // there are no filter so not inside of filter

}


function wpdev_bk_get_max_days_in_calendar(){
    $max_monthes_in_calendar = get_bk_option( 'booking_max_monthes_in_calendar');
    if (strpos($max_monthes_in_calendar, 'm') !== false) {
        $max_monthes_in_calendar = str_replace('m', '', $max_monthes_in_calendar) * 31 +5;
    } else {
        $max_monthes_in_calendar = str_replace('y', '', $max_monthes_in_calendar) * 365+15 ;
    }
    return $max_monthes_in_calendar;
}


/**
 * Getting total cost  of the booking.
 * 
 * Example of function call:
        $total_cost_of_booking = wpbc_get_cost_of_booking( array(
              'form' => 'select-one^visitors'.$value->id.'^'.$min_free_items, 
              'all_dates' => createDateRangeArray( date_i18n("d.m.Y", strtotime($bk_date_start) ), date_i18n("d.m.Y", strtotime($bk_date_finish) ) ), 
              'bk_type' => $value->id, 
              'booking_form_type' => apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $value->id )
          ) ) ;
 * 
 * @param type $args
 * @return string - formated cost.
 */
function wpbc_get_cost_of_booking( $args = array() ) {
    
    $defaults = array(
        'form' => '',                   // text^cost_hint2^740.00~select-multiple^rangetime2[]^15:00 - 16:00~text^name2^~text^secondname2^~email^email2^~text^phone2^~select-one^accommodation_meals2^ ~select-one^visitors2^1~textarea^details2^~checkbox^term_and_condition2[]^
        'all_dates' => '',              // 13.04.2015, 14.04.2015, 15.04.2015
        'bk_type' => 1,                 // ID of booking resource
        'booking_form_type' => ''       // Default custom  form  of booking resource
    );
    $params = wp_parse_args( $args, $defaults );
            
    $_POST['booking_form_type'] = $params['booking_form_type']; // Its required for the correct calculation  of the Advanced Cost.
    
    
    make_bk_action('check_multiuser_params_for_client_side', $params[ "bk_type"] );
    // TODO: Set for multiuser - user ID (ajax request do not transfear it
    //$this->client_side_active_params_of_user

    $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
    
    $sdform = $params['form'];
        
    $dates = $params[ "all_dates" ];
    $my_dates = explode(", ",$dates);

    $start_end_time = get_times_from_bk_form($sdform, $my_dates, $params[ "bk_type"] );        
    $start_time = $start_end_time[0];
    $end_time = $start_end_time[1];
    $my_dates = $start_end_time[2];

    // Get cost of main calendar with all rates discounts and  so on...
    $summ = apply_filters('wpdev_get_booking_cost', $params['bk_type'], $dates, array($start_time, $end_time ), $params['form'] );
    $summ = floatval( $summ );
    $summ = round($summ,2);

    $summ_original = apply_bk_filter('wpdev_get_bk_booking_cost', $params['bk_type'], $dates, array($start_time, $end_time ), $params['form'], true , true );
    $summ_original = floatval( $summ_original );
    $summ_original = round($summ_original,2);


    // Get description according coupons discount for main calendar if its exist
    $coupon_info_4_main_calendar = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $params['bk_type'], $dates, array($start_time, $end_time ), $params['form']   );

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Check additional cost based on several calendars inside of this form //////////////////////////////////////////////////////////////
    $additional_calendars_cost = apply_bk_filter('check_cost_for_additional_calendars', $summ, $params['form'], $params['bk_type'],  array($start_time, $end_time)   ); // Apply cost according additional calendars        
    $summ_total       = $additional_calendars_cost[0];
    $summ_additional  = $additional_calendars_cost[1];
    $dates_additional = $additional_calendars_cost[2];

    $additional_description = '';           
    if ( count($summ_additional)>0 ) {  // we have additional calendars inside of this form

            // Main calendar description and discount info //
            $additional_description .= '<br />' . get_booking_title($params['bk_type']) . ': ' . $cost_currency   . $summ  ;
            if ($coupon_info_4_main_calendar != '')
                $additional_description .=   $coupon_info_4_main_calendar ;
            $coupon_info_4_main_calendar = '';
            $additional_description .= '<br />' ;

            // Additional calendars - info and discounts //
            foreach ($summ_additional as $key=>$ss) {

                $additional_description .= get_booking_title($key) . ': ' . $cost_currency  . $ss ;

                // Discounts info ///////////////////////////////////////////////////////////////////////////////////////////////////////
                $form_content_for_specific_calendar = $this->get_bk_form_with_correct_id($params['form'], $key ,  $params['bk_type'] );
                $dates_in_specific_calendar = $dates_additional[$key];
                $coupon_info_4_calendars = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $key , $dates_in_specific_calendar , array($start_time, $end_time ), $form_content_for_specific_calendar );
                if ($coupon_info_4_calendars != '')
                    $additional_description .=   $coupon_info_4_calendars ;
                $coupon_info_4_calendars = '';
                /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

                $additional_description .= '<br />' ;
            }

    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ_total , $params['form'], $params['bk_type'], $params[ "all_dates" ] ); // Apply fixed deposit
    if ($summ_deposit != $summ_total )  $is_deposit = true;
    else                                $is_deposit = false;
    $summ_balance = $summ_total - $summ_deposit;
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
    $summ_additional_hint = $summ_total - $summ_original;

    
    $summ_original_hint         = wpdev_bk_cost_number_format( $summ_original );
    $summ_additional_hint_hint  = wpdev_bk_cost_number_format( $summ_additional_hint );
    $summ_total_orig = $summ_total;
    $summ_total            = wpdev_bk_cost_number_format( $summ_total );
    $summ_deposit_hint          = wpdev_bk_cost_number_format( $summ_deposit );
    $summ_balance_hint          = wpdev_bk_cost_number_format( $summ_balance ); 

    //[cost_hint] - Full cost of the booking.
    //[original_cost_hint] - Cost of the booking for the selected dates only.
    //[additional_cost_hint] - Additional cost, which depends on the fields selection in the form. 
    //[deposit_hint] - The deposit cost of the booking.
    //[balance_hint] - Balance cost of the booking - difference between deposit and full cost.        
    return array(
            'original_cost_hint'     => $summ_original_hint
                , 'original_cost_orig'     => $summ_original
            , 'additional_cost_hint' => $summ_additional_hint_hint
                , 'additional_cost_orig' => $summ_additional_hint
            
            , 'cost_hint'            => $summ_total 
                , 'total_orig'           => $summ_total_orig
            , 'deposit_hint'         => $summ_deposit_hint 
                , 'deposit_orig'         => $summ_deposit 
            , 'balance_hint'         => $summ_balance_hint 
                , 'balance_orig'         => $summ_balance
    );
}
