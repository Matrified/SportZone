<?php
// let a customer cancel their own order while it is still Pending (Osman)
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf()) {
    header("Location: " . BASE_URL . "orders.php");
    exit;
}

$user_id  = $_SESSION['user_id'];
$order_id = (int) ($_POST['order_id'] ?? 0);

// make sure the order belongs to this user and is still pending
$stmt = $conn->prepare("SELECT status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$order) {
    set_flash('error', 'Order not found.');
    header("Location: " . BASE_URL . "orders.php");
    exit;
}

if ($order['status'] !== 'Pending') {
    set_flash('error', 'Only pending orders can be cancelled.');
    header("Location: " . BASE_URL . "orders.php");
    exit;
}

// cancel and put the stock back
$conn->begin_transaction();
try {
    $upd = $conn->prepare("UPDATE orders SET status = 'Cancelled' WHERE order_id = ?");
    $upd->bind_param("i", $order_id);
    $upd->execute();
    $upd->close();

    // return stock for each item that still maps to a product
    $items = $conn->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ? AND product_id IS NOT NULL");
    $items->bind_param("i", $order_id);
    $items->execute();
    $res = $items->get_result();
    $restock = $conn->prepare("UPDATE products SET stock = stock + ? WHERE product_id = ?");
    while ($row = $res->fetch_assoc()) {
        $restock->bind_param("ii", $row['quantity'], $row['product_id']);
        $restock->execute();
    }
    $restock->close();
    $items->close();

    $conn->commit();
    set_flash('success', 'Order #' . str_pad($order_id, 5, '0', STR_PAD_LEFT) . ' has been cancelled.');
} catch (Exception $e) {
    $conn->rollback();
    set_flash('error', 'Could not cancel the order. Please try again.');
}

header("Location: " . BASE_URL . "orders.php");
exit;
