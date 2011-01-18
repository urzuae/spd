<?php
/******************************************************************************
 *                                                                            *
 * index.php@z2 en esto está basado la funcionalidad del sistema              *
 *                                                                            *
 * Esto funciona de la siguiente manera:                                      *
 *  - Leer la configuracion para obtener unas variables (p.ej. base de datos) *
 *  - Incluir funciones comunes del archivo main.php                          *
 *  - Ejecutar la funcion do_module() o mostrar los modulos                   *
 *   - Tratamos de entrar al directorio del modulo que se pidio ($_module)    *
 *   - Ejecutar $_op.php                                                      *
 *   - Leer, interpretar e imprimir el html/$_op.html                         *
 *    - Si no existia ese html imprimir la variable: $_html (del $_op.php)    *
 *                                                                            * 
 ******************************************************************************/

define('_IN_ADMIN_MAIN_INDEX', '1');
global $PHP_SELF, $_site_title, $db, $uid, $title, $super, $_modulesdir, $tipo_usuario,$hora,$usuario_franja;
$PHP_SELF = $_SERVER['PHP_SELF'];
$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$hora = $dias[date('w')];
$hora .=" ";
$hora .=date('d-m-Y h:i A');
$_site_title = "Bienvenida";
$usuario_franja="Bienvenido Administrador";
//$tipo_usuario = "Administrador";

require_once("../config.php");
//definimos algunos directorios para su uso dentro de los htmls
require_once("$_includesdir/main.php");
//require_once("$_includesdir/licencias.php");
if (isset($_module))
    $_htmldir = "$_module/html";
$_themedir = "../themes";
$_imgdir = "../img";
require_once("$_includesdir/main.php");

if (isset($login))
{
	$sql = "SELECT admin_id, admin_name, password FROM admins WHERE admin_name='$_POST[admin_name]' AND password=PASSWORD('$_POST[password]')";
	$result = $db->sql_query($sql);
	if ($db->sql_numrows($result) > 0) //si se encontro en la base de datos
	{
		list ($admin_id, $admin_name, $password) = $db->sql_fetchrow($result);
 		setcookie("_admin_id", $admin_id);
 		setcookie("_admin_name", md5($admin_name));
 		setcookie("_admin_password", md5($password));
 		header("location:index.php"); //volver a abrir la pagina
	}
	else 
         {
              global $_module, $_op, $_themedir, $_theme,$_imgdir,$msg_ciclo;
            if ($_theme != "") {
                include_once("$_themedir/theme.php");
                _theme_before_content();
        }
      //mostramos login fields
      print("<script>alert('Login incorrecto');location.href='index.php';</script>");
      if ($_theme != "")
      _theme_after_content();

  }
}

//en este lugar hay ke usar identificacion del admin
if (isset($_COOKIE['_admin_id']))
    $admin_id = $_COOKIE['_admin_id'];
else 
    $admin_id = 0;
$sql = "SELECT admin_name, password FROM admins WHERE admin_id='$admin_id'";
$result = $db->sql_query($sql);

list($admin_name, $admin_password) = $db->sql_fetchrow($result);

//si no es korrekto pedir ke loggee

if (!isset($_COOKIE['_admin_name']) || !isset($_COOKIE['_admin_password']))
{
    if ($_theme != "") {
        require_once("$_themedir/theme.php");
        global $_admin_menu;
        $_admin_menu = "Por favor ingrese su usuario y contraseña para acceder a esta sección";
        _theme_before_content();
    }
	//mostramos login fields
	print ("<div class=title>Admin login</div><br><table border=\"0\"><form action=\"index.php\" method=post>\n"
		. "<tr><td>Usuario</td><td><input type=\"text\" name=\"admin_name\"></td></tr>\n"
		. "<tr><td>Clave</td><td><input type=\"password\" name=\"password\"></td></tr>\n"
		. "<tr><td colspan=2 align=center><input type=\"submit\" name=\"login\" value=\"Login\"></td></tr></form></table>");
    if ($_theme != "") {
        _theme_after_content();
    }
    die();
}

if (!($_COOKIE['_admin_name'] == md5($admin_name) && $_COOKIE['_admin_password'] == md5($admin_password)))
{
    if ($_theme != "") {
        require_once("$_themedir/theme.php");
        global $_admin_menu;
        $_admin_menu = "Por favor ingrese su usuario y contraseña para acceder a esta sección";
        _theme_before_content();
    }
	//mostramos login fields
	print ("<div class=title>Admin login</div><br><table><form action=\"index.php\" method=post>\n"
		. "<tr><td>Usuario</td><td><input type=\"text\" name=\"admin_name\"></td></tr>\n"
		. "<tr><td>Clave</td><td><input type=\"password\" name=\"password\"></td></tr>\n"
		. "<tr><td colspan=2 align=center><input type=\"submit\" name=\"login\" value=\"Login\"></td></tr></form></table>");
    if ($_theme != "") {
        _theme_after_content();
    }
    die();
}


//todas las variables deklaradas aki 
//inician con underscore ("_") para evitar ke se usen por error



//esta funcion se manda llamar kada ke se kiere hacer algo (siempre)
function do_module($_module) {
	//variables ke estan disponibles en los modulos
	global $PHP_SELF, $_includesdir, $_htmldir, $_themedir, $_op, $_theme, $_html, $_COOKIE,$_imgdir,$msg_ciclo;
	
	//si existe el modulo ke estamos buskando empezar
	if (file_exists("$_module")) {
		
		//si se manda una _op entonces tratar de usar el archivo del 
		//modulo de nombre _op
		//si no hay una definida por default poner index para ke trate 
		//de jalar index.php, .html, etc.
		if (!isset($_op)) $_op = "index";
		
		//si existe el html o el php entonces existe esta op del modulo 
		//(si no pues no se hace nada)
		if (file_exists("$_module/$_op.php") 
		    || file_exists("$_htmldir/$_op.html")) {
			
			//si existe poner el php kon el nombre op
			if (file_exists("$_module/$_op.php"))
				include("$_module/$_op.php");

            if ($_theme != "") {
                include("$_themedir/theme.php");
                global $_admin_menu, $_admin_menu2;
                $_admin_menu = _do_admin_menu();
                _theme_before_content();
            }
			//si existe imprimir el html
			//(e interpretar sus variables) de nombre op
			if (file_exists("$_htmldir/$_op.html")) {
				$_tmpl_file = "$_htmldir/$_op.html";
				$_thefile = implode("", file($_tmpl_file));
                if ($_theme != "") $_thefile = _html_get_body($_thefile); //aseguramos ke no tenga <head>
				//kitar los \' de las '
				for($_i = 0; $_i < strlen($_thefile); $_i++)
				{
				  if($_thefile[$_i] == '"')
				  {
				    $_thefile = substr($_thefile, 0, $_i)."\\\"".substr($_thefile, $_i+1, strlen($_thefile)+1);
				    $_i++;
				  }
				}				
				$_thefile = "\$_r_file=\"".$_thefile."\";";
				eval($_thefile);
				print $_r_file;
			}
			else
            {
                print $_html; //esta variable se imprime si no hay 
                       //un .html, en teoría el .php debe de 
                       //poner su salida aquí
            }
			
            if ($_theme != "")
                _theme_after_content();
		}
		else
			die ("No existe esa operación en el módulo...");

	}else
		die ("No existe ese módulo...");
}


function _do_admin_menu()
{
    global $PHP_SELF,$_licenses_used,$_menu_c;
    $_menu_c = 1;
    $_content = "<br>";
    $_menu = "";
    $files=array("" => "");
    if($_menu_c > 0)
    {
        $files = array("Ciclo de venta" => "",
			"&nbsp;&nbsp;Visualizar Ciclo de venta" => "Campanas",
                       
			"Distribuidores" => "",
				"&nbsp;&nbsp;Administrar Distribuidores" => "Concesionarias",
				//"&nbsp;&nbsp;Carga de Distribuidores" => "Carga de Datos&_op=grupos",
				"&nbsp;&nbsp;Crear Distribuidor" => "Concesionarias&_op=new",

			"Usuarios" => "",
				"&nbsp;&nbsp;Crear Usuarios" => "Usuarios&_op=new",
				"&nbsp;&nbsp;Consultar Usuarios" => "Usuarios",

			"Cat&aacute;logo de Productos" => "",
				"&nbsp;&nbsp;Administraci&oacute;n de Productos" => "Modelos",
				//"&nbsp;&nbsp;Carga de Productos" => "Carga de Datos&_op=modelos",

			"Cat&aacute;logo de Fuentes" => "",
				"&nbsp;&nbsp;Administraci&oacute;n  de fuentes" => "Catalogos",

                       "Prospectos" => "",
                       "&nbsp;&nbsp;Carga de Prospectos" => "Carga de Datos&_op=prospectos",
                       "&nbsp;&nbsp;Reasignar Contactos" => "Contactos&_op=contactos",
                       "&nbsp;&nbsp;Cancelar Contactos" => "Contactos&_op=cancelar",

                       "Monitorear" => "",
                       "&nbsp;&nbsp;Asignación" => "Monitoreo&_op=monitoreo_concesionarias_asignacion",
                       "&nbsp;&nbsp;Reasignación" => "Monitoreo&_op=monitoreo_concesionarias_reasignacion",
                       "&nbsp;&nbsp;Seguimiento" => "Monitoreo&_op=seguimiento_prospectos",
                       "&nbsp;&nbsp;Distribuidores" => "Monitoreo&_op=monitoreo_concesionarias",
                       "&nbsp;&nbsp;Tasa de conversion" => "Monitoreo&_op=monitoreo_tasa_de_conversion",
                       "Reportes" => "",
                       "&nbsp;Campa&ntilde;as" => "",
                       "&nbsp;&nbsp;Reporte de Avance" => "Reportes&_op=campanas_avance",
                       "&nbsp;&nbsp;Reporte de Penalizaciones" => "Reportes&_op=penalizacion_usuarios",
                       "&nbsp;Estadisticas" => "",
                       "&nbsp;&nbsp;Gr&aacute;ficos" => "Reportes&_op=autos",
                       "&nbsp;&nbsp;Status Distribuidor" => "Reportes&_op=status_concesionaria",
                       "&nbsp;&nbsp;Prospectos <b>No</b> asignados" => "Reportes&_op=prospectos_no_asignados",
                       "&nbsp;Reportes" => "",
                       "&nbsp;&nbsp;Cantidad de Ventas" => "Reportes&_op=pdf_cantidad_ventas_concretadas_por_vendedor",
                       "&nbsp;&nbsp;Cancelaciones de Ventas" => "Reportes&_op=pdf_cancelaciones_ventas",
                       "&nbsp;&nbsp;Motivos de cancelaciones de Ventas" => "Reportes&_op=pdf_cancelaciones_ventas_motivos"
                    );
    }


        $i=0;
	foreach ($files AS $key=>$file)
	{
            if ($file)
            
                $_menu .= "<a href=\"$PHP_SELF?_module=$file\">".$key."</a><br>\n";
	  else
	    $_menu .= "<BR><B>".$key."</B><br>\n";
	}
	$_content .= $_menu;
        //$_content .= "<h2>Panel de Control</h2>";
        $files = array("Documentos de Apoyo","Respaldar");
    /*foreach ($files as $file)
        $_content .= "<a href=\"$PHP_SELF?_module=$file\">$file</a><br>\n";*/

    $_content .= "<br><a href=\"$PHP_SELF?_logout=1\">Salir</a>";
    return $_content;

}
if (isset($_module)) 
{ 
    if (isset($_COOKIE['_admin_id'])) 
        $db->sql_query("UPDATE admins SET last_activity=NOW() WHERE admin_id='".$_COOKIE['_admin_id']."' LIMIT 1")
            or die("Error al actualizar tabla admins");
	do_module($_module);
}
else //mostrar el menu de modulos ke se pueden administrar
{
    if (isset($_logout))
    {
        setcookie ("_admin_password", "", $time-3200);
        setcookie ("_admin_name", "", $time-3200);
        setcookie ("_admin_id", "", $time-3200);
        header("location: $PHP_SELF");
    }
    else
    {
        if ($_theme != "")
        {
            require_once("$_themedir/theme.php");
            global $_admin_menu;
            $_admin_menu = _do_admin_menu();
            _theme_before_content();
        }

        /*$msg_admin='<span class="parrafo">El <b>administrador</b> del sitio es un rol vital, ya que permite configurar todas las variables disponibles del sistema, crear gerentes, distribuidoras/matrices, cambiar contrase&ntilde;as, y ver los resultados de las ventas a traves de los reportes generados por el sistema.</br>
                Un usuario con este rol tiene el nivel m&aacute;s alto de acceso posible a la aplicaci&oacute;n.<br></span>';

        $msg_gte="<span class='parrafo'>El <b>gerente</b> es el rol creado para administrar una distribuidora/matriz, permite crear y administrar usuarios, asignar los prospectos a los vendedores, monitorear prospectos y revisar los reportes generados por la distribuidora/matriz.</br>
              Los gerentes también pueden reasignar el prospecto de un vendedor y asignarselo a otro, buscando una mejor distribución del trabajo o un vendedor especializado.<br></span>";

        $msg_ven="<span class='parrafo'>El rol de <b>vendedor</b> permite ingresar nuevos prospectos, darles seguimiento programando actividades como llamada telefónicas, visitas o correos electrónicos, también permite avanzar a los prospectos en el ciclo de ventas y cerrar los prospectos al concretar la venta.</br>
              Este rol también puede cancelar un prospecto en caso de ser necesario.<br></span>";
	*/
	//Tabla para alojar las instrucciones y links
	//$inst_1='<span class"instrucciones"><a href="http://pcsmexico.com/salesfunnel/ged/admin/index.php?_module=Campanas">Configura</a> ciclo de ventas<br><a href="http://pcsmexico.com/salesfunnel/ged/admin/index.php?_module=Concesionarias">Dar de alta</a>distribuidor<br><a href="http://pcsmexico.com/salesfunnel/ged/admin/index.php?_module=Modelos">Dar de alta</a>productos</span>';

        //mostramos bienvenida
        $sql = "SELECT admin_name, last_login, last_activity, logged_from FROM admins WHERE admin_id='$_admin_id'";
        list($admin, $lastlogin, $lastactivity, $logged_from) = $db->sql_fetchrow($db->sql_query($sql)) or die("Error");

        /*print("
	      <div style= padding-left:70;>
	      </br>
	      <h1>Bienvenido</h1>
	      </br>
	      <table cellpadding=\"4\" width=\"60%\" align=\"center\">
		<tr>
		   <td class='parrafo'>Bienvenido a su sistema <b>Sales Funnel</b>, usted esta ingresando con el perfil de administrador sistema sales funnel, deberá seguir las instrucciones que a continuación se listan para configurar la solución de acuerdo a sus necesidades.
                   </td>
                </tr>
              </table>
              </br>
	      <h3>Roles del Sistema Sales Funnel</h3>
	      </br>
              <table cellpadding=\"4\" width=\"60%\" align=\"center\">
		<tr class=\"row1\" height=\"100\"><th width=\"20%\"><h3>Administrador sistema Sales Funnel</h3></th><td>".$msg_admin."</td></tr>
                <tr class=\"row2\" height=\"100\"><th><h3>Gerente de ventas</h3></th><td>".$msg_gte."</td></tr>
                <tr class=\"row1\" height=\"100\"><th><h3>Vendedor</h3></th><td>".$msg_ven."</td></tr>
	      </table>
	      </br>
	      </br>
	</div>");*/
	print("
		<div class=\"container\" style=\"padding-top:70px;\">
			<h1>Bienvenido</h1>
			<br/>
			<div>
				<h2>Bienvenido al Sistema de Prospección de Licencias</h2>
			</div>
		</div>
	      ");

        //actualizar el ultimo login
        $from = $_SERVER['REMOTE_ADDR'];
        if ($_SERVER['REMOTE_HOST'] != "") $from .= "->".$_SERVER['REMOTE_HOST'];
        $db->sql_query("UPDATE admins SET last_login=NOW(), logged_from='$from' WHERE admin_id='$admin_id' LIMIT 1");
        if ($_theme != "") {
            _theme_after_content();
        }
    }
}
?>