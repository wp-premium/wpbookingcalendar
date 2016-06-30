<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPBC_Field_Help_Textarea extends WPBC_Field_Help_Text {

    public  $title = '';
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
    
    public function init(){
        ?>        
        <div id="wpbc_field_help_section_<?php echo $this->htmlid; ?>" 
             class="wpbc_field_help_panel_background form-horizontal code_description wpbc_field_help_panel_field" 
             style="display:none;" >

            <div class="wpbc_field_help_panel_header"><?php echo $this->title ; ?></div><hr/>

            <?php 

            $this->setRequiredField('one-row');

            $this->setNameField();      $this->setDefaultValueField();

            $this->setIdField();        $this->setClassField();

            $this->setRowsField();      $this->setCollsField();

            ?><div class="clear"></div><hr/><?php

            $this->setPutInFormField('one-row');

            $this->setPutInContentField('one-row');

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
 
    
    public function setSizeField($group_css=''){
        ?>
            <input  type="hidden" value="" class="<?php echo $group_css; ?>"
                   name="<?php echo $this->htmlid; ?>_size" id="<?php echo $this->htmlid; ?>_size" 
                   />            
        <?php
    }

    
    public function setMaxlengthField($group_css=''){
        ?>
            <input type="hidden" value="" class="<?php echo $group_css; ?>"
                   name="<?php echo $this->htmlid; ?>_maxlength" id="<?php echo $this->htmlid; ?>_maxlength" 
                   />            
        <?php
    }

    
    public function setRowsField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">    
            <label for="<?php echo $this->htmlid; ?>_rows" class="control-label"><code><?php 
                _e('Rows' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_rows" id="<?php echo $this->htmlid; ?>_rows" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />            
        </div>
        <?php
    }

    
    public function setCollsField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>"> 
            <label for="<?php echo $this->htmlid; ?>_cols" class="control-label"><code><?php 
                _e('Columns' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_cols" id="<?php echo $this->htmlid; ?>_cols" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />            
        </div>
        <?php
    }
    
    public function js(){
        /* General Format: [text name 9/19 id:88id class:77class "Default_value"] */
        ?><script type="text/javascript">

            function <?php echo $this->update_js_function_name; ?>(){

                var p_name      = '';
                var p_required  = '';
                var p_default   = '';
                var p_id        = '';
                var p_class     = '';
                var p_cols      = '';
                var p_rows = '';

                if ( jQuery('#<?php echo $this->htmlid; ?>_required').prop("checked") ) {
                    p_required = '*';
                }
                // Set Name only Letters
                if (jQuery('#<?php echo $this->htmlid; ?>_name').val() != '') {
                    p_name = jQuery('#<?php echo $this->htmlid; ?>_name').val();
                    p_name = p_name.replace(/[^A-Za-z0-9_-]*[0-9]*$/g,'').replace(/[^A-Za-z0-9_-]/g,'');
                    jQuery('#<?php echo $this->htmlid; ?>_name').val(p_name);
                }
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
                // Set Size only 0-9
                if (jQuery('#<?php echo $this->htmlid; ?>_cols').val() != '') {
                    p_cols = jQuery('#<?php echo $this->htmlid; ?>_cols').val();
                    p_cols = p_cols.replace(/[^0-9]/g,'');
                    jQuery('#<?php echo $this->htmlid; ?>_cols').val(p_cols);
                    if (p_cols != '')
                        p_cols = ' ' + p_cols + 'x';
                }
                // Set Max Length only 0-9
                if (jQuery('#<?php echo $this->htmlid; ?>_rows').val() != '') {

                    p_rows = jQuery('#<?php echo $this->htmlid; ?>_rows').val();
                    p_rows = p_rows.replace(/[^0-9]/g,'');
                    jQuery('#<?php echo $this->htmlid; ?>_rows').val(p_rows);
                    if (p_rows != '')
                        if (p_cols == '') 
                            p_rows = ' x' + p_rows;            
                }

                if (p_name != ''){
                    jQuery('#<?php echo $this->htmlid; ?>_put_in_form').val('[<?php echo $this->get_type(); ?>' 
                            + p_required + ' ' 
                            + p_name 
                            + p_cols 
                            + p_rows 
                            + p_id 
                            + p_class 
                            + p_default 
                            + ']');
                    jQuery('#<?php echo $this->htmlid; ?>_put_in_content').val('['+p_name+']');
                } else {
                    jQuery('#<?php echo $this->htmlid; ?>_put_in_form').val('');
                    jQuery('#<?php echo $this->htmlid; ?>_put_in_content').val('');
                }
            }

        </script>
        <?php
    } 
}
?>