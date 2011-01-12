<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $gid,$user_id,$ano,$mes;

$mensaje='Error: meta no actualizada';
if( ($gid > 0) && ($user_id > 0) && ($ano > 0))
{
    $del="DELETE FROM crm_proyeccion WHERE gid=".$gid." AND uid=".$user_id." AND YEAR(fecha_inicio)='".$ano."';";
    $res=$db->sql_query($del) or die("Error al eliminar:  ".$del);
    $mensaje='Se ha eliminado la meta';
}
echo $mensaje;
die();
?>