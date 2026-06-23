<?php
/**
 * SportZone - User Registration
 * Member 1 (Hadi Abdulla) - Authentication & User Management
 * Server-side validation (PHP) + client-side validation (JS).
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

// Already logged in? Send to home.
if (is_logged_in()) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$errors = [];
$old = ['full_name' => '', 'email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['general'] = 'Invalid session token. Please try again.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $password  = $_POST['password'] ?? '';
        $confirm   = $_POST['confirm_password'] ?? '';
        $terms     = isset($_POST['terms']);

        $old['full_name'] = $full_name;
        $old['email']     = $email;

        // ---- Server-side validation ----
        if ($full_name === '' || strlen($full_name) < 3) {
            $errors['full_name'] = 'Full name must be at least 3 characters.';
        } elseif (!preg_match('/^[a-zA-Z\s.\'-]+$/', $full_name)) {
            $errors['full_name'] = 'Full name may only contain letters and spaces.';
        }

        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if ($password !== $confirm) {
            $errors['confirm_password'] = 'Passwords do not match.';
        }

        if (!$terms) {
            $errors['terms'] = 'You must agree to the Terms & Conditions.';
        }

        // ---- Check email uniqueness ----
        if (empty($errors['email'])) {
            $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
            $check->bind_param("s", $email);
            $check->execute();
            $check->store_result();
            if ($check->num_rows > 0) {
                $errors['email'] = 'An account with this email already exists.';
            }
            $check->close();
        }

        // ---- Insert if valid ----
        if (empty($errors)) {
            $hashed = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, 'customer', 'active')");
            $stmt->bind_param("sss", $full_name, $email, $hashed);
            if ($stmt->execute()) {
                $stmt->close();
                set_flash('success', 'Registration successful! You can now log in.');
                header("Location: " . BASE_URL . "login.php");
                exit;
            } else {
                $errors['general'] = 'Something went wrong. Please try again.';
            }
        }
    }
}

$page_title = 'Register';
include 'includes/head.php';
include 'includes/header.php';
?>

<section class="section">
    <div class="form-card">
        <h2 class="text-center" style="margin-bottom:8px;">Create Account</h2>
        <p class="text-center" style="color:#666; margin-bottom:24px;">Join SportZone and start shopping</p>

        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= sanitize($errors['general']) ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST" action="register.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group <?= isset($errors['full_name']) ? 'invalid' : '' ?>">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" value="<?= sanitize($old['full_name']) ?>" placeholder="John Doe">
                <span class="error-text"><?= $errors['full_name'] ?? '' ?></span>
            </div>

            <div class="form-group <?= isset($errors['email']) ? 'invalid' : '' ?>">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= sanitize($old['email']) ?>" placeholder="you@example.com">
                <span class="error-text"><?= $errors['email'] ?? '' ?></span>
            </div>

            <div class="form-group <?= isset($errors['password']) ? 'invalid' : '' ?>">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="At least 6 characters">
                <span class="error-text"><?= $errors['password'] ?? '' ?></span>
            </div>

            <div class="form-group <?= isset($errors['confirm_password']) ? 'invalid' : '' ?>">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" placeholder="Re-enter your password">
                <span class="error-text"><?= $errors['confirm_password'] ?? '' ?></span>
            </div>

            <div class="form-group <?= isset($errors['terms']) ? 'invalid' : '' ?>" style="display:flex; gap:8px; align-items:flex-start;">
                <input type="checkbox" id="terms" name="terms" style="width:auto; margin-top:5px;">
                <label for="terms" style="font-weight:400; font-size:0.88rem;">I agree to the Terms &amp; Conditions and Privacy Policy.</label>
            </div>
            <span class="error-text" style="<?= isset($errors['terms']) ? 'display:block;' : '' ?> margin-top:-12px; margin-bottom:12px;"><?= $errors['terms'] ?? '' ?></span>

            <button type="submit" class="btn btn-primary btn-block">Register</button>
        </form>

        <p class="text-center mt-2" style="font-size:0.9rem;">
            Already have an account? <a href="login.php" style="color:var(--color-accent); font-weight:600;">Login here</a>
        </p>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/auth.js"></script>
<?php include 'includes/footer.php'; ?>
