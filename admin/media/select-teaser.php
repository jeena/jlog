<?php
 include_once('..'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $l['admin']['pic_choose_old_teaser'] ?></title>
 <meta charset="UTF-8" />
 <link rel="stylesheet" href="<?php echo JLOG_PATH ?>/personal/css/popup.css" type="text/css" media="screen" />
 <meta name="viewport" content="width=device-width,initial-scale=1"/>
<script>
function selectImg(evt) {
	opener.document.forms['entryform'].elements['teaserpic'].value = this.dataset.img;
	window.close();
}

document.addEventListener('DOMContentLoaded', () => {
	const btn = document.getElementsByTagName('button');
	for (let i = 0; i < btn.length; ++i) {
		btn[i].addEventListener('click', selectImg);
	}
});
</script>
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

	foreach($file as $filename => $ctime) {
		$filename = htmlspecialchars($filename);
		?><button type="button" data-img="<?= substr($filename, 2) ?>">
		 	<img height="50" src="<?= JLOG_PATH ?>/img/<?= $filename ?>">
		</button><?php
	}
}
?>
</body>
</html>
