<?php
global $db,$_includesdir,$fecha_ini,$fecha_fin,$id,$tipo;
include_once("funciones.php");
include_once("filtro_fechas.php");
$archivo='modules/Gerente/blanco.png';
$array_fases=Regresa_Fases($db);
$array_unidades=Regresa_Unidades($db);
$array_tipo_pagos=Regresa_Tipos_Pagos($db);
$total_bd=0;
$titulos="Gráficas de la Fase ".$array_fases[$id];

switch($id)
{
    case 1:
    {
        $sql_count="SELECT COUNT(*) as total_f FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND fase_id=".$id.";";
        $sql="SELECT fase_id as ids ,count(fase_id) as total FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND fase_id=".$id." GROUP BY fase_id ORDER BY fase_id;";
        $tmp_celda="Total de prospectos";
        $titulo=" Grafica por No. de Prospectos";
        break;
    }
    case 2:
    {
        $sql_count="SELECT COUNT(*) as total_f FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND fase_id=".$id.";";
        $sql="SELECT unidad_id as ids ,count(unidad_id) as total FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND fase_id=".$id." GROUP BY unidad_id ORDER BY unidad_id;";
        $tmp_celda="Modelo";
        $titulo=" Grafica por Modelos";
        break;
    }
    case 3:
    {
        $sql_count="SELECT COUNT(*) as total_f FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND fase_id=".$id.";";
        $sql="SELECT unidad_id as ids ,count(unidad_id) as total FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." AND fase_id=".$id." GROUP BY unidad_id ORDER BY unidad_id;";
        $tmp_celda="Modelo";
        $titulo=" Grafica por Modelos";
        break;

    }
}
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
        if($id == 1)
        {
            $data_titulos[$ids]=" ".$array_fases[$ids]." (".$total.")";
            $tmp=$array_fases[$ids];
        }
        if($id >= 2)
        {
            $data_titulos[$ids]=" ".$array_unidades[$ids]." (".$total.")";
            $tmp=$array_unidades[$ids];
        }
        $promedio=0;
        if($total_bd != 0)  $promedio=(($total/$total_bd)*100);
        $promedio_total= $promedio_total + $promedio;
        $tabla_resul.="<tr><td align='left'>".$tmp."</td><td align='center'>".$total."</td><td align='right'>".number_format($promedio,2,'.','')."&nbsp;%</td></tr>";
    }
    $tabla_resul.="</tbody><thead><tr><td align='left'>Total:</td><td align='center'>".$totales."</td><td align='right'>".number_format($promedio_total,2,'.','')."&nbsp;%</td></tr></thead></table>";
    include_once("construye_grafica.php");
    if($id == 3)
    {
        $_grafico_t='';
        $titulo=" Grafica por Forma de Pago";
        $data_valores_t=array();
        $data_ids_t=array();
        $data_titulos_t=array();
        $totales=0;
        $promedio_total=0;
        $tmp='';
        $sql="SELECT tipo_pago_id as ids ,count(tipo_pago_id) as total FROM mfll_contactos_unidades
              WHERE 1 ".$rango_fechas." AND fase_id=".$id." GROUP BY tipo_pago_id ORDER BY tipo_pago_id;";
        $tmp_celda="Tipo Pago";
        $res=$db->sql_query($sql);
        if($db->sql_numrows($res) > 0)
        {
            $tabla_resul_t="<table width='100%'>
                    <thead><tr><th>$tmp_celda</th><th>Totales</th><th>Porcentaje</th></tr></thead><tbody>";
            while(list($ids,$total) = $db->sql_fetchrow($res))
            {
                $data_valores_t[]=$total;
                $data_ids_t[]=$ids;
                $totales=$totales + $total;

                $data_titulos_t[$ids]=" ".$array_tipo_pagos[$ids]." (".$total.")";
                $tmp=$array_tipo_pagos[$ids];
                
                $promedio=0;
                if($total_bd != 0)  $promedio=(($total/$total_bd)*100);

                $promedio_total= $promedio_total + $promedio;
                $tabla_resul_t.="<tr><td align='left'>".$tmp."</td><td align='center'>".$total."</td><td align='right'>".number_format($promedio,2,'.','')."&nbsp;%</td></tr>";
            }
            $tabla_resul_t.="</tbody><thead><tr><td align='left'>Total:</td><td align='center'>".$totales."</td><td align='right'>".number_format($promedio_total,2,'.','')."&nbsp;%</td></tr></thead></table>";
            include_once("construye_grafica_p.php");
        }
    }
}
else
{
    $_grafico =  "No hay registros en ese rango de fechas";
}
$url_reg="index.php?_module=Gerente&_op=graficas&tipo=".$tipo."&fecha_ini=".$fecha_ini."&fecha_fin=".$fecha_fin."&submit=sumbit";
$boton_regreso="<a href='".$url_reg."'>Regresar</a>";
?>