<?php
require "../../Model/init.php";

requireRole("admin");

$orders = $orderModel->listAll();
$payments = $paymentModel->listAll();
$byMethod = $paymentModel->totalByMethod();
$totalCollected = $paymentModel->totalCollected();

$pageTitle = "Transaction report";
include "../../Student/View/header.php";
?>

<h1>Transaction report</h1>
<p class="muted">Every order and every payment recorded on the platform.</p>

<div class="col-half">
    <div class="card">
        <h3>Collected by payment method</h3>
        <table>
            <tr>
                <th>Method</th>
                <th>Payments</th>
                <th>Total</th>
            </tr>
            <?php
            $totalMethods = count($byMethod);
            for ($i = 0; $i < $totalMethods; $i++) {
                $row = $byMethod[$i];
                ?>
                <tr>
                    <td><?php echo strtoupper(esc($row["method"])); ?></td>
                    <td><?php echo esc($row["orders_count"]); ?></td>
                    <td><?php echo formatMoney($row["total"]); ?></td>
                </tr>
            <?php } ?>
            <tr>
                <th>All methods</th>
                <th><?php echo count($payments); ?></th>
                <th><?php echo formatMoney($totalCollected); ?></th>
            </tr>
        </table>
    </div>
</div>

<div class="col-half">
    <div class="card">
        <h3>Orders by status</h3>
        <table>
            <tr>
                <th>Status</th>
                <th>Orders</th>
            </tr>
            <?php
            $statuses = array("placed", "accepted", "delivered", "completed", "cancelled");
            $totalStatuses = count($statuses);
            for ($i = 0; $i < $totalStatuses; $i++) {
                ?>
                <tr>
                    <td>
                        <span class="badge badge-<?php echo esc($statuses[$i]); ?>">
                            <?php echo esc($statuses[$i]); ?>
                        </span>
                    </td>
                    <td><?php echo esc($orderModel->countByStatus($statuses[$i])); ?></td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>
<div class="clear"></div>

<h2>Payments</h2>
<?php
$totalPayments = count($payments);
if ($totalPayments == 0) {
    ?>
    <div class="card">
        <p class="muted">No payment has been recorded yet.</p>
    </div>
    <?php
} else {
    ?>
    <table>
        <tr>
            <th>Transaction</th>
            <th>Order</th>
            <th>Gig</th>
            <th>Client</th>
            <th>Student</th>
            <th>Method</th>
            <th>Account</th>
            <th>Amount</th>
            <th>Paid at</th>
        </tr>
        <?php
        for ($i = 0; $i < $totalPayments; $i++) {
            $payment = $payments[$i];
            ?>
            <tr>
                <td><?php echo esc($payment["txn_id"]); ?></td>
                <td>#<?php echo esc($payment["order_id"]); ?></td>
                <td><?php echo esc($payment["gig_title"]); ?></td>
                <td><?php echo esc($payment["client_name"]); ?></td>
                <td><?php echo esc($payment["student_name"]); ?></td>
                <td><?php echo strtoupper(esc($payment["method"])); ?></td>
                <td>**** <?php echo esc($payment["account_last4"]); ?></td>
                <td><?php echo formatMoney($payment["amount_bdt"]); ?></td>
                <td><?php echo esc(date("d M Y, h:i A", strtotime($payment["paid_at"]))); ?></td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<h2 class="spaced">Orders</h2>
<?php
$totalOrders = count($orders);
if ($totalOrders == 0) {
    ?>
    <div class="card">
        <p class="muted">No order has been placed yet.</p>
    </div>
    <?php
} else {
    ?>
    <table>
        <tr>
            <th>Order</th>
            <th>Gig</th>
            <th>Client</th>
            <th>Student</th>
            <th>Amount</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Payment</th>
        </tr>
        <?php
        for ($i = 0; $i < $totalOrders; $i++) {
            $order = $orders[$i];
            ?>
            <tr>
                <td>#<?php echo esc($order["order_id"]); ?></td>
                <td><?php echo esc($order["gig_title"]); ?></td>
                <td><?php echo esc($order["client_name"]); ?></td>
                <td><?php echo esc($order["student_name"]); ?></td>
                <td><?php echo formatMoney($order["amount_bdt"]); ?></td>
                <td><?php echo esc(date("d M Y", strtotime($order["deadline"]))); ?></td>
                <td>
                    <span class="badge badge-<?php echo esc($order["status"]); ?>">
                        <?php echo esc($order["status"]); ?>
                    </span>
                </td>
                <td>
                    <?php if ($order["txn_id"]) { ?>
                        <?php echo esc($order["txn_id"]); ?>
                    <?php } else { ?>
                        <span class="muted">unpaid</span>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include "../../Student/View/footer.php"; ?>
