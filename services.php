<?php

// setup the ajax action hooks
add_action('wp_ajax_yarpp_display_exclude_terms', 'yarpp_ajax_display_exclude_terms');
add_action('wp_ajax_yarpp_display_demo_web', 'yarpp_ajax_display_demo_web');
add_action('wp_ajax_yarpp_display_demo_rss', 'yarpp_ajax_display_demo_rss');

function yarpp_ajax_display_exclude_terms() {
	global $wpdb;
	
	if ( !isset($_REQUEST['taxonomy']) )
		return;
	
	$taxonomy = (string) $_REQUEST['taxonomy'];
	
	header("HTTP/1.1 200");
	header("Content-Type: text/html; charset=UTF-8");
	
	$exclude = yarpp_get_option('exclude');
	if ( !isset($exclude[$taxonomy]) )
		$exclude[$taxonomy] = array();
	$terms = get_terms($taxonomy, array(
		'exclude' => $exclude[$taxonomy],
		'number' => 100,
		'offset' => $_REQUEST['offset']
	));
	
	if ( !count($terms) ) {
		echo ':('; // no more :(
		exit;
	}
	
	foreach ($terms as $term) {
		echo "<input type='checkbox' name='exclude[$taxonomy][$term->term_id]' value='true' /> <label>" . esc_html($term->name) . "</label> ";
		//for='exclude[$taxonomy][$cat->term_id]' it's not HTML. :(
	}
	exit;
}

function yarpp_ajax_display_demo_web() {
	header("HTTP/1.1 200");
	header("Content-Type: text/html; charset=UTF-8");

	$return = yarpp_related(array('post'),array(),false,false,'demo_web');
	echo ereg_replace("[\n\r]",'',nl2br(htmlspecialchars($return)));
	exit;
}

function yarpp_ajax_display_demo_rss() {
	header("HTTP/1.1 200");
	header("Content-Type: text/html; charset=UTF-8");

	$return = yarpp_related(array('post'),array(),false,false,'demo_rss');
	echo ereg_replace("[\n\r]",'',nl2br(htmlspecialchars($return)));
	exit;
}
