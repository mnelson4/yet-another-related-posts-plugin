<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://mitcho.com/code/yarpp/
Description: Returns a list of the related entries based on keyword matches, limited by a certain relatedness threshold. New and improved, version 2.0!
Version: 2.0.6
Author: mitcho (Michael Yoshitaka Erlewine)
*/

require_once('includes.php');
require_once('magic.php');
require_once('related-functions.php');

add_action('admin_menu','yarpp_admin_menu');
add_action('admin_print_scripts','yarpp_upgrade_check');
add_filter('the_content','yarpp_default',1200);
add_filter('the_content_rss','yarpp_rss',600);
add_filter('the_excerpt_rss','yarpp_rss_excerpt',600);
register_activation_hook(__FILE__,'yarpp_activate');

// new in 2.0: add as a widget
add_action('plugins_loaded', 'widget_yarpp_init');

?>