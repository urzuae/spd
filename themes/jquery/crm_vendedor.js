var email;
var aleatorio;
var user;
var site_name;
var uid;
var url_alta_vendedor="index.php?_module=Gerente&_op=usuario";
var cadena_c;
$(document).ready(function(){
    //Checar correo electronico
    $("#mail1").blur(function()
    {
        email=$("#mail1").val();
        if(email.length > 0)
        {
            if(!valEmail(email))
            {
                alert("Favor de proporcinar un correo valido");
                return false;
            }
            else
            {
                email=email.toLowerCase();
            }
        }
    })
    
    // boton guardar datos
    $("#guardavendedor").click(function(){
        var f = document.contacto;
        user     =$("#user").val();
        email    =$("#mail1").val();
        site_name=$("#site_name").val();
        uid=$("#uid").val();
        if (user == '')
        {
            alert("Ingrese un nombre de usuario");
            return false;
        }
        if (email.length > 0)
        {
            if(!valEmail(email))
            {
                alert("Favor de proporcinar un correo valido");
                return false;
            }
        }
        else
        {
            alert("Ingrese un correo electronico");
            return false;
        }
        aleatorio = Math.round(Math.random()*1000);
        if(user.length > 3)
        {
            if(confirm("Desear dar de alta al usuario"))
            {
                $("#user").val('');
                $("#mail1").val('');
                $.post(url_alta_vendedor,{uid:uid,user:user,email:email,aleatorio:aleatorio},function(data){
                    $.gritter.add({
                        title: site_name,
                        text: data,
                        //image:'http://localhost/sf_site/'+site_name+'/img/logo/'+site_name+'.png',
                        image:'http://www.pcsmexico.com/salesfunnel/'+site_name+'/img/logo/'+site_name+'.png',
                        sticky: false,
                        time: '6000'
                    });
                })
            }
            else
            {
                alert('El usuario no ha sido dado de alta');
            }
        }
    })
    //fin de jquery
});

function valEmail(txt)
{
    var b=/^[^@\s]+@[^@\.\s]+(\.[^@\.\s]+)+$/
    return b.test(txt)
}
function capsall(theForm){
    var els = theForm.elements;
    for(i=0; i<els.length; i++){
        switch(els[i].type){
            case "text":
                if (els[i].name == "email")
                    break;
                els[i].value= els[i].value.toUpperCase();
                break;
        }
    }
}
function check_string(cadena)
{
  var regreso='';
  var chars="ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  var s = "";
  var j = 0;
  for (i = 0; i < cadena.length; i++)
  {
    if (chars.indexOf(cadena.charAt(i)) != -1)
    {
      s = s + cadena.charAt(i);
    }
    else j++;
  }
  regreso = s;
/*  if (j > 0)
  {
   return false;
  }*/
  return regreso;
}

