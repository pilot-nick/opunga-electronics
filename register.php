<?php

include "php/auth.php";

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Register - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

<script src="js/app.js"></script>

</head>

<body class="auth">

<div class="auth-card">

<div class="auth-head">

<?php echo brand_auth_logo_html(); ?>

<h1>Create Account</h1>

<p>Register to start shopping with us</p>

</div>

<form action="php/register.php" method="POST">

<?php echo csrf_field(); ?>

<div class="field">

<label for="fullname">Full Name</label>

<input
id="fullname"
type="text"
name="fullname"
placeholder="Jane Doe"
autocomplete="name"
maxlength="100"
required>

</div>

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

<label for="phone">Phone (Optional)</label>

<input
id="phone"
type="tel"
name="phone"
placeholder="07XXXXXXXX"
autocomplete="tel"
maxlength="20"
pattern="[0-9+\- ]{7,20}">

<p class="hint">We use this to confirm service bookings by phone.</p>

</div>

<div class="field">

<label for="password">Password</label>

<input
id="password"
type="password"
name="password"
placeholder="Create a password"
minlength="8"
autocomplete="new-password"
required>

<p class="hint">Use at least 8 characters.</p>

</div>

<div class="field">

<label for="confirm_password">Confirm Password</label>

<input
id="confirm_password"
type="password"
name="confirm_password"
placeholder="Repeat your password"
minlength="8"
autocomplete="new-password"
required>

</div>

<button type="submit" class="btn btn--block">Register</button>

</form>

<p class="auth-foot">
Already have an account?
<a href="login.php">Login</a>
</p>

</div>

</body>

</html>
