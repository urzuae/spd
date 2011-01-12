/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var idFont = 0;
$(function() {    
    $("a").click(function(){
        $("#nombreFuente").attr("value",$(this).attr("name"));
        idFont =  $(this).attr("id");
    });

    $('#basicModal input.basic, #basicModal a.basic').click(function (e) {
        e.preventDefault();
        $('#basicModalContent').modal();
    });
});
$(function(){
    $("#addFont").click(function(){
        var contentHtml = "<tr class='nuevoFuenteHijo'><td>Origen hijo</td><td><input class='requiredChild' type='text' name='nombreFuenteHijo' id='nombreFuenteHijo'></td><tr>";
        $("#updateFont tbody tr.nuevoFuenteHijo").remove();
        $("#updateFont tbody tr.padre").after(contentHtml);
        $("#flagAddChild").attr("value", "1");
    });
    $("#saveFont").click(function(){
        if($(".required").attr("value").length < 2)
        {
            alert("El nombre del origen debe tener al menos dos caracteres");
            return false;
        }
        if($("#flagAddChild").val() == "1")
            if($(".requiredChild").attr("value").length < 2)
            {
                alert("El nombre del origen debe tener al menos dos caracteres");
                return false;
            }
        $("#flagAddChild").attr("value", "0");
        location.href ="index.php?_module=Catalogos&_op=mostrarArbol&padre_id=" +
        idFont + "&guardar=1&nombrePadre=" + $("#nombreFuente").attr("value") +
        "&nombreHijo=" + $("#nombreFuenteHijo").attr("value");
    });
    $("#deleteFont").click(function(){
        if(!confirm("¿Realmente desea eliminar el origen?"))
            return false;
        location.href ="index.php?_module=Catalogos&_op=mostrarArbol&padre_id=" + idFont + "&del=1";        
    });
    $("#updFont").click(function(){
        if(!confirm("¿Realmente desea bloquear el origen?"))
            return false;
        location.href ="index.php?_module=Catalogos&_op=mostrarArbol&padre_id=" + idFont + "&upd=1";
    });
    $("#upddesFont").click(function(){
        if(!confirm("¿Realmente desea desbloquear el origen?"))
            return false;
        location.href ="index.php?_module=Catalogos&_op=mostrarArbol&padre_id=" + idFont + "&upddes=1";
    });

});
