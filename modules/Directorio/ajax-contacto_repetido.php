<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
} 

global $nom, $ap, $am, $mod, $db;
       
//inicializar cosas
$sql = "SELECT c.contacto_id FROM crm_contactos AS c, crm_prospectos_unidades AS u
        WHERE c.nombre = '$nom' AND
              c.apellido_paterno = '$ap' AND
              c.apellido_materno = '$am' AND
              c.contacto_id = u.contacto_id AND
              u.modelo_id = '$mod'";
$result = $db->sql_query($sql) or die("Error al obtener datos del usuario");
$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><complete>";
$count = 0;
if ($db->sql_numrows($result) > 0)
{
    $xml .= "<estatus>existe</estatus>\n";
}
else
    $xml .= "<estatus>no_existe</estatus>\n";
$xml .= "</complete>";
header("Content-Type: application/xml; charset=\"iso-8859-1\"',true");
die($xml);
?>
