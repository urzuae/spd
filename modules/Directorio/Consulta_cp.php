<?php
if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$codigo;
$codigo=$_GET['cp'];
$sql = "SELECT d_codigo,d_asenta FROM cps WHERE d_codigo='".$codigo."'";
$res = $db->sql_query($sql);
$listColonias =array();
if($db->sql_numrows($res)>0)
{
    while(list($d_codigo,$d_asenta) = $db->sql_fetchrow($res))
    {
        $nombre=str_replace('�','&aacute;',$d_asenta);
		$nombre=str_replace('�','&eacute;',$nombre);
		$nombre=str_replace('�','&iacute;',$nombre);
		$nombre=str_replace('�','&oacute;',$nombre);
		$nombre=str_replace('�','&uacute;',$nombre);
		$nombre=str_replace('�','&ntilde;',$nombre);
        $select.='<option value="'.$nombre.'">'.$nombre.'</option>';
    }
}
echo $select;
die();
?>
