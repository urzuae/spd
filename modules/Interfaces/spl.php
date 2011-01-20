<?
	if(!defined('_IN_MAIN_INDEX'))
		die("No puedes accesar directamente a este archivo");
	
	global $db, $_theme, $data;
	
	$_theme = "";
	
	class spl
	{
		public function delete_contact($param)
		{
			$data = $param;
			$params = array();
			
			foreach ($data as $key=>$value)
				$params[] = $value;
				
			$distribuidor_id = $param[0];
			
			$sql = "SELECT contacto_id, uid FROM crm_contactos WHERE distribuidor_id='$distribuidor_id' LIMIT 1";
			$result = $db->sql_query($sql) or der($sql);
			list($contacto_id, $uid) = $db->sql_fetchrow($result);
			
			$sql = "INSERT INTO crm_prospectos_cancelaciones (contacto_id, uid, motivo, motivo_id)VALUES('$contacto_id', '$uid', 'Distribuidor cancelado', '1')";
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
		}
	}
	
?>