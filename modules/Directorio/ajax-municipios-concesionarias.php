<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $entidad_federativa_id, $municipio;
       
//inicializar cosas
$sql = "SELECT g.gid, g.name FROM groups_municipios AS m, groups AS g WHERE m.entidad_id='$entidad_federativa_id' AND m.municipio='$municipio' AND g.gid=m.gid ORDER BY municipio";
$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
$xml = "<?xml version=\"1.0\" encoding=\"ISO-8859-1\"?>\n<complete>\n";
if ($db->sql_numrows($result) > 0)
{
	$xml .= "<option value=\"\">Seleccione uno</option>\n";
	while (list($gid, $name) = $db->sql_fetchrow($result))
	{
		$xml .= "<option value=\"$gid\"><![CDATA[$name]]></option>\n";
	}
}
$xml .= "</complete>";
header("Content-Type: application/xml; charset=\"ISO-8859-1\"',true");
die($xml);
?>
