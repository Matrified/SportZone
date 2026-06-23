<?php
// Home page
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
$page_title = 'Home';
include 'includes/head.php';
include 'includes/header.php';

// latest products for the featured row
$featured = $conn->query("SELECT * FROM products ORDER BY created_at DESC, product_id DESC LIMIT 8");

// categories for the home grid
$categories = $conn->query("SELECT * FROM categories");

$wishIds = get_wishlist_ids($conn);
?>

<section class="container home-top">
    <!-- hero banner -->
    <div class="hero-banner">
        <img src="assets/images/hero.jpg" alt="" class="hero-bg"
             onerror="this.style.display='none';">
        <div class="hero-text">
            <h1>Gear up for game day</h1>
            <p>Equipment, footwear and apparel for football, basketball, running, gym and more.</p>
            <a href="products.php" class="btn btn-primary">Shop Now</a>
        </div>
    </div>

    <!-- categories -->
    <div class="home-cats">
        <h3>Categories</h3>
        <div class="home-cats-grid">
            <?php while ($cat = $categories->fetch_assoc()): ?>
                <a href="products.php?category=<?= sanitize($cat['slug']) ?>" class="cat-card">
                    <span class="cat-icon"><?= icon('cat-' . $cat['slug'] . '.png', $cat['icon'], $cat['name']) ?></span>
                    <span><?= sanitize($cat['name']) ?></span>
                </a>
            <?php endwhile; ?>
            <a href="products.php?sort=newest" class="cat-card">
                <span class="cat-icon"><?= icon('cat-new.png', '✨', 'New Arrivals') ?></span>
                <span>New Arrivals</span>
            </a>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <h2 class="section-title">Featured Products</h2>
        <p class="section-subtitle">Some of our latest gear</p>
        <div class="product-grid">
            <?php while ($p = $featured->fetch_assoc()): ?>
                <div class="product-card">
                    <?php if (is_logged_in()): $fav = in_array($p['product_id'], $wishIds); ?>
                        <button class="wish-btn js-wish <?= $fav ? 'active' : '' ?>" data-id="<?= $p['product_id'] ?>" title="Wishlist"><?= $fav ? '♥' : '♡' ?></button>
                    <?php endif; ?>
                    <a href="product-details.php?id=<?= $p['product_id'] ?>">
                        <div class="img-wrap">
                            <img src="<?= product_image_url($p['image']) ?>" alt="<?= sanitize($p['name']) ?>">
                            <?php if ($p['stock'] == 0): ?>
                                <span class="stock-badge out">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        <div class="info">
                            <div class="brand"><?= sanitize($p['brand']) ?></div>
                            <div class="name"><?= sanitize($p['name']) ?></div>
                            <div class="price"><?= money($p['price']) ?></div>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
        <div class="text-center mt-2">
            <a href="products.php" class="btn btn-outline">View All Products</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
