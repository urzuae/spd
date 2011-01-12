<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $uid;
if (!$campana_id) $campana_id = 1;
$_theme = "";

if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND timestamp<'$fecha_fin 23:59:59'";
}

$result = $db->sql_query("SELECT gid FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid) = $db->sql_fetchrow($result);

include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_pie.php");
include("$_includesdir/jpgraph/jpgraph_pie3d.php");
// We need some data
$datax = array();
$datay= array();
//buscamos los status


$data=array();
$campanas=array();
$campanas_id=array();
$urls=array();
do
{
  $sql = "SELECT campana_id, nombre, next_campana_id FROM crm_campanas WHERE campana_id='$campana_id'";
  $result = $db->sql_query($sql) or die($sql);
  list($campana_id, $nombre, $next_campana_id) = $db->sql_fetchrow($result);
  if (in_array($campana_id, $campanas_id)) break; //evitar recursivas
  $campanas[] = $nombre;
  $campanas_id[] = $campana_id;
  $sql = "SELECT l.contacto_id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE c.contacto_id=l.contacto_id AND l.campana_id='$campana_id'";//diferentes de finalizados status!=-1
  $result = $db->sql_query($sql) or die($sql);
  $cuantas = $db->sql_numrows($result);//la cantidad de contactos no finalizados
  $data[] = $cuantas;
  $total_datos += $cuantas;
  array_push($urls, "index.php?_module=Estadisticas&_op=graph_iframe&campana_id=$campana_id");
  $campana_id = $next_campana_id;
}
while ($next_campana_id && $ccc++ < 20);

if ($total_datos == 0) die("<div style=\"font-family:Arial;font-size:11px;text-align:center;\">Gráfica vacia.<br><a href=\"javascript:history.go(-1);\">Regresar</a></div>");

// Setup the graph. 
$graph = new PieGraph(600,450,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set("Contactos en el ciclo de campañas");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->SetSize(0.5);
$p1->SetCenter(0.50);
$p1->SetStartAngle(285); //positivo para que sea manecillas del reloj y desde que angulo
$p1->SetLegends($campanas);


// Show absolute values
$p1->SetLabelType(PIE_VALUE_ABS); 
$p1->value->SetFormat('%d');
$p1->value->Show(); 


$p1->ExplodeAll(20);

$p1->SetCSIMTargets($urls);

$graph->Add($p1);



$graph->StrokeCSIM();

echo "<br><center><a href=\"javascript:window.open('index.php?_module=Estadisticas&_op=ciclo', '_parent');\" style=\"font-family: Arial,Helvetica,sans-serif;  font-size: 12px;\">Regresar</a></center>";
 ?>