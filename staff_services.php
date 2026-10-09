<?php

include "php/auth.php";
include "php/db.php";

require_staff();

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

$sql = "SELECT
            service_bookings.id,
            service_bookings.device,
            service_bookings.preferred_date,
            service_bookings.notes,
            service_bookings.quoted_price,
            service_bookings.status,
            service_bookings.created_at,
            services.name,
            services.category,
            services.price,
            users.fullname,
            users.email,
            users.phone
        FROM service_bookings
        LEFT JOIN services
            ON service_bookings.service_id = services.id
        LEFT JOIN users
            ON service_bookings.user_id = users.id
        ORDER BY service_bookings.created_at DESC";

$result = mysqli_query($conn, $sql);

$statuses = array("Requested", "Contacted", "Scheduled", "In Progress", "Completed", "Cancelled");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Staff Services - Opunga Cyber and Electronics</title>

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

<h1 class="page-title">Service Bookings</h1>

<?php echo flash_message(); ?>

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

<div class="toolbar">

<div class="actions">

<a href="staff_dashboard.php" class="btn">Dashboard</a>

</div>

<p class="muted">Update the status of a booking, then save.</p>

</div>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Booking</th>
<th>Customer</th>
<th>Service</th>
<th>Device</th>
<th>Preferred Date</th>
<th>Status</th>
<th>Update</th>

</tr>

</thead>

<tbody>

<?php

$found = false;

while($row = mysqli_fetch_assoc($result)){

$found = true;

$current = $row['status'];

?>

<tr>

<td>

<strong>#<?php echo (int)$row['id']; ?></strong>

<br>

<span class="muted"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span>

</td>

<td class="text-left">

<?php echo htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?>

<br>

<span class="muted"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></span>

<?php if($row['phone'] != ""){ ?>

<br>

<span class="muted"><?php echo htmlspecialchars($row['phone'], ENT_QUOTES, 'UTF-8'); ?></span>

<?php } ?>

</td>

<td class="text-left">

<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>

<br>

<span class="muted">
<?php if($row['quoted_price'] !== null){ ?>
Quoted KSh <?php echo number_format($row['quoted_price']); ?>
<?php } else { ?>
KSh <?php echo number_format($row['price']); ?>
<?php } ?>
</span>

</td>

<td class="text-left">

<?php echo htmlspecialchars($row['device'], ENT_QUOTES, 'UTF-8'); ?>

<?php if($row['notes'] != ""){ ?>

<br>

<span class="muted"><?php echo htmlspecialchars($row['notes'], ENT_QUOTES, 'UTF-8'); ?></span>

<?php } ?>

</td>

<td class="mono"><?php echo $row['preferred_date'] ? $row['preferred_date'] : "Not set"; ?></td>

<td>

<span class="status status--<?php echo strtolower(str_replace(" ", "-", htmlspecialchars($current, ENT_QUOTES, 'UTF-8'))); ?>">

<?php echo htmlspecialchars($current, ENT_QUOTES, 'UTF-8'); ?>

</span>

</td>

<td>

<form action="php/update_booking.php" method="POST">

<?php echo csrf_field(); ?>

<input
type="hidden"
name="booking_id"
value="<?php echo (int)$row['id']; ?>">

<select name="status">

<?php foreach($statuses as $option){ ?>

<option value="<?php echo $option; ?>" <?php if($current === $option) echo "selected"; ?>>

<?php echo $option; ?>

</option>

<?php } ?>

</select>

<input
type="number"
name="quoted_price"
step="0.01"
min="0"
placeholder="Quoted KSh"
value="<?php echo $row['quoted_price'] !== null ? $row['quoted_price'] : ''; ?>"
style="margin-top:8px;">

<button type="submit" class="btn btn--success btn--sm" style="margin-top:8px;">
Save
</button>

</form>

</td>

</tr>

<?php }

if(!$found){
?>

<tr>

<td colspan="7" class="table-empty">No service bookings yet.</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</main>

</div>

</body>

</html>