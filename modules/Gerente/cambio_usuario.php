<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $uid, $submit, $user, $name, $password, $password2,$email;
$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
// if ($super > 6)
// {
//   $msg = "<h1>Usted no es un Gerente</h1>";
// } else {

  $sql = "SELECT gid, user, name FROM users WHERE uid='$uid' limit 1";
  $result = $db->sql_query($sql) or die($sql);
  list($gid, $user_orig, $name_orig) = $db->sql_fetchrow($result);
  if ($password)
  {
    if ($password != $password2)
    {
      $msg .= "Los passwords no coinciden";
      
    }
    else
    {/*
      //checar si ya existe este usario
      $sql = "SELECT user FROM users WHERE user='$user'";
      $r = $db->sql_query($sql) or die($sql);
      if ($db->sql_numrows($r))
      {
        $msg .= "Este nombre de usuario no está disponible, porfavor introduzca otro.";
      }
      else //ahora si, actualizar
      {*/
        $password = strtoupper($password);
        $sql = "UPDATE users set password=PASSWORD('$password'), name='$name', email='$email' WHERE uid='$uid'";
        $r = $db->sql_query($sql) or die($sql);
        header("location:index.php");
//       }
    }
  }
  
// }

global $_admin_menu;
$_admin_menu = "";
 ?>