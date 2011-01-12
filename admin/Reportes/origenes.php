<?
	if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
		die ( "No puedes acceder directamente a este archivo..." );
	}
	
	
	global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id;
	global $_admin_menu2, $_admin_menu;
	
	$graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_origenes&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&gid=$gid\"></iframe>";
	$_html_b .= "<h1>Gráfica de Contactos por Origen</h1><center>$graph</center>";
	
	if($gid){
		$where_gid = "AND c.gid = $gid";
		$l_excel .= "&gid=$gid";
	}
	
	if ($fecha_ini) {
		$l_excel .= "&fecha_ini=$fecha_ini";
		$fecha_ini_o = $fecha_ini;
		$titulo .= " desde $fecha_ini";
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND c.fecha_importado>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin) {
		$l_excel .= "&fecha_fin=$fecha_fin";
		$fecha_fin_o = $fecha_fin;
		$titulo .= " hasta $fecha_fin";
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND c.fecha_importado<'$fecha_fin 23:59:59'";
	}
	
	$sql = "select o.nombre, count(c.contacto_id) from crm_contactos_origenes as o, crm_contactos as c where c.origen_id = o.origen_id $where_fecha $where_gid group by o.nombre";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	while ( list ( $modelo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
		$tabla_contenido_origenes .= "<tr class=\"row" . (++ $rowclass % 2 ? "2" : "1") . "\" >
                    <td>$modelo</td>
                    <td>$cuenta</td>
                    </tr>";
	}
	$titulo = "Reporte de Contactos por Origen";
	$link_excel = "origenes_excel" . $l_excel;
	$contenido_origenes = "<table>
         <thead>
           <tr>
	         <td rowspan=\"1\">Origen</td>
	         <td rowspan=\"1\">Total</td>
	      </tr>
	     </thead>
	     $tabla_contenido_origenes
	   </table>";
	$fecha_fin = $fecha_fin_o;
	$fecha_ini = $fecha_ini_o;
	  $select_groups = "<select name=\"gid\">";
  $result = $db->sql_query("SELECT gid,name FROM groups WHERE 1 ORDER BY gid") or die("Error al cargar grupos");
  $select_groups .= "<option value=\"\">Selecciona una concesionaria</option>\n";
  while(list($_gid,$name) = $db->sql_fetchrow($result)){
  	if ($_gid == $gid)
  	  $selected = " SELECTED";
  	else
  	  $selected = "";
  	$select_groups .= "<option value=\"$_gid\"$selected>$_gid - $name</option>";
  }
  $select_groups .= "</select>";
?>
