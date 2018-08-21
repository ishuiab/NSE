function search() {
    var series = $("#series").val();
    var start  = $("#start").val();
    var end    = $("#end").val();
    $.post("req_pro.php",{
          request: "unleash",
          series:series,
          start:start,
          end:end,
          status:status
          },function(data, status){
               $("#anon_div").empty();
               $("#anon_div").append(data);
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