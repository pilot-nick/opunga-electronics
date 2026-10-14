<?php

include "auth.php";
include "db.php";
include "mpesa.php";

require_login();

require_customer();

$user_id = (int)$_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../checkout.php");
}

csrf_check();

// Checkout always asks for the customer's M-PESA phone number, then sends an
// STK push prompt. The site never asks the customer to type an M-PESA code.
$phone254 = mpesa_normalize_phone(post_trimmed('mpesa_phone'));

if($phone254 === ""){
    echo "<script>
    alert('Enter a valid M-PESA phone number, for example 0712345678.');
    window.location='../checkout.php';
    </script>";

    exit();
}

// Without the Daraja credentials no prompt can be sent, so say so instead of
// quietly creating an order that can never be paid.
if(!mpesa_enabled()){
    echo "<script>
    alert('Online M-PESA payment is not available right now. Please try again later.');
    window.location='../cart.php';
    </script>";

    exit();
}

// A customer with an unpaid order finishes paying it instead of stacking
// up more pending orders, which ties up stock more than once.
$existing = mysqli_prepare($conn,
    "SELECT id FROM orders
     WHERE user_id = ? AND status IN ('Pending', 'Processing')
     LIMIT 1");

mysqli_stmt_bind_param($existing, "i", $user_id);
mysqli_stmt_execute($existing);

$openOrder = mysqli_stmt_get_result($existing)->fetch_assoc();

mysqli_stmt_close($existing);

if($openOrder){
    safe_redirect("../payment.php?order=" . (int)$openOrder['id']);
}

// Everything from here on runs inside one transaction, so the cart read,
// the stock decrement, and the order insert either all succeed or all roll back.
mysqli_begin_transaction($conn);

// Lock the product rows for this cart so two customers checking out at the
// same time cannot both take the last unit
$select = mysqli_prepare($conn,
    "SELECT products.id, products.name, products.price, products.stock, cart.quantity
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.user_id = ?
     FOR UPDATE");

mysqli_stmt_bind_param($select, "i", $user_id);
mysqli_stmt_execute($select);

$items = array();
$total = 0.0;
$problem = "";

$rows = mysqli_stmt_get_result($select);

while($row = mysqli_fetch_assoc($rows)){

    $qty = (int)$row['quantity'];
    $stock = (int)$row['stock'];
    $price = (float)$row['price'];

    if($qty < 1){
        $problem = "Your cart contains an invalid quantity.";
        break;
    }

    if($stock < 1){
        $problem = "Sorry, " . $row['name'] . " is out of stock.";
        break;
    }

    if($qty > $stock){
        $problem = "Only " . $stock . " unit(s) of " . $row['name'] . " are available.";
        break;
    }

    $items[] = array(
        "id" => (int)$row['id'],
        "price" => $price,
        "quantity" => $qty
    );

    $total += $price * $qty;
}

mysqli_stmt_close($select);

if(count($items) == 0){
    $problem = "Your cart is empty.";
}

if($problem != ""){

    mysqli_rollback($conn);

    // json_encode escapes quotes and also <, > and /, so a product name
    // containing </script> cannot break out of this script block.
    echo "<script>
    alert(" . json_encode($problem) . ");
    window.location='../cart.php';
    </script>";

    exit();
}

$createOrder = mysqli_prepare($conn,
    "INSERT INTO orders(user_id, total, status)
     VALUES(?, ?, 'Pending')");

mysqli_stmt_bind_param($createOrder, "id", $user_id, $total);
mysqli_stmt_execute($createOrder);

$order_id = mysqli_insert_id($conn);

mysqli_stmt_close($createOrder);

if(mysqli_errno($conn) != 0 || $order_id < 1){
    mysqli_rollback($conn);

    echo "<script>
    alert('We could not start your order. Nothing was charged. Please try again.');
    window.location='../cart.php';
    </script>";

    exit();
}

foreach($items as $item){

    $id = $item['id'];
    $qty = $item['quantity'];
    $price = number_format($item['price'], 2, '.', '');

    $addItem = mysqli_prepare($conn,
        "INSERT INTO order_items(order_id, product_id, quantity, price)
         VALUES(?, ?, ?, ?)");

    mysqli_stmt_bind_param($addItem, "iiis", $order_id, $id, $qty, $price);
    mysqli_stmt_execute($addItem);

    mysqli_stmt_close($addItem);

    $reduce = mysqli_prepare($conn,
        "UPDATE products
         SET stock = stock - ?
         WHERE id = ? AND stock >= ?");

    mysqli_stmt_bind_param($reduce, "iii", $qty, $id, $qty);
    mysqli_stmt_execute($reduce);

    $reduced = mysqli_stmt_affected_rows($reduce);

    mysqli_stmt_close($reduce);

    if($reduced != 1){
        mysqli_rollback($conn);

        echo "<script>
        alert('Stock changed while you were checking out. Nothing was charged. Please review your cart.');
        window.location='../cart.php';
        </script>";

        exit();
    }

    if(mysqli_errno($conn) != 0){
        mysqli_rollback($conn);

        echo "<script>
        alert('We could not complete your order. Nothing was charged. Please try again.');
        window.location='../cart.php';
        </script>";

        exit();
    }
}

$amount = number_format($total, 2, '.', '');

// The column is updated with the Daraja CheckoutRequestID once the prompt
// has been accepted by Safaricom.
$payCode = "";

$pay = mysqli_prepare($conn,
    "INSERT INTO payments(order_id, amount, mpesa_code)
     VALUES(?, ?, ?)");

mysqli_stmt_bind_param($pay, "iss", $order_id, $amount, $payCode);
mysqli_stmt_execute($pay);

mysqli_stmt_close($pay);

if(mysqli_errno($conn) != 0){
    mysqli_rollback($conn);

    echo "<script>
    alert('We could not record your payment. Nothing was charged. Please try again.');
    window.location='../cart.php';
    </script>";

    exit();
}

mysqli_commit($conn);

// The cart is deliberately left alone here: it is only emptied once the
// payment is actually confirmed, so a failed or cancelled prompt loses no
// items and the customer can simply edit the cart and try again.
$push = mpesa_stk_push($phone254, $total, 'OPN' . $order_id, 'Opunga shop order');

if(!$push['ok']){

    mpesa_abandon_order($conn, $order_id);

    echo "<script>
    alert(" . json_encode($push['error'] . " You can review your cart and try again.") . ");
    window.location='../cart.php';
    </script>";

    exit();
}

$saveCode = mysqli_prepare($conn,
    "UPDATE payments SET mpesa_code = ? WHERE order_id = ?");

mysqli_stmt_bind_param($saveCode, "si", $push['checkout'], $order_id);
mysqli_stmt_execute($saveCode);

$saved = mysqli_stmt_affected_rows($saveCode);

mysqli_stmt_close($saveCode);

if($saved != 1){
    mpesa_abandon_order($conn, $order_id);

    echo "<script>
    alert('We could not keep track of your payment. Nothing was charged, and your cart is safe.');
    window.location='../cart.php';
    </script>";

    exit();
}

safe_redirect("../payment.php?order=" . $order_id);

?>