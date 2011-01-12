<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
  }
  global $db,$uid,$cid,$unid;
  
  $sql = "select zona_id from crm_zonas_gerentes where uid=$uid limit 1";
  $result = $db->sql_query($sql) or die($sql);
  list($zid) = $db->sql_fetchrow($result);
  //echo $sql;
  
  if($cid && !$unid){
  	$sql = "SELECT nombre FROM `crm_unidades`";
  	$result = $db->sql_query($sql) or die($sql);
	while(list($nombre) = $db->sql_fetchrow($result)){
      $sql2 = "select u.modelo, count(u.modelo) from crm_prospectos_unidades as u, crm_contactos as c where 
		u.contacto_id = c.contacto_id and c.gid = $cid and u.modelo = '$nombre' group by modelo";
	  $result2 = $db->sql_query($sql2) or die($sql2);
	  list($modelo, $cuenta) = $db->sql_fetchrow($result2);
	  if(!$cuenta){
	     $cuenta = 0;
	     $tabla_contenido_zonas .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$nombre&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
	  }
	  else{
	     $urls= "index.php?_module=$_module&_op=$_op&cid=$cid&unid=$modelo";
	     $tabla_contenido_zonas .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$nombre</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
	  }
	}
	$sql = "select name from groups where gid = $cid limit 1";
	$result = $db->sql_query($sql) or die($sql);
	list($grupo) = $db->sql_fetchrow($result);
	$titulo_zonas = "Reporte Distribuidora $grupo";
	$tipo_reporte = "Modelos";
	$link_excel = "zonas_excel&cid=$cid";
  }
  elseif($cid && $unid){
    $sql2 = "SELECT nombre, campana_id FROM crm_campanas LIMIT 8";
    $result2 = $db->sql_query($sql2) or die($sql2);
    while(list($nombre,$campana_id) = $db->sql_fetchrow($result2)){
  	   $sql = "select count(c.contacto_id) from crm_prospectos_unidades as u, crm_contactos as c, crm_campanas_llamadas as ll where 
		   u.contacto_id = c.contacto_id and c.gid = $cid and ll.contacto_id = c.contacto_id and u.modelo = '$unid' and right( ll.campana_id, 1 ) = $campana_id group by u.modelo, ll.campana_id";
	   $result = $db->sql_query($sql) or die($sql);
	   list($cuenta) = $db->sql_fetchrow($result);
	   if(!$cuenta)
	      $cuenta = 0;		
	   $tabla_contenido_zonas .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;$nombre&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
	}
	$sql = "select name from groups where gid = $cid limit 1";
	$result = $db->sql_query($sql) or die($sql);
	list($grupo) = $db->sql_fetchrow($result);
	$titulo_zonas = "Reporte Distribuidora $grupo Modelo $unid";
	$tipo_reporte = "Ciclo";
	$link_excel = "zonas_excel&cid=$cid&unid=$unid";
  }
  else{
    $sql2 = "select g.name, g.gid from groups as g, groups_zonas as gz where g.gid = gz.gid and gz.zona_id = $zid";
    $result2 = $db->sql_query($sql2) or die($sql2);
    while(list($zona, $gid) = $db->sql_fetchrow($result2)){
	   $sql3 = "select count(contacto_id) from crm_contactos where gid = $gid";
	   $result3 = $db->sql_query($sql3) or die($sql3);
	   while(list($cuenta) = $db->sql_fetchrow($result3)){
			$urls = "index.php?_module=$_module&_op=$_op&cid=$gid";
	        $tabla_contenido_zonas .= "<tr class=\"row".(++$rowclass%2?"2":"1")."\" >
                    <td>&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"$urls\">$zona</a>&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    <td>$cuenta&nbsp;&nbsp;&nbsp;&nbsp;</td>
                    </tr>";
		}
	}	
	$titulo_zonas = "Contactos Por Zona";
	$tipo_reporte = "Distribuidora";
	$link_excel = "zonas_excel";
  }
  $contenido_zonas = "<table>
         <thead>
           <tr>
	         <td rowspan=\"1\">$tipo_reporte</td>
	         <td rowspan=\"1\">Total</td>
	      </tr>
	     </thead>
	     $tabla_contenido_zonas
	   </table>";

/*Las siguientes lineas se comentaron debido a que este codigo era el que creaba la grafica, 
la cual se cambio para que se exportara directamente a excel.*/
/*
$graph = "<br><iframe style=\"width:650px;height:650px;\" border=\"0\" frameBorder=\"NO\"  SCROLLING=\"NO\" name=\"graph\" src=\"?_module=$_module&_op=graph_zonas&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin\">";

$_html .= "<h1>Gr�fica de Contactos por Zona</h1><center>$graph</center>";
*/
 ?>
