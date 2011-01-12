<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp;
$del="DELETE FROM crm_prospectos_unidades WHERE contacto_id=".$contacto_id." AND modelo_id=$modelo_id AND version_id=$version_id AND transmision_id=$transmision_id AND timestamp='$timestamp';";
$res=$db->sql_query($del) or die($del);
include_once("modules/Directorio/visualiza_modelos.php");
echo $buffer;
die();

?>