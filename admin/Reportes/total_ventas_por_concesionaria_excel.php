<?
if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
    die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $gid, $fecha_ini, $fecha_fin;

$titulo = date ( "d-M-Y" );



if ($fecha_ini) {
    $titulo = " desde $fecha_ini";
    $fecha_ini = date_reverse ( $fecha_ini );
    $where_fecha .= " AND timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
    $titulo .= " hasta $fecha_fin";
    $fecha_fin = date_reverse ( $fecha_fin );
    $where_fecha .= " AND timestamp<'$fecha_fin 23:59:59'";
}

require_once 'Spreadsheet/Excel/Writer.php';
$workbook = new Spreadsheet_Excel_Writer ( );

if ($gid) {
    $worksheet = $workbook->addWorksheet ( "Reporte Distribuidora " . $gid );
} else {
    $worksheet = $workbook->addWorksheet ( "Reporte Ventas" );
}

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

if ($gid) {
    $sql1 = "select name from groups where gid = $gid limit 1";
    $result1 = $db->sql_query ( $sql1 ) or die ( $sql1 );
    list ( $grupo ) = $db->sql_fetchrow ( $result1 );
    $worksheet->setColumn ( 0, 0, 15 ); // Nombre
    $worksheet->setColumn ( 1, 1, 10 ); // Cantidad
    $worksheet->write ( 0, 0, sprintf ( "REPORTE DISTRIBUIDORA %s %s (%s)", $gid, $grupo, $titulo ), $header_format );
    $worksheet->write ( 1, 0, "Nombre", $titles_format );
    $worksheet->write ( 1, 1, "Total", $titles_format );
    $reporte = "Distribuidora";
} else {
    $worksheet->setColumn ( 0, 0, 5 ); // Zona
    $worksheet->setColumn ( 1, 1, 40 ); // Nombre
    $worksheet->write ( 0, 0, sprintf ( "REPORTE VENTAS POR DISTRIBUIDORA (%s)", $titulo ), $header_format );
    $worksheet->write ( 1, 0, "#", $titles_format );
    $worksheet->write ( 1, 1, "Distribuidora", $titles_format );
    $worksheet->write ( 1, 2, "Total", $titles_format );
    $reporte = "Modelo";
}

$fila = 2;

if ($gid) {
    /*
    $sql = "SELECT u.name,
                   count(v.uid)
            FROM crm_prospectos_ventas AS v,
                 users AS u
            where v.uid=u.uid
    $where_fecha AND
                  u.gid='$gid'
            group by (v.uid)";*/
    $sql = "SELECT u.name,
                   count(distinct(v.contacto_id))
            FROM crm_prospectos_ventas AS v,
                 users AS u
            where v.uid=u.uid
    $where_fecha AND
                  u.gid='$gid'
            group by (v.uid)";

    $result = $db->sql_query ( $sql ) or die ( $sql );
    $total = 0;
    while ( list ( $origen, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
        $worksheet->write ( $fila, 0, $origen, $normal_format ); //Modelo
        $worksheet->write ( $fila, 1, $cuenta, $normal_format ); //Prospectos
        $total = $total + $cuenta;
        $fila ++;
    }
    $fila++;
    $worksheet->write ( $fila, 0, "Total", $normal_format ); //Prospectos
    $worksheet->write ( $fila, 1, $total, $normal_format ); //Prospectos
} else {
    $totalVentas = 0;
    $sql = "select grupos.gid, grupos.name, count(distinct(ventas.contacto_id)) from groups as grupos,
                users as vendedor, crm_prospectos_ventas as ventas where grupos.gid=vendedor.gid and
                vendedor.uid=ventas.uid $where_fecha group by grupos.gid";

    $resultGetTotal = $db->sql_query($sql) or die("Se ha generado un error al obtener las ventas por distribuidora ->".$sql);
    while(list($gid, $nombreConcesionaria, $ventas) = $db->sql_fetchrow($resultGetTotal))
    {
        $worksheet->write ( $fila, 0, $gid, $normal_format ); //id
        $worksheet->write ( $fila, 1, $nombreConcesionaria, $normal_format ); //nombre
        $worksheet->write ( $fila, 2, $ventas, $normal_format ); //id
        $totalVentas = $totalVentas + $ventas;
        $fila++;
    }
    //ventas cuyo vendedor ha sido dado de baja    
    $worksheet->write ( $fila, 1, "SubTotal", $normal_format ); //Prospectos
    $worksheet->write ( $fila, 2, $totalVentas, $normal_format ); //Prospectos
    $fila++;
    $listDel = getSalesFromDel($db, $where_fecha);
    $groups = $listDel["list"];
    foreach($groups as $key => $value)
    {
        if($groups[$key]["total"] > 0)
        {
            $worksheet->write ( $fila, 0, $key, $normal_format ); //id
            $worksheet->write ( $fila, 1, $groups[$key]["nombre"], $normal_format ); //nombre
            $worksheet->write ( $fila, 2, $groups[$key]["total"], $normal_format ); //total
            $fila++;
        }
    }

    $worksheet->write ( $fila, 1, "Subtotal", $normal_format ); //Prospectos
    $worksheet->write ( $fila, 2, $listDel["totalDel"], $normal_format ); //Prospectos
    $fila++;
    $worksheet->write ( $fila, 1, "Total", $normal_format ); //Prospectos
    $worksheet->write ( $fila, 2, ($listDel["totalDel"] + $totalVentas), $normal_format ); //Prospectos
}

$workbook->send ( sprintf ( "reporte%s.xls", $reporte ) );
$workbook->close ();
$workbook->sendFile ();


function getSalesFromDel($db, $whereFecha)
{
    $groups = array();
    $sqlGetGroups = "select gid, name from groups";
    $resultGetGroups = $db->sql_query($sqlGetGroups) or die("Error al  obtener los grupos");
    while(list($gid,$name) = $db->sql_fetchrow($resultGetGroups))
    $groups[$gid] = array("nombre" => $name, "total" => 0);
    $totalglobal = 0;
    $sqlWithDeleteSales = "select distinct(ventas.contacto_id), gid  from crm_prospectos_ventas as ventas
    left join users as vendedores on ventas.uid=vendedores.uid where vendedores.uid is null $whereFecha";
    $resultDeleteSales = $db->sql_query($sqlWithDeleteSales) or die("Error al obtener las ventas con vendedores dados de baja");
    while(list($contactoId) = $db->sql_fetchrow($resultDeleteSales))
    {
        $sqlRecoveryGid = "select to_uid,to_gid from crm_contactos_asignacion_log where contacto_id='$contactoId'  and to_gid <> 0 order by timestamp desc limit 1";
        $resulRecoveryGid = $db->sql_query($sqlRecoveryGid) or die("Error al obtener el contacto de los logs");
        list($toUid, $toGid) = $db->sql_fetchrow($resulRecoveryGid);
        if(array_key_exists($toGid, $groups))
        {
            $groups[$toGid]["total"] = $groups[$toGid]["total"] + 1;
            $totalglobal++;
        }
        //else
    }
    return array("list" => $groups, "totalDel" => $totalglobal);
    /*
    $listDel = "";
    foreach($groups as $key => $value)
    if($groups[$key]["total"] > 0)
    $listDel .= "<tr><td>$key</td><td align='left'>".$groups[$key]["nombre"]."</td><td align='right'>".$groups[$key]["total"]."</td></tr>";
    return array("list" => $listDel, "totalDel" => $totalglobal);
     *
     */
}
?>