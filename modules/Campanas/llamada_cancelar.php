<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $_site_title, $db, $uid, $campana_id, $contacto_id, $submit, $motivo_id, $motivo;

//chekar si estamos autorizados
$_site_title = "Cancelar llamada";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);
$result = $db->sql_query("SELECT name FROM groups WHERE gid='$gid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($grupo) = $db->sql_fetchrow($result);

$sql = "SELECT campana_id FROM crm_campanas_groups  WHERE campana_id='$campana_id' AND gid='$gid' LIMIT 1";

if($db->sql_numrows($db->sql_query($sql)) < 1) die("No está autorizado para acceder aquí.<br>".$sql);

$_css = $_themedir."css/".$_theme."/style.css";
$_theme = "";

if ($submit)
{
  $sql = "INSERT INTO crm_prospectos_cancelaciones (contacto_id, uid, motivo, motivo_id)VALUES('$contacto_id', '$uid', '$motivo', '$motivo_id')";
  $db->sql_query($sql) or die($sql.print_r($db->sql_error()));
  //actualizar el status como finalizado
	$sql = "insert into crm_campanas_llamadas_finalizadas select * from crm_campanas_llamadas where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "insert into crm_contactos_finalizados select * from crm_contactos where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "delete from crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$sql = "delete from crm_contactos WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
  die("<html><head><script>alert('Guardado');window.opener.location='index.php?_module=$_module&_op=llamada&campana_id=$campana_id';window.close();</script></head></body>");
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
