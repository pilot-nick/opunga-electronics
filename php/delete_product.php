<?php

include "auth.php";
include "db.php";

require_admin();

if($_SERVER["REQUEST_METHOD"] != "POST"){
    safe_redirect("../admin.php");
}

csrf_check();

$id = isset($_POST['id']) ? intval($_POST['id']) : 0;

if($id < 1){
    safe_redirect("../admin.php");
}

$stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");

mysqli_stmt_bind_param($stmt, "i", $id);

$ok = mysqli_stmt_execute($stmt);

mysqli_stmt_close($stmt);

// A silent failure here leaves the product in the catalogue while the
// admin has been told it was deleted.
if(!$ok){
    safe_redirect("../admin.php?error=" . rawurlencode("The product could not be deleted. Please try again."));
}

safe_redirect("../admin.php?ok=" . rawurlencode("Product deleted."));

?>
