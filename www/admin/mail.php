<?php

include __DIR__.'/../common.inc.php';

if((!$mySession->isLogged())||(!$myUser->isAdmin())) {
    $mySession->sendMessage("Forbidden !","error");
    header('Location: /');
    exit();
}

include __DIR__.'/../common_header.php';

?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include __DIR__.'/../common_leftmenu.php'; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <div class="row">
		<h4><i class="fa fa-list" aria-hidden="true"></i> <?php echo _("Mail queue"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("To"); ?></th>
			    <th><?php echo _("Subject"); ?></th>
			    <th><?php echo _("Status"); ?></th>
			    <th><?php echo _("Requeued"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID,userId,Subject,tryCount,sentDate,addDate FROM Mails ORDER BY addDate DESC;");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$mail_id = $row["ID"];
			$add_date = new DateTime($row["addDate"]);
			if($row["sentDate"]) {
			    $sent_date = new DateTime($row["sentDate"]);
			} else {
			    $sent_date = FALSE;
			}
			$mail_retries = $row["tryCount"];

			$to_user = new User($row["userId"]);

			echo "<tr>
			    <td><a class='ajaxDialog' title='Show mail' href='/ajaxCb.php?action=mailShow&id=$mail_id'>$to_user->eMail</a></td>
			    <td>".stripslashes($row["Subject"])."</td>
			    <td>".($sent_date ? $sent_date->format('m-d-Y h:m:s'):"In queue")."</td>
			    <td>$mail_retries</td>
			    <td>
				<a class=\"ajaxCall\" title=\""._("Remove this mail")."\" href=\"/ajaxCb.php?action=mailRemove&id=$mail_id\">
				    <i class=\"fa fa-trash\" aria-hidden=\"true\"></i>
				</a>
			    </td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
	    </div>
	</div>
    </div>
</div>
<?php

include __DIR__.'/../common_footer.php';

?>
