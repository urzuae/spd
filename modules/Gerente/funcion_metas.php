<?php

function Regresa_Combo_Anos($ano_id)
{
    if($ano_id=='') $ano_id=date('Y');
    $array_anos= array('2010','2011','2012','2013','2014','2015','2016','2017','2018','2019','2020');
    $combo="<SELECT name='ano_id' id='ano_id' style='width:150px;'>";
    foreach($array_anos as $ano)
    {
        $tmp='';
        if($ano == $ano_id) $tmp=' SELECTED ';
        $combo.="<option value='".$ano."' ".$tmp.">".$ano."</option>";
    }
    $combo.="</select>";
    return $combo;
}
function Regresa_Combo_Meses($array_meses)
{
    if(count($array_meses) == 0) $array_meses=array(date('m'));

    $array_meses= array('01' => 'Enero','02' => 'Febrero','03' => 'Marzo','04' => 'Abril','05' => 'Mayo','06' => 'Junio',
                       '07' => 'Julio','08' => 'Agosto','09' => 'Septiembre','10' => 'Octubre','11' => 'Noviembre','12' => 'Diciembre');
    $combo="<SELECT multiple name='meses_id[]' id='meses_id' style='width:150px;'>";
    foreach($array_meses as $id => $mes)
    {
        $tmp='';
        if(in_array($id,$array_meses))  $tmp=' SELECTED ';
        $combo.="<option value='".$id."' ".$tmp.">".$mes."</option>";
    }
    $combo.="</select>";
    return $combo;
}
function Regresa_Array_Meses()
{
    $array_meses= array('01' => 'Enero','02' => 'Febrero','03' => 'Marzo','04' => 'Abril','05' => 'Mayo','06' => 'Junio',
                       '07' => 'Julio','08' => 'Agosto','09' => 'Septiembre','10' => 'Octubre','11' => 'Noviembre','12' => 'Diciembre');
    return $array_meses;
}
function Regresa_Combo_Dias()
{
    $array_meses_dias= array('01' => '31','02' => '28','03' => '31','04' => '30','05' => '31','06' => '30',
                        '07' => '31','08' => '31','09' => '30','10' => '31','11' => '30','12' => '31');
    return $array_meses_dias;
}




function Regresa_Combo_Vendedores($db,$gid,$id_user)
{
    $combo='';
    $sql="SELECT uid,name FROM users WHERE active=1 AND super=8 AND gid='".$gid."' ORDER BY name;";
    $res=$db->sql_query($sql) or die("Error en la consulta  ".$sql);
    if($db->sql_numrows($res)>0)
    {
        $combo="<SELECT multiple name='id_user[]' id='id_user' style='width:160px;border:1px solid #cdcdcd;'>";
        while(list($id,$name) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $id_user)
               $tmp=" SELECTED ";
            $combo.="<option value='".$id."' ".$tmp.">".$name."</option>";
        }
        $combo.="</SELECT>";
    }
    return $combo;
}

function Regresa_Uids_Vendedores($db,$gid,$id_user)
{
    $array_uid=array();
    $filtro='';
    if($id_user > 0)
        $filtro= "AND uid=".$id_user." ";

    $sql="SELECT uid FROM users WHERE active=1 AND super=8 AND gid='".$gid."' ".$filtro.";";
    $res=$db->sql_query($sql) or die("Error en la consulta de vendedores:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id) = $db->sql_fetchrow($res))
        {
            $array_uid[]=$id;
        }
    }
    return $array_uid;
}

function Regresa_Vendedores($db,$gid,$id_user)
{
    $array_uid=array();
    $filtro='';
    if($id_user > 0)
        $filtro= "AND uid=".$id_user." ";

    $sql="SELECT uid,name FROM users WHERE super=8 AND gid='".$gid."' ".$filtro.";";
    $res=$db->sql_query($sql) or die("Error en la consulta de vendedores:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$name) = $db->sql_fetchrow($res))
        {
            $array_uid[$id]=$name;
        }
    }
    return $array_uid;
}

?>