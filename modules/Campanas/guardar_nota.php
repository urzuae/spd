<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid,  $contacto_id, $submit, $nota, $campana_id;

$_theme = "";

if (isset($nota))
{
  $nota2 = date("d-m-Y");
  
  $sql = "SELECT name FROM users WHERE uid='$uid' LIMIT 1";
$r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
list($nombre_u) = $db->sql_fetchrow($r2);
$nota2 .= ": $nombre_u";
  if ($campana_id) 
  {
            $sql = "SELECT nombre FROM crm_campanas WHERE campana_id='$campana_id' LIMIT 1";
            $r2 = $db->sql_query($sql) or die("Error".print_r($db->sql_error()));
            list($campana) = $db->sql_fetchrow($r2);
			$nota2 .= ": $campana";
  }
  $nota = $nota2."\n".$nota."\n";
  $notanl = str_replace("\n","\\n", $nota);
  $notanl = str_replace("\r","", $notanl);
  $sql = "UPDATE crm_contactos SET nota = concat(nota,'$nota') WHERE contacto_id='$contacto_id' AND uid='$uid'";
  $db->sql_query($sql) or die("Error");
  $js = "window.opener.document.guardar_nota.nota_bak.value+='$notanl';alert('Nota guardada');";
}
  die("<html><head><script>$js window.close();</script></head><body></body><html>");

?>
