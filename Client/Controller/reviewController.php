<?php
require "../../Model/init.php";

requirePost();
requireRole("client");

$orderId = cleanInput($_POST["order_id"] ?? "");
$rating = cleanInput($_POST["rating"] ?? "");
$comment = cleanInput($_POST["comment"] ?? "");

if (!isDigitsOnly($orderId)) {
    setFlash("error", "That order does not exist.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

$order = $orderModel->findById($orderId);

if (!$order) {
    setFlash("error", "That order does not exist.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

if ($order["client_id"] != currentUserId()) {
    setFlash("error", "You can only review your own order.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

$orderPage = BASE_URL . "/Student/View/orderDetails.php?order_id=" . $orderId;

if ($order["status"] != "completed") {
    setFlash("error", "You can review an order only after it is completed.");
    redirectTo($orderPage);
}

if ($reviewModel->findByOrder($orderId)) {
    setFlash("error", "You have already reviewed this order.");
    redirectTo($orderPage);
}

if (!isValidWholeNumber($rating, 1, 5)) {
    setFlash("error", "Choose a rating from 1 to 5.");
    redirectTo($orderPage);
}

if (isTooLong($comment, 500)) {
    setFlash("error", "The comment must be 500 characters or less.");
    redirectTo($orderPage);
}

$ratingValue = round($rating);

if ($reviewModel->create($orderId, currentUserId(), $order["student_id"], $ratingValue, $comment)) {
    setFlash("success", "Thank you. Your review is now on the student profile.");
} else {
    setFlash("error", "Could not save the review.");
}

redirectTo($orderPage);
