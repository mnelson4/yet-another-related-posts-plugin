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
	$democode = stripslashes(yarpp_get_option('before_related',true))."\n";
	for ($i=1;$i<=yarpp_get_option('limit');$i++) {
		$democode .= stripslashes(yarpp_get_option('before_title',true)).stripslashes(htmlspecialchars("<a href='".__("PERMALINK",'yarpp')."$i'>".__("RELATED TITLE",'yarpp')." $i</a>")).(yarpp_get_option('show_excerpt')?"\r\t".stripslashes(yarpp_get_option('before_post',true)).yarpp_excerpt(LOREMIPSUM,yarpp_get_option('excerpt_length')).stripslashes(yarpp_get_option('before_post',true)):'').stripslashes(yarpp_get_option('after_title',true))."\n";
	}
	$democode .= stripslashes(yarpp_get_option('after_related',true));
	if (yarpp_get_option('promote_yarpp'))
		$democode .= htmlspecialchars("\n<p>".__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp')."</p>");
	echo $democode;
	exit;
}

function yarpp_ajax_display_demo_rss() {
	$democode = stripslashes(yarpp_get_option('rss_before_related',true))."\n";
	for ($i=1;$i<=yarpp_get_option('rss_limit');$i++) {
		$democode .= stripslashes(yarpp_get_option('rss_before_title',true)).stripslashes(htmlspecialchars("<a href='".__("RELATED TITLE",'yarpp')."$i'>".__("RELATED TITLE",'yarpp')." $i</a>")).(yarpp_get_option('rss_show_excerpt')?"\r\t".stripslashes(yarpp_get_option('rss_before_post',true)).yarpp_excerpt(LOREMIPSUM,yarpp_get_option('rss_excerpt_length')).stripslashes(yarpp_get_option('rss_before_post',true)):'').stripslashes(yarpp_get_option('rss_after_title',true))."\n";
	}
	$democode .= stripslashes(yarpp_get_option('rss_after_related',true));
	if (yarpp_get_option('rss_promote_yarpp'))
		$democode .= htmlspecialchars("\n<p>".__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp')."</p>");
	echo $democode;
	exit;
}

function yarpp_build_cache() {
	global $wpdb;
	if (!is_user_logged_in() || !current_user_can('level_10')) {
		wp_die(__('You cannot rebuild the YARPP cache.', 'yarpp'));
	}
	
	if (!$_POST['i']) {
		$id = $wpdb->get_var("select min(ID), count(ID) from $wpdb->posts where post_status = 'publish'",0);
		$i = 1;
		$m = $wpdb->get_var(null,1);
	} else {
		$id = $_POST['id'];
		$i = $_POST['i'];
		$m = $_POST['m'];
	}
	
	$timeout = 3; // seconds
	$start = yarpp_microtime_float();
	while ((yarpp_microtime_float() - $start) < $timeout and $i <= $m) {
		$result = yarpp_cache_enforce(array('post'),$id,true);
		
		if (!$result) {
			header('Content-Type: application/json');	
			echo "{result:'error',id: '$id', title: '".addslashes($title)."', i: $i, m: $m, percent: '".(floor(1000 * $i/$m)/10)."'}";
			exit();
		}
		
		$id = $wpdb->get_var("select ID, post_title from $wpdb->posts where ID > $id and post_status = 'publish' order by ID asc limit 1",0);
		$title = $wpdb->get_var(null,1);
		$i++;
	}

	//header('Status: 404 Not Found');
	//header('HTTP/1.1 404 Not Found');
	//echo sprintf(__("You do not have the permission to write the file '%s'.", CSP_PO_TEXTDOMAIN), $_POST['pofile']);

	header('Content-Type: application/json');	
	echo "{result:'success',time:'".(yarpp_microtime_float() - $start)."', id: '$id', title: '".addslashes($title)."', i: $i, m: $m, percent: '".(floor(1000 * $i/$m)/10)."'}";
	exit();
}

?>
