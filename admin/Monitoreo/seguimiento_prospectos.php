<?
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $rsort, $orderby, $sql, $result,$cant,$_module,$_op,$notassignedsdeassigneds,$notassignedsnews;
global $_dbhost,$_dbuname,$_dbpass,$_dbname,$_site_name;
include_once($_includesdir."/Genera_Excel.php");
include_once("crea_filtro.php");
include_once("formato_salida_datos.php");
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

$array_asignados=Regresa_asignados($db,$filtro,$filtro_contacto);

$sql="SELECT b.gid,a.name,count(b.gid) as total FROM crm_contactos b LEFT JOIN groups_ubications a ON b.gid=a.gid  WHERE b.gid >0  ".$filtro." ".$filtro_contacto." GROUP by b.gid ORDER BY b.gid;";
$result = $db->sql_query($sql);
if($db->sql_numrows($result)>0)
{
    $tabla_campanas .= "
            <table align='center' class='tablesorter'>
            <thead>
            <tr>
            <th rowspan=\"1\" width='3%' onmouseover=\"return escape('Id del Distribuidor')\">#</th>
            <th rowspan=\"1\" width='22%'>Distribuidor</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos')\">TP</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos Asignados')\">TPA</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos SIN Asignar')\">TPSA</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos en Compromiso')\">TPPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Total de Hrs de Prospectos en Compromiso')\">H PPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Promedio de horas de Prospectos en Compromiso')\">P PPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Maximo de horas de Prospectos en Compromiso')\">M PPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Total de Prospectos en Atención')\">TPPA</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Total de Hrs de Prospectos en Atención')\">H PPA</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Promedio de horas de Prospectos en Atención')\">P PPA</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Maximo de horas de Prospectos en Atención')\">M PPA</th>
            <th colspan=\"1\" width='2%'></th>
            <th colspan=\"1\" width='2%'></th>
            </tr>
            </thead>";
    $t_tp=0;
    $t_tpa=0;
    $t_tpsa=0;
    $t_tpes=0;
    $t_tpss=0;
    $t_trss=0;
    $t_thss=0;
    $maximo_t=0;
    $maximo_a=0;
    while(list($gid, $nombreConcesionaria, $totalContactos) = $db->sql_fetchrow($result))
    {
        $gid=str_pad($gid, 4, "0", STR_PAD_LEFT);

        $t_tp=$t_tp + $totalContactos;
        $tpa=$array_asignados[$gid] + 0;
        $t_tpa=$t_tpa + $tpa;
        $tpsa= ($totalContactos - $tpa);
        $t_tpsa=$t_tpsa +$tpsa;

        Crea_tabla_temporal($db,$gid,$filtro_contacto);
        $array_compromiso=Regresa_Prospectos_en_Seguimiento($db,$gid);
        $array_atencion  =Regresa_Prospectos_en_Atencion($db,$gid);
        Elimina_tabla_temporal($db);

        $tpss=($tpa - ($array_compromiso['pros_seguimiento'] + 0));
        $tabla_campanas .=  "<tr class=\"row".($class_row++%2?"2":"1")."\" style=\"cursor:pointer; height:32px;\">
        <td>".$gid."</td>
        <td>".$nombreConcesionaria."</td>
        <td align='right'>".$totalContactos."</td>
        <td align='right'>".$tpa."</td>
        <td align='right'>".$tpsa."</td>
        <td align='right'>".($array_compromiso['pros_seguimiento'] + 0)."</td>
        <td align='right'>".brigeFormatDayToDayHour($array_compromiso['hrs_cita_atrazada'] + 0)."</td>
        <td align='right'>".brigeFormatDayToDayHour($array_compromiso['prom_cita_atrazada'] + 0)."</td>
        <td align='right'>".brigeFormatDayToDayHour($array_compromiso['max_cita_atrazada'] + 0)."</td>
        <td align='right'>".$tpss."</td>
        <td align='right'>".brigeFormatDayToDayHour($array_atencion['total'])."</td>
        <td align='right'>".brigeFormatDayToDayHour($array_atencion['promedio'])."</td>
        <td align='right'>".brigeFormatDayToDayHour($array_atencion['maximo'])."</td>
        <td align='center'><a href='index.php?_module=Monitoreo&_op=seguimiento_prospectos_vendedor&asignados=$tpa&gid=$gid$url'>Ven</a></td>
        <td align='center'><a href='index.php?_module=Monitoreo&_op=seguimiento_concesionaria_prospectos&gid=$gid$url'>Pro</a></td>
        </tr>";
        $t_tpes  =$t_tpes   + $array_compromiso['pros_seguimiento'];
        $t_h_tpes=$t_h_tpes + $array_compromiso['hrs_cita_atrazada'];
        $t_p_tpes=$t_p_tpes + $array_compromiso['prom_cita_atrazada'];
        $t_m_tpes=$t_m_tpes + $array_compromiso['max_cita_atrazada'];
        $t_tpss  =$t_tpss   + $tpss;
        $t_thss  =$t_thss   + $array_atencion['total'];
        $t_trss  =$t_trss   + $array_atencion['promedio'];

        if($maximo_t < $array_compromiso['max_cita_atrazada'])
        $maximo_t = $array_compromiso['max_cita_atrazada'];
        if($maximo_a < $array_atencion['maximo'])
        $maximo_a = $array_atencion['maximo'];
    }
    $tabla_campanas .= "<thead><tr>
                            <td></td>
                            <td>Totales:</td>
                            <td align='right'>".$t_tp."</td>
                            <td align='right'>".$t_tpa."</td>
                            <td align='right'>".$t_tpsa."</td>
                            <td align='right'>".$t_tpes."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_h_tpes)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_p_tpes)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($maximo_t)."</td>
                            <td align='right'>".$t_tpss."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_thss)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_trss)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($maximo_a)."</td>
                            <td></td>
                            <td></td>
                            </tr></thead></table>";
    $tabla_campanas .= "Nomenclatura:&nbsp; <b>TP </b>&nbsp;Total de prospectos<br>
                        &nbsp;&nbsp;  <b>TPA</b>&nbsp;Total de prospectos asignados<br>
                        &nbsp;&nbsp;  <b>TPSA</b>&nbsp;Total de prospectos sin asignar<br>
                        &nbsp;&nbsp;  <b>TPPC</b>&nbsp;Total de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>H PPC</b>&nbsp;Horas de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>P PPC</b>&nbsp;Promedio de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>M PPC</b>&nbsp;Maximo de horas de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>TPPA</b>&nbsp;Total de prospectos en atencion<br>
                        &nbsp;&nbsp;  <b>H PPA</b>&nbsp;Horas de prospectos en atencion<br>
                        &nbsp;&nbsp;  <b>P PPA</b>&nbsp;Promedio de prospectos en atencion<br>
                        &nbsp;&nbsp;  <b>M PPA</b>&nbsp;Maximo de horas de prospectos en atencion";

    $report = new Genera_Excel($tabla_campanas,"Seguimiento-concesionaria", $_site_name);
    $reporteExcel = $report->Obten_href();
}
else
{
    $tabla_campanas= "<br><center>La consulta no arroja resultados</center><br>";
}
include_once("templateFiltros.php");
// Funciones Auxiliares para la etapa de seguimiento
function Crea_tabla_temporal($db,$gid,$filtro_contacto){
    $db->sql_query("DROP TABLE IF EXISTS tmp_c;");
    $db->sql_query("CREATE TABLE tmp_c as SELECT DISTINCT c.contacto_id,b.gid,b.uid,c.id,c.user_id,c.inicio,c.fin,c.fecha_cita,c.timestamp,0 as status,0000000000 as evento_id,0000000000 as cierre_id FROM crm_contactos b LEFT JOIN crm_campanas_llamadas c on b.contacto_id=c.contacto_id where b.gid=".$gid." ".$filtro_contacto.";");
}
function Elimina_tabla_temporal($db){
    $db->sql_query("DROP TABLE tmp_c;");
}
function Regresa_asignados($db,$filtro,$filtro_contacto){
    $array_asignados= array();
    $sql_a="SELECT b.gid,count(b.gid) as total FROM crm_contactos b LEFT JOIN groups_ubications a ON b.gid=a.gid  WHERE b.gid>0 AND b.uid != 0 ".$filtro." ".$filtro_contacto."  GROUP by b.gid ORDER BY b.gid ;";
    $res_a = $db->sql_query($sql_a);
    if($db->sql_numrows($res_a)>0)
    {
        while(list($gid,$total)=$db->sql_fetchrow($res_a))
        {
            $gid=str_pad($gid, 4, "0", STR_PAD_LEFT);
            $array_asignados[$gid]=$total;
        }
    }
    return $array_asignados;
}
function Regresa_Prospectos_en_Atencion($db,$gid){
    $array_atencion=array();
    $sql_atencion="SELECT SUM(TIMESTAMPDIFF(HOUR,a.timestamp,now())) as total,
                          AVG(TIMESTAMPDIFF(HOUR,a.timestamp,now())) AS promedio,
                          MAX(TIMESTAMPDIFF(HOUR,a.timestamp,now())) AS maximo
                    FROM unicos_log as a WHERE a.gid=".$gid." AND a.contacto_id IN (SELECT b.contacto_id FROM tmp_c b WHERE b.status=0);";
    $res_atencion=$db->sql_query($sql_atencion);
    if($db->sql_numrows($res_atencion)>0)
    {
        while(list($total,$promedio,$maximo) = $db->sql_fetchrow($res_atencion))
        {
            $array_atencion['total']=($total + 0);
            $array_atencion['promedio']=($promedio + 0);
            $array_atencion['maximo']=($maximo + 0);
        }
    }
    return $array_atencion;
}
function Regresa_Prospectos_en_Seguimiento($db,$gid){
    $array_compromiso=array();
    $upd_seguimiento    = "update tmp_c c set c.status=1 WHERE c.id in (select b.llamada_id from crm_campanas_llamadas_eventos b where b.uid=c.uid and c.gid=".$gid.");";
    $res_upd_seguimiento=$db->sql_query($upd_seguimiento);
    $no_prospectos_compromiso=$db->sql_affectedrows($res_upd_seguimiento);

    $upd_evento_id="update tmp_c c set c.evento_id=(select distinct b.evento_id FROM crm_campanas_llamadas_eventos b where b.uid=c.uid and c.id=b.llamada_id order by evento_id desc limit 1) WHERE c.status=1;";
    $db->sql_query($upd_evento_id);

    $upd_cierre ="update tmp_c c set c.cierre_id=(select distinct b.cierre_id FROM crm_campanas_llamadas_eventos_cierres b where b.uid=c.uid and c.evento_id=b.evento_id order by b.cierre_id desc limit 1) WHERE c.status=1;";
    $db->sql_query($upd_cierre);

    $udp_no_asignados   = "update tmp_c c set c.status=2 WHERE c.uid=0;";
    $db->sql_query($udp_no_asignados);

    $sql_compromiso="select COUNT(b.fecha_cita) as pros_cita_atrazada,SUM(TIMESTAMPDIFF(HOUR,b.fecha_cita,now())) as hrs_cita_atrazada,max(TIMESTAMPDIFF(HOUR,b.fecha_cita,now())) as max_cita_atrazada,avg(TIMESTAMPDIFF(HOUR,b.fecha_cita,now())) as prom_cita_atrazada from tmp_c b where b.gid=".$gid." and b.status=1 and b.cierre_id=0 and b.fecha_cita<=now() group by b.gid;";
    $res_compromiso=$db->sql_query($sql_compromiso);
    if($db->sql_numrows($res_compromiso) > 0)
    {
        $array_compromiso=$db->sql_fetchrow($res_compromiso);
    }
    $array_compromiso['pros_seguimiento']=$no_prospectos_compromiso;
    return $array_compromiso;
}
/*
// PROCESO UTILIZANDO STORE PROCEDURES
include_once 'genera_excel.php';
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $rsort, $orderby, $sql, $result,$cant,$_module,$_op,$notassignedsdeassigneds,$notassignedsnews;
global $_dbhost,$_dbuname,$_dbpass,$_dbname;
include_once("crea_filtro.php");
include_once("formato_salida_datos.php");
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

$array_asignados=Regresa_asignados($db,$filtro,$filtro_contacto);
$sql="SELECT b.gid,a.name,count(b.gid) as total FROM crm_contactos b LEFT JOIN groups_ubications a ON b.gid=a.gid  WHERE b.gid > 0 ".$filtro." ".$filtro_contacto." GROUP by b.gid ORDER BY b.gid;";
$result = $db->sql_query($sql);
if($db->sql_numrows($result)>0)
{
    $tabla_campanas .= "
            <table align='center' class='tablesorter'>
            <thead>
            <tr>
            <th rowspan=\"1\" width='3%' onmouseover=\"return escape('Id de la Concesionaria')\">#</th>
            <th rowspan=\"1\" width='22%'>Concesionaria</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos')\">TP</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos Asignados')\">TPA</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos SIN Asignar')\">TPSA</th>
            <th rowspan=\"1\" width='6%' onmouseover=\"return escape('Total de Prospectos en Compromiso')\">TPPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Total de Hrs de Prospectos en Compromiso')\">H PPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Promedio de horas de Prospectos en Compromiso')\">P PPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Maximo de horas de Prospectos en Compromiso')\">M PPC</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Total de Prospectos en Atención')\">TPPA</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Total de Hrs de Prospectos en Atención')\">H PPA</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Promedio de horas de Prospectos en Atención')\">P PPA</th>
            <th rowspan=\"1\" width='7%' onmouseover=\"return escape('Maximo de horas de Prospectos en Atención')\">M PPA</th>
            <th colspan=\"1\" width='2%'></th>
            <th colspan=\"1\" width='2%'></th>
            </tr>
            </thead>";
    $t_tp=0;
    $t_tpa=0;
    $t_tpsa=0;
    $t_tpes=0;
    $t_tpss=0;
    $t_trss=0;
    $t_thss=0;
    $maximo_t=0;
    $maximo_a=0;
    while(list($gid, $nombreConcesionaria, $totalContactos) = $db->sql_fetchrow($result))
    {
        $gid=str_pad($gid, 4, "0", STR_PAD_LEFT);

        $t_tp=$t_tp + $totalContactos;
        $tpa=$array_asignados[$gid] + 0;
        $t_tpa=$t_tpa + $tpa;
        $tpsa= ($totalContactos - $tpa);
        $t_tpsa=$t_tpsa +$tpsa;
        
        $datos_store=Ejecuta_Store($_dbhost,$_dbuname,$_dbpass,$_dbname,$gid);
        $tpss=($tpa - ($datos_store['total_com'] + 0));
        $tabla_campanas .=  "<tr class=\"row".($class_row++%2?"2":"1")."\" style=\"cursor:pointer; height:32px;\">
        <td>".$gid."</td>
        <td>".$nombreConcesionaria."</td>
        <td align='right'>".$totalContactos."</td>
        <td align='right'>".$tpa."</td>
        <td align='right'>".$tpsa."</td>
        <td align='right'>".($datos_store['total_com'] + 0)."</td>
        <td align='right'>".brigeFormatDayToDayHour($datos_store['hrs_com'] + 0)."</td>
        <td align='right'>".brigeFormatDayToDayHour($datos_store['prom_com'] + 0)."</td>
        <td align='right'>".brigeFormatDayToDayHour($datos_store['max_com'] + 0)."</td>
        <td align='right'>".$tpss."</td>
        <td align='right'>".brigeFormatDayToDayHour($datos_store['hrs_aten'])."</td>
        <td align='right'>".brigeFormatDayToDayHour($datos_store['prom_aten'])."</td>
        <td align='right'>".brigeFormatDayToDayHour($datos_store['max_aten'])."</td>
        <td align='center'><a href='index.php?_module=Monitoreo&_op=seguimiento_prospectos_vendedor&asignados=$tpa&gid=$gid$url'>Ven</a></td>
        <td align='center'><a href='index.php?_module=Monitoreo&_op=seguimiento_concesionaria_prospectos&gid=$gid$url'>Pro</a></td>
        </tr>";
        $t_tpes  =$t_tpes   + $datos_store['total_com'];
        $t_h_tpes=$t_h_tpes + $datos_store['hrs_com'];
        $t_p_tpes=$t_p_tpes + $datos_store['prom_com'];

        $t_tpss  =$t_tpss   + $tpss;
        $t_thss  =$t_thss   + $datos_store['hrs_aten'];
        $t_trss  =$t_trss   + $datos_store['prom_aten'];

        if($maximo_t < $datos_store['max_com'])
        $maximo_t = $datos_store['max_com'];
        if($maximo_a < $datos_store['max_aten'])
        $maximo_a = $datos_store['max_aten'];
    }
    $tabla_campanas .= "<thead><tr>
                            <td></td>
                            <td>Totales:</td>
                            <td align='right'>".$t_tp."</td>
                            <td align='right'>".$t_tpa."</td>
                            <td align='right'>".$t_tpsa."</td>
                            <td align='right'>".$t_tpes."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_h_tpes)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_p_tpes)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($maximo_t)."</td>
                            <td align='right'>".$t_tpss."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_thss)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($t_trss)."</td>
                            <td align='right'>".brigeFormatDayToDayHour($maximo_a)."</td>
                            <td></td>
                            <td></td>
                            </tr></thead></table>";
    $tabla_campanas .= "Nomenclatura:&nbsp; <b>TP </b>&nbsp;Total de prospectos<br>
                        &nbsp;&nbsp;  <b>TPA</b>&nbsp;Total de prospectos asignados<br>
                        &nbsp;&nbsp;  <b>TPSA</b>&nbsp;Total de prospectos sin asignar<br>
                        &nbsp;&nbsp;  <b>TPPC</b>&nbsp;Total de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>H PPC</b>&nbsp;Horas de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>P PPC</b>&nbsp;Promedio de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>M PPC</b>&nbsp;Maximo de horas de prospectos en compromiso<br>
                        &nbsp;&nbsp;  <b>TPPA</b>&nbsp;Total de prospectos en atencion<br>
                        &nbsp;&nbsp;  <b>H PPA</b>&nbsp;Horas de prospectos en atencion<br>
                        &nbsp;&nbsp;  <b>P PPA</b>&nbsp;Promedio de prospectos en atencion<br>
                        &nbsp;&nbsp;  <b>M PPA</b>&nbsp;Maximo de horas de prospectos en atencion";

    $report = new Genera_Excel($tabla_campanas,"reporteSeguimiento".date("d-m-Y"));
    $reporteExcel = $report->Obten_href();
}
else
{
    $tabla_campanas= "<br><center>La consulta no arroja resultados</center><br>";
}
include_once("templateFiltros.php");

// Funciones Auxiliares para la etapa de seguimiento
function Ejecuta_Store($_dbhost,$_dbuname,$_dbpass,$_dbname,$gid)
{
    $array=array();
    $linkTodb = mysqli_connect($_dbhost,$_dbuname,$_dbpass);
    if (mysqli_connect_errno())
    {
        echo "Error de Conexion";
        exit();
    }
    $conn = mysqli_select_db ($linkTodb,$_dbname) or die("Error de Base de Datos");
    $result = mysqli_query($linkTodb,'CALL Store2('.$gid.');') or die("Error en el procedimiento");
    $i=1;
    if(mysqli_num_rows($result) > 0)
    {
        $array = mysqli_fetch_array($result,MYSQLI_ASSOC);
    }
    return $array;
}
function Regresa_asignados($db,$filtro,$filtro_contacto){
    $array_asignados= array();
    $sql_a="SELECT b.gid,count(b.gid) as total FROM crm_contactos b LEFT JOIN groups_ubications a ON b.gid=a.gid  WHERE b.gid>0 AND b.uid != 0 ".$filtro." ".$filtro_contacto."  GROUP by b.gid ORDER BY b.gid ;";
    $res_a = $db->sql_query($sql_a);
    if($db->sql_numrows($res_a)>0)
    {
        while(list($gid,$total)=$db->sql_fetchrow($res_a))
        {
            $gid=str_pad($gid, 4, "0", STR_PAD_LEFT);
            $array_asignados[$gid]=$total;
        }
    }
    return $array_asignados;
}

*/
?>