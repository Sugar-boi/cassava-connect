<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$cart = $_SESSION['cart'] ?? [];
if(!$cart){
    flash_set('Cart is empty');
    header('Location: cart.php');
    exit;
}
require_once __DIR__ . '/../includes/security.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    // your normal form processing here...
}


// Group products by vendor
$vendors_in_cart = [];
foreach($cart as $pid => $it) $vendors_in_cart[$it['vendor_id']] = true;
$unique_vendors = array_keys($vendors_in_cart);

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $address = trim($_POST['address']);
    if(!$address){
        flash_set('Please enter a delivery address.');
    } else {
        foreach($unique_vendors as $v_id){
            $order_total = 0;
            foreach($cart as $pid => $it){
                if($it['vendor_id'] == $v_id){
                    $order_total += $it['qty'] * $it['price'];
                }
            }

            // ✅ Insert order
            $stmt = $pdo->prepare("
                INSERT INTO orders (user_id, vendor_id, total_amount, status, created_at)
                VALUES (?, ?, ?, 'Pending Vendor Approval', NOW())
            ");
            $stmt->execute([current_user_id(), $v_id, $order_total]);
            $order_id = $pdo->lastInsertId();

            // ✅ Insert each order item
            $stmt2 = $pdo->prepare("
                INSERT INTO order_items (order_id, product_id, quantity, price)
                VALUES (?, ?, ?, ?)
            ");
            foreach($cart as $pid => $it){
                if($it['vendor_id'] == $v_id){
                    $stmt2->execute([$order_id, $pid, $it['qty'], $it['price']]);
                    // reduce stock
                    $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?")
                        ->execute([$it['qty'], $pid]);
                }
            }

            // ✅ Notify vendor
            $pdo->prepare("
                INSERT INTO vendor_notifications (vendor_id, message)
                VALUES (?, ?)
            ")->execute([$v_id, '📦 You have a new order pending approval.']);
        }

        // ✅ Clear session cart
        unset($_SESSION['cart']);

        flash_set('✅ Order placed successfully! Your vendors will contact you for delivery. Payment is on delivery.');
        header('Location: order-summary.php');
        exit;
    }
}
?>

<h2>Checkout</h2>

<form method="post">
    <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">

  <div class="mb-3">
    <label class="form-label">Delivery address</label>
    <textarea class="form-control" name="address" required></textarea>
  </div>
  <p><strong>Note:</strong> Payment will be made on delivery (Cash or transfer). Vendors will contact you to confirm orders.</p>
  <button class="btn btn-success">Place Order</button>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
