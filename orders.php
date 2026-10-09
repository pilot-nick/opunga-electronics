<?php

include "php/auth.php";
include "php/db.php";

require_login();

require_customer();

$user_id = $_SESSION['user_id'];

$sql = "SELECT * FROM orders WHERE user_id='$user_id' ORDER BY order_date DESC";

$result = mysqli_query($conn,$sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Customer Orders - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<span class="topbar-welcome">Order History</span>

<button
type="button"
class="btn btn--topbar"
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

<h1 class="page-title">Customer Orders</h1>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Order ID</th>
<th>Date</th>
<th>Total</th>
<th>Status</th>
<th>Receipt</th>

</tr>

</thead>

<tbody>

<?php

$found = false;

while($row=mysqli_fetch_assoc($result)){

$found = true;

?>

<tr>

<td><strong>#<?php echo $row['id']; ?></strong></td>

<td class="mono"><?php echo $row['order_date']; ?></td>

<td class="mono">KSh <?php echo number_format($row['total']); ?></td>

<td>

<span class="status status--<?php echo strtolower(htmlspecialchars($row['status'])); ?>">

<?php echo htmlspecialchars($row['status']); ?>

</span>

</td>

<td>

<a
class="btn btn--teal btn--sm"
href="receipt.php?order=<?php echo $row['id']; ?>"
target="_blank">

View Receipt

</a>

</td>

</tr>

<?php }

if(!$found){
?>

<tr>

<td colspan="5" class="table-empty">You have not placed any orders yet.</td>

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
