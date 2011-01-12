<?
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$unidad_id,$version_id,$nombre_modelo,$new,$guardat,$categoria_id;

$nombre_modelo=regresa_nombre($db,$unidad_id);
$nombre_version=regresa_nombre_version($db,$categoria_id);
$array_transmisiones=listado_transmisiones($db);
$array_seleccionadas=regresa_transmisiones_seleccionadas($db,$categoria_id);
if(count($array_transmisiones) > 0)
{
    $buffer="<input type='hidden' name='unidad_id' id='unidad_id' value='".$unidad_id."'>
        <input type='hidden' name='seleccionados' id='seleccionados'>
        <table width='60%' border='0' align='center'>
        <thead>
        <tr>
            <th class='parrafo' width='70%'>Nombre de la Subcategoria</th>
            <th class='parrafo' colspan='3'>Acciones</th>
        </tr></thead><tbody>";
    foreach($array_transmisiones as $clave => $valor)
    {
        $tmp='';
        if(in_array($clave,$array_seleccionadas))
            $tmp=' CHECKED ';
        $buffer.="<tr class=\"row".(($c++%2)+1)."\" height='30'>
                    <td><a href='index.php?_module=Modelos&_op=editt&unidad_id=".$unidad_id."&categoria_id=".$categoria_id."&subcategoria_id=".$clave."'>".$valor."</a></td>
                    <td width='10%' align='center'><a href='index.php?_module=$_module&_op=actualiza_subcategoria&subcategoria_id=$clave&unidad_id=$unidad_id&categoria_id=$categoria_id'><img src='../img/edit.gif' width='16' height='16' border='0' onmouseover=\"return escape('Actualiza nombre de la Categoria')\"></a></td>
                    <td width='10%' align='center'><a href=\"#\" onclick=\"del_subcategoria('".$clave."','".$categoria_id."','".$unidad_id."')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Eliminar la categoria')\"  border=\"0\"></a></td>
                    <td width='10%' align='center'><input type='checkbox' name='marca_".$clave."' id='marca_".$clave."' ".$tmp." value='".$clave."' onmouseover=\"return escape('Asigna subcategoria a la categoria')\");\"></td>
                  </tr>";
    }
    $buffer.="<tr height='30'><td colspan='3' align='right'>
                <input type='button' name='marcar' id='marcar' value='Marcar Todos'>
                &nbsp;&nbsp;&nbsp;
                <input type='button' name='desmarcar' id='desmarcar' value='Desmarcar Todos'>
                &nbsp;&nbsp;&nbsp;
                <input type='button' name='asignar_categoria' id='asignar_categoria' value='Asignar a categoria'>
                </td></tr>
        </tbody><thead>
        <tr height='30' class='row2'>
        <td colspan='3' align='right'><font color='#ffffff'>
            <a href='index.php?_module=Modelos&_op=new_subcategoria&unidad_id=".$unidad_id."&categoria_id=".$categoria_id."'>Crear Subcategoria</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href='index.php?_module=Modelos&_op=edit&unidad_id=".$unidad_id."'>Regresar a Categorias</a>
            &nbsp;&nbsp;&nbsp;&nbsp;
            <a href='index.php?_module=Modelos'>Regresar a Productos</a>
        </font></td>
        </tr></thead></table><br>
        <div id='resultado' style='align-text:center;'></div>";




}



/*$buffer="<script>function nuevatransmision(){var vers= prompt('Ingrese el nombre de la subcategoria');if (vers) location.href=('index.php?_module=$_module&_op=editt&unidad_id=$unidad_id&version_id=$version_id&new='+vers);}</script>\n";
$buffer.=$error;
$buffer.='<input type="hidden" name="version_id" value="'.$version_id.'"><table width="80%" align="0" align="center" >
            <tbody><tr class="row1">
               <td width=25%>Nombre del Producto:</td><td width=35%>'.$nombre_modelo.'</td><td width=40%>&nbsp;</td>
            </tr>
            <tr class="row2">
               <td width=25%>Nombre de la Categoria:</td><td width=35%><input type="text" name="version" value="'.$nombre_version.'" size="35"></td><td width=40%>&nbsp;</td>
            </tr>
            <tr class="row1">
                <td>SubCategoria</td><td><br>';
                $buffer.=regresa_transmisiones_asignadas($db,$array_transmisiones,$array_seleccionadas);
                $buffer.="</td><td valign='top'><a href=\"#\" onclick=\"nuevatransmision()\"> Ingresar nueva subcategoria</a><hr color='#ffffff'><font color='#333333'>Subcategorias</font><br><ul>";
                if(count($array_seleccionadas)>0)
                {
                    foreach($array_seleccionadas as $var)
                    {
                        $buffer.="<li>".$array_transmisiones[$var]."</li>";
                    }
                }
                $buffer.="</ul><br><hr color='#ffffff'>Nota: Para seleccionar las caracter&iacute;sticas de la subcategoria, presiona la tecla <font color='#0f00ff'>Ctrl+ clic</font> en opci&oacute;n.<br>Para desmarcar alguna de las opciones, realiza el mismo proceso.<br>Al finalizar dar clic en el bot&oacute;n Guardar Subcategorias</td>
                    </tr>
                    <tr class='row1'>
                        <td colspan='2' align='center'>
                        <input type='submit' name='guardat' value='Guardar Subcategorias'>&nbsp;&nbsp;
                        <input type='button' name='regresa' value='Regresar a Categorias' onclick=\"location='index.php?_module=Modelos&_op=edit&unidad_id=$unidad_id'\">
                        </td></tr>
                    </tbody>
                </table>";

*/

/* **************** FUNCIONES AUXILIARES ***************/

/**
 * Funcion que regresa el nombre de la unidad
 * @param $db conector de la base de datos
 * @param int $unidad_id, id del unidad
 * @return string regresa el nombre de la unidad
 */
function regresa_nombre($db,$unidad_id)
{
    $name='';
    $sql="SELECT nombre FROM crm_unidades WHERE unidad_id=".$unidad_id.";";
    $res=$db->sql_query($sql);
    if ( $db->sql_numrows($res) > 0 )
    {
        $name=$db->sql_fetchfield(0,0,$res);
    }
    return $name;
}
function regresa_nombre_version($db,$version_id)
{
    $name='';
    $sql="SELECT nombre FROM crm_versiones WHERE version_id=".$version_id.";";
    $res=$db->sql_query($sql);
    if ( $db->sql_numrows($res) > 0 )
    {
        $name=$db->sql_fetchfield(0,0,$res);
    }
    return $name;

}
function regresa_transmisiones_seleccionadas($db,$version_id)
{
    $array=array();
    $sql="SELECT a.version_id,a.transmision_id FROM crm_version_transmisiones a WHERE a.version_id=".$version_id." ORDER BY a.version_id;";
    $res=$db->sql_query($sql);
    if ( $db->sql_numrows($res) > 0 )
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[]=$fila['transmision_id'];
        }
    }
    return $array;
}

function regresa_transmisiones_asignadas($db,$array_transmisiones,$array)
{
    $select='';
    if ( count($array_transmisiones) > 0 )
    {
        $select.="<select multiple name='transmisiones_ids[]' id='transmision' style='width:260px;height:160px;background-color:#fff;color:#666;'>";
        foreach($array_transmisiones as $key => $value)
        {
            $tmp="";
            if(in_array($key,$array))
               $tmp=" selected ";
            $select.="<option value=".$key." ".$tmp.">".$value."</option>";
        }
        $select.="</select>";
    }
    return $select;
}

function listado_transmisiones($db)
{
    $array=array();
    $sql="SELECT transmision_id,nombre FROM crm_transmisiones ORDER BY transmision_id;";
    $res=$db->sql_query($sql);
    if ( $db->sql_numrows($res) > 0 )
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['transmision_id']]=$fila['nombre'];

        }
    }
    return $array;
}
?>