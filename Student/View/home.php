<?php
require "../../Model/init.php";

$latestGigs = $gigModel->search("", 0, 0, "newest");
$categories = $gigModel->allCategories();

$pageTitle = "Hire student talent on campus";
include "header.php";
?>

<div class="hero">
    <h1>Hire student talent from your own campus</h1>
    <p>
        MicroJob connects university students who can design, code, write and edit with the
        students, faculty members and alumni who need those small jobs done.
    </p>
    <a class="btn" href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php">Browse gigs</a>
    <?php if (!isLoggedIn()) { ?>
        <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/register.php">Start selling</a>
    <?php } ?>
</div>

<div class="col-third">
    <div class="card">
        <h3>1. Find the right student</h3>
        <p class="muted">
            Search the catalogue by category, keyword or budget. Every gig shows the price, the
            delivery time and the seller rating.
        </p>
    </div>
</div>
<div class="col-third">
    <div class="card">
        <h3>2. Hire and pay</h3>
        <p class="muted">
            Place the order with your requirement, then pay. You get a transaction record.
        </p>
    </div>
</div>
<div class="col-third">
    <div class="card">
        <h3>3. Receive and review</h3>
        <p class="muted">
            The student delivers before the deadline. Mark the order complete and leave a rating
            that helps the next buyer.
        </p>
    </div>
</div>
<div class="clear"></div>

<h2 class="spaced">Categories</h2>
<div class="card">
    <?php
    $totalCategories = count($categories);
    for ($i = 0; $i < $totalCategories; $i++) {
        $category = $categories[$i];
        ?>
        <a class="btn btn-small btn-secondary"
            href="<?php echo BASE_URL; ?>/Student/View/browseGigs.php?category_id=<?php echo $category["category_id"]; ?>">
            <?php echo esc($category["name"]); ?>
        </a>
    <?php } ?>
</div>

<h2 class="spaced">Latest gigs</h2>
<?php
$totalGigs = count($latestGigs);
if ($totalGigs == 0) {
    ?>
    <div class="card">
        <p class="muted">No gig has been published yet.</p>
    </div>
    <?php
} else {
    $shown = 0;
    for ($i = 0; $i < $totalGigs; $i++) {
        if ($shown >= 6) {
            break;
        }
        $gig = $latestGigs[$i];
        $shown = $shown + 1;
        ?>
        <div class="gig">
            <span class="price"><?php echo formatMoney($gig["price_bdt"]); ?></span>
            <h3>
                <a href="<?php echo BASE_URL; ?>/Student/View/gigDetails.php?gig_id=<?php echo $gig["gig_id"]; ?>">
                    <?php echo esc($gig["title"]); ?>
                </a>
            </h3>
            <p class="meta">
                <?php echo esc($gig["category_name"]); ?> &middot;
                by <?php echo esc($gig["student_name"]); ?> &middot;
                delivery in <?php echo esc($gig["delivery_days"]); ?> day(s)
            </p>
            <p><?php echo esc(substr($gig["description"], 0, 160)); ?>...</p>
        </div>
    <?php }
}
?>

<?php include "footer.php"; ?>
