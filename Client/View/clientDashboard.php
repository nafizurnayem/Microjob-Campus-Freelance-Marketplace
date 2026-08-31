<?php
require "../../Model/init.php";

requireRole("client");

$clientId = currentUserId();

$myOrders = $orderModel->listByClient($clientId);
$payments = $paymentModel->listByClient($clientId);

$awaitingPayment = 0;
$active = 0;
$completed = 0;
$spent = 0;

$totalOrders = count($myOrders);
for ($i = 0; $i < $totalOrders; $i++) {
    $order = $myOrders[$i];

    if ($order["status"] == "placed" && !$order["payment_id"]) {
        $awaitingPayment = $awaitingPayment + 1;
    }
    if ($order["status"] == "accepted" || $order["status"] == "delivered") {
        $active = $active + 1;
    }
    if ($order["status"] == "completed") {
        $completed = $completed + 1;
    }
}

$totalPayments = count($payments);
for ($i = 0; $i < $totalPayments; $i++) {
    $spent = $spent + $payments[$i]["amount_bdt"];
}

$pageTitle = "Client dashboard";
include "../../Student/View/header.php";
?>

<h1>Hello, <?php echo esc(currentUserName()); ?></h1>
<p class="muted">Find a student, place an order and track your work from here.</p>

<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo $awaitingPayment; ?></span>
        <span class="label">Waiting for your payment</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo $active; ?></span>
        <span class="label">Work in progress</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney(round($spent, 2)); ?></span>
        <span class="label">Total spent (<?php echo $completed; ?> completed)</span>
    </div>
</div>
<div class="clear"></div>

<div class="card">
    <a class="btn" href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php">Browse gigs and hire</a>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/myOrders.php">My orders</a>
</div>

<h2>Recent orders</h2>
<?php if ($totalOrders == 0) { ?>
    <div class="card">
        <p class="muted">
            You have not hired anybody yet.
            <a href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php">Browse the gig catalogue</a>
            to get started.
        </p>
    </div>
<?php } else { ?>
    <table>
        <tr>
            <th>Order</th>
            <th>Gig</th>
            <th>Student</th>
            <th>Amount</th>
            <th>Deadline</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php
        $shown = 0;
        for ($i = 0; $i < $totalOrders; $i++) {
            if ($shown >= 8) {
                break;
            }
            $order = $myOrders[$i];
            $shown = $shown + 1;
            ?>
            <tr>
                <td>#<?php echo esc($order["order_id"]); ?></td>
                <td><?php echo esc($order["gig_title"]); ?></td>
                <td><?php echo esc($order["student_name"]); ?></td>
                <td><?php echo formatMoney($order["amount_bdt"]); ?></td>
                <td><?php echo esc(date("d M Y", strtotime($order["deadline"]))); ?></td>
                <td>
                    <span class="badge badge-<?php echo esc($order["status"]); ?>">
                        <?php echo esc($order["status"]); ?>
                    </span>
                </td>
                <td>
                    <?php if (!$order["payment_id"] && $order["status"] == "placed") { ?>
                        <a class="btn btn-small"
                           href="<?php echo BASE_URL; ?>/Client/View/checkout.php?order_id=<?php echo $order["order_id"]; ?>">
                            Pay now
                        </a>
                    <?php } else { ?>
                        <a class="btn btn-small btn-secondary"
                           href="<?php echo BASE_URL; ?>/Student/View/orderDetails.php?order_id=<?php echo $order["order_id"]; ?>">
                            Open
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
    <p class="spaced"><a href="<?php echo BASE_URL; ?>/Student/View/myOrders.php">See all orders</a></p>
<?php } ?>

<?php include "../../Student/View/footer.php"; ?>
