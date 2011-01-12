<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $uid,$gid;
if (!$campana_id) $campana_id = 1;
$_theme = "";

if ($fecha_ini) {
	$fecha_ini = date_reverse ( $fecha_ini );
	$where_fecha .= " AND v.timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin) {
	$fecha_fin = date_reverse ( $fecha_fin );
	$where_fecha .= " AND v.timestamp<'$fecha_fin 23:59:59'";
}
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_pie.php");
include("$_includesdir/jpgraph/jpgraph_pie3d.php");


$origenes = array();
$sql = "SELECT u.name, count(v.uid) FROM crm_prospectos_ventas AS v, users AS u where v.uid=u.uid $where_fecha AND u.gid='$gid' group by (v.uid)";
$result = $db->sql_query($sql) or die($sql);
while(list($origen, $cuenta) = $db->sql_fetchrow($result))
{
	$origenes[$origen] = $cuenta;
}



foreach ($origenes as $origen=>$cuenta)
{
//    $_html .= "$modelo: $cuenta <br>";
   $data[] = $cuenta;
   $campanas[] = "$origen ($cuenta)";
   $total_datos += $cuenta;
   $urls[] = "javascript:alert('Vendedor(a) $origen: $cuenta venta(s) realizada(s) ' );";
}


if ($total_datos == 0) die("<div style=\"font-family:Arial;font-size:11px;text-align:center;\">Gr�fica vacia.<br><a href=\"javascript:history.go(-1);\">Regresar</a></div>");

// Setup the graph. 
$graph = new PieGraph(600,450,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set("Total de ventas por vendedor distribuidora $gid");
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->SetSize(.4);
$p1->SetCenter(.4);
$p1->SetStartAngle(0); //positivo para que sea manecillas del reloj y desde que angulo
$p1->SetLegends($campanas);


// Show absolute values
$p1->SetLabelType(PIE_VALUE_ABS); 
$p1->value->SetFormat('%d');
$p1->value->Show(); 


$p1->ExplodeAll(20);

$p1->SetCSIMTargets($urls);

$graph->Add($p1);



$graph->StrokeCSIM();

 ?>