<?
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $tabla, $gid, $from, $campana_id, $uid, $orderby,$tmp_filtros,$order,$url, $asignados, $filtroPorVehiculo,$asignados,$_site_name;
include_once($_includesdir."/Genera_Excel.php");
include_once("regresa_filtros.php");
include_once("formato_salida_datos.php");
include_once ("crea_filtro_vehiculo.php");
include_once("templateFiltros.php");
$filtro='';
$filtro_contacto='';
$tabla='';
$filtro_vehiculo='';
if( count($tmp_filtros) > 0)
{
    $filtro=" AND ".implode(" AND ",$tmp_filtros);
}
if( count($tmp_filtros_contactos) > 0)
{
    $filtro_contacto=" AND ".implode(" AND ",$tmp_filtros_contactos);
}
if( count($filtroPorVehiculo) > 0)
{
    $filtro_vehiculo=" AND ".implode(" AND ",$filtroPorVehiculo)." AND b.contacto_id=m.contacto_id ";
    $tabla=', crm_prospectos_unidades m';
}

$vendedores=vendedores($db,$gid);
$sql="SELECT b.uid,count(b.uid) as total FROM crm_contactos b, groups_ubications a".$tabla." WHERE b.gid=".$gid." AND b.uid!=0 AND b.gid=a.gid ".$filtro." ".$filtro_contacto." ".$filtro_vehiculo." GROUP by b.uid ORDER BY b.uid;";
$res=$db->sql_query($sql) or die($sql);
$num=$db->sql_numrows($res);
if( $num > 0)
{
    $t_tp=0;
    $t_tpa=0;
    $t_tpsa=0;
    $t_tpes=0;
    $t_tpss=0;
    $class_row = 0;
    $total_porcentaje=0;
    $toteles_atencion=0;
    $maximo_comp=0;
    $maximo_aten=0;
    $tabla_vendedores.= '<table width="100%" border="0" class="tablesorter">
                <thead>
                    <tr>
                        <th>Vendedor</th>
                        <th>Asignados</th>
                        <th>% Asignados</th>
                        <th>P. Atencion</th>
                        <th>HRA</th>
                        <th>Prom. HRA</th>
                        <th>Max. HRA</th>
                        <th>P. Compromiso</th>
                        <th>HRC</th>
                        <th>Prom. HRC</th>
                        <th>Max. HRC</th>
                    </tr>
                </thead>
                <tbody>';
    while (list($uid,$total) = $db->sql_fetchrow($res))
    {
        $total_compromiso=0;

        $porcentajeContactosAsignados=0;
        if($asignados > 0)
            $porcentajeContactosAsignados=($total * 100) /$asignados;

        $total_porcentaje=$total_porcentaje + $porcentajeContactosAsignados;
        $porcentajeContactosAsignados=number_format($porcentajeContactosAsignados,2,'.','');
        
        Crear_tabla_Temporal($db,$gid,$uid,$filtro,$filtro_contacto,$tabla,$filtro_vehiculo);
        $array_compromiso=Regresa_Prospectos_en_Seguimiento($db,$gid,$uid);
        $array_atencion  =Regresa_Prospectos_en_Atencion($db,$gid,$uid);
        Elimina_tabla_temporal($db);

        if(($array_compromiso['pros_seguimiento'] + 0) <= 0)
            $array_compromiso['pros_seguimiento']=0;
            
        if(($array_compromiso['hrs_cita_atrazada'] + 0)<= 0)
            $array_compromiso['hrs_cita_atrazada'] = 0;

        
        $tabla_vendedores.= "<tr class=\"row".($class_row++%2?"2":"1")."\">"
        ."<td><a href='index.php?_module=Monitoreo&_op=seguimiento_prospectos_por_vendedor&uid=$uid&gid=$gid&$url'>".$vendedores[$uid]."</a></td>"
        ."<td align='right'>".$total."</td>"
        ."<td align='right'>".$porcentajeContactosAsignados."</td>"
        ."<td align='right'>".($array_atencion["pros_atencion"] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_atencion["total"] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_atencion["promedio"] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_atencion["maximo"] + 0)."</td>"
        ."<td align='right'>".($array_compromiso['pros_seguimiento'] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_compromiso['hrs_cita_atrazada']  + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_compromiso['prom_cita_atrazada'] + 0)."</td>"
        ."<td align='right'>".brigeFormatDayToDayHour($array_compromiso['max_cita_atrazada']  + 0)."</td>"
        ."</tr>";
        $totales_asignados=$totales_asignados + $total;
        $toteles_atencion =$toteles_atencion + ($array_atencion["pros_atencion"] + 0);

        $t_tpes  =$t_tpes   + $array_compromiso['pros_seguimiento'] + 0;
        $t_h_tpes=$t_h_tpes + $array_compromiso['hrs_cita_atrazada'] + 0;
        $t_p_tpes=$t_p_tpes + $array_compromiso['prom_cita_atrazada'] + 0;
        $t_m_tpes=$t_m_tpes + $array_compromiso['max_cita_atrazada'];

        $t_tpss  =$t_tpss   + $tpss;
        $t_h_tpss=$t_h_tpss + $array_atencion['total'];
        $t_p_tpss=$t_p_tpss + $array_atencion['promedio'];
        $t_m_tpss=$t_m_tpss + $array_atencion['maximo'];

        if($maximo_aten <= $array_atencion['maximo'])
            $maximo_aten=$array_atencion['maximo'];

        if($maximo_comp <= $array_compromiso['max_cita_atrazada'])
            $maximo_comp=$array_compromiso['max_cita_atrazada'];

    }
    $tabla_vendedores.= "</tbody>
            <thead>
            <tr>
            <td>Totales ".$num." vendedores</td>
            <td align='right'>".$totales_asignados."</td>
            <td align='right'>".number_format($total_porcentaje,2,'.','')."</td>
            <td align='right'>".$toteles_atencion."</td>
            <td align='right'>".brigeFormatDayToDayHour($t_h_tpss)."</td>
            <td align='right'>".brigeFormatDayToDayHour($t_p_tpss)."</td>
            <td align='right'>".brigeFormatDayToDayHour($maximo_aten)."</td>
            <td align='right'>".$t_tpes."</td>
            <td align='right'>".brigeFormatDayToDayHour($t_h_tpes)."</td>
            <td align='right'>".brigeFormatDayToDayHour($t_p_tpes)."</td>
            <td align='right'>".brigeFormatDayToDayHour($maximo_comp)."</td>
            </tr>
            </thead>
            </tr></table>";
    
    $report = new Genera_Excel($tabla_vendedores,"Seguimiento_Vendedores", $_site_name);
    $reporteExcel = $report->Obten_href();
    if(!$class_row)
        $tabla_vendedores= "<br>Los prospectos no estan asignados a ning&uacute;n Vendedor<br>";
    require_once("templateVehiculo.php");
}

/***** funciones auxiliares para el calculo del reporte**/
function Crear_tabla_Temporal($db,$gid,$uid,$filtro,$filtro_contacto,$tabla,$filtro_vehiculo)
{
    $db->sql_query("DROP TABLE IF EXISTS temporal_v;");
    $db->sql_query("CREATE TABLE temporal_v as SELECT DISTINCT c.contacto_id,b.gid,b.uid,c.id,c.user_id,c.inicio,c.fin,c.fecha_cita,0 as status,0000000000 as evento_id,0000000000 as cierre_id FROM crm_contactos b,crm_campanas_llamadas c".$tabla." WHERE b.contacto_id=c.contacto_id AND b.uid=".$uid." ".$filtro." ".$filtro_contacto." ".$filtro_vehiculo.";");
}

function Regresa_Prospectos_en_Seguimiento($db,$gid,$uid)
{
    $array_compromiso=array();
    $upd_seguimiento    = "update temporal_v as c set c.status=1 WHERE c.id in (select b.llamada_id from crm_campanas_llamadas_eventos b where b.uid=c.uid and c.gid=".$gid." and c.uid=".$uid.");";
    $res_upd_seguimiento=$db->sql_query($upd_seguimiento);
    $no_prospectos_compromiso=$db->sql_affectedrows($res_upd_seguimiento);

    $upd_evento_id="update temporal_v as c set c.evento_id=(select distinct b.evento_id FROM crm_campanas_llamadas_eventos b where b.uid=c.uid and c.id=b.llamada_id order by evento_id desc limit 1) WHERE c.status=1;";
    $db->sql_query($upd_evento_id);

    $upd_cierre   ="update temporal_v as c set c.cierre_id=(select distinct b.cierre_id FROM crm_campanas_llamadas_eventos_cierres b where b.uid=c.uid and c.evento_id=b.evento_id order by b.cierre_id desc limit 1) WHERE c.status=1;";
    $db->sql_query($upd_cierre);

    $udp_no_asignados = "update temporal_v c set c.status=2 WHERE c.uid=0;";
    $db->sql_query($udp_no_asignados);

    $sql_compromiso="select COUNT(b.fecha_cita) as pros_cita_atrazada,SUM(TIMESTAMPDIFF(HOUR,b.fecha_cita,now())) as hrs_cita_atrazada,max(TIMESTAMPDIFF(HOUR,b.fecha_cita,now())) as max_cita_atrazada,avg(TIMESTAMPDIFF(HOUR,b.fecha_cita,now())) as prom_cita_atrazada from temporal_v b where b.gid=".$gid." and b.uid=".$uid." and b.status=1 and b.cierre_id=0 and b.fecha_cita<=now() group by b.uid;";
    $res_compromiso=$db->sql_query($sql_compromiso);
    if($db->sql_numrows($res_compromiso) > 0)
    {
        $array_compromiso=$db->sql_fetchrow($res_compromiso);
    }
    $array_compromiso['pros_seguimiento']=$no_prospectos_compromiso;
    return $array_compromiso;
}

function Elimina_tabla_temporal($db)
{
    $db->sql_query("DROP TABLE temporal_v;");
}

function Regresa_Prospectos_en_Atencion($db,$gid,$uid)
{
    $array=array();
    $sql_asi="SELECT COUNT(a.timestamp) AS pros_atencion,SUM(TIMESTAMPDIFF(HOUR,a.timestamp,now())) as total,
                          AVG(TIMESTAMPDIFF(HOUR,a.timestamp,now())) AS promedio,
                          MAX(TIMESTAMPDIFF(HOUR,a.timestamp,now())) AS maximo
                    FROM unicos_log as a WHERE a.gid=".$gid." AND a.uid=".$uid." AND a.contacto_id IN (SELECT b.contacto_id FROM temporal_v b WHERE b.status=0);";
    $res_asi=$db->sql_query($sql_asi);
    if($db->sql_numrows($res_asi)>0)
    {
        $array=$db->sql_fetchrow($res_asi);
    }
    return $array;
}
function vendedores($db,$gid)
{
    $sql_tmp="SELECT uid,name from users WHERE gid='".$gid."' ORDER BY uid;";
    $res_tmp=$db->sql_query($sql_tmp) or die($sql_tmp);
    if($db->sql_numrows($res_tmp) > 0)
    {
        while($rs=$db->sql_fetchrow($res_tmp))
        $array_tmp[$rs['uid']]=$rs['name'];
    }
    return $array_tmp;
}
?>