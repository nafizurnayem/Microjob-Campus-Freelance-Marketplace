<?php
require "../../Model/init.php";

requirePost();
requireRole("student");

$studentId = currentUserId();

$amount = cleanInput($_POST["amount_bdt"] ?? "");
$method = cleanInput($_POST["method"] ?? "");
$accountNumber = str_replace(" ", "", cleanInput($_POST["account_no"] ?? ""));

$earningsPage = BASE_URL . "/Student/View/earnings.php";

if (!isInList($method, array("bkash", "nagad", "bank"))) {
    setFlash("error", "Choose a valid withdrawal method.");
    redirectTo($earningsPage);
}

$accountIsValid = false;
if ($method == "bank") {
    $accountIsValid = isValidAccountNumber($accountNumber, 10, 20);
} else {
    $accountIsValid = isValidPhone($accountNumber);
}

if (!$accountIsValid) {
    setFlash("error", "The account number is not in the expected format.");
    redirectTo($earningsPage);
}

$earnings = $orderModel->earningsOf($studentId);
$withdrawn = $adminModel->withdrawnTotal($studentId);
$available = round($earnings["earned"] - $withdrawn, 2);

if ($available <= 0) {
    setFlash("error", "You have nothing available to withdraw yet.");
    redirectTo($earningsPage);
}

if (!isValidPrice($amount, 1, $available)) {
    setFlash("error", "Enter an amount between 1 and " . formatMoney($available) . ".");
    redirectTo($earningsPage);
}

$amountValue = round($amount, 2);

if ($adminModel->createWithdrawal($studentId, $amountValue, $method, $accountNumber)) {
    setFlash("success", "Withdrawal of " . formatMoney($amountValue) . " requested. The admin will process it.");
} else {
    setFlash("error", "Could not save the withdrawal request.");
}

redirectTo($earningsPage);
