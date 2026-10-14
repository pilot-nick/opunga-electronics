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

if($product_id < 1){
    safe_redirect("../cart.php");
}

$del = mysqli_prepare($conn, "DELETE FROM cart WHERE user_id = ? AND product_id = ?");

mysqli_stmt_bind_param($del, "ii", $user_id, $product_id);
mysqli_stmt_execute($del);

mysqli_stmt_close($del);

safe_redirect("../cart.php?ok=" . rawurlencode("Item removed from your cart."));

?>