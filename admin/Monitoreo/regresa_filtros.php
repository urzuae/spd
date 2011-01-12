<?php
function genera_grupo_empresarial($db,$grupoemp_id)
{
	$buffer="";
	$res = $db->sql_query("SELECT nombre FROM crm_grupos_empresariales WHERE grupo_empresarial_id=".$grupoemp_id." order by nombre ASC");
	if($db->sql_numrows($res) > 0)
	{
		$buffer=$db->sql_fetchfield('nombre',0,$res);
	}
	return $buffer;
}
/**
 * Funcion que genera el select de origenes
 */
function genera_region($db,$region_id)
{
	$buffer="";
	$res = $db->sql_query("SELECT nombre FROM crm_regiones WHERE region_id=".$region_id." order by nombre ASC");
	if($db->sql_numrows($res) > 0)
	{						
		$buffer=$db->sql_fetchfield('nombre',0,$res);
	}
	return $buffer;
}
/**
 * Funcion que genera el select de zonas
 */
function genera_zonas($db,$zona_id)
{
	$buffer="";
	$res = $db->sql_query("SELECT nombre FROM crm_zonas WHERE zona_id=".$zona_id." order by nombre ASC");
	if($db->sql_numrows($res) > 0)
	{						
		$buffer=$db->sql_fetchfield('nombre',0,$res);
	}
	return $buffer;
}
/**
 * Funcion que genera el select de entidades
 */
function genera_entidades($db,$entidad_id)
{
	$buffer="";
	$res = $db->sql_query("SELECT nombre FROM crm_entidades WHERE id_entidad=".$entidad_id." order by nombre ASC");
	if($db->sql_numrows($res) > 0)
	{						
		$buffer=$db->sql_fetchfield('nombre',0,$res);
	}
	return $buffer;
}

/**
 * Funcion que genera el select de plaza
 */
function genera_plaza($db,$plaza_id)
{
	$buffer="";
	$res = $db->sql_query("SELECT nombre FROM crm_plazas WHERE plaza_id=".$plaza_id." order by nombre ASC");
	if($db->sql_numrows($res) > 0)
	{
		$buffer=$db->sql_fetchfield('nombre',0,$res);
	}
	return $buffer;

}

/**
 * Funcion que genera el select de origen
 */
function genera_origen($db,$origen)
{
	$buffer="";
	$res = $db->sql_query("SELECT nombre FROM crm_fuentes WHERE fuente_id=".$origen." order by nombre ASC");
	if($db->sql_numrows($res) > 0)
	{						
		$buffer=$db->sql_fetchfield('nombre',0,$res);
	}
	return $buffer;
}
/**
 * Funcion que genera el select de grupos
 */
function genera_grupos($db,$id_grupo)
{
	$buffer="";
	$res = $db->sql_query("SELECT name FROM groups WHERE gid=".$id_grupo." order by gid");
	if($db->sql_numrows($res) > 0)
	{						
		$buffer=$db->sql_fetchfield('name',0,$res);
	}
	return $buffer;
}
    $grupo_empresarial_id=$_REQUEST['grupo_empresarial_id'];
    $region_id=$_REQUEST['region_id'];
	$zona_id=$_REQUEST['zona_id'];
	$entidad_id=$_REQUEST['entidad_id'];
	$plaza_id=$_REQUEST['plaza_id'];
	$concesionaria=$_REQUEST['concesionaria'];
	$origen=$_REQUEST['origen'];
	$gid=$_REQUEST['gid'];
	$fecha_ini=$_REQUEST['fecha_ini'];
	$fecha_fin=$_REQUEST['fecha_fin'];
    $basico=$_REQUEST['basico'];
    $medio=$_REQUEST['medio'];
    $avanzado=$_REQUEST['avanzado'];

$url="&grupo_empresarial_id=".$grupo_empresarial_id.
         "&region_id=".$region_id.
		 "&zona_id=".$zona_id.
		 "&entidad_id=".$entidad_id.
		 "&plaza_id=".$plaza_id.
		 "&origen=".$origen.
		 "&fecha_ini=".$fecha_ini.
		 "&fecha_fin=".$fecha_fin.
         "&basico=".$basico.
         "&medio=".$medio.
         "&avanzado=".$avanzado.
         "&gid=".$gid;     
    $categorias="";
    if($basico>0)
        $categorias.="B&aacute;sico&nbsp;&nbsp; ";
    if($medio>0)
        $categorias.="Medio&nbsp;&nbsp; ";
    if($avanzado>0)
        $categorias.="Avanzada&nbsp;&nbsp; ";

    $filtro_res=genera_grupo_empresarial($db,$grupo_empresarial_id);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros.="<tr><td>Grupo Empresarial</td><td>".$filtro_res."</td></tr>";
	
    $filtro_res=genera_region($db,$region_id);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros="<tr><td>Regi&oacute;n</td><td>".$filtro_res."</td></tr>";
		
	$filtro_res=genera_zonas($db,$zona_id);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros.="<tr><td>Zona</td><td>".$filtro_res."</td></tr>";
		
	$filtro_res=genera_entidades($db,$entidad_id);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros.="<tr><td>Entidad</td><td>".$filtro_res."</td></tr>";
		
	$filtro_res=genera_plaza($db,$plaza_id);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros.="<tr><td>Plaza</td><td>".$filtro_res."</td></tr>";
	
    $filtro_res=genera_grupos($db,$gid);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros.="<tr><td>Distribuidor</td><td>".$filtro_res."</td></tr>";
		
	$filtro_res=genera_origen($db,$origen);
	if(strlen($filtro_res) > 1)
		$leyenda_filtros.="<tr><td>Origen</td><td>".$filtro_res."</td></tr>";
	
    if(strlen($categorias)>0)
		$leyenda_filtros.="<tr><td>Nivel</td><td>".$categorias."</td></tr>";

	if(($fecha_ini != '') && ($fecha_fin != ''))
	{
		$leyenda_filtros.="<tr><td>Periodo Comprendido</td><td>".$fecha_ini."&nbsp;&nbsp;&nbsp;&nbsp;al&nbsp;&nbsp;&nbsp;".$_REQUEST['fecha_fin']."</td></tr>";
		$tmp_filtros_contactos[]="substr(b.timestamp,1,10) BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'";
	}

    if(($fecha_ini != '') && ($fecha_fin == ''))
	{
		$leyenda_filtros.="<tr><td>Fecha </td><td>".$_REQUEST['fecha_ini']."</td></tr>";
		 $tmp_filtros_contactos[]="substr(b.timestamp,1,10)='".$fecha_ini."'";
	}
		
	if(($fecha_ini == '') && ($fecha_fin != ''))
	{
		$leyenda_filtros.="<tr><td>Fecha </td><td>".$_REQUEST['fecha_fin']."</td></tr>";	 
		$tmp_filtros_contactos[]="substr(b.timestamp,1,10)='".$fecha_fin."'";
	}	

	if($grupo_empresarial_id !=0)
		$tmp_filtros[]= "a.grupo_empresarial_id=".$grupo_empresarial_id;

	if($region_id!=0)
		$tmp_filtros[]= "a.region_id=".$region_id;

	if($zona_id!=0)
		$tmp_filtros[]= "a.zona_id=".$zona_id;

	if($entidad_gid!=0)
		$tmp_filtros[]= "a.entidad_gid=".$entidad_gid;

	if($plaza_gid!=0)
		$tmp_filtros[]= "a.plaza_id=".$plaza_id;

	if($origen!=0)
		$tmp_filtros_contactos[]= "b.origen_id='".$origen."'";

    if($gid!=0)
		$tmp_filtros[]= "b.gid='".$gid."'";
    if ( ($basico>0) && ($medio == 0) && ($avanzado == 0) )
    {
        $tmp_filtros[]= " a.nivel_id = 1 ";
    }
    if ( ($basico == 0) && ($medio > 0) && ($avanzado == 0) )
    {
        $tmp_filtros[]= " a.nivel_id = 2 ";
    }
    if ( ($basico == 0) && ($medio == 0) && ($avanzado >0) )
    {
        $tmp_filtros[]= " a.nivel_id = 3 ";
    }
    if ( ($basico > 0) && ($medio > 0) && ($avanzado == 0) )
    {
        $tmp_filtros[]= " a.nivel_id BETWEEN 1 AND 2 ";
    }
    if ( ($basico == 0) && ($medio > 0) && ($avanzado > 0) )
    {
        $tmp_filtros[]= " a.nivel_id BETWEEN 2 AND 3 ";
    }
    if ( ($basico > 0) && ($medio == 0) && ($avanzado > 0) )
    {
        $tmp_filtros[]= "( a.nivel_id = 1 OR nivel_id = 3)";
    }
?>