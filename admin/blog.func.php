<?php
// Untersuchen ob alles eingegeben wurde
function check_input($form_input) {
global $l;
    if(strlen(trim($form_input['topic'])) < 1) $errors[] = $l['admin']['no_headline'];

    // checking URL
    if(strlen(trim($form_input['url'])) < 1) $errors[] = $l['admin']['no_url'];
    elseif(!preg_match("/^[a-z0-9\-_\.\/,]+$/", $form_input['url'])) $errors[] = $l['admin']['false_url_letters'];
    else {
        $f = escape_for_mysql($form_input);
        if(empty($f['date'])) $f['date'] = strftime("%Y-%m-%d %H:%M:%S");

        list($date, $time) = explode(" ", $form_input["date"]);
        list($year, $month, $day) = explode("-", $date);
        list($hour, $minute, $second) = explode(":", $time);

        # TODO: (jeena) diese Abfrage scheint noch falsch zu sein
/*
        if(
            !checkdate((int)$month, (int)$day, (int)$year) OR
            $hour < 0 OR $hour > 23 OR
            $minute < 0 OR $minute > 59 OR
            $second < 0 OR $second > 59
        ) $errors[] = $l['admin']['false_date'];
*/   
        if($form_input['section'] == 'page') {
            $sql = "SELECT id FROM ".JLOG_DB_CONTENT." WHERE url = '".$f['url']."';";
        }
        else {
            $sql = "SELECT id FROM ".JLOG_DB_CONTENT." WHERE
                        YEAR(date)  = ".date("Y", $f['date'])." AND
                        MONTH(date) = ".date("m", $f['date'])." AND
                        url         = '".$f['url']."';";
        }
    
        $check_url = new Query($sql);

        if($check_url->error()) {
         echo "<pre>\n";
         echo $check_url->getError();
         echo "</pre>\n";
         die();
        }

        if($check_url->numRows() > 0) {
            $c = $check_url->fetch();
            if($c['id'] != $form_input['id'] AND $form_input['section'] != 'page') $errors[] = $l['admin']['url_duplicate'];
            elseif($c['id'] != $form_input['id'] AND $form_input['section'] == 'page') $errors[] = $l['admin']['url_duplicate_page'];
        }
    }
    
     if(strlen(trim($form_input['teaserpic']) > 0) AND !is_file(JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR."t_".$form_input['teaserpic'])) {
      	$errors[] = $l['admin']['false_teaserpic'];
      }

     if($form_input['teaserpiconblog'] == "1" AND strlen(trim($form_input['teaserpic'])) == 0) $errors[] = $l['admin']['no_teaserpic_uploaded'];
    
    if(strlen(trim($form_input['teaser'])) < 1) $errors[] = $l['admin']['no_teaser'];
    if(strlen(trim($form_input['content'])) < 1) $errors[] = $l['admin']['no_content'];
    
 return $errors;
}

// Eingabeformular
function form_output($form_input) {
 $form_input = array_htmlspecialchars($form_input);
global $l, $categories, $plugins;

     if($form_input['teaserpiconblog'] == 1) $form_input['teaserpiconblog_check'] = "checked='checked'";
     if($form_input['section'] == 'page') $page = " checked='checked'";
     else $weblog = " checked='checked'";
     if($form_input['allowcomments'] === '0') $form_input['comments_check'] = "checked='checked'";
     if($form_input['allowpingback'] === '0') $form_input['pingback_check'] = "checked='checked'";

 $o = "
   <form method='post' id='entryform' action='".$_SERVER['SCRIPT_NAME']."' accept-charset='UTF-8'>
    <fieldset><legend>".$l['admin']['metadata']."</legend>
     <p><label>".$l['admin']['section']."</label><br />
        <input id='weblog' name='section' type='radio' value='weblog'".$weblog." /><label for='weblog' class='nobreak'>".$l['admin']['section_weblog']."</label>&nbsp;
        <input id='page' name='section' type='radio' value='page'".$page." /><label for='page' class='nobreak'>".$l['admin']['section_page']."</label></p>
     <p><label for='topic'>".$l['admin']['headline']."</label><br />
        <input id='topic' name='topic' class='long' type='text' maxlength='255' size='60' value='".$form_input['topic']."' /></p>
     <p><label for='url'>".$l['admin']['url']."</label><br />
        <input id='url' name='url' class='long' type='text' maxlength='200' size='60' value='".$form_input['url']."' /></p>
     <p><label for='teaser'>".$l['admin']['teaser']."</label><br />
        <textarea id='teaser' name='teaser' class='small' rows='2' cols='60' >".$form_input['teaser']."</textarea></p>
     <p><label for='keywords'>".$l['admin']['keywords']."</label><br />
        <input id='keywords' name='keywords' class='long' type='text' maxlength='255' size='60' value='".$form_input['keywords']."' /></p>
".$categories->output_select($form_input['categories'])."
      <p id='jlogteaserpic' style='display: none;'><label for='teaserpic'>".$l['admin']['pic_for_teaser']."</label><br />
          <input id='teaserpic' name='teaserpic' class='short' type='text' size='6' value='".$form_input['teaserpic']."' />
          <input id='teaserpiconblog' name='teaserpiconblog' type='checkbox' value='1' ".$form_input['teaserpiconblog_check']." /> <label for='teaserpiconblog'>".$l['admin']['show_tpic_on_archive']."</label>
          <script type='text/javascript'>
           document.write(\"<br /><input type='button' name='teaserupload' value='".$l['admin']['pic_upload']."' onclick='jlog_wopen(\\\"".add_session_id_to_url("media/upload-teaser.php")."\\\");' /><input type='button' name='teaserchose' value='".$l['admin']['pic_choose']."' onclick='jlog_wopen(\\\"".add_session_id_to_url("media/select-teaser.php")."\\\");' />\");
          </script>
         </p>
     <p><input id='allowcomments' type='checkbox' name='allowcomments' value='0' ".$form_input['comments_check']." /><label for='allowcomments'>".$l['admin']['comments_closed']."</label><br />
        <input id='allowpingback' type='checkbox' name='allowpingback' value='0' ".$form_input['pingback_check']." /><label for='allowpingback'>".$l['admin']['pingback_closed']."</label></p>

    </fieldset>

    <fieldset><legend>".$l['admin']['contentdata']."</legend>
     <script type='text/javascript'>
      document.write(\"<br /><label for='content'>".$l['admin']['contentpic_choose']."</label><br /><input name='imgupload' type='button' value='".$l['admin']['pic_upload']."' onclick='jlog_wopen(\\\"".add_session_id_to_url("media/upload-picture.php")."\\\");' /><input name='imgselect' type='button' value='".$l['admin']['pic_choose']."' onclick='jlog_wopen(\\\"".add_session_id_to_url("media/select-picture.php")."\\\");' />\");
     </script>
    <p><label for='content'>".$l['admin']['content']." (".$l['admin']['howto_bbcode'].")</label><br /><br id='bbcode' />
       <textarea id='content' name='content' rows='15' cols='60' class='big'>".$form_input['content']."</textarea></p>

    <p><input class='send' type='submit' name='form_submitted' value='".$l['admin']['preview']."' />
       <input class='send' type='submit' name='form_submitted' value='".$l['admin']['publish']."' />
       <input type='hidden' name='id' value='".$form_input['id']."' />
       <input type='hidden' name='date' value='".$form_input['date']."' />
       ".add_session_id_input_tag()."
    </p>
    </fieldset>
   </form>
   <script type='text/javascript'>
    jlog_admin = true;
    jlog_l_comments_bold = '".$l['admin']['content_bold']."';
    jlog_l_comments_italic = '".$l['admin']['content_italic']."';
    jlog_l_comments_quote = '".$l['admin']['content_quote']."';
    jlog_l_comments_url = '".$l['admin']['content_url']."';
    jlog_l_comments_plz_format_txt = '".$l['admin']['content_plz_format_txt']."';
    jlog_l_comments_input_on_pos = '".$l['admin']['content_input_on_pos']."';
    jlog_l_comments_url_href = '".$l['admin']['content_url_href']."';
    jlog_l_comments_url_node = '".$l['admin']['content_url_node']."';
    jlog_l_list = '".$l['admin']['content_list']."';
    jlog_l_headline = '".$l['admin']['content_headline']."';
   </script>
 ";

   ### Plugin Hook
   $o = $plugins->callHook('adminForm', $o, $form_input);
  
 return $o;
}

function preview_output($form_input) {
global $l, $bbcode, $categories;

    // get data from _post
    if(empty($form_input['date'])) $form_input['date'] = time();
    $output =  "<h2 class='preview'>".$l['admin']['preview']."</h2>\n<div class='preview'>".do_entry($form_input, NULL, $section)."</div>";

 return $output;
}

function insert_blog($form_input) {
global $l, $plugins;

    if($form_input['allowcomments'] != "0") $form_input['allowcomments'] = "1";
    if($form_input['allowpingback'] != "0") $form_input['allowpingback'] = "1";

     $form_input = escape_for_mysql($form_input);
     $sql = "INSERT INTO ".JLOG_DB_CONTENT." (
                    topic,
                    url,
                    section,
                    date,
                    teaser,
                    teaserpic,
                    teaserpiconblog,
                    keywords,
                    content,
                    comments,
                    allowpingback   )
                VALUES (
                '".$form_input['topic']."',
                '".$form_input['url']."',
                '".$form_input['section']."',
                NOW(),
                '".$form_input['teaser']."',
                '".$form_input['teaserpic']."',
                '".$form_input['teaserpiconblog']."',
                '".$form_input['keywords']."',
                '".$form_input['content']."',
                '".$form_input['allowcomments']."',
                '".$form_input['allowpingback']."'  );";

    $writeblog = new Query($sql);
    $id = mysql_insert_id();
     if($writeblog->error()) {
        echo "<pre>\n";
        echo $writeblog->getError();
        echo "</pre>\n";
        die();
     }
     
    if(is_array($form_input['categories']) AND $form_input['categories']['0'] != 'no_categories') {
    $sql = "INSERT INTO ".JLOG_DB_CATASSIGN." ( cat_id, content_id )
                VALUES \n";
    foreach($form_input['categories'] AS $category) {
            if(++$i > 1) $sql .= ",\n";
            $sql .= "( '".$category."', '".$id."')";
    }
    $sql .= ";";
    
    $catassign = new Query($sql);
     if($catassign->error()) {
        echo "<pre>\n";
        echo $catassign->getError();
        echo "</pre>\n";
        die();
     }
    }

   ### Plugin Hook
   $plugins->callHook('insertEntry', $id, $form_input);
 return $id;
}

function get_blog($id) {
global $l, $categories;

    $sql = 'SELECT id, url, topic, UNIX_TIMESTAMP(date) AS date, ' .
    		'teaser, teaserpic, teaserpiconblog, keywords, ' .
		'content, comments, allowpingback, section FROM ' .
    		JLOG_DB_CONTENT . ' WHERE id = \'' . $id .
		'\' LIMIT 1;';

    $blog = new Query($sql);
     if($blog->error()) {
        echo "<pre>\n";
        echo $blog->getError();
        echo "</pre>\n";
        die();
     }
    $form_input = $blog->fetch();
    
   $form_input['categories'] = $categories->get_assigned_categories($form_input['id']);
     
 return $form_input;
}

function update_blog($form_input) {
global $l, $plugins;

    if($form_input['allowcomments'] != "0") $form_input['allowcomments'] = "1";
    if($form_input['allowpingback'] != "0") $form_input['allowpingback'] = "1";

     $form_input = escape_for_mysql($form_input);
     $sql = "UPDATE ".JLOG_DB_CONTENT." SET
                    topic              = '".$form_input['topic']."',
                    url                 = '".$form_input['url']."',
                    section             = '".$form_input['section']."',
                    teaser             = '".$form_input['teaser']."',
                    teaserpic           = '".$form_input['teaserpic']."',
                    teaserpiconblog = '".$form_input['teaserpiconblog']."',
                    keywords            = '".$form_input['keywords']."',
                    content             = '".$form_input['content']."',
                    comments          = '".$form_input['allowcomments']."',
                    allowpingback     = '".$form_input['allowpingback']."'
                WHERE id = '".$form_input['id']."' LIMIT 1;";


    $updateblog = new Query($sql);
     if($updateblog->error()) {
        echo "<pre>\n";
        echo $updateblog->getError();
        echo "</pre>\n";
        die();
     }
     
    if(is_array($form_input['categories'])) {
    $sql = "DELETE FROM ".JLOG_DB_CATASSIGN." WHERE content_id = '".$form_input['id']."';";
    $trashcatassign = new Query($sql);
     if($trashcatassign->error()) {
        echo "<pre>\n";
        echo $trashcatassign->getError();
        echo "</pre>\n";
        die();
     }
     
    if(is_array($form_input['categories']) AND $form_input['categories']['0'] != 'no_categories') {
        $sql = "INSERT INTO ".JLOG_DB_CATASSIGN." ( cat_id, content_id )
                    VALUES \n";
        foreach($form_input['categories'] AS $category) {
            if(++$i > 1) $sql .= ",\n";
            $sql .= "( '".$category."', '".$form_input['id']."')";
        }
        $sql .= ";";
        
        $catassign = new Query($sql);
         if($catassign->error()) {
            echo "<pre>\n";
            echo $catassign->getError();
            echo "</pre>\n";
            die();
         }
    }
    }

   ### Plugin Hook
   $plugins->callHook('updateEntry', $form_input['id'], $form_input);

 return $l['admin']['data_updated'];
}

function trash_blog($id) {
global $l;

    $sql = "DELETE FROM ".JLOG_DB_CONTENT." WHERE id = '".escape_for_mysql($id)."' LIMIT 1";
    
    $trashblog = new Query($sql);
   if($trashblog->error()) {
        echo "<pre>\n";
        echo $trashblog->getError();
        echo "</pre>\n";
        die();
   }
 return $l['admin']['postleted'];
}

/**
 * add PHPSESSID GET parameter if cookies are not allowed
 **/
function add_session_id_to_url($url="") {
	if(empty($_COOKIE[session_name()])) {
	    if(strpos($url, "?") === false)  $url .= "?";
	    else $url .= "&";
	    $url .= session_name() . "=" . htmlspecialchars(session_id());
	}
	return $url;
}

/**
 * add PHPSESSID <input>-Tag if cookies are not allowed
 */
function add_session_id_input_tag() {
	if(empty($_COOKIE[session_name()])) {
	    return "<input type='hidden' name='" . session_name() . "' value='" . htmlspecialchars(session_id()) . "' />";
	}
}

// output the administration menu
function output_admin_menu() {
global $l, $plugins;
        $o = '<p id="admin-menu">
 <a href="'.add_session_id_to_url("./").'">'.$l['admin']['menu_home'].'</a> |
 <a href="'.add_session_id_to_url("categories.php").'">'.$l['admin']['menu_categories'].'</a> |
 <a href="'.add_session_id_to_url("comments.php").'">'.$l['admin']['menu_comments'].'</a> |
 <a href="'.add_session_id_to_url("settings.php").'">'.$l['admin']['menu_settings'].'</a> |
 <a href="'.add_session_id_to_url("plugin.php").'">'.$l['admin']['menu_plugins'].'</a> |
 <a href="'.add_session_id_to_url("logout.php").'">'.$l['admin']['menu_logout'].'</a>
</p>';

	### Plugin Hook
	$o = $plugins->callHook('adminMenu', $o);
	
 return $o;
}


// eof
