<?php
require "../../Model/init.php";

requirePost();

$fullName = cleanInput($_POST["full_name"] ?? "");
$email = strtolower(cleanInput($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$confirmPassword = $_POST["confirm_password"] ?? "";
$role = cleanInput($_POST["role"] ?? "");
$university = cleanInput($_POST["university"] ?? "");
$department = cleanInput($_POST["department"] ?? "");
$phone = cleanInput($_POST["phone"] ?? "");
$skills = cleanInput($_POST["skills"] ?? "");
$bio = cleanInput($_POST["bio"] ?? "");

keepOldInput(array(
    "full_name"  => $fullName,
    "email"      => $email,
    "role"       => $role,
    "university" => $university,
    "department" => $department,
    "phone"      => $phone,
    "skills"     => $skills,
    "bio"        => $bio
));

$errors = array();

if (!isInList($role, array("student", "client"))) {
    array_push($errors, "Choose whether you want to sell or hire.");
}

if (isEmptyValue($fullName)) {
    array_push($errors, "Full name is required.");
} else if (isTooShort($fullName, 3) || isTooLong($fullName, 100)) {
    array_push($errors, "Full name must be between 3 and 100 characters.");
}

if (isEmptyValue($email)) {
    array_push($errors, "Email is required.");
} else if (!isValidEmail($email)) {
    array_push($errors, "Enter a valid email address.");
} else if (isTooLong($email, 150)) {
    array_push($errors, "Email is too long.");
}

if (isTooShort($password, MIN_PASSWORD_LENGTH)) {
    array_push($errors, "Password must be at least " . MIN_PASSWORD_LENGTH . " characters.");
}

if ($password != $confirmPassword) {
    array_push($errors, "Passwords do not match.");
}

if (!isValidPhone($phone)) {
    array_push($errors, "Enter an 11 digit mobile number starting with 01.");
}

if (isEmptyValue($university)) {
    array_push($errors, "University name is required.");
}

if (isEmptyValue($department)) {
    array_push($errors, "Department is required.");
}

if (isTooLong($skills, 255)) {
    array_push($errors, "Skills list is too long.");
}

if (isTooLong($bio, 1000)) {
    array_push($errors, "Bio must be 1000 characters or less.");
}

if (count($errors) == 0 && $userModel->emailExists($email)) {
    array_push($errors, "That email is already registered. Try logging in instead.");
}

if (count($errors) > 0) {
    setFlash("error", implode(" ", $errors));
    redirectTo(BASE_URL . "/Student/View/register.php");
}

$user = new User();
$user->setBasic($fullName, $email, $password, $role);
$user->setCampus($university, $department, $phone);

if ($role == "client") {
    $user->setProfile($bio, "");
} else {
    $user->setProfile($bio, $skills);
}

$newId = $userModel->create($user);

if ($newId == 0) {
    setFlash("error", "Could not create the account. Please try again.");
    redirectTo(BASE_URL . "/Student/View/register.php");
}

clearOldInput();
setFlash("success", "Account created. You can log in now.");
redirectTo(BASE_URL . "/Student/View/login.php");
