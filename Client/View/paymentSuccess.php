<?php
require "../../Model/init.php";

requireRole("client");

$orderId = cleanInput($_GET["order_id"] ?? "");

if (!isDigitsOnly($orderId)) {
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

$order = $orderModel->findById($orderId);

if (!$order || $order["client_id"] != currentUserId()) {
    setFlash("error", "That order does not exist.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

$payment = $paymentModel->findByOrder($orderId);

if (!$payment) {
    redirectTo(BASE_URL . "/Client/View/checkout.php?order_id=" . $orderId);
}

$pageTitle = "Payment receipt";
include "../../Student/View/header.php";
?>

<h1>Payment received</h1>
<p class="muted">
    Keep this transaction id. <?php echo esc($order["student_name"]); ?> can now accept the order
    and start working.
</p>

<div class="col-half">
    <div class="card">
        <h3>Receipt</h3>
        <table>
            <tr>
                <th>Transaction id</th>
                <td><strong><?php echo esc($payment["txn_id"]); ?></strong></td>
            </tr>
            <tr>
                <th>Amount</th>
                <td><?php echo formatMoney($payment["amount_bdt"]); ?></td>
            </tr>
            <tr>
                <th>Method</th>
                <td><?php echo strtoupper(esc($payment["method"])); ?></td>
            </tr>
            <tr>
                <th>Account ending</th>
                <td><?php echo esc($payment["account_last4"]); ?></td>
            </tr>
            <tr>
                <th>Paid at</th>
                <td><?php echo esc(date("d M Y, h:i A", strtotime($payment["paid_at"]))); ?></td>
            </tr>
            <tr>
                <th>Order</th>
                <td>#<?php echo esc($order["order_id"]); ?> &mdash; <?php echo esc($order["gig_title"]); ?></td>
            </tr>
            <tr>
                <th>Delivery by</th>
                <td><?php echo esc(date("d M Y", strtotime($order["deadline"]))); ?></td>
            </tr>
        </table>

        <p class="spaced">
            <a class="btn"
               href="<?php echo BASE_URL; ?>/Student/View/orderDetails.php?order_id=<?php echo esc($order["order_id"]); ?>">
                Open the order
            </a>
            <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php">Browse more gigs</a>
        </p>
    </div>
</div>

<div class="col-half">
    <div class="card">
        <h3>What happens next</h3>
        <p class="muted">1. The student accepts the order.</p>
        <p class="muted">2. You can send requirements and files as messages on the order page.</p>
        <p class="muted">3. The student marks the work delivered before the deadline.</p>
        <p class="muted">4. You confirm completion and leave a rating.</p>
    </div>
</div>
<div class="clear"></div>

<?php include "../../Student/View/footer.php"; ?>
