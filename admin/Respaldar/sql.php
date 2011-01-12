<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$_theme = "";
global $db, $table;

$output = "";
$sql = "SHOW TABLES LIKE '%$table%'";

$result = $db->sql_query($sql);
while (list($table) = $db->sql_fetchrow($result))
{
  $sql = "SELECT * FROM `$table` WHERE 1";
  
  $result2 = $db->sql_query($sql);
  $num_cols = $db->sql_numfields($result2);
  $output .= "TRUNCATE TABLE `$table`;\n";
  
  while ($array = $db->sql_fetchrow($result2))
  {
    $output .= "INSERT INTO `$table` VALUES (";
    for ($i = 0; $i < $num_cols; $i ++)
    {
      if ($i) $output .= ",";
      $output .= "'".addslashes($array[$i])."'";
    }
    $output .= ");\n";
  }
}

header('Content-type: application/sql');
header('Content-Disposition: attachment; filename="backup-'.date('Ymd').'.sql"');
echo $output;
die();
?> 
