<?php

include __DIR__."/common.inc.php";

set_error_handler("rpcErrorHandler");
error_reporting(E_ERROR | E_PARSE | E_COMPILE_ERROR | E_COMPILE_WARNING | E_CORE_ERROR | E_CORE_WARNING);

function rpcErrorHandler($errorcode, $errordescription, $filename, $linenumber, $context) {
    switch ($errorcode) {
    case E_NOTICE:
    case E_WARNING:
        break;
    default:
        $body = "Error code: $errorcode\n";
        $body .= "Error description: $errordescription\n";
        $body .= "Error occured in: $filename, line $linenumber\n";
        errorlog($body);
        header('Content-type: text/xml; charset=utf-8');
        ?>
        <methodResponse>
            <fault>
                <value>
                    <struct>
                        <member>
                            <name>faultCode</name>
                            <value><int>0</int></value>
                        </member>
                        <member>
                            <name>faultString</name>
                            <value>
                                <string>
                                    Error code: <?=$errorcode?>.
                                    Error description: <?=$errordescription?>.
                                    Error occured in: <?=$filename?>, line <?=$linenumber?>.
                                </string>
                            </value>
                        </member>
                    </struct>
                </value>
            </fault>
        </methodResponse>
        <?php
        die();
    }
}

function pingback($method, $params, $extra) {
    $source_uri = $params[0];
    $target_uri = $params[1];

    $creq = curl_init();

    curl_setopt($creq, CURLOPT_URL, $source_uri);
    curl_setopt($creq, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($creq, CURLOPT_TIMEOUT, 15);
    if(!$data = curl_exec($creq)) {
        // This page they claim they're linking to me from, it doesn't exist!  That can't be right.
	LOGWrite($_SERVER["SCRIPT_NAME"], "Source URI $source_uri does not exist (Target URI was $target_uri)");
        return array('faultCode' => 0x0010, 'faultString' => $error);
    }
    if(!strstr($data, htmlspecialchars($target_uri))) {
        // Doesn't contain a valid link
        if(!strstr($data, $target_uri)) {
            // Doesn't contain an invalid link either!
	    LOGWrite($_SERVER["SCRIPT_NAME"], "Source URI $source_uri does not contain a link to the target URI ($target_uri)");
            return array('faultCode' => 0x0011, 'faultString' => $error);
        }
    }
    if(preg_match("/<title>([^<]*)<\/title>/i", $data, $matches)) {
        $title = $matches[1];
    } else {
        $title = '';
    }
    curl_close($creq);

    $query = "SELECT COUNT(*) AS number FROM weblog_xrefs ";
    $query .= "WHERE sourceURI = '".$db->escape_string(preg_replace("/&(amp;)?/i","&amp;",$source_uri))."' AND entryid = ".$targetid;
    $rdsPb = $db->query($query);
    $rdPb = $rdsPb->fetch_assoc();
    if($rdPb['number'] > 0) {
        // They've already pingbacked/trackbacked this post once from this source URI.  No more!!!
        $error = "The pingback from ".$source_uri." to ".$target_uri." has already been registered";
        errorLog($error);
        return array('faultCode' => 0x0030, 'faultString' => $error);
    }
    $query = "INSERT INTO weblog_xrefs (type, date, sourceURI, entryid, title) ";
    $query .= "VALUES ('Pingback', '".gmdate('Y-m-d H:i:s')."', '".$db->escape_string(preg_replace("/&(amp;)?/i","&amp;",$source_uri))."', ".$targetid.", ";
    $query .= "'".$db->escape_string($title)."')";
    $db->query($query);

    // Yay! We done it! We is l33t!
    $message = "Thanks! Pingback from $source_uri to $target_uri registered";
    errorLog($message);
    return $message;
}

$server = xmlrpc_server_create();
xmlrpc_server_register_method($server, 'pingback.ping', 'pingback');

// Process request
$request = file_get_contents("php://input");
$options = array('escaping' => 'markup', 'encoding' => 'utf-8');
$response = xmlrpc_server_call_method($server, $request, null, $options);

// Output response
header('Content-type: application/xml; charset=utf-8');
echo $response;

// Clean up
xmlrpc_server_destroy($server);

exit;
?>