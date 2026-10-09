<?php

include "php/auth.php";
include "php/db.php";

start_secure_session();

require_customer();

$user_id = $_SESSION['user_id'] ?? null;

$success = isset($_GET['booked']) ? $_GET['booked'] : "";
$error = isset($_GET['error']) ? htmlspecialchars($_GET['error']) : "";

$filter = isset($_GET['category']) ? mysqli_real_escape_string($conn, $_GET['category']) : "";

$sql = "SELECT * FROM services WHERE active=1";

if($filter != ""){
    $sql .= " AND category='$filter'";
}

$sql .= " ORDER BY category, name";

$result = mysqli_query($conn,$sql);

$category_sql = "SELECT DISTINCT category FROM services WHERE active=1 ORDER BY category";

$categories = mysqli_query($conn,$category_sql);

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Cyber Services - Opunga Cyber and Electronics</title>

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

<h1 class="page-title">Cyber Services</h1>

<p class="muted">

Book any of our services online. We will confirm your booking by phone, then
come to your home or office at a time that suits you.

</p>

<?php if($success != ""){ ?>

<div class="alert alert--success">

Your booking was received. We will call you shortly to confirm.

<a href="my_services.php" class="btn btn--sm btn--teal">View My Bookings</a>

</div>

<?php } ?>

<?php if($error != ""){ ?>

<div class="alert alert--danger"><?php echo $error; ?></div>

<?php } ?>

<div class="search-box">

<form method="GET" class="row">

<select name="category" style="flex:0 1 240px;">

<option value="">All Service Categories</option>

<?php while($cat=mysqli_fetch_assoc($categories)){ ?>

<option
value="<?php echo htmlspecialchars($cat['category']); ?>"
<?php if($filter==$cat['category']) echo "selected"; ?>>

<?php echo htmlspecialchars($cat['category']); ?>

</option>

<?php } ?>

</select>

<button type="submit" class="btn">Filter</button>

<?php if($filter != ""){ ?>

<a href="services.php" class="btn btn--ghost">Reset</a>

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

<div class="service-icon" aria-hidden="true"><?php echo htmlspecialchars($row['icon'], ENT_QUOTES, 'UTF-8'); ?></div>

<h3><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></h3>

<p>Category: <b><?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?></b></p>

<p class="card-desc"><?php echo htmlspecialchars($row['description'], ENT_QUOTES, 'UTF-8'); ?></p>

<p class="card-price">KSh <?php echo number_format($row['price']); ?></p>

<form action="php/book_service.php" method="POST">

<?php echo csrf_field(); ?>

<input type="hidden" name="service_id" value="<?php echo $row['id']; ?>">

<div class="field">

<label for="device<?php echo $row['id']; ?>">Device Or Item</label>

<input
id="device<?php echo $row['id']; ?>"
type="text"
name="device"
placeholder="e.g. HP EliteBook laptop"
maxlength="100"
required>

</div>

<div class="field">

<label for="date<?php echo $row['id']; ?>">Preferred Date</label>

<input
id="date<?php echo $row['id']; ?>"
type="date"
name="preferred_date"
min="<?php echo date("Y-m-d"); ?>">

</div>

<div class="field">

<label for="notes<?php echo $row['id']; ?>">Notes (Optional)</label>

<textarea
id="notes<?php echo $row['id']; ?>"
name="notes"
rows="2"
maxlength="500"
placeholder="Describe the problem briefly."></textarea>

</div>

<button type="submit" class="btn btn--block">

Book This Service

</button>

</form>

</div>

<?php }

if(!$found){
?>

<div class="empty" style="grid-column:1 / -1;">

<strong>No services found.</strong>

<p>Try a different category filter.</p>

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
