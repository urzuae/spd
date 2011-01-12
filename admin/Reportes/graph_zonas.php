<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $uid, $zid;
if (!$campana_id) $campana_id = 1;
$_theme = "";
$zonas = array();
$urls = array();
if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $where_fecha .= " AND fecha_importado>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $where_fecha .= " AND fecha_importado<'$fecha_fin 23:59:59'";
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
		$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid".$where_fecha;
		$result3 = $db->sql_query($sql3) or die($sql3);
		while(list($cuenta) = $db->sql_fetchrow($result3))
			{
				$zonas[$gid] = $cuenta;
				$urls[] = "index.php?_module=$_module&_op=graph_grupo&cid=$gid";
			}
		}	
        foreach ($zonas as $zona=>$cuenta)
	{
	//    $_html .= "$modelo: $cuenta <br>";
	$data[] = $cuenta;
	$campanas[] = "$zona ($cuenta)";
	$total_datos += $cuenta;
	}
        $titulo = "Contactos por Zona por Distribuidora";
}
else
{
	$sql = "select nombre, zona_id from crm_zonas";
	$result = $db->sql_query($sql) or die($sql);
	while(list($zona, $zona_id) = $db->sql_fetchrow($result))
	{
		$cuenta = 0;
		$sql2 = "select gid from groups_zonas where zona_id = $zona_id";
		$result2 = $db->sql_query($sql2) or die($sql2);
		while(list($gid) = $db->sql_fetchrow($result2))
			{
			
			$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid".$where_fecha;
			$result3 = $db->sql_query($sql3) or die($sql3);
			while(list($c) = $db->sql_fetchrow($result3))
				{
					$cuenta = $cuenta + $c;
				}
			}
		$zonas[$zona_id] = $cuenta;
		$urls[] = "index.php?_module=$_module&_op=$_op&zid=$zona_id";
	}
        foreach ($zonas as $zona=>$cuenta)
	{
	//    $_html .= "$modelo: $cuenta <br>";
	$data[] = $cuenta;
	$campanas[] = "Zona $zona ($cuenta)";
	$total_datos += $cuenta;
	}
        $titulo = "Contactos por Zona";
}	




if ($total_datos == 0) die("<div style=\"font-family:Arial;font-size:11px;text-align:center;\">Gráfica vacia.<br><a href=\"javascript:history.go(-1);\">Regresar</a></div>");

// Setup the graph. 
$graph = new PieGraph(600,600,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set($titulo);
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->SetSize(.35);
$p1->SetCenter(.45);
$p1->SetStartAngle(0); //positivo para que sea manecillas del reloj y desde que angulo
$p1->SetLegends($campanas);


// Show absolute values
$p1->SetLabelType(PIE_VALUE_ABS); 
$p1->value->SetFormat('%d');
$p1->value->Show(); 


$p1->ExplodeAll(20);

$p1->SetCSIMTargets($urls);

$graph->Add($p1);
$graph->legend->Pos(0.01,0.99,"right", "bottom");



$graph->StrokeCSIM();

 ?>