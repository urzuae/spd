<?php
/**
 * Funcion que genera el select de origenes
 */
global $db;
function genera_empresarial($db,$grupo_id)
{
    $res = $db->sql_query("SELECT DISTINCT nombre,grupo_empresarial_id FROM crm_grupos_empresariales order by nombre ASC");
    if($db->sql_numrows($res) > 0)
    {
        $select_empresarial="<select name='grupo_empresarial_id' id='grupo_empresarial_id'  style='width:250px;'>
                          <option selected='selected' value='0'>Selecciona Grupo Empresarial</option>";
        while($rs = $db->sql_fetchrow($res))
        {
            $tmp_seleccion="";
            if($rs['grupo_empresarial_id']==$grupo_id)
            {
                $tmp_seleccion="SELECTED";
            }
            $select_empresarial.="<option value='".$rs['grupo_empresarial_id']."' ".$tmp_seleccion.">".$rs['nombre']."</option>";
        }
        $select_empresarial.="</select>";
    }

    return $select_empresarial;
}
function genera_region($db,$region_id)
{
    $res = $db->sql_query("SELECT * FROM crm_regiones order by nombre ASC");
    if($db->sql_numrows($res) > 0)
    {
        $select_regiones="<select name='region_id' id='region_id' style='width:250px;'>
                          <option selected='selected' value='0'>Selecciona Region</option>";
        while($rs = $db->sql_fetchrow($res))
        {
            $tmp_seleccion="";
            if($rs['region_id']==$region_id)
            $tmp_seleccion="SELECTED";
            $select_regiones.="<option value=".$rs['region_id']." ".$tmp_seleccion.">".$rs['nombre']."</option>";
        }
        $select_regiones.="</select>";
    }
    return $select_regiones;
}

/**
 * Funcion que genera el select de zonas
 */

function genera_zonas($db,$zona_id)
{
    $res = $db->sql_query("SELECT zona_id,nombre FROM crm_zonas order by nombre;");
    if($db->sql_numrows($res) > 0)
    {
        $select_zonas='<select id="zona_id" name="zona_id" style="width:250px;">
                       <option value="0" selected="selected">Selecciona Zona</option>';
        while($rs = $db->sql_fetchrow($res))
        {
            $tmp_seleccion="";
            if($rs['zona_id']==$zona_id)
            $tmp_seleccion="SELECTED";
            $select_zonas.= "<option value=".$rs['zona_id']." ".$tmp_seleccion.">".strtoupper($rs['nombre'])."</option>";
        }
        $select_zonas.='</select>';
    }
    return $select_zonas;
}
/**
 * Funcion que genera el select de entidades
 */
function genera_entidades($db,$entidad_id)
{
    $res = $db->sql_query("SELECT id_entidad,nombre FROM crm_entidades order by nombre ASC");
    if($db->sql_numrows($res) > 0)
    {
        $select_entidad='<select id="entidad_id" name="entidad_id" style="width:250px;">
                <option value="0" selected="selected">Selecciona Entidad</option>';

        while($rs = $db->sql_fetchrow($res))
        {
            $tmp_seleccion="";
            if($rs['id_entidad']==$entidad_id)
            $tmp_seleccion="SELECTED";
            $select_entidad.= "<option value=".$rs['id_entidad']." ".$tmp_seleccion.">{$rs['nombre']}</option>";
        }
        $select_entidad.='</select>';
    }
    return $select_entidad;

}
/**
 * Funcion que genera el select de plaza
 */
function genera_plaza($db,$plaza_id)
{
    $res = $db->sql_query("SELECT plaza_id,nombre FROM crm_plazas order by nombre ASC");
    if($db->sql_numrows($res) > 0)
    {
        $select_plaza='<select id="plaza_id" name="plaza_id" style="width:250px;">
                   <option value="0" selected="selected">Selecciona Plaza</option>';
        while($rs = $db->sql_fetchrow($res))
        {
            $tmp_seleccion="";
            if($rs['plaza_id']==$plaza_id)
            $tmp_seleccion="SELECTED";
            $select_plaza.= "<option value=".$rs['plaza_id']." ".$tmp_seleccion.">{$rs['nombre']}</option>";
        }
        $select_plaza.='</select>';
    }
    return $select_plaza;
}
/**
 * Funcion que genera el select de origen
 */
function genera_origen($db,$origen_padre_id)
{
    $sql_padres="SELECT a.padre_id,b.nombre,b.fuente_id FROM crm_fuentes_arbol a,crm_fuentes b WHERE a.padre_id=1 and a.hijo_id=b.fuente_id ORDER BY b.nombre;";
    $res_padres=$db->sql_query($sql_padres);
    if( $db->sql_numrows($res_padres) > 0)
    {
        $select_origenPadre="<select name=\"padre_id\" id=\"padre_id\" class=\"nodo\">
                          <option value='0'>Seleccione</option>";
        while($fila = $db->sql_fetchrow($res_padres))
        {
            $select_origenPadre.= "<option value=\"".$fila['fuente_id']."\">".$fila['nombre']."</otpion>";
        }
        $select_origenPadre.="</select>";
    }
    /** cambios de luis hdez **/
    $select_origenPadre.="&nbsp;&nbsp;
                        <input type='hidden' id='origen' name='origen'>
                        <br><select name='hijo_id_1' id='hijo_id_1' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_2' id='hijo_id_2' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_3' id='hijo_id_3' class='nodo'><option value='0'>Seleccionar</option></select>
                        <br><select name='hijo_id_4' id='hijo_id_4' class='nodo'><option value='0'>Seleccionar</option></select>";
    return $select_origenPadre;
}

/**
 * Funcion que genera el select de grupos
 */
function genera_grupos($db,$id_group)
{
    $res = $db->sql_query("SELECT DISTINCT groups.gid,groups.name FROM groups order by groups.gid ASC");
    if($db->sql_numrows($res) > 0)
    {
        $select_concesion='<select id="concesionaria" name="concesionaria"  style="width:250px;">
                            <option value="0" selected="selected">Selecciona Distribuidor</option>';
        while($rs = $db->sql_fetchrow($res))
        {
            $tmp_seleccion="";
            if($rs['gid']==$id_group)
            $tmp_seleccion="SELECTED";
            $select_concesion.= "<option value=".$rs['gid']." ".$tmp_seleccion.">".$rs['gid']."&nbsp;&nbsp;".$rs['name']."</option>";
        }
        $select_concesion.='</select>';
    }
    return $select_concesion;
}

function genera_categoria($basico,$medio,$avanzado)
{
    $cat_b=" value=0 ";$cat_m=" value=0 ";$cat_a=" value=0 ";
    if($basico>0) $cat_b=" value=1 checked";
    if($medio>0) $cat_m =" value=2 checked";
    if($avanzado>0) $cat_a=" value=3 checked";
    $select_categoria="<input type='checkbox'  name='basico'   id='basico' $cat_b>&nbsp;B&aacute;sico<br>
                        <input type='checkbox' name='medio'    id='medio'  $cat_m>&nbsp;Medio<br>
                        <input type='checkbox' name='avanzado' id='avanzado' $cat_a>&nbsp;Avanzado";
    return $select_categoria;

}

$grupo_empresarial_id=$_REQUEST['grupo_empresarial_id'];
$region_id=$_REQUEST['region_id'];
$zona_id=$_REQUEST['zona_id'];
$entidad_id=$_REQUEST['entidad_id'];
$plaza_id=$_REQUEST['plaza_id'];
$concesionaria=$_REQUEST['concesionaria'];
$origen=$_REQUEST['origen'];
$fecha_ini=$_REQUEST['fecha_ini'];
$fecha_fin=$_REQUEST['fecha_fin'];
$basico=$_REQUEST['basico'];
$medio=$_REQUEST['medio'];
$avanzado=$_REQUEST['avanzado'];

if($grupo_empresarial_id >0 )
{
    $region_id=0;
    $zona_id=0;
    $entidad_id=0;
    $plaza_id=0;
}

$url="&grupo_empresarial_id=".$grupo_empresarial_id.
         "&region_id=".$region_id.
         "&zona_id=".$zona_id.
         "&entidad_id=".$entidad_id.
         "&plaza_id=".$plaza_id.
         "&origen=".$origen.
         "&fecha_ini=".$fecha_ini.
         "&fecha_fin=".$fecha_fin.
         "&basico=".$basico.
         "&medio=".$medio.
         "&avanzado=".$avanzado;

// generamos los catalogos
$select_empresarial=genera_empresarial($db,$grupo_empresarial_id);
$select_regiones=genera_region($db,$region_id);
$select_zonas=genera_zonas($db,$zona_id);
$select_entidad=genera_entidades($db,$entidad_id);
$select_plaza=genera_plaza($db,$plaza_id);
$select_categoria=genera_categoria($basico,$medio,$avanzado);
$select_concesion=genera_grupos($db,$concesionaria);
$select_origenPadre=genera_origen($db,$origen);

if($fecha_ini!='' && $fecha_fin=='')
{
    $tmp_filtros_contactos[]="substr(b.timestamp,1,10)='".$fecha_ini."'";
    $tmp_fechas=" AND substr(b.timestamp,1,10)='".$fecha_ini."'";
    $fecha_i=$fecha_ini;
    $fecha_c=$fecha_ini;
}

if($fecha_ini=='' && $fecha_fin!='')
{
    $tmp_filtros_contactos[]="substr(b.timestamp,1,10)='".$fecha_fin."'";
    $tmp_fechas=" AND substr(b.timestamp,1,10)='".$fecha_fin."'";
    $tmp_filtros_store[]="substr(a.timestamp,1,10)='".$fecha_fin."'";
    $fecha_i=$fecha_fin;
    $fecha_c=$fecha_fin;

}

if($fecha_ini!='' && $fecha_fin!='')
{
    $tmp_filtros_contactos[]="substr(b.timestamp,1,10) BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'";
    $tmp_fechas=" AND substr(b.timestamp,1,10) BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'";
    $tmp_filtros_store[]="substr(a.timestamp,1,10) BETWEEN '".$fecha_ini."' AND '".$fecha_fin."'";
    $fecha_i=$fecha_ini;
    $fecha_c=$fecha_fin;

}

if($grupo_empresarial_id !=0)
$tmp_filtros[]= "a.grupo_empresarial_id=".$grupo_empresarial_id;

if($region_id!=0)
$tmp_filtros[]= "a.region_id=".$region_id;

if($zona_id!=0)
$tmp_filtros[]= "a.zona_id=".$zona_id;

if($entidad_id!=0)
$tmp_filtros[]= "a.entidad_id=".$entidad_id;

if($plaza_id!=0)
$tmp_filtros[]= "a.plaza_id=".$plaza_id;

if($concesionaria!=0)
{
    $concesionaria= 0 +$concesionaria;
    $tmp_filtros[]= "b.gid='".$concesionaria."'";
}
if($origen!=0)
{
    $tmp_filtros_contactos[]= "b.origen_id='".$origen."'";
    $tmp_origen=$origen;
}

if ( ($basico>0) && ($medio == 0) && ($avanzado == 0) )
{
    $tmp_filtros[]= " a.nivel_id = 1 ";
}
if ( ($basico == 0) && ($medio > 0) && ($avanzado == 0) )
{
    $tmp_filtros[]= " a.nivel_id = 2 ";
}
if ( ($basico == 0) && ($medio == 0) && ($avanzado >0) )
{
    $tmp_filtros[]= " a.nivel_id = 3 ";
}
if ( ($basico > 0) && ($medio > 0) && ($avanzado == 0) )
{
    $tmp_filtros[]= " a.nivel_id BETWEEN 1 AND 2 ";
}
if ( ($basico == 0) && ($medio > 0) && ($avanzado > 0) )
{
    $tmp_filtros[]= " a.nivel_id BETWEEN 2 AND 3 ";
}
if ( ($basico > 0) && ($medio == 0) && ($avanzado > 0) )
{
    $tmp_filtros[]= "( a.nivel_id = 1 OR nivel_id = 3)";
}
  
?>