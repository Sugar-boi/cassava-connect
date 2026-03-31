<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$user_id = current_user_id();

$stmt = $pdo->prepare("
  SELECT o.id, o.total_amount, o.status, o.created_at, v.business_name
  FROM orders o
  JOIN vendors v ON o.vendor_id = v.id
  WHERE o.user_id = ?
  ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>

<h2>Order Summary</h2>

<?php if (!$orders): ?>
  <p>You have no orders yet.</p>
<?php else: ?>
  <table class="table table-bordered">
    <thead class="table-success">
      <tr>
        <th>Vendor</th>
        <th>Total (₦)</th>
        <th>Status</th>
        <th>Date</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($orders as $o): ?>
      <tr>
        <td><?=$o['business_name']?></td>
        <td><?=number_format($o['total_amount'],2)?></td>
        <td><?=$o['status']?></td>
        <td><?=date('d M Y H:i', strtotime($o['created_at']))?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
