<?
include_once($_includesdir."/Genera_Excel.php");

function count_asignados($db,$uid,$tabla,$filtro)
{
	if(!empty($filtro))
		$filtro=" AND ".$filtro;
	$sql_tmp="SELECT COUNT(*) FROM ".$tabla." WHERE uid=".$uid." AND uid>0 ".$filtro.";";
    $res_tmp=$db->sql_query($sql_tmp);
    $num_tmp=$db->sql_fetchrow($res_tmp);
    return $num_tmp[0];
}

function count_total($db,$gid,$tabla,$filtro)
{
	if(!empty($filtro))
		$filtro=" AND ".$filtro;

	$sql_tmp="SELECT COUNT(*) FROM ".$tabla." WHERE gid=".$gid." ".$filtro.";";
    $res_tmp=$db->sql_query($sql_tmp);
    $num_tmp=$db->sql_fetchrow($res_tmp);
    return $num_tmp[0];
}

global $db, $tabla, $gid, $from, $campana_id, $uid, $orderby,$tmp_filtros,$order,$url,$_site_name;
$tabla=" reporte_contactos_asignados ";
if (!defined('_IN_ADMIN_MAIN_INDEX')) 
{
	die ("No puedes acceder directamente a este archivo...");
}    
	include_once("regresa_filtros.php");
	$filtro=implode(" AND ",$tmp_filtros);
	$sql="SELECT DISTINCT vendedor, uid FROM ".$tabla." where uid >0  and ".$filtro." order by vendedor ASC;";
	$contador_total=count_total($db,$gid,$tabla,$filtro);	
	switch($order)
	{
		case "asc":
			$order="desc";
			break;
		case "desc":
			$order="asc";
			break;
		default:
			$order="desc";
			break;
	}
	$contador_asignado=0;
	$contador_no_asignado=0;
	$res = $db->sql_query($sql);
	if($db->sql_numrows($res) > 0)
	{
		$tabla_vendedores.= '
			<table width="100%" border="0">
				<thead>
			    	<tr>
			        	<td style="width:60%;"><a href="index.php?_module=Monitoreo&_op=monitoreo_vendedor_concesionaria&order='.$order.''.$url.'" style="color:white;">Nombre del Vendedor</a></td>  
			            <td style="width:40%;">Asignados</td>
			        </tr>
				</thead>
			    <tbody>';
			    $tmp_asig=0;
			   while($rs = $db->sql_fetchrow($res))
				{
			    	$vendedor=$rs['uid'];
			        $tmp_asig=count_asignados($db,$rs['uid'],$tabla,$filtro);
			        $contador_asignado = $contador_asignado + $tmp_asig;
			        if( ($tmp_asig) > 0)
			        {
			        	$tabla_vendedores.= "<tr class=\"row".($class_row++%2?"2":"1")."\">
			        	<td><a href='index.php?_module=Monitoreo&_op=monitoreo_prospectos_vendedor&uid=$vendedor$url'>".$rs['vendedor']."</a></td>
			        	<td>".$tmp_asig."</td>
			        	</tr>";
			        }
				}
		$tabla_vendedores.= "
			<tr>
			<td>Total</td>
			<td>".$contador_asignado."</td>
			</tr></table>";
          $objeto = new Genera_Excel($tabla_vendedores,'Asignacion-Vendedores',$_site_name);
          $boton_excel=$objeto->Obten_href();

	}
	else 
	{
		$tabla_vendedores= "<br>Los prospectos no estan asignados a ning&uacute;n Vendedor<br>";
	}
	$tabla_vendedores.= "
		<table width='60%' border='0'>
			<tr>
				<td colspan='2'>Total:&nbsp;&nbsp;".$contador_total." </td>
			</tr>                                 
			<tr>
				<td colspan='2'>Total de asignados:&nbsp;&nbsp;".$contador_asignado." </td>
			</tr>                                 
			<tr>
				<td colspan='2'>Total de No asignados:&nbsp;&nbsp;".( $contador_total  -  $contador_asignado) ."</td>
			</tr>                                 
		</table>";

?>
