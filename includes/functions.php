<?php
/**
 * SportZone - Shared Helper Functions
 * Include AFTER db_connect.php
 */

// Sanitize user input to help prevent XSS when echoing data back to HTML
function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Auth helpers
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

function is_admin() {
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

// Redirect helpers - call BEFORE any HTML output
function require_login() {
    if (!is_logged_in()) {
        header("Location: " . BASE_URL . "login.php");
        exit;
    }
}

function require_admin() {
    require_login();
    if (!is_admin()) {
        header("Location: " . BASE_URL . "index.php");
        exit;
    }
}

// Returns number of items currently in the logged-in user's cart (for the nav badge)
function get_cart_count($conn) {
    if (!is_logged_in()) {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COALESCE(SUM(quantity),0) AS total FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return (int) $result['total'];
}

// Simple star rating renderer, returns HTML string
function render_stars($rating) {
    $rating = round($rating);
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        $html .= $i <= $rating ? '★' : '☆';
    }
    return $html;
}

// format a price with the currency prefix, e.g. money(89.9) => "RM 89.90"
function money($amount) {
    return CURRENCY . ' ' . number_format((float) $amount, 2);
}

// Renders an icon image from assets/images/icons/. If the image file isn't
// there yet, it falls back to the emoji so the UI never looks broken.
function icon($file, $emoji, $label = '') {
    $src = BASE_URL . 'assets/images/icons/' . $file;
    $alt = htmlspecialchars($label, ENT_QUOTES);
    return '<span class="ic"><img src="' . $src . '" alt="' . $alt . '" '
         . 'onerror="this.style.display=\'none\';this.nextElementSibling.style.display=\'inline-flex\';">'
         . '<span class="ic-fb" style="display:none;">' . $emoji . '</span></span>';
}

// Returns a usable image URL for a product. Falls back to a bundled
// local SVG placeholder so the site works fully offline (no internet).
function product_image_url($image) {
    if (!empty($image) && file_exists(UPLOAD_PATH . $image)) {
        return UPLOAD_URL . rawurlencode($image);
    }
    return BASE_URL . 'assets/images/placeholder.svg';
}

// Flash messages (one-time session messages survive a redirect)
function set_flash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function get_flash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Render a flash message (call near top of page body)
function render_flash() {
    $flash = get_flash();
    if ($flash) {
        $cls = $flash['type'] === 'success' ? 'alert-success' : 'alert-error';
        echo '<div class="container" style="margin-top:18px;"><div class="alert ' . $cls . '">'
            . sanitize($flash['message']) . '</div></div>';
    }
}

// CSRF token helpers (protect state-changing form posts)
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="csrf_token" value="' . csrf_token() . '">';
}

function verify_csrf() {
    return isset($_POST['csrf_token'])
        && hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token']);
}

// Average rating + review count for a product
function product_rating($conn, $product_id) {
    $stmt = $conn->prepare("SELECT COALESCE(AVG(rating),0) AS avg_rating, COUNT(*) AS cnt FROM reviews WHERE product_id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    return ['avg' => (float) $row['avg_rating'], 'count' => (int) $row['cnt']];
}

// list of product ids the current user has wishlisted (so we can fill the hearts)
function get_wishlist_ids($conn) {
    if (!is_logged_in()) {
        return [];
    }
    $ids = [];
    $stmt = $conn->prepare("SELECT product_id FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($r = $res->fetch_assoc()) {
        $ids[] = (int) $r['product_id'];
    }
    $stmt->close();
    return $ids;
}

function get_wishlist_count($conn) {
    if (!is_logged_in()) {
        return 0;
    }
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM wishlist WHERE user_id = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $c = (int) $stmt->get_result()->fetch_assoc()['c'];
    $stmt->close();
    return $c;
}
