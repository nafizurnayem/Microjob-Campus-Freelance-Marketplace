<?php
require "../../Model/init.php";

$gigId = cleanInput($_GET["gig_id"] ?? "");

if (!isDigitsOnly($gigId)) {
    setFlash("error", "That gig does not exist.");
    redirectTo(BASE_URL . "/Student/View/browseGigs.php");
}

$gig = $gigModel->findById($gigId);

if (!$gig) {
    setFlash("error", "That gig does not exist.");
    redirectTo(BASE_URL . "/Student/View/browseGigs.php");
}

$canSeeUnapproved = false;
if (isAdmin() || currentUserId() == $gig["student_id"]) {
    $canSeeUnapproved = true;
}

if ($gig["status"] != "approved" && !$canSeeUnapproved) {
    setFlash("error", "That gig is not available.");
    redirectTo(BASE_URL . "/Student/View/browseGigs.php");
}

$rating = $userModel->ratingOf($gig["student_id"]);
$reviews = $reviewModel->listByStudent($gig["student_id"]);
$skills = explode(",", $gig["skills"]);

$deliveryDate = date("d M Y", strtotime("+" . $gig["delivery_days"] . " days"));

$canHire = false;
if (isClient() && $gig["status"] == "approved" && $gig["student_status"] == "active") {
    $canHire = true;
}

$pageTitle = $gig["title"];
include "header.php";
?>

<div class="content">
    <div class="card">
        <span class="price"><?php echo formatMoney($gig["price_bdt"]); ?></span>
        <h1><?php echo esc($gig["title"]); ?></h1>
        <p class="meta">
            <?php echo esc($gig["category_name"]); ?> &middot;
            delivery in <?php echo esc($gig["delivery_days"]); ?> day(s)
            <?php if ($gig["status"] != "approved") { ?>
                &middot; <span class="badge badge-<?php echo esc($gig["status"]); ?>">
                    <?php echo esc($gig["status"]); ?>
                </span>
            <?php } ?>
        </p>
        <p><?php echo esc($gig["description"]); ?></p>
    </div>

    <?php if ($canHire) { ?>
        <div class="card">
            <h2>Hire <?php echo esc($gig["student_name"]); ?></h2>
            <p class="muted">
                You will pay <strong><?php echo formatMoney($gig["price_bdt"]); ?></strong>
                and the work is due by <strong><?php echo esc($deliveryDate); ?></strong>.
            </p>

            <form action="<?php echo BASE_URL; ?>/Student/Controller/orderController.php" method="post"
                  onsubmit="return validateOrder()">
                <input type="hidden" name="action" value="place" />
                <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />

                <div class="field">
                    <label for="requirement">What exactly do you need?</label>
                    <textarea id="requirement" name="requirement"><?php echo esc(oldInput("requirement")); ?></textarea>
                    <span class="hint">Be specific: topic, size, format, deadline notes.</span>
                    <span class="error" id="requirementError"></span>
                </div>

                <input type="submit" value="Place order" />
            </form>
        </div>
    <?php } else if (!isLoggedIn()) { ?>
        <div class="card">
            <p><a href="<?php echo BASE_URL; ?>/Student/View/login.php">Log in as a client</a> to hire this student.</p>
        </div>
    <?php } else if (isStudent()) { ?>
        <div class="card">
            <p class="muted">You are logged in as a student. Only client accounts can hire.</p>
        </div>
    <?php } ?>

    <h2>Reviews for this student</h2>
    <?php
    $totalReviews = count($reviews);
    if ($totalReviews == 0) {
        ?>
        <div class="card">
            <p class="muted">No review yet.</p>
        </div>
        <?php
    } else {
        $shown = 0;
        for ($i = 0; $i < $totalReviews; $i++) {
            if ($shown >= 5) {
                break;
            }
            $review = $reviews[$i];
            $shown = $shown + 1;
            ?>
            <div class="card">
                <p class="rating">
                    <?php
                    for ($star = 0; $star < $review["rating"]; $star++) {
                        echo "&#9733;";
                    }
                    ?>
                    <span class="muted">
                        by <?php echo esc($review["client_name"]); ?>
                        on <?php echo esc(date("d M Y", strtotime($review["created_at"]))); ?>
                    </span>
                </p>
                <p><?php echo esc($review["comment"]); ?></p>
                <p class="muted">Gig: <?php echo esc($review["gig_title"]); ?></p>
            </div>
        <?php }
    }
    ?>
</div>

<div class="sidebar">
    <div class="card">
        <h3><?php echo esc($gig["student_name"]); ?></h3>
        <p class="muted">
            <?php echo esc($gig["department"]); ?>, <?php echo esc($gig["university"]); ?>
        </p>
        <p class="rating">
            <?php
            if ($rating["total"] > 0) {
                echo esc($rating["average"]) . " / 5";
            } else {
                echo "<span class='muted'>Not rated yet</span>";
            }
            ?>
            <span class="muted">(<?php echo esc($rating["total"]); ?> review(s))</span>
        </p>
        <p><?php echo esc($gig["bio"]); ?></p>

        <?php
        $totalSkills = count($skills);
        if (trim($gig["skills"]) != "") {
            for ($i = 0; $i < $totalSkills; $i++) {
                ?>
                <span class="badge"><?php echo esc(trim($skills[$i])); ?></span>
            <?php }
        }
        ?>
    </div>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php
clearOldInput();
include "footer.php";
?>
