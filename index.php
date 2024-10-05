<?php

/**
 * Jlog
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * $HeadURL: http://jeenaparadies.net/svn/jlog/trunk/index.php $
 * $Rev: 1777 $
 * $Author: robertb $
 * $Date: 2009-01-04 18:22:36 +0100 (So, 04. Jan 2009) $
 */

 if(!file_exists(dirname( __FILE__ ).DIRECTORY_SEPARATOR.'personal'.DIRECTORY_SEPARATOR.'settings.inc.php')) {
    if(dirname($_SERVER['SCRIPT_NAME']) !== "/") $dir = dirname($_SERVER['SCRIPT_NAME']);
    require_once('scripts'.DIRECTORY_SEPARATOR.'proto.inc.php');
    header('Location: '.proto()."://{$_SERVER['HTTP_HOST']}$dir/setup.php");
    exit;
 }
 require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

 $c['meta']['robots']        = "noindex, follow";
 $c['meta']['description']   = htmlspecialchars(strip_tags(str_replace("\n", '', JLOG_DESCRIPTION)), ENT_QUOTES);
 $c['meta']['title']         = $l['index_topic'];
 $c['meta']['html_language'] = $l['html_language'];

 $c['main'] = "";
 $cc = array();
 $cc = count_comments();

 $max_blog = JLOG_MAX_BLOG_ORGINAL + JLOG_MAX_BLOG_BIG + JLOG_MAX_BLOG_SMALL;

// -- Inhalte holen

    $sql = "SELECT id, url, topic, date as mysql_date,
            UNIX_TIMESTAMP(date) AS date,
            DATE_FORMAT(date, '%Y-%m-%dT%T".substr(date("O"), 0, 3) . ":" . substr(date("O"), 3)."') AS metadate,
	    teaser, teaserpic, teaserpiconblog, keywords, content,
	    comments, allowpingback, section
              FROM ".JLOG_DB_CONTENT." WHERE section = 'weblog' ORDER BY mysql_date DESC LIMIT ".$max_blog.";";
    $blog = new Query($sql);
     if($blog->error()) {
        echo "<pre>\n";
        echo $blog->getError();
        echo "</pre>\n";
        die();
     }

$number_of = $blog->numRows();

// -- ganze Posts ausgeben
$i_orginal = 0;
while (++$i_orginal <= JLOG_MAX_BLOG_ORGINAL) {
 $cd = array();
 $cd = $blog->fetch();
 $c['meta']['date'] = $cd['metadate'];
 if(empty($cd)) break 1;
 $c['main'] .= do_entry($cd, $cc);
}

// -- Teaser ausgeben
$i = 0;
while (++$i <= JLOG_MAX_BLOG_BIG) {
 $cd = $blog->fetch();
 if(empty($c['meta']['date'])) $c['meta']['date'] = $cd['metadate'];
 if(empty($cd)) break 1;
 $c['main'] .= do_teaser($cd, $cc);
}

if((JLOG_MAX_BLOG_BIG > 0) AND ($number_of > (JLOG_MAX_BLOG_BIG + JLOG_MAX_BLOG_ORGINAL))) $c['main'] .= "\n  <hr />";

if($number_of > JLOG_MAX_BLOG_BIG + JLOG_MAX_BLOG_ORGINAL) $c['main'] .= "\n  <ul class='entries'>";

// -- Liste mit alten Beiträgen ausgeben
$linklist = false;
while ($cd = $blog->fetch()) {
 if(empty($c['meta']['date'])) $c['meta']['date'] = $cd['metadate'];
 ++$i;
 $linklist = true;

 $tmp_comments = "";
 if(isset($cc[$cd['id']]) AND $cc[$cd['id']] != 0) $tmp_comments = " <a title='".$l['content_comments_title']."' href='".blog($cd['date'], $cd['url'])."#comments'>(".$cc[$cd['id']].")</a>";

 $c['main'] .= "
   <li>".strftime(JLOG_DATE_SUBCURRENT, $cd['date'])." <a href='".blog($cd['date'], $cd['url'])."'>".htmlspecialchars($cd['topic'], ENT_QUOTES)."</a>".$tmp_comments."</li>";
 }

if($linklist) $c['main'] .= "\n  </ul>\n  <hr />";

// -- Link zum Archiv
 $c['main'] .= "
  <p class='archivelink'>".$l['content_archive']." <a href='".archive()."'>".$l['content_archivelink']."</a>.</p>";

// -- Daten in Template einfügen und ausgeben --
require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;

// eof
