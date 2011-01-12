<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $vaciar, $submit, $_module, $_op, $file;
if ($submit)
{
  if (isset($file[name]))
  {
    $filename = $_FILES['file']['tmp_name'];
    $table = $_FILES['file']['name'];
    //chekar si existe la tabla
    $result = $db->sql_query("SHOW TABLES");
    $tables = array();
    while (list($t) = $db->sql_fetchrow($result))
        array_push($tables, $t);
    if (!in_array($table, $tables))
    {
        $error = "<div class=title>No existe la tabla '$table'</div>";
    }
    else
    {
        if ($vaciar)
        {
            $db->sql_query("TRUNCATE TABLE `$table` ") or die ("No se pudo vaciar, abortando");
            $vaciado = " vaciado y";
        }
        $sql = "LOAD DATA LOCAL INFILE '".$filename."' INTO TABLE `".$table."` FIELDS TERMINATED BY ';' ENCLOSED BY '\"' ESCAPED BY '\\\\' LINES TERMINATED BY '\\n'";
        $result = $db->sql_query($sql) or die("<br>No se pudo actualizar la tabla <br>".@print_r($db->sql_error()));
        $actualizando_tabla =  "<b>La tabla \"$table\" se ha$vaciado actualizado</b>";
    }
  } else $error = "<div class=title>Error no subio el archivo</div>";
//   $actualizando_tabla = "Actualizada tabla $table";
}

?> 
