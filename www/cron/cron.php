<?php

include __DIR__.'/../common.inc.php';

// ====================================================================================
// Mail queue
// ====================================================================================
$result = doQuery("SELECT ID,userId,Subject,Body FROM Mails WHERE sentDate IS NULL AND tryCount < 100 ORDER BY addDate,tryCount LIMIT 10;");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$mail_id = $row["ID"];
	$mail_user_id = $row["userId"];
	$mail_subject = $row["Subject"];
	$mail_body = $row["Body"];

	$user = new User($mail_user_id);
	if($user) {
	    $ret = sendMail($user->eMail,$user->displayName,$mail_subject,$mail_body);
	    if($ret === TRUE) {
		// Sent
	        LOGWrite(__FILE__,"Mail with subject $mail_subject to $user->eMail SENT !");
	        doQuery("UPDATE Mails SET sentDate=NOW() WHERE ID='$mail_id';");
	    } else {
		// Error
	        doQuery("UPDATE Mails SET tryCount=tryCount+1 WHERE ID='$mail_id';");
	    }
	} else {
	    echo "Error: unknown user $mail_user_id\n";
	}
    }
}

// ====================================================================================
// RSS Feeder
// ====================================================================================

$result = doQuery("SELECT ID FROM Sources WHERE Type=2;");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$source_id = $row["ID"];
	RSSHarvester($source_id,$error);
    }
}