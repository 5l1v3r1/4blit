<?php

include "common_header.php";

if($mySession->isLogged()) {
// LOGGED USERS
?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
<?php
if(!empty($_GET["r"])) {
    $inbox_id = intval($_GET["r"]);
    $result = doQuery("SELECT Type, Title, Content, replyTo, isRead, addDate FROM UserMessages WHERE userId='$myUser->ID' AND ID='$inbox_id';");
    if(mysqli_num_rows($result) > 0) {
	$row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$inbox_type = $row["Type"];
	$inbox_title = stripslashes($row["Title"]);
	$inbox_content = stripslashes($row["Content"]);
	$inbox_replyto = new User($row["replyTo"]);
	$inbox_is_read = $row["isRead"];
	$inbox_adddate = new DateTime($row["addDate"]);
?>
	<h2><?php echo $inbox_title; ?></h2>
	<p class="lead">
	    <?php echo $inbox_content; ?>
	</p>
	<p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?php printf(_("Received on %s"),$inbox_adddate->format("d-m-Y")); ?></small></p>
	<div class="btn-group" role="group">
	    <button type="button" class="btn btn-secondary"><a href="/inbox"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i> <?php echo _("Back"); ?></a></button>
	    <button type="button" class="btn btn-secondary"><a class="ajaxDialog" title="<?php echo _("Reply to $inbox_replyto->displayName"); ?>" href="/ajaxCb.php?action=userSendMessage&ID=<?php echo $inbox_replyto->ID; ?>"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("Reply to $inbox_replyto->displayName"); ?></a></button>
	    <button type="button" class="btn btn-secondary"><a class="ajaxDialog" title="<?php echo _("Delete"); ?>" href="/ajaxCb.php?action=userDeleteMessage&ID=<?php echo $inbox_id; ?>"><i class="fa fa-trash" aria-hidden="true"></i> <?php echo _("Delete"); ?></a></button>
	</div>
<?php
	/* Mark as read */
	doQuery("UPDATE UserMessages SET isRead=1,readDate=NOW() WHERE ID='$inbox_id';");
    } else {
	echo "<h2>Invalid ID</h2>
	<p><a href='/inbox'>Go back and try again</a></p>";
    }
} else {
    $p = (isset($_GET["p"]) ? intval($_GET["p"]):0);
    $p_offset = $p * 10;
    $result = doQuery("SELECT ID FROM UserMessages WHERE userId='$myUser->ID';");
    $p_max = ceil(mysqli_num_rows($result) / 10);
?>
	    <div class="row"><!-- ALL MAILS -->
		<h4><i class="fa fa-envelope-o" aria-hidden="true"></i> <?php printf(_("%s's Inbox"),$myUser->displayName); ?></h4>
		<table class="table table-hover">
		    <thead>
			    <tr>
				<th></th>
				<th><?php echo _("Title"); ?></th>
				<th></th>
				<th><?php echo _("Date"); ?></th>
				<th></th>
			    </tr>
			</thead>
			<tbody id="inboxList">
<?php
		    $result = doQuery("SELECT ID, Type, Title, Content, isRead, addDate FROM UserMessages WHERE userId='$myUser->ID' ORDER BY addDate DESC LIMIT 10 OFFSET $p_offset;");
		    if(mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			    $inbox_id = $row["ID"];
			    $inbox_type = $row["Type"];
			    $inbox_title = stripslashes($row["Title"]);
			    $inbox_content = stripslashes($row["Content"]);
			    $inbox_is_read = $row["isRead"];
			    $inbox_adddate = new DateTime($row["addDate"]);

			    echo "<tr class=\"".($inbox_is_read ? "":"table-info")." clickable-row\" data-href=\"/inbox?r=$inbox_id\">
			    <td></td>
			    <td>$inbox_title</td>
			    <td>".getExcerpt(strip_tags($inbox_content),20)."</td>
			    <td>".$inbox_adddate->format('d-m-Y h:m:s')."</td>
			    </tr>";
			}
		    } else {
			echo "<tr><td colspan=4>No messages</td></tr>";
		    } 
?>
		</tbody></table>
		<nav aria-label="Page navigation">
		    <ul class="pagination">
<?php
			if($p > 0) {
			    echo "<li class='page-item'><a class='page-link' href='?p=".($p-1)."'>Prev</a></li>";
			}
			for($c=1;$c<$p_max;$c++) {
			    echo "<li class='page-item'><a class='page-link' href='?p=$c' ".(($c==$p)?'active':'').">$c</a></li>";
			}
			if($p < $p_max) {
			    echo "<li class='page-item'><a class='page-link' href='?p=".($p+1)."'>Next</a></li>";
			}
?>
		    </ul>
		</nav>
	    </div><!-- /ALL MAILS -->
<?php
}
?>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->

<?php
} else {
    header("Location: /");
}

include "common_footer.php";

?>
