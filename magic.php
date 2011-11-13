<?php

//=TEMPLATING/DISPLAY===========

function yarpp_set_score_override_flag($q) {
	global $yarpp;
	if ( $yarpp->cache->is_yarpp_time() ) {
		$yarpp->cache->score_override = ($q->query_vars['orderby'] == 'score');

		if (!empty($q->query_vars['showposts'])) {
			$yarpp->cache->online_limit = $q->query_vars['showposts'];
		} else {
			$yarpp->cache->online_limit = false;
		}
	} else {
		$yarpp->cache->score_override = false;
		$yarpp->cache->online_limit = false;
	}
}

//=CACHING===========

function yarpp_sql( $reference_ID = false ) {
	global $wpdb, $post, $yarpp;

	if ( is_object($post) && !$reference_ID ) {
		$reference_ID = $post->ID;
	}

	$options = array( 'threshold', 'show_pass_post', 'past_only', 'body', 'title', 'tags', 'categories', 'exclude', 'recent_only', 'recent_number', 'recent_units');
	$yarpp_options = yarpp_get_option();
	// mask it so we only get the ones specified in $options
	$optvals = array_intersect_key($yarpp_options, array_flip($options));
	extract($optvals);

	$limit = max(yarpp_get_option('limit'), yarpp_get_option('rss_limit'));

	// Fetch keywords
	$keywords = $yarpp->cache->get_keywords($reference_ID);

	// get weights
	$weights = array(
		'body' => (($body == 3)?3:(($body == 2)?1:0)),
		'title' => (($title == 3)?3:(($title == 2)?1:0)),
		'tag' => (($tags != 1)?1:0),
		'cat' => (($categories != 1)?1:0)
	);
	$totalweight = array_sum( array_values( $weights ) );

	// get disallowed categories and tags
	$disterms = wp_parse_id_list($exclude['category'] . ',' . $exclude['post_tag']);
	$usedisterms = count($disterms);
	$disterms = implode(',', $disterms);

	$criteria = array();
	if ($weights['body'])
		$criteria['body'] = "(MATCH (post_content) AGAINST ('".$wpdb->escape($keywords['body'])."'))";
	if ($weights['title'])
		$criteria['title'] = "(MATCH (post_title) AGAINST ('".$wpdb->escape($keywords['title'])."'))";
	if ($weights['tag'])
		$criteria['tag'] = "COUNT( DISTINCT tagtax.term_taxonomy_id )";
	if ($weights['cat'])
		$criteria['cat'] = "COUNT( DISTINCT cattax.term_taxonomy_id )";

	$newsql = "SELECT $reference_ID as reference_ID, ID, "; //post_title, post_date, post_content, post_excerpt,

	$newsql .= 'ROUND(0';
	foreach ($criteria as $key => $value) {
		$newsql .= "+ $value * ".$weights[$key];
	}
	$newsql .= ',1) as score';

	$newsql .= "\n from $wpdb->posts \n";

	if ($usedisterms)
		$newsql .= " left join $wpdb->term_relationships as blockrel on ($wpdb->posts.ID = blockrel.object_id)
		left join $wpdb->term_taxonomy as blocktax using (`term_taxonomy_id`)
		left join $wpdb->terms as blockterm on (blocktax.term_id = blockterm.term_id and blockterm.term_id in ($disterms))\n";

	if ($weights['tag'])
		$newsql .= " left JOIN $wpdb->term_relationships AS thistag ON (thistag.object_id = $reference_ID )
		left JOIN $wpdb->term_relationships AS tagrel on (tagrel.term_taxonomy_id = thistag.term_taxonomy_id
		AND tagrel.object_id = $wpdb->posts.ID)
		left JOIN $wpdb->term_taxonomy AS tagtax ON ( tagrel.term_taxonomy_id = tagtax.term_taxonomy_id
		AND tagtax.taxonomy = 'post_tag')\n";

	if ($weights['cat'])
		$newsql .= " left JOIN $wpdb->term_relationships AS thiscat ON (thiscat.object_id = $reference_ID )
		left JOIN $wpdb->term_relationships AS catrel on (catrel.term_taxonomy_id = thiscat.term_taxonomy_id
		AND catrel.object_id = $wpdb->posts.ID)
		left JOIN $wpdb->term_taxonomy AS cattax ON ( catrel.term_taxonomy_id = cattax.term_taxonomy_id
		AND cattax.taxonomy = 'category')\n";

	// WHERE

	$newsql .= " where (post_status IN ( 'publish',	'static' ) and ID != '$reference_ID')";

	if ($past_only) { // 3.1.8: revised $past_only option
		if ( is_object($post) && $reference_ID == $post->ID )
			$reference_post_date = $post->post_date;
		else
			$reference_post_date = $wpdb->get_var("select post_date from $wpdb->posts where ID = $reference_ID");
		$newsql .= " and post_date <= '$reference_post_date' ";
	}
	if (!$show_pass_post)
		$newsql .= " and post_password ='' ";
	if ($recent_only)
		$newsql .= " and post_date > date_sub(now(), interval $recent_number $recent_units) ";

	$newsql .= " and post_type = 'post'";

	// GROUP BY
	$newsql .= "\n group by ID \n";
	// HAVING
	// safethreshold is so the new calibration system works.
	// number_format fix suggested by vkovalcik! :)
	$safethreshold = number_format(max($threshold,0.1), 2, '.', '');
	$newsql .= " having score >= $safethreshold";
	if ($usedisterms)
		$newsql .= " and count(blockterm.term_id) = 0";

	$newsql .= (($categories == 3)?' and '.$criteria['cat'].' >= 1':'');
	$newsql .= (($categories == 4)?' and '.$criteria['cat'].' >= 2':'');
	$newsql .= (($tags == 3)?' and '.$criteria['tag'].' >= 1':'');
	$newsql .= (($tags == 4)?' and '.$criteria['tag'].' >= 2':'');
	$newsql .= " order by score desc limit ".$limit;

	// in caching, we cross-relate regardless of whether we're going to actually
	// use it or not.
	$newsql = "($newsql) union (".str_replace("post_type = 'post'","post_type = 'page'",$newsql).")";

	if ($yarpp->debug) echo "<!--$newsql-->";
	return $newsql;
}

/* new in 2.1! the domain argument refers to {website,widget,rss}, though widget is not used yet. */

/* new in 3.0! new query-based approach: EXTREMELY HACKY! */

function yarpp_related($type,$args,$echo = true,$reference_ID=false,$domain = 'website') {
	global $yarpp, $wp_query, $pagenow, $yarpp;

	$yarpp->upgrade_check();

	if ($domain == 'demo_web' || $domain == 'demo_rss') {
		if ($yarpp->cache->demo_time) // if we're already in a YARPP loop, stop now.
			return false;
	} else {
		if ($yarpp->cache->is_yarpp_time()) // if we're already in a YARPP loop, stop now.
			return false;
		if ( !$reference_ID )
			$reference_ID = get_the_ID();

		$cache_status = $yarpp->cache->enforce($reference_ID);
		
		// If cache status is YARPP_DONT_RUN, end here without returning or echoing anything.
		if ( YARPP_DONT_RUN == $cache_status )
			return;
	}

	get_currentuserinfo();

	// set the "domain prefix", used for all the preferences.
	if ($domain == 'rss' || $domain == 'demo_rss')
		$domainprefix = 'rss_';
	else
		$domainprefix = '';
	// get options
	// note the 2.1 change... the options array changed from what you might call a "list" to a "hash"... this changes the structure of the $args to something which is, in the long term, much more useful
	$options = array(
		'cross_relate'=>"cross_relate",
		'limit'=>"${domainprefix}limit",
		'use_template'=>"${domainprefix}use_template",
		'order'=>"${domainprefix}order",
		'template_file'=>"${domainprefix}template_file",
		'promote_yarpp'=>"${domainprefix}promote_yarpp");
	$optvals = array();
	foreach (array_keys($options) as $option) {
		if (isset($args[$option])) {
			$optvals[$option] = $args[$option];
		} else {
			$optvals[$option] = yarpp_get_option($options[$option]);
		}
	}
	extract($optvals);
	// override $type for cross_relate:
	if ($cross_relate)
		$type = array('post','page');

	if ($domain == 'demo_web' || $domain == 'demo_rss') {
		// It's DEMO TIME!
		$yarpp->cache->demo_time = true;
		if ($domain == 'demo_web')
			$yarpp->cache->demo_limit = yarpp_get_option('limit');
		else
			$yarpp->cache->demo_limit = yarpp_get_option('rss_limit');
	} else if ( YARPP_NO_RELATED == $cache_status ) {
		// There are no results, so no yarpp time for us... :'(
	} else {
		// Get ready for YARPP TIME!
		$yarpp->cache->begin_yarpp_time($reference_ID);
	}

	// so we can return to normal later
	$current_query = $wp_query;
	$current_pagenow = $pagenow;

	$output = '';
	$wp_query = new WP_Query();
	$orders = explode(' ',$order);
	if ( 'demo_web' == $domain || 'demo_rss' == $domain ) {
		$wp_query->query('');
	} else if ( YARPP_NO_RELATED == $cache_status ) {
		// If there are no related posts, get no query
	} else {
		$wp_query->query(array(
			'p' => $reference_ID,
			'orderby' => $orders[0],
			'order' => $orders[1],
			'showposts' => $limit,
			'post_type' => $type
		));
	}

	$wp_query->in_the_loop = true;
	$wp_query->is_feed = $current_query->is_feed;
	// make sure we get the right is_single value
	// (see http://wordpress.org/support/topic/288230)
	$wp_query->is_single = false;

	if ($domain == 'metabox') {
		include(YARPP_DIR.'/template-metabox.php');
	} elseif ($use_template and file_exists(STYLESHEETPATH . '/' . $template_file) and $template_file != '') {
		ob_start();
		include(STYLESHEETPATH . '/' . $template_file);
		$output = ob_get_contents();
		ob_end_clean();
	} elseif ($domain == 'widget') {
		include(YARPP_DIR.'/template-widget.php');
	} else {
		include(YARPP_DIR.'/template-builtin.php');
	}

	if ( 'demo_web' == $domain || 'demo_rss' == $domain ) {
		$yarpp->cache->demo_time = false;
	} else if ( YARPP_NO_RELATED == $cache_status ) {
		// Uh, do nothing. Stay very still.
	} else {
		$yarpp->cache->end_yarpp_time(); // YARPP time is over... :(
	}

	// restore the older wp_query.
	$wp_query = $current_query; unset($current_query);
	wp_reset_postdata();
	$pagenow = $current_pagenow; unset($current_pagenow);

	if ($promote_yarpp and $domain != 'metabox')
		$output .= "\n<p>".sprintf(__("Related posts brought to you by <a href='%s'>Yet Another Related Posts Plugin</a>.",'yarpp'), 'http://yarpp.org')."</p>";

	if ($echo) echo $output; else return ((!empty($output))?"\n\n":'').$output;
}

function yarpp_related_exist($type,$args,$reference_ID=false) {
	global $yarpp, $post, $yarpp;

	$yarpp->upgrade_check();

	if (is_object($post) && !$reference_ID)
		$reference_ID = $post->ID;

	if ($yarpp->cache->is_yarpp_time()) // if we're already in a YARPP loop, stop now.
		return false;

	if (yarpp_get_option('cross_relate'))
		$type = array('post','page');

	$cache_status = $yarpp->cache->enforce($reference_ID);

	if ( YARPP_NO_RELATED == $cache_status )
		return false;

	$yarpp->cache->begin_yarpp_time($reference_ID); // get ready for YARPP TIME!
	$related_query = new WP_Query();
	$related_query->query(array('p'=>$reference_ID,'showposts'=>1,'post_type'=>$type));
	$return = $related_query->have_posts();
	unset($related_query);
	$yarpp->cache->end_yarpp_time(); // YARPP time is over. :(

	return $return;
}

