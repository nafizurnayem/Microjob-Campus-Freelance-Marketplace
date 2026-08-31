<?php
require "../../Model/init.php";

requireRole("admin");

$totals = $adminModel->platformTotals();
$pendingGigs = $gigModel->listByStatus("pending");
$withdrawals = $adminModel->allWithdrawals();

$pageTitle = "Admin dashboard";
include "../../Student/View/header.php";
?>

<h1>Administration</h1>
<p class="muted">Moderate gigs, manage accounts and watch the money move.</p>

<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo esc($totals["students"]); ?></span>
        <span class="label">Students</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo esc($totals["clients"]); ?></span>
        <span class="label">Clients</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo esc($totals["gigs"]); ?></span>
        <span class="label">Gigs (<?php echo esc($totals["pending_gigs"]); ?> pending)</span>
    </div>
</div>
<div class="clear"></div>

<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo esc($totals["orders"]); ?></span>
        <span class="label">Orders placed</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo formatMoney($totals["revenue"]); ?></span>
        <span class="label">Payments collected</span>
    </div>
</div>
<div class="col-third">
    <div class="stat">
        <span class="value"><?php echo esc($totals["pending_withdrawals"]); ?></span>
        <span class="label">Withdrawal requests</span>
    </div>
</div>
<div class="clear"></div>

<div class="card">
    <a class="btn" href="<?php echo BASE_URL; ?>/Admin/View/manageGigs.php">Moderate gigs</a>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Admin/View/manageUsers.php">Manage users</a>
    <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Admin/View/reports.php">Transaction report</a>
</div>

<h2>Gigs waiting for approval</h2>
<?php if (count($pendingGigs) == 0) { ?>
    <div class="card">
        <p class="muted">Nothing is waiting for approval.</p>
    </div>
<?php } else { ?>
    <table>
        <tr>
            <th>Gig</th>
            <th>Student</th>
            <th>Price</th>
            <th>Delivery</th>
            <th></th>
        </tr>
        <?php
        $total = count($pendingGigs);
        for ($i = 0; $i < $total; $i++) {
            $gig = $pendingGigs[$i];
            ?>
            <tr>
                <td><?php echo esc($gig["title"]); ?></td>
                <td><?php echo esc($gig["student_name"]); ?></td>
                <td><?php echo formatMoney($gig["price_bdt"]); ?></td>
                <td><?php echo esc($gig["delivery_days"]); ?> day(s)</td>
                <td>
                    <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post">
                        <input type="hidden" name="action" value="gig_status" />
                        <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />
                        <input type="hidden" name="status" value="approved" />
                        <input class="btn-small" type="submit" value="Approve" />
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<h2 class="spaced">Withdrawal requests</h2>
<?php if (count($withdrawals) == 0) { ?>
    <div class="card">
        <p class="muted">No withdrawal has been requested yet.</p>
    </div>
<?php } else { ?>
    <table>
        <tr>
            <th>Student</th>
            <th>Amount</th>
            <th>Method</th>
            <th>Account</th>
            <th>Requested</th>
            <th>Status</th>
            <th></th>
        </tr>
        <?php
        $total = count($withdrawals);
        for ($i = 0; $i < $total; $i++) {
            $item = $withdrawals[$i];
            ?>
            <tr>
                <td><?php echo esc($item["student_name"]); ?></td>
                <td><?php echo formatMoney($item["amount_bdt"]); ?></td>
                <td><?php echo strtoupper(esc($item["method"])); ?></td>
                <td><?php echo esc($item["account_no"]); ?></td>
                <td><?php echo esc(date("d M Y", strtotime($item["requested_at"]))); ?></td>
                <td>
                    <span class="badge badge-<?php echo esc($item["status"]); ?>">
                        <?php echo esc($item["status"]); ?>
                    </span>
                </td>
                <td>
                    <?php if ($item["status"] == "requested") { ?>
                        <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post">
                            <input type="hidden" name="action" value="withdrawal_paid" />
                            <input type="hidden" name="withdrawal_id" value="<?php echo esc($item["withdrawal_id"]); ?>" />
                            <input class="btn-small" type="submit" value="Mark paid" />
                        </form>
                    <?php } ?>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include "../../Student/View/footer.php"; ?>
