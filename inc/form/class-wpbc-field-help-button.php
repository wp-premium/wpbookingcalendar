<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPBC_Field_Help_Button extends WPBC_Field_Help_Text {

    public    $title = '';
    protected $help ='';
    
    protected $type = '';
    protected $htmlid = '';
    
    protected $update_js_function_name = '';
    
    
    function __construct( $params = array() ) {
        //                                   cols/rows
        //Format [textarea* textarea_-740_name 50x17 id:idd class:clsss "def"]
        
        $this->type     = $params['type'];
        $this->htmlid   = $params['id'];
        $this->title    = $params['title'];
        $this->update_js_function_name = 'wpbc_'. $this->htmlid .'_field_help_update';
        if (isset($params['help'])){
            $this->help = $params['help'];
        }
        $this->js();
        $this->init();
        $this->setAdvancedParameters( $params );
    }
    
    
    // Ovveride structure
    public function init(){
        ?>        
        <div id="wpbc_field_help_section_<?php echo $this->htmlid; ?>" 
             class="wpbc_field_help_panel_background form-horizontal code_description wpbc_field_help_panel_field" 
             style="display:none;" >

            <div class="wpbc_field_help_panel_header"><?php echo $this->title ; ?></div><hr/>

            <?php 

            $this->setDefaultValueField('one-row');

            $this->setIdField();        $this->setClassField();

            ?><div class="clear"></div><hr/><?php

            $this->setPutInFormField('one-row');

            ?><div class="clear"></div><?php 
            
            if (! empty($this->help)) {
                
                ?><hr/><div class="wpbc-help-message"><?php
                
                echo $this->help; 
                
                ?></div><?php
            }
            ?>
            <script type="text/javascript">  <?php echo $this->update_js_function_name; ?>(); </script>
        </div>            
        <?php
    }
     
    
    // Ovverride - Its our title of button
    public function setDefaultValueField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">    
            <label for="<?php echo $this->htmlid; ?>_default" class="control-label"><?php 
                _e('Label' ,'booking'); ?> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_default" id="<?php echo $this->htmlid; ?>_default"
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />
        </div>
        <?php
    }
    
    
    public function js(){
        /* General Format: [submit id:ddd class:cls "Send"] */
        ?><script type="text/javascript">

            function <?php echo $this->update_js_function_name; ?>(){

                var p_default   = '';
                var p_id        = '';
                var p_class     = '';

                // Any characters, but without [ ] and "
                if (jQuery('#<?php echo $this->htmlid; ?>_default').val() != '') {
                    p_default = jQuery('#<?php echo $this->htmlid; ?>_default').val().replace(/[\[\]]/g,'').replace(/"/g,'&quot;');
                    jQuery('#<?php echo $this->htmlid; ?>_default').val(p_default);
                    if (p_default != '')
                        p_default = ' "' +  p_default + '"';
                }

                if (jQuery('#<?php echo $this->htmlid; ?>_id').val() != '') {
                    p_id = jQuery('#<?php echo $this->htmlid; ?>_id').val().replace(/[^A-Za-z-_0-9]/g, "");
                    jQuery('#<?php echo $this->htmlid; ?>_id').val(p_id);
                    if (p_id != '')
                        p_id = ' id:' + p_id;
                }

                if (jQuery('#<?php echo $this->htmlid; ?>_class').val() != '') {
                    p_class = jQuery('#<?php echo $this->htmlid; ?>_class').val().replace(/[^A-Za-z-_0-9]/g, "");
                    jQuery('#<?php echo $this->htmlid; ?>_class').val(p_class);
                    if (p_class != '')
                        p_class = ' class:' + p_class;
                }

                
                jQuery('#<?php echo $this->htmlid; ?>_put_in_form').val('[<?php echo $this->get_type(); ?>' 
                        + p_id 
                        + p_class 
                        + p_default 
                        + ']');
            }

        </script>
        <?php
    } 
}
?>