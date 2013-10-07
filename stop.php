<?php
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

 $c['meta']['robots'] 		= "noindex, follow";
 $c['title']					= JLOG_WEBSITE;

 $stop = escape_for_mysql(strip($_GET));

 $sql = "UPDATE ".JLOG_DB_COMMENTS." SET 
              mail_by_comment = '0'
         WHERE
          reference = '".$stop['id']."' AND
          email     = '".$stop['email']."'";
 
    $stop = new Query($sql);
     if($stop->error()) {
        echo "<pre>\n";
        echo $stop->getError();
        echo "</pre>\n";
        die();
     }
   
 $c['main'] = "<p>".$l['comments_stop_successful']."</p>";

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
