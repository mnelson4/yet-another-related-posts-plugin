<?php

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
