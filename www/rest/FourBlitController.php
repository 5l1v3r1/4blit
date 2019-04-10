<?php

use RestServer\RestException;

class FourBlitController
{
    /**
     * Verify source connection 
     *
     * @url POST /verify
     */
    public function verify() {
	global $DB;

	$client_ip = getClientIP();
	
	$source_api_key = mysqli_real_escape_string($DB,$_POST["key"]);

	if(strlen($source_api_key) > 0) {
	    $result = doQuery("SELECT ID FROM Sources WHERE apiKey='$source_api_key';");
	    if(mysqli_num_rows($result) > 0) {
		$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
		
		$source = new Source($row["ID"]);
		
		LOGWrite(__CLASS__,"Received verify request from IP $client_ip for blog $source->Name: no errors detected");
	    
		return array("success" => "verified", "message" => "No errors detected");
	    } else {
		LOGWrite(__CLASS__,"Received verify request from IP $client_ip for blog $source->Name: returned ERROR, wrong api key");
		throw new RestException(403, "Wrong Api Key");
	    }
	} else {
	    LOGWrite(__CLASS__,"Received verify request from IP $client_ip for blog $source->Name: returned ERROR, missing api key");
	    throw new RestException(403, "Missing Api Key");
	}
    }

    /**
     * Register new source (like a WP blog...)
     *
     * @url POST /register
     */
    public function register() {
	global $DB;

	$client_ip = getClientIP();
	
	$source_api_key = mysqli_real_escape_string($DB,trim($_POST["key"]));

	$source_name = mysqli_real_escape_string($DB,$_POST["blog_name"]); // MANDATORY
	$source_description = mysqli_real_escape_string($DB,$_POST["blog_description"]);
	$source_admin_email = mysqli_real_escape_string($DB,$_POST["blog_admin_email"]); // MANDATORY
	$source_language = mysqli_real_escape_string($DB,$_POST["blog_language"]);
	$source_url = mysqli_real_escape_string($DB,$_POST["blog_url"]); // MANDATORY

	LOGWrite(__CLASS__,"Received new register request from IP $client_ip for blog $source_name");
	
	/* Check URL validity */
	if(!isURLAvailable($source_url)) {
	    throw new RestException(403, "Invalid Blog URL");
	    exit;
	}

	if(strlen($source_name) < 3) {
	    throw new RestException(403, "Blog NAME is mandatory");
	    exit;
	}

	if(strlen($source_admin_email) < 3) {
	    throw new RestException(403, "Blog ADMIN E-MAIL is mandatory");
	    exit;
	}

	if(strlen($source_url) < 3) {
	    throw new RestException(403, "Blog URL invalid or missing");
	    exit;
	}

	if(strlen($source_api_key) > 0) {
	    $result = doQuery("SELECT ID FROM Sources WHERE URL='$source_url' AND apiKey='$source_api_key';");
	    if(mysqli_num_rows($result) > 0) {
		/* OK, Blog is already present and api Key is valid... */
		LOGWrite(__CLASS__,"Reconnect with blog $source_url with ID $source_id ");
		return array("success" => "Blog reconnected successfully !", "apikey" => $source_api_key);
	    } else {
		throw new RestException(403, "Blog URL and apiKey doesn\'t match !");
		exit;
	    }
	} else {
	    /* Check for duplicate blog name using URL */
	    $result = doQuery("SELECT ID FROM Sources WHERE URL='$source_url';");
	    if(mysqli_num_rows($result) > 0) {
		throw new RestException(403, "This blog seems to be already registered !");
		exit;
	    }
	}

	/* Check if user already exists (email matching)... */
	$result = doQuery("SELECT ID FROM Users WHERE eMail='$source_admin_email';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $user_id = $row["ID"];
	    $user = new User($user_id);
	    LOGWrite(__CLASS__,"Detected user $user_id (IP: $client_ip) while registering blog $source_name");
	} else {
	    /* ...or create new user, asking for verification */
	    $OTP = APG(16);

	    doQuery("INSERT INTO Users(displayName,eMail,Level,isEnable,OTP,addDate) VALUES ('','$source_admin_email',1,0,'$OTP',NOW());");
	    $user_id = mysqli_insert_id($DB);

	    $user = new User($user_id);
	    $user->sendMail(getString("user-validate-mail-subject", array('%OTP%' => $OTP)),getString("user-validate-mail-body", array('%OTP%' => $OTP)));

	    LOGWrite(__CLASS__,"Added new user $user_id (IP: $client_ip) from blog $source_name");
	}

	/* Now create BLOG */
	$api_key = createApiKey();

	doQuery("INSERT INTO Sources(Type,botId,userId,Name,Description,adminEMail,Language,URL,apiKey,isEnable,addDate) VALUES ('1','','$user_id','$source_name','$source_description','$source_admin_email','$source_language','$source_url','$api_key',0,NOW());");
	$source_id = mysqli_insert_id($DB);

	LOGWrite(__CLASS__,"Registered new blog $source_id for user $user_id");
	
	$user->sendMessage(MESSAGE_WARNING,getString("user-source-waitapproval-subject", array('%sourceName%' => $source_name)),getString("user-source-waitapproval-body", array('%sourceName%' => $source_name,'%sourceUrl%' => $source_url,'%ownerEmail' => $user->eMail)));

	adminMessage(MESSAGE_WARNING,getString("admin-source-waitapproval-subject", array('%sourceName%' => $source_name)),getString("admin-source-waitapproval-body", array('%sourceName%' => $source_name,'%sourceUrl%' => $source_url,'%ownerEmail%' => $user->eMail)));
	
	return array("success" => "New blog registered successfully !", "apikey" => $api_key);
    }

    /**
     * Get post queue with publish status
     *
     * @url POST /queue
     * @url PUT /queue
     */
    public function queue() {
	global $DB;

	$api_key = substr(cleanInput($_POST["key"]),0,65);

	/* Get the Source by API Key.. */
    	$result = doQuery("SELECT ID FROM Sources WHERE apiKey='$api_key';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $source_id = $row["ID"];
	
	    $result = doQuery("SELECT ID FROM Posts WHERE sourceId='$source_id' ORDER BY addDate DESC LIMIT 10;");
	    if(mysqli_num_rows($result) > 0) {

		$res_array = array();

		while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		    $post = new Post($row["ID"]);
		
		    $res_array[] = array("id" => $post->ID, "title" => $post->Title, "added" => $post->addDate->format('h:m:s d-m-Y'), "published" => (is_null($post->publishDate) ? NULL:$post->publishDate->format('h:m:s d-m-Y')));
		}
		return array("success" => count($res_array), "result" => $res_array);
	    } else {
		return array("success" => NULL);
	    }
	} else {
	    throw new RestException(403, "Wrong Api Key: ".$api_key);
	}
    }

    /**
     * Publish a new post to broadcast
     *
     * @url POST /publish
     * @url PUT /publish
     */
    public function publish() {
	global $DB;

	$api_key = substr(cleanInput($_POST["key"]),0,65);

	$title = mysqli_real_escape_string($DB,$_POST["title"]);

	LOGWrite(__CLASS__,"Publish from ".getClientIP()." with key $api_key and title $title");

	$author = mysqli_real_escape_string($DB,$_POST["author"]);
	$excerpt = mysqli_real_escape_string($DB,$_POST["excerpt"]);
	$image_url = mysqli_real_escape_string($DB,$_POST["image_url"]);
	$url = mysqli_real_escape_string($DB,$_POST["url"]);
	$tags = $_POST["tags"];

	/* Get the Source by API Key.. */
    	$result = doQuery("SELECT ID FROM Sources WHERE apiKey='$api_key';");
	if(mysqli_num_rows($result) > 0) {
	    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	    $source_id = $row["ID"];

	    $source = new Source($source_id);

	    /* Calculate post hash... */
	    $hash = crc32(stripslashes($title).stripslashes($excerpt));

	    /* ...and compare with posts published tp avoid duplicates. */
	    $result = doQuery("SELECT ID FROM Posts WHERE Hash='$hash' AND sourceId='$source_id';");
    	    if(mysqli_num_rows($result) == 0) {
		/* Add post to posts queue... */
		doQuery("INSERT INTO Posts (userId,botId,sourceId,Title,Excerpt,Author,ImageURL,URL,Hash,isActive,addDate) VALUES ('$source->userId','$source->botId','$source_id','$title','$excerpt','$author','$image_url','$url','$hash',1,NOW());");
		$post_id = mysqli_insert_id($DB);

		/* Manage post TAGS... */
		if(strlen($tags) > 0) {
		    foreach(explode(',',$tags) as $tag) {
		        postAddTag($post_id,$tag);
		    }
		}

		doQuery("UPDATE Sources SET lastUpdate=NOW() WHERE ID='$source_id';");

		$source->Log("Post $post_id added to BOT $bot_id queue",MESSAGE_NOTICE);

		return array("success" => "Post $post_id added to BOT $bot_id queue");
	    } else {
	        LOGWrite(__CLASS__,"Error: duplicate post with hash $hash");
		$source->Log("Post $post_id not added because is a duplicate",MESSAGE_WARNING);

		throw new RestException(403, "Error: this POST seems to be a duplicate");
	    }
	} else {
	    throw new RestException(403, "Wrong Api Key: ".$api_key);
	}
    }
}