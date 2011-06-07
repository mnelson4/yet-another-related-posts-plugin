<?php

global $wpdb, $yarpp_value_options, $yarpp_binary_options, $wp_version, $yarpp_cache, $yarpp_templateable, $yarpp_myisam;

// Reenforce YARPP setup:
if (!get_option('yarpp_version'))
  yarpp_activate();
else
  yarpp_upgrade_check();

// if action=flush, reset the cache
if (isset($_GET['action']) && $_GET['action'] == 'flush') {
  $yarpp_cache->flush();
}

// check to see that templates are in the right place
$yarpp_templateable = (count(glob(STYLESHEETPATH . '/yarpp-template-*.php')) > 0);
if (!$yarpp_templateable) {
  yarpp_set_option('use_template',false);
  yarpp_set_option('rss_use_template',false);
}

// 3.3: move version checking here, in PHP:
if ( current_user_can('update_plugins' ) ) {
	$yarpp_version_info = yarpp_version_info();
	
	// these strings are not localizable, as long as the plugin data on wordpress.org
	// cannot be.
	$slug = 'yet-another-related-posts-plugin';
	$plugin_name = 'Yet Another Related Posts Plugin';
	$file = basename(YARPP_DIR) . '/yarpp.php';
	if ( $yarpp_version_info['result'] == 'new' ) {
		// make sure the update system is aware of this version
		$current = get_site_transient( 'update_plugins' );
		if ( !isset( $current->response[ $file ] ) ) {
			delete_site_transient( 'update_plugins' );
			wp_update_plugins();
		}
	
		echo '<div class="updated"><p>';
		$details_url = self_admin_url('plugin-install.php?tab=plugin-information&plugin=' . $slug . '&TB_iframe=true&width=600&height=800');
		printf( __('There is a new version of %1$s available. <a href="%2$s" class="thickbox" title="%3$s">View version %4$s details</a> or <a href="%5$s">update automatically</a>.'), $plugin_name, esc_url($details_url), esc_attr($plugin_name), $yarpp_version_info['current']['version'], wp_nonce_url( self_admin_url('update.php?action=upgrade-plugin&plugin=') . $file, 'upgrade-plugin_' . $file) );
		echo '</p></div>';
	} else if ( $yarpp_version_info['result'] == 'newbeta' ) {
		echo '<div class="updated"><p>';
		printf(__("There is a new beta (%s) of Yet Another Related Posts Plugin. You can <a href=\"%s\">download it here</a> at your own risk.","yarpp"), $yarpp_version_info['beta']['version'], $yarpp_version_info['beta']['url']);
		echo '</p></div>';
	}
}

if (isset($_POST['myisam_override'])) {
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

if ($yarpp_myisam && !yarpp_enabled()) {
  echo '<div class="updated"><p>';
  if (yarpp_activate()) {
    _e('The YARPP database had an error but has been fixed.','yarpp');
  } else {
    _e('The YARPP database has an error which could not be fixed.','yarpp');
    printf(__('Please try <a href="%s" target="_blank">manual SQL setup</a>.','yarpp'), 'http://mitcho.com/code/yarpp/sql.php?prefix='.urlencode($wpdb->prefix));
  }
  echo '</div></p>';
}

if (isset($_POST['update_yarpp'])) {
	foreach (array_keys($yarpp_value_options) as $option) {
    if (isset($_POST[$option]) && is_string($_POST[$option]))
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
	
	foreach (array_keys($yarpp_binary_options) as $option) {
		(isset($_POST[$option])) ? yarpp_set_option($option,1) : yarpp_set_option($option,0);
	}
	echo '<div class="updated fade"><p>'.__('Options saved!','yarpp').'</p></div>';
}

?>
<script type="text/javascript">
//<!--

// since 3.3: add screen option toggles
jQuery(function() {
	postboxes.add_postbox_toggles(pagenow);
});

var spinner = '<?php echo esc_url( admin_url( 'images/wpspin_light.gif' ) ); ?>';

function load_display_demo_web() {
	jQuery.ajax({type:'POST',
	  url: ajaxurl,
	  data:'action=yarpp_display_demo_web',
	  beforeSend:function(){jQuery('#display_demo_web').eq(0).html('<img src="' + spinner + '" alt="loading..."/>')},
	  success:function(html){jQuery('#display_demo_web').eq(0).html('<pre>'+html+'</pre>')},
	  dataType:'html'}
	)
}

function load_display_demo_rss() {
	jQuery.ajax({type:'POST',
	  url: ajaxurl,
	  data:'action=yarpp_display_demo_rss',
	  beforeSend:function(){jQuery('#display_demo_rss').eq(0).html('<img src="'+spinner+'" alt="loading..."/>')},
	  success:function(html){jQuery('#display_demo_rss').eq(0).html('<pre>'+html+'</pre>')},
	  dataType:'html'}
	)
}

function load_display_distags() {
	jQuery.ajax({type:'POST',
	  url: ajaxurl,
	  data:'action=yarpp_display_distags',
	  beforeSend:function(){jQuery('#display_distags').eq(0).html('<img src="'+spinner+'" alt="loading..."/>')},
	  success:function(html){jQuery('#display_distags').eq(0).html(html)},
	  dataType:'html'}
	)
}

function load_display_discats() {
	jQuery.ajax({type:'POST',
	  url: ajaxurl,
	  data:'action=yarpp_display_discats',
	  beforeSend:function(){jQuery('#display_discats').eq(0).html('<img src="'+spinner+'" alt="loading..."/>')},
	  success:function(html){jQuery('#display_discats').eq(0).html(html)},
	  dataType:'html'}
	)
}
//-->
</script>

<div class="wrap">
		<h2>
			<?php _e('Yet Another Related Posts Plugin Options','yarpp');?> <small><?php
      echo yarpp_get_option('version');
			?></small>
		</h2>

	<?php echo "<div id='yarpp-version' style='display:none;'>".yarpp_get_option('version')."</div>"; ?>

	<form method="post">

  <div id="yarpp_author_text">
	<small><?php printf(__('by <a href="%s" target="_blank">mitcho (Michael 芳貴 Erlewine)</a>','yarpp'), 'http://yarpp.org/');?></small>
  </div>

<!--	<div style='border:1px solid #ddd;padding:8px;'>-->

<?php
wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', false );
wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', false );
?>
<div id="poststuff" class="metabox-holder has-right-sidebar">

<div class="inner-sidebar" id="side-info-column">
<?php
do_meta_boxes('settings_page_yarpp', 'side', array());
?>
</div>

<div id="post-body-content">
<?php
do_meta_boxes('settings_page_yarpp', 'normal', array());
?>
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
	jQuery('.template').click(template);
	
	function excerpt() {
		if (!jQuery('.template').eq(0).attr('checked') && jQuery('.show_excerpt').eq(0).attr('checked'))
			jQuery('.excerpted').show();
		else
			jQuery('.excerpted').hide();
	}
	jQuery('.show_excerpt,.template').click(excerpt);

	function rss_display() {
		if (jQuery('.rss_display').eq(0).attr('checked'))
			jQuery('.rss_displayed').show();
		else
			jQuery('.rss_displayed').hide();
		rss_excerpt();
	}
	jQuery('.rss_display').click(rss_display);
	
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
	jQuery('.rss_template').click(rss_template);
	
	function rss_excerpt() {
		if (jQuery('.rss_display').eq(0).attr('checked') && jQuery('.rss_show_excerpt').eq(0).attr('checked'))
			jQuery('.rss_excerpted').show();
		else
			jQuery('.rss_excerpted').hide();
	}
	jQuery('.rss_display,.rss_show_excerpt').click(rss_excerpt);

	function yarpp_js_init() {
		template();
		rss_template();
		load_display_discats();
		load_display_distags();
		load_display_demo_web();
		load_display_demo_rss();

		var version = jQuery('#yarpp-version').html();
	}

	jQuery(yarpp_js_init);
	//-->
	</script>

	<div>
		<p class="submit">
			<input type="submit" class='button-primary' name="update_yarpp" value="<?php _e("Update options",'yarpp')?>" />
			<!--<input type="submit" onclick='return confirm("<?php _e("Do you really want to reset your configuration?",'yarpp');?>");' class="yarpp_warning" name="reset_yarpp" value="<?php _e('Reset options','yarpp')?>" />-->
		</p>
	</div>
<!--cache engine: <?php echo $yarpp_cache->name;?>; cache status: <?php echo $yarpp_cache->cache_status();?>-->

</form>
