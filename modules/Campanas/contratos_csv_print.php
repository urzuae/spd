<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$_theme = "";
$_css = $_themedir."/style.css";
global $db;

$fp = fopen('contratos_print.csv','w');
$result = $db->sql_query("SELECT * FROM crm_contratos");
while ($row = $db->sql_fetchrow($result))
{
    $line = array();
    $i = 0;
    while ($i < count($row)/2)
    {
        $field = $row[$i++]; 
        switch($i)
        {   //recordar ke se hizo i+++
            case 103: list($field, $hora) = explode(" ", $field); //timestamp
            case 26: //fechas de nacimiento
            case 67:
            case 76:
                    list($y, $m, $d) = explode("-", $field);
                    array_push($line, "$y");
                    array_push($line, "$m");
                    array_push($line, "$d");
                    break;
            default: array_push($line, $field);
        }
        
    }

    fputcsv($fp, $line, ",", "\"");
}
fclose($fp);
chmod('contratos_print.csv', 0666);
header("location: contratos_print.csv");
?>
