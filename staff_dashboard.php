<?php

include "php/auth.php";
include "php/db.php";

require_staff();

$total_orders = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders"));

$pending = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Pending'"));

$processing = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Processing'"));

$delivered = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM orders WHERE status = 'Delivered'"));

$total_services = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM services WHERE active = 1"));

$requested = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM service_bookings WHERE status = 'Requested'"));

$in_progress = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM service_bookings WHERE status = 'In Progress'"));

$completed = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT COUNT(*) AS total FROM service_bookings WHERE status = 'Completed'"));

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Staff Dashboard - Opunga Cyber and Electronics</title>

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

<h1 class="page-title">Staff Overview</h1>

<div class="cards">

<div class="stat stat--brand">
<div class="stat-value"><?php echo $total_orders['total']; ?></div>
<div class="stat-label">Total Orders</div>
</div>

<div class="stat stat--warning">
<div class="stat-value"><?php echo $pending['total']; ?></div>
<div class="stat-label">Pending</div>
</div>

<div class="stat stat--purple">
<div class="stat-value"><?php echo $processing['total']; ?></div>
<div class="stat-label">Processing</div>
</div>

<div class="stat stat--success">
<div class="stat-value"><?php echo $delivered['total']; ?></div>
<div class="stat-label">Delivered</div>
</div>

</div>

<h1 class="page-title">Service Bookings</h1>

<div class="cards">

<div class="stat stat--brand">
<div class="stat-value"><?php echo $total_services['total']; ?></div>
<div class="stat-label">Active Services</div>
</div>

<div class="stat stat--warning">
<div class="stat-value"><?php echo $requested['total']; ?></div>
<div class="stat-label">New Requests</div>
</div>

<div class="stat stat--teal">
<div class="stat-value"><?php echo $in_progress['total']; ?></div>
<div class="stat-label">In Progress</div>
</div>

<div class="stat stat--success">
<div class="stat-value"><?php echo $completed['total']; ?></div>
<div class="stat-label">Completed</div>
</div>

</div>

<div class="toolbar">

<div class="actions">

<a href="staff_orders.php" class="btn">Orders</a>

<a href="staff_services.php" class="btn">Services</a>

</div>

</div>

</main>

</div>

</body>

</html>