<?php

function yarpp_related($reference_ID = false, $args = array(), $echo = true) {
	global $yarpp;

	if ( is_array($reference_ID) ) {
		_doing_it_wrong( __FUNCTION__, "YARPP's (internal) related function signature now takes the \$reference_ID first.", '3.4');
		return;
	}
	
	return $yarpp->display_related($reference_ID, $args, $echo);
}

function yarpp_related_exist($reference_ID = false, $args = array()) {
	global $yarpp;

	if ( is_array($reference_ID) ) {
		_doing_it_wrong( __FUNCTION__, "YARPP's (internal) related function signature now takes the \$reference_ID first.", '3.4');
		return;
	}
	
	return $yarpp->related_exist($reference_ID, $args, $echo);
}

function yarpp_get_related($reference_ID = false, $args = array()) {
	global $yarpp;
	return $yarpp->get_related($reference_ID, $args);
}

// Here are the related_WHATEVER functions, as introduced in 1.1
// Since YARPP 2.1, these functions receive (optionally) one array argument. See the documentation for instructions on how to customize their output.

function related_posts($args = array(),$echo=true,$reference_ID=false) {
	$args['post_type'] = array('post');
	if ( yarpp_get_option('cross_relate') )
		$args['post_type'] = array('post', 'page');
	return yarpp_related($reference_ID, $args, $echo);
}

function related_pages($args = array(),$echo=true,$reference_ID=false) {
	$args['post_type'] = array('page');
	if ( yarpp_get_option('cross_relate') )
		$args['post_type'] = array('post', 'page');
	return yarpp_related($reference_ID, $args, $echo);
}

function related_entries($args = array(),$echo=true,$reference_ID=false) {
	$args['post_type'] = array('post', 'page');
	return yarpp_related($reference_ID, $args, $echo);
}

function related_posts_exist($args = array(),$reference_ID=false) {
	$args['post_type'] = array('post');
	if ( yarpp_get_option('cross_relate') )
		$args['post_type'] = array('post', 'page');
	return yarpp_related_exist($reference_ID, $args);
}

function related_pages_exist($args = array(),$reference_ID=false) {
	$args['post_type'] = array('page');
	if ( yarpp_get_option('cross_relate') )
		$args['post_type'] = array('post', 'page');
	return yarpp_related_exist($reference_ID, $args);
}

function related_entries_exist($args = array(),$reference_ID=false) {
	$args['post_type'] = array('post', 'page');
	return yarpp_related_exist($reference_ID, $args);
}
