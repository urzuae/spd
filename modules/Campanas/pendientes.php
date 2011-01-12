<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $how_many, $from, $orderby, $del;
$window_opc = "'llamada','menubar=no,location=yes,resizable=yes,scrollbars=yes,status=no,navigation=no,titlebar=no,directories=no,width=800,height=650,left=200,top=0,alwaysraised=yes'";
//status = -2 = postpuesto
//mostrar los de hoy o anteriores
//solo mostrar los ke le pertenezkan a este usuario o a ninguno
$sql = "SELECT c.contacto_id, c.nombre, c.apellido_paterno, c.apellido_materno, l.fecha_cita, l.campana_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id AND l.status_id=-2 "
       ."AND l.fecha_cita < '".date("Y-m-d")." 23:59:59' "
       ."AND (user_id='$uid' OR user_id='0') ORDER BY l.fecha_cita DESC"; //hoy o antes
$result = $db->sql_query($sql) or die("Error en consulta ".$sql);
$_tabla = "<center><table>\n";
$_tabla .= "<thead><tr><td>Nombre</td><td>Paterno</td><td>Materno</td><td>Día</td><td>Hora</td><td></td></tr></thead>\n";
while(list($contacto_id, $nombre, $ap_pat, $ap_mat, $cita, $campana_id) = $db->sql_fetchrow($result))
{
    list($dia_cita, $hora_cita) = explode(" ", $cita);
    list($hora, $min, $seg) = explode(":", $hora_cita);
    if ($dia_cita == date("Y-m-d"))
        $dia_cita = "Hoy";
    $hora_cita = "$hora:$min";
    $_tabla .= "<tr class=\"row".(($c++%2)+1)."\"><td>$nombre</td><td>$ap_pat</td><td>$ap_mat</td><td>$dia_cita</td><td>$hora_cita</td>"
               ."<td align=\"center\"><a href=\"#\" onclick=\"window.open('index.php?_module=$_module&_op=llamada&contacto_id=$contacto_id&campana_id=$campana_id',$window_opc);\"><img src=\"img/phone.gif\" border=></a></td>"
               ."</tr>\n";
}
$_tabla .= "</table></center>\n";
$_html = $_tabla;
?>