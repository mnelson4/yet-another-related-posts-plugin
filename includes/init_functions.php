<?php
/* Init Functions ---------------------------------------------------------------------------------------------------*/

function narpp_init() {
	global $yarpp, $narpp;
	$yarpp = new NARPP_Core;
}

function narpp_plugin_activate($network_wide) {
    update_option('yarpp_activated', true);
}

function yarpp_set_option($options, $value = null) {
	global $yarpp;
	$yarpp->set_option($options, $value);
}

function yarpp_get_option($option = null) {
	global $yarpp;
	return $yarpp->get_option($option);
}
