<?php
require "../../Model/init.php";

requireLogin();

$orderId = cleanInput($_GET["order_id"] ?? "");

if (!isDigitsOnly($orderId)) {
    setFlash("error", "That order does not exist.");
    redirectTo(dashboardFor(currentRole()));
}

$order = $orderModel->findById($orderId);

if (!$order) {
    setFlash("error", "That order does not exist.");
    redirectTo(dashboardFor(currentRole()));
}

$isClientOfOrder = ($order["client_id"] == currentUserId());
$isStudentOfOrder = ($order["student_id"] == currentUserId());

if (!$isClientOfOrder && !$isStudentOfOrder && !isAdmin()) {
    setFlash("error", "You are not allowed to open that order.");
    redirectTo(dashboardFor(currentRole()));
}

$payment = $paymentModel->findByOrder($orderId);
$review = $reviewModel->findByOrder($orderId);
$messages = $orderModel->listMessages($orderId);

$isPaid = $payment ? true : false;

$pageTitle = "Order #" . $order["order_id"];
include "header.php";
?>

<h1>Order #<?php echo esc($order["order_id"]); ?></h1>
<p class="muted">
    Placed on <?php echo esc(date("d M Y, h:i A", strtotime($order["created_at"]))); ?>
</p>

<div class="content">
    <div class="card">
        <span class="price"><?php echo formatMoney($order["amount_bdt"]); ?></span>
        <h2><?php echo esc($order["gig_title"]); ?></h2>
        <p class="meta">
            Client: <?php echo esc($order["client_name"]); ?> &middot;
            Student: <?php echo esc($order["student_name"]); ?> &middot;
            Due <?php echo esc(date("d M Y", strtotime($order["deadline"]))); ?>
        </p>

        <h3>Requirement</h3>
        <p><?php echo esc($order["requirement"]); ?></p>

        <h3>Status</h3>
        <p>
            <span class="badge badge-<?php echo esc($order["status"]); ?>">
                <?php echo esc($order["status"]); ?>
            </span>
            <?php if ($isPaid) { ?>
                <span class="badge badge-paid">paid</span>
            <?php } else { ?>
                <span class="muted">payment pending</span>
            <?php } ?>
        </p>

        <!-- live status, refreshed by fetch() without reloading -->
        <p id="liveStatus" class="muted">Checking live status...</p>
        <button class="btn-small btn-secondary" onclick="refreshOrderStatus(<?php echo esc($order["order_id"]); ?>)">
            Refresh status
        </button>
    </div>

    <!-- actions -->
    <div class="card">
        <h3>What you can do now</h3>

        <?php if ($isClientOfOrder && !$isPaid && $order["status"] == "placed") { ?>
            <a class="btn" href="<?php echo BASE_URL; ?>/Client/View/checkout.php?order_id=<?php echo esc($order["order_id"]); ?>">
                Pay <?php echo formatMoney($order["amount_bdt"]); ?>
            </a>
            <form action="<?php echo BASE_URL; ?>/Student/Controller/orderController.php" method="post"
                  onsubmit="return confirm('Cancel this order?');">
                <input type="hidden" name="action" value="cancel" />
                <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />
                <input class="btn-danger btn-small" type="submit" value="Cancel order" />
            </form>
        <?php } ?>

        <?php if ($isStudentOfOrder && $isPaid && $order["status"] == "placed") { ?>
            <form action="<?php echo BASE_URL; ?>/Student/Controller/orderController.php" method="post">
                <input type="hidden" name="action" value="accept" />
                <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />
                <input type="submit" value="Accept this order" />
            </form>
        <?php } ?>

        <?php if ($isStudentOfOrder && $order["status"] == "accepted") { ?>
            <form action="<?php echo BASE_URL; ?>/Student/Controller/orderController.php" method="post">
                <input type="hidden" name="action" value="deliver" />
                <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />
                <input type="submit" value="Mark as delivered" />
            </form>
        <?php } ?>

        <?php if ($isClientOfOrder && $order["status"] == "delivered") { ?>
            <form action="<?php echo BASE_URL; ?>/Student/Controller/orderController.php" method="post">
                <input type="hidden" name="action" value="complete" />
                <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />
                <input type="submit" value="Confirm and complete" />
            </form>
        <?php } ?>

        <?php if ($isStudentOfOrder && !$isPaid) { ?>
            <p class="muted">Waiting for the client to pay. You can start once the payment arrives.</p>
        <?php } ?>

        <?php if ($order["status"] == "completed") { ?>
            <p class="muted">
                This order was completed on
                <?php echo esc(date("d M Y", strtotime($order["completed_at"]))); ?>.
            </p>
        <?php } ?>

        <?php if ($order["status"] == "cancelled") { ?>
            <p class="muted">This order was cancelled.</p>
        <?php } ?>
    </div>

    <!-- review -->
    <?php if ($order["status"] == "completed") { ?>
        <div class="card">
            <h3>Review</h3>

            <?php if ($review) { ?>
                <p class="rating">
                    <?php
                    for ($star = 0; $star < $review["rating"]; $star++) {
                        echo "&#9733;";
                    }
                    ?>
                    <span class="muted">
                        on <?php echo esc(date("d M Y", strtotime($review["created_at"]))); ?>
                    </span>
                </p>
                <p><?php echo esc($review["comment"]); ?></p>

            <?php } else if ($isClientOfOrder) { ?>
                <form action="<?php echo BASE_URL; ?>/Client/Controller/reviewController.php" method="post"
                      onsubmit="return validateReview()">
                    <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />

                    <div class="field">
                        <label for="rating">Rating</label>
                        <select id="rating" name="rating">
                            <option value="">-- choose --</option>
                            <option value="5">5 - excellent</option>
                            <option value="4">4 - good</option>
                            <option value="3">3 - average</option>
                            <option value="2">2 - poor</option>
                            <option value="1">1 - very poor</option>
                        </select>
                        <span class="error" id="ratingError"></span>
                    </div>

                    <div class="field">
                        <label for="comment">Comment</label>
                        <textarea id="comment" name="comment"></textarea>
                        <span class="error" id="commentError"></span>
                    </div>

                    <input type="submit" value="Submit review" />
                </form>

            <?php } else { ?>
                <p class="muted">The client has not left a review yet.</p>
            <?php } ?>
        </div>
    <?php } ?>

    <!-- message thread -->
    <div class="card">
        <h3>Messages</h3>

        <?php
        $totalMessages = count($messages);
        if ($totalMessages == 0) {
            ?>
            <p class="muted">No message yet.</p>
            <?php
        } else {
            for ($i = 0; $i < $totalMessages; $i++) {
                $message = $messages[$i];
                ?>
                <div class="message-row">
                    <span class="who"><?php echo esc($message["sender_name"]); ?></span>
                    <span class="muted">
                        (<?php echo esc($message["sender_role"]); ?>)
                        <?php echo esc(date("d M Y, h:i A", strtotime($message["sent_at"]))); ?>
                    </span>
                    <p><?php echo esc($message["body"]); ?></p>
                </div>
            <?php }
        }
        ?>

        <?php if ($isClientOfOrder || $isStudentOfOrder) { ?>
            <form class="spaced" action="<?php echo BASE_URL; ?>/Student/Controller/orderController.php" method="post"
                  onsubmit="return validateMessage()">
                <input type="hidden" name="action" value="message" />
                <input type="hidden" name="order_id" value="<?php echo esc($order["order_id"]); ?>" />

                <div class="field">
                    <label for="body">Write a message</label>
                    <textarea id="body" name="body"></textarea>
                    <span class="error" id="bodyError"></span>
                </div>

                <input type="submit" value="Send message" />
            </form>
        <?php } ?>
    </div>
</div>

<!-- payment summary -->
<div class="sidebar">
    <div class="card">
        <h3>Payment</h3>
        <?php if ($isPaid) { ?>
            <p><strong><?php echo formatMoney($payment["amount_bdt"]); ?></strong></p>
            <p class="muted">Method: <?php echo strtoupper(esc($payment["method"])); ?></p>
            <p class="muted">Account ending: <?php echo esc($payment["account_last4"]); ?></p>
            <p class="muted">Transaction: <?php echo esc($payment["txn_id"]); ?></p>
            <p class="muted">
                Paid on <?php echo esc(date("d M Y, h:i A", strtotime($payment["paid_at"]))); ?>
            </p>
        <?php } else { ?>
            <p class="muted">This order has not been paid yet.</p>
            <?php if ($isClientOfOrder && $order["status"] == "placed") { ?>
                <a class="btn btn-small"
                   href="<?php echo BASE_URL; ?>/Client/View/checkout.php?order_id=<?php echo esc($order["order_id"]); ?>">
                    Pay now
                </a>
            <?php } ?>
        <?php } ?>
    </div>
</div>
<div class="clear"></div>

<script>
    var API_BASE = "<?php echo BASE_URL; ?>/Student/Controller/api";
</script>
<script src="<?php echo BASE_URL; ?>/Student/View/assets/ajax.js"></script>
<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>
<script>
    refreshOrderStatus(<?php echo esc($order["order_id"]); ?>);
</script>

<?php include "footer.php"; ?>
