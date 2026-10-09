<?php

include "php/auth.php";
include "php/db.php";

start_secure_session();

require_customer();

// Search and Category Filter
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn,$_GET['search']) : "";
$category = isset($_GET['category']) ? mysqli_real_escape_string($conn,$_GET['category']) : "";

$sql = "SELECT * FROM products WHERE 1";

if($search != ""){
    $sql .= " AND name LIKE '%$search%'";
}

if($category != ""){
    $sql .= " AND category='$category'";
}

$result = mysqli_query($conn,$sql);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Opunga Cyber and Electronics Shop</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<?php if(isset($_SESSION['user_id'])): ?>
<span class="topbar-welcome">
Welcome back,
<strong><?php echo htmlspecialchars($_SESSION['fullname']); ?></strong>
</span>
<?php else: ?>
<span class="topbar-welcome">
<a href="login.php">Login</a> | <a href="register.php">Register</a>
</span>
<?php endif; ?>

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

<div class="search-box">

<form method="GET" class="row">

<input
type="text"
name="search"
placeholder="Search products..."
value="<?php echo htmlspecialchars($search); ?>"
style="flex:1 1 240px;">

<select name="category" style="flex:0 1 190px;">

<option value="">All Categories</option>

<option value="Phones" <?php if($category=="Phones") echo "selected"; ?>>Phones</option>

<option value="Laptops" <?php if($category=="Laptops") echo "selected"; ?>>Laptops</option>

<option value="Televisions" <?php if($category=="Televisions") echo "selected"; ?>>Televisions</option>

<option value="Accessories" <?php if($category=="Accessories") echo "selected"; ?>>Accessories</option>

</select>

<button type="submit" class="btn">Search</button>

<?php if($search != "" || $category != ""){ ?>

<a href="index.php" class="btn btn--ghost">Reset</a>

<?php } ?>

</form>

</div>

<div class="products">

<?php
$found = false;

while($row=mysqli_fetch_assoc($result)){
$found = true;
?>

<div class="card">

<img
src="images/<?php echo htmlspecialchars($row['image']); ?>"
alt="<?php echo htmlspecialchars($row['name']); ?>"
loading="lazy">

<h3><?php echo htmlspecialchars($row['name']); ?></h3>

<p>Category: <b><?php echo htmlspecialchars($row['category']); ?></b></p>

<p class="card-price">KSh <?php echo number_format($row['price']); ?></p>

<p>
<?php if($row['stock'] > 5){ ?>
<span class="stock-tag"><?php echo $row['stock']; ?> in stock</span>
<?php } else { ?>
<span class="stock-tag stock-tag--low"><?php echo $row['stock']; ?> left</span>
<?php } ?>
</p>

<form action="php/add_to_cart.php" method="POST">

<?php echo csrf_field(); ?>

<input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">

<div class="qty">

<label for="qty<?php echo $row['id']; ?>">Qty</label>

<input
id="qty<?php echo $row['id']; ?>"
type="number"
name="quantity"
value="1"
min="1"
max="<?php echo $row['stock']; ?>">

</div>

<button type="submit" class="btn btn--block" <?php if($row['stock']<1) echo "disabled"; ?>>

Add To Cart

</button>

</form>

</div>

<?php }

if(!$found){
?>

<div class="empty" style="grid-column:1 / -1;">

<strong>No products found.</strong>

<p>Try a different search term or clear the category filter.</p>

</div>

<?php } ?>

</div>

</div>

<footer class="footer">

<h3><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics Shop</h3>

<p>&#128222; Contact: 0700335569</p>

<p>&#128205; M-PESA Till Number: 3461337</p>

<p class="muted">&copy; <?php echo date("Y"); ?> Opunga Cyber and Electronics Shop. All Rights Reserved.</p>

</footer>

</body>

</html>
