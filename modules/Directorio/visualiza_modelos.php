<?php
$sql="SELECT b.edit_contact FROM crm_contactos as a left join users as b on a.uid=b.uid
      WHERE a.contacto_id=".$contacto_id." limit 1;";
$res=$db->sql_query($sql);
list($edit_contact)=$db->sql_fetchrow($res);


$sql="SELECT modelo,version,ano,paquete,modelo_id,version_id,transmision_id,timestamp FROM crm_prospectos_unidades WHERE contacto_id='".$contacto_id."' order by timestamp;";
$res=$db->sql_query($sql);
if($db->sql_numrows($res) > 0)
{
    $buffer="<table width='80%' align='center' border='0' bordercolor='dcdcdc'>";
    if($edit_contact == 1)
    {
    $buffer.="<table width='80%' align='center' border='0' bordercolor='dcdcdc'>
        <tr>
        <td colspan='8' align='justify'>
        Pasos para actualizar la informaci&oacute;n de un producto:<br>
        1.- Dar un clic en el nombre del producto que aparece en el listado.<br>
        2.- Actualizar los datos del producto.<br>
        3.- Dar clic en el boton Actualizar Inf producto.<br>
        4.- El cambio se visualizar&aacute; en el listado de productos.
        </td>
        </tr>";
    }
        $buffer.="<tr class='franja'>
        <td style='color:#fff;text-align:center;font-weight:bold;'>Producto</td>
        <td style='color:#fff;text-align:center;font-weight:bold;'>Categoria</td>
        <td style='color:#fff;text-align:center;font-weight:bold;'>Subcategoria</td>";
    if($edit_contact == 1)
    {
        $buffer.="<td style='color:#fff;text-align:center;font-weight:bold;'>Borrar</td>";
    }
    $buffer.="</tr>";
    $cont=0;
    while(list($modelo,$version,$ano,$paquete,$modelo_id,$version_id,$transmision_id,$timestamp) = $db->sql_fetchrow($res))
    {
        $buffer.="<tr onMouseDown=\"actualiza_modelo('$modelo','$contacto_id','$modelo_id','$version_id','$transmision_id','$ano','$timestamp');\">
        <td>".$modelo."</td>
        <td>".$version."</td>
        <td>".$paquete."</td>";
        if($edit_contact == 1)
        {
            $buffer.="<td align='center'><input type='button' name='del$tmp' style='background-color:#ffffff;color:#3e4f88;border:0px;' onclick=\"elimina_modelo('$modelo','$contacto_id','$modelo_id','$version_id','$transmision_id','$timestamp');\"   value='Borrar' ></td>";
        }
        $buffer.="</tr>";
        $cont++;
    }
    $buffer.="</table>";
}

function regresa_transmision($db,$transmision_id)
{
    $dato='';
    $sql="SELECT nombre FROM crm_transmisiones WHERE transmision_id=".$transmision_id.";";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $dato=$db->sql_fetchfield(0,0,$res);
    }
    return $dato;

}
?>