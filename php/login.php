<?php

include "auth.php";
include "db.php";

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../login.php");
}

csrf_check();

login_throttle();

$email = isset($_POST['email']) ? trim($_POST['email']) : "";
$password = isset($_POST['password']) ? $_POST['password'] : "";

$stmt = mysqli_prepare($conn, "SELECT id, fullname, email, password, role FROM users WHERE email = ? LIMIT 1");

mysqli_stmt_bind_param($stmt, "s", $email);

mysqli_stmt_execute($stmt);

$result = mysqli_stmt_get_result($stmt);

$user = $result ? mysqli_fetch_assoc($result) : null;

mysqli_stmt_close($stmt);

// When the email does not exist we still run a hash comparison against a
// throwaway hash, so the reply time does not reveal which emails are registered.
if(!$user){
    password_verify($password, '$2y$10$usesomesillystringfore7hnbRJHxXVLeakoG8K30oukPsA.ztMG');
}

if(!$user || !password_verify($password, $user['password']) || $user['role'] !== 'customer'){

    login_throttle_fail();

    echo "<script>
    alert('Incorrect email or password.');
    window.location='../login.php';
    </script>";

    exit();
}

login_throttle_reset();

start_secure_session();

session_regenerate_id(true);

$_SESSION['user_id'] = $user['id'];
$_SESSION['fullname'] = $user['fullname'];
$_SESSION['email'] = $user['email'];
$_SESSION['is_admin'] = false;
$_SESSION['is_staff'] = false;

safe_redirect("../index.php");

?>
