<?php
/*
Plugin Name: Yet Another Related Posts Plugin
Plugin URI: http://mitcho.com/code
Description: Returns a list of the related entries based on keyword matches, limited by a certain relatedness threshold. Like the tried and true Related Posts plugins—just better!
Version: 1.0
Author: Alexander Malov, Mike Lu, Peter Bowyer, mitcho (Michael Erlewine)
*/

// Begin setup

$yarpp_version = "1.0";

function yarpp_enabled() {
	global $wpdb;
	$indexdata = $wpdb->get_results("show index from $wpdb->posts");
	foreach ($indexdata as $index) {
		if ($index->Key_name == 'post_related') return 1;
	}
	return 0;
}

function yarpp_activate() {
	global $wpdb;
	add_option('threshold',5);
	add_option('limit',5);
	add_option('lim',10);
	add_option('show_score',true);
	if (!yarpp_enabled()) {
		$wpdb->query("ALTER TABLE $wpdb->posts ADD FULLTEXT `post_related` ( `post_name` , `post_content` )");
	}
	return 1;
}

// End setup

// Begin Related Posts

// This section was more or less completely written by 

/**
 * Builds a word frequency list from the Wordpress post, and returns a string
 * to be used in matching against the MySQL full-text index.
 *
 * @param integer $num_to_ret The number of words to use when matching against
 * 							  the database.
 * @return string The words
 */
function current_post_keywords($num_to_ret = 20) {
	global $post;
	// An array of weightings, to make adjusting them easier.
	$w = array(
			   'title' => 2,
			   'name' => 2,
			   'content' => 1,
			   'cat_name' => 3
		      );
	
	/*
	Thanks to http://www.eatdrinksleepmovabletype.com/tutorials/building_a_weighted_keyword_list/
	for the basics for this code.  It saved me much typing (or thinking) :)
	*/
	
	// This needs experimenting with.  I've given post title and url a double
	// weighting, changing this may give you better results
	$string = str_repeat($post->post_title, $w['title'].' ').
			  str_repeat(str_replace('-', ' ', $post->post_name).' ', $w['name']).
			  str_repeat(strip_tags((MARKDOWN_WP_POSTS) ? Markdown($post->post_content) : $post->post_content), $w['content'].' ');//mitcho: strip_tags
	
	// Cat names don't help with the current query: the category names of other
	// posts aren't retrieved by the query to be matched against (and can't be
	// indexed)
	// But I've left this in just in case...
	$post_categories = get_the_category();
	foreach ($post_categories as $cat) {
		$string .= str_repeat($cat->cat_name.' ', $w['cat_name']);
	}
	
	// Remove punctuation.
	$wordlist = preg_split('/\s*[\s+\.|\?|,|(|)|\-+|\'|\"|=|;|&#0215;|\$|\/|:|{|}]\s*/i', strtolower($string));
	
	// Build an array of the unique words and number of times they occur.
	$a = array_count_values($wordlist);
	
	//Remove words that don't matter--"stop words."
	$overusedwords = array( '', 'a', 'an', 'the', 'and', 'of', 'i', 'to', 'is', 'in', 'with', 'for', 'as', 'that', 'on', 'at', 'this', 'my', 'was', 'our', 'it', 'you', 'we', '1', '2', '3', '4', '5', '6', '7', '8', '9', '0', '10', 'about', 'after', 'all', 'almost', 'along', 'also', 'amp', 'another', 'any', 'are', 'area', 'around', 'available', 'back', 'be', 'because', 'been', 'being', 'best', 'better', 'big', 'bit', 'both', 'but', 'by', 'c', 'came', 'can', 'capable', 'control', 'could', 'course', 'd', 'dan', 'day', 'decided', 'did', 'didn', 'different', 'div', 'do', 'doesn', 'don', 'down', 'drive', 'e', 'each', 'easily', 'easy', 'edition', 'end', 'enough', 'even', 'every', 'example', 'few', 'find', 'first', 'found', 'from', 'get', 'go', 'going', 'good', 'got', 'gt', 'had', 'hard', 'has', 'have', 'he', 'her', 'here', 'how', 'if', 'into', 'isn', 'just', 'know', 'last', 'left', 'li', 'like', 'little', 'll', 'long', 'look', 'lot', 'lt', 'm', 'made', 'make', 'many', 'mb', 'me', 'menu', 'might', 'mm', 'more', 'most', 'much', 'name', 'nbsp', 'need', 'new', 'no', 'not', 'now', 'number', 'off', 'old', 'one', 'only', 'or', 'original', 'other', 'out', 'over', 'part', 'place', 'point', 'pretty', 'probably', 'problem', 'put', 'quite', 'quot', 'r', 're', 'really', 'results', 'right', 's', 'same', 'saw', 'see', 'set', 'several', 'she', 'sherree', 'should', 'since', 'size', 'small', 'so', 'some', 'something', 'special', 'still', 'stuff', 'such', 'sure', 'system', 't', 'take', 'than', 'their', 'them', 'then', 'there', 'these', 'they', 'thing', 'things', 'think', 'those', 'though', 'through', 'time', 'today', 'together', 'too', 'took', 'two', 'up', 'us', 'use', 'used', 'using', 've', 'very', 'want', 'way', 'well', 'went', 'were', 'what', 'when', 'where', 'which', 'while', 'white', 'who', 'will', 'would', 'your');
	
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

function related_posts() {
   
	global $wpdb, $post, $user_level;
	get_currentuserinfo();

	// Get option values from the options page--this can be overwritten: see readme
	$args = func_get_args();
	$options = array('limit','threshold','before_title','after_title','show_excerpt','len','before_post','after_post','show_pass_post','past_only','show_score');
	$optvals = array();
	foreach (array_keys($options) as $index) {
		if (isset($args[$index+1])) {
			$optvals[$options[$index]] = stripslashes($args[$index+1]);
		} else {
			$optvals[$options[$index]] = stripslashes(get_option($options[$index]));
		}
	}
	extract($optvals);
			
	// Fetch keywords
    $terms = current_post_keywords();

	// Make sure the post is not from the future
	$time_difference = get_settings('gmt_offset');
	$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));
	
	// Primary SQL query
	
    $sql = "SELECT ID, post_title, post_content,"
         . "MATCH (post_name, post_content) "
         . "AGAINST ('$terms') AS score "
         . "FROM $wpdb->posts WHERE "
         . "MATCH (post_name, post_content) AGAINST ('$terms') >= $threshold "
		 . "AND (post_status IN ( 'publish',  'static' ) && ID != '$post->ID') ";
	if (past_only) { $sql .= "AND post_date <= '$now' "; }
    if ($show_pass_post=='false') { $sql .= "AND post_password ='' "; }
    $sql .= "ORDER BY score DESC LIMIT $limit";
    $results = $wpdb->get_results($sql);
    $output = '';
    if ($results) {
		foreach ($results as $result) {
			$title = stripslashes(apply_filters('the_title', $result->post_title));
			$permalink = get_permalink($result->ID);
			$post_content = strip_tags($result->post_content);
			$post_content = stripslashes($post_content);
			$output .= $before_title .'<a href="'. $permalink .'" rel="bookmark" title="Permanent Link: ' . $title . '">' . $title . (($show_score and $user_level >= 8)? ' ('.round($result->score,3).')':'') . '</a>' . $after_title;
			if ($show_excerpt=='true') {
				$ze = substr($post_content, 0, $len);
				$ze = substr($ze, 0, strrpos($ze,''));
				$ze = $ze . '...';
				$output .= $before_post . $ze . $after_post;
			}
		}
		echo $output;
	} else {
		echo $before_title.'No related posts'.$after_title;
    }
}

function related_posts_exist($threshold = 0,$past_only = 2,$show_pass_post = 2) {
	global $wpdb, $post;

	if ($threshold == 0) $threshold = get_option('threshold');
	if ($past_only == 2) $past_only = get_option('past_only');
	if ($show_pass_post == 2) $past_only = get_option('show_pass_post');
    $terms = current_post_keywords();

	$time_difference = get_settings('gmt_offset');
	$now = gmdate("Y-m-d H:i:s",(time()+($time_difference*3600)));
	
    $sql = "SELECT COUNT(*) as count "
         . "FROM $wpdb->posts WHERE "
         . "MATCH (post_name, post_content) AGAINST ('$terms') >= $threshold "
		 . "AND (post_status IN ( 'publish',  'static' ) && ID != '$post->ID') ";
	if (past_only) { $sql .= "AND post_date <= '$now' "; }
    if ($show_pass_post=='false') { $sql .= "AND post_password ='' "; }
    $result = $wpdb->get_var($sql);
	return $result > 0 ? true: false;
}

// End Related Posts

// Begin Related Posts Options

function yarpp_subpanel() {
	global $yarpp_version;
	if (!yarpp_enabled()) {
		echo '<div class="updated">';
		if (yarpp_activate()) echo 'The YARPP database had an error but has been fixed.';
		echo '</div>';
	}
	
     if (isset($_POST['update_yarpp'])) {
		$valueoptions = array('limit','threshold','len','before_title','after_title','before_post','after_post');
		foreach ($valueoptions as $option) {
			update_option($option,$_POST[$option]);
		}
		$checkoptions = array('past_only','show_score','show_excerpt','show_pass_post');
		foreach ($checkoptions as $option) {
			(isset($_POST[$option])) ? update_option($option,true) : update_option($option,false);
		}		
       ?> <div class="updated">Options saved!</div><?php
     }

	function checkbox($option,$desc,$tr="<tr>
				<td colspan='2'>",$inputplus = '') {
		echo "			$tr<label for='$option'>$desc</label></td>
				<td>
				<input $inputplus type='checkbox' name='$option' value='true'". ((get_option($option)) ? ' checked="checked"': '' )."  />
				</td>
			</tr>";
	}
	function textbox($option,$desc,$size=2,$tr="<tr>
				<td colspan='2'>") {
		echo "			$tr<label for='$option'>$desc</label></td>
				<td><input name='$option' type='text' id='$option' value='".htmlspecialchars(stripslashes(get_option($option)))."' size='$size' /></td>
			</tr>";
	}

	if (get_option('threshold') == '') update_option('threshold',5);
	if (get_option('length') == '') update_option('length',5);
	if (get_option('len') == '') update_option('len',10);
	?>

	<div class="wrap">
		<h2>Yet Another Related Posts Plugin Options <small><?php echo $yarpp_version; ?></small></h2>
		<p><small>by <a href="http://mitcho.com/code/">mitcho (Michael 芳貴 Erlewine)</a> and based on the fabulous work of <a href="http://peter.mapledesign.co.uk/weblog/archives/wordpress-related-posts-plugin">Peter Bower</a>, <a href="http://wasabi.pbwiki.com/Related%20Entries">Alexander Malov & Mike Lu</a>.</small></p>
		<form method="post">
		<fieldset class="options">
		<h3>"Relatedness" options</h3>
		<p>YARPP is different than the <a href="http://wasabi.pbwiki.com/Related%20Entries">previous plugins it is based on</a> as it limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>. <a href="#" onclick="javascript:document.getElementById('yarpp_match_explanation').style.display = 'inline';this.style.display='none'" id="yarpp_match_explanation_trigger">Tell me more.</a></p>
		
		<p id="yarpp_match_explanation" style="display:none;">The higher the match threshold, the more restrictive, and you get less related posts overall. By default, the match threshold is 5. If you want to find an appropriate match score, I recommend you turn on the "show admins the match scores" setting below. That way, you can see what kinds of related posts are being picked up and	 with what kind of match scores, and determine an apprpriate threshold for your site. <a href="#" onclick="javascript:document.getElementById('yarpp_match_explanation_trigger').style.display = 'inline';this.parentNode.style.display='none'" id="yarpp_match_explanation_trigger">Tell me less now.</a></p>
		
		<table>
<?php textbox('limit','Maximum number of related posts:')?>
<?php textbox('threshold','Match threshold:')?>
		</table>
		<h3>Display options</h3>
		<table>
			<tr>
           		<td colspan='2'><label for="before_title">Before</label> / <label for="after_title">After (Post Title) </label>:</td>
				<td><input name="before_title" type="text" id="before_title" value="<?php echo htmlspecialchars(stripslashes(get_option('before_title'))); ?>" size="10" /> / <input name="after_title" type="text" id="after_title" value="<?php echo htmlspecialchars(stripslashes(get_option('after_title'))); ?>" size="10" /><em><small> For example: &lt;li&gt;&lt;/li&gt; or &lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>
<?php checkbox('show_excerpt',"Show excerpt?","<tr>
				<td colspan='2'>",' onclick="javascript:excerpt()"'); ?>
<?php textbox('len','Excerpt length (No. of words):',null,"<tr name='excerpted'>
				<td style='background-color: gray; width: .3px;'>&nbsp;</td><td>")?>

			<tr name="excerpted">
				<td style='background-color: gray; width: 3px;'>&nbsp;</td><td><label for="before_post">Before</label> / <label for="after_post">After</label> (Excerpt):</td>
				<td><input name="before_post" type="text" id="before_post" value="<?php echo htmlspecialchars(stripslashes(get_option('before_post'))); ?>" size="10" /> / <input name="after_post" type="text" id="after_post" value="<?php echo htmlspecialchars(stripslashes(get_option('after_post'))); ?>" size="10" /><em><small> For example: &lt;li&gt;&lt;/li&gt; or &lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>

<?php checkbox('show_past_post',"Show password protected posts?"); ?>
<?php checkbox('past_only',"Show only previous posts?"); ?>
<?php checkbox('show_score',"Show admins (user level > 8) the match scores?"); ?>

		</table>
		</fieldset>

		<div class="submit"><input type="submit" name="update_yarpp" value="<?php _e('Save!', 'update_yarpp') ?>"  style="font-weight:bold;" /></div>
		
		</form>       
		
    </div>
    <script language="javascript">
    	function excerpt() {
			if (!document.getElementsByName('show_excerpt')[0].checked) {
				document.getElementsByName('excerpted')[0].style.display = 'none';
				document.getElementsByName('excerpted')[1].style.display = 'none';
			} else {
				document.getElementsByName('excerpted')[0].style.display = 'table-row';
				document.getElementsByName('excerpted')[1].style.display = 'table-row';
			}
    	}
    	excerpt();
    </script>

<?php } 

// End Related Posts Options

function yarpp_admin_menu() {
   if (function_exists('add_submenu_page')) {
        add_submenu_page('plugins.php', __('Related Posts (YARPP) Options'), __('Related Posts (YARPP) Options'), 1, __FILE__, 'yarpp_subpanel');
        }
}

// add_action('publish_post', 'find_keywords', 1);
add_action('admin_menu', 'yarpp_admin_menu');
register_activation_hook(__FILE__,'yarpp_activate' );

?>