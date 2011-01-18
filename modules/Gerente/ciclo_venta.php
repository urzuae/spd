<?php
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $_site_name,$_includesdir,$_modulesdir,$buffer,$buffer_ventas,$buffer_semaforo,$ano_id;
include_once("Grafico_Ciclo_Venta.php");
include_once("Grafico_Ventas.php");
include_once($_includesdir."/fusion/FusionCharts.php");
include_once("funcion_metas.php");
$select_ano=Regresa_Combo_Anos($ano_id);
$array_meses=Regresa_Combo_Meses($array_meses);
if($ano_id=='') $ano_id=date('Y');

$obj           = new Grafico_Ciclo_Venta($db,$uid,$_includesdir);
$buffer        = $obj->Obten_Grafico();
$obj_vtas      = new Grafico_Ventas($db,$uid,$_includesdir,4,$ano_id);
$buffer_ventas = $obj_vtas->Obten_Grafico_Ventas();

$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if ($super == "4" || $super == "6")
{
    include_once("Grafico_Ventas_Anual.php");
    $obj_sem     = new Grafico_Ventas_Anual($db,$uid,$_includesdir,4,$ano_id);
    $buffer_semaforo  = $obj_sem->Obten_Grafico_Ventas();
}
?>