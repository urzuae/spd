<?php
global $db,$_includesdir;
$archivo='modules/grafico.png';
require_once("../$_includesdir/jpgraph/jpgraph.php");
require_once("../$_includesdir/jpgraph/jpgraph_pie.php");
require_once("../$_includesdir/jpgraph/jpgraph_pie3d.php");
$graph_t = new PieGraph(640,350);
$graph_t->title->Set($titulo);
$graph_t->title->SetFont(FF_FONT1,FS_BOLD);
$p1_t = new PiePlot3D($data_valores_t);
$p1_t->SetAngle(30);
$p1_t->SetSize(0.5);
$p1_t->SetCenter(0.45);
$p1_t->SetHeight(15);
$p1_t->ExplodeAll(10);
$p1_t->SetLegends($data_titulos_t);
$graph_t->Add($p1_t);
$graph_t->Stroke($archivo);
//$_grafico_t = $graph_t->StrokeCSIM('auto','',0, true);
?>