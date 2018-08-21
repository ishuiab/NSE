function search() {
    var series = $("#series").val();
    var start  = $("#start").val();
    var end    = $("#end").val();
    var status = $("#status").val();
    $.post("req_pro.php",{
          request: "stock",
          series:series,
          start:start,
          end:end,
          },function(data, status){
               $("#stock_div").empty();
               $("#stock_div").append(data);
       });
}
function view_data(it) {
    $('#data').modal('show');
    $.post("req_pro.php",{
       request: "img_detail",
       it:it
      },function(data, status){
          $("#m_body").empty();
          $("#m_body").append(data);
   });
}
function fetch(id){
    var item = $("#"+id+"_itm").val();
    $.post("req_pro.php",{
        request: "get_prc",
        item:item
       },function(data, status){
           if(data == "ERROR"){
                alert("Error in fetching the data");
           }

           if(data == "ALI:"){
                var res = data.split(":");
                $("#"+id+"_slr").val(res[0]);
                $("#"+id+"_eps" ).removeAttr("disabled");
                
           }

           if(data == "NULL"){
                $("#"+id+"_eps" ).removeAttr("disabled");
           }else{
               var res = data.split(":");
                $("#"+id+"_lot").val(res[1]);
                $("#"+id+"_eps").val(res[0]);
                $("#"+id+"_slr").val(res[2]);
                calc(id);
           }
    });
}
function calc(id){
    var lot =  parseInt($("#"+id+"_lot").val());
    var lqt =  parseInt($("#"+id+"_lqt").val());
    var eps =  parseFloat($("#"+id+"_eps").val());

    var qty = (lot * lqt);
    var tps = (eps * lqt);
    
    
    $("#"+id+"_qty").val(qty);
    $("#"+id+"_tps").val(tps);
    var tps = parseFloat($("#"+id+"_tps").val());
    var qty = parseInt($("#"+id+"_qty").val());
    

    var ups = (tps / qty);
    $("#"+id+"_ups").val(ups);
}
function add(id){
    calc(id);
    var slr    = $("#"+id+"_slr").val();
    var itm    = $("#"+id+"_itm").val();
    var lot    = $("#"+id+"_lot").val();
    var lqt    = $("#"+id+"_lqt").val();
    var qty    = $("#"+id+"_qty").val();
    var eps    = $("#"+id+"_eps").val(); 
    var tps    = $("#"+id+"_tps").val();
    var des    = $("#"+id+"_des").val();


    $.post("req_pro.php",{
        request: "add_ord",
        slr:slr,
        itm:itm,
        lot:lot,
        lqt:lqt,
        qty:qty,
        eps:eps,
        tps:tps,
        des:des
       },function(data, status){
        $("#"+id+"_ord").val(data);
    });

}
function search_id(id){
    $('#o_body').empty();
    $('#ord').modal('show');
    $.post("req_pro.php",{
        request: "find_m",
        term:id,
        from:"FROM",
        to:"TO"
    },function(data, status){
      $('#o_body').empty();
      $('#o_body').append(data);
      
    });
}
function gen_stk_rep(){
    var seller= $("#STOCK_slr").val();
    $.post("req_pro.php",{
        request: "gen_stk_rep",
        seller:seller,
    },function(data, status){
        $("#stock_div").empty();
        $("#stock_div").append(data);
    });
}
function update(id){
    calc(id);
    console.log("upd "+id);
    var seller = $("#STOCK_slr").val();
    var lqt    = $("#"+id+"_lqt").val();
    var tps    = $("#"+id+"_tps").val();
    $.post("req_pro.php",{
        request: "upd_stk",
        seller:seller,
        lqt:lqt,
        id:id,
        tps:tps
    },function(data, status){
        gen_stk_rep();
    });
}
function delet(id){
    console.log("del "+id);
    calc(id);
    var seller = $("#STOCK_slr").val();
    
    var lqt    = $("#"+id+"_lqt").val();
    var tps    = $("#"+id+"_tps").val();
    
    $.post("req_pro.php",{
        request: "del_stk",
        seller:seller,
        lqt:lqt,
        tps:tps,
        id:id
    },function(data, status){
        gen_stk_rep();
    });
    

}