<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

if (current_user_role() !== 'buyer') {
  flash_set('Access denied.');
  header('Location: /cassava-connect/public/index.php');
  exit;
}

$buyer_id = current_user_id();

// ✅ Fetch all orders made by this buyer
$stmt = $pdo->prepare("
  SELECT o.*, v.business_name 
  FROM orders o
  JOIN vendors v ON o.vendor_id = v.id
  WHERE o.buyer_id = ?
  ORDER BY o.created_at DESC
");
$stmt->execute([$buyer_id]);
$orders = $stmt->fetchAll();
?>

<div class="container my-5">
  <h2 class="text-success mb-4">📦 My Orders</h2>

  <?php if (empty($orders)): ?>
    <div class="alert alert-info">You haven’t placed any orders yet.</div>
  <?php else: ?>
    <div class="table-responsive">
      <table class="table table-bordered align-middle">
        <thead class="table-success">
          <tr>
            <th>Order ID</th>
            <th>Vendor</th>
            <th>Total (₦)</th>
            <th>Payment</th>
            <th>Status</th>
            <th>Delivery Address</th>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($orders as $o): ?>
            <tr>
              <td>#<?= htmlspecialchars($o['id']) ?></td>
              <td><?= htmlspecialchars($o['business_name']) ?></td>
              <td><?= number_format($o['total_amount'], 2) ?></td>
              <td>
                <span class="badge <?= $o['payment_status'] === 'Paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                  <?= htmlspecialchars($o['payment_status']) ?>
                </span>
              </td>
              <td>
                <span class="badge bg-info text-dark"><?= htmlspecialchars($o['status']) ?></span>
              </td>
              <td><?= htmlspecialchars($o['delivery_address']) ?></td>
              <td><?= date('M d, Y', strtotime($o['created_at'])) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
