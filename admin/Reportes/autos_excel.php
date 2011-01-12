<?
if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
	die ( "No puedes acceder directamente a este archivo..." );
}

global $db, $fecha_ini, $fecha_fin,$gid;

if($gid){
	$where_concesionaria .= " AND c.gid = '$gid'";	
}

if ($fecha_fin || $fecha_ini) {
    $sql = "SELECT nombre FROM `crm_unidades`";
    $result = $db->sql_query ( $sql ) or die ( $sql );
    
    if ($fecha_ini) {
		$titulo .= " desde $fecha_ini";
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND c.fecha_importado>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin) {
		$titulo .= " hasta $fecha_fin";
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND c.fecha_importado<'$fecha_fin 23:59:59'";
	}
    
    while ( list ( $modelo_nombre) = $db->sql_fetchrow ( $result ) ) {
        $sql_con[] = "SELECT u.modelo, COUNT( u.contacto_id ) FROM crm_prospectos_unidades AS u, crm_contactos AS c
    	 WHERE u.modelo = '$modelo_nombre' AND c.contacto_id = u.contacto_id ".$where_fecha." $where_concesionaria GROUP BY u.modelo "; 	
    }
}
else{
	$sql_con[] = "select u.modelo, 
	                     count(u.modelo) 
	              from crm_prospectos_unidades as u,
	                   crm_contactos as c
	              WHERE  u.contacto_id = c.contacto_id
	                     $where_concesionaria
	              group by u.modelo";
}
 

$modelos = array ( );

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer ( );
$worksheet = $workbook->addWorksheet ( "Reporte Autos" );
$worksheet->setLandscape ();

$worksheet->setMerge ( 0, 0, 0, 8 ); // Nombre de la empresa


$header_format = $workbook->addFormat ( array ('align' => 'center' ) ); // formato para los encabezados
$header_format->setBold ();
$header_format->setBgColor ( "gray" );
$header_format->setColor ( "white" );

$titles_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$titles_format->setSize ( 9 );
$titles_format->setBold ();

$normal_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$normal_format->setSize ( 8 );

//el ancho de las columnas
$worksheet->setColumn ( 0, 0, 20 ); // # Modelo del Auto Prospectado
$worksheet->setColumn ( 1, 1, 8 ); // Cantidad


$worksheet->write ( 0, 0, "REPORTE DE MODELOS DE AUTOS PROSPECTADOS \n" . $titulo, $header_format );
$worksheet->write ( 1, 0, "Modelo", $titles_format );
$worksheet->write ( 1, 1, "Cantidad", $titles_format );

$fila = 2;

foreach ($sql_con as $consultas => $consulta){
	$result = $db->sql_query ( $consulta ) or die ( $consulta );
    while ( list ( $modelo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
	  $worksheet->write ( $fila, 0, $modelo, $normal_format ); //modelo
	  $worksheet->write ( $fila, 1, $cuenta, $normal_format ); //modelo
	  $fila ++;
    }
}

$workbook->send ( 'reporteAutos.xls' );
$workbook->close ();
$workbook->sendFile ();

?>