<?
//   if (!defined('_IN_ADMIN_MAIN_INDEX')) {
//     die ("No puedes acceder directamente a este archivo...");
// }
    global $db, $file, $submit, $del;
    global $_admin_menu2, $_admin_menu,$_site_title;
$_site_title = "Reportes";
$_admin_menu2 .= "<h2>Campañas</h2>
<a href=\"index.php?_module=$_module&_op=campanas_avance\" >Reporte de avance</a><br>
<a href=\"index.php?_module=$_module&_op=penalizacion_usuarios\" >Reporte de penalizaciones por usuario</a><br>
<br>
<h2>Estadísticas</h2>
<a href=\"index.php?_module=$_module&_op=autos\">Graficas</a><br>
<a href=\"index.php?_module=$_module&_op=status_concesionaria\">Status Distribuidor</a><br>
<a href=\"index.php?_module=$_module&_op=prospectos_no_asignados\">Prospectos no asignados a Distribuidores</a><br>
<br>
<h2>Reportes</h2>
<a href=\"index.php?_module=$_module&_op=pdf_cantidad_ventas_concretadas_por_vendedor\">Cantidad de Ventas Concretadas por Vendedor</a><br>
<a href=\"index.php?_module=$_module&_op=pdf_cancelaciones_ventas\">Cantidad de Cancelaciones de Ventas</a><br>
<a href=\"index.php?_module=$_module&_op=pdf_cancelaciones_ventas_motivos\">Motivos de Cancelaciones de Ventas</a><br>
";
 ?>
