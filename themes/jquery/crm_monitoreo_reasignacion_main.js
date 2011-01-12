/*
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
/* plugin para ordenar las filas de una tabla en la pantalla principal de reasigncaion.
 * Se ha empleado en el modulo de monitoreo*/

$.tablesorter.addParser({
        // set a unique id
        id: "horas",
        is: function(s) {
            // return false so this parser is not auto detected
            return false;
        },
        format: function(s) {
            // format your data for normalization
            return s.toLowerCase().replace(/ d /,".").replace(/ h/,"");;
        },
        type: "numeric"
    }) ;

$(document).ready(function(){
    $(".tablesorter").tablesorter({
        headers: {
                5: {
                	sorter:"horas"
                },
                6: {
                	sorter:"horas"
                },
                7: {
                	sorter:"horas"
                }
            }
    });
});
