<?php
require "../../Model/init.php";

requirePost();
requireLogin();

$userId = currentUserId();

$currentPassword = $_POST["current_password"] ?? "";
$newPassword = $_POST["new_password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";

$page = BASE_URL . "/Student/View/changePassword.php";

$user = $userModel->findById($userId);

if (!$user) {
    logoutUser();
    redirectTo(BASE_URL . "/Student/View/login.php");
}

if ($currentPassword == "" || $currentPassword != $user["password"]) {
    setFlash("error", "The current password is not correct.");
    redirectTo($page);
}

if (isTooShort($newPassword, MIN_PASSWORD_LENGTH)) {
    setFlash("error", "The new password must be at least " . MIN_PASSWORD_LENGTH . " characters.");
    redirectTo($page);
}

if ($newPassword != $confirmPassword) {
    setFlash("error", "The two new passwords do not match.");
    redirectTo($page);
}

if ($newPassword == $currentPassword) {
    setFlash("error", "The new password must be different from the current one.");
    redirectTo($page);
}

if ($userModel->changePassword($userId, $newPassword)) {
    setFlash("success", "Password updated.");
    redirectTo(BASE_URL . "/Student/View/profile.php");
}

setFlash("error", "Could not update the password.");
redirectTo($page);
