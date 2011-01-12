<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $modelo_id, $version_id;
$buffer="";
if($modelo_id>0)
{
    $sql="SELECT a.version_id,b.nombre FROM  crm_vehiculo_versiones a, crm_versiones b WHERE a.vehiculo_id=".$modelo_id." AND a.version_id=b.version_id ORDER BY b.nombre";
    $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {
         $buffer.="<option value='0'>Selecciona</option>";
         while($rs = $db->sql_fetchrow($res))
         {
            $tmp="";
            if($rs["version_id"] == $version_id)
                $tmp=" SELECTED ";
            $buffer.='<option value="'.$rs["version_id"].'" '.$tmp.'>'.htmlentities($rs["nombre"]).'</option>';
         }
    }
}
echo $buffer;
die();
?>