<?
if (! defined ( '_IN_MAIN_INDEX' )) {
    die ( "No puedes acceder directamente a este archivo..." );
}
global $db, $uid, $orderby, $rsort,$jquery;
$sql = "SELECT gid, super FROM users WHERE uid='$uid'";
$result = $db->sql_query ( $sql ) or die ( "Error" );
list ( $gid, $super ) = $db->sql_fetchrow ( $result );
if ($super > 6) {
    $_html = "<h1>Usted no es un Gerente</h1>";
}
else
{
    global $submit, $seleccionar, $buscar_asignado;
    if ($seleccionar) //si se van a reasignar
    {
       //buscar a que campaï¿½a lo meteremos
        $sql = "SELECT c.campana_id FROM crm_campanas_groups AS g, crm_campanas AS c WHERE c.campana_id=g.campana_id AND g.gid='$gid' ORDER BY c.campana_id  LIMIT 1"; //la primera que sea parte de un ciclo
        $result = $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );
        list ( $campana_id ) = $db->sql_fetchrow ( $result );
        $sql = "SELECT c.contacto_id FROM crm_contactos_finalizados AS c  WHERE (gid='$gid' )"; //OR gid='0'
        $result = $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );
        if ($db->sql_numrows ( $result ) > 0)
        {
            while ( list ( $contacto_id ) = $db->sql_fetchrow ( $result ) ) //revisar si lo mandaron en el post ( => on)
            {
                $tmp = "chbx_$contacto_id";
                if (array_key_exists ( "$tmp", $_POST ))
                {
                    $sql = "insert into crm_contactos select * from crm_contactos_finalizados where contacto_id = '$contacto_id'";
                    $db->sql_query ( $sql ) or die ( $sql );

                    $sql = "delete from crm_contactos_finalizados WHERE contacto_id='$contacto_id'";
                    $db->sql_query ( $sql ) or die ( $sql );

                    $sql = "SELECT uid FROM crm_contactos WHERE contacto_id='$contacto_id'"; //OR gid='0'
                    $result2 = $db->sql_query ( $sql ) or die ( "Error al asignar" . print_r ( $db->sql_error () ) );
                    list ( $from_uid ) = $db->sql_fetchrow ( $result2 );

                    $sql = "update crm_contactos set uid = '0' where contacto_id = '$contacto_id'";
                    $db->sql_query ( $sql ) or die ( $sql );

                    $sql = "INSERT INTO crm_contactos_asignacion_log (contacto_id,uid,from_uid,to_uid)VALUES('$contacto_id','$uid','$from_uid','0')";
                    $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );

                    $sql = "SELECT id FROM crm_campanas_llamadas_finalizadas WHERE contacto_id='$contacto_id' LIMIT 1";
                    $result2 = $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );
                    if (list ( $llamada_id ) = $db->sql_fetchrow ( $result2 ))
                    {
                        $sql = "insert into crm_campanas_llamadas select * from crm_campanas_llamadas_finalizadas where contacto_id = '$contacto_id'";
                        $db->sql_query ( $sql ) or die ( $sql );

                        $sql = "update crm_campanas_llamadas set status_id = '0' where contacto_id = '$contacto_id'";
                        $db->sql_query ( $sql ) or die ( $sql );

                        $sql = "delete from crm_campanas_llamadas_finalizadas WHERE contacto_id='$contacto_id'";
                        $db->sql_query ( $sql ) or die ( $sql );
                    }
                    else
                    {
                        $sql = "INSERT INTO crm_campanas_llamadas (campana_id,status_id,fecha_cita)VALUES('$campana_id','0','0000-00-00 00:00:00')";
                        $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );
                    }
                }
            }
        }
    }

    global $submit, $nombre, $apellido_paterno, $apellido_materno, $telefono, $contacto_id, $no_asignados, $order, $vehiculo, $fecha_desde, $fecha_hasta, $tipo;
    if ($submit)
    {
        $array_origen=regresa_origenes($db);
        $array_logs=logs_llamdas($db,$gid);
        $array_usuarios=usuarios($db,$gid);
        $array_tipo_cancelacion=regresa_motivos_cancelacion($db);
        $nombre_bk = $nombre;
        $apellido_paterno_bk = $apellido_paterno;
        $apellido_materno_bk = $apellido_materno;
        $vehiculo_bk = $vehiculo;
        $filtros = array();

        // construyo una tabla con los prospectos de la concesionaria $gid y que pueden estar en la tablas
        // de crm_contactos o crm_contactos_finalizados
        $tabla_tmp="prospectos_".$gid.rand(0,50000);
        $sql_delete="DROP TABLE IF EXISTS ".$tabla_tmp.";";
        $db->sql_query($sql_delete);
/*        $sql_create="CREATE TABLE $tabla_tmp AS
                    (SELECT a.contacto_id, a.nombre,a.apellido_paterno,a.apellido_materno,a.origen_id, a.uid, a.gid,'crm-contactos' AS tabla
                     FROM crm_contactos a WHERE a.gid =$gid)
                    UNION
                    (SELECT b.contacto_id, b.nombre,b.apellido_paterno,b.apellido_materno,b.origen_id, b.uid, b.gid,'crm-contactos-fin' AS tabla
                     FROM crm_contactos_finalizados b WHERE b.gid = $gid);";*/
        $sql_create="CREATE TABLE $tabla_tmp AS
                    (SELECT b.contacto_id, b.nombre,b.apellido_paterno,b.apellido_materno,b.origen_id, b.uid, b.gid,'crm-contactos-fin' AS tabla
                     FROM crm_contactos_finalizados b WHERE b.gid = $gid);";
        $res_create=$db->sql_query($sql_create) or die("Error al crear la tabla:  ".$sql_create);
        // Preparamos los diversos filtros
        if( ($fecha_desde) && ($fecha_hasta))
        {
            $t_fecha_desde=substr($fecha_desde,6,4).'-'.substr($fecha_desde,3,2).'-'.substr($fecha_desde,0,2);
            $t_fecha_hasta=substr($fecha_hasta,6,4).'-'.substr($fecha_hasta,3,2).'-'.substr($fecha_hasta,0,2);
            $filtros[]=" substr(c.timestamp,1,10) BETWEEN '".$t_fecha_desde."' AND '".$t_fecha_hasta."' " ;
        }
        if( ($fecha_desde) && (!$fecha_hasta))
        {
            $t_fecha_desde=substr($fecha_desde,6,4).'-'.substr($fecha_desde,3,2).'-'.substr($fecha_desde,0,2);
            $filtros[]=" substr(c.timestamp,1,10) ='".$t_fecha_desde."'" ;
        }
        if( (!$fecha_desde) && ($fecha_hasta))
        {
            $t_fecha_hasta=substr($fecha_hasta,6,4).'-'.substr($fecha_hasta,3,2).'-'.substr($fecha_hasta,0,2);
            $filtros[]=" substr(c.timestamp,1,10) ='".$fecha_hasta."'" ;
        }
        if ($nombre)
            $filtros[]= " a.nombre LIKE '%$nombre%' ";
        if ($apellido_paterno)
            $filtros[]= " a.apellido_paterno LIKE '%$apellido_paterno%'";
        if ($apellido_materno)
            $filtros[]= " a.apellido_materno LIKE '%$apellido_materno%'";

        $tabla=" LEFT JOIN $tabla_tmp a ON c.contacto_id=a.contacto_id";
        $campo_c=" DISTINCT (c.contacto_id),c.uid AS uid_m,c.motivo_id as no_motivo,c.motivo as texto_motivo,c.timestamp,concat(a.nombre,' ',a.apellido_paterno,' ',a.apellido_materno) as nombre, a.origen_id,a.uid,a.gid,'Can' as tipo_finalizado ";
        $campo_v=" DISTINCT (c.contacto_id),c.uid AS uid_m,1 as no_motivo,'Venta' as texto_motivo,c.timestamp,concat(a.nombre,' ',a.apellido_paterno,' ',a.apellido_materno) as nombre, a.origen_id,a.uid,a.gid,'Ven' as tipo_finalizado ";

       if(count($filtros) > 0)
            $filtro= " AND ".implode(" AND ",$filtros);

        switch($tipo)
        {
            case 'Venta':
                $tit="Fecha de Venta";
                $sql="SELECT $campo_v FROM crm_prospectos_ventas c $tabla where a.gid=$gid $filtro ORDER BY c.timestamp";
                break;
            case 'Cancelación':
                $tit="Fecha de Cancelaci&oacute;n";
                $sql="SELECT $campo_c FROM crm_prospectos_cancelaciones c $tabla where a.gid=$gid $filtro  ORDER BY c.timestamp ";
                break;
            default:
                $tit="Fecha de Cancelaci&oacute;n o Venta";
                $sql="(SELECT $campo_c FROM crm_prospectos_cancelaciones c $tabla where a.gid=$gid $filtro ) UNION  (SELECT $campo_v FROM crm_prospectos_ventas c $tabla where gid=$gid $filtro );";
                break;
        }
        $result = $db->sql_query ( $sql ) or die ( "Error al leer" . print_r ( $db->sql_error () ) );
        $tuplas = $db->sql_numrows ( $result );
        if ( $tuplas > 0)
        {
            $lista_contactos.= "<center><div id=\"loading\"><img src=\"img/loading.gif\"></div></center>
                                <table align='center' class='tablesorter' width='100%'>
                                <thead><tr><td colspan='9' align='right' ><a targer=\"_blank\" href=\"index.php?_module=$_module&_op=reporte_reasignacion&orderby=origen_id&submit=1&rsort=$rsort&nombre=$nombre_bk&apellido_paterno=$apellido_paterno_bk&apellido_materno=$apellido_materno_bk&vehiculo=$vehiculo_bk&tipo=$tipo&fecha_desde=$fecha_desde&fecha_hasta=$fecha_hasta&gid=$gid\"><font color=\"white\">Exportar esta búsqueda a Excel</font></a></td></tr>
                                <tr>
                                <th>Campaña</th>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Dias desde el Útimo contacto</th>
                                <th>Vehículo</th>
                                <th>Último usuario asignado</th>
                                <th width=\"10\">$tit</th>
                                <th>Motivo</th>
                                <th>Seleccionar</th>
                                </tr></thead>";
                $contador_registros=0;
                while ( list ( $c, $uid_m,$no_motivo,$motivo,$timestamp,$nombre,$origen_id,$c_uid,$gid,$tipo_finalizado) = htmlize ( $db->sql_fetchrow ( $result ) ) )
                {
                    $muestra=1;
                    $origen= $array_origen[$origen_id];

                    $r3 = $db->sql_query ( "SELECT modelo FROM crm_prospectos_unidades WHERE contacto_id='$c' LIMIT 1" );
                    list ( $vehiculo ) = $db->sql_fetchrow ( $r3 );
                    if ($vehiculo_bk)
                    {
                        if (strpos ( strtoupper ( $vehiculo ), strtoupper ( $vehiculo_bk ) ) === FALSE)
                        {
                            $muestra=0;
                            continue;
                        }
                    }                
                    $asignado_a = "";
                    if ($c_uid)
                    {
                        $asignado_a=$array_usuarios[$c_uid];
                    }
                    $ultimo_contacto_timestamp = "";
                    $ultimo_contacto_timestamp_bk = "";
                    $ultimo_contacto=substr($array_logs[$c],1,10);

                    $sql="SELECT UNIX_TIMESTAMP('".$array_logs[$c]."');";
                    $r3 = $db->sql_query ( $sql ) or die ( $sql );
                    $ultimo_contacto_timestamp=$db->sql_fetchfield(0,0,$r3);
                    if ($ultimo_contacto_timestamp)
                    {
                        $ultimo_contacto_timestamp = time () - $ultimo_contacto_timestamp;
                        $ultimo_contacto_timestamp_bk = $ultimo_contacto_timestamp;
                        if ($ultimo_contacto_timestamp > 0)
                        {
                            $ultimo_contacto_timestamp = $ultimo_contacto_timestamp / 60 / 60 / 24; //entre 60 segs, entre 60 mins
                            $ultimo_contacto_timestamp = sprintf ( "%u", $ultimo_contacto_timestamp ); //entero
                            $ultimo_contacto_timestamp .= " dias"; //($ultimo_contacto_timestamp!=1?"s":"")
                        }
                     }
                     if($tipo_finalizado == 'Can')
                     {
                         if($no_motivo > 0)
                            $motivo=$array_tipo_cancelacion[$no_motivo];
                     }
                     if($muestra == 1)
                     {
                         $motivo=ucfirst(strtolower($motivo));
                         $contador_registros++;
                         $lista_contactos .= "<tr class=\"row" . (++ $row_class % 2 + 1) . "\">
                                    <td>$origen</td>
                                    <td>$c</td>
						           <td style=\"cursor:pointer;\" onclick=\"location.href='index.php?_module=Directorio&_op=contacto_finalizado&contacto_id=$c&last_module=$_module&last_op=$_op';\">{$nombre}</td>
						           <td>$ultimo_contacto_timestamp</td>
						           <td>$vehiculo</td>
						           <td>$asignado_a</td>
						           <td id=\"fecha_$c\" class=\"fecha\">$timestamp</td>
						           <td id=\"motivo_$c\" class=\"motivo\">$motivo</td>
						           <td><input type=\"checkbox\" name=\"chbx_$c\" style=\"height:12;width:16;\" ></td>
						         </tr>";
                    }
                }
                $lista_contactos .= "<thead><tr class=\"row" . (++ $row_class % 2 + 1) . "\" style=\"text-align:center;\">
                                 <td colspan=\"9\">Total de prospectos: $contador_registros </td></tr></thead>
                                 <thead><tr class=\"row" . (++ $row_class % 2 + 1) . "\" style=\"text-align:center;\">
                                       <td colspan=\"9\"><input name=\"all\" type=\"button\" onclick=\"allon();\" value=\"Todos\">&nbsp;" . "<input name=\"none\" type=\"button\" onclick=\"alloff();\" value=\"Ninguno\"></td>
                                     </tr></thead>
                                     <thead><tr class=\"row" . (++ $row_class % 2 + 1) . "\" style=\"text-align:center;\">
                                     <td colspan=\"9\"><input type=\"submit\" name=\"seleccionar\" value=\"Preparar para asignación\"></td>
                                  </tr></thead></table>";
            }
            else
            {
            	$lista_contactos .= "<br><center>No se encontraron contactos con esos datos, por favor intente de nuevo.</center>";
            }
            $sql_drop="DROP TABLE $tabla_tmp;";
            $res_drop=$db->sql_query($sql_drop) or die ("Error al eliminar la tabla:  ".$sql_drop);
            $nombre = $nombre_bk;
            $apellido_paterno = $apellido_paterno_bk;
            $apellido_materno = $apellido_materno_bk;
            $vehiculo = $vehiculo_bk;
    }
    require_once ("$_includesdir/select.php");
    $sql = "SELECT nombre from crm_unidades order by nombre asc";
    $r = $db->sql_query ( $sql ) or die ( $sql );
    if($db->sql_numrows($r)>0)
    {
        while ( list ( $mod ) = $db->sql_fetchrow ( $r ) )
        {
            $modelos [] = $mod;
        }
    }
    $select_modelo = select_array ( "vehiculo", $modelos, $vehiculo );
    $tipos = array ("Cancelación", "Venta" );
    $select_tipo_registro = select_array ( "tipo", $tipos, $tipo );
}

function regresa_origenes($db)
{
    $array=array();
    $sql="SELECT  fuente_id,nombre FROM crm_fuentes ORDER BY fuente_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res)) {
            $array[$fila['fuente_id']]=$fila['nombre'];
        }
    }
    return $array;
}

function logs_llamdas($db,$gid)
{
    $sql="SELECT DISTINCT (contacto_id), timestamp
          FROM `crm_campanas_llamadas_log`
          WHERE campana_id LIKE '".$gid."%'
          ORDER BY contacto_id, timestamp asc";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['contacto_id']]=$fila['timestamp'];
        }
        $array=array_unique($array);
        }
    return $array;
}

function usuarios($db,$gid)
{
    $sql = "SELECT uid,user FROM users WHERE gid='$gid'";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['uid']]=$fila['user'];
        }
    }
    return $array;
}

function regresa_motivos_cancelacion($db)
{
    $array=array();
    $sql="SELECT  motivo_id,motivo FROM crm_prospectos_cancelaciones_motivos ORDER BY motivo_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0) {
        while($fila = $db->sql_fetchrow($res)) {
            $array[$fila['motivo_id']]=$fila['motivo'];
        }
    }
    return $array;
}
?>