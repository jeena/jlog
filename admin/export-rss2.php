<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 define("JLOG_EXPORT_RSS2", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'update.php');
 
 header('Content-type: application/xml');
 header('Content-Disposition: attachment; filename="jlog-rss2.xml"');

 echo $data['rss_full'];

?>
