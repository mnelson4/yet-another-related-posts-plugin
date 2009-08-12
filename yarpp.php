<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://mitcho.com/code/yarpp/
Description: Returns a list of related entries based on a unique algorithm for display on your blog and RSS feeds. A templating feature allows customization of the display.
Version: 3.0.9
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
*/

define('YARPP_NUMERICAL_VERSION','3.09');
define('YARPP_VERSION','3.09');

require_once('includes.php');
require_once('related-functions.php');
require_once('template-functions.php');

add_action('admin_menu','yarpp_admin_menu');
add_action('admin_print_scripts','yarpp_upgrade_check');
add_filter('the_content','yarpp_default',1200);
add_filter('the_content_rss','yarpp_rss',600);
add_filter('the_excerpt_rss','yarpp_rss_excerpt',600);
register_activation_hook(__FILE__,'yarpp_activate');

load_plugin_textdomain('yarpp', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)).'/lang',dirname(plugin_basename(__FILE__)).'/lang');

// new in 2.0: add as a widget
add_action('plugins_loaded', 'widget_yarpp_init');
// new in 3.0: add meta box
add_action( 'admin_menu', 'yarpp_add_metabox');
function yarpp_add_metabox() {
	if (function_exists('add_meta_box')) {
    add_meta_box( 'yarpp_relatedposts', __( 'Related Posts' , 'yarpp'), 'yarpp_metabox', 'post', 'normal' );
	}
}
function yarpp_metabox() {
	global $post;
	echo '<div id="yarpp-related-posts">';
	if ($post->ID)
		yarpp_related(array('post'),array('limit'=>1000),true,false,'metabox');
	else
		echo "<p>Related entries may be displayed once you save your entry.</p>";
	echo '</div>';
}

add_action('save_post','yarpp_save_cache');

add_filter('posts_join','yarpp_join_filter');
add_filter('posts_where','yarpp_where_filter');
add_filter('posts_orderby','yarpp_orderby_filter');
add_filter('posts_fields','yarpp_fields_filter');
add_filter('posts_request','yarpp_demo_request_filter');
add_filter('post_limits','yarpp_limit_filter');
add_action('parse_query','yarpp_set_score_override_flag'); // sets the score override flag. 
