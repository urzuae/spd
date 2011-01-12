<?

if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
    die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id;
global $_admin_menu2, $_admin_menu;

//	$where_fecha = "AND timestamp > '2008-02-11'";

if ($fecha_ini) {
    $l_excel = "&fecha_ini=$fecha_ini";
    $fecha_ini_o = $fecha_ini;
    $titulo = " desde $fecha_ini";
    $fecha_ini = date_reverse ( $fecha_ini );
    $where_fecha .= " AND timestamp >= '$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
    $l_excel .= "&fecha_fin=$fecha_fin";
    $fecha_fin_o = $fecha_fin;
    $titulo .= " hasta $fecha_fin";
    $fecha_fin = date_reverse ( $fecha_fin );
    $where_fecha .= " AND timestamp <= '$fecha_fin 23:59:59'";
}

if ($gid) {
    $sql1 = "select name from groups where gid = $gid limit 1";
    $result1 = $db->sql_query ( $sql1 ) or die ( $sql1 );
    list ( $grupo ) = $db->sql_fetchrow ( $result1 );
	$sql = "SELECT u.name,
	               count(distinct(v.contacto_id))
	        FROM crm_prospectos_ventas AS v,
	             users AS u
	        where v.uid=u.uid
	              $where_fecha AND
	              u.gid='$gid'
	        group by (v.uid)";    
    $result = $db->sql_query ( $sql ) or die ( $sql );
    $total = 0; //para contar cuantos contactos hay hast ahora
    while ( list ( $origen, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
        $tabla_contenido_ventas .= "<tr class=\"row" . (++ $rowclass % 2 ? "2" : "1") . "\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$origen&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
        $total = $total + $cuenta;
    }
    $tabla_contenido_ventas .= "<thead>
                                    <tr><th align='left'>Total :</th><th>$total</th></tr>
                                </thead>";


    $titulo = "Reporte Distribuidora $grupo";
    $tipo_reporte = "Vendedor";
    $link_excel = "total_ventas_por_distribuidora_excel&gid=$gid";
    $link_grap = "_por_vendedor";
    $gid_o = $gid;
} else {
    $totalVentas = 0;
    $sql = "select grupos.gid, grupos.name, count(distinct(ventas.contacto_id)) from groups as grupos,
                users as vendedor, crm_prospectos_ventas as ventas where grupos.gid=vendedor.gid and
                vendedor.uid=ventas.uid $where_fecha group by grupos.gid";
    $resultGetTotal = $db->sql_query($sql) or die("Se ha generado un error al obtener las ventas por concesionaria ->".$sql);
    while(list($gid, $nombreConcesionaria, $ventas) = $db->sql_fetchrow($resultGetTotal))
    {
        $urls = "index.php?_module=$_module&_op=$_op&gid=$gid&$l_excel";
        $tabla_contenido_ventas .= "<tr class=\"row" . (++ $rowclass % 2 ? "2" : "1") . "\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$gid</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$nombreConcesionaria</td>
                    <td>$ventas&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
        $totalVentas = $totalVentas + $ventas;
    }
    //ventas cuyo vendedor ha sido dado de baja
    $listDel = getSalesFromDel($db, $where_fecha);
    $tabla_contenido_ventas .= "<thead>
                                    <tr><th></th><th align='left'>Subtotal :</th><th>$totalVentas</th></tr>
                                </thead>
                                 <tbody>                                        
                                        ".$listDel["list"]."
                                        <tr><td></td><td align='left'></td><td align='right'></td></tr>
                                 </tbody>
                                 <thead>
                                    <tr><td></td><td align='left'>Subtotal</td><td align='right'>".$listDel["totalDel"]."</td></tr>
                                </thead>
                                 <tbody>                                       
                                        <tr><td></td><td align='left'></td><td align='right'></td></tr>
                                 </tbody>
                                 <thead>
                                    <tr><th></th><th align='left'>Total :</th><th>".($totalVentas + $listDel["totalDel"])."</th></tr>
                                </thead>";

    $titulo = "Ventas Por Distribuidora";
    $tipo_reporte = "Distribuidora";
    $tipo_reporte_inicio = "<td rowspan=\"1\">#</td>";
    $link_excel = "total_ventas_por_distribuidora_excel".$l_excel;
}
$contenido_ventas = "<table>
         <thead>
           <tr>
$tipo_reporte_inicio
             <td rowspan=\"1\">$tipo_reporte</td>
             <td rowspan=\"1\">Total</td>
          </tr>
         </thead>
$tabla_contenido_ventas
       </table>";
$fecha_fin = $fecha_fin_o;
$fecha_ini = $fecha_ini_o;

$graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_total_ventas_por_concesionaria$link_grap&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&gid=$gid_o\">";
$_html_b .= "<h1>Gráfica de Total de ventas por Distribuidora</h1><center>$graph</center>";

//obtiene las ventas de prospectos borrados
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
    $listDel = "";
    foreach($groups as $key => $value)    
        if($groups[$key]["total"] > 0)
            $listDel .= "<tr><td>$key</td><td align='left'>".$groups[$key]["nombre"]."</td><td align='right'>".$groups[$key]["total"]."</td></tr>";
    return array("list" => $listDel, "totalDel" => $totalglobal);
}
?>