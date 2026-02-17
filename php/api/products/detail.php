<?php
/**
 * Get Product Detail API
 * GET /api/products/detail.php?id=1
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../../config/config.php';

$productId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : null;

if (!$productId && !$slug) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Product ID or slug is required']);
    exit;
}

$conn = getDbConnection();

// Get product
if ($productId) {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.id
                            LEFT JOIN brands b ON p.brand_id = b.id
                            WHERE p.id = ? AND p.is_active = 1");
    $stmt->bind_param("i", $productId);
} else {
    $stmt = $conn->prepare("SELECT p.*, c.name as category_name, c.slug as category_slug, b.name as brand_name, b.slug as brand_slug
                            FROM products p
                            LEFT JOIN categories c ON p.category_id = c.id
                            LEFT JOIN brands b ON p.brand_id = b.id
                            WHERE p.slug = ? AND p.is_active = 1");
    $stmt->bind_param("s", $slug);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Product not found']);
    $stmt->close();
    closeDbConnection($conn);
    exit;
}

$product = $result->fetch_assoc();
$stmt->close();

// Update view count
$conn->query("UPDATE products SET views = views + 1 WHERE id = " . $product['id']);

// Get product images
$stmt = $conn->prepare("SELECT * FROM product_images WHERE product_id = ? ORDER BY sort_order");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$imagesResult = $stmt->get_result();
$product['images'] = [];
while ($img = $imagesResult->fetch_assoc()) {
    $product['images'][] = $img;
}
$stmt->close();

// Get product specifications
$stmt = $conn->prepare("SELECT * FROM product_specifications WHERE product_id = ? ORDER BY sort_order");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$specsResult = $stmt->get_result();
$product['specifications'] = [];
while ($spec = $specsResult->fetch_assoc()) {
    $product['specifications'][] = $spec;
}
$stmt->close();

// Get reviews summary
$stmt = $conn->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as review_count FROM reviews WHERE product_id = ? AND is_approved = 1");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$reviewSummary = $stmt->get_result()->fetch_assoc();
$product['avg_rating'] = $reviewSummary['avg_rating'] ? round(floatval($reviewSummary['avg_rating']), 1) : 0;
$product['review_count'] = intval($reviewSummary['review_count']);
$stmt->close();

// Get recent reviews
$stmt = $conn->prepare("SELECT r.*, u.first_name, u.last_name 
                        FROM reviews r 
                        JOIN users u ON r.user_id = u.id 
                        WHERE r.product_id = ? AND r.is_approved = 1 
                        ORDER BY r.created_at DESC 
                        LIMIT 5");
$stmt->bind_param("i", $product['id']);
$stmt->execute();
$reviewsResult = $stmt->get_result();
$product['reviews'] = [];
while ($review = $reviewsResult->fetch_assoc()) {
    $product['reviews'][] = $review;
}
$stmt->close();

// Format prices
$product['price'] = floatval($product['price']);
$product['sale_price'] = $product['sale_price'] ? floatval($product['sale_price']) : null;
$product['discount_percent'] = $product['sale_price'] ? round((($product['price'] - $product['sale_price']) / $product['price']) * 100) : 0;

echo json_encode([
    'success' => true,
    'data' => $product
]);

closeDbConnection($conn);
?>
