<?php
/**
 * SportZone - Order History & Tracking
 * Member 3 (Osman Omer Gumaa) - Order Tracking feature.
 * Lists the logged-in customer's orders; expandable details modal.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch all orders for this user (newest first)
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$orders = $stmt->get_result();
$stmt->close();

$page_title = 'My Orders';
include 'includes/head.php';
include 'includes/header.php';
$flash = get_flash();
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">My Orders</h2>
        <p class="section-subtitle">Track the status of your purchases</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>

        <?php if ($orders->num_rows === 0): ?>
            <div class="empty-state">
                <div style="font-size:3rem;">📦</div>
                <h3>No orders yet</h3>
                <p style="color:#666; margin-bottom:18px;">When you place an order, it will appear here.</p>
                <a href="products.php" class="btn btn-primary">Start Shopping</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Date</th>
                            <th>Items</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php while ($o = $orders->fetch_assoc()):
                        // Fetch items summary for this order
                        $istmt = $conn->prepare("SELECT product_name, quantity, price, size FROM order_items WHERE order_id = ?");
                        $istmt->bind_param("i", $o['order_id']);
                        $istmt->execute();
                        $items = $istmt->get_result();
                        $itemList = [];
                        $itemRows = [];
                        while ($it = $items->fetch_assoc()) {
                            $itemList[] = $it['product_name'] . ' x' . $it['quantity'];
                            $itemRows[] = $it;
                        }
                        $istmt->close();
                        $summary = implode(', ', $itemList);
                        if (strlen($summary) > 50) $summary = substr($summary, 0, 50) . '...';
                    ?>
                        <tr>
                            <td><strong>#<?= str_pad($o['order_id'], 5, '0', STR_PAD_LEFT) ?></strong></td>
                            <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                            <td><?= sanitize($summary) ?></td>
                            <td><?= money($o['total']) ?></td>
                            <td><span class="status-badge status-<?= strtolower($o['status']) ?>"><?= sanitize($o['status']) ?></span></td>
                            <td>
                                <button class="btn btn-outline btn-sm view-order-btn"
                                    data-order='<?= htmlspecialchars(json_encode([
                                        'id' => str_pad($o['order_id'], 5, '0', STR_PAD_LEFT),
                                        'date' => date('d M Y, H:i', strtotime($o['created_at'])),
                                        'status' => $o['status'],
                                        'name' => $o['full_name'],
                                        'phone' => $o['phone'],
                                        'address' => $o['address'] . ', ' . $o['city'] . ' ' . $o['postal_code'],
                                        'payment' => $o['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Credit/Debit Card',
                                        'subtotal' => number_format($o['subtotal'], 2),
                                        'shipping' => number_format($o['shipping_fee'], 2),
                                        'discount' => number_format($o['discount_amount'], 2),
                                        'discount_code' => $o['discount_code'],
                                        'total' => number_format($o['total'], 2),
                                        'items' => $itemRows
                                    ]), ENT_QUOTES, 'UTF-8') ?>'>View</button>
                                <?php if ($o['status'] === 'Pending'): ?>
                                    <form method="POST" action="cancel_order.php" style="display:inline;" onsubmit="return confirm('Cancel this order?')">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="order_id" value="<?= $o['order_id'] ?>">
                                        <button type="submit" class="btn btn-sm" style="background:var(--color-danger); color:#fff;">Cancel</button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Order Details Modal -->
<div class="modal-overlay" id="orderModal">
    <div class="modal">
        <button class="modal-close" id="modalClose">&times;</button>
        <h3 id="modalTitle">Order Details</h3>
        <div id="modalBody"></div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/orders.js"></script>
<?php include 'includes/footer.php'; ?>
