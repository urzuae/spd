<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id;
$contacto_id=$_GET["contacto_id"];
$hoy=date("Y-m-d H:i:s");
$regreso="";
$upd="UPDATE crm_contactos SET fecha_firmado='".$hoy."' WHERE contacto_id=".$contacto_id.";";
if($db->sql_query($upd))
{
    $regreso="&quot;El contrato VWL ya ha sido firmado por el cliente";
}
echo $regreso;
die();
?>
