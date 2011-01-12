<?php
/*********** FUNCIONES AUXILIARES *********/

/**
 * Funcion que regresa un array con el id y nombre de las concesionarias
 * @param <type> $db conexion a la base de datos
 * @return <type> $array, array de consecionarias
 */
function Regresa_Concesionarias($db)
{
    $array=array();
    $sql="SELECT gid,name FROM groups ORDER BY gid;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $array[$id]=$nombre;
        }
    }
    return $array;
}

/**
 * Funcion que regresa un array con el id y nombre de las fases
 * @param <type> $db conexion a la base de datos
 * @return <type> $array, array de fases
 */
function Regresa_Fases($db)
{
    $array=array();
    $sql="SELECT fase_id,fase_nombre FROM mfll_fases ORDER BY fase_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $array[$id]=$nombre;
        }
    }
    return $array;
}

function Regresa_Unidades($db)
{
    $array=array();
    $sql="SELECT unidad_id,nombre FROM crm_unidades ORDER BY nombre;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $array[$id]=$nombre;
        }
    }
    return $array;
}
function Regresa_Vendedores($db)
{
    $array=array();
    $sql="SELECT uid,name FROM users ORDER BY name;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $array[$id]=$nombre;
        }
    }
    return $array;
}

function Regresa_Eventos($db)
{
    $array=array();
    $sql="SELECT evento_id,evento_nombre FROM mfll_eventos ORDER BY evento_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $array[$id]=$nombre;
        }
    }
    return $array;
}

function Regresa_Tipos_Pagos($db)
{
    $array=array();
    $sql="SELECT tipo_pago_id,tipo_pago_nombre FROM mfll_tipo_pagos ORDER BY tipo_pago_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $array[$id]=$nombre;
        }
    }
    return $array;
}
function Genera_Combo_Eventos($db,$evento_id)
{
    $buffer='';
    $sql="SELECT evento_id,evento_nombre FROM mfll_eventos ORDER BY evento_nombre;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $buffer.="<select name='evento_id' id='evento_id' style='width:180px; border:1px solid #cdcdcd;color:#000000;'>
                  <option value='0'></option>";
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $evento_id)   $tmp=' SELECTED ';
            $buffer.="<option value='".$id."' ".$tmp." >".$nombre."</option>";
        }
        $buffer.="</select>";
    }
    return $buffer;
}
function Genera_Combo_Fases($db,$id_fase)
{
    $buffer='';
    $sql="SELECT fase_id,fase_nombre FROM mfll_fases ORDER BY fase_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $buffer.="<select name='fase_id' id='fase_id' style='width:180px; border:1px solid #cdcdcd;color:#000000;'>
                  <option value='0'></option>";
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $id_fase)   $tmp=' SELECTED ';
            $buffer.="<option value='".$id."' ".$tmp." >".$nombre."</option>";
        }
        $buffer.="</select>";
    }
    return $buffer;
}
function Genera_Combo_Unidades($db,$id_unidad)
{
    $buffer='';
    $sql="SELECT unidad_id,nombre FROM crm_unidades ORDER BY nombre;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $buffer.="<select name='unidad_id' id='unidad_id' style='width:180px; border:1px solid #cdcdcd;color:#000000;'>
                  <option value='0'></option>";
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $id_unidad)   $tmp=' SELECTED ';
            $buffer.="<option value='".$id."' ".$tmp." >".$nombre."</option>";
        }
        $buffer.="</select>";
    }
    return $buffer;
}
function Genera_Combo_Tipo_Pago($db,$id_tipo_pago)
{
    $buffer='';
    /*$sql="SELECT tipo_pago_id,tipo_pago_nombre FROM mfll_tipo_pagos ORDER BY tipo_pago_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {*/
        $buffer.="<select name='tipo_pago_id' id='tipo_pago_id' style='width:180px; border:1px solid #cdcdcd;color:#000000;'>
                  <option value='0'></option>";
       /* while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $id_tipo_pago)   $tmp=' SELECTED ';
            $buffer.="<option value='".$id."' ".$tmp." >".$nombre."</option>";
        }*/
        $buffer.="</select>";
    //}
    return $buffer;
}
function Genera_combo_Concesionarias($db,$gid)
{
    $buffer='';
    $sql="SELECT gid,name FROM groups WHERE gid> 3 ORDER BY gid;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $buffer.="<select name='tmp_gid' id='tmp_gid' style='width:320px; border:1px solid #cdcdcd;color:#000000;'>
                  <option value='0'></option>";
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $tmp='';
            if($id == $gid)   $tmp=' SELECTED ';
            $buffer.="<option value='".$id."' ".$tmp." >".$id."  -  ".$nombre."</option>";
        }
        $buffer.="</select>";
    }
    return $buffer;
}
function Genera_combo_Vendedores($db,$uid)
{
    $buffer.="<select name='tmp_uid' id='tmp_uid' style='width:260px; border:1px solid #cdcdcd;color:#000000;'>
              <option value='0'></option></select>";
    return $buffer;
}

?>