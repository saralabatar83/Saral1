<?php
/**
 * Application Configuration
 * TechMart E-commerce
 */

// Start session
session_start();

// Site settings
define('SITE_NAME', 'TechMart');
define('SITE_URL', 'http://localhost/techmart');
define('SITE_EMAIL', 'support@techmart.com');

// Currency
define('CURRENCY', 'AED');
define('CURRENCY_SYMBOL', 'AED ');

// Tax rate (5% VAT in UAE)
define('TAX_RATE', 0.05);

// Free shipping threshold
define('FREE_SHIPPING_THRESHOLD', 500);
define('SHIPPING_COST', 25);

// Pagination
define('PRODUCTS_PER_PAGE', 20);

// Upload paths
define('UPLOAD_PATH', '../uploads/');
define('PRODUCT_IMAGE_PATH', UPLOAD_PATH . 'products/');
define('BRAND_IMAGE_PATH', UPLOAD_PATH . 'brands/');
define('BANNER_IMAGE_PATH', UPLOAD_PATH . 'banners/');
define('AVATAR_PATH', UPLOAD_PATH . 'avatars/');

// Include database config
require_once 'database.php';

// Helper functions
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function redirect($url) {
    header("Location: $url");
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function formatPrice($price) {
    return CURRENCY_SYMBOL . number_format($price, 2);
}

function generateOrderNumber() {
    return 'TM' . date('Ymd') . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}
?>
