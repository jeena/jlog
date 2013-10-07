<?php

 function com_form_output($com_form) {
 $com_form = array_htmlspecialchars($com_form);
 global $l, $plugins;
 if(!isset($com_form['content'])) $com_form['content'] = "";

  $output = "
   <form method='post' action='#entryform' id='entryform'>
    <fieldset><legend>".$l['comments_entryform']."</legend>
    <p class='xmp'>
     <span>".$l['comments_bbcode']."
      <a onclick=\"jlog_learnbb('".JLOG_PATH."'); return false;\" href='".JLOG_PATH."/learn_bb.php'>BBcode</a>?
     </span>
     <br id='bbcode' />
     <textarea rows='8' cols='30' name='content'>".$com_form['content']."</textarea>
    </p>
    <p>
     <input class='userdata' type='text' name='name' value='".$com_form['name']."'
      onfocus=\"if(this.value &amp;&amp; this.value=='".$l['comments_name']."')this.value=''\"
      onblur=\"if(this.value=='') this.value='".$l['comments_name']."'\" />
     <input class='userdata' type='text' name='city' value='".$com_form['city']."'
      onfocus=\"if(this.value &amp;&amp; this.value=='".$l['comments_city']."')this.value=''\"
      onblur=\"if(this.value=='') this.value='".$l['comments_city']."'\" /><br />
     <input class='userdata' type='text' name='email' value='".$com_form['email']."'
      onfocus=\"if(this.value &amp;&amp; this.value=='".$l['comments_email']."')this.value=''\"
      onblur=\"if(this.value=='') this.value='".$l['comments_email']."'\" />
     <input class='userdata' type='text' name='homepage' value='".$com_form['homepage']."' />
    </p>
    <p class='checkbox'>
     <input type='checkbox' id='mail_by_comment' name='mail_by_comment' ";
    if(isset($com_form['mail_by_comment']) AND $com_form['mail_by_comment'] == 1) $output .= "checked='checked'";
  $output .= " value='1' /> <label for='mail_by_comment'>".$l['comments_mail_by_comment']."</label>&nbsp;";
    if(defined('JLOG_ADMIN')) $output .= "\n     <input type='hidden' value='".$com_form['id']."' name='id' />\n";
    else {
        $output .= "   <input type='checkbox' id='cookie' name='cookie' ";
        if(isset($com_form['cookie']) AND $com_form['cookie'] == 1) $output .= "checked='checked'";
        $output .= " value='1' /> <label for='cookie'>".$l['comments_save_data']."</label>\n";
    }
  $output .= "     <input type='hidden' value='".$com_form['sid']."' name='sid' />
    </p>
    <p>
     <input class='send' type='submit' name='form_submitted' value='".$l['comments_preview']."' onclick=\"this.form.action = '#preview'\" />
     <input class='send' type='submit' name='form_submitted' value='".$l['comments_send']."' />";

  if(defined("JLOG_ADMIN")) $output .= add_session_id_input_tag();

  $output .= "
    </p>
    </fieldset>
   </form>\n
   ";

   ### Plugin Hook
   $output = $plugins->callHook('commentForm', $output, $com_form);

  return $output;
 }

 function com_javascript_variables() {
 global $l;
    return "
   <script type='text/javascript'>
    jlog_l_comments_show = '".$l['comments_show']."';
    jlog_l_comments_hide = '".$l['comments_hide']."';
    jlog_l_comments_bold = '".$l['comments_bold']."';
    jlog_l_comments_italic = '".$l['comments_italic']."';
    jlog_l_comments_quote = '".$l['comments_quote']."';
    jlog_l_comments_url = '".$l['comments_url']."';
    jlog_l_comments_plz_format_txt = '".$l['comments_plz_format_txt']."';
    jlog_l_comments_url_href = '".$l['comments_url_href']."';
    jlog_l_comments_url_node = '".$l['comments_url_node']."';
   </script>
   "; 
 }
 
 function com_check_errors($com_form) {
  global $l;
   if(empty($com_form['sid'])) $errors[] = $l['comments_no_sid'];
   if(isset($com_form['email']) AND $com_form['email'] != "" AND !preg_match("/^[^@]+@.+\.\D{2,6}$/", $com_form['email']) AND $com_form['email'] != $l['comments_email']) $errors[] = $l['comments_false_mail'];
   if(empty($com_form['content'])) $errors[] = $l['comments_notext'];
  if(isset($errors)) return $errors;
 }

 function com_clean_data($data) {
  global $l;
   if(empty($data['name']) OR $data['name'] == $l['comments_name']) $data['name'] = "";
   if(empty($data['city']) OR $data['city'] == $l['comments_city']) $data['city'] = "";
   if(empty($data['email']) OR $data['email'] == $l['comments_email']) $data['email'] = "";
   if(empty($data['homepage']) OR $data['homepage'] == $l['comments_homepage']) $data['homepage'] = "";

   if(empty($data['date'])) $data['date'] = time();

  return $data;
 }

 function set_cookie($data) {
  $userdaten = array( $data['name'],
                             $data['city'],
                             $data['email'],
                             $data['homepage'] );
  $cookielife = time() + 42 * 24 * 60 * 60;
  $path = parse_url(JLOG_PATH);
  if(!isset($path['path'])) $path['path'] = "";
  setcookie("jlog_userdata", urlencode(serialize($userdaten)), $cookielife, $path['path']."/");
 }

 function trash_cookie() {
  $cookielife = time() - 3600;
  setcookie("jlog_userdata", '', $cookielife, "/");
 }

 function new_sid() {
     list($usec, $sec) = explode(' ', microtime());
     mt_srand((float) $sec + ((float) $usec * 100000));
     return $_SERVER["REMOTE_ADDR"]."-".time()."-".mt_rand(1000,9999);
 }
 
 // Funcitons
 
 function do_comment($data, $nr) {
 global $l, $bbcomments, $plugins;

    $meta = array_htmlspecialchars($data);
    $comment = "
  <li id='c".$data['id']."'>
   <p class='meta'><a class='permalink' title='".$l['comments_permalink']."' href='#c".$data['id']."'>".$nr."</a> <cite>";
   if(!empty($meta['homepage'])) $comment .= "<a title='".$meta['homepage']."' href='".$meta['homepage']."'>";
   if(!empty($meta['name'])) $comment .= $meta['name'];
   else $comment .= $l['comments_anonym'];
   if(!empty($meta['homepage'])) $comment .= "</a>";
   $comment .= "</cite>";
   if(!empty($meta['city'])) $comment .= " ".$l['comments_from']." ".$meta['city'];
   $comment .= " ".$l['comments_posted']." ".strftime(JLOG_DATE_COMMENT, $data['date']).":</p>\n".$bbcomments->parse($data['content'])."</li>";

   ### Plugin Hook
	 $comment = $plugins->callHook('showComment', $comment, $data, $nr);

 return $comment;
 }
?>
