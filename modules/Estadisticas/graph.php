<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $origen_id;
if (!$campana_id) $campana_id = 1;

if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND l.timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND l.timestamp<'$fecha_fin 23:59:59'";
}
if ($origen_id)
{
  $and_origen .= " AND c.origen_id='$origen_id'";
}

$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE (campana_id='0' OR campana_id='$campana_id') ORDER BY orden";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($status_id, $nombre) = $db->sql_fetchrow($result))
{
    $sql = "SELECT id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND l.campana_id='$campana_id' AND l.status_id='$status_id' $and_fecha $and_origen";
    //falta la campana_id y timestamp
    $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
    if (strlen($nombre) > 15)
        $nombre = substr($nombre, 0, 11)."...";
    array_push($datax, $nombre);
    array_push($datay, $db->sql_numrows($result2));
    $total_datos += $db->sql_numrows($result2);
}
if ($total_datos == 0)
{
  header("location:img/grafica_vacia.gif");
  die("");
}
print_r($datax);
print_r($datay);

/*include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_pie.php");
include("$_includesdir/jpgraph/jpgraph_pie3d.php");
// We need some data
$datax = array();
$datay= array();
//buscamos los status
$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE (campana_id='0' OR campana_id='$campana_id') ORDER BY orden";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($status_id, $nombre) = $db->sql_fetchrow($result))
{
    $sql = "SELECT id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND l.campana_id='$campana_id' AND l.status_id='$status_id' $and_fecha $and_origen";
    //falta la campana_id y timestamp
    $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
    if (strlen($nombre) > 15) $nombre = substr($nombre, 0, 11)."...";
    array_push($datax, $nombre);
    array_push($datay, $db->sql_numrows($result2));
    $total_datos += $db->sql_numrows($result2);
}
// $datay=array(13,25,21,35,31,6);
// $datax=array("Realizada","Pendiente","Activa","No Rot.","Pospuesta","Exitosa");




// Setup the graph. 
$graph = new PieGraph(600,450,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set("Llamadas");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot3D($datay);
$p1->SetSize(0.5);
$p1->SetCenter(0.50);
$p1->SetStartAngle(285); //positivo para que sea manecillas del reloj y desde que angulo
$p1->SetLegends($datax);


// Show absolute values
$p1->SetLabelType(PIE_VALUE_ABS); 
$p1->value->SetFormat('%d');
$p1->value->Show();


$p1->ExplodeAll(20);

// $p1->SetCSIMTargets($urls);

$graph->Add($p1);
$graph->Stroke();
/*
// Setup the graph. 
$graph = new Graph(600,450,"auto"); 
$graph->img->SetMargin(60,20,30,90);
$graph->SetScale("textlin");
$graph->SetMarginColor("#5988CC");
$graph->SetShadow();

// Set up the title for the graph
$graph->title->Set("Llamadas");
//$graph->title->SetFont(FF_VERDANA,FS_NORMAL,12);
$graph->title->SetColor("white");

// Setup font for axis
//$graph->xaxis->SetFont(FF_VERDANA,FS_NORMAL,10);
//$graph->yaxis->SetFont(FF_VERDANA,FS_NORMAL,10);

// Show 0 label on Y-axis (default is not to show)
$graph->yscale->ticks->SupressZeroLabel(false);

// Setup X-axis labels
$graph->xaxis->SetTickLabels($datax);
//$graph->xaxis->SetLabelAngle(50);

// Create the bar pot
$bplot = new BarPlot($datay);
$bplot->SetWidth(0.6);

// Setup color for gradient fill style 
$bplot->SetFillGradient("#5686CC","#AAC1E8",GRAD_LEFT_REFLECTION);

// Set color for the frame of each bar
$bplot->SetColor("white");
$graph->Add($bplot);

// Finally send the graph to the browser
$graph->Stroke();

*/
 ?>
