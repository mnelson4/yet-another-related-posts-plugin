<?php

class YARPP_Meta_Box {
	function checkbox($option,$desc,$tr="<tr valign='top'>
				<th class='th-full' colspan='2' scope='row'>",$inputplus = '',$thplus='') {
		echo "			$tr<input $inputplus type='checkbox' name='$option' value='true'". ((yarpp_get_option($option) == 1) ? ' checked="checked"': '' )."  /> $desc</th>$thplus
			</tr>";
	}
	function textbox($option,$desc,$size=2,$tr="<tr valign='top'>
				<th scope='row'>", $note = '') {
		$value = stripslashes(yarpp_get_option($option,true));
		echo "			$tr$desc</th>
				<td><input name='$option' type='text' id='$option' value='$value' size='$size' />";
		if ( !empty($note) )
			echo " <em><small>{$note}</small></em>";
		echo "</td>
			</tr>";
	}
	function beforeafter($options,$desc,$size=10,$tr="<tr valign='top'>
				<th scope='row'>", $note = '') {
		echo "			$tr$desc</th>
				<td>";
		$value = stripslashes(yarpp_get_option($options[0],true));
		echo "<input name='{$options[0]}' type='text' id='{$options[0]}' value='$value' size='$size' /> / ";
		$value = stripslashes(yarpp_get_option($options[1],true));
		echo "<input name='{$options[1]}' type='text' id='{$options[1]}' value='$value' size='$size' />";
		if ( !empty($note) )
			echo " <em><small>{$note}</small></em>";
		echo "</td>
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
													<select name='$option'>
				<option $inputplus value='1'". (($value == 1) ? ' selected="selected"': '' )."  > ".__("do not consider",'yarpp')."</option>
				<option $inputplus value='2'". (($value == 2) ? ' selected="selected"': '' )."  > ".__("consider",'yarpp')."</option>
				<option $inputplus value='3'". (($value == 3) ? ' selected="selected"': '' )."  >
				".sprintf(__("require at least one %s in common",'yarpp'),__($type,'yarpp'))."</option>
				<option $inputplus value='4'". (($value == 4) ? ' selected="selected"': '' )."  >
				".sprintf(__("require more than one %s in common",'yarpp'),__($type,'yarpp'))."</option>
													</select>
				</td>
			</tr>";
	}
	
	function importance2($option,$desc,$type='word',$tr="<tr valign='top'>
				<th scope='row'>",$inputplus = '') {
		$value = yarpp_get_option($option);
	
		echo "		$tr$desc</th>
				<td>
													<select name='$option'>
				<option $inputplus value='1'". (($value == 1) ? ' selected="selected"': '' )."  >".__("do not consider",'yarpp')."</option>
				<option $inputplus value='2'". (($value == 2) ? ' selected="selected"': '' )."  > ".__("consider",'yarpp')."</option>
				<option $inputplus value='3'". (($value == 3) ? ' selected="selected"': '' )."  > ".__("consider with extra weight",'yarpp')."</option>
													</select>
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
}

class YARPP_Meta_Box_Pool extends YARPP_Meta_Box {
	function display() {
?>
	<p><?php _e('"The Pool" refers to the pool of posts and pages that are candidates for display as related to the current entry.','yarpp');?></p>

	<table class="form-table" style="margin-top: 0; clear:none;">
		<tbody>
			<tr valign='top'>
				<th scope='row'><?php _e('Disallow by category:','yarpp');?></th><td><div id='display_discats' style="overflow:auto;max-height:100px;"></div></td></tr>
			<tr valign='top'>
				<th scope='row'><?php _e('Disallow by tag:','yarpp');?></th>
				<td><div id='display_distags' style="overflow:auto;max-height:100px;"></div></td></tr>
<?php
	$this->checkbox('show_pass_post',__("Show password protected posts?",'yarpp'));

	$recent_number = "<input name=\"recent_number\" type=\"text\" id=\"recent_number\" value=\"".stripslashes(yarpp_get_option('recent_number',true))."\" size=\"2\" />";
	$recent_units = "<select name=\"recent_units\" id=\"recent_units\">
		<option value='day'". (('day'==yarpp_get_option('recent_units'))?" selected='selected'":'').">".__('day(s)','yarpp')."</option>
		<option value='week'". (('week'==yarpp_get_option('recent_units'))?" selected='selected'":'').">".__('week(s)','yarpp')."</option>
		<option value='month'". (('month'==yarpp_get_option('recent_units'))?" selected='selected'":'').">".__('month(s)','yarpp')."</option>
	</select>";
	$this->checkbox('recent_only',str_replace('NUMBER',$recent_number,str_replace('UNITS',$recent_units,__("Show only posts from the past NUMBER UNITS",'yarpp'))));
?>

		</tbody>
	</table>
<?php
	}
}

add_meta_box('yarpp_pool', __('"The Pool"','yarpp'), array(new YARPP_Meta_Box_Pool, 'display'), 'settings_page_yarpp', 'normal', 'core');

class YARPP_Meta_Box_Relatedness extends YARPP_Meta_Box {
	function display() {
		global $yarpp_myisam;
?>
	<p><?php _e('YARPP limits the related posts list by (1) a maximum number and (2) a <em>match threshold</em>.','yarpp');?> <a href="#" class='info'><?php _e('more&gt;','yarpp');?><span><?php _e('The higher the match threshold, the more restrictive, and you get less related posts overall. The default match threshold is 5. If you want to find an appropriate match threshhold, take a look at some post\'s related posts display and their scores. You can see what kinds of related posts are being picked up and with what kind of match scores, and determine an appropriate threshold for your site.','yarpp');?></span></a></p>

	<table class="form-table" style="margin-top: 0; clear:none;">
		<tbody>

<?php
	$this->textbox('threshold',__('Match threshold:','yarpp'));
	$this->importance2('title',__("Titles: ",'yarpp'),'word',"<tr valign='top'>
			<th scope='row'>",(!$yarpp_myisam?' readonly="readonly" disabled="disabled"':''));
	$this->importance2('body',__("Bodies: ",'yarpp'),'word',"<tr valign='top'>
			<th scope='row'>",(!$yarpp_myisam?' readonly="readonly" disabled="disabled"':''));
	$this->importance('tags',__("Tags: ",'yarpp'),'tag',"<tr valign='top'>
			<th scope='row'>",'');
	$this->importance('categories',__("Categories: ",'yarpp'),'category',"<tr valign='top'>
			<th scope='row'>",'');
	$this->checkbox('cross_relate',__("Cross-relate posts and pages?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("When the \"Cross-relate posts and pages\" option is selected, the <code>related_posts()</code>, <code>related_pages()</code>, and <code>related_entries()</code> all will give the same output, returning both related pages and posts.",'yarpp')."</span></a>");
	$this->checkbox('past_only',__("Show only previous posts?",'yarpp'));
?>
			</tbody>
		</table>
<?php
	}
}

add_meta_box('yarpp_relatedness', __('"Relatedness" options','yarpp'), array(new YARPP_Meta_Box_Relatedness, 'display'), 'settings_page_yarpp', 'normal', 'core');

class YARPP_Meta_Box_Display_Web extends YARPP_Meta_Box {
	function display() {
		global $yarpp_templateable;
	?>
		<table class="form-table" style="margin-top: 0; clear:none;">
		<tbody>
<?php
		$this->checkbox('auto_display',__("Automatically display related posts?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option automatically displays related posts right after the content on single entry pages. If this option is off, you will need to manually insert <code>related_posts()</code> or variants (<code>related_pages()</code> and <code>related_entries()</code>) into your theme files.",'yarpp')."</span></a>","<tr valign='top'>
			<th class='th-full' colspan='2' scope='row' style='width:100%;'>",'','<td rowspan="3" style="border-left:8px transparent solid;"><b>'.__("Website display code example",'yarpp').'</b><br /><small>'.__("(Update options to reload.)",'yarpp').'</small><br/>'
."<div id='display_demo_web' style='overflow:auto;width:350px;max-height:500px;'></div></td>");
		$this->textbox('limit',__('Maximum number of related posts:','yarpp'));
		$this->checkbox('use_template',__("Display using a custom template file",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This advanced option gives you full power to customize how your related posts are displayed. Templates (stored in your theme folder) are written in PHP.",'yarpp')."</span></a>","<tr valign='top'><th colspan='2'>",' class="template"'.(!$yarpp_templateable?' disabled="disabled"':'')); ?>
		</tbody></table>
		<table class="form-table" style="clear:none;"><tbody>
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
	<?php
	$this->beforeafter(array('before_related', 'after_related'),__("Before / after related entries:",'yarpp'),15,"<tr class='not_templated' valign='top'>\r\t\t\t\t<th>", __("For example:",'yarpp') . ' &lt;ol&gt;&lt;/ol&gt;' . __(' or ','yarpp') . '&lt;div&gt;&lt;/div&gt;');
	$this->beforeafter(array('before_title', 'after_title'),__("Before / after each related entry:",'yarpp'),15,"<tr class='not_templated' valign='top'>\r\t\t\t\t<th>", __("For example:",'yarpp') . ' &lt;li&gt;&lt;/li&gt;' . __(' or ','yarpp') . '&lt;dl&gt;&lt;/dl&gt;');
	
	$this->checkbox('show_excerpt',__("Show excerpt?",'yarpp'),"<tr class='not_templated' valign='top'><th colspan='2'>",' class="show_excerpt"');
	$this->textbox('excerpt_length',__('Excerpt length (No. of words):','yarpp'),10,"<tr class='excerpted' valign='top'>
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

	<?php $this->textbox('no_results',__('Default display if no results:','yarpp'),'40',"<tr class='not_templated' valign='top'>
				<th>")?>
	<?php $this->checkbox('promote_yarpp',__("Help promote Yet Another Related Posts Plugin?",'yarpp')
	." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.sprintf(__("This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated.", 'yarpp'),"<code>".htmlspecialchars(sprintf(__("Related posts brought to you by <a href='%s'>Yet Another Related Posts Plugin</a>.",'yarpp'), 'http://yarpp.org'))."</code>")	."</span></a>"); ?>
		</tbody>
		</table>
<?php
	}
}

add_meta_box('yarpp_display_web', __('Display options <small>for your website</small>','yarpp'), array(new YARPP_Meta_Box_Display_Web, 'display'), 'settings_page_yarpp', 'normal', 'core');

class YARPP_Meta_Box_Display_Feed extends YARPP_Meta_Box {
	function display() {
		global $yarpp_templateable;
?>
		<table class="form-table" style="margin-top: 0; clear:none;"><tbody>
<?php

$this->checkbox('rss_display',__("Display related posts in feeds?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option displays related posts at the end of each item in your RSS and Atom feeds. No template changes are needed.",'yarpp')."</span></a>","<tr valign='top'><th colspan='2' style='width:100%'>",' class="rss_display"','<td class="rss_displayed" rowspan="4" style="border-left:8px transparent solid;"><b>'.__("RSS display code example",'yarpp').'</b><br /><small>'.__("(Update options to reload.)",'yarpp').'</small><br/>'
."<div id='display_demo_rss' style='overflow:auto;width:350px;max-height:500px;'></div></td>");
$this->checkbox('rss_excerpt_display',__("Display related posts in the descriptions?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This option displays the related posts in the RSS description fields, not just the content. If your feeds are set up to only display excerpts, however, only the description field is used, so this option is required for any display at all.",'yarpp')."</span></a>","<tr class='rss_displayed' valign='top'>
			<th class='th-full' colspan='2' scope='row'>");

	$this->textbox('rss_limit',__('Maximum number of related posts:','yarpp'),2, "<tr valign='top' class='rss_displayed'>
				<th scope='row'>");
	$this->checkbox('rss_use_template',__("Display using a custom template file",'yarpp')." <!--<span style='color:red;'>".__('NEW!','yarpp')."</span>--> <a href='#' class='info'>".__('more&gt;','yarpp')."<span>".__("This advanced option gives you full power to customize how your related posts are displayed. Templates (stored in your theme folder) are written in PHP.",'yarpp')."</span></a>","<tr valign='top' class='rss_displayed'><th colspan='2'>",' class="rss_template"'.(!$yarpp_templateable?' disabled="disabled"':'')); ?>
	</tbody></table>
	<table class="form-table rss_displayed" style="clear:none;">
		<tbody>
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

	<?php 
	$this->beforeafter(array('rss_before_related', 'rss_after_related'),__("Before / after related entries:",'yarpp'),15,"<tr class='rss_not_templated' valign='top'>\r\t\t\t\t<th>", __("For example:",'yarpp') . ' &lt;ol&gt;&lt;/ol&gt;' . __(' or ','yarpp') . '&lt;div&gt;&lt;/div&gt;');
	$this->beforeafter(array('rss_before_title', 'rss_after_title'),__("Before / after each related entry:",'yarpp'),15,"<tr class='rss_not_templated' valign='top'>\r\t\t\t\t<th>", __("For example:",'yarpp') . ' &lt;li&gt;&lt;/li&gt;' . __(' or ','yarpp') . '&lt;dl&gt;&lt;/dl&gt;');
	
	$this->checkbox('rss_show_excerpt',__("Show excerpt?",'yarpp'),"<tr class='rss_not_templated' valign='top'><th colspan='2'>",' class="rss_show_excerpt"');
	$this->textbox('rss_excerpt_length',__('Excerpt length (No. of words):','yarpp'),10,"<tr class='rss_excerpted' valign='top'>\r\t\t\t\t<th>");

	$this->beforeafter(array('rss_before_post', 'rss_after_post'),__("Before / after (excerpt):",'yarpp'),10,"<tr class='rss_excerpted' valign='top'>\r\t\t\t\t<th>", __("For example:",'yarpp') . ' &lt;li&gt;&lt;/li&gt;' . __(' or ','yarpp') . '&lt;dl&gt;&lt;/dl&gt;');

	?>
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

	<?php $this->textbox('rss_no_results',__('Default display if no results:','yarpp'),'40',"<tr valign='top' class='rss_not_templated'>
			<th scope='row'>")?>
	<?php $this->checkbox('rss_promote_yarpp',__("Help promote Yet Another Related Posts Plugin?",'yarpp')." <a href='#' class='info'>".__('more&gt;','yarpp')."<span>"
	.sprintf(__("This option will add the code %s. Try turning it on, updating your options, and see the code in the code example to the right. These links and donations are greatly appreciated.", 'yarpp'),"<code>".htmlspecialchars(sprintf(__("Related posts brought to you by <a href='%s'>Yet Another Related Posts Plugin</a>.",'yarpp'), 'http://yarpp.org'))."</code>")	."</span></a>","<tr valign='top' class='rss_displayed'>
			<th class='th-full' colspan='2' scope='row'>"); ?>
		</tbody></table>
<?php
	}
}

add_meta_box('yarpp_display_rss', __('Display options <small>for RSS</small>','yarpp'), array(new YARPP_Meta_Box_Display_Feed, 'display'), 'settings_page_yarpp', 'normal', 'core');

class YARPP_Meta_Box_Contact extends YARPP_Meta_Box {
	function display() {
		$pluginurl = plugin_dir_url(__FILE__);
		?>
		<ul class='yarpp_contacts'>
		<li  style="background: url(<?php echo $pluginurl . 'wordpress.png'; ?>) no-repeat left bottom;"><a href="http://wordpress.org/tags/yet-another-related-posts-plugin" target="_blank"><?php _e('YARPP Forum', 'yarpp'); ?></a></li>
		<li style="background: url(<?php echo $pluginurl . 'twitter.png' ; ?>) no-repeat left bottom;"><a href="http://twitter.com/yarpp" target="_blank"><?php _e('YARPP on Twitter', 'yarpp'); ?></a></li>
		<li style="background: url(<?php echo $pluginurl . 'plugin.png'; ?>) no-repeat left bottom;"><a href="http://yarpp.org" target="_blank"><?php _e('YARPP on the Web', 'yarpp'); ?></a></li>
		<li style="background: url(<?php echo $pluginurl . 'paypal-icon.png'; ?>) no-repeat left bottom;"><a href='http://tinyurl.com/donatetomitcho' target='_new'><img src="https://www.paypal.com/<?php echo paypal_directory(); ?>i/btn/btn_donate_SM.gif" name="submit" alt="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal');?>" title="<?php _e('Donate to mitcho (Michael Yoshitaka Erlewine) for this plugin via PayPal','yarpp');?>"/></a></li>
	 </ul>
<?php
	}
}

add_meta_box('yarpp_display_contact', __('Contact YARPP','yarpp'), array(new YARPP_Meta_Box_Contact, 'display'), 'settings_page_yarpp', 'side', 'core');

// since 3.3: hook for registering new YARPP meta boxes
do_action( 'add_meta_boxes_settings_page_yarpp' );

