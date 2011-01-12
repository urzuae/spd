<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $how_many, $from, $orderby, $uid, $_site_title;
$_site_title="Contactos";
$how_many = 25;
if ($from < 1 || !$from) $from = 0;
if (!$orderby) $orderby = "nombre";

$sql = "SELECT contacto_id, nombre, apellido_paterno, apellido_materno FROM crm_contactos WHERE uid = '$uid' ORDER BY $orderby LIMIT $from, $how_many";
$result = $db->sql_query($sql) or die("Error al consultar contactos");

$tabla_contactos .= "<table width=\"100%\" border=0>\n";
$tabla_contactos .= "<thead><tr>"
                    ."<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=nombre\" style=\"color:#ffffff\">Nombre</a></td>"
                    ."<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=apellido_paterno\" style=\"color:#ffffff\">Apellido Paterno</a></td>"
                    ."<td><a href=\"index.php?_module=$_module&_op=$_op&orderby=apellido_materno\" style=\"color:#ffffff\">Apellido Materno</a></td>"
                    ."<td></td></tr></thead>\n";
while (list($contacto_id, $name, $apellido_paterno, $apellido_materno) =
         $db->sql_fetchrow($result))
{
    $tabla_contactos .= "<tr class=\"row".(($c++%2)+1)."\"><td>$name</td><td>$apellido_paterno</td><td>$apellido_materno</td>"
                        ."<td><a href=\"index.php?_module=$_module&_op=contacto&contacto_id=$contacto_id\"><img src=\"img/edit.gif\" onmouseover=\"return escape('Editar')\"  border=></a></td>"
                        ."</tr>\n";
}
$tabla_contactos .= "</table>\n";


    $result = $db->sql_query("SELECT contacto_id FROM crm_contactos WHERE 1") or die("Error al cargar contactos");
    $num_news = $db->sql_numrows($result);
    if ($num_news > $how_many)
    {
        if ($from > 0) 
            $paginacion_contactos .= "<a href=\"index.php?_module=$_module&orderby=$orderby&from=".($from - $how_many)."\">&lt;</a>&nbsp;";
        for ($i = 0; $i < $num_news; $i += $how_many)
        {
            $j++;
            if ($j != ($from/$how_many + 1))
            {
                if (($j > 10 && $num_news > 10*$how_many) && ($j < (($num_news/$how_many)-10)))
                {
                    continue;//corregir esto para que salga mejor
                }
                else if ($j ==10)
                {
                    $paginacion_contactos .= "&nbsp;...";
                    continue;
                }
            }
            if (!($i >= $from && $i <= ($from + $how_many - 1)))
            {
                $link1 = "<a href=\"index.php?_module=$_module&orderby=$orderby&from=".($i)."\">";
                $link2 = "</a>";
            }
            else $link1 = $link2 = "";
            $paginacion_contactos .= "&nbsp;$link1$j$link2";
            if ($j == ($from/$how_many + 1) && (($j > 10 && $num_news > 10*$how_many) && ($j < (($num_news/$how_many)-10)))) $paginacion_contactos .= "&nbsp;...";
        }
        if (($from + $how_many) < $num_news) 
            $paginacion_contactos .= "&nbsp;<a href=\"index.php?_module=$_module&orderby=$orderby&from=".($from + $how_many)."\">&gt;</a>";
//         $_html = "$tabla_contactos$paginacion_contactos<br><a href=\"index.php?_module=$_module&_op=contacto\">Nuevo</a>";
    }
?>
