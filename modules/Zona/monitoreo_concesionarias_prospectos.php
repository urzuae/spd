<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $orderby, $rsort;
$window_opc = "'llamada','location=no,resizable=yes,scrollbars=yes,navigation=no,titlebar=no,directories=no,width=800,height=750,left=0,top=0,alwaysraised=yes'";

//obtener gerente de que zona es
$sql = "SELECT zona_id FROM crm_zonas_gerentes WHERE uid='$uid'";
$r = $db->sql_query($sql) or die("<br>Error".$db->sql_error());
list($zona_id) = $db->sql_fetchrow($r);

//obtener concesionarias que administra
$sql = "SELECT gid FROM groups_zonas WHERE zona_id='$zona_id'";
$r = $db->sql_query($sql) or die("<br>Error".$db->sql_error());
while (list($gid) = $db->sql_fetchrow($r))
{
  $sql = "SELECT name FROM groups WHERE gid='$gid'";
  $r2 = $db->sql_query($sql) or die("<br>Error".print_r($db->sql_error()));
  list($nombre) = $db->sql_fetchrow($r2);
  $sql = "SELECT contacto_id FROM crm_contactos WHERE gid='$gid'";
  $r2 = $db->sql_query($sql) or die("<br>Error".print_r($db->sql_error()));
  $total = $db->sql_numrows($r2);
  $sql = "SELECT c.contacto_id FROM crm_contactos AS c WHERE  c.gid='$gid' AND c.uid!='0'";// , crm_campanas_llamadas AS l  c.contacto_id=l.contacto_id
  $r2 = $db->sql_query($sql) or die("<br>Error".print_r($db->sql_error()));
  $asignados = $db->sql_numrows($r2);
  //buscar cuantos contactos tenemos
  $tabla_campanas .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" style=\"cursor:pointer;\" onclick=\"location.href='index.php?_module=$_module&_op=monitoreo_prospectos&gid=$gid';\">"
                    ."<td>$gid</td>"
                    ."<td>$nombre</td>"
                    ."<td>$total</td>"
                    ."<td>$asignados</td>"
                    ."</tr>";
}

$tabla_campanas = "<table border=\"0\">\n"
                    . "<thead><tr>"
                    ."<td colspan=\"2\"><a href=\"index.php?_module=$_module&_op=$_op&orderby=nombre\" style=\"color:#ffffff\">Concesionaria</a></td>"
//                     ."<td colspan=\"".count($campanas)."\">Ciclo</td>"
                    ."<td rowspan=\"1\"><a href=\"index.php?_module=$_module&_op=$_op&orderby=total&rsort=$nrsort\" style=\"color:#ffffff\">Prospectos</a></td>"
                    ."<td rowspan=\"1\"><a href=\"index.php?_module=$_module&_op=$_op&orderby=total&rsort=$nrsort\" style=\"color:#ffffff\">Asignados</a></td>"
                    ."</tr></thead>" .$tabla_campanas;

?>