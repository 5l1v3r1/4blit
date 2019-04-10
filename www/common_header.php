<?php

include_once "common.inc.php";

include_once "common_cb.php";

?>
<!DOCTYPE html>
<html lang="<?php echo $gbLang; ?>">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Connect your blog with Telegram channel, keep your followers always up-to-date. We did the hard way for you: just add your WP blog or RSS feed, create your channel, your bot and start broadcasting the world !">
    <meta name="author" content="Michele Pinassi">
    <meta name="keywords" content="telegram, blog, rss, feed, broadcast, channel, bot">

    <meta property="og:title" content="4bl.it" />
    <meta property="og:description" content="4bl.it - Telegram broadcasting made easy !" />
    <meta property="og:url" content="http://www.4bl.it/" />
    <meta property="og:image" content="http://www.4bl.it/img/banner.jpg" />

    <meta property="fb:app_id" content="713842645356104" />

    <meta name="twitter:card" content="summary" />
    <meta name="twitter:site" content="@4bl.it" />
    <meta name="twitter:creator" content="@4bl.it" />

    <meta name="flattr:id" content="9y71y5">

    <title>4bl.it - Telegram broadcasting made easy !</title>

    <link href="/css/bootstrap.min.css" rel="stylesheet">

    <link href="/css/glyphicons.css" rel="stylesheet">
    <link href="/css/font-awesome.min.css" rel="stylesheet">

    <!-- Bootstrap social CSS -->
    <link href="/css/bootstrap-social.css" rel="stylesheet">

    <link href="/css/animate.css" rel="stylesheet">

    <link rel="stylesheet" href="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.4/themes/smoothness/jquery-ui.css">

    <link href="/css/validationEngine.jquery.css" rel="stylesheet" />

    <link href="https://fonts.googleapis.com/css?family=Raleway:500" rel="stylesheet" type="text/css">

    <link href="/css/common.css" rel="stylesheet">

<?php

if(isset($gbCssScripts)) {
    foreach($gbCssScripts as $cssScript) {
	echo "<link href=\"/css/$cssScript\" rel=\"stylesheet\">";
    }
}

$local_css = basename($_SERVER['SCRIPT_FILENAME'],".php").".css";

if(file_exists("./css/".$local_css)) {
    echo "\t<!-- local CSS -->\n\t<link href=\"/css/".$local_css."\" rel=\"stylesheet\" />\n";
}
?>
    <link rel="icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="shortcut icon" href="/favicon.ico" type="image/x-icon" />
    <link rel="pingback" href="http://www.4bl.it/xmlrpc" />

    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
<?php
if(isset($gbHeadCode)) {
    echo $gbHeadCode;
}
?>
</head>
<body>
    <div id="fb-root"></div>
    <script>(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
	js = d.createElement(s); js.id = id;
	js.src = "//connect.facebook.net/it_IT/sdk.js#xfbml=1&version=v2.7&appId=713842645356104";
	fjs.parentNode.insertBefore(js, fjs);
    }(document, 'script', 'facebook-jssdk'));</script>
    <div id="cookiebar"></div>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top"><!-- NAVBAR -->
	<a class="navbar-brand" href="/">4bl.it</a>
	<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#collapsingNavbar" aria-controls="collapsingNavbar" aria-expanded="false" aria-label="Toggle navigation">
    	    <span class="navbar-toggler-icon"></span>
	</button>
	<div class="collapse navbar-collapse" id="collapsingNavbar">
	    <ul class="navbar-nav mr-auto">
		<li class="nav-item">
		    <a class="nav-link" href="/blog"><?php echo _("Blog"); ?></a>
		</li>
		<li class="nav-item">
		    <a class="nav-link" href="/channel"><?php echo _("Public channels"); ?></a>
		</li>
		<li class="nav-item">
		    <a class="nav-link" href="/api"><?php echo _("API"); ?></a>
		</li>
		<li class="nav-item">
		    <a class="nav-link" href="/plugin"><?php echo _("Plugin"); ?></a>
		</li>
		<li class="nav-item">
		    <a class="nav-link" href="/contacts"><?php echo _("Contacts"); ?></a>
		</li>
	    </ul>
	    <ul class="nav navbar-nav pull-right">
<?php
if($mySession->isLogged()) {
?>		<li class="nav-item"><small><a href="/logout" class="nav-link"><i class="fa fa-sign-out"></i><?php echo _("Logout"); ?></a></small></li>
<?php } else { ?>
		<li class="nav-item dropdown">
		    <a href="#" class="dropdown-toggle nav-link" data-toggle="dropdown"><b><?php echo _("Login or sign-up"); ?></b> <span class="caret"></span></a>
		    <ul id="login-dp" class="dropdown-menu dropdown-menu-right">
			<li>
		    	    <div class="row">
				<div class="col-md-12">
				    <?php echo _("Social authentication"); ?>
				    <!-- <div class="social-buttons">
					<script async src="https://telegram.org/js/telegram-widget.js?4" data-telegram-login="FourBlit_Bot" data-size="medium" data-auth-url="telegram_login" data-request-access="write"></script>
				    </div> -->
				    <div class="social-buttons">
					<a href="?l=facebook" class="btn btn-facebook w-100"><i class="fa fa-facebook"></i> Facebook</a>
				    </div>
				    <div class="social-buttons">
					<a href="?l=twitter" class="btn btn-twitter w-100"><i class="fa fa-twitter"></i> Twitter</a>
				    </div>
				    <div class="social-buttons">
					<a href="?l=google" class="btn btn-google-plus w-100"><i class="fa fa-google"></i> Google</a>
				    </div>
				</div>
		    	    </div>
		        </li>
		    </ul>
		</li>
<?php } ?>
	    </ul>
	</div>
    </nav><!-- /NAVBAR -->
