<?php
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 $get = strip($_GET);

 $meta['robots']        = "noindex, follow";
 $c['meta']['title'] = $l['err404_topic'];
 $c['main'] = "<h2>".$l['err404_topic']."</h2>\n<p>".$l['err404_message']."</p>";
 $c['main'] .= '     <form id="searchform" action="'.JLOG_PATH.'/search.php" accept-charset="UTF-8">
      <p><input class="userdata" type="text" name="q" size="30" value="'.htmlspecialchars($get['url']).'" />
         <input class="send" type="submit" value="'.$l['content_search'].'" /></p>
     </form>';

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
