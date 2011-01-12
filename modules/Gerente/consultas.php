<?php
if (!defined('_IN_MAIN_INDEX'))
{
    die ("No puedes acceder directamente a este archivo...");
}
global $db,$_includesdir,$nombre,$apaterno,$amaterno,$evento_id,$fase_id,$unidad_id,$tipo_pago_id,$tmp_gid,$tmp_uid,$fecha_ini,$fecha_fin,$submit;
$titulo="Busqueda de Prospectos";
$encabezado="No se encontraron registros con el filtro seleccionado";
include_once("funciones.php");
$combo_eventos=Genera_Combo_Eventos($db,$evento_id);
$combo_fases=Genera_Combo_Fases($db,$fase_id);
$combo_modelos=Genera_Combo_Unidades($db,$unidad_id);
$combo_pagos=Genera_Combo_Tipo_Pago($db,$tipo_pago_id);
$combo_concesionarias=Genera_combo_Concesionarias($db,$tmp_gid);
$combo_vendedores=Genera_combo_Vendedores($db,$tmp_uid);
if(isset($submit))
{
    include_once("filtro_busquedas.php");
    if($filtro != '')   $filtro= "AND ".$filtro;

    $sql="SELECT a.contacto_id,concat(a.nombre,' ',a.apellido_paterno,' ',apellido_materno) AS nombre,
          b.evento_id,b.fase_id,b.gid,b.uid,b.unidad_id,b.tipo_pago_id,b.timestmamp
          FROM mfll_contactos AS a LEFT JOIN mfll_contactos_unidades AS b ON a.contacto_id=b.contacto_id
          WHERE 1 ".$filtro." ORDER BY b.evento_id,b.fase_id,b.tipo_pago_id,a.nombre,a.apellido_paterno,a.apellido_materno;";

    $res=$db->sql_query($sql) or die("Error en la consulta:  ".$sql);
    $tuplas=$db->sql_numrows($res);
    if($tuplas > 0)
    {
        $array_eventos=Regresa_Eventos($db);
        $array_fases=Regresa_Fases($db);
        $array_unidades=Regresa_Unidades($db);
        $array_tipo_pago=Regresa_Tipos_Pagos($db);
        $array_gids=Regresa_Concesionarias($db);
        $array_uids=Regresa_Vendedores($db);
        $encabezado="Listado de prospectos por filtro seleccionado";
        $buffer="<table align='center' class='tablesorter' width='100%'>
                 <thead><tr>
                <th width='12%'>Evento</th>
                <th width='8%'>Fase</th>
                <th width='20%'>Nombre</th>
                <th width='9%'>Unidad</th>
                <th width='7%'>Tipo Pago</th>
                <th width='15%'>Concesionaria</th>
                <th width='13%'>Vendedor</th>
                <th width='9%'>Ingreso</th></tr></thead><tbody>";
        while(list($contacto_id,$nombre,$evento_id,$fase_id,$xgid,$xuid,$unidad_id,$tipo_pago_id,$timestamp) = $db->sql_fetchrow($res))
        {
            $buffer.="<tr class=\"row".($class_row++%2?"2":"1")."\" style=\"cursor:pointer;height:30px;\" >
                        <td>".$array_eventos[$evento_id]."</td>
                        <td>".$array_fases[$fase_id]."</td>
                        <td>".$nombre."</td>
                        <td>".$array_unidades[$unidad_id]."</td>
                        <td>".$array_tipo_pago[$tipo_pago_id]."</td>
                        <td>".$array_gids[$xgid]."</td>
                        <td>".$array_uids[$xuid]."</td>
                        <td>".$timestamp."</td>
                    </tr>";
        }
        $buffer.="</tbody><thead><tr><td colspan='8'>Total de Prospectos: ".$tuplas."</td></tr></thead></table>";

    }
}
?>