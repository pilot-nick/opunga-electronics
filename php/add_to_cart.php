<?php

include "auth.php";
include "db.php";

require_login();

require_customer();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../cart.php");
}

csrf_check();

$user_id = (int)$_SESSION['user_id'];

$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 0;

if($product_id < 1 || $quantity < 1){
    safe_redirect("../index.php");
}

$check = mysqli_prepare($conn, "SELECT stock FROM products WHERE id = ? LIMIT 1");

mysqli_stmt_bind_param($check, "i", $product_id);
mysqli_stmt_execute($check);

$row = mysqli_stmt_get_result($check)->fetch_assoc();

mysqli_stmt_close($check);

if(!$row){
    safe_redirect("../index.php");
}

$stock = (int)$row['stock'];

if($stock < 1){
    echo "<script>
    alert('Sorry, that product is out of stock.');
    window.location='../index.php';
    </script>";

    exit();
}

if($quantity > $stock){
    $quantity = $stock;
}

$existing = mysqli_prepare($conn,
    "SELECT id, quantity FROM cart
     WHERE user_id = ? AND product_id = ?
     LIMIT 1");

mysqli_stmt_bind_param($existing, "ii", $user_id, $product_id);
mysqli_stmt_execute($existing);

$current = mysqli_stmt_get_result($existing)->fetch_assoc();

mysqli_stmt_close($existing);

if($current){

    $newQty = (int)$current['quantity'] + $quantity;

    if($newQty > $stock){
        $newQty = $stock;
    }

    $cart_id = (int)$current['id'];

    $update = mysqli_prepare($conn, "UPDATE cart SET quantity = ? WHERE id = ?");

    mysqli_stmt_bind_param($update, "ii", $newQty, $cart_id);

    $ok = mysqli_stmt_execute($update);

    mysqli_stmt_close($update);

}else{

    $insert = mysqli_prepare($conn,
        "INSERT INTO cart(user_id, product_id, quantity)
         VALUES(?, ?, ?)");

    mysqli_stmt_bind_param($insert, "iii", $user_id, $product_id, $quantity);

    $ok = mysqli_stmt_execute($insert);

    mysqli_stmt_close($insert);
}

// Do not bounce back to the cart as though the item landed when it did not.
if(!$ok){
    echo "<script>
    alert('Sorry, that item could not be added to your cart. Please try again.');
    window.location='../index.php';
    </script>";

    exit();
}

safe_redirect("../cart.php?ok=" . rawurlencode("Added to your cart."));

?>
