/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/*var urlFilter = "Filtros/combos.php";
var urlFilterFuentes="Filtros/filtroFuentes.php";
var urlFilterVehiculos = "Filtros/filtroVehiculo.php";*/

var urlFilter ="index.php?_module=Filtros&_op=combos";
var urlFilterFuentes ="index.php?_module=Filtros&_op=filtroFuentes";
var urlFilterVehiculos  ="index.php?_module=Filtros&_op=filtroVehiculo";

$(document).ready(function(){
    $('#hijo_id_1').hide();
    $('#hijo_id_2').hide();
    $('#hijo_id_3').hide();
    $('#hijo_id_4').hide();
    $("select").change(function()
    {
        var combos = new Array();
        combos['grupo_empresarial_id'] = "region_id";
        combos['region_id'] = "zona_id";
        combos['zona_id'] = "entidad_id";
        combos['entidad_id'] = "plaza_id";
        combos['plaza_id'] = "concesionaria";
        posicion = $(this).attr("name");
        valor = $(this).val()
        if(posicion == 'grupo_empresarial_id' && valor>0)
        {
            $("#ubicacion").hide()
            $("#zona_id").html('<option value="0" selected="selected">Selecciona Zona</option>')
            $("#entidad_id").html('<option value="0" selected="selected">Selecciona Entidad</option>')
            $("#plaza_id").html('<option value="0" selected="selected">Selecciona Plaza</option>')
            $("#concesionaria").html('<option value="0" selected="selected">Selecciona Concesionaria</option>')
        }
        else
        {
            $("#ubicacion").show()
            if(posicion == 'region_id' && valor==0)
            {
                $("#zona_id").html('<option value="0" selected="selected">Selecciona Zona</option>')
                $("#entidad_id").html('<option value="0" selected="selected">Selecciona Entidad</option>')
                $("#plaza_id").html('<option value="0" selected="selected">Selecciona Plaza</option>')
                $("#concesionaria").html('<option value="0" selected="selected">Selecciona Concesionaria</option>')
            }
            else
            {
                $("#"+combos[posicion]).html('<option selected="selected" value="0">Cargando...</option>')
                if(valor > 0 || posicion !='nombre')
                {
                    $.get(urlFilter,{
                        combo:$(this).attr("name"),
                        id:$(this).val(),
                        id_zona:$('#zona_id').val()
                    },function(data){
                        $("#"+combos[posicion]).html(data);
                    })
                }
            }
        }
    })

    $("#padre_id").change(function(event){
        if($("#padre_id").val() > 0)
        {
            displayListChilds(event,'padre_id','hijo_id_1');
        }
        else
        {
            valor=0;
            $('#hijo_id_1').hide();
            $('#hijo_id_2').hide();
            $('#hijo_id_3').hide();
            $('#hijo_id_4').hide();
            $("#origen").val(valor);
        }
    });

    $("#hijo_id_1").change(function(event){
        if($("#hijo_id_1").val() != 0){
            displayListChilds(event,'hijo_id_1','hijo_id_2');
        }
    });

    $("#hijo_id_2").change(function(event){
        if($("#hijo_id_2").val() != 0){
            displayListChilds(event,'hijo_id_2','hijo_id_3');
        }
    });

    $("#hijo_id_3").change(function(event){
        if($("#hijo_id_3").val() != 0){
            displayListChilds(event,'hijo_id_3','hijo_id_4');
        }
    });


    function displayListChilds(event,div_padre,div_hijo)
    {
        var valoractual=$('#'+div_padre).val()
        if($('#'+div_padre).val() != 0)
        {
            $.get(urlFilterFuentes,{
                id:$('#'+div_padre).val()
            },function(data){
                if(data.length>0)
                {
                    $("#"+div_hijo).html(data);
                    $("#"+div_hijo).show();
                }
                else
                {
                    $("#origen").val(valoractual);
                }
            })
        }
    }

    $("#basico").click(function(){
        window.document.frmFechas.basico.value=0;
        if(window.document.frmFechas.basico.checked)
            window.document.frmFechas.basico.value=1;
    })
    $("#medio").click(function(){
        window.document.frmFechas.medio.value=0;
        if(window.document.frmFechas.medio.checked)
            window.document.frmFechas.medio.value=2;
    })
    $("#avanzado").click(function(){
        window.document.frmFechas.avanzado.value=0;
        if(window.document.frmFechas.avanzado.checked)
            window.document.frmFechas.avanzado.value=3;
    })

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
            $("#displayFilter tbody.filterVehicle tr.showTransmision td").after(contentHtmlFilterTransmsion);
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
            $("#displayFilter tbody.filterVehicle tr.showVersion td").after(contentHtmlFilterVersion);
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
            $("#displayFilter tbody.filterVehicle tr.showUnited td").after(contentHtmlFilterVehicle);
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
