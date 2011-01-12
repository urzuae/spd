<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
 global $db, $box, $button, $del, $submit, $title, $body, $id;

if ($submit)
{
    $sql = "UPDATE news SET title='$title', body='$body' WHERE new_id='$id'";
    $db->sql_query($sql) or die("Error al guardar noticia".print_r($db->sql_error()));
    header("location: index.php?_module=$_module");
}

    $result = $db->sql_query("SELECT new_id, title, body, timestamp FROM news WHERE new_id='$id'") or die("Error al cargar news");
    list($id, $title, $body, $timestamp) = htmlize($db->sql_fetchrow($result));
 ?>