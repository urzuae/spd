<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$gid;
$buffer='';
if($gid > 0)
{
    $sql="SELECT uid,name FROM users WHERE super=8 AND gid='".$gid."' ORDER BY name;";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $buffer.="<option value='0'></option>";
        while(list($id,$nombre) = $db->sql_fetchrow($res))
        {
            $buffer.="<option value='".$id."' ".$tmp." >".$id."  -  ".$nombre."</option>";
        }
    }
}
echo $buffer;
die();
?>