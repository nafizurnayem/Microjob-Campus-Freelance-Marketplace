<?php
require "../../Model/init.php";

requireRole("student");

$categories = $gigModel->allCategories();

$gigId = cleanInput($_GET["gig_id"] ?? "");
$editing = false;
$gig = null;

if ($gigId != "") {
    if (!isDigitsOnly($gigId)) {
        setFlash("error", "That gig does not exist.");
        redirectTo(BASE_URL . "/Student/View/myGigs.php");
    }

    $gig = $gigModel->findById($gigId);

    if (!$gig) {
        setFlash("error", "That gig does not exist.");
        redirectTo(BASE_URL . "/Student/View/myGigs.php");
    }

    requireOwner($gig["student_id"]);
    $editing = true;
}

function gigValue($field, $gig, $fallback)
{
    $old = oldInput($field);
    if ($old != "") {
        return $old;
    }
    if ($gig) {
        return $gig[$field];
    }
    return $fallback;
}

$pageTitle = $editing ? "Edit gig" : "Publish a gig";
include "header.php";
?>

<h1><?php echo $editing ? "Edit gig" : "Publish a new gig"; ?></h1>
<p class="muted">Describe one clear service with a fixed price and a delivery time.</p>

<div class="col-half">
    <form action="<?php echo BASE_URL; ?>/Student/Controller/gigController.php" method="post"
          onsubmit="return validateGig()">
        <input type="hidden" name="action" value="<?php echo $editing ? "update" : "create"; ?>" />
        <?php if ($editing) { ?>
            <input type="hidden" name="gig_id" value="<?php echo esc($gig["gig_id"]); ?>" />
        <?php } ?>

        <fieldset>
            <legend>Gig details</legend>

            <div class="field">
                <label for="title">Title</label>
                <input type="text" id="title" name="title"
                       value="<?php echo esc(gigValue("title", $gig, "")); ?>" />
                <span class="hint">Start with "I will ...". At least 10 characters.</span>
                <span class="error" id="titleError"></span>
            </div>

            <div class="field">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="0">-- choose --</option>
                    <?php
                    $selectedCategory = gigValue("category_id", $gig, 0);
                    $totalCategories = count($categories);
                    for ($i = 0; $i < $totalCategories; $i++) {
                        $category = $categories[$i];
                        ?>
                        <option value="<?php echo esc($category["category_id"]); ?>"
                            <?php if ($selectedCategory == $category["category_id"]) { echo "selected"; } ?>>
                            <?php echo esc($category["name"]); ?>
                        </option>
                    <?php } ?>
                </select>
                <span class="error" id="category_idError"></span>
            </div>

            <div class="field">
                <label for="description">Description</label>
                <textarea id="description" name="description"><?php echo esc(gigValue("description", $gig, "")); ?></textarea>
                <span class="hint">At least 30 characters. Say what the buyer receives.</span>
                <span class="error" id="descriptionError"></span>
            </div>

            <div class="field">
                <label for="price_bdt">Price (<?php echo CURRENCY; ?>)</label>
                <input type="text" id="price_bdt" name="price_bdt"
                       value="<?php echo esc(gigValue("price_bdt", $gig, "")); ?>" />
                <span class="hint">Between <?php echo MIN_GIG_PRICE; ?> and <?php echo MAX_GIG_PRICE; ?>.</span>
                <span class="error" id="price_bdtError"></span>
            </div>

            <div class="field">
                <label for="delivery_days">Delivery time (days)</label>
                <input type="text" id="delivery_days" name="delivery_days"
                       value="<?php echo esc(gigValue("delivery_days", $gig, "")); ?>" />
                <span class="hint">1 to <?php echo MAX_DELIVERY_DAYS; ?> days.</span>
                <span class="error" id="delivery_daysError"></span>
            </div>

            <input type="submit" value="<?php echo $editing ? "Save changes" : "Publish gig"; ?>" />
            <a class="btn btn-secondary" href="<?php echo BASE_URL; ?>/Student/View/myGigs.php">Cancel</a>
        </fieldset>
    </form>
</div>

<div class="col-half">
    <div class="card">
        <h3>Tips for a gig that sells</h3>
        <p class="muted">Write the title as a promise: "I will design a seminar poster in 2 days".</p>
        <p class="muted">Say exactly what the buyer gets: file formats, number of revisions, length.</p>
        <p class="muted">Price honestly. A fair price with a good review beats an unrealistic one.</p>
        <p class="muted">Only promise a delivery time you can actually meet on top of your classes.</p>
    </div>
</div>
<div class="clear"></div>

<script src="<?php echo BASE_URL; ?>/Student/View/assets/validate.js"></script>

<?php
clearOldInput();
include "footer.php";
?>
