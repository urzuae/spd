<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $user_id, $group_id, $empresa_id;
// global $_admin_menu2, $_admin_menu;

$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

$filename = "recuperado_excel.csv";
$fp = fopen("$filename", 'w');

fputcsv($fp, array("Empresa","Total entregado","Recuperado","Porcentaje","Restante"));



$sql = "SELECT empresa_id, nombre FROM empresas WHERE 1";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
$i = 0;
while(list($empresa_id, $nombre) = $db->sql_fetchrow($result))
{
  $data[$i] = 0;
  $data_total[$i] = 0;

  //todas las campañas de esta empresa
  $sql = "SELECT campana_id FROM groups AS g, crm_campanas_groups AS c WHERE c.gid=g.gid AND g.empresa_id='$empresa_id'";
  $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
  while(list($campana_id) = $db->sql_fetchrow($result2))
  {
    $sql = "SELECT SUM(o.saldo), SUM(o.saldo_original) FROM crm_campanas_llamadas AS c, crm_contactos AS o WHERE c.contacto_id=o.contacto_id AND campana_id='$campana_id'";
    $result3 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    list($importe_unidades, $importe_unidades_original) = $db->sql_fetchrow($result3);
    if (!$importe_unidades_original) $importe_unidades_original = 0;
    if (!$importe_unidades) $importe_unidades = 0;
    $total_recuperado[$i] += ($importe_unidades_original - $importe_unidades);
    $total_entregado[$i] += $importe_unidades_original;
  }

  if ($total_entregado[$i]) 
    $percent[$i] = $total_recuperado[$i] / $total_entregado[$i] * 100;
  else
    $percent[$i] = "0";
  
  
  fputcsv($fp, array("$nombre",$total_entregado[$i],$total_recuperado[$i],$percent[$i]."%",$total_entregado[$i]-$total_recuperado[$i]));

  $i++;
}
//total
fputcsv($fp, array("Total",array_sum($total_entregado),array_sum($total_recuperado),array_sum($percent)/$i,array_sum($total_entregado)-array_sum($total_recuperado)));


fclose($fp);
chmod($filename, 0666);
header("Content-type: text/csv\n");
header("Content-disposition: inline; filename=$filename.csv");
header("Content-transfer-encoding: binary\n");
// header("Content-length: $len\n");
header("location:$filename");

?>