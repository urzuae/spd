<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $user, $uid,$_site_title, $_licenses, $_licenses_not_used, $_licenses_used;
$_html='';
#checamos licencia
$sql = "SELECT COUNT(*) FROM users WHERE active=1";
$res = $db->sql_query($sql) or die("Error en el count de licencias:  " . $sql);
list($_licenses_used) = $db->sql_fetchrow($res);
$_licenses_not_used = ($_licenses - $_licenses_used);


$_site_title = "Crear un nuevo usuario";
$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
$gid = sprintf("%04d", $gid);
if ($super > 6)
{
  $_html = "<h1>Usted no es un Gerente</h1>";
} else {
  if ($user)
  {
      if ($_licenses_not_used > 0) {
  	  $user =  $gid.$user; //agregarlo la concesionaria y hacer 0 fill
          $sql = "SELECT user FROM users WHERE user='$user'";
          $r = $db->sql_query($sql) or die($sql);
        $sql2 = "SELECT email FROM `users` WHERE super='6' AND uid=$uid LIMIT 1";
        $destinatario = $db->sql_query($sql2) or die($sql2);
        if ($db->sql_numrows($r))
        {
            $error .= "Este nombre de usuario no está disponible, porfavor introduzca otro.";
        }
        else
        {
            $sql = "INSERT INTO users (user,name,gid,password,super)VALUES('".strtoupper($user)."','".strtoupper($user)."','$gid',PASSWORD('".strtoupper($user)."'),'8')";
            $r = $db->sql_query($sql) or die($sql);
            $msg="Usuario ".$user ."   creado. Al acceder a su cuenta, favor de teclear el usuario en el password";

            $cabecera="From: Sales Funnel<noreply@pcsmexico.com>";
            $para="To: $user<$mail1>";
            $mensaje1 =<<<EOBODY
        <html>
        <head>
        <title>Nuevo vendedor creado</title>
        </head>
        <body>
        <img style="margin-left:5px;" src="http://www.pcsmexico.com/salesfunnel/activation/images/sf_small.png" /><br/><br/>
        </br>
        <b>Se le ha dado de alta, las claves de acceso para entrar a su sesión de Vendedor son las siguientes:</b>
        <p>Usuario: <b>{$user}</b> </p>
        <p>Contraseña: <b>{$user}</b> </p>
        <p>Al ingresar por primera con su nueva cuenta, se le solicitara que actualize sus datos.</p>
        </body>
        </html>
EOBODY;

	$mensaje= "
	     <html>
	     <head>
	     <title>Claves de usuario</title>
	     </head>
	     <body>
	     <h1> Se acaba de crear un nuevo vendedor para el sistema Sales Funnel </h1>
	     <p>El nuevo usuario es: .$user y su contraseña es .$user </p>
	     </body>
	     </html>
	     ";
	
	#include ("includes/swift.php");
	
	/*mail("$destinatario",
	     "Claves de usuario",
	     "$mensaje") ;*/
	
	mail("$para","Nuevo vendedor","$mensaje1", "$cabecera");
        }
        $_html= "<script language='JavaScript'>
                alert('".$msg."');
                location.href='index.php?_module=".$_module."&_op=".$_op."';
                </script>";
      }
      else
      {
        $_html= "<script language='JavaScript'>
                alert('No se puede crear el usuario, se terminaron las licencias compradas, por favor comunicate con el personal de ventas de PCS Mexico');
                </script>";
      }
  }
}
 ?>