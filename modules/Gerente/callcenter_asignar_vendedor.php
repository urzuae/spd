<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $user, $uid, $submit;

$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
$gid = sprintf("%04d", $gid);
if ($super > 6)
{
  $_html = "<h1>Usted no es un Gerente</h1>";
} else {

  if ($submit) //guardar
  {
  	$sql = "DELETE FROM users_asigna_vendedor WHERE uid IN (SELECT uid FROM users WHERE gid='$gid')";
  	$db->sql_query($sql) or die($sql);
  	foreach ($_POST AS $var=>$value)
  	{
  		if (strpos($var, "checkbox") === 0) //inicia con checkbox
  		{
  			list($prefix, $uid) = explode("_",$var);
  			$sql = "INSERT INTO users_asigna_vendedor (uid)VALUES('$uid')";
  			$db->sql_query($sql) or die($sql);
  		}
  	}
  }
	
	//$sql  = "SELECT uid, user FROM users WHERE gid='$gid' AND ( user LIKE '%CALLCENTER' OR user LIKE '%HOSTESS')";
    $sql  = "SELECT uid, user FROM users WHERE gid='$gid' AND super != 6 and super!=4;";
	$result = $db->sql_query($sql) or die("Error2");
	while (list($uid, $user) = $db->sql_fetchrow($result))
	{
		$sql  = "SELECT uid FROM users_asigna_vendedor WHERE uid='$uid'";
		$result2 = $db->sql_query($sql) or die("Error3");
		if ($db->sql_numrows($result2) > 0)
			$checked = "CHECKED=\"CHECKED\"";
		else 
			$checked = "";
			
		$users .= "<tr class=\"row".($rowclass++%2+1)."\"><td>$user</td><td><input type=\"checkbox\" name=\"checkbox_$uid\" $checked></td></tr>";
	}

}
 ?>