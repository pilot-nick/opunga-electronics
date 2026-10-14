<?php

include "php/auth.php";
include "php/db.php";

require_login();

require_customer();

$user = $_SESSION['user_id'];

$sql = "SELECT
products.id,
products.name,
products.price,
products.image,
products.stock,
cart.quantity

FROM cart

JOIN products
ON cart.product_id = products.id

WHERE cart.user_id='$user'";

$result = mysqli_query($conn,$sql);

$total = 0;

$rows = array();

while($row=mysqli_fetch_assoc($result)){

$amount = $row['price'] * $row['quantity'];

$total += $amount;

$rows[] = array(
'id' => $row['id'],
'name' => $row['name'],
'image' => $row['image'],
'price' => $row['price'],
'stock' => $row['stock'],
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

<title>Shopping Cart - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<span class="topbar-welcome">Your Cart</span>

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

<h1 class="page-title">Your Shopping Cart</h1>

<?php echo flash_message(); ?>

<?php if(count($rows) == 0){ ?>

<div class="empty">

<strong>Your cart is empty.</strong>

<p>Browse the shop and add a few products to get started.</p>

<a href="index.php" class="btn">Continue Shopping</a>

</div>

<?php } else { ?>

<div class="table-wrap">

<table>

<thead>

<tr>

<th>Image</th>
<th>Product</th>
<th>Price</th>
<th>Quantity</th>
<th>Total</th>
<th>Actions</th>

</tr>

</thead>

<tbody>

<?php foreach($rows as $row){ ?>

<tr>

<td><img
class="img-thumb"
src="images/<?php echo htmlspecialchars($row['image']); ?>"
alt="<?php echo htmlspecialchars($row['name']); ?>"></td>

<td class="text-left"><?php echo htmlspecialchars($row['name']); ?></td>

<td class="mono">KSh <?php echo number_format($row['price']); ?></td>

<td><?php echo $row['quantity']; ?></td>

<td class="mono">KSh <?php echo number_format($row['amount']); ?></td>

<td>

<form method="POST" action="php/update_cart.php" style="display:flex;gap:6px;align-items:center;flex-wrap:wrap;">

<?php echo csrf_field(); ?>

<input type="hidden" name="product_id" value="<?php echo (int)$row['id']; ?>">

<input
type="number"
name="quantity"
value="<?php echo (int)$row['quantity']; ?>"
min="1"
max="<?php echo (int)$row['stock']; ?>"
aria-label="Quantity for <?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>"
style="width:70px;">

<button type="submit" class="btn btn--sm">Update</button>

<button
type="submit"
class="btn btn--sm btn--danger"
formaction="php/remove_from_cart.php"
formnovalidate>Remove</button>

</form>

</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

<div class="total-box">

<h2>Grand Total</h2>

<span class="total-amount">KSh <?php echo number_format($total); ?></span>

</div>

<div class="actions" style="margin-top:18px;">

<a href="checkout.php" class="btn btn--success">Proceed to Checkout</a>

<a href="index.php" class="btn btn--ghost">Continue Shopping</a>

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
