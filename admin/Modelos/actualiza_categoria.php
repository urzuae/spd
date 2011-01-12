<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$categoria_id,$unidad_id,$_module,$_op,$submit,$_site_title;
$_site_title = "Actualizaci&oacute;n de categoria";


$name='';
$sql="SELECT nombre FROM crm_versiones WHERE version_id=".$categoria_id.";";
$res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
if($db->sql_numrows($res) > 0)
{
    list($name)= $db->sql_fetchrow($res);
}

?>