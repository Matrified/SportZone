<?php
/**
 * SportZone - Shared Header / Navbar
 * Requires: config.php, db_connect.php, functions.php already included
 * by the page that includes this file.
 */
$cart_count = get_cart_count($conn);
?>
<header class="site-header">
    <div class="container navbar">
        <a href="<?= BASE_URL ?>index.php" class="logo">SPORT<span>ZONE</span></a>

        <button class="hamburger" id="hamburgerBtn">&#9776;</button>

        <nav>
            <ul class="nav-links" id="navLinks">
                <li><a href="<?= BASE_URL ?>index.php">Home</a></li>
                <li><a href="<?= BASE_URL ?>products.php">Shop</a></li>
                <li><a href="<?= BASE_URL ?>group.php">Our Team</a></li>
                <?php if (is_logged_in()): ?>
                    <li><a href="<?= BASE_URL ?>orders.php">My Orders</a></li>
                <?php endif; ?>
                <?php if (is_admin()): ?>
                    <li><a href="<?= BASE_URL ?>admin/dashboard.php">Admin Panel</a></li>
                <?php endif; ?>
            </ul>
        </nav>

        <form class="search-bar" action="<?= BASE_URL ?>products.php" method="GET">
            <input type="text" name="search" placeholder="Search for products..." value="<?= isset($_GET['search']) ? sanitize($_GET['search']) : '' ?>">
            <button type="submit" title="Search"><?= icon('search.png', '🔍', 'Search') ?></button>
        </form>

        <div class="nav-icons">
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>wishlist.php" class="icon-link" title="Wishlist">
                    <?= icon('heart.png', '♡', 'Wishlist') ?>
                    <?php $wc = get_wishlist_count($conn); ?>
                    <span class="cart-badge" id="wishBadge" style="<?= $wc > 0 ? '' : 'display:none;' ?>"><?= $wc ?></span>
                </a>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>cart.php" class="icon-link" title="Cart">
                <?= icon('cart.png', '🛒', 'Cart') ?>
                <?php if ($cart_count > 0): ?>
                    <span class="cart-badge"><?= $cart_count ?></span>
                <?php endif; ?>
            </a>
            <?php if (is_logged_in()): ?>
                <a href="<?= BASE_URL ?>profile.php" class="icon-link" title="My Account"><?= icon('user.png', '👤', 'Account') ?></a>
                <a href="<?= BASE_URL ?>logout.php" class="icon-link" title="Logout"><?= icon('logout.png', '⎋', 'Logout') ?></a>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login.php" class="icon-link" title="Login"><?= icon('user.png', '👤', 'Login') ?></a>
            <?php endif; ?>
        </div>
    </div>

    <div class="category-bar">
        <div class="container">
            <ul>
                <li><a href="<?= BASE_URL ?>products.php?category=football">Football</a></li>
                <li><a href="<?= BASE_URL ?>products.php?category=basketball">Basketball</a></li>
                <li><a href="<?= BASE_URL ?>products.php?category=running">Running</a></li>
                <li><a href="<?= BASE_URL ?>products.php?category=gym">Gym</a></li>
                <li><a href="<?= BASE_URL ?>products.php?category=sportswear">Sportswear</a></li>
            </ul>
        </div>
    </div>
</header>
