<?php
// Shared page top. The view sets $pageTitle before including this file.
$pageTitle = $pageTitle ?? SITE_NAME;
$flashError = getFlash("error");
$flashSuccess = getFlash("success");
?>
<!DOCTYPE html>
<html>

<head>
    <title><?php echo esc($pageTitle); ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/Student/View/assets/style.css" />
</head>

<body>

    <div class="site-header">
        <div class="page">
            <a class="brand" href="<?php echo BASE_URL; ?>/Student/View/home.php">Micro<span>Job</span></a>
            <div class="nav">
                <a href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php">Browse Gigs</a>

                <?php if (isLoggedIn()) { ?>

                    <a href="<?php echo dashboardFor(currentRole()); ?>">Dashboard</a>

                    <?php if (isStudent()) { ?>
                        <a href="<?php echo BASE_URL; ?>/Student/View/myGigs.php">My Gigs</a>
                        <a href="<?php echo BASE_URL; ?>/Student/View/myOrders.php">Orders</a>
                        <a href="<?php echo BASE_URL; ?>/Student/View/earnings.php">Earnings</a>
                    <?php } ?>

                    <?php if (isClient()) { ?>
                        <a href="<?php echo BASE_URL; ?>/Student/View/myOrders.php">My Orders</a>
                    <?php } ?>

                    <?php if (isAdmin()) { ?>
                        <a href="<?php echo BASE_URL; ?>/Admin/View/manageGigs.php">Gigs</a>
                        <a href="<?php echo BASE_URL; ?>/Admin/View/manageUsers.php">Users</a>
                        <a href="<?php echo BASE_URL; ?>/Admin/View/reports.php">Reports</a>
                    <?php } ?>

                    <a href="<?php echo BASE_URL; ?>/Student/View/profile.php">Profile</a>
                    <a href="<?php echo BASE_URL; ?>/Student/Controller/logoutController.php">Logout</a>
                    <span class="who"><?php echo esc(currentUserName()); ?> (<?php echo esc(currentRole()); ?>)</span>

                <?php } else { ?>

                    <a href="<?php echo BASE_URL; ?>/Student/View/login.php">Login</a>
                    <a href="<?php echo BASE_URL; ?>/Student/View/register.php">Register</a>

                <?php } ?>
            </div>
            <div class="clear"></div>
        </div>
    </div>

    <div class="page">

        <?php if ($flashError != "") { ?>
            <div class="alert-error"><?php echo esc($flashError); ?></div>
        <?php } ?>

        <?php if ($flashSuccess != "") { ?>
            <div class="alert-success"><?php echo esc($flashSuccess); ?></div>
        <?php } ?>
