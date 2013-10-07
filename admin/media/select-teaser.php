<?php
 include_once('..'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $l['admin']['pic_choose_old_teaser'] ?></title>
 <link rel="stylesheet" href="<?php echo JLOG_PATH ?>/personal/css/popup.css" type="text/css" media="screen" />
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1><?php echo $l['admin']['pic_choose_old_teaser'] ?></h1> 
<?php
// Bildernamen für blog in ein Array schreiben
$dir = JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR;
$handle = opendir ($dir);
while (false !== ($filename = readdir ($handle))) {
 if (substr($filename, 0, 2) === 't_') {
	$ctime = filectime($dir.$filename);
	$file[$filename] = $ctime;
 }
}
closedir($handle);

if(is_array($file)) {

	asort($file);
	reset($file);

	while ( list($filename, $ctime) = each($file)) {
	 echo "<a href=\"#\"
		 onclick=\"opener.document.forms['entryform'].elements['teaserpic'].value='';
		 opener.document.forms['entryform'].elements['teaserpic'].value+='".substr($filename, 2, strlen($filename))."';
		 window.close();\"><img height=\"50\" src=\"".JLOG_PATH."/img/".$filename."\"></a> ";
	}
}

?>
</body>
</html>
