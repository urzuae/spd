<?php
global $db,$_includesdir,$_site_title;

$_site_title = "Grafica de contactactos re agendados";
$style1 = "row1";
$style2 = "row2";

if($_REQUEST["fecha_ini"] != "" and $_REQUEST["fecha_fin"] != "")
    $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'";
elseif($_REQUEST["fecha_ini"] != "")
    $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') >= '".$_REQUEST["fecha_ini"]."'";
elseif($_REQUEST["fecha_fin"] != "")
    $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') <= '".$_REQUEST["fecha_fin"]."'";

    
$sql = "select motivo_id, motivo from crm_motivos_reagenda";
$cs = $db->sql_query($sql);
$contador_array = 0;
while(list($motivo_id, $motivo) = $db->sql_fetchrow($cs)){
	$motivos[$contador_array] = $motivo;
	$contador_array++;
}
//$motivos[$contador_array] = "NO ESPECIFICADO";
$illegal_zero = true;

//CONTACTOS REAGENDADOS
$cont_1 = $cont_2 = $cont_3 = $cont_4 = $cont_5 = $cont_6 = $cont_7 = $cont_default = 0;
$sql = "SELECT motivo_reagenda FROM crm_historial_contactos a WHERE log_id > 0 $rango_fechas";
$cs = $db->sql_query($sql);
while(list($motivo_reagenda) = $db->sql_fetchrow($cs)){
	switch($motivo_reagenda){
		case 1:	$cont_1++; $illegal_zero = false; break;
		case 2:	$cont_2++; $illegal_zero = false; break;
		case 3:	$cont_3++; $illegal_zero = false; break;
		case 4:	$cont_4++; $illegal_zero = false; break;
		case 5:	$cont_5++; $illegal_zero = false; break;
		case 6:	$cont_6++; $illegal_zero = false; break;
		case 7:	$cont_7++; $illegal_zero = false; break;
		//default: $cont_default++; $illegal_zero = false; break;
	}
}
$contador_motivos = array($cont_1,$cont_2,$cont_3,$cont_4,$cont_5,$cont_6,$cont_7);
$leyend_motivos = array("$motivos[0] = $cont_1","$motivos[1] = $cont_2","$motivos[2] = $cont_3",
"$motivos[3] = $cont_4","$motivos[4] = $cont_5","$motivos[5] = $cont_6","$motivos[6] = $cont_7");


if($illegal_zero == false){
//LIBRERIAS DE GRAFICOS
    require_once("$_includesdir/jpgraph/jpgraph.php");
    require_once("$_includesdir/jpgraph/jpgraph_pie.php");
    require_once("$_includesdir/jpgraph/jpgraph_pie3d.php");

    $graph = new PieGraph(640,400);
    $graph->SetShadow();

    $p1 = new PiePlot3D($contador_motivos);
    $p1->SetAngle(30);
    $p1->SetSize(0.5);
    $p1->SetCenter(0.40,0.65);
    $p1->SetHeight(15);
    $p1->ExplodeAll(10);
    $p1->SetLegends($leyend_motivos);
    //$p1->SetCSIMTargets(array("","index.php?_module=CallcenterNacional&_op=grafica_eliminados&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin",""));
    $graph->Add($p1);
    $_grafico = $graph->StrokeCSIM('auto','',0, true);
}
else
    $_grafico = "No existen registros reagendados en ese rango de fechas";
?>
