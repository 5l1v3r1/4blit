$(document).ready(function(){
    $.ajax({ 
        type    : "GET",
        url     : "/ajaxCb.php",
	dataType: "json",
        data: {
            action: "inboxMessage",
            idx: '0',
        },
        success: function(data) {
    	    $('#inboxList').append(data.html());
        }
    });
});