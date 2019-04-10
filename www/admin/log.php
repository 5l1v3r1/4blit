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
		<h4><i class="fa fa-list" aria-hidden="true"></i> <?php echo _("System Log"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("IP"); ?></th>
			    <th><?php echo _("Context"); ?></th>
			    <th><?php echo _("Description"); ?></th>
			    <th><?php echo _("Add Date"); ?></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT IP,Context,Description,addDate FROM Log WHERE TIMESTAMPDIFF(DAY,addDate,NOW()) < 2 ORDER BY addDate DESC;");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$log_date = new DateTime($row["addDate"]);
			echo "<tr>
			    <td>".$row["IP"]."</td>
			    <td>".$row["Context"]."</td>
			    <td>".stripslashes($row["Description"])."</td>
			    <td>".$log_date->format('m-d-Y h:m:s')."</td>
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
