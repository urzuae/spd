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

$sql = "SELECT empresa_id, nombre FROM empresas WHERE 1";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
$i = 0;
while(list($empresa_id, $nombre) = $db->sql_fetchrow($result))
{
  $data[$i] = 0;
  $data_total[$i] = 0;

  //todas las campañas de esta empresa
  $sql = "SELECT campana_id FROM groups AS g, crm_campanas_groups AS c WHERE c.gid=g.gid AND g.empresa_id='$empresa_id'";
  $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
  while(list($campana_id) = $db->sql_fetchrow($result2))
  {
    $sql = "SELECT SUM(o.saldo), SUM(o.saldo_original) FROM crm_campanas_llamadas AS c, crm_contactos AS o WHERE c.contacto_id=o.contacto_id AND campana_id='$campana_id'";
    $result3 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    list($importe_unidades_total, $importe_unidades_original) = $db->sql_fetchrow($result3);
    //solo lo de la fecha seleccionado
    $sql = "SELECT SUM(o.saldo) FROM crm_campanas_llamadas AS c, crm_contactos AS o WHERE c.contacto_id=o.contacto_id AND campana_id='$campana_id'$and_fecha";
    $result3 = $db->sql_query($sql) or die("Error<br>$sql<br>".print_r($db->sql_error()));
    list($importe_unidades) = $db->sql_fetchrow($result3);
    if (!$importe_unidades_original) $importe_unidades_original = 0;
    if (!$importe_unidades) $importe_unidades = 0;
    $data[$i] += ($importe_unidades_original - $importe_unidades);
    $data_total[$i] += $importe_unidades_original;
  }
  if ($data[$i]) $percent = sprintf(" (%.1f%%)", $data[$i] / $data_total[$i] * 100);
  else $percent = "";
  array_push($datax, $nombre);
  if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
  array_push($datay, $data[$i]);
  array_push($datay2, $data_total[$i] - $data[$i]);
  $i++;

}
// die(print_r($datay).print_r($datay2));
// $datay=array(13,25,21,35,31,6);
// $datax=array("Realizada","Pendiente","Activa","No Rot.","Pospuesta","Exitosa");

// Setup the graph. 
$graph = new Graph(600,450,"auto"); 
$graph->img->SetMargin(60,20,30,20 + $nombre_mas_largo*6+5);

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
$graph->xaxis->SetLabelAngle(90); 

$graph->SetBackgroundImage("themes/fm/files/fon_pag_interior2.gif",BGIMG_COPY);

// $datay=array(12,8,19,3,10,5);
// $datay2=array(8,2,11,7,14,4);

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.9);
$bplot->value->Show();
$bplot->value->SetColor("black","red"); 
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