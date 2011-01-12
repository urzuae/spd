/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var urlFilter ="index.php?_module=Filtros&_op=filtroRegiones";
$("document").ready(function(){
    listeningList();
});

/*
 * Detecta un cambio en algunos de la lista geografica (region,zona,entidad,plaza)
 */
function listeningList()
{
    $("#listRegiones").change(listeningRegions);
    $("#listZonas").change(listeningZonas);
    $("#listEntidades").change(listeningEntiades);
}
/*
 * Si ha cambiado el select de entidades
 */
function listeningEntiades()
{
    $.getJSON(urlFilter, 
    {
        changeEntidades:1,
        entidadId:  $("#listEntidades option:selected").val()
    }, function(data){
        tree = data;
        if(tree.error == 0)
        {
            $("#listPlazas option").remove();
            $.each(tree.plazas, function(index,item)
            {
                var option= "<option value='"+ tree.plazas[index][0] + "'> " + unescape(tree.plazas[index][1]) + "</option>";
                $("#listPlazas").append(option);
            });
        }
    });
}
/*
 * Si ha cambiado el select de zonas
 */
function listeningZonas()
{
    var i = 0, lengthTreeEntidades = 0, lengthTreePlazas = 0;
    $.getJSON(urlFilter,
    {
        changeZonas:1,
        zonaId: $("#listZonas option:selected").val()
    }, function(data){
        tree = data;
        if(tree.error == 0)
        {
            //cargar lista de entidades
            $("#listEntidades option").remove();
            $.each(tree.entidades, function(index,item)
            {
                var option= "<option value='"+ tree.entidades[index][0] + "'> " + unescape(tree.entidades[index][1]) + "</option>";
                $("#listEntidades").append(option);
            });

            $("#listPlazas option").remove();
            $.each(tree.plazas, function(index,item)
            {
                var option= "<option value='"+ tree.plazas[index][0] + "'> " + unescape(tree.plazas[index][1]) + "</option>";
                $("#listPlazas").append(option);
            });
        }});
}
/*
 * Si la lista de regione ha cambiado
 */
function listeningRegions()
{
    $.getJSON(urlFilter,
    {
        changeRegion:1,        
        regionId: $("#listRegiones option:selected").val()
    }, function(data)
    {
        tree = data;
        if(tree.error == 0)
        {
            // remover y añadir zonas
            $("#listZonas option").remove();
            $.each(tree.zonas, function(index, item)
            {
                var option = "<option value='"+ tree.zonas[index][0] + "'> " + unescape(tree.zonas[index][1]) + "</option>";
                $("#listZonas").append(option);
            });
                
            // remover  y añadir entidades
            $("#listEntidades option").remove();
            $.each(tree.entidades, function(index,item)
            {
                var option= "<option value='"+ tree.entidades[index][0] + "'> " + unescape(tree.entidades[index][1]) + "</option>";
                $("#listEntidades").append(option);
            });

            //remover y añadir plazas
            $("#listPlazas option").remove();
            $.each(tree.plazas, function(index,item)
            {
                var option= "<option value='"+ tree.plazas[index][0] + "'> " + unescape(tree.plazas[index][1]) + "</option>";
                $("#listPlazas").append(option);                
            });
        }
    }
    )};