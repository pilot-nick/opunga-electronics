<?php

include "auth.php";
include "db.php";
include "mpesa.php";

require_login();

$user_id = (int)$_SESSION['user_id'];

$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

function pay_status_json($payload)
{
    echo json_encode($payload);
    exit();
}

if($order_id < 1){
    pay_status_json(array('ok' => false, 'status' => 'error', 'message' => 'Invalid order.'));
}

// A customer may only poll their own order
$stmt = mysqli_prepare($conn,
    "SELECT orders.id, orders.status, orders.total,
            payments.id AS payment_id, payments.mpesa_code
     FROM orders
     LEFT JOIN payments ON payments.order_id = orders.id
     WHERE orders.id = ? AND orders.user_id = ?
     LIMIT 1");

mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);

$row = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$row){
    pay_status_json(array('ok' => false, 'status' => 'error', 'message' => 'Order not found.'));
}

if($row['status'] === 'Paid'){
    pay_status_json(array('ok' => true, 'status' => 'paid'));
}

if($row['status'] !== 'Pending' && $row['status'] !== 'Processing'){
    pay_status_json(array('ok' => true, 'status' => 'cancelled', 'message' => 'This order was not paid for and has been cancelled.'));
}

// No payment row, no live STK prompt, or Daraja not configured yet: there
// is nothing to poll, the page falls back to the manual SMS code entry.
$code = $row['mpesa_code'];

if(!$row['payment_id'] || !mpesa_enabled() || !mpesa_has_live_stk($code)){
    pay_status_json(array('ok' => true, 'status' => 'manual'));
}

// Ask Daraja for the final state of the original prompt. The callback
// normally resolves the payment first, and the query here is the fallback
// for hosts that cannot receive Safaricom's inbound webhook.
$query = mpesa_stk_query($code);

if($query['ok']){

    // The query does not return the M-PESA receipt number, so the
    // CheckoutRequestID is kept until a callback replaces it with the real
    // code.
    mpesa_apply_payment($conn, $order_id, $code, (float)$row['total']);

    pay_status_json(array('ok' => true, 'status' => 'paid'));
}

// Give a definite answer once Daraja has finished with the request
if($query['result_code'] !== ''){

    mpesa_abandon_order($conn, $order_id);

    $message = 'The M-PESA payment was not completed and the order has been cancelled.';

    if($query['error'] !== '' && strpos(strtolower($query['error']), 'processed successfully') === false){
        $message .= ' ' . $query['error'];
    }

    pay_status_json(array('ok' => true, 'status' => 'cancelled', 'message' => $message));
}

// Temporary network problem: let the page poll again in a moment
pay_status_json(array('ok' => false, 'status' => 'error', 'message' => 'Could not check the payment status. Please wait.'));
?>