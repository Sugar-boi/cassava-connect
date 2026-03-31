<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/security.php';
?>


<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Cassava Connect</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
   <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
   
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">
  <!-- Custom CSS --> 
  <link href="/cassava-connect/assets/css/style.css" rel="stylesheet">
  <!-- Favicon -->
  <link rel="icon" type="image/png" href="/cassava-connect/assets/img/favicon.png">
  <style>
.active-nav {
  background-color: #ffffff !important;
  color: #000000 !important;
  border-radius: 6px;
  padding: 4px 10px !important;
  font-weight: 700;
}
.navbar .nav-link:hover {
  color: #dfe6e9 !important;
}
body{
  background-image: url('cassava_img.jpg');
  background-size: cover; 
}
</style>
</head>

<body  style="background-color: #f9f9f4;">

<?php 
$current_page = basename($_SERVER['PHP_SELF']); 
?>

<nav class="navbar navbar-expand-lg navbar-light" style="background-color: #198754;">
  <div class="container">
    <a class="navbar-brand text-white fw-bold" href="/cassava-connect/public/index.php">
      <img src="/cassava-connect/assets/img/logo.png" alt="Cassava Connect" width="40" height="40" class="me-2 rounded-circle border border-white">
      Cassava Connect
    </a>

    <button class="navbar-toggler bg-light" data-bs-toggle="collapse" data-bs-target="#navmenu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navmenu">
      <ul class="navbar-nav ms-auto align-items-center">

        <!-- ✅ Products -->
        <li class="nav-item">
          <a class="nav-link <?= $current_page == 'products.php' ? 'fw-bold active-nav' : 'text-white' ?>" 
             href="/cassava-connect/public/products.php">
            <i class="fa-solid fa-boxes-stacked me-1"></i> Products
          </a>
        </li>

        <?php if(is_logged_in()): ?>
          <?php if(current_user_role() === 'vendor'): ?>
            <?php
            $stmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM vendor_notifications vn 
                JOIN vendors v ON vn.vendor_id = v.id 
                WHERE v.user_id = ? AND vn.is_read = 0
            ");
            $stmt->execute([current_user_id()]);
            $notif_count = $stmt->fetchColumn();
            ?>

            <!-- ✅ Orders -->
            <li class="nav-item position-relative">
              <a class="nav-link <?= $current_page == 'vendor-orders.php' ? 'fw-bold active-nav' : 'text-white' ?>" 
                 href="/cassava-connect/public/vendor/vendor-orders.php">
                <i class="fa-solid fa-bell me-1"></i> Orders
                <?php if($notif_count > 0): ?>
                  <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                    <?= $notif_count ?>
                  </span>
                <?php endif; ?>
              </a>
            </li>

            <!-- ✅ Vendor Dashboard -->
            <li class="nav-item">
              <a class="nav-link <?= $current_page == 'vendor-dashboard.php' ? 'fw-bold active-nav' : 'text-white' ?>" 
                 href="/cassava-connect/public/dashboard/vendor-dashboard.php">
                <i class="fa-solid fa-chart-line me-1"></i> Vendor Dashboard
              </a>
            </li>
          <?php endif; ?>

          <!-- ✅ Cart -->
          <li class="nav-item position-relative">
            <a class="nav-link <?= $current_page == 'cart.php' ? 'fw-bold active-nav' : 'text-white' ?>" 
               href="/cassava-connect/public/cart.php">
              <i class="fa-solid fa-cart-shopping me-1"></i> Cart
              <?php if(!empty($_SESSION['cart'])): ?>
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                  <?= count($_SESSION['cart']); ?>
                </span>
              <?php endif; ?>
            </a>
          </li>

          <!-- ✅ Logout -->
          <li class="nav-item">
            <a class="nav-link text-white" href="/cassava-connect/public/logout.php">
              <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
            </a>
          </li>

        <?php else: ?>
          <!-- ✅ Guest Links -->
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'login.php' ? 'fw-bold active-nav' : 'text-white' ?>" 
               href="/cassava-connect/public/login.php">
              <i class="fa-solid fa-user me-1"></i> Login
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link <?= $current_page == 'register.php' ? 'fw-bold active-nav' : 'text-white' ?>" 
               href="/cassava-connect/public/register.php">
              <i class="fa-solid fa-user-plus me-1"></i> Register
            </a>
          </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>

<!-- ✅ Active Link Styling -->





<!--<div class="container mt-4">-->

<!-- ✅ Toast Notifications -->
<?php if($m = flash_get()): ?>
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 9999;">
  <div id="toastMessage" class="toast align-items-center text-white bg-success border-0 show" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">
        <?= htmlspecialchars($m) ?>
      </div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>
<?php endif; ?>
