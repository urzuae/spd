<?
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_licenses,$_includesdir,$producto,$url,$_module,$_op,$submit,$msg_ciclo,$_site_title;
$_site_title = "Nuevo producto";
if ($submit)
{
    $n =$db->sql_numrows($db->sql_query("SELECT nombre FROM crm_unidades WHERE nombre='".$producto."';"));
    if ($n != 0)
    {
        $error = "<b>No se pudo crear el producto, por que ya existe otro con el nombre \"$producto\"</b><br>\n";
    }
    else
    {
        $url=strtolower($url);
        if( ($url=='') ||  ($url=='http://'))
            $url='#';
        $ins="INSERT INTO crm_unidades (url,nombre,active) VALUES ('".$url."','".$producto."','1');";
        $res=$db->sql_query($ins) or die("Error en el insert de productos:  ".$sql);
    }
    header("location: index.php?_module=Modelos");
}
?>