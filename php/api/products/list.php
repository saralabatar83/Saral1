<?php
/**
 * Get Products List API
 * GET /api/products/list.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once '../../config/config.php';

$conn = getDbConnection();

// Get query parameters
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = isset($_GET['limit']) ? min(50, max(1, intval($_GET['limit']))) : PRODUCTS_PER_PAGE;
$offset = ($page - 1) * $limit;

$categoryId = isset($_GET['category']) ? intval($_GET['category']) : null;
$brandId = isset($_GET['brand']) ? intval($_GET['brand']) : null;
$search = isset($_GET['search']) ? sanitize($_GET['search']) : null;
$featured = isset($_GET['featured']) ? true : false;
$dailyOffers = isset($_GET['daily_offers']) ? true : false;
$bestsellers = isset($_GET['bestsellers']) ? true : false;
$newArrivals = isset($_GET['new']) ? true : false;
$sortBy = isset($_GET['sort']) ? sanitize($_GET['sort']) : 'newest';

// Build query
$sql = "SELECT p.*, c.name as category_name, b.name as brand_name,
               (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image,
               (SELECT AVG(rating) FROM reviews WHERE product_id = p.id AND is_approved = 1) as avg_rating,
               (SELECT COUNT(*) FROM reviews WHERE product_id = p.id AND is_approved = 1) as review_count
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN brands b ON p.brand_id = b.id
        WHERE p.is_active = 1";

$params = [];
$types = "";

if ($categoryId) {
    $sql .= " AND (p.category_id = ? OR c.parent_id = ?)";
    $params[] = $categoryId;
    $params[] = $categoryId;
    $types .= "ii";
}

if ($brandId) {
    $sql .= " AND p.brand_id = ?";
    $params[] = $brandId;
    $types .= "i";
}

if ($search) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}

if ($featured) {
    $sql .= " AND p.is_featured = 1";
}

if ($dailyOffers) {
    $sql .= " AND p.is_daily_offer = 1 AND p.sale_price IS NOT NULL";
}

if ($bestsellers) {
    $sql .= " AND p.is_bestseller = 1";
}

if ($newArrivals) {
    $sql .= " AND p.is_new = 1";
}

// Sorting
switch ($sortBy) {
    case 'price_low':
        $sql .= " ORDER BY COALESCE(p.sale_price, p.price) ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY COALESCE(p.sale_price, p.price) DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    case 'popular':
        $sql .= " ORDER BY p.views DESC";
        break;
    case 'rating':
        $sql .= " ORDER BY avg_rating DESC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$sql .= " LIMIT ? OFFSET ?";
$params[] = $limit;
$params[] = $offset;
$types .= "ii";

// Execute query
$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
while ($row = $result->fetch_assoc()) {
    $row['price'] = floatval($row['price']);
    $row['sale_price'] = $row['sale_price'] ? floatval($row['sale_price']) : null;
    $row['avg_rating'] = $row['avg_rating'] ? round(floatval($row['avg_rating']), 1) : 0;
    $row['review_count'] = intval($row['review_count']);
    $row['discount_percent'] = $row['sale_price'] ? round((($row['price'] - $row['sale_price']) / $row['price']) * 100) : 0;
    $products[] = $row;
}
$stmt->close();

// Get total count for pagination
$countSql = "SELECT COUNT(*) as total FROM products p WHERE p.is_active = 1";
$countResult = $conn->query($countSql);
$totalProducts = $countResult->fetch_assoc()['total'];
$totalPages = ceil($totalProducts / $limit);

echo json_encode([
    'success' => true,
    'data' => $products,
    'pagination' => [
        'current_page' => $page,
        'total_pages' => $totalPages,
        'total_products' => intval($totalProducts),
        'per_page' => $limit
    ]
]);

closeDbConnection($conn);
?>
