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
	    <h1><?php echo _("Sources"); ?></h1>
	    <div class="row top-buffer">
		<h4><i class="fa fa-cubes" aria-hidden="true"></i> <?php echo _("Sources"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("Type"); ?></th>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("Description"); ?></th>
			    <th><?php echo _("URL"); ?></th>
			    <th><?php echo _("Added on"); ?></th>
			    <th><?php echo _("Owner"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Sources;");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$source = new Source($row["ID"]);

			$owner = new user($source->userId);

			switch($source->Type) {
			    case 1:
				$type = "wordpress";
				break;
			    case 2:
				$type = "rss";
				break;
			    default:
				$type = "question-circle";
			}

			echo "<tr>
			    <td><i class=\"fa fa-$type\" aria-hidden=\"true\"></i>";

			if($source->botId == 0) {
			    echo "<i class=\"fa fa-fa-exclamation-triangle text-error\" aria-hidden=\"true\" alt=\"Warning: no BOT selected !\"></i>";

			}
			echo "</td>
			    <td>$source->ID</td>
			    <td>$source->Name</td>
			    <td>$source->Description</td>
			    <td><a href='$source->URL' target='_new'>$source->URL</a></td>
			    <td>".$source->addDate->format('m-d-Y h:m:s')."</td>
			    <td>$owner->displayName</td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?

include __DIR__.'/../common_footer.php';

?>
