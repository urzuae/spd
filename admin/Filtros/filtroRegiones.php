<?php
if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die ("No puedes acceder directamente a este archivo...");
}
global $db, $changeRegion,$changeZonas,$changeEntidad;
$changeRegion = $_GET["changeRegion"];
$changeZonas = $_GET["changeZonas"];
$changeEntidad = $_GET["changeEntidades"];

if($changeRegion)
{
    $regionId = $_GET["regionId"];
    try{        
        die(json_encode(createTreeFromRegion($regionId,$db)));
    }
    catch(Exception $e)
    {
        die(json_encode(array("tree" => "","error" =>1)));
    }
}
if($changeZonas)
{
    try
    {
        $zonaId = $_GET["zonaId"];
        die(json_encode(createTreeFromZona($zonaId,$db)));
    }
    catch(Exceptio $e)
    {
        die(json_encode(array("tree" => "","error" =>1)));
    }
}
if($changeEntidad)
{
    $entidadId = $_GET["entidadId"];
    try{
        die(json_encode(createTreeFromEntidad($entidadId,$db)));
    }
    catch(Exception $e)
    {
        die(json_encode(array("tree" => "","error" =>1)));
    }
}
/*
 * Crea un array asociativo a partir del id de entidad
 * @param int  $regionId -> el id de la region
 * @return Array -> Arbol asociado de zonas entidades plazas
 */
function createTreeFromEntidad($entidadId,$db)
{
    $tree["error"] = 0;    
    $tree["plazas"] = getPlazas($entidadId,$db);
    return $tree;
}

/*
 * Crea un array asociativo a partir del id de zona
 * @param int  $regionId -> el id de la region
 * @return Array -> Arbol asociado de zonas entidades plazas
 */
function createTreeFromZona($zonaId,$db)
{
    $tree["error"] = 0;
    $tree["entidades"] = getEntidades($zonaId,$db);
    $tree["plazas"] = getPlazas($tree["entidades"][0][0],$db);
    return $tree;
}

/*
 * Crea un array asociativo a partir del id de region
 * @param int  $regionId -> el id de la region
 * @return Array -> Arbol asociado de zonas entidades plazas
 */
function createTreeFromRegion($regionId,$db)
{
    $tree["error"] = 0;
    $tree["zonas"] = getZonas($regionId,$db);
    $tree["entidades"] = getEntidades($tree["zonas"][0][0],$db);
    $tree["plazas"] = getPlazas($tree["entidades"][0][0],$db);
    return $tree;
}
/*
 * Obtiene las zonas en funcion del identificador de region
 * @param int -> id de region
 * @return Array -> Arreglo bidimendional con el nombre y el identificador de la zona
 */
function getZonas($regionId,$db)
{
    $listZonas = array();
    $sqlZonas = "select  distinct(zona_id), upper(nombre) from crm_zonas where region_id='$regionId'";
    $result =$db->sql_query($sqlZonas) or die("Error al obtener las zonas->".$sqlZonas);
    while(list($zonaId, $zonaName) = $db->sql_fetchrow($result))
    $listZonas[] = array($zonaId, rawurlencode($zonaName));
    return $listZonas;
}
/*
 * Obtiene las entidades en funcion del identificador de la zona
 * @param int zonaId ->identificador de la zona
 * @return Array -> Arreglo bidimencional con el nombre e identificador de la entidad
 */
function getEntidades($zonaId,$db)
{
    $listEntidades = array();
    $sqlEntidades = "select distinct(entidades.id_entidad), entidades.nombre from crm_entidades as entidades,
    crm_zonas_entidad as ze where entidades.id_entidad=ze.entidad_id and ze.zona_id='$zonaId'";    
    $result = $db->sql_query($sqlEntidades) or die("Error al obtener las entidades->".$sqlEntidades);
    while(list($entidadId,$entidadName) = $db->sql_fetchrow($result))
    $listEntidades[] = array($entidadId, rawurlencode( $entidadName));
    return $listEntidades;
}
/*
 * Obtiene las plazas en function del identifecador de entidad
 * @param int $idRegion: el identificador de region
 * @return Array -> arreglo bidimensional con el identificador y nombre de la plaza
 */
function getPlazas($entidadId,$db)
{
    $listPlazas = array();
    $sqlPlazas = "select distinct(plaza_id), nombre from crm_plazas where entidad_id='$entidadId'";
    $result = $db->sql_query($sqlPlazas) or die("Error al obtener las plazas->".$sqlPlazas);
    while(list($plazaId, $plazaName) = $db->sql_fetchrow($result))
    $listPlazas[] = array($plazaId, rawurlencode($plazaName));
    return $listPlazas;
}
?>
