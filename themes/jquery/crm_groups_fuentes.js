var cadena_filtros='';
$(document).ready(function(){
    $(".tablesorter").tablesorter({});
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

    $("#guardar").click(function(){
        cadena_filtros='';
        $('input:checkbox:checked').each(function(i, item){
        cadena_filtros+=$(item).val()+"|";});
        document.getElementById("seleccionados").value=cadena_filtros;
    });
});
