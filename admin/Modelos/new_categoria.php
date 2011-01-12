<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$categoria,$unidad_id,$_module,$_op,$submit,$msg_ciclo,$_site_title;
$_site_title = "Nuevo categoria";
if ($submit)
{
    $n =$db->sql_numrows($db->sql_query("SELECT nombre FROM crm_versiones WHERE nombre='".$categoria."';"));
    if ($n != 0)
    {
        $error = "<b>No se pudo crear la categoria, por que ya existe otra con el nombre \"$categoria\"</b><br>\n";
    }
    else
    {
        $ins="INSERT INTO crm_versiones (nombre) VALUES ('".$categoria."');";
        $res=$db->sql_query($ins) or die("Error en el insert de categorias:  ".$sql);
    }
    header("location: index.php?_module=Modelos&_op=edit&unidad_id=$unidad_id");
}
?>