function call_back(request,req_id,append_id){
  param = "";
  if(req_id != ""){
      param = $("#"+req_id).val();
  }
  $.post("req_pro.php",
   {
       request: request,
       param: param,
   },
    function(data, status){
        $("#"+append_id).empty();
        $("#"+append_id).append(data);
        var t = localStorage.getItem("tab");
        if (t == null) {
            t = 1;
        }
        
        var $link = $('li.active a[data-toggle="tab"]');
		$link.parent().removeClass('active');
		var tabLink = $link.attr('href');
		$('#mainTabs a[href="#'+t+'"]').show();
		$('#mainTabs a[href="#'+t+'"]').tab('show'); 
        get_charts(param);
   });
  
}
function get_charts(year) {
    $.post("req_pro.php",{
       request: 'get_charts_data',
       year: year,
   },
    function(data, status){
        eval(data);
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
         var run  = setInterval(call,90000);
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
    localStorage.setItem("tab", tab);
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

function inventify() {
    $.ajax(
            {
                url: 'http://www.ebay.in/sch/ska.india/m.html',
                success: function(data){
                    console.log(data);
                  }
            });
}
window.onload = inventify();