<?php
require_once __DIR__ . '/../../includes/header.php';
require_login();
if(current_user_role() !== 'vendor'){ header('Location: /cassava-connect/public/index.php'); exit; }

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $order_id = intval($_POST['order_id']);
    $action = $_POST['action'] ?? '';

    // ensure vendor owns the order
    $stmt = $pdo->prepare("SELECT v.id as vendor_id FROM orders o JOIN vendors v ON o.vendor_id = v.id WHERE o.id = ?");
    $stmt->execute([$order_id]);
    $row = $stmt->fetch();
    if(!$row){ flash_set('Order not found'); header('Location: vendor-dashboard.php'); exit; }

    $vendor_id = $row['vendor_id'];
    $stmt = $pdo->prepare("SELECT user_id FROM vendors WHERE id = ?");
    $stmt->execute([$vendor_id]);
    $vuser = $stmt->fetchColumn();
    if($vuser != current_user_id()){
        flash_set('Unauthorized');
        header('Location: vendor-dashboard.php');
        exit;
    }

    if($action === 'accept'){
        $pdo->prepare("UPDATE orders SET status='accepted' WHERE id = ?")->execute([$order_id]);
        flash_set('Order accepted');
    } elseif($action === 'ship'){
        $pdo->prepare("UPDATE orders SET status='shipped' WHERE id = ?")->execute([$order_id]);
        flash_set('Order marked as shipped');
    } elseif($action === 'delivered_paid'){
        $pdo->prepare("UPDATE orders SET status='delivered', payment_status='paid' WHERE id = ?")->execute([$order_id]);
        flash_set('Order marked delivered & paid');
    }
}
header('Location: vendor-dashboard.php');
exit;
