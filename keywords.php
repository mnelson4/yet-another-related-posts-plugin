<?php

function yarpp_extract_keywords($source,$max = 20) {
	global $overusedwords;

	if (function_exists('mb_split')) {
		mb_regex_encoding(get_option('blog_charset'));
		$wordlist = mb_split('\s*\W+\s*', mb_strtolower($source));
	} else
		$wordlist = preg_split('%\s*\W+\s*%', strtolower($source));

	// Build an array of the unique words and number of times they occur.
	$tokens = array_count_values($wordlist);

	// Remove the stop words from the list.
	foreach ($overusedwords as $word) {
		 unset($tokens[$word]);
	}
	// Remove words which are only a letter
	foreach (array_keys($tokens) as $word) {
		if (function_exists('mb_strlen'))
			if (mb_strlen($word) < 2) unset($tokens[$word]);
		else
			if (strlen($word) < 2) unset($tokens[$word]);
	}

	arsort($tokens, SORT_NUMERIC);

	$types = array_keys($tokens);

	if (count($types) > $max)
		$types = array_slice($types, 0, $max);
	return implode(' ', $types);
}

function post_title_keywords($ID,$max = 20) {
	return yarpp_extract_keywords(html_entity_strip(get_the_title($ID)),$max);
}

function html_entity_strip($html) {
	$html = preg_replace('/&#x[0-9a-f]+;/','',$html);
	$html = preg_replace('/&#[0-9]+;/','',$html);
	$html = preg_replace('/&[a-zA-Z]+;/','',$html);
	return $html;
}

function post_body_keywords($ID,$max = 20) {
	$posts = get_posts(array('p'=>$ID));
	if (count($posts) != 1)
		return '';
	$content = strip_tags(apply_filters_if_white('the_content',$posts[0]->post_content));
	$content = html_entity_strip($content);
	return yarpp_extract_keywords($content,$max);
}
