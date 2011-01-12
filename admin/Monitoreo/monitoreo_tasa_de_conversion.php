<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $user, $gid, $fecha_ini, $fecha_fin, $submit, $fuente_id;
if ($fecha_ini)
{
  $titulo .= " desde $fecha_ini";
  $fecha_ini = date_reverse($fecha_ini);
  $and_fecha .= " AND v.timestamp >='$fecha_ini 00:01:01'";
  $and_fecha_imp .= " AND c.timestamp >='$fecha_ini 00:01:01'";

}
if ($fecha_fin)
{
  $titulo .= " hasta $fecha_fin";
  $fecha_fin = date_reverse($fecha_fin);
  $and_fecha .= " AND v.timestamp<='$fecha_fin 23:59:59'";
  $and_fecha_imp .= " AND c.timestamp <='$fecha_fin 23:59:59'";
}
if($fuente_id){
	$and_origen = " AND c.origen_id = '$fuente_id'";
}
if($gid)
{
    	$and_gid = " AND c.gid = '$gid'";
}

$select_fuentes = "<select name=\"fuente_id\">
                   <option value=\"0\">Todos</option>";
$sql = "SELECT fuente_id, nombre FROM crm_fuentes where fuente_id>1";
$result = $db->sql_query($sql) or die("Error");
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

$select_concesionarias = "<select name=\"gid\">
                          <option value=\"0\">Todas</option>";
$sql = "SELECT gid, name FROM groups where gid>0 order by gid;";
$result = $db->sql_query($sql) or die("Error");
while(list($id_gid, $name) = $db->sql_fetchrow($result))
{
	$selected = "";
    if($id_gid == $gid)
    {
	  $nombre_concecionaria = $name;
	  $selected = " SELECTED";
    }
	$select_concesionarias .= "<option value=\"$id_gid\" $selected>$id_gid&nbsp;&nbsp;&nbsp;$name</option>";
    $array_concesionarias[$id_gid]=$name;
}
$select_concesionarias.= "</select>";

if ($submit)
{
    // sacamos el total de prospectos por de ese filtro

    $sql = "SELECT COUNT(c.contacto_id) FROM crm_contactos as c WHERE c.contacto_id > 0 $and_fecha_imp $and_origen $and_gid";
    $r = $db->sql_query($sql) or die($sql);
    list($num_capturados) = $db->sql_fetchrow($r);

    $sql_fin = "SELECT COUNT(c.contacto_id) FROM crm_contactos_finalizados as c WHERE c.contacto_id > 0 $and_fecha_imp $and_origen $and_gid";
    $r_fin = $db->sql_query($sql_fin) or die($sql_fin);
    list($num_capturados_fin) = $db->sql_fetchrow($r_fin);
    $num_capturados= $num_capturados + $num_capturados_fin;
    //este cuenta las que tienen a su contacto en finalizados
	$sql = "SELECT DISTINCT c.contacto_id, v.modelo_id, v.version_id, v.transmision_id ,v.timestamp,v.timestamp_unidades,v.chasis,v.precio
            FROM crm_prospectos_ventas as v,crm_contactos_finalizados AS c
		    WHERE  v.contacto_id=c.contacto_id $and_fecha $and_origen $and_gid"; //OR gid='0'
    $r = $db->sql_query($sql) or die($sql);
	$num_ventas = $db->sql_numrows($r);
    if($num_ventas>0)
    {
        while($tupla = $db->sql_fetchrow($r))
        {
            $tmp_count=$tupla['contacto_id'].$tupla['modelo_id'].$tupla['version_id'].$tupla['transmision_id'].$tupla['timestamp_unidades'].$tupla['timestamp'].$tupla['chasis'];
            $array_count[$tmp_count]=$tmp_count;
        }
        $sql_datos="SELECT DISTINCT c.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.chasis,v.precio,v.uid,c.origen_id,concat(c.nombre,' ',c.apellido_paterno,' ',c.apellido_materno) as nombre,c.tel_casa,c.email,c.tel_oficina
		        FROM crm_prospectos_ventas as v,
		             crm_contactos_finalizados AS c
		        WHERE v.contacto_id=c.contacto_id $and_fecha $and_origen $and_gid  ORDER BY nombre,apellido_paterno,apellido_materno";

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
		$sql = "SELECT DISTINCT c.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.chasis,v.precio
		        FROM crm_prospectos_ventas as v ,crm_contactos AS c  WHERE v.contacto_id=c.contacto_id
                $and_fecha $and_origen $and_gid";

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

            $sql_datos="SELECT DISTINCT c.contacto_id, v.modelo_id, v.version_id, v.transmision_id,v.timestamp,v.timestamp_unidades,v.chasis,v.precio,v.uid,c.origen_id,concat(c.nombre,' ',c.apellido_paterno,' ',c.apellido_materno) as nombre,c.tel_casa,c.email,c.tel_oficina
		        FROM crm_prospectos_ventas as v,
		             crm_contactos  AS c
		        WHERE v.contacto_id=c.contacto_id 
		              $and_fecha
		              $and_origen $and_gid ORDER BY nombre,apellido_paterno,apellido_materno";
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

    if ($num_capturados)
        $tasa = sprintf("%02.2f%%", $num_ventas / $num_capturados * 100);
    else
        $tasa = "0%";

    if($fuente_id)
    {
        $tabla_fuente = "<tr>
		<td></td>
		<td></td>
		<td style=\"font-size:24px;text-align:center;\">Origen $nombre_fuente</td>
		</tr>";
    }
	if($gid)
    {
        $tabla_fuente.= "<tr>
		<td></td>
		<td></td>
		<td style=\"font-size:24px;text-align:center;\">$nombre_concecionaria</td>
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
            $_html_datos= "<table border=\"0\" width=\"90%\" class=\"tablesorter\">
                        <thead>
                        <tr>
                            <th>No</th>
                            <th>Campana</th>
                            <th>Nombre del comprador</th>
                            <th>Vendedor</th>
                            <th>Tel casa</th>
                            <th>Tel oficina</th>
                            <th>Email</th>
                            <th>fecha Vta</th>
                            <th>Modelo</th>
                            <th>Chasis</th>
                         </tr>
                         </thead><tbody>";
            $con=0;
            foreach($array_datos as $contacto => $array)
            {
                $con++;
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
                          </tr>";
            }
            $_html_datos.= "</tbody><thead><tr><td colspan='10'> Total:  ".count($array_count)."</td></tr></thead></table></div>";

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
?>