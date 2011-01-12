<?php
require_once 'xlsFunctions.php';

global $db, $gid, $nombre, $apellido_materno, $apellido_paterno, $vehiculo, $tipo, $fecha_desde, $fecha_hasta, $orderby, $rsort;

$nombre_bk = $nombre;
$apellido_paterno_bk = $apellido_paterno;
$apellido_materno_bk = $apellido_materno;
$vehiculo_bk = $vehiculo;

if ($nombre)
	$where .= "AND c.nombre LIKE '%$nombre%' ";
if ($apellido_paterno)
	$where .= "AND c.apellido_paterno LIKE '%$apellido_paterno%'";
if ($apellido_materno)
	$where .= "AND c.apellido_materno LIKE '%$apellido_materno%'";

$sql = "SELECT c.contacto_id, c.origen_id, c.nombre, c.apellido_paterno, c.apellido_materno, c.tel_casa, c.tel_oficina, c.tel_movil, c.tel_otro, c.uid" . " FROM crm_contactos_finalizados AS c  WHERE (gid='$gid' ) $where ORDER BY c.`timestamp`"; //OR gid='0'
$result = $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );
if ($db->sql_numrows ( $result ) > 0) {
	while ( list ( $c, $origen_id, $nombre, $apellido_paterno, $apellido_materno, $t1, $t2, $t3, $t4, $c_uid ) = htmlize ( $db->sql_fetchrow ( $result ) ) ) {
		$motivo = "";
		$sql_c = "select motivo_id, motivo, DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) from crm_prospectos_cancelaciones where contacto_id = '$c' order by timestamp desc";
		$r3 = $db->sql_query ( $sql_c ) or die ( "Error al consultar datos de cancelacion<br>" . $sql_c );
		if ($db->sql_numrows ( $r3 ) > 0) {
			list ( $motivo_id, $razon, $fecha_cv, $timestamp_cv ) = $db->sql_fetchrow ( $r3 );
			if ($motivo_id == 0)
				$motivo = $razon; else {
				$r4 = $db->sql_query ( "SELECT motivo FROM crm_prospectos_cancelaciones_motivos WHERE motivo_id='$motivo_id' LIMIT 1" );
				list ( $motivo ) = $db->sql_fetchrow ( $r3 );
			}
		}
		$sql_v = "select DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) from crm_prospectos_ventas where contacto_id = '$c' order by timestamp desc";
		$r3 = $db->sql_query ( $sql_v ) or die ( "Error al consultar datos de venta<br>" . $sql_v );
		if ($db->sql_numrows ( $r3 ) > 0) {
			list ( $fecha_cv, $timestamp_cv ) = $db->sql_fetchrow ( $r3 );
			$motivo = "Venta";
		}
		if ($motivo == "")
			$motivo = "Desconocido";
		if ($t4)
			$t = $t4;
		if ($t3)
			$t = $t3;
		if ($t2)
			$t = $t2;
		if ($t1)
			$t = $t1;
		$telefono_ = $t;
		//ponerle nombre al origen
		$r3 = $db->sql_query ( "SELECT nombre FROM crm_contactos_origenes WHERE origen_id='$origen_id' LIMIT 1" );
		list ( $origen ) = $db->sql_fetchrow ( $r3 );
		//el vehiculo que quieren
		$r3 = $db->sql_query ( "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$c' LIMIT 1" );
		list ( $vehiculo ) = $db->sql_fetchrow ( $r3 );
		if ($vehiculo_bk)
			if (strpos ( strtoupper ( $vehiculo ), strtoupper ( $vehiculo_bk ) ) === FALSE)
				continue;
		if ($c_uid) {
			$sql = "SELECT user FROM users WHERE uid='$c_uid'";
			$result2 = $db->sql_query ( $sql ) or die ( "Error" );
			list ( $asignado_a ) = htmlize ( $db->sql_fetchrow ( $result2 ) );
		} else
			$asignado_a = "";
		
		$sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$c' ORDER BY timestamp DESC LIMIT 1";
		$r3 = $db->sql_query ( $sql ) or die ( $sql );
		list ( $ultimo_contacto, $ultimo_contacto_timestamp ) = $db->sql_fetchrow ( $r3 );
		//darle formato en horas al timestamp
		if ($ultimo_contacto_timestamp) {
			$ultimo_contacto_timestamp = time () - $ultimo_contacto_timestamp;
			$ultimo_contacto_timestamp_bk = $ultimo_contacto_timestamp;
			if ($ultimo_contacto_timestamp > 0) {
				$ultimo_contacto_timestamp = $ultimo_contacto_timestamp / 60 / 60 / 24; //entre 60 segs, entre 60 mins
				$ultimo_contacto_timestamp = sprintf ( "%u", $ultimo_contacto_timestamp ); //entero
				

				$ultimo_contacto_timestamp .= " dias"; //($ultimo_contacto_timestamp!=1?"s":"")
			}
		
		} else {
			$ultimo_contacto_timestamp = "";
			$ultimo_contacto_timestamp_bk = "";
		}
		$contactos_id [] = $c;
		$contactos_id_para_sortear [$c] = $c;
		$asignados_a [$c] = $asignado_a;
		$nombres [$c] = "$nombre $apellido_paterno $apellido_materno";
		$origenes [$c] = $origen;
		$origenes_id [$c] = $origen_id;
		$vehiculos [$c] = $vehiculo;
		$esperas [$c] = $ultimo_contacto_timestamp;
		$ultimo_contactos_ts [$c] = $ultimo_contacto_timestamp_bk;
		$motivos [$c] = $motivo;
		$fechas_cv [$c] = $fecha_cv;
		$timestamps_cv [$c] = $timestamp_cv;
	}
	if (count ( $contactos_id ) > 0) {
		//ordenar la tabla por los datos que solicitan
		

		switch ( $orderby) {
			case "fecha" :
				$array_para_ordenar = &$timestamps_cv;
			// 		                  $rsort = 0;
			break;
			case "origen_id" :
				$array_para_ordenar = &$origenes_id;
			// 		                  $rsort = 0;
			break;
			case "contacto_id" :
				$array_para_ordenar = &$contactos_id_para_sortear;
			
			// 		                  $rsort = 0;
			break;
			case "nombre" :
				$array_para_ordenar = &$nombres;
			// 		                  $rsort = 0;
			break; //por referencia para evitar que copie
			case "ultimo_contacto" :
				$array_para_ordenar = &$ultimo_contactos_ts;
			// 		                  $rsort = 1;
			break;
			case "asignado_a" :
				$array_para_ordenar = &$asignados_a;
			// 		                  $rsort = 0;
			break;
			case "vehiculo" :
				$array_para_ordenar = &$vehiculos;
			//                      $rsort = 0;
			break;
			default :
				$array_para_ordenar = &$ultimo_contactos_ts;
			// 		                  $rsort = 0;
		}
		if (! $rsort)
			asort ( $array_para_ordenar ); else //ordenar por valor y conservar asociaciï¿½n de keys
			arsort ( $array_para_ordenar ); //ordenar por valor  en orden inverso y conservar asociaciï¿½n de keys
		foreach ( $array_para_ordenar as $key => $value ) {
			$ordered_contacto_ids [] = $key; //echo $key."->$value<br>";
		}
		if ($rsort == "1")
			$rsort = "0"; else
			$rsort = "1";
		header ( "Pragma: public" );
		header ( "Expires: 0" );
		header ( "Cache-Control: must-revalidate, post-check=0, pre-check=0" );
		header ( "Content-Type: application/force-download" );
		header ( "Content-Type: application/octet-stream" );
		header ( "Content-Type: application/download" );
		header ( "Content-Disposition: attachment;filename=ProspectosFinalizados.xls " );
		header ( "Content-Transfer-Encoding: binary " );
		xlsBOF ();
		xlsWriteLabel ( 0, 0, "Campaña" );
		xlsWriteLabel ( 0, 1, "Id" );
		xlsWriteLabel ( 0, 2, "Nombre" );
		xlsWriteLabel ( 0, 3, "Dias desde el último contacto" );
		xlsWriteLabel ( 0, 4, "Vehículo" );
		xlsWriteLabel ( 0, 5, "Último usuario asignado" );
		xlsWriteLabel ( 0, 6, "Fecha de la cancelación o venta" );
		xlsWriteLabel ( 0, 7, "Motivo" );
		$xlsRow = 1;
		foreach ( $ordered_contacto_ids as $c ) {
			if ($fecha_desde == "")
				$fecha_desde = "00-00-0000";
			if ($fecha_hasta == "")
				$fecha_hasta = date ( "d-m-Y" );
			list ( $day, $month, $year ) = explode ( "-", $fecha_desde );
			$desde = mktime ( 0, 0, 0, $month, $day, $year );
			list ( $day, $month, $year ) = explode ( "-", $fecha_hasta );
			$hasta = mktime ( 23, 59, 59, $month, $day, $year );
			//echo "fecha desde: " . date_reverse($fecha_desde) . "  ";
			$tipo_registro = $motivos [$c] == "Venta" ? "Venta" : "Cancelación";
			
			if ($tipo == "")
				$filtro_tipo = 1; else
				$filtro_tipo = $tipo_registro == $tipo ? 1 : 0;
				
			//echo "tipo_registro = $tipo_registro, motivo = $motivos[$c], filtro_tipo = $filtro_tipo<br>";
			

			if ($fecha_desde == "")
				$filtro_desde = 1; else
				$filtro_desde = $timestamps_cv [$c] >= $desde ? 1 : 0;
			
			if ($fecha_hasta == "")
				$filtro_hasta = 1; else
				$filtro_hasta = $timestamps_cv [$c] <= $hasta ? 1 : 0;
			
			if ($filtro_desde && $filtro_hasta && $filtro_tipo) {
				xlsWriteLabel ( $xlsRow, 0, $origenes [$c] );
				xlsWriteLabel ( $xlsRow, 1, $c );
				xlsWriteLabel ( $xlsRow, 2, $nombres [$c] );
				xlsWriteLabel ( $xlsRow, 3, $esperas [$c] );
				xlsWriteLabel ( $xlsRow, 4, $vehiculos [$c] );
				xlsWriteLabel ( $xlsRow, 5, $asignados_a [$c] );
				xlsWriteLabel ( $xlsRow, 6, $fechas_cv [$c] );
				xlsWriteLabel ( $xlsRow, 7, $motivos [$c] );
				$xlsRow ++;
			}
		}
		xlsEOF ();
	} //si hay algo que mostrar
} else
	echo "<script language=\"javascript\" type=\"text/javascript\">alert(\"No hay registros para generar el reporte\");</script>";

exit ();
?>