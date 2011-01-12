<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$producto,$unidad_id,$_module,$_op,$submit,$msg_ciclo,$_site_title;
$_site_title = "Actualizaci&oacute;n de origen";

$fuente='';
$padre_id=$unidad_id;
$sql="SELECT nombre FROM crm_fuentes WHERE fuente_id=".$unidad_id.";";
$res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
if($db->sql_numrows($res) > 0)
{
    list($fuente)= $db->sql_fetchrow($res);
}

?>