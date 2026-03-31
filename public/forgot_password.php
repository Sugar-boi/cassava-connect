<?php
require_once '../config/db.php';
require_once '../includes/functions.php'; // for flash_set, csrf, etc.

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // Generate secure token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

        // Save token in DB
        $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
        $stmt->execute([$user['id'], $token, $expires]);

        // Send email with reset link
        $reset_link = "http://localhost/cassava-connect/public/reset_password.php?token=$token";
        // Use mail() or PHPMailer here
        // Example:
        // mail($email, "Password Reset", "Click here to reset: $reset_link");

        flash_set("Password reset link sent to $email (check email).");
    } else {
        flash_set("No account found with that email.");
    }
}
?>
<h3>Forgot Password</h3>
<form method="post">
    <input type="email" name="email" placeholder="Your email" required>
    <button type="submit">Send Reset Link</button>
</form>