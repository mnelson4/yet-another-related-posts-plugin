<?php

$yarpp_storage_class = 'YARPP_Cache_Tables';

define('YARPP_TABLES_RELATED_TABLE', 'yarpp_related_cache');
define('YARPP_TABLES_KEYWORDS_TABLE', 'yarpp_keyword_cache');

class YARPP_Cache_Tables {
	public $name = "custom tables";
	private $yarpp_time = false;
	public $demo_time = false;
	public $score_override = false; // @todo: should be private
	public $online_limit = false; // @todo: should be private

	/**
	 * SETUP/STATUS
	 */
	function YARPP_Cache_Tables() {
		$this->name = __($this->name, 'yarpp');
		add_filter('posts_join',array(&$this,'join_filter'));
		add_filter('posts_where',array(&$this,'where_filter'));
		add_filter('posts_orderby',array(&$this,'orderby_filter'));
		add_filter('posts_fields',array(&$this,'fields_filter'));
		add_filter('posts_request',array(&$this,'demo_request_filter'));
		add_filter('post_limits',array(&$this,'limit_filter'));
	}

	public function is_enabled() {
		global $wpdb;
		// now check for the cache tables
		$tabledata = $wpdb->get_col("show tables");
		if (array_search($wpdb->prefix . YARPP_TABLES_RELATED_TABLE,$tabledata) !== false &&
			array_search($wpdb->prefix . YARPP_TABLES_KEYWORDS_TABLE,$tabledata) !== false)
			return true;
		else
			return false;
	}

	public function setup() {
		global $wpdb;
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}" . YARPP_TABLES_KEYWORDS_TABLE . "` (
			`ID` bigint(20) unsigned NOT NULL default '0',
			`body` text NOT NULL,
			`title` text NOT NULL,
			`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY  (`ID`)
			) ENGINE=MyISAM COMMENT='YARPP''s keyword cache table';");
		$wpdb->query("CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . "` (
			`reference_ID` bigint(20) unsigned NOT NULL default '0',
			`ID` bigint(20) unsigned NOT NULL default '0',
			`score` float unsigned NOT NULL default '0',
			`date` timestamp NOT NULL default CURRENT_TIMESTAMP,
			PRIMARY KEY ( `reference_ID` , `ID` ),
			INDEX (`score`), INDEX (`ID`)
			) ENGINE=MyISAM;");
	}
	
	public function upgrade($last_version) {
		global $wpdb;
		if ( $last_version && version_compare('3.2.1b4', $last_version) > 0 ) {
			// Change primary key to be (reference_ID, ID) to ensure that we don't
			// get duplicates.
			// We unfortunately have to clear the cache first here, to ensure that there
			// are no duplicates.
			$this->flush();
			$wpdb->query('ALTER TABLE ' . $wpdb->prefix . YARPP_TABLES_RELATED_TABLE .
			  ' DROP PRIMARY KEY ,' .
			  ' ADD PRIMARY KEY ( `reference_ID` , `ID` ),' .
			  ' ADD INDEX (`score`), ADD INDEX (`ID`)');
		}
	}

	public function cache_status() {
		global $wpdb;
		return $wpdb->get_var("select (count(p.ID)-sum(c.ID IS NULL))/count(p.ID)
			FROM `{$wpdb->posts}` as p
			LEFT JOIN `{$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . "` as c ON (p.ID = c.reference_ID)
			WHERE p.post_status = 'publish' ");
	}

	public function uncached($limit = 20, $offset = 0) {
		global $wpdb;
		return $wpdb->get_col("select SQL_CALC_FOUND_ROWS p.ID
			FROM `{$wpdb->posts}` as p
			LEFT JOIN `{$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . "` as c ON (p.ID = c.reference_ID)
			WHERE p.post_status = 'publish' and c.ID IS NULL
			LIMIT $limit OFFSET $offset");
	}

	/**
	 * MAGIC FILTERS
	 */
	public function join_filter($arg) {
		global $wpdb;
		if ($this->yarpp_time)
			$arg .= " join {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " as yarpp on {$wpdb->posts}.ID = yarpp.ID";
		return $arg;
	}

	public function where_filter($arg) {
		global $wpdb;
		$threshold = yarpp_get_option('threshold');
		if ($this->yarpp_time) {

			$arg = str_replace("$wpdb->posts.ID = ","yarpp.score >= $threshold and yarpp.reference_ID = ",$arg);

			if (yarpp_get_option("recent_only"))
				$arg .= " and post_date > date_sub(now(), interval ".yarpp_get_option("recent_number")." ".yarpp_get_option("recent_units").") ";
		}
		return $arg;
	}

	public function orderby_filter($arg) {
		global $wpdb;
		if ($this->yarpp_time and $this->score_override)
			$arg = str_replace("$wpdb->posts.post_date","yarpp.score",$arg);
		return $arg;
	}

	public function fields_filter($arg) {
		global $wpdb;
		if ($this->yarpp_time)
			$arg .= ", yarpp.score";
		return $arg;
	}

	public function demo_request_filter($arg) {
		global $wpdb;
		if ($this->demo_time) {
			$wpdb->query("set @count = 0;");
			$arg = "SELECT SQL_CALC_FOUND_ROWS ID + {$this->demo_limit} as ID, post_author, post_date, post_date_gmt, '" . LOREMIPSUM . "' as post_content,
			concat('".__('Example post ','yarpp')."',@count:=@count+1) as post_title, 0 as post_category, '' as post_excerpt, 'publish' as post_status, 'open' as comment_status, 'open' as ping_status, '' as post_password, concat('example-post-',@count) as post_name, '' as to_ping, '' as pinged, post_modified, post_modified_gmt, '' as post_content_filtered, 0 as post_parent, concat('PERMALINK',@count) as guid, 0 as menu_order, 'post' as post_type, '' as post_mime_type, 0 as comment_count, 'SCORE' as score
			FROM $wpdb->posts
			ORDER BY ID DESC LIMIT 0, {$this->demo_limit}";
		}
		return $arg;
	}

	public function limit_filter($arg) {
		global $wpdb;
		if ($this->yarpp_time and $this->online_limit) {
			return " limit {$this->online_limit} ";
		}
		return $arg;
	}

	/**
	 * RELATEDNESS CACHE CONTROL
	 */
	public function is_yarpp_time() {
		return $this->yarpp_time;
	}
	 
	public function begin_yarpp_time() {
		$this->yarpp_time = true;
	}

	public function end_yarpp_time() {
		$this->yarpp_time = false;
	}

	// @return YARPP_NO_RELATED | YARPP_RELATED | YARPP_NOT_CACHED
	public function is_cached($reference_ID) {
		global $wpdb;
		
		$result = wp_cache_get( 'is_cached_' . $reference_ID, 'yarpp' );
		if ( false !== $result )
			return $result;
		
		$min_id = $wpdb->get_var("select min(ID) as min_id from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where reference_ID = $reference_ID");

		if ( is_null( $min_id ) )
			return YARPP_NOT_CACHED;
		
		if ( 0 == $min_id )
			$result = YARPP_NO_RELATED;
		else
			$result = YARPP_RELATED;
		
		wp_cache_set( 'is_cached_' . $reference_ID, $result, 'yarpp' );
		
		return $result;
	}

	public function clear($reference_ID) {
		global $wpdb;
		if (is_array($reference_ID) && count($reference_ID))
			$wpdb->query("delete from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where reference_ID in (".implode(',',$reference_ID).")");
		else if (is_int($reference_ID))
			$wpdb->query("delete from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where reference_ID = {$reference_ID}");
	}

	// @return YARPP_RELATED | YARPP_NO_RELATED | YARPP_NOT_CACHED
	public function update($reference_ID) {
		global $wpdb, $yarpp_debug;
		
		// $reference_ID must be numeric
		if ( !$reference_ID = absint($reference_ID) )
			return YARPP_NOT_CACHED;

		$original_related = $this->related($reference_ID);

		// clear out the cruft
		$this->clear($reference_ID);

		$wpdb->query("insert into {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " (reference_ID,ID,score) ".yarpp_sql($reference_ID)." on duplicate key update date = now()");

		// If there were related entries saved...
		if ( $wpdb->rows_affected ) {
			$new_related = $this->related($reference_ID);

			if ($yarpp_debug) echo "<!--YARPP just set the cache for post $reference_ID-->";

			// Clear the caches of any items which are no longer related or are newly related.
			if (count($original_related)) {
				$this->clear(array_diff($original_related, $new_related));
				$this->clear(array_diff($new_related, $original_related));
			}
			
			return YARPP_RELATED;
		} else {
			$wpdb->query("insert into {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " (reference_ID,ID,score) values ($reference_ID,0,0) on duplicate key update date = now()");

			//if (!$wpdb->rows_affected)
			//	return YARPP_NOT_CACHED;

			// Clear the caches of those which are no longer related.
			if ( count($original_related) )
				$this->clear($original_related);

			return YARPP_NO_RELATED;
		}
	}

	public function flush() {
		global $wpdb;
		$wpdb->query("truncate table `{$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . "`");
		$wpdb->query("truncate table `{$wpdb->prefix}" . YARPP_TABLES_KEYWORDS_TABLE . "`");
	}

	public function related($reference_ID = null, $related_ID = null) {
		global $wpdb;

		if ( !is_int( $reference_ID ) && !is_int( $related_ID ) )
			return new WP_Error('yarpp_cache_error', "reference ID and/or related ID must be ints" );

		if (!is_null($reference_ID) && !is_null($related_ID)) {
			$results = $wpdb->get_col("select ID from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where reference_ID = $reference_ID and ID = $related_ID");
			return count($results) > 0;
		}

		// return a list of ID's of "related" entries
		if (!is_null($reference_ID)) {
			return $wpdb->get_col("select distinct ID from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where reference_ID = $reference_ID and ID != 0");
		}

		// return a list of entities which list this post as "related"
		if (!is_null($related_ID)) {
			return $wpdb->get_col("select distinct reference_ID from {$wpdb->prefix}" . YARPP_TABLES_RELATED_TABLE . " where ID = $related_ID");
		}

		return false;
	}

	/**
	 * KEYWORDS CACHE CONTROL
	 */
	// @return (array) with body and title keywords
	private function cache_keywords($ID) {
		global $wpdb;
		$body_terms = post_body_keywords($ID);
		$title_terms = post_title_keywords($ID);

		if (defined('DB_CHARSET') && DB_CHARSET) {
			$wpdb->query('set names '.DB_CHARSET);
		}

		$wpdb->query("insert into {$wpdb->prefix}" . YARPP_TABLES_KEYWORDS_TABLE . " (ID,body,title) values ($ID,'$body_terms ','$title_terms ') on duplicate key update date = now(), body = '$body_terms ', title = '$title_terms '");

		return array( 'body' => $body_terms, 'title' => $title_terms );
	}

	// @param $ID (int)
	// @param $type (string) body | title | all
	// @return (string|array) depending on whether "all" were requested or not
	public function get_keywords( $ID, $type = 'all' ) {
		global $wpdb;

		if ( !is_int($ID) )
			return false;

		$keywords = $wpdb->get_row("select body, title from {$wpdb->prefix}" . YARPP_TABLES_KEYWORDS_TABLE . " where ID = $ID", ARRAY_A);
		if ( empty($keywords) ) // if empty, try caching them first.
			$keywords = $this->cache_keywords($ID);

		if ( empty($keywords) )
			return false;
		
		if ( 'all' == $type )
			return $keywords;
		return $keywords[$type];
	}
}