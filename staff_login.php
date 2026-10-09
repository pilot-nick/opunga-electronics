<?php

include "php/auth.php";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Staff Login - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="auth auth--staff">

<div class="auth-card">

<div class="auth-head">

<?php echo brand_auth_logo_html(); ?>

<h1>Opunga Cyber and Electronics</h1>

<p>Staff login</p>

</div>

<form action="php/staff_login.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="email">Email</label>

<input
id="email"
type="email"
name="email"
placeholder="staff@example.com"
autocomplete="email"
maxlength="100"
required>

</div>

<div class="field">

<label for="password">Password</label>

<input
id="password"
type="password"
name="password"
placeholder="Enter your password"
autocomplete="current-password"
required>

</div>

<button type="submit" class="btn btn--block">Staff Login</button>

</form>

<div class="auth-note">

<span class="auth-note-icon" aria-hidden="true">&#128737;</span>

<span>This portal is for <b>staff accounts only</b>. It gives access
to bookings and orders, not to products or settings.</span>

</div>

<nav class="auth-portals" aria-label="Other sign in portals">

<a href="login.php">Customer</a>

<a href="admin_login.php">Admin</a>

<a href="staff_login.php" aria-current="page">Staff</a>

</nav>

</div>

</body>

</html>
