<?php
// Session state and the page guards used by every view and controller.
function isLoggedIn()
{
    $loggedIn = $_SESSION["isLoggedIn"] ?? false;
    if ($loggedIn) {
        return true;
    }
    return false;
}

function currentUserId()
{
    return $_SESSION["user_id"] ?? 0;
}

function currentUserName()
{
    return $_SESSION["full_name"] ?? "";
}

function currentRole()
{
    return $_SESSION["role"] ?? "";
}

function isStudent()
{
    return currentRole() == "student";
}

function isClient()
{
    return currentRole() == "client";
}

function isAdmin()
{
    return currentRole() == "admin";
}

function dashboardFor($role)
{
    switch ($role) {
        case "student":
            return BASE_URL . "/Student/View/studentDashboard.php";
        case "client":
            return BASE_URL . "/Client/View/clientDashboard.php";
        case "admin":
            return BASE_URL . "/Admin/View/adminDashboard.php";
        default:
            return BASE_URL . "/Student/View/login.php";
    }
}

function redirectTo($url)
{
    Header("Location: " . $url);
    exit;
}

function requireLogin()
{
    if (!isLoggedIn()) {
        setFlash("error", "Please log in first.");
        redirectTo(BASE_URL . "/Student/View/login.php");
    }
}

function requireRole($role)
{
    requireLogin();

    if (currentRole() != $role) {
        setFlash("error", "You are not allowed to open that page.");
        redirectTo(dashboardFor(currentRole()));
    }
}

function requirePost()
{
    if ($_SERVER["REQUEST_METHOD"] != "POST") {
        redirectTo(BASE_URL . "/Student/View/home.php");
    }
}

function requireOwner($ownerId)
{
    if (currentUserId() != $ownerId && !isAdmin()) {
        setFlash("error", "You are not allowed to open that item.");
        redirectTo(dashboardFor(currentRole()));
    }
}

function loginUser($user)
{
    $_SESSION["isLoggedIn"] = true;
    $_SESSION["user_id"]    = $user["user_id"];
    $_SESSION["full_name"]  = $user["full_name"];
    $_SESSION["email"]      = $user["email"];
    $_SESSION["role"]       = $user["role"];
}

function logoutUser()
{
    session_unset();
    session_destroy();
}
