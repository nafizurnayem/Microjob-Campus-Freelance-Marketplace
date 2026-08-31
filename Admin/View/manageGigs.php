<?php
require "../../Model/init.php";

requireRole("admin");

$filter = cleanInput($_GET["status"] ?? "pending");

if (!isInList($filter, array("pending", "approved", "rejected"))) {
    $filter = "pending";
}

$gigs = $gigModel->listByStatus($filter);
$categories = $gigModel->allCategories();

$pageTitle = "Moderate gigs";
include "../../Student/View/header.php";
?>

<h1>Moderate gigs</h1>

<div class="card">
    <a class="btn btn-small <?php if ($filter != "pending") { echo "btn-secondary"; } ?>"
       href="<?php echo BASE_URL; ?>/Admin/View/manageGigs.php?status=pending">
        Pending (<?php echo esc($gigModel->countByStatus("pending")); ?>)
    </a>
    <a class="btn btn-small <?php if ($filter != "approved") { echo "btn-secondary"; } ?>"
       href="<?php echo BASE_URL; ?>/Admin/View/manageGigs.php?status=approved">
        Approved (<?php echo esc($gigModel->countByStatus("approved")); ?>)
    </a>
    <a class="btn btn-small <?php if ($filter != "rejected") { echo "btn-secondary"; } ?>"
       href="<?php echo BASE_URL; ?>/Admin/View/manageGigs.php?status=rejected">
        Rejected (<?php echo esc($gigModel->countByStatus("rejected")); ?>)
    </a>
</div>

<?php
$total = count($gigs);
if ($total == 0) {
    ?>
    <div class="card">
        <p class="muted">No gig with status <?php echo esc($filter); ?>.</p>
    </div>
    <?php
} else {
    for ($i = 0; $i < $total; $i++) {
        $gig = $gigs[$i];
        ?>
        <div class="gig">
            <span class="price"><?php echo formatMoney($gig["price_bdt"]); ?></span>
            <h3>
                <a href="<?php echo BASE_URL; ?>/Student/View/gigDetails.php?gig_id=<?php echo esc($gig["gig_id"]); ?>">
                    <?php echo esc($gig["title"]); ?>
                </a>
            </h3>
            <p class="meta">
                <?php echo esc($gig["category_name"]); ?> &middot;
                by <?php echo esc($gig["student_name"]); ?> &middot;
                delivery in <?php echo esc($gig["delivery_days"]); ?> day(s) &middot;
                submitted <?php echo esc(date("d M Y", strtotime($gig["created_at"]))); ?>
            </p>
            <p><?php echo esc(substr($gig["description"], 0, 300)); ?></p>

            <?php if ($gig["status"] != "approved") { ?>
                <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post">
                    <input type="hidden" name="action" value="gig_status" />
                    <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />
                    <input type="hidden" name="status" value="approved" />
                    <input class="btn-small" type="submit" value="Approve" />
                </form>
            <?php } ?>

            <?php if ($gig["status"] != "rejected") { ?>
                <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post">
                    <input type="hidden" name="action" value="gig_status" />
                    <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />
                    <input type="hidden" name="status" value="rejected" />
                    <input class="btn-small btn-secondary" type="submit" value="Reject" />
                </form>
            <?php } ?>

            <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post"
                  onsubmit="return confirm('Remove this gig and every order placed on it?');">
                <input type="hidden" name="action" value="gig_delete" />
                <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />
                <input class="btn-small btn-danger" type="submit" value="Remove" />
            </form>
        </div>
    <?php }
}
?>

<h2 class="spaced">Categories</h2>

<div class="col-half">
    <div class="card">
        <table>
            <tr>
                <th>Category</th>
                <th></th>
            </tr>
            <?php
            $totalCategories = count($categories);
            for ($i = 0; $i < $totalCategories; $i++) {
                $category = $categories[$i];
                ?>
                <tr>
                    <td><?php echo esc($category["name"]); ?></td>
                    <td>
                        <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post">
                            <input type="hidden" name="action" value="category_delete" />
                            <input type="hidden" name="category_id" value="<?php echo esc($category["category_id"]); ?>" />
                            <input class="btn-small btn-danger" type="submit" value="Delete" />
                        </form>
                    </td>
                </tr>
            <?php } ?>
        </table>
    </div>
</div>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Admin/Controller/adminController.php" method="post"
          onsubmit="return validateCategory()">
        <input type="hidden" name="action" value="category_add" />
        <fieldset>
            <legend>Add a category</legend>
            <div class="field">
                <label for="name">Category name</label>
                <input type="text" id="name" name="name" />
                <span class="error" id="nameError"></span>
            </div>
            <input type="submit" value="Add category" />
            <p class="hint spaced">
                A category that still holds gigs cannot be deleted.
            </p>
        </fieldset>
    </form>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php include "../../Student/View/footer.php"; ?>
