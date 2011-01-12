<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $to, $submit, $fecha_ini, $fecha_fin;

if ($fecha_ini)
{
  $rango .= " desde el $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND fecha>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $rango .= " hasta el $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND fecha<'$fecha_fin 23:59:59'";
}

$sql = "SELECT queja_id, fecha, uid, status_id FROM crm_quejas WHERE status_id!=0$and_fecha"; //abierta=0
$result = $db->sql_query($sql) or die("Error");
if ($db->sql_numrows($result)>0)
{
  $tabla .= "<table style=\"width:100%;\"><thead><tr><td>FOLIO</td><td>EXPEDICION</td><td>ASESOR</td><td>STATUS</td></tr></thead>\n<tbody>\n";
  $tabla2 .= "<table style=\"width:100%;\"><thead><tr><td>FOLIO</td><td>EXPEDICION</td><td>ASESOR</td><td>STATUS</td></tr></thead>\n<tbody>\n";
  while (list($queja_id, $fecha, $uid, $status_id) = htmlize($db->sql_fetchrow($result)))
  {
    list($fecha, $hora) = explode(" ", $fecha);
    $fecha = date_reverse($fecha);
    $sql = "SELECT u.name, g.name FROM users AS u, groups AS g WHERE u.gid=g.gid AND uid='$uid'";
    $result2 = $db->sql_query($sql) or die("Error");
    list($uname, $gname) = htmlize($db->sql_fetchrow($result2));
    $sql = "SELECT nombre FROM crm_quejas_status WHERE status_id='$status_id'";
    $result2 = $db->sql_query($sql) or die("Error");
    list($status) = htmlize($db->sql_fetchrow($result2));
    $tabla .= "<tr class=\"row".($rowclass++%2+1)."\"><td>$queja_id</td><td>$fecha</td><td>$gname</td><td>$status</td></tr>\n";
    $tabla2 .= "<tr><td>$queja_id</td><td>$fecha</td><td>$gname</td><td>$status</td></tr>\n";
  }
  $tabla .= "</tbody></table>";
  $tabla2 .= "</tbody></table>";
  $body = "Por medio del presente le hago llegar una relación de los folios que han sido resueltos$rango.<br><br>";
  $footer = "<br>Sin más por el momento quedo de usted. <br><br>ATENTAMENTE:<br>&nbsp;&nbsp;CALL CENTER";
  
  if ($to && $submit)
  {
    mail($to, "Casos Clientes resueltos", "$body$tabla2$footer");
    $mensaje = "<h1>Email Enviado a $to</h1>";
  }
}
else $tabla = "<center>No se encontraron registros que cumplan con esos criterios</center>";


$fecha_ini = date_reverse($fecha_ini);
$fecha_fin = date_reverse($fecha_fin);
?>