<?php

// new in 3.4: put everything YARPP into an object, expected to be a singleton global $yarpp
class YARPP {

	public $debug = false;
	
	public $cache;
	public $admin;
	private $storage_class;

	function __construct() {
		// register text domain
		load_plugin_textdomain( 'yarpp', false, dirname(plugin_basename(__FILE__)) . '/lang' );

		// load cache object
		require_once(YARPP_DIR . '/class-cache.php');
		require_once(YARPP_DIR . '/cache-' . YARPP_CACHE_TYPE . '.php');
		$this->storage_class = $yarpp_storage_class;
		$this->cache = new $this->storage_class( $this );

		register_activation_hook( __FILE__, array($this, 'activate') );		
		add_action( 'admin_init', array($this, 'admin_init') );
		
		// update cache on save
		add_action( 'save_post', array($this->cache, 'save_post') );
		// new in 3.2: update cache on delete
		add_action( 'delete_post', array($this->cache, 'delete_post') );
		// new in 3.2.1: handle post_status transitions
		add_action( 'transition_post_status', array($this->cache, 'transition_post_status'), 10, 3);

		// automatic display hooks:
		add_filter( 'the_content', array( $this, 'the_content' ), 1200 );
		add_filter( 'the_content_rss', array( $this, 'the_content_rss' ), 600 );
		add_filter( 'the_excerpt_rss', array( $this, 'the_excerpt_rss' ), 600 );

		if ( isset($_REQUEST['yarpp_debug']) )
			$yarpp->debug = true;

		// new in 3.4: only load UI if we're in the admin
		if ( is_admin() ) {
			require_once(YARPP_DIR . '/class-admin.php');
			$this->admin = new YARPP_Admin( $this );
		}
	}
	
	function admin_init() {
		// Register AJAX services
		if ( defined('DOING_AJAX') && DOING_AJAX ) {
			add_action( 'wp_ajax_yarpp_display_exclude_terms', array( $this, 'ajax_display_exclude_terms' ) );
			add_action( 'wp_ajax_yarpp_display_demo', array( $this, 'ajax_display_demo' ) );
		}
	}

	/*
	 * INFRASTRUCTURE
	 */

	function enabled() {
		global $wpdb;
		if ( $this->cache->is_enabled() === false )
			return false;
		$indexdata = $wpdb->get_results("show index from $wpdb->posts");
		foreach ($indexdata as $index) {
			if ($index->Key_name == 'yarpp_title')
				return true;
		}
		return false;
	}
	
	function activate() {
		global $yarpp_version, $wpdb;
	
		$wpdb->get_results("show index from $wpdb->posts where Key_name='yarpp_title'");
		if (!$wpdb->num_rows)
			$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_title` ( `post_title` )");
	
		$wpdb->get_results("show index from $wpdb->posts where Key_name='yarpp_content'");
		if (!$wpdb->num_rows)
			$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_content` ( `post_content` )");
		
		if ( !$this->enabled() ) {
			// If we are still not enabled, run the cache abstraction's setup method.
			$this->cache->setup();
			// If we're still not enabled, give up.
			if ( !$this->enabled() )
				return 0;
		}
		
		if ( !get_option('yarpp_version') ) {
			add_option( 'yarpp_version', YARPP_VERSION );
			$this->version_info(true);
		} else {
			$this->upgrade_check();
		}
	
		return 1;
	}
	
	function myisam_check() {
		global $wpdb;
		$tables = $wpdb->get_results("show table status like '{$wpdb->posts}'");
		foreach ($tables as $table) {
			if ($table->Engine == 'MyISAM') return true;
			else return $table->Engine;
		}
		return 'UNKNOWN';
	}
	
	function upgrade_check() {
		$last_version = get_option( 'yarpp_version' );
		if (version_compare(YARPP_VERSION, $last_version) === 0)
			return;
	
		if ( $last_version && version_compare('3.4b2', $last_version) > 0 )
			$this->upgrade_3_4b2();
		if ( $last_version && version_compare('3.4b5', $last_version) > 0 )
			$this->upgrade_3_4b5();
	
		$this->cache->upgrade($last_version);
	
		$this->version_info(true);
	
		update_option('yarpp_version',YARPP_VERSION);
	}
	
	function upgrade_3_4b2() {
		global $wpdb, $yarpp_value_options, $yarpp_binary_options;
	
		$yarpp_options = array();
		foreach ( $yarpp_value_options as $key => $default ) {
			$value = get_option( "yarpp_$key", null );
			if ( is_null($value) )
				continue;
	
			// value options used to be stored with a bajillion slashes...
			$value = stripslashes(stripslashes($value));
			// value options used to be stored with a blank space at the end... don't ask.
			$value = rtrim($value, ' ');
			
			if ( is_int($default) )
				$yarpp_options[$key] = absint($value);
			else
				$yarpp_options[$key] = $value;
		}
		foreach ( $yarpp_binary_options as $key => $default ) {
			$value = get_option( "yarpp_$key", null );
			if ( is_null($value) )
				continue;
			$yarpp_options[$key] = (boolean) $value;
		}
		
		// add the options directly first, then call set_option which will ensure defaults,
		// in case any new options have been added.
		update_option( 'yarpp', $yarpp_options );
		yarpp_set_option( $yarpp_options );
		
		$option_keys = array_keys( $yarpp_options );
		// append some keys for options which are long deprecated:
		$option_keys[] = 'ad_hoc_caching';
		$option_keys[] = 'excerpt_len';
		$option_keys[] = 'show_score';
		if ( count($option_keys) ) {
			$in = "('yarpp_" . join("', 'yarpp_", $option_keys) . "')";
			$wpdb->query("delete from {$wpdb->options} where option_name in {$in}");
		}
	}
	
	function upgrade_3_4b5() {
		$options = yarpp_get_option();
		$options['exclude'] = array(
			'post_tag' => $options['distags'],
			'category' => $options['discats']
		);
		unset( $options['distags'] );
		unset( $options['discats'] );
		update_option( 'yarpp', $options );
	}
	
	/*
	 * DEFAULT CONTENT FILTERS
	 */
	 
	function the_content($content) {
		global $post;
	
		if (is_feed())
			return $this->the_content_rss($content);
	
		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( yarpp_get_option('cross_relate') )
			$type = array('post','page');
	
		if ( yarpp_get_option('auto_display') && is_single() )
			return $content . yarpp_related($type,array(),false,false,'website');
		else
			return $content;
	}
	
	function the_content_rss($content) {
		global $post;
	
		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( yarpp_get_option('cross_relate') )
			$type = array('post','page');
	
		if ( yarpp_get_option('rss_display') )
			return $content . yarpp_related($type,array(),false,false,'rss');
		else
			return $content;
	}
	
	function the_excerpt_rss($content) {
		global $post;
	
		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( yarpp_get_option('cross_relate') )
			$type = array('post','page');
	
		if ( yarpp_get_option('rss_excerpt_display') && yarpp_get_option('rss_display') )
			return $content . clean_pre(yarpp_related($type,array(),false,false,'rss'));
		else
			return $content;
	}
	
	/*
	 * UTILS
	 */
	
	// new in 3.3: use PHP serialized format instead of JSON
	function version_info( $enforce_cache = false ) {
		if (false === ($result = get_transient('yarpp_version_info')) || $enforce_cache) {
			$version = YARPP_VERSION;
			$remote = wp_remote_post("http://mitcho.com/code/yarpp/checkversion.php?format=php&version={$version}");
			
			if (is_wp_error($remote))
				return false;
			
			$result = unserialize($remote['body']);
			set_transient('yarpp_version_info', $result, 60*60*12);
		}
		return $result;
	}

	/*
	 * AJAX SERVICES
	 */

	function ajax_display_exclude_terms() {
		if ( !isset($_REQUEST['taxonomy']) )
			return;
		
		$taxonomy = (string) $_REQUEST['taxonomy'];
		
		header("HTTP/1.1 200");
		header("Content-Type: text/html; charset=UTF-8");
		
		$exclude = yarpp_get_option('exclude');
		if ( !isset($exclude[$taxonomy]) )
			$exclude[$taxonomy] = array();
		$terms = get_terms($taxonomy, array(
			'exclude' => $exclude[$taxonomy],
			'number' => 100,
			'offset' => $_REQUEST['offset']
		));
		
		if ( !count($terms) ) {
			echo ':('; // no more :(
			exit;
		}
		
		foreach ($terms as $term) {
			echo "<input type='checkbox' name='exclude[$taxonomy][$term->term_id]' value='true' /> <label>" . esc_html($term->name) . "</label> ";
			//for='exclude[$taxonomy][$cat->term_id]' it's not HTML. :(
		}
		exit;
	}
	
	function ajax_display_demo() {
		header("HTTP/1.1 200");
		header("Content-Type: text/html; charset=UTF-8");
	
		$domain = 'demo_web';
		if ( isset($_REQUEST['domain']) )
			$domain = $_REQUEST['domain'];
	
		$return = yarpp_related(array('post'), array(), false, false, $domain);
		echo ereg_replace("[\n\r]",'',nl2br(htmlspecialchars($return)));
		exit;
	}
}