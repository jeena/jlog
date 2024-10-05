<?php
/* -- setup.php for Jlog
   -- Please delete this file if you have done the setup
*/

 if(file_exists(dirname( __FILE__ ).DIRECTORY_SEPARATOR.'personal'.DIRECTORY_SEPARATOR.'settings.inc.php')) {
     die("The setup has already been sucessfull!");
 }

 // derzeit gibt es noch etliche E_NOTICE-Meldungen in JLog, deshalb:
 error_reporting(E_ALL ^ E_NOTICE);
 header("Content-Type: text/html; charset=UTF-8");

 // defining to avoid notifications
 define("JLOG_NEW_VERSION", '1.1.0');
 define("JLOG_SETUP", true);
 define("JLOG_ADMIN", false);
 $basepath = dirname( __FILE__ ).DIRECTORY_SEPARATOR;

 require_once('scripts'.DIRECTORY_SEPARATOR.'proto.inc.php');

 define("JLOG_WEBSITE", $_SERVER["HTTP_HOST"]);
 define("JLOG_PATH", dirname(proto()."://{$_SERVER["HTTP_HOST"]}{$_SERVER["SCRIPT_NAME"]}"));

 // read prefered language from browser
 $dir = opendir('.'.DIRECTORY_SEPARATOR.'lang');
 $languages = array();
 while(($file = readdir($dir)) !== false) {
  if($file == '.' OR $file == '..') continue;
  if(!preg_match('/lang\.([a-zA-z0-9]+)\.inc\.php/', $file, $matches)) continue;
  $languages[] = $matches[1];
 }
 if(!empty($_GET['lang'])) {
     $lang = $_GET['lang'];
 } else {
     $lang = getlang($languages, 'de');     
 }
 define('JLOG_LANGUAGE', $lang);
 
 // load required scripts and libraries
 require('.'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'lang.'.$lang.'.inc.php');
 require('.'.DIRECTORY_SEPARATOR.'lang'.DIRECTORY_SEPARATOR.'lang-admin.'.$lang.'.inc.php');
 require('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'database.class.php');
 require('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'general.func.php'); 
 require('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'settings.class.php');
 require('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'url_syntax.php');
 require('.'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'version.inc.php');
 
 define("JLOG_NEW_VERSION", JLOG_SOFTWARE_VERSION);	// causes warning because of redefinition
 define("JLOG_PHPV", JLOG_SOFTWARE_PHPV);
 define("JLOG_MYSQLV", JLOG_SOFTWARE_MYSQLV);
 
 $errors = array();
 
 $l['admin']['submit'] = $l['admin']['s_install'];
 $setup = new Settings($l);

 $c = '';	// content to display

 if($_POST) {
  $setup->importDataByArray(strip($_POST));

  // validate user entry
  if(count($errors = $setup->validate()) == 0) {
   define("JLOG_BASEPATH", $setup->getValue('jlog_basepath'));
   if(is_writable(JLOG_BASEPATH.'personal'.DIRECTORY_SEPARATOR)) {
     $c .= "<ul>\n";
     
     // build some MySQL tables
     if(count($errors = create_mysql_tables($setup->getValue(false))) == 0) {
      $c .= "<li>".$l['admin']['s_tables_ok']."</li>\n";

      // create and chmod on some directories and files
      if(count($errors = do_personal()) == 0) {
       $c .= "<li>".$l['admin']['s_personal_ok']."</li>\n";

       // build settings.inc.php
       if(count($errors = $setup->do_settings()) == 0) $c .= "<li>".$l['admin']['master_ok']."</li>\n";
      }
      $c .= "</ul>";
     }
   }
   else {
    $errors[] = $l['admin']['s_personal_not_wrtbl'];
   }
  }
  if(count($errors) > 0) {
   $c .= error_output($errors);
   $c .= $setup->form_output();
  }
  else $c .= "<h2>".$l['admin']['s_ready_head']."</h2>"."<p style='text-align: left;'>".$l['admin']['s_ready']."</p>";
 }
 else {
  // validate PHP and MySQL versions
  if(!version_compare(phpversion(), JLOG_PHPV, ">=") == 1) $errors[] = $l['admin']['s_phpv_tolow'];
  if(!is_writable($basepath.'personal'.DIRECTORY_SEPARATOR)) $errors[] = $l['admin']['s_personal_not_wrtbl'];
  if(!is_writable($basepath.'img'.DIRECTORY_SEPARATOR)) $errors[] = $l['admin']['s_img_not_wrtbl'];

  if(empty($errors)) {
   // output form
   $setup->importSuggestedData();
   $c .= $setup->form_output();
  }
  else $c .= error_output($errors);
 }
 
 echo do_htmlpage($c);
 




#### some needed functions for the setup ####

 function create_mysql_tables($data) {
  # returns false if all tables were created, if not returns the $errors array

  $sql['content'] = '
    CREATE TABLE `'.$data['jlog_db_prefix'].'content` (
      id int(11) auto_increment,
      url varchar(200),
      topic varchar(255),
      date datetime,
      teaser mediumtext,
      teaserpic varchar(10),
      teaserpiconblog tinyint(1),
      keywords varchar(255),
      content longtext,
      comments tinyint(1) default \'1\',
      allowpingback tinyint(1) default \'1\',
      section varchar(10) default \'weblog\',
      UNIQUE KEY id (id),
      FULLTEXT KEY content_index (content, topic, teaser, keywords)
    ) TYPE=MyISAM CHARACTER SET utf8;';

  $sql['comments'] = '
   CREATE TABLE `'.$data["jlog_db_prefix"].'comments` (
     id int(11) auto_increment,
     sid varchar(35),
     name varchar(255),
     city varchar(255),
     email varchar(255),
     homepage varchar(255),
     content mediumtext,
     date datetime,
     reference int(11),
     mail_by_comment tinyint(1),
     type varchar(30) default \'\',
     PRIMARY KEY (id),
     UNIQUE KEY sid (sid),
     FULLTEXT KEY comments_index ( name, city, email, homepage, content )
   ) TYPE=MyISAM CHARACTER SET utf8;';
   
  $sql['categories'] = '
   CREATE TABLE `'.$data["jlog_db_prefix"].'categories` (
     id tinyint(4) auto_increment,
     name tinytext,
     url varchar(100),
     description text,
     UNIQUE KEY id (id),
     UNIQUE KEY url (url)
   ) TYPE=MyISAM CHARACTER SET utf8;';
   
  $sql['catassign'] = '
   CREATE TABLE `'.$data["jlog_db_prefix"].'catassign` (
     content_id int(11),
     cat_id tinyint(4)
   ) TYPE=MyISAM CHARACTER SET utf8;';

  $sql['attributes'] = '
   CREATE TABLE `'.$data["jlog_db_prefix"].'attributes` (
     id int(10) unsigned NOT NULL auto_increment,
     entry_id int(10) unsigned NOT NULL default \'0\',
     name varchar(120) NOT NULL default \'\',
     value varchar(250) NOT NULL default \'\',
     PRIMARY KEY (id),
     KEY entry_id (entry_id)
   ) TYPE=MyISAM CHARACTER SET utf8;';

    global $l;

    $connect = new mysqli($data['jlog_db_url'], $data['jlog_db_user'], $data['jlog_db_pwd'], $data['jlog_db']);
    if($connect === false) {
    	$errors[] = "Falsche Zugangsdaten";	// mysqli_error is not available (?)
    }
    # FIXME
    elseif(!version_compare($connect->server_version, JLOG_MYSQLV, ">=") == 1) {
    	$errors[] = $l['admin']['s_mysqlv_tolow'];
    }
    else {
		 new Query("SET NAMES utf8");
     $create['content'] = new Query($sql['content']);
     if($create['content']->error()) $errors[] = "MySQL <pre>".$create['content']->getError()."</pre>";
     $create['comments'] = new Query($sql['comments']);
     if($create['comments']->error()) $errors[] = "MySQL <pre>".$create['comments']->getError()."</pre>";    
     $create['categories'] = new Query($sql['categories']);
     if($create['categories']->error()) $errors[] = "MySQL <pre>".$create['categories']->getError()."</pre>";
     $create['catassign'] = new Query($sql['catassign']);
     if($create['catassign']->error()) $errors[] = "MySQL <pre>".$create['catassign']->getError()."</pre>";
     $create['attributes'] = new Query($sql['attributes']);
     if($create['attributes']->error()) $errors[] = "MySQL <pre>".$create['attributes']->getError()."</pre>";
  }
   
   return $errors;
 }
 
 function do_personal() {
  # returns true if all files and dirs could be generated
  # if not returns the $errors array

  global $l;

  // make some dirs
  $oldmask = umask(0);

  // make some files
  if(!fopen(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."settings.inc.php", "w")) $errors[] = $l['admin']['s_problem_fwrite']." /personal/settings.inc.php";
  if(!fopen(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."rss.xml", "w")) $errors[] = $l['admin']['s_problem_fwrite']." /personal/rss.xml";
  if(!fopen(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."rss-full.xml", "w")) $errors[] = $l['admin']['s_problem_fwrite']." /personal/rss-full.xml";
  if(!fopen(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."subcurrent.inc", "w")) $errors[] = $l['admin']['s_problem_fwrite']." /personal/subcurrent.inc";

  // chmod 666 so that the user have the ability to delete/write to this files
  if(!chmod(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."settings.inc.php", 0666)) $errors[] = $l['admin']['s_problem_chmod']." /personal/settings.inc.php";
  if(!chmod(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."rss.xml", 0666)) $errors[] = $l['admin']['s_problem_chmod']." /personal/rss.xml";
  if(!chmod(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."rss-full.xml", 0666)) $errors[] = $l['admin']['s_problem_chmod']." /personal/rss-full.xml";
  if(!chmod(JLOG_BASEPATH."personal".DIRECTORY_SEPARATOR."subcurrent.inc", 0666)) $errors[] = $l['admin']['s_problem_chmod']." /personal/subcurrent.inc";

  umask($oldmask);

  return $errors;
 }

 function do_htmlpage($content) {

  return '<!DOCTYPE html>
<html lang="'.JLOG_LANGUAGE.'
  <title>SETUP Jlog ' . JLOG_NEW_VERSION . '</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="scripts/css/admin.css" type="text/css" />
  <style type="text/css">
   body {
    background: #F3F3F3;
    color: black;
    font-family: verdana, sans-serif;
    font-size: 100.01%;
   }
   main {
    font-size: 0.9em;
    background: white;
    padding: 20px;
    margin: 1em auto;
    border: 1px solid #aaa;
    width: 600px;
   }
   h1 {
    font-family: georgia, "Times New Roman", Times, sans-serif;
    font-size: 80px;
    margin: 0 0 0 30px;
   }
   #logo { float: right; }
   h2 { margin: 1.5em 0 0.3em 0; font-weight: normal; clear: right; }
   .ok { color: green; }
   .notok, .error { color: red; }
   table { border-spacing: 0.5em; }
   fieldset { padding: 1em; border: 1px solid #ccc; clear: both; margin-top: 1em; }
   legend { font-weight: bold; padding: 0 1em; }
   .button { font-size: 3em; }
   p { text-align: center; }
   fieldset p { text-align: left; }
   a img { border: none; }
  </style>
 </head>
 <body>
  <main>
   <h1><a href="https://github.com/jeena/jlog/" title="Jlog v'.JLOG_NEW_VERSION.'"><img id="logo" src="http://jeenaparadies.net/img/jlog-logo.png" style="width: 210px; height: 120px;" alt="Jlog" /></a> SETUP</h1>
    '.$content.'
  </main>
 </body>
</html>';
 }
 
  function getlang ($allowed, $default) {
    $string = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? null;
    if (null === $string) {
      return $default;
    }
    
    $accepted_languages = preg_split('/,\s*/', $string);
    
    $cur_l = $default;
    $cur_q = 0;
    
    foreach ($accepted_languages as $accepted_language) {
      $res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
                        '(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);
      
      if (!$res) {
        continue;
      }
      
      $lang_code = explode ('-', $matches[1]);
      
      if (isset($matches[2])) {
        $lang_quality = (float)$matches[2];
      } else {
        $lang_quality = 1.0;
      }
      
      while (count ($lang_code)) {
        if (in_array (strtolower (join ('-', $lang_code)), $allowed)) {
          if ($lang_quality > $cur_q) {
            $cur_l = strtolower (join ('-', $lang_code));
            $cur_q = $lang_quality;
            break;
          }
        }
        array_pop ($lang_code);
      }
    }
    
    return $cur_l;
  }
