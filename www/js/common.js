// ========================================================================================= COOKIES

var cookiePromptTest = false; //change this to true to test the message

$(function () {
    if (cookiePromptTest || checkCookie("cookiePrompt") != "on") {
	// the id of the element the message will appear before
	$("#cookiebar").before('<div id="cookie-prompt" class="alert alert-notice"><button type="button" class="close" aria-label="Close" onclick="closeCookiePrompt()"><span aria-hidden="true">×</span></button>This website uses cookies. By continuing we assume your permission to deploy cookies, as detailed in our <a href="/legal#cookies" class="alert-link" rel="nofollow" title="Cookies Policy">cookies policy</a>.</div>');
    }
});

function closeCookiePrompt() {
    if (!cookiePromptTest) {
	createCookie("cookiePrompt", "on", 30); //don't show message for 30 days once closed (change if required)
    }
    $("#cookie-prompt").remove();
}

function createCookie(name, value, days) {
    if (days) {
	var date = new Date();
	date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
	var expires = "; expires=" + date.toGMTString();
    }
    else var expires = "";
    document.cookie = name + "=" + value + expires + "; path=/";
}

function checkCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
	var c = ca[i];
	while (c.charAt(0) == ' ') c = c.substring(1, c.length);
	if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}

function eraseCookie(name) {
    createCookie(name, "", -1);
}

// ========================================================================================= AJAXDIALOG

$(function (){
    $('.ajaxDialog').click(function() {
        var url = this.href;
        var title = this.title;
        // show a spinner or something via css
        var dialog = $("<div style=\"display:none\" class=\"loading\"><img src=\"/img/spinner.gif\"> loading...</div>").appendTo("body");
        // open the dialog
        dialog.dialog({
    	    open: function(event, ui) {
    		$('#ajaxDialog').validationEngine();
    	    },
            // add a close listener to prevent adding multiple divs to the document
            close: function(event, ui) {
                // remove div with all data and events
                $('#ajaxDialog').validationEngine('hideAll');
                dialog.remove();
            },
    	    title: title,
            modal: true,
            height: 'auto',
            width: 500,
            buttons: {
        	    'OK': function() {
        		if(jQuery('#ajaxDialog').validationEngine('validate')) { 
        		    jQuery('#ajaxDialog').submit();
        		    $(this).dialog("close");
        		}
        	    },
        	    "Annulla": function() {
        		$(this).dialog("close");
        	    }
        	}
        });
        // load remote content
        dialog.load(
            url,
            {}, // omit this param object to issue a GET request instead a POST request, otherwise you may provide post parameters within the object
            function (responseText, textStatus, XMLHttpRequest) {
                // remove the loading class
                dialog.removeClass('loading');
            }
        );
        //prevent the browser to follow the link
        return false;
    });
    $('.ajaxCall').click(function() {
        var url = this.href;
	var container = this;

	$.ajax({
	    url: url,
	    dataType: 'html',
	    success: function(data) {
		$(container).html(data);
    	    },
	    error: function(XMLHttpRequest, textStatus, errorThrown) {
		$(container).html('ERROR');
	    }
	});
	return false;
    });
});

$(document).ready(function() {
    $( "input[type=submit], button" ).button();

    $(".clickable-row").click(function() {
        window.location = $(this).data("href");
    });

    jQuery('form').validationEngine();

    $('.table tr[data-href]').each(function(){
        $(this).css('cursor','pointer').hover(
            function(){ 
                $(this).addClass('active'); 
            },  
            function(){ 
                $(this).removeClass('active'); 
            }).click( function(){ 
                document.location = $(this).attr('data-href'); 
            }
        );
    });
});
