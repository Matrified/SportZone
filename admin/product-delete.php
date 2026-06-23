<?php
/**
 * SportZone - Admin Delete Product
 * Member 4 (Mohamed Tarek) - Product CRUD (Delete).
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verify_csrf()) {
    $id = (int) ($_POST['id'] ?? 0);

    // Remove image file if present
    $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($row) {
        if (!empty($row['image']) && file_exists(UPLOAD_PATH . $row['image'])) {
            @unlink(UPLOAD_PATH . $row['image']);
        }
        $del = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $del->bind_param("i", $id);
        $del->execute();
        $del->close();
        set_flash('success', 'Product deleted successfully.');
    }
}

header("Location: " . BASE_URL . "admin/products.php");
exit;
