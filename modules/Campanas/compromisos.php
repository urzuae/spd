<?

if (!defined('_IN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $submit, $orderby, $fecha_ini, $fecha_fin, $_site_title;

$_site_title = "Compromisos";
$window_opc = "'llamada','menubar=no,location=no,resizable=yes,scrollbars=yes,status=no,navigation=no,titlebar=no,directories=no,width=800,height=750,left=200,top=0,alwaysraised=yes'";
$titulo = "Compromisos";
//obtener todos los nombres de las campanas
$sql = "SELECT campana_id, nombre FROM crm_campanas ";
$r2 = $db->sql_query($sql) or die($sql . (print_r($db->sql_error())));
while (list($id, $nombre) = $db->sql_fetchrow($r2)) {
    $campanas[$id] = $nombre;
}
//obtener eventos_ids para más adelante usarlos
$sql = "SELECT tipo_id, nombre FROM `crm_campanas_llamadas_eventos_tipos` ";
$result = $db->sql_query($sql) or die("Error al buscar tipo<br>$sql" . print_r($db->sql_error()));
while (list($tipo_id, $nombre) = htmlize($db->sql_fetchrow($result))) {
    $evento_tipo_ids[$tipo_id] = $nombre;
}


// checamos si hay compromisos del dia
if (!$submit)
{
    $titulo_alerta="No existen compromisos para el dia de hoy";
    $and_fecha .= " AND c.fecha_cita between '".date('Y-m-d')." 00:00:00' AND '".date('Y-m-d')." 23:59:59'";
    $fecha_ini=date('d-m-Y');
    $fecha_fin=date('d-m-Y');

}
#if($submit || $orderby)
else
{
    $titulo_alerta="No existen compromisos en este rango de fechas";
    if ($fecha_ini) {
        $titulo.= " desde " . $fecha_ini;
        $fecha_inicio = date_reverse($fecha_ini);
        $and_fecha .= " AND c.fecha_cita>'$fecha_inicio 00:00:00'";
    }
    if ($fecha_fin) {
        $tiutlo.=" hasta " . $fecha_fin;
        $fecha_final = date_reverse($fecha_fin);
        $and_fecha .= " AND c.fecha_cita<'$fecha_final 23:59:59'";
    }
}
$sql = "SELECT c.id, d.origen_id, c.contacto_id, d.nombre, d.apellido_paterno, d.apellido_materno,
            DATE_FORMAT(c.fecha_cita,'%d-%m-%Y %H:%i'), UNIX_TIMESTAMP(c.fecha_cita),d.tel_casa,
            d.tel_oficina, d.tel_movil, d.tel_otro, c.`timestamp`, c.status_id, c.campana_id,d.fecha_importado
            FROM crm_campanas_llamadas AS c, crm_contactos AS d
            WHERE c.status_id='-2' AND d.contacto_id=c.contacto_id AND d.uid='$uid' $and_fecha";
    $r2 = $db->sql_query($sql) or die("Error en la consulta:  " . $sql);
    $num = $db->sql_numrows($r2);

if ($num > 0) {
    while (list($llamada_id, $origen_id, $contacto_id, $nombre, $apellido_paterno, $apellido_materno, $fecha_cita, $fecha_cita_timestamp, $tel_casa, $tel_oficina, $tel_movil, $tel_otro, $llamda_timestamp, $status_id, $campana_id, $fecha_importado) = $db->sql_fetchrow($r2)) {
        if ($tel_otro)
            $tel = $tel_otro;
        if ($tel_movil)
            $tel = $tel_movil;
        if ($tel_oficina)
            $tel = $tel_oficina;
        if ($tel_casa)
            $tel = $tel_casa;
        //ponerle nombre al origen
        $r3 = $db->sql_query("SELECT nombre FROM crm_fuentes WHERE fuente_id='$origen_id' LIMIT 1");
        list($origen) = $db->sql_fetchrow($r3);
        //buscar la fecha de los contactos en el log (cuando cambio de ciclo de venta)
        $sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp ASC LIMIT 1";
        $r3 = $db->sql_query($sql) or die($sql);
        list($primer_contacto, $primer_contacto_timestamp) = $db->sql_fetchrow($r3);
        $sql = "SELECT DATE_FORMAT(timestamp,'%d-%m-%Y'), UNIX_TIMESTAMP(timestamp) FROM crm_campanas_llamadas_log WHERE contacto_id='$contacto_id' ORDER BY timestamp DESC LIMIT 1";
        $r3 = $db->sql_query($sql) or die($sql);
        list($ultimo_contacto, $ultimo_contacto_timestamp) = $db->sql_fetchrow($r3);

        //formatear el tiempo que lleva de retraso la cita
        if ($fecha_cita_timestamp && $status_id == -2) {
            $retraso = time() - $fecha_cita_timestamp;
            if ($retraso > 0) {
                $horas = floor($retraso / 60 / 60);
                $mins = round($retraso / 60 - $horas * 60);
                $retraso = "$horas hr $mins m";
            }
            else
                $retraso = "";
        }
        else
            $retraso = "";

        //darle formato en horas al timestamp
        if ($ultimo_contacto_timestamp) {
            $ultimo_contacto_timestamp = time() - $ultimo_contacto_timestamp;
            $ultimo_contacto_timestamp_bk = $ultimo_contacto_timestamp;
            if ($ultimo_contacto_timestamp > 0) {
                $ultimo_contacto_timestamp = $ultimo_contacto_timestamp / 60 / 60; //entre 60 segs, entre 60 mins
                $ultimo_contacto_timestamp = sprintf("%u", $ultimo_contacto_timestamp); //entero

                $ultimo_contacto_timestamp .= " hr"; //($ultimo_contacto_timestamp!=1?"s":"")
            }
        } else {
            $ultimo_contacto_timestamp = "";
            $ultimo_contacto_timestamp_bk = "";
        }
        $sql = "SELECT e.evento_id, e.tipo_id FROM crm_campanas_llamadas_eventos AS e, crm_campanas_llamadas AS l WHERE l.id=e.llamada_id AND l.contacto_id='$contacto_id' ORDER BY evento_id DESC"; //buscar el más viejo
        $result = $db->sql_query($sql) or die("Error al buscar tipo" . print_r($db->sql_error()));
        list($evento_id, $evento_tipo_id) = htmlize($db->sql_fetchrow($result));
        //ver si no está cerrado
        $sql = "SELECT cierre_id  FROM crm_campanas_llamadas_eventos_cierres WHERE evento_id='$evento_id'";
        $result = $db->sql_query($sql) or die("Error al buscar tipo" . print_r($db->sql_error()));
        list($cierre_id) = htmlize($db->sql_fetchrow($result));
        if ($cierre_id) //ya fue cerrado, quitar el evento
            $tipo = "";
        else //si no fue cerrado, guardar el evento_id
            $tipo = $evento_tipo_ids[$evento_tipo_id];

        $contacto_ids[] = $contacto_id;
        $origenes[$contacto_id] = $origen;
        $origenes_id[$contacto_id] = $origen_id;
        $nombres[$contacto_id] = "$nombre $apellido_paterno $apellido_materno";
        $tels[$contacto_id] = $tel;
        $esperas[$contacto_id] = $fecha_importado;
        $primer_contactos[$contacto_id] = $primer_contacto;
        $ultimo_contactos[$contacto_id] = $ultimo_contacto;
        $primer_contactos_ts[$contacto_id] = $primer_contacto_timestamp ? $primer_contacto_timestamp : '9999999999'; //para el sort
        $ultimo_contactos_ts[$contacto_id] = $ultimo_contacto_timestamp_bk;
        $fecha_citas[$contacto_id] = $fecha_cita;
        $fecha_citas_ts[$contacto_id] = $fecha_cita_timestamp ? $fecha_cita_timestamp : '9999999999'; //para mandarla hasta abajo
        $retrasos[$contacto_id] = $retraso;
        $llamada_ts[$contacto_id] = $llamada_timestamp;
        $llamada_ids[$contacto_id] = $llamada_id;
        $campana_ids[$contacto_id] = $campana_id;
        $tipos[$contacto_id] = $tipo;

        $counter++; //queremos el total de contactos
    }
    //ordenar la tabla por los datos que solicitan
    switch ($orderby) {
        case "origen_id": $array_para_ordenar = &$origenes_id;
            $rsort = 0;
            break;
        case "nombre": $array_para_ordenar = &$nombres;
            $rsort = 0;
            break; //por referencia para evitar que copie
        case "tel": $array_para_ordenar = &$tels;
            $rsort = 0;
            break;
        case "ultimo_contacto": $array_para_ordenar = &$ultimo_contactos_ts;
            $rsort = 1;
            break;
        case "primer_contacto": $array_para_ordenar = &$primer_contactos_ts;
            $rsort = 0;
            break;
        case "fecha_cita": $array_para_ordenar = &$fecha_citas_ts;
            $rsort = 0;
            break;
        case "tipo": $array_para_ordenar = &$tipos;
            $rsort = 0;
            break;

        default: $array_para_ordenar = &$fecha_citas_ts; //default ordenar por retraso
            $rsort = 0;
    }
    if (!$rsort)
        asort($array_para_ordenar); //ordenar por valor y conservar asociación de keys
 else
        arsort($array_para_ordenar); //ordenar por valor  en orden inverso y conservar asociación de keys
 foreach ($array_para_ordenar AS $key => $value) {
        $ordered_contacto_ids[] = $key; //echo $key."->$value<br>";
    }
    //hasta el final crear la tabla
    $tabla_campanas .= "<table style=\"width:100%;\">"
            . "<thead>"
            . "<tr>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=origen_id&\" style=\"color:white;\">Campaña</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=campana\" style=\"color:white;\">Ciclo</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=nombre\" style=\"color:white;\">Nombre</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=tel\" style=\"color:white;\">Teléfono</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=ultimo_contacto\" style=\"color:white;\">Registro</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=primer_contacto\" style=\"color:white;\">Primer contacto</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=ultimo_contacto\" style=\"color:white;\">Último contacto</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=fecha_cita\" style=\"color:white;\">Compromiso</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=fecha_cita\" style=\"color:white;\">Retraso</a></td>"
            . "<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=tipo\" style=\"color:white;\">Tipo</a></td>"
            . "<td>Acción</td></tr>"
            . "</thead>"
            . "<tbody>";
    foreach ($ordered_contacto_ids AS $contacto_id) {
        $origen = $origenes[$contacto_id];
        $nombre = $nombres[$contacto_id];
        $tel = $tels[$contacto_id];
        $espera = $esperas[$contacto_id];
        $primer_contacto = $primer_contactos[$contacto_id];
        $ultimo_contacto = $ultimo_contactos[$contacto_id];
        $fecha_cita = $fecha_citas[$contacto_id];
        $retraso = $retrasos[$contacto_id];
        $tipo = $tipos[$contacto_id];
        $llamada_id = $llamada_ids[$contacto_id];
        $campana_id = $campana_ids[$contacto_id];
        $campana = $campanas[$campana_id];
        $tabla_campanas .= "<tr class=\"row" . (($c++ % 2) + 1) . "\">"
                . "<td>$origen</td>"
                . "<td>$campana</td>"
                . "<td>$nombre $apellido_paterno $apellido_materno</td>"
                . "<td>$tel</td>"
                . "<td>$espera</td>"
                . "<td>$primer_contacto</td>"
                . "<td>$ultimo_contacto</td>"
                . "<td>$fecha_cita</td>"
                . "<td>$retraso</td>"
                . "<td>$tipo</td>"
                . "<td align=\"center\"><a href=\"#\" onclick=\"window.open('index.php?_module=$_module&_op=llamada&campana_id=$campana_id&llamada_id=$llamada_id&contacto_id=$contacto_id&nopendientes=1',$window_opc);\"><img src=\"img/phone.gif\" border=></a></td>"
                . "</tr>";
    }
    $tabla_campanas .= "<tr class=\"row" . (($c++ % 2) + 1) . "\"><td align=\"right\"><b>Total</b></td><td><b> $counter</b></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td></tr>";
    $tabla_campanas .= "</tbody></table>";
}//if numrows
else
{
    $tabla_campanas .= "<script>alert('".$titulo_alerta."'); window.close();</script>";
}
?>
