<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$unidad_id,$_module,$_op,$submit,$_site_title;
$_site_title = "Actualizaci&oacute;n de producto";

$name='';
$padre_id=$unidad_id;
$sql="SELECT nombre,lower(url) FROM crm_unidades WHERE unidad_id=".$unidad_id.";";
$res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
if($db->sql_numrows($res) > 0)
{
    list($name,$url)= $db->sql_fetchrow($res);
}

?>