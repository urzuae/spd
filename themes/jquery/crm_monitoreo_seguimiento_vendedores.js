$.tablesorter.addParser({
        id: "horas",
        is: function(s) {
            return false;
        },
        format: function(s) {
            return s.toLowerCase().replace(/ d /,".").replace(/ h/,"");;
        },
        type: "numeric"
    }) ;

$(document).ready(function(){    
    $(".tablesorter").tablesorter({
        headers: {
                4: {
                    sorter:"horas"
                },
                5: {
                	sorter:"horas"
                },
                6: {
                	sorter:"horas"
                },
                8: {
                	sorter:"horas"
                },
                9: {
                	sorter:"horas"
                },
                10: {
                	sorter:"horas"
                }
            }
    });
});
