<?php
 include_once('..'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $l['admin']['pic_choose_old'] ?></title>
 <meta charset="UTF-8" />
 <link rel="stylesheet" href="<?php echo JLOG_PATH ?>/personal/css/popup.css" type="text/css" media="screen" />
 <script type="text/javascript" src="<?php echo JLOG_PATH ?>/scripts/javascripts.js"></script>
 <meta name="viewport" content="width=device-width,initial-scale=1"/>
</head>
<body>
<h1><?php echo $l['admin']['pic_choose_old'] ?></h1> 
<?php
// Bildernamen für blog in ein Array schreiben
$dir = JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR;

if(isset($_GET['p'])) {
	$p = htmlspecialchars($_GET['p']);
	$btnSubmit = htmlspecialchars($l['admin']['pic_insert']);
	?>
   	 <p><img src="<?= JLOG_PATH ?>/img/<?= $p ?>" alt="" border="1" height="100"></p>
       <form onSubmit="jlog_bbcode_img('<?= $p ?>'); return false;" accept-charset="UTF-8">
   	 <p><?php echo $l['admin']['pic_class'] ?><br />
   	    <input id="class" type="text" size="20"></p>
   	 <p><?php echo $l['admin']['pic_alt'] ?><br />
   	    <input id="alt" type="text" size="20"></p>
   	 <p><button value="<?= $btnSubmit ?>"><?= $btnSubmit ?></button></p>
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

		foreach ($file as $filename => $ctime) {
			$filename = htmlspecialchars($filename);
			?><a href='?p=<?= $filename ?>'>
				<img height="50" src="<?= JLOG_PATH ?>/img/<?= $filename ?>">
			</a><?php
		}
	}
}
?>
</body>
</html>
