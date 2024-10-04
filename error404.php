<?php
sleep(1);	// XXX: Remove if not wanted
 require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 $get = strip($_GET);

 $meta['robots']        = "noindex, follow";
 $c['meta']['title'] = $l['err404_topic'];
 $c['main'] = "<h2>".$l['err404_topic']."</h2>\n<p>".$l['err404_message']."</p>";
 $btnValue = htmlspecialchars($l['content_search']);
 $c['main'] .= '     <form id="searchform" action="'.JLOG_PATH.'/search.php" accept-charset="UTF-8">
      <p><input class="userdata" type="text" name="q" size="30" value="'.htmlspecialchars($get['url'] ?? '').'" />
         <button class="send" value="'.$btnValue.'">'.$btnValue.'</button></p>
     </form>';

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
