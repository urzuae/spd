//var urlFilter = "admin/Filtros/filtroFuentes.php";
var urlFilter = "admin/Filtros/filtroFuentes.php";
//var urlFilter ="index.php?_module=Filtros&_op=filtroVehiculo";
$(document).ready(function (){
        $('#basicModal input.basic, #basicModal a.basic').click(function (e) {
		e.preventDefault();
		$('#basicModalContent').modal();});

        $('#hijo_id_1').hide();
        $('#hijo_id_2').hide();
        $('#hijo_id_3').hide();
        $('#hijo_id_4').hide();
        $("#padre_id").change(function(event){
         if($("#padre_id").val() > 0)
         {
             displayListChilds(event,'padre_id','hijo_id_1');
         }
        else
         {
            $('#hijo_id_1').hide();
            $('#hijo_id_2').hide();
            $('#hijo_id_3').hide();
            $('#hijo_id_4').hide();
        }
        });

        $("#hijo_id_1").change(function(event){
         if($("#hijo_id_1").val() != 0){
             displayListChilds(event,'hijo_id_1','hijo_id_2');}});

        $("#hijo_id_2").change(function(event){
         if($("#hijo_id_2").val() != 0){
             displayListChilds(event,'hijo_id_2','hijo_id_3');}});

        $("#hijo_id_3").change(function(event){
         if($("#hijo_id_3").val() != 0){
             displayListChilds(event,'hijo_id_3','hijo_id_4');}});

        function displayListChilds(event,div_padre,div_hijo)
        {
        var valoractual=$('#'+div_padre).val()
        if($('#'+div_padre).val() != 0)
        {
                $.get(urlFilter,{
                    id:$('#'+div_padre).val()
					},function(data){
                    if(data.length>0)
                    {
                            $("#"+div_hijo).html(data);
                            $("#"+div_hijo).show();
                    }
                    else                                                   
                         $("#origen").val(valoractual);                        
                    });
        }
    }
});
