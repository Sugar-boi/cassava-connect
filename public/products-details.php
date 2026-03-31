<?php
require_once __DIR__ . '/../includes/header.php';

$id = intval($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT p.*, v.business_name FROM products p JOIN vendors v ON p.vendor_id=v.id WHERE p.id = ?");
$stmt->execute([$id]);
$p = $stmt->fetch();
if(!$p){
    exit('Product not found');
}

// handle add to cart
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $qty = max(1, intval($_POST['qty']));
    $cart = $_SESSION['cart'] ?? [];
    // cart item: product_id => [qty, name, price, vendor_id]
    if(isset($cart[$id])){
        $cart[$id]['qty'] += $qty;
    } else {
        $cart[$id] = [
            'qty' => $qty,
            'name' => $p['name'],
            'price' => $p['price'],
            'vendor_id' => $p['vendor_id']
        ];
    }
    $_SESSION['cart'] = $cart;
    flash_set('Added to cart');
    header("Location: cart.php");
    exit;
}
?>
<h2><?=htmlspecialchars($p['name'])?></h2>
<div class="row">
  <div class="col-md-6">
    <?php if($p['image']): ?>
      <img src="<?=htmlspecialchars($p['image'])?>" class="img-fluid">
    <?php endif; ?>
  </div>
  <div class="col-md-6">
    <p><strong>Price:</strong> ₦<?=number_format($p['price'],2)?> / <?=htmlspecialchars($p['unit'])?></p>
    <p><strong>Vendor:</strong> <?=htmlspecialchars($p['business_name'])?></p>
    <p><?=nl2br(htmlspecialchars($p['description']))?></p>
   <form method="post" action="add-to-cart.php">
  <input type="hidden" name="product_id" value="<?=$p['id']?>">
  <button type="submit" class="btn btn-success mt-2">
    <i class="bi bi-cart"></i> Add to Cart
  </button>
</form>
    <form method="post" class="mt-4">
      <div class="mb-3">
        <label class="form-label fw-bold">Quantity</label>
        <input type="number" name="qty" class="form-control" value="1" min="1" required>
      </div>
      <button type="submit" class="btn btn-primary">
        <i class="bi bi-cart"></i> Add to Cart
      </button>
    </form>
  </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
