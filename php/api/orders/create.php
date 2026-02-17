<?php
/**
 * Create Order API
 * POST /api/orders/create.php
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

require_once '../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Please login to place order']);
    exit;
}

$userId = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);

$addressId = intval($data['address_id'] ?? 0);
$paymentMethod = sanitize($data['payment_method'] ?? 'cod');
$couponCode = sanitize($data['coupon_code'] ?? '');
$notes = sanitize($data['notes'] ?? '');

$conn = getDbConnection();

// Get cart items
$stmt = $conn->prepare("SELECT c.*, p.name, p.sku, p.price, p.sale_price, p.quantity as stock
                        FROM cart c
                        JOIN products p ON c.product_id = p.id
                        WHERE c.user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$cartResult = $stmt->get_result();

if ($cartResult->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cart is empty']);
    $stmt->close();
    closeDbConnection($conn);
    exit;
}

$cartItems = [];
$subtotal = 0;

while ($item = $cartResult->fetch_assoc()) {
    // Check stock
    if ($item['stock'] < $item['quantity']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => "Insufficient stock for {$item['name']}"]);
        $stmt->close();
        closeDbConnection($conn);
        exit;
    }
    
    $price = $item['sale_price'] ? floatval($item['sale_price']) : floatval($item['price']);
    $item['unit_price'] = $price;
    $item['total_price'] = $price * $item['quantity'];
    $subtotal += $item['total_price'];
    $cartItems[] = $item;
}
$stmt->close();

// Get shipping address
$stmt = $conn->prepare("SELECT * FROM addresses WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $addressId, $userId);
$stmt->execute();
$address = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$address) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid shipping address']);
    closeDbConnection($conn);
    exit;
}

// Calculate totals
$discountAmount = 0;

// Validate coupon if provided
if ($couponCode) {
    $stmt = $conn->prepare("SELECT * FROM coupons WHERE code = ? AND is_active = 1 AND (starts_at IS NULL OR starts_at <= NOW()) AND (expires_at IS NULL OR expires_at >= NOW()) AND (usage_limit IS NULL OR used_count < usage_limit)");
    $stmt->bind_param("s", $couponCode);
    $stmt->execute();
    $coupon = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    
    if ($coupon && $subtotal >= $coupon['min_order_amount']) {
        if ($coupon['discount_type'] === 'percentage') {
            $discountAmount = $subtotal * ($coupon['discount_value'] / 100);
            if ($coupon['max_discount_amount'] && $discountAmount > $coupon['max_discount_amount']) {
                $discountAmount = $coupon['max_discount_amount'];
            }
        } else {
            $discountAmount = $coupon['discount_value'];
        }
    }
}

$taxAmount = ($subtotal - $discountAmount) * TAX_RATE;
$shippingAmount = $subtotal >= FREE_SHIPPING_THRESHOLD ? 0 : SHIPPING_COST;
$totalAmount = $subtotal - $discountAmount + $taxAmount + $shippingAmount;

// Generate order number
$orderNumber = generateOrderNumber();

// Start transaction
$conn->begin_transaction();

try {
    // Create order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, order_number, payment_method, subtotal, tax_amount, shipping_amount, discount_amount, total_amount, coupon_code, shipping_address_id, shipping_name, shipping_phone, shipping_address, shipping_city, shipping_country, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $shippingAddressText = $address['address_line1'] . ($address['address_line2'] ? ', ' . $address['address_line2'] : '');
    
    $stmt->bind_param("issdddddsissssss", 
        $userId, $orderNumber, $paymentMethod, $subtotal, $taxAmount, $shippingAmount, $discountAmount, $totalAmount, $couponCode, $addressId, $address['full_name'], $address['phone'], $shippingAddressText, $address['city'], $address['country'], $notes
    );
    $stmt->execute();
    $orderId = $conn->insert_id;
    $stmt->close();
    
    // Create order items
    $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, product_sku, quantity, unit_price, total_price) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($cartItems as $item) {
        $stmt->bind_param("iissiddd", $orderId, $item['product_id'], $item['name'], $item['sku'], $item['quantity'], $item['unit_price'], $item['total_price']);
        $stmt->execute();
        
        // Update product stock
        $conn->query("UPDATE products SET quantity = quantity - {$item['quantity']} WHERE id = {$item['product_id']}");
    }
    $stmt->close();
    
    // Add order status history
    $stmt = $conn->prepare("INSERT INTO order_status_history (order_id, status, comment) VALUES (?, 'pending', 'Order placed')");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $stmt->close();
    
    // Update coupon usage if used
    if ($couponCode && isset($coupon)) {
        $conn->query("UPDATE coupons SET used_count = used_count + 1 WHERE id = {$coupon['id']}");
        
        $stmt = $conn->prepare("INSERT INTO coupon_usage (coupon_id, user_id, order_id, discount_amount) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $coupon['id'], $userId, $orderId, $discountAmount);
        $stmt->execute();
        $stmt->close();
    }
    
    // Clear cart
    $stmt = $conn->prepare("DELETE FROM cart WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $stmt->close();
    
    $conn->commit();
    
    echo json_encode([
        'success' => true,
        'message' => 'Order placed successfully',
        'data' => [
            'order_id' => $orderId,
            'order_number' => $orderNumber,
            'total_amount' => $totalAmount
        ]
    ]);
    
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to place order: ' . $e->getMessage()]);
}

closeDbConnection($conn);
?>
