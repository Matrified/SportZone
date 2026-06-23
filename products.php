<?php
/**
 * SportZone - Product Listing Page
 * Member 2 (Ahmed Mahmoud Mohamed) - Product Browsing & Categorization,
 * Search & Filter System.
 * Supports: search, category filter, brand filter, price range, sorting, pagination.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// ---- Read filters from query string ----
$search    = trim($_GET['search'] ?? '');
$catSlug   = trim($_GET['category'] ?? '');
$brand     = trim($_GET['brand'] ?? '');
$minPrice  = isset($_GET['min_price']) && $_GET['min_price'] !== '' ? (float) $_GET['min_price'] : null;
$maxPrice  = isset($_GET['max_price']) && $_GET['max_price'] !== '' ? (float) $_GET['max_price'] : null;
$sort      = $_GET['sort'] ?? 'newest';
$page      = max(1, (int) ($_GET['page'] ?? 1));
$perPage   = 8;
$offset    = ($page - 1) * $perPage;

// ---- Build dynamic WHERE clause with bound params (prevents SQL injection) ----
$where  = [];
$params = [];
$types  = '';

if ($search !== '') {
    $where[] = "(p.name LIKE ? OR p.brand LIKE ? OR p.description LIKE ?)";
    $like = "%$search%";
    $params[] = $like; $params[] = $like; $params[] = $like;
    $types .= 'sss';
}

$activeCat = null;
if ($catSlug !== '') {
    $cstmt = $conn->prepare("SELECT * FROM categories WHERE slug = ?");
    $cstmt->bind_param("s", $catSlug);
    $cstmt->execute();
    $activeCat = $cstmt->get_result()->fetch_assoc();
    $cstmt->close();
    if ($activeCat) {
        $where[] = "p.category_id = ?";
        $params[] = $activeCat['category_id'];
        $types .= 'i';
    }
}

if ($brand !== '') {
    $where[] = "p.brand = ?";
    $params[] = $brand;
    $types .= 's';
}

if ($minPrice !== null) {
    $where[] = "p.price >= ?";
    $params[] = $minPrice;
    $types .= 'd';
}
if ($maxPrice !== null) {
    $where[] = "p.price <= ?";
    $params[] = $maxPrice;
    $types .= 'd';
}

$whereSql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

// ---- Sorting (whitelist to avoid injection) ----
switch ($sort) {
    case 'price_low':  $orderSql = 'ORDER BY p.price ASC';  break;
    case 'price_high': $orderSql = 'ORDER BY p.price DESC'; break;
    case 'name':       $orderSql = 'ORDER BY p.name ASC';   break;
    case 'rating':     $orderSql = 'ORDER BY (SELECT COALESCE(AVG(r.rating),0) FROM reviews r WHERE r.product_id = p.product_id) DESC, p.created_at DESC'; break;
    case 'popular':    $orderSql = 'ORDER BY (SELECT COALESCE(SUM(oi.quantity),0) FROM order_items oi WHERE oi.product_id = p.product_id) DESC, p.created_at DESC'; break;
    default:           $orderSql = 'ORDER BY p.created_at DESC'; break;
}

// ---- Count total for pagination ----
$countSql = "SELECT COUNT(*) AS total FROM products p $whereSql";
$cstmt = $conn->prepare($countSql);
if ($types) $cstmt->bind_param($types, ...$params);
$cstmt->execute();
$totalRows = (int) $cstmt->get_result()->fetch_assoc()['total'];
$cstmt->close();
$totalPages = max(1, ceil($totalRows / $perPage));

// ---- Fetch products for current page ----
$sql = "SELECT p.*, c.name AS category_name FROM products p
        JOIN categories c ON p.category_id = c.category_id
        $whereSql $orderSql LIMIT ? OFFSET ?";
$stmt = $conn->prepare($sql);
$pageParams = $params;
$pageTypes  = $types . 'ii';
$pageParams[] = $perPage;
$pageParams[] = $offset;
$stmt->bind_param($pageTypes, ...$pageParams);
$stmt->execute();
$products = $stmt->get_result();
$stmt->close();

// ---- Data for filter UI ----
$allCategories = $conn->query("SELECT * FROM categories ORDER BY name");
$allBrands = $conn->query("SELECT DISTINCT brand FROM products WHERE brand IS NOT NULL ORDER BY brand");

$wishIds = get_wishlist_ids($conn);

// Helper to build query strings preserving filters
function build_query($overrides = []) {
    $params = array_merge($_GET, $overrides);
    return '?' . http_build_query(array_filter($params, fn($v) => $v !== '' && $v !== null));
}

$page_title = $activeCat ? $activeCat['name'] : ($search !== '' ? "Search: $search" : 'Shop');
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="section">
    <div class="container">
        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="<?= BASE_URL ?>index.php">Home</a> &rsaquo;
            <span><?= $activeCat ? sanitize($activeCat['name']) : 'All Products' ?></span>
        </nav>

        <div class="flex-between mb-2" style="flex-wrap:wrap; gap:12px;">
            <div>
                <h2 class="section-title" style="margin-bottom:2px;"><?= $activeCat ? sanitize($activeCat['name']) : ($search !== '' ? 'Search Results' : 'All Products') ?></h2>
                <p style="color:#666;"><?= $totalRows ?> product<?= $totalRows != 1 ? 's' : '' ?> found<?= $search !== '' ? ' for "' . sanitize($search) . '"' : '' ?></p>
            </div>
            <button class="btn btn-outline btn-sm filter-toggle" id="filterToggle">☰ Filters</button>
        </div>

        <div class="shop-layout">
            <!-- Sidebar filters -->
            <aside class="filter-panel" id="filterPanel">
                <form method="GET" action="products.php">
                    <?php if ($search !== ''): ?>
                        <input type="hidden" name="search" value="<?= sanitize($search) ?>">
                    <?php endif; ?>

                    <div class="filter-group">
                        <h4>Category</h4>
                        <ul class="filter-list">
                            <li><a href="products.php" class="<?= !$activeCat ? 'active' : '' ?>">All Categories</a></li>
                            <?php while ($c = $allCategories->fetch_assoc()): ?>
                                <li><a href="products.php?category=<?= sanitize($c['slug']) ?>" class="<?= ($activeCat && $activeCat['category_id'] == $c['category_id']) ? 'active' : '' ?>"><?= sanitize($c['name']) ?></a></li>
                            <?php endwhile; ?>
                        </ul>
                    </div>

                    <?php if ($catSlug !== ''): ?>
                        <input type="hidden" name="category" value="<?= sanitize($catSlug) ?>">
                    <?php endif; ?>

                    <div class="filter-group">
                        <h4>Price Range</h4>
                        <div style="display:flex; gap:8px;">
                            <input type="number" name="min_price" placeholder="Min" min="0" value="<?= $minPrice !== null ? $minPrice : '' ?>" style="width:50%; padding:8px; border:1px solid var(--color-border); border-radius:6px;">
                            <input type="number" name="max_price" placeholder="Max" min="0" value="<?= $maxPrice !== null ? $maxPrice : '' ?>" style="width:50%; padding:8px; border:1px solid var(--color-border); border-radius:6px;">
                        </div>
                    </div>

                    <div class="filter-group">
                        <h4>Brand</h4>
                        <?php while ($b = $allBrands->fetch_assoc()): ?>
                            <label class="check-row">
                                <input type="radio" name="brand" value="<?= sanitize($b['brand']) ?>" <?= $brand === $b['brand'] ? 'checked' : '' ?>>
                                <?= sanitize($b['brand']) ?>
                            </label>
                        <?php endwhile; ?>
                        <label class="check-row">
                            <input type="radio" name="brand" value="" <?= $brand === '' ? 'checked' : '' ?>> All Brands
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block">Apply Filters</button>
                    <a href="products.php" class="btn btn-outline btn-block" style="margin-top:10px;">Reset</a>
                </form>
            </aside>

            <!-- Product results -->
            <div class="shop-main">
                <div class="sort-bar">
                    <form method="GET" action="products.php" id="sortForm">
                        <?php foreach (['search' => $search, 'category' => $catSlug, 'brand' => $brand, 'min_price' => $minPrice, 'max_price' => $maxPrice] as $k => $v): ?>
                            <?php if ($v !== '' && $v !== null): ?><input type="hidden" name="<?= $k ?>" value="<?= sanitize($v) ?>"><?php endif; ?>
                        <?php endforeach; ?>
                        <label for="sort" style="font-size:0.9rem; font-weight:600;">Sort by:</label>
                        <select name="sort" id="sort" onchange="document.getElementById('sortForm').submit()">
                            <option value="newest"     <?= $sort === 'newest' ? 'selected' : '' ?>>Newest</option>
                            <option value="popular"    <?= $sort === 'popular' ? 'selected' : '' ?>>Best Selling</option>
                            <option value="rating"     <?= $sort === 'rating' ? 'selected' : '' ?>>Top Rated</option>
                            <option value="price_low"  <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                            <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                            <option value="name"       <?= $sort === 'name' ? 'selected' : '' ?>>Name (A-Z)</option>
                        </select>
                    </form>
                </div>

                <?php if ($products->num_rows === 0): ?>
                    <div class="empty-state">
                        <div style="font-size:3rem;">🔍</div>
                        <h3>No products found</h3>
                        <p style="color:#666;">Try adjusting your filters or search terms.</p>
                    </div>
                <?php else: ?>
                    <div class="product-grid">
                        <?php while ($p = $products->fetch_assoc()):
                            $rating = product_rating($conn, $p['product_id']);
                        ?>
                            <div class="product-card">
                                <?php if (is_logged_in()): $fav = in_array($p['product_id'], $wishIds); ?>
                                    <button class="wish-btn js-wish <?= $fav ? 'active' : '' ?>" data-id="<?= $p['product_id'] ?>" title="Wishlist"><?= $fav ? '♥' : '♡' ?></button>
                                <?php endif; ?>
                                <a href="product-details.php?id=<?= $p['product_id'] ?>">
                                    <div class="img-wrap">
                                        <img src="<?= product_image_url($p['image']) ?>" alt="<?= sanitize($p['name']) ?>">
                                        <span class="stock-badge <?= $p['stock'] == 0 ? 'out' : '' ?>"><?= $p['stock'] > 0 ? 'In Stock' : 'Out of Stock' ?></span>
                                    </div>
                                    <div class="info">
                                        <div class="brand"><?= sanitize($p['brand']) ?></div>
                                        <div class="name"><?= sanitize($p['name']) ?></div>
                                        <div class="rating-row" style="color:#f5a623; font-size:0.85rem;">
                                            <?= render_stars($rating['avg']) ?>
                                            <span style="color:#999;">(<?= $rating['count'] ?>)</span>
                                        </div>
                                        <div class="price"><?= money($p['price']) ?></div>
                                        <span class="btn btn-dark btn-sm btn-block">View Product</span>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile; ?>
                    </div>

                    <!-- Pagination -->
                    <?php if ($totalPages > 1): ?>
                        <div class="pagination">
                            <?php if ($page > 1): ?>
                                <a href="<?= build_query(['page' => $page - 1]) ?>">&laquo; Prev</a>
                            <?php endif; ?>
                            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                                <a href="<?= build_query(['page' => $i]) ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
                            <?php endfor; ?>
                            <?php if ($page < $totalPages): ?>
                                <a href="<?= build_query(['page' => $page + 1]) ?>">Next &raquo;</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/shop.js"></script>
<?php include 'includes/footer.php'; ?>
