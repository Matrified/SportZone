<?php
// Contact page - saves the message to the database
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

$errors = [];
$old = ['name' => '', 'email' => '', 'message' => ''];
$sent = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['general'] = 'Invalid session token. Please try again.';
    } else {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $old = ['name' => $name, 'email' => $email, 'message' => $message];

        if ($name === '' || strlen($name) < 2) {
            $errors['name'] = 'Please enter your name.';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }
        if (strlen($message) < 10) {
            $errors['message'] = 'Your message should be at least 10 characters.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $name, $email, $message);
            $stmt->execute();
            $stmt->close();
            $sent = true;
            $old = ['name' => '', 'email' => '', 'message' => ''];
        }
    }
}

$page_title = 'Contact Us';
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="section">
    <div class="container contact-grid">
        <div>
            <h2 class="section-title">Get in Touch</h2>
            <p class="section-subtitle">Questions about an order or a product? Send us a message.</p>

            <div class="contact-info">
                <p><strong>Email:</strong> support@sportzone.com</p>
                <p><strong>Phone:</strong> +60 3-8312 5000</p>
                <p><strong>Address:</strong> Persiaran Multimedia, 63100 Cyberjaya, Selangor, Malaysia</p>
                <p><strong>Hours:</strong> Mon–Fri, 9am–6pm</p>
            </div>
        </div>

        <div class="panel">
            <?php if ($sent): ?>
                <div class="alert alert-success">Thanks for reaching out! We'll reply to your email soon.</div>
            <?php endif; ?>
            <?php if (!empty($errors['general'])): ?>
                <div class="alert alert-error"><?= sanitize($errors['general']) ?></div>
            <?php endif; ?>

            <form method="POST" action="contact.php" id="contactForm" novalidate>
                <?= csrf_field() ?>
                <div class="form-group <?= isset($errors['name']) ? 'invalid' : '' ?>">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" value="<?= sanitize($old['name']) ?>">
                    <span class="error-text"><?= $errors['name'] ?? '' ?></span>
                </div>
                <div class="form-group <?= isset($errors['email']) ? 'invalid' : '' ?>">
                    <label for="email">Your Email</label>
                    <input type="email" id="email" name="email" value="<?= sanitize($old['email']) ?>">
                    <span class="error-text"><?= $errors['email'] ?? '' ?></span>
                </div>
                <div class="form-group <?= isset($errors['message']) ? 'invalid' : '' ?>">
                    <label for="message">Message</label>
                    <textarea id="message" name="message" rows="5"><?= sanitize($old['message']) ?></textarea>
                    <span class="error-text"><?= $errors['message'] ?? '' ?></span>
                </div>
                <button type="submit" class="btn btn-primary">Send Message</button>
            </form>
        </div>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/contact.js"></script>
<?php include 'includes/footer.php'; ?>
