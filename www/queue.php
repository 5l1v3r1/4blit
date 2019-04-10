<?php

$gbTailCode = "<script type=\"text/javascript\">
$(function (){
    $('a.ajaxCustomCall').click(function() {
	var url = this.href;
	var container = this;

	var tr = $(this).closest('tr');

	$.ajax({
    	    url: url,
    	    dataType: 'html',
    	    success: function(data) {
		tr.find('td').fadeOut(1000,function(){ 
		    tr.remove();
    		}); 
    	    },
	    error: function(XMLHttpRequest, textStatus, errorThrown) {
		$(container).html('ERROR');
	    }
	});
	return false;
    });
});
</script>";

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
	    <div class="row">
<?php
if(empty($_GET["id"])) {
?>
		<h4><i class="fa fa-users" aria-hidden="true"></i> <?php echo _("BOTs"); ?></h4>
		<table class="table table-hover">
		    <thead>
			<tr>
			    <th><?php echo _("ID"); ?></th>
			    <th><?php echo _("Name"); ?></th>
			    <th><?php echo _("Waiting posts"); ?></th>
			    <th><?php echo _("Total posts"); ?></th>
			    <th><?php echo _("Last publish"); ?></th>
			    <th></th>
			</tr>
		    </thead>
		    <tbody>
<?php
		$result = doQuery("SELECT ID,(SELECT count(ID) FROM Posts WHERE botId=t1.ID) AS totalPosts, (SELECT count(ID) FROM Posts WHERE botId=t1.ID AND isPublished=0) AS waitingPosts FROM Bots AS t1 WHERE userId='$myUser->ID';");
		if(mysqli_num_rows($result) > 0) {
		    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			$tmpBot = new Bot($row["ID"]);
			$waitingPosts = $row["waitingPosts"];
			$totalPosts = $row["totalPosts"];

			echo "<tr>
			    <td>$tmpBot->ID</td>
			    <td>$tmpBot->Name</td>
			    <td>$waitingPosts</td>
			    <td>$totalPosts</td>
			    <td>".(empty($tmpBot->lastPublish) ? "never" : $tmpBot->lastPublish->format('m-d-Y h:m:s'))."</td>
			    <td><a href=\"/queue/$tmpBot->ID\" title=\""._("Show BOT $tmpBot->ID queue")."\">
				<i class=\"fa fa-database\" aria-hidden=\"true\"></i>
			    </a></td>
			</tr>";
		    }
		} else {
?>
	    	    <div class="alert alert-warning" role="alert">
		        <strong>Ooops !</strong> We need at least <a href="/configs">one BOT configured</a> before show you the queue...
		    </div>
<?php
	        }
?>	
		</tbody></table>
<?php
} else {
		$bot = new Bot(intval($_GET["id"]));
		if(!empty($bot)) {
?>
		    <h4><i class="fa fa-database" aria-hidden="true"></i> <?php printf(_("Posts queue for BOT %s"),$bot->Name); ?></h4>
		    <table class="table table-hover">
			<thead>
			    <tr>
				<th><?php echo _("Title"); ?></th>
				<th><?php echo _("URL"); ?></th>
				<th><?php echo _("Views"); ?></th>
				<th><?php echo _("Add date"); ?></th>
				<th><?php echo _("Publish date"); ?></th>
				<th></th>
			    </tr>
			</thead>
			<tbody>
<?php
		    $result = doQuery("SELECT ID FROM Posts WHERE userId='$myUser->ID' AND botId='$bot->ID' ORDER BY addDate DESC LIMIT 20;");
		    if(mysqli_num_rows($result) > 0) {
			while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
			    $post = new Post($row["ID"]);
			    echo "<tr class=\"".($post->isPublished ? "":"table-info")."\" id=\"tr-post-$post->ID \">
			    <td>$post->Title</td>
			    <td><a href='$post->URL' target='new'>$post->URL</a></td>
			    <td>$post->Views</td>
			    <td>".$post->addDate->format('h:m:s d-m-Y')."</td>
			    <td>".(is_null($post->publishDate) ? "not yet":$post->publishDate->format('h:m:s d-m-Y'))."</td>
			    <td>";
			    if(!$post->isPublished) {
				echo "<a class=\"ajaxCall\" title=\""._("Toggle this post")."\" href=\"/ajaxCb.php?action=togglePost&ID=$post->ID\">
				    <i class=\"fa fa-".($post->isActive ? "pause":"play")."\" aria-hidden=\"true\"></i>
				</a><a class=\"ajaxCustomCall\" title=\""._("Delete this post")."\" href=\"/ajaxCb.php?action=deletePost&ID=$post->ID\">
				    <i class=\"fa fa-trash\" aria-hidden=\"true\"></i>
				</a>";
			    } 
			    echo "</td>
			    </tr>";
			}
		    }
?>
		    </tbody>
		</table>
<?php
		}
}
?>
	    </div>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->

<?php
} else {
    header("Location: /");
}

include "common_footer.php";
?>
