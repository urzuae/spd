<? 
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $del, $_admin_menu2, $gid,$bloquea,$desbloquea,$_site_title,$_licenses,$_licenses_not_used,$_licenses_used;
$_site_title = "Usuarios";
if($desbloquea)
{
   	$db->sql_query("UPDATE users SET active=true WHERE uid=".$desbloquea);
    $error="Se ha desbloqueado elusuario con id:  ".$desbloquea;
}
if($bloquea)
{
    $db->sql_query("update users set active='0' where uid='$bloquea'") or die("No se pudo borrar");
}

if($del)
{
    $sql="SELECT uid,gid,super,name FROM delete_users WHERE uid=".$del.";";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) == 0)
        $db->sql_query("INSERT INTO delete_users ( SELECT uid,gid,super,name FROM delete_users WHERE uid=".$del.");");
    else
    {
        $db->sql_query("UPDATE delete_users SET gid='".$db->sql_fetchfield(1,0,$res)."',super='".$db->sql_fetchfield(2,0,$res)."', name='".$db->sql_fetchfield(3,0,$res)."' WHERE uid='".$del."';");
    }
    $db->sql_query("update crm_contactos set uid='0' where uid='$del'") or die("No se pudo borrar");
    $db->sql_query("DELETE FROM users WHERE uid='$del' LIMIT 1") or die("No se pudo borrar");
}
//lista de usuarios
$_html = "";
$result = $db->sql_query("SELECT u.uid, u.name, u.user, g.name, u.super, u.last_login, u.last_activity, u.logged_from,u.active FROM users AS u, groups AS g WHERE u.gid=g.gid AND u.gid='$gid'") OR die("Error al consultar db: ".print_r($db->sql_error()));
if ($db->sql_numrows($result))
{
  $_html = "<script>function desbloquea(id,name){if (confirm('Esta seguro que desea desbloquear al usuario '+name)) location.href=('index.php?_module=$_module&desbloquea='+id);}</script>";
  $_html .= "<br>
            <table width='40%' align='center' border='0'>
                <thead>
                    <tr>
                        <td align='left' colspan='2' height='30'>Derechos de uso</td>
                    </tr>
                </thead>
                <tbody>
                    <tr class='row2' height='30'>
                        <td>Derechos de uso</td>
                        <td>$_licenses</td>
                    </tr>
                    <tr class='row1' height='30'>
                        <td>Derechos de uso asignados</td>
                        <td>$_licenses_used</td>
                    </tr>
                    <tr class='row2' height='30'>
                        <td>Derechos de uso libres</td>
                        <td>$_licenses_not_used</td>
                    </tr>
                </tbody></table>";
  $_html .= "<table class='tablesorter'>";
  $_html .= "<thead><tr style=\"font-weight:bold;\"><th width='15%'>Usuario</th><th width='15%'>Nombre</th><th width='20%'>Distribuidor</th>";
  $_html .= "<th width='14%'>Ultimo registro</th><th width='14%'>Ultima actividad</th><th width='14%'>Registrado desde</th><th width='10%' colspan=5>Acci&oacute;n</th></tr></thead><tbody>";
  while (list($id, $name, $user, $group, $super, $last_login, $last_activity, $logged_from,$active) = htmlize($db->sql_fetchrow($result))) {
      if ($super <= 6) $user = "<b>$user</b>";
      if (!($c++ % 2))
          $class = "row1";
      else 
          $class = "row2";
      $_html .=  "<tr class=$class><td>$user</td><td style=\"width:50%;\">$name</td><td>$group</td><td>$last_login</td><td>$last_activity</td><td>$logged_from</td>"
                ."<td><a href=\"index.php?_module=$_module&_op=edit&id=$id\"><img src=\"../img/edit.gif\" onmouseover=\"return escape('Editar')\"  border=0></a></td>"
                ."<td><a href=\"#\" onclick=\"bloquea('$id','$name')\"><img src=\"../img/cross.gif\" onmouseover=\"return escape('Bloquear')\"  border=0></a></td>"
                ."<td><a href=\"#\" onclick=\"del('$id','$name')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Borrar')\"  border=0></a></td><td>\n";
        if($active == 0)
        {
            $_html .="<a href=\"#\" onclick=\"desbloquea('$id','$id')\"><img src=\"../img/lock.gif\" width='18' height='18' onmouseover=\"return escape('Desbloquear Usuario')\"  border=0></a>";
        }
        $_html .="</td></tr>\n";
  }
  $_html .= "</tbody></table>";
}
$select_groups = "<select name=\"gid\" onchange=\"document.forms[0].submit();\">";
$result = $db->sql_query("SELECT gid,name FROM groups WHERE 1 ORDER BY gid") or die("Error al cargar grupos");
$select_groups .= "<option value=\"\">Selecciona distribuidor</option>";
while(list($_gid,$name) = $db->sql_fetchrow($result))
{
  if ($_gid == $gid)
    $selected = " SELECTED";
  else
    $selected = "";
  $select_groups .= "<option value=\"$_gid\"$selected>$_gid - $name</option>";
}
$select_groups .= "</select>";
#$_admin_menu2 .= "<table border=0><tr><td></td><td><a href=\"index.php?_module=$_module&_op=new\">Crear un nuevo usuario</a></td></tr>";
#$_admin_menu2 .= "<tr><td></td><td><a href=\"index.php?_module=$_module&_op=config&id=0\">Cambiar la configuraci&oacute;n personal por default</a></td></tr></table>";
?>