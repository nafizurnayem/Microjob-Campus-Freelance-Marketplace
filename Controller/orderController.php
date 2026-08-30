<?php
// One entry point for every order action: place, accept, deliver, complete, cancel, message.
require "../../Model/init.php";

requirePost();
requireLogin();

$action = cleanInput($_POST["action"] ?? "");
$userId = currentUserId();

if (!isInList($action, array("place", "accept", "deliver", "complete", "cancel", "message"))) {
    setFlash("error", "Unknown action.");
    redirectTo(dashboardFor(currentRole()));
}

if ($action == "place") {
    requireRole("client");

    $gigId = cleanInput($_POST["gig_id"] ?? "");
    $requirement = cleanInput($_POST["requirement"] ?? "");

    keepOldInput(array("requirement" => $requirement));

    if (!isDigitsOnly($gigId)) {
        setFlash("error", "That gig does not exist.");
        redirectTo(BASE_URL . "/Student/View/browseGigs.php");
    }

    $gig = $gigModel->findById($gigId);

    if (!$gig || $gig["status"] != "approved" || $gig["student_status"] != "active") {
        setFlash("error", "That gig is not available for hire.");
        redirectTo(BASE_URL . "/Student/View/browseGigs.php");
    }

    if ($gig["student_id"] == $userId) {
        setFlash("error", "You cannot order your own gig.");
        redirectTo(BASE_URL . "/Student/View/gigDetails.php?gig_id=" . $gigId);
    }

    if (isTooShort($requirement, 15) || isTooLong($requirement, 2000)) {
        setFlash("error", "Describe your requirement in 15 to 2000 characters.");
        redirectTo(BASE_URL . "/Student/View/gigDetails.php?gig_id=" . $gigId);
    }

    $amount = round($gig["price_bdt"], 2);
    $deadline = date("Y-m-d", strtotime("+" . $gig["delivery_days"] . " days"));

    $orderId = $orderModel->create($gigId, $userId, $gig["student_id"], $requirement, $amount, $deadline);

    if ($orderId == 0) {
        setFlash("error", "Could not place the order. Please try again.");
        redirectTo(BASE_URL . "/Student/View/gigDetails.php?gig_id=" . $gigId);
    }

    clearOldInput();
    setFlash("success", "Order placed. Complete the payment to start the work.");
    redirectTo(BASE_URL . "/Client/View/checkout.php?order_id=" . $orderId);
}

$orderId = cleanInput($_POST["order_id"] ?? "");

if (!isDigitsOnly($orderId)) {
    setFlash("error", "That order does not exist.");
    redirectTo(dashboardFor(currentRole()));
}

$order = $orderModel->findById($orderId);

if (!$order) {
    setFlash("error", "That order does not exist.");
    redirectTo(dashboardFor(currentRole()));
}

$isClientOfOrder = ($order["client_id"] == $userId);
$isStudentOfOrder = ($order["student_id"] == $userId);

if (!$isClientOfOrder && !$isStudentOfOrder) {
    setFlash("error", "You are not allowed to open that order.");
    redirectTo(dashboardFor(currentRole()));
}

$orderPage = BASE_URL . "/Student/View/orderDetails.php?order_id=" . $orderId;

if ($action == "accept") {
    requireRole("student");

    if (!$isStudentOfOrder) {
        setFlash("error", "That order is not yours.");
        redirectTo(BASE_URL . "/Student/View/myOrders.php");
    }

    if ($orderModel->acceptByStudent($orderId, $userId)) {
        setFlash("success", "Order accepted. Deliver it before " . date("d M Y", strtotime($order["deadline"])) . ".");
    } else {
        setFlash("error", "This order cannot be accepted. It must be paid and still waiting.");
    }

    redirectTo($orderPage);
}

if ($action == "deliver") {
    requireRole("student");

    if (!$isStudentOfOrder) {
        setFlash("error", "That order is not yours.");
        redirectTo(BASE_URL . "/Student/View/myOrders.php");
    }

    if ($orderModel->deliverByStudent($orderId, $userId)) {
        setFlash("success", "Marked as delivered. The client will confirm it.");
    } else {
        setFlash("error", "Only an accepted order can be delivered.");
    }

    redirectTo($orderPage);
}

if ($action == "complete") {
    requireRole("client");

    if (!$isClientOfOrder) {
        setFlash("error", "That order is not yours.");
        redirectTo(BASE_URL . "/Student/View/myOrders.php");
    }

    if ($orderModel->completeByClient($orderId, $userId)) {
        setFlash("success", "Order completed. You can leave a review now.");
    } else {
        setFlash("error", "Only a delivered order can be marked complete.");
    }

    redirectTo($orderPage);
}

if ($action == "cancel") {
    requireRole("client");

    if (!$isClientOfOrder) {
        setFlash("error", "That order is not yours.");
        redirectTo(BASE_URL . "/Student/View/myOrders.php");
    }

    if ($orderModel->cancelByClient($orderId, $userId)) {
        setFlash("success", "Order cancelled.");
    } else {
        setFlash("error", "Only an unpaid order that has not started yet can be cancelled.");
    }

    redirectTo($orderPage);
}

if ($action == "message") {
    $body = cleanInput($_POST["body"] ?? "");

    if (isEmptyValue($body) || isTooLong($body, 1000)) {
        setFlash("error", "A message must be between 1 and 1000 characters.");
        redirectTo($orderPage);
    }

    if ($orderModel->addMessage($orderId, $userId, $body)) {
        setFlash("success", "Message sent.");
    } else {
        setFlash("error", "Could not send the message.");
    }

    redirectTo($orderPage);
}
