<?php
require_once __DIR__ . '/../includes/header.php';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $role = $_POST['role'] === 'vendor' ? 'vendor' : 'buyer';

    if(!$name || !$email || !$password){
        flash_set('Please fill required fields');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if($stmt->fetch()){
            flash_set('Email already registered');
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (name,email,phone,password,role) VALUES (?,?,?,?,?)");
            $stmt->execute([$name,$email,$phone,$hash,$role]);
            $userId = $pdo->lastInsertId();
            if($role === 'vendor'){
                $stmt = $pdo->prepare("INSERT INTO vendors (user_id, business_name, location) VALUES (?,?,?)");
                $stmt->execute([$userId, $name . "'s farm", '']);
            }
            flash_set('Registration successful. You may login.');
            header('Location: login.php');
            exit;
        }
    }
}
?>
<a class="nav-link <?= $current_page == 'register.php' ? 'fw-bold text-dark bg-white rounded px-2' : 'text-white' ?>" 
   href="/cassava-connect/public/register.php">
   <i class="fa-solid fa-user-plus me-1"></i> Register
</a>


<div class="container d-flex justify-content-center align-items-center" style="min-height: 85vh;">
  <div class="card shadow-lg p-4 w-100" style="max-width: 500px; border-radius: 15px;">
    <h3 class="text-center text-success mb-4">Create Account</h3>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">

      <div class="mb-3">
        <label class="form-label">Full name</label>
        <input class="form-control" name="name" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Phone</label>
        <input class="form-control" name="phone">
      </div>

      <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control" type="password" name="password" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Role</label>
        <select name="role" class="form-select">
          <option value="buyer">Buyer</option>
          <option value="vendor">Vendor</option>
        </select>
      </div>

      <button class="btn btn-success w-100">Register</button>
    </form>
    <div class="text-center mt-3">
      <small>Already have an account? <a href="login.php" class="text-success fw-bold">Login</a></small>
    </div>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
