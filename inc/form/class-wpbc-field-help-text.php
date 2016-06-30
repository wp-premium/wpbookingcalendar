<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPBC_Field_Help_Text {

    public    $title = '';
    private   $help = array();
    
    protected $type = '';
    protected $htmlid = '';
    
    protected $update_js_function_name = '';
    
    
    function __construct( $params = array() ) {
        
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

    
    public function get_type(){
        return $this->type;
    }
    
    
    public function show(){
       ?><script type="text/javascript">
            jQuery("#wpbc_field_help_section_<?php echo $this->htmlid; ?>").show();
       </script><?php 
    }
    
    
    public function hide(){
       ?><script type="text/javascript">
            jQuery("#wpbc_field_help_section_<?php echo $this->htmlid; ?>").hide();
       </script><?php 
    }
    
    
    public function setAdvancedParameters( $params ){
        // Set Advanced parameters to the Parameters. //////////////////////////
        if (isset($params['advanced'])){
            foreach ($params['advanced'] as $parameter_name => $parameter_value) {
                
                // Settings Properties, like: CHECKED, SELECTED, DISABLED
                if ( isset($parameter_value['prop'] ) ) {

                    ?><script type="text/javascript"><?php

                    foreach ($parameter_value['prop'] as $prop_name=>$prop_value) { ?>
                     jQuery("#<?php echo $this->htmlid . $parameter_name; ?>").prop("<?php echo $prop_name; ?>",<?php echo (($prop_value)?'true':'false'); ?>);                                     
                    <?php } 

                    ?></script><?php 
                }

                // Settings Values to the Fields
                if ( isset($parameter_value['value'] ) ) {

                    ?><script type="text/javascript">                                    
                     jQuery("#<?php echo $this->htmlid . $parameter_name; ?>").val("<?php echo $parameter_value['value']; ?>");                                                                          
                    </script><?php 
                }

                // Settings CSS to the INPUTS
                if ( isset($parameter_value['css'] ) ) {

                    ?><script type="text/javascript"><?php

                    foreach ($parameter_value['css'] as $style_prop_name=>$style_prop_value) { ?>
                     jQuery("#<?php echo $this->htmlid . $parameter_name; ?>").parent().css("<?php echo $style_prop_name; ?>","<?php echo $style_prop_value; ?>");                                     
                    <?php } 

                    ?></script><?php 
                }
                // Settings CSS to the INPUTS
                if ( isset($parameter_value['label'] ) ) {

                    ?><script type="text/javascript"><?php

                    foreach ($parameter_value['label'] as $style_prop_name=>$style_prop_value) { 
                        if ($style_prop_name == 'html') { ?>                       
                     jQuery("#<?php echo $this->htmlid . $parameter_name; ?>").parent().find("label").html("<?php echo $style_prop_value; ?>");
                        <?php } 
                    }

                    ?></script><?php 
                }
            }
        }
        ////////////////////////////////////////////////////////////////////////        
        ?><script type="text/javascript">  <?php echo $this->update_js_function_name; ?>(); </script><?php
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

            $this->setSizeField();      $this->setMaxlengthField();
            
            if ($this->type == 'text')
                $this->setPlaceholderField('one-row');
            
            ?><div class="clear"></div><hr/><?php

            $this->setPutInFormField('one-row');

            $this->setPutInContentField('one-row');

            ?><div class="clear"></div><?php
            if (count($this->help)>0) echo '<hr/>';
            foreach ($this->help as $help_text_section) {
                if (! empty($help_text_section))
                    $this->setHelpInfo( $help_text_section, 'one-row');
            }
            ?><div class="clear"></div>            
        </div>            
        <?php
    }
 
    
    public function setHelpInfo($help_text_section, $group_css=''){
        
        ?><div class="wpbc-help-message <?php echo $group_css; ?>"><?php
        
        echo $help_text_section ; 
        
        ?></div><hr/><?php
    }
    
    
    public function setRequiredField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>" style="white-space: nowrap;">
            <input type="checkbox"  style="width: auto; padding: 0px; margin: 0 5px 0 0;"
                   id="<?php echo $this->htmlid; ?>_required" name="<?php echo $this->htmlid; ?>_required"
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onclick="javascript:this.onchange();"
                   />
            <label class="" for="<?php echo $this->htmlid; ?>_required" style="width: auto; display: inline;"><?php 
            printf(__('Set as %srequired%s' ,'booking'),'<strong>','</strong>'); ?>.</label>            
        </div>
        <?php
    }
    
    
    public function setNameField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">
            <label for="<?php echo $this->htmlid; ?>_name" class="control-label"><strong><?php 
                _e('Name' ,'booking'); ?></strong> (<?php _e('required' ,'booking'); ?>):</label>
            <input type="text" value="unique_field<?php //echo time(); ?>_name" 
                   name="<?php echo $this->htmlid; ?>_name" id="<?php echo $this->htmlid; ?>_name"  
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />
        </div>
        <?php
    }

    
    public function setDefaultValueField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">    
            <label for="<?php echo $this->htmlid; ?>_default" class="control-label"><?php 
                _e('Default value' ,'booking'); ?> (<?php _e('optional' ,'booking'); ?>):</label>
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

    
    public function setIdField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">   
            <label for="<?php echo $this->htmlid; ?>_id" class="control-label"><code><?php 
                _e('ID' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_id" id="<?php echo $this->htmlid; ?>_id" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"                   
                   />            
        </div>
        <?php
    }

    
    public function setPlaceholderField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">    
            <label for="<?php echo $this->htmlid; ?>_placeholder" class="control-label"><code><?php 
            _e('Placeholder' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_placeholder" id="<?php echo $this->htmlid; ?>_placeholder" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />            
        </div>
        <?php
    }
        
    


    public function setClassField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">    
            <label for="<?php echo $this->htmlid; ?>_class" class="control-label"><code><?php 
            _e('Class' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_class" id="<?php echo $this->htmlid; ?>_class" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />            
        </div>
        <?php
    }

    
    public function setSizeField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>">    
            <label for="<?php echo $this->htmlid; ?>_size" class="control-label"><code><?php 
                _e('Size' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_size" id="<?php echo $this->htmlid; ?>_size" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />            
        </div>
        <?php
    }

    
    public function setMaxlengthField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>"> 
            <label for="<?php echo $this->htmlid; ?>_maxlength" class="control-label"><code><?php 
                _e('Maxlength' ,'booking'); ?></code> (<?php _e('optional' ,'booking'); ?>):</label>
            <input type="text" 
                   name="<?php echo $this->htmlid; ?>_maxlength" id="<?php echo $this->htmlid; ?>_maxlength" 
                   onchange="javascript:<?php echo $this->update_js_function_name; ?>();" 
                   onkeypress="javascript:this.onchange();" 
                   onpaste="javascript:this.onchange();" 
                   oninput="javascript:this.onchange();"
                   />            
        </div>
        <?php
    }
    
    
    public function setPutInFormField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>"> 
            <label for="<?php echo $this->htmlid; ?>_put_in_form" class="control-label"><?php 
                printf(__('Copy and paste this shortcode into the form at left side' ,'booking'),'&amp;'); ?></label>
            <input 
                name="<?php echo $this->htmlid; ?>_put_in_form" id="<?php echo $this->htmlid; ?>_put_in_form" 
                class="put-in" type="text"  readonly="readonly" name="text" 
                onfocus="this.select()"
                />
        </div>
        <?php
    }
    
    
    public function setPutInContentField($group_css=''){
        ?>
        <div class="parameter-group <?php echo $group_css; ?>"> 
            <label for="<?php echo $this->htmlid; ?>_put_in_content" class="control-label"><?php 
                printf(__('Put this code in %sContent of Booking Fields%s and in %sEmail Templates%s' ,'booking')
                        ,'<code><a href="javascript:void(0)" onclick="javascript:jQuery(\'.visibility_container\').css(\'display\',\'none\');jQuery(\'#visibility_container_form_content_data\').css(\'display\',\'block\');jQuery(\'.nav-tab\').removeClass(\'booking-submenu-tab-selected\');jQuery(\'.booking-submenu-tab-content\').addClass(\'booking-submenu-tab-selected\');">','</a></code>','<code><a href="admin.php?page=' . WPDEV_BK_PLUGIN_DIRNAME . '/'. WPDEV_BK_PLUGIN_FILENAME . 'wpdev-booking-option&tab=email" >'
                        ,'</a></code>'); ?></label>
            <input 
                name="<?php echo $this->htmlid; ?>_put_in_content" id="<?php echo $this->htmlid; ?>_put_in_content" 
                class="put-in" type="text" readonly="readonly" 
                onfocus="this.select()" 
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
                var p_placeholder = '';
                var p_size      = '';
                var p_maxlength = '';

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
                
                if ( jQuery('#<?php echo $this->htmlid; ?>_placeholder').length )
                if (jQuery('#<?php echo $this->htmlid; ?>_placeholder').val() != '') {
                    
                    p_placeholder = jQuery('#<?php echo $this->htmlid; ?>_placeholder').val().replace(/[^A-Za-z-_0-9\s]/g, "");
                    p_placeholder = p_placeholder.replace(/[\s]/g, "_");
                    
                    jQuery('#<?php echo $this->htmlid; ?>_placeholder').val(p_placeholder);
                    if (p_placeholder != '')
                        p_placeholder = ' placeholder:' + p_placeholder;
                }
                
                
                // Set Size only 0-9
                if (jQuery('#<?php echo $this->htmlid; ?>_size').val() != '') {
                    p_size = jQuery('#<?php echo $this->htmlid; ?>_size').val();
                    p_size = p_size.replace(/[^0-9]/g,'');
                    jQuery('#<?php echo $this->htmlid; ?>_size').val(p_size);
                    if (p_size != '')
                        p_size = ' ' + p_size + '/';
                }
                // Set Max Length only 0-9
                if (jQuery('#<?php echo $this->htmlid; ?>_maxlength').val() != '') {

                    p_maxlength = jQuery('#<?php echo $this->htmlid; ?>_maxlength').val();
                    p_maxlength = p_maxlength.replace(/[^0-9]/g,'');
                    jQuery('#<?php echo $this->htmlid; ?>_maxlength').val(p_maxlength);
                    if (p_maxlength != '')
                        if (p_size == '') 
                            p_maxlength = ' /' + p_maxlength;            
                }

                if (p_name != ''){
                    jQuery('#<?php echo $this->htmlid; ?>_put_in_form').val('[<?php echo $this->get_type(); ?>' 
                            + p_required + ' ' 
                            + p_name 
                            + p_size 
                            + p_maxlength 
                            + p_id 
                            + p_class 
                            + p_placeholder
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
