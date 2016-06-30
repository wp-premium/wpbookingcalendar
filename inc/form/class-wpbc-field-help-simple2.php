<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPBC_Field_Help_Simple2 extends WPBC_Field_Help_Simple {

    public    $title = '';
    protected $help = array();
    
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
            if (is_array($params['help']))
                $this->help   = $params['help'];
            else 
                $this->help[] = $params['help'];
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

            <div class="wpbc_field_help_panel_header"><?php echo $this->title ; ?></div><hr/><?php

            $this->setPutInFormField('one-row');

            $this->setPutInContentField('one-row');
            
            ?><div class="clear"></div><?php 
            
            foreach ($this->help as $help_text_section) {
                if (! empty($help_text_section))
                    $this->setHelpInfo( $help_text_section, 'one-row');
            }
            
            ?><div class="clear"></div>
            <script type="text/javascript">  <?php echo $this->update_js_function_name; ?>(); </script>
        </div>            
        <?php
    }
    
    
    public function js(){
        /* General Format: [captcha] */
        ?><script type="text/javascript">
            function <?php echo $this->update_js_function_name; ?>(){
                var p_name = '<?php echo $this->get_type(); ?>';
                                
                jQuery('#<?php echo $this->htmlid; ?>_put_in_form').val('['+p_name+']');
                jQuery('#<?php echo $this->htmlid; ?>_put_in_content').val('['+p_name+']');            
            }
        </script>
        <?php
    } 
}
?>