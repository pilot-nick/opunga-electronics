<?php

include "php/auth.php";
include "php/db.php";

require_admin();

$requested = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM service_bookings
     WHERE status = 'Requested'");

$in_progress = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM service_bookings
     WHERE status = 'In Progress'");

$completed = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM service_bookings
     WHERE status = 'Completed'");

$total_services = mysqli_query($conn,
    "SELECT COUNT(*) AS total
     FROM services
     WHERE active = 1");

$products = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM products"));

$customers = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM users WHERE role = 'customer'"));

$orders = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders"));

$pending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'"));

$processing = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Processing'"));

$delivered = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Delivered'"));

$lowstock = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM products WHERE stock <= 5"));
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Dashboard - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body>

<div class="admin-shell">

<aside class="sidebar">

<a class="sidebar-brand" href="admin_dashboard.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Admin</a>

<a href="admin_dashboard.php">Dashboard</a>

<a href="admin.php">Products</a>

<a href="admin_orders.php">Orders</a>

<a href="admin_services.php">Services</a>

<a href="add_product.php">&#10133; Add Product</a>

<a href="add_staff.php">&#10133; Add Staff</a>

<a href="index.php">&#127760; Shop</a>

<a href="php/logout.php">&#128682; Logout</a>

<button
type="button"
class="btn btn--topbar"
style="margin:16px 20px 0;width:calc(100% - 40px);"
data-theme-toggle
data-label-dark="&#127769; Dark"
data-label-light="&#9728; Light"
aria-pressed="false">

&#127769; Dark

</button>

</aside>

<main class="admin-main">

<h1 class="page-title">Shop Summary</h1>

<div class="cards">

<div class="stat stat--brand">
<div class="stat-value"><?php echo $products['total']; ?></div>
<div class="stat-label">Products</div>
</div>

<div class="stat stat--purple">
<div class="stat-value"><?php echo $customers['total']; ?></div>
<div class="stat-label">Customers</div>
</div>

<div class="stat stat--teal">
<div class="stat-value"><?php echo $orders['total']; ?></div>
<div class="stat-label">Orders</div>
</div>

</div>

<h1 class="page-title">Service Bookings</h1>

<div class="cards">

<div class="stat stat--brand">
<div class="stat-value"><?php echo mysqli_fetch_assoc($total_services)['total']; ?></div>
<div class="stat-label">Active Services</div>
</div>

<div class="stat stat--warning">
<div class="stat-value"><?php echo mysqli_fetch_assoc($requested)['total']; ?></div>
<div class="stat-label">New Requests</div>
</div>

<div class="stat stat--teal">
<div class="stat-value"><?php echo mysqli_fetch_assoc($in_progress)['total']; ?></div>
<div class="stat-label">In Progress</div>
</div>

<div class="stat stat--success">
<div class="stat-value"><?php echo mysqli_fetch_assoc($completed)['total']; ?></div>
<div class="stat-label">Completed</div>
</div>

</div>

<h1 class="page-title">Order Status</h1>

<div class="cards">

<div class="stat stat--warning">
<div class="stat-value"><?php echo $pending['total']; ?></div>
<div class="stat-label">Pending</div>
</div>

<div class="stat stat--brand">
<div class="stat-value"><?php echo $processing['total']; ?></div>
<div class="stat-label">Processing</div>
</div>

<div class="stat stat--success">
<div class="stat-value"><?php echo $delivered['total']; ?></div>
<div class="stat-label">Delivered</div>
</div>

<div class="stat stat--danger">
<div class="stat-value"><?php echo $lowstock['total']; ?></div>
<div class="stat-label">Low Stock</div>
</div>

</div>

<div class="toolbar">

<div class="actions">

<a href="admin.php" class="btn">Products</a>

<a href="admin_orders.php" class="btn">Orders</a>

<a href="admin_services.php" class="btn">Services</a>

<a href="index.php" class="btn btn--ghost">&#127760; View Shop</a>

</div>

</div>

</main>

</div>

</body>

</html>
