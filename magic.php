<?php

/* yarpp_cache_keywords is EXPERIMENTAL and not used.
*  Don't worry about it. ^^ 
*/
function yarpp_cache_keywords() {
	global $wpdb, $post, $yarpp_debug;
    $body_terms = post_body_keywords();
    $title_terms = post_title_keywords();
	/*
	CREATE TABLE `mitcho_wrdp1`.`wp_yarpp_keyword_cache` (
	`ID` BIGINT( 20 ) UNSIGNED NOT NULL ,
	`body` TEXT NOT NULL ,
	`title` TEXT NOT NULL ,
	`date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ,
	PRIMARY KEY ( `ID` )
	) ENGINE = MYISAM COMMENT = 'YARPP''s keyword cache table' 
	*/
	$timeout = 400;

	if ($yarpp_debug) echo '<!--'.$wpdb->get_var("select count(*) as count from wp_yarpp_keyword_cache where ID = $post->ID and date > date_sub(now(),interval $timeout minute)").'-->';

	if ($wpdb->get_var("select count(*) as count from wp_yarpp_keyword_cache where ID = $post->ID and date > date_sub(now(),interval $timeout minute)") == 0) {
		$wpdb->query('set names utf8');
	
		$wpdb->query("insert into wp_yarpp_keyword_cache (ID,body,title) values ($post->ID,'$body_terms','$title_terms') on duplicate key update body = '$body_terms', title = '$title_terms'");
	
		if ($yarpp_debug) echo "<!--"."insert into wp_yarpp_keyword_cache (ID,body,title) values ($post->ID,'$body_terms','$title_terms') on duplicate key update body = '$body_terms', title = '$title_terms'"."-->";
	}
}

function yarpp_sql($type,$args,$giveresults = true,$domain='website') {
	global $wpdb, $post, $yarpp_debug;

	// set $yarpp_debug
	if (isset($_REQUEST['yarpp_debug']))
		$yarpp_debug = true;

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

	//yarpp_cache_keywords();

	// Fetch keywords
    $body_terms = post_body_keywords();
    $title_terms = post_title_keywords();
    
    if ($yarpp_debug) echo "<!--TITLE TERMS: $title_terms-->"; // debug
    if ($yarpp_debug) echo "<!--BODY TERMS: $body_terms-->"; // debug
    
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
		$criteria['body'] = "(MATCH (post_content) AGAINST ('$body_terms'))";
	if ($titleweight)
		$criteria['title'] = "(MATCH (post_title) AGAINST ('$title_terms'))";
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

	if ($yarpp_debug) echo "<!--$newsql-->";
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
				$output .= ' <abbr title="'.sprintf(__('%f is the YARPP match score between the current entry and this related entry. You are seeing this value because you are logged in to WordPress as an administrator. It is not shown to regular visitors.','yarpp'),round($result->score,3)).'">('.round($result->score,3).')</abbr>';
			$output .= '</a>';
			if ($show_excerpt) {
				$output .= $before_post . yarpp_excerpt($post_content,$excerpt_length) . $after_post;
			}
			$output .=  $after_title;
		}
		$output = stripslashes(stripslashes($before_related)).$output.stripslashes(stripslashes($after_related));
		if ($promote_yarpp)
			$output .= "\n<p>".__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp')."</p>";

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