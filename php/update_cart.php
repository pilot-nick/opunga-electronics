<?php

include "auth.php";
include "db.php";

require_login();

require_customer();

$user_id = (int)$_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../cart.php");
}

csrf_check();

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;

if($product_id < 1){
    safe_redirect("../cart.php?error=" . rawurlencode("That item could not be found."));
}

// A quantity of zero or less is treated as removing the line
if($quantity < 1){

    $del = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ? AND product_id = ?");

    mysqli_stmt_bind_param($del, "ii", $user_id, $product_id);
    mysqli_stmt_execute($del);

    mysqli_stmt_close($del);

    safe_redirect("../cart.php?ok=" . rawurlencode("Item removed from your cart."));

}

// The item must still be in this user's cart, and it must still be stocked
$stmt = mysqli_prepare($conn,
    "SELECT products.stock
     FROM cart
     JOIN products ON cart.product_id = products.id
     WHERE cart.user_id = ? AND cart.product_id = ?
     LIMIT 1");

mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
mysqli_stmt_execute($stmt);

$row = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$row){
    safe_redirect("../cart.php?error=" . rawurlencode("That item is not in your cart."));
}

$stock = (int)$row['stock'];

if($stock < 1){
    safe_redirect("../cart.php?error=" . rawurlencode("That item is now out of stock."));
}

$notice = "";

if($quantity > $stock){
    $quantity = $stock;

    $notice = "Only " . $stock . " unit(s) available, so the quantity was adjusted.";
}

$update = mysqli_prepare($conn,
    "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");

mysqli_stmt_bind_param($update, "iii", $quantity, $user_id, $product_id);
mysqli_stmt_execute($update);

mysqli_stmt_close($update);

if($notice !== ""){
    safe_redirect("../cart.php?error=" . rawurlencode($notice));
}

safe_redirect("../cart.php?ok=" . rawurlencode("Cart updated."));

?>