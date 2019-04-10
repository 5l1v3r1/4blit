<?php

include "common_header.php";

if(!$mySession->isLogged()) {
    header('Location: /');
    exit();
}

$bots_id = array();

$result = doQuery("SELECT ID FROM Bots WHERE userId='$myUser->ID';");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$bots_id[] = $row["ID"];
    }
}

?>
<div class="container-fluid"><!-- CONTAINER -->
    <div class="row">
	<div class="col-sm-3 col-md-2 sidebar">
	    <?php include "common_leftmenu.php"; ?>
	</div>
        <div class="col-sm-9 col-md-10 main" id="mainContent"><!-- MAIN -->
	    <h1><?php echo _("Dashboard"); ?></h1>

            <div class="row">
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-success">
                        <div class="card-block bg-success">
                            <div class="rotate">
                                <i class="fa fa-user fa-5x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo _("Bots"); ?></h6>
                            <h1 class="display-1">
			    <?php 
				echo count($bots_id);
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-danger">
                        <div class="card-block bg-danger">
                            <div class="rotate">
                                <i class="fa fa-list fa-4x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo _("Posts"); ?></h6>
                            <h1 class="display-1">
			    <?php
			    $result = doQuery("SELECT ID FROM Posts WHERE userId='$myUser->ID';");
			    echo mysqli_num_rows($result);
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-info">
                        <div class="card-block bg-info">
                            <div class="rotate">
                                <i class="fa fa-users fa-5x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo _("Sources"); ?></h6>
                            <h1 class="display-1">
			    <?php
			    $result = doQuery("SELECT ID FROM Sources WHERE userId='$myUser->ID';");
			    echo mysqli_num_rows($result);
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <div class="card card-inverse card-warning">
                        <div class="card-block bg-warning">
                            <div class="rotate">
                                <i class="fa fa-check-square-o fa-5x"></i>
                            </div>
                            <h6 class="text-uppercase"><?php echo("Clicks"); ?></h6>
                            <h1 class="display-1">
			    <?php
			    $result = doQuery("SELECT SUM(Views) AS Views FROM Posts WHERE userId='$myUser->ID' ORDER BY addDate LIMIT 100;");
			    $row = mysqli_fetch_array($result,MYSQLI_ASSOC);
			    echo ($row["Views"]?$row["Views"]:"0");
			    ?>
			    </h1>
                        </div>
                    </div>
                </div>
            </div>
	    <hr>
<?php 
if($myUser->Level > 1) {
?>
	    <h4><i class="fa fa-list" aria-hidden="true"></i> <?php echo _("Latest posts"); ?></h4>
	    <table class="table table-condensed table-hover table-striped">
		<thead>
		    <tr>
			<th data-column-id="id">ID</th>
			<th data-column-id="title">Title</th>
			<th data-column-id="views">Views</th>
			<th data-column-id="publishDate">publishDate</th>
			<th data-column-id="commands"></th>
		    </tr>
		</thead>
		<tbody>
<?php		    
    if(isset($_GET["page"])) {
	$page = intval($_GET["page"]);
	$page_offset = $page * 10;
    } else {
	$page_offset = 0;
    }

    $result = doQuery("SELECT ID FROM Posts WHERE userId='$myUser->ID' ORDER BY addDate DESC LIMIT 10 OFFSET $page_offset;");
    if(mysqli_num_rows($result) > 0) {
	while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	    $post = new Post($row["ID"]);
	    
	    echo "<tr data-href=\"/rd/$post->ID\">
		<td>$post->ID</td>
		<td>$post->Title</td>
		<td>$post->Views</td>
		<td>".(is_null($post->publishDate) ? "not yet":$post->publishDate->format('h:m:s m-d-Y'))."</td>
	    </tr>";
	}
    }
?>
		</tbody>
	    </table>
<?php
} else {
?>
	    <div class="alert alert-info" role="alert">
		<strong>Welcome !</strong> Check your mailbox and activate your account to start playing with 4blit features !
	    </div>
<?php
}
?>
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php

include "common_footer.php";

?>
