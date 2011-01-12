<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $ciclo_de_venta_id, $fecha_ini, $fecha_fin;
if ($ciclo_de_venta_id)
  $where .= " AND ciclo_de_venta_id='$ciclo_de_venta_id' ";
if ($fecha_ini)
{
  $where .= " AND fecha_captura>='".date_reverse($fecha_ini)."'";
}
if ($fecha_fin)
{
  $where .= " AND fecha_captura<='".date_reverse($fecha_fin)."'";
}


$sql = "SELECT prospecto_id, contacto_id, uid, fecha_captura, ciclo_de_venta_id FROM crm_prospectos WHERE 1 $where order by fecha_captura";
$result = $db->sql_query($sql) or die("Error consultando $sql");
while (list($prospecto_id, $contacto_id, $uid, $fecha_captura, $ciclo_de_venta_id) = htmlize($db->sql_fetchrow($result)))
{
  $sql = "SELECT nombre FROM crm_prospectos_ciclo_de_venta WHERE ciclo_de_venta_id='$ciclo_de_venta_id'";
  $result2 = $db->sql_query($sql) or die("Error consultando 2");
  list($status) = htmlize($db->sql_fetchrow($result2));
  $sql = "SELECT nombre, apellido_paterno, apellido_materno FROM crm_contactos WHERE contacto_id='$contacto_id'";
  $result2 = $db->sql_query($sql) or die("Error consultando 2");
  list($nombre, $apellido_paterno, $apellido_materno) = htmlize($db->sql_fetchrow($result2));
  $sql = "SELECT name FROM users WHERE uid='$uid'";
  $result2 = $db->sql_query($sql) or die("Error consultando 2");
  list($usuario) = htmlize($db->sql_fetchrow($result2));
  list($fecha, $hora) = explode(" ", $fecha_captura);
  $fecha = date_reverse($fecha);
  $tabla .= "<tr class=\"row".(++$class_row%2?'1':'2')."\">"
            ."<td>$fecha</td>"
            ."<td>$nombre $apellido_paterno $apellido_materno</td>"
            ."<td>$usuario</td>"
            ."<td>$status</td>"
            //."<td><a href=\"index.php?_module=$_module&_op=print&queja_id=$queja_id\" target=\"pdf\"><img src=\"../img/print.gif\" border=0></a></td>"
            ."</tr>\n";
}
if ($db->sql_numrows($result) > 0)
{
  $tabla  = "<table style=\"width:100%;\">\n"
            ."<thead><tr><td>Fecha de captura</td><td>Nombre</td><td>Capturado por</td><td>Ciclo de venta</td></tr></thead>\n"//<td style=\"width:24px;\">Acción</td>
            .$tabla."</table>\n";
}
else
  $tabla = "<br><center>No se encontraron registros.</center><br>";
  
$sql = "SELECT ciclo_de_venta_id, nombre FROM crm_prospectos_ciclo_de_venta WHERE 1 ORDER BY ciclo_de_venta_id";
$result = $db->sql_query($sql) or die("Error al consultar status");
while (list($status_id_s, $nombre) = htmlize($db->sql_fetchrow($result)))
{
  if ($ciclo_de_venta_id == $status_id_s) $selected = " SELECTED";
  else $selected = "";
  $select_status .= "<option value=\"$status_id_s\"$selected>$nombre</option>";
}

$select_status = "<select name=\"ciclo_de_venta_id\"><option value=''>Todos</option>".$select_status."</select>";
?>
