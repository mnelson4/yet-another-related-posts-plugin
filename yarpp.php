<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://mitcho.com/code/yarpp/
Description: Returns a list of related entries based on a unique algorithm for display on your blog and RSS feeds. A templating feature allows customization of the display.
Version: 3.1.7
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
*/

define('YARPP_VERSION','3.1.7');
define('YARPP_DIR',dirname(__FILE__));

require_once(YARPP_DIR.'/includes.php');
require_once(YARPP_DIR.'/related-functions.php');
require_once(YARPP_DIR.'/template-functions.php');

add_action('admin_menu','yarpp_admin_menu');
add_action('admin_print_scripts','yarpp_upgrade_check');
add_filter('the_content','yarpp_default',1200);
add_filter('the_content_rss','yarpp_rss',600);
add_filter('the_excerpt_rss','yarpp_rss_excerpt',600);
register_activation_hook(__FILE__,'yarpp_activate');

// new in 3.1: clear cache when updating certain settings.
add_action('update_option_yarpp_distags','yarpp_clear_cache');
add_action('update_option_yarpp_discats','yarpp_clear_cache');
add_action('update_option_yarpp_show_pass_post','yarpp_clear_cache');
add_action('update_option_yarpp_recent_only','yarpp_clear_cache');
add_action('update_option_yarpp_threshold','yarpp_clear_cache');
add_action('update_option_yarpp_title','yarpp_clear_cache');
add_action('update_option_yarpp_body','yarpp_clear_cache');
add_action('update_option_yarpp_categories','yarpp_clear_cache');
add_action('update_option_yarpp_tags','yarpp_clear_cache');
add_action('update_option_yarpp_tags','yarpp_clear_cache');

load_plugin_textdomain('yarpp', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)).'/lang',dirname(plugin_basename(__FILE__)).'/lang');

// new in 2.0: add as a widget
add_action('widgets_init', 'widget_yarpp_init');
// new in 3.0: add meta box
add_action( 'admin_menu', 'yarpp_add_metabox');

// update cache on save
add_action('save_post','yarpp_save_cache');

add_filter('posts_join','yarpp_join_filter');
add_filter('posts_where','yarpp_where_filter');
add_filter('posts_orderby','yarpp_orderby_filter');
add_filter('posts_fields','yarpp_fields_filter');
add_filter('posts_request','yarpp_demo_request_filter');
add_filter('post_limits','yarpp_limit_filter');
add_action('parse_query','yarpp_set_score_override_flag'); // sets the score override flag. 

// set $yarpp_debug
if (isset($_REQUEST['yarpp_debug']))
  $yarpp_debug = true;
