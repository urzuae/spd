<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $id, $submit;

if ($submit) //crear el usuario
{
    global $theme, $default_module;
    $sets = "`theme`='$theme', `default_module`='$default_module'";
    $sql = "UPDATE users_configs SET $sets WHERE `uid`='$id'";
    $db->sql_query($sql)
        or die("No se pudo modificar el usuario ".print_r($db->sql_error()));
    header("location: index.php?_module=$_module");

}

//default_module
if ($id == 0)
    $gid = 0;
else
{   //buscar el gid en la db
    list($gid) = $db->sql_fetchrow($db->sql_query("SELECT gid FROM users WHERE uid='$id'")) 
        or die("No se puede obtener gid");
    //si esta el modulo relacionado kon el gid en la db entonces tenemos permiso para leer
    $sql = "SELECT module FROM groups_accesses WHERE gid='$gid'";
    $result = $db->sql_query($sql)
        or die("No se puede obtener los accesos del grupo");
    $allowed_mods = array();
    while (list($module) = $db->sql_fetchrow($result))
        array_push($allowed_mods, $module);
}

$sql = "SELECT default_module, theme FROM users_configs WHERE uid='$id' LIMIT 1";
list($default_module, $theme) = htmlize($db->sql_fetchrow($db->sql_query($sql)));

//default_module
$dir_handle = @opendir("../modules") or die("No se puede leer el directorio themes");
$gselect = "<select name=default_module>";
while ($file = readdir($dir_handle))
{
    if (strpos($file, ".") === FALSE)
    {
        //chekamos si tenemos axeso a este modulo
        if ($gid != 0) //si tiene grupo entonces es un usuario
            if (!in_array($file, $allowed_mods))
                continue; //el ke sigue
        if ($file == $default_module) $selected = "SELECTED"; //seleccionar el primero
        else $selected = "";
        $gselect .= "<option value=\"$file\" $selected>$file</option>";
    }
}
$gselect .= "</select>";
$default_module_select = $gselect;

//theme
$dir_handle = @opendir("../themes") or die("No se puede leer el directorio themes");
$gselect = "<select name=theme>";
while ($file = readdir($dir_handle))
{
    if (strpos($file, ".") === FALSE)
    {
        if ($file == $theme) $selected = "SELECTED"; //seleccionar el primero
        else $selected = "";
        $gselect .= "<option value=\"$file\" $selected>$file</option>";
    }
}
$gselect .= "</select>";
$theme_select = $gselect;
?>