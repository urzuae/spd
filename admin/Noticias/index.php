<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
 global $db, $box, $button, $del, $submit, $title, $body;

if ($submit)
{
    $sql = "INSERT INTO news (title, body) VALUES ('$title', '$body')";
    $db->sql_query($sql) or die("Error al guardar noticia");
}
if(isset($del) && $del != "")
{
    $db->sql_query("DELETE FROM news WHERE new_id='$del' LIMIT 1") or die("No se pudo borrar");
}

      $_html = "<script>function del(id,name){if (confirm('Esta seguro que desea borrar la noticia: '+name)) location.href=('index.php?_module=$_module&del='+id);}</script>";

    $_html .= "<h1>Lista de Noticias</h1>\n<table>\n";
    $_html .= "<thead><tr><td>Título</td><td>Noticia</td><td>Última modificación</td><td colspan=2>Acción</td></tr></thead>";
    $result = $db->sql_query("SELECT new_id, title, body, timestamp FROM news WHERE 1 ORDER BY timestamp") or die("Error al cargar news");
    while (list($id, $title, $body, $timestamp) = htmlize($db->sql_fetchrow($result)))
    {
        if (!($c++ % 2))
            $class = "row1";
        else 
            $class = "row2";
        if (strlen($body) > 50) $body = substr($body, 0, 50)."...";
        $_html .= "<tr class=\"$class\"><td><a href=\"../index.php?_module=Noticias&id=$id\">$title</a></td>"
              ."<td>$body</td>"
              ."<td>$timestamp</td>"
              ."<td><a href=\"index.php?_module=$_module&_op=edit&id=$id\"><img src=\"../img/edit.gif\" onmouseover=\"return escape('Editar')\"  border=0></a></td>"
              ."<td><a href=\"#\" onclick=\"del('$id','$title')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=0></a></td></tr>\n";
        $_html .= "</tr>\n";
    }
    $_html .= "</table>";
    $_html .= "<a href=\"javascript: return void(0);\" onclick=\"document.getElementById('new_new').style.display='inline'\"><img src=\"../img/new.gif\" border=0> Crear una noticia nueva</a>\n";
    $_html .= "<span id='new_new' style=\"display:none;\"><h1>Crear Noticia Nueva</h1>\n";
    $_html .= "<script>function validate(f){if (f.title.value == '' || f.body.value == ''){alert('Llene ambos campos primero');return false;}return true;}</script>\n";
    $_html .= "<form onsubmit=\"return validate(this);\"><input type=hidden name=_module value=\"$_module\"><input type=hidden name=_op value=\"$_op\">\n";
    $_html .= "Titulo: <br><center><input name=\"title\" style=\"width:95%\"><br>\n";
    $_html .= "<textarea name=\"body\" style=\"width:95%\" rows=5></textarea><br>\n";
    $_html .= "<input type=submit name=submit value=\"Crear noticia\"></center>\n";
    $_html .= "</form>\n</span>";
 ?>
