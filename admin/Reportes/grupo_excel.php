<?
if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
	die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $cid, $unid,$fecha_ini, $fecha_fin;

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer ( );

if ($cid && ! $unid) {
	$worksheetZonas = $workbook->addWorksheet ( "Reporte Distribuidora " . $grupo );
} else {
	$worksheetZonas = $workbook->addWorksheet ( "Reporte Modelos" );
}

$worksheetZonas->setLandscape ();

$worksheetZonas->setMerge ( 0, 0, 0, 8 ); // Nombre de la empresa


$header_format = $workbook->addFormat ( array ('align' => 'center' ) ); // formato para los encabezados
$header_format->setBold ();
$header_format->setBgColor ( "gray" );
$header_format->setColor ( "white" );

$titles_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$titles_format->setSize ( 9 );
$titles_format->setBold ();

$normal_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$normal_format->setColor ( "blue" );
$normal_format->setSize ( 8 );

$worksheetZonas->setColumn ( 1, 1, 12 ); // Cantidad


$sql = "select name from groups where gid = $cid limit 1";
$result = $db->sql_query ( $sql ) or die ( $sql );
list ( $grupo ) = $db->sql_fetchrow ( $result );

$titulo = date("d-M-Y");

if ($fecha_ini) {
	$fecha_ini_o = $fecha_ini;
	$titulo .= " desde $fecha_ini";
	$fecha_ini = date_reverse ( $fecha_ini );
	$where_fecha .= " AND c.fecha_importado>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
	$fecha_fin_o = $fecha_fin;
	$titulo .= " desde $fecha_fin";
	$fecha_fin = date_reverse ( $fecha_fin );
	$where_fecha .= " AND c.fecha_importado<'$fecha_fin 23:59:59'";
}

if ($cid && ! $unid) {
	$worksheetZonas->setColumn ( 0, 0, 18 ); // Zona
	$worksheetZonas->write ( 0, 0, sprintf ( "REPORTE CONCESIONARIA %s (%s)", $grupo, $titulo ), $header_format );
	$worksheetZonas->write ( 1, 0, "Modelo", $titles_format );
	$worksheetZonas->write ( 1, 1, "Prospectos", $titles_format );
	$reporte = "Distribuidora";
} elseif ($cid && $unid) {
	$worksheetZonas->setColumn ( 0, 0, 20 ); // Zona
	$worksheetZonas->write ( 0, 0, sprintf ( "REPORTE MODELO %s CONCESIONARIA %s (%s)", $zid, $grupo, $titulo ), $header_format );
	$worksheetZonas->write ( 1, 0, "Ciclo", $titles_format );
	$worksheetZonas->write ( 1, 1, "Prospectos", $titles_format );
	$reporte = "Modelo";
}

$fila = 2;

if ($unid && $cid) {
	$sql = "SELECT nombre, campana_id FROM crm_campanas LIMIT 8";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	while ( list ( $nombre, $campana_id ) = $db->sql_fetchrow ( $result ) ) {
		$sql2 = "select count(c.contacto_id) from crm_prospectos_unidades as u, crm_contactos as c, crm_campanas_llamadas as ll 
			     where u.contacto_id = c.contacto_id and c.gid = $cid 
			     and ll.contacto_id = c.contacto_id and u.modelo = '$unid'
			     and right( ll.campana_id, 1 ) = $campana_id $where_fecha 
			     group by u.modelo, ll.campana_id ";
		$result2 = $db->sql_query ( $sql2 ) or die ( $sql2 );
		list ( $cuenta ) = $db->sql_fetchrow ( $result2 );
		if (! $cuenta)
			$cuenta = 0;
		$worksheetZonas->write ( $fila, 0, $nombre, $normal_format ); //Modelo
		$worksheetZonas->write ( $fila, 1, $cuenta ); //Prospectos
		$fila ++;
	}
	/*$sql = "select right(ll.campana_id, 1), count(c.contacto_id) from crm_prospectos_unidades as u, crm_contactos as c, crm_campanas_llamadas as ll where 
		u.contacto_id = c.contacto_id and c.gid = $cid and ll.contacto_id = c.contacto_id and u.modelo = '$unid' group by u.modelo, ll.campana_id";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	while ( list ( $ciclo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
		$sql2 = "SELECT nombre FROM crm_campanas where campana_id = '$ciclo'";
		$result2 = $db->sql_query ( $sql2 ) or die ( $sql2 );
		list ( $nombre ) = $db->sql_fetchrow ( $result2 );
		$worksheetZonas->write ( $fila, 0, $nombre, $normal_format);//Modelo
        $worksheetZonas->write ( $fila, 1, $cuenta);//Prospectos
        $fila++;
	}*/

} elseif ($cid && ! $unid) {
	$sql = "select u.modelo, count(u.modelo) from crm_prospectos_unidades as u, crm_contactos as c where 
		u.contacto_id = c.contacto_id and c.gid = $cid $where_fecha $where_fecha group by modelo";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	while ( list ( $modelo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
		$worksheetZonas->write ( $fila, 0, $modelo, $normal_format ); //Campana
		$worksheetZonas->write ( $fila, 1, $cuenta ); //Prospectos
		$fila ++;
	}
}

$workbook->send ( sprintf ( "reporte%s.xls", $reporte ) );
$workbook->close ();
$workbook->sendFile ();

?>