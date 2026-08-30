<?php
require "../../Model/init.php";

requireRole("student");

$studentId = currentUserId();

$earnings = $orderModel->earningsOf($studentId);
$withdrawn = $adminModel->withdrawnTotal($studentId);
$requests = $adminModel->withdrawalsOf($studentId);
$reviews = $reviewModel->listByStudent($studentId);
$rating = $userModel->ratingOf($studentId);

$available = round($earnings["earned"] - $withdrawn, 2);
if ($available < 0) {
    $available = 0;
}

$pageTitle = "Earnings";
include "header.php";
?>

<h1>Earnings</h1>
<p class="muted">Money is counted only when an order reaches the <strong>completed</strong> status.</p>

<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney($earnings["earned"]); ?></span>
        <span class="label">Total earned</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney($withdrawn); ?></span>
        <span class="label">Already requested</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney($available); ?></span>
        <span class="label">Available to withdraw</span>
    </div>
</div>
<div class="clear"></div>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Student/Controller/withdrawController.php" method="post"
          onsubmit="return validateWithdraw()">
        <fieldset>
            <legend>Request a withdrawal</legend>

            <div class="field">
                <label for="amount_bdt">Amount (<?php echo CURRENCY; ?>)</label>
                <input type="text" id="amount_bdt" name="amount_bdt" />
                <span class="hint">You can request up to <?php echo formatMoney($available); ?>.</span>
                <span class="error" id="amount_bdtError"></span>
            </div>

            <div class="field">
                <label for="method">Send to</label>
                <select id="method" name="method">
                    <option value="bkash">bKash</option>
                    <option value="nagad">Nagad</option>
                    <option value="bank">Bank account</option>
                </select>
            </div>

            <div class="field">
                <label for="account_no">Account number</label>
                <input type="text" id="account_no" name="account_no" />
                <span class="hint">11 digits for bKash or Nagad, 10 to 20 for a bank account.</span>
                <span class="error" id="account_noError"></span>
            </div>

            <input type="submit" value="Request withdrawal" />
        </fieldset>
    </form>
</div>

<div class="col-half">
    <div class="card">
        <h3>Your rating</h3>
        <p class="rating">
            <?php
            if ($rating["total"] > 0) {
                echo esc($rating["average"]) . " / 5";
            } else {
                echo "<span class='muted'>Not rated yet</span>";
            }
            ?>
            <span class="muted">from <?php echo esc($rating["total"]); ?> review(s)</span>
        </p>

        <?php
        $totalReviews = count($reviews);
        $shown = 0;
        for ($i = 0; $i < $totalReviews; $i++) {
            if ($shown >= 3) {
                break;
            }
            $review = $reviews[$i];
            $shown = $shown + 1;
            ?>
            <div class="message-row">
                <span class="rating">
                    <?php
                    for ($star = 0; $star < $review["rating"]; $star++) {
                        echo "&#9733;";
                    }
                    ?>
                </span>
                <span class="muted">by <?php echo esc($review["client_name"]); ?></span>
                <p><?php echo esc($review["comment"]); ?></p>
            </div>
        <?php } ?>
    </div>
</div>
<div class="clear"></div>

<h2>Withdrawal history</h2>
<?php
$totalRequests = count($requests);
if ($totalRequests == 0) {
    ?>
    <div class="card">
        <p class="muted">You have not requested a withdrawal yet.</p>
    </div>
    <?php
} else {
    ?>
    <table>
        <tr>
            <th>Requested on</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Account</th>
            <th>Status</th>
        </tr>
        <?php
        for ($i = 0; $i < $totalRequests; $i++) {
            $item = $requests[$i];
            ?>
            <tr>
                <td><?php echo esc(date("d M Y", strtotime($item["requested_at"]))); ?></td>
                <td><?php echo formatMoney($item["amount_bdt"]); ?></td>
                <td><?php echo strtoupper(esc($item["method"])); ?></td>
                <td><?php echo esc($item["account_no"]); ?></td>
                <td>
                    <span class="badge badge-<?php echo esc($item["status"]); ?>">
                        <?php echo esc($item["status"]); ?>
                    </span>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php include "footer.php"; ?>
