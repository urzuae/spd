<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $user_id, $group_id, $empresa_id;
global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
$_css = $_themedir."/files/styleindex.css";
$_css2 = $_themedir."/style.css";
$_theme = "";
$_html = "<html><head><link type=\"text/css\" href=\"$_css\" rel=\"stylesheet\"><link type=\"text/css\" href=\"$_css2\" rel=\"stylesheet\"></head><body>";
$_html .= "<h1>Reportes Mensuales</h1>"
          ."<table>";
for ($mes = 2; $mes <= 9; $mes++)
{
  $im1 = "<img src=\"index.php?_module=Estadisticas&_op=graph_mensual_recuperado&mes=$mes\"><br><br>";
  $im2 = "<img src=\"index.php?_module=Estadisticas&_op=graph_mensual_llamadas&mes=$mes\"><br><br>";
  $_html1 .= "<td>$im1</td>";
  $_html2 .= "<td>$im2</td>";
}
$_html .= "<tr>$_html1</tr><tr>$_html2</tr></table><p align=\"center\"><a href=\"javascript:history.go(-1);\" class=\"content\">Regresar</a></p></body></html>";
 ?>