/* 
 * Script de Jquery, para manipular el ciclo de venta
 * 
 */
var nm_ciclo_venta;
var url_ciclo_venta ="index.php?_module=Campanas&_op=ciclo";
var total_ciclos=0;
var aleatorio=0;
var id=0;
var chars='ABCDEFGHIJKLMNÑOPQRSTUVWXYZabcdefghijklmnñopqrstuvwxyzÁÉÍÓÚ ';
$(document).ready(function (){
    $("#nva_etapa").hide();
    $("#nm_ciclo_venta").hide();
    $("#guardar_etapa").hide();
    $("#actualiza_etapa").hide();

    $("#cancelar_etapa").click(function(){
        $("#nva_etapa").hide();
        $("#nm_ciclo_venta").hide();
        $("#guardar_etapa").hide();
        $("#actualiza_etapa").hide();
        $("#nm_ciclo_venta").val('');
    });

    $("#guardar_etapa").click(function(){
        nm_ciclo_venta=$("#nm_ciclo_venta").val();
        aleatorio = Math.round(Math.random()*1000);
        if(nm_ciclo_venta.length > 0)
        {
            if(check_chars(nm_ciclo_venta, chars))
            {
                $.get(url_ciclo_venta,{opc:0,nm_ciclo:nm_ciclo_venta,random:aleatorio},function(valor){
                    if(valor== 0)
                    {
                        $.get(url_ciclo_venta,{opc:1,nm_ciclo:nm_ciclo_venta,random:aleatorio},function(data){
                            $("#nva_etapa").hide();
                            $("#nm_ciclo_venta").hide();
                            $("#guardar_etapa").hide();
                            $("#actualiza_etapa").hide();
                            location.href="index.php?_module=Campanas";
                        });
                    }
                    else
                    {
                        alert("Etapa existente, favor de teclear otro nombre");
                    }
                });
            }
            else
            {
                alert('Favor de eliminar los caracteres no válidos, sólo se aceptan letras.');
            }
        }
        else
        {
            alert('Favor de proporcionar el nombre de la etapa.');
        }
    });


    $("#actualiza_etapa").click(function(){
        nm_ciclo_venta=$("#nm_ciclo_venta").val();
        aleatorio = Math.round(Math.random()*1000);
        if(nm_ciclo_venta.length > 0)
        {
            if(check_chars(nm_ciclo_venta, chars))
            {
                $.get(url_ciclo_venta,{opc:0,nm_ciclo:nm_ciclo_venta,random:aleatorio},function(valor){
                    if(valor== 0)
                    {
                        $.get(url_ciclo_venta,{opc:5,id:id,nm_ciclo:nm_ciclo_venta,random:aleatorio},function(data){
                            $("#nva_etapa").hide();
                            $("#nm_ciclo_venta").hide();
                            $("#guardar_etapa").hide();
                            $("#actualiza_etapa").hide();
                            location.href="index.php?_module=Campanas";
                        });
                    }
                    else
                    {
                        alert("Etapa existente, favor de teclear otro nombre");
                    }
                });
            }
            else
            {
                alert('Favor de eliminar los caracteres no válidos, sólo se aceptan letras.');
            }
        }
        else
        {
            alert('Favor de proporcionar el nombre de la etapa.');
        }
    });

    $("#incluir").click(function(){
        $("#nm_ciclo_venta").val('');
        total_ciclos=$("#total_ciclo").val();
        if(total_ciclos < 10)
        {
            $("#nva_etapa").show();
            $("#nm_ciclo_venta").show();
            $("#guardar_etapa").show();
            $("#actualiza_etapa").hide();
        }
        else
        {
            $("#nva_etapa").hide();
            $("#nm_ciclo_venta").hide();
            $("#guardar_etapa").hide();
            $("#actualiza_etapa").hide();
            $("#respuesta").css({ color: "#FF0000", background: "#FFFF99", width:"350px", align:"center", border: "1px solid #FF0000"});
            $("#respuesta").html("Solamente se aceptan 10 etapas del ciclo de venta");
        }
    });

    $("#submit").click(function(){
        total_ciclos=$("#total_ciclo").val();
        if(total_ciclos > 0)
        {
            if(confirm("Esta seguro de guardar el Ciclo de Venta"))
            {
                $("#submit").submit();
                alert("Se ha guardado su ciclo de venta");
            }
            else
                return false;
        }
        else
            {
                alert("El ciclo de venta no puede ser vacio, favor de proporciona al menos una etapa");
            }
    })
});


function check_chars(el, chars)
{
  var s = "";
  var j = 0;
  for (i = 0; i < el.length; i++)
  {
    if (chars.indexOf(el.charAt(i)) != -1)
    {
      s = s + el.charAt(i);
    }
    else j++;
  }
  el.value = s;
  if (j > 0)
  {   
   return false;
  }
  return true;
}

function actualiza(consec,name)
{
    id=consec;
    $("#nva_etapa").show();
    $("#nm_ciclo_venta").show();
    $("#guardar_etapa").hide();
    $("#actualiza_etapa").show();
    $("#nm_ciclo_venta").val(name);
}
function asciende(id,consec)
{
    $.get(url_ciclo_venta,{opc:2,id:id,consec:consec},function(){
    location.href="index.php?_module=Campanas";
    });

}

function desciende(id,consec)
{
    $.get(url_ciclo_venta,{opc:3,id:id,consec:consec},function(){});
    location.href="index.php?_module=Campanas";
}

function elimina_etapa(id,total)
{
    if(total > 1)
    {
        if(confirm("Esta seguro de eliminar la etapa seleccionada."))
        {
            $.get(url_ciclo_venta,{opc:4,id:id},function(data){
            location.href="index.php?_module=Campanas";
            });
        }
    }
    else
    {
        alert("No puede dejar vacio el Ciclo de Venta\n\nUsted puede modificar el nombre de la etapa");
    }
}