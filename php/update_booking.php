<?php

include "auth.php";
include "db.php";

require_staff();

$back = panel_page("admin_services.php", "staff_services.php");

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect($back);
}

csrf_check();

$booking_id = isset($_POST['booking_id']) ? intval($_POST['booking_id']) : 0;
$status = isset($_POST['status']) ? $_POST['status'] : "";

$allowed = array(
    "Requested",
    "Contacted",
    "Scheduled",
    "In Progress",
    "Completed",
    "Cancelled"
);

if($booking_id < 1 || !in_array($status, $allowed, true)){
    safe_redirect($back . "?error=" . rawurlencode("That booking update was not valid."));
}

$quoted = isset($_POST['quoted_price']) ? trim($_POST['quoted_price']) : "";

$hasQuote = ($quoted !== "" && is_numeric($quoted) && (float)$quoted >= 0);

if($hasQuote){
    $quotedValue = number_format((float)$quoted, 2, '.', '');

    $stmt = mysqli_prepare($conn,
        "UPDATE service_bookings
         SET status = ?, quoted_price = ?
         WHERE id = ?");

    mysqli_stmt_bind_param($stmt, "sdi", $status, $quotedValue, $booking_id);
} else {
    $stmt = mysqli_prepare($conn,
        "UPDATE service_bookings SET status = ? WHERE id = ?");

    mysqli_stmt_bind_param($stmt, "si", $status, $booking_id);
}

// Never redirect as if it worked when the row was not actually changed,
// otherwise staff plan a visit for a customer who was never notified.
$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    safe_redirect($back . "?error=" . rawurlencode("The booking could not be saved. Please try again."));
}

safe_redirect($back . "?ok=" . rawurlencode("Booking updated."));

?>
