<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $id, $title, $body, $how_many, $from;
$how_many = 5;
if ($from < 1) $from = 0;
$max_length = 200;
if (!$id)
{
    $_html .= "<h1>Lista de Noticias</h1>\n\n";
//     $_html .= "<thead><tr><td>Título</td><td>Noticia</td><td>Última modificación</td><td colspan=2>Acción</td></tr></thead>";
    $result = $db->sql_query("SELECT new_id, title, body, timestamp FROM news WHERE 1 ORDER BY timestamp LIMIT $from, $how_many") or die("Error al cargar news");
    while (list($id, $title, $body, $timestamp) = htmlize($db->sql_fetchrow($result)))
    {

        if (strlen($body) > $max_length) $body = substr($body, 0, $max_length)."...";
        $body = nl2br($body);
        $_html .= "<h1>$title</h1>\n$body<br><br>\n"
                    ."<center>(<a href=\"index.php?_module=$_module&id=$id\">Leer más...</a>)</center>\n"
                    ."<div class=footer>Última modificación: $timestamp</div>";
    }
    $_html .= "<br>\n";
    $_html .= "<br><center>\n";
    $result = $db->sql_query("SELECT new_id FROM news WHERE 1 ORDER BY timestamp ") or die("Error al cargar news");
    $num_news = $db->sql_numrows($result);
    if ($from > 0) $_html .= "<a href=\"index.php?_module=$_module&from=".($from - $how_many)."\">Anterior</a>&nbsp;";
    for ($i = 0; $i < $num_news; $i += $how_many)
    {
        $j++;
        if (!($i >= $from && $i <= ($from + $how_many - 1)))
        {
            $link1 = "<b><a href=\"index.php?_module=$_module&from=".($i)."\">";
            $link2 = "</a></b>";
        }
        else $link1 = $link2 = "";
        $_html .= "&nbsp;$link1$j$link2";
    }
    if (($from + $how_many) < $num_news) 
        $_html .= "&nbsp;<a href=\"index.php?_module=$_module&from=".($from + $how_many)."\">Siguiente</a>";
    $_html .= "</center><br>\n";
//     $_html .= "<a href=\"javascript: return void(0);\" onclick=\"document.getElementById('new_new').style.display='inline'\"><img src=\"img/new.gif\" border=0> Crear una noticia nueva</a>\n";
}
else
{
    $result = $db->sql_query("SELECT new_id, title, body, timestamp FROM news WHERE new_id='$id'") or die("Error al cargar news");
    list($id, $title, $body, $timestamp) = htmlize($db->sql_fetchrow($result));
    $body = nl2br($body);
    $_html .= "<h1>$title</h1>\n$body<br><br>\n"
                    ."<div class=footer>última modificación: $timestamp</div>";
    $_html .= "<br><br><center><a href=\"javascript: history.go(-1);\">Regresar</a></center><br><br>";
}
?>