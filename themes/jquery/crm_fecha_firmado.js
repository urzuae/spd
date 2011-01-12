/* 
 * Este script sirve para actualizar la fecha del contrato firmado
 */
var urlRegistraFirma ="index.php?_module=Campanas&_op=RegistraFechaFirmado";
$(document).ready(function (){
     $("#autorizado").click(function(){
     if( $("#contacto_id").val() > 0)
     {
        $.get(urlRegistraFirma,{contacto_id:$("#contacto_id").val()},function(data){$("#firmado").html(data);});
     }
     })
});