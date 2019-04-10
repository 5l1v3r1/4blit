<?php

include_once __DIR__."/common.inc.php";

function helpAddTag($help_id,$tag) {
    global $DB;
    $tag = strtolower(trim($tag));
    
    if(strlen($tag) > 0) {
	$result = doQuery("SELECT ID FROM Tag WHERE Tag LIKE \"$tag\";");
	if(mysqli_num_rows($result) > 0) {
    	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $tag_id = $row["ID"];
	} else {
	    doQuery("INSERT INTO Tag(Tag) VALUES (\"".mysqli_real_escape_string($DB,$tag)."\");");
	    $tag_id = mysqli_insert_id($DB);
	}
	doQuery("INSERT INTO HelpTags(helpId,tagId,addDate) VALUES ('$help_id','$tag_id',NOW());");
	return true;
    } else {
	return false;
    }
}


if(isset($_GET["action"])) {
    $cbAction = cleanInput($_GET["action"]);
} else if(isset($_POST["action"])) {
    $cbAction = cleanInput($_POST["action"]);
}

if($cbAction == "sourceReportissue") {
    if($mySession->checkNonce($_POST["nonce"])) {

    }
}


// ===================================================================== REGISTER / LOGIN VIA SOCIAL
if(isset($_GET["l"])) {
    switch($_GET["l"]) {
	    case "facebook":
		$authProvider = "Facebook";
	        break;
	    case "twitter":
		$authProvider = "Twitter";
	        break;
	    case "google":
		$authProvider = "Google";
	        break;
	    default:
		break;
    }
    $_SESSION["otp"] = $_GET["otp"];
    $_SESSION["authProvider"] = $authProvider;
}

if(!$mySession->isLogged()) {
    /// Logged in via Telegram
    if (isset($_COOKIE['tg_user'])) {
	$auth_data_json = urldecode($_COOKIE['tg_user']);
	$auth_data = json_decode($auth_data_json, true);
    
	$check_hash = $auth_data['hash'];

	$first_name = htmlspecialchars($tg_user['first_name']);
	$last_name = htmlspecialchars($tg_user['last_name']);
	if(isset($tg_user['username'])) {
	    $username = htmlspecialchars($tg_user['username']);
	 }
    }

    /// Logged in via HybridAuth
    if(isset($_SESSION["authProvider"])) {
	try {
	    $authProvider = cleanInput($_SESSION["authProvider"]);
	
	    $adapter = $hybridAuth->authenticate($authProvider);
	
	    $userTokens = $adapter->getAccessToken();
	    $userProfile = $adapter->getUserProfile();
	} catch(Exception $e) {
	
	    LOGWrite($_SERVER["SCRIPT_NAME"],"HybridAuth exception (Code ".$e->getCode()."): ".$e->getMessage());
	    $mySession->sendMessage("Error while authenticating on $authProvider ! Error code: ".$e->getCode(),"error");
	}
	unset($_SESSION["authProvider"]);
    }

    if(isset($userTokens)) {
	if(isset($userProfile)) {

	    $authProviderUID = $userProfile->identifier;

	    // Check for OTP...
	    if(isset($_SESSION["otp"])) {
		$otp = cleanInput($_SESSION["otp"]);
		$result = doQuery("SELECT ID FROM Users WHERE OTP='$otp' LIMIT 1;");
		if(mysqli_num_rows($result) > 0) {
		    // Validation of OTP: just connect and login !
		    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		    $myUser = new User($row["ID"]);
		    if($myUser->isEnable == 0) {
			// Add auth provider
	    		$mySession->userId = $myUser->ID;
			doQuery("INSERT INTO UserAuthProvider (userId,authProvider,authProviderUID) VALUES ('$myUser->ID','$authProvider','$authProviderUID');");
			LOGWrite($_SERVER["SCRIPT_NAME"], "User $myUser->displayName VALIDATE YOUR ACCOUNT via $authProvider");
			// Enable user
			doQuery("UPDATE Users SET isEnable=1,lastLogin=NOW() WHERE ID='$mySession->userId';");

			$mySession->sendMessage("Welcome $myUser->displayName !");
		    } else {
			$mySession->sendMessage("Ooops ! Something wrong happens... please contact our support service as soon as possibile !");
		    }
		}
	    } else {
	        $result = doQuery("SELECT userId FROM UserAuthProvider WHERE authProvider='$authProvider' AND authProviderUID='$authProviderUID';");
		if(mysqli_num_rows($result) > 0) {
		    // Already signed-up user - LOGIN !
		    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		    $myUser = new User($row["userId"]);
    
		    if($myUser->isEnable == 1) {
	    		$mySession->userId = $myUser->ID;
			doQuery("UPDATE Users SET lastLogin=NOW() WHERE ID='$mySession->userId';");

			LOGWrite($_SERVER["SCRIPT_NAME"], "User $myUser->displayName LOGGED IN via $authProvider");

			$mySession->sendMessage("Welcome back $myUser->displayName");
		    } else {
			$mySession->sendMessage("Cannot login: user DISABLED. Please contact our support service as soon as possibile !");
		    }
		} else {
		    // New user - REGISTER !
		    $result = doQuery("SELECT ID FROM Users WHERE eMail LIKE '$userProfile->email';");
		    if(mysqli_num_rows($result) > 0) {
    			$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			$myUser = new User($row["ID"]);
			if($myUser->isEnable == 1) {
			    $mySession->userId = $myUser->ID;
			    // Indirizzo e-mail già presente...
			    doQuery("INSERT INTO UserAuthProvider (userId,authProvider,authProviderUID) VALUES ('$myUser->ID','$authProvider','$authProviderUID');");

			    LOGWrite($_SERVER["SCRIPT_NAME"], "User $myUser->displayName LOGGED IN with provider $authProvider");
			} else {
			    $mySession->sendMessage("Cannot login: user DISABLED. Please contact our support service as soon as possibile !");
			}
		    } else {
			doQuery("INSERT INTO Users (eMail,displayName,ACL,Level,isEnable,addDate) VALUES ('$userProfile->email','$userProfile->displayName','".mysqli_real_escape_string($DB,serialize($defaultAcl))."',".USER_FREEPERSONAL.",1,NOW());");
			$mySession->userId = mysqli_insert_id($DB);

			$myUser = new User($mySession->userId);

			doQuery("INSERT INTO UserAuthProvider (userId,authProvider,authProviderUID) VALUES ('$myUser->ID','$authProvider','$authProviderUID');");
			LOGWrite($_SERVER["SCRIPT_NAME"], "New user $myUser->displayName REGISTERED with $authProvider");

			$mySession->sendMessage("Welcome aboard $myUser->displayName !");
		    }
		    $myUser->sendMail(getString("user-welcome-mail-subject"),getString("user-welcome-mail-body"),true);
	        }
	    }

	    // Save userId into session row
	    doQuery("UPDATE Sessions SET userId='".$mySession->userId."',lastAction=NOW() WHERE ID='".$mySession->ID."';");
	    $mySession->setOpt("sessionToken",$userTokens);

	    if(isset($_GET["back_url"])) {
		header('Location: /'.$_GET["back_url"]);
	    } else {
		header('Location: /');
	    }
	    exit();
	}
    }
}

if($mySession->isLogged()) {
    // ===================================================================== UNREGISTER
    if(isset($_GET["unregister"])) {
	LOGWrite($_SERVER["SCRIPT_NAME"], $mySession->userId,"User DEREGISTER itself");
	$mySession->userId=false;
	doQuery("UPDATE Sessions SET userId='',lastAction=NOW() WHERE ID='".$mySession->ID."';");
	$mySession->sendMessage("Logged out");
	header('Location: /');
	exit();
    // ===================================================================== ANYTHING ELSE: RESTORE SESSION DATA
    }
}

if(($mySession->isLogged())&&(isset($cbAction))) {
    /* ===========================================
    Add or edit source
    =========================================== */
    if($cbAction == "addSource") {
	$source_url = $_POST["sourceUrl"];

	if(isURLAvailable($source_url)) {
	    if(isset($_POST["isPublic"]) && $_POST["isPublic"] == "on") {
		$is_public = 1;
	    } else {
		$is_public = 0;
	    }
	    $source_name = mysqli_real_escape_string($DB,$_POST["sourceName"]);
	    $source_description = mysqli_real_escape_string($DB,$_POST["sourceDescription"]);

	    $source_apikey =createApiKey();

	    doQuery("INSERT INTO Sources(Type,userId,Name,Description,URL,apiKey,isPublic,addDate) VALUES ('2','$myUser->ID','$source_name','$source_description','$source_url','$source_apikey','$is_public',NOW());");
	    $source_id = mysqli_insert_id($DB);

	    $mySession->sendMessage("Added new source ID $source_id from URL $source_url","success");
	} else {
	    $mySession->sendMessage("Oops ! URL $source_url seems to be not available: please check and try again.","error");
	}
    }

    if($cbAction == "editSource") {
	$source_id = cleanInput($_POST["sourceId"]);

	$source_name = mysqli_real_escape_string($DB,$_POST["sourceName"]);
	$source_description = mysqli_real_escape_string($DB,$_POST["sourceDescription"]);

	$source_url = mysqli_real_escape_string($DB,$_POST["sourceUrl"]);

	$source_apikey = mysqli_real_escape_string($DB,$_POST["sourceApiKey"]);

	$source_bot_id = intval($_POST["sourceBot"]);

	$source = new Source($source_id);
	if($source) {
	    if(isset($_POST["isPublic"]) && $_POST["isPublic"] == "on") {
		$is_public = 1;
	    } else {
		$is_public = 0;
	    }

	    if(isset($_POST["isMaskAuthor"])) {
		if($_POST["isMaskAuthor"] == "on") {
		    $source->setACL("maskAuthor",true);
		} else {
		    $source->setACL("maskAuthor",false);
		}
	    }

	    if(isset($_POST["isStrictIp"]) && $_POST["isStrictIp"] == "on") {
	        $is_strict_ip = 1;
	    } else {
	        $is_strict_ip = 0;
	    }

	    if(isset($_POST["deleteThis"]) && $_POST["deleteThis"] == "on") { /* Delete this BOT */
		doQuery("DELETE FROM Sources WHERE ID='$source_id';");
		LOGWrite($_SERVER["SCRIPT_NAME"], "Blog $source->Name ($source_id) owned by user $myUser->displayName was DELETED");

		$mySession->sendMessage("Your source $source->Name was deleted successfully !","success");
	    } else {
		doQuery("UPDATE Sources SET Name='$source_name',Description='$source_description',URL='$source_url',botId='$source_bot_id',apiKey='$source_apikey',isPublic='$is_public',isStrictIp='$is_strict_ip' WHERE ID='$source_id';");
		LOGWrite($_SERVER["SCRIPT_NAME"], "Source $source->Name ($source_id) owned by user $myUser->displayName was UPDATED");

		$mySession->sendMessage("Your source $source->Name was updated successfully !","success");
	    }
	}
    }

    /* ===========================================
    Add/Edit BOT
    =========================================== */
    if($cbAction == "editBot") {
	if(intval($_POST["botId"]) > 0) {
	    $bot_id = cleanInput($_POST["botId"]);

	    $result = doQuery("SELECT ID FROM Bots WHERE userId='$myUser->ID' AND ID='$bot_id';");
	    if(mysqli_num_rows($result) > 0) {
		$bot_name = mysqli_real_escape_string($DB,cleanInput($_POST["botName"]));

		if(preg_match("/^(\d+):(\S+)$/", $_POST["botToken"],$bot_token)) { /* ID:Token */
		    $bot_id = $bot_token[1];
		    $bot_sha = $bot_token[2];

		    if(isset($_POST["isEnable"]) && $_POST["isEnable"] == "on") {
			$is_enable = 1;
		    } else {
			$is_enable = 0;
		    }

		    $bot_publish_delay = intval($_POST["botPublishDelay"]);
		    if($myUser->getACL("minBotPublishDelay") > $bot_publish_delay) {
			$bot_publish_delay = $myUser->getACL("minBotPublishDelay");
		    }

		    if(isset($_POST["deleteThis"]) && $_POST["deleteThis"] == "on") { /* Delete this BOT */
		        doQuery("DELETE FROM Bots WHERE ID='$bot_id';");
			LOGWrite($_SERVER["SCRIPT_NAME"], "Bot $bot_name ($bot_id) owned by user $myUser->displayName was DELETED");

			$mySession->sendMessage("Your BOT $bot_name was deleted successfully !","success");
		    } else {
			doQuery("UPDATE Bots SET Name='$bot_name',ID='$bot_id',botToken='$bot_id:$bot_sha',publishDelay='$bot_publish_delay',isEnable='$is_enable',errorCounter=0 WHERE ID='$bot_id';");
			LOGWrite($_SERVER["SCRIPT_NAME"], "Bot $bot_name ($bot_id) owned by user $myUser->displayName was UPDATED");

			$mySession->sendMessage("Your BOT $bot_name was updated successfully !","success");
		    }
		}
	    }
	} else {
	    // Add new BOT
	    $bot_name = mysqli_real_escape_string(cleanInput($_POST["botName"]));
	    if(preg_match("/^(\d+):(\S+)$/", $_POST["botToken"],$bot_token)) { /* ID:Token */
		$bot_id = $bot_token[1];
		$bot_sha = $bot_token[2];

		$bot_name = mysqli_real_escape_string($DB,cleanInput($_POST["botName"]));

		$result = doQuery("SELECT ID FROM Bots WHERE ID='$bot_id';");
	        if(mysqli_num_rows($result) > 0) {
		    $mySession->sendMessage("Oops ! Seems that BOT $bot_id was already added.","error");
		} else {
	    	    $bot_publish_delay = intval($_POST["botPublishDelay"]);
		    if($myUser->getACL("minBotPublishDelay") > $bot_publish_delay) {
			$bot_publish_delay = $myUser->getACL("minBotPublishDelay");
		    }

		    doQuery("INSERT INTO Bots(ID,userId,Name,botToken,publishDelay,addDate) VALUES ('$bot_id','$myUser->ID','$bot_name','$bot_id:$bot_sha','$bot_publish_delay',NOW());");
		    LOGWrite($_SERVER["SCRIPT_NAME"], "Added BOT token for user $myUser->displayName and Bot ID $bot_id");
	    
		    $mySession->sendMessage("Your new BOT Token was addedd successfully !","success");
		}
	    } else {
		$mySession->sendMessage("Bot token is not valid ! Should be something like 12345661:AbcdEfghIlMnOpqrsTuvzXy so check and try again.","error");
	    }
	}
    }

    /* ===========================================
    Edit channel
    =========================================== */
    if($cbAction == "editChannel") {
	$channel_id = cleanInput($_POST["channelId"]);
	
	$channel = new Channel($channel_id);
	if($channel) {
	    if(isset($_POST["isEnable"]) && $_POST["isEnable"] == "on") {
		$is_enable = 1;
	    } else {
		$is_enable = 0;
	    }

	    $channel_bot = cleanInput($_POST["channelBot"]);
	    $new_channel_id = cleanInput($_POST["newChannelID"]);

	    if(isset($_POST["deleteThis"]) && $_POST["deleteThis"] == "on") { /* Delete this Channel */
		doQuery("DELETE FROM Chats WHERE ID='$channel_id';");
		LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $channel->ID owned by user $myUser->displayName was DELETED");

		$mySession->sendMessage("Your channel $channel->ID was deleted successfully !","success");
	    } else {
		if(strcmp($new_channel_id,$channel_id) != 0) {
		    doQuery("UPDATE Chats SET ID='$new_channel_id',botId='$channel_bot',isEnable='$is_enable' WHERE ID='$channel_id';");
		    LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $channel->ID owned by user $myUser->displayName was UPDATED with new ID $new_channel_id");
		} else {
		    doQuery("UPDATE Chats SET isEnable='$is_enable',botId='$channel_bot' WHERE ID='$channel_id';");
		    LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $channel->ID owned by user $myUser->displayName was UPDATED");
		}
		$mySession->sendMessage("Your channel $channel->ID was updated successfully !","success");
	    }
	}
    }

    /* ===========================================
    Add channel
    =========================================== */
    if($cbAction == "addChannel") {
	if(isset($_POST["isEnable"]) && $_POST["isEnable"] == "on") {
	    $is_enable = 1;
	} else {
	    $is_enable = 0;
	}

	$channel_bot = cleanInput($_POST["channelBot"]);
	$new_channel_id = cleanInput($_POST["newChannelID"]);

	if(strlen($new_channel_id) > 5) {
	    doQuery("INSERT INTO Chats(ID,Type,botId,isEnable,addDate) VALUES('$new_channel_id','channel','$channel_bot','$is_enable',NOW());");
	    LOGWrite($_SERVER["SCRIPT_NAME"], "Channel $new_channel_id wad added by user $myUser->displayName");

	    $mySession->sendMessage("Your new channel $new_channel_id was added successfully !","success");
	} else {
	    $mySession->sendMessage("Please check channel id $new_channel_id: seems to be invalid ! ","error");
	}
    }

    /* ===========================================
    Add/Edit Help
    =========================================== */
    if($cbAction == "editHelp") {
	$help_id = intval($_POST["helpId"]);
	$help_title = mysqli_real_escape_string($DB,cleanInput($_POST["helpTitle"]));
	$help_content = mysqli_real_escape_string($DB,cleanInput($_POST["helpContent"]));

	if($help_id > 0) {
	    doQuery("UPDATE Help SET Title='$help_title',Content='$help_content' WHERE ID='$help_id';");
	} else {
	    doQuery("INSERT INTO Help(Title,Content,addDate) VALUES ('$help_title','$help_content',NOW())");
	    $help_id = mysqli_insert_id($DB);
	}
	
	if(strlen($_POST["helpTags"]) > 0) {
	    foreach(explode(',',$_POST["helpTags"]) as $tag) {
		helpAddTag($help_id,$tag);
	    }
	}
    }

    /* ===========================================
    Add/Edit Blog Post
    =========================================== */
    if($cbAction == "editBlogPost") {
	$blog_post_id = intval($_POST["blogPostId"]);
	$blog_post_title = mysqli_real_escape_string($DB,$_POST["blogPostTitle"]);
	$blog_post_content = mysqli_real_escape_string($DB,$_POST["blogPostContent"]);

	if($blog_post_id > 0) {
	    doQuery("UPDATE Blog SET Title='$blog_post_title',Content='$blog_post_content' WHERE ID='$blog_post_id';");

	    $mySession->sendMessage("Post '$blog_post_title' updated succesfully !","success");
	} else {
	    doQuery("INSERT INTO Blog(Title,Content,addDate) VALUES ('$blog_post_title','$blog_post_content',NOW())");
	    $blog_post_id = mysqli_insert_id($DB);

	    $mySession->sendMessage("New post '$blog_post_title' added succesfully !","success");
	}
    }

    /* ===========================================
    Delete user account
    =========================================== */
    if($cbAction == "userDelete") {
	$user_id = intval($_POST["userId"]);
	
	$user = new User($user_id);
	if(isset($user->ID)) {
	    // Remove all Sources belonging to this user
	    doQuery("DELETE FROM Sources WHERE userId='$user_id';");
	    // Remove all BOTs 
	    doQuery("DELETE FROM Bots WHERE userId='$user_id';");
	    // Remove all POSTs
	    doQuery("DELETE FROM Posts WHERE userId='$user_id';");
	    // Finally, disable user account
	    doQuery("UPDATE Users SET isEnable=0 WHERE ID='$user_id';");
	    // ...and send eMail
	    $user->sendMail(getString("user-delete-mail-subject"),getString("user-delete-mail-body"));

	    $mySession->sendMessage("User $user->displayName deleted succesfully !","success");
	} else {
	    $mySession->sendMessage("Error deleting $user_id user","error");
	}
    }

    /* ===========================================
    Enable user
    =========================================== */
    if($cbAction == "userEnable") {
	$user_id = intval($_POST["userId"]);
	
	$user = new User($user_id);
	if($user) {
	    // Enable user account
	    doQuery("UPDATE Users SET isEnable=1 WHERE ID='$user_id';");
	}
    }

    /* ===========================================
    Send message to user
    =========================================== */
    if($cbAction == "userSendMessage") {
	$user_id = intval($_POST["userId"]);
	
	$user = new User($user_id);
	if($user) {
    	    $message_title = mysqli_real_escape_string($DB,$_POST["mailTitle"]);
	    $message_content = mysqli_real_escape_string($DB,$_POST["mailContent"]);

	    $user->sendMessage(MESSAGE_NOTICE, $message_title, $message_content, true);

	    $mySession->sendMessage("Message '$message_title' to $user->displayName sent !","success");
	}
    }

    /* ===========================================
    Delete user message
    =========================================== */
    if($cbAction == "userDeleteMessage") {
	$inbox_id = intval($_POST["inboxId"]);
	if($inbox_id > 0) {
	    $result = doQuery("SELECT Type, Title, Content, replyTo, isRead, addDate FROM UserMessages WHERE userId='$myUser->ID' AND ID='$inbox_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$inbox_title = stripslashes($row["Title"]);
		doQuery("DELETE FROM UserMessages WHERE ID='$inbox_id';");
	        $mySession->sendMessage("Message '$inbox_title' deleted successfully !","success");
	    }
	}
    }
}

?>
