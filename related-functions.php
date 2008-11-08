<?php

// Here are the related_WHATEVER functions, as introduced in 1.1, which actually just use the yarpp_related and yarpp_related_exist functions.

// Since YARPP 2.1, these functions receive (optionally) one array argument. See the documentation for instructions on how to customize their output.

function related_posts($a = array()) {
	return yarpp_related(array('post'),$a);
}

function related_pages($a = array()) {
	return yarpp_related(array('page'),$a);
}

function related_entries($a = array()) {
	return yarpp_related(array('page','post'),$a);
}

function related_posts_exist($a = array()) {
	return yarpp_related_exist(array('post'),$a);
}

function related_pages_exist($a = array()) {
	return yarpp_related_exist(array('page'),$a);
}

function related_entries_exist($a = array()) {
	return yarpp_related_exist(array('page','post'),$a);
}

?>