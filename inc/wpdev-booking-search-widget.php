<?php

// BookingWidget Class
class BookingSearchWidget extends WP_Widget {
    /** constructor */
    function __construct() {
        parent::__construct(false, $name = 'Booking Calendar - Search Form');
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
        extract( $args );
        $booking_search_widget_title = apply_filters('widget_title', $instance['booking_search_widget_title']);
        if (function_exists('icl_translate')) 
            $booking_search_widget_title = icl_translate( 'wpml_custom', 'wpbc_custom_widget_bookingsearch_title1', $booking_search_widget_title);

        $booking_search_widget_searchresultstitle   = $instance['booking_search_widget_searchresultstitle'];
	$booking_search_widget_noresultstitle       = $instance['booking_search_widget_noresultstitle'];
	$booking_search_widget_searchresults        = $instance['booking_search_widget_searchresults'];

        echo $before_widget;
        
        if ($booking_search_widget_title != '') echo $before_title . htmlspecialchars_decode($booking_search_widget_title) . $after_title;

        echo "<div class='widget_wpdev_booking'>";

        $booking_search_widget_searchresults  = apply_bk_filter('wpdev_check_for_active_language', $booking_search_widget_searchresults );
        $booking_search_widget_noresultstitle = apply_bk_filter('wpdev_check_for_active_language', $booking_search_widget_noresultstitle );
        $booking_search_widget_searchresultstitle = apply_bk_filter('wpdev_check_for_active_language', $booking_search_widget_searchresultstitle );
        
        echo do_shortcode('[bookingsearch searchresults="'.$booking_search_widget_searchresults.'" noresultstitle="'.$booking_search_widget_noresultstitle.'" searchresultstitle="'.$booking_search_widget_searchresultstitle.'" ]');

        echo "</div>";

        echo $after_widget;


    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {
	$instance = $old_instance;

	$instance['booking_search_widget_title']              = strip_tags($new_instance['booking_search_widget_title']);
	$instance['booking_search_widget_searchresultstitle'] = strip_tags($new_instance['booking_search_widget_searchresultstitle']);
	$instance['booking_search_widget_noresultstitle']     = strip_tags($new_instance['booking_search_widget_noresultstitle']);
	$instance['booking_search_widget_searchresults']      = $new_instance['booking_search_widget_searchresults'];
        return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {

        if ( isset($instance['booking_search_widget_title']) ) $booking_search_widget_title           = esc_attr($instance['booking_search_widget_title']);
        else $booking_search_widget_title = __('Search availability' ,'booking');

        if ( isset($instance['booking_search_widget_searchresults']) )
             $booking_search_widget_searchresults = esc_attr($instance['booking_search_widget_searchresults']);
        else $booking_search_widget_searchresults = '';

        if ( isset($instance['booking_search_widget_noresultstitle']) )
             $booking_search_widget_noresultstitle = esc_attr($instance['booking_search_widget_noresultstitle']);
        else $booking_search_widget_noresultstitle = __('Nothing found.' ,'booking');

        if ( isset($instance['booking_search_widget_searchresultstitle']) )
             $booking_search_widget_searchresultstitle = esc_attr($instance['booking_search_widget_searchresultstitle']);
        else $booking_search_widget_searchresultstitle  = __('Search results.' ,'booking');



        ?>
        <p>
            <label for="<?php echo $this->get_field_id('booking_search_widget_title'); ?>"><?php _e('Title of search widget' ,'booking'); ?>:</label><br/>
            <input value="<?php echo $booking_search_widget_title; ?>"
                   name="<?php echo $this->get_field_name('booking_search_widget_title'); ?>"
                   id="<?php echo $this->get_field_id('booking_search_widget_title'); ?>"
                   type="text" class="widefat" style="width:100%;line-height: 1.5em;" />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('booking_search_widget_searchresultstitle'); ?>"><?php _e('Title of search results' ,'booking'); ?>:</label><br/>
            <input value="<?php echo $booking_search_widget_searchresultstitle; ?>"
                   name="<?php echo $this->get_field_name('booking_search_widget_searchresultstitle'); ?>"
                   id="<?php echo $this->get_field_id('booking_search_widget_searchresultstitle'); ?>"
                   type="text" class="widefat" style="width:100%;line-height: 1.5em;" />
            <span style="font-size:10px;"><?php printf(__("Please type the %sTitle of search results%s." ,'booking'),'<em>','</em>'); ?></span>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('booking_search_widget_noresultstitle'); ?>"><?php _e('Nothing found message' ,'booking'); ?>:</label><br/>
            <input value="<?php echo $booking_search_widget_noresultstitle; ?>"
                   name="<?php echo $this->get_field_name('booking_search_widget_noresultstitle'); ?>"
                   id="<?php echo $this->get_field_id('booking_search_widget_noresultstitle'); ?>"
                   type="text" class="widefat" style="width:100%;line-height: 1.5em;" />
            <span style="font-size:10px;"><?php printf(__("Please type the %smessage ,what is showing, when  nothing found%s." ,'booking'),'<em>' ,'</em>'); ?></span>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('booking_search_widget_searchresults'); ?>"><?php _e('URL of Search Results' ,'booking'); ?>*:</label><br/>
            <input value="<?php echo $booking_search_widget_searchresults; ?>"
                   name="<?php echo $this->get_field_name('booking_search_widget_searchresults'); ?>"
                   id="<?php echo $this->get_field_id('booking_search_widget_searchresults'); ?>"
                   type="text" class="widefat" style="width:100%;line-height: 1.5em;" />
            <span style="font-size:10px;"><?php printf(__("Please type the URL of the page %s(with %s shortcode in content)%s, where search results will show." ,'booking'),'','<strong>[bookingsearchresults]</strong>',''); ?></span>
        </p>

        <?php
    }

} // class BookingWidget

add_action('widgets_init', create_function('', 'return register_widget("BookingSearchWidget");'));
?>