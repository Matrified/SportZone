<?php
// validates a promo code against the user's cart subtotal and returns the discount (Osman)
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !is_logged_in() || !verify_csrf()) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid request.']);
    exit;
}

$code = strtoupper(trim($_POST['code'] ?? ''));
if ($code === '') {
    echo json_encode(['ok' => false, 'msg' => 'Please enter a code.']);
    exit;
}

// work out the cart subtotal on the server (don't trust the browser)
$stmt = $conn->prepare("SELECT COALESCE(SUM(p.price * c.quantity),0) AS subtotal
                        FROM cart c JOIN products p ON c.product_id = p.product_id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$subtotal = (float) $stmt->get_result()->fetch_assoc()['subtotal'];
$stmt->close();

if ($subtotal <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Your cart is empty.']);
    exit;
}

// look up the code
$stmt = $conn->prepare("SELECT type, value FROM promo_codes WHERE code = ? AND active = 1");
$stmt->bind_param("s", $code);
$stmt->execute();
$promo = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$promo) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid or expired code.']);
    exit;
}

// calculate the discount
if ($promo['type'] === 'percent') {
    $discount = $subtotal * ((float) $promo['value'] / 100);
} else {
    $discount = (float) $promo['value'];
}
if ($discount > $subtotal) {
    $discount = $subtotal;
}

$total = $subtotal + SHIPPING_FEE - $discount;

echo json_encode([
    'ok'       => true,
    'code'     => $code,
    'discount' => number_format($discount, 2),
    'total'    => number_format($total, 2),
    'msg'      => 'Code applied: ' . $code
]);
