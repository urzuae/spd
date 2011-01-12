<?php
header("Cache-Control: no-cache, must-revalidate");
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$fuente_id,$guardar,$busca,$bus_gid,$bus_nom,$seleccionados,$msg_ciclo;

$filtro='';
$actualizadas='';

$nom_fuente=Regresa_Fuente($db,$fuente_id);
if($busca)
{
    if(($bus_gid != '') && ($bus_nom != ''))
        $filtro=" AND ( gid='".$bus_gid."' OR  name like '%".$bus_nom."%') ";
    if(($bus_gid != '') && ($bus_nom == ''))
        $filtro=" AND  gid='".$bus_gid."' ";
    if(($bus_gid == '') && ($bus_nom != ''))
        $filtro=" AND name like '%".$bus_nom."%' ";
}
if($guardar)
{
    $array_groups=array();
    $array_groups=explode('|',$seleccionados);
    $elemento = array_pop($array_groups);
    $actualizaciones=0;
    $sql_delete_fuentes="DELETE FROM crm_groups_fuentes WHERE fuente_id='".$fuente_id."';";
    if($db->sql_query($sql_delete_fuentes))
    {
        if(count($array_groups) > 0)
        {
            foreach($array_groups as $valor)
            {
                if($db->sql_query("INSERT INTO crm_groups_fuentes (gid,fuente_id) VALUES ('".$valor."','".$fuente_id."');"))
                    $actualizaciones++;
            }
        }
    }
    $actualizadas="<font color='#3e4f88'>".$actualizaciones." Distribuidores <b>NO VISIBLES</b> para la fuente ".$nom_fuente."</font>";
}




$array_concesionarias_no_visibles=Regresa_Concesionarias_No_Visibles($db,$fuente_id);
$buffer=Regresa_Concesionarias($db,$array_concesionarias_no_visibles,$filtro);



$tabla="<br><br><table width='80%' align='center' border='0'>
        <tr>
            <td width='22%' align='left' valign='top'>Buscar Distribuidor por ID:</td>
            <td width='58%' align='left' valign='top'><input type='text' name='bus_gid' id='bus_gid' value='' size='8' maxlength='4'></td>
            <td width='20%'align='left' valign='middle' rowspan='2'><input type='submit' name='busca' id='busca' value='Buscar'></td>
        </tr>
        <tr>
            <td align='left' valign='top'>Buscar Distribuidor por nombre:</td>
            <td align='left' valign='top'><input type='text' name='bus_nom' id='bus_nom' value='' size='65'></td>
        </tr>
        <tr height='30'>
            <td colspan='3' align='left' valign='top'><input type='hidden' name='seleccionados' id='seleccionados' value=''></td>
        </tr>
        <tr>
            <td colspan='3' align='center' valign='top'><font color='#800000' size='2'><b>Marque</b> las distribuidoras en las que no desea que se visualize la fuente: </font><b>".$nom_fuente."</b><br></td>
        </tr>
        <tr height='30'>
            <td align='center' colspan='3' valign='top'>
            <input type='button' name='marcar' id='marcar' value='Marcar Todos'>&nbsp;&nbsp;&nbsp;
            <input type='button' name='desmarcar' id='desmarcar' value='Desmarcar Todos'>&nbsp;&nbsp;&nbsp;
            <input type='submit' name='guardar' id='guardar' value='Guardar Movimientos' >&nbsp;&nbsp;&nbsp;
            <input type='button' name='btnr' value='Regresar a fuentes' onClick=\"location='index.php?_module=Catalogos&_op=mostrarArbol'\";>
            </td>
        </tr>
        <tr>
            <td colspan='3' align='center' valign='top'>".$actualizadas."</td>
        </tr>
        <tr>
            <td colspan='3' align='left' valign='top'>".$buffer."</td>
        </tr>
        <tr>
            <td colspan='3' align='center' valign='top'>
            </td>
        </tr>
        </table>";
/***************FUNCTIONES AUXILIARES ***************/

function Regresa_Concesionarias_No_Visibles($db,$fuente_id)
{
    $array=array();
    $sql="SELECT gid FROM crm_groups_fuentes WHERE fuente_id='".$fuente_id."';";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)>0)
    {
        while(list($gid)=$db->sql_fetchrow($res))
        {
            $array[]=$gid;
        }
    }
    return $array;
}
function Regresa_Concesionarias($db,$array,$filtro)
{
    $buffer='No Existen distribuidoras';
    $sql="SELECT gid,name FROM groups WHERE active=1 ".$filtro." ORDER BY gid;";
    $res=$db->sql_query($sql);
    $num=$db->sql_numrows($res);
    if($num >0)
    {
        $contador=0;
        $buffer="<table align='center' class='tablesorter' width='60%'>
                 <thead><tr><th>&nbsp;</th><th>Id</th><th>Nombre</th></tr></thead><tbody>";
        while(list($gid,$name)=$db->sql_fetchrow($res))
        {
            $tmp=' ';
            $val=0;
            $jsname="g".$gid;
            if(in_array($gid,$array))
            {
                $tmp=' checked ';
                $val=1;
            }

            $buffer.="<tr class=\"row".($class_row++%2?"2":"1")."\" style=\"cursor:pointer;\">";
            $buffer.="<td align='left'><input type='checkbox' name='".$jsname."'  id='".$jsname."' value='".$gid."' ".$tmp."></td><td>".$gid."</td><td>".$name."</td></tr>";
            $contador++;
        }
        $buffer.="</tbody><thead><tr><td colspan='3'>Total de Distribuidores:  ".$num."</td></tr></thead></table>";
    }
    return $buffer;

}
function Regresa_Fuente($db,$fuente_id)
{
    $nm='';
    $sql="SELECT nombre FROM crm_fuentes WHERE fuente_id='".$fuente_id."';";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)>0)
    {
        $nm=$db->sql_fetchfield(0,0,$res);
    }
    return $nm;
}
?>
