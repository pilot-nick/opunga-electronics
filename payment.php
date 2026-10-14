<?php

include "php/auth.php";
include "php/db.php";
include "php/mpesa.php";

require_login();

$user_id = (int)$_SESSION['user_id'];

$order_id = isset($_GET['order']) ? intval($_GET['order']) : 0;

if($order_id < 1){
    safe_redirect("index.php");
}

$stmt = mysqli_prepare($conn,
    "SELECT orders.id, orders.status, orders.total,
            payments.id AS payment_id, payments.mpesa_code
     FROM orders
     LEFT JOIN payments ON payments.order_id = orders.id
     WHERE orders.id = ? AND orders.user_id = ?
     LIMIT 1");

mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);

$row = mysqli_stmt_get_result($stmt)->fetch_assoc();

mysqli_stmt_close($stmt);

if(!$row){
    safe_redirect("orders.php");
}

// Already paid? Straight to the receipt.
if($row['status'] === 'Paid'){
    safe_redirect("receipt.php?order=" . $order_id);
}

$code = $row['mpesa_code'];

$liveStk = $row['payment_id'] && mpesa_enabled() && mpesa_has_live_stk($code);

$manualAllowed = $row['payment_id']
    && ($row['status'] === 'Pending' || $row['status'] === 'Processing');

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Pay for Order #<?php echo (int)$order_id; ?> - Opunga Cyber and Electronics</title>

<?php echo brand_favicon_html(); ?>
<link rel="stylesheet" href="css/style.css">

</head>

<body class="page">

<header class="topbar">

<div class="topbar-inner">

<a class="brand" href="index.php"><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics</a>

<span class="topbar-welcome">Pay for Order #<?php echo (int)$order_id; ?></span>

</div>

</header>

<nav class="nav">

<div class="nav-list">

<a href="index.php">Home</a>

<a href="cart.php">Cart</a>

<a href="checkout.php">Checkout</a>

<a href="orders.php">My Orders</a>

<a href="services.php">Cyber Services</a>

<a href="my_services.php">My Bookings</a>

<a href="profile.php">My Profile</a>

<span class="nav-spacer"></span>

<?php admin_nav_link(); ?>

<a href="php/logout.php">Logout</a>

</div>

</nav>

<div class="container container--mid">

<h1 class="page-title">Pay for Order #<?php echo (int)$order_id; ?></h1>

<div class="total-box">

<h2>Total Due</h2>

<span class="total-amount">KSh <?php echo number_format($row['total']); ?></span>

</div>

<?php if($liveStk){ ?>

<div class="panel" id="stk-panel">

<h2>Waiting for M-PESA</h2>

<div class="pay-status" id="pay-status">

<p>We have sent an M-PESA prompt to your phone.</p>

<p>Enter your M-PESA PIN to authorise the payment of KSh <?php echo number_format($row['total']); ?>.</p>

<p class="hint">This page updates automatically as soon as Safaricom confirms the payment.</p>

</div>

<div class="actions" style="margin-top:12px;">

<button type="button" class="btn" id="check-now">Check Now</button>

</div>

</div>

<?php } ?>

<?php if($manualAllowed){ ?>

<div class="panel" style="margin-top:20px;">

<h2>Need to Change Your Cart?</h2>

<p class="hint">Cancel this order and the stock is put back, then edit your cart and check out
again. Nothing has been charged.</p>

<form action="php/cancel_order.php" method="POST" onsubmit="return confirm('Cancel order #<?php echo (int)$order_id; ?> and edit your cart? Nothing has been charged.');">

<?php echo csrf_field(); ?>

<input type="hidden" name="order_id" value="<?php echo (int)$order_id; ?>">

<button type="submit" class="btn btn--danger">Cancel &amp; Edit Cart</button>

</form>

</div>

<?php } ?>

</div>

<footer class="footer">

<h3><?php echo brand_logo_html("brand-logo"); ?> Opunga Cyber and Electronics Shop</h3>

<p>&#128222; Contact: 0700335569</p>

<p>&#128205; M-PESA Till Number: 3461337</p>

</footer>

<script>
(function(){
    var ORDER_ID = <?php echo (int)$order_id; ?>;
    var live = <?php echo $liveStk ? 'true' : 'false'; ?>;
    var polls = 0;
    var MAX_POLLS = 30;
    var timer = null;

    var statusEl = document.getElementById('pay-status');
    var checkBtn = document.getElementById('check-now');

    function setMessage(html){
        if(statusEl){
            statusEl.innerHTML = html;
        }
    }

    function stopPolling(){
        if(timer !== null){
            clearInterval(timer);
            timer = null;
        }
        if(checkBtn){
            checkBtn.disabled = false;
        }
    }

    function showTimeout(){
        stopPolling();
        setMessage('<p>Your M-PESA prompt may have expired.</p>' +
            '<p>Enter the code from your SMS below if you already paid, or use ' +
            '<strong>Cancel &amp; Edit Cart</strong> to change your order and try again.</p>');
    }

    function pollStatus(){
        if(polls >= MAX_POLLS){
            showTimeout();
            return;
        }

        polls++;

        if(checkBtn){
            checkBtn.disabled = true;
        }

        fetch('php/payment_status.php?order=' + ORDER_ID, { cache: 'no-store' })
            .then(function(r){ return r.json(); })
            .then(function(data){
                if(data && data.status === 'paid'){
                    window.location = 'receipt.php?order=' + ORDER_ID;
                    return;
                }

                if(data && data.status === 'cancelled'){
                    stopPolling();
                    var msg = data.message || 'The M-PESA payment was not completed and the order was cancelled.';
                    window.location = 'cart.php?error=' + encodeURIComponent(msg + ' You can edit your cart and try again.');
                    return;
                }

                if(data && data.status === 'manual'){
                    stopPolling();
                    return;
                }

                if(checkBtn){
                    checkBtn.disabled = false;
                }
            })
            .catch(function(){
                if(checkBtn){
                    checkBtn.disabled = false;
                }
            });
    }

    if(live && statusEl){
        pollStatus();
        timer = setInterval(pollStatus, 4000);
    }

    if(checkBtn){
        checkBtn.addEventListener('click', function(){
            pollStatus();
        });
    }
})();
</script>

</body>

</html>