<?php
/**
 * SportZone - Admin Dashboard
 * Member 4 (Mohamed Tarek) - Dashboard Analytics.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Statistics
$totalProducts = $conn->query("SELECT COUNT(*) AS c FROM products")->fetch_assoc()['c'];
$totalUsers    = $conn->query("SELECT COUNT(*) AS c FROM users WHERE role = 'customer'")->fetch_assoc()['c'];
$totalOrders   = $conn->query("SELECT COUNT(*) AS c FROM orders")->fetch_assoc()['c'];
$revenue       = $conn->query("SELECT COALESCE(SUM(total),0) AS s FROM orders WHERE status != 'Cancelled'")->fetch_assoc()['s'];

// Low stock products
$lowStock = $conn->query("SELECT name, stock FROM products WHERE stock <= 20 ORDER BY stock ASC LIMIT 5");

// Recent orders
$recentOrders = $conn->query("SELECT o.order_id, o.full_name, o.total, o.status, o.created_at
                              FROM orders o ORDER BY o.created_at DESC LIMIT 6");

// Sales for last 7 days (for chart)
$salesData = $conn->query("SELECT DATE(created_at) AS d, SUM(total) AS t
                           FROM orders WHERE status != 'Cancelled'
                           AND created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY)
                           GROUP BY DATE(created_at) ORDER BY d ASC");
$chartLabels = [];
$chartValues = [];
$salesMap = [];
while ($r = $salesData->fetch_assoc()) {
    $salesMap[$r['d']] = (float) $r['t'];
}
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i days"));
    $chartLabels[] = date('D', strtotime($day));
    $chartValues[] = $salesMap[$day] ?? 0;
}

$page_title = 'Dashboard';
include __DIR__ . '/includes/admin_header.php';
?>

<!-- Stat cards -->
<div class="stat-grid">
    <div class="stat-card">
        <div class="stat-icon" style="background:#e3edfd;">📦</div>
        <div><div class="stat-num"><?= number_format($totalProducts) ?></div><div class="stat-label">Total Products</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#e6f6ec;">👥</div>
        <div><div class="stat-num"><?= number_format($totalUsers) ?></div><div class="stat-label">Customers</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fdf3e3;">🧾</div>
        <div><div class="stat-num"><?= number_format($totalOrders) ?></div><div class="stat-label">Total Orders</div></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fde8e8;">💰</div>
        <div><div class="stat-num"><?= money($revenue) ?></div><div class="stat-label">Revenue</div></div>
    </div>
</div>

<div class="admin-grid-2">
    <!-- Sales chart -->
    <div class="panel">
        <h3 style="margin-bottom:16px;">Sales Overview (Last 7 Days)</h3>
        <canvas id="salesChart" height="120"></canvas>
    </div>

    <!-- Low stock -->
    <div class="panel">
        <h3 style="margin-bottom:16px;">Low Stock Alerts</h3>
        <?php if ($lowStock->num_rows === 0): ?>
            <p style="color:#666;">All products are well stocked. 👍</p>
        <?php else: ?>
            <?php while ($p = $lowStock->fetch_assoc()): ?>
                <div class="flex-between" style="padding:8px 0; border-bottom:1px solid var(--color-border);">
                    <span style="font-size:0.9rem;"><?= sanitize($p['name']) ?></span>
                    <span class="status-badge <?= $p['stock'] <= 10 ? 'status-cancelled' : 'status-pending' ?>"><?= $p['stock'] ?> left</span>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Recent orders -->
<div class="panel">
    <div class="flex-between" style="margin-bottom:16px;">
        <h3>Recent Orders</h3>
        <a href="<?= BASE_URL ?>admin/orders.php" class="btn btn-outline btn-sm">View All</a>
    </div>
    <div class="table-wrap">
        <table class="data-table">
            <thead><tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th></tr></thead>
            <tbody>
            <?php while ($o = $recentOrders->fetch_assoc()): ?>
                <tr>
                    <td><a href="<?= BASE_URL ?>admin/order-details.php?id=<?= $o['order_id'] ?>"><strong>#<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?></strong></a></td>
                    <td><?= sanitize($o['full_name']) ?></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><?= money($o['total']) ?></td>
                    <td><span class="status-badge status-<?= strtolower($o['status']) ?>"><?= sanitize($o['status']) ?></span></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($recentOrders->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align:center; color:#888;">No orders yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    const CHART_LABELS = <?= json_encode($chartLabels) ?>;
    const CHART_VALUES = <?= json_encode($chartValues) ?>;
</script>
<?php include __DIR__ . '/includes/admin_footer.php'; ?>
