<?
  if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $submit, $campana_id, $campana_id2, $gid;
$sql = "SELECT contacto_id, nombre, apellido_paterno, apellido_materno FROM crm_contactos WHERE 1";
$result = $db->sql_query($sql) or die($sql);
while (list($contacto_id, $n, $p, $m) = $db->sql_fetchrow($result))
{
  if (!$p) continue;
  $p_ini = strpos($n, $p);
  $n2 = substr($n, $p_ini + strlen($p) + 1);
//   $n2 = substr($n, strlen($p) + 1);
  echo "1) '$n' - '$p' = '$n2'<br>";
  if ($m)
  {
    $m_ini = strpos($n2, $m);
    $n3 = substr($n2, $m_ini + strlen($m) + 1);
//     $n3 = substr($n2, strlen($m) + 1);
  }
  else $n3 = $n2;
  
  echo "2) '$n2' - '$m' = '$n3'<br>";
  $sql = "UPDATE crm_contactos SET nombre='$n3' WHERE contacto_id='$contacto_id'";
  $db->sql_query($sql) or die("Error");
}


 ?>
