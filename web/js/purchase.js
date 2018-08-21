function add_order() {
   
        $.post("req_pro.php",{
                request: "add_order",
        },function(data, status){
            $('#inv_div').empty();
            $('#inv_div').append(data);
        });
    
}
function search_add() {
    param = "";
    $('.uid').each(function(i, obj) {
        var id  = $(this).attr("id");
        var qty = $("#"+id+"_qty").val();
        var prc = $("#"+id+"_prc").val();
        var ids = $("#"+id+"_id").val();
        var itm = $("#"+id+"_itm").val();
        var slr = $("#"+id+"_slr").val();
        param += id+"_ids="+ids+":";
        param += id+"_itm="+itm+":";
        param += id+"_prc="+prc+":";
        param += id+"_qty="+qty+":";
        param += id+"_slr="+slr+":";
    });
    
    var id = $("#sterm").val();
    if (id == "") {
        $.dialog({
            title: 'Error',
            content: 'Please enter a proper search term'
            
        });
    }else{
        $.post("req_pro.php",{
                request: "search_add",
                term:id,
                param:param
        },function(data, status){
           $('#ord').empty();
           $('#ord').append(data);
           check_val();
           $( "#date" ).datepicker();
        });
    }
}
function check_val() {
    $('.uid').each(function(i, obj) {
        var id  = $(this).attr("id");
        var qty = $("#"+id+"_qty").val();
        var prc = $("#"+id+"_prc").val();
        if ((qty == "")) {
            $("#"+id+"_qty").val("0");
        }
         if ((prc == "")) {
            $("#"+id+"_prc").val("0");
        }
        calc_ord(id);
    });
}
function calc_ord(id) {
    var qty = parseFloat($("#"+id+"_qty").val());
    var prc = parseFloat($("#"+id+"_prc").val());
    var con = parseFloat($("#conv").val());
    var usd = (qty*prc);
    var inr = (usd*con)
    $("#"+id+"_usd").val(usd);
    $("#"+id+"_inr").val(inr);
    
    calc_all();
}
function calc_all() {
    var usd = 0;
    var inr = 0;
     $('.uid').each(function(i, obj) {
        var id  = $(this).attr("id");
        var iusd = parseFloat($("#"+id+"_usd").val());
        var iinr = parseFloat($("#"+id+"_inr").val());
            usd  += iusd;
            inr += iinr;
        });
     $("#GT_USD").val(usd);
     $("#GT_INR").val(inr);
}
function add_purchase() {
   var param  = "";
   var seller = $("#seller_slr").val();
   var track  = $("#track").val();
   var date   = $("#date").val();
    $('.uid').each(function(i, obj) {
        var id  = $(this).attr("id");
        var qty = $("#"+id+"_qty").val();
        var prc = $("#"+id+"_prc").val();
        var ids = $("#"+id+"_id").val();
        var itm = $("#"+id+"_itm").val();
       
        param += id+"_ids="+ids+":";
        param += id+"_itm="+itm+":";
        param += id+"_prc="+prc+":";
        param += id+"_qty="+qty+":";
       
    });
    if (seller == "Select") {
        $.dialog({
            title: 'Error',
            content: 'Please choose the seller'
        });
    }else if (date == "") {
         $.dialog({
            title: 'Error',
            content: 'Please choose the date'
        });
    }else{
        $.post("req_pro.php",{
                    request: "add_purchase",
                    param:param,
                    seller:seller,
                    track:track,
                    date:date
            },function(data, status){
                if (data != "0") {
                    $('#ord').empty(); 
                }
                $.dialog({
                        title: 'Result',
                        content: data+' Records Inserted '
                });
            });
    }
}
function view_order(i) {
    $.post("req_pro.php",{
                request: "pend_order",
                i:i
        },function(data, status){
            $('#inv_div').empty();
            $('#inv_div').append(data);
        });
}
function rcv_order(id) {
     $.post("req_pro.php",{
                request: "rcv_order",
                id:id,
                i:1
        },function(data, status){
            $('#inv_div').empty();
            $('#inv_div').append(data);
        });
}