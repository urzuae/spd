/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
var urlFilter = "index.php?_module=Filtros&_op=combos";
var urlFilterFuentes="index.php?_module=Filtros&_op=filtroFuentes";

        $(document).ready(function(){
            $('#hijo_id_1').hide();
            $('#hijo_id_2').hide();
            $('#hijo_id_3').hide();
            $('#hijo_id_4').hide();
			$("select").change(function()
            {
				var combos = new Array();
                combos['grupo_empresarial_id'] = "region_id";
				combos['region_id'] = "zona_id";
				combos['zona_id'] = "entidad_id";
                combos['entidad_id'] = "plaza_id";
                combos['plaza_id'] = "concesionaria";
				posicion = $(this).attr("name");
				valor = $(this).val()
                if(posicion == 'grupo_empresarial_id' && valor>0)
                 {
                    $("#ubicacion").hide()
                    $("#zona_id").html('<option value="0" selected="selected">Selecciona Zona</option>')
                    $("#entidad_id").html('<option value="0" selected="selected">Selecciona Entidad</option>')
                    $("#plaza_id").html('<option value="0" selected="selected">Selecciona Plaza</option>')
                    $("#concesionaria").html('<option value="0" selected="selected">Selecciona Concesionaria</option>')
                 }
                else
                {
                    $("#ubicacion").show()
                    if(posicion == 'region_id' && valor==0)
                    {
                        $("#zona_id").html('<option value="0" selected="selected">Selecciona Zona</option>')
                        $("#entidad_id").html('<option value="0" selected="selected">Selecciona Entidad</option>')
                        $("#plaza_id").html('<option value="0" selected="selected">Selecciona Plaza</option>')
                        $("#concesionaria").html('<option value="0" selected="selected">Selecciona Concesionaria</option>')
    				}
                    else
                    {
                        $("#"+combos[posicion]).html('<option selected="selected" value="0">Cargando...</option>')
                        if(valor > 0 || posicion !='nombre')
                        {
                			$.get(urlFilter,{
                    				combo:$(this).attr("name"),
									id:$(this).val(),
                                    id_zona:$('#zona_id').val()
									},function(data){
                                        $("#"+combos[posicion]).html(data);
							})
                        }
                    }
                }
			})
    
            $("#padre_id").change(function(event){
                if($("#padre_id").val() > 0)
                {
                    displayListChilds(event,'padre_id','hijo_id_1');
                }
                else
                {
                    valor=0;
                    $('#hijo_id_1').hide();
                    $('#hijo_id_2').hide();
                    $('#hijo_id_3').hide();
                    $('#hijo_id_4').hide();
                    $("#origen").val(valor);
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
                    $.get(urlFilterFuentes,{
                    id:$('#'+div_padre).val()
                    },function(data){
                    if(data.length>0)
                    {
                        $("#"+div_hijo).html(data);
                        $("#"+div_hijo).show();
                    }
                    else
                    {
                        $("#origen").val(valoractual);
                    }
                    })
                }
            }
            
            $("#basico").click(function(){
                window.document.concesion.basico.value=0;
                if(window.document.concesion.basico.checked)
                    window.document.concesion.basico.value=1;
            })
            $("#medio").click(function(){
                window.document.concesion.medio.value=0;
                if(window.document.concesion.medio.checked)
                    window.document.concesion.medio.value=2;
            })
            $("#avanzado").click(function(){
                window.document.concesion.avanzado.value=0;
                if(window.document.concesion.avanzado.checked)
                    window.document.concesion.avanzado.value=3;
            })
    })