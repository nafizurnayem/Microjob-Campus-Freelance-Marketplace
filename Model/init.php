<?php
// Bootstrap for every page: session, constants, database connection, model objects.
session_start();
date_default_timezone_set("Asia/Dhaka");

define("SITE_NAME", "MicroJob for Students");
define("CURRENCY", "BDT");
define("BASE_URL", "/Webtech-Summer-2025-26/final/Project");

define("DB_HOST", "localhost");
define("DB_USER", "root");
define("DB_PASS", "");
define("DB_NAME", "microjob_db");

define("MIN_PASSWORD_LENGTH", 6);
define("MAX_GIG_PRICE", 100000);
define("MIN_GIG_PRICE", 50);
define("MAX_DELIVERY_DAYS", 30);

// Failing queries return false instead of throwing, so raw SQL never reaches the page.
mysqli_report(MYSQLI_REPORT_OFF);

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_errno) {
    echo "<h2>Database is not available.</h2>";
    echo "<p>Start MySQL in XAMPP and import <code>database/microjob.sql</code> in phpMyAdmin.</p>";
    exit;
}
$conn->set_charset("utf8mb4");

require "validator.php";
require "auth.php";

require "userModel.php";
require "gigModel.php";
require "orderModel.php";
require "paymentModel.php";
require "reviewModel.php";
require "adminModel.php";

$userModel = new UserModel();
$userModel->setConnection($conn);

$gigModel = new GigModel();
$gigModel->setConnection($conn);

$orderModel = new OrderModel();
$orderModel->setConnection($conn);

$paymentModel = new PaymentModel();
$paymentModel->setConnection($conn);

$reviewModel = new ReviewModel();
$reviewModel->setConnection($conn);

$adminModel = new AdminModel();
$adminModel->setConnection($conn);
