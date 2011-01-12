<?

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}
global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno,
 $submit, $status_id, $ciclo_de_venta_id, $uid, $gid, $rsort, $open, $orderby, $uid_, $_module,
 $_op, $url, $filtro, $leyenda_filtros, $tmp_filtros,$_site_name;

include_once($_includesdir."/Genera_Excel.php");
include_once("regresa_filtros.php");
include_once("formato_salida_datos.php");
include_once("crea_filtro_vehiculo.php");

$filtro = '';
$filtro_contacto = '';
$filtro_vehiculo = '';
if (count($tmp_filtros) > 0) {
    $filtro = " AND " . implode(" AND ", $tmp_filtros);
}
if (count($tmp_filtros_contactos) > 0) {
    $filtro_contacto = " AND " . implode(" AND ", $tmp_filtros_contactos);
}
if (count($filtroPorVehiculo) > 0) {
    $filtro_vehiculo = " AND " . implode(" AND ", $filtroPorVehiculo) . " ";
}
$vendedores = vendedores($db, $gid);
$origenes = Origenes($db);
$unidades = Unidades($db);
$campanas = Campanas($db, $gid);



$sql = "SELECT b.contacto_id,b.origen_id,concat(b.nombre,' ',b.apellido_paterno,' ',b.apellido_materno) as prospecto,b.fecha_importado,m.modelo_id FROM crm_contactos b, crm_prospectos_unidades m  WHERE b.gid=" . $gid . " AND b.contacto_id=m.contacto_id  " . $filtro_contacto . "  " . $filtro_vehiculo . " GROUP by b.contacto_id ORDER BY b.contacto_id;";
$res = $db->sql_query($sql) or die($sql);
$num = $db->sql_numrows($res);
if ($num > 0) {
    $total_hrs_compromiso = 0;
    $total_hrs_atencion = 0;
    $tabla_campanas .= "<table class=\"tablesorter\" width='100%' align='center'>
                        <thead>
                        <tr>
                        <th>Fuente</th>
                        <th>Nombre</th>
                        <th>HRA</th>
                        <th>HRC</th>
                        <th>Primer contacto</th>
                        <th>Ultimo contacto</th>
                        <th>Producto</th>
                        </tr></thead><tbody>";
    while (list($contacto_id, $origen_id, $prospecto, $fecha_importado, $modelo_id) = $db->sql_fetchrow($res)) {
        $array_compromiso = Regresa_Prospectos_en_Seguimiento($db, $contacto_id);
        $array_atencion = Regresa_Prospectos_en_Atencion($db, $contacto_id);

        $campaignId = $campanas[$contacto_id]['llamada_id'];
        $campana = $campanas[$contacto_id]['campana_id'];
        $tabla_campanas .= "<tr class=\"row" . (($c++ % 2) + 1) . "\">"
                . "<td>" . $origenes[$origen_id] . "</td>"
                . "<td><a href=\"index.php?_module=$_module&_op=llamada_ro&llamada_id=$campaignId&contacto_id=$contacto_id&campana_id=$campana\">$prospecto</a></td>"
                . "<td>" . brigeFormatDayToDayHour($array_atencion["hrs_cita_atencion"] + 0) . "</td>"
                . "<td>" . brigeFormatDayToDayHour($array_compromiso['hrs_cita_atrazada'] + 0) . "</td>"
                . "<td>" . $fecha_importado . "</td>"
                . "<td>" . Regresa_ultimo_log($db, $contacto_id) . "</td>"
                . "<td>" . $unidades[$modelo_id] . "</td>"
                . "</tr>";
        $total_hrs_compromiso = $total_hrs_compromiso + $array_compromiso['hrs_cita_atrazada'] + 0;
        $total_hrs_atencion = $total_hrs_atencion + $array_atencion["hrs_cita_atencion"] + 0;
    }
    $tabla_campanas .= "</tbody><thead><tr>
                <td>Total prospectos:</td>
                <td>" . $num . "</td>
                <td>" . brigeFormatDayToDayHour($total_hrs_atencion) . "</td>
                <td>" . brigeFormatDayToDayHour($total_hrs_compromiso) . "</td>
                <td></td><td></td><td></td>
                </tr></thead></tr></table>";
    $report = new Genera_Excel($tabla_campanas, "Seguimiento_Prospectos", $_site_name);
    $reporteExcel = $report->Obten_href();
    require_once("templateVehiculo.php");
}

function Regresa_Prospectos_en_Atencion($db, $contacto_id) {
    $array = array();
    $sql_asi = "select TIMESTAMPDIFF(HOUR,a.timestamp,now()) as hrs_cita_atencion
              FROM tmp_p b,unicos_log a
              WHERE a.contacto_id=" . $contacto_id . " and b.status=0 and a.contacto_id=b.contacto_id;";
    $res_asi = $db->sql_query($sql_asi);
    if ($db->sql_numrows($res_asi) > 0) {
        $array = $db->sql_fetchrow($res_asi);
    }
    return $array;
}

function Regresa_ultimo_log($db, $contacto_id) {
    $fecha = '0000-00-00 00:00:00';
    $sql_log = "SELECT timestamp FROM unicos_log WHERE contacto_id=" . $contacto_id . ";";
    $res_log = $db->sql_query($sql_log);
    if ($db->sql_numrows($res_log) > 0) {
        $fecha = $db->sql_fetchfield(0, 0, $res_log);
    }
    return $fecha;
}

function Regresa_Prospectos_en_Seguimiento($db, $contacto_id) {
    $array_compromiso = array();
    $sql_d = "DROP TABLE IF EXISTS tmp_p;";
    $res_d = $db->sql_query($sql_d);
    $sql_c = "CREATE TABLE tmp_p as SELECT DISTINCT c.contacto_id,b.gid,b.uid,c.id,c.user_id,c.fecha_cita,0 as status,0000000000 as evento_id,0000000000 as cierre_id FROM crm_contactos b,crm_campanas_llamadas c WHERE b.contacto_id=" . $contacto_id . " AND b.contacto_id=c.contacto_id ;";
    $res_c = $db->sql_query($sql_c);
    $upd_seguimiento = "update tmp_p c set c.status=1 WHERE c.id in (select b.llamada_id from crm_campanas_llamadas_eventos b where b.uid=c.uid and c.contacto_id=" . $contacto_id . ");";
    $res_upd_seguimiento = $db->sql_query($upd_seguimiento);
    $no_prospectos_compromiso = $db->sql_affectedrows($res_upd_seguimiento);
    $upd_evento_id = "update tmp_p c set c.evento_id=(select distinct b.evento_id FROM crm_campanas_llamadas_eventos b where b.uid=c.uid and c.id=b.llamada_id order by evento_id desc limit 1) WHERE c.status=1;";
    $db->sql_query($upd_evento_id);
    $upd_cierre = "update tmp_p c set c.cierre_id=(select distinct b.cierre_id FROM crm_campanas_llamadas_eventos_cierres b where b.uid=c.uid and c.evento_id=b.evento_id order by b.cierre_id desc limit 1) WHERE c.status=1;";
    $db->sql_query($upd_cierre);
    $udp_no_asignados = "update tmp_p c set c.status=2 WHERE c.uid=0;";
    $db->sql_query($udp_no_asignados);
    $sql_compromiso = "select b.fecha_cita as cita_atrazada,TIMESTAMPDIFF(HOUR,b.fecha_cita,now()) as hrs_cita_atrazada from tmp_p b where b.contacto_id=" . $contacto_id . " and b.status=1 and b.cierre_id=0 and b.fecha_cita<=now();";
    $res_compromiso = $db->sql_query($sql_compromiso);
    if ($db->sql_numrows($res_compromiso) > 0) {
        $array_compromiso = $db->sql_fetchrow($res_compromiso);
    }
    return $array_compromiso;
}

function vendedores($db, $gid) {
    $sql_tmp = "SELECT uid,name from users WHERE gid='" . $gid . "' ORDER BY uid;";
    $res_tmp = $db->sql_query($sql_tmp) or die($sql_tmp);
    if ($db->sql_numrows($res_tmp) > 0) {
        while ($rs = $db->sql_fetchrow($res_tmp))
            $array_tmp[$rs['uid']] = $rs['name'];
    }
    return $array_tmp;
}

function Origenes($db) {
    $sql_tmp = "SELECT fuente_id,nombre from crm_fuentes  ORDER BY fuente_id;";
    $res_tmp = $db->sql_query($sql_tmp) or die($sql_tmp);
    if ($db->sql_numrows($res_tmp) > 0) {
        while ($rs = $db->sql_fetchrow($res_tmp)) {
            $array_tmp[$rs['fuente_id']] = $rs['nombre'];
        }
    }
    return $array_tmp;
}

function Unidades($db) {
    $sql = "SELECT unidad_id, nombre FROM crm_unidades";
    $r = $db->sql_query($sql) or die($sql);
    $unidades = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
        $unidades[$id] = $n;
    return $unidades;
}

function Campanas($db, $gid) {
    $gid = $gid + 0;
    $array_campanas = array();
    $sql_cam = "SELECT id,campana_id,contacto_id FROM crm_campanas_llamadas WHERE campana_id like '" . $gid . "%' ORDER BY contacto_id;";
    $res_cam = $db->sql_query($sql_cam);
    if ($db->sql_numrows($res_cam) > 0) {
        while (list($id, $campana_id, $contacto_id) = $db->sql_fetchrow($res_cam)) {
            $array_campanas[$contacto_id]['llamada_id'] = $id;
            $array_campanas[$contacto_id]['campana_id'] = $campana_id;
        }
        return $array_campanas;
    }
}

?>