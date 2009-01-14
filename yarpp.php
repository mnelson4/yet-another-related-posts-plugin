<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://mitcho.com/code/yarpp/
Description: Returns a list of the related entries based on a unique algorithm using titles, post bodies, tags, and categories. Now with RSS feed support!
Version: 3.0b1
Author: mitcho (Michael Yoshitaka Erlewine)
Author URI: http://mitcho.com/
*/

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
	add_meta_box( __( 'Related Posts' , 'yarpp'), __( 'Related Posts' , 'yarpp'), 'yarpp_metabox', 'post', 'normal' );
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
function yarpp_save_cache($post_ID,$force=true) {
	global $wpdb;
	$parent_ID = $wpdb->get_var("select post_parent from $wpdb->posts where ID='$post_ID'");
	if ($parent_ID != $post_ID and $parent_ID)
		$post_ID = $parent_ID;
	if (yarpp_get_option('cross_relate'))
		$type = array('post','page');
	else
		$type = array('post');
	yarpp_cache_enforce($type,$post_ID,$force);
}

//==TEMPLATING

add_filter('posts_join','yarpp_join_filter');
add_filter('posts_where','yarpp_where_filter');
add_filter('posts_orderby','yarpp_orderby_filter');
add_filter('posts_fields','yarpp_fields_filter');
add_action('parse_query','yarpp_set_score_override_flag'); // sets the score override flag. 

function yarpp_set_score_override_flag($q) {
	global $yarpp_time, $yarpp_score_override;
	if ($yarpp_time) {
		if ($q->query_vars['orderby'] == 'score')
			$yarpp_score_override = true;
		else
			$yarpp_score_override = false;
	}
}

function yarpp_join_filter($arg) {
	global $wpdb, $yarpp_time;
	if ($yarpp_time) {
		$arg .= " join {$wpdb->prefix}yarpp_related_cache as yarpp using (ID)";
	}
	return $arg;
}

function yarpp_where_filter($arg) {
	global $wpdb, $yarpp_time;
	$threshold = yarpp_get_option('threshold');
	if ($yarpp_time) {
		$arg = str_replace("$wpdb->posts.ID = ","yarpp.score > $threshold and yarpp.reference_ID = ",$arg);
	}
	return $arg;
}

function yarpp_orderby_filter($arg) {
	global $wpdb, $yarpp_time, $yarpp_score_override;
	if ($yarpp_time and $yarpp_score_override) {
		$arg = str_replace("$wpdb->posts.post_date","yarpp.score",$arg);
	}
	return $arg;
}

function yarpp_fields_filter($arg) {
	global $wpdb, $yarpp_time;
	if ($yarpp_time) {
		$arg .= ", yarpp.score";
	}
	return $arg;
}

