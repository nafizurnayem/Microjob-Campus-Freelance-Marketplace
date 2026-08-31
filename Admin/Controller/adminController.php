<?php
// One entry point for every admin action: gig_status, gig_delete, user_status, category_add, category_delete, withdrawal_paid.
require "../../Model/init.php";

requirePost();
requireRole("admin");

$action = cleanInput($_POST["action"] ?? "");

$allowedActions = array(
    "gig_status",
    "gig_delete",
    "user_status",
    "category_add",
    "category_delete",
    "withdrawal_paid"
);

if (!isInList($action, $allowedActions)) {
    setFlash("error", "Unknown action.");
    redirectTo(BASE_URL . "/Admin/View/adminDashboard.php");
}

if ($action == "gig_status") {
    $gigId = cleanInput($_POST["gig_id"] ?? "");
    $status = cleanInput($_POST["status"] ?? "");

    if (!isDigitsOnly($gigId) || !isInList($status, array("approved", "rejected", "pending"))) {
        setFlash("error", "Invalid gig or status.");
        redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
    }

    if ($gigModel->setStatus($gigId, $status)) {
        setFlash("success", "Gig marked as " . $status . ".");
    } else {
        setFlash("error", "Could not update that gig.");
    }

    redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
}

if ($action == "gig_delete") {
    $gigId = cleanInput($_POST["gig_id"] ?? "");

    if (!isDigitsOnly($gigId)) {
        setFlash("error", "Invalid gig.");
        redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
    }

    if ($gigModel->deleteAny($gigId)) {
        setFlash("success", "Gig removed.");
    } else {
        setFlash("error", "Could not remove that gig.");
    }

    redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
}

if ($action == "user_status") {
    $userId = cleanInput($_POST["user_id"] ?? "");
    $status = cleanInput($_POST["status"] ?? "");

    if (!isDigitsOnly($userId) || !isInList($status, array("active", "suspended"))) {
        setFlash("error", "Invalid user or status.");
        redirectTo(BASE_URL . "/Admin/View/manageUsers.php");
    }

    if ($userId == currentUserId()) {
        setFlash("error", "You cannot change the status of your own account.");
        redirectTo(BASE_URL . "/Admin/View/manageUsers.php");
    }

    if ($userModel->setStatus($userId, $status)) {
        setFlash("success", "Account marked as " . $status . ".");
    } else {
        setFlash("error", "Could not update that account.");
    }

    redirectTo(BASE_URL . "/Admin/View/manageUsers.php");
}

if ($action == "category_add") {
    $name = cleanInput($_POST["name"] ?? "");

    if (isTooShort($name, 3) || isTooLong($name, 80)) {
        setFlash("error", "Category name must be between 3 and 80 characters.");
        redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
    }

    if ($gigModel->addCategory($name)) {
        setFlash("success", "Category added.");
    } else {
        setFlash("error", "That category already exists.");
    }

    redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
}

if ($action == "category_delete") {
    $categoryId = cleanInput($_POST["category_id"] ?? "");

    if (!isDigitsOnly($categoryId)) {
        setFlash("error", "Invalid category.");
        redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
    }

    if ($gigModel->deleteCategory($categoryId)) {
        setFlash("success", "Category deleted.");
    } else {
        setFlash("error", "This category still has gigs, so it cannot be deleted.");
    }

    redirectTo(BASE_URL . "/Admin/View/manageGigs.php");
}

if ($action == "withdrawal_paid") {
    $withdrawalId = cleanInput($_POST["withdrawal_id"] ?? "");

    if (!isDigitsOnly($withdrawalId)) {
        setFlash("error", "Invalid withdrawal request.");
        redirectTo(BASE_URL . "/Admin/View/adminDashboard.php");
    }

    if ($adminModel->markWithdrawalPaid($withdrawalId)) {
        setFlash("success", "Withdrawal marked as paid.");
    } else {
        setFlash("error", "That request was already handled.");
    }

    redirectTo(BASE_URL . "/Admin/View/adminDashboard.php");
}
