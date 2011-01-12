<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $table, $_site_title;
if (!$table) $table = "crm_campanas_llamadas_base1";
if (!$sep) $sep = ";";
$fp = fopen("$table.csv",'w');
// $sql = "SELECT contacto_id, s.nombre, r.nombre, u.user, inicio, fin, intentos FROM $table as t, crm_campanas_llamadas_status as s, crm_campanas_llamadas_resultados as r,  users as u WHERE t.status_id=s.status_id AND t.resultado_id=r.resultado_id AND t.user_id=u.uid";
$sql = "SELECT s.nombre, u.user, inicio, fin, fecha_cita, intentos, timestamp FROM $table as t, crm_campanas_llamadas_status as s, users as u WHERE t.status_id=s.status_id AND t.user_id=u.uid ORDER BY s.nombre ASC";
$result = $db->sql_query($sql) or die(print_r($db->sql_error()).$sql);
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
chmod("$table.csv", 0666);
header("location: $table.csv");

$_site_title = "Reporte";
?>