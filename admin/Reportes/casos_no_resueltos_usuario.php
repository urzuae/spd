<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $to, $submit;

$sql = "SELECT queja_id, fecha, uid FROM crm_quejas WHERE status_id=0"; //abierta=0
$result = $db->sql_query($sql) or die("Error");
$tabla .= "<table style=\"width:100%;\"><thead><tr><td>FOLIO</td><td>EXPEDICION</td><td>ASESOR</td></tr></thead>\n<tbody>\n";
$tabla2 .= "<table style=\"width:100%;\"><thead><tr><td>FOLIO</td><td>EXPEDICION</td><td>ASESOR</td></tr></thead>\n<tbody>\n";
while (list($queja_id, $fecha, $uid) = htmlize($db->sql_fetchrow($result)))
{
  list($fecha, $hora) = explode(" ", $fecha);
  $fecha = date_reverse($fecha);
  $sql = "SELECT u.name, g.name FROM users AS u, groups AS g WHERE u.gid=g.gid AND uid='$uid'";
  $result2 = $db->sql_query($sql) or die("Error");
  list($uname, $gname) = htmlize($db->sql_fetchrow($result2));
  $tabla .= "<tr class=\"row".($rowclass++%2+1)."\"><td>$queja_id</td><td>$fecha</td><td>$uname</td></tr>\n";
  $tabla2 .= "<tr><td>$queja_id</td><td>$fecha</td><td>$uname</td></tr>\n";
}
$tabla .= "</tbody></table>";
$tabla2 .= "</tbody></table>";
$body = "Por medio del presente le hago llegar una relación de los folios que aún no han sido resueltos.<br><br>";
$footer = "<br>Sin más por el momento quedo de usted. <br><br>ATENTAMENTE:<br>&nbsp;&nbsp;CALL CENTER";

if ($to && $submit)
{
  mail($to, "Casos Clientes no resueltos", "$body$tabla2$footer");
  $mensaje = "<h1>Email Enviado a $to</h1>";
}
?>