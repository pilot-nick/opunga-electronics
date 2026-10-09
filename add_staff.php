<?php

include "php/auth.php";
include "php/db.php";

require_admin();

$staff = mysqli_query($conn,
    "SELECT id, fullname, email, phone
     FROM users
     WHERE role = 'staff'
     ORDER BY fullname");

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Add Staff - Opunga Cyber and Electronics</title>

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

<h1 class="page-title">Add Staff</h1>

<div class="panel form-narrow">

<form action="php/add_staff.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="fullname">Full Name</label>

<input
id="fullname"
type="text"
name="fullname"
placeholder="Staff Member"
maxlength="100"
required>

</div>

<div class="field">

<label for="email">Email</label>

<input
id="email"
type="email"
name="email"
placeholder="staff@example.com"
autocomplete="off"
maxlength="100"
required>

<p class="hint">This is the email they use to sign in on the staff login page.</p>

</div>

<div class="form-grid">

<div class="field">

<label for="phone">Phone (optional)</label>

<input
id="phone"
type="text"
name="phone"
placeholder="+254700000000"
maxlength="20">

</div>

</div>

<div class="field">

<label for="password">Password</label>

<input
id="password"
type="password"
name="password"
placeholder="At least 8 characters"
autocomplete="new-password"
minlength="8"
required>

</div>

<div class="field">

<label for="confirm_password">Confirm Password</label>

<input
id="confirm_password"
type="password"
name="confirm_password"
placeholder="Repeat the password"
autocomplete="new-password"
minlength="8"
required>

</div>

<div class="actions">

<button type="submit" class="btn btn--success">Create Staff Account</button>

<a href="admin_dashboard.php" class="btn btn--ghost">Cancel</a>

</div>

</form>

</div>

<h1 class="page-title">Current Staff</h1>

<div class="table-wrap">

<table>

<thead>

<tr>
<th>ID</th>
<th>Name</th>
<th>Email</th>
<th>Phone</th>
</tr>

</thead>

<tbody>

<?php

$found = false;

while($row = mysqli_fetch_assoc($staff)){

$found = true;

?>

<tr>
<td><?php echo htmlspecialchars($row['id'], ENT_QUOTES, 'UTF-8'); ?></td>
<td class="text-left"><?php echo htmlspecialchars($row['fullname'], ENT_QUOTES, 'UTF-8'); ?></td>
<td class="text-left"><?php echo htmlspecialchars($row['email'], ENT_QUOTES, 'UTF-8'); ?></td>
<td class="mono"><?php echo htmlspecialchars($row['phone'] ? $row['phone'] : '-', ENT_QUOTES, 'UTF-8'); ?></td>
</tr>

<?php } ?>

<?php if(!$found){ ?>

<tr>
<td colspan="4" class="table-empty">No staff accounts yet.</td>
</tr>

<?php } ?>

</tbody>

</table>

</div>

</main>

</div>

</body>

</html>
