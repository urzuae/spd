<?php
/*
 * Muestra el arbol de concesionarias
 */
include_once 'Compuesto.php';
global $db, $padre_id,$gid;
$tit="No se tiene registrado el Id en las distribuidoras";

$nombreConcesionaria=Regresa_Nombre_Concesionaria($db,$gid);
if($nombreConcesionaria != '')
{
    $tit="&Aacute;rbol de Origenes";
    $nombreConcesionaria.="<br><br><font size='2' color='#333333'>Favor de <b><font color='#800000'>Marcar</font></b> los origenes que la concesionaria no podrá visualizar</font>";
    $treeString = '';
    $root = new Compuesto(0,"Fuentes VW",1,1);
    createTreeNode($db, 0, $root,$gid);
    $root->mostrar(1,$treeString);
}
$botones='<input type="button" name="boton_checks" id="boton_checks" value="Guardar Cambios">
          &nbsp;&nbsp;
          <input type="button" name="boton_reg" id="boton_reg" value="Regresar" onClick=location="index.php?_module=Concesionarias">
                <br><div id="fuentes"></div>';

//Crea el arbol de fuentes a mostrar
function createTreeNode($db, $idPadre, Compuesto $nodoPadre,$gid)
{
    $sql = "select distinct(tree.hijo_id), fuentes.nombre,fuentes.active from crm_fuentes_arbol as tree,
            crm_fuentes as fuentes  where tree.hijo_id=fuentes.fuente_id and tree.padre_id='$idPadre'";
    $result = $db->sql_query($sql);
    if($db->sql_numrows($result) == 0)
        return null;
    else
    {
        while(list($idFuente, $nombreFuente,$active) = $db->sql_fetchrow($result))
        {
            $visible=1;
            $sql="SELECT * FROM crm_groups_fuentes WHERE gid=".$gid." AND fuente_id='".$idFuente."';";
            $res_sql=$db->sql_query($sql);
            if($db->sql_numrows($res_sql)>0)
            {
                $visible=0;
            }
            $nodo = new Compuesto($idFuente, $nombreFuente,$active,$visible);
            $nodoPadre->agregar($nodo);
            createTreeNode($db, $idFuente, $nodo,$gid);
        }
    }
}

// Metodo que regresa el nombre de la concesionaria
function Regresa_Nombre_Concesionaria($db,$gid)
{
    $nombre='';
    $sql="SELECT gid,name FROM groups WHERE gid=".$gid.";";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res)>0)
    {
        list($gid,$name)=$db->sql_fetchrow($res);
        $nombre=$gid."   -   ".$name;
    }
    return $nombre;
}
?>