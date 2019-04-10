<?php

include "common.inc.php";

if($mySession->isLogged()) {
    // ===================================================================== LOGOUT
    LOGWrite($_SERVER["SCRIPT_NAME"],"User $myUser->displayName LOGGED OUT");
    $mySession->userId = false;
    doQuery("UPDATE Sessions SET userId='',lastAction=NOW() WHERE ID='".$mySession->ID."';");
    $mySession->sendMessage("Logged out");
    header('Location: /');
    exit();
} else {
    header('Location: /');
    exit();
}

?>
