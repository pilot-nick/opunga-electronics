# Deploying Opunga Cyber and Electronics to cPanel

This guide is for standard cPanel shared hosting running Apache 2.4 and PHP 7.4 or 8.x.

## 1. Create the database in cPanel

1. Open **cPanel → MySQL Databases**.
2. Create a database, for example `yourname_opunga`.
3. Click **Add User to Database** and set a strong password.
4. Note all three values, you need them in the next step:
   - Database name: `yourname_opunga`
   - Username: `yourname_opungauser`
   - Host: `localhost`
5. In **phpMyAdmin**, import `opunga_shop.sql` that sits in the project folder. It contains the
   tables plus the four products, twenty services, and the admin account.

## 2. Set the database credentials

cPanel shared hosting usually cannot set PHP environment variables through the panel, so
create a config file one level above the public folder instead.

**Option A, environment variables (preferred if your host supports them)**

Add these to your host's PHP configuration or `.htaccess`:

```
SetEnv OPUNGA_DB_HOST localhost
SetEnv OPUNGA_DB_NAME yourname_opunga
SetEnv OPUNGA_DB_USER yourname_opungauser
SetEnv OPUNGA_DB_PASS your-database-password
```

**Option B, a config file (works everywhere)**

Create `config.php` in the folder *above* `public_html`, next to it:

```php
<?php
define('OPUNGA_DB_HOST', 'localhost');
define('OPUNGA_DB_NAME', 'yourname_opunga');
define('OPUNGA_DB_USER', 'yourname_opungauser');
define('OPUNGA_DB_PASS', 'your-database-password');
```

Then update `php/db.php` so the last lines read:

```php
$configFile = dirname(__DIR__) . '/config.php';

if(file_exists($configFile)){

    require_once $configFile;

    $db_host = defined('OPUNGA_DB_HOST') ? OPUNGA_DB_HOST : $db_host;
    $db_name = defined('OPUNGA_DB_NAME') ? OPUNGA_DB_NAME : $db_name;
    $db_user = defined('OPUNGA_DB_USER') ? OPUNGA_DB_USER : $db_user;
    $db_pass = defined('OPUNGA_DB_PASS') ? OPUNGA_DB_PASS : $db_pass;

}

$conn = @mysqli_connect($db_host, $db_user, $db_pass, $db_name);
```

Because `config.php` sits outside the web root, nobody can read the password over HTTP.

## 3. Upload the files

1. In cPanel open **File Manager** and go to `public_html`.
2. Delete the default `index.html` that cPanel puts there.
3. Upload the contents of the project folder, not the folder itself. The layout should be:

```
public_html/
  .htaccess
  index.php
  login.php
  register.php
  services.php
  cart.php
  checkout.php
  orders.php
  profile.php
  receipt.php
  my_services.php
  admin.php
  admin_dashboard.php
  admin_orders.php
  admin_services.php
  add_product.php
  edit_product.php
  archive/        (blocked by .htaccess, safe to delete)
  css/
  images/
  js/
  php/
  opunga_shop.sql (delete after you import it)
```

**Do not upload:** `archive/`, `opunga_shop.sql`, or the temporary SQL export. Everything in
`archive/` is a retired duplicate page and is already blocked, but deleting it keeps the live
folder clean.

## 4. PHP version

In cPanel open **MultiPHP Manager** and set the domain to PHP 7.4 or 8.0. The code uses
`mysqli` with prepared statements and `password_hash`, both fine on 7.4 and 8.x.

## 5. Turn on HTTPS

1. In cPanel open **SSL/TLS Status** and run **Run AutoSSL** for your domain.
2. Confirm the site loads over `https://`.
3. Open `.htaccess` in File Manager and uncomment the HTTPS block at the bottom:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} !=on
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>
```

Only do this after AutoSSL succeeds, otherwise visitors land on a URL that will not serve.

## 6. Change the admin password

The account in the SQL dump is `opunga@gmail.com`. Sign in, go to your profile, and set a new
password. Then delete `opunga_shop.sql` from `public_html`.

The bundled demo customers (`john@`, `mary@`, `peter@`) all use the password `123456`.
Delete them before going live, they are only there for testing.

## 7. M-PESA

The checkout asks the customer for their M-PESA phone number and sends an STK push prompt to
their phone, so they pay with a PIN. The site never asks for an M-PESA code. The order starts
as `Pending` and becomes `Paid` automatically once Safaricom confirms the money (callback or
status query).

If the Daraja keys below are left empty, checkout tells customers that online M-PESA is
unavailable, so no orders can be placed until you fill them in.

**To turn on automatic STK push confirmation**

1. Register for the Safaricom **Daraja** API and create an app to get a **consumer key** and
   **consumer secret**. For sandbox tests that is all you need: the test shortcode `174379`
   and the published sandbox passkey are filled in automatically. For live payments also set
   `OPUNGA_MPESA_ENV` to `live`, your own till in `OPUNGA_MPESA_TILL`, and the **passkey**
   Safaricom issued for that till number (Daraja app → "Lipa na M-PESA Online Shortcode").
2. Add the values to `config.php` (optionally via `php/config.local.php` on hosts like
   InfinityFree that cannot store files above the web root):

```php
define('OPUNGA_MPESA_ENV', 'sandbox');          // 'sandbox' to test, 'live' for real money
define('OPUNGA_MPESA_CONSUMER_KEY', '');        // paste your Daraja consumer key
define('OPUNGA_MPESA_CONSUMER_SECRET', '');     // paste your Daraja consumer secret
define('OPUNGA_MPESA_PASSKEY', '');             // blank in sandbox; required for live
define('OPUNGA_MPESA_TILL', '');                // blank uses 174379 sandbox / 3461337 live
define('OPUNGA_MPESA_CALLBACK_URL', '');
```

3. If `OPUNGA_MPESA_CALLBACK_URL` is left empty the shop uses the current site address
   automatically (`.../php/mpesa_callback.php`). Set it explicitly if you need a fixed HTTPS
   URL. Add the callback URL to the till in the Safaricom portal for live payments.
4. On hosts whose firewall or proxy blocks inbound Safaricom callbacks (InfinityFree's
   browser-security system is one example) the checkout's status poll still works, because it
   asks Daraja for the payment state instead of waiting for the callback. No change needed.

Flow: customer enters their phone at checkout → STK push prompt appears on their phone →
they enter their M-PESA PIN → the payment resolves via callback or the status poll → order
status `Paid`, cart items for that order are removed, receipt shown. If the prompt is declined
or expires, the order is cancelled, stock is restored, nothing is charged, and the customer is
sent back to their cart to edit it and try again.

## 8. Final checks after upload

- `https://yourdomain/login.php` loads
- Register a new account and it works
- Add to cart and check out, the order appears as `Pending` in Admin → Orders
- `https://yourdomain/php/db.php` returns 403
- `https://yourdomain/opunga_shop.sql` returns 403 or 404
- `https://yourdomain/admin_dashboard.php` while signed out redirects to login
