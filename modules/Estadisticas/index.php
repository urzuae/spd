<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid;
global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";

$_html .= "<h1>Selecciona una opción</h1>";

$_admin_menu2 .= "<br>
<a href=\"index.php?_module=$_module&_op=campanas\">Campañas</a><br>
<a href=\"index.php?_module=$_module&_op=ciclo\">Ciclo</a><br>

";
/*
<a href=\"index.php?_module=Clientes&_op=graficas\">Clientes</a><br>
<a href=\"index.php?_module=Quejas&_op=graficas\">Casos clientes</a><br>*/

 ?>
