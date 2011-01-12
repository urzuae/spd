<?php
include_once($_includesdir."/Genera_Excel.php");
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}
global $db, $fecha_ini, $fecha_fin,$_site_name;
$leyenda = "";
$fecha_actual = date("Y-m-d");
if ((!empty($fecha_ini)) && (!empty($fecha_fin))) {
    $filtro = " AND fecha_importado BETWEEN '" . $fecha_ini . " 00-01-01' AND  '" . $fecha_fin . " 23-59-59' ";
    $leyenda = " del periodo " . $fecha_ini . "  al   " . $fecha_fin;
}
if ((!empty($fecha_ini)) && (empty($fecha_fin))) {
    $filtro = " AND substr(a.fecha_importado,1,10)='" . $fecha_ini . "' ";
    $leyenda = " del dia " . $fecha_ini;
}
if ((empty($fecha_ini)) && (!empty($fecha_fin))) {
    $filtro = " AND substr(a.fecha_importado,1,10)='" . $fecha_fin . "' ";
    $leyenda = " del dia " . $fecha_fin;
}
// con esta consulta saco a los padres
$sql = "SELECT b.padre_id,a.fuente_id,a.nombre FROM crm_fuentes_arbol b,crm_fuentes a WHERE b.padre_id=1 and b.hijo_id=a.fuente_id; ";
$res = $db->sql_query($sql) or die($sql);
if ($db->sql_numrows($res) > 0) {
    while ($fila_origen = $db->sql_fetchrow($query_id)) {
        $array_fuentes_padres[$fila_origen['fuente_id']] = $fila_origen['nombre'];
        $array_fuentes_padres_valores[$fila_origen['fuente_id']] = 0;
        $array_fuentes_padres_seguimiento[$fila_origen['fuente_id']] = 0;
    }
}

$sql = "SELECT b.gid,b.name,count(a.gid) as total FROM groups b, crm_contactos a WHERE b.gid > 0 and b.gid=a.gid " . $filtro . " GROUP by b.gid ORDER BY b.gid;";
$result = $db->sql_query($sql) or die("Error" . print_r($db->sql_error()));
if ($db->sql_numrows($result) > 0) {
    $tabla_campanas .= "
			<table align='center' class='tablesorter'>
            <thead><tr>
            <td width=5%></td><td width=35%></td><td width=10%>Total de</td><td colspan='" . count($array_fuentes_padres) . "' align='center'>Prospectos por Fuente:</td><td colspan='" . count($array_fuentes_padres) . "'  align='center'>Seguimiento de prospectos por Fuente:</td>
            </tr></thead>
			<thead><tr>
			<th>#</th>
            <th>Distribuidor</th>
			<th>Prosp.</th>";
    if (count($array_fuentes_padres) > 0) {
        $array = array('Fuentes', 'fuente', 'Fuente', 'de', '');
        for ($i = 1; $i <= 2; $i++) {
            foreach ($array_fuentes_padres as $clave => $titulo) {
                $titulo = str_replace($array, "", $titulo);
                $tabla_campanas .= "<th width='8%'>" . $titulo . "</th>";
            }
        }
    }
    $tabla_campanas .= "</tr></thead>";
    $total_fin = 0;
    $asignado_fin = 0;
    $hrs_total = 0;
    $prosp_total = 0;
    $hrs_max_total = 0;
    $fuente_vw = 0;
    $array_problemas = array();
    while (list($gid, $name, $total) = $db->sql_fetchrow($result)) {
        $array_fuentes_prosp = total_prospectos($db, $gid, $filtro, $array_fuentes_padres_valores);
        $array_fuentes_compr = total_seguimiento($db, $gid, $filtro, $array_fuentes_padres_seguimiento);
        $tabla_campanas .= "<tr class=\"row" . ($class_row++ % 2 ? "2" : "1") . "\" style=\"cursor:pointer;\">"
                . "<td>$gid</td>"
                . "<td align='left'>$name</td>"
                . "<td align='right'>$total</td>";
        if (count($array_fuentes_prosp) > 0) {
            foreach ($array_fuentes_prosp as $cl => $val) {
                if (empty($cl)) {
                    $array_problemas[] = "Concesionaria " . $gid . " tiene " . $val . " prospectos con fuente eliminada";
                } else {
                    $tabla_campanas .= "<td align='right'>" . $val . "</td>";
                }
                $array_totales_prosp[$cl] = $array_totales_prosp[$cl] + $val;
            }
        }
        if (count($array_fuentes_compr) > 0) {
            foreach ($array_fuentes_compr as $cl => $val) {
                if (!empty($cl)) {
                    $tabla_campanas .= "<td align='right'>" . $val . "</td>";
                }
                $array_totales_compr[$cl] = $array_totales_compr[$cl] + $val;
            }
        }
        $tabla_campanas .="</tr>";
        $total_fin = 0 + $total_fin + $total;
        $total_cn = 0 + $total_cn + $fuente_cn;
        $total_vw = 0 + $total_vw + $fuente_vw;
        $total_cn_uid = 0 + $total_cn_uid + $fuente_cn_uid;
        $total_vw_uid = 0 + $total_vw_uid + $fuente_vw_uid;
    }
    $tabla_campanas .= "<thead><tr>
							<td>&nbsp;</td>
							<td align='right'>Totales:</td>
							<td align='right'>$total_fin</td>";
    foreach ($array_totales_prosp as $cl => $vl) {
        if (!empty($cl)) {
            $tabla_campanas .= "<td align='right'>" . $vl . "</td>";
        }
    }
    foreach ($array_totales_compr as $cl => $vl) {
        if (!empty($cl)) {
            $tabla_campanas .= "<td align='right'>" . $vl . "</td>";
        }
    }
    $tabla_campanas .= "</tr></thead></table>";
    if (count($array_problemas) > 0) {
        $tabla_campanas .="<table>";
        foreach ($array_problemas as $error) {
            $tabla_campanas .="<tr><td>" . $error . "</td></tr>";
        }
        $tabla_campanas .="</table>";
    }
    $objeto = new Genera_Excel($tabla_campanas, 'Monitoreo-Concesionarias',$_site_name);
    $boton_excel = $objeto->Obten_href();
} else {
    $tabla_campanas = "<center><p>No existen contactos ingresados en esas fechas</p></center> ";
}

/* * ********METODOS AUXILIARES **** */

/**
 * Esta funcion regresa el numero de prospectos por fuente
 * @param conexion a la base de datos $db
 * @param int $gid, id de la concesionaria
 * @param int $fuente id de la fuente
 * @return int regresa el numero de prospectos por fuente
 */
function total_prospectos($db, $gid, $filtro, $array_fuentes_padres) {
    $sql_tmp = "SELECT a.origen_id,count(a.origen_id) as total FROM crm_contactos  a WHERE a.gid =" . $gid . " " . $filtro . " group by a.origen_id ORDER BY a.origen_id";
    $res_tmp = $db->sql_query($sql_tmp);
    $num_tmp = $db->sql_numrows($res_tmp);
    if ($num_tmp > 0) {
        while ($registro = $db->sql_fetchrow($res_tmp)) {
            $tmp_padre = regresa_padre($db, $registro['origen_id']);
            $tmp_valor = $registro['total'] + 0;
            $array_fuentes_padres[$tmp_padre] = $array_fuentes_padres[$tmp_padre] + $tmp_valor;
        }
    }
    return $array_fuentes_padres;
}

function regresa_padre($db, $origen) {
    $sql_tmp = "SELECT * FROM crm_fuentes_arbol WHERE hijo_id ='" . $origen . "';";
    $res_tmp = $db->sql_query($sql_tmp);
    if ($db->sql_numrows($res_tmp) > 0) {
        $tmp_padre = $db->sql_fetchfield(0, 0, $res_tmp);
        $tmp_hijo = $db->sql_fetchfield(1, 0, $res_tmp);
        if ($tmp_padre != 1) {
            $valor = regresa_padre($db, $tmp_padre, $cadena_origenes);
        } else {
            $valor = $tmp_hijo;
        }
    }
    return $valor;
}

/**
 * Esta funcion regresa el numero de prospectos con un compromiso por fuente
 * @param int $gid, id de la concesionaria
 * @param int $fuente id de la fuente
 * @return int regresa el numero de prospectos por fuente
 * @return int regresa el numero de prospectos con un compromiso por fuente
 */
//function total_seguimiento($db,$gid,$fuente,$filtro,$array_fuentes_padres_seguimiento)
function total_seguimiento($db, $gid, $filtro, $array_fuentes_padres) {
    $reg = 0;
    $sql_tmp = "SELECT DISTINCT(a.contacto_id),a.origen_id
              FROM crm_contactos a, crm_campanas_llamadas b, crm_campanas_llamadas_eventos c
              WHERE a.gid = " . $gid . " AND a.contacto_id = b.contacto_id  AND b.id = c.llamada_id " . $filtro . " ORDER BY a.origen_id;";
    $res_tmp = $db->sql_query($sql_tmp);
    $num_tmp = $db->sql_numrows($res_tmp);
    if ($num_tmp > 0) {
        while ($registro = $db->sql_fetchrow($res_tmp)) {
            $tmp_padre = regresa_padre($db, $registro['origen_id']);
            $array_fuentes_padres[$tmp_padre] = $array_fuentes_padres[$tmp_padre] + 1;
        }
    }
    return $array_fuentes_padres;
}

?>