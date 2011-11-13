<?php

// new in 3.4: put everything YARPP into an object, expected to be a singleton global $yarpp
class YARPP {

	public $debug = false;
	
	public $cache;
	private $storage_class;

	function __construct() {
		require_once(YARPP_DIR . '/cache-' . YARPP_CACHE_TYPE . '.php');
		$this->storage_class = $yarpp_storage_class;
		$this->cache = new $this->storage_class( $this );
		
		// update cache on save
		add_action('save_post', array($this->cache, 'save_post') );
		// new in 3.2: update cache on delete
		add_action('delete_post', array($this->cache, 'delete_post') );
		// new in 3.2.1: handle post_status transitions
		add_action('transition_post_status', array($this->cache, 'transition_post_status'), 10, 3);
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

abstract class YARPP_Cache {

	public $core;

	function __construct( &$core ) {
		$this->core = &$core;
		$this->name = __($this->name, 'yarpp');
	}
	
	// Note: return value changed in 3.4
	// return YARPP_NO_RELATED | YARPP_RELATED | YARPP_DONT_RUN | false if no good input
	function enforce($reference_ID, $force = false) {
		if ( !$reference_ID = absint($reference_ID) )
			return false;
	
		$status = $this->is_cached($reference_ID);
		$status = apply_filters( 'yarpp_cache_enforce_status', $status, $reference_ID );
	
		// There's a stop signal:
		if ( YARPP_DONT_RUN === $status )
			return YARPP_DONT_RUN;
	
		// If not cached, process now:
		if ( YARPP_NOT_CACHED == $status || $force ) {
			$status = $this->update($reference_ID);
			// if still not cached, there's a problem, but for the time being return NO RELATED
			if ( YARPP_NOT_CACHED === $status )
				return YARPP_NO_RELATED;
		}
	
		// There are no related posts
		if ( YARPP_NO_RELATED === $status )
			return YARPP_NO_RELATED;
	
		// There are results
		return YARPP_RELATED;
	}
	
	/*
	 * POST STATUS INTERACTIONS
	 */
	
	function save_post($post_ID, $force=true) {
		global $wpdb;
	
		// new in 3.2: don't compute cache during import
		if ( defined( 'WP_IMPORTING' ) )
			return;
	
		$sql = "select post_parent from $wpdb->posts where ID='$post_ID'";
		$parent_ID = $wpdb->get_var($sql);
	
		if ( $parent_ID != $post_ID && $parent_ID )
			$post_ID = $parent_ID;
	
		$this->enforce((int) $post_ID, $force);
	}
	
	// Clear the cache for this entry and for all posts which are "related" to it.
	// New in 3.2: This is called when a post is deleted.
	function delete_post($post_ID) {
		// Clear the cache for this post.
		$this->clear($post_ID);
	
		// Find all "peers" which list this post as a related post.
		$peers = $this->related(null, $post_ID);
		// Clear the peers' caches.
		$this->clear($peers);
	}
	
	// New in 3.2.1: handle various post_status transitions
	function transition_post_status($new_status, $old_status, $post) {
		switch ($new_status) {
			case "draft":
				$this->delete_post($post->ID);
				break;
			case "publish":
				// find everything which is related to this post, and clear them, so that this
				// post might show up as related to them.
				$related = $this->related($post->ID, null);
				$this->clear($related);
		}
	}
	
	/*
	 * KEYWORDS
	 */
	
	public function title_keywords($ID,$max = 20) {
		return $this->extract_keywords(get_the_title($ID),$max);
	}
	
	public function body_keywords( $ID, $max = 20 ) {
		$post = get_post( $ID );
		if ( empty($post) )
			return '';
		$content = $this->apply_filters_if_white( 'the_content', $post->post_content );
		return $this->extract_keywords( $content, $max );
	}
	
	private function extract_keywords($html, $max = 20) {
	
		$lang = 'en_US';
		if ( defined('WPLANG') ) {
			$lang = substr(WPLANG, 0, 2);
			switch ( $lang ) {
				case 'de':
					$lang = 'de_DE';
				case 'it':
					$lang = 'it_IT';
				case 'pl':
					$lang = 'pl_PL';
				case 'bg':
					$lang = 'bg_BG';
				case 'fr':
					$lang = 'fr_FR';
				case 'cs':
					$lang = 'cs_CZ';
				case 'nl':
					$lang = 'nl_NL';
			}
		}
	
		$words_file = YARPP_DIR . '/lang/words-' . $lang . '.php';
		if ( file_exists($words_file) )
			include( $words_file );
		if ( !isset($overusedwords) )
			$overusedwords = array();
	
		// strip tags and html entities
		$text = preg_replace('/&(#x[0-9a-f]+|#[0-9]+|[a-zA-Z]+);/', '', strip_tags($html) );
	
		// 3.2.2: ignore soft hyphens
		// Requires PHP 5: http://bugs.php.net/bug.php?id=25670
		$softhyphen = html_entity_decode('&#173;',ENT_NOQUOTES,'UTF-8');
		$text = str_replace($softhyphen, '', $text);
	
		$charset = get_option('blog_charset');
		if ( function_exists('mb_split') && !empty($charset) ) {
			mb_regex_encoding($charset);
			$wordlist = mb_split('\s*\W+\s*', mb_strtolower($text, $charset));
		} else
			$wordlist = preg_split('%\s*\W+\s*%', strtolower($text));
	
		// Build an array of the unique words and number of times they occur.
		$tokens = array_count_values($wordlist);
	
		// Remove the stop words from the list.
		$overusedwords = apply_filters( 'yarpp_keywords_overused_words', $overusedwords );
		if ( is_array($overusedwords) ) {
			foreach ($overusedwords as $word) {
				 unset($tokens[$word]);
			}
		}
		// Remove words which are only a letter
		$mb_strlen_exists = function_exists('mb_strlen');
		foreach (array_keys($tokens) as $word) {
			if ($mb_strlen_exists)
				if (mb_strlen($word) < 2) unset($tokens[$word]);
			else
				if (strlen($word) < 2) unset($tokens[$word]);
		}
	
		arsort($tokens, SORT_NUMERIC);
	
		$types = array_keys($tokens);
	
		if (count($types) > $max)
			$types = array_slice($types, 0, $max);
		return implode(' ', $types);
	}
	
	/* new in 2.0! apply_filters_if_white (previously apply_filters_without) now has a blacklist.
	 * It can be modified via the yarpp_blacklist and yarpp_blackmethods filters.
	 */
	/* blacklisted so far:
		- diggZ-Et
		- reddZ-Et
		- dzoneZ-Et
		- WP-Syntax
		- Viper's Video Quicktags
		- WP-CodeBox
		- WP shortcodes
		- WP Greet Box
		//- Tweet This - could not reproduce problem.
	*/
	function white( $filter ) {
		static $blacklist, $blackmethods;
	
		if ( is_null($blacklist) || is_null($blackmethods) ) {
			$yarpp_blacklist = array('yarpp_default', 'diggZEt_AddBut', 'reddZEt_AddBut', 'dzoneZEt_AddBut', 'wp_syntax_before_filter', 'wp_syntax_after_filter', 'wp_codebox_before_filter', 'wp_codebox_after_filter', 'do_shortcode');//,'insert_tweet_this'
			$yarpp_blackmethods = array('addinlinejs', 'replacebbcode', 'filter_content');
		
			$blacklist = (array) apply_filters( 'yarpp_blacklist', $yarpp_blacklist );
			$blackmethods = (array) apply_filters( 'yarpp_blackmethods', $yarpp_blackmethods );
		}
		
		if ( is_array($filter) && in_array( $filter[1], $blackmethods ) )
			return false;
		return !in_array( $filter, $blacklist );
	}
	
	/* FYI, apply_filters_if_white was used here to avoid a loop in apply_filters('the_content') > yarpp_default() > yarpp_related() > current_post_keywords() > apply_filters('the_content').*/
	function apply_filters_if_white($tag, $value) {
		global $wp_filter, $merged_filters, $wp_current_filter;
	
		$args = array();
	
		// Do 'all' actions first
		if ( isset($wp_filter['all']) ) {
			$wp_current_filter[] = $tag;
			$args = func_get_args();
			_wp_call_all_hook($args);
		}
	
		if ( !isset($wp_filter[$tag]) ) {
			if ( isset($wp_filter['all']) )
				array_pop($wp_current_filter);
			return $value;
		}
	
		if ( !isset($wp_filter['all']) )
			$wp_current_filter[] = $tag;
	
		// Sort
		if ( !isset( $merged_filters[ $tag ] ) ) {
			ksort($wp_filter[$tag]);
			$merged_filters[ $tag ] = true;
		}
	
		reset( $wp_filter[ $tag ] );
	
		if ( empty($args) )
			$args = func_get_args();
	
		do {
			foreach( (array) current($wp_filter[$tag]) as $the_ )
				if ( !is_null($the_['function'])
				and $this->white($the_['function'])){ // HACK
					$args[1] = $value;
					$value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
				}
	
		} while ( next($wp_filter[$tag]) !== false );
	
		array_pop( $wp_current_filter );
	
		return $value;
	}
}

require_once(YARPP_DIR.'/magic.php');
require_once(YARPP_DIR.'/services.php');

if ( !defined('WP_CONTENT_URL') )
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

global $yarpp_value_options, $yarpp_binary_options, $yarpp_clear_cache_options;
// here's a list of all the options YARPP uses (except version), as well as their default values, sans the yarpp_ prefix, split up into binary options and value options. These arrays are used in updating settings (options.php) and other tasks.
$yarpp_value_options = array(
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
	'title' => 2,
	'body' => 2,
	'categories' => 1, // changed default in 3.3
	'tags' => 2);
$yarpp_binary_options = array(
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
	'myisam_override' => false);
// These are options which, when updated, will trigger a clearing of the cache
$yarpp_clear_cache_options = array(
	'show_pass_post','recent_only','threshold','title','body','categories',
	'tags');

function yarpp_admin_menu() {
	$hook = add_options_page(__('Related Posts (YARPP)','yarpp'),__('Related Posts (YARPP)','yarpp'), 'manage_options', 'yarpp', 'yarpp_options_page');
	add_action("load-$hook",'yarpp_load_thickbox');
	// new in 3.3: load options page sections as metaboxes
	include('options-meta-boxes.php');
	// new in 3.0.12: add settings link to the plugins page
	add_filter('plugin_action_links', 'yarpp_settings_link', 10, 2);
}

// since 3.3
function yarpp_admin_enqueue() {
	global $current_screen;
	if (is_object($current_screen) && $current_screen->id == 'settings_page_yarpp') {
		wp_enqueue_script( 'postbox' );
		wp_enqueue_style( 'yarpp_options', plugins_url( 'options.css', __FILE__ ), array(), YARPP_VERSION );
		wp_enqueue_script( 'yarpp_options', plugins_url( 'options.js', __FILE__ ), array('jquery'), YARPP_VERSION );
	}
}

function yarpp_settings_link($links, $file) {
	$this_plugin = dirname(plugin_basename(__FILE__)) . '/yarpp.php';
	if($file == $this_plugin) {
		$links[] = '<a href="options-general.php?page=yarpp">' . __('Settings', 'yarpp') . '</a>';
	}
	return $links;
}

function yarpp_load_thickbox() {
	wp_enqueue_script( 'thickbox' );
	wp_enqueue_style( 'thickbox' );
}

function yarpp_options_page() {
	// for proper metabox support:
	require(YARPP_DIR.'/options.php');
}

function widget_yarpp_init() {
	register_widget( 'YARPP_Widget' );
}

// vaguely based on code by MK Safi
// http://msafi.com/fix-yet-another-related-posts-plugin-yarpp-widget-and-add-it-to-the-sidebar/
class YARPP_Widget extends WP_Widget {
	function YARPP_Widget() {
		parent::WP_Widget(false, $name = __('Related Posts (YARPP)','yarpp'));
	}

	function widget($args, $instance) {
		global $post;
		if (!is_singular())
			return;

		extract($args);

		$type = ($post->post_type == 'page' ? array('page') : array('post'));
		if (yarpp_get_option('cross_relate'))
			$type = array('post','page');

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
		echo yarpp_related($type,$instance,false,false,'widget');
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
				jQuery(function() {
					function ensureTemplateChoice() {
						if (jQuery('#<?php echo $this->get_field_id('use_template'); ?>').attr('checked')) {
							jQuery('#<?php echo $this->get_field_id('title'); ?>').attr('disabled',true);
							jQuery('#<?php echo $this->get_field_id('template_file_p'); ?>').show();
						} else {
							jQuery('#<?php echo $this->get_field_id('title'); ?>').attr('disabled',false);
							jQuery('#<?php echo $this->get_field_id('template_file_p'); ?>').hide();
						}
					}
					jQuery('#<?php echo $this->get_field_id('use_template'); ?>').change(ensureTemplateChoice);
					ensureTemplateChoice();
				});
				</script>

		<?php
	}
}


function yarpp_default($content) {
	global $wpdb, $post;

	if (is_feed())
		return yarpp_rss($content);

	$type = ($post->post_type == 'page' ? array('page') : array('post'));
	if (yarpp_get_option('cross_relate'))
		$type = array('post','page');

	if (yarpp_get_option('auto_display') && is_single())
		return $content . yarpp_related($type,array(),false,false,'website');
	else
		return $content;
}

function yarpp_rss($content) {
	global $wpdb, $post;

	$type = ($post->post_type == 'page' ? array('page') : array('post'));
	if (yarpp_get_option('cross_relate'))
		$type = array('post','page');

	if (yarpp_get_option('rss_display'))
		return $content.yarpp_related($type,array(),false,false,'rss');
	else
		return $content;
}

function yarpp_rss_excerpt($content) {
	global $wpdb, $post;

	$type = ($post->post_type == 'page' ? array('page') : array('post'));
	if (yarpp_get_option('cross_relate'))
		$type = array('post','page');

	if (yarpp_get_option('rss_excerpt_display') && yarpp_get_option('rss_display'))
		return $content.clean_pre(yarpp_related($type,array(),false,false,'rss'));
	else
		return $content;
}

// Used only in demo mode
if (!defined('LOREMIPSUM'))
	define('LOREMIPSUM','Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras tincidunt justo a urna. Ut turpis. Phasellus convallis, odio sit amet cursus convallis, eros orci scelerisque velit, ut sodales neque nisl at ante. Suspendisse metus. Curabitur auctor pede quis mi. Pellentesque lorem justo, condimentum ac, dapibus sit amet, ornare et, erat. Quisque velit. Etiam sodales dui feugiat neque suscipit bibendum. Integer mattis. Nullam et ante non sem commodo malesuada. Pellentesque ultrices fermentum lectus. Maecenas hendrerit neque ac est. Fusce tortor mi, tristique sed, cursus at, pellentesque non, dui. Suspendisse potenti.');

function yarpp_set_option($options, $value = null) {
	global $yarpp_clear_cache_options, $yarpp;

	$current_options = yarpp_get_option();

	// we can call yarpp_set_option(key,value) if we like:
	if ( !is_array($options) && isset($value) )
		$options = array( $options => $value );

	$new_options = array_merge( $current_options, $options );

	// new in 3.1: clear cache when updating certain settings.
	$new_options_which_require_flush = array_intersect( array_keys( array_diff_assoc($options, $current_options) ), $yarpp_clear_cache_options );
	if ( count($new_options_which_require_flush) ||
		( isset($options['exclude']) && $options['exclude'] != $current_options['exclude'] ) )
		$yarpp->cache->flush();		

	update_option( 'yarpp', $new_options );
}

function yarpp_get_option($option = null) {
	global $yarpp_value_options, $yarpp_binary_options;

	$options = get_option( 'yarpp' );
	// ensure defaults if not set:
	$options = array_merge( $yarpp_value_options, $yarpp_binary_options, $options );
	if ( !isset($options['exclude']) )
		$options['exclude'] = array();
	
	if ( is_null( $option ) )
		return $options;
	if ( isset($options[$option]) )
		return $options[$option];
	return null;
}

function yarpp_add_metabox() {
	add_meta_box( 'yarpp_relatedposts', __( 'Related Posts' , 'yarpp') . ' <span class="postbox-title-action"><a href="' . esc_url( admin_url('options-general.php?page=yarpp') ) . '" class="edit-box open-box">' . __( 'Configure' ) . '</a></span>', 'yarpp_metabox', 'post', 'normal' );
}
function yarpp_metabox() {
	global $post;
	echo '<style>#yarpp_relatedposts h3 .postbox-title-action { right: 30px; top: 5px; position: absolute; padding: 0 }</style><div id="yarpp-related-posts">';
	if ( $post->ID )
		yarpp_related(array('post'),array('limit'=>1000),true,false,'metabox');
	else
		echo "<p>".__("Related entries may be displayed once you save your entry",'yarpp').".</p>";
	echo '</div>';
}

// since 3.3: default metaboxes to show:
function yarpp_default_hidden_meta_boxes($hidden, $screen) {
	if ( 'settings_page_yarpp' == $screen->id )
		$hidden = array( 'yarpp_pool', 'yarpp_relatedness' );
	return $hidden;
}

// since 3.3.2: fix for WP 3.0.x
if ( !function_exists( 'self_admin_url' ) ) {
	function self_admin_url($path = '', $scheme = 'admin') {
		if ( defined( 'WP_NETWORK_ADMIN' ) && WP_NETWORK_ADMIN )
			return network_admin_url($path, $scheme);
		elseif ( defined( 'WP_USER_ADMIN' ) && WP_USER_ADMIN )
			return user_admin_url($path, $scheme);
		else
			return admin_url($path, $scheme);
	}
}
