<?php

abstract class YARPP_Cache {

	public $core;
	public $score_override = false;
	public $online_limit = false;

	function __construct( &$core ) {
		$this->core = &$core;
		$this->name = __($this->name, 'yarpp');
	}
	
	// Note: return value changed in 3.4
	// return YARPP_NO_RELATED | YARPP_RELATED | YARPP_DONT_RUN | false if no good input
	function enforce($reference_ID, $force = false) {
		if ( !$reference_ID = absint($reference_ID) )
			return false;
	
		$status = $this->is_cached($reference_ID);
		$status = apply_filters( 'yarpp_cache_enforce_status', $status, $reference_ID );
	
		// There's a stop signal:
		if ( YARPP_DONT_RUN === $status )
			return YARPP_DONT_RUN;
	
		// If not cached, process now:
		if ( YARPP_NOT_CACHED == $status || $force ) {
			$status = $this->update($reference_ID);
			// if still not cached, there's a problem, but for the time being return NO RELATED
			if ( YARPP_NOT_CACHED === $status )
				return YARPP_NO_RELATED;
		}
	
		// There are no related posts
		if ( YARPP_NO_RELATED === $status )
			return YARPP_NO_RELATED;
	
		// There are results
		return YARPP_RELATED;
	}
	
	/*
	 * POST STATUS INTERACTIONS
	 */
	
	function save_post($post_ID, $force=true) {
		global $wpdb;
	
		// new in 3.2: don't compute cache during import
		if ( defined( 'WP_IMPORTING' ) )
			return;
	
		$sql = "select post_parent from $wpdb->posts where ID='$post_ID'";
		$parent_ID = $wpdb->get_var($sql);
	
		if ( $parent_ID != $post_ID && $parent_ID )
			$post_ID = $parent_ID;
	
		$this->enforce((int) $post_ID, $force);
	}
	
	// Clear the cache for this entry and for all posts which are "related" to it.
	// New in 3.2: This is called when a post is deleted.
	function delete_post($post_ID) {
		// Clear the cache for this post.
		$this->clear($post_ID);
	
		// Find all "peers" which list this post as a related post.
		$peers = $this->related(null, $post_ID);
		// Clear the peers' caches.
		$this->clear($peers);
	}
	
	// New in 3.2.1: handle various post_status transitions
	function transition_post_status($new_status, $old_status, $post) {
		switch ($new_status) {
			case "draft":
				$this->delete_post($post->ID);
				break;
			case "publish":
				// find everything which is related to this post, and clear them, so that this
				// post might show up as related to them.
				$related = $this->related($post->ID, null);
				$this->clear($related);
		}
	}
	
	function set_score_override_flag($q) {
		if ( $this->is_yarpp_time() ) {
			$this->score_override = ($q->query_vars['orderby'] == 'score');
	
			if (!empty($q->query_vars['showposts'])) {
				$this->online_limit = $q->query_vars['showposts'];
			} else {
				$this->online_limit = false;
			}
		} else {
			$this->score_override = false;
			$this->online_limit = false;
		}
	}

	/*
	 * KEYWORDS
	 */
	
	public function title_keywords($ID,$max = 20) {
		return $this->extract_keywords(get_the_title($ID),$max);
	}
	
	public function body_keywords( $ID, $max = 20 ) {
		$post = get_post( $ID );
		if ( empty($post) )
			return '';
		$content = $this->apply_filters_if_white( 'the_content', $post->post_content );
		return $this->extract_keywords( $content, $max );
	}
	
	private function extract_keywords($html, $max = 20) {
	
		$lang = 'en_US';
		if ( defined('WPLANG') ) {
			$lang = substr(WPLANG, 0, 2);
			switch ( $lang ) {
				case 'de':
					$lang = 'de_DE';
				case 'it':
					$lang = 'it_IT';
				case 'pl':
					$lang = 'pl_PL';
				case 'bg':
					$lang = 'bg_BG';
				case 'fr':
					$lang = 'fr_FR';
				case 'cs':
					$lang = 'cs_CZ';
				case 'nl':
					$lang = 'nl_NL';
			}
		}
	
		$words_file = YARPP_DIR . '/lang/words-' . $lang . '.php';
		if ( file_exists($words_file) )
			include( $words_file );
		if ( !isset($overusedwords) )
			$overusedwords = array();
	
		// strip tags and html entities
		$text = preg_replace('/&(#x[0-9a-f]+|#[0-9]+|[a-zA-Z]+);/', '', strip_tags($html) );
	
		// 3.2.2: ignore soft hyphens
		// Requires PHP 5: http://bugs.php.net/bug.php?id=25670
		$softhyphen = html_entity_decode('&#173;',ENT_NOQUOTES,'UTF-8');
		$text = str_replace($softhyphen, '', $text);
	
		$charset = get_option('blog_charset');
		if ( function_exists('mb_split') && !empty($charset) ) {
			mb_regex_encoding($charset);
			$wordlist = mb_split('\s*\W+\s*', mb_strtolower($text, $charset));
		} else
			$wordlist = preg_split('%\s*\W+\s*%', strtolower($text));
	
		// Build an array of the unique words and number of times they occur.
		$tokens = array_count_values($wordlist);
	
		// Remove the stop words from the list.
		$overusedwords = apply_filters( 'yarpp_keywords_overused_words', $overusedwords );
		if ( is_array($overusedwords) ) {
			foreach ($overusedwords as $word) {
				 unset($tokens[$word]);
			}
		}
		// Remove words which are only a letter
		$mb_strlen_exists = function_exists('mb_strlen');
		foreach (array_keys($tokens) as $word) {
			if ($mb_strlen_exists)
				if (mb_strlen($word) < 2) unset($tokens[$word]);
			else
				if (strlen($word) < 2) unset($tokens[$word]);
		}
	
		arsort($tokens, SORT_NUMERIC);
	
		$types = array_keys($tokens);
	
		if (count($types) > $max)
			$types = array_slice($types, 0, $max);
		return implode(' ', $types);
	}
	
	/* new in 2.0! apply_filters_if_white (previously apply_filters_without) now has a blacklist.
	 * It can be modified via the yarpp_blacklist and yarpp_blackmethods filters.
	 */
	/* blacklisted so far:
		- diggZ-Et
		- reddZ-Et
		- dzoneZ-Et
		- WP-Syntax
		- Viper's Video Quicktags
		- WP-CodeBox
		- WP shortcodes
		- WP Greet Box
		//- Tweet This - could not reproduce problem.
	*/
	function white( $filter ) {
		static $blacklist, $blackmethods;
	
		if ( is_null($blacklist) || is_null($blackmethods) ) {
			$yarpp_blacklist = array('yarpp_default', 'diggZEt_AddBut', 'reddZEt_AddBut', 'dzoneZEt_AddBut', 'wp_syntax_before_filter', 'wp_syntax_after_filter', 'wp_codebox_before_filter', 'wp_codebox_after_filter', 'do_shortcode');//,'insert_tweet_this'
			$yarpp_blackmethods = array('addinlinejs', 'replacebbcode', 'filter_content');
		
			$blacklist = (array) apply_filters( 'yarpp_blacklist', $yarpp_blacklist );
			$blackmethods = (array) apply_filters( 'yarpp_blackmethods', $yarpp_blackmethods );
		}
		
		if ( is_array($filter) && in_array( $filter[1], $blackmethods ) )
			return false;
		return !in_array( $filter, $blacklist );
	}
	
	/* FYI, apply_filters_if_white was used here to avoid a loop in apply_filters('the_content') > yarpp_default() > yarpp_related() > current_post_keywords() > apply_filters('the_content').*/
	function apply_filters_if_white($tag, $value) {
		global $wp_filter, $merged_filters, $wp_current_filter;
	
		$args = array();
	
		// Do 'all' actions first
		if ( isset($wp_filter['all']) ) {
			$wp_current_filter[] = $tag;
			$args = func_get_args();
			_wp_call_all_hook($args);
		}
	
		if ( !isset($wp_filter[$tag]) ) {
			if ( isset($wp_filter['all']) )
				array_pop($wp_current_filter);
			return $value;
		}
	
		if ( !isset($wp_filter['all']) )
			$wp_current_filter[] = $tag;
	
		// Sort
		if ( !isset( $merged_filters[ $tag ] ) ) {
			ksort($wp_filter[$tag]);
			$merged_filters[ $tag ] = true;
		}
	
		reset( $wp_filter[ $tag ] );
	
		if ( empty($args) )
			$args = func_get_args();
	
		do {
			foreach( (array) current($wp_filter[$tag]) as $the_ )
				if ( !is_null($the_['function'])
				and $this->white($the_['function'])){ // HACK
					$args[1] = $value;
					$value = call_user_func_array($the_['function'], array_slice($args, 1, (int) $the_['accepted_args']));
				}
	
		} while ( next($wp_filter[$tag]) !== false );
	
		array_pop( $wp_current_filter );
	
		return $value;
	}
}
