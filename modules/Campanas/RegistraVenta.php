<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $contacto_id,$modelo,$modelo_id,$version_id,$transmision_id,$chasis,$precio,$fecha;
$mensaje='Error: Venta No Registrada';
$contacto_id=$_POST['contacto_id'];
$modelo=$_POST['modelo'];
$modelo_id=$_POST['modelo_id'];
$version_id=$_POST['version_id'];
$transmision_id=$_POST['transmision_id'];
$fecha=$_POST['fecha'];
$chasis=$_POST['chasis'];
$precio=$_POST['precio'];
$uid=$_POST['uid'];
$date=date('Y-m-d H:i:s');
$precio = remove_money_format2($precio);
$chasis = trim(strtoupper($chasis));
$sql_chasis="select chasis from crm_prospectos_ventas WHERE chasis='".$chasis ."';";
$res_chasis=$db->sql_query($sql_chasis);
if($db->sql_numrows($res_chasis) == 0)
{
    $sql="INSERT INTO crm_prospectos_ventas (contacto_id,uid,chasis,precio,timestamp,modelo_id,version_id,transmision_id,timestamp_unidades) VALUES ('$contacto_id','$uid','$chasis','$precio','$date','$modelo_id','$version_id','$transmision_id','$fecha');";
    if($db->sql_query($sql))
    {
        $sql = "insert into crm_campanas_llamadas_finalizadas select * from crm_campanas_llamadas where contacto_id = '$contacto_id'";
    	$db->sql_query($sql) or die($sql);
        /*$sql = "insert into crm_contactos_finalizados select * from crm_contactos where contacto_id = '$contacto_id'";
    	$db->sql_query($sql) or die($sql);
    	$sql = "delete from crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
    	$db->sql_query($sql) or die($sql);
    	$sql = "delete from crm_contactos WHERE contacto_id='$contacto_id'";
    	$db->sql_query($sql) or die($sql);*/
        $mensaje='Venta Registrada';
    }
}
else
{
    $mensaje='El chasis '.$chasis.' ya se encuentra registrado';
}
echo $mensaje;
die();
?>