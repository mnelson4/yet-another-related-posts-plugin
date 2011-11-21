<?php

//=CACHING===========

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
	$related_query = $wp_query; // backwards compatibility

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
	$wp_query = $current_query; unset($current_query); unset($related_query);
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

