var gid=0;
var url_fuentes ="index.php?_module=Concesionarias&_op=seleccionafuente";
$(document).ready(function (){
	jQuery("#docheckchildren").checkboxTree({
			collapsedarrow: "../img/img-arrow-collapsed.gif",
			expandedarrow: "../img/img-arrow-expanded.gif",
			blankarrow: "../img/img-arrow-blank.gif",
			checkchildren: true,
			checkparents: true
	});
    $("#boton_checks").click(function(){
        gid=$("#gid").val();
        if(confirm("Desea guardar los cambios en las fuentes, para la concesionaria  "+gid))
        {
            cadena_filtros='';
            $('input:checked').each(function(i, item){
                cadena_filtros+=$(item).val()+"|";
            });
            $.post(url_fuentes,{gid:gid,fuentes:cadena_filtros},function(data){
             alert(data);
             location.href ="index.php?_module=Concesionarias&_op=fuentes&gid="+gid;
            });
        }
    });
});
