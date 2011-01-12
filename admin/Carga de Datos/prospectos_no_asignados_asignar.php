<?
if(!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
global $db, $submit;

if($submit)
{
    $gids = "";
    foreach($_POST as $name => $value)
    {
        list($chbx, $gid) = explode("_", $name);
        if($chbx != "chbx") //checar los nombres de las variables, si no empiezan con chbx saltarlas
            continue;
        
        if($gids)
            $gids .= ",";
        $gids .= "('$gid')";
    }
    $db->sql_query("TRUNCATE groups_asignar;") or die("Error al truncar");
    
    $sql = "INSERT INTO groups_asignar (gid)VALUES $gids;";
    
    $db->sql_query($sql) or die("Error al insertar");

}

$sql = "SELECT gid FROM groups_asignar";
$r = $db->sql_query($sql) or die($sql);
$gids_asignar = array();
while(list($gid, $name) = $db->sql_fetchrow($r))
{
    $gids_asignar[] = $gid;
}

$sql = "SELECT gid, name FROM groups WHERE gid != '1' ORDER BY gid";
$r = $db->sql_query($sql) or die($sql);
$table_gids = "<table class=\"tablesorter\" align='center'><thead><tr><th>Número</th><th>Nombre</th><th> </th></thead><tbody>";
while(list($gid, $name) = $db->sql_fetchrow($r))
{
    if(in_array($gid, $gids_asignar))
        $selected = " CHECKED";
    else
        $selected = "";
    $table_gids .= "<tr><td>$gid</td><td>$name</td><td><input name=\"chbx_$gid\" id=\"chbx_$gid\" type=\"checkbox\"$selected></td></tr>";
}
$table_gids .= "</tbody></table><br>";
$_html = '
<script>

function alloff() {
toogleall(false);
}
function allon() {
toogleall(true);
}
function toogleall(sw) {
with(document.seleccionar) {
for(i=0;i<elements.length;i++) {
thiselm = elements[i];
//thiselm.checked = !thiselm.checked
thiselm.checked = sw;
}
}
}

  $(document).ready(function(){
  	$("table").tablesorter({
  		widgets: [\'zebra\']
  	});
  });

</script>

' . "<form method=\"post\" name=\"seleccionar\"><input type=\"hidden\" name=\"_module\" value=\"$_module\"><input type=\"hidden\" name=\"_op\" value=\"$_op\">$table_gids" . "<div align=\"center\"><input type=\"submit\" name=\"submit\" value=\"Guardar\">"
."&nbsp;<input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;"
."<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\">"
."</div></form>";

global $_admin_menu2, $_admin_menu;
// $_admin_menu = " ";
$_admin_menu2 .= "<br>
<a href=\"index.php?_module=$_module&_op=grupos\">Concesionarias</a><br>
<a href=\"index.php?_module=$_module&_op=prospectos_no_asignados_asignar\">Calificación de prospectos</a><br>
<a href=\"index.php?_module=$_module&_op=prospectos\">Prospectos</a><br>
<a href=\"index.php?_module=$_module&_op=prospectos_no_asignados\">Prospectos no asignados</a><br>";
/*

<a href=\"index.php?_module=$_module&_op=usuarios\">Usuarios</a><br>
 
 */
?>
