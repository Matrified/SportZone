<?php
// Admin - view messages sent through the Contact page
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db_connect.php';
require_once __DIR__ . '/../includes/functions.php';
require_admin();

// delete a message
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete' && verify_csrf()) {
    $mid = (int) ($_POST['message_id'] ?? 0);
    $stmt = $conn->prepare("DELETE FROM contact_messages WHERE message_id = ?");
    $stmt->bind_param("i", $mid);
    $stmt->execute();
    $stmt->close();
    set_flash('success', 'Message deleted.');
    header("Location: " . BASE_URL . "admin/messages.php");
    exit;
}

$messages = $conn->query("SELECT * FROM contact_messages ORDER BY created_at DESC");

$page_title = 'Contact Messages';
include __DIR__ . '/includes/admin_header.php';
?>

<div class="panel" style="padding:0;">
    <div class="table-wrap" style="border:none;">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Name</th><th>Email</th><th>Message</th><th></th></tr></thead>
            <tbody>
            <?php while ($m = $messages->fetch_assoc()): ?>
                <tr>
                    <td style="white-space:nowrap;"><?= date('d M Y', strtotime($m['created_at'])) ?></td>
                    <td><?= sanitize($m['name']) ?></td>
                    <td><?= sanitize($m['email']) ?></td>
                    <td style="max-width:420px;"><?= nl2br(sanitize($m['message'])) ?></td>
                    <td>
                        <form method="POST" action="messages.php" onsubmit="return confirm('Delete this message?')">
                            <?= csrf_field() ?>
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="message_id" value="<?= $m['message_id'] ?>">
                            <button type="submit" class="btn btn-sm" style="background:var(--color-danger); color:#fff;">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
            <?php if ($messages->num_rows === 0): ?>
                <tr><td colspan="5" style="text-align:center; color:#888; padding:30px;">No messages yet.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include __DIR__ . '/includes/admin_footer.php'; ?>
