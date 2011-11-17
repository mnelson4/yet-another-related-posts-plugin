<?php
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
		<li>Display related posts from your own site as well as other sites</li>
		<li>Your content linked to from related content on other sites</li>
		<!--<li>Off-server Processing</li>-->
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
