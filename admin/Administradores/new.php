<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $user, $password, $submit;

if ($submit) //crear el usuario
{
    if ($db->sql_numrows($db->sql_query("SELECT admin_name FROM admins WHERE admin_name='$user'")) > 0)
        $error = "<br>Ese admin ya esta registrado en el sistema, intenta otro nombre de admin";
    else 
    {
        $sql = "INSERT INTO admins (`admin_id`, `admin_name`, `password`) VALUES('', '$user', PASSWORD('$password'))";
        $db->sql_query($sql)
            or die("No se pudo agregar el admin ".print_r($db->sql_error()));
        header("location: index.php?_module=$_module");
    }
}
?>