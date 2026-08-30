<?php
require "../../Model/init.php";

requirePost();
requireRole("student");

$studentId = currentUserId();
$action = cleanInput($_POST["action"] ?? "");

if (!isInList($action, array("create", "update", "delete"))) {
    setFlash("error", "Unknown action.");
    redirectTo(BASE_URL . "/Student/View/myGigs.php");
}

if ($action == "delete") {
    $gigId = cleanInput($_POST["gig_id"] ?? "");

    if (!isDigitsOnly($gigId)) {
        setFlash("error", "That gig does not exist.");
        redirectTo(BASE_URL . "/Student/View/myGigs.php");
    }

    if ($gigModel->deleteOwn($gigId, $studentId)) {
        setFlash("success", "Gig deleted.");
    } else {
        setFlash("error", "That gig could not be deleted.");
    }

    redirectTo(BASE_URL . "/Student/View/myGigs.php");
}

$title = cleanInput($_POST["title"] ?? "");
$description = cleanInput($_POST["description"] ?? "");
$categoryId = cleanInput($_POST["category_id"] ?? "");
$price = cleanInput($_POST["price_bdt"] ?? "");
$deliveryDays = cleanInput($_POST["delivery_days"] ?? "");

keepOldInput(array(
    "title"         => $title,
    "description"   => $description,
    "category_id"   => $categoryId,
    "price_bdt"     => $price,
    "delivery_days" => $deliveryDays
));

$errors = array();

if (isTooShort($title, 10) || isTooLong($title, 150)) {
    array_push($errors, "Title must be between 10 and 150 characters.");
}

if (isTooShort($description, 30) || isTooLong($description, 3000)) {
    array_push($errors, "Description must be between 30 and 3000 characters.");
}

if (!isDigitsOnly($categoryId) || !$gigModel->categoryExists($categoryId)) {
    array_push($errors, "Choose a valid category.");
}

if (!isValidPrice($price, MIN_GIG_PRICE, MAX_GIG_PRICE)) {
    array_push($errors, "Price must be between " . MIN_GIG_PRICE . " and " . MAX_GIG_PRICE . " " . CURRENCY . ".");
}

if (!isValidWholeNumber($deliveryDays, 1, MAX_DELIVERY_DAYS)) {
    array_push($errors, "Delivery time must be between 1 and " . MAX_DELIVERY_DAYS . " days.");
}

$priceValue = round($price, 2);
$daysValue = round($deliveryDays);

if ($action == "create") {
    if (count($errors) > 0) {
        setFlash("error", implode(" ", $errors));
        redirectTo(BASE_URL . "/Student/View/gigForm.php");
    }

    if ($gigModel->create($studentId, $categoryId, $title, $description, $priceValue, $daysValue)) {
        clearOldInput();
        setFlash("success", "Gig submitted. It goes live once the admin approves it.");
    } else {
        setFlash("error", "Could not save the gig. Please try again.");
    }

    redirectTo(BASE_URL . "/Student/View/myGigs.php");
}

$gigId = cleanInput($_POST["gig_id"] ?? "");

if (!isDigitsOnly($gigId)) {
    setFlash("error", "That gig does not exist.");
    redirectTo(BASE_URL . "/Student/View/myGigs.php");
}

$existing = $gigModel->findById($gigId);

if (!$existing) {
    setFlash("error", "That gig does not exist.");
    redirectTo(BASE_URL . "/Student/View/myGigs.php");
}

requireOwner($existing["student_id"]);

if (count($errors) > 0) {
    setFlash("error", implode(" ", $errors));
    redirectTo(BASE_URL . "/Student/View/gigForm.php?gig_id=" . $gigId);
}

if ($gigModel->update($gigId, $studentId, $categoryId, $title, $description, $priceValue, $daysValue)) {
    clearOldInput();
    setFlash("success", "Gig updated. It needs admin approval again before buyers can see it.");
} else {
    setFlash("error", "Could not update the gig.");
}

redirectTo(BASE_URL . "/Student/View/myGigs.php");
