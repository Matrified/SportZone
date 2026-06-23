<?php
/**
 * SportZone - Update / Remove cart item handler
 * Member 3 (Osman Omer Gumaa) - Shopping Cart System.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    header("Location: " . BASE_URL . "cart.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? '';
$cart_id = (int) ($_POST['cart_id'] ?? 0);

if ($action === 'remove') {
    $stmt = $conn->prepare("DELETE FROM cart WHERE cart_id = ? AND user_id = ?");
    $stmt->bind_param("ii", $cart_id, $user_id);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Item removed from cart.');
}
elseif ($action === 'update') {
    $quantity = max(1, (int) ($_POST['quantity'] ?? 1));
    // Clamp to available stock
    $s = $conn->prepare("SELECT p.stock FROM cart c JOIN products p ON c.product_id = p.product_id WHERE c.cart_id = ? AND c.user_id = ?");
    $s->bind_param("ii", $cart_id, $user_id);
    $s->execute();
    $row = $s->get_result()->fetch_assoc();
    $s->close();
    if ($row) {
        $quantity = min($quantity, max(1, (int) $row['stock']));
        $upd = $conn->prepare("UPDATE cart SET quantity = ? WHERE cart_id = ? AND user_id = ?");
        $upd->bind_param("iii", $quantity, $cart_id, $user_id);
        $upd->execute();
        $upd->close();
    }
}
elseif ($action === 'clear') {
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: " . BASE_URL . "cart.php");
exit;
