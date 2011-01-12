<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $fecha_ini, $fecha_fin;
if (!$campana_id) $campana_id = 1;
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_bar.php");


// We need some data
$datax = array();
$datay2 = array();
$datay= array();
$data = array();
$data_total = array();
$colores = array("red", "blue", "green", "yellow", "violet", "cyan", "orange", "purple");

if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND c.timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND c.timestamp<'$fecha_fin 23:59:59'";
}

$where .= "$and_fecha";

$sql = "SELECT DISTINCT(cuotas_impagadas) FROM crm_contactos WHERE gid!='0' ORDER BY cuotas_impagadas";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
$i = 0;
while(list($ci) = $db->sql_fetchrow($result))
{
//   if ($ci == 0) continue;
//   if ($ci == 1) $ci_where = " AND c.cuotas_impagadas_original<='$ci'";
//   else 
  $ci_where = " AND c.cuotas_impagadas='$ci'";
  $sql = "SELECT c.contacto_id FROM crm_contactos AS c, crm_campanas_llamadas AS l WHERE l.contacto_id=c.contacto_id$ci_where";
  $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
  $cuantas = $db->sql_numrows($result2);
//   if ($data[$i]) $percent = sprintf(" (%.1f%%)", $data[$i] / $data_total[$i] * 100);
//   else $percent = "";
  if (!$cuantas) continue;
  array_push($datax, $ci);//nombre
  array_push($datay, $cuantas);
  $i++;
  $sumatoria += $cuantas;

}/*
print_r($datax);
print_r($datay);*/
$graph = new Graph(600,450,"auto"); 
$graph->img->SetMargin(40,20,30,40);

$graph->SetScale("textlin");
// $graph->Set90AndMargin(40 + $nombre_mas_largo*6,20,30,20);
$graph->SetMarginColor("#C3C2C0");
$graph->SetShadow();
// $graph->xaxis->title->Set("Status");
// $graph->yaxis->title->Set("Llamadas");

// Set up the title for the graph
$graph->title->Set("Llamadas");
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

$graph->SetBackgroundImage("themes/fm/files/fon_pag_interior2.gif",BGIMG_COPY);
// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.9);
$bplot->value->Show();
$bplot->value->SetColor("black","red"); 
$bplot->SetValuePos('top');

// Setup color for gradient fill style 
$bplot->SetFillGradient("#C96A68","#B03A2C",GRAD_LEFT_REFLECTION);
// $bplot->SetFillColor($colores);

// Set color for the frame of each bar
$bplot->SetColor("white");//
$graph->Add($bplot);


$txt=new Text( "Total de registros: $sumatoria");
$txt->SetPos( 440,15);
$txt->SetColor( "black");
$graph->AddText( $txt);


// Finally send the graph to the browser
$graph->Stroke();


 ?>