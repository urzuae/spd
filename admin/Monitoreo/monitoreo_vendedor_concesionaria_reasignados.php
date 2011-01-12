<?

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}
global $db, $gid, $from, $campana_id, $uid, $orderby, $tmp_filtros, $order, $url, $_dbhost, $_dbuname, $_dbpass, $_dbname,$_site_name;

$fecha_i = '';
$fecha_c = '';
$tmp_origen = 0;
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
    $filtro_vehiculo = " AND " . implode(" AND ", $filtroPorVehiculo) . " AND b.contacto_id=m.contacto_id ";
    $tabla = ', crm_prospectos_unidades m';
}
$vendedores = vendedores($db, $gid);
$array_totales = genera_total($db, $filtro, $filtro_contacto, $filtro_vehiculo, $tabla);
$contador_totales = 0;
$sql = "SELECT DISTINCT b.gid,b.uid
          FROM  groups_ubications a, crm_contactos b" . $tabla . "
          WHERE b.gid > 0 AND b.uid!= 0 AND a.gid=b.gid " . $filtro . " " . $filtro_contacto . " " . $filtro_vehiculo . " order by b.uid ASC;";
$res = $db->sql_query($sql) or die("Error" . $sql);
if ($db->sql_numrows($res) > 0) {
    $tabla_vendedores.= '
        <table align="center" class="tablesorter">
            <thead>
                <tr>
                    <th width="28%">Nombre del Vendedor</th>
                    <th width="12%">Total Prospectos</th>
                    <th width="12%">Total Prospectos Reasignados</th>
                    <th width="12%">No. de Reasignaciones</th>
                    <th width="12%">No. Prospectos Recibidos</th>
                    <th width="12%">No. Prospectos Quitados</th>
                    <th width="12%">Max de Reasignaciones</th>
                </tr>
            </thead>';
    $tmp_totales = 0;
    $tmp_reasig = 0;
    $tmp_total_reasig = 0;

    $total_tmp_totales = 0;
    $total_tmp_reasig = 0;
    $total_tmp_total_reasig = 0;

    $t_maximo = 0;
    $total_contactos = 0;
    $total_reasignaciones = 0;
    while (list($gid, $uid) = $db->sql_fetchrow($res)) {

        $tmp_totales = 0 + $array_totales[$uid];
        $array_reasignaciones=genera_reasignaciones_vendedor($db,$uid,$filtro_contacto);
        $tmp_reasig = $array_reasignaciones[$uid]['no_cont'] + 0;
        $tmp_total_reasig = $array_reasignaciones[$uid]['no_reas'] + 0;
        $tmp_maximo = $array_reasignaciones[$uid]['maximos'] + 0;
        $tmp_recibidos = 0 + movimientos_recibidos($db, $uid);
        $tmp_quitados = 0 + movimientos_quitados($db, $uid);

        if ($tmp_maximo > $t_maximo)
            $t_maximo = $tmp_maximo;
        $total_tmp_totales = $total_tmp_totales + $tmp_totales;
        $total_tmp_reasig = $total_tmp_reasig + $tmp_reasig;
        $total_tmp_total_reasig = $total_tmp_total_reasig + $tmp_total_reasig;
        $total_recibidos = $total_recibidos + $tmp_recibidos;
        $total_quitados = $total_quitados + $tmp_quitados;
        $tabla_vendedores.= "<tr class=\"row" . ($class_row++ % 2 ? "2" : "1") . "\" style=\"cursor:pointer;\">
		        	<td align='left'><a href='index.php?_module=Monitoreo&_op=monitoreo_prospecto_vendedor_reasignado&gid=$gid&uid=$uid$url'>" . $vendedores[$uid] . "</a></td>
		        	<td>" . $tmp_totales . "</td>
		        	<td>" . $tmp_reasig . "</td>
                    <td>" . $tmp_total_reasig . "</td>
                    <td>" . $tmp_recibidos . "</td>
                    <td>" . $tmp_quitados . "</td>
		        	<td>" . $tmp_maximo . "</td>
		        	</tr>";
    }
    $tabla_vendedores.= "
			<thead><tr>
			<td>Total</td>
			<td>" . $total_tmp_totales . "</td>
			<td>" . $total_tmp_reasig . "</td>
			<td>" . $total_tmp_total_reasig . "</td>
            <td>" . $total_recibidos . "</td><td>" . $total_quitados . "</td><td>" . $t_maximo . "</td>
			</tr></thead></table>";
    $objeto = new Genera_Excel($tabla_vendedores, 'Reasignacion-Vendedores', $_site_name);
    $boton_excel = $objeto->Obten_href();
}
else {
    $tabla_vendedores = "<br>Los prospectos no estan asignados a ning&uacute;n Vendedor<br>";
}
include_once("templateVehiculo.php");


/* * ********* FUNCIONES AUXILIARES  ******************* */

/**
 * Funcion que sirve para sacar el total de prospectos por gid
 *
 * @param int conexion a Base de datos $db
 * @param string nombre de la tabla de donde se recuperan los datos $tabla
 * @param string filtro para consultar la BD  $filtro
 * @return array con los totales de prospectos por grupo
 */
function genera_total($db, $filtro, $filtro_contacto, $filtro_vehiculo, $tabla) {
    $sql_totales = "SELECT a.gid,b.uid,count(b.uid) as totales FROM  groups_ubications a, crm_contactos b" . $tabla . " WHERE a.gid=b.gid AND b.uid != 0 " . $filtro . "  " . $filtro_contacto . " " . $filtro_vehiculo . " GROUP BY b.uid ORDER BY b.uid;";
    $res_totales = $db->sql_query($sql_totales) or die("Error" . $sql_totales);
    if ($db->sql_numrows($res_totales) > 0) {
        while ($fila = $db->sql_fetchrow($res_totales)) {
            $array_totales[$fila['uid']] = $fila['totales'];
        }
    }
    return $array_totales;
}

function movimientos_recibidos($db, $uid) {
    $recibidos = 0;
    $sql_recibidos = "SELECT count(*) FROM crm_contactos_asignacion_log WHERE to_uid=" . $uid . " AND from_uid >0;";
    $res_recibidos = $db->sql_query($sql_recibidos);
    if ($db->sql_numrows($res_recibidos) > 0)
        $recibidos = $db->sql_fetchfield(0, 0, $res_recibidos);
    return $recibidos;
}

function movimientos_quitados($db, $uid) {
    $quitados = 0;
    $sql_quitados = "SELECT count( * ) FROM crm_contactos_asignacion_log WHERE from_uid=" . $uid . " AND to_uid >0";
    $res_quitados = $db->sql_query($sql_quitados);
    if ($db->sql_numrows($res_quitados) > 0)
        $quitados = $db->sql_fetchfield(0, 0, $res_quitados);
    return $quitados;
}

/**
 * Funcion que sirve para sacar el total de reasignaciones
 *
 * @param int conexion a Base de datos $db
 * @param string nombre de la tabla de donde se recuperan los datos $tabla
 * @param string filtro para consultar la BD  $filtro
 * @return array con los totales de prospectos con resignacion por vendedor
 */
function genera_reasignados($db, $filtro) {
    if (!empty($filtro))
        $filtro = " AND " . $filtro;
    $sql_reasignados = "SELECT a.gid, b.uid,b.contacto_id, c.contacto_id AS log, c.total, c.timestamp
                    FROM groups_ubications a, crm_contactos b, total_logs_contactos c, crm_prospectos_unidades pu
                    WHERE  a.gid=b.gid AND b.contacto_id = c.contacto_id AND b.contacto_id = pu.contacto_id " . $filtro . " ORDER BY b.uid, b.contacto_id;";
    $res_reasignados = $db->sql_query($sql_reasignados) or die("Error" . $sql_reasignados);
    if ($db->sql_numrows($res_reasignados) > 0) {
        $tmp_uid = 0;
        while ($fila = $db->sql_fetchrow($res_asignados)) {
            if ($tmp_iud != $fila['uid']) {
                $total_contactos = 0;
                $total_reasignados = 0;
                $total_maximo = 0;
                $tmp_iud = $fila['uid'];
            }
            if ($fila['total'] > 2) {
                $total_contactos++;
                $total_reasignados = $total_reasignados + ($fila['total'] - 2);
                if (($fila['total'] - 2 ) > $total_maximo)
                    $total_maximo = $fila['total'] - 2;
            }
            $array_reasignados[$fila['uid']]['total_contactos_reasignados'] = $total_contactos;
            $array_reasignados[$fila['uid']]['total_reasigaciones'] = $total_reasignados;
            $array_reasignados[$fila['uid']]['maximo'] = $total_maximo;
        }
    }
    return $array_reasignados;
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
function genera_reasignaciones_vendedor($db,$uid,$filtro_contacto)
{
    $no_reas=0;
    $array_gids=array();
    $array_reasignaciones=array();
    $filtro_contacto.=$filtro_contacto." AND b.uid = '".$uid."' ";
    $sql="SELECT b.uid,count(c.contacto_id) as total
        FROM crm_contactos b, crm_contactos_asignacion_log c
        WHERE b.contacto_id = c.contacto_id  AND b.uid > 0 ".$filtro_contacto." GROUP BY (c.contacto_id) ORDER BY c.contacto_id;";
    $res=$db->sql_query($sql) OR die("Error:  ".$sql);
    $tuplas=$db->sql_numrows($res);
    if($tuplas > 0)
    {

        while(list($_uid,$no_reasignaciones)=$db->sql_fetchrow($res))
        {
            $no_reas = $no_reas + $no_reasignaciones;
        }
        $array_reasignaciones[$uid]['no_reas']=$no_reas+ 0;
        $array_reasignaciones[$uid]['no_cont']=$tuplas + 0;

    }
    return $array_reasignaciones;
}
?>