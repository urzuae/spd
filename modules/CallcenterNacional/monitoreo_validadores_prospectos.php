<?php
global $db,$fecha_ini,$fecha_fin,$uid,$id_contacto,$submit,$submit_excel,$_site_title;

$_site_title = "Monitoreo de validacion de prospectos";
$uid = $_REQUEST["uid"];
$style1 = "row1";
$style2 = "row2";
if( ($_REQUEST["excel"]) or ($submit))
{
   
    $rango_fechas = "";
    $order=" ORDER BY a.timestamp,a.nombre,a.primer_apellido,a.segundo_apellido";
    if($fecha_ini != "" and $fecha_fin != "")
    {
        $rango_fechas_f = " AND date_format(a.timestamp,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'";
        $order=" ORDER BY a.nombre,a.primer_apellido,a.segundo_apellido,a.timestamp";
    }
    elseif($fecha_ini != "")
    {
        $rango_fechas_f = " AND date_format(a.timestamp,'%Y-%m-%d') = '".$_REQUEST["fecha_ini"]."'";
        $order=" ORDER BY a.nombre,a.primer_apellido,a.segundo_apellido,a.timestamp";
    }
    elseif($fecha_fin != "")
    {
        $rango_fechas_f = " AND date_format(a.timestamp,'%Y-%m-%d') = '".$_REQUEST["fecha_fin"]."'";
        $order=" ORDER BY a.nombre,a.primer_apellido,a.segundo_apellido,a.timestamp";
    }
    if($uid > 0)
    {
        $_uid_oculto.="<input type='hidden' name='uid' id='uid' value='$uid'>";
        $total_prospectos=0;
        $total=0;
        $cant = 0;
        $array_motivos_reagendaciones=Catalogo_Motivos($db,'crm_motivos_reagenda');
        $array_motivos_cancelaciones =Catalogo_Motivos($db,'crm_prospectos_cancelaciones_motivos');
        $sql = "SELECT name FROM users where uid = '$uid'";
        $cs = $db->sql_query($sql);
        list($_name) = $db->sql_fetchrow($cs);

        /**************************** LISTADO DE PROSPECTOS ***********************/
        $sql="SELECT a.contacto_id,a.fecha_alta,a.primer_contacto,a.elimina,a.motivo_fin,a.reagenda, a.motivo_reagenda,
          a.envia_concesionaria,a.timestamp,a.tipo_envio,count(a.contacto_id) as intentos,
          concat(b.nombre,' ',b.apellido_paterno,' ',b.apellido_materno) as nombre,concat('Casa:', b.tel_casa, '<br>Oficina:', b.tel_oficina, '<br>
          Movil: ', b.tel_movil, '<br>Otro:', b.tel_otro) as telefonos, b.email
          FROM crm_historial_contactos as a LEFT JOIN crm_contactos_no_asignados_finalizados as b
          ON a.contacto_id=b.contacto_id WHERE  a.uid='".$uid."' ".$rango_fechas_f." and b.nombre IS NOT NULL
          GROUP BY a.contacto_id ".$order.";";
        $res=$db->sql_query($sql) or die("Error en la consulta del historial:  ".$sql);
        $total_prospectos=$db->sql_numrows($res);
        if($total_prospectos > 0)
        {
            $total=0;
            $xnombre='';
            $_listado_prospectos='
                <table width="100%" align="center" border="0">
                <thead>
                <tr>
                <td colspan="10" align="center">Listado de Prospectos - ('.$total_prospectos.' prospectos encontrados)</td>
                </tr></thead>
                </table>
                <table width="100%" align="center" border="0" class="tablesorter">
                <thead>
                <tr>
                    <th width="3%">No</td>
                    <th width="25%">Nombre de Contacto</th>
                    <th width="17%">Tel&eacute;fonos</th>
                    <th width="14%">E-Mail</th>
                    <th width=" 9%">Fecha Mov</th>
                    <th width=" 9%">Status</th>
                    <th width="14%">Comentario</th>
                    <th width=" 5%">Intentos</th>
                    <th width=" 5%">Entrada</th>
                    <th width=" 5%">Historial</th>
                </tr></thead><tbody>';
            while(list($contacto_id,$fecha_alta,$primer_contacto,$elimina,$motivo_fin,$reagenda,$motivo_reagenda,$envia_concesionaria,$fecha,$tipo_envio,$intentos,$nombre,$telefonos,$email) = $db->sql_fetchrow($res))
            {
                $total++;
                $status='';
                $tipo_movimiento='';
                $comentario='';

                if($style == $style1)
                    $style = $style2;
                else
                    $style = $style1;

                if($elimina == 1)
                {
                    $status='Eliminado';
                    $comentario=$array_motivos_cancelaciones[$motivo_fin];
                }
                if($reagenda == 1)
                {
                    $status='Reagendado';
                    $comentario=$array_motivos_reagendaciones[$motivo_reagenda];
                }
                if($envia_concesionaria == 1)
                {
                    $status='Distribuidora  ';
                    $comentario='';
                }
                $comentario=ucfirst(strtolower(($comentario)));
                if($nombre=='')
                {
                    $array_datos=Regresa_Datos_Generales($db,$contacto_id);
                    $telefono=$array_datos[0];
                    $email=$array_datos[1];
                    $nombre=$array_datos[2];
                }
                $_listado_prospectos .= "<tr>
                                        <td class=\"$style\">$total</td>
                                        <td class=\"$style\">$nombre</td>
                                        <td class=\"$style\">$telefonos</td>
                                        <td class=\"$style\">$email</td>
                                        <td class=\"$style\" align=\"center\">".$fecha."</td>
                                        <td class=\"$style\" align=\"center\">$status</td>
                                        <td class=\"$style\">$comentario</td>
                                        <td class=\"$style\">$intentos</td>
                                        <td class=\"$style\">$tipo_envio</td>
                                        <td class=\"$style\" align=\"center\">";
                if(($status=='Reagendado') or ($intentos>1))
                {
                    $_listado_prospectos .= "<input type='button' name='ver' class='basic demo' value='Ver' style='background:#ffffff;color:#3e4f88;border:0px'onclick=\"Regresa_Historial('".$contacto_id."','".$uid."','".$fecha_ini."','".$fecha_fin."');\">";
                }
                else
                {
                    $_listado_prospectos .= "&nbsp;";
                }
                $_listado_prospectos .= "</td></tr>";
                $_csv .= "\"$nombre\",\"$array_datos[0]\",\"$array_datos[1]\",\"$fecha\",\"$status\",\"$comentario\",\"$intentos\",\"$tipo_envio\"\n";
            }
            $_listado_prospectos .= "</tbody><thead><tr><td colspan='10'>Total de prospectos:  $total_prospectos</tr></thead></table>";
        }
        else
        {
            $_listado_prospectos='<center>No existen prospectos en el rango de fechas seleccionados</center>';
        }
    }
}
if($_REQUEST["excel"])
{
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="'.$_op.'_'.date("d-m-Y").'.csv"');
    die($_csv);
}

function Regresa_Datos_Generales($db,$contacto_id)
{
    $array_tmp=array();
    $sql="SELECT contacto_id,concat(nombre,' ',apellido_paterno,' ',apellido_materno) as nombre,concat('Casa:', tel_casa, '<br>Oficina:', tel_oficina, '<br>
              Movil: ', tel_movil, '<br>Otro:', tel_otro) as telefonos, email
              FROM crm_contactos_no_asignados WHERE contacto_id=".$contacto_id." limit 1;;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)>0)
    {
        list($contacto_id,$nombre,$telefonos, $email)=$db->sql_fetchrow($res);
        $array_tmp[]=$telefonos;
        $array_tmp[]=$email;
        $array_tmp[]=$contacto_id.' '.$nombre;
    }
    return $array_tmp;
}

function Catalogo_Motivos($db,$tabla)
{
    $array=array();
    $sql="SELECT motivo_id,motivo FROM ".$tabla." ORDER BY motivo_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)>0)
    {
        while(list($motivo_id,$motivo)=$db->sql_fetchrow($res))
        {
            $array[$motivo_id]=$motivo;
        }
    }
    return $array;
}

?>