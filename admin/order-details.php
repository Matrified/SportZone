<?php
/**
 * SportZone - Admin Order Details
 * Member 4 (Mohamed Tarek) - Order Management System (detail view).
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);

$stmt = $conn->prepare("SELECT o.*, u.email FROM orders o JOIN users u ON o.user_id = u.user_id WHERE o.order_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Order not found.');
    header("Location: " . BASE_URL . "admin/orders.php");
    exit;
}

$istmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$istmt->bind_param("i", $id);
$istmt->execute();
$items = $istmt->get_result();
$istmt->close();

$page_title = 'Order #' . str_pad($order['order_id'], 5, '0', STR_PAD_LEFT);
include __DIR__ . '/includes/admin_header.php';
?>

<a href="<?= BASE_URL ?>admin/orders.php" class="btn btn-outline btn-sm mb-2">&larr; Back to Orders</a>

<div class="admin-grid-2">
    <div class="panel">
        <h3 style="margin-bottom:14px;">Customer Information</h3>
        <p style="margin-bottom:6px;"><strong>Name:</strong> <?= sanitize($order['full_name']) ?></p>
        <p style="margin-bottom:6px;"><strong>Email:</strong> <?= sanitize($order['email']) ?></p>
        <p style="margin-bottom:6px;"><strong>Phone:</strong> <?= sanitize($order['phone']) ?></p>
        <p style="margin-bottom:6px;"><strong>Address:</strong> <?= sanitize($order['address']) ?>, <?= sanitize($order['city']) ?> <?= sanitize($order['postal_code']) ?></p>
        <p><strong>Payment:</strong> <?= $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Credit/Debit Card' ?></p>
    </div>

    <div class="panel">
        <h3 style="margin-bottom:14px;">Order Summary</h3>
        <p style="margin-bottom:6px;"><strong>Order Date:</strong> <?= date('d M Y, H:i', strtotime($order['created_at'])) ?></p>
        <p style="margin-bottom:6px;"><strong>Status:</strong> <span class="status-badge status-<?= strtolower($order['status']) ?>"><?= sanitize($order['status']) ?></span></p>
        <form method="POST" action="orders.php" style="margin-top:14px;">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="update_status">
            <input type="hidden" name="order_id" value="<?= $order['order_id'] ?>">
            <label style="font-weight:600; font-size:0.9rem; display:block; margin-bottom:6px;">Update Status</label>
            <div style="display:flex; gap:8px;">
                <select name="status" style="flex:1; padding:9px; border:1px solid var(--color-border); border-radius:6px;">
                    <?php foreach (['Pending', 'Processing', 'Delivered', 'Cancelled'] as $s): ?>
                        <option value="<?= $s ?>" <?= $order['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Update</button>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <h3 style="margin-bottom:14px;">Ordered Items</h3>
    <div class="table-wrap" style="border:none;">
        <table class="data-table">
            <thead><tr><th>Product</th><th>Size</th><th>Quantity</th><th>Unit Price</th><th>Subtotal</th></tr></thead>
            <tbody>
            <?php while ($it = $items->fetch_assoc()): ?>
                <tr>
                    <td><?= sanitize($it['product_name']) ?></td>
                    <td><?= $it['size'] ? sanitize($it['size']) : '-' ?></td>
                    <td><?= $it['quantity'] ?></td>
                    <td><?= money($it['price']) ?></td>
                    <td><?= money($it['price'] * $it['quantity']) ?></td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
    <div style="max-width:300px; margin-left:auto; margin-top:16px;">
        <div class="summary-row"><span>Subtotal</span><span><?= money($order['subtotal']) ?></span></div>
        <div class="summary-row"><span>Shipping</span><span><?= money($order['shipping_fee']) ?></span></div>
        <div class="summary-row total"><span>Total</span><span><?= money($order['total']) ?></span></div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
