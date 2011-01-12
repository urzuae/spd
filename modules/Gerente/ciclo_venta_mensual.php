<?php
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid,$mes_id,$_site_name,$_includesdir,$_modulesdir,$buffer,$buffer_ventas,$buffer_semaforo,$ano_id;
include_once("Grafico_Ventas_Barra_Horizontales.php");
include_once($_includesdir."/fusion/FusionCharts.php");
include_once("funcion_metas.php");
$obj_vtas      = new Grafico_Ventas_Horizontales($db,$uid,$_includesdir,$ano_id,$mes_id,4);
$buffer_ventas_mes = $obj_vtas->Obten_Grafico_Ventas();
?>