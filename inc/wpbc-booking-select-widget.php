<?php

// BookingSelectWidget Class
class BookingSelectWidget extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct(false, $name = 'Booking Calendar - Resource Selection');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $booking_select_widget_title = apply_filters('widget_title', $instance['booking_select_widget_title']);
        $booking_select_widget_title = apply_bk_filter('wpdev_check_for_active_language',  $booking_select_widget_title );
        if (function_exists('icl_translate')) 
            $booking_select_widget_title = icl_translate( 'wpml_custom', 'wpbc_custom_widget_bookingselect_title1', $booking_select_widget_title);
        
        $booking_select_widget_first_option_title =  $instance['booking_select_widget_first_option_title'];
        if (function_exists('icl_translate')) 
            $booking_select_widget_first_option_title = icl_translate( 'wpml_custom', 'wpbc_custom_widget_bookingselect_title2', $booking_select_widget_first_option_title);
        
        
        if ( class_exists('wpdev_bk_biz_m') )
            $booking_select_widget_form_type = $instance['booking_select_widget_form_type'];
        else $booking_select_widget_form_type = '';
        
        $booking_select_widget_type = $instance['booking_select_widget_type'];
        if (empty($booking_select_widget_type)) $booking_select_widget_type = '' ;
        
        $booking_select_widget_calendar_count = $instance['booking_select_widget_calendar_count'];
        if (empty($booking_select_widget_calendar_count))
            $booking_select_widget_calendar_count = 1;
        
        $booking_select_widget_selected_type = $instance['booking_select_widget_selected_type'];


        echo $before_widget;
        if (isset($_GET['booking_hash'])) {
            _e('You need to use special shortcode [bookingedit] for booking editing.' ,'booking');
            echo $after_widget;
            return;
        }

        if ($booking_select_widget_title != '') echo $before_title . htmlspecialchars_decode( $booking_select_widget_title) . $after_title;

        echo "<div class='widget_wpdev_booking  months_num_in_row_1'>";
        
        if ( ! empty($booking_select_widget_form_type ) )
            $booking_select_widget_form_type = " form_type='{$booking_select_widget_form_type}' ";
            
        if ( is_array($booking_select_widget_type) ) {
            $booking_select_widget_type = array_diff( $booking_select_widget_type, array( '' ) ); //Remove empty elements, '' - if user was selected it.
            $booking_select_widget_type = implode(',',$booking_select_widget_type);
        }

        if ( ! empty($booking_select_widget_selected_type ) ) {
            $booking_select_widget_selected_type = ' selected_type="' . $booking_select_widget_selected_type . '"';
        } else
            $booking_select_widget_selected_type = '';
        
        if ( ! empty( $booking_select_widget_first_option_title ) ) {
            $booking_select_widget_first_option_title = apply_bk_filter('wpdev_check_for_active_language',  $booking_select_widget_first_option_title );
            $booking_select_widget_first_option_title = str_replace('"', '', $booking_select_widget_first_option_title );
            $booking_select_widget_first_option_title = ' first_option_title="' . $booking_select_widget_first_option_title . '"';
        } else 
            $booking_select_widget_first_option_title = ' first_option_title=""';
        
        echo do_shortcode('[bookingselect label="" '. $booking_select_widget_first_option_title . $booking_select_widget_form_type .' nummonths='.$booking_select_widget_calendar_count.' type="'.$booking_select_widget_type.'" '.$booking_select_widget_selected_type.']'); // 
            
        echo "</div>";
        
        echo $after_widget;
    }

    
    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
//debuge('POST',$new_instance, $old_instance);die;        
	$instance = $old_instance;

	$instance['booking_select_widget_title']              = strip_tags($new_instance['booking_select_widget_title']);
        $instance['booking_select_widget_first_option_title'] = strip_tags($new_instance['booking_select_widget_first_option_title']);
        
        if ( class_exists('wpdev_bk_biz_m') )
            $instance['booking_select_widget_form_type']   = strip_tags($new_instance['booking_select_widget_form_type']);
	$instance['booking_select_widget_calendar_count']  = strip_tags($new_instance['booking_select_widget_calendar_count']);
	$instance['booking_select_widget_type']            = $new_instance['booking_select_widget_type'];
	$instance['booking_select_widget_selected_type']      = strip_tags($new_instance['booking_select_widget_selected_type']);
        return $instance;
    }

    
    /** @see WP_Widget::form */
    function form($instance) {
//debuge('GET',$instance);        
        if ( isset($instance['booking_select_widget_title']) ) 
             $booking_select_widget_title = esc_attr($instance['booking_select_widget_title']);
        else $booking_select_widget_title = '';
        
        if ( isset($instance['booking_select_widget_first_option_title']) ) 
             $booking_select_widget_first_option_title = esc_attr($instance['booking_select_widget_first_option_title']);
        else $booking_select_widget_first_option_title = '';
        
        if ( ( class_exists('wpdev_bk_biz_m') ) && ( isset($instance['booking_select_widget_form_type']) ) )
             $booking_select_widget_form_type = esc_attr($instance['booking_select_widget_form_type']);
        else $booking_select_widget_form_type = '';
                
        if ( isset($instance['booking_select_widget_calendar_count']) ) 
             $booking_select_widget_calendar_count = esc_attr($instance['booking_select_widget_calendar_count']);
        else $booking_select_widget_calendar_count = 1;
        
        if ( isset($instance['booking_select_widget_type']) ) 
             $booking_select_widget_type = ($instance['booking_select_widget_type']);
        else $booking_select_widget_type = '';
        
        if ( isset($instance['booking_select_widget_selected_type']) ) 
             $booking_select_widget_selected_type = ($instance['booking_select_widget_selected_type']);
        else $booking_select_widget_selected_type = '';
                        
        ?>
        <p>
            <label for="<?php   echo $this->get_field_id('booking_select_widget_title'); ?>"><?php _e('Title' ,'booking'); ?>:</label><br/>
            <input value="<?php echo $booking_select_widget_title; ?>"
                   name="<?php  echo $this->get_field_name('booking_select_widget_title'); ?>"
                   id="<?php    echo $this->get_field_id('booking_select_widget_title'); ?>"
                   type="text" class="widefat" style="width:100%;line-height: 1.5em;" />
        </p>

        <?php 
                $types_list = get_bk_types(false, false); 
        
//debuge($booking_select_widget_type);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('booking_select_widget_type'); ?>"><?php _e('Booking resource' ,'booking'); ?>:</label><br/>
            <select multiple="MULTIPLE" style="height:90px;width:100%;line-height: 1.5em;"
                   name="<?php echo $this->get_field_name('booking_select_widget_type'); ?>[]"
                   id="<?php echo $this->get_field_id('booking_select_widget_type'); ?>"
                   >
                <?php
                    if ( ! is_array( $booking_select_widget_type ) )
                        $booking_select_widget_type = explode(',',$booking_select_widget_type );                
                ?>
                <option value="" style="font-weight:bold;" <?php if( in_array( '',  $booking_select_widget_type  ) ) echo "selected"; ?> ><?php 
                    echo __('All' ,'booking'); 
                ?></option>
                
                <?php
                    foreach ($types_list as $tl) { ?>
                        <option  <?php if( in_array( $tl->id,  $booking_select_widget_type  ) ) echo "selected"; ?>
                                style="<?php if ( isset($tl->parent) ) if ($tl->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                                value="<?php echo $tl->id; ?>"><?php echo $tl->title; ?></option>
                <?php } ?>
            </select>
            <br>
            <span class="description" style="font-size: 0.97em;font-style:italic;padding:2px;"><?php 
                printf(__('Select booking resources, for showing in selectbox. Please use CTRL to select multiple booking resources.' ,'booking'),'<br />'); 
            ?></span>
        </p>


        <p>
            <label for="<?php echo $this->get_field_id('booking_select_widget_selected_type'); ?>"><?php _e('Preselected resource' ,'booking'); ?>:</label><br/>
            <select
                   name="<?php echo $this->get_field_name('booking_select_widget_selected_type'); ?>"
                   id="<?php echo $this->get_field_id('booking_select_widget_selected_type'); ?>"
                   style="width:100%;line-height: 1.5em;">
                
                        <option value="" style="font-weight:bold;" <?php if( $booking_select_widget_selected_type == '' ) echo "selected"; ?> ><?php 
                            echo __('None' ,'booking'); 
                        ?></option>
                
                <?php foreach ($types_list as $tl) { ?>
                        <option  <?php if($booking_select_widget_selected_type == $tl->id ) echo "selected"; ?>
                            style="<?php if  (isset($tl->parent)) if ($tl->parent == 0 ) { echo 'font-weight:bold;'; } else { echo 'font-size:11px;padding-left:20px;'; } ?>"
                            value="<?php echo $tl->id; ?>"><?php echo $tl->title; ?></option>
                <?php } ?>
            </select>
        </p>        

                
        <p>
            <label for="<?php echo $this->get_field_id('booking_select_widget_calendar_count'); ?>"><?php _e('Visible months' ,'booking'); ?>:</label><br/>

            <select style="width:100%;line-height: 1.5em;"
                    name="<?php echo $this->get_field_name('booking_select_widget_calendar_count'); ?>"
                    id="<?php echo $this->get_field_id('booking_select_widget_calendar_count'); ?>"
            >
            <?php foreach ( array(1,2,3,4,5,6,7,8,9,10,11,12) as $tl) { ?>
                <option  <?php if($booking_select_widget_calendar_count == $tl ) echo " selected "; ?>
                        style="font-weight:bold;"
                        value="<?php echo $tl; ?>"><?php echo $tl; ?></option>
            <?php } ?>
            </select>
        </p>

        
        <?php if ( class_exists('wpdev_bk_biz_m')) {
              $booking_forms_extended = get_bk_option( 'booking_forms_extended');

            if ($booking_forms_extended !== false) {
                if ( is_serialized( $booking_forms_extended ) ) 
                     $booking_forms_extended = unserialize($booking_forms_extended);
                else $booking_forms_extended = array();

                ?>
                <p>                    
                    <label for="<?php   echo $this->get_field_id('booking_select_widget_form_type'); ?>"><?php _e('Default form' ,'booking'); ?>:</label><br/>
                    <select style="width:100%;line-height: 1.5em;" 
                            id="<?php   echo $this->get_field_id('booking_select_widget_form_type'); ?>" 
                            name="<?php echo $this->get_field_name('booking_select_widget_form_type'); ?>" >
                                <option value="standard" <?php if ($booking_select_widget_form_type == 'standard') echo ' selected '; ?> ><?php _e('Standard' ,'booking'); ?></option>
                            <?php foreach ($booking_forms_extended as $value) { ?>
                                <option value="<?php echo $value['name']; ?>" <?php 
                                    if ($booking_select_widget_form_type == $value['name']) echo ' selected '; 
                                    ?>><?php echo $value['name']; ?></option>
                            <?php } ?>
                    </select>
                    <br>
                    <span class="description" style="font-size: 0.97em;font-style:italic;padding:2px;"><?php _e('Select default custom booking form' ,'booking'); ?></span>
                </p>
            <?php
            } 
        } ?>
        


        <p>
            <label for="<?php   echo $this->get_field_id('booking_select_widget_first_option_title'); ?>"><?php _e('First option title' ,'booking'); ?>:</label><br/>
            <input value="<?php echo $booking_select_widget_first_option_title; ?>"
                   name="<?php  echo $this->get_field_name('booking_select_widget_first_option_title'); ?>"
                   id="<?php    echo $this->get_field_id('booking_select_widget_first_option_title'); ?>"
                   type="text" class="widefat" style="width:100%;line-height: 1.5em;" />
            <br>
            <span class="description" style="font-size: 0.97em;font-style:italic;padding:2px;"><?php 
                _e('First option in dropdown list.' ,'booking');
                echo ' ';
                _e('Please leave it empty if you want to skip it.' ,'booking'); ?></span>
            
        </p>

        
        <p style="font-size:10px;" > 
        <?php 
            printf(__("%sImportant!!!%s Please note, if you show booking calendar (inserted into post/page) with widget at the same page, then the last will not be visible." ,'booking'),'<strong>','</strong>'); 
        ?>
        </p><?php
    }

} // class BookingSelectWidget

// register widget - New, since WordPress - 2.8
add_action('widgets_init', create_function('', 'return register_widget("BookingSelectWidget");'));

?>