<?php
require_once __DIR__ . '/../includes/header.php';

$redirect_to = $_GET['redirect'] ?? 'products.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user && password_verify($password, $user['password'])){
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['user_name'] = $user['name'];
        flash_set('Login successful');

         // <<< ADD ROLE-BASED REDIRECT HERE >>>
       if ($user['role'] === 'admin') {
    header('Location: admin.php');
} else {
  
    header("Location: $redirect_to"); // redirect to products.php or original page
}
exit;
    } else {
        flash_set('Invalid email or password', 'danger');
    }
}
?>
<a class="nav-link <?= $current_page == 'login.php' ? 'fw-bold text-dark bg-white rounded px-2' : 'text-white' ?>" 
   href="/cassava-connect/public/login.php">
   <i class="fa-solid fa-user me-1"></i> Login
</a>


<div class="container d-flex justify-content-center align-items-center" style="min-height: 80vh;">
  <div class="card shadow-lg p-4 w-100" style="max-width: 400px; width: 100%; border-radius: 20px;">
    <h3 class="text-center text-success mb-4">Login</h3>
    <form method="post">
       <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" name="email" type="email" required>
      </div>

      <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control" type="password" name="password" required>
   <!-- Forgot Password link -->
    <div class="text-end mb-3">
        <a href="forget_password.php" class="text-success small fw-bold">Forgot Password?</a>
    </div>
</div>

      <button class="btn btn-success w-100">Login</button>
    </form>
    <div class="text-center mt-3">
      
      <small>Don't have an account? <a href="register.php" class="text-success fw-bold">Register</a></small>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
