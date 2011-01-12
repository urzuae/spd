<?

if (!defined('_IN_ADMIN_MAIN_INDEX')) {
    die("No puedes acceder directamente a este archivo...");
}

global $db, $gid, $submit, $listPlazas;
$warning = "";
$leyend = "";
$style = "style='width:250px;'";
if ($submit)
{
    $sqlGetConcesionaria = "select gid, plaza_id from crm_plazas_concesionarias where gid='$gid'";
    $result = $db->sql_query($sqlGetConcesionaria) or die("Error al comprobar si existe la distribuidora->" . $sqlGetConcesionaria);
    if ($db->sql_numrows($result))
    {
        $sqlUpdatePlazasConcesionarias = "update crm_plazas_concesionarias set plaza_id='$listPlazas' where gid='$gid'";
        $db->sql_query($sqlUpdatePlazasConcesionarias) or die("Error al actualizar la relacion plaza-distribuidora->" . $sqlUpdatePlazasConcesionarias);
    }else
    {
        $sqlInserPlazaConcesionaria = "insert into crm_plazas_concesionarias values('$gid','$listPlazas')";
        $db->sql_query($sqlInserPlazaConcesionaria) or die("Error al insertar registro en la relacion  plaza-distribuidora->" . $sqlInserPlazaConcesionaria);
    }
    $updateGroupsUbications = "UPDATE groups_ubications SET region_id=" . $_GET['listRegiones'] . ",zona_id=" . $_GET['listZonas'] . ", entidad_id=" . $_GET['listEntidades'] . ", plaza_id=" . $_GET['listPlazas'] . " WHERE gid=" . $gid . ";";
    $db->sql_query($updateGroupsUbications) or die("Error al actualizar la tabla de ubicaciones de distribuidoras" . $updateGroupsUbications);
    $leyend = "Datos actualizados exitosamente";
}

/*
 * Obtener datos de la concesionaria
 */
$sqlConcesionarias = "select b.gid, b.name,a.region_id,a.zona_id,a.entidad_id,a.plaza_id from groups as b left join groups_ubications as a
                      ON b.gid=a.gid where b.gid='$gid'";
$resultConcesionarias = $db->sql_query($sqlConcesionarias) or die("Error al obtener la lista de Distribuidoras->" . $sqlConcesionarias);
if($db->sql_numrows() >0)
{
    list($id, $nameConcesionaria,$region_id,$zona_id,$entidad_id,$plaza_id) = $db->sql_fetchrow($resultConcesionarias);
    $nombreConcesionaria = $nameConcesionaria;

    #Regiones
    $sqlRegiones = "select region_id, nombre from crm_regiones";
    $resultRegiones = $db->sql_query($sqlRegiones) or die("Error al obtener las regiones");
    $listRegiones = "<select name='listRegiones' $style id='listRegiones'>";
    while (list($id, $nombre) = $db->sql_fetchrow($resultRegiones))
    {
        $selected = "";
        if ($id == $region_id)
            $selected = "selected='selected'";
        $listRegiones .="<option value='$id' $selected>".strtoupper($nombre)."</option>";
    }
    $listRegiones .="</select>";

    #ZONAS
    $sqlZonas = "select distinct(zona_id), nombre from crm_zonas WHERE region_id=".$region_id.";";
    $resultZonas = $db->sql_query($sqlZonas) or die("Error al obtener las zonas");
    $listZonas = "<select name='listZonas' $style id='listZonas'>";
    while (list($id, $nombre) = $db->sql_fetchrow($resultZonas))
    {
        $selected = "";
        if ($id == $zona_id)
            $selected = "selected='selected'";
        $listZonas.= "<option value='$id' $selected>".strtoupper($nombre)."</option>";

    }
    $listZonas .= "</select>";


    #Entidad
    $sqlEntidades = "select id_entidad,nombre from crm_entidades;";
    $resultEntidades = $db->sql_query($sqlEntidades);
    $listEntidades .= "<select name='listEntidades' $style id='listEntidades'>";
    while (list($id, $nombre) = $db->sql_fetchrow($resultEntidades))
    {
        $selected = "";
        if ($id == $entidad_id)
            $selected = "selected='selected'";
        $listEntidades .= "<option value='$id' $selected>".strtoupper($nombre)."</option>";
    }
    $listEntidades.= "</select>";


    $sqlPlazas = "select distinct(plaza_id), nombre from crm_plazas WHERE entidad_id=".$entidad_id.";";
    $resultPlazas = $db->sql_query($sqlPlazas);
    $listPlazas = "<select name='listPlazas' $style id='listPlazas'>";
    while (list($id, $nombre) = $db->sql_fetchrow($resultPlazas))
    {
        $selected = "";
        if ($id == $plaza_id)
            $selected = "selected='selected'";
        $listPlazas .= "<option value='$id' $selected>".strtoupper($nombre)."</option>";
    }
    $listPlazas.= "</select>";

}
else
{
    $warning = "No se han encontrado datos geograficos asociados con el grupo.
    Por favor eliga de las opciones mostrados arriba para actualizar el grupo";
}
?>