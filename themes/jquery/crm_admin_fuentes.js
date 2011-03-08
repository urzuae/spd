var urlRegistraOrigen ="index.php?_module=Catalogos&_op=inserta_origen";
var site_name;
$(document).ready(function(){
    $("#guardar_fuente").click(function(){
        site_name=$("#site_name").val();
        if($("#origen").val()>0)
        {
            if($("#fuente").val()=='')
            {
                alert("Por favor teclear el nombre del origen");
            }
            else
            {
                if(confirm("¿Desea dar de alta el origen?"))
                {
                    $.post(urlRegistraOrigen,{opc:1,fuente_id:$("#origen").val(),nm_fuente:$("#fuente").val()},function(data){
                        $("#resultado").html(data);
                        $("#fuente").val('');
                        $.gritter.add({
                            title:site_name,
                            text: data,
                            image:'http://www.pcsmexico.com/salesfunnel/'+site_name+'/img/logo/'+site_name+'.png',                            
                            sticky: false,
                            time: '10000'
                        });                    
                        sleep(1000);
                        location.href = "index.php?_module=Catalogos&_op=find";
                    });
                }
            }
        }
        else
        {
            alert("Por favor seleccione un origen ");
        }
    });

    /*$("#actualiza_fuente").click(function(){
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
                    location.href = "index.php?_module=Catalogos&_op=find";
                });
            }
        }
        else
        {
            alert("Por favor seleccione un origen ");
        }
    });*/

});

function sleep(millisegundos)
{
    var inicio = new Date().getTime();
    while ((new Date().getTime() - inicio) < millisegundos)
    {
    }
}
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