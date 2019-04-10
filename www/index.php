<?php

include "common_header.php";

if($mySession->isLogged()) {
// LOGGED USERS
    header('Location: /home');
} else { 
// NON LOGGED USERS
?>
<div class="carousel fade-carousel slide" data-ride="carousel" data-interval="4000" id="bs-carousel">
    <ol class="carousel-indicators">
	    <li data-target="#bs-carousel" data-slide-to="0" class="active"></li>
	    <li data-target="#bs-carousel" data-slide-to="1"></li>
	    <li data-target="#bs-carousel" data-slide-to="2"></li>
    </ol>
    <div class="carousel-inner">
	    <div class="carousel-item slides active">
		<div class="overlay"></div>
		<div class="slide-1"></div>
		<div class="hero">
	    	    <hgroup>
	    	        <h1><?php echo _("Your blog on Telegram"); ?></h1>
			<h3><?php echo _("Promote your blog articles on your Telegram channel, for free !"); ?></h3>
		    </hgroup>
	    	    <!-- <a href="/login" class="btn btn-hero btn-lg" role="button" aria-disabled="true"><?php echo _("Discover more"); ?></a> -->
		</div>
        </div>
        <div class="carousel-item slides">
		<div class="slide-2"></div>
		<div class="hero">
		    <hgroup>
        		<h1><?php echo _("Over 1 billion potential users"); ?></h1>
			<h3><?php echo _("There are over 1 billion of users on Telegram and growing. And you can engage them in real time."); ?></h3>
		    </hgroup>
	    	    <!-- <a href="/login" class="btn btn-hero btn-lg" role="button" aria-disabled="true"><?php echo _("Discover more"); ?></a> -->
		</div>
        </div>
        <div class="carousel-item slides">
		<div class="slide-3"></div>
		<div class="hero">
	    	    <hgroup>
        		<h1><?php echo _("It's free"); ?></h1>
        		<h3><?php echo _("Our service was provided free of charge. No credit card required. Enjoy !"); ?></h3>
		    </hgroup>
	    	    <!-- <a href="/login" class="btn btn-hero btn-lg" role="button" aria-disabled="true"><?php echo _("Discover more"); ?></a> -->
		</div>
        </div>
    </div>
</div>
<div class="container">
    <div class="row">
	&nbsp;
    </div>
</div>
<div class="container">
    <div class="row">
	<div class="col-lg-12">
	    <h3>
	    <?php
	    $result = doQuery("SELECT ID FROM Posts;");
	    $posts_num = mysqli_num_rows($result);

	    $result = doQuery("SELECT ID FROM Sources WHERE isPublic=1;");
	    $sources_num = mysqli_num_rows($result);

	    echo sprintf(_("Hooooray, since the beginning of <i>4bl.it</i> we have already send %d posts for %d blogs !"),$posts_num,$sources_num);
	    ?>
	    </h3>
	</div>
    </div>
</div> <!-- /container -->

<?php
}

include "common_footer.php";

?>