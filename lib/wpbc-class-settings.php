<?php
/**
 * @version 1.0
 * @package Booking Calendar 
 * @subpackage Interface
 * @category Settings
 * 
 * @author wpdevelop
 * @link http://wpbookingcalendar.com/
 * @email info@wpbookingcalendar.com
 *
 * @modified 2014.07.28
 */

if ( ! defined( 'ABSPATH' ) ) exit;                                             // Exit if accessed directly

class WPBC_Settings {

    private $is_only_icons = false;    
    private $term;
    private $title;
    private $icon;
    private $description;
    private $style;
    private $link;
    private $sub_menu;
    private $submit_in_toolbar;
    private $submit_title;
    private $submit_form;
    
    function __construct( $params = array() ) {
        
        global $wpbc_settings;
        
        $this->term = 'wpbc_settings' . count($wpbc_settings);
        $this->title = '';
        $this->icon = '';
        $this->description = '';        
        $this->is_only_icons = false;
        $this->style = '';
        $this->link = '';
        $this->sub_menu = array();
        
        $this->submit_in_toolbar = false;
        $this->submit_form = false;
        $this->submit_title = __('Save Changes' ,'booking') ;        
        
        $this->setParams($params);
        
        // Connect Submenu Line for active settings page
        add_bk_action('wpdev_booking_settings_top_menu_submenu_line', array(&$this, 'toolbar_top_sub_menu'));        
        
        // Connect Content of Settings page
        add_bk_action('wpdev_booking_settings_show_content', array(&$this, 'settings_show_content')); // Settings
        
    }
    
    // Get settings page URL
    private function get_settings_url(){
        if ( empty($this->link) )
            return "admin.php?page=".WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME ."wpdev-booking-option&tab=" . $this->term ;
        else 
            return $this->link;
    }
    
    
    // Parameters - Define
    public function setParams($params){
        
        if (isset($params['term']))
            $this->term = $params['term'];
        
        if (isset($params['title']))
            $this->title = $params['title'];
        
        if (isset($params['icon']))
            $this->icon = $params['icon'];
        
        if (isset($params['description']))
            $this->description = $params['description'];     
        
        if (isset($params['is_only_icons']))
            $this->is_only_icons = $params['is_only_icons'];  
        
        if (isset($params['style']))
            $this->style = $params['style'];  
             
        if (isset($params['link']))
            $this->link = $params['link'];  
             
             
        if (isset($params['submit_form']))
            $this->submit_form = $params['submit_form'];  
             
        if (isset($params['submit_title']))
            $this->submit_title = $params['submit_title'];  
        
        if (isset($params['submit_in_toolbar']))
            $this->submit_in_toolbar = $params['submit_in_toolbar'];              

        
        // HOOK: on submit of settings form
        if ( ( isset($params['settings_submit']) ) && (! empty($this->submit_form)) ){        
            add_bk_action('wpbc_submit_of_settings_form_' . $this->submit_form , $params['settings_submit'] ); 
        }
        
        // Content of Settings page        
        if ( ( isset($params['settings_content']) ) && (isset( $params['term'] )) ){        
            add_bk_action('wpbc_content_of_settings_form_' . $params['term'],  $params['settings_content'] ); 
        }
        
    }
    
    
    // Top TABs - Show
    public function toolbar_top_tabs_menu() {
                
        if ( (isset($_GET['tab'])) && ($_GET['tab'] == $this->term ) ) {              
            $is_selected = true;
        } else {  
            $is_selected = false;             
        }  

        // Link
        if ( $is_selected ) {             
            /* ?><span class="nav-tab nav-tab-active" <?php if (! empty($this->style)) { echo ' style="'.$this->style.'" '; } ?>><?php /**/            
            ?><a title="<?php echo $this->description; ?>" href="<?php echo $this->get_settings_url(); ?>"
               rel="tooltip" class="nav-tab nav-tab-active" <?php if (! empty($this->style)) { echo ' style="'.$this->style.'" '; } ?>><?php   

        } else { 
            ?><a title="<?php echo $this->description; ?>" href="<?php echo $this->get_settings_url(); ?>"
               rel="tooltip" class="nav-tab tooltip_bottom" <?php if (! empty($this->style)) { echo ' style="'.$this->style.'" '; } ?>><?php   
        } 
        
        // Icon
        ?><i class="<?php if ( $is_selected ) { echo 'icon-white '; } echo $this->icon; ?>"></i><?php 
        
        
        // Title
        ?><span class="nav-tab-text"> <?php  
                
        if ( $this->is_only_icons ) 
            echo '&nbsp;'; 
        else 
            echo $this->title; 
        
        ?></span><?php 
        
        
        if ( $is_selected ) { 
            ?></a><script type="text/javascript"> jQuery(document).ready(function(){ jQuery('div.bookingpage h2').html( '<?php echo $this->description; ?>'); }); </script><?php             
        } else { 
            ?></a><?php             
        }
         
        echo '&nbsp;';
    }    
    
    
    // Sub Menu - Define 
    public function add_sub_menu($param) {
        
        /*
        $this->sub_menu[] = array(    
                selected => true 
              , 'title' => __('Google Calendar' ,'booking') 
              , 'description' => __('Customization of synchronization with Google Calendar' ,'booking')
              , 'header' => __('Synchronization Bookings Settings' ,'booking')
              , 'visibility_container' => 'visibility_container_sync_google_calendar'
              , 'active_status' => 'booking_is_sync_google_calendar'
              , 'style' => ''
            );                
        */       
        
        // Predefined parameters
        $new_sub_menu_element = array( 
                                          'style' => ''
                                        , 'selected' => false
                                        , 'active_status' => false
                                        );
        
        // Define other parameters
        foreach ($param as $key => $value) {
            $new_sub_menu_element[ $key ] = $value ;
        }
        
        // Add Sub Menu item
        $this->sub_menu[] = $new_sub_menu_element;
        
        // HOOK: on submit of settings form
        if ( ( isset($new_sub_menu_element['settings_submit']) ) && (! empty($this->submit_form)) ){        
            add_bk_action('wpbc_submit_of_settings_form_' . $this->submit_form , $new_sub_menu_element['settings_submit'] ); 
        }
        
        // Content of Settings page        
        if ( ( isset($new_sub_menu_element['settings_content']) ) && (isset( $new_sub_menu_element['visibility_container'] )) ){        
            add_bk_action('wpbc_content_of_settings_form_' . $new_sub_menu_element['visibility_container'],  $new_sub_menu_element['settings_content'] ); 
        }
    }


    // Sub Menu - Show
    public function toolbar_top_sub_menu() {
        
        if ( (isset($_GET['tab'])) && ( $_GET['tab'] == $this->term ) && ( count($this->sub_menu) > 0 ) ) {
            
            $active_itmes_in_top_menu = array(); 
            ?>
            <div class="booking-submenu-tab-container">
                <div class="nav-tabs booking-submenu-tab-insidecontainer"><?php 
                
                    foreach ( $this->sub_menu as $sub_menu_item ) { 
                            
                            ?> <a href="javascript:void(0)" 
                                 onclick="javascript:jQuery('.visibility_container').css('display','none');
                                     jQuery('#<?php echo $sub_menu_item['visibility_container']; ?>').css('display','block');
                                     jQuery('.nav-tab').removeClass('booking-submenu-tab-selected');
                                     jQuery(this).addClass('booking-submenu-tab-selected');"
                                 rel="tooltip" class="tooltip_bottom nav-tab booking-submenu-tab <?php 
                                     if ( $sub_menu_item['selected'] ) {
                                         echo 'booking-submenu-tab-selected ';
                                     }
                                     if (! empty($sub_menu_item['active_status'])) {
                                        if ( get_bk_option( $sub_menu_item['active_status'] ) != 'On' ) { 
                                            echo ' booking-submenu-tab-disabled ';                                             
                                        }                                         
                                     } ?>"
                                 original-title="<?php echo $sub_menu_item['description']; ?>"
                                 style="<?php echo $sub_menu_item['style']; ?>"
                               > <?php 
                               echo $sub_menu_item['title'];
                               
                            if (! empty($sub_menu_item['active_status'])) {  
                              
                                $active_itmes_in_top_menu[] = array( $sub_menu_item['active_status'] , $sub_menu_item['active_status'] . '_dublicated' );
                              
                              ?> <input 
                                   type="checkbox" <?php if ( get_bk_option( $sub_menu_item['active_status'] ) == 'On' ) echo ' checked="CHECKED" '; ?>  
                                   name="<?php echo $sub_menu_item['active_status'] . '_dublicated'; ?>" 
                                   id="<?php echo $sub_menu_item['active_status'] . '_dublicated'; ?>" 
                                   onchange="if ( jQuery('#' + '<?php echo $sub_menu_item['active_status']; ?>' ).length ) {document.getElementById('<?php echo $sub_menu_item['active_status']; ?>').checked=this.checked;}" 
                                 /><?php                             
                            } 
                            ?> </a> <?php
                    } 
                    
                    
                    // Set Submit button to toolbar
                    if ( $this->submit_in_toolbar ) {
                            ?> <input type="button" class="button-primary button" value="<?php echo $this->submit_title; ?>" 
                                       style="float:right;"
                                       onclick="document.forms['<?php echo $this->submit_form; ?>'].submit();" />
                                <div class="clear" style="height:0px;"></div><?php                        
                    }
                    
                    
                    if ( count($active_itmes_in_top_menu) > 0 ) {
                        
                        ?> <script type="text/javascript">
                            function recheck_active_itmes_in_top_menu( internal_checkbox, top_checkbox ){
                                
                                if ( ( jQuery('#' + internal_checkbox ).length ) && ( jQuery('#' + top_checkbox ).length ) ) {
                                    if (document.getElementById( internal_checkbox ).checked != document.getElementById( top_checkbox ).checked ) {
                                        document.getElementById( top_checkbox ).checked = document.getElementById( internal_checkbox ).checked;
                                        if ( document.getElementById( top_checkbox ).checked )
                                            jQuery('#' + top_checkbox ).parent().removeClass('booking-submenu-tab-disabled');
                                        else
                                            jQuery('#' + top_checkbox ).parent().addClass('booking-submenu-tab-disabled');
                                    }
                                }
                            }
                            
                            jQuery(document).ready( function(){
                                <?php foreach ($active_itmes_in_top_menu as $active_item) { ?>
                                  recheck_active_itmes_in_top_menu('<?php echo $active_item[0]; ?>', '<?php echo $active_item[1]; ?>');
                                <?php } ?>
                            });
                        </script><?php 
                    
                    } ?>            
                </div>
            </div>
           <?php
        }
        
    }
    
    
    /*
     * Hooks:     
     * make_bk_action('wpbc_submit_of_settings_form_' . $this->submit_form );   // Submitting:
     * 
     * make_bk_action('wpbc_before_content_of_settings_form_' . $this->submit_form );
     * 
     * make_bk_action('wpbc_content_of_settings_form_' . $sub_section['visibility_container'] ); 
     * or
     * make_bk_action('wpbc_content_of_settings_form_' . $this->term );
     * 
     * make_bk_action('wpbc_after_content_of_settings_form_' . $this->submit_form );
     */
    // Show Content
    public function settings_show_content() {
        if ( (isset($_GET['tab'])) && ( $_GET['tab'] == $this->term ) && ($this->submit_form !== false ) ) {
            ?>
            <div class="clear" style="height:0px;"></div>
            <div id="ajax_working"></div>
            <?php            
            if ( isset( $_POST['is_form_sbmitted_'. $this->submit_form ] ) ) {
                   
                   check_admin_referer( 'wpbc_settings_page_'.$this->submit_form  );
                    
                   make_bk_action('wpbc_submit_of_settings_form_' . $this->submit_form );  
            }
            ?>    
            <div class="metabox-holder">
                <?php 
                make_bk_action('wpbc_before_content_of_settings_form_' . $this->submit_form ); 
                ?>
                <form  name="<?php echo $this->submit_form; ?>" id="<?php echo $this->submit_form; ?>"  action="" method="post" >
                    <input type="hidden" name="is_form_sbmitted_<?php echo $this->submit_form; ?>" id="is_form_sbmitted_<?php echo $this->submit_form; ?>" value="1" />
                        
                    <?php 
                    wp_nonce_field( 'wpbc_settings_page_'.$this->submit_form );
                    
                    if ( count($this->sub_menu) > 0 ) {                         // Several Tabs
                        foreach ($this->sub_menu as $sub_section) {
                                                 
                            ?><div  id="<?php echo $sub_section['visibility_container']; ?>" 
                                    style="<?php 
                                    if ( $sub_section['selected'] ) {
                                        echo 'display:block;';                                        
                                    } else {
                                        echo 'display:none;';                                        
                                    } ?>"
                                    class="visibility_container"                                     
                                    ><?php
                            
                                make_bk_action('wpbc_content_of_settings_form_' . $sub_section['visibility_container'] );
                            
                            ?></div><?php   
                            
                        }
                    } else {                                                    // General One Content
                        
                        ?><div  id="visibility_container_<?php echo $this->term; ?>" class="visibility_container" ><?php

                            make_bk_action('wpbc_content_of_settings_form_' . $this->term );

                        ?></div><?php                                
                    }
                    ?>
                    <div class="clear" style="height:10px;"></div>
                    <input class="button-primary button" style="float:right;" type="submit" value="<?php echo $this->submit_title; ?>" name="submit_form" />
                    <div class="clear" style="height:10px;"></div>
                </form>
                <?php 
                    make_bk_action('wpbc_after_content_of_settings_form_' . $this->submit_form ); 
                ?>                
            </div><?php            
        }
    }
}
?>