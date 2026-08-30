<?php
// JSON endpoint for the live gig search (XMLHttpRequest).
require "../../../Model/init.php";

if ($_SERVER["REQUEST_METHOD"] != "GET") {
    echo json_encode(array(
        "success" => false,
        "message" => "Only GET is accepted on this endpoint.",
        "data"    => array()
    ));
    exit;
}

$keyword = cleanInput($_GET["keyword"] ?? "");
$categoryId = cleanInput($_GET["category_id"] ?? "0");
$maxPrice = cleanInput($_GET["max_price"] ?? "");
$sortBy = cleanInput($_GET["sort_by"] ?? "newest");

if (!isDigitsOnly($categoryId)) {
    $categoryId = 0;
}

if (!isInList($sortBy, array("newest", "price_low", "price_high", "fastest"))) {
    $sortBy = "newest";
}

if (isTooLong($keyword, 100)) {
    $keyword = substr($keyword, 0, 100);
}

if ($categoryId > 0) {
    setcookie("last_category", $categoryId, strtotime("+30 days"), "/");
}

$gigs = $gigModel->search($keyword, $categoryId, $maxPrice, $sortBy);

$payload = array();
$total = count($gigs);

for ($i = 0; $i < $total; $i++) {
    $gig = $gigs[$i];

    $shortDescription = substr($gig["description"], 0, 160);
    if (strlen($gig["description"]) > 160) {
        $shortDescription = $shortDescription . "...";
    }

    array_push($payload, array(
        "gig_id"            => $gig["gig_id"],
        "title"             => $gig["title"],
        "short_description" => $shortDescription,
        "category_name"     => $gig["category_name"],
        "student_name"      => $gig["student_name"],
        "price_bdt"         => round($gig["price_bdt"], 2),
        "currency"          => CURRENCY,
        "delivery_days"     => $gig["delivery_days"],
        "link"              => BASE_URL . "/Student/View/gigDetails.php?gig_id=" . $gig["gig_id"]
    ));
}

Header("Content-Type: application/json");
echo json_encode(array(
    "success" => true,
    "message" => "",
    "data"    => $payload
));
