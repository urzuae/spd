<?php
global $db,$_includesdir,$fecha_ini,$fecha_fin,$id;
require_once("../$_includesdir/jpgraph/jpgraph.php");
require_once("../$_includesdir/jpgraph/jpgraph_pie.php");
require_once("../$_includesdir/jpgraph/jpgraph_pie3d.php");
$graph = new PieGraph(640,350);
#$graph->SetShadow();
$graph->title->Set($titulo);
$graph->title->SetFont(FF_FONT1,FS_BOLD);
$p1 = new PiePlot3D($data_valores);
$p1->SetAngle(30);
$p1->SetSize(0.5);
$p1->SetCenter(0.45);
$p1->SetHeight(15);
$p1->ExplodeAll(10);
$p1->SetLegends($data_titulos);
$p1->SetCSIMTargets($array_url);
$graph->Add($p1);
$_grafico = $graph->StrokeCSIM('auto','',0, true);
?>