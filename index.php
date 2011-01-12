<?php
/******************************************************************************
 *                                                                            *
 * index.php@z2 en esto está basado la funcionalidad del sistema              *
 *                                                                            *
 * Esto funciona de la siguiente manera:                                      *
 *  - Leer la configuracion para obtener unas variables (p.ej. base de datos) *
 *  - Incluir funciones comunes del archivo main.php                          *
 *  - Ejecutar la funcion do_module()                                         *
 *   - Tratamos de entrar al directorio del modulo que se pidio ($_module)    *
 *   - Cargar la configuracion de ese modulo, si la hay ($_module.cfg.php)    *
 *   - Ejecutar $_op.php                                                      *
 *   - Si hay un tema cargarlo ($_theme@.php) imprimir el _header (y boxes)   *
 *	(Si se quiere desactivar el theme usar unset($_theme) en el arch      *
 *    - El tema se carga ejecutando theme.php y este usa el tema indicado     *
 *   - Leer, interpretar e imprimir el html/$_op.html                         *
 *    - Si no existia ese html imprimir la variable: $_html (del $_op.php)    *
 *   - Si hay un tema imprimir el _footer (y boxes)                           *
 *                                                                            * 
 ******************************************************************************/
// if ($_SERVER[SERVER_PORT] != 443) header('https://www.pcsmexico.com/vw');
define('_IN_MAIN_INDEX', '1');
$PHP_SELF = $_SERVER['PHP_SELF'];
/*$hora = date("Y-m-d h:i");*/
$dias = array("Domingo","Lunes","Martes","Miercoles","Jueves","Viernes","Sábado");
$hora = $dias[date('w')];
$hora .=" ";
$hora .=date('d-m-Y h:i A');

$start_timestamp = date("Y-m-d H:i:s");
//$tipo_usuario = "Administrador";
global $super,$name,$name_usuario,$hora,$usuario_franja;

////////////////////////////// FUNCTIONS ///////////////////////////////////////
//esta funcion se manda llamar kada ke se kiere hacer algo (siempre)
function _do_module($_module){   
	//variables ke estan disponibles en los modulos
    global $PHP_SELF, $_includesdir, $_modulesdir, $_op, $_theme, $_html, $_htmldir, $_module, $_site_title,$_themedir,$_themedir_img,$name,$name_usuario,$hora;    
    //si se manda una _op entonces tratar de usar el archivo del 
    //modulo de nombre _op
	//si existe el modulo ke estamos buskando empezar
    if (file_exists("$_modulesdir/$_module"))
    {
		//si existe el html o el php entonces existe esta op del modulo 
		//(si no pues no se hace nada)
		if (file_exists("$_modulesdir/$_module/$_op.php") || file_exists("$_htmldir/$_op.html"))
        {
			//si existe poner el php kon el nombre op
			if (file_exists("$_modulesdir/$_module/$_op.php"))
				include("$_modulesdir/$_module/$_op.php");
            //si se kiere ke esta página tenga un theme 
            //entonces el php tendrá definida la variable $_theme
            //y komo ya evaluamos el php ya sabemos de esa variable
            if ($_theme != "")
            {
                include_once("$_themedir/theme.php");
                _theme_before_content();
            }
			//si existe imprimir el html 
			//(e interpretar sus variables) de nombre op
			if (file_exists("$_htmldir/$_op.html"))
            {
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


function _ask_to_login($message)
{
    global $_module, $_op, $_themedir, $_theme,$_themedir_img;
    if ($_theme != "") {
        include_once("$_themedir/theme.php");
        _theme_before_content();
    }
    //mostramos login fields
    print("<div class=title>$message   </div><br>"
        . "<div class=title>Login</div><br><table><form action=\"index.php\" method=post>\n"
        . "<input type=\"hidden\" name=\"_module\" value=\"$_module\">"
        . "<input type=\"hidden\" name=\"_op\" value=\"$_op\">"
        . "<tr><td>Usuario</td><td><input type=\"text\" name=\"_user\" onblur=\"\" class=\"texto_acceso\"></td></tr>\n"
        . "<tr><td>Clave</td><td><input type=\"password\" name=\"_password\" class=\"texto_acceso\"></td></tr>\n"
        . "<tr><td colspan=2 align=center><br><input type=\"submit\" name=\"_do_login\" value=\"Login\"></td></tr></form></table>");
    if ($_theme != "")
    _theme_after_content();
}

function _deny_access($message)
{
    global $_op, $_themedir, $_theme,$_themedir_img;
    if ($_theme != "")
    {
        include_once("$_themedir/theme.php");
        _theme_before_content();
    }
    $op="";
    print("<div class=title>$message</div><br>");
    print("<center><a href=\"javascript: history.go(-1)\">Regresar</a></center><br><br>");
    if ($_theme != "")
        _theme_after_content();
}
function _check_access($uid)
{
    global $db, $_module;
    //si no mandaron uid, buscar usuario anonimo
    if ($uid == 0)
        $gid = 0;
    else
    {   //buscar el gid en la db
        list($gid) = $db->sql_fetchrow($db->sql_query("SELECT gid FROM users WHERE uid='$uid'"))
            or die("No se puede obtener gid, &oacute; el usuario esta bloqueado");
    }
    //si esta el modulo relacionado kon el gid en la db entonces tenemos permiso para leer
    $sql = "SELECT module FROM groups_accesses WHERE gid='$gid' AND module='$_module' LIMIT 1";
    $result = $db->sql_query($sql)
        or die("No se puede obtener los accesos del grupo");
    $authorized = $db->sql_numrows($result);
    if ($authorized == 0)
        return false;
    else
        return true;
}

function _check_active($uid)
{
    global $db;
    if ($uid == 0) return true; //el usuario anonimo siempre esta activo
    $sql="SELECT active FROM users WHERE uid='$uid' and active=1;";
    $result = $db->sql_query($sql) or die("No se puede obtener el id de bloqueo");
    if($db->sql_numrows($result) == 0)
        return false;
    else
        return true;
}

function _auth()
{
    //esta función esta como snippet the includes/auth.php y se hace automatikamente
    //si se hace un include de auth.php, solo cambie el return type
    global $db;
    $uid = $_COOKIE[_uid];
    $sql = "SELECT user, password FROM users WHERE uid='$uid' AND active=1";
    $result = $db->sql_query($sql);
    list($user, $password) = $db->sql_fetchrow($result);
    if ($_COOKIE[_user] == md5($user) && $_COOKIE[_password] == md5($password))
        return true;
    else 
        return false;
}


////////////////////////////// INIT ALL ////////////////////////////////////////

require_once("config.php");
require_once("$_includesdir/main.php");
//si no hay uid no se ha hecho login
if (isset($_COOKIE['_uid']))
{
    $uid = $_COOKIE['_uid'];
    //tal vez hay un tema personalizado
    #$sql = "SELECT default_module, theme FROM users_configs WHERE uid='$uid' LIMIT 1";
    $sql = "SELECT default_module FROM users_configs WHERE uid='$uid' LIMIT 1";
    $result = $db->sql_query($sql) or die("Error al cargar configuración personalizada");
    if ($db->sql_numrows($result))
        list($_default_module) = $db->sql_fetchrow($result);
        
}
//else $uid = 0;
$__uid = $uid;
$_themedir = "themes/";
$_imgdir = "img";

//si no hay una definida por default poner la de config.php
//chekar si ya se esta registrado o no para ver cual se pone
if (!isset($_module) || $_module == "")
{
    $_module = $_defaultmodule;
}
//si no hay una definida por default poner index para ke trate 
//de jalar index.php, .html, etc.
if (!isset($_op) || $_op == "") $_op = "index";

/////////////////////////////// MAIN ////////////////////////////////////////
//todo dentro de una funcion para evitar que se puedan acceder variables undef


    $sql = "SELECT super,name FROM users WHERE uid='$uid'";
    $_result = $db->sql_query($sql) or die("Error al consultar user y password en db");
    $super = $db->sql_fetchfield(0, 0, $_result);
    $name = $db->sql_fetchfield(1, 0, $_result);
    if ($super == "4" || $super == "6") {
        $usuario_franja="Bienvenido $name";
    } elseif ($super == "8") {
	$usuario_franja="Bienvenido $name";
    } elseif ($super = null); {
	$usuario_franja=="";
    }

///////USER//////
//estan pidiendo hacer login o logout
$_wrong_user = false;
if (isset($_do_login))
    if ($_do_login == "Login")
        if (!login($_POST['_user'], $_POST['_password']))
            $_wrong_user = true; //para que no le pidamos login otra vez
if (isset($_do_logout))
    logout();

///////BODY///////
if ( (_check_access($uid)) &&  (_check_active($uid)) )
{
    //si sí podemos ejecutar
    //registrar la fecha de la actividad
    if (isset($_COOKIE['_uid']))
    {
        $db->sql_query("UPDATE users SET last_activity=NOW() WHERE uid='".$_COOKIE['_uid']."' LIMIT 1")
        or die("Error al actualizar tabla admins");

        //checamos que el usuario ya haya seteado su password, si no, lo obligamos a hacerlo
        
        $sql = "SELECT uid,super,name FROM users WHERE uid='".$_COOKIE['_uid']."' AND password=PASSWORD(user) AND active=1";
        $_result = $db->sql_query($sql) or die("Error al consultar user y password en db");
        $_user_igual_a_password = $db->sql_numrows($_result);
        if ($_user_igual_a_password > 0) //el password = user
        {
            $_module = "Gerente";
        	$_op = "cambio_usuario";
        }
	$super=$db->sql_fetchfield(1,0,$_result);
	$name =$db->sql_fetchfield(2,0,$_result);
    }
    //todas las variables deklaradas aki
    //inician con underscore ("_") para evitar ke se usen por error
    //definimos algunos directorios para su uso dentro de los htmls
    $_htmldir = "$_modulesdir/$_module/html";
    $_boxdir = "boxes";
    _do_module($_module);
}
else // no tenemos accesos, determinar que mensaje mostrar
{   //si no podemos  avisar y pedir login    
    if ($_wrong_user)
    {
        _deny_access("Error: Usuario o Password incorrecto");

    }
    else
    {
        
            if ($uid == 0)
            {
                _ask_to_login("El módulo que solicita solo puede ser accedido por usuarios registrados");
            }
            else
            {
                _ask_to_login("Su  perfil no tiene acceso a este módulo");
            }
           

    }
}
///////BODY///////
$db->sql_query("insert `load` (uid, module, op, start, stop)values('$__uid', '$_module', '$_op', '$start_timestamp', NOW())") or die(print_r($db->sql_error()));


?>