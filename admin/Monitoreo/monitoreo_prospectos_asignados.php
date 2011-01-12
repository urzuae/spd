<?

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}
global $db, $gid, $uid, $url,$_site_name;
include_once($_includesdir."/Genera_Excel.php");
include_once("regresa_filtros.php");
include_once ("crea_filtro_vehiculo.php");
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
// saco totales de origenes padre, de vendedores y de campanas por el gid
$name_group = regresa_concesionaria($db, $gid);
$array_origenes = regresa_origenes($db);
$array_vendedores = regresa_vendedor($db, $gid);
$array_campanas = regresa_campanas($db, $gid);

$sql_contactos = "SELECT b.contacto_id, concat(b.nombre, ' ', b.apellido_paterno, ' ', b.apellido_materno ) AS prospecto, b.origen_id, b.fecha_importado, b.uid,m.modelo_id,m.modelo
    FROM crm_contactos b,crm_prospectos_unidades m
    WHERE b.gid=" . $gid . " AND b.contacto_id=m.contacto_id " . $filtro_contacto . " " . $filtro_vehiculo . ";";
$res_contactos = $db->sql_query($sql_contactos) or die("Error" . $sql_contactos);
$num = $db->sql_numrows($res_contactos);
if ($num > 0) {
    $total_hrs_sin_atencion = 0;
    $tabla_campanas = "<table align='center' class='tablesorter' width='100%'>"
            . "<thead>"
            . "<tr>"
            . "<th>Campa&ntilde;a</th>"
            . "<th>Nombre</th>"
            . "<th>Vendedor</th>"
            . "<th>Fecha de importaci&oacute;n</th>"
            . "<th>Fecha de asignacion</th>"
            . "<th>Hrs de Retraso</th>"
            . "<th>Producto</th>"
            . "</tr></thead>";
    while (list($contacto_id, $prospecto, $origen_id, $fecha_importado, $uid, $modelo_id, $modelo) = $db->sql_fetchrow($res_contactos)) {
        $array_retraso = calcula($db, $contacto_id, $uid);
        $total_hrs_sin_atencion = $total_hrs_sin_atencion + ($array_retraso['num_horas'] + 0);
        $tabla_campanas .= "
                <tr class=\"row" . ($class_row++ % 2 ? "2" : "1") . "\" style=\"cursor:pointer;\">"
                . "<td>" . $array_origenes[$origen_id] . "</td>"
                . "<td align='left'><a href=\"index.php?_module=$_module&_op=llamada_ro&llamada_id=" . $array_campanas[$contacto_id]['id'] . "&contacto_id=" . $contacto_id . "&campana_id=" . $array_campanas[$contacto_id]['campana_id'] . "\">
				" . $prospecto . "</a></td>"
                . "<td align='left'>" . $array_vendedores[$uid] . "</td>"
                . "<td align='center'>" . $fecha_importado . "</td>"
                . "<td align='center'>" . $array_retraso['timestamp'] . "</td>"
                . "<td align='right'>" . brigeFormatDayToDayHour($array_retraso['num_horas'] + 0) . "</td>"
                . "<td align='center'>" . $modelo . "</td>"
                . "</tr>";
    }
    $tabla_campanas .="</tbody><thead>
				<tr>
                <td>&nbsp;</td>
		 		<td>Total de prospectos: $num </td>
                <td colspan='3'>&nbsp;</td>
                <td align='right'>" . brigeFormatDayToDayHour($total_hrs_sin_atencion) . "</td>
                <td>&nbsp;</td>
                </tr></thead>
		</table>";
    $objeto = new Genera_Excel($tabla_campanas, 'Asignacion-Prospectos', $_site_name);
    $boton_excel = $objeto->Obten_href();
} else {
    $tabla_campanas = "<center>No hay registros de la distribuidora:   " . $name_group . "</center>";
}

include_once("templateVehiculo.php");

function calcula($db, $contacto_id, $uid) {
    $hoy = date("Y-m-d H:i:s");
    $tmp = array();
    if ($uid == 0)
        $sql = "select contacto_id,TIMESTAMPDIFF( HOUR , timestamp, '" . $hoy . "' ) as num_horas,timestamp from unicos_log where contacto_id=" . $contacto_id . " order by timestamp desc limit 1";
    else
        $sql="select contacto_id,timestamp from unicos_log where contacto_id=" . $contacto_id . " order by timestamp desc limit 1";
    //echo "<br>".    $sql;
    $res = $db->sql_query($sql);
    if ($db->sql_numrows($res) > 0) {
        $tmp = $db->sql_fetchrow($res);
    }
    return $tmp;
}

/**
 * Metodo que cambia el numero de horas en formato dias - horas
 * @param int $totalHours numero de horas
 * @return string  con la cadena de dias y horas
 */
function brigeFormatDayToDayHour($totalHours) {
    $hoursForDay = 24;
    $days = "" + ($totalHours / $hoursForDay);
    list($day, $decimalDay) = explode(".", $days);
    $decimalDay = ((float) ("." . $decimalDay)) * $hoursForDay;
    return "$day d " . (int) $decimalDay . " h";
}

/**
 *
 * @param $db conexion a la base de adtos
 * @return array con los origenes, usando como llave el origen_id
 */
function regresa_origenes($db) {
    $tmp_array = array();
    $sql_con = "SELECT fuente_id,nombre FROM crm_fuentes ORDER BY fuente_id;";
    $res_con = $db->sql_query($sql_con);
    if ($db->sql_numrows($res_con) > 0) {
        while ($fila = $db->sql_fetchrow($res_con)) {
            $tmp_array[$fila['fuente_id']] = $fila['nombre'];
        }
    }
    return $tmp_array;
}

/**
 *
 * @param $db conexion a la base de datos
 * @param int $gid id de la concesionaria
 * @return array con los nombres de los vendedores, usando como llave el uid del vendedor y solo se saca los de gid
 */
function regresa_vendedor($db, $gid) {
    $tmp_array = array();
    $sql_con = "SELECT DISTINCT a.gid,a.uid,b.name FROM crm_contactos a,users b WHERE a.gid=" . $gid . " and a.uid=b.uid;";
    $res_con = $db->sql_query($sql_con);
    if ($db->sql_numrows($res_con) > 0) {
        while ($fila = $db->sql_fetchrow($res_con)) {
            $tmp_array[$fila['uid']] = $fila['name'];
        }
    }
    return $tmp_array;
}

/**
 * 
 */
function regresa_concesionaria($db, $gid) {
    $name = "";
    $sql_con = "SELECT name FROM groups WHERE gid=" . $gid . ";";
    $res_con = $db->sql_query($sql_con);
    if ($db->sql_numrows($res_con) > 0) {
        $name = $db->sql_fetchfield(0, 0, $res_con);
    }
    return $name;
}

/**
 *
 * @param $db conexion a la base de datos
 * @param int $gid id de la concesionaria
 * @return array con los id de la campanas, usando como llave el contacto_id del contacto y solo se saca los de gid
 */
function regresa_campanas($db, $gid) {
    $gid = 0 + $gid;
    $tmp_array = array();
    $sql_con = "SELECT id, campana_id,contacto_id FROM crm_campanas_llamadas WHERE campana_id LIKE '" . $gid . "%'ORDER BY contacto_id";
    //$sql_con="SELECT id,campana_id FROM crm_campanas_llamadas WHERE contacto_id=".$contacto_id." limit 1;";
    $res_con = $db->sql_query($sql_con);
    if ($db->sql_numrows($res_con) > 0) {
        while ($fila = $db->sql_fetchrow($res_con)) {
            $tmp_array[$fila['contacto_id']]['id'] = $fila['id'];
            $tmp_array[$fila['contacto_id']]['campana_id'] = $fila['campana_id'];
        }
    }
    return $tmp_array;
}

function Regresa_Modelos($db, $contacto_id) {
    $n_modelos = '';
    $sql_m = "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id=" . $contacto_id . ";";
    $res_m = $db->sql_query($sql_m);
    if ($db->sql_numrows($res_m) > 0) {
        while (list($modelo) = $db->sql_fetchrow($res_m)) {
            $tmp_modelos[] = $modelo;
        }
        $n_modelos = implode(", ", $tmp_modelos);
    }
    return $n_modelos;
}

?>
