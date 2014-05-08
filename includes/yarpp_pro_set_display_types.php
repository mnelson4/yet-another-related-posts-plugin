<?php
if (!isset($_GET['ypsdt']) || $_GET['ypsdt'] === true) die();
$types  = (isset($_GET['types']) && is_array($_GET['types'])) ? $_GET['types'] : array();

include_once(realpath('../../../../').'/wp-config.php');
$yarppPro = get_option('yarpp_pro');
$yarppPro['auto_display_post_types'] = $types;
update_option('yarpp_pro',$yarppPro);

header('Content-Type: text/plain');
die('ok');