function call_back(request,req_id,append_id){
  param = "";
  if(req_id != ""){
      param = $("#"+req_id).val();
  }
  $.post("req_pro.php",
   {
       request: request,
       param: param
   },
    function(data, status){
        $("#"+append_id).empty();
        $("#"+append_id).append(data);
        var t = localStorage.getItem("tab_anon");
        if (t == null) {
            t = 1;
        }
        
        var $link = $('li.active a[data-toggle="tab"]');
		$link.parent().removeClass('active');
		var tabLink = $link.attr('href');
		$('#mainTabs a[href="#'+t+'"]').show();
		$('#mainTabs a[href="#'+t+'"]').tab('show'); 
        
   });
}
function action(date,request){
  set_stat(date);
  $.post("req_pro.php",
   {
       request: request,
       date:date
   },function(data, status){
        call_back('get_stat_div','year','date_div');
   });
}
function set_stat(date) {
   
   $.post("req_pro.php",{
       request: "set_stat",
       date:date
   },function(data, status){
        call_back('get_stat_div','year','date_div');
     }); 
}
function update_data(){
         var run  = setInterval(call,30000);
}
function call() {
    call_back('get_stat_div','year','date_div')
}
function reset(date){
  $.post("req_pro.php",
   {
       request: "reset_me",
       date:date
   },function(data, status){
        call_back('get_stat_div','year','date_div');
   });
}
function process_all(month){
  $.post("req_pro.php",
   {
       request: "all_month",
       month:month
   },function(data, status){
        call_back('get_stat_div','year','date_div');
   });
}
function view_data(date) {
    $('#data').modal('show');
    $.post("req_pro.php",{
       request: "summary_detail",
       date:date
      },function(data, status){
          $("#m_body").empty();
          $("#m_body").append(data);
          call_back('get_stat_div','year','date_div');
   });
}
function last_click(tab) {
    localStorage.setItem("tab_anon", tab);
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
function search() {
    $("#anon_div").empty();
    $("#ult_API").empty();
    var id = $("#s_id").val();
    if(id.length > 3){
       $.post("req_pro.php",{
          request: "ult_src",
          id:id
          },function(data, status){
              if (data == "NULL") {
                  
                  $.alert({
                      title: 'Error Message',
                      content: '<b>No Results Found In DB</b>',
                 });
              }else{
                  $("#anon_div").append(data);
              }
       });
    }else{
      $.alert({
					title: 'Error Message',
					content: '<b>ID Should Be More Than or Equal to 4 Character</b>',
	  });
    }
}
function search_API(){
  $("#ult_API").empty();
  var sterm = $("#s_term").val();
  if (sterm == "") {
    $.alert({
					title: 'Error Message',
					content: '<b>Enter Search Term</b>',
	  });
  }else{
     $.post("req_pro.php",{
          request: "ult_API",
          sterm:sterm
          },function(data, status){
              $("#ult_API").append(data);
       });
  }
}
function c(a) {
    console.log(a);
}
function SaveTerm() {
    var sterm = $("#s_term").val();
    var id    = $("#s_id").val();
    var desc  = $("#desc").val();
    
    $("#anon_div").empty();
    $("#ult_API").empty();
    
    
    $.post("req_pro.php",{
          request: "ult_save",
          sterm:sterm,
          id:id,
          desc:desc
          },function(data, status){
            
              $.alert({
					title: 'Success Message',
					content: data,
               });
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