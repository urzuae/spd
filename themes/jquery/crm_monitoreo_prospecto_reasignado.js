/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

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
/*        headers: {
                3: {
                	sorter:"horas"
                }
            },
       dateFormat: "uk"*/
    });
});


