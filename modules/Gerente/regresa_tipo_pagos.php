<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$fase_id;
$buffer='';
if($fase_id == 3)
{
    $sql="SELECT tipo_pago_id,tipo_pago_nombre FROM mfll_tipo_pagos ORDER BY tipo_pago_id;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $buffer.="<option value='0'></option>";
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $buffer.="<option value='".$id."' ".$tmp." >".$nombre."</option>";
        }
    }
}
echo $buffer;
die();
?>