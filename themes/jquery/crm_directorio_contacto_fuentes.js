var urlFilter="index.php?_module=Directorio&_op=filtroFuentes&random=";
var gid=0;
$(document).ready(function (){
        $('#basicModal input.basic, #basicModal a.basic').click(function (e) {
		e.preventDefault();
		$('#basicModalContent').modal();});

        $('#hijo_id_1').hide();
        $('#hijo_id_2').hide();
        $('#hijo_id_3').hide();
        $('#hijo_id_4').hide();
        $("#padre_id").change(function(event){
        gid=$("#gid").val();
         if($("#padre_id").val() != 0)
         {
             displayListChilds(event,'padre_id','hijo_id_1',gid);
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
             displayListChilds(event,'hijo_id_1','hijo_id_2',gid);}});

        $("#hijo_id_2").change(function(event){
         if($("#hijo_id_2").val() != 0){
             displayListChilds(event,'hijo_id_2','hijo_id_3',gid);}});

        $("#hijo_id_3").change(function(event){
         if($("#hijo_id_3").val() != 0){
             displayListChilds(event,'hijo_id_3','hijo_id_4',gid);}});

        function displayListChilds(event,div_padre,div_hijo,gid)
        {
        var valoractual=$('#'+div_padre).val()
        if($('#'+div_padre).val() != 0)
        {
            aleatorio = Math.round(Math.random()*1000);
            url=urlFilter+aleatorio;
            $.get(url,{
                    gid:gid,id:$('#'+div_padre).val()
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
