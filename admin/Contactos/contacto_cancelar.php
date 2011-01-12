<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $campana_id, $contacto_id, $submit, $motivo_id, $motivo, $gid;

//chekar si estamos autorizados

$result = $db->sql_query("SELECT name FROM groups WHERE gid='$gid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($grupo) = $db->sql_fetchrow($result);


$_css = $_themedir."/style.css";
$_theme = "";

if ($submit)
{
  $sql = "INSERT INTO crm_prospectos_cancelaciones (contacto_id, uid, motivo, motivo_id)VALUES('$contacto_id', '$uid', '$motivo', '$motivo_id')";
  $db->sql_query($sql) or die($sql.print_r($db->sql_error()));
  //actualizar el status como finalizad
  $sql = "INSERT INTO crm_contactos_finalizados Select * from crm_contactos where contacto_id = '$contacto_id'";
  $db->sql_query($sql) or die($sql.print_r($db->sql_error()));
  $sql = "INSERT INTO crm_campanas_llamadas_finalizadas Select * from crm_campanas_llamadas where contacto_id = '$contacto_id'";
  $db->sql_query($sql) or die($sql.print_r($db->sql_error()));
  $sql = "delete from crm_contactos where contacto_id = '$contacto_id'";
  $db->sql_query($sql) or die($sql.print_r($db->sql_error()));
  $sql = "delete from crm_campanas_llamadas where contacto_id = '$contacto_id'";
  $db->sql_query($sql) or die($sql.print_r($db->sql_error()));
  die("<html><head><script>alert('Guardado');window.opener.location='index.php?_module=$_module&_op=cancelar&buscar_gid=$gid&submit=buscar';window.close();</script></head></body>");
}


$select_motivos = "<select name=\"motivo_id\">";
$result2 = $db->sql_query("SELECT motivo_id, motivo FROM crm_prospectos_cancelaciones_motivos WHERE 1 order by motivo_id") or die("Error".print_r($db->sql_error()));
while(list($a_uid, $a_motivo) = htmlize($db->sql_fetchrow($result2)))
{
  $select_motivos .= "<option value=\"$a_uid\">$a_motivo</option>";
}
$select_motivos .= "<option value=\"0\">Otro</option>";
$select_motivos .= "</select>";
?>
