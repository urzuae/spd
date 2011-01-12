$.tablesorter.addParser({
        id: "horas",
        is: function(s) {
            return false;
        },
        format: function(s) {
            return s.toLowerCase().replace(/ d /,".").replace(/ h/,"");
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
