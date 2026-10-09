<?php

include "php/auth.php";
include "php/db.php";

require_login();

require_customer();

$user_id = $_SESSION['user_id'];

$sql="SELECT
products.id,
products.name,
products.price,
cart.quantity

FROM cart

JOIN products
ON cart.product_id=products.id

WHERE cart.user_id='$user_id'";

$result=mysqli_query($conn,$sql);

$rows = array();

$total=0;

while($row=mysqli_fetch_assoc($result)){

$amount=$row['price']*$row['quantity'];

$total+=$amount;

$rows[] = array(
'name' => $row['name'],
'price' => $row['price'],
'quantity' => $row['quantity'],
'amount' => $amount
);

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Checkout - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<span class="topbar-welcome">Secure Checkout</span>

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

<div class="container container--mid">

<h1 class="page-title">Checkout</h1>

<?php if(count($rows) == 0){ ?>

<div class="empty">

<strong>Nothing to check out.</strong>

<p>Your cart is empty right now.</p>

<a href="index.php" class="btn">Continue Shopping</a>

</div>

<?php } else { ?>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Product</th>
<th>Price</th>
<th>Quantity</th>
<th>Total</th>

</tr>

</thead>

<tbody>

<?php foreach($rows as $row){ ?>

<tr>

<td class="text-left"><?php echo htmlspecialchars($row['name']); ?></td>

<td class="mono">KSh <?php echo number_format($row['price']); ?></td>

<td><?php echo $row['quantity']; ?></td>

<td class="mono">KSh <?php echo number_format($row['amount']); ?></td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div class="total-box">

<h2>Grand Total</h2>

<span class="total-amount">KSh <?php echo number_format($total); ?></span>

</div>

<div class="panel" style="margin-top:20px;">

<h2>M-PESA Payment</h2>

<div class="receipt-meta">

<div>
<span>M-PESA Till Number</span>
<strong>3461337</strong>
</div>

<div>
<span>Shop Contact</span>
<strong>0700335569</strong>
</div>

</div>

<form action="php/place_order.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="mpesa_code">Enter M-PESA Transaction Code</label>

<input
id="mpesa_code"
type="text"
name="mpesa_code"
placeholder="e.g. QGH7X2K4LM"
pattern="[A-Za-z0-9]{6,}"
required>

<p class="hint">Found the code in your M-PESA confirmation SMS.</p>

</div>

<button type="submit" class="btn btn--success btn--block">Complete Order</button>

</form>

</div>

<?php } ?>

</div>

<footer class="footer">

<h3><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics Shop</h3>

<p>&#128222; Contact: 0700335569</p>

<p>&#128205; M-PESA Till Number: 3461337</p>

</footer>

</body>

</html>
