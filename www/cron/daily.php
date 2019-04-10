<?php

include __DIR__.'/../common.inc.php';

// ====================================================================================
// Computa le statistiche per sorgente per post per clicks
// ====================================================================================
$result = doQuery("SELECT ID FROM Sources;");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$source_id = $row["ID"];

        $result2 = doQuery("SELECT SUM(Views) AS Views, COUNT(ID) AS Posts, DATE_SUB(CURDATE(), INTERVAL 1 DAY) AS DayDate FROM Posts WHERE sourceId='$source_id' AND DATE(publishDate)=DATE_SUB(CURDATE(), INTERVAL 1 DAY);");
	if(mysqli_num_rows($result2) > 0) {
	    $row2 = mysqli_fetch_array($result2,MYSQLI_ASSOC);
	
	    $source_views = intval($row2["Views"]);
	    $source_posts = intval($row2["Posts"]);
	    $source_day = $row2["DayDate"];

	    doQuery("INSERT INTO SourcesStats(Day,sourceId,numPosts,numClicks) VALUES ('$source_day','$source_id','$source_posts','$source_views');");
	}
    }
}
