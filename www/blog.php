<?php

include "common_header.php";

?>

<div class="jumbotron">
    <div class="container"><!-- CONTAINER -->
	<h1><?php echo _("Blog"); ?></h1>
	<p class="lead"><?php echo _("News, updates and all the important stuff about 4bl.it project."); ?></p>
    </div><!-- /CONTAINER -->
</div>
<div class="container">
    <ul class="timeline">
<?php
$c=0;

$result = doQuery("SELECT ID,Title,Content,addDate FROM Blog ORDER BY addDate DESC LIMIT 10;");
if(mysqli_num_rows($result) > 0) {
    while($row = mysqli_fetch_array($result,MYSQLI_ASSOC)) {
	$blog_post_id = $row["ID"];
	$blog_post_title = stripslashes($row["Title"]);
	$blog_post_content = stripslashes($row["Content"]);
	$blog_post_date = new DateTime($row["addDate"]);

	if ($c % 2 == 0) {
	    echo "<li>";
	} else {
	    echo "<li class=\"timeline-inverted\">";
	} 
?>
	<div class="timeline-badge"><i class="fa fa-comments"></i></div>
	<div class="timeline-panel">
	    <div class="timeline-heading">
		<h4 class="timeline-title"><?php echo "<a href='/blog/$blog_post_id'>$blog_post_title</a>"; ?></h4>
		<p><small class="text-muted"><i class="glyphicon glyphicon-time"></i> <?php printf(_("Published on %s"),$blog_post_date->format("d-m-Y")); ?></small></p>
	    </div>
	    <div class="timeline-body"> 
		<p><?php echo $blog_post_content; ?></p>
		<p class="pull-right">
		    <small><a class="ajaxDialog" title="Edit post" href="/ajaxCb.php?action=editBlogPost&id=<?php echo $blog_post_id; ?>"><i class="fa fa-edit" aria-hidden="true"></i></a></small>
		    <small><a class="ajaxDialog" title="Remove post" href="/ajaxCb.php?action=removeBlogPost&id=<?php echo $blog_post_id; ?>"><i class="fa fa-remove" aria-hidden="true"></i></a></small>
		</p>
            </div>
          </div>
        </li>
<?php
	$c++;
    }
}
?>
    </ul>
<?php
if(($mySession->isLogged()) && ($myUser->getACL('canAddBlogPost') == 1)) {
// LOGGED USERS
?>
    <div class="btn-group" role="group">
	<a class="ajaxDialog btn btn-secondary" title="<?php echo _("New post"); ?>" href="/ajaxCb.php?action=editBlogPost"><i class="fa fa-plus" aria-hidden="true"></i> <?php echo _("New post"); ?></a>
    </div>
<?php
}
?>
</div>

<?php

include "common_footer.php";

?>
