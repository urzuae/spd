<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$contacto_id,$modelo_id,$version_id,$transmision_id,$timestamp,$campana_id,$llamada_id,$nopendientes,$_site_title;
$_css = $_themedir."/style.css";
$_theme = "";
$_site_title = "Actualizar venta";

$url_listado="index.php?_module=Campanas&_op=llamada_venta&contacto_id=$contacto_id&campana_id=$campana_id&llamada_id=$llamada_id&nopendientes=$nopendientes";
$buffer="<p align='center'> La venta no esta registrada en la Tabla de Modelos Vendidos</p>";
$unidades=unidades($db);
$sql="SELECT contacto_id,uid,chasis,precio,timestamp,modelo_id,version_id,transmision_id,timestamp_unidades
      FROM crm_prospectos_ventas
      WHERE contacto_id='".$contacto_id."' AND modelo_id='".$modelo_id."' AND version_id='".$version_id."'
        AND transmision_id='".$transmision_id."' AND timestamp='".$timestamp."';";
$res=$db->sql_query($sql) or die("Error en la consulta de los datos");
$num=$db->sql_numrows($res);
if( $num > 0)
{

    $distintivo=0;
    list($c,$uid,$chasis,$precio,$timestamp,$modelo_id,$version_id,$transmision_id,$timestamp_unidades) = $db->sql_fetchrow($res);
    $tmp_name_chasis="chasis".$contacto_id.$modelo_id.$version_id.$transmision_id.$distintivo;
    $tmp_name_precio="precio".$contacto_id.$modelo_id.$version_id.$transmision_id.$distintivo;
    $boton="b".$contacto_id.$modelo_id.$version_id.$transmision_id.$distintivo;
    $nom_vendedor=Regresa_Nombre_Vendedor($db,$uid);
    $nom_contacto=Regresa_Nombre_Prospecto($db,$contacto_id);
    $buffer="<input type='hidden' name='contacto_id' id='contacto_id' value='".$c."'>
             <input type='hidden' name='modelo_id' id='modelo_id' value='".$modelo_id."'>
             <input type='hidden' name='version_id' id='version_id' value='".$version_id."'>
             <input type='hidden' name='transmision_id' id='transmision_id' value='".$transmision_id."'>
             <input type='hidden' name='chasis' id='chasis' value='".$cchasis."'>
             <input type='hidden' name='precio' id='precio' value='".$precio."'>
             <input type='hidden' name='timestamp' id='timestamp' value='".$timestamp."'>
             <input type='hidden' name='timestamp_unidades' id='timestamp_unidades' value='".$timestamp_unidades."'>
             <table width='95%' align='center' border='0'>
             <tr>
             <td colspan='2' align='center'> Actualizaci&oacute;n del Modelo Vendido</td>
              </tr>
            <tr>
             <td align='Left'>Nombre del Cliente:</td><td>".$nom_contacto."</td>
            </tr>
            <tr>
             <td align='Left'>Vendedor:</td><td>".$nom_vendedor."</td>
            </tr>
            <tr>
             <td align='Left'>Modelo:</td><td>".$unidades[$modelo_id]."</td>
            </tr>
            <tr>
             <td align='Left'>Chasis (".$chasis."):</td><td><input type='text' size='15' name='".$tmp_name_chasis."' id='".$tmp_name_chasis."' value='".$chasis."' maxlength='17'></td>
             </tr>
            <tr>
             <td align='Left'>Precio (".$precio."):</td><td><input type='text' size='15' name='".$tmp_name_precio."' id='".$tmp_name_precio."' value='".$precio."' onblur='this.value = moneyFormat(this.value);'></td>
             </tr>";
            $buffer.="<tr><td colspan='2' align='center'>
                <input type='button' name='".$boton."' id='".$boton."' value='Actualiza' onclick=\"actualiza_venta('$unidades[$modelo_id]','$contacto_id','$modelo_id','$version_id','$transmision_id','$timestamp','$tmp_name_chasis','$tmp_name_precio','$uid','$boton','$chasis','$precio');\">
                &nbsp;&nbsp;
                <input type='button' name='ventas' id='ventas' value='Listado de Ventas' onclick=\"window.location.href='$url_listado'\">
              </td></tr><table>
              <br><center><div id='valida' style='color:#ff0000;font-size:14px;font-weigth:bold;'></div></center><br>";
}
function Regresa_Nombre_Prospecto($db,$c)
{
    $nombre='';
    $sql_v_f="SELECT concat(nombre,' ',apellido_paterno,' ',apellido_materno) FROM crm_contactos WHERE contacto_id=".$c.";";    
    $res_v_f=$db->sql_query($sql_v_f);
    if($db->sql_numrows($res_v_f)>0)
    {
        $nombre=$db->sql_fetchfield(0,0,$res_v_f);
    }
    else
    {
        $sql_v="SELECT concat(nombre,' ',apellido_paterno,' ',apellido_materno) FROM crm_contactos_finalizados WHERE contacto_id=".$c.";";
        $res_v=$db->sql_query($sql_v);
        if($db->sql_numrows($res_v)>0)
        {
            $nombre=$db->sql_fetchfield(0,0,$res_v);
        }
    }
    return $nombre;
}

function Regresa_Nombre_Vendedor($db,$uid)
{
    $nombre='';
    $sql_v="SELECT name FROM users WHERE uid=".$uid." limit 1;";
    $res_v=$db->sql_query($sql_v);
    if($db->sql_numrows($res_v)>0)
    {
        $nombre=$db->sql_fetchfield(0,0,$res_v);
    }
    return $nombre;
}

function unidades($db)
{
    $sql = "SELECT unidad_id, nombre FROM crm_unidades";
    $r = $db->sql_query($sql) or die($sql);
    $unidades = array();
    while (list($id, $n) = $db->sql_fetchrow($r))
        $unidades[$id] = $n;
    return $unidades;
}

?>
