<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
 global $db,$_licenses,$_includesdir,
        $unidad_id,$categoria_id,
        $_module,$_op,$submit,$_site_title,$subcategoria;
$_site_title = "Nuevo Subcategoria";
if ($submit)
{
    $n =$db->sql_numrows($db->sql_query("SELECT nombre FROM crm_transmisiones WHERE nombre='".$subcategoria."';"));
    if ($n != 0)
    {
        $error = "<b>No se pudo crear la Subcategoria, por que ya existe otra con el nombre \"$subcategoria\"</b><br>\n";
    }
    else
    {
        $ins="INSERT INTO crm_transmisiones (nombre) VALUES ('".$subcategoria."');";
        $res=$db->sql_query($ins) or die("Error en el insert de Subcategorias:  ".$sql);
    }
    header("location: index.php?_module=Modelos&_op=editt&unidad_id=$unidad_id&categoria_id=$categoria_id");
}
?>