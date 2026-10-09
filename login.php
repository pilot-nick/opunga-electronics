<?php

include "php/auth.php";

$redirect = isset($_GET['next']) ? $_GET['next'] : "";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Login - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="auth">

<div class="auth-card">

<div class="auth-head">

<?php echo brand_auth_logo_html(); ?>

<h1>Opunga Cyber and Electronics</h1>

<p>Sign in to your customer account</p>

</div>

<form action="php/login.php" method="POST">

<?php echo csrf_field(); ?>

<input type="hidden" name="next" value="<?php echo htmlspecialchars($redirect, ENT_QUOTES, 'UTF-8'); ?>">

<div class="field">

<label for="email">Email</label>

<input
id="email"
type="email"
name="email"
placeholder="you@example.com"
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

<button type="submit" class="btn btn--block">Login</button>

</form>

<p class="auth-foot">
Don't have an account?
<a href="register.php">Register</a>
</p>

<div class="auth-note">

<span class="auth-note-icon" aria-hidden="true">&#128100;</span>

<span>Customer accounts only. Use the email address you registered with.</span>

</div>

<nav class="auth-portals" aria-label="Other sign in portals">

<a href="login.php" aria-current="page">Customer</a>

<a href="admin_login.php">Admin</a>

<a href="staff_login.php">Staff</a>

</nav>

</div>

</body>

</html>
