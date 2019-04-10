<div class="list-group panel">
    <a href="/home" class="list-group-item">
        <i class="fa fa-home"></i> <?php echo _("Overview"); ?>
    </a>
    <a href="/inbox" class="list-group-item">
<?php
/* Check if there's any unread messages in inbox.. */
$result = doQuery("SELECT ID FROM UserMessages WHERE userId='$myUser->ID' AND isRead=0;");
if(mysqli_num_rows($result) > 0) {
    echo "<span class=\"tag tag-danger tag-pill pull-xs-right\">".mysqli_num_rows($result)."</span>";
}
?>
	<i class="fa fa-envelope-o"></i> <?php echo _("Inbox"); ?>
    </a>
    <a href="/reports" class="list-group-item">
        <i class="fa fa-pie-chart"></i> <?php echo _("Reports"); ?>
    </a>
    <a href="/queue" class="list-group-item">
        <i class="fa fa-stack-exchange"></i> <?php echo _("Bot queues"); ?>
    </a>
    <a href="/configs" class="list-group-item">
        <i class="fa fa-wrench"></i> <?php echo _("Configurations"); ?>
    </a>
<?php
if($myUser->isAdmin()) {
/* Administrator-only menu */
?>
    <a href="/admin/log.php" class="list-group-item">
        <i class="fa fa-list"></i> <?php echo _("System Log"); ?>
    </a>
    <a href="/admin/users.php" class="list-group-item">
        <i class="fa fa-users"></i> <?php echo _("Users"); ?>
    </a>
    <a href="/admin/sources.php" class="list-group-item">
        <i class="fa fa-cubes"></i> <?php echo _("Sources"); ?>
    </a>
    <a href="/admin/mail.php" class="list-group-item">
        <i class="fa fa-envelope"></i> <?php echo _("Mail queue"); ?>
    </a>
<?php
}
?>

    <a href="/account" class="list-group-item">
        <i class="fa fa-user"></i> <?php echo _("My account"); ?>
    </a>
    <a href="?logout=1" class="list-group-item">
        <i class="fa fa-sign-out"></i> <?php echo _("Logout"); ?>
    </a>
</div>