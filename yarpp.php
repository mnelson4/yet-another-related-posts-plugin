<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://yarpp.org/
Description: Returns a list of related entries based on a unique algorithm for display on your blog and RSS feeds. A templating feature allows customization of the display.
Version: 3.4b6
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
Donate link: http://tinyurl.com/donatetomitcho
*/

define('YARPP_VERSION', '3.4b6');
define('YARPP_DIR', dirname(__FILE__));
define('YARPP_NO_RELATED', ':(');
define('YARPP_RELATED', ':)');
define('YARPP_NOT_CACHED', ':/');
define('YARPP_DONT_RUN', 'X(');

require_once(YARPP_DIR.'/class-core.php');
require_once(YARPP_DIR.'/includes.php');
require_once(YARPP_DIR.'/magic.php');
require_once(YARPP_DIR.'/related-functions.php');
require_once(YARPP_DIR.'/template-functions.php');
require_once(YARPP_DIR.'/class-widget.php');

// New in 3.2: load YARPP cache engine
// By default, this is tables, which uses custom db tables.
// Use postmeta instead and avoid custom tables by adding the following to wp-config:
//   define('YARPP_CACHE_TYPE', 'postmeta');
if (!defined('YARPP_CACHE_TYPE'))
	define('YARPP_CACHE_TYPE', 'tables');

// new in 3.3.3: init yarpp on init
add_action( 'init', 'yarpp_init' );
function yarpp_init() {
	global $yarpp;
	$yarpp = new YARPP;

	// new in 3.3: include BlogGlue meta box
	if ( file_exists( YARPP_DIR . '/blogglue.php' ) )
		include_once( YARPP_DIR . '/blogglue.php' );
}
