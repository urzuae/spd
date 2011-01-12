<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del;
$dir = "../files/";
if ($file && $submit)
{
    $tmp_name = $_FILES['file']['tmp_name'];
    $new_name = $_FILES['file']['name'];
    move_uploaded_file($tmp_name, "$dir/$new_name");
    chmod("$dir/$new_name", 0666);
    $msg = "<h1>Archivo \"$new_name\" subido con éxito</h1>";
}
else if (isset($del) && $del != "")
{
    unlink("$dir/$del");
    $msg = "<h1>Archivo \"$del\" borrado con éxito</h1>";
}
$list .= "<table><thead><tr><td>Nombre</td><td>Tamaño (en bytes)</td><td>Última modificación</td><td>Borrar</td></tr></thead>";
$dir_handle = @opendir("$dir") or die("No se puede leer el directorio admin");
while ($file = readdir($dir_handle)) {
    if ((strpos($file, ".") === 0)) //si enkontramos archivo que empieze por . no mostrar
        continue;
    $fp = fopen("$dir/$file", "r");
    $fstat = fstat($fp);
    fclose($fp);
    if (!($c++ % 2))
        $class = "row1";
    else
        $class = "row2";
    $list .= "<tr class=\"$class\"><td><a href=\"../modules/$_module/files/$file\" target=\"documento_de_apoyo\">$file</a></td>"
            . "<td align=right>" . $fstat['size'] . "</td>\n"
            . "<td>" . date('Y-m-d H:i:s', $fstat['mtime']) . "</td>\n"
            . "<td align=right><a href=\"#\" onclick=\"del('$file')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=0></a></td>\n";
    $list .= "</tr>\n";
}
closedir($dir_handle);
$list .= "</table>\n";
?>
