<?php

$yarpp_storage_class = 'YARPP_Cache_Postmeta';

define('YARPP_POSTMETA_TITLE_KEYWORDS_KEY','_yarpp_title_keywords');
define('YARPP_POSTMETA_BODY_KEYWORDS_KEY','_yarpp_body_keywords');
define('YARPP_POSTMETA_RELATED_KEY', '_yarpp_related');
define('YARPP_NO_RELATED', ':(');

class YARPP_Cache_Postmeta {
	// variables used for lookup
	var $related_postdata = array();
	var $related_IDs = array();
	var $name = "postmeta";
	var $yarpp_time = false;
	var $demo_time = false;
	var $score_override = false;
	var $online_limit = false;

	/**
	 * SETUP/STATUS
	 */
	function YARPP_Cache_Postmeta() {
		$this->name = __($this->name, 'yarpp');
		add_filter('posts_where',array(&$this,'where_filter'));
		add_filter('posts_orderby',array(&$this,'orderby_filter'));
		add_filter('posts_fields',array(&$this,'fields_filter'));
		add_filter('posts_request',array(&$this,'demo_request_filter'));
		add_filter('post_limits',array(&$this,'limit_filter'));
	}

	function is_enabled() {
		return true; // always enabled.
	}

	function setup() {
	}
	
	function upgrade() {
	}

	function cache_status() {
		global $wpdb;
		return $wpdb->get_var("select (count(p.ID)-sum(m.meta_value IS NULL))/count(p.ID)
			FROM `{$wpdb->posts}` as p
			LEFT JOIN `{$wpdb->postmeta}` as m ON (p.ID = m.post_id and m.meta_key = '" . YARPP_POSTMETA_RELATED_KEY . "')
			WHERE p.post_status = 'publish'");
	}

	function uncached($limit = 20, $offset = 0) {
		global $wpdb;
		return $wpdb->get_col("select SQL_CALC_FOUND_ROWS p.ID
			FROM `{$wpdb->posts}` as p
			LEFT JOIN `{$wpdb->postmeta}` as m ON (p.ID = m.post_id and m.meta_key = '" . YARPP_POSTMETA_RELATED_KEY . "')
			WHERE p.post_status = 'publish' and m.meta_value IS NULL
			LIMIT $limit OFFSET $offset");
	}

	/**
	 * MAGIC FILTERS
	 */
	function where_filter($arg) {
		global $wpdb;
		if ($this->yarpp_time) {
			$threshold = yarpp_get_option('threshold');
			// modify the where clause to use the related ID list.
			if (!count($this->related_IDs))
				$this->related_IDs = array(0);
			$arg = preg_replace("!{$wpdb->posts}.ID = \d+!","{$wpdb->posts}.ID in (".join(',',$this->related_IDs).")",$arg);

			// if we have "recent only" set, add an additional condition
			if (yarpp_get_option("recent_only"))
				$arg .= " and post_date > date_sub(now(), interval ".yarpp_get_option("recent_number")." ".yarpp_get_option("recent_units").") ";
		}
		return $arg;
	}

	function orderby_filter($arg) {
		global $wpdb;
		// only order by score if the score function is added in fields_filter, which only happens
		// if there are related posts in the postdata
		if ($this->yarpp_time && $this->score_override &&
		    is_array($this->related_postdata) && count($this->related_postdata))
			return str_replace("$wpdb->posts.post_date","score",$arg);
		return $arg;
	}

	function fields_filter($arg) {
		global $wpdb, $wpdb;
		if ($this->yarpp_time && is_array($this->related_postdata) && count($this->related_postdata)) {
			$scores = array();
			foreach ($this->related_postdata as $related_entry) {
				$scores[] = " WHEN {$related_entry['ID']} THEN {$related_entry['score']}";
			}
			$arg .= ", CASE {$wpdb->posts}.ID" . join('',$scores) ." END as score";
		}
		return $arg;
	}

	function demo_request_filter($arg) {
		global $wpdb;
		if ($this->demo_time) {
			$wpdb->query("set @count = 0;");
			return "SELECT SQL_CALC_FOUND_ROWS ID + {$this->demo_limit} as ID, post_author, post_date, post_date_gmt, '" . LOREMIPSUM . "' as post_content,
			concat('".__('Example post ','yarpp')."',@count:=@count+1) as post_title, 0 as post_category, '' as post_excerpt, 'publish' as post_status, 'open' as comment_status, 'open' as ping_status, '' as post_password, concat('example-post-',@count) as post_name, '' as to_ping, '' as pinged, post_modified, post_modified_gmt, '' as post_content_filtered, 0 as post_parent, concat('PERMALINK',@count) as guid, 0 as menu_order, 'post' as post_type, '' as post_mime_type, 0 as comment_count, 'SCORE' as score
			FROM $wpdb->posts
			ORDER BY ID DESC LIMIT 0, {$this->demo_limit}";
		}
		return $arg;
	}

	function limit_filter($arg) {
		global $wpdb;
		if ($this->yarpp_time and $this->online_limit)
			return " limit {$this->online_limit} ";
		return $arg;
	}

	/**
	 * RELATEDNESS CACHE CONTROL
	 */
	function begin_yarpp_time($reference_ID) {
		$this->yarpp_time = true;
		// get the related posts from postdata, and also construct the relate_IDs array
		$this->related_postdata = get_post_meta($reference_ID,YARPP_POSTMETA_RELATED_KEY,true);
		if (is_array($this->related_postdata) && count($this->related_postdata))
			$this->related_IDs = array_map(create_function('$x','return $x["ID"];'), $this->related_postdata);
	}

	function end_yarpp_time() {
		$this->yarpp_time = false;
		$this->related_IDs = array();
		$this->related_postdata = array();
	}

	function is_cached($reference_ID) {
		return get_post_meta($reference_ID, YARPP_POSTMETA_RELATED_KEY, true) != false;
	}

	function clear($reference_ID) {
		if (is_int($reference_ID))
			$reference_ID = array($reference_ID);
		// make sure that we have a non-trivial array
		if (!is_array($reference_ID) || !count($reference_ID))
			return;
		// clear each cache
		foreach($reference_ID as $id) {
			delete_post_meta( $id, YARPP_POSTMETA_RELATED_KEY );
		}
	}

	function update($reference_ID) {
		global $wpdb, $yarpp_debug;

		// $reference_ID must be numeric
		if ( !is_int( $reference_ID ) )
			return new WP_Error('yarpp_cache_error', "reference ID must be an int" );

		$original_related = $this->related($reference_ID);
		$related = $wpdb->get_results(yarpp_sql(array(),true,$reference_ID), ARRAY_A);
		$new_related = array_map(create_function('$x','return $x["ID"];'), $related);

		if (count($new_related)) {
			update_post_meta($reference_ID, YARPP_POSTMETA_RELATED_KEY, $related);
			if ($yarpp_debug) echo "<!--YARPP just set the cache for post $reference_ID-->";

			// Clear the caches of any items which are no longer related or are newly related.
			if (count($original_related)) {
				$this->clear(array_diff($original_related, $new_related));
				$this->clear(array_diff($new_related, $original_related));
			}
		} else {
			update_post_meta($reference_ID, YARPP_POSTMETA_RELATED_KEY, YARPP_NO_RELATED);
			// Clear the caches of those which are no longer related.
			if (count($original_related)) {
				$this->clear($original_related);
			}
		}
	}

	function flush() {
		global $wpdb;
		return $wpdb->query("delete from `{$wpdb->postmeta}` where meta_key = '" . YARPP_POSTMETA_RELATED_KEY . "'");
	}

	function related($reference_ID = null, $related_ID = null) {
		global $wpdb;

		if ( !is_int( $reference_ID ) && !is_int( $related_ID ) )
			return new WP_Error('yarpp_cache_error', "reference ID and/or related ID must be ints" );

		if (!is_null($reference_ID) && !is_null($related_ID)) {
			$results = get_post_meta($reference_ID,YARPP_POSTMETA_RELATED_KEY,true);
			foreach($results as $result) {
				if ($result['ID'] == $related_ID)
					return true;
			}
			return false;
		}

		// return a list of ID's of "related" entries
		if (!is_null($reference_ID)) {
			$results = get_post_meta($reference_ID,YARPP_POSTMETA_RELATED_KEY,true);
			if (!$results || $results == YARPP_NO_RELATED)
				return array();
			return array_map(create_function('$x','return $x["ID"];'), $results);
		}

		// return a list of entities which list this post as "related"
		if (!is_null($related_ID)) {
			return $wpdb->get_col("select post_id from `{$wpdb->postmeta}` where meta_key = '" . YARPP_POSTMETA_RELATED_KEY . "' and meta_value regexp 's:2:\"ID\";s:\d+:\"{$related_ID}\"'");
		}

		return false;
	}

	/**
	 * KEYWORDS CACHE CONTROL
	 */
	function cache_keywords($ID) {
		update_post_meta($ID, YARPP_POSTMETA_BODY_KEYWORDS_KEY, post_body_keywords($ID));
		update_post_meta($ID, YARPP_POSTMETA_TITLE_KEYWORDS_KEY, post_title_keywords($ID));
	}

	function get_keywords($ID, $type='body') {
		$key = $type == 'body' ? YARPP_POSTMETA_BODY_KEYWORDS_KEY : YARPP_POSTMETA_TITLE_KEYWORDS_KEY;
		$out = get_post_meta($ID, $key, true);

		// if empty, try caching them first
		if ($out === false) {
			yarpp_cache_keywords($ID);
			$out = get_post_meta($ID, $key, true);
		}

		return $out;
	}
}