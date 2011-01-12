<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $status_id, $fecha_ini, $fecha_fin;
if (isset($status_id))
  $where .= " AND status_id='$status_id' ";
if ($fecha_ini)
{
  $fecha_ini = date_reverse($fecha_ini);
  $where .= " AND fecha>='$fecha_ini'";
}
if ($fecha_fin)
{
  $fecha_fin = date_reverse($fecha_fin);
  $where .= " AND fecha<='$fecha_fin'";
}


$sql = "SELECT queja_id, contacto_id, status_id, fecha FROM crm_quejas WHERE 1 $where";
$result = $db->sql_query($sql) or die("Error consultando $sql");
while (list($queja_id, $contacto_id, $status_id_q, $fecha) = htmlize($db->sql_fetchrow($result)))
{
  $sql = "SELECT nombre FROM crm_quejas_status WHERE status_id='$status_id_q'";
  $result2 = $db->sql_query($sql) or die("Error consultando 2");
  list($status) = htmlize($db->sql_fetchrow($result2));
  $sql = "SELECT nombre, apellido_paterno, apellido_materno FROM crm_contactos WHERE contacto_id='$contacto_id'";
  $result2 = $db->sql_query($sql) or die("Error consultando 2");
  list($nombre, $apellido_paterno, $apellido_materno) = htmlize($db->sql_fetchrow($result2));
  list($fecha, $hora) = explode(" ", $fecha);
  $fecha = date_reverse($fecha);
  $tabla .= "<tr class=\"row".(++$class_row%2?'1':'2')."\">"
            ."<td>$fecha</td>"
            ."<td>$nombre $apellido_paterno $apellido_materno</td>"
            ."<td>$status</td>"
            ."<td><a href=\"index.php?_module=$_module&_op=print&queja_id=$queja_id\" target=\"pdf\"><img src=\"../img/print.gif\" border=0></a></td>"
            ."</tr>\n";
}
if ($db->sql_numrows($result) > 0)
{
  $tabla  = "<table style=\"width:100%;\">\n"
            ."<thead><tr><td>Fecha</td><td>Nombre</td><td>Status</td><td style=\"width:24px;\">Acción</td></tr></thead>\n"
            .$tabla."</table>\n";
}
else
  $tabla = "<br><center>No se encontraron registros.</center><br>";
  
$sql = "SELECT status_id, nombre FROM crm_quejas_status WHERE 1 ORDER BY status_id";
$result = $db->sql_query($sql) or die("Error al consultar status");
while (list($status_id_s, $nombre) = htmlize($db->sql_fetchrow($result)))
{
  if ($status_id == $status_id_s) $selected = " SELECTED";
  else $selected = "";
  $select_status .= "<option value=\"$status_id_s\"$selected>$nombre</option>";
}

$select_status = "<select name=\"status_id\"><option>Todos</option>".$select_status."</select>";
?>