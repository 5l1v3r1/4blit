<?php

$CFG["dbHost"] = "localhost";
$CFG["dbUser"] = "";
$CFG["dbPassword"] = "";
$CFG["dbName"] = "";

$CFG["mailTemplate"] = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
<html xmlns='http://www.w3.org/1999/xhtml'>
<head>
<meta http-equiv='Content-Type' content='text/html; charset=UTF-8'/>
<meta name='viewport' content='width=device-width, initial-scale=1.0'/>
<title>%title%</title>
<style>
p { text-align: justify; }
#outlook a {padding:0;}
body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0;}
table td {border-collapse: collapse;}
@media screen and (max-width: 630px){
    *[class=\"container\"] { width: 320px !important; padding:0px !important}
    *[class=\"mobile-column\"] {display: block;}
    *[class=\"mob-column\"] {float: none !important;width: 100% !important;}         
    *[class=\"mobile-padding\"] {padding-left:10px !important;padding-right:10px !important;}         
    *[class=\"hide\"] {display:none !important;}          
    *[class=\"100p\"] {width:100% !important; height:auto !important;}            
    *[class=\"mobile-column-blog\"] {padding-bottom:20px;display:block;}
}
@media screen and (max-width: 450px){
    *[class=\"mobhide\"] {display:none !important;}    
    *[class=\"font-bump\"] {font-size:16px !important;}
    *[class=\"link-bump\"] {font-size:21px !important;}
    *[class=\"100p-mob\"] {width:100% !important; height:auto !important;}
}
</style>
</head>
<div style='background:#efefef;'>
<body style='padding:0; margin:0'>
    <table border='0' cellpadding='0' cellspacing='0' class='mobile-padding' style='margin: 0; padding: 0' width='100%'>
    <tr><td align='center' valign='top'>
    <!-- HEADER -->
    <table border='0' cellpadding='0' cellspacing='0' class='100p' width='640'>
	<tr>
	    <td height='20'></td>
        </tr>
    </table>
    <table border='0' cellpadding='0' cellspacing='0' class='100p' width='640'>
	<tr>
    	    <td valign='top'><img alt='4bl.it' border='0' class='100p' src='http://www.4bl.it/img/mail_header.gif' style='display:block'></td>
        </tr>
    </table>
    <table border='0' cellpadding='0' cellspacing='0' class='100p' width='640'>
        <tr>
            <td height='20'></td>
        </tr>
    </table>
    <!-- /HEADER -->
    <table border='0' cellpadding='20' cellspacing='0' class='100p' style='background-color: #E6E6E6' width='640'>
        <tr>
            <td valign='top'>
    		<table border='0' cellpadding='0' cellspacing='0'>
            	    <tr>
                        <td align='left' style='font-size:24px; color:red; font-family:Helvetica, Arial, sans-serif;'>%title%</td>
                    </tr>
                    <tr>
                        <td height='20'></td>
                    </tr>
                    <tr>
                        <td align='left class='font-bump' style='font-size:14px; color:#333333; font-family:Arial, Helvetica, sans-serif;'>
			    %body%
			</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <table border='0' cellpadding='0' cellspacing='0' class='100p' width='640'>
	<tr>
    	    <td height='20'></td>
	</tr>
    </table>
    <!-- FOOTER -->
    <table border='0' cellpadding='20' cellspacing='0' class='100p' style='background-color: #E6E6E6' width='640'>
        <tr>
            <td valign='top'>
    		<table border='0' cellpadding='0' cellspacing='0'>
		    <tr>
                	<td align='left class='font-bump' style='font-size:14px; color:#333333; font-family:Arial, Helvetica, sans-serif;'>
			    <a href='http://www.4bl.it'>4bl.it</a> <b style='color:red;'>//</b> Made with *love* in Siena, Tuscany, Italy <b style='color:red;'>//</b> info@4bl.it
			</td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <!-- /FOOTER -->
    </td></tr></table>
</body></html>";

$defaultAcl = array(
    'canLogin' => true,		// Può accedere al sistema ?
);

define('MESSAGE_NOTICE',1);
define('MESSAGE_WARNING',2);
define('MESSAGE_ERROR',4);

define('USER_FREEPERSONAL',1);
define('USER_PATRON',5);
define('USER_ADMINISTRATOR',10);

/* Level 1 - Unconfirmed */
$defaultLevel[1] = array(
    'maxSources' => 0,
    'maxBots' => 0,
    'maxChannels' => 0,
    'minBotPublishDelay' => 720,
    'canAddHelp' => 0,
    'canAddBlogPost' => 0,
);

/* Level 2 - Free personal */
$defaultLevel[2] = array(
    'maxSources' => 1,
    'maxBots' => 1,
    'maxChannels' => 1,
    'minBotPublishDelay' => 720,
    'canAddHelp' => 0,
    'canAddBlogPost' => 0,
);

/* Level 5 - Patron */
$defaultLevel[5] = array(
    'maxSources' => 5,
    'maxBots' => 5,
    'maxChannels' => 5,
    'minBotPublishDelay' => 30,
    'canAddHelp' => 0,
    'canAddBlogPost' => 0,
);

/* Level 10 - Administrator */
$defaultLevel[10] = array(
    'maxSources' => 1000,
    'maxBots' => 1000,
    'maxChannels' => 1000,
    'minBotPublishDelay' => 0,
    'canAddHelp' => 1,
    'canAddBlogPost' => 1,
);

$hybridAuthConfig = array(
    "callback" => "",
    "providers" => array (
	"Facebook" => array (
    	    "enabled" => true,
	    "keys"    => array("id" => "", "secret" => ""),
	    "scope"   => "email, public_profile",
	    "display" => "popup"
	),
	"Twitter" => array(
            "enabled" => true,
            "keys" => array("key" => "", "secret" => ""),
            "includeEmail" => true,
        ),
	"Google" => array ( // 'id' is your google client id
               "enabled" => true,
               "keys" => array ( "id" => "", "secret" => "" ),
        ),
    ),

    // if you want to enable logging, set 'debug_mode' to true  then provide a writable file by the web server on "debug_file"
    "debug_mode" => true,
    "debug_file" => "debug.log",
);

