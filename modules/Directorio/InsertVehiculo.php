<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id,$modelo_id,$version_id,$transmision_id,$ano_id,$color_ext,$color_int,$tipo_pint,$nombre,$paterno,$materno;
$modelo=busca_datos($db,'crm_unidades','unidad_id',$modelo_id);
$version=busca_datos($db,'crm_versiones','version_id',$version_id);
$transmision=busca_datos($db,'crm_transmisiones','transmision_id',$transmision_id);
$hoy=date("Y-m-d H:i:s");

$ins="INSERT INTO crm_prospectos_unidades (contacto_id,accesorios,modelo,version,paquete,ano,tipo_pintura,color_exterior,color_interior,modelo_id,version_id,transmision_id,timestamp)
 VALUES ('".$contacto_id."','','".$modelo."','".$version."','".$transmision."','".$ano_id."','".$tipo_pint."','".$color_ext."','".$color_int."',".$modelo_id.",".$version_id.",".$transmision_id.",'".$hoy."');";
$res=$db->sql_query($ins) or die($ins);

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
