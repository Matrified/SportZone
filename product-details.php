<?php
/**
 * SportZone - Product Details Page
 * Member 2 (Ahmed Mahmoud Mohamed) - Product Details, Reviews & Ratings.
 * Member 3 contributes the Add-to-Cart entry point.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$id = (int) ($_GET['id'] ?? 0);

// ---- Handle review submission ----
$reviewError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_review') {
    if (!is_logged_in()) {
        set_flash('error', 'Please log in to write a review.');
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
    if (!verify_csrf()) {
        $reviewError = 'Invalid session token.';
    } else {
        $rating  = (int) ($_POST['rating'] ?? 0);
        $comment = trim($_POST['comment'] ?? '');
        if ($rating < 1 || $rating > 5) {
            $reviewError = 'Please select a star rating.';
        } elseif ($comment === '') {
            $reviewError = 'Please write a short comment.';
        } else {
            $rstmt = $conn->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?, ?, ?, ?)");
            $rstmt->bind_param("iiis", $id, $_SESSION['user_id'], $rating, $comment);
            $rstmt->execute();
            $rstmt->close();
            set_flash('success', 'Thank you! Your review has been posted.');
            header("Location: " . BASE_URL . "product-details.php?id=$id");
            exit;
        }
    }
}

// ---- Fetch product ----
$stmt = $conn->prepare("SELECT p.*, c.name AS category_name, c.slug AS category_slug
                        FROM products p JOIN categories c ON p.category_id = c.category_id
                        WHERE p.product_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$product) {
    $page_title = 'Product Not Found';
    include 'includes/head.php';
    include 'includes/header.php';
    echo '<section class="section"><div class="container empty-state"><h2>Product Not Found</h2><p style="color:#666; margin-bottom:18px;">The product you are looking for does not exist.</p><a href="' . BASE_URL . 'products.php" class="btn btn-primary">Back to Shop</a></div></section>';
    include 'includes/footer.php';
    exit;
}

$rating = product_rating($conn, $id);

// ---- Reviews ----
$rstmt = $conn->prepare("SELECT r.*, u.full_name FROM reviews r JOIN users u ON r.user_id = u.user_id WHERE r.product_id = ? ORDER BY r.created_at DESC");
$rstmt->bind_param("i", $id);
$rstmt->execute();
$reviews = $rstmt->get_result();
$rstmt->close();

// ---- Related products (same category) ----
$relStmt = $conn->prepare("SELECT * FROM products WHERE category_id = ? AND product_id != ? ORDER BY RAND() LIMIT 4");
$relStmt->bind_param("ii", $product['category_id'], $id);
$relStmt->execute();
$related = $relStmt->get_result();
$relStmt->close();

$sizes = $product['sizes'] ? explode(',', $product['sizes']) : [];
$inWish = in_array($id, get_wishlist_ids($conn));

$page_title = $product['name'];
include 'includes/head.php';
include 'includes/header.php';
$flash = get_flash();
?>

<section class="section">
    <div class="container">
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>index.php">Home</a> &rsaquo;
            <a href="<?= BASE_URL ?>products.php?category=<?= sanitize($product['category_slug']) ?>"><?= sanitize($product['category_name']) ?></a> &rsaquo;
            <span><?= sanitize($product['name']) ?></span>
        </nav>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>

        <div class="product-detail">
            <!-- Image gallery -->
            <div class="pd-gallery">
                <div class="pd-main-img">
                    <img src="<?= product_image_url($product['image']) ?>" alt="<?= sanitize($product['name']) ?>" id="mainImage">
                </div>
            </div>

            <!-- Info panel -->
            <div class="pd-info">
                <div class="brand" style="text-transform:uppercase; color:#888; letter-spacing:1px; font-size:0.8rem;"><?= sanitize($product['brand']) ?></div>
                <h1 style="font-size:1.8rem; margin:6px 0;"><?= sanitize($product['name']) ?></h1>

                <div style="display:flex; align-items:center; gap:10px; margin-bottom:14px;">
                    <span style="color:#f5a623; font-size:1.1rem;"><?= render_stars($rating['avg']) ?></span>
                    <span style="color:#777; font-size:0.9rem;"><?= number_format($rating['avg'], 1) ?> (<?= $rating['count'] ?> reviews)</span>
                </div>

                <div class="pd-price"><?= money($product['price']) ?></div>

                <p class="pd-stock <?= $product['stock'] > 0 ? 'in' : 'out' ?>">
                    <?= $product['stock'] > 0 ? '● In Stock (' . $product['stock'] . ' available)' : '● Out of Stock' ?>
                </p>

                <p style="color:#555; margin:16px 0; line-height:1.7;"><?= nl2br(sanitize($product['description'])) ?></p>

                <?php if ($product['stock'] > 0): ?>
                <form method="POST" action="<?= BASE_URL ?>add_to_cart.php" class="pd-form">
                    <?= csrf_field() ?>
                    <input type="hidden" name="product_id" value="<?= $product['product_id'] ?>">

                    <?php if ($sizes): ?>
                        <div class="form-group" style="max-width:200px;">
                            <label for="size">Size</label>
                            <select name="size" id="size" required>
                                <option value="">Select size</option>
                                <?php foreach ($sizes as $s): ?>
                                    <option value="<?= sanitize(trim($s)) ?>"><?= sanitize(trim($s)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="form-group" style="max-width:140px;">
                        <label for="quantity">Quantity</label>
                        <input type="number" name="quantity" id="quantity" value="1" min="1" max="<?= $product['stock'] ?>">
                    </div>

                    <div style="display:flex; gap:12px; margin-top:8px;">
                        <button type="submit" class="btn btn-primary">Add to Cart</button>
                        <button type="submit" name="buy_now" value="1" class="btn btn-dark">Buy Now</button>
                    </div>
                </form>
                <?php else: ?>
                    <button class="btn btn-outline" disabled>Out of Stock</button>
                <?php endif; ?>

                <?php if (is_logged_in()): ?>
                    <button type="button" class="btn btn-outline wish-toggle js-wish <?= $inWish ? 'active' : '' ?>"
                            data-id="<?= $product['product_id'] ?>" style="margin-top:12px;">
                        <span class="wl-heart"><?= $inWish ? '♥' : '♡' ?></span>
                        <span class="wl-text"><?= $inWish ? 'Saved to Wishlist' : 'Add to Wishlist' ?></span>
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tabs: Description + Reviews -->
        <div class="pd-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="desc">Description</button>
                <button class="tab-btn" data-tab="reviews">Reviews (<?= $rating['count'] ?>)</button>
            </div>

            <div class="tab-content active" id="tab-desc">
                <p style="line-height:1.8; color:#444;"><?= nl2br(sanitize($product['description'])) ?></p>
                <ul style="margin-top:14px; color:#444;">
                    <li><strong>Brand:</strong> <?= sanitize($product['brand']) ?></li>
                    <li><strong>Category:</strong> <?= sanitize($product['category_name']) ?></li>
                    <?php if ($sizes): ?><li><strong>Available sizes:</strong> <?= sanitize($product['sizes']) ?></li><?php endif; ?>
                </ul>
            </div>

            <div class="tab-content" id="tab-reviews">
                <?php if (!empty($reviewError)): ?>
                    <div class="alert alert-error"><?= sanitize($reviewError) ?></div>
                <?php endif; ?>

                <?php if ($reviews->num_rows === 0): ?>
                    <p style="color:#666;">No reviews yet. Be the first to review this product!</p>
                <?php else: ?>
                    <div class="review-list">
                        <?php while ($r = $reviews->fetch_assoc()): ?>
                            <div class="review-item">
                                <div class="flex-between">
                                    <strong><?= sanitize($r['full_name']) ?></strong>
                                    <span style="color:#999; font-size:0.8rem;"><?= date('d M Y', strtotime($r['created_at'])) ?></span>
                                </div>
                                <div style="color:#f5a623;"><?= render_stars($r['rating']) ?></div>
                                <p style="color:#555; margin-top:4px;"><?= nl2br(sanitize($r['comment'])) ?></p>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>

                <!-- Add review form -->
                <div class="review-form-wrap">
                    <h4 style="margin-bottom:12px;">Write a Review</h4>
                    <?php if (is_logged_in()): ?>
                        <form method="POST" action="product-details.php?id=<?= $id ?>" id="reviewForm">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="add_review">
                            <input type="hidden" name="rating" id="ratingValue" value="0">

                            <div class="form-group">
                                <label>Your Rating</label>
                                <div class="star-input" id="starInput">
                                    <span data-value="1">☆</span>
                                    <span data-value="2">☆</span>
                                    <span data-value="3">☆</span>
                                    <span data-value="4">☆</span>
                                    <span data-value="5">☆</span>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="comment">Your Review</label>
                                <textarea name="comment" id="comment" rows="3" placeholder="Share your thoughts about this product..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Submit Review</button>
                        </form>
                    <?php else: ?>
                        <p style="color:#666;"><a href="login.php" style="color:var(--color-accent); font-weight:600;">Log in</a> to write a review.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Related products -->
        <?php if ($related->num_rows > 0): ?>
            <h2 class="section-title" style="margin-top:50px;">Related Products</h2>
            <div class="product-grid">
                <?php while ($rp = $related->fetch_assoc()): ?>
                    <a href="product-details.php?id=<?= $rp['product_id'] ?>" class="product-card">
                        <div class="img-wrap">
                            <img src="<?= product_image_url($rp['image']) ?>" alt="<?= sanitize($rp['name']) ?>">
                        </div>
                        <div class="info">
                            <div class="brand"><?= sanitize($rp['brand']) ?></div>
                            <div class="name"><?= sanitize($rp['name']) ?></div>
                            <div class="price"><?= money($rp['price']) ?></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/product.js"></script>
<?php include 'includes/footer.php'; ?>
