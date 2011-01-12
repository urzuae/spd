<?
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db;
$sql = "SELECT llamada_id FROM crm_encuestas_resultados WHERE 1";
$result = $db->sql_query($sql) or die($sql);
while(list($llamada_id) = $db->sql_fetchrow($result))
{
  $sql = "UPDATE crm_campanas_llamadas SET status_id='-1' WHERE id='$llamada_id'";
  $db->sql_query($sql) or die($sql);
  echo $sql."<br>";
  $i++;
}
echo "<hr>$i";
?>