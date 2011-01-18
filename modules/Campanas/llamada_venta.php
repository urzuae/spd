<?
  if (!defined('_IN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $uid, $campana_id, $contacto_id, $submit, $chasis, $precio,$llamada_id,$nopendientes;
$url="index.php?_module=Campanas&_op=llamada&contacto_id=$contacto_id&campana_id=$campana_id&llamada_id=$llamada_id&nopendientes=$nopendientes";
$atributos="width=450,height=210,toolbar=No,location=No,directories=No,status=No,menubar=No,scrollbars=No,resizable=NO";
//chekar si estamos autorizados
$result = $db->sql_query("SELECT gid,edit_venta FROM users WHERE uid='$uid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($gid,$edit_venta) = $db->sql_fetchrow($result);
$result = $db->sql_query("SELECT name FROM groups WHERE gid='$gid' LIMIT 1") or die("Error en grupo ".print_r($db->sql_error()));
list($grupo) = $db->sql_fetchrow($result);

$sql = "SELECT campana_id FROM crm_campanas_groups  WHERE campana_id='$campana_id' AND gid='$gid' LIMIT 1";

if($db->sql_numrows($db->sql_query($sql)) < 1) die("No está autorizado para acceder aquí.<br>".$sql);
$_css = $_themedir."css/".$_theme."/style.css";
$_theme = "";

$buffer="El contacto no tiene productos asociados";

// Consulta la tabla para sacar los modelos seleccionados por el prospecto
$sql="SELECT modelo,modelo_id,version_id,transmision_id,timestamp, venta_confirmada
      FROM crm_prospectos_unidades WHERE contacto_id='".$contacto_id."' order by timestamp;";
$res=$db->sql_query($sql) or die("Error: En la consulta de n modelos por prospecto:  ".$sql);
if($db->sql_numrows($res) > 0)
{
              /*<th width='12%'>Categoria</th>
              <th width='12%'>SubCategoria</th>
              <th width=' 5%'>Versi&oacute;n</th>*/

        $buffer=
          "<table width='99%' align='center'>
            <thead><tr bgcolor='#cdcdcd'>
              <th width='12%'>Modelo</th>
              <th width=' 9%'>No Serie</th>
              <th width=' 9%'>No Licencias</th>
              <th width=' 9%'>Venta</th>";
        if($edit_venta==1)
        {
            $buffer.="<th width=' 9%'></th><th width=' 9%'></th>";
        }
        $buffer.="</tr></thead><tbody>";
        $distintivo=0;
        while(list($modelo,$modelo_id,$version_id,$transmision_id,$timestamp,$venta_conf) = $db->sql_fetchrow($res))
        {
            $tmp_name_chasis="chasis".$contacto_id.$modelo_id.$version_id.$transmision_id.$distintivo;
            $tmp_name_precio="precio".$contacto_id.$modelo_id.$version_id.$transmision_id.$distintivo;
            $boton="b".$contacto_id.$modelo_id.$version_id.$transmision_id.$distintivo;
            $venta_registrada=Busca_en_ventas($db,$contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp);
            $read='';
            if(count($venta_registrada)>0)
                $read='Readonly';
            
            /*<td>".busca_datos($db,'crm_versiones',' version_id',$version_id)."</td>
            <td>".busca_datos($db,'crm_transmisiones',' transmision_id',$transmision_id)."</td>
            <td>".$ano."</td>*/

            $buffer.="<tr>
            <td>".busca_datos($db,'crm_unidades',' unidad_id',$modelo_id)."</td>
            <td><input type='text' size='12' name='$tmp_name_chasis' id='$tmp_name_chasis' value='".$venta_registrada['chasis']."' maxlength='17' $read></td>
            <td><input type='text' size='12' name='$tmp_name_precio' id='$tmp_name_precio' value='".$venta_registrada['precio']."' onblur='this.value = this.value;' $read></td>";
            if(count($venta_registrada) == 0)
            {
                $timestamp_vta='';
                $chasis='';
                $buffer.="<td><input type='button' name='$boton' id='$boton' value='Registra' onclick=\"concreta_venta('$modelo','$contacto_id','$modelo_id','$version_id','$transmision_id','$timestamp','$tmp_name_chasis','$tmp_name_precio','$uid','$boton');\">";
            }
            else
            {
              $conf_message = $venta_conf==0?"No confirmada":"Confirmada";
                $buffer.="<td style=\"text-align:center;\">$conf_message</td>";
                $timestamp_vta=$venta_registrada['timestamp'];
                $chasis=$venta_registrada['chasis'];
            }
            $url_update="index.php?_module=Campanas&_op=actualiza_venta&contacto_id=$contacto_id&modelo_id=$modelo_id&version_id=$version_id&transmision_id=$transmision_id&timestamp=$timestamp_vta&timestamp_unidades=$timestamp&chasis=$chasis&campana_id=$campana_id&llamada_id=$llamada_id&nopendientes=$nopendientes";
            if ( ($edit_venta==1) && (count($venta_registrada)>0))
            {
                $buffer.="<td align='center'><a href='$url_update'><img src='img/edit.gif' style='border: 0px solid white; cursor: pointer;' title='Actualizar informacion sobre la venta de vehiculo'></a></td>
                          <td align='center'><a href='#' onclick=\"elimina_venta_vendedores('$contacto_id','$modelo_id','$version_id','$transmision_id','$timestamp_vta','$chasis','$campana_id','$llamada_id','$nopendientes');\"><img src='img/del.gif' style='border: 0px solid white; cursor: pointer;' title='Cancelar Venta de vehiculo'></a></td>";
            }
            $buffer.="</tr>";
            $distintivo++;
    }
    $buffer.="</table><br><center><div id='valida' style='color:#ff0000;font-size:14px;font-weigth:bold;'></div></center><br>";
    $buffer.="<center><br><input type='button' name='cerrar' value='Cerrar Ventana' onclick=\"self.close();window.opener.location.href='".$url."'\"></center>";
}

function Busca_en_ventas($db,$contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp)
{
    $array=array();
    $sql_vtas="SELECT chasis,precio,timestamp FROM crm_prospectos_ventas WHERE contacto_id=".$contacto_id." AND
          modelo_id=".$modelo_id." AND version_id=".$version_id." AND transmision_id=".$transmision_id. " AND timestamp_unidades='".$timestamp."' LIMIT 1;";
    $res_vtas=$db->sql_query($sql_vtas);
    if($db->sql_numrows($res_vtas)> 0)
    {
        $array=$db->sql_fetchrow($res_vtas);
    }
    return $array;
}
function busca_datos($db,$tabla,$campo,$valor)
{
    $dato='';
    $sql="SELECT nombre FROM ".$tabla." WHERE ".$campo."=".$valor.";";
    $res=$db->sql_query($sql);
    if($db->sql_numrows($res) > 0)
    {
        $dato=$db->sql_fetchfield(0,0,$res);
    }
    return $dato;
}

/*if ($submit)
{
  $precio = remove_money_format2($precio);
  $sql = "INSERT INTO crm_prospectos_ventas (contacto_id, uid, chasis, precio)VALUES('$contacto_id', '$uid', '$chasis', '$precio')";
  $db->sql_query($sql) or die($sql);
  
  //actualizar el status como finalizado
	$sql = "insert into crm_campanas_llamadas_finalizadas select * from crm_campanas_llamadas where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	/*
     *   esto estaba comentado antes de que luis le moviera
     * $sql = "insert into crm_contactos_finalizados select * from crm_contactos where contacto_id = '$contacto_id'";
	$db->sql_query($sql) or die($sql);	
	$sql = "delete from crm_campanas_llamadas WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);
	$sql = "delete from crm_contactos WHERE contacto_id='$contacto_id'";
	$db->sql_query($sql) or die($sql);*/
  /*die("<html><head><script>alert('Guardado');window.close();</script></head></body>");
}
*/
?>
