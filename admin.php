<?php

include "php/auth.php";
include "php/db.php";

require_admin();

$search = isset($_GET['search']) ? trim($_GET['search']) : "";
$category = isset($_GET['category']) ? trim($_GET['category']) : "";

$sql = "SELECT * FROM products WHERE 1=1";

$params = array();
$types = "";

if($search != ""){

    $like = "%" . $search . "%";

    $sql .= " AND (name LIKE ? OR category LIKE ?)";

    $params[] = $like;
    $params[] = $like;

    $types .= "ss";

}

if($category != ""){

    $sql .= " AND category = ?";

    $params[] = $category;

    $types .= "s";

}

$sql .= " ORDER BY id DESC";

$stmt = mysqli_prepare($conn, $sql);

if($params){
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

mysqli_stmt_close($stmt);

$categories = mysqli_query($conn,
    "SELECT DISTINCT category FROM products ORDER BY category");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Manage Products - Opunga Cyber and Electronics</title>

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

<h1 class="page-title">Manage Products</h1>

<?php echo flash_message(); ?>

<div class="toolbar">

<div class="actions">

<a href="add_product.php" class="btn btn--success">&#10133; Add Product</a>

<a href="admin_orders.php" class="btn btn--purple">Customer Orders</a>

</div>

<form method="GET">

<input
type="text"
name="search"
placeholder="Search product or category..."
value="<?php echo htmlspecialchars($search, ENT_QUOTES, 'UTF-8'); ?>">

<select name="category">

<option value="">All Categories</option>

<?php while($cat=mysqli_fetch_assoc($categories)){ ?>

<option
value="<?php echo htmlspecialchars($cat['category'], ENT_QUOTES, 'UTF-8'); ?>"
<?php if($category === $cat['category']) echo "selected"; ?>>

<?php echo htmlspecialchars($cat['category'], ENT_QUOTES, 'UTF-8'); ?>

</option>

<?php } ?>

</select>

<button type="submit" class="btn">Search</button>

</form>

</div>

<div class="table-wrap">

<table>

<thead>

<tr>
<th>ID</th>
<th>Image</th>
<th>Name</th>
<th>Category</th>
<th>Price</th>
<th>Stock</th>
<th>Actions</th>
</tr>

</thead>

<tbody>

<?php

$found = false;

while($row=mysqli_fetch_assoc($result)){

$found = true;

?>

<tr>

<td><?php echo (int)$row['id']; ?></td>

<td>
<img
class="img-thumb"
src="images/<?php echo htmlspecialchars($row['image'], ENT_QUOTES, 'UTF-8'); ?>"
alt="<?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?>">
</td>

<td class="text-left"><?php echo htmlspecialchars($row['name'], ENT_QUOTES, 'UTF-8'); ?></td>

<td><?php echo htmlspecialchars($row['category'], ENT_QUOTES, 'UTF-8'); ?></td>

<td class="mono">KSh <?php echo number_format($row['price']); ?></td>

<td>

<?php if($row['stock'] <= 5){ ?>

<span class="stock-tag stock-tag--low"><?php echo (int)$row['stock']; ?></span>

<?php } else { ?>

<span class="stock-tag"><?php echo (int)$row['stock']; ?></span>

<?php } ?>

</td>

<td>

<div class="actions" style="justify-content:center;">

<a
class="btn btn--warning btn--sm"
href="edit_product.php?id=<?php echo (int)$row['id']; ?>">

Edit

</a>

<form
action="php/delete_product.php"
method="POST"
style="display:inline;"
onsubmit="return confirm('Delete this product?')">

<?php echo csrf_field(); ?>

<input type="hidden" name="id" value="<?php echo (int)$row['id']; ?>">

<button type="submit" class="btn btn--danger btn--sm">

Delete

</button>

</form>

</div>

</td>

</tr>

<?php }

if(!$found){
?>

<tr>

<td colspan="7" class="table-empty">No products matched your search.</td>

</tr>

<?php } ?>

</tbody>

</table>

</div>

</main>

</div>

</body>

</html>
