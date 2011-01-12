<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $user_id, $gid, $empresa_id, $fecha_ini, $fecha_fin;
// if (!$campana_id) $campana_id = 1;
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_bar.php");

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

if ($empresa_id)
{
  $result0 = $db->sql_query("SELECT gid FROM groups WHERE empresa_id='$empresa_id'") or die("Error");
  while (list($gid) = $db->sql_fetchrow($result0))
  {
    $result = $db->sql_query("SELECT uid FROM users WHERE gid='$gid'") or die("Error");
    while (list($_uid) = $db->sql_fetchrow($result))
      $where .= " OR user_id='$_uid'";
  }
  $where = " AND (0 $where)";
}
elseif ($gid) //buscar los uid del grupo
{
  $result = $db->sql_query("SELECT uid FROM users WHERE gid='$gid'") or die("Error");
  while (list($_uid) = $db->sql_fetchrow($result))
    $where .= " OR user_id='$_uid'";
  $where = " AND (0 $where)";
}
elseif ($user_id) 
{
  $where = " AND user_id='$user_id'";
}

$where .= "$and_fecha";


// We need some data
$datax = array();
$datay= array();
$percentages = array();
$colores = array("red", "blue", "green", "yellow", "violet", "cyan", "orange", "purple");
$sql = "SELECT id FROM crm_campanas_llamadas WHERE campana_id='$campana_id' $where";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
$llamadas_totales = $db->sql_numrows($result);
//buscamos los status
$sumatoria = 0;
if ($campana_id != 0) $where_campana_id = " AND campana_id='$campana_id'";

$sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE campana_id='0' OR campana_id='$campana_id' ORDER BY orden";
if ($campana_id == 0) $sql = "SELECT status_id, nombre FROM crm_campanas_llamadas_status WHERE 1 ORDER BY orden";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($status_id, $nombre) = $db->sql_fetchrow($result))
{
    if ($status_id != -4) //cobrado
      $where_saldo = " AND c.saldo=c.saldo_original ";
    else $where_saldo = "";
    $sql = "SELECT l.id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id $where_campana_id AND l.status_id='$status_id' $where_saldo $where";
    //falta la campana_id y timestamp
    $result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
//     if (strlen($nombre) > 15) $nombre = substr($nombre, 0, 11)."...";
    if ($llamadas_totales)
    {
      $percent = sprintf("%.1f", $db->sql_numrows($result2) / $llamadas_totales * 100);
      array_push($percentages, $percent);
      $nombre .= " (%$percent)";
    }
    if (!$status_id) //no realizada
      $pendientes = $db->sql_numrows($result2);
    else
    {
      array_push($datax, $nombre);
      if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
      array_push($datay, $db->sql_numrows($result2));
      $sumatoria += $db->sql_numrows($result2);
    }
}
// array_push($datax, "TOTAL");
// array_push($datay, "$sumatoria");
//agregar los pagos parciales, estos no cuentan los COBRADOS (-4)
$sql = "SELECT l.id FROM crm_campanas_llamadas AS l, crm_contactos AS c WHERE l.contacto_id=c.contacto_id $where_campana_id AND c.saldo!=c.saldo_original AND c.saldo!=0 $where";
$result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
array_unshift($datax, "PAGO PARCIAL");
array_unshift($datay, $db->sql_numrows($result2));
$sumatoria += $db->sql_numrows($result2);

// Setup the graph. 
$graph = new Graph(600,450,"auto"); 
$graph->img->SetMargin(40,20,30,20 + $nombre_mas_largo*6);

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