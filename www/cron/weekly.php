<?php

include __DIR__.'/../common.inc.php';

// ====================================================================================
// Prepara la mail settimanale di riepilogo
// ====================================================================================
$result = doQuery("SELECT ID FROM Users WHERE isEnable=1 AND Level > 1;");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$user = new User($row["ID"]);

	$user_id = $user->ID;
	
	$result2 = doQuery("SELECT ID,Name, Description, URL FROM Sources WHERE userId=$user_id;");
	if(mysqli_num_rows($result2) > 0) {
    	    while($row2 = mysqli_fetch_array($result2,MYSQLI_ASSOC)) {
		// Get numbers of post published by this source
		$source_id = $row2["ID"];
		$source_name = $row2["Name"];
		$source_description = $row2["Description"];
		$source_url = $row2["URL"];

		echo "$source_id";

		$result3 = doQuery("SELECT Day,numPosts,numClicks FROM SourcesStats WHERE DATE(Day)=DATE_SUB(CURDATE(), INTERVAL 1 WEEK) AND sourceId=$source_id");
		if(mysqli_num_rows($result3) > 0) {
    		    $row3 = mysqli_fetch_array($result3,MYSQLI_ASSOC);
    	    	    while($row3 = mysqli_fetch_array($result3,MYSQLI_ASSOC)) {
			$source_day = $row["Day"];
			$source_num_posts = $row["numPosts"];
			$source_num_clicks = $row["numClicks"];
			
			echo "$source_day $source_num_posts = $source_num_clicks\n";
		    }
		}
	    }
	} else {

	}
    }
}

