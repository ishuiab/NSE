function gen_pkg() {
    var from = $("#from").val();
    var to   = $("#to").val();
    if (from == "") {
        $.dialog({
						title: 'Error',
						content: '<b>From Is Mandatory</b>',
				});
    }else{
        if (to != ""){
            if (from > to) {
                $.dialog({
						title: 'Error',
						content: '<b>From Should Be Less Than To</b>',
				});
            }else{
                var param = "request=gen_pkg&from="+from+"&to="+to;
                 $.ajax({
                        url: "req_pro.php",
                        data: param,
                        cache: false,
                        dataType: 'html',
                        type: 'POST',
                        success: function(result){
                                $("#pkg_div").empty();
                                $("#pkg_div").append(result);
                        }});
            }
        }else{
                var param = "request=gen_pkg&from="+from+"&to="+to;
                 $.ajax({
                        url: "req_pro.php",
                        data: param,
                        cache: false,
                        dataType: 'html',
                        type: 'POST',
                        success: function(result){
                                $("#pkg_div").empty();
                                $("#pkg_div").append(result);
                        }});
        }
    }
}

function print_pkg() {
    var from = $("#from_sv").val();
    var to   = $("#to_sv").val();
    var link = "print_pkg.php?from="+from+"&to="+to;
    window.open(link);
}