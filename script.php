<?php
define('_IN_MAIN_INDEX', '1');
//chdir('/var/www/vw');
////////////////////////////// INIT ALL ////////////////////////////////////////
require_once("config.php");
require_once("$_includesdir/main.php");

$_script = $argv[1];
if (file_exists("scripts/".$_script.".php"))
{
  require_once("scripts/".$_script.".php");
}
else 
{
  die("No existe el script $_script\n");
  return 1;
}
?>