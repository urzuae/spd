<?php
global $db,$_includesdir,$fecha_ini,$fecha_fin,$id,$tipo;
include_once("funciones.php");
include_once("filtro_fechas.php");
$array_vendedores=Regresa_Vendedores($db);
$total_bd=0;
$titulo=" Gráfico por vendedores";
$sql_count="SELECT COUNT(*) as total_f FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND gid=".$id.";";
$sql="SELECT uid as ids ,count(uid) as total FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND gid=".$id." GROUP BY uid ORDER BY uid;";
$tmp_celda="Total de prospectos";
$res_count=$db->sql_query($sql_count);
if($db->sql_numrows($res_count) > 0)
    $total_bd=$db->sql_fetchfield(0,0,$res_count);

$res=$db->sql_query($sql);
if($db->sql_numrows($res) > 0)
{
    $tabla_resul="<table width='100%'>
                    <thead><tr><th>$tmp_celda</th><th>Totales</th><th>Porcentaje</th></tr></thead><tbody>";
    while(list($ids,$total) = $db->sql_fetchrow($res))
    {
        $data_valores[]=$total;
        $data_ids[]=$ids;
        $totales=$totales + $total;
        $data_titulos[$ids]=" ".$array_vendedores[$ids]." (".$total.")";
        $tmp=$array_vendedores[$ids];
        $promedio=0;
        if($total_bd != 0)  $promedio=(($total/$total_bd)*100);
        $promedio_total= $promedio_total + $promedio;
        $tabla_resul.="<tr><td align='left'>".$tmp."</td><td align='center'>".$total."</td><td align='right'>".number_format($promedio,2,'.','')."&nbsp;%</td></tr>";
    }
    $tabla_resul.="</tbody><thead><tr><td align='left'>Total:</td><td align='center'>".$totales."</td><td align='right'>".number_format($promedio_total,2,'.','')."&nbsp;%</td></tr></thead></table>";
    include_once("construye_grafica.php");
    
}
else
{
    $_grafico =  "No hay registros en ese rango de fechas";
}
$url_reg="index.php?_module=Gerente&_op=graficas&tipo=".$tipo."&fecha_ini=".$fecha_ini."&fecha_fin=".$fecha_fin."&submit=sumbit";
$boton_regreso="<a href='".$url_reg."'>Regresar</a>";
?>