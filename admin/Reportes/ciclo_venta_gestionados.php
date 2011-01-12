<?
	if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
		die ( "No puedes acceder directamente a este archivo..." );
	}
	global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id;
	global $_admin_menu2, $_admin_menu;
	
    if($gid){
      $gid = (integer) $gid;
	  $where_gid = sprintf("AND LEFT( c.campana_id, %s ) = %s",strlen($gid),  $gid);
	  $l_gid = "&gid=$gid";
    }
    
    $graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_ciclos_venta_gestionados&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&gid=$gid\">";
	$_html_b .= "<h1>Gráfica de Contactos por Ciclo de Venta</h1><center>$graph</center>";
	
	if ($fecha_ini) {
		$l_excel = "&fecha_ini=$fecha_ini";
		$titulo .= " desde $fecha_ini";
		$fecha_ini_o = $fecha_ini;
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND cl.timestamp>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin) {
		$l_excel .= "&fecha_fin=$fecha_fin";
		$titulo .= " hasta $fecha_fin";
		$fecha_fin_o = $fecha_fin;
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND cl.timestamp<'$fecha_fin 23:59:59'";
	}

    $i = 0;
	$sql = "SELECT nombre FROM crm_campanas where campana_id = campana_id < 9 LIMIT 8";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	while ( list ( $nombre ) = $db->sql_fetchrow ( $result ) ) {
		$campanas[$i] = $nombre;
		$i++;
	}
	
	$i=0;
	$sql = "SELECT right(c.campana_id,1) as id,
	               count(cl.contacto_id)
	        FROM crm_campanas as c,
	             crm_campanas_llamadas as cl
	        WHERE c.campana_id = cl.campana_id AND 
	              cl.status_id != 0 
	              $where_fecha 
	              $where_gid
	        GROUP BY right(campana_id,1)";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	while ( list ( $ciclo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
		$tabla_contenido_ciclo_venta .= "<tr class=\"row" . (++ $rowclass % 2 ? "2" : "1") . "\" >
                    <td>$campanas[$i]</td>
                    <td>$cuenta</td>
                    </tr>";
		$i ++;
	}
	
	$titulo = "Reporte de contactos por ciclo de venta gestionados";
	$link_excel = "ciclo_venta_gestionados_excel" . $l_excel.$l_gid;
	$contenido_ciclo_venta_gestionados = "<table>
         <thead>
           <tr>
	         <td rowspan=\"1\">Ciclo</td>
	         <td rowspan=\"1\">Total</td>
	      </tr>
	     </thead>
	     $tabla_contenido_ciclo_venta
	   </table>";
	$fecha_fin = $fecha_fin_o;
	$fecha_ini = $fecha_ini_o;
	  $select_groups = "<select name=\"gid\">";
  $result = $db->sql_query("SELECT gid,name FROM groups WHERE 1 ORDER BY gid") or die("Error al cargar grupos");
  $select_groups .= "<option value=\"\">Selecciona una distribuidora</option>\n";
  while(list($_gid,$name) = $db->sql_fetchrow($result)){
  	if ($_gid == $gid)
  	  $selected = " SELECTED";
  	else
  	  $selected = "";
  	$select_groups .= "<option value=\"$_gid\"$selected>$_gid - $name</option>";
  }
  $select_groups .= "</select>";
	?>
