<?php
ini_set("session.use_trans_sid", false);
session_start();

$hostname = $_SERVER['HTTP_HOST'];
$path = dirname($_SERVER['SCRIPT_NAME']);

if (!isset($_SESSION['logged_in']) || !$_SESSION['logged_in']) {
    header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/login.php?url='.urlencode($_SERVER["REQUEST_URI"]));
    exit;
}

// eof
