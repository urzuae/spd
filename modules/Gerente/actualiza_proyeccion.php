<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$uid,$_module,$_op,$user_id,$ano,$mes,$actualiza_proyeccion,$cantidad;
include_once("funcion_metas.php");
$sql  = "SELECT gid, super FROM users WHERE uid='".$uid."'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if($super == 6)
{
    $array_meses=Regresa_Array_Meses();
    if(!$actualiza_proyeccion)
    {
        $array_vendedeores=Regresa_Vendedores($db,$gid,$user_id);
        $username=$array_vendedeores[$user_id];
        $nm_mes=$array_meses[str_pad($mes,2,'0',STR_PAD_LEFT)];
        $cantidad=0;
        $sql="SELECT id,cantidad FROM crm_proyeccion
         WHERE uid=".$user_id." AND YEAR(fecha_inicio)='".$ano."' AND MONTH(fecha_inicio)='".$mes."';";
        $res=$db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res)>0)
        {
            list($id,$cantidad) = $db->sql_fetchrow($res);
        }
    }
    else
    {
        $array_dias_meses=Regresa_Combo_Dias();
        $signos = array('$',',');
        $cantidad = str_replace($signos,'',$cantidad);
        $sql="SELECT id FROM crm_proyeccion WHERE uid=".$user_id." AND YEAR(fecha_inicio)='".$ano."' AND MONTH(fecha_inicio)='".$mes."';";
        $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res) > 0)
        {
            $upd="UPDATE crm_proyeccion SET  cantidad='".$cantidad."' WHERE uid=".$user_id." AND YEAR(fecha_inicio)='".$ano."' AND MONTH(fecha_inicio)='".$mes."';";
        }
        else
        {
            $fecha_ini=$ano.'-'.str_pad($mes,2,'0',STR_PAD_LEFT)."-01 00:01:01";
            $fecha_fin=$ano.'-'.str_pad($mes,2,'0',STR_PAD_LEFT)."-".$array_dias_meses[str_pad($mes,2,'0',STR_PAD_LEFT)]." 23:59:59";
            $sql="select TIMESTAMPDIFF(DAY,'".$fecha_ini."','".$fecha_fin."') as dias;";
            $res=$db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
            list($no_dias)= $db->sql_fetchrow($res);
            if($no_dias >= 0)
            {
                $no_dias=$no_dias+1;
                $fecha_actual=date('Y-m-d H:i:s');
                $upd="INSERT INTO crm_proyeccion (gid,uid,fecha_inicio,fecha_concluye,cantidad,no_dias,timestamp,active)
                VALUES ('".$gid."','".$user_id."','".$fecha_ini."','".$fecha_fin."','".$cantidad."','".$no_dias."','".$fecha_actual."','1');";
            }
        }
        $db->sql_query($upd) or die("Error al actualizar:  ".$upd);
        header("location: index.php?_module=Gerente&_op=consulta_proyeccion&ano_id=$ano");

    }
}

function Busca_Meta($db,$gid,$id,$fecha_inicio,$fecha_final,$cantidad)
{
    $reg=0;
    $sql="SELECT id FROM crm_proyeccion WHERE gid='".$gid."' AND uid='".$id."' AND fecha_inicio='".$fecha_inicio."' AND fecha_concluye='".$fecha_final."' AND cantidad='".$cantidad."';";
    $res=$db->sql_query($sql) or die("Error en la consulta de vendedores:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {
        $reg=1;
    }
    return $reg;
}
function Inserta_Nueva_Meta($db,$gid,$uid,$fecha_inicio,$fecha_final,$cantidad)
{
    # calculamos el numero de dias;
    $reg=0;
    $sql="select TIMESTAMPDIFF(DAY,'".$fecha_inicio."','".$fecha_final."') as dias;";
    $res=$db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
    list($no_dias)= $db->sql_fetchrow($res);
    if($no_dias >= 0)
    {
        $no_dias=$no_dias+1;
        $fecha_actual=date('Y-m-d H:i:s');
        $ins="INSERT INTO crm_proyeccion (gid,uid,fecha_inicio,fecha_concluye,cantidad,no_dias,timestamp,active)
          VALUES ('".$gid."','".$uid."','".$fecha_inicio."','".$fecha_final."','".$cantidad."','".$no_dias."','".$fecha_actual."','1');";
        if($db->sql_query($ins) or die ("Error en el update:  ".$ins))
            $reg=1;
    }
    return $reg;

}

function Desactiva_Meta_Existente($db,$gid,$id,$ano_id,$mes_id)
{
    $reg=0;
    $sql="SELECT id FROM crm_proyeccion WHERE gid='".$gid."' AND uid='".$id."' AND YEAR(fecha_inicio) ='".$ano_id."' AND MONTH(fecha_inicio) ='".$mes_id."';";
    $res=$db->sql_query($sql) or die ("Error en el update:  ".$sql);
    if($db->sql_numrows($res)>0)
        $reg=1;
    return $reg;
}
?>