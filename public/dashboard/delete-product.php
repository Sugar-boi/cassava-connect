<?php
require_once __DIR__ . '/../../includes/header.php';
require_once __DIR__ . '/../../includes/security.php';

require_login();

if (current_user_role() !== 'vendor') {
    flash_set('Access denied.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $product_id = intval($_POST['product_id']);

    // ✅ Get vendor ID
    $stmt = $pdo->prepare("SELECT id FROM vendors WHERE user_id = ?");
    $stmt->execute([current_user_id()]);
    $vendor = $stmt->fetch();

    if (!$vendor) {
        flash_set('Vendor profile not found.');
        header('Location: vendor-dashboard.php');
        exit;
    }

    $vendor_id = $vendor['id'];

    // ✅ Confirm ownership before deleting
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$product_id, $vendor_id]);
    $product = $stmt->fetch();

    if ($product) {
        // Delete product image
        if (!empty($product['image'])) {
            $imgPath = __DIR__ . '/../../' . ltrim($product['image'], '/');
            if (file_exists($imgPath)) unlink($imgPath);
        }

        // Delete product record
        $pdo->prepare("DELETE FROM products WHERE id = ? AND vendor_id = ?")
            ->execute([$product_id, $vendor_id]);

        flash_set('✅ Product deleted successfully.');
    } else {
        flash_set('❌ You do not have permission to delete this product.');
    }

    header('Location: vendor-dashboard.php');
    exit;
}
?>
