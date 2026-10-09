<?php

include "php/auth.php";
include "php/db.php";

require_staff();

$sql = "SELECT
            orders.id,
            users.fullname,
            users.email,
            orders.total,
            orders.status,
            orders.order_date
        FROM orders
        INNER JOIN users
            ON orders.user_id = users.id
        ORDER BY orders.order_date DESC";

$result = mysqli_query($conn, $sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Staff Orders - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body>

<div class="admin-shell">

<aside class="sidebar">

<a class="sidebar-brand" href="staff_dashboard.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Staff</a>

<a href="staff_dashboard.php">Dashboard</a>

<a href="staff_orders.php">Orders</a>

<a href="staff_services.php">Services</a>

<a href="php/logout.php">Logout</a>

</aside>

<main class="admin-main">

<h1 class="page-title">Customer Orders</h1>

<?php echo flash_message(); ?>

<div class="toolbar">

<div class="actions">

<a href="staff_dashboard.php" class="btn">Dashboard</a>

</div>

<p class="muted">Update the status of an order, then save.</p>

</div>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Order ID</th>
<th>Customer</th>
<th>Email</th>
<th>Total</th>
<th>Status</th>
<th>Date</th>
<th>Update</th>
<th>Receipt</th>

</tr>

</thead>

<tbody>

<?php

$found = false;

while($row = mysqli_fetch_assoc($result)){

$found = true;

$current = $row['status'];

$statuses = array("Pending", "Processing", "Shipped", "Delivered", "Paid", "Cancelled");

?>

<tr>

<td><strong>#<?php echo (int)$row['id']; ?></strong></td>

<td class="text-left"><?php echo htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>

<td class="text-left"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>

<td class="mono">KSh <?php echo number_format($row['total']); ?></td>

<td>

<span class="status status--<?php echo strtolower(str_replace(" ", "-", htmlspecialchars($current, ENT_QUOTES, 'UTF-8'))); ?>">

<?php echo htmlspecialchars($current, ENT_QUOTES, 'UTF-8'); ?>

</span>

</td>

<td class="mono"><?php echo $row['order_date']; ?></td>

<td>

<form action="php/update_order.php" method="POST">

<?php echo csrf_field(); ?>

<input
type="hidden"
name="order_id"
value="<?php echo (int)$row['id']; ?>">

<select name="status">

<?php foreach($statuses as $option){ ?>

<option value="<?php echo $option; ?>" <?php if($current === $option) echo "selected"; ?>>

<?php echo $option; ?>

</option>

<?php } ?>

</select>

<button type="submit" class="btn btn--success btn--sm" style="margin-top:8px;">
Save
</button>

</form>

</td>

<td>

<a
class="btn btn--teal btn--sm"
href="receipt.php?order=<?php echo (int)$row['id']; ?>"
target="_blank">

View Receipt

</a>

</td>

</tr>

<?php }

if(!$found){
?>

<tr>

<td colspan="8" class="table-empty">No customer orders yet.</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</main>

</div>

</body>

</html>