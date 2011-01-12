<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $contacto_id,$modelo_id,$version_id,$transmision_id,$ano_id,$modelo_anterior,$version_anterior,$transmision_anterior,$timestamp_anterior;
$modelo=busca_datos($db,'crm_unidades','unidad_id',$modelo_id);
$version=busca_datos($db,'crm_versiones','version_id',$version_id);
$transmision=busca_datos($db,'crm_transmisiones','transmision_id',$transmision_id);
$hoy=date("Y-m-d H:i:s");
$upd="UPDATE crm_prospectos_unidades SET modelo='".$modelo."',version='".$version."',paquete='".$transmision."',ano='".$ano_id."',modelo_id=".$modelo_id.", version_id=".$version_id.", transmision_id=".$transmision_id."
      WHERE contacto_id=".$contacto_id." AND modelo_id=".$modelo_anterior." AND version_id=".$version_anterior." AND transmision_id=".$transmision_anterior." AND timestamp='".$timestamp_anterior."';";
$res=$db->sql_query($upd) or die($upd);
include_once("modules/Directorio/visualiza_modelos.php");
echo $buffer;
die();


function busca_datos($db,$tabla,$campo,$valor)
{
    $dato='';
    $sql="SELECT nombre FROM ".$tabla." WHERE ".$campo."=".$valor.";";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $dato=$db->sql_fetchfield(0,0,$res);
    }
    return $dato;
}
?>
