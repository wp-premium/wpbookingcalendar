<?php
if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

class wpdev_booking {

    // <editor-fold defaultstate="collapsed" desc="  C O N S T R U C T O R  &  P r o p e r t i e s ">

    var $icon_button_url;
    var $prefix;
    var $settings;
    var $wpdev_bk_personal;
    var $captcha_instance;

    function __construct() {

        // Add settings top line before all other menu items.
        add_bk_action('wpdev_booking_settings_top_menu', array($this, 'settings_menu_top_line'));
        add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_menu_content'));

        $this->captcha_instance = new wpdevReallySimpleCaptcha();
        $this->prefix = 'wpdev_bk';
        $this->settings = array(  'custom_buttons' =>array(),
                'custom_buttons_func_name_from_js_file' => 'set_bk_buttons', //Edit this name at the JS file of custom buttons
                'custom_editor_button_row'=>1 );
        $this->icon_button_url = WPDEV_BK_PLUGIN_URL . '/img/bc-16x16.png';

        if ( class_exists('wpdev_bk_personal'))  $this->wpdev_bk_personal = new wpdev_bk_personal();
        else                                     $this->wpdev_bk_personal = false;

        // Create admin menu
        add_action('admin_menu', array(&$this, 'add_new_admin_menu'));

        // Set loading translation
        add_action('plugins_loaded', 'load_bk_Translation',1000);
        // Check content according language shortcodes
        add_bk_filter('wpdev_check_for_active_language', 'wpdev_check_for_active_language');   

        // Show dashboard widget for the settings
        add_bk_action('dashboard_bk_widget_show',    array(&$this, 'dashboard_bk_widget_show'));
        
        // User defined - hooks
        add_action('wpdev_bk_add_calendar', array(&$this, 'add_calendar_action') ,10 , 2);
        add_action('wpdev_bk_add_form',     array(&$this, 'add_booking_form_action') ,10 , 2);
        add_bk_action('wpdevbk_add_form',   array(&$this, 'add_booking_form_action'));
        add_filter('wpdev_bk_get_form',            array(&$this, 'get_booking_form_action') ,10 , 2);
        add_bk_filter('wpdevbk_get_booking_form',  array(&$this, 'get_booking_form_action'));
        add_filter('wpdev_bk_get_showing_date_format', array(&$this,'get_showing_date_format') ,10 , 1);
        add_filter('wpdev_bk_is_next_day',             array(&$this,'is_next_day') ,10 , 2);
                        
        // Get script for calendar activation
        add_bk_filter('get_script_for_calendar', array(&$this, 'get_script_for_calendar'));  
        add_bk_filter('pre_get_calendar_html',   array(&$this, 'pre_get_calendar_html'));  

        // S H O R T C O D E s - Booking
        add_shortcode('booking', array(&$this, 'booking_shortcode'));
        add_shortcode('bookingcalendar', array(&$this, 'booking_calendar_only_shortcode'));
        add_shortcode('bookingform', array(&$this, 'bookingform_shortcode'));
        add_shortcode('bookingedit', array(&$this, 'bookingedit_shortcode'));
        add_shortcode('bookingsearch', array(&$this, 'bookingsearch_shortcode'));
        add_shortcode('bookingsearchresults', array(&$this, 'bookingsearchresults_shortcode'));
        add_shortcode('bookingselect', array(&$this, 'bookingselect_shortcode'));
        add_shortcode('bookingresource', array(&$this, 'bookingresource_shortcode'));        

        add_shortcode('bookingtimeline', array(&$this, 'bookingtimeline_shortcode'));

        // Add settings link at the plugin page
        add_filter('plugin_action_links', array(&$this, 'plugin_links'), 10, 2 );
        add_filter('plugin_row_meta', array(&$this, 'plugin_row_meta_bk'), 10, 4 );


        add_action('wp_dashboard_setup', array($this, 'dashboard_bk_widget_setup'));
        add_bk_action('wpdev_booking_technical_booking_section', array(&$this, 'wpdev_booking_technical_booking_section'));

        
        // Install / Uninstall
        register_activation_hook( WPDEV_BK_FILE, array(&$this,'wpdev_booking_activate_initial' ));
        register_deactivation_hook( WPDEV_BK_FILE, array(&$this,'wpdev_booking_deactivate' ));
        add_filter('upgrader_post_install', array(&$this, 'install_in_bulk_upgrade'), 10, 2); //Todo: fix Upgrade during bulk upgrade of plugins

        add_bk_action('wpdev_booking_activate_user', array(&$this, 'wpdev_booking_activate'));
        //////////////////////////////////////////////////////////////////////////////////////////////////////////////////


        
        if(defined('WP_ADMIN')){
            $booking_version_num = get_option( 'booking_version_num');        
            if ($booking_version_num === false ) $booking_version_num = '0';
            if ( version_compare(WP_BK_VERSION_NUM, $booking_version_num) > 0 ){
                
                add_action('plugins_loaded', array(&$this,'wpdev_booking_activate_initial'));
                            
            } else {    // Check if we was update from  free to paid or from lower to  higher versions,  and do not make normal  activation. In this case we need to make it.
                
                $is_make_activation = false;
        
                
                if ( ( class_exists('wpdev_bk_personal') ) && ( ! wpbc_is_table_exists('bookingtypes') )  ) 
                    $is_make_activation = true;
                
                if ( (! $is_make_activation) && ( class_exists('wpdev_bk_biz_s') ) && (wpbc_is_field_in_table_exists('booking','pay_request') == 0) )     
                    $is_make_activation = true;
                                
                if ( (! $is_make_activation) && ( class_exists('wpdev_bk_biz_m') ) && ( ! wpbc_is_table_exists('booking_types_meta') )  ) 
                    $is_make_activation = true;
                
                if ( (! $is_make_activation) && ( class_exists('wpdev_bk_biz_l') ) && ( ! wpbc_is_table_exists('booking_coupons') )  )                 
                    $is_make_activation = true;
                
                if ( (! $is_make_activation) && ( class_exists('wpdev_bk_multiuser') ) && (wpbc_is_field_in_table_exists('booking_coupons','users') == 0) ) 
                    $is_make_activation = true;
                
                if ( $is_make_activation ) {
                    // add_action('admin_init', array(&$this,'silent_deactivate_WPBC'));
                    add_action('plugins_loaded', array(&$this,'wpdev_booking_activate_initial'));                    
                }
            }
        }
        
    }
    // </editor-fold>


    // <editor-fold defaultstate="collapsed" desc="    Dashboard Widget Setup   ">

     // Setup Booking widget for dashboard
    function dashboard_bk_widget_setup(){
        
        $is_user_activated = apply_bk_filter('multiuser_is_current_user_active',  true );           //FixIn: 6.0.1.17
        if ( ! $is_user_activated  )
            return false;
        
        
        $user_role = get_bk_option( 'booking_user_role_booking' );
        if ( $user_role == 'administrator' )  $user_role = 'activate_plugins';
        if ( $user_role == 'editor' )         $user_role = 'publish_pages';
        if ( $user_role == 'author' )         $user_role = 'publish_posts';
        if ( $user_role == 'contributor' )    $user_role = 'edit_posts';
        if ( $user_role == 'subscriber')      $user_role = 'read';
        if ( ! current_user_can( $user_role ) ) 
            return;
        
       // if (current_user_can('manage_options')) {
        $bk_dashboard_widget_id = 'booking_dashboard_widget';
        wp_add_dashboard_widget( $bk_dashboard_widget_id,
                                 sprintf(__('Booking Calendar' ,'booking') ),
                                 array($this, 'dashboard_bk_widget_show'),
                                null);

        //$dashboard_widgets_order = (array)get_user_option( "meta-box-order_dashboard" );
        //debuge($dashboard_widgets_order);
        //$dashboard_widgets_order['normal']='';
        //$dashboard_widgets_order['side']='';
        //$user = wp_get_current_user();
        //update_user_option($user->ID, 'meta-box-order_dashboard', $dashboard_widgets_order);
        global $wp_meta_boxes;
        $normal_dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

        if (isset($normal_dashboard[$bk_dashboard_widget_id])){
            // Backup and delete our new dashbaord widget from the end of the array
            $example_widget_backup = array($bk_dashboard_widget_id => $normal_dashboard[$bk_dashboard_widget_id]);
            unset($normal_dashboard[$bk_dashboard_widget_id]);
        } else $example_widget_backup = array();

        if ( is_array($normal_dashboard) ) {                        // Sometimes, some other plugins can modify this item, so its can be not a array
            // Merge the two arrays together so our widget is at the beginning
            if (is_array($normal_dashboard))
                $sorted_dashboard = array_merge($example_widget_backup, $normal_dashboard);
            else $sorted_dashboard = $example_widget_backup;
            // Save the sorted array back into the original metaboxes
            $wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
        }
        //}
    }


    // Show Booking Dashboard Widget content
    function dashboard_bk_widget_show() {
        
        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce_dashboard" ,  true , true );

        global $wpdb;
        $bk_admin_url = 'admin.php?page='. WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking&wh_approved=' ;

        if  (wpbc_is_field_in_table_exists('booking','is_new') == 0)  $update_count = 0;  // do not created this field, so do not use this method
        else                                                            $update_count = getNumOfNewBookings();

        if ($update_count > 0) {
            $update_count_title = "<span class='update-plugins count-$update_count' title=''><span class='update-count bk-update-count'>" . number_format_i18n($update_count) . "</span></span>" ;
        } else 
            $update_count_title = '0';

        
        $my_resources = '';
        if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
            $is_superadmin = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
            $user = wp_get_current_user();
            $user_bk_id = $user->ID;
            if (! $is_superadmin) { // User not superadmin
                $bk_ids = apply_bk_filter('get_bk_resources_of_user',false);
                if ($bk_ids !== false) {                      
                  foreach ($bk_ids as $bk_id) { $my_resources .= $bk_id->ID . ','; }
                  $my_resources = substr($my_resources,0,-1);
                }
            }
        }


        $sql_req = "SELECT DISTINCT bk.booking_id as id, dt.approved, dt.booking_date, bk.modification_date as m_date , bk.is_new as new
                    FROM {$wpdb->prefix}bookingdates as dt
                    INNER JOIN {$wpdb->prefix}booking as bk
                        ON bk.booking_id = dt.booking_id " ;
        if ($my_resources!='') $sql_req .=     " WHERE  bk.booking_type IN ({$my_resources})";

        $sql_req .=     "ORDER BY dt.booking_date" ;

        $sql_results =  $wpdb->get_results( $sql_req ) ;

        $bk_array = array();
        if (! empty($sql_results))
            foreach ($sql_results as $v) {
                if (! isset($bk_array[$v->id]) ) $bk_array[$v->id] = array( 'dates'=>array() , 'bk_today'=>0, 'm_today'=>0 );

                $bk_array[$v->id]['id'] = $v->id ;
                $bk_array[$v->id]['approved'] = $v->approved ;
                $bk_array[$v->id]['dates'][] = $v->booking_date ;
                $bk_array[$v->id]['m_date'] = $v->m_date ;
                $bk_array[$v->id]['new'] = $v->new ;
                if ( is_today_date($v->booking_date) ) $bk_array[$v->id]['bk_today'] = 1 ;
                if ( is_today_date($v->m_date) ) $bk_array[$v->id]['m_today'] = 1 ;
            }

        $counter_new = $counter_pending = $counter_all = $counter_approved = 0;
        $counter_bk_today = $counter_m_today = 0;

        if (! empty($bk_array))
            foreach ($bk_array as $k=>$v) {
                $counter_all++;
                if ($v['approved']) $counter_approved++;
                else                $counter_pending++;
                if ($v['new'])      $counter_new++;

                if ($v['m_today'])  $counter_m_today++;
                if ($v['bk_today']) $counter_bk_today++;
            }

        ?>
        <style type="text/css">

            #dashboard_bk {
                width:100%;
            }
            #dashboard_bk .bk_dashboard_section {
                float:left;
                margin:0px;
                padding:0px;
                width:100%;
            }
            #dashboard-widgets-wrap #dashboard_bk .bk_dashboard_section {
               width:49%;
            }
            #dashboard-widgets-wrap #dashboard_bk .bk_right {
                float:right
            }
            #dashboard_bk .bk_header {
                color: #555555;
                font-size: 13px;
                font-weight: 600;
                line-height: 1em;
            }
            #dashboard_bk .bk_table {
                background:none repeat scroll 0 0 #FFFBFB;
                border-bottom:none;
                border-top:1px solid #ECECEC;
                margin:6px 0 10px 6px;
                padding:2px 10px;
                width:95%;
                -border-radius:4px;
                -moz-border-radius:4px;
                -webkit-border-radius:4px;
                -moz-box-shadow:0 0 2px #C5C3C3;
                -webkit-box-shadow:0 0 2px #C5C3C3;
                -box-shadow:0 0 2px #C5C3C3;
            }
            #dashboard_bk table.bk_table td{
                border-bottom:1px solid #DDDDDD;
                line-height:19px;
                padding:4px 0px 4px 10px;
                font-size:13px;
            }
            #dashboard_bk table.bk_table tr td.first{
               text-align:center;
               padding:4px 0px;
            }
            #dashboard_bk table.bk_table tr td a {
                text-decoration: none;
            }
            #dashboard_bk table.bk_table tr td a span{
                font-size:18px;
                font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
            }
            #dashboard_bk table.bk_table td.bk_spec_font a{
                font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                font-size:14px;
            }
            #dashboard_bk table.bk_table td.bk_spec_font {
                font-family: Georgia,"Times New Roman","Bitstream Charter",Times,serif;
                font-size:13px;
            }
            #dashboard_bk table.bk_table td.pending a{
                color:#E66F00;
            }
            #dashboard_bk table.bk_table td.new-bookings a{
                color:red;
            }
            #dashboard_bk table.bk_table td.actual-bookings a{
                color:green;
            }
            #dashboard-widgets-wrap #dashboard_bk .border_orrange, #dashboard_bk .border_orrange {
                border:1px solid #EEAB26;
                background: #FFFBCC;
                padding:0px;
                width:98%;  clear:both;
                margin:5px 5px 20px;
                border-radius:10px;
                -webkit-border-radius:10px;
                -moz-border-radius:10px;
            }
            #dashboard_bk .bk_dashboard_section h4 {
                font-size:13px;
                margin:10px 4px;
            }
            #bk_errror_loading {
                 text-align: center;
                 font-style: italic;
                 font-size:11px;
            }
        </style>
        
        <div id="dashboard_bk" >
            <div class="bk_dashboard_section bk_right">
                <span class="bk_header"><?php _e('Statistic' ,'booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_is_new=1&wh_booking_date=3&view_mode=vm_listing'; ?>"><span class=""><?php echo $counter_new; ?></span></a> </td>
                        <td class=""> <a href="<?php echo $bk_admin_url,'&wh_is_new=1&wh_booking_date=3&view_mode=vm_listing'; ?>"><?php _e('New (unverified) booking(s)' ,'booking');?></a></td>
                    </tr>
                    <tr>
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_approved=0&wh_booking_date=3&view_mode=vm_listing'; ?>"><span class=""><?php echo $counter_pending; ?></span></a></td>
                        <td class="pending"><a href="<?php echo $bk_admin_url,'&wh_approved=0&wh_booking_date=3&view_mode=vm_listing'; ?>" class=""><?php _e('Pending booking(s)' ,'booking');?></a></td>
                    </tr>
                </table>
            </div>

            <div class="bk_dashboard_section" >
                <span class="bk_header"><?php _e('Agenda' ,'booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_modification_date=1&wh_booking_date=3&view_mode=vm_listing'; ?>"><span><?php echo $counter_m_today; ?></span></a> </td>
                        <td class="new-bookings"><a href="<?php echo $bk_admin_url,'&wh_modification_date=1&wh_booking_date=3&view_mode=vm_listing'; ?>" class=""><?php _e('New booking(s) made today' ,'booking');?></a> </td>
                    </tr>
                    <tr>
                        <td class="first"> <a href="<?php echo $bk_admin_url,'&wh_booking_date=1&view_mode=vm_listing'; ?>"><span><?php echo $counter_bk_today; ?></span></a> </td>
                        <td class="actual-bookings"> <a href="<?php echo $bk_admin_url,'&wh_booking_date=1&view_mode=vm_listing'; ?>" class=""><?php _e('Bookings for today' ,'booking');?></a> </td>
                    </tr>
                </table>
            </div>
            <div style="clear:both;margin-bottom:20px;"></div>
            <?php
            $version = 'free';
            $version = get_bk_version();
            if ( wpdev_bk_is_this_demo() ) 
                $version = 'free';

            if ( ($version !== 'free') && ( class_exists('wpdev_bk_multiuser') === false ) ) { ?>
                <div class="bk_dashboard_section border_orrange" id="bk_upgrade_section"> 
                    <div style="padding:0px 10px;width:96%;">
                        <h4><?php _e('Upgrade to higher versions' ,'booking') ?>:</h4>
                        <p> Check additional advanced functionality, which exist in higher versions and can be interesting for you <a href="http://wpbookingcalendar.com/features/" target="_blank">here &raquo;</a></p>
                        <p> <a class="button button-primary" style="font-size: 1.1em;font-weight: bold;height: 2.5em;line-height: 1.1em;padding: 8px 25px;"  href="<?php echo wpbc_up_link(); ?>" target="_blank"><?php if ( wpbc_get_ver_sufix() == '' ) { _e('Purchase' ,'booking'); } else { _e('Upgrade Now' ,'booking'); } ?></a> </p>
                    </div>
                </div>
                <div style="clear:both;"></div>
                <?php if ( wpbc_get_ver_sufix() != '' ) { ?>
                    <script type="text/javascript">
                        jQuery(document).ready(function(){
                            jQuery('#bk_upgrade_section').animate({opacity:1},5000).fadeOut(2000);
                        });
                    </script>
                <?php } ?>
            <?php } ?>


            <div class="bk_dashboard_section" >
                <span class="bk_header"><?php _e('Current version' ,'booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td style="width:35%;text-align: right;;" class=""><?php _e('Version' ,'booking');?>:</td>
                        <td style="color: #e50;font-family: Arial;font-size: 13px;font-weight: bold;text-align: left;text-shadow: 0 -1px 0 #eee;;" 
                            class="bk_spec_font"><?php 
                        if ( substr( WPDEV_BK_VERSION, 0, 2 ) == '9.' ) {
                            $show_version =  substr( WPDEV_BK_VERSION , 2 ) ;                            
                            if ( substr($show_version, ( -1 * ( strlen( WP_BK_VERSION_NUM ) ) ) ) === WP_BK_VERSION_NUM ) {
                                $show_version = substr($show_version, 0, ( -1 * ( strlen( WP_BK_VERSION_NUM ) ) - 1 ) );
                                $show_version = str_replace('.', ' ', $show_version) . " <sup><strong style='font-size:12px;'>" . WP_BK_VERSION_NUM . "</strong></sup>" ;
                            }                                           
                            echo $show_version ; 
                        } else 
                            echo WPDEV_BK_VERSION;
                        ?></td>
                    </tr>
                    <?php if ($version != 'free') { ?>
                    <tr>
                        <td style="width:35%;text-align: right;" class="first b"><?php _e('Type' ,'booking');?>:</td>
                        <td style="text-align: left;  font-weight: bold;" class="bk_spec_font"><?php $ver = get_bk_version();if (class_exists('wpdev_bk_multiuser')) $ver = 'multiUser';$ver = str_replace('_m', ' Medium',$ver);$ver = str_replace('_l', ' Large',$ver);$ver = str_replace('_s', ' Small',$ver);$ver = str_replace('biz', 'Business',$ver); echo ucwords($ver);  ?></td>
                    </tr>
                    <tr>
                        <td style="width:35%;text-align: right;" class="first b"><?php _e('Used for' ,'booking');?>:</td>
                        <td style="text-align: left;  font-weight: bold;" class="bk_spec_font"><?php 
                                $v_type = '';
                                if( strpos( strtolower(WPDEV_BK_VERSION) , 'multisite') !== false  ) {
                                    $v_type = '5';
                                } else if( strpos( strtolower(WPDEV_BK_VERSION) , 'develop') !== false  ) {
                                    $v_type = '2';
                                }
                                if ( ! empty($v_type) ) { 
                                    echo ' ' .$v_type . ' '. __('websites' ,'booking'); 
                                } else {
                                    echo ' 1' . ' '. __('website' ,'booking'); 
                                }
                        ?></td>
                    </tr>
                    <?php } ?>
                    <tr>
                        <td style="width:35%;text-align: right;" class="first b"><?php _e('Release date' ,'booking');?>:</td>
                        <td style="text-align: left;  font-weight: bold;" class="bk_spec_font"><?php echo date ("d.m.Y", filemtime(WPDEV_BK_FILE)); ?></td>
                    </tr>
                    
                </table>
                
                <table class="bk_table"style="border:none;">
                    <tr >
                        <td colspan="2" style="border:none;text-align:center;" class=""><?php 
                            if ($version == 'free') { 
                                ?><a class="button-primary button" style="font-weight:bold;" target="_blank" href="http://wpbookingcalendar.com/overview/"><?php _e('Check Premium Features' ,'booking');?></a><?php                             
                            } elseif  ( wpbc_get_ver_sufix() != '' )  { 
                                ?><a class="button-primary button"  href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ; ?>wpdev-booking-option&tab=upgrade"><?php _e('Upgrade' ,'booking');?></a><?php                                                             
                            } else {
                                ?><a class="button-primary button" target="_blank" href="http://wpbookingcalendar.com/overview/"><?php _e('Explore Premium Features' ,'booking');?></a><?php                                                             
                            }
                      ?></td>
                    </tr>
                    
                </table>
            </div>

            <div class="bk_dashboard_section bk_right">
                <span class="bk_header"><?php _e('Support' ,'booking');?>:</span>
                <table class="bk_table">
                    <tr class="first">
                        <td style="text-align:center;" class="bk_spec_font"><a  
                            href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'wpbc-getting-started' ), 'index.php' ) ) ); ?>"
                            ><?php _e('Getting Started' ,'booking');?></a>
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://wpbookingcalendar.com/help/"><?php _e('Help Info' ,'booking');?></a></td>
                    </tr>
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="http://wpbookingcalendar.com/faq/"><?php _e('FAQ' ,'booking');?></a></td>
                    </tr>
                    <tr class="">
                        <td style="text-align:center;" class="bk_spec_font"><a href="mailto:support@wpbookingcalendar.com"><?php _e('Contact email' ,'booking');?></a></td>
                    </tr>                                        
                    <tr>
                        <td style="text-align:center;" class="bk_spec_font"><a target="_blank" href="https://wordpress.org/plugins/booking/"><?php _e('Rate plugin (thanks:)' ,'booking');?></a></td>
                    </tr>
                    
                </table>
            </div>

            <div style="clear:both;"></div>

            
            <div style="width:95%;border:none; clear:both;margin:10px 0px;" id="bk_news_section"> <!-- Section 4 -->
                <div style="width: 96%; margin-right: 0px;; " >
                    <span class="bk_header">Booking Calendar News:</span><br/>
                    <div id="bk_news" class="rssSummary"> <span style="font-size:13px;text-align:center;">Loading...</span></div>
                    <div id="ajax_bk_respond" class="rssSummary"  style="display:none;"></div>
                    <script type="text/javascript">

                        jQuery.ajax({                                           // Start Ajax Sending
                            // url: '<?php echo WPDEV_BK_PLUGIN_URL , '/' ,  WPDEV_BK_PLUGIN_FILENAME ; ?>' ,
                            url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
                            type:'POST',
                            success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_bk_respond').html( data );},
                            error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;
                                //alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);
                                //if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#faq-13');}
                            },
                            // beforeSend: someFunction,
                            data:{
                                // ajax_action : 'CHECK_BK_NEWS',
                                action : 'CHECK_BK_NEWS',
                                wpbc_nonce: document.getElementById('wpbc_admin_panel_nonce_dashboard').value
                            }
                        });

                    </script>                           
                </div>
            </div>
            
            <div style="clear:both;"></div>            
        </div>

        <div style="clear:both;"></div>
        <!--div id="modal_content1" style="display:block;width:100%;height:100px;" class="modal_content_text" >
          <iframe src="http://wpbookingcalendar.com/purchase/#content" style="border:1px solid red; width:100%;height:100px;padding:0px;margin:0px;"></iframe>
        </div-->
        <?php
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="    Update info of plugin at the plugins section   ">
    // Functions for using at future versions: update info of plugin at the plugins section.
    function plugin_row_meta_bk($plugin_meta, $plugin_file, $plugin_data, $context) {

        $this_plugin = plugin_basename(WPDEV_BK_FILE);

        if ($plugin_file == $this_plugin ) {

            $is_delete_if_deactive =  get_bk_option( 'booking_is_delete_if_deactive' ); // check

            if ($is_delete_if_deactive == 'On') { ?>
                <div class="plugin-update-tr">
                <div class="update-message">
                    <strong><?php _e('Warning !!!' ,'booking'); ?> </strong>
                    <?php _e('All booking data will be deleted when the plugin is deactivated.' ,'booking'); ?><br />
                    <?php printf(__('If you want to save your booking data, please uncheck the %s"Delete booking data"%s at the' ,'booking'), '<strong>','</strong>'); ?>
                    <a href="admin.php?page=<?php echo WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME; ?>wpdev-booking-option"> <?php _e('settings page' ,'booking'); ?> </a>
                </div>
                </div>
                <?php
            }

            /*
            [$plugin_meta] => Array
                (
                    [0] => Version 2.8.35
                    [1] => By wpdevelop
                    [2] => Visit plugin site
                )

            [$plugin_file] => booking/wpdev-booking.php
            [$plugin_data] => Array
                (
                    [Name] => Booking Calendar
                    [PluginURI] => http://wpbookingcalendar.com/demo/
                    [Version] => 2.8.35
                    [Description] => Online booking and availability checking service for your site.
                    [Author] => wpdevelop
                    [AuthorURI] => http://wpbookingcalendar.com/
                    [TextDomain] =>
                    [DomainPath] =>
                    [Network] =>
                    [Title] => Booking Calendar
                    [AuthorName] => wpdevelop
                )

            [$context] => all
            /**/

            // Echo plugin description here
                   return $plugin_meta;
        } else     return $plugin_meta;
    }






    // Adds Settings link to plugins settings
    function plugin_links($links, $file) {

        $this_plugin = plugin_basename(WPDEV_BK_FILE);

        if ($file == $this_plugin) {

            $settings_link = '<a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option">'.__("Settings" ,'booking').'</a>';
            
            array_unshift($links, $settings_link);
            
            $settings_link = '<a title="'.__("Check new functionality in this plugin update." ,'booking').'" href="'. esc_url( admin_url( add_query_arg( array( 'page' => 'wpbc-about' ), 'index.php' ) ) ) .'">'.__("What's New" ,'booking').'</a>';
            
            array_unshift($links, $settings_link);
        }
        return $links;
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="  ADMIN MENU SECTIONS   ">
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // ADMIN MENU SECTIONS  ///////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function add_new_admin_menu() {
        $users_roles = array(
            get_bk_option( 'booking_user_role_booking' ),
            get_bk_option( 'booking_user_role_addbooking' ),
            get_bk_option( 'booking_user_role_settings' ) ,
            get_bk_option( 'booking_user_role_resources' )
            );

        for ($i = 0 ; $i < count($users_roles) ; $i++) {
            
            if ( empty($users_roles[$i]) )              $users_roles[$i] = 'editor';         //Fix: 2015-03-02 Need to have this fix for WordPress MU installations.             
            
            if ( $users_roles[$i] == 'administrator' )  $users_roles[$i] = 'manage_options'; //Fix: 2015-03-02 Need to have this fix for WordPress MU installations.             
            if ( $users_roles[$i] == 'editor' )         $users_roles[$i] = 'publish_pages';
            if ( $users_roles[$i] == 'author' )         $users_roles[$i] = 'publish_posts';
            if ( $users_roles[$i] == 'contributor' )    $users_roles[$i] = 'edit_posts';
            if ( $users_roles[$i] == 'subscriber')      $users_roles[$i] = 'read';
        }
        

        if  (wpbc_is_field_in_table_exists('booking','is_new') == 0)  $update_count = 0;  // do not created this field, so do not use this method
        else                                                            $update_count = getNumOfNewBookings();

        $title = __('Booking' ,'booking');
        $update_title = $title;
        $is_user_activated = apply_bk_filter('multiuser_is_current_user_active',  true );           //FixIn: 6.0.1.17
        if ( ( $update_count > 0 ) && ( $is_user_activated ) ) {
            $update_count_title = "<span class='update-plugins count-$update_count' title='$update_title'><span class='update-count bk-update-count'>" . number_format_i18n($update_count) . "</span></span>" ;
            $update_title .= $update_count_title;
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // M A I N     B O O K I N G
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook1 = add_menu_page( __('Booking calendar' ,'booking'),  $update_title , $users_roles[0],
                WPDEV_BK_FILE . 'wpdev-booking', array(&$this, 'on_show_booking_page_main'),  WPDEV_BK_PLUGIN_URL . '/img/bc-16x16.png'  );
        add_action("admin_print_scripts-" . $pagehook1 , array( &$this, 'load_admin_scripts_on_page'));        
        add_action("admin_print_styles-".   $pagehook1 , array( &$this, 'load_css_skip_client_css') );     //Fix. 5.3 We need to load at client side and admin  side styles for having correct  calendar skins at  datepick elemtns. This hook for loading the styles at  the header of page   
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S E R V A T I O N
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook2 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Add booking' ,'booking'), __('Add booking' ,'booking'), $users_roles[1],
                WPDEV_BK_FILE .'wpdev-booking-reservation', array(&$this, 'on_show_booking_page_addbooking')  );
        add_action("admin_print_scripts-" . $pagehook2 , array( &$this, 'load_admin_client_scripts_on_page'));
        add_action("admin_print_styles-".   $pagehook2 , array( &$this, 'load_admin_client_styles_on_page') );     // This hook for loading the styles at  the header of page   
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // A D D     R E S O U R C E S     Management
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $version = get_bk_version();
        //$is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'not_low_level_user'); //Anxo customizarion
        if ($version != 'free') { //Anxo customizarion

            $pagehook4 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Resources' ,'booking'), __('Resources' ,'booking'), $users_roles[3],
                    WPDEV_BK_FILE .'wpdev-booking-resources', array(&$this, 'on_show_booking_page_resources')  );
            add_action("admin_print_scripts-" . $pagehook4 , array( &$this, 'load_admin_scripts_on_page'));
            add_action("admin_print_styles-".   $pagehook4 , array( &$this, 'load_admin_styles_on_page') );     // This hook for loading the styles at  the header of page               
        }

        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        // S E T T I N G S
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $pagehook3 = add_submenu_page(WPDEV_BK_FILE . 'wpdev-booking',__('Booking Calendar Settings' ,'booking'), __('Settings' ,'booking'), $users_roles[2],
                WPDEV_BK_FILE .'wpdev-booking-option', array(&$this, 'on_show_booking_page_settings')  );
        add_action("admin_print_scripts-" . $pagehook3 , array( &$this, 'load_admin_scripts_on_page'));
        add_action("admin_print_styles-".   $pagehook3 , array( &$this, 'load_admin_styles_on_page') );     // This hook for loading the styles at  the header of page               
        
        
        ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

         global $submenu, $menu;               // Change Title of the Main menu inside of submenu
         if (isset($submenu[plugin_basename( WPDEV_BK_FILE ) . 'wpdev-booking']))
            $submenu[plugin_basename( WPDEV_BK_FILE ) . 'wpdev-booking'][0][0] = __('Bookings' ,'booking');
    }

    ////////////////////////////////////////////////////////////////////////////
    // Hooks to load Scripts and Styles on Client and Admin sides  /////////////
    ////////////////////////////////////////////////////////////////////////////
    // JS - Admin
    function load_admin_scripts_on_page() {        
      do_action('wpbc_load_admin_scripts_on_page');        
    }
    // CSS - Admin
    function load_admin_styles_on_page() {
      do_action('wpbc_load_admin_styles_on_page');          
    }
    // JS - Client & Admin
    function load_admin_client_scripts_on_page() {        
        do_action('wpbc_load_admin_client_scripts_on_page','both');
    }
    // CSS - Client & Admin
    function load_admin_client_styles_on_page() {
        do_action('wpbc_load_admin_client_styles_on_page','both');
    }
    
    function load_css_skip_client_css(){
        do_action('wpbc_load_css_skip_client_css');
    }
    ////////////////////////////////////////////////////////////////////////////
    
    // Booking page
    function on_show_booking_page_main() {

        $this->on_show_page_adminmenu('wpdev-booking','/img/bc-16x16.png', __('Bookings listing' ,'booking'),1);
    }

    // Add resrvation page
    function on_show_booking_page_addbooking() {

        $this->on_show_page_adminmenu('wpdev-booking-reservation','/img/add-1-48x48.png', __('Add booking' ,'booking'),2);
    }

    // Settings page
    function on_show_booking_page_settings() {

        $this->on_show_page_adminmenu('wpdev-booking-option','/img/General-setting-64x64.png', __('General Settings' ,'booking'),3);
    }

    // Resources page
    function on_show_booking_page_resources() {

        $this->on_show_page_adminmenu('wpdev-booking-resources','/img/Resources-64x64.png', __('Booking resources management' ,'booking'),4);
    }

    // Show content
    function on_show_page_adminmenu($html_id, $icon, $title, $content_type) {
        ?>
        <div id="<?php echo $html_id; ?>-general" class="wrap bookingpage">
            <?php /*
            if ($content_type > 2 )
                echo '<div class="icon32" style="margin:5px 40px 10px 10px;"><img src="'. WPDEV_BK_PLUGIN_URL . $icon .'"><br /></div>' ;
            else
                echo '<div class="icon32" style="margin:10px 25px 10px 10px;"><img src="'. WPDEV_BK_PLUGIN_URL . $icon .'"><br /></div>' ; /**/ ?>
            <h2><?php echo $title; ?></h2>
            <?php
            switch ($content_type) {
                case 1: $this->content_of_booking_page();
                    break;
                case 2: $this->content_of_reservation_page();
                    break;
                case 3: $this->content_of_settings_page();
                    break;
                case 4: $this->content_of_resource_page();
                    break;
                default: break;
            } ?>
        </div>
        <?php
    }
    ///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // </editor-fold>


    
    // <editor-fold defaultstate="collapsed" desc="   S U P P O R T     F U N C T I O N S     ">
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  S U P P O R T     F U N C T I O N S        ///////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function silent_deactivate_WPBC() {
        deactivate_plugins( WPDEV_BK_PLUGIN_DIRNAME.'/'.WPDEV_BK_PLUGIN_FILENAME, true );
    }
    



    // Check if nowday is tommorow from previosday
    function is_next_day($nowday, $previosday) {

        if ( empty($previosday) ) return false;

        $nowday_d = (date('m.d.Y',  mysql2date('U', $nowday ))  );
        $prior_day = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        if ($prior_day == $nowday_d)    return true;                // if its the same date


        $previos_array = (date('m.d.Y',  mysql2date('U', $previosday ))  );
        $previos_array = explode('.',$previos_array);
        $prior_day =  date('m.d.Y' , mktime(0, 0, 0, $previos_array[0], ($previos_array[1]+1), $previos_array[2] ));


        if ($prior_day == $nowday_d)    return true;                // zavtra
        else                            return false;               // net
    }

    // Change date format
    function get_showing_date_format($mydate ) {
        $date_format = get_bk_option( 'booking_date_format');
        if ($date_format == '') $date_format = "d.m.Y";

        $time_format = get_bk_option( 'booking_time_format');
        if ( $time_format !== false  ) {
            $time_format = ' ' . $time_format;
            $my_time = date('H:i:s' , $mydate);
            if ($my_time == '00:00:00')     $time_format='';
        }
        else  $time_format='';

        // return date($date_format . $time_format , $mydate);
        return date_i18n($date_format,$mydate) .'<sup class="booking-table-time">' . date_i18n($time_format  , $mydate).'</sup>';
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="   B O O K I N G s       A D M I N       F U N C T I O N s   ">
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  B  O O K I N G s       A D M I N       F U N C T I O N s       ///////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get dates
    function get_dates ($approved = 'all', $bk_type = 1, $additional_bk_types= array(),  $skip_booking_id = ''  ) {
        /*
        $bk_type_1 = explode(',', $bk_type); $bk_type = '';
        foreach ($bk_type_1 as $bkt) {
            if (!empty($bkt)) { $bk_type .= $bkt . ','; }
        }
        $bk_type = substr($bk_type, 0, -1);

        $additional_bk_types_1= array();
        foreach ($additional_bk_types as $bkt) {
            if (!empty($bkt)) { $additional_bk_types_1[] = $bkt; }
        }
        $additional_bk_types =$additional_bk_types_1;*/

        // if ( ! defined('WP_ADMIN') ) if ($approved == 0)  return array(array(),array());

        make_bk_action('check_pending_not_paid_auto_cancell_bookings', $bk_type );

        if ( count($additional_bk_types)>0 ) $bk_type_additional = $bk_type .',' . implode(',', $additional_bk_types);
        else                                 $bk_type_additional = $bk_type;

        global $wpdb;
        $dates_array = $time_array = array();
        
        if ($approved == 'admin_blank') {

            $sql_req = "SELECT DISTINCT dt.booking_date

                     FROM {$wpdb->prefix}bookingdates as dt

                     INNER JOIN {$wpdb->prefix}booking as bk

                     ON    bk.booking_id = dt.booking_id

                     WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type IN ($bk_type_additional) AND bk.form like '%admin@blank.com%'

                     ORDER BY dt.booking_date" ;
            $dates_approve = $wpdb->get_results(  $sql_req  );
            
        } else {
            
            if ($approved == 'all')
                $sql_req = apply_bk_filter('get_bk_dates_sql', "SELECT DISTINCT dt.booking_date

                     FROM {$wpdb->prefix}bookingdates as dt

                     INNER JOIN {$wpdb->prefix}booking as bk

                     ON    bk.booking_id = dt.booking_id

                     WHERE  dt.booking_date >= CURDATE()  AND bk.booking_type IN ($bk_type_additional)
                         
                     ". (($skip_booking_id != '') ? " AND dt.booking_id NOT IN ( ".$skip_booking_id." ) ":"") ."
                         
                     ORDER BY dt.booking_date", $bk_type_additional, 'all' , $skip_booking_id);

            else
                $sql_req = apply_bk_filter('get_bk_dates_sql', "SELECT DISTINCT dt.booking_date

                     FROM {$wpdb->prefix}bookingdates as dt

                     INNER JOIN {$wpdb->prefix}booking as bk

                     ON    bk.booking_id = dt.booking_id

                     WHERE  dt.approved = $approved AND dt.booking_date >= CURDATE() AND bk.booking_type IN ($bk_type_additional)
                         
                     ". (($skip_booking_id != '') ? " AND dt.booking_id NOT IN ( ".$skip_booking_id." ) ":"") ."

                     ORDER BY dt.booking_date", $bk_type_additional, $approved, $skip_booking_id );

            $dates_approve = apply_bk_filter('get_bk_dates', $wpdb->get_results( $sql_req ), $approved, 0,$bk_type );
        }


        // loop with all dates which is selected by someone
        //$my_previous_date = false;
        if (! empty($dates_approve))
            foreach ($dates_approve as $my_date) {
            
                ////////////////////////////////////////////////////////////////
                // Extend unavailbale interval to extra hours; cleaning time, or any other service time
                /**                            
//                $extra_hours_in  = 1;
//                $extra_hours_out = 1;
//                if ( substr( $my_date->booking_date, -1 ) == '1' )
//                    $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '-' . $extra_hours_in  . ' hour', strtotime( $my_date->booking_date ) ) );
//                if ( substr( $my_date->booking_date, -1 ) == '2' )
//                    $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '+' . $extra_hours_out . ' hour', strtotime( $my_date->booking_date ) ) );

                $extra_minutes_in  = 0;
                $extra_minutes_out = 30;
                if ( substr( $my_date->booking_date, -1 ) == '1' )
                    $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '-' . $extra_minutes_in  . ' minutes', strtotime( $my_date->booking_date ) ) );
                if ( substr( $my_date->booking_date, -1 ) == '2' )
                    $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '+' . $extra_minutes_out . ' minutes', strtotime( $my_date->booking_date ) ) );
                
                // Fix overlap of previous times
                if ( $my_previous_date !== false ) {
                    if (
                           ( substr( $my_date->booking_date, -1 ) == '1' ) 
                        && ( substr( $my_previous_date, -1 ) == '2' )    
                        && ( strtotime( $my_previous_date ) >= strtotime( $my_date->booking_date )  )                                
                       ) {
                        $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '-1 second', strtotime( $my_previous_date ) ) );
                    }                    
                }
                $my_previous_date = $my_date->booking_date;
                /**/
                ////////////////////////////////////////////////////////////////                            
                // Extend unavailbale interval to extra DAYS
                /*
                $extra_days_in  = 0;
                $extra_days_out = 21;
                $initial_check_in_day = $initial_check_out_day = false;
                if ( substr( $my_date->booking_date, -1 ) == '1' )  {
                    $initial_check_in_day = $my_date->booking_date;
                    $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '-' . $extra_days_in . ' day', strtotime( $my_date->booking_date ) ) );
                } 
                if ( substr( $my_date->booking_date, -1 ) == '2' ) {
                    $initial_check_out_day = $my_date->booking_date;
                    $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '+' . $extra_days_out . ' day', strtotime( $my_date->booking_date ) ) );                        
                } 

                // Fix overlap of previous times
                if ( $my_previous_date !== false ) {
                    if (
                           ( substr( $my_date->booking_date, -1 ) == '1' ) 
                        && ( substr( $my_previous_date, -1 ) == '2' )    
                        && ( strtotime( $my_previous_date ) >= strtotime( $my_date->booking_date )  )                                
                       ) {
                        $my_date->booking_date = date( 'Y-m-d H:i:s', strtotime( '-1 second', strtotime( $my_previous_date ) ) );
                    }                    
                }
                $my_previous_date = $my_date->booking_date;

                if ( $initial_check_in_day !== false )
                    for ( $di = 0; $di < $extra_days_in; $di++ ) {
                        $my_block_date = date( 'Y-m-d', strtotime( '-' . $di . ' day', strtotime( $initial_check_in_day ) ) );
                        $my_dt = explode( '-', $my_block_date );
                        array_push( $dates_array , $my_dt );
                        array_push( $time_array , array( '00', '00', '00' ) );                        
                    }

                if (  $initial_check_out_day !== false )
                    for ( $di = 0; $di < $extra_days_out; $di++ ) {
                        $my_block_date = date( 'Y-m-d', strtotime( '+' . $di . ' day', strtotime( $initial_check_out_day ) ) );        
                        $my_dt = explode( '-', $my_block_date );
                        array_push( $dates_array , $my_dt );
                        array_push( $time_array , array( '00', '00', '00' ) );                        
                    }
                 
                /**/
                ////////////////////////////////////////////////////////////////
                
            
                $my_date = explode(' ',$my_date->booking_date);
                
                $my_dt = explode('-',$my_date[0]);
                $my_tm = explode(':',$my_date[1]);

                array_push( $dates_array , $my_dt );
                array_push( $time_array , $my_tm );
            }
       
        return    array($dates_array,$time_array); 
    }

    // Generate booking CAPTCHA fields  for booking form
    function createCapthaContent($bk_tp) {
        $admin_uri = ltrim( str_replace( get_site_url( null, '', 'admin' ), '', admin_url('admin.php?') ), '/' ) ;
        if ( (  get_bk_option( 'booking_is_use_captcha' ) !== 'On' ) 
                || ( strpos($_SERVER['REQUEST_URI'], $admin_uri ) !== false ) 
           ) return '';
        else {
            $this->captcha_instance->cleanup(1);

            $word = $this->captcha_instance->generate_random_word();
            $prefix = mt_rand();
            $this->captcha_instance->generate_image($prefix, $word);

            $filename = $prefix . '.png';
            $captcha_url = WPDEV_BK_PLUGIN_URL . '/js/captcha/tmp/' .$filename;
            $html  = '<input  autocomplete="off" type="text" class="captachinput" value="" name="captcha_input'.$bk_tp.'" id="captcha_input'.$bk_tp.'" />';
            $html .= '<img class="captcha_img"  id="captcha_img' . $bk_tp . '" alt="captcha" src="' . $captcha_url . '" />';
            $ref = substr($filename, 0, strrpos($filename, '.'));
            $html = '<input  autocomplete="off" type="hidden" name="wpdev_captcha_challenge_' . $bk_tp . '"  id="wpdev_captcha_challenge_' . $bk_tp . '" value="' . $ref . '" />'
                    . $html
                    . '<span id="captcha_msg'.$bk_tp.'" class="wpdev-help-message" ></span>';
            return $html;
        }
    }

    // Get default Booking resource
    function get_default_type() {
        if( $this->wpdev_bk_personal !== false ) {
            if (( isset( $_GET['booking_type'] )  )  && ($_GET['booking_type'] != '')) $bk_type = $_GET['booking_type'];
            else $bk_type = $this->wpdev_bk_personal->get_default_booking_resource_id();
        } else $bk_type =1;
        return $bk_type;
    }
    // </editor-fold>


    
    // <editor-fold defaultstate="collapsed" desc="   A D M I N    M E N U    P A G E S    ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //  A D M I N    M E N U    P A G E S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function content_of_booking_page() {

        do_action('wpbc_hook_booking_page_header', 'booking');
        
        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        // Check if this user ACTIVE and can be at this page in MultiUser version
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');
        if (! $is_can) return false;

        // Get default Booking Resource, if not its not set in GET parameter
        if ( ! isset($_GET['booking_type']) ) {
            $default_booking_resource = get_bk_option( 'booking_default_booking_resource');
            if ((isset($default_booking_resource)) && ($default_booking_resource !== false)) {
                $_GET['booking_type']=  $default_booking_resource;
                make_bk_action('check_if_bk_res_parent_with_childs_set_parent_res', $default_booking_resource  );  // Check if this resource parent and has some additional childs if so then assign to parent_res=1
            }
        }

        // Check if User can be here in MultiUser version for this booking resource (is this user owner of this resource or not)
        if (  isset($_GET['booking_type']) ) {
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, $_GET['booking_type']);
            if ( !$is_can) { return ; }
        } else {
            if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
                $bk_multiuser = apply_bk_filter('get_default_bk_resource_for_user',false);
                if ($bk_multiuser == false) return;
            }
        }

        // Booking listing
        ?> <div class="wpdevbk">
            <div id="ajax_working"></div>
            <div class="clear" style="height:1px;"></div>
            <div id="ajax_respond" class="ajax_respond" style="display:none;"></div>
            <?php                
                make_bk_action('write_content_for_popups' );
                //debugq();
                wpdevbk_show_booking_page();
                wpdevbk_show_booking_footer();                
                //debugq(); ?>
           </div><?php
        //debugq();
        do_action('wpbc_hook_booking_page_footer', 'booking');
    }

    //Content of the Add reservation page
    function content_of_reservation_page() {

        do_action('wpbc_hook_add_booking_page_header', 'add_booking');
        
        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');

        if (! $is_can) return false;

        if ( ! isset($_GET['booking_type']) ) {

            $default_booking_resource = get_bk_option( 'booking_default_booking_resource');
            if ((isset($default_booking_resource)) && (! empty($default_booking_resource) )) {
            } else {
                if( $this->wpdev_bk_personal !== false ) {
                    $default_booking_resource = $this->wpdev_bk_personal->get_default_booking_resource_id();
                } else $default_booking_resource = 1;
            }
            $_GET['booking_type'] =  $default_booking_resource;

            make_bk_action('check_if_bk_res_parent_with_childs_set_parent_res', $default_booking_resource  );

            if ( class_exists('wpdev_bk_multiuser')) {  // If MultiUser so
                $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
                if (! $is_can) { // User not superadmin
                    $bk_multiuser = apply_bk_filter('get_default_bk_resource_for_user',false);
                    if ($bk_multiuser == false) return;
                    else $default_booking_resource = $bk_multiuser;
                }
            }

        }

        ?><div id="ajax_working"></div>
          <div class="clear" style="margin:20px;"></div><?php                
          
        if( $this->wpdev_bk_personal !== false )  
            $this->wpdev_bk_personal->booking_types_pages('noedit');
        
        ?><div class="clear" style="margin:20px;"></div><?php

        $bk_type = $this->get_default_type();

        if ($bk_type < 1) {
            if( $this->wpdev_bk_personal !== false ) {
                   $bk_type = $this->wpdev_bk_personal->get_default_booking_resource_id();
            } else $bk_type =1;
            $_GET['booking_type'] = $bk_type;
        }
        if (isset($_GET['booking_type'])) {
            $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, $_GET['booking_type'] ); if ( !$is_can) { return ; }
        }
        echo '<div style="width:100%;">';
        do_action('wpdev_bk_add_form',$bk_type, get_bk_option( 'booking_client_cal_count'));        
        //make_bk_action( 'wpdevbk_add_form', $bk_type , 12, 1, 'standard' , '', false, '{calendar months_num_in_row=4 width=100% cell_height=35px}'  );    //FixIn:6.0.1.6
        ?>
        <div style="float:left;border:none;margin:0px 0 10px 1px; color:#777;">            
            <fieldset>
            <label for="is_send_email_for_new_booking" class="description">   
              <input name="is_send_email_for_new_booking" id="is_send_email_for_new_booking" type="checkbox"
                  checked="CHECKED" 
                  value="On" 
                   />
              <?php _e('Send email notification to customer about this operation' ,'booking');?>
            </label>
            </fieldset>
            
        </div>
        <?php
        echo '</div>';
        wpdevbk_booking_listing_write_js();
        
        do_action('wpbc_hook_add_booking_page_footer', 'add_booking');
    }

    //content of    S E T T I N G S     page  - actions runs
    function content_of_settings_page () {

        do_action('wpbc_hook_settings_page_header', 'settings');

        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');
        if (! $is_can) return false;

        make_bk_action('wpdev_booking_settings_top_menu');            
        ?> <div id="ajax_respond" class="ajax_respond" style="display:none;"></div>
        <div class="clear" ></div>
        <div id="ajax_working"></div>        
        <div id="poststuff0" class="metabox-holder" style="margin-top:0px;">
        <?php
        $is_can = apply_bk_filter('recheck_version', true); if (! $is_can) return;
        make_bk_action('wpdev_booking_settings_show_content'); ?>
        </div> <?php
        wpdevbk_booking_listing_write_js();
        
        do_action('wpbc_hook_settings_page_footer', 'settings');
    }

    //content of resources management page
    function content_of_resource_page(){
        
        do_action('wpbc_hook_resources_page_header', 'resources');
        
        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_nonce" ,  true , true );
        
        $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'check_for_active_users');
        if (! $is_can) return false;

        /* ?> <div style="margin-top:10px;height:1px;clear:both;border-top:1px solid #bbc;"></div>  <?php /**/
        make_bk_action('wpdev_booking_resources_top_menu');
        ?> <div id="ajax_respond" class="ajax_respond" style="display:none;"></div>
        <div class="clear" ></div>
        <div id="ajax_working"></div>
        <div id="poststuff0" class="metabox-holder" style="margin-top:0px;">
        <?php make_bk_action('wpdev_booking_resources_show_content'); ?>            
        </div> <?php
        wpdevbk_booking_listing_write_js();
        
        do_action('wpbc_hook_resources_page_footer', 'resources');
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="  S E T T I N G S     S U P P O R T   F U N C T I O N S    ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // S E T T I N G S     S U P P O R T   F U N C T I O N S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Show top line menu
    function settings_menu_top_line() {

        $is_super_hero = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
        $version = get_bk_version();        
        if (! isset($_GET['tab'])) $_GET['tab'] = 'main';        
        if ( (! $is_super_hero) && ($_GET['tab'] == 'main') ) $_GET['tab'] = 'form';

        global $wpbc_settings;
        
        if ( $is_super_hero ) {
            $wpbc_settings[10] = new WPBC_Settings( array( 'term' => 'main' 
                                                         , 'title'       => __('General' ,'booking')
                                                         , 'description' => __('General Settings' ,'booking')
                                                         , 'icon'        => 'icon-asterisk' ) ); 
        }
        $wpbc_settings[20] = new WPBC_Settings( array( 'term' => 'form' 
                                                     , 'title'       => __('Fields' ,'booking')
                                                     , 'description' => __('Fields Settings' ,'booking')
                                                     , 'icon'        => 'icon-text-height' ) ); 
        $wpbc_settings[30] = new WPBC_Settings( array( 'term' => 'email' 
                                                     , 'title'       => __('Emails' ,'booking')
                                                     , 'description' => __('Emails Settings' ,'booking')
                                                     , 'icon'        => 'icon-envelope' ) ); 
        
        
        if ( $version != 'free' ) {
          if ( ($version == 'biz_s') || ($version == 'biz_l') || ($version == 'biz_m') ) 
          $wpbc_settings[40] = new WPBC_Settings( array( 'term' => 'payment' 
                                                       , 'title'       => __('Payments' ,'booking')
                                                       , 'description' => __('Payments Settings' ,'booking')
                                                       , 'icon'        => 'icon-shopping-cart' ) ); 
          if ( ( $version == 'biz_l' ) && ( $is_super_hero ) )
          $wpbc_settings[60] = new WPBC_Settings( array( 'term' => 'search' 
                                                       , 'title'       => __('Search' ,'booking')
                                                       , 'description' => __('Search Settings' ,'booking')
                                                       , 'icon'        => 'icon-search' ) );
          if ( (class_exists('wpdev_bk_multiuser')) && ( $is_super_hero ) )
          $wpbc_settings[70] = new WPBC_Settings( array( 'term' => 'users' 
                                                       , 'title'       => __('Users' ,'booking')
                                                       , 'description' => __('Users Settings' ,'booking')
                                                       , 'icon'        => 'icon-user' ) ); 
          //if ( ( $version != 'biz_l' ) && ( ! wpdev_bk_is_this_demo() ) )
          if ( ( class_exists('wpdev_bk_multiuser') === false ) && ( ! wpdev_bk_is_this_demo() ) )
            $wpbc_settings[80] = new WPBC_Settings( array( 'term' => 'upgrade' 
                                                       , 'title'       => __('Upgrade' ,'booking')
                                                       , 'description' => __('Upgrade' ,'booking')
                                                       , 'icon'        => 'icon-shopping-cart' 
                                                       , 'style'       => 'float:right;' ) ); 
        } else {
          
          $wpdev_copyright_adminpanel  = get_bk_option( 'booking_wpdev_copyright_adminpanel' );           
          if ( ( $wpdev_copyright_adminpanel !== 'Off' ) && ( ! wpdev_bk_is_this_demo() ) ) 
            $wpbc_settings[90] = new WPBC_Settings( array( 'term' => 'buy' 
                                                        , 'title'       => __('Upgrade' ,'booking')
                                                        , 'description' => __('Upgrade' ,'booking')
                                                        , 'icon'        => 'icon-shopping-cart' 
                                                        , 'style'       => 'float:right;' 
                                                        , 'link'        => 'http://wpbookingcalendar.com/') ); 
        }
        
        //HOOK: Definition of other Settings pages
        make_bk_action( 'wpbc_define_top_menu_settings' ); 
        
        ?><div style="height:1px;clear:both;margin-top:20px;"></div>
          <div id="menu-wpdevplugin">
            <div class="nav-tabs-wrapper">
                <div class="nav-tabs wpdevbk" style="width:100%;"><?php
                    ksort($wpbc_settings);        
                    foreach ($wpbc_settings as $settings_menu) {
                        $settings_menu->toolbar_top_tabs_menu();
                    }
        ?> </div> </div> </div> <?php

        $wpdev_copyright_adminpanel  = get_bk_option( 'booking_wpdev_copyright_adminpanel' );           
        if ( ( $version == 'free' ) && ( ( $wpdev_copyright_adminpanel !== 'Off' ) && ( ! wpdev_bk_is_this_demo() ) ) )
            $support_links = '<div id="support_links"> <a href="http://wpbookingcalendar.com/features/" target="_blank">'.__('Features' ,'booking').'</a> | <a href="http://wpbookingcalendar.com/demo/" target="_blank">'.__('Live Demos' ,'booking').'</a> | <a href="http://wpbookingcalendar.com/faq/" target="_blank">'.__('FAQ' ,'booking').'</a> | <a href="mailto:info@wpbookingcalendar.com" target="_blank">'.__('Contact' ,'booking').'</a> | <a href="http://wpbookingcalendar.com/buy/" target="_blank">'.__('Buy' ,'booking').'</a></div>';
        else
            $support_links = '<div id="support_links"> <a class="live-tipsy" original-title="" href="http://wpbookingcalendar.com/faq/" target="_blank">'.__('FAQ' ,'booking').'</a> | <a href="mailto:info@wpbookingcalendar.com" target="_blank">'.__('Contact' ,'booking').'</a> </div>';
        ?><script type="text/javascript"> jQuery('div.bookingpage h2').after('<?php echo $support_links; ?>');</script><div style="height:1px;clear:both;border-top:1px solid #bbc;"></div><?php

        // Submenu in top line  
        make_bk_action('wpdev_booking_settings_top_menu_submenu_line');   

    }

    // Show content of settings page 
    function settings_menu_content() {

        $version = get_bk_version();
        if ( wpdev_bk_is_this_demo() ) 
            $version = 'free';

        if   ( ! isset($_GET['tab']) )  {
             $is_can = apply_bk_filter('multiuser_is_user_can_be_here', true, 'only_super_admin');
             if ($is_can) {
                $_GET['tab'] = 'main';
             } else {                           // Multiuser first page for common user page
                $_GET['tab'] = 'form';
             }
        }
        
        switch ($_GET['tab']) {
            case 'main':                
                wpdev_bk_settings_general();
                break;
            case '':
                wpdev_bk_settings_general();
                break;
            case 'form':
                if (! class_exists('wpdev_bk_personal')) 
                    wpdev_bk_settings_form_labels();
                break;
            case 'email':
                if (! class_exists('wpdev_bk_personal')) 
                    wpbc_settings_emails();
                break;                
            case 'upgrade':
                if ( ($version !== 'free') && ( class_exists('wpdev_bk_multiuser') === false ) ) 
                     wpdev_bk_upgrade_window($version);
                break;
        }
    }

    // </editor-fold>

    

    // <editor-fold defaultstate="collapsed" desc="   C L I E N T   S I D E     &    H O O K S ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   C L I E N T   S I D E     &    H O O K S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Get scripts for calendar activation
    function get_script_for_calendar($bk_type, $additional_bk_types, $my_selected_dates_without_calendar, $my_boook_count, $start_month_calendar = false ){

        $my_boook_type = (int) $bk_type;
        $start_script_code = "<script type='text/javascript'>";
        $start_script_code .= "  jQuery(document).ready( function(){";

        
        $skip_booking_id = '';  // Id of booking to skip in calendar
        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $skip_booking_id = $my_booking_id_type[0];  
            }
        }
        
        
        // Blank days //////////////////////////////////////////////////////////////////
        $start_script_code .= "  date_admin_blank[". $bk_type. "] = [];";
        $dates_and_time_for_admin_blank = $this->get_dates('admin_blank', $bk_type, $additional_bk_types);
        $dates_blank = $dates_and_time_for_admin_blank[0];
        $times_blank = $dates_and_time_for_admin_blank[1];
        $i=-1;
        foreach ($dates_blank as $date_blank) {
            $i++;

            $td_class =   ($date_blank[1]+0). "-" . ($date_blank[2]+0). "-". $date_blank[0];

            $start_script_code .= " if (typeof( date_admin_blank[". $bk_type. "][ '". $td_class . "' ] ) == 'undefined'){ ";
            $start_script_code .= " date_admin_blank[". $bk_type. "][ '". $td_class . "' ] = [];} ";

            $start_script_code .= "  date_admin_blank[". $bk_type. "][ '". $td_class . "' ][  date_admin_blank[".$bk_type."]['".$td_class."'].length  ] = [".
                    ($date_blank[1]+0).", ". ($date_blank[2]+0).", ". ($date_blank[0]+0).", ".
                    ($times_blank[$i][0]+0).", ". ($times_blank[$i][1]+0).", ". ($times_blank[$i][2]+0).
                    "];";
        }
        ////////////////////////////////////////////////////////////////////////////////

        $start_script_code .= "  date2approve[". $bk_type. "] = [];";
        
        $booking_is_days_always_available = get_bk_option( 'booking_is_days_always_available' );
        if ( $booking_is_days_always_available == 'On' ) {
           // No Booked days
            
        } else {

                if ( (class_exists('wpdev_bk_biz_l')) && (get_bk_option( 'booking_is_show_pending_days_as_available') == 'On') ){
                    $dates_to_approve = array();
                    $times_to_approve = array();            
                } else {
                    $dates_and_time_to_approve = $this->get_dates('0', $bk_type, $additional_bk_types, $skip_booking_id);
                    $dates_to_approve = $dates_and_time_to_approve[0];
                    $times_to_approve = $dates_and_time_to_approve[1];
                }
                $i=-1;
                foreach ($dates_to_approve as $date_to_approve) {
                    $i++;

                    $td_class =   ($date_to_approve[1]+0). "-" . ($date_to_approve[2]+0). "-". $date_to_approve[0];

                    $start_script_code .= " if (typeof( date2approve[". $bk_type. "][ '". $td_class . "' ] ) == 'undefined'){ ";
                    $start_script_code .= " date2approve[". $bk_type. "][ '". $td_class . "' ] = [];} ";

                    $start_script_code .= "  date2approve[". $bk_type. "][ '". $td_class . "' ][  date2approve[".$bk_type."]['".$td_class."'].length  ] = [".
                            ($date_to_approve[1]+0).", ". ($date_to_approve[2]+0).", ". ($date_to_approve[0]+0).", ".
                            ($times_to_approve[$i][0]+0).", ". ($times_to_approve[$i][1]+0).", ". ($times_to_approve[$i][2]+0).
                            "];";
                }
        }
        
        $start_script_code .= "  var date_approved_par = [];";
        $start_script_code .= apply_filters('wpdev_booking_availability_filter', '', $bk_type);

        if ( $booking_is_days_always_available == 'On' ) {
            // No Booked days          
            
        } else {
            
            $dates_and_time_to_approve = $this->get_dates('1', $my_boook_type, $additional_bk_types, $skip_booking_id);

            $dates_approved =   $dates_and_time_to_approve[0];
            $times_to_approve = $dates_and_time_to_approve[1];
            $i=-1;       
            foreach ($dates_approved as $date_to_approve) {
                $i++;

                $td_class =   ($date_to_approve[1]+0)."-".($date_to_approve[2]+0)."-".($date_to_approve[0]);

                $start_script_code .= " if (typeof( date_approved_par[ '". $td_class . "' ] ) == 'undefined'){ ";
                $start_script_code .= " date_approved_par[ '". $td_class . "' ] = [];} ";

                $start_script_code.=" date_approved_par[ '".$td_class."' ][  date_approved_par['".$td_class."'].length  ] = [".
                        ($date_to_approve[1]+0).",".($date_to_approve[2]+0).",".($date_to_approve[0]+0).", ".
                        ($times_to_approve[$i][0]+0).", ". ($times_to_approve[$i][1]+0).", ". ($times_to_approve[$i][2]+0).
                        "];";
            }
        }
        
        // TODO: This code section have the impact to the performace in  BM / BL / MU versions ////////////////
        if ($my_selected_dates_without_calendar == '')
            $start_script_code .= apply_filters('wpdev_booking_show_rates_at_calendar', '', $bk_type);
        $start_script_code .= apply_filters('wpdev_booking_show_availability_at_calendar', '', $bk_type);
        ///////////////////////////////////////////////////////////////////////////////////////////////////////
        
        if ($my_selected_dates_without_calendar == '') {
            $start_script_code .= apply_filters('wpdev_booking_get_additional_info_to_dates', '', $bk_type);
            $start_script_code .= "  init_datepick_cal('". $my_boook_type ."', date_approved_par, ".
                                        $my_boook_count ." , ". get_bk_option( 'booking_start_day_weeek' ) ;
            $start_js_month = ", false " ;
            if ($start_month_calendar !== false)
                if (is_array($start_month_calendar))
                    $start_js_month = ", [" . ($start_month_calendar[0]+0) . "," . ($start_month_calendar[1]+0) . "] ";

            $start_script_code .= $start_js_month .  "  );  ";
        }
        $start_script_code .= "}); </script>";

        return $start_script_code;
    }

    // Get code of the legend here
    function get_legend(){
        $my_result = '';
        if (get_bk_option( 'booking_is_show_legend' ) == 'On') {  

            $booking_legend_is_show_item_available    = get_bk_option( 'booking_legend_is_show_item_available');
            $booking_legend_text_for_item_available   = get_bk_option( 'booking_legend_text_for_item_available');

            $booking_legend_is_show_item_pending    = get_bk_option( 'booking_legend_is_show_item_pending');
            $booking_legend_text_for_item_pending   = get_bk_option( 'booking_legend_text_for_item_pending');

            $booking_legend_is_show_item_approved    = get_bk_option( 'booking_legend_is_show_item_approved');
            $booking_legend_text_for_item_approved   = get_bk_option( 'booking_legend_text_for_item_approved');

            $booking_legend_text_for_item_available = apply_bk_filter('wpdev_check_for_active_language',  $booking_legend_text_for_item_available );
            $booking_legend_text_for_item_pending   = apply_bk_filter('wpdev_check_for_active_language',  $booking_legend_text_for_item_pending );
            $booking_legend_text_for_item_approved  =  apply_bk_filter('wpdev_check_for_active_language', $booking_legend_text_for_item_approved );

                        
            $text_for_day_cell = ( (0)?'&nbsp;':date('d') );
            
            $booking_legend_is_show_numbers = get_bk_option( 'booking_legend_is_show_numbers');     //FixIn:6.0.1.4            
            if ( $booking_legend_is_show_numbers == 'Off' )
                $text_for_day_cell = '&nbsp;';
            else
                $text_for_day_cell = date('d');
            
            $my_result .= '<div class="block_hints datepick">';
            if ($booking_legend_is_show_item_available  == 'On') // __('Available' ,'booking')
                $my_result .= '<div class="wpdev_hint_with_text"><div class="block_free datepick-days-cell"><a>'.$text_for_day_cell.'</a></div><div class="block_text">- '. $booking_legend_text_for_item_available.'</div></div>';
            if ($booking_legend_is_show_item_approved  == 'On') // __('Booked' ,'booking') 
                $my_result .= '<div class="wpdev_hint_with_text"><div class="block_booked date_approved">'.$text_for_day_cell.'</div><div class="block_text">- '.$booking_legend_text_for_item_approved.'</div></div>';
            if ($booking_legend_is_show_item_pending  == 'On') // __('Pending' ,'booking') 
                $my_result .= '<div class="wpdev_hint_with_text"><div class="block_pending date2approve">'.$text_for_day_cell.'</div><div class="block_text">- '.$booking_legend_text_for_item_pending.'</div></div>';

            if ( class_exists('wpdev_bk_biz_s') ) {

                $booking_legend_is_show_item_partially    = get_bk_option( 'booking_legend_is_show_item_partially');
                $booking_legend_text_for_item_partially   = get_bk_option( 'booking_legend_text_for_item_partially');
                $booking_legend_text_for_item_partially  =  apply_bk_filter('wpdev_check_for_active_language', $booking_legend_text_for_item_partially );
                
                if ($booking_legend_is_show_item_partially  == 'On') { // __('Partially booked' ,'booking')                    
                    if ( get_bk_option( 'booking_range_selection_time_is_active' ) === 'On') {                        
                        $my_result .=  '<div class="wpdev_hint_with_text">' . 
                                                '<div class="block_check_in_out date_available date_approved check_in_time"  >
                                                    <div class="check-in-div"><div></div></div>
                                                    <div class="check-out-div"><div></div></div>
                                                    '.$text_for_day_cell.'
                                                </div>'.
                                                '<div class="block_text">- '. $booking_legend_text_for_item_partially .'</div>'.
                                        '</div>';                        
                    } else {
                        $my_result .= '<div class="wpdev_hint_with_text"><div class="block_time timespartly">'.$text_for_day_cell.'</div><div class="block_text">- '. $booking_legend_text_for_item_partially .'</div></div>';
                    }                        
                }
                
            }
            $my_result .= '</div><div class="wpdev_clear_hint"></div>';
        }
        return $my_result;
    }

    
    // Get HTML for the initilizing inline calendars
    function pre_get_calendar_html( $bk_type=1, $cal_count=1, $bk_otions=array() ){
        //SHORTCODE:
        /*
         * [booking type=56 form_type='standard' nummonths=4 
         *          options='{calendar months_num_in_row=2 width=568px cell_height=30px}']
         */
        
        $bk_otions = parse_calendar_options($bk_otions);
        /*  options:
            [months_num_in_row] => 2
            [width] => 284px
            [cell_height] => 40px
         */
        $width = $months_num_in_row = $cell_height = '';
        
        if (!empty($bk_otions)){
            
             if (isset($bk_otions['months_num_in_row'])) 
                 $months_num_in_row = $bk_otions['months_num_in_row'];
             
             if (isset($bk_otions['width'])) 
                 $width = 'width:'.$bk_otions['width'].';';
             
             if (isset($bk_otions['cell_height'])) 
                 $cell_height = $bk_otions['cell_height'];             
        }
        
        if (empty($width)){
            if (!empty($months_num_in_row))
                $width = 'width:'.($months_num_in_row*284).'px;';
            else
                $width = 'width:'.($cal_count*284).'px;';
        }
        
        if (!empty($cell_height))
             $style= '<style type="text/css" rel="stylesheet" >'.
                        '.hasDatepick .datepick-inline .datepick-title-row th,'.
                        '.hasDatepick .datepick-inline .datepick-days-cell{'.
                            ' height: '.$cell_height.' !important; '.
                        '}'.
                     '</style>';
        else $style= '';
        
        $calendar  = $style. 
                     '<div class="bk_calendar_frame months_num_in_row_'.$months_num_in_row.' cal_month_num_'.$cal_count.'" style="'.$width.'">'.
                        '<div id="calendar_booking'.$bk_type.'">'.
                            __('Calendar is loading...' ,'booking').
                        '</div>'.
                     '</div>'.
                     '';
        
        $booking_is_show_powered_by_notice = get_bk_option( 'booking_is_show_powered_by_notice' );          
        if ( (!class_exists('wpdev_bk_personal')) && ($booking_is_show_powered_by_notice == 'On') )
            $calendar .= '<div style="font-size:9px;text-align:left;margin-top:3px;">Powered by <a style="font-size:9px;" href="http://wpbookingcalendar.com" target="_blank">WP Booking Calendar</a></div>';
                
        $calendar .= '<textarea id="date_booking'.$bk_type.'" name="date_booking'.$bk_type.'" autocomplete="off" style="display:none;"></textarea>';   // Calendar code
        
        $calendar  .= $this->get_legend(); 
        
        return $calendar;
    }
    
    
    // Get form
    function get_booking_form($my_boook_type) {
        
        $booking_form_field_active1     = get_bk_option( 'booking_form_field_active1');
        $booking_form_field_required1   = get_bk_option( 'booking_form_field_required1');
        $booking_form_field_label1      = get_bk_option( 'booking_form_field_label1');
        $booking_form_field_label1 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label1 );
        if (function_exists('icl_translate')) 
            $booking_form_field_label1 = icl_translate( 'wpml_custom', 'wpbc_custom_form_field_label1', $booking_form_field_label1);

        $booking_form_field_active2     = get_bk_option( 'booking_form_field_active2');
        $booking_form_field_required2   = get_bk_option( 'booking_form_field_required2');
        $booking_form_field_label2      = get_bk_option( 'booking_form_field_label2');
        $booking_form_field_label2 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label2 );
        if (function_exists('icl_translate')) 
            $booking_form_field_label2 = icl_translate( 'wpml_custom', 'wpbc_custom_form_field_label2', $booking_form_field_label2);
        
        $booking_form_field_active3     = get_bk_option( 'booking_form_field_active3');
        $booking_form_field_required3   = get_bk_option( 'booking_form_field_required3');
        $booking_form_field_label3      = get_bk_option( 'booking_form_field_label3');
        $booking_form_field_label3 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label3 );
        if (function_exists('icl_translate')) 
            $booking_form_field_label3 = icl_translate( 'wpml_custom', 'wpbc_custom_form_field_label3', $booking_form_field_label3);
        
        $booking_form_field_active4     = get_bk_option( 'booking_form_field_active4');
        $booking_form_field_required4   = get_bk_option( 'booking_form_field_required4');
        $booking_form_field_label4      = get_bk_option( 'booking_form_field_label4');
        $booking_form_field_label4 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label4 );
        if (function_exists('icl_translate')) 
            $booking_form_field_label4 = icl_translate( 'wpml_custom', 'wpbc_custom_form_field_label4', $booking_form_field_label4);
        
        $booking_form_field_active5     = get_bk_option( 'booking_form_field_active5');
        $booking_form_field_required5   = get_bk_option( 'booking_form_field_required5');
        $booking_form_field_label5      = get_bk_option( 'booking_form_field_label5');
        $booking_form_field_label5 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label5 );
        if (function_exists('icl_translate')) 
            $booking_form_field_label5 = icl_translate( 'wpml_custom', 'wpbc_custom_form_field_label5', $booking_form_field_label5);
        
        $booking_form_field_active6     = get_bk_option( 'booking_form_field_active6');
        $booking_form_field_required6   = get_bk_option( 'booking_form_field_required6');
        $booking_form_field_label6      = get_bk_option( 'booking_form_field_label6');
        $booking_form_field_label6 = apply_bk_filter('wpdev_check_for_active_language', $booking_form_field_label6 );
        if (function_exists('icl_translate')) 
            $booking_form_field_label6 = icl_translate( 'wpml_custom', 'wpbc_custom_form_field_label6', $booking_form_field_label6);
        $booking_form_field_values6     = get_bk_option( 'booking_form_field_values6' );
        
        $my_form =  '[calendar]';
                //'<div style="text-align:left;">'.
                //'<p>'.__('First Name (required)' ,'booking').':<br />  <span class="wpdev-form-control-wrap name'.$my_boook_type.'"><input type="text" name="name'.$my_boook_type.'" value="" class="wpdev-validates-as-required" size="40" /></span> </p>'.
                    
        if ($booking_form_field_active1  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="name'.$my_boook_type.'" class="control-label">'.$booking_form_field_label1.(($booking_form_field_required1=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="name'.$my_boook_type.'" id="name'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required1=='On')?' wpdev-validates-as-required ':'').'">
                      </div>
                    </div>';
        
        if ($booking_form_field_active2  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="secondname'.$my_boook_type.'" class="control-label">'.$booking_form_field_label2.(($booking_form_field_required2=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="secondname'.$my_boook_type.'" id="secondname'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required2=='On')?' wpdev-validates-as-required ':'').'">
                      </div>
                    </div>';                    
                  
        if ($booking_form_field_active3  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="email'.$my_boook_type.'" class="control-label">'.$booking_form_field_label3.(($booking_form_field_required3=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="email'.$my_boook_type.'" id="email'.$my_boook_type.'" class="input-xlarge wpdev-validates-as-email'.(($booking_form_field_required3=='On')?' wpdev-validates-as-required ':'').'">
                      </div>
                    </div>';

        if ($booking_form_field_active6  == 'On') {
            $my_form.='  <div class="control-group">
                          <label for="visitors'.$my_boook_type.'" class="control-label">'.$booking_form_field_label6.(($booking_form_field_required6=='On')?'*':'').':</label>
                          <div class="controls">
                            <select name="visitors'.$my_boook_type.'" id="visitors'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required6=='On')?' wpdev-validates-as-required ':'').'">';
            
            //$booking_form_field_values6 = explode("\n",$booking_form_field_values6);
            $booking_form_field_values6 = preg_split('/\r\n|\r|\n/', $booking_form_field_values6);
            foreach ($booking_form_field_values6 as $select_option) {
                $select_option = str_replace(array("'",'"'), '', $select_option);
                $my_form.='  <option value="'.$select_option.'">'.$select_option.'</option>';    
            }
            
            $my_form.='     </select>                            
                            <p class="help-block"></p>
                          </div>
                        </div>';                    
        }
        
        if ($booking_form_field_active4  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="phone'.$my_boook_type.'" class="control-label">'.$booking_form_field_label4.(($booking_form_field_required4=='On')?'*':'').':</label>
                      <div class="controls">
                        <input type="text" name="phone'.$my_boook_type.'" id="phone'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required4=='On')?' wpdev-validates-as-required ':'').'">
                        <p class="help-block"></p>
                      </div>
                    </div>';                    
        
        if ($booking_form_field_active5  != 'Off')
        $my_form.='  <div class="control-group">
                      <label for="details" class="control-label">'.$booking_form_field_label5.(($booking_form_field_required5=='On')?'*':'').':</label>
                      <div class="controls">
                        <textarea rows="3" name="details'.$my_boook_type.'" id="details'.$my_boook_type.'" class="input-xlarge'.(($booking_form_field_required5=='On')?' wpdev-validates-as-required ':'').'"></textarea>
                      </div>
                    </div>';
        
        $my_form.='  <div class="control-group">[captcha]</div>';
                    
        $my_form.='  <button class="btn btn-primary" type="button" onclick="mybooking_submit(this.form,'.$my_boook_type.',\''.getBookingLocale().'\');" >'.__('Send' ,'booking').'</button> ';
                  
                //.'<p>'.__('Last Name (required)' ,'booking').':<br />  <span class="wpdev-form-control-wrap secondname'.$my_boook_type.'"><input type="text" name="secondname'.$my_boook_type.'" value="" class="wpdev-validates-as-required" size="40" /></span> </p>'.
                //'<p>'.__('Email (required)' ,'booking').':<br /> <span class="wpdev-form-control-wrap email'.$my_boook_type.'"><input type="text" name="email'.$my_boook_type.'" value="" class="wpdev-validates-as-email wpdev-validates-as-required" size="40" /></span> </p>'.
                //'<p>'.__('Phone' ,'booking').':<br />            <span class="wpdev-form-control-wrap phone'.$my_boook_type.'"><input type="text" name="phone'.$my_boook_type.'" value="" size="40" /></span> </p>'.
                //'<p>'.__('Details' ,'booking').':<br />          <span class="wpdev-form-control-wrap details'.$my_boook_type.'"><textarea name="details'.$my_boook_type.'" cols="40" rows="10"></textarea></span> </p>';
                
                //$my_form .=  '<p>[captcha]</p>';
                //$my_form .=  '<p><input type="button" value="'.__('Send' ,'booking').'" onclick="mybooking_submit(this.form,'.$my_boook_type.',\''.getBookingLocale().'\');" /></p>
                //        </div>';

        return $my_form;
    }

    // Get booking form
    function get_booking_form_action($my_boook_type=1,$my_boook_count=1, $my_booking_form = 'standard',  $my_selected_dates_without_calendar = '', $start_month_calendar = false, $bk_otions=array()) {

        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0, $my_booking_form , $my_selected_dates_without_calendar, $start_month_calendar, $bk_otions );
        return $res;
    }

    //Show booking form from action call - wpdev_bk_add_form
    function add_booking_form_action($bk_type =1, $cal_count =1, $is_echo = 1, $my_booking_form = 'standard', $my_selected_dates_without_calendar = '', $start_month_calendar = false, $bk_otions=array() ) {
        
        $additional_bk_types = array();
        if ( strpos($bk_type,';') !== false ) {
            $additional_bk_types = explode(';',$bk_type);
            $bk_type = $additional_bk_types[0];
        }

        $is_booking_resource_exist = apply_bk_filter('wpdev_is_booking_resource_exist',true, $bk_type, $is_echo );
        if (! $is_booking_resource_exist) {
            if ( $is_echo )     echo '';
            return '';
        }

        make_bk_action('check_multiuser_params_for_client_side', $bk_type );


        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type != false)
                if ($my_booking_id_type[1]=='') {
                    $my_result = __('Wrong booking hash in URL (probably expired)' ,'booking');
                    if ( $is_echo )            echo $my_result;
                    else                       return $my_result;
                    return;
                }
        }

        if ($bk_type == '') {
            $my_result = __('Booking resource type is not defined. Its can be, when at the URL is wrong booking hash.' ,'booking');
            if ( $is_echo )            echo $my_result;
            else                       return $my_result;
            return;
        }

        
        
              
        $start_script_code = $this->get_script_for_calendar($bk_type, $additional_bk_types, $my_selected_dates_without_calendar, $cal_count, $start_month_calendar );
        
        // Apply scripts for the conditions in the rnage days selections
        $start_script_code = apply_bk_filter('wpdev_bk_define_additional_js_options_for_bk_shortcode', $start_script_code, $bk_type, $bk_otions);  
        
        $my_result =  ' ' . $this->get__client_side_booking_content($bk_type, $my_booking_form, $my_selected_dates_without_calendar, $cal_count, $bk_otions ) . ' ' . $start_script_code ;

        $my_result = apply_filters('wpdev_booking_form', $my_result , $bk_type);

        make_bk_action('finish_check_multiuser_params_for_client_side', $bk_type );

        if ( $is_echo )            echo $my_result;
        else                       return $my_result;
    }

    //Show only calendar from action call - wpdev_bk_add_calendar
    function add_calendar_action($bk_type =1, $cal_count =1, $is_echo = 1, $start_month_calendar = false, $bk_otions=array()) {

        $additional_bk_types = array();
        if ( strpos($bk_type,';') !== false ) {
            $additional_bk_types = explode(';',$bk_type);
            $bk_type = $additional_bk_types[0];
        }

        make_bk_action('check_multiuser_params_for_client_side', $bk_type );

        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type != false)
                if ($my_booking_id_type[1]=='') {
                    $my_result = __('Wrong booking hash in URL (probably expired)' ,'booking');
                    if ( $is_echo )            echo $my_result;
                    else                       return $my_result;
                    return;
                }
        }

        $start_script_code = $this->get_script_for_calendar($bk_type, $additional_bk_types, '' , $cal_count, $start_month_calendar );

        $my_result = '<div style="clear:both;height:10px;"></div>' . $this->pre_get_calendar_html( $bk_type, $cal_count, $bk_otions );

        // $my_result .= $this->get_legend();                                  // Get Legend code here

        $my_result .=   ' ' . $start_script_code ;

        $my_result = apply_filters('wpdev_booking_calendar', $my_result , $bk_type);

        make_bk_action('finish_check_multiuser_params_for_client_side', $bk_type );

        if ( $is_echo )            echo $my_result;
        else                       return $my_result;
    }

    // Get content at client side of  C A L E N D A R
    function get__client_side_booking_content($my_boook_type = 1 , $my_booking_form = 'standard', $my_selected_dates_without_calendar = '', $cal_count = 1, $bk_otions = array() ) {

        $nl = '<div style="clear:both;height:10px;"></div>';                                                            // New line
        if ($my_selected_dates_without_calendar=='') {
            $calendar = $this->pre_get_calendar_html( $my_boook_type, $cal_count, $bk_otions );
        } else {
            $calendar = '<textarea rows="3" cols="50" id="date_booking'.$my_boook_type.'" name="date_booking'.$my_boook_type.'"  autocomplete="off" style="display:none;">'.$my_selected_dates_without_calendar.'</textarea>';   // Calendar code
        }
        // $calendar  .= $this->get_legend();                                  // Get Legend code here


        $form = '<a name="bklnk'.$my_boook_type.'"></a><div id="booking_form_div'.$my_boook_type.'" class="booking_form_div">';
        
        // FixIn:6.0.1.5
        $custom_params = array();
        if (! empty($bk_otions)) {
            $param ='\s*([name|value]+)=[\'"]{1}([^\'"]+)[\'"]{1}\s*'; // Find all possible options
            $pattern_to_search='%\s*{([^\s]+)' . $param . $param .'}\s*[,]?\s*%';
            preg_match_all($pattern_to_search, $bk_otions, $matches, PREG_SET_ORDER);
            //debuge($matches);  
            foreach ( $matches as $matche_value ) {
                if ( $matche_value[1] == 'parameter' ) {
                    $custom_params[ $matche_value[3] ]= $matche_value[5];
                }
            }
        }
        // FixIn:6.0.1.5

        if(  $this->wpdev_bk_personal !== false  )   $form .= $this->wpdev_bk_personal->get_booking_form($my_boook_type, $my_booking_form, $custom_params);  // FixIn:6.0.1.5       // Get booking form
        else                                    $form .= $this->get_booking_form($my_boook_type);

        // Insert calendar into form
        if ( strpos($form, '[calendar]') !== false )  $form = str_replace('[calendar]', $calendar ,$form);
        else                                          $form = '<div class="booking_form_div">' . $calendar . '</div>' . $nl . $form ;

        $form = apply_bk_filter('wpdev_check_for_additional_calendars_in_form', $form, $my_boook_type , array( 
                                                                                    'booking_form' => $my_booking_form , 
                                                                                    'selected_dates' => $my_selected_dates_without_calendar , 
                                                                                    'cal_count'=>$cal_count , 
                                                                                    'otions'=>$bk_otions     )  
                               );

        if ( strpos($form, '[captcha]') !== false ) {
            $captcha = $this->createCapthaContent($my_boook_type);
            $form =str_replace('[captcha]', $captcha ,$form);
        }

        $form = apply_filters('wpdev_booking_form_content', $form , $my_boook_type);
        // Add booking type field
        $form      .= '<input id="bk_type'.$my_boook_type.'" name="bk_type'.$my_boook_type.'" class="" type="hidden" value="'.$my_boook_type.'" /></div>';        
        $submitting = '<div id="submiting'.$my_boook_type.'"></div><div class="form_bk_messages" id="form_bk_messages'.$my_boook_type.'" ></div>';
        
        //Params: $action = -1, $name = "_wpnonce", $referer = true , $echo = true
        $wpbc_nonce  = wp_nonce_field('INSERT_INTO_TABLE',  ("wpbc_nonce" . $my_boook_type) ,  true , false );
        $wpbc_nonce .= wp_nonce_field('CALCULATE_THE_COST', ("wpbc_nonceCALCULATE_THE_COST" . $my_boook_type) ,  true , false );
        
        $res = $form . $submitting . $wpbc_nonce;

        $my_random_id = time() * rand(0,1000);
        $my_random_id = 'form_id'. $my_random_id;        
        
        $booking_form_is_using_bs_css = get_bk_option( 'booking_form_is_using_bs_css');
        $booking_form_format_type     = get_bk_option( 'booking_form_format_type');
        
        $return_form = '<div id="'.$my_random_id.'" '.(($booking_form_is_using_bs_css=='On')?'class="wpdevbk"':'').'>'.
                         '<form  id="booking_form'.$my_boook_type.'"   class="booking_form '.$booking_form_format_type.'" method="post" action="">'.
                           '<div id="ajax_respond_insert'.$my_boook_type.'" class="ajax_respond_insert" style="display:none;"></div>'.
                           $res.
                         '</form></div>';
        
        $return_form .= '<div id="booking_form_garbage'.$my_boook_type.'" class="booking_form_garbage"></div>';
        
        if ($my_selected_dates_without_calendar == '' ) {
            // Check according already shown Booking Calendar  and set do not visible of it
            $return_form .= '<script type="text/javascript">
                                jQuery(document).ready( function(){
                                    jQuery(".widget_wpdev_booking .booking_form.form-horizontal").removeClass("form-horizontal");
                                    var visible_booking_id_on_page_num = visible_booking_id_on_page.length;
                                    if (visible_booking_id_on_page_num !== null ) {
                                        for (var i=0;i< visible_booking_id_on_page_num ;i++){
                                          if ( visible_booking_id_on_page[i]=="booking_form_div'.$my_boook_type.'" ) {
                                              document.getElementById("'.$my_random_id.'").innerHTML = "<span style=\'color:#A00;font-size:10px;\'>'.                                                      
                                                       sprintf( esc_js( __('%sWarning! Booking calendar for this booking resource are already at the page, please check more about this issue at %sthis page%s' ,'booking') )
                                                                , ''
                                                                , ''
                                                                , ': http://wpbookingcalendar.com/faq/why-the-booking-calendar-widget-not-show-on-page/'                                                            
                                                        ) 
                                                .'</span>";
                                              jQuery("#'.$my_random_id.'").animate( {opacity: 1}, 10000 ).fadeOut(5000);
                                              return;
                                          }
                                        }
                                        visible_booking_id_on_page[ visible_booking_id_on_page_num ]="booking_form_div'.$my_boook_type.'";
                                    }
                                });
                            </script>';
        } else {
            $return_form .= '<script type="text/javascript">
                                jQuery(document).ready( function(){            
                                    if(typeof( showCostHintInsideBkForm ) == "function") {
                                        showCostHintInsideBkForm('.$my_boook_type.');
                                    }
                                });
                            </script>';
        }

        $is_use_auto_fill_for_logged = get_bk_option( 'booking_is_use_autofill_4_logged_user' ) ;


        if (! isset($_GET['booking_hash']))
            if ($is_use_auto_fill_for_logged == 'On') {

                $curr_user = wp_get_current_user();
                if ( $curr_user->ID > 0 ) {

                    $return_form .= '<script type="text/javascript">
                                jQuery(document).ready( function(){
                                    var bk_af_submit_form = document.getElementById( "booking_form'.$my_boook_type.'" );
                                    var bk_af_count = bk_af_submit_form.elements.length;
                                    var bk_af_element;
                                    var bk_af_reg;
                                    for (var bk_af_i=0; bk_af_i<bk_af_count; bk_af_i++)   {
                                        bk_af_element = bk_af_submit_form.elements[bk_af_i];
                                        if (
                                            (bk_af_element.type == "text") &&
                                            (bk_af_element.type !=="button") &&
                                            (bk_af_element.type !=="hidden") &&
                                            (bk_af_element.name !== ("date_booking'.$my_boook_type.'" ) )
                                           ) {
                                                // Second Name
                                                bk_af_reg = /^([A-Za-z0-9_\-\.])*(last|second){1}([_\-\.])?name([A-Za-z0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->last_name).'";
                                                // First Name
                                                bk_af_reg = /^name([0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->first_name).'";
                                                bk_af_reg = /^([A-Za-z0-9_\-\.])*(first|my){1}([_\-\.])?name([A-Za-z0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->first_name).'";
                                                // Email
                                                bk_af_reg = /^(e)?([_\-\.])?mail([0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->user_email).'";
                                                // URL
                                                bk_af_reg = /^([A-Za-z0-9_\-\.])*(URL|site|web|WEB){1}([A-Za-z0-9_\-\.])*$/;
                                                if(bk_af_reg.test(bk_af_element.name) != false)
                                                    if (bk_af_element.value == "" )
                                                        bk_af_element.value  = "'.str_replace("'",'',$curr_user->user_url).'";
                                           }
                                    }
                                });
                                </script>';
                }
             }

        return $return_form ;
    }
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="   S H O R T    C O D E S ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //   S H O R T    C O D E S
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    // Replace MARK at post with content at client side   -----    [booking nummonths='1' type='1']
    function booking_shortcode($attr) {
//debuge($attr);

        if (isset($_GET['booking_hash'])) return __('You need to use special shortcode [bookingedit] for booking editing.' ,'booking');

        $my_boook_count = get_bk_option( 'booking_client_cal_count' );
        $my_boook_type = 1;
        $my_booking_form = 'standard';
        $start_month_calendar = false;
        $bk_otions = array();

        if ( isset( $attr['nummonths'] ) ) { $my_boook_count = $attr['nummonths'];  }
        if ( isset( $attr['type'] ) )      { $my_boook_type = $attr['type'];        }
        if ( isset( $attr['form_type'] ) ) { $my_booking_form = $attr['form_type']; }

        if ( isset( $attr['agregate'] )  && (! empty( $attr['agregate'] )) ) {
            $additional_bk_types = $attr['agregate'];
            $my_boook_type .= ';'.$additional_bk_types;
        }
        if ( isset( $attr['aggregate'] )  && (! empty( $attr['aggregate'] )) ) {
            $additional_bk_types = $attr['aggregate'];
            $my_boook_type .= ';'.$additional_bk_types;
        }


        if ( isset( $attr['startmonth'] ) ) { // Set start month of calendar, fomrat: '2011-1'

            $start_month_calendar = explode( '-', $attr['startmonth'] );
            if ( (is_array($start_month_calendar))  && ( count($start_month_calendar) > 1) ) { }
            else $start_month_calendar = false;

        }

        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }
        
        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0 , $my_booking_form , '', $start_month_calendar, $bk_otions );

        return $res;
    }

    // Replace MARK at post with content at client side   -----    [booking nummonths='1' type='1']
    function booking_calendar_only_shortcode($attr) {
        $my_boook_count = get_bk_option( 'booking_client_cal_count' );
        $my_boook_type = 1;
        $start_month_calendar = false;
        $bk_otions = array();
        if ( isset( $attr['nummonths'] ) ) { $my_boook_count = $attr['nummonths']; }
        if ( isset( $attr['type'] ) )      { $my_boook_type = $attr['type'];       }
        if ( isset( $attr['agregate'] )  && (! empty( $attr['agregate'] )) ) {
            $additional_bk_types = $attr['agregate'];
            $my_boook_type .= ';'.$additional_bk_types;
        }

        if ( isset( $attr['startmonth'] ) ) { // Set start month of calendar, fomrat: '2011-1'
            $start_month_calendar = explode( '-', $attr['startmonth'] );
            if ( (is_array($start_month_calendar))  && ( count($start_month_calendar) > 1) ) { }
            else $start_month_calendar = false;
        }
        
        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }
        $res = $this->add_calendar_action($my_boook_type,$my_boook_count, 0, $start_month_calendar, $bk_otions  );


        $start_script_code = "<div id='calendar_booking_unselectable".$my_boook_type."'></div>";
        return $start_script_code. $res ;
    }

    // Show only booking form, with already selected dates
    function bookingform_shortcode($attr) {

        $my_boook_type = 1;
        $my_booking_form = 'standard';
        $my_boook_count = 1;
        $my_selected_dates_without_calendar = '';

        if ( isset( $attr['type'] ) )           { $my_boook_type = $attr['type'];                                }
        if ( isset( $attr['form_type'] ) )      { $my_booking_form = $attr['form_type'];                         }
        if ( isset( $attr['selected_dates'] ) ) { $my_selected_dates_without_calendar = $attr['selected_dates']; }  //$my_selected_dates_without_calendar = '20.08.2010, 29.08.2010';

        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0 , $my_booking_form, $my_selected_dates_without_calendar, false );
        return $res;
    }

    // Show booking form for editing
    function bookingedit_shortcode($attr) {
        $my_boook_count = get_bk_option( 'booking_client_cal_count' );
        $my_boook_type = 1;
        $my_booking_form = 'standard';
        $bk_otions = array();
        if ( isset( $attr['nummonths'] ) )   { $my_boook_count = $attr['nummonths'];  }
        if ( isset( $attr['type'] ) )        { $my_boook_type = $attr['type'];        }
        if ( isset( $attr['form_type'] ) )   { $my_booking_form = $attr['form_type']; }

        if (isset($_GET['booking_hash'])) {
            $my_booking_id_type = apply_bk_filter('wpdev_booking_get_hash_to_id',false, $_GET['booking_hash'] );
            if ($my_booking_id_type !== false) {
                $my_edited_bk_id = $my_booking_id_type[0];
                $my_boook_type        = $my_booking_id_type[1];
                if ($my_boook_type == '') return __('Wrong booking hash in URL. Probably hash is expired.' ,'booking');
            } else {
                return __('Wrong booking hash in URL. Probably hash is expired.' ,'booking');
            }

        } else {
            return __('You do not set any parameters for booking editing' ,'booking');
        }
        if ( isset( $attr['options'] ) ) { $bk_otions = $attr['options']; }

        $res = $this->add_booking_form_action($my_boook_type,$my_boook_count, 0 , $my_booking_form, '', false, $bk_otions );

        if (isset($_GET['booking_pay'])) {
            // Payment form
            $res .= apply_bk_filter('wpdev_get_payment_form',$my_edited_bk_id, $my_boook_type );

        }

        return $res;
    }

    // Search form
    function bookingsearch_shortcode($attr) {

        $search_form = apply_bk_filter('wpdev_get_booking_search_form','', $attr );

        return $search_form ;
    }

    // Search Results form
    function bookingsearchresults_shortcode($attr) {

        $search_results = apply_bk_filter('wpdev_get_booking_search_results','', $attr );

        return $search_results ;
    }

    // Select Booking form using the selectbox
    function bookingselect_shortcode($attr) {

        $search_form = apply_bk_filter('wpdev_get_booking_select_form','', $attr );

        return $search_form ;
    }

    // Select Booking form using the selectbox
    function bookingresource_shortcode($attr) {

        $search_form = apply_bk_filter('wpbc_booking_resource_info','', $attr );

        return $search_form ;
    }
    

    // TimeLine shortcode
    // TODO: Finish  here with  ability to show the timeline at the front-end.
    function bookingtimeline_shortcode($attr) {
        
        ob_start();
        
        bookings_overview_in_calendar();
        wpdevbk_booking_listing_write_js();                                     // Wtite inline  JS
        wpdevbk_booking_listing_write_css();                                    // Write inline  CSS        
        
        $timeline_results = ob_get_contents();

        ob_end_clean();
        
        return $timeline_results ;
    }
    
    
    // </editor-fold>



    // <editor-fold defaultstate="collapsed" desc="  A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///   A C T I V A T I O N   A N D   D E A C T I V A T I O N    O F   T H I S   P L U G I N  ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    // Activation  of the plugin, when the use is clicked on the "Active" link at  the Plugins WordPress menu.
    function wpdev_booking_activate_initial(){
        
        // Activate the plugin
        $this->wpdev_booking_activate();
        
        // Bail if this demo or activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) || wpdev_bk_is_this_demo() )
		return;
        
        // Add the transient to redirect - Showing Welcome screen
	set_transient( '_wpbc_activation_redirect', true, 30 );        
    }
    
    // Activate
    function wpdev_booking_activate() {
        update_bk_option( 'booking_activation_process','On');

        //update_bk_option( 'booking_version_num',WP_BK_VERSION_NUM);             //FixIn:5.4.2
        
        $version = get_bk_version();
        $is_demo = wpdev_bk_is_this_demo();
        
        load_bk_Translation();
        // set execution time to 15 minutes, its not worked if we have SAFE MODE ON at PHP
        if (function_exists('set_time_limit')) 		if( !in_array(ini_get('safe_mode'),array('1', 'On')) ) set_time_limit(900);

        add_bk_option( 'booking_admin_cal_count' , ($is_demo)?'3':'2');
        
        add_bk_option( 'booking_skin',  '/css/skins/traditional.css');

        add_bk_option( 'bookings_num_per_page','10');
        add_bk_option( 'booking_sort_order','');
        add_bk_option( 'booking_default_toolbar_tab','filter');
        add_bk_option( 'bookings_listing_default_view_mode','vm_calendar');//,'vm_listing');
        
        
        if ($version=='free')   add_bk_option( 'booking_view_days_num','90');   // 3 Month - for one resource
        else                    add_bk_option( 'booking_view_days_num','30');   // Month view for several resources
        //add_bk_option( 'booking_sort_order_direction', 'ASC');

        add_bk_option( 'booking_max_monthes_in_calendar', '1y');
        add_bk_option( 'booking_client_cal_count', '1' );
        add_bk_option( 'booking_start_day_weeek' ,'0');
        add_bk_option( 'booking_title_after_reservation' , sprintf(__('Thank you for your online booking. %s We will send confirmation of your booking as soon as possible.' ,'booking'), '') );
        add_bk_option( 'booking_title_after_reservation_time' , '7000' );
        add_bk_option( 'booking_type_of_thank_you_message' , 'message' );
        add_bk_option( 'booking_thank_you_page_URL' ,  '/thank-you' );
        add_bk_option( 'booking_is_use_autofill_4_logged_user' , ($is_demo)?'On':'Off' );


        add_bk_option( 'booking_date_format' , get_option('date_format') );
        add_bk_option( 'booking_date_view_type', 'short');    // short / wide
        add_bk_option( 'booking_is_delete_if_deactive' , ($is_demo)?'On':'Off' ); // check
        
        add_bk_option( 'booking_dif_colors_approval_pending' , 'On' );
        add_bk_option( 'booking_is_use_hints_at_admin_panel' , 'On' );
        add_bk_option( 'booking_is_not_load_bs_script_in_client' , 'Off' );
        add_bk_option( 'booking_is_not_load_bs_script_in_admin' , 'Off' );
        add_bk_option( 'booking_is_load_js_css_on_specific_pages' , 'Off' );
        add_bk_option( 'booking_pages_for_load_js_css' , '' );
        
        // Set the type of days selections based on the previous saved data ....
        $booking_type_of_day_selections = 'multiple'; //'single';
        
        if ( get_bk_option( 'booking_multiple_day_selections' ) == 'On')    $booking_type_of_day_selections = 'multiple';                     
        if ( get_bk_option( 'booking_range_selection_is_active') == 'On' )  $booking_type_of_day_selections = 'range';
        if ( $is_demo )                                                     $booking_type_of_day_selections = 'multiple';
        
        add_bk_option( 'booking_type_of_day_selections' , $booking_type_of_day_selections );

        add_bk_option( 'booking_form_is_using_bs_css' ,'On');
        add_bk_option( 'booking_form_format_type' ,'vertical');

        add_bk_option( 'booking_form_field_active1' ,'On');
        add_bk_option( 'booking_form_field_required1' ,'On');
        add_bk_option( 'booking_form_field_label1' ,'First Name');
        add_bk_option( 'booking_form_field_active2' ,'On');
        add_bk_option( 'booking_form_field_required2' ,'On');
        add_bk_option( 'booking_form_field_label2' ,'Last Name');
        add_bk_option( 'booking_form_field_active3' ,'On');
        add_bk_option( 'booking_form_field_required3' ,'On');
        add_bk_option( 'booking_form_field_label3' ,'Email');
        add_bk_option( 'booking_form_field_active4' ,'On');
        add_bk_option( 'booking_form_field_required4' ,'Off');
        add_bk_option( 'booking_form_field_label4' ,'Phone');
        add_bk_option( 'booking_form_field_active5' ,'On');
        add_bk_option( 'booking_form_field_required5' ,'Off');
        add_bk_option( 'booking_form_field_label5' ,'Details');

        add_bk_option( 'booking_form_field_active6' ,'Off');
        add_bk_option( 'booking_form_field_required6' ,'Off');
        add_bk_option( 'booking_form_field_label6' ,'Visitors');
        add_bk_option( 'booking_form_field_values6', "1\n2\n3\n4" );
        
        add_bk_option( 'booking_is_days_always_available' ,'Off');
        add_bk_option( 'booking_check_on_server_if_dates_free' ,'Off');
        
        add_bk_option( 'booking_unavailable_days_num_from_today' , '0' );
        add_bk_option( 'booking_unavailable_day0' ,'Off');
        add_bk_option( 'booking_unavailable_day1' ,'Off');
        add_bk_option( 'booking_unavailable_day2' ,'Off');
        add_bk_option( 'booking_unavailable_day3' ,'Off');
        add_bk_option( 'booking_unavailable_day4' ,'Off');
        add_bk_option( 'booking_unavailable_day5' ,'Off');
        add_bk_option( 'booking_unavailable_day6' ,'Off');

        if ( $is_demo ) {
            add_bk_option( 'booking_user_role_booking', 'subscriber' );
            add_bk_option( 'booking_user_role_addbooking', 'subscriber' );
            add_bk_option( 'booking_user_role_resources', 'subscriber' );
            add_bk_option( 'booking_user_role_settings', 'subscriber' );
        } else {
            add_bk_option( 'booking_user_role_booking', 'editor' );
            add_bk_option( 'booking_user_role_addbooking', 'editor' );
            add_bk_option( 'booking_user_role_resources', 'editor' );
            add_bk_option( 'booking_user_role_settings', 'administrator' );
        }
        $blg_title = get_option('blogname');
        $blg_title = str_replace('"', '', $blg_title);
        $blg_title = str_replace("'", '', $blg_title);

        add_bk_option( 'booking_email_reservation_adress', htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_reservation_from_adress', '[visitoremail]'); //htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_reservation_subject',__('New booking' ,'booking'));
        add_bk_option( 'booking_email_reservation_content',htmlspecialchars(sprintf(__('You need to approve a new booking %s for: %s Person detail information:%s Currently a new booking is waiting for approval. Please visit the moderation panel%sThank you, %s' ,'booking'),'[bookingtype]','[dates]<br/><br/>','<br/> [content]<br/><br/>',' [moderatelink]<br/><br/>',$blg_title.'<br/>[siteurl]')));

        
        add_bk_option( 'booking_email_newbookingbyperson_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_newbookingbyperson_subject',__('New booking' ,'booking'));
        if( $this->wpdev_bk_personal !== false )
            add_bk_option( 'booking_email_newbookingbyperson_content',htmlspecialchars(sprintf(__('Your reservation %s for: %s is processing now! We will send confirmation by email. %sYou can edit this booking at this page: %s  Thank you, %s' ,'booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>', '[visitorbookingediturl]<br/><br/>' , $blg_title.'<br/>[siteurl]')));
        else
            add_bk_option( 'booking_email_newbookingbyperson_content',htmlspecialchars(sprintf(__('Your reservation %s for: %s is processing now! We will send confirmation by email. %s Thank you, %s' ,'booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>', $blg_title.'<br/>[siteurl]')));
        add_bk_option( 'booking_is_email_newbookingbyperson_adress', 'Off' );
        
        
        add_bk_option( 'booking_email_approval_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_approval_subject',__('Your booking has been approved' ,'booking'));
        if( $this->wpdev_bk_personal !== false )
            add_bk_option( 'booking_email_approval_content',htmlspecialchars(sprintf(__('Your reservation %s for: %s has been approved.%sYou can edit the booking on this page: %s Thank you, %s' ,'booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>', '[visitorbookingediturl]<br/><br/>' , $blg_title.'<br/>[siteurl]')));
        else add_bk_option( 'booking_email_approval_content',htmlspecialchars(sprintf(__('Your booking %s for: %s has been approved.%sThank you, %s' ,'booking'),'[bookingtype]','[dates]','<br/><br/>[content]<br/><br/>',$blg_title.'<br/>[siteurl]')));

        add_bk_option( 'booking_email_deny_adress',htmlspecialchars('"Booking system" <' .get_option('admin_email').'>'));
        add_bk_option( 'booking_email_deny_subject',__('Your booking has been declined' ,'booking'));
        add_bk_option( 'booking_email_deny_content',htmlspecialchars(sprintf(__('Your booking %s for: %s has been  canceled. %sThank you, %s' ,'booking'),'[bookingtype]','[dates]','<br/>[denyreason]<br/><br/>[content]<br/><br/>',$blg_title.'<br/>[siteurl]')));

        add_bk_option( 'booking_is_email_reservation_adress', 'On' );
        add_bk_option( 'booking_is_email_approval_adress', 'On' );
        add_bk_option( 'booking_is_email_deny_adress', 'On' );

        add_bk_option( 'booking_is_email_approval_send_copy_to_admin' , 'Off' );
        add_bk_option( 'booking_is_email_deny_send_copy_to_admin' , 'Off' );


        add_bk_option( 'booking_widget_title', __('Booking form' ,'booking') );
        add_bk_option( 'booking_widget_show', 'booking_form' );
        add_bk_option( 'booking_widget_type', '1' );
        add_bk_option( 'booking_widget_calendar_count',  '1');
        add_bk_option( 'booking_widget_last_field','');

        add_bk_option( 'booking_wpdev_copyright_adminpanel','On' );
        add_bk_option( 'booking_is_show_powered_by_notice','On' );
        add_bk_option( 'booking_is_use_captcha' , 'Off' );
        add_bk_option( 'booking_is_show_legend' , 'Off' );
        add_bk_option( 'booking_legend_is_show_item_available' , 'On' );
        add_bk_option( 'booking_legend_text_for_item_available', __('Available' ,'booking') );
        add_bk_option( 'booking_legend_is_show_item_pending', 'On' );
        add_bk_option( 'booking_legend_text_for_item_pending', __('Pending' ,'booking') );
        add_bk_option( 'booking_legend_is_show_item_approved', 'On' );
        add_bk_option( 'booking_legend_text_for_item_approved', __('Booked' ,'booking') );
        if ( class_exists('wpdev_bk_biz_s') ) {
            add_bk_option( 'booking_legend_is_show_item_partially', 'On' );
            add_bk_option( 'booking_legend_text_for_item_partially', __('Partially booked' ,'booking') );
        }
        add_bk_option( 'booking_legend_is_show_numbers', 'On' );                //FixIn:6.0.1.4


        // Create here tables which is needed for using plugin
        global $wpdb;
        $charset_collate = '';
        //if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset) ) $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
            if ( ! empty($wpdb->collate) ) $charset_collate .= " COLLATE $wpdb->collate";
        //}

        $wp_queries = array();
        if ( ! wpbc_is_table_exists('booking') ) { // Cehck if tables not exist yet

            $simple_sql = "CREATE TABLE {$wpdb->prefix}booking (
                     booking_id bigint(20) unsigned NOT NULL auto_increment,
                     form text ,
                     booking_type bigint(10) NOT NULL default 1,
                     PRIMARY KEY  (booking_id)
                    ) {$charset_collate};";
            $wpdb->query( $simple_sql );
        } elseif  (wpbc_is_field_in_table_exists('booking','form') == 0) {
            $wp_queries[]  = "ALTER TABLE {$wpdb->prefix}booking ADD form TEXT AFTER booking_id";
        }

        if  (wpbc_is_field_in_table_exists('booking','modification_date') == 0) {
            $wp_queries[]  = "ALTER TABLE {$wpdb->prefix}booking ADD modification_date datetime AFTER booking_id";            
        }

        if  (wpbc_is_field_in_table_exists('booking','sort_date') == 0) {
            $wp_queries[]  = "ALTER TABLE {$wpdb->prefix}booking ADD sort_date datetime AFTER booking_id";
        }

        if  (wpbc_is_field_in_table_exists('booking','status') == 0) {
            $wp_queries[]  = "ALTER TABLE {$wpdb->prefix}booking ADD status varchar(200) NOT NULL default '' AFTER booking_id";
        }

        if  (wpbc_is_field_in_table_exists('booking','is_new') == 0) {
            $wp_queries[]  = "ALTER TABLE {$wpdb->prefix}booking ADD is_new bigint(10) NOT NULL default 1 AFTER booking_id";            
        }

        // Version: 5.2 - Google ID of the booking for Sync functionality
        if  (wpbc_is_field_in_table_exists('booking','sync_gid') == 0) {
            $wp_queries[]  = "ALTER TABLE {$wpdb->prefix}booking ADD sync_gid varchar(200) NOT NULL default '' AFTER booking_id";            
        }
        
        if ( ! wpbc_is_table_exists('bookingdates') ) { // Cehck if tables not exist yet
            $simple_sql = "CREATE TABLE {$wpdb->prefix}bookingdates (
                     booking_id bigint(20) unsigned NOT NULL,
                     booking_date datetime NOT NULL default '0000-00-00 00:00:00',
                     approved bigint(20) unsigned NOT NULL default 0
                    ) {$charset_collate}";
            $wpdb->query( $simple_sql );

            if( $this->wpdev_bk_personal == false ) {
                $wp_queries[] = "INSERT INTO {$wpdb->prefix}booking ( form, modification_date ) VALUES (
                     'text^name1^Jony~text^secondname1^Smith~text^email1^example-free@wpbookingcalendar.com~text^phone1^8(038)458-77-77~textarea^details1^Reserve a room with sea view', NOW() );";
            }
        }

        if (!class_exists('wpdev_bk_biz_l')) {
            if  (wpbc_is_index_in_table_exists('bookingdates','booking_id_dates') == 0) {
                $simple_sql = "CREATE UNIQUE INDEX booking_id_dates ON {$wpdb->prefix}bookingdates (booking_id, booking_date);";
                $wpdb->query( $simple_sql );
            }
        } else {
            if  (wpbc_is_index_in_table_exists('bookingdates','booking_id_dates') != 0) {
                $simple_sql = "DROP INDEX booking_id_dates ON  {$wpdb->prefix}bookingdates ;";
                $wpdb->query( $simple_sql );
            }
        }
        

        if (count($wp_queries)>0) {
            foreach ($wp_queries as $wp_q)
                $wpdb->query( $wp_q );

            if( $this->wpdev_bk_personal == false ) {
                $temp_id = $wpdb->insert_id;
                $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                         booking_id,
                         booking_date
                        ) VALUES
                        ( ". $temp_id .", CURDATE()+ INTERVAL 2 day ),
                        ( ". $temp_id .", CURDATE()+ INTERVAL 3 day ),
                        ( ". $temp_id .", CURDATE()+ INTERVAL 4 day );";
                $wpdb->query( $wp_queries_sub );
            }
        }

        // if( $this->wpdev_bk_personal !== false )  $this->wpdev_bk_personal->pro_activate();
        make_bk_action('wpdev_booking_activation');

        update_bk_option( 'booking_version_num',WP_BK_VERSION_NUM);             //FixIn:5.4.2
        
        // Examples in demos
        if ( $is_demo ) {  $this->createExamples4Demo(); }
        
        /**
        // Fill Development server by initial bookings
        if (  $_SERVER['HTTP_HOST'] === 'dev'  ) {  
            for ($i = 0; $i < 5; $i++) {
                //if (!class_exists('wpdev_bk_personal')) 
                $this->createExamples4Demo( array(1,2,3,4,5,6,7,8,9,10,11,12) ); 
            }
        }
        $this->setDefaultInitialValues();/**/

        $this->reindex_booking_db();

        update_bk_option( 'booking_activation_process','Off');
    }

    // Deactivate
    function wpdev_booking_deactivate() {

        // set execution time to 15 minutes, its not worked if we have SAFE MODE ON at PHP
        if (function_exists('set_time_limit')) 		if( !in_array(ini_get('safe_mode'),array('1', 'On')) ) set_time_limit(900);


        $is_delete_if_deactive =  get_bk_option( 'booking_is_delete_if_deactive' ); // check

        if ($is_delete_if_deactive == 'On') {
            // Delete here tables and options, which are needed for using plugin
            delete_bk_option( 'booking_version_num');

            delete_bk_option( 'booking_skin');
            delete_bk_option( 'bookings_num_per_page');
            delete_bk_option( 'booking_sort_order');
            delete_bk_option( 'booking_sort_order_direction');
            delete_bk_option( 'booking_default_toolbar_tab');
            delete_bk_option( 'bookings_listing_default_view_mode');
            delete_bk_option( 'booking_view_days_num');

            delete_bk_option( 'booking_max_monthes_in_calendar');
            delete_bk_option( 'booking_admin_cal_count' );
            delete_bk_option( 'booking_client_cal_count' );
            delete_bk_option( 'booking_start_day_weeek' );
            delete_bk_option( 'booking_title_after_reservation');
            delete_bk_option( 'booking_title_after_reservation_time');
            delete_bk_option( 'booking_type_of_thank_you_message' , 'message' );
            delete_bk_option( 'booking_thank_you_page_URL' , site_url() );
            delete_bk_option( 'booking_is_use_autofill_4_logged_user' ) ;
            
            delete_bk_option( 'booking_form_is_using_bs_css');
            delete_bk_option( 'booking_form_format_type');
            
            delete_bk_option( 'booking_form_field_active1');
            delete_bk_option( 'booking_form_field_required1');
            delete_bk_option( 'booking_form_field_label1');
            delete_bk_option( 'booking_form_field_active2');
            delete_bk_option( 'booking_form_field_required2');
            delete_bk_option( 'booking_form_field_label2');
            delete_bk_option( 'booking_form_field_active3');
            delete_bk_option( 'booking_form_field_required3');
            delete_bk_option( 'booking_form_field_label3');
            delete_bk_option( 'booking_form_field_active4');
            delete_bk_option( 'booking_form_field_required4');
            delete_bk_option( 'booking_form_field_label4');
            delete_bk_option( 'booking_form_field_active5');
            delete_bk_option( 'booking_form_field_required5');
            delete_bk_option( 'booking_form_field_label5');
            
            delete_bk_option( 'booking_form_field_active6' );
            delete_bk_option( 'booking_form_field_required6' );
            delete_bk_option( 'booking_form_field_label6' );
            delete_bk_option( 'booking_form_field_values6' );

        
            delete_bk_option( 'booking_is_days_always_available');
            delete_bk_option( 'booking_check_on_server_if_dates_free');
            
            delete_bk_option( 'booking_date_format');
            delete_bk_option( 'booking_date_view_type');
            delete_bk_option( 'booking_is_delete_if_deactive' ); // check
            delete_bk_option( 'booking_wpdev_copyright_adminpanel' );             // check
            delete_bk_option( 'booking_is_show_powered_by_notice' );             // check
            delete_bk_option( 'booking_is_use_captcha' );
            delete_bk_option( 'booking_is_show_legend' );
            delete_bk_option( 'booking_legend_is_show_item_available' );
            delete_bk_option( 'booking_legend_text_for_item_available' );
            delete_bk_option( 'booking_legend_is_show_item_pending' );
            delete_bk_option( 'booking_legend_text_for_item_pending' );
            delete_bk_option( 'booking_legend_is_show_item_approved' );
            delete_bk_option( 'booking_legend_text_for_item_approved' );
            if ( class_exists('wpdev_bk_biz_s') ) {
                delete_bk_option( 'booking_legend_is_show_item_partially' );
                delete_bk_option( 'booking_legend_text_for_item_partially' );
            }
            delete_bk_option( 'booking_legend_is_show_numbers' );               //FixIn:6.0.1.4



            delete_bk_option( 'booking_dif_colors_approval_pending'   );
            delete_bk_option( 'booking_is_use_hints_at_admin_panel'  );
            delete_bk_option( 'booking_is_not_load_bs_script_in_client'  );
            delete_bk_option( 'booking_is_not_load_bs_script_in_admin'  );
            delete_bk_option( 'booking_is_load_js_css_on_specific_pages'  );
            delete_bk_option( 'booking_pages_for_load_js_css' );

            delete_bk_option( 'booking_multiple_day_selections' );
            delete_bk_option( 'booking_type_of_day_selections' );

            delete_bk_option( 'booking_unavailable_days_num_from_today' );

            delete_bk_option( 'booking_unavailable_day0' );
            delete_bk_option( 'booking_unavailable_day1' );
            delete_bk_option( 'booking_unavailable_day2' );
            delete_bk_option( 'booking_unavailable_day3' );
            delete_bk_option( 'booking_unavailable_day4' );
            delete_bk_option( 'booking_unavailable_day5' );
            delete_bk_option( 'booking_unavailable_day6' );

            delete_bk_option( 'booking_user_role_booking' );
            delete_bk_option( 'booking_user_role_addbooking' );
            delete_bk_option( 'booking_user_role_resources');
            delete_bk_option( 'booking_user_role_settings' );


            delete_bk_option( 'booking_email_reservation_adress');
            delete_bk_option( 'booking_email_reservation_from_adress');
            delete_bk_option( 'booking_email_reservation_subject');
            delete_bk_option( 'booking_email_reservation_content');

            delete_bk_option( 'booking_email_approval_adress');
            delete_bk_option( 'booking_email_approval_subject');
            delete_bk_option( 'booking_email_approval_content');

            delete_bk_option( 'booking_email_deny_adress');
            delete_bk_option( 'booking_email_deny_subject');
            delete_bk_option( 'booking_email_deny_content');

            delete_bk_option( 'booking_is_email_reservation_adress'  );
            delete_bk_option( 'booking_is_email_approval_adress'  );
            delete_bk_option( 'booking_is_email_deny_adress'  );

            delete_bk_option( 'booking_email_newbookingbyperson_adress' );
            delete_bk_option( 'booking_email_newbookingbyperson_subject');
            delete_bk_option( 'booking_email_newbookingbyperson_content');
            delete_bk_option( 'booking_is_email_newbookingbyperson_adress');


            delete_bk_option( 'booking_is_email_approval_send_copy_to_admin'  );
            delete_bk_option( 'booking_is_email_deny_send_copy_to_admin'  );
            
            
            
            delete_bk_option( 'booking_widget_title');
            delete_bk_option( 'booking_widget_show');
            delete_bk_option( 'booking_widget_type');
            delete_bk_option( 'booking_widget_calendar_count');
            delete_bk_option( 'booking_widget_last_field');

            global $wpdb;
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}booking" );
            $wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}bookingdates" );

            // Delete all users booking windows states   
            if ( false === $wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '%booking_%'" ) ){    // All users data
                bk_error('Error during deleting user meta at DB',__FILE__,__LINE__);
                die();
            }
            // Delete or Drafts and Pending from demo sites
            if ( wpdev_bk_is_this_demo() ) {  // Delete all temp posts at the demo sites: (post_status = pending || draft) && ( post_type = post ) && (post_author != 1)
                  $postss = $wpdb->get_results( "SELECT * FROM {$wpdb->posts} WHERE ( post_status = 'pending' OR  post_status = 'draft' OR  post_status = 'auto-draft' OR  post_status = 'trash' OR  post_status = 'inherit' ) AND ( post_type='post' OR  post_type='revision') AND post_author != 1" );
                  foreach ($postss as $pp) { wp_delete_post( $pp->ID , true ); }
             }

           make_bk_action('wpdev_booking_deactivation');
           delete_bk_option( 'booking_activation_process' );
        }
    }



    function  createExamples4Demo($my_bk_types=array()){ global $wpdb;
            $version = get_bk_version();

            if (class_exists('wpdev_bk_multiuser')) {

              if (empty($my_bk_types))   $my_bk_types=array(13,14,15,16,17);                // The booking resources with these IDs are exist in the Demo sites
              else                       shuffle($my_bk_types);

              // Get NUMBER of Bookings
              $bookings_count = $wpdb->get_results( "SELECT COUNT(*) as count FROM {$wpdb->prefix}booking as bk" );
              if (count($bookings_count)>0)   $bookings_count = $bookings_count[0]->count ;
              if ($bookings_count>=20) return;      
              
              
             $max_num_bookings = 4;                                                        // How many bookings exist  per resource   
              foreach ($my_bk_types as $resource_id) {                                     // Loop all resources                                        
                    $bk_type  = $resource_id;                                              // Booking Resource
                    $min_days = 2;
                    $max_days = 7;                    
                    $evry_one = $max_days+3;                                                  // Multiplier of interval between 2 dates of different bookings
                    $days_start_shift =  rand($max_days,(3*$max_days));//(ceil($max_num_bookings/2)) * $max_days;           // How long far ago we are start bookings    
                    
                for ($i = 0; $i < $max_num_bookings; $i++) {               
                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $start_time = '14:00';
                    $end_time   = '12:00';
                    
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^1~';
                    //$form .= 'checkbox^children'.$bk_type.'[]^0~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info').'~';
                    $form .= 'coupon^coupon'.$bk_type.'^ ';

                    
                    $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query( $wp_bk_querie );
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL ".$my_interval." day  ,". $is_appr." ),";                                                
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query( $wp_queries_sub ) ;                                        
                 }
              }
            } else if ( $version == 'free' ) {
                 if (empty($my_bk_types))   $my_bk_types=array(1,1);
                 else                       shuffle($my_bk_types);
                
                 for ($i = 0; $i < count($my_bk_types); $i++) {
                     
                    $bk_type = 1;//rand(1,4);
                    $is_appr = rand(0,1);
                    $evry_one = 2;//rand(1,7);
                    if (  $_SERVER['HTTP_HOST'] === 'dev'  ) {  
                        $evry_one = rand(1,14);//2;//rand(1,7);
                        $num_days = rand(1,7);//2;//rand(1,7);
                        $days_start_shift = rand(-28,0);
                    }
                    
                    
                    $second_name = $this->getInitialValues4Demo('second_name');
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info');

                    $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, modification_date ) VALUES ( '".$form."', NOW()  ) ;";
                    $wpdb->query( $wp_bk_querie );
                    $temp_id = $wpdb->insert_id;
                    $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    
                    if (  $_SERVER['HTTP_HOST'] === 'dev'  ) {  
                        for ($d_num = 0; $d_num < $num_days; $d_num++) {
                                $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL ".($days_start_shift + 2*($i+1)*$evry_one + $d_num)." day  ,". $is_appr." ),";
                        }
                        $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    } else {
                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+2)." day ,". $is_appr." ),
                                        ( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+3)." day  ,". $is_appr." ),
                                        ( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+4)." day ,". $is_appr." );";
                    }
                    
                    $wpdb->query( $wp_queries_sub );
                 }
            } else if ( $version == 'personal' ) {
                    $max_num_bookings = 8;                                                  // How many bookings exist     
                for ($i = 0; $i < $max_num_bookings; $i++) {               

                    $bk_type  = rand(1,4);                                                  // Booking Resource
                    $min_days = 1;
                    $max_days = 7;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift = -1 * (ceil($max_num_bookings/2)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^'.rand(1,4).'~';
                    $form .= 'select-one^children'.$bk_type.'^'.rand(0,3).'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info');

                    $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, booking_type, hash,  modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query( $wp_bk_querie );
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL ".($days_start_shift + $i*$evry_one + $d_num)." day  ,". $is_appr." ),";
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query( $wp_queries_sub );
                 }
            } else if ( $version == 'biz_s' ) {
                    $max_num_bookings = 8;                                                  // How many bookings exist     
                for ($i = 0; $i < $max_num_bookings; $i++) {               

                    $bk_type  = rand(1,4);                                                  // Booking Resource
                    $min_days = 1;
                    $max_days = 1;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift = (ceil($max_num_bookings/4)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $range_time = $this->getInitialValues4Demo('rangetime');
                    $start_time = $range_time[0];
                    $end_time   = $range_time[1];

                    $form  = '';
                    $form .= 'select-one^rangetime'.$bk_type.'^'.$start_time.' - '.$end_time.'~';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^'.rand(1,4).'~';
                    $form .= 'checkbox^children'.$bk_type.'[]^'.rand(0,3).'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info');

                    $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query( $wp_bk_querie );
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
//                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL \"".($days_start_shift + $i*$evry_one + $d_num)." ".$start_time.":01\" DAY_SECOND  ,". $is_appr." ),";
//                        $wp_queries_sub .= "( ". $temp_id .", CURDATE()+ INTERVAL \"".($days_start_shift + $i*$evry_one + $d_num)." ".$end_time  .":02\" DAY_SECOND  ,". $is_appr." ),";                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL -".$days_start_shift." DAY) + INTERVAL \"".$my_interval." ".$start_time.":01\" DAY_SECOND  ,". $is_appr." ),";                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL -".$days_start_shift." DAY) + INTERVAL \"".$my_interval." ".$end_time.":02\" DAY_SECOND  ,". $is_appr." ),";
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query( $wp_queries_sub );
                 }
            } else if ( $version == 'biz_m' ) {
                    $max_num_bookings = 8;                                                  // How many bookings exist     
                for ($i = 0; $i < $max_num_bookings; $i++) {               

                    $bk_type  = rand(1,4);                                                  // Booking Resource
                    $min_days = 3;
                    $max_days = 7;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift =  (ceil($max_num_bookings/2)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $start_time = '14:00';
                    $end_time   = '12:00';
                    
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^'.rand(1,4).'~';
                    $form .= 'checkbox^children'.$bk_type.'[]^'.rand(0,3).'~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info').'~';
                    $form .= 'text^starttime'.$bk_type.'^'.$start_time.'~';
                    $form .= 'text^endtime'.$bk_type.'^'.$end_time;

                    $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query( $wp_bk_querie );
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
                        if ($d_num == 0) {                                       // Check In
                            $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL \"".$my_interval." ".$start_time.":01\" DAY_SECOND  ,". $is_appr." ),";
                        } elseif ($d_num == ($num_days-1) ) {                   // Check Out
                            $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL -".$days_start_shift." day) + INTERVAL \"".$my_interval." ".$end_time.":02\" DAY_SECOND  ,". $is_appr." ),";
                        } else {
                            $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL ".$my_interval." day  ,". $is_appr." ),";
                        }                        
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query( $wp_queries_sub );
                 }

            } else if ( $version == 'biz_l' ) {
                    $max_num_bookings = 12;                                                  // How many bookings exist     
                for ($res_groups = 0; $res_groups < 2; $res_groups++)    
                for ($i = 0; $i < $max_num_bookings; $i++) {               
                    if($res_groups) 
                        $bk_type  = rand(1,6);                                                  // Booking Resource
                    else $bk_type  = rand(7,12);                                                  // Booking Resource
                    $min_days = 2;
                    $max_days = 7;                    
                    $is_appr  = rand(0,1);                                                  // Pending | Approved
                    $evry_one = $max_days;                                                  // Multiplier of interval between 2 dates of different bookings
                    $num_days = rand($min_days,$max_days);                                  // Max Number of Dates for specific booking
                    $days_start_shift =  (ceil($max_num_bookings/2)) * $max_days;       // How long far ago we are start bookings    

                    $second_name = $this->getInitialValues4Demo('second_name');
                    $city =  $this->getInitialValues4Demo('city');
                    $start_time = '14:00';
                    $end_time   = '12:00';

                    
                    $form  = '';
                    $form .= 'text^name'.$bk_type.'^'.$this->getInitialValues4Demo('name').'~';
                    $form .= 'text^secondname'.$bk_type.'^'.$second_name.'~';
                    $form .= 'text^email'.$bk_type.'^'.$second_name.'.example@wpbookingcalendar.com~';
                    $form .= 'text^address'.$bk_type.'^'.$this->getInitialValues4Demo('adress').'~';
                    $form .= 'text^city'.$bk_type.'^'.$city[0].'~';
                    $form .= 'text^postcode'.$bk_type.'^'.$this->getInitialValues4Demo('postcode').'~';
                    $form .= 'text^country'.$bk_type.'^'.$city[1].'~';
                    $form .= 'text^phone'.$bk_type.'^'.$this->getInitialValues4Demo('phone').'~';
                    $form .= 'select-one^visitors'.$bk_type.'^1~';
                    //$form .= 'checkbox^children'.$bk_type.'[]^0~';
                    $form .= 'textarea^details'.$bk_type.'^'.$this->getInitialValues4Demo('info').'~';
                    $form .= 'coupon^coupon'.$bk_type.'^ ';

                    $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, booking_type, cost, hash, modification_date ) VALUES
                                                       ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."'), NOW() ) ;";
                    $wpdb->query( $wp_bk_querie );
                    $temp_id = $wpdb->insert_id;
                    
                    $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                         booking_id,
                                         booking_date,
                                         approved
                                        ) VALUES ";
                    for ($d_num = 0; $d_num < $num_days; $d_num++) {
                        $my_interval = ( $i*$evry_one + $d_num);
                        
                        $wp_queries_sub .= "( ". $temp_id .", DATE_ADD(CURDATE(), INTERVAL  -".$days_start_shift." day) + INTERVAL ".$my_interval." day  ,". $is_appr." ),";                                                
                    }
                    $wp_queries_sub = substr($wp_queries_sub,0,-1) . ";";
                    
                    $wpdb->query( $wp_queries_sub );
                 }
            }
    }


    function getInitialValues4Demo($type) {
        $names = array('Jacob', 'Michael', 'Daniel', 'Anthony', 'William', 'Emma', 'Sophia', 'Kamila', 'Isabella', 'Jack', 'Daniel', 'Matthew',
                'Olivia', 'Emily', 'Grace', 'Jessica', 'Joshua', 'Harry', 'Thomas', 'Oliver', 'Jack' );
        $second_names = array(  'Smith', 'Johnson', 'Widams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilyson', 'Gonzalez', 'Gomez',
                'Taylor', 'Bron', 'Wilson', 'Davies', 'Robinson', 'Evans', 'Walker', 'Jackson', 'Clarke' );
        $city =    array( 'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Antonio', 'San Diego', 'San Jose', 'Detroit',
                'San Francisco', 'Jacksonville', 'Austin',
                'London', 'Birmingham', 'Leeds', 'Glasgow', 'Sheffield', 'Bradford', 'Edinburgh', 'Liverpool', 'Manchester' );
        $adress =   array('30 Mortensen Avenue', '144 Hitchcock Rd', '222 Lincoln Ave', '200 Lincoln Ave', '65 West Alisal St',
                '426 Work St', '65 West Alisal Street', '159 Main St', '305 Jonoton Avenue', '423 Caiptown Rd', '34 Linoro Ave',
                '50 Voro Ave', '15 East St', '226 Middle St', '35 West Town Street', '59 Other St', '50 Merci Ave', '15 Dolof St',
                '226 Gordon St', '35 Sero Street', '59 Exit St' );
        $country = array( 'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'UK','UK','UK','UK','UK','UK','UK','UK','UK' );

        $range_times = array( array("10:00","12:00"), array("12:00","14:00"), array("14:00","16:00"), array("16:00","18:00"), array("18:00","20:00") );

        switch ($type) {
            case 'rangetime':
                return $range_times[ rand(0 , (count($range_times)-1) ) ] ;
                break;
            case 'name':
                return $names[ rand(0 , (count($names)-1) ) ] ;
                break;
            case 'second_name':
                return $second_names[ rand(0 , (count($second_names)-1) ) ] ;
                break;
            case 'adress':
                return $adress[ rand(0 , (count($adress)-1) ) ] ;
                break;
            case 'city':
                $city_num = rand(0 , (count($city)-1) )  ;
                return array( $city[$city_num], $country[$city_num]) ;
                break;
            case 'postcode':
                return (rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9));
                break;
            case 'phone':
                return (rand(0,9).rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9)) ;
                break;
            case 'starttime':
                return ('0'.rand(0,9) . ':' .rand(1,3).'0' );
                break;
            case 'endtime':
                return (rand(12,23) . ':' .rand(1,3).'0' );
                break;
            case 'visitors':
                return rand(1,4);
                break;
            default:
                return '';
                break;
        }

    }


    function setDefaultInitialValues($evry_one = 1) {
        global $wpdb;
        $names = array(  'Jacob', 'Michael', 'Daniel', 'Anthony', 'William', 'Emma', 'Sophia', 'Kamila', 'Isabella', 'Jack', 'Daniel', 'Matthew',
                'Olivia', 'Emily', 'Grace', 'Jessica', 'Joshua', 'Harry', 'Thomas', 'Oliver', 'Jack' );
        $second_names = array(  'Smith', 'Johnson', 'Widams', 'Brown', 'Jones', 'Miller', 'Davis', 'Garcia', 'Rodriguez', 'Wilyson', 'Gonzalez', 'Gomez',
                'Taylor', 'Bron', 'Wilson', 'Davies', 'Robinson', 'Evans', 'Walker', 'Jackson', 'Clarke' );
        $city =    array(       'New York', 'Los Angeles', 'Chicago', 'Houston', 'Phoenix', 'San Antonio', 'San Diego', 'San Jose', 'Detroit',
                'San Francisco', 'Jacksonville', 'Austin',
                'London', 'Birmingham', 'Leeds', 'Glasgow', 'Sheffield', 'Bradford', 'Edinburgh', 'Liverpool', 'Manchester' );
        $adress =   array(      '30 Mortensen Avenue', '144 Hitchcock Rd', '222 Lincoln Ave', '200 Lincoln Ave', '65 West Alisal St',
                '426 Work St', '65 West Alisal Street', '159 Main St', '305 Jonoton Avenue', '423 Caiptown Rd', '34 Linoro Ave',
                '50 Voro Ave', '15 East St', '226 Middle St', '35 West Town Street', '59 Other St', '50 Merci Ave', '15 Dolof St',
                '226 Gordon St', '35 Sero Street', '59 Exit St' );
        $country = array( 'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'US' ,'UK','UK','UK','UK','UK','UK','UK','UK','UK' );
        $info = array(    '  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ' ,'  ','  ','  ','  ','  ','  ','  ','  ','  ' );

        for ($i = 0; $i < count($names); $i++) {
            if ( ($i % $evry_one) !==0 ) {
                continue;
            }
            $bk_type = rand(1,4);
            $is_appr = rand(0,1);

            $start_time = '0'.rand(0,9) . ':' .rand(1,3).'0' ;
            $end_time     = rand(12,23) . ':' .rand(1,3).'0' ;

            $form = 'text^starttime'.$bk_type.'^'.$start_time.'~';
            $form .='text^endtime'.$bk_type.'^'.$end_time.'~';
            $form .='text^name'.$bk_type.'^'.$names[$i].'~';
            $form .='text^secondname'.$bk_type.'^'.$second_names[$i].'~';
            $form .='text^email'.$bk_type.'^'.$second_names[$i].'.example@wpbookingcalendar.com~';
            $form .='text^address'.$bk_type.'^'.$adress[$i].'~';
            $form .='text^city'.$bk_type.'^'.$city[$i].'~';
            $form .='text^postcode'.$bk_type.'^'.rand(0,9).rand(0,9).rand(0,9).rand(0,9).rand(0,9).'~';
            $form .='text^country'.$bk_type.'^'.$country[$i].'~';
            $form .='text^phone'.$bk_type.'^'.rand(0,9).rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'-'.rand(0,9).rand(0,9).'~';
            $form .='select-one^visitors'.$bk_type.'^'.rand(0,9).'~';
            $form .='checkbox^children'.$bk_type.'[]^false~';
            $form .='textarea^details'.$bk_type.'^'.$info[$i];

            $wp_bk_querie = "INSERT INTO {$wpdb->prefix}booking ( form, booking_type, cost, hash ) VALUES
                                               ( '".$form."', ".$bk_type .", ".rand(0,1000).", MD5('". time() . '_' . rand(1000,1000000)."') ) ;";
            $wpdb->query( $wp_bk_querie );

            $temp_id = $wpdb->insert_id;
            $wp_queries_sub = "INSERT INTO {$wpdb->prefix}bookingdates (
                                 booking_id,
                                 booking_date,
                                 approved
                                ) VALUES
                                ( ". $temp_id .", CURDATE()+ INTERVAL \"".(2*($i+1)*$evry_one+2)." ".$start_time.":01"."\" DAY_SECOND ,". $is_appr." ),
                                ( ". $temp_id .", CURDATE()+ INTERVAL ".(2*($i+1)*$evry_one+3)." day  ,". $is_appr." ),
                                ( ". $temp_id .", CURDATE()+ INTERVAL \"".(2*($i+1)*$evry_one+4)." ".$end_time.":02"."\" DAY_SECOND ,". $is_appr." );";
            $wpdb->query( $wp_queries_sub );
        }
    }

    // Upgrade during bulk upgrade of plugins
    function install_in_bulk_upgrade( $return, $hook_extra ){

        if ( is_wp_error($return) )
		return $return;


        if (isset($hook_extra))
            if (isset($hook_extra['plugin'])) {
                $file_name = basename( WPDEV_BK_FILE );
                $pos = strpos( $hook_extra['plugin']  ,  trim($file_name)  );
                if ($pos !== false) {
                        $this->wpdev_booking_activate();
                }
            }
        return $return;
    }

// </editor-fold>


    
    // <editor-fold defaultstate="collapsed" desc="  M A I N T E N C E      F U N C T I O N S  ">
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    ///   M A I N T E N C E      F U N C T I O N S     ////////////////////////////////////////////////////
    ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    function wpdev_booking_technical_booking_section(){
        $link = 'admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option';
        if ( (isset($_REQUEST['reindex_sort_data'])) && ($_REQUEST['reindex_sort_data']=='1') ){
            $this->reindex_booking_db();
        }
        ?><div class='meta-box technical-booking-section'>
            <div <?php $my_close_open_win_id = 'bk_general_settings_technical_section'; ?>  id="<?php echo $my_close_open_win_id; ?>" class="postbox <?php if ( '1' == get_user_option( 'booking_win_' . $my_close_open_win_id ) ) echo 'closed'; ?>" > <div title="<?php _e('Click to toggle' ,'booking'); ?>" class="handlediv"  onclick="javascript:verify_window_opening(<?php echo get_bk_current_user_id(); ?>, '<?php echo $my_close_open_win_id; ?>');"><br></div>
                <h3 class='hndle'><span><?php _e('Technical support section' ,'booking'); ?></span></h3> <div class="inside">
                    
                    <table class="form-table"><tbody>
                        <tr valign="top">
                            <th scope="row"><label for="is_reindex_booking_data" ><?php _e('Reindex booking data' ,'booking'); ?>:</label></th>
                            <td>
                                <input class="button" id="is_reindex_booking_data" type="button" value="<?php  _e('Reindex' ,'booking');; ?>" name="is_reindex_booking_data"
                                    onclick="javascript: window.location.href='<?php echo $link ;?>&reindex_sort_data=1';"
                                       />                                
                            </td>
                        </tr>
                        <tr><td colspan="2">
                                <p class="description"><?php _e(' Click, if you want to reindex booking data by booking dates sort field (Your installation/update of the plugin must be successful).' ,'booking');?></p>
                        </td></tr>
                    </tbody></table>
                    
            </div></div></div><?php
    }


    function reindex_booking_db(){ global $wpdb;
        
        if ( $_SERVER['QUERY_STRING'] == 'page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME. 'wpdev-booking-option&reindex_sort_data=1' )
             $is_show_messages = true;
        else $is_show_messages = false;

        if ($is_show_messages)  {
            // Hide all settings
            ?>
            <style type="text/css" rel="stylesheet" >
                #post_option .meta-box {
                    display:none;
                }
                #post_option .button-primary {
                    display:none;
                }
                #post_option .technical-booking-section {
                    display:block;
                }
            </style>
            <?php
        }

        if  (wpbc_is_field_in_table_exists('booking','sort_date') == 0) {
            $simple_sql  = "ALTER TABLE {$wpdb->prefix}booking ADD sort_date datetime AFTER booking_id";
            $wpdb->query( $simple_sql );
        }

        // Refill the sort date index.
        if  (wpbc_is_field_in_table_exists('booking','sort_date') != 0) {
            
            //1. Select  all bookings ID, where sort_date is NULL in wp_booking
            $sql  = " SELECT booking_id as id" ;
            $sql .= " FROM {$wpdb->prefix}booking as bk" ;
            $sql .= " WHERE sort_date IS NULL" ;
            $bookings_res = $wpdb->get_results(  $sql  );
            
            if ($is_show_messages)  printf(__('%s Found %s not indexed bookings %s' ,'booking'),' ',count($bookings_res), '<br/>');


            if (count($bookings_res) > 0 ) {
                $id_string = '';
                foreach ($bookings_res as $value) {  $id_string .= $value->id . ','; }
                $id_string = substr($id_string,0,-1);

                //2. Select all (FIRST ??) booking_date, where booking_id = booking_id from #1 in wp_bookingdates
                $sql  = " SELECT booking_id as id, booking_date as date" ;
                $sql .= " FROM {$wpdb->prefix}bookingdates as bdt" ;
                $sql .= " WHERE booking_id IN ( ". $id_string ." ) GROUP BY bdt.booking_id ORDER BY bdt.booking_date " ;

                $sort_date_array = $wpdb->get_results(  $sql  );

                if ($is_show_messages) printf(__('%s Finish getting sort dates. %s' ,'booking'),' ','<br/>');

                //3. Insert  that firtst date into the bookings in wp_booking
                $ii=0;
                foreach ($sort_date_array as $value) { $ii++;
                    $sql  = "UPDATE {$wpdb->prefix}booking as bdt ";
                    $sql .= " SET sort_date = '".$value->date. "' WHERE booking_id  = ". $value->id . " ";
                    $wpdb->query( $sql );

                    if ($is_show_messages) printf(__('Updated booking: %s' ,'booking'),$value->id  . '  ['.$ii.' / '.count($bookings_res).'] <br/>');
                }
            }

        }


    }

    // </editor-fold>

}

