<?php
session_start();

// Protect page: only admin can access
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

require_once '../config/db.php';

// =============================
// HANDLE APPROVE / REJECT
// =============================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve') {
        $stmt = $pdo->prepare("UPDATE products SET status = 'approved' WHERE id = ?");
        $stmt->execute([$id]);
    }

    if ($action === 'reject') {
        $stmt = $pdo->prepare("UPDATE products SET status = 'rejected' WHERE id = ?");
        $stmt->execute([$id]);
    }

    header("Location: admin.php");
    exit;
}

// =============================
// FETCH USERS
// =============================
$stmt = $pdo->query("SELECT id, name, email, role FROM users ORDER BY id ASC");
$users = $stmt->fetchAll();

// Stats
$total_users = count($users);
$total_admins = count(array_filter($users, fn($u) => $u['role'] === 'admin'));
$total_vendors = count(array_filter($users, fn($u) => $u['role'] === 'vendor'));
$total_buyers = count(array_filter($users, fn($u) => $u['role'] === 'buyer'));

// =============================
// FETCH PENDING PRODUCTS
// =============================
$stmt = $pdo->query("
    SELECT products.*, users.name AS vendor_name
    FROM products
    JOIN users ON products.vendor_id = users.id
    ORDER BY products.id DESC
");
$products = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard | Cassava Connect</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f9; }
        #header { padding: 20px; margin-bottom: 20px; border-radius: 10px; background: white; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .stat-card { border-radius: 10px; padding: 20px; color: white; }
        .card-users { background: #0d6efd; }
        .card-admins { background: #dc3545; }
        .card-vendors { background: #ffc107; color: black; }
        .card-buyers { background: #198754; }
        .badge-status { font-size: 0.9em; }
        table img { max-height: 50px; object-fit: cover; border-radius: 5px; }
    </style>
</head>
<body class="container mt-5">

<!-- HEADER -->
<div id="header">
    <h2>Admin Dashboard</h2>
    <p>Welcome, <?= htmlspecialchars($_SESSION['user_name']) ?> (Admin)</p>
    <a href="logout.php" class="btn btn-danger">Logout</a>
</div>

<!-- STATS -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="stat-card card-users text-center">
            <h4>Total Users</h4>
            <h2><?= $total_users ?></h2>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-admins text-center">
            <h4>Admins</h4>
            <h2><?= $total_admins ?></h2>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-vendors text-center">
            <h4>Vendors</h4>
            <h2><?= $total_vendors ?></h2>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="stat-card card-buyers text-center">
            <h4>Buyers</h4>
            <h2><?= $total_buyers ?></h2>
        </div>
    </div>
</div>

<!-- USERS TABLE -->
<h3>All Users</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Role</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= $user['id'] ?></td>
                <td><?= htmlspecialchars($user['name']) ?></td>
                <td><?= htmlspecialchars($user['email']) ?></td>
                <td><?= $user['role'] ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- PRODUCTS APPROVAL -->
<h3 class="mt-5">Vendor Products</h3>
<table class="table table-bordered table-striped">
    <thead>
        <tr>
            <th>ID</th>
            <th>Preview</th>
            <th>Product Name</th>
            <th>Vendor</th>
            <th>Price</th>
            <th>Status</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <tr>
                    <td><?= $product['id'] ?></td>
                    <td>
                        <?php if ($product['image']): ?>
                            <img src="<?= htmlspecialchars($product['image']) ?>" alt="Product Image">
                        <?php else: ?>
                            <span class="text-muted">No Image</span>
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($product['name']) ?></td>
                    <td><?= htmlspecialchars($product['vendor_name']) ?></td>
                    <td>₦<?= number_format($product['price']) ?></td>
                    <td>
                        <?php
                            $status_class = match($product['status']) {
                                'pending' => 'bg-warning text-dark',
                                'approved' => 'bg-success',
                                'rejected' => 'bg-danger',
                                default => 'bg-secondary'
                            };
                        ?>
                        <span class="badge badge-status <?= $status_class ?>"><?= ucfirst($product['status']) ?></span>
                    </td>
                    <td>
                        <?php if ($product['status'] === 'pending'): ?>
                            <a href="admin.php?action=approve&id=<?= $product['id'] ?>" class="btn btn-success btn-sm">Approve</a>
                            <a href="admin.php?action=reject&id=<?= $product['id'] ?>" class="btn btn-danger btn-sm">Reject</a>
                        <?php else: ?>
                            <span class="text-muted">No actions</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="7" class="text-center">No products found</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

</body>
</html>