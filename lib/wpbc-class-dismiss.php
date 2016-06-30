<?php
// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class WPBC_Dismiss {
    
    public  $element_id;
    public  $title;
    public  $html_class;

    public function __construct( ) {
        
        // Define the JavaScript functions.
        add_action( 'admin_head', array( $this, 'admin_head' ) );        
    }
    
    public function admin_head() {
        ?><script type="text/javascript">
            //<![CDATA[
            function wpbc_dismiss_window(us_id,  window_id ){

                jQuery.ajax({                                           // Start Ajax Sending
                        // url: '<?php echo WPDEV_BK_PLUGIN_URL . '/' . WPDEV_BK_PLUGIN_FILENAME; ?>',
                        url: wpbc_ajaxurl,
                        type:'POST',
                        success: function (data, textStatus){if( textStatus == 'success')   jQuery('#ajax_respond').html( data );},
                        error:function (XMLHttpRequest, textStatus, errorThrown){window.status = 'Ajax sending Error status:'+ textStatus;alert(XMLHttpRequest.status + ' ' + XMLHttpRequest.statusText);if (XMLHttpRequest.status == 500) {alert('Please check at this page according this error:' + ' http://wpbookingcalendar.com/faq/#ajax-sending-error');}},
                        // beforeSend: someFunction,
                        data:{
                            //ajax_action : 'USER_SAVE_WINDOW_STATE',
                            action : 'USER_SAVE_WINDOW_STATE',
                            user_id: us_id ,
                            window: window_id,
                            is_closed: 1,
                            wpbc_nonce: document.getElementById('wpbc_admin_panel_dismiss_window_nonce').value 
                        }
                });
            }
            //]]>
            function wpbc_hide_window(window_id ){
                jQuery('#'+ window_id ).fadeOut(1000);
            }            
        </script>
        <style type="text/css" media="screen">
            .wpbc-panel-dismiss {
                    position: absolute;
                    top: 5px;
                    right: 10px;
                    padding: 8px 3px;
                    font-size: 13px;
                    text-decoration: none;
                    line-height: 1;
                    outline: 0 none;
                    cursor: pointer;
                    font-family: sans-serif;
            }
            .wpbc-panel-dismiss:before {
                    content: ' ';
                    position: absolute;
                    left: -12px;
                    width: 10px;
                    height: 100%;
                    background: url('../wp-admin/images/xit.gif') 0 7% no-repeat;
            }
            .wpbc-panel-dismiss:hover:before {
                    background-position: 100% 7%;
            }
        </style>
        <?php
    }

    
    public function render( $params = array() ){
        if (isset($params['id'])) 
                $this->element_id = $params['id'];
        else    return  false;                                                  // Exit, because we do not have ID of element
        
        if (isset($params['title'])) 
                $this->title = $params['title'];
        else    $this->title = __( 'Dismiss'  ,'booking');
        
        if (isset($params['class'])) 
                $this->html_class = $params['class'];
        else    $this->html_class = 'wpbc-panel-dismiss';
        
        $this->show();
        return true;
    }

    public function show(){
        
        // Check if this window is already Hided or not
        if ( '1' == get_user_option( 'booking_win_' . $this->element_id ) )     // Panel Hided
            return false;                                                       
        else {                                                                  // Show Panel
            ?><script type="text/javascript"> jQuery('#<?php echo $this->element_id; ?>').show(); </script><?php
        }
        wp_nonce_field('wpbc_ajax_admin_nonce',  "wpbc_admin_panel_dismiss_window_nonce" ,  true , true );
        // Show Hide link
        ?><a class="<?php echo $this->html_class; ?>" href="javascript:void(0)" 
             onclick="javascript: wpbc_hide_window('<?php echo $this->element_id; ?>');
                                 wpbc_dismiss_window(<?php echo get_bk_current_user_id(); ?>, '<?php echo $this->element_id; ?>');"
          ><?php echo $this->title; ?></a><?php
    }

}

global $wpbc_Dismiss;
$wpbc_Dismiss = new WPBC_Dismiss();
?>