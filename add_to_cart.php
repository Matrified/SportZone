<?php
/**
 * SportZone - Add to Cart handler
 * Member 3 (Osman Omer Gumaa) - Shopping Cart System.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: " . BASE_URL . "products.php");
    exit;
}

if (!is_logged_in()) {
    set_flash('error', 'Please log in to add items to your cart.');
    header("Location: " . BASE_URL . "login.php");
    exit;
}

if (!verify_csrf()) {
    set_flash('error', 'Invalid session token.');
    header("Location: " . BASE_URL . "products.php");
    exit;
}

$user_id    = $_SESSION['user_id'];
$product_id = (int) ($_POST['product_id'] ?? 0);
$size       = trim($_POST['size'] ?? '');
$quantity   = max(1, (int) ($_POST['quantity'] ?? 1));
$buyNow     = isset($_POST['buy_now']);

// Validate product + stock
$stmt = $conn->prepare("SELECT name, stock FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    set_flash('error', 'Product not found.');
    header("Location: " . BASE_URL . "products.php");
    exit;
}
if ($product['stock'] < 1) {
    set_flash('error', 'This product is out of stock.');
    header("Location: " . BASE_URL . "product-details.php?id=$product_id");
    exit;
}
if ($quantity > $product['stock']) {
    $quantity = $product['stock'];
}

// Normalize: store NULL (not empty string) when product has no size option.
$sizeVal = $size !== '' ? $size : null;

// Already in cart (same product + size)? Update quantity, else insert.
// The <=> NULL-safe equality operator matches NULL sizes correctly.
$check = $conn->prepare("SELECT cart_id, quantity FROM cart WHERE user_id = ? AND product_id = ? AND size <=> ?");
$check->bind_param("iis", $user_id, $product_id, $sizeVal);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

if ($existing) {
    $newQty = min($product['stock'], $existing['quantity'] + $quantity);
    $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ?");
    $upd->bind_param("ii", $newQty, $existing['cart_id']);
    $upd->execute();
    $upd->close();
} else {
    $ins = $conn->prepare("INSERT INTO cart (user_id, product_id, size, quantity) VALUES (?, ?, ?, ?)");
    $ins->bind_param("iisi", $user_id, $product_id, $sizeVal, $quantity);
    $ins->execute();
    $ins->close();
}

if ($buyNow) {
    header("Location: " . BASE_URL . "checkout.php");
    exit;
}

set_flash('success', sanitize($product['name']) . ' added to your cart.');
header("Location: " . BASE_URL . "cart.php");
exit;
