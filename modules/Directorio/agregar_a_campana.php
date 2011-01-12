<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $close_after,
       $contacto_id, $campana_id, $cita, $uid;
if ($submit)
{
    if ($cita)
    {
        list($ff, $hh) = explode(" ", $cita);
        $ff = date_reverse($ff);
        $cita = "$ff $hh";
    }
    if ($campana_id != 0)
    {
        $sql = "INSERT INTO crm_campanas_llamadas (campana_id, contacto_id, fecha_cita,user_id,status_id)VALUES('$campana_id', '$contacto_id','$cita','$uid','-2')";
        $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
    }
    die("<html><body onload=\"window.close();location.href='index.php?_module=$_module';\"></body></html>");

}
$_html = "<br><h1>¿Desea agregar este contacto a una campaña?</h1><br>\n";
$_html .= "<center><form><table><tr><td>Campaña</td><td><input type=hidden name=_module value=\"$_module\"><input type=hidden name=_op value=\"$_op\"><input type=hidden name=contacto_id value=\"$contacto_id\"><select name=\"campana_id\"><option value=0>Ninguna</option>";
$sql = "SELECT campana_id, nombre FROM crm_campanas WHERE 1 ORDER BY nombre";
$result = $db->sql_query($sql) or die("Error");
while ($row = $db->sql_fetchrow($result))
{
    list($campana_id, $nombre) = $row;
    $_html .= "<option value=\"$campana_id\">$nombre</option>";
}
$_html .= "</select></td></tr>";
$_html .= '<tr><td>Cita</td><td><input name="cita" id="cita" value=""><img src="img/calendar.png" id="f_trigger_c" style="border: 1px solid red; cursor: pointer;" title="Fecha" onmouseover="this.style.background=red;" onmouseout="this.style.background=\'\'"></td></tr>';
$_html .= '<script>Calendar.setup( { inputField :"cita", ifFormat :"%d-%m-%Y %H:%M", showsTime : true, timeFormat : "24", button : "f_trigger_c" } );</script>';
$_html .= "<tr><td colspan=2 align=center><input type=submit name=submit value=Guardar></td></tr></table></form></center>";

if ($close_after)
{
  global $_no_boxes;
  $_no_boxes = 1;
}
?>