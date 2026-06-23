<?php
// wishlist page - shows the products the user has hearted
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT p.* FROM wishlist w
                        JOIN products p ON w.product_id = p.product_id
                        WHERE w.user_id = ? ORDER BY w.added_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

$page_title = 'My Wishlist';
$body_page = 'wishlist';
include 'includes/head.php';
include 'includes/header.php';
$flash = get_flash();
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">My Wishlist</h2>
        <p class="section-subtitle">Products you saved for later</p>

        <?php if ($products->num_rows === 0): ?>
            <div class="empty-state">
                <div style="font-size:3rem;">♡</div>
                <h3>Your wishlist is empty</h3>
                <p style="color:#666; margin-bottom:18px;">Tap the heart on any product to save it here.</p>
                <a href="products.php" class="btn btn-primary">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="product-grid">
                <?php while ($p = $products->fetch_assoc()):
                    $rating = product_rating($conn, $p['product_id']);
                ?>
                    <div class="product-card">
                        <button class="wish-btn js-wish active" data-id="<?= $p['product_id'] ?>" title="Remove from wishlist">♥</button>
                        <a href="product-details.php?id=<?= $p['product_id'] ?>">
                            <div class="img-wrap">
                                <img src="<?= product_image_url($p['image']) ?>" alt="<?= sanitize($p['name']) ?>">
                                <?php if ($p['stock'] == 0): ?><span class="stock-badge out">Out of Stock</span><?php endif; ?>
                            </div>
                            <div class="info">
                                <div class="brand"><?= sanitize($p['brand']) ?></div>
                                <div class="name"><?= sanitize($p['name']) ?></div>
                                <div class="rating-row" style="color:#f5a623; font-size:0.85rem;">
                                    <?= render_stars($rating['avg']) ?> <span style="color:#999;">(<?= $rating['count'] ?>)</span>
                                </div>
                                <div class="price"><?= money($p['price']) ?></div>
                            </div>
                        </a>
                        <div style="padding:0 16px 16px;">
                            <a href="product-details.php?id=<?= $p['product_id'] ?>" class="btn btn-dark btn-sm btn-block">View Product</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<input type="hidden" id="csrfToken" value="<?= csrf_token() ?>">
<script src="<?= BASE_URL ?>assets/js/wishlist.js"></script>
<?php include 'includes/footer.php'; ?>
