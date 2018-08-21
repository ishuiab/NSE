function map_it(cls) {
  var map   = $("#map").val(); 
  var param = "map="+map+"&cls="+cls+"&par=";
  $('.'+cls).each(function(index, obj){
    var id  = $(this).attr('id');
    console.log(id);
    var val = $("#"+id).val();
    param += id+":"+val+";";
  });
  console.log(param);
   $.ajax({
				url: "anom_fix.php",
				data: param,
				cache: false,
				dataType: 'html',
				type: 'GET',
				success: function(result){
						location.reload();
				}});
}