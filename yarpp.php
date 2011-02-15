<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://mitcho.com/code/yarpp/
Description: Returns a list of related entries based on a unique algorithm for display on your blog and RSS feeds. A templating feature allows customization of the display.
Version: 3.2.1b1
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=0&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8
*/

// set $yarpp_debug
if (isset($_REQUEST['yarpp_debug']))
  $yarpp_debug = true;

define('YARPP_VERSION','3.2.1b1');
define('YARPP_DIR',dirname(__FILE__));
// 3.2.1: safer new version checking
add_action('wp_ajax_yarpp_version_json', 'yarpp_version_json');

require_once(YARPP_DIR.'/includes.php');
require_once(YARPP_DIR.'/related-functions.php');
require_once(YARPP_DIR.'/template-functions.php');

// New in 3.2: load YARPP cache engine
// By default, this is tables, which uses custom db tables.
// Use postmeta instead and avoid custom tables by adding the following to wp-config:
//   define('YARPP_CACHE_TYPE', 'postmeta');
if (!defined('YARPP_CACHE_TYPE'))
	define('YARPP_CACHE_TYPE', 'tables');
require_once(YARPP_DIR . '/cache-' . YARPP_CACHE_TYPE . '.php');
global $yarpp_cache;
$yarpp_cache = new $yarpp_storage_class;

// Setup admin
add_action('admin_menu','yarpp_admin_menu');
add_action('admin_print_scripts','yarpp_upgrade_check');
add_filter('the_content','yarpp_default',1200);
add_filter('the_content_rss','yarpp_rss',600);
add_filter('the_excerpt_rss','yarpp_rss_excerpt',600);
register_activation_hook(__FILE__,'yarpp_activate');

load_plugin_textdomain('yarpp', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)).'/lang',dirname(plugin_basename(__FILE__)).'/lang');

// new in 2.0: add as a widget
add_action('widgets_init', 'widget_yarpp_init');
// new in 3.0: add meta box
add_action( 'admin_menu', 'yarpp_add_metabox');

// update cache on save
add_action('save_post','yarpp_save_cache');

// new in 3.2: update cache on delete
add_action('delete_post','yarpp_delete_cache');
// new in 3.2.1: handle post_status transitions
add_action('transition_post_status','yarpp_status_transition', 3);

// sets the score override flag.
add_action('parse_query','yarpp_set_score_override_flag');
