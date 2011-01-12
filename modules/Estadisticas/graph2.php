<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id;
if (!$campana_id) $campana_id = 1;
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_bar.php");
// We need some data
$datax = array();
$datay= array();
$percentages = array();
$sql = "SELECT id FROM crm_campanas_llamadas WHERE campana_id='$campana_id'";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
$llamadas_totales = $db->sql_numrows($result);
//buscamos los status
$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE campana_id='0' OR campana_id='$campana_id' ORDER BY orden";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($status_id, $nombre) = $db->sql_fetchrow($result))
{
    $sql = "SELECT id FROM crm_campanas_llamadas WHERE campana_id='$campana_id' AND status_id='$status_id'";
    //falta la campana_id y timestamp
    $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
//     if (strlen($nombre) > 15) $nombre = substr($nombre, 0, 11)."...";
    if ($percentages)array_push($percentages, sprintf("%.2f", $db->sql_numrows($result2) / $llamadas_totales * 100));
    array_push($datax, $nombre);
    if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
    array_push($datay, $db->sql_numrows($result2));
}
// $datay=array(13,25,21,35,31,6);
// $datax=array("Realizada","Pendiente","Activa","No Rot.","Pospuesta","Exitosa");

// Setup the graph. 
$graph = new Graph(600,450,"auto"); 
// $graph->img->SetMargin(40,20,30,20 + $nombre_mas_largo*6);

$graph->SetScale("textlin");
$graph->Set90AndMargin(20 + $nombre_mas_largo*6,20,30,20);
$graph->SetMarginColor("#C3C2C0");
$graph->SetShadow();
// $graph->xaxis->title->Set("Status");
// $graph->yaxis->title->Set("Llamadas");

// Set up the title for the graph
// $graph->title->Set("Llamadas");
// $graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
$graph->title->SetColor("white");

// Setup font for axis
// $graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
// $graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
// $graph->xaxis->SetLabelAngle(90); 

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.9);
$bplot->value->Show();
$bplot->value->SetColor("black","red"); 
$bplot->SetValuePos('top');
// Setup color for gradient fill style 
$bplot->SetFillGradient("#C96A68","#B03A2C",GRAD_LEFT_REFLECTION);

// Set color for the frame of each bar
$bplot->SetColor("white");
$graph->Add($bplot);

// Finally send the graph to the browser
$graph->Stroke();


 ?>