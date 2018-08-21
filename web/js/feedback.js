function parse() {
    var data = $("#fbck").val();
    $.post("req_pro.php",{
       request: "feedback",
       data:data
      },function(data, status){
          $("#results").empty();
          $("#results").append(data);
   });
}