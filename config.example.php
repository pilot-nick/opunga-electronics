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
