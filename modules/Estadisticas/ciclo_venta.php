<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id;
global $_admin_menu2, $_admin_menu;





$graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_ciclos_venta&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\">";

$_html .= "<h1>Gráfica de Contactos por Ciclo de Venta</h1><center>$graph</center>";

 ?>
