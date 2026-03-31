<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

if (current_user_role() !== 'buyer') {
    flash_set('Only buyers can add to cart.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
    $product_id = (int) $_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? max(1, intval($_POST['quantity'])) : 1;

    // ✅ Fetch product info from database (including vendor_id)
    $stmt = $pdo->prepare("SELECT id, name, price, unit, vendor_id FROM products WHERE id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Double-check vendor_id is valid
        if (empty($product['vendor_id'])) {
            flash_set('⚠️ Product has no vendor assigned. Please contact admin.');
            header('Location: /cassava-connect/public/cart.php');
            exit;
        }

        $item = [
            'id' => $product['id'],
            'name' => $product['name'],
            'price' => $product['price'],
            'unit' => $product['unit'],
            'quantity' => $quantity,
            'vendor_id' => $product['vendor_id'] // ✅ This will now be stored
        ];

        // Initialize cart if not exists
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];

        // If already in cart, increase quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]['quantity'] += $quantity;
        } else {
            $_SESSION['cart'][$product_id] = $item;
        }

        flash_set('✅ Added to cart successfully!');
    } else {
        flash_set('Product not found.');
    }
}

header('Location: /cassava-connect/public/cart.php');
exit;
?>