<?php

include "php/auth.php";
include "php/db.php";

require_login();

require_customer();

$user_id = $_SESSION['user_id'];

$result = mysqli_query($conn,
"SELECT * FROM users WHERE id='$user_id'");

$user = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>My Profile - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<span class="topbar-welcome">My Profile</span>

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

<div class="container container--narrow">

<div class="panel" style="margin-top:26px;">

<h1 class="page-title" style="margin-top:0;">My Profile</h1>

<p class="muted">Keep your contact and delivery details up to date.</p>

<div class="divider"></div>

<form action="php/update_profile.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="fullname">Full Name</label>

<input
id="fullname"
type="text"
name="fullname"
value="<?php echo htmlspecialchars($user['fullname']); ?>"
required>

</div>

<div class="field">

<label for="email">Email</label>

<input
id="email"
type="email"
name="email"
value="<?php echo htmlspecialchars($user['email']); ?>"
required>

</div>

<div class="field">

<label for="phone">Phone</label>

<input
id="phone"
type="text"
name="phone"
value="<?php echo htmlspecialchars($user['phone']); ?>">

<p class="hint">Used for delivery calls and M-PESA confirmation.</p>

</div>

<div class="field">

<label for="address">Delivery Address</label>

<textarea
id="address"
name="address"
rows="4"><?php echo htmlspecialchars($user['address']); ?></textarea>

</div>

<button type="submit" class="btn btn--block">Save Changes</button>

</form>

</div>

</div>

<footer class="footer">

<h3><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics Shop</h3>

<p>&#128222; Contact: 0700335569</p>

<p>&#128205; M-PESA Till Number: 3461337</p>

</footer>

</body>

</html>
