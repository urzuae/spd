<? 
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$unidad_id,$nombre_modelo,$new,$guarda;

// Crear version
if(isset($new) && $new != "")
{
    $n =$db->sql_numrows($db->sql_query("SELECT nombre FROM crm_versiones WHERE nombre='$new'"));
    if ($n != 0)
        $error = "<b>No se pudo crear la Categoria \"$new\", por que ya existe en la tabla</b><br>\n";
    else
    {
        $db->sql_query("INSERT INTO crm_versiones (version_id, nombre) VALUES ('','$new')") or die("No se pudo crear la version");
        $version_id_sig=$db->sql_nextid();
        $error="<font color='#AA2000'>Se ha creado la categoria, por favor actualiza la subcategoria del producto creado.</font>";
    }
}

if($guarda)
{
    //SACAMOS LAS VERSIONES LIGADOS AL VEHICULO
    $del="DELETE FROM crm_vehiculo_versiones WHERE vehiculo_id=".$unidad_id.";";
    $db->sql_query($del);
    if(($_POST['versiones_ids']) >0)
    {
        foreach($_POST['versiones_ids'] as $id)
        {
            $inser="INSERT INTO crm_vehiculo_versiones (vehiculo_id,version_id) VALUES ('".$unidad_id."','".$id."');";
            $db->sql_query($inser);
            $error="<font color='#3e4f88'>Se han almacenado las Categorias seleccionadas</font>  ";
        }
    }
}


$nombre_modelo=regresa_nombre($db,$unidad_id);
$array_versiones=listado_versiones($db);
$array_seleccionadas=regresa_versiones_seleccionadas($db,$unidad_id);
if(count($array_versiones)> 0)
{
    $buffer="<input type='hidden' name='unidad_id' id='unidad_id' value='".$unidad_id."'>
        <input type='hidden' name='seleccionados' id='seleccionados'>
        <table width='60%' border='0' align='center'>
        <thead>
        <tr>
            <th class='parrafo' width='70%'>Nombre de la Categoria</th>
            <th class='parrafo' colspan='3'>Acciones</th>
        </tr></thead><tbody>";
    foreach($array_versiones as $clave => $valor)
    {
        $tmp='';
        if(in_array($clave,$array_seleccionadas))
            $tmp=' CHECKED ';
        $buffer.="<tr class=\"row".(($c++%2)+1)."\" height='30'>
                    <td><a href='index.php?_module=Modelos&_op=editt&unidad_id=".$unidad_id."&categoria_id=".$clave."'>".$valor."</a></td>
                    <td width='10%' align='center'><a href='index.php?_module=$_module&_op=actualiza_categoria&categoria_id=$clave&unidad_id=$unidad_id'><img src='../img/edit.gif' width='16' height='16' border='0' onmouseover=\"return escape('Actualiza nombre de la Categoria')\"></a></td>
                    <td width='10%' align='center'><a href=\"#\" onclick=\"del_categoria('".$clave."','".$unidad_id."')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Eliminar la categoria')\"  border=\"0\"></a></td>
                    <td width='10%' align='center'><input type='checkbox' name='marca_".$clave."' id='marca_".$clave."' ".$tmp." value='".$clave."' onmouseover=\"return escape('Asigna categoria al producto')\");\"></td>
                  </tr>";
    }
    $buffer.="<!--<tr height='30'><td colspan='3' align='right'>
                </td></tr>
        </tbody>--><!--<thead>
        <tr height='30' class='row2'>
        <td colspan='3' align='right'><font color='#ffffff'>
        </font></td>
        </tr></thead>--></table>
        <center><a href='index.php?_module=Modelos&_op=new_categoria&unidad_id=".$unidad_id."'>Crear Categoria</a></center>
        </br>
        <center><a href='index.php?_module=Modelos'>Regresar a Productos</a></center>
        </br></br>
                <center>
                <input type='button' name='marcar' id='marcar' value='Marcar Todos'>
                <input type='button' name='desmarcar' id='desmarcar' value='Desmarcar Todos'>
                <input type='button' name='asignar' id='asignar' value='Asignar a producto'>
                </center>
                </br>
                </br>
                </br>
                <center>
        <div id='resultado' style='align-text:center;'></div>";

}

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
function regresa_versiones_seleccionadas($db,$unidad_id)
{
    $array=array();
    $sql="SELECT a.vehiculo_id,a.version_id FROM crm_vehiculo_versiones a WHERE a.vehiculo_id=".$unidad_id." ORDER BY a.version_id;";
    $res=$db->sql_query($sql);
    if ( $db->sql_numrows($res) > 0 )
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[]=$fila['version_id'];
        }
    }
    return $array;
}

function listado_versiones($db)
{
    $array=array();
    $sql="SELECT version_id,nombre FROM crm_versiones ORDER BY version_id;";
    $res=$db->sql_query($sql);
    if ( $db->sql_numrows($res) > 0 )
    {
        while($fila = $db->sql_fetchrow($res))
        {
            $array[$fila['version_id']]=$fila['nombre'];

        }
    }
    return $array;
}
?>