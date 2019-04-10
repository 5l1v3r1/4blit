<?php

include __DIR__.'/common.inc.php';
require_once __DIR__.'/bot/FourBlitBot.php';

if(isset($_GET["action"])) {
    $ajaxAction = cleanInput($_GET["action"]);
} else {
    $ajaxAction = cleanInput($_POST["action"]);
}

if($ajaxAction == "login") {
    echo "<div class='modal-dialog'>
	<div class='modal-content'>
	    <form class='form-horizontal' role='form'>
		<div class='modal-header'>
	    	    <button type='button' class='close' data-dismiss='modal' aria-hidden='true'>&times;</button>
		    <h4 class='modal-title' id='dialogModalLabel'>"._("Sign-in")."</h4>
		</div>
		<div class='modal-body row'>
		    <div class='col-md-6'>
			<a class='btn btn-block btn-social btn-facebook' href='?register=facebook'>
			    <i class='fa fa-facebook'></i>". _("Sign-in Facebook")."
			</a>
		    </div><div class='col-md-6'>
			"._("<p>We use social sign-in to simplify your life with password</p><p>P.S. we dont' publish anything without your permission</p>")."
		    </div>
		</div>
		<div class='modal-footer'>
		    <button type='button' class='btn' data-dismiss='modal'>"._("Annulla")."</button>
		</div>
	    </form>
	</div>
    </div>";
}

if($ajaxAction == "sourceReportIssue") {
    $source_id = intval($_GET["id"]);

    $source = new Source($source_id);
    if($source->ID) {
	echo "<form method='POST' id='ajaxDialog'>
	    <input type='hidden' name='action' value='sourceReportIssue'>
	    <input type='hidden' name='sourceId' value='$source_id'>
	    <input type='hidden' name='nonce' value='".$mySession->getNonce()."'>
	    <div class='form-group row'>
		<label for='staticEmail' class='col-sm-2 col-form-label'>"._("Contact e-mail")."</label>
		<div class='col-sm-10'>
		    <input type='text' class='form-control' name='reportSender' placeholder='Your email, just in case we need to contact you'>
		</div>
	    </div>
	    <div class='form-group row'>
		<textarea class='form-control' name='reportData' placeholder='Describe here the issue you encountered'></textarea>
	    </div>
	</form>";
    }
}

if($ajaxAction == "getPosts") {
// current=1&rowCount=10&sort[sender]=asc&searchPhrase=	
    $current = $_POST["current"];
    $row_count = $_POST["rowCount"];

    $searchQuery = "";

    if(isset($_POST["searchPhrase"])) {
        $search = cleanInput($_POST["searchPhrase"]);
	$searchQuery = "AND Title LIKE '%$search%'";
    }

    $jsonArray = array();

    $result = doQuery("SELECT ID FROM Posts WHERE userId='$myUser->ID' $searchQuery;");
    $jsonArray["total"] = mysqli_num_rows($result)-1;

    $result = doQuery("SELECT ID FROM Posts WHERE userId='$myUser->ID' $searchQuery ORDER BY addDate DESC LIMIT $row_count OFFSET $current;");
    if(mysqli_num_rows($result) > 0) {
	$jsonArray["current"] = $_POST["current"];
	$jsonArray["rowCount"] = $_POST["rowCount"];

	while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	    $post = new Post($row["ID"]);

	    $jsonRow[] = array(
		"id" => $post->ID,
		"title" => $post->Title,
		"views" => $post->Views,
		"publishDate" => (is_null($post->publishDate) ? "not yet":$post->publishDate->format('h:m:s m-d-Y'))
	    );
	}
	$jsonArray["rows"] = $jsonRow;
    }
    echo json_encode($jsonArray);
}

/* ===========================================
Get tags
=========================================== */
if($ajaxAction == "getTags") {
    $result = doQuery("SELECT postId,tagId,COUNT(*) AS rank FROM PostTags GROUP BY tagId ORDER BY rank DESC LIMIT 10;");
    if(mysqli_num_rows($result) > 0) {
	while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	    $tag = getTag($row["tagId"]);
	    if(strlen($tag) > 0) {
		$json_data[] = array('text' => getTag($row["tagId"]),'link' => '/rd/'.$row["postId"], 'weight' => $row["rank"]);
	    }
	}
        echo json_encode($json_data);
    } else {
	echo "No tags";
    }
}

/* ===========================================
Get statistical data about sources
=========================================== */
if($ajaxAction == "getGraphData") {

    $labels = array();
    $points = array();

    $result = doQuery("SELECT Day,sourceId,numPosts,numClicks FROM SourcesStats AS t1 INNER JOIN Sources AS t2 ON t1.sourceId=t2.ID WHERE t2.userId='$myUser->ID' AND Day >= DATE(NOW()) - INTERVAL 7 DAY;");
    if(mysqli_num_rows($result) > 0) {
	while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	    $labels[] = $row["Day"];
	    $clicks[] = $row["numClicks"];
	    $posts[] = $row["numPosts"];
	}
    }
    echo json_encode(array('labels' => $labels, 'clicks' => $clicks, 'posts' => $posts));
}

if($mySession->isLogged()) {

    /* ===========================================
    Users admin actions
    =========================================== */
    if($ajaxAction == "userDelete") {
	if(isset($_GET["ID"])) { 
	    $tmpUser = new User(intval($_GET["ID"]));
	    echo "<form method='POST' id='ajaxDialog'>
		<input type='hidden' name='action' value='userDelete'>
		<input type='hidden' name='userId' value='$tmpUser->ID'>
		<p>"._("Delete user $tmpUser->eMail account ? This action cannot be undone !")."</p>
	    </form>";
	}
    }

    if($ajaxAction == "userEnable") {
	if(isset($_GET["ID"])) { 
	    $tmpUser = new User(intval($_GET["ID"]));
	    echo "<form method='POST' id='ajaxDialog'>
		<input type='hidden' name='action' value='userEnable'>
		<input type='hidden' name='userId' value='$tmpUser->ID'>
		<p>"._("Please confirm: enable $tmpUser->eMail account ?")."</p>
	    </form>";
	}
    }

    if($ajaxAction == "userDeleteMessage") {
	if(isset($_GET["ID"])) { 
	    $inbox_id = intval($_GET["ID"]);

	    echo "<form method='POST' id='ajaxDialog'>
		<input type='hidden' name='action' value='userDeleteMessage'>
		<input type='hidden' name='inboxId' value='$inbox_id'>";

	    $result = doQuery("SELECT Type, Title, Content, replyTo, isRead, addDate FROM UserMessages WHERE userId='$myUser->ID' AND ID='$inbox_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$inbox_type = $row["Type"];
		$inbox_title = stripslashes($row["Title"]);
		echo _("Confirm delete of mail'$inbox_title' ?");
	    } else {
		echo "ID error";
	    }
	    echo "</form>";
	}
    }


    if($ajaxAction == "userSendMessage") {
	if(isset($_GET["ID"])) { 
	    $tmpUser = new User(intval($_GET["ID"]));

	    echo "<script>tinymce.init({ selector:'textarea' });</script>
		<p>Send message to $tmpUser->displayName ($tmpUser->eMail)</p>
		<div class='form-group'>
		    <label for='mailTitle'>Message title:</label>
		    <input type='text' name='mailTitle' class='w-100 form-text validate[required]' id='mailTitle'>
		</div><div class='form-group'>
		    <label for='mailContent'>Content</label>
		    <textarea name='mailContent' class='form-control' id='mailContent'></textarea>
	        </div>
	    </form>";
	} else {

	}
    }

    /* ===========================================
    Join BOT
    =========================================== */
    if($ajaxAction == "joinBot") {
    	$bot_id = intval(cleanInput($_GET["ID"]));
	if($bot_id > 0) {
	    $bot = new Bot($bot_id);

	    $chatbot = new FourBlitBot($bot->ID,$bot->botToken, 'FourBlitBotChat');

	    $bot_webhook = "https://www.4bl.it/hook/".$bot->ID;

	    $chatbot->removeWebhook();

	    $chatbot->setWebhook($bot_webhook);

	    echo "Webhook ($bot_webhook) for bot $bot->ID SET. Now can safely close this window !";
	}
    }


    /* ===========================================
    Add or edit SOURCES
    =========================================== */
    if($ajaxAction == "genApiKey") {
	echo createApiKey();
    }

    if($ajaxAction == "editSource") {
	if(isset($_GET["ID"])) {
	    $source_id = intval($_GET["ID"]);
	    $source = new Source($source_id);
	}

	echo "<script>
		$(function() {    
		    $('.gen-apikey').click(function(e) {
			$.ajax({
			    method: 'POST',
			    url: 'ajaxCb.php',
			    data: {action: 'genApiKey'},
			    datatype: 'text',
			}).done(function(data) {
			    $('#sourceApiKey').val(data)
			});
			return false;
		    });
		});
		</script>
	<form method='POST' id='ajaxDialog'>";
	if(isset($source->ID)) {
	    echo "<input type='hidden' name='action' value='editSource'>
	    <input type='hidden' name='sourceId' value='$source->ID'>
    	    <div class='form-group'>
		<label for='sourceBotSelect'>"._("Connected to BOT")."</label>";
	    $result = doQuery("SELECT ID,Name FROM Bots WHERE userId='$myUser->ID';");
	    if(mysqli_num_rows($result) > 0) {
		echo "<select class='form-control' id='sourceBotSelect' name='sourceBot'>";
		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		    $bot_id = $row["ID"];
		    $bot_name = stripslashes($row["Name"]);
		    echo "<option value='$bot_id' ".isSelected($source->botId,$bot_id).">$bot_name</option>";
		}
		echo "</select>";
	    } else {
		echo "<div class='alert alert-danger' role='alert'>There are no BOTs available: please add one !</div>";
	    }
	    echo "</div>";
	} else {
	    echo "<input type='hidden' name='action' value='addSource'>";
	} 
	if($source->Type > 1) {
	    echo "<div class='form-group'>
		<span class='form-group-addon'>"._("Type").":</span>
		<select class='form-control' id='sourceType' name='sourceType'>
		    <option value='2' ".isSelected($source->Type,2).">RSS Feed</option>
		</select>
	    </div>";
	}
	echo "<div class='form-group'>
		<span class='form-group-addon'>"._("URL").":</span>
		<input type='text' id='blogUrl' name='sourceUrl' class='w-100 validate[required,custom[url]]' value='".$source->URL."'>
		<p class='help-block'>"._("Source URL can be an RSS feed or a Blog, if connected via plugin")."</p>
	    </div><div class='form-group'>
		<span class='form-group-addon'>"._("Name").":</span>
		<input type='text' id='sourceName' name='sourceName' class='w-100 validate[required]' value='".$source->Name."'>
		<p class='help-block'>"._("The name of this source")."</p>
	    </div>";
	if(isset($source->ID)) {
	    echo " <div class='form-group'>
		<span class='form-group-addon'>"._("Api KEY").":</span>
		<input type='text' id='sourceApiKey' name='sourceApiKey' class='w-100 validate[required]' value='".$source->apiKey."' readonly><a href='#' class='gen-apikey'><i class='fa fa-key' aria-hidden='true'></i></a>
		<p class='help-block'>"._("Api Key to user this source. Click on key button to generate a new Api key.")."</p>
	    </div>";
	}
	echo "<div class='form-group'>
		<span class='form-group-addon'>"._("Description").":</span>
		<input type='text' id='sourceDescription' name='sourceDescription' class='w-100 validate[required]' value='".$source->Description."'>
		<p class='help-block'>"._("A brief description of this source")."</p>
	    </div><div class='form-group'>
	        <input type='checkbox' name='isStrictIp' ".isChecked($source->isStrictIp)."> Strict IP check
	        <p class='help-block'>"._("Allow publish only from detected IP on join ($source->sourceIp)")."</p>
	    </div><div class='form-group'>
	    	<input type='checkbox' name='isPublic' ".isChecked($source->isPublic)."> Public source
		<p class='help-block'>"._("If checked, this source will be threatened as public")."</p>
	    </div><div class='form-group'>
	        <input type='checkbox' name='isMaskAuthor' ".isChecked($source->getACL('maskAuthor'))."> Mask author
	        <p class='help-block'>"._("If checked, replace author with source name ($source->Name)")."</p>
	    </div>";
	if(isset($source->ID)) {
	    echo "<div class='form-group'>
	    	<input type='checkbox' name='deleteThis'> Delete this source
		<p class='help-block'>"._("** WARNING ** If checked, this source will be permanently deleted")."</p>
	    </div>";
	} 
	echo "</form>";
    }

    /* ===========================================
    Force fetch SOURCES
    =========================================== */
    if($ajaxAction == "fetchSource") {
	$source_id = intval($_GET["ID"]);
	if(RSSHarvester($source_id,$error)) {
	    echo "OK ! RSS fetched :-)";
	} else {
	    echo "Ooops ! Something went wrong: $error";
	}
    }

    /* ===========================================
    Check SOURCES
    =========================================== */
    if($ajaxAction == "checkSource") {
	$source_id = intval($_GET["ID"]);
	$source = new Source($source_id);
	if(isset($source->ID)) {
	    $result = doQuery("SELECT Type,Message,addDate,numRepeat FROM SourcesLog WHERE sourceId='$source_id' ORDER BY addDate DESC LIMIT 10;");
	    if(mysqli_num_rows($result) > 0) {
		echo "<div class='list-group'>";
		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		    $message = stripslashes($row["Message"]);
		    $add_date = new DateTime($row["addDate"]);
		    switch($row["Type"]) {
			case MESSAGE_NOTIFY:
			    $type = "info";
			    break;
			case MESSAGE_WARNING:
			    $type = "warning";
			    break;
			case MESSAGE_ERROR:
			    $type = "danger";
			    break;
			default:
			    $type = "info";
		    }
		    echo "<p class='list-group-item list-group-item-action list-group-item-$type'>$message (".$add_date->format("d-m-Y H:i:s").")</p>";
		}
		echo "</div>";
	    } else {
		echo _("Niente da segnalare :-)");
	    }
	} else {
	    echo _("Source ID error");
	}
    }

    /* ===========================================
    Edit BOT
    =========================================== */

    if($ajaxAction == "editBot") {
	echo "<form method='POST' id='ajaxDialog'>
	<input type='hidden' name='action' value='editBot'>";

	if(isset($_GET["ID"])) {
	    $bot_id = cleanInput($_GET["ID"]);

	    $result = doQuery("SELECT ID FROM Bots WHERE userId='$myUser->ID' AND ID='$bot_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    	        $tmpBot = new Bot($row["ID"]);

	        echo "<input type='hidden' name='botId' value='$tmpBot->ID'>";
	    }
	} else {
	    echo "<input type='hidden' name='botId' value=''>";
	}
	echo "<div class='form-group'>
	    <span class='form-group-addon'>"._("Name").":</span>
	    <input type='text' id='botName' name='botName' class='validate[required]' value='".$tmpBot->Name."'>
	    <p class='help-block'>"._("The name for your BOT, just to identify quickly")."</p>
	</div><div class='form-group'>
	    <span class='form-group-addon'>"._("BOT Token").":</span>
	    <input type='text' id='botToken' name='botToken' class='validate[required]' value='".$tmpBot->botToken."'>
	    <p class='help-block'>"._("Telegram BOT Token: need to access Telegram realtime chat network")."</p>
	</div><div class='form-group'>
	    <span class='form-group-addon'>"._("Publish posts every")."</span>
	    <select name='botPublishDelay'>
	        <option value='30' ".isSelected($tmpBot->publishDelay,30).">"._("30 minutes")."</option>
	        <option value='60' ".isSelected($tmpBot->publishDelay,60).">"._("1 hour")."</option>
	        <option value='120' ".isSelected($tmpBot->publishDelay,120).">"._("2 hours")."</option>
	        <option value='240' ".isSelected($tmpBot->publishDelay,240).">"._("4 hours")."</option>
	        <option value='720' ".isSelected($tmpBot->publishDelay,720).">"._("12 hours")."</option>
	        <option value='1440' ".isSelected($tmpBot->publishDelay,1440).">"._("1 day")."</option>
	        <option value='2880' ".isSelected($tmpBot->publishDelay,2880).">"._("2 day2")."</option>
	    </select>
	</div><div class='form-group'>
	    <input type='checkbox' name='isEnable' ".isChecked($tmpBot->isEnable)."> Enabled
	    <p class='help-block'>"._("If enabled, this BOT can publish to linked Telegram channels")."</p>
	</div>";
	if(isset($tmpBot)) {
	    echo "<div class='form-group'>
		<input type='checkbox' name='deleteThis'> Delete this BOT
		<p class='help-block'>"._("** WARNING ** If checked, this bot will be permanently deleted")."</p>
	    </div>";
	}
    }

    if($ajaxAction == "editChannel") {
	echo "<form method='POST' id='ajaxDialog'>";
	
	$channel_id = cleanInput($_GET["ID"]);
	if($channel_id) {
	    $channel = new Channel($channel_id);
	    if($channel) {
		echo "<input type='hidden' name='action' value='editChannel'>
		<input type='hidden' name='channelId' value='$channel->ID'>";
	    }
	} else {
	    echo "<input type='hidden' name='action' value='addChannel'>";
	}
	echo "<div class='form-group'>
		<span class='form-group-addon'>Channel name:</span>
		<input type='text' id='newChannelId' name='newChannelID' class='validate[required]' value='".$channel->ID."'>
		<p class='help-block'>"._("Telegram channels starts with '@': copy your channel ID here")."</p>
	    </div><div class='form-group'>
		<span class='form-group-addon'>BOT linked:</span>
		<select name='channelBot' id='channelBot'>";
	$result = doQuery("SELECT ID,Name FROM Bots WHERE userId='$myUser->ID';");
	if(mysqli_num_rows($result) > 0) {
	    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	        $bot_id = $row["ID"];
	        $bot_name = $row["Name"];
	        echo "<option value='$bot_id' ".isSelected($channel->botId,$bot_id).">$bot_name</option>";
	    }
	}
	echo "</select>
		<p class='help-block'>"._("Choose a BOT to link with this channel")."</p>
	    </div><div class='form-group'>
	    	<input type='checkbox' name='isEnable' ".isChecked($channel->isEnable)."> Enabled
		<p class='help-block'>"._("If checked, this channel is enabled")."</p>
	    </div><div class='form-group'>
	    	<input type='checkbox' name='deleteThis'> Delete this channel
		<p class='help-block'>"._("** WARNING ** If checked, this channel will be permanently deleted")."</p>
	    </div>
	</form>";
    }

    /* ===========================================
    Pause QUEUE post
    =========================================== */
    if($ajaxAction == "togglePost") {
	$post_id = intval($_GET["ID"]);
        $result = doQuery("SELECT isActive FROM Posts WHERE userId='$myUser->ID' AND ID='$post_id';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    if($row["isActive"] == 1) {
		doQuery("UPDATE Posts SET isActive=0 WHERE ID='$post_id';");
		echo "<i class=\"fa fa-play\" aria-hidden=\"true\"></i>";
	    } else {
		doQuery("UPDATE Posts SET isActive=1 WHERE ID='$post_id';");
		echo "<i class=\"fa fa-pause\" aria-hidden=\"true\"></i>";
	    }
	}
    }

    /* ===========================================
    Delete QUEUE post
    =========================================== */
    if($ajaxAction == "deletePost") {
	$post_id = intval($_GET["ID"]);
        $result = doQuery("SELECT Title,sourceId,botId FROM Posts WHERE userId='$myUser->ID' AND ID='$post_id';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $post_title = stripslashes($row["Title"]);
	    $source_id = $row["sourceId"];
	    $bot_id = $row["botId"];

	    $source = new Source($source_id);

	    doQuery("DELETE FROM Posts WHERE ID='$post_id';");

	    $source->Log("Deleted post '$post_title' (ID: $post_id) from BOT queue $bot_id",MESSAGE_NOTICE);
	}
    }

    /* ===========================================
    Add or edit BLOG post
    =========================================== */

    if($ajaxAction == "editBlogPost") {
	$blog_post_id = '';
	$blog_post_title = '';
	$blog_post_content = '';

	if(!empty($_GET["id"])) {
	    $blog_post_id = intval($_GET["id"]);

	    $result = doQuery("SELECT ID,Title,Content,addDate FROM Blog WHERE ID='$blog_post_id';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		$blog_post_id = $row["ID"];
		$blog_post_title = stripslashes($row["Title"]);
		$blog_post_content = stripslashes($row["Content"]);
	    }
	}

	echo "<script>tinymce.init({ selector:'textarea' });</script>
	<form method='POST' id='ajaxDialog'>
	    <input type='hidden' name='action' value='editBlogPost'>
	    <input type='hidden' name='blogPostId' value='$blog_post_id'>
	    <div class='form-group'>
		<label for='blogTitle'>Title:</label>
		<input type='text' name='blogPostTitle' class='w-100 form-text validate[required]' id='blogPostTitle' value=\"$blog_post_title\">
	    </div><div class='form-group'>
		<label for='contents'>Content</label>
		<textarea name='blogPostContent' class='form-control' id='blogPostContent'>$blog_post_content</textarea>
	    </div>
	</form>";
    }

    /* ===========================================
    Resend welcome mail
    =========================================== */
    if($ajaxAction == 'welcomeMail') {
	$myUser->sendMail(getString("user-welcome-mail-subject"),getString("user-welcome-mail-body"));
	$mySession->sendMessage("Welcome mail sent to $myUser->eMail");
	echo "<span class='glyphicon glyphicon-envelope'></span>"._("Resend welcome mail");
    }

    /* ===========================================
    Show mail
    =========================================== */
    if($ajaxAction == 'mailShow') {

	$mailId = intval($_GET["id"]);
	$result = doQuery("SELECT userId,Subject,Body,tryCount,sentDate,addDate FROM Mails WHERE ID='$mailId';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $mail_id = $row["ID"];
	    $add_date = new DateTime($row["addDate"]);
	    if($row["sentDate"]) {
	        $sent_date = new DateTime($row["sentDate"]);
	    } else {
	        $sent_date = FALSE;
	    }

	    $to_user = new User($row["userId"]);
	    $mail_subject = stripslashes($row["Subject"]);
	    $mail_body = stripslashes($row["Body"]);
	    
	    echo "<h2>$mail_subject</h2>
	    $mail_body";
	}
    }

    if($ajaxAction == 'mailRemove') {
	$mailId = intval($_GET["id"]);
	doQuery("DELETE FROM Mails WHERE ID='$mailId';");
	echo "Removed";
    }

}

?>
