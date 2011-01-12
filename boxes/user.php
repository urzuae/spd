<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $_module, $_op, $_do_login;
$uid = $_COOKIE[_uid];
$result = $db->sql_query("SELECT name FROM users WHERE uid='$uid'")
    or die("Error al buscar nombre de usuario");
list($name) = htmlize($db->sql_fetchrow($result));
if ($name)
{
    $_content .= "Bienvenido <br><i>$name</i><br><br>\n";
    $_content .= "<center><a href=\"index.php?_do_logout=1\" class=\"box_content\">Salir</a></center><br>\n";
}
else if (!$_do_login)
{
    $_content = "\n"
        . "<form action=\"index.php\" method=post>\n"
        . "<table>\n"
        . "<tr><td><input type=\"hidden\" name=\"_module\" value=\"$_module\"></td></tr>"
        . "<tr><td><input type=\"hidden\" name=\"_op\" value=\"$_op\"></td></tr>"
        . "<tr><td>Usuario</td></tr>\n"
        . "<tr><td><input type=\"text\" name=\"_user\" style=\"width:100%\"></td></tr>\n"
        . "<tr><td>Password</td></tr>\n"
        . "<tr><td><input type=\"password\" name=\"_password\" style=\"width:100%\"></td></tr>\n"
        . "<tr><td><center><input type=\"submit\" name=\"_do_login\" value=\"Login\"></center></td></tr>"
        . "</table></form>";
} else
{
    $_content .= $_menu;
    $_content .= "<br><b>Usuario o password incorrecto</b><br>\n";
    $_content .= "<center><a href=\"javascript: history.go(-1);\" class=\"box_content\">Regresar</a></center><br>\n";
}
?>
