<?
if (!defined('_IN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $uid, $cid, $unid;
if (!$campana_id) $campana_id = 1;
$_theme = "";
$zonas = array();
$urls = array();

$sql = "select name from groups where gid = $cid limit 1";
$result = $db->sql_query($sql) or die($sql);
list($grupo) = $db->sql_fetchrow($result);

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
 
 
if($unid && $cid)
{
	$sql = "select right(ll.campana_id, 1), count(c.contacto_id) from crm_prospectos_unidades as u, crm_contactos as c, crm_campanas_llamadas as ll where 
		u.contacto_id = c.contacto_id and c.gid = $cid and ll.contacto_id = c.contacto_id and u.modelo = '$unid' group by u.modelo, ll.campana_id";
	//echo $sql;
	$result = $db->sql_query($sql) or die($sql);
	while(list($ciclo, $cuenta) = $db->sql_fetchrow($result))
		{
		$zonas[$ciclo] = $cuenta;
                //echo "$ciclo - $cuenta";
		$urls[] = "#";
                $sql2 = "SELECT nombre FROM crm_campanas where campana_id = '$ciclo'";
                $result2 = $db->sql_query($sql2) or die($sql2);
                list($nombre) = $db->sql_fetchrow($result2);
                $nombres[] = $nombre;
                //echo "$nombre<br>";
		}

			
} 
elseif($cid && !$unid)
{
	$sql = "select u.modelo, count(u.modelo) from crm_prospectos_unidades as u, crm_contactos as c where 
		u.contacto_id = c.contacto_id and c.gid = $cid group by modelo";
	$result = $db->sql_query($sql) or die($sql);
	while(list($modelo, $cuenta) = $db->sql_fetchrow($result))
		{

		$zonas[$modelo] = $cuenta;
		$urls[] = "index.php?_module=$_module&_op=$_op&cid=$cid&unid=$modelo";
		}
	
}




foreach ($zonas as $zona=>$cuenta)
	{
	//    $_html .= "$modelo: $cuenta <br>";
	$data[] = $cuenta;
	$campanas[] = "$zona ($cuenta)";
	$total_datos += $cuenta;
	}

if ($total_datos == 0) die("<div style=\"font-family:Arial;font-size:11px;text-align:center;\">Gráfica vacia.<br><a href=\"javascript:history.go(-1);\">Regresar</a></div>");

// Setup the graph. 
$graph = new PieGraph(600,600,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set($grupo);
$graph->title->SetFont(FF_FONT1,FS_BOLD);

$p1 = new PiePlot3D($data);
$p1->SetSize(.35);
$p1->SetCenter(.45);
$p1->SetStartAngle(0); //positivo para que sea manecillas del reloj y desde que angulo
if($unid)
$p1->SetLegends($nombres);
else
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