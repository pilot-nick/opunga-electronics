<?php

include "auth.php";
include "db.php";

require_staff();

$back = panel_page("admin_orders.php", "staff_orders.php");

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect($back);
}

csrf_check();

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : "";

$allowed = array(
    "Pending",
    "Processing",
    "Shipped",
    "Delivered",
    "Cancelled",
    "Paid"
);

if($order_id < 1 || !in_array($status, $allowed, true)){
    safe_redirect($back . "?error=" . rawurlencode("That order update was not valid."));
}

$stmt = mysqli_prepare($conn, "UPDATE orders SET status = ? WHERE id = ?");

mysqli_stmt_bind_param($stmt, "si", $status, $order_id);

// A failed write must never look like a success, otherwise staff go and
// plan a delivery for an order whose status never actually changed.
$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    safe_redirect($back . "?error=" . rawurlencode("The order status could not be saved. Please try again."));
}

safe_redirect($back . "?ok=" . rawurlencode("Order status saved."));

?>
