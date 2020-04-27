<?php
 include_once('..'.DIRECTORY_SEPARATOR.'auth.php');
 define("JLOG_ADMIN", true);
 require_once('..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'scripts'.DIRECTORY_SEPARATOR.'prepend.inc.php');
 require_once('..'.DIRECTORY_SEPARATOR.'blog.func.php');

 $max_file_size = 300000;
 $up_dir = JLOG_BASEPATH.'img'.DIRECTORY_SEPARATOR;
 $up_dir_img = JLOG_PATH."/img/";
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
<head>
<title><?php echo $l['admin']['pic_upload_header'] ?></title>
 <link rel="stylesheet" href="<?php echo JLOG_PATH ?>/personal/css/popup.css" type="text/css" media="screen" />
 <script type="text/javascript" src="<?php echo JLOG_PATH ?>/scripts/javascripts.js"></script>
 <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body>
<h1><?php echo $l['admin']['pic_upload_header'] ?></h1>
<?php
 if (isset($_FILES['probe']) && ! $_FILES['probe']['error']) // Alternativ: and $_FILES['probe']['size']
  {
   // Überprüfungen:
   unset($errors);
   $e = substr($_FILES['probe']['name'], -4);
   if(!preg_match('~.jpg|jpeg|.gif|.png~i', $e)) $errors[] = $l['admin']['pic_bad_type']." (".$_FILES['probe']['type'].")";

   if ($_FILES['probe']['size'] > $max_file_size) $errors[] = $l['admin']['pic_to_big']." (".number_format($_FILES['probe']['size']/1000,0,",","")." KB)";


   if(empty($errors)) {
     $nr = 0;
     switch(true)
      {
       case preg_match('~.jpg|jpeg~i', $e):
        for(;;) { $nr++; if (!file_exists($up_dir.$nr.".jpg")) break; }
        $filename = $nr.".jpg";
       break;
       case preg_match('~.gif~i', $e):
        for(;;) { $nr++; if (!file_exists($up_dir.$nr.".gif")) break; }
        $filename = $nr.".gif";
       break;
       case preg_match('~.png~i', $e):
        for(;;) { $nr++; if (!file_exists($up_dir.$nr.".png")) break; }
        $filename = $nr.".png";
       break;
      }

    if(empty($errors)) {
     if(!move_uploaded_file($_FILES['probe']['tmp_name'], $up_dir.$filename)) $errors[] = $l['admin']['pic_error'];
     else chmod($up_dir.$filename, 0664);
    }
   }
   if (empty($errors)) {
     ?>
     <p><?php echo $l['admin']['pic_uploaded'] ?></p>
     <p><img src="<?php echo $up_dir_img.$filename; ?>" alt="" border="1" height="100"></p>
       <form onSubmit="jlog_bbcode_img('<?php echo $filename; ?>'); return false;">
     <p><?php echo $l['admin']['pic_class'] ?><br />
        <input id="class" type="text" size="20"></p>
     <p><?php echo $l['admin']['pic_alt'] ?><br />
        <input id="alt" type="text" size="20"></p>
     <p><button value="<?php echo $l['admin']['pic_insert'] ?>"><?php echo htmlspecialchars($l['admin']['pic_insert']) ?></button></p>
     </form>
    <?php
   }
  }
  elseif($_FILES['probe']['error'] === 2) $errors[] = $l['admin']['pic_to_big'];
  if(isset($errors)) echo error_output($errors);

if (empty($_FILES['probe']) or isset($errors))
 {
 ?>
 <p><?php echo $l['admin']['pic_instructions'] ?></p>
 <form action="<?php echo $_SERVER['SCRIPT_NAME']; ?>" method="post" enctype="multipart/form-data" accept-charset="UTF-8">
  <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $max_file_size ?>">
  <?php echo add_session_id_input_tag() ?>
  <input type="file" name="probe" /><br><br>
  <button value="<?php echo $l['admin']['pic_upload'] ?>"><?php echo htmlspecialchars($l['admin']['pic_upload']) ?></button>
 </form>
 <?php
 }
?>
</body>
</html>
