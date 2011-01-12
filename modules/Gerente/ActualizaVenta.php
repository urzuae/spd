<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id,$modelo,$modelo_id,$version_id,$transmision_id,$chasis,$precio,$fecha;
$mensaje='Error: Venta No Actualizada';
$contacto_id=$_POST['contacto_id'];
$modelo=$_POST['modelo'];
$modelo_id=$_POST['modelo_id'];
$version_id=$_POST['version_id'];
$transmision_id=$_POST['transmision_id'];
$timestamp=$_POST['timestamp'];
$transmision_id=$_POST['transmision_id'];
$timestamp=$_POST['timestamp'];
$chasis=$_POST['chasis'];
$precio=$_POST['precio'];
$chasis_ant=$_POST['chasis_ant'];
$precio_ant=$_POST['precio_ant'];
$uid=$_POST['uid'];
$precio = remove_money_format2($precio);
$chasis = trim(strtoupper($chasis));
if($chasis != $chasis_ant)
{
    $sql_chasis="select chasis from crm_prospectos_ventas WHERE chasis='".$chasis ."';";
    $res_chasis=$db->sql_query($sql_chasis);
    if($db->sql_numrows($res_chasis) == 0)
    {
        $sql="UPDATE crm_prospectos_ventas AS a SET a.chasis='".$chasis."',a.precio='".$precio."',a.timestamp='$timestamp'
        WHERE a.contacto_id='$contacto_id' AND a.modelo_id='$modelo_id' AND a.version_id='$version_id'
        AND a.transmision_id='$transmision_id' AND a.timestamp='$timestamp';";
        if($db->sql_query($sql))
            $mensaje='Venta Actualizada';
    }
    else
    {
        $mensaje='El chasis '.$chasis.' ya se encuentra registrado';
    }
}
else
{
    $sql="UPDATE crm_prospectos_ventas AS a SET a.precio='".$precio."',a.timestamp='$timestamp' 
    WHERE a.contacto_id='$contacto_id' AND a.modelo_id='$modelo_id' AND a.version_id='$version_id'
    AND a.transmision_id='$transmision_id' AND a.timestamp='$timestamp';";
    if($db->sql_query($sql))
    {
        $mensaje='Venta Actualizada';
    }
}
echo $mensaje;
die();
?>