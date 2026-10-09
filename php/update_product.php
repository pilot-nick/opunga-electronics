<?php

include "auth.php";
include "db.php";

require_admin();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../admin.php");
}

csrf_check();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
$name = isset($_POST['name']) ? trim($_POST['name']) : "";
$category = isset($_POST['category']) ? trim($_POST['category']) : "";
$price = isset($_POST['price']) ? $_POST['price'] : "";
$image = isset($_POST['image']) ? trim($_POST['image']) : "";
$stock = isset($_POST['stock']) ? $_POST['stock'] : "";

function go_back($message)
{
    echo "<script>
    alert(" . json_encode($message) . ");
    window.location='../admin.php';
    </script>";

    exit();
}

if($id < 1){
    go_back("Invalid product.");
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

// Only allow a plain file name, so the image path cannot be redirected
if($image != "" && !preg_match('/^[A-Za-z0-9._-]+$/', $image)){
    go_back("The image name may only contain letters, numbers, dots, dashes and underscores.");
}

$stmt = mysqli_prepare($conn,
    "UPDATE products
     SET name = ?, category = ?, price = ?, image = ?, stock = ?
     WHERE id = ?");

mysqli_stmt_bind_param($stmt, "ssdsi", $name, $category, $price, $image, $stock, $id);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

if(!$ok){
    go_back("The product could not be updated.");
}

safe_redirect("../admin.php?ok=" . rawurlencode("Product updated."));

?>
