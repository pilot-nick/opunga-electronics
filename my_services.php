<?php

include "php/auth.php";
include "php/db.php";

require_login();

require_customer();

$user_id = $_SESSION['user_id'];

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
            services.price
        FROM service_bookings
        LEFT JOIN services
            ON service_bookings.service_id = services.id
        WHERE service_bookings.user_id = '$user_id'
        ORDER BY service_bookings.created_at DESC";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Service Bookings - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<span class="topbar-welcome">Service Bookings</span>

<button
type="button"
class="btn btn--topbar"
id="darkBtn"
data-theme-toggle
data-label-dark="&#127769; Dark Mode"
data-label-light="&#9728; Light Mode"
aria-pressed="false">

&#127769; Dark Mode

</button>

</div>

</header>

<nav class="nav">

<div class="nav-list">

<a href="index.php">Home</a>

<a href="cart.php">Cart</a>

<a href="checkout.php">Checkout</a>

<a href="orders.php">My Orders</a>

<a href="services.php">Cyber Services</a>

<a href="my_services.php">My Bookings</a>

<a href="profile.php">My Profile</a>

<span class="nav-spacer"></span>

<?php admin_nav_link(); ?>

<a href="php/logout.php">Logout</a>

</div>

</nav>

<div class="container">

<h1 class="page-title">My Service Bookings</h1>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Booking</th>
<th>Service</th>
<th>Device</th>
<th>Preferred Date</th>
<th>Price</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php

$found = false;

while($row=mysqli_fetch_assoc($result)){

$found = true;

// Escaped because the value is dropped straight into a class attribute
$status = strtolower(str_replace(" ", "-", $row['status']));

$status = preg_replace('/[^a-z0-9-]/', '', $status);

?>

<tr>

<td>

<strong>#<?php echo $row['id']; ?></strong>

<br>

<span class="muted"><?php echo date("d M Y", strtotime($row['created_at'])); ?></span>

</td>

<td class="text-left">

<?php echo htmlspecialchars($row['name']); ?>

<br>

<span class="muted"><?php echo htmlspecialchars($row['category']); ?></span>

<?php if($row['notes'] != ""){ ?>

<br>

<span class="muted"><?php echo htmlspecialchars($row['notes']); ?></span>

<?php } ?>

</td>

<td class="text-left"><?php echo htmlspecialchars($row['device']); ?></td>

<td class="mono"><?php echo $row['preferred_date'] ? $row['preferred_date'] : "Not set"; ?></td>

<td class="mono">KSh <?php echo number_format($row['price']); ?></td>

<td>

<span class="status status--<?php echo htmlspecialchars($status, ENT_QUOTES, 'UTF-8'); ?>">

<?php echo htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8'); ?>

</span>

</td>

</tr>

<?php }

if(!$found){
?>

<tr>

<td colspan="6" class="table-empty">

You have not booked any services yet.

<a href="services.php" class="btn btn--sm">Browse Services</a>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</div>

<footer class="footer">

<h3><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics Shop</h3>

<p>&#128222; Contact: 0700335569</p>

<p>&#128205; M-PESA Till Number: 3461337</p>

</footer>

</body>

</html>
