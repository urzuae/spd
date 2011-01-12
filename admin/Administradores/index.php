<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $del;

if(isset($del) && $del != "")
{
    $db->sql_query("DELETE FROM admins WHERE admin_id='$del' LIMIT 1") or die("No se pudo borrar");
}

//lista de usuarios
$_html = "<script>function del(id,name){if (confirm('Esta seguro que desea borrar al admin '+name)) location.href=('index.php?_module=$_module&del='+id);}</script>";

$_html .= "<div class=title>Lista de Admins</div><br>";
$_html .= "La siguiente es una lista de los administradores del sistema.<br><br>";
$_html .= "<table cellspacing=1 cellpadding=2 width='60%' align='center'>";
$_html .= "<thead><tr style=\"font-weight:bold;\"><td>Admin</td><td>Ultimo Registro</td><td>Ultima actividad</td><td colspan=2>Acción</td></tr></thead><tbody>";
$result = $db->sql_query("SELECT admin_id, admin_name, last_activity, last_login FROM admins WHERE 1") OR die("Error al consultar db: ".print_r($db->sql_error()));
while (list($id, $name, $last_activity, $last_login) = htmlize($db->sql_fetchrow($result))) {
    $_html .=  "<tr class=\"row".(($c++%2)+1)."\"><td>$name</td><td>$last_login</td><td>$last_activity</td>"
              ."<td><a href=\"index.php?_module=$_module&_op=edit&id=$id\"><img src=\"../img/edit.gif\" onmouseover=\"return escape('Editar')\"  border=0></a></td>"
              ."<td><a href=\"#\" onclick=\"del('$id','$name')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=0></a></td></tr>\n";
}
$_html .= "</tbody></table>";
global $_admin_menu2;//<img src=\"../img/new.gif\" border=0>
$_admin_menu2 .= "<table><tr><td></td><td><a href=\"index.php?_module=$_module&_op=new\"> Crear un nuevo Admin</a></td></tr></table>";
?>