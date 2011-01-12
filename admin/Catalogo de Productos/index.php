<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $del;

if($del)
{
     $db->sql_query("DELETE FROM crm_catalogo WHERE art_id='$del'") or die("Error al borrar.");
     if (file_exists("$_module/files/$del.jpg"))
     {
        unlink("$_module/files/$del.jpg");
     }
     if (file_exists("$_module/files/$del.pdf"))
     {
        unlink("$_module/files/$del.pdf");
     }
}

//lista de usuarios
$_html = "<script>function del(id,name){if (confirm('Esta seguro que desea borrar el artículo '+name)) location.href=('index.php?_module=$_module&del='+id);}</script>";
$_html .= "<h1>Catálogo de Productos</h1><br>\n";
$_html .= "Las columnas Imagen y Documento  muestran  si se tienen estos archivos.<br>\n";
$_html .= "La columna Imagen y Documento muestra si se tienen estos archivos.<br>\n";
$_html .= "Si desea agregar algún artículo de un Click en el símbolo de <a href=\"index.php?_module=$_module&_op=edit\"><img border=\"0\" src=\"../img/more.gif\"></a><br><br>\n";
$_html .= $error;
$result = $db->sql_query("SELECT art_id, nombre FROM crm_catalogo WHERE parent_id='0' ORDER BY nombre") OR die("Error al consultar db: ".print_r($db->sql_error()));
$_html .= "<table >\n";
if ($db->sql_numrows($result) > 0)
    $_html .= "<thead><tr style=\"font-weight:bold;\"><td width=\"150\">Nombre</td><td>Imagen</td><td>Documento</td><td colspan=3>Acción</td></tr></thead><tbody>\n";

while (list($art_id, $nombre) = htmlize($db->sql_fetchrow($result))) {
    $img_img = $img_doc = "";
    if (file_exists("$_module/files/$art_id.jpg")) $img_img = "<img src=\"../img/ok.gif\" border=0>";
    if (file_exists("$_module/files/$art_id.pdf")) $img_doc = "<img src=\"../img/ok.gif\" border=0>";
    $_html .=  "<tr class=\"row".(($c++%2)+1)."\"><td><a href=\"index.php?_module=$_module&_op=edit&art_id=$art_id\">$nombre<a></td>"
              ."<td align=\"center\">$img_img</td>"
              ."<td align=\"center\">$img_doc</td>"
              ."<td><a href=\"index.php?_module=$_module&_op=edit&art_id=$art_id\"><img src=\"../img/edit.gif\" onmouseover=\"return escape('Editar')\"  border=0></a></td>"
              ."<td><a href=\"index.php?_module=$_module&_op=edit&parent_id=$art_id\"><img src=\"../img/more.gif\" onmouseover=\"return escape('Agregar')\"  border=0></a></td>"
              ."<td><a href=\"#\" onclick=\"del('$art_id','$nombre')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=0></a></td></tr>\n";
    //ahora los hijos
    $result2 = $db->sql_query("SELECT art_id, nombre FROM crm_catalogo WHERE parent_id='$art_id'  ORDER BY nombre") OR die("Error al consultar db: ".print_r($db->sql_error()));
    while (list($art_id, $nombre) = htmlize($db->sql_fetchrow($result2))) {
        $img_img = $img_doc = "";
        if (file_exists("$_module/files/$art_id.jpg")) $img_img = "<img src=\"../img/ok.gif\" border=0>";
        if (file_exists("$_module/files/$art_id.pdf")) $img_doc = "<img src=\"../img/ok.gif\" border=0>";
        $_html .=  "<tr class=\"row".(($c++%2)+1)."\"><td><a href=\"index.php?_module=$_module&_op=edit&art_id=$art_id\"> -- $nombre<a></td>"
                ."<td align=\"center\">$img_img</td>"
                ."<td align=\"center\">$img_doc</td>"
                ."<td><a href=\"index.php?_module=$_module&_op=edit&art_id=$art_id\"><img src=\"../img/edit.gif\" onmouseover=\"return escape('Editar')\"  border=0></a></td>"
                ."<td></td>"
                ."<td><a href=\"#\" onclick=\"del('$art_id','$nombre')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=0></a></td></tr>\n";
     }
}
//cerrar tabla
$_html .= "</tbody></table>";
global $_admin_menu2;//<img src=\"../img/new.gif\" border=0>
$_admin_menu2 .= "<table><tr><td></td><td><a href=\"index.php?_module=$_module&_op=edit\"> Crear un nuevo artículo</a></td></tr></table>";

?> 
