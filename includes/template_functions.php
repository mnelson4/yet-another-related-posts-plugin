<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// two YARPP-specific Template Tags, to be used in the YARPP-template Loop.

function yarpp_the_score() {
	echo get_the_score();
}

function yarpp_get_the_score() {
	global $post;

	$score = (float) $post->score;
	return apply_filters('get_the_score', $score);
}


/**
 * Only define `the_score` (and the other non-namespaced functions) for backward-compatibility with code that's
 * already using them, and if they won't conflict with other plugins. Remember this gets loaded on 'init' priority 10
 * so if another plugin defines them, they probably have already done so.
 */
if (! function_exists('the_score')){
    function the_score(){
        yarpp_the_score();
    }
}

if (! function_exists('get_the_score')){
    function get_the_score(){
        return yarpp_get_the_score();
    }
}