<?php

include "php/auth.php";
include "php/db.php";

require_admin();

if(!isset($_GET['id'])){
    safe_redirect("admin.php");
}

$id = intval($_GET['id']);

$stmt = mysqli_prepare($conn, "SELECT * FROM products WHERE id = ? LIMIT 1");

mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

$product = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$product){
    http_response_code(404);
    die("Product not found.");
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Edit Product - Opunga Cyber and Electronics</title>

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

<h1 class="page-title">Edit Product</h1>

<div class="panel">

<?php if($product['image'] != ""){ ?>

<img
class="img-thumb"
src="images/<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
alt="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
style="width:100%;height:200px;">

<?php } ?>

<form action="php/update_product.php" method="POST">

<?php echo csrf_field(); ?>

<input type="hidden" name="id" value="<?php echo $product['id']; ?>">

<div class="field">

<label for="name">Product Name</label>

<input
id="name"
type="text"
name="name"
value="<?php echo htmlspecialchars($product['name'], ENT_QUOTES, 'UTF-8'); ?>"
maxlength="100"
required>

</div>

<div class="field">

<label for="category">Category</label>

<input
id="category"
type="text"
name="category"
value="<?php echo htmlspecialchars($product['category'], ENT_QUOTES, 'UTF-8'); ?>"
maxlength="50"
required>

</div>

<div class="field">

<label for="price">Price (KSh)</label>

<input
id="price"
type="number"
name="price"
step="0.01"
min="0"
value="<?php echo $product['price']; ?>"
required>

</div>

<div class="field">

<label for="image">Image File Name</label>

<input
id="image"
type="text"
name="image"
value="<?php echo htmlspecialchars($product['image'], ENT_QUOTES, 'UTF-8'); ?>"
pattern="[A-Za-z0-9._\-]*"
maxlength="255">

<p class="hint">Place the file in the <strong>images</strong> folder, then enter its name here.</p>

</div>

<div class="field">

<label for="stock">Stock</label>

<input
id="stock"
type="number"
name="stock"
min="0"
value="<?php echo $product['stock']; ?>"
required>

</div>

<button type="submit" class="btn btn--success">Save Changes</button>

<a href="admin.php" class="btn btn--ghost">Cancel</a>

</form>

</div>

</main>

</div>

</body>

</html>
