<?php

use Phpcsp\Security\ContentSecurityPolicyHeaderBuilder;

use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;

include_once __DIR__.'/config.inc.php';
require_once __DIR__.'/Hybrid/autoload.php';
require_once __DIR__.'/PHPMailerAutoload.php';
require_once __DIR__.'/PhpConsole/__autoload.php';

include __DIR__.'/SimplePieAutoloader.php';

include __DIR__ . '/Phpcsp/Security/ContentSecurityPolicyHeaderBuilder.php';

if((function_exists('locale_accept_from_http'))&&(isset($_SERVER['HTTP_ACCEPT_LANGUAGE']))) {
    $gbLang = locale_accept_from_http($_SERVER['HTTP_ACCEPT_LANGUAGE']);
} else if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
    $gbLang = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
} else {
    $gbLang = "en_US"; /* Default */
}

if(file_exists(__DIR__."/lang/$gbLang.php")) {
    include __DIR__."/lang/$gbLang.php";
} else {
    include __DIR__."/lang/default.php";
}

setlocale(LC_ALL, $gbLang);
bindtextdomain("messages","./lang");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");

$sessionId = session_id();

if(empty($sessionId)) {
    session_start();
    $sessionId = session_id();
}

$phpConsole = PhpConsole\Handler::getInstance();
$phpConsole->start(); // start handling PHP errors & exceptions
$phpConsole->getConnector()->setSourcesBasePath($_SERVER['DOCUMENT_ROOT']); // so files paths on client will be shorter (optional)

PhpConsole\Helper::register(); // required to register PC class in global namespace, must be called only once

// header("Content-Security-Policy: default-src *");
// header("X-Content-Security-Policy: default-src *");
// header("X-WebKit-CSP: default-src *");

$DB = OpenDB();

$mySession = new Session($sessionId);

$hybridAuth = new Hybridauth($hybridAuthConfig);

if($mySession->isLogged()) {
    $myUser = new User($mySession->userId);
}

function is_cli() {
    return (php_sapi_name() === 'cli');
}

function doQuery($query) {
    global $DB;
    $result = mysqli_query($DB,$query);
    if($result == FALSE) {
	LOGWrite($_SERVER["SCRIPT_NAME"],"DB Query Error: ".mysqli_error($DB)." ($query)");
	exit();
    }
    usleep(500);
    return $result;
}

function OpenDB() {
    global $CFG;
    
    $db = mysqli_connect($CFG["dbHost"],$CFG["dbUser"],$CFG["dbPassword"],$CFG["dbName"]);
    if($db == false) {
        echo "Error connecting to MySQL DB @ ".$CFG["dbHost"]." !";
        die();
    }

    return $db;
}

function isSelected($value,$match) {
    if($value == $match) return "selected";
}

function isChecked($value) {
    if($value) return "checked";
}

function getClientIP() {
    if(getenv('HTTP_X_FORWARDED_FOR')) {
	return getenv('REMOTE_ADDR')." (".getenv('HTTP_X_FORWARDED_FOR').")";
    } else {
	return getenv('REMOTE_ADDR');
    }
}

function cleanInput($u_Input) {
    $banlist = array (
	" insert ", " select ", " update ", " delete ", " distinct ", " having ", " truncate ", " replace ",
	" handler ", " like ", " as ", " or ", " procedure ", " limit ", " order by ", " group by ", " asc ", " desc "
    );
    $replacelist = array (
	" ins3rt ", " s3lect ", " upd4te ", " d3lete ", " d1stinct ", " h4ving ", " trunc4te ", " r3place ",
	" h4ndler ", " l1ke ", " 4s ", " 0r ", " procedur3 ", " l1mit ", " 0rder by ", " gr0up by ", " 4sc ", " d3sc "
    );
    if(preg_match( "/([a-zA-Z0-9])/", $u_Input )) {
	$u_Input = trim(str_replace($banlist, $replacelist, $u_Input));
    } else {
	$u_Input = NULL;
    }
    return $u_Input;
}

function APG($nChar=5) {
    $salt = "abcdefghjkmnpqrstuvwxyzABCDEFGHJKMNPQRSTUVWXYZ0123456789";
    srand((double)microtime()*1000000); 
    $i = 0;
    $pass = '';
    while ($i <= $nChar) {
	$num = rand() % strlen($salt);
        $tmp = substr($salt, $num, 1);
        $pass = $pass . $tmp;
        $i++;
    }
    return $pass;
}

function createApiKey() {
    while(1) {
	$key = APG(64);
	$result = doQuery("SELECT ID FROM Sources WHERE apiKey='$key';");
	if(mysqli_num_rows($result) > 0) {
	    continue;
	} else {
	    break;
	}
    }
    return $key;
}

function sendMail($email,$display_name,$subject,$message) {
    global $CFG;
    $mail = new PHPMailer(true); // the true param means it will throw exceptions on errors, which we need to catch
    $mail->IsSMTP(); // telling the class to use SMTP

    $mail->SMTPSecure = false;

    //Whether to use SMTP authentication
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = "info@4bl.it";
    //Password to use for SMTP authentication
    $mail->Password = "4ll4round";

    try {
	$mail->Host       = "smtp-out.kpnqwest.it"; // SMTP server
	$mail->Port       = 25;
        $mail->AddReplyTo('no-reply@4bl.it', '4bl.it');
        $mail->AddAddress($email, $display_name);
	    
        $mail->SetFrom('info@4bl.it', '4bl.it');
    
	$mail->Subject = "[4bl.it] $subject";
        $mail->MsgHTML($message);
        $mail->AltBody = strip_tags($message);
        $mail->send();

	return true;
    } catch (phpmailerException $e) {
        LOGWrite(__METHOD__,"Error: ".$e->errorMessage());
    } catch (Exception $e) {
        LOGWrite(__METHOD__,"Error: ".$e->errorMessage());
    }
    return false;
}

function LOGWrite($context,$description) {
    global $DB;
    $IP = GetClientIP();
    $myContext = mysqli_real_escape_string($DB,$context);
    $myDescription = mysqli_real_escape_string($DB,$description);
    doQuery("INSERT INTO Log (addDate,IP,Context,Description) VALUES (NOW(),'$IP','$myContext','$myDescription')");
}

function getString($idx,$argsArray=array()) {
    global $TLANG;
    
    if(strlen($TLANG[$idx]) > 0) {
        /* Esiste la stringa */
        if(is_array($argsArray)) {
	    $defArray = array_keys($argsArray);
	    $myString = str_replace($defArray,$argsArray,$TLANG[$idx]);
	}
	return $myString;
    } else {
	return "[$idx:".var_export($argsArray,true)."]";
    }
}

function isURLAvailable($url) {
    //check, if a valid url is provided
    if(!filter_var($url, FILTER_VALIDATE_URL)) {
	return false;
    }

    //initialize curl
    $curlInit = curl_init($url);
    curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
    curl_setopt($curlInit,CURLOPT_HEADER,true);
    curl_setopt($curlInit,CURLOPT_NOBODY,true);
    curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

    //get answer
    $response = curl_exec($curlInit);

    $httpCode = curl_getinfo($curlInit, CURLINFO_HTTP_CODE);

    curl_close($curlInit);

    if($httpCode == 404) {
	// 404 
	return false;
    } else {
	return true;
    }
}

function isEmailValid($email) {
    return !!filter_var($email, FILTER_VALIDATE_EMAIL);
}

function getExcerpt($string, $length=55) {
    $suffix = '&hellip;';
    $text = trim(str_replace(array("\r","\n", "\t"), ' ', strip_tags($string)));

    $words = explode(' ', $text, $length + 1);
    if (count($words) > $length) {
        array_pop($words);
        array_push($words, '[...]');
        $text = implode(' ', $words);
    }
    return $text;
}

function sanitizeString($text) {
    $strFind = array('<<','>>','<','>');
    $strReplace = array('⟪','⟫','〈','〉');

    $text = html_entity_decode($text, ENT_COMPAT, 'UTF-8');
    $text = str_replace($strFind,$strReplace,$text);
    return $text;
}

function getTag($tag_id) {
    $result = doQuery("SELECT Tag FROM Tags WHERE ID='$tag_id';");
    if(mysqli_num_rows($result) > 0) {
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	return preg_replace('/[^\x20-\x7E]/','', $row["Tag"]);
    } else {
	return false;
    }
}


class Session {
    var $ID;
    var $userId=false;
    var $Opts=array();
    
    function __construct($ID) {
	/* Cancella tutte le sessioni piu vecchie di 1 ora */
	doQuery("DELETE FROM Sessions WHERE HOUR(TIMEDIFF(NOW(),lastAction)) > 1;");
	/* Procedi... */
	doQuery("INSERT INTO Sessions (ID,IP) VALUES ('$ID','".getClientIP()."') ON DUPLICATE KEY UPDATE lastAction=NOW();");
	$this->ID = $ID;
	
	$result = doQuery("SELECT userId,Opts FROM Sessions WHERE ID='$ID';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $this->userId = $row["userId"];
	    if(!empty($row["Opts"])) {
		foreach(unserialize(stripslashes($row["Opts"])) as $key => $value) {
	    	    $this->Opts[$key] = $value;
		}
	    }
	} else {
	    $this->userId = false;
	}
    }   
    
    function __destruct() {
	global $DB;
	// Save ACL array
	$tmp_opts = mysqli_real_escape_string($DB,serialize($this->Opts));
	doQuery("UPDATE Sessions SET Opts='$tmp_opts' WHERE ID='$this->ID';");
    }

    function sendMessage($message, $type='information') {
	global $DB;
	/* Types: 'alert', 'information', 'error', 'warning', 'notification', 'success' */
	doQuery("INSERT INTO SessionMessages (sessionId,Type,Message,addDate) VALUES ('$this->ID','$type','".mysqli_real_escape_string($DB,$message)."',NOW());");
    }

    function isLogged() {
	if($this->userId > 0) {
	    return true;
	} else {
	    return false;
	}
    }

    public function getOpt($key) {
    	return $this->Opts[$key];
    }

    public function setOpt($key,$value) {
	$this->Opts[$key] = $value;
    }

    function getNonce() {
	$nonce = md5(APG(10));
	$this->setOpt('nonce',$nonce);
	return $nonce;
    }

    function checkNonce($nonce) {
	$tmp_nonce = $this->getOpt('nonce');
	$this->setOpt('nonce',FALSE);
	if(strcmp($nonce,$tmp_nonce)==0) {
	    return true;
	} else {
	    return false;
	}
    }

}

class Channel {
    var $ID;
    var $botId;
    var $AVP;
    var $isEnable;
    var $addDate;
    var $chgDate;

    function __construct($ID) {
	$result = doQuery("SELECT botId, AVP, isEnable, addDate, chgDate FROM Chats WHERE ID='$ID' AND Type='channel';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $this->ID = $ID;
	    $this->botId = $row["botId"];

	    if($row["AVP"]) {
	        $this->AVP = unserialize($row["AVP"]);
	    }

	    $this->isEnable = $row["isEnable"];

	    $this->addDate = new DateTime($row["addDate"]);
	    $this->chgDate = new DateTime($row["chgDate"]);
	}
    }
}

function getUserFromOTP($OTP) {
    $result = doQuery("SELECT ID FROM Users WHERE OTP='$OTP' AND isEnable=0;");
    if(mysqli_num_rows($result) > 0) {
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	return $row["ID"];
    } else {
	return false;
    }
}

function adminMessage($type,$subject,$message) {
    LOGWrite(__FUNCTION__,"Send admin message $subject");
    $result = doQuery("SELECT ID FROM Users WHERE Level='".USER_ADMINISTRATOR."';");
    if(mysqli_num_rows($result) > 0) {
	while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	    $user = new User($row["ID"]);
	    if($user) {
		$user->sendMessage($type,$subject,$message);
	    }
	}
    }
}

class User {
    var $ID;
    var $displayName;
    var $eMail;
    var $loginETA;
    var $ACL=array();
    var $Level;
    var $isEnable=false;

    function __construct($ID) {
	$result = doQuery("SELECT ID,displayName,eMail,ACL,isEnable,DATEDIFF(NOW(),lastLogin) AS loginETA,Level FROM Users WHERE ID='$ID';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $this->ID = $row["ID"];
	    $this->displayName = stripslashes($row["displayName"]);
	    $this->eMail = stripslashes($row["eMail"]);
	    $this->isEnable = $row["isEnable"];
	    $this->Level = $row["Level"];
	    $this->loginETA = $row["loginETA"];
	    if($row["ACL"]) {
		$this->ACL = unserialize(stripslashes($row["ACL"]));
	    }
	}
    }

    function getLevel() {
	switch($this->Level) {
	    case USER_FREEPERSONAL: 
		return "Free personal";
	    case USER_PATRON: 
		return "Patron";
	    case USER_ADMINISTRATOR:
		return "Administrator";
	    default:
		return "Unknown";
	}
    }

    function isAdmin() {
	if($this->Level == USER_ADMINISTRATOR) { 
	    return true;
	}
	return false;
    }

    function getACL($ACL) {
	global $defaultLevel;
	$ACLLevel = $defaultLevel[$this->Level];
	if(isset($ACL)) {
	    if(array_key_exists($ACL,$ACLLevel)) {
		return $ACLLevel[$ACL];
	    }
	}
	return false;
    }

    function sendMail($subject,$message,$adminCc=false) {
	global $CFG;
	global $DB;

	$mail_subject = str_replace(array("%userName%","%eMail%"),array($this->displayName,$this->eMail),$subject);
	$mail_body = str_replace(array("%body%","%title%","%eMail%","%userName%","%eMail%"),array($message,$subject,$this->eMail,$this->displayName,$this->eMail),$CFG["mailTemplate"]);

	LOGWrite(__METHOD__,"Send mail to $this->eMail with subject $subject");

	doQuery("INSERT INTO Mails(userId,Subject,Body,tryCount,addDate) VALUES ('$this->ID','".mysqli_real_escape_string($DB,$mail_subject)."','".mysqli_real_escape_string($DB,$mail_body)."',0,NOW());");
    }

    function sendMessage($type, $subject, $message, $sendMail=false) {
	global $CFG;
	global $DB;

	doQuery("INSERT INTO UserMessages(userId,Type,Title,Content,addDate) VALUES ('$this->ID','$type','".mysqli_real_escape_string($DB,$subject)."','".mysqli_real_escape_string($DB,$message)."',NOW());");

	if($sendMail) {
	    $excerpt = getExcerpt($mex_body);

	    $this->sendMail(getString("mail-admin-message-subject",array("%subject%" => $mex_subject)),getString("mail-admin-message-body",array("%body%" => $excerpt)),true);
	}
    }
}

class Bot {
    var $ID;
    var $userId;
    var $Name;
    var $botToken;
    var $isEnable;
    var $errorCounter;
    var $lastError;
    var $publishDelay;
    var $lastPublish;
    var $addDate;

    function __construct($ID) {
        $result = doQuery("SELECT ID, userId, Name, botToken, isEnable, publishDelay, errorCounter, lastError, lastPublish, addDate FROM Bots WHERE ID='$ID';");
        if(mysqli_num_rows($result) > 0) {
    	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    
	    $this->ID = $ID;
	    $this->userId = $row["userId"];
	    $this->Name = stripslashes($row["Name"]);
	    $this->botToken = stripslashes($row["botToken"]);
	    $this->errorCounter = intval($row["errorCounter"]);
	    $this->lastError = stripslashes($row["lastError"]);
	    $this->isEnable = $row["isEnable"];
	    $this->publishDelay = $row["publishDelay"];
	    if(empty($row["lastPublish"])) {
		$this->lastPublish = false;
	    } else {
		$this->lastPublish = new DateTime($row["lastPublish"]);
	    }
	    $this->addDate = new DateTime($row["addDate"]);
	} else {
	    return false;
	}
    }

}

class Source {
    var $ID;
    var $Type; // 0=Unknown, 1=Plugin, 2=RSS Feed
    var $botId;
    var $userId;
    var $Name;
    var $Description;
    var $adminEMail;
    var $Lang;
    var $URL;
    var $ACL;
    var $apiKey;
    var $sourceIp;
    var $isPublic;
    var $addDate;

    function __construct($ID) {
        $result = doQuery("SELECT ID, Type, botId, userId, Name, Description, adminEMail, Language, URL, ACL, apiKey, sourceIp, isPublic, addDate FROM Sources WHERE ID='$ID';");
        if(mysqli_num_rows($result) > 0) {
    	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    
	    $this->ID = $ID;
	    $this->Type = $row["Type"];
	    $this->botId = $row["botId"];
	    $this->userId = $row["userId"];
	    $this->Name = stripslashes($row["Name"]);
	    $this->Description = stripslashes($row["Description"]);
	    $this->adminEMail = stripslashes($row["adminEMail"]);
	    $this->Language = $row["Language"];
	    $this->URL = stripslashes($row["URL"]);
	    $this->ACL = unserialize($row["ACL"]);
	    $this->apiKey = $row["apiKey"];
	    $this->sourceIp = $row["sourceIp"];
	    $this->isPublic = $row["isPublic"];
	    $this->addDate = new DateTime($row["addDate"]);
	} else {
	    return false;
	}
    }

    function __destruct() {
	doQuery("UPDATE Sources SET ACL='".serialize($this->ACL)."' WHERE ID='$this->ID';");
    }

    function setAcl($key,$value) {
	$this->ACL[$key] = $value;
    }

    function getAcl($key) {
	return $this->ACL[$key];
    }

    function Log($message,$type=MESSAGE_NOTICE) {
	global $DB;
	$result = doQuery("SELECT ID,Message FROM SourcesLog WHERE sourceId='$this->ID' ORDER BY addDate DESC LIMIT 1");
	if(mysqli_num_rows($result) > 0) {
    	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    if(strcmp(stripslashes($row["Message"]),$message) == 0) {
		$id = $row["ID"];
		doQuery("UPDATE SourcesLog SET numRepeat=numRepeat+1,lastEvent=NOW() WHERE ID='$id';");
		return;
	    }
	}
	doQuery("INSERT INTO SourcesLog(sourceId, Type, Message, addDate) VALUES ('$this->ID','$type','".mysqli_real_escape_string($DB,$message)."',NOW());");
    }
}

function postAddTag($post_id,$tag) {
    global $DB;
    $tag = strtolower(trim($tag));
    
    if(strlen($tag) > 0) {
	$result = doQuery("SELECT ID FROM Tags WHERE Tag LIKE \"$tag\";");
	if(mysqli_num_rows($result) > 0) {
    	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $tag_id = $row["ID"];
	} else {
	    doQuery("INSERT INTO Tags(Tag) VALUES (\"".mysqli_real_escape_string($DB,$tag)."\");");
	    $tag_id = mysqli_insert_id($DB);
	}
	doQuery("INSERT INTO PostTags(postId,tagId,addDate) VALUES ('$post_id','$tag_id',NOW());");

	LOGWrite(__METHOD__,"Add TAG $tag ($tag_id) to POST $post_id");

	return true;
    } else {
	return false;
    }
}

class Post {
    var $ID;
    var $Title;
    var $Excerpt;
    var $Author;
    var $imageUrl;
    var $URL;
    var $addDate;
    var $publishDate;
    var $isPublished;
    var $isActive;
    var $Views;
    var $sourceId;
    var $botId;

    function __construct($ID) {
        $result = doQuery("SELECT sourceId, botId, Title, Excerpt, Author, ImageURL, URL, Views, isPublished, isActive, addDate, publishDate FROM Posts WHERE ID='$ID';");
        if(mysqli_num_rows($result) > 0) {
    	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $this->Title = strip_tags(stripslashes($row["Title"]));
	    $this->Excerpt = strip_tags(stripslashes($row["Excerpt"]));
	    $this->Author =  stripslashes($row["Author"]);
	    $this->imageUrl = stripslashes($row["ImageURL"]);
	    $this->URL = stripslashes($row["URL"]);
	    $this->Views = stripslashes($row["Views"]);
	    $this->isPublished = $row["isPublished"];
	    $this->isActive = $row["isActive"];
	    $this->addDate = new DateTime($row["addDate"]);
	    if(is_null($row["publishDate"])) {
		$this->publishDate = NULL;
	    } else {
		$this->publishDate = new DateTime($row["publishDate"]);
	    }
	    $this->ID = $ID;
	    $this->sourceId = $row["sourceId"];
	    $this->botId = $row["botId"];
	}
    }

    function setPublished() {
	$this->isPublished = true;
	/* Set post published... */
	doQuery("UPDATE Posts SET isPublished=1, publishDate=NOW() WHERE ID='$this->ID';");
	/* Update also lastPublish on BOT */
	doQuery("UPDATE Bots SET lastPublish=NOW() WHERE ID='$this->botId';");
    }

    function getAddDate($format='Y-m-d H:i:s') {
	return $this->addDate->format($format);
    }

    function getUniqueURL() {
	return "http://www.4bl.it/rd/$this->ID";
    }
}

function RSSHarvester($source_id,&$error=false) {
    global $DB;

    $source = new Source($source_id);

    $bot_id = $source->botId;
    $user_id = $source->userId;

    if($bot_id <= 0) {
	$error = "No BOT linked to this source";
	return false;
    }

    if(is_cli()) {
	echo "Harvesting RSS for source ID $source_id (URL: $source->URL)...\n";
    }

    if(isURLAvailable($source->URL)) {
	try {
	    $feed = new SimplePie();
    	    $feed->set_feed_url($source->URL);
	    $feed->set_cache_location(__DIR__.'/temp');
	    $feed->set_cache_duration(3600);
    	    $feed->init();
	    $feed->handle_content_type();
	} catch (Exception $e) {
	    $error = "Exception ".$e->getMessage();
	    $source->Log($error);
	    if(is_cli()) {
		echo "Exception: ".$e->getMessage();
	    }
	    return false;
	}
        if ($feed->error()) {
    	    LOGWrite($_SERVER["SCRIPT_NAME"],"Error while harvesting source URL ".$source->URL." (ID $source_id): ".$feed->error());
	    $error = "Error while harvesting source URL ".$source->URL." (ID $source_id): ".$feed->error();
	    $source->Log($error,MESSAGE_ERROR);
	    if(is_cli()) {
		echo "Feed error: ".$feed->error();
	    }
	    return false;
	}

	$max = $feed->get_item_quantity();

	if(is_cli()) {
	    echo "Parsing $max feed items...\n";
	}

	if ($max > 0) {
	    for ($x = 0; $x < $max; $x++) {

		if(is_cli()) {
		    echo "Item $x of $max START\n";
		}

    		$item = $feed->get_item($x);

		if($source->getACL('maskAuthor') === true) {
	    	    $author = $source->Name;
		} else if($author = $item->get_author()) {
	    	    $author = $author->get_name();
		} else {
	    	    $author = $source->Name;
		}
	        $title = mysqli_real_escape_string($DB,$item->get_title());
		$excerpt = mysqli_real_escape_string($DB,getExcerpt($item->get_content()));
		$url = $item->get_permalink();

	        $hash = crc32(stripslashes($title).stripslashes($excerpt));
    
		if ($enclosure = $item->get_enclosure()) {
    	    	    $image_url = $enclosure->get_thumbnail();
		} else {
	    	    $image_url = "";
		}

		/* ...compare with posts published to avoid duplicates. */
		$result2 = doQuery("SELECT ID FROM Posts WHERE (Hash='$hash' OR URL='$url') AND botId='$bot_id';");
    		if(mysqli_num_rows($result2) == 0) {
	    	    /* Add post to posts queue... */
	    	    doQuery("INSERT INTO Posts (userId,botId,sourceId,Title,Excerpt,Author,ImageURL,URL,Hash,isActive,addDate) VALUES ('$user_id','$bot_id','$source_id','$title','$excerpt','".mysqli_real_escape_string($DB,$author)."','$image_url','$url','$hash',1,NOW());");
	    	    $post_id = mysqli_insert_id($DB);

	    	    /* Get post categories just to use it as tags... */
	    	    if ($enclosure = $item->get_enclosure()) {
	    		foreach ((array) $enclosure->get_categories() as $category) {
		    	    postAddTag($post_id,$category->get_label());
			}
		    }
		
		    doQuery("UPDATE Sources SET chgDate=NOW() WHERE ID='$source_id';");
		
		    LOGWrite($_SERVER["SCRIPT_NAME"],"Post $post_id added to BOT $bot_id queue...");

		    if(is_cli()) {
			echo "Post $post_id added to BOT $bot_id queue...\n";
		    }

		    $source->Log("Post $post_id added to BOT $bot_id queue",MESSAGE_NOTICE);
		}
		if(is_cli()) {
		    echo "Item $x of $max END\n";
		}
	    } 
	}
	if(is_cli()) {
	    echo "Harvested RSS for source ID $source_id (URL: $source->URL) COMPLETE\n";
	}
	return true;
    } else {
	$error = "URL $source->URL is not available";
	$source->Log("URL $source->URL is not available",MESSAGE_ERROR);

	if(is_cli()) {
	    echo "URL $source->URL is not available\n";
	}

	return false;
    }
}

?>