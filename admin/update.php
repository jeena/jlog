<?php 
### update.php  Jlog 1.0.2 => Jlog 1.1.0

  define("JLOG_ADMIN", true);
  define("JLOG_UPDATE", true);
  
  // load prepend.inc.php
  require_once('..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');

  include(JLOG_BASEPATH.'lang'.DIRECTORY_SEPARATOR.'lang.'.JLOG_LANGUAGE.'.inc.php');
  include(JLOG_BASEPATH.'lang'.DIRECTORY_SEPARATOR.'lang-admin.'.JLOG_LANGUAGE.'.inc.php');
  
  // Rendering
  $c['meta']['title'] = "Update";
  //$c['main'] = sprintf("<h2>Update von <var>%s</var> auf <var>%s</var></h2>", JLOG_INSTALLED_VERSION, JLOG_SOFTWARE_VERSION);

  require_once(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'JlogUpdater.php');
  $updater = new JlogUpdater();
  
  if ($updater->isUp2Date()) {
      $c['main'] = '<p>Das Update auf ' . JLOG_INSTALLED_VERSION . ' wurde bereits erfolgreich durchgeführt.</p>';
  }
  else if (!isset($_POST['update'])) {
      $c['main'] = $updater->prepareForm($l);
  }
  else {
      $c['main'] = $updater->performUpdate($l);
      
      // Ready :-)
      require(JLOG_BASEPATH."scripts".DIRECTORY_SEPARATOR."update.php");
  }

require(JLOG_BASEPATH.'scripts'.DIRECTORY_SEPARATOR.'do_template.php');
echo $body;
?>