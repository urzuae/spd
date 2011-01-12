<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $modelo_id, $version_id,$transmision_id;
$buffer="";
if ($version_id > 0)
{
    $sql="SELECT a.transmision_id,b.nombre FROM  crm_version_transmisiones a, crm_transmisiones b WHERE a.version_id=".$version_id." AND a.transmision_id=b.transmision_id ORDER BY b.nombre";
    $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
    if($db->sql_numrows($res) > 0)
    {
         $buffer.="<option value='0'>Selecciona</option>";
         while($rs = $db->sql_fetchrow($res))
         {
            $tmp="";
            if($rs["transmision_id"] == $transmision_id)
                $tmp=" SELECTED ";
            $buffer.='<option value="'.$rs["transmision_id"].'" '.$tmp.'>'.htmlentities($rs["nombre"]).'</option>';
         }
    }
}
echo $buffer;
die();
?>