<?php
require_once '../config/db.php';
require_once '../includes/functions.php';

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at >= NOW()");
$stmt->execute([$token]);
$reset = $stmt->fetch();

if (!$reset) {
    die("Invalid or expired token.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'];
    $hash = password_hash($new_password, PASSWORD_DEFAULT);

    // Update user password
    $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hash, $reset['user_id']]);

    // Delete used token
    $stmt = $pdo->prepare("DELETE FROM password_resets WHERE id = ?");
    $stmt->execute([$reset['id']]);

    flash_set("Password successfully reset. You can now log in.");
    header("Location: login.php");
    exit;
}
?>
<h3>Reset Password</h3>
<form method="post">
    <input type="password" name="password" placeholder="New Password" required>
    <button type="submit">Reset Password</button>
</form>