<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $id, $user, $name, $gid, $password, $submit;



if ($submit) //crear el usuario
{
    if ($password == "") //no cambiar password
        $sql = "UPDATE admins SET `admin_name`='$user', WHERE `admin_id`='$id'";
    else 
        $sql = "UPDATE admins SET `admin_name`='$user', `password`=PASSWORD('$password') WHERE `admin_id`='$id'";

    $db->sql_query($sql)
        or die("No se pudo modificar el Admin ".print_r($db->sql_error()));
    header("location: index.php?_module=$_module");

}

$sql = "SELECT admin_name FROM admins WHERE admin_id='$id' LIMIT 1";

list($user) = htmlize($db->sql_fetchrow($db->sql_query($sql)));
?>