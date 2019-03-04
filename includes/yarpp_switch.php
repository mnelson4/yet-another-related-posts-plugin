<?php
if (!isset($_GET['go']) || trim($_GET['go']) === '') die();

include_once(realpath('../../../../').'/wp-config.php');
$switch = htmlentities($_GET['go']);