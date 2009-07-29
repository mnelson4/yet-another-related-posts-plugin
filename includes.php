<?php

require_once('magic.php');
require_once('keywords.php');
require_once('intl.php');
require_once('services.php');

if ( !defined('WP_CONTENT_URL') )
	define('WP_CONTENT_URL', get_option('siteurl') . '/wp-content');
if ( !defined('WP_CONTENT_DIR') )
	define('WP_CONTENT_DIR', ABSPATH . 'wp-content');

// here's a list of all the options YARPP uses (except version), as well as their default values, sans the yarpp_ prefix, split up into binary options and value options. These arrays are used in updating settings (options.php) and other tasks.
$yarpp_value_options = array('threshold' => 5,
				'limit' => 5,
				'template_file' => '', // new in 2.2
				'excerpt_length' => 10,
				'recent_number' => 12,
				'recent_units' => 'month',
				'before_title' => '<li>',
				'after_title' => '</li>',
				'before_post' => ' <small>',
				'after_post' => '</small>',
				'before_related' => '<p>'.__('Related posts:','yarpp').'<ol>',
				'after_related' => '</ol></p>',
				'no_results' => '<p>'.__('No related posts.','yarpp').'</p>',
				'order' => 'score DESC',
				'rss_limit' => 3,
				'rss_template_file' => '', // new in 2.2
				'rss_excerpt_length' => 10,
				'rss_before_title' => '<li>',
				'rss_after_title' => '</li>',
				'rss_before_post' => ' <small>',
				'rss_after_post' => '</small>',
				'rss_before_related' => '<p>'.__('Related posts:','yarpp').'<ol>',
				'rss_after_related' => '</ol></p>',
				'rss_no_results' => '<p>'.__('No related posts.','yarpp').'</p>',
				'rss_order' => 'score DESC',
				'title' => '2',
				'body' => '2',
				'categories' => '2',
				'tags' => '2',
				'distags' => '',
				'discats' => '');
$yarpp_binary_options = array('past_only' => true,
				'show_excerpt' => false,
				'recent_only' => false, // new in 3.0
				'use_template' => false, // new in 2.2
				'rss_show_excerpt' => false,
				'rss_use_template' => false, // new in 2.2
				'show_pass_post' => false,
				'cross_relate' => false,
				'auto_display' => true,
				'rss_display' => true,
				'rss_excerpt_display' => true,
				'promote_yarpp' => false,
				'rss_promote_yarpp' => false,
				'ad_hoc_caching' => true);

function yarpp_enabled() {
	global $wpdb;
	$indexdata = $wpdb->get_results("show index from $wpdb->posts");
	foreach ($indexdata as $index) {
		if ($index->Key_name == 'yarpp_title') {
			// now check for the cache tables
			$tabledata = $wpdb->get_col("show tables");
			if (array_search("{$wpdb->prefix}yarpp_related_cache",$tabledata) !== false and array_search("{$wpdb->prefix}yarpp_keyword_cache",$tabledata) !== false)
				return 1;
			else
				return 0;
		};
	}
	return 0;
}

function yarpp_reinforce() {
	if (!get_option('yarpp_version'))
		yarpp_activate();
	yarpp_upgrade_check(true);
}

function yarpp_activate() {
	global $yarpp_version, $wpdb, $yarpp_binary_options, $yarpp_value_options;
	foreach (array_keys($yarpp_value_options) as $option) {
		if (!get_option("yarpp_$option") or get_option("yarpp_$option") == '')
		add_option("yarpp_$option",$yarpp_value_options[$option]);
	}
	foreach (array_keys($yarpp_binary_options) as $option) {
		if (!get_option("yarpp_$option") or get_option("yarpp_$option") == '')
		add_option("yarpp_$option",$yarpp_binary_options[$option]." ");
	}
	if (!yarpp_enabled()) {
		if (!$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_title` ( `post_title`)")) {
			echo "<!--".__('MySQL error on adding yarpp_title','yarpp').": ";
			$wpdb->print_error();
			echo "-->";
		}
		if (!$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_content` ( `post_content`)")) {
			echo "<!--".__('MySQL error on adding yarpp_content','yarpp').": ";
			$wpdb->print_error();
			echo "-->";
		}
		if (!$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}yarpp_keyword_cache` (
			`ID` bigint(20) unsigned NOT NULL default '0',
			`body` text collate utf8_unicode_ci NOT NULL,
			`title` text collate utf8_unicode_ci NOT NULL,
			`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY  (`ID`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='YARPP''s keyword cache table';")) {
			echo "<!--".__('MySQL error on creating yarpp_keyword_cache table','yarpp').": ";
			$wpdb->print_error();
			echo "-->";
		}
		if (!$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}yarpp_related_cache` (
			`reference_ID` bigint(20) unsigned NOT NULL default '0',
			`ID` bigint(20) unsigned NOT NULL default '0',
			`score` float unsigned NOT NULL default '0',
			`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY  (`reference_ID`,`ID`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;")) {
			echo "<!--".__('MySQL error on creating yarpp_related_cache table','yarpp').": ";
			$wpdb->print_error();
			echo "-->";
		}
		if (!yarpp_enabled()) {
			return 0;
		}
	}
	add_option('yarpp_version',YARPP_VERSION);
	update_option('yarpp_version',YARPP_VERSION);
	return 1;
}

function yarpp_myisam_check() {
	global $wpdb;
	$tables = $wpdb->get_results("show table status like '$wpdb->posts'");
	foreach ($tables as $table) {
		if ($table->Engine == 'MyISAM') return true;
		else return $table->Engine;
	}
	return 'UNKNOWN';
}

function yarpp_upgrade_check($inuse = false) {
	global $wpdb, $yarpp_value_options, $yarpp_binary_options;

	foreach (array_keys($yarpp_value_options) as $option) {
		if (!get_option("yarpp_$option") or get_option("yarpp_$option") == '')
		add_option("yarpp_$option",$yarpp_value_options[$option].' ');
	}
	foreach (array_keys($yarpp_binary_options) as $option) {
		if (!get_option("yarpp_$option") or get_option("yarpp_$option") == '')
		add_option("yarpp_$option",$yarpp_binary_options[$option]." ");
	}

	// upgrade check

	if (get_option('threshold') and get_option('limit') and get_option('len')) {
		yarpp_activate();
		yarpp_upgrade_one_five();
		update_option('yarpp_version','1.5');
	}
	
	if (eregi_replace('[a-z].*$','',get_option('yarpp_version')) < 2) {
		foreach (array_keys($yarpp_value_options) as $option) {
			if (!get_option("yarpp_$option"))
			add_option("yarpp_$option",$yarpp_value_options[$option].' ');
		}
		foreach (array_keys($yarpp_binary_options) as $option) {
			if (!get_option("yarpp_$option"))
			add_option("yarpp_$option",$yarpp_binary_options[$option]);
		}

	}

	if (eregi_replace('[a-z].*$','',get_option('yarpp_version')) < 2.03) {
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_title` ( `post_title`)");
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_content` ( `post_content`)");		update_option('yarpp_version','2.03');
	}

	if (eregi_replace('[a-z].*$','',get_option('yarpp_version')) < YARPP_NUMERICAL_VERSION or get_option('yarpp_version') != YARPP_VERSION) {
		update_option('yarpp_version',YARPP_VERSION);
		
		//if (!$inuse)
		//	echo '<div id="message" class="updated fade" style="background-color: rgb(207, 235, 247);">'.__('<h3>An important message from YARPP:</h3><p>Thank you for upgrading to YARPP 2. YARPP 2.0 adds the much requested ability to limit related entry results by certain tags or categories. 2.0 also brings more fine tuned control of the magic algorithm, letting you specify how the algorithm should consider or not consider entry content, titles, tags, and categories. Make sure to adjust the new settings to your liking and perhaps readjust your threshold.</p><p>For more information, check out the <a href="http://mitcho.com/code/yarpp/">YARPP documentation</a>. (This message will not be displayed again.)</p>','yarpp').'</div>';
	}

	// just in case, try to add the index one more time.	
	if (!yarpp_enabled()) {
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_title` ( `post_title`)");
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_content` ( `post_content`)");
	}
	
}

function yarpp_admin_menu() {
	$hook = add_options_page(__('Related Posts (YARPP)','yarpp'),__('Related Posts (YARPP)','yarpp'), 8, 'yet-another-related-posts-plugin/options.php', 'yarpp_options_page');
   //if (function_exists('add_submenu_page')) add_submenu_page('options-general.php', 'Related Posts (YARPP)', 'Related Posts (YARPP)', 8, 'yet-another-related-posts-plugin/options.php');
	add_action("load-$hook",'yarpp_load_thickbox');
}

function yarpp_load_thickbox() {
	wp_enqueue_script( 'thickbox' );
	if (function_exists('wp_enqueue_style')) {
		wp_enqueue_style( 'thickbox' );
	}
}

function yarpp_options_page() {
	require(str_replace('includes.php','options.php',__FILE__));
}

// This function was written by @tyok
function widget_yarpp_init() {

	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	function widget_yarpp($args) {
		extract($args);
		global $wpdb, $post;
		if (is_single() && have_posts()) {
      the_post();
      echo $before_widget;
      echo $before_title . __('Related Posts','yarpp') . $after_title;
      echo yarpp_related(array('post'),array());
      echo $after_widget;
		}
	}
	register_sidebar_widget(__('YARPP','yarpp'), 'widget_yarpp');
}

function yarpp_default($content) {
	global $wpdb, $post;
	if (is_feed())
		return yarpp_rss($content);
	elseif (yarpp_get_option('auto_display') and is_single())
		return $content.yarpp_related(array('post'),array(),false,false,'website');
	else
		return $content;
}

function yarpp_rss($content) {
	global $wpdb, $post;
	if (yarpp_get_option('rss_display'))
		return $content.yarpp_related(array('post'),array(),false,false,'rss');
	else
		return $content;
}

function yarpp_rss_excerpt($content) {
	global $wpdb, $post;
	if (yarpp_get_option('rss_excerpt_display') && yarpp_get_option('rss_display'))
		return $content.clean_pre(yarpp_related(array('post'),array(),false,false,'rss'));
	else
		return $content;
}


/* new in 2.0! apply_filters_if_white (previously apply_filters_without) now has a blacklist. It's defined here. */

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

$yarpp_blacklist = array(null,'yarpp_default','diggZEt_AddBut','reddZEt_AddBut','dzoneZEt_AddBut','wp_syntax_before_filter','wp_syntax_after_filter','wp_codebox_before_filter','wp_codebox_after_filter','do_shortcode');//,'insert_tweet_this'
$yarpp_blackmethods = array(null,'addinlinejs','replacebbcode','filter_content');

function yarpp_white($filter) {
	global $yarpp_blacklist;
	global $yarpp_blackmethods;
	if (is_array($filter)) {
		if (array_search($filter[1],$yarpp_blackmethods)) //print_r($filter[1]);
			return false;
	}
	if (array_search($filter,$yarpp_blacklist)) //print_r($filter);
		return false;
	return true;
}

/* FYI, apply_filters_if_white was used here to avoid a loop in apply_filters('the_content') > yarpp_default() > yarpp_related() > current_post_keywords() > apply_filters('the_content').*/

function apply_filters_if_white($tag, $value) {
	global $wp_filter, $merged_filters, $wp_current_filter;

	$args = array();
	$wp_current_filter[] = $tag;

	// Do 'all' actions first
	if ( isset($wp_filter['all']) ) {
		$args = func_get_args();
		_wp_call_all_hook($args);
	}

	if ( !isset($wp_filter[$tag]) ) {
		array_pop($wp_current_filter);
		return $value;
	}

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	if ( empty($args) )
		$args = func_get_args();


	do{
		foreach( (array) current($wp_filter[$tag]) as $the_ ) {
			if ( !is_null($the_['function'])
			and yarpp_white($the_['function'])){ // HACK
				$args[1] = $value;
				$value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
			}
		}

	} while ( next($wp_filter[$tag]) !== false );

	array_pop( $wp_current_filter );

	return $value;
}

// upgrade to 1.5!
function yarpp_upgrade_one_five() {
	global $wpdb;
	$migrate_options = array('past_only','show_excerpt','show_pass_post','cross_relate','limit','threshold','before_title','after_title','before_post','after_post');
	foreach ($migrate_options as $option) {
		if (get_option($option)) {
			update_option("yarpp_$option",get_option($option));
			delete_option($option);
		}
	}
	// len is one option where we actually change the name of the option
	update_option('yarpp_excerpt_length',get_option('len'));
	delete_option('len');

	// override these defaults for those who upgrade from < 1.5
	update_option('yarpp_auto_display',false);
	update_option('yarpp_before_related','');
	update_option('yarpp_after_related','');
	unset($yarpp_version);
}

define('LOREMIPSUM','Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras tincidunt justo a urna. Ut turpis. Phasellus convallis, odio sit amet cursus convallis, eros orci scelerisque velit, ut sodales neque nisl at ante. Suspendisse metus. Curabitur auctor pede quis mi. Pellentesque lorem justo, condimentum ac, dapibus sit amet, ornare et, erat. Quisque velit. Etiam sodales dui feugiat neque suscipit bibendum. Integer mattis. Nullam et ante non sem commodo malesuada. Pellentesque ultrices fermentum lectus. Maecenas hendrerit neque ac est. Fusce tortor mi, tristique sed, cursus at, pellentesque non, dui. Suspendisse potenti.');

function yarpp_excerpt($content,$length) {
  $content = strip_tags($content);
	preg_replace('/([,;.-]+)\s*/','\1 ',$content);
	return implode(' ',array_slice(preg_split('/\s+/',$content),0,$length)).'...';
}

function yarpp_set_option($option,$value) {
	global $yarpp_value_options;
	if (array_search($option,array_keys($yarpp_value_options)) === true)
		update_option("yarpp_$option",$value.' ');
	else
		update_option("yarpp_$option",$value);
}

function yarpp_get_option($option,$escapehtml = false) {
	global $yarpp_value_options;
	if (!(array_search($option,array_keys($yarpp_value_options)) === false))
		$return = chop(get_option("yarpp_$option"));
	else
		$return = get_option("yarpp_$option");
	if ($escapehtml)
		$return = htmlspecialchars(stripslashes($return));
	return $return;
}

function yarpp_microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function yarpp_check_version_json($version) {
  include_once(ABSPATH . WPINC . '/class-snoopy.php');
  if (class_exists('Snoopy')) {
    $snoopy = new Snoopy;
    $snoopy->referer = get_bloginfo('siteurl');
    $result = $snoopy->fetch("http://mitcho.com/code/yarpp/checkversion.php?version=$version");
    if ($result) {
      return $snoopy->results;
    }
  }
  return '{}';
}

?>