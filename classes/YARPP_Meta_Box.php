<?php

class YARPP_Meta_Box {
    protected $template_text = null;
    protected $yarpp         = null;

    public function __construct() {
        global $yarpp;
        $this->yarpp = $yarpp;
        $this->template_text =
            __(
                "This advanced option gives you full power to customize how your related posts are displayed.&nbsp;".
                "Templates (stored in your theme folder) are written in PHP.",
                'yarpp'
            );
    }

    private function offer_copy_templates() {
        return (!$this->yarpp->diagnostic_custom_templates() && $this->yarpp->admin->can_copy_templates());
    }

    public function checkbox($option, $desc, $class = null) {
        include(YARPP_DIR.'/includes/phtmls/yarpp_meta_box_checkbox.phtml');
    }

    public function template_checkbox($rss=false, $class = null) {
        $pre             = ($rss) ? 'rss_' : '';
        $chosen_template = yarpp_get_option($pre."template");
        $choice          = ($chosen_template === false)
                           ? 'builtin' : (($chosen_template === 'thumbnails') ? 'thumbnails' : 'custom');

        $builtIn         = ($choice === 'builtin') ? 'active' : null;

        $thumbnails      = ($choice === 'thumbnails') ? 'active' : null;
        $diagPostThumbs  = (!$this->yarpp->diagnostic_post_thumbnails()) ? 'disabled' : null;

        $custom          = ($choice === 'custom') ? 'active' : null;
        $diagCustTemplt  = (!$this->yarpp->diagnostic_custom_templates()) ? 'disabled' : null;
        
        include(YARPP_DIR.'/includes/phtmls/yarpp_meta_box_template_checkbox.phtml');
    }

    public function template_file($rss=false, $class=null) {
        $pre             = ($rss) ? 'rss_' : '';
        $chosen_template = yarpp_get_option($pre.'template');
        
        include(YARPP_DIR.'/includes/phtmls/yarpp_meta_box_template_file.phtml');
    }

    public function textbox($option, $desc, $size = 2, $class = null, $note = null) {
        $value = esc_attr(yarpp_get_option($option));
        
        include(YARPP_DIR.'/includes/phtmls/yarpp_meta_box_textbox.phtml');   
    }

    public function beforeafter($options, $desc, $size = 10, $class = null, $note = null) {
        include(YARPP_DIR.'/includes/phtmls/yarpp_meta_box_beforeafter.phtml');
    }

    /* MARK: Last cleaning spot */
    public function tax_weight($taxonomy) {
        $weight     = (int) yarpp_get_option("weight[tax][{$taxonomy->name}]");
        $require    = (int) yarpp_get_option("require_tax[{$taxonomy->name}]");
        
        include(YARPP_DIR.'/includes/phtmls/yarpp_meta_box_tax_weight.phtml');
    }

    /* MARK: Last cleaning spot */
    public function weight($option, $desc) {
        $weight = (int) yarpp_get_option("weight[$option]");

        /* Both require MyISAM fulltext indexing: */
        $fulltext = $this->yarpp->diagnostic_fulltext_disabled() ? ' readonly="readonly" disabled="disabled"' : '';

        echo "<div class='yarpp_form_row yarpp_form_select'><div class='yarpp_form_label'>{$desc}</div><div>";
        echo "<select name='weight[{$option}]'>";
        echo "<option {$fulltext} value='no'".((!$weight) ? ' selected="selected"': '')."  >".__("do not consider", 'yarpp')."</option>";
        echo "<option {$fulltext} value='consider'".(($weight == 1) ? ' selected="selected"': '')."  > ".__("consider", 'yarpp')."</option>";
        echo "<option {$fulltext} value='consider_extra'".(($weight > 1) ? ' selected="selected"': '')."  > ".__("consider with extra weight", 'yarpp')."</option>";
        echo "</select></div></div>";
    }

    public function displayorder($option, $class=null) {
        echo "<div class='yarpp_form_row yarpp_form_select $class'><div class='yarpp_form_label'>";
            _e( "Order results:", 'yarpp' );
            echo "</div><div><select name='$option' id='<?php echo $option; ?>'>";
                $order = yarpp_get_option( $option );
                ?>
                <option value="score DESC" <?php echo ( $order == 'score DESC'?' selected="selected"':'' )?>><?php _e( "score (high relevance to low)", 'yarpp' ); ?></option>
                <option value="score ASC" <?php echo ( $order == 'score ASC'?' selected="selected"':'' )?>><?php _e( "score (low relevance to high)", 'yarpp' ); ?></option>
                <option value="post_date DESC" <?php echo ( $order == 'post_date DESC'?' selected="selected"':'' )?>><?php _e( "date (new to old)", 'yarpp' ); ?></option>
                <option value="post_date ASC" <?php echo ( $order == 'post_date ASC'?' selected="selected"':'' )?>><?php _e( "date (old to new)", 'yarpp' ); ?></option>
                <option value="post_title ASC" <?php echo ( $order == 'post_title ASC'?' selected="selected"':'' )?>><?php _e( "title (alphabetical)", 'yarpp' ); ?></option>
                <option value="post_title DESC" <?php echo ( $order == 'post_title DESC'?' selected="selected"':'' )?>><?php _e( "title (reverse alphabetical)", 'yarpp' ); ?></option>
                <?php
        echo "</select></div></div>";
    }
}