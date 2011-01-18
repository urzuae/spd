<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $_module, $_op, $_do_login,$_modulesdir;
if(isset($_COOKIE['_uid']))
{
    $uid = $_COOKIE['_uid'];
}
else
{
    $uid = 0;//$_GET['uid'];
}
if ($uid)
    list($gid) = htmlize($db->sql_fetchrow($db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1")))
        or die ("Error al buscar el grupo");
else
    $gid = 0;


$_menu = "<br>\n";

$result = $db->sql_query("SELECT user, name, super, gid FROM users WHERE uid='$uid'") or die("Error al buscar nombre de usuario");

list($user, $name, $super, $gid) = htmlize($db->sql_fetchrow($result));

if ($gid == "0001") //gerente de zona
{
    if($super == 2 || $super == 3 )
	{
	    $files["Monitoreo"] = "";
	    $files["&nbsp;&nbsp;&nbsp;&nbsp;Concesionarias"] = "Zona&_op=monitoreo_concesionarias";
	    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos"] = "Zona&_op=monitoreo_concesionarias_prospectos";
	    $files[""] = "";
	    $files["Reportes"] = "Zona&_op=zonas";
	}
    elseif ($super == 4 || $super == 6)
    {
    //$files["Metas"] = "";
    //$files["&nbsp;&nbsp;&nbsp;&nbsp;Crear meta"] = "Gerente&_op=proyeccion";
    //$files["&nbsp;&nbsp;&nbsp;&nbsp;Consultar meta"] = "Gerente&_op=consulta_proyeccion";
    //$files["<p></p>"] = "";
    $files["Usuarios"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Crear vendedor"] = "Gerente&_op=usuario";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Permisos a vendedores"] = "Gerente&_op=administracion_vendedores";
    $files[""] = "";
    $files["Prospectos"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Asignación de prospectos"] = "Gerente&_op=contactos";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos finalizados"] = "Gerente&_op=reasignar";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Carga de prospectos"] = "Gerente&_op=carga_contactos";
    $files["&#32;"] = "";
    $files["Monitoreo"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Ciclo de Venta"] = "Gerente&_op=ciclo_venta";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Vendedores"] = "Gerente&_op=monitoreo_vendedores";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos registrados"] = "Gerente&_op=monitoreo_alta_prospectos";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos"] = "Gerente&_op=monitoreo_prospectos";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Tasa de conversión"] = "Gerente&_op=monitoreo_tasa_de_conversion";
    $files["<b></b>"] = "";
    $files["Reportes"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Campa&ntilde;as"] = "Estadisticas&_op=campanas";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Ciclo de venta"] = "Estadisticas&_op=ciclo";
    }
    elseif($super == 8)
    {
        $files = array(
	    "Capturar prospecto" => "Directorio&_op=contacto",
	    "Prospectos" => "Campanas",
	    "Compromisos" => "Campanas&_op=compromisos",
	    //"Ciclo de Venta" => "Gerente&_op=ciclo_venta"
	);
    }
    else
    {
        $files["Capturar Prospecto"] = "Directorio&_op=seleccionar_concesionaria";
    }
}
else
{
  if ($super == 4 || $super == 6) 
  {
    $files["Usuarios"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Crear usuarios"] = "Gerente&_op=usuario";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Permisos vendedores"] = "Gerente&_op=administracion_vendedores";
    $files[""] = "";
    $files["Prospectos"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Asignación de prospectos"] = "Gerente&_op=contactos";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos finalizados"] = "Gerente&_op=reasignar";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Carga de prospectos"] = "Gerente&_op=carga_contactos";
    $files[""] = "";
    $files["Monitoreo"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Ciclo de Venta"] = "Gerente&_op=ciclo_venta";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Vendedores"] = "Gerente&_op=monitoreo_vendedores";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos registrados"] = "Gerente&_op=monitoreo_alta_prospectos";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Prospectos"] = "Gerente&_op=monitoreo_prospectos";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Tasa de conversión"] = "Gerente&_op=monitoreo_tasa_de_conversion";
    $files[""] = "";
    $files["Reportes"] = "";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Campa&ntilde;as"] = "Estadisticas&_op=campanas";
    $files["&nbsp;&nbsp;&nbsp;&nbsp;Ciclo de venta"] = "Estadisticas&_op=ciclo";

  }
  else
  {
    $files = array("Capturar prospecto" => "Directorio&_op=contacto", "Prospectos" => "Campanas","Compromisos" => "Campanas&_op=compromisos");
  }
}
foreach ($files AS $key => $file)
{
    if ($file)
        $_menu .= "<a href=\"". $_SERVER['PHP_SELF']."?_module=$file\">".$key."</a><br>\n";
    else
        $_menu .= "<b>".$key."</b><br>\n";
}
/*if (($super >= 2 && $super <= 6 ) && $gid != 1) //pegar pdfs
{
  $_menu .= "<a href=\"PCS-Manual-Gerente-CRM.pdf\">Manual Gerente CRM</a><br>\n";
  $_menu .= "<a href=\"PCS-Manual-Gerente-Ventas.pdf\">Manual Gerente Ventas</a><br>\n";
  $_menu .= "<a href=\"PCS-Manual-Vendedor.pdf\">Manual Vendedor</a><br>\n";
  $_menu .= "<a href=\"PCS-Manual-Callcenter.pdf\">Manual Call Center</a><br>\n";
  $_menu .= "<a href=\"PCS-Manual-Hostess.pdf\">Manual Hostess</a><br>\n";
}
elseif ($gid == 1 && $super == 2 )
{
  $_menu .= "<a href=\"PCS-Manual-Gerente-Zona.pdf\">Manual Gerente de Zona</a><br>\n";
}
*/
if ($user)
{
    $_content .= $_menu;
    $_content .= "<br>Bienvenido <br><i>$name</i><br>";
    if ($gid == 1 && $super == 2 )
      $_content .= ($super?"Coordinador de Gerentes de Zona":"Gerente de Zona")."<br>\n";
    elseif ($gid == 1 && ($super == 10 ||$super == 9 ))
      $_content .= "Callcenter Nacional<br>\n";
    elseif ($super == 10)
      $_content .= "Callcenter. Concesionaria $gid<br>\n";
    elseif ($super == 12)
      $_content .= "Hostess. Concesionaria $gid<br>\n";
    elseif($super == 6 || $super == 4)
      $_content .= ($super?"Gerente de la ":"")."Distribuidora $gid<br>\n";
   
    $_content .= "<br><br><a href=\"index.php?_do_logout=1\" class=\"box_content\">Salir</a><br>\n";
}
else if (!$_do_login)
{
//     $_content .= $_menu;
       $_content .= "\n"//<br>Entre a tu cuenta para poder ver más opciones:<br>
        . "<form action=\"index.php\" method=post>\n"
        . "<table style=\"width:100%;\">\n"
        . "<tr><td><input type=\"hidden\" name=\"_module\" value=\"$_module\"></td></tr>"
        . "<tr><td><input type=\"hidden\" name=\"_op\" value=\"$_op\"></td></tr>"
        . "<tr><td>Usuario</td></tr>\n"
        . "<tr><td><input type=\"text\" name=\"_user\" style=\"width:100%;color:black;\"></td></tr>\n"
        . "<tr><td>Clave</td></tr>\n"
        . "<tr><td><input type=\"password\" name=\"_password\" style=\"width:100%\"></td></tr>\n"
        . "<tr><td><center><input type=\"submit\" name=\"_do_login\" value=\"Login\"></center></td></tr>"
        . "</table></form>";
}
else
{
    $_content .= "<br><b>Usuario o password incorrecto</b><br>\n";
    $_content .= "<center><a href=\"javascript: history.go(-1);\" class=\"box_content\">Regresar</a></center><br>\n";
}
?>