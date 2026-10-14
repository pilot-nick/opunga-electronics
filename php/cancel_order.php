<?php

include "auth.php";
include "db.php";
include "mpesa.php";

require_login();

$user_id = (int)$_SESSION['user_id'];

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../cart.php");
}

csrf_check();

$order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;

if($order_id < 1){
    safe_redirect("../cart.php");
}

// A customer may only cancel their own still-open order
$stmt = mysqli_prepare($conn,
    "SELECT id, status FROM orders WHERE id = ? AND user_id = ? LIMIT 1");

mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);

$row = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$row){
    echo "<script>
    alert('That order could not be found.');
    window.location='../orders.php';
    </script>";

    exit();
}

if($row['status'] !== 'Pending' && $row['status'] !== 'Processing'){
    echo "<script>
    alert('That order cannot be cancelled.');
    window.location='../orders.php';
    </script>";

    exit();
}

// Return the stock, drop the never-paid payment row and cancel the order
mpesa_abandon_order($conn, $order_id);

echo "<script>
alert('Order cancelled. Nothing was charged and the stock was put back.');
window.location='../cart.php';
</script>";

exit();

?>