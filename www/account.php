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
	    <h1><?php echo _("My Account"); ?></h1>
	    <div class="form-group">
	        <label class="control-label"><?php echo _("Name"); ?>:</label>
		<p class="form-control-static"><?php echo $myUser->displayName; ?></p>
	    </div>
	    <div class="form-group">
	        <label class="control-label"><?php echo _("eMail address"); ?>:</label>
		<p class="form-control-static"><?php echo $myUser->eMail; ?></p>
	    </div>
	    <div class="form-group">
		<label class="control-label"><?php echo _("Service level"); ?>:</label>
	        <p class="form-control-static"><?php echo $myUser->getLevel(); ?></p>
	    </div>
	    <div class="form-group">
		<label class="control-label"><?php echo _("Authentication provider(s)"); ?>:</label>
		<ul>
<?php
	    $result = doQuery("SELECT authProvider,authProviderUID FROM UserAuthProvider WHERE userId='$mySession->userId';");
	    if(mysqli_num_rows($result) > 0) {
	        while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
		    $authProvider = $row["authProvider"];
		    $authProviderUID = $row["authProviderUID"];

		    echo "<li><b>$authProvider</b>, UID $authProviderUID</li>";
		}
	    }
?>
		</ul>
	    </div>
	    <hr>
	    <div class="btn-group"><!-- TOOLBAR -->
		<a class="btn btn-default" title="<?php echo _("Delete account"); ?>" href="?action=deleteAccount">
		    <span class='glyphicon glyphicon-remove'></span> <?php echo _("Delete my account"); ?>
		</a>
		<a class="btn btn-default ajaxCall" title="<?php echo _("Resend welcome mail"); ?>" href="/ajaxCb?action=welcomeMail">
		    <span class='glyphicon glyphicon-envelope'></span> <?php echo _("Resend welcome mail"); ?>
		</a>
	    </div><!-- /TOOLBAR -->
	</div><!-- /MAIN -->
    </div>
</div><!-- /CONTAINER -->
<?php
} else {
    header("Location: /");
}

include "common_footer.php";

?>
