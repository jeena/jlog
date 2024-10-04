<?php 
include_once('.'.DIRECTORY_SEPARATOR.'auth.php'); 
define("JLOG_ADMIN", true); 
define("JLOG_COMMENTS", true); 
require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

$get = strip($_GET);
$data = strip($_POST);
$mysql_id = isset($get['id']) ? escape_for_mysql($get['id']) : null;

$c['meta']['title'] = $l['admin']['kill_c_topic'];
$c['main'] = output_admin_menu();

array_contains($get, array('action'));

if($get['action'] == 'trash' AND $data['trash'] == $l['admin']['yes']) {

    ### Plugin Hook
    $get['id'] = $plugins->callHook('deleteComment', $get['id']); 

    if( trash($get['id'], JLOG_DB_COMMENTS ) == true) { 
      $c['main'] .= $l['admin']['kill_c_killed']; 
      include_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php'); 
    }
}
elseif ($get['action'] == 'trash' AND empty($data['trash'])) { 
$c['main'] .= " 
   <form method='post' id='entryform' action='comments.php?id=".$get['id']."&amp;action=trash' accept-charset='UTF-8'> 
     <p>".$l['admin']['kill_c_sure']." 
        <input class='send' type='submit' name='trash' value='".$l['admin']['yes']."' />
        ".add_session_id_input_tag()."
        <a href='".add_session_id_to_url("comments.php")."'>".$l['admin']['no']."</a></p> 
   </form>"; 
    
    $sql = 'SELECT id, sid, name, city, email, homepage, content, ' .
    	      'date, reference, mail_by_comment, type FROM ' .
	      JLOG_DB_COMMENTS." 
              WHERE id = ".$mysql_id." 
              ORDER BY id DESC LIMIT 1;"; 

    $comment = new Query($sql); 
     if($comment->error()) { 
        echo "<pre>\n"; 
        echo $comment->getError(); 
        echo "</pre>\n"; 
        die(); 
     }
      
   $daten = $comment->fetch(); 
  $c['main'] .= "<ul class='comments'>".do_comment($daten, "x")."</ul>"; 

}
elseif($get['action'] == 'change' AND !empty($get['id'])) { 

  $l["comments_comment_topic"]   = $l['admin']["comments_comment_topic"]; 
  $l["comments_by"]              = $l['admin']["comments_by"]; 
  $l["comments_name"]            = $l['admin']["comments_name"]; 
  $l["comments_city"]            = $l['admin']["comments_city"]; 
  $l["comments_email"]           = $l['admin']["comments_email"]; 
  $l["comments_homepage"]        = $l['admin']["comments_homepage"]; 
  $l["comments_bbcode"]          = $l['admin']["comments_bbcode"]; 
  $l["comments_send"]            = $l['admin']["comments_send"]; 
  $l["comments_preview"]         = $l['admin']["comments_preview"]; 
  $l["comments_no_sid"]          = $l['admin']["comments_no_sid"]; 
  $l["comments_false_mail"]      = $l['admin']["comments_false_mail"]; 
  $l["comments_notext"]          = $l['admin']["comments_notext"]; 
  $l["comments_false_hp"]        = $l['admin']["comments_false_hp"]; 
  $l["comments_anonym"]          = $l['admin']["comments_anonym"]; 
  $l["comments_permalink"]       = $l['admin']["comments_permalink"]; 
  $l["comments_from"]            = $l['admin']["comments_from"]; 
  $l["comments_posted"]          = $l['admin']["comments_posted"]; 
  $l["comments_entryform"]       = $l['admin']["comments_entryform"]; 
  $l["comments_mail_by_comment"] = $l['admin']["comments_mail_by_comment"]; 
  $l["comments_thx"]             = $l['admin']["comments_thx"]; 
  $l["comments_preview"]         = $l['admin']["comments_preview"]; 
  $l["comments_send"]            = $l['admin']["comments_send"]; 
  $l["comments_bold"]            = $l['admin']["comments_bold"]; 
  $l["comments_italic"]          = $l['admin']["comments_italic"]; 
  $l["comments_quote"]           = $l['admin']["comments_quote"]; 
  $l["comments_url"]             = $l['admin']["comments_url"]; 
  $l["comments_plz_format_txt"]  = $l['admin']["comments_plz_format_txt"]; 
  $l["comments_url_href"]        = $l['admin']["comments_url_href"]; 
  $l["comments_url_node"]        = $l['admin']["comments_url_node"]; 

	$form_submitted = false;

	if (isset($data['form_submitted'])) {
	    if($data['form_submitted'] == $l['comments_preview']) {
		$c['main'] .= "\n  <h2>".$l['admin']['comments_change_h']."</h2>
		    <ul class='comments' id='preview'>
		    ".do_comment($data, 1)."
		    </ul>".com_form_output($data).com_javascript_variables();
		$form_submitted = true;
	    }
	    elseif($data['form_submitted'] == $l['comments_send']) {
		if(count($errors = com_check_errors($data)) > 0) $c['main'] .= "\n  <h2>".$l['admin']['comments_change_h']."</h2>\n".error_output($error).com_form_output($data).com_javascript_variables();
		else {

		    $data = com_clean_data($data);

	### Plugin Hook
		    $data = $plugins->callHook('updateComment', $data);

		    $data = escape_for_mysql($data);

		    $sql = "UPDATE ".JLOG_DB_COMMENTS."
			SET
			name                    = '".$data['name']."',
			city                    = '".$data['city']."',
			email                   = '".$data['email']."',
			homepage                = '".$data['homepage']."',
			content                 = '".$data['content']."',
			mail_by_comment         = '".$data['mail_by_comment']."' 
		    WHERE id = '".$data['id']."' LIMIT 1;";

		   $updatecomment = new Query($sql);
		    if($updatecomment->error()) {
		       echo "<pre>\n";
		       echo $updatecomment->getError();
		       echo "</pre>\n";
		       die();
		    }
		    $c['main'] .= "\n  <h2>".$l['admin']['comments_change_h']."</h2>\n".$l['admin']['comments_updated']." <a href='".add_session_id_to_url("comments.php")."'>".$l['admin']['comments_admin']."</a>";
		    include_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php');
		}

		$form_submitted = true;
	    }
	}

	if (!$form_submitted) {
        $sql = 'SELECT id, sid, name, city, email, homepage, content, ' .
		'UNIX_TIMESTAMP(date) AS date, reference, ' .
		'mail_by_comment, type FROM '.JLOG_DB_COMMENTS."
              WHERE id = ".$mysql_id."
              ORDER BY id DESC LIMIT 1;";

        $comment = new Query($sql);
        if($comment->error()) {
            echo "<pre>\n";
            echo $comment->getError();
            echo "</pre>\n";
            die();
        }
 
        $data = $comment->fetch();
        if(empty($data['name'])) $data['name'] = $l['comments_name'];
        if(empty($data['city'])) $data['city'] = $l['comments_city'];
        if(empty($data['email'])) $data['email'] = $l['comments_email'];
        if(empty($data['homepage'])) $data['homepage'] = $l['comments_homepage'];

        $c['main'] .= "\n  <h2>".$l['admin']['comments_change_h']."</h2>
            <ul class='comments' id='preview'>
            ".do_comment($data, 1)."
            </ul>".com_form_output($data).com_javascript_variables();
    }
}
else {
	$yl = new Year_Links($get['y'] ?? null, JLOG_START_YEAR,
		add_session_id_to_url(JLOG_PATH."/admin/comments.php"),
		$l['admin']);

  $c['main'] .= "
<h2>".$l['admin']['kill_c_topic']."</h2>
<p>".$l['admin']['kill_c_description']."</p>
<p>".$yl->get_admin_linklist()."</p>
  <table>
   <tr>
    <th>".$l['admin']['change']."</th><th>".$l['admin']['delete']."</th><th>ID</th><th>".$l['comments_name']."</th><th>".$l['comments_posted']."</th><th>".$l['admin']['kill_c_entry']."</th>
   </tr>";

    $sql = "SELECT
                    ".JLOG_DB_COMMENTS.".id AS id,
                    ".JLOG_DB_CONTENT.".url AS url,
                    UNIX_TIMESTAMP(".JLOG_DB_CONTENT.".date) AS reference_date,
                    UNIX_TIMESTAMP(".JLOG_DB_COMMENTS.".date) AS date,
                    ".JLOG_DB_COMMENTS.".name AS name,
                    ".JLOG_DB_CONTENT.".topic AS topic,
                    ".JLOG_DB_COMMENTS.".email AS email,
 										".JLOG_DB_COMMENTS.".type AS type
              FROM ".JLOG_DB_COMMENTS.", ".JLOG_DB_CONTENT."
              WHERE ".JLOG_DB_COMMENTS.".reference = ".JLOG_DB_CONTENT.".id
              AND YEAR(".JLOG_DB_COMMENTS.".date) = '".$yl->get_selected_year()."'
              ORDER BY id DESC;";

    $comments = new Query($sql);
     if($comments->error()) {
        echo "<pre>\n";
        echo $comments->getError();
        echo "</pre>\n";
        die();
     }

    while ($daten = $comments->fetch()) {

      if(empty($daten['name'])) $daten['name'] = $l['comments_anonym'];
 			elseif($daten['type'] != 'pingback') $daten['name'] = htmlspecialchars($daten['name'], ENT_QUOTES);

      if(!empty($daten['email'])) {
        $email_a = "<a href='mailto:".$daten['email']."'>";
        $email_b = "</a>";
      }
      else {
        $email_a = "";
        $email_b = "";
      }
       $comment = "
      <tr> 
       <td><a href='".add_session_id_to_url("?id=".$daten['id']."&amp;action=change")."'><img src='".JLOG_PATH."/img/JLOG_edit.png' alt='".$l['admin']['change']."' /></a></td>
       <td><a href='".add_session_id_to_url("?id=".$daten['id']."&amp;action=trash")."'><img src='".JLOG_PATH."/img/JLOG_trash.png' alt='".$l['admin']['delete']."' /></a></td>
       <td><a href='".blog($daten['reference_date'], $daten['url'])."#c".$daten['id']."'>".$daten['id']."</a></td>
       <td>".$email_a.$daten['name'].$email_b."</td>
       <td>".strftime(JLOG_DATE_COMMENT, $daten['date'])."</td>
       <td>".$daten['topic']."</td>
      </tr>";

	 		### Plugin Hook
	    $c['main'] .= $plugins->callHook('commentAdminList', $comment, $daten);

    }
     
    $c['main'] .= "
    </table>";
}

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
