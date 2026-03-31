<?php
require_once __DIR__ . '/../../includes/header.php';
require_login();

if (current_user_role() !== 'vendor') {
    flash_set('Only vendors can access this page.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}
require_once __DIR__ . '/../../includes/security.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    // your normal form processing here...
}


// ✅ Get vendor ID
$stmt = $pdo->prepare("SELECT id FROM vendors WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$vendor = $stmt->fetch();

if (!$vendor) {
    exit('Vendor profile not found.');
}
$vendor_id = $vendor['id'];

// ✅ Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $desc = trim($_POST['description']);
    $category = trim($_POST['category']);
    $price = floatval($_POST['price']);
    $stock = intval($_POST['stock']);
    $unit = trim($_POST['unit']);
    $grade = trim($_POST['grade']);
    $type = trim($_POST['type']);
    $location = trim($_POST['location']);

    // ✅ Handle image upload
    $imagePath = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['image']['tmp_name'];
        $fname = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $_FILES['image']['name']);
        $uploadDir = __DIR__ . '/../../uploads/products/';
        $dest = $uploadDir . $fname;

        // Create directory if missing
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];
        $ext = strtolower(pathinfo($fname, PATHINFO_EXTENSION));

        if (in_array($ext, $allowedTypes)) {
            if (move_uploaded_file($tmp, $dest)) {
                $imagePath = '/cassava-connect/uploads/products/' . $fname;
            }
        }
    }

    // ✅ Insert into database
    $stmt = $pdo->prepare("
        INSERT INTO products 
        (vendor_id, name, description, category, type, location, price, stock, unit, grade, image, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$vendor_id, $name, $desc, $category, $type, $location, $price, $stock, $unit, $grade, $imagePath]);

    flash_set('✅ Product added successfully!');
    header('Location: vendor-dashboard.php');
    exit;
}
?>

<h2 class="text-success mt-4 mb-3">Add Product</h2>

<form method="post" enctype="multipart/form-data" class="p-4 bg-light shadow-sm rounded">
  <input type="hidden" name="csrf_token" value="<?=csrf_token()?>">

  <div class="mb-3">
    <label class="form-label fw-bold">Product Name</label>
    <input class="form-control" name="name" required placeholder="e.g. Fresh Cassava Root">
  </div>

  <div class="mb-3">
    <label class="form-label fw-bold">Description</label>
    <textarea class="form-control" name="description" rows="3" placeholder="Describe your product..."></textarea>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label fw-bold">Category</label>
      <input class="form-control" name="category" placeholder="Garri, Flour, Fresh Root" required>
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label fw-bold">Type</label>
      <select class="form-select" name="type" required>
        <option value="">-- Select Type --</option>
        <option value="Fresh Root">Fresh Root ( ege alata)</option>
        <option value="Cassava Flour">Cassava Flour (Garri)</option>
        <option value="Cassava Chips">Cassava Chips</option>
        <option value="Cassava Starch">Cassava Starch</option>
      </select>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label fw-bold">Location</label>
      <input class="form-control" name="location" placeholder="e.g. Oyo, Enugu" required>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">Price (₦)</label>
      <input class="form-control" name="price" type="number" step="0.01" required>
    </div>
    <div class="col-md-3 mb-3">
      <label class="form-label fw-bold">Stock</label>
      <input class="form-control" name="stock" type="number" required>
    </div>
  </div>

  <div class="row">
    <div class="col-md-6 mb-3">
      <label class="form-label fw-bold">Unit</label>
      <input class="form-control" name="unit" value="load">
    </div>
    <div class="col-md-6 mb-3">
      <label class="form-label fw-bold">Grade</label>
      <input class="form-control" name="grade" placeholder="e.g. Grade A, Premium">
    </div>
  </div>

  <div class="mb-3">
    <label class="form-label fw-bold">Product Image</label>
    <input class="form-control" type="file" name="image" accept="image/*">
    <small class="text-muted">Accepted formats: JPG, PNG, GIF</small>
  </div>

  <button class="btn btn-success w-100 py-2">
    <i class="bi bi-plus-circle"></i> Add Product
  </button>
</form>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>
