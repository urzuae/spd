<?php
/*if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}*/
function conexion(){
	if (!($link=mysql_connect("localhost","root","mysql_pwd!"))){
		die("Error conectando a la base de datos.");
	}
	if (!$link=mysql_select_db("crm_prospectos",$link)){ 
		die("Error seleccionando la base de datos.");
	}
	    return($link);
	
  }
$conecta = conexion();

global $db, $user, $uid, $fecha_ini, $fecha_fin, $submit, $fuente_id, $concesionaria_id,$sql;

$res = mysql_query("SELECT * FROM crm_regiones order by nombre ASC");
$cant =  mysql_num_rows($res);
if($cant>0){						
	while($rs = mysql_fetch_array($res)){	
		$select_regiones.="<option value=".$rs['region_id']."> {$rs['nombre']}</option>";	
	}
} 

$res = mysql_query("SELECT * FROM crm_contactos_origenes order by nombre ASC");
$cant =  mysql_num_rows($res);
if($cant>0){						
	while($rs = mysql_fetch_array($res)){	
		$select_origen.="<option value=".$rs['origen_id']."> {$rs['nombre']}</option>";	
	}
} 

$res = mysql_query("SELECT DISTINCT(nombre) FROM crm_unidades order by nombre ASC");
$cant =  mysql_num_rows($res);
if($cant>0){						
	while($rs = mysql_fetch_array($res)){	
		$select_modelo.="<option value=".$rs['nombre']."> {$rs['nombre']}</option>";	
	}
} 

if ($submit){
	
	$crm_region=$_REQUEST['crm_region'];
	$crm_zona=$_REQUEST['crm_zona'];
	$concesionaria=$_REQUEST['concesionaria'];
	$origen=$_REQUEST['origen'];
	$modelo=$_REQUEST['modelo'];	
	$fecha_ini=$_REQUEST['fecha_ini'];
	$fecha_fin=$_REQUEST['fecha_fin'];
	
		$res = mysql_query("SELECT * FROM crm_regiones order by nombre ASC");
		$cant =  mysql_num_rows($res);
		if($cant>0){						
			while($rs = mysql_fetch_array($res)){
				if($rs['region_id']==$crm_region){				
								$select_regiones.="<option value=".$rs['region_id']." selected> {$rs['nombre']}</option>";
							}else
							$select_regiones.="<option value=".$rs['region_id']."> {$rs['nombre']}</option>";					
			}
		}
	
		$res = mysql_query("SELECT * FROM crm_contactos_origenes order by nombre ASC");
				$cant =  mysql_num_rows($res);
				if($cant>0){						
					while($rs = mysql_fetch_array($res)){	
						if($rs['origen_id']==$origen){				
								$select_origen.="<option value=".$rs['origen_id']." selected> {$rs['nombre']}</option>";
							}else
							$select_origen.="<option value=".$rs['origen_id']."> {$rs['nombre']}</option>";	
					}
				}	
	
		$res = mysql_query("SELECT DISTINCT(nombre) FROM crm_unidades order by nombre ASC");
			$cant =  mysql_num_rows($res);
			if($cant>0){						
				while($rs = mysql_fetch_array($res)){	
					if($rs['nombre']==$modelo){				
						$select_modelo.="<option value=".$rs['nombre']." selected> {$rs['nombre']}</option>";	
					}else
					$select_modelo .= "<option value=".$rs['nombre']."> {$rs['nombre']}</option>";
				}
			}
		
			
		if($fecha_ini!='' && $fecha_fin==''){						
			$case1= "substr(fecha_importado,1,10)='".$fecha_ini."'";
			$colum++;
		}
		if($fecha_ini=='' && $fecha_fin!=''){					
			$case2= "substr(fecha_importado,1,10)='".$fecha_fin."'";
			$colum++;
		}
		if($fecha_ini!='' && $fecha_fin!=''){							
			$case3="substr(fecha_importado,1,10) BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'";
			$colum++;
		}
		if($crm_region){
			if($colum)
				$case4= "AND region_id=".$crm_region;
			else 
				$case4= "region_id=".$crm_region;			
			$colum++;
		}
		if($crm_zona){
			if($colum)
				$case5= "AND zona_id=".$crm_zona;
			else 
				$case5= "zona_id=".$crm_zona;
				
			$colum++;
		}
		if($concesionaria){
			if($colum)
				$case6= "AND gid='".$concesionaria."'";
			else 
				$case6= "gid='".$concesionaria."'";
			
			$colum++;
		}
		if($origen){
			if($colum)
				$case7= "AND origen_id=".$origen;
			else 
				$case7= "origen_id=".$origen;
			
			$colum++;
		}
		if($modelo){
			if($colum)
				$case8= "AND modelo='".$modelo."' ";
			else 
				$case8= "modelo='".$modelo."' ";
			
			$colum++;
		}
		
		if($colum)
		$case0="select * from reporte_contactos_asignados where";
		else 
		$case0="select * from reporte_contactos_asignados";
		
		$colum=0;
		
		$_html .="<table>
	          <thead>
	            <tr align='center'>
	              <td colspan=\"4\" align='center'> Reporte de contactos asignados</td>
	            </tr>
	            <tr align='center'>
					<td>Prospecto</td>
					<td>Fecha de Importación</td>
					<td>Distribuidor</td>
					<td>Modelo</td>
				</tr>";
		
		$arraSql['0']=$case0;
		$arraSql['1']=$case1;
		$arraSql['2']=$case2;
		$arraSql['3']=$case3;
		$arraSql['4']=$case4;
		$arraSql['5']=$case5;
		$arraSql['6']=$case6;
		$arraSql['7']=$case7;
		$arraSql['8']=$case8;			
	
		$sql= implode(" ", $arraSql);	
		$sql=
		$result = mysql_query($sql);
		$cant =  mysql_num_rows($result);
		if($cant>0){						
			while($rs = mysql_fetch_array($result)){	
				$_html.="<tr class=\"row1\" align='left'>			             
				      <td>".$rs['Prospecto']."</td> <td>".$rs['fecha_importado']."</td> <td>".$rs['name']."</td> <td>".$rs['modelo']."</td>
				      </tr>";	
			}
		}else  		
	$_html.="<tr class=\"row1\" align='center'> <td colspan=\"4\" align='center'> No hay registros</td> </tr>"; 
				
	}
		$_html .="</table>";

?>