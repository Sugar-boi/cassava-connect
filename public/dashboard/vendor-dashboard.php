<?php
require_once __DIR__ . '/../../includes/header.php';
require_login();

// Only vendors can access
if (current_user_role() !== 'vendor') {
    flash_set('Access denied.');
    header('Location: /cassava-connect/public/index.php');
    exit;
}

// ✅ Get vendor ID
$stmt = $pdo->prepare("SELECT id FROM vendors WHERE user_id = ?");
$stmt->execute([current_user_id()]);
$vendor = $stmt->fetch();

if (!$vendor) {
    exit('Vendor profile not found.');
}

$vendor_id = $vendor['id'];

// ✅ Fetch unread notifications
$stmt = $pdo->prepare("
    SELECT * 
    FROM vendor_notifications 
    WHERE vendor_id = ? AND is_read = 0
    ORDER BY created_at DESC
");
$stmt->execute([$vendor_id]);
$notifications = $stmt->fetchAll();

// ✅ Fetch vendor products
$stmt = $pdo->prepare("SELECT * FROM products WHERE vendor_id = ? ORDER BY created_at DESC");
$stmt->execute([$vendor_id]);
$products = $stmt->fetchAll();
?>

<div class="container my-5">
    <!-- Notifications -->
    <?php if(!empty($notifications)): ?>
    <div class="mb-4">
        <h4 class="text-warning">🔔 Notifications 
            <span class="badge bg-danger"><?= count($notifications) ?></span>
        </h4>
        <ul class="list-group">
            <?php foreach($notifications as $note): ?>
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <?= htmlspecialchars($note['message']) ?>
                <small class="text-muted"><?= date('M d, Y H:i', strtotime($note['created_at'])) ?></small>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <!-- Products -->
    <h2 class="text-success mb-4">📦 My Products</h2>
    <div class="d-flex justify-content-between align-items-center mb-3">
        <a href="/cassava-connect/public/dashboard/add-product.php" class="btn btn-success">
            <i class="fa-solid fa-plus"></i> Add New Product
        </a>
    </div>

    <?php if (empty($products)): ?>
        <div class="alert alert-info">You haven’t added any products yet.</div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($products as $p): ?>
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <!-- Product Image -->
                <div class="position-relative">
                    <?php
                    $imagePath = $p['image'] ?? '';
                    if (!empty($imagePath) && (str_starts_with($imagePath, 'C:\\') || str_starts_with($imagePath, 'D:\\'))) {
                        $basename = basename($imagePath);
                        $imagePath = '/cassava-connect/uploads/products/' . $basename;
                    }
                    ?>
                    <div class="position-relative">
                        <?php if (!empty($imagePath)): ?>
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($p['name']) ?>" 
                                 style="height: 200px; object-fit: cover;">
                        <?php else: ?>
                            <div class="bg-light d-flex align-items-center justify-content-center" style="height: 200px;">
                                <span class="text-muted">No Image</span>
                            </div>
                        <?php endif; ?>

                        <span class="badge bg-success position-absolute top-0 start-0 m-2">
                            <?= htmlspecialchars($p['category']) ?>
                        </span>
                    </div>
                </div>

                <!-- Product Details -->
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title mb-2"><?= htmlspecialchars($p['name']) ?></h5>
                    <p class="card-text text-muted mb-2"><?= nl2br(htmlspecialchars($p['description'])) ?></p>
                    <ul class="list-unstyled small mb-3">
                        <li><i class="fa-solid fa-tag me-2"></i>Type: <?= htmlspecialchars($p['type']) ?></li>
                        <li><i class="fa-solid fa-cube me-2"></i>Grade: <?= htmlspecialchars($p['grade']) ?></li>
                        <li><i class="fa-solid fa-map-marker-alt me-2"></i>Location: <?= htmlspecialchars($p['location']) ?></li>
                        <li><i class="fa-solid fa-boxes-stacked me-2"></i>Stock: <?= htmlspecialchars($p['stock']) ?> <?= htmlspecialchars($p['unit']) ?></li>
                    </ul>

                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <h5 class="text-success fw-bold mb-0">₦<?= number_format($p['price'], 2) ?></h5>
                        <div class="d-flex justify-content-between">
                            <a href="/cassava-connect/public/dashboard/edit-product.php?id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="fa-solid fa-pen"></i>
                            </a>
                            <form method="post"
                                  action="/cassava-connect/public/dashboard/delete-product.php"
                                  onsubmit="return confirm('Delete this product?');"
                                  style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= csrf_token() ?>">
                                <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card-footer text-muted small text-center">
                    Added on <?= date('M d, Y', strtotime($p['created_at'])) ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?>