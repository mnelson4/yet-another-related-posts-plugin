<?php

class NARPP_Admin {
	public $core;
	public $hook;
	
	function __construct(&$core) {
		$this->core = &$core;
		
		/* If action = flush and the nonce is correct, reset the cache */
		if (isset($_GET['action']) && $_GET['action'] === 'flush' && check_ajax_referer('yarpp_cache_flush', false, false) !== false) {
			$this->core->cache->flush();
			wp_redirect(admin_url('/options-general.php?page=yarpp'));
			die();
		}

		/* If action = copy_templates and the nonce is correct, copy templates */
		if (isset($_GET['action']) && $_GET['action'] === 'copy_templates' && check_ajax_referer('yarpp_copy_templates', false, false) !== false) {
			$this->copy_templates();
			wp_redirect(admin_url('/options-general.php?page=yarpp'));
			die();
		}


		add_action('admin_init', array($this, 'ajax_register'));
		add_action('admin_menu', array($this, 'ui_register'));

		add_filter('current_screen', array($this, 'settings_screen'));
		add_filter('screen_settings', array($this, 'render_screen_settings'), 10, 2);
		add_filter('default_hidden_meta_boxes', array($this, 'default_hidden_meta_boxes'), 10, 2);
	}

    /**
     * @since 4.0.3 Moved method to Core.
     */
	public function get_templates() {
		return $this->core->get_templates();
	}

    /**
     * Register AJAX services
     */
	function ajax_register() {
		if (defined('DOING_AJAX') && DOING_AJAX) {
                add_action('wp_ajax_yarpp_display_exclude_terms',   array($this, 'ajax_display_exclude_terms'));
                add_action('wp_ajax_yarpp_display_demo',            array($this, 'ajax_display_demo'));
                add_action('wp_ajax_yarpp_display',                 array($this, 'ajax_display'));
                add_action('wp_ajax_yarpp_set_display_code',        array($this, 'ajax_set_display_code'));
		}
	}
	
	function ui_register() {
		global $wp_version;

		if (get_option('yarpp_activated')) {

			delete_option('yarpp_activated');
 			delete_option('yarpp_upgraded');

            add_action('admin_notices', array($this, 'install_notice'));

		}
		
		if ($this->core->get_option('optin')) delete_option('yarpp_upgraded');
		
		/*
		 * Setup Admin
		 */
        $titleName = 'NARPP';
		$this->hook = add_options_page($titleName, $titleName, 'manage_options', 'yarpp', array($this, 'options_page'));
		
		/**
         * @since 3.0.12  Add settings link to the plugins page.
         */
		add_filter('plugin_action_links', array($this, 'settings_link'), 10, 2);

		$metabox_post_types = $this->core->get_option('auto_display_post_types');
		if (!in_array('post', $metabox_post_types)) $metabox_post_types[] = 'post';

		/**
         * @since 3.0  Add meta box.
        */
            foreach ($metabox_post_types as $post_type) {
                $optionsUrl    = esc_url(admin_url('options-general.php?page=yarpp'));
                $title  =
                    __('Related Posts' , 'narpp').
                    '<span class="postbox-title-action">'.
                        '<a href="'.$optionsUrl. '" class="edit-box open-box">'.__('Configure').'</a>'.
                    '</span>';
                add_meta_box('yarpp_relatedposts',$title, array($this, 'metabox'), $post_type, 'normal');
            }
		
		/**
         * @since 3.3: properly enqueue scripts for admin.
         */
		add_action('admin_enqueue_scripts', array($this, 'enqueue'));
	}

	/**
     * @since 3.5.4 Only load metabox code if we're going to be on the settings page.
     */
	function settings_screen($current_screen) {
		if ($current_screen->id !== 'settings_page_yarpp') return $current_screen;

		/**
         * @since 3.3: Load options page sections as meta-boxes.
         */
		include_once(YARPP_DIR . '/includes/narpp_meta_boxes_hooks.php');

		/**
         * @since 3.5.5 Check that add_help_tab method callable (WP >= 3.3).
         */
		if (is_callable(array($current_screen, 'add_help_tab'))) {
			$current_screen->add_help_tab(array(
				'id'        => 'faq',
				'title'     => __('Frequently Asked Questions', 'narpp'),
				'callback'  => array(&$this, 'help_faq')
			));

			$current_screen->add_help_tab(array(
				'id'        => 'dev',
				'title'     => __('Developing with NARPP', 'narpp'),
				'callback'  => array(&$this, 'help_dev')
			));
		}
		
		return $current_screen;
	}
	
	private $readme = null;
	
	public function help_faq() {
		if (is_null($this->readme)) $this->readme = file_get_contents(YARPP_DIR.'/readme.txt');

		if (preg_match('!== Frequently Asked Questions ==(.*?)^==!sm', $this->readme, $matches)) {
			echo $this->markdown($matches[1]);
        } else {
			echo(
                '<a href="https://wordpress.org/plugins/narpp#faq">'.
                    __('Frequently Asked Questions', 'narpp').
                '</a>'
            );
        }
	}
	
	public function help_dev() {
		if (is_null($this->readme)) $this->readme = file_get_contents(YARPP_DIR.'/readme.txt');

		if (preg_match('!== Developing with NARPP ==(.*?)^==!sm', $this->readme, $matches)) {
			echo $this->markdown( $matches[1] );
        } else {
			echo(
                '<a href="https://wordpress.org/plugins/narpp#faq" target="_blank">'.
                    __('Developing with NARPP', 'narpp').
                '</a>'
            );
        }
	}

    public function install_notice(){
    }
	
	// faux-markdown, required for the help text rendering
	protected function markdown( $text ) {
		$replacements = array(
			// strip each line
			'!\s*[\r\n] *!' => "\n",
			
			// headers
			'!^=(.*?)=\s*$!m' => '<h3>\1</h3>',
			
			// bullets
			'!^(\* .*([\r\n]\* .*)*)$!m' => "<ul>\n\\1\n</ul>",
			'!^\* (.*?)$!m' => '<li>\1</li>',
			'!^(\d+\. .*([\r\n]\d+\. .*)*)$!m' => "<ol>\n\\1\n</ol>",
			'!^\d+\. (.*?)$!m' => '<li>\1</li>',
			
			// code block
			'!^(\t.*([\r\n]\t.*)*)$!m' => "<pre>\n\\1\n</pre>",
			
			// wrap p
			'!^([^<\t].*[^>])$!m' => '<p>\1</p>',
			// bold
			'!\*([^*]*?)\*!' => '<strong>\1</strong>',
			// code
			'!`([^`]*?)`!' => '<code>\1</code>',
			// links
			'!\[([^]]+)\]\(([^)]+)\)!' => '<a href="\2" target="_new">\1</a>',
		);
		$text = preg_replace(array_keys($replacements), array_values($replacements), $text);
		
		return $text;
	}
	
	public function render_screen_settings ($output, $current_screen) {
		if ( $current_screen->id != 'settings_page_yarpp' )
			return $output;

		$output .= "<div id='yarpp_extra_screen_settings'><label for='yarpp_display_code'><input type='checkbox' name='yarpp_display_code' id='yarpp_display_code'";
		$output .= checked($this->core->get_option('display_code'), true, false);
		$output .= " />";
		$output .= __('Show example code output', 'narpp');
		$output .= '</label></div>';

		return $output;
	}
	
	// since 3.3
	public function enqueue() {
		$version = defined('WP_DEBUG') && WP_DEBUG ? time() : YARPP_VERSION;
		$screen = get_current_screen();
		if (!is_null($screen) && $screen->id === 'settings_page_yarpp') {
            wp_enqueue_style('wp-pointer');
            wp_enqueue_style('narpp_options', plugins_url('style/options_basic.css', dirname(__FILE__)), array(), $version );

            wp_enqueue_script('postbox');
            wp_enqueue_script('wp-pointer');
            wp_enqueue_script('narpp_options', plugins_url('js/options_basic.js', dirname(__FILE__)), array('jquery'), $version );

		}

		$metabox_post_types = $this->core->get_option('auto_display_post_types');
		if (!is_null($screen) && ($screen->id == 'post' || in_array( $screen->id, $metabox_post_types))) {
			wp_enqueue_script('yarpp_metabox', plugins_url('js/metabox.js', dirname(__FILE__)), array('jquery'), $version );
		}
	}
	
	public function settings_link($links, $file) {
		$this_plugin = dirname(plugin_basename(dirname(__FILE__))).'/yarpp.php';
		if($file == $this_plugin) {
			$links[] = '<a href="options-general.php?page=yarpp">'.__('Settings').'</a>';
		}
		return $links;
	}
	
	public function options_page() {
	    include_once(YARPP_DIR . '/includes/narpp_options.php');
	}

	// @since 3.4: don't actually compute results here, but use ajax instead		
	public function metabox() {
		?>
		<style>
		#yarpp_relatedposts h3 .postbox-title-action {
			right: 30px;
			top: 5px;
			position: absolute;
			padding: 0;
		}
		#yarpp_relatedposts:hover .edit-box {
			display: inline;
		}
		</style>
		<?php
		if ( !get_the_ID() ) {
			echo "<div><p>".__("Related entries may be displayed once you save your entry",'narpp').".</p></div>";
		} else {
			wp_nonce_field( 'yarpp_display', 'yarpp_display-nonce', false );
			echo '<div id="yarpp-related-posts"><img src="' . esc_url( admin_url( 'images/wpspin_light.gif' ) ) . '" alt="" /></div>';
		}
	}
	
	// @since 3.3: default metaboxes to show:
	public function default_hidden_meta_boxes($hidden, $screen) {
		if ($screen->id === 'settings_page_yarpp') {
			$hidden = $this->core->default_hidden_metaboxes;
        }
		return $hidden;
	}
	
	// @since 4: UI to copy templates
	public function can_copy_templates() {
		$theme_dir = get_stylesheet_directory();
		// If we can't write to the theme, return false
		if (!is_dir($theme_dir) || !is_writable($theme_dir)) return false;
		
		require_once(ABSPATH.'wp-admin/includes/file.php');
		WP_Filesystem(false, get_stylesheet_directory());
		global $wp_filesystem;			
		// direct method is the only method that I've tested so far
		return $wp_filesystem->method === 'direct';
	}
	
	public function copy_templates() {
		$templates_dir = trailingslashit(trailingslashit(YARPP_DIR).'yarpp-templates');
		
		require_once(ABSPATH.'wp-admin/includes/file.php');
		WP_Filesystem(false, get_stylesheet_directory());
		global $wp_filesystem;
		if ( $wp_filesystem->method !== 'direct') return false;
		
		return copy_dir($templates_dir, get_stylesheet_directory(), array('.svn'));
	}
	
	/*
	 * AJAX SERVICES
	 */

	public function ajax_display_exclude_terms() {
		check_ajax_referer('yarpp_display_exclude_terms');
		
		if (!isset($_REQUEST['taxonomy'])) return;
		
		$taxonomy = (string) $_REQUEST['taxonomy'];
		
		header("HTTP/1.1 200");
		header("Content-Type: text/html; charset=UTF-8");
		
		$exclude_tt_ids = wp_parse_id_list($this->core->get_option('exclude'));
		$exclude_term_ids = $this->get_term_ids_from_tt_ids( $taxonomy, $exclude_tt_ids );
//		if ('category' === $taxonomy) $exclude .= ','.get_option('default_category');

		$terms = get_terms($taxonomy, array(
			'exclude' => $exclude_term_ids,
			'hide_empty' => false,
			'hierarchical' => false,
			'number' => 100,
			'offset' => $_REQUEST['offset']
		));
		
		if ( !count($terms) ) {
			echo ':('; // no more :(
			exit;
		}
		
		foreach ($terms as $term) {
			echo "<span><input type='checkbox' name='exclude["
                 . esc_attr($term->term_taxonomy_id)
                 . "]' id='exclude_"
                 . esc_attr($term->term_taxonomy_id)
                 . "' value='true' /> <label for='exclude_"
                 . esc_attr($term->term_taxonomy_id)
                 . "'>"
                 . esc_html($term->name)
                 . "</label></span> ";
		}
		exit;
	}
	
	public function get_term_ids_from_tt_ids( $taxonomy, $tt_ids ) {
		global $wpdb;
		$tt_ids = wp_parse_id_list($tt_ids);
		if ( empty($tt_ids) )
			return array();
		return $wpdb->get_col("select term_id from $wpdb->term_taxonomy where taxonomy = '{$taxonomy}' and term_taxonomy_id in (" . join(',', $tt_ids) . ")");
	}
	
	public function ajax_display() {
		check_ajax_referer('yarpp_display');

		if (!isset($_REQUEST['ID'])) return;

		$args = array(
			'post_type' => array('post'),
			'domain' => isset($_REQUEST['domain']) ? $_REQUEST['domain'] : 'website'
		);

		if ($this->core->get_option('cross_relate')) $args['post_type'] = $this->core->get_post_types();
			
		$return = $this->core->display_related(absint($_REQUEST['ID']), $args, false);

        header("HTTP/1.1 200");
        header("Content-Type: text/html; charset=UTF-8");
		echo $return;

		die();
	}

	public function ajax_display_demo() {
		check_ajax_referer('yarpp_display_demo');

		header("HTTP/1.1 200");
		header("Content-Type: text/html; charset=UTF-8");
	
		$args = array(
			'post_type' => array('post'),
			'domain'    => (isset($_REQUEST['domain'])) ? $_REQUEST['domain'] : 'website'
		);
			
		$return = $this->core->display_demo_related($args, false);
		echo preg_replace("/[\n\r]/",'',nl2br(htmlspecialchars($return)));
		exit;
	}

	public function ajax_set_display_code() {
		check_ajax_referer( 'yarpp_set_display_code' );

		header("HTTP/1.1 200");
		header("Content-Type: text; charset=UTF-8");
		
		$data = $this->core->set_option( 'display_code', isset($_REQUEST['checked']) );
		echo 'ok';
		die();
	}
}
