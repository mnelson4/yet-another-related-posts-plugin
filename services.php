<?php

// setup the ajax action hooks
if (function_exists('add_action')) {
	add_action('wp_ajax_yarpp_display_discats', 'yarpp_ajax_display_discats');
	add_action('wp_ajax_yarpp_display_distags', 'yarpp_ajax_display_distags');
	add_action('wp_ajax_yarpp_display_demo_web', 'yarpp_ajax_display_demo_web');
	add_action('wp_ajax_yarpp_display_demo_rss', 'yarpp_ajax_display_demo_rss');
	add_action('wp_ajax_yarpp_build_cache_action', 'yarpp_build_cache');
}

function yarpp_ajax_display_discats() {
	global $wpdb;
	$discats = explode(',',yarpp_get_option('discats'));
	array_unshift($discats,' ');
	foreach ($wpdb->get_results("select $wpdb->terms.term_id, name from $wpdb->terms natural join $wpdb->term_taxonomy where $wpdb->term_taxonomy.taxonomy = 'category' order by name") as $cat) {
		echo "<input type='checkbox' name='discats[$cat->term_id]' value='true'". (array_search($cat->term_id,$discats) ? ' checked="checked"': '' )."  /> <label>$cat->name</label> ";//for='discats[$cat->term_id]' it's not HTML. :(
	}
	exit;
}

function yarpp_ajax_display_distags() {
	global $wpdb;
	$distags = explode(',',yarpp_get_option('distags'));
	array_unshift($distags,' ');
	foreach ($wpdb->get_results("select $wpdb->terms.term_id, name from $wpdb->terms natural join $wpdb->term_taxonomy where $wpdb->term_taxonomy.taxonomy = 'post_tag' order by name") as $tag) {
		echo "<input type='checkbox' name='distags[$tag->term_id]' value='true'". (array_search($tag->term_id,$distags) ? ' checked="checked"': '' )."  /> <label>$tag->name</label> ";// for='distags[$tag->term_id]'
	}
	exit;
}

function yarpp_ajax_display_demo_web() {
	global $wpdb, $post, $yarpp_demo_time, $wp_query, $id, $page, $pages, $yarpp_limit;

	header("Content-Type: text/html; charset=UTF-8");

	$yarpp_limit = yarpp_get_option('limit');
	$return = yarpp_related(array('post'),array(),false,false,'demo_web');
	unset($yarpp_limit);
	echo ereg_replace("[\n\r]",'',nl2br(htmlspecialchars($return)));
	exit;
}

function yarpp_ajax_display_demo_rss() {
	global $wpdb, $post, $yarpp_demo_time, $wp_query, $id, $page, $pages, $yarpp_limit;

	header("Content-Type: text/html; charset=utf-8");

	$yarpp_limit = yarpp_get_option('rss_limit');
	$return = yarpp_related(array('post'),array(),false,false,'demo_rss');
	unset($yarpp_limit);
	echo ereg_replace("[\n\r]",'',nl2br(htmlspecialchars($return)));
	exit;
}

function yarpp_build_cache() {
	global $wpdb, $yarpp_cache;
	if (!is_user_logged_in() || !current_user_can('manage_options')) {
		wp_die(__('You cannot rebuild the YARPP cache.', 'yarpp'));
	}

//	$timeout = 2; // seconds
	$start = yarpp_microtime_float();

	$atATime = 10;
	if (!$_POST['i']) {
		$i = 0;
    $uncached = $yarpp_cache->uncached($atATime, 0);
		$m = $wpdb->get_var("select found_rows()");
	} else {
		$i = $_POST['i'];
    $uncached = $yarpp_cache->uncached($atATime, $i);
		$m = $_POST['m'];
	}

	error_log('uncached:' . join(':', $uncached) . ':' . count($uncached) . ':' . is_array($uncached));

	if (!count($uncached)) {
    $title = get_the_title($_POST['lastid']);
    header('Content-Type: application/json');
    echo "{\"result\":\"error\",\"title\": \"".addcslashes($title, '"\\')."\", \"i\": $i, \"m\": $m, \"percent\": \"".(floor(1000 * $i/$m)/10)."\", \"status\": \"" . $yarpp_cache->cache_status() . "\"}";
    exit();
	}

//	while ((yarpp_microtime_float() - $start) < $timeout and $i <= $m) {
  foreach ($uncached as $id) {
		$result = yarpp_cache_enforce(array('post'),$id,true);

		if (!$result) {
      $title = get_the_title($id);
			header('Content-Type: application/json');
			echo "{\"result\":\"error\",\"title\": \"".addcslashes($title, '"\\')."\", \"i\": $id, \"i\": $i, \"m\": $m, \"percent\": \"".(floor(1000 * $i/$m)/10)."\", \"status\": \"" . $yarpp_cache->cache_status() . "\"}";
			exit();
		}

		$i++;
	}

	header('Content-Type: application/json');
	echo "{\"result\":\"success\",\"time\":\"".(yarpp_microtime_float() - $start)."\", \"id\": $id, \"i\": $i, \"m\": $m, \"percent\": \"".(floor(1000 * $i/$m)/10)."\", \"status\": \"" . $yarpp_cache->cache_status() . "\"}";
	exit();
}
