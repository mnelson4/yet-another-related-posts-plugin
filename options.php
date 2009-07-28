<?php

global $wpdb, $yarpp_value_options, $yarpp_binary_options, $wp_version;

// check to see that templates are in the right place

if (!count(glob(STYLESHEETPATH . '/yarpp-template-*.php'))) {
  if (count(glob(WP_CONTENT_DIR.'/plugins/yet-another-related-posts-plugin/yarpp-templates/yarpp-template-*.php')))
  	echo "<div class='updated'>"
	  .str_replace("TEMPLATEPATH",STYLESHEETPATH,__("Please move the YARPP template files into your theme to complete installation. Simply move the sample template files (currently in <code>wp-content/plugins/yet-another-related-posts-plugin/yarpp-templates/</code>) to the <code>TEMPLATEPATH</code> directory.",'yarpp'))
	  ."</div>";

  else 
  	echo "<div class='updated'>"
  	.str_replace('TEMPLATEPATH',STYLESHEETPATH,__("No YARPP template files were found in your theme (<code>TEMPLATEPATH</code>)  so the templating feature has been turned off.",'yarpp'))
  	."</div>";
  
  yarpp_set_option('use_template',false);
  yarpp_set_option('rss_use_template',false);
  
}

if ($_POST['myisam_override']) {
	yarpp_set_option('myisam_override',1);
	echo "<div class='updated'>"
	.__("The MyISAM check has been overridden. You may now use the \"consider titles\" and \"consider bodies\" relatedness criteria.",'yarpp')
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
		."<form method='post'><input type='submit' class='button' name='myisam_override' value='"
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
		echo '<div class="updated"><p>';
		if (yarpp_activate())
			_e('The YARPP database had an error but has been fixed.','yarpp');
		else 
			__('The YARPP database has an error which could not be fixed.','yarpp')
			.str_replace('<A>','<a href=\'http://mitcho.com/code/yarpp/sql.php?prefix='.urlencode($wpdb->prefix).'\'>',__('Please try <A>manual SQL setup</a>.','yarpp'));
		echo '</div></p>';
	}
}

yarpp_reinforce(); // just in case, set default options, etc.

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
	echo '<div class="updated fade" style="background-color: rgb(207, 235, 247);"><p>'.__('Options saved!','yarpp')
	.' '.str_replace('<A>','<a class="thickbox" title="'.__('Related posts cache status','yarpp').'" href="#TB_inline?height=100&width=300&inlineId=yarpp-cache-status">',
	__('If you updated the "pool" options or "relatedness" options displayed, please rebuild your cache now from the <A>related posts status pane</a>.','yarpp')).'</p></div>';
}

// check if the cache is complete or not.
$cache_complete = $wpdb->get_var("select (count(p.ID)-sum(c.ID IS NULL))/count(p.ID)
  FROM $wpdb->posts as p
  LEFT JOIN {$wpdb->prefix}yarpp_related_cache as c ON ( p.ID = c.reference_ID )
  WHERE p.post_status = 'publish' ");

if (yarpp_get_option('ad_hoc_caching') != 1) {
  
  if ($cache_complete > 0 and $cache_complete < 1)
    echo '<div class="updated fade" style="background-color: rgb(207, 235, 247);"><p>'.str_replace('<A>','<a class="thickbox" title="'.__('Related posts cache status','yarpp').'" href="#TB_inline?height=100&width=300&inlineId=yarpp-cache-status">',__('Your related posts cache is incomplete. Please build your cache from the <A>related posts status pane</a>.','yarpp')).'</p></div>';
  
  if ($cache_complete == 0)
    echo '<div class="updated fade" style="background-color: rgb(207, 235, 247);"><p>'.str_replace('<A>','<a class="thickbox" title="'.__('Related posts cache status','yarpp').'" href="#TB_inline?height=100&width=300&inlineId=yarpp-cache-status">',__('Your related posts cache is empty. Please build your cache from the <A>related posts status pane</a>.','yarpp')).'</p></div>';
}
	
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

function checkbox($option,$desc,$tr="<tr valign='top'>
			<th class='th-full' colspan='2' scope='row'>",$inputplus = '',$thplus='') {
	echo "			$tr<input $inputplus type='checkbox' name='$option' value='true'". ((yarpp_get_option($option) == 1) ? ' checked="checked"': '' )."  /> $desc</th>$thplus
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

function load_display_demo_web() {
	jQuery.ajax({type:'POST',
	    url:'admin-ajax.php',
	    data:'action=yarpp_display_demo_web',
	    beforeSend:function(){jQuery('#display_demo_web').eq(0).html('<img src="../wp-content/plugins/yet-another-related-posts-plugin/i/spin.gif" alt="loading..."/>')},
	    success:function(html){jQuery('#display_demo_web').eq(0).html('<pre>'+html+'</pre>')},
	    dataType:'html'}
	)
}

function load_display_demo_rss() {
	jQuery.ajax({type:'POST',
	    url:'admin-ajax.php',
	    data:'action=yarpp_display_demo_rss',
	    beforeSend:function(){jQuery('#display_demo_rss').eq(0).html('<img src="../wp-content/plugins/yet-another-related-posts-plugin/i/spin.gif" alt="loading..."/>')},
	    success:function(html){jQuery('#display_demo_rss').eq(0).html('<pre>'+html+'</pre>')},
	    dataType:'html'}
	)
}

function load_display_distags() {
	jQuery.ajax({type:'POST',
	    url:'admin-ajax.php',
	    data:'action=yarpp_display_distags',
	    beforeSend:function(){jQuery('#display_distags').eq(0).html('<img src="../wp-content/plugins/yet-another-related-posts-plugin/i/spin.gif" alt="loading..."/>')},
	    success:function(html){jQuery('#display_distags').eq(0).html(html)},
	    dataType:'html'}
	)
}

function load_display_discats() {
	jQuery.ajax({type:'POST',
	    url:'admin-ajax.php',
	    data:'action=yarpp_display_discats',
	    beforeSend:function(){jQuery('#display_discats').eq(0).html('<img src="../wp-content/plugins/yet-another-related-posts-plugin/i/spin.gif" alt="loading..."/>')},
	    success:function(html){jQuery('#display_discats').eq(0).html(html)},
	    dataType:'html'}
	)
}
//-->
</script>

<div class="wrap">
		<h2>
			<?php _e('Yet Another Related Posts Plugin Options','yarpp');?> <small><?php 
			
			$display_version = yarpp_get_option('version');
			$split = explode('.',$display_version);
			if (strlen($split[1]) != 1) {
				$pos = strpos($display_version,'.')+2;
				$display_version = substr($display_version,0,$pos).'.'.substr($display_version,$pos);
			}
      echo $display_version;
			?></small>
		</h2>

	<?php echo "<div id='yarpp-version' style='display:none;'>".yarpp_get_option('version')."</div>"; ?>
		
	<form method="post">

			<a href='https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=66G4DATK4999L&item_name=mitcho%2ecom%2fcode%3a%20donate%20to%20Michael%20Yoshitaka%20Erlewine&no_shipping=1&no_note=1&tax=0&currency_code=USD&lc=US&charset=UTF%2d8' target='_new'><img src="https://www.paypal.com/<?php echo paypal_directory(); ?>i/btn/btn_donate_SM.gif" name="submit" alt="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" title="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal','yarpp');?>" style="float:right" /></a>

	<p><small><?php _e('by <a href="http://mitcho.com/code/">mitcho (Michael 芳貴 Erlewine)</a>','yarpp');?>. <?php _e('Follow <a href="http://twitter.com/yarpp/">Yet Another Related Posts Plugin on Twitter</a>','yarpp');?>.</small></p>


	<!--The Pool-->
	<div style='border:1px solid #ddd;padding:8px;'>
	<h3><?php _e('"The Pool"','yarpp');?></h3>
	<p><?php _e('"The Pool" refers to the pool of posts and pages that are candidates for display as related to the current entry.','yarpp');?></p>
	
	<table class="form-table" style="margin-top: 0">
		<tbody>
			<tr valign='top'>
				<th scope='row'><?php _e('Disallow by category:','yarpp');?></th><td><div id='display_discats' style="overflow:auto;max-height:100px;"></div></td></tr>
			<tr valign='top'>
				<th scope='row'><?php _e('Disallow by tag:','yarpp');?></th>
				<td><div id='display_distags' style="overflow:auto;max-height:100px;"></div></td></tr>
	<?php checkbox('show_pass_post',__("Show password protected posts?",'yarpp')); ?>
	<?php /*checkbox('past_only',__("Show only previous posts?",'yarpp')); */ ?>
	<?php 
	
	$recent_number = "<input name=\"recent_number\" type=\"text\" id=\"recent_number\" value=\"".stripslashes(yarpp_get_option('recent_number',true))."\" size=\"2\" />";
	$recent_units = "<select name=\"recent_units\" id=\"recent_units\">
		<option value='day'". (('day'==yarpp_get_option('recent_units'))?" selected='selected'":'').">".__('day(s)','yarpp')."</option>
		<option value='week'". (('week'==yarpp_get_option('recent_units'))?" selected='selected'":'').">".__('week(s)','yarpp')."</option>
		<option value='month'". (('month'==yarpp_get_option('recent_units'))?" selected='selected'":'').">".__('month(s)','yarpp')."</option>
	</select>";
	checkbox('recent_only',str_replace('NUMBER',$recent_number,str_replace('UNITS',$recent_units,__("Show only posts from the past NUMBER UNITS",'yarpp')))); ?>

		</tbody>
	</table>
	</div>

	<!-- Relatedness -->
	<div style='border:1px solid #ddd;padding:8px;'>
	<h3><?php _e('"Relatedness" options','yarpp');?></h3>

	<p><?php _e('YARPP is different than the <a href="http://wasabi.pbwiki.com/Related%20Entries">previous plugins it is based on</a> as it limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.','yarpp');?> <a href="#" class='info'><?php _e('more&gt;','yarpp');?><span><?php _e('The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshhold, take a look at some post\'s related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.','yarpp');?></span></a></p>
	
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
	</div>

<script language="javascript">
//<!--
	function template() {
		if (jQuery('.template').eq(0).attr('checked')) {
			jQuery('.templated').show();
			jQuery('.not_templated').hide();
		} else {
			jQuery('.templated').hide();
			jQuery('.not_templated').show();
		}
		excerpt();
	}
	function excerpt() {
		if (!jQuery('.template').eq(0).attr('checked') && jQuery('.show_excerpt').eq(0).attr('checked'))
			jQuery('.excerpted').show();
		else
			jQuery('.excerpted').hide();
	}
	
	function rss_display() {
		if (jQuery('.rss_display').eq(0).attr('checked'))
			jQuery('.rss_displayed').show();
		else
			jQuery('.rss_displayed').hide();
		rss_excerpt();
	}
	function rss_template() {
		if (jQuery('.rss_template').eq(0).attr('checked')) {
			jQuery('.rss_templated').show();
			jQuery('.rss_not_templated').hide();
		} else {
			jQuery('.rss_templated').hide();
			jQuery('.rss_not_templated').show();
		}
		rss_excerpt();
	}
	function rss_excerpt() {
		if (jQuery('.rss_display').eq(0).attr('checked') && jQuery('.rss_show_excerpt').eq(0).attr('checked'))
			jQuery('.rss_excerpted').show();
		else
			jQuery('.rss_excerpted').hide();
	}
	
	function yarpp_js_init() {
		template();
		rss_template();
		load_display_discats();
		load_display_distags();
		load_display_demo_web();
		load_display_demo_rss();
		jQuery('#build-cache-button').click(function() {
			jQuery('#yarpp-cache-message').hide();
			jQuery('#build-cache-button').hide();
			jQuery('#build-display').css('display','block');
			yarppBuildRequest();
		});
		
		version = jQuery('#yarpp-version').html();

		var json = <?php echo yarpp_check_version_json($display_version); ?>;
		if (json.result == 'newbeta')
		    jQuery('#yarpp-version').addClass('updated').html(<?php echo "'<p>".str_replace('VERSION',"'+json.beta.version+'",str_replace('<A>',"<a href=\"'+json.beta.url+'\">",addslashes(__("There is a new beta (VERSION) of Yet Another Related Posts Plugin. You can <A>download it here</a> at your own risk.","yarpp"))))."</p>'"?>).show();
		if (json.result == 'new')
		    jQuery('#yarpp-version').addClass('updated').html(<?php echo "'<p>".str_replace('VERSION',"'+json.current.version+'",str_replace('<A>',"<a href=\"'+json.current.url+'\">",addslashes(__("There is a new version (VERSION) of Yet Another Related Posts Plugin available! You can <A>download it here</a>.","yarpp"))))."</p>'"?>).show();
	}
	
	jQuery(document).ready(yarpp_js_init);
	
//-->
</script>


		<!-- Display options -->
			<div style='border:1px solid #ddd;padding:8px;'>
		<h3><?php _e("Display options <small>for your website</small>",'yarpp');?></h3>
		
		<table class="form-table" style="margin-top: 0;width:100%">
<?php
checkbox('auto_display',__("Automatically display related posts?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files.",'yarpp')."</span></a>","<tr valign='top'>
			<th class='th-full' colspan='2' scope='row'>",'','<td rowspan="11" style="border-left:8px transparent solid;"><b>'.__("Website display code example",'yarpp').'</b><br /><small>'.__("(Update options to reload.)",'yarpp').'</small><br/>'
."<div id='display_demo_web' style='overflow:auto;width:350px;max-height:500px;'></div></td>");?>

	<?php textbox('limit',__('Maximum number of related posts:','yarpp'))?>
	<?php checkbox('use_template',__("Display using a custom template file",'yarpp')." <span style='color:red;'>".__('NEW!','yarpp')."</span> <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This advanced option gives you full power to customize how your related posts are displayed. Templates (stored in your theme folder) are written in PHP.",'yarpp')."</span></a>","<tr valign='top'><th colspan='2'>",' class="template" onclick="javascript:template()"'); ?>
			<tr valign='top' class='templated'>
				<th><?php _e("Template file:",'yarpp');?></th>
				<td>
					<select name="template_file" id="template_file">
						<?php foreach (glob(STYLESHEETPATH . '/yarpp-template-*.php') as $template): ?>
						<option value='<?php echo htmlspecialchars(basename($template))?>'<?php echo (basename($template)==yarpp_get_option('template_file'))?" selected='selected'":'';?>><?php echo htmlspecialchars(basename($template))?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr valign='top' class='not_templated'>
				<th><?php _e("Before / after related entries:",'yarpp');?></th>
				<td><input name="before_related" type="text" id="before_related" value="<?php echo stripslashes(yarpp_get_option('before_related',true)); ?>" size="10" /> / <input name="after_related" type="text" id="after_related" value="<?php echo stripslashes(yarpp_get_option('after_related',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;ol&gt;&lt;/ol&gt;<?php _e(' or ','yarpp');?>&lt;div&gt;&lt;/div&gt;</small></em>
				</td>
			</tr>
			<tr valign='top' class='not_templated'>
				<th><?php _e("Before / after each related entry:",'yarpp');?></th>
				<td><input name="before_title" type="text" id="before_title" value="<?php echo stripslashes(yarpp_get_option('before_title',true)); ?>" size="10" /> / <input name="after_title" type="text" id="after_title" value="<?php echo stripslashes(yarpp_get_option('after_title',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt;<?php _e(' or ','yarpp');?>&lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>
	<?php checkbox('show_excerpt',__("Show excerpt?",'yarpp'),"<tr class='not_templated' valign='top'><th colspan='2'>",' class="show_excerpt" onclick="javascript:excerpt()"'); ?>
	<?php textbox('excerpt_length',__('Excerpt length (No. of words):','yarpp'),null,"<tr class='excerpted' valign='top'>
				<th>")?>
	
			<tr class="excerpted" valign='top'>
				<th><?php _e("Before / after (Excerpt):",'yarpp');?></th>
				<td><input name="before_post" type="text" id="before_post" value="<?php echo stripslashes(yarpp_get_option('before_post',true)); ?>" size="10" /> / <input name="after_post" type="text" id="after_post" value="<?php echo stripslashes(yarpp_get_option('after_post')); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt;<?php _e(' or ','yarpp');?>&lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>

			<tr valign='top'>
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
	
	<?php textbox('no_results',__('Default display if no results:','yarpp'),'40',"<tr class='not_templated' valign='top'>
				<th>")?>
	<?php checkbox('promote_yarpp',__("Help promote Yet Another Related Posts Plugin?",'yarpp')
	." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.sprintf(__("This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated.", 'yarpp'),"<code>".htmlspecialchars(__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp'))."</code>")	."</span></a>"); ?>
		</table>
		</div>

		<!-- Display options for RSS -->
			<div style='border:1px solid #ddd;padding:8px;'>
		<h3><?php _e("Display options <small>for RSS</small>",'yarpp');?></h3>
		
		<table class="form-table" style="margin-top: 0;width:100%">
<?php

checkbox('rss_display',__("Display related posts in feeds?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option displays related posts at the end of each item in your RSS and Atom feeds. No template changes are needed.",'yarpp')."</span></a>","<tr valign='top'><th colspan='3'>",' class="rss_display" onclick="javascript:rss_display();"');
checkbox('rss_excerpt_display',__("Display related posts in the descriptions?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option displays the related posts in the RSS description fields, not just the content. If your feeds are set up to only display excerpts, however, only the description field is used, so this option is required for any display at all.",'yarpp')."</span></a>","<tr class='rss_displayed' valign='top'>
			<th class='th-full' colspan='2' scope='row'>",'','<td rowspan="9" style="border-left:8px transparent solid;"><b>'.__("RSS display code example",'yarpp').'</b><br /><small>'.__("(Update options to reload.)",'yarpp').'</small><br/>'
."<div id='display_demo_rss' style='overflow:auto;width:350px;max-height:500px;'></div></td>"); ?>
	<?php textbox('rss_limit',__('Maximum number of related posts:','yarpp'),2)?>
	<?php checkbox('rss_use_template',__("Display using a custom template file",'yarpp')." <span style='color:red;'>".__('NEW!','yarpp')."</span> <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This advanced option gives you full power to customize how your related posts are displayed. Templates (stored in your theme folder) are written in PHP.",'yarpp')."</span></a>","<tr valign='top'><th colspan='2'>",' class="rss_template" onclick="javascript:rss_template()"'); ?>
			<tr valign='top' class='rss_templated'>
				<th><?php _e("Template file:",'yarpp');?></th>
				<td>
					<select name="rss_template_file" id="rss_template_file">
						<?php foreach (glob(STYLESHEETPATH . '/yarpp-template-*.php') as $template): ?>
						<option value='<?php echo htmlspecialchars(basename($template))?>'<?php echo (basename($template)==yarpp_get_option('rss_template_file'))?" selected='selected'":'';?>><?php echo htmlspecialchars(basename($template))?></option>
						<?php endforeach; ?>
					</select>
				</td>
			</tr>
			<tr class='rss_not_templated' valign='top'>
				<th><?php _e("Before / after related entries display:",'yarpp');?></th>
				<td><input name="rss_before_related" type="text" id="rss_before_related" value="<?php echo stripslashes(yarpp_get_option('rss_before_related',true)); ?>" size="10" /> / <input name="rss_after_related" type="text" id="rss_after_related" value="<?php echo stripslashes(yarpp_get_option('rss_after_related',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;ol&gt;&lt;/ol&gt;<?php _e(' or ','yarpp');?>&lt;div&gt;&lt;/div&gt;</small></em>
				</td>
			</tr>
			<tr class='rss_not_templated' valign='top'>
				<th><?php _e("Before / after each related entry:",'yarpp');?></th>
				<td><input name="rss_before_title" type="text" id="rss_before_title" value="<?php echo stripslashes(yarpp_get_option('rss_before_title',true)); ?>" size="10" /> / <input name="rss_after_title" type="text" id="rss_after_title" value="<?php echo stripslashes(yarpp_get_option('rss_after_title',true)); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt;<?php _e(' or ','yarpp');?>&lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>
	<?php checkbox('rss_show_excerpt',__("Show excerpt?",'yarpp'),"<tr class='rss_not_templated' valign='top'><th colspan='2'>",' class="rss_show_excerpt" onclick="javascript:rss_excerpt()"'); ?>
	<?php textbox('rss_excerpt_length',__('Excerpt length (No. of words):','yarpp'),null,"<tr class='rss_excerpted' valign='top'>
				<th>")?>
	
			<tr class="rss_excerpted" valign='top'>
				<th><?php _e("Before / after (excerpt):",'yarpp');?></th>
				<td><input name="rss_before_post" type="text" id="rss_before_post" value="<?php echo stripslashes(yarpp_get_option('rss_before_post',true)); ?>" size="10" /> / <input name="rss_after_post" type="text" id="rss_after_post" value="<?php echo stripslashes(yarpp_get_option('rss_after_post')); ?>" size="10" /><em><small> <?php _e("For example:",'yarpp');?> &lt;li&gt;&lt;/li&gt;<?php _e(' or ','yarpp');?>&lt;dl&gt;&lt;/dl&gt;</small></em>
				</td>
			</tr>

			<tr class='rss_displayed' valign='top'>
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
	
	<?php textbox('rss_no_results',__('Default display if no results:','yarpp'),'40',"<tr valign='top' class='rss_not_templated'>
			<th scope='row'>")?>
	<?php checkbox('rss_promote_yarpp',__("Help promote Yet Another Related Posts Plugin?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.sprintf(__("This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated.", 'yarpp'),"<code>".htmlspecialchars(__("Related posts brought to you by <a href='http://mitcho.com/code/yarpp/'>Yet Another Related Posts Plugin</a>.",'yarpp'))."</code>")	."</span></a>","<tr valign='top' class='rss_displayed'>
			<th class='th-full' colspan='2' scope='row'>"); ?>
		</table>
		</div>

	<div style='border:1px solid #ddd;padding:8px;'>
	<h3><?php _e('Advanced','yarpp');?> <span style='color:red;'><?php _e('NEW!','yarpp')?></span></h3>
	
	<table class="form-table" style="margin-top: 0">
	<tr valign='top' colspan='2'><td><input class="thickbox button" type="button" value="<?php _e("Show cache status",'yarpp');?>" title="<?php _e('Related posts cache status','yarpp');?>" alt="#TB_inline?height=100&width=300&inlineId=yarpp-cache-status"/>
	<?php checkbox('ad_hoc_caching',__("When the cache is incomplete, compute related posts on the fly?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.__("If a displayed post's related posts are not cached and this option is on, YARPP will compute them on the fly.<br />If this option is off and a post's related posts have not been cached, it will display as if it has no related posts.",'yarpp')
	."</span></a>"); ?>
	</table>
		</div>

	<script type='text/javascript'>
	//<!--
	time=0;i=0;m=0;id=0;
  timeout = 10000;
	function yarppBuildRequest() {
		jQuery.ajax({
			url:'admin-ajax.php',
			type: 'post',
			data: {action:'yarpp_build_cache_action',i:i,m:m,id:id},
			dataType: 'json',
			timeout: timeout,
			success: function (json) {
				if (json.result == 'success') {
					i = json.i;
					m = json.m;
					id = json.id;
					time = time + parseFloat(json.time);
					var remaining = Math.floor((m-i)*(time/i));
					var min = Math.floor(remaining/60);
					var sec = Math.floor(remaining - 60*min);
					if (i < m) {
						jQuery('#yarpp-bar').css('width',json.percent+'%');
						jQuery('#yarpp-percentage').html(json.percent+'%');
						jQuery('#yarpp-latest').html(json.title);
						if (min > 0) {
							jQuery('#yarpp-time').html(<?php echo str_replace('SEC',"'+sec+'",str_replace('MIN',"'+min+'",__("'MIN minute(s) and SEC second(s) remaining'",'yarpp')));?>);
						} else {
							jQuery('#yarpp-time').html(<?php echo str_replace('SEC',"'+sec+'",__("'SEC second(s) remaining'",'yarpp'));?>);
						}
						yarppBuildRequest();
					} else {
						jQuery('#build-display').html('<p><?php _e("Your related posts cache is now complete.",'yarpp');?><br/><small><?php echo str_replace('SEC',"'+(Math.floor(time*10)/10)+'",__('The SQL queries took SEC seconds.','yarpp'));?></small></p>');
					}
					return;
				} else if (json.result == 'error') {
					i = json.i;
					m = json.m;
					id = json.id;
          jQuery('#yarpp-latest').html('<?php echo str_replace('TITLE',"'+json.title+'",__('There was an error while constructing the related posts for TITLE','yarpp'))?>');
				} else {
          jQuery('#yarpp-latest').html('<?php _e('Constructing the related posts timed out.','yarpp')?>');
				}
				timeout += 5000;
				jQuery('#build-cache-button').show().val('<?php _e("Try to continue...",'yarpp');?>');
			},
			error: function(json) {
				jQuery('#yarpp-latest').html('<?php _e('Constructing the related posts timed out.','yarpp')?>');
				timeout += 5000;
				jQuery('#build-cache-button').show().val('<?php _e("Try to continue...",'yarpp');?>');
			}
		});
		return false;
	}
	//-->
	</script>
	
	<div id='yarpp-cache-status' style='display:none;'><p id='yarpp-cache-message'><?php echo str_replace('PERCENT',floor($cache_complete * 1000)/10,__("Your related posts cache is PERCENT% complete.",'yarpp'));?></p>
		<center><input type='button' class='button' id='build-cache-button' value='build the cache now'/></center>
		<div id='build-display' style='display:none;margin-top:15px;'>
			<div class="progress-container" style='border: 1px solid #ccc; width: 200px; margin: 2px 5px 2px 0; padding: 1px; float: left; background: white;'>
			   <div id='yarpp-bar' style="width: 0%; height: 12px; background-color: #21759B;">&nbsp;</div>
			</div><div id='yarpp-percentage'>0%</div>
			<p style='font-size: .8em' id='yarpp-latest'><?php _e('starting...','yarpp');?></p>
			<p style='font-size: .8em' id='yarpp-time'></p>
		</div>
	</div>
	
	<div>
		<p class="submit">
			<input type="submit" name="update_yarpp" value="<?php _e("Update options",'yarpp')?>" />
			<input type="submit" onclick='return confirm("<?php _e("Do you really want to reset your configuration?",'yarpp');?>");' class="yarpp_warning" name="reset_yarpp" value="<?php _e('Reset options','yarpp')?>" />
		</p>
	</div>
</form>

<?php

?>