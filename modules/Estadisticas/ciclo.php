<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin;
global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
if ($gid != 1) $where_gid = " AND gid='$gid'";

$sql = "SELECT nombre, campana_id FROM crm_campanas WHERE 1";
$result = $db->sql_query($sql) or die("Error");
$select_campanas .= "<select name=campana_id>\n";
while (list($nombre, $campana_id2) = $db->sql_fetchrow($result))
{
    //if ($gid == 1) if (!strpos($nombre, 'Prospecc')) continue;
    $result2 = $db->sql_query("SELECT campana_id FROM crm_campanas_groups where campana_id='$campana_id2' $where_gid") or die("Error 2");
    if ($db->sql_numrows($result2) > 0)
    {
        $result3 = $db->sql_query("SELECT contacto_id FROM crm_campanas_llamadas where campana_id='$campana_id2'") or die("Error 3");
        if ($db->sql_numrows($result2) < 1) continue; //campaña sin call center
        if ($campana_id2 == $campana_id) $sel = " SELECTED";
        else $sel = "";
        $select_campanas .= "<option value=\"$campana_id2\" $sel>$nombre</option>";
    }
}
$select_campanas .= "</select>";

if ($campana_id) 
{
  $graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_ciclo&campana_id=$campana_id&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\">";
} 
$_html .= "<h1>Gráfica de ciclos</h1><center>$graph<h1>Selecciona una campaña donde inicie el ciclo</h1><form><input type=hidden name=_module value=\"$_module\"><input type=hidden name=_op value=\"$_op\">$select_campanas<input type=submit value=Aceptar></form></center>";
 ?>
