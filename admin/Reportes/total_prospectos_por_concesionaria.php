<?
if(!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
global $db, $gid, $fecha_ini, $fecha_fin;

if($fecha_ini)
{
    $l_excel = "&fecha_ini=$fecha_ini";
    $fecha_ini_o = $fecha_ini;
    $titulo = " desde $fecha_ini";
    $fecha_ini = date_reverse($fecha_ini);
    $where_fecha .= " AND co.fecha_importado>'$fecha_ini 00:00:00'";
}
if($fecha_fin)
{
    $l_excel .= "&fecha_fin=$fecha_fin";
    $fecha_fin_o = $fecha_fin;
    $titulo .= " hasta $fecha_fin";
    $fecha_fin = date_reverse($fecha_fin);
    $where_fecha .= " AND co.fecha_importado<'$fecha_fin 23:59:59'";
}

if($gid)
{
    $sql = "SELECT v.nombre, 
		               count(v.nombre) 
		        FROM crm_campanas as c, 
		             crm_campanas_llamadas as l,
		             crm_contactos AS co, 
		             crm_prospectos_ciclo_de_venta AS v 
		        WHERE c.campana_id=l.campana_id AND 
		              l.contacto_id=co.contacto_id AND 
		              v.ciclo_de_venta_id=c.etapa_ciclo_id AND 
		              co.gid='$gid'
		              $where_fecha 
		        GROUP BY (v.nombre) 
		        order by c.etapa_ciclo_id ";
    $result = $db->sql_query($sql) or die($sql);
    while(list($origen, $cuenta) = $db->sql_fetchrow($result))
    {
        $tabla_contenido_prospectos .= "<tr class=\"row" . (++$rowclass % 2 ? "2" : "1") . "\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$origen&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
    }
    $titulo = "Reporte distribuidora $gid";
    $tipo_reporte = "Ciclo";
    $link_excel = "total_prospectos_por_distribuidora_excel&gid=$gid";
    $link_grap = "graph_prospectos_ciclo_ventas";
    $gid_o = $gid;
} else
{
    //los que no estan finalizados
    $sql = "SELECT co.gid, 
		               COUNT(co.uid) 
		        FROM `crm_contactos` AS co 
		        WHERE 1 
		              $where_fecha 
		        GROUP BY (co.gid)";
    $result = $db->sql_query($sql) or die($sql);
    $cuantos = array();
    while(list($gid, $cuenta) = $db->sql_fetchrow($result))
    {
        $cuantos[$gid] = $cuenta;
    }
    //los finalizados
    $sql = "SELECT co.gid, 
		               COUNT(co.uid) 
		        FROM `crm_contactos_finalizados` AS co 
		        WHERE 1 
		              $where_fecha 
		        GROUP BY (co.gid)";
    $result = $db->sql_query($sql) or die($sql);
    
    while(list($gid, $cuenta) = $db->sql_fetchrow($result))
    {
        $cuantos[$gid] =  $cuantos[$gid] + $cuenta;
        
    }
    $tabla_contenido_prospectos = "<tbody>";
    $cuenta_total = 0;
    foreach($cuantos as $origen => $cuenta)
    {
        $sql_name = "SELECT name FROM groups WHERE gid = '$origen'";
        $result_name = $db->sql_query($sql_name) or die($sql_name);
        list($nombre_concesionaria) = $db->sql_fetchrow($result_name);
        $urls = "index.php?_module=$_module&_op=$_op&gid=$origen";
        $tabla_contenido_prospectos .= "<tr class=\"row" . (++$rowclass % 2 ? "2" : "1") . "\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$origen</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$nombre_concesionaria&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		$cuenta_total += $cuenta;
    }
    $tabla_contenido_prospectos .= "</tbody><tfoot><tr class=\"row" . (++$rowclass % 2 ? "2" : "1") . "\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>Total&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta_total&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr></tfoot>";
                    
    $titulo = "Prospectos Por Distribuidora";
    $tipo_reporte = "Distribuidora";
    $link_excel = "total_prospectos_por_distribuidora_excel" . $l_excel;
    $nombre_concesionaria_td = "<td rowspan=\"1\">Nombre</td>";
    $link_grap = "graph_total_prospectos_por_distribuidora";
}
$contenido_prospectos = "<table>
         <thead>
           <tr>
	         <td rowspan=\"1\">$tipo_reporte</td>
	         $nombre_concesionaria_td
	         <td rowspan=\"1\">Total</td>
	      </tr>
	     </thead>
	     $tabla_contenido_prospectos
	     
	   </table>";

/*Las siguientes lineas se comentaron debido a que este codigo era el que creaba la grafica, 
la cual se cambio para que se exportara directamente a excel.*/
$fecha_fin = $fecha_fin_o;
$fecha_ini = $fecha_ini_o;
/*
$graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=$link_grap&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&gid=$gid_o\">";
$_html_b .= "<h1>Gráfica de Total de Prospectos por Concesionaria</h1><center>$graph</center>";
*/
?>