<?php
 include_once('..'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $l['admin']['pic_choose_old'] ?></title>
 <link rel="stylesheet" href="<?php echo JLOG_PATH ?>/personal/css/popup.css" type="text/css" media="screen" />
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
 <script type="text/javascript" src="<?php echo JLOG_PATH ?>/scripts/javascripts.js"></script>
</head>
<body>
<h1><?php echo $l['admin']['pic_choose_old'] ?></h1> 
<?php
// Bildernamen für blog in ein Array schreiben
$dir = JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR;

if($_GET['p']) { ?>
   	 <p><img src="<?php echo JLOG_PATH ?>/img/<?php echo $_GET['p']; ?>" alt="" border="1" height="100"></p>
       <form onSubmit="jlog_bbcode_img('<?php echo htmlspecialchars($_GET['p']); ?>'); return false;" accept-charset="UTF-8">
   	 <p><?php echo $l['admin']['pic_class'] ?><br />
   	    <input id="class" type="text" size="20"></p>
   	 <p><?php echo $l['admin']['pic_alt'] ?><br />
   	    <input id="alt" type="text" size="20"></p>
   	 <p><input type="submit" value="<?php echo $l['admin']['pic_insert'] ?>"></p>
   	 </form>
<?php
}
else {
	$handle = opendir ($dir);
	while (false !== ($filename = readdir ($handle))) {
	 if ($filename != "." && $filename != ".." && substr($filename, 0, 2) != 't_' && substr($filename, 0, 5) != 'JLOG_') {
		$ctime = filectime($dir.$filename);
		$file[$filename] = $ctime;
	 }
	}
	closedir($handle);
	
	if(is_array($file)) {
	
		asort($file);
		reset($file);
	
		while ( list($filename, $ctime) = each($file)) {
		 echo "<a href='?p=".$filename."' ><img height=\"50\" src=\"".JLOG_PATH."/img/".$filename."\"></a>\n";
		}
	}
}
?>
</body>
</html>
