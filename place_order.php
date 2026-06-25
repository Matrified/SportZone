<?php
/**
 * SportZone - Place Order handler
 * Member 3 (Osman Omer Gumaa) - Checkout & Order Placement.
 * Server-side validation, transaction, stock decrement, cart clear.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    header("Location: " . BASE_URL . "checkout.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// ---- Server-side validation ----
$full_name   = trim($_POST['full_name'] ?? '');
$phone       = trim($_POST['phone'] ?? '');
$address      = trim($_POST['address'] ?? '');
$city        = trim($_POST['city'] ?? '');
$postal_code = trim($_POST['postal_code'] ?? '');
$payment     = $_POST['payment_method'] ?? 'cod';

$errors = [];
if ($full_name === '')   $errors[] = 'Full name is required.';
if (!preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) $errors[] = 'Valid phone number is required.';
if ($address === '')     $errors[] = 'Address is required.';
if ($city === '')        $errors[] = 'City is required.';
if ($postal_code === '') $errors[] = 'Postal code is required.';
if (!in_array($payment, ['cod', 'card'])) $payment = 'cod';

if ($errors) {
    set_flash('error', implode(' ', $errors));
    header("Location: " . BASE_URL . "checkout.php");
    exit;
}

// ---- Load cart ----
$stmt = $conn->prepare("SELECT c.product_id, c.quantity, c.size, p.name, p.price, p.stock
                        FROM cart c JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

$items = [];
$subtotal = 0;
while ($row = $res->fetch_assoc()) {
    if ($row['quantity'] > $row['stock']) {
        set_flash('error', 'Not enough stock for ' . sanitize($row['name']) . '. Please update your cart.');
        header("Location: " . BASE_URL . "cart.php");
        exit;
    }
    $items[] = $row;
    $subtotal += $row['price'] * $row['quantity'];
}

if (empty($items)) {
    set_flash('error', 'Your cart is empty.');
    header("Location: " . BASE_URL . "cart.php");
    exit;
}

$shipping = SHIPPING_FEE;

// validate promo code on the server (never trust the browser's number)
$discount = 0;
$discountCode = null;
$promoInput = strtoupper(trim($_POST['promo_code'] ?? ''));
if ($promoInput !== '') {
    $pstmt = $conn->prepare("SELECT type, value FROM promo_codes WHERE code = ? AND active = 1");
    $pstmt->bind_param("s", $promoInput);
    $pstmt->execute();
    $promo = $pstmt->get_result()->fetch_assoc();
    $pstmt->close();
    if ($promo) {
        if ($promo['type'] === 'percent') {
            $discount = $subtotal * ((float) $promo['value'] / 100);
        } else {
            $discount = (float) $promo['value'];
        }
        if ($discount > $subtotal) $discount = $subtotal;
        $discountCode = $promoInput;
    }
}

$total = $subtotal + $shipping - $discount;

// ---- Transaction: create order, items, decrement stock, clear cart ----
$conn->begin_transaction();
try {
    $ostmt = $conn->prepare("INSERT INTO orders
        (user_id, full_name, phone, address, city, postal_code, payment_method, subtotal, shipping_fee, discount_code, discount_amount, total, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $ostmt->bind_param("issssssddsdd", $user_id, $full_name, $phone, $address, $city, $postal_code, $payment, $subtotal, $shipping, $discountCode, $discount, $total);
    $ostmt->execute();
    $order_id = $conn->insert_id;
    $ostmt->close();

    $istmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, size, quantity, price) VALUES (?, ?, ?, ?, ?, ?)");
    $dstmt = $conn->prepare("UPDATE products SET stock = stock - ? WHERE product_id = ?");
    foreach ($items as $it) {
        $istmt->bind_param("iissid", $order_id, $it['product_id'], $it['name'], $it['size'], $it['quantity'], $it['price']);
        $istmt->execute();
        $dstmt->bind_param("ii", $it['quantity'], $it['product_id']);
        $dstmt->execute();
    }
    $istmt->close();
    $dstmt->close();

    // Clear cart
    $clr = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $clr->bind_param("i", $user_id);
    $clr->execute();
    $clr->close();

    $conn->commit();
    $_SESSION['last_order_id'] = $order_id;
    header("Location: " . BASE_URL . "order-success.php");
    exit;
} catch (Exception $e) {
    $conn->rollback();
    set_flash('error', 'Order failed. Please try again.');
    header("Location: " . BASE_URL . "checkout.php");
    exit;
}
