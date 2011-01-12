<?
	if (! defined ( '_IN_ADMIN_MAIN_INDEX' )) {
		die ( "No puedes acceder directamente a este archivo..." );
	}
	global $db, $cid,$unid,$fecha_ini, $fecha_fin;
	
	$graph = "<br><iframe style=\"width:650px;height:500px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_grupo&cid=$cid&unid=$unid&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\">";
    $_html_b .= "<h1>Gráfica de Por zonas</h1><center>$graph</center>";
	
	$sql = "select name from groups where gid = $cid limit 1";
	$result = $db->sql_query ( $sql ) or die ( $sql );
	list ( $grupo ) = $db->sql_fetchrow ( $result );
	
    if ($fecha_ini) {
		$l_excel = "&fecha_ini=$fecha_ini";
		$fecha_ini_o = $fecha_ini;
		$fecha_ini = date_reverse ( $fecha_ini );
		$where_fecha .= " AND c.fecha_importado>'$fecha_ini 00:00:00'";
	}
	if ($fecha_fin) {
		$l_excel .= "&fecha_fin=$fecha_fin";
		$fecha_fin_o = $fecha_fin;
		$fecha_fin = date_reverse ( $fecha_fin );
		$where_fecha .= " AND c.fecha_importado<'$fecha_fin 23:59:59'";
    }
	
	if ($unid && $cid) {
		$sql2 = "SELECT nombre, campana_id FROM crm_campanas LIMIT 8";
		$result2 = $db->sql_query($sql2) or die($sql2);
		while(list($nombre,$campana_id) = $db->sql_fetchrow($result2)){
			$sql = "select count(c.contacto_id) 
			        from crm_prospectos_unidades as u, 
			             crm_contactos as c, 
			             crm_campanas_llamadas as ll 
			        where u.contacto_id = c.contacto_id and c.gid = $cid 
			        and ll.contacto_id = c.contacto_id and u.modelo = '$unid'
			        and right( ll.campana_id, 1 ) = $campana_id
			        $where_fecha 
			        group by u.modelo, ll.campana_id";
			$result = $db->sql_query ( $sql ) or die ( $sql );
			list ( $cuenta ) = $db->sql_fetchrow ( $result );
			if(!$cuenta)
			   $cuenta = 0; 
			$tabla_contenido_grupo .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$nombre&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		}
		/*$sql = "select right( ll.campana_id, 1 ), count(c.contacto_id) from crm_prospectos_unidades as u, crm_contactos as c, crm_campanas_llamadas as ll where 
		   u.contacto_id = c.contacto_id and c.gid = $cid and ll.contacto_id = c.contacto_id and u.modelo = '$unid' group by u.modelo, ll.campana_id";
		//echo $sql;
		$result = $db->sql_query ( $sql ) or die ( $sql );
		while ( list ( $ciclo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
			$sql2 = "SELECT nombre FROM crm_campanas where campana_id = '$ciclo'";
			$result2 = $db->sql_query ( $sql2 ) or die ( $sql2 );
			list ( $nombre ) = $db->sql_fetchrow ( $result2 );
			$tabla_contenido_grupo .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$nombre&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		}*/
        $titulo = "Reporte Modelo ".$unid.". Distribuidora ".$grupo ;
       	$link_excel = sprintf("grupo_excel&cid=%s&unid=%s%s",$cid,$unid,$l_excel);
       	$val_grupo = "Ciclo";
	
	} elseif ($cid && ! $unid) {
		$sql = "select u.modelo, count(u.modelo) from crm_prospectos_unidades as u, crm_contactos as c where 
		u.contacto_id = c.contacto_id and c.gid = $cid $where_fecha group by modelo";
		$result = $db->sql_query ( $sql ) or die ( $sql );
		while ( list ( $modelo, $cuenta ) = $db->sql_fetchrow ( $result ) ) {
			$zonas [$modelo] = $cuenta;
			$urls = "index.php?_module=$_module&_op=$_op&cid=$cid&unid=$modelo";
			$tabla_contenido_grupo .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$modelo</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$zonas[$modelo]&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		}
        $titulo = "Reporte Distribuidora ".$grupo ;
       	$link_excel = sprintf("grupo_excel&cid=%s%s",$cid,$l_excel);
       	$val_grupo = "Modelo";
	}
	
	$contenido_grupos = "<table>
         <thead>
           <tr>
	         <td rowspan=\"1\">$val_grupo</td>
	         <td rowspan=\"1\">Prospectos</td>
	      </tr>
	     </thead>
	     $tabla_contenido_grupo
	   </table>";
	$fecha_fin = $fecha_fin_o;
    $fecha_ini = $fecha_ini_o;   
	?>
