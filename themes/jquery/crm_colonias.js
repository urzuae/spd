var urlCps="../activation/zipCodeInfo.php";
$(document).ready(function (){
        $("#colonia").jecKill();
        $("#colonia").jec();

	$("#cp").blur(function(event){
        if($("#cp").val() != '')
        {
            $.getJSON(urlCps,{zipcode:$("#cp").val()},function(data){
                if(data.district != "")
                {
                    $("#poblacion").attr("value",data.district);
                    $("#ciudad").attr("value",data.state);
                    $("#pais").attr("value","MEXICO");
                    $("#colonia option").remove();
                    $.each(data.settlement, function(i,item){
                        $("#colonia").append('<option value="' + i + '">' + item + '</option>');
                    });
                    $('#entidad_id option[value='+data.id_mexico_state+']').attr('selected','selected');
                }
                else
                {
                    $("#poblacion").attr("value",'');
                    $("#ciudad").attr("value",'');
                    $("#pais").attr("value","");
                    $("#colonia option").remove();
                    $("#colonia").jecKill();
                    $("#colonia").jec();
                    $('#entidad_id option[value=0]').attr('selected','selected');
                }

            })
	}
        event.preventDefault();
    })
});