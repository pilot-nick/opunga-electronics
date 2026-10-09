<?php

require_once __DIR__ . "/brand.php";

if(!function_exists("start_secure_session")){

    function start_secure_session()
    {
        if(session_status() == PHP_SESSION_ACTIVE){
            return;
        }

        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
            || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

        session_set_cookie_params(
            0,
            "/",
            "",
            $https,
            true
        );

        session_name("OPUNGASESSID");
        session_start();

        if(empty($_SESSION['created'])){
            $_SESSION['created'] = time();
        }

        // Rotate the session id periodically to limit fixation windows
        if(time() - $_SESSION['created'] > 1800){
            session_regenerate_id(true);
            $_SESSION['created'] = time();
        }
    }

}

if(!function_exists("require_login")){

    function require_login()
    {
        start_secure_session();

        if(!isset($_SESSION['user_id'])){
            header("Location: login.php");
            exit();
        }
    }

}

if(!function_exists("require_admin")){

    function require_admin()
    {
        start_secure_session();

        // Not signed in at all: send them to the login form
        if(!isset($_SESSION['user_id'])){
            header("Location: login.php");
            exit();
        }

        // Signed in but not an admin: refuse without saying anything useful
        if(!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true){
            http_response_code(403);
            die("Administrator access only.");
        }
    }

}

if(!function_exists("is_staff")){

    // Anyone logged in with staff or admin privileges
    function is_staff()
    {
        return (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true)
            || (isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true);
    }

}

if(!function_exists("require_staff")){

    function require_staff()
    {
        start_secure_session();

        // Not signed in at all: send them to the staff login form
        if(!isset($_SESSION['user_id'])){
            header("Location: staff_login.php");
            exit();
        }

        // Signed in but not staff or admin: refuse
        if(!is_staff()){
            http_response_code(403);
            die("Staff access only.");
        }
    }

}

if(!function_exists("require_customer")){

    // Storefront pages are only for customers; admins go to the admin panel
    // and staff go to the staff panel
    function require_customer()
    {
        if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true){
            safe_redirect("admin_dashboard.php");
        }

        if(isset($_SESSION['is_staff']) && $_SESSION['is_staff'] === true){
            safe_redirect("staff_dashboard.php");
        }
    }

}

if(!function_exists("admin_nav_link")){

    // The nav bar only offers the admin panel to signed in administrators
    function admin_nav_link()
    {
        if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true){
            echo '<a href="admin_dashboard.php">Admin Panel</a>';
        }
    }

}

if(!function_exists("post_value")){

    // Reads a submitted field without tripping an undefined index warning
    // when it is missing, and never hands back an array.
    function post_value($key, $default = "")
    {
        if(!isset($_POST[$key]) || is_array($_POST[$key])){
            return $default;
        }

        return $_POST[$key];
    }

}

if(!function_exists("post_trimmed")){

    // Same as post_value() with surrounding whitespace removed
    function post_trimmed($key, $default = "")
    {
        return trim(post_value($key, $default));
    }

}

if(!function_exists("login_throttle")){

    // Failed sign in attempts are counted in the session, so a password can
    // be guessed slowly instead of a few hundred times a minute.
    function login_throttle()
    {
        start_secure_session();

        $now = time();

        // Forget the history once the oldest attempt is older than the window
        if(isset($_SESSION['login_tries']) && is_array($_SESSION['login_tries'])){
            $recent = array();

            foreach($_SESSION['login_tries'] as $stamp){
                if($now - $stamp < 900){ $recent[] = $stamp; }
            }

            $_SESSION['login_tries'] = $recent;
        }

        $attempts = isset($_SESSION['login_tries']) ? count($_SESSION['login_tries']) : 0;

        if($attempts >= 8){
            http_response_code(429);
            die("Too many failed sign in attempts. Please wait a few minutes and try again.");
        }
    }

}

if(!function_exists("login_throttle_fail")){

    // Record one failed attempt
    function login_throttle_fail()
    {
        start_secure_session();

        if(!isset($_SESSION['login_tries']) || !is_array($_SESSION['login_tries'])){
            $_SESSION['login_tries'] = array();
        }

        $_SESSION['login_tries'][] = time();
    }

}

if(!function_exists("login_throttle_reset")){

    // A successful sign in clears the counter
    function login_throttle_reset()
    {
        unset($_SESSION['login_tries']);
    }

}

if(!function_exists("panel_page")){

    // Staff and admins share handlers such as php/update_order.php, so each
    // one has to be sent back to the panel they are actually allowed to
    // open, otherwise staff land on an administrator page and get a 403.
    function panel_page($adminFile, $staffFile)
    {
        if(isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true){
            return "../" . $adminFile;
        }

        return "../" . $staffFile;
    }

}

if(!function_exists("flash_message")){

    // Renders the one-shot feedback a handler passed along after a
    // write, e.g. php/update_order.php -> admin_orders.php?error=...
    // The text is escaped, so a handler can never inject markup here.
    function flash_message()
    {
        $out = '';

        if(isset($_GET['error']) && $_GET['error'] !== ''){
            $out .= '<div class="alert alert--danger">'
                . htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8')
                . '</div>';
        }

        if(isset($_GET['ok']) && $_GET['ok'] !== ''){
            $out .= '<div class="alert alert--success">'
                . htmlspecialchars($_GET['ok'], ENT_QUOTES, 'UTF-8')
                . '</div>';
        }

        return $out;
    }

}

if(!function_exists("csrf_token")){

    function csrf_token()
    {
        start_secure_session();

        if(empty($_SESSION['csrf_token'])){
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

}

if(!function_exists("csrf_field")){

    function csrf_field()
    {
        return '<input type="hidden" name="csrf_token" value="' . htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') . '">';
    }

}

if(!function_exists("csrf_check")){

    function csrf_check()
    {
        start_secure_session();

        $sent = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : "";

        if(empty($_SESSION['csrf_token']) || !is_string($sent) || !hash_equals($_SESSION['csrf_token'], $sent)){
            http_response_code(403);
            die("Your session expired. Please go back, reload the page and try again.");
        }

        return true;
    }

}

if(!function_exists("safe_redirect")){

    // Only ever redirects to a path inside this site. Anything absolute,
    // protocol relative, or carrying a scheme or backslash is refused, so
    // a future safe_redirect($_GET['next']) cannot become an open redirect.
    function safe_redirect($url)
    {
        $url = (string)$url;

        $isInternal = $url !== ''
            && !preg_match('#^[a-zA-Z][a-zA-Z0-9+.\-]*:#', $url)
            && strpos($url, '//') !== 0
            && strpos($url, '\\') === false
            && strpos($url, "\r") === false
            && strpos($url, "\n") === false;

        if(!$isInternal){
            error_log("Opunga: refused a redirect outside the site: " . $url);

            $url = "../index.php";
        }

        header("Location: " . $url);
        exit();
    }

}

?>
