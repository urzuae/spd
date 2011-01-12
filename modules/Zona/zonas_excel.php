<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$uid,$cid,$unid;

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer ( );

$sql = "select zona_id from crm_zonas_gerentes where uid=$uid limit 1";
$result = $db->sql_query($sql) or die($sql);
list($zid) = $db->sql_fetchrow($result);

if(($cid && !$unid) || ($cid && $unid) ){
  $worksheetZonas = $workbook->addWorksheet ( "Reporte Distribuidora ".$cid );
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
$normal_format->setSize ( 8 );

$worksheetZonas->setColumn ( 1, 1, 8 ); // Cantidad

if($cid){
  $sql = "select name from groups where gid = $cid limit 1";
  $result = $db->sql_query($sql) or die($sql);
  list($grupo) = $db->sql_fetchrow($result);
  
  $worksheetZonas->setColumn ( 0, 0, 15 ); // Zona
  if($unid){
  	 $worksheetZonas->write ( 0, 0, sprintf("REPORTE CONCESIONARIA %s MODELO %s (%s)",$grupo,$unid,date("d-M-Y")), $header_format );
     $worksheetZonas->write ( 1, 0, "Ciclo", $titles_format);
  }
  else{
  	 $worksheetZonas->write ( 0, 0, sprintf("REPORTE CONCESIONARIA %s (%s)",$grupo,date("d-M-Y")), $header_format );
     $worksheetZonas->write ( 1, 0, "Modelo", $titles_format);
  }
  $worksheetZonas->write ( 1, 1, "Total", $titles_format);
}
else{
  $worksheetZonas->setColumn ( 0, 0, 40 ); // Zona
  $worksheetZonas->write ( 0, 0, "CONTACTOS POR CONCESIONARIA (".date("d-M-Y").")", $header_format );
  $worksheetZonas->write ( 1, 0, "Concesionaria", $titles_format);
  $worksheetZonas->write ( 1, 1, "Total", $titles_format);
}

$fila = 2;

if($cid && $unid){
	$sql2 = "SELECT nombre, campana_id FROM crm_campanas LIMIT 8";
    $result2 = $db->sql_query($sql2) or die($sql2);
    while(list($nombre,$campana_id) = $db->sql_fetchrow($result2)){
  	   $sql = "select count(c.contacto_id) from crm_prospectos_unidades as u, crm_contactos as c, crm_campanas_llamadas as ll where 
		   u.contacto_id = c.contacto_id and c.gid = $cid and ll.contacto_id = c.contacto_id and u.modelo = '$unid' and right( ll.campana_id, 1 ) = $campana_id group by u.modelo, ll.campana_id";
	   $result = $db->sql_query($sql) or die($sql);
	   list($cuenta) = $db->sql_fetchrow($result);
	   if(!$cuenta)
	      $cuenta = 0;
	   $worksheetZonas->write ( $fila, 0, $nombre, $normal_format);//zona
       $worksheetZonas->write ( $fila, 1, $cuenta);//cuenta
       $fila++;   
    }
}
elseif($cid && !$unid){
    $sql = "SELECT nombre FROM `crm_unidades`";
  	$result = $db->sql_query($sql) or die($sql);
	while(list($nombre) = $db->sql_fetchrow($result)){
      $sql2 = "select u.modelo, count(u.modelo) from crm_prospectos_unidades as u, crm_contactos as c where 
		u.contacto_id = c.contacto_id and c.gid = $cid and u.modelo = '$nombre' group by modelo";
	  $result2 = $db->sql_query($sql2) or die($sql2);
	  list($modelo, $cuenta) = $db->sql_fetchrow($result2);
	  if(!$cuenta)
	     $cuenta = 0;
      $worksheetZonas->write ( $fila, 0, $nombre, $normal_format);//zona
      $worksheetZonas->write ( $fila, 1, $cuenta);//cuenta
      $fila++;
	}
	/*
	$sql = "select u.modelo, count(u.modelo) from crm_prospectos_unidades as u, crm_contactos as c where 
		u.contacto_id = c.contacto_id and c.gid = $cid group by modelo";
	$result = $db->sql_query($sql) or die($sql);
	while(list($modelo, $cuenta) = $db->sql_fetchrow($result)){
	    $worksheetZonas->write ( $fila, 0, $modelo, $normal_format);//zona
        $worksheetZonas->write ( $fila, 1, $cuenta);//cuenta
        $fila++;
	}*/
}
else{
    $sql2 = "select g.name, g.gid from groups as g, groups_zonas as gz where g.gid = gz.gid and gz.zona_id = $zid";
    $result2 = $db->sql_query($sql2) or die($sql2);
    while(list($zona, $gid) = $db->sql_fetchrow($result2)){
	   $sql3 = "select count(contacto_id) from crm_contactos where gid = $gid";
	   $result3 = $db->sql_query($sql3) or die($sql3);
	   while(list($cuenta) = $db->sql_fetchrow($result3)){
			$worksheetZonas->write ( $fila, 0, $zona, $normal_format);//zona
            $worksheetZonas->write ( $fila, 1, $cuenta);//cuenta
            $fila++;
		}
	}
}

$workbook->send ( 'reporteZonas.xls' );
$workbook->close ();
$workbook->sendFile ();

?>