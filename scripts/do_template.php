<?php
if(ereg('gzip', getenv('HTTP_ACCEPT_ENCODING')) &&
	!ini_get('zlib.output_compression'))
{
	ob_start('ob_gzhandler');
}
else ob_start(); 

$handle = fopen (JLOG_BASEPATH.'personal'.DIRECTORY_SEPARATOR.'template.tpl', "r");
$_body = "";
while (!feof($handle))  $_body .= fgets($handle);
fclose ($handle);

$handle = fopen (JLOG_BASEPATH.'personal'.DIRECTORY_SEPARATOR.'subcurrent.inc', "r");
$c['subnav_current'] = "";
while (!feof($handle))  $c['subnav_current'] .= fgets($handle);
fclose ($handle);

// Aditional Header Data

header("Content-Type: text/html; charset=UTF-8");
if(!isset($c['meta']['aditionalheader'])) $c['meta']['aditionalheader'] = "";
$c['meta']['aditionalheader'] .= ' <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />'."\n";
if(isset($c['meta']['robots'])) $c['meta']['aditionalheader'] .= '  <meta name="robots" content="'.$c['meta']['robots'].'" />'."\n";
if(isset($c['meta']['keywords'])) $c['meta']['aditionalheader'] .= '  <meta name="KeyWords" content="'.htmlspecialchars(strip_tags($c['meta']['keywords']), ENT_QUOTES).'" />'."\n";
if(isset($c['meta']['description'])) $c['meta']['aditionalheader'] .= '  <meta name="Description" content="'.htmlspecialchars(strip_tags(trim($c['meta']['description']), ENT_QUOTES)).'" />'."\n";
if(isset($c['meta']['date'])) $c['meta']['aditionalheader'] .= '  <meta name="date" content="'.$c['meta']['date'].'" />';
if(isset($c['meta']['pingback'])) {
        $c['meta']['aditionalheader'] .= "\n".'  <link rel="pingback" href="'.JLOG_PATH.'/xmlrpc.php" />';
        header("X-Pingback: ".JLOG_PATH."/xmlrpc.php");
}

$c['meta']['aditionalheader'] .= 
'  <meta http-equiv="Content-Style-Type" content="text/css" />
  <meta name="generator" content="Jlog v'.JLOG_SOFTWARE_VERSION.'" />
  <link rel="start" href="'.JLOG_PATH.'/" title="'.$l['meta_start'].'" />
  <link rel="search" href="'.JLOG_PATH.'/search.php" title="'.$l['meta_search'].'" />
  <link rel="alternate" type="application/rss+xml" title="RSS 2.0 - Summaries" href="'.JLOG_PATH.'/personal/rss.xml" />
  <link rel="alternate" type="application/rss+xml" title="RSS 2.0 - Full Posts" href="'.JLOG_PATH.'/personal/rss-full.xml" />
  <script type="text/javascript" src="'.JLOG_PATH.'/scripts/javascripts.js"></script>';

// do this on admincenter
if(defined('JLOG_ADMIN')) {
 // turn off cashing
 header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
 header("Last-Modified: " . gmdate("D, d M Y H:i:s") ." GMT");
 header("Cache-Control: no-cache");
 header("Pragma: no-cache");
 header("Cache-Control: post-check=0, pre-check=0", FALSE);
 // include admin.css
 $c['meta']['aditionalheader'] .= "\n   ".'<link rel="stylesheet" href="'.JLOG_PATH.'/personal/css/admin.css" type="text/css" />';
 $c['main'] = "<div id='admin'>".$c['main']."</div>";
}

$_search = array (
        "<jlog:language />",
        "<jlog:website />",
        "<jlog:title />",
        "<jlog:aditional-header />",
        "<jlog:homelink />",
        "<jlog:skipcontent />",
        "<jlog:home />",
        "<jlog:slogan-h />",
        "<jlog:slogan />",
        "<jlog:search-h />",
        "<jlog:searchstring />",
        "<jlog:search />",
        "<jlog:categorieslist-h />",
        "<jlog:current-h />",
        "<jlog:subcurrent />",
        "<jlog:archive-more />",
        "<jlog:archivelink />",
        "<jlog:archive />",
        "<jlog:sub-info />",
        "<jlog:rss-info />",
        "<jlog:rss-link />",
        "<jlog:copyright />",
        "<jlog:content />",
        "<jlog:powered />"
);

$_replace = array (
        $l['language'],
        htmlspecialchars(JLOG_WEBSITE, ENT_QUOTES),
        htmlspecialchars($c['meta']['title']),
   		$c['meta']['aditionalheader'],
        JLOG_PATH,
        $l['content_skip'],
        $l['meta_start'],
        $l['subnav_aboutpage'],
        JLOG_DESCRIPTION,
        $l['content_search_topic'],
        '', // bugfix
        $l['content_search'],
        $l['content_categorieslist_h'],
        $l['subnav_current'],
        $c['subnav_current'],
        $l['content_archive'],
        archive(),
        $l['content_archivelink'],
        $l['subnav_info'],
        $l['subnav_rss'],
        "<a href='".JLOG_PATH."/personal/rss-full.xml'><img  src='".JLOG_PATH."/img/JLOG_rss-full.png' alt='XML - Fullpost' /></a> <a href='".JLOG_PATH."/personal/rss.xml'><img  src='".JLOG_PATH."/img/JLOG_rss-summary.png' alt='XML - Summary' /></a>",
        "&copy;&nbsp;".date('Y')." ".JLOG_PUBLISHER.", ".$l['subnav_copyright'],
        $c['main'],
        $l['subnav_powered']." <a href='".JLOG_SOFTWARE_URL."' title='version ".JLOG_SOFTWARE_VERSION."'>Jlog</a>"
);

$body = str_replace($_search, $_replace, $_body);

$jlogTemplateTags = new JLOG_Tags($body);

    if(($categorieslist_tag = $jlogTemplateTags->getTag('categorieslist')) !== false) {
        if(strlen($categorieslist_class = $jlogTemplateTags->getAttributeValue('categorieslist', 'class'))>0) $categorieslist_class = ' class="'.$categorieslist_class.'"';
        if( $categorieslist = $categories->output_whole_list("\n ".'<ul'.$categorieslist_class.'>'."\n")) {
          $body = str_replace($categorieslist_tag, $categorieslist, $body );
        }
        else $body = str_replace($categorieslist_tag, '', $body );
    }

$body = $plugins->callHook('body', $body, $jlogTemplateTags);
