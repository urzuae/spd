<?php
function regresa_name_group($db,$gid)
{
    $sql="SELECT gid,name,nivel_id FROM groups_ubications WHERE gid=".$gid.";";
    $res=$db->sql_query($sql);
    return $db->sql_fetchrow($res);
}
function actualiza_grupo($db,$gid,$id_nivel,$name)
{
    $mensaje="<br><br><font color='#800000'><b>Error no Aactualizado</b></font>";
    if($id_nivel==0)
        $id_nivel=1;
    switch($id_nivel)
    {
        case 0:  $nivel="Basico";  break;
        case 1:  $nivel="Basico";  break;
        case 2:  $nivel="Medio";  break;
        case 3:  $nivel="Avanzado";  break;
    }
    // actualizo la tabla de groups_ubications
    $upda_groups="UPDATE groups_ubications SET nivel_id=".$id_nivel.", nombre_nivel='".$nivel."' WHERE gid=".$gid.";";
    $res_groups=$db->sql_query($upda_groups);
    $upda_tabla_tem="UPDATE reporte_contactos_asignados SET nivel_id=".$id_nivel." WHERE gid=".$gid.";";
    $res_tabla=$db->sql_query($upda_tabla_tem);
    $sql_nivel="SELECT gid FROM crm_niveles_concesionarias WHERE gid=".$gid.";";
    $res_nivel=$db->sql_query($sql_nivel);
    
    if($db->sql_numrows($res_nivel) > 0)
        $sql_cambio="UPDATE crm_niveles_concesionarias SET nombre='".$nivel."',nivel_id=".$id_nivel." WHERE gid=".$gid.";";
    else
        $sql_cambio="INSERT INTO crm_niveles_concesionarias (gid,nombre,nivel_id) VALUES (".$gid.",'".$nivel."',".$id_nivel.");";


    if($db->sql_query($sql_cambio))
    {
        $mensaje="<br><br><font color='3e4f88'>Se ha actualizado la distribuidora:  $name al nivel $nivel</font><br>";
    }
    return $mensaje;
}

function regresa_forma($id_nivel,$gid,$name)
{

    $tmp_basico="";
    $tmp_medio="";
    $tmp_avanzado="";
    if($id_nivel==1)
        $tmp_basico=" selected ";
    if($id_nivel==2)
        $tmp_medio=" selected ";
    if($id_nivel==3)
        $tmp_avanzado=" selected ";

    $buffer='<input type="hidden" name="gid" value="'.$gid.'">
            <table width="90%" border="0" align="center" cellspancing="3">
            <tr height="30px">
            <td width="35%">Nombre de la Distribuidora: </td><td>'.$name.'</td>
            </tr>
            <tr height="30px">
            <td width="35%">Categoria: </td>
            <td>
                <select name="id_nivel" id="id_nivel" style="width:250px;">
                    <option value="0" >Seleccione</option>
                    <option value="1" '.$tmp_basico.'>B&aacute;sico</option>
                    <option value="2" '.$tmp_medio.'>Medio</option>
                    <option value="3" '.$tmp_avanzado.'>Avanzado</option>
                </select>
            </td>
            </tr>
            <tr height="30px">
                <td colspan="2" width="100%" align="center">
                <input value="Guardar" name="submit" type="submit">&nbsp;&nbsp;
                <input type="button" value="Regresar" onclick=location="index.php?_module=Concesionarias">&nbsp;&nbsp;
           </tr>
         </table>
<br>';
    return $buffer;
}
/** Rutina que sirve para asignar una Categoria (Basico, Medio, Avanzado) a una concesionaria **/
if (!defined('_IN_ADMIN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $gid;
$gid=$_REQUEST['gid'];
$tmp_datos=regresa_name_group($db,$gid);
$name=$tmp_datos['name'];
if($_REQUEST['submit'])
{
    if($_REQUEST['id_nivel']!='')
    {
        $buffer=regresa_forma($_REQUEST['id_nivel'],$gid,$name);
        $buffer.="<br>".actualiza_grupo($db,$gid,$_REQUEST['id_nivel'],$name);
    }
}
else
{
    $buffer=regresa_forma($tmp_datos['nivel_id'],$gid,$name);
}
?>