<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://yarpp.org/
Description: Returns a list of related entries based on a unique algorithm for display on your blog and RSS feeds. A templating feature allows customization of the display.
Version: 3.3.1
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: http://tinyurl.com/donatetomitcho
*/

// set $yarpp_debug
if (isset($_REQUEST['yarpp_debug']))
  $yarpp_debug = true;

define('YARPP_VERSION','3.3.1');
define('YARPP_DIR',dirname(__FILE__));

require_once(YARPP_DIR.'/includes.php');
require_once(YARPP_DIR.'/related-functions.php');
require_once(YARPP_DIR.'/template-functions.php');

// New in 3.2: load YARPP cache engine
// By default, this is tables, which uses custom db tables.
// Use postmeta instead and avoid custom tables by adding the following to wp-config:
//   define('YARPP_CACHE_TYPE', 'postmeta');
if (!defined('YARPP_CACHE_TYPE'))
	define('YARPP_CACHE_TYPE', 'tables');
global $yarpp_cache, $yarpp_storage_class;
require_once(YARPP_DIR . '/cache-' . YARPP_CACHE_TYPE . '.php');
// For PHP 4, we have to pass this object by reference:
$GLOBALS['yarpp_cache'] =& new $yarpp_storage_class;

register_activation_hook(__FILE__,'yarpp_activate');
load_plugin_textdomain('yarpp', PLUGINDIR.'/'.dirname(plugin_basename(__FILE__)), dirname(plugin_basename(__FILE__)).'/lang',dirname(plugin_basename(__FILE__)).'/lang');

// setup admin
add_action('admin_menu','yarpp_admin_menu');
// new in 3.3: properly enqueue scripts for admin:
add_action( 'admin_enqueue_scripts', 'yarpp_admin_enqueue' );
// new in 3.3: set default meta boxes to show:
add_filter( 'default_hidden_meta_boxes', 'yarpp_default_hidden_meta_boxes', 10, 2 );

// automatic display hooks:
add_filter('the_content','yarpp_default',1200);
add_filter('the_content_rss','yarpp_rss',600);
add_filter('the_excerpt_rss','yarpp_rss_excerpt',600);

// new in 2.0: add as a widget
add_action('widgets_init', 'widget_yarpp_init');
// new in 3.0: add meta box
add_action( 'admin_menu', 'yarpp_add_metabox');

// update cache on save
add_action('save_post','yarpp_save_cache');

// new in 3.2: update cache on delete
add_action('delete_post','yarpp_delete_cache');
// new in 3.2.1: handle post_status transitions
add_action('transition_post_status','yarpp_status_transition', 10, 3);

// sets the score override flag.
add_action('parse_query','yarpp_set_score_override_flag');

// new in 3.3: include BlogGlue meta box
if ( file_exists( YARPP_DIR . '/blogglue.php' ) )
	include_once( YARPP_DIR . '/blogglue.php' );
