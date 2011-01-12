<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
} 

global $db, $cp_id;
       
//inicializar cosas
$sql = "SELECT DISTINCT(d_mnpi), d_estado FROM cps WHERE d_codigo='$cp_id'";
$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><complete>";
$count = 0;
if ($db->sql_numrows($result) > 0)
{
	list($poblacion,$estado)=$db->sql_fetchrow($result);
    $xml .= "<poblacion>".strtoupper($poblacion)."</poblacion>\n";
    $xml .= "<estado>".strtoupper($estado)."</estado>\n";

}
$xml .= "</complete>";
header("Content-Type: application/xml; charset=\"iso-8859-1\"',true");
die($xml);
?>
