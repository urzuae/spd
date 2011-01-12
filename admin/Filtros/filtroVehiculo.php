<?php

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}

global $db, $united, $version, $transmision;
$united = $_GET["uniteds"];
$version = $_GET["version"];
$transmision = $_GET["transmision"];
if($united)
{
    $selectedId = $_GET["selectedId"];
    try{
        die(json_encode(getUniteds($db,$selectedId)));
    }
    catch(Exception $e)
    {
        die(json_encode(array("uniteds" => "","error" =>1)));
    }
}

if($version)
{
    $vehicleId = $_GET["vehicleId"];
    $selectedId = $_GET["selectedId"];
    try
    {
        die(json_encode(getVersions($db,$vehicleId,$selectedId)));
    }
    catch(Exception $e)
    {
        die(json_encode(array("versions" => "","error" =>1)));
    }
}
if($transmision)
{
    try
    {
        $vehicleId =  $_GET["vehicleId"];
        $versionId = $_GET["versionId"];
        $selectedId = $_GET["selectedId"];
        die(json_encode(getTransmisions($db,$vehicleId, $versionId,$selectedId)));
    }
    catch(Exception $e)
    {
        die(json_encode(array("transmisions" =>0, "erro" => 1)));
    }
}

/*
 * Obtiene la lista de transmisiones de una version en especifico de un vehiculo
 * @param int $vehicleId -> id del automovil
 * @param int $versionId -> id de la version del automovil
 * @return String -> lista de las transmisiones asociadas a la version del automovil
 */
function getTransmisions($db,$vehicleId, $versionId, $selectedId = 0)
{
    $select = "";
    $sql= "SELECT DISTINCT (e.transmision_id), e.nombre
            FROM crm_unidades AS a, crm_vehiculo_versiones AS b,
                 crm_versiones AS c, crm_version_transmisiones AS d, crm_transmisiones AS e
            WHERE a.unidad_id = b.vehiculo_id
            AND b.version_id = c.version_id
            AND c.version_id = d.version_id
            AND d.transmision_id = e.transmision_id
            AND a.unidad_id = '$vehicleId'
            AND b.version_id = '$versionId'
            ORDER BY e.transmision_id;";
    $transmitions = array("transmisions" =>0, "nombre" => 0, "error"=>0);

    $result=$db->sql_query($sql);
    if($db->sql_numrows($result) > 0)
    {
        while(list($transmisionId, $nombre) = $db->sql_fetchrow($result))
        {
            $selected = "";
            if($selectedId == $transmisionId)
                $selected = "selected='selected'";
            $select .= "<option value='$transmisionId' $selected>$nombre</option>";
        }
        $transmitions =  array("transmisions" => $select , "error" => 0);
    }
    return $transmitions;
}
/*
 * Obtiene la lista de verisones de un vehiculo en funcio del id del automovil
 * @param int $vehicleId -> el id del automovil
 * @return String $listVersion -> lista de veriones de un automovi
 */
function getVersions($db,$vehicleId,$selectedId=0)
{
    $select = "";
    $versions = array("versions" =>0, "nombre" => 0, "error" => 0);
    $sql = "select version.version_id, version.nombre from crm_versiones as version,
            crm_vehiculo_versiones as vv, crm_unidades as vehiculo where
            vehiculo.unidad_id=vv.vehiculo_id and vv.version_id=version.version_id
            and vehiculo.unidad_id='$vehicleId'";
    $result=$db->sql_query($sql);
    if($db->sql_numrows($result) > 0)
    {
        while(list($version_id,$nombre) = $db->sql_fetchrow($result))
        {
            $selected = "";
            if($selectedId == $version_id)
            $selected = "selected='selected'";
            $select .= "<option value='$version_id' $selected>$nombre</option>";
        }
        $versions =  array("versions" => $select , "error" => 0);
    }

    return $versions;
}
/*
 * Obtiene los vehiculos del catalogo
 * @param $vehiculo_id  -> el id del vehiculo
 * @retum array -> vector con los vehiculos del catalogo
 */
function getUniteds($db,$selectedId=0)
{    
    $uniteds = array("uniteds" =>0, "nombre" => 0, "error" => 0);
    $sql = "select unidad_id, nombre from crm_unidades order by nombre asc";
    $select = "";
    $result=$db->sql_query($sql);
    if($db->sql_numrows($result) > 0)
    {
        while(list($vehiculo_id, $nombre) = $db->sql_fetchrow($result))
        {
            $selected = "";
            if($selectedId == $vehiculo_id)
            $selected = "selected='selected'";
            $select .= "<option value='$vehiculo_id' $selected>$nombre</option>";
        }
        $uniteds =  array("uniteds" => $select , "error" => 0);
    }
    return $uniteds;
}
?>
