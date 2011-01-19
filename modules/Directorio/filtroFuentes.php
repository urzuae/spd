<?php

if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $idcombo,$gid;
$idcombo = $_GET["id"];
$gid=$_GET['gid'];
$array=array();
$filtro_gid='';
if($idcombo != 0)
{
    if($gid!=0)
    {
        $res_no_visibles=$db->sql_query("SELECT fuente_id FROM crm_groups_fuentes WHERE gid='".$gid."';");
        if($db->sql_numrows($res_no_visibles) > 0)
        {
            while(list($fuente_id)=$db->sql_fetchrow($res_no_visibles))
            {
                $array[]=$fuente_id;
            }
            $lista_gid_no_visibles=implode(',',$array);
        }
    }
    if($lista_gid_no_visibles!='')
        $filtro_gid=" AND b.fuente_id NOT IN (".$lista_gid_no_visibles.") ";

    $res =$db->sql_query("SELECT a.padre_id,b.nombre,b.fuente_id FROM crm_fuentes_arbol a,crm_fuentes b WHERE a.padre_id=".$idcombo." and a.hijo_id=b.fuente_id and b.active=1 ".$filtro_gid." ORDER BY b.nombre ASC");
    if($db->sql_numrows($res) > 0)
    {
        echo"<option value='0'>Selecciona</option>";
        while($rs = $db->sql_fetchrow($res))
            echo '<option value="'.$rs["fuente_id"].'">'.htmlentities($rs["nombre"]).'</option>';
    }
}
die();
?>
