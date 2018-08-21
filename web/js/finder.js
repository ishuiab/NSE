function search() {
    var sterm = $("#sterm").val();
    var from  = $("#from").val();
    var to    = $("#to").val();
    if (!sterm.replace(/\s/g, '').length) {
        $.dialog({
            title: 'Error',
            content: 'Please enter a proper search term',
        });
    }else{
        $.post("req_pro.php",{
                request: "find_me",
                term:sterm,
                from:from,
                to:to
        },function(data, status){
            $('#results').empty();
            $('#results').append(data);
        });
    }
}

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