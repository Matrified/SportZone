<?php
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
$page_title = 'Our Team';
include 'includes/head.php';
include 'includes/header.php';

$members = [
    [
        'name' => 'Hadi Abdulla',
        'id' => '242UC243PP',
        'role' => 'Authentication & User Management Specialist',
        'pages' => 'Login Page, Registration Page, User Profile/Account Page',
        'features' => 'User registration & login system, session management, profile management'
    ],
    [
        'name' => 'Ahmed Mahmoud Mohamed',
        'id' => '243UC245XT',
        'role' => 'Product Catalog & Display Specialist',
        'pages' => 'Home Page, Product Listing Page, Product Details Page',
        'features' => 'Product browsing & categorization, search & filter system, reviews & ratings'
    ],
    [
        'name' => 'Osman Omer Gumaa',
        'id' => '243UC245R0',
        'role' => 'Shopping Cart & Order Processing Specialist',
        'pages' => 'Shopping Cart Page, Checkout Page, Order History Page',
        'features' => 'Shopping cart system, checkout & order placement, order tracking'
    ],
    [
        'name' => 'Mohamed Tarek',
        'id' => '242UC2435F',
        'role' => 'Admin Panel & Management Specialist',
        'pages' => 'Admin Dashboard, Product Management (CRUD), Order Management',
        'features' => 'Dashboard analytics, product CRUD operations, order management system'
    ],
];
?>

<section class="section">
    <div class="container">
        <!-- page header -->
        <div class="team-cover">
            <p class="team-cover-uni">Multimedia University</p>
            <h2 style="margin-top:6px;">Web Application Development (CIT6224)</h2>
            <p style="color:#666; margin-top:4px;">SportZone — Sports Equipment &amp; Apparel E-Commerce System</p>
            <p style="font-weight:600; color:var(--color-accent); margin-top:6px;">Group 16 &middot; TC2L</p>
        </div>

        <h2 class="section-title" style="text-align:center;">Meet Our Team</h2>
        <p class="section-subtitle" style="text-align:center;">The four developers behind SportZone</p>

        <div class="team-grid">
            <?php foreach ($members as $m): ?>
                <div class="team-card">
                    <div class="team-avatar">👤</div>
                    <h3 style="margin-bottom:2px; text-align:center;"><?= sanitize($m['name']) ?></h3>
                    <p style="color:#888; font-size:0.85rem; text-align:center; margin-bottom:12px;">Student ID: <?= sanitize($m['id']) ?></p>
                    <p style="font-weight:600; color:var(--color-accent); margin-bottom:10px; text-align:center;"><?= sanitize($m['role']) ?></p>
                    <p style="font-size:0.9rem; margin-bottom:6px;"><strong>Pages developed:</strong> <?= sanitize($m['pages']) ?></p>
                    <p style="font-size:0.9rem;"><strong>Key features:</strong> <?= sanitize($m['features']) ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
