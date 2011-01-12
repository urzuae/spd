<?php
if(isset($_REQUEST["send"]))
{
    global $db,$_includesdir,$_site_title;
    $_site_title = "Grafica de estado";
    $intentos = 0;
    $eliminados = 0;
    $asignados = 0;
    $reagenda = 0;

    $style1 = "row1";
    $style2 = "row2";

    if($_REQUEST["fecha_ini"] != "" and $_REQUEST["fecha_fin"] != "")
        $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'";
    elseif($_REQUEST["fecha_ini"] != "")
        $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') >= '".$_REQUEST["fecha_ini"]."'";
    elseif($_REQUEST["fecha_fin"] != "")
        $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') <= '".$_REQUEST["fecha_fin"]."'";

        
    //TOTAL DE REAGENDADOS
    $total_reagendados = 0;
    $sql = "SELECT COUNT(contacto_id) FROM crm_historial_contactos a WHERE log_id > 0 and reagenda = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_reagendados) = $db->sql_fetchrow($cs);
    
    //TOTAL DE ELIMINADOS
    $total_eliminados = 0;
    $sql = "SELECT COUNT(log_id) FROM crm_historial_contactos a WHERE log_id > 0 and elimina = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_eliminados) = $db->sql_fetchrow($cs);    
    
    //TOTAL DE ENVIADOS A CONCESIONARIA
    $total_enviados_concesionaria = 0;
    $sql = "SELECT COUNT(log_id) FROM crm_historial_contactos a WHERE log_id > 0 and envia_concesionaria = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_enviados_concesionaria) = $db->sql_fetchrow($cs);
    
    //LIBRERIAS DE GRAFICOS
    require_once("$_includesdir/jpgraph/jpgraph.php");
    require_once("$_includesdir/jpgraph/jpgraph_pie.php");
    require_once("$_includesdir/jpgraph/jpgraph_pie3d.php");

    if($total_reagendados == 0 and $total_eliminados == 0 and $total_enviados_concesionaria == 0)
        $_grafico =  "No hay registros en ese rango de fechas";
    else
    {
        $data = array($total_reagendados,$total_eliminados,$total_enviados_concesionaria);
        $graph = new PieGraph(640,350);
        $graph->SetShadow();

        $p1 = new PiePlot3D($data);
        $p1->SetAngle(30);
        $p1->SetSize(0.5);
        $p1->SetCenter(0.45);
        $p1->SetHeight(15);
        $p1->ExplodeAll(10);
        $p1->SetLegends(array("Reagendados = $total_reagendados","Eliminados = $total_eliminados","Enviados a concesionaria = $total_enviados_concesionaria"));
        $p1->SetCSIMTargets(array("index.php?_module=CallcenterNacional&_op=grafica_reagendados&fecha_ini=".$_REQUEST["fecha_ini"]."&fecha_fin=".$_REQUEST["fecha_fin"],"index.php?_module=CallcenterNacional&_op=grafica_eliminados&fecha_ini=".$_REQUEST["fecha_ini"]."&fecha_fin=".$_REQUEST["fecha_fin"],"index.php?_module=CallcenterNacional&_op=grafica_enviados_concesionaria&fecha_ini=".$_REQUEST["fecha_ini"]."&fecha_fin=".$_REQUEST["fecha_fin"]));
        $graph->Add($p1);
        $_grafico = $graph->StrokeCSIM('auto','',0, true);
    }
}
?>
