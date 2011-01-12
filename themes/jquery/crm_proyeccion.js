var cantidad;
var id_meta;
var num_aleatorio;
var urlEliminaMeta ="index.php?_module=Gerente&_op=elimina_meta";
var tmp;
$(document).ready(function (){

    $("#todos_meses").click(function(event){
        if($("#todos_meses").is(":checked"))
            $("#meses_id option").attr('selected',true);
        else
            $("#meses_id option").attr('selected',false);
    });

    $("#todos_uids").click(function(event){
        if($("#todos_uids").is(":checked"))
            $("#id_user option").attr('selected',true);
        else
            $("#id_user option").attr('selected',false);
    });


     $("#guarda_proyeccion").click(function(){
         cantidad=$("#cantidad").val();
         cantidad=cantidad.replace('$','');
         cantidad=cantidad.replace(',','');
         if( (cantidad > 0) &&  ($("#meses_id").val() != null) && ($("#id_user").val() != null))
         {
            if(!confirm("Desea guardar los datos de la proyeccion"))
            {
                return false;
            }
         }
        else
        {
            alert("Favor de llenar todos los campos, son obligatorios");
            return false;
        }
     });
});

function elimina_meta(gid,uid,ano,mes)
{
    if(confirm("Desea eliminar la meta"))
    {
        num_aleatorio = Math.round(Math.random()*(153650));
        $.get(urlEliminaMeta,{gid:gid,user_id:uid,ano:ano,mes:mes,num_aleatorio:num_aleatorio},function(data){
            alert(data);
            location.href="index.php?_module=Gerente&_op=consulta_proyeccion&ano_id="+ano;
        });
    }
}