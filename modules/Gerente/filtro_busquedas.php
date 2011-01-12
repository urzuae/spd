<?php
$array_filtros=array();
$filtro='';
if($nombre != '')
    $array_filtros[]=" a.nombre LIKE '%".strtoupper($nombre)."%'";
if($apaterno != '')
    $array_filtros[]=" a.apellido_paterno LIKE '%".strtoupper($apaterno)."%'";
if($amaterno != '')
    $array_filtros[]=" a.apellido_materno LIKE '%".strtoupper($amaterno)."%'";


if($evento_id > 0)
    $array_filtros[]=" b.evento_id ='".$evento_id."'";

if($unidad_id > 0)
    $array_filtros[]=" b.unidad_id ='".$unidad_id."'";

if($fase_id > 0)
    $array_filtros[]=" b.fase_id ='".$fase_id."'";

if($tipo_pago_id > 0)
    $array_filtros[]=" b.tipo_pago_id ='".$tipo_pago_id."'";

if($tmp_gid > 0)
    $array_filtros[]=" b.gid ='".$tmp_gid."'";

if($tmp_uid > 0)
    $array_filtros[]=" b.uid ='".$tmp_uid."'";

if($fecha_ini != "" && $fecha_fin != "")
    $array_filtros[]=" b.timestmamp between '".$fecha_ini."' and '".$fecha_fin."'";
elseif($fecha_ini != "")
    $array_filtros[]=" b.timestmamp >= '".$fecha_ini."'";
elseif($fecha_fin != "")
    $array_filtros[]=" b.timestmamp <= '".$fecha_fin."'";



if(count($array_filtros) > 0)
{
    $filtro  = implode (" AND ",$array_filtros);
}
?>