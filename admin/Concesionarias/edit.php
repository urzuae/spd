<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
$prefix = "chbx_";
function modules_array()
{
    //crear el menu de administrador para el tema
    $dir_handle = @opendir("../modules/") or die("No se puede leer el directorio admin");
    $a = array();
    // Loop through the files
    while ($file = readdir($dir_handle))
    {
        if (!(strpos($file, ".") === FALSE)) //si enkontramos . o .. o un .php o .* no mostrarlo
            continue;
        array_push($a, $file);
    }
    // Close
    closedir($dir_handle);

    reset($a);
    return $a;
}

global $db, $gid, $submit;

if ($gid != 0) list($name) = htmlize($db->sql_fetchrow($db->sql_query("SELECT name FROM groups WHERE gid='$gid' LIMIT 1"))) or die ("Error al buscar nombre de grupo");
else $name = "<i>Usuario anónimo</i>";

if ($submit) //actualizar permisos
{
    //primero kitar todos los permisos (vaciar ese pedazo de db)
    $db->sql_query("DELETE FROM groups_accesses WHERE gid='$gid'") or die("Error al borrar permisos");
    $vars = $_POST;
    reset($vars);
    foreach ($_POST as $post_var) //importante, el foreach es para que de los loops suficientes, nada mas
    {
        $key = key($vars); //obtener el nombre de la variable, esto es lo importante
        //ver si tiene ke ver kon los checkboxes
        if (strstr($key, $prefix) && $post_var == "on")
        {
            //dar permiso
            $module = substr($key, strlen($prefix)); //el nombre del modulo es todo despues del prefijo
            $module = strtr($module, "%20", " ");
            $db->sql_query("INSERT INTO groups_accesses (gid, module)VALUES('$gid', '$module')")
                or die("No se pudo agregar permiso");
        }
        next($vars);
    }
    header("location: index.php?_module=$_module");
}

//chekamos a kuales tenemos axeso segun la DB
$auth_modules = array();
$result = $db->sql_query("SELECT module FROM groups_accesses WHERE gid='$gid'") or die("Error al buscar accesos ".print_r($db->sql_error()));
while (list($module) = $db->sql_fetchrow($result))
    array_push($auth_modules, $module);


$modules_array = modules_array();
foreach ($modules_array as $module)
{
    if (in_array($module, $auth_modules)) $checked = "checked=\"checked\"";
    else $checked = "";
    $moduleuri = strtr($module, " ", "%20"); //usamos mucho los espacios y estos no son validos
    $modules_list .= "<input name=\"$prefix$moduleuri\" type=\"checkbox\"  $checked><a href=\"../index.php?_module=$module\">$module</a><br>\n";
}
?>