<?php
 include_once('..'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require_once('..'.DIRECTORY_SEPARATOR.'blog.func.php');

 $max_file_size = 60000;
 $up_dir = JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR;
 $up_dir_img = JLOG_PATH."/img/";
 
?>
<!DOCTYPE html>
<html>
<head>
<title><?php echo $l['admin']['pic_upload_teaser'] ?></title>
 <meta charset="UTF-8" />
 <link rel="stylesheet" href="<?php echo JLOG_PATH ?>/personal/css/popup.css" type="text/css" media="screen" />
 <meta name="viewport" content="width=device-width,initial-scale=1"/>
</head>
<body>
<h1><?php echo $l['admin']['pic_upload_teaser'] ?></h1>
<?php
 if (isset($_FILES['probe'])) {
 	if ($_FILES['probe']['error']) // Alternativ: and $_FILES['probe']['size']
	{
	   // Überprüfungen:
	   unset($errors);
	   $e = substr($_FILES['probe']['name'], -4);
	   if(!preg_match('~.jpg|jpeg|.gif|.png~i', substr($_FILES['probe']['name'],-4))) $errors[] = $l['admin']['pic_bad_type']." (".$e.")";

	   if ($_FILES['probe']['size'] > $max_file_size) $errors[] = $l['admin']['pic_to_big']." (".number_format($_FILES['probe']['size']/1000,0,",","")." KB)";

	   if(empty($errors)) {
	     $nr = 0;
	     switch(true)
	      {
	       case preg_match('~.jpg|jpeg~i', $e):
		for(;;) { $nr++; if (!file_exists($up_dir."t_".$nr.".jpg")) break; }
		$filename = "t_".$nr.".jpg";
	       break;
	       case preg_match('~.gif~i', $e):
		for(;;) { $nr++; if (!file_exists($up_dir."t_".$nr.".gif")) break; }
		$filename = "t_".$nr.".gif";
	       break;
	       case preg_match('~.png~i', $e):
		for(;;) { $nr++; if (!file_exists($up_dir."t_".$nr.".png")) break; }
		$filename = "t_".$nr.".png";
	       break;
	      }


		 $imginfo = getimagesize($_FILES['probe']['tmp_name']);

		 if($imginfo[1] > 150 AND $imginfo[0] > 150 ) {
		  $errors[] = $l['admin']['pic_height_widht'];
		 }
		 elseif($imginfo[0] > 150 ) {
		  $errors[] = $l['admin']['pic_width'];
		 }
		 elseif($imginfo[1] > 150 ) {
		  $errors[] = $l['admin']['pic_height'];
		 }
	    if(empty($errors)) {
	     if(!move_uploaded_file($_FILES['probe']['tmp_name'], $up_dir.$filename)) $errors[] = $l['admin']['pic_error'];
	     else chmod($up_dir.$filename, 0664);
	    }
	   }
	   if (empty($errors)) {
	     ?>
	     <p><?php echo $l['admin']['pic_uploaded'] ?></p>
	     <img src="<?php echo $up_dir_img.$filename; ?>" alt="" border="1" height="100">
	     <p><a href="#" onclick="opener.document.forms['entryform'].elements['teaserpic'].value='<?php echo str_replace('t_', '', $filename); ?>';window.close();"><em><?php echo $l['admin']['pic_insert'] ?></em></a></p>
	     <?php
	   }
	  }
  	elseif($_FILES['probe']['error'] === 2) $errors[] = $l['admin']['pic_to_big'];
}
  if(isset($errors)) echo error_output($errors);

if (empty($_FILES['probe']) or isset($errors))
 {
 $btnSubmit = htmlspecialchars($l['admin']['pic_upload']);
 ?>
 <p><?php echo $l['admin']['pic_instr_teaser'] ?></p>
 <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size ?>">
  <?php echo add_session_id_input_tag() ?>
  <input type="file" name="probe" /><br><br>
  <button value="<?= $btnSubmit ?>"><?= $btnSubmit ?></button>
 </form>
 <?php
 }
?>
</body>
</html>
