<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$_theme = "";
$_css = $_themedir."/style.css";
global $db;

$fp = fopen('contratos.csv','w');
$result = $db->sql_query("SELECT * FROM crm_contratos");
while ($row = $db->sql_fetchrow($result))
{
    $line = array();
    $i = 0;
    while ($i < count($row)/2)
    {
        $field = $row[$i++];
        array_push($line, $field);
    }

    fputcsv($fp, $line, ",", "\"");
}
fclose($fp);
chmod('contratos.csv', 0666);
header("location: contratos.csv");
?>
