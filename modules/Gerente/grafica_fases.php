<?php
if($submit)
{
    $data_titulos=array('Maneja','Firma','Llevatelo');
    $data_valores=array(35,50,17);
    $colors=array('#3e4f88','#ffff99','#800000');
    include ("$_includesdir/jpgraph/jpgraph.php");
    include("$_includesdir/jpgraph/jpgraph_pie.php");
    include("$_includesdir/jpgraph/jpgraph_pie3d.php");
    $graph = new PieGraph(400,450,"auto");
    $graph->title->Set($titulo);
    $graph->title->SetFont(FF_FONT1,FS_BOLD);
    $p1 = new PiePlot3D($data_valores);
    $p1->SetSliceColors($colors);
    $p1->SetSize(.35);
    $p1->SetCenter(.45);
    $p1->SetStartAngle(0);
    $p1->SetLegends($data_titulos);
    $p1->SetLabelType(PIE_VALUE_ABS);
    $p1->value->SetFormat('%d');
    $p1->value->Show();
    $p1->ExplodeAll(20);
    $graph->Add($p1);
    $graph->legend->Pos(0.01,0.99,"right", "bottom");
    //$graph->Stroke($archivo);
    $graph->Stroke();
}

?>
