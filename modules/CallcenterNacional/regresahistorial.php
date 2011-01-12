<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
header("Content-type: text/html; charset=iso-8859-1");
global $db, $contacto_id,$random,$_site_title;
$uid=$uid_id;
$_site_title = "Regresar historial";
if($fecha_ini != "" and $fecha_fin != "")
{
    $rango_fechas_f = " AND date_format(a.timestamp,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'";
}
elseif($fecha_ini != "")
{
    $rango_fechas_f = " AND date_format(a.timestamp,'%Y-%m-%d') = '".$_REQUEST["fecha_ini"]."'";
}
elseif($fecha_fin != "")
{
    $rango_fechas_f = " AND date_format(a.timestamp,'%Y-%m-%d') = '".$_REQUEST["fecha_fin"]."'";
}

if($contacto_id > 0)
{
    $total_prospectos=0;
    $total=0;
    $array_vendedores=Catalogo_Vendedor($db);
    $array_motivos_reagendaciones=Catalogo_Motivos($db,'crm_motivos_reagenda');
    $array_motivos_cancelaciones =Catalogo_Motivos($db,'crm_prospectos_cancelaciones_motivos');
  
    $sql="SELECT a.contacto_id,concat(b.nombre,' ',b.apellido_paterno,' ',b.apellido_materno) as nombre,a.fecha_alta,
    a.primer_contacto,a.elimina,a.motivo_fin,a.reagenda, a.motivo_reagenda, a.envia_concesionaria,
    a.timestamp,a.tipo_envio,a.uid,concat('Casa:', b.tel_casa, '<br>Oficina:', b.tel_oficina, '<br>
          Movil: ', b.tel_movil, '<br>Otro:', b.tel_otro) as telefonos, b.email
    FROM crm_historial_contactos as a LEFT JOIN crm_contactos_no_asignados_finalizados as b
    ON a.contacto_id=b.contacto_id WHERE a.contacto_id=".$contacto_id." ORDER BY a.timestamp;";
    $res=$db->sql_query($sql) or die("Error en la consulta del historial:  ".$sql);
    $total_prospectos=$db->sql_numrows($res);
    if($total_prospectos > 0)
    {
            $_listado_prospectos ='
        <table width="90%" align="center"><thead>
        <tr height="30">
            <td colspan="5" align="center">Listado de Movimientos)</td>
        </tr></thead>';
        $total=0;
        while(list($contacto_id,$nombre,$fecha_alta,$primer_contacto,$elimina,$motivo_fin,$reagenda,$motivo_reagenda,$envia_concesionaria,$fecha,$tipo_envio,$uid,$telefonos,$email) = $db->sql_fetchrow($res))
        {
            $total++;
            if($total==1)
            {
                $array_datos=Regresa_Datos_Generales($db,$contacto_id);
                $nombre=str_replace('á','&aacute;',$nombre);
                $_listado_prospectos .= "<tr><td colspan='4'><font color='#800000'>Nombre:</font>   ".$nombre."
                                    <br><font color='#800000'>Tel&eacute;fonos:</font>  ".$telefonos."
                                    <br><font color='#800000'>Email:</font>  ".$email."</td></tr>";
                $_listado_prospectos .='<thead<tr>
                <td width="20%"><font color="white">Fecha Mov</font></td>
                <td width="15%"><font color="white">Status</font></td>
                <td width="25%"><font color="white">Comentario</font></td>
                <td width="30%"><font color="white">Vendedor</font></td>
                <td width="10%"><font color="white">Entrada</font></td></tr></thead>';
            }
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
                $status='Distribuidora';
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
                                        <td class=\"$style\" align=\"center\">".$fecha."</td>
                                        <td class=\"$style\" align=\"center\">$status</td>
                                        <td class=\"$style\">$comentario</td>
                                        <td class=\"$style\">$array_vendedores[$uid]</td>
                                        <td class=\"$style\">$tipo_envio</td></tr>";
        }
            $_listado_prospectos .= "<thead><tr><td colspan='5'>Total de movimientos:  $total_prospectos</tr></thead></table>";
    }
}
echo $_listado_prospectos;
die();


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
function Catalogo_Vendedor($db)
{
    $array=array();
    $res=$db->sql_query("SELECT uid, name FROM users WHERE gid = 1 AND super =10 ORDER BY uid;");
    if($db->sql_numrows($res) > 0)
    {
        while(list($uid,$name) = $db->sql_fetchrow($res))
        {
            $array[$uid]=$name;
        }
    }
    return $array;
}
?>
