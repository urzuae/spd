<?php
/* Crea el filtro por vehiculo*/
global $db;

$filtroPorVehiculo = array();
$aliasProspectosUnidades = "m";
$idVehiculo = $_REQUEST["listVehicle"];
$idVersion = $_REQUEST["listVersion"];
$idTransmision = $_REQUEST["listTransmision"];
if($idVehiculo)//si se ha proporcionado un vehiculo
{
    $leyenda_filtros.="<tr><td>Vehiculo</td><td>".getNameVehicle($db, $idVehiculo)."</td></tr>";
    $filtroPorVehiculo[] = "$aliasProspectosUnidades.modelo_id='$idVehiculo'";
}
if($idVersion)
{
    $leyenda_filtros.="<tr><td>Version</td><td>".getNameVersion($db, $idVersion)."</td></tr>";
    $filtroPorVehiculo[] = "$aliasProspectosUnidades.version_id='$idVersion'";
}
if($idTransmision)
{
    $leyenda_filtros.="<tr><td>Transmision</td><td>".getNameTransmision($db, $idTransmision)."</td></tr>";
    $filtroPorVehiculo[] = "$aliasProspectosUnidades.transmision_id='$idTransmision'";
}
$url .= "&listVehicle=$idVehiculo&listVersion=$idVersion&listTransmision=$idTransmision";

//obtiene el nombre del vehiculo
function getNameVehicle($db,$idVehiculo)
{
    $sql ="select nombre from crm_unidades where unidad_id='$idVehiculo'";
    $result = $db->sql_query($sql) or die("Erro al obtener el nombre del vehiculo->".$sql);
    list($nombreVehiculo) = $db->sql_fetchrow($result);
    return$nombreVehiculo;
}
//obtiene el nombre de la version
function getNameVersion($db, $versionId)
{
    $sql = "select nombre from crm_versiones where version_id='$versionId'";
    $result = $db->sql_query($sql) or die("Error al obtener el nombre de la version->".$sql);
    list($nombreVersion) = $db->sql_fetchrow($result);
    return $nombreVersion;
}
//obtiene el nombre de la transmision
function getNameTransmision($db, $transmisionId)
{
    $sql = "select nombre from crm_transmisiones where transmision_id='$transmisionId'";
    $result = $db->sql_query($sql) or die("Error al obtener el nombre de la transmision");
    list($nombreTransmision) = $db->sql_fetchrow($result);
    return $nombreTransmision;
}

?>