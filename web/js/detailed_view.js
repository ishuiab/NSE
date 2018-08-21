function user_det(user) {
    $("#m_body").empty();
    $('#data').modal('show');
    $.post("req_pro.php",{
       request: "user_det",
       user:user
      },function(data, status){ 
          $("#m_body").append(data);
   });
}
function ord_det(id) {
    $("#o_body").empty();
    $('#ord').modal('show');
    $.post("req_pro.php",{
       request: "ord_det",
       id:id
      },function(data, status){
          $("#o_body").append(data);
   });
}
function gen_invoice(id){
		  $.ajax({
				url: "inv.php",
				data: "id="+id,
				cache: false,
				dataType: 'html',
				type: 'GET',
				success: function(result){
						if (result != 1) {
                            var win = window.open('inv/'+result+".html", '_blank');
							if (win) {
								   //Browser has allowed it to be opened
								   win.focus();
						    } else {
								   //Browser has blocked it
								   alert('Please allow popups for this website');
						   }
                        }else{
							  $.alert({
								title: 'Error Message',
								content: '<b>Invoice Generation Failed</b>',
							});
						}
				}});
}
function today_update() {
    rtd();
    var run  = setInterval(rtd,300000);
}
function rtd() {
  $.post("req_pro.php",{
       request: "today"
   },function(data, status){
        
   });
}
document.onload = today_update();
