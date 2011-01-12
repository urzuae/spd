/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var urlFilterVehiculos ="index.php?_module=Directorio&_op=filtroVehiculo";
$(document).ready(function(){

    $("#listVehicle").change(function(){
        displayListVersionVehicle();
    });
    $("#listVersion").change(function(){
        displayListTransmisionVehicle();
    });
})


/*
 * Lista las transmisiones de cada vehiculo
 */
function displayListTransmisionVehicle()
{
    var contentHtmlFilterTransmsion = "<td class='list'><select style='width: 200px;' name='listTransmision' id='listTramsmision' style='width:150px;'><option value='0'></option>";
    var vehiculoId = $("#listVehicle option:selected").val();
    var version_id = $("#listVersion option:selected").val();
    if(vehiculoId == null)
        vehiculoId = $("#idVehiculo").val();
    if(version_id == null || version_id == 0)
        version_id = $("#idVersion").val();    
    $.getJSON(urlFilterVehiculos,
    {
        transmision: 1,
        vehicleId : vehiculoId,
        versionId : version_id,
        selectedId :$("#idTransmision").val()
    }, function(data){
        transmisions = data;
        if(transmisions.error == 0)
        {
            contentHtmlFilterTransmsion = contentHtmlFilterTransmsion + transmisions.transmisions + "</select></td>";
            $("#displayFilter tbody.filterVehicle tr.showTransmision td.list").remove();
            $("#displayFilter tbody.filterVehicle tr.showTransmision td.addBefore").before(contentHtmlFilterTransmsion);
        }
    });
//event.preventDefault();    
}

/*
 * Muestra las versiones de un vehiculo
 */
function displayListVersionVehicle()
{
    var contentHtmlFilterVersion = "<td class='list'><select style='width: 200px;' name='listVersion' id='listVersion' style='width:150px;'><option value='0'></option>";        
    var idVehiculo = $("#listVehicle option:selected").val();    
    if(idVehiculo == null)
        idVehiculo = $("#idVehiculo").val();

    $.getJSON(urlFilterVehiculos,
    {
        version : 1,
        vehicleId : idVehiculo,
        selectedId :$("#idVersion").val()
    }, function(data){
        versions = data;
        if(versions.error == 0)
        {
            contentHtmlFilterVersion = contentHtmlFilterVersion + versions.versions + "</select></td>";
            $("#displayFilter tbody.filterVehicle tr.showVersion td.list").remove();
            $("#displayFilter tbody.filterVehicle tr.showTransmision td.list").remove();            
            $("#displayFilter tbody.filterVehicle tr.showVersion td.addBefore").before(contentHtmlFilterVersion)
            $("#listVersion").change(function(){
                displayListTransmisionVehicle();
            });
            return;
        }
        if(versions.error == 1)
            alert("Ha ocurrido un error al cargar las versiones del vehiculo");
        return;
    });
//event.preventDefault();
    displayListTransmisionVehicle();
}

/*
 * Despliegue la lista de vehiculos en el filtrado por automovil
 */
$(function(){
    var contentHtmlFilterVehicle = "<td><select style='width: 200px;' name='listVehicle' id='listVehicle' style='width:150px;'><option value='0'></option>";    
    
    $.getJSON(urlFilterVehiculos,
    {
        uniteds : 1,
        selectedId : $("#idVehiculo").val()
    }, function(data){
        uniteds = data;
        if(uniteds.error == 0)
        {
            contentHtmlFilterVehicle = contentHtmlFilterVehicle + uniteds.uniteds + "</select></td>";
            $("#displayFilter tbody.filterVehicle tr.showUnited td.list").remove();
            $("#displayFilter tbody.filterVehicle tr.showUnited td.addBefore").before(contentHtmlFilterVehicle);
            $("#listVehicle").change(function(){
                displayListVersionVehicle()
            });
            return;
        }
        if(uniteds.error == 1)
        {
            alert("Error al obtener el la lista de vehiculos");
            return
        }
        else
            alert("Se ha producido un error al obterner las lista de vehiculos");
    });
    if($("#idVersion").val().length > 0)
    {
        displayListVersionVehicle();
    }
});
