<?php
if (!defined('_IN_MAIN_INDEX') && !defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
require_once("$_includesdir/db/db.php");

//ya tenemos seteado un cookie kon datos, entonces signifika ke podemos autentifikar

global $db;
$uid = $_COOKIE[_uid];
$sql = "SELECT user, password FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql);

list($user, $password) = $db->sql_fetchrow($result);

if (!($_COOKIE[_user] == md5($user) && $_COOKIE[_password] == md5($password))) 
	die("No está autorizado a ver está página");

?>
