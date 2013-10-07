<?php
 include_once('.'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'url_syntax.php');
 require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'settings.class.php');
 require(JLOG_BASEPATH.'admin'.DIRECTORY_SEPARATOR.'blog.func.php');

 $c['meta']['title'] = $l['admin']['m_title'];
 $c['main'] = output_admin_menu()."<h2>".$l['admin']['m_title']."</h2>";

 $settings = new Settings($l);
 if($_POST) {
  $settings->importDataByArray(strip($_POST));
  if(count($errors = $settings->validate()) == 0) {
   if(count($errors = $settings->do_settings()) == 0) {
    $c['main'] .= $l['admin']['m_settings_ok'];
   }

  }
  if(count($errors) > 0) {
   $c['main'] .= error_output($errors);
   $c['main'] .= $settings->form_output();
  }
 }
 else {
  $settings->importDataByConstants();
  $c['main'] .= $settings->form_output();
 }
require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'update.php');
require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>
