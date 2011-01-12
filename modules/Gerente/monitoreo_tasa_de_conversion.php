<?
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}


global $db, $user, $uid, $fecha_ini, $fecha_fin, $submit, $fuente_id;

if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini_script=$fecha_ini;
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND v.timestamp>'$fecha_ini 00:00:00'";
}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin_script=$fecha_fin;
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND v.timestamp<'$fecha_fin 23:59:59'";
}
if($fuente_id){
	$and_origen = " AND origen_id = '$fuente_id'";
}

$sql  = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
$gid = sprintf("%04d", $gid);
if ($super > 6)
{
  $_html = "<h1>Usted no es un Gerente</h1>";
}
else
{
    if($gid!='')
    {
        $res_no_visibles=$db->sql_query("SELECT fuente_id FROM crm_groups_fuentes WHERE gid='".$gid."';");
        if($db->sql_numrows($res_no_visibles) > 0)
        {
            while(list($id_fuente)=$db->sql_fetchrow($res_no_visibles))
            {
                $array[]=$id_fuente;
            }
            $lista_gid_no_visibles=implode(',',$array);
        }
    }
    if($lista_gid_no_visibles!='')
        $filtro_gid=" AND fuente_id NOT IN (".$lista_gid_no_visibles.") ";

    $sql = "SELECT fuente_id, nombre FROM crm_fuentes where fuente_id > 1 ".$filtro_gid.";";
    $result = $db->sql_query($sql) or die("Error");
    $select_fuentes = "<select name=\"fuente_id\">
                     <option value=\"0\">Todos</option>";
    while(list($origen_id, $nombre_origen) = $db->sql_fetchrow($result))
    {
    	$selected = "";
        if($fuente_id == $origen_id)
        {
            $nombre_fuente = $nombre_origen;
            $selected = " SELECTED";
        }
        $select_fuentes .= "<option value=\"$origen_id\" $selected>$nombre_origen</option>";
        $array_origenes[$origen_id]=$nombre_origen;
    }
    $select_fuentes .= "</select>";



	if ($submit)
	{
		//este cuenta las que tienen a su contacto en finalizados
		$sql = "SELECT DISTINCT v.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.chasis,v.precio
		        FROM crm_prospectos_ventas as v, 
		             crm_contactos_finalizados AS c  
		        WHERE v.eliminar=0 AND v.contacto_id=c.contacto_id AND
		              c.gid='$gid'
		              $and_fecha
		              $and_origen ORDER BY c.contacto_id"; //OR gid='0'
        $r = $db->sql_query($sql) or die($sql);
		$num_ventas = $db->sql_numrows($r);
        if($num_ventas>0)
        {
            while($tupla = $db->sql_fetchrow($r))
            {
               $tmp_count=$tupla['contacto_id'].$tupla['modelo_id'].$tupla['version_id'].$tupla['transmision_id'].$tupla['timestamp_unidades'].$tupla['timestamp'].$tupla['chasis'];
               $array_count[$tmp_count]=$tmp_count;
            }

        $sql_datos="SELECT DISTINCT v.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.precio,v.chasis,v.uid,c.origen_id,concat(c.nombre,' ',c.apellido_paterno,' ',c.apellido_materno) as nombre,c.tel_casa,c.email,c.tel_oficina
		        FROM crm_prospectos_ventas as v,
		             crm_contactos_finalizados AS c
		        WHERE v.eliminar=0 AND  v.contacto_id=c.contacto_id AND
		              c.gid='$gid'
		              $and_fecha
		              $and_origen ORDER BY nombre,apellido_paterno,apellido_materno";
            $res_datos=$db->sql_query($sql_datos);
            if($db->sql_numrows($res_datos) > 0)
            {
                $contador=0;
                while($fila=$db->sql_fetchrow($res_datos))
                {
                    $tmp=$fila['contacto_id'].$fila['modelo_id'].$fila['version_id'].$fila['transmision_id'].$fila['timestamp_unidades'].$fila['timestamp'].$fila['chasis'];
                    $tmp_uid=$fila['uid'];
                    $array_datos[$tmp]=$fila;
                    $array_datos[$tmp]['modelo']=vehiculo($db,$fila['contacto_id'],$fila['modelo_id'],$fila['version_id'],$fila['transmision_id'],$fila['timestamp_unidades']);
                    $array_datos[$tmp]['chasis']=chasis($db,$fila['contacto_id'],$fila['modelo_id'],$fila['version_id'],$fila['transmision_id'],$fila['timestamp'],$fila['timestamp_unidades'],$fila['chasis']);
                    $array_datos[$tmp]['vendedor']=vendedor($db,$tmp_uid);
                    $contador++;
                }
            }
        }
        // y este cuenta a los que no tienen su contacto finalizado aún
        $sql = "SELECT DISTINCT v.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.chasis,v.precio
		        FROM crm_prospectos_ventas as v, 
		             crm_contactos AS c  
		        WHERE v.eliminar=0 AND v.contacto_id=c.contacto_id AND
		              gid='$gid'  
		              $and_fecha
		              $and_origen ORDER BY c.contacto_id"; //OR gid='0'

		$r = $db->sql_query($sql) or die($sql);
        $num=$db->sql_numrows($r);
		$num_ventas += $num;
        if($num>0)
        {
                while($tupla = $db->sql_fetchrow($r))
                {
                    $tmp_count=$tupla['contacto_id'].$tupla['modelo_id'].$tupla['version_id'].$tupla['transmision_id'].$tupla['timestamp_unidades'].$tupla['timestamp'].$tupla['chasis'];
                    $array_count[$tmp_count]=$tmp_count;
                }

            $sql_datos="SELECT DISTINCT v.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.precio,v.chasis,v.uid,c.origen_id,concat(c.nombre,' ',c.apellido_paterno,' ',c.apellido_materno) as nombre,c.tel_casa,c.email,c.tel_oficina
                     FROM crm_prospectos_ventas as v,
		             crm_contactos  AS c
                    WHERE v.eliminar=0 AND v.contacto_id=c.contacto_id AND
		              gid='$gid'
		              $and_fecha
		              $and_origen ORDER BY nombre,apellido_paterno,apellido_materno";
            $res_datos=$db->sql_query($sql_datos);
            if($db->sql_numrows($res_datos) > 0)
            {
                while($fila=$db->sql_fetchrow($res_datos))
                {
                    $tmp=$fila['contacto_id'].$fila['modelo_id'].$fila['version_id'].$fila['transmision_id'].$fila['timestamp_unidades'].$fila['timestamp'].$fila['chasis'];
                    $tmp_uid=$fila['uid'];
                    $array_datos[$tmp]=$fila;
                    $array_datos[$tmp]['modelo']=vehiculo($db,$fila['contacto_id'],$fila['modelo_id'],$fila['version_id'],$fila['transmision_id'],$fila['timestamp_unidades']);
                    $array_datos[$tmp]['chasis']=chasis($db,$fila['contacto_id'],$fila['modelo_id'],$fila['version_id'],$fila['transmision_id'],$fila['timestamp'],$fila['timestamp_unidades'],$fila['chasis']);
                    $array_datos[$tmp]['vendedor']=vendedor($db,$tmp_uid);
                    $contador++;
                }
            }
        }
		$sql = "SELECT COUNT(v.contacto_id) 
		        FROM crm_contactos as v 
		        WHERE gid='$gid' 
		              $and_fecha 
		              $and_origen";
        $r = $db->sql_query($sql) or die($sql);
		list($num_capturados) = $db->sql_fetchrow($r);

        $sql_fin = "SELECT COUNT(v.contacto_id)
		        FROM crm_contactos_finalizados as v
		        WHERE gid='$gid'
		              $and_fecha
		              $and_origen";
        $r_fin = $db->sql_query($sql_fin) or die($sql_fin);
        list($num_capturados_fin) = $db->sql_fetchrow($r_fin);

        $num_capturados= $num_capturados + $num_capturados_fin;

		if ($num_capturados)
			$tasa = sprintf("%02.2f%%", $num_ventas / $num_capturados * 100);
		else
			$tasa = "0%";
	    if($fuente_id){
	    	$tabla_fuente = "<tr>
			<td></td>
			<td></td>
			<td style=\"font-size:24px;text-align:center;\">Origen $nombre_fuente</td>
		</tr>";
	    }
        $_html = "
        <table border=\"0\">
		<tr>
			<td></td>
			<td></td>
			<td style=\"font-size:24px;text-align:center;\"><a href='#' id='visualiza' style=\"font-size:24px;color:#3e4f88;\">$num_ventas ventas</a></td>
		</tr>
		<tr>
			<td style=\"font-size:24px;font-weight:bold;\">$tasa</td>
			<td style=\"font-size:36px;\">=</td>
			<td><hr style=\"width:240px;\"></td>
		</tr>
		<tr>
			<td></td>
			<td></td>
			<td style=\"font-size:24px;text-align:center;\">$num_capturados prospectos</td>
		</tr>
		$tabla_fuente
		</table><br>";
        if(count($array_datos) > 0)
        {
            $_html_datos= "<table border=\"0\" width=\"90%\" class='tablesorter'>
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Campana</th>
                            <th>Nombre del comprador</th>
                            <th>Vendedor</th>
                            <th>Tel casa</th>
                            <th>Tel oficina</th>
                            <th>Email</th>
                            <th>Fecha Vta</th>
                            <th>Producto</th>
                            <th>No. Serie</th>
                            <th>Modifcar</th>
                            <th>Cancelar</th>
                         </tr>
                         </thead><tbody>";
            $con=0;
            $atributos="width=450,height=210,toolbar=No,location=No,directories=No,status=No,menubar=No,scrollbars=No,resizable=NO";
            foreach($array_datos as $contacto => $array)
            {
                $con++;
                $tmp_con=$array_datos[$contacto]['contacto_id'];
                $tmp_mod=$array_datos[$contacto]['modelo_id'];
                $tmp_ver=$array_datos[$contacto]['version_id'];
                $tmp_tra=$array_datos[$contacto]['transmision_id'];
                $tmp_tim=$array_datos[$contacto]['timestamp'];
                $tmp_cha=$array_datos[$contacto]['chasis'];
                $url="index.php?_module=Gerente&_op=actualiza_venta&contacto_id=$tmp_con&modelo_id=$tmp_mod&version_id=$tmp_ver&transmision_id=$tmp_tra&timestamp=$tmp_tim&chasis=$tmp_cha&fecha_ini=$fecha_ini_script&fecha_fin=$fecha_fin_script&fuente_id=$fuente_id";
                $tmp_origen=$array_datos[$contacto]['origen_id'];
                $_html_datos.= "<tr class=\"row".($class_row++%2?"2":"1")."\">
                          <td>&nbsp;".$con."</td>
                          <td>&nbsp;".$array_origenes[$tmp_origen]."</td>
                          <td>&nbsp;".$array_datos[$contacto]['nombre']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['vendedor']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['tel_casa']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['tel_oficina']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['email']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['timestamp']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['modelo']."</td>
                          <td>&nbsp;".$array_datos[$contacto]['chasis']."</td>
                          <td align='center'><a href='#' onclick=\"window.open('$url','ventana','$atributos');\"><img src='img/edit.gif' style='border: 0px solid white; cursor: pointer;' title='Actualizar informacion sobre la venta de vehiculo'></a></a></td>
                          <td align='center'><a href='#' onclick=\"elimina_venta('$tmp_con','$tmp_mod','$tmp_ver','$tmp_tra','$tmp_tim','$tmp_cha','$fecha_ini','$fecha_fin','$fuente_id');\"><img src='img/del.gif' style='border: 0px solid white; cursor: pointer;' title='Cancelar Venta de vehiculo'></a></td>
                          </tr>";
              }
            $_html_datos.= "</tbody><thead><tr><td colspan='12'> Total:  ".count($array_count)."</td></tr></thead></table></div>";
        }
		
	}
}
$fecha_ini = date_reverse($fecha_ini);
$fecha_fin = date_reverse($fecha_fin); 

function vehiculo($db,$contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp_unidades)
{
    $filtro='';
    if($modelo_id > 0)
        $filtro.=" AND modelo_id=".$modelo_id;
    if($version_id > 0)
        $filtro.=" AND version_id=".$version_id;
    if($transmision_id > 0)
        $filtro.=" AND transmision_id=".$transmision_id;
    if($timestamp_unidades!='')
        $filtro.=" AND timestamp='".$timestamp_unidades."' ";

    $regreso='';
    $sql="SELECT nombre from crm_unidades where unidad_id=".$modelo_id." limit 1;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)> 0)
    {
        $regreso=$db->sql_fetchfield(0,0, $res);
    }
    
    if($regreso=='')
    {
        $sql2="SELECT modelo from crm_prospectos_unidades where contacto_id=".$contacto_id." ".$filtro." limit 1;";
        $res2=$db->sql_query($sql2);
        if($db->sql_numrows($res2)> 0)
        {
            $regreso=$db->sql_fetchfield(0,0, $res2);
        }

    }
    return $regreso;
}

function chasis($db,$contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp,$timestamp_unidades,$chasis)
{
    if($modelo_id > 0)
        $filtro.=" AND modelo_id=".$modelo_id;
    if($version_id > 0)
        $filtro.=" AND version_id=".$version_id;
    if($transmision_id > 0)
        $filtro.=" AND transmision_id=".$transmision_id;
    if($timestamp!='')
        $filtro.=" AND timestamp='".$timestamp."'";
    if($timestamp_unidades!='')
        $filtro.=" AND timestamp_unidades='".$timestamp_unidades."'";
    if($chasis!='')
        $filtro.=" AND chasis='".$chasis."'";

    $regreso='';
    $sql="SELECT chasis from crm_prospectos_ventas where contacto_id=".$contacto_id." ".$filtro." limit 1;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)> 0)
    {
        $regreso=$db->sql_fetchfield(0,0, $res);
    }
    return $regreso;
}
function vendedor($db,$uid)
{
    $regreso='';
    $sql="SELECT name from users where uid=".$uid." limit 1;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)> 0)
    {
        $regreso=$db->sql_fetchfield(0,0, $res);
    }
    return $regreso;
}
function limpiarArray($array){
        $retorno=null;
        if($array!=null){
            $retorno[0]=$array[0];
        }
        for($i=1;$i<count($array);$i++){
            $repetido=false;
            $elemento=$array[$i];
            for($j=0;$j<count($retorno) && !$repetido;$j++){
                if($elemento==$retorno[$j]){
                    $repetido=true;
                }
            }
            if(!$repetido){
                $retorno[]=$elemento;
            }
        }
        return $retorno;
    }
?>