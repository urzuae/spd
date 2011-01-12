<?
global $db, $file, $submit, $del,$_site_title;
global $_admin_menu2, $_admin_menu;
$_site_title = "Carga de datos";
$_admin_menu2 .= "<br>
<a href=\"index.php?_module=$_module&_op=grupos\">Distribuidores</a><br>
<a href=\"index.php?_module=$_module&_op=prospectos\">Prospectos</a><br>
<a href=\"index.php?_module=$_module&_op=modelos\">Productos</a><br>";
#<a href=\"index.php?_module=$_module&_op=usuarios\">Usuarios</a><br>
?>