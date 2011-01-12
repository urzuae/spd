<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
    global $db, $file, $submit, $del;
    $dir = "$_modulesdir/$_module/files";

    $list .= "<table><thead><tr><td>Nombre</td><td>Tamaño (en bytes)</td><td>Última modificación</td></tr></thead>";
    $dir_handle = @opendir("$dir") or die("No se puede leer el directorio admin");
    while ($file = readdir($dir_handle))
    {
        if ((strpos($file, ".") === 0)) //si enkontramos archivo que empieze por . no mostrar
            continue;
        $fp = fopen("$dir/$file", "r") ;
        $fstat = fstat($fp);
        fclose($fp);
        if (!($c++ % 2))
            $class = "row1";
        else 
            $class = "row2";
        $list .= "<tr class=\"$class\"><td><a href=\"../modules/$_module/files/$file\">$file</a></td>"
              ."<td align=right>".$fstat['size']."</td>\n"
              ."<td>".date('Y-m-d H:i:s', $fstat['mtime'])."</td>\n";
        $list .= "</tr>\n";
    }
    closedir($dir_handle);
    $list .= "</table>\n";
 
 ?>
