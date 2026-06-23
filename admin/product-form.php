<?php
/**
 * SportZone - Admin Add / Edit Product
 * Member 4 (Mohamed Tarek) - Product CRUD (Create + Update) with image upload.
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

$id = (int) ($_GET['id'] ?? 0);
$isEdit = $id > 0;
$errors = [];
$product = [
    'name' => '', 'description' => '', 'category_id' => '',
    'brand' => '', 'price' => '', 'stock' => '', 'sizes' => '', 'image' => ''
];

// Load existing product for edit
if ($isEdit) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $found = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$found) {
        set_flash('error', 'Product not found.');
        header("Location: " . BASE_URL . "admin/products.php");
        exit;
    }
    $product = $found;
}

$categories = $conn->query("SELECT * FROM categories ORDER BY name");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['general'] = 'Invalid session token.';
    } else {
        $product['name']        = trim($_POST['name'] ?? '');
        $product['description'] = trim($_POST['description'] ?? '');
        $product['category_id'] = (int) ($_POST['category_id'] ?? 0);
        $product['brand']       = trim($_POST['brand'] ?? '');
        $product['price']       = $_POST['price'] ?? '';
        $product['stock']       = $_POST['stock'] ?? '';
        $product['sizes']       = trim($_POST['sizes'] ?? '');

        if ($product['name'] === '')                 $errors['name'] = 'Product name is required.';
        if ($product['category_id'] <= 0)            $errors['category_id'] = 'Please select a category.';
        if (!is_numeric($product['price']) || $product['price'] < 0) $errors['price'] = 'Enter a valid price.';
        if (!is_numeric($product['stock']) || $product['stock'] < 0)  $errors['stock'] = 'Enter a valid stock quantity.';

        // ---- Image upload (optional) ----
        $imageName = $product['image'] ?? '';
        if (!empty($_FILES['image']['name'])) {
            $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp', 'image/gif' => 'gif'];
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $_FILES['image']['tmp_name']);
            finfo_close($finfo);

            if (!isset($allowed[$mime])) {
                $errors['image'] = 'Image must be JPG, PNG, WEBP or GIF.';
            } elseif ($_FILES['image']['size'] > 3 * 1024 * 1024) {
                $errors['image'] = 'Image must be under 3MB.';
            } else {
                if (!is_dir(UPLOAD_PATH)) mkdir(UPLOAD_PATH, 0777, true);
                $imageName = 'prod_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
                move_uploaded_file($_FILES['image']['tmp_name'], UPLOAD_PATH . $imageName);
            }
        }

        if (empty($errors)) {
            $price = (float) $product['price'];
            $stock = (int) $product['stock'];
            if ($isEdit) {
                $stmt = $conn->prepare("UPDATE products SET name=?, description=?, category_id=?, brand=?, price=?, stock=?, sizes=?, image=? WHERE product_id=?");
                $stmt->bind_param("ssisdissi", $product['name'], $product['description'], $product['category_id'], $product['brand'], $price, $stock, $product['sizes'], $imageName, $id);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Product updated successfully.');
            } else {
                $stmt = $conn->prepare("INSERT INTO products (name, description, category_id, brand, price, stock, sizes, image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssisdiss", $product['name'], $product['description'], $product['category_id'], $product['brand'], $price, $stock, $product['sizes'], $imageName);
                $stmt->execute();
                $stmt->close();
                set_flash('success', 'Product added successfully.');
            }
            header("Location: " . BASE_URL . "admin/products.php");
            exit;
        }
    }
}

$page_title = $isEdit ? 'Edit Product' : 'Add Product';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="panel" style="max-width:720px;">
    <?php if (!empty($errors['general'])): ?>
        <div class="alert alert-error"><?= sanitize($errors['general']) ?></div>
    <?php endif; ?>

    <form method="POST" action="product-form.php<?= $isEdit ? '?id=' . $id : '' ?>" enctype="multipart/form-data" novalidate>
        <?= csrf_field() ?>

        <div class="form-group <?= isset($errors['name']) ? 'invalid' : '' ?>">
            <label for="name">Product Name</label>
            <input type="text" id="name" name="name" value="<?= sanitize($product['name']) ?>">
            <span class="error-text"><?= $errors['name'] ?? '' ?></span>
        </div>

        <div class="form-group">
            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"><?= sanitize($product['description']) ?></textarea>
        </div>

        <div style="display:flex; gap:14px;">
            <div class="form-group <?= isset($errors['category_id']) ? 'invalid' : '' ?>" style="flex:1;">
                <label for="category_id">Category</label>
                <select id="category_id" name="category_id">
                    <option value="">Select category</option>
                    <?php while ($c = $categories->fetch_assoc()): ?>
                        <option value="<?= $c['category_id'] ?>" <?= $product['category_id'] == $c['category_id'] ? 'selected' : '' ?>><?= sanitize($c['name']) ?></option>
                    <?php endwhile; ?>
                </select>
                <span class="error-text"><?= $errors['category_id'] ?? '' ?></span>
            </div>
            <div class="form-group" style="flex:1;">
                <label for="brand">Brand</label>
                <input type="text" id="brand" name="brand" value="<?= sanitize($product['brand']) ?>">
            </div>
        </div>

        <div style="display:flex; gap:14px;">
            <div class="form-group <?= isset($errors['price']) ? 'invalid' : '' ?>" style="flex:1;">
                <label for="price">Price ($)</label>
                <input type="number" step="0.01" id="price" name="price" value="<?= sanitize($product['price']) ?>">
                <span class="error-text"><?= $errors['price'] ?? '' ?></span>
            </div>
            <div class="form-group <?= isset($errors['stock']) ? 'invalid' : '' ?>" style="flex:1;">
                <label for="stock">Stock Quantity</label>
                <input type="number" id="stock" name="stock" value="<?= sanitize($product['stock']) ?>">
                <span class="error-text"><?= $errors['stock'] ?? '' ?></span>
            </div>
        </div>

        <div class="form-group">
            <label for="sizes">Sizes (comma separated, optional)</label>
            <input type="text" id="sizes" name="sizes" value="<?= sanitize($product['sizes']) ?>" placeholder="e.g. S,M,L,XL or 7,8,9,10">
        </div>

        <div class="form-group <?= isset($errors['image']) ? 'invalid' : '' ?>">
            <label for="image">Product Image</label>
            <input type="file" id="image" name="image" accept="image/*">
            <span class="error-text"><?= $errors['image'] ?? '' ?></span>
            <div style="margin-top:12px;">
                <img id="imagePreview" src="<?= product_image_url($product['image']) ?>" alt="Preview" style="width:120px; height:120px; object-fit:cover; border-radius:8px; border:1px solid var(--color-border);">
            </div>
        </div>

        <div style="display:flex; gap:12px;">
            <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Product' : 'Add Product' ?></button>
            <a href="<?= BASE_URL ?>admin/products.php" class="btn btn-outline">Cancel</a>
        </div>
    </form>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
