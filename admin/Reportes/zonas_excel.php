<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db,$zid, $fecha_ini, $fecha_fin;

$zonas = array();

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer ( );

if(isset($zid)){
  $worksheetZonas = $workbook->addWorksheet ( "Reporte Zona ".$zid );
}
else{
  $worksheetZonas = $workbook->addWorksheet ( "Reporte Zonas" );
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
$normal_format->setColor("blue");
$normal_format->setSize ( 8 );

$worksheetZonas->setColumn ( 1, 1, 8 ); // Cantidad

if ($fecha_ini) {
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse ( $fecha_ini );
  $where_fecha .= " AND fecha_importado>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse ( $fecha_fin );
  $where_fecha .= " AND fecha_importado<'$fecha_fin 23:59:59'";
}

if(isset($zid)){
  $worksheetZonas->setColumn ( 0, 0, 40 ); // Zona
  $worksheetZonas->write ( 0, 0, "REPORTE POR ZONAS ".$titulo, $header_format );
  $worksheetZonas->write ( 1, 0, "Distribuidora", $titles_format);
  $worksheetZonas->write ( 1, 1, "Contactos", $titles_format);
}
else{
  $worksheetZonas->setColumn ( 0, 0, 20 ); // Zona
  $worksheetZonas->write ( 0, 0, "REPORTE ZONA ".$zid." ".$titulo, $header_format );
  $worksheetZonas->write ( 1, 0, "Zona", $titles_format);
  $worksheetZonas->write ( 1, 1, "Contactos", $titles_format);
}

$fila = 2;

if(isset($zid)){
	$sql2 = "select g.name, g.gid from groups as g, groups_zonas as gz where g.gid = gz.gid and gz.zona_id = $zid";
	$result2 = $db->sql_query($sql2) or die($sql2);
	while(list($zona, $gid) = $db->sql_fetchrow($result2)){
		$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid ".$where_fecha;
		$result3 = $db->sql_query($sql3) or die($sql3);
		while(list($cuenta) = $db->sql_fetchrow($result3)){
				$zonas[$gid] = $cuenta;
		}
		$worksheetZonas->write ( $fila, 0, $zona, $normal_format);//zona
        $worksheetZonas->write ( $fila, 1, $zonas[$gid]);//cuenta
        $fila++;
	}	
}
else{
  $sql = "select nombre, zona_id from crm_zonas";
  $result = $db->sql_query($sql) or die($sql);
  while(list($zona, $zona_id) = $db->sql_fetchrow($result)){
	$cuenta = 0;
	$sql2 = "select gid from groups_zonas where zona_id = $zona_id";
	$result2 = $db->sql_query($sql2) or die($sql2);
	while(list($gid) = $db->sql_fetchrow($result2)){
		$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid ".$where_fecha;
		$result3 = $db->sql_query($sql3) or die($sql3);
		while(list($c) = $db->sql_fetchrow($result3)){
			$cuenta = $cuenta + $c;
		}
	}
	$worksheetZonas->write ( $fila, 0, $zona, $normal_format);//zona
    $worksheetZonas->write ( $fila, 1, $cuenta);//cuenta
    $fila++;
  }
}

$workbook->send ( 'reporteZonas.xls' );
$workbook->close ();
$workbook->sendFile ();

?>