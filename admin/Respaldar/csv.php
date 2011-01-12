<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
include("$_includesdir/zip.lib.php");

global $db, $table;
$zip = new zipfile();
$sql = "SHOW TABLES LIKE '%$table%'";
$result = $db->sql_query($sql);
while (list($table) = $db->sql_fetchrow($result))
{
  $sql = "SELECT * FROM `$table` WHERE 1";
  $result2 = $db->sql_query($sql);
  $num_cols = $db->sql_numfields($result2);
  $output = "";
  while ($array = $db->sql_fetchrow($result2))
  {
    for ($i = 0; $i < $num_cols; $i ++)
    {
      if ($i) $output .= ";";
      $output .= "\"".addslashes($array[$i])."\"";
    }
    $output .= "\n";
  }
  $zip->addFile($output, $table);
}

// //ya estan las tablas, ahora las imagenes
$dir = "imagenes/";

// Open a known directory, and proceed to read its contents
if (is_dir($dir)) {
   if ($dh = opendir($dir)) {
       while (($file = readdir($dh)) !== false) {
           if (strpos($file, ".jpg") <= 0) continue;
           $filename = $dir.$file;
           $zip->addFile(fread(fopen ($filename, "r"), filesize ($filename)), "__".$filename);
       }
       closedir($dh);
   }
}
//$zip->addFile(fread(fopen ($filename, "r"), filesize ($filename)), $filename);

header('Content-type: application/zip');
$date = date("ymd");
header("Content-Disposition: attachment; filename=\"backup".$date.".zip\"");
echo $zip->file();

?> 
