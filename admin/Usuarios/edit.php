<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $id, $user, $name, $nameSuper, $gid, $super_val, $password, $submit, $active;

if ($submit) //crear el usuario
{
    $password = strtoupper($password);
    if($active)
		$active=0;
		else
		$active=1;
    $super = $super_val;
    if ($password == "") //no cambiar password
        $sql = "UPDATE users SET `name`='$name', `gid`='$gid', super='$super', active='$active' WHERE `uid`='$id'";
    else
        $sql = "UPDATE users SET `name`='$name', `gid`='$gid', `password`=PASSWORD('$password'), super='$super', active='$active' WHERE `uid`='$id'";
    $db->sql_query($sql)
        or die("No se pudo modificar el usuario ".print_r($db->sql_error()));

    header("location: index.php?_module=$_module");


}
//lista de grupos
$sql = "SELECT user, name, gid, super, active FROM users WHERE uid='$id' LIMIT 1";

list($user, $name, $gid, $super, $active) = htmlize($db->sql_fetchrow($db->sql_query($sql)));


$gselect = "<select name=gid onchange=\"return check_gid();\">";
$result = $db->sql_query("SELECT gid, name FROM groups WHERE 1");
while (list($g_id, $g_name) = $db->sql_fetchrow($result))
{
    if ($g_id == $gid) $selected = "SELECTED"; //seleccionar el primero
    else $selected = "";
    $gselect .= "<option value=\"$g_id\" $selected>$g_id - $g_name</option>";
}
$gselect .= "</select>";
/*TIPOS DE USUARIOS*/
$select_super = "<select name=super_val >";
$result = $db->sql_query("SELECT tipo_id, nombre FROM users_types WHERE 1 order by tipo_id");
while (list($t_id, $nameSuper) = $db->sql_fetchrow($result))
{
    if ($super == $t_id) $selected = "SELECTED"; //seleccionar el primero
    else $selected = "";
    $select_super .= "<option value=\"$t_id\" $selected>$nameSuper</option>";
}
$select_super .= "</select>";

if ($active==0)
    $checked="<input type='checkbox' name='active' checked>&nbsp;&nbsp;<font color='#800000'>Bloqueado</font>";
else
    $checked="<input type='checkbox' name='active'>";


if ($super) $super_checked = "CHECKED";
else $super_checked = "";
?>