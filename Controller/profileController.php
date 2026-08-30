<?php
require "../../Model/init.php";

requirePost();
requireLogin();

$userId = currentUserId();
$action = cleanInput($_POST["action"] ?? "");

if (!isInList($action, array("update", "delete"))) {
    setFlash("error", "Unknown action.");
    redirectTo(BASE_URL . "/Student/View/profile.php");
}

$user = $userModel->findById($userId);

if (!$user) {
    logoutUser();
    redirectTo(BASE_URL . "/Student/View/login.php");
}

if ($action == "delete") {
    if ($user["role"] == "admin") {
        setFlash("error", "An administrator account cannot be deleted here.");
        redirectTo(BASE_URL . "/Student/View/profile.php");
    }

    $password = $_POST["password"] ?? "";

    if ($password == "" || $password != $user["password"]) {
        setFlash("error", "The password does not match. The account was not deleted.");
        redirectTo(BASE_URL . "/Student/View/profile.php");
    }

    if ($userModel->deleteAccount($userId)) {
        logoutUser();
        Header("Location: " . BASE_URL . "/Student/View/login.php");
        exit;
    }

    setFlash("error", "Could not delete the account.");
    redirectTo(BASE_URL . "/Student/View/profile.php");
}

$fullName = cleanInput($_POST["full_name"] ?? "");
$university = cleanInput($_POST["university"] ?? "");
$department = cleanInput($_POST["department"] ?? "");
$phone = cleanInput($_POST["phone"] ?? "");
$bio = cleanInput($_POST["bio"] ?? "");
$skills = cleanInput($_POST["skills"] ?? "");

if ($user["role"] != "student") {
    $skills = "";
}

keepOldInput(array(
    "full_name"  => $fullName,
    "university" => $university,
    "department" => $department,
    "phone"      => $phone,
    "bio"        => $bio,
    "skills"     => $skills
));

$errors = array();

if (isTooShort($fullName, 3) || isTooLong($fullName, 100)) {
    array_push($errors, "Full name must be between 3 and 100 characters.");
}

if (isEmptyValue($university) || isTooLong($university, 150)) {
    array_push($errors, "University name is required.");
}

if (isEmptyValue($department) || isTooLong($department, 100)) {
    array_push($errors, "Department is required.");
}

if (!isValidPhone($phone)) {
    array_push($errors, "Enter an 11 digit mobile number starting with 01.");
}

if (isTooLong($bio, 1000)) {
    array_push($errors, "Bio must be 1000 characters or less.");
}

if (isTooLong($skills, 255)) {
    array_push($errors, "Skills list is too long.");
}

if (count($errors) > 0) {
    setFlash("error", implode(" ", $errors));
    redirectTo(BASE_URL . "/Student/View/profile.php");
}

if ($userModel->updateProfile($userId, $fullName, $university, $department, $phone, $bio, $skills)) {
    $_SESSION["full_name"] = $fullName;
    clearOldInput();
    setFlash("success", "Profile updated.");
} else {
    setFlash("error", "Could not update the profile.");
}

redirectTo(BASE_URL . "/Student/View/profile.php");
