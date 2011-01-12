
$(function(){
    $(".guardar").click(function(){        
        if($(".requerido").val().length ==  0)
        {
            alert("El texto debe ser mayor a 2 caracteres");
            return false;
        }
        else return true;
    });
});
