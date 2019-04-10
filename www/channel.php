<?php

include "common_header.php";

if(empty($_GET["id"])) {
    // Show complete list of public channels
?>
<div class="jumbotron">
    <div class="container"><!-- CONTAINER -->
	<h1><?php echo _("Public channels"); ?></h1>
	<p class="lead"><?php echo _("Looking for something to read ? Here's a list of public channels broadcasted by 4blit... "); ?></p>
    </div><!-- /CONTAINER -->
</div>
<div class="container">
    <div class="row">
	<table class="table table-hover">
	<thead>
	    <tr>
		<th><?php echo _("Name"); ?></th>
		<th><?php echo _("Description"); ?></th>
		<th><?php echo _("Channel"); ?></th>
	    </tr>
	</thead>
	<tbody>
<?php
    $result = doQuery("SELECT ID FROM Sources WHERE isPublic=1;");
    if(mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	    $tmpSource = new Source($row["ID"]);

	    $chan = '';
	    $result2 = doQuery("SELECT ID FROM Chats WHERE botId='$tmpSource->botId' AND Type='channel';");
	    if(mysqli_num_rows($result2) > 0) {
	        $row2 = mysqli_fetch_array($result2,MYSQLI_ASSOC);
		$chan = $row2["ID"];
	    }

	    echo "<tr>
	        <td><a href='/channel/$tmpSource->ID'>$tmpSource->Name</a></td>
	        <td>$tmpSource->Description</td>
		<td><a href='tg://resolve?domain=$chan'>$chan</a></td>
	    </tr>";
    	}
	mysqli_free_result($result);
    }
?>	
	</tbody></table>
    </div>
</div>
<?php
} else {
    // Se ID canale specificato, mostra dettagli 
    $source_id = intval($_GET["id"]);

    $result = doQuery("SELECT ID FROM Sources WHERE ID='$source_id';");
    if(mysqli_num_rows($result) > 0) {
        $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
	$tmpSource = new Source($row["ID"]);

	$chan = '';
	$result2 = doQuery("SELECT ID FROM Chats WHERE botId='$tmpSource->botId' AND Type='channel';");
	if(mysqli_num_rows($result2) > 0) {
	    $row2 = mysqli_fetch_array($result2,MYSQLI_ASSOC);
	    $chan = $row2["ID"];
	}
?>
    <div class="jumbotron">
	<div class="container"><!-- CONTAINER -->
	    <h1><?php echo $tmpSource->Name; ?></h1>
	    <p class="lead"><?php echo $tmpSource->Description; ?></p>
	</div><!-- /CONTAINER -->
    </div>
    <div class="container">
	<div class="row">
	    <h3><?php echo _("Latest post from $tmpSource->Name"); ?></h3>
	    <table class="table table-hover">
		<thead>
		    <tr>
			<th><?php echo _("Title"); ?></th>
			<th><?php echo _("Publish date"); ?></th>
		    </tr>
		</thead><tbody>
<?php
	    $result2 = doQuery("SELECT ID FROM Posts WHERE sourceId='$tmpSource->ID' ORDER BY addDate DESC LIMIT 5;");
	    if(mysqli_num_rows($result2) > 0) {
		while($row = mysqli_fetch_array($result2,MYSQLI_ASSOC)) {
		    $post = new Post($row["ID"]);
	    
		    echo "<tr data-href=\"/rd/$post->ID\">
		    <td>$post->Title</td>
		    <td>".(is_null($post->publishDate) ? "not yet":$post->publishDate->format('h:m:s m-d-Y'))."</td>
	    	    </tr>";
		}
	    }
?>
		</tbody>
	    </table>
	    <p class="text-left">
		<small><?php echo _("data fetched from"); ?>&nbsp;<a href="<?php echo $tmpSource->URL; ?>"><?php echo $tmpSource->URL; ?></a></small>
	    </p>
	</div>	<div class="row">
	    <p class="lead">
		<?php echo _("Join freely this public channel clicking on this link"); ?>:&nbsp;<a href="tg://resolve?domain=<?php echo $chan; ?>"><?php echo $chan; ?></a></td>
	    </p>
	</div>
	<hr/>
	<div class="row">
	    <a class="ajaxDialog btn btn-danger" title="Report an issue" href="/ajaxCb?action=sourceReportIssue&id=<?php echo $tmpSource->ID;?>">
		<i class="fa fa-fa-exclamation-triangle text-error" aria-hidden="true"></i> <?php echo _("Report an issue"); ?>
	    </a>
	</div>
<?php
    } else {
	echo "<h3>Invalid channel ID</h3>";
    }
?>
    </div><!-- /CONTAINER -->
<?php
}

include "common_footer.php";

?>
