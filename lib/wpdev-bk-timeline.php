<?php 
if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly


////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//  C a l e n d a r    T i m e l i n e       ///////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    ///////////////////////////////////////////////////////////////////////////////////////
    // SUPPORT functions 
    function dates_only_of_specific_resource($booked_dates_array, $resource_id, $bookings ) {


        foreach ($booked_dates_array as $key => $value) {

            $new_array = array();
            foreach ($value as $bk_id) {
                if ( $bk_id['resource']  == $resource_id ) {
                    $new_array[] = $bk_id['id'];
                }
            }
            if (!empty($new_array))
                $booked_dates_array[$key] = $new_array;
            else
                unset($booked_dates_array[$key]);
        }
        return $booked_dates_array;
    }

    function times_only_of_specific_resource($time_array_new, $resource_id, $bookings ) {

        foreach ($time_array_new as $date_key => $times_array) {

            foreach ($times_array as $time_key => $value) {

                $new_array = array();
                foreach ($value as $bk_id ) {
                
                    if ( $bk_id['resource'] == $resource_id ) {
                        $new_array[] = $bk_id['id'];
                    }
                }
                $time_array_new[$date_key][$time_key] = $new_array;
            }
        }
        return $time_array_new;
    }

    
function write_bk_id_css_classes( $prefix, $previous_booking_id ) {
    
    if ((! isset($previous_booking_id)) || (empty($previous_booking_id))) 
        return '';
    
    if ( is_string($previous_booking_id ) )    
        $bk_id_array = explode(',', $previous_booking_id);
    else if (is_array($previous_booking_id)) 
        $bk_id_array =   $previous_booking_id;
    else // Some Unknown situation
        return '';
    
    $bk_id_array = array_unique($bk_id_array);
    
    // If we are have several bookings,  so  add this special class
    if (count($bk_id_array)>1)              
         $css_class = 'here_several_bk_id ';
    else $css_class = '';
    
    foreach ($bk_id_array as $bk_id) {
        $css_class .= $prefix . $bk_id . ' ';
    }
    
    return $css_class;
}    
    
// Booking TimeLine ROW    
function wpdev_bk_timeline_booking_row( $current_resource_id, $start_date, $booking_data = array() ) {

    if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
            $is_show_resources_matrix = true;
    else    $is_show_resources_matrix = false;                                       
//debuge($booking_data);
    $booked_dates_array = $booking_data[0];
    $bookings           = $booking_data[1];
    $booking_types      = $booking_data[2];
    $time_array_new     = $booking_data[3];


    // Remove dates and Times from  the arrays, which is not belong to the $current_resource_id
    // We do not remove it only, when  the $current_resource_id - is empty - OLD ALL Resources VIEW
    if ( empty($current_resource_id)) { $current_resource_id = 1; }
    
    $booked_dates_array = dates_only_of_specific_resource($booked_dates_array, $current_resource_id, $bookings );
    $time_array_new     = times_only_of_specific_resource($time_array_new, $current_resource_id, $bookings );
    

    $current_date = $start_date;

    $bk_url_listing     = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking' ;

    // Initial  params
    $view_days_num = $_REQUEST['view_days_num'];

    if ( ! $is_show_resources_matrix ) {                                             // Single booking resource

        switch ($view_days_num) {
            case '90':
                $days_num = 7;
                $cell_width = '13.8%';
                $dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                $time_selles_num  = 1;
                break;
            case '365':
                $days_num = 32;
                $cell_width = '3%';
                $dwa = array(1=>__('M' ,'booking'),2=>__('Tu' ,'booking'),3=>__('W' ,'booking'),4=>__('Th' ,'booking'),5=>__('F' ,'booking'),6=>__('Sa' ,'booking'),7=>__('Su' ,'booking'));
                $time_selles_num  = 1;
                break;
            default:  // 30
                $days_num = 1;
                $cell_width =  '99%';;
                $dwa = array(1=>__('Mon' ,'booking'),2=>__('Tue' ,'booking'),3=>__('Wed' ,'booking'),4=>__('Thu' ,'booking'),5=>__('Fri' ,'booking'),6=>__('Sat' ,'booking'),7=>__('Sun' ,'booking'));
                $time_selles_num  = 24;//25;
                //$view_days_num = 1;
                break;
        }

    } else {                                                                // Multiple booking resources
        //$view_days_num = 365;
        switch ($view_days_num) {
            case '1':
                $days_num = 1;
                $cell_width = '99%';//(7*4.75*31) . 'px';
                $dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                $time_selles_num  = 24;$dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                break;
            case '7':
                $days_num = 7;
                $cell_width = '13.8%';//(4.75*31) . 'px';
                $dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                $time_selles_num  = 4;
                break;
            case '60':
                $days_num = 62;
                $cell_width = '1.5%';//(12) . 'px';;
                $dwa = array(1=>__('M' ,'booking'),2=>__('T' ,'booking'),3=>__('W' ,'booking'),4=>__('T' ,'booking'),5=>__('F' ,'booking'),6=>__('S' ,'booking'),7=>__('S' ,'booking'));
                $time_selles_num  = 1;
                break;
            case 'old_365':
                $days_num = 365;
                $cell_width = '1%';//(2) . 'px';;
                $time_selles_num  = 1;
                $dwa = array(1=>__('M' ,'booking'),2=>__('Tu' ,'booking'),3=>__('W' ,'booking'),4=>__('Th' ,'booking'),5=>__('F' ,'booking'),6=>__('Sa' ,'booking'),7=>__('Su' ,'booking'));
                break;

            default:  // 30
                $days_num = 32;
                $cell_width = '3%';//(31) . 'px';;
                $dwa = array(1=>__('Mon' ,'booking'),2=>__('Tue' ,'booking'),3=>__('Wed' ,'booking'),4=>__('Thu' ,'booking'),5=>__('Fri' ,'booking'),6=>__('Sat' ,'booking'),7=>__('Sun' ,'booking'),);
                $time_selles_num  = 1;//25;
                break;
        }
    }

    if ($start_date === false) {
        $start_year     = date('Y') ;
        $start_month    = date('n') ;
        $start_day      = date('j') ;
        if (! empty($_REQUEST['scroll_start_date'])) {   // scroll_start_date=2013-07-01
            $scroll_start_date= explode('-',$_REQUEST['scroll_start_date']);

            $start_year     = $scroll_start_date[0];            //2012
            $start_month    = $scroll_start_date[1];           //09
            $start_day      = $scroll_start_date[2];    //date("d");//1;     //31
        } 

    } else {
        $start_year     = date("Y", $start_date);    //2012
        $start_month    = date("m", $start_date);    //09
        $start_day      = date("d", $start_date);    //31
    }

    $previous_booking_id = false;


    ?>

    <div class="container-fluid <?php if ($is_show_resources_matrix) { echo ' matrix_resources '; } else { echo ' single_resource '; } ?>"><div class="row-fluid"><div class="span12">

    <div id="timeline_scroller<?php echo $current_resource_id; ?>" class="calendar_timeline_scroller" style="width:100%">

    <div class="calendar_timeline_frame"  style="width:100%;">
        <?php
        $is_approved = false;
        $previous_month = '';
        for ($d_inc = 0; $d_inc < $days_num; $d_inc++) {

            $real_date = mktime(0, 0, 0, $start_month, ($start_day+$d_inc) , $start_year);

            if ( date_i18n('m.d.Y') == date_i18n("m.d.Y", $real_date) ) $is_today = ' today_date ';
            else  {
                if ( date_i18n('m.d.Y') > date_i18n("m.d.Y", $real_date) ) 
                    $is_today = ' past_date ';
                else 
                    $is_today = '';
            }

            $yy = date("Y", $real_date);    //2012
            $mm = date("m", $real_date);    //09
            $dd = date("d", $real_date);    //31
            $ww = date("N", $real_date);    //7

            $day_week = $dwa[$ww];          //Su

            $day_title = $dd;                
            if ($view_days_num==1) {
              $day_title =  wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00');
              $day_title  = $day_week . ', ' .  $day_title[0];
            }
            if ($view_days_num==7) {
              $day_title =  wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00');
              $day_title  = $day_week . ', ' .  $dd;
            }
            if ($view_days_num==30) {
              $day_title  = __('Times' ,'booking');
            }

            $day_filter_id = $yy.'-'.$mm.'-'.$dd;

            if ($previous_month != $mm) {
                $previous_month = $mm;
                $month_title = date_i18n("F", $real_date);    //09
                $month_class = ' new_month ';
            } else {
                $month_title = '';
                $month_class = '';
            }
            /*
            if ( ($d_inc> 0 ) && ($month_class == ' new_month ') && ($view_days_num == '365') ) {
                ?>
                <div id="cell_<?php  echo $current_resource_id . '_' . $day_filter_id ; ?>" class="calendar_overview_cell cell_header  time_in_days_num_<?php echo $view_days_num;?>  weekday<?php echo $ww ?><?php echo  ' '.$day_filter_id.' '.$month_class ; ?>" style="<?php echo 'width:1px;'; ?>">
                </div>
                <?php
                break;
            }/**/

                ?><div  id="cell_<?php  echo $current_resource_id . '_' . $day_filter_id ; ?>"
                      class="calendar_overview_cell weekday<?php echo $ww . ' ';  echo $is_today; echo  ' '.$day_filter_id.' '.$month_class ; ?>"
                      style="<?php echo 'width:' . $cell_width . ';'; ?>"                          
                      >
                      <?php
                       // Just show current date in calendar.
                      if (false) {
                        $tooltip_date = wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00', 'Y / m / d, D');
                        echo '<a  '.
                           ' class="calendar_inday_title tooltip_top time_in_days_num_'.$view_days_num.' "'.
                           ' rel="tooltip" data-original-title="'.$tooltip_date[0].'"'.
                             '   >'.$dd.( ($view_days_num=='90')?'/'.$mm:'') .'</a>';
                      } else {
                          // if (in_array($ww, array(1,3,6)))  // Show number of days only for the specific week days - for speeder loading.
                        echo '<span  '.
                           ' class="calendar_inday_title time_in_days_num_'.$view_days_num.' "'.
                             '   >'.$dd.( ($view_days_num=='90')?'/'.$mm:'') .'</span>';
                      }

                       $title_in_day = $title = $title_hint ='';
                       $is_bk = 0;



                       if ($time_selles_num != 24) {  //  Full     D a t e s

                          if ($booked_dates_array !== false) {

                              $link_id_parameter = array();
                               if ( isset($booked_dates_array[ $day_filter_id ]) ) {    // This date is    B O O K E D

                                   $is_bk = 1;
                                   $booked_dates_array[ $day_filter_id ] = array_unique($booked_dates_array[ $day_filter_id ]);
                                   foreach ($booked_dates_array[ $day_filter_id ] as $bk_id ) {

                                       $booking_num_in_day = count($booked_dates_array[ $day_filter_id ]);
                                       if( ($previous_booking_id != $bk_id) || ($booking_num_in_day>1) ){
                                           // if the booking take for the several  days, so then do not show title in the other days
                                           $my_bk_info = get_booking_info_4_tooltip( $bk_id , $bookings, $booking_types, $title_in_day , $title , $title_hint );

                                           $title_in_day = $my_bk_info[0];
                                           $title        = $my_bk_info[1];
                                           $title_hint   = $my_bk_info[2];
                                           $is_approved  = $my_bk_info[3];
                                       }
                                       if ($booking_num_in_day>1) {
                                           $previous_booking_id .= ',' . $bk_id;
                                       } else $previous_booking_id = $bk_id;
                                       
                                       $link_id_parameter[]=$bk_id;
                                   }

                               } else $previous_booking_id = false;


                                // Just one day cell

                                $title_hint = str_replace('"', "", $title_hint)   ;
                                $link_id_parameter = implode(',', $link_id_parameter);
                                if ( strpos($title_in_day, ',') !== false) {
                                    $title_in_day = explode(',', $title_in_day) ;
                                    $title_in_day = $title_in_day[0] . '..' . $title_in_day[ (count($title_in_day)-1) ];
                                    $title_in_day = '<span style=\'font-size:7px;\'>' . $title_in_day . '</span>';
                                }

                                // Show the circle with  bk ID(s) in a day
                                //href="'.$bk_url_listing.'&wh_booking_id='.$link_id_parameter.'&view_mode=vm_listing&tab=actions"                                  
                                echo '<a  
                                      href="'.$bk_url_listing.'&wh_booking_id='.$link_id_parameter.'&view_mode=vm_listing&tab=actions" 
                                      data-content="<div class=\'\'>'.$title_hint.'</div>"
                                      data-original-title="'.'ID: '.$title.'"
                                      rel="popover" class="'.write_bk_id_css_classes('a_bk_id_',$previous_booking_id).' popover_bottom  ' . ( ($title!='')?'first_day_in_bookin':'' ).' ">'.$title_in_day.'</a>';

                                $tm = floor(24 / $time_selles_num);
                                $tt = 0 ;
                                $my_bkid_title = '';
                                echo '<div class="'.write_bk_id_css_classes('cell_bk_id_',$previous_booking_id).' time_section_in_day timeslots_in_this_day' . $time_selles_num .
                                                 ' time_hour'.($tt*$tm).'  time_in_days_num_'.$view_days_num.' '.
                                                 ( $is_bk ?'time_booked_in_day':'' ).' '.( $is_approved ?'approved':'' ).
                                               ' ">'.
                                (($is_bk)?($my_bkid_title):'').
                                '</div>';

                          }

                       }

                       
                       if ($time_selles_num ==24 ) {  // Time Slots in a date
                            if ( isset($time_array_new[ $day_filter_id ]) ) {


                            // Loop time cells  /////////////////////////////////////////////////////////////////////////////////////////////////
                            $tm = floor(24 / $time_selles_num);
                            for ($tt = 0; $tt < $time_selles_num; $tt++) {

                                $my_bk_id_array = $time_array_new[$day_filter_id][$tt *60 * 60] ;
                                $my_bk_id_array = array_unique($my_bk_id_array); //remove dublicates

                                if (empty($my_bk_id_array)) {   // Time cell  is    E m p t y

                                    $is_bk = 0;
                                    $previous_booking_id = false;
                                    $my_bkid_title = $title_in_day = $title = $title_hint ='';

                                } else {                        // Time cell is     B O O K E D
                                    $is_bk = 1;
                                    $link_id_parameter = array();
                                    
                                    if( ($previous_booking_id !== $my_bk_id_array) || ($previous_booking_id === false) ){
                                       $my_bkid_title = $title_in_day = $title = $title_hint ='';
                                       foreach ($my_bk_id_array as $bk_id) {

                                           $my_bk_info = get_booking_info_4_tooltip( $bk_id , $bookings, $booking_types, $title_in_day , $title , $title_hint );

                                           $title_in_day = $my_bk_info[0];
                                           $title        = $my_bk_info[1];
                                           $title_hint   = $my_bk_info[2];
                                           $is_approved  = $my_bk_info[3];
                                           $link_id_parameter[] = $bk_id;
                                       }

                                    } else {
                                        $my_bkid_title = $title_in_day = $title = $title_hint ='';
                                    }
                                    $previous_booking_id = $my_bk_id_array;


                                    $title_hint = str_replace('"', "", $title_hint)   ;
                                    $link_id_parameter = implode(',', $link_id_parameter);
                                    if ( strpos($title_in_day, ',') !== false) {
                                        $title_in_day = explode(',', $title_in_day) ;
                                        $title_in_day = $title_in_day[0] . '..' . $title_in_day[ (count($title_in_day)-1) ];
                                        $title_in_day = '<span style=\'font-size:7px;\'>' . $title_in_day . '</span>';
                                    }

                                    // Show the circle with  bk ID(s) in a day
                                    $my_bkid_title = '<a  href="'.$bk_url_listing.'&wh_booking_id='.$link_id_parameter.'&view_mode=vm_listing&tab=actions"
                                     data-content="<div class=\'\'>'.$title_hint.'</div>"
                                     data-original-title="'.'ID: '.$title.'"
                                     rel="popover" class="'.write_bk_id_css_classes('cell_bk_id_',$previous_booking_id).' popover_bottom  ' . ( ($title!='')?'first_day_in_bookin':'' ).' ">'.$title_in_day.'</a>';
                                }

                                $is_past_time = '';
                                if (  ( $is_today == ' today_date ' ) && ( intval( date_i18n('H') ) > ($tt*$tm) )  ) { 
                                        $is_past_time = ' past_time ';
                                }

                                echo '<div class="'.write_bk_id_css_classes('cell_bk_id_',$previous_booking_id).' time_section_in_day timeslots_in_this_day' . $time_selles_num .
                                                 ' time_hour'.($tt*$tm).'  time_in_days_num_'.$view_days_num.' '.
                                                 ( $is_bk ? ' time_booked_in_day' . $is_past_time : '' ).' '.( $is_approved ?'approved':'' ).
                                               ' ">'.
                                (($is_bk)?($my_bkid_title):'').
                                '</div>';
                            } //////////////////////////////////////////////////////////////////////////////////////////////////////////////

                           } else { // Just  time borders
                                $tm = floor(24 / $time_selles_num);
                                for ($tt = 0; $tt < $time_selles_num; $tt++) {
                                    echo '<div class="time_section_in_day timeslots_in_this_day' . $time_selles_num .
                                                     ' time_hour'.($tt*$tm).'  time_in_days_num_'.$view_days_num.' '.
                                                     ( $is_bk ?'time_booked_in_day':'' ).' '.( $is_approved ?'approved':'' ).
                                                   ' ">'.
                                    (($is_bk)?($my_bkid_title):'').
                                    '</div>';
                                }
                           }

                       }


                    /*<div class="day_line"></div>*/?>
                </div><?php


        } ?>
    </div>

    </div>

    </div></div></div>
    <?php

    return $current_date ;
}

// H E A D E R
function wpdev_bk_timeline_header_row( $start_date = false ) {

    if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
            $is_show_resources_matrix = true;
    else    $is_show_resources_matrix = false;
    $current_resource_id = '';
    // Initial  params
    $view_days_num = $_REQUEST['view_days_num'];

    if ($is_show_resources_matrix) {
        // MATRIX VIEW
        switch ($view_days_num) {
            case '1':
                $days_num = 1;
                $cell_width =  '99%';;
                $dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                $time_selles_num  = 24;
                break;
            case '7':
                $days_num = 7;
                $cell_width = '13.8%';
                // $dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                $dwa = array(1=>__('Mon' ,'booking'),2=>__('Tue' ,'booking'),3=>__('Wed' ,'booking'),4=>__('Thu' ,'booking'),5=>__('Fri' ,'booking'),6=>__('Sat' ,'booking'),7=>__('Sun' ,'booking'));
                $time_selles_num  = 1;
                break;                
            case '30':
                $days_num = 31;
                $cell_width = '3%';
                $dwa = array(1=>__('Mon' ,'booking'),2=>__('Tue' ,'booking'),3=>__('Wed' ,'booking'),4=>__('Thu' ,'booking'),5=>__('Fri' ,'booking'),6=>__('Sat' ,'booking'),7=>__('Sun' ,'booking'));
                $time_selles_num  = 1;
                break;                    
            case '60':
                $days_num = 62;
                $cell_width = '1.5%';
                $dwa = array( 1 => substr(__('Mon' ,'booking'),0,-1), 2 => substr(__('Tue' ,'booking'),0,-1), 3 => substr(__('Wed' ,'booking'),0,-1), 4 => substr(__('Thu' ,'booking'),0,-1), 5 => substr(__('Fri' ,'booking'),0,-1), 6 => substr(__('Sat' ,'booking'),0,-1), 7 => substr(__('Sun' ,'booking'),0,-1) );
                $time_selles_num  = 1;
                break;
            default:  // 30
                $days_num = 31;
                $cell_width = '3%';
                $dwa = array(1=>__('Mon' ,'booking'),2=>__('Tue' ,'booking'),3=>__('Wed' ,'booking'),4=>__('Thu' ,'booking'),5=>__('Fri' ,'booking'),6=>__('Sat' ,'booking'),7=>__('Sun' ,'booking'));
                $time_selles_num  = 1;
                break;
        }
    } else {

        switch ($view_days_num) {
            case '90':
                $days_num = 7;
                $cell_width = '13.8%';
                $dwa = array(1=>__('Monday' ,'booking'),2=>__('Tuesday' ,'booking'),3=>__('Wednesday' ,'booking'),4=>__('Thursday' ,'booking'),5=>__('Friday' ,'booking'),6=>__('Saturday' ,'booking'),7=>__('Sunday' ,'booking'));
                $time_selles_num  = 1;
                break;
            case '365':
                $days_num = 32;
                $cell_width = '3%';
                $dwa = array( 1 => substr(__('Mon' ,'booking'),0,-1), 2 => substr(__('Tue' ,'booking'),0,-1), 3 => substr(__('Wed' ,'booking'),0,-1), 4 => substr(__('Thu' ,'booking'),0,-1), 5 => substr(__('Fri' ,'booking'),0,-1), 6 => substr(__('Sat' ,'booking'),0,-1), 7 => substr(__('Sun' ,'booking'),0,-1) );
                $time_selles_num  = 1;
                break;
            default:  // 30
                $days_num = 1;
                $cell_width =  '99%';;
                $dwa = array(1=>__('Mon' ,'booking'),2=>__('Tue' ,'booking'),3=>__('Wed' ,'booking'),4=>__('Thu' ,'booking'),5=>__('Fri' ,'booking'),6=>__('Sat' ,'booking'),7=>__('Sun' ,'booking'));
                $time_selles_num  = 24;
                break;
        }
    }

    if ($start_date === false) {
        $start_year     = date('Y') ;
        $start_month    = date('n') ;
        $start_day      = date('j') ;
        if (! empty($_REQUEST['scroll_start_date'])) {   // scroll_start_date=2013-07-01
            $scroll_start_date= explode('-',$_REQUEST['scroll_start_date']);

            $start_year     = $scroll_start_date[0];            //2012
            $start_month    = $scroll_start_date[1];           //09
            $start_day      = $scroll_start_date[2];    //date("d");//1;     //31
        } 

    } else {
        $start_year     = date("Y", $start_date);    //2012
        $start_month    = date("m", $start_date);    //09
        $start_day      = date("d", $start_date);    //31
    } 
    ?>
    <div class="container-fluid <?php if ($is_show_resources_matrix) { echo ' matrix_resources '; } else { echo ' single_resource '; } ?> bk_timeline_header"><div class="row-fluid"><div class="span12">
        <div id="timeline_scroller<?php echo $current_resource_id; ?>" class="calendar_timeline_scroller">
            <div class="calendar_timeline_frame" >
                <?php

                $previous_month = '';
                $bk_admin_url_today = get_params_in_url( array('scroll_month', 'scroll_day', 'scroll_start_date') );
                for ($d_inc = 0; $d_inc < $days_num; $d_inc++) {

                    $real_date = mktime(0, 0, 0, $start_month, ($start_day+$d_inc) , $start_year);

                    if ( date_i18n('m.d.Y') == date_i18n("m.d.Y", $real_date) ) $is_today = ' today_date ';
                    else  $is_today = '';
 
                    $yy = date("Y", $real_date);    //2012
                    $mm = date("m", $real_date);    //09
                    $dd = date("d", $real_date);    //31
                    $ww = date("N", $real_date);    //7
                    $day_week = $dwa[$ww];          //Su

                    $day_title = $dd . ' ' .  $day_week;
                    if ($is_show_resources_matrix) {
                        if ($view_days_num==1) {
                          $day_title =  wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00');
                          //$day_title  = $day_week . '<br/>' .  $day_title[0];
                          $day_title  =   '(' . $day_week . ') &nbsp; ' . $day_title[0] ;                           //FixIn:6.0.1
                        }
                        if ($view_days_num==7) {
                          $day_title =  wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00');  
                          $day_title  =  $day_week . '<br/>' .  $day_title[0];
                        }
                        if ($view_days_num==30) {
                          $day_title  =  $dd . '<br/>' .  $day_week;
                        }

                        if ($view_days_num==60) {
                          $day_title  =  $dd . '<br/>' .  $day_week;
                        }

                    } else {
                        if ($view_days_num==1) {
                          $day_title =  wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00');
                          $day_title  = $day_week . '<br/>' .  $day_title[0];
                        }
                        if ($view_days_num==7) {
                          $day_title =  wpdevbk_get_date_in_correct_format( $yy.'-'.$mm.'-'.$dd.' 00:00:00');
                          $day_title  = $day_week . '<br/>' .  $dd;
                        }
                        if ($view_days_num==30) {
                          $day_title  = __('Times' ,'booking');
                        }
                        if ($view_days_num==90) {
                          $day_title  = $day_week;
                        }
                        if ($view_days_num==365) {
                          $day_title  = $dd;
                        }
                    }
                    $day_filter_id = $yy.'-'.$mm.'-'.$dd;

                    if ($previous_month != $mm) {
                        $previous_month = $mm;
                        $month_title = date_i18n("F", $real_date);    //09
                        $month_class = ' new_month ';
                    } else {
                        $month_title = '';
                        $month_class = '';
                    }

                    /*
                    // We are need to  stop DAY loop, if we are in Month view mode, and we are already out of this month
                    if (
                         ( ($d_inc> 0 ) && ($month_class == ' new_month ') && (in_array($view_days_num ,array('365'))) && (! $is_show_resources_matrix) ) || 
                         ( ($d_inc> 0 ) && ($month_class == ' new_month ') && (in_array($view_days_num ,array('30'))) && ($is_show_resources_matrix) )  
                       )     
                    {
                        ?>
                        <div id="cell_<?php  echo  $current_resource_id. '_' . $day_filter_id ; ?>" 
                             class="calendar_overview_cell cell_header time_in_days_num_<?php echo $view_days_num;?> weekday<?php echo $ww . ' '.$day_filter_id.' '.$month_class ; ?>" 
                             style="width:1px;"></div>
                        <?php
                        break;
                    }/**/

                    ?>
                    <div id="cell_<?php  echo $current_resource_id . '_' . $day_filter_id ; ?>" 
                         class="calendar_overview_cell cell_header time_in_days_num_<?php echo $view_days_num;?> weekday<?php echo $ww . ' '.$day_filter_id.' '.$month_class ; ?>" 
                         style="<?php echo 'width:' . $cell_width . ';'; ?>">

                           <?php if ($month_title != '') { ?>
                           <div class="month_year"><?php echo $month_title .', ' . $yy ;?></div>
                           <?php }
                                if ( ( $view_days_num==30 ) || ( $view_days_num == 60) ) { 
                                    ?><a href='<?php echo $bk_admin_url_today . '&scroll_start_date=' . $yy . '-' . $mm . '-' . $dd ; ?>'><?php                                 
                                }
                                
                                ?><div class="day_num"><?php echo $day_title;?></div><?php
                                
                                if ( ( $view_days_num==30 ) || ( $view_days_num == 60) ) {
                                    ?></a><?php                                 
                                } 
                            // T i m e   c e l l s
                            $tm = floor(24 / $time_selles_num);
                            for ($tt = 0; $tt < $time_selles_num; $tt++) { ?>
                                <div class="time_section_in_day time_section_in_day_header time_hour<?php echo ($tt*$tm); ?> time_in_days_num_<?php echo $view_days_num;?>">
                                    <?php echo (  ( ($view_days_num<31)? (( ($tt*$tm) < 10?'0':'').($tt*$tm).'<sup>:00</sup>'):'' )  );?>
                                </div>
                            <?php }
                           ?>
                    </div>
                    <?php        

                } ?>
            </div>
        </div>
    </div></div></div>
    <?php

    return $real_date ;
}

// Structure of the TIMELINE
function wpdev_bk_timeline( $dates_array, $bookings, $booking_types, $time_array_new = array() ){

        if  ((isset($_REQUEST['wh_booking_type'])) && ( strpos($_REQUEST['wh_booking_type'], ',') !== false ) )
                $is_show_resources_matrix = true;
        else    $is_show_resources_matrix = false;

        $scroll_day = 0;
        $scroll_month = 0;

        $start_year = date("Y");                                            // 2012
        $start_month = date("m");                                           // 09            

        // Set the correct  start  date, if was selected the stard date different from the today  in the Filters Tab.
        if (! empty($_REQUEST['scroll_start_date'])) {   // scroll_start_date=2013-07-01
            $scroll_start_date= explode('-',$_REQUEST['scroll_start_date']);

            $start_year     = $scroll_start_date[0];            //2012
            $start_month    = $scroll_start_date[1];           //09
            $start_day      = $scroll_start_date[2];    //date("d");//1;     //31
        } 





        $view_days_num = $_REQUEST['view_days_num'];                        // Get start date and number of rows, which is depend from the view days mode

        ////////////////////////////////////////////////////////////////////////////////////////////////
        // Get Start Date and Scroll Day/Month Variables ///////////////////////////////////////////////
        ////////////////////////////////////////////////////////////////////////////////////////////////
        if ($is_show_resources_matrix) {
            // MATRIX VIEW
//debuge($_REQUEST['wh_booking_type']);
            $bk_resources_id = explode(',', $_REQUEST['wh_booking_type']);
            $max_rows_number = count($bk_resources_id);
//debuge($bk_resources_id);
            switch ($view_days_num) {
                case '1':
                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = date("d");
                    break;

                case '30':
                case '60':
                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];                        
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = 1;
                    break;   

                case '7': // 7 Week - start from Monday (or other start week day)
                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];                        
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = date("d");
                    $start_week_day_num = date("w");
                    $start_day_weeek  = get_bk_option( 'booking_start_day_weeek' ); //[0]:Sun .. [6]:Sut

                    if ($start_week_day_num != $start_day_weeek) {
                        for ($d_inc = 1; $d_inc < 8; $d_inc++) {                // Just get week  back
                            $real_date = mktime(0, 0, 0, $start_month, ($start_day-$d_inc ) , $start_year);

                            $start_week_day_num = date("w", $real_date);
                            if ($start_week_day_num == $start_day_weeek) {
                                $start_day = date("d", $real_date);
                                $start_year  = date("Y", $real_date);
                                $start_month = date("m", $real_date);
                                $d_inc=9;
                            }
                        }
                    }
                    break;

                default:  //30
                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];                        
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = 1;
                    break;
            }

        } else {
            // SINGLE Resource VIEW    
            switch ($view_days_num) {
                case '90':
                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                    else $scroll_day = 0;

                    $max_rows_number = 12;
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = date("d");
                    $start_week_day_num = date("w");
                    $start_day_weeek  = get_bk_option( 'booking_start_day_weeek' ); //[0]:Sun .. [6]:Sut

                    if ($start_week_day_num != $start_day_weeek) {
                        for ($d_inc = 1; $d_inc < 8; $d_inc++) {                // Just get week  back
                            $real_date = mktime(0, 0, 0, $start_month, ($start_day-$d_inc ) , $start_year);

                            $start_week_day_num = date("w", $real_date);
                            if ($start_week_day_num == $start_day_weeek) {
                                $start_day = date("d", $real_date);
                                $start_year  = date("Y", $real_date);
                                $start_month = date("m", $real_date);
                                $d_inc=9;
                            }
                        }
                    }
                    break;

                case '365':
                    if (isset($_REQUEST['scroll_month'])) $scroll_month = $_REQUEST['scroll_month'];
                    else $scroll_month = 0;
                    $max_rows_number = 12;
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = 1;
                    break;

                default:  // 30
                    if (isset($_REQUEST['scroll_day'])) $scroll_day = $_REQUEST['scroll_day'];
                    else $scroll_day = 0;

                    $max_rows_number = 31;
                    if (empty($_REQUEST['scroll_start_date'])) $start_day = date("d");
                    break;
            }
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////

    ?><div class="bookings_overview_in_calendar_frame">
        <table class="bookings_overview_in_calendar booking_table table table-striped" cellpadding="0" cellspacing="0">
        <tr>
            <th style="width:200px;"><?php  if ($is_show_resources_matrix) { _e('Resources' ,'booking'); }?></th>
            <th style="text-align:center;"><?php  _e('Dates' ,'booking'); //wpdev_calendar_overview_buttons_navigations();?></th>
        </tr>
        <tr><td colspan="2"> </td></tr>
        <tr>
            <td class="bk_resource_selector"></td>
            <td style="padding:0px;"><?php                                  // Header above the calendar table
                $real_date = mktime(0, 0, 0, ($start_month), $start_day , $start_year);

            if ($is_show_resources_matrix) {    // MATRIX VIEW                    
                switch ($view_days_num) {                                        // Set real start date for the each rows in calendar
                    case '1':
                    case '7':
                        $real_date = mktime(0, 0, 0, $start_month, ( $start_day + $scroll_day ) , $start_year);
                        break;

                    case '30':
                    case '60':
                        $real_date = mktime(0, 0, 0, ($start_month +  $scroll_month ), $start_day  , $start_year);
                        break;

                    default:  // 30
                        $real_date = mktime(0, 0, 0, ($start_month +  $scroll_month ), $start_day  , $start_year);
                        break;
                }                    
            } else {                            // Single Resource View
                switch ($view_days_num) {                                        // Set real start date for the each rows in calendar
                    case '90':
                        $real_date = mktime(0, 0, 0, $start_month, ( $start_day +  $scroll_day ) , $start_year);
                        break;

                    case '365':
                        $real_date = mktime(0, 0, 0, ($start_month + $scroll_month ), $start_day , $start_year);
                        break;

                    default:  // 30
                        $real_date = mktime(0, 0, 0, $start_month, ( $start_day  + $scroll_day ) , $start_year);
                        break;
                }
            }


                wpdev_bk_timeline_header_row( $real_date ); ?>
            </td>
        </tr><?php


        for ($d_inc = 0; $d_inc < $max_rows_number; $d_inc++) {

            // Ger Start Date to real_date  variabale  /////////////////////
            if ($is_show_resources_matrix) {    // MATRIX VIEW                    
                switch ($view_days_num) {                                        // Set real start date for the each rows in calendar
                    case '1':
                    case '7':
                        $real_date = mktime(0, 0, 0, $start_month, ( $start_day + $scroll_day ) , $start_year);
                        break;

                    case '30':
                    case '90':
                        $real_date = mktime(0, 0, 0, ($start_month +  $scroll_month ), $start_day  , $start_year);
                        break;

                    default:  // 30
                        $real_date = mktime(0, 0, 0, ($start_month +  $scroll_month ), $start_day  , $start_year);
                        break;
                }                    
            } else {                            // Single Resource View
                switch ($view_days_num) {                                        // Set real start date for the each rows in calendar
                    case '90':
                        $real_date = mktime(0, 0, 0, $start_month, ( $start_day + $d_inc*7 + $scroll_day ) , $start_year);
                        break;

                    case '365':
                        $real_date = mktime(0, 0, 0, ($start_month+$d_inc + $scroll_month ), $start_day , $start_year);
                        break;

                    default:  // 30
                        $real_date = mktime(0, 0, 0, $start_month, ( $start_day + $d_inc + $scroll_day ) , $start_year);
                        break;
                }
            }
            ////////////////////////////////////////////////////////////////
          ?>
          <tr>
            <td style="border-right:2px solid #CC5544;">
                <div class="resource_title"><?php                    
                // Title in first collumn of the each row in calendar //////
                if ( ( $is_show_resources_matrix ) && ( isset($bk_resources_id[$d_inc]) ) &&  (isset($booking_types[ $bk_resources_id[$d_inc] ] )) )
                {  // Matrix - resource titles

                    $resource_value = $booking_types[ $bk_resources_id[$d_inc]  ];                        
                    $bk_admin_url = get_params_in_url( array('wh_booking_type' ) );

                    ?><div class="resource_title <?php if (isset($resource_value->parent)){  if ($resource_value->parent == 0) {echo 'parent';} else {echo 'child';} } ?> ">
                        <a href="<?php echo $bk_admin_url .'&wh_booking_type='. $bk_resources_id[$d_inc] ; ?>" /><?php 
                                echo $resource_value->title; ?> 
                        </a>
                       </div><?php
                } else {                            // Single Resource - Dates titles
                    switch ($view_days_num) {                                
                        case '90':
                            $end_real_date = mktime(0, 0, 0, $start_month, ( $start_day + $d_inc*7 + $scroll_day )+6 , $start_year);
                            $date_format = ' j, Y';//get_bk_option( 'booking_date_format');
                            echo __(date_i18n("M", $real_date)) . date( $date_format , $real_date) . ' - ' . __(date_i18n("M", $end_real_date)) .  date( $date_format , $end_real_date);
                            break;

                        case '365':
                            echo __(date("F", $real_date)) . ', ' . date("Y", $real_date);
                            break;

                        default:  // 30
                            //$date_format = 'd / m / Y';
                            $date_format = get_bk_option( 'booking_date_format');                           //FixIn:5.4.5.13
                            echo __( date_i18n( "D", $real_date ) ) . ', ' . date( $date_format, $real_date );
                            break;
                    }
                }
              ?></div>
            </td>
            <td  style="padding:0px;">
                <div class="resource_dates"><?php 

                if ( $is_show_resources_matrix )    $resource_id = $bk_resources_id[$d_inc] ; 
                else {
                    if ( isset($_REQUEST['wh_booking_type']) ) 
                         $resource_id = $_REQUEST['wh_booking_type'];
                    elseif ( isset($_GET['booking_type']) )  
                        $resource_id = $_GET['booking_type'];
                    else $resource_id = '';
                }
                wpdev_bk_timeline_booking_row( $resource_id, $real_date, array( $dates_array, $bookings, $booking_types, $time_array_new ) );

                ?></div>
            </td>
          </tr>
        <?php }

    ?></table></div><?php
}


//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
// Get info  for mouse over   T O O L T I P   in admin panel in calendar.  ///////////////////////////////////////////
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
function get_booking_info_4_tooltip( $bk_id, $bookings, $booking_types, $title_in_day='', $title='', $title_hint=''  ){

    $user = wp_get_current_user(); $user_bk_id = $user->ID;
   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   // Gathering data  about the booking to  show in the calendar !!! ////////////////////////////////////////////////////
   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get it from the option settings
    $what_show_in_day_template = get_bk_option( 'booking_default_title_in_day_for_calendar_view_mode' );// '<span style="font-size:07px;">[id]</span>:[name]';


   if ($title != '')            $title .= ' / ';                            // Other Booking in the same day
   $title        .=  $bk_id ;
   
   if ($title_in_day != '')     $title_in_day .= ',';           // Other Booking in the same day
   //$title_in_day .=  $bk_id ;
   if (function_exists ('get_title_for_showing_in_day')) {
        $title_in_day .= get_title_for_showing_in_day($bk_id, $bookings, $what_show_in_day_template);
   } else {
       $title_in_day .=  $bk_id .':'. $bookings[$bk_id]->form_data['_all_fields_'][ 'name' ];
   }

   if ($title_hint != '') $title_hint .= ' <hr style="margin:10px 5px;" /> ';   // Other Booking in the same day

   $title_hint .= '<div class=\'booking-listing-collumn\' >';

   if (function_exists ('get_booking_title')) {

        if (isset($booking_types[$bookings[$bk_id]->booking_type]))
             $bk_title = $booking_types[$bookings[$bk_id]->booking_type]->title;
        else $bk_title = get_booking_title( $bookings[$bk_id]->booking_type );
        $bk_title = '<span class=\'label label-resource label-info\'>' . $bk_title . '</span>' ;
   } else $bk_title = '';
   $title_hint .= '<span class=\'field-id\'>'.$bk_id.'</span>' . ' '. $bk_title;


   if (class_exists('wpdev_bk_biz_s')) {
        $title_hint .= '<div style=\'float:right;\'>';
        if (function_exists ('wpdev_bk_get_payment_status_simple')) {
            $pay_status = wpdev_bk_get_payment_status_simple( $bookings[$bk_id]->pay_status );
            $pay_status = '<span class=\'label label-payment-status payment-label-unknown\'><span style=\'font-size:07px;\'>'.__('Payment' ,'booking').'</span> '.$pay_status.'</span>';
        } else $pay_status = '';
        $title_hint .= ' '. $pay_status;

        $currency = apply_bk_filter( 'get_currency_info' );
        $show_cost_value = wpdev_bk_cost_number_format( $bookings[$bk_id]->cost );
        $title_hint .= ' <div class="cost-fields-group" style=\'float:right; margin:2px;\'>'.$currency.' '. $show_cost_value .'</div>';
        $title_hint .= '</div>';
   }

   $title_hint .= '<div>'. $bookings[$bk_id]->form_show .'</div>';//$bookings[$bk_id]->form_data['name'].' ' . $bookings[$bk_id]->form_data['secondname'] ;

   //$title_hint .= ' '. $bookings[$bk_id]->remark;

   //BL
   $bk_dates_short_id = array(); if (count($bookings[$bk_id]->dates) > 0 ) $bk_dates_short_id      = (isset($bookings[$bk_id]->dates_short_id))?$bookings[$bk_id]->dates_short_id:array();      // Array ([0] => [1] => .... [4] => 6... [11] => [12] => 8 )

   $is_approved = 0;   if (count($bookings[$bk_id]->dates) > 0 )     $is_approved = $bookings[$bk_id]->dates[0]->approved ;
   $short_dates_content = wpdevbk_get_str_from_dates_short($bookings[$bk_id]->dates_short, $is_approved , $bk_dates_short_id , $booking_types );
   $short_dates_content = str_replace('"', "'", $short_dates_content);
   $title_hint .= '<div style=\'margin-top:5px;\'>' . $short_dates_content . '</div>';
    
      
   $is_approved = 0;
   if ( ! empty($bookings[$bk_id]->dates) )
        $is_approved = $bookings[$bk_id]->dates[0]->approved;
   
   $title .= '<div class=\'timeline_info_bk_actionsbar_'.$bk_id.'\'  style=\'display: inline;
    line-height: 1em;
    padding: 10px;
    vertical-align: text-top;\'>';
    $is_can = true;//current_user_can( 'edit_posts' );
    if ($is_can) {
   if ( class_exists('wpdev_bk_personal') ) {
        $bk_url_add         = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-reservation' ;        
        $bk_hash            = (isset($bookings[$bk_id]->hash))?$bookings[$bk_id]->hash:'';         
        $bk_booking_type    = $bookings[$bk_id]->booking_type;
        $edit_booking_url = $bk_url_add . '&booking_type='.$bk_booking_type.'&booking_hash='.$bk_hash.'&parent_res=1' ; 
        $title .= '<a class=\'button button-secondary\' style=\'margin-right:5px;\' href=\''.$edit_booking_url .'\' onclick=\'\' ><i class=\'icon-edit\'></i></a>';
   } 
   
   $title .= '<a class=\'button button-secondary approve_bk_link '.($is_approved?'hidden_items':'').'\' style=\'margin-right:5px;\' href=\'javascript:;\' onclick=\'javascript:approve_unapprove_booking('. $bk_id.',1, '. $user_bk_id .', &quot;'. getBookingLocale() .'&quot; , 1   );\' ><i class=\'icon-ok-circle\'></i></a>';   
   $title .= '<a class=\'button button-secondary pending_bk_link '.($is_approved?'':'hidden_items').'\' style=\'margin-right:5px;\' href=\'javascript:;\' onclick=\'javascript:approve_unapprove_booking('. $bk_id.',0, '. $user_bk_id .', &quot;'. getBookingLocale() .'&quot; , 1   );\' ><i class=\'icon-ban-circle\'></i></a>';
   $title .= '<a class=\'button button-secondary\' style=\'margin-right:5px;\' href=\'javascript:;\' onclick=\'javascript:delete_booking('. $bk_id.', '. $user_bk_id .', &quot;'. getBookingLocale() .'&quot; , 1   );\' ><i class=\'icon-trash\'></i></a>';
    }   
   $title .= '</div>';

   $title_hint .= '</div>';
   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
   //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

   return( array($title_in_day, $title, $title_hint, $is_approved) );
}        
?>