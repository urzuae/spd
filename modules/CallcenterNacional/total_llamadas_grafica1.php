<?php
if(isset($_REQUEST))
{
    $total_llamadas = $_REQUEST["total_llamadas"];
    $prospectos_trabajados = $_REQUEST["prospectos_trabajados"];
    $_includesdir = "../../includes";

    //LIBRERIAS DE GRAFICOS
    require_once("$_includesdir/jpgraph/jpgraph.php");
    require_once("$_includesdir/jpgraph/jpgraph_pie.php");
    require_once("$_includesdir/jpgraph/jpgraph_pie3d.php");

    $data = array($total_llamadas,$prospectos_trabajados);
    $graph = new PieGraph(640,350);
    $graph->SetShadow();

    $p1 = new PiePlot3D($data);
    $p1->SetAngle(30);
    $p1->SetSize(0.5);
    $p1->SetCenter(0.45);
    $p1->SetHeight(15);
    $p1->ExplodeAll(10);
    $p1->SetLegends(array("Total de llamadas realizadas = $total_llamadas","Total de prospectos trabajados = $prospectos_trabajados"));
    $p1->SetTheme("pastel");
    $p1->SetCSIMTargets(array("index.php?_module=CallcenterNacional&_op=total_llamadas_grafica2","index.php?_module=CallcenterNacional&_op=total_prospectos_trabajados_grafica1"));
    $graph->Add($p1);
    $graph->StrokeCSIM();
    die;
}
?>
