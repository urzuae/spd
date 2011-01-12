<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $uid, $zid, $cid, $unid, $ciid;
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
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_pie.php");
include("$_includesdir/jpgraph/jpgraph_pie3d.php");
 
if($zid)
{
	$sql2 = "select g.name, g.gid from groups as g, groups_zonas as gz where g.gid = gz.gid and gz.zona_id = $zid";
	$result2 = $db->sql_query($sql2) or die($sql2);
	while(list($zona, $gid) = $db->sql_fetchrow($result2))
		{
		$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid";
		$result3 = $db->sql_query($sql3) or die($sql3);
		while(list($cuenta) = $db->sql_fetchrow($result3))
			{
				$zonas[$zona] = $cuenta;
			}
		}	
}
else
{
$sql = "select nombre, zona_id from crm_zonas";
$result = $db->sql_query($sql) or die($sql);
while(list($zona, $zona_id) = $db->sql_fetchrow($result))
{
	$sql2 = "select gid from groups_zonas where zona_id = $zona_id";
	$result2 = $db->sql_query($sql2) or die($sql2);
	while(list($gid) = $db->sql_fetchrow($result2))
		{
		$cuenta = 0;
		$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid";
		$result3 = $db->sql_query($sql3) or die($sql3);
		while(list($c) = $db->sql_fetchrow($result3))
			{
				$cuenta += $c;
			}
		$zonas[$zona] = $cuenta;
		}
}
}	


foreach ($zonas as $zona=>$cuenta)
	{
	//    $_html .= "$modelo: $cuenta <br>";
	$data[] = $cuenta;
	$campanas[] = "$zona ($cuenta)";
	$total_datos += $cuenta;
	$urls[] = "javascript:alert('$zona: $cuenta');";
	}

if ($total_datos == 0) die("<div style=\"font-family:Arial;font-size:11px;text-align:center;\">Gráfica vacia.<br><a href=\"javascript:history.go(-1);\">Regresar</a></div>");

// Setup the graph. 
$graph = new PieGraph(600,450,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set("Contactos por Zona");
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