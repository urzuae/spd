
var urlRegistraProducto ="index.php?_module=Modelos&_op=admin_producto";
$(document).ready(function(){
    $("#marcar").click(function(){
        $('input').each(function(i, item){
            $(item).attr('checked', true);
        });
    });
    $("#desmarcar").click(function(){
            $('input').each(function(i, item){
            $(item).attr('checked', false);
        });
    });
    $("#asignar").click(function(){
        cadena_filtros='';
        $('input:checkbox:checked').each(function(i, item){
        cadena_filtros+=$(item).val()+"|";});
        if(cadena_filtros.length > 0)
        {
            document.getElementById("seleccionados").value=cadena_filtros;
            $.post(urlRegistraProducto,{opc:1,unidad_id:$("#unidad_id").val(),seleccionados:cadena_filtros,tipo:1},function(data){
                $("#resultado").html(data);
                location.href = "index.php?_module=Modelos&_op=edit&unidad_id="+$("#unidad_id").val();
            });

        }
        else{
            alert("Seleccione al menos una categoria");
        }
    });

    $("#asignar_categoria").click(function(){
        cadena_filtros='';
        alert("asignar categorias    "+$("#unidad_id").val()+"     sub:   "+$("#categoria_id").val());
        $('input:checkbox:checked').each(function(i, item){
        cadena_filtros+=$(item).val()+"|";});
        if(cadena_filtros.length > 0)
        {
            document.getElementById("seleccionados").value=cadena_filtros;
            $.post(urlRegistraProducto,{opc:6,unidad_id:$("#unidad_id").val(),categoria_id:$("#categoria_id").val(),seleccionados:cadena_filtros,tipo:1},function(data){
                $("#resultado").html(data);
                location.href = "index.php?_module=Modelos&_op=editt&unidad_id="+$("#unidad_id").val()+"&categoria_id="+$("#categoria_id").val();
            });

        }
        else{
            alert("Seleccione al menos una categoria");
        }
    })



    $("#actualiza_producto").click(function(){
        if($("#unidad_id").val()>0)
        {
            if($("#name_prod").val()=='')
            {
                alert("Por favor teclee el nombre del producto");
            }
            else
            {
                $.post(urlRegistraProducto,{opc:3,unidad_id:$("#unidad_id").val(),nm_unidad:$("#name_prod").val(),link_unidad:$("#url").val()},function(data){
                    $("#resultado").html(data);
                    location.href = "index.php?_module=Modelos";
                });
            }
        }
        else
        {
            alert("Por favor seleccione un producto");
        }
    })

    $("#actualiza_categoria").click(function(){

        if($("#categoria_id").val()>0)
        {
            if($("#name_categoria").val()=='')
            {
                alert("Por favor teclee el nombre de la categoria");
            }
            else
            {
                $.post(urlRegistraProducto,{opc:4,categoria_id:$("#categoria_id").val(),nm_categoria:$("#name_categoria").val(),link_unidad:$("#url").val()},function(data){
                    $("#resultado").html(data);
                    location.href = "index.php?_module=Modelos&_op=edit&unidad_id="+$("#unidad_id").val();
                });
            }
        }
        else
        {
            alert("Por favor seleccione un producto");
        }
    })

    $("#actualiza_subcategoria").click(function(){
        if($("#subcategoria_id").val()>0)
        {
            if($("#name_subcategoria").val()=='')
            {
                alert("Por favor teclee el nombre de la Subcategoria");
            }
            else
            {
                $.post(urlRegistraProducto,{opc:8,subcategoria_id:$("#subcategoria_id").val(),nm_subcategoria:$("#name_subcategoria").val(),link_unidad:$("#url").val()},function(data){
                    $("#resultado").html(data);
                    location.href = "index.php?_module=Modelos&_op=editt&unidad_id="+$("#unidad_id").val()+"&categoria_id="+$("#categoria_id").val();
                });
            }
        }
        else
        {
            alert("Por favor seleccione un producto");
        }

    })

});
function del_producto(id,nombre)
{
    if(id > 0)
    {
        if(confirm("¿Esta seguro de eliminar el producto:  "+nombre))
        {
            $.post(urlRegistraProducto,{opc:2,unidad_id:id},function(data){
               $("#resultado").html(data);
                location.href = "index.php?_module=Modelos";
            });
        }
    }
}

function del_categoria(id,unidad_id)
{
    if(id > 0)
    {
        if(confirm("¿Esta seguro de eliminar la categoria"))
        {
            $.post(urlRegistraProducto,{opc:5,categoria_id:id},function(data){
               $("#resultado").html(data);
                location.href = "index.php?_module=Modelos&_op=edit&unidad_id="+unidad_id;
            });
        }
    }
}
function del_subcategoria(id,categoria_id,unidad_id)
{
    if(id > 0)
    {
        if(confirm("¿Esta seguro de eliminar la subcategoria"))
        {
            $.post(urlRegistraProducto,{opc:7,subcategoria_id:id},function(data){
               $("#resultado").html(data);
                location.href = "index.php?_module=Modelos&_op=editt&unidad_id="+unidad_id+"&categoria_id="+categoria_id;
            });
        }
    }

}