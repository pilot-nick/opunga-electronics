<?php

include "php/auth.php";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Admin Login - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="auth auth--admin">

<div class="auth-card">

<div class="auth-head">

<?php echo brand_auth_logo_html(); ?>

<h1>Opunga Cyber and Electronics</h1>

<p>Administrator login</p>

</div>

<form action="php/admin_login.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="email">Email</label>

<input
id="email"
type="email"
name="email"
placeholder="admin@example.com"
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

<button type="submit" class="btn btn--block">Admin Login</button>

</form>

<div class="auth-note">

<span class="auth-note-icon" aria-hidden="true">&#128274;</span>

<span>This portal is for <b>administrator accounts only</b>.
Staff sign in on the separate staff login page.</span>

</div>

<nav class="auth-portals" aria-label="Other sign in portals">

<a href="login.php">Customer</a>

<a href="admin_login.php" aria-current="page">Admin</a>

<a href="staff_login.php">Staff</a>

</nav>

</div>

</body>

</html>