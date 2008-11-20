<?php

global $wpdb, $yarpp_value_options, $yarpp_binary_options, $wp_version;

if ($_POST['myisam_override']) {
	yarpp_set_option('myisam_override',1);
	echo "<div class='updated'>"
	.__("The MyISAM check has been overridden. You may now use the \"consider titles\" and \"consider bodies\" relatedness criteria.")
	."</div>";
}

$yarpp_myisam = true;
if (!yarpp_get_option('myisam_override')) {
	$yarpp_check_return = yarpp_myisam_check();
	if ($yarpp_check_return !== true) { // if it's not *exactly* true
		echo "<div class='updated'>"
		.sprintf(__("YARPP's \"consider titles\" and \"consider bodies\" relatedness criteria require your <code>%s</code> table to use the <a href='http://dev.mysql.com/doc/refman/5.0/en/storage-engines.html'>MyISAM storage engine</a>, but the table seems to be using the <code>%s</code> engine. These two options have been disabled.",'yarpp'),$wpdb->posts,$yarpp_check_return)
		."<br />"
		.sprintf(__("To restore these features, please update your <code>%s</code> table by executing the following SQL directive: <code>ALTER TABLE `%s` ENGINE = MyISAM;</code> . No data will be erased by altering the table's engine, although there are performance implications.",'yarpp'),$wpdb->posts,$wpdb->posts)
		."<br />"
		.sprintf(__("If, despite this check, you are sure that <code>%s</code> is using the MyISAM engine, press this magic button:",'yarpp'),$wpdb->posts)
		."<br />"
		."<form method='post'><input type='submit' name='myisam_override' value='"
		.__("Trust me. Let me use MyISAM features.",'yarpp')
		."'></input></form>"
		."</div>";
	
		yarpp_set_option('title',1);
		yarpp_set_option('body',1);
		$yarpp_myisam = false;
	}
}

$yarpp_twopointfive = true;
if (substr($wp_version,0,3) < 2.5) {
	echo "<div class='updated'>The \"consider tags\" and \"consider categories\" options require WordPress version 2.5. These two options have been disabled.</div>";

	yarpp_set_option('categories',1);
	yarpp_set_option('tags',1);
	$yarpp_twopointfive = false;
}

if ($yarpp_myisam) {
	if (!yarpp_enabled()) {
		echo '<div class="updated">';
		if (yarpp_activate())
			_e('The YARPP database had an error but has been fixed.','yarpp');
		else 
			_e('The YARPP database has an error which could not be fixed.','yarpp');
		echo '</div>';
	}
}

yarpp_reinforce(); // just in case, set default options, etc.

//compute $tagmap
$tagmap = array();
foreach ($wpdb->get_results("select $wpdb->terms.term_id, name from $wpdb->terms natural join $wpdb->term_taxonomy where $wpdb->term_taxonomy.taxonomy = 'category'") as $tag) {
	$tagmap[$tag->term_id] = strtolower($tag->name);
}

function yarpp_mapthetag($id) {
	global $tagmap;
	return $tagmap[$id];
}
function yarpp_unmapthetag($name) {
	global $tagmap;
	$untagmap = array_flip($tagmap);
	return $untagmap[$name];
}

if (isset($_POST['update_yarpp'])) {
	foreach (array_keys($yarpp_value_options) as $option) {
		yarpp_set_option($option,addslashes($_POST[$option]));
	}
	foreach (array('title','body','tags','categories') as $key) {
		if (!isset($_POST[$key])) yarpp_set_option($key,1);
	}
	if (isset($_POST['discats'])) { 
		yarpp_set_option('discats',implode(',',array_keys($_POST['discats']))); // discats is different
	} else {
		yarpp_set_option('discats','');
	}

	if (isset($_POST['distags'])) { 
		yarpp_set_option('distags',implode(',',array_keys($_POST['distags']))); // distags is also different
	} else {
		yarpp_set_option('distags','');
	}
	//update_option('yarpp_distags',implode(',',array_map('yarpp_unmapthetag',preg_split('!\s*[;,]\s*!',strtolower($_POST['distags']))))); // distags is even more different
	
	foreach (array_keys($yarpp_binary_options) as $option) {
		(isset($_POST[$option])) ? yarpp_set_option($option,true) : yarpp_set_option($option,false);
	}		
	echo '<div id="message" class="updated fade" style="background-color: rgb(207, 235, 247);"><p>Options saved!</p></div>';
}

function checkbox($option,$desc,$tr="<tr valign='top'>
			<th class='th-full' colspan='2' scope='row'>",$inputplus = '',$thplus='') {
	echo "			$tr<input $inputplus type='checkbox' name='$option' value='true'". ((yarpp_get_option($option)) ? ' checked="checked"': '' )."  /> $desc</th>$thplus
		</tr>";
}
function textbox($option,$desc,$size=2,$tr="<tr valign='top'>
			<th scope='row'>") {
	$value = yarpp_get_option($option,true);
	echo "			$tr$desc</th>
			<td><input name='$option' type='text' id='$option' value='$value' size='$size' /></td>
		</tr>";
}
function importance($option,$desc,$type='word',$tr="<tr valign='top'>
			<th scope='row'>",$inputplus = '') {
	$value = yarpp_get_option($option);
	
	// $type could be...
	__('word','yarpp');
	__('tag','yarpp');
	__('category','yarpp');
	
	echo "		$tr$desc</th>
			<td>
			<input $inputplus type='radio' name='$option' value='1'". (($value == 1) ? ' checked="checked"': '' )."  /> ".__("do not consider",'yarpp')."
			<input $inputplus type='radio' name='$option' value='2'". (($value == 2) ? ' checked="checked"': '' )."  /> ".__("consider",'yarpp')."
			<input $inputplus type='radio' name='$option' value='3'". (($value == 3) ? ' checked="checked"': '' )."  /> 
			".sprintf(__("require at least one %s in common",'yarpp'),__($type,'yarpp'))."
			<input $inputplus type='radio' name='$option' value='4'". (($value == 4) ? ' checked="checked"': '' )."  /> 
			".sprintf(__("require more than one %s in common",'yarpp'),__($type,'yarpp'))."
			</td>
		</tr>";
}

function importance2($option,$desc,$type='word',$tr="<tr valign='top'>
			<th scope='row'>",$inputplus = '') {
	$value = yarpp_get_option($option);

	echo "		$tr$desc</th>
			<td>
			<input $inputplus type='radio' name='$option' value='1'". (($value == 1) ? ' checked="checked"': '' )."  />
			".__("do not consider",'yarpp')."
			<input $inputplus type='radio' name='$option' value='2'". (($value == 2) ? ' checked="checked"': '' )."  /> ".__("consider",'yarpp')."
			<input $inputplus type='radio' name='$option' value='3'". (($value == 3) ? ' checked="checked"': '' )."  /> ".__("consider with extra weight",'yarpp')."
			</td>
		</tr>";
}

function select($option,$desc,$type='word',$tr="<tr valign='top'>
			<th scope='row'>",$inputplus = '') {
	echo "		$tr$desc</th>
			<td>
			<input $inputplus type='radio' name='$option' value='1'". ((yarpp_get_option($option) == 1) ? ' checked="checked"': '' )."  /> 
			".__("do not consider",'yarpp')."
			<input $inputplus type='radio' name='$option' value='2'". ((yarpp_get_option($option) == 2) ? ' checked="checked"': '' )."  />
			".__("consider",'yarpp')."
			<input $inputplus type='radio' name='$option' value='3'". ((yarpp_get_option($option) == 3) ? ' checked="checked"': '' )."  />
			".sprintf(__("require at least one %s in common",'yarpp'),__($type,'yarpp'))."
			<input $inputplus type='radio' name='$option' value='4'". ((yarpp_get_option($option) == 4) ? ' checked="checked"': '' )."  />
			".sprintf(__("require more than one %s in common",'yarpp'),__($type,'yarpp'))."
			</td>
		</tr>";
}

?>
<script type="text/javascript">
//<!--

var rss=document.createElement("link");
rss.setAttribute("rel", "alternate");
rss.setAttribute("type", "application/rss+xml");
rss.setAttribute('title',"<?php _e("Yet Another Related Posts Plugin version history (RSS 2.0)",'yarpp');?>");
rss.setAttribute("href", "http://mitcho.com/code/yarpp/yarpp.rss");
document.getElementsByTagName("head")[0].appendChild(rss);

var css=document.createElement("link");
css.setAttribute("rel", "stylesheet");
css.setAttribute("type", "text/css");
css.setAttribute("href", "../wp-content/plugins/yet-another-related-posts-plugin/options.css");
document.getElementsByTagName("head")[0].appendChild(css);
document.getElementsByTagName("body")[0].setAttribute('onload',"excerpt();rss_excerpt();do_rss_display();");
//-->
</script>

<div class="wrap">
		<h2>
			<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=1&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8' target='_new'><img src="https://www.paypal.com/<?php echo paypal_directory(); ?>i/btn/btn_donate_SM.gif" name="submit" alt="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" title="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" style="float:right" /></a>
			
			
			<?php _e('Yet Another Related Posts Plugin Options','yarpp');?> <small><?php 
			
			$display_version = yarpp_get_option('version');
			$split = explode('.',$display_version);
			if (strlen($split[1]) == 1)
				echo $display_version;
			else {
				$pos = strpos($display_version,'.')+2;
				echo substr($display_version,0,$pos).'.'.substr($display_version,$pos);
			}
			?></small>
		</h2>

	<form method="post">


	<p><small><?php _e('by <a href="http://mitcho.com/code/">mitcho (Michael 芳貴 Erlewine)</a> and based on the fabulous work of <a href="http://peter.mapledesign.co.uk/weblog/archives/wordpress-related-posts-plugin">Peter Bower</a>, <a href="http://wasabi.pbwiki.com/Related%20Entries">Alexander Malov &amp; Mike Lu</a>.','yarpp');?></small></p>


	<!--The Pool-->
	<h3><?php _e('"The Pool"','yarpp');?></h3>
	<p><?php _e('"The Pool" refers to the pool of posts and pages that are candidates for display as related to the current entry.','yarpp');?></p>
	
	<table class="form-table" style="margin-top: 0">
		<tbody>
			<tr valign='top'>
				<th scope='row'><?php _e('Disallow by category:','yarpp');?></th><td><div style="overflow:auto;max-height:100px;">
			<?php
			$discats = explode(',',yarpp_get_option('discats'));
			array_unshift($discats,' ');
			foreach ($wpdb->get_results("select $wpdb->terms.term_id, name from $wpdb->terms natural join $wpdb->term_taxonomy where $wpdb->term_taxonomy.taxonomy = 'category' order by name") as $cat) {
				echo "<input type='checkbox' name='discats[$cat->term_id]' value='true'". (array_search($cat->term_id,$discats) ? ' checked="checked"': '' )."  /> <label>$cat->name</label> ";//for='discats[$cat->term_id]' it's not HTML. :(
			}?>
				</div></td></tr>
			<tr valign='top'>
				<th scope='row'><?php _e('Disallow by tag:','yarpp');?></th>
				<td><div style="overflow:auto;max-height:100px;">
			<?php
			$distags = explode(',',yarpp_get_option('distags'));
			array_unshift($distags,' ');
			foreach ($wpdb->get_results("select $wpdb->terms.term_id, name from $wpdb->terms natural join $wpdb->term_taxonomy where $wpdb->term_taxonomy.taxonomy = 'post_tag' order by name") as $tag) {
				echo "<input type='checkbox' name='distags[$tag->term_id]' value='true'". (array_search($tag->term_id,$distags) ? ' checked="checked"': '' )."  /> <label>$tag->name</label> ";// for='distags[$tag->term_id]'
			}?>
				</div></td></tr>
	<?php checkbox('show_past_post',__("Show password protected posts?",'yarpp')); ?>
	<?php checkbox('past_only',__("Show only previous posts?",'yarpp')); ?>
		</tbody>
	</table>

	<!-- Relatedness -->
	<h3><?php _e('"Relatedness" options','yarpp');?></h3>
	<p><?php _e('YARPP is different than the <a href="http://wasabi.pbwiki.com/Related%20Entries">previous plugins it is based on</a> as it limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.','yarpp');?> <a href="#" class='info'><?php _e('more&gt;','yarpp');?><span><?php _e('The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshhold, I recommend you turn on the "show admins the match scores" setting below. That way, you can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.','yarpp');?></span></a></p>
	
	<table class="form-table" style="margin-top: 0">
		<tbody>
	
	<?php textbox('threshold',__('Match threshold:','yarpp'))?>
	<?php importance2('title',__("Titles: ",'yarpp'),'word',"<tr valign='top'>
			<th scope='row'>",(!$yarpp_myisam?' readonly="readonly" disabled="disabled"':''))?>
	<?php importance2('body',__("Bodies: ",'yarpp'),'word',"<tr valign='top'>
			<th scope='row'>",(!$yarpp_myisam?' readonly="readonly" disabled="disabled"':''))?>
	<?php importance('tags',__("Tags: ",'yarpp'),'tag',"<tr valign='top'>
			<th scope='row'>",(!$yarpp_twopointfive?' readonly="readonly" disabled="disabled"':''))?>
	<?php importance('categories',__("Categories: ",'yarpp'),'category',"<tr valign='top'>
			<th scope='row'>",(!$yarpp_twopointfive?' readonly="readonly" disabled="disabled"':''))?>
	<?php checkbox('cross_relate',__("Cross-relate posts and pages?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("When the \"Cross-relate posts and pages\" option is selected, the <code>related_posts()</code>, <code>related_pages()</code>, and <code>related_entries()</code> all will give the same output, returning both related pages and posts.",'yarpp')."</span></a>"); ?>
			</tbody>
		</table>

<script language="javascript">
//<!--
	function excerpt() {
		display = 'none';
		if (document.getElementsByName('show_excerpt')[0].checked) {
			display = 'table-row';
		}
		document.getElementsByName('excerpted')[0].style.display = display;
		document.getElementsByName('excerpted')[1].style.display = display;
	}
	function rss_excerpt() {
		display = 'none';
		if (document.getElementsByName('rss_display')[0].checked && document.getElementsByName('rss_show_excerpt')[0].checked) {
			display = 'table-row';
		}
		document.getElementsByName('rss_excerpted')[0].style.display = display;
		document.getElementsByName('rss_excerpted')[1].style.display = display;
	}
	function do_rss_display() {
		display = 'none';
		if (document.getElementsByName('rss_display')[0].checked) {
			rss_excerpt();
			display = 'table-row';
		}
		document.getElementsByName('rss_displayed')[0].style.display = display;
		document.getElementsByName('rss_displayed')[1].style.display = display;
		document.getElementsByName('rss_displayed')[2].style.display = display;
		document.getElementsByName('rss_displayed')[3].style.display = display;
		document.getElementsByName('rss_displayed')[4].style.display = display;
		document.getElementsByName('rss_displayed')[5].style.display = display;
		document.getElementsByName('rss_displayed')[6].style.display = display;
		document.getElementsByName('rss_displayed')[7].style.display = display;
	}
//-->
</script>


		<!-- Display options -->
		<h3><?php _e("Display options <small>for your website</small>",'yarpp');?></h3>
		
		<table class="form-table" style="margin-top: 0">
<?php
// construct the demo code based on current preferences

$democode = stripslashes(yarpp_get_option('before_related',true))."
";
for ($i=1;$i<=yarpp_get_option('limit');$i++) {
	$democode .= stripslashes(yarpp_get_option('before_title',true)).stripslashes(htmlspecialchars("<a href='".__("PERMALINK",'yarpp')."$i'>".__("RELATED TITLE",'yarpp')." $i</a>")).(yarpp_get_option('show_excerpt')?"\r\t".stripslashes(yarpp_get_option('before_post',true)).yarpp_excerpt(LOREMIPSUM,yarpp_get_option('excerpt_length')).stripslashes(yarpp_get_option('before_post',true)):'').stripslashes(yarpp_get_option('after_title',true))."
";
}
$democode .= stripslashes(yarpp_get_option('after_related',true));
if (yarpp_get_option('promote_yarpp'))
	$democode .= htmlspecialchars("\n<p>".__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp')."</p>");

checkbox('auto_display',__("Automatically display related posts?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files.",'yarpp')."</span></a>","<tr valign='top'>
			<th class='th-full' colspan='2' scope='row'>",'','<td rowspan="11" style="border-left:8px white solid;"><b>'.__("Website display code example",'yarpp').'</b><br /><small>'.__("(Update options to reload.)",'yarpp').'</small><br/>'
."<code><pre style='overflow:auto;width:350px;'>".($democode)."</pre></code></td>"); ?>
	<?php textbox('limit',__('Maximum number of related posts:','yarpp'))?>
			<tr valign='top'>
				<th><?php _e("Before / after related entries:",'yarpp');?></th>
				<td><input name="before_related" type="text" id="before_related" value="<?php echo stripslashes(yarpp_get_option('before_related',true)); ?>" size="10" /> / <input name="after_related" type="text" id="after_related" value="<?php echo stripslashes(yarpp_get_option('after_related',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;ol&gt;&lt;/ol&gt; or &lt;div&gt;&lt;/div&gt;</small></em>
				</td>
			</tr>
			<tr valign='top'>
				<th><?php _e("Before / after each related entry:",'yarpp');?></th>
				<td><input name="before_title" type="text" id="before_title" value="<?php echo stripslashes(yarpp_get_option('before_title',true)); ?>" size="10" /> / <input name="after_title" type="text" id="after_title" value="<?php echo stripslashes(yarpp_get_option('after_title',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt; or &lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>
	<?php checkbox('show_excerpt',__("Show excerpt?",'yarpp'),"<tr valign='top'><th colspan='2'>",' onclick="javascript:excerpt()"'); ?>
	<?php textbox('excerpt_length',__('Excerpt length (No. of words):','yarpp'),null,"<tr name='excerpted' valign='top' ".(yarpp_get_option('show_excerpt')?'':"style='display:none'").">
				<th>")?>
	
			<tr name="excerpted" valign='top' <?php echo (yarpp_get_option('show_excerpt')?'':"style='display:none'")?>>
				<th><?php _e("Before / after (Excerpt):",'yarpp');?></th>
				<td><input name="before_post" type="text" id="before_post" value="<?php echo stripslashes(yarpp_get_option('before_post',true)); ?>" size="10" /> / <input name="after_post" type="text" id="after_post" value="<?php echo stripslashes(yarpp_get_option('after_post')); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt; or &lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>

			<tr name="excerpted" valign='top'>
				<th><?php _e("Order results:",'yarpp');?></th>
				<td><select name="order" id="order">
					<option value="score DESC" <?php echo (yarpp_get_option('order')=='score DESC'?' selected="selected"':'')?>><?php _e("score (high relevance to low)",'yarpp');?></option>
					<option value="score ASC" <?php echo (yarpp_get_option('order')=='score ASC'?' selected="selected"':'')?>><?php _e("score (low relevance to high)",'yarpp');?></option>
					<option value="post_date DESC" <?php echo (yarpp_get_option('order')=='post_date DESC'?' selected="selected"':'')?>><?php _e("date (new to old)",'yarpp');?></option>
					<option value="post_date ASC" <?php echo (yarpp_get_option('order')=='post_date ASC'?' selected="selected"':'')?>><?php _e("date (old to new)",'yarpp');?></option>
					<option value="post_title ASC" <?php echo (yarpp_get_option('order')=='post_title ASC'?' selected="selected"':'')?>><?php _e("title (alphabetical)",'yarpp');?></option>
					<option value="post_title DESC" <?php echo (yarpp_get_option('order')=='post_title DESC'?' selected="selected"':'')?>><?php _e("title (reverse alphabetical)",'yarpp');?></option>
				</select>
				</td>
			</tr>
	
	<?php textbox('no_results',__('Default display if no results:','yarpp'),'40')?>
	<?php checkbox('show_score',__("Show admins (user level > 8) the match scores?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("With this option on, each related entry's total 'match score' (all above the threshold, set above) are displayed after each entry title, <em>if you are an administrator and logged in.</em> Even if you see these values, your visitors will not.",'yarpp')."</span></a>"); ?>
	<?php checkbox('promote_yarpp',__("Help promote Yet Another Related Posts Plugin?",'yarpp')
	." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.sprintf(__("This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated."),"<code>".htmlspecialchars(__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp'))."</code>")
	."</span></a>"); ?>
		</table>

		<!-- Display options for RSS -->
		<h3><?php _e("Display options <small>for RSS</small>",'yarpp');?> <span style='color:red;'>NEW!</span></h3>
		
		<table class="form-table" style="margin-top: 0">
<?php
// construct the demo code based on current preferences for RSS

$democode = stripslashes(yarpp_get_option('rss_before_related',true))."
";
for ($i=1;$i<=yarpp_get_option('rss_limit');$i++) {
	$democode .= stripslashes(yarpp_get_option('rss_before_title',true)).stripslashes(htmlspecialchars("<a href='".__("RELATED TITLE",'yarpp')."$i'>".__("RELATED TITLE",'yarpp')." $i</a>")).(yarpp_get_option('rss_show_excerpt')?"\r\t".stripslashes(yarpp_get_option('rss_before_post',true)).yarpp_excerpt(LOREMIPSUM,yarpp_get_option('rss_excerpt_length')).stripslashes(yarpp_get_option('rss_before_post',true)):'').stripslashes(yarpp_get_option('rss_after_title',true))."
";
}
$democode .= stripslashes(yarpp_get_option('rss_after_related',true));
if (yarpp_get_option('rss_promote_yarpp'))
	$democode .= htmlspecialchars("\n<p>".__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp')."</p>");

checkbox('rss_display',__("Display related posts in feeds?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option displays related posts at the end of each item in your RSS and Atom feeds. No template changes are needed.",'yarpp')."</span></a>","<tr valign='top'><th colspan='3'>",' onclick="javascript:do_rss_display();"');
checkbox('rss_excerpt_display',__("Display related posts in the descriptions?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option displays the related posts in the RSS description fields, not just the content. If your feeds are set up to only display excerpts, however, only the description field is used, so this option is required for any display at all.",'yarpp')."</span></a>","<tr name='rss_displayed' valign='top'>
			<th class='th-full' colspan='2' scope='row'>",'','<td rowspan="10" style="border-left:8px white solid;"><b>'.__("RSS display code example",'yarpp').'</b><br /><small>'.__("(Update options to reload.)",'yarpp').'</small><br/>'
."<code><pre style='overflow:auto;width:350px;'>".($democode)."</pre></code></td>"); ?>
	<?php textbox('rss_limit',__('Maximum number of related posts:','yarpp'),2,"<tr valign='top' name='rss_displayed'>
			<th scope='row'>")?>
			<tr name='rss_displayed' valign='top'>
				<th><?php _e("Before / after related entries display:",'yarpp');?></th>
				<td><input name="rss_before_related" type="text" id="rss_before_related" value="<?php echo stripslashes(yarpp_get_option('rss_before_related',true)); ?>" size="10" /> / <input name="rss_after_related" type="text" id="rss_after_related" value="<?php echo stripslashes(yarpp_get_option('rss_after_related',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;ol&gt;&lt;/ol&gt; or &lt;div&gt;&lt;/div&gt;</small></em>
				</td>
			</tr>
			<tr name='rss_displayed' valign='top'>
				<th><?php _e("Before / after each related entry:",'yarpp');?></th>
				<td><input name="rss_before_title" type="text" id="rss_before_title" value="<?php echo stripslashes(yarpp_get_option('rss_before_title',true)); ?>" size="10" /> / <input name="rss_after_title" type="text" id="rss_after_title" value="<?php echo stripslashes(yarpp_get_option('rss_after_title',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt; or &lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>
	<?php checkbox('rss_show_excerpt',__("Show excerpt?",'yarpp'),"<tr name='rss_displayed' valign='top'><th colspan='2'>",' onclick="javascript:rss_excerpt()"'); ?>
	<?php textbox('rss_excerpt_length',__('Excerpt length (No. of words):','yarpp'),null,"<tr name='rss_excerpted' valign='top' ".(yarpp_get_option('rss_show_excerpt')?'':"style='display:none'").">
				<th>")?>
	
			<tr name="rss_excerpted" valign='top' <?php echo (yarpp_get_option('rss_show_excerpt')?'':"style='display:none'")?>>
				<th><?php _e("Before / after (excerpt):",'yarpp');?></th>
				<td><input name="rss_before_post" type="text" id="rss_before_post" value="<?php echo stripslashes(yarpp_get_option('rss_before_post',true)); ?>" size="10" /> / <input name="rss_after_post" type="text" id="rss_after_post" value="<?php echo stripslashes(yarpp_get_option('rss_after_post')); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt; or &lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>

			<tr name='rss_displayed' valign='top'>
				<th><?php _e("Order results:",'yarpp');?></th>
				<td><select name="rss_order" id="rss_order">
					<option value="score DESC" <?php echo (yarpp_get_option('rss_order')=='score DESC'?' selected="selected"':'')?>><?php _e("score (high relevance to low)",'yarpp');?></option>
					<option value="score ASC" <?php echo (yarpp_get_option('rss_order')=='score ASC'?' selected="selected"':'')?>><?php _e("score (low relevance to high)",'yarpp');?></option>
					<option value="post_date DESC" <?php echo (yarpp_get_option('rss_order')=='post_date DESC'?' selected="selected"':'')?>><?php _e("date (new to old)",'yarpp');?></option>
					<option value="post_date ASC" <?php echo (yarpp_get_option('rss_order')=='post_date ASC'?' selected="selected"':'')?>><?php _e("date (old to new)",'yarpp');?></option>
					<option value="post_title ASC" <?php echo (yarpp_get_option('rss_order')=='post_title ASC'?' selected="selected"':'')?>><?php _e("title (alphabetical)",'yarpp');?></option>
					<option value="post_title DESC" <?php echo (yarpp_get_option('rss_order')=='post_title DESC'?' selected="selected"':'')?>><?php _e("title (reverse alphabetical)",'yarpp');?></option>
				</select>
				</td>
			</tr>
	
	<?php textbox('rss_no_results',__('Default display if no results:','yarpp'),'40',"<tr valign='top' name='rss_displayed'>
			<th scope='row'>")?>
	<?php checkbox('rss_promote_yarpp',__("Help promote Yet Another Related Posts Plugin?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.sprintf(__("This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated."),"<code>".htmlspecialchars(__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp'))."</code>")
	."</span></a>","<tr valign='top' name='rss_displayed'>
			<th class='th-full' colspan='2' scope='row'>"); ?>
		</table>

	<div>
		<p class="submit">
			<input type="submit" name="update_yarpp" value="Update options" />
			<input type="submit" onclick='return confirm("Do you really want to reset your configuration?");' class="yarpp_warning" name="reset_yarpp" value="Reset options" />
		</p>
	</div>
</form>

<?php

?>