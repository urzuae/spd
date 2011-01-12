var urlVersionS="index.php?_module=Directorio&_op=VersionSeleccionada";
var urlTransmisionS="index.php?_module=Directorio&_op=TransmisionSeleccionada";
var urlFilter ="index.php?_module=Filtros&_op=filtroVehiculo";
var urlcreate ="index.php?_module=Directorio&_op=InsertVehiculo";
var urlupdate ="index.php?_module=Directorio&_op=ActualizaVehiculo";
var modelo_anterior=0;
var version_anterior=0;
var transmision_anterior=0;
var timestamp_anterior='';

$(document).ready(function(){    
    $('#datos').hide();

    $("#visualiza").click(function()
    {
        $('#datos').show();
    });

    $("#listVehicle").change(function(event){
        displayListVersionVehicle(event);
    });
    $("#listVersion").change(function(event){
        displayListTransmisionVehicle(event);
    });

    /** Guardamos el modelo en la bd**/
    $("#otro_auto").click(function(){
        if( ($("#listVehicle").val() > 0) )
        {
            $.get(urlcreate,{
                    contacto_id:$("#contacto_id").val(),
                    modelo_id:$("#listVehicle").val(),
                    version_id:$("#listVersion").val(),
                    transmision_id:$("#listTramsmision").val(),
                    ano_id:$("#ano_vehiculo").val(),
                    color_ext:$("#color_ext").val(),
                    color_int:$("#color_int").val(),
                    tipo_pint:$("#tipo_pint").val()
            },
            function(data){
                $("#listadoVehicles").html(data);
            });
        }
        else
        {
            alert("Favor de seleccionar un vehiculo");
        }
    });

    /*** actualizo el modelo **/
    $("#actualiza_auto").click(function(){
        if( ($("#listVehicle").val() > 0) )
        {
            $.get(urlupdate,{
                    contacto_id:$("#contacto_id").val(),
                    modelo_id:$("#listVehicle").val(),
                    version_id:$("#listVersion").val(),
                    transmision_id:$("#listTramsmision").val(),
                    ano_id:$("#ano_vehiculo").val(),
                    color_ext:$("#color_ext").val(),
                    color_int:$("#color_int").val(),
                    tipo_pint:$("#tipo_pint").val(),
                    modelo_anterior:modelo_anterior,
                    version_anterior:version_anterior,
                    transmision_anterior:transmision_anterior,
                    timestamp_anterior:timestamp_anterior
            },
            function(data){
                $("#listadoVehicles").html(data);
            });
        }
        else
        {
            alert("Favor de seleccionar un vehiculo");
        }

    });
});

/*
 * Lista las transmisiones de cada vehiculo
 */
function displayListTransmisionVehicle(event)
{
    var contentHtmlFilterTransmsion = "<td class='list'><select style='width: 200px;' name='listTramsmision' id='listTramsmision' style='width:150px;'><option value='0'>Ninguno</option>";
    $.getJSON(urlFilter,
    {
        transmision: 1,
        vehicleId : $("#listVehicle option:selected").val(),
        versionId : $("#listVersion option:selected").val()
    }, function(data){
        transmisions = data;
        if(transmisions.error == 0)
        {
            contentHtmlFilterTransmsion = contentHtmlFilterTransmsion + transmisions.transmisions + "</select></td>";
            $("#displayFilter tbody.filterVehicle tr.showTransmision td.list").remove();
            $("#displayFilter tbody.filterVehicle tr.showTransmision td").after(contentHtmlFilterTransmsion);
        }
    });
    event.preventDefault();
}

/*
 * Muestra las versiones de un vehiculo
 */
function displayListVersionVehicle(event)
{
    var contentHtmlFilterVersion = "<td class='list'><select style='width: 200px;' name='listVersion' id='listVersion' style='width:150px;'><option value='0'>Ninguno</option>";
    $.getJSON(urlFilter,
    {
        version : 1,
        vehicleId : $("#listVehicle option:selected").val()
    }, function(data){
        versions = data;
        if(versions.error == 0)
        {
                contentHtmlFilterVersion = contentHtmlFilterVersion + versions.versions + "</select></td>";
                $("#displayFilter tbody.filterVehicle tr.showVersion td.list").remove();
                $("#displayFilter tbody.filterVehicle tr.showTransmision td.list").remove();
                $("#displayFilter tbody.filterVehicle tr.showVersion td").after(contentHtmlFilterVersion);
                $("#listVersion").change(function(event){
                    displayListTransmisionVehicle(event);
                });
                return;
        }
        if(versions.error == 1)
            alert("Ha ocurrido un error al cargar las versiones del vehiculo");
        return;
    });
    event.preventDefault();
}

/*
 *  Funcion que elimina vehiculos de un prospecto
 */

function elimina_modelo(modelo,contacto_id,modelo_id,version_id,transmision_id,timestamp)
{
    urldelete ="index.php?_module=Directorio&_op=EliminaVehiculo";
    if(confirm("¿Desea eliminar el modelo: "+modelo))
    {
        $.get(urldelete,{contacto_id:contacto_id,
                         modelo_id:modelo_id,
                         version_id:version_id,
                         transmision_id:transmision_id,
                         timestamp:timestamp
                          },function(data){$("#listadoVehicles").html(data);});
    }
}
function actualiza_modelo(modelo,contacto_id,modelo_id,version_id,transmision_id,ano,tipo_pintura,color_exterior,color_interior,timestamp)
{
    modelo_anterior=modelo_id;
    version_anterior=version_id;
    transmision_anterior=transmision_id;
    timestamp_anterior=timestamp;
    $("#ano_vehiculo").val(ano);
    $("#color_ext").val(color_exterior);
    $("#color_int").val(color_interior);
    $("#tipo_pint").val(tipo_pintura);
    $("#listVehicle").val(modelo_id);
    $.get(urlVersionS,{modelo_id:modelo_id,version_id:version_id},function(data){$("#listVersion").html(data);});
    $.get(urlTransmisionS,{modelo_id:modelo_id,version_id:version_id,transmision_id:transmision_id},function(datat){$("#listTramsmision").html(datat);});
}


/*
 * Despliegue la lista de vehiculos en el filtrado por automovil
 */
$(function(){
    var contentHtmlFilterVehicle = "<td><select style='width: 200px;' name='listVehicle' id='listVehicle' style='width:150px;'><option value='0'></option>";
    $.getJSON(urlFilter,
    {
        uniteds : 1
    }, function(data){
        uniteds = data;
        if(uniteds.error == 0)
        {
            contentHtmlFilterVehicle = contentHtmlFilterVehicle + uniteds.uniteds + "</select></td>";
            $("#displayFilter tbody.filterVehicle tr.showUnited td.list").remove();
            $("#displayFilter tbody.filterVehicle tr.showUnited td").after(contentHtmlFilterVehicle);
            $("#listVehicle").change(function(event){
                displayListVersionVehicle(event)
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
});