<?php

require_once __DIR__.'/FourBlitBot.php';

$result = doQuery("SELECT ID,userId,botToken,TIMESTAMPDIFF(MINUTE, lastPublish, NOW()) AS lastPublish,publishDelay,errorCounter,lastError FROM Bots WHERE isEnable=1;");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$bot_token = $row["botToken"];
	$bot_id = $row["ID"];

	try {
	    $bot = new Bot($bot_id);
	    $bot_user = new User($row["userId"]);
	    if(isset($bot_user->ID)) {
		$last_publish = $row["lastPublish"]; /* How minutes since last publish ? */
		$publish_delay = $row["publishDelay"]; /* Minutes between every publish */
	    
	        $error_counter = $row["errorCounter"]; /* How many consequential errors ? */

		if($error_counter > 5) {
		    doQuery("UPDATE Bots SET isEnable=0 WHERE ID=$bot_id;");
		    $last_error = stripslashes($row["lastError"]);
		    LOGWrite($_SERVER["SCRIPT_NAME"],"BOT $bot_id DISABLED due multiple errors: $last_error");
		    $bot_user->sendMessage(MESSAGE_WARNING, getString("user-error-botdisabled-subject", array('%botName%' => $bot->Name)),getString("user-error-botdisabled-body", array('%botName%' => $bot->Name,'%errorMessage' => $last_error)),true);
	        } else {
		    if($bot_user->getACL("minBotPublishDelay") > $publish_delay) {
			$publish_delay = $bot_user->getACL("minBotPublishDelay");
		    }

		    if(empty($last_publish)||($last_publish > $publish_delay)) {
			$bot = new FourBlitBot($bot_id, $bot_token, 'FourBlitBotChat');
			$bot->runPoll();
		    }
		}
	    }
	} catch (Exception $e) {
	    // Don't stop if one bot raise an exception
	}
    }
}

?>