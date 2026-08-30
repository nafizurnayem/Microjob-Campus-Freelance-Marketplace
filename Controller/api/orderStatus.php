<?php
// JSON endpoint for the live order status (fetch).
require "../../../Model/init.php";

Header("Content-Type: application/json");

if (!isLoggedIn()) {
    echo json_encode(array(
        "success" => false,
        "message" => "Please log in first.",
        "data"    => array()
    ));
    exit;
}

$orderId = cleanInput($_GET["order_id"] ?? "");

if (!isDigitsOnly($orderId)) {
    echo json_encode(array(
        "success" => false,
        "message" => "Invalid order.",
        "data"    => array()
    ));
    exit;
}

$order = $orderModel->findById($orderId);

if (!$order) {
    echo json_encode(array(
        "success" => false,
        "message" => "Order not found.",
        "data"    => array()
    ));
    exit;
}

$isOwner = false;
if ($order["client_id"] == currentUserId() || $order["student_id"] == currentUserId() || isAdmin()) {
    $isOwner = true;
}

if (!$isOwner) {
    echo json_encode(array(
        "success" => false,
        "message" => "You are not allowed to see this order.",
        "data"    => array()
    ));
    exit;
}

$labels = array(
    "placed"    => "Placed, waiting for the student to accept",
    "accepted"  => "Accepted, work in progress",
    "delivered" => "Delivered, waiting for your confirmation",
    "completed" => "Completed",
    "cancelled" => "Cancelled"
);

$label = $labels[$order["status"]] ?? $order["status"];

echo json_encode(array(
    "success" => true,
    "message" => "",
    "data"    => array(
        "order_id"     => $order["order_id"],
        "status"       => $order["status"],
        "status_label" => $label,
        "paid"         => $order["payment_id"] ? true : false,
        "checked_at"   => date("d M Y, h:i A")
    )
));
