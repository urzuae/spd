<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $user_id, $group_id, $empresa_id;
global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";
$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

$sql = "SELECT nombre, campana_id FROM crm_campanas WHERE 1";
$result = $db->sql_query($sql) or die("Error");
$select_campanas .= "<select name=campana_id>\n";
while (list($nombre, $campana_id2) = $db->sql_fetchrow($result))
{
    $result2 = $db->sql_query("SELECT campana_id FROM crm_campanas_groups where campana_id='$campana_id2' AND gid='$gid'") or die("Error 2");
    if ($db->sql_numrows($result2) > 0)
    {
        $result3 = $db->sql_query("SELECT contacto_id FROM crm_campanas_llamadas where campana_id='$campana_id2'") or die("Error 3");
        if ($db->sql_numrows($result2) < 1) continue; //campaña sin call center
        if ($campana_id2 == $campana_id) $sel = " SELECTED";
        else $sel = "";
        $select_campanas .= "<option value=\"$campana_id2\" $sel>$nombre</option>";
    }
}
$select_campanas .= "</select>";

$sql = "SELECT uid, user FROM users WHERE 1 ORDER BY user";
$result = $db->sql_query($sql) or die("$sql");
$select_usuarios .= "<select name=user_id>\n";
if (!$user_id || $gid) $sel = "SELECTED";
else $sel = "";
$select_usuarios .= "<option value=\"\" $sel>Todos</option>";
while (list($user_id_, $name) = $db->sql_fetchrow($result))
{
        if ($user_id_ == $user_id) $sel = " SELECTED";
        else $sel = "";
        $name=strtolower($name);
        $select_usuarios .= "<option value=\"$user_id_\" $sel>$name</option>";
}
$select_usuarios .= "</select>";

$sql = "SELECT gid, name FROM groups WHERE 1 ORDER BY gid";
$result = $db->sql_query($sql) or die("$sql");
$select_grupos .= "<select name=group_id>\n";
if (!$group_id) $sel = "SELECTED";
else $sel = "";
$select_grupos .= "<option value=\"\" $sel>Todos</option>";
while (list($group_id_, $name) = $db->sql_fetchrow($result))
{
        if ($group_id_ == $group_id) $sel = " SELECTED";
        else $sel = "";
        $name=strtolower($name);
        $select_grupos .= "<option value=\"$group_id_\" $sel>$name</option>";
}
$select_grupos .= "</select>";

$sql = "SELECT empresa_id, nombre FROM empresas WHERE 1 ORDER BY empresa_id";
$result = $db->sql_query($sql) or die("$sql");
$select_empresas .= "<select name=empresa_id>\n";
if (!$empresa_id) $sel = "SELECTED";
else $sel = "";
$select_empresas .= "<option value=\"\" $sel>Todos</option>";
while (list($e_id_, $nombre) = $db->sql_fetchrow($result))
{
        if ($e_id_ == $empresa_id) $sel = " SELECTED";
        else $sel = "";
        $nombre=strtolower($nombre);
        $select_empresas .= "<option value=\"$e_id_\" $sel>$nombre</option>";
}
$select_empresas .= "</select>";

if ($campana_id)
{
  if ($empresa_id) $andurl = "&empresa_id=$empresa_id";
  elseif ($group_id) $andurl = "&gid=$group_id";
  elseif ($user_id) $andurl = "&user_id=$user_id";
  $graph = "<img src=\"?_module=$_module&_op=graph&campana_id=$campana_id&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin$andurl\">";
//   $graph .= "<br><img src=\"?_module=$_module&_op=graph2&campana_id=$campana_id&fecha_ini=$fecha_ini&fecha_fin=\">";
}
$_html .= "<center>$graph<h1>Selecciona una campaña</h1><form><input type=hidden name=_module value=\"$_module\"><input type=hidden name=_op value=\"$_op\">$select_campanas<input type=submit value=Aceptar></form></center>";

// if ($campana_id) $_admin_menu2 .= "<br>
// <a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Llamadas al día</a><br>
// <a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Llamadas exitosas</a><br>
// <a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Porcentajes</a><br>
// <a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Hora de llamadas</a><br>
// <a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Telefonístas</a><br>
// <a href=\"index.php?_module=$_module&campana_id=$campana_id&_op=g\">Prospectos</a><br>
// ";
 ?>