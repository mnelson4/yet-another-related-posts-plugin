<?php
// @codingStandardsIgnoreStart
if (! defined('ABSPATH')) {
    exit;
} // Exit if accessed directly
// @codingStandardsIgnoreStart

class NARPP_Meta_Box_Display_Web extends NARPP_Meta_Box
{

    public function display()
    {
        global $yarpp;

        echo "<div style='overflow:auto'>";
        echo '<div class="yarpp_code_display"';
        if (! $yarpp->get_option('code_display')) {
            echo ' style="display: none;"';
        }
        echo '><strong>' . __("Website display code example", 'narpp') . '</strong><br /><small>' . __(
                "(Update options to reload.)",
                'narpp'
            ) . "</small><br/><div id='display_demo_web'></div></div>";

        echo "<div class='yarpp_form_row yarpp_form_post_types'><div>";
        echo 'Automatically display related content from NARPP on: ';
        echo " <span class='yarpp_help' data-help='" . esc_attr(
                __(
                    "This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files.",
                    'narpp'
                )
            ) . "'>&nbsp;</span>&nbsp;&nbsp;";
        echo "</div><div>";
        $post_types = yarpp_get_option('auto_display_post_types');
        foreach ($yarpp->get_post_types('objects') as $post_type) {
            echo "<label for='yarpp_post_type_" . esc_attr(
                    $post_type->name
                ) . "'><input id='yarpp_post_type_" . esc_attr(
                     $post_type->name
                 ) . "' name='auto_display_post_types[" . esc_attr($post_type->name) . "]' type='checkbox' ";
            checked(in_array($post_type->name, $post_types));
            echo "/> " . esc_html($post_type->labels->name) . "</label> ";
        }
        echo "</div></div>";

        $this->checkbox('auto_display_archive', __("Also display in archives", 'narpp'));

        $this->textbox('limit', __('Maximum number of related posts:', 'narpp'));
        $this->template_checkbox(false);
        echo "</div>";

        $chosen_template = yarpp_get_option("template");
        $choice = false === $chosen_template
            ? 'builtin'
            :
            ($chosen_template == 'thumbnails' ? 'thumbnails' : 'custom');

        echo "<div class='postbox yarpp_subbox template_options_custom'";
        if ($choice != 'custom') {
            echo ' style="display: none;"';
        }
        echo ">";
        echo '<div class="yarpp_form_row"><div>' . $this->template_text . '</div></div>';
        $this->template_file(false);
        echo "</div>";

        echo "<div class='postbox yarpp_subbox template_options_thumbnails'";
        if ($choice != 'thumbnails') {
            echo ' style="display: none;"';
        }
        echo ">";
        $this->textbox('thumbnails_heading', __('Heading:', 'narpp'), 40);
        $this->textbox('thumbnails_default', __('Default image (URL):', 'narpp'), 40);
        $this->textbox('no_results', __('Default display if no results:', 'narpp'), 40, 'sync_no_results');
        echo "</div>";

        echo "<div class='postbox yarpp_subbox template_options_builtin'";
        if ($choice != 'builtin') {
            echo ' style="display: none;"';
        }
        echo ">";
        $this->beforeafter(
            array('before_related', 'after_related'),
            __("Before / after related entries:", 'narpp'),
            15,
            '',
            __("For example:", 'narpp') . ' &lt;ol&gt;&lt;/ol&gt;' . __(
                ' or ',
                'narpp'
            ) . '&lt;div&gt;&lt;/div&gt;'
        );
        $this->beforeafter(
            array('before_title', 'after_title'),
            __("Before / after each related entry:", 'narpp'),
            15,
            '',
            __("For example:", 'narpp') . ' &lt;li&gt;&lt;/li&gt;' . __(
                ' or ',
                'narpp'
            ) . '&lt;dl&gt;&lt;/dl&gt;'
        );

        $this->checkbox('show_excerpt', __("Show excerpt?", 'narpp'), 'show_excerpt');
        $this->textbox('excerpt_length', __('Excerpt length (No. of words):', 'narpp'), 10, 'excerpted');

        $this->beforeafter(
            array('before_post', 'after_post'),
            __("Before / after (excerpt):", 'narpp'),
            10,
            'excerpted',
            __("For example:", 'narpp') . ' &lt;li&gt;&lt;/li&gt;' . __(
                ' or ',
                'narpp'
            ) . '&lt;dl&gt;&lt;/dl&gt;'
        );

        $this->textbox('no_results', __('Default display if no results:', 'narpp'), 40, 'sync_no_results');
        echo "</div>";

        $this->displayorder('order');

        $this->checkbox(
            'promote_yarpp',
            __("Help promote Not Another Related Posts Plugin?", 'narpp') .
            '<span class="yarpp_help" data-help="' .
            'This option will add the line &ldquo;powered by NARPP&rdquo; beneath the related posts section. ' .
            'This link is greatly appreciated."></span>'
        );
    }
}