<?php

$gbCssScript = array("jqcloud.min.css");
$gbJsScripts = array("jqcloud.min.js","tags.js");

include "common_header.php";

// TODO
//
// TagCloud don't work

?>
<div class="jumbotron">
    <div class="container"><!-- CONTAINER -->
	<h1><?php echo _("Tag cloud"); ?></h1>
	<p class="lead"><?php echo _("Tag cloud from public channels posts"); ?></p>
    </div><!-- /CONTAINER -->
</div>
<div class="container">
    <div class="row">
	<div class="w-100" id="tagcloud"></div>
    </div>
</div>
<?php

include "common_footer.php";

?>
