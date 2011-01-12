<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db,$codigo_campana;
$longitud=strlen($codigo_campana) +0;
$xml = "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?><complete>";
if($longitud > 0)
{
    $sql = "SELECT codigo_campana  FROM crm_contactos WHERE codigo_campana='".$codigo_campana."'";
    $result = $db->sql_query($sql) or die($sql);
    $count = 0;    
    if ($db->sql_numrows($result) > 0)
    {
        $xml .= "<campana>El codigo de campaña ya se encuentra registrado.</campana>\n";
    }
    else
    {
        $xml .= "<campana>no_existe</campana>\n";
    }
}
else
{
    $xml .= "<campana>vacio</campana>\n";
}
$xml .= "</complete>";
header("Content-Type: application/xml; charset=\"iso-8859-1\"',true");
die($xml);
?>