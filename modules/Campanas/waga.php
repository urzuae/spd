<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db;

//chekar si estamos autorizados
$result = $db->sql_query("SELECT contacto_id FROM crm_prospectos_cancelaciones") or die("Error".print_r($db->sql_error()));
while(list($contacto_id) = $db->sql_fetchrow($result))
{
	$sql = "insert into crm_campanas_llamadas_finalizadas select * from crm_campanas_llamadas where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "insert into crm_contactos_finalizados select * from crm_contactos where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "delete from crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$sql = "delete from crm_contactos WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$count++;
}

$result = $db->sql_query("SELECT contacto_id FROM crm_prospectos_ventas") or die("Error".print_r($db->sql_error()));
while(list($contacto_id) = $db->sql_fetchrow($result))
{
	$sql = "insert into crm_campanas_llamadas_finalizadas select * from crm_campanas_llamadas where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "insert into crm_contactos_finalizados select * from crm_contactos where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "delete from crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$sql = "delete from crm_contactos WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$count++;
}

print("$count registros cambiados");

?>
