<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $campana_id, $campana_id2, $gid;
$sql = "SELECT u.name, g.name, u.super FROM users AS u, groups AS g WHERE g.gid=u.gid";
$result = $db->sql_query($sql) or die($sql);
while (list($n, $g, $sgid) = $db->sql_fetchrow($result))
{
  echo "$n,$g,".($sgid?"Gerente":"")."<br>";
}


 ?>
