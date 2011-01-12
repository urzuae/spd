<?php
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

header('Content-Type: text/html; charset=iso-8859-1');
global $db, $opc,$id,$orden,$_site_title;
$_site_title = "Ciclo de ventas";
$opc   =$_GET['opc'];
$id    =$_GET['id'];
$consec=$_GET['consec'];
$nm_ciclo=$_GET['nm_ciclo'];
$reg=0;
switch($opc)
{
    case 0:
        $reg=Consulta($db,$nm_ciclo);
        break;
    case 1:
        Nuevo($db,$nm_ciclo);
        break;
    case 2:
         Asciende($db,$id,$consec);
         break;
    case 3:
         Desciende($db,$id,$consec);
         break;
     case 4:
        Elimina($db,$id);
        break;
     case 5:
        Actualiza($db,$id,$nm_ciclo);
        break;
}
echo $reg;
die();

function Consulta($db,$texto)
{
    $reg=0;
    $texto=Quita_Acentos($texto);
    $sql="SELECT nombre FROM crm_ciclo_venta WHERE nombre='".$texto."';";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $reg=1;
    }
    return $reg;
}

function Quita_Acentos($texto)
{
    $texto = htmlentities($texto ,ENT_QUOTES,'UTF-8');
    $texto=str_replace('&Aacute;','Á',$texto);
    $texto=str_replace('&Eacute;','É',$texto);
    $texto=str_replace('&Iacute;','Í',$texto);
    $texto=str_replace('&Oacute;','Ó',$texto);
    $texto=str_replace('&Uacute;','Ú',$texto);
    $texto=str_replace('&Ntilde;','Ñ',$texto);
    return $texto;
}
function Nuevo($db,$texto)
{    
    $texto=Quita_Acentos($texto);
    $sql="SELECT max(orden) as maximo FROM crm_ciclo_venta;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        list($maximo) = $db->sql_fetchrow($res);
    }
    $maximo++;
    $db->sql_query("INSERT INTO crm_ciclo_venta (orden,nombre) VALUES ('".$maximo."','".$texto."');");
}
function Actualiza($db,$id,$name)
{
    $name=Quita_Acentos($name);
    $update="UPDATE crm_ciclo_venta SET nombre='".$name."' WHERE id=".$id.";";
    $db->sql_query($update) or die("Error en la actualiación:  ".$update);
}

function Asciende($db,$c_id,$consec)
{
    if( ($consec > 1) && ($consec <11) )
    {
        $sql="SELECT id,orden,nombre FROM crm_ciclo_venta ORDER BY orden;";
        $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res) > 0)
        {
            $id_anterior=0;
            $consec_anterior=0;
            while(list($id,$orden,$nombre) = $db->sql_fetchrow($res))
            {
                if($id == $c_id)
                {
                    $orden_act=$consec - 1;
                    $orden_sig=$consec_anterior + 1;
                    $db->sql_query("UPDATE crm_ciclo_venta SET orden=".$orden_act." WHERE id=".$id.";") or die("error");
                    $db->sql_query("UPDATE crm_ciclo_venta SET orden=".$orden_sig." WHERE id=".$id_anterior.";") or die("error2");
                    break;
                }
                $id_anterior=$id;
                $consec_anterior=$orden;
            }
        }
    }
}
function Desciende($db,$c_id,$consec)
{
    if( ($consec > 0) && ($consec <10) )
    {
        $sql="SELECT id,orden,nombre FROM crm_ciclo_venta ORDER BY orden DESC;";
        $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
        if($db->sql_numrows($res) > 0)
        {
            $id_anterior=0;
            $consec_anterior=0;
            while(list($id,$orden,$nombre) = $db->sql_fetchrow($res))
            {
                if($id == $c_id)
                {
                    $orden_act=$consec + 1;
                    $orden_sig=$consec_anterior - 1;
                    $db->sql_query("UPDATE crm_ciclo_venta SET orden=".$orden_act." WHERE id=".$id.";") or die("error");
                    $db->sql_query("UPDATE crm_ciclo_venta SET orden=".$orden_sig." WHERE id=".$id_anterior.";") or die("error2");
                    break;
                }
                $id_anterior=$id;
                $consec_anterior=$orden;
            }
        }
    }
}
function Elimina($db,$id)
{
    $db->sql_query("DELETE FROM crm_ciclo_venta WHERE id=".$id.";");
}
?>
