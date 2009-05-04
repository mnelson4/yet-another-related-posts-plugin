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
	global $wpdb;
	return yarpp_extract_keywords(html_entity_strip($wpdb->get_var("select post_title from $wpdb->posts where ID = $ID")),$max);
}

function html_entity_strip($html) {
	$html = preg_replace('/&#x[0-9a-f]+;/','',$html);
	$html = preg_replace('/&#[0-9]+;/','',$html);
	$html = preg_replace('/&[a-zA-Z]+;/','',$html);
	return $html;
}

function post_body_keywords($ID,$max = 20) {
	global $wpdb;
	$content = strip_tags(apply_filters_if_white('the_content',$wpdb->get_var("select post_content from $wpdb->posts where ID = $ID")));
	//echo "<!--".get_option('blog_charset')."-->";
	/*if (get_option('blog_charset') == 'UTF-8')
		$content = html_entity_decode_utf8($content);
	else
		$content = html_entity_decode($content,ENT_QUOTES,get_option('blog_charset'));*/
	$content = html_entity_strip($content);
	return yarpp_extract_keywords($content,$max);
}

function yarpp_cache_keywords($ID) {
	global $wpdb, $yarpp_debug;
    $body_terms = post_body_keywords($ID);
    $title_terms = post_title_keywords($ID);
	/*
	CREATE TABLE `wp_yarpp_keyword_cache` (
	`ID` BIGINT( 20 ) UNSIGNED NOT NULL ,
	`body` TEXT NOT NULL ,
	`title` TEXT NOT NULL ,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	PRIMARY KEY ( `ID` )
	) ENGINE = MYISAM COMMENT = 'YARPP\'s keyword cache table' 
	*/
	
	if (defined('DB_CHARSET')) {
  	$wpdb->query('set names '.DB_CHARSET);
	}
	
	$wpdb->query("insert into {$wpdb->prefix}yarpp_keyword_cache (ID,body,title) values ($ID,'$body_terms ','$title_terms ') on duplicate key update date = now(), body = '$body_terms ', title = '$title_terms '");
	
	//echo "<!--"."insert into {$wpdb->prefix}yarpp_keyword_cache (ID,body,title) values ($ID,'$body_terms','$title_terms') on duplicate key update date = now(), body = '$body_terms', title = '$title_terms'"."-->";

}

function yarpp_get_cached_keywords($ID,$type='body') {
	global $wpdb;
	$out = $wpdb->get_var("select $type from {$wpdb->prefix}yarpp_keyword_cache where ID = $ID");
	if ($out === false or $out == '')
		yarpp_cache_keywords($ID);
	$out = $wpdb->get_var("select $type from {$wpdb->prefix}yarpp_keyword_cache where ID = $ID");
	if ($out === false or $out == '') {
		//echo "<!--YARPP ERROR: couldn't select/create yarpp $type keywords for $ID-->";
    return false;
	} else {
		return $out;
  }
}

// replacement html_entity_decode code from php.net
// author: laurynas dot butkus at gmail dot com

function html_entity_decode_utf8($string) {
    static $trans_tbl;
    
    // replace numeric entities
    $string = preg_replace('~&#x([0-9a-f]+);~ei', 'code2utf(hexdec("\\1"))', $string);
    $string = preg_replace('~&#([0-9]+);~e', 'code2utf(\\1)', $string);

    // replace literal entities
    if (!isset($trans_tbl))
    {
        $trans_tbl = array();
        
        foreach (get_html_translation_table(HTML_ENTITIES) as $val=>$key)
            $trans_tbl[$key] = utf8_encode($val);
    }
    
    return strtr($string, $trans_tbl);
}

// Returns the utf string corresponding to the unicode value (from php.net, courtesy - romans@void.lv)
function code2utf($num)
{
    if ($num < 128) return chr($num);
    if ($num < 2048) return chr(($num >> 6) + 192) . chr(($num & 63) + 128);
    if ($num < 65536) return chr(($num >> 12) + 224) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    if ($num < 2097152) return chr(($num >> 18) + 240) . chr((($num >> 12) & 63) + 128) . chr((($num >> 6) & 63) + 128) . chr(($num & 63) + 128);
    return '';
}

?>