<?php

// vaguely based on code by MK Safi
// http://msafi.com/fix-yet-another-related-posts-plugin-yarpp-widget-and-add-it-to-the-sidebar/
class YARPP_Widget extends WP_Widget {
	function YARPP_Widget() {
		parent::WP_Widget(false, $name = __('Related Posts (YARPP)','yarpp'));
	}

	function widget($args, $instance) {
		global $post;
		if ( !is_singular() )
			return;

		extract($args);

		$instance['post_type'] = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( yarpp_get_option('cross_relate') )
			$instance['post_type'] = array('post','page');

		$title = apply_filters('widget_title', $instance['title']);
		echo $before_widget;
		if ( !$instance['use_template'] ) {
			echo $before_title;
			if ($title)
				echo $title;
			else
				_e('Related Posts (YARPP)','yarpp');
			echo $after_title;
		}

		$instance['domain'] = 'widget';
		echo yarpp_related(null, $instance, false);
		echo $after_widget;
	}

	function update($new_instance, $old_instance) {
		// this starts with default values.
		$instance = array( 'promote_yarpp' => 0, 'use_template' => 0 );
		foreach ( $instance as $field => $val ) {
			if ( isset($new_instance[$field]) )
				$instance[$field] = 1;
		}
		if ($instance['use_template']) {
			$instance['template_file'] = $new_instance['template_file'];
			$instance['title'] = $old_instance['title'];
		} else {
			$instance['template_file'] = $old_instance['template_file'];
			$instance['title'] = $new_instance['title'];
		}
		return $instance;
	}

	function form($instance) {
		$title = esc_attr($instance['title']);
		$template_file = $instance['template_file'];
		?>
				<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>

			<?php // if there are YARPP templates installed...
			
				$templates = glob(STYLESHEETPATH . '/yarpp-template-*.php');
				if ( is_array($templates) && count($templates) ): ?>

				<p><input class="checkbox" id="<?php echo $this->get_field_id('use_template'); ?>" name="<?php echo $this->get_field_name('use_template'); ?>" type="checkbox" <?php checked($instance['use_template'], true) ?> /> <label for="<?php echo $this->get_field_id('use_template'); ?>"><?php _e("Display using a custom template file",'yarpp');?></label></p>
				<p id="<?php echo $this->get_field_id('template_file_p'); ?>"><label for="<?php echo $this->get_field_id('template_file'); ?>"><?php _e("Template file:",'yarpp');?></label> <select name="<?php echo $this->get_field_name('template_file'); ?>" id="<?php echo $this->get_field_id('template_file'); ?>">
					<?php foreach ($templates as $template): ?>
					<option value='<?php echo htmlspecialchars(basename($template))?>'<?php echo (basename($template)==$template_file)?" selected='selected'":'';?>><?php echo htmlspecialchars(basename($template))?></option>
					<?php endforeach; ?>
				</select><p>

			<?php endif; ?>

				<p><input class="checkbox" id="<?php echo $this->get_field_id('promote_yarpp'); ?>" name="<?php echo $this->get_field_name('promote_yarpp'); ?>" type="checkbox" <?php checked($instance['images'], true) ?> /> <label for="<?php echo $this->get_field_id('promote_yarpp'); ?>"><?php _e("Help promote Yet Another Related Posts Plugin?",'yarpp'); ?></label></p>

				<script type="text/javascript">
				jQuery(function($) {
					function ensureTemplateChoice() {
						if ($('#<?php echo $this->get_field_id('use_template'); ?>').attr('checked')) {
							$('#<?php echo $this->get_field_id('title'); ?>').attr('disabled',true);
							$('#<?php echo $this->get_field_id('template_file_p'); ?>').show();
						} else {
							$('#<?php echo $this->get_field_id('title'); ?>').attr('disabled',false);
							$('#<?php echo $this->get_field_id('template_file_p'); ?>').hide();
						}
					}
					$('#<?php echo $this->get_field_id('use_template'); ?>').change(ensureTemplateChoice);
					ensureTemplateChoice();
				});
				</script>

		<?php
	}
}
// new in 2.0: add as a widget
function yarpp_widget_init() {
	register_widget( 'YARPP_Widget' );
}
add_action( 'widgets_init', 'yarpp_widget_init' );
