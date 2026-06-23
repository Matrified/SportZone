<?php
/**
 * SportZone - Admin Category Management
 * Member 4 (Mohamed Tarek) - Category Management (CRUD).
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$errors = [];

// Add category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add' && verify_csrf()) {
    $name = trim($_POST['name'] ?? '');
    $icon = trim($_POST['icon'] ?? '');
    $slug = strtolower(preg_replace('/[^a-z0-9]+/i', '-', $name));
    if ($name === '') {
        $errors['name'] = 'Category name is required.';
    } else {
        $stmt = $conn->prepare("INSERT INTO categories (name, slug, icon) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $slug, $icon);
        if ($stmt->execute()) {
            set_flash('success', 'Category added.');
            header("Location: " . BASE_URL . "admin/categories.php");
            exit;
        } else {
            $errors['name'] = 'A category with that name/slug already exists.';
        }
        $stmt->close();
    }
}

// Delete category
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && verify_csrf()) {
    $cid = (int) ($_POST['category_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $cid);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Category deleted (its products were also removed).');
    header("Location: " . BASE_URL . "admin/categories.php");
    exit;
}

$categories = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.category_id = c.category_id) AS product_count FROM categories c ORDER BY c.name");

$page_title = 'Category Management';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="admin-grid-2">
    <div class="panel">
        <h3 style="margin-bottom:16px;">Add Category</h3>
        <form method="POST" action="categories.php">
            <?= csrf_field() ?>
            <input type="hidden" name="action" value="add">
            <div class="form-group <?= isset($errors['name']) ? 'invalid' : '' ?>">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" placeholder="e.g. Tennis">
                <span class="error-text"><?= $errors['name'] ?? '' ?></span>
            </div>
            <div class="form-group">
                <label for="icon">Icon (emoji, optional)</label>
                <input type="text" id="icon" name="icon" placeholder="🎾" maxlength="4">
            </div>
            <button type="submit" class="btn btn-primary">Add Category</button>
        </form>
    </div>

    <div class="panel">
        <h3 style="margin-bottom:16px;">Existing Categories</h3>
        <div class="table-wrap" style="border:none;">
            <table class="data-table">
                <thead><tr><th>Icon</th><th>Name</th><th>Products</th><th></th></tr></thead>
                <tbody>
                <?php while ($c = $categories->fetch_assoc()): ?>
                    <tr>
                        <td style="font-size:1.3rem;"><?= $c['icon'] ?></td>
                        <td><?= sanitize($c['name']) ?></td>
                        <td><?= $c['product_count'] ?></td>
                        <td>
                            <form method="POST" action="categories.php" onsubmit="return confirm('Delete this category and ALL its products?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="category_id" value="<?= $c['category_id'] ?>">
                                <button type="submit" class="btn btn-sm" style="background:var(--color-danger); color:#fff;">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
