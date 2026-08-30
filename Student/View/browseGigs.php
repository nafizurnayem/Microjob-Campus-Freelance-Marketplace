<?php
// Gig catalogue. The list is redrawn by assets/ajax.js without reloading.
require "../../Model/init.php";

$categories = $gigModel->allCategories();

$keyword = cleanInput($_GET["keyword"] ?? "");
$maxPrice = cleanInput($_GET["max_price"] ?? "");
$sortBy = cleanInput($_GET["sort_by"] ?? "newest");

$categoryId = cleanInput($_GET["category_id"] ?? "");
if ($categoryId == "") {
    $categoryId = $_COOKIE["last_category"] ?? "";
}
if (!isDigitsOnly($categoryId)) {
    $categoryId = 0;
}

if (!isInList($sortBy, array("newest", "price_low", "price_high", "fastest"))) {
    $sortBy = "newest";
}

$gigs = $gigModel->search($keyword, $categoryId, $maxPrice, $sortBy);

$pageTitle = "Browse gigs";
include "header.php";
?>

<h1>Browse gigs</h1>
<p class="muted">
    <span id="resultCount"><?php echo count($gigs); ?> gig(s) found</span>.
    Prices are in <?php echo CURRENCY; ?>.
</p>

<div class="sidebar">
    <div class="card">
        <h3>Filter</h3>

        <div class="field">
            <label for="keyword">Keyword</label>
            <input type="text" id="keyword" name="keyword" value="<?php echo esc($keyword); ?>"
                   onkeyup="searchGigs()" />
        </div>

        <div class="field">
            <label for="category_id">Category</label>
            <select id="category_id" name="category_id" onchange="rememberCategory()">
                <option value="0">All categories</option>
                <?php
                $totalCategories = count($categories);
                for ($i = 0; $i < $totalCategories; $i++) {
                    $category = $categories[$i];
                    ?>
                    <option value="<?php echo esc($category["category_id"]); ?>"
                        <?php if ($categoryId == $category["category_id"]) { echo "selected"; } ?>>
                        <?php echo esc($category["name"]); ?>
                    </option>
                <?php } ?>
            </select>
        </div>

        <div class="field">
            <label for="max_price">Maximum price (<?php echo CURRENCY; ?>)</label>
            <input type="text" id="max_price" name="max_price" value="<?php echo esc($maxPrice); ?>"
                   onkeyup="searchGigs()" />
            <span class="hint">Leave empty for any price.</span>
        </div>

        <div class="field">
            <label for="sort_by">Sort by</label>
            <select id="sort_by" name="sort_by" onchange="searchGigs()">
                <option value="newest" <?php if ($sortBy == "newest") { echo "selected"; } ?>>Newest first</option>
                <option value="price_low" <?php if ($sortBy == "price_low") { echo "selected"; } ?>>Price: low to high</option>
                <option value="price_high" <?php if ($sortBy == "price_high") { echo "selected"; } ?>>Price: high to low</option>
                <option value="fastest" <?php if ($sortBy == "fastest") { echo "selected"; } ?>>Fastest delivery</option>
            </select>
        </div>

        <button onclick="searchGigs()">Apply filter</button>
    </div>
</div>

<div class="content">
    <div id="results">
        <?php
        $totalGigs = count($gigs);
        if ($totalGigs == 0) {
            ?>
            <div class="card">
                <p class="muted">No gig matches your filter. Try a different keyword or a higher price.</p>
            </div>
            <?php
        } else {
            for ($i = 0; $i < $totalGigs; $i++) {
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
                        delivery in <?php echo esc($gig["delivery_days"]); ?> day(s)
                    </p>
                    <p><?php echo esc(substr($gig["description"], 0, 160)); ?>...</p>
                    <a class="btn btn-small"
                       href="<?php echo BASE_URL; ?>/Student/View/gigDetails.php?gig_id=<?php echo esc($gig["gig_id"]); ?>">
                        View details
                    </a>
                </div>
            <?php }
        }
        ?>
    </div>
</div>
<div class="clear"></div>

<script>
    var API_BASE = "<?php echo BASE_URL; ?>/Student/Controller/api";
</script>
<script src="<?php echo BASE_URL; ?>/Student/View/assets/ajax.js"></script>

<?php include "footer.php"; ?>
