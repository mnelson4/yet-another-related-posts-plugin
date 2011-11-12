<?php

function yarpp_extract_keywords($html, $max = 20) {

	$lang = 'en_US';
	if ( defined('WPLANG') ) {
		$lang = substr(WPLANG, 0, 2);
		switch ( $lang ) {
			case 'de':
				$lang = 'de_DE';
			case 'it':
				$lang = 'it_IT';
			case 'pl':
				$lang = 'pl_PL';
			case 'bg':
				$lang = 'bg_BG';
			case 'fr':
				$lang = 'fr_FR';
			case 'cs':
				$lang = 'cs_CZ';
			case 'nl':
				$lang = 'nl_NL';
		}
	}

	$words_file = YARPP_DIR . '/lang/words-' . $lang . '.php';
	if ( file_exists($words_file) )
		include( $words_file );
	if ( !isset($overusedwords) )
		$overusedwords = array();

	// strip tags and html entities
	$text = preg_replace('/&(#x[0-9a-f]+|#[0-9]+|[a-zA-Z]+);/', '', strip_tags($html) );

	// 3.2.2: ignore soft hyphens
	// Requires PHP 5: http://bugs.php.net/bug.php?id=25670
	$softhyphen = html_entity_decode('&#173;',ENT_NOQUOTES,'UTF-8');
	$text = str_replace($softhyphen, '', $text);

	$charset = get_option('blog_charset');
	if ( function_exists('mb_split') && !empty($charset) ) {
		mb_regex_encoding($charset);
		$wordlist = mb_split('\s*\W+\s*', mb_strtolower($text, $charset));
	} else
		$wordlist = preg_split('%\s*\W+\s*%', strtolower($text));

	// Build an array of the unique words and number of times they occur.
	$tokens = array_count_values($wordlist);

	// Remove the stop words from the list.
	$overusedwords = apply_filters( 'yarpp_keywords_overused_words', $overusedwords );
	if ( is_array($overusedwords) ) {
		foreach ($overusedwords as $word) {
			 unset($tokens[$word]);
		}
	}
	// Remove words which are only a letter
	$mb_strlen_exists = function_exists('mb_strlen');
	foreach (array_keys($tokens) as $word) {
		if ($mb_strlen_exists)
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
	return yarpp_extract_keywords(get_the_title($ID),$max);
}

function post_body_keywords( $ID, $max = 20 ) {
	$post = get_post( $ID );
	if ( empty($post) )
		return '';
	$content = apply_filters_if_white( 'the_content', $post->post_content );
	return yarpp_extract_keywords( $content, $max );
}

/* new in 2.0! apply_filters_if_white (previously apply_filters_without) now has a blacklist.
 * It can be modified via the yarpp_blacklist and yarpp_blackmethods filters.
 */
/* blacklisted so far:
	- diggZ-Et
	- reddZ-Et
	- dzoneZ-Et
	- WP-Syntax
	- Viper's Video Quicktags
	- WP-CodeBox
	- WP shortcodes
	- WP Greet Box
	//- Tweet This - could not reproduce problem.
*/
function yarpp_white( $filter ) {
	static $blacklist, $blackmethods;

	if ( is_null($blacklist) || is_null($blackmethods) ) {
		$yarpp_blacklist = array('yarpp_default', 'diggZEt_AddBut', 'reddZEt_AddBut', 'dzoneZEt_AddBut', 'wp_syntax_before_filter', 'wp_syntax_after_filter', 'wp_codebox_before_filter', 'wp_codebox_after_filter', 'do_shortcode');//,'insert_tweet_this'
		$yarpp_blackmethods = array('addinlinejs', 'replacebbcode', 'filter_content');
	
		$blacklist = (array) apply_filters( 'yarpp_blacklist', $yarpp_blacklist );
		$blackmethods = (array) apply_filters( 'yarpp_blackmethods', $yarpp_blackmethods );
	}
	
	if ( is_array($filter) && in_array( $filter[1], $blackmethods ) )
		return false;
	return !in_array( $filter, $blacklist );
}

/* FYI, apply_filters_if_white was used here to avoid a loop in apply_filters('the_content') > yarpp_default() > yarpp_related() > current_post_keywords() > apply_filters('the_content').*/
function apply_filters_if_white($tag, $value) {
	global $wp_filter, $merged_filters, $wp_current_filter;

	$args = array();

	// Do 'all' actions first
	if ( isset($wp_filter['all']) ) {
		$wp_current_filter[] = $tag;
		$args = func_get_args();
		_wp_call_all_hook($args);
	}

	if ( !isset($wp_filter[$tag]) ) {
		if ( isset($wp_filter['all']) )
			array_pop($wp_current_filter);
		return $value;
	}

	if ( !isset($wp_filter['all']) )
		$wp_current_filter[] = $tag;

	// Sort
	if ( !isset( $merged_filters[ $tag ] ) ) {
		ksort($wp_filter[$tag]);
		$merged_filters[ $tag ] = true;
	}

	reset( $wp_filter[ $tag ] );

	if ( empty($args) )
		$args = func_get_args();

	do {
		foreach( (array) current($wp_filter[$tag]) as $the_ )
			if ( !is_null($the_['function'])
			and yarpp_white($the_['function'])){ // HACK
				$args[1] = $value;
				$value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
			}

	} while ( next($wp_filter[$tag]) !== false );

	array_pop( $wp_current_filter );

	return $value;
}
