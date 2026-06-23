<?php
/**
 * SportZone - User Profile / Account
 * Member 1 (Hadi Abdulla) - View & edit profile, change password.
 */
require_once 'includes/config.php';
require_once 'includes/db_connect.php';
require_once 'includes/functions.php';
require_login();

$user_id = $_SESSION['user_id'];
$errors = [];

// Load current user
$stmt = $conn->prepare("SELECT full_name, email, phone, address, created_at FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// ---- Handle profile update ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_profile') {
    if (!verify_csrf()) {
        $errors['general'] = 'Invalid session token.';
    } else {
        $full_name = trim($_POST['full_name'] ?? '');
        $phone     = trim($_POST['phone'] ?? '');
        $address   = trim($_POST['address'] ?? '');

        if ($full_name === '' || strlen($full_name) < 3) {
            $errors['full_name'] = 'Full name must be at least 3 characters.';
        }
        if ($phone !== '' && !preg_match('/^[0-9+\-\s]{7,20}$/', $phone)) {
            $errors['phone'] = 'Please enter a valid phone number.';
        }

        if (empty($errors)) {
            $upd = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, address = ? WHERE user_id = ?");
            $upd->bind_param("sssi", $full_name, $phone, $address, $user_id);
            $upd->execute();
            $upd->close();
            $_SESSION['full_name'] = $full_name;
            set_flash('success', 'Profile updated successfully.');
            header("Location: " . BASE_URL . "profile.php");
            exit;
        }
    }
}

// ---- Handle password change ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'change_password') {
    if (!verify_csrf()) {
        $errors['general'] = 'Invalid session token.';
    } else {
        $current = $_POST['current_password'] ?? '';
        $new     = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_new'] ?? '';

        $pstmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");
        $pstmt->bind_param("i", $user_id);
        $pstmt->execute();
        $hash = $pstmt->get_result()->fetch_assoc()['password'];
        $pstmt->close();

        if (!password_verify($current, $hash)) {
            $errors['pwd'] = 'Your current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $errors['pwd'] = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $errors['pwd'] = 'New passwords do not match.';
        } else {
            $newHash = password_hash($new, PASSWORD_DEFAULT);
            $upd = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
            $upd->bind_param("si", $newHash, $user_id);
            $upd->execute();
            $upd->close();
            set_flash('success', 'Password changed successfully.');
            header("Location: " . BASE_URL . "profile.php");
            exit;
        }
    }
}

$page_title = 'My Account';
include 'includes/head.php';
include 'includes/header.php';
$flash = get_flash();
?>

<section class="section">
    <div class="container">
        <h2 class="section-title">My Account</h2>
        <p class="section-subtitle">Manage your profile information and security</p>

        <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] === 'success' ? 'success' : 'error' ?>"><?= sanitize($flash['message']) ?></div>
        <?php endif; ?>
        <?php if (!empty($errors['general'])): ?>
            <div class="alert alert-error"><?= sanitize($errors['general']) ?></div>
        <?php endif; ?>

        <div class="profile-layout">
            <!-- Sidebar -->
            <aside class="profile-sidebar">
                <div class="avatar">👤</div>
                <h3><?= sanitize($user['full_name']) ?></h3>
                <p style="color:#888; font-size:0.85rem;"><?= sanitize($user['email']) ?></p>
                <p style="color:#aaa; font-size:0.8rem; margin-top:6px;">Member since <?= date('M Y', strtotime($user['created_at'])) ?></p>
                <a href="orders.php" class="btn btn-dark btn-block mt-2">My Orders</a>
                <a href="logout.php" class="btn btn-outline btn-block" style="margin-top:10px;">Logout</a>
            </aside>

            <!-- Main -->
            <div class="profile-main">
                <div class="panel">
                    <h3 style="margin-bottom:18px;">Profile Information</h3>
                    <form method="POST" action="profile.php" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group <?= isset($errors['full_name']) ? 'invalid' : '' ?>">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" name="full_name" value="<?= sanitize($user['full_name']) ?>">
                            <span class="error-text"><?= $errors['full_name'] ?? '' ?></span>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" value="<?= sanitize($user['email']) ?>" disabled style="background:#f3f3f3;">
                        </div>

                        <div class="form-group <?= isset($errors['phone']) ? 'invalid' : '' ?>">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" value="<?= sanitize($user['phone'] ?? '') ?>" placeholder="e.g. +60 12-345 6789">
                            <span class="error-text"><?= $errors['phone'] ?? '' ?></span>
                        </div>

                        <div class="form-group">
                            <label for="address">Default Shipping Address</label>
                            <textarea id="address" name="address" rows="3" placeholder="Street, city, postal code"><?= sanitize($user['address'] ?? '') ?></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">Save Changes</button>
                    </form>
                </div>

                <div class="panel">
                    <h3 style="margin-bottom:18px;">Change Password</h3>
                    <?php if (!empty($errors['pwd'])): ?>
                        <div class="alert alert-error"><?= sanitize($errors['pwd']) ?></div>
                    <?php endif; ?>
                    <form method="POST" action="profile.php" novalidate>
                        <?= csrf_field() ?>
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password">
                        </div>
                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" placeholder="At least 6 characters">
                        </div>
                        <div class="form-group">
                            <label for="confirm_new">Confirm New Password</label>
                            <input type="password" id="confirm_new" name="confirm_new">
                        </div>

                        <button type="submit" class="btn btn-dark">Update Password</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
