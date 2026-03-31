<?php
require_once __DIR__ . '/../includes/header.php';
require_login();

$cart = $_SESSION['cart'] ?? [];

if (empty($cart)) {
  echo "<div class='container my-5'><div class='alert alert-info'>🛒 Your cart is empty.</div></div>";
  require_once __DIR__ . '/../includes/footer.php';
  exit;
}

$total = 0;
?>

<div class="container my-5">
  <h2 class="text-success mb-4">🛒 My Cart</h2>

  <table class="table table-bordered align-middle">
    <thead class="table-success">
      <tr>
        <th>Product</th>
        <th>Price (₦)</th>
        <th>Quantity</th>
        <th>Subtotal (₦)</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cart as $id => $item): 
        // ✅ Safely extract values
        $name = isset($item['name']) ? htmlspecialchars($item['name']) : 'Unnamed Product';
        $price = isset($item['price']) ? (float)$item['price'] : 0;
        $qty = isset($item['qty']) ? (int)$item['qty'] : 1;
        $subtotal = $price * $qty;
        $total += $subtotal;
      ?>
        <tr>
          <td><?= $name ?></td>
          <td>₦<?= number_format($price, 2) ?></td>
          <td><?= $qty ?></td>
          <td>₦<?= number_format($subtotal, 2) ?></td>
          <td>
            <form method="post" action="remove-from-cart.php" style="display:inline;">
              <input type="hidden" name="id" value="<?= htmlspecialchars($id) ?>">
              <button type="submit" class="btn btn-sm btn-danger">Remove</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div class="text-end fw-bold fs-5 mb-4">
    Total: ₦<?= number_format($total, 2) ?>
  </div>

 <div class="text-end">
<?php if (!is_logged_in()): ?>
    <a href="login.php?redirect=place_order.php" class="btn btn-success">
        Login to Place Order
    </a>
<?php else: ?>
    <form method="POST" action="place_order.php">
        <input type="hidden" name="delivery_address" value="Buyer address here">
        <button type="submit" class="btn btn-success">
            Place Order
        </button>
    </form>
<?php endif; ?>
</div>

    <!-- <div class="text-end">
    <a href="place_order.php" class="btn btn-success btn-lg">Place Order</a>
  </div> -->
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
