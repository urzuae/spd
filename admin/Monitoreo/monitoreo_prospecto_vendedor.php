<?
/**
 * Funcion que regresa el nombre del vendedor
 *
 * @param conexion bd $db
 * @param int id del usuario $uid
 * @return String nombre del vendedor
 */
function asigna_vendedor($db,$uid)
{
    $sql_tmp="SELECT uid,gid,super,name,user from users WHERE uid=".$uid." AND super=8;";
    $res_tmp=$db->sql_query($sql_tmp) or die($sql_tmp);
    $array_tmp=$db->sql_fetchrow($res_tmp);
    return $array_tmp['name'];
}

/**
 * Funcion que consulta el total de contactos asignados
 * 
 * @param conexion bd $db
 * @param int id del usuario $uid
 * @return int total de asignados
 */
function regresa_total_vendedor($db,$uid)
{
    $sql1="SELECT COUNT(*) FROM crm_contactos WHERE uid=".$uid.";";
    $res_sql1=$db->sql_query($sql1) or die($sql1);
    $total_no_asignados=$db->sql_fetchrow($res_sql1);
    return $total_no_asignados[0];
}

/**
 * Funcion que consulta los 2 ultimos logs del contacto
 * 
 * @param conexion bd $db
 * @param int numero de logs existentes $uid
 * @param Array de datos de los logs
 * @return regresa un array con los datos del logs
 */
function varios_logs($db,$num_tuplas,$array_tuplas)
{
    $array_datos['concesionaria_anterior']="Concesionaria no asignada";
    $array_datos['vendedor_anterior']="Vendedor no asignado";
    
    for($j=0;$j<$num_tuplas;$j++)
    {
        if($j==0)
        {
            $array_datos['fecha_ultimo_movimiento']=$array_tuplas[$j]['timestamp'];
        }
        if($j==1)
        {
            $array_datos['fecha_penultimo_movimiento']=$array_tuplas[$j]['timestamp'];
            $array_datos['to_uid']=$array_tuplas[$j]['to_uid'];
            $array_datos['to_gid']=$array_tuplas[$j]['to_gid'];
            if($array_tuplas[$j]['to_uid']>0)
            {
                $tmp_array_vendedor=asigna_vendedor($db,$array_tuplas[$j]['to_uid']);
                $array_datos['vendedor_anterior']=$tmp_array_vendedor['user'];
            }
            
            if($array_tuplas[$j]['to_gid']>0)
            {
                $tmp_array_concesionaria=asigna_vendedor($db,$array_tuplas[$j]['to_uid']);
                $array_datos['concesionaria_anterior']=$tmp_array_concesionaria['name'];
            }
        }
    }
    return $array_datos;
}
/**
 * Funcion que consulta los 2 ultimos logs del contacto
 * 
 * @param Array de datos de los logs
 * @return regresa un array con los datos del logs
 */
function un_log($array_tuplas)
{
    #echo"\nhhh\n".$array_tuplas[0]['to_uid']."   ".$array_tuplas[0]['to_gid']."   ".$array_tuplas[0]['timestamp']."\n\n";
    $array_datos['to_uid']=$array_tuplas[0]['to_uid'];
    $array_datos['to_gid']=$array_tuplas[0]['to_gid'];
    $array_datos['fecha_ultimo_movimiento']=$array_tuplas[0]['timestamp'];
    
    $array_datos['vendedor_anterior']="Sin reasignación";
    $array_datos['concesionaria_anterior']="Sin reasignación";
    $array_datos['fecha_penultimo_movimiento']="0000-00-00 00:00:00";
    return $array_datos;
}

/**
 * Funcion que consulta los 2 ultimos logs del contacto
 * 
 * @param conexion bd $db
 * @param int id del contacto
 * @return regresa un array con los datos del logs
 */
function revisa_logs($db,$contacto_id)
{
    $sql_tmp="SELECT contacto_id,uid,from_uid,to_uid,from_gid,to_gid,timestamp from crm_contactos_asignacion_log WHERE contacto_id=".$contacto_id." ORDER BY timestamp DESC LIMIT 2;";
    $res_tmp=$db->sql_query($sql_tmp) or die($sql_tmp);
    $num_tuplas=$db->sql_numrows($res_tmp);
    while($tmp=$db->sql_fetchrow($res_tmp))
    {
        $array_tuplas[]=$tmp;
    }
    switch($num_tuplas)
    {
        case 2:
            $array_datos=varios_logs($db,$num_tuplas,$array_tuplas);
            break;
        default:
            $array_datos=un_log($array_tuplas);
            break;
    }
    return $array_datos;
}


    if (!defined('_IN_ADMIN_MAIN_INDEX')) 
    {
        die ("No puedes acceder directamente a este archivo...");
    }
    

    
    global $db, $how_many, $from, $campana_id, $nombre, $apellido_paterno, $apellido_materno, 
        $submit, $status_id, $ciclo_de_venta_id, $uid, $orderby;

$uid = $_GET['uid'];
$gid = $_GET['gid'];
$order=$_GET['order'];

$sql_vendedor  = "SELECT name FROM users WHERE uid='".$uid."' AND super=8;";
$resul_vendedor = $db->sql_query($sql_vendedor) or die($sql_vendedor);
$array_name=$db->sql_fetchrow($resul_vendedor);
$nombre_vendedor=$array_name[0];


$sql3="SELECT contacto_id,uid,gid,concat(nombre,' ',apellido_paterno,' ',apellido_materno) as Prospecto,fecha_importado  from crm_contactos WHERE uid=$uid ORDER BY gid,nombre,apellido_paterno,apellido_materno $order;";
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

$res_sql3 = $db->sql_query($sql3) or die($sql3);
$tuplas=$db->sql_numrows($res_sql3);
if($tuplas > 0)
{
    $tabla_vendedores.= '<table class="width100" border="0">
                            <thead>
                            <tr>
                            <td style="width:35%;"><a href="index.php?_module=Monitoreo&_op=monitoreo_prospecto_vendedor&uid='.$uid.'&gid='.$gid.'&order='.$order.'" style="color:white;">Nombre del Contacto</a></td>  
                            <td style="width:30%;"> </td>
                            <td style="width:30%;">Vendedor anterior</td>
                            </tr></thead>
                            <tbody>';
	while ($fila = $db->sql_fetchrow($res_sql3))
    {
        $gid=$fila['gid'];
        
    	$array_log=revisa_logs($db,$fila['contacto_id']);
        $tabla_vendedores.= "<tr class=\"row".($class_row++%2?"2":"1")."\">
                                 <td><a href='index.php?_module=Monitoreo&_op=llamada_ro&contacto_id=".$fila['contacto_id']."'>".$fila['Prospecto']."</a></td>
                                 <td></td>
                                 <td>&nbsp;</td>
                                 </tr>";
    }
    $tabla_vendedores.= "<tr>
                         <td colspan=\"4\">Total:&nbsp;&nbsp;".regresa_total_vendedor($db,$uid)." </td>
                         </tr></table>";
}
else 
{
    $tabla_vendedores= "<br>Los prospectos no estan asignados a ning&uacute;n Vendedor<br>";
}
?>