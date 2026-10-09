<?php

include "auth.php";
include "db.php";

require_admin();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../add_staff.php");
}

csrf_check();

$fullname = post_trimmed('fullname');
$email = post_trimmed('email');
$phone = post_trimmed('phone');
$password = post_value('password');
$confirm = post_value('confirm_password');

function fail($message)
{
    echo "<script>
    alert(" . json_encode($message) . ");
    window.location='../add_staff.php';
    </script>";

    exit();
}

if($fullname == "" || $email == "" || $password == ""){
    fail("Please fill in every required field.");
}

if(strlen($fullname) > 100){
    fail("The name is too long.");
}

if(!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 100){
    fail("Please enter a valid email address.");
}

if($phone != "" && !preg_match('/^[0-9+\- ]{7,20}$/', $phone)){
    fail("Please enter a valid phone number.");
}

if(strlen($password) < 8){
    fail("The password must be at least 8 characters.");
}

if($password !== $confirm){
    fail("The two passwords do not match.");
}

$check = mysqli_prepare($conn, "SELECT id FROM users WHERE email = ? LIMIT 1");

mysqli_stmt_bind_param($check, "s", $email);
mysqli_stmt_execute($check);

$checkResult = mysqli_stmt_get_result($check);

$exists = $checkResult ? mysqli_fetch_assoc($checkResult) !== null : false;

mysqli_stmt_close($check);

if($exists){
    fail("That email is already in use. Please choose another one.");
}

$hash = password_hash($password, PASSWORD_DEFAULT);

$stmt = mysqli_prepare($conn,
    "INSERT INTO users(fullname, email, password, phone, role)
     VALUES(?, ?, ?, ?, 'staff')");

mysqli_stmt_bind_param($stmt, "ssss", $fullname, $email, $hash, $phone);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    fail("The staff account could not be created.");
}

echo "<script>
alert('Staff account created. They can now log in on the staff login page.');
window.location='../add_staff.php';
</script>";

?>
