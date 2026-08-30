<?php
require "../../Model/init.php";

requireRole("student");

$gigs = $gigModel->listByStudent(currentUserId());

$pageTitle = "My gigs";
include "header.php";
?>

<h1>My gigs</h1>
<p class="muted">
    A new or edited gig goes back to <strong>pending</strong> and becomes visible to buyers only
    after the admin approves it.
</p>

<div class="card">
    <a class="btn" href="<?php echo BASE_URL; ?>/Student/View/gigForm.php">Publish a new gig</a>
</div>

<?php
$total = count($gigs);
if ($total == 0) {
    ?>
    <div class="card">
        <p class="muted">You have not published any gig yet.</p>
    </div>
    <?php
} else {
    ?>
    <table>
        <tr>
            <th>Title</th>
            <th>Category</th>
            <th>Price</th>
            <th>Delivery</th>
            <th>Status</th>
            <th>Created</th>
            <th></th>
        </tr>
        <?php
        for ($i = 0; $i < $total; $i++) {
            $gig = $gigs[$i];
            ?>
            <tr>
                <td>
                    <a href="<?php echo BASE_URL; ?>/Student/View/gigDetails.php?gig_id=<?php echo esc($gig["gig_id"]); ?>">
                        <?php echo esc($gig["title"]); ?>
                    </a>
                </td>
                <td><?php echo esc($gig["category_name"]); ?></td>
                <td><?php echo formatMoney($gig["price_bdt"]); ?></td>
                <td><?php echo esc($gig["delivery_days"]); ?> day(s)</td>
                <td>
                    <span class="badge badge-<?php echo esc($gig["status"]); ?>">
                        <?php echo esc($gig["status"]); ?>
                    </span>
                </td>
                <td><?php echo esc(date("d M Y", strtotime($gig["created_at"]))); ?></td>
                <td>
                    <a class="btn btn-small btn-secondary"
                       href="<?php echo BASE_URL; ?>/Student/View/gigForm.php?gig_id=<?php echo esc($gig["gig_id"]); ?>">
                        Edit
                    </a>

                    <form action="<?php echo BASE_URL; ?>/Student/Controller/gigController.php" method="post"
                          onsubmit="return confirm('Delete this gig? Orders already placed on it will also be removed.');">
                        <input type="hidden" name="action" value="delete" />
                        <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />
                        <input class="btn-small btn-danger" type="submit" value="Delete" />
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>
<?php } ?>

<?php include "footer.php"; ?>
