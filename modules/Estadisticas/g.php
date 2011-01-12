<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id;


$graph = "<img src=\"?_module=$_module&_op=graph&campana_id=$campana_id\">";


global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";

if ($campana_id) $_admin_menu2 .= "<br>
<a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Llamadas al día</a><br>
<a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Llamadas exitosas</a><br>
<a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Porcentajes</a><br>
<a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Hora de llamadas</a><br>
<a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Telefonístas</a><br>
<a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Prospectos</a><br>
";
 ?>