<?php
/**
 * SportZone - Admin Layout Header
 * Member 4 (Mohamed Tarek) - Admin Panel.
 * Pages must include config/db/functions and call require_admin() BEFORE this.
 */
$admin_name = $_SESSION['full_name'] ?? 'Admin';
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' | SportZone Admin' : 'SportZone Admin' ?></title>
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/admin.css">
</head>
<body class="admin-body">
<div class="admin-wrap">
    <!-- Sidebar -->
    <aside class="admin-sidebar" id="adminSidebar">
        <div class="admin-logo">SPORT<span>ZONE</span><small>Admin Panel</small></div>
        <nav>
            <ul>
                <li><a href="<?= BASE_URL ?>admin/dashboard.php" class="<?= $current === 'dashboard.php' ? 'active' : '' ?>">📊 Dashboard</a></li>
                <li><a href="<?= BASE_URL ?>admin/products.php" class="<?= in_array($current, ['products.php','product-form.php']) ? 'active' : '' ?>">📦 Products</a></li>
                <li><a href="<?= BASE_URL ?>admin/categories.php" class="<?= $current === 'categories.php' ? 'active' : '' ?>">🏷 Categories</a></li>
                <li><a href="<?= BASE_URL ?>admin/orders.php" class="<?= in_array($current, ['orders.php','order-details.php']) ? 'active' : '' ?>">🧾 Orders</a></li>
                <li><a href="<?= BASE_URL ?>admin/users.php" class="<?= $current === 'users.php' ? 'active' : '' ?>">👥 Users</a></li>
                <li><a href="<?= BASE_URL ?>admin/messages.php" class="<?= $current === 'messages.php' ? 'active' : '' ?>">✉ Messages</a></li>
                <li class="divider"></li>
                <li><a href="<?= BASE_URL ?>index.php">🌐 View Store</a></li>
                <li><a href="<?= BASE_URL ?>logout.php">⎋ Logout</a></li>
            </ul>
        </nav>
    </aside>

    <!-- Main -->
    <div class="admin-main">
        <header class="admin-topbar">
            <button class="admin-menu-btn" id="adminMenuBtn">&#9776;</button>
            <h1><?= isset($page_title) ? sanitize($page_title) : 'Dashboard' ?></h1>
            <div class="admin-user">
                <span>👤 <?= sanitize($admin_name) ?></span>
            </div>
        </header>
        <main class="admin-content">
            <?php $flash = get_flash(); if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= sanitize($flash['message']) ?></div>
            <?php endif; ?>
