<?php

include "php/auth.php";
include "php/db.php";

start_secure_session();

require_login();

$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;

if($order_id < 1){
    http_response_code(400);
    die("Invalid order.");
}

$isAdmin = isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;

// LEFT JOIN so an order is still viewable even if the customer account
// has since been removed
$stmt = mysqli_prepare($conn,
    "SELECT orders.id, orders.order_date, orders.total, orders.status,
            orders.user_id, users.fullname, users.email
     FROM orders
     LEFT JOIN users ON users.id = orders.user_id
     WHERE orders.id = ?
     LIMIT 1");

mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);

$order = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$order){
    http_response_code(404);
    die("Order not found.");
}

// A customer may only open their own receipt
if(!$isAdmin && (int)$order['user_id'] !== (int)$_SESSION['user_id']){
    http_response_code(403);
    die("You do not have access to that order.");
}

$itemStmt = mysqli_prepare($conn,
    "SELECT products.name, order_items.quantity, order_items.price
     FROM order_items
     JOIN products ON order_items.product_id = products.id
     WHERE order_items.order_id = ?");

mysqli_stmt_bind_param($itemStmt, "i", $order_id);
mysqli_stmt_execute($itemStmt);

$rows = array();

$total = 0;

$itemResult = mysqli_stmt_get_result($itemStmt);

while($row = mysqli_fetch_assoc($itemResult)){

    $amount = $row['price'] * $row['quantity'];

    $total += $amount;

    $rows[] = array(
        'name' => $row['name'],
        'price' => $row['price'],
        'quantity' => $row['quantity'],
        'amount' => $amount
    );

}

mysqli_stmt_close($itemStmt);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Receipt #<?php echo (int)$order_id; ?> - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

</head>

<body class="page">

<div class="container container--narrow">

<div class="receipt">

<div class="receipt-head">

<h1><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics Shop</h1>

<p class="muted">Receipt #<?php echo (int)$order['id']; ?></p>

</div>

<div class="receipt-meta">

<div>
<span>Customer</span>
<strong><?php echo $order['fullname'] !== null ? htmlspecialchars($order['fullname'], ENT_QUOTES, 'UTF-8') : 'Deleted account'; ?></strong>
</div>

<div>
<span>Email</span>
<strong><?php echo $order['email'] !== null ? htmlspecialchars($order['email'], ENT_QUOTES, 'UTF-8') : 'Deleted account'; ?></strong>
</div>

<div>
<span>Date</span>
<strong><?php echo $order['order_date']; ?></strong>
</div>

<div>
<span>Status</span>
<strong><?php echo htmlspecialchars($order['status'], ENT_QUOTES, 'UTF-8'); ?></strong>
</div>

</div>

<table>

<thead>

<tr>
<th>Item</th>
<th>Price</th>
<th>Qty</th>
<th>Amount</th>
</tr>

</thead>

<tbody>

<?php foreach($rows as $item){ ?>

<tr>

<td class="text-left"><?php echo htmlspecialchars($item['name'], ENT_QUOTES, 'UTF-8'); ?></td>

<td class="mono">KSh <?php echo number_format($item['price']); ?></td>

<td><?php echo (int)$item['quantity']; ?></td>

<td class="mono">KSh <?php echo number_format($item['amount']); ?></td>

</tr>

<?php } ?>

</tbody>

<tfoot>

<tr>

<th colspan="3" class="text-left">Total Paid</th>

<th class="mono">KSh <?php echo number_format($order['total']); ?></th>

</tr>

</tfoot>

</table>

<p style="margin:0 0 12px;">Thank you for shopping with Opunga Cyber and Electronics Shop.</p>

<p class="muted">&#128222; Contact: 0700335569</p>

<p class="muted">&#128205; M-PESA Till Number: 3461337</p>

<div class="actions" style="margin-top:20px;">

<a href="orders.php" class="btn">&#128722; My Orders</a>

<button type="button" class="btn btn--ghost" onclick="window.print()">&#128424; Print Receipt</button>

</div>

</div>

</div>

</body>

</html>
