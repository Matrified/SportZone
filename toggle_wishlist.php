<?php
// add or remove a product from the user's wishlist (Osman / extra feature)
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'Bad request']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['ok' => false, 'login' => true, 'msg' => 'Please log in first.']);
    exit;
}

if (!verify_csrf()) {
    echo json_encode(['ok' => false, 'msg' => 'Invalid token']);
    exit;
}

$user_id    = $_SESSION['user_id'];
$product_id = (int) ($_POST['product_id'] ?? 0);

// make sure product exists
$check = $conn->prepare("SELECT product_id FROM products WHERE product_id = ?");
$check->bind_param("i", $product_id);
$check->execute();
if ($check->get_result()->num_rows === 0) {
    $check->close();
    echo json_encode(['ok' => false, 'msg' => 'Product not found']);
    exit;
}
$check->close();

// already in wishlist?
$q = $conn->prepare("SELECT wishlist_id FROM wishlist WHERE user_id = ? AND product_id = ?");
$q->bind_param("ii", $user_id, $product_id);
$q->execute();
$exists = $q->get_result()->fetch_assoc();
$q->close();

if ($exists) {
    $del = $conn->prepare("DELETE FROM wishlist WHERE user_id = ? AND product_id = ?");
    $del->bind_param("ii", $user_id, $product_id);
    $del->execute();
    $del->close();
    $state = 'removed';
} else {
    $ins = $conn->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $ins->bind_param("ii", $user_id, $product_id);
    $ins->execute();
    $ins->close();
    $state = 'added';
}

echo json_encode(['ok' => true, 'state' => $state, 'count' => get_wishlist_count($conn)]);
