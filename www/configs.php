<?php

include "common_header.php";

if(!$mySession->isLogged()) {
    header("Location: /");
    exit();
}
?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <h1><?php echo _("Configurations"); ?></h1>
<?php
if($myUser->Level < 2) {
?>
	    <div class="alert alert-warning" role="alert">
		<strong>Please !</strong> Check your mailbox <?php echo $myUser->eMail; ?> and follow instructions on activation email !
	    </div>
<?php
}
?>
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
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Sources WHERE userId='$myUser->ID';");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$source = new Source($row["ID"]);

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
			    <td><a class=\"ajaxDialog\" title=\""._("Edit source $source->Name")."\" href=\"/ajaxCb.php?action=editSource&ID=$source->ID\">
				<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Source $source->Name operation log")."\" href=\"/ajaxCb.php?action=checkSource&ID=$source->ID\">
				<i class=\"fa fa-heartbeat\" aria-hidden=\"true\"></i>
			    </a>";
			if($source->Type == 2) {
			    echo "<a class=\"ajaxDialog\" title=\""._("Fetch source $source->Name")."\" href=\"/ajaxCb.php?action=fetchSource&ID=$source->ID\">
				<i class=\"fa fa-cogs\" aria-hidden=\"true\"></i>
			    </a>";
			}
			echo "</td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
<?php
		if($myUser->getACL("maxSources") > mysqli_num_rows($result)) {
?>
		<div class="btn-group" role="group">
		    <a class="ajaxDialog btn btn-primary" title="<?php echo _("New RSS source"); ?>" href="/ajaxCb.php?action=editSource"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("New RSS source"); ?></a>
		</div>
		<br/><br/>
		<div class="alert alert-info" role="alert">
		    <?php echo _("<strong>Please note:</strong> Wordpress blogs are added automatically on plugin connection, so you don't need to add them manually"); ?>
		</div>
<?php
		}
?>
		<hr/>
	    </div><div class="row">
		<h4><i class="fa fa-users" aria-hidden="true"></i> <?php echo _("BOTs"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th></th>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("Last publish"); ?></th>
			    <th><?php echo _("Added on"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID FROM Bots WHERE userId='$myUser->ID';");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$tmpBot = new Bot($row["ID"]);

			echo "<tr>
			    <td>";
			if($tmpBot->errorCounter > 0) {
			    echo "<i class=\"fa fa-exclamation-triangle text-error\" aria-hidden=\"true\" alt=\"Warning: ".$tmpBot->lastError."\"></i>";
			} else {
			    echo "<i class=\"fa fa-check-circle-o text-success\" aria-hidden=\"true\" alt=\"Online\"></i>";
			}
			echo "</td>
			    <td>$tmpBot->ID</td>
			    <td>$tmpBot->Name</td>
			    <td>".$tmpBot->lastPublish->format('m-d-Y h:m:s')."</td>
			    <td>".$tmpBot->addDate->format('m-d-Y h:m:s')."</td>
			    <td><a href=\"/queue/$tmpBot->ID\" title=\""._("Show BOT $tmpBot->ID queue")."\">
				<i class=\"fa fa-database\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Edit BOT $tmpBot->ID")."\" href=\"/ajaxCb.php?action=editBot&ID=$tmpBot->ID\">
				<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>
			    </a><a class=\"ajaxDialog\" title=\""._("Re-join BOT $tmpBot->ID")."\" href=\"/ajaxCb.php?action=joinBot&ID=$tmpBot->ID\">
				<i class=\"fa fa-link\" aria-hidden=\"true\"></i>
			    </a></td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
<?php
		if($myUser->getACL("maxBots") > mysqli_num_rows($result)) {
?>
		    <div class="btn-group" role="group">
			<a class="ajaxDialog btn btn-primary" title="<?php echo _("New BOT"); ?>" href="/ajaxCb.php?action=editBot"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("New BOT"); ?></a>
		    </div>
<?php
		}
?>
		<hr/>
	    </div><div class="row">
		<h4><i class="fa fa-cloud" aria-hidden="true"></i> <?php echo _("Channels"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Type"); ?></th>
			    <th><?php echo _("Bot linked"); ?></th>
			    <th><?php echo _("Added on"); ?></th>
			    <th><?php echo _("Last update"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT t1.ID,t1.botId,t1.Type,t1.addDate,t2.chgDate FROM Chats AS t1 JOIN Bots AS t2 ON t1.botId=t2.ID WHERE t2.userId='$myUser->ID';");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$channel_id = $row["ID"];
			$channel_bot_id = $row["botId"];
			$channel_type = $row["Type"];
			$channel_adddate = new DateTime($row["addDate"]);
			$channel_chgdate = new DateTime($row["chgDate"]);

			echo "<tr>
			    <td>$channel_id</td>
			    <td>$channel_type</td>
			    <td>$channel_bot_id</td>
			    <td>".$channel_adddate->format('H:m:s m-d-Y')."</td>
			    <td>".$channel_chgdate->format('H:m:s m-d-Y')."</td>
			    <td>";
			if($channel_type === 'channel') {
			    echo "<a class=\"ajaxDialog\" title=\""._("Edit Channel $channel_id")."\" href=\"/ajaxCb.php?action=editChannel&ID=$channel_id\">
				<i class=\"fa fa-pencil-square-o\" aria-hidden=\"true\"></i>
				</a>
				<a href='tg://resolve?domain=$channel_id'><i class=\"fa fa-paper-plane-o\" aria-hidden=\"true\"></i></a>";
			}
			echo "</td>
			</tr>";
		    }
		}
?>	
		</tbody></table>
<?php
		if($myUser->getACL("maxChannels") > mysqli_num_rows($result)) {
?>

		<div class="btn-group" role="group">
		    <a class="ajaxDialog btn btn-primary" title="<?php echo _("Add Telegram Channel"); ?>" href="/ajaxCb.php?action=editChannel"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("Add Telegram Channel"); ?></a>
		</div>
<?php
		}
?>
		<hr/>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include "common_footer.php";

?>
