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

function yarpp_sql($type,$args,$giveresults = true,$domain='website') {
	global $wpdb, $post;

	// set the "domain prefix", used for all the preferences.
	if ($domain == 'rss')
		$domainprefix = 'rss_';
	else
		$domainprefix = '';

	$options = array('limit'=>"${domainprefix}limit",
		'order'=>"${domainprefix}order",
		'threshold'=>'threshold',
		'show_excerpt'=>"${domainprefix}show_excerpt",
		'excerpt_length'=>"${domainprefix}excerpt_length",
		'show_pass_post'=>'show_pass_post',
		'past_only'=>'past_only',
		'cross_relate'=>'cross_relate',
		'body'=>'body',
		'title'=>'title',
		'tags'=>'tags',
		'categories'=>'categories',
		'distags'=>'distags',
		'discats'=>'discats');
	$optvals = array();
	foreach (array_keys($options) as $option) {
		if (isset($args[$option])) {
			$optvals[$option] = stripslashes($args[$option]);
		} else {
			$optvals[$option] = stripslashes(stripslashes(yarpp_get_option($options[$option])));
		}
	}

	extract($optvals);

	// if cross_relate is set, override the type argument and make sure both matches are accepted in the sql query
	if ($cross_relate) $type = array('post','page');

	// Fetch keywords
    $body_terms = post_body_keywords();
    $title_terms = post_title_keywords();
    
	// Make sure the post is not from the future
	$time_difference = get_settings('gmt_offset');
	$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));
	
	// get weights
	
	$bodyweight = (($body == 3)?3:(($body == 2)?1:0));
	$titleweight = (($title == 3)?3:(($title == 2)?1:0));
	$tagweight = (($tags != 1)?1:0);
	$catweight = (($categories != 1)?1:0);
	$weights = array();
	$weights['body'] = $bodyweight;
	$weights['title'] = $titleweight;
	$weights['cat'] = $catweight;
	$weights['tag'] = $tagweight;
	
	$totalweight = $bodyweight + $titleweight + $tagweight + $catweight;
	
	$weightedthresh = $threshold/($totalweight + 0.1);
	
	// get disallowed categories and tags
	
	$disterms = implode(',', array_filter(array_merge(explode(',',$discats),explode(',',$distags)),'is_numeric'));

	$usedisterms = count(array_filter(array_merge(explode(',',$discats),explode(',',$distags)),'is_numeric'));

	$criteria = array();
	if ($bodyweight)
		$criteria['body'] = "(MATCH (post_content) AGAINST ('".post_body_keywords()."'))";
	if ($titleweight)
		$criteria['title'] = "(MATCH (post_title) AGAINST ('".post_title_keywords()."'))";
	if ($tagweight)
		$criteria['tag'] = "COUNT( DISTINCT tagtax.term_taxonomy_id )";
	if ($catweight)
		$criteria['cat'] = "COUNT( DISTINCT cattax.term_taxonomy_id )";

	$newsql = "SELECT ID, post_title, post_date, post_content, ";

	foreach ($criteria as $key => $value) {
		$newsql .= "$value as ${key}score, ";
	}

	$newsql .= '(0';
	foreach ($criteria as $key => $value) {
		$newsql .= "+ $value * ".$weights[$key];
	}
	$newsql .= ') as score';

	if ($usedisterms)
	$newsql .= ", count(blockterm.term_id) as block";
	
	$newsql .= "\n from $wpdb->posts \n";

	if ($usedisterms)
		$newsql .= " left join $wpdb->term_relationships as blockrel on ($wpdb->posts.ID = blockrel.object_id)
		left join $wpdb->term_taxonomy as blocktax using (`term_taxonomy_id`)
		left join $wpdb->terms as blockterm on (blocktax.term_id = blockterm.term_id and blockterm.term_id in ($disterms))\n";

	if ($tagweight)
		$newsql .= " left JOIN $wpdb->term_relationships AS thistag ON (thistag.object_id = $post->ID ) 
		left JOIN $wpdb->term_relationships AS tagrel on (tagrel.term_taxonomy_id = thistag.term_taxonomy_id
		AND tagrel.object_id = $wpdb->posts.ID)
		left JOIN $wpdb->term_taxonomy AS tagtax ON ( tagrel.term_taxonomy_id = tagtax.term_taxonomy_id
		AND tagtax.taxonomy = 'post_tag')\n";

	if ($catweight)
		$newsql .= " left JOIN $wpdb->term_relationships AS thiscat ON (thiscat.object_id = $post->ID ) 
		left JOIN $wpdb->term_relationships AS catrel on (catrel.term_taxonomy_id = thiscat.term_taxonomy_id
		AND catrel.object_id = $wpdb->posts.ID)
		left JOIN $wpdb->term_taxonomy AS cattax ON ( catrel.term_taxonomy_id = cattax.term_taxonomy_id
		AND cattax.taxonomy = 'category')\n";

	// WHERE
	
	$newsql .= " where (post_status IN ( 'publish',  'static' ) and ID != '$post->ID')";

	if ($past_only)
		$newsql .= " and post_date <= '$now' ";
	if (!$show_pass_post)
		$newsql .= " and post_password ='' ";

	$newsql .= " and post_type IN ('".implode("', '",$type)."')";

	// GROUP BY
	$newsql .= "\n group by id \n";
	// HAVING
	$newsql .= " having score >= $threshold";
	if ($usedisterms)
		$newsql .= " and block = 0";

	$newsql .= (($categories == 3)?' and catscore >= 1':'');
	$newsql .= (($categories == 4)?' and catscore >= 2':'');
	$newsql .= (($tags == 3)?' and tagscore >= 1':'');
	$newsql .= (($tags == 4)?' and tagscore >= 2':'');
	$newsql .= " order by ".(($order?$order:"score desc"))." limit ".$limit;

	if (!$giveresults) {
		$newsql = "select count(t.ID) from ($newsql) as t";
	}

	//echo "<!--$newsql-->";
	return $newsql;
}

/* new in 2.1! the domain argument refers to {website,widget,rss}, though widget is not used yet. */

function yarpp_related($type,$args,$echo = true,$domain = 'website') {
	global $wpdb, $post, $userdata;
	get_currentuserinfo();

	// set the "domain prefix", used for all the preferences.
	if ($domain == 'rss')
		$domainprefix = 'rss_';
	else
		$domainprefix = '';

	// get options
	// note the 2.1 change... the options array changed from what you might call a "list" to a "hash"... this changes the structure of the $args to something which is, in the long term, much more useful
	$options = array(
		'before_related'=>"${domainprefix}before_related",
		'after_related'=>"${domainprefix}after_related",
		'before_title'=>"${domainprefix}before_title",
		'after_title'=>"${domainprefix}after_title",
		'show_excerpt'=>"${domainprefix}show_excerpt",
		'excerpt_length'=>"${domainprefix}excerpt_length",
		'before_post'=>"${domainprefix}before_post",
		'after_post'=>"${domainprefix}after_post",
		'no_results'=>"${domainprefix}no_results",
		'promote_yarpp'=>"${domainprefix}promote_yarpp",
		'show_score'=>'show_score');
	$optvals = array();
	foreach (array_keys($options) as $option) {
		if (isset($args[$option])) {
			$optvals[$option] = stripslashes($args[$option]);
		} else {
			$optvals[$option] = stripslashes(stripslashes(yarpp_get_option($options[$option])));
		}
	}
	extract($optvals);
	
    $results = $wpdb->get_results(yarpp_sql($type,$args,true,$domain));
    $output = '';
    if ($results) {
		foreach ($results as $result) {
			$title = stripslashes(apply_filters('the_title', $result->post_title));
			$permalink = get_permalink($result->ID);
			$post_content = strip_tags($result->post_content);
			$post_content = stripslashes($post_content);
			$output .= "$before_title<a href='$permalink' rel='bookmark' title='Permanent Link: $title'>$title";
			if ($show_score and $userdata->user_level >= 8 and $domain != 'rss')
				$output .= ' <abbr title="'.round($result->score,3).' is the YARPP match score between the current entry and this related entry. You are seeing this value because you are logged in to WordPress as an administrator. It is not shown to regular visitors.">('.round($result->score,3).')</abbr>';
			$output .= '</a>';
			if ($show_excerpt) {
				$output .= $before_post . yarpp_excerpt($post_content,$excerpt_length) . $after_post;
			}
			$output .=  $after_title;
		}
		$output = stripslashes(stripslashes($before_related)).$output.stripslashes(stripslashes($after_related));
		if ($promote_yarpp)
			$output .= "\n<p>Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.</p>";

	} else {
		$output = $no_results;
    }
	if ($echo) echo $output; else return ((!empty($output))?"\n\n":'').$output;
}

function yarpp_related_exist($type,$args) {
	global $wpdb, $post;

	$options = array('threshold'=>'threshold','show_pass_post'=>'show_pass_post','past_only'=>'past_only');
	$optvals = array();
	foreach (array_keys($options) as $option) {
		if (isset($args[$option])) {
			$optvals[$option] = stripslashes($args[$option]);
		} else {
			$optvals[$option] = stripslashes(stripslashes(yarpp_get_option($options[$option])));
		}
	}
	extract($optvals);

    $result = $wpdb->get_var(yarpp_sql($type,$args,false,$domain));
	return $result > 0 ? true: false;
}

?>