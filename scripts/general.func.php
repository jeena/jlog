<?php
// get weblog link
function blog($date, $url, $section = 'weblog') {
        if($section == 'weblog' OR $section == 'comment') {
                $y = date("Y", $date);
                $m = date("m", $date);
                if(JLOG_CLEAN_URL === true) $permalink = JLOG_PATH."/".$y."/".$m."/".$url;
                else $permalink = JLOG_PATH."/log.php?y=".$y."&amp;m=".$m."&amp;url=".$url;
        }
        else {
                if(JLOG_CLEAN_URL === true) $permalink = JLOG_PATH."/".$url;
                else $permalink = JLOG_PATH."/page.php?url=".$url;
        }

   ### Plugin Hook
   global $plugins;
   $permalink = $plugins->callHook('permalink', $permalink, $date, $url, $section);

   return $permalink;
}

function archive() {
    if(JLOG_CLEAN_URL === true) return JLOG_PATH."/archive";
    else return JLOG_PATH."/archive.php";
}

// get year links
class Year_Links {

        function Year_Links($get, $start, $page, $l, $cat="") {
         $date  = getdate();
         $this->_now = $date['year'];
         $this->_start = $start;
         $this->_page = $page;
         $this->_l = $l;
         if(JLOG_CLEAN_URL === true) {
                if($cat != "") {
                        list($tmp, $cat) = explode("=", $cat);
                        $this->cat = "/cat/".$cat;
                 }
         }
         elseif($cat !== "") $this->cat = $cat."&amp;";
         
         if($get >= $this->_start OR $get <= $this->_now AND preg_match("[0-9]", $get)) $this->year = $get;
         else $this->year = $this->_now;
        }

        function get_linklist() {

         for($y = $this->_start; $y <= $this->_now; $y++) {
          if($y != $this->_start) $years_links .= " | ";
          if($y == $this->year) $years_links .= " <strong>".$y."</strong>";
          else {
           if(JLOG_CLEAN_URL === true) $years_links .= " <a href='".JLOG_PATH.$this->cat."/".$y."/'>".$y."</a>\n";
           else $years_links .= " <a href='".$this->_page.(strpos($this->_page, '?') === false ? "?" : "&amp;").$this->cat."y=".$y."'>".$y."</a>\n";
          }
         }
         
         return $this->_l['content_choose_year'].$years_links; 
        }
        
        function get_admin_linklist() {
        
         for($y = $this->_start; $y <= $this->_now; $y++) {
          if($y != $this->_start) $years_links .= " | ";
          if($y == $this->year) $years_links .= " <strong>".$y."</strong>";
          else $years_links .= " <a href='".$this->_page.(strpos($this->_page, '?') === false ? "?" : "&amp;")."y=".$y."'>".$y."</a>\n";
         }
         
         return $this->_l['content_choose_year'].$years_links; 

        }
        
// get selected year
        function get_selected_year() {
         return $this->year;
        }
}

// kill Magic Quotes
function strip($_data) {
  if (!get_magic_quotes_gpc()) return $_data;
  else {
        if (is_array($_data)) foreach($_data as $key => $val) $_data[$key] = strip($val);
        else $_data = stripslashes($_data);
        return $_data;
  }
}
// escape input for mysql
function escape_for_mysql($_data) {
        if (is_array($_data))
		foreach($_data as $key => $val) $_data[$key] = escape_for_mysql($val);
        else
		$_data = mysql_real_escape_string($_data);
		// uses last opened MySQL link implicitly
		// assumption is valid because this function is never called
		// before mysql_connect

        return $_data;
}
// htmlspecialchars a whole array
function array_htmlspecialchars($_data) {
        if (is_array($_data)) foreach($_data as $key => $val) $_data[$key] = array_htmlspecialchars($val);
        else $_data = htmlspecialchars($_data, ENT_QUOTES);
        return $_data;
}
// Fehler ausgeben
function error_output($errors, $id = "", $headline = false) {
global $l;
	$error = "";
	if($headline === false) $headline = $l["error"];
	if(isset($errors)) {
		if(strlen($headline) > 0) $error = "\n<h3 id='".$id."' class='error'>".$headline."</h3>";
		$error .= "\n <ul class='error'>\n";
		foreach($errors AS $f) $error .= "  <li>".$f."</li>\n";
		$error .= " </ul>\n";
   }
 return $error;
}

// Aus der Datenbank löschen (wird beim Kommentarlöschen gebraucht)

function trash($id, $table) {
        $sql = "DELETE FROM ".$table." WHERE id = '".$id."' LIMIT 1";
        
        $trash = new Query($sql);
   if($trash->error()) {
        echo "<pre>\n";
        echo $trash->getError();
        echo "</pre>\n";
        die();
   }
 return true;
}

// output a teaser
function do_teaser($data, $cc, $pre = '<h2>', $post = '</h2>') {
global $l, $bbcode, $categories, $plugins;

 if(empty($data['date_url'])) $data['date_url'] = $data['date']; # fix for search.php

 $output = "\n <div class='teaser'>\n";
  if($data['teaserpic'] != "") {
   list($img_width, $img_height, $img_type, $img_attr) = @getimagesize(JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR.'t_'.$data['teaserpic']);
   $output .= "   <a title='".$l['content_permalink']."' href='".blog($data['date_url'], $data['url'], $data['section'])."'><img class='teaserpic' src='".JLOG_PATH."/img/t_".$data['teaserpic']."' style='width: ".$img_width."px; height: ".$img_height."px;' alt='' /></a>\n";
  }
  $output .= "  ".$pre."<a title='".$l['content_permalink']."' href='".blog($data['date_url'], $data['url'], $data['section'])."'>".htmlspecialchars($data['topic'], ENT_QUOTES)."</a>".$post."
   <p class='date meta'>".$l['content_posted']." ".strftime(JLOG_DATE, $data['date']).$categories->output_assigned_links($data['id'])."</p>";
  $output .= $bbcode->parse($data['teaser']);

  $output .="   <p class='meta'><a title='".$l['content_more_title']."' href='".blog($data['date_url'], $data['url'], $data['section'])."'>".$l['content_more']."</a>";
  
  if($data['section'] == 'weblog') {
          if(isset($cc[$data['id']]) AND $cc[$data['id']] != 0) $tmp_comments = " <a title='".$l['content_comments_title']."' href='".blog($data['date'], $data['url'])."#comments'>".$l['content_comments']." (".$cc[$data['id']].")</a>";
          elseif($data['comments'] === '0') $tmp_comments = $l['comments_teaser_closed'];
          else $tmp_comments = " <a href='".blog($data['date'], $data['url'])."#comments'>".$l['content_comment_plz']."</a>";
          $output .= " | ".$tmp_comments;
  }
  $output .= "</p>\n </div>\n";

   ### Plugin Hook
   $output = $plugins->callHook('doTeaser', $output, $data, $cc, $pre, $post);

 return $output;
}

function do_entry($data, $cc = NULL, $section = 'weblog', $pre = '<h2>', $post = '</h2>') {
global $l, $bbcode, $categories, $plugins;

 $output = "
 <div class='mainitem'> 
  ".$pre."<a title='".$l['content_permalink']."' href='".blog($data['date'], $data['url'], $section)."'>".htmlspecialchars($data['topic'], ENT_QUOTES)."</a>".$post."\n";

  if($data['teaserpic'] != "" AND $data['teaserpiconblog'] == 1) {
   list($img_width, $img_height, $img_type, $img_attr) = @getimagesize(JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR.'t_'.$data['teaserpic']);
   $output .= "<a title='".$l['content_permalink']."' href='".blog($data['date'], $data['url'], $section)."'><img class='teaserpic' src='".JLOG_PATH."/img/t_".$data['teaserpic']."' style='width: ".$img_width."px; height: ".$img_height."px;' alt='' /></a>";
  }

  if($section == 'weblog' OR ($cat = $categories->output_assigned_links($data['id'])) != "") {
   $output .= " <p class='date meta'>";
   if($section == 'weblog') $output .= $l['content_posted']." ".strftime(JLOG_DATE, $data['date']);
   $output .= $categories->output_assigned_links($data['id'])."</p>";
  }

  $output .= $bbcode->parse($data['content']);
  $path_parts = pathinfo($_SERVER['SCRIPT_NAME']);

        if($data['section'] == 'weblog' AND $path_parts['basename'] != 'log.php') {
         if(isset($cc[$data['id']]) AND $cc[$data['id']] != 0) $tmp_comments = " <a title='".$l['content_comments_title']."' href='".blog($data['date'], $data['url'])."#comments'>".$l['content_comments']." (".$cc[$data['id']].")</a>";
         elseif($data['comments'] === '0') $tmp_comments = $l['comments_teaser_closed'];
         else $tmp_comments = "<a href='".blog($data['date'], $data['url'])."#comments'>".$l['content_comment_plz']."</a>";
    $output .="  <p class='meta'>".$tmp_comments."</p>";
        }

  if($section == 'weblog') $output .= ' <hr />';
  $output .= " </div>\n";

   ### Plugin Hook
   $output = $plugins->callHook('doEntry', $output, $data, $cc, $section);

 return $output;
}

function count_comments() {
        // -- Kommentare zählen
         $sql = "SELECT reference, COUNT(*) as count FROM ".JLOG_DB_COMMENTS." WHERE type <> 'pingback' GROUP BY reference";
         $comments = new Query($sql);
             if($comments->error()) {
                echo "<pre>\n";
                echo $comments->getError();
             echo "</pre>\n";
             die();
          }
        // -- Anzahl der jeweiligen Kommentare
         $com = array();
         while($c = $comments->fetch()) $com[$c['reference']] = $c['count'];
				 
				 ### Plugin Hook
				 global $plugins;
			   $com = $plugins->callHook('countComments', $com);
			
         return $com;
}

if (!function_exists('is_a')) {
    function is_a($object, $class)
    {
        if (!is_object($object)) {
            return false;
        }

        if (get_class($object) == strtolower($class)) {
            return true;
        } else {
            return is_subclass_of($object, $class);
        }
    }
}

if (!function_exists("stripos")) {
  function stripos($str,$needle,$offset=0) {
      return strpos( strtolower($str), strtolower($needle), $offset );
  }
}

if(!function_exists('str_ireplace')){
    function str_ireplace($search, $replace, $subject){
        if(is_array($search)){
            array_walk($search, create_function('&$pat, $key', '"/".preg_quote($pat, "/")."/i"'));
        }
        else{
            $search = '/'.preg_quote($search, '/').'/i';
        }
        return preg_replace($search, $replace, $subject);
    }
}

if ( !function_exists('file_put_contents') && !defined('FILE_APPEND') ) {
	define('FILE_APPEND', 1);
	function file_put_contents($n, $d, $flag = false) {
	    $mode = ($flag == FILE_APPEND || strtoupper($flag) == 'FILE_APPEND') ? 'a' : 'w';
	    $f = @fopen($n, $mode);
	    if ($f === false) {
	        return 0;
	    } else {
	        if (is_array($d)) $d = implode($d);
	        $bytes_written = fwrite($f, $d);
	        fclose($f);
	        return $bytes_written;
	    }
	}
}


function my_serialize_cfg($arg) {   
    if(is_string($arg)) return "'".preg_replace("/'/","\\'",$arg)."'";
    elseif(is_integer($arg)) return (string)$arg;
    elseif(is_float($arg)) return (string)$arg;
    elseif(is_null($arg)) return 'NULL';
    elseif(is_bool($arg)) {
        if($arg) return 'true';
        else return 'false';
    }
    elseif(is_array($arg)) {
        $retval = 'Array(';
        foreach($arg as $key => $value) {
            $retval .= my_serialize_cfg($key).' => '.my_serialize_cfg($value).',';
        }
        $retval .= ')';
        return $retval;
    }
    else die("unsupported type! ".gettype($arg));
}

class JLOG_Tags {
  var $tree = array();

  function JLOG_Tags($body) {
      preg_match_all('/<jlog:([a-z]\w+)\s?([^>]*)\/?>(<\/(\1):(\2)>)?/ims', $body, $this->tree);
  }

      function getTag($tagname) {
          if(($tagnr = array_search($tagname, $this->tree[1])) !== false) return $this->tree[0][$tagnr];
          else return false;
      }

      function getAttributeValue($tagname, $attribute) {
          $pattern = '/(?:^|\s)([a-z]\w+)(?:=)(?:(?:\'([^\']+)\')|(?:"([^"]*)")|([^\s,]+))/i';
          if(($tagnr = array_search($tagname, $this->tree[1])) !== false) {
              preg_match_all($pattern, $this->tree[2][ $tagnr ], $matches, PREG_SET_ORDER);
              $a = count($matches);
              for($i=0;$i<$a;$i++) {
                  if($matches[$i][1] == $attribute) return $matches[$i][3];
              }
          }
          else return;
      }
}

// security functions
function hashPassword($pw) {
	// TODO: see iusses/2 for details
	return md5($pw);
}
