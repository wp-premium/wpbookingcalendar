<?php
/*
This is COMMERCIAL SCRIPT
We are do not guarantee correct work and support of Booking Calendar, if some file(s) was modified by someone else then wpdevelop.
*/


////////////////////////////////////////////////////////////////////////////////
//  S Q L   Modifications  for  Booking Listing  ///////////////////////////////
////////////////////////////////////////////////////////////////////////////////

// Resources
function get_l_bklist_sql_resources($blank, $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 ){

        global $wpdb;
        $sql_where = '';

        // BL                                                               // Childs in dif sub resources
        $sql_where.=   "        OR ( bk.booking_id IN (
                                                 SELECT DISTINCT booking_id
                                                 FROM {$wpdb->prefix}bookingdates as dtt
                                                 WHERE  " ;
        if ($wh_approved !== '')
                                $sql_where.=                  " dtt.approved = $wh_approved  AND " ;
        $sql_where.= set_dates_filter_for_sql($wh_booking_date, $wh_booking_date2, 'dtt.') ;

        $sql_where.=                                          "   (
                                                                dtt.type_id IN ( ". $wh_booking_type ." )
                                                                OR  dtt.type_id IN (
                                                                                     SELECT booking_type_id
                                                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                                                    )
                                                             )
                                                 )
                              ) " ;
        // BL                                                               // Just Childs sub resources
        $sql_where.=   "         OR ( bk.booking_type IN (
                                                     SELECT booking_type_id
                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                    )
                              )" ;

    return $sql_where;
}
add_bk_filter('get_l_bklist_sql_resources', 'get_l_bklist_sql_resources');


// Resources
function get_l_bklist_sql_resources_for_calendar_view($blank, $wh_booking_type, $wh_approved, $wh_booking_date, $wh_booking_date2 ){

        global $wpdb;
        $sql_where = '';

        // BL                                                               // Childs in dif sub resources
        $sql_where.=   "        OR ( bk.booking_id IN (
                                                 SELECT DISTINCT booking_id
                                                 FROM {$wpdb->prefix}bookingdates as dtt
                                                 WHERE  " ;
        if ($wh_approved !== '')
                                $sql_where.=                  " dtt.approved = $wh_approved  AND " ;
        $sql_where.= set_dates_filter_for_sql($wh_booking_date, $wh_booking_date2, 'dtt.') ;

        $sql_where.=                                          "   (
                                                                dtt.type_id IN ( ". $wh_booking_type ." )
                                                                OR  dtt.type_id IN (
                                                                                     SELECT booking_type_id
                                                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                                                    )
                                                             )
                                                 )
                              ) " ;
/*
        // BL                                                               // Just Childs sub resources
        $sql_where.=   "         OR ( bk.booking_type IN (
                                                     SELECT booking_type_id
                                                     FROM {$wpdb->prefix}bookingtypes as bt
                                                     WHERE  bt.parent IN ( ". $wh_booking_type ." )
                                                    )
                              )" ;
/**/
    return $sql_where;
}
add_bk_filter('get_l_bklist_sql_resources_for_calendar_view', 'get_l_bklist_sql_resources_for_calendar_view');



//FixIn: 6.0.1.1
class WPBC_Search_Availability {
    
    public $min_date_check;
    public $max_date_check;
        
    private $check_in_date_time;                                                // Check In date  from Request transformed to yyyy-mm-dd hh:mm:ss for SQL request
    private $check_out_date_time;                                               // Check Out date from Request transformed to yyyy-mm-dd hh:mm:ss for SQL request
        
    public $check_in_date;                                                      // Check In date  from Request                     
    public $check_out_date;                                                     // Check Out date from Request
        
    private $search_param_visitors;                                             // Search parameter Number of Visitors    
    private $search_param_category;                                             // Search parameter Category (restrinctions from  posts)
    private $search_param_tag;                                                  // Search parameter Tag (restrinctions from  posts)
    private $search_param_custom_fields;                                        // Search parameter - Custom Fields from posts or fields. Each custom field must start from "booking_" term.
    private $search_param_users_limit;                                          // Search parameter - search  only for specific user(s). Several  users can be as comma seperated string.    
    public  $search_param_additional_days;                                      // Search parameter to  search additional +/- 2 days from check in/out dates
    
    private $is_skip_check_in;                                                  // Is skip checking Check In  days,  if they  booked or not. Parameter from General Booking Settings page.
    private $is_skip_check_out;                                                 // Is skip checking Check Out days,  if they  booked or not. Parameter from General Booking Settings page.
        
    private $text_title_no_results;                                             // Title: "No results"
    private $text_title_found_results;                                          // Title: "{searchresults} Results Found"
    private $text_title_advanced_search;                                        // Title: "Advanced search results"
    private $custom_content_found_item;                                         // Customization of Foind Item from Booking > Settings > Search page. 
    
    private $posts_cache_content_with_booking_forms;                            // Cached Posts with  booking forms
    
    private $booking_types;
    private $parents_or_single;
    
    public $booking_dates;                                                      // Array of all Booked Dates.
    public $booked_dates_in_resources;                                          // Array of Booking Resources with booked dates. 
    
    private $displayed_resources_in_search_results;                             //backup of all displayed resources ins earch  resources
    public $is_check_for_showing_resources;                                     // Is show only not displayed previously booking resources in advanced search "+/- 2 days"
    
    function __construct() {
        
        $this->min_date_check = '1980-01-01'; 
        $this->max_date_check = '1980-01-01'; 
                
        $this->check_in_date  = '1980-01-01'; 
        $this->check_out_date = '1980-01-01'; 
        
        $this->check_in_date_time  = '1980-01-01 00:00:00'; 
        $this->check_out_date_time = '1980-01-01 23:59:59'; 
        
        $this->search_param_visitors = 1;
        $this->search_param_category = '';
        $this->search_param_tag = '';
        $this->search_param_users_limit = '';
        $this->search_param_additional_days = 0;
        $this->search_param_custom_fields = array();
        
        $this->is_skip_check_in = false;
        $this->is_skip_check_out = false;            
        
        $this->text_title_no_results = '';
        $this->text_title_found_results = '';
        $this->text_title_advanced_search= '';
        $this->custom_content_found_item = get_bk_option( 'booking_found_search_item' );
        $this->custom_content_found_item = apply_bk_filter( 'wpdev_check_for_active_language', $this->custom_content_found_item );
        
        $this->posts_cache_content_with_booking_forms = '';
        
        $this->booking_types      = array();
        $this->parents_or_single  = array();        
        
        $this->booking_dates = array();
        $this->booked_dates_in_resources = array();   
        
        $this->displayed_resources_in_search_results = array();   
        $this->is_check_for_showing_resources = true;
    }

    
    public function searching() {
        
        // Standard ////////////////////////////////////////////////////////////
        
        // Define all booked dates
        $this->sql();

        // Assign booked dates to resources ( Work with Min/Max dates filtering for specific search  request)
        $this->set_booked_dates_to_resources();

        // Get Free resources based on search  parameters
        $free_objects = $this->get_free_resources();

        // Show Title
        $found_num = $this->show_title( $free_objects );

        // Show Results
        $bk_date_start  = $this->check_in_date;       
        $bk_date_finish = $this->check_out_date;
        $this->show_results( $free_objects, $bk_date_start, $bk_date_finish  );
        
        $this->displayed_resources_in_search_results = $free_objects;
        
        // Advanced ////////////////////////////////////////////////////////////
        
        if ( $this->search_param_additional_days > 0 ) {

            $check_dates_intervals = array();
            for ( $i = 1; $i <= $this->search_param_additional_days; $i++ ) {
                $check_dates_intervals[] = $i;
                $check_dates_intervals[] = -$i;
            }
            
            
            $min_date_check = strtotime( $this->min_date_check );
            $max_date_check = strtotime( $this->max_date_check );   

            $found_num = 0;
            
            foreach ( $check_dates_intervals as $day_shift ) {
                if ( $day_shift > 0 )
                    $day_shift_text = ' +' . $day_shift . ' day';
                else 
                    $day_shift_text = ' -' . abs($day_shift) . ' day';
                
                if (abs($day_shift)>1)
                    $day_shift_text .= 's';
                
                $this->min_date_check = date( 'Y-m-d' , strtotime( $day_shift_text , $min_date_check ) );
                $this->max_date_check = date( 'Y-m-d' , strtotime( $day_shift_text , $max_date_check ) );

                $this->check_in_date  = $this->min_date_check;
                $this->check_out_date = $this->max_date_check; 

                $this->booked_dates_in_resources = array();

                $this->set_booked_dates_to_resources();

                $free_objects = $this->get_free_resources();
                
                // Restrict display already showed booking resources 
                $free_objects = $this->check_for_showing_resources ( $free_objects );

                $found_num += $this->show_title_additional( $free_objects );

                $this->show_results( $free_objects, $this->min_date_check, $this->max_date_check  );
            }
            
            if ( $found_num > 0 ) {
                ?><script type="text/javascript" >
                    jQuery('.wpbc_advanced_search_header').html('<?php  echo '<h2>' . __( 'Advanced', 'booking' ) . ' (' . $found_num . ' ' . strtolower( __( 'Search results', 'booking' ) ) . ')' .  '</h2>' ?>');
                </script> <?php
            }
        }    
        
        $this->write_js();

    }
    
    
    private function check_for_showing_resources ( $free_objects ) {
        
        if ( ! $this->is_check_for_showing_resources ) return $free_objects;
        
        $return_free_objects = array();
        
        foreach ( $free_objects as $resource_id => $resource_obj ) {
            
            if ( isset( $this->displayed_resources_in_search_results[ $resource_id ] ) ) {
                
            } else {
                $return_free_objects[ $resource_id ] = $resource_obj;
                $this->displayed_resources_in_search_results[ $resource_id ] = $resource_obj;
            }
            
        }
        
        return $return_free_objects;
    }
    
    
    public function set_custom_fields ( $param ) {
        $this->search_param_custom_fields = $param;
    }
    
    public function set_booking_types( $param ) {
        
        $this->booking_types = $param;
        
        foreach ( $this->booking_types as $bk_t ) {   
              $this->booked_dates_in_resources[ $bk_t['obj']->id ] = array();        // Create Resources ID array for future assigning booked dates
        }
    }
    
    public function set_parents_or_single( $param ) {
        $this->parents_or_single  = $param;
    }
    
    public function set_cache_content( $param ) {
       $this->posts_cache_content_with_booking_forms =  $param;       
    }

        private function remove_posts_not_fit_to_params() {

            // Check  according users restrictions if its exist
            if ( !empty( $this->search_param_users_limit ) ) {
                $this->search_param_users_limit = explode( ',', $this->search_param_users_limit );
                foreach ( $this->posts_cache_content_with_booking_forms as $key_c => $value_c ) {
                    $is_exist = false;
                    if ( ( isset( $value_c->user ) ) && ( in_array( $value_c->user, $this->search_param_users_limit ) ) )
                        $is_exist = true;
                    if ( !$is_exist )
                        unset( $this->posts_cache_content_with_booking_forms[$key_c] );
                }
            }


            // In Category search functionality
            if ( !empty( $this->search_param_category ) )
                foreach ( $this->posts_cache_content_with_booking_forms as $key_c => $value_c ) {
                    $cats = $value_c->category;
                    $is_exist = false;
                    foreach ( $cats as $cats_c ) {
                        if ( strtolower( trim( $cats_c['category'] ) ) == strtolower( trim( $this->search_param_category ) ) )
                            $is_exist = true;
                    }
                    if ( !$is_exist ) {
                        unset( $this->posts_cache_content_with_booking_forms[$key_c] );
                    }
                }


            // In TAGS search functionality
            if ( !empty( $this->search_param_tag ) )
                foreach ( $this->posts_cache_content_with_booking_forms as $key_c => $value_c ) {
                    $cats = $value_c->tags;
                    $is_exist = false;
                    foreach ( $cats as $cats_c ) {
                        if ( strtolower( trim( $cats_c['tag'] ) ) == strtolower( trim( $this->search_param_tag ) ) )
                            $is_exist = true;
                    }
                    if ( !$is_exist ) {
                        unset( $this->posts_cache_content_with_booking_forms[$key_c] );
                    }
                }

            // Custom fields
            foreach ( $this->search_param_custom_fields as $custom_f_key => $custom_f_value ) {         // $this->search_param_custom_fields = Array ( [booking_width] => 50 )
                if ( !empty( $custom_f_value ) ) {

                    // Normilize the Custom Search option to Array (in case if we was using any comma separated option)                  
                    $custom_f_value = explode( ',', $custom_f_value );
                    foreach ( $custom_f_value as $key_v => $value_v ) {
                        $custom_f_value[$key_v] = strtolower( trim( $value_v ) );
                    }

                    foreach ( $this->posts_cache_content_with_booking_forms as $key_c => $value_c ) {
                        $custom_fields = $value_c->custom_fields;

                        if ( isset( $custom_fields[$custom_f_key] ) ) {

                            $custom_field_in_post = $custom_fields[$custom_f_key];
                            // Normilize the Custom Fields in our POST Content
                            foreach ( $custom_field_in_post as $key_v => $value_v ) {
                                $custom_field_in_post[$key_v] = strtolower( trim( $value_v ) );
                            }

                               
                            $is_exist = false;
                            $custom_field_difference = array_diff( $custom_f_value, $custom_field_in_post );
                          
                            //if ( ( count( $custom_field_difference ) == 0 ) && ( count( $custom_field_in_post ) == count( $custom_f_value ) ) ) 
                            if ( count( $custom_field_difference ) == 0 )       //FixIn:6.0.1.3   
                                $is_exist = true;

                            $is_check_for_length_or_area_parameter = false;     //FixIn:6.0.1.3
                            if ( $is_check_for_length_or_area_parameter ) {                                          
                                // Checking Custom fields,  if its lower or equal  to  search custom parameter
                                if ( ( count( $custom_field_in_post ) == count( $custom_f_value ) ) ) {
                                    foreach ( $custom_field_in_post as $temp_key_post => $temp_value_post ) {
                                        if ( $custom_f_value[$temp_key_post] >= $temp_value_post )
                                            $is_exist = true;
                                    }
                                }
                            }

                            if ( !$is_exist )
                                unset( $this->posts_cache_content_with_booking_forms[$key_c] );
                        } else { //this custom field is not exist inside of the post, so we will remove this post  from the search  results
                            unset( $this->posts_cache_content_with_booking_forms[$key_c] );
                        }
                    }
                }
            }


        }

    public function define_parameters() {
        
        // Paramters in request ////////////////////////////////////////////////////

        if ( isset( $_REQUEST['bk_check_in'] ) )    $this->check_in_date_time = $_REQUEST['bk_check_in'] . ' 00:00:00';     //'2010-12-05 00:00:00';        
        if ( isset( $_REQUEST['bk_check_out'] ) )   $this->check_out_date_time = $_REQUEST['bk_check_out'] . ' 23:59:59';   //'2011-01-30 00:00:00';        
        
        if ( isset( $_REQUEST['bk_visitors'] ) )    $this->search_param_visitors = intval($_REQUEST['bk_visitors']);        
        if ( isset( $_REQUEST['bk_category'] ) )    $this->search_param_category = $_REQUEST['bk_category'];        
        if ( isset( $_REQUEST['bk_tag'] ) )         $this->search_param_tag = $_REQUEST['bk_tag'];        
        if ( isset( $_REQUEST['bk_users'] ) )       $this->search_param_users_limit = $_REQUEST['bk_users'];
        
        if ( isset( $_REQUEST['bk_no_results_title'] ) )     $this->text_title_no_results = $_REQUEST['bk_no_results_title'];
        if ( isset( $_REQUEST['bk_search_results_title'] ) ) $this->text_title_found_results = $_REQUEST['bk_search_results_title'];
        if ( isset( $_REQUEST['additional_search'] ) )       $this->search_param_additional_days = intval( $_REQUEST['additional_search'] );


        // Custom fields in the search  request ////////////////////////////////////           
        if ( isset( $_REQUEST['bk_search_params'] ) ) {                             // Example: $this->search_param_custom_fields = Array ( [booking_width] => 50 )
            $bk_search_params = $_REQUEST['bk_search_params'];
            $bk_search_params = explode( '~', $bk_search_params );
            foreach ( $bk_search_params as $custom_value ) {
                if ( !empty( $custom_value ) ) {
                    $custom_value = explode( '^', $custom_value );
                    if ( (!empty( $custom_value )) && (strpos( $custom_value[1], 'booking_' ) === 0) ) {
                        $this->search_param_custom_fields[ $custom_value[1] ] = $custom_value[2];
                    }
                }
            }
        }     
        
        $this->check_in_date  = substr( $this->check_in_date_time,  0, 10 );
        $this->check_out_date = substr( $this->check_out_date_time, 0, 10 );
        
        $this->min_date_check = $this->check_in_date;
        $this->max_date_check = $this->check_out_date;
        
        
        // Show the Cehck In/Out date as available for the booking resources with  capacity > 1
        if ( (get_bk_option( 'booking_range_selection_time_is_active' ) == 'On') && (get_bk_option( 'booking_check_out_available_for_parents' ) == 'On') )
            $this->is_skip_check_out = true;

        if ( (get_bk_option( 'booking_range_selection_time_is_active' ) == 'On') && (get_bk_option( 'booking_check_in_available_for_parents' ) == 'On') )
            $this->is_skip_check_in = true;
        ////////////////////////////////////////////////////////////////////////////
        
        
        $this->remove_posts_not_fit_to_params();
        
    }

    
    // SQL - Get Booked Dates //////////////////////////////////////////////////
    public function sql() {
        
        global $wpdb;
            
        $bk_type_additional_id = array_keys( $this->booked_dates_in_resources );
        $bk_type_additional_id = implode( ',', $bk_type_additional_id );


        if ( $this->search_param_additional_days > 0 ) {
            if ( $this->search_param_additional_days >  7 ) $this->search_param_additional_days = 7;
            $date_start_interval  = ' - INTERVAL ' . intval( $this->search_param_additional_days ) . ' DAY ';
            $date_finish_interval = ' + INTERVAL ' . intval( $this->search_param_additional_days ) . ' DAY ';
        } else {
            $date_start_interval  = '';
            $date_finish_interval = '';
        }

        $wpbc_bdtb_booking   = $wpdb->prefix . "booking";
        $wpbc_bdtb_dates     = $wpdb->prefix . "bookingdates";
        $wpbc_bdtb_resources = $wpdb->prefix . "bookingtypes";

        // Get unavailable Dates  
        $sql_req = $wpdb->prepare(
                "SELECT DISTINCT dt.booking_date, dt.type_id as date_res_type, dt.booking_id, dt.approved, bk.form, bt.parent, bt.prioritet, bt.booking_type_id as type, bk.cost
                    FROM {$wpbc_bdtb_dates} as dt
                        INNER JOIN {$wpbc_bdtb_booking} as bk
                        ON    bk.booking_id = dt.booking_id
                            INNER JOIN {$wpbc_bdtb_resources} as bt
                            ON    bk.booking_type = bt.booking_type_id
                     WHERE dt.booking_date >= %s " . $date_start_interval . " AND dt.booking_date <= %s " . $date_finish_interval . " AND
                            ( bk.booking_type IN ({$bk_type_additional_id}) " .     // All bookings from PARENT TYPE
                "OR bt.parent  IN ({$bk_type_additional_id}) " .                    // Bookings from CHILD Type
                "OR dt.type_id IN ({$bk_type_additional_id}) " .                    // Bk. Dates from OTHER TYPEs, which belong to This TYPE
                ") " .
                " ORDER BY dt.booking_date "
                , $this->check_in_date_time, $this->check_out_date_time );

        $this->booking_dates = $wpdb->get_results( $sql_req );        
    }
    
    
    public function set_booked_dates_to_resources( ) {
        
        // Assign  to booking resources $this->booked_dates_in_resources Booked Dates /
        foreach ( $this->booking_dates as $dt_obj ) {

            if ( ! $this->is_date_in_check_interval( $dt_obj->booking_date ) )
                continue;
            
            $bk_time = substr( $dt_obj->booking_date, 11 );
            $bk_date = substr( $dt_obj->booking_date, 0, 10 );
            if ( ($bk_time == '00:00:00' ) ) {
                if ( !empty( $dt_obj->date_res_type ) )
                    $this->booked_dates_in_resources[$dt_obj->date_res_type][] = $dt_obj->booking_date;
                else
                    $this->booked_dates_in_resources[$dt_obj->type][] = $dt_obj->booking_date;
            } else {

                $is_start_time = substr( $bk_time, 7 );
                if ( $is_start_time == '1' )    $is_start_time = 1;
                else                            $is_start_time = 0;

                if ( ( ($bk_date == $this->check_in_date) && ( $is_start_time ) && (!$this->is_skip_check_in) ) || // Start search date is at date with start time, so this day is BUSY
                        ( ($bk_date == $this->check_out_date) && (!$is_start_time) && (!$this->is_skip_check_out) ) || // Finish search date is at date with check-out time, so this day is BUSY
                        ( ($bk_date != $this->check_in_date) && ($bk_date != $this->check_out_date) )   // Some day is busy inside of search days interval, so this day is BUSY
                ) {

                    if ( !empty( $dt_obj->date_res_type ) )
                        $this->booked_dates_in_resources[$dt_obj->date_res_type][] = $dt_obj->booking_date;
                    else
                        $this->booked_dates_in_resources[$dt_obj->type][] = $dt_obj->booking_date;
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////////    

        // Recehck  Dates for the availability based on the season filters /////////
        //$search_dates = wpdevbkGetDaysBetween( $this->check_in_date_time, $this->check_out_date_time );
        $search_dates = wpdevbkGetDaysBetween( $this->min_date_check, $this->max_date_check );
        $cached_season_filters = array();

        foreach ( $this->booked_dates_in_resources as $bk_type_id => $value ) {

            $cached_season_filters[ $bk_type_id ] = apply_bk_filter( 'get_available_days', $bk_type_id );

            foreach ( $search_dates as $search_date ) {
                $is_date_available = is_this_day_available_on_season_filters( $search_date, $bk_type_id, $cached_season_filters[$bk_type_id] );    // Get availability

                if ( !$is_date_available ) {                 
                    $this->booked_dates_in_resources[ $bk_type_id ][] = date_i18n( 'Y-m-d H:i:s', strtotime( $search_date ) );
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////////

//debuge($this->booked_dates_in_resources);    
        
    }
    
    
    public function is_date_in_check_interval( $date_to_check ) {
        
        $min_date_int_check = strtotime( $this->min_date_check );
        $max_date_int_check = strtotime( $this->max_date_check );
        
        $date_int_to_check = strtotime( $date_to_check ); 
        
        if ( ( $date_int_to_check >= $min_date_int_check ) && ( $date_int_to_check <= $max_date_int_check ) )
            return true;
        else 
            return false;
    }
    
    public function get_free_resources() {
        
        $temp_parents_or_single = $this->parents_or_single;
        
        // Get only parents and single BK Resources:
    //debuge($this->parents_or_single);
        // Remove all busy elements ////////////////////////////////////////////////////////////////////////////////////
        $free_objects = array();
        foreach ( $this->parents_or_single as $key => $value ) {

            //check all CHILDS objects, if its booked in this dates interval or not
            if ( count( $value['child'] ) > 0 ) {
                foreach ( $value['child'] as $ch_key => $ch_value ) {
                    if ( isset($this->booked_dates_in_resources[$ch_value->id]))            //FixIn: 6.0.1.13
                        if ( count( $this->booked_dates_in_resources[$ch_value->id] ) > 0 ) { // Some dates are booked for this booking resource at search date interval
                            unset( $this->parents_or_single[$key]['child'][$ch_key] );  // Remove this child oject
                            $this->parents_or_single[$key]['count'] --;                // Reduce the count of child objects
                        }
                }
            }

            // Check PARENT object if its booked or not
            //if ( count( $this->booked_dates_in_resources[$this->parents_or_single[$key]['obj']->id] ) > 0 ) { // This item is also booked            
            if (  ( isset( $this->booked_dates_in_resources[$this->parents_or_single[$key]['obj']->id] ) )      //FixIn: 6.0.1.13
               && ( count( $this->booked_dates_in_resources[$this->parents_or_single[$key]['obj']->id] ) > 0 ) 
               ) { // This item is also booked
                $this->parents_or_single[$key]['obj']->is_booked = 1;         // Its booked
                $this->parents_or_single[$key]['count'] --;                    // Reduce items count
            } else {
                if ( empty( $this->parents_or_single[$key]['obj'] ) ) {
                    $this->parents_or_single[$key]['obj'] = new StdClass;
                }
                $this->parents_or_single[$key]['obj']->is_booked = 0;         // Free
            }

            // Set number of available items
            $this->parents_or_single[$key]['obj']->items_count = $this->parents_or_single[$key]['count'];

            // If this bk res. available so then add it to new free archive
            if ( ($this->parents_or_single[$key]['obj']->is_booked != 1) || ($this->parents_or_single[$key]['obj']->items_count > 0) )
                $free_objects[$key] = $this->parents_or_single[$key]['obj'];
        }


        // Get SETTINGS, how visitors apply to availability number.
        $is_vis_apply = get_bk_option( 'booking_is_use_visitors_number_for_availability' );  // On  | Off
        $availability_for = get_bk_option( 'booking_availability_based_on' );                  // items | visitors

        if ( $is_vis_apply == 'On' ) {
            if ( $availability_for == 'items' ) { // items
                $availability_base = 'items';
            } else {                            // visitors
                $availability_base = 'visitors';
            }
        } else { // visitors = 'Off'
            $availability_base = 'off';
        }


        // Remove some items, if availabilty less then number of visitors in search form
        if ( $availability_base !== 'off' ) // check only if visitors apply to availability
            foreach ( $free_objects as $key => $value ) {
                if ( $availability_base == 'visitors' ) {     // visitors
                    if ( ($value->items_count * $value->visitors) < $this->search_param_visitors ) {
                        // Total number of VISITORS in all available ITEMS less then num of visitors in search form
                        // So remove this item
                        unset( $free_objects[$key] );
                    }
                } else {                                    // items
                    if ( ( $value->items_count <= 0 ) || ($value->visitors < $this->search_param_visitors ) ) {
                        // we have that items have capacity of visitors less then in search form
                        // or
                        // all items booked
                        // So remove this item
                        unset( $free_objects[$key] );
                    }
                }
            }

        
        $this->parents_or_single = $temp_parents_or_single;    
        
        return $free_objects;
    }

    
    public function show_title_additional( $free_objects ) {
        
        $search_results_found = 0;
        foreach ( $free_objects as $key => $value ) {
            if ( isset( $this->posts_cache_content_with_booking_forms[$value->id] ) ) {
                $search_results_found++;
            }
        }

        if (  ( $search_results_found > 0 ) && ( empty( $this->text_title_advanced_search ) )  ){
            
            $this->text_title_advanced_search = '<center class="wpbc_advanced_search_header"><h2>' . __( 'Advanced', 'booking' ) . ' ' . strtolower( __( 'Search results', 'booking' ) ) .  '</h2></center>';
            echo $this->text_title_advanced_search;                
        }
        
        return $search_results_found;
    }
    
    public function show_title( $free_objects ) {

        if ( empty( $this->text_title_no_results ) )
            $this->text_title_no_results = __( 'Nothing found', 'booking' );

        $my_search_title = '<center><h2>' . $this->text_title_no_results . '</h2></center>';
        
        if ( empty( $this->text_title_found_results ) )
            $this->text_title_found_results = __( 'Search results', 'booking' );       // sprintf( __('%s Results Found', 'booking'), '{searchresults}')
        
        $search_results_found = 0;
        foreach ( $free_objects as $key => $value ) {
            if ( isset( $this->posts_cache_content_with_booking_forms[$value->id] ) ) {
                $search_results_found++;
            }
        }
        
        if ( $search_results_found > 0 ) {
            $text_title_found_results = str_replace( '{searchresults}', $search_results_found, $this->text_title_found_results );
            $my_search_title = '<center><h2>' . $text_title_found_results . '</h2></center>';
        }

        if ( is_admin() && ( defined( 'DOING_AJAX' ) ) && ( DOING_AJAX ) )
            $my_search_title = '<div class="booking_search_ajax_container">' . $my_search_title;

        echo $my_search_title;
        
        return $search_results_found;
    }
    
    
    public function show_results( $free_objects, $bk_date_start, $bk_date_finish ) {

        // Sort the booking resources array with priority descending ///////////
        $sort_free_objects = array();
        foreach ( $free_objects as $key => $value ) {
            $sort_free_objects[] = $value->prioritet;
        }
        array_multisort( $sort_free_objects, SORT_DESC, SORT_NUMERIC, $free_objects );
        ////////////////////////////////////////////////////////////////////////

        foreach ( $free_objects as $key => $value ) {

            $custom_content_found_item_echo = $this->custom_content_found_item;


            // GUID
            if ( function_exists( 'icl_object_id' ) ) {
                $bc_post_type = get_post_type( $this->posts_cache_content_with_booking_forms[$value->id]->ID );
                $post_translated_id = icl_object_id( $this->posts_cache_content_with_booking_forms[$value->id]->ID, $bc_post_type, true, substr( WPDEV_BK_LOCALE_RELOAD, 0, 2 ) );
                if ( !empty( $post_translated_id ) ) {
                    $my_translated_guid = get_permalink( $post_translated_id ); //$my_translated_post->guid;
                }
            }


            if ( (isset( $this->posts_cache_content_with_booking_forms[$value->id]->post_excerpt )) && ( $this->posts_cache_content_with_booking_forms[$value->id]->post_excerpt != '' ) ) {
                $booking_info = $this->posts_cache_content_with_booking_forms[$value->id]->post_excerpt;
                if ( function_exists( 'icl_object_id' ) ) {
                    $bc_post_type = get_post_type( $this->posts_cache_content_with_booking_forms[$value->id]->ID );
                    $post_translated_id = icl_object_id( $this->posts_cache_content_with_booking_forms[$value->id]->ID, $bc_post_type, true, substr( WPDEV_BK_LOCALE_RELOAD, 0, 2 ) );

                    if ( !empty( $post_translated_id ) ) {
                        $my_translated_post = get_post( $post_translated_id );
                        $booking_info = $my_translated_post->post_excerpt;
                    }
                }

                $booking_info = str_replace( '"', '', $booking_info );
                $booking_info = str_replace( "'", '', $booking_info );
                $booking_info = html_entity_decode( $booking_info );
                $booking_info = apply_bk_filter( 'wpdev_check_for_active_language', $booking_info );
                $custom_content_found_item_echo = str_replace( '[booking_info]', '<div class="booking_search_result_info">' . $booking_info . '</div>', $custom_content_found_item_echo );
            } else
                $custom_content_found_item_echo = str_replace( '[booking_info]', '', $custom_content_found_item_echo );

            if ( isset( $this->posts_cache_content_with_booking_forms[$value->id] ) ) {
                $booking_cache_title = $this->posts_cache_content_with_booking_forms[$value->id]->post_title;
                if ( function_exists( 'icl_object_id' ) ) {
                    $bc_post_type = get_post_type( $this->posts_cache_content_with_booking_forms[$value->id]->ID );
                    $post_translated_id = icl_object_id( $this->posts_cache_content_with_booking_forms[$value->id]->ID, $bc_post_type, true, substr( WPDEV_BK_LOCALE_RELOAD, 0, 2 ) );

                    if ( !empty( $post_translated_id ) ) {
                        $my_translated_post = get_post( $post_translated_id );
                        $booking_cache_title = $my_translated_post->post_title;
                    }
                }
                $booking_cache_title = str_replace( '"', '', $booking_cache_title );
                $booking_cache_title = str_replace( "'", '', $booking_cache_title );
                $booking_cache_title = html_entity_decode( $booking_cache_title );
                $booking_cache_title = apply_bk_filter( 'wpdev_check_for_active_language', $booking_cache_title );
            } else
                $booking_cache_title = '';
            //FixIn: 6.0.1
            $custom_content_found_item_echo = str_replace( '[search_check_in]', date_i18n( get_bk_option( 'booking_date_format' ), mysql2date( 'U', $bk_date_start ) ), $custom_content_found_item_echo );
            $custom_content_found_item_echo = str_replace( '[search_check_out]', date_i18n( get_bk_option( 'booking_date_format' ), mysql2date( 'U', $bk_date_finish ) ), $custom_content_found_item_echo );

            $custom_content_found_item_echo = str_replace( '[num_available_resources]', '<span class="booking_search_result_items_num">' . $value->items_count . '</span>', $custom_content_found_item_echo );
            $custom_content_found_item_echo = str_replace( '[max_visitors]', '<span class="booking_search_result_visitors_num">' . $value->visitors . '</span>', $custom_content_found_item_echo );
            $cost_currency = apply_bk_filter( 'get_currency_info', 'paypal' );
            $custom_content_found_item_echo = str_replace( '[standard_cost]', '<span class="booking_search_result_cost">' . $cost_currency . $value->cost . '</span>', $custom_content_found_item_echo );

            // if this bk rsource is inserted in some page so then show it
            if ( isset( $this->posts_cache_content_with_booking_forms[$value->id] ) ) {

                $my_link = get_permalink( $this->posts_cache_content_with_booking_forms[$value->id]->ID ); // $this->posts_cache_content_with_booking_forms[ $value->id ]->ID -- ID of the post

                if ( !empty( $my_translated_guid ) )
                    $my_link = $my_translated_guid;

                if ( function_exists( 'qtrans_convertURL' ) ) {
                    $q_lang = getBookingLocale();
                    if ( strlen( $q_lang ) > 2 ) {
                        $q_lang = substr( $q_lang, 0, 2 );
                    }
                    $my_link = qtrans_convertURL( $my_link, $q_lang );
                }

                if ( strpos( $my_link, '?' ) === false )    $my_link .= '?';
                else                                        $my_link .= '&';

                if ( strpos( $custom_content_found_item_echo, '[link_to_booking_resource]' ) === false ) {
                    $start_x_pos = strpos( $custom_content_found_item_echo, '[link_to_booking_resource' );

                    if ( $start_x_pos !== false ) {
                        $end_y_pos = strpos( $custom_content_found_item_echo, ']', $start_x_pos );
                        $get_button_title = substr( $custom_content_found_item_echo, $start_x_pos, ( $end_y_pos - $start_x_pos ) );
                        $get_button_title = str_replace( '[link_to_booking_resource', '', $get_button_title );
                        $get_button_title = trim( $get_button_title );
                        $get_button_title = substr( $get_button_title, 1, -1 );

                        $first_part = substr( $custom_content_found_item_echo, 0, $start_x_pos );
                        $last_part = substr( $custom_content_found_item_echo, ( $end_y_pos + 1 ) );

                        $custom_content_found_item_echo = $first_part .
                                '<a class="btn" href="' . $my_link . 'bk_check_in=' . $bk_date_start . '&bk_check_out=' . $bk_date_finish . '&bk_visitors=' . $this->search_param_visitors . '&bk_type=' . $value->id . '#bklnk' . $value->id . '" >' . trim( $get_button_title ) . '</a>' .
                                $last_part;
                    }
                } else {
                    $custom_content_found_item_echo = str_replace( '[link_to_booking_resource]', '<a class="btn" href="' . $my_link . 'bk_check_in=' . $bk_date_start . '&bk_check_out=' . $bk_date_finish . '&bk_visitors=' . $this->search_param_visitors . '&bk_type=' . $value->id . '#bklnk' . $value->id . '" >' . __( 'Book now', 'booking' ) . '</a>', $custom_content_found_item_echo );
                }

                $full_link = $my_link . 'bk_check_in=' . $bk_date_start . '&bk_check_out=' . $bk_date_finish . '&bk_visitors=' . $this->search_param_visitors . '&bk_type=' . $value->id . '#bklnk' . $value->id;
                $custom_content_found_item_echo = str_replace( '[book_now_link]', $full_link, $custom_content_found_item_echo );   //FixIn:6.0.1

                if ( true ) {   // Show image and title as not links
                    if ( (isset( $this->posts_cache_content_with_booking_forms[$value->id]->picture )) && ( $this->posts_cache_content_with_booking_forms[$value->id]->picture != 0) ) {
                        $image_src = $this->posts_cache_content_with_booking_forms[$value->id]->picture[0];
                        $image_w = $this->posts_cache_content_with_booking_forms[$value->id]->picture[1];
                        $image_h = $this->posts_cache_content_with_booking_forms[$value->id]->picture[2];

                        $custom_content_found_item_echo = str_replace( '[booking_featured_image]', '<img class="booking_featured_image" src="' . $image_src . '" />', $custom_content_found_item_echo );
                    } else
                        $custom_content_found_item_echo = str_replace( '[booking_featured_image]', '', $custom_content_found_item_echo );

                    $custom_content_found_item_echo = str_replace( '[booking_resource_title]', '<div class="booking_search_result_title">' . $booking_cache_title . '</div>', $custom_content_found_item_echo );
                } else {

                    if ( (isset( $this->posts_cache_content_with_booking_forms[$value->id]->picture )) && ( $this->posts_cache_content_with_booking_forms[$value->id]->picture != 0) ) {
                        $image_src = $this->posts_cache_content_with_booking_forms[$value->id]->picture[0];
                        $image_w = $this->posts_cache_content_with_booking_forms[$value->id]->picture[1];
                        $image_h = $this->posts_cache_content_with_booking_forms[$value->id]->picture[2];

                        $custom_content_found_item_echo = str_replace( '[booking_featured_image]'
                                , '<a  style="float:none; font-size:1em !important; border: none;background: transparent !important;" href="' . $full_link . '" >'
                                . '<img class="booking_featured_image" src="' . $image_src . '" /></a>'
                                , $custom_content_found_item_echo );
                    } else
                        $custom_content_found_item_echo = str_replace( '[booking_featured_image]', '', $custom_content_found_item_echo );


                    $custom_content_found_item_echo = str_replace( '[booking_resource_title]', '<div class="booking_search_result_title">'
                            . '<a  style="float:none; font-size:1em !important; border: none;background: transparent !important;" href="' . $full_link . '" >'
                            . $booking_cache_title
                            . '</a></div>', $custom_content_found_item_echo );
                }

                /**
                  Show the total cost  of the booking,
                  based on  the check in/out dates, number of selected visitors
                  and default form  for booking resource - for correct calculation of "Advanced cost" based on number of visitors.
                 */
                $total_cost_of_booking = wpbc_get_cost_of_booking( array(
                    'form' => 'select-one^visitors' . $value->id . '^' . $this->search_param_visitors,
                    'all_dates' => createDateRangeArray( date_i18n( "d.m.Y", strtotime( $bk_date_start ) ), date_i18n( "d.m.Y", strtotime( $bk_date_finish ) ) ),
                    'bk_type' => $value->id,
                    'booking_form_type' => apply_bk_filter( 'wpdev_get_default_booking_form_for_resource', 'standard', $value->id )
                        ) );

                $custom_content_found_item_echo = str_replace( '[cost_hint]'
                        , '<span class="booking_search_result_cost_hint">'
                        . $cost_currency . $total_cost_of_booking['cost_hint']
                        . '</span>'
                        , $custom_content_found_item_echo );
                $custom_content_found_item_echo = str_replace( '[original_cost_hint]'
                        , '<span class="booking_search_result_original_cost_hint">'
                        . $cost_currency . $total_cost_of_booking['original_cost_hint']
                        . '</span>'
                        , $custom_content_found_item_echo );
                $custom_content_found_item_echo = str_replace( '[additional_cost_hint]'
                        , '<span class="booking_search_result_additional_cost_hint">'
                        . $cost_currency . $total_cost_of_booking['additional_cost_hint']
                        . '</span>'
                        , $custom_content_found_item_echo );
                $custom_content_found_item_echo = str_replace( '[deposit_hint]'
                        , '<span class="booking_search_result_deposit_hint">'
                        . $cost_currency . $total_cost_of_booking['deposit_hint']
                        . '</span>'
                        , $custom_content_found_item_echo );
                $custom_content_found_item_echo = str_replace( '[balance_hint]'
                        , '<span class="booking_search_result_balance_hint">'
                        . $cost_currency . $total_cost_of_booking['balance_hint']
                        . '</span>'
                        , $custom_content_found_item_echo );

                echo '<div  class="booking_search_result_item">' . $custom_content_found_item_echo . '</div>';
            }
        }
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
        
    }
    
    public function write_js(){
        ?><script type="text/javascript" >
                    if (document.getElementById('booking_search_results' ) != null ) {
                    document.getElementById('booking_search_results' ).innerHTML = '';}
                </script> <?php
            if ( is_admin() && ( defined( 'DOING_AJAX' ) ) && ( DOING_AJAX ) ) {
        ?></div><script type="text/javascript" >
                              jQuery("#booking_search_ajax").after( jQuery("#booking_search_ajax .booking_search_ajax_container") );
                              jQuery("#booking_search_ajax").hide();
                    </script> <?php
            }
        
    } 
} // End Class

