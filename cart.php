<?php
/**
 * SportZone - Shopping Cart Page
 * Member 3 (Osman Omer Gumaa) - Shopping Cart System.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Fetch cart items joined with product info
$stmt = $conn->prepare("SELECT c.cart_id, c.quantity, c.size, p.product_id, p.name, p.price, p.image, p.stock
                        FROM cart c JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ? ORDER BY c.added_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$items = $stmt->get_result();
$stmt->close();

$cartItems = [];
$subtotal = 0;
while ($row = $items->fetch_assoc()) {
    $row['line_total'] = $row['price'] * $row['quantity'];
    $subtotal += $row['line_total'];
    $cartItems[] = $row;
}
$shipping = $subtotal > 0 ? SHIPPING_FEE : 0;
$total = $subtotal + $shipping;

$page_title = 'Shopping Cart';
include 'includes/head.php';
include 'includes/header.php';
$flash = get_flash();
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">Shopping Cart</h2>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>

        <?php if (empty($cartItems)): ?>
            <div class="empty-state">
                <div style="font-size:3rem;">🛒</div>
                <h3>Your cart is empty</h3>
                <p style="color:#666; margin-bottom:18px;">Looks like you haven't added anything yet.</p>
                <a href="products.php" class="btn btn-primary">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="cart-layout">
                <!-- Cart items -->
                <div class="cart-items">
                    <div class="table-wrap">
                        <table class="data-table cart-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartItems as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="cart-product">
                                                <img src="<?= product_image_url($item['image']) ?>" alt="<?= sanitize($item['name']) ?>">
                                                <div>
                                                    <a href="product-details.php?id=<?= $item['product_id'] ?>" style="font-weight:600;"><?= sanitize($item['name']) ?></a>
                                                    <?php if ($item['size']): ?><div style="font-size:0.8rem; color:#888;">Size: <?= sanitize($item['size']) ?></div><?php endif; ?>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?= money($item['price']) ?></td>
                                        <td>
                                            <form method="POST" action="update_cart.php" class="qty-form">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" max="<?= $item['stock'] ?>" onchange="this.form.submit()" style="width:64px; padding:6px; border:1px solid var(--color-border); border-radius:6px;">
                                            </form>
                                        </td>
                                        <td><strong><?= money($item['line_total']) ?></strong></td>
                                        <td>
                                            <form method="POST" action="update_cart.php" onsubmit="return confirm('Remove this item?')">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="cart_id" value="<?= $item['cart_id'] ?>">
                                                <button type="submit" class="remove-btn" title="Remove">&times;</button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="flex-between mt-2">
                        <a href="products.php" class="btn btn-outline btn-sm">&larr; Continue Shopping</a>
                        <form method="POST" action="update_cart.php" onsubmit="return confirm('Clear entire cart?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="clear">
                            <button type="submit" class="btn btn-outline btn-sm">Clear Cart</button>
                        </form>
                    </div>
                </div>

                <!-- Summary -->
                <aside class="cart-summary">
                    <h3>Order Summary</h3>
                    <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                    <div class="summary-row"><span>Shipping</span><span><?= money($shipping) ?></span></div>
                    <div class="summary-row total"><span>Total</span><span><?= money($total) ?></span></div>
                    <a href="checkout.php" class="btn btn-primary btn-block mt-2">Proceed to Checkout</a>
                </aside>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
