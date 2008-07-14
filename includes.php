<?php

// here's a list of all the options YARPP uses (except version), as well as their default values, sans the yarpp_ prefix, split up into binary options and value options. These arrays are used in updating settings (options.php) and other tasks.
$yarpp_value_options = array('threshold' => 5,
				'limit' => 5,
				'excerpt_length' => 10,
				'before_title' => '<li>',
				'after_title' => '</li>',
				'before_post' => '<small>',
				'after_post' => '</small>',
				'before_related' => '<p>Related posts:<ol>',
				'after_related' => '</ol></p>',
				'no_results' => '<p>No related posts.</p>',
				'title' => '2',
				'body' => '2',
				'categories' => '2',
				'tags' => '2',
				'distags' => '',
				'discats' => '',
				'order' => 'score DESC'
				);
$yarpp_binary_options = array('past_only' => true,
				'show_score' => true,
				'show_excerpt' => false,
				'show_pass_post' => false,
				'cross_relate' => false,
				'auto_display' => true,
				'promote_yarpp' => false);

function yarpp_enabled() {
	global $wpdb;
	$indexdata = $wpdb->get_results("show index from $wpdb->posts");
	foreach ($indexdata as $index) {
		if ($index->Key_name == 'yarpp_cache') return 1;
	}
	return 0;
}

function yarpp_activate() {
	global $yarpp_version, $wpdb, $yarpp_binary_options, $yarpp_value_options;
	$yarpp_options = array_merge($yarpp_binary_options, $yarpp_value_options);
	foreach (array_keys($yarpp_options) as $option) {
		add_option('yarpp_'.$option,$yarpp_options[$option]);
	}
	if (!yarpp_enabled()) {
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_cache` ( `post_title` , `post_content` )");
	}
	add_option('yarpp_version','2.03');
	update_option('yarpp_version','2.03');
	return 1;
}

function yarpp_upgrade_check() {
	global $wpdb, $yarpp_value_options, $yarpp_binary_options;

	if (get_option('threshold') and get_option('limit') and get_option('len')) {
		yarpp_activate(); // just to make sure, in case the plugin was just replaced and not deactivated / activated
		echo '<div id="message" class="updated fade" style="background-color: rgb(207, 235, 247);"><h3>An important message from YARPP:</h3><p>Thank you for upgrading to YARPP 1.5. YARPP 1.5 adds "simple installation" which automagically prints a simple related posts display at the end of each single entry (permalink) page without tinkering with any theme files. As a previous YARPP user, you probably have already edited your theme files to your liking, so this "automatic display" feature has been turned off.</p><p>If you would like to use "automatic display," remove <code>related_posts</code> from your <code>single.php</code> file and turn on automatic display in the YARPP options. Make sure to adjust the new prefix and suffix preferences to your liking as well.</p><p>For more information, check out the <a href="http://mitcho.com/code/yarpp/">YARPP documentation</a>. (This message will not be displayed again.)</p></div>';
		yarpp_upgrade_one_five();
		update_option('yarpp_version','1.5');
	}
	
	if (get_option('yarpp_version') < 2) {
		foreach (array_keys($yarpp_value_options) as $option) {
			if (!get_option('yarpp_'.$option))
			add_option('yarpp_'.$option,$yarpp_value_options[$option]);
		}
		foreach (array_keys($yarpp_binary_options) as $option) {
			if (!get_option('yarpp_'.$option))
			add_option('yarpp_'.$option,$yarpp_binary_options[$option]);
		}

		echo '<div id="message" class="updated fade" style="background-color: rgb(207, 235, 247);"><h3>An important message from YARPP:</h3><p>Thank you for upgrading to YARPP 2.0. YARPP 2.0 adds the much requested ability to limit related entry results by certain tags or categories. 2.0 also brings more fine tuned control of the magic algorithm, letting you specify how the algorithm should consider or not consider entry content, titles, tags, and categories. Make sure to adjust the new settings to your liking and perhaps readjust your threshold.</p><p>For more information, check out the <a href="http://mitcho.com/code/yarpp/">YARPP documentation</a>. (This message will not be displayed again.)</p></div>';
		update_option('yarpp_version','2.0');
	}
	
	if (get_option('yarpp_version') < 2.02) {
		update_option('yarpp_version','2.02');
	}

	if (get_option('yarpp_version') < 2.03) {	
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `yarpp_cache` ( `post_title` , `post_content` )");
		update_option('yarpp_version','2.03');
	}
	
}

function yarpp_admin_menu() {
	add_options_page('Related Posts (YARPP)', 'Related Posts (YARPP)', 8, 'yet-another-related-posts-plugin/options.php', 'yarpp_options_page');
   //if (function_exists('add_submenu_page')) add_submenu_page('options-general.php', 'Related Posts (YARPP)', 'Related Posts (YARPP)', 8, 'yet-another-related-posts-plugin/options.php');
}

function yarpp_options_page() {
	require(str_replace('includes.php','options.php',__FILE__));
}

// This function was written by tyok
function widget_yarpp_init() {

	if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
		return;

	function widget_yarpp($args) {
		extract($args);
		global $wpdb, $post, $user_level;
		if (get_option('yarpp_auto_display') and is_single()) {
				echo $before_widget;
				    	echo $before_title . 'Related Posts' . $after_title;
					echo yarpp_related(array('post'),array());
				echo $after_widget;
		}
	}
	register_sidebar_widget(__('YARPP'), 'widget_yarpp');
}

function yarpp_default($content) {
	global $wpdb, $post, $user_level;
	if (get_option('yarpp_auto_display') and is_single()) {
		return $content."\n\n".yarpp_related(array('post'),array(),false);
	} else {
		return $content;
	}
}

/* new in 2.0! apply_filters_if_white (previously apply_filters_without) now has a blacklist. It's defined here. */

/* blacklisted so far:
	- diggZEt
	- WP-Syntax
	- Viper's Video Quicktags
	- WP-CodeBox
	- WP shortcodes
*/

$yarpp_blacklist = array(null,'yarpp_default','diggZEt_AddBut','wp_syntax_before_filter','wp_syntax_after_filter','wp_codebox_before_filter','wp_codebox_after_filter','do_shortcode');
$yarpp_blackmethods = array(null,'addinlinejs','replacebbcode');

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

function apply_filters_if_white($tag, $string) {
	global $wp_filter, $merged_filters, $yarpp_blacklist;

	if ( !isset( $merged_filters[ $tag ] ) )
		merge_filters($tag);

	if ( !isset($wp_filter[$tag]) )
		return $string;

	reset( $wp_filter[ $tag ] );

	$args = func_get_args();

	do{
		foreach( (array) current($wp_filter[$tag]) as $the_ ) {
			if ( !is_null($the_['function'])
			and yarpp_white($the_['function'])){ // HACK
				$args[1] = $string;
				$oldstring = $string;
				$string = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
			}
		}

	} while ( next($wp_filter[$tag]) !== false );

	return $string;
}

// upgrade to 1.5!
function yarpp_upgrade_one_five() {
	global $wpdb;
	$migrate_options = array('past_only','show_score','show_excerpt','show_pass_post','cross_relate','limit','threshold','before_title','after_title','before_post','after_post');
	foreach ($migrate_options as $option) {
		if (get_option($option)) {
			update_option('yarpp_'.$option,get_option($option));
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

// upgrade to 1.5!
function yarpp_upgrade_one_six() {
	global $wpdb;
}

define('LOREMIPSUM','Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Cras tincidunt justo a urna. Ut turpis. Phasellus convallis, odio sit amet cursus convallis, eros orci scelerisque velit, ut sodales neque nisl at ante. Suspendisse metus. Curabitur auctor pede quis mi. Pellentesque lorem justo, condimentum ac, dapibus sit amet, ornare et, erat. Quisque velit. Etiam sodales dui feugiat neque suscipit bibendum. Integer mattis. Nullam et ante non sem commodo malesuada. Pellentesque ultrices fermentum lectus. Maecenas hendrerit neque ac est. Fusce tortor mi, tristique sed, cursus at, pellentesque non, dui. Suspendisse potenti.');

function yarpp_excerpt($content,$length) {
	preg_replace('/([,;.-]+)\s*/','\1 ',$content);
	return implode(' ',array_slice(preg_split('/\s+/',$content),0,$length)).'...';
}

?>