<?php
if($fecha_ini != "" && $fecha_fin != "")
    $rango_fechas = " and timestmamp between '".$fecha_ini."' and '".$fecha_fin."'";
elseif($fecha_ini != "")
    $rango_fechas = " and timestmamp >= '".$fecha_ini."'";
elseif($fecha_fin != "")
    $rango_fechas = " and timestmamp <= '".$fecha_fin."'";

if($evento_id > 0)
    $rango_fechas .= " and evento_id='".$evento_id."' ";
?>
