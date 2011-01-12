<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$uid,$_includesdir,$cantidad,$guarda_proyeccion,$id_user,$select_ano,$select_mes,$meses_id,$ano_id;
include_once("funcion_metas.php");
$select_ano  = Regresa_Combo_Anos($ano_id);
$select_meses= Regresa_Combo_Meses($meses_id);

$sql  = "SELECT gid, super FROM users WHERE uid='".$uid."'";
$result = $db->sql_query($sql) or die("Error");
list($gid, $super) = $db->sql_fetchrow($result);
if($super == 6)
{
    $combo_vendedores=Regresa_Combo_Vendedores($db,$gid,$id_user);
    if($guarda_proyeccion)
    {
        if(count($id_user)>0)
        {
            $no_dias=0;
            $array_dias_meses=Regresa_Combo_Dias();
            foreach ($id_user as $_usuario_id)
            {
                if(count($meses_id) >0)
                {
                    foreach($meses_id as $mes_id)
                    {
                        if(Desactiva_Meta_Existente($db,$gid,$_usuario_id,$ano_id,$mes_id)==0)
                       {
                            $fecha_ini=$ano_id.'-'.$mes_id."-01 00:01:01";
                            $fecha_fin=$ano_id.'-'.$mes_id."-".$array_dias_meses[$mes_id]." 23:59:59";

                            $signos = array('$',',');
                            $cantidad = str_replace($signos,'',$cantidad);
                            if(Busca_Meta($db,$gid,$_usuario_id,$fecha_ini,$fecha_fin,$cantidad) == 0)
                            {
                                $msg="<br><br><font color='#ff0000' size='3'>ERROR EN LA FECHAS PROPORCIONADAS</font>";
                                if(Inserta_Nueva_Meta($db,$gid,$_usuario_id,$fecha_ini,$fecha_fin,$cantidad)>0)
                                    $msg="<br><br><font color='#00CC00' size='3'>SE HA REGISTRADO LA PROYECCION</font>";
                            }
                        }
                        else
                        {
                            $msg.="<br><font color='#ff0000' size='3'>La Proyeccion del año ".$ano_id." mes ".$mes_id." ya se encuentra registrada</font>";
                        }
                    }
                }
            }
            $id=0;
            $fecha_ini='';
            $fecha_fin='';
            $cantidad=0;
        }
        else
        {
            $msg="<font color='#ff0000' size='3'>Por favor, capture todos los campos</font>";
        }
        $cantidad=0;
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