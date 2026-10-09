<?php

include "auth.php";
include "db.php";

require_login();

require_customer();

$user_id = (int)$_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../checkout.php");
}

csrf_check();

$mpesa = strtoupper(post_trimmed('mpesa_code'));

if(!preg_match('/^[A-Z0-9]{6,12}$/', $mpesa)){
    echo "<script>
    alert('Enter the transaction code exactly as it appears in your M-PESA SMS.');
    window.location='../checkout.php';
    </script>";

    exit();
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

$pay = mysqli_prepare($conn,
    "INSERT INTO payments(order_id, amount, mpesa_code)
     VALUES(?, ?, ?)");

mysqli_stmt_bind_param($pay, "iss", $order_id, $amount, $mpesa);
mysqli_stmt_execute($pay);

mysqli_stmt_close($pay);

$clear = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ?");

mysqli_stmt_bind_param($clear, "i", $user_id);
mysqli_stmt_execute($clear);

mysqli_stmt_close($clear);

if(mysqli_errno($conn) != 0){
    mysqli_rollback($conn);

    echo "<script>
    alert('We could not record your payment reference. Nothing was charged. Please try again.');
    window.location='../cart.php';
    </script>";

    exit();
}

mysqli_commit($conn);

safe_redirect("../receipt.php?order=" . $order_id);

?>
