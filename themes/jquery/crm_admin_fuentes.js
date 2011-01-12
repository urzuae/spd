
var urlRegistraOrigen ="index.php?_module=Catalogos&_op=inserta_origen";
$(document).ready(function(){
    $("#guardar_fuente").click(function(){
        if($("#padre_id").val()>0)
        {
            if($("#fuente").val()=='')
            {
                alert("Por favor teclee el nombre origen creado");
            }
            else
            {
                $.post(urlRegistraOrigen,{opc:1,fuente_id:$("#padre_id").val(),nm_fuente:$("#fuente").val()},function(data){
                    $("#resultado").html(data);
                    location.href = "index.php?_module=Catalogos";
                });
            }
        }
        else
        {
            alert("Por favor seleccione un origen ");
        }
    });

    $("#actualiza_fuente").click(function(){
        if($("#padre_id").val()>0)
        {
            if($("#fuente").val()=='')
            {
                alert("Por favor teclee el nombre del origen creado");
            }
            else
            {
                $.post(urlRegistraOrigen,{opc:5,fuente_id:$("#padre_id").val(),nm_fuente:$("#fuente").val()},function(data){
                    $("#resultado").html(data);
                    location.href = "index.php?_module=Catalogos";
                });
            }
        }
        else
        {
            alert("Por favor seleccione un origen ");
        }
    });

});

function bloquea_origen(id)
{
    if(id > 0)
    {
        $.post(urlRegistraOrigen,{opc:2,fuente_id:id},function(data){
            $("#resultado").html(data);
            location.href = "index.php?_module=Catalogos";
        });
    }
}
function desbloquea_origen(id)
{
    if(id > 0)
    {
        $.post(urlRegistraOrigen,{opc:3,fuente_id:id},function(data){
            $("#resultado").html(data);
            location.href = "index.php?_module=Catalogos";
        });
    }
}
function elimina_origen(id)
{
    if(id > 0)
    {
        if(confirm("¿Esta seguro de eliminar la fuente"))
        {
            $.post(urlRegistraOrigen,{opc:4,fuente_id:id},function(data){
               $("#resultado").html(data);
                location.href = "index.php?_module=Catalogos";
            });
        }
    }
}
