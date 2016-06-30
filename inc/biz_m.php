<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly
require_once(WPDEV_BK_PLUGIN_DIR. '/inc/lib_m.php' );
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/form/conditions.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/form/conditions.php' ); }
if (file_exists(WPDEV_BK_PLUGIN_DIR. '/inc/biz_l.php')) { require_once(WPDEV_BK_PLUGIN_DIR. '/inc/biz_l.php' ); }

global $wpdevbk_cache_booking_types_meta;
global $wpdevbk_cache_season_filters;

class wpdev_bk_biz_m {

    var $wpdev_bk_biz_l;

    function __construct(){
                add_bk_action('wpdev_booking_activation', array($this, 'pro_activate'));
                add_bk_action('wpdev_booking_deactivation', array($this, 'pro_deactivate'));

                add_bk_action('wpdev_ajax_show_cost', array($this, 'wpdev_ajax_show_cost'));

                add_bk_filter('wpdev_reapply_bk_form', array(&$this, 'wpdev_reapply_bk_form'));
                


                add_bk_action('show_additional_shortcode_help_for_form', array($this, 'show_additional_shortcode_help_for_form'));

                add_bk_filter('check_if_cost_exist_in_field', array(&$this, 'check_if_cost_exist_in_field'));


                // Resources settings //
                add_bk_action('resources_settings_table_headers', array($this, 'resources_settings_table_headers'));
                add_bk_action('resources_settings_table_footers', array($this, 'resources_settings_table_footers'));
                add_bk_action('resources_settings_table_collumns', array($this, 'resources_settings_table_collumns'));
                add_bk_filter('get_sql_4_update_def_form_in_resources', array(&$this, 'get_sql_4_update_def_form_in_resources'));
                add_bk_filter('wpdev_get_default_booking_form_for_resource', array(&$this, 'wpdev_get_default_booking_form_for_resource'));


                add_bk_action('wpdev_bk_booking_resource_page_before', array($this, 'wpdev_bk_booking_resource_page_before'));

                add_bk_action('wpdev_booking_resources_show_content', array(&$this, 'wpdev_booking_resources_show_content'));
//                    add_bk_action('wpdev_booking_settings_show_content',  array(&$this, 'settings_menu_content'));


                add_bk_filter('wpdev_season_rates', array(&$this, 'apply_season_rates'));
                add_bk_filter('get_available_days', array(&$this, 'get_available_days'));                    



                add_bk_action('wpdev_booking_resources_top_menu', array($this, 'wpdev_booking_resources_top_menu'));



                add_bk_action('show_settings_for_activating_fixed_deposit', array(&$this, 'show_settings_for_activating_fixed_deposit'));
                add_bk_filter('fixed_deposit_amount_apply', array(&$this, 'fixed_deposit_amount_apply'));

                add_bk_action('advanced_cost_management_settings', array(&$this, 'advanced_cost_management_settings'));
                add_bk_filter('advanced_cost_apply', array(&$this, 'advanced_cost_apply'));
                add_bk_filter('reupdate_static_cost_hints_in_form', array(&$this, 'reupdate_static_cost_hints_in_form'));   //FixIn: 5.4.5.5


                add_bk_action('update_booking_form_at_settings', array(&$this, 'update_booking_form_at_settings'));
                add_bk_filter('wpdev_get_booking_form', array(&$this, 'wpdev_get_booking_form'));
                add_bk_filter('wpdev_get_booking_form_content', array(&$this, 'wpdev_get_booking_form_content'));
                add_bk_action('wpdev_show_bk_form_selection', array(&$this, 'wpdev_show_bk_form_selection'));
                add_bk_action('wpdev_delete_booking_form', array(&$this, 'wpdev_delete_booking_form'));

                add_bk_action('wpdev_show_booking_form_selection', array(&$this, 'wpdev_show_booking_form_selection'));
                add_bk_action('wpdev_booking_fields_settings_top_toolbar', array(&$this, 'wpdev_booking_fields_settings_top_toolbar'));


                add_action('wpbc_define_js_vars', array(&$this, 'wpbc_define_js_vars') );
                add_action('wpbc_enqueue_js_files', array(&$this, 'wpbc_enqueue_js_files') );
                add_action('wpbc_enqueue_css_files',array(&$this, 'wpbc_enqueue_css_files') );
        
                add_bk_filter('wpdev_bk_define_additional_js_options_for_bk_shortcode', 
                                                                            array(&$this, 'wpdev_bk_define_additional_js_options_for_bk_shortcode'));

                add_filter('wpdev_booking_availability_filter', array(&$this, 'js_availability_filter') , 10, 2 );                // Write JS files
                add_filter('wpdev_booking_show_rates_at_calendar', array(&$this, 'show_rates_at_calendar') , 10, 2 );                // Write JS files

                add_bk_filter('get_unavailbale_dates_of_season_filters', array(&$this, 'get_unavailbale_dates_of_season_filters'));


                add_action('settings_set_show_cost_in_tooltips', array(&$this, 'settings_set_show_cost_in_tooltips'));    // Write General Settings
                add_action('settings_calendar_unavailable_days', array(&$this, 'settings_calendar_unavailable_days'));    // General Settings - Unavailbale days

                 add_bk_filter('wpdev_check_for_additional_calendars_in_form', array(&$this, 'wpdev_check_for_additional_calendars_in_form'));
                 add_bk_filter('check_cost_for_additional_calendars', array(&$this, 'check_cost_for_additional_calendars'));



                if ( class_exists('wpdev_bk_biz_l')) {  $this->wpdev_bk_biz_l = new wpdev_bk_biz_l();
                } else {                                $this->wpdev_bk_biz_l = false; }



    }



   // Possible to book many different items / rooms / facilties via. one form
   function wpdev_check_for_additional_calendars_in_form($form, $my_boook_type, $options = false ) {

        $calendars = array(); $cal_num = -1;$additional_calendars = '';
        while ( strpos($form, '[calendar') !== false ) { $cal_num++; $calendars[$cal_num] = array();
             $cal_start = strpos($form, '[calendar');
             $cal_end = strpos($form, ']' , $cal_start+1);

             $new_cal = substr($form, ($cal_start+9),  ($cal_end - $cal_start-9) );
             $new_cal = trim($new_cal);
             $params = explode(' ', $new_cal);
             foreach ($params as $param) {
                 $param = explode('=',$param);
                 $calendars[$cal_num][$param[0]] = $param[1];
             }

             if (isset($calendars[$cal_num]['id'])) {

                 $bk_type = $calendars[$cal_num]['id'];

                 $my_selected_dates_without_calendar = '';
                 $my_boook_count =1;
                 $bk_otions = array();

                 if (! empty($options)) {
                     $my_booking_form = $options['booking_form' ];
                     $my_selected_dates_without_calendar = $options['selected_dates' ];
                     $my_boook_count = $options['cal_count'];
                     $bk_otions = $options['otions'];
                 }

                //Fix
                $bk_cal = '<a name="bklnk'.$bk_type.'"></a><div id="booking_form_div'.$bk_type.'" class="booking_form_div">';
                 $additional_calendars .= $bk_type . ',';
                 $bk_cal .= apply_bk_filter('pre_get_calendar_html',$bk_type, $my_boook_count, $bk_otions );
                 //$bk_cal .= '<div id="calendar_booking'.$bk_type.'">&nbsp;</div>';
                 //$bk_cal .= '<textarea rows="3" cols="50" id="date_booking'.$bk_type.'" name="date_booking'.$bk_type.'" style="display:none;"></textarea>';   // Calendar code
                 $bk_cal .= '<input type="hidden" name="parent_of_additional_calendar'.$bk_type.'" id="parent_of_additional_calendar'.$bk_type.'" value="'.$my_boook_type.'" /> ';
                //Fix
                $bk_cal .= '<div id="submiting'.$bk_type.'"></div><div class="form_bk_messages" id="form_bk_messages'.$bk_type.'" ></div>'; 
                $bk_cal .= wp_nonce_field('INSERT_INTO_TABLE',  ("wpbc_nonce" . $bk_type) ,  true , false );
                $bk_cal .= wp_nonce_field('CALCULATE_THE_COST', ("wpbc_nonceCALCULATE_THE_COST" . $bk_type) ,  true , false );
                $bk_cal .= '</div>';
                 $additional_bk_types = array();

                 $start_script_code = apply_bk_filter('get_script_for_calendar',$bk_type, $additional_bk_types, $my_selected_dates_without_calendar, $my_boook_count );
                 $start_script_code = apply_bk_filter('wpdev_bk_define_additional_js_options_for_bk_shortcode', $start_script_code, $bk_type, $bk_otions);  

                 $form = substr_replace($form,  $bk_cal  .$start_script_code  , $cal_start, ($cal_end - $cal_start+1) );
                 //$form .= '<div  id="paypalbooking_form'.$bk_type.'"></div>';

                 //Todo: this element is add showhint elemnts, think how to make it in more good way, 2 lines above is added showhint shortcode
                 // its also not really correct thing
                 //$form = $this->wpdev_reapply_bk_form($form, $bk_type);     //cost hint
             }

         }
         if (isset($additional_calendars))
             if ($additional_calendars!=''){
                 $additional_calendars = substr($additional_calendars, 0, -1);
                 $form .= ' <input type="hidden" name="additional_calendars'.$my_boook_type.'" id="additional_calendars'.$my_boook_type.'" value="'.$additional_calendars.'" /> ';

             }
         return $form;
   }


   // Get Form with replaced old ID to new  one
   function get_bk_form_with_correct_id($bk_form, $correct_id,  $replace_id) {

                $bk_form_arr = explode('~',$bk_form);
                $formdata_additional = '';
                for ($i = 0; $i < count($bk_form_arr); $i++) {
                    $my_form_field = explode( '^', $bk_form_arr[$i] );
                    if ($formdata_additional !=='') $formdata_additional .=  '~';

                     if ( substr( $my_form_field[1],  strlen($my_form_field[1]) -2 ,2) == '[]' )
                         $my_form_field[1] = substr( $my_form_field[1], 0, ( strlen($my_form_field[1]) - strlen('' .$replace_id)  ) - 2 ) . $correct_id . '[]';
                     else
                         $my_form_field[1] = substr( $my_form_field[1], 0, ( strlen($my_form_field[1]) - strlen('' .$replace_id)  )  ) . $correct_id  ;

                     $formdata_additional .= $my_form_field[0] . '^' . $my_form_field[1] . '^' . $my_form_field[2];
                }

                return $formdata_additional;
   }


   // Get total and costs for each other calendars, which are inside of this form
   function check_cost_for_additional_calendars($summ, $post_form, $post_bk_type,  $time_array , $is_discount_calculate = true ){

        $summ_total = $summ;

            // Check for additional calendars:
            $send_form_content = $post_form;
            $offset = 0;
            $summ_additional = array();
            $dates_additional = array();
            while ( strpos( $send_form_content , 'textarea^date_booking' , $offset) !== false ) {
                $offset = strpos( $send_form_content , 'textarea^date_booking' , $offset)+1;
                $offset_end = strpos( $send_form_content , '^' , $offset+20);
                $other_bk_id = substr($send_form_content, $offset+20, $offset_end - $offset -20 ) ;                             // ID

                $offset_end_dates_data = strpos( $send_form_content , '~' ,  $offset_end );
                if ($offset_end_dates_data === false) { $offset_end_dates_data = strlen($send_form_content); }
                $other_bk_dates = substr($send_form_content, $offset_end+1 , $offset_end_dates_data - $offset_end-1  );         // Dates

                // Replace inside of form old ID to the new correct ID
                $send_form_content = $this->get_bk_form_with_correct_id($send_form_content, $other_bk_id,  $post_bk_type );   //Form

                if (empty($other_bk_dates) ) $summ_add = 0;
                else $summ_add = apply_bk_filter('wpdev_get_bk_booking_cost', $other_bk_id , $other_bk_dates , $time_array , $send_form_content , $is_discount_calculate );
                $summ_add = floatval( $summ_add );
                $summ_add = round($summ_add,2);
                $summ_additional[ $other_bk_id ]= $summ_add;
                $dates_additional[ $other_bk_id ]= $other_bk_dates;

                $send_form_content = $post_form;
            }

//debuge($summ, $summ_additional);
            foreach ($summ_additional as $ss) { $summ_total += $ss; }           // Summ all costs

//debuge(array($summ_total, $summ_additional, $dates_additional));
        return array($summ_total, $summ_additional, $dates_additional) ;

   }


// S U P P O R T       F u n c t i o n s    //////////////////////////////////////////////////////////////////////////////////////////////////

    // Reset to Payment form
    function reset_to_default_form($form_type ){

                    return '[calendar] \n\
<div class="payment-form"> \n\
 <div class="form-hints"> \n\ 
      '.__('Dates' ,'booking').': [selected_short_timedates_hint]  ([nights_number_hint] - '.__('night(s)' ,'booking').')<br><br> \n\ 
      '.__('Full cost of the booking' ,'booking').': [cost_hint] <br> \n\ 
 </div><hr/> \n\ 
 <p>'.__('First Name (required)' ,'booking').':<br />[text* name] </p> \n\
 <p>'.__('Last Name (required)' ,'booking').':<br />[text* secondname] </p> \n\
 <p>'.__('Email (required)' ,'booking').':<br />[email* email] </p> \n\
 <p>'.__('Phone' ,'booking').':<br />[text phone] </p> \n\
 <p>'.__('Address (required)' ,'booking').':<br />  [text* address] </p> \n\  
 <p>'.__('City (required)' ,'booking').':<br />  [text* city] </p> \n\
 <p>'.__('Post code (required)' ,'booking').':<br />  [text* postcode] </p> \n\  
 <p>'.__('Country (required)' ,'booking').':<br />  [country] </p> \n\
 <p>'.__('Adults' ,'booking').':  [select visitors class:span1 "1" "2" "3" "4"] '.__('Children' ,'booking').': [select children class:span1 "0" "1" "2" "3"]</p> \n\
 <p>'.__('Details' ,'booking').':<br /> [textarea details] </p> \n\
 <p>[checkbox* term_and_condition use_label_element "'.__('I Accept term and conditions' ,'booking').'"] </p> \n\
 <p>[captcha]</p> \n\
 <p>[submit class:btn "'.__('Send' ,'booking').'"]</p> \n\
</div>';

     }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Define JavaScripts Variables               //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function wpbc_define_js_vars( $where_to_load = 'both' ){ 
        
        $wpdev_bk_season_filter = '';
        $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();            
        $my_day_tag =  date('n-j-Y' );                                          // TODAY
        $my_day_arr = explode('-',$my_day_tag);
        $day    = ($my_day_arr[1]+0); 
        $month  = ($my_day_arr[0]+0); 
        $year   = ($my_day_arr[2]+0);        
        
        for ($i = 0; $i < $max_monthes_in_calendar ; $i++) {                    // Days 
            $wpdev_bk_season_filter .= '"'.$my_day_tag.'":[],';                  //FixIn:6.1
            $day++;
            $my_day_tag =  date('n-j-Y' , mktime(0, 0, 0, $month, $day, $year ));
        }            
        
        if (  ! empty( $wpdev_bk_season_filter) )                               //FixIn:6.1
            $wpdev_bk_season_filter = substr ( $wpdev_bk_season_filter, 0, -1); //FixIn:6.1
        
        wp_localize_script('wpbc-global-vars', 'wpbc_global4', array(
              'bk_cost_depends_from_selection_line1' => apply_bk_filter('get_currency_info', 'paypal') . ' ' . esc_js(__('per 1 day' ,'booking'))
            , 'bk_cost_depends_from_selection_line2' => '% ' . esc_js(__('from the cost of 1 day ' ,'booking'))
            , 'bk_cost_depends_from_selection_line3' => sprintf( esc_js(__('Additional cost in %s per 1 day' ,'booking')), apply_bk_filter('get_currency_info', 'paypal') )        
            , 'bk_cost_depends_from_selection_line14summ' => apply_bk_filter('get_currency_info', 'paypal') . ' ' . esc_js(__(' for all days!' ,'booking'))
            , 'bk_cost_depends_from_selection_line24summ' => '% ' . esc_js( __('for all days!' ,'booking') )
            , 'wpdev_bk_season_filter' => '{'.$wpdev_bk_season_filter.'}'       //FixIn:6.1
            //, 'wpdev_bk_season_filter_action' => 'false;'                     //FixIn:6.1
            ,'wpbc_available_days_num_from_today' =>  intval( get_bk_option('booking_available_days_num_from_today') )
        ) );                
    }    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load JavaScripts Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_js_files( $where_to_load = 'both' ){ 
        wp_enqueue_script( 'wpbc-bm', WPDEV_BK_PLUGIN_URL . '/inc/js/biz_m'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-global-vars' ), '1.0');
        wp_enqueue_script( 'wpbc-conditions', WPDEV_BK_PLUGIN_URL . '/inc/form/js/conditions'.((WP_BK_MIN)?'.min':'').'.js', array( 'wpbc-bm' ), '1.0');
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Load CSS Files                     //////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////   
    function wpbc_enqueue_css_files( $where_to_load = 'both' ){         
        
    }
    
    
    // write JS variables
    function js_define_variables(){
    ?>
var bk_cost_depends_from_selection_line1 = '<?php echo apply_bk_filter('get_currency_info', 'paypal'). ' '.   esc_js(__('per 1 day' ,'booking')); ?>';
var bk_cost_depends_from_selection_line2 = '<?php echo '% '. esc_js(__('from the cost of 1 day ' ,'booking')); ?>';
var bk_cost_depends_from_selection_line3 = '<?php echo sprintf( esc_js(__('Additional cost in %s per 1 day' ,'booking')), apply_bk_filter('get_currency_info', 'paypal') ); ?>';
var bk_cost_depends_from_selection_line14summ = '<?php echo apply_bk_filter('get_currency_info', 'paypal') . ' '.   esc_js(__(' for all days!' ,'booking')); ?>';
var bk_cost_depends_from_selection_line24summ = '<?php echo '% '. esc_js(__(' for all days!' ,'booking')); ?>';
var wpdev_bk_season_filter = [];<?php
        $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();            
        $my_day =  date('m.d.Y' );                                          // TODAY
        for ($i = 0; $i < $max_monthes_in_calendar ; $i++) {                // Days 

            $my_day_arr = explode('.',$my_day);
            $day    = ($my_day_arr[1]+0);
            $month  = ($my_day_arr[0]+0);
            $year   = ($my_day_arr[2]+0);
            $my_day_tag =   $month . '-' . $day . '-' . $year ;

            echo " wpdev_bk_season_filter['".$my_day_tag."'] = [];";

            $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));
        }            
    }

    // Apply scripts for the conditions in the rnage days selections
    function wpdev_bk_define_additional_js_options_for_bk_shortcode( $start_script_code, $bk_type, $bk_otions){

        /*  $options    structure:
            {select-day condition="season" for="High season" value="14"},
            {select-day condition="season" for="Low season" value="2-5"},
            {select-day condition="weekday" for="1" value="4"},
            {select-day condition="weekday" for="5" value="3"},
            {select-day condition="weekday" for="6" value="2,7"},
            {select-day condition="weekday" for="0" value="7,14"}
         */
        if (empty($bk_otions)) return $start_script_code;                   // Return default scripts if options is empty.

        /* $matches    structure:
         * Array
            (
                [0] => {select-day condition="weekday" for="6" value="2,7"},
                [1] => select-day
                [2] => condition
                [3] => weekday
                [4] => for
                [5] => 6
                [6] => value
                [7] => 2,7
            )
         */
        $param ='\s*([condition|for|value]+)=[\'"]{1}([^\'"]+)[\'"]{1}\s*'; // Find all possible options
        $pattern_to_search='%\s*{([^\s]+)'. $param . $param . $param .'}\s*[,]?\s*%';
        preg_match_all($pattern_to_search, $bk_otions, $matches, PREG_SET_ORDER);
//debuge($matches);  
        /////////////////////////////////////////////////////////////////////////////////////////////////


        /*     Strucure example
               (
                    [select-day] => Array
                        (
                            [season] => Array
                                (
                                    [0] => Array
                                        (
                                            [for] => High season
                                            [value] => 14
                                        )

                                    [1] => Array
                                        (
                                            [for] => Low season
                                            [value] => 2-5
                                        )

                                )

                            [weekday] => Array
                                (
                                    [0] => Array
                                        (
                                            [for] => 1
                                            [value] => 4
                                        )

                                    [1] => Array
                                        (
                                            [for] => 5
                                            [value] => 3
                                        )

                                    [2] => Array
                                        (
                                            [for] => 6
                                            [value] => 2,7
                                        )

                                    [3] => Array
                                        (
                                            [for] => 0
                                            [value] => 7,14
                                        )

                                )

                        )

                )
         */
        $conditions = array();                                              // Create strucure from the options:
        foreach ($matches as $option) {
            if (! isset($conditions[ $option[1] ]) ) $conditions[ $option[1] ] = array();
            //                       select-day    season                     select-day    season            
            if (! isset($conditions[ $option[1] ][$option[3]]) ) $conditions[ $option[1] ][ $option[3] ] = array();

            $conditions[ $option[1] ][ $option[3] ][]=array();
            $ind = count(  $conditions[ $option[1] ][ $option[3] ] ) - 1;   // Get index of the specific rule for the conditions.

            $conditions[ $option[1] ][ $option[3] ][$ind][ $option[4]  ] = $option[5];    // [for] => High season
            $conditions[ $option[1] ][ $option[3] ][$ind][ $option[6]  ] = $option[7];    // [value] => 14            
        }
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////
//debuge($conditions);            

        $script_code = '';                                                  // Define JS variables for the calendar

        //Define the Start day depend from the Season Filter 
        if (isset($conditions['start-day']) ) {
            // S E A S O N S  conditions ///////////////////////////////////
            $seasons = array();
            if (isset($conditions['start-day']['season']) ) {

                $script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12

                $script_code .= " if (typeof( wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "] ) == 'undefined'){ ";
                $script_code .= " wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "] = []; } ";

                foreach ($conditions['start-day']['season'] as $season) {

                    $seasons[]              = $season['for'];                        
                    $escaped_season_title   = wpdev_bk_get_escape_season_filter_name( $season['for'] );
                    $condition_season_value = rangeNumListToCommaNumList( $season['value'] );

                    $script_code .= " wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "][ wpdev_bk_seasons_conditions_for_start_day[". $bk_type. "].length] = ['".$escaped_season_title."',[".$condition_season_value."]]; ";
                }
                $script_code .= " }); </script>";                               //FixIn: 5.4.5.12

                // Define Script for Applying specific CSS Classes into the dates in Calendar
                $script_code .= wpdev_bk_define_js_script_for_definition_season_filters( $seasons ) ;                  
            } //////////////////////////////////////////////////////////////

        }

        if (isset($conditions['select-day']) ) {

            // S E A S O N S  conditions ///////////////////////////////////
            $seasons = array();
            if (isset($conditions['select-day']['season']) ) {

                $script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12

                $script_code .= " if (typeof( wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "] ) == 'undefined'){ ";
                $script_code .= " wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "] = []; } ";

                foreach ($conditions['select-day']['season'] as $season) {

                    $seasons[]              = $season['for'];                        
                    $escaped_season_title   = wpdev_bk_get_escape_season_filter_name( $season['for'] );
                    $condition_season_value = rangeNumListToCommaNumList( $season['value'] );

                    $script_code .= " wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "][ wpdev_bk_seasons_conditions_for_range_selection[". $bk_type. "].length] = ['".$escaped_season_title."',[".$condition_season_value."]]; ";
                }
                $script_code .= " }); </script>";                               //FixIn: 5.4.5.12
//debuge($seasons);
                // Define Script for Applying specific CSS Classes into the dates in Calendar
                $script_code .= wpdev_bk_define_js_script_for_definition_season_filters( $seasons ) ;                  
            } //////////////////////////////////////////////////////////////


            // Weekday conditions //////////////////////////////////////////
            if (isset($conditions['select-day']['weekday']) ) {

                $script_code .= "<script type='text/javascript'> jQuery(document).ready( function(){ ";     //FixIn: 5.4.5.12
                $script_code .= " ";
                $script_code .= " if (typeof( wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "] ) == 'undefined'){ ";
                $script_code .= " wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "] = []; } ";

                foreach ($conditions['select-day']['weekday'] as $weekday) {
                    $day_of_week             = $weekday['for'];
                    $condition_weekday_value = rangeNumListToCommaNumList( $weekday['value'] );
                    $script_code .= " wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "][ wpdev_bk_weekday_conditions_for_range_selection[". $bk_type. "].length] = [".$day_of_week.",[".$condition_weekday_value."]]; ";
                }
                $script_code .= " }); </script>";                               //FixIn: 5.4.5.12
            } //////////////////////////////////////////////////////////////                
        }            
        return $script_code . $start_script_code;
    }







//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function get_default_booking_form($bk_type){
        global $wpdb;
        $res_view_max = $wpdb->get_results( $wpdb->prepare( "SELECT default_form FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d ",  $bk_type ) );
        $default_form =  $res_view_max[0]->default_form;
        if ($default_form == '') return 'standard';
        else return $default_form;
    }


     // Just Get ALL booking types from DB
    function get_standard_cost_for_bk_resource($booking_type_id = 0) {

        $res = $this->get_booking_types($booking_type_id);

        if (count($res)>0) {
            return $res[0]->cost;
        } else return 0;

    }

    // Just Get ALL booking types from DB
    function get_booking_types($booking_type_id = 0) {
        global $wpdb;
        $max_stringg_sql='';
        $order_type = 'title';

        if ( class_exists('wpdev_bk_biz_l')) {  // If Business Large then get resources from that
            $types_list = apply_bk_filter('get_booking_types_hierarhy_linear',array() );
            //$types_list = apply_bk_filter('multiuser_resource_list', $types_list);

            for ($i = 0; $i < count($types_list); $i++) {
                $types_list[$i]['obj']->count = $types_list[$i]['count'];
                $types_list[$i] = $types_list[$i]['obj'];
                if ( (isset($booking_type_id)) &&(isset($types_list[$i]->booking_type_id)) && ($booking_type_id != 0) && ($booking_type_id == $types_list[$i]->booking_type_id ) ) return $types_list[$i];
            }
            if ($booking_type_id == 0) return $types_list;
        }
                
        // Get booking resources only  as numbers                               // FixIn:5.4.3
        $booking_type_id_array = explode(',',$booking_type_id);
        $booking_type_id = array();
        foreach ( $booking_type_id_array as $bk_t ) {
            $bk_t = (int) $bk_t;
            if ( $bk_t > 0 ) 
                $booking_type_id[] = $bk_t;
        }
        $booking_type_id = implode(',',$booking_type_id);

        if ($booking_type_id == 0 ) {  // Normal getting
            $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, cost {$max_stringg_sql} FROM {$wpdb->prefix}bookingtypes  ORDER BY {$order_type}" );
        } else {
            $types_list = $wpdb->get_results( "SELECT booking_type_id as id, title, cost {$max_stringg_sql} FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id IN ( {$booking_type_id} )" );
        }
        //$types_list = apply_bk_filter('multiuser_resource_list', $types_list);

        return $types_list;
    }

    // Get meta data from booking type
    function get_bk_type_meta($type_id, $meta_key){
        global $wpdb;

        if ( IS_USE_WPDEV_BK_CACHE ) {
            global $wpdevbk_cache_booking_types_meta;            
            if (! isset($wpdevbk_cache_booking_types_meta)) $wpdevbk_cache_booking_types_meta = array();
            if (! isset($wpdevbk_cache_booking_types_meta[$meta_key])) {
                $wpdevbk_cache_booking_types_meta[$meta_key] = array();

                $result = $wpdb->get_results( $wpdb->prepare( "SELECT type_id, meta_id as id, meta_value as value FROM {$wpdb->prefix}booking_types_meta WHERE  meta_key = %s "
                                                , $meta_key ) );

                foreach ($result as $value) {
                    if (!isset($wpdevbk_cache_booking_types_meta[$meta_key][$value->type_id])) $wpdevbk_cache_booking_types_meta[$meta_key][$value->type_id] = array();
                    $wpdevbk_cache_booking_types_meta[$meta_key][$value->type_id][] = $value;
                }
                if ( ! isset( $wpdevbk_cache_booking_types_meta[$meta_key][$type_id] ) ) return array();
                return $wpdevbk_cache_booking_types_meta[$meta_key][$type_id];
            } else {
                if ( ! isset( $wpdevbk_cache_booking_types_meta[$meta_key][$type_id] ) ) return array();
                return $wpdevbk_cache_booking_types_meta[$meta_key][$type_id];
            }
        } else {   
            $result = $wpdb->get_results( $wpdb->prepare( "SELECT meta_id as id, meta_value as value FROM {$wpdb->prefix}booking_types_meta WHERE type_id = %d AND meta_key = %s "
                                            , $type_id, $meta_key ) );
            return $result;
        }
    }

    // Set meta data from booking type
    function set_bk_type_meta($type_id, $meta_key, $meta_value){
        global $wpdb;

        $result = $wpdb->get_results( $wpdb->prepare( "SELECT count(type_id) as cnt FROM {$wpdb->prefix}booking_types_meta WHERE type_id = %d AND meta_key = %s "
                                    , $type_id, $meta_key ) );
//debuge($type_id, $meta_key, $meta_value, $result);
        if ( $result[0]->cnt > 0 ) {
            if ( false === $wpdb->query(( "UPDATE {$wpdb->prefix}booking_types_meta SET meta_value = '".$meta_value."' WHERE type_id = " .  $type_id . " AND meta_key ='".$meta_key."'") ) ){
//debuge($type_id,$meta_key, $meta_value);
               bk_error('Error during updating to DB booking availability of booking resource',__FILE__,__LINE__ );
               return false;
            }
        } else {
            if ( false === $wpdb->query(( "INSERT INTO {$wpdb->prefix}booking_types_meta ( type_id, meta_key, meta_value) VALUES ( " .  $type_id . ", '" .  $meta_key . "', '" .  $meta_value . "' );") ) ){
//debuge($type_id,$meta_key, $meta_value);
                bk_error('Error during updating to DB booking availability of booking resource' ,__FILE__,__LINE__);
               return false;
            }
        }
        return true;
    }

    //Get available days depends from seaosn filter
    function get_available_days( $type_id ){
        $filters = array(); global $wpdb;
        $return_result = array('available'=>true,'days'=> $filters ) ;

        $availability_res = $this->get_bk_type_meta($type_id,'availability');
        if ( count($availability_res)>0 ) {
            if ( is_serialized( $availability_res[0]->value ) )   $availability = unserialize($availability_res[0]->value);
            else                                                  $availability = $availability_res[0]->value;

            $days_avalaibility = $availability['general'];
            $seasonfilter      = $availability['filter'];
            if (is_array($seasonfilter))
                foreach ($seasonfilter as $key => $value) {
                    if ($value == 'On') {


                        if ( IS_USE_WPDEV_BK_CACHE ) {
                            global $wpdevbk_cache_season_filters;
                            $filter_id = $key;
                            if (! isset($wpdevbk_cache_season_filters)) $wpdevbk_cache_season_filters = array();
                            if (! isset($wpdevbk_cache_season_filters[$filter_id])) {
                                $result = $wpdb->get_results( "SELECT booking_filter_id as id, filter FROM {$wpdb->prefix}booking_seasons" );

                                foreach ($result as $value) {
                                    $wpdevbk_cache_season_filters[$value->id] = array($value);
                                }
                                $result = $wpdevbk_cache_season_filters[$filter_id];
                            } else {
                                $result = $wpdevbk_cache_season_filters[$filter_id];
                            }
                        } else
                            $result = $wpdb->get_results( $wpdb->prepare( "SELECT filter FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d" , $key ) );
                        if (! empty($result))
                        foreach($result as $filter) {
                            /*
                            if ( is_serialized( $filter->filter ) )    $filter = unserialize($filter->filter);
                            else                                                   $filter = $filter->filter;
                            $filters[]=$filter; 

                            */
                            //FixIn:6.0.1.8
                            if ( is_serialized( $filter->filter ) ) $filter_data = unserialize($filter->filter); 
                            else                                    $filter_data = $filter->filter;
                            
                            if ( isset($filter->id) ) $filters[$filter->id]=$filter_data;
                            else                      $filters[]=$filter_data;
                            
                            
                        }
                    }
                }
        }
          else  $days_avalaibility = 'On';


        if ( $days_avalaibility == 'On' ) $return_result['available'] = true;
        else                              $return_result['available'] = false;
        $return_result['days'] = $filters;
//debuge($return_result);
        return $return_result;
    }

    // Set available and unavailable days into calendar form using JS variables.
    function js_availability_filter($blank, $type_id ) { $script = '';
        $res_days = $this->get_available_days( $type_id );
//debuge($res_days);
        $version = '1.0';

        $script .= ' is_all_days_available['.$type_id.'] = ' . ($res_days['available']+0) . '; ';
        $script .= ' avalaibility_filters['.$type_id.'] = []; ';            

         // foreach ($res_days['days'] as $value) { // loop all assign filters
            foreach ($res_days['days'] as $filter_id => $value) {                             // FixIn: 6.0.1.8
                $version = '1.0';

                if (isset($value['version']))                               // Version 2.0
                    if ($value['version'] == '2.0')   {
                        $version = '2.0';
                        $value_js_header =  '[ ["2.0"], [';
                        $value_js = '';
                        foreach ($value as $yy => $monthes) {
                            if ( ($yy != 'name') && ($yy != 'version') )
                                foreach ($monthes as $mm=>$days) {
                                    if ($mm>0)
                                        foreach ($days as $dd=>$dvalue) {
                                            if ($dvalue==1) {
                                               $value_js  .= '"' . $yy . '-' . $mm . '-' . $dd . '", ';
                                            }
                                        }
                                }
                        }
                                                
                        if ( ! empty( $value_js ) ) {                           // FixIn: 5.4.1
                            $value_js = substr($value_js, 0, -2);               // Delete last ", "
                          //$value_js = $value_js_header . $value_js . '] ]';
                            $value_js = $value_js_header . $value_js . '], '.$filter_id.' ]';     // FixIn: 6.0.1.8
                            $script .= ' avalaibility_filters['.$type_id.'][ avalaibility_filters['.$type_id.'].length ]= '.$value_js . '; ';
                        }
                    }


                if ($version == '1.0') {                                    // Version 1.0

                    $value_js =  '[ [   ';
                    foreach ($value['weekdays'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; }// loop week days
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], [   ';
                    foreach ($value['days'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; }// loop all days numbers
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], [   ';
                    foreach ($value['monthes'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; }// loop all monthes nums
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                    $value_js .=  '], [   ';
                    foreach ($value['year'] as $key => $val) { if ($val == 'On') $value_js .= $key . ', '; } // loop all years nums
                    $value_js =  substr($value_js, 0, -2); //Delete last ", "
                  //$value_js .=  '] ]';
                    $value_js .=  '], '.$filter_id.' ]';                      // FixIn: 6.0.1.8
                    
                    
                    // Time availability
                    //FixIn: 5.4.3
                    if (  (! empty($value['start_time']))  &&   (! empty($value['end_time']))  )  {
//                        $strt_time = explode(':',$value['start_time']);
//                        $fin_time = explode(':',$value['end_time']);
//                        $script .= ' if(typeof( global_avalaibility_times['.$type_id.']) == "undefined") {  global_avalaibility_times['.$type_id.'] = [];  }';
//
//
//
//                        if (  (count($res_days['days']) ==1 ) &&  (($res_days['available']+0) == 0)  ){
//                            $script .= ' is_all_days_available['.$type_id.'] = ' . 1 . '; '; // Set all days available
//                            // set start unavailable hours
//                            $script .= ' global_avalaibility_times['.$type_id.'][ global_avalaibility_times['.$type_id.'].length ]= [ ["00","00"],  ["'.$strt_time[0] . '", "'.$strt_time[1] . '"]  ]; ';
//                            // set end unavailable hours
//                            $script .= ' global_avalaibility_times['.$type_id.'][ global_avalaibility_times['.$type_id.'].length ]= [ ["'.$fin_time[0] . '", "'.$fin_time[1] . '"], ["23","59"]  ]; ';
//
//                        } else
//                          $script .= ' global_avalaibility_times['.$type_id.'][ global_avalaibility_times['.$type_id.'].length ]= [ ["'.$strt_time[0] . '", "'.$strt_time[1] . '"],  ["'.$fin_time[0] . '", "'.$fin_time[1] . '"]  ]; ';
//
                    } else
                     $script .= ' avalaibility_filters['.$type_id.'][ avalaibility_filters['.$type_id.'].length ]= '.$value_js . '; ';
                }
            }
//debuge($script);
        return $script;//' alert('.$type_id.'); ';
    }



    function get_unavailbale_dates_of_season_filters($blank, $type_id ){
        $res_days = $this->get_available_days( $type_id );

        return($res_days);
    }

    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // B o o k i n g   F O R M S    customization
    // ////////////////////////////////////////////////

    // Show select box for selection of several booking forms at the settings page of form fields customisation page
    // ADD New  form and have link to Delete exist form
    function wpdev_booking_fields_settings_top_toolbar() {
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
        if ( (! $is_can) && (! WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) return;

        // Create new custom empty form here
        if ( (isset($_POST['booking_form_new_name'])) && (! empty($_POST['booking_form_new_name'])) ) {
            $new_name = substr( $_POST['booking_form_new_name'] , 0 ,30 );

            // Remove all symbols, which can  generate an issues
            /* $new_name = str_replace('+','plus',    $new_name);
            $new_name = str_replace('%','percent', $new_name);
            $new_name = str_replace(' ','_', $new_name); /**/
            $new_name = sanitize_title( $new_name );

            $_GET['booking_form'] = $new_name;
            $booking_forms_extended = get_bk_option( 'booking_forms_extended');

            $my_default_form      =  apply_bk_filter('wpdev_get_default_form', '' );
            $my_default_form_show =  apply_bk_filter('wpdev_get_default_form_show', '' );
            $my_default_form        = str_replace('\\n\\','',$my_default_form ) ;
            $my_default_form_show   = str_replace('\\n\\','',$my_default_form_show ) ;

            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);

                $i = 0;
                // Check already exist names for rewrite it
                foreach ($booking_forms_extended as $value) {
                    if ($value['name'] == $new_name){
                        $i = 'modified';
                        break;
                    } $i++;
                }
                if ($i !== 'modified') {  // add new booking form
                    $booking_forms_extended[count($booking_forms_extended)] = array('name'=>$new_name, 'form'=>$my_default_form, 'content'=>$my_default_form_show); //reset previously exist form with  the same name
                }

            } else {
                $booking_forms_extended = array( array('name'=>$new_name, 'form'=>$my_default_form, 'content'=>$my_default_form_show) );
            }

            update_bk_option( 'booking_forms_extended' , serialize($booking_forms_extended) );
            //Refresh  the page
            ?>
            <script type="text/javascript">
                window.location.href='<?php echo 'admin.php?page='.WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking-option&tab=form&booking_form='.$new_name;?>';
            </script>
            <?php
        }


        $booking_forms_extended = get_bk_option( 'booking_forms_extended');
        if ($booking_forms_extended !== false) {
            if ( is_serialized( $booking_forms_extended ) )
                $booking_forms_extended = unserialize($booking_forms_extended);

                // If the Name of Custom  form contain "+" symbol, it can generate issue of not loading custom form. 
                // Thats  why  need to replace this symbol.
                $is_fix_exist = false;
                foreach ($booking_forms_extended as $key => $value) {
                    /*
                    if(strpos($value['name'], ' ') !== false) {

                        $value['name'] = str_replace(' ', '_', $value['name']);
                        $booking_forms_extended[$key]['name'] = $value['name'];
                        $is_fix_exist = true;
                    } /**/                       

                    if(strpos($value['name'], '+') !== false) {
                        $value['name'] = str_replace('+', 'plus', $value['name']);
                        $booking_forms_extended[$key]['name'] = $value['name'];
                        $is_fix_exist = true;
                    }                        
                }
                if ( $is_fix_exist ) {
                    update_bk_option( 'booking_forms_extended' , serialize($booking_forms_extended) );
                    $booking_forms_extended = get_bk_option( 'booking_forms_extended');
                    $booking_forms_extended = unserialize($booking_forms_extended);
                }

        } else {
            $booking_forms_extended = array();
        }
//debuge($booking_forms_extended);            
        ?>
          <div class="btn-toolbar" style="margin:0;float: left; height: auto;">

                <div class="btn-group" style="margin:0 5px 5px;float: left; height: auto;">
<!--                        <label class="wpbc_inline_legend" for="select_booking_form"><?php _e('Custom Form' ,'booking'); ?></label>-->
                    <select name="select_booking_form" id="select_booking_form" onchange="javascript:changeBookingForm(this);" style="margin:0px;">

                            <option style="padding:3px;border-bottom: 1px solid #ccc;" value="standard" <?php if (  (! isset($_GET['booking_form'])) || ($_GET['booking_form'] == 'standard')  ) { echo 'selected="selected"'; } ?>  ><?php _e('Standard' ,'booking'); ?></option>

                            <optgroup label="<?php _e('Custom Form' ,'booking'); ?>">
                            <?php
                            foreach ($booking_forms_extended as $value) { ?>
                                <option value="<?php echo $value['name']; ?>"
                                        <?php if ( (isset($_GET['booking_form']) ) && ($_GET['booking_form'] == $value['name'] ) ) { echo 'selected="selected"'; } ?>
                                        ><?php echo $value['name']; ?></option>
                            <?php  }
                            ?>
                            </optgroup>
                    </select>
                    <a     data-original-title="<?php _e('Load selected booking form' ,'booking'); ?>"  rel="tooltip" 
                           class="tooltip_top button button-secondary"  
                           onclick="javascript:changeBookingForm(document.getElementById('select_booking_form'));" /><?php _e('Load' ,'booking'); ?></a>
                </div>
<?php
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
?>
                <div style="margin:0 5px 5px;float: left; height: auto;">
                    <?php if (  (isset($_GET['booking_form'])) && ($_GET['booking_form'] != 'standard')  ) { ?>
                    <a  data-original-title="<?php _e('Delete selected booking form' ,'booking'); ?>"  rel="tooltip" 
                        class="tooltip_top button button-secondary <?php if (  (! isset($_GET['booking_form'])) || ($_GET['booking_form'] == 'standard')  ) {echo 'disabled';} ?>"
                        onclick="javascript:
                                if ( bk_are_you_sure('<?php echo esc_js(__('Do you really want to delete selected booking form ?' ,'booking')); ?>') )
                                    delete_bk_form( document.getElementById('select_booking_form').options[document.getElementById('select_booking_form').selectedIndex].value, <?php echo $user_bk_id; ?> );
                                " >
                        <?php _e('Delete' ,'booking'); ?></a>
                    <?php } ?>
                </div>
                <div style="margin:0 5px 5px;float: left; height: auto;">
                    <a     data-original-title="<?php _e('Add new custom form' ,'booking'); ?>"  rel="tooltip" class="tooltip_top button-primary button" id="bk_form_plus"
                           onclick="javascript:addBKForm('Plus');" /><?php _e('Add New Custom Form' ,'booking'); ?></a>
                </div>
                <div style="margin:0 5px 5px;float: left; height: auto;display:none;" id="bk_form_addbutton" >
                  <form  name="post_settings_form_fields_new_form" action="" method="post" id="post_settings_form_fields_new_form" style="line-height: 10px;margin: 0;">
                      <div class="btn-group">                         
                          <input  type="text" placeholder="<?php echo __('Type the name of booking form' ,'booking'); ?>"
                                  value="" name="booking_form_new_name" id="booking_form_new_name"
                                  maxlength="30"
                                  style="margin-bottom:0px;" />
                          <a  data-original-title="<?php _e('Create new form' ,'booking'); ?>"  rel="tooltip" 
                              class="tooltip_top button button-primary"
                              onclick="javascript:document.forms['post_settings_form_fields_new_form'].submit();"
                              style="margin-bottom:0px;" ><?php _e('Create' ,'booking'); ?></a>
                      </div>
                      <a  data-original-title="<?php _e('Delete form' ,'booking'); ?>"  rel="tooltip" 
                          class="tooltip_top button button-secondary"
                          style="margin-bottom:0px;" 
                          onclick="javascript:document.getElementById('bk_form_plus').style.display='block'; document.getElementById('bk_form_addbutton').style.display='none';jQuery('#booking_form_new_name').val('');"
                          ><?php _e('Cancel' ,'booking'); ?></a>                        
                  </form>
                </div>                  
          </div>
          <span class="booking-submenu-tab-separator-vertical" style="float: left;height: 12px;margin: 3px 10px;"></span>
        <?php
    }

    // DELETE specific booking form
    function wpdev_delete_booking_form(){
        if (isset($_POST['formname'])) {
            $form_name = $_POST['formname'];
            // this function is executed only in admin panel. In some servers, the WP_ADMIN constant is not defined, and thats can generate issue in MultiUser version, where will not select the specific user                
            if (!defined('WP_ADMIN'))  define('WP_ADMIN',  true );
            $booking_forms_extended = get_bk_option( 'booking_forms_extended');
            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);

                $booking_forms_extended_new = array();
                // Check already exist names for rewrite it
                foreach ($booking_forms_extended as $value) {

                    if ($value['name'] == $form_name){   continue;  //skip it
                    } else {                             $booking_forms_extended_new[] = $value; }

                }

                update_bk_option( 'booking_forms_extended' , serialize($booking_forms_extended_new) );
                ?>
                <script type="text/javascript">
                    document.getElementById('ajax_message').innerHTML = '<?php echo __('Deleted' ,'booking'); ?>';
                    jQuery('#ajax_message').fadeOut(1000);
                    window.location.href='<?php echo 'admin.php?page='.WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking-option&tab=form&booking_form=standard';?>';
                </script> <?php
            } else {
                ?>
                <script type="text/javascript">
                    document.getElementById('ajax_message').innerHTML = '<?php echo __('There are no extended booking forms' ,'booking'); ?>';
                    jQuery('#ajax_message').fadeOut(1000);
                    window.location.href='<?php echo 'admin.php?page='.WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking-option&tab=form&booking_form=standard';?>';
                </script> <?php
            }
        }
    }

    // UPDATE at settings page of form fields customisation page -- booking form(s)
    function update_booking_form_at_settings(){ 
        $booking_form = $booking_form_show = $new_name ='';
        if ( isset($_GET['booking_form'])  )         $new_name = $_GET['booking_form'];
        if ( (isset($_POST['booking_form_new_name'])) && (! empty($_POST['booking_form_new_name'])) )  
            $new_name = substr( $_POST['booking_form_new_name'] , 0 ,30 );

        if (isset($_POST['booking_form'])) {
                 $booking_form =  ($_POST['booking_form']);
                 $booking_form = str_replace('\"','"',$booking_form);
                 $booking_form = str_replace("\'","'",$booking_form);
        }
        if ( isset( $_POST['booking_form_show'] ) ) {
             $booking_form_show =  ($_POST['booking_form_show']);
             $booking_form_show = str_replace('\"','"',$booking_form_show);
             $booking_form_show = str_replace("\'","'",$booking_form_show);
        }

        if ( ( ! empty($new_name) ) && ( ! empty($booking_form) ) && ( ! empty($booking_form_show) ) ) {

            $booking_forms_extended = get_bk_option( 'booking_forms_extended');
            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);
                $i = 0;
                // Check already exist names for rewrite it
                foreach ($booking_forms_extended as $value) {
                    if ($value['name'] == $new_name){
                        $booking_forms_extended[$i]['form']     = $booking_form;
                        $booking_forms_extended[$i]['content']  = $booking_form_show;
                        $i = 'modified';
                        break;
                    } $i++;
                }
                if ($i !== 'modified') {  // add new booking form
                    $booking_forms_extended[count($booking_forms_extended)] = array('name'=>$new_name, 'form'=>$booking_form, 'content'=> $booking_form_show );
                }

            } else {
                $booking_forms_extended = array( array('name'=>$new_name, 'form'=>$booking_form, 'content'=>$booking_form_show ) );
            }
//debuge($booking_forms_extended);
            update_bk_option( 'booking_forms_extended' , serialize($booking_forms_extended) );
        }
    }

    ///////////////////////////////////////////////////////////
    // Get Content of CUSTOM Form  and CUSTOM Form Content data
    //
    //
    // Get Booking form Fields content
    function wpdev_get_booking_form($booking_form_def_value, $my_booking_form_name){

            $booking_forms_extended = get_bk_option( 'booking_forms_extended');

            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);
                // Check already exist names for rewrite it
                if (is_array($booking_forms_extended))
                    foreach ($booking_forms_extended as $value) {
                      if (isset($value['name']))
                        $value['name'] = str_replace('  ', ' ', $value['name']);
                        $my_booking_form_name = str_replace('  ', ' ', $my_booking_form_name);
                        if ($value['name'] == $my_booking_form_name){
                            return $value['form'];
                        }
                    }
            }
        return $booking_form_def_value;
    }

    // Get Booking form CONTENT
    function wpdev_get_booking_form_content($booking_form_def_value, $my_booking_form_name, $booking_forms_extended = false){

            if ($booking_forms_extended === false)
                $booking_forms_extended = get_bk_option( 'booking_forms_extended');

            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);
                // Check already exist names for rewrite it
                if (is_array($booking_forms_extended))
                    foreach ($booking_forms_extended as $value) {
                      if (isset($value['name']))
                        $value['name'] = str_replace('  ', ' ', $value['name']);
                        $my_booking_form_name = str_replace('  ', ' ', $my_booking_form_name);
                        if ($value['name'] == $my_booking_form_name){
                            if ( (isset($value['content']))  ) // && (! empty($value['content'])) )
                                return $value['content'];
                        }
                    }
            }
        return $booking_form_def_value;
    }


    /////////////////////////////////////////////
    // SELECTIONS in interface Booking Form Names
    //
    //
    // in the popup configuration dialog - INSERTING SHORTCODE Booking.
    function wpdev_show_bk_form_selection($booking_form_type = 'booking_form_type'){

        $booking_forms_extended = get_bk_option( 'booking_forms_extended');

        if ($booking_forms_extended !== false) {
            if ( is_serialized( $booking_forms_extended ) ) 
                 $booking_forms_extended = unserialize($booking_forms_extended);


            ?>
            <div class="field">
                <fieldset>
                    <label for="<?php echo $booking_form_type; ?>"><?php _e('Booking form type:' ,'booking'); ?></label>
                    <select id="<?php echo $booking_form_type; ?>" name="<?php echo $booking_form_type; ?>" >
                            <option value="standard"><?php _e('Standard' ,'booking'); ?></option>
                        <?php foreach ($booking_forms_extended as $value) { ?>
                            <option value="<?php echo $value['name']; ?>"><?php echo $value['name']; ?></option>
                        <?php } ?>
                    </select>
                    <span class="description"><?php _e('Select type of booking form' ,'booking'); ?></span>
                </fieldset>
            </div>
        <?php
        }
    }

    // in the ADD NEW Booking page
    function wpdev_show_booking_form_selection(){

        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
        if ( (! $is_can) && (! WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) return ;

        $booking_forms_extended = get_bk_option( 'booking_forms_extended');

        $link_base = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-reservation' ;

        if (isset($_GET['booking_hash'])) $link_base .= '&booking_hash=' . $_GET['booking_hash']  ;
        if (isset($_GET['parent_res'])) $link_base .= '&parent_res=' . $_GET['parent_res']  ;



        if (isset($_GET['booking_type']))
            if ($_GET['booking_type'] > 0 )
            $link_base .= '&booking_type=' . $_GET['booking_type']  ;

        $link_base .= '&booking_form=' ;

        if ($booking_forms_extended !== false) {
            if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);
            
        ?>
            <div style="float:left;margin:0px 10px;">
                <fieldset>
                <label for="booking_form_type"><?php _e('Booking Form' ,'booking'); ?>:</label>
                <select id="booking_form_type" name="booking_form_type" style="width:200px;"
                    onchange="javascript: location.href='<?php echo $link_base; ?>' + this.value;"

                        >
                    <option value="standard"><?php _e('Standard' ,'booking'); ?></option>
                    <?php foreach ($booking_forms_extended as $value) { ?>
                    <option value="<?php echo $value['name']; ?>"   <?php if ((isset($_GET['booking_form'])) && ($_GET['booking_form'] == $value['name']) ) { echo ' selected="SELECTED" '; } ?>   ><?php echo $value['name']; ?></option>
                    <?php } ?>
                </select>
                </fieldset>
            </div>
        <?php
        }
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////


    // Check if this day inside of filter  , return TRUE  or FALSE   or   array( 'hour', 'start_time', 'end_time']) if HOUR filter this FILTER ID
    function is_day_inside_of_filter($day , $month, $year, $filter_id){

        global $wpdb;

        if ( IS_USE_WPDEV_BK_CACHE ) {
            global $wpdevbk_cache_season_filters;

            if (! isset($wpdevbk_cache_season_filters)) $wpdevbk_cache_season_filters = array();
            if (! isset($wpdevbk_cache_season_filters[$filter_id])) {
                $result = $wpdb->get_results( "SELECT booking_filter_id as id, filter FROM {$wpdb->prefix}booking_seasons" );

                foreach ($result as $value) {
                    $wpdevbk_cache_season_filters[$value->id] = array($value);
                }
                $result = $wpdevbk_cache_season_filters[$filter_id];
            } else {
                $result = $wpdevbk_cache_season_filters[$filter_id];
            }
        } else
            $result = $wpdb->get_results( $wpdb->prepare( "SELECT filter FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d " , $filter_id ) );

        if (count($result)>0){

            foreach($result as $filter) {

                if ( is_serialized( $filter->filter ) ) $filter = unserialize($filter->filter);
                else $filter = $filter->filter ;
            }

            return wpdev_bk_is_day_inside_of_filter($day , $month, $year, $filter);                
        }
        return false;    // there are no filter so not inside of filter
    }





// C O S T   H I N T    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Check if total cost field exist, and if its exist get cost from it
    function check_if_cost_exist_in_field( $blank , $formmy, $booking_type ){

        $form_elements = get_form_content ($formmy, $booking_type);
        if (isset($form_elements['_all_']))
           if (isset($form_elements['_all_']['total_bk_cost' . $booking_type ])) {
                $fin_cost = $form_elements['_all_']['total_bk_cost' . $booking_type ];
                return $fin_cost;
           }

        return false;
    }


    // Set fields inside of form for editing total cost
    function wpdev_reapply_bk_form_for_cost_input($return_form, $bk_type){

        $my_form = '';

        if ( strpos($_SERVER['REQUEST_URI'],'wpdev-booking.phpwpdev-booking-reservation')!==false ) {
            $my_form =  '<div id="show_edit_cost_fields"><p><div class="legendspan">'.__('Standard booking resource cost' ,'booking') . ':</div> '. '<input type="text" disabled="disabled" value="'.$this->get_standard_cost_for_bk_resource($bk_type).'" id="standard_bk_cost'.$bk_type.'"  name="standard_bk_cost'.$bk_type.'" /></p>';
            $my_form .= '<p><div class="legendspan">'.__('Total booking resource cost' ,'booking') . ':</div>  '. '<input type="text" value="0" id="total_bk_cost'.$bk_type.'"  name="total_bk_cost'.$bk_type.'" /></p>';            

            if ( strpos($_SERVER['REQUEST_URI'],'booking_hash') !== false ) {
                $my_form .= '<script type="text/javascript">jQuery(document).ready( function(){ ';                
                if (isset($_GET['booking_hash'])) {
                    $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
                    if ($my_booking_id_type !== false) {
                        $booking_id = $my_booking_id_type[0];  
                        $cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);                        
                        $my_form .= ' jQuery("#total_bk_cost'.$bk_type.'").val("'.$cost.'") ';
                    }
                }
                $my_form .= '});</script></div>';
            } else {
                $my_form .= '<script type="text/javascript">jQuery(document).ready( function(){ if(typeof( showCostHintInsideBkForm ) == "function") { show_cost_init=setTimeout(function(){ showCostHintInsideBkForm('.$bk_type.'); },2500);  } });</script></div>';
            }
        }
        $return_form = str_replace('[cost_corrections]', $my_form, $return_form);

        return $return_form ;
    }


    // Check the form according show Hint and modificate it
    function wpdev_reapply_bk_form($return_form, $bk_type, $my_booking_form = ''){

        $cost_currency = get_bk_option( 'booking_paypal_curency' );
        if ($cost_currency == 'USD' ) $cost_currency = '$';
        elseif ($cost_currency == 'EUR' ) $cost_currency = '&euro;';
        elseif ($cost_currency == 'GBP' ) $cost_currency = '&#163;';
        elseif ($cost_currency == 'JPY' ) $cost_currency = '&#165;';

        $_POST['booking_form_type'] = $my_booking_form; // Its required for the correct calculation  of the Advanced Cost.
        $show_cost_hint = apply_bk_filter('advanced_cost_apply', 0 , '', $bk_type, array() , true );    // Get info  to show advanced cost.
        $return_form = apply_bk_filter('reupdate_static_cost_hints_in_form', $return_form, $bk_type );    //FixIn: 5.4.5.5
//debuge($show_cost_hint);                
        foreach ( $show_cost_hint as $key_name => $value ) {

            if (  strpos( $return_form, '['.$key_name.']' ) !== false )
                $return_form = str_replace('['.$key_name.']', 
                                        '<span id="bookinghint_' . $key_name . $bk_type.'">'.$cost_currency.' 0.00</span>'
                                        .'<input style="display:none;" type="text" value="0.00" id="'.$key_name.''.$bk_type.'"  name="'.$key_name.''.$bk_type.'" />'
                                , $return_form);            
        }

        // Cost Hints
        $return_form = str_replace('[cost_hint]', '<span id="booking_hint'.$bk_type.'">'.$cost_currency.' 0.00</span><input style="display:none;" type="text" value="0.00" id="cost_hint'.$bk_type.'"  name="cost_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[original_cost_hint]', '<span id="original_booking_hint'.$bk_type.'">'.$cost_currency.' 0.00</span><input style="display:none;" type="text" value="0.00" id="original_cost_hint'.$bk_type.'"  name="original_cost_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[additional_cost_hint]', '<span id="additional_booking_hint'.$bk_type.'">'.$cost_currency.' 0.00</span><input style="display:none;" type="text" value="0.00" id="additional_cost_hint'.$bk_type.'"  name="additional_cost_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[deposit_hint]', '<span id="deposit_booking_hint'.$bk_type.'">'.$cost_currency.' 0.00</span><input style="display:none;" type="text" value="0.00" id="deposit_hint'.$bk_type.'"  name="deposit_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[balance_hint]', '<span id="balance_booking_hint'.$bk_type.'">'.$cost_currency.' 0.00</span><input style="display:none;" type="text" value="0.00" id="balance_hint'.$bk_type.'"  name="balance_hint'.$bk_type.'" />', $return_form);

        if (function_exists ('get_booking_title')) {
            $bk_title = get_booking_title( $bk_type );
            $bk_title = apply_bk_filter('wpdev_check_for_active_language', $bk_title );
        } else 
            $bk_title = '';        
        $return_form = str_replace('[resource_title_hint]',             '<span id="resource_title_hint_tip'.$bk_type.'"> '.$bk_title.'</span><input style="display:none;" class="wpdevbk-date-time-hint-input" type="text" value="'.$bk_title.'" id="resource_title_hint'.$bk_type.'"  name="resource_title_hint'.$bk_type.'" />', $return_form);
        
        // Dates and Times Hints
        $return_form = str_replace('[check_in_date_hint]',              '<span id="check_in_date_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input" type="text" value="..." id="check_in_date_hint'.$bk_type.'"  name="check_in_date_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[check_out_date_hint]',             '<span id="check_out_date_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="check_out_date_hint'.$bk_type.'"  name="check_out_date_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[start_time_hint]',                 '<span id="start_time_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="start_time_hint'.$bk_type.'"  name="start_time_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[end_time_hint]',                   '<span id="end_time_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="end_time_hint'.$bk_type.'"  name="end_time_hint'.$bk_type.'" />', $return_form);            
        $return_form = str_replace('[selected_dates_hint]',             '<span id="selected_dates_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="selected_dates_hint'.$bk_type.'"  name="selected_dates_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[selected_timedates_hint]',         '<span id="selected_timedates_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="selected_timedates_hint'.$bk_type.'"  name="selected_timedates_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[selected_short_dates_hint]',       '<span id="selected_short_dates_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="selected_short_dates_hint'.$bk_type.'"  name="selected_short_dates_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[selected_short_timedates_hint]',   '<span id="selected_short_timedates_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="selected_short_timedates_hint'.$bk_type.'"  name="selected_short_timedates_hint'.$bk_type.'" />', $return_form);            
        $return_form = str_replace('[days_number_hint]',                '<span id="days_number_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="days_number_hint'.$bk_type.'"  name="days_number_hint'.$bk_type.'" />', $return_form);
        $return_form = str_replace('[nights_number_hint]',              '<span id="nights_number_hint_tip'.$bk_type.'">...</span><input style="display:none;" class="wpdevbk-date-time-hint-input"  type="text" value="..." id="nights_number_hint'.$bk_type.'"  name="nights_number_hint'.$bk_type.'" />', $return_form);

        $return_form = $this->wpdev_reapply_bk_form_for_cost_input($return_form, $bk_type);
        if (function_exists('wpdev_bk_form_conditions_parsing')) 
            $return_form = wpdev_bk_form_conditions_parsing( $return_form, $bk_type);
        return $return_form ;
    }

    // Ajax function call, for showing cost
    function wpdev_ajax_show_cost(){

        make_bk_action('check_multiuser_params_for_client_side', $_POST[ "bk_type"] );

        // TODO: Set for multiuser - user ID (ajax request do not transfear it
        //$this->client_side_active_params_of_user
//debuge($_POST);
        $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
        $sdform = $_POST['form'];
        $dates = $_POST[ "all_dates" ];

        if (strpos($dates,' - ')!== FALSE) {
            $dates =explode(' - ', $dates );
            $dates = createDateRangeArray($dates[0],$dates[1]);
        }
        $my_dates = explode(", ",$dates);

        $start_end_time = get_times_from_bk_form($sdform, $my_dates, $_POST[ "bk_type"] );
        $start_time = $start_end_time[0];
        $end_time = $start_end_time[1];
        $my_dates = $start_end_time[2];

        // Get cost of main calendar with all rates discounts and  so on...
        $summ = apply_filters('wpdev_get_booking_cost', $_POST['bk_type'], $dates, array($start_time, $end_time ), $_POST['form'] );
        $summ = floatval( $summ );
        $summ = round($summ,2);

        
       
        
        $summ_original = apply_bk_filter('wpdev_get_bk_booking_cost', $_POST['bk_type'], $dates, array($start_time, $end_time ), $_POST['form'], true , true );
        $summ_original = floatval( $summ_original );
        $summ_original = round($summ_original,2);

        
//TODO: 10/03/2015 - Finish here         
$show_cost_hint = apply_bk_filter('advanced_cost_apply', $summ_original , $_POST['form'], $_POST['bk_type'], explode(',', $dates) , true );    // Get info  to show advanced cost.
//debuge($show_cost_hint);


        // Get description according coupons discount for main calendar if its exist
        $coupon_info_4_main_calendar = apply_bk_filter('wpdev_get_additional_description_about_coupons', '', $_POST['bk_type'], $dates, array($start_time, $end_time ), $_POST['form']   );


        // Check additional cost based on several calendars inside of this form //////////////////////////////////////////////////////////////
        $additional_calendars_cost = $this->check_cost_for_additional_calendars($summ, $_POST['form'], $_POST['bk_type'],  array($start_time, $end_time)   );
        $summ_total       = $additional_calendars_cost[0];
        $summ_additional  = $additional_calendars_cost[1];
        $dates_additional = $additional_calendars_cost[2];

        $additional_description = '';           
        if ( count($summ_additional)>0 ) {  // we have additional calendars inside of this form

            // Main calendar description and discount info //
            $additional_description .= '<br />' . get_booking_title($_POST['bk_type']) . ': ' . $cost_currency   . $summ  ;
            if ($coupon_info_4_main_calendar != '')
                $additional_description .=   $coupon_info_4_main_calendar ;
            $coupon_info_4_main_calendar = '';
            $additional_description .= '<br />' ;



            // Additional calendars - info and discounts //
            foreach ($summ_additional as $key=>$ss) {

                $additional_description .= get_booking_title($key) . ': ' . $cost_currency  . $ss ;

                // Discounts info ///////////////////////////////////////////////////////////////////////////////////////////////////////
                $form_content_for_specific_calendar = $this->get_bk_form_with_correct_id($_POST['form'], $key ,  $_POST['bk_type'] );
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


        $summ_deposit = apply_bk_filter('fixed_deposit_amount_apply', $summ_total , $_POST['form'], $_POST['bk_type'], $_POST[ "all_dates" ] ); // Apply fixed deposit
        if ($summ_deposit != $summ_total )  $is_deposit = true;
        else                                $is_deposit = false;
        $summ_balance = $summ_total - $summ_deposit;
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////        
        $summ_additional_hint = $summ_total - $summ_original;

        $summ_original         = wpdev_bk_cost_number_format( $summ_original );
        $summ_additional_hint  = wpdev_bk_cost_number_format( $summ_additional_hint );
        $summ_total_orig=$summ_total;
        $summ_total            = wpdev_bk_cost_number_format( $summ_total );
        $summ_deposit          = wpdev_bk_cost_number_format( $summ_deposit );
        $summ_balance          = wpdev_bk_cost_number_format( $summ_balance ); 

        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // Dates and Times Hints: ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $check_in_date_hint             =  
        $check_out_date_hint            =  
        $start_time_hint                =  
        $end_time_hint                  =  
        $selected_dates_hint            =  
        $selected_timedates_hint        =  
        $selected_short_dates_hint      =  
        $selected_short_timedates_hint  =  
        $days_number_hint               =  
        $nights_number_hint             =  '...';

        if (! empty($my_dates)) {
            if ( (! isset($start_time[0])) || ($start_time[0] == '') ) $start_time[0] = '00';
            if ( (! isset($start_time[1])) || ($start_time[1] == '') ) $start_time[1] = '00';                
            if ( (! isset($end_time[0])) || ($end_time[0] == '') ) $end_time[0] = '00';
            if ( (! isset($end_time[1])) || ($end_time[1] == '') ) $end_time[1] = '00';

            //Get sorted dates array: [0] => 2013-10-07, [1] => 2013-10-08, [2] => 2013-10-09, .....
            $only_days = array();
            foreach ($my_dates as $new_day) {
                if (! empty($new_day)) {
                    $new_day=trim($new_day);
                    if (strpos($new_day, '.')!==false) $new_day = explode('.',$new_day);
                    else                               $new_day = explode('-',$new_day);
                    $only_days[] = $new_day[2] .'-' . $new_day[1] .'-' . $new_day[0];
                }
            }

            if (! empty($only_days)) {

                // Sort dates 
                sort($only_days);

                $selected_dates_hint            =  array();
                $selected_timedates_hint        =  array();

                $days_and_times = array();
                $only_full_days = array();
                foreach ($only_days as $day_num => $day ) {

                    if ($day_num==0) { //First  date
                        $days_and_times[] = $day . ' '.$start_time[0].':'.$start_time[1].':'.$start_time[2];
                    } else if ( $day_num == (count($only_days)-1) ) { //Last date
                        $days_and_times[] = $day . ' '.$end_time[0].':'.$end_time[1].':'.$end_time[2];
                    } else {
                        $days_and_times[] = $day . ' 00:00:00';
                    } 
                    $only_full_days[] = $day . ' 00:00:00';

                    // Wide Dates
                    $selected_dates_hint[]      = change_date_format( $only_full_days[ (count($only_full_days)-1) ] ) ;
                    $selected_timedates_hint[]  = change_date_format( $days_and_times[ (count($days_and_times)-1) ] ) ;
                }

                // Remove duplicated same dates, if we are selected only 1 date

                $selected_dates_hint     = array_values(array_unique($selected_dates_hint));
                $selected_timedates_hint = array_values(array_unique($selected_timedates_hint));

                // Number of days & nights
                $days_number_hint               = count( $selected_dates_hint );
                $nights_number_hint             = ($days_number_hint>1) ? ($days_number_hint-1) : $days_number_hint;                


                // Wide Dates
                $selected_dates_hint            = implode(', ', $selected_dates_hint );
                $selected_timedates_hint        = implode(', ', $selected_timedates_hint );

                //Short Dates
                $selected_short_timedates_hint  = get_dates_short_format(  implode(',', $days_and_times) );
                $only_full_days = array_values(array_unique($only_full_days));
                $selected_short_dates_hint      = get_dates_short_format(  implode(',', $only_full_days) );

                // Check  In / Out Dates            
                $check_in_date_hint             = change_date_format( $only_full_days[0]  );
                $check_out_date_hint            = change_date_format( $only_full_days[ (count($only_full_days)-1) ] );


                // Times:
                $time_format = get_bk_option( 'booking_time_format');
                if ( $time_format === false  ) $time_format = '';

                $start_time_hint = date_i18n($time_format, mktime($start_time[0], $start_time[1], $start_time[2] ));
                $end_time_hint   = date_i18n($time_format, mktime($end_time[0], $end_time[1], $end_time[2] ));
            }
        }


        // JavaScript setup //
        ?> <script type="text/javascript">
              if (document.getElementById('booking_hint<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('booking_hint<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php
                   echo  $cost_currency  . $summ_total .
                         $coupon_info_4_main_calendar .
                         $additional_description;
                   ?>';
                document.getElementById('cost_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $summ_total ; ?>';                      
              }<?php
                   foreach ( $show_cost_hint as $cost_hint_key => $cost_hint_value ) {
                       ?>
                        if (document.getElementById('bookinghint_<?php echo $cost_hint_key; ?><?php echo $_POST['bk_type']; ?>' ) !== null) {
                          document.getElementById('bookinghint_<?php echo $cost_hint_key; ?><?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php
                             echo  $cost_currency  . wpdev_bk_cost_number_format( $cost_hint_value ); ?>';
                          document.getElementById('<?php echo $cost_hint_key; ?><?php echo $_POST['bk_type']; ?>' ).value = '<?php echo wpdev_bk_cost_number_format( $cost_hint_value ); ?>';
                        }                           
                       <?php    
                   }
              ?>
              if (document.getElementById('additional_booking_hint<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('additional_booking_hint<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php
                   echo  $cost_currency  . $summ_additional_hint ; ?>';
              }
              if (document.getElementById('additional_cost_hint<?php echo $_POST['bk_type']; ?>' ) !== null) {  //FixIn:6.1
                document.getElementById('additional_cost_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $summ_additional_hint ; ?>';
              }

              if (document.getElementById('original_booking_hint<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('original_booking_hint<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php
                   echo  $cost_currency  . $summ_original ; ?>';
                document.getElementById('original_cost_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $summ_original ; ?>';           
              }

              if (document.getElementById('deposit_booking_hint<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('deposit_booking_hint<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php
                   echo  $cost_currency  . $summ_deposit ; ?>';
                document.getElementById('deposit_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $summ_deposit ; ?>';           
              }

              if (document.getElementById('balance_booking_hint<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('balance_booking_hint<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php
                   echo  $cost_currency  . $summ_balance ; ?>';
                document.getElementById('balance_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $summ_balance ; ?>';           
              }
              
              
              if (document.getElementById('total_bk_cost<?php echo $_POST['bk_type']; ?>') != null) {
                if ( jQuery('#total_bk_cost<?php echo $_POST['bk_type']; ?>').val() == 0 )  
                    document.getElementById('total_bk_cost<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo  $summ_total_orig; ?>';                    
              }

              // Dates and Times shortcodes:
              if (document.getElementById('check_in_date_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('check_in_date_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $check_in_date_hint ; ?>';
                document.getElementById('check_in_date_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $check_in_date_hint ; ?>';           
              }                  
              if (document.getElementById('check_out_date_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('check_out_date_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $check_out_date_hint ; ?>';
                document.getElementById('check_out_date_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $check_out_date_hint ; ?>';           
              }                  
              if (document.getElementById('start_time_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('start_time_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $start_time_hint ; ?>';
                document.getElementById('start_time_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $start_time_hint ; ?>';           
              }                  
              if (document.getElementById('end_time_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('end_time_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $end_time_hint ; ?>';
                document.getElementById('end_time_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $end_time_hint ; ?>';           
              }                  
              if (document.getElementById('selected_dates_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('selected_dates_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $selected_dates_hint ; ?>';
                document.getElementById('selected_dates_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $selected_dates_hint ; ?>';           
              }                  
              if (document.getElementById('selected_timedates_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('selected_timedates_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $selected_timedates_hint ; ?>';
                document.getElementById('selected_timedates_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $selected_timedates_hint ; ?>';           
              }                  
              if (document.getElementById('selected_short_dates_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('selected_short_dates_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $selected_short_dates_hint ; ?>';
                document.getElementById('selected_short_dates_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $selected_short_dates_hint ; ?>';           
              }                  
              if (document.getElementById('selected_short_timedates_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('selected_short_timedates_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $selected_short_timedates_hint ; ?>';
                document.getElementById('selected_short_timedates_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $selected_short_timedates_hint ; ?>';           
              }
              if (document.getElementById('days_number_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('days_number_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $days_number_hint; ?>';
                document.getElementById('days_number_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $days_number_hint ; ?>';           
              }                  
              if (document.getElementById('nights_number_hint_tip<?php echo $_POST['bk_type']; ?>' ) !== null) {
                document.getElementById('nights_number_hint_tip<?php echo $_POST['bk_type']; ?>' ).innerHTML = '<?php echo  $nights_number_hint; ?>';
                document.getElementById('nights_number_hint<?php echo $_POST['bk_type']; ?>' ).value = '<?php echo $nights_number_hint ; ?>';           
              }                  
//              jQuery('#booking_form_div<?php echo $_POST['bk_type']; ?> input[type="button"]').removeAttr('disabled');  // Activate the submit button
//              jQuery('#booking_form_div<?php echo $_POST['bk_type']; ?> input[type="button"]').css('color',  submit_bk_color );

           </script> <?php
    }


    // Show help hint of shortcode at the admin panel
    function show_additional_shortcode_help_for_form(){
        ?><span class="description"><?php printf(__('%s - show cost hint for full booking in real time, depending on number of days selected and form elements.' ,'booking'),'<code>[cost_hint]</code>');?></span>
          <span class="description example-code"><?php printf(__('Example: %sThe full cost of payment: %s' ,'booking'),'&lt;div  style="text-align:left;line-height:28px;"&gt;&lt;p&gt;', '[cost_hint]&lt;/p&gt;&lt;/div&gt;');?> </span><br/><?php
        ?><span class="description"><?php printf(__('%s - show hint of original booking cost without additional costs for full booking in real time, depends only from days selection.' ,'booking'),'<code>[original_cost_hint]</code>');?></span>
          <span class="description example-code"><?php printf(__('Example: %sThe original cost for payment: %s ' ,'booking'),'&lt;div  style="text-align:left;line-height:28px;"&gt;&lt;p&gt;', '[original_cost_hint]&lt;/p&gt;&lt;/div&gt;');?></span><br/><?php
        ?><span class="description"><?php printf(__('%s - show cost hint of additional booking cost, which depends from selection of form elements.' ,'booking'),'<code>[additional_cost_hint]</code>');?></span>
          <span class="description example-code"><?php printf(__('Example: %sThe additional cost for payment: %s ' ,'booking'),'&lt;div  style="text-align:left;line-height:28px;"&gt;&lt;p&gt;', '[additional_cost_hint]&lt;/p&gt;&lt;/div&gt;');?></span><br/><?php
        ?><span class="description"><?php printf(__('%s - enter direct cost at admin panel at page: ' ,'booking'),'<code>[cost_corrections]</code>'); echo '"'; _e("Add booking" ,'booking'); echo '". '; ?></span>
          <span class="description example-code"><?php printf(__('Example: %s' ,'booking'), '[cost_corrections]');?></span><br/><?php
          //TODO: descriptin  about these shortcodes: 
          /*
        [deposit_hint] - show deposit cost, 
        [balance_hint] - show balance cost, 
        [check_in_date_hint] - check in date, 
        [check_out_date_hint] - cehck out date, 
        [start_time_hint] - start time, 
        [end_time_hint] - end time, 
        [selected_dates_hint] - all dates, 
        [selected_timedates_hint] - all dates with times, 
        [selected_short_dates_hint] - dates in "short" format, 
        [selected_short_timedates_hint] - dates in "short" format with times,
        [days_number_hint] - number of selected days, 
        [nights_number_hint] - number of selected nights  (Business Medium/Large, MultiUser)
         */
    }

    /////////////////////////////////////////////////////////////////////////////////////


// R A T E S  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////



    // Define JavaScript variable for showing tooltip rates for 1 day
    function show_rates_at_calendar($blank, $type_id ) {  $start_script_code = '';

        // Save at the Advnaced settings these 2 parameters
        $is_show_cost_in_tooltips =    get_bk_option( 'booking_is_show_cost_in_tooltips' );
        $highlight_cost_word = get_bk_option( 'booking_highlight_cost_word'); ;
        $highlight_cost_word = apply_bk_filter('wpdev_check_for_active_language', $highlight_cost_word );

        $is_show_cost_in_date_cell =    get_bk_option( 'booking_is_show_cost_in_date_cell' );
        $booking_cost_in_date_cell_currency  = get_bk_option( 'booking_cost_in_date_cell_currency');  
        
        if ( ( $is_show_cost_in_tooltips !== 'On' ) && ( $is_show_cost_in_date_cell !== 'On' ) ) 
            return $start_script_code;

        if ( $is_show_cost_in_tooltips == 'On' )
            $start_script_code .= ' is_show_cost_in_tooltips = true; ';

        if ( $is_show_cost_in_date_cell == 'On' )
            $start_script_code .= ' is_show_cost_in_date_cell = true; ';

        
        $cost_currency = get_bk_option( 'booking_paypal_curency' );
        if ($cost_currency == 'USD' ) $cost_currency = '$';
        elseif ($cost_currency == 'EUR' ) $cost_currency = '&euro;';
        $start_script_code .= " cost_curency =  '". esc_js($highlight_cost_word .$cost_currency) . " '; ";

        $start_script_code .= " wpbc_curency_symbol =  '". esc_js($booking_cost_in_date_cell_currency) . " '; ";
        
        // Get cost of 1 time unit
        $cost = 0;
        $result = $this->get_booking_types($type_id); // Main info according booking type
        if ( count($result)>0 )  $cost = $result[0]->cost;

        // Get period of costs - multiplier
        $price_period        =  get_bk_option( 'booking_paypal_price_period' );

        if ($price_period == 'day') {
            $cost_multiplier = 1;
        } elseif ($price_period == 'night') {
            $cost_multiplier = 1;
        } elseif ($price_period == 'hour') {
            $cost_multiplier = 24;
        } else {
            $cost_multiplier = 1;
        }


        $prices_per_day = array();                                          // PHP Debug
        $prices_per_day[$type_id] = array();                                // PHP Debug

        $start_script_code .= "  prices_per_day[". $type_id ."] = [] ;  ";

        $max_monthes_in_calendar = wpdev_bk_get_max_days_in_calendar();            
        $my_day =  date('m.d.Y' );          // Start days from TODAY

        for ($i = 0; $i < $max_monthes_in_calendar ; $i++) {

            $my_day_arr = explode('.',$my_day);

            $day = ($my_day_arr[1]+0);
            $month= ($my_day_arr[0]+0);
            $year = ($my_day_arr[2]+0);

            $my_day_tag =   $month . '-' . $day . '-' . $year ;

            $fin_day_cost =  $this->get_1_day_cost_apply_rates($type_id, $cost, $day , $month, $year );
            $fin_day_cost = round($fin_day_cost,2);

            $prices_per_day[$type_id][$my_day_tag] = $fin_day_cost;         // PHP Debug
            if ($fin_day_cost != 0)
                $start_script_code .= "  prices_per_day[". $type_id ."]['".$my_day_tag."'] = '".$fin_day_cost."' ;  ";

            $my_day =  date('m.d.Y' , mktime(0, 0, 0, $month, ($day+1), $year ));
        }

        //debuge($prices_per_day); die;                                     // PHP Debug

        return $start_script_code;
    }

    // Apply season rates to D A Y S array with/without $time_array   -   send from P A Y P A L form
    // $days_array = array( 'dd.mm.yyyy', 'dd.mm.yyyy', ... )
    function apply_season_rates( $paypal_dayprice, $days_array, $booking_type, $times_array, $post_form ) {

// debuge($paypal_dayprice, $days_array, $booking_type, $times_array, $post_form, 'test ');

     if ($times_array[0] ==  array('00','00','00') ) $times_array[0] =  array('00','00','01');
     if ($times_array[1] ==  array('00','00','02') ) $times_array[1] =  array('24','00','02');

        $one_night = 0;
        $paypal_price_period        =  get_bk_option( 'booking_paypal_price_period' );
        $costs_depends_from_selection_new = array();
        if ($paypal_price_period == 'day') {

            $costs_depends_from_selection = $this->get_all_days_cost_depends_from_selected_days_count($booking_type, $days_array, $times_array );
            if ($costs_depends_from_selection !== false) {
                $costs_depends_from_selection[0]=0;                    
                for ($ii = 1; $ii < count($costs_depends_from_selection); $ii++) {
                    $costs_depends_from_selection_new[] = $costs_depends_from_selection[$ii];
                }
            }

        }elseif ($paypal_price_period == 'night') {

            if (count($days_array)>1) {
                if (  ( ($times_array[0] == array('00','00','01') )  && ($times_array[1] == array('00','00','00') ))  ||
                      ( ($times_array[0] == array('00','00','01') )  && ($times_array[1] == array('24','00','02') ))
                   ) { $one_night = 1; }
            }

            $costs_depends_from_selection = $this->get_all_days_cost_depends_from_selected_days_count($booking_type, $days_array, $times_array );
            if ($costs_depends_from_selection !== false) {
                $costs_depends_from_selection[0]=0;                    
                for ($ii = 1; $ii < count($costs_depends_from_selection); $ii++) {
                    $costs_depends_from_selection_new[] = $costs_depends_from_selection[$ii];
                    $one_night = 0;
                }
            }


        }elseif ($paypal_price_period == 'hour') {
            
            
        } else {
                //return array($paypal_dayprice); //fixed
        }

        $days_rates = array();

        for($i=0;$i<(count($days_array) - $one_night );$i++){ $d_day = $days_array[$i];

           if (! empty($d_day)) {
//            foreach ($days_array as $d_day) { $i++;
               $times_array_check = array(array('00','00','01'),array('24','00','02'));
               if ( $i==0 )                   { $times_array_check[0] =  $times_array[0]; }
               if ( $i == (count($days_array) -1- $one_night )) { $times_array_check[1] =  $times_array[1]; }

                //$times_array_check = array($times_array[0],$times_array[1]);  // Its will make cost calculation only between entered times, even on multiple days
               $d_day = explode('.',$d_day);
               $day =  ($d_day[0]+0); $month =  ($d_day[1]+0); $year = ($d_day[2]+0);
               $week =  date('w', mktime(0, 0, 0, $month, $day, $year) );
               $days_rates[] = $this->get_1_day_cost_apply_rates($booking_type, $paypal_dayprice, $day , $month, $year, $times_array_check , $post_form );
            }
        }
        //if (count($days_rates)>1) $days_rates[count($days_rates)-1] = 0;
        // If fixed deposit so take only for first day cost

        if ($paypal_price_period == 'fixed') { if (count($days_rates)>0) { $days_rates = array($days_rates[0]); } else {$days_rates = array();} }


        /**/

        if ( ( count($costs_depends_from_selection_new)>0)  &&
             (! ( (count($days_array) == 1 )  && (empty($days_array[0])) ) )
           ){
            $rates_with_procents = array();
            // check is some value of $costs_depends_from_selection_new consist % if its true so then apply this procents to days
            $is_rates_with_procents = false;
            for ($iii = 0; $iii < count($costs_depends_from_selection_new); $iii++) {
                if ( strpos($costs_depends_from_selection_new[$iii], 'add') !== false ) {
                    $my_vvalue = floatval(str_replace('add','',$costs_depends_from_selection_new[$iii] ) );
                    $rates_with_procents[]= $my_vvalue + $days_rates[$iii];
                } elseif ( strpos($costs_depends_from_selection_new[$iii], '%') !== false ) {
                    $is_rates_with_procents = true;
                    $proc = str_replace('%','',$costs_depends_from_selection_new[$iii] ) * 1;
                    if (isset($days_rates[$iii]))
                            $rates_with_procents[]= $proc*$days_rates[$iii]/100;
                } else {
                    $rates_with_procents[]= floatval($costs_depends_from_selection_new[$iii]);// $days_rates[$iii]; // just cost
                }
            }

            if ($is_rates_with_procents) return $rates_with_procents;               // Rates with procents from cost depends from number of days
            else                         return $costs_depends_from_selection_new;  // Cost depends from number of days
        } else                           return $days_rates;                        // Just pure rates

    }

            // Get count of MINUTES from time in format "17:20" or array(17, 20)
            function get_minutes_num_from_time($time_array){
                if (is_string($time_array)) {
                    $time_array = explode(':',$time_array);
                }
                if (is_array($time_array)) {
                    return  ($time_array[0]*60+ intval($time_array[1]));
                }
                return $time_array;
            }

            // Get COST based on hourly rate - $hour_cost and start and end time during 1 day
            /*  $times_array                        (its arrayin fomat
            //  (start_minutes, end minutes)                        or
            //  ("12:00", "17:30")                                  or
            //  (array("12","00","00"), array("22", "00", "00"))    /**/
            function get_cost_between_times($times_array, $hour_cost) {
                    $start_time = $times_array[0];      // Get Times
                    if (count($times_array)>1) $end_time   = $times_array[1];
                    else                       $end_time = array('24','00','00');
                    if (is_string($start_time)) { $start_time = explode(':', $start_time);$start_time[2] = '00'; }
                    if (is_string($end_time))   { $end_time   = explode(':', $end_time);  $end_time[2] = '00'; }

                    if ( (is_int($end_time)) && (is_int($start_time)) ) {   // 1000000 correction need to make.

                        if ($end_time > 1000000) { $ostatok = $end_time % 1000000;
                            if ($ostatok == 0) $end_time = $end_time  / 1000000;
                            else               $end_time = ( $end_time + ( 1000000 - $ostatok ) )  / 1000000;
                        }
                        if ($start_time > 1000000) { $ostatok = $start_time  % 1000000;
                            if ($ostatok == 0) $start_time = $start_time  / 1000000;
                            else               $start_time = ( $start_time + ( 1000000 - $ostatok ) )  / 1000000;
                        }
                        return round(  ( ($end_time - $start_time) * ($hour_cost / 60 ) ) , 2 );
                    }

                    if (empty($start_time[0]) ) $start_time[0] = '00';
                    if (empty($end_time[0]) ) $end_time[0] = '00';

                    if (! isset($start_time[1])) $start_time[1] = '00';
                    if (! isset($end_time[1])) $end_time[1] = '00';



                    if ( ($end_time[0] == '00') && ($end_time[1] == '00') ) $end_time[0] = '24';


                    $m_dif =  ($end_time[0] * 60 + intval($end_time[1]) ) - ($start_time[0] * 60 + intval($start_time[1]) ) ;
                    $h_dif = intval($m_dif / 60) ;
                    $m_dif = ($m_dif - ($h_dif*60) ) / 60 ;

                    $summ = round( ( 1 * $h_dif * $hour_cost ) + ( 1 * $m_dif * $hour_cost ) , 2);

                    return $summ;
            }


    // Get 1 DAY cost OR cost from time to  time at  $times_array
    function get_1_day_cost_apply_rates( $type_id, $base_cost, $day , $month, $year, $times_array=false, $post_form = '' ) {


//debuge('Start', $type_id, $base_cost, $day , $month, $year, $times_array);

        $price_period =  get_bk_option( 'booking_paypal_price_period' );       // Get cost period and set multiplier for it.

        if ($price_period == 'day') {         $cost_multiplier = 1;
        } elseif ($price_period == 'night') { $cost_multiplier = 1;
        } elseif ($price_period == 'hour')  { $cost_multiplier = 24;        // Day have a 24 hours
        } else {                              $cost_multiplier = 1;   }     // fixed  // return $base_cost;

        $rate_meta_res = $this->get_bk_type_meta($type_id,'rates');         // Get all RATES for this bk resource

        if ( count($rate_meta_res)>0 ) {
            if ( is_serialized( $rate_meta_res[0]->value ) )  $rate_meta = unserialize($rate_meta_res[0]->value);
            else                                              $rate_meta = $rate_meta_res[0]->value;

            $rate              = $rate_meta['rate'];                        // Rate values                           (key -> ID)
            $seasonfilter      = $rate_meta['filter'];                      // If this filter assign to rate On/Off  (key -> ID)
//debuge($rate_meta);
            if (isset($rate_meta['rate_type']))   $rate_type = $rate_meta['rate_type'];       // is rate curency or %
            else                                  $rate_type = array();


            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get    B A S E    C O S T   with   Rates  and get    H O U R L Y   R a t e s
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            $base_cost_with_rates = $base_cost;
            $hourly_rates = array();
            ////////////////////////////////////////////////////////////////
            // Get here Cost of the day with rates - $base_cost_with_rates, If curency rate is assing for this day so then just assign it and stop
            // also get all hour filters rates
            foreach ($seasonfilter as $filter_id => $is_filter_ON) {  // Id_filter => On  || Id_filter => Off
                if ($is_filter_ON == 'On') {                                       // Only activated filters
                    $is_day_inside_of_filter = $this->is_day_inside_of_filter($day , $month, $year, $filter_id);  // Check  if this day inside of filter

                    if ( $is_day_inside_of_filter === true ) {              // If return true then Only D A Y filters here
                        if ( isset($rate_type[$filter_id]) ) {                    // It Can be situation that in previos version is not set rate_type so need to check its
                            if ($rate_type[$filter_id] == '%') $base_cost_with_rates =  ( ($base_cost_with_rates * $rate[$filter_id] / 100) ) ; // %
                            else {                                          // Here is the place where we need in future create the priority of rates according direct curency value
                                   $base_cost_with_rates =  $rate[$filter_id]; break;} //here rate_type  == 'curency so we return direct value and break all other rates
                        } else $base_cost_with_rates =  ( ($base_cost_with_rates * $rate[$filter_id] / 100) ) ; // Default - %
                    }

                    if( is_array($is_day_inside_of_filter) ) {              // Its HOURLY filter, save them for future work
                      if ($is_day_inside_of_filter[0] == 'hour') { $hourly_rates[$filter_id]=array( 'rate'=>$rate[$filter_id], 'rate_type'=>$rate_type[$filter_id], 'time'=>array($is_day_inside_of_filter[1],$is_day_inside_of_filter[2]) ); }
                    }

                } // close ON if
            }  // close foreach

// Customization for the  Joao ///////////////////////////////////////////////////////////////////////
if ($post_form !='') {

$booking_form_show = get_form_content ($post_form, $type_id);
$booking_form_show = $booking_form_show['_all_'] ;;
//debuge($booking_form_show); die;
}
if (strpos($base_cost_with_rates, '=')) {



$base_cost_with_rates = str_replace('[', '', $base_cost_with_rates);  // [visitors=1:140;2:150]
$base_cost_with_rates = str_replace(']', '', $base_cost_with_rates);
$base_cost_with_rates = explode('=',$base_cost_with_rates);

$my_field_name = $base_cost_with_rates[0];                  // visitors

$my_temp_field_values = explode(';',$base_cost_with_rates[1]);
$my_field_values = array();
foreach ($my_temp_field_values as $m_value) {
    $m_value = explode(':',$m_value);
    $my_field_values[$m_value[0]] = $m_value[1];
}
/*[1] => Array
        (
            [1] => 140
            [2] => 150
        )*/
if ($post_form !='') {
    foreach ($booking_form_show as $bk_ft_key=>$bk_ft_value) {
        if ( $bk_ft_key == ($my_field_name . $type_id) ) {
            if ( isset(  $my_field_values[ $bk_ft_value  ]  ) ) {
                $base_cost_with_rates = $my_field_values[ $bk_ft_value  ] ;
                break;
            }
        }
    }
} else {
    $base_cost_with_rates = array_shift(array_values($my_field_values));
}
if (is_array($base_cost_with_rates)) {
     $base_cost_with_rates = array_shift(array_values($my_field_values));
}
}

//debuge($base_cost_with_rates);
// Customization for the  Joao ///////////////////////////////////////////////////////////////////////


//debuge($my_field_name, $my_field_values)                ;
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
//debuge(array( '$base_cost'=>$base_cost, '$base_cost_with_rates'=>$base_cost_with_rates,'$hourly_rates'=>$hourly_rates, $rate_type[$filter_id], $price_period));
//die;
            if ( ( count($hourly_rates) == 0 ) && ($price_period == 'fixed') ) {
                return $base_cost_with_rates;
            }

            // H O U R s ///////////////////////////////////////////////////
            $general_hours_arr = array();

            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get   S T A R T  and   E N D   T i m e   for this day (or 0-24 or from function params $starttime)
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            if ($times_array === false) {                                   // Time is not pass to the function
                $global_start_time = array('00','00','00');
                $global_finis_time = array('24','00','00');
            } else {                                                        // Time is set and we need calculate cost between it
                $global_start_time = $times_array[0];
                if (count($times_array)>1) $global_finis_time   = $times_array[1];
                else                       $global_finis_time = array('24','00','00');
                if (is_string($global_start_time))   { $global_start_time = explode(':', $global_start_time);$global_start_time[2] = '00'; }
                if (is_string($global_finis_time))   { $global_finis_time   = explode(':', $global_finis_time);  $global_finis_time[2] = '00'; }
                if ($global_finis_time == array('00','00','00')) $global_finis_time = array('24','00','00');
             }
             $general_hours_arr[ $this->get_minutes_num_from_time($global_start_time)*1000000 ] = array('start' , $base_cost_with_rates, '' );  // start glob work times array
             $general_hours_arr[ $this->get_minutes_num_from_time($global_finis_time)*1000000 ] = array('end'   , $base_cost_with_rates, '' );  // end glob work times array
             /////////////////////////////////////////////////////////////////////////////////////////////////////////


            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            // Get   all   H O U R L Y    R A T E S    in    S o r t e d    by  Minutes*100   array
            /////////////////////////////////////////////////////////////////////////////////////////////////////////
            foreach ($hourly_rates as $hour_filter_id => $hour_rate) {
               if (! isset($hour_rate['rate_type']) ) $hour_rate['rate_type'] ='%';

               $r__start = 1000000 * $this->get_minutes_num_from_time($hour_rate['time'][0]);
               $r__fin   = 1000000 * $this->get_minutes_num_from_time($hour_rate['time'][1]);
               while( isset($general_hours_arr[$r__start]) ) {$r__start--;}
               while( isset($general_hours_arr[$r__fin]) )   {$r__fin--;}

               $general_hours_arr[$r__start] = array('rate_start' , $hour_rate['rate'] , $hour_rate['rate_type'] );
               $general_hours_arr[$r__fin]   = array('rate_end'   , $hour_rate['rate'] , $hour_rate['rate_type'] );
            }
            ksort( $general_hours_arr );                                    // SORT time(rate) arrays with start/end time
            /////////////////////////////////////////////////////////////////////////////////////////////////////////

//debuge(array('$general_hours_arr'=>$general_hours_arr));

            if (    ($price_period == 'hour') ||                            // Get hour rates, already based on cost with applying rates for days not hours
                    ( ($price_period == 'fixed') && ( count($hourly_rates)>0 ) )
               )                                                               $base_hour_cost = $base_cost_with_rates ;
            else                                                               $base_hour_cost = $base_cost_with_rates / 24 ;

//debuge(array('$base_hour_cost'=>$base_hour_cost));

            $is_continue = false;                                           // Calculate cost for our times in array segments
            $general_summ_array = array();
            $cur_rate = $base_hour_cost;
            $cur_type = 'curency';
            foreach ($general_hours_arr as $minute_time => $rate_value) {

                if ($is_continue) {                                         // Calculation
                    if ($cur_type == 'curency') {
                        if ($price_period == 'fixed')  $general_summ_array[] = $cur_rate;
                        else                           $general_summ_array[] = $this->get_cost_between_times( array($previos_time[0] ,$minute_time), $cur_rate);
                    } else {
                        $procent_base =  $this->get_cost_between_times( array($previos_time[0] ,$minute_time), $base_hour_cost);
                        $general_summ_array[] =  ( ($procent_base * $cur_rate / 100) ) ; // %
                    }
                }

                if ( $rate_value[0] == 'start' ) { $is_continue = true; }   // start calculate from this time
                if ( $rate_value[0] == 'end'   ) { break; }                 // Finish calculation

                $previos_time = array($minute_time, $rate_value);           // Save previos time and rate

                if ( $rate_value[0] == 'rate_start' ) {                              // RATE start so get type and value of rate
                    $cur_type = $rate_value[2];
                    if ( ($price_period == 'hour') || ($price_period == 'fixed') )
                          $cur_rate = $rate_value[1];
                    else  {
                        if ($cur_type == 'curency') $cur_rate = $rate_value[1] / 24;
                        else $cur_rate = $rate_value[1];
                    }

                }
                if ( $rate_value[0] == 'rate_end'   ) {                              // Rate end so set standard  type and rate
                    $cur_rate = $base_hour_cost;
                    $cur_type = 'curency';
                }
            } // close foreach time cost array

// debuge( array('$general_summ_array' =>  $general_summ_array )  );//die;

            if ( count($general_hours_arr) > 0 ) {                          // summ all costs into one variable - its 1 day cost ( or cost between times), with already aplly day rates filters
                   if ($price_period == 'fixed')  $return_cost = $general_summ_array[0];
                   else {
                        $return_cost = 0;
                        foreach ($general_summ_array as $vv) { $return_cost += $vv;  }
                   }
            } else                      $return_cost = $base_cost_with_rates;

            ////////////////////////////////////////////////////////////////
//debuge('$return_cost, $price_period, $hourly_rates', $return_cost, $price_period, $hourly_rates);
            return $return_cost;   // Evrything is calculated based on hours
            /*
            if( ($times_array !== false) && (count($hourly_rates)==0) ) {   //hourly rates do not exist BUT we set time from one time to end time
                if ($price_period == 'hour')        $hour_cost = $return_cost ;
                else {
                    if ($price_period == 'fixed')   return $return_cost;
                    elseif ($price_period == 'night')   return $return_cost; // alredy calculated, because time is exist //FIXED now
                    elseif ($price_period == 'day')   return $return_cost; // alredy calculated, because time is exist //FIXED now
                    else                            $hour_cost = $base_cost / 24 ;
                }
                    return $this->get_cost_between_times($times_array, $hour_cost);
            } else  return  $return_cost;    // Return day price after assigning of rates
            /**/

        } // Finish R A T E S  work


        // There    N o    R A T E S  at all
        if ($times_array === false)                 return  $cost_multiplier * $base_cost;      // No times, cost for 1 day
        else { // Also need to check according times hour
            if ($price_period == 'hour')            $hour_cost = $base_cost ;
            else {
                    if ($price_period == 'fixed')   return $base_cost;
                    else                            $hour_cost = $base_cost / 24 ;
            }
            return $this->get_cost_between_times($times_array, $hour_cost);                     // Cost for some time interval
        }

    }

//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    /** Reupdate static cost hints in booking form. Showing standard additional  costs,  if selected specific option in selectbox or checkbox.
     * 
     * @param string $form - booking form
     * @param int $bktype - Id of booking resource
     * @return string - content of booking form.
     */
    function reupdate_static_cost_hints_in_form( $form , $bktype ) {     //FixIn: 5.4.5.5
            
        $booking_form_name='';
        if (isset($_POST['booking_form_type']) ){
            if (! empty($_POST['booking_form_type'])) {
                $booking_form_name = $_POST['booking_form_type'];
                $booking_form_name = str_replace("\'",'',$booking_form_name);
                if ($booking_form_name == 'standard') $booking_form_name = '';
            }
        }

        if ($booking_form_name === '') 
            $field__values = get_bk_option( 'booking_advanced_costs_values' ); 
        else 
            $field__values = get_bk_option( 'booking_advanced_costs_values_for' . $booking_form_name );             
        
        $field__values_unserilize = array();
        if ( $field__values !== false ) {                                       
            if ( is_serialized( $field__values ) )   
                $field__values_unserilize = unserialize($field__values);
            else                                     
                $field__values_unserilize = $field__values;
        }
        
        
        foreach ( $field__values_unserilize as $key => $value ) {
            
            $pattern = '\[(' . $key . ')_hint_static' . '([^\]]*)' . '\]';
            
            preg_match_all("/$pattern/", $form, $matches, PREG_SET_ORDER);
            
            /*
            [0] => Array
                (
                    [0] => [surflessons_hint_static "1"]
                    [1] => surflessons
                    [2] =>  "1"
                )

            [1] => Array
                (
                    [0] => [surflessons_hint_static "2"]
                    [1] => surflessons
                    [2] =>  "2"
                )
             */
            if ( count($matches) > 0 ) {
                
                foreach ( $matches as $static_hint ) {
                    
                    $cost_for_insert = '';
                    if ( isset($field__values_unserilize[ $static_hint[1] ] ) ) {
                        
//debuge($field__values_unserilize[ $static_hint[1] ], $static_hint);

                        if ( isset( $field__values_unserilize[ $static_hint[1] ][ 'checkbox' ] ) )             // Check additional cost in  standard checkbox, like this [checkbox some_name ""]                             
                            $cost_for_insert = $field__values_unserilize[ $static_hint[1] ][ 'checkbox' ];
                        
                        
                        if ( isset( $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ] ) ) 
                            $cost_for_insert = $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ];
                        
                        $static_hint[2] = str_replace( "'", '', $static_hint[2] );
                        $static_hint[2] = str_replace( '"', '', $static_hint[2] );
                        $static_hint[2] = trim($static_hint[2]);
                        if ( isset( $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ] ) ) 
                            $cost_for_insert = $field__values_unserilize[ $static_hint[1] ][ $static_hint[2] ];
//debuge($static_hint, $cost_for_insert);

                        if ( strpos($cost_for_insert, '%') === false ) {
                            $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
                            $cost_for_insert = $cost_currency . ' ' . $cost_for_insert;                            
                        } else {
                            //Here we are have percents, then set  it to the empty
                            $cost_for_insert = '';
                        }  
                        // Replace staic cost  hint element to  the cost  value
                        $form = str_replace( $static_hint[0], $cost_for_insert, $form );
                    }
                    
                }
                
                //debuge($matches);
            }
        }
        
        //debuge($field__values_unserilize);
        
        return $form;
    }
    
    
    // Apply advanced cost to the cost from paypal form
    function advanced_cost_apply( $summ , $form , $bktype , $days_array , $is_get_description = false ){
        //if ($summ == 0 ) return  $summ;
        $booking_form_name='';
        if (isset($_POST['booking_form_type']) ){
            if (! empty($_POST['booking_form_type'])) {
                $booking_form_name = $_POST['booking_form_type'];
                $booking_form_name = str_replace("\'",'',$booking_form_name);
                if ($booking_form_name == 'standard') $booking_form_name = '';
            }
        }

//debuge($form);
        $additional_cost = 0;                                               // advanced cost, which will apply
        $booking_form_show = get_form_content ($form, $bktype);

//debuge($booking_form_show);
        if ($booking_form_name === '') { $field__values = get_bk_option( 'booking_advanced_costs_values' ); }     // Get saved advanced cost structure for STANDARD form
        else { $field__values = get_bk_option( 'booking_advanced_costs_values_for' . $booking_form_name ); }
//debuge($field__values, $booking_form_name);
        $full_procents = 1;
        $advanced_cost_hint = array();
        if ( $field__values !== false ) {                                   // Its exist
            if ( is_serialized( $field__values ) )   $field__values_unserilize = unserialize($field__values);
            else                                               $field__values_unserilize = $field__values;
        $booking_form_show['content'] ='';

        

            if (! empty($field__values_unserilize)) {                       // Checking
                if (is_array($field__values_unserilize)) {
                    foreach ($field__values_unserilize as $key_name => $value) {    // repeat in format "visitors"  =>  array ("1"=>25, "2"=>"200%")
                        $key_name= trim($key_name);                         // Get trim visitors name (or some other)
                        
                        $advanced_cost_hint[$key_name] = array( 'value' => $value );
// debuge($key_name, $value);
// debuge($booking_form_show);
                        if (isset( $booking_form_show[$key_name] )) {       // Get value sending from booking form like this $booking_form_show["visitors"]
                            $selected_value = $booking_form_show[$key_name];


                            if ( is_array($selected_value) )  $selected_value_array = $selected_value;
                            else {
                                if ( strpos($selected_value,',')===false )
                                     $selected_value_array = array($selected_value);
                                else $selected_value_array = explode(',',$selected_value);
                            }

//debuge($value, $selected_value_array);
                            foreach ($selected_value_array as $selected_value ) {
                                        $selected_value = trim($selected_value);
                                        $selected_value = str_replace(' ','_',$selected_value);
                                        if (
                                                ($selected_value == '') ||
                                                ($selected_value == 'yes') ||
                                                ($selected_value ==  __('yes' ,'booking') )
                                            ) $selected_value = 'checkbox';

                                        if ( isset($value[$selected_value]) ) {         // check how its value for selected value in cash or procent
                                            $additional_single_cost = $value[$selected_value];                      
                                            $additional_single_cost = str_replace(',','.',$additional_single_cost);
                                            $full_additional_single_cost = 0;
                                            if ( strpos($additional_single_cost, '%') !== false ) {                     // %
                                                if ( strpos($additional_single_cost, '+') !== false ) {
                                                    $additional_single_cost = str_replace('%','',$additional_single_cost);
                                                    $additional_single_cost = str_replace('+','',$additional_single_cost);
                                                    $additional_single_cost = floatval($additional_single_cost);  
                                                    $full_additional_single_cost = floatval(  $summ * ( $additional_single_cost/100)  );
                                                    $advanced_cost_hint[$key_name]['fixed'] = $full_additional_single_cost;
                                                    $additional_cost += $full_additional_single_cost;  
                                                } else {
                                                    $additional_single_cost = str_replace('%','',$additional_single_cost);
                                                    $additional_single_cost = floatval($additional_single_cost);
                                                    $advanced_cost_hint[$key_name]['percent'] = ( ( $additional_single_cost * 1 /100)  );
                                                    $full_procents =  ( ( $additional_single_cost * $full_procents /100)  );
//debuge('$full_procents, $additional_single_cost', $full_procents, $additional_single_cost);                                                    
                                                }
                                            }elseif ( strpos($additional_single_cost, '/day') !== false ) {             // per day
                                                $additional_single_cost = str_replace('/day','',$additional_single_cost);
                                                $additional_single_cost = floatval($additional_single_cost);
                                                $full_additional_single_cost = floatval($additional_single_cost)*count($days_array);                                               
                                                $advanced_cost_hint[$key_name]['fixed'] = $full_additional_single_cost;
                                                $additional_cost += $full_additional_single_cost;
                                            }elseif ( strpos($additional_single_cost, '/night') !== false ) {             // per day
                                                $additional_single_cost = str_replace('/night','',$additional_single_cost);
                                                $additional_single_cost = floatval($additional_single_cost);
                                                $nights_count = (count($days_array)-1);
                                                if ($nights_count==0) $nights_count = 1;
                                                $full_additional_single_cost = floatval($additional_single_cost)*$nights_count;
                                                $advanced_cost_hint[$key_name]['fixed'] = $full_additional_single_cost;
                                                $additional_cost += $full_additional_single_cost;
                                            }else{                                                                      // cashe
                                                $full_additional_single_cost = floatval($additional_single_cost);
                                                $advanced_cost_hint[$key_name]['fixed'] = $full_additional_single_cost;
                                                $additional_cost += $full_additional_single_cost;
                                            }                                          
                                        }
                            }

                        }

                    }
                }
            }

        }

        
        if ( $is_get_description ) {
            
            foreach ( $advanced_cost_hint as $key_name => $array_values ) {

                if (! isset($advanced_cost_hint[$key_name]['cost_hint']))
                    $advanced_cost_hint[$key_name]['cost_hint'] = '';

                if ( isset($array_values['percent'])) {

                    if ( get_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents' ) == 'On' ) {
                        $tot_sum = ( ($summ + $additional_cost) * $full_procents );
                        $advanced_cost_hint[$key_name]['cost_hint'] =  $tot_sum - $tot_sum / $advanced_cost_hint[$key_name]['percent'] ;
                    } else { 
//debuge($summ , $full_procents )                        ;
                        $tot_sum = ( ($summ ) * $full_procents );
                       
                        $advanced_cost_hint[$key_name]['cost_hint'] = $tot_sum - $tot_sum / $advanced_cost_hint[$key_name]['percent'] ;
//debuge($tot_sum,$advanced_cost_hint[$key_name]['percent'], $advanced_cost_hint[$key_name]['cost_hint']);                         
                    }

                } else if ( isset($array_values['fixed'])) {
                    $advanced_cost_hint[$key_name]['cost_hint'] = $advanced_cost_hint[$key_name]['fixed'];
                }
            }
            
        $show_advanced_cost_hints = array();
        foreach ( $advanced_cost_hint as $key => $value ) {
            $show_advanced_cost_hints[$key . '_hint'] = $value['cost_hint'];
        }
            
//debuge($advanced_cost_hint);
        return $show_advanced_cost_hints;
        }


        if ( get_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents' ) == 'On' ) {
            return ($summ + $additional_cost) * $full_procents;
        } else {                                                                              
            return $summ * $full_procents + $additional_cost ;
        }
    }



//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Apply fixed deposit cost to the cost from paypal form
    function fixed_deposit_amount_apply($summ , $post_form, $booking_type , $booking_days = false ) {

        $original_summ = $summ;                                             // Original cost for booking
        // $is_resource_deposit_payment_active   = get_bk_option( 'booking_is_resource_deposit_payment_active');
        // if ($is_resource_deposit_payment_active == 'On') {

                $fixed_deposit = $this->get_bk_type_meta( $booking_type ,'fixed_deposit');

                if ( count($fixed_deposit) > 0 ) {
                    if ( is_serialized( $fixed_deposit[0]->value ) ) $fixed_deposit = unserialize($fixed_deposit[0]->value);
                    else                                             $fixed_deposit = $fixed_deposit[0]->value;
                }
                else $fixed_deposit = array('amount'=>'100',
                                            'type'=>'%',
                                            'active' => 'Off',
                                            'apply_after_days' => '0',
                                            'season_filter' => '0'
                                           );

                $resource_deposit_amount            = $fixed_deposit['amount'];
                $resource_deposit_amount_apply_to   = $fixed_deposit['type'];
                $resource_deposit_is_active         = $fixed_deposit['active'];

                if (isset($fixed_deposit['apply_after_days']))
                     $resource_deposit_apply_after_days  = $fixed_deposit['apply_after_days'];
                else $resource_deposit_apply_after_days  = '0';

                if (isset($fixed_deposit['season_filter']))
                     $resource_deposit_season_filter  = $fixed_deposit['season_filter'];
                else $resource_deposit_season_filter  = '0';
                

                
                // Check if the difference between TODAY and Check In date is valid for the Apply of deposit.
                if ($booking_days !== false) {
                    $sortedDates = getbkSortedDates($booking_days);
                    if ( ! empty($sortedDates) ) {
                        $dates_diff  = getbkDatesDiff('+' . $resource_deposit_apply_after_days . ' days', $sortedDates[0]);
                        if ($dates_diff > 0)
                            return $summ;
                    }
                }
                
                if ( ! $this->is_check_in_day_in_season_filter( $resource_deposit_season_filter, $booking_days ) )
                    return $summ;        
                
                if ($resource_deposit_is_active == 'On') {

                    if ($resource_deposit_amount_apply_to == '%') $summ = $summ * $resource_deposit_amount / 100 ;
                    else $summ = $resource_deposit_amount;                        
                }
        // }
        return ($summ );
    }
    
    
        function is_check_in_day_in_season_filter( $season_filter_id, $days_string ) {

            if ( $season_filter_id == '0' )                                     // All days - not the season  filter
                return true;
            
            $sortedDates = getbkSortedDates( $days_string );                     // Get Check in day from string: 06.04.2015, 05.04.2015, 07.04.2015, 08.04.2015, 26.03.2015, 09.04.2015, 27.03.2015

            if ( ! empty( $sortedDates ) ) {
                $check_in_date = $sortedDates[0];
                $check_in_date = explode(' ', $check_in_date);
                $check_in_date = $check_in_date[0];
                $check_in_date = explode('-', $check_in_date);
                $check_in = array();
                $check_in['year']  = intval( $check_in_date[0] );
                $check_in['month'] = intval( $check_in_date[1] );
                $check_in['day']   = intval( $check_in_date[2] );                            
            } else 
                return false;


            
            $is_day_inside_of_filter = $this->is_day_inside_of_filter(          // Check  if this day inside of filter
                                                                        $check_in['day'], 
                                                                        $check_in['month'], 
                                                                        $check_in['year'], 
                                                                        $season_filter_id
                                                                      );  
//debuge($check_in_date, $is_day_inside_of_filter , 'tra ta ta');            
            if ( $is_day_inside_of_filter ) 
                return true;
            else
                return  false;                
        }
    
//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




 //  R E S O U R C E     T A B L E     C O S T    C o l l  u m n    ////////////////////////////////////////////////////////////////////////////

       // Show headers collumns
       function resources_settings_table_headers(){
           $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');

           if (isset($_GET['tab'])) if (  ($_GET['tab']=='cost')  ) { ?>

            <th style="text-align:center;width:320px" rel="tooltip" class="tooltip_bottom"  title="<?php _e('Setting rate or cost, which  is depend from number of selected days for the resource' ,'booking');?>">
             <?php _e('Rates' ,'booking'); echo " | "; _e('Valuation days' ,'booking'); echo " | "; _e('Deposit' ,'booking'); ?>
            </th>
            <?php } if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability')  ) { ?>
            <th style="width:60px;text-align:center;" rel="tooltip" class="tooltip_bottom"  title="<?php _e('Setting rate or cost, which  is depend from number of selected days for the resource' ,'booking');?>">
             <?php _e('Availability' ,'booking'); ?>
            </th>
          <?php
            } if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) if(   (! isset($_GET['tab'])) || ( (isset($_GET['tab'])) && ($_GET['tab']=='resource') )   ) { ?>
            <th style="width:60px;text-align:center;" rel="tooltip" class="tooltip_bottom"  title="<?php _e('Setting the default form for the specific resource' ,'booking');?>">
             <?php _e('Default Form' ,'booking'); ?>
            </th>
          <?php
            }
       }

       // Show footers collumns
       function resources_settings_table_footers(){
          if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability') || ($_GET['tab']=='cost')  ) { ?>              
            <td></td>
          <?php  } 
       }


       // Show Resources Collumns
       function resources_settings_table_collumns( $bt, $all_id, $alternative_color, $advanced_params = array() ){
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');

            $page_num = ''; $wh_resource_id='';
            if (isset($_REQUEST['page_num'])) $page_num = '&page_num='.$_REQUEST['page_num'];
            if (isset($_REQUEST['wh_resource_id'])) $wh_resource_id =  '&wh_resource_id='.$_REQUEST['wh_resource_id'];

            $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources' . $page_num . $wh_resource_id ;
          if (isset($_GET['tab'])) if (  ($_GET['tab']=='cost')  ) {  $link .= '&tab=cost'; ?>
                <?php // Show Costs  ?>
                <td style="text-align:center;border-left:1px solid #ccc;" <?php echo $alternative_color; ?> >
                    <a     class="button" style="font-size: 11px !important;" 
                           href="javascript:void(0)" onclick="javascript:wpdevbk_get_selected_checkboxes_id('<?php echo $link; ?>','wpdev_edit_rates', '<?php echo $bt->id; ?>', 'input:checkbox.resources_items');"
                           ><?php _e('Rates' ,'booking'); ?></a>

                    <?php  if( in_array( get_bk_option( 'booking_paypal_price_period' ), array('day', 'night') ) ) { ?>
                    <a class="button" style="font-size: 11px !important;" 
                       href="javascript:void(0)" onclick="javascript:wpdevbk_get_selected_checkboxes_id('<?php echo $link; ?>','wpdev_edit_costs_from_days', '<?php echo $bt->id; ?>', 'input:checkbox.resources_items');"
                       ><?php _e('Valuation days' ,'booking'); ?></a>                        
                    <?php } else { ?>
                      <a   rel="tooltip" class="tooltip_top button disabled"  style="font-size: 11px !important;"
                         title="<?php echo __('Set the cost based on the number of days selected for the reservation' ,'booking'); ?>"
                         href="javascript:void(0)" onclick="javascript:alert('<?php _e('Activation of this feature is require setting cost per day or cost per night' ,'booking'); ?>');"><?php _e('Valuation days' ,'booking'); ?></a>
                    <?php } ?>
                    <?php /*
                          $is_resource_deposit_payment_active   = get_bk_option( 'booking_is_resource_deposit_payment_active');
                          if ($is_resource_deposit_payment_active == 'On') { */ ?> 
                            <a class="button" style="font-size: 11px !important;" 
                               href="javascript:void(0)" onclick="javascript:wpdevbk_get_selected_checkboxes_id('<?php echo $link; ?>','wpdev_edit_costs_deposit_payment', '<?php echo $bt->id; ?>', 'input:checkbox.resources_items');"
                               ><?php _e('Deposit amount' ,'booking'); ?></a>

                          <?php /* } else { ?>
                            <a   style="font-size: 11px !important;"  rel="tooltip" class="tooltip_top button disabled" title="<?php echo __('Set the deposit payment required for the payment form' ,'booking'); ?>"
                                 href="javascript:void(0)" onclick="javascript:alert('<?php _e('Activate this feature at the cost section of general booking settings page, firstly.' ,'booking') ; ?>');"><?php _e('Deposit amount' ,'booking'); ?></a>
                          <?php } */ ?>
                </td>
            <?php } if (isset($_GET['tab'])) if ( ($_GET['tab']=='availability')  ) {  $link .= '&tab=availability'; ?>
                    <td style="text-align:center;font-size: 11px;border-left:1px solid #ccc;" <?php echo $alternative_color; ?> >

                        <a class="button" style="font-size: 11px !important;" 
                               href="javascript:void(0)" onclick="javascript:wpdevbk_get_selected_checkboxes_id('<?php echo $link; ?>','wpdev_edit_avalaibility', '<?php echo $bt->id; ?>', 'input:checkbox.resources_items');"
                               ><?php _e('Availability' ,'booking'); ?></a>

                    </td>
            <?php
                } 
                if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) ) if(   (! isset($_GET['tab'])) || ( (isset($_GET['tab'])) && ($_GET['tab']=='resource') )   ) {  $link .= ''; ?>
                    <td style="text-align:center;font-size: 11px;border-left:1px solid #ccc;" <?php echo $alternative_color; ?> >

                  <?php
                    if (isset($advanced_params['custom_forms'])) $booking_forms_extended = $advanced_params['custom_forms'];
                    else $booking_forms_extended = false;
                    if ($booking_forms_extended !== false) { ?>
                        <legend class="wpbc_mobile_legend"><?php _e('Default form' ,'booking'); ?>:</legend>
                        <select id="booking_default_form_<?php echo $bt->id ; ?>" name="booking_default_form_<?php echo $bt->id ; ?>" style="width:120px;" >
                            <option value="standard" ><?php _e('Standard' ,'booking'); ?></option>
                            <?php foreach ($booking_forms_extended as $value) { ?>
                            <option value="<?php echo $value['name']; ?>" <?php if ( $bt->default_form == $value['name'] ) { echo ' selected="SELECTED" '; } ?>   ><?php echo $value['name']; ?></option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                    </td>
                <?php
                }
       }


                // Update SQL dfor editing bk resources
                function get_sql_4_update_def_form_in_resources($blank, $bt){

                    global $wpdb;
                    $sql_res = '';

                    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                    if ( ($is_can) || (WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS) )
                            $sql_res = $wpdb->prepare( " , default_form = %s ", $_POST['booking_default_form_'.$bt->id] );

                    return $sql_res;
                }

       function wpdev_get_default_booking_form_for_resource($blank, $booking_resource_id) {
            global $wpdb;
            $types_list = $wpdb->get_results( $wpdb->prepare( "SELECT default_form FROM {$wpdb->prefix}bookingtypes  WHERE booking_type_id = %d " , $booking_resource_id ) );
            if ($types_list)
                return $types_list[0]->default_form;
            else
                return $blank;

       }

       //Show Rates, Availbaility and other sections for resource configurations
       function wpdev_bk_booking_resource_page_before(){

                $this->show_specific_type_avalaibility_filter();
                $this->show_specific_type_rate();
                $this->show_specific_cost_depends_from_days_count();
                // $is_resource_deposit_payment_active   = get_bk_option( 'booking_is_resource_deposit_payment_active');
                // if ($is_resource_deposit_payment_active == 'On')
                    $this->show_setings_for_deposit_cost_amount();
       }



// A d m i n   S E T T I N G S    M E N U     ////////////////////////////////////////////////////////////////////////////////////////////////


        // A v a i l a b i l i t y   Settings of BK Resource SubPage - Edit availability for specific booking resource
        function show_specific_type_avalaibility_filter(){ 
            
            if (! isset($_GET['wpdev_edit_avalaibility'])) return;
//debuge($_POST);
            global $wpdb;

            // Recehck  if we are edit several  booking resourecs at once 
            $edit_id = $_GET['wpdev_edit_avalaibility'];
            $edit_id_array = explode(',', $edit_id);
            $edit_id = $edit_id_array[0]; // By default get  the info for the first booking resource

            if ( isset( $_POST['submit_availabilitytypefilter'] ) ) {

                $availability = array();
                $days_avalaibility = $_POST['days_avalaibility'];

                    $where = '';
                    $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                    if ($where != '') $where = ' WHERE ' . $where;

                $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons  ".$where."  ORDER BY booking_filter_id DESC" );
                foreach ($filter_list as $value) {
                    if ( isset( $_POST['seasonfilter'.$value->id] ) )  $seasonfilter[$value->id] = 'On' ;
                    else                                               $seasonfilter[$value->id] = 'Off' ;
                }
                $availability['general'] = $days_avalaibility;
                $availability['filter']  = $seasonfilter;

                // Update data to all edited booking resources.
                foreach ($edit_id_array as $edit_id) {
                    $this->set_bk_type_meta($edit_id,'availability',serialize($availability));
                }

            } else {
                $availability_res = $this->get_bk_type_meta($edit_id,'availability');
//debuge($availability_res);
                if ( count($availability_res)>0 ) {

                    if ( is_serialized( $availability_res[0]->value ) )   $availability = unserialize($availability_res[0]->value);
                    else                                                  $availability = $availability_res[0]->value;

                    $days_avalaibility = $availability['general'];
                    $seasonfilter      = $availability['filter'];
                } else {
                    $days_avalaibility = 'On';
                }
            }

            // Get titles of the edited booking resources
            $title = array();
            $results = $this->get_booking_types(implode(',', $edit_id_array));
            foreach ($results as $value) {
                $title[] = $value->title ;
            }
            $title = implode(', ', $title);    

            if (! isset($days_avalaibility)) $days_avalaibility = 'On'; 

            if ($days_avalaibility =='On') $days_avalaibility_word = __('unavailable' ,'booking');
            else                           $days_avalaibility_word = __('available' ,'booking');
            ?>
                       <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Availability booking type' ,'booking'); ?></span></h3> <div class="inside">

                            <form  name="avalaibilitytypefilter" action="" method="post" id="avalaibilitytypefilter" >

                                <div class="wpbc_season_filter_hedear_section" >
                                    <?php printf(__('All days for %s' ,'booking'),'<span class="wpbc_selected_resources">'.$title.'</span>'); ?>:
                                    <legend class="wpbc_mobile_legend"><br/></legend>
                                    <label for="days_avalaibility_on" class="wpbc_label_available">
                                        <input onchange="javascript:setavailabilitycontent('<?php _e('unavailable' ,'booking'); ?>');" type="radio" name="days_avalaibility" id="days_avalaibility_on" <?php if ($days_avalaibility == 'On') echo 'checked="checked"'; ?>  value="On" />
                                        <?php _e('available' ,'booking');?>
                                    </label>
                                    <label for="days_avalaibility_off"  class="wpbc_label_unavailable">
                                        <input onchange="javascript:setavailabilitycontent('<?php _e('available' ,'booking'); ?>');" type="radio" name="days_avalaibility" id="days_avalaibility_off" <?php if ($days_avalaibility == 'Off') echo 'checked="checked"'; ?> value="Off" />
                                            <?php _e('unavailable' ,'booking');?>
                                    </label>
                                    <div class="wpbc-help-message"><?php printf(__('Select %s days by activating specific season filter below or %sadd new season filter%s' ,'booking')
                                            ,'<span id="selectword">'.$days_avalaibility_word .'</span>' 
                                            ,'<a class="button button-secondary" href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=filter&filterdisplay=1">','</a>'); ?></div>
                                </div>                                    
                                <div style="clear: both;height:1px;"></div> 

                                <table class="booking_table resource_table wpbc_seasonfilters_table" cellpadding="0" cellspacing="0">
                                    <tr>
                                        <th class="wpbc_column_1">
                                            <input name="seasonfilter_all" id="seasonfilter_all" type="checkbox"  onclick="javascript:setCheckBoxInTable(this.checked, 'filter_avalaibility');" />
                                        </th>
                                        <th class="wpbc_column_2"><?php _e('Season Filter Name' ,'booking') ?></th>
                                        <th class="wpbc_column_3"><?php _e('Description' ,'booking') ?></th>
                                    </tr>
                                  <?php
                                        $where = '';
                                        $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                                        if ($where != '') $where = ' WHERE ' . $where;

                                    $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons ".$where." ORDER BY booking_filter_id DESC" );
                                    $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=filter';
                                    $td_class = ' alternative_color ';
                                    foreach ($filter_list as $value) {
                                        $is_checked = 'not_checked';
                                        if(isset($seasonfilter)) if(isset($seasonfilter[$value->id])) if($seasonfilter[$value->id] == 'On') $is_checked = 'checked';

                                        if ( $td_class == '') $td_class = ' alternative_color ';
                                        else                  $td_class = '';
                                    ?>
                                          <tr>
                                              <td class="wpbc_column_1 <?php echo $td_class, $is_checked; ?>"><legend class="wpbc_mobile_legend"><?php _e('Active' ,'booking'); ?>:</legend>
                                                  <input <?php if ( $is_checked == 'checked') echo 'checked="checked"'; ?> value="<?php  if(isset($seasonfilter)) if(isset($seasonfilter[$value->id])) echo $seasonfilter[$value->id];?>" class="filter_avalaibility" name="seasonfilter<?php echo $value->id; ?>" id="seasonfilter<?php echo $value->id; ?>" type="checkbox"/>
                                              </td>
                                              <td class="wpbc_column_2 <?php echo $td_class, $is_checked; ?>"><legend class="wpbc_mobile_legend"><?php _e('Season Filter Name' ,'booking'); ?>: </legend><a class="wpbc_season_filer_link" title="<?php _e('Edit season filter' ,'booking'); ?>" style="text-decoration:none;" href="<?php echo $link . '&wpdev_edit=' . $value->id; ?>"><?php echo $value->title; ?></a></td>
                                              <td class="wpbc_column_3 <?php echo $td_class, $is_checked; ?>"><legend class="wpbc_mobile_legend"><?php _e('Description' ,'booking'); ?>: </legend><?php echo $this->get_filter_description($value->filter); ?></td>
                                          </tr>
                                    <?php    echo '<div>';
                                    } ?>
                                </table>

                                <div class="clear" style="height:10px;"></div>
                                <input class="button-primary button" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_availabilitytypefilter"/>
                                <div class="clear" style="height:10px;"></div>

                            </form>

                       </div> </div> </div><?php
        }

        // R a t e s   Settings of Rates for specific resource -- Subpage of Bk resources
        function show_specific_type_rate(){ 

            if (! isset($_GET['wpdev_edit_rates'])) return;

            $seasonfilter = array();
            $rate = array();
            $rate_type = array();
            global $wpdb;

            // Recehck  if we are edit several  booking resourecs at once 
            $edit_id = $_GET['wpdev_edit_rates'];
            $edit_id_array = explode(',', $edit_id);
            $edit_id = $edit_id_array[0]; // By default get  the info for the first booking resource



            if ( isset( $_POST['submit_rate_filter'] ) ) {
//debuge($_POST);
                $rates_meta = array();
                $where = '';
                $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                if ($where != '') $where = ' WHERE ' . $where;

                $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons ".$where." ORDER BY booking_filter_id DESC" );
                foreach ($filter_list as $value) {
                    if ( isset( $_POST['rates_is_active'.$value->id] ) )  $seasonfilter[$value->id] = 'On' ;
                    else                                                  $seasonfilter[$value->id] = 'Off' ;

                    if ( isset( $_POST['rate'.$value->id] ) )  $rate[$value->id] = $_POST['rate'.$value->id] ;
                    else                                       $rate[$value->id] = '0' ;

                    if ( isset( $_POST['rate_type'.$value->id] ) )  $rate_type[$value->id] = $_POST['rate_type'.$value->id] ;
                    else                                            $rate_type[$value->id] = '%' ;

                }
                $rates_meta['filter'] = $seasonfilter;
                $rates_meta['rate']  = $rate;
                $rates_meta['rate_type']  = $rate_type;
//debuge($rates_meta);
                foreach ($edit_id_array as $edit_id) {
                    $this->set_bk_type_meta($edit_id,'rates',serialize($rates_meta));
                }

            } else {
                $rates_res = $this->get_bk_type_meta($edit_id,'rates');
//debuge($edit_id,$rates_res);
                if ( count($rates_res)>0 ) {
                    if ( is_serialized( $rates_res[0]->value ) )        $rates_meta = unserialize($rates_res[0]->value);
                    else                                                $rates_meta = $rates_res[0]->value;
//debuge($rates_meta);
                    $rate         = $rates_meta['rate'];
                    $seasonfilter = $rates_meta['filter'];
                    if (isset($rates_meta['rate_type'])) $rate_type    = $rates_meta['rate_type'];
                    else                                 $rate_type    = array();
                } else {
                }
            }


            // Get titles of the edited booking resources
            $title = array();
            $results = $this->get_booking_types(implode(',', $edit_id_array));
            foreach ($results as $value) {
                $title[] = $value->title ;
                $cost    = $value->cost;
            }
            $title = implode(', ', $title);    
            if (count($edit_id_array) > 1 ) $cost = '';


            ?>
                       <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Seasonal rates of booking resource' ,'booking'); ?></span></h3> <div class="inside">

                            <form  name="sesonratesfilter" action="" method="post" id="sesonratesfilter" >

                                <div style="padding:0px 10px; font-size:12px;" class="">
                                           <div class="wpbc-help-message" style="font-size:1.2em;">
                                                <?php printf(__('Enter seasonal rate(s) (cost diference in %s from standard cost %s or a fixed cost) of the booking resource (%s) or %sAdd a new seasonal filter%s' ,'booking')
                                                        ,'%' 
                                                        , '<span style="color:#F90;">' . $cost . '</span>' 
                                                        ,'<span style="color:#F90;">' . $title .'</span>' 
                                                        ,'<a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=filter&filterdisplay=1" class="button button-primary">'
                                                        ,'</a>');
                                                ?>
                                           </div>
                                    <div class="seasonal_rates">
                                          <table class="booking_table resource_table wpbc_rates_table" cellpadding="0" cellspacing="0">
                                              <tr>
                                                  <th style="width:15px;">
                                                      <input name="seasonfilter_all" id="seasonfilter_all" type="checkbox"  onclick="javascript:setCheckBoxInTable(this.checked, 'filter_avalaibility');" />
                                                  </th>
                                                  <th style="width:140px;height:30px;"><?php _e('Rates' ,'booking') ?></th>
                                                  <th style="width:150px;"><?php _e('Final cost' ,'booking') ?></th>
                                                  <th style="width:200px;text-align: left;"><?php _e('Season Filter' ,'booking') ?></th>
                                                  <th><?php _e('Description' ,'booking') ?></th>
                                              </tr>
                                      <?php
                                        $where = '';
                                        $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                                        if ($where != '') $where = ' WHERE ' . $where;

                                        $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons ".$where." ORDER BY booking_filter_id DESC" );
                                        $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=filter';
                                                
                                        $td_class = ' alternative_color ';
                                        foreach ($filter_list as $value) {
                                            
                                            // Skip hour filter for rates
                                            if ( is_serialized( $value->filter ) ) $starttimes = unserialize($value->filter);
                                            else                                         $starttimes = $value->filter;
// Skip hourly rate if not per hour
//if ( (!empty ($starttimes['start_time'])) && (!empty ($starttimes['end_time'])) && ( get_bk_option( 'booking_paypal_price_period' ) !== 'hour') ) { continue; }

                                            if ( $td_class == '') $td_class = ' alternative_color ';
                                            else                  $td_class = '';

                                            $is_checked = 'not_checked';
                                            if (isset($seasonfilter[$value->id])) if($seasonfilter[$value->id] == 'On') $is_checked = 'checked';
                                        ?>
                                              <tr>
                                                  <td class="<?php echo $td_class , $is_checked; ?>">
                                                      <legend class="wpbc_mobile_legend"><?php _e('Active' ,'booking'); ?>:</legend>
                                                      <input <?php if ( $is_checked == 'checked') echo 'checked="checked"'; ?> value="<?php if (isset($seasonfilter[$value->id])) echo $seasonfilter[$value->id];?>" class="filter_avalaibility" name="rates_is_active<?php echo $value->id; ?>" id="rates_is_active<?php echo $value->id; ?>" type="checkbox"/>
                                                  </td>
                                                  <td class="rates_collumn <?php echo $td_class , $is_checked; ?>">
                                                     <fieldset>
                                                        <legend class="wpbc_mobile_legend clear"><?php _e('Rate' ,'booking'); ?>:</legend> 
                                                        <input value="<?php if ( isset($rate[$value->id]) ) $rate_now = $rate[$value->id]; else $rate_now = '0'; echo $rate_now; ?>" 
                                                               maxlength="700" type="text" 
                                                               name="rate<?php echo $value->id; ?>" id="rate<?php echo $value->id; ?>" >
                                                        <select name="rate_type<?php echo $value->id; ?>" id="rate_type<?php echo $value->id; ?>">
                                                              <option value="%"       <?php if ( isset($rate_type[$value->id]) ) { if ($rate_type[$value->id] == '%')       echo 'selected="selected"'; } else  echo 'selected="selected"'; ?>  >%</option>
                                                              <option value="curency" <?php if ( isset($rate_type[$value->id]) ) { if ($rate_type[$value->id] == 'curency') echo 'selected="selected"'; } ?> >
                                                              <?php echo get_bk_option( 'booking_paypal_curency' ); ?>
                                                                <?php //if ( (!empty ($starttimes['start_time'])) && (!empty ($starttimes['end_time'])) ) {  _e('for 1 hour' ,'booking');  } else { ?>
                                                                    <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day')    _e('for 1 day' ,'booking');    ?>
                                                                    <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night')  _e('for 1 night' ,'booking');  ?>
                                                                    <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed')  _e('fixed deposit' ,'booking');?>
                                                                    <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour')   _e('for 1 hour' ,'booking');   ?>
                                                                <?php //} ?>
                                                              </option>
                                                        </select>
                                                     </fieldset>
                                                  </td>
                                                  <td class="<?php echo $td_class , $is_checked; ?>" style="text-align: center;font-weight: bold;">
                                                      <legend class="wpbc_mobile_legend"><?php _e('Final cost' ,'booking'); ?>:</legend>
                                                    <?php
                                                            if ($cost !== '') {
                                                                if ( isset($rate_type[$value->id]) ) {
                                                                    if ($rate_type[$value->id] == 'curency') {
                                                                        echo $rate_now ;
                                                                    } else echo  ($cost*$rate_now/100);  // + $cost;
                                                                } else echo  ($cost*$rate_now/100) ; // + $cost;

                                                                echo ' <span style="font-weight:normal;">', get_bk_option( 'booking_paypal_curency' ); ?>                                                                          
                                                                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'day')    _e('for 1 day' ,'booking');    ?>
                                                                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'night')  _e('for 1 night' ,'booking');  ?>
                                                                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'fixed')  _e('fixed deposit' ,'booking');?>
                                                                          <?php if( get_bk_option( 'booking_paypal_price_period' ) == 'hour')   _e('for 1 hour' ,'booking');   ?>
                                                               <?php echo '</span>'; 
                                                            }
                                                           ?>

                                                  </td>
                                                  <td class="<?php echo $td_class , $is_checked; ?>"><legend class="wpbc_mobile_legend"><?php _e('Filter Name' ,'booking'); ?>:</legend><a  class="wpbc_season_filer_link" title="<?php _e('Edit season filter' ,'booking'); ?>" class="wpbc-season-filter-link" href="<?php echo $link . '&wpdev_edit=' . $value->id; ?>"><?php echo $value->title; ?></a></td>
                                                  <td class="<?php echo $td_class , $is_checked; ?>"><legend class="wpbc_mobile_legend"><?php _e('Description' ,'booking'); ?>:</legend><?php                                                           
                                                    echo $this->get_filter_description($value->filter); 
                                                  ?></td>
                                              </tr>
                                        <?php    echo '<div>';
                                        }
                                       ?></table></div>
                                    <div class="wpbc-help-message" style="font-size:1em;"><strong><?php _e('Note!' ,'booking'); echo '</strong> '; _e('Check boxe(s) at left side if you want to activate specific cost.' ,'booking');  ?></div>
                                </div>

                                <div class="clear" style="height:1px;"></div>
                                <input class="button-primary button" style="margin:0 10px;" type="submit" value="<?php _e('Update Rates' ,'booking'); ?>" name="submit_rate_filter"/>
                                <div class="clear" style="height:1px;"></div>

                            </form>

                       </div> </div> </div>

            <?php
        }

        // V a l u a t i o n    Days
        function get_all_days_cost_depends_from_selected_days_count($booking_type, $days_array , $times_array ){

                $days_costs = array();
                $maximum_days_count = count($days_array) ;
                $costs_depends = $this->get_bk_type_meta($booking_type,'costs_depends');                    

                if (count($costs_depends) > 0 ) {
                    if ( is_serialized( $costs_depends[0]->value ) )  $costs_depends = unserialize($costs_depends[0]->value);
                    else                                              $costs_depends = $costs_depends[0]->value;
                } else 
                    return false;

//debuge($booking_type, $days_array , $costs_depends);

                    $sortedDates = getbkSortedDates(implode(',',$days_array));
                    if ( ! empty($sortedDates) ) {
                        $check_in_date = $sortedDates[0];
                        $check_in_date = explode(' ', $check_in_date);
                        $check_in_date = $check_in_date[0];
                        $check_in_date = explode('-', $check_in_date);
                        $check_in = array();
                        $check_in['year']  = intval( $check_in_date[0] );
                        $check_in['month'] = intval( $check_in_date[1] );
                        $check_in['day']   = intval( $check_in_date[2] );                            
                    } else 
                        return false;
// debuge($check_in_date, $check_in);

// 3. Check only seson filters for the all dates and season filers, where check in date in this season filter. All other seson filers skip.
// 4. If the Check In date in the Valuation cost item for the specific seson filer and in All Days settings, then first item have the higher priority.


                foreach ( $costs_depends as $value ) {                        // Loop all items of "Valuation days" cost settings.    

                    if ( $value['active'] == 'On' ) {                         // Only Active

                        // Check  COST_DEPEND settings according SEASON FILTERS : //////////////////////
                        // Only if all days is inside of this filter so then apply it
                        $is_can_continue_with_this_item = true;
//debuge($value['season_filter']);
                        if ( ! empty($value['season_filter'] ) ) {   // THIS item  have  some filters s recheck  it.

                            // Check  if this day inside of filter
                            $is_day_inside_of_filter = $this->is_day_inside_of_filter( $check_in['day'], $check_in['month'], $check_in['year'], $value['season_filter'] );  
                            if ( ! $is_day_inside_of_filter ) 
                                $is_can_continue_with_this_item = false;


//                                for($i=0;$i<(count($days_array)  );$i++){
//                                    $d_day = $days_array[$i];
//                                    if (! empty($d_day)) {
//
//                                       $d_day = explode('.',$d_day);
//                                       $day =  ($d_day[0]+0); 
//                                       $month =  ($d_day[1]+0); 
//                                       $year = ($d_day[2]+0);
//                                       // $week =  date('w', mktime(0, 0, 0, $month, $day, $year) );
//
//                                       $is_day_inside_of_filter = $this->is_day_inside_of_filter($day , $month, $year, $value['season_filter']);  // Check  if this day inside of filter
//
//                                       if ( ! $is_day_inside_of_filter) 
//                                           $is_can_continue_with_this_item = false;
//                                    }
//                                }

                        }
                        if (! $is_can_continue_with_this_item) continue;
                        ////////////////////////////////////////////////////////////////////////////////


                        if ($value['type'] == 'summ') {

                            // Check  situation, when the "Together" date alredy set  by some other setting ///////
                            if ( isset( $days_costs[ $value['from'] ] ) ) {
                                $is_can_continue = false;
                                for ($ii = 1; $ii < $value['from']; $ii++) { // Recheck if all previous dates are also set - its mean that was set "Together" option
                                     if (! isset($days_costs[$ii] ) ) {
                                         $is_can_continue = true;            // We have one date not set, its mean the previousl was set For or From selecttors, and we can apply Together
                                     }
                                }
                                if (! $is_can_continue) continue; // Aleready set this option
                            } /////////////////////////////////////////////////////////////////////////////////////


                            if ($value['cost_apply_to'] == '%')  $value['cost']  .= '%';

                            if ( $value['from'] == ($maximum_days_count)) {

                                $days_costs[ $value['from']  ] =  $value['cost'];

                                if ( strpos($value['cost'] , '%') !== false ) $assign_value= $value['cost'];
                                else $assign_value = 0;

                                for ($ii = 1; $ii < $value['from']; $ii++) {
                                     $days_costs[$ii] = $assign_value;
                                }
//debuge($days_costs);                                    
                                return $days_costs;

                            } elseif ( $value['from'] < ($maximum_days_count)) {

                                $days_costs[ $value['from']  ] =   $value['cost'];
                                if ( strpos($value['cost'] , '%') !== false ) $assign_value= $value['cost'];
                                else $assign_value = 0;
                                for ($ii = 1; $ii < $value['from']; $ii++) {
                                     $days_costs[$ii] = $assign_value;
                                }

                            }
                        } elseif ($value['type'] == '=') {
                            if ($value['from'] == 'LAST') $value['from'] = $maximum_days_count;

                            if ( isset( $days_costs[ $value['from'] ] ) ) continue; // Aleready set this option                                

                            if ( $value['from'] <= $maximum_days_count) {

                              if ($value['cost_apply_to'] == 'add') $days_costs[ $value['from']  ] = 'add' .$value['cost'];
                              elseif ($value['cost_apply_to'] == '%') $days_costs[ $value['from']  ] = $value['cost'] . '%';
                              elseif ($value['cost_apply_to'] == 'fixed') $days_costs[ $value['from']  ] = $value['cost'];
                              else $days_costs[ $value['from']  ] = $value['cost'];

                            }
                        } elseif ($value['type'] == '>') {
                          for ($i = $value['from']; $i <= $value['to']; $i++) {
                              if ( $i <= $maximum_days_count)
                                if ( ! isset($days_costs[$i]) ) {

                                      if ($value['cost_apply_to'] == 'add') $days_costs[  $i   ] = 'add' . $value['cost'];
                                      elseif ($value['cost_apply_to'] == '%') $days_costs[  $i   ] = $value['cost'] . '%';
                                      elseif ($value['cost_apply_to'] == 'fixed') $days_costs[  $i   ] = $value['cost'];
                                      else $days_costs[ $i ] = $value['cost'];
                                }
                          }

                        }
//debuge($days_costs);
                    }
                }



                for ($i = 1; $i <= $maximum_days_count ; $i++) {
                    if ( ! isset($days_costs[$i]) ) {
                        $days_costs[$i] = '100%';
                    }
                }
                ksort($days_costs);
//debuge($days_costs);                    
                return $days_costs;

        }


        // Valuation Days Settings
        function show_specific_cost_depends_from_days_count(){
//debuge($_POST);                    
            // If we do not edit costs so then exit
            if ( ! isset($_GET['wpdev_edit_costs_from_days'] ) )               
                return;     

            // If cost not per day or per night so then exit
            if ( ! in_array( get_bk_option( 'booking_paypal_price_period' ), array( 'day' , 'night' ) ) )   
                return;     


            global $wpdb;                    
            // Recehck  if we are edit several  booking resourecs at once 
            $edit_id = $_GET['wpdev_edit_costs_from_days'];
            $edit_id_array = explode(',', $edit_id);
            $edit_id = $edit_id_array[0];                               // By default get  the info for the first booking resource

            if ( isset( $_POST['submit_cost_from_days'] ) ) {

                $post_all_indexes = $_POST['all_indexes'];
                if (substr($post_all_indexes,0,1) == ',') $post_all_indexes = substr($post_all_indexes,1);      // delete first ','
                if (substr($post_all_indexes,-1) == ',')  $post_all_indexes = substr($post_all_indexes,0,-1);   // delete last  ','
                $post_all_indexes=explode(',',$post_all_indexes);

                $costs_depends = array();
                foreach ($post_all_indexes as $ind) {
                    if ( isset($_POST[ 'dayscost_type' . $ind ])) {
                        $new_array_line = array();
                        if ( isset($_POST[ 'dayscost_is_active' . $ind ]) ) $new_array_line['active'] = 'On';
                        else                                                $new_array_line['active'] = 'Off';
                        $new_array_line['type'] = $_POST[ 'dayscost_type' . $ind ];
                        $new_array_line['from'] = $_POST[ 'dayscost_from' . $ind ];
                        $new_array_line['to']   = $_POST[ 'dayscost_to'   . $ind ];
                        $new_array_line['cost'] = str_replace('%','',$_POST[ 'dayscost'      . $ind ]);
                        $new_array_line['cost_apply_to'] = (isset($_POST[ 'cost_apply_to'      . $ind ])) ? $_POST[ 'cost_apply_to'      . $ind ]: '';
                        if (! isset($_POST[ 'season_filter'   . $ind ])) $new_array_line['season_filter'] = 0;
                        else                                             $new_array_line['season_filter']   = $_POST[ 'season_filter'   . $ind ];
                        $costs_depends[] = $new_array_line;
                    }
                }
                foreach ($edit_id_array as $edit_id) {
                    $this->set_bk_type_meta($edit_id,'costs_depends', serialize($costs_depends));
                }

            } else {
                $costs_depends = $this->get_bk_type_meta($edit_id,'costs_depends');

                if (count($costs_depends) > 0 ) {
                    if ( is_serialized( $costs_depends[0]->value ) ) $costs_depends = unserialize($costs_depends[0]->value);
                    else                                             $costs_depends = $costs_depends[0]->value;
                }
                else $costs_depends = array();
            }

            $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=cost';

            // Get titles of the edited booking resources
            $results = $this->get_booking_types(implode(',', $edit_id_array));
            foreach ($results as $value) {
                $cost = $value->cost;
            }

            // Get seson filters.
            $where = '';
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') $where = ' WHERE ' . $where;
            $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons ".$where." ORDER BY booking_filter_id DESC"  );

            $cost_currency = apply_bk_filter('get_currency_info', 'paypal');
            ?>
            <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Set cost of booking resource based on the number of days selected' ,'booking'); ?></span></h3> <div class="inside">
<!--                        <div class="wpbc-help-message" style="font-size:1em;"><?php echo '<strong>'; _e('Note!' ,'booking'); echo '</strong> ';printf(__('If you add costs here, %srates%s for this booking resource will not take effect !!!' ,'booking'), '<a href="'. $link.'&wpdev_edit_rates=' . $edit_id .'">','</a>'); ?></div>-->
                <div class="wpbc-help-message" style="font-size:1em;"><?php echo '<strong>'; _e('Note' ,'booking'); echo '</strong> '; ?>
                    <ul style="list-style: disc outside none;margin: 0 15px;">
                        <li>
                            <?php printf(__('Cost setings at %stop have higher priority%s than other costs of same type at the %sbottom%s of the list.' ,'booking'), '<b>','</b>', '<b>','</b>'); ?><br/>
                            <?php printf(__('Please create all %s terms firstly %s(from higher priority to lower)%s, then terms %s and after terms %s' ,'booking'), '<b>"'.__('Together' ,'booking').'"</b>', '<em>', '</em>', '<b>"'.__('For' ,'booking').'"</b>', '<b>"'.__('From' ,'booking').' - '. __('To' ,'booking').'"</b>'); ?>
                        </li>
                        <li><?php printf(__('%s and %s terms have higher priority than a range %s days.' ,'booking'), '<b>"'.__('Together' ,'booking').'"</b>','<b>"'.__('For' ,'booking').'"</b>', '<b>"'.__('From' ,'booking').' - '. __('To' ,'booking').'"</b>'); ?></li>
                    </ul>
                </div>
                <div class="wpbc-error-message" style="font-size:1em;text-align:left;"><?php echo '<strong>'; _e('Warning!' ,'booking'); echo '</strong> '; 
                printf(__('Specific cost will take affect, only if it active (the box at the left side is checked) and if "Check In" (start) date belong to selected season filter or if set "Any days".' ,'booking'), '<span style="color:#d11;">','</span>', '<b>','</b>'); ?></div>
                <form  name="cost_day_count" action="" method="post" id="cost_day_count" >

                   <table class="resource_table booking_table wpbc_valuationdays_table" cellpadding="0" cellspacing="0">
                      <thead>
                        <tr>
                            <th class="wpbc_column_1">
                                <input name="seasonfilter_all" id="seasonfilter_all" type="checkbox"  onclick="javascript:setCheckBoxInTable(this.checked, 'costs_depends');" />
                            </th>
                            <th class="wpbc_column_2"><?php _e('Number of Days' ,'booking') ?></th>
                            <th class="wpbc_column_3"><?php _e('Cost Settings' ,'booking') ?></th>
                            <th class="wpbc_column_4" colspan="2"><?php _e('Season Filter of Check In date' ,'booking') ?></th>

                         </tr>
                      </thead>
                      <tbody id="costs_days_container">
                        <?php
                        $td_class = ' alternative_color ';
                        $all_indexes =',';
                        $start_i = 0;

                        // If we have settings cost per night, so we need to have required option in Valution days cost settings: "For 
                        if ( get_bk_option( 'booking_paypal_price_period' ) == 'night' ) {
                            $td_class = '';
                           ?>
                           <tr id="cost_days_row0" style="display:none;" >
                             <td class="wpbc_column_1 <?php echo $td_class; ?>" ><legend class="wpbc_mobile_legend"><?php _e('Active' ,'booking'); ?>:</legend>
                                <input  type="checkbox" checked="CHECKED"
                                       value="On"  name="dayscost_is_active0" id="dayscost_is_active0" />
                            </td>
                            <td class="wpbc_column_2 <?php echo $td_class; ?>" >
                                <div  style="float:left;">
                                    <select name="dayscost_type0" id="dayscost_type0" >
                                          <option value="=" selected="selected" ><?php _e('For' ,'booking'); ?></option>
                                    </select>
                                   <legend class="wpbc_mobile_legend"><?php _e('Day Number(s)' ,'booking'); ?>:<div class="clear"></div></legend> 
                                   <input value="2" maxlength="7" type="text" name="dayscost_from0" id="dayscost_from0" />
                                </div>

                                <div class="wpbc_field_cost-to" id="additional_costs_limit0" 
                                     style="display:none;float:left;">
                                    <div class="wpbc_text-label"><?php _e('to' ,'booking'); ?></div>                                            
                                    <input value="2" maxlength="7" type="text" name="dayscost_to0" id="dayscost_to0" />
                                </div>

                                <div class="wpbc_text-label"><?php  _e('day' ,'booking'); ?></div>                                        
                            </td>                                    
                            <td class="wpbc_column_3 <?php echo $td_class; ?>" style="text-align: left;">
                                <div class="wpbc_text-label">=</div>
                                <legend class="wpbc_mobile_legend clear"><div class="clear"></div><?php _e('Cost' ,'booking'); ?>:<div class="clear"></div></legend> 
                                <input value="0" maxlength="7" type="text" name="dayscost0" id="dayscost0" />
                                <span id="cost_days_row_help0" >
                                    <select name="cost_apply_to0" id="cost_apply_to0">
                                        <option selected="selected" value="fixed" ><?php echo $cost_currency, ' ';  _e('per 1 day' ,'booking');?></option>
                                    </select>
                                </span>
                            </td>  
                            <td class="wpbc_column_4 <?php echo $td_class; ?>" >
                                <legend class="wpbc_mobile_legend clear"><?php _e('Season Filter of Check In date' ,'booking'); ?>:</legend> 
                                <select name="season_filter0" id="season_filter0">
                                  <option  selected="SELECTED" value="0" ><?php echo __('Any days' ,'booking'); ?></option>
                                </select>
                            </td>
                            <td class="wpbc_column_5 <?php echo $td_class; ?>" ></td>                                    
                           </tr>    
                           <?php 
                           $all_indexes .= $start_i .',';
                           $start_i++;                                   
                        }

                        for ($i = $start_i; $i < count($costs_depends); $i++) { 

                          if ( $td_class == '') $td_class = ' alternative_color ';
                          else                  $td_class = '';
                          ?>
                          <tr id="cost_days_row<?php echo $i; ?>" >

                            <td class="wpbc_column_1 <?php echo $td_class; ?>" ><legend class="wpbc_mobile_legend"><?php _e('Active' ,'booking'); ?>:</legend>
                                <input <?php if($costs_depends[$i]['active'] == 'On') echo 'checked="checked"'; ?>
                                    value="" class="costs_depends"
                                    name="dayscost_is_active<?php echo $i; ?>" id="dayscost_is_active<?php echo $i; ?>" type="checkbox" />
                            </td>

                            <td class="wpbc_column_2 <?php echo $td_class; ?>" >
                                <div  style="float:left;">
                                    <select name="dayscost_type<?php echo $i; ?>" id="dayscost_type<?php echo $i; ?>"
                                            onchange="javascript:
                                                    if ( ( this.options[this.selectedIndex].value != '=' )
                                                      && ( this.options[this.selectedIndex].value != 'summ' ))
                                                      document.getElementById('additional_costs_limit<?php echo $i; ?>').style.display = 'block';
                                                    else
                                                      document.getElementById('additional_costs_limit<?php echo $i; ?>').style.display = 'none';
                                                    if ( this.options[this.selectedIndex].value != 'summ' )
                                                         jQuery('#cost_days_row_help<?php echo $i; ?>').html( '<?php                                                                                        
                                                         //echo $cost_currency ,' ' , __('per 1 day' ,'booking');
                                                         echo '<select name=&quote;cost_apply_to'. $i.'&quote; id=&quote;cost_apply_to'. $i.'&quote; style=&quote;width:220px;padding:3px 1px 1px 1px !important;&quote; >';
                                                         echo '<option value=&quote;fixed&quote; >'. $cost_currency. ' '.  __('per 1 day' ,'booking').'</option>';
                                                         echo '<option value=&quote;%&quote;     >% '.__('from the cost of 1 day ' ,'booking').'</option>';
                                                         echo '<option value=&quote;add&quote;   >'.sprintf(__('Additional cost in %s per 1 day' ,'booking'),$cost_currency).'</option>';
                                                         echo '</select>';  ?>' );
                                                    else  jQuery('#cost_days_row_help<?php echo $i; ?>').html( '<?php 
                                                         echo '<select name=&quote;cost_apply_to'. $i.'&quote; id=&quote;cost_apply_to'. $i.'&quote; style=&quote;width:220px;padding:3px 1px 1px 1px !important;&quote; >';
                                                         echo '<option value=&quote;fixed&quote; >'. $cost_currency. ' '.  __(' for all days!' ,'booking').'</option>';
                                                         echo '<option value=&quote;%&quote;     >% '.__(' for all days!' ,'booking').'</option>';
                                                         //echo '<option value=&quote;add&quote;   >'.sprintf(__('Additional cost in %s per 1 day' ,'booking'),$cost_currency).'</option>';
                                                         echo '</select>';  ?>' );
                                                         //echo __('For all days!' ,'booking'); ?>' ); " >
                                          <option value="=" <?php if ( $costs_depends[$i]['type'] == '=') echo 'selected="selected"'; ?>  ><?php _e('For' ,'booking'); ?></option>
                                          <option value=">" <?php if ( $costs_depends[$i]['type'] == '>') echo 'selected="selected"'; ?>   ><?php _e('From' ,'booking'); ?></option>
                                          <option value="summ" <?php if ( $costs_depends[$i]['type'] == 'summ') echo 'selected="selected"'; ?>  ><?php _e('Together' ,'booking'); ?></option>
                                    </select>
                                   <legend class="wpbc_mobile_legend clear"><?php _e('Day Number(s)' ,'booking'); ?>:<div class="clear"></div></legend> 
                                   <input value="<?php echo $costs_depends[$i]['from']; ?>" maxlength="7" type="text" 
                                          name="dayscost_from<?php echo $i; ?>" id="dayscost_from<?php echo $i; ?>" >
                                </div>

                                <div class="wpbc_field_cost-to" id="additional_costs_limit<?php echo $i; ?>" style="<?php if ( ( $costs_depends[$i]['type'] == '=') || ( $costs_depends[$i]['type'] == 'summ') ) echo "display:none;";?>float:left;">
                                    <div class="wpbc_text-label"><?php _e('to' ,'booking'); ?></div>                                            
                                    <input value="<?php echo $costs_depends[$i]['to']; ?>" maxlength="7" type="text" 
                                           name="dayscost_to<?php echo $i; ?>" id="dayscost_to<?php echo $i; ?>" >
                                </div>

                                <div class="wpbc_text-label"><?php
                                      if ($costs_depends[$i]['type'] == '=')  _e('day' ,'booking');
                                      else                                    _e('days' ,'booking');
                                ?></div>                                        
                            </td>

                            <td class="wpbc_column_3 <?php echo $td_class; ?>" style="text-align: left;">
                                <div class="wpbc_text-label">=</div>
                                <legend class="wpbc_mobile_legend clear"><div class="clear"></div><?php _e('Cost' ,'booking'); ?>:<div class="clear"></div></legend> 
                                <input value="<?php echo $costs_depends[$i]['cost']; ?>" maxlength="7" 
                                         type="text" name="dayscost<?php echo $i; ?>" id="dayscost<?php echo $i; ?>" >
                                <?php echo ' <span id="cost_days_row_help'.$i.'" >';
                                        if ($costs_depends[$i]['type'] != 'summ') {
                                            ?>
                                                <select name="cost_apply_to<?php echo $i; ?>" id="cost_apply_to<?php echo $i; ?>">
                                                    <option <?php if ( $costs_depends[$i]['cost_apply_to'] == '%') echo 'selected="selected"'; ?> value="%"     ><?php echo '% ';  _e('from the cost of 1 day ' ,'booking');?></option>
                                                    <option <?php if ( $costs_depends[$i]['cost_apply_to'] == 'fixed') echo 'selected="selected"'; ?> value="fixed" ><?php echo $cost_currency, ' ';  _e('per 1 day' ,'booking');?></option>
                                                    <option <?php if ( $costs_depends[$i]['cost_apply_to'] == 'add') echo 'selected="selected"'; ?> value="add"   ><?php printf(__('Additional cost in %s per 1 day' ,'booking'),$cost_currency);?></option>
                                                </select>
                                            <?php
                                        } else  {
                                            ?>
                                                <select name="cost_apply_to<?php echo $i; ?>" id="cost_apply_to<?php echo $i; ?>">
                                                    <option <?php if ( $costs_depends[$i]['cost_apply_to'] == '%') echo 'selected="selected"'; ?> value="%"     ><?php echo '% ';  _e(' for all days!' ,'booking');?></option>
                                                    <option <?php if ( $costs_depends[$i]['cost_apply_to'] == 'fixed') echo 'selected="selected"'; ?> value="fixed" ><?php echo $cost_currency, ' ';  _e(' for all days!' ,'booking');?></option>
                                                </select>
                                            <?php
                                        }
                                      echo '</span>';
                                ?>
                            </td>

                            <td class="wpbc_column_4 <?php echo $td_class; ?>" >
                                <legend class="wpbc_mobile_legend clear"><?php _e('Season Filter of Check In date' ,'booking'); ?>:</legend> 
                                <select name="season_filter<?php echo $i; ?>" id="season_filter<?php echo $i; ?>">
                                       <option <?php if ($costs_depends[$i]['season_filter'] == 0 ) echo ' selected="SELECTED" '; ?>   value="0" ><?php echo __('Any days' ,'booking'); ?></option>
                                    <?php foreach ($filter_list as $value_filter) { ?>
                                       <option <?php if ($costs_depends[$i]['season_filter'] == $value_filter->id ) echo ' selected="SELECTED" '; ?>  value="<?php echo $value_filter->id; ?>" >
                                            <?php echo $value_filter->title; echo ' - '; echo strip_tags($this->get_filter_description($value_filter->filter)); ?>
                                       </option>
                                    <?php } ?>
                                </select>
                            </td>

                            <td class="wpbc_column_5 <?php echo $td_class; ?>" >
                                <input class="button-secondary button" type="button" 
                                       value="<?php _e('Remove' ,'booking'); ?>"
                                       name="new_cost<?php echo $i; ?>"
                                       onclick="javascript:remove_new_days_cost_row(<?php echo $i; ?>);" />                                        
                            </td>

                          </tr><?php 
                          $all_indexes .= $i .','; 
                        }
                        ?>
                      </tbody>
                   </table>                            
                   <input type="hidden" value="<?php echo $all_indexes; ?>" name="all_indexes"  id="all_indexes" />                       
                   <div class="clear" style="height:1px;"></div>
                   <input class="button-secondary button" style="float:left;margin:10px 0 0;" type="button" value="<?php _e('Add new cost' ,'booking'); ?>" name="new_cost" onclick="javascript:add_new_days_cost_row();"/>
                   <input class="button-primary button"   style="float:left;margin:10px;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_cost_from_days"/> 
                   <div class="clear" style="height:1px;"></div>

                </form>
                <script type="text/javascript">
                    var row__id = <?php echo $i; ?>;

                    function remove_new_days_cost_row(row_id){
                         jQuery('#cost_days_row' + row_id ).remove();
                         var all_indexes = jQuery('#all_indexes').val();
                         var temp = all_indexes.split(',' + row_id + ',');
                         all_indexes =  temp.join(',');
                         jQuery('#all_indexes').val(  all_indexes  );
                    }

                    function add_new_days_cost_row(){

                        var d_html = ''

                        var d_row_class = '';
                        if ( (row__id % 2) == 1 )
                            d_row_class = ' alternative_color ';

                        d_html += '<tr id="cost_days_row'+row__id+'" >';
                        ////////////////////////////////////////////////////////////////////////////////                                    
                        d_html += '<td class="wpbc_column_1 '+d_row_class+'" ><legend class="wpbc_mobile_legend"><?php echo esc_js( __('Active' ,'booking') ); ?>:</legend>';
                        d_html += '   <input  type="checkbox" checked="CHECKED" value="" class="costs_depends"';
                        d_html += '           name="dayscost_is_active'+row__id+'" id="dayscost_is_active'+row__id+'" />';
                        d_html += '</td>';
                        ////////////////////////////////////////////////////////////////////////////////
                        d_html += '<td class="wpbc_column_2 '+d_row_class+'" >';
                        d_html += '    <div  style="float:left;">';
                        d_html += '       <select name="dayscost_type'+row__id+'" id="dayscost_type'+row__id+'"';
                        d_html += '               onchange="javascript:if ( ( this.options[this.selectedIndex].value != \'=\' ) && ( this.options[this.selectedIndex].value != \'summ\' ) ) document.getElementById(\'additional_costs_limit'+row__id+'\').style.display = \'block\';  else  document.getElementById(\'additional_costs_limit'+row__id+'\').style.display = \'none\';   \n\
                                                  if ( this.options[this.selectedIndex].value != \'summ\' ) addRowForCustomizationCostDependsFromNumSellDays('+row__id+');   \n\
                                                  else  addRowForCustomizationCostDependsFromNumSellDays4Summ('+row__id+');   \n\ //jQuery(\'#cost_days_row_help'+row__id+'\').html( \'<?php echo $cost_currency ,' ' , esc_js( __('for all days' ,'booking') ); ?>\' ); " >';
                        d_html += '             <option value="="><?php echo esc_js( __('For' ,'booking') ); ?></option>';
                        d_html += '             <option value=">" <?php echo 'selected="selected"'; ?>  ><?php echo esc_js( __('From' ,'booking') ); ?></option>';
                        d_html += '             <option value="summ"  selected="SELECTED"><?php echo esc_js( __('Together' ,'booking') ); ?></option>';
                        d_html += '       </select>';

                        d_html += '       <legend class="wpbc_mobile_legend clear"><?php echo esc_js( __('Day Number(s)' ,'booking') ); ?>:<div class="clear"></div></legend> ';
                        d_html += '       <input value="7" maxlength="7" type="text" ';
                        d_html += '              name="dayscost_from'+row__id+'" id="dayscost_from'+row__id+'" >';
                        d_html += '    </div>';

                        d_html += '        <div class="wpbc_field_cost-to" id="additional_costs_limit'+row__id+'" style="display:none;float:left;">';
                        d_html += '            <div class="wpbc_text-label"><?php echo esc_js( __('to' ,'booking') ); ?></div>';
                        d_html += '            <input value="14" maxlength="7" type="text" ';
                        d_html += '                   name="dayscost_to'+row__id+'" id="dayscost_to'+row__id+'" >';
                        d_html += '        </div>';

                        d_html += '        <div class="wpbc_text-label"><?php  echo esc_js( __('day(s)' ,'booking') );  ?></div>'; 
                        d_html += '</td>';
                        ////////////////////////////////////////////////////////////////////////////////
                        d_html += '<td class="wpbc_column_3 '+d_row_class+'" style="text-align: left;">';
                        d_html += '    <div class="wpbc_text-label">=</div>';
                        d_html += '    <legend class="wpbc_mobile_legend clear"><div class="clear"></div><?php echo esc_js( __('Cost' ,'booking') ); ?>:<div class="clear"></div></legend> ';
                        d_html += '    <input value="<?php echo '90';//(7*$cost); ?>" maxlength="7" ';
                        d_html += '             type="text" name="dayscost'+row__id+'" id="dayscost'+row__id+'" >';
                        d_html += '         <?php
                                                    echo ' <span id="cost_days_row_help'?>'+row__id+'<?php
                                                    echo '" >'; ?>'+getRowForCustomizationCostDependsFromNumSellDays4Summ(row__id)+'<?php echo '</span>';
                                             ?>';
                        d_html += '</td>';
                        ////////////////////////////////////////////////////////////////////////////////
                        d_html += '<td class="wpbc_column_4 '+d_row_class+'" >';
                        d_html += '    <legend class="wpbc_mobile_legend clear"><?php echo esc_js( __('Season Filter of Check In date' ,'booking') ); ?>:</legend> ';
                        d_html += '    <select name="season_filter'+row__id+'" id="season_filter'+row__id+'">';
                        d_html += '       <option selected="SELECTED" value="0" ><?php echo esc_js( __('Any days' ,'booking') ); ?></option>';
                                          <?php foreach ($filter_list as $value_filter) { ?>
                        d_html += '       <option value="<?php echo $value_filter->id; ?>" >';
                        d_html += '       <?php echo esc_js($value_filter->title . ' - ' . strip_tags($this->get_filter_description($value_filter->filter)) ) ; ?>';
                        d_html += '       </option>';
                                          <?php } ?>
                        d_html += '    </select>';
                        d_html += '</td>';
                        ////////////////////////////////////////////////////////////////////////////////
                        d_html += '<td class="wpbc_column_5 '+d_row_class+'" >';
                        d_html += '    <input class="button-secondary button" type="button" ';
                        d_html += '           value="<?php echo esc_js( __('Remove' ,'booking') ); ?>"';
                        d_html += '           name="new_cost'+row__id+'"';
                        d_html += '           onclick="javascript:remove_new_days_cost_row('+row__id+');" />'; 
                        d_html += '</td>';
                        ////////////////////////////////////////////////////////////////////////////////
                        d_html += '</tr>';

                        jQuery('#all_indexes').val( jQuery('#all_indexes').val() + +row__id+ ','  );
                        jQuery('#costs_days_container').append(d_html);
                        row__id++;
                    }
                </script>
           </div> </div> </div>
           <?php
        }


        // Show settings for saving deposit amount
        function show_setings_for_deposit_cost_amount(){

            global $wpdb;
            if (! isset($_GET['wpdev_edit_costs_deposit_payment']))           return;     // If we do not edit costs so then exit

            // Recehck  if we are edit several  booking resourecs at once 
            $edit_id = $_GET['wpdev_edit_costs_deposit_payment'];
            $edit_id_array = explode(',', $edit_id);
            $edit_id = $edit_id_array[0]; // By default get  the info for the first booking resource


            if ( isset( $_POST['submit_resource_deposit'] ) ) {
                    $resource_deposit_apply_after_days =                $_POST['resource_deposit_apply_after_days'];
                    $resource_deposit_amount =                          $_POST['resource_deposit_amount'];
                    $resource_deposit_amount_apply_to =                 $_POST['resource_deposit_amount_apply_to'];  // fixed, %
                    if (isset($_POST['resource_deposit_is_active']))    $resource_deposit_is_active = 'On';
                    else                                                $resource_deposit_is_active = 'Off';

                    if (! isset($_POST[ 'season_filter' ])) $season_filter = 0;
                    else                                    $season_filter = $_POST[ 'season_filter' ];
                                        
                    $fixed_deposit = array(
                                            'amount'=>$resource_deposit_amount,
                                            'type'=>$resource_deposit_amount_apply_to,
                                            'active' => $resource_deposit_is_active,
                                            'apply_after_days' => $resource_deposit_apply_after_days,
                                            'season_filter' => $season_filter
                                          );
                    
                    foreach ($edit_id_array as $edit_id) {
                        $this->set_bk_type_meta($edit_id,'fixed_deposit', serialize($fixed_deposit));
                    }

            } else {
                    $fixed_deposit = $this->get_bk_type_meta($edit_id,'fixed_deposit');

                    if (count($fixed_deposit) > 0 ) {
                        if ( is_serialized( $fixed_deposit[0]->value ) ) $fixed_deposit = unserialize($fixed_deposit[0]->value);
                        else                                             $fixed_deposit = $fixed_deposit[0]->value;
                    }
                    else $fixed_deposit = array('amount'=>'100',
                                                'type'=>'%',
                                                'active' => 'On',
                                                'apply_after_days' => '0',
                                                'season_filter' => '0');
                    
            }
            $resource_deposit_amount            = $fixed_deposit['amount'];
            $resource_deposit_amount_apply_to   = $fixed_deposit['type'];
            $resource_deposit_is_active         = $fixed_deposit['active'];
            if (isset($fixed_deposit['apply_after_days']))
                $resource_deposit_apply_after_days  = $fixed_deposit['apply_after_days'];
            else
                $resource_deposit_apply_after_days  = '0';
            
            if (isset($fixed_deposit['season_filter']))
                $resource_deposit_season_filter  = $fixed_deposit['season_filter'];
            else
                $resource_deposit_season_filter  = '0';

            $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=cost';


            // Get titles of the edited booking resources
            $title = array();
            $results = $this->get_booking_types(implode(',', $edit_id_array));
            foreach ($results as $value) {
                $title[] = $value->title ;
                $cost    = $value->cost;
            }
            $title = implode(', ', $title);    
            if (count($edit_id_array) > 1 ) $cost = '';

            
            // Get seson filters.
            $where = '';
            $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
            if ($where != '') $where = ' WHERE ' . $where;
            $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons ".$where." ORDER BY booking_filter_id DESC"  );
            
            ?>
                       <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Set amount of deposit payment' ,'booking'); ?></span></h3> <div class="inside">

                            <form  name="cost_day_count" action="" method="post" id="cost_day_count" >
    <p class="wpbc-info-message" style="text-align:left;"><strong><?php 
        if ($resource_deposit_is_active=='On') {
            if ($cost != '') {
                $cost_dep = $cost;
                if ($resource_deposit_amount_apply_to == '%') $cost_dep = $cost_dep * $resource_deposit_amount / 100 ;
                else $cost_dep = $resource_deposit_amount;
                echo ' '; _e('Deposit payment total' ,'booking'); echo ' ', $cost_dep, ' ', get_bk_option( 'booking_paypal_curency' );
            }
         } else {
             _e('Deposit payment is not active for booking resource' ,'booking'); echo ': <strong>', $title, '</strong> ';   
         }  ?>
        </strong>         
    </p>              
    <table class="form-table wpbc_deposit_table" >
        <tbody>
                <tr>    
                    <th scope="row"><?php _e('Status' ,'booking'); ?>:</th>
                    <td>
                        <fieldset>
                            <label for="resource_deposit_is_active">
                                <input <?php if($resource_deposit_is_active == 'On') echo 'checked="checked"'; ?>
                                      value="<?php echo $resource_deposit_is_active; ?>" class="costs_depends"
                                      name="resource_deposit_is_active" id="resource_deposit_is_active" 
                                      type="checkbox"/>
                                <?php _e('Active' ,'booking'); ?>
                                <?php echo ' '; _e('deposit payment for booking resource' ,'booking'); echo ': <strong>', $title, '</strong> ';   ?>
                            </label>
                        </fieldset>   
                    </td>
                </tr>
                <tr>    
                    <th scope="row"><label for="resource_deposit_amount"><?php _e('Deposit amount' ,'booking'); ?>:</label></th>
                    <td class="rates_collumn">
                        <fieldset>
                            <input value="<?php echo $resource_deposit_amount; ?>" 
                                   maxlength="7"  type="text"
                                         name="resource_deposit_amount" id="resource_deposit_amount" 
                            />
                            <select name="resource_deposit_amount_apply_to"
                                    id="resource_deposit_amount_apply_to" >
                                 <option <?php if ( $resource_deposit_amount_apply_to == 'fixed') echo 'selected="selected"'; ?> value="fixed" ><?php  _e('fixed total in' ,'booking');  echo ' ', get_bk_option( 'booking_paypal_curency' ); ?></option>
                                 <option <?php if ( $resource_deposit_amount_apply_to == '%') echo 'selected="selected"'; ?> value="%"     ><?php echo '% ';  _e('of payment' ,'booking');?></option>
                            </select>
                        </fieldset>   
                    </td>
                </tr>
                <tr>    
                    <th scope="row"><label for="resource_deposit_apply_after_days"><?php _e('Conditions' ,'booking'); ?>:</label></th>
                    <td>
                        <fieldset>             
                            <span for="description" ><?php printf(__('Show deposit payment form, only if difference between %sToday%s and %sCheck In%s days more than' ,'booking'), '<b>"', '"</b>', '<b>"', '"</b>'); ?></span>
                            <select id="resource_deposit_apply_after_days" name="resource_deposit_apply_after_days">
                                <?php  for ($i = 0; $i < 365; $i++) { ?>
                                <option <?php if($resource_deposit_apply_after_days == $i) echo "selected"; ?> 
                                    value="<?php echo $i; ?>"><?php 
                                    if ($i==0) {
                                        echo '---'; 
                                    } else
                                        echo $i, ' ',__('day(s)' ,'booking'); 
                                ?></option><?php                                              
                                } ?>
                            </select>                                    
                        </fieldset>   
                    </td>
                </tr>
                
                <tr>
                    <th scope="row"></td>
                    <td>
                        <fieldset>             
                            <?php $link_season = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=filter'; ?>
                            <span for="description" ><?php printf(__('Show deposit payment form, only if %sCheck In%s day inside of this %sSeason Filter%s' ,'booking'), '<b>"', '"</b>',  '<a class="wpbc_season_filer_link" style="text-decoration:none;" href="' . $link_season . '">', '</a>'); ?>:</span>
                                                    
                            <select name="season_filter" id="season_filter" style="width:215px;">
                                   <option <?php if ( $resource_deposit_season_filter == 0 ) echo ' selected="SELECTED" '; ?>   value="0" ><?php echo __('Any days' ,'booking'); ?></option>
                                <?php foreach ($filter_list as $value_filter) { ?>
                                   <option <?php if ( $resource_deposit_season_filter == $value_filter->id ) echo ' selected="SELECTED" '; ?>  value="<?php echo $value_filter->id; ?>" >
                                        <?php echo $value_filter->title; echo ' - '; echo strip_tags($this->get_filter_description($value_filter->filter)); ?>
                                   </option>
                                <?php } ?>
                            </select>
                        </fieldset>
                    </td>


                </tr>

        </tbody>
    </table>
                                <div class="clear" style="height:1px;"></div>
                                <input class="button-primary button" type="submit" value="<?php _e('Update Deposit' ,'booking'); ?>" name="submit_resource_deposit"/>
                                <div class="clear" style="height:1px;"></div>
                            </form>

                       </div> </div> </div>

            <?php
        }


        function show_settings_for_activating_fixed_deposit(){
            return;
            $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=cost';

            if ( isset( $_POST['paypal_price_period'] ) ) {
                if (isset($_POST['is_resource_deposit_payment_active'])) $is_resource_deposit_payment_active = 'On';
                else                                                     $is_resource_deposit_payment_active = 'Off';
                update_bk_option( 'booking_is_resource_deposit_payment_active' ,  $is_resource_deposit_payment_active );
            }
            $is_resource_deposit_payment_active   = get_bk_option( 'booking_is_resource_deposit_payment_active');


            ?>
               <tr valign="top" class="ver_premium_hotel">
                    <th scope="row"><?php _e('Deposit payment' ,'booking'); ?>:</th>
                    <td>
                        <fieldset>
                            <label for="is_resource_deposit_payment_active" >
                                <input <?php if ($is_resource_deposit_payment_active == 'On') echo "checked";/**/ ?>  value="<?php echo $is_resource_deposit_payment_active; ?>" name="is_resource_deposit_payment_active" id="is_resource_deposit_payment_active" type="checkbox" style="margin:-3px 3px 0 0;" />
                                <?php printf(__('Check this box if you want to use the %sdeposit%s total %spayment%s on the payment form, instead of the full total of the booking.' ,'booking'),'<strong>','</strong>','<strong>','</strong>');
                                ?><p class="description"><strong><?php _e('Note!' ,'booking');  ?></strong> <?php printf(__(' You can configure the deposit payment for your booking resources %shere%s.' ,'booking'), '<a href="'.$link.'">', '</a>');?></p>
                                </label>
                        </fieldset>
                    </td>
                </tr>


            <?php
        }


    //   B o o k i n g    r e s o u r c e     menu page from Settings booking menu ///////////////////////////////////////////////////

            // Get description of each season filter in human language
            function get_filter_description($filter){
                if ( is_serialized( $filter ) )  $filter = unserialize($filter);

                $result = ''; $description = '';
                $weekdays = array(__('Su' ,'booking'),__('Mo' ,'booking'),__('Tu' ,'booking'),__('We' ,'booking'),__('Th' ,'booking'),__('Fr' ,'booking'),__('Sa' ,'booking'));
                $monthes =  array(0,__('Jan' ,'booking'),__( 'Feb' ,'booking'),__( 'Mar' ,'booking'),__( 'Apr' ,'booking'),__('May' ,'booking'),__('Jun' ,'booking'),__( 'Jul' ,'booking'),__( 'Aug' ,'booking'),__( 'Sep' ,'booking'),__( 'Oct' ,'booking'),__( 'Nov' ,'booking'),__( 'Dec' ,'booking') );

                if ( (isset($filter['version'])) && ( $filter['version'] == '2.0' ) ){ // New filter
                    //'New filter version 2.0';

                    $last_day = '';
                    $last_day_id = '';
                    $last_show_day = '';
                    $short_days = array();
                    foreach ($filter as $key_year=>$value_months) {
                        if (is_numeric($key_year) ) {  // check only years - skip  the "version" and "name" key  fields here

                            foreach ($value_months as $value_month=>$value_dates) {

                                    foreach ($value_dates as $date_num=>$is_date) {

                                        if (($date_num>=1) && ($is_date=='1')) {
                                            if ($value_month < 9) $v_month = '0'.$value_month;
                                            else                  $v_month = $value_month;
                                            if ($date_num < 9)    $d_num = '0'.$date_num;
                                            else                  $d_num = $date_num;
                                            $dte = $key_year . '-' . $v_month . '-' . $d_num ;

                                                if (empty($last_day)) { // First date
                                                    $short_days[]= $dte;
                                                    $last_show_day = $dte;
                                                } else {                // All other days
                                                    if ( wpdevbk_is_next_day( $dte ,$last_day) ) {
                                                        if ($last_show_day != '-') { $short_days[]= '-'; }
                                                        $last_show_day = '-';
                                                    } else {
                                                        if ($last_show_day !=$last_day) { $short_days[]= $last_day;}
                                                        $short_days[]= ',';
                                                        $short_days[]= $dte;
                                                        $last_show_day = $dte;
                                                    }
                                                }
                                                $last_day = $dte;
                                        }
                                    }
                            }
                        }
                    }

                    if (isset($dte)) if($last_show_day != $dte) { $short_days[]= $dte; }

                    $short_dates_content = '';
                    foreach ($short_days as $dt) {
                        if ($dt == '-') {       $short_dates_content .= '<span class="date_tire1"> - </span>';
                        } elseif ($dt == ',') { $short_dates_content .= '<span class="date_tire1">, </span>';
                        } else {
                            $short_dates_content .= '<strong>';
                            $bk_date = wpdevbk_get_date_in_correct_format($dt);
                            $short_dates_content .= $bk_date[0];
                            $short_dates_content .= '</strong>';
                        }
                    }
                    $description = $short_dates_content;
                    if ( empty( $short_days ) )
                        $description = '<span style="color:#ff0000;font-weight:bold;">' . __('No days' ,'booking') . '</span>';

                } else if (  (! empty($filter['start_time']))  &&  (! empty($filter['start_time']))  ) { // Time availability
                     $description .= __('From' ,'booking') . ' <strong>' . $filter['start_time'] . '</strong> ' .  __('to' ,'booking') . ' <strong>' . $filter['end_time'] . '</strong> ' . __('time' ,'booking') ;
                } else {
                        //debuge($filter);
                        //Week days
                        $cnt = 0;
                        foreach ($filter['weekdays'] as $key => $value) {
                            if ($value == 'On') {
                                if ($result !=='') $result .=', ';
                                $result .= $weekdays[$key];
                                $cnt++;
                            }
                        }
                        if ( ($result !=='' )||($cnt == 0) ) {
                            if ($cnt ==7)
                                $description .= '';
                            elseif ($cnt == 0)
                                return '<span style="color:#ff0000;font-weight:bold;">' . __('No days' ,'booking') . '</span>';
                            else
                                $description .= __('Every' ,'booking') . ' <strong>' . $result . '</strong> ';
                        }


                        //Days
                        $cnt = 0;$result='';
                        foreach ($filter['days'] as $key => $value) {
                            if ($value == 'On') {
                                if ($result !=='') $result .=', ';
                                $result .=  $key ;
                                $cnt++;
                            }
                        }
                        if ( ($result !=='' )||($cnt == 0) ) {
                            if ($cnt ==31) {
                               if ($description == '')  $description .= __('Each day ' ,'booking').' ';
                               else                     $description .= __('on each day ' ,'booking').' ';
                            } elseif ($cnt == 0)
                                return '<span style="color:#ff0000;font-weight:bold;">' . __('No days' ,'booking') . '</span>';
                            else {
                               if ($description == '')  $description .= __('On each ' ,'booking'). ' <strong>' . $result . '</strong> ';
                               else                     $description .= __('on each ' ,'booking') . ' <strong>' . $result . '</strong> ';
                            }

                        }

                        //Monthes
                        $cnt = 0;$result='';
                        foreach ($filter['monthes'] as $key => $value) {
                            if ($value == 'On') {
                                if ($result !=='') $result .=', ';
                                $result .=  $monthes[$key]; ;
                                $cnt++;
                            }
                        }
                        if ( ($result !=='' )||($cnt == 0) ) {
                            if ($cnt ==12)
                                $description .= __('of every month ' ,'booking');
                            elseif ($cnt == 0)
                                return '<span style="color:#ff0000;font-weight:bold;">' . __('No days' ,'booking') . '</span>';
                            else
                                $description .= __('of' ,'booking') . ' <strong>' . $result . '</strong> ';
                        }


                        //Years
                        $cnt = 0;$result='';
                        foreach ($filter['year'] as $key => $value) {
                            if ($value == 'On') {
                                if ($result !=='') $result .=', ';
                                $result .=  $key ;
                                $cnt++;
                            }
                        }
                        if ( ($result !=='' )||($cnt == 0) ) {
                            if ($cnt == 0)
                                return '<span style="color:#ff0000;font-weight:bold;">' . __('No days' ,'booking') . '</span>';
                            else
                                $description .=   ' <strong>' . $result . '</strong>';
                        }
                }
                return $description;

            }



    //   S e a s o n     f i l t e r          menu page from Settings booking menu ///////////////////////////////////////////////////

    function form_for_range_days_filter($wpdev_edit_id){ global $wpdb;
        $start_year = date('Y') ;
        $is_show_week_days = true;                                         // Just a little different design of showing dates
        $is_visible_season_filter_form = false;

        // Saving or updating the filters //////////////////////////////////
        if (isset($_POST['filter_name_range_season_filter'])) {             //Only the form (verison 2.0) have this field, so we will save in current format

            $range_filer = array();
            for ($yy = ($start_year - 1) ; $yy < ($start_year+11); $yy++) { $range_filer[$yy] = array(1=>array(),2=>array(),3=>array(),4=>array(),5=>array(),6=>array(),7=>array(),8=>array(),9=>array(),10=>array(),11=>array(),12=>array()); }

            foreach ($_REQUEST as $key=>$value) {

                 if (strpos($key, '-') !== false ) {
                     $key= explode('-',$key);
                     if ( (isset($key[0])) && (isset($key[1])) && (isset($key[2])) )
                        $range_filer[ $key[0] ][ $key[1] ][ $key[2] ] = 1 ;
                 }

            }
            $range_filer['name'] = $_REQUEST['filter_name_range_season_filter'];
            $range_filer['version'] = '2.0';

            $ser_filter = serialize ($range_filer);
            $is_insert = false;
            if ($_POST['wpdev_edit_id']>0) {
                $sql = $wpdb->prepare( "UPDATE  {$wpdb->prefix}booking_seasons SET title = %s, filter = '{$ser_filter}' WHERE booking_filter_id = %d "
                        ,$_POST['filter_name_range_season_filter'], $_POST['wpdev_edit_id'] );
                $is_visible_season_filter_form = true;
            } else {
                $sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}booking_seasons ( title, filter ) VALUES ( %s, '{$ser_filter}')"
                        ,$_POST['filter_name_range_season_filter'] );
                $is_insert = true;
            }
            if ( false === $wpdb->query( $sql ) ){
               echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:bold;">'; bk_error('Error during updating to DB booking filters',__FILE__,__LINE__ ); echo  '</div>';
            }else {
                if ($is_insert) {
                    $newid = (int) $wpdb->insert_id;
                    make_bk_action('added_new_season_filter',$newid);
                }
                echo '<div class="updated ajax_message textleft" style="font-size:12px;font-weight:bold;">'.__('Filter saved' ,'booking').'</div>';
            }
            //JS for hide messages
            echo '<script type="text/javascript">jQuery(".warning_message").animate({opacity:1},10000).fadeOut(2000);jQuery(".error_message").animate({opacity:1},10000).fadeOut(2000);jQuery(".info_message").animate({opacity:1},5000).fadeOut(2000);</script>';
        }
        ////////////////////////////////////////////////////////////////////



        //Edit Filter - we are need to get  all info  from the filter for the editing.
        if (isset($_GET['wpdev_edit'])) {
            $wpdev_edit_id = $_GET['wpdev_edit'];
            $sql_list = $wpdb->get_results($wpdb->prepare( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d" , $wpdev_edit_id  ) );
            if (count($sql_list) == 0 ) {
                $wpdev_edit_id = 0;
             } else {
                 $filter_name = $sql_list[0]->title;
                 if ( is_serialized( $sql_list[0]->filter ) )  $my_edit_filter = unserialize($sql_list[0]->filter);
                 else                                          $my_edit_filter = $sql_list[0]->filter;

                 if ( (isset($my_edit_filter['version'])) && ($my_edit_filter['version'] == '2.0') ){ 
                    // Good this is new filter  2.0
                    $is_visible_season_filter_form = true;
                 } else {
                     // Its not the FILTER 2.0
                     $is_visible_season_filter_form = false;
                 }
             }
        }

        if ( (isset($_GET['filterdisplay'])) && ($_GET['filterdisplay']==2) )
            $is_visible_season_filter_form = true;

        if ($is_visible_season_filter_form !== true)  return;               // Exit if we are do not need to show Creat filter form.

        ?>
        <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Specific Dates Filter' ,'booking'); ?></span></h3> <div class="inside">
        <div class="range_season_filter_form" style="<?php if (! $is_visible_season_filter_form) echo 'display:none;'; ?>">
          <form class="form-inline" name="range_season_filter_form" action="" method="post">
            <input id="wpdev_edit_id"  type="hidden"   value="<?php echo $wpdev_edit_id; ?>" name="wpdev_edit_id"/>

            <div style="margin:0 5px 20px; height: auto;">                        
                <label for="filter_name_range_season_filter" class="wpbc_inline_legend"><?php echo __('Filter Name' ,'booking'); ?>: </label>
                <input type="text" id="filter_name_range_season_filter" name="filter_name_range_season_filter" 
                       value="<?php if(! empty($filter_name)) echo $filter_name; ?>"                           
                       placeholder="<?php _e('Type filter name' ,'booking'); ?>" 
                       class="wpbc_stick_right" style="float:left;margin:0;width: 180px;" />

                <a onclick="javascript:document.forms['range_season_filter_form'].submit();" 
                   class="tooltip_top button button-primary wpbc_stick_left" 
                   rel="tooltip" data-original-title="<?php _e('Create new season filter' ,'booking'); ?>"><?php 
                    if ($wpdev_edit_id ==0 ) 
                        _e('Create' ,'booking'); 
                    else  
                        _e('Save' ,'booking');  ?></a>

                <a onclick="javascript: jQuery('.drc_all').attr('checked', false);"
                   class="tooltip_top button button-secondary" 
                   ><?php _e('Reset' ,'booking'); ?></a>
            </div>      
            <div class="clear"></div>
        <?php
        // Show tabs selection - YEARS  ?>
        <div id="menu-wpdevplugin" style="width:auto;">
            <div class="nav-tabs-wrapper" style="height:1px;">
                <div class="nav-tabs">
                   <?php
                   $is_only_icons = false;
                   $my_icon='';
                   for ($yy = ($start_year - 1) ; $yy < ($start_year+11); $yy++) { ?>
                    <?php $title = $yy; $my_tab = 'days_range_container'.$yy ;  $my_additinal_class= ''; ?>
                    <?php if ($yy == $start_year) {  $slct_a = 'selected'; $selected_title = $title; $selected_icon = $my_icon; }
                          else {  $slct_a = ''; } ?><a class="nav-tab <?php if ($slct_a == 'selected') { echo ' nav-tab-active '; } echo $my_additinal_class; ?>" title="<?php echo $yy;  ?>" style="padding: 4px 14px;"
                             href="javascript:void(0)" onclick="javascript:jQuery('.visibility_container').hide(); jQuery('#<?php echo $my_tab; ?>').show();jQuery('.nav-tab').removeClass('nav-tab-active');jQuery(this).addClass('nav-tab-active');">
                              <?php $day_filter_id = $yy; ?>                                  
                                <span class="nav-tab-text-visible"><?php echo $title;  ?></span>
                                <input id="<?php echo $day_filter_id; ?>" class="drc_all filter_month" name="<?php echo $day_filter_id; ?>"
                                       <?php  ?>  value="<?php echo $day_filter_id; ?>"  type="checkbox"
                                       onclick="javascript:jQuery('.drc_year_<?php echo $yy;?>').attr('checked', this.checked);"
                                       />                                  
                          </a>
                   <?php } ?>
                </div>
            </div>
        </div>

        <?php // Show tabs selection - YEARS  ?>
        <div class="booking-submenu-tab-container" style="margin:0;padding:0;">
            <div class="nav-tabs booking-submenu-tab-insidecontainer">
                <?php $dwa = array(1=>__('Mo' ,'booking'),2=>__('Tu' ,'booking'),3=>__('We' ,'booking'),4=>__('Th' ,'booking'),5=>__('Fr' ,'booking'),6=>__('Sa' ,'booking'),7=>__('Su' ,'booking'),); ?>
                <?php for ($yy = ($start_year -1); $yy < ($start_year+11); $yy++) { ?>
                <div class="visibility_container active" id="days_range_container<?php echo $yy; ?>" style="<?php if ($selected_title ==  $yy ) { echo 'display:block;'; } else { echo 'display:none;'; }  ?>">


                    <table class="resource_table booking_table range_season_filter" cellpadding="0" cellspacing="0">
                        <?php for ($mm = 1; $mm < 13; $mm++) { ?>
                        <tr>

                            <td class="month_title"><?php $day_filter_id = $yy.'-'.$mm ; ?>
                                <label for="<?php echo $day_filter_id; ?>" >
                                    <input id="<?php echo $day_filter_id; ?>" class="drc_all filter_month <?php echo 'drc_year_'.$yy; ?>" name="<?php echo $day_filter_id; ?>"
                                                    <?php if ($day_filter_id == 1) echo "checked"; ?>  value="<?php echo $day_filter_id; ?>"  type="checkbox"
                                                    onclick="javascript:jQuery('.drc_year_month_<?php echo $yy.'-'.$mm;?>').attr('checked', this.checked);"
                                                    />
                                    <?php $full_month_title = date("F", mktime(0, 0, 0, $mm, 1, $yy));
                                    echo $full_month_title;?>
                                    <legend class="wpbc_mobile_legend clear"> <?php echo $yy; ?></legend>
                                </label>
                            </td>
                            <td>
                            <?php $day_num_previous = '00';
                            for ($dd = 1; $dd < 32; $dd++) { ?>                                    
                                    <?php
                                      $day_filter_id = $yy.'-'.$mm.'-'.$dd;
                                      $day_num = date("d", mktime(0, 0, 0, $mm, $dd, $yy));
                                      $day_week = date("N", mktime(0, 0, 0, $mm, $dd, $yy));

                                      $is_checked = '';
                                          if ( isset($my_edit_filter) ) {
                                             if (isset ($my_edit_filter[$yy]))
                                                   if (isset ($my_edit_filter[$yy][$mm]))
                                                       if (isset ($my_edit_filter[$yy][$mm][$dd]))
                                                           $is_checked =  "checked";
                                           }
                                      ?>
                               <div class="weekday_cell weekday<?php echo $day_week ?><?php echo  ' '.$is_checked.' ' ; ?>" >
                                    <?php  if ($day_num_previous < $day_num) {
                                          $day_num_previous = $day_num;
                                          ?><label for="<?php echo $day_filter_id; ?>"><div class="day_num"><?php echo $day_num;?></div>
                                            <div class="day_week"><?php //echo $dwa[$day_week];?>
                                          <input type="checkbox"
                                                 id="<?php echo $day_filter_id; ?>" name="<?php echo $day_filter_id; ?>"
                                                 class="drc_all filter_month <?php echo 'drc_year_'.$yy; echo ' drc_year_month_'.$yy.'-'.$mm; ?>"
                                                 <?php echo  ' '.$is_checked.' ' ; ?>
                                                 value="<?php echo $day_filter_id; ?>"
                                           /><br/>
                                           <?php if ($is_show_week_days) echo $dwa[$day_week];?></div></label>
                                     <?php } ?>
                                </div>
                            <?php } ?>
                                <div class="clear"></div>
                             </td>
                        </tr>
                        <?php } ?>
                    </table>

                </div>

                <?php } ?>
            </div>
        </div>


            <div class="clear" style="height:20px;"></div>
            <a onclick="javascript:document.forms['range_season_filter_form'].submit();" 
               class="tooltip_top button button-primary" 
               rel="tooltip" data-original-title="<?php _e('Create new season filter' ,'booking'); ?>"><?php  if ($wpdev_edit_id ==0 ) { _e('Create New Season Filter' ,'booking'); } else { _e('Save changes' ,'booking'); } ?></a>

        </form>
        </div>
        </div></div></div>
        <?php
    }


    function form_for_conditional_days_filter($wpdev_edit_id){  
        global $wpdb;
        $is_visible_season_filter_form = false;
        $start_year = date('Y') ;

        $filter_week_day  = array();
        $filter_month_day = array();
        $filter_month = array();
        $filter_year = array();
        $filter_start_time = '';
        $filter_end_time = '';
        $wpdev_edit_id = 0;

        //Add new filter 1.0
        if ( isset( $_POST['filter_name'] ) ) {
            $filter=array(); $filter['weekdays']=array();$filter['days']=array();$filter['monthes']=array();$filter['year']=array();

            // Time  ////////////////////////////////////////////////////////////////////////////////////////////////////
            if (isset ($_POST[ 'filter_start_time' ])) $filter_start_time = $_POST[ 'filter_start_time' ];
            $filter['start_time'] = $filter_start_time;
            if (isset ($_POST[ 'filter_end_time' ]))   $filter_end_time = $_POST[ 'filter_end_time' ];
            $filter['end_time'] = $filter_end_time;
            if ( (! empty($filter_start_time)) && (! empty($filter_end_time)) )   $globalswitcher = 'On';
            else                                                                  $globalswitcher = '';
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////

            //Weekdays
            for ($k = 0; $k < 7; $k++) {  // Days of week
               if (isset ($_POST[ 'filter_week_day' . $k ])) $filter_week_day[$k] = 'On';
               else                                          $filter_week_day[$k] = 'Off';
               if (! empty($globalswitcher)) $filter['weekdays'][$k] = $globalswitcher;               //Use Globalswitcher, when time is set, so all other have to be On
               else                          $filter['weekdays'][$k] = $filter_week_day[$k];
            }

            //Days
            for ($k = 1; $k < 32; $k++) {  // Days of month
               if (isset ($_POST[ 'filter_month_day' . $k ])) $filter_month_day[$k] = 'On';
               else                                           $filter_month_day[$k] = 'Off';
               if (! empty($globalswitcher)) $filter['days'][$k] = $globalswitcher;               //Use Globalswitcher, when time is set, so all other have to be On
               else                          $filter['days'][$k] = $filter_month_day[$k];
            }

            //Monthes
            for ($k = 1; $k < 13; $k++) {  // Days of month
               if (isset ($_POST[ 'filter_month' . $k ])) $filter_month[$k] = 'On';
               else                                       $filter_month[$k] = 'Off';
               if (! empty($globalswitcher)) $filter['monthes'][$k] = $globalswitcher;               //Use Globalswitcher, when time is set, so all other have to be On
               else                          $filter['monthes'][$k] = $filter_month[$k];
            }

            //Years
            for ($k = ($start_year-1); $k < ($start_year+11); $k++) {  // Days of month
               if (isset ($_POST[ 'filter_year' . $k ])) $filter_year[$k] = 'On';
               else                                      $filter_year[$k] = 'Off';
               if (! empty($globalswitcher)) $filter['year'][$k] = $globalswitcher;               //Use Globalswitcher, when time is set, so all other have to be On
               else                          $filter['year'][$k] = $filter_year[$k];
            }

            $ser_filter = serialize  ($filter);
            $is_insert = false;
            if ($_POST['wpdev_edit_id']>0)
                $sql = $wpdb->prepare( "UPDATE  {$wpdb->prefix}booking_seasons SET title = %s, filter = %s WHERE booking_filter_id = %d "
                        , $_POST['filter_name'], $ser_filter, $_POST['wpdev_edit_id'] );
            else {
                $sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}booking_seasons ( title, filter ) VALUES ( %s, %s )", $_POST['filter_name'], $ser_filter );
                $is_insert = true;
            }
            if ( false === $wpdb->query( $sql ) ){
               echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:bold;">'; bk_error('Error during updating to DB booking filters',__FILE__,__LINE__ ); echo  '</div>';
            }else {
                if ($is_insert) {
                    $newid = (int) $wpdb->insert_id;
                    make_bk_action('added_new_season_filter',$newid);
                }
                echo '<div class="updated ajax_message textleft" style="font-size:12px;font-weight:bold;">'.__('Filter saved' ,'booking').'</div>';
            }

            //JS for hide messages
            echo '<script type="text/javascript">jQuery(".warning_message").animate({opacity:1},10000).fadeOut(2000);jQuery(".error_message").animate({opacity:1},10000).fadeOut(2000);jQuery(".info_message").animate({opacity:1},5000).fadeOut(2000);</script>';
        }


        //Edit Filter
        if (isset($_GET['wpdev_edit'])) {
            $wpdev_edit_id = $_GET['wpdev_edit'];
            $sql_list = $wpdb->get_results( $wpdb->prepare( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d" ,  $wpdev_edit_id  ) );
            if (count($sql_list) == 0 ) {
                $wpdev_edit_id = 0;
             } else {
                 $filter_name = $sql_list[0]->title;
                 if ( is_serialized( $sql_list[0]->filter ) )  $my_edit_filter = unserialize($sql_list[0]->filter);
                 else                                          $my_edit_filter = $sql_list[0]->filter;

                 if ( (isset($my_edit_filter['version'])) && ($my_edit_filter['version'] == '2.0') ){ // Good this is new filter
                          // Its filter 2.0, so  neeed to  hide it
                    $is_visible_season_filter_form = false;
                 } else { // Its not the FILTER 2.0
                    $is_visible_season_filter_form = true;
                 }
                 if ($is_visible_season_filter_form) {
                    $filter_week_day  = $my_edit_filter['weekdays'];
                    $filter_month_day = $my_edit_filter['days'];
                    $filter_month     = $my_edit_filter['monthes'];
                    $filter_year      = $my_edit_filter['year'];
                    if (isset($my_edit_filter['start_time'])) $filter_start_time = $my_edit_filter['start_time'];
                    if (isset($my_edit_filter['end_time']))   $filter_end_time   = $my_edit_filter['end_time'];
                 }
             }
        }

        if ( (isset($_GET['filterdisplay'])) && ($_GET['filterdisplay']==1) ) {
            $is_visible_season_filter_form = true;
        }

        if ($is_visible_season_filter_form !== true)  
            return;
        
                
        ?>
        <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Conditional Dates Filter' ,'booking'); ?></span></h3> <div class="inside">
        <form  name="post_option_cost" action="" method="post" id="post_option_filter" >
            <input id="wpdev_edit_id"  type="hidden"   value="<?php echo $wpdev_edit_id; ?>" name="wpdev_edit_id"/>                
            <div id="new_filter_div">

            <div style="margin:0 5px 20px; height: auto;">                        
                <label for="filter_name" class="wpbc_inline_legend"><?php echo __('Filter Name' ,'booking'); ?>: </label>
                <input type="text" id="filter_name" name="filter_name" 
                       value="<?php if(! empty($filter_name)) echo $filter_name; ?>"                           
                       placeholder="<?php _e('Type filter name' ,'booking'); ?>" 
                       class="wpbc_stick_right" style="float:left;margin:0;width: 180px;" />

                <a onclick="javascript:document.forms['post_option_cost'].submit();" 
                   class="tooltip_top button button-primary wpbc_stick_left" 
                   rel="tooltip" data-original-title="<?php _e('Create new season filter' ,'booking'); ?>"><?php 
                    if ($wpdev_edit_id ==0 ) 
                        _e('Create' ,'booking'); 
                    else  
                        _e('Save' ,'booking');  ?></a>

                <a onclick="javascript: jQuery('.filter_week').attr('checked', false);jQuery('.filter_month_day').attr('checked', false);jQuery('.filter_month').attr('checked', false);jQuery('.filter_year').attr('checked', false);"
                   class="tooltip_top button button-secondary" 
                   ><?php _e('Reset' ,'booking'); ?></a>
            </div>      

             <div class="clear"></div>

                <div id="days_filters">

                    <div class="filter_div">
                        <div class="filter_div_title">
                            <fieldset>
                                <label for="filter_week_day_all" >
                                    <input id="filter_week_day_all" name="filter_week_day_all" type="checkbox" onclick="javascript:setCheckBoxInTable(this.checked, 'filter_week');" />
                                    <?php _e('Days of week' ,'booking');?>
                                </label>
                            </fieldset>
                        </div>
                        <div class="filter_inner">
                            <fieldset>
                                <label for="filter_week_day0" >
                                    <input id="filter_week_day0" class="filter_week" name="filter_week_day0" <?php for( $nnn = 0; $nnn < 7; $nnn++ ){ if (! isset($filter_week_day[$nnn])) {$filter_week_day[$nnn]='Off';} }  if ($filter_week_day[0] == 'On') echo "checked"; ?> value="<?php echo $filter_week_day[0]; ?>"  type="checkbox" />
                                    <?php _e('Sunday' ,'booking');?>
                                </label>
                            </fieldset>
                            <fieldset>
                                <label for="filter_week_day1" >
                                    <input id="filter_week_day1" class="filter_week" name="filter_week_day1" <?php if ($filter_week_day[1] == 'On') echo "checked"; ?>  value="<?php echo $filter_week_day[1]; ?>"  type="checkbox" />
                                    <?php _e('Monday' ,'booking');?>
                                </label>
                            </fieldset>
                            <fieldset>
                                <label for="filter_week_day2" >
                                    <input id="filter_week_day2" class="filter_week" name="filter_week_day2" <?php if ($filter_week_day[2] == 'On') echo "checked"; ?>  value="<?php echo $filter_week_day[2]; ?>"  type="checkbox" />
                                    <?php _e('Tuesday' ,'booking');?>
                                </label>
                            </fieldset>
                            <fieldset>
                                <label for="filter_week_day3" >
                                    <input id="filter_week_day3" class="filter_week" name="filter_week_day3" <?php if ($filter_week_day[3] == 'On') echo "checked"; ?>  value="<?php echo $filter_week_day[3]; ?>"  type="checkbox" />
                                    <?php _e('Wednesday' ,'booking');?>
                                </label>
                            </fieldset>
                            <fieldset>
                                <label for="filter_week_day4" >
                                    <input id="filter_week_day4" class="filter_week" name="filter_week_day4" <?php if ($filter_week_day[4] == 'On') echo "checked"; ?>  value="<?php echo $filter_week_day[4]; ?>"  type="checkbox" />
                                    <?php _e('Thursday' ,'booking');?>
                                </label>
                            </fieldset>
                            <fieldset>
                                <label for="filter_week_day5" >
                                    <input id="filter_week_day5" class="filter_week" name="filter_week_day5" <?php if ($filter_week_day[5] == 'On') echo "checked"; ?>  value="<?php echo $filter_week_day[5]; ?>"  type="checkbox" />
                                    <?php _e('Friday' ,'booking');?>
                                </label>
                            </fieldset>
                            <fieldset>
                                <label for="filter_week_day6" >
                                    <input id="filter_week_day6" class="filter_week" name="filter_week_day6" <?php if ($filter_week_day[6] == 'On') echo "checked"; ?>  value="<?php echo $filter_week_day[6]; ?>"  type="checkbox" />
                                    <?php _e('Saturday' ,'booking');?>
                                </label>
                            </fieldset>
                        </div>
                    </div>

                    <?php  for($nnn=1;$nnn<32;$nnn++){ if (! isset($filter_month_day[$nnn])) {$filter_month_day[$nnn]='Off';} }  ?>
                    <div class="filter_div">
                        <div class="filter_div_title">
                            <fieldset>
                                <label for="filter_month_day_all" >
                                    <input id="filter_month_day_all" name="filter_month_day_all" type="checkbox" onclick="javascript:setCheckBoxInTable(this.checked, 'filter_month_day');" />
                                    <?php _e('Days of month' ,'booking');?>
                                </label>
                            </fieldset>
                        </div>

                        <div class="filter_inner">
                            <div style="float:left;margin-right:10px;">
                                <?php for ($sf = 1; $sf < 8; $sf++) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month_day<?php echo $sf; ?>" >
                                            <input id="filter_month_day<?php echo $sf; ?>" name="filter_month_day<?php echo $sf; ?>"  
                                                   class="filter_month_day" type="checkbox" 
                                                   <?php if ($filter_month_day[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month_day[$sf]; ?>" />
                                            <?php echo $sf; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                            </div>
                            <div style="float:left;margin-right:10px;">
                                <?php for ($sf = 8; $sf < 15; $sf++) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month_day<?php echo $sf; ?>" >
                                            <input id="filter_month_day<?php echo $sf; ?>" name="filter_month_day<?php echo $sf; ?>"  
                                                   class="filter_month_day" type="checkbox" 
                                                   <?php if ($filter_month_day[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month_day[$sf]; ?>" />
                                            <?php echo $sf; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                            </div>
                            <div style="float:left;margin-right:10px;">
                                <?php for ($sf = 15; $sf < 22; $sf++) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month_day<?php echo $sf; ?>" >
                                            <input id="filter_month_day<?php echo $sf; ?>" name="filter_month_day<?php echo $sf; ?>"  
                                                   class="filter_month_day" type="checkbox" 
                                                   <?php if ($filter_month_day[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month_day[$sf]; ?>" />
                                            <?php echo $sf; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                             </div>
                            <div style="float:left;margin-right:10px;">
                                <?php for ($sf = 22; $sf < 29; $sf++) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month_day<?php echo $sf; ?>" >
                                            <input id="filter_month_day<?php echo $sf; ?>" name="filter_month_day<?php echo $sf; ?>"  
                                                   class="filter_month_day" type="checkbox" 
                                                   <?php if ($filter_month_day[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month_day[$sf]; ?>" />
                                            <?php echo $sf; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                             </div>
                             <div style="float:left;margin-right:10px;">
                                <?php for ($sf = 29; $sf < 32; $sf++) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month_day<?php echo $sf; ?>" >
                                            <input id="filter_month_day<?php echo $sf; ?>" name="filter_month_day<?php echo $sf; ?>"  
                                                   class="filter_month_day" type="checkbox" 
                                                   <?php if ($filter_month_day[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month_day[$sf]; ?>" />
                                            <?php echo $sf; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                             </div>
                        </div>
                    </div>

                    <?php for($nnn=1;$nnn<13;$nnn++){ if (! isset($filter_month[$nnn])) {$filter_month[$nnn]='Off';}}  ?>
                    <div class="filter_div">
                        <div class="filter_div_title">
                            <fieldset>
                                <label for="filter_month_all" >
                                    <input id="filter_month_all" name="filter_month_all" type="checkbox" onclick="javascript:setCheckBoxInTable(this.checked, 'filter_month');" />
                                    <?php _e('Months' ,'booking');?>
                                </label>
                            </fieldset>
                        </div>

                        <?php 
                        $season_filter_months = array (
                          1 =>  __('January' ,'booking'), 
                          2 =>  __('February' ,'booking'), 
                          3 =>  __('March' ,'booking'), 
                          4 =>  __('April' ,'booking'), 
                          5 =>  __('May' ,'booking'), 
                          6 =>  __('June' ,'booking'), 
                        );
                        ?>
                        <div class="filter_inner">
                            <div style="float:left;margin-right:10px;">                                    
                                <?php foreach ($season_filter_months as $sf => $value_name) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month<?php echo $sf; ?>" >
                                            <input class="filter_month" type="checkbox" 
                                                   id="filter_month<?php echo $sf; ?>" name="filter_month<?php echo $sf; ?>" 
                                                   <?php if ($filter_month[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month[$sf]; ?>" />
                                            <?php echo $value_name; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                            </div>
                            <?php 
                            $season_filter_months = array (
                                7 =>  __('July' ,'booking'), 
                                8 =>  __('August' ,'booking'), 
                                9 =>  __('September' ,'booking'), 
                                10 =>  __('October' ,'booking'), 
                                11 =>  __('November' ,'booking'), 
                                12 =>  __('December' ,'booking'), 
                            );
                            ?>
                            <div style="float:left;margin-right:10px;">
                                <?php foreach ($season_filter_months as $sf => $value_name) { ?>                                         
                                    <fieldset>
                                        <label for="filter_month<?php echo $sf; ?>" >
                                            <input class="filter_month" type="checkbox" 
                                                   id="filter_month<?php echo $sf; ?>" name="filter_month<?php echo $sf; ?>" 
                                                   <?php if ($filter_month[$sf] == 'On') echo ' checked="CHECKED" '; ?>  
                                                   value="<?php echo $filter_month[$sf]; ?>" />
                                            <?php echo $value_name; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                            </div>
                        </div>
                    </div>

                    <div class="filter_div">
                        <div class="filter_div_title">
                            <fieldset>
                                <label for="filter_year_all" >
                                    <input id="filter_year_all" name="filter_year_all" type="checkbox" onclick="javascript:setCheckBoxInTable(this.checked, 'filter_year');" />
                                    <?php _e('Years' ,'booking');?>
                                </label>
                            </fieldset>
                        </div>

                        <div class="filter_inner">
                            <div style="float:left;margin-right:10px;">
                                <?php  
                                for($nnn=($start_year -1);$nnn<($start_year +11);$nnn++){ if (! isset($filter_year[$nnn])) {$filter_year[$nnn]='Off';}}
                                for ($yy = ($start_year -1) ; $yy < ($start_year+5); $yy++) { ?>                                    
                                    <fieldset>
                                        <label for="filter_year<?php echo $yy ; ?>" >
                                            <input  class="filter_year" type="checkbox"
                                                    id="filter_year<?php echo $yy ; ?>" name="filter_year<?php echo $yy ; ?>" 
                                                    <?php if ($filter_year[$yy] == 'On') echo ' checked="CHECKED" '; ?> 
                                                    value="<?php echo $filter_year[$yy]; ?>"  />
                                            <?php echo $yy; ?>
                                        </label>
                                    </fieldset>                                    
                                 <?php } ?>
                            </div>
                            <div style="float:left;margin-right:10px;">
                                <?php
                                for ($yy = ($start_year +5) ; $yy < ($start_year+11); $yy++) { ?>
                                    <fieldset>
                                        <label for="filter_year<?php echo $yy ; ?>" >
                                            <input  class="filter_year" type="checkbox"
                                                    id="filter_year<?php echo $yy ; ?>" name="filter_year<?php echo $yy ; ?>" 
                                                    <?php if ($filter_year[$yy] == 'On') echo ' checked="CHECKED" '; ?> 
                                                    value="<?php echo $filter_year[$yy]; ?>"  />
                                            <?php echo $yy; ?>
                                        </label>
                                    </fieldset>                                    
                                <?php } ?>
                            </div>
                        </div>
                    </div>


                </div>
                <div class="clear" style="height:10px;"></div>
                <a onclick="javascript:document.forms['post_option_cost'].submit();" 
                   class="tooltip_top button button-primary" 
                   rel="tooltip" data-original-title="<?php _e('Create new season filter' ,'booking'); ?>"><?php  if ($wpdev_edit_id ==0 ) { _e('Create New Season Filter' ,'booking'); } else { _e('Save changes' ,'booking'); } ?></a>

            </div>
            <div class="clear"></div>

        </form>
        </div></div></div>
        <?php
    }


    function show_booking_date_filter(){ global $wpdb;

        if (isset($_GET['wpdev_edit']))
            $wpdev_edit_id = $_GET['wpdev_edit'];
        else 
            $wpdev_edit_id = 0;

        // Delete filter
        if (isset($_GET['wpdev_delete'])) {
            $delete_id = $_GET['wpdev_delete'];
            $sql_list = $wpdb->get_results( $wpdb->prepare( "SELECT count(booking_filter_id) as count FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d " , $delete_id ) );
            if ($sql_list[0]->count > 0 ) {
                $sql = $wpdb->prepare( "DELETE FROM {$wpdb->prefix}booking_seasons WHERE booking_filter_id = %d ",  $delete_id ) ;
                if ( false === $wpdb->query( $sql ) ){
                   echo '<div class="error_message ajax_message textleft" style="font-size:12px;font-weight:bold;">';
                    bk_error('Error during deleting from DB booking filters' ,__FILE__,__LINE__); echo   '</div>';
                }else echo '<div class="updated ajax_message textleft" style="font-size:12px;font-weight:bold;">'.__('Filter deleted successfully' ,'booking').'</div>';
            }
            echo '<script type="text/javascript">jQuery(".warning_message").animate({opacity:1},10000).fadeOut(2000);jQuery(".error_message").animate({opacity:1},10000).fadeOut(2000);jQuery(".info_message").animate({opacity:1},5000).fadeOut(2000);</script>';
        }
        ?>
        <div class="create-filter-buttons-section" style="height:40px;">
            <a href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME; ?>wpdev-booking-resources&tab=filter&filterdisplay=2"
               style="float: left;  margin:1px 15px 15px 0;font-weight:700;"
               class="button button-secondary"><?php _e('Create dates filter' ,'booking');?></a>
            <a href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME; ?>wpdev-booking-resources&tab=filter&filterdisplay=1"
               style="float: left; margin:1px 15px 15px 0;font-weight:700;"
               class="button button-secondary"><?php _e('Create conditional days filter' ,'booking');?></a>
        </div>
        <div class="clear"></div><?php

        // Show Forms for the updating and creating new Season Filters
        $this->form_for_range_days_filter($wpdev_edit_id);
        $this->form_for_conditional_days_filter($wpdev_edit_id);

        ?>
        <div class='meta-box'>  <div  class="postbox" > <h3 class='hndle'><span><?php _e('Listing of season filters' ,'booking'); ?></span></h3> <div class="inside">


            <table class="booking_table resource_table wpbc_seasonfilters_table" cellpadding="0" cellspacing="0">
                <tr>
                    <th class="wpbc_column_1"><?php _e('ID' ,'booking') ?></th>
                    <th class="wpbc_column_2"><?php _e('Season Filter Name' ,'booking') ?></th>
                    <th class="wpbc_column_3"><?php _e('Filters' ,'booking') ?></th>
                    <th class="wpbc_column_4"><?php _e('Actions' ,'booking') ?></th>
                </tr>
                <?php
                  $td_class = '';
                  $where = '';
                  $where = apply_bk_filter('multiuser_modify_SQL_for_current_user', $where);
                  if ($where != '') $where = ' WHERE ' . $where;

                  $filter_list = $wpdb->get_results( "SELECT booking_filter_id as id, title, filter FROM {$wpdb->prefix}booking_seasons  ".$where." ORDER BY booking_filter_id DESC" );
                  $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=filter';
                  foreach ($filter_list as $value) {

                    if ( $td_class == '') $td_class = ' alternative_color ';
                    else                  $td_class = '';                          
                    if ( $wpdev_edit_id == $value->id ) 
                        $td_class = ' edited_row_now ';
                  ?>
                      <tr>
                        <td class="wpbc_column_1 <?php echo $td_class; ?>"><span style="font-size:10px;font-weight:bold;"><legend class="wpbc_mobile_legend"><?php _e('ID' ,'booking'); ?>: </legend><?php echo $value->id; ?></span></td>
                        <td class="wpbc_column_2 <?php echo $td_class; ?>"><legend class="wpbc_mobile_legend"><?php _e('Season Filter Name' ,'booking'); ?>: </legend><?php echo $value->title; ?></td>
                        <td class="wpbc_column_3 <?php echo $td_class; ?>"><legend class="wpbc_mobile_legend"><?php _e('Description' ,'booking'); ?>: </legend><?php echo $this->get_filter_description($value->filter); ?></span></td>
                        <td  class="wpbc_column_4 <?php echo $td_class; ?>" style="text-align:center;">                                
                           <a href="<?php echo $link.'&wpdev_edit=' . $value->id ; ?>"  
                              title="<?php  _e('Edit' ,'booking'); ?>" 
                              class="button button-secondary"
                              ><?php  _e('Edit' ,'booking'); ?></a>
                           <a href="javascript:void(0)"  
                              title="<?php  _e('Delete' ,'booking'); ?>"
                              onclick="javascript: var answer = confirm('<?php _e("Do you really want to delete?" ,'booking'); ?>'); if (! answer){ return false; } else {location.href='<?php echo $link.'&wpdev_delete=' . $value->id ; ?>';return false;}"
                              class="button button-secondary"
                              ><?php  _e('Delete' ,'booking'); ?></a>
                       </td>
                      </tr>
                  <?php   
                  }
         ?></table>


        </div></div></div><?php
    }



    // B L O C K s ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // S e t t i n g s /////////////////////////////////////////////////////

    

    function settings_calendar_unavailable_days() {
        if (isset($_POST['booking_available_days_num_from_today'])) {
             update_bk_option( 'booking_available_days_num_from_today' ,  $_POST['booking_available_days_num_from_today'] );
        }
        $booking_available_days_num_from_today = get_bk_option( 'booking_available_days_num_from_today');
        ?>
        <tr valign="top">
            <th scope="row"><label for="booking_available_days_num_from_today" ><?php _e('Limit available days from today' ,'booking'); ?>:</label></th>
            <td>
                <select id="booking_available_days_num_from_today" name="booking_available_days_num_from_today">
                    <option <?php if( empty( $booking_available_days_num_from_today ) ) echo "selected"; ?> value=""><?php echo ' - '; ?></option>
                    <?php                                  
                    $days_array = range( 365, 1, 1);                    
                    foreach ( $days_array as $i ) { 
                        ?>                    
                        <option <?php if( $booking_available_days_num_from_today == $i ) echo "selected"; ?> value="<?php echo $i; ?>"><?php echo $i; ?></option>
                    <?php } ?>
                </select>
                <span class="description"><?php _e('Select number of available days in calendar start from today.' ,'booking');?></span>
            </td>
        </tr>         
        <?php
    }
    
    
    // Settings for selecting default booking resource
    function settings_set_show_cost_in_tooltips(){
            if (isset($_POST['booking_highlight_cost_word'])) {
                 if (isset( $_POST['booking_is_show_cost_in_tooltips'] ))     $booking_is_show_cost_in_tooltips = 'On';
                 else                                                         $booking_is_show_cost_in_tooltips = 'Off';
                 update_bk_option( 'booking_is_show_cost_in_tooltips' ,  $booking_is_show_cost_in_tooltips );
                 update_bk_option( 'booking_highlight_cost_word' ,  $_POST['booking_highlight_cost_word'] );
                 
                 
                 update_bk_option( 'booking_is_show_cost_in_date_cell' , ( ( isset( $_POST['booking_is_show_cost_in_date_cell'] ) ) ? 'On' : 'Off' )  );
                 update_bk_option( 'booking_cost_in_date_cell_currency' ,  ($_POST['booking_cost_in_date_cell_currency']) );
            }
            $booking_is_show_cost_in_tooltips   = get_bk_option( 'booking_is_show_cost_in_tooltips');
            $booking_highlight_cost_word        = get_bk_option( 'booking_highlight_cost_word');
                        
            $booking_is_show_cost_in_date_cell   = get_bk_option( 'booking_is_show_cost_in_date_cell');
            $booking_cost_in_date_cell_currency  = get_bk_option( 'booking_cost_in_date_cell_currency');            
         ?>
        
        
               <tr valign="top" class="ver_premium_plus">
                    <th scope="row">
                        <?php $show_untill_version_update = '5.4';  $wpbc_settings_element = 'dismiss_new_booking_is_show_cost_in_date_cell'; if ( ( version_compare(WP_BK_VERSION_NUM, $show_untill_version_update ) < 0 ) && ( '1' != get_user_option( 'booking_win_' . $wpbc_settings_element ) ) ) { ?><div id="<?php echo $wpbc_settings_element; ?>"  class="new-label clearfix-height new-label-settings"><a class="tooltip_bottom" data-original-title="<?php _e('Hide' ,'booking'); ?>" rel="tooltip" href="javascript:void(0)"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $wpbc_settings_element; ?>');jQuery('#<?php echo $wpbc_settings_element; ?>').hide();" ><img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/label_new_blue.png" style="width:24px; height:24px;"></a></div><?php }  ?>                         
                        <?php _e('Showing cost in date cell' ,'booking'); ?>:
                    </th>
                    <td>
                        <label for="booking_is_show_cost_in_date_cell">
                        <input <?php if ($booking_is_show_cost_in_date_cell == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_show_cost_in_date_cell; ?>" name="booking_is_show_cost_in_date_cell" id="booking_is_show_cost_in_date_cell" type="checkbox"
                             onclick="javascript: if (this.checked) { 
                                 jQuery('#togle_settings_cost_in_date_cell').slideDown('normal'); 
                                 jQuery('#booking_is_show_cost_in_tooltips').attr('checked', false); 
                                 jQuery('#togle_settings_cost_day_show').slideUp('normal');
                             } else  jQuery('#togle_settings_cost_in_date_cell').slideUp('normal');"
                                                                                                          />
                        <?php printf(__(' Check this box to display the %sdaily cost at the date cells%s in the calendar(s).' ,'booking'),'<b>','</b>');?></label>
                    </td>
                </tr>

                <tr valign="top" class="ver_premium_plus"><td colspan="2" style="padding:0px;">
                    <div style="margin: -10px 0 10px 50px;">    
                    <table id="togle_settings_cost_in_date_cell" style="width:100%;<?php if ($booking_is_show_cost_in_date_cell != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                        <tr>
                        <th scope="row"><label for="booking_cost_in_date_cell_currency" ><?php _e('Currency symbol' ,'booking'); ?>:</label></th>
                            <td>
                    <fieldset>
                    <?php   $currency_formats =  array( '&#36;', '&#8364;', '&#163;', '&#165;' ) ;
                            $is_custom_format = true;
                            
                            foreach ( $currency_formats as $format ) {
                                
                                ?><label title='<?php echo esc_attr($format); ?>'>
                                    <input type='radio' name='booking_cost_in_date_cell_currency_selection' 
                                           value='<?php echo esc_attr($format); ?>' 
                                           onchange="javascript:if(this.checked) jQuery('#booking_cost_in_date_cell_currency').val( jQuery(this).val().replace(/[\u00A0-\u9999<>\&]/gim, function(i) {
   return '&#'+i.charCodeAt(0)+';';
}) );"
                                        <?php 
                                            if ( $booking_cost_in_date_cell_currency === $format ) {  
                                                echo " checked='checked'"; $is_custom_format = false;                                                 
                                            } 
                                        ?> /><?php echo $format; ?></label>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php
                            }
                            ?><label>
                                <input type="radio" value="custom" 
                                    name="booking_cost_in_date_cell_currency_selection" id="booking_cost_in_date_cell_currency_selection_custom" 
                                    <?php if ( $is_custom_format )  echo ' checked="checked"'; ?>
                                /><?php _e('Custom' ,'booking'); ?>:</label>&nbsp;                            
                              <input value="<?php echo htmlentities($booking_cost_in_date_cell_currency); ?>" type="text"  size="10"  
                                       name="booking_cost_in_date_cell_currency" id="booking_cost_in_date_cell_currency"                                         
                                       onchange="javascript: document.getElementById('booking_cost_in_date_cell_currency_selection_custom').checked = true;"
                              />
                              <p class="description"><?php printf(__('Type your %scurrency symbol%s to display near daily cost in date cells. %sDocumentation on currency symbols%s' ,'booking'),'<b>','</b>','<a href="http://dev.w3.org/html5/html-author/charref" target="_blank">','</a>');?></p> 
                    </fieldset>
                                
                                
                            </td>
                        </tr>
                    </table>
                    </div>
                </td></tr>        
        
               <tr valign="top" class="ver_premium_plus">
                    <th scope="row">
                        <?php _e('Showing cost in tooltip' ,'booking'); ?>:
                    </th>
                    <td>
                        <label for="booking_is_show_cost_in_tooltips">
                        <input <?php if ($booking_is_show_cost_in_tooltips == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_is_show_cost_in_tooltips; ?>" name="booking_is_show_cost_in_tooltips" id="booking_is_show_cost_in_tooltips" type="checkbox"
                             onclick="javascript: if (this.checked) { 
                                jQuery('#togle_settings_cost_day_show').slideDown('normal');
                                jQuery('#booking_is_show_cost_in_date_cell').attr('checked', false); 
                                jQuery('#togle_settings_cost_in_date_cell').slideUp('normal');                             
                         } else  jQuery('#togle_settings_cost_day_show').slideUp('normal');"
                                                                                                          />
                        <?php _e(' Check this box to display the daily cost with a tooltip when mouse hovers over each day on the calendar(s).' ,'booking');?></label>
                    </td>
                </tr>

                <tr valign="top" class="ver_premium_plus"><td colspan="2" style="padding:0px;">
                    <div style="margin: -10px 0 10px 50px;">    
                    <table id="togle_settings_cost_day_show" style="width:100%;<?php if ($booking_is_show_cost_in_tooltips != 'On') echo "display:none;";/**/ ?>" class="hided_settings_table">
                        <tr>
                        <th scope="row"><label for="booking_highlight_cost_word" ><?php _e('Cost Title' ,'booking'); ?>:</label></th>
                            <td><input value="<?php echo $booking_highlight_cost_word; ?>" name="booking_highlight_cost_word" id="booking_highlight_cost_word"  type="text"    />
                                <p class="description"><?php printf(__('Type your %scost%s description' ,'booking'),'<b>','</b>');?></p>                                    
                            </td>
                        </tr>
                    </table>
                    </div>
                </td></tr>
        <?php
    }




    // Get fields from booking form at the settings page or return false if no fields
    function get_fields_from_booking_form( $booking_form_content = '' ){
        if ( empty($booking_form_content) )
            $booking_form  = get_bk_option( 'booking_form' );
        else
            $booking_form = $booking_form_content;
        $types = 'text[*]?|email[*]?|time[*]?|textarea[*]?|select[*]?|checkbox[*]?|radio|acceptance|captchac|captchar|file[*]?|quiz';
        $regex = '%\[\s*(' . $types . ')(\s+[a-zA-Z][0-9a-zA-Z:._-]*)([-0-9a-zA-Z:#_/|\s]*)?((?:\s*(?:"[^"]*"|\'[^\']*\'))*)?\s*\]%';
        $fields_count = preg_match_all($regex, $booking_form, $fields_matches) ;

        if ($fields_count>0) return array($fields_count, $fields_matches);
        else return false;
    }

    // Show advanced cost managemnt block at paypal/cost Settings page
    function advanced_cost_management_settings(){                               //FixIn: 6.0.1

        if ( isset( $_POST['checking_form_submit'] ) ) {
//debuge($_POST);
            $booking_forms_extended = get_bk_option( 'booking_forms_extended');
            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) 
                    $booking_forms_extended = unserialize($booking_forms_extended);                        
                $booking_forms_extended[]=array('name'=>'standard','form'=>'');
            } else 
                $booking_forms_extended = array(array('name'=>'standard','form'=>''));

            foreach ($booking_forms_extended as $bk_form_ext) {

                
                    if ( $_POST['advanced_form_submit_name'] !== $bk_form_ext['name'] )
                        continue;
                
                    $booking_update_fields = $this->get_fields_from_booking_form($bk_form_ext['form']);

                    if ($bk_form_ext['name'] == 'standard') {
                        $field_sufix = '';
                    } else {
                        $field_sufix = $bk_form_ext['name'];
                        $field_sufix = '__' . $field_sufix;
                    }
                    $field_sufix = str_replace(' ', '', $field_sufix); $field_sufix = str_replace('"', '', $field_sufix); $field_sufix = str_replace("'" , '', $field_sufix);
//debuge($field_sufix);
                    if ($booking_update_fields !== false) {

                        $booking_additional_cost_value = array();   // General main variable for serilise and save it

                        for ($i = 0; $i < $booking_update_fields[0]; $i++) {

                            if ( ($booking_update_fields[1][1][$i] == 'checkbox') || ($booking_update_fields[1][1][$i] == 'checkbox*') || ($booking_update_fields[1][1][$i] == 'select') || ($booking_update_fields[1][1][$i] == 'select*') ){ // Right now working only with select boxes

                                $field_update_name = trim($booking_update_fields[1][2][$i]);
                                $field_update_values = trim($booking_update_fields[1][4][$i]);

                                if ( ! isset($booking_additional_cost_value[$field_update_name]) )
                                    $booking_additional_cost_value[$field_update_name] = array();

                                $fields_update_count_values = preg_match_all( '%\s*"[a-zA-Z0-9.:\s,\[\]/\\-_!@&-=+?~]{0,}"\s*%', $field_update_values, $fields_update_matches_values) ;
//debuge('$fields_update_matches_values',$fields_update_count_values);
//debuge('------- $fields_update_matches_values',$fields_update_matches_values);
                                for ($j = 0; $j < $fields_update_count_values; $j++) {
                                    $field_update_orig_value = trim(str_replace('"','',$fields_update_matches_values[0][$j]));
                                    $field_update_orig_value = explode('@@',$field_update_orig_value);                                      
                                    $field_update_orig_value = $field_update_orig_value[ ( count($field_update_orig_value) - 1 ) ];                                            
                                    $field_update_orig_value = trim(str_replace(' ','_',$field_update_orig_value));
//debuge('$field_update_orig_value',$field_update_orig_value);
                                    if ($field_update_orig_value == '') // Its simple checkbox set 0 index
                                        $booking_additional_cost_value[ $field_update_name ]['checkbox'] = $_POST['additional_cost_value_' . $field_update_name . $field_update_orig_value . $field_sufix ] ;
                                    else
                                        $booking_additional_cost_value[ $field_update_name ][ $field_update_orig_value ] = $_POST['additional_cost_value_' . $field_update_name . $field_update_orig_value . $field_sufix ] ;
//debuge('$booking_additional_cost_value', $booking_additional_cost_value);                                            
                                }
                            }
                        }
//debuge('Its fin:','booking_advanced_costs_values_for' . $bk_form_ext['name'],$booking_additional_cost_value);
                        if ($bk_form_ext['name'] == 'standard') {
                            update_bk_option( 'booking_advanced_costs_values'   , serialize($booking_additional_cost_value) );
                        }else {
                            update_bk_option( 'booking_advanced_costs_values_for' . $bk_form_ext['name']  , serialize($booking_additional_cost_value) );
                        }
                    }


            } // End FOR

            if (isset( $_POST['booking_advanced_costs_calc_fixed_cost_with_procents'] ))  
                update_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents'   , 'On' );
            else                                                                   
                update_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents'   , 'Off' );
        }
        $booking_advanced_costs_calc_fixed_cost_with_procents = get_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents' );

        $booking_forms_extended = get_bk_option( 'booking_forms_extended');
        if ($booking_forms_extended !== false) {
            if ( is_serialized( $booking_forms_extended ) ) $booking_forms_extended = unserialize($booking_forms_extended);
            $booking_forms_extended[] = array( 'name' => 'standard' , 'form' => '' );
        } else $booking_forms_extended = array( array( 'name' => 'standard' , 'form'=>'' ) );
        ?>
        <div class="booking-submenu-tab-container" style="margin:-12px 0 20px 0;padding:5px;">
            <div class="nav-tabs booking-submenu-tab-insidecontainer wpdevbk " style="margin:5px 0 0;">
            <?php     //FixIn: 5.4.5.5
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
            if ( ( $is_can ) || ( WP_BK_CUSTOM_FORMS_FOR_REGULAR_USERS ) ) {
            ?><div style="margin:0 5px 5px;float: left; height: auto;">                    
                    <label class="wpbc_inline_legend" for="select_booking_form"><?php _e('Custom Form' ,'booking'); ?></label>
                    <select class="wpbc_stick_right" style="float:left;margin:0;" 
                              name="select_booking_form" id="select_booking_form" onchange="javascript:changeBookingFormForAdvancedCost(this);">

                        <option style="padding:3px;border-bottom: 1px solid #ccc;" value="form_divstandard" <?php if (  (! isset($_GET['booking_form'])) || ($_GET['booking_form'] == 'standard')  ) { echo 'selected="selected"'; } ?>  ><?php _e('Standard' ,'booking'); ?></option>

                        <optgroup label="<?php _e('Custom Form' ,'booking'); ?>">
                        <?php
                        foreach ($booking_forms_extended as $value) {   
                            if ($value['name']=='standard') continue;
                            $div_form_name = 'form_div' . $value['name'];
                            $div_form_name = str_replace(' ', '', $div_form_name);
                            $div_form_name = str_replace('"', '', $div_form_name);
                            $div_form_name = str_replace("'" , '', $div_form_name);                                    
                            ?>
                            <option value="<?php echo $div_form_name; ?>"
                                    <?php if ( (isset($_GET['booking_form']) ) && ($_GET['booking_form'] == $value['name'] ) ) { echo 'selected="selected"'; } ?>
                                    <?php if ( (isset($_POST['advanced_form_submit_name']) ) && ( $_POST['advanced_form_submit_name'] == $value['name'] ) ) { echo 'selected="selected"'; } ?>
                                    ><?php echo $value['name']; ?></option>
                        <?php  } ?>
                        </optgroup>
                    </select>
                    <a data-original-title="<?php _e('Load selected booking form'); ?>"  rel="tooltip" 
                       class="tooltip_top button button-secondary wpbc_stick_left"  style="float:left;"
                       onclick="javascript:changeBookingFormForAdvancedCost(document.getElementById('select_booking_form'));" ><?php _e('Load' ,'booking'); ?></a>
                       </label>                    
              </div> 
              <?php } ?>  
              <button  class="button-primary button" style="float: right;font-size: 12px;margin: 0 5px 0 0;"
                                            onclick="javascript: wpbc_submit_advanced_form();"
                   ><?php _e('Save Changes' ,'booking'); ?></button>                        
              <div class="clear" style="height:0px;"></div>
            </div>
        </div>

        <div class='meta-box'>  
           <div <?php $my_close_open_win_id = 'bk_settings_costs_advanced_cost_management'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                <h3 class='hndle'><span><?php _e('Advanced cost management' ,'booking'); ?></span></h3> <div class="inside">

              <form  name="advanced_costs_form" action="" method="post" id="advanced_costs_form" >
                  <input type="hidden" name="checking_form_submit" id="checking_form_submit" value="1" />
                  <input type="hidden" name="advanced_form_submit_name" id="advanced_form_submit_name" value="standard" />

                    <?php
                    foreach ($booking_forms_extended as $bk_form_ext) {  
                       $div_form_name = 'form_div' . $bk_form_ext['name']; 
                       $div_form_name = str_replace(' ', '', $div_form_name); 
                       $div_form_name = str_replace('"', '', $div_form_name); 
                       $div_form_name = str_replace("'" , '', $div_form_name);
                       
                       $mystyle = 'display:none;';
                       
                       if ( isset( $_POST['advanced_form_submit_name'] ) ) {
                           if ( $_POST['advanced_form_submit_name'] == $bk_form_ext['name'] ) {
                               $mystyle = ''; 
                           }
                       } else if ($bk_form_ext['name'] == 'standard') {
                           $mystyle = ''; 
                       }

                       echo '<div style="'.$mystyle.'" class="wpdev_forms_div" id="'.$div_form_name.'">';
                       ?><div class="wpbc-help-message wpbc_season_filter_hedear_section" style="text-transform: capitalize;"><?php 
                            _e('Configure Additional cost for the form' ,'booking'); ?> 
                           <span class="wpbc_label_available"><?php echo $bk_form_ext['name']; ?></span> 
                        </div><?php
                        $booking_fields = $this->get_fields_from_booking_form($bk_form_ext['form']);

                        if ($booking_fields !== false) {
                            $fields_count   = $booking_fields[0] ;
                            $fields_matches = $booking_fields[1] ;
                        } else { $fields_count = 0; }
                        ?>
                       <table class="form-table settings-table0">
                        <tbody><?php

                            if ($bk_form_ext['name'] == 'standard') {
                                $field__values = get_bk_option( 'booking_advanced_costs_values' );
                                if ( $field__values !== false ) {
                                    if ( is_serialized( $field__values ) )   $field__values_unserilize = unserialize($field__values);
                                    else                                     $field__values_unserilize = $field__values;
                                }
                                $field_sufix = '';
                            } else {
                                $field__values = get_bk_option( 'booking_advanced_costs_values_for' . $bk_form_ext['name'] );
                                if ( $field__values !== false ) {
                                    if ( is_serialized( $field__values ) )   $field__values_unserilize = unserialize($field__values);
                                    else                                     $field__values_unserilize = $field__values;
                                }
                                $field_sufix = $bk_form_ext['name'];
                                $field_sufix = '__' . $field_sufix;
                            }

                            $field_sufix = str_replace(' ', '', $field_sufix);
                            $field_sufix = str_replace('"', '', $field_sufix);
                            $field_sufix = str_replace("'" , '', $field_sufix);

                            for ($i = 0; $i < $fields_count; $i++) {

                                if ( ($fields_matches[1][$i] == 'checkbox*') || ($fields_matches[1][$i] == 'checkbox') || ($fields_matches[1][$i] == 'select') || ($fields_matches[1][$i] == 'select*') ){ // Right now working only with select boxes

                                $field__name = trim($fields_matches[2][$i]);
                                $field__orig_value = trim($fields_matches[4][$i]);
                                $fields_count_values = preg_match_all( '%\s*"[a-zA-Z0-9.:\s$,\[\]/\\-_!@&-=+?~]{0,}"\s*%', $field__orig_value, $fields_matches_values) ;
                                ?>  <tr valign="top" style="border-top:1px solid #eee;">
                                        <th scope="row">
                                            <label for="paypal_is_active" ><?php _e('Additional cost for' ,'booking'); echo ' <span style="color:#0d5;">' , $fields_matches[2][$i], '</span>'; ?>:</label>
                                        </th>
                                        <td><?php
                                              for ($j = 0; $j < $fields_count_values; $j++) { ?>
                                                <?php
                                                $field__value = '100%';
                                                $field_orig_val = trim(str_replace('"','',$fields_matches_values[0][$j]));
                                                $field_orig_val = explode('@@',$field_orig_val); 
                                                $field_orig_val = $field_orig_val[ ( count($field_orig_val) - 1 ) ];
                                                $field_orig_val = trim(str_replace(' ','_',$field_orig_val));

                                                if (   ($field__values)  !== false ) { // Default

                                                    if ($field_orig_val =='') {

                                                       if ( isset($field__values_unserilize[ $field__name ]) )
                                                           if ( isset($field__values_unserilize[ $field__name ][ 'checkbox' ]) )
                                                                $field__value = $field__values_unserilize[ $field__name ][  'checkbox'  ];

                                                    } else {

                                                       if ( isset($field__values_unserilize[ $field__name ]) )
                                                           if ( isset($field__values_unserilize[ $field__name ][ $field_orig_val ]) )
                                                                $field__value = $field__values_unserilize[ $field__name ][ $field_orig_val ];
                                                    }
                                                }
                                                ?>
                                                <span style="font-weight:bold;font-size:13px;">
                                                  <?php echo $field_orig_val ; ?> 
                                                </span>
                                                <span style="font-weight:bold;"> = </span>
                                                <input value='<?php echo $field__value; ?>'    style="width: 100px;text-align:left;" type="text"
                                                         name="additional_cost_value_<?php echo $field__name, $field_orig_val, $field_sufix; ?>"
                                                           id="additional_cost_value_<?php echo $field__name, $field_orig_val, $field_sufix; ?>" /><br/>
                                              <?php } ?>                                               
                                        </td>
                                        <?php /*  // Show help  info  about usage of cost hints for the additional costs ?>
                                        <td style="text-aling:left;">
                                            <div class="wpbc-help-message" style="margin:0px;float:left;">
                                                <code style="">[<?php echo trim($fields_matches[2][$i]); ?>_hint]</code>
                                                <span style="font-style: italic;font-size:0.9em;"> - <?php 
                                                    printf( __( 'use this shortcode in the %sbooking form%s to show additional cost of this selected option.','booking'),
                                                            '<a href="' . esc_url( admin_url( add_query_arg( array( 'page' => WPDEV_BK_PLUGIN_DIRNAME . '/' . WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option',
                                                            'tab' => 'form' ), 'admin.php' ) ) ) . '">', '</a>' );
                                                    
                                                    echo '<div style="margin-top:5px;"><strong>'; _e('Note' ,'booking');  echo ':</strong> ';
                                                    
                                                    printf( __('If you set additional cost as percent for specific option, then its will show cost for each seperated options as percent from the %stotal cost%s and not from  the original cost (excluding fixed additional costs).','booking'),
                                                            '<strong>', '</strong>'
                                                            );
                                                    echo '</div>';
                                                    ?>
                                                </span>
                                            </div>
                                        </td><?php /**/ ?>
                                    </tr>
                                <?php
                                } //End if select
                            } // END FOREACH
                            ?>
                        </tbody>
                       </table>

                       <?php 
                       echo "</div>";                             
                    } ?>
                    <div class="clear" style="height:1px;margin:20px 0 10px;border-top:1px solid #ccc;"></div>
                    <label for="booking_advanced_costs_calc_fixed_cost_with_procents">
                        <input <?php if ($booking_advanced_costs_calc_fixed_cost_with_procents == 'On') echo "checked";/**/ ?>  value="<?php echo $booking_advanced_costs_calc_fixed_cost_with_procents; ?>" name="booking_advanced_costs_calc_fixed_cost_with_procents" id="booking_advanced_costs_calc_fixed_cost_with_procents" type="checkbox" style="margin:-3px 3px 0 0;" />
                        <?php _e('Check this box if you want that specific additional cost, which configured as percentage for some option, apply to other additional fixed costs and not only to original booking cost.' ,'booking');?>
                    </label>


                    <div  class="wpbc-help-message"  style=""><strong><?php _e('Note' ,'booking'); ?>. </strong>
                          <?php 
                          printf(__('Configure additional cost, which depend from selection of selectbox(es) and checkbox(es).' ,'booking')
                                  );
                          echo ' ';
                          printf(__('Fields %s(selectbox(es) and checkbox(es))%s are shown here automatically if they exist in the %sbooking form%s.' ,'booking')
                                  ,'<em>', '</em>'
                                  , '<em><a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&tab=form" >','</a></em>' 
                                ); 
                          ?>
                        <hr/>
                          <strong><?php 
                          _e('Enter additional cost in formats:' ,'booking'); ?></strong>
                          <p class="description"><?php printf(__('For example, if the original cost of the booking is %s, then after applying additional costs the total cost will be folowing' ,'booking'), '<code>$80</code>');?>:</p><?php
                          ?><ul style="list-style: disc outside none;margin: 0 15px;">
                              <li>
                                  <strong><?php _e('Enter fixed cost' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be %s' ,'booking'), '<code>55</code>', '<code>$80 + $55 = $135</code>');?>
                              </li>
                              <li>
                                  <strong><?php _e('Enter percentage of the entire booking' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be %s' ,'booking'), '<code>200%</code>', '<code>$80 * 200% = $160</code>'); ?>
                              </li>
                              <li>
                                  <strong><?php _e('Enter fixed amount for each selected day' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be (if selected 3 days) %s' ,'booking'), '<code>50/day</code> ' .__('or' ,'booking') . '<code>50/night</code>' , '<code>3 * $80 + 3 * $50 = $390</code>'); ?>                                      
                              </li>
                              <li>
                                  <strong><?php _e('Enter percentage as additional sum, which is based only on original cost and not full sum' ,'booking');?></strong>:<?php printf(__('%s, then total cost will be %s' ,'booking'), '<code>+75%</code>', '<code>80 + 80 * 75% = $140</code>'); ?>
                              </li>                                      
                          </ul> 
                          <hr />
                          <?php  printf(__('Please check more info about configuration of this cost settings on this %spage%s.' ,'booking')
                                  , '<em><a href="http://wpbookingcalendar.com/faq/" >','</a></em>' 
                                ); 
                          ?>
                    </div>
                    <div class="clear" style="height:10px;"></div>
                    <button  class="button-primary button" style="float: right;font-size: 12px;margin: 0 5px 0 0;"
                                            onclick="javascript: wpbc_submit_advanced_form();"
                   ><?php _e('Save Changes' ,'booking'); ?></button>
                    <script type="text/javascript">
                        function wpbc_submit_advanced_form(){
                            
                            var selectObj = document.getElementById('select_booking_form');
                            var idx = selectObj.selectedIndex;     
                            var my_form = selectObj.options[idx].value;
                            my_form = my_form.substr(8);
                            jQuery('#advanced_form_submit_name').val( my_form );                            
                            jQuery( "div.wpdev_forms_div:hidden" ).remove();                            
                            document.forms['advanced_costs_form'].submit();
                        }
                    </script>
            </form>

       </div> </div> </div>
      <?php
    }


    // Show Availability and Rates resource content list in selected tab menu
    function show_booking_availability_rates_settings_page(){ global $wpdb;
        $alternative_color = '0';
        $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-resources&tab=resources';

        if ((isset($_POST['submit_resources']))) {

            $bk_types = get_bk_types(true);

            // Edit ////////////////////////////////////////////////////////
            if ( ($_POST['bulk_resources_action'] == 'blank' ) || ($_POST['bulk_resources_action'] == 'edit' ) ) {

                foreach ($bk_types as $bt) {
                      if ( false === $wpdb->query( 
                              $wpdb->prepare( "UPDATE {$wpdb->prefix}bookingtypes SET cost = %s WHERE booking_type_id = %d " 
                                              ,$_POST['resource_cost'.$bt->id], $bt->id )
                                                 )  
                         )  bk_error('Error during updating to DB booking resources' ,__FILE__,__LINE__);
                }

            }

            // Delete //////////////////////////////////////////////////////
            if  ($_POST['bulk_resources_action'] == 'delete' ) {

              $delete_bk_id = '';
              foreach ($bk_types as $bt) { // Delete - Get all ID for deletion
                  if (isset($_POST['resources_items_'.$bt->id]))
                      $delete_bk_id .= $bt->id . ',';
              }

              if (! empty($delete_bk_id)) {
                  $delete_bk_id = substr($delete_bk_id,0,-1);                 // Remove last Comma
                  $delete_sql = "DELETE FROM {$wpdb->prefix}bookingtypes WHERE booking_type_id IN ( {$delete_bk_id} )";

                  if ( false === $wpdb->query( $delete_sql ) )  bk_error('Error during deleting booking resources',__FILE__,__LINE__ );
              }

            }

        }


          $bk_types = get_bk_types(true);

          $all_id = array(array('id'=>0,'title'=>' - '));
          foreach ($bk_types as $btt) {
                if (isset($btt->parent)) if ($btt->parent==0)  $all_id[] = array('id'=>$btt->id, 'title'=> $btt->title);
          }
          $advanced_params = array();
          make_bk_action('wpdev_bk_booking_resource_page_before');
        ?>
        <div style="clear:both;width:100%;height:1px;"></div>
                <div style="position: absolute; right: 15px;top: 25px;" class="wpdevbk">
                <form  name="booking_filters_formID" action="" method="post" id="booking_filters_formID" class=" form-search">

                    <?php if (isset($_REQUEST['wh_resource_id']))  $wh_resource_id = $_REQUEST['wh_resource_id'];                  //  {'1', '2', .... }
                          else                                    $wh_resource_id      = '';                    ?>
                    <input class="input" type="text" placeholder="<?php _e('Resource ID or Title' ,'booking'); ?>" name="wh_resource_id" id="wh_resource_id" value="<?php echo $wh_resource_id; ?>" >
                    <input class="input" type="hidden"  name="page_num" id="page_num" value="1" >
                    <button class="button button-secondary" type="submit"><?php _e('Go' ,'booking'); ?></button>
                </form>
                </div><?php


        $is_show_add_resource = array();
        if (isset($_GET['wpdev_edit_rates'])) $is_show_add_resource = $_GET['wpdev_edit_rates'];
        if (isset($_GET['wpdev_edit_avalaibility'])) $is_show_add_resource = $_GET['wpdev_edit_avalaibility'];

        if (isset($_GET['wpdev_edit_costs_from_days'])) $is_show_add_resource = $_GET['wpdev_edit_costs_from_days'];            
        if (isset($_GET['wpdev_edit_costs_deposit_payment'])) $is_show_add_resource = $_GET['wpdev_edit_costs_deposit_payment'];

        if (empty($is_show_add_resource)) $is_show_add_resource = array();
        else $is_show_add_resource = explode(',',$is_show_add_resource);

        ?>
        <div style="width:100%;">

            <form  name="post_option_resources" action="" method="post" id="post_option_resources" >
                
                <div class="clear" style="height:25px;width:100%;clear:both;display:none;"></div>
                <div style="height:auto;display:none;">
                    <select name="bulk_resources_action" id="bulk_resources_action" style="float:left;width:110px;margin-right:10px;" >
                        <option value="blank"><?php _e('Bulk Actions' ,'booking') ?></option>
                        <option value="edit"v><?php _e('Edit' ,'booking') ?></option>
                        <option value="delete"><?php _e('Delete' ,'booking') ?></option>
                    </select>
                    <input class="button-primary button" style="float:left;margin-top:1px;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_resources"/>                     
                    <?php if (isset($_REQUEST['page_num'])) { ?>
                        <input class="input" type="hidden"  name="page_num" id="page_num" value="<?php echo $_REQUEST['page_num']; ?>" >
                    <?php } if (isset($_REQUEST['wh_resource_id'])) { ?>
                        <input class="input" type="hidden"  name="wh_resource_id" id="wh_resource_id" value="<?php echo $_REQUEST['wh_resource_id']; ?>" >
                    <?php } ?>
                </div>
                <div class="clear" style="height:15px;width:100%;clear:both;"></div>
                
                
                <table style="width:99%;" class="resource_table booking_table" cellpadding="0" cellspacing="0">
                        <?php // Headers  ?>
                    <tr>
                        <th style="width:15px;"><input type="checkbox" onclick="javascript:jQuery('.resources_items').attr('checked', this.checked);" class="resources_items" id="resources_items_all"  name="resources_items_all" /></th>
                        <th style="width:10px;height:35px;border-left: 1px solid #BBBBBB;"> <?php _e('ID' ,'booking'); ?> </th>
                        <th style="height:35px;width:220px;"> <?php _e('Resource name' ,'booking'); ?> </th>
                        <?php make_bk_action('resources_settings_table_headers' ); ?>
                        <th style="text-align:center;"> <?php _e('Info' ,'booking'); ?> </th>

                    </tr>
                    <?php
                    if (! empty($bk_types))
                      foreach ($bk_types as $bt) {
                              if ( $alternative_color == '')    $alternative_color = ' class="alternative_color" ';
                              else                              $alternative_color = '';

                              if ( in_array($bt->id, $is_show_add_resource) ) $alternative_color = ' class="resource_line_selected" ';
                           ?>
                           <tr>
                                <td <?php echo $alternative_color; ?> ><legend class="wpbc_mobile_legend"><?php _e('Selection' ,'booking'); ?>:</legend><input type="checkbox" <?php //if ( in_array($bt->id, $is_show_add_resource) ) {echo ' checked ';} ?> class="resources_items" id="resources_items_<?php echo $bt->id; ?>" value="<?php echo $bt->id; ?>"  name="resources_items_<?php echo $bt->id; ?>" /></td>
                                <td style="border-left: 1px solid #ccc;text-align: center;" <?php echo $alternative_color; ?> ><legend class="wpbc_mobile_legend"><?php _e('ID' ,'booking'); ?>:</legend><?php echo $bt->id; ?></td>
                                <td style="<?php if (isset($bt->parent)) if ($bt->parent != 0 ) { echo 'padding-left:50px;'; } ?>" <?php echo $alternative_color; ?> >
                                    <legend class="wpbc_mobile_legend"><?php _e('Resource Name' ,'booking'); ?>:</legend>
                                    <span style="<?php if ( (isset($bt->parent)) && (isset($bt->count)) )  if ( ($bt->parent == 0 ) && ( $bt->count > 1 ) )  { echo 'font-weight:bold;'; }?>"><?php echo $bt->title; ?></span>
                                    <!--input  maxlength="17" type="text"
                                        style="<?php  if ((isset($bt->parent)) && (isset($bt->count)) )    if ( ($bt->parent == 0 ) && ( $bt->count > 1 ) ) { echo 'width:210px;font-weight:bold;'; } else { echo 'width:170px;font-size:11px;'; } ?>"
                                        value="<?php echo $bt->title; ?>"
                                        name="type_title<?php echo $bt->id; ?>" id="type_title<?php echo $bt->id; ?>" /-->
                                    <?php if ((isset($bt->parent)) && (isset($bt->count)) )   if ($bt->parent == 0 ) { make_bk_action('resources_settings_after_title', $bt, $all_id, $alternative_color ); } ?>
                                </td>

                                <?php make_bk_action('resources_settings_table_collumns', $bt, $all_id, $alternative_color , $advanced_params ); ?>
                                <td style="border-right: 0px;border-left: 1px solid #ccc;text-align: center;" <?php echo $alternative_color; ?> >
                                    <legend class="wpbc_mobile_legend"><?php _e('Info' ,'booking'); ?>:</legend>
                                    <?php make_bk_action('resources_settings_table_info_collumns', $bt, $all_id, $alternative_color ); ?>
                                </td>

                           </tr>
                           <?php
                      }
                            ?>

                        <tr class="wpbc_table_footer">
                            <td></td>
                            <td></td>
                            <td></td>
                            <?php make_bk_action('resources_settings_table_footers' ); ?>
                            <td></td>
                        </tr>

                        </table>

                        <div class="clear" style="height:10px;width:100%;clear:both;"></div>
                        <input class="button-primary button" style="float:left;margin-top:1px;" type="submit" value="<?php _e('Save Changes' ,'booking'); ?>" name="submit_resources"/>
                        <div class="clear" style="height:5px;width:100%;clear:both;"></div>

                        <?php // Show Pagination
                        echo '<div class="wpdevbk">';
                        $active_page_num = (isset($_REQUEST['page_num']))?$_REQUEST['page_num']:1;
                        $items_count_in_page=get_bk_option( 'booking_resourses_num_per_page');
                        wpdevbk_show_pagination(get_booking_resources_count(), $active_page_num, $items_count_in_page,
                                array('page','tab', 'wh_resource_id')//,'wpdev_edit_costs_from_days','wpdev_edit_rates', 'wpdev_edit_costs_deposit_payment','wpdev_edit_avalaibility')
                                );
                        echo '</div>';
                        ?>

                        <div class="clear" style="height:1px;"></div>

                    </form>

        </div>

        <div style="clear:both;width:100%;height:1px;"></div> <?php

    }

// Resources

    // Show top TAB selection menu for the Resources page:
    function wpdev_booking_resources_top_menu(){

        $is_only_icons = ! true;
        if ($is_only_icons) echo '<style type="text/css"> #menu-wpdevplugin .nav-tab { padding:4px 2px 6px 32px !important; } </style>';

        if  (! isset($_GET['tab'])) $_GET['tab'] = 'resource';
        $selected_title = $_GET['tab'];

        $selected_icon = '';
        ?>
         <div style="height:1px;clear:both;margin-top:20px;"></div>
         <div id="menu-wpdevplugin">
            <div class="nav-tabs-wrapper">
                <div class="nav-tabs wpdevbk">



                    <?php $title = __('Resources' ,'booking');
                    $my_icon = 'icon-list'; $my_tab = 'resource';  ?>
                    <?php if ($_GET['tab'] == 'resource') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = __('Resources Settings' ,'booking'); $selected_icon = $my_icon;  ?><a class="nav-tab nav-tab-active"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php _e('Resources management' ,'booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } ?><i class="<?php if ($slct_a == 'selected') { echo 'icon-white '; } echo $my_icon; ?>"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></a><?php } else { ?></span></a><?php } ?>

                    <?php $title = __('Costs and Rates' ,'booking');
                    $my_icon = 'icon-signal'; $my_tab = 'cost';  ?>
                    <?php if ($_GET['tab'] == 'cost') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
    <?php if ($slct_a == 'selected') {  $selected_title = __('Costs and Rates Settings' ,'booking'); $selected_icon = $my_icon;  ?><a class="nav-tab nav-tab-active"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php _e('Customization of rates, valuation days cost and deposit amount ' ,'booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } ?><i class="<?php if ($slct_a == 'selected') { echo 'icon-white '; } echo $my_icon; ?>"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></a><?php } else { ?></span></a><?php } ?>

                    <?php $title = __('Advanced Cost' ,'booking');
                    $my_icon = 'icon-shopping-cart'; $my_tab = 'cost_advanced';  ?>
                    <?php if ($_GET['tab'] == 'cost_advanced') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = __('Advanced Cost Settings' ,'booking'); $selected_icon = $my_icon;  ?><a class="nav-tab nav-tab-active"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php _e('Customization of additional cost, which depend from form fields' ,'booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } ?><i class="<?php if ($slct_a == 'selected') { echo 'icon-white '; } echo $my_icon; ?>"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></a><?php } else { ?></span></a><?php } ?>

                    <?php if (class_exists('wpdev_bk_biz_l'))   { ?>
                        <?php $title = __('Coupons' ,'booking');
                        $my_icon = 'icon-tags'; $my_tab = 'coupons';  ?>
                        <?php if ($_GET['tab'] == 'coupons') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                        <?php if ($slct_a == 'selected') {  $selected_title = __('Coupons Settings' ,'booking'); $selected_icon = $my_icon;  ?><a class="nav-tab nav-tab-active"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php _e('Setting coupons for discount' ,'booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } ?><i class="<?php if ($slct_a == 'selected') { echo 'icon-white '; } echo $my_icon; ?>"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></a><?php } else { ?></span></a><?php } ?>
                    <?php } ?>

                    <?php $title = __('Availability' ,'booking');
                    $my_icon = 'icon-check'; $my_tab = 'availability';  ?>
                    <?php if ($_GET['tab'] == 'availability') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = __('Availability Settings' ,'booking'); $selected_icon = $my_icon;  ?><a class="nav-tab nav-tab-active"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php _e('Customization of availability settings' ,'booking'); ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } ?><i class="<?php if ($slct_a == 'selected') { echo 'icon-white '; } echo $my_icon; ?>"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></a><?php } else { ?></span></a><?php } ?>

                    <?php $title = __('Season Filters' ,'booking');
                    $my_icon = 'icon-asterisk'; $my_tab = 'filter';  ?>
                    <?php if ($_GET['tab'] == 'filter') {  $slct_a = 'selected'; } else {  $slct_a = ''; } ?>
                    <?php if ($slct_a == 'selected') {  $selected_title = __('Season Filters Settings' ,'booking'); $selected_icon = $my_icon;  ?><a class="nav-tab nav-tab-active"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } else { ?><a rel="tooltip" class="nav-tab tooltip_bottom" title="<?php _e('Customization of season filters settings' ,'booking') ; ?>" href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-resources&tab=<?php echo $my_tab; ?>"><?php } ?><i class="<?php if ($slct_a == 'selected') { echo 'icon-white '; } echo $my_icon; ?>"></i><span class="nav-tab-text"> <?php  if ($is_only_icons) echo '&nbsp;'; else echo $title; ?><?php if ($slct_a == 'selected') { ?></span></a><?php } else { ?></span></a><?php } ?>

                </div>
            </div>
         </div>
         <script type="text/javascript">
//                var val1 = '<img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/<?php echo $selected_icon; ?>"><br />';
//                jQuery('div.wrap div.icon32').html(val1);
                jQuery('div.bookingpage h2').html( '<?php echo $selected_title ; ?>');
          </script>
        <div style="height:1px;clear:both;border-top:1px solid #bbc;"></div>  <?php
    }

    // Show content of specific resource tab selection
    function wpdev_booking_resources_show_content(){ global $wpdb;

        if  ( $_GET['tab'] == 'filter')   {
            $this->show_booking_date_filter();
        } elseif  ( $_GET['tab'] == 'availability')   {
            $this->show_booking_availability_rates_settings_page();
        } elseif  ( $_GET['tab'] == 'cost_advanced')   {
            $this->advanced_cost_management_settings();
        } elseif  ( $_GET['tab'] == 'cost')   {
            $this->show_booking_availability_rates_settings_page();
        } elseif  ( $_GET['tab'] == 'coupons')   {
          make_bk_action('wpdev_booking_settings_show_coupons');
        }
    }


//   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ///////////////////////////////////////////////////

    // Activate
    function pro_activate() {


            add_bk_option( 'booking_available_days_num_from_today', '' );
            
            add_bk_option( 'booking_forms_extended', serialize(array()) );

            add_bk_option( 'booking_is_resource_deposit_payment_active' , 'On');

            add_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents', 'Off');

            add_bk_option( 'booking_is_show_cost_in_tooltips',  'Off');
            add_bk_option( 'booking_highlight_cost_word',  __('Cost: ' ,'booking')  );
            
            add_bk_option( 'booking_is_show_cost_in_date_cell' , 'Off');
            add_bk_option( 'booking_cost_in_date_cell_currency' , '&#36;');            
            
            add_bk_option( 'booking_visitor_number_rate', '0');
            add_bk_option( 'booking_visitor_number_rate_type', '%');
            if ( wpdev_bk_is_this_demo() )
                update_bk_option( 'booking_form', str_replace('\\n\\','', $this->reset_to_default_form('payment') ) );

            global $wpdb;
            $charset_collate = '';
            //if ( $wpdb->has_cap( 'collation' ) ) {
                        if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
                        if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
            //}


            if  (wpbc_is_field_in_table_exists('bookingtypes','default_form') == 0){
                $simple_sql = "ALTER TABLE {$wpdb->prefix}bookingtypes ADD default_form varchar(249) NOT NULL default 'standard'";
                $wpdb->query( $simple_sql );
           }

            // Season filter table
            if ( ( ! wpbc_is_table_exists('booking_seasons')  )) { // Cehck if tables not exist yet

                    $wp_queries=array();
                    $wp_queries[] = "CREATE TABLE {$wpdb->prefix}booking_seasons (
                         booking_filter_id bigint(20) unsigned NOT NULL auto_increment,
                         title varchar(200) NOT NULL default '',
                         filter text ,
                         PRIMARY KEY  (booking_filter_id)
                        ) $charset_collate;";

                    $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( __('Weekend' ,'booking') ) .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:2:"On";i:1;s:3:"Off";i:2;s:3:"Off";i:3;s:3:"Off";i:4;s:3:"Off";i:5;s:3:"Off";i:6;s:2:"On";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\' );';
//                  $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( __('Work days' ,'booking') ) .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:3:"Off";i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:3:"Off";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\' );';

                    ////////////////////////////////////////////////////////////
                    // Configuration  of the Own Conditional Season Filters
                    ////////////////////////////////////////////////////////////
                    $date_identificator = strtotime('+4 weeks');                                    //$date_identificator = strtotime( 'first day of next month' );
                    $my_date_title = date_i18n( 'F',  $date_identificator);   
                    $my_date = date( 'Y-n',  $date_identificator);                                     
                    $my_date = explode('-', $my_date);
                    $next_year  = $my_date[0];
                    $next_month = $my_date[1];        

                    $filter = array();
                    $filter['weekdays'] = array();
                    for ($k = 0; $k < 7; $k++) {  
                        $filter['weekdays'][$k] = 'On';
                    }
                    $filter['days'] = array();
                    for ($k = 1; $k < 32; $k++) {  
                        if ( $k < 15 )
                            $filter['days'][$k] = 'On';
                        else
                            $filter['days'][$k] = 'Off';
                    }
                    $filter['monthes'] = array();
                    for ($k = 1; $k < 13; $k++) {      
                        if ( $next_month == $k )
                            $filter['monthes'][$k] = 'On';
                        else
                            $filter['monthes'][$k] = 'Off';
                    }
                    $filter['year'] = array();
                    $start_year = date('Y') ;
                    for ($k = ($start_year-1); $k < ($start_year+11); $k++) { 
                        if ( $next_year == $k )
                            $filter['year'][$k] = 'On';
                        else
                            $filter['year'][$k] = 'Off';
                    }
                    $filter['start_time'] = ''; $filter['end_time'] = '';      
                    $configurable_filter = serialize($filter);                    
                    $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( '1 - 14, '. $my_date_title ) .'", \''. 
                                                                                                                  $configurable_filter .'\' );';

    
    
                    $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_seasons ( title, filter ) VALUES ( "'. wpbc_clean_parameter( __('High season' ,'booking') ) .'", \'a:6:{s:8:"weekdays";a:7:{i:0;s:2:"On";i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";}s:4:"days";a:31:{i:1;s:2:"On";i:2;s:2:"On";i:3;s:2:"On";i:4;s:2:"On";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:2:"On";i:11;s:2:"On";i:12;s:2:"On";i:13;s:2:"On";i:14;s:2:"On";i:15;s:2:"On";i:16;s:2:"On";i:17;s:2:"On";i:18;s:2:"On";i:19;s:2:"On";i:20;s:2:"On";i:21;s:2:"On";i:22;s:2:"On";i:23;s:2:"On";i:24;s:2:"On";i:25;s:2:"On";i:26;s:2:"On";i:27;s:2:"On";i:28;s:2:"On";i:29;s:2:"On";i:30;s:2:"On";i:31;s:2:"On";}s:7:"monthes";a:12:{i:1;s:3:"Off";i:2;s:3:"Off";i:3;s:3:"Off";i:4;s:3:"Off";i:5;s:2:"On";i:6;s:2:"On";i:7;s:2:"On";i:8;s:2:"On";i:9;s:2:"On";i:10;s:3:"Off";i:11;s:3:"Off";i:12;s:3:"Off";}s:4:"year";a:12:{i:2013;s:3:"Off";i:2014;s:2:"On";i:2015;s:2:"On";i:2016;s:2:"On";i:2017;s:2:"On";i:2018;s:2:"On";i:2019;s:2:"On";i:2020;s:2:"On";i:2021;s:3:"Off";i:2022;s:3:"Off";i:2023;s:3:"Off";i:2024;s:3:"Off";}s:10:"start_time";s:0:"";s:8:"end_time";s:0:"";}\' );';    
                    
                    foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );
            }

            // Booking Types   M E T A  table
            if ( ( ! wpbc_is_table_exists('booking_types_meta')  )) { // Cehck if tables not exist yet

                    $wp_queries=array();
                    $wp_queries[] = "CREATE TABLE {$wpdb->prefix}booking_types_meta (
                         meta_id bigint(20) unsigned NOT NULL auto_increment,
                         type_id bigint(20) NOT NULL default 0,
                         meta_key varchar(200) NOT NULL default '',
                         meta_value text ,
                         PRIMARY KEY  (meta_id)
                        ) $charset_collate;";

                    foreach ($wp_queries as $wp_q) $wpdb->query( $wp_q );
            }


            if ( wpdev_bk_is_this_demo() )          {

                update_bk_option( 'booking_type_of_day_selections' , 'range' );
                update_bk_option( 'booking_range_selection_type', 'dynamic');
                update_bk_option( 'booking_range_selection_days_count','7');
                update_bk_option( 'booking_range_selection_days_max_count_dynamic',30);
                update_bk_option( 'booking_range_selection_days_specific_num_dynamic','');
                update_bk_option( 'booking_range_start_day' , '-1' );
                update_bk_option( 'booking_range_selection_days_count_dynamic','3');
                update_bk_option( 'booking_range_start_day_dynamic' , '-1' );
                update_bk_option( 'booking_range_selection_time_is_active', 'On');
                update_bk_option( 'booking_range_selection_start_time','14:00');
                update_bk_option( 'booking_range_selection_end_time','12:00');/**/

                update_bk_option( 'booking_view_days_num','30');
                //update_bk_option( 'booking_is_show_legend' , 'Off' );

                //update_bk_option( 'booking_is_show_cost_in_tooltips',  'On');
                update_bk_option( 'booking_is_show_cost_in_date_cell',  'On');

                update_bk_option( 'booking_skin', '/css/skins/traditional.css'); // '/css/skins/standard.css');

                                //form fields setting

                 update_bk_option( 'booking_form_show',  '<div class="payment-content-form"> 
<strong>First Name</strong>:<span class="fieldvalue">[name]</span><br/> 
<strong>Last Name</strong>:<span class="fieldvalue">[secondname]</span><br/> 
<strong>Email</strong>:<span class="fieldvalue">[email]</span><br/> 
<strong>Phone</strong>:<span class="fieldvalue">[phone]</span><br/> 
<strong>Address</strong>:<span class="fieldvalue">[address]</span><br/> 
<strong>City</strong>:<span class="fieldvalue">[city]</span><br/> 
<strong>Post code</strong>:<span class="fieldvalue">[postcode]</span><br/> 
<strong>Country</strong>:<span class="fieldvalue">[country]</span><br/> 
<strong>Adults</strong>:<span class="fieldvalue"> [visitors]</span><br/> 
<strong>Children</strong>:<span class="fieldvalue"> [children]</span><br/> 
<strong>Details</strong>:<br /><span class="fieldvalue"> [details]</span> 
</div>' );
                    update_bk_option( 'booking_advanced_costs_values', 
                    unserialize( 's:247:"a:3:{s:8:"visitors";a:4:{i:1;s:4:"100%";i:2;s:4:"200%";i:3;s:4:"300%";i:4;s:4:"400%";}s:8:"children";a:4:{i:0;s:4:"100%";i:1;s:4:"100%";i:2;s:4:"100%";i:3;s:4:"100%";}s:18:"term_and_condition";a:1:{s:28:"I_Accept_term_and_conditions";s:4:"100%";}}"') );

                    $wp_queries=array();
                    $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 4, "rates", "a:3:{s:6:\"filter\";a:3:{i:3;s:3:\"Off\";i:2;s:3:\"Off\";i:1;s:2:\"On\";}s:4:\"rate\";a:3:{i:3;s:1:\"0\";i:2;s:1:\"0\";i:1;s:3:\"200\";}s:9:\"rate_type\";a:3:{i:3;s:1:\"%\";i:2;s:1:\"%\";i:1;s:1:\"%\";}}" );';
                    $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 3, "costs_depends", "a:3:{i:0;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\">\";s:4:\"from\";s:1:\"1\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:2:\"50\";s:13:\"cost_apply_to\";s:5:\"fixed\";}i:1;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:1:\"=\";s:4:\"from\";s:1:\"3\";s:2:\"to\";s:1:\"4\";s:4:\"cost\";s:2:\"45\";s:13:\"cost_apply_to\";s:5:\"fixed\";}i:2;a:6:{s:6:\"active\";s:2:\"On\";s:4:\"type\";s:4:\"summ\";s:4:\"from\";s:1:\"4\";s:2:\"to\";s:1:\"2\";s:4:\"cost\";s:3:\"175\";s:13:\"cost_apply_to\";s:5:\"fixed\";}}" );';
                    $wp_queries[] = 'INSERT INTO '.$wpdb->prefix .'booking_types_meta (  type_id, meta_key, meta_value ) VALUES ( 2, "availability", "a:2:{s:7:\"general\";s:2:\"On\";s:6:\"filter\";a:3:{i:3;s:3:\"Off\";i:2;s:2:\"On\";i:1;s:3:\"Off\";}}" );';


                    foreach ($wp_queries as $wp_q) $wpdb->query($wp_q);


            }

    }

    //Decativate
    function pro_deactivate(){

        delete_bk_option( 'booking_available_days_num_from_today');
            
        delete_bk_option( 'booking_forms_extended');

        delete_bk_option( 'booking_is_resource_deposit_payment_active' );

        delete_bk_option( 'booking_is_show_cost_in_tooltips');
        delete_bk_option( 'booking_highlight_cost_word');

        delete_bk_option( 'booking_is_show_cost_in_date_cell' );
        delete_bk_option( 'booking_cost_in_date_cell_currency' );            
        
        
        delete_bk_option( 'booking_visitor_number_rate');
        delete_bk_option( 'booking_visitor_number_rate_type');

        delete_bk_option( 'booking_advanced_costs_values');
        delete_bk_option( 'booking_advanced_costs_calc_fixed_cost_with_procents');

        global $wpdb;
        // delete_bk_option( 'booking_form_show');

        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}booking_seasons");
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}booking_types_meta");
     }


}

?>