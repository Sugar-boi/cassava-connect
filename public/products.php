<?php
require_once __DIR__ . '/../includes/header.php';

$q = $_GET['q'] ?? '';
$type = $_GET['type'] ?? '';
$location = $_GET['location'] ?? '';
$min = $_GET['min'] ?? '';
$max = $_GET['max'] ?? '';

$query = "SELECT p.*, v.business_name 
          FROM products p 
          JOIN vendors v ON p.vendor_id = v.id 
          WHERE p.status = 'approved'";
$params = [];

// search
if ($q) {
  $query .= " AND (p.name LIKE ? OR p.category LIKE ?)";
  $like = "%$q%";
  $params[] = $like;
  $params[] = $like;
}

// filters
if ($type) { $query .= " AND p.type = ?"; $params[] = $type; }
if ($location) { $query .= " AND p.location = ?"; $params[] = $location; }
if ($min) { $query .= " AND p.price >= ?"; $params[] = $min; }
if ($max) { $query .= " AND p.price <= ?"; $params[] = $max; }

$query .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$products = $stmt->fetchAll(); // ✅ only once
?>

<a class="nav-link <?= $current_page == 'products.php' ? 'fw-bold text-dark bg-white rounded px-2' : 'text-white' ?>" 
   href="/cassava-connect/public/products.php">
   <i class="fa-solid fa-boxes-stacked me-1"></i> Products
</a>

<h2>Products</h2>
<form method="GET" class="row g-3 mb-4">
  <div class="col-md-3">
    <input type="text" name="q" class="form-control" placeholder="Search name or category" value="<?=htmlspecialchars($_GET['q'] ?? '')?>">
  </div>

  <div class="col-md-3">
    <select name="type" class="form-select">
      <option value="">All Types</option>
      <option value="Fresh Root" <?= (($_GET['type'] ?? '') == 'Fresh Root') ? 'selected' : '' ?>>Fresh Root</option>
      <option value="Cassava Flour" <?= (($_GET['type'] ?? '') == 'Cassava Flour') ? 'selected' : '' ?>>Cassava Flour</option>
      <option value="Cassava Chips" <?= (($_GET['type'] ?? '') == 'Cassava Chips') ? 'selected' : '' ?>>Cassava Chips</option>
      <option value="Cassava Starch" <?= (($_GET['type'] ?? '') == 'Cassava Starch') ? 'selected' : '' ?>>Cassava Starch</option>
    </select>
  </div>

  <div class="col-md-3">
    <select name="location" class="form-select">
      <option value="">All Locations</option>
      <option value="Enugu" <?= (($_GET['location'] ?? '') == 'Enugu') ? 'selected' : '' ?>>Enugu</option>
      <option value="Oyo" <?= (($_GET['location'] ?? '') == 'Oyo') ? 'selected' : '' ?>>Oyo</option>
      <option value="Kano" <?= (($_GET['location'] ?? '') == 'Kano') ? 'selected' : '' ?>>Kano</option>
      <option value="Benue" <?= (($_GET['location'] ?? '') == 'Benue') ? 'selected' : '' ?>>Benue</option>
    </select>
  </div>

  <div class="col-md-3">
    <div class="input-group">
      <input type="number" name="min" class="form-control" placeholder="Min ₦" value="<?=htmlspecialchars($_GET['min'] ?? '')?>">
      <input type="number" name="max" class="form-control" placeholder="Max ₦" value="<?=htmlspecialchars($_GET['max'] ?? '')?>">
      <button class="btn btn-success">Filter</button>
    </div>
  </div>
</form>

<div class="row">
  <?php if (empty($products)): ?>
  <div class="alert alert-info">No products found.</div>
<?php endif; ?>

<?php foreach($products as $p): ?>
  <div class="col-md-4 mb-3">
    <div class="card">
      <?php if($p['image']): ?>
        <img src="<?=htmlspecialchars($p['image'])?>" class="card-img-top" style="height:200px; object-fit:cover;">
      <?php endif; ?>
     <div class="card-body">
  <h5 class="card-title"><?=htmlspecialchars($p['name'])?></h5>
  
  <!-- STATUS BADGE FOR DEBUGGING -->
  <span class="badge 
      <?= $p['status'] === 'approved' ? 'bg-success' : ($p['status'] === 'pending' ? 'bg-warning text-dark' : 'bg-danger') ?>">
      <?= ucfirst($p['status']) ?>
  </span>
  
  <p class="card-text">₦<?=number_format($p['price'],2)?></p>
  <p class="card-text"><small>Vendor: <?=htmlspecialchars($p['business_name'])?></small></p>
  <a class="btn btn-sm btn-success" href="products-details.php?id=<?=$p['id']?>">View</a>
</div>
    </div>
  </div>
<?php endforeach; ?>
</div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
