<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $user_id, $gid, $empresa_id, $mes;
// if (!$campana_id) $campana_id = 1;

/*include("../../config.php");
include("../../$_includesdir/db/db.php");*/
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_bar.php");


if (!$mes) $mes = 2;

// We need some data
$datax = array();
$datay = array();
$datay2 = array();
$porcentajes = array();
$urls = array();
$sql = "SELECT empresa_id, nombre FROM empresas WHERE 1";
$result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($empresa_id, $nombre) = $db->sql_fetchrow($result))
{
  $empresas[$empresa_id] = $nombre;
  if (strlen($nombre) > $nombre_mas_largo) $nombre_mas_largo = strlen($nombre);
}

$meses = array('ENERO','FEBRERO','MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO','AGOSTO','SEPTIEMBRE','OCTUBRE','NOVIEMBRE','DICIEMBRE');
//buscar cuantos meses hay
$sql = "SELECT DISTINCT(mes) FROM crm_mensual_recobro WHERE 1 ORDER BY mes";
$result2 = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
while(list($mes) = $db->sql_fetchrow($result2))
{
  $sql = "SELECT ini, fin FROM crm_mensual_recobro WHERE  mes='$mes' AND ini!=0 ORDER BY empresa_id";// INI es lo recuperado, FIN es lo entregado
  $result = $db->sql_query($sql) or die("Error<br>".print_r($db->sql_error()));
  $recuperado_mensual = $entregado_mensual = 0;
  while(list($recuperado, $entregado) = $db->sql_fetchrow($result))
  {
    $recuperado_mensual += $recuperado;
    $entregado_mensual += $entregado;
  }
  if ($entregado_mensual > $entregado_mensual_mayor) $entregado_mensual_mayor = $entregado_mensual;
  array_push($datax, "{$meses[$mes-1]}\n(".($entregado_mensual?sprintf("%0.2f",$recuperado_mensual/$entregado_mensual*100):"")."%)");
  array_push($datay, $entregado_mensual);
  array_push($datay2, $recuperado_mensual);
//   array_push($porcentajes, $recuperado_mensual/$entregado*100);
  array_push($urls, "index.php?_module=Estadisticas&_op=graph_total_mensual&mes=$mes");
}


function moneyCallback($aVal) {
    return "$".number_format($aVal);
}

/**/
$tick_size = 1000000;
$graph = new Graph(1024,768,"auto"); 
$graph->SetScale("textlin",0,$entregado_mensual_mayor + $tick_size);
// $graph->SetMargin(60,20,20,30);
$graph->Set90AndMargin(60,30,120,30);

$graph->SetMarginColor("white");

$graph->SetBackgroundImage("themes/fm/files/header.gif",BGIMG_COPY);

$graph->SetColor("#C3C2C0");
$graph->SetColor("white");
$graph->title->SetFont(FF_FONT1,FS_NORMAL,48);
// $graph->title->Set("Recobro 2007");
$graph->title->SetColor("black");

//lineas negras de la parte de atrás
$graph->ygrid->SetColor("black");
$graph->ygrid->Show(true,true);
$graph->ygrid->SetLineStyle('dotted');

$graph->yaxis->SetLabelFormatCallback('moneyCallback'); 
$graph->yaxis->scale->ticks->Set($tick_size);
$graph->yaxis->SetLabelSide(SIDE_RIGHT);
$graph->yaxis->SetPos("max");
$graph->yaxis->SetTickSide(SIDE_LEFT);
$graph->xaxis->SetTickLabels($datax);
// $graph->yaxis->SetTextLabelInterval(1); 


$graph->xaxis->SetFont(FF_FONT1,FS_NORMAL,12);
$graph->yaxis->SetFont(FF_FONT0,FS_NORMAL,12);


$bplot = new BarPlot($datay);
$bplot->SetWidth(1);
$bplot->value->Show();
$bplot->value->SetColor("black","red"); 
$bplot->value->SetFont(FF_FONT1,FS_NORMAL,12);
// $bplot->SetFillColor("#9999FF");
// $bplot->SetFillGradient("#234F6C","#8BC4EA",GRAD_LEFT_REFLECTION);
$bplot->SetFillGradient("#C96A68","#B03A2C",GRAD_LEFT_REFLECTION);
// Set color for the frame of each bar
// $bplot->SetColor("#8BC4EA");//el borde
$bplot->SetColor("white");
$bplot->value->SetFormatCallback('moneyCallback');
$bplot->SetCSIMTargets($urls);


// Create the bar pot
$bplot2 = new BarPlot($datay2);
$bplot2->SetWidth(1);
$bplot2->value->Show();
$bplot2->value->SetColor("black","red"); 
$bplot2->value->SetFont(FF_FONT1,FS_NORMAL,12);
$bplot2->SetValuePos('top');
// $bplot2->SetFillColor("#993366");
// $bplot2->SetFillGradient("#212869","#928DEA",GRAD_LEFT_REFLECTION);
$bplot2->SetColor("white");
$bplot2->SetFillGradient("#F0CECE","#F0928E",GRAD_LEFT_REFLECTION);
// Set color for the frame of each bar
// $bplot2->SetColor("#928DEA");//el borde
$bplot2->value->SetFormatCallback('moneyCallback');
$bplot2->SetCSIMTargets($urls);

$gbplot = new GroupBarPlot(array($bplot,$bplot2));

$graph->Add($gbplot);

//leyendas
// $gbplot->SetCSIMTargets("javascript:history(-1);","Regresar");
// $gbplot->SetLegend('2001','#20','Legend target');
// 
// $graph->Add($gbplot);

$txt=new Text("Regresar",250,-40);
// $txt->SetAngle(90);
$txt->SetCSIMTarget("index.php?_module=$_module","Regresar");
$txt->SetFont(FF_FONT1,FS_NORMAL,12);
// $txt->SetBox('yellow','navy','gray');
$txt->SetColor("red");

$graph->AddText( $txt);




// Finally send the graph to the browser
$graph->StrokeCSIM();
die();
/**/
 ?>