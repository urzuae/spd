<?
	if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
		die ( "No puedes acceder directamente a este archivo..." );
	}
	global $db, $campana_id, $gid, $uid, $fecha_ini, $fecha_fin, $origen_id, $zid;
	
	$graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"index.php?_module=$_module&_op=graph_zonas&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&zid=$zid\">";
	$_html_b .= "<h1>Gráfica de Por zonas</h1><center>$graph</center>";
	
	if (! $campana_id) {
		$campana_id = 1;
	}
	$zonas = array ( );
	$urls = array ( );
	
	if ($fecha_ini) {
		$l_excel = "&fecha_ini=$fecha_ini";
		$fecha_ini_o = $fecha_ini;
		$titulo .= " desde $fecha_ini";
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND fecha_importado>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin) {
		$l_excel .= "&fecha_fin=$fecha_fin";
		$fecha_fin_o = $fecha_fin;
		$titulo .= " hasta $fecha_fin";
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND fecha_importado<'$fecha_fin 23:59:59'";
	}
	
	if ($zid) {
		$sql2 = "select g.name, g.gid from groups as g, groups_zonas as gz where g.gid = gz.gid and gz.zona_id = $zid";
		$result2 = $db->sql_query ( $sql2 ) or die ( $sql2 );
		while ( list ( $zona, $gid ) = $db->sql_fetchrow ( $result2 ) ) {
			$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid ".$where_fecha;
			$result3 = $db->sql_query ( $sql3 ) or die ( $sql3 );
			while ( list ( $cuenta ) = $db->sql_fetchrow ( $result3 ) ) {
				$zonas [$gid] = $cuenta;
				$urls = "index.php?_module=$_module&_op=grupo&cid=$gid";
			}
			$tabla_contenido_zonas .= "<tr class=\"row" . (++ $rowclass % 2 ? "2" : "1") . "\">
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$zona</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>&nbsp;$zonas[$gid]&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		}
		$titulo = "Reporte Zona $zid";
		$link_excel = "zonas_excel&zid=$zid".$l_excel;
		$val_zona = "Concesionaria";
	} else {
		$sql = "select nombre, zona_id from crm_zonas";
		$result = $db->sql_query ( $sql ) or die ( $sql );
		while ( list ( $zona, $zona_id ) = $db->sql_fetchrow ( $result ) ) {
			$cuenta = 0;
			$sql2 = "select gid from groups_zonas where zona_id = $zona_id";
			$result2 = $db->sql_query ( $sql2 ) or die ( $sql2 );
			while ( list ( $gid ) = $db->sql_fetchrow ( $result2 ) ) {
				$sql3 = "select count(contacto_id) from crm_contactos where gid = $gid ".$where_fecha;
				$result3 = $db->sql_query ( $sql3 ) or die ( $sql3 );
				while ( list ( $c ) = $db->sql_fetchrow ( $result3 ) ) {
					$cuenta = $cuenta + $c;
				}
			}
			$zonas [$zona_id] = $cuenta;
			$urls = "index.php?_module=$_module&_op=$_op&zid=$zona_id";
			$tabla_contenido_zonas .= "<tr class=\"row" . (++ $rowclass % 2 ? "2" : "1") . "\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$zona</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		}
		$titulo = "Reporte Por Zonas";
		$link_excel = "zonas_excel".$l_excel;
		$val_zona = "Zona";
	}
	$contenido_zonas = "<table>
         <thead>
           <tr>
	         <td rowspan=\"1\">$val_zona</td>
	         <td rowspan=\"1\">Total</td>
	      </tr>
	     </thead>
	     $tabla_contenido_zonas
	   </table>";
	
	$fecha_fin = $fecha_fin_o;
    $fecha_ini = $fecha_ini_o;
	?>