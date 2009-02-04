<?php

function yarpp_sql($type,$args,$giveresults = true,$reference_ID=false,$domain='website') {
	global $wpdb, $post, $yarpp_debug;

	if (is_object($post) and !$reference_ID) {
		$reference_ID = $post->ID;
	}
	
	// set $yarpp_debug
	if (isset($_REQUEST['yarpp_debug']))
		$yarpp_debug = true;

	// set the "domain prefix", used for all the preferences.
	if ($domain == 'rss')
		$domainprefix = 'rss_';
	else
		$domainprefix = '';

	$options = array('limit'=>"${domainprefix}limit",
		'threshold'=>'threshold',
		'show_pass_post'=>'show_pass_post',
		'past_only'=>'past_only',
		'cross_relate'=>'cross_relate',
		'body'=>'body',
		'title'=>'title',
		'tags'=>'tags',
		'categories'=>'categories',
		'distags'=>'distags',
		'discats'=>'discats',
		'recent_only'=>'recent_only',
		'recent_number'=>'recent_number',
		'recent_units'=>'recent_units');
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
    $body_terms = yarpp_get_cached_keywords($reference_ID,'body');
    $title_terms = yarpp_get_cached_keywords($reference_ID,'title');
    
    if ($yarpp_debug) echo "<!--TITLE TERMS: $title_terms-->"; // debug
    if ($yarpp_debug) echo "<!--BODY TERMS: $body_terms-->"; // debug
	
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
	
	// get disallowed categories and tags
	
	$disterms = implode(',', array_filter(array_merge(explode(',',$discats),explode(',',$distags)),'is_numeric'));

	$usedisterms = count(array_filter(array_merge(explode(',',$discats),explode(',',$distags)),'is_numeric'));

	$criteria = array();
	if ($bodyweight)
		$criteria['body'] = "(MATCH (post_content) AGAINST ('".$wpdb->escape($body_terms)."'))";
	if ($titleweight)
		$criteria['title'] = "(MATCH (post_title) AGAINST ('".$wpdb->escape($title_terms)."'))";
	if ($tagweight)
		$criteria['tag'] = "COUNT( DISTINCT tagtax.term_taxonomy_id )";
	if ($catweight)
		$criteria['cat'] = "COUNT( DISTINCT cattax.term_taxonomy_id )";

	$newsql = "SELECT $reference_ID, ID, "; //post_title, post_date, post_content, post_excerpt, 

	//foreach ($criteria as $key => $value) {
	//	$newsql .= "$value as ${key}score, ";
	//}

	$newsql .= '(0';
	foreach ($criteria as $key => $value) {
		$newsql .= "+ $value * ".$weights[$key];
	}
	$newsql .= ') as score';
	
	$newsql .= "\n from $wpdb->posts \n";

	if ($usedisterms)
		$newsql .= " left join $wpdb->term_relationships as blockrel on ($wpdb->posts.ID = blockrel.object_id)
		left join $wpdb->term_taxonomy as blocktax using (`term_taxonomy_id`)
		left join $wpdb->terms as blockterm on (blocktax.term_id = blockterm.term_id and blockterm.term_id in ($disterms))\n";

	if ($tagweight)
		$newsql .= " left JOIN $wpdb->term_relationships AS thistag ON (thistag.object_id = $reference_ID ) 
		left JOIN $wpdb->term_relationships AS tagrel on (tagrel.term_taxonomy_id = thistag.term_taxonomy_id
		AND tagrel.object_id = $wpdb->posts.ID)
		left JOIN $wpdb->term_taxonomy AS tagtax ON ( tagrel.term_taxonomy_id = tagtax.term_taxonomy_id
		AND tagtax.taxonomy = 'post_tag')\n";

	if ($catweight)
		$newsql .= " left JOIN $wpdb->term_relationships AS thiscat ON (thiscat.object_id = $reference_ID ) 
		left JOIN $wpdb->term_relationships AS catrel on (catrel.term_taxonomy_id = thiscat.term_taxonomy_id
		AND catrel.object_id = $wpdb->posts.ID)
		left JOIN $wpdb->term_taxonomy AS cattax ON ( catrel.term_taxonomy_id = cattax.term_taxonomy_id
		AND cattax.taxonomy = 'category')\n";

	// WHERE
	
	$newsql .= " where (post_status IN ( 'publish',  'static' ) and ID != '$reference_ID')";

	if ($past_only)
		$newsql .= " and post_date <= NOW() ";
	if (!$show_pass_post)
		$newsql .= " and post_password ='' ";
	if ($recent_only)
		$newsql .= " and post_date > date_sub(now(), interval $recent_number $recent_units) ";

	$newsql .= " and post_type IN ('".implode("', '",$type)."')";

	// GROUP BY
	$newsql .= "\n group by id \n";
	// HAVING
	$safethreshold = max($threshold/2,0.1); // this is so the new calibration system works.
	$newsql .= " having score >= $safethreshold";
	if ($usedisterms)
		$newsql .= " and count(blockterm.term_id) = 0";

	$newsql .= (($categories == 3)?' and '.$criteria['cat'].' >= 1':'');
	$newsql .= (($categories == 4)?' and '.$criteria['cat'].' >= 2':'');
	$newsql .= (($tags == 3)?' and '.$criteria['tag'].' >= 1':'');
	$newsql .= (($tags == 4)?' and '.$criteria['tag'].' >= 2':'');
	$newsql .= " order by score desc limit ".$limit;

	if (!$giveresults) {
		$newsql = "select count(t.ID) from ($newsql) as t";
	}

	if ($yarpp_debug) echo "<!--$newsql-->";
	return $newsql;
}

/* new in 2.1! the domain argument refers to {website,widget,rss}, though widget is not used yet. */

function yarpp_related($type,$args,$echo = true,$reference_ID=false,$domain = 'website') {
	global $wpdb, $post, $userdata, $yarpp_time, $wp_query, $id, $page, $pages;
	
	if ($yarpp_time) // if we're already in a YARPP loop, stop now.
		return false;
	
	if (is_object($post) and !$reference_ID)
		$reference_ID = $post->ID;
	
	get_currentuserinfo();

	// set the "domain prefix", used for all the preferences.
	if ($domain == 'rss')
		$domainprefix = 'rss_';
	else
		$domainprefix = '';

	// get options
	// note the 2.1 change... the options array changed from what you might call a "list" to a "hash"... this changes the structure of the $args to something which is, in the long term, much more useful
	$options = array(
		'use_template'=>"${domainprefix}use_template",
		'order'=>"${domainprefix}order",
		'template_file'=>"${domainprefix}template_file",
		'promote_yarpp'=>"${domainprefix}promote_yarpp");
	$optvals = array();
	foreach (array_keys($options) as $option) {
		if (isset($args[$option])) {
			$optvals[$option] = stripslashes($args[$option]);
		} else {
			$optvals[$option] = stripslashes(stripslashes(yarpp_get_option($options[$option])));
		}
	}
	extract($optvals);
	
	if (yarpp_get_option('ad_hoc_caching') == 1)
		yarpp_cache_enforce($type,$reference_ID);
	
    $output = '';
	
	$yarpp_time = true; // get ready for YARPP TIME!
	// just so we can return to normal later
	$current_query = $wp_query;
	$current_post = $post;
	$current_id = $id;
	$current_page = $page;
	$current_pages = $pages;

	$related_query = new WP_Query();
	$orders = split(' ',$order);
	$related_query->query("p=$reference_ID&orderby=".$orders[0]."&order=".$orders[1]);

	if ($domain == 'metabox') {
		include('template-metabox.php');
	} elseif ($use_template) {
		ob_start();
		include('../yarpp-templates/'.$template_file);
		$output = ob_get_contents();
		ob_end_clean();
	} else {
		include('template-builtin.php');
	}
		
	unset($related_query);
	$yarpp_time = false; // YARPP time is over... :(
	
	// restore the older wp_query.
	$wp_query = null; $wp_query = $current_query; unset($current_query);
	$post = null; $post = $current_post; unset($current_post);
	$pages = null; $pages = $current_pages; unset($current_pages);
	$id = $current_id; unset($current_id);
	$page = $current_page; unset($current_page);
	
	if ($promote_yarpp and $domain != 'metabox')
		$output .= "\n<p>".__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp')."</p>";
	
	if ($echo) echo $output; else return ((!empty($output))?"\n\n":'').$output;
}

function yarpp_related_exist($type,$args,$reference_ID=false) {
	global $wpdb, $post, $yarpp_time;

	if (is_object($post) and !$reference_ID)
		$reference_ID = $post->ID;
	
	if ($yarpp_time) // if we're already in a YARPP loop, stop now.
		return false;
	
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

    $result = $wpdb->get_var(yarpp_sql($type,$args,false,$reference_ID));
	return $result > 0 ? true: false;
}

function yarpp_cache_enforce($type=array('post'),$reference_ID,$force=false) {
	global $wpdb, $yarpp_debug;
	
	$timeout = 600;
	
	if (!$force) {
		if ($wpdb->get_var("select count(*) as count from {$wpdb->prefix}yarpp_related_cache where reference_ID = $reference_ID and date > date_sub(now(),interval $timeout minute)")) {
			if ($yarpp_debug) echo "<!--YARPP is using the cache right now.-->";
			return false;
		}
	}
	
	yarpp_cache_keywords($reference_ID);
	
	$wpdb->query("delete from {$wpdb->prefix}yarpp_related_cache where reference_ID = $reference_ID");
	
	$wpdb->query("insert into {$wpdb->prefix}yarpp_related_cache (reference_ID,ID,score) ".yarpp_sql($type,array(),true,$reference_ID)." on duplicate key update date = now()");
	if ($wpdb->rows_affected and $yarpp_debug) echo "<!--YARPP just set the cache.-->";
	if (!$wpdb->rows_affected) {
		$wpdb->query("insert into {$wpdb->prefix}yarpp_related_cache (reference_ID,ID,score) values ($reference_ID,0,0)");
		if (!$wpdb->rows_affected)
			return false;
	}
	//$wpdb->query("delete from {$wpdb->prefix}yarpp_related_cache where date <= date_sub(now(),interval $timeout minute)");
	//if ($wpdb->rows_affected)
	//	if ($yarpp_debug) echo "<!--$wpdb->rows_affected rows were cleared as they had expired.-->";
	
	return true;
	
}

