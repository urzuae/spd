<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
	die ("No puedes acceder directamente a este archivo...");
}

global $db, $id, $status, $file, $init_status, $title, $delid;

//se esta tratando de agregar un box
if (isset($file))
{
  //obtener el ultimo "order" del ultimo box, para poner el nuevo al final
  $sql = "SELECT `order` FROM `boxes` WHERE `status`='$init_status' ORDER BY `order` DESC LIMIT 1";
  $result = $db->sql_query($sql);
  if ($db->sql_numrows($result) > 0)
    list($last_order) = htmlize($db->sql_fetchrow($result));
  else $last_order = 0;
  
  if (!isset($init_status)) $init_status = 0;
  $sql = "INSERT INTO boxes (`file`, `title`, `status`, `order`) VALUES ('$file', '$title', '$init_status',  '".($last_order+1)."')";
  
  $db->sql_query($sql);
}

//se esta borrando uno nuevo
if (isset($delid))
  $db->sql_query("DELETE FROM boxes WHERE `id`='$delid' LIMIT 1");

//entramos a lo siguiente si se esta solicitando una operacion (activar o desactivar)
if (isset($status))
{
  //obtener el ultimo "order" del ultimo box, para poner el nuevo al final
  $sql = "SELECT `order` FROM `boxes` WHERE `status`='$status' ORDER BY `order` DESC LIMIT 1";
  $result = $db->sql_query($sql);
  if ($db->sql_numrows($result) > 0)
    list($last_order) = htmlize($db->sql_fetchrow($result));
  else $last_order = 0;
  //cambiar de lugar y poner el nuevo order (posteriormente chekamos los orders de los ke habia en el ke estaba antes)
  $sql = "UPDATE `boxes` SET `status`='$status',`order`='".($last_order + 1)."' WHERE `id`='$id'";
  $db->sql_query($sql);
}
$_content = "<h1>Configuración de Boxes</h1>\n";
$_content .= "Los boxes son los pequeños bloques que el usuario ve en la página a la izquierda y la derecha del contenido principal.<br>\n";
$_content .= "Estos pueden ser menús, anuncios, etc.\n";

$_content .= "<h1>Lista de Boxes</h1>";
$_content .= "<table border=\"0\" cellspacing=2 cellpadding=3>";
//desaktivado
$sql = "SELECT `id`, `file`, `title`, `order` FROM `boxes` WHERE `status`=0 ORDER BY `order`";
$result = $db->sql_query($sql);
if ($db->sql_numrows($result) > 0)
{
  $_content .= "<thead><tr><td colspan=\"6\"><b>Desactivados</b></td></tr></thead>";
  while (list($id, $file, $title, $order) = htmlize($db->sql_fetchrow($result)))
  {
    if (!($i++ % 2)) $style = "row1";
    else $style = "row2";
    $_content .= "<tr class=\"$style\"><td>$order</td><td>$title</td><td><a href=\"index.php?_module=$_module&id=$id&status=1\">Izquierda</a></td><td><a href=\"index.php?_module=$_module&id=$id&status=2\">Derecha</a></td><td>Desactivar</td><td><a href=\"index.php?_module=$_module&delid=$id\">Borrar</a></td></tr>";
  }
}
//izkierda
$sql = "SELECT `id`, `file`, `title`, `order` FROM boxes WHERE `status`=1 ORDER BY `order`";
$result = $db->sql_query($sql);
if ($db->sql_numrows($result) > 0)
{
  $_content .= "<thead><tr><td colspan=\"6\"><b>Izquierda</b></td></tr></thead>";
  while (list($id, $file, $title, $order) = htmlize($db->sql_fetchrow($result)))
  {
    if (!($i++ % 2)) $style = "row1";
    else $style = "row2";
    $_content .= "<tr class=\"$style\"><td>$order</td><td>$title</td><td>Izquierda</td><td><a href=\"index.php?_module=$_module&id=$id&status=2\">Derecha</a></td><td><a href=\"index.php?_module=$_module&id=$id&status=0\">Desactivar</a></td><td><a href=\"index.php?_module=$_module&delid=$id\">Borrar</a></td></tr>";
  }
}
//derecha
$sql = "SELECT `id`, `file`, `title`, `order` FROM boxes WHERE `status`=2 ORDER BY `order`";
$result = $db->sql_query($sql);
if ($db->sql_numrows($result) > 0)
{
  $_content .= "<thead><tr><td colspan=\"6\"><b>Derecha</b></td></tr></thead>";
  while (list($id, $file, $title, $order) = htmlize($db->sql_fetchrow($result)))
  {
    if (!($i++ % 2)) $style = "row1";
    else $style = "row2";
    $_content .= "<tr class=\"$style\"><td>$order</td><td>$title</td><td><a href=\"index.php?_module=$_module&id=$id&status=1\">Izquierda</a></td><td>Derecha</td><td><a href=\"index.php?_module=$_module&id=$id&status=0\">Desactivar</a></td><td><a href=\"index.php?_module=$_module&delid=$id\">Borrar</a></td></tr>";
  }
}
$_content .= "</table>";


//la parte de agregar bloke
$_content .= "\n";
$_content .= "<hr><form action=\"index.php\"><input type=\"hidden\" name=\"_module\" value=\"$_module\">";
$_content .= "<table cellspacing=2 cellpadding=3><thead><tr><td colspan=2>Agregar un Box nuevo</td></tr>";
$_content .= "<tr class=row1><td>Titulo</td><td><input type=\"text\" name=\"title\"></td></tr>";
$_content .= "<tr class=row2><td>Box</td><td><select name=\"file\">";
$dir_handle = @opendir("../boxes") or die("No se puede leer el directorio boxes");
// Loop through the files
while ($file = readdir($dir_handle))
{
        if (!(strpos($file, "~") === FALSE) || (strpos($file, ".php") === FALSE) || $file == ".." || $file == ".") //si enkontramos ~
                continue;
        $file = substr($file, 0, strpos($file, ".php"));
        $_content .= "<option value=\"$file\">$file</option>";
}
// Close
closedir($dir_handle);
$_content .= "</select></td></tr>";
$_content .= "<tr class=row1><td>Izquierda</td><td><input name=\"init_status\" value=\"1\" type=\"radio\"></td></tr>";
$_content .= "<tr class=row2><td>Derecha</td><td><input name=\"init_status\" value=\"2\" type=\"radio\"></td></tr>";
$_content .= "<tr class=row1><td colspan=2><input type=\"submit\" value=\"Agregar\"></td></tr></table></form>";

//agregas la parte de configurar algunos
$_content .= "<h1>Configurar boxes</h1>\n";
$_content .= "Seleccione la box que desea configurar \n";
$_content .= "<form name=config action=\"index.php\">\n";
$_content .= "<input type=\"hidden\" name=\"_module\" value=\"$_module\">\n";
$_content .= "<select name=\"_op\">\n";

//podremos configurar todos los que estén en este directorio y sean .php
//omitir index.php
$dir_handle = @opendir("$_module") or die("No se puede leer el directorio boxes");
// Loop through the files
while ($file = readdir($dir_handle))
{
        if (!(strpos($file, "~") === FALSE) || (strpos($file, ".php") === FALSE) || $file == ".." || $file == ".") //si enkontramos ~
                continue;
        if ($file == "index.php") 
                continue;
        $file = substr($file, 0, strpos($file, ".php"));
        $_content .= "<option value=\"$file\">$file</option>";
}
// Close
closedir($dir_handle);
$_content .= "</select>\n";
$_content .= "<input type=submit value=\"Configurar\">\n";
$_html = $_content;
?>
