<?php
$now_date = getdate();

$data['rss'] = "";
$data['rss_full'] = "";
$data['sub'] = "";

 if(JLOG_SUB_CURRENT < 15) $limit = "LIMIT 15";
 else $limit = "LIMIT ".JLOG_SUB_CURRENT;

    $sql = "SELECT id, url, topic, UNIX_TIMESTAMP(date) AS date,
    	      teaser, teaserpic, teaserpiconblog, keywords, content,
	      comments, allowpingback, section
              FROM ".JLOG_DB_CONTENT."
              WHERE section = 'weblog'
              ORDER BY date DESC
              ".$limit.";";
    
    $rss_sub = new Query($sql);
     if($rss_sub->error()) {
        echo "<pre>\n";
        echo $rss_sub->getError();
        echo "</pre>\n";
        die();
     }
if(defined('JLOG_ADMIN') AND !defined('JLOG_COMMENTS')) {
    $data['rss'] = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?" . ">
    <rss version=\"2.0\">
       <channel>
          <title>".htmlspecialchars(JLOG_WEBSITE)."</title>
          <link>".htmlspecialchars(JLOG_PATH)."</link>
          <description>".htmlspecialchars(JLOG_DESCRIPTION)."</description>
          <language>".$l['language']."</language>
          <lastBuildDate>".date('r')."</lastBuildDate>
          <docs>http://blogs.law.harvard.edu/tech/rss</docs>
          <generator>&lt;a href=&quot;".JLOG_SOFTWARE_URL."&quot;&gt;Jlog v".JLOG_SOFTWARE_VERSION."&lt;/a&gt;</generator>
          <managingEditor>".htmlspecialchars(JLOG_PUBLISHER)." ".htmlspecialchars(JLOG_EMAIL)."</managingEditor>
          <copyright>&amp;copy;".$now_date['year']." by ".htmlspecialchars(JLOG_PUBLISHER)."</copyright>\n\n";

    $data['rss_full'] = $data['rss'];
}
$data['sub'] = "<ul class='subcurrentlist'>\n";

 if(!isset($cc)) $cc = count_comments();

$sub = 0;
while ($row = $rss_sub->fetch()) {
++$sub;

 if($sub <= JLOG_SUB_CURRENT) {
    $tmp_comments = "";
    if(isset($cc[$row['id']]) AND $cc[$row['id']] != 0) $tmp_comments = " <a class='comments' title='".$l['content_comments_title']."' href='".blog($row['date'], $row['url'])."#comments'>(".$cc[$row['id']].")</a>";
    $data['sub'] .= "     <li>".strftime(JLOG_DATE_SUBCURRENT, $row['date'])." <a href='".blog($row['date'], $row['url'])."'>".htmlspecialchars($row['topic'], ENT_QUOTES)."</a>".$tmp_comments."</li>\n";
 }

 if($sub <= 15 AND defined('JLOG_ADMIN')) {

# Kopfdaten
     $data['rss'] .= "       <item>\n       <title>".htmlspecialchars($row['topic'], ENT_QUOTES)."</title>\n";
    $data['rss_full'] .= "       <item>\n        <title>".htmlspecialchars($row['topic'], ENT_QUOTES)."</title>\n";
    
     $data['rss'] .= "        <guid isPermaLink=\"true\">".blog($row['date'], $row['url'])."</guid>\n";
    $data['rss_full'] .= "        <guid isPermaLink=\"true\">".blog($row['date'], $row['url'])."</guid>\n";

     $data['rss'] .= "        <pubDate>".date('r', $row['date'])."</pubDate>\n";
     $data['rss_full'] .= "        <pubDate>".date('r', $row['date'])."</pubDate>\n";

     $data['rss'] .= "        <link>".blog($row['date'], $row['url'])."</link>\n";
    $data['rss_full'] .= "        <link>".blog($row['date'], $row['url'])."</link>\n";

     $data['rss'] .= "        <comments>".blog($row['date'], $row['url'])."#comments</comments>\n";
    $data['rss_full'] .= "        <comments>".blog($row['date'], $row['url'])."#comments</comments>\n";

     $data['rss'] .= $categories->output_rss($row['id']);
    $data['rss_full'] .= $categories->output_rss($row['id']);


# Inhaltsdaten
     $data['rss'] .= "        <description>\n".htmlspecialchars($bbcode->parse($row['teaser']))."\n        </description>\n";
    $data['rss_full'] .= "        <description>\n";

    if($row['teaserpiconblog'] == 1)  $data['rss_full'] .= htmlspecialchars("<img src='".JLOG_PATH."/personal/img/t_".$row['teaserpic']."' alt='' />");
    $data['rss_full'] .= htmlspecialchars($bbcode->parse($row['content']))."\n        </description>\n";

     $data['rss'] .= "       </item>\n\n";
    $data['rss_full'] .= "       </item>\n\n";
 }
}
if(defined('JLOG_ADMIN') AND !defined('JLOG_COMMENTS')) {
    $data['rss'] .= "</channel>\n</rss>";
    $data['rss_full'] .= "</channel>\n</rss>";
}
$data['sub'] .= "    </ul>";

if(defined('JLOG_ADMIN') AND !defined('JLOG_COMMENTS')) {
    $file['rss'] = JLOG_BASEPATH.'personal'.DIRECTORY_SEPARATOR.'rss.xml';
    $file['rss_full'] = JLOG_BASEPATH.'personal'.DIRECTORY_SEPARATOR.'rss-full.xml';
}

$file['sub'] = JLOG_BASEPATH.'personal'.DIRECTORY_SEPARATOR.'subcurrent.inc';

   ### Plugin Hook
   if (isset($plugins) and is_object($plugins)) {
       $data = $plugins->callHook('onUpdate', $data);
   }

$i = 0;
$errors = array();

    foreach($file AS $d => $filename) {
        if (is_writable($filename)) {
            if (!$handle = fopen($filename, 'w')) {
             $errors[] .= $l['admin']['can_not_open']." ($filename)";
             exit;
            }
            if (!fwrite($handle, $data[$d])) {
             $errors[] .= $l['admin']['can_not_write']." ($filename)";
             exit;
            }
            ++$i;
            fclose($handle);
        } else {
            $errors[] .= $l['admin']['no_wrtitenable']." ($filename)";
        }
    }


if(count($errors) > 0) {
 $c['main'] .= error_output($errors);
}

if($i == 4 AND defined('JLOG_ADMIN') AND !defined('JLOG_COMMENTS')) $c['main'] .= "<p>".$l['admin']['rss_ok']."</p>";
unset($i);
unset($sub);

?>
