<?php
// About page
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
$page_title = 'About Us';
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="section">
    <div class="container" style="max-width:820px;">
        <h2 class="section-title">About SportZone</h2>
        <p class="section-subtitle">Sports equipment &amp; apparel, online.</p>

        <p style="margin-bottom:16px; line-height:1.8; color:#444;">
            SportZone is an online sports store built as a university project for the Web Application
            Development module (CIT6224) at Multimedia University. We bring together equipment, footwear
            and apparel for football, basketball, running, gym and everyday sportswear in one place, so
            you can shop for everything you need without visiting several shops.
        </p>

        <p style="margin-bottom:24px; line-height:1.8; color:#444;">
            Our goal is to make buying sports gear simple: clear product information, honest customer
            reviews, an easy shopping cart and a quick checkout, with order tracking from the moment you
            place an order until it arrives.
        </p>

        <div class="about-grid">
            <div class="about-box">
                <h3>What we offer</h3>
                <ul style="color:#555; line-height:1.9; margin-left:18px;">
                    <li>Five product categories with dozens of items</li>
                    <li>Search, filtering and sorting to find gear fast</li>
                    <li>Verified customer reviews and ratings</li>
                    <li>Wishlist to save items for later</li>
                    <li>Secure accounts and order history</li>
                </ul>
            </div>
            <div class="about-box">
                <h3>Why shop with us</h3>
                <ul style="color:#555; line-height:1.9; margin-left:18px;">
                    <li>Browse anytime, no store hours</li>
                    <li>Transparent pricing in Ringgit (RM)</li>
                    <li>Real-time stock availability</li>
                    <li>Order tracking and easy cancellation</li>
                    <li>Responsive design for phone and desktop</li>
                </ul>
            </div>
        </div>

        <p style="margin-top:24px; color:#444;">
            Have a question? Visit our <a href="contact.php" style="color:var(--color-accent); font-weight:600;">Contact page</a>
            and we'll get back to you.
        </p>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
