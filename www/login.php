<?php
include "common.inc.php";

if($mySession->isLogged()) {
    header("Location: /");
}

if(isset($_GET["otp"])) {
    $otp = cleanInput($_GET["otp"]);
}

include "common_header.php";

?>
<div class="container">
    <div class="row main">
	<div class="main-login main-center">
	    <h1><?php echo _("Login with"); ?></h1>
	    <div class="clearfix">&nbsp;</div>
	    <div class="row">
		<div class="col-md-4">
		    <a href="?l=facebook&otp=<?php echo $otp; ?>" class="btn btn-social btn-facebook w-100"><i class="fa fa-facebook"></i> Facebook</a>
		</div><div class="col-md-4">
		    <a href="?l=twitter&otp=<?php echo $otp; ?>" class="btn btn-social btn-twitter w-100"><i class="fa fa-twitter"></i> Twitter</a>
		</div><div class="col-md-4">
		    <a href="?l=google&otp=<?php echo $otp; ?>" class="btn btn-social btn-google-plus w-100"><i class="fa fa-google"></i> Google</a>
		</div>
	    </div>
	    <div class="clearfix">&nbsp;</div>
	    <h6><?php echo _("We use third-party authentication to ensure security for all our users (we never know your password). Choose which one fits your needs."); ?></h6>
	</div>
    </div>
</div>
<?php

include "common_footer.php";

?>
