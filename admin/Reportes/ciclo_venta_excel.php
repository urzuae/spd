<?
if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
	die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $cid, $unid,$fecha_ini, $fecha_fin,$gid;

$titulo = date ( "d-M-Y" );

if($gid){
	$where_gid = sprintf("AND LEFT( c.campana_id, %s ) = %s",strlen($gid), $gid);
}

if ($fecha_ini) {
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse ( $fecha_ini );
  $where_fecha .= " AND cl.timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse ( $fecha_fin );
  $where_fecha .= " AND cl.timestamp<'$fecha_fin 23:59:59'";
}

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer( );

$worksheet= $workbook->addWorksheet ( "Origen ");
$worksheet->setLandscape();
$worksheet->setMerge ( 0, 0, 0, 5 ); // Nombre de la empresa

$header_format = $workbook->addFormat( array ('align' => 'center' ) ); // formato para los encabezados
$header_format->setBold();
$header_format->setBgColor( "gray" );
$header_format->setColor( "white" );

$titles_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$titles_format->setSize ( 9 );
$titles_format->setBold ();

$worksheet->setColumn ( 1, 1, 12 ); // Cantidad
$worksheet->setColumn ( 0, 0, 18 ); // Zona

$worksheet->write ( 0, 0, sprintf ( "REPORTE CICLO DE VENTA (%s)", $titulo ), $header_format );
$worksheet->write ( 1, 0, "Ciclo", $titles_format );
$worksheet->write ( 1, 1, "Prospectos", $titles_format );

$fila = 2;

$sql = "SELECT right(c.campana_id,1) as id, count(cl.contacto_id) 
        FROM crm_campanas as c, 
             crm_campanas_llamadas as cl 
        WHERE c.campana_id = cl.campana_id 
              $where_fecha 
              $where_gid
        GROUP BY right(campana_id,1)";
$result = $db->sql_query($sql) or die($sql);
while(list($ciclo, $cuenta) = $db->sql_fetchrow($result)){
    $worksheet->write ( $fila, 1, $cuenta);//Prospectos
    $fila++;
}

$fila = 2;

$sql = "SELECT nombre FROM crm_campanas where campana_id = campana_id < 9 LIMIT 8";
$result = $db->sql_query($sql) or die($sql);
while(list($nombre) = $db->sql_fetchrow($result)){
	$worksheet->write ( $fila, 0, $nombre, $normal_format);//Campana
    $fila++;
}


$workbook->send ( "reporteCicloVenta.xls");
$workbook->close ();
$workbook->sendFile ();

?>