<?php

include "auth.php";
include "db.php";

require_login();

require_customer();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../profile.php");
}

csrf_check();

$user_id = (int)$_SESSION['user_id'];

$fullname = post_trimmed('fullname');
$email = post_trimmed('email');
$phone = post_trimmed('phone');
$address = post_trimmed('address');

function go_back($message)
{
    echo "<script>
    alert(" . json_encode($message) . ");
    window.location='../profile.php';
    </script>";

    exit();
}

if($fullname == "" || $email == ""){
    go_back("Your name and email are required.");
}

if(strlen($fullname) > 100){
    go_back("Your name is too long.");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100){
    go_back("Please enter a valid email address.");
}

if($phone != "" && !preg_match('/^[0-9+\- ]{7,20}$/', $phone)){
    go_back("Please enter a valid phone number.");
}

if(strlen($address) > 1000){
    go_back("Your address is too long.");
}

$check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? AND id <> ? LIMIT 1");

mysqli_stmt_bind_param($check, "si", $email, $user_id);
mysqli_stmt_execute($check);

$checkResult = mysqli_stmt_get_result($check);

$taken = $checkResult ? mysqli_fetch_assoc($checkResult) !== null : false;

mysqli_stmt_close($check);

if($taken){
    go_back("That email is already used by another account.");
}

$stmt = mysqli_prepare($conn,
    "UPDATE users
     SET fullname = ?, email = ?, phone = ?, address = ?
     WHERE id = ?");

mysqli_stmt_bind_param($stmt, "ssssi", $fullname, $email, $phone, $address, $user_id);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    go_back("Your profile could not be saved.");
}

$_SESSION['fullname'] = $fullname;
$_SESSION['email'] = $email;

safe_redirect("../profile.php");

?>
