<?php
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $gid,$fuentes;
$fuentes=str_replace('fuenteid_','',$fuentes);
$array_fuentes=explode('|',$fuentes);
$elemento = array_pop($array_fuentes);

$actualizaciones=0;
$filtro_hijos='';
$total_hijos=0;
$total_hijo_no_visibles=0;
$array_fuentes_padres=array();

$sql_delete_fuentes="DELETE FROM crm_groups_fuentes WHERE gid=".$gid.";";
if($db->sql_query($sql_delete_fuentes))
{
    foreach($array_fuentes as $origen_id)
    {
        $id_origen=$origen_id + 0;
        $res=$db->sql_query("SELECT * FROM crm_fuentes_arbol WHERE padre_id=".$origen_id.";");
        if($db->sql_numrows($res)== 0)
        {
            if(Inserta_Tabla($db,$gid,$origen_id))
                $actualizaciones++;
        }
        else   // si es padre la fuente, necesitamos checar que todos sus hijos, sean no visibles
        {
            if($origen_id < 0)
                $array_fuentes_padres[]=$origen_id;
        }
    }
    if(count($array_fuentes_padres)>0)
    {
        foreach($array_fuentes_padres as $id_origen)
        {
            $array_hijos=Regresa_hijos($db,$id_origen);
            $total_hijos=count($array_hijos);
            if( $total_hijos > 0)
                $total_hijo_no_visibles=Regresa_Total_Hijos_No_Visible($db,$gid,$id_origen,$array_hijos);

            if($total_hijo_no_visibles >= $total_hijos) // con esta comparacion garantizo que todos los hijos son no visbles
            {
                if(Inserta_Tabla($db,$gid,$id_origen))
                    $actualizaciones++;
            }
        }
    } 
}
echo "Numero de fuentes no visibles: ".$actualizaciones." ";
die();


function Inserta_Tabla($db,$gid,$origen_id)
{
    $reg=false;
    $sql_ins="INSERT INTO crm_groups_fuentes (gid,fuente_id) VALUES ('".$gid."','".$origen_id."');";
    if($db->sql_query($sql_ins))
    {
        $reg=true;
    }
    return $reg;
}

function Regresa_Total_Hijos_No_Visible($db,$gid,$id_origen,$array_hijos)
{
    $total=0;
    foreach($array_hijos as $id_origen)
    {
        $res_c=$db->sql_query("SELECT fuente_id FROM crm_groups_fuentes WHERE fuente_id=".$id_origen.";");
        if($db->sql_numrows($res_c) > 0)
        {
          $total++;
        }
    }
    return $total;
}
function Regresa_hijos($db,$origen_id)
{
    $array_tmp=array();
    $res=$db->sql_query("SELECT a.hijo_id FROM crm_fuentes_arbol as a, crm_fuentes b WHERE a.padre_id='".$origen_id."' AND a.hijo_id=b.fuente_id AND b.active=1 order by hijo_id;");
    if($db->sql_numrows($res)>0)
    {
        while(list($hijo_id) = $db->sql_fetchrow($res))
        {
            $array_tmp[]=$hijo_id;
        }
    }
    return $array_tmp;
}
?>