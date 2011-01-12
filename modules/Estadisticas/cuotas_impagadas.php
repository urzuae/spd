<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $user_id, $group_id, $empresa_id;
global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);



$graph = "<h1>Cuotas impagadas</h1><img src=\"?_module=$_module&_op=graph_cuotas_impagadas&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\"><br>";
$graph .= "<h1>Cuotas impagadas originales</h1><img src=\"?_module=$_module&_op=graph_cuotas_impagadas_original&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\">";


?>