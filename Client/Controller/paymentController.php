<?php
// Simulated payment. No real gateway is contacted and no full account number is stored.
require "../../Model/init.php";

requirePost();
requireRole("client");

$orderId = cleanInput($_POST["order_id"] ?? "");
$method = cleanInput($_POST["method"] ?? "");
$accountNumber = str_replace(" ", "", cleanInput($_POST["account_no"] ?? ""));

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
    setFlash("error", "You are not allowed to pay for that order.");
    redirectTo(BASE_URL . "/Student/View/myOrders.php");
}

if ($order["status"] == "cancelled") {
    setFlash("error", "This order was cancelled.");
    redirectTo(BASE_URL . "/Student/View/orderDetails.php?order_id=" . $orderId);
}

if ($paymentModel->isOrderPaid($orderId)) {
    setFlash("error", "This order is already paid.");
    redirectTo(BASE_URL . "/Student/View/orderDetails.php?order_id=" . $orderId);
}

if (!isInList($method, array("bkash", "nagad", "bank", "card"))) {
    setFlash("error", "Choose a valid payment method.");
    redirectTo(BASE_URL . "/Client/View/checkout.php?order_id=" . $orderId);
}

$accountIsValid = false;

switch ($method) {
    case "bkash":
    case "nagad":
        $accountIsValid = isValidPhone($accountNumber);
        break;
    case "bank":
        $accountIsValid = isValidAccountNumber($accountNumber, 10, 20);
        break;
    case "card":
        $accountIsValid = isValidAccountNumber($accountNumber, 16, 16);
        break;
}

if (!$accountIsValid) {
    setFlash("error", "The account or card number is not in the expected format.");
    redirectTo(BASE_URL . "/Client/View/checkout.php?order_id=" . $orderId);
}

$amount = round($order["amount_bdt"], 2);
$lastFour = $paymentModel->lastFourDigits($accountNumber);
$txnId = $paymentModel->makeTransactionId($method, $orderId);

if (!$paymentModel->create($orderId, $method, $lastFour, $txnId, $amount)) {
    setFlash("error", "The payment could not be recorded. Please try again.");
    redirectTo(BASE_URL . "/Client/View/checkout.php?order_id=" . $orderId);
}

setFlash("success", "Payment successful. Transaction " . $txnId . ".");
redirectTo(BASE_URL . "/Client/View/paymentSuccess.php?order_id=" . $orderId);
