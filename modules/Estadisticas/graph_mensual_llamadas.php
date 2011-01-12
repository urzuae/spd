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
$status = array();
$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE 1";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($status_id, $nombre) = $db->sql_fetchrow($result))
{
  $status[$status_id] = $nombre;
  if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
}

$sql = "SELECT DISTINCT(status_id) FROM crm_campanas_llamadas_mensual WHERE mes='$mes' ORDER BY status_id";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($status_id) = $db->sql_fetchrow($result))
{
  $sql = "SELECT SUM(cuantos) FROM crm_campanas_llamadas_mensual WHERE mes='$mes' AND status_id='$status_id' ORDER BY status_id";
  $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
  list($cuantos) = $db->sql_fetchrow($result2);
  if ($status_id == -4) {$cobrados = $cuantos; continue;}
  array_push($datax, $status[$status_id]);
  array_push($datay, $cuantos);
}


$graph = new Graph(350,250,"auto"); 
$graph->img->SetMargin(30,10,10,10 + $nombre_mas_largo*5+5);

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
// $bplot->value->Show();
$bplot->value->SetColor("black","red"); 
$bplot->value->SetFont(FF_FONT0,FS_NORMAL,6);
$bplot->SetValuePos('bottom');
// Setup color for gradient fill style 
$bplot->SetFillGradient("#C96A68","#B03A2C",GRAD_LEFT_REFLECTION);
// Set color for the frame of each bar
$bplot->SetColor("white");


$graph->Add($bplot);

$txt=new Text( "Cobrados: $cobrados");
$txt->SetPos( 250,15);
$txt->SetColor( "black");
$graph->AddText( $txt);

// Finally send the graph to the browser
$graph->Stroke();

 ?>