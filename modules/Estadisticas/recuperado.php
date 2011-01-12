<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $user_id, $group_id, $empresa_id;
global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);



$graph = "<img src=\"?_module=$_module&_op=graph_recuperado&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\">";



// We need some data
$datax = array();
$datay2 = array();
$datay= array();
$data = array();
$data_total = array();
$colores = array("red", "blue", "green", "yellow", "violet", "cyan", "orange", "purple");




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
    $data[$i] += ($importe_unidades_original - $importe_unidades);
    $data_total[$i] += $importe_unidades_original;
  }

  array_push($datax, $nombre);
  if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
  array_push($datay, $data[$i]);
  array_push($datay2, $data_total[$i]);
  $t2 += $data[$i];
  $t3 += $data_total[$i];
  $i++;
}
$datax[$i] = "TOTAL";
$datay[$i] = $t2;
$datay2[$i] = $t3;

$table = "<table><thead><tr><td>Empresa</td><td>Total entregado</td><td>Recuperado</td><td>Porcentaje</td><td>Restantes</td><td><a href=\"index.php?_module=$_module&_op=recuperado_excel\"><img src=\"img/excel.png\"></a></td></tr></thead>";

for ($i = 0; $i < count($datax); $i++)
{
  if ($data[$i]) $percent = sprintf(" (%.1f%%)", $data[$i] / $data_total[$i] * 100);
  else $percent = "";
 
  $table .= "<tr class=\"row".(($c++%2)+1)."\"><td>{$datax[$i]}</td><td>".money_format2("%d",$datay2[$i])."</td><td>".money_format2("%d",$datay[$i])." </td><td>$percent</td><td>".money_format2("%d",$datay2[$i]-$datay[$i])."</td><td></td></tr>";
}
$table .= "</table>";
?>