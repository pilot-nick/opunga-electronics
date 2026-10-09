<?php

include "auth.php";
include "db.php";

require_login();

require_customer();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../services.php");
}

csrf_check();

$service_id = (int)post_value('service_id', '0');
$device = post_trimmed('device');
$preferred_date = post_value('preferred_date');
$notes = post_trimmed('notes');

if($service_id < 1 || $device == ""){
    header("Location: ../services.php?error=Please+choose+a+service+and+describe+the+device.");
    exit();
}

if(strlen($device) > 100){
    header("Location: ../services.php?error=The+device+name+is+too+long.");
    exit();
}

if(strlen($notes) > 500){
    header("Location: ../services.php?error=Your+notes+are+too+long.");
    exit();
}

$check = mysqli_prepare($conn, "SELECT id FROM services WHERE id = ? AND active = 1 LIMIT 1");

mysqli_stmt_bind_param($check, "i", $service_id);
mysqli_stmt_execute($check);

$checkResult = mysqli_stmt_get_result($check);

$available = $checkResult ? mysqli_fetch_assoc($checkResult) !== null : false;

mysqli_stmt_close($check);

if(!$available){
    header("Location: ../services.php?error=That+service+is+no+longer+available.");
    exit();
}

// Validate the date if one was given
$date = null;

if($preferred_date != ""){

    $time = strtotime($preferred_date);

    if($time === false || date("Y-m-d", $time) != $preferred_date){
        header("Location: ../services.php?error=Please+enter+a+valid+preferred+date.");
        exit();
    }

    if($time < strtotime(date("Y-m-d"))){
        header("Location: ../services.php?error=Please+choose+a+date+that+is+not+in+the+past.");
        exit();
    }

    $date = $preferred_date;
}

$user_id = (int)$_SESSION['user_id'];

$stmt = mysqli_prepare($conn,
    "INSERT INTO service_bookings(user_id, service_id, device, preferred_date, notes)
     VALUES(?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt, "iisss", $user_id, $service_id, $device, $date, $notes);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    header("Location: ../services.php?error=We+could+not+save+your+booking.+Please+try+again.");
    exit();
}

$booking_id = mysqli_insert_id($conn);

header("Location: ../services.php?booked=$booking_id");

exit();

?>
