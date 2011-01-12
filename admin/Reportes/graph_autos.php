<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
  die ("No puedes acceder directamente a este archivo...");
}
global $db, $file, $submit, $del, $campana_id, $fecha_ini, $fecha_fin, $uid,$gid;
if (!$campana_id) $campana_id = 1;
$_theme = "";

if($gid){
	$where_concesionaria .= " AND c.gid = '$gid'";	
}

if ($fecha_fin || $fecha_ini) {
    $sql = "SELECT nombre FROM `crm_unidades`";
    $result = $db->sql_query ( $sql ) or die ( $sql );
    
    if ($fecha_ini) {
		$titulo .= " desde $fecha_ini";
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND c.fecha_importado>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin) {
		$titulo .= " hasta $fecha_fin";
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND c.fecha_importado<'$fecha_fin 23:59:59'";
	}
    
    while ( list ( $modelo_nombre) = $db->sql_fetchrow ( $result ) ) {
        $sql_con[] = "SELECT u.modelo, COUNT( u.contacto_id ) FROM crm_prospectos_unidades AS u, crm_contactos AS c
    	 WHERE u.modelo = '$modelo_nombre' AND c.contacto_id = u.contacto_id ".$where_fecha." $where_concesionaria GROUP BY u.modelo "; 	
    }
}
else{
	$sql_con[] = "select u.modelo, 
	                     count(u.modelo) 
	              from crm_prospectos_unidades as u,
	                   crm_contactos as c
	              WHERE  u.contacto_id = c.contacto_id
	                     $where_concesionaria
	              group by u.modelo";
}
include("$_includesdir/jpgraph/jpgraph.php");
include("$_includesdir/jpgraph/jpgraph_pie.php");
include("$_includesdir/jpgraph/jpgraph_pie3d.php");

$modelos = array();
foreach ($sql_con as $consultas => $consulta){
	$result = $db->sql_query ( $consulta ) or die ( $consulta );
    while ( list ( $modelo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
	  $modelos[$modelo] = $cuenta;
    }
}

/*
$sql = "select modelo, count(modelo) from crm_prospectos_unidades group by modelo";
$result = $db->sql_query($sql) or die($sql);
while(list($modelo, $cuenta) = $db->sql_fetchrow($result))
	{

   list($modelo) = explode(" ", $modelo);
	$modelos[$modelo] = $cuenta;
	}
*/

foreach ($modelos as $modelo=>$cuenta)
{
//    $_html .= "$modelo: $cuenta <br>";
   $data[] = $cuenta;
   $campanas[] = "$modelo ($cuenta)";
   $total_datos += $cuenta;
   $urls[] = "javascript:alert('$modelo: $cuenta');";
}

if ($total_datos == 0) die("<div style=\"font-family:Arial;font-size:11px;text-align:center;\">Gráfica vacia.<br><a href=\"javascript:history.go(-1);\">Regresar</a></div>");

// Setup the graph. 
$graph = new PieGraph(600,450,"auto"); 
// $graph->img->SetMargin(60,20,30,90);

$graph->SetShadow();

$graph->title->Set("Autos Prospectados");
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


$p1->ExplodeAll(25);

$p1->SetCSIMTargets($urls);

$graph->Add($p1);



$graph->StrokeCSIM();

 ?>