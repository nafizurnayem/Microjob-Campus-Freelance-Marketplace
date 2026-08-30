<?php
require "../../Model/init.php";

requireLogin();

if (isAdmin()) {
    redirectTo(BASE_URL . "/Admin/View/reports.php");
}

$asStudent = isStudent();

if ($asStudent) {
    $orders = $orderModel->listByStudent(currentUserId());
} else {
    $orders = $orderModel->listByClient(currentUserId());
}

$pageTitle = "My orders";
include "header.php";
?>

<h1><?php echo $asStudent ? "Orders I received" : "Orders I placed"; ?></h1>

<?php
$total = count($orders);
if ($total == 0) {
    ?>
    <div class="card">
        <p class="muted">
            <?php if ($asStudent) { ?>
                No order yet. Make sure your gigs are approved and visible in the catalogue.
            <?php } else { ?>
                You have not placed any order.
                <a href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php">Browse gigs</a>.
            <?php } ?>
        </p>
    </div>
    <?php
} else {
    ?>
    <table>
        <tr>
            <th>Order</th>
            <th>Gig</th>
            <th><?php echo $asStudent ? "Client" : "Student"; ?></th>
            <th>Amount</th>
            <th>Deadline</th>
            <th>Status</th>
            <th>Payment</th>
            <th></th>
        </tr>
        <?php
        for ($i = 0; $i < $total; $i++) {
            $order = $orders[$i];
            ?>
            <tr>
                <td>#<?php echo esc($order["order_id"]); ?></td>
                <td><?php echo esc($order["gig_title"]); ?></td>
                <td>
                    <?php echo $asStudent ? esc($order["client_name"]) : esc($order["student_name"]); ?>
                </td>
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
                        <br /><span class="muted"><?php echo esc($order["txn_id"]); ?></span>
                    <?php } else { ?>
                        <span class="muted">unpaid</span>
                    <?php } ?>
                </td>
                <td>
                    <?php if (!$asStudent && !$order["payment_id"] && $order["status"] == "placed") { ?>
                        <a class="btn btn-small"
                           href="<?php echo BASE_URL; ?>/Client/View/checkout.php?order_id=<?php echo esc($order["order_id"]); ?>">
                            Pay now
                        </a>
                    <?php } else { ?>
                        <a class="btn btn-small btn-secondary"
                           href="<?php echo BASE_URL; ?>/Student/View/orderDetails.php?order_id=<?php echo esc($order["order_id"]); ?>">
                            Open
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include "footer.php"; ?>
