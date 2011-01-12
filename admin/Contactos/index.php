<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}


  global $_admin_menu2, $_admin_menu,$_site_title;
  $_site_title = "Reasignar contactos";
  $_html .= "<center><h1>Selecciona una opción</h1></center>";
  
  $_admin_menu2 .= "<br>"
//                   ."<a href=\"index.php?_module=$_module&_op=users\">Usuarios</a><br>"
                  ."<a href=\"index.php?_module=$_module&_op=contactos\">Reasignación de prospectos</a><br>"
                    ;


 ?>