<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $_modulesdir;
$uid = $_COOKIE[_uid];
if ($uid)
    list($gid) = htmlize($db->sql_fetchrow($db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1")))
        or die ("Error al buscar el grupo");
else
    $gid = 0;

//crear el menu haciendo un ls y luego komparando si tenemos axeso
$files = @scandir("$_modulesdir") or die("No se puede leer el directorio de modulos");

// Loop through the files
foreach ($files as $file)
{
    if (!(strpos($file, ".") === FALSE)) //si enkontramos . o .. o un .php o .* no mostrarlo
        continue;
    if (is_file("$_modulesdir/$file"))
        continue;
    $sql = "SELECT module FROM groups_accesses WHERE gid='$gid' AND module='$file'";
    $result = $db->sql_query($sql) 
        or die("Error al buscar permisos");
    if ($db->sql_numrows($result) > 0)
        $_content .= "<a href=\"index.php?_module=$file\" class=\"box_content\">$file</a><br>";
}



?>
