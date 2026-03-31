<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/mailer.php'; // include the new mailer

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        flash_set('Invalid email address.');
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

            $stmt = $pdo->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
            $stmt->execute([$user['id'], $token, $expires]);

            send_password_reset($email, $token);
        }
        flash_set('If that email exists, a reset link has been sent.');
    }
}
?>

<h3>Forgot Password</h3>
<form method="post">
    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" placeholder="Your email" required>
    </div>
    <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>
</form>