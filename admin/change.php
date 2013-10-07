<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');
 
 $get = strip($_GET);
 $post = strip($_POST);
 
 $c['meta']['title'] = $l['admin']['change_headline'];
 $c['main'] = output_admin_menu();
 $c['main'] .= "<h2>".$l['admin']['change_headline']."</h2>";

if($get['action'] == "trash" AND $post['trash'] == $l['admin']['yes']) {
 $c['main'] .= "<p>".trash_blog($get['id'])."</p>";
 include_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php');
}
elseif ($get['action'] == "trash" AND empty($post['trash'])) {
 $c['main'] .= "
   <form method='post' id='entryform' action='".$_SERVER['SCRIPT_NAME']."?id=".$get['id']."&amp;action=trash' accept-charset='UTF-8'>
	 <p>".$l['admin']['rearly_delete']." <input class='send' type='submit' name='trash' value='".$l['admin']['yes']."' />
	  ".add_session_id_input_tag()."
	  <a href='".JLOG_PATH."/admin/'>".$l['admin']['no']."</a></p>
   </form>";
  $form_input = get_blog($get['id']);
  $c['main'] .= preview_output($form_input);
  $c['title'] = $l['admin']['delete_blogentry'];  
}

else {
	if(isset($get['id'])) $form_input = get_blog($get['id']);
	elseif (isset($_POST)) $form_input = $post;
	else $c['main'] .= $l['admin']['error_occurred'];
	
	if($post['form_submitted'] == $l['admin']['preview']) {
	 $c['main'] .= error_output(check_input($form_input));
	 $c['main'] .= preview_output($form_input);
	 $c['main'] .= form_output($form_input);
	}
	elseif($post['form_submitted'] == $l['admin']['publish']) {
		// Put data to database
		if(!check_input($form_input)) {
		 $c['main'] .= "<p>".update_blog($form_input)."</p>";
		 include_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php');
		}
		else {
		 // show preview and form
		 $c['main'] .= error_output(check_input($form_input));
	 	 $c['main'] .= form_output($form_input);
		}
	}
	else {
		// show form
	 	 $c['main'] .= form_output($form_input);
	}
}

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
