<? 
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $user, $name, $gid, $sgid, $password, $config, $submit, $super_val,$_licenses,$_licenses_not_used,$_licenses_used;
if ($submit) //crear el usuario
{
    if( $_licenses_not_used <= 0)
    {
        $_html= "<script language='JavaScript'>
                    alert('No se puede crear el usuario, se tiene".$_licenses." licencias compradas, por favor comunicate con el personal de ventas de PCS Mexico');
                    </script>";
    }
    else
    {
        if ($db->sql_numrows($db->sql_query("SELECT name FROM users WHERE user='$user'")) > 0)
            $error = "<br>Ese usuario ya esta registrado en el sistema, intenta otro nombre de usuario";
        else
        {
            $password = strtoupper($password);
            $db->sql_query("INSERT INTO users (`user`, `name`, `gid`, `super`, `password`) VALUES('$user', '$name', '$gid', '$super_val', PASSWORD('$password'))")
                or die("No se pudo agregar el usuario ".print_r($db->sql_error()));
            $uid = $db->sql_nextid();
            //ahora seteamos la configuración por default (copiando la de uid=0 en la db)
            $result = $db->sql_query("SELECT * FROM users_configs WHERE uid='0' LIMIT 1")
            or die("Error al cargar la configuracion por default");
            $row = $db->sql_fetchrow($result);
            //el primer valor sera el uid, así ke lo kambiamos por el nuevo
            $values = "'$uid'";
            $i = 1;
            while (isset($row[$i]))
            {
                $values .= ", '".$row[$i++]."'";
            }
            $sql = "INSERT INTO users_configs VALUES($values)";
            $db->sql_query($sql) or die("Error al crear configuración por default");
            //si se selexiono la kasilla de configurar entonces redirigir a la de configuración personal
            if ($config) $configurarusuario = "&_op=config&id=$uid";
            else $configurarusuario = "";
            header("location: index.php?_module=$_module$configurarusuario");
        }
    }
}
$gselect = "<select name=gid onchange=\"return check_gid();\">";
$result = $db->sql_query("SELECT gid, name FROM groups WHERE 1 order by gid");
while (list($id, $name) = $db->sql_fetchrow($result))
{
    if (!$i++) $selected = "SELECTED"; //seleccionar el primero
    else $selected = "";
    $gselect .= "<option value=\"$id\" $selected>$id - $name</option>";
}
$gselect .= "</select>";
/*TIPOS DE USUARIOS*/
$select_super = "<select name=super_val>";
$result = $db->sql_query("SELECT tipo_id, nombre FROM users_types WHERE 1 order by tipo_id");
while (list($id, $name) = $db->sql_fetchrow($result))
{
    if((trim($name) == 'Gerente de ventas') || (trim($name)  == 'Vendedor'))
    {
        if($name == 'Gerente de ventas') $name='Gerente';
        if (!$i++) $selected = "SELECTED"; //seleccionar el primero
        else $selected = "";
        $select_super .= "<option value=\"$id\" $selected>$name</option>";
    }
}
$select_super .= "</select>";
?>