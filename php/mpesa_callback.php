<?php

// Safaricom calls this URL after an STK push with the transaction result.
// It runs without a customer session, and must always answer HTTP 200
// (after processing) so Safaricom stops retrying the callback.
//
// Some free hosts block inbound webhooks entirely; the checkout's status
// poll (php/payment_status.php) is the fallback that keeps the flow working
// in that case.

include "db.php";
include "mpesa.php";

$raw = file_get_contents('php://input');

$data = mpesa_callback_data($raw);

if($data === false){
    error_log("Opunga M-PESA: received an unparseable callback: " . substr($raw, 0, 300));
    http_response_code(400);
    exit();
}

// Nothing to do for a rejected or expired prompt: the status poll abandons
// the order itself once the query confirms the failure.
if($data['result_code'] !== '0'){
    http_response_code(200);
    exit();
}

if($data['checkout_id'] === '' || $data['receipt'] === ''){
    error_log("Opunga M-PESA: success callback missing checkout or receipt");
    http_response_code(200);
    exit();
}

// Match the callback to the order whose payment row holds this
// CheckoutRequestID. mpesa_apply_payment() also verifies the amount so a
// stray or forged callback cannot mark the wrong order paid.
$stmt = mysqli_prepare($conn,
    "SELECT p.order_id
     FROM payments p
     WHERE p.mpesa_code = ?
     LIMIT 1");

mysqli_stmt_bind_param($stmt, "s", $data['checkout_id']);
mysqli_stmt_execute($stmt);

$row = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$row){
    error_log("Opunga M-PESA: callback for unknown checkout " . $data['checkout_id']);
    http_response_code(200);
    exit();
}

mpesa_apply_payment($conn, (int)$row['order_id'], $data['receipt'], $data['amount']);

http_response_code(200);
echo json_encode(array('ResultCode' => 0, 'ResultDesc' => 'Success'));
exit();
?>