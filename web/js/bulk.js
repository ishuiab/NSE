function getscrips(sel){
    star = sel.value;
    if(star == "select"){
        $("#scrip").prop('disabled', 'disabled');
        $("#scrip").empty();
        $("#scrip").append("<option>Select</option>");
        $("#bulk_id").prop('disabled', 'disabled');
        $("#bulk_id").empty();
        $("#bulk_id").append("<option>Select</option>");
    }else{
        $.post("req_pro.php",{
            request: 'getscrip',
            param: star,
        },
         function(data, status){
            $("#scrip").prop('disabled', false);
            $("#scrip").empty();
            $("#scrip").append(data);
            $("#bulk_id").empty();
            $("#bulk_id").append("<option>Select</option>");
            $("#bulk_id").prop('disabled', 'disabled');
        });
        
    }
}

function getbulkids(sel){
    scrip = sel.value;
    if(scrip == "select"){
        $("#bulk_id").prop('disabled', 'disabled');
        $("#bulk_id").empty();
        $("#bulk_id").append("<option>Select</option>");
    }else{
        $.post("req_pro.php",{
            request: 'getbulkids',
            param: scrip,
        },
        function(data, status){
            $("#bulk_id").prop('disabled', false);
            $("#bulk_id").empty();
            $("#bulk_id").append(data);
            
        });
    }
}

function fetch_info(){
    bulk_id = $("#bulk_id").val();
    if(bulk_id == "Select"){
        alert("Please Select A Proper Bulk ID");
    }else{
        $.post("req_pro.php",{
            request: 'bulkstat',
            param: bulk_id,
        },
        function(data, status){
            $("#stats").empty();
            $("#stats").append(data);
        });
    }
}
function view_data(star_id,trn) {
    $.post("req_pro.php",{
        request: "stardet",
        param:star_id,
        trn:trn
       },function(data, status){
           $("#m_body").empty();
           $("#m_body").append(data);
    });
    $('#data').modal('show');
}