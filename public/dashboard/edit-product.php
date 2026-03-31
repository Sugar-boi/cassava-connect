<?php
require_once __DIR__ . '/../../includes/header.php';
require_login();

// ✅ Only vendors allowed
if (current_user_role() !== 'vendor') {
    flash_set('Access denied.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}

require_once __DIR__ . '/../../includes/security.php';

// ✅ Get vendor ID
$stmt = $pdo->prepare("SELECT id FROM vendors WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$vendor = $stmt->fetch();

if (!$vendor) {
    exit('Vendor profile not found.');
}
$vendor_id = $vendor['id'];

// ✅ Get product ID from URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    flash_set('Invalid product.');
    header('Location: vendor-dashboard.php');
    exit;
}

$product_id = (int)$_GET['id'];

// ✅ Fetch product to edit
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND vendor_id = ?");
$stmt->execute([$product_id, $vendor_id]);
$product = $stmt->fetch();

if (!$product) {
    flash_set('Product not found.');
    header('Location: vendor-dashboard.php');
    exit;
}

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $unit = trim($_POST['unit']);
    $grade = trim($_POST['grade']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);

    $imagePath = $product['image']; // keep old one

if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
    $tmp = $_FILES['image']['tmp_name'];
    $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['image']['name']);

    // ✅ Use absolute path for moving the file
    $uploadDir = realpath(__DIR__ . '/../../') . '/uploads/products/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $dest = $uploadDir . $fname;
    $allowed = ['jpg', 'jpeg', 'png', 'gif'];
    $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

    if (in_array($ext, $allowed)) {
        if (move_uploaded_file($tmp, $dest)) {
            // ✅ Delete old image if it exists
            if (!empty($product['image'])) {
                $oldPath = realpath(__DIR__ . '/../../') . '/' . ltrim($product['image'], '/');
                if (file_exists($oldPath)) unlink($oldPath);
            }

            // ✅ Store correct relative URL for browser
            $imagePath = '/cassava-connect/uploads/products/' . $fname;
        }
    } else {
        flash_set('❌ Invalid file type. Only JPG, PNG, and GIF allowed.');
    }
}



    // ✅ Update in DB
    $stmt = $pdo->prepare("
        UPDATE products 
SET name=?, description=?, category=?, type=?, location=?, price=?, stock=?, unit=?, grade=?, image=?
WHERE id=? AND vendor_id=?

    ");
    $stmt->execute([$name, $desc, $category, $type, $location, $price, $stock, $unit, $grade, $imagePath, $product_id, $vendor_id]);

    flash_set('✅ Product updated successfully!');
    header('Location: vendor-dashboard.php');
    exit;
}
?>

<div class="container my-5">
  <h2 class="text-success mb-4">✏️ Edit Product</h2>

  <form method="post" enctype="multipart/form-data" class="p-4 bg-light shadow-sm rounded">
    <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">

    <div class="mb-3">
      <label class="form-label fw-bold">Product Name</label>
      <input class="form-control" name="name" value="<?= htmlspecialchars($product['name']) ?>" required>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold">Description</label>
      <textarea class="form-control" name="description" rows="3"><?= htmlspecialchars($product['description']) ?></textarea>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Category</label>
        <input class="form-control" name="category" value="<?= htmlspecialchars($product['category']) ?>" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Type</label>
        <select class="form-select" name="type" required>
          <option value="">-- Select Type --</option>
          <option value="Fresh Root" <?= $product['type'] == 'Fresh Root' ? 'selected' : '' ?>>Fresh Root ( ege alata)</option>
          <option value="Cassava Flour" <?= $product['type'] == 'Cassava Flour' ? 'selected' : '' ?>>Cassava Flour (garri)</option>
          <option value="Cassava Chips" <?= $product['type'] == 'Cassava Chips' ? 'selected' : '' ?>>Cassava Chips</option>
          <option value="Cassava Starch" <?= $product['type'] == 'Cassava Starch' ? 'selected' : '' ?>>Cassava Starch </option>
        </select>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Location</label>
        <input class="form-control" name="location" value="<?= htmlspecialchars($product['location']) ?>" required>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Price (₦)</label>
        <input class="form-control" name="price" type="number" step="0.01" value="<?= $product['price'] ?>" required>
      </div>
      <div class="col-md-3 mb-3">
        <label class="form-label fw-bold">Stock</label>
        <input class="form-control" name="stock" type="number" value="<?= $product['stock'] ?>" required>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Unit</label>
        <input class="form-control" name="unit" value="<?= htmlspecialchars($product['unit']) ?>">
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label fw-bold">Grade</label>
        <input class="form-control" name="grade" value="<?= htmlspecialchars($product['grade']) ?>">
      </div>
    </div>

    <div class="mb-3">
      <label class="form-label fw-bold">Product Image</label><br>
      <?php if (!empty($product['image'])): ?>
        <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product image" class="img-thumbnail mb-2" style="max-height:150px;">
      <?php endif; ?>
      <input class="form-control" type="file" name="image" accept="image/*">
      <small class="text-muted">Leave blank to keep current image</small>
    </div>

    <button class="btn btn-success w-100 py-2">
      <i class="fa-solid fa-save"></i> Update Product
    </button>
  </form>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
