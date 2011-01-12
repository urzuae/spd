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
  array_push($datax, $empresas[$empresa_id]);
  array_push($datay, $ini);
  array_push($datay2, $fin-$ini);
}


$graph = new Graph(350,250,"auto"); 
$graph->img->SetMargin(50,10,10,10 + $nombre_mas_largo*5+5);

$graph->SetScale("textlin");
$graph->SetMarginColor("#C3C2C0");
$graph->SetShadow();
$graph->title->Set("$mes/2007");
$graph->title->SetFont(FF_FONT0,FS_NORMAL,8);
$graph->title->SetColor("white");

$graph->yscale->ticks->SupressZeroLabel(false);
$graph->xaxis->SetTickLabels($datax);
$graph->xaxis->SetLabelAngle(90); 
$graph->xaxis->SetFont(FF_FONT0,FS_NORMAL,6);
$graph->yaxis->SetFont(FF_FONT0,FS_NORMAL,6);


$bplot = new BarPlot($datay);
$bplot->SetWidth(0.9);
$bplot->value->Show();
$bplot->value->SetColor("black","red"); 
$bplot->value->SetFont(FF_FONT0,FS_NORMAL,6);
$bplot->SetValuePos('bottom');
// Setup color for gradient fill style 
$bplot->SetFillGradient("#C96A68","#B03A2C",GRAD_LEFT_REFLECTION);
// Set color for the frame of each bar
$bplot->SetColor("white");
// Create the bar pot
$bplot2 = new BarPlot($datay2);
$bplot2->SetWidth(0.9);
$bplot2->value->Show();
$bplot2->value->SetColor("black","red"); 
$bplot2->value->SetFont(FF_FONT0,FS_NORMAL,6);
$bplot2->SetValuePos('top');
// Setup color for gradient fill style 

// Set color for the frame of each bar
$bplot2->SetColor("white");
$bplot2->SetFillGradient("#F0CECE","#F0928E",GRAD_LEFT_REFLECTION);
$gbplot = new AccBarPlot(array($bplot,$bplot2));

$graph->Add($gbplot);

// Finally send the graph to the browser
$graph->Stroke();

 ?>