<?php

$overusedwords = array( '', 'a', 'an', 'the', 'and', 'of', 'i', 'to', 'is', 'in', 'with', 'for', 'as', 'that', 'on', 'at', 'this', 'my', 'was', 'our', 'it', 'you', 'we', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '10', 'about', 'after', 'all', 'almost', 'along', 'also', 'amp', 'another', 'any', 'are', 'area', 'around', 'available', 'back', 'be', 'because', 'been', 'being', 'best', 'better', 'big', 'bit', 'both', 'but', 'by', 'c', 'came', 'can', 'capable', 'control', 'could', 'course', 'd', 'dan', 'day', 'decided', 'did', 'didn', 'different', 'div', 'do', 'doesn', 'don', 'down', 'drive', 'e', 'each', 'easily', 'easy', 'edition', 'end', 'enough', 'even', 'every', 'example', 'few', 'find', 'first', 'found', 'from', 'get', 'go', 'going', 'good', 'got', 'gt', 'had', 'hard', 'has', 'have', 'he', 'her', 'here', 'how', 'if', 'into', 'isn', 'just', 'know', 'last', 'left', 'li', 'like', 'little', 'll', 'long', 'look', 'lot', 'lt', 'm', 'made', 'make', 'many', 'mb', 'me', 'menu', 'might', 'mm', 'more', 'most', 'much', 'name', 'nbsp', 'need', 'new', 'no', 'not', 'now', 'number', 'off', 'old', 'one', 'only', 'or', 'original', 'other', 'out', 'over', 'part', 'place', 'point', 'pretty', 'probably', 'problem', 'put', 'quite', 'quot', 'r', 're', 'really', 'results', 'right', 's', 'same', 'saw', 'see', 'set', 'several', 'she', 'sherree', 'should', 'since', 'size', 'small', 'so', 'some', 'something', 'special', 'still', 'stuff', 'such', 'sure', 'system', 't', 'take', 'than', 'their', 'them', 'then', 'there', 'these', 'they', 'thing', 'things', 'think', 'those', 'though', 'through', 'time', 'today', 'together', 'too', 'took', 'two', 'up', 'us', 'use', 'used', 'using', 've', 'very', 'want', 'way', 'well', 'went', 'were', 'what', 'when', 'where', 'which', 'while', 'white', 'who', 'will', 'would', 'your');

function post_title_keywords($num_to_ret = 20) {
	global $post, $overusedwords;
	$wordlist = preg_split('/\s*[\s+\.|\?|,|(|)|\-+|\'|\"|=|;|&#0215;|\$|\/|:|{|}]\s*/i', strtolower($post->post_title));

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

// post_body_keywords()
// Based on create_post_keywords by Peter Bowyer with major changes by mitcho
/**
 * Builds a word frequency list from the Wordpress post, and returns a string
 * to be used in matching against the MySQL full-text index.
 *
 * @param integer $num_to_ret The number of words to use when matching against
 * 							  the database.
 * @return string The words
 */
function post_body_keywords($num_to_ret = 20) {
	global $post, $overusedwords;
	
	$string = strip_tags(apply_filters_if_white('the_content',$post->post_content));
	
	// Remove punctuation and split
	$wordlist = preg_split('/\s*[\s+\.|\?|,|(|)|\-+|\'|\"|=|;|&#0215;|\$|\/|:|{|}]\s*/i', strtolower($string));
	
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

function yarpp_sql($options_array,$giveresults = true) {
	global $wpdb, $post;

	extract($options_array);

	// if cross_relate is set, override the type argument and make sure both matches are accepted in the sql query
	if (get_option('yarpp_cross_relate')) $type = array('post','page');

	// Fetch keywords
    $body_terms = post_body_keywords();
    $title_terms = post_title_keywords();
    
	// Make sure the post is not from the future
	$time_difference = get_settings('gmt_offset');
	$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));
	
	// get weights
	
	$bodyweight = ((get_option('yarpp_body') == 3)?3:((get_option('yarpp_body') == 2)?1:0));
	$titleweight = ((get_option('yarpp_title') == 3)?3:((get_option('yarpp_title') == 2)?1:0));
	$tagweight = ((get_option('yarpp_tags') != 1)?1:0);
	$catweight = ((get_option('yarpp_categories') != 1)?1:0);
	
	$totalweight = $bodyweight + $titleweight + $tagweight + $catweight;
	
	$weightedthresh = $threshold/($totalweight + 0.1);
	
	// get disallowed categories and tags
	
	$disterms = implode(',', array_filter(array_merge(explode(',',get_option('yarpp_discats')),explode(',',get_option('yarpp_distags'))),'is_numeric'));

	$sql = "SELECT *, (bodyscore * $bodyweight + titlescore * $titleweight + tagscore * $tagweight + catscore * $catweight) AS score
	from (
		select ID, post_title, post_date, post_content, (MATCH (post_content) AGAINST ('".post_body_keywords()."')) as bodyscore, (MATCH (post_title) AGAINST ('".post_title_keywords()."')) as titlescore, ifnull(catscore,0) as catscore, ifnull(tagscore,0) as tagscore
		from $wpdb->posts "
	.(count(array_filter(array_merge(explode(',',get_option('yarpp_discats')),explode(',',get_option('yarpp_distags'))),'is_numeric'))?"	left join (
			select count(*) as block, object_id from $wpdb->term_relationships natural join $wpdb->term_taxonomy natural join $wpdb->terms
			where $wpdb->terms.term_id in ($disterms)
			group by object_id
		) as poolblock on ($wpdb->posts.ID = poolblock.object_id)":'')
	."	left join (
			select count(*) as tagscore, object_id from $wpdb->term_relationships natural join $wpdb->term_taxonomy
			where $wpdb->term_taxonomy.taxonomy = 'post_tag'
			and $wpdb->term_taxonomy.term_taxonomy_id in (select term_taxonomy_id from $wpdb->term_relationships where object_id = '$post->ID')
			group by object_id
		) as matchtags on ($wpdb->posts.ID = matchtags.object_id)
		left join (
			select count(*) as catscore, object_id from $wpdb->term_relationships natural join $wpdb->term_taxonomy
			where $wpdb->term_taxonomy.taxonomy = 'category'
			and $wpdb->term_taxonomy.term_taxonomy_id in (select term_taxonomy_id from $wpdb->term_relationships where object_id = '$post->ID')
			group by object_id
		) as matchcats on ($wpdb->posts.ID = matchcats.object_id)
		where ((post_status IN ( 'publish',  'static' ) && ID != '$post->ID')"
.($past_only ?" and post_date <= '$now' ":' ')
.((!$show_pass_post)?" and post_password ='' ":' ')
."			and post_type IN ('".implode("', '",$type)."')"
.(count(array_filter(array_merge(explode(',',get_option('yarpp_discats')),explode(',',get_option('yarpp_distags'))),'is_numeric'))?"			and block IS NULL":'').
"		)
	) as rawscores";

	$sql .= " where (bodyscore * $bodyweight + titlescore * $titleweight + tagscore * $tagweight + catscore * $catweight) >= $threshold"
.((get_option('yarpp_categories') == 3)?' and catscore >= 1':'')
.((get_option('yarpp_categories') == 4)?' and catscore >= 2':'')
.((get_option('yarpp_tags') == 3)?' and tagscore >= 1':'')
.((get_option('yarpp_tags') == 4)?' and tagscore >= 2':'')
." order by ".((get_option('yarpp_order')?get_option('yarpp_order'):"score desc"))." limit $limit";

	//echo $sql;

	if (!$giveresults) {
		$sql = 'select count(*) from ('.$sql.')';
	}

	return $sql;
}

function yarpp_related($type,$args,$echo = true) {
	global $wpdb, $post, $user_level;
	get_currentuserinfo();

	// get options
	$options = array('limit','threshold','before_title','after_title','show_excerpt','excerpt_length','before_post','after_post','show_pass_post','past_only','show_score');
	$optvals = array();
	foreach (array_keys($options) as $index) {
		if (isset($args[$index+1])) {
			$optvals[$options[$index]] = stripslashes($args[$index+1]);
		} else {
			$optvals[$options[$index]] = stripslashes(stripslashes(get_option('yarpp_'.$options[$index])));
		}
	}
	extract($optvals);
	$optvals['type'] = $type;
	
	// Primary SQL query
	
    $results = $wpdb->get_results(yarpp_sql($optvals));
    $output = '';
    if ($results) {
		foreach ($results as $result) {
			$title = stripslashes(apply_filters('the_title', $result->post_title));
			$permalink = get_permalink($result->ID);
			$post_content = strip_tags($result->post_content);
			$post_content = stripslashes($post_content);
			$output .= $before_title .'<a href="'. $permalink .'" rel="bookmark" title="Permanent Link: ' . $title . '">' . $title . (($show_score and $user_level >= 8)? ' ('.round($result->score,3).')':'') . '</a>';
			if ($show_excerpt) {
				$output .= $before_post . yarpp_excerpt($post_content,$excerpt_length) . $after_post;
			}
			$output .=  $after_title;
		}
		$output = stripslashes(stripslashes(get_option('yarpp_before_related'))).$output.stripslashes(stripslashes(get_option('yarpp_after_related')));
		if (get_option('yarpp_promote_yarpp'))
			$output .= "\n<p>Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.</p>";

	} else {
		$output = get_option('yarpp_no_results');
    }
	if ($echo) echo $output; else return $output;
}

function yarpp_related_exist($type,$args) {
	global $wpdb, $post;

	$options = array('threshold','show_pass_post','past_only');
	$optvals = array();
	foreach (array_keys($options) as $index) {
		if (isset($args[$index+1])) {
			$optvals[$options[$index]] = stripslashes($args[$index+1]);
		} else {
			$optvals[$options[$index]] = stripslashes(stripslashes(get_option('yarpp_'.$options[$index])));
		}
	}
	extract($optvals);
	$optvals['type'] = $type;

    $result = $wpdb->get_var(yarpp_sql($optvals,false));
	return $result > 0 ? true: false;
}

?>