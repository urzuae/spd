<?

if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $del, $new,$_module,$_id,$msg_ciclo,$_site_title;
$_site_title = "Productos";
$error="";
if($del)
{
    $db->sql_query("UPDATE crm_unidades SET active=0 WHERE unidad_id='$del' LIMIT 1") or die("No se pudo borrar el vehiculo");
    $error="<font color='#666'>Se ha eliminado el modelo seleccionado.</font>";
}

//lista de usuarios
if(isset($new) && $new != "")
{
    $n =$db->sql_numrows($db->sql_query("SELECT nombre FROM crm_unidades WHERE nombre='$new'"));
    if ($n != 0)
        $error = "<b>No se pudo crear el producto \"$new\", por que ya existe en la tabla</b><br>\n";
    else
    {
        $db->sql_query("INSERT INTO crm_unidades (unidad_id, nombre)VALUES('','$new')") or die("No se pudo crear el modelo");
        $unidad_id_sig=$db->sql_nextid();
        $error="<font color='#3e4f88'>Se ha creado el nuevo producto, por favor actualiza la versi&oacute;n y la subcategoria de tu producto creado.</font>";
    }
}

//lista de usuarios
$_html .= "<div class=title>Lista de Productos</div><br>\n";
$_html .= "Aquí se muestra la lista de productos registrados en el sistema.<br>\n";
$_html .= $error;
$_html .= "<table width='60%' border='0' align='center' class='tablesorter'>";
$_html .= "<thead><tr><th>Nombre del Producto</th><th colspan='2'>Acciones</th></tr></thead><tbody>";
$result = $db->sql_query("SELECT unidad_id, nombre FROM crm_unidades WHERE active=1 ORDER BY nombre") OR die("Error al consultar db: ".print_r($db->sql_error()));
while (list($unidad_id, $nombre) = htmlize($db->sql_fetchrow($result)))
{
	$_html .=  "<tr class=\"row".(($c++%2)+1)."\">
                <td><a href=\"index.php?_module=$_module&_op=edit&unidad_id=$unidad_id\">$nombre<a></td>
                <td><a href=\"index.php?_module=$_module&_op=actualiza&unidad_id=$unidad_id\"><img src=\"../img/edit.gif\" onmouseover=\"return escape('Actualiza el nombre del producto')\"  border=0></a></td>
                <td><a href=\"#\" onclick=\"del_producto('$unidad_id','$nombre')\"><img src=\"../img/del.gif\" onmouseover=\"return escape('Elimina el producto')\"  border=0></a></td>"

              ."</tr>\n";
}
$_html .=  '
			    </td></tr></thead></table></td></tr></table>
			    <center>
			    <INPUT TYPE="submit" VALUE="Crear un producto" onclick="window.location=\'index.php?_module=Modelos&_op=new\'" >
			    </center>';

/* Texto anterior
<td colspan='3' align='right'><a href=\"index.php?_module=$_module&_op=new\" ><span class='parrafo'>Crear un Producto nuevo</span></a></td></tr></thead></table></td></tr></table><br>";*/
	    
$_html .= "<center><p>¿Necesita ayuda? De un clic en el ícono.</p>
          <a href=\"../admin/Ayuda/ayuda1.php\" onClick=\"return popup(this, 'notes')\"><img src=\"../img/ayuda.gif\" alt=\"Ayuda\" /></a></center>";



?>
