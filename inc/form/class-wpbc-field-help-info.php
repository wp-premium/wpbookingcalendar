<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class WPBC_Field_Help_Info {

    public  $title = '';
    private $help = array();
    
    private $type = '';
    private $htmlid = '';
    
    function __construct( $params = array() ) {
        
        $this->type     = $params['type'];
        $this->htmlid   = $params['id'];
        $this->title    = $params['title'];
        
        if (isset($params['help'])){
            if (is_array($params['help']))
                $this->help   = $params['help'];
            else 
                $this->help[] = $params['help'];
        }
        $this->init();
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
    
    
    public function init(){
        ?>        
        <div id="wpbc_field_help_section_<?php echo $this->htmlid; ?>" 
             class="wpbc_field_help_panel_background form-horizontal code_description wpbc_field_help_panel_field" 
             style="display:none;" >

            <div class="wpbc_field_help_panel_header"><?php echo $this->title ; ?></div><hr/>

            <?php 
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
    
}
?>