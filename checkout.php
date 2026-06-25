<?php
/**
 * SportZone - Checkout Page
 * Member 3 (Osman Omer Gumaa) - Checkout & Order Placement.
 * Multi-step form: 1) Shipping  2) Payment  3) Review.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

// Load user defaults
$ustmt = $conn->prepare("SELECT full_name, phone, address FROM users WHERE user_id = ?");
$ustmt->bind_param("i", $user_id);
$ustmt->execute();
$user = $ustmt->get_result()->fetch_assoc();
$ustmt->close();

// Load cart
$stmt = $conn->prepare("SELECT c.quantity, c.size, p.name, p.price, p.image
                        FROM cart c JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$cartItems = [];
$subtotal = 0;
while ($row = $res->fetch_assoc()) {
    $row['line_total'] = $row['price'] * $row['quantity'];
    $subtotal += $row['line_total'];
    $cartItems[] = $row;
}

// Empty cart guard
if (empty($cartItems)) {
    set_flash('error', 'Your cart is empty.');
    header("Location: " . BASE_URL . "cart.php");
    exit;
}

$shipping = SHIPPING_FEE;
$total = $subtotal + $shipping;

$page_title = 'Checkout';
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">Checkout</h2>

        <!-- Step indicator -->
        <div class="step-indicator">
            <div class="step active" data-step="1"><span>1</span> Shipping</div>
            <div class="step" data-step="2"><span>2</span> Payment</div>
            <div class="step" data-step="3"><span>3</span> Review</div>
        </div>

        <div class="checkout-layout">
            <form method="POST" action="place_order.php" id="checkoutForm" class="checkout-form" novalidate>
                <?= csrf_field() ?>
                <input type="hidden" name="promo_code" id="promoCode" value="">

                <!-- STEP 1: Shipping -->
                <div class="checkout-step active" id="step-1">
                    <h3>Shipping Address</h3>
                    <div class="form-group">
                        <label for="full_name">Full Name</label>
                        <input type="text" id="full_name" name="full_name" value="<?= sanitize($user['full_name']) ?>" required>
                        <span class="error-text">Please enter your full name.</span>
                    </div>
                    <div class="form-group">
                        <label for="phone">Phone Number</label>
                        <input type="text" id="phone" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" required>
                        <span class="error-text">Please enter a valid phone number.</span>
                    </div>
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" id="address" name="address" value="<?= sanitize($user['address'] ?? '') ?>" required>
                        <span class="error-text">Please enter your address.</span>
                    </div>
                    <div style="display:flex; gap:14px;">
                        <div class="form-group" style="flex:1;">
                            <label for="city">City</label>
                            <input type="text" id="city" name="city" required>
                            <span class="error-text">Please enter your city.</span>
                        </div>
                        <div class="form-group" style="flex:1;">
                            <label for="postal_code">Postal Code</label>
                            <input type="text" id="postal_code" name="postal_code" required>
                            <span class="error-text">Please enter your postal code.</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-primary next-step" data-next="2">Continue to Payment</button>
                </div>

                <!-- STEP 2: Payment -->
                <div class="checkout-step" id="step-2">
                    <h3>Payment Method</h3>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="cod" checked>
                        <div>
                            <strong>Cash on Delivery</strong>
                            <p style="font-size:0.85rem; color:#666;">Pay with cash when your order arrives.</p>
                        </div>
                    </label>
                    <label class="payment-option">
                        <input type="radio" name="payment_method" value="card">
                        <div>
                            <strong>Credit / Debit Card</strong>
                            <p style="font-size:0.85rem; color:#666;">Simulated card payment (demo only — no real charge).</p>
                        </div>
                    </label>

                    <div id="cardFields" style="display:none; margin-top:14px;">
                        <div class="form-group">
                            <label>Card Number</label>
                            <input type="text" name="card_number" placeholder="1234 5678 9012 3456" maxlength="19">
                        </div>
                        <div style="display:flex; gap:14px;">
                            <div class="form-group" style="flex:1;">
                                <label>Expiry</label>
                                <input type="text" name="card_expiry" placeholder="MM/YY" maxlength="5">
                            </div>
                            <div class="form-group" style="flex:1;">
                                <label>CVV</label>
                                <input type="text" name="card_cvv" placeholder="123" maxlength="4">
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; gap:12px;">
                        <button type="button" class="btn btn-outline prev-step" data-prev="1">Back</button>
                        <button type="button" class="btn btn-primary next-step" data-next="3">Review Order</button>
                    </div>
                </div>

                <!-- STEP 3: Review -->
                <div class="checkout-step" id="step-3">
                    <h3>Review &amp; Confirm</h3>
                    <div id="reviewSummary" class="review-box"></div>
                    <div style="display:flex; gap:12px; margin-top:16px;">
                        <button type="button" class="btn btn-outline prev-step" data-prev="2">Back</button>
                        <button type="submit" class="btn btn-primary" id="placeOrderBtn">Place Order</button>
                    </div>
                </div>
            </form>

            <!-- Order summary sidebar -->
            <aside class="cart-summary">
                <h3>Order Summary</h3>
                <?php foreach ($cartItems as $item): ?>
                    <div class="summary-item">
                        <img src="<?= product_image_url($item['image']) ?>" alt="">
                        <div style="flex:1;">
                            <div style="font-size:0.85rem; font-weight:600;"><?= sanitize($item['name']) ?></div>
                            <div style="font-size:0.78rem; color:#888;">Qty: <?= $item['quantity'] ?><?= $item['size'] ? ' • Size: ' . sanitize($item['size']) : '' ?></div>
                        </div>
                        <div style="font-size:0.85rem;"><?= money($item['line_total']) ?></div>
                    </div>
                <?php endforeach; ?>
                <hr style="border:none; border-top:1px solid var(--color-border); margin:14px 0;">

                <div class="promo-box">
                    <input type="text" id="promoInput" placeholder="Promo code (e.g. SPORT10)">
                    <button type="button" id="applyPromo" class="btn btn-dark btn-sm">Apply</button>
                </div>
                <div id="promoMsg" class="promo-msg"></div>

                <div class="summary-row"><span>Subtotal</span><span><?= money($subtotal) ?></span></div>
                <div class="summary-row"><span>Shipping</span><span><?= money($shipping) ?></span></div>
                <div class="summary-row" id="discountRow" style="display:none; color:var(--color-success);">
                    <span>Discount</span><span id="discountVal">- RM 0.00</span>
                </div>
                <div class="summary-row total"><span>Total</span><span id="summaryTotal"><?= money($total) ?></span></div>
            </aside>
        </div>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/checkout.js"></script>
<?php include 'includes/footer.php'; ?>
