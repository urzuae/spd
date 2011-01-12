<?
 include_once($_includesdir."/Genera_Excel.php");

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
	global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
    $submit, $status_id, $ciclo_de_venta_id, $uid,$gid,$rsort, $open,$orderby,$uid_,$_module,
    $nsort,$_op,$url,$filtro,$leyenda_filtros,$tmp_filtros,$_site_name;
       
	global $gid,$uid;
	$uid=$_GET['uid'];
	include_once("regresa_filtros.php");
	$filtro=implode(" AND ",$tmp_filtros);;

	$sql_group = "SELECT name FROM groups WHERE gid='$gid'";
    $res_group = $db->sql_query($sql_group) or die($sql_group);
    $name_group = $db->sql_fetchfield(0,0,$res_group);
    

	$sql_vendedor = "SELECT name FROM users WHERE uid='$uid' AND super=8;";
	$res_vendedor = $db->sql_query($sql_vendedor) or die($sql_vendedor);
	$name_vendedor = $db->sql_fetchfield(0,0,$res_vendedor);
	  
	$how_many = 25;
	if ($from < 1 || !$from) 
		$from = 0;
	if ($open) 
		$array_tabs_abiertos = explode("-",$open);

	$counter_total = 0;
	$sql_campanas = "SELECT c.campana_id, c.nombre FROM crm_campanas AS c, crm_campanas_groups AS g WHERE g.gid=".$gid." AND c.campana_id=g.campana_id ORDER BY campana_id LIMIT $from, $how_many";
	$res_campanas = $db->sql_query($sql_campanas) or die("Error al consultar campañas ".print_r($db->sql_error()));
	if($db->sql_numrows($res_campanas)> 0)
	{	
		$tabla_campanas .= "<table class=\"width100\">"
	                  ."<thead>"
					  ."<tr>"
	                  ."<td style=\"width:13%;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=origen_id&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Campaña</a></td>"
					  ."<td style=\"width:22%;\"><a href=\"#\" onclick=\"location.href='index.php?_module=$_module&_op=$_op&uid=$uid&campana_id=$campana_id&gid=$gid&orderby=nombre&rsort=$nrsort&open='+document.getElementById('open').value;\" style=\"color:white;\">Nombre</a></td>"
					  ."<td style=\"width:15%;\">Vendedor</td>"
					  ."<td style=\"width:15%;\">Fecha de importaci&oacute;n</td>"
					  ."<td style=\"width:15%;\">Fecha de asignacion</td>"
	//				  ."<td style=\"width:15%;\">Hrs de Retraso</td>"
	                  ."</thead>"
	                  ."<tbody>";
		while (list($campana_id, $name) = htmlize($db->sql_fetchrow($res_campanas)))
		{
	  		$sql_ciclo = "SELECT c.id,d.origen_id,c.contacto_id,d.prospecto,c.status_id,d.uid,d.gid FROM crm_campanas_llamadas AS c, reporte_contactos_asignados AS d 
	  		WHERE c.campana_id='".$campana_id."' AND d.contacto_id=c.contacto_id AND d.gid='".$gid."' AND d.uid=".$uid." AND ".$filtro.";";
	  		$res_ciclo = $db->sql_query($sql_ciclo) or die($sql.(print_r($db->sql_error())));		
	  		$num_ciclo=$db->sql_numrows($res_ciclo);
	  		if ($num_ciclo>0)
	  		{
				$contacto_ids = array();
				$llamada_ids[] = array();
				$campana_ids[] = array();
				$origenes = array();
				$origenes_id = array();
				$nombres = array();
				$vendedor_actual=array();
				$vendedor=array();
				$fecha_log_ultimo=array();
				$fecha_importado=array();		
				$ordered_contacto_ids = array();
				$fecha_retraso = array();
				$counter = 0;		
				$array_para_ordenar=array();
			    while (list($llamada_id, $origen_id, $contacto_id, $prospecto, $status_id, $c_uid,$hrs_retraso) = $db->sql_fetchrow($res_ciclo))
	    		{     
	    			
		  			$res_origen = $db->sql_query("SELECT nombre FROM crm_contactos_origenes WHERE origen_id='$origen_id' LIMIT 1");
		  			list($origen) = $db->sql_fetchrow($res_origen);	  	
		  			$res_reasignacion=$db->sql_query("SELECT to_uid,to_gid,fecha_log_ultima,vendedor,fecha_importado,hrs_retraso_g FROM reporte_contactos_asignados WHERE contacto_id=".$contacto_id." limit 1;");
					$array_tmp_logs= $db->sql_fetchrow($res_reasignacion);
					$contacto_ids[] = $contacto_id;
		  			$llamada_ids[$contacto_id] = $llamada_id;
		  			$campana_ids[$contacto_id] = $campana_id;
		  			$origenes[$contacto_id] = $origen;
		  			$origenes_id[$contacto_id] = $origen_id;
		  			$nombres[$contacto_id] = $prospecto;
					$vendedor[$contacto_id]=$array_tmp_logs['vendedor'];
					$fecha_retraso[$contacto_id]=$array_tmp_logs['hrs_retraso_g'];
					$fecha_log_ultimo[$contacto_id]=$array_tmp_logs['fecha_log_ultima'];
					$fecha_importado[$contacto_id]=$array_tmp_logs['fecha_importado'];			  	
		  			$counter++;
	    		}
				switch($orderby)
				{
					case "origen_id": $array_para_ordenar = &$origenes_id; break;
					case "prospecto": $array_para_ordenar = &$prospecto;   break; 
					default: $array_para_ordenar = &$nombres;              break;			
				} 
				if (!$rsort)
					asort($array_para_ordenar); 
				else
					arsort($array_para_ordenar); 

				foreach ($array_para_ordenar AS $key=>$value)
				{
					$ordered_contacto_ids[] = $key;
				}
				if ($rsort)
      				$nrsort = 0;
    			else
      				$nrsort = 1;  
				
      			foreach ($ordered_contacto_ids AS $contacto_id)
		  		{
			  		$origen = $origenes[$contacto_id];
			  		$prospecto = $nombres[$contacto_id];
		      		$llamada_id = $llamada_ids[$contacto_id];
	      	  		$tabla_campanas .= "
	      	  			<tr class=\"row".(($c++%2)+1)."\">"
						."<td>$origen</td>"
	            		."<td><a href=\"index.php?_module=$_module&_op=llamada_ro&llamada_id={$llamada_ids[$contacto_id]}&contacto_id=$contacto_id&campana_id={$campana_ids[$contacto_id]}\">
						$prospecto</a></td>"	
						."<td>{$vendedor[$contacto_id]}</td>"						
	            		."<td>{$fecha_importado[$contacto_id]}1</td>"
						."<td>{$fecha_log_ultimo[$contacto_id]}</td>"
	            		."</tr>";
				}
				$counter_total += $counter;
	  		}
		}
		if ($counter_total)
		{
			$tabla_campanas .=
			"<table style=\"text-align: left; width: 100%;\" border=\"0\" cellpadding=\"2\" cellspacing=\"2\"> <tbody> 
				<tr>
		 		<th>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; Total $counter_total</th>
				</tr>
			</table>";
          $objeto = new Genera_Excel($tabla_campanas,'Asignacion-Vendedores-Prospectos',$_site_name);
          $boton_excel=$objeto->Obten_href();

		}
		else
		{
			$tabla_campanas .= "<center>No hay prospectos asignados</center>";
		}
	}

?>