<?php
/**
 * SportZone - Admin User Management
 * Member 4 (Mohamed Tarek) - User Management (view + activate/deactivate).
 */
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// Toggle account status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'toggle' && verify_csrf()) {
    $uid = (int) ($_POST['user_id'] ?? 0);
    // Prevent admin from disabling their own account
    if ($uid !== (int) $_SESSION['user_id']) {
        $stmt = $conn->prepare("UPDATE users SET status = IF(status='active','inactive','active') WHERE user_id = ? AND role = 'customer'");
        $stmt->bind_param("i", $uid);
        $stmt->execute();
        $stmt->close();
        set_flash('success', 'User status updated.');
    } else {
        set_flash('error', 'You cannot change your own account status.');
    }
    header("Location: " . BASE_URL . "admin/users.php");
    exit;
}

$users = $conn->query("SELECT u.*,
    (SELECT COUNT(*) FROM orders o WHERE o.user_id = u.user_id) AS order_count
    FROM users u ORDER BY u.created_at DESC");

$page_title = 'User Management';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="panel" style="padding:0;">
    <div class="table-wrap" style="border:none;">
        <table class="data-table">
            <thead>
                <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Orders</th><th>Joined</th><th>Status</th><th>Action</th></tr>
            </thead>
            <tbody>
            <?php while ($u = $users->fetch_assoc()): ?>
                <tr>
                    <td>#<?= $u['user_id'] ?></td>
                    <td><?= sanitize($u['full_name']) ?></td>
                    <td><?= sanitize($u['email']) ?></td>
                    <td><span class="status-badge <?= $u['role'] === 'admin' ? 'status-processing' : 'status-delivered' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><?= $u['order_count'] ?></td>
                    <td><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                    <td><span class="status-badge <?= $u['status'] === 'active' ? 'status-delivered' : 'status-cancelled' ?>"><?= ucfirst($u['status']) ?></span></td>
                    <td>
                        <?php if ($u['role'] === 'customer'): ?>
                            <form method="POST" action="users.php" onsubmit="return confirm('Change this user\'s status?')">
                                <?= csrf_field() ?>
                                <input type="hidden" name="action" value="toggle">
                                <input type="hidden" name="user_id" value="<?= $u['user_id'] ?>">
                                <button type="submit" class="btn btn-outline btn-sm"><?= $u['status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
                            </form>
                        <?php else: ?>
                            <span style="color:#aaa; font-size:0.85rem;">—</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
