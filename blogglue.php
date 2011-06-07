<?php
function yarpp_show_blogglue_upsell() {
  if (current_user_can('activate_plugins') &&
			get_option( 'yarpp_blogglue_promo_runonce', false) !== false)
		return;
	update_option( 'yarpp_blogglue_promo_runonce', true );
?>
<style type="text/css">
#TB_window { min-width: 670px; min-height: 370px; max-height: 370px; }
#blogglue_top { color: black; padding: 15px; border-bottom: 1px solid #CCC; overflow:auto; }
#blogglue_top img { float: left; border: 3px solid #555;margin-right: 15px; }
#blogglue_top p { line-height: 1.7em; }
#blogglue_bottom, #blogglue_bottom ul { text-align: center; }
#blogglue_bottom h2 { font-family: "Copperplate Gothic Light"; font-size: 1.3em; }
#blogglue_bottom a { display: block; width: 150px; padding: 7px 5px; color: white !important; text-decoration: none; font-size: 18px; margin: 0 auto; background-color: #88AF3D; border: 2px solid #5C5C5C; border-radius: 5px; -moz-border-radius: 5px; -webkit-border-radius: 5px; text-align: center; }
#signature { float:right; }
#disclaimer { color: #666; font-size: 10px; padding-bottom: 0.6em }
p.button_info { margin: 10px auto; font-size: 10px; color: #666; font-weight: bold; width: 150px; }
</style>
<div id="blogglue_update" style="display: none;">
<div id="blogglue_top">
<img src="<?php echo plugins_url( 'mitcho-small.jpg', __FILE__ ) ; ?>" alt="mitcho's Photo"/>
<h2>A Note From YARPP's Author, mitcho</h2>
<p>For a long time YARPP has been great at linking posts within a single blog. BlogGlue gives your site Related Posts from across the BlogGlue Network and your posts will also show up on other sites you trust. <strong>BlogGlue is the next generation of YARPP.</strong>
<span id="signature">&mdash; mitcho</span></p>
</div>
<div id="blogglue_bottom">
<table>
<tr>
<td width="200">
  <a href="<?php echo  wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=arkayne-site-to-site-related-content'), 'install-plugin_arkayne-site-to-site-related-content'); ?>">Get BlogGlue</a>
  <p class="button_info">Get your posts promoted by other blogs today.</p>
</td>
<td width="250">
<h2>Benefits Of BlogGlue</h2>
<ul>
	<li>Related Links on Partner Blogs</li>
	<li>Related Links in Tweets</li>
	<li>Off-server Processing</li>
	<li>Built-in SEO Analyzer</li>
</ul>
</td>
<td width="200">
  <a href="#" onclick="tb_remove(); return false;">No Thanks</a>
  <p class="button_info">I don't want other blogs promoting me.</p>
</td>
</tr>
<tr><td colspan="3" id="disclaimer">Installing BlogGlue will deactivate YARPP. Your YARPP settings will be saved.<img src="http://www.blogglue.com/cohorts/track/yarpp_popup.gif"></td></tr>
</table>
</div>
</div>
<?php
	echo '<script>window.onload=function(){ tb_show("BIG ANNOUNCEMENT: YARPP has partnered with BlogGlue to connect blogs.", "#TB_inline?height=600&width=650&inlineId=blogglue_update", null); }</script>';
}
add_action('admin_print_scripts-plugins.php', 'yarpp_show_blogglue_upsell' );

function add_yarpp_blogglue_meta_box() {
	class YARPP_Meta_Box_BlogGlue extends YARPP_Meta_Box {
		function display() {
			$pluginurl = plugin_dir_url(__FILE__);
			?>
<style type="text/css">
#blogglue_upsell {
	text-align: center;
}
#blogglue_upsell ul {
	list-style-type: disc;
	list-style-position: inside;
	text-align: left;
	margin: 10px 0 10px 15px;
}
#blogglue_install_steps {
	text-align: center;
	height: 200px;
}
#TB_ajaxContent {
	height: 220px !important;
}
ul.install_help {
	list-style-type: disc;
	list-style-position: inside;
	text-align: left;
	margin: 20px 0px;
}
</style>
<div id="blogglue_upsell">
	<img src="http://s3.amazonaws.com/arkayne-media/img/logo-md.png" alt="BlogGlue Logo"/>
	<ul>
		<li>Related Links on Partner Blogs</li>
		<li>Related Links in Tweets</li>
		<li>Off-server Processing</li>
		<li>Built-in SEO Analyzer</li>
	</ul>
	<a href="#TB_inline?height=300&width=400&inlineId=blogglue_install" class="thickbox"><img src="http://s3.amazonaws.com/arkayne-media/img/email_try.png" alt="Upgrade"/></a><img src="http://www.blogglue.com/cohorts/track/yarpp_sidebar.gif"/>
</div>
<div id="blogglue_install" style="display: none;">
	<div id="blogglue_install_steps">
	<img src="http://s3.amazonaws.com/arkayne-media/img/logo.png" alt="BlogGlue Logo"/>
	<ul class="install_help">
		<li>Installing BlogGlue will disable YARPP</li>
		<li>Your YARPP settings will still be saved</li>
		<li>Once the download is complete, activate the BlogGlue plugin</li>
		<li>After the plugin is activated, follow the signup instructions</li>
	</ul>
	<a href="<?php echo  wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=arkayne-site-to-site-related-content'), 'install-plugin_arkayne-site-to-site-related-content'); ?>"><img src="http://s3.amazonaws.com/arkayne-media/img/start_free_sm.png" width="94" height="30"/></a>
	</div>
</div>
	<?php
		}
	}
	
	add_meta_box('yarpp_display_blogglue', 'Upgrade To BlogGlue', array(new YARPP_Meta_Box_BlogGlue, 'display'), 'settings_page_yarpp', 'side', 'core');
}
add_action( 'add_meta_boxes_settings_page_yarpp', 'add_yarpp_blogglue_meta_box' );
