<?php
if($_REQUEST["send"] == "true")
{
    global $db,$_includesdir,$fecha_ini,$fecha_fin,$_site_title;
    $_site_title = "Total de llamadas";
    $fecha_ini = $_REQUEST["fecha_ini"];
    $fecha_fin = $_REQUEST["fecha_fin"];

    $style1 = "row1";
    $style2 = "row2";

    if($fecha_ini != "" and $fecha_fin != "")
        $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') between '".$fecha_ini."' and '".$fecha_fin."'";
    elseif($fecha_ini != "")
        $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') = '".$fecha_ini."'";
    elseif($fecha_fin != "")
        $rango_fechas = " and date_format(a.timestamp,'%Y-%m-%d') = '".$fecha_fin."'";

    //TOTAL DE LLAMADAS REALIZADAS
    $total_llamadas = $prospectos_trabajados = 0;
    $sql = "SELECT COUNT(log_id) FROM crm_historial_contactos a WHERE log_id > 0 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_llamadas) = $db->sql_fetchrow($cs);
    
    //TOTAL DE REAGENDADOS
    $total_reagendados = 0;
    $sql = "SELECT COUNT(DISTINCT(contacto_id)) FROM crm_historial_contactos a WHERE log_id > 0 and reagenda = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_reagendados) = $db->sql_fetchrow($cs);
    
    //TOTAL DE INTENTOS REAGENDADOS
    $total_intentos_reagendados = 0;
    $sql = "SELECT COUNT(contacto_id) FROM crm_historial_contactos a WHERE log_id > 0 and reagenda = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_intentos_reagendados) = $db->sql_fetchrow($cs);
    
    //TOTAL DE ELIMINADOS
    $total_eliminados = 0;
    $sql = "SELECT COUNT(contacto_id) FROM crm_historial_contactos a WHERE log_id > 0 and elimina = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_eliminados) = $db->sql_fetchrow($cs);    
    
    //TOTAL DE ENVIADOS A CONCESIONARIA
    $total_enviados_concesionaria = 0;
    $sql = "SELECT COUNT(contacto_id) FROM crm_historial_contactos a WHERE log_id > 0 and envia_concesionaria = 1 $rango_fechas";
    $cs = $db->sql_query($sql);
    list($total_enviados_concesionaria) = $db->sql_fetchrow($cs);
    
    //CABECERA PARA EL EXCEL
    $_csv = "\"\",\"PROSPECTO TRABAJADO\",\"FECHA ASIGNACION AL CALLCENTER NACIONAL\",\"FECHA DEL PRIMER CONTACTO\",\"SE REAGENDO\",\"SE ELIMINA\",\"SE ENVIA A CONCESIONARIO\",\"NÚMERO DE INTENTOS/TOTAL DE LLAMADAS\"\n";
    
    //INFORMACION A LISTAR
    $numero = 0;
    $sql = "SELECT contacto_id, reagenda, elimina, envia_concesionaria,
    CONCAT(nombre, ' ', primer_apellido, ' ', segundo_apellido) as nombres, 
	date_format(fecha_alta,'%d/%m/%Y'), date_format(timestamp,'%d/%m/%Y')
    FROM crm_historial_contactos a WHERE log_id > 0 $rango_fechas group by contacto_id ORDER BY timestamp,nombre";
    $cs = $db->sql_query($sql);
    while(list($contacto_id, $reagenda, $elimina, $envia_concesionaria, $nombre, $fecha_alta, $primer_contacto) = $db->sql_fetchrow($cs)){
    $ult_movimiento=substr($movimiento,8,2).'-'.substr($movimiento,5,2).'-'.substr($movimiento,0,4);
    	if($style == $style1)
            $style = $style2;
        else
            $style = $style1;
    	
    	//REAGENDA
    	$sql2 = "SELECT COUNT(reagenda) FROM crm_historial_contactos a 
    	WHERE contacto_id = '$contacto_id' and reagenda = 1 $rango_fechas";
    	$cs2 = $db->sql_query($sql2);
    	list($reagenda) = $db->sql_fetchrow($cs2);
    	
    	//ELIMINA
    	$sql2 = "SELECT COUNT(elimina) FROM crm_historial_contactos a 
    	WHERE contacto_id = '$contacto_id' and elimina = 1 $rango_fechas";
    	$cs2 = $db->sql_query($sql2);
    	list($elimina) = $db->sql_fetchrow($cs2);
    	
    	//ENVIA CONCESIONARIA
    	$sql2 = "SELECT COUNT(envia_concesionaria) FROM crm_historial_contactos a 
    	WHERE contacto_id = '$contacto_id' and envia_concesionaria = 1 $rango_fechas";
    	$cs2 = $db->sql_query($sql2);
    	list($envia_concesionaria) = $db->sql_fetchrow($cs2);
    	
    	//TOTAL INTENTOS
    	$total_intentos = $reagenda + $elimina + $envia_concesionaria;
    	
    	//NUMERO DE CONTACTO
    	$numero++;
    	
    	//ARRAY CONTACTOS
    	$array_contactos[] = array(
    		'contacto_id' => $contacto_id,
    		'nombre' => $nombre,
    		'fecha_alta' => $fecha_alta,
    		'primer_contacto' => $primer_contacto,
    		'reagenda' => $reagenda,
    		'elimina' => $elimina,
    		'envia_concesionaria' => $envia_concesionaria,
    		'total_intentos' => $total_intentos);
    	
    	//SALIDA HTML
    	$tabla_detalles .= "<tr>
                <td class=\"$style\" align=\"center\">$numero</td>
                <td class=\"$style\">$nombre</td>
                <td class=\"$style\" align=\"center\">$fecha_alta</td>
                <td class=\"$style\" align=\"center\">$primer_contacto</td>
                <td class=\"$style\" align=\"center\">$reagenda</td>
                <td class=\"$style\" align=\"center\">$elimina</td>
                <td class=\"$style\" align=\"center\">$envia_concesionaria</td>
                <td class=\"$style\" align=\"center\">$total_intentos</td>
                <td class=\"$style\" align=\"center\">
                    <input type='button' name='ver' class='basic demo' value='Ver' style='background:#ffffff;color:#3e4f88;border:0px'onclick=\"Regresa_Historial('".$contacto_id."');\">
                </td>

            </tr>";
            
        //SALIDA EXCEL
        $_csv .= "\"".$numero."\",\"$nombre\",\"$fecha_alta\",\"$primer_contacto\",\"$reagenda\",\"$elimina\",\"$envia_concesionaria\",\"$total_intentos\"\n";
    	
        unset($nombre);
     }
    $prospectos_trabajados = $numero;
    
        
    $_csv .= "\"\",\"\",\"\",\"\",\"$total_intentos_reagendados\",\"$total_eliminados\",\"$total_enviados_concesionaria\",\"$total_llamadas\"\n";
    if($_REQUEST["excel"]){
        header('Content-type: text/csv');
        header('Content-Disposition: attachment; filename="'.$_op.'_'.date("d-m-Y").'.csv"');
        die($_csv);
    }

    if($total_llamadas == 0 and $prospectos_trabajados == 0)
        $_grafico =  "No hay registros en ese rango de fechas";
    else
    {
        //LIBRERIAS DE GRAFICOS
        require_once("$_includesdir/jpgraph/jpgraph.php");
        require_once("$_includesdir/jpgraph/jpgraph_pie.php");
        require_once("$_includesdir/jpgraph/jpgraph_pie3d.php");

        $data = array($total_llamadas,$prospectos_trabajados);
        $graph = new PieGraph(640,350);
        $graph->SetShadow();

        $p1 = new PiePlot3D($data);
        $p1->SetAngle(30);
        $p1->SetSize(0.5);
        $p1->SetCenter(0.45);
        $p1->SetHeight(15);
        $p1->ExplodeAll(10);
        $p1->SetLegends(array("Total de llamadas realizadas = $total_llamadas","Total de prospectos trabajados = $prospectos_trabajados"));
        $p1->SetTheme("pastel");
        //$p1->SetCSIMTargets(array("index.php?_module=CallcenterNacional&_op=total_llamadas2","index.php?_module=CallcenterNacional&_op=total_llamadas3"));
        $graph->Add($p1);
        $_grafico = $graph->StrokeCSIM('auto','',0, true);


        //TABLA DE DETALLES
        $_tabla = "<table border=\"0\" width=\"100%\" class=\"tablesorter\">";
        $_tabla .= "<thead><tr><th rowspan=\"2\"></th><th rowspan=\"2\">Prospecto trabajado</th>
            <th rowspan=\"2\">Fecha en que se asign&oacute; al Callcenter Nacional</th>
            <th rowspan=\"2\">Fecha de movimiento</td><td colspan=\"3\" align=\"center\">Detalle de Intentos</th>
            <th rowspan=\"2\">N&uacute;mero de intentos /<br> Total de llamadas</th>
            <th rowspan=\"2\">Historial</th>
            </tr>";
        $_tabla .= "<tr><th>Reagenda</th><th>Elimina</th><th>Env&iacute;a a concesionario</th></tr></thead>";
        $_tabla .= '<tbody>';
        $_tabla .= $tabla_detalles;
        $_tabla .= '</tbody>';
        $_tabla .= "<thead><tr><td colspan=\"4\"></td>
        <td style='font-weight:bold'><center>$total_intentos_reagendados</center></td>
        <td style='font-weight:bold'><center>$total_eliminados</center></td>
        <td style='font-weight:bold'><center>$total_enviados_concesionaria</center></td>
        <td style='font-weight:bold'><center>$total_llamadas</center></td><td>&nbsp;</td>
        </tr></thead>";
        $_tabla .= "</table>";

    }
}
?>