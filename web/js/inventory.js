function search() {
    var sterm = $("#sterm").val();
    if (!sterm.replace(/\s/g, '').length) {
        $.dialog({
            title: 'Error',
            content: 'Please enter a proper search term',
        });
    }else{
        $.post("req_pro.php",{
                request: "search_inv",
                term:sterm,
        },function(data, status){
            $('#inv_div').empty();
            $('#inv_div').append(data);
        });
    }
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
function add_img(im) {
    var link = $("#"+im+"_img").val();
    var item = $("#item_now").val();
    if (link != "") {
        $.post("req_pro.php",{
            request: "img_fetch",
            link:link,
            item:item,
            no:im
        },function(data, status){
          
            if (data == "0") {
                $.dialog({
                    title: 'Error',
                    content: 'Please enter a proper link',
                });
                
            }else{
                view_data(item);
            }
        });
    }else{
        $.dialog({
            title: 'Error',
            content: 'Link Should Not Be Left Blank',
        });
    }
   
    
}
function view_analysis(id) {
    $('#data').modal('show');
    $.post("req_pro.php",{
       request: "item_analysis",
       it:id
      },function(data, status){
          $("#m_body").empty();
          $("#m_body").append(data);
   });
}
