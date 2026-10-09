<?php

include "php/auth.php";
include "php/db.php";

require_admin();

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Product - Opunga Cyber and Electronics</title>

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

<div class="container container--narrow" style="padding:0;">

<h1 class="page-title">Add Product</h1>

<div class="panel">

<form action="php/add_product.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="name">Product Name</label>

<input
id="name"
type="text"
name="name"
placeholder="Samsung Galaxy A15"
maxlength="100"
required>

</div>

<div class="field">

<label for="category">Category</label>

<input
id="category"
type="text"
name="category"
placeholder="Phones"
maxlength="50"
required>

<p class="hint">One of: Phones, Laptops, Televisions, Accessories.</p>

</div>

<div class="row">

<div class="field" style="flex:1 1 160px;">

<label for="price">Price (KSh)</label>

<input
id="price"
type="number"
name="price"
min="0"
step="0.01"
placeholder="45000"
required>

</div>

<div class="field" style="flex:1 1 160px;">

<label for="stock">Stock</label>

<input
id="stock"
type="number"
name="stock"
min="0"
step="1"
placeholder="10"
required>

</div>

</div>

<div class="field">

<label for="image">Image File Name</label>

<input
id="image"
type="text"
name="image"
placeholder="a14.jpg"
pattern="[A-Za-z0-9._\-]*"
maxlength="255">

<p class="hint">Place the file in the <strong>images</strong> folder, then enter its name here.</p>

</div>

<div class="actions">

<button type="submit" class="btn btn--success">Save Product</button>

<a href="admin.php" class="btn btn--ghost">Cancel</a>

</div>

</form>

</div>

</div>

</main>

</div>

</body>

</html>
