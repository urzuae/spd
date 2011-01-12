<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_includesdir,$fecha_ini,$fecha_fin,$submit;
$tipo=$_REQUEST['tipo'];
$fecha_ini=$_REQUEST["fecha_ini"];
$fecha_fin=$_REQUEST["fecha_fin"];
$id=$_REQUEST["id"];
$evento_id=$_REQUEST["evento_id"];
$titulo="Gr&aacute;fica por Fases";
if($tipo == 2) $titulo="Gr&aacute;fica por Concesionarias";
include_once("funciones.php");
$combo_eventos=Genera_Combo_Eventos($db,$evento_id);
if(isset($submit))
{
    include_once("filtro_fechas.php");
    $array_fases=Regresa_Fases($db);
    $array_concesionarias=Regresa_Concesionarias($db);
    $array_url=array();
    $total_bd=0;

    switch($tipo)
    {
        case 1:
        {
            $sql_count="SELECT COUNT(*) as total_f FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas.";";
            $sql="SELECT fase_id as ids ,count(fase_id) as total FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." GROUP BY fase_id ORDER BY fase_id;";
            $titulo="Gráfica por Fases";
            $tmp_celda="Fases";
            break;
        }
        case 2:
        {
            $sql_count="SELECT COUNT(*) as total_f FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas.";";
            $sql="SELECT gid as ids ,count(gid) as total FROM mfll_contactos_unidades WHERE 1 ".$rango_fechas." GROUP BY gid ORDER BY gid;";
            $titulo="Gráfica por Concesionarias";
            $tmp_celda="Concesionarias";
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

            if($tipo == 1)
            {
                $data_titulos[$ids]=" ".$array_fases[$ids]." (".$total.")";
                $tmp=$array_fases[$ids];
                $archivo_grafica='graficasfases';
            }
            if($tipo == 2)
            {
                $data_titulos[$ids]=" ".$array_concesionarias[$ids]." (".$total.")";
                $tmp=$array_concesionarias[$ids];
                $archivo_grafica='graficasconcesionarias';
            }
            $array_url[]="index.php?_module=Gerente&_op=".$archivo_grafica."&tipo=".$tipo."&fecha_ini=".$fecha_ini."&fecha_fin=".$fecha_fin."&evento_id=".$evento_id."&id=".$ids;
            $promedio=0;
            if($total_bd != 0)
                $promedio=(($total/$total_bd)*100);

            $promedio_total= $promedio_total + $promedio;
            $tabla_resul.="<tr><td align='left'>".$tmp."</td><td align='center'>".$total."</td><td align='right'>".number_format($promedio,2,'.','')."&nbsp;%</td></tr>";
        }
        $tabla_resul.="</tbody><thead><tr><td align='left'>Total:</td><td align='center'>".$totales."</td><td align='right'>".number_format($promedio_total,2,'.','')."&nbsp;%</td></tr></thead></table>";
        include_once("construye_grafica.php");

    }
    else
    {
        $_grafico =  "<span style=\"color:#3e4f88;font-weight:bold;\">No se recuperaron registros con el filtro seleccionado</span>";
    }
}
?>