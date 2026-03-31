<?php
require_once __DIR__ . '/../../includes/header.php';
if (current_user_role() !== 'vendor') {
    flash_set('Access denied.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}


if (!isset($pdo)) {
    exit('Database connection ($pdo) not found.');
}

require_login();

// Only vendors can access
if (current_user_role() !== 'vendor') {
    flash_set('Access denied.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}

// Get vendor ID
$stmt = $pdo->prepare("SELECT id FROM vendors WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$vendor = $stmt->fetch();

if (!$vendor) {
    exit('Vendor profile not found.');
}

$vendor_id = $vendor['id'];

// Allowed statuses matching your DB enum exactly
$allowed_statuses = [
    'Pending Vendor Approval',
    'Processing',
    'Delivered',
    'Cancelled'
];

// Handle order status update
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    if (!in_array($new_status, $allowed_statuses, true)) {
        exit('Invalid status value.');
    }

    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ? AND vendor_id = ?");
    $stmt->execute([$new_status, $order_id, $vendor_id]);

    flash_set("Order #$order_id updated to $new_status.");
    header('Location: vendor-orders.php');
    exit;
}

// Fetch vendor orders
$stmt = $pdo->prepare("
    SELECT o.*, u.name AS buyer_name, u.email
    FROM orders o
    JOIN users u ON o.buyer_id = u.id
    WHERE o.vendor_id = ?
    ORDER BY o.created_at DESC
");
$stmt->execute([$vendor_id]);
$orders = $stmt->fetchAll();

// Mark vendor notifications as read
$pdo->prepare("
    UPDATE vendor_notifications 
    SET is_read = 1 
    WHERE vendor_id = ? AND is_read = 0
")->execute([$vendor_id]);
?>

<h2 class="mt-4 mb-3 text-success">📦 Vendor Orders</h2>

<?php if (empty($orders)): ?>
  <div class="alert alert-info">No orders yet.</div>
<?php else: ?>
  <table class="table table-bordered align-middle shadow-sm">
    <thead class="table-success">
      <tr>
        <th>#</th>
        <th>Buyer</th>
        <th>Total (₦)</th>
        <th>Status</th>
        <th>Payment</th>
        <th>Placed</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
        <tr>
          <td><?= htmlspecialchars($o['id']) ?></td>
          <td>
            <?= htmlspecialchars($o['buyer_name']) ?><br>
            <small class="text-muted"><?= htmlspecialchars($o['email']) ?></small>
          </td>
          <td>₦<?= number_format($o['total_amount'], 2) ?></td>
          <td><span class="badge bg-info"><?= htmlspecialchars($o['status']) ?></span></td>
          <td><?= htmlspecialchars($o['payment_status']) ?></td>
          <td><?= htmlspecialchars($o['created_at']) ?></td>
          <td>
            <form method="post" class="d-flex">
              <input type="hidden" name="order_id" value="<?= (int)$o['id'] ?>">
              <select name="status" class="form-select form-select-sm me-2" style="width:auto;">
                <?php foreach ($allowed_statuses as $status): ?>
                  <option value="<?= htmlspecialchars($status) ?>" <?= $o['status'] === $status ? 'selected' : '' ?>>
                    <?= htmlspecialchars($status) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <button class="btn btn-sm btn-primary" name="update_status">Update</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>