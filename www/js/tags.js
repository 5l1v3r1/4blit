$(document).ready(function(){
    var jsonData = $.ajax({
	url: '/ajaxCb?action=getTags',
	method: 'GET',
	dataType: 'json',
	success: function(result) {
	    $('#tagcloud').jQCloud(result);
	}
    });
});
