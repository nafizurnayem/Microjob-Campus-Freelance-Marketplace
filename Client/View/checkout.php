<?php
require "../../Model/init.php";

requireRole("client");

$orderId = cleanInput($_GET["order_id"] ?? "");

if (!isDigitsOnly($orderId)) {
    setFlash("error", "That order does not exist.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

$order = $orderModel->findById($orderId);

if (!$order) {
    setFlash("error", "That order does not exist.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

if ($order["client_id"] != currentUserId()) {
    setFlash("error", "You are not allowed to pay for that order.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

if ($paymentModel->isOrderPaid($orderId)) {
    setFlash("error", "This order is already paid.");
    redirectTo(BASE_URL . "/Student/View/orderDetails.php?order_id=" . $orderId);
}

if ($order["status"] == "cancelled") {
    setFlash("error", "This order was cancelled.");
    redirectTo(BASE_URL . "/Student/View/orderDetails.php?order_id=" . $orderId);
}

$pageTitle = "Checkout";
include "../../Student/View/header.php";
?>

<h1>Checkout</h1>
<p class="muted">
    Payments on this site are <strong>simulated for the course project</strong>. No money moves
    and no real gateway is contacted. Do not type a real account or card number.
</p>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Client/Controller/paymentController.php" method="post"
          onsubmit="return validatePayment()">
        <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />

        <fieldset>
            <legend>Pay <?php echo formatMoney($order["amount_bdt"]); ?></legend>

            <div class="field">
                <label for="method">Payment method</label>
                <select id="method" name="method" onchange="paymentMethodChanged()">
                    <option value="">-- choose --</option>
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="bank">Bank transfer</option>
                    <option value="card">Debit / credit card (Visa, Mastercard)</option>
                </select>
                <span class="error" id="methodError"></span>
            </div>

            <div class="field">
                <label for="account_no" id="accountLabel">Account number</label>
                <input type="text" id="account_no" name="account_no" />
                <span class="hint" id="accountHint">Choose a payment method first.</span>
                <span class="error" id="account_noError"></span>
            </div>

            <p class="hint">
                Only the last 4 digits are stored. The rest is discarded immediately.
            </p>

            <input type="submit" value="Pay <?php echo formatMoney($order["amount_bdt"]); ?>" />
            <a class="btn btn-secondary"
               href="<?php echo BASE_URL; ?>/Student/View/orderDetails.php?order_id=<?php echo esc($order["order_id"]); ?>">
                Back to order
            </a>
        </fieldset>
    </form>
</div>

<div class="col-half">
    <div class="card">
        <h3>Order summary</h3>
        <table>
            <tr>
                <th>Order</th>
                <td>#<?php echo esc($order["order_id"]); ?></td>
            </tr>
            <tr>
                <th>Gig</th>
                <td><?php echo esc($order["gig_title"]); ?></td>
            </tr>
            <tr>
                <th>Student</th>
                <td><?php echo esc($order["student_name"]); ?></td>
            </tr>
            <tr>
                <th>Delivery by</th>
                <td><?php echo esc(date("d M Y", strtotime($order["deadline"]))); ?></td>
            </tr>
            <tr>
                <th>Amount</th>
                <td><strong><?php echo formatMoney($order["amount_bdt"]); ?></strong></td>
            </tr>
        </table>
        <p class="hint spaced">
            The amount is taken from the order on the server, not from this page, so it cannot be
            changed from the browser.
        </p>
    </div>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php include "../../Student/View/footer.php"; ?>
