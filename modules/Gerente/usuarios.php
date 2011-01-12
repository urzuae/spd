<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid;

$_css = $_themedir."css/".$_theme."/style.css";
$_theme = "";
$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super > 6)
{
	die("<html><head><script>window.close();</script></head></html>");
} else {
	$result2 = $db->sql_query("SELECT uid, user FROM users WHERE gid='$gid' AND super='8'") or die("Error");
	if ($db->sql_numrows($result2) > 0)
	{
		while(list($a_uid, $a_user) = htmlize($db->sql_fetchrow($result2)))
		{
		  $sql = "SELECT count(c.contacto_id) FROM crm_campanas_llamadas AS l, crm_contactos AS c
				  WHERE c.contacto_id=l.contacto_id AND c.uid='$a_uid'";
		  $r3 = $db->sql_query($sql) or die("Error");
		  list($prospectos) = $db->sql_fetchrow($r3);
		  $rows .= "<tr class=\"row".(++$row_class%2+1)."\">
					<td align=\"right\"> $a_user</td>
					<td>$prospectos</td>
				   </tr>";
		}
	}
	else  die("<html><head><script>window.close();</script></head></html>");
}
?>
