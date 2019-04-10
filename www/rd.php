<?php

include "common.inc.php";

$ID = cleanInput($_GET["id"]);

$result = doQuery("SELECT URL,TIMESTAMPDIFF(HOUR,lastURLCheck,NOW()) AS lastCheck,isActive FROM Posts WHERE ID='$ID';");
if(mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
    
    $URL = stripslashes($row["URL"]);
    $isActive = $row["isActive"];
    $lastCheck = intval($row["lastCheck"]);

    if((!$lastCheck)||($lastCheck > 6)) {
	$isAvailable = isURLAvailable($URL);

	if(!$isAvailable) {
	    $isActive=false;
	    doQuery("UPDATE Posts SET lastURLCheck=NOW(),isActive=0 WHERE ID='$ID';");
	} else {
	    doQuery("UPDATE Posts SET lastURLCheck=NOW() WHERE ID='$ID';");
	}
    }

    if($isActive) {
	// Increase view counter...
	doQuery("UPDATE Posts SET Views=Views+1 WHERE ID='$ID';");
	// Redirect !
	header("Location: $URL", TRUE, 307);
//	header("refresh:1; url=$URL");
    } else {
	// Ooops, invalid URL: redirect to abuse page !
	include "common_header.php";
?>
<div class="container">
    <div class="row">
	<div class="clearfix">&nbsp;</div>
	<h1>Ooops !</h1>
	<p>The URL <i><?php echo $URL; ?></i> you are trying to visit seems in trouble now. Please try clicking on the following link or, if still don't work, try again in few minutes...</p>
	<div class="center-block">
	    <a class="btn btn-danger" href="<?php echo $URL; ?>"><?php echo $URL; ?></a>
	</div>
    </div>
</div>

<?php
	include "common_footer.php";
    }
} else {
    echo "Invalid ID";
}

?>