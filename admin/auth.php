<?php
ini_set("session.use_trans_sid", false);
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'proto.inc.php');
session_start();

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: ' . proto() . "://$hostname" .
    	($path == '/' ? '' : $path) . '/login.php?url=' .
	urlencode($_SERVER["REQUEST_URI"]));
    exit;
}

// eof
