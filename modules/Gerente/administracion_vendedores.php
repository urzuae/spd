<?
 if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $user, $uid, $submit,$_site_title;
$_site_title = "Permisos de vendedores";
$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
$gid = sprintf("%04d", $gid);
if ($super > 6)
{
  $_html = "<h1>Usted no es un Gerente</h1>";
}
else
{
    if ($submit) //guardar
    {
      	$sql = "SELECT uid FROM users WHERE gid='$gid' AND super = '8'";
      	$result = $db->sql_query($sql) or die($sql);
      	while(list($uid) = $db->sql_fetchrow($result))
        {
          	$sql  = "UPDATE users SET edit_contact = 0,edit_venta=0 WHERE uid = '$uid'";
            $db->sql_query($sql) or die("Error2");
        }
  	  	foreach ($_POST AS $var=>$value)
        {
            if (strpos($var, "editcontact") === 0) //inicia con checkbox
            {
                list($prefix, $uid) = explode("_",$var);
                $sql  = "UPDATE users SET edit_contact = 1 WHERE uid = '$uid'";
                $db->sql_query($sql) or die($sql);
            }
            if (strpos($var, "editventa") === 0) //inicia con checkbox
            {
                list($prefix, $uid) = explode("_",$var);
                $sql  = "UPDATE users SET edit_venta = 1 WHERE uid = '$uid'";
                $db->sql_query($sql) or die($sql);
            }

        }
  	}
	$sql  = "SELECT uid, user, edit_contact,edit_venta FROM users WHERE gid='$gid' AND super = '8'";
	$result = $db->sql_query($sql) or die("Error2");
	while (list($uid, $user, $edit_contact, $edit_venta) = $db->sql_fetchrow($result))
	{			
		if($edit_contact == 0)
			$checked2 = "";
		if($edit_contact == 1)
		 $checked2 = "CHECKED=\"CHECKED\"";

		if($edit_venta == 0)
			$checkedv = "";
		if($edit_venta == 1)
		 $checkedv = "CHECKED=\"CHECKED\"";

		$users .= "<tr class=\"row".($rowclass++%2+1)."\">
		    <td>$uid</td>
                    <td>$user</td>
                    <td><input type=\"checkbox\" name=\"editcontact_$uid\" $checked2></td>
                    <td><input type=\"checkbox\" name=\"editventa_$uid\" $checkedv></td>
                   </tr>";
	}
}
?>