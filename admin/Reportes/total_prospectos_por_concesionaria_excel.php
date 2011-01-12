<?
if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
	die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $gid, $fecha_ini, $fecha_fin;

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer( );

$titulo = date ( "d-M-Y" );

if ($fecha_ini) {
  $titulo = " desde $fecha_ini";
  $fecha_ini = date_reverse ( $fecha_ini );
  $where_fecha .= " AND co.fecha_importado>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse ( $fecha_fin );
  $where_fecha .= " AND co.fecha_importado<'$fecha_fin 23:59:59'";
}

if ($gid ) {
	$worksheet = $workbook->addWorksheet ( "Reporte Concesionaria " . $gid );
} else {
	$worksheet = $workbook->addWorksheet ("Reporte Ventas" );
}

$worksheet->setLandscape();

$worksheet->setMerge ( 0, 0, 0, 8 ); // Nombre de la empresa


$header_format = $workbook->addFormat( array ('align' => 'center' ) ); // formato para los encabezados
$header_format->setBold();
$header_format->setBgColor( "gray" );
$header_format->setColor( "white" );

$titles_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$titles_format->setSize ( 9 );
$titles_format->setBold ();

$normal_format = $workbook->addFormat ( array ('align' => 'left' ) ); // formato para los titulos
$normal_format->setSize ( 8 );

$worksheet->setColumn ( 1, 1, 10 ); // Cantidad

if ( $gid ) {
	$worksheet->setColumn ( 0, 0, 30 ); // Nombre
	$worksheet->write ( 0, 0, sprintf ( "REPORTE CONCESIONARIA %s (%s)", $gid, $titulo ), $header_format );
	$worksheet->write ( 1, 0, "Ciclo", $titles_format );
	$worksheet->write ( 1, 1, "Total", $titles_format );
} else {
	$worksheet->setColumn ( 0, 0, 12 ); // Zona
	$worksheet->write ( 0, 0, sprintf ( "Total de Prospectos por concesionaria (%s)", $titulo ), $header_format );
	$worksheet->write ( 1, 0, "Concesionaria", $titles_format );
	$worksheet->write ( 1, 1, "Total", $titles_format );
}

$fila = 2;

if ($gid) {
    $sql = "SELECT v.nombre, count(v.nombre) 
            FROM crm_campanas as c, 
                 crm_campanas_llamadas as l, 
                 crm_contactos AS co, 
                 crm_prospectos_ciclo_de_venta AS v 
            WHERE c.campana_id=l.campana_id AND 
                  l.contacto_id=co.contacto_id AND 
                  v.ciclo_de_venta_id=c.etapa_ciclo_id AND 
                  co.gid='$gid' 
                  $where_fecha
            GROUP BY (v.nombre) 
            ORDER BY c.etapa_ciclo_id ";
    $result = $db->sql_query($sql) or die($sql);
    while(list($origen, $cuenta) = $db->sql_fetchrow($result)){
        $worksheet->write ( $fila, 0, $origen, $normal_format);//Modelo
        $worksheet->write ( $fila, 1, $cuenta, $normal_format);//Prospectos
        $fila++;
    }
} else{

    //los que no estan finalizados
    $sql = "SELECT co.gid, 
		               COUNT(co.uid) 
		        FROM `crm_contactos` AS co 
		        WHERE 1
		              $where_fecha 
		        GROUP BY (co.gid)";
    $result = $db->sql_query($sql) or die($sql);
    $cuantos = array();
    while(list($gid, $cuenta) = $db->sql_fetchrow($result))
    {
        $cuantos[$gid] += $cuenta;
    }
    //los finalizados
    $sql = "SELECT co.gid, 
		               COUNT(co.uid) 
		        FROM `crm_contactos_finalizados` AS co 
		        WHERE 1
		              $where_fecha 
		        GROUP BY (co.gid)";
    $result = $db->sql_query($sql) or die($sql);

    while(list($gid, $cuenta) = $db->sql_fetchrow($result))
    {
        $cuantos[$gid] += $cuenta;
    }
	foreach ($cuantos AS $origen=>$cuenta) 
	{
        $worksheet->write ( $fila, 0, $origen, $normal_format);//Modelo
        $worksheet->write ( $fila, 1, $cuenta, $normal_format);//Prospectos
        $fila++;
    }
}

$workbook->send ( "reporteProspectos.xls" );
$workbook->close ();
$workbook->sendFile ();

?>