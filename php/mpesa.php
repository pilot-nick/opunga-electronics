<?php

// Daraja (Safaricom M-PESA) STK push client used by the checkout flow.
//
// The consumer key and secret come from config.php or config.local.php
// (OPUNGA_MPESA_* defines). In sandbox mode the fixed test shortcode (174379)
// and the published sandbox passkey are filled in automatically, so a test
// only needs the key and secret. In live mode provide the till and the
// passkey Safaricom issued for it.
//
// This file only defines functions; php/.htaccess denies direct browser
// access, and a direct request would print nothing anyway.

if(!defined('OPUNGA_MPESA_ENV')){ define('OPUNGA_MPESA_ENV', 'sandbox'); }
if(!defined('OPUNGA_MPESA_CONSUMER_KEY')){ define('OPUNGA_MPESA_CONSUMER_KEY', ''); }
if(!defined('OPUNGA_MPESA_CONSUMER_SECRET')){ define('OPUNGA_MPESA_CONSUMER_SECRET', ''); }
if(!defined('OPUNGA_MPESA_PASSKEY')){ define('OPUNGA_MPESA_PASSKEY', ''); }
if(!defined('OPUNGA_MPESA_TILL')){ define('OPUNGA_MPESA_TILL', ''); }
if(!defined('OPUNGA_MPESA_CALLBACK_URL')){ define('OPUNGA_MPESA_CALLBACK_URL', ''); }

function mpesa_cfg($name, $fallback = "")
{
    return defined($name) && constant($name) !== '' ? constant($name) : $fallback;
}

// Which Daraja environment to talk to: 'sandbox' or 'live'
function mpesa_env()
{
    return mpesa_cfg('OPUNGA_MPESA_ENV', 'sandbox') === 'live' ? 'live' : 'sandbox';
}

// The shortcode to charge. Falls back to the sandbox test shortcode when no
// till is configured, or to the shop's till in live mode.
function mpesa_till()
{
    $till = mpesa_cfg('OPUNGA_MPESA_TILL');

    if($till !== ''){
        return $till;
    }

    return mpesa_env() === 'sandbox' ? '174379' : '3461337';
}

// The passkey that signs each request. The sandbox passkey is a fixed public
// test value, so it is supplied automatically to make testing one step.
function mpesa_passkey()
{
    $passkey = mpesa_cfg('OPUNGA_MPESA_PASSKEY');

    if($passkey !== ''){
        return $passkey;
    }

    return mpesa_env() === 'sandbox'
        ? 'bfb279f9aa9bdbcf158e97dd71a467cd2e0c893059b10f78e6b72ada1ed2c919'
        : '';
}

// True once the key and secret are provided and a passkey is available
// (explicitly, or from the sandbox default), meaning checkout can trigger a
// real STK push.
function mpesa_enabled()
{
    return mpesa_cfg('OPUNGA_MPESA_CONSUMER_KEY') !== ''
        && mpesa_cfg('OPUNGA_MPESA_CONSUMER_SECRET') !== ''
        && mpesa_passkey() !== '';
}

function mpesa_base_url()
{
    return mpesa_env() === 'live'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke';
}

// Daraja timestamps are East African Time in YYYYMMDDHHmmss.
function mpesa_stk_timestamp()
{
    $dt = new DateTime('now', new DateTimeZone('Africa/Nairobi'));

    return $dt->format('YmdHis');
}

// The M-PESA Password is base64(shortcode + passkey + timestamp).
function mpesa_password($till, $passkey)
{
    return base64_encode($till . $passkey . mpesa_stk_timestamp());
}

// Normalise any phone format ("0712345678", "254712345678", "712345678")
// into the 254 format Safaricom expects, or "" when it is not a valid
// M-PESA number.
function mpesa_normalize_phone($phone)
{
    $p = preg_replace('/\D+/', '', (string)$phone);

    if(strlen($p) === 9){
        $p = '254' . $p;
    } else if(strlen($p) === 10 && $p[0] === '0'){
        $p = '254' . substr($p, 1);
    }

    if(!preg_match('/^254(7|1)\d{8}$/', $p)){
        return "";
    }

    return $p;
}

// If no callback URL is configured, reuse the host the customer is on so
// the sandbox (or a temporary tunnel) also receives callbacks.
function mpesa_default_callback()
{
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';

    $folder = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '/php';

    return ($https ? 'https://' : 'http://') . $host . $folder . '/mpesa_callback.php';
}

// Small cURL wrapper. Returns array('status' => int, 'body' => string) or
// false when the transport itself fails.
function mpesa_http($url, $method, $token, $payload = null)
{
    $headers = array('Content-Type: application/json');

    if($token !== ""){
        $headers[] = 'Authorization: Bearer ' . $token;
    }

    $ch = curl_init($url);

    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10
    ));

    if($method === 'POST'){
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    }

    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if($body === false){
        error_log("Opunga M-PESA: $method $url transport error: $error");

        return false;
    }

    return array('status' => $status, 'body' => $body);
}

// Fetches a fresh OAuth access token from Daraja, or false on failure.
function mpesa_token()
{
    $key = mpesa_cfg('OPUNGA_MPESA_CONSUMER_KEY');
    $secret = mpesa_cfg('OPUNGA_MPESA_CONSUMER_SECRET');

    $url = mpesa_base_url() . '/oauth/v1/generate?grant_type=client_credentials';

    $ch = curl_init($url);

    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => array('Authorization: Basic ' . base64_encode($key . ':' . $secret)),
        CURLOPT_TIMEOUT => 20,
        CURLOPT_CONNECTTIMEOUT => 10
    ));

    $body = curl_exec($ch);
    $status = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);

    curl_close($ch);

    if($body === false || $status !== 200){
        error_log("Opunga M-PESA: token request failed (http $status) $error");

        return false;
    }

    $json = json_decode($body, true);

    if(!is_array($json) || empty($json['access_token'])){
        error_log("Opunga M-PESA: no access_token in token response: $body");

        return false;
    }

    return $json['access_token'];
}

// Sends an STK push prompt to the customer's phone. Returns
// array('ok' => bool, 'checkout' => string, 'error' => string).
function mpesa_stk_push($phone254, $amount, $accountRef, $description)
{
    $till = mpesa_till();

    $token = mpesa_token();

    if($token === false){
        return array('ok' => false, 'checkout' => '', 'error' => 'Could not reach M-PESA right now.');
    }

    $payload = array(
        'BusinessShortCode' => $till,
        'Password' => mpesa_password($till, mpesa_passkey()),
        'Timestamp' => mpesa_stk_timestamp(),
        'TransactionType' => 'CustomerBuyGoodsOnline',
        'Amount' => (int)round($amount),
        'PartyA' => $phone254,
        'PartyB' => $till,
        'PhoneNumber' => $phone254,
        'CallBackURL' => mpesa_cfg('OPUNGA_MPESA_CALLBACK_URL', mpesa_default_callback()),
        'AccountReference' => substr((string)$accountRef, 0, 12),
        'TransactionDesc' => substr((string)$description, 0, 13)
    );

    $res = mpesa_http(mpesa_base_url() . '/mpesa/stkpush/v1/processrequest', 'POST', $token, $payload);

    if($res === false){
        return array('ok' => false, 'checkout' => '', 'error' => 'Network problem contacting M-PESA. Please try again.');
    }

    $json = json_decode($res['body'], true);

    if($res['status'] !== 200 || !is_array($json)){
        error_log("Opunga M-PESA: STK push HTTP {$res['status']}: {$res['body']}");

        return array('ok' => false, 'checkout' => '', 'error' => 'M-PESA rejected the payment request.');
    }

    if(!empty($json['errorCode'])){
        error_log("Opunga M-PESA: STK push {$json['errorCode']} {$json['errorMessage']}");

        $message = isset($json['errorMessage']) ? $json['errorMessage'] : 'M-PESA returned an error.';

        return array('ok' => false, 'checkout' => '', 'error' => $message);
    }

    $checkout = isset($json['CheckoutRequestID']) ? (string)$json['CheckoutRequestID'] : '';
    $responseCode = isset($json['ResponseCode']) ? (string)$json['ResponseCode'] : '1';

    if($responseCode !== '0' || $checkout === ''){
        error_log("Opunga M-PESA: STK push response code $responseCode body: {$res['body']}");

        return array('ok' => false, 'checkout' => '', 'error' => 'The M-PESA prompt could not be sent. Please try again.');
    }

    return array(
        'ok' => true,
        'checkout' => $checkout,
        'error' => '',
        'description' => isset($json['ResponseDescription']) ? $json['ResponseDescription'] : ''
    );
}

// Asks Daraja for the final state of a previous STK push. Returns
// array('ok' => bool, 'result_code' => string, 'error' => string).
function mpesa_stk_query($checkoutId)
{
    $till = mpesa_till();

    $token = mpesa_token();

    if($token === false){
        return array('ok' => false, 'result_code' => '', 'error' => 'Could not reach M-PESA right now.');
    }

    $payload = array(
        'BusinessShortCode' => $till,
        'Password' => mpesa_password($till, mpesa_passkey()),
        'Timestamp' => mpesa_stk_timestamp(),
        'CheckoutRequestID' => $checkoutId
    );

    $res = mpesa_http(mpesa_base_url() . '/mpesa/stkpushquery/v1/query', 'POST', $token, $payload);

    if($res === false){
        return array('ok' => false, 'result_code' => '', 'error' => 'Network problem contacting M-PESA.');
    }

    $json = json_decode($res['body'], true);

    if($res['status'] !== 200 || !is_array($json)){
        error_log("Opunga M-PESA: STK query HTTP {$res['status']}: {$res['body']}");

        return array('ok' => false, 'result_code' => '', 'error' => 'Could not check the payment status.');
    }

    $resultCode = isset($json['ResultCode']) ? (string)$json['ResultCode'] : '';

    $description = isset($json['ResultDesc']) ? (string)$json['ResultDesc'] : 'Unknown status.';

    return array(
        'ok' => ($resultCode === '0'),
        'result_code' => $resultCode,
        'error' => $description
    );
}

// Parses a Safaricom STK callback body into a usable array, or false when
// it is not a valid callback.
function mpesa_callback_data($raw)
{
    $json = json_decode($raw, true);

    if(!is_array($json) || !isset($json['Body']['stkCallback'])){
        return false;
    }

    $cb = $json['Body']['stkCallback'];

    $data = array(
        'checkout_id' => isset($cb['CheckoutRequestID']) ? (string)$cb['CheckoutRequestID'] : '',
        'result_code' => isset($cb['ResultCode']) ? (string)$cb['ResultCode'] : '',
        'result_desc' => isset($cb['ResultDesc']) ? (string)$cb['ResultDesc'] : '',
        'amount' => 0.0,
        'receipt' => '',
        'phone' => '',
        'date' => ''
    );

    if(isset($cb['CallbackMetadata']['Item']) && is_array($cb['CallbackMetadata']['Item'])){
        foreach($cb['CallbackMetadata']['Item'] as $item){
            if(!isset($item['Name'])){ continue; }

            $value = isset($item['Value']) ? $item['Value'] : '';

            if($item['Name'] === 'Amount'){
                $data['amount'] = (float)$value;
            } else if($item['Name'] === 'MpesaReceiptNumber'){
                $data['receipt'] = (string)$value;
            } else if($item['Name'] === 'PhoneNumber'){
                $data['phone'] = (string)$value;
            } else if($item['Name'] === 'TransactionDate'){
                $data['date'] = (string)$value;
            }
        }
    }

    return $data;
}

// True when mpesa_code holds a Daraja CheckoutRequestID (for example
// "ws_CO_16112026103038157") rather than a typed SMS receipt code.
function mpesa_has_live_stk($mpesaCode)
{
    return is_string($mpesaCode)
        && preg_match('/^[A-Za-z0-9_]{8,40}$/', $mpesaCode)
        && strpos($mpesaCode, '_') !== false;
}

// Records a confirmed payment for an order and clears the covered items
// from the customer's cart. Safe to call more than once: a second callback
// with the same receipt is a no-op.
function mpesa_apply_payment($conn, $orderId, $receipt, $amount)
{
    $stmt = mysqli_prepare($conn,
        "SELECT p.id, p.amount, p.mpesa_code, o.status, o.user_id
         FROM payments p
         JOIN orders o ON o.id = p.order_id
         WHERE p.order_id = ?
         LIMIT 1");

    mysqli_stmt_bind_param($stmt, "i", $orderId);
    mysqli_stmt_execute($stmt);

    $row = mysqli_stmt_get_result($stmt)->fetch_assoc();

    mysqli_stmt_close($stmt);

    if(!$row){
        return false;
    }

    // Never mark an order paid for a different amount than its payment row
    $expected = (int)round((float)$row['amount']);
    $received = (int)round($amount);

    if($expected !== $received){
        error_log("Opunga M-PESA: order $orderId amount mismatch: expected $expected, got $received");

        return false;
    }

    // Already applied with this exact receipt
    if($row['mpesa_code'] === $receipt){
        return true;
    }

    if(empty($receipt)){
        return false;
    }

    mysqli_begin_transaction($conn);

    $updPay = mysqli_prepare($conn,
        "UPDATE payments SET mpesa_code = ?, payment_date = NOW() WHERE id = ?");

    mysqli_stmt_bind_param($updPay, "si", $receipt, $row['id']);
    mysqli_stmt_execute($updPay);
    mysqli_stmt_close($updPay);

    $updOrder = mysqli_prepare($conn, "UPDATE orders SET status = 'Paid' WHERE id = ?");

    mysqli_stmt_bind_param($updOrder, "i", $orderId);
    mysqli_stmt_execute($updOrder);
    mysqli_stmt_close($updOrder);

    // Remove exactly the items this order covers from the still-full cart,
    // so anything the customer added later is left alone.
    $delCart = mysqli_prepare($conn,
        "DELETE FROM cart
         WHERE user_id = ?
           AND product_id IN (SELECT product_id FROM order_items WHERE order_id = ?)");

    $userId = (int)$row['user_id'];

    mysqli_stmt_bind_param($delCart, "ii", $userId, $orderId);
    mysqli_stmt_execute($delCart);
    mysqli_stmt_close($delCart);

    mysqli_commit($conn);

    return true;
}

// Undo a never-paid order: give the stock back, delete its payment row and
// mark the order cancelled. Used when the STK push fails, the customer
// declines it, or the customer cancels from the waiting page.
function mpesa_abandon_order($conn, $orderId)
{
    mysqli_begin_transaction($conn);

    $restock = mysqli_prepare($conn,
        "UPDATE products p
         JOIN order_items oi ON oi.product_id = p.id
         SET p.stock = p.stock + oi.quantity
         WHERE oi.order_id = ?");

    mysqli_stmt_bind_param($restock, "i", $orderId);
    mysqli_stmt_execute($restock);
    mysqli_stmt_close($restock);

    $upd = mysqli_prepare($conn, "UPDATE orders SET status = 'Cancelled' WHERE id = ?");

    mysqli_stmt_bind_param($upd, "i", $orderId);
    mysqli_stmt_execute($upd);
    mysqli_stmt_close($upd);

    $del = mysqli_prepare($conn, "DELETE FROM payments WHERE order_id = ?");

    mysqli_stmt_bind_param($del, "i", $orderId);
    mysqli_stmt_execute($del);
    mysqli_stmt_close($del);

    mysqli_commit($conn);
}

?>