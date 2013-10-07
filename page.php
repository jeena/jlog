<?php
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

 $get = strip($_GET);
 
 $date = strftime(JLOG_DATE);
 
 $sql_get = escape_for_mysql($get);

	$sql = "SELECT id, url, topic, UNIX_TIMESTAMP(date) AS date,
		         DATE_FORMAT(date, '%Y-%m-%dT%T".substr(date("O"), 0, 3) . ":" . substr(date("O"), 3)."') AS metadate,
			 teaser, teaserpic, teaserpiconblog, keywords,
			 content, comments, allowpingback, section
			  FROM ".JLOG_DB_CONTENT."
			  WHERE 
			  		url			= '".$sql_get['url']."' AND
			  		section		= 'page'
			  LIMIT 1";

    $blog = new Query($sql);
     if($blog->error()) {
        echo "<pre>\n";
        echo $blog->getError();
        echo "</pre>\n";
        die();
     }

 if($blog->numRows() == 0) {
  header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
  include_once(JLOG_BASEPATH."error404.php");
  exit;
 }
 $daten = $blog->fetch();

 $c['meta']['date']			= $daten['metadate'];
 $c['meta']['description'] = strip_tags($bbcode->parse($daten['teaser']));
 $c['meta']['keywords']	   = $daten['keywords'];
 $c['meta']['title']			= $daten['topic'];

 $c['main']	= do_entry($daten, NULL, 'page');


require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
