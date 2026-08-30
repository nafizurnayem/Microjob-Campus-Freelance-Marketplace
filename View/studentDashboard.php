<?php
require "../../Model/init.php";

requireRole("student");

$studentId = currentUserId();

$myGigs = $gigModel->listByStudent($studentId);
$myOrders = $orderModel->listByStudent($studentId);
$earnings = $orderModel->earningsOf($studentId);
$rating = $userModel->ratingOf($studentId);

$newOrders = $orderModel->countForStudentByStatus($studentId, "placed");
$workingOn = $orderModel->countForStudentByStatus($studentId, "accepted");

$pageTitle = "Student dashboard";
include "header.php";
?>

<h1>Hello, <?php echo esc(currentUserName()); ?></h1>
<p class="muted">This is your seller dashboard. Publish gigs, deliver orders and track earnings.</p>

<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo count($myGigs); ?></span>
        <span class="label">Gigs published</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo $newOrders; ?></span>
        <span class="label">New orders waiting</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo $workingOn; ?></span>
        <span class="label">In progress</span>
    </div>
</div>
<div class="clear"></div>

<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney($earnings["earned"]); ?></span>
        <span class="label">Earned (completed orders)</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney($earnings["pending"]); ?></span>
        <span class="label">Pending in active orders</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value">
            <?php
            if ($rating["total"] > 0) {
                echo esc($rating["average"]) . " / 5";
            } else {
                echo "-";
            }
            ?>
        </span>
        <span class="label"><?php echo esc($rating["total"]); ?> review(s)</span>
    </div>
</div>
<div class="clear"></div>

<div class="card">
    <a class="btn" href="<?php echo BASE_URL; ?>/Student/View/gigForm.php">Publish a new gig</a>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/myGigs.php">Manage my gigs</a>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/earnings.php">Earnings and withdrawal</a>
</div>

<h2>Recent orders</h2>
<?php if (count($myOrders) == 0) { ?>
    <div class="card">
        <p class="muted">
            No order yet. Publish a gig and wait for the admin to approve it, then it becomes
            visible to buyers.
        </p>
    </div>
<?php } else { ?>
    <table>
        <tr>
            <th>Order</th>
            <th>Gig</th>
            <th>Client</th>
            <th>Amount</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Paid</th>
            <th></th>
        </tr>
        <?php
        $total = count($myOrders);
        $shown = 0;
        for ($i = 0; $i < $total; $i++) {
            if ($shown >= 8) {
                break;
            }
            $order = $myOrders[$i];
            $shown = $shown + 1;
            ?>
            <tr>
                <td>#<?php echo esc($order["order_id"]); ?></td>
                <td><?php echo esc($order["gig_title"]); ?></td>
                <td><?php echo esc($order["client_name"]); ?></td>
                <td><?php echo formatMoney($order["amount_bdt"]); ?></td>
                <td><?php echo esc(date("d M Y", strtotime($order["deadline"]))); ?></td>
                <td>
                    <span class="badge badge-<?php echo esc($order["status"]); ?>">
                        <?php echo esc($order["status"]); ?>
                    </span>
                </td>
                <td>
                    <?php if ($order["payment_id"]) { ?>
                        <span class="badge badge-paid">paid</span>
                    <?php } else { ?>
                        <span class="muted">unpaid</span>
                    <?php } ?>
                </td>
                <td>
                    <a class="btn btn-small"
                       href="<?php echo BASE_URL; ?>/Student/View/orderDetails.php?order_id=<?php echo $order["order_id"]; ?>">
                        Open
                    </a>
                </td>
            </tr>
        <?php } ?>
    </table>
    <p class="spaced"><a href="<?php echo BASE_URL; ?>/Student/View/myOrders.php">See all orders</a></p>
<?php } ?>

<?php include "footer.php"; ?>
