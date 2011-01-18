<?php
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db,$_module;
#$_module='Catalogos';
$buffer="";

// Guardo en un array los nombre de las fuentes
$sql="SELECT a.padre_id,a.hijo_id FROM crm_fuentes_arbol as a WHERE a.padre_id =1 ORDER BY a.padre_id,a.hijo_id;";
$res=$db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
if($db->sql_numrows($res)>0)
{
    while(list($id_padre,$id_hijo)=$db->sql_fetchrow($res))
    {
        $array_padres[$id_hijo]=Listado_Fuentes_Hijas($db,$id_hijo);
    }
}

function Listado_Fuentes_Hijas($db,$id_hijo)
{
    $array_hijos=array();
    $result = $db->sql_query("SELECT a.hijo_id FROM crm_fuentes_arbol as a left join crm_fuentes as b on a.hijo_id = b. fuente_id WHERE a.padre_id=".$id_hijo." AND b.active != 2;") OR die("Error al consultar db: ".print_r($db->sql_error()));
    if($db->sql_numrows($res_z)>0)
    {
        while(list($hijo_ids)= $db->sql_fetchrow($res_z))
        {
            $array_hijos[]=$hijo_ids;
        }
    }
    return $array_hijos;
}

$_site_title = "Origenes";
$_html .= "<div class=title>Lista de Fuentes</div><br>\n";
$_html .= "Aquí se muestra la lista de los fuentes.<br>\n";
$_html .= "<table width='60%' border='0' align='center' class='tablesorter'>";
$_html .= "<thead><tr><th>Fuentes</th><th colspan='4'>Acciones</th></tr></thead><tbody>";
if (count($array_padres)>0)
    
{   foreach($array_padres as $clave => $array_datos){
    $_html.=Pinta_fuente($db,$clave,1,$_module);
    if(count($array_datos) > 0)
    {
        foreach($array_datos as $hijos)
        {
            $_html.=Pinta_fuente($db,$hijos,2,$_module);
        }
    }
}
}

$_html .=  '<center></tbody><thead><tr class="row".(($c++%2)+1)."\">
			    <td colspan="8" align="right">
			    </td></tr></thead></table></td></tr></table><br>
                            <INPUT TYPE="submit" VALUE="Crear una fuente" onclick="window.location=\'index.php?_module=Catalogos&_op=new\'" >
			    </center>';

function Pinta_fuente($db,$id_fuente,$pos,$_module)
{
    $tit='';
    if($pos > 1 ) $tit="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    
    $sql="SELECT fuente_id,nombre,active FROM crm_fuentes WHERE active!= 2 and fuente_id=".$id_fuente." ORDER BY fuente_id limit 1;";
    $res=$db->sql_query($sql) or die ("Error en la consulta:  ".$sql);
    if($db->sql_numrows($res)>0)
    {
        list($id,$name,$active)=$db->sql_fetchrow($res);
        $buf=  "
            <tr class='row2'>
                    <td><a href='index.php?_module=".$_module."&_op=edit&unidad_id=".$id."'>".$tit.$name."<a></td>
                    <td align='center'><a href='index.php?_module=$_module&_op=edit&unidad_id=$id'><img src='../img/edit.gif' width='16' height='16' border='0' onmouseover=\"return escape('Actualiza nombre de la fuente')\"></a></td>";
        if($active==1)
        {
            $buf.="<td align='center'><a href='#' onclick=\"bloquea_origen('".$id."');\"><img src='../img/lock.gif' width='16' height='16' border='0' onmouseover=\"return escape('Bloquea la fuente')\"></a></td>
                          <td>&nbsp;</td>";
        }
        else
        {
            $buf.="<td>&nbsp;</td>
                          <td align='center'><a href='#' onclick=\"desbloquea_origen('".$id."');\"><img src='../img/desbloquea.jpg' width='16' height='16' border='0' onmouseover=\"return escape('Desbloquea la fuente')\"></a></td>";
        }
        $buf.="<td align='center'><a href='#' onclick=\"elimina_origen('".$id."');\"><img src='../img/del.gif' width='16' height='16' border='0' onmouseover=\"return escape('Elimina la fuente')\"></a></td>
            </tr>\n";
    }
    return $buf;
}
?>