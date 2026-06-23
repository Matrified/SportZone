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
$total = $subtotal + $shipping;

// ---- Transaction: create order, items, decrement stock, clear cart ----
$conn->begin_transaction();
try {
    $ostmt = $conn->prepare("INSERT INTO orders
        (user_id, full_name, phone, address, city, postal_code, payment_method, subtotal, shipping_fee, total, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')");
    $ostmt->bind_param("issssssddd", $user_id, $full_name, $phone, $address, $city, $postal_code, $payment, $subtotal, $shipping, $total);
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
