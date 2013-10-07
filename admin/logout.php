<?php
     session_start();
     session_destroy();

     $hostname = $_SERVER['HTTP_HOST'];
     $path = dirname(dirname($_SERVER['SCRIPT_NAME']));

     header('Location: http://'.$hostname.($path == '/' ? '' : $path).'/');
?>
