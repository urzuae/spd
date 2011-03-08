/* 
 * Este script sirve para actualizar la fecha del contrato firmado
 */
var urlRegistraFirma ="index.php?_module=Campanas&_op=RegistraFechaFirmado";
var url_Guarda_Nota  ="index.php?_module=Campanas&_op=guardar_nota";
var gid;
var uid;
var contacto_id;
var campana_id;
var llamada_id
var nota;
$(document).ready(function (){
     $("#autorizado").click(function(){
         if( $("#contacto_id").val() > 0)
         {
            $.get(urlRegistraFirma,{contacto_id:$("#contacto_id").val()},function(data){$("#firmado").html(data);});
        }
     })
    $("#guardar_nota").click(function(){
        nota=$("#nota").val();
        gid =$("#gid").val();
        uid =$("#uid").val();
        contacto_id =$("#contacto_id").val();
        campana_id =$("#campana_id").val();
        llamada_id =$("#llamada_id").val();
        if (nota.length > 0)
        {
            if((gid.length > 0) && (uid.length > 0) && (contacto_id.length > 0) && (campana_id.length > 0))
            {
                $.post(url_Guarda_Nota,{uid:uid,contacto_id:contacto_id,submit:1,nota:nota,campana_id:campana_id},function(data){
                    $("#resultado_nota").html(data);
                    alert(data);
                    location.href="index.php?_module=Campanas&_op=llamada&campana_id="+campana_id+"&contacto_id="+contacto_id+"&llamada_id="+llamada_id
                });
            }
        }
        else
        {
            alert("Por favor capture la nota");
        }
    });

});