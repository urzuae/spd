<?
if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
	die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $cid, $unid,$fecha_ini,$fecha_fin, $gid;

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer( );

$titulo = date ( "d-M-Y" );

if($gid){
	$where_gid = "AND c.gid = $gid";
}

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

$worksheet= $workbook->addWorksheet ( "Origen ");
$worksheet->setLandscape();
$worksheet->setMerge ( 0, 0, 0, 8 ); // Nombre de la empresa

$header_format = $workbook->addFormat( array ('align' => 'center' ) ); // formato para los encabezados
$header_format->setBold();
$header_format->setBgColor( "gray" );
$header_format->setColor( "white" );

$titles_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$titles_format->setSize ( 9 );
$titles_format->setBold ();

$worksheet->setColumn ( 1, 1, 12 ); // Cantidad
$worksheet->setColumn ( 0, 0, 18 ); // Zona

$worksheet->write ( 0, 0, sprintf ( "REPORTE ORIGEN (%s)", $titulo ), $header_format );
$worksheet->write ( 1, 0, "Origen", $titles_format );
$worksheet->write ( 1, 1, "Prospectos", $titles_format );

$fila = 2;

$sql = "select o.nombre, count(c.contacto_id) from crm_contactos_origenes as o, crm_contactos as c where c.origen_id = o.origen_id $where_fecha $where_gid group by o.nombre";
$result = $db->sql_query($sql) or die($sql);
while(list($origen, $cuenta) = $db->sql_fetchrow($result)){
    $worksheet->write ( $fila, 0, $origen, $normal_format);//Campana
    $worksheet->write ( $fila, 1, $cuenta);//Prospectos
    $fila++;
}

$workbook->send ( "reporteOrigenes.xls");
$workbook->close ();
$workbook->sendFile ();

?>