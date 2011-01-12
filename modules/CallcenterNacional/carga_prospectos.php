<?php
global $db,$_includesdir,$_site_title;
require_once "$_includesdir/paginacion.php";

$_site_title = "Carga de prospectos";
$style1 = "row1";
$style2 = "row2";
$link_nombre_sort = $_REQUEST["link_nombre_sort"];
$link_pa_sort = $_REQUEST["link_pa_sort"];
$link_sa_sort = $_REQUEST["link_sa_sort"];
$link_fecha = $_REQUEST["link_fecha"];

if($link_nombre_sort != "")
    $sort = "nombre $link_nombre_sort";
elseif($link_pa_sort != "")
    $sort = "apellido_paterno $link_pa_sort";
elseif($link_sa_sort != "")
    $sort = "apellido_materno $link_sa_sort";
else
    $sort = "fecha_alta $link_fecha";

if($_REQUEST["fecha_ini"] != "" and $_REQUEST["fecha_fin"] != "")
{
    $fecha_ini = $_REQUEST["fecha_ini"];
    $fecha_fin = $_REQUEST["fecha_fin"];
    $sql = "SELECT nombre, apellido_paterno, apellido_materno, fecha_alta
    FROM crm_contactos_no_asignados where date_format(fecha_alta,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'
    union SELECT nombre, apellido_paterno, apellido_materno, fecha_alta
    FROM crm_contactos_no_asignados_finalizados where date_format(fecha_alta,'%Y-%m-%d') between '".$_REQUEST["fecha_ini"]."' and '".$_REQUEST["fecha_fin"]."'
    order by $sort ";
}
elseif($_REQUEST["fecha_ini"] != "")
{
    $sql = "SELECT nombre, apellido_paterno, apellido_materno, fecha_alta
    FROM crm_contactos_no_asignados where date_format(fecha_alta,'%Y-%m-%d') >= '".$_REQUEST["fecha_ini"]."'
    union select  nombre, apellido_paterno, apellido_materno, fecha_alta
    from crm_contactos_no_asignados_finalizados where date_format(fecha_alta,'%Y-%m-%d') >= '".$_REQUEST["fecha_ini"]."'
    order by $sort";
    $fecha_ini = $_REQUEST["fecha_ini"];
}
elseif($_REQUEST["fecha_fin"] != "")
{
    $sql = "SELECT nombre, apellido_paterno, apellido_materno, fecha_alta
    FROM crm_contactos_no_asignados where date_format(fecha_alta,'%Y-%m-%d') <= '".$_REQUEST["fecha_fin"]."'
    union select  nombre, apellido_paterno, apellido_materno, fecha_alta
    from crm_contactos_no_asignados_finalizados where date_format(fecha_alta,'%Y-%m-%d') <= '".$_REQUEST["fecha_fin"]."' 
    order by $sort";
    $fecha_fin = $_REQUEST["fecha_fin"];
}
else
{
    $sql = "SELECT nombre, apellido_paterno, apellido_materno, fecha_alta
    FROM crm_contactos_no_asignados 
    union SELECT nombre, apellido_paterno, apellido_materno, fecha_alta
    FROM crm_contactos_no_asignados_finalizados order by $sort ";
}

$cs = $db->sql_query($sql);
$cant = $db->sql_affectedrows($cs);

$paginas = new paginacion($cant,1000,"$_includesdir");
$html_paginas = $paginas->imprimir_paginas();
$sql .= " ".$paginas->sql_limit();
$cs = $db->sql_query($sql);


//ORDENAR
//******************************************************************************

//NOMBRE
if($link_nombre_sort == "asc")
    $link_nombre_sort = "desc";
else
    $link_nombre_sort = "asc";
$link_nombre = "index.php?_module=CallcenterNacional&_op=carga_prospectos&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&link_nombre_sort=$link_nombre_sort";

//PRIMER APELLIDO
if($link_pa_sort == "asc")
    $link_pa_sort = "desc";
else
    $link_pa_sort = "asc";
$link_pa = "index.php?_module=CallcenterNacional&_op=carga_prospectos&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&link_pa_sort=$link_pa_sort";

//SEGUNDO APELLIDO
if($link_sa_sort == "asc")
    $link_sa_sort = "desc";
else
    $link_sa_sort = "asc";
$link_sa = "index.php?_module=CallcenterNacional&_op=carga_prospectos&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&link_sa_sort=$link_sa_sort";

//FECHA
if($link_fecha == "asc")
    $link_fecha = "desc";
else
    $link_fecha = "asc";
$link_fecha = "index.php?_module=CallcenterNacional&_op=carga_prospectos&fecha_ini=$fecha_ini&fecha_fin=$fecha_fin&link_fecha=$link_fecha";

//******************************************************************************

while(list($nombre, $apellido_paterno, $apellido_materno, $fecha_importado) = $db->sql_fetchrow($cs))
{
    $fecha_importado = explode(" ",$fecha_importado);
    $fecha = $fecha_importado[0];
    $hora = $fecha_importado[1];
    $fecha = explode("-",$fecha);
    $fecha_importado = "$fecha[2]-$fecha[1]-$fecha[0] $hora";

    if($style == $style1)
        $style = $style2;
    else
        $style = $style1;
    $_listado_prospectos .= "<tr><td class=\"$style\">$nombre</td><td class=\"$style\">$apellido_paterno</td><td class=\"$style\">$apellido_materno</td><td class=\"$style\">$fecha_importado</td>\n";
    $_csv .= "\"$nombre\",\"$apellido_paterno\",\"$apellido_materno\",\"$fecha_importado\"\n";
}

if($_REQUEST["excel"]){
    header('Content-type: text/csv');
    header('Content-Disposition: attachment; filename="carga_prospectos_'.date("d-m-Y").'.csv"');
    die($_csv);
}

?>
