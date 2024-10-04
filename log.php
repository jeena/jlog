<?php


require_once('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

$get = strip($_GET);
$c['form_content'] = "";
$date = strftime(JLOG_DATE);

$sql_get = escape_for_mysql($get);

if(!empty($sql_get['y']) AND !empty($sql_get['m']) AND !empty($sql_get['url'])) {
    $sql = "SELECT 
                id, url, topic,
                UNIX_TIMESTAMP(date) AS date,
                DATE_FORMAT(date, '%Y-%m-%dT%T".substr(date("O"), 0, 3) . ":" . substr(date("O"), 3)."') AS metadate,
                teaser, teaserpic, teaserpiconblog, keywords,
                content, comments, allowpingback, section
            FROM ".JLOG_DB_CONTENT."
            WHERE 
                YEAR(date)      = '".$sql_get['y']."' AND
                MONTH(date)     = '".$sql_get['m']."' AND
                url                     = '".$sql_get['url']."' AND
                section         = 'weblog'
            LIMIT 1";

    $blog = new Query($sql);
    if($blog->error()) {
        echo "<pre>\n";
        echo $blog->getError();
        echo "</pre>\n";
        die();
    }

    if($blog->numRows() == 0) {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        include_once(JLOG_BASEPATH."error404.php");
        exit;
    }
}
else {
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    include_once(JLOG_BASEPATH."error404.php");
    exit;
}


$blogentry = $blog->fetch();

// get comments from Database

$sql_comments = "SELECT 
                    id, sid, name, city, email, homepage,
				    content, UNIX_TIMESTAMP(date) AS date,
				    reference, mail_by_comment, type
				FROM ".JLOG_DB_COMMENTS."
				WHERE reference = '".$blogentry['id']."'
				ORDER BY date;";

$c['meta']['date']        = $blogentry['metadate'];
$c['meta']['description'] = strip_tags($bbcode->parse($blogentry['teaser']));
$c['meta']['keywords']    = $blogentry['keywords'];
$c['meta']['title']       = $blogentry['topic'];
$c['meta']['pingback']    = true;

$c['main'] = do_entry($blogentry);

// Form entry

$com_form = strip($_POST);
if(!isset($com_form['type'])) $com_form['type'] = "";
$error = com_check_errors($com_form);

// Preview
if(isset($com_form['form_submitted']) AND $com_form['form_submitted'] === $l['comments_preview']) {

    $comments = new Query($sql_comments);
    if($comments->error()) {
        echo "<pre>\n";
        echo $comments->getError();
        echo "</pre>\n";
        die();
    }

    $commentsArray = array();
    $countComments = 0;
    while($commentsArray[] = $comments->fetch());
    
    foreach($commentsArray as $tmp_comment) {
    	if(!(isset($tmp_comment['type']) && $tmp_comment['type'] == 'pingback')) {
		++$countComments;
	}
    }

    $preview = "";
    if(isset($error)) $preview .= error_output($error);
    $clear_form = com_clean_data($com_form);
    $clear_form['id'] = "";
  
    ### Plugin Hook
    $clear_form = $plugins->callHook('previewComment', $clear_form, $blogentry);

    $preview .= "<ul class='comments' id='preview'>
                ".do_comment($clear_form, $countComments)."
                </ul>";

    $c['form_content'] .= $preview;
    $c['form_content'] .= com_form_output($com_form).com_javascript_variables();
}

// Send data to DB
elseif(isset($com_form['form_submitted']) AND $com_form['form_submitted'] == $l['comments_send'] AND $blogentry['comments'] == 1) {
    
    if(isset($error)) {

       $c['form_content'] .= error_output($error);
       $c['form_content'] .= com_form_output($com_form).com_javascript_variables();
    }
    else {
        // Send comment

        $com_form = com_clean_data($com_form);
        
        ### Plugin Hook
        $com_form = $plugins->callHook('newComment', $com_form, $blogentry);

	if (!isset($com_form['sid'])) {
		$c['form_content'] .= '<p class="error">Der Kommentar wurde nicht gespeichert.</p>';
	}
	else {
        $com = escape_for_mysql($com_form);
        if(!isset($com['mail_by_comment'])) $com['mail_by_comment'] = "";
        
        $sql = "INSERT INTO ".JLOG_DB_COMMENTS." (
                        sid,
                        name,
                        city,
                        email,
                        homepage,
                        content,
                        reference,
                        mail_by_comment,
                        date,
						type
                )
                VALUES (
                    '".$com['sid']."',
                    '".$com['name']."',
                    '".$com['city']."',
                    '".$com['email']."',
                    '".$com['homepage']."',
                    '".$com['content']."',
                    '".$blogentry['id']."',
                    '".$com['mail_by_comment']."',
                    NOW(),
        			'".$com['type']."'
                )"; 

        $newcomment = new Query($sql);
        $cid = $connect->insert_id;
        if($newcomment->error()) {
            if($newcomment->getErrno() == 1062) {
                $errors[] = $l['comments_duplicate'];
                $c['form_content'] .= error_output($errors, 'entryform').com_javascript_variables();
            }
            else {
                 echo "<pre>\n";
                 echo $newcomment->getError();
                 echo "</pre>\n";
                 die();
            }
        }
        else {
            if(isset($com_form['cookie']) AND $com_form['cookie'] == 1) set_cookie($com_form);
            else trash_cookie();
    
            include_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php');
    
            $sql = "SELECT DISTINCT email
                            FROM ".JLOG_DB_COMMENTS." WHERE reference = '".$blogentry['id']."' AND mail_by_comment = 1";
            $comment_mail = new Query($sql);
    
            // we are going to send some mail
            require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'mail.class.php');
    
            if($comment_mail->error()) {
                echo "<pre>\n";
                echo $comment_mail->getError();
                echo "</pre>\n";
                die();
            }
            elseif( JLOG_INFO_BY_COMMENT ) {
                // we need this for some mail texts
                require(JLOG_BASEPATH.'lang'.DIRECTORY_SEPARATOR.'lang-admin.'.JLOG_LANGUAGE.'.inc.php');
        
                $mail = new Jlog_Mail();
                $mail->setFrom($com_form['email'], $com_form['name']);
                $mail->setSubject($l['admin']['comments_mailsubject']." - ".$blogentry['topic']);
        
                $text = $l['admin']['comments_mail_txt']." »".$blogentry['topic']."«\n- -\n";
                if(!empty($com_form['name'])) $text .= $com_form['name'];
                else $text .= $l['admin']['comments_anonym'];
                if(!empty($com_form['city'])) $text .= " ".$l['comments_from']." ".$com_form['city'];
                $text .= " ".$l['admin']['comments_posted']." ".$date.":\n\n";
                $text .= html_entity_decode(strip_tags($bbcomments->parse($com_form['content'])));
                $text .= "\n\n".str_replace ( '&amp;', '&', blog($blogentry['date'], $blogentry['url']))."#c".$cid;
                $text .= "\n\n".$l['admin']['kill_c_email']."\n".JLOG_PATH."/admin/comments.php?action=trash&id=".$cid;
                $mail->setText($text);
                
                ### Plugin Hook
                $mail = $plugins->callHook('adminMail', $mail, $blogentry, $cid);
                $mail->send(JLOG_EMAIL);
            }
    
            $mail = new Jlog_Mail();
            $mail->setSubject($l['comments_mailsubject']." - ".$blogentry['topic']);
            $mail->setFrom(JLOG_EMAIL, JLOG_WEBSITE);
        
            $text = $l['comments_mail_txt']." »".$blogentry['topic']."«\n- -\n";
            if(!empty($com_form['name'])) $text .= $com_form['name'];
            else $text .= $l['comments_anonym'];
            if(!empty($com_form['city'])) $text .= " ".$l['comments_from']." ".$com_form['city'];
            $text .= " ".$l['comments_posted']." ".$date.":\n\n";
            $text .= html_entity_decode(strip_tags($bbcomments->parse($com_form['content'])));
            $text .= "\n\n".str_replace ( '&amp;', '&', blog($blogentry['date'], $blogentry['url']))."#c".$cid."";
            $text .= "\n-- \n".$l['comments_stop_receiving']."\n";
            $text .= JLOG_PATH."/stop.php?id=".$blogentry['id']."&email=";

            while ($data = $comment_mail->fetch()) {
                if($data['email'] != $com_form['email']) {
                    // set text for current user
                    $mail->setText($text . $data['email']);
                    $mail = $plugins->callHook('commentorMail', $mail, $blogentry);
                    // send mail
		    # XXX bugfix
                    $mail->send($data['email']);
                }
            }
            $c['form_content'] .= "<p id='entryform'>".$l['comments_thx']."</p>".com_javascript_variables();
        }
    }
    }
}

// If nothing happens
elseif($blogentry['comments'] == 1) {
    $com_form['name']             = $l['comments_name'];
    $com_form['city']             = $l['comments_city'];
    $com_form['email']            = $l['comments_email'];
    $com_form['homepage']         = $l['comments_homepage'];
    $com_form['sid']              = new_sid();
    if(isset($_COOKIE["jlog_userdata"])) {
        $cookie = unserialize(urldecode($_COOKIE["jlog_userdata"]));
        if($cookie != "")       $com_form['cookie']    = 1;
        if($cookie[0] != "") $com_form['name']                 = $cookie[0];
        if($cookie[1] != "") $com_form['city']                 = $cookie[1];
        if($cookie[2] != "") $com_form['email']                = $cookie[2];
        if($cookie[3] != "") $com_form['homepage']     = $cookie[3];
    }
    $c['form_content'] .= com_form_output($com_form).com_javascript_variables();
}
else $c['form_content'] .= "  <p id='entryform'>".$l['comments_closed']."</p>\n".com_javascript_variables();



// get comments and pingbacks

$comments = new Query($sql_comments);
if($comments->error()) {
    echo "<pre>\n";
    echo $comments->getError();
    echo "</pre>\n";
    die();
}
$countPingbacks = 0;
$countComments = 0;
$commentsArray = array();
$no_comments = "";

while($tmp_commentsArray = $comments->fetch()) $commentsArray[] = $tmp_commentsArray;
foreach($commentsArray as $tmp_comment) {
    if($tmp_comment['type'] == 'pingback') ++$countPingbacks;
    else ++$countComments;
}

if($countPingbacks > 0)  {
    if($countComments < 1) $no_comments = " class='entryform'";
    $c['main'] .= "\n <h3 id='pingbacks'".$no_comments.">".$l['pingback_topic']."</h3>\n  <ol id='pingbackslist'>";
    foreach($commentsArray as $pingback) {
        if($pingback['type'] == 'pingback') $c['main'] .= "\n   <li><a href='".$pingback['homepage']."'>".$pingback['name']."</a></li>";
    }
    $c['main'] .= "\n  </ol>\n";
}

if($countComments < 1) $no_comments = " class='entryform'";
$c['main'] .= "\n <h3 id='comments'".$no_comments.">".$l['comments_comment_topic']."</h3>\n";

if($countComments > 0) {
    $c['main'] .= "  <ul class='comments' id='commentslist'>";
    
    $i = 0;
    foreach($commentsArray as $data) {
        if($data['type'] !== 'pingback') {
            ++$i;
            $data = com_clean_data($data);
            $c['main'] .= do_comment($data, $i);
        }
    }
    
    $c['main'] .= "\n  </ul>\n";
}

$c['main'] .= $c['form_content'];

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;

?>
