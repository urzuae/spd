<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $user_id, $gid, $empresa_id, $mes;
// if (!$campana_id) $campana_id = 1;
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_bar.php");


if (!$mes) $mes = 2;

// We need some data
$datax = array();
$datay = array();
$datay2 = array();
$empresas = array();
$sql = "SELECT empresa_id, nombre FROM empresas WHERE 1";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($empresa_id, $nombre) = $db->sql_fetchrow($result))
{
  $empresas[$empresa_id] = $nombre;
  if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
}

$sql = "SELECT empresa_id, ini, fin FROM crm_mensual_recobro WHERE mes='$mes' AND ini!=0 ORDER BY empresa_id";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($empresa_id, $ini, $fin) = $db->sql_fetchrow($result))
{
  array_push($datax, $empresas[$empresa_id]."\n(".($fin?sprintf("%0.2f",$ini/$fin*100):"")."%)");
  array_push($datay, $fin);
  array_push($datay2, $ini);
  if ($fin > $entregado_mensual_mayor) $entregado_mensual_mayor = $fin;
}


function moneyCallback($aVal) {
    return "$".number_format($aVal);
}
$tick_size = 500000;
$meses = array('ENERO','FEBRERO','MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE');
$graph = new Graph(1024,768,"auto");
$graph->SetScale("textlin",0,$entregado_mensual_mayor + $tick_size);
// $graph->Set90AndMargin(20+ $nombre_mas_largo*5,30,120,30);//
$graph->SetMargin(20+ $nombre_mas_largo*5,30,120,40);//

$graph->SetMarginColor("white");

$graph->SetBackgroundImage("themes/fm/files/header.gif",BGIMG_COPY);

// $graph->SetColor("#C3C2C0");
$graph->SetColor("white");
$graph->title->SetFont(FF_FONT0,FS_NORMAL,8);
$graph->title->SetColor("black");
$graph->title->Set("{$meses[$mes-1]}/2007");


$graph->ygrid->SetColor("black");
$graph->ygrid->Show(true,true);
$graph->ygrid->SetLineStyle('dotted');

$graph->yaxis->SetLabelFormatCallback('moneyCallback'); 
$graph->yaxis->scale->ticks->Set($tick_size);
// $graph->yaxis->SetLabelSide(SIDE_RIGHT);
// $graph->yaxis->SetPos("max");
$graph->yaxis->SetTickSide(SIDE_LEFT);
$graph->xaxis->SetTickLabels($datax);

$bplot = new BarPlot($datay);
$bplot->SetWidth(0.9);
$bplot->value->Show();
$bplot->value->SetColor("black","red"); 
$bplot->value->SetFont(FF_FONT1,FS_NORMAL,8);
$bplot->SetValuePos('top');
$bplot->value->SetFormatCallback('moneyCallback');
// $bplot->SetFillGradient("#234F6C","#8BC4EA",GRAD_LEFT_REFLECTION);
$bplot->SetFillGradient("#C96A68","#B03A2C",GRAD_LEFT_REFLECTION);

// Set color for the frame of each bar
// $bplot->SetColor("#8BC4EA");//el borde
$bplot->SetColor("white");
// Create the bar pot
$bplot2 = new BarPlot($datay2);
$bplot2->SetWidth(0.9);
$bplot2->value->Show();
$bplot2->value->SetColor("black","red"); 
$bplot2->value->SetFont(FF_FONT1,FS_NORMAL,12);
$bplot2->SetValuePos('top');
$bplot2->value->SetFormatCallback('moneyCallback');
// Setup color for gradient fill style 

// Set color for the frame of each bar
// $bplot2->SetFillGradient("#212869","#928DEA",GRAD_LEFT_REFLECTION);
// Set color for the frame of each bar
// $bplot2->SetColor("#928DEA");//el borde
$bplot2->SetColor("white");
$bplot2->SetFillGradient("#F0CECE","#F0928E",GRAD_LEFT_REFLECTION);
$gbplot = new GroupBarPlot(array($bplot,$bplot2));

$graph->Add($gbplot);



$txt=new Text("Regresar",900,150);
// $txt->SetAngle(90);
$txt->SetCSIMTarget("index.php?_module=$_module&_op=graph_total","Regresar");
// $txt->SetFont(FF_FONT1,FS_BOLD);
// $txt->SetBox('yellow','navy','gray');
$txt->SetColor("red");

$graph->AddText( $txt);

// Finally send the graph to the browser
$graph->StrokeCSIM();
die();

 ?>