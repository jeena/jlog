<?php

require_once JLOG_BASEPATH.'/scripts/stringparser_bbcode.class.php';

// Zeilenumbrüche verschiedener Betriebsysteme vereinheitlichen
function convertlinebreaks ($text) {
  return preg_replace ("/\015\012|\015|\012/", "\n", $text);
}

// Alles bis auf Neuezeile-Zeichen entfernen
function bbcode_stripcontents ($text) {
  return preg_replace ("/[^\n]/", '', $text);
}

// Sonderzeichen behandeln
function special_character($text) {
  return str_replace("&amp;#", "&#", $text);
}

function do_bbcode_url ($action, $attributes, $content, $params, $node_object) {
  // get URL by parameters
  $url = isset($attributes['default']) ? $attributes['default'] : $content;
  
  // validate URL
  if($action == 'validate') {
    // Due to Bug #146 we will only allow specific protocolls in the url
    // currently, these are: HTTP, FTP, News and Mailto - or relative URLs
    // starting with a slash
		if(preg_match('#^(http://|ftp://|news:|mailto:|/)#i', $url)) return true;	 
		// Some people just write www.example.org, skipping the http://
		// We're going to be gentle a prefix this link with the protocoll.
		// However, example.org (without www) will not be recognized
		elseif(substr($url, 0, 4) == 'www.') return true;
		// all other links will be ignored 	
		return true;
  }
  // generate link
  else {
    // prefix URL with http:// if the protocoll was skipped
    if(substr($url, 0, 4) == 'www.') {
      $url = 'http://' . $url;
    }
    // in case a relative url is given without a link text, we display
    // the full URI as link text, not just the relative path
    if(!isset($attributes['default']) AND substr($url, 0, 1) == '/') {
      $content = (isset($_SERVER['HTTPS']) ? 'https://' : 'http://') 
               . $_SERVER['HTTP_HOST'] 
               . $url;
    }
    // build link
    return '<a href="' . htmlspecialchars($url) . '">' . $content . '</a>';
  }
}

// Funktion zum Einbinden von Bildern
function do_bbcode_img ($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') {
        if (isset($attributes['caption'])) {
            $node_object->setFlag('paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
            if ($node_object->_parent->type() == STRINGPARSER_NODE_ROOT OR
                in_array($node_object->_parent->_codeInfo['content_type'], array('block', 'list', 'listitem'))) {
                return true;
            }
            else return false;
        }
        else return true;
    }
		$title = empty($attributes["title"]) ? "" : " title='".htmlspecialchars($attributes["title"])."'";

    if (isset($attributes['class']) AND isset($attributes['caption'])) $class_caption = " class='img ".htmlspecialchars($attributes['class'])."'";
    elseif (isset($attributes['class'])) $class = " class='".htmlspecialchars($attributes['class'])."'";
    elseif (isset($attributes['caption'])) $class_caption = " class='img'"; // bugfix by Sebastian Kochendörfer #215

    if (strpos($content, "http://") === 0) return "<img src='".htmlspecialchars($content)."'".$class." alt='".htmlspecialchars($attributes['alt'])."'".$title." />";
    else {
        list($img_width, $img_height, $img_type, $img_attr) = @getimagesize(JLOG_BASEPATH.'/img'.DIRECTORY_SEPARATOR.htmlspecialchars($content));
        $img = "<img src='".JLOG_PATH."/img/".htmlspecialchars($content)."'".$class." alt='".htmlspecialchars($attributes['alt'])."' style='width: ".$img_width."px;'".$title." />";
    }

     if(isset($attributes['caption'])) {
        return "\n<dl".$class_caption." style='width: ".$img_width."px;'>\n <dt>".$img."</dt>\n  <dd>".htmlspecialchars($attributes['caption'])."</dd>\n</dl>\n";
     }
     else return $img;
}

// Funktion zum Einbinden von HTML Code, welcher vom Browser interpretiert wird
function do_bbcode_html($action, $attributes, $content, $params, $node_object) {
    if ($action == 'validate') return true;
    return $content;
}

$bbcode = new StringParser_BBCode ();
$bbcode->addFilter (STRINGPARSER_FILTER_PRE, 'convertlinebreaks');
$bbcode->addFilter (STRINGPARSER_FILTER_POST, 'special_character');

$bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'htmlspecialchars');
$bbcode->addParser (array ('block', 'inline', 'link', 'listitem'), 'nl2br');
$bbcode->addParser ('list', 'bbcode_stripcontents');
$bbcode->setRootParagraphHandling (true);

$bbcode->addCode ('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());

$bbcode->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
                  'inline', array ('listitem', 'block', 'inline', 'link'), array ());

$bbcode->addCode ('headline', 'simple_replace', null, array('start_tag' => '<h3>', 'end_tag' => '</h3>'),
                        'block', array('block'), array('inline', 'link'));

$bbcode->addCode ('quote', 'simple_replace', null, array('start_tag' => '<blockquote>', 'end_tag' => '</blockquote>'),
                        'block', array('block', 'listitem'), array('inline', 'link'));

$bbcode->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'),
                  'link', array ('listitem', 'block', 'inline'), array ('link'));

$bbcode->addCode ('img', 'usecontent', 'do_bbcode_img', array (),
                  'image', array ('listitem', 'block', 'inline', 'link'), array ());

$bbcode->addCode ('html', 'usecontent', 'do_bbcode_html', array (),
                  'html', array ('listitem', 'block', 'inline', 'link'), array ('image'));

$bbcode->addCode ('list', 'simple_replace', null, array ('start_tag' => '<ul>', 'end_tag' => '</ul>'),
                  'list', array ('block', 'listitem'), array ());

$bbcode->addCode ('olist', 'simple_replace', null, array ('start_tag' => '<ol>', 'end_tag' => '</ol>'),
                  'list', array ('block', 'listitem'), array ());

$bbcode->addCode ('*', 'simple_replace', null, array ('start_tag' => '<li>', 'end_tag' => '</li>'),
                  'listitem', array ('list', 'olist' ), array ());

$bbcode->setCodeFlag ('*', 'closetag', BBCODE_CLOSETAG_OPTIONAL);
$bbcode->setCodeFlag ('*', 'paragraphs', false);
$bbcode->setCodeFlag ('list', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcode->setCodeFlag ('list', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('list', 'closetag.after.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('olist', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcode->setCodeFlag ('olist', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('olist', 'closetag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('headline', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcode->setCodeFlag ('headline', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('headline', 'closetag.after.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('html', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('html', 'closetag.after.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcode->setCodeFlag ('quote', 'paragraphs', true);
$bbcode->setCodeFlag ('quote', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcode->setCodeFlag ('quote', 'closetag.after.newline', BBCODE_NEWLINE_DROP);

// BBCode for comments
$bbcomments = new StringParser_BBCode ();
$bbcomments->addFilter (STRINGPARSER_FILTER_PRE, 'convertlinebreaks');
$bbcomments->addFilter (STRINGPARSER_FILTER_POST, 'special_character');

$bbcomments->addParser (array ('block', 'inline', 'link'), 'htmlspecialchars');
$bbcomments->addParser (array ('block', 'inline', 'link'), 'nl2br');
$bbcomments->setRootParagraphHandling (true);

$bbcomments->addCode ('b', 'simple_replace', null, array ('start_tag' => '<strong>', 'end_tag' => '</strong>'),
                  'inline', array ('block', 'inline', 'link'), array ());
$bbcomments->addCode ('i', 'simple_replace', null, array ('start_tag' => '<em>', 'end_tag' => '</em>'),
                  'inline', array ('block', 'inline', 'link'), array ());
$bbcomments->addCode ('url', 'usecontent?', 'do_bbcode_url', array ('usecontent_param' => 'default'),
                  'link', array ('block', 'inline'), array ('link'));
$bbcomments->addCode ('quote', 'simple_replace', null, array('start_tag' => '<blockquote>', 'end_tag' => '</blockquote>'),
                        'block', array('block'), array('inline', 'link'));
    
$bbcomments->setCodeFlag ('quote', 'paragraph_type', BBCODE_PARAGRAPH_BLOCK_ELEMENT);
$bbcomments->setCodeFlag ('quote', 'paragraphs', true);
$bbcomments->setCodeFlag ('quote', 'opentag.before.newline', BBCODE_NEWLINE_DROP);
$bbcomments->setCodeFlag ('quote', 'closetag.after.newline', BBCODE_NEWLINE_DROP);

// eof
