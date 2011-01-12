<?php
if(!defined('_IN_MAIN_INDEX') && !defined('_IN_ADMIN_MAIN_INDEX'))
{
    die("No puedes acceder directamente a este archivo...");
}
//las siguientes funciones son para determinar el estado de la republica
global $_entidades_federativas;
$_entidades_federativas = array(
    "AGUASCALIENTES", 
    "BAJA CALIFORNIA", 
    "BAJA CALIFORNIA SUR", 
    "CAMPECHE", 
    "CHIAPAS", 
    "CHIHUAHUA", 
    "COAHUILA", 
    "COLIMA", 
    "DISTRITO FEDERAL", 
    "DURANGO", 
    "GUANAJUATO", 
    "GUERRERO", 
    "HIDALGO", 
    "JALISCO", 
    "ESTADO DE MEXICO", 
    "MICHOACAN", 
    "MORELOS", 
    "NAYARIT", 
    "NUEVO LEON", 
    "OAXACA", 
    "PUEBLA", 
    "QUERETARO", 
    "QUINTANA ROO", 
    "SAN LUIS POTOSI", 
    "SINALOA", 
    "SONORA", 
    "TABASCO", 
    "TAMAULIPAS", 
    "TLAXCALA", 
    "VERACRUZ", 
    "YUCATAN", 
    "ZACATECAS");

// $_entidades_federativas = sort($_entidades_federativas);
function entidad_federativa($id)
{
    global $_entidades_federativas;
    return $_entidades_federativas[$id - 1]; //el id 1 debe ser el aguascalientes
}

function select_entidades_federativas($preselect_id = "0")
{
    if(($preselect_id > 32 && $preselect_id < 1))
        $preselect_id = 32;
    global $_entidades_federativas;
    $select = "<select name=\"entidad_id\" id=\"entidad_id\">\n";
    if(($i) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>Seleccione una entidad federativa</option>\n";
    foreach($_entidades_federativas as $entidad)
    {
        if(($i + 1) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"" . ($i + 1) . "\"$selected>" . $entidad . "</option>\n";
        $i++;
    }
    $select .= "</select>\n";
    return $select;
}
global $_dias;
$_dias = array();
for($i = 1; $i <= 31; $i++)
    array_push($_dias, $i);

function select_dia($preselect_id = 0)
{
    if(($preselect_id > 32 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_entidades_federativas;
    $select = "<select name=\"dia\">\n";
    if(($i) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>día</option>\n";
    for($i = 1; $i <= 31; $i++)
    {
        if(($i) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        if(strlen($i) < 2)
            $j = "0$i";
        else
            $j = $i;
        $select .= " <option value=\"" . ($j) . "\"$selected>" . $j . "</option>\n";
    }
    $select .= "</select>\n";
    return $select;
}

function select_dia_extra($name, $preselect_id = 0)
{
    if(($preselect_id > 32 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_entidades_federativas;
    $select = "<select name=\"$name\">\n";
    if(($i) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>día</option>\n";
    for($i = 1; $i <= 31; $i++)
    {
        if(($i) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        if(strlen($i) < 2)
            $j = "0$i";
        else
            $j = $i;
        $select .= " <option value=\"" . ($j) . "\"$selected>" . $j . "</option>\n";
    }
    $select .= "</select>\n";
    return $select;
}
global $_meses;
$_meses = array(
    "Enero", 
    "Febrero", 
    "Marzo", 
    "Abril", 
    "Mayo", 
    "Junio", 
    "Julio", 
    "Agosto", 
    "Septiembre", 
    "Octubre", 
    "Noviembre", 
    "Diciembre");

function select_mes($preselect_id = 0)
{
    if(($preselect_id > 12 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_meses;
    $select = "<select name=\"mes\" style=\"width:80px;\">\n";
    if((0) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>mes</option>\n";
    foreach($_meses as $entidad)
    {
        if(($i + 1) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        if(strlen($i) < 2)
            $j = "0" . ($i + 1);
        else
            $j = $i + 1;
        $select .= " <option value=\"$j\"$selected>" . $entidad . "</option>\n";
        $i++;
    }
    $select .= "</select>\n";
    return $select;
}

function select_mes_extra($name, $preselect_id = 0)
{
    if(($preselect_id > 12 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_meses;
    $select = "<select name=\"$name\" style=\"width:80px;\">\n";
    if((0) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>mes</option>\n";
    foreach($_meses as $entidad)
    {
        if(($i + 1) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        if(strlen($i + 1) < 2)
            $j = "0" . ($i + 1);
        else
            $j = $i + 1;
        $select .= " <option value=\"$j\"$selected>" . $entidad . "</option>\n";
        $i++;
    }
    $select .= "</select>\n";
    return $select;
}
global $_anos;
$_anos = array();
for($i = 2006; $i > 1900; $i--)
    array_push($_anos, $i);

function select_ano($preselect_id = 0)
{
    if(($preselect_id > 12 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_anos;
    $select = "<select name=\"ano\">\n";
    if((0) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>año</option>\n";
    foreach($_anos as $entidad)
    {
        if($entidad == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"" . $entidad . "\"$selected>" . $entidad . "</option>\n";
        $i++;
    }
    $select .= "</select>\n";
    return $select;
}

global $_edo_civil;
$_edo_civil = array(
    "Otro", 
    "Soltero", 
    "Casado", 
    "Divorciado", 
    "Union Libre", 
    "Viudo");

function select_edo_civil($preselect_id = 0)
{
    if(($preselect_id > 4 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_edo_civil;
    $select = "<select name=\"edo_civil\">\n";
    if((0) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>Seleccione uno</option>\n";
    foreach($_edo_civil as $entidad)
    {
        if(($i + 1) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"" . ($i + 1) . "\"$selected>" . $entidad . "</option>\n";
        $i++;
    }
    $select .= "</select>\n";
    
    return $select;
}

function select_sexo($f = 1) //1 mujer, 0 hombre
{
    $select = "<select name=\"sexo\">\n";
    $select .= " <option value=\"0\"";
    if(!$f)
        $select .= " SELECTED";
    $select .= ">Masculino</option>\n";
    $select .= " <option value=\"1\"";
    if($f)
        $select .= " SELECTED";
    $select .= ">Femenino</option>\n";
    $select .= "</select>\n";
    return $select;
}

function select_sino($name, $si = 1)
{
    $select = "<select name=\"$name\" id=\"$name\">\n";
    if(!isset($si))
        $selected = " SELECTED";
    $select .= " <option value=\"-1\"$selected>Seleccione uno</option>\n";
    $select .= " <option value=\"0\"";
    if($si == 0 && $selected == "")
        $select .= " SELECTED";
    $select .= ">no</option>\n";
    $select .= " <option value=\"1\"";
    if($si == 1 && $selected == "")
        $select .= " SELECTED";
    $select .= ">si</option>\n";
    $select .= "</select>\n";
    return $select;
}

function select_medio_contacto($preselected)
{
    $select = "<select name=\"medio_contacto\" id=\"medio_contacto\">\n";
    if($preselected == "")
        $selected = " SELECTED";
    $select .= " <option value=\"0\"$selected>Seleccione uno</option>\n";
    $select .= " <option value=\"telefono\"";
    if($preselected == "telefono")
        $select .= " SELECTED";
    $select .= ">Teléfono</option>\n";
    $select .= " <option value=\"email\"";
    if($preselected == "email")
        $select .= " SELECTED";
    $select .= ">Correo Electronico</option>\n</select>";
    return $select;
}

function select_rate($name, $from, $to, $rate)
{
    $select = "<table border=0 cellspacing=0 cellpadding=0 width=80%>\n <tr>\n";
    for($i = $from; $i <= $to; $i++)
    {
        if($i == $rate)
            $checked = " checked=\"checked\"";
        else
            $checked = "";
        $select .= "  <td style=\"text-align: right;\">$i</td><td><input$checked name=\"$name\" value=\"$i\" type=\"radio\"></td>\n";
    }
    $select .= " <tr>\n</table>\n";
    return $select;
}

global $_confirmado;
$_confirmado = array(
    "Confirmado", 
    "Cancelado", 
    "No se localizó");

function select_confirmado($preselect_id = 0)
{
    if(($preselect_id > 4 && $preselect_id < 1) || !$preselect_id)
        $preselect_id = 0;
    global $_confirmado;
    $select = "<select name=\"confirmado\">\n";
    if((0) == $preselect_id)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"0\"$selected>Seleccione uno</option>\n";
    foreach($_confirmado as $entidad)
    {
        if(($i + 1) == $preselect_id)
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"" . ($i + 1) . "\"$selected>" . $entidad . "</option>\n";
        $i++;
    }
    $select .= "</select>\n";
    return $select;
}

function select_array($name, $array, $preselected = "")
{
    if(!is_array($array))
        return "";
    $select = "<select name=\"$name\" id=\"$name\">\n";
    if((0) == $preselected)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"\"$selected>Seleccione uno</option>\n";
    
    foreach($array as $option)
    {
        if(strtoupper($option) == strtoupper($preselected))
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"$option\"$selected>" . $option . "</option>\n";
    }
    $select .= "</select>\n";
    return $select;
}

function select_array_index($name, $array, $preselected = 0)
{
    if(!is_array($array))
        return "";
    $select = "<select name=\"$name\" id=\"$name\">\n";
    if((0) == $preselected)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"\"$selected>Seleccione uno</option>\n";
    
    foreach($array as $option)
    {
        if($option[0] == $preselected)
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"$option[0]\"$selected>" . $option[1] . "</option>\n";
    }
    $select .= "</select>\n";
    return $select;
}

function select_array_assoc($name, $array, $preselected = 0)
{
    if(!is_array($array))
        return "";
    $select = "<select name=\"$name\" id=\"$name\">\n";
    if((0) == $preselected)
        $selected = " SELECTED";
    else
        $selected = "";
    $select .= " <option value=\"\"$selected>Seleccione uno</option>\n";
    
    foreach($array as $key => $option)
    {
        if($key == $preselected)
            $selected = " SELECTED";
        else
            $selected = "";
        $select .= " <option value=\"$key\"$selected>" . $option . "</option>\n";
    }
    $select .= "</select>\n";
    return $select;
}
?>
