<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp,$chasis;
$_css = $_themedir."/style.css";
$_theme = "";
$buffer="Error: La venta no se pudo eliminar";
$sql="DELETE FROM crm_prospectos_ventas 
      WHERE contacto_id='".$contacto_id."' AND modelo_id='".$modelo_id."' AND version_id='".$version_id."'
      AND transmision_id='".$transmision_id."' AND timestamp='".$timestamp."' AND chasis='".$chasis."';";
if($db->sql_query($sql))
{
    $db->sql_query("insert into crm_campanas_llamadas select * from crm_campanas_llamadas_finalizadas where contacto_id = '$contacto_id'");
    $db->sql_query("delete from crm_campanas_llamadas_finalizadas where contacto_id = '$contacto_id'");
    $buffer="Se ha cancelado la venta";
}
echo $buffer;
die();
?>
