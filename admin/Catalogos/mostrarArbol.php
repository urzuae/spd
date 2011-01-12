<?php
/* 
 * Muestra el arbol de concesionarias 
 */
include_once 'Compuesto.php';
global $db, $padre_id,$msg_ciclo,$_site_title;
$_site_title = "Crear Origenes";

if($_REQUEST["guardar"])
{
    $nombrePadre = $_REQUEST["nombrePadre"];
    $nombreHijo = $_REQUEST["nombreHijo"];
    $sql = "update crm_fuentes set nombre='$nombrePadre' where fuente_id='$padre_id'";
    $db->sql_query($sql) or die("Erro al actualizar el nombre de la fuente padre ->".$sql);
    if(strlen($nombreHijo) > 0 && $nombreHijo !="undefined")
    {
        $sql="SELECT max(fuente_id) as maximo FROM crm_fuentes;";
        $res=$db->sql_query($sql);
        if($db->sql_numrows($res)> 0)
        {
            $maximo=$db->sql_fetchfield(0,0,$res);
            $maximo=$maximo + 1;
            $now = date("Y-m-d H:i:s");
            $sql = "insert into crm_fuentes values(".$maximo.",'$nombreHijo', '$now','1')";
            $db->sql_query($sql) or die("Error al insertar nodo hijo ->".$sql);
                        $sql= "insert into crm_fuentes_arbol values('$padre_id',".$maximo.")";
            $db->sql_query($sql) or  die("Error al insertar la relacion padre-hijo->".$sql);
        }
    }
    header("location: index.php?_module=Catalogos&_op=mostrarArbol");
}
if($_REQUEST["del"])
{
    $sql= "delete from crm_fuentes where fuente_id='$padre_id'";
    $db->sql_query($sql) or die("Error al eliminar la fuente ->".$sql);
    $sql = "delete from crm_fuentes_arbol where hijo_id='$padre_id'";
    $db->sql_query($sql) or die("Erro al eliminar los hijos de la fuente->".$sql);
    header("location: index.php?_module=Catalogos&_op=mostrarArbol");
}
if($_REQUEST["upd"])
{
    $sql= "update crm_fuentes set active = 0 where fuente_id='$padre_id'";
    $db->sql_query($sql) or die("Error al bloquear la fuente ->".$sql);
    header("location: index.php?_module=Catalogos&_op=mostrarArbol");
}

if($_REQUEST["upddes"])
{
    $sql= "update crm_fuentes set active = 1 where fuente_id='$padre_id'";
    $db->sql_query($sql) or die("Error al desbloquear la fuente ->".$sql);
    header("location: index.php?_module=Catalogos&_op=mostrarArbol");
}

$treeString = "";
$root = new Compuesto(1,"Catalogo de Origenes",1);
createTreeNode($db, 1, $root);
$root->mostrar(1,$treeString);
//Crea el arbol de fuentes a mostrar
function createTreeNode($db, $idPadre, Compuesto $nodoPadre)
{
    $sql = "select distinct(tree.hijo_id), fuentes.nombre,fuentes.active from crm_fuentes_arbol as tree,
            crm_fuentes as fuentes  where tree.hijo_id=fuentes.fuente_id and tree.padre_id='$idPadre';";
    $result = $db->sql_query($sql);
    if($db->sql_numrows($result) == 0)
        return null;
    else
    {        
        while(list($idFuente, $nombreFuente,$active) = $db->sql_fetchrow($result))
        {
            $nodo = new Compuesto($idFuente, $nombreFuente,$active);
            $nodoPadre->agregar($nodo);            
            createTreeNode($db, $idFuente, $nodo);
        }        
    }
}
?>
