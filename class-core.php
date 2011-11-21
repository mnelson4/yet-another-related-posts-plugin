<?php

// new in 3.4: put everything YARPP into an object, expected to be a singleton global $yarpp
class YARPP {

	public $debug = false;
	
	public $cache;
	public $admin;
	private $storage_class;
	
	public $myisam = true;
	
	public $templates = array();
	
	// here's a list of all the options YARPP uses (except version), as well as their default values, sans the yarpp_ prefix, split up into binary options and value options. These arrays are used in updating settings (options.php) and other tasks.
	public $default_options = array();
	// These are options which, when updated, will trigger a clearing of the cache
	public $clear_cache_options = array( 'show_pass_post', 'recent_only', 'threshold', 'weight');

	function __construct() {
		$this->load_default_options();

		// register text domain
		load_plugin_textdomain( 'yarpp', false, dirname(plugin_basename(__FILE__)) . '/lang' );

		// load cache object
		require_once(YARPP_DIR . '/class-cache.php');
		require_once(YARPP_DIR . '/cache-' . YARPP_CACHE_TYPE . '.php');
		$this->storage_class = $yarpp_storage_class;
		$this->cache = new $this->storage_class( $this );

		register_activation_hook( __FILE__, array($this, 'activate') );
		
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
			$this->debug = true;

		// new in 3.4: only load UI if we're in the admin
		if ( is_admin() ) {
			require_once(YARPP_DIR . '/class-admin.php');
			$this->admin = new YARPP_Admin( $this );
		}
	}
		
	/*
	 * OPTIONS
	 */
	
	private function load_default_options() {
		$this->default_options = array(
			'threshold' => 5,
			'limit' => 5,
			'template_file' => '', // new in 2.2
			'excerpt_length' => 10,
			'recent_number' => 12,
			'recent_units' => 'month',
			'before_title' => '<li>',
			'after_title' => '</li>',
			'before_post' => ' <small>',
			'after_post' => '</small>',
			'before_related' => '<p>'.__('Related posts:','yarpp').'</p><ol>',
			'after_related' => '</ol>',
			'no_results' => '<p>'.__('No related posts.','yarpp').'</p>',
			'order' => 'score DESC',
			'rss_limit' => 3,
			'rss_template_file' => '', // new in 2.2
			'rss_excerpt_length' => 10,
			'rss_before_title' => '<li>',
			'rss_after_title' => '</li>',
			'rss_before_post' => ' <small>',
			'rss_after_post' => '</small>',
			'rss_before_related' => '<p>'.__('Related posts:','yarpp').'</p><ol>',
			'rss_after_related' => '</ol>',
			'rss_no_results' => '<p>'.__('No related posts.','yarpp').'</p>',
			'rss_order' => 'score DESC',
			'past_only' => true,
			'show_excerpt' => false,
			'recent_only' => false, // new in 3.0
			'use_template' => false, // new in 2.2
			'rss_show_excerpt' => false,
			'rss_use_template' => false, // new in 2.2
			'show_pass_post' => false,
			'cross_relate' => false,
			'auto_display' => true,
			'rss_display' => false, // changed default in 3.1.7
			'rss_excerpt_display' => true,
			'promote_yarpp' => false,
			'rss_promote_yarpp' => false,
			'myisam_override' => false,
			'exclude' => array(), // conslidated YARPP 3.4
			'weight' => array( // consolidated in YARPP 3.4
				'title' => 2,
				'body' => 2,
				'tax' => array(
					'category' => 2, // changed default in 3.4
					'post_tag' => 2
				)
			)
		);
	}
	
	function set_option( $options, $value = null ) {
		$current_options = $this->get_option();
	
		// we can call yarpp_set_option(key,value) if we like:
		if ( !is_array($options) && isset($value) )
			$options = array( $options => $value );
	
		$new_options = array_merge( $current_options, $options );
	
		// new in 3.1: clear cache when updating certain settings.
		$new_options_which_require_flush = array_intersect( array_keys( array_diff_assoc($options, $current_options) ), $this->clear_cache_options );
		if ( count($new_options_which_require_flush) ||
			( isset($options['exclude']) && $options['exclude'] != $current_options['exclude'] ) ||
			( isset($options['weight']) && $options['weight'] != $current_options['weight'] ) )
			$this->cache->flush();		
	
		update_option( 'yarpp', $new_options );
	}
	
	// 3.4b8: $option can be a path, of the query_str variety, i.e. "option[suboption][subsuboption]"
	function get_option( $option = null ) {
		$options = get_option( 'yarpp' );
		// ensure defaults if not set:
		$options = array_merge( $this->default_options, $options );
		// some extra work is required for arrays:
		foreach ( $this->default_options as $key => $default ) {
			if ( !is_array($default) )
				continue;
			$options[$key] = array_merge( $this->default_options[$key], $options[$key] );
		}
		if ( !isset($options['weight']['tax']) )
			$options['weight']['tax'] = $this->default_options['weight']['tax'];
	
		if ( is_null( $option ) )
			return $options;
	
		$optionpath = array();
		$parsed_option = array();
		wp_parse_str($option, $parsed_option);
		$optionpath = $this->array_flatten($parsed_option);
		
		$current = $options;
		foreach ( $optionpath as $optionpart ) {
			if ( !is_array($current) || !isset($current[$optionpart]) )
				return null;
			$current = $current[$optionpart];
		}
		return $current;
	}
	
	private function array_flatten($array, $given = array()) {
		foreach ($array as $key => $val) {
			$given[] = $key;
			if ( is_array($val) )
				$given = $this->array_flatten($val, $given);
		}
		return $given;
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
		global $wpdb;
	
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
		if ( $last_version && version_compare('3.4b8', $last_version) > 0 )
			$this->upgrade_3_4b8();
	
		$this->cache->upgrade($last_version);
	
		$this->version_info(true);
	
		update_option('yarpp_version',YARPP_VERSION);
	}
	
	function upgrade_3_4b2() {
		global $wpdb;
	
		$yarpp_3_3_options = array(
			'threshold' => 5,
			'limit' => 5,
			'template_file' => '', // new in 2.2
			'excerpt_length' => 10,
			'recent_number' => 12,
			'recent_units' => 'month',
			'before_title' => '<li>',
			'after_title' => '</li>',
			'before_post' => ' <small>',
			'after_post' => '</small>',
			'before_related' => '<p>'.__('Related posts:','yarpp').'</p><ol>',
			'after_related' => '</ol>',
			'no_results' => '<p>'.__('No related posts.','yarpp').'</p>',
			'order' => 'score DESC',
			'rss_limit' => 3,
			'rss_template_file' => '', // new in 2.2
			'rss_excerpt_length' => 10,
			'rss_before_title' => '<li>',
			'rss_after_title' => '</li>',
			'rss_before_post' => ' <small>',
			'rss_after_post' => '</small>',
			'rss_before_related' => '<p>'.__('Related posts:','yarpp').'</p><ol>',
			'rss_after_related' => '</ol>',
			'rss_no_results' => '<p>'.__('No related posts.','yarpp').'</p>',
			'rss_order' => 'score DESC',
			'title' => '2',
			'body' => '2',
			'categories' => '1', // changed default in 3.3
			'tags' => '2',
			'distags' => '',
			'discats' => '',
			'past_only' => true,
			'show_excerpt' => false,
			'recent_only' => false, // new in 3.0
			'use_template' => false, // new in 2.2
			'rss_show_excerpt' => false,
			'rss_use_template' => false, // new in 2.2
			'show_pass_post' => false,
			'cross_relate' => false,
			'auto_display' => true,
			'rss_display' => false, // changed default in 3.1.7
			'rss_excerpt_display' => true,
			'promote_yarpp' => false,
			'rss_promote_yarpp' => false);
	
		$yarpp_options = array();
		foreach ( $yarpp_3_3_options as $key => $default ) {
			$value = get_option( "yarpp_$key", null );
			if ( is_null($value) )
				continue;

			if ( is_bool($default) ) {
				$yarpp_options[$key] = (boolean) $value;
				continue;
			}

			// value options used to be stored with a bajillion slashes...
			$value = stripslashes(stripslashes($value));
			// value options used to be stored with a blank space at the end... don't ask.
			$value = rtrim($value, ' ');
			
			if ( is_int($default) )
				$yarpp_options[$key] = absint($value);
			else
				$yarpp_options[$key] = $value;
		}
		
		// add the options directly first, then call set_option which will ensure defaults,
		// in case any new options have been added.
		update_option( 'yarpp', $yarpp_options );
		$this->set_option( $yarpp_options );
		
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
		$options = $this->get_option();
		$options['exclude'] = array(
			'post_tag' => $options['distags'],
			'category' => $options['discats']
		);
		unset( $options['distags'] );
		unset( $options['discats'] );
		update_option( 'yarpp', $options );
	}
	
	function upgrade_3_4b8() {
		$options = $this->get_option();
		$options['weight'] = array(
			'title' => $options['title'],
			'body' => $options['body'],
			'tax' => array(
				'post_tag' => $options['tags'],
				'category' => $options['categories'],
			)
		);
		unset( $options['title'] );
		unset( $options['body'] );
		unset( $options['tags'] );
		unset( $options['categories'] );
		update_option( 'yarpp', $options );
	}
	
	// @todo: custom post type support
	function get_post_types() {
		return array('post', 'page');
	}
	
	function get_taxonomies() {
		$taxonomies = get_taxonomies(array(), 'objects');
		return array_filter( $taxonomies, array($this, 'taxonomy_filter') );
	}
	
	private function taxonomy_filter( $taxonomy ) {
		// if yarpp_support is set and false, or if show_ui is false, skip it
		if ( (isset($taxonomy->yarpp_support) && !$taxonomy->yarpp_support) ||
			 !$taxonomy->show_ui )
			return false;
		if ( !count(array_intersect( $taxonomy->object_type, $this->get_post_types() )) )
			return false;
		return true;
	}
	
	/*
	 * CORE LOOKUP + DISPLAY FUNCTIONS
	 */
	 
	/* new in 2.1! the domain argument refers to {website,widget,rss}, though widget is not used yet. */
	/* new in 3.0! new query-based approach: EXTREMELY HACKY! */
	function related($type,$args,$echo = true,$reference_ID=false,$domain = 'website') {
		global $wp_query, $pagenow;
	
		$this->upgrade_check();
	
		if ($domain == 'demo_web' || $domain == 'demo_rss') {
			if ($this->cache->demo_time) // if we're already in a YARPP loop, stop now.
				return false;
		} else {
			if ($this->cache->is_yarpp_time()) // if we're already in a YARPP loop, stop now.
				return false;
			if ( !$reference_ID )
				$reference_ID = get_the_ID();
	
			$cache_status = $this->cache->enforce($reference_ID);
			
			// If cache status is YARPP_DONT_RUN, end here without returning or echoing anything.
			if ( YARPP_DONT_RUN == $cache_status )
				return;
		}
	
		get_currentuserinfo();
	
		// set the "domain prefix", used for all the preferences.
		if ($domain == 'rss' || $domain == 'demo_rss')
			$domainprefix = 'rss_';
		else
			$domainprefix = '';
		// get options
		// note the 2.1 change... the options array changed from what you might call a "list" to a "hash"... this changes the structure of the $args to something which is, in the long term, much more useful
		$options = array(
			'cross_relate'=>"cross_relate",
			'limit'=>"${domainprefix}limit",
			'use_template'=>"${domainprefix}use_template",
			'order'=>"${domainprefix}order",
			'template_file'=>"${domainprefix}template_file",
			'promote_yarpp'=>"${domainprefix}promote_yarpp");
		$optvals = array();
		foreach (array_keys($options) as $option) {
			if (isset($args[$option])) {
				$optvals[$option] = $args[$option];
			} else {
				$optvals[$option] = $this->get_option($options[$option]);
			}
		}
		extract($optvals);
		// override $type for cross_relate:
		if ($cross_relate)
			$type = array('post','page');
	
		if ($domain == 'demo_web' || $domain == 'demo_rss') {
			// It's DEMO TIME!
			$this->cache->demo_time = true;
			if ($domain == 'demo_web')
				$this->cache->demo_limit = $this->get_option('limit');
			else
				$this->cache->demo_limit = $this->get_option('rss_limit');
		} else if ( YARPP_NO_RELATED == $cache_status ) {
			// There are no results, so no yarpp time for us... :'(
		} else {
			// Get ready for YARPP TIME!
			$this->cache->begin_yarpp_time($reference_ID);
		}
	
		// so we can return to normal later
		$current_query = $wp_query;
		$current_pagenow = $pagenow;
	
		$output = '';
		$wp_query = new WP_Query();
		$orders = explode(' ',$order);
		if ( 'demo_web' == $domain || 'demo_rss' == $domain ) {
			$wp_query->query('');
		} else if ( YARPP_NO_RELATED == $cache_status ) {
			// If there are no related posts, get no query
		} else {
			$wp_query->query(array(
				'p' => $reference_ID,
				'orderby' => $orders[0],
				'order' => $orders[1],
				'showposts' => $limit,
				'post_type' => $type
			));
		}
	
		$wp_query->in_the_loop = true;
		$wp_query->is_feed = $current_query->is_feed;
		// make sure we get the right is_single value
		// (see http://wordpress.org/support/topic/288230)
		$wp_query->is_single = false;
		$related_query = $wp_query; // backwards compatibility
	
		if ($domain == 'metabox') {
			include(YARPP_DIR.'/template-metabox.php');
		} elseif ($use_template and file_exists(STYLESHEETPATH . '/' . $template_file) and $template_file != '') {
			ob_start();
			include(STYLESHEETPATH . '/' . $template_file);
			$output = ob_get_contents();
			ob_end_clean();
		} elseif ($domain == 'widget') {
			include(YARPP_DIR.'/template-widget.php');
		} else {
			include(YARPP_DIR.'/template-builtin.php');
		}
	
		if ( 'demo_web' == $domain || 'demo_rss' == $domain ) {
			$this->cache->demo_time = false;
		} else if ( YARPP_NO_RELATED == $cache_status ) {
			// Uh, do nothing. Stay very still.
		} else {
			$this->cache->end_yarpp_time(); // YARPP time is over... :(
		}
	
		// restore the older wp_query.
		$wp_query = $current_query; unset($current_query); unset($related_query);
		wp_reset_postdata();
		$pagenow = $current_pagenow; unset($current_pagenow);
	
		if ($promote_yarpp and $domain != 'metabox')
			$output .= "\n<p>".sprintf(__("Related posts brought to you by <a href='%s'>Yet Another Related Posts Plugin</a>.",'yarpp'), 'http://yarpp.org')."</p>";
	
		if ($echo)
			echo $output;
		else
			return ((!empty($output))?"\n\n":'').$output;
	}
	
	function related_exist($type,$args,$reference_ID=false) {
		global $post;
	
		$this->upgrade_check();
	
		if (is_object($post) && !$reference_ID)
			$reference_ID = $post->ID;
	
		if ($this->cache->is_yarpp_time()) // if we're already in a YARPP loop, stop now.
			return false;
	
		if ($this->get_option('cross_relate'))
			$type = array('post','page');
	
		$cache_status = $this->cache->enforce($reference_ID);
	
		if ( YARPP_NO_RELATED == $cache_status )
			return false;
	
		$this->cache->begin_yarpp_time($reference_ID); // get ready for YARPP TIME!
		$related_query = new WP_Query();
		$related_query->query(array('p'=>$reference_ID,'showposts'=>1,'post_type'=>$type));
		$return = $related_query->have_posts();
		unset($related_query);
		$this->cache->end_yarpp_time(); // YARPP time is over. :(
	
		return $return;
	}
	
	/*
	 * DEFAULT CONTENT FILTERS
	 */
	 
	function the_content($content) {
		global $post;

		if (is_feed())
			return $this->the_content_rss($content);
	
		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( $this->get_option('cross_relate') )
			$type = array('post','page');
	
		if ( $this->get_option('auto_display') && is_single() )
			return $content . $this->related($type,array(),false,false,'website');
		else
			return $content;
	}
	
	function the_content_rss($content) {
		global $post;
	
		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( $this->get_option('cross_relate') )
			$type = array('post','page');
	
		if ( $this->get_option('rss_display') )
			return $content . $this->related($type,array(),false,false,'rss');
		else
			return $content;
	}
	
	function the_excerpt_rss($content) {
		global $post;

		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if ( $this->get_option('cross_relate') )
			$type = array('post','page');
	
		if ( $this->get_option('rss_excerpt_display') && $this->get_option('rss_display') )
			return $content . clean_pre($this->related($type,array(),false,false,'rss'));
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
}