<?php

function yarpp_related($type,$args,$echo = true,$reference_ID=false,$domain = 'website') {
	global $yarpp;
	return $yarpp->related($type, $args, $echo, $reference_ID, $domain);
}

function yarpp_related_exist($type,$args,$reference_ID=false) {
	global $yarpp;
	return $yarpp->related_exist($type, $args, $reference_ID);
}

// Here are the related_WHATEVER functions, as introduced in 1.1, which actually just use the yarpp_related and yarpp_related_exist functions.

// Since YARPP 2.1, these functions receive (optionally) one array argument. See the documentation for instructions on how to customize their output.

function related_posts($a = array(),$echo=true,$reference_ID=false) {
	return yarpp_related(array('post'),$a,$echo,$reference_ID);
}

function related_pages($a = array(),$echo=true,$reference_ID=false) {
	return yarpp_related(array('page'),$a,$echo,$reference_ID);
}

function related_entries($a = array(),$echo=true,$reference_ID=false) {
	return yarpp_related(array('page','post'),$a,$echo,$reference_ID);
}

function related_posts_exist($a = array(),$reference_ID=false) {
	return yarpp_related_exist(array('post'),$a,$reference_ID);
}

function related_pages_exist($a = array(),$reference_ID=false) {
	return yarpp_related_exist(array('page'),$a,$reference_ID);
}

function related_entries_exist($a = array(),$reference_ID=false) {
	return yarpp_related_exist(array('page','post'),$a,$reference_ID);
}
