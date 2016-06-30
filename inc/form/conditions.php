<?php 
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

// Get season filter title for usage in CSS Classes ////////////////////////
function wpdev_bk_get_escape_season_filter_name($title){

    $title = str_replace(' ','_',$title);
    $title = strtolower($title);
    $title = esc_attr($title);

    return $title;
}


/*======= S E A S O N ===================================================<br />
[condition name="season-times" type="season" value="*"]
  Default: [select rangetime "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00"]
[/condition]
[condition name="season-times" type="season" value="High season"]
  High season: [select rangetime  "10:00 - 12:00" "12:00 - 14:00" "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00"]
[/condition]
[condition name="season-times" type="season" value="Low season"]
  Low season: [select rangetime "12:00 - 14:00" "14:00 - 16:00"]
[/condition]


============ W E E K D A Y ==============================================<br />
[condition name="weekday-condition" type="weekday" value="*"]
  Default:   [select rangetime  "10:00 - 11:00" "11:00 - 12:00" "12:00 - 13:00" "13:00 - 14:00" "14:00 - 15:00" "15:00 - 16:00" "16:00 - 17:00" "17:00 - 18:00"]
[/condition]
[condition name="weekday-condition" type="weekday" value="1,2"]  
  Monday, Tuesday:    [select rangetime  "10:00 - 12:00" "12:00 - 14:00"]
[/condition]
[condition name="weekday-condition" type="weekday" value="3,4"]
  Wednesday, Thursday:  [select rangetime  "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00"]
[/condition]
[condition name="weekday-condition" type="weekday" value="5,6,0"]
  Friday, Saturday, Sunday:  [select rangetime  "10:00 - 12:00" "12:00 - 14:00" "14:00 - 16:00" "16:00 - 18:00" "18:00 - 20:00"]
[/condition]

=======Live========================================================<br />
<div class="conditional_section_season-times">
    <div class="conditional_section_element_season-times  wpdevbk_season_ wpdevbk_default_condition" id="cond_el_56_580769713784"> 
      Default: ...
    </div>
</div>
....
<div class="booking_form_garbage" id="booking_form_garbage56">
    <div style="" class="conditional_section_element_season-times  wpdevbk_season_low_season wpdevbk_optional_condition" id="cond_el_56_449274684248"> 
      Low season: ...
    </div><div style="" class="conditional_section_element_season-times  wpdevbk_season_high_season wpdevbk_optional_condition" id="cond_el_56_651996188116"> 
      High season: ...
    </div>
</div> 
==== TODO: Finish this conditon: name="visitors_selection" type="select" =================================================================<br />
  [condition name="visitors_selection" type="select" value="1" options="name:visitors"]
         [select rangetime  "12:00 - 14:00" "14:00 - 16:00"] 
  [/condition]
  [condition name="visitors_selection" type="select" value="2" options="name:visitors"]
         [select rangetime  "16:00 - 18:00" "18:00 - 20:00"] 
  [/condition]    
*/
// BOOKING FORM    PARSING    for the    C O N D I T I O N S ///////////////////////////////////////////////////////////////////////////////
function wpdev_bk_form_conditions_parsing($form, $bk_type){ global $wpdb;

    // Types of the conditions
    $condition_types =  'season|weekday|select'; 

    $pattern_to_search='%\[\s*condition\s+name="(\s*[^"]*)"\s+type="('.$condition_types.')"\s+value="(\s*[^"]*)"\s*(options="(\s*[^"]*)"\s*)?\]%';

    preg_match_all($pattern_to_search, $form, $matches, PREG_SET_ORDER);

    // Matches Array itme structure: ////////////////////////////////////////////////*
    /*
    //Full:  [0] => [condition name="times2" type="weekday" value="1,2,3,4,5" options="name:data"]
    //name:  [1] => times2
    //type:  [2] => weekday
    //value: [3] => 1,2,3,4,5
    //options[5] => name:data
    *///////////////////////////////////////////////////////////////////////////////    

    // Convert found items into the structure ///////////////////////////////////////
    $conditions=array();
    foreach ($matches as $condition) {

        $c_full  = $condition[0]; // => [condition name="times2" type="weekday" value="1,2,3,4,5"]
        $c_name  = $condition[1]; // => times2
        $c_type  = $condition[2]; // => weekday
        $c_value = $condition[3]; // => 1,2,3,4,5
                                  // => name:data    - this value inside optional parameter: options="name:data"
        if (isset($condition[5])) $c_options = $condition[5]; 
        else $c_options = false;

        $offset_open_start  = strpos($form, $c_full);
        $offset_open_end = strpos($form, ']', $offset_open_start+1);         

        $offset_close_start = strpos($form, '[/condition]', $offset_open_end+1);         
        $offset_close_end = strpos($form, ']', $offset_close_start+1);         

        $c_content           = substr($form, $offset_open_end+1, ($offset_close_start-$offset_open_end-1) ) ;
        $c_content_structure = substr($form, $offset_open_start, ($offset_close_end-$offset_open_start+1) ) ;


        if (! isset($conditions[$c_name])) $conditions[$c_name] = array();

        $conditions[$c_name][]=array(
                                'type'=>$c_type,
                                'value'=>$c_value,
                                'options'=>$c_options,
                                'content'=>$c_content,
                                'structure'=>$c_content_structure           // Full Structure to Replace
                                );
    }
    /////////////////////////////////////////////////////////////////////////////////   


    $seasons = array();
    
    // Replace the SHORTCODES in the FORM content of the form to the HTML ///////////
    foreach ($conditions as $condition_name => $values) {                   // Conditions doe the specific Name
        $class_condition_name = 'conditional_section_element_'.$condition_name .' ';
        for ($i = 0; $i < count($values); $i++) {

            $value = $values[$i];

            if ($i==0)  $my_html = '<div class="conditional_section_'.$condition_name.'">'; // First condtion, so  we are start condition section
            else        $my_html = '';

            $class_prefix = $class_condition_name;
            $c_value = explode(',', $value['value']);
            foreach ($c_value as $c_v_orig) {

                $c_v = esc_attr(strtolower(str_replace(' ','_',$c_v_orig)));

                if ( ($c_v == '*')||($c_v == '') ) $is_this_element_default = true;
                else                               $is_this_element_default = false;
                
                if ( $is_this_element_default ) $c_v =''; 

                switch ($value['type']) {
                    case 'season':   
                        $seasons[]     = $c_v_orig;
                        $class_prefix .= ' wpdevbk_season_' . $c_v;
                        break;
                    case 'weekday':
                        $class_prefix .= ' wpdevbk_weekday_' . $c_v;
                        break;
                    case 'select':                                              //TODO: Finish this condition logic
                        $class_prefix .= ' wpdevbk_select_' . $c_v;
                        break;
                    default:
                        $class_prefix .= ' wpdevbk_condition_' . $c_v;
                        break;
                } 
            }
            $my_random_id = time() * rand(0,1000);

            $my_html.= '<div id="cond_el_' . $bk_type . '_' . $my_random_id . '" ';
            $my_html.=       'class="' . $class_prefix . ( ($is_this_element_default) ? ' wpdevbk_default_condition' : ' wpdevbk_optional_condition' ) . '" ';
            $my_html.=       ( ($is_this_element_default) ? '' : ' style="display:none;" ' );
            $my_html.= '> ';
            $my_html.=      $value['content'];
            $my_html.= '</div>';

            if ($i==(count($values)-1))  $my_html .='</div>';               // Last condtion, so  we are close condition section

            $form = str_replace($value['structure'], $my_html, $form);      // Replace
        }
    }

    // Hide ALL optional  elemtns to the Garbage section using JavaScrip - after page is loaded.
    $start_script_code = "<script type='text/javascript'>";
    $start_script_code .="jQuery(document).ready( function(){ ";
    $start_script_code .="   moveOptionalElementsToGarbage( ".$bk_type." ); ";
    $start_script_code .="});";
    $start_script_code .= "</script>";   

    // Get Season Filters, using SQL from DB, which are used in FORM
    $start_script_code .= wpdev_bk_define_js_script_for_definition_season_filters( $seasons ) ;


    return $form . $start_script_code;

}

function wpdev_bk_define_js_script_for_definition_season_filters( $seasons = array() ) { global $wpdb;
    
    $start_script_code = '';
    // Get Season Filters, using SQL from DB, which are used in FORM
    if( ( count($seasons)>0 ) && (1) ){

        $season_filter_names = '';
        foreach ($seasons as $season) {
            $season_filter_names .= $wpdb->prepare( "%s," , $season );
        }
        $season_filter_names = substr($season_filter_names, 0, -1);
        //        $season_filter_names = implode("','",$seasons);
        //        $season_filter_names = "'".$season_filter_names."'";

        $where  = "";
        $my_sql = "SELECT booking_filter_id as id, title, filter 
                    FROM {$wpdb->prefix}booking_seasons 
                    WHERE title IN ( {$season_filter_names} ) 
                    ORDER BY booking_filter_id" ;
        $filter_list = $wpdb->get_results( $my_sql );     // SQL

        if (count($filter_list)>0) {

            $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();            
            $my_day =  date('m.d.Y' );          // TODAY

            $start_script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12
            for ($i = 0; $i < $max_monthes_in_calendar; $i++) {             // Days 

                $my_day_arr = explode('.',$my_day);
                $day    = ($my_day_arr[1]+0);
                $month  = ($my_day_arr[0]+0);
                $year   = ($my_day_arr[2]+0);
                $my_day_tag =   $month . '-' . $day . '-' . $year ;
                foreach ($filter_list as $filter_value) {             // Season filters

                    if ( (isset($filter_value->filter)) && (isset($filter_value->title)) ) {

                        $is_day_inside_of_filter = wpdev_bk_is_day_inside_of_filter($day , $month, $year, $filter_value->filter);
                        if ( $is_day_inside_of_filter ) {
                             $start_script_code .= " wpdev_bk_season_filter['".$my_day_tag."'][ wpdev_bk_season_filter['".$my_day_tag."'].length ] = ";
                             $start_script_code .=    " 'wpdevbk_season_" . wpdev_bk_get_escape_season_filter_name($filter_value->title)."'; ";
                        }
                    }
                }
                $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));
            }
            $start_script_code .= " }); </script>";                               //FixIn: 5.4.5.12
        }
    }

    return $start_script_code;
}

?>
