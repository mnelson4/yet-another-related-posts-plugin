<?php

function yarpp_extract_keywords($source,$num_to_ret = 20) {
	global $post, $overusedwords;
	
	if (function_exists('mb_split')) {
		mb_regex_encoding(get_option('blog_charset'));
		$wordlist = mb_split('\s*\W+\s*', mb_strtolower($source));
	} else
		$wordlist = preg_split('%\s*\W+\s*%', strtolower($source));	

	// Build an array of the unique words and number of times they occur.
	$a = array_count_values($wordlist);
	
	// Remove the stop words from the list.
	foreach ($overusedwords as $word) {
		 unset($a[$word]);
	}
	arsort($a, SORT_NUMERIC);
	
	$num_words = count($a);
	$num_to_ret = $num_words > $num_to_ret ? $num_to_ret : $num_words;
	
	$outwords = array_slice($a, 0, $num_to_ret);
	return implode(' ', array_keys($outwords));
}

function post_title_keywords($max = 20) {
	global $post;
	return yarpp_extract_keywords($post->post_title,$max);
}

function post_body_keywords($max = 20) {
	global $post;
	$content = strip_tags(apply_filters_if_white('the_content',$post->post_content));
	return yarpp_extract_keywords($content,$max);
}

/* yarpp_cache_keywords is EXPERIMENTAL and not used.
*  Don't worry about it. ^^ 
*/
function yarpp_cache_keywords() {
	global $wpdb, $post, $yarpp_debug;
    $body_terms = post_body_keywords();
    $title_terms = post_title_keywords();
	/*
	CREATE TABLE `wp_yarpp_keyword_cache` (
	`ID` BIGINT( 20 ) UNSIGNED NOT NULL ,
	`body` TEXT NOT NULL ,
	`title` TEXT NOT NULL ,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	PRIMARY KEY ( `ID` )
	) ENGINE = MYISAM COMMENT = 'YARPP\'s keyword cache table' 
	*/
	$timeout = 400;

	if (!$wpdb->get_var("select count(*) as count from wp_yarpp_keyword_cache where ID = $post->ID and date > date_sub(now(),interval $timeout minute)")) {
		$wpdb->query('set names utf8');
	
		$wpdb->query("insert into wp_yarpp_keyword_cache (ID,body,title) values ($post->ID,'$body_terms','$title_terms') on duplicate key update body = '$body_terms', title = '$title_terms'");
	
		if ($yarpp_debug) echo "<!--"."insert into wp_yarpp_keyword_cache (ID,body,title) values ($post->ID,'$body_terms','$title_terms') on duplicate key update body = '$body_terms', title = '$title_terms'"."-->";
	}
}

?>