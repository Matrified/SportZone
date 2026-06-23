<?php
/**
 * SportZone - Order Confirmation
 * Member 3 (Osman Omer Gumaa) - Order Placement confirmation screen.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$order_id = $_SESSION['last_order_id'] ?? 0;
if (!$order_id) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}
unset($_SESSION['last_order_id']);

// Confirm the order belongs to this user
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $_SESSION['user_id']);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$page_title = 'Order Confirmed';
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width:640px; text-align:center;">
        <div style="font-size:4rem;">✅</div>
        <h2 class="section-title" style="margin-top:10px;">Thank You for Your Order!</h2>
        <p style="color:#666; margin-bottom:24px;">Your order has been placed successfully and is now being processed.</p>

        <div class="panel" style="text-align:left;">
            <div class="flex-between" style="margin-bottom:10px;">
                <span style="color:#888;">Order Number</span>
                <strong>#<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?></strong>
            </div>
            <div class="flex-between" style="margin-bottom:10px;">
                <span style="color:#888;">Order Date</span>
                <span><?= date('d M Y, H:i', strtotime($order['created_at'])) ?></span>
            </div>
            <div class="flex-between" style="margin-bottom:10px;">
                <span style="color:#888;">Payment Method</span>
                <span><?= $order['payment_method'] === 'cod' ? 'Cash on Delivery' : 'Credit/Debit Card' ?></span>
            </div>
            <div class="flex-between" style="margin-bottom:10px;">
                <span style="color:#888;">Total Amount</span>
                <strong style="color:var(--color-accent);"><?= money($order['total']) ?></strong>
            </div>
            <div class="flex-between">
                <span style="color:#888;">Status</span>
                <span class="status-badge status-pending"><?= sanitize($order['status']) ?></span>
            </div>
        </div>

        <div style="display:flex; gap:12px; justify-content:center; margin-top:24px;">
            <a href="orders.php" class="btn btn-dark">Track My Order</a>
            <a href="products.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
