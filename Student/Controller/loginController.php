<?php
require "../../Model/init.php";

requirePost();

$email = strtolower(cleanInput($_POST["email"] ?? ""));
$password = $_POST["password"] ?? "";
$remember = $_POST["remember"] ?? "";

keepOldInput(array("email" => $email));

if (isEmptyValue($email) || isEmptyValue($password)) {
    setFlash("error", "Email and password are both required.");
    redirectTo(BASE_URL . "/Student/View/login.php");
}

if (!isValidEmail($email)) {
    setFlash("error", "Enter a valid email address.");
    redirectTo(BASE_URL . "/Student/View/login.php");
}

$attempt = $userModel->attemptLogin($email, $password);

if ($attempt["ok"] != true) {
    if ($attempt["reason"] == "suspended") {
        setFlash("error", "This account has been suspended. Contact the administrator.");
    } else {
        setFlash("error", "Invalid email or password.");
    }
    redirectTo(BASE_URL . "/Student/View/login.php");
}

$user = $attempt["user"];

// "Remember me" keeps the email in a cookie for 30 days.
if ($remember == "yes") {
    setcookie("remember_email", $email, strtotime("+30 days"), "/");
} else {
    setcookie("remember_email", "", time() - 3600, "/");
}

loginUser($user);
clearOldInput();

setFlash("success", "Welcome back, " . $user["full_name"] . ".");
redirectTo(dashboardFor($user["role"]));
