<?php
require_once __DIR__ . '/../includes/header.php';

$token = $_GET['token'] ?? '';
$stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token=? AND reset_expires > NOW()");
$stmt->execute([$token]);
$user = $stmt->fetch();

if(!$user){
    exit('Invalid or expired token.');
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $pass = $_POST['password'];
    if(strlen($pass) < 6){
        flash_set('Password must be at least 6 characters.');
    } else {
        $hash = password_hash($pass, PASSWORD_DEFAULT);
        $pdo->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expires=NULL WHERE id=?")
            ->execute([$hash, $user['id']]);
        flash_set('Password updated. You can now log in.');
        header('Location: login.php');
        exit;
    }
}
?>
<h2>Reset Password</h2>
<form method="post">
  <div class="mb-3">
    <label>New Password</label>
    <input type="password" name="password" class="form-control" required>
  </div>
  <button class="btn btn-success">Update Password</button>
</form>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
