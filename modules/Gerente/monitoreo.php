<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $uid, $msg;
$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super > 6)
{
  $_html = "<h1>Usted no es un Gerente</h1>";
} else {

  global $_admin_menu2, $_admin_menu;
  
  $_html .= "<h1>Monitoreo</h1>";
  if ($msg) $_html .= "<script>alert('$msg');</script>";
  
  $_admin_menu2 .= "<br>"
//                   ."<a href=\"index.php?_module=$_module&_op=users\">Usuarios</a><br>"
                  ."<a href=\"index.php?_module=$_module&_op=monitoreo_vendedores\">Vendedoress</a><br>"
                  ."<a href=\"index.php?_module=$_module&_op=monitoreo_prospectos\">Prospectos</a><br>"
                    ;

}
 ?>