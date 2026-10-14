<?php
/**
 * Copy this file to the folder ABOVE your public_html folder and rename it
 * to config.php, then fill in your real cPanel database details.
 *
 *     /home/yourname/config.php        <- the file lives here
 *     /home/yourname/public_html/...   <- the shop is here
 *
 * Because it sits outside the web root, nobody can read the password over HTTP.
 * Delete this template once you have made your own copy.
 */

define('OPUNGA_DB_HOST', 'localhost');
define('OPUNGA_DB_NAME', 'yourname_opunga');
define('OPUNGA_DB_USER', 'yourname_opungauser');
define('OPUNGA_DB_PASS', 'paste-your-database-password-here');

/*
 * M-PESA Daraja (STK push).
 *
 * Checkout asks the customer for their M-PESA phone number and sends a
 * payment prompt to it. Fill in the consumer key and secret below to enable
 * it; until then checkout tells customers that online M-PESA is unavailable.
 * Get the key/secret from the Daraja portal.
 *
 * In sandbox mode the test shortcode (174379) and the published sandbox
 * passkey are filled in automatically, so a sandbox test only needs the key
 * and secret below. For live payments set ENV to 'live' and provide your own
 * till (OPUNGA_MPESA_TILL) and the passkey Safaricom issued for it.
 */

define('OPUNGA_MPESA_ENV', 'sandbox');
define('OPUNGA_MPESA_CONSUMER_KEY', '');
define('OPUNGA_MPESA_CONSUMER_SECRET', '');
define('OPUNGA_MPESA_PASSKEY', '');
define('OPUNGA_MPESA_TILL', '');
define('OPUNGA_MPESA_CALLBACK_URL', '');
