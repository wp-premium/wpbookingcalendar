<?php 

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S u p p o r t    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Get array of images - icons inside of this directory
    function wpdev_bk_dir_list ($directories) {

        // create an array to hold directory list
        $results = array();

        if (is_string($directories)) $directories = array($directories);
        foreach ($directories as $dir) {
            if ( is_dir($dir) )
                $directory = $dir ;
            else
                $directory = WPDEV_BK_PLUGIN_DIR . $dir ;
            
            if ( file_exists( $directory ) ) {                                  //FixIn: 5.4.5
                // create a handler for the directory
                $handler = @opendir($directory);
                if ($handler !== false) {
                    // keep going until all files in directory have been read
                    while ($file = readdir($handler)) {

                        // if $file isn't this directory or its parent,
                        // add it to the results array
                        if ($file != '.' && $file != '..' && ( strpos($file, '.css' ) !== false ) )
                            $results[] = array($file, /* WPDEV_BK_PLUGIN_URL .*/ $dir . $file,  ucfirst(strtolower( str_replace('.css', '', $file))) );
                    }

                    // tidy up: close the handler
                    closedir($handler);
                }
            }
        }
        // done!
        return $results;
    }
    
    // Set URL from absolute to relative (starting from /)                            
    function wpbc_set_relative_url( $url ){

        $url = esc_url_raw($url);

        $url_path = parse_url($url,  PHP_URL_PATH);
        $url_path =  ( empty($url_path) ? $url : $url_path );

        $url =  trim($url_path, '/');
        return  '/' . $url;
    }
    
    // Insert New Line symbols after <br> tags. Usefull for the settings pages to  show in redable view
    function wpbc_nl_after_br($param) {
        
        $value = preg_replace( "@(&lt;|<)br\s*/?(&gt;|>)(\r\n)?@", "<br/>", $param );
        
        return $value;
    }
    

    /**
     * Replace ** to <strong> and * to  <em>
     * 
     * @param String $text
     * @return string
     */
    if ( ! function_exists( 'wpbc_recheck_strong_symbols' ) ) { 
    function wpbc_recheck_strong_symbols( $text ){
    
        $patterns =  '/(\*\*)(\s*[^\*\*]*)(\*\*)/';    
        $replacement = '<strong>${2}</strong>';
        $value_return = preg_replace($patterns, $replacement, $text);

        $patterns =  '/(\*)(\s*[^\*]*)(\*)/';    
        $replacement = '<em>${2}</em>';
        $value_return = preg_replace($patterns, $replacement, $value_return);

        return $value_return;
    }
    }
    // Get Correct Relative URL 
    function wpbc_make_link_relative( $link ){

        if ( $link  == get_option('siteurl') ) 
            $link = '/';
        $link = '/' . trim( wp_make_link_relative( $link ), '/' ); 

        return $link;        
    }
    
    // Get Correct Absolute URL 
    function wpbc_make_link_absolute( $link ){
    
        if ( ( $link  != get_option('siteurl') ) && ( strpos($link, 'http') !== 0 ) )
            $link  = get_option('siteurl') . '/' . trim( wp_make_link_relative( $link ), '/' ); 
        return esc_js( $link ) ;
    }
    
    // Check according actual language
    function wpdev_check_for_active_language($content_orig){

        $content=$content_orig;

        $languages = array();
        $content_ex = explode('[lang',$content);

        foreach ($content_ex as $value) {

            if (substr($value,0,1) == '=') {

                $pos_s = strpos($value,'=');
                $pos_f = strpos($value,']');
                $key = trim( substr($value, ($pos_s+1), ($pos_f-$pos_s-1) ) );
                $value_l = trim( substr($value,  $pos_f+1  ) );
                $languages[$key] = $value_l;

            } else  $languages['default'] = $value;
        }


         $locale = getBookingLocale();

        //// $locale = 'fr_FR';

        if ( isset( $languages[$locale] ) ) $return_text = $languages[$locale];
        else                                $return_text = $languages['default'];

        $return_text = wpdev_bk_check_qtranslate($return_text, $locale);

        $return_text = wpbc_check_wpml_tags( $return_text, $locale );                        //FixIn: 5.4.5.8
        
        return $return_text;
    }

    
    /**  Register and Translate everything in [wpml]Some Text to translate[/wpml] tags.
    * 
    * @param string $text
    * @param string $locale
    * @return string
    */
    function wpbc_check_wpml_tags( $text, $locale='' ) {                        //FixIn: 5.4.5.8

        if ( $locale == '' ) {
            $locale = getBookingLocale();
        }
        if ( strlen( $locale ) > 2 ) {
            $locale = substr($locale, 0, 2 );
        }

        $is_tranlsation_exist_s = strpos( $text, '[wpml]' );
        $is_tranlsation_exist_f = strpos( $text, '[/wpml]' );

        if ( ( $is_tranlsation_exist_s !== false )  &&  ( $is_tranlsation_exist_f !== false ) )  {

            $shortcode = 'wpml';

            // Find anything between [wpml] and [/wpml] shortcodes. Magic here: [\s\S]*? - fit to any text
            preg_match_all( '/\[' . $shortcode . '\]([\s\S]*?)\[\/' . $shortcode . '\]/i', $text, $wpml_translations, PREG_SET_ORDER );               
//debuge( $wpml_translations );

            foreach ( $wpml_translations as $translation ) {                
                $text_to_replace      = $translation[0];
                $translation_to_check = $translation[1];

                if ( function_exists ( 'icl_register_string' ) ){
                    
                    if ( false ) {   // Depricated functions
                    
                      icl_register_string('Booking Calendar', 'wpbc-' . tag_escape( $translation_to_check ) , $translation_to_check );

                      //TODO: Need to  execurte this after deactivation  of plugin  or after updating of some option...
                      //icl_unregister_string ( 'Booking Calendar', 'wpbc-' . tag_escape( $translation_to_check ) );                      
                      
                      if ( function_exists ( 'icl_translate' ) ){
                          $translation_to_check = icl_translate ( 'Booking Calendar', 'wpbc-' . tag_escape( $translation_to_check ) , $translation_to_check  );
                      }
                      
                    } else { // WPML Version: 3.2
                    
                        // Help info:  do_action( 'wpml_register_single_string', string $context, string $name, string $value )
                        do_action( 'wpml_register_single_string', 'Booking Calendar', 'wpbc-' . tag_escape( $translation_to_check ) , $translation_to_check );


                        // Help info:  apply_filters( 'wpml_translate_single_string', string $original_value, string $context, string $name, string $$language_code )
                        //$translation_to_check = apply_filters( 'wpml_translate_single_string', $translation_to_check, 'Booking Calendar',  'wpbc-' . tag_escape( $translation_to_check ) );
                        $language_code = $locale;
                        $translation_to_check = apply_filters( 'wpml_translate_single_string', $translation_to_check, 'Booking Calendar',  'wpbc-' . tag_escape( $translation_to_check ), $language_code );

                    }
                }                
                $text = str_replace( $text_to_replace, $translation_to_check, $text );
            }            
        }  

        return $text;
    }
    
    
    function wpdev_bk_check_qtranslate($text, $locale=''){
        if ($locale == '') {
            $locale = getBookingLocale();
        }
        if (strlen($locale)>2) {
            $locale = substr($locale, 0 ,2);
        }

        $is_tranlsation_exist = strpos($text, '<!--:'.$locale.'-->');

        if ($is_tranlsation_exist !== false) {
            $tranlsation_end = strpos($text, '<!--:-->', $is_tranlsation_exist);

            $text = substr($text, $is_tranlsation_exist , ($tranlsation_end - $is_tranlsation_exist ) );
        }

        return $text;
    }

    
    function wpdev_bk_arraytolower( $array ){
        return unserialize(strtolower(serialize($array)));
    }


    function wpdev_bk_cost_number_format( $value ){
        $value_formated      =  apply_bk_filter('get_bk_currency_format', $value );
        return  $value_formated;
        //return number_format ( $value , 2 , '.' , ' ' );
    }


    function get_bk_current_user_id() {
        $user = wp_get_current_user();
        return ( isset( $user->ID ) ? (int) $user->ID : 0 );
    }


    // Get form content for table
    function get_booking_form_show() {
        
        $booking_form_field_active1     = get_bk_option( 'booking_form_field_active1');
        $booking_form_field_label1      = get_bk_option( 'booking_form_field_label1');
        $booking_form_field_label1      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label1 );
        
        $booking_form_field_active2     = get_bk_option( 'booking_form_field_active2');
        $booking_form_field_label2      = get_bk_option( 'booking_form_field_label2');
        $booking_form_field_label2      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label2 );
        
        $booking_form_field_active3     = get_bk_option( 'booking_form_field_active3');
        $booking_form_field_label3      = get_bk_option( 'booking_form_field_label3');
        $booking_form_field_label3      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label3 );
        
        $booking_form_field_active4     = get_bk_option( 'booking_form_field_active4');
        $booking_form_field_label4      = get_bk_option( 'booking_form_field_label4');
        $booking_form_field_label4      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label4 );
        
        $booking_form_field_active5     = get_bk_option( 'booking_form_field_active5');
        $booking_form_field_label5      = get_bk_option( 'booking_form_field_label5');
        $booking_form_field_label5      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label5 );
        
        $booking_form_field_active6     = get_bk_option( 'booking_form_field_active6');
        $booking_form_field_label6      = get_bk_option( 'booking_form_field_label6');
        $booking_form_field_label6      = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label6 );
        
        $booking_form_show = '<div style="text-align:left;word-wrap: break-word;">';
        if ($booking_form_field_active1 != 'Off')
        $booking_form_show.='<strong>'.$booking_form_field_label1.'</strong>: <span class="fieldvalue">[name]</span><br/>';
        if ($booking_form_field_active2 != 'Off')
        $booking_form_show.='<strong>'.$booking_form_field_label2.'</strong>: <span class="fieldvalue">[secondname]</span><br/>';
        if ($booking_form_field_active3 != 'Off')
        $booking_form_show.='<strong>'.$booking_form_field_label3.'</strong>: <span class="fieldvalue">[email]</span><br/>';
        if ($booking_form_field_active6 == 'On')
        $booking_form_show.='<strong>'.$booking_form_field_label6.'</strong>: <span class="fieldvalue">[visitors]</span><br/>';
        if ($booking_form_field_active4 != 'Off')
        $booking_form_show.='<strong>'.$booking_form_field_label4.'</strong>: <span class="fieldvalue">[phone]</span><br/>';
        if ($booking_form_field_active5 != 'Off')
        $booking_form_show.='<strong>'.$booking_form_field_label5.'</strong>: <br /><span class="fieldvalue">[details]</span>';
        $booking_form_show.='</div>';
            
        return $booking_form_show;
    }


    // Parse form content
    function get_form_content ($formdata, $bktype =-1 , $booking_form_show ='', $extended_params = array() ) {
//debuge($formdata, $bktype , $booking_form_show, $extended_params);
        if ($bktype == -1) {
            if (function_exists('get__default_type')) $bktype = get__default_type();
            else $bktype=1;
        }
        if ($booking_form_show==='') {
            if (function_exists ('get_booking_title')) {
                
                //RECHECK  HERE ACCORDING MULTISUSE SPECIFIC BOOKING FORM
                $booking_form_show  = get_bk_option( 'booking_form_show' );

                if (class_exists('wpdev_bk_biz_m')) {

                    $is_recehck_for_custom_form = true;
                    $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');

                    if ( ( class_exists('wpdev_bk_multiuser')) && ($is_can) ){

                          $is_recehck_for_custom_form = false;
                          $user_bk_id = apply_bk_filter('get_user_of_this_bk_resource', false, $bktype );
                          $user = wp_get_current_user();

                          if ( ($user_bk_id !== false) && ($user->ID != $user_bk_id) ){ // Only for the Super Booking Admin User, get  the booking form of the specific user

                              $is_user_super_admin = apply_bk_filter('is_user_super_admin',  $user_bk_id );
 
                              if (! $is_user_super_admin )  
                                    $booking_form_show = get_user_option( 'booking_form_show', $user_bk_id );

                              $my_booking_form_name = apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $bktype);

                              if ( ($my_booking_form_name!='standard') && (!empty($my_booking_form_name)) ) {

                                  $booking_forms_extended = get_user_option( 'booking_forms_extended', $user_bk_id);

                                  $booking_form_show  = apply_bk_filter('wpdev_get_booking_form_content', $booking_form_show, $my_booking_form_name, $booking_forms_extended );
                              }
                          } else $is_recehck_for_custom_form = true;
                    }


                    if ( $is_recehck_for_custom_form ) { // Recheck  for the CUSTOM FORM

                        $my_booking_form_name = apply_bk_filter('wpdev_get_default_booking_form_for_resource', 'standard', $bktype);
                        if ( ($my_booking_form_name!='standard') && (!empty($my_booking_form_name)) )
                            $booking_form_show  = apply_bk_filter('wpdev_get_booking_form_content', $booking_form_show, $my_booking_form_name);
                    }

                }
                
            } else // Standard
                $booking_form_show  = get_booking_form_show();

            $booking_form_show =  apply_bk_filter('wpdev_check_for_active_language', $booking_form_show );  // Translation  recehck
            
        }

//debuge($formdata, $bktype, $booking_form_show);

        $formdata_array = explode('~',$formdata);
        $formdata_array_count = count($formdata_array);
        $email_adress='';
        $name_of_person = '';
        $coupon_code = '';
        $secondname_of_person = '';
        $visitors_count = 1;
        $select_box_selected_items = array();
        $check_box_selected_items = array();
        $all_fields_array = array();
        $all_fields_array_without_types = array();
        $checkbox_value=array();
     
        for ( $i=0 ; $i < $formdata_array_count ; $i++) {
            
            if ( empty( $formdata_array[$i] ) ) {
                continue;
            }
            
            $elemnts = explode('^',$formdata_array[$i]);

            $type = $elemnts[0];
            $element_name = $elemnts[1];
            $value = $elemnts[2];            
            $value = nl2br($value);                                             // Add BR instead if /n elements
            
            $count_pos = strlen( $bktype );

            $type_name = $elemnts[1];
            $type_name = str_replace('[]','',$type_name);
            if ($bktype == substr( $type_name,  -1*$count_pos ) ) $type_name = substr( $type_name, 0, -1*$count_pos ); // $type_name = str_replace($bktype,'',$elemnts[1]);

            //if ( ($type_name == 'email') || ($type == 'email')  )               $email_adress = $value;                       //FixIn: 6.0.1.9
            if ( ( ($type_name == 'email') || ($type == 'email')  ) && ( empty($email_adress) )   )    $email_adress = $value;  //FixIn: 6.0.1.9
            if ( ($type_name == 'coupon') || ($type == 'coupon')  )             $coupon_code = $value;
            if ( ($type_name == 'name') || ($type == 'name')  )                 $name_of_person = $value;
            if ( ($type_name == 'secondname') || ($type == 'secondname')  )     $secondname_of_person = $value;
            if ( ($type_name == 'visitors') || ($type == 'visitors')  )         $visitors_count = $value;

            if ($type == 'checkbox') {
//debuge($type_name , $type,   $element_name, $value);
//       children   checkbox children11[]    true
                if ($value == 'true') {
                    $value = __('yes' ,'booking');
                }

                if ($value == 'false') {
                    $value = __('no' ,'booking');
                }

                if  ( $value !='' )
                    if ( ( isset($checkbox_value[ str_replace('[]','',(string) $element_name) ]) ) && ( is_array($checkbox_value[ str_replace('[]','',(string) $element_name) ]) ) ) {
                        $checkbox_value[ str_replace('[]','',(string) $element_name) ][] = $value;
                    } else {
                        //if ($value != __('yes' ,'booking') )
                            $checkbox_value[ str_replace('[]','',(string) $element_name) ] = array($value);
                        //else
                            //$checkbox_value[ str_replace('[]','',(string) $element_name) ] = 'checkbox';
                    }

                $value = $value .' ' . '['. $type_name .']';
            }

            if ( ( $type == 'select-one') || ( $type == 'select-multiple' ) ) { // add all select box selected items to return array
                $select_box_selected_items[$type_name] = $value;
            }

            if ( ($type == 'checkbox') && (isset($checkbox_value)) ) {
                if (isset(  $checkbox_value[ str_replace('[]','',(string) $element_name) ] )) {
                    if (is_array(  $checkbox_value[ str_replace('[]','',(string) $element_name) ] ))
                        $current_checkbox_value = implode(', ', $checkbox_value[ str_replace('[]','',(string) $element_name) ] );
                    else
                        $current_checkbox_value = $checkbox_value[ str_replace('[]','',(string) $element_name) ] ;
                } else {
                    $current_checkbox_value = '';
                }
                $all_fields_array[ str_replace('[]','',(string) $element_name) ] = $current_checkbox_value;
                $all_fields_array_without_types[ substr(   str_replace('[]','',(string) $element_name), 0 , -1*strlen( $bktype ) )  ] = $current_checkbox_value;

                $check_box_selected_items[$type_name] = $current_checkbox_value;
            } else {
                $all_fields_array[ str_replace('[]','',(string) $element_name) ] = $value;
                $all_fields_array_without_types[ substr(   str_replace('[]','',(string) $element_name), 0 , -1*strlen( $bktype ) )   ] = $value;
            }
            $booking_form_show = str_replace( '['. $type_name .']', $value ,$booking_form_show);
        }


        // Remove all shortcodes, which is not replaced early.
        $booking_form_show = preg_replace ('/[\s]{0,}\[[a-zA-Z0-9.,-_]{0,}\][\s]{0,}/', '', $booking_form_show);

        if (! isset($all_fields_array_without_types[ 'booking_resource_id'  ])) $all_fields_array_without_types[ 'booking_resource_id'  ] = $bktype;
        if (! isset($all_fields_array_without_types[ 'resource_id'  ]))         $all_fields_array_without_types[ 'resource_id'  ] = $bktype;
        if (! isset($all_fields_array_without_types[ 'type_id'  ]))             $all_fields_array_without_types[ 'type_id'  ] = $bktype;

        if (! isset($all_fields_array_without_types[ 'type'  ]))                $all_fields_array_without_types[ 'type'  ] = $bktype;
        if (! isset($all_fields_array_without_types[ 'resource'  ]))            $all_fields_array_without_types[ 'resource'  ] = $bktype;

        foreach ($extended_params as $key_param=>$value_param) {
            if (! isset($all_fields_array_without_types[  $key_param  ]))            $all_fields_array_without_types[ $key_param  ] = $value_param;
        }

        $return_array =   array('content' => $booking_form_show,
                                'email' => $email_adress,
                                'name' => $name_of_person,
                                'secondname' => $secondname_of_person ,
                                'visitors' => $visitors_count ,
                                'coupon'=>$coupon_code ,
                                '_all_' => $all_fields_array,
                                '_all_fields_'=>$all_fields_array_without_types
                               ) ;

        foreach ($select_box_selected_items as $key=>$value) {
            if (! isset($return_array[$key])) {
                $return_array[$key] = $value;
            }
        }
        foreach ($check_box_selected_items as $key=>$value) {
            if (! isset($return_array[$key])) {
                $return_array[$key] = $value;
            }
        }
//debuge($return_array);
        return $return_array ;

    }


    function parse_calendar_options($bk_otions ){
        
            if (empty($bk_otions)) return false;

            /* $matches    structure:
             * Array
                (
                    [0] => Array
                        (
                            [0] => {calendar months="6" months_num_in_row="2" width="284px" cell_height="40px"}, 
                            [1] => calendar
                            [2] => months="6" months_num_in_row="2" width="284px" cell_height="40px"
                        )

                    [1] => Array
                        (
                            [0] => {select-day condition="weekday" for="5" value="3"}, 
                            [1] => select-day
                            [2] => condition="weekday" for="5" value="3"
                        )
                     .....
                )
             */
//debuge($bk_otions);                        
            $pattern_to_search='%\s*{([^\s]+)\s*([^}]+)\s*}\s*[,]?\s*%';
            preg_match_all($pattern_to_search, $bk_otions, $matches, PREG_SET_ORDER);
            foreach ($matches as $value) {
                if ($value[1] == 'calendar') {
                    $paramas = $value[2];
                    $paramas = trim($paramas);
                    $paramas = explode(' ',$paramas);
                    $options = array();
                    foreach ($paramas as $vv) {
                        if (! empty($vv)) {
                            $vv = trim($vv);
                            $vv = explode('=',$vv);    
                            $options[$vv[0]] = trim($vv[1]);
                        }
                    }
                    if (count($options)==0) return false;
                    else                    return $options;
                }
            }
        // We are do  not have the "calendar" options in the shortcode    
        return false;
    }
    


    // Get version
    function get_bk_version(){ 
        $version = 'free';
        if (class_exists('wpdev_bk_personal'))     $version = 'personal';
        if (class_exists('wpdev_bk_biz_s'))        $version = 'biz_s';
        if (class_exists('wpdev_bk_biz_m'))        $version = 'biz_m';
        if (class_exists('wpdev_bk_biz_l'))        $version = 'biz_l';
        return $version;
    }
    

    /**
     * Ceck  if user accidentially update Booking Calendar Paid version  to Free
     * 
     * @return true|false
     */
    function wpbc_is_updated_paid_to_free() {
        
        if ( ( wpbc_is_table_exists('bookingtypes') ) && ( ! class_exists('wpdev_bk_personal') )  ) 
            return  true;
        else
            return false;                    
    }
    
    ////////////////////////////////////////////////////////////////////////////
    function wpbc_get_ver_sufix() {               
        if( strpos( strtolower(WPDEV_BK_VERSION) , 'multisite') !== false  ) {
            $v_type = '-multi';                         
        } else if( strpos( strtolower(WPDEV_BK_VERSION) , 'develop') !== false  ) {
            $v_type = '-dev';
        } else {
            $v_type = '';
        }  
        $v = '';
        if (class_exists('wpdev_bk_personal'))  $v = 'ps'. $v_type;
        if (class_exists('wpdev_bk_biz_s'))     $v = 'bs'. $v_type;
        if (class_exists('wpdev_bk_biz_m'))     $v = 'bm'. $v_type;
        if (class_exists('wpdev_bk_biz_l'))     $v = 'bl'. $v_type;
        if (class_exists('wpdev_bk_multiuser')) $v = '';        
        return $v ;
    }
    
    
    function wpbc_up_link() {
        if ( ! wpdev_bk_is_this_demo() ) 
             $v = wpbc_get_ver_sufix();
        else $v = '';
        return 'http://wpbookingcalendar.com/' . ( ( empty($v) ) ? '' : 'upgrade-' . $v  . '/' ) ;
    }
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // DB - cheking if table, field or index exists
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * Check if table exist
     * 
     * @global type $wpdb
     * @param string $tablename
     * @return 0|1
     */
    function wpbc_is_table_exists( $tablename ) {
        
        global $wpdb;
        
        if ( (! empty($wpdb->prefix) ) && ( strpos($tablename, $wpdb->prefix) === false ) ) 
            $tablename = $wpdb->prefix . $tablename ;
        
        $sql_check_table = $wpdb->prepare("SHOW TABLES LIKE %s" , $tablename ); //FixIn 5.4.3
        
        $res = $wpdb->get_results( $sql_check_table );
        
        return count($res);                                                     //FixIn 5.4.3
        /*
        $sql_check_table = $wpdb->prepare("
            SELECT COUNT(*) AS count
            FROM information_schema.tables
            WHERE table_schema = '". DB_NAME ."'
            AND table_name = %s " , $tablename );

        $res = $wpdb->get_results( $sql_check_table );
        return $res[0]->count;*/
    }

    
    /**
     * Check if table exist
     * 
     * @global type $wpdb
     * @param string $tablename
     * @param type $fieldname
     * @return 0|1
     */
    function wpbc_is_field_in_table_exists( $tablename , $fieldname) {
        global $wpdb;
        if ( (! empty($wpdb->prefix) ) && ( strpos($tablename, $wpdb->prefix) === false ) ) $tablename = $wpdb->prefix . $tablename ;
        $sql_check_table = "SHOW COLUMNS FROM {$tablename}" ;

        $res = $wpdb->get_results( $sql_check_table );

        foreach ($res as $fld) {
            if ($fld->Field == $fieldname) return 1;
        }

        return 0;
    }

    
    /**
     * Check if index exist
     * 
     * @global type $wpdb
     * @param string $tablename
     * @param type $fieldindex
     * @return 0|1
     */
    function wpbc_is_index_in_table_exists( $tablename , $fieldindex) {
        global $wpdb;
        if ( (! empty($wpdb->prefix) ) && ( strpos($tablename, $wpdb->prefix) === false ) ) $tablename = $wpdb->prefix . $tablename ;
        $sql_check_table = $wpdb->prepare("SHOW INDEX FROM {$tablename} WHERE Key_name = %s", $fieldindex );       
        $res = $wpdb->get_results( $sql_check_table );
        if (count($res)>0) return 1;
        else               return 0;
    }

    
    
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Dates 
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /*
     * Get SQL for inserttion Dates into DB
     */
    function wpbc_get_SQL_to_insert_dates( $dates_in_diff_formats , $is_approved_dates, $booking_id ) {

        $my_dates = $dates_in_diff_formats['array'];
        $start_time = $dates_in_diff_formats['start_time'];
        $end_time = $dates_in_diff_formats['end_time'];

        $i=0;
        $insert = '';
        $my_date_previos = '';
        $my_dates4emeil = '';
        foreach ($my_dates as $my_date) {
            $i++;          // Loop through all dates
            $my_date= str_replace('-','.',$my_date);

            if (strpos($my_date,'.')!==false) {

                if ( get_bk_option( 'booking_recurrent_time' ) !== 'On') {
                        $my_date = explode('.',$my_date);
                        if ($i == 1) {
                            if ( (! isset($start_time[0])) || (empty($start_time[0])) ) $start_time[0] = '00';
                            if ( (! isset($start_time[1])) || (empty($start_time[1])) ) $start_time[1] = '00';                        
                            if ( ($start_time[0] == '00') && ($start_time[1] == '00') ) $start_time[2] = '00';

                            $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $start_time[0], $start_time[1], $start_time[2] );
                        }elseif ($i == count($my_dates)) {

                            if ( (! isset($end_time[0])) || (empty($end_time[0])) ) $end_time[0] = '00';
                            if ( (! isset($end_time[1])) || (empty($end_time[1])) ) $end_time[1] = '00';                        
                            if ( ($end_time[0] == '00') && ($end_time[1] == '00') ) $end_time[2] = '00';

                            $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $end_time[0], $end_time[1], $end_time[2] );
                        }else {
                            $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], '00', '00', '00' );
                        }
                        $my_dates4emeil .= $date . ',';
                        if ( !empty($insert) ) $insert .= ', ';
                        $insert .= "('$booking_id', '$date', '$is_approved_dates' )";
                } else {
                        if ($my_date_previos  == $my_date) continue; // escape for single day selections.

                        $my_date_previos  = $my_date;
                        $my_date = explode('.',$my_date);
                        $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $start_time[0], $start_time[1], $start_time[2] );
                        $my_dates4emeil .= $date . ',';
                        if ( !empty($insert) ) $insert .= ', ';
                        $insert .= "('$booking_id', '$date', '$is_approved_dates' )";

                        $date = sprintf( "%04d-%02d-%02d %02d:%02d:%02d", $my_date[0], $my_date[1], $my_date[2], $end_time[0], $end_time[1], $end_time[2] );
                        $my_dates4emeil .= $date . ',';
                        if ( !empty($insert) ) $insert .= ', ';
                        $insert .= "('$booking_id', '$date', '$is_approved_dates' )";
                }

            }
        }
    //    $my_dates4emeil = substr($my_dates4emeil,0,-1);
    //    
    //    return array($insert, $my_dates4emeil) ;
        return $insert;
    }


    /*
     * Get the dates in different formats.
     * 
     * Reurn:
     * array(
              'string' => "30.02.2014, 31.02.2014, 01.03.2014" 
            , 'array'  => array("2014-02-30", "2014-02-31", ....
            , 'start_time'  => array('00','00','00');
            , 'end_time'    => array('00','00','00');
        );
     */
    function wpbc_get_dates_in_diff_formats( $str_dates__dd_mm_yyyy, $booking_type, $booking_form_data ){

        $str_dates__dd_mm_yyyy = str_replace('|',',',$str_dates__dd_mm_yyyy);       // Check  this for some old versions of plugin

        if ( strpos($str_dates__dd_mm_yyyy,' - ') !== false ) {                     // Recheck for any type of Range Days Formats
            $arr_check_in_out_dates = explode(' - ', $str_dates__dd_mm_yyyy );
            $str_dates__dd_mm_yyyy = createDateRangeArray( $arr_check_in_out_dates[0], $arr_check_in_out_dates[1] );
        }    


        $days_array = explode(',', $str_dates__dd_mm_yyyy);                         // Create dates Array
        $only_days  = array();

        foreach ($days_array as $new_day) {

            if (! empty($new_day)) {

                $new_day = trim( $new_day );       

                $new_day = str_replace( '-', '.', $new_day);

                $new_day = explode( '.', $new_day);

                $only_days[] = sprintf( "%04d-%02d-%02d", intval($new_day[2]), intval($new_day[1]), intval($new_day[0]) );            
            }
        }

        sort($only_days);                                                           // Sort Dates


        // Get Times from booking form if these fields exist
        $start_end_time = wpbc_get_times_in_form($booking_form_data, $booking_type); 
        if ( $start_end_time !== false ) {
            $start_time = $start_end_time[0];       // array('00','00','01');
            $end_time   = $start_end_time[1];       // array('00','00','01');

            if ( count( $only_days ) == 1 )         // add end date if selected 1 day only and times is exist
                $only_days[]=$only_days[0];
        } else {
            $start_time = array('00','00','00');
            $end_time   = array('00','00','00');
        }



        return array(
              'string' => $str_dates__dd_mm_yyyy    // dd_mm_yyyy
            , 'array'  => $only_days  
            , 'start_time'  => $start_time
            , 'end_time'    => $end_time
        );
    }


    /*
     * Get  the Times from  the booking form if these form fields exist
     * If no then return - false
     * 
     * Return Times in format:
     *  array ( array('00','00','01'), array('00','00','01') )
     * 
     */
    function wpbc_get_times_in_form($booking_form_data, $booking_type){

        $is_time_exist = false;

        if ( ! class_exists('wpdev_bk_biz_s') )         
            return $is_time_exist;

        $start_time = $end_time = '00:00:00';


        if ( strpos($booking_form_data,'rangetime' . $booking_type ) !== false ) {   

            // ~checkbox^mymultiple4^~checkbox^rangetime4^ ~checkbox^rangetime4^12:00 - 13:00~ checkbox^rangetime4^~checkbox^rangetime4^~text^name4^Jonny~ 

            // Types of the conditions
            $f_type =  '[^\^]*';     
            $f_name =  'rangetime[\d]*[\[\]]{0,2}';     
            $f_value =  '[\s]*([0-9:]*)[\s]*\-[\s]*([0-9:]*)[\s]*[^~]*';     

            $pattern_to_search='%[~]?'.$f_type.'\^'.$f_name.'\^'.$f_value.'[~]?%';

            preg_match_all($pattern_to_search, $booking_form_data, $matches, PREG_SET_ORDER);
            /* Exmaple of $matches:

             Array (  [0] => Array (
                            [0] => ~checkbox^rangetime4^13:00 - 14:00~
                            [1] => 13:00
                            [2] => 14:00
                                    ) )
            */   

            if (count($matches)>0){

                    $start_time = get_time_array_checked_on_AMPM( trim($matches[0][1]) );
                    $start_time[2]='01';

                    $end_time   = get_time_array_checked_on_AMPM( trim($matches[0][2]) );
                    $end_time[2]='02';

                    $is_time_exist = true; 

            } else {
                $start_time = array('00','00','01');
                $end_time   = array('00','00','02');
            }

        } else {

            if ( strpos($booking_form_data,'starttime' . $booking_type ) !== false ) {      // Get START TIME From form request
                $pos1 = strpos($booking_form_data,'starttime' . $booking_type );            // Find start time pos
                $pos1 = strpos($booking_form_data,'^',$pos1)+1;                             // Find TIME pos
                $pos2 = strpos($booking_form_data,'~',$pos1);                               // Find TIME length
                if ($pos2 === false) $pos2 = strlen($booking_form_data);
                $pos2 = $pos2-$pos1;
                $start_time = substr( $booking_form_data, $pos1,$pos2)  ;
                $start_time = explode(':',$start_time);
                if ($start_time == '') $start_time = '00:00';
                $start_time[2]='01';
            } else 
                $start_time = explode(':',$start_time);

            if ( strpos($booking_form_data,'endtime' . $booking_type ) !== false ) {    // Get END TIME From form request
                $pos1 = strpos($booking_form_data,'endtime' . $booking_type );          // Find start time pos
                $pos1 = strpos($booking_form_data,'^',$pos1)+1;                         // Find TIME pos
                $pos2 = strpos($booking_form_data,'~',$pos1);                           // Find TIME length
                if ($pos2 === false) $pos2 = strlen($booking_form_data);
                $pos2 = $pos2-$pos1;
                $end_time = substr( $booking_form_data, $pos1,$pos2)  ;
                if ($end_time == '') $end_time = '00:00';

                $is_time_exist = true; 

                $end_time = explode(':',$end_time);
                $end_time[2]='02';
            } else 
                $end_time = explode(':',$end_time);

            if ( strpos($booking_form_data,'durationtime' . $booking_type ) !== false ) {   // Get END TIME From form request
                $pos1 = strpos($booking_form_data,'durationtime' . $booking_type );         // Find start time pos
                $pos1 = strpos($booking_form_data,'^',$pos1)+1;                             // Find TIME pos
                $pos2 = strpos($booking_form_data,'~',$pos1);                               // Find TIME length
                if ($pos2 === false) $pos2 = strlen($booking_form_data);
                $pos2 = $pos2-$pos1;
                $end_time = substr( $booking_form_data, $pos1,$pos2)  ;

                $is_time_exist = true; 

                $end_time = explode(':',$end_time);

                // Here we are get start time and add duration for end time
                $new_end_time = mktime(intval($start_time[0]), intval($start_time[1]));
                $new_end_time = $new_end_time + $end_time[0]*60*60 + $end_time[1]*60;
                $end_time = date('H:i',$new_end_time);
                if ($end_time == '00:00') $end_time = '23:59';
                $end_time = explode(':',$end_time);
                $end_time[2]='02';
            }

        }

        if ( $is_time_exist )
            return array( $start_time, $end_time );
        else 
            return  false;
    }    
    

    // Get start and end time from booking form data
    // Depricated: in 5.2 ;  New function - wpbc_get_times_in_form
    function get_times_from_bk_form($sdform, $my_dates, $bktype){

        $start_time = $end_time = '00:00:00';
        if ( class_exists('wpdev_bk_biz_s')) {

        if ( strpos($sdform,'rangetime' . $bktype ) !== false ) {   // Get START TIME From form request
            //  Example of $sdform content.
            // ~checkbox^mymultiple4^~checkbox^rangetime4^ ~checkbox^rangetime4^12:00 - 13:00~ checkbox^rangetime4^~checkbox^rangetime4^~text^name4^Jonny~            
//debuge($sdform);            
            // Types of the conditions
            $f_type =  '[^\^]*';    // Any Field types
            $f_name =  'rangetime[\d]*[\[\]]{0,2}';    // Any Field types
            $f_value =  '[\s]*([0-9:]*)[\s]*\-[\s]*([0-9:]*)[\s]*[^~]*';    // Any Field types

            $pattern_to_search='%[~]?'.$f_type.'\^'.$f_name.'\^'.$f_value.'[~]?%';

            preg_match_all($pattern_to_search, $sdform, $matches, PREG_SET_ORDER);
            /* Exmaple of $matches:
             * 
             *
             Array (  [0] => Array (
                            [0] => ~checkbox^rangetime4^13:00 - 14:00~
                            [1] => 13:00
                            [2] => 14:00
                                    ) )
            */
//debuge($matches);            
            if (count($matches)>0){

                    $start_time = get_time_array_checked_on_AMPM( trim($matches[0][1]) );
                    $start_time[2]='01';

                    $end_time   = get_time_array_checked_on_AMPM( trim($matches[0][2]) );
                    $end_time[2]='02';
                    
                    if ( count($my_dates) == 1 ) { // add end date if someone select only 1 day with time range
                        $my_dates[]=$my_dates[0];
                    }                    
            } else {
                $start_time=array('00','00','01');
                $end_time=array('00','00','02');
            }

        } else {

            if ( strpos($sdform,'starttime' . $bktype ) !== false ) {   // Get START TIME From form request
                $pos1 = strpos($sdform,'starttime' . $bktype );  // Find start time pos
                $pos1 = strpos($sdform,'^',$pos1)+1;             // Find TIME pos
                $pos2 = strpos($sdform,'~',$pos1);               // Find TIME length
                if ($pos2 === false) $pos2 = strlen($sdform);
                $pos2 = $pos2-$pos1;
                $start_time = substr( $sdform, $pos1,$pos2)  ;
                $start_time = explode(':',$start_time);
                if ($start_time == '') $start_time = '00:00';
                $start_time[2]='01';
            } else  $start_time = explode(':',$start_time);

            if ( strpos($sdform,'endtime' . $bktype ) !== false ) {    // Get END TIME From form request
                $pos1 = strpos($sdform,'endtime' . $bktype );    // Find start time pos
                $pos1 = strpos($sdform,'^',$pos1)+1;             // Find TIME pos
                $pos2 = strpos($sdform,'~',$pos1);               // Find TIME length
                if ($pos2 === false) $pos2 = strlen($sdform);
                $pos2 = $pos2-$pos1;
                $end_time = substr( $sdform, $pos1,$pos2)  ;
                if ($end_time == '') $end_time = '00:00';

                if ( count($my_dates) == 1 ) { // add end date if someone select only 1 day with time range
                    $my_dates[]=$my_dates[0];
                }
                $end_time = explode(':',$end_time);
                $end_time[2]='02';
            } else  $end_time = explode(':',$end_time);

            if ( strpos($sdform,'durationtime' . $bktype ) !== false ) {    // Get END TIME From form request
                $pos1 = strpos($sdform,'durationtime' . $bktype );    // Find start time pos
                $pos1 = strpos($sdform,'^',$pos1)+1;             // Find TIME pos
                $pos2 = strpos($sdform,'~',$pos1);               // Find TIME length
                if ($pos2 === false) $pos2 = strlen($sdform);
                $pos2 = $pos2-$pos1;
                $end_time = substr( $sdform, $pos1,$pos2)  ;

                if ( count($my_dates) == 1 ) { // add end date if someone select only 1 day with time range
                    $my_dates[]=$my_dates[0];
                }
                $end_time = explode(':',$end_time);

                // Here we are get start time and add duration for end time
                $new_end_time = mktime(intval($start_time[0]), intval($start_time[1]));
                $new_end_time = $new_end_time + $end_time[0]*60*60 + $end_time[1]*60;
                $end_time = date('H:i',$new_end_time);
                if ($end_time == '00:00') $end_time = '23:59';
                $end_time = explode(':',$end_time);
                $end_time[2]='02';

            }
        }

        }
        return array($start_time, $end_time, $my_dates );
    }

    
    // Change date format
    function change_date_format( $mydates ) {
        if (empty($mydates)) return '';
        $mydates = explode(',',$mydates);

        $mydates_result = '';

        $date_format = get_bk_option( 'booking_date_format');
        $time_format = get_bk_option( 'booking_time_format');
        if ( $time_format !== false  ) $time_format = ' ' . $time_format;
        else                           $time_format='';

        if ($date_format == '') $date_format = "d.m.Y";

        foreach ($mydates as $dt) {
            $dt = trim($dt);
            $dta = explode(' ',$dt);
            $tms = $dta[1];
            $tms = explode(':' , $tms);
            $dta = $dta[0];
            $dta = explode('-',$dta);

            $date_format_now = $date_format . $time_format;
            if ($tms == array('00','00','00'))     $date_format_now = $date_format;

            //   H        M        S        M        D        Y
            $mydates_result .= date_i18n($date_format_now, mktime($tms[0], $tms[1], $tms[2], $dta[1], $dta[2], $dta[0])) . ', ';
        }

        return substr($mydates_result,0,-2);
    }


    // Get dates 4 emeil
    function get_dates_str ($approved_id_str) {
        global $wpdb;
        $dates_approve = $wpdb->get_results( 
                "SELECT DISTINCT booking_date FROM {$wpdb->prefix}bookingdates WHERE  booking_id IN ({$approved_id_str}) ORDER BY booking_date" );
        $dates_str = '';
        // loop with all dates which is selected by someone
        foreach ($dates_approve as $my_date) {

            if ($dates_str != '') $dates_str .= ', ';
            $dates_str .= $my_date->booking_date;//$my_date[1] . '.' .$my_date[2] . '.' . $my_date[0];
        }

        return $dates_str;
    }


    // check if AM/PM exist and replace it to havemilitary format
    function get_time_array_checked_on_AMPM($start_time) {

        $start_time = trim($start_time);
        $start_time_plus = 0;

        if ( strpos( strtolower( $start_time) ,'am' ) !== false ) {
            $start_time = str_replace('am', '',  $start_time );
            $start_time = str_replace('AM', '',  $start_time );
        }

        if ( strpos( strtolower( $start_time) ,'pm' ) !== false ) {
            $start_time = str_replace('pm', '',  $start_time );
            $start_time = str_replace('PM', '',  $start_time );
            $start_time_plus = 12;
        }

        $start_time = explode(':',trim($start_time));

        $start_time[0] = $start_time[0] + $start_time_plus;
        $start_time[1] = $start_time[1] + 0;

        if ($start_time[0] < 10 ) $start_time[0] = '0' . $start_time[0];
        if ($start_time[1] < 10 ) $start_time[1] = '0' . $start_time[1];

        return $start_time;
    }

        
    // Return the number of days between the two dates:
    function getbkDatesDiff ($d1, $d2) {
        return round( (strtotime($d1)-strtotime($d2))/86400);
    }

    
    function getbkSortedDates($booking_days) {

        if (strpos($booking_days,' - ')!== FALSE) {
            $booking_days =explode(' - ', $booking_days );
            $booking_days = createDateRangeArray($booking_days[0],$booking_days[1]);
        }

        $days_array     = explode(',', $booking_days);
        $only_days = array();

        foreach ($days_array as $new_day) {
            if (! empty($new_day)) {
                $new_day=trim($new_day);
                if (strpos($new_day, '.')!==false) $new_day = explode('.',$new_day);
                else                               $new_day = explode('-',$new_day);
                $only_days[] = $new_day[2] .'-' . $new_day[1] .'-' . $new_day[0] . ' 00:00:00';
            }
        }
        if (! empty($only_days)) {
            sort($only_days);
        }
        return $only_days;
    }
    
    // Check if dates in range format and fix it to the coma seperated dates
    function createDateRangeArray($strDateFrom,$strDateTo) {

        $aryRange=array();
        $strDateFrom = explode('.', $strDateFrom);
        $strDateTo = explode('.', $strDateTo);
        $iDateFrom = mktime( 1, 0, 0, ($strDateFrom[1]+0), ($strDateFrom[0]+0), ($strDateFrom[2]+0));
        $iDateTo   = mktime( 1, 0, 0, ($strDateTo[1] + 0), ($strDateTo[0] + 0), ($strDateTo[2] + 0));

        if ($iDateTo>=$iDateFrom) {
            array_push($aryRange,date('d.m.Y',$iDateFrom)); // first entry

            while ($iDateFrom<$iDateTo) {
                $iDateFrom+=86400; // add 24 hours
                array_push($aryRange,date('d.m.Y',$iDateFrom));
            }
        }
        $aryRange = implode(', ', $aryRange);
        return $aryRange;
    }


    // Check if nowday is tommorow from previosday
    function is_next_day($nowday, $previosday) {

        $nowday_d = (date('m.d.Y',  mysql2date('U', $nowday ))  );
        $prior_day = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        if ($prior_day == $nowday_d)    return true;                // if its the same date


        $previos_array = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        $previos_array = explode('.',$previos_array);
        $prior_day =  date('m.d.Y' , mktime(0, 0, 0, $previos_array[0], ($previos_array[1]+1), $previos_array[2] ));


        if ($prior_day == $nowday_d)    return true;                // zavtra
        else                            return false;               // net
    }

    // Check if nowday is tommorow from previosday
    function get_tommorow_day($nowday) {

        $nowday_d = (date('m.d.Y',  mysql2date('U', $nowday ))  );
        $previos_array = explode('.',$nowday_d);
        $tommorow_day =   mktime(0, 0, 0, $previos_array[0], ($previos_array[1]+1), $previos_array[2] ) ;
        return $tommorow_day;
    }


    function wpdevbkGetDaysBetween($sStartDate, $sEndDate){
        // Firstly, format the provided dates.
        // This function works best with YYYY-MM-DD
        // but other date formats will work thanks
        // to strtotime().
        $sStartDate = gmdate("Y-m-d", strtotime($sStartDate));
        $sEndDate = gmdate("Y-m-d", strtotime($sEndDate));

        // Start the variable off with the start date
        $aDays[] = $sStartDate;

        // Set a 'temp' variable, sCurrentDate, with
        // the start date - before beginning the loop
        $sCurrentDate = $sStartDate;

        // While the current date is less than the end date
        while($sCurrentDate < $sEndDate){
            // Add a day to the current date
            $sCurrentDate = gmdate("Y-m-d", strtotime("+1 day", strtotime($sCurrentDate)));

            // Add this new day to the aDays array
            $aDays[] = $sCurrentDate;
        }
        // Once the loop has finished, return the
        // array of days.
        return $aDays;
    }

    // Check if nowday is tommorow from previosday
    function is_today_date($some_day) {

        $some_day_d = (date('m.d.Y',  mysql2date('U', $some_day ))  );
        $today_day = (date('m.d.Y')  );
        if ($today_day == $some_day_d)    return true;                // if its the same date
        else                              return false;               // net

    }


    // Get days in short format view
    function get_dates_short_format( $days ) {  // $days - string with comma seperated dates

        if (empty($days)) return '';
        
        $days = explode(',', $days);

        $previosday = false;
        $result_string = '';
        $last_show_day = '';

        foreach ($days as $day) {
            $is_fin_at_end = false;
            if ($previosday !== false) {            // Not first day
                if ( is_next_day($day, $previosday) ) {
                    $previosday = $day;                        // Set previos day for next loop
                    $is_fin_at_end = true;
                } else {
                    if ($last_show_day !== $previosday) {      // check if previos day was show or no
                        $result_string .= ' - ' . change_date_format($previosday); // assign in needed format this day
                    }
                    $result_string .= ', ' . change_date_format($day); // assign in needed format this day
                    $previosday = $day;                        // Set previos day for next loop
                    $last_show_day = $day;
                }
            } else {                                 // First day
                $result_string = change_date_format($day); // assign in needed format first day
                $last_show_day = $day;
                $previosday = $day;                        // Set previos day for next loop
            }
        }

        if ($is_fin_at_end) {
            $result_string .= ' - ' . change_date_format($day);
        } // assign in needed format this day

        return $result_string;
    }

    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Replace the shortcodes in the form by values from array
    function replace_bk_shortcodes_in_form($form, $field_values=array(), $is_delete_unknown_shortcodes = false) {

        $new_form = $form;

        // Patern for searching of the shortcodes in some form
        $any_shortcodes = '[a-zA-Z][0-9a-zA-Z:._-]*';
        $regex = '%\[\s*(' . $any_shortcodes . ')\s*\]%';

        // Search  any shortcodes in the $form
        preg_match_all($regex, $form, $matches, PREG_PATTERN_ORDER);   // PREG_PATTERN_ORDER, PREG_SET_ORDER, PREG_OFFSET_CAPTURE

        // Loop  all found shortcodes
        if (isset($matches[1])) {
                foreach ($matches[1] as $key=>$field) {

                    //$field             // secondname
                    //$matches[0][$key]  // [secondname]
                    //$matches[1][$key]  // secondname

                    if (isset($field_values[$field])) $replace_value = $field_values[$field];
                    else {
                        if ($is_delete_unknown_shortcodes) $replace_value = '';
                        else $replace_value = $matches[0][$key];
                    }

                    $new_form = str_replace( $matches[0][$key] , $replace_value, $new_form);
                }
        }
        return  $new_form;
    }


    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Emails
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    /**
     * Check email and format  it
     * 
     * @param string $emails
     * @return string
     */
    function wpbc_validate_emails( $emails ) {

        $emails = str_replace(';', ',', $emails);
        
	if ( !is_array( $emails ) )
		$emails = explode( ',', $emails );

        $emails_list = array();
	foreach ( (array) $emails as $recipient ) {
            
            // Break $recipient into name and address parts if in the format "Foo <bar@baz.com>"
            $recipient_name = '';
            if( preg_match( '/(.*)<(.+)>/', $recipient, $matches ) ) {
                if ( count( $matches ) == 3 ) {
                    $recipient_name = $matches[1];
                    $recipient = $matches[2];                 
                }
            } else {                
                // Check about correct  format  of email
                if( preg_match( '/([\w\.\-_]+)?\w+@[\w-_]+(\.\w+){1,}/im', $recipient, $matches ) ) {
                    $recipient = $matches[0];
                }             
            }

            $recipient_name = str_replace('"', '', $recipient_name);
            $recipient_name = trim( wp_specialchars_decode( esc_html( stripslashes( $recipient_name ) ), ENT_QUOTES ) );
            
            $emails_list[] =   ( empty( $recipient_name ) ? '' : $recipient_name . ' '  )
                               . '<' . sanitize_email( $recipient ) . '>';		
	}
        
        $emails_list = implode( ',', $emails_list );

        return $emails_list;
    }    
    
    /**
     * Convert email  address to the correct  format  like  "Jony Smith" <smith@server.com> - tp  prevent "WordPress" title in email.
     * @param string $wpbc_mail - just  simple email
     * @param type $booking_form_show - array with  name and secondname of the person - title
     * @return string - formated email.
     */
    function wpbc_format_email_address( $wpbc_mail, $booking_form_show = array() ) {
        
        //FixIn:5.4.4
        $wpbc_email_title =  ((isset($booking_form_show['firstname']))?$booking_form_show['firstname'].' ':'')
                            .((isset($booking_form_show['name']))?$booking_form_show['name'].' ':'')
                            .((isset($booking_form_show['lastname']))?$booking_form_show['lastname'].' ':'')
                            .((isset($booking_form_show['secondname']))?$booking_form_show['secondname'].' ':'');
        $wpbc_email_title = ( ( empty($wpbc_email_title ) ) ? __('Booking system' ,'booking') : substr( $wpbc_email_title, 0 , -1 ) );
        
        $wpbc_email_title = str_replace('"', '', $wpbc_email_title);
        $wpbc_email_title = trim( wp_specialchars_decode( esc_html( stripslashes( $wpbc_email_title ) ), ENT_QUOTES ) );
        
        $wpbc_mail = wpbc_validate_emails( $wpbc_email_title . ' <' .  $wpbc_mail . '> ' );
        
        return $wpbc_mail;
        
        /*        
        //FixIn:5.4.3
        if  (  WP_BK_USE_TITLES_IN_EMAILS && 
              ( strpos($wpbc_mail, '<') === false ) &&
              ( strpos($wpbc_mail, ';') === false ) &&
              ( strpos($wpbc_mail, ',') === false )  
            ) {
            $wpbc_email_title =  ((isset($booking_form_show['firstname']))?$booking_form_show['firstname'].' ':'')
                                .((isset($booking_form_show['name']))?$booking_form_show['name'].' ':'')
                                .((isset($booking_form_show['lastname']))?$booking_form_show['lastname'].' ':'')
                                .((isset($booking_form_show['secondname']))?$booking_form_show['secondname'].' ':'');

            $wpbc_mail = '"' . ((empty($wpbc_email_title))?__('Booking system' ,'booking'):substr($wpbc_email_title,0,-1)) . '" ' .   
                         '<' . $wpbc_mail . '>';
        }
        return $wpbc_mail;
        */
    }

    
    class wpbc_email_return_path {
  	function __construct() {
		add_action( 'phpmailer_init', array( $this, 'fix' ) );    
  	}
 
	function fix( $phpmailer ) {
	  	$phpmailer->Sender = $phpmailer->From;
	}
    }
 
    function wpbc_wp_mail( $mail_recipient, $mail_subject, $mail_body, $mail_headers ){
        
        $wpbc_email_return_path = new wpbc_email_return_path();
        
        // $mail_recipient = str_replace( '"', '', $mail_recipient );           //FixIn:5.4.3
        
        @wp_mail($mail_recipient, $mail_subject, $mail_body, $mail_headers);
        
        unset( $wpbc_email_return_path );
    }
    
    
    // Approve Email
    function sendApproveEmails($approved_id_str, $is_send_emeils, $denyreason = ''){

        global $wpdb;
        $sql = "SELECT * FROM {$wpdb->prefix}booking as bk WHERE bk.booking_id IN ({$approved_id_str})";
        $result = $wpdb->get_results( $sql  );

        $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_approval_adress') ) ; //'"'. 'Booking sender' . '" <' . $booking_form_show['email'].'>';
        $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_approval_subject') );
        $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_approval_content') );
        $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
        $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

        foreach ($result as $res) {

            $mail_body_to_send      = $mail_body;
            $mail_subject_to_send   = $mail_subject;

            // Sending mail ///////////////////////////////////////////////////////
            if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $res->booking_type );
            else $bk_title = '';

            if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( get_dates_str($res->booking_id) );
            else                                                     $my_dates_4_send = change_date_format(get_dates_str($res->booking_id));

            $my_dates4emeil_check_in_out = explode(',',get_dates_str($res->booking_id));

            $my_check_in_date  = change_date_format($my_dates4emeil_check_in_out[0] );
            $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );            
            $my_check_out_plus1day = change_date_format( date_i18n( 'Y-m-d H:i:s',  strtotime( $my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] . " +1 day" ) ) ); //FixIn: 6.0.1.11
            
            if ( ( isset($res->sync_gid) ) && (! empty($res->sync_gid)) ) {
                $res->form .= "~text^sync_gid{$res->booking_type}^{$res->sync_gid}";
            }
            
            $booking_form_show = get_form_content( $res->form,
                                                   $res->booking_type,
                                                   '',
                                                   array('booking_id'=> $res->booking_id ,
                                                         'id'=> $res->booking_id ,
                                                         'dates'=> $my_dates_4_send,
                                                         'check_in_date' => $my_check_in_date,
                                                         'check_out_date' => $my_check_out_date,
                                                         'check_out_plus1day' => $my_check_out_plus1day,            //FixIn: 6.0.1.11
                                                         'dates_count' => count($my_dates4emeil_check_in_out),
                                                         'cost' => (isset($res->cost))?wpdev_bk_cost_number_format($res->cost):'',
                                                         'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                         'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                         'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                         'denyreason' => $denyreason,
                                                         'remote_ip'     => $_SERVER['REMOTE_ADDR'],           // The IP address from which the user is viewing the current page. 
                                                         'user_agent'    => $_SERVER['HTTP_USER_AGENT'],       // Contents of the User-Agent: header from the current request, if there is one. 
                                                         'request_url'   => $_SERVER['HTTP_REFERER'],          // The address of the page (if any) where action was occured. Because we are sending it in Ajax request, we need to use the REFERER HTTP
                                                         'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                         'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                    
                                                       
                                                       )
                                                 );

            make_bk_action('booking_aproved', $res, $booking_form_show);
            $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
            $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $res->booking_id );

            $mail_subject_to_send = replace_bk_shortcodes_in_form($mail_subject_to_send, $booking_form_show['_all_fields_'], true);
            $mail_body_to_send    = replace_bk_shortcodes_in_form($mail_body_to_send,    $booking_form_show['_all_fields_'], true);
//$mail_body_to_send = str_replace(array("\r\n", "\r", "\n"), "<br />", $mail_body_to_send);     // Fix issue of showing /n instead of the new line
//$mail_body_to_send = nl2br($mail_body_to_send);
//debuge( htmlspecialchars ($mail_body_to_send) );
            $mail_recipients = $booking_form_show['email'];

            $mail_headers = "From: $mail_sender\n";
            $mail_headers .= "Content-Type: text/html\n";

            // Check  about  sending email  to several  emails from  booking form.
            $mail_recipients = check_for_several_emails_in_form( $mail_recipients, $res->form, $res->booking_type );     //FixIn: 6.0.1.9

            if (strpos($mail_recipients, ',')!==false) {        $mail_recipients= explode(';', $mail_recipients);        //FixIn: 6.0.1.9
            } else if (strpos($mail_recipients, ';')!==false) { $mail_recipients= explode(';', $mail_recipients);
            } else {                                            $mail_recipients= array($mail_recipients); }


            foreach ($mail_recipients as $mail_recipient) {
                
                $mail_recipient =  wpbc_format_email_address( $mail_recipient , $booking_form_show );
                
                if ( 
                         ( get_bk_option( 'booking_is_email_approval_adress'  ) != 'Off' )  
                      && ( $is_send_emeils != 0 ) 
                      && ( strpos($mail_recipient,'@blank.com') === false )  
                      && ( strpos($mail_body_to_send,'admin@blank.com') === false )  
                      && (! empty($mail_recipient) )  
                    ) {
                        wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers);                 
                      }

                // Send copy to Admin 
                if ( WP_BK_STRICTLY_FROM_EMAILS ) 
                    $mail_headers_for_admin = $mail_headers;
                else                      
                    $mail_headers_for_admin = "From: $mail_recipient\nContent-Type: text/html\n";                
                
                $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );
                $mail_recipient = wpbc_validate_emails( $mail_recipient );
                $is_email_approval_send_copy_to_admin = get_bk_option( 'booking_is_email_approval_send_copy_to_admin' );
                
                if (   ( $is_email_approval_send_copy_to_admin == 'On' )
                    && ( strpos($mail_recipient,'@blank.com') === false ) 
                    && ( strpos($mail_body_to_send,'admin@blank.com') === false ) 
                    && ($is_send_emeils != 0 )
                    ) {    
                        wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers_for_admin);
                      }
            }

        }

    }

    // Decline Email
    function sendDeclineEmails($approved_id_str, $is_send_emeils, $denyreason = '') {

        global $wpdb;
        $sql = "SELECT *    FROM {$wpdb->prefix}booking as bk
                            WHERE bk.booking_id IN ({$approved_id_str})";

        $result = $wpdb->get_results( $sql );

        $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_deny_adress') ) ;
        $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_deny_subject') );
        $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_deny_content') );
        $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
        $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

        foreach ($result as $res) {

            $mail_body_to_send      = $mail_body;
            $mail_subject_to_send   = $mail_subject;


            // Sending mail ///////////////////////////////////////////////////////
            if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $res->booking_type );
            else $bk_title = '';

            if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( get_dates_str($res->booking_id) );
            else                                                     $my_dates_4_send = change_date_format(get_dates_str($res->booking_id));

            $my_dates4emeil_check_in_out = explode(',',get_dates_str($res->booking_id));

            $my_check_in_date  = change_date_format($my_dates4emeil_check_in_out[0] );
            $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );
            $my_check_out_plus1day = change_date_format( date_i18n( 'Y-m-d H:i:s',  strtotime( $my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] . " +1 day" ) ) ); //FixIn: 6.0.1.11

            if ( ( isset($res->sync_gid) ) && (! empty($res->sync_gid)) ) {
                $res->form .= "~text^sync_gid{$res->booking_type}^{$res->sync_gid}";
            }

            $booking_form_show = get_form_content( $res->form,
                                                   $res->booking_type,
                                                   '',
                                                   array('booking_id'=> $res->booking_id ,
                                                         'id'=> $res->booking_id ,
                                                         'dates'=> $my_dates_4_send,
                                                         'check_in_date' => $my_check_in_date,
                                                         'check_out_date' => $my_check_out_date,
                                                         'check_out_plus1day' => $my_check_out_plus1day,            //FixIn: 6.0.1.11
                                                         'dates_count' => count($my_dates4emeil_check_in_out),
                                                         'cost' => (isset($res->cost))?wpdev_bk_cost_number_format($res->cost):'',
                                                         'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                         'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                         'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                         'denyreason' => $denyreason,
                                                         'remote_ip'     => $_SERVER['REMOTE_ADDR'],           // The IP address from which the user is viewing the current page. 
                                                         'user_agent'    => $_SERVER['HTTP_USER_AGENT'],       // Contents of the User-Agent: header from the current request, if there is one. 
                                                         'request_url'   => $_SERVER['HTTP_REFERER'],          // The address of the page (if any) where action was occured. Because we are sending it in Ajax request, we need to use the REFERER HTTP
                                                         'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                         'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                                                                           
                                                       )
                                                 );

            $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
            $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $res->booking_id );
            $mail_body_to_send = str_replace('\n', '<br />', $mail_body_to_send);     // Fix issue of showing /n instead of the new line

            $mail_subject_to_send = replace_bk_shortcodes_in_form($mail_subject_to_send, $booking_form_show['_all_fields_'], true);
            $mail_body_to_send    = replace_bk_shortcodes_in_form($mail_body_to_send,    $booking_form_show['_all_fields_'], true);


            $mail_recipients =  $booking_form_show['email'];

            $mail_headers = "From: $mail_sender\n";
            $mail_headers .= "Content-Type: text/html\n";

            // Check  about  sending email  to several  emails from  booking form.
            $mail_recipients = check_for_several_emails_in_form( $mail_recipients, $res->form, $res->booking_type );     //FixIn: 6.0.1.9   

            if (strpos($mail_recipients, ',')!==false) {        $mail_recipients= explode(';', $mail_recipients);        //FixIn: 6.0.1.9
            } else if (strpos($mail_recipients, ';')!==false) { $mail_recipients= explode(';', $mail_recipients);
            } else {                                            $mail_recipients= array($mail_recipients); }


            foreach ($mail_recipients as $mail_recipient) {
                
                $mail_recipient =  wpbc_format_email_address( $mail_recipient , $booking_form_show );
                
                if ( (get_bk_option( 'booking_is_email_deny_adress'  ) != 'Off')  && ($is_send_emeils != 0 ) ){
                    if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                        wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers);

                }
                
                if ( WP_BK_STRICTLY_FROM_EMAILS ) 
                    $mail_headers_for_admin = $mail_headers;
                else
                    $mail_headers_for_admin = "From: $mail_recipient\nContent-Type: text/html\n";
                // Send to the Admin also
                $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );
                $mail_recipient = wpbc_validate_emails( $mail_recipient );
                $is_email_deny_send_copy_to_admin = get_bk_option( 'booking_is_email_deny_send_copy_to_admin' );
                if ( $is_email_deny_send_copy_to_admin == 'On')
                    if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                        if ($is_send_emeils != 0 )
                            wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers_for_admin);
            }

        }

    }

    // Modifications Email
    function sendModificationEmails($booking_id, $bktype, $formdata) {

        $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_modification_adress') ) ;
        $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_modification_subject') );
        $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_modification_content') );
        $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
        $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

        $mail_body_to_send      = $mail_body;
        $mail_subject_to_send   = $mail_subject;

        if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $bktype );
        else $bk_title = '';

        $my_dates4emeil = get_dates_str($booking_id) ;
        if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( $my_dates4emeil );
        else                                                     $my_dates_4_send = change_date_format( $my_dates4emeil );

        $my_dates4emeil_check_in_out = explode(',', $my_dates4emeil );

        $my_check_in_date  = change_date_format($my_dates4emeil_check_in_out[0] );
        $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );
        $my_check_out_plus1day = change_date_format( date_i18n( 'Y-m-d H:i:s',  strtotime( $my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] . " +1 day" ) ) ); //FixIn: 6.0.1.11

        $my_cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);
        $my_cost = wpdev_bk_cost_number_format( $my_cost );
        
        $booking_form_show = get_form_content( $formdata,
                                               $bktype,
                                               '',
                                               array('booking_id'=> $booking_id ,
                                                     'id'=> $booking_id ,
                                                     'dates'=> $my_dates_4_send,
                                                     'check_in_date' => $my_check_in_date,
                                                     'check_out_date' => $my_check_out_date,
                                                     'check_out_plus1day' => $my_check_out_plus1day,            //FixIn: 6.0.1.11
                                                     'dates_count' => count($my_dates4emeil_check_in_out),
                                                     'cost' => $my_cost,
                                                     'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                     'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'remote_ip'     => $_SERVER['REMOTE_ADDR'],           // The IP address from which the user is viewing the current page. 
                                                     'user_agent'    => $_SERVER['HTTP_USER_AGENT'],       // Contents of the User-Agent: header from the current request, if there is one. 
                                                     'request_url'   => $_SERVER['HTTP_REFERER'],          // The address of the page (if any) where action was occured. Because we are sending it in Ajax request, we need to use the REFERER HTTP
                                                     'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                     'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                                                                       
                                                   )
                                             );
        $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
        $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $booking_id );

        $mail_subject_to_send = replace_bk_shortcodes_in_form($mail_subject_to_send, $booking_form_show['_all_fields_'], true);
        $mail_body_to_send    = replace_bk_shortcodes_in_form($mail_body_to_send,    $booking_form_show['_all_fields_'], true);
        $mail_body_to_send = str_replace('\n', '<br />', $mail_body_to_send);     // Fix issue of showing /n instead of the new line

        $mail_recipients =  $booking_form_show['email'];
        $mail_headers = "From: $mail_sender\nContent-Type: text/html\n";

        // Check  about  sending email  to several  emails from  booking form.
        $mail_recipients = check_for_several_emails_in_form( $mail_recipients, $formdata, $bktype );     //FixIn: 6.0.1.9   
        
        if (strpos($mail_recipients, ',')!==false) {        $mail_recipients= explode(';', $mail_recipients);       //FixIn: 6.0.1.9
        } else if (strpos($mail_recipients, ';')!==false) { $mail_recipients= explode(';', $mail_recipients);
        } else {                                            $mail_recipients= array($mail_recipients); }


        foreach ($mail_recipients as $mail_recipient) {
            $mail_recipient = wpbc_format_email_address( $mail_recipient , $booking_form_show );
            if (  (get_bk_option( 'booking_is_email_modification_adress'  ) != 'Off')  ){
                if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                    wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers);

            }

            if ( WP_BK_STRICTLY_FROM_EMAILS ) 
                $mail_headers_for_admin = $mail_headers;
            else
                $mail_headers_for_admin = "From: $mail_recipient\nContent-Type: text/html\n";
            // Send to the Admin also
            $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );
            $mail_recipient = wpbc_validate_emails( $mail_recipient );
            $is_email_modification_send_copy_to_admin = get_bk_option( 'booking_is_email_modification_send_copy_to_admin' );
            if ( $is_email_modification_send_copy_to_admin == 'On')
                if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                        wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers_for_admin);
        }


    }

    // New Booking Email
    function sendNewBookingEmails($booking_id, $bktype, $formdata) {

        $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_from_adress') ) ; //'"'. 'Booking sender' . '" <' . $booking_form_show['email'].'>';
        $mail_recipients =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );//'"Booking receipent" <' .get_option('admin_email').'>';
        $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_subject') );
        $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_content') );
        $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
        $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

        $mail_body_to_send      = $mail_body;
        $mail_subject_to_send   = $mail_subject;

        if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $bktype );
        else $bk_title = '';

        $my_dates4emeil = get_dates_str($booking_id) ;
        if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( $my_dates4emeil );
        else                                                     $my_dates_4_send = change_date_format( $my_dates4emeil );

        $my_dates4emeil_check_in_out = explode(',', $my_dates4emeil );

        $my_check_in_date  = change_date_format($my_dates4emeil_check_in_out[0] );
        $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );
        $my_check_out_plus1day = change_date_format( date_i18n( 'Y-m-d H:i:s',  strtotime( $my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] . " +1 day" ) ) ); //FixIn: 6.0.1.11

        $my_cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);
        $my_cost = wpdev_bk_cost_number_format( $my_cost );
        
        $booking_form_show = get_form_content( $formdata,
                                               $bktype,
                                               '',
                                               array('booking_id'=> $booking_id ,
                                                     'id'=> $booking_id ,
                                                     'dates'=> $my_dates_4_send,
                                                     'check_in_date' => $my_check_in_date,
                                                     'check_out_date' => $my_check_out_date,
                                                     'check_out_plus1day' => $my_check_out_plus1day,            //FixIn: 6.0.1.11
                                                     'dates_count' => count($my_dates4emeil_check_in_out),
                                                     'cost' => $my_cost,
                                                     'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                     'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'remote_ip'     => $_SERVER['REMOTE_ADDR'],           // The IP address from which the user is viewing the current page. 
                                                     'user_agent'    => $_SERVER['HTTP_USER_AGENT'],       // Contents of the User-Agent: header from the current request, if there is one. 
                                                     'request_url'   => $_SERVER['HTTP_REFERER'],          // The address of the page (if any) where action was occured. Because we are sending it in Ajax request, we need to use the REFERER HTTP
                                                     'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                     'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                    
                                                   )
                                             );
        $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
        $mail_body_to_send = str_replace('[moderatelink]', htmlspecialchars_decode(
                '<a href="'. admin_url( 'admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking&view_mode=vm_listing&tab=actions&wh_booking_id='. $booking_id ) . '">'
                . __('here' ,'booking') . '</a>'), $mail_body_to_send);
        
//        $mail_body_to_send = str_replace('[moderatelink]', htmlspecialchars_decode(
//                '<a href="'. admin_url( 'admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . urlencode('/'). WPDEV_BK_PLUGIN_FILENAME .'wpdev-booking'.urlencode('&').'view_mode=vm_listing'.urlencode('&').'tab=actions'.urlencode('&').'wh_booking_id='. $booking_id ) . '">'
//                . __('here' ,'booking') . '</a>'), $mail_body_to_send);
        
        $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $booking_id );

        $mail_subject_to_send = replace_bk_shortcodes_in_form($mail_subject_to_send, $booking_form_show['_all_fields_'], true);
        $mail_body_to_send    = replace_bk_shortcodes_in_form($mail_body_to_send,    $booking_form_show['_all_fields_'], true);        
        $mail_body_to_send = str_replace('\n', '<br />', $mail_body_to_send);     // Fix issue of showing /n instead of the new line

        if ( strpos($mail_recipients,'[visitoremail]') !== false ) {
            $wpbc_mail =  wpbc_format_email_address( $booking_form_show['email'] , $booking_form_show );

            $mail_recipients = str_replace('[visitoremail]',$wpbc_mail ,$mail_recipients);
        }
        $mail_recipients = replace_bk_shortcodes_in_form($mail_recipients, $booking_form_show['_all_fields_'], true);
        if ( strpos($mail_sender,'[visitoremail]') !== false ) {
            $wpbc_email_title =    ((isset($booking_form_show['firstname']))?$booking_form_show['firstname'].' ':'')
                            .((isset($booking_form_show['name']))?$booking_form_show['name'].' ':'')
                            .((isset($booking_form_show['lastname']))?$booking_form_show['lastname'].' ':'')
                            .((isset($booking_form_show['secondname']))?$booking_form_show['secondname'].' ':'');

            $wpbc_mail = '"' . ((empty($wpbc_email_title))?__('Booking system' ,'booking'):$wpbc_email_title) . '" '    
                         . '<'.$booking_form_show['email'].'>';
            
            $mail_sender = str_replace('[visitoremail]',$wpbc_mail,$mail_sender);
        }
        $mail_sender = replace_bk_shortcodes_in_form($mail_sender, $booking_form_show['_all_fields_'], true);
        
        $mail_headers = "From: $mail_sender\nContent-Type: text/html\n";
//        $mail_headers = "From: $mail_sender\n";
//        preg_match('/<(.*)>/', $mail_sender, $simple_email_matches );
//        $reply_to_email = ( count( $simple_email_matches ) > 1 ) ? $simple_email_matches[1] : $mail_sender;
//        $mail_headers .= 'Reply-To: ' . $reply_to_email . "\n";        
//        $mail_headers .= 'X-Sender: ' . $reply_to_email . "\n";
//        $mail_headers .= 'Return-Path: ' . $reply_to_email . "\n";
//        $mail_headers .= 'Content-type: text/html; charset=' . get_option( 'blog_charset' ) . "\n";

        if (strpos($mail_recipients, ',')!==false) {        $mail_recipients= explode(',', $mail_recipients);
        } else if (strpos($mail_recipients, ';')!==false) { $mail_recipients= explode(';', $mail_recipients);
        } else {                                            $mail_recipients= array($mail_recipients); }

//debuge($mail_recipients);
        foreach ($mail_recipients as $mail_recipient) {
            $mail_recipient = wpbc_validate_emails( $mail_recipient );
            if (  (get_bk_option( 'booking_is_email_reservation_adress'  ) != 'Off')  ){
                if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) ) {                    
                    wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers);
                }

            }
        }

        /////////////////////////////////////////////////////////////////////////

        if (get_bk_option( 'booking_is_email_newbookingbyperson_adress'  ) == 'On') {

            $mail_sender    =  htmlspecialchars_decode( get_bk_option( 'booking_email_newbookingbyperson_adress') ) ; //'"'. 'Booking sender' . '" <' . $booking_form_show['email'].'>';
            $mail_recipients =  $booking_form_show['email'];
            $mail_subject   =  htmlspecialchars_decode( get_bk_option( 'booking_email_newbookingbyperson_subject') );
            $mail_body      =  htmlspecialchars_decode( get_bk_option( 'booking_email_newbookingbyperson_content') );
            $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
            $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

            $mail_body_to_send      = $mail_body;
            $mail_subject_to_send   = $mail_subject;

            $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
            $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $booking_id );

            $mail_subject_to_send = replace_bk_shortcodes_in_form($mail_subject_to_send, $booking_form_show['_all_fields_'], true);
            $mail_body_to_send    = replace_bk_shortcodes_in_form($mail_body_to_send,    $booking_form_show['_all_fields_'], true);
            $mail_body_to_send = str_replace('\n', '<br />', $mail_body_to_send);     // Fix issue of showing /n instead of the new line


            $wpbc_mail =  wpbc_format_email_address( $booking_form_show['email'] , $booking_form_show );

            if ( strpos($mail_recipients,'[visitoremail]') !== false ) 
                $mail_recipients = str_replace( '[visitoremail]', $wpbc_mail, $mail_recipients );
            else 
                $mail_recipients =  $wpbc_mail;  
            
           $mail_recipients = check_for_several_emails_in_form( $mail_recipients, $formdata, $bktype );     //FixIn: 6.0.1.9   
           
           $mail_recipients = replace_bk_shortcodes_in_form($mail_recipients, $booking_form_show['_all_fields_'], true);
            
            if ( strpos($mail_sender,'[visitoremail]') !== false ) 
                $mail_sender = str_replace( '[visitoremail]', $wpbc_mail, $mail_sender );
            
            $mail_sender = replace_bk_shortcodes_in_form($mail_sender, $booking_form_show['_all_fields_'], true);
            
            $mail_headers = "From: $mail_sender\nContent-Type: text/html\n";
//            $mail_headers = "From: $mail_sender\n";
//            preg_match('/<(.*)>/', $mail_sender, $simple_email_matches );
//            $reply_to_email = ( count( $simple_email_matches ) > 1 ) ? $simple_email_matches[1] : $mail_sender;
//            $mail_headers .= 'Reply-To: ' . $reply_to_email . "\n";        
//            $mail_headers .= 'X-Sender: ' . $reply_to_email . "\n";
//            $mail_headers .= 'Return-Path: ' . $reply_to_email . "\n";
//            $mail_headers .= 'Content-type: text/html; charset=' . get_option( 'blog_charset' ) . "\n";


            if (strpos($mail_recipients, ',')!==false) {        $mail_recipients= explode(',', $mail_recipients);
            } else if (strpos($mail_recipients, ';')!==false) { $mail_recipients= explode(';', $mail_recipients);
            } else {                                            $mail_recipients= array($mail_recipients); }

//debuge($mail_recipients, 'visitor');
            foreach ($mail_recipients as $mail_recipient) {
                    $mail_recipient = wpbc_validate_emails( $mail_recipient );
                    if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) ) {
                        wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers);
                    }

            }

        }


    }

    
    // Payment request Email
    function sendPaymentRequestEmail($booking_id, $bktype, $formdata, $reason = '' ){

        $mail_sender      = htmlspecialchars_decode( get_bk_option( 'booking_email_payment_request_adress'));
        $mail_subject     = htmlspecialchars_decode( get_bk_option( 'booking_email_payment_request_subject'));
        $mail_body        = htmlspecialchars_decode( get_bk_option( 'booking_email_payment_request_content'));
        $mail_subject =  apply_bk_filter('wpdev_check_for_active_language', $mail_subject );
        $mail_body    =  apply_bk_filter('wpdev_check_for_active_language', $mail_body );

        $mail_body_to_send      = $mail_body;
        $mail_subject_to_send   = $mail_subject;

        if (function_exists ('get_booking_title')) $bk_title = get_booking_title( $bktype );
        else $bk_title = '';

        $my_dates4emeil = get_dates_str($booking_id) ;
        if (get_bk_option( 'booking_date_view_type') == 'short') $my_dates_4_send = get_dates_short_format( $my_dates4emeil );
        else                                                     $my_dates_4_send = change_date_format( $my_dates4emeil );

        $my_dates4emeil_check_in_out = explode(',', $my_dates4emeil );

        $my_check_in_date  = change_date_format($my_dates4emeil_check_in_out[0] );
        $my_check_out_date = change_date_format($my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] );
        $my_check_out_plus1day = change_date_format( date_i18n( 'Y-m-d H:i:s',  strtotime( $my_dates4emeil_check_in_out[ count($my_dates4emeil_check_in_out)-1 ] . " +1 day" ) ) ); //FixIn: 6.0.1.11

        $my_cost = apply_bk_filter('get_booking_cost_from_db', '', $booking_id);
        $my_cost = wpdev_bk_cost_number_format( $my_cost );
        
        $booking_form_show = get_form_content( $formdata,
                                               $bktype,
                                               '',
                                               array('booking_id'=> $booking_id ,
                                                     'id'=> $booking_id ,
                                                     'dates'=> $my_dates_4_send,
                                                     'check_in_date' => $my_check_in_date,
                                                     'check_out_date' => $my_check_out_date,
                                                     'check_out_plus1day' => $my_check_out_plus1day,            //FixIn: 6.0.1.11
                                                     'dates_count' => count($my_dates4emeil_check_in_out),
                                                     'cost' => $my_cost,
                                                     'siteurl' => htmlspecialchars_decode( '<a href="'.home_url().'">' . home_url() . '</a>'),
                                                     'resource_title'=> apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'bookingtype' => apply_bk_filter('wpdev_check_for_active_language', $bk_title ),
                                                     'paymentreason' => $reason,
                                                     'remote_ip'     => $_SERVER['REMOTE_ADDR'],           // The IP address from which the user is viewing the current page. 
                                                     'user_agent'    => $_SERVER['HTTP_USER_AGENT'],       // Contents of the User-Agent: header from the current request, if there is one. 
                                                     'request_url'   => $_SERVER['HTTP_REFERER'],          // The address of the page (if any) where action was occured. Because we are sending it in Ajax request, we need to use the REFERER HTTP
                                                     'current_date' => date_i18n(get_bk_option( 'booking_date_format') ),
                                                     'current_time' => date_i18n(get_bk_option( 'booking_time_format') )                                                                                                       
                                                   )
                                             );
        $mail_body_to_send = str_replace('[content]', $booking_form_show['content'], $mail_body_to_send);
        $mail_body_to_send = apply_bk_filter('wpdev_booking_set_booking_edit_link_at_email', $mail_body_to_send, $booking_id );

        $mail_subject_to_send = replace_bk_shortcodes_in_form($mail_subject_to_send, $booking_form_show['_all_fields_'], true);
        $mail_body_to_send    = replace_bk_shortcodes_in_form($mail_body_to_send,    $booking_form_show['_all_fields_'], true);
        $mail_body_to_send = str_replace('\n', '<br />', $mail_body_to_send);     // Fix issue of showing /n instead of the new line


        $mail_recipients =  $booking_form_show['email'];
        $mail_headers = "From: $mail_sender\nContent-Type: text/html\n";

        // Check  about  sending email  to several  emails from  booking form.
        $mail_recipients = check_for_several_emails_in_form( $mail_recipients, $formdata, $bktype );     //FixIn: 6.0.1.9   
        
        if (strpos($mail_recipients, ',')!==false) {        $mail_recipients= explode(';', $mail_recipients);       //FixIn: 6.0.1.9
        } else if (strpos($mail_recipients, ';')!==false) { $mail_recipients= explode(';', $mail_recipients);
        } else {                                            $mail_recipients= array($mail_recipients); }


        foreach ($mail_recipients as $mail_recipient) {
            
            $mail_recipient = wpbc_format_email_address( $mail_recipient , $booking_form_show );
            
            if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers);

            if ( WP_BK_STRICTLY_FROM_EMAILS ) 
                $mail_headers_for_admin = $mail_headers;
            else
                $mail_headers_for_admin = "From: $mail_recipient\nContent-Type: text/html\n";
            
            // Send to the Admin also
            $is_email_payment_request_send_copy_to_admin = get_bk_option( 'booking_is_email_payment_request_send_copy_to_admin' );
            $mail_recipient =  htmlspecialchars_decode( get_bk_option( 'booking_email_reservation_adress') );
            $mail_recipient = wpbc_validate_emails( $mail_recipient );
            if ( $is_email_payment_request_send_copy_to_admin == 'On')
                if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
                        wpbc_wp_mail( $mail_recipient, $mail_subject_to_send, $mail_body_to_send, $mail_headers_for_admin);
        }
        if ( ( strpos($mail_recipient,'@blank.com') === false ) && ( strpos($mail_body_to_send,'admin@blank.com') === false ) )
            return true;
        else 
            return false;
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
     
    }


    function check_for_several_emails_in_form( $mail_recipients, $formdata, $bktype ) {  // FixIn: 6.0.1.9

        $possible_other_emails = explode('~',$formdata);
        $possible_other_emails = array_map("explode", array_fill(0,count($possible_other_emails),'^'), $possible_other_emails);
        $other_emails = array();
        foreach ( $possible_other_emails as $possible_emails ) {
            if (       ( $possible_emails[0] == 'email' ) 
                    //&& ( $possible_emails[1] != 'email' . $bktype ) 
                    && ( ! empty($possible_emails[2]) ) 
                )
                    $other_emails[]=$possible_emails[2];
        }
        if ( count( $other_emails ) > 1 ) {
            $other_emails = implode(',',$other_emails);
            $mail_recipients =  $other_emails;
        }
        return $mail_recipients;
    }
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  D e b u g    f u n c t i o n s       ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    if (!function_exists ('debuge')) {
        function debuge() {
            $numargs = func_num_args();
            $var = func_get_args();
            $makeexit = is_bool($var[count($var)-1])?$var[count($var)-1]:false;
            echo "<div style=''><pre class='prettyprint linenums' style=''>";
            print_r ( $var );
            echo "</pre></div>";
            echo '<script type="text/javascript"> jQuery(".ajax_respond_insert").css("display","block"); jQuery(".ajax_respond").css("display","block"); </script>';
            if ($makeexit) {
                echo '<div style="font-size:18px;float:right;">' . get_num_queries(). '/'  . timer_stop(0, 3) . 'qps</div>';
                exit;
            }
        }
    }

    if (!function_exists ('debugq')) {
        function debugq() {
                echo '<div style="font-size:18px;float:right;">' . get_num_queries(). '/'  . timer_stop(0, 3) . 'qps</div>';
        }
    }

    if (!function_exists ('bk_error')) {
        function bk_error( $msg , $file_name='', $line_num=''){
            if (!defined('WPDEV_BK_VERSION'))  $ver_num = 'Undefined yet';
            else                               $ver_num = WPDEV_BK_VERSION ;


            $last_db_error = '';
            global $EZSQL_ERROR;
            if (isset($EZSQL_ERROR[ (count($EZSQL_ERROR)-1)])) {

                $last_db_error2 = $EZSQL_ERROR[ (count($EZSQL_ERROR)-1)];

                if  ( (isset($last_db_error2['query'])) && (isset($last_db_error2['error_str'])) ) {

                    $query = $last_db_error2['query'];
                    $str   = str_replace('','',$last_db_error2['error_str']);
                    $str   = str_replace('"','', $str );     $str   = str_replace("'",'', $str );
                    $query   = str_replace('"','', $query ); $query   = str_replace("'",'', $query );

                    $str   = htmlspecialchars( $str, ENT_QUOTES );
                    $query = htmlspecialchars( $query , ENT_QUOTES );


                    //$last_db_error = '<p class="wpdberror"><strong>Last error:</strong> ['.$str.']<br /><code>'.$query.'</code></p>';
                    $last_db_error =  $str ;
                    if ( WP_BK_DEBUG_MODE )
                        $last_db_error .= '::<span style="color:#300;">'.$query.'</span>';
                }
            }
            echo $msg . '<br /><span style="font-size:11px;"> [F:' .  str_replace( dirname( $file_name ) , '' , $file_name ). "|L:" .  $line_num  . "|V:" .  $ver_num  . "|DB:" .  $last_db_error  ."] </span>" ;



        }
    }




    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  Internal plugin action hooks system      ///////////////////////////////////////////////////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    global $wpdev_bk_action, $wpdev_bk_filter;

    if (!function_exists ('add_bk_filter')) {
    function add_bk_filter($filter_type, $filter) {
        global $wpdev_bk_filter;

        $args = array();
        if ( is_array($filter) && 1 == count($filter) && is_object($filter[0]) ) // array(&$this)
            $args[] =& $filter[0];
        else
            $args[] = $filter;
        for ( $a = 2; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        if ( is_array($wpdev_bk_filter) )

            if ( isset($wpdev_bk_filter[$filter_type]) ) {
                if ( is_array($wpdev_bk_filter[$filter_type]) )
                    $wpdev_bk_filter[$filter_type][]= $args;
                else
                    $wpdev_bk_filter[$filter_type]= array($args);
            } else
                $wpdev_bk_filter[$filter_type]= array($args);
        else
            $wpdev_bk_filter = array( $filter_type => array( $args ) ) ;
    }
    }

    if (!function_exists ('remove_bk_filter')) {
    function remove_bk_filter($filter_type, $filter) {
        global $wpdev_bk_filter;

        if ( isset($wpdev_bk_filter[$filter_type]) ) {
            for ($i = 0; $i < count($wpdev_bk_filter[$filter_type]); $i++) {
                if ( $wpdev_bk_filter[$filter_type][$i][0] == $filter ) {
                    $wpdev_bk_filter[$filter_type][$i] = null;
                    return;
                }
            }
        }
    }
    }

    if (!function_exists ('apply_bk_filter')) {
    function apply_bk_filter($filter_type) {
        global $wpdev_bk_filter;


        $args = array();
        for ( $a = 1; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        if ( count($args) > 0 )
            $value = $args[0];
        else
            $value = false;

        if ( is_array($wpdev_bk_filter) )
            if ( isset($wpdev_bk_filter[$filter_type]) )
                foreach ($wpdev_bk_filter[$filter_type] as $filter) {
                    $filter_func = array_shift($filter);
                    $parameter = $args;
                    $value =  call_user_func_array($filter_func,$parameter );
                }
        return $value;
    }
    }

    if (!function_exists ('make_bk_action')) {
    function make_bk_action($action_type) {
        global $wpdev_bk_action;


        $args = array();
        for ( $a = 1; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        //$value = $args[0];

        if ( is_array($wpdev_bk_action) )
            if ( isset($wpdev_bk_action[$action_type]) )
                foreach ($wpdev_bk_action[$action_type] as $action) {
                    $action_func = array_shift($action);
                    $parameter = $action;
                    call_user_func_array($action_func,$args );
                }
    }
    }

    if (!function_exists ('add_bk_action')) {
    function add_bk_action($action_type, $action) {
        global $wpdev_bk_action;

        $args = array();
        if ( is_array($action) && 1 == count($action) && is_object($action[0]) ) // array(&$this)
            $args[] =& $action[0];
        else
            $args[] = $action;
        for ( $a = 2; $a < func_num_args(); $a++ )
            $args[] = func_get_arg($a);

        if ( is_array($wpdev_bk_action) )
            if ( isset($wpdev_bk_action[$action_type]) ) {
                if ( is_array($wpdev_bk_action[$action_type]) )
                    $wpdev_bk_action[$action_type][]= $args;
                else
                    $wpdev_bk_action[$action_type]= array($args);
            } else
                    $wpdev_bk_action[$action_type]= array($args);

        else
            $wpdev_bk_action = array( $action_type => array( $args ) ) ;
    }
    }

    if (!function_exists ('remove_bk_action')) {
    function remove_bk_action($action_type, $action) {
        global $wpdev_bk_action;

        if ( isset($wpdev_bk_action[$action_type]) ) {
            for ($i = 0; $i < count($wpdev_bk_action[$action_type]); $i++) {
                if ( $wpdev_bk_action[$action_type][$i][0] == $action ) {
                    $wpdev_bk_action[$action_type][$i] = null;
                    return;
                }
            }
        }
    }
    }

    // Work with Booking  Options //////////////////////////////////////////////////


    if (!function_exists ('get_bk_option')) {
    // Get
    function get_bk_option( $option, $default = false ) {

        $u_value = apply_bk_filter('wpdev_bk_get_option', 'no-values'  , $option, $default );
        if ( $u_value !== 'no-values' ) return $u_value;

        return get_option( $option, $default  );
    }
    }

    if (!function_exists ('update_bk_option')) {
    // Update
    function  update_bk_option ( $option, $newvalue ) {

        $u_value = apply_bk_filter('wpdev_bk_update_option', 'no-values'  , $option, $newvalue );
        if ( $u_value !== 'no-values' ) return $u_value;

        return update_option($option, $newvalue);
    }
    }

    if (!function_exists ('delete_bk_option')) {
    // Dekete
    function  delete_bk_option ( $option   ) {

        $u_value = apply_bk_filter('wpdev_bk_delete_option', 'no-values'  , $option );
        if ( $u_value !== 'no-values' ) return $u_value;

        return delete_option($option );
    }
    }

    if (!function_exists ('add_bk_option')) {
    // Add
    function add_bk_option( $option, $value = '', $deprecated = '', $autoload = 'yes' ) {

        $u_value = apply_bk_filter('wpdev_bk_add_option', 'no-values'  , $option, $value, $deprecated,  $autoload );
        if ( $u_value !== 'no-values' ) return $u_value;

        return add_option( $option, $value  , $deprecated  , $autoload   );
    }
    }
    ////////////////////////////////////////////////////////////////////////////////



    //   Load locale          //////////////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!function_exists ('load_bk_Translation')) {
        function load_bk_Translation(){
            //$locale = 'fr_FR'; loadLocale($locale);                                      // Localization
            if ( ! loadLocale() ) { loadLocale('en_US'); }
            $locale = getBookingLocale();
        }
    }

    if (!function_exists ('loadLocale')) {
    function loadLocale($locale = '') { 
        if ( empty( $locale ) ) $locale = getBookingLocale();
        if ( !empty( $locale ) ) {

            $domain = 'booking';//str_replace('.php','',WPDEV_BK_PLUGIN_FILENAME) ;
            $mofile = WPDEV_BK_PLUGIN_DIR  .'/languages/'.$domain.'-'.$locale.'.mo';
            if (file_exists($mofile)) {
                                                                                //return load9999_textdomain($domain , $mofile);  // Depricated
                $plugin_rel_path = WPDEV_BK_PLUGIN_DIRNAME .'/languages'  ;
                return load_plugin_textdomain( $domain ,   false, $plugin_rel_path ) ;
            } else   return false;
        }
        return false;
    }
    }


    
    if (!function_exists ('getBookingLocale')) {
        function getBookingLocale() {
            if( defined('WPDEV_BK_LOCALE_RELOAD') ) return WPDEV_BK_LOCALE_RELOAD;
            else define('WPDEV_BK_LOCALE_RELOAD', get_locale() );
            return get_locale();
        }
    }

    
    add_filter('plugin_locale', 'plugin_locale_bk_recheck', 100, 2);  // When load_plugin_text_domain is work, its get def loacle and not that, we send to it so need to reupdate it
    function plugin_locale_bk_recheck($locale, $plugin_domain ) {

        if ($plugin_domain == 'booking') 
            if( defined('WPDEV_BK_LOCALE_RELOAD') )
                return WPDEV_BK_LOCALE_RELOAD;
        
        return $locale;
    }



    //   Get header info from this file, just for compatibility with WordPress 2.8 and older versions //////////////////////////////////////
    if (!function_exists ('get_file_data_wpdev')) {
    function get_file_data_wpdev( $file, $default_headers, $context = '' ) {
        // We don't need to write to the file, so just open for reading.
        $fp = fopen( $file, 'r' );

        // Pull only the first 8kiB of the file in.
        $file_data = fread( $fp, 8192 );

        // PHP will close file handle, but we are good citizens.
        fclose( $fp );

        if( $context != '' ) {
            $extra_headers = array();//apply_filters( "extra_$context".'_headers', array() );

            $extra_headers = array_flip( $extra_headers );
            foreach( $extra_headers as $key=>$value ) {
                $extra_headers[$key] = $key;
            }
            $all_headers = array_merge($extra_headers, $default_headers);
        } else {
            $all_headers = $default_headers;
        }

        foreach ( $all_headers as $field => $regex ) {
            preg_match( '/' . preg_quote( $regex, '/' ) . ':(.*)$/mi', $file_data, ${$field});
            if ( !empty( ${$field} ) )
                ${$field} =  trim(preg_replace("/\s*(?:\*\/|\?>).*/", '',  ${$field}[1] ));
            else
                ${$field} = '';
        }

        $file_data = compact( array_keys( $all_headers ) );

        return $file_data;
    }
    }


    // Security  
    function escape_any_xss($formdata){

        $formdata_array = explode('~',$formdata);
        $formdata_array_count = count($formdata_array);

        $clean_formdata = '';

        for ( $i=0 ; $i < $formdata_array_count ; $i++) {
            $elemnts = explode('^',$formdata_array[$i]);
            if ( count( $elemnts ) > 2 ) {
                $type = $elemnts[0];
                $element_name = $elemnts[1];
                $value = $elemnts[2];

                $value = wpbc_clean_parameter( $value );

                // convert to new value
                $clean_formdata .= $type . '^' . $element_name . '^' . $value . '~';
            }
        }

        $clean_formdata = substr($clean_formdata, 0, -1);
        $clean_formdata = str_replace('%', '&#37;', $clean_formdata ); // clean any % from the form, because otherwise, there is problems with SQL prepare function
        
        return $clean_formdata;
    }

    // check $value for injection here
    function wpbc_clean_parameter( $value ) {
        $value = preg_replace( '/<[^>]*>/', '', $value ); // clean any tags
        $value = str_replace( '<', ' ', $value ); // clean any tags
        $value = str_replace( '>', ' ', $value ); // clean any tags
        $value = strip_tags( $value );
        global $wpdb;
        $value = $wpdb->_real_escape( $value ); // Clean SQL injection    
        return $value; 
    }

    function wpbc_clean_string_for_db( $value ) {
        global $wpdb;
        $value = trim( $wpdb->prepare("%s", $value) , "'" );
        return $value;
    }
    
    function getNumOfNewBookings(){

          global $wpdb;

          if  (wpbc_is_field_in_table_exists('booking','is_new') == 0)  return 0;  // do not created this field, so return 0

          $sql_req = "SELECT bk.booking_id FROM {$wpdb->prefix}booking as bk WHERE  bk.is_new = 1" ;

          $sql_req = apply_bk_filter('get_sql_for_checking_new_bookings', $sql_req );
          $sql_req = apply_bk_filter('get_sql_for_checking_new_bookings_multiuser', $sql_req );

          $bookings = $wpdb->get_results( $sql_req );

          return count($bookings) ;
        
    }


    function renew_NumOfNewBookings($id_of_new_bookings, $is_new = '0' , $user_id = 1 ){
        global $wpdb;

        if (count($id_of_new_bookings) > 0 ) {

            if  (wpbc_is_field_in_table_exists('booking','is_new') == 0)  return 0;  // do not created this field, so return 0
            
            $id_of_new_bookings = implode(',', $id_of_new_bookings);
            $id_of_new_bookings = wpbc_clean_string_for_db( $id_of_new_bookings );
            
            
//debuge($id_of_new_bookings);           
            if ($id_of_new_bookings == 'all') {
                $update_sql = "UPDATE {$wpdb->prefix}booking AS bk SET bk.is_new = {$is_new} ";   
//debuge($update_sql);                
                $update_sql = apply_bk_filter('update_sql_for_checking_new_bookings', $update_sql, 0 , $user_id );
            } else
                $update_sql = "UPDATE {$wpdb->prefix}booking AS bk SET bk.is_new = {$is_new} WHERE bk.booking_id IN  ( {$id_of_new_bookings} ) ";

            if ( false === $wpdb->query( $update_sql  ) ) {
                bk_error('Error during updating status of bookings at DB',__FILE__,__LINE__);
                die();
            }
        }
    }


    function wpdev_bk_is_this_demo(){
        //  return true;
        if  (
                ( strpos($_SERVER['SCRIPT_FILENAME'],'wpbookingcalendar.com') !== FALSE ) ||
                ( strpos($_SERVER['HTTP_HOST'],'wpbookingcalendar.com') !== FALSE )
            )
              return true;
            else
              return false;
    }


    // Add Admin Bar
    add_action( 'admin_bar_menu', 'wp_admin_bar_bookings_menu', 70 );

    function wp_admin_bar_bookings_menu(){
        global $wp_admin_bar;
        
        $current_user = wp_get_current_user();

        $curr_user_role = get_bk_option( 'booking_user_role_booking' );
        $level = 10;
        if ($curr_user_role == 'administrator')       $level = 10;
        else if ($curr_user_role == 'editor')         $level = 7;
        else if ($curr_user_role == 'author')         $level = 2;
        else if ($curr_user_role == 'contributor')    $level = 1;
        else if ($curr_user_role == 'subscriber')     $level = 0;

        $is_super_admin = apply_bk_filter('multiuser_is_user_can_be_here', false, 'only_super_admin');
        if (   ( ($current_user->user_level < $level) && (! $is_super_admin)  ) || !is_admin_bar_showing() ) return;


        $update_count = getNumOfNewBookings();

        $title = __('Bookings' ,'booking');
        $update_title = $title;
        $is_user_activated = apply_bk_filter('multiuser_is_current_user_active',  true );           //FixIn: 6.0.1.17

        if ( ( $update_count > 0 ) && ( $is_user_activated ) ){
            $update_count_title = "&nbsp;<span id='ab-updates' class='booking-count bk-update-count' >" . number_format_i18n($update_count) . "</span>" ; //id='booking-count'
            $update_title .= $update_count_title;
        }

        $link_bookings = admin_url('admin.php'). "?page=" . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . "wpdev-booking";
        $link_settings = admin_url('admin.php'). "?page=" . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . "wpdev-booking-option";

        $wp_admin_bar->add_menu(
                array(
                    'id' => 'booking_options',
                    'title' => $update_title ,
                    'href' => $link_bookings
                    )
                );
        
         $curr_user_role_settings = get_bk_option( 'booking_user_role_settings' );
         $level = 10;
         if ($curr_user_role_settings == 'administrator')       $level = 10;
         else if ($curr_user_role_settings == 'editor')         $level = 7;
         else if ($curr_user_role_settings == 'author')         $level = 2;
         else if ($curr_user_role_settings == 'contributor')    $level = 1;
         else if ($curr_user_role_settings == 'subscriber')     $level = 0;

         if (   ( ($current_user->user_level < $level) && (! $is_super_admin)  ) || !is_admin_bar_showing() ) return;


         $wp_admin_bar->add_menu(
                array(
                    'parent' => 'booking_options',
                    'title' => __( 'Settings' ,'booking'),
                    'href' => $link_settings,
                    'id' => 'booking_settings'
                    )
                );
    }


//    if (  $_SERVER['HTTP_HOST'] === 'dev'  ) 
//        define ('OBC_CHECK_URL', 'http://dev/');
//    else
        define ('OBC_CHECK_URL', 'http://wpbookingcalendar.com/');

    function wpdev_ajax_check_bk_news( $sub_url = '' ){

        $v=array();
        if (class_exists('wpdev_bk_personal'))          $v[] = 'wpdev_bk_personal';
        if (class_exists('wpdev_bk_biz_s'))             $v[] = 'wpdev_bk_biz_s';
        if (class_exists('wpdev_bk_biz_m'))             $v[] = 'wpdev_bk_biz_m';
        if (class_exists('wpdev_bk_biz_l'))             $v[] = 'wpdev_bk_biz_l';
        if (class_exists('wpdev_bk_multiuser'))         $v[] = 'wpdev_bk_multiuser';

        $obc_settings = array();
        $ver = get_bk_option('bk_version_data');
        if ( $ver !== false ) { $obc_settings = array( 'subscription_key'=>maybe_serialize($ver) ); }
        
        $params = array(
                    'action' => 'get_news',
                    'subscription_email' => isset($obc_settings['subscription_email'])?$obc_settings['subscription_email']:false,
                    'subscription_key'   => isset($obc_settings['subscription_key'])?$obc_settings['subscription_key']:false,
                    'bk' => array('bk_ver'=>WPDEV_BK_VERSION, 'bk_url'=>WPDEV_BK_PLUGIN_URL,'bk_dir'=>WPDEV_BK_PLUGIN_DIR, 'bk_clss'=>$v),
                    'siteurl'            => get_option('siteurl'),
                    'siteip'            => $_SERVER['SERVER_ADDR'],
                    'admin_email'        => get_option('admin_email')
        );

        $request = new WP_Http();
        if (empty($sub_url)) $sub_url = 'info/';
        $result  = $request->request( OBC_CHECK_URL . $sub_url, array(
            'method' => 'POST',
            'timeout' => 15,
            'body' => $params
            ));

        if (!is_wp_error($result) && ($result['response']['code']=='200') && (true) ) {

           $string = ($result['body']);                                         //$string = str_replace( "'", '&#039;', $string );
           echo $string;
           echo ' <script type="text/javascript"> ';  
           echo '    jQuery("#ajax_bk_respond").after( jQuery("#ajax_bk_respond #bk_news_loaded") );';
           echo '    jQuery("#bk_news_loaded").slideUp(1).slideDown(1500);';
           echo ' </script> ';

        } else  /**/
            { // Some error appear
            echo '<div id="bk_errror_loading">';
            if (is_wp_error($result))  echo $result->get_error_message();
            else                       echo $result['response']['message'];
            echo '</div>';
            echo ' <script type="text/javascript"> ';
            echo '    document.getElementById("bk_news").style.display="none";';
            echo '    jQuery("#ajax_bk_respond").after( jQuery("#ajax_bk_respond #bk_errror_loading") );';
            echo '    jQuery("#bk_errror_loading").slideUp(1).slideDown(1500);';
            echo '    jQuery("#bk_news_section").animate({opacity:1},3000).slideUp(1500);';
            echo ' </script> ';
        }

    }


    function wpdev_ajax_check_bk_version(){
        $v=array();
        if (class_exists('wpdev_bk_personal'))            $v[] = 'wpdev_bk_personal';
        if (class_exists('wpdev_bk_biz_s'))        $v[] = 'wpdev_bk_biz_s';
        if (class_exists('wpdev_bk_biz_m'))   $v[] = 'wpdev_bk_biz_m';
        if (class_exists('wpdev_bk_biz_l'))          $v[] = 'wpdev_bk_biz_l';
        if (class_exists('wpdev_bk_multiuser'))      $v[] = 'wpdev_bk_multiuser';

        $obc_settings = array();
        $params = array(
                    'action' => 'set_register',
                    'order_number'   => isset($_POST['order_num'])?$_POST['order_num']:false,
                    'bk' => array('bk_ver'=>WPDEV_BK_VERSION, 'bk_url'=>WPDEV_BK_PLUGIN_URL,'bk_dir'=>WPDEV_BK_PLUGIN_DIR, 'bk_clss'=>$v),
                    'siteurl'            => get_option('siteurl'),
                    'siteip'            => $_SERVER['SERVER_ADDR'],
                    'admin_email'        => get_option('admin_email')
        );

        update_bk_option( 'bk_version_data' ,  serialize($params) );

        $request = new WP_Http();
        $result  = $request->request( OBC_CHECK_URL . 'register/', array(
            'method' => 'POST',
            'timeout' => 15,
            'body' => $params
            ));

        if (!is_wp_error($result) && ($result['response']['code']=='200') && (true) ) {

           $string = ($result['body']);                                         //$string = str_replace( "'", '&#039;', $string );
           echo $string;
           echo ' <script type="text/javascript"> ';  
           echo '    jQuery("#ajax_respond").after( jQuery("#ajax_respond #bk_registration_info") );';           
           echo ' </script> ';
           
        } else  /**/
            { // Some error appear
            echo '<div id="bk_errror_loading" class="warning_message" style="font-weight:normal;font-size:1.2em;">';
            echo '<h3>'; _e('Warning! Some error occur, during sending registration request.' ,'booking'); echo '</h3>';
            
            if (is_wp_error($result))  echo $result->get_error_message();
            else                       echo $result['response']['message'];
            echo '<br /><br />';
            _e('Please refresh this page and if the same error appear again contact support by email (with  info about order number and website) for finishing the registrations' ,'booking'); echo ' <a href="mailto:activate@wpbookingcalendar.com">activate@wpbookingcalendar.com</a>';
            echo '</strong></div>';
            echo ' <script type="text/javascript"> ';
            echo '    document.getElementById("ajax_message").style.display="none";';
            
            echo '    jQuery("#ajax_respond").after( jQuery("#ajax_respond #bk_errror_loading") );';
            echo '    jQuery("#bk_errror_loading").slideUp(1).slideDown(1500);';
            
            echo '    jQuery("#recheck_version").animate({opacity:1},3000).slideUp(1500);';
            echo ' </script> ';
        }


    }

   // add_action('init', 'check_OBC_activation');
   // define ('OBC_UPDATE_URL', 'http://w/activate/?my_act=1');

    function check_OBC_activation(){

            $obc_settings = array( 'subscription_email' => 'name@server.com', 'subscription_key' => 'dfsdfsdfasdfcewqrwrq33454c4wrewr5');

            $request = wp_remote_post(OBC_UPDATE_URL, array(
                'timeout' => 15,
                'body' => array(
                    'action' => 'activation',
                    'subscription_email' => isset($obc_settings['subscription_email'])?$obc_settings['subscription_email']:false,
                    'subscription_key' => isset($obc_settings['subscription_key'])?$obc_settings['subscription_key']:false,
                    'plugins' => $plugins,                    
                    )));

//debuge($request);
            
            if ( is_wp_error($request) )     $res = false;
            else                             $res = maybe_unserialize($request['body']);
    }

    // Show Ajax message at the top of page //////////////////////////////////////////////////////////////////////////////////////////////////////
    if (!function_exists ('wpdev_bk_show_ajax_message')) {
         function wpdev_bk_show_ajax_message($mess, $show_time = 3000, $is_hide=false) {
        ?> <script type="text/javascript"> 
            document.getElementById('ajax_working').innerHTML =
            '<div class="updated ajax_message" id="ajax_message">\n\
                <div style="float:left;">'+'<?php echo $mess; ?>'+'</div> \n\
                <div class="wpbc_spin_loader">\n\
                       <img src="<?php echo WPDEV_BK_PLUGIN_URL; ?>/img/ajax-loader.gif">\n\
                </div>\n\
            </div>';

            jQuery('.updated.ajax_message').animate({opacity: 1},<?php echo $show_time; ?>);
            <?php if($is_hide) { ?> jQuery('.updated.ajax_message').fadeOut(2000); <?php } ?>

        </script> <?php
    }
    }

    function wpdevbk_show_booking_footer(){
        $wpdev_copyright_adminpanel  = get_bk_option( 'booking_wpdev_copyright_adminpanel' );             // check
        if ( ( $wpdev_copyright_adminpanel !== 'Off' ) && ( ! wpdev_bk_is_this_demo() ) ) {
            $message = '';
            $message .= '<a target="_blank" href="http://wpbookingcalendar.com/">Booking Calendar</a> ' . __('version' ,'booking') . ' ' . WP_BK_VERSION_NUM ;

            $message .= ' | '. sprintf(__('Add your %s on %swordpress.org%s, if you enjoyed by this plugin.' ,'booking'), 
                            '<a target="_blank" href="http://goo.gl/tcrrpK" >&#9733;&#9733;&#9733;&#9733;&#9733;</a>',
                            '<a target="_blank" href="http://goo.gl/tcrrpK" >',
                            '</a>'   );

            echo '<div id="wpbc-footer" style="position:absolute;bottom:40px;text-align:left;width:95%;font-size:10px;text-shadow:0 1px 0 #fff;margin:0;color:#888;">' . $message . '</div>';
            ?>
            <script type="text/javascript">
                jQuery(document).ready(function(){
                    jQuery('#wpfooter').append( jQuery('#wpbc-footer') );
                });
            </script>
            <?php
        }
    }
?>