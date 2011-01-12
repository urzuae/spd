<?
if(!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id, $_theme;
//global $_admin_menu2, $_admin_menu, ;
$_theme = "";

$fecha_ini = "1-10-2008";
$fecha_fin = "31-10-2008";

if($fecha_ini)
{
    $l_excel .= "&fecha_ini=$fecha_ini";
    $titulo .= " desde $fecha_ini";
    $fecha_ini_o = $fecha_ini;
    $fecha_ini = date_reverse($fecha_ini);
    $where_fecha .= " AND cl.fecha_importado>'$fecha_ini 00:00:00'";
}
if($fecha_fin)
{
    $l_excel .= "&fecha_fin=$fecha_fin";
    $titulo .= " hasta $fecha_fin";
    $fecha_fin_o = $fecha_fin;
    $fecha_fin = date_reverse($fecha_fin);
    $where_fecha .= " AND cl.fecha_importado<'$fecha_fin 23:59:59'";
}

$i = 0;
$sql = "SELECT nombre FROM crm_campanas where campana_id = campana_id < 9 LIMIT 8";
$result = $db->sql_query($sql) or die($sql);
while(list($nombre) = $db->sql_fetchrow($result))
{
    $campanas[$i] = $nombre;
    $i++;
}

//obtenemos todas las campañas posibles para evitar consultas posteriores
$origenes = array();
$sql = "SELECT origen_id, nombre FROM crm_contactos_origenes ";
$r = $db->sql_query($sql) or die($sql);
while(list($c_id, $c_nombre) = $db->sql_fetchrow($r))
{
    $origenes[$c_id] = $c_nombre;
}
$etapas = array();
$sql = "SELECT etapa_ciclo_id, campana_id FROM crm_campanas ";
$r = $db->sql_query($sql) or die($sql);
while(list($c_id, $campana) = $db->sql_fetchrow($r))
{
    $etapas[$campana_id] = $c_id;
}

$sql = "SELECT contacto_id, origen_id, fecha_importado, gid
	        FROM crm_contactos AS cl 
	        where 1 $where_fecha";
$result = $db->sql_query($sql) or die($sql);

$contacto_ids = array();
$origen_ids = array();
$fecha_importados = array();
$gids = array();
while(list($contacto_id, $origen_id, $fecha_importado, $gid) = $db->sql_fetchrow($result))
{
    $contacto_ids[] = $contacto_id;
    $origen_ids[$contacto_id] = $origen_id;
    $fecha_importados[$contacto_id] = $fecha_importado;
    $gids[$contacto_id] = $gid;
    //coche
    $sql = "select modelo from crm_prospectos_unidades where contacto_id='$contacto_id'";
    $result2 = $db->sql_query($sql) or die($sql);
    list($modelo) = $db->sql_fetchrow($result2);
    $modelos[$contacto_id] = $modelo;
    //ciclo de venta
    $sql = "select campana_id from crm_campanas_llamadas where contacto_id='$contacto_id'";
    $result2 = $db->sql_query($sql) or die($sql);
    list($campana_id) = $db->sql_fetchrow($result2);
    $etapa_ciclo_ids[$contacto_id] = $etapas[$campana_id];
}

$sql = "SELECT contacto_id, origen_id, fecha_importado, gid
	        FROM crm_contactos_finalizados AS cl 
	        where 1 $where_fecha";
$result = $db->sql_query($sql) or die($sql);
while(list($contacto_id, $origen_id, $fecha_importado, $gid) = $db->sql_fetchrow($result))
{
    $contacto_id = 0 - $contacto_id;
    $contacto_ids[] = $contacto_id;
    $origen_ids[$contacto_id] = $origen_id;
    $fecha_importados[$contacto_id] = $fecha_importado;
    $gids[$contacto_id] = $gid;
    //coche
    $sql = "select modelo from crm_prospectos_unidades where contacto_id='$contacto_id'";
    $result2 = $db->sql_query($sql) or die($sql);
    list($modelo) = $db->sql_fetchrow($result2);
    $modelos[$contacto_id] = $modelo;
    //ciclo de venta
    $sql = "select campana_id from crm_campanas_llamadas where contacto_id='$contacto_id'";
    $result2 = $db->sql_query($sql) or die($sql);
    list($campana_id) = $db->sql_fetchrow($result2);
    $etapa_ciclo_ids[$contacto_id] = $etapas[$campana_id];
}
$tabla = "";
foreach($contacto_ids as $c)
{
    $tabla .= "{$c},{$origenes[$origen_ids[$c]]},{$fecha_importados[$c]},{$gids[$c]},{$modelos[$c]},{$etapa_ciclo_ids[$c]}\n";
}

header("Content-type: text/csv");
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="filename.csv"');
die($tabla);
?>