<?
	/*if(!defined('_IN_MAIN_INDEX'))
		die("No puedes accesar directamente a este archivo");
		
	global $_theme;
	
	$_theme = "";
	
	//Parsear apellido
	//Escapar el sql
	
	/*if (isset($_POST['data'])) {
		//$data = json_decode($_POST['data']);
		$data = unserialize($data);
		//print_r($data);
		//$data = json_encode($data);
		//print_r($data);
		//$data = json_decode($data);
		//$data = $_POST['data'];
		//print_r($data);
		$params = array();
		
		foreach ($data as $row) {
			//print_r($row);
			$temp = explode(",", $row);
			$params[] = mysql_escape_string($temp[4]);
		}
		
		//print_r($params);
		
		$sql = "SELECT gid FROM groups WHERE name='SALES FUNNEL'";
		$res = $db->sql_query($sql) or die($sql);
		list($gid) = $db->sql_fetchrow($res);
		
		$sql = "SELECT count(c.uid),c.uid,u.gid from crm_contactos as c, users as u where c.uid=u.uid and u.super='8' group by c.uid";
		$result = $db->sql_query($sql);
		$flag = false;
		while(list($current_count, $current_uid, $current_gid) = $db->sql_fetchrow($result)) {
			if(!$flag)
			{
				$count = $current_count;
				$uid = $current_uid;
				$gid = $current_gid;
				$flag = true;
			}
			else
			{
				if ($current_count < $count)
				{
					$count = $current_count;
					$uid = $current_uid;
					$gid = $current_gid;
				}
			}
		}
		
		$sql = "INSERT INTO crm_contactos ( nombre, apellido_paterno, tel_casa, email,nota,gid,uid)
			VALUES ('".$params[0]."','".$params[1]."','".$params[2]."','".$params[3]."','".$params[4]."','".$gid."','".$uid."')";
		
		$result = $db->sql_query($sql) or die($sql);
		$contacto_id = $db->sql_nextid();
		
		//buscar a que campaña lo meteremos
		$sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
		$result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
		list($campana_id) = $db->sql_fetchrow($result);
		//para agregarlo a crm_campanas_llamadas
		$sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id) values ('$campana_id', '$contacto_id')";
		$db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));
		//guardar el log de asignacion
		$sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','$uid','0','$uid','0','$gid')";
		$db->sql_query($sql) or die("Error al insertar al log");
		return true;
	}
	else
	{
		echo false;
	}*/
	
	class pcshome
	{
		public function add_contact($param)
		{
			$_dbhost = 'localhost';
			$_dbuname = 'spd';
			$_dbpass = 'spd';
			$_dbname = 'spd';
			include("../../includes/db/mysql.php");
			$db = new sql_db($_dbhost, $_dbuname, $_dbpass, $_dbname, false);
			
			if(isset($param))
			{
				//$data = unserialize($param);
				$data = $param;
				$params = array();
				
				foreach ($data as $row) {
					$temp = explode(",", $row);
					$params[] = mysql_escape_string($temp[4]);
				}
				$sql = "SELECT gid FROM groups WHERE name='SALES FUNNEL'";
				$res = $db->sql_query($sql) or die($sql);
				list($gid) = $db->sql_fetchrow($res);
				
				$sql = "SELECT COUNT(contacto_id), u.uid, u.gid, u.email FROM crm_contactos c RIGHT JOIN (SELECT uid, gid, email FROM users WHERE super='8') u ON u.uid=c.uid GROUP BY uid";
				$result = $db->sql_query($sql);
				$flag = false;
				while(list($current_count, $current_uid, $current_gid, $current_email) = $db->sql_fetchrow($result)) {
					if(!$flag)
					{
						$count = $current_count;
						$uid = $current_uid;
						$gid = $current_gid;
						$send_mail = $current_email;
						$flag = true;
					}
					else
					{
						if ($current_count < $count)
						{
							$count = $current_count;
							$uid = $current_uid;
							$gid = $current_gid;
							$send_mail = $current_email;
						}
					}
				}
				
				$nombre = $params[0];
				$apellido = $params[1];
				$empresa = $params[2];
				$telefono = $params[3];
				$email = $params[4];
				
				$sql = "INSERT INTO crm_contactos ( nombre, apellido_paterno, tel_casa, email,nota,gid,uid, fecha_importado, empresa)
					VALUES ('".$nombre."','".$apellido."','".$telefono."','".$email."','".$params[5]."\n"."','".$gid."','".$uid."',CURRENT_TIMESTAMP, '$empresa')";
				
				$result = $db->sql_query($sql) or die($sql);
				$contacto_id = $db->sql_nextid();
				
				//buscar a que campaña lo meteremos
				$sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
				$result = $db->sql_query($sql) or die("Error al leer".print_r($db->sql_error()));
				list($campana_id) = $db->sql_fetchrow($result);
				//para agregarlo a crm_campanas_llamadas
				$sql = "insert into crm_campanas_llamadas  (campana_id, contacto_id,inicio,fecha_cita,user_id) values ('$campana_id', '$contacto_id',CURRENT_TIMESTAMP,CURRENT_TIMESTAMP,'$uid')";
				$db->sql_query($sql) or die("Error al insertar a la campaña".print_r($db->sql_error()));
				//guardar el log de asignacion
				$sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id, uid, from_uid, to_uid, from_gid, to_gid)VALUES('$contacto_id','$uid','0','$uid','0','$gid')";
				$db->sql_query($sql) or die("Error al insertar al log");
				
				$sql = "SELECT l.id FROM crm_campanas_llamadas AS l, crm_contactos AS c
					WHERE l.contacto_id=c.contacto_id AND c.uid='$uid'  AND l.campana_id='$campana_id' AND l.status_id!=-1 AND c.contacto_id='$contacto_id' ORDER BY l.timestamp LIMIT 1";
				$result = $db->sql_query($sql);
				
				list($llamada_id) = htmlize($db->sql_fetchrow($result));
				
				$sql = "INSERT INTO crm_campanas_llamadas_eventos (llamada_id,tipo_id,comentario,uid,fecha_cita) VALUES('$llamada_id','2','Llamar inmediatamente','$uid',CURRENT_TIMESTAMP)";
				$db->sql_query($sql) or die("Error".print_r($db->sql_error()));
				
$msg =<<<EOBODY
	<html>
		<head>
			<title>Se ha registrado un prospecto</title>
		</head>
		<body>
			<img style="margin-left:5px;" src="http://pcsmexico.com/files/logo.gif" /><br/><br/>
			<br/>
			<b>Se ha registrado {$nombre} {$apellido}  como prospecto de Distribuidor.</b>
			<p>Comunicarse inmediatamente al siguiente telefono de contacto: {$telefono}
			o a la siguiente direccion electronica: {$email}</p>
			<p>Dispone de hasta dos horas para realizar el contacto con el interesado
			y registrar los avances realizados</p>
		</body>
	</html>
EOBODY;
					$eol="\n";
					$headers = 'From: salesfunnel@pcsmexico.com' . $eol;
					$headers .= 'Reply-To: <salesfunnel@pcsmexico.com>' . $eol;
					$headers .= 'Return-Path: <salesfunnel@pcsmexico.com>' . $eol;
					$headers .= 'Content-Type: text/html; charset=iso-8859-1';
					
					mail($send_mail, "Alta de distribuidora", $msg, $headers);
				
			return true;
			}
			else
				return false;
		}
	}
	
	$soap=new SoapServer(null,array('uri'=>'localhost'));
	$soap->setClass('pcshome');
	$soap->handle();
	die();
	
?>