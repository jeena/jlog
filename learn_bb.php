<?php
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 $version = $_GET['v'] ?? '';

if($version == "small") {
echo "
 <html>
  <head>
  <title>".$l['bbtitle']."</title>
   <link rel='stylesheet' href='".JLOG_PATH."/personal/css/popup.css' type='text/css' media='screen' />
  </head>
  <body>
   <h1>".$l['bbtitle']."</h1>
     ".$l['bbxmp']."
  </body>
 </html>";
}

else {
 $c['meta']['title'] = $c['title'] = $l['bbtitle'];
 $c['main'] = $l['bbxmp'];

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;

}
?>
