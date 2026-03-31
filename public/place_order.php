<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

if (current_user_role() !== 'buyer') {
    flash_set('Only buyers can place orders.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}

if (empty($_SESSION['cart'])) {
    flash_set('Your cart is empty.');
    header('Location: /cassava-connect/public/cart.php');
    exit;
}

$buyer_id = current_user_id();
$delivery_address = $_POST['delivery_address'] ?? '';

// ✅ Begin transaction
$pdo->beginTransaction();

try {
    foreach ($_SESSION['cart'] as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO orders (buyer_id, vendor_id, total_amount, payment_method, payment_status, status, delivery_address, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $buyer_id,
            $item['vendor_id'], // ✅ now valid
            $item['price'] * $item['qty'], // ✅ use 'qty' instead of 'quantity'
            'Cash',              // or 'Online' if applicable
            'Unpaid',            // must match your ENUM('Paid','Unpaid')
            'Pending',           // current status
            $delivery_address
        ]);

         $order_id = $pdo->lastInsertId(); // get the new order id

    // Insert notification for vendor
    $stmt = $pdo->prepare("
        INSERT INTO vendor_notifications (vendor_id, message, is_read, created_at)
        VALUES (?, ?, 0, NOW())
    ");
    $message = "New order #$order_id placed for your product.";
    $stmt->execute([$item['vendor_id'], $message]);
    }

    $pdo->commit();

    // Clear cart after placing order
    unset($_SESSION['cart']);

    flash_set('✅ Order placed successfully!');
    header('Location: /cassava-connect/public/orders.php');
    exit;

} catch (Exception $e) {
    $pdo->rollBack();
    flash_set('Error placing order: ' . $e->getMessage());
    header('Location: /cassava-connect/public/cart.php');
    exit;
}
