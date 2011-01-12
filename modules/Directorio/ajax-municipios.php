<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $entidad_federativa_id;
       
//inicializar cosas
$sql = "SELECT DISTINCT(municipio) FROM groups_municipios WHERE entidad_id='$entidad_federativa_id' ORDER BY municipio";
$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><complete>";
if ($db->sql_numrows($result) > 0)
{
	$xml .= "<option value=\"\">Seleccione uno</option>";
	while (list($municipio) = $db->sql_fetchrow($result))
	{
		$xml .= "<option value=\"$municipio\">$municipio</option>";
	}
}
$xml .= "</complete>";
header("Content-Type: application/xml; charset=\"iso-8859-1\"',true");
die($xml);
?>
