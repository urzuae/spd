<?

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}

global $db, $campana_id, $nombre, $apellido_paterno, $apellido_materno,
 $submit, $status_id, $ciclo_de_venta_id, $uid, $gid, $rsort, $open, $orderby, $uid_, $_module,
 $nsort, $_op, $url, $filtro, $leyenda_filtros, $tmp_filtros,$_site_name;

global $gid, $uid, $_dbhost, $_dbuname, $_dbpass, $_dbname;
$fecha_i = '';
$fecha_c = '';
$tmp_origen = 0;
$tabla = " crm_contactos ";
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
$array_origenes = regresa_origenes($db);
$array_vendedores = regresa_vendedor($db, $gid, $uid);
$array_campanas = regresa_campanas($db, $gid);
$name_vendedor = $array_vendedores[$uid];

$array_duplicados=array();

$sql_contactos = "SELECT b.contacto_id, concat(b.nombre, ' ', b.apellido_paterno, ' ', b.apellido_materno ) AS prospecto,
                    b.origen_id, b.fecha_importado,m.modelo_id,m.modelo
                    FROM crm_contactos b,groups_ubications a, crm_prospectos_unidades m
                    WHERE b.gid=a.gid AND b.uid=" . $uid . " AND b.contacto_id=m.contacto_id " . $filtro . " " . $filtro_contacto . " " . $filtro_vehiculo . ";";
$res_contactos = $db->sql_query($sql_contactos) or die("Error" . $sql_contactos);
if ($db->sql_numrows($res_contactos) > 0) {
    $tabla_campanas = "<table align='center' class='tablesorter' width='100%'>"
            . "<thead>"
            . "<tr>"
            . "<th width='15%'>Campa&ntilde;a</th>"
            . "<th width='35%'>Nombre</th>"
            . "<th width='15%'>F. de &Uacute;ltima Reasig</th>"
            . "<th width='15%'>F. de Primera Asignaci&oacute;n</th>"
            . "<th width='10%'>Total de Reasignaciones</th>"
            . "<th width='10%'>Producto</th>"
            . "</tr></thead>";
    $counter = 0;
    $total_reasignaciones = 0;
    while ($fila = $db->sql_fetchrow($res_contactos)) {
        if ($counter == 0) {
            $name_group = $fila['concesionaria'];
        }
        $contacto_id = $fila['contacto_id'];
        if(!in_array($contacto_id,$array_duplicados))
        {
            $array_duplicados[]=$contacto_id;
            $array_reasignaciones=genera_reasignaciones_prospecto($db,$contacto_id,$filtro_contacto);
            $num_reasig = $array_reasignaciones[$contacto_id]['reasig'];
            if ($num_reasig < 0)
                $num_reasig = 0;

            $total_reasignaciones = $total_reasignaciones + $num_reasig;
        }
        else
        {
            $num_reasig =0;
        }
        $tabla_campanas .= "
                <tr class=\"row" . ($class_row++ % 2 ? "2" : "1") . "\" style=\"cursor:pointer;\">"
                . "<td>" . $array_origenes[$fila['origen_id']] . "</td>"
                . "<td align='left'><a href=\"index.php?_module=$_module&_op=llamada_ro&llamada_id=" . $array_campanas[$fila['contacto_id']]['id'] . "&contacto_id=" . $fila['contacto_id'] . "&campana_id=" . $array_campanas[$fila['contacto_id']]['campana_id'] . "\">
				" . $fila['prospecto'] . "</a></td>"
                . "<td align='center'>" . $array_reasignaciones[$contacto_id]['maximo'] . "</td>"
                . "<td align='center'>" . $array_reasignaciones[$contacto_id]['minimo'] . "</td>"
                . "<td align='right '>" . $num_reasig . "</td>"
                . "<td align='center'>" . $fila['modelo'] . "</td>"
                . "</tr>";
        
        $counter++;

    }
    $tabla_campanas .="</tbody><thead>
				<tr>
                <td>&nbsp;</td>
		 		<td>Total de prospectos asignados $counter</td>
                <td colspan='2'>&nbsp;</td>
                <td>" . $total_reasignaciones . "</td><td>&nbsp;</td>
				</tr></thead>
			</table>";
    $objeto = new Genera_Excel($tabla_campanas, 'Reasignacion-Vendedor-Prospectos', $_site_name);
    $boton_excel = $objeto->Obten_href();
}
else {
    $tabla_campanas = "<center>No hay registros de la distribuidora:   " . $name_group . "</center>";
}
include_once("templateVehiculo.php");

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
function regresa_vendedor($db, $gid, $uid) {
    $tmp_array = array();
    $sql_con = "SELECT uid,name FROM users  WHERE gid=" . $gid . " and uid=" . $uid . ";";
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

function genera_reasignaciones_prospecto($db,$contacto_id,$filtro_contacto)
{
    $array_reasignaciones=array();
    $filtro_contacto.=$filtro_contacto." AND b.contacto_id = '".$contacto_id."' ";
    $sql="SELECT c.timestamp
          FROM crm_contactos b, crm_contactos_asignacion_log c
          WHERE b.contacto_id = c.contacto_id ".$filtro_contacto."
          ORDER BY c.contacto_id,c.timestamp;";
  
    $res=$db->sql_query($sql) or die("<br>Error:  ".$sql);
    $tuplas=$db->sql_numrows($res);
    if($tuplas > 0)
    {
        $i=0;
        while(list($timestamp)=$db->sql_fetchrow($res))
        {
            if($i == 0)             $primera=$timestamp;
            if($i == ($tuplas - 1)) $ultima=$timestamp;
            $i++;
        }
        $array_reasignaciones[$contacto_id]['reasig']=$tuplas+ 0;
        $array_reasignaciones[$contacto_id]['maximo']=$ultima;
        $array_reasignaciones[$contacto_id]['minimo']=$primera;
    }
    return $array_reasignaciones;

}

?>