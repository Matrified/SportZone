<?php
/**
 * SportZone - Admin Product Management (list)
 * Member 4 (Mohamed Tarek) - Product CRUD Operations (Read + entry to C/U/D).
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$search  = trim($_GET['search'] ?? '');
$page    = max(1, (int) ($_GET['page'] ?? 1));
$perPage = 10;
$offset  = ($page - 1) * $perPage;

$where = '';
$params = [];
$types = '';
if ($search !== '') {
    $where = "WHERE p.name LIKE ? OR p.brand LIKE ?";
    $like = "%$search%";
    $params = [$like, $like];
    $types = 'ss';
}

$countSql = "SELECT COUNT(*) AS c FROM products p $where";
$cstmt = $conn->prepare($countSql);
if ($types) $cstmt->bind_param($types, ...$params);
$cstmt->execute();
$total = (int) $cstmt->get_result()->fetch_assoc()['c'];
$cstmt->close();
$totalPages = max(1, ceil($total / $perPage));

$sql = "SELECT p.*, c.name AS category_name FROM products p
        JOIN categories c ON p.category_id = c.category_id
        $where ORDER BY p.product_id DESC LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$pp = $params; $pt = $types . 'ii'; $pp[] = $perPage; $pp[] = $offset;
$stmt->bind_param($pt, ...$pp);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

$page_title = 'Product Management';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="flex-between mb-2" style="flex-wrap:wrap; gap:12px;">
    <form method="GET" action="products.php" class="admin-search">
        <input type="text" name="search" placeholder="Search products..." value="<?= sanitize($search) ?>">
        <button type="submit" class="btn btn-dark btn-sm">Search</button>
    </form>
    <a href="<?= BASE_URL ?>admin/product-form.php" class="btn btn-primary">+ Add New Product</a>
</div>

<div class="panel" style="padding:0;">
    <div class="table-wrap" style="border:none;">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Image</th><th>Name</th><th>Category</th><th>Brand</th><th>Price</th><th>Stock</th><th>Actions</th></tr>
            </thead>
            <tbody>
            <?php while ($p = $products->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $p['product_id'] ?></td>
                    <td><img src="<?= product_image_url($p['image']) ?>" alt="" style="width:44px; height:44px; object-fit:cover; border-radius:6px;"></td>
                    <td><?= sanitize($p['name']) ?></td>
                    <td><?= sanitize($p['category_name']) ?></td>
                    <td><?= sanitize($p['brand']) ?></td>
                    <td><?= money($p['price']) ?></td>
                    <td><span class="status-badge <?= $p['stock'] <= 10 ? 'status-cancelled' : ($p['stock'] <= 20 ? 'status-pending' : 'status-delivered') ?>"><?= $p['stock'] ?></span></td>
                    <td>
                        <div style="display:flex; gap:6px;">
                            <a href="<?= BASE_URL ?>admin/product-form.php?id=<?= $p['product_id'] ?>" class="btn btn-outline btn-sm">Edit</a>
                            <form method="POST" action="product-delete.php" onsubmit="return confirm('Delete this product? This cannot be undone.')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="id" value="<?= $p['product_id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background:var(--color-danger); color:#fff;">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($products->num_rows === 0): ?>
                <tr><td colspan="8" style="text-align:center; color:#888; padding:30px;">No products found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($totalPages > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?><?= $search !== '' ? '&search=' . urlencode($search) : '' ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
<?php endif; ?>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
