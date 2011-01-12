<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) 
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $rsort, $orderby, $sql, $result,$_site_name;
global $cant,$_module,$_op,$notassignedsdeassigneds,$notassignedsnews;
global $tmp_filtros,$filtro;
include_once($_includesdir."/Genera_Excel.php");
include_once("crea_filtro.php");
$filtro='';
$filtro_contacto='';
if( count($tmp_filtros) > 0)
{
    $filtro=" AND ".implode(" AND ",$tmp_filtros);
}
if( count($tmp_filtros_contactos) > 0)
{
    $filtro_contacto=" AND ".implode(" AND ",$tmp_filtros_contactos);
}
$array_retrasos=array();
$array_asignados=genera_asignados($db,$filtro,$filtro_contacto);
$array_retrasos=checa_retrasos($db,$filtro,$filtro_contacto);
$sql="SELECT b.gid,a.name,count(b.gid) as total FROM crm_contactos b LEFT JOIN groups_ubications a ON b.gid=a.gid  WHERE b.gid>0 ".$filtro." ".$filtro_contacto." GROUP by b.gid ORDER BY b.gid;";
$result = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
if($db->sql_numrows($result)> 0)
{
    $tabla_campanas .= "
            <table align='center' class='tablesorter'>
            <thead><tr>
            <th width='5%'>#</th>
            <th width='42%'>Distribuidor</th>
            <th width='8%'>No Prospectos</th>
            <th width='8%'>Prospectos Asignados</th>
            <th width='8%'>Porcentaje Asignaci&oacute;n</th>
            <th width='8%'>Hrs Retraso</th>
            <th width='8%'>Promedio de Hrs</th>
            <th width='8%'>Maximo hrs retraso</th>
            <th width='5%'>&nbsp;</th>
            </tr>
            </thead>";
    $total_fin=0;
    $asignado_fin=0;
    $hrs_total=0;
    $prosp_total=0;
    $hrs_max_total=0;
    while (list($gid, $name, $total) = $db->sql_fetchrow($result))
    {
        $tmp_gid=str_pad($gid, 4, "0", STR_PAD_LEFT);
        
        // Porcentaje de prospectos
        $porcentaje_asignados=0 + ( ($array_asignados[$tmp_gid] * 100) /$total);
        $porcentaje_asignados= number_format($porcentaje_asignados, 2, '.', ' ');
        $tabla_campanas .= "<tr class=\"row".($class_row++%2?"2":"1")."\" style=\"cursor:pointer;\">"
        ."<td>$gid</td>"
        ."<td align='left'>$name</td>"
        ."<td align='right'>$total</td>"
        ."<td align='right'>".($array_asignados[$tmp_gid] + 0)."</td>"
        ."<td align='right'>$porcentaje_asignados</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_retrasos[$tmp_gid]['hrs'] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_retrasos[$tmp_gid]['promedio'] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_retrasos[$tmp_gid]['maximo'] + 0)."</td>"
        ."<td align='center'><a href='index.php?_module=Monitoreo&_op=monitoreo_prospectos_asignados&gid=$tmp_gid$url'>Prospectos</a></td>"
        ."</tr>";       
        $total_fin     = 0 + $total_fin + $total;
        $asignado_fin  = 0 + $asignado_fin + $array_asignados[$tmp_gid];
        $prosp_total   = 0 + $prosp_total + $array_retrasos[$tmp_gid]['total'];
        $hrs_total     = 0 + $hrs_total + $array_retrasos[$tmp_gid]['hrs'];
        $prom_total    = 0 + $prom_total + $array_retrasos[$tmp_gid]['promedio'];

        if($array_retrasos[$tmp_gid]['maximo'] > $hrs_max_total )
            $hrs_max_total = $array_retrasos[$tmp_gid]['maximo'];
    }
    if($total_fin==0)
        $porcentaje_total=0;
    else
        $porcentaje_total=(($asignado_fin/$total_fin) *100);
        
    $tabla_campanas .= "<thead><tr>
                            <td>&nbsp;</td>
                            <td align='right'>Totales:</td>
                            <td align='right'>$total_fin</td>
                            <td align='right'>$asignado_fin</td>
                            <td align='right'>".number_format ($porcentaje_total, 2, '.', ' ')."</td>
                            <td align='right'>".brigeFormatDayToDayHour($hrs_total)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($hrs_total / $total_fin)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($hrs_max_total)."</td>
                            <td align='center'>&nbsp;</td>
                            </tr></thead></table>";
    $objeto = new Genera_Excel($tabla_campanas,'Asignacion-Concesionarias',$_site_name);
    $boton_excel=$objeto->Obten_href();
}
else
{
    $tabla_campanas= "<br><center>La consulta no arroja resultados</center><br>";
}
include_once("templateFiltros.php");


/** FUNCIONES AUXILIARES**/
function brigeFormatDayToDayHour($totaldays)
{
    $hoursForDay = 24;
    $days = "" +  ($totaldays/$hoursForDay);
    list($day,$decimalDay) = explode(".",$days);
    $decimalDay =  ((float)(".".$decimalDay))*$hoursForDay;
    return "$day d ".(int)$decimalDay." h";
}

function checa_retrasos($db,$filtro,$filtro_contacto)
{
    $date=date("Y-m-d H:i:s");
    $sql_ret="SELECT b.gid, count( b.gid ) as totales, sum( TIMESTAMPDIFF( HOUR , c.timestamp, '".$date."'  )) AS num_horas, avg( TIMESTAMPDIFF( HOUR , c.timestamp, '".$date."'  )) AS promedio, max( TIMESTAMPDIFF( HOUR , c.timestamp, '".$date."'  ) ) as maximo
                FROM crm_contactos b, unicos_log c
                WHERE b.gid > 0 AND b.uid = 0 AND b.contacto_id = c.contacto_id  ".$filtro_contacto." GROUP BY b.gid order by b.gid;";
    $res_ret=$db->sql_query($sql_ret);
    if($db->sql_numrows($res_ret) > 0)
    {
        while(list($gid,$total,$hrs,$promedio,$maximo)=$db->sql_fetchrow($res_ret))
        {
            $gid=str_pad($gid, 4, "0", STR_PAD_LEFT);
            $array_retrasos[$gid]['total']=$total;
            $array_retrasos[$gid]['hrs']=$hrs;
            $array_retrasos[$gid]['maximo']=$maximo;
            $array_retrasos[$gid]['promedio']=$promedio;
        }
    }
    return $array_retrasos;
}
/**
 * Funcion que sirve para sacar el total de asignados y con uid distinto de cero
 */
function genera_asignados($db,$filtro,$filtro_contacto)
{
    $sql_asignados = "SELECT a.gid,count(b.gid) as totales FROM groups_ubications a, crm_contactos b WHERE a.gid>0 and a.gid=b.gid AND b.uid != 0 ".$filtro." ".$filtro_contacto." GROUP by a.gid ORDER BY a.gid";
    $res_asignados = $db->sql_query($sql_asignados) or die("Error".print_r($db->sql_error()));
    if($db->sql_numrows($res_asignados) > 0)
    {
        while(list($gid,$totales)=$db->sql_fetchrow($res_asignados))
        {
            $tmp_gid=str_pad($gid, 4, "0", STR_PAD_LEFT);
            $array_asignados[$tmp_gid]=$totales;
        }
    }
    return $array_asignados;
}
?>