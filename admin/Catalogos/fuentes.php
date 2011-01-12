<?php
header("Cache-Control: no-cache, must-revalidate");
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db;
$array_padres=regresa_padres($db,$id_padre);
if(count($array_padres) > 0)
{
    $html_origen_padre='<table width="100%" class="tablesorter" align="center" border="0">
            <thead>
            <tr height="30px">
                <th align="center" width="80%">Origenes Padres</th>
                <td align="center" width="20%">Acciones</td>
            </tr></thead><tbody>';
            foreach($array_padres as $clave => $valor)
            {
                    $html_origen_padre.='
                        <tr class="row'.($class_row++%2?"2":"1").'" style="cursor:pointer;">
                        <td>'.strtoupper($valor).'</td>
                        <td align="center">&nbsp;&nbsp;
                        <a href="index.php?_module=Catalogos&_op=nuevaFuente&padre_id='.$clave.'&nombre='.$valor.'"><img border="0" width="16" height="16" src="../img/backup/new.png" alt="A&ntilde;adir origenes"></a>
                        &nbsp;&nbsp;
                        <a href=""><img border="0" width="16" height="16" src="../img/backup/edit.png" alt="Modificar origenes"></a>
                        &nbsp;&nbsp;
                        <a href=""><img border="0" width="16" height="16" src="../img/backup/del.png" alt="Eliminar origenes Padres"></a>
                        &nbsp;&nbsp;
                        <a href="index.php?_module=Catalogos&_op=mostrarArbol"><img border="0" width="16" height="16" src="../img/backup/list.png" alt="Bloquear origenes"></a>
                        &nbsp;&nbsp;
                        </td>
                    </tr>';
            }
            $html_origen_padre.='</tbody><thead><tr><td align="center">N&uacute;mero de origenes:</td><td align="center">'.count($array_padres).' </td></tr></thead></table><br>';
}




/**
 * Funcion que sirve para sacar los id de los padres de cualquier nivel del arbol
 * @param int conexion a la base de datos $db
 * @param int id del padre en caso de que estuviese seleccionado $id_padre
 * @return array  con los nombres de de los padres que se meteran en un combo
 */
function regresa_padres($db,$id_padre)
{
    if($id_padre> 0 )
    {
        $filtro=" WHERE padre_id=".$id_padre;
    }
    $sql_padre="SELECT fuente_id,nombre FROM crm_fuentes ".$filtro." ORDER BY fuente_id;";
    $res_padre=$db->sql_query($sql_padre);
    if($db->sql_numrows($res_padre) > 0)
    {
        while($fila = $db->sql_fetchrow($res_padre))
        {
            $array_padre[$fila['fuente_id']]=$fila['nombre'];
        }
    }
return $array_padre;
}
?>