<?php

include "auth.php";
include "db.php";

require_admin();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../admin.php");
}

csrf_check();

$name = post_trimmed('name');
$category = post_trimmed('category');
$price = post_value('price');
$image = post_trimmed('image');
$stock = post_value('stock');

function go_back($message)
{
    echo "<script>
    alert(" . json_encode($message) . ");
    window.location='../admin.php';
    </script>";

    exit();
}

if($name == "" || $category == ""){
    go_back("Please provide a name and a category.");
}

if(strlen($name) > 100 || strlen($category) > 50){
    go_back("The name or category is too long.");
}

if(!is_numeric($price) || $price < 0 || $price > 99999999.99){
    go_back("Please enter a valid price.");
}

if(!is_numeric($stock) || $stock < 0 || $stock > 1000000){
    go_back("Please enter a valid stock quantity.");
}

$price = number_format((float)$price, 2, '.', '');
$stock = (int)$stock;

if($image != "" && !preg_match('/^[A-Za-z0-9._-]+$/', $image)){
    go_back("The image name may only contain letters, numbers, dots, dashes and underscores.");
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO products(name, category, price, image, stock)
     VALUES(?, ?, ?, ?, ?)");

mysqli_stmt_bind_param($stmt, "ssdsi", $name, $category, $price, $image, $stock);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    go_back("The product could not be added.");
}

safe_redirect("../admin.php");

?>
