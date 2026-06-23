<?php
/**
 * SportZone - Admin Order Management
 * Member 4 (Mohamed Tarek) - Order Management System.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// ---- Handle inline status update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_status' && verify_csrf()) {
    $order_id = (int) ($_POST['order_id'] ?? 0);
    $status   = $_POST['status'] ?? '';
    if (in_array($status, ['Pending', 'Processing', 'Delivered', 'Cancelled'])) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $status, $order_id);
        $stmt->execute();
        $stmt->close();
        set_flash('success', "Order #" . str_pad($order_id, 5, '0', STR_PAD_LEFT) . " updated to $status.");
    }
    header("Location: " . BASE_URL . "admin/orders.php" . (!empty($_POST['back']) ? '?status=' . urlencode($_POST['back']) : ''));
    exit;
}

$filter = $_GET['status'] ?? '';
$where = '';
$params = [];
$types = '';
if (in_array($filter, ['Pending', 'Processing', 'Delivered', 'Cancelled'])) {
    $where = "WHERE status = ?";
    $params[] = $filter;
    $types = 's';
}

$sql = "SELECT * FROM orders $where ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

$page_title = 'Order Management';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-filter-tabs mb-2">
    <a href="orders.php" class="<?= $filter === '' ? 'active' : '' ?>">All</a>
    <a href="orders.php?status=Pending" class="<?= $filter === 'Pending' ? 'active' : '' ?>">Pending</a>
    <a href="orders.php?status=Processing" class="<?= $filter === 'Processing' ? 'active' : '' ?>">Processing</a>
    <a href="orders.php?status=Delivered" class="<?= $filter === 'Delivered' ? 'active' : '' ?>">Delivered</a>
    <a href="orders.php?status=Cancelled" class="<?= $filter === 'Cancelled' ? 'active' : '' ?>">Cancelled</a>
</div>

<div class="panel" style="padding:0;">
    <div class="table-wrap" style="border:none;">
        <table class="data-table">
            <thead>
                <tr><th>Order ID</th><th>Customer</th><th>Date</th><th>Total</th><th>Status</th><th>Update Status</th><th></th></tr>
            </thead>
            <tbody>
            <?php while ($o = $orders->fetch_assoc()): ?>
                <tr>
                    <td><strong>#<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                    <td><?= sanitize($o['full_name']) ?></td>
                    <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                    <td><?= money($o['total']) ?></td>
                    <td><span class="status-badge status-<?= strtolower($o['status']) ?>"><?= sanitize($o['status']) ?></span></td>
                    <td>
                        <form method="POST" action="orders.php" class="status-form">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="update_status">
                            <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                            <input type="hidden" name="back" value="<?= sanitize($filter) ?>">
                            <select name="status" onchange="this.form.submit()">
                                <?php foreach (['Pending', 'Processing', 'Delivered', 'Cancelled'] as $s): ?>
                                    <option value="<?= $s ?>" <?= $o['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </td>
                    <td><a href="<?= BASE_URL ?>admin/order-details.php?id=<?= $o['order_id'] ?>" class="btn btn-outline btn-sm">View</a></td>
                </tr>
            <?php endwhile; ?>
            <?php if ($orders->num_rows === 0): ?>
                <tr><td colspan="7" style="text-align:center; color:#888; padding:30px;">No orders found.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
