<?php
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $unidad_id,$seleccionados,$opc,$nm_unidad,$link_unidad,$categoria_id,$nm_categoria,
       $categoria_id,$nm_subcategoria,$subcategoria_id;
switch($opc)
{
    case 1:
        Inserta_Categorias($db,$unidad_id,$seleccionados);
        break;
    case 2:
        Elimina_Producto($db,$unidad_id);
        break;
    case 3:
        Actualiza_Producto($db,$unidad_id,$nm_unidad,$link_unidad);
        break;
    case 4:
        Actualiza_Categoria($db,$categoria_id,$nm_categoria);
        break;
    case 5:
        Elimina_Categoria($db,$categoria_id);
        break;
    case 6:
        Inserta_SubCategorias($db,$unidad_id,$categoria_id,$seleccionados);
        break;
    case 7:
        Elimina_SubCategoria($db,$subcategoria_id);
        break;
    case 8:
        Actualiza_Subcategoria($db,$subcategoria_id,$nm_subcategoria);
        break;

}
die();

function Actualiza_Subcategoria($db,$subcategoria_id,$nm_subcategoria)
{
    $upd="UPDATE crm_transmisiones SET nombre='".$nm_subcategoria."' WHERE transmision_id=".$subcategoria_id.";";
    $res=$db->sql_query($upd) or die("Error en la actualizacion:  ".$upd);
}

function Actualiza_Categoria($db,$categoria_id,$nm_categoria)
{
    $upd="UPDATE crm_versiones SET nombre='".$nm_categoria."' WHERE version_id=".$categoria_id.";";
    $res=$db->sql_query($upd) or die("Error en la actualizacion:  ".$upd);
}
function Actualiza_Producto($db,$unidad_id,$nm_unidad,$link_unidad)
{
    $upd="UPDATE crm_unidades SET nombre='".$nm_unidad."', url='".$link_unidad."' WHERE unidad_id=".$unidad_id.";";
    $res=$db->sql_query($upd) or die("Error en la actualizacion:  ".$upd);
}

function Elimina_Categoria($db,$categoria_id)
{
    $db->sql_query("DELETE FROM crm_vehiculo_versiones WHERE version_id=".$categoria_id.";");
    $db->sql_query("DELETE FROM crm_versiones WHERE version_id=".$categoria_id.";");
}
function Elimina_Producto($db,$unidad_id)
{
    $del="UPDATE crm_unidades SET active=0 WHERE unidad_id=".$unidad_id.";";
    $res=$db->sql_query($del) or die("Error en el del:  ".$del);
}
function Elimina_SubCategoria($db,$subcategoria_id)
{
    $db->sql_query("DELETE FROM crm_version_transmisiones WHERE transmision_id=".$subcategoria_id.";");
    $db->sql_query("DELETE FROM crm_transmisiones WHERE transmision_id=".$subcategoria_id.";");

}
function Inserta_Categorias($db,$unidad_id,$seleccionados)
{
    $seleccionados=substr($seleccionados,0,(strlen($seleccionados) - 1));
    $array_versiones=explode('|',$seleccionados);
    if(count($array_versiones) > 0)
    {
        $del="delete from crm_vehiculo_versiones WHERE vehiculo_id=".$unidad_id.";";
        $res=$db->sql_query($del) or die ("Error en el delete:  ".$del);
        foreach($array_versiones as $categoria_id)
        {
            $ins="INSERT INTO crm_vehiculo_versiones (vehiculo_id,version_id) VALUES ('".$unidad_id."','".$categoria_id."');";
            $res=$db->sql_query($ins) or die("Error en el inser:  ".$ins);
        }
        echo "Guardado";
    }
}
function Inserta_SubCategorias($db,$unidad_id,$categoria_id,$seleccionados)
{
    $seleccionados=substr($seleccionados,0,(strlen($seleccionados) - 1));
    $array_versiones=explode('|',$seleccionados);
    if(count($array_versiones) > 0)
    {
        $del="delete from crm_version_transmisiones WHERE version_id=".$categoria_id.";";
        $res=$db->sql_query($del) or die ("Error en el delete:  ".$del);
        foreach($array_versiones as $subcategoria_id)
        {
            $ins="INSERT INTO crm_version_transmisiones (version_id,transmision_id) VALUES ('".$categoria_id."','".$subcategoria_id."');";
            $res=$db->sql_query($ins) or die("Error en el inser:  ".$ins);
        }
        echo "Guardado";
    }
}
?>
