<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id,$submit,$_includesdir;
global $_admin_menu2, $_admin_menu;
include_once($_includesdir . "/fusion/FusionCharts.php");
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

if ($gid != 1)
{
    $where_gid = " AND gid='$gid'";
}
$sql = "SELECT nombre, campana_id FROM crm_campanas WHERE 1";
$sql = "SELECT nombre, c.campana_id FROM crm_campanas AS c, crm_campanas_groups  AS g WHERE c.campana_id=g.campana_id $where_gid ORDER BY c.campana_id";
$result = $db->sql_query($sql) or die("Error  ".$sql);
$select_campanas .= "<select name=campana_id>\n";
while (list($nombre, $campana_id2) = $db->sql_fetchrow($result))
{
    $result2 = $db->sql_query("SELECT campana_id FROM crm_campanas_groups where campana_id='$campana_id2'") or die("Error 2");
    if ($db->sql_numrows($result2) > 0)
    {
        $result3 = $db->sql_query("SELECT contacto_id FROM crm_campanas_llamadas where campana_id='$campana_id2'") or die("Error 3");
        if ($db->sql_numrows($result2) < 1) continue; //campaña sin call center
        $sel = "";
        if ($campana_id2 == $campana_id)
            $sel = " SELECTED";
        
        $select_campanas .= "<option value=\"$campana_id2\" $sel>$nombre</option>";
    }
}
$select_campanas .= "</select>";
$lista_gid_no_visibles='';
$array=array();
$res_no_visibles=$db->sql_query("SELECT fuente_id FROM crm_groups_fuentes WHERE gid='".$gid."' ORDER BY fuente_id;");
if($db->sql_numrows($res_no_visibles) > 0)
{
    while(list($fuente_id)=$db->sql_fetchrow($res_no_visibles))
    {
        $array[]=$fuente_id;
    }
    $lista_gid_no_visibles=implode(',',$array);
}
if($lista_gid_no_visibles!='')
    $filtro_gid=" WHERE fuente_id NOT IN (".$lista_gid_no_visibles.") ";

$sql = "SELECT nombre, fuente_id FROM  crm_fuentes  ".$filtro_gid." ORDER BY nombre";
$result = $db->sql_query($sql) or die("Error  ".$sql);
$select_origenes .= "<select name=origen_id>\n";
$select_origenes .= "<option value=\"\">Todas</option>";
while (list($nombre, $origen_id2) = $db->sql_fetchrow($result))
{
        if ($origen_id2 == $origen_id) $sel = " SELECTED";
        else $sel = "";
        $select_origenes .= "<option value=\"$origen_id2\" $sel>$nombre</option>";
}
$select_origenes .= "</select>";


if($submit)
{
  //Recuperamos la informacion
    if (!$campana_id)
        $campana_id = 1;
    if ($fecha_ini)
    {
        $titulo .= " desde $fecha_ini";
        $fecha_ini = date_reverse($fecha_ini);
        $and_fecha .= " AND l.timestamp>'$fecha_ini 00:00:00'";
    }
    if ($fecha_fin)
    {
        $titulo .= " hasta $fecha_fin";
        $fecha_fin = date_reverse($fecha_fin);
        $and_fecha .= " AND l.timestamp<'$fecha_fin 23:59:59'";
    }
    if ($origen_id)
    {
        $and_origen .= " AND c.origen_id='$origen_id'";
    }
    $total_datos=0;
    $sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE (campana_id='0' OR campana_id='$campana_id') ORDER BY orden";
    $result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
    if($db->sql_numrows($result) > 0)
    {
        $xml="<chart bgColor='FFFFFF,FFFFFF' caption='Sales Funnel' FontSize= '18'   showPercentValues='0' decimals='0' baseFontSize='11' isSliced='1' formatNumberScale='1' showValues='1' formatNumberScale='0' showBorder='1'>";
        while(list($status_id, $nombre) = $db->sql_fetchrow($result))
        {
            $sql = "SELECT id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND l.campana_id='$campana_id' AND l.status_id='$status_id' $and_fecha $and_origen";
            $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
            $total=$db->sql_numrows($result2);
            $xml.="<set label='".$nombre."' value='".$total."'/>";
            $total_datos += $total;
        }
        $xml.="</chart>";
        if($total_datos > 0)
        {
            $graph=renderChartHTML($_includesdir."/fusion/Pie3D.swf", "", $xml, "grafico", 450, 350, false);
        }

    }

}



/*if ($campana_id)
  $graph = "<img src=\"?_module=$_module&_op=graph&campana_id=$campana_id&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&origen_id=$origen_id\">";
$_html .= "<center>$graph<h1>Selecciona una campaña</h1><form><input type=hidden name=_module value=\"$_module\"><input type=hidden name=_op value=\"$_op\">$select_campanas<input type=submit value=Aceptar></form></center>";
*/
 ?>
