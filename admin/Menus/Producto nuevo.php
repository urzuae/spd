<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $file, $submit;
if ($file && $submit)
{
			$imgsdir = "../boxes/img/";		
			$tmp_name = $_FILES['file']['tmp_name'];
			$new_name = "newstuff.jpg";
			move_uploaded_file($tmp_name, $imgsdir.$new_name);
			chmod($imgsdir.$new_name, 0666);
}
?>
