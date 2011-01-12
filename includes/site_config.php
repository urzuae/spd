<?php
if (!defined('_IN_MAIN_INDEX') && !defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

function get_site_name()
{
  $s = $_SERVER['PHP_SELF'];
  //obtener las ultimas diagonales
  //quitar el admin/ si estamos en el admin
  if (defined('_IN_ADMIN_MAIN_INDEX'))
      $s = preg_replace("/\/admin\//", "/", $s);
  //quitar el index.php
  $s = substr($s, 0, strrpos($s, "/"));
  //quitar el 
  $s = substr($s, strrpos($s, "/") + 1);
  return $s;
}

?>
