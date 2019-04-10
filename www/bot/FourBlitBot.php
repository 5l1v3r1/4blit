<?php

require_once __DIR__.'/TelegramBot.php';

require_once __DIR__.'/../common.inc.php';

class FourBlitBot extends TelegramBot {
    public function init() {
	parent::init();
    }
}

class FourBlitBotChat extends TelegramBotChat {

    protected $ID=false;
    protected $botId;
    protected $chatId;

    public function __construct($core, $bot_id, $chat_id, $chat_type='private') {
	parent::__construct($core, $bot_id, $chat_id, $chat_type);

	$this->botId = $bot_id;
	$this->chatId = $chat_id;
    }

    public function init() {
	
    }

    public function on_poll() {
	global $DB;

	if($this->chatType == 'channel') {
	    $result = doQuery("SELECT ID FROM Posts WHERE botId='$this->botId' AND isPublished=0 AND isActive=1 ORDER BY addDate LIMIT 1;");
	    if(mysqli_num_rows($result) > 0) {
		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		    $post_id = intval($row["ID"]);
		    $post = new Post($post_id);

		    $msg = "<b>".sanitizeString($post->Title)."</b>\n".sanitizeString($post->Excerpt)."\n\nby ".sanitizeString($post->Author)." - ".$post->getUniqueURL();

		    $response = $this->apiSendMessage($msg);
		    if($response['ok'] === true) {
			LOGWrite(__METHOD__, "Bot ID $this->botId just send post ID $post_id to chat ID $this->chatId");
			doQuery("UPDATE Bots SET errorCounter=0 WHERE ID='$this->botId';");
			$post->setPublished();
		    } else {
			LOGWrite(__METHOD__, "ERROR ".$response['error_code']." while bot ID $this->botId try to send post ID $post_id to chat ID $this->chatId: ".$response['description']);
			doQuery("UPDATE Bots SET errorCounter=errorCounter+1,lastError='".$response['error_code']." - ".mysqli_real_escape_string($DB,$response['description'])."' WHERE ID='$this->botId';");
		    }
		}
	    }
	}
    }
    
    /* ===== HELP ===== */
    public function command_help($params, $message) {
	$this->apiSendMessage("BotID:".$this->botId." Chat ID:".$this->chatId." Type:".$this->chatType);
    }

    public function bot_added_to_chat($message) {
	LOGWrite(__METHOD__,"Bot ID $this->botId added to ".$message['chat']['type']." ".$message['chat']['title']);
    }

    public function bot_kicked_from_chat($message) {
	LOGWrite(__METHOD__,"Bot ID $this->botId kicked from ".$message['chat']['type']." ".$message['chat']['title']);
    }

    public function some_command($command, $params, $message) {
	LOGWrite(__METHOD__,"Bot ID $this->botId receive command from ".$message['chat']['type']." ".$message['chat']['title'].": ".$message['text']);
    }

    public function message($text, $message) {
	LOGWrite(__METHOD__,"Bot ID $this->botId receive from ".$message['chat']['type']." ".$message['chat']['title'].": ".$message['text']);
    }
}