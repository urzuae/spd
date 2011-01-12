var urlFilter="index.php?_module=CallcenterNacional&_op=regresahistorial&random=";
var gid=0;
$(document).ready(function (){
    $(".tablesorter").tablesorter({
        headers: {
                5: {
                	sorter:"horas"
                }
            },
       dateFormat: "uk"
    });
});
function Regresa_Historial(contacto_id)
{    
    aleatorio = Math.round(Math.random()*1000);
    url=urlFilter+aleatorio;
    $.get(url,{contacto_id:contacto_id},function(data){$("#historial").html(data);});
    $('#basicModalContent').modal();
}

