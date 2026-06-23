<?php
/**
 * SportZone - User Login
 * Member 1 (Hadi Abdulla) - Authentication & User Management
 * Authenticates user, creates session, detects role for redirect.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';

if (is_logged_in()) {
    header("Location: " . BASE_URL . "index.php");
    exit;
}

$errors = [];
$old = ['email' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        $errors['general'] = 'Invalid session token. Please try again.';
    } else {
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $old['email'] = $email;

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        }
        if ($password === '') {
            $errors['password'] = 'Password is required.';
        }

        if (empty($errors)) {
            $stmt = $conn->prepare("SELECT user_id, full_name, password, role, status FROM users WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                if ($user['status'] !== 'active') {
                    $errors['general'] = 'Your account has been deactivated. Please contact support.';
                } elseif (password_verify($password, $user['password'])) {
                    // Prevent session fixation
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $user['user_id'];
                    $_SESSION['full_name'] = $user['full_name'];
                    $_SESSION['role']      = $user['role'];

                    if ($user['role'] === 'admin') {
                        header("Location: " . BASE_URL . "admin/dashboard.php");
                    } else {
                        header("Location: " . BASE_URL . "index.php");
                    }
                    exit;
                } else {
                    $errors['general'] = 'Invalid email or password.';
                }
            } else {
                $errors['general'] = 'Invalid email or password.';
            }
            $stmt->close();
        }
    }
}

$page_title = 'Login';
include 'includes/head.php';
include 'includes/header.php';
$flash = get_flash();
?>

<section class="section">
    <div class="form-card">
        <h2 class="text-center" style="margin-bottom:8px;">Welcome Back</h2>
        <p class="text-center" style="color:#666; margin-bottom:24px;">Login to your SportZone account</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= sanitize($errors['general']) ?></div>
        <?php endif; ?>

        <form id="loginForm" method="POST" action="login.php" novalidate>
            <?= csrf_field() ?>

            <div class="form-group <?= isset($errors['email']) ? 'invalid' : '' ?>">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" value="<?= sanitize($old['email']) ?>" placeholder="you@example.com">
                <span class="error-text"><?= $errors['email'] ?? '' ?></span>
            </div>

            <div class="form-group <?= isset($errors['password']) ? 'invalid' : '' ?>">
                <label for="password">Password</label>
                <div style="position:relative;">
                    <input type="password" id="password" name="password" placeholder="Your password">
                    <button type="button" id="togglePassword" style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; font-size:1.1rem;">👁</button>
                </div>
                <span class="error-text"><?= $errors['password'] ?? '' ?></span>
            </div>

            <div class="form-group" style="display:flex; align-items:center; gap:8px;">
                <input type="checkbox" id="remember" name="remember" style="width:auto;">
                <label for="remember" style="font-weight:400; font-size:0.88rem;">Remember me</label>
            </div>

            <button type="submit" class="btn btn-primary btn-block">Login</button>
        </form>

        <p class="text-center mt-2" style="font-size:0.9rem;">
            Don't have an account? <a href="register.php" style="color:var(--color-accent); font-weight:600;">Register here</a>
        </p>
    </div>
</section>

<script src="<?= BASE_URL ?>assets/js/auth.js"></script>
<?php include 'includes/footer.php'; ?>
