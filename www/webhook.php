<?php

require_once __DIR__.'/bot/FourBlitBot.php';

$key = cleanInput($_GET["key"]);

if(strlen($key) > 8) {
    $result = doQuery("SELECT ID,botToken FROM Bots WHERE isEnable=1 AND ID='$key';");
    if(mysqli_num_rows($result) > 0) {
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);

	$botToken = $row["botToken"];
	$botId = $row["ID"];

	$bot = new FourBlitBot($botId, $botToken, 'FourBlitBotChat');

//	$botWebhook = "https://www.4bl.it/bot/hook/$key";

// 	$bot->setWebhook($botWebhook);

/* 
if ($_GET["hook"] == 'set') {
    $bot->setWebhook(BOT_WEBHOOK);
} else if ($_GET["hook"] == 'remove') {
    $bot->removeWebhook();
}
*/

	$response = file_get_contents('php://input');
	$update = json_decode($response, true);

	LOGWrite($_SERVER["SCRIPT_NAME"],"Got update for botId $botId");

	$bot->init();
	$bot->onUpdateReceived($update);
    } else {
	echo "Invalid ID";
    }
} else {
    echo "Bot ID not set";
}
